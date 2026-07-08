const initSqlJs = require('sql.js');
const path = require('path');
const fs = require('fs');
const os = require('os');

const DB_PATH = path.join(os.homedir(), '.planethosts-studio', 'library.db');

let db = null;
let SQL = null;

async function initDb() {
    const dir = path.dirname(DB_PATH);
    if (!fs.existsSync(dir)) fs.mkdirSync(dir, { recursive: true });
    
    SQL = await initSqlJs();
    
    if (fs.existsSync(DB_PATH)) {
        const buffer = fs.readFileSync(DB_PATH);
        db = new SQL.Database(buffer);
    } else {
        db = new SQL.Database();
    }
    
    db.run(`CREATE TABLE IF NOT EXISTS songs (
        id INTEGER PRIMARY KEY,
        path TEXT UNIQUE,
        title TEXT,
        artist TEXT,
        album TEXT,
        genre TEXT,
        year INTEGER,
        bpm REAL,
        track INTEGER,
        duration REAL,
        bitrate INTEGER,
        format TEXT,
        file_size INTEGER,
        file_modified INTEGER,
        date_added INTEGER,
        play_count INTEGER DEFAULT 0,
        last_played INTEGER,
        rating INTEGER DEFAULT 0
    )`);
    
    db.run(`CREATE INDEX IF NOT EXISTS idx_songs_artist ON songs(artist)`);
    db.run(`CREATE INDEX IF NOT EXISTS idx_songs_album ON songs(album)`);
    db.run(`CREATE INDEX IF NOT EXISTS idx_songs_genre ON songs(genre)`);
    db.run(`CREATE INDEX IF NOT EXISTS idx_songs_title ON songs(title)`);
    
    db.run(`CREATE TABLE IF NOT EXISTS playlists (
        id INTEGER PRIMARY KEY,
        name TEXT,
        created INTEGER,
        modified INTEGER
    )`);
    
    db.run(`CREATE TABLE IF NOT EXISTS playlist_songs (
        id INTEGER PRIMARY KEY,
        playlist_id INTEGER,
        song_id INTEGER,
        position INTEGER,
        FOREIGN KEY (playlist_id) REFERENCES playlists(id),
        FOREIGN KEY (song_id) REFERENCES songs(id)
    )`);
    
    db.run(`CREATE TABLE IF NOT EXISTS schedule (
        id INTEGER PRIMARY KEY,
        day_of_week INTEGER,
        start_time TEXT,
        end_time TEXT,
        type TEXT,
        name TEXT,
        playlist_id INTEGER,
        enabled INTEGER DEFAULT 1
    )`);
    
    save();
    return true;
}

function save() {
    if (db) {
        const data = db.export();
        const buffer = Buffer.from(data);
        fs.writeFileSync(DB_PATH, buffer);
    }
}

