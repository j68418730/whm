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
            $records = $this->db->table('dns_records')->where('zone_id', $d->id)->where('type', 'A')->where('is_user_subdomain', 1)->get() ?: [];
            foreach ($records as $r) {
                $subdomains[] = (object)['domain' => $d->domain, 'name' => $r->name, 'value' => $r->value, 'record_id' => $r->id, 'zone_id' => $d->id];
            }
        }
        return $this->view('user.domains', ['user' => $u, 'hosting' => $this->hostingUser, 'domains' => $domains, 'subdomains' => $subdomains, 'title' => 'Domains']);
    }

public function buy()
    {
        $u = $this->requireUser();
        return $this->view('user.domain_buy', ['user' => $u, 'hosting' => $this->hostingUser, 'title' => 'Buy Domain']);
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
        return $this->view('user.domains', ['user' => $u, 'hosting' => $this->hostingUser, 'domains' => [], 'subdomains' => [], 'title' => 'Add Domain']);
    }

    public function zoneRecords($id)
    {
        $u = $this->requireUser();
        $zone = $this->db->table('dns_zones')->where('id', $id)->first();
        $records = $zone ? $this->dns->getRecords($id) : [];
        echo json_encode(['success' => true, 'records' => $records]);
        exit;
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
        $subdomainRecords = [];
        foreach ($zones as $z) {
            $records = $this->db->table('dns_records')->where('zone_id', $z->id)->where('type', 'A')->where('is_user_subdomain', 1)->get() ?: [];
            foreach ($records as $r) $subdomainRecords[] = $r;
        }
        return $this->view('user.subdomains', ['user' => $u, 'hosting' => $this->hostingUser, 'zones' => $zones, 'subdomainRecords' => $subdomainRecords, 'title' => 'Subdomains']);
    }

    public function createSubdomain()
    {
        $u = $this->requireUser();
        $subdomain = $this->request->post('subdomain', '');
        $domain = $this->request->post('domain', '');
        if ($subdomain && $domain) {
            $full = $subdomain . '.' . $domain;
            $ip = $_SERVER['SERVER_ADDR'] ?? 'planet-hosts.com';
            // Resolve the DNS zone: try exact domain first, then strip subdomain prefixes
            // until we find a managed zone (e.g. test.planet-hosts.com -> planet-hosts.com).
            $zone = null;
            $recordName = $subdomain;
            $zoneDomain = $domain;
            $parts = explode('.', $domain);
            while (count($parts) > 1) {
                $cand = implode('.', $parts);
                $z = $this->db->table('dns_zones')->where('domain', $cand)->first();
                if ($z) { $zone = $z; break; }
                array_shift($parts);
            }
            if ($zone && $zone->domain !== $domain) {
                // We found a parent zone; the record name must be the full sub-subdomain path
                $recordName = str_replace('.' . $zone->domain, '', $full);
            }
            if ($zone) {
                $recordId = $this->dns->addRecord($zone->id, $recordName, 'A', $ip, 300);
                if ($recordId) $this->db->table('dns_records')->where('id', $recordId)->update(['is_user_subdomain' => 1]);
                // Ensure the wildcard A record exists on the parent zone so all subdomains resolve
                $this->dns->addRecord($zone->id, '*', 'A', $ip, 300);
                $msg = "Subdomain {$full} created pointing to {$ip}.";
                $domainOwner = $this->db->table('hosting_users')->where('domain', $domain)->first();
                $ownerUser = $domainOwner ? $domainOwner->username : $this->hostingUser->username;
                $home = '/home/' . $ownerUser;
                $docRoot = $home . '/public_html/' . $subdomain;
                @exec("sudo mkdir -p {$docRoot} && sudo chown -R {$this->hostingUser->username}:{$this->hostingUser->username} {$docRoot} 2>/dev/null");
                $vhostCfg = "<VirtualHost *:80>\n    ServerName {$full}\n    DocumentRoot {$docRoot}\n    <Directory {$docRoot}>\n        Options Indexes FollowSymLinks\n        AllowOverride All\n        Require all granted\n    </Directory>\n    ErrorLog /var/log/apache2/{$full}_error.log\n    CustomLog /var/log/apache2/{$full}_access.log combined\n</VirtualHost>\n";
                $tmpDir = '/var/www/radiohosting/storage/dns';
                if (!is_dir($tmpDir)) @mkdir($tmpDir, 0775, true);
                $tmpFile = $tmpDir . '/vhost_' . $full . '.conf';
                file_put_contents($tmpFile, $vhostCfg);
                @exec("sudo cp {$tmpFile} /etc/apache2/sites-available/{$full}.conf && sudo a2ensite {$full}.conf 2>/dev/null");
                @unlink($tmpFile);

                // Auto-issue SSL for the subdomain (webroot challenge) then create SSL vhost
                $certDir = '/etc/letsencrypt/live/' . $full;
                $certFile = $certDir . '/fullchain.pem';
                $keyFile = $certDir . '/privkey.pem';
                if (!file_exists($certFile)) {
                    // Ensure webroot challenge dir exists, then run certbot
                    @exec("sudo mkdir -p {$docRoot}/.well-known/acme-challenge && sudo chown -R www-data:www-data {$docRoot} 2>/dev/null");
                    $certCmd = "sudo certbot certonly --webroot -w " . escapeshellarg($docRoot)
                        . " -d " . escapeshellarg($full) . " --non-interactive --agree-tos "
                        . " --email support@planet-hosts.com --expand 2>&1";
                    @exec($certCmd, $certOut, $certCode);
                }
                if (!file_exists($certFile)) {
                    // Fall back to the parent domain's cert if issuance failed
                    $certFile = '/etc/letsencrypt/live/' . $domain . '/fullchain.pem';
                    $keyFile = '/etc/letsencrypt/live/' . $domain . '/privkey.pem';
                }
                if (file_exists($certFile)) {
                    $sslCfg = "<IfModule mod_ssl.c>\n<VirtualHost *:443>\n    ServerName {$full}\n    DocumentRoot {$docRoot}\n    <Directory {$docRoot}>\n        Options Indexes FollowSymLinks\n        AllowOverride All\n        Require all granted\n    </Directory>\n    ErrorLog /var/log/apache2/{$full}_error.log\n    CustomLog /var/log/apache2/{$full}_access.log combined\n    SSLEngine on\n    SSLCertificateFile {$certFile}\n    SSLCertificateKeyFile {$keyFile}\n</VirtualHost>\n</IfModule>\n";
                    $sslFile = $tmpDir . '/vhost_ssl_' . $full . '.conf';
                    file_put_contents($sslFile, $sslCfg);
                    @exec("sudo cp {$sslFile} /etc/apache2/sites-available/{$full}-le-ssl.conf && sudo a2ensite {$full}-le-ssl.conf 2>/dev/null");
                    @unlink($sslFile);
                }
                @exec("sudo systemctl reload apache2 2>/dev/null");
                $msg .= " Vhost created for {$full}.";
                if (!empty($_POST['create_ftp'])) {
                    $ftpUser = trim($_POST['ftp_username'] ?: $subdomain);
                    $ftpPass = $_POST['ftp_password'] ?? bin2hex(random_bytes(6));
                    // Jail FTP to the subdomain folder itself (never expose public_html or allow escaping it)
                    $ftpDir = 'public_html/' . $subdomain;
                    $reserved = ['admin','root','administrator','superuser','system','sys','www','web','test','user','guest','demo','ftp','mail','mysql','backup','support','info','hostmaster','postmaster','webmaster','nobody','daemon','bin'];
                    if (in_array(strtolower($ftpUser), $reserved)) { $_SESSION['error'] = "Username '{$ftpUser}' is reserved."; $this->response->redirect('/user/subdomains'); exit; }
                    if (!preg_match('/^[a-z][a-z0-9_]+$/', $ftpUser)) { $_SESSION['error'] = 'Invalid FTP username (letters, numbers, underscore only).'; $this->response->redirect('/user/subdomains'); exit; }
                    if (strlen($ftpPass) < 6) { $_SESSION['error'] = 'FTP password must be at least 6 characters.'; $this->response->redirect('/user/subdomains'); exit; }
                    $fullUser = $ownerUser . '_' . $ftpUser;
                    if ($this->db->table('ftp_accounts')->where('username', $fullUser)->first()) { $_SESSION['error'] = "FTP user '{$fullUser}' already exists."; $this->response->redirect('/user/subdomains'); exit; }
                    $absDir = $home . '/' . $ftpDir;
                    @exec("sudo mkdir -p {$absDir} && sudo chown -R {$fullUser}:{$fullUser} {$absDir} 2>/dev/null");
                    try {
                        $this->db->table('ftp_accounts')->insertGetId([
                            'hosting_user_id' => $this->hostingUser->id,
                            'username' => $fullUser, 'password_hash' => password_hash($ftpPass, PASSWORD_DEFAULT),
                            'directory' => $ftpDir, 'permissions' => 'read_write',
                        ]);
                        @exec("sudo useradd -m -d {$absDir} -s /bin/bash {$fullUser} 2>/dev/null");
                        @exec("echo '{$ftpPass}' | sudo passwd --stdin {$fullUser} 2>/dev/null");
                        // Jail this FTP user to the subdomain folder via vsftpd per-user config
                        @exec("sudo mkdir -p /etc/vsftpd_user_conf && echo 'local_root={$absDir}' | sudo tee /etc/vsftpd_user_conf/{$fullUser} >/dev/null");
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

    public function updateDnsRecord($id)
    {
        $u = $this->requireUser();
        $input = json_decode(file_get_contents('php://input'), true) ?: [];
        $record = $this->db->table('dns_records')->where('id', $id)->where('is_user_subdomain', 1)->first();
        if (!$record) {
            echo json_encode(['success' => false, 'message' => 'Record not found.']);
            exit;
        }
        $update = [];
        if (!empty($input['value'])) $update['value'] = $input['value'];
        if (!empty($input['ttl'])) $update['ttl'] = (int)$input['ttl'];
        if (!empty($input['type'])) $update['type'] = strtoupper($input['type']);
        if (!empty($input['name'] ?? '')) {
            $name = preg_replace('/[^a-z0-9\-\.]/i', '', $input['name']);
            if (strlen($name) > 0) $update['name'] = $name;
        }
        if (!empty($update)) {
            $this->db->table('dns_records')->where('id', $id)->update($update);
            $zone = $this->db->table('dns_zones')->where('id', $record->zone_id)->first();
            if ($zone) $this->dns->syncZoneToBind($zone->id);
            echo json_encode(['success' => true, 'message' => 'DNS record updated.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No changes.']);
        }
        exit;
    }

    public function deleteSubdomain($id)
    {
        $u = $this->requireUser();
        $record = $this->db->table('dns_records')->where('id', $id)->where('is_user_subdomain', 1)->first();
        if ($record) {
            $zone = $this->db->table('dns_zones')->where('id', $record->zone_id)->first();
            $domainOwner = $zone ? $this->db->table('hosting_users')->where('domain', $zone->domain)->first() : null;
            $ownerUser = $domainOwner ? $domainOwner->username : $this->hostingUser->username;
            $full = $record->name . '.' . ($zone->domain ?? '');
            $this->db->table('dns_records')->where('id', $id)->delete();
            @exec("sudo a2dissite {$full}.conf 2>/dev/null && sudo a2dissite {$full}-le-ssl.conf 2>/dev/null && sudo rm -f /etc/apache2/sites-available/{$full}.conf /etc/apache2/sites-available/{$full}-le-ssl.conf && sudo systemctl reload apache2 2>/dev/null");
            @exec("sudo rm -rf /home/{$ownerUser}/public_html/{$record->name} 2>/dev/null");
            $_SESSION['success'] = "Subdomain {$full} deleted.";
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

