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

    public function __construct($basePath)
    {
        $this->basePath = $basePath;
        $this->licenseFile = $basePath . '/license.key';
        $this->publicKeyFile = $basePath . '/config/license_public.pem';
    }

    public function verify()
    {
        if (!is_file($this->licenseFile)) {
            return $this->trialResult();
        }

        $content = file_get_contents($this->licenseFile);

        // 1. Try online verification first
        $online = $this->verifyOnline($content);
        if ($online !== null) {
            return $online;
        }

        // 2. Fallback to local RSA verification
        $local = $this->verifyLocal($content);
        if ($local !== null) {
            return $local;
        }

        // 3. Try trial
        return $this->trialResult('License validation failed');
    }

    protected function verifyOnline($content)
    {
        $payload = json_encode(['license' => $content, 'server_ip' => $_SERVER['SERVER_ADDR'] ?? '']);

        $ch = curl_init($this->verifyUrl);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || !$response) {
            return null; // Fallback
        }

        $data = json_decode($response, true);
        if (!$data || !isset($data['valid'])) {
            return null;
        }

        if ($data['valid']) {
            $this->valid = true;
            $this->data = $data;
            return [
                'valid' => true,
                'data' => $data,
                'type' => $data['type'] ?? 'full',
                'trial' => false,
                'source' => 'online',
            ];
        }

        // Server says invalid - respect that
        return [
            'valid' => false,
            'error' => $data['error'] ?? 'License rejected by server',
            'trial' => false,
            'type' => $data['type'] ?? 'full',
            'source' => 'online',
        ];
    }

    protected function verifyLocal($content)
    {
        if (!is_file($this->publicKeyFile)) {
            return null;
        }

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
                return ['valid' => false, 'error' => 'License expired on ' . $data['expiry'], 'type' => $data['type'] ?? 'full', 'trial' => false, 'source' => 'local'];
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
        if ($result['trial'] && $result['trial_days_left'] > 0) return true;
        if (!$result['valid']) return false;

        $type = $result['type'] ?? 'full';
        if ($type === 'full') return true;

        $featureMap = [
            'hosting' => ['accounts', 'packages', 'dns', 'email', 'ftp', 'databases', 'backups', 'ssl', 'domains', 'cron', 'php', 'apache'],
            'icecast' => ['radio', 'streams', 'autodj', 'djs', 'transcoding', 'radio_settings'],
        ];

        if ($type === 'hosting' && in_array($feature, $featureMap['hosting'])) return true;
        if ($type === 'icecast' && in_array($feature, $featureMap['icecast'])) return true;

        return false;
    }

    public function getFeatureRestrictions()
    {
        $result = $this->verify();
        if ($result['trial'] && $result['trial_days_left'] > 0) return 'trial';
        if (!$result['valid']) return 'none';
        return $result['type'] ?? 'full';
    }

    public function isValid() { return $this->valid; }
    public function getData() { return $this->data; }
    public function getLicenseId() { return $this->data['license_id'] ?? null; }
    public function getLicensee() { return $this->data['licensee'] ?? null; }
    public function getExpiry() { return $this->data['expiry'] ?? null; }
}
