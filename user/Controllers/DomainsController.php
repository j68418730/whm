<?php

namespace User\Controllers;

use Core\Controller;

class DomainsController extends Controller
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

    protected function requireUser()
    {
        if (!$this->auth->check()) { $this->response->redirect('/?login'); exit; }
        $user = $this->auth->user();
        $this->hostingUser = $this->db->table('hosting_users')->where('email', $user->email)->first();
        return $user;
    }

    public function index()
    {
        $u = $this->requireUser();
        $domains = $this->hostingUser ? ($this->db->table('dns_zones')->where('domain', 'LIKE', '%' . ($this->hostingUser->domain ?? '') . '%')->get() ?: []) : [];
        return $this->view('user.domains', ['user' => $u, 'hosting' => $this->hostingUser, 'domains' => $domains, 'title' => 'Domains']);
    }

    public function add()
    {
        $u = $this->requireUser();
        if ($_POST) {
            $domain = $this->request->post('domain', '');
            $serverIp = $_SERVER['SERVER_ADDR'] ?? '45.61.59.55';
            if ($domain && $this->hostingUser) {
                $this->db->table('dns_zones')->insertGetId([
                    'domain' => $domain, 'ns1' => 'ns1.planet-hosts.com', 'ns2' => 'ns2.planet-hosts.com',
                    'admin_email' => 'admin@' . $domain, 'serial' => date('Ymd') . '01', 'ttl' => 300,
                ]);
                $zone = $this->db->table('dns_zones')->where('domain', $domain)->first();
                if ($zone) {
                    $this->db->table('dns_records')->insertGetId(['zone_id' => $zone->id, 'name' => '@', 'type' => 'A', 'value' => $serverIp, 'ttl' => 300]);
                    $this->db->table('dns_records')->insertGetId(['zone_id' => $zone->id, 'name' => 'www', 'type' => 'CNAME', 'value' => $domain, 'ttl' => 300]);
                    $this->db->table('dns_records')->insertGetId(['zone_id' => $zone->id, 'name' => '@', 'type' => 'NS', 'value' => 'ns1.planet-hosts.com', 'ttl' => 300]);
                    $this->db->table('dns_records')->insertGetId(['zone_id' => $zone->id, 'name' => '@', 'type' => 'NS', 'value' => 'ns2.planet-hosts.com', 'ttl' => 300]);
                }
                $_SESSION['success'] = "Domain {$domain} added.";
            }
            $this->response->redirect('/user/domains');
            exit;
        }
        return $this->view('user.domains', ['user' => $u, 'hosting' => $this->hostingUser, 'domains' => [], 'title' => 'Add Domain']);
    }

    public function zone($id)
    {
        $u = $this->requireUser();
        $zone = $this->db->table('dns_zones')->where('id', $id)->first();
        $records = $zone ? ($this->db->table('dns_records')->where('zone_id', $id)->get() ?: []) : [];
        return $this->view('user.zone', ['user' => $u, 'hosting' => $this->hostingUser, 'zone' => $zone, 'records' => $records, 'title' => 'DNS Zone']);
    }

    public function addRecord($zoneId)
    {
        $u = $this->requireUser();
        $this->db->table('dns_records')->insertGetId([
            'zone_id' => $zoneId, 'name' => $this->request->post('name', '@'),
            'type' => $this->request->post('type', 'A'), 'value' => $this->request->post('value', ''),
            'ttl' => (int)$this->request->post('ttl', 300), 'priority' => $this->request->post('priority') ? (int)$this->request->post('priority') : null,
        ]);
        $_SESSION['success'] = 'Record added.';
        $this->response->redirect('/user/domains/zone/' . $zoneId);
        exit;
    }

    public function deleteRecord($zoneId, $recordId)
    {
        $u = $this->requireUser();
        $this->db->table('dns_records')->where('id', $recordId)->delete();
        $_SESSION['success'] = 'Record deleted.';
        $this->response->redirect('/user/domains/zone/' . $zoneId);
        exit;
    }

    public function subdomains()
    {
        $u = $this->requireUser();
        return $this->view('user.subdomains', ['user' => $u, 'hosting' => $this->hostingUser, 'title' => 'Subdomains']);
    }

    public function redirects()
    {
        $u = $this->requireUser();
        return $this->view('user.redirects', ['user' => $u, 'hosting' => $this->hostingUser, 'title' => 'Redirects']);
    }
}
