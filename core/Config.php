<?php
/**
 * Configuration Class
 * Simple configuration loader with dot-notation access
 */

namespace Core;

class Config
{
    protected $data = [];

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    public function get($key, $default = null)
    {
        if (is_null($key)) {
            return $this->data;
        }

        if (isset($this->data[$key])) {
            return $this->data[$key];
        }

        // Support dot-notation for nested arrays
        if (str_contains($key, '.')) {
            $segments = explode('.', $key);
            $data = $this->data;

            foreach ($segments as $segment) {
                if (is_array($data) && isset($data[$segment])) {
                    $data = $data[$segment];
                } else {
                    return $default;
                }
            }

            return $data;
        }

        return $default;
    }

    public function set($key, $value)
    {
        if (str_contains($key, '.')) {
            $segments = explode('.', $key);
            $data = &$this->data;

            foreach ($segments as $i => $segment) {
                if ($i === count($segments) - 1) {
                    $data[$segment] = $value;
                } else {
                    if (!isset($data[$segment]) || !is_array($data[$segment])) {
                        $data[$segment] = [];
                    }
                    $data = &$data[$segment];
                }
            }
        } else {
            $this->data[$key] = $value;
        }
    }

    public function all()
    {
        return $this->data;
    }
}