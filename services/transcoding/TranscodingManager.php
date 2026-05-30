<?php
/**
 * Transcoding Manager Service
 * Handles audio transcoding for radio streams
 * Integrated as core service in WHM
 */

namespace Services\Transcoding;

use Core\Config;

class TranscodingManager
{
    protected $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Transcode an audio file to a specified format and bitrate
     */
    public function transcode($sourceFile, $targetFormat, $bitrate = 128)
    {
        // Validate format
        $supportedFormats = $this->config->get('radio.transcoding.supported_formats');
        if (!in_array($targetFormat, $supportedFormats)) {
            throw new \Exception("Unsupported target format: {$targetFormat}");
        }

        // Validate bitrate
        $allowedBitrates = $this->config->get('radio.transcoding.default_bitrates');
        if (!in_array($bitrate, $allowedBitrates)) {
            throw new \Exception("Unsupported bitrate: {$bitrate}");
        }

        // Generate target file name
        $info = pathinfo($sourceFile);
        $targetFile = "{$info['dirname']}/{$info['filename']}_{$bitrate}.{$targetFormat}";

        // Build ffmpeg command
        $ffmpegPath = $this->config->get('radio.transcoding.ffmpeg_path');
        $command = "{$ffmpegPath} -i \"{$sourceFile}\" -b:a {$bitrate}k \"{$targetFile}\" -y";

        // Execute the command
        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            throw new \Exception("Transcoding failed: " . implode("\n", $output));
        }

        return $targetFile;
    }

    /**
     * Transcode a stream in real-time (for relay)
     */
    public function transcodeStream($sourceMount, $targetMount, $targetFormat, $bitrate = 128)
    {
        // This would typically involve setting up a relay or using ffmpeg to re-encode a stream
        // For simplicity, we'll note that this is a more complex operation.

        // Validate format and bitrate as above
        $supportedFormats = $this->config->get('radio.transcoding.supported_formats');
        if (!in_array($targetFormat, $supportedFormats)) {
            throw new \Exception("Unsupported target format: {$targetFormat}");
        }

        $allowedBitrates = $this->config->get('radio.transcoding.default_bitrates');
        if (!in_array($bitrate, $allowedBitrates)) {
            throw new \Exception("Unsupported bitrate: {$bitrate}");
        }

        // In a real system, we would use ffmpeg to read from the source stream and write to the target
        // For example: ffmpeg -i http://source:port/source_mount -b:a 128k -f mp3 icecast://source:port/target_mount
        // But note: This is a long-running process and would be managed differently.

        // We'll return a command that could be used to start the transcoding process.
        $ffmpegPath = $this->config->get('radio.transcoding.ffmpeg_path');
        $command = "{$ffmpegPath} -re -i http://localhost:{$sourceMount} -b:a {$bitrate}k -f {$targetFormat} icecast://source:hackme@localhost:8001/{$targetMount}";

        // Note: This is a simplified example. In reality, we would need to handle authentication, etc.
        return $command;
    }

    /**
     * Check if transcoding is enabled
     */
    public function isEnabled()
    {
        return $this->config->get('radio.transcoding.enabled');
    }
}