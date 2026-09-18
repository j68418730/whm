<?php

namespace Admin\Controllers;

use Core\Controller;
use Services\TerminalSessionService;

/**
 * Planet Hosts Terminal — real PTY sessions over SSE.
 * ROOT = unrestricted server shell; user/reseller = their own Linux account
 * (OS-enforced isolation). See services/TerminalSessionService.php.
 */
class TerminalController extends Controller
{
    protected $auth;
    protected $request;
    protected $response;
    protected $term;

    public function __construct()
    {
        $app = \Core\Application::getInstance();
        $this->auth = $app->get('auth');
        $this->request = $app->get('request');
        $this->response = $app->get('response');
        $this->term = new TerminalSessionService();
    }

    public function index()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $user = $this->auth->user();
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);
        return $this->view('admin.terminal.index', [
            'user' => $user,
            'theme_settings' => $theme_settings,
            'title' => 'Terminal',
            'hostname' => gethostname(),
        ]);
    }

    /** POST /admin/terminal/session — create a ROOT session. */
    public function create()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->json(['error' => 'Unauthorized']); $this->response->send(); exit; }
        $user = $this->auth->user();
        $cols = (int)$this->request->post('cols', 80);
        $rows = (int)$this->request->post('rows', 24);
        try {
            $s = $this->term->create('root', $user, 'root@' . gethostname(), $cols, $rows);
            $this->response->json(['ok' => true, 'session' => $s])->send();
        } catch (\Exception $e) {
            $this->response->json(['ok' => false, 'error' => $e->getMessage()])->send();
        }
        exit;
    }

    /** GET /admin/terminal/stream/{sid} — SSE output stream. */
    public function stream($sid)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->setContent('event: error\ndata: Unauthorized\n\n'); $this->response->send(); exit; }
        $sid = preg_replace('/[^a-f0-9]/', '', (string)$sid);
        $session = $this->term->session($sid);
        if (!$session) { header('Content-Type: text/event-stream'); echo "event: error\ndata: Session not found\n\n"; exit; }

        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('X-Accel-Buffering: no');
        header('Connection: keep-alive');

        $offset = (int)$this->request->get('offset', 0);
        $lastPing = time();
        $lastEof = 0;
        while (true) {
            if (connection_aborted()) break;
            $res = $this->term->outputSince($sid, $offset);
            if ($res['data'] !== '') {
                // base64-encode to survive any byte (ANSI, control chars, UTF-8)
                echo "data: " . base64_encode($res['data']) . "\n\n";
                @ob_flush(); @flush();
                $offset = $res['offset'];
                $lastPing = time();
                $lastEof = 0;
            }
            if ($res['eof']) {
                $lastEof++;
                if ($lastEof >= 3) { echo "event: end\ndata: session\n\n"; @ob_flush(); @flush(); break; }
            }
            // Heartbeat every 15s to keep the connection alive
            if (time() - $lastPing > 15) {
                echo ": ping\n\n";
                @ob_flush(); @flush();
                $lastPing = time();
            }
            usleep(150000);
        }
        exit;
    }

    /** POST /admin/terminal/input/{sid} — raw bytes (base64). */
    public function input($sid)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->json(['error' => 'Unauthorized']); $this->response->send(); exit; }
        $sid = preg_replace('/[^a-f0-9]/', '', (string)$sid);
        $raw = $this->request->post('data', '');
        $bytes = base64_decode($raw, true);
        if ($bytes === false) $bytes = '';
        $ok = $this->term->input($sid, $bytes);
        $this->response->json(['ok' => $ok])->send();
        exit;
    }

    /** POST /admin/terminal/resize/{sid} */
    public function resize($sid)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->json(['error' => 'Unauthorized']); $this->response->send(); exit; }
        $sid = preg_replace('/[^a-f0-9]/', '', (string)$sid);
        $cols = (int)$this->request->post('cols', 80);
        $rows = (int)$this->request->post('rows', 24);
        $ok = $this->term->resize($sid, $cols, $rows);
        $this->response->json(['ok' => $ok])->send();
        exit;
    }

    /** POST /admin/terminal/kill/{sid} */
    public function kill($sid)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->json(['error' => 'Unauthorized']); $this->response->send(); exit; }
        $sid = preg_replace('/[^a-f0-9]/', '', (string)$sid);
        $ok = $this->term->kill($sid);
        $this->response->json(['ok' => $ok])->send();
        exit;
    }

    /** GET /admin/terminal/sessions */
    public function list()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->json(['error' => 'Unauthorized']); $this->response->send(); exit; }
        $sessions = $this->term->sessions();
        $this->response->json(['ok' => true, 'sessions' => $sessions])->send();
        exit;
    }

    /** Legacy exec endpoint kept for safety/back-compat but now rejected. */
    public function exec()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->json(['error' => 'Unauthorized']); $this->response->send(); exit; }
        $this->response->json(['error' => 'Direct exec is disabled. Use a real Terminal session (/admin/terminal).'], 400)->send();
        exit;
    }
}