<?php
$p = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');

// Check Call Spectre user
$u = $p->query("SELECT id, username, package_id, email FROM hosting_users WHERE username='callspectre'")->fetch(PDO::FETCH_OBJ);
if (!$u) { echo "USER NOT FOUND\n"; exit; }
echo "User: {$u->username} (id:{$u->id}) package_id:{$u->package_id}\n";

// Check package
$pkg = $p->query("SELECT id, type, name, chatroom_enabled, chatroom_voice_enabled FROM hosting_packages WHERE id={$u->package_id}")->fetch(PDO::FETCH_OBJ);
if ($pkg) {
    echo "Package: {$pkg->name} type:{$pkg->type} chatroom:{$pkg->chatroom_enabled} voice:{$pkg->chatroom_voice_enabled}\n";
} else {
    echo "PACKAGE NOT FOUND for id {$u->package_id}\n";
}

// Check chatbox tenant
$ct = $p->query("SELECT id, voice_enabled FROM chatbox_tenants WHERE hosting_user_id={$u->id}")->fetch(PDO::FETCH_OBJ);
if ($ct) {
    echo "Chatbox Tenant: id={$ct->id} voice={$ct->voice_enabled}\n";
} else {
    echo "NO CHATBOX TENANT\n";
}

// Check streams
$s = $p->query("SELECT id, port, status FROM radio_streams WHERE user_id={$u->id}")->fetch(PDO::FETCH_OBJ);
if ($s) {
    echo "Stream: id={$s->id} port={$s->port} status={$s->status}\n";
} else {
    echo "NO STREAMS\n";
}