// ─── SONGS ───
function addSong(meta) {
    try {
        db.run(`INSERT OR REPLACE INTO songs 
            (path, title, artist, album, genre, year, bpm, track, duration, bitrate, format, file_size, file_modified, date_added)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
            meta.path, meta.title || 'Unknown', meta.artist || 'Unknown', 
            meta.album || 'Unknown', meta.genre || '', meta.year || 0,
            meta.bpm || 0, meta.track || 0, meta.duration || 0,
            meta.bitrate || 0, meta.format || '', meta.file_size || 0,
            meta.file_modified || 0, Date.now());
        save();
        return true;
    } catch(e) { return false; }
}

function searchSongs(query, limit = 200) {
    if (!db) return [];
    const q = `%${query}%`;
    const stmt = db.prepare(`SELECT * FROM songs 
        WHERE title LIKE ? OR artist LIKE ? OR album LIKE ? OR genre LIKE ?
        ORDER BY artist, album, track LIMIT ?`);
    stmt.bind([q, q, q, q, limit]);
    const results = [];
    while (stmt.step()) results.push(stmt.getAsObject());
    stmt.free();
    return results;
}

function getAllSongs(limit = 500) {
    if (!db) return [];
    const stmt = db.prepare(`SELECT * FROM songs ORDER BY artist, album, track LIMIT ?`);
    stmt.bind([limit]);
    const results = [];
    while (stmt.step()) results.push(stmt.getAsObject());
    stmt.free();
    return results;
}

function getSongsByArtist(artist) {
    if (!db) return [];
    const stmt = db.prepare(`SELECT * FROM songs WHERE artist = ? ORDER BY album, track`);
    stmt.bind([artist]);
    const results = [];
    while (stmt.step()) results.push(stmt.getAsObject());
    stmt.free();
    return results;
}

function getRecentSongs(limit = 20) {
    if (!db) return [];
    const stmt = db.prepare(`SELECT * FROM songs ORDER BY date_added DESC LIMIT ?`);
    stmt.bind([limit]);
    const results = [];
    while (stmt.step()) results.push(stmt.getAsObject());
    stmt.free();
    return results;
}

function getSongCount() {
    if (!db) return 0;
    const stmt = db.prepare(`SELECT COUNT(*) as count FROM songs`);
    stmt.step();
    const row = stmt.getAsObject();
    stmt.free();
    return row.count;
}

function incrementPlayCount(id) {
    db.run(`UPDATE songs SET play_count = play_count + 1, last_played = ? WHERE id = ?`, Date.now(), id);
    save();
}

function updateRating(id, rating) {
    db.run(`UPDATE songs SET rating = ? WHERE id = ?`, rating, id);
    save();
}

function getStats() {
    if (!db) return { songs: 0, artists: 0, albums: 0, total_duration: 0 };
    const r = db.exec(`SELECT 
        COUNT(*) as songs,
        COUNT(DISTINCT artist) as artists,
        COUNT(DISTINCT album) as albums,
        SUM(duration) as total_duration
        FROM songs`);
    return r.length > 0 ? r[0].values[0] : { songs: 0, artists: 0, albums: 0, total_duration: 0 };
}

function getArtists() {
    if (!db) return [];
    const stmt = db.prepare(`SELECT artist, COUNT(*) as count FROM songs GROUP BY artist ORDER BY artist`);
    const results = [];
    while (stmt.step()) results.push(stmt.getAsObject());
    stmt.free();
    return results;
}

function getAlbums(artist) {
    if (!db) return [];
    const stmt = artist 
        ? db.prepare(`SELECT album, artist, COUNT(*) as count FROM songs WHERE artist = ? GROUP BY album ORDER BY album`)
        : db.prepare(`SELECT album, artist, COUNT(*) as count FROM songs GROUP BY album ORDER BY album`);
    if (artist) stmt.bind([artist]);
    const results = [];
    while (stmt.step()) results.push(stmt.getAsObject());
    stmt.free();
    return results;
}

function getGenres() {
    if (!db) return [];
    const stmt = db.prepare(`SELECT genre, COUNT(*) as count FROM songs WHERE genre != '' GROUP BY genre ORDER BY genre`);
    const results = [];
    while (stmt.step()) results.push(stmt.getAsObject());
    stmt.free();
    return results;
}

function getSong(id) {
    if (!db) return null;
    const stmt = db.prepare(`SELECT * FROM songs WHERE id = ?`);
    stmt.bind([id]);
    if (stmt.step()) { const r = stmt.getAsObject(); stmt.free(); return r; }
    stmt.free();
    return null;
}

// ─── PLAYLISTS ───
function createPlaylist(name) {
    db.run(`INSERT INTO playlists (name, created, modified) VALUES (?, ?, ?)`, name, Date.now(), Date.now());
    save();
    const id = db.exec(`SELECT last_insert_rowid()`)[0].values[0][0];
    return id;
}

function getPlaylists() {
    if (!db) return [];
    const stmt = db.prepare(`SELECT p.*, (SELECT COUNT(*) FROM playlist_songs WHERE playlist_id = p.id) as song_count FROM playlists p ORDER BY p.name`);
    const results = [];
    while (stmt.step()) results.push(stmt.getAsObject());
    stmt.free();
    return results;
}

function addToPlaylist(playlistId, songId) {
    const max = db.exec(`SELECT MAX(position) as m FROM playlist_songs WHERE playlist_id = ${playlistId}`);
    const pos = (max.length > 0 && max[0].values[0][0]) ? max[0].values[0][0] + 1 : 0;
    db.run(`INSERT INTO playlist_songs (playlist_id, song_id, position) VALUES (?, ?, ?)`, playlistId, songId, pos);
    db.run(`UPDATE playlists SET modified = ? WHERE id = ?`, Date.now(), playlistId);
    save();
}

function getPlaylistSongs(playlistId) {
    if (!db) return [];
    const stmt = db.prepare(`SELECT s.*, ps.position FROM playlist_songs ps JOIN songs s ON s.id = ps.song_id WHERE ps.playlist_id = ? ORDER BY ps.position`);
    stmt.bind([playlistId]);
    const results = [];
    while (stmt.step()) results.push(stmt.getAsObject());
    stmt.free();
    return results;
}

function removeFromPlaylist(playlistId, songId) {
    db.run(`DELETE FROM playlist_songs WHERE playlist_id = ? AND song_id = ?`, playlistId, songId);
    db.run(`UPDATE playlists SET modified = ? WHERE id = ?`, Date.now(), playlistId);
    save();
}

function deletePlaylist(id) {
    db.run(`DELETE FROM playlist_songs WHERE playlist_id = ?`, id);
    db.run(`DELETE FROM playlists WHERE id = ?`, id);
    save();
}

// ─── SCHEDULE ───
function addScheduleEvent(event) {
    db.run(`INSERT INTO schedule (day_of_week, start_time, end_time, type, name, playlist_id, enabled) VALUES (?, ?, ?, ?, ?, ?, ?)`,
        event.day_of_week, event.start_time, event.end_time, event.type, event.name, event.playlist_id || null, 1);
    save();
    const id = db.exec(`SELECT last_insert_rowid()`)[0].values[0][0];
    return id;
}

function getSchedule() {
    if (!db) return [];
    const stmt = db.prepare(`SELECT * FROM schedule ORDER BY day_of_week, start_time`);
    const results = [];
    while (stmt.step()) results.push(stmt.getAsObject());
    stmt.free();
    return results;
}

function deleteScheduleEvent(id) {
    db.run(`DELETE FROM schedule WHERE id = ?`, id);
    save();
}

// ─── CLOSE ───
function close() {
    if (db) { save(); db.close(); db = null; }
}

module.exports = {
    initDb, save, close,
    addSong, searchSongs, getAllSongs, getSongsByArtist, getRecentSongs, getSongCount, getSong,
    incrementPlayCount, updateRating, getStats,
    getArtists, getAlbums, getGenres,
    createPlaylist, getPlaylists, addToPlaylist, getPlaylistSongs, removeFromPlaylist, deletePlaylist,
    addScheduleEvent, getSchedule, deleteScheduleEvent
};