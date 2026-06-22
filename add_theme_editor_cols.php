<?php
$p = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');
$p->exec("ALTER TABLE chatbox_tenants ADD COLUMN IF NOT EXISTS custom_css TEXT DEFAULT NULL");
$p->exec("ALTER TABLE chatbox_tenants ADD COLUMN IF NOT EXISTS widget_border_color VARCHAR(50) DEFAULT 'rgba(255,255,255,.1)'");
$p->exec("ALTER TABLE chatbox_tenants ADD COLUMN IF NOT EXISTS widget_glow_color VARCHAR(50) DEFAULT '#008cff'");
$p->exec("ALTER TABLE chatbox_tenants ADD COLUMN IF NOT EXISTS widget_avatar_shape VARCHAR(20) DEFAULT 'circle'");
echo "OK\n";
