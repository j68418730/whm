<?php

namespace User\Controllers;

use Core\Controller;

/**
 * Client Security Center
 *
 * Application-level access control for a customer's OWN services.
 * This is a separate module from the admin firewall (/admin/firewall).
 * No Linux firewall (firewalld/iptables/nft) is ever touched here.
 */
class SecurityCenterController extends Controller
{
    protected $auth;
    protected $request;
    protected $response;
    protected $db;
    protected $hostingUser;
    protected $security;

    public function __construct()
    {
        $app = \Core\Application::getInstance();
        $this->auth = $app->get('auth');
        $this->request = $app->get('request');
        $this->response = $app->get('response');
        $this->db = $app->get('db');
        $this->security = new \Services\SecurityService($this->db);
    }

    protected function requireUser()
    {
        if (!$this->auth->check()) { $this->response->redirect('/?login'); exit; }
        $user = $this->auth->user();
        $this->hostingUser = $this->db->table('hosting_users')->where('email', $user->email)->first();
        if (!$this->hostingUser && !empty($user->id)) $this->hostingUser = $this->db->table('hosting_users')->where('id', $user->id)->first();
        if (!$this->hostingUser) { $this->response->redirect('/?login'); exit; }
        return $user;
    }

    protected function actor()
    {
        $u = $this->auth->user();
        return ($u->name ?? $u->email ?? '') ?: 'Owner';
    }

    protected function customerId()
    {
        return (int)($this->hostingUser->id ?? 0);
    }

    // ─── Dashboard / tab dispatcher ───
    public function index()
    {
        $u = $this->requireUser();
        $cid = $this->customerId();
        $tab = $_GET['tab'] ?? 'dashboard';

        switch ($tab) {
            case 'rules':
                return $this->render('rules', ['rules' => $this->security->getRules($cid)], $u);
            case 'services':
                return $this->render('services', ['rules' => $this->security->getRules($cid)], $u);
            case 'login':
                return $this->render('login', [
                    'settings' => $this->security->getSettings($cid),
                    'attempts' => $this->recentAttempts($cid, 25),
                    'trusted'  => $this->security->getTrusted($cid),
                    'sessions' => $this->security->getSessions($cid),
                ], $u);
            case 'audit':
                return $this->render('audit', ['logs' => $this->security->getLogs($cid, 500)], $u);
            case 'alerts':
                $alerts = $this->security->getAlerts($cid, 100);
                $this->security->markAlertsRead($cid);
                return $this->render('alerts', ['alerts' => $alerts], $u);
            default:
                $data = $this->security->dashboard($cid);
                $data['sessions'] = $this->security->getSessions($cid);
                return $this->render('dashboard', $data, $u);
        }
    }

    protected function render($tab, $extra, $user)
    {
        $cid = $this->customerId();
        return $this->view('user.security_center', array_merge([
            'user' => $user, 'hosting' => $this->hostingUser,
            'services' => $this->security->listServices(),
            'actions' => $this->security->listActions(),
            'ruleTypes' => $this->security->listRuleTypes(),
            'activeTab' => $tab,
            'activeService' => $_GET['service'] ?? 'all',
            'serviceData' => $this->serviceContext($cid, $_GET['service'] ?? 'all'),
            'title' => 'Security Center',
        ], $extra));
    }

    public function ruleStore()
    {
        $u = $this->requireUser();
        $cid = $this->customerId();
        $data = $this->request->post();
        $this->security->addRule($cid, $data, $this->actor());
        $_SESSION['success'] = 'Security rule added.';
        $this->response->redirect('/user/security?tab=rules');
        exit;
    }

    public function ruleDelete($id)
    {
        $u = $this->requireUser();
        $this->security->deleteRule($this->customerId(), (int)$id, $this->actor());
        $_SESSION['success'] = 'Security rule removed.';
        $this->response->redirect('/user/security?tab=rules');
        exit;
    }

    // ─── Service controls (per-service quick actions) ───
    protected function serviceContext($cid, $service)
    {
        $out = [];
        if ($service === 'radio' || $service === 'requests' || $service === 'dj') {
            try {
                $out['stations'] = $this->db->table('streaming_stations')->where('user_id', $cid)->get() ?: [];
            } catch (\Exception $e) {}
        }
        if ($service === 'chat') {
            try {
                $tenant = $this->db->table('chatbox_tenants')->where('hosting_user_id', $cid)->first();
                if ($tenant) $out['rooms'] = $this->db->table('chatbox_rooms')->where('tenant_id', $tenant->id)->get() ?: [];
            } catch (\Exception $e) {}
        }
        if ($service === 'game') {
            try { $out['games'] = $this->db->table('game_types')->where('is_active', 1)->get() ?: []; } catch (\Exception $e) {}
        }
        return $out;
    }

    // ─── Login security settings ───
    public function loginSecuritySave()
    {
        $u = $this->requireUser();
        $cid = $this->customerId();
        $keys = ['max_login_attempts','lockout_minutes','session_timeout_minutes','require_trusted','country_restriction'];
        foreach ($keys as $k) {
            if (isset($_POST[$k])) $this->security->setSetting($cid, $k, trim((string)$_POST[$k]));
        }
        // Trusted IP / device
        if (!empty($_POST['trust_ip'])) {
            $ip = trim($_POST['trust_ip']);
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                $this->security->addTrusted($cid, 'ip', $ip, $_POST['trust_label'] ?? '');
                $this->security->log($cid, 'trusted_added', $ip, 'login', 'logged', $this->actor(), 'Trusted IP added');
            }
        }
        $_SESSION['success'] = 'Login security settings saved.';
        $this->response->redirect('/user/security?tab=login');
        exit;
    }

    public function trustedDelete($id)
    {
        $u = $this->requireUser();
        $this->security->removeTrusted($this->customerId(), (int)$id);
        $_SESSION['success'] = 'Trusted entry removed.';
        $this->response->redirect('/user/security?tab=login');
        exit;
    }

    public function sessionTerminate($id)
    {
        $u = $this->requireUser();
        $cid = $this->customerId();
        // Terminate any session except the current one
        $current = session_id() ? hash('sha256', session_id()) : '';
        $sessions = $this->security->getSessions($cid);
        foreach ($sessions as $s) {
            if ((int)$s->id === (int)$id && $s->session_hash !== $current) {
                $this->db->table('security_sessions')->where('id', $id)->where('customer_id', $cid)->delete();
                $this->security->log($cid, 'session_terminated', 'session#' . $id, 'login', 'logged', $this->actor(), 'Session terminated');
            }
        }
        $_SESSION['success'] = 'Session terminated.';
        $this->response->redirect('/user/security?tab=login');
        exit;
    }

    protected function recentAttempts($cid, $limit = 25)
    {
        try {
            $st = $this->db->pdo()->prepare("SELECT * FROM security_login_attempts WHERE customer_id = ? ORDER BY id DESC LIMIT " . (int)$limit);
            $st->execute([$cid]);
            return $st->fetchAll(\PDO::FETCH_OBJ) ?: [];
        } catch (\Exception $e) { return []; }
    }

    // ─── Audit log ───

    public function alertsClear()
    {
        $u = $this->requireUser();
        $this->db->table('security_alerts')->where('customer_id', $this->customerId())->delete();
        $_SESSION['success'] = 'All notifications cleared.';
        $this->response->redirect('/user/security?tab=alerts');
        exit;
    }
}
