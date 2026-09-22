<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Knowledgebase</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{background:#0a0f1a;color:#e2e8f0;font-family:-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;min-height:100vh}
.wrap{max-width:900px;margin:0 auto;padding:40px 20px 80px}
.top{display:flex;align-items:center;gap:12px;margin-bottom:32px}
.logo{width:40px;height:40px;border-radius:10px;background:linear-gradient(135deg,#147ff0,#0c65c8);display:grid;place-items:center;font-size:20px;font-weight:800;color:#fff}
.top h1{font-size:22px}
.top .tag{font-size:12px;color:#8ea8c5}
.search{width:100%;padding:14px 18px;border-radius:12px;border:1px solid #1c3d60;background:#0c2340;color:#e2e8f0;font-size:15px;outline:none;margin-bottom:28px}
.search:focus{border-color:#1683ff}
.grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}
@media(max-width:640px){.grid{grid-template-columns:1fr}}
.card{background:#0e1f38;border:1px solid #1c3d60;border-radius:12px;padding:18px;transition:.15s}
.card:hover{border-color:#1683ff;transform:translateY(-1px)}
.card h3{font-size:14px;color:#fff;margin-bottom:10px;display:flex;justify-content:space-between;align-items:center}
.card .count{font-size:11px;color:#8ea8c5;background:rgba(22,131,255,.1);padding:3px 8px;border-radius:20px}
.card a{display:block;color:#a8c6e8;text-decoration:none;font-size:13px;padding:7px 0;border-bottom:1px solid rgba(255,255,255,.05)}
.card a:last-child{border-bottom:0}
.card a:hover{color:#1683ff}
.empty{text-align:center;color:#8ea8c5;padding:40px 0}
.foot{text-align:center;color:#5b7290;font-size:12px;margin-top:50px}
.foot a{color:#1683ff;text-decoration:none}
</style>
</head>
<body>
<div class="wrap">
<div class="top">
<div class="logo">K</div>
<div>
<h1>Knowledgebase</h1>
<div class="tag">Guides, help &amp; answers for <?php echo htmlspecialchars($_SERVER['HTTP_HOST'] ?? ''); ?></div>
</div>
</div>
<input class="search" placeholder="Search articles…" oninput="filter(this.value)">
<div class="grid">
<?php $byCat = []; foreach ($articles as $a) $byCat[(int)($a->category_id ?? 0)][] = $a; ?>
<?php if (empty($articles)): ?><div class="empty">No articles published yet.</div><?php endif; ?>
<?php foreach ($byCat as $catId => $list): ?>
<div class="card">
<h3><?php echo htmlspecialchars($catNames[$catId] ?? 'General'); ?><span class="count"><?php echo count($list); ?></span></h3>
<?php foreach ($list as $a): ?><a href="#" onclick="return false" title="<?php echo htmlspecialchars(strip_tags($a->content ?? '')); ?>"><?php echo htmlspecialchars($a->title ?? ''); ?></a><?php endforeach; ?>
</div>
<?php endforeach; ?>
</div>
<div class="foot">© <?php echo date('Y'); ?> <?php echo htmlspecialchars($_SERVER['HTTP_HOST'] ?? ''); ?> · <a href="/">Home</a></div>
</div>
<script>
function filter(q){q=q.toLowerCase();document.querySelectorAll('.card').forEach(function(c){var ok=c.querySelectorAll('a').length&&(c.textContent.toLowerCase().indexOf(q)>-1);c.style.display=q&&!ok?'none':'';});}
</script>
</body>
</html>