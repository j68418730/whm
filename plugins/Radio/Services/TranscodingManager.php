<?php

namespace Plugins\Radio\Services;

use Core\Config;

class TranscodingManager
{
    protected $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function transcode($sourceFile, $targetFormat, $bitrate = 128)
    {
        $supportedFormats = $this->config->get('radio.transcoding.supported_formats');
        if (!in_array($targetFormat, $supportedFormats)) {
            throw new \Exception("Unsupported target format: {$targetFormat}");
        }
        $allowedBitrates = $this->config->get('radio.transcoding.default_bitrates');
        if (!in_array($bitrate, $allowedBitrates)) {
            throw new \Exception("Unsupported bitrate: {$bitrate}");
        }
        $info = pathinfo($sourceFile);
        $targetFile = "{$info['dirname']}/{$info['filename']}_{$bitrate}.{$targetFormat}";
        $ffmpegPath = $this->config->get('radio.transcoding.ffmpeg_path');
        $command = "{$ffmpegPath} -i \"{$sourceFile}\" -b:a {$bitrate}k \"{$targetFile}\" -y";
        exec($command, $output, $returnVar);
        if ($returnVar !== 0) {
            throw new \Exception("Transcoding failed: " . implode("\n", $output));
        }
        return $targetFile;
    }

    public function transcodeStream($sourceMount, $targetMount, $targetFormat, $bitrate = 128)
    {
        $supportedFormats = $this->config->get('radio.transcoding.supported_formats');
        if (!in_array($targetFormat, $supportedFormats)) {
            throw new \Exception("Unsupported target format: {$targetFormat}");
        }
        $allowedBitrates = $this->config->get('radio.transcoding.default_bitrates');
        if (!in_array($bitrate, $allowedBitrates)) {
            throw new \Exception("Unsupported bitrate: {$bitrate}");
        }
        $ffmpegPath = $this->config->get('radio.transcoding.ffmpeg_path');
        $icecastPassword = $this->config->get('radio.servers.icecast.admin_password', 'hackme');
        $command = "{$ffmpegPath} -re -i http://localhost:{$sourceMount} -b:a {$bitrate}k -f {$targetFormat} icecast://source:{$icecastPassword}@localhost:8001/{$targetMount}";
        return $command;
    }

    public function isEnabled()
    {
        return $this->config->get('radio.transcoding.enabled');
    }

    public function getOptions()
    {
        $formats = $this->config->get('radio.transcoding.supported_formats', []);
        $bitrates = $this->config->get('radio.transcoding.default_bitrates', []);
        $options = [];
        foreach ($formats as $format) {
            foreach ($bitrates as $bitrate) {
                $options[] = ['format' => $format, 'bitrate' => $bitrate];
            }
        }
        return $options;
    }
}
