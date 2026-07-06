<?php

namespace User\Controllers;

use Core\Controller;

class UserController extends Controller
{
    protected $auth;
    protected $request;
    protected $response;
    protected $db;
    protected $hostingUser;
    protected $package;

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
        $hostings = $this->db->table('hosting_users')->get() ?: [];
        // Try to find by ID first (sudo/Login as User mode)
        if (!empty($user->id)) {
            foreach ($hostings as $h) { if ($h->id == $user->id) { $this->hostingUser = $h; break; } }
        }
        if (!$this->hostingUser && !empty($user->email)) {
            foreach ($hostings as $h) { if ($h->email === $user->email) { $this->hostingUser = $h; break; } }
        }
        if (!$this->hostingUser && !empty($user->name)) {
            foreach ($hostings as $h) { if ($h->username === $user->name || $h->first_name === $user->name) { $this->hostingUser = $h; break; } }
        }
        if (!$this->hostingUser && !empty($hostings)) {
            $this->hostingUser = $hostings[0];
        }
        if ($this->hostingUser) {
            $this->package = $this->db->table('hosting_packages')->where('id', $this->hostingUser->package_id)->first();
        }
        return $user;
    }

    public function index()
    {
        $u = $this->loadUser();
        $hosting = $this->hostingUser;
        $pkg = $this->package;
        $pkgType = $pkg->type ?? ($hosting->plan_type ?? '');
        $uid = $hosting ? $hosting->id : 0;

        // Detect account type
        $isWeb = $pkgType === 'web_hosting' || $pkgType === 'hosting' || $pkgType === '' || str_contains($pkgType, 'web');
        $isRadio = str_contains($pkgType, 'icecast') || str_contains($pkgType, 'shoutcast') || str_contains($pkgType, 'radio') || str_contains($pkgType, 'stream');
        $isVps = str_contains($pkgType, 'vps') || str_contains($pkgType, 'virtual');
        $isDedicated = str_contains($pkgType, 'dedicated') || str_contains($pkgType, 'ded');
        $isChat = str_contains($pkgType, 'chat') || str_contains($pkgType, 'livechat');
        $isGame = str_contains($pkgType, 'game');

        // Streams
        $streams = $uid ? ($this->db->table('radio_streams')->where('user_id', $uid)->get() ?: []) : [];
        $hasStreams = count($streams) > 0;
        if ($hasStreams) $isRadio = true;

        // Disk usage (only for web/vps/dedicated)
        $diskTotal = $pkg->disk_space ?? 0;
        $diskUsed = 0; $diskPct = 0;
        if ($hosting && ($isWeb || $isVps || $isDedicated)) {
            $dir = '/home/' . $hosting->username;
            if (is_dir($dir)) {
                $du = @shell_exec("du -sk " . escapeshellarg($dir) . " 2>/dev/null | awk '{print \$1}'");
                $diskUsed = $du ? round((int)trim($du) / 1024 / 1024, 2) : 0;
            }
            $diskPct = $diskTotal > 0 ? round(($diskUsed / $diskTotal) * 100) : 0;
        }

        // Email (only for web)
        $emailAccounts = [];
        $emailCount = 0;
        $emailAllowed = false;
        if ($isWeb && $uid && $hosting) {
            try { $emailAccounts = $this->db->table('mail_accounts')->where('domain', $hosting->domain ?? '')->get() ?: []; } catch (\Exception $e) {}
            $emailCount = count($emailAccounts);
            $emailAllowed = ($pkg->email_accounts ?? 0) > 0;
        }

        // Domains (only for web)
        $domains = [];
        if ($isWeb && $uid) {
            try { $domains = $this->db->table('hosting_domains')->where('user_id', $uid)->get() ?: []; } catch (\Exception $e) {}
            if ($hosting && $hosting->domain) {
                $hasMain = false;
                foreach ($domains as $d) { if (($d->domain ?? '') === $hosting->domain) $hasMain = true; }
                if (!$hasMain) {
                    $mainDomain = (object)['domain' => $hosting->domain, 'type' => 'main', 'status' => 'active'];
                    array_unshift($domains, $mainDomain);
                }
            }
        }

        // Tickets (all account types)
        $openTickets = $uid ? ($this->db->table('tickets')->where('user_id', $uid)->where('status', 'open')->get() ?: []) : [];
        $pendingTickets = $uid ? ($this->db->table('tickets')->where('user_id', $uid)->where('status', 'pending')->get() ?: []) : [];
        $resolvedTickets = $uid ? ($this->db->table('tickets')->where('user_id', $uid)->where('status', 'resolved')->get() ?: []) : [];

        // Invoices (all account types)
        $openInvoices = $uid ? ($this->db->table('invoices')->where('user_id', $uid)->where('status', 'unpaid')->get() ?: []) : [];
        $paidInvoices = $uid ? ($this->db->table('invoices')->where('user_id', $uid)->where('status', 'paid')->get() ?: []) : [];

        // Services / Orders (all account types)
        $services = $uid ? ($this->db->table('billing_services')->where('user_id', $uid)->get() ?: []) : [];
        // If no billing services, show the hosting package as a service
        if (empty($services) && $hosting && $pkg) {
            $svc = new \stdClass();
            $svc->id = $hosting->id;
            $svc->name = $pkg->name;
            $svc->type = $pkg->type ?? 'web_hosting';
            $svc->price = $pkg->monthly_price ?? 0;
            $services = [$svc];
        }
        $orders = $uid ? ($this->db->table('billing_orders')->where('user_id', $uid)->get() ?: []) : [];

        // Game servers (all account types)
        $gameServers = $uid ? ($this->db->table('game_servers')->where('user_id', $uid)->get() ?: []) : [];
        $hasGames = count($gameServers) > 0;

        // Chatbox tenant (all account types)
        $chatTenant = null;
        if ($uid) $chatTenant = $this->db->table('chatbox_tenants')->where('hosting_user_id', $uid)->first();
        $hasChat = $chatTenant !== null;

        // Activity (recent)
        $recentActivity = [];
        try { $recentActivity = $this->db->table('activity_log')->where('user_id', $uid)->orderBy('created_at', 'DESC')->limit(5)->get() ?: []; } catch (\Exception $e) {}

        $lastLogin = $hosting->last_login ?? 'First login';
        $userIp = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';

        $notifications = [];
        if ($diskPct > 90) $notifications[] = ['type' => 'warning', 'msg' => "Disk usage at {$diskPct}%"];
        if (count($openInvoices) > 0) $notifications[] = ['type' => 'danger', 'msg' => count($openInvoices) . ' unpaid invoice(s)'];

        return $this->view('user.dashboard', [
            'user' => $u, 'hosting' => $hosting, 'package' => $pkg,
            'streams' => $streams, 'hasStreams' => $hasStreams,
            'isWeb' => $isWeb, 'isRadio' => $isRadio, 'isVps' => $isVps,
            'isDedicated' => $isDedicated, 'isChat' => $isChat, 'isGame' => $isGame,
            'diskUsed' => $diskUsed, 'diskTotal' => $diskTotal, 'diskPct' => $diskPct,
            'emailCount' => $emailCount, 'emailAllowed' => $emailAllowed,
            'domains' => $domains, 'openTickets' => $openTickets, 'pendingTickets' => $pendingTickets,
            'resolvedTickets' => $resolvedTickets, 'openInvoices' => $openInvoices, 'paidInvoices' => $paidInvoices,
            'services' => $services, 'orders' => $orders,
            'gameServers' => $gameServers, 'hasGames' => $hasGames,
            'chatTenant' => $chatTenant, 'hasChat' => $hasChat,
            'recentActivity' => $recentActivity,
            'lastLogin' => $lastLogin, 'userIp' => $userIp,
            'notifications' => $notifications, 'title' => 'Dashboard',
        ]);
    }

    public function services() { $u = $this->loadUser(); return $this->view('user.services', ['user' => $u, 'hosting' => $this->hostingUser, 'title' => 'My Services']); }
    public function usage() { $u = $this->loadUser(); return $this->view('user.usage', ['user' => $u, 'hosting' => $this->hostingUser, 'title' => 'Resource Usage']); }
    public function profile() { $u = $this->loadUser(); return $this->view('user.profile', ['user' => $u, 'hosting' => $this->hostingUser, 'package' => $this->package, 'title' => 'Profile']); }
    public function security() { $u = $this->loadUser(); return $this->view('user.security', ['user' => $u, 'hosting' => $this->hostingUser, 'title' => 'Security']); }
    public function support() { $u = $this->loadUser(); return $this->view('user.support', ['user' => $u, 'hosting' => $this->hostingUser, 'title' => 'Support']); }
    public function stats() { $u = $this->loadUser(); return $this->view('user.stats', ['user' => $u, 'hosting' => $this->hostingUser, 'title' => 'Statistics']); }
    public function tools() { $u = $this->loadUser(); return $this->view('user.tools', ['user' => $u, 'hosting' => $this->hostingUser, 'title' => 'Tools']); }
    public function login()
    {
        $username = $_POST['email'] ?? $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        $user = $this->db->table('hosting_users')->where('username', $username)->first();
        if (!$user) $user = $this->db->table('hosting_users')->where('email', $username)->first();
        if ($user && password_verify($password, $user->password_hash)) {
            $_SESSION['user'] = (object)[
                'id' => $user->id, 'email' => $user->email,
                'name' => $user->username, 'is_admin' => false,
            ];
            $_SESSION['is_admin'] = false;
            header('Location: /user');
        } else {
            header('Location: /?login=error');
        }
        exit;
    }

    public function terminal() { $u = $this->loadUser(); return $this->view('user.terminal', ['user' => $u, 'hosting' => $this->hostingUser, 'title' => 'Terminal']); }
    public function ftp() { $u = $this->loadUser(); $accts = []; if ($this->hostingUser) { try { $accts = $this->db->table('ftp_accounts')->where('hosting_user_id', $this->hostingUser->id)->get() ?: []; } catch (\Exception $e) {} } return $this->view('user.ftp', ['user' => $u, 'hosting' => $this->hostingUser, 'package' => $this->package, 'ftpAccounts' => $accts, 'title' => 'FTP Manager']); }
    public function ftpCreate() { $u = $this->loadUser(); if (!$this->hostingUser) { header('Location: /user/ftp'); exit; } $username = strtolower(preg_replace('/[^a-z0-9]/', '', $_POST['username'] ?? '')); $password = $_POST['password'] ?? ''; $dir = trim($_POST['directory'] ?? 'public_html'); $perms = $_POST['permissions'] ?? 'read_write'; $quota = $_POST['quota'] ?? 'unlimited'; $ssl = (int)($_POST['ssl_enabled'] ?? 1); if (!$username || !$password) { $_SESSION['error'] = 'Username and password required.'; header('Location: /user/ftp'); exit; } $fullUser = $this->hostingUser->username . '_' . $username; try { $this->db->table('ftp_accounts')->insertGetId(['hosting_user_id' => $this->hostingUser->id, 'username' => $fullUser, 'password_hash' => password_hash($password, PASSWORD_DEFAULT), 'directory' => $dir, 'permissions' => $perms, 'quota' => $quota, 'ssl_enabled' => $ssl]); $_SESSION['success'] = "FTP user '{$fullUser}' created."; @exec("sudo mkdir -p /home/{$this->hostingUser->username}/{$dir} 2>/dev/null; sudo useradd -m -d /home/{$this->hostingUser->username} -s /bin/bash {$fullUser} 2>/dev/null; echo '{$password}' | sudo passwd --stdin {$fullUser} 2>/dev/null"); } catch (\Exception $e) { $_SESSION['error'] = 'Failed to create FTP user.'; } header('Location: /user/ftp'); exit; }
    public function ftpPassword($id) { $u = $this->loadUser(); if ($this->hostingUser) { $pw = $_POST['password'] ?? ''; if (strlen($pw) >= 6) { try { $acct = $this->db->table('ftp_accounts')->where('id', $id)->where('hosting_user_id', $this->hostingUser->id)->first(); if ($acct) { $this->db->table('ftp_accounts')->where('id', $id)->update(['password_hash' => password_hash($pw, PASSWORD_DEFAULT)]); @exec("echo '{$pw}' | sudo passwd --stdin {$acct->username} 2>/dev/null"); $_SESSION['success'] = 'FTP password changed.'; } } catch (\Exception $e) {} } } header('Location: /user/ftp'); exit; }
    public function ftpToggle($id) { $u = $this->loadUser(); if ($this->hostingUser) { try { $acct = $this->db->table('ftp_accounts')->where('id', $id)->where('hosting_user_id', $this->hostingUser->id)->first(); if ($acct) { $new = $acct->is_active ? 0 : 1; $this->db->table('ftp_accounts')->where('id', $id)->update(['is_active' => $new]); $_SESSION['success'] = $new ? 'FTP account unsuspended.' : 'FTP account suspended.'; @exec($new ? "sudo usermod -U {$acct->username} 2>/dev/null" : "sudo usermod -L {$acct->username} 2>/dev/null"); } } catch (\Exception $e) {} } header('Location: /user/ftp'); exit; }
    public function ftpDelete($id) { $u = $this->loadUser(); if ($this->hostingUser) { try { $acct = $this->db->table('ftp_accounts')->where('id', $id)->where('hosting_user_id', $this->hostingUser->id)->first(); if ($acct) { @exec("sudo userdel -rf {$acct->username} 2>/dev/null"); $this->db->table('ftp_accounts')->where('id', $id)->delete(); $_SESSION['success'] = 'FTP account deleted.'; } } catch (\Exception $e) {} } header('Location: /user/ftp'); exit; }
    public function cronSave() { $u = $this->loadUser(); $min=$_POST['minute']??'*';$hr=$_POST['hour']??'*';$day=$_POST['day']??'*';$mon=$_POST['month']??'*';$wkd=$_POST['weekday']??'*';$cmd=$_POST['command']??'';if($cmd&&$this->hostingUser){$line="$min $hr $day $mon $wkd $cmd\n";$f='/home/'.$this->hostingUser->username.'/crontab.txt';file_put_contents($f,$line,FILE_APPEND);@exec("sudo crontab -u {$this->hostingUser->username} {$f} 2>/dev/null");$_SESSION['success']='Cron job added.';}header('Location: /user/cron');exit;}
    public function cronDelete() { $u = $this->loadUser(); $job=$_GET['job']??'';if($job&&$this->hostingUser){$f='/home/'.$this->hostingUser->username.'/crontab.txt';if(is_file($f)){$lines=file($f,FILE_IGNORE_NEW_LINES);$new=[];foreach($lines as $l){if(trim($l)!==trim($job))$new[]=$l;}file_put_contents($f,implode("\n",$new));@exec("sudo crontab -u {$this->hostingUser->username} {$f} 2>/dev/null");$_SESSION['success']='Cron job deleted.';}else{$_SESSION['error']='No crontab file.';}}header('Location: /user/cron');exit;}
    public function backupCreate() { $u=$this->loadUser();if($this->hostingUser){$d='/home/'.$this->hostingUser->username;$f=$d.'/backup_'.date('Y-m-d_H-i-s').'.tar.gz';@exec("sudo tar czf '{$f}' -C '{$d}/public_html' . 2>/dev/null");$_SESSION['success']='Backup created.';}header('Location: /user/backup');exit;}
    public function backupRestore() { $u=$this->loadUser();if($this->hostingUser){$f='/home/'.$this->hostingUser->username.'/'.basename($_GET['file']??'');if(is_file($f)){@exec("sudo tar xzf '{$f}' -C '/home/{$this->hostingUser->username}/public_html' 2>/dev/null");$_SESSION['success']='Backup restored.';}else $_SESSION['error']='File not found.';}header('Location: /user/backup');exit;}
    public function backupDownload() { $u=$this->loadUser();$f='/home/'.($this->hostingUser->username??'').'/'.basename($_GET['file']??'');if(is_file($f)){header('Content-Type: application/octet-stream');header('Content-Disposition: attachment; filename="'.basename($f).'"');readfile($f);exit;}header('Location: /user/backup');exit;}
    public function backupDelete() { $u=$this->loadUser();if($this->hostingUser){$f='/home/'.$this->hostingUser->username.'/'.basename($_GET['file']??'');if(is_file($f)){unlink($f);$_SESSION['success']='Backup deleted.';}}header('Location: /user/backup');exit;}
    public function cron() { $u = $this->loadUser(); return $this->view('user.cron', ['user' => $u, 'hosting' => $this->hostingUser, 'title' => 'Cron Jobs']); }
    public function git() { $u = $this->loadUser(); return $this->view('user.git', ['user' => $u, 'hosting' => $this->hostingUser, 'title' => 'Git Deployments']); }
    public function backup() { $u = $this->loadUser(); return $this->view('user.backup', ['user' => $u, 'hosting' => $this->hostingUser, 'title' => 'Backups']); }
    public function games() { $u = $this->loadUser(); return $this->view('user.games', ['user' => $u, 'hosting' => $this->hostingUser, 'title' => 'Game Servers']); }
    public function websiteBuilder() { $u = $this->loadUser(); return $this->view('user.websitebuilder', ['user' => $u, 'hosting' => $this->hostingUser, 'title' => 'Website Builder']); }
    public function installer() { $u = $this->loadUser(); return $this->view('user.installer', ['user' => $u, 'hosting' => $this->hostingUser, 'title' => 'Quick Install']); }
    public function djPanel() { header('Location: /dj_panel.php'); exit; }
    public function publicDjs() { $u = $this->loadUser(); $allDjs = []; if ($this->hostingUser) { try { $pdo = $this->db->pdo(); $hid = (int)$this->hostingUser->id; $stmt = $pdo->prepare("SELECT d.* FROM radio_djs d JOIN radio_stations s ON d.stream_id = s.id WHERE s.hosting_user_id = ? AND d.status = 'active' ORDER BY d.name ASC"); $stmt->execute([$hid]); $allDjs = $stmt->fetchAll(\PDO::FETCH_OBJ); } catch(\Exception $e) {} } return $this->view('user.public_djs', ['user' => $u, 'hosting' => $this->hostingUser, 'allDjs' => $allDjs, 'title' => 'Our DJs']); }
    public function publicDjsEmbed() { require BASE_PATH . '/user/Views/public_djs_embed.php'; exit; }
    public function publicDjsByUser($username)
    {
        $pdo = $this->db->pdo();
        $stmt = $pdo->prepare("SELECT d.*, s.name as station_name FROM radio_djs d JOIN radio_stations s ON d.stream_id = s.id JOIN hosting_users h ON s.hosting_user_id = h.id WHERE h.username = ? AND d.status = 'active' ORDER BY d.name ASC");
        $stmt->execute([$username]);
        $djs = $stmt->fetchAll(\PDO::FETCH_OBJ);
        $stationName = $djs[0]->station_name ?? $username . "'s Station";
        require BASE_PATH . '/user/Views/public_djs_public.php';
        exit;
    }
    public function publicDjsOnline($username)
    {
        $pdo = $this->db->pdo();
        $stmt = $pdo->prepare("SELECT d.*, s.name as station_name FROM radio_djs d JOIN radio_stations s ON d.stream_id = s.id JOIN hosting_users h ON s.hosting_user_id = h.id WHERE h.username = ? AND d.status = 'active' AND d.last_active >= NOW() - INTERVAL 5 MINUTE ORDER BY d.name ASC");
        $stmt->execute([$username]);
        $djs = $stmt->fetchAll(\PDO::FETCH_OBJ);
        $stationName = $username . "'s Station";
        require BASE_PATH . '/user/Views/public_djs_public.php';
        exit;
    }
    public function djApply() { $u = $this->loadUser(); $streams = []; if ($this->hostingUser) { try { $streams = $this->db->table('radio_streams')->where('user_id', $this->hostingUser->id)->get() ?: []; } catch(\Exception $e) {} } return $this->view('user.dj_apply', ['user' => $u, 'hosting' => $this->hostingUser, 'streams' => $streams, 'title' => 'Apply as DJ']); }
    public function djApplySubmit() { $u = $this->loadUser(); if ($this->hostingUser) { $name = $_POST['dj_name'] ?? ''; $email = $_POST['email'] ?? ''; $genres = $_POST['genres'] ?? ''; $bio = $_POST['bio'] ?? ''; $streams = $this->db->table('radio_streams')->where('user_id', $this->hostingUser->id)->get() ?: []; $sid = $streams[0]->id ?? 0; if ($sid && $name) { try { $this->db->table('radio_djs')->insertGetId(['stream_id' => $sid, 'username' => strtolower(preg_replace('/[^a-z0-9]/','',$name)), 'password' => password_hash(bin2hex(random_bytes(4)), PASSWORD_DEFAULT), 'name' => $name, 'email' => $email, 'bio' => $genres ? $genres . "\n" . $bio : $bio, 'status' => 'active']); $_SESSION['success'] = "DJ application submitted!"; } catch(\Exception $e) { $_SESSION['error'] = 'Failed to create DJ.'; } } } header('Location: /user/dj/apply'); exit; }
    public function installerInstall() { $u = $this->loadUser(); $name = $_POST['app_name'] ?? ''; $dir = trim($_POST['directory'] ?? 'public_html'); if ($this->hostingUser && $name) { $home = '/home/' . $this->hostingUser->username; $target = $home . '/' . ltrim($dir, '/'); $localZip = BASE_PATH . '/appsinstall_files/' . $name . '.zip'; if (file_exists($localZip)) { @mkdir($target, 0755, true); copy($localZip, $target . '/installer.zip'); @exec("cd " . escapeshellarg($target) . " && unzip -qo installer.zip 2>/dev/null && rm -f installer.zip && chown -R {$this->hostingUser->username}:{$this->hostingUser->username} . 2>/dev/null"); $subs = glob($target . '/*', GLOB_ONLYDIR); if ($subs && count($subs) === 1) { $bn = basename($subs[0]); if (is_file($subs[0] . '/index.php') || is_file($subs[0] . '/wp-config-sample.php')) { @exec("cd " . escapeshellarg($target) . " && mv " . escapeshellarg($bn) . "/* . 2>/dev/null && mv " . escapeshellarg($bn) . "/.[!.]* . 2>/dev/null && rmdir " . escapeshellarg($bn) . " 2>/dev/null"); } } $_SESSION['success'] = "$name installed successfully!"; } else { $_SESSION['error'] = "App package not found."; } } header('Location: /user/installer'); exit; }
    public function webmailRedirect() { header('Location: /webmail_autologin.php'); exit; }
    public function dismissAlert($id) { $u = $this->loadUser(); if ($this->hostingUser) { try { $a = $this->db->table('user_alerts')->where('id', $id)->where('hosting_user_id', $this->hostingUser->id)->first(); if ($a && $a->can_delete) { $this->db->table('user_alerts')->where('id', $id)->update(['is_read' => 1]); echo 'OK'; } else { echo 'LOCKED'; } } catch (\Exception $e) {} } exit; }
    public function fetchAlerts() { $u = $this->loadUser(); header('Content-Type: application/json'); $alerts = []; if ($this->hostingUser) { try { $alerts = $this->db->table('user_alerts')->where('hosting_user_id', $this->hostingUser->id)->where('is_read', 0)->orderBy('created_at', 'DESC')->limit(20)->get() ?: []; } catch (\Exception $e) {} } echo json_encode($alerts); exit; }
    public function logout() { session_destroy(); header('Location: /'); exit; }
    public function chat() { $u = $this->loadUser(); $app = \Core\Application::getInstance(); $user = $app->get('auth')->user(); $pdo = $this->db->pdo(); $hosting = $this->hostingUser; require BASE_PATH . '/public/user/chat.php'; exit; }
    public function admins() { $u = $this->loadUser(); $app = \Core\Application::getInstance(); $user = $app->get('auth')->user(); $pdo = $this->db->pdo(); $hosting = $this->hostingUser; require BASE_PATH . '/public/user/admins.php'; exit; }
    public function djManager() { $u = $this->loadUser(); $app = \Core\Application::getInstance(); $user = $app->get('auth')->user(); $pdo = $this->db->pdo(); $hosting = $this->hostingUser; require BASE_PATH . '/public/user/dj-manager.php'; exit; }
    public function phpSwitcher() { $u = $this->loadUser(); $app = \Core\Application::getInstance(); $user = $app->get('auth')->user(); $pdo = $this->db->pdo(); $hosting = $this->hostingUser; require BASE_PATH . '/public/user/php-switcher.php'; exit; }
}
