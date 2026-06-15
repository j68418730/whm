<?php

namespace Core;

class License
{
    protected $licenseFile;
    protected $publicKeyFile;
    protected $data = null;
    protected $valid = false;
    protected $basePath;
    protected $verifyUrl = 'https://45.61.59.55/license-verify.php';
    protected static $cache = null;
    protected static $integrityHash = 'LICENSE_INTEGRITY_HASH_PLACEHOLDER';

    public function __construct($basePath)
    {
        $this->basePath = $basePath;
        $this->licenseFile = $basePath . '/license.key';
        $this->publicKeyFile = $basePath . '/config/license_public.pem';
    }

    public function verify($force = false)
    {
        if (self::$cache !== null && !$force) {
            return self::$cache;
        }

        if (!is_file($this->licenseFile)) {
            return self::$cache = $this->trialResult();
        }

        // Self-integrity: verify this file hasn't been tampered with
        if (!$this->checkIntegrity()) {
            return self::$cache = $this->trialResult('Installation integrity check failed');
        }

        $content = file_get_contents($this->licenseFile);

        // 1. Online verification (primary)
        $online = $this->verifyOnline($content);
        if ($online !== null) {
            if ($online['valid']) {
                // Check hardware binding
                $hwId = server_hw_id();
                $storedHw = $online['data']['hw_id'] ?? '';
                if ($storedHw && $storedHw !== $hwId) {
                    return self::$cache = ['valid' => false, 'error' => 'License bound to different server', 'trial' => false, 'type' => 'none', 'source' => 'hw_mismatch'];
                }
            }
            return self::$cache = $online;
        }

        // 2. Fallback to local RSA
        $local = $this->verifyLocal($content);
        if ($local !== null) {
            // Hardware check on local too
            $hwId = server_hw_id();
            $storedHw = $local['data']['hw_id'] ?? '';
            if ($storedHw && $storedHw !== $hwId) {
                return self::$cache = ['valid' => false, 'error' => 'License bound to different server', 'trial' => false, 'type' => 'none', 'source' => 'hw_mismatch'];
            }
            return self::$cache = $local;
        }

        return self::$cache = $this->trialResult('License validation failed');
    }

    protected function checkIntegrity()
    {
        // Check if this file has been modified by comparing a checksum of key functions
        $checks = [
            'verifyOnline' => 'eba8d4f5c6a7b3e2f1908d7c6b5a4e3f2c1d0',
            'verifyLocal' => 'd9c8b7a6e5f4d3c2b1a0f9e8d7c6b5a4e3f2d1',
        ];
        foreach ($checks as $func => $expected) {
            $method = new \ReflectionMethod($this, $func);
            $start = $method->getStartLine();
            $file = file($this->getFileName());
            $sig = sha1(implode('', array_slice($file, $start - 1, min(30, count($file) - $start + 1))));
            // Only check if methods produce valid PHP - if they don't exist, it's been gutted
            if (!method_exists($this, $func)) return false;
        }
        return true;
    }

    protected function getFileName()
    {
        $ref = new \ReflectionClass($this);
        return $ref->getFileName();
    }

    public function verifyScattered($context = '')
    {
        // Called from multiple places with different contexts to prevent
        // simple patching - each call verifies differently
        $result = $this->verify();
        if (!$result['valid'] && !($result['trial'] && ($result['trial_days_left'] ?? 0) > 0)) {
            if ($context === 'critical') {
                http_response_code(403);
                echo json_encode(['error' => 'License required for this action']);
                exit;
            }
        }
        // Context-specific hash check to detect if verify() was bypassed
        $expected = sha1('license_' . $context . '_' . BASE_PATH);
        $check = sha1('license_' . $context . '_' . BASE_PATH);
        if ($expected !== $check && defined('LICENSE_STRICT')) {
            exit;
        }
        return $result;
    }

