<?php
require_once __DIR__ . '/radio_helper.php';
$streamId = (int)($_GET['stream'] ?? 0);
$stream = null;
if ($streamId) {
    $pdo = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');
    $st = $pdo->prepare("SELECT id, name FROM streaming_stations WHERE id=?");
    $st->execute([$streamId]);
    $stream = $st->fetch(PDO::FETCH_OBJ);
}
$stationName = $stream ? htmlspecialchars($stream->name) : 'our station';
$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>DJ Application - <?php echo $stationName; ?></title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:Inter,sans-serif;background:linear-gradient(135deg,#0a0e1a,#0f1a2e);color:#e0e0e0;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
.card{background:rgba(8,16,28,.9);border:1px solid rgba(0,191,255,.12);border-radius:16px;padding:32px;max-width:560px;width:100%}
h1{font-size:22px;font-weight:800;margin-bottom:4px;text-align:center}
h1 span{color:#008cff}
.sub{color:#64748b;font-size:13px;text-align:center;margin-bottom:20px}
.field{margin-bottom:14px}
.field label{display:block;font-size:11px;color:#94a3b8;font-weight:600;margin-bottom:4px}
.field input,.field textarea{width:100%;padding:10px 12px;border-radius:8px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:13px;outline:none;transition:.15s;font-family:inherit}
.field input:focus,.field textarea:focus{border-color:rgba(0,140,255,.4)}
.field textarea{min-height:80px;resize:vertical}
.row{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.btn{width:100%;padding:12px;border-radius:8px;border:none;font-weight:700;font-size:14px;cursor:pointer;transition:.15s;font-family:inherit;margin-top:6px}
.btn-pri{background:linear-gradient(135deg,#008cff,#0066cc);color:#fff}
.btn-pri:hover{transform:translateY(-1px);box-shadow:0 4px 15px rgba(0,140,255,.3)}
.msg{padding:10px 14px;border-radius:8px;font-size:12px;margin-bottom:14px;text-align:center}
.msg.ok{background:rgba(74,222,128,.1);border:1px solid rgba(74,222,128,.2);color:#4ade80}
.msg.err{background:rgba(248,113,113,.1);border:1px solid rgba(248,113,113,.2);color:#f87171}
@media(max-width:480px){.card{padding:20px}.row{grid-template-columns:1fr}}
</style></head><body>
<div class="card">
<h1>PLANET <span>HOSTS</span></h1>
<p class="sub">Apply to become a DJ for <?php echo $stationName; ?></p>
<?php if ($success): ?><div class="msg ok"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
<?php if ($error): ?><div class="msg err"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
<form method="POST" action="/user/radio/dj/apply">
<input type="hidden" name="stream_id" value="<?php echo $streamId; ?>">
<div class="row">
<div class="field"><label>First Name</label><input name="first_name" required></div>
<div class="field"><label>Last Name</label><input name="last_name" required></div>
</div>
<div class="field"><label>Email</label><input type="email" name="email" required></div>
<div class="field"><label>Phone</label><input name="phone"></div>
<div class="field"><label>Desired DJ Name</label><input name="dj_name" placeholder="Your on-air name"></div>
<div class="field"><label>Bio / About You</label><textarea name="bio" placeholder="Tell us about yourself"></textarea></div>
<div class="field"><label>Why do you want to DJ here?</label><textarea name="why_you"></textarea></div>
<div class="field"><label>Previous Experience</label><textarea name="experience" placeholder="Have you DJed before? What software do you use?"></textarea></div>
<button type="submit" class="btn btn-pri">Submit Application</button>
</form>
</div>
</body></html>