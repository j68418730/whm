<?php

namespace Core;

class License
{
    protected $licenseFile;
    protected $publicKeyFile;
    protected $data = null;
    protected $valid = false;
    protected $basePath;

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
        if (!is_file($this->publicKeyFile)) {
            return $this->trialResult();
        }

        $content = file_get_contents($this->licenseFile);
        if (!preg_match('/^-----BEGIN PLANET HOSTS LICENSE-----+\s*(.+?)\s*-----BEGIN LICENSE DATA-----+\s*(.+?)\s*-----END LICENSE DATA-----+\s*-----END PLANET HOSTS LICENSE-----+\s*$/s', $content, $matches)) {
            return $this->trialResult();
        }

        $signatureB64 = trim($matches[1]);
        $payload = trim($matches[2]);
        $signature = base64_decode($signatureB64, true);
        if ($signature === false) return $this->trialResult('Invalid signature encoding');

        $publicKey = file_get_contents($this->publicKeyFile);
        $pubKeyId = openssl_get_publickey($publicKey);
        if ($pubKeyId === false) return $this->trialResult('Invalid public key');

        $result = openssl_verify($payload, $signature, $pubKeyId, OPENSSL_ALGO_SHA256);
        if ($result !== 1) return $this->trialResult('License signature invalid');

        $this->data = json_decode($payload, true);
        if (!$this->data) return $this->trialResult('Invalid license data');

        $this->valid = true;

        if (isset($this->data['expiry']) && $this->data['expiry'] !== 'never') {
            if (strtotime($this->data['expiry']) < time()) {
                $this->valid = false;
                return ['valid' => false, 'error' => 'License expired on ' . $this->data['expiry'], 'type' => $this->data['type'] ?? 'full', 'trial' => false];
            }
        }

        return ['valid' => true, 'data' => $this->data, 'type' => $this->data['type'] ?? 'full', 'trial' => false];
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
            'type' => 'full', // trial gets full access
        ];
    }

    protected function getInstallDate()
    {
        $file = $this->basePath . '/.installed';
        if (is_file($file)) {
            $ts = (int)trim(file_get_contents($file));
            if ($ts > 0) return $ts;
        }
        return time(); // If no install date, assume just installed
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
