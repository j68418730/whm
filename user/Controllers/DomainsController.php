<?php

namespace User\Controllers;

use Core\Controller;

class DomainsController extends Controller
{
    protected $auth, $request, $response, $db, $dns, $hostingUser;

    public function __construct()
    {
        $app = \Core\Application::getInstance();
        $this->auth = $app->get('auth');
        $this->request = $app->get('request');
        $this->response = $app->get('response');
        $this->db = $app->get('db');
        $this->dns = new \Admin\Services\DnsManager();
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
        $subdomains = [];
        foreach ($domains as $d) {
            $records = $this->db->table('dns_records')->where('zone_id', $d->id)->where('type', 'A')->get() ?: [];
            foreach ($records as $r) {
                if ($r->name !== '@') $subdomains[] = (object)['domain' => $d->domain, 'name' => $r->name, 'value' => $r->value, 'record_id' => $r->id, 'zone_id' => $d->id];
            }
        }
        return $this->view('user.domains', ['user' => $u, 'hosting' => $this->hostingUser, 'domains' => $domains, 'subdomains' => $subdomains, 'title' => 'Domains']);
    }

    public function add()
    {
        $u = $this->requireUser();
        if ($_POST) {
            $domain = $this->request->post('domain', '');
            $serverIp = $_SERVER['SERVER_ADDR'] ?? 'planet-hosts.com';
            if ($domain && $this->hostingUser) {
                $zoneId = $this->dns->provisionDomain($domain, $serverIp);
                $_SESSION['success'] = "Domain {$domain} added with full DNS provisioning (SOA, NS, A, MX, SPF, DKIM, DMARC).";
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
        $records = $zone ? $this->dns->getRecords($id) : [];
        return $this->view('user.zone', ['user' => $u, 'hosting' => $this->hostingUser, 'zone' => $zone, 'records' => $records, 'title' => 'DNS Zone']);
    }

    public function addRecord($zoneId)
    {
        $u = $this->requireUser();
        $this->dns->addRecord($zoneId, $this->request->post('name', '@'), $this->request->post('type', 'A'),
            $this->request->post('value', ''), (int)$this->request->post('ttl', 300),
            $this->request->post('priority') ? (int)$this->request->post('priority') : null);
        $_SESSION['success'] = 'Record added.';
        $this->response->redirect('/user/domains/zone/' . $zoneId);
    }

    public function deleteRecord($zoneId, $recordId)
    {
        $u = $this->requireUser();
        $this->dns->deleteRecord($recordId);
        $_SESSION['success'] = 'Record deleted.';
        $this->response->redirect('/user/domains/zone/' . $zoneId);
    }

    public function subdomains()
    {
        $u = $this->requireUser();
        $zones = $this->hostingUser ? ($this->db->table('dns_zones')->where('domain', 'LIKE', '%' . ($this->hostingUser->domain ?? '') . '%')->get() ?: []) : [];
        return $this->view('user.subdomains', ['user' => $u, 'hosting' => $this->hostingUser, 'zones' => $zones, 'title' => 'Subdomains']);
    }

    public function createSubdomain()
    {
        $u = $this->requireUser();
        $subdomain = $this->request->post('subdomain', '');
        $domain = $this->request->post('domain', '');
        if ($subdomain && $domain) {
            $full = $subdomain . '.' . $domain;
            $ip = $_SERVER['SERVER_ADDR'] ?? 'planet-hosts.com';
            $zone = $this->db->table('dns_zones')->where('domain', $domain)->first();
            if ($zone) {
                $this->dns->addRecord($zone->id, $subdomain, 'A', $ip, 300);
                $msg = "Subdomain {$full} created pointing to {$ip}.";
                if (!empty($_POST['create_ftp'])) {
                    $ftpUser = trim($_POST['ftp_username'] ?: $subdomain);
                    $ftpPass = $_POST['ftp_password'] ?? bin2hex(random_bytes(6));
                    $ftpDir = trim($_POST['ftp_dir'] ?: 'public_html/' . $subdomain);
                    $fullUser = $this->hostingUser->username . '_' . $ftpUser;
                    $home = '/home/' . $this->hostingUser->username;
                    $absDir = $home . '/' . ltrim($ftpDir, '/');
                    if (!is_dir($absDir)) @mkdir($absDir, 0755, true);
                    try {
                        $this->db->table('ftp_accounts')->insertGetId([
                            'hosting_user_id' => $this->hostingUser->id,
                            'username' => $fullUser, 'password_hash' => password_hash($ftpPass, PASSWORD_DEFAULT),
                            'directory' => $ftpDir, 'permissions' => 'read_write',
                        ]);
                        @exec("sudo useradd -m -d {$home} -s /bin/bash {$fullUser} 2>/dev/null");
                        @exec("echo '{$ftpPass}' | sudo passwd --stdin {$fullUser} 2>/dev/null");
                        $msg .= " FTP account '{$fullUser}' created (pass: {$ftpPass}).";
                    } catch (\Exception $e) { $msg .= ' FTP creation failed.'; }
                }
                $_SESSION['success'] = $msg;
            } else {
                $_SESSION['error'] = "Domain {$domain} not found in DNS zones.";
            }
        }
        $this->response->redirect('/user/subdomains');
    }

    public function redirects()
    {
        $u = $this->requireUser();
        $uid = $this->hostingUser->id ?? 0;
        $redirects = $uid ? ($this->db->table('dns_records')->where('zone_id', $uid)->where('type', 'REDIRECT')->get() ?: []) : [];
        return $this->view('user.redirects', ['user' => $u, 'hosting' => $this->hostingUser, 'redirects' => $redirects, 'title' => 'Redirects']);
    }

    public function addRedirect()
    {
        $u = $this->requireUser();
        $uid = $this->hostingUser->id ?? 0;
        $this->db->table('dns_records')->insertGetId([
            'zone_id' => $uid, 'name' => $this->request->post('source', ''),
            'type' => 'REDIRECT', 'value' => $this->request->post('destination', ''),
            'priority' => $this->request->post('type', '301') === '301' ? 301 : 302,
        ]);
        $_SESSION['success'] = 'Redirect created.';
        $this->response->redirect('/user/redirects');
    }

    public function deleteRedirect($id)
    {
        $u = $this->requireUser();
        $this->db->table('dns_records')->where('id', $id)->delete();
        $_SESSION['success'] = 'Redirect deleted.';
        $this->response->redirect('/user/redirects');
    }
}

