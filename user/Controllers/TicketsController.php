<?php

namespace User\Controllers;

use Core\Controller;

class TicketsController extends Controller
{
    protected $auth;
    protected $request;
    protected $response;
    protected $db;
    protected $hostingUser;

    public function __construct()
    {
        $app = \Core\Application::getInstance();
        $this->auth = $app->get('auth');
        $this->request = $app->get('request');
        $this->response = $app->get('response');
        $this->db = $app->get('db');
    }

    protected function loadUser()
    {
        if (!$this->auth->check()) { $this->response->redirect('/?login'); exit; }
        $user = $this->auth->user();
        $this->hostingUser = $this->db->table('hosting_users')->where('email', $user->email)->first();
        return $user;
    }

    public function index()
    {
        $u = $this->loadUser();
        $uid = $this->hostingUser->id ?? 0;
        $tickets = $uid ? ($this->db->table('tickets')->where('user_id', $uid)->get() ?: []) : [];
        return $this->view('user.tickets', ['user' => $u, 'hosting' => $this->hostingUser, 'title' => 'Support Tickets', 'tickets' => $tickets]);
    }

    public function create()
    {
        $u = $this->loadUser();
        $uid = $this->hostingUser->id ?? 0;
        if ($uid) {
            $this->db->table('tickets')->insertGetId([
                'user_id' => $uid,
                'subject' => $this->request->post('subject', ''),
                'department' => $this->request->post('department', 'Technical'),
                'message' => $this->request->post('message', ''),
                'status' => 'open',
            ]);
        }
        $_SESSION['success'] = 'Ticket submitted.';
        $this->response->redirect('/user/tickets');
    }

    public function show($id)
    {
        $u = $this->loadUser();
        $uid = $this->hostingUser->id ?? 0;
        $ticket = $this->db->table('tickets')->where('id', $id)->where('user_id', $uid)->first();
        if (!$ticket) { $this->response->redirect('/user/tickets'); exit; }
        $replies = $this->db->table('ticket_replies')->where('ticket_id', $id)->get() ?: [];
        return $this->view('user.ticket_view', ['user' => $u, 'hosting' => $this->hostingUser, 'title' => 'Ticket #' . $id, 'ticket' => $ticket, 'replies' => $replies]);
    }

    public function reply($id)
    {
        $u = $this->loadUser();
        $uid = $this->hostingUser->id ?? 0;
        $ticket = $this->db->table('tickets')->where('id', $id)->where('user_id', $uid)->first();
        if ($ticket) {
            $this->db->table('ticket_replies')->insertGetId([
                'ticket_id' => $id,
                'user_id' => $uid,
                'message' => $this->request->post('message', ''),
            ]);
            $this->db->table('tickets')->where('id', $id)->update(['status' => 'answered']);
        }
        $_SESSION['success'] = 'Reply posted.';
        $this->response->redirect('/user/tickets/' . $id);
    }

    public function close($id)
    {
        $u = $this->loadUser();
        $uid = $this->hostingUser->id ?? 0;
        $ticket = $this->db->table('tickets')->where('id', $id)->where('user_id', $uid)->first();
        if ($ticket) {
            $this->db->table('tickets')->where('id', $id)->update(['status' => 'closed']);
        }
        $this->response->redirect('/user/tickets');
    }
}
