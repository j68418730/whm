<?php
namespace Core;

class Widget
{
    protected $key = '';
    protected $name = '';
    protected $description = '';
    protected $icon = 'bi-box';
    protected $defaultZone = 'main';
    protected $defaultSort = 0;
    protected $renderCallback = null;
    protected $settings = [];
    protected $height = 1; // grid units (1, 2, 3)

    public function __construct($key, $name, $description = '', $icon = 'bi-box', $renderCallback = null)
    {
        $this->key = $key;
        $this->name = $name;
        $this->description = $description;
        $this->icon = $icon;
        $this->renderCallback = $renderCallback;
    }

    public function getKey() { return $this->key; }
    public function getName() { return $this->name; }
    public function getDescription() { return $this->description; }
    public function getIcon() { return $this->icon; }
    public function getDefaultZone() { return $this->defaultZone; }
    public function getDefaultSort() { return $this->defaultSort; }
    public function getHeight() { return $this->height; }

    public function setHeight($h) { $this->height = $h; return $this; }
    public function setDefaultZone($z) { $this->defaultZone = $z; return $this; }
    public function setDefaultSort($s) { $this->defaultSort = $s; return $this; }
    public function setSettings($s) { $this->settings = $s; return $this; }

    public function render($userWidget = null)
    {
        if ($this->renderCallback && is_callable($this->renderCallback)) {
            return call_user_func($this->renderCallback, $userWidget);
        }
        return '<div class="card"><div class="card-body"><h5>' . htmlspecialchars($this->name) . '</h5><p class="text-muted">' . htmlspecialchars($this->description) . '</p></div></div>';
    }
}
