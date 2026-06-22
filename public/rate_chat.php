<?php
// Customer Chat Rating
session_start();
$pdo = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');

if ($_POST) {
    $chatId = (int)$_POST['chat_id'];
    $rating = (int)$_POST['rating'];
    $feedback = $_POST['feedback'] ?? '';
    $pdo->prepare("INSERT INTO chat_ratings (chat_id, tenant_id, rating, feedback) VALUES (?, 0, ?, ?) ON DUPLICATE KEY UPDATE rating=?, feedback=?")
        ->execute([$chatId, $rating, $feedback, $rating, $feedback]);
    echo '<div style="text-align:center;padding:40px;color:#4ade80;font-size:18px">✓ Thank you for your feedback!</div>';
    exit;
}

$chatId = (int)$_GET['id'];
$chat = $pdo->prepare("SELECT * FROM chat_sessions WHERE id = ?");
$chat->execute([$chatId]); $chat = $chat->fetch(PDO::FETCH_OBJ);
if (!$chat) die('Chat not found');

$existing = $pdo->prepare("SELECT * FROM chat_ratings WHERE chat_id = ?");
$existing->execute([$chatId]); $existing = $existing->fetch(PDO::FETCH_OBJ);
if ($existing) { echo '<div style="text-align:center;padding:40px;color:#64748b">You already rated this chat.</div>'; exit; }
?>
<!DOCTYPE html><html><head><title>Rate Your Chat - Planet Hosts</title>
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<link rel="stylesheet" href="/theme/assets/css/style.css">
<style>body{display:flex;justify-content:center;align-items:center;min-height:100vh;margin:0;background:#000;font-family:Inter,sans-serif}
.bg{position:fixed;inset:0;background:linear-gradient(rgba(2,8,23,.9),rgba(2,8,23,.97)),url(/theme/assets/img/background.png);background-size:cover;z-index:-2}
.card{background:rgba(8,16,28,.95);border:1px solid rgba(0,191,255,.12);border-radius:16px;padding:40px;max-width:450px;width:90%;text-align:center;position:relative}
h1{color:#fff;font-size:22px;margin:0 0 8px}h1 span{color:#008cff}
p{color:#64748b;font-size:14px;margin:0 0 24px}
.stars{display:flex;justify-content:center;gap:8px;margin-bottom:20px;flex-direction:row-reverse}
.stars input{display:none}
.stars label{font-size:36px;cursor:pointer;color:#333;transition:.15s}
.stars label:hover,.stars label:hover~label,.stars input:checked~label{color:#facc15}
textarea{width:100%;padding:12px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#fff;outline:none;box-sizing:border-box;margin-bottom:16px;font-family:Inter,sans-serif}
.btn{width:100%;padding:14px;background:linear-gradient(135deg,#008cff,#3bb8ff);color:#fff;border:none;border-radius:8px;font-size:15px;font-weight:700;cursor:pointer}
</style></head><body>
<div class="bg"></div>
<div class="card">
<h1>PLANET-<span>HOSTS</span></h1>
<p>How was your chat experience?</p>
<form method="POST">
<input type="hidden" name="chat_id" value="<?php echo $chatId; ?>">
<div class="stars">
<input type="radio" name="rating" value="5" id="s5"><label for="s5">★</label>
<input type="radio" name="rating" value="4" id="s4"><label for="s4">★</label>
<input type="radio" name="rating" value="3" id="s3"><label for="s3">★</label>
<input type="radio" name="rating" value="2" id="s2"><label for="s2">★</label>
<input type="radio" name="rating" value="1" id="s1"><label for="s1">★</label>
</div>
<textarea name="feedback" rows="3" placeholder="Any additional feedback? (optional)"></textarea>
<button type="submit" class="btn">Submit Rating</button>
</form>
</div></body></html>
