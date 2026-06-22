<?php
namespace Admin\Controllers;

use Core\Controller;

class UserFeaturesController extends Controller
{
    protected $auth, $request, $response, $db;

    public function __construct()
    {
        $app = \Core\Application::getInstance();
        $this->auth = $app->get('auth');
        $this->request = $app->get('request');
        $this->response = $app->get('response');
        $this->db = $app->get('db');
    }

    public function index()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $user = $this->auth->user();
        
        // Group packages by type
        $allPackages = $this->db->table('hosting_packages')->orderBy('type', 'ASC')->orderBy('sort_order', 'ASC')->get() ?: [];
        $packagesByType = [];
        $typeOrder = ['web_hosting'=>'Web Hosting', 'Web Hosting'=>'Web Hosting',
            'web_reseller'=>'Web Hosting Reseller', 'Web Hosting Reseller'=>'Web Hosting Reseller',
            'icecast'=>'Icecast Streaming', 'Icecast Streaming'=>'Icecast Streaming',
            'icecast_reseller'=>'Icecast Reseller', 'Icecast Reseller'=>'Icecast Reseller',
            'shoutcast'=>'SHOUTcast', 'SHOUTcast'=>'SHOUTcast',
            'shoutcast_reseller'=>'SHOUTcast Reseller', 'SHOUTcast Reseller'=>'SHOUTcast Reseller',
            'vps'=>'VPS Servers', 'VPS Servers'=>'VPS Servers',
            'dedicated'=>'Dedicated Servers', 'Dedicated Servers'=>'Dedicated Servers',
            'game_server'=>'Game Servers', 'Game Servers'=>'Game Servers',
        ];
        
        foreach ($allPackages as $pkg) {
            $type = $pkg->type ?? 'Other';
            $label = $typeOrder[$type] ?? ucwords(str_replace(['_','-'], ' ', $type));
            if (!isset($packagesByType[$label])) $packagesByType[$label] = [];
            $packagesByType[$label][] = $pkg;
        }

        // Features grouped by relevance to package type
        $allFeatures = [
            'disk_space' => 'Disk Space',
            'bandwidth' => 'Bandwidth',
            'email_accounts' => 'Email Accounts',
            'ftp_accounts' => 'FTP Accounts',
            'databases' => 'Databases',
            'subdomains' => 'Subdomains',
            'addon_domains' => 'Addon Domains',
            'listener_limit' => 'Listeners',
            'bitrate' => 'Bitrate',
            'storage_limit' => 'Storage',
            'dj_accounts' => 'DJ Accounts',
            'chatroom_enabled' => 'Chat Room',
            'live_chat_enabled' => 'Live Chat',
        ];

        return $this->view('admin.userfeatures.index', [
            'user' => $user, 'title' => 'Feature Manager',
            'packagesByType' => $packagesByType, 'allFeatures' => $allFeatures,
            'theme_settings' => json_decode($user->theme_settings ?? '{}', true),
        ]);
    }

    public function toggle($feature)
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $packageId = (int)($this->request->get('package', 0));
        if ($packageId) {
            $pkg = $this->db->table('hosting_packages')->where('id', $packageId)->first();
            if ($pkg) {
                $feats = json_decode($pkg->features ?? '{}', true);
                $feats[$feature] = isset($feats[$feature]) ? !$feats[$feature] : 0;
                $this->db->table('hosting_packages')->where('id', $packageId)->update(['features' => json_encode($feats)]);
                $_SESSION['success_message'] = "Feature '{$feature}' toggled.";
            }
        }
        $this->response->redirect('/admin/userfeatures');
    }
}
