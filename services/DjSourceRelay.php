<?php
/**
 * DJ Source Authentication Relay
 *
 * Listens on a dedicated port (default: base_port + 2, e.g. 9002 for stream on 9000).
 * DJs connect here with their DJ username:password.
 * The relay validates against radio_djs table, then relays audio to SHOUTcast.
 *
 * Run: php /var/www/radiohosting/services/DjSourceRelay.php [stream_id]
 * Or use the AutoDJ runner to start it.
 */
namespace Services;

class DjSourceRelay
{
    protected $streamId;
    protected $db;
    protected $running = true;

    public function __construct($streamId)
    {
        $this->streamId = (int)$streamId;
        try {
            $this->db = new \PDO("mysql:host=localhost;dbname=radiohosting","radiouser","Skylinehosting171");
        } catch (\Exception $e) {}
    }

    public function run()
    {
        $st = $this->db->query("SELECT * FROM streaming_stations WHERE id=" . $this->streamId)->fetch(\PDO::FETCH_OBJ);
        if (!$st) { echo "Stream not found\n"; return; }

        $relayPort = $st->port + 2; // e.g., 9002 for stream on port 9000
        $shoutHost = 'localhost';
        $shoutPort = $st->port;
        $srcPass = $st->plain_password;
        $logDir = '/home/' . $this->getUsername($st->user_id) . '/radio/autodj';
        @mkdir($logDir, 0755, true);
        $logFile = $logDir . '/djrelay.log';
        $pidFile = $logDir . '/djrelay.pid';
        file_put_contents($pidFile, getmypid());

        $server = @stream_socket_server("tcp://0.0.0.0:{$relayPort}", $errno, $errstr, STREAM_SERVER_LISTEN | STREAM_SERVER_BIND);
        if (!$server) {
            $this->log($logFile, "Failed to listen on port {$relayPort}: {$errstr}");
            return;
        }
        $this->log($logFile, "DJ Relay listening on port {$relayPort} for stream {$this->streamId}");

        while ($this->running) {
            $client = @stream_socket_accept($server, 5);
            if (!$client) { usleep(100000); continue; }
            stream_set_timeout($client, 10);
            $this->handleClient($client, $shoutHost, $shoutPort, $srcPass, $logFile);
            if (is_resource($client)) fclose($client);
        }
        fclose($server);
        @unlink($pidFile);
    }

