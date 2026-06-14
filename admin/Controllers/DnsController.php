<?php

namespace Admin\Controllers;

use Core\Controller;
use Admin\Services\DnsManager;

class DnsController extends Controller
{
    protected $auth;
    protected $request;
    protected $response;
    protected $dns;

    public function __construct()
    {
        $app = \Core\Application::getInstance();
        $this->auth = $app->get('auth');
        $this->request = $app->get('request');
        $this->response = $app->get('response');
        $this->dns = new DnsManager();
    }

    public function index()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $user = $this->auth->user();
        $zones = $this->dns->getZones();
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);
        return $this->view('admin.dns.index', [
            'user' => $user,
            'zones' => $zones,
            'dnsStats' => ['total_zones' => count($zones), 'active_zones' => count($zones), 'total_records' => 0],
            'theme_settings' => $theme_settings
        ]);
    }

    public function createZone()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $domain = $this->request->post('domain', '');
        if ($domain) {
            $this->dns->createZone($domain);
            $_SESSION['success_message'] = "Zone '{$domain}' created.";
        }
        $this->response->redirect('/admin/dns');
        exit;
    }

    public function editZone($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $user = $this->auth->user();
        $zone = $this->dns->getZone($id);
        if (!$zone) { $this->response->redirect('/admin/dns'); exit; }
        $records = $this->dns->getRecords($id);
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);
        return $this->view('admin.dns.edit', [
            'user' => $user,
            'zone' => $zone,
            'records' => $records,
            'theme_settings' => $theme_settings
        ]);
    }

    public function deleteZone($id)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $this->dns->deleteZone($id);
        $_SESSION['success_message'] = 'Zone deleted.';
        $this->response->redirect('/admin/dns');
        exit;
    }

    public function addRecord($zoneId)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $this->dns->addRecord($zoneId, $this->request->post('name', ''), $this->request->post('type', 'A'), $this->request->post('value', ''), (int)$this->request->post('ttl', 300), $this->request->post('priority') ? (int)$this->request->post('priority') : null);
        $_SESSION['success_message'] = 'Record added.';
        $this->response->redirect('/admin/dns/edit/' . $zoneId);
        exit;
    }

    public function deleteRecord($zoneId, $recordId)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $this->dns->deleteRecord($recordId);
        $_SESSION['success_message'] = 'Record deleted.';
        $this->response->redirect('/admin/dns/edit/' . $zoneId);
        exit;
    }

    public function nameservers()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $user = $this->auth->user();
        $ns = $this->dns->getNameservers();
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);
        return $this->view('admin.dns.nameservers', ['user' => $user, 'nameservers' => $ns, 'theme_settings' => $theme_settings]);
    }

    public function saveNameservers()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $this->dns->setNameservers($this->request->post('ns1', ''), $this->request->post('ns2', ''), $this->request->post('ns3', ''), $this->request->post('ns4', ''));
        $_SESSION['success_message'] = 'Nameservers updated.';
        $this->response->redirect('/admin/dns/nameservers');
        exit;
    }
}
