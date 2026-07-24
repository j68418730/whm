CREATE TABLE IF NOT EXISTS radio_global_playlists (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    description TEXT,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS radio_global_playlist_items (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    playlist_id INT NOT NULL,
    file_path   VARCHAR(255) NOT NULL,
    title       VARCHAR(255),
    artist      VARCHAR(100),
    album       VARCHAR(100),
    duration    INT,
    file_size   BIGINT,
    added_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (playlist_id) REFERENCES radio_global_playlists(id) ON DELETE CASCADE,
    INDEX idx_playlist_id (playlist_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