    protected function handleClient($client, $shoutHost, $shoutPort, $srcPass, $logFile)
    {
        // Read the first line (ICY source request)
        $line = fgets($client);
        if (!$line) return;
        $this->log($logFile, "Source request: " . trim($line));

        // Read headers to find Authorization header OR password after blank line
        $headers = '';
        $authHeader = '';
        $gotBlank = false;
        $passwordLine = '';
        while (($h = fgets($client)) !== false) {
            $h = trim($h);
            if ($h === '') { $gotBlank = true; continue; }
            if ($gotBlank) {
                $passwordLine = $h;
                break;
            }
            $headers .= $h . "\n";
            if (preg_match('/^Authorization:\s*Basic\s+(.+)$/i', $h, $m)) {
                $authHeader = base64_decode($m[1]);
            }
        }

        // Authenticate DJ
        $dj = null;
        if ($authHeader) {
            list($djUser, $djPass) = explode(':', $authHeader, 2);
            $dj = $this->authenticateDj($djUser, $djPass);
        }
        if (!$dj && $passwordLine) {
            // Try password as "username:password" format (SAM Broadcaster)
            if (strpos($passwordLine, ':') !== false) {
                list($djUser, $djPass) = explode(':', $passwordLine, 2);
                $dj = $this->authenticateDj($djUser, $djPass);
            }
            if (!$dj) {
                $dj = $this->authenticateDjByPassword($passwordLine);
            }
        }
        if (!$dj) {
            $this->log($logFile, "Auth failed - no valid DJ credentials");
            fwrite($client, "HTTP/1.0 401 Unauthorized\r\n\r\nInvalid DJ credentials. Use format: djusername:djpassword");
            fclose($client);
            return;
        }
        $this->log($logFile, "DJ authenticated: {$dj->username} ({$dj->name})");

        // Stop AutoDJ via PID file and stream-specific runner
        $pidFile = '/home/' . $this->getUsername($this->streamId) . '/radio/autodj/autodj.pid';
        if (file_exists($pidFile)) {
            $pid = (int)trim(file_get_contents($pidFile));
            if ($pid > 0) { exec("kill {$pid} 2>/dev/null"); usleep(500000); exec("kill -0 {$pid} 2>/dev/null && kill -9 {$pid} 2>/dev/null"); }
            @unlink($pidFile);
        }
        exec("pkill -f \"runner_{$this->streamId}\" 2>/dev/null");
        exec("pkill -f \"ffmpeg.*{$this->streamId}\" 2>/dev/null");
        try {
            $this->db->exec("UPDATE streaming_stations SET autodj_enabled=0 WHERE id=" . $this->streamId);
            $this->db->exec("UPDATE radio_autodj_config SET autodj_enabled=0 WHERE station_id=" . ($this->streamId + 10000));
        } catch (\Exception $e) {}

        // Wait for SHOUTcast to detect source disconnection
        sleep(2);

        // Connect to SHOUTcast as source
        $shout = @fsockopen($shoutHost, $shoutPort, $errno, $errstr, 5);
        if (!$shout) {
            $this->log($logFile, "SHOUTcast connection failed: {$errstr}");
            fwrite($client, "HTTP/1.0 502 Bad Gateway\r\n\r\nCannot connect to stream");
            return;
        }
        stream_set_timeout($shout, 5);
        fwrite($shout, $srcPass . "\r\n");
        $resp = fread($shout, 1024);
        if (strpos($resp, 'OK2') === false && strpos($resp, 'OK') === false) {
            $this->log($logFile, "SHOUTcast auth failed: " . trim($resp));
            fwrite($client, "HTTP/1.0 502 Bad Gateway\r\n\r\nStream auth failed");
            fclose($shout);
            return;
        }
        // Send ICY headers to SHOUTcast
        fwrite($shout, "icy-name: {$dj->name}\r\nicy-br: 128\r\nicy-pub: 1\r\nContent-Type: audio/mpeg\r\n\r\n");

        // Relay audio from client to SHOUTcast
        fwrite($client, "OK2\r\nicy-caps:11\r\n\r\n");
        $this->log($logFile, "Relaying audio from DJ {$dj->username}");

        $bufSize = 65536;
        while (!feof($client)) {
            $data = fread($client, $bufSize);
            if ($data === false || $data === '') break;
            fwrite($shout, $data);
        }
        fclose($shout);
        $this->log($logFile, "DJ {$dj->username} disconnected");
    }

    protected function authenticateDj($username, $password)
    {
        if (!$username || !$password || !$this->db) return null;
        $st = $this->db->prepare("SELECT * FROM radio_djs WHERE username = ? AND stream_id = ? AND status = 'active'");
        $st->execute([$username, $this->streamId]);
        $dj = $st->fetch(\PDO::FETCH_OBJ);
        if ($dj && password_verify($password, $dj->password)) return $dj;
        return null;
    }

    protected function authenticateDjByPassword($password)
    {
        if (!$password || !$this->db) return null;
        $st = $this->db->prepare("SELECT * FROM radio_djs WHERE stream_id = ? AND status = 'active'");
        $st->execute([$this->streamId]);
        while ($dj = $st->fetch(\PDO::FETCH_OBJ)) {
            if (password_verify($password, $dj->password)) return $dj;
        }
        return null;
    }

    protected function getUsername($userId)
    {
        $st = $this->db->prepare("SELECT username FROM hosting_users WHERE id = ?");
        $st->execute([$userId]);
        $r = $st->fetch(\PDO::FETCH_OBJ);
        return $r ? $r->username : 'unknown';
    }

    protected function log($file, $msg)
    {
        $line = "[" . date('Y-m-d H:i:s') . "] {$msg}\n";
        file_put_contents($file, $line, FILE_APPEND);
        echo $line;
    }
}

// CLI entry point
if (php_sapi_name() === 'cli' && isset($argv[1])) {
    $relay = new DjSourceRelay($argv[1]);
    $relay->run();
}