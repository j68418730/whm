<?php
$p = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');
$q = $p->query("SELECT u.username, u.package_id, p.name, p.type, p.chatroom_enabled, p.chatroom_voice_enabled FROM hosting_users u JOIN hosting_packages p ON u.package_id=p.id WHERE u.username='callspectre'")->fetch(PDO::FETCH_OBJ);
if ($q) {
    echo "User: {$q->username}\nPackage: {$q->name} (id:{$q->package_id})\nType: {$q->type}\nChatroom: {$q->chatroom_enabled}\nVoice: {$q->chatroom_voice_enabled}\n";
} else {
    echo "NOT FOUND\n";
}
