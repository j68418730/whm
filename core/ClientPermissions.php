<?php
// Client Permission System
// Client = full access to their own modules
// Sub-users = only what client grants

$allPermissions = [
    'dashboard' => '📊 Dashboard',
    'radio' => '📡 Radio / Icecast',
    'streams' => '🎵 Stream Management',
    'djs' => '🎤 DJ Management',
    'autodj' => '🤖 AutoDJ',
    'chatbox' => '💬 Chat Room',
    'chat_admin' => '⚙️ Chat Admin Panel',
    'tickets' => '🎫 Support Tickets',
    'billing' => '💰 Billing / Invoices',
    'files' => '📁 File Manager',
    'email' => '📧 Email Accounts',
    'domains' => '🌐 Domains',
    'databases' => '🗄 Databases',
    'usage' => '📊 Resource Usage',
    'settings' => '⚙️ Account Settings',
];

function clientHasAccess($clientId, $subUser, $permission) {
    // Client (owner) has full access
    if (!$subUser) return true;
    // Sub-user: check permissions
    if (empty($subUser->permissions)) return false;
    $perms = json_decode($subUser->permissions, true) ?: [];
    return in_array($permission, $perms);
}

function getClientSubUsers($clientId) {
    $p = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');
    $q = $p->prepare("SELECT * FROM client_sub_users WHERE client_id = ? ORDER BY created_at DESC");
    $q->execute([$clientId]);
    return $q->fetchAll(PDO::FETCH_OBJ);
}
