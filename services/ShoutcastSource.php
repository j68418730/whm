<?php
/**
 * SHOUTcast v2 Source Client for AutoDJ
 * Streams audio files from playlist to a SHOUTcast server
 */
class ShoutcastSource
{
    protected $host;
    protected $port;
    protected $password;
    protected $bitrate;
    protected $name;
    protected $pidFile;
    protected $logFile;
    protected $playlistFile;
    protected $running = true;
    protected $db;
    protected $streamId;

    public function __construct($host, $port, $password, $bitrate = 128, $name = 'Radio', $streamId = null)
    {
        $this->host = $host;
        $this->port = $port;
        $this->password = $password;
        $this->bitrate = $bitrate;
        $this->name = $name;
        $this->streamId = $streamId;
        try {
            $this->db = new PDO("mysql:host=localhost;dbname=radiohosting","radiouser","Skylinehosting171");
        } catch (\Exception $e) {}
    }

    public function setPidFile($path) { $this->pidFile = $path; }
    public function setLogFile($path) { $this->logFile = $path; }
    public function setPlaylistFile($path) { $this->playlistFile = $path; }
    public function run()
    {
        if ($this->pidFile) file_put_contents($this->pidFile, getmypid());
        $this->log("AutoDJ started");
        $sock = $this->connect();
        if (!$sock) { $this->log("Failed to connect to SHOUTcast"); return; }
        while ($this->running) {
            $files = $this->getPlaylistFiles();
            if (empty($files)) {
                $this->log("No files in playlist, waiting...");
                sleep(10);
                continue;
            }
            shuffle($files);
            foreach ($files as $file) {
                if (!$this->running) break;
                $this->streamFile($file, $sock);
            }
        }
        fclose($sock);
        $this->log("AutoDJ stopped");
        if ($this->pidFile) @unlink($this->pidFile);
    }

    public function stop()
    {
        $this->running = false;
    }

    protected function getPlaylistFiles()
    {
        if ($this->playlistFile && file_exists($this->playlistFile)) {
            $content = file_get_contents($this->playlistFile);
            $files = [];
            foreach (explode("\n", $content) as $line) {
                $line = trim($line);
                if ($line && $line[0] !== '#' && strpos($line, '/') === 0) {
                    $files[] = $line;
                }
            }
            if (!empty($files)) return $files;
        }
        return [];
    }

    protected function connect()
    {
        $sock = @fsockopen($this->host, $this->port, $errno, $errstr, 10);
        if (!$sock) { $this->log("Connection failed: $errstr"); return null; }
        stream_set_timeout($sock, 5);
        fwrite($sock, $this->password . "\r\n");
        $resp = fread($sock, 1024);
        $this->log("Auth response: " . trim($resp));
        if (strpos($resp, 'OK2') === false && strpos($resp, 'OK') === false) {
            $this->log("Auth rejected");
            fclose($sock);
            return null;
        }
        $headers = "icy-name: {$this->name}\r\n"
            . "icy-br: {$this->bitrate}\r\n"
            . "icy-pub: 1\r\n"
            . "Content-Type: audio/mpeg\r\n"
            . "\r\n";
        fwrite($sock, $headers);
        return $sock;
    }

    protected function streamFile($path, &$sock)
    {
        if (!file_exists($path)) { $this->log("File not found: $path"); return; }
        $name = basename($path);
        // Parse title/artist from filename
        $title = pathinfo($name, PATHINFO_FILENAME);
        $artist = '';
        $parts = explode(' - ', $title, 2);
        if (count($parts) === 2) { $artist = trim($parts[0]); $title = trim($parts[1]); }
        $this->log("Streaming: $name");

        // Update DB
        if ($this->db && $this->streamId) {
            try {
                $this->db->exec("UPDATE streaming_stations SET current_song=" . $this->db->quote($title) . ", current_artist=" . $this->db->quote($artist) . " WHERE id=" . ((int)$this->streamId % 10000));
                $this->db->exec("INSERT INTO radio_song_history (stream_id, title, artist, played_at) VALUES (" . ((int)$this->streamId % 10000) . ", " . $this->db->quote($title) . ", " . $this->db->quote($artist) . ", NOW())");
            } catch (\Exception $e) { $this->log("DB update failed: " . $e->getMessage()); }
        }

        // Pipe through FFmpeg to strip metadata (album art etc.)
        $tmpFile = '/tmp/shoutcast_pipe_' . uniqid() . '.raw';
        $cmd = "ffmpeg -i " . escapeshellarg($path) . " -f mp3 -vn -map_metadata -1 -c:a libmp3lame -b:a {$this->bitrate}k -y " . escapeshellarg($tmpFile) . " 2>/dev/null";
        exec($cmd, $cmdOut, $cmdCode);
        if ($cmdCode !== 0 || !is_file($tmpFile)) { $this->log("Cannot transcode: $path"); @unlink($tmpFile); return; }
        $fp = fopen($tmpFile, 'r');
        if (!$fp) { $this->log("Cannot open transcoded: $path"); @unlink($tmpFile); return; }

        $bufSize = 65536;
        while ($this->running && !feof($fp)) {
            $data = fread($fp, $bufSize);
            if ($data === false || $data === '') break;
            $written = @fwrite($sock, $data);
            if ($written === false || $written === 0) {
                $this->log("Write failed, reconnecting...");
                fclose($fp); @unlink($tmpFile);
                fclose($sock);
                $sock = $this->connect();
                if (!$sock) { $this->running = false; return; }
                $written = @fwrite($sock, $data);
                if ($written === false || $written === 0) { $this->running = false; @unlink($tmpFile); return; }
            }
            usleep(50000);
        }
        fclose($fp);
        @unlink($tmpFile);
    }

    protected function log($msg)
    {
        $line = "[" . date('Y-m-d H:i:s') . "] {$msg}\n";
        if ($this->logFile) file_put_contents($this->logFile, $line, FILE_APPEND);
    }
}