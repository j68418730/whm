<?php

namespace Core;

class License
{
    protected $licenseFile;
    protected $publicKeyFile;
    protected $data = null;
    protected $valid = false;
    protected $basePath;

    const TRIAL_DAYS = 30;
    const GRACE_DAYS = 7;

    public function __construct($basePath)
    {
        $this->basePath = $basePath;
        $this->licenseFile = $basePath . "/license.key";
        $this->publicKeyFile = $basePath . "/config/license_public.pem";
    }

    public function verify($force = false)
    {
        if ($this->valid && !$force) {
            return $this->buildResult();
        }

        // Try local RSA verification first
        if (is_file($this->licenseFile) && is_file($this->publicKeyFile)) {
            $content = file_get_contents($this->licenseFile);
            $result = $this->verifyLocal($content);
            if ($result !== null) {
                return $result;
            }
        }

        // No valid license — return trial result
        return $this->trialResult();
    }

    public function hasFeature($feature)
    {
        $result = $this->verify();
        if (!$result['valid'] && !($result['trial'] ?? false)) {
            return false;
        }

        // Trial gets all basic features
        if ($result['trial'] ?? false) {
            $trialFeatures = ['accounts','packages','dns','email','ftp','databases','backups','ssl','domains','radio','streams','autodj','shared_hosting','radio_hosting','streaming_icecast','streaming_shoutcast_v1','streaming_shoutcast_v2','streaming_autodj','email_hosting','ftp_hosting','database_hosting','ssl_auto','backups','monitoring','api_access','desktop_app'];
            return in_array($feature, $trialFeatures);
        }

        // Licensed — check feature flags from license data
        $features = $result['features'] ?? [];
        if (in_array($feature, $features)) {
            return true;
        }

        // Check by license type
        $type = $result['type'] ?? 'full';
        $typeFeatures = $this->getFeaturesForType($type);
        return in_array($feature, $typeFeatures);
    }

    public function getTrialDaysLeft()
    {
        $trialFile = $this->basePath . '/storage/.trial_started';
        if (!is_file($trialFile)) {
            return self::TRIAL_DAYS;
        }
        $started = (int)file_get_contents($trialFile);
        $elapsed = floor((time() - $started) / 86400);
        return max(0, self::TRIAL_DAYS - $elapsed);
    }

    public function startTrial()
    {
        $trialFile = $this->basePath . '/storage/.trial_started';
        if (!is_file($trialFile)) {
            file_put_contents($trialFile, time());
        }
    }

    public function getDaysUntilExpiry()
    {
        $result = $this->verify();
        if (!isset($result['data']['expiry']) || $result['data']['expiry'] === 'never') {
            return null;
        }
        $expiry = strtotime($result['data']['expiry']);
        if ($expiry === false) return null;
        return max(0, ceil(($expiry - time()) / 86400));
    }

    public function getGraceDaysLeft()
    {
        $graceFile = $this->basePath . '/storage/.grace_started';
        if (!is_file($graceFile)) {
            return self::GRACE_DAYS;
        }
        $started = (int)file_get_contents($graceFile);
        $elapsed = floor((time() - $started) / 86400);
        return max(0, self::GRACE_DAYS - $elapsed);
    }

    public function startGrace()
    {
        $graceFile = $this->basePath . '/storage/.grace_started';
        if (!is_file($graceFile)) {
            file_put_contents($graceFile, time());
        }
    }

    public function isExpired()
    {
        $result = $this->verify();
        if ($result['valid']) return false;
        if ($result['trial'] ?? false) {
            return $this->getTrialDaysLeft() <= 0;
        }
        return true;
    }

