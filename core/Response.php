<?php
/**
 * Response Class
 * Simplified HTTP response wrapper
 */

namespace Core;

class Response
{
    protected $headers = [];
    protected $content = '';
    protected $statusCode = 200;

    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    public function setStatusCode($code)
    {
        $this->statusCode = $code;
        return $this;
    }

    public function setHeader($key, $value)
    {
        $this->headers[$key] = $value;
        return $this;
    }

    public function send()
    {
        // Send headers
        foreach ($this->headers as $key => $value) {
            header("{$key}: {$value}");
        }

        // Set status code
        http_response_code($this->statusCode);

        // Send content
        echo $this->content;
    }

    public function redirect($url)
    {
        header("Location: {$url}");
        exit;
    }

    public function json($data, $statusCode = 200)
    {
        $this->setHeader('Content-Type', 'application/json');
        $this->setContent(json_encode($data));
        $this->statusCode = $statusCode;
        return $this;
    }
}