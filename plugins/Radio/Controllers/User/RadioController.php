<?php

namespace Plugins\Radio\Controllers\User;

use Core\Controller;

class RadioController extends Controller
{
    protected $db, $request, $response, $auth;

    public function __construct()
    {
        $app = \Core\Application::getInstance();
        $this->db = $app->get('db');
        $this->request = $app->get('request');
        $this->response = $app->get('response');
        $this->auth = $app->get('auth');
    }

    // POST /connector/station/{slug}/requests
    // Body: { artist, title, guest_name, message }
    public function connectorRequest($slug)
    {
        $this->response->header('Content-Type: application/json');
        
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        
        $artist = trim($data['artist'] ?? '');
        $title = trim($data['title'] ?? '');
        $guestName = trim($data['guest_name'] ?? '');
        $message = trim($data['message'] ?? '');
        
        if (!$title) {
            $this->response->json(['success' => false, 'error' => 'Song title required']);
            return;
        }
        
        $pdo = $this->db->pdo();
        
        // Resolve station by slug
        $st = $pdo->prepare("SELECT id, name FROM streaming_stations WHERE LOWER(name) LIKE ? ORDER BY id LIMIT 1");
        $st->execute(["%{$slug}%"]);
        $station = $st->fetch(\PDO::FETCH_OBJ);
        
        if (!$station) {
            // Try exact match by numeric slug
            if (is_numeric($slug)) {
                $st = $pdo->prepare("SELECT id, name FROM streaming_stations WHERE id = ?");
                $st->execute([(int)$slug]);
                $station = $st->fetch(\PDO::FETCH_OBJ);
            }
        }
        
        if (!$station) {
            $this->response->json(['success' => false, 'error' => 'Station not found']);
            return;
        }
        
        $sessionId = session_id() ?? bin2hex(random_bytes(16));
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        $pdo->prepare("INSERT INTO radio_requests (stream_id, artist, title, guest_name, message, ip, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?)")
            ->execute([$station->id, $artist, $title, $guestName, $message, $ip, $ua]);
        
        $this->response->json(['success' => true, 'id' => $pdo->lastInsertId()]);
    }

    // POST /radio/public/request (legacy)
    public function publicRequest()
    {
        $this->response->header('Content-Type: application/json');
        
        $artist = trim($this->request->post('artist', ''));
        $title = trim($this->request->post('title', ''));
        $guestName = trim($this->request->post('guest_name', ''));
        $message = trim($this->request->post('message', ''));
        $streamId = (int)$this->request->post('stream_id', 0);
        
        if (!$title) {
            $this->response->json(['success' => false, 'error' => 'Song title required']);
            return;
        }
        
        if (!$streamId) {
            $this->response->json(['success' => false, 'error' => 'Station ID required']);
            return;
        }
        
        $pdo = $this->db->pdo();
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        $pdo->prepare("INSERT INTO radio_requests (stream_id, artist, title, guest_name, message, ip, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?)")
            ->execute([$streamId, $artist, $title, $guestName, $message, $ip, $ua]);
        
        $this->response->json(['success' => true, 'id' => $pdo->lastInsertId()]);
    }
}