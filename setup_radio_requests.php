<?php
$p = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');
$q = $p->query("SHOW TABLES LIKE 'radio_%'");
echo "Radio tables:\n";
foreach ($q as $r) echo "  " . $r[0] . "\n";

// Check if requests table exists
$q2 = $p->query("SHOW TABLES LIKE 'radio_requests'");
if ($q2->rowCount() == 0) {
    $p->exec("CREATE TABLE IF NOT EXISTS radio_requests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        stream_id INT NOT NULL,
        user_id INT DEFAULT NULL,
        guest_name VARCHAR(100) DEFAULT NULL,
        artist VARCHAR(255) NOT NULL,
        title VARCHAR(255) NOT NULL,
        message TEXT DEFAULT NULL,
        status ENUM('pending','played','removed') DEFAULT 'pending',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (stream_id) REFERENCES radio_streams(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "radio_requests table created\n";
}

// Check if current_dj column exists
$q3 = $p->query("SHOW COLUMNS FROM radio_streams LIKE 'current_dj'");
if ($q3->rowCount() == 0) {
    $p->exec("ALTER TABLE radio_streams ADD COLUMN current_dj varchar(100) DEFAULT NULL");
    $p->exec("ALTER TABLE radio_streams ADD COLUMN last_song_title varchar(255) DEFAULT NULL");
    $p->exec("ALTER TABLE radio_streams ADD COLUMN last_song_artist varchar(255) DEFAULT NULL");
    $p->exec("ALTER TABLE radio_streams ADD COLUMN autodj_active tinyint(1) DEFAULT 0");
    echo "Stream columns added\n";
}

// Create played_songs history table
$p->exec("CREATE TABLE IF NOT EXISTS radio_played_songs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    stream_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    artist VARCHAR(255) DEFAULT NULL,
    dj_name VARCHAR(100) DEFAULT NULL,
    is_autodj TINYINT(1) DEFAULT 0,
    played_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (stream_id) REFERENCES radio_streams(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
echo "radio_played_songs table ready\n";
