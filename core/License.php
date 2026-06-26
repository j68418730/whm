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
        $this->licenseFile = $basePath . "/license.key";
        $this->publicKeyFile = $basePath . "/config/license_public.pem";
    }

    public function verify($force = false)
    {
        if ($this->valid && !$force) {
            return ["valid" => true, "type" => "full", "source" => "cached"];
        }

        if (!is_file($this->licenseFile)) {
            return $this->trialResult("No license file");
        }

        if (!is_file($this->publicKeyFile)) {
            return $this->trialResult("No public key file");
        }

        $content = file_get_contents($this->licenseFile);

        // Local RSA verification
        $result = $this->verifyLocal($content);
        if ($result !== null) {
            return $result;
        }

        return $this->trialResult("License verification failed");
    }

    public function hasFeature($feature)
    {
        return true;
    }

    protected function verifyLocal($content)
    {
        if (!preg_match("/^-----BEGIN PLANET HOSTS LICENSE-----+\s+(.+?)\s+-----BEGIN LICENSE DATA-----+\s+(.+?)\s+-----END LICENSE DATA-----+\s+-----END PLANET HOSTS LICENSE-----+\s*$/s", $content, $matches)) {
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

        if (isset($data["expiry"]) && $data["expiry"] !== "never") {
            if (strtotime($data["expiry"]) < time()) {
                $this->valid = false;
                return ["valid" => false, "error" => "Expired " . $data["expiry"], "type" => $data["type"] ?? "full"];
            }
        }

        return ["valid" => true, "data" => $data, "type" => $data["type"] ?? "full"];
    }

    protected function trialResult($error = null)
    {
        return ["valid" => false, "error" => $error ?: "Trial mode", "trial" => true];
    }

    public function isValid() { $this->verify(); return $this->valid; }
    public function getData() { $this->verify(); return $this->data; }
}
