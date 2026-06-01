<?php

namespace Core;

class License
{
    protected $licenseFile;
    protected $publicKeyFile;
    protected $data = null;
    protected $valid = false;

    public function __construct($basePath)
    {
        $this->licenseFile = $basePath . '/license.key';
        $this->publicKeyFile = $basePath . '/config/license_public.pem';
    }

    public function verify()
    {
        if (!is_file($this->licenseFile)) {
            return ['valid' => false, 'error' => 'License file not found'];
        }
        if (!is_file($this->publicKeyFile)) {
            return ['valid' => false, 'error' => 'Public key not found'];
        }

        $content = file_get_contents($this->licenseFile);

        // Parse the license file
        if (!preg_match('/^-----BEGIN PLANET HOSTS LICENSE-----+\s*(.+?)\s*-----BEGIN LICENSE DATA-----+\s*(.+?)\s*-----END LICENSE DATA-----+\s*-----END PLANET HOSTS LICENSE-----+\s*$/s', $content, $matches)) {
            return ['valid' => false, 'error' => 'Invalid license file format'];
        }

        $signatureB64 = trim($matches[1]);
        $payload = trim($matches[2]);

        // Decode the signature
        $signature = base64_decode($signatureB64, true);
        if ($signature === false) {
            return ['valid' => false, 'error' => 'Invalid signature encoding'];
        }

        // Load public key
        $publicKey = file_get_contents($this->publicKeyFile);
        $pubKeyId = openssl_get_publickey($publicKey);
        if ($pubKeyId === false) {
            return ['valid' => false, 'error' => 'Invalid public key'];
        }

        // Verify the signature
        $result = openssl_verify($payload, $signature, $pubKeyId, OPENSSL_ALGO_SHA256);

        if ($result !== 1) {
            return ['valid' => false, 'error' => 'Signature mismatch - license is invalid or tampered'];
        }

        // Parse payload
        $this->data = json_decode($payload, true);
        if (!$this->data) {
            return ['valid' => false, 'error' => 'Invalid license data'];
        }

        $this->valid = true;

        // Check expiry
        if (isset($this->data['expiry']) && $this->data['expiry'] !== 'never') {
            if (strtotime($this->data['expiry']) < time()) {
                $this->valid = false;
                return ['valid' => false, 'error' => 'License expired on ' . $this->data['expiry']];
            }
        }

        return ['valid' => true, 'data' => $this->data];
    }

    public function isValid()
    {
        return $this->valid;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getLicenseId()
    {
        return $this->data['license_id'] ?? null;
    }

    public function getLicensee()
    {
        return $this->data['licensee'] ?? null;
    }

    public function getExpiry()
    {
        return $this->data['expiry'] ?? null;
    }
}
