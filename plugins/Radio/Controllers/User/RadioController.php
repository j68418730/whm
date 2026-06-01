<?php

namespace Plugins\Radio\Controllers\User;

use Core\Controller;
use Plugins\Radio\Services\StreamManager;
use Plugins\Radio\Services\AutoDJManager;
use Plugins\Radio\Services\TranscodingManager;

class RadioController extends Controller
{
    protected $streamManager;
    protected $autodjManager;
    protected $transcodingManager;
    protected $auth;
    protected $request;
    protected $response;

    public function __construct()
    {
        $app = \Core\Application::getInstance();
        $this->streamManager = $app->get('radio.stream');
        $this->autodjManager = $app->get('radio.autodj');
        $this->transcodingManager = $app->get('radio.transcoding');
        $this->auth = $app->get('auth');
        $this->request = $app->get('request');
        $this->response = $app->get('response');
    }

    public function index()
    {
        if (!$this->auth->check()) {
            $this->response->redirect('/login');
            exit;
        }
        $userId = $this->auth->user()->id;
        $streams = $this->streamManager->getUserStreams($userId);
        return $this->view('Plugins.Radio.Views.user.radio.index', ['streams' => $streams]);
    }

    public function create()
    {
        if (!$this->auth->check()) {
            $this->response->redirect('/login');
            exit;
        }
        return $this->view('Plugins.Radio.Views.user.radio.create');
    }

    public function store()
    {
        if (!$this->auth->check()) {
            $this->response->redirect('/login');
            exit;
        }
        $serverType = $this->request->post('server_type') ?? 'icecast';
        $port = $this->request->post('port') ?? null;
        $password = $this->request->post('password') ?? null;
        $userId = $this->auth->user()->id;
        try {
            $stream = $this->streamManager->createStream($userId, $serverType, $port, $password);
            $this->response->redirect('/radio/stream/' . $stream['id']);
            exit;
        } catch (\Exception $e) {
            return $this->view('Plugins.Radio.Views.user.radio.create', [
                'error' => $e->getMessage()
            ]);
        }
    }

    public function show($streamId)
    {
        if (!$this->auth->check()) {
            $this->response->redirect('/login');
            exit;
        }
        $userId = $this->auth->user()->id;
        $stream = $this->streamManager->getStream($streamId, $userId);
        if (!$stream) {
            $this->response->setStatusCode(404);
            $this->response->setContent('404 - Stream not found');
            $this->response->send();
            exit;
        }
        $autodj = null;
        try {
            $autodj = $this->autodjManager->getByStreamId($streamId);
        } catch (\Exception $e) {
        }
        $transcodingOptions = $this->transcodingManager->getOptions();
        return $this->view('Plugins.Radio.Views.user.radio.show', [
            'stream' => $stream,
            'autodj' => $autodj,
            'transcodingOptions' => $transcodingOptions
        ]);
    }

    public function start($streamId)
    {
        if (!$this->auth->check()) {
            $this->response->redirect('/login');
            exit;
        }
        $userId = $this->auth->user()->id;
        $this->streamManager->startStream($streamId, $userId);
        $this->response->redirect('/radio/stream/' . $streamId);
        exit;
    }

    public function stop($streamId)
    {
        if (!$this->auth->check()) {
            $this->response->redirect('/login');
            exit;
        }
        $userId = $this->auth->user()->id;
        $this->streamManager->stopStream($streamId, $userId);
        $this->response->redirect('/radio/stream/' . $streamId);
        exit;
    }

    public function enableAutodj($streamId)
    {
        if (!$this->auth->check()) {
            $this->response->redirect('/login');
            exit;
        }
        $userId = $this->auth->user()->id;
        $this->autodjManager->enableAutodj($streamId, $userId);
        $this->response->redirect('/radio/stream/' . $streamId);
        exit;
    }

    public function disableAutodj($streamId)
    {
        if (!$this->auth->check()) {
            $this->response->redirect('/login');
            exit;
        }
        $userId = $this->auth->user()->id;
        $this->autodjManager->disableAutodj($streamId, $userId);
        $this->response->redirect('/radio/stream/' . $streamId);
        exit;
    }

    public function startAutodj($autodjId)
    {
        if (!$this->auth->check()) {
            $this->response->redirect('/login');
            exit;
        }
        $this->autodjManager->startAutodj($autodjId);
        $autodj = $this->autodjManager->getById($autodjId);
        $streamId = $autodj ? $autodj->stream_id : 0;
        $this->response->redirect('/radio/stream/' . $streamId);
        exit;
    }

    public function stopAutodj($autodjId)
    {
        if (!$this->auth->check()) {
            $this->response->redirect('/login');
            exit;
        }
        $this->autodjManager->stopAutodj($autodjId);
        $autodj = $this->autodjManager->getById($autodjId);
        $streamId = $autodj ? $autodj->stream_id : 0;
        $this->response->redirect('/radio/stream/' . $streamId);
        exit;
    }
}
