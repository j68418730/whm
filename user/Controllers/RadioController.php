<?php
/**
 * Radio Controller
 * Handles radio streaming requests for the user panel
 */

namespace User\Controllers;

use Core\Controller;
use Services\Stream\StreamManager;
use Services\AutoDJ\AutoDJManager;
use Services\Transcoding\TranscodingManager;
use Core\Auth;
use Core\Request;
use Core\Response;

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
        // Get services from the application container
        $app = \Core\Application::getInstance();
        $this->streamManager = $app->get('radio.stream');
        $this->autodjManager = $app->get('radio.autodj');
        $this->transcodingManager = $app->get('radio.transcoding');
        $this->auth = $app->get('auth');
        $this->request = $app->get('request');
        $this->response = $app->get('response');
    }

    /**
     * Show the radio dashboard
     */
    public function index()
    {
        // Check if user is logged in
        if (!$this->auth->check()) {
            $this->response->redirect('/login');
            exit;
        }

        // Get the current user's streams
        $userId = $this->auth->user()->id;
        $streams = $this->streamManager->getUserStreams($userId);

        // Render the view
        return $this->view('user.radio.index', [
            'streams' => $streams
        ]);
    }

    /**
     * Show the create stream form
     */
    public function create()
    {
        // Check if user is logged in
        if (!$this->auth->check()) {
            $this->response->redirect('/login');
            exit;
        }

        return $this->view('user.radio.create');
    }

    /**
     * Store a new stream
     */
    public function store()
    {
        // Check if user is logged in
        if (!$this->auth->check()) {
            $this->response->redirect('/login');
            exit;
        }

        // Validate input
        $serverType = $this->request->post('server_type') ?? 'icecast';
        $port = $this->request->post('port') ?? null;
        $password = $this->request->post('password') ?? null;

        $userId = $this->auth->user()->id;

        try {
            $stream = $this->streamManager->createStream($userId, $serverType, $port, $password);

            // Redirect to the stream management page
            $this->response->redirect('/radio/stream/'.$stream['id']);
            exit;
        } catch (\Exception $e) {
            // Show error
            return $this->view('user.radio.create', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Show a specific stream
     */
    public function show($streamId)
    {
        // Check if user is logged in
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

        // Get AutoDJ status if enabled
        $autodj = null;
        try {
            $autodj = $this->autodjManager->getByStreamId($streamId);
        } catch (\Exception $e) {
            // AutoDJ not enabled
        }

        // Get transcoding options
        $transcodingOptions = $this->transcodingManager->getOptions();

        return $this->view('user.radio.show', [
            'stream' => $stream,
            'autodj' => $autodj,
            'transcodingOptions' => $transcodingOptions
        ]);
    }

    /**
     * Start a stream
     */
    public function start($streamId)
    {
        // Check if user is logged in
        if (!$this->auth->check()) {
            $this->response->redirect('/login');
            exit;
        }

        $userId = $this->auth->user()->id;
        $this->streamManager->startStream($streamId, $userId);

        $this->response->redirect('/radio/stream/'.$streamId);
        exit;
    }

    /**
     * Stop a stream
     */
    public function stop($streamId)
    {
        // Check if user is logged in
        if (!$this->auth->check()) {
            $this->response->redirect('/login');
            exit;
        }

        $userId = $this->auth->user()->id;
        $this->streamManager->stopStream($streamId, $userId);

        $this->response->redirect('/radio/stream/'.$streamId);
        exit;
    }

    /**
     * Enable AutoDJ for a stream
     */
    public function enableAutodj($streamId)
    {
        // Check if user is logged in
        if (!$this->auth->check()) {
            $this->response->redirect('/login');
            exit;
        }

        $userId = $this->auth->user()->id;
        $this->autodjManager->enableAutodj($streamId, $userId);

        $this->response->redirect('/radio/stream/'.$streamId);
        exit;
    }

    /**
     * Disable AutoDJ for a stream
     */
    public function disableAutodj($streamId)
    {
        // Check if user is logged in
        if (!$this->auth->check()) {
            $this->response->redirect('/login');
            exit;
        }

        $userId = $this->auth->user()->id;
        $this->autodjManager->disableAutodj($streamId, $userId);

        $this->response->redirect('/radio/stream/'.$streamId);
        exit;
    }

    /**
     * Start AutoDJ
     */
    public function startAutodj($autodjId)
    {
        // Check if user is logged in
        if (!$this->auth->check()) {
            $this->response->redirect('/login');
            exit;
        }

        $this->autodjManager->startAutodj($autodjId);

        // Get the stream ID from the AutoDJ record to redirect back
        $autodj = $this->autodjManager->getById($autodjId);
        $streamId = $autodj ? $autodj->stream_id : 0;

        $this->response->redirect('/radio/stream/'.$streamId);
        exit;
    }

    /**
     * Stop AutoDJ
     */
    public function stopAutodj($autodjId)
    {
        // Check if user is logged in
        if (!$this->auth->check()) {
            $this->response->redirect('/login');
            exit;
        }

        $this->autodjManager->stopAutodj($autodjId);

        // Get the stream ID from the AutoDJ record to redirect back
        $autodj = $this->autodjManager->getById($autodjId);
        $streamId = $autodj ? $autodj->stream_id : 0;

        $this->response->redirect('/radio/stream/'.$streamId);
        exit;
    }
}