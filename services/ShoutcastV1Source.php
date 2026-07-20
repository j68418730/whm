<?php
/**
 * SHOUTcast v1 Source Client for AutoDJ
 * Reads MP3 files directly and streams to SHOUTcast v1 server
 * No exec/popen needed — pure PHP socket streaming
 */
class ShoutcastV1Source
{
    protected $host, $port, $password, $bitrate, $name, $pidFile, $logFile, $playlistFile, $streamId, $db;
    protected $running = true;

    public function __construct($host, $port, $password, $bitrate = 128, $name = 'Radio', $streamId = null)
    {
        $this->host = $host;
        $this->port = $port;
        $this->password = $password;
        $this->bitrate = $bitrate;
        $this->name = $name;
        $this->streamId = $streamId;
        try { $this->db = new PDO("mysql:host=localhost;dbname=radiohosting","radiouser","Skylinehosting171"); } catch (\Exception $e) {}
    }

    public function setPidFile($path) { $this->pidFile = $path; }
    public function setLogFile($path) { $this->logFile = $path; }
    public function setPlaylistFile($path) { $this->playlistFile = $path; }

    public function log($msg)
    {
        $line = "[" . date('Y-m-d H:i:s') . "] " . $msg . "\n";
        if ($this->logFile) file_put_contents($this->logFile, $line, FILE_APPEND);
    }

    public function run()
    {
        if ($this->pidFile) file_put_contents($this->pidFile, getmypid());
        $this->log("AutoDJ started");

        // Connect once and stay connected
        $sock = $this->connect();
        if (!$sock) { $this->log("Initial connection failed"); return; }

        while ($this->running) {
            $files = $this->getPlaylistFiles();
            if (empty($files)) { $this->log("No files, waiting..."); sleep(10); continue; }

            // Play files in order (no shuffle for sequential playback)
            foreach ($files as $file) {
                if (!$this->running) break;
                $this->streamFile($file, $sock);
            }
            // If we get here and socket died, try to reconnect
            if (!$sock) {
                $this->log("Socket lost, reconnecting...");
                $sock = $this->connect();
                if (!$sock) { sleep(10); }
            }
        }
        if ($sock) fclose($sock);
        $this->log("AutoDJ stopped");
        if ($this->pidFile) @unlink($this->pidFile);
    }

    protected function connect()
    {
        $sock = @fsockopen($this->host, $this->port, $errno, $errstr, 10);
        if (!$sock) { $this->log("Connection failed: $errstr"); sleep(5); return null; }
        stream_set_timeout($sock, 15);
        // Wait for server banner if any
        usleep(500000);
        fwrite($sock, $this->password . "\r\n");
        $resp = fread($sock, 1024);
        $this->log("Auth: [" . trim(preg_replace('/[\x00-\x1f]/', ' ', $resp)) . "]");
        if (strpos($resp, 'OK') === false && strpos($resp, 'OK2') === false) {
            $this->log("Auth rejected, retrying in 5s");
            fclose($sock);
            sleep(5);
            return null;
        }
        $headers = "icy-name: {$this->name}\r\nicy-br: {$this->bitrate}\r\nicy-pub: 1\r\n";
        fwrite($sock, $headers . "\r\n");
        $this->log("Connected & authenticated");
        return $sock;
    }

    protected function getPlaylistFiles()
    {
        if (!$this->playlistFile || !file_exists($this->playlistFile)) return [];
        $content = file_get_contents($this->playlistFile);
        $files = [];
        foreach (explode("\n", $content) as $line) {
            $line = trim($line);
            if ($line && $line[0] !== '#' && file_exists($line)) $files[] = $line;
        }
        return $files;
    }

    protected function streamFile($path, &$sock)
    {
        if (!file_exists($path)) return;
        $name = basename($path);
        $this->log("Streaming: $name");
        // Update current song in DB
        $title = pathinfo($name, PATHINFO_FILENAME);
        $artist = '';
        $parts = explode(' - ', $title, 2);
        if (count($parts) === 2) { $artist = trim($parts[0]); $title = trim($parts[1]); }
        if ($this->db && $this->streamId) {
            try {
                $this->db->exec("UPDATE streaming_stations SET current_song=" . $this->db->quote($title) . ", current_artist=" . $this->db->quote($artist) . ", current_song_started=NOW() WHERE id=" . ((int)$this->streamId % 10000));
                $this->db->exec("INSERT INTO radio_song_history (stream_id, title, artist, played_at) VALUES (" . ((int)$this->streamId % 10000) . ", " . $this->db->quote($title) . ", " . $this->db->quote($artist) . ", NOW())");
            } catch (\Exception $e) { $this->log("DB update failed: " . $e->getMessage()); }
        }
        $fp = fopen($path, 'rb');
        if (!$fp) return;
        $bufSize = 65536;
        // Slightly faster than real-time to maintain buffer (0.9x delay = 1.1x speed)
        $bytesPerSec = ($this->bitrate * 1000) / 8;
        $delayPerChunk = ($bufSize / $bytesPerSec) * 900000;
        while ($this->running && !feof($fp)) {
            $data = fread($fp, $bufSize);
            if ($data === false || $data === '') break;
            $written = @fwrite($sock, $data);
            if ($written === false || $written === 0) { $this->log("Write failed"); break; }
            usleep($delayPerChunk);
        }
        fclose($fp);
    }

    public function stop() { $this->running = false; }
}
