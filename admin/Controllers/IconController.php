<?php

namespace Admin\Controllers;

use Core\Controller;

class IconController extends Controller
{
    protected $auth;
    protected $response;

    public function __construct()
    {
        $app = \Core\Application::getInstance();
        $this->auth = $app->get('auth');
        $this->response = $app->get('response');
    }

    public function generate()
    {
        $prompt = $_GET['text'] ?? 'icon';
        $useAI = isset($_GET['ai']);

        if ($useAI) {
            return $this->generateAI($prompt);
        }

        return $this->generateGD($prompt);
    }

    private function generateGD($prompt)
    {
        $size = (int)($_GET['size'] ?? 128);
        $bg = $this->hexToRgb($_GET['bg'] ?? '#0A84FF');
        $fg = $this->hexToRgb($_GET['fg'] ?? '#FFFFFF');

        $img = imagecreatetruecolor($size, $size);
        $bgColor = imagecolorallocate($img, $bg[0], $bg[1], $bg[2]);
        $fgColor = imagecolorallocate($img, $fg[0], $fg[1], $fg[2]);

        imagefilledrectangle($img, 0, 0, $size, $size, $bgColor);

        $words = explode(' ', $prompt);
        $initials = '';
        foreach ($words as $w) {
            if (!empty(trim($w))) $initials .= strtoupper(trim($w)[0]);
        }
        $initials = substr($initials, 0, 3);

        $fontSize = $size / (strlen($initials) > 1 ? 2.5 : 2);
        $fontFile = '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf';
        if (!is_file($fontFile)) {
            $x = ($size / 2) - (imagefontwidth(5) * strlen($initials) * ($fontSize / 12) / 2);
            $y = ($size / 2) - (imagefontheight(5) * ($fontSize / 12) / 2);
            imagestring($img, 5, max(0, $x), max(0, $y), $initials, $fgColor);
        } else {
            $bbox = imagettfbbox($fontSize, 0, $fontFile, $initials);
            $x = ($size - ($bbox[2] - $bbox[0])) / 2;
            $y = ($size - ($bbox[1] - $bbox[7])) / 2;
            imagettftext($img, $fontSize, 0, max(0, $x), max(0, $y), $fgColor, $fontFile, $initials);
        }

        header('Content-Type: image/png');
        imagepng($img);
        imagedestroy($img);
        exit;
    }

    private function generateAI($prompt)
    {
        $apiKey = getenv('OPENAI_API_KEY') ?: '';
        $style = $_GET['style'] ?? 'flat icon design, simple, colorful, transparent background, minimalist, vector style';

        $data = [
            'model' => 'dall-e-3',
            'prompt' => "Create a simple flat icon representing: {$prompt}. Style: {$style}. Solid background, centered, professional, 256x256.",
            'n' => 1,
            'size' => '256x256',
        ];

        $ch = curl_init('https://api.openai.com/v1/images/generations');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey,
            ],
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            $error = json_decode($response, true);
            $msg = $error['error']['message'] ?? 'API error';
            // Fallback to GD generation
            return $this->generateGD($prompt);
        }

        $result = json_decode($response, true);
        $imageUrl = $result['data'][0]['url'] ?? '';

        if ($imageUrl) {
            header('Location: ' . $imageUrl);
            exit;
        }

        return $this->generateGD($prompt);
    }

    private function hexToRgb($hex)
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        return [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
    }
}
