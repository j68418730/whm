<?php
namespace Services;

class RadioAutoDJPlayer
{
    protected $stream;
    protected $username;
    protected $musicDir;
    protected $autodjDir;
    protected $playlistIds = [];

    public function __construct($stream, $username, $playlistIds = [])
    {
        $this->stream = $stream;
        $this->username = $username;
        $this->musicDir = "/home/{$username}/radio/musicdatabase";
        $this->autodjDir = "/home/{$username}/radio/autodj";
        $this->playlistIds = is_array($playlistIds) ? $playlistIds : [];
    }

    public function start()
    {
        $this->stop();
        @mkdir($this->autodjDir, 0755, true);
        @mkdir($this->musicDir, 0755, true);
        $files = $this->scanMusicFiles();
        if (empty($files)) { error_log('AUTODJ: scanMusicFiles empty for dir=' . $this->musicDir . ' ids=' . json_encode($this->playlistIds)); return false; }
        error_log('AUTODJ: found ' . count($files) . ' files');
        $port = $this->stream->port ?? 8000;
        $port = $this->stream->port ?? 8000;
        $engine = $this->stream->engine ?? 'icecast';
        $password = $this->stream->plain_password ?? ($this->stream->password ?? '');
        $bitrate = $this->stream->bitrate ?? 128;
        $mount = $this->stream->mount_point ?? '/stream';
        $name = $this->stream->name ?? 'Radio';

        if ($engine === 'shoutcast' || $engine === 'shoutcast2') {
            // Use custom PHP source client for SHOUTcast v2
            $playlistPath = $this->generateM3u($files);
            $streamId = $this->stream->id ?? 0;
            $scriptPath = $this->autodjDir . '/runner_' . $streamId . '.php';
            $logPath = $this->autodjDir . '/autodj.log';
            $pidFile = $this->autodjDir . '/autodj.pid';
            $safeName = addcslashes($name, "'\\");
            $runner = <<<PHP
<?php
require_once '/var/www/radiohosting/services/ShoutcastSource.php';
\$s = new ShoutcastSource('localhost', {$port}, '{$password}', {$bitrate}, '{$safeName}', {$streamId});
\$s->setPidFile('{$pidFile}');
\$s->setLogFile('{$logPath}');
\$s->setPlaylistFile('{$playlistPath}');
\$s->run();
PHP;
            file_put_contents($scriptPath, $runner);
            exec("nohup php {$scriptPath} > {$logPath} 2>&1 & echo $!", $out);
            $pid = (int)($out[0] ?? 0);
            if ($pid > 0) file_put_contents($pidFile, $pid);
            usleep(500000);
        } else {
            $playlistPath = $this->generateConcat($files);
            $logPath = $this->autodjDir . '/autodj.log';
            $pidFile = $this->autodjDir . '/autodj.pid';
            $url = "http://source:{$password}@localhost:{$port}{$mount}";
            $cmd = "nohup ffmpeg -re -stream_loop -1 -f concat -safe 0 -i " . escapeshellarg($playlistPath)
                . " -c:a libmp3lame -b:a {$bitrate}k -f mp3 " . escapeshellarg($url)
                . " > {$logPath} 2>&1 & echo $!";
            error_log('AUTODJ: running ffmpeg cmd=' . $cmd);
            exec($cmd, $out, $code);
            error_log('AUTODJ: ffmpeg exit=' . $code . ' out=' . json_encode($out));
            $pid = (int)($out[0] ?? 0);
            if ($pid > 0) file_put_contents($pidFile, $pid);
            usleep(500000);
        }
        return $this->isRunning();
    }

    public function stop()
    {
        $streamId = $this->stream->id ?? 0;
        $pidFile = $this->autodjDir . '/autodj.pid';
        if (file_exists($pidFile)) {
            $pid = (int)trim(file_get_contents($pidFile));
            if ($pid > 0) {
                exec("kill {$pid} 2>/dev/null");
                usleep(300000);
                exec("kill -0 {$pid} 2>/dev/null && kill -9 {$pid} 2>/dev/null");
            }
            @unlink($pidFile);
        }
        $port = $this->stream->port ?? 8000;
        exec("pkill -f \"runner_{$streamId}\" 2>/dev/null");
        exec("pkill -f \"ffmpeg.*{$port}\" 2>/dev/null");
        exec("pkill -f \"ShoutcastSource.*{$port}\" 2>/dev/null");
    }

    public function isRunning()
    {
        $streamId = $this->stream->id ?? 0;
        $pidFile = $this->autodjDir . '/autodj.pid';
        if (file_exists($pidFile)) {
            $pid = (int)trim(file_get_contents($pidFile));
            if ($pid > 0) {
                exec("kill -0 {$pid} 2>/dev/null", $out, $code);
                return $code === 0;
            }
        }
        $port = $this->stream->port ?? 8000;
        exec("pgrep -f \"runner_{$streamId}\" 2>/dev/null", $pids);
        if (!empty($pids)) return true;
        exec("pgrep -f \"ffmpeg.*{$port}\" 2>/dev/null", $pids);
        return !empty($pids);
    }

    protected function scanMusicFiles()
    {
        $files = [];
        $extensions = ['mp3', 'wav', 'flac', 'ogg', 'aac', 'm4a', 'wma'];
        if (!empty($this->playlistIds)) {
            foreach ($this->playlistIds as $plId) {
                $dir = $this->musicDir . '/playlist_' . (int)$plId;
                if (!is_dir($dir)) continue;
                $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir));
                foreach ($it as $f) {
                    if ($f->isFile() && in_array(strtolower($f->getExtension()), $extensions)) {
                        $files[] = $f->getPathname();
                    }
                }
            }
        } else {
            if (!is_dir($this->musicDir)) return $files;
            $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->musicDir));
            foreach ($it as $f) {
                if ($f->isFile() && in_array(strtolower($f->getExtension()), $extensions)) {
                    $files[] = $f->getPathname();
                }
            }
        }
        shuffle($files);
        return $files;
    }

    protected function generateConcat($files)
    {
        $path = $this->autodjDir . '/concat.txt';
        $content = "ffconcat version 1.0\n";
        foreach ($files as $f) {
            $content .= "file " . escapeshellarg($f) . "\n";
        }
        file_put_contents($path, $content);
        return $path;
    }

    protected function generateM3u($files)
    {
        $path = $this->autodjDir . '/playlist.m3u';
        $content = "#EXTM3U\n";
        foreach ($files as $f) {
            $content .= "#EXTINF:-1," . basename($f) . "\n{$f}\n";
        }
        file_put_contents($path, $content);
        return $path;
    }
}