    protected function verifyLocal($content)
    {
        $pattern = "/^-----BEGIN PLANET HOSTS LICENSE-----\s+(.+?)\s+-----BEGIN LICENSE DATA-----\s+(.+?)\s+-----END LICENSE DATA-----\s+-----END PLANET HOSTS LICENSE-----\s*$/s";
        if (!preg_match($pattern, $content, $matches)) {
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

        $this->data = $data;

        // Check expiry
        if (isset($data['expiry']) && $data['expiry'] !== 'never') {
            $expiryTs = strtotime($data['expiry']);
            if ($expiryTs !== false && $expiryTs < time()) {
                $this->valid = false;
                return $this->buildResult(false, 'License expired on ' . $data['expiry'], $data['type'] ?? 'full');
            }
        }

        $this->valid = true;
        return $this->buildResult();
    }

    protected function trialResult()
    {
        $daysLeft = $this->getTrialDaysLeft();
        $inGrace = $daysLeft <= 0 && $this->getGraceDaysLeft() > 0;

        if ($inGrace) {
            return [
                'valid' => false,
                'trial' => true,
                'in_grace' => true,
                'trial_days_left' => 0,
                'grace_days_left' => $this->getGraceDaysLeft(),
                'error' => 'Trial ended, grace period active',
                'type' => 'trial',
            ];
        }

        if ($daysLeft <= 0) {
            return [
                'valid' => false,
                'trial' => true,
                'expired' => true,
                'error' => 'Trial period has ended. License required.',
                'type' => 'trial',
            ];
        }

        return [
            'valid' => false,
            'trial' => true,
            'trial_days_left' => $daysLeft,
            'error' => 'Trial mode — ' . $daysLeft . ' days remaining',
            'type' => 'trial',
        ];
    }

    protected function buildResult($valid = null, $error = null, $type = null)
    {
        if ($valid === null) $valid = $this->valid;
        if ($type === null) $type = $this->data['type'] ?? 'full';

        $result = [
            'valid' => $valid,
            'type' => $type,
            'trial' => false,
        ];

        if ($valid && $this->data) {
            $result['data'] = $this->data;
            $result['features'] = $this->getFeaturesForType($type);
            $result['trial_days_left'] = 0;
        }

        if ($error) {
            $result['error'] = $error;
        }

        if ($this->data) {
            $result['license_id'] = $this->data['license_id'] ?? null;
            $result['licensee'] = $this->data['licensee'] ?? null;
            $result['expiry'] = $this->data['expiry'] ?? 'never';
        }

        return $result;
    }

    protected function getFeaturesForType($type)
    {
        $all = ['accounts','packages','dns','email','ftp','databases','backups','ssl','domains','radio','streams','autodj',
                 'shared_hosting','radio_hosting','streaming_icecast','streaming_shoutcast_v1','streaming_shoutcast_v2',
                 'streaming_autodj','email_hosting','ftp_hosting','database_hosting','ssl_auto','backups','monitoring',
                 'api_access','desktop_app'];

        switch ($type) {
            case 'trial':
                return $all;

            case 'hosting':
                return array_merge($all, ['ssl_wildcard','marketplace']);

            case 'icecast':
                return ['radio','streams','autodj','streaming_icecast','streaming_shoutcast_v1','streaming_shoutcast_v2',
                         'streaming_autodj','ssl_auto','monitoring','api_access','desktop_app'];

            case 'full':
            case 'lifetime':
                return array_merge($all, ['reseller_hosting','ssl_wildcard','marketplace','white_label']);

            case 'reseller':
                return array_merge($all, ['reseller_hosting','white_label','ssl_wildcard','marketplace']);

            case 'enterprise':
                return array_merge($all, ['reseller_hosting','vps_hosting','game_hosting','dns_clustering',
                                           'multi_server','white_label','ssl_wildcard','marketplace',
                                           'streaming_rtmp','streaming_rtsp','streaming_relay']);

            default:
                return $all;
        }
    }

    public function getLicenseTypeLabel($type)
    {
        $labels = [
            'trial' => 'Trial License',
            'monthly' => 'Monthly License',
            'yearly' => 'Yearly License',
            'lifetime' => 'Lifetime License',
            'internal' => 'Internal Development License',
            'reseller' => 'Reseller License',
            'enterprise' => 'Enterprise License',
            'hosting' => 'Hosting License',
            'icecast' => 'Icecast License',
            'full' => 'Full License',
        ];
        return $labels[$type] ?? ucfirst($type) . ' License';
    }

    public function isValid() { $this->verify(); return $this->valid; }
    public function getData() { $this->verify(); return $this->data; }
}