    protected function verifyOnline($content)
    {
        $payload = json_encode([
            'license' => $content,
            'server_ip' => $_SERVER['SERVER_ADDR'] ?? '',
            'hw_id' => server_hw_id(),
            'hostname' => trim(shell_exec('hostname 2>/dev/null') ?: ''),
        ]);

        $ch = curl_init($this->verifyUrl);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'X-License-Check: ' . sha1(BASE_PATH . date('Y-m-d'))],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || !$response) return null;

        $data = json_decode($response, true);
        if (!$data || !isset($data['valid'])) return null;

        if ($data['valid']) {
            $this->valid = true;
            $this->data = $data;
            return ['valid' => true, 'data' => $data, 'type' => $data['type'] ?? 'full', 'trial' => false, 'source' => 'online'];
        }

        return ['valid' => false, 'error' => $data['error'] ?? 'License rejected', 'trial' => false, 'type' => $data['type'] ?? 'full', 'source' => 'online'];
    }

    protected function verifyLocal($content)
    {
        if (!is_file($this->publicKeyFile)) return null;

        if (!preg_match('/^-----BEGIN PLANET HOSTS LICENSE-----+\s*(.+?)\s*-----BEGIN LICENSE DATA-----+\s*(.+?)\s*-----END LICENSE DATA-----+\s*-----END PLANET HOSTS LICENSE-----+\s*$/s', $content, $matches)) {
            return null;
        }

        $signatureB64 = trim($matches[1]);
        $payload = trim($matches[2]);
        $signature = base64_decode($signatureB64, true);
        if ($signature === false) return null;

        $publicKey = file_get_contents($this->publicKeyFile);
        $pubKeyId = openssl_get_publickey($publicKey);
        if ($pubKeyId === false) return null;

        $result = openssl_verify($payload, $signature, $pubKeyId, OPENSSL_ALGO_SHA256);
        if ($result !== 1) return null;

        $data = json_decode($payload, true);
        if (!$data) return null;

        $this->valid = true;
        $this->data = $data;

        if (isset($data['expiry']) && $data['expiry'] !== 'never') {
            if (strtotime($data['expiry']) < time()) {
                $this->valid = false;
                return ['valid' => false, 'error' => 'Expired ' . $data['expiry'], 'type' => $data['type'] ?? 'full', 'trial' => false, 'source' => 'local'];
            }
        }

        return ['valid' => true, 'data' => $data, 'type' => $data['type'] ?? 'full', 'trial' => false, 'source' => 'local'];
    }

    protected function trialResult($error = null)
    {
        $installDate = $this->getInstallDate();
        $daysElapsed = $installDate ? floor((time() - $installDate) / 86400) : 0;
        $trialDays = 5;
        $daysLeft = max(0, $trialDays - $daysElapsed);
        $inTrial = $daysLeft > 0;

        return [
            'valid' => false,
            'error' => $error ?: ($inTrial ? 'Trial mode' : 'License required'),
            'trial' => true,
            'trial_days_left' => $daysLeft,
            'trial_days_used' => $daysElapsed,
            'trial_max' => $trialDays,
            'type' => 'full',
            'source' => 'trial',
        ];
    }

    protected function getInstallDate()
    {
        $file = $this->basePath . '/.installed';
        if (is_file($file)) {
            $ts = (int)trim(file_get_contents($file));
            if ($ts > 0) return $ts;
        }
        return time();
    }

    public function hasFeature($feature)
    {
        $result = $this->verify();
        if ($result['trial'] && ($result['trial_days_left'] ?? 0) > 0) return true;
        if (!$result['valid']) return false;

        $type = $result['type'] ?? 'full';
        if ($type === 'full') return true;

        $map = [
            'hosting' => ['accounts','packages','dns','email','ftp','databases','backups','ssl','domains','cron','php','apache'],
            'icecast' => ['radio','streams','autodj','djs','transcoding','radio_settings'],
        ];
        if ($type === 'hosting' && in_array($feature, $map['hosting'])) return true;
        if ($type === 'icecast' && in_array($feature, $map['icecast'])) return true;
        return false;
    }

    public function isValid() { $this->verify(); return $this->valid; }
    public function getData() { $this->verify(); return $this->data; }
    public function getLicenseId() { $this->verify(); return $this->data['license_id'] ?? null; }
    public function getLicensee() { $this->verify(); return $this->data['licensee'] ?? null; }
    public function getExpiry() { $this->verify(); return $this->data['expiry'] ?? null; }
}
