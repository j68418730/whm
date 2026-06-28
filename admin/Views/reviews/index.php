<style>
.tab-btn-cst{border:none;background:transparent;color:var(--text-secondary);cursor:pointer;font-size:13px;padding:8px 16px;border-bottom:2px solid transparent;transition:.15s}
.tab-btn-cst.active{color:var(--accent);border-bottom-color:var(--accent)}
.tab-btn-cst:hover{color:var(--text-primary)}
</style>
<div style="display:flex;gap:0;margin-bottom:16px;border-bottom:1px solid rgba(255,255,255,.06)">
<button class="tab-btn-cst active" onclick="switchRvTab('reviews',this)">📝 Reviews</button>
<button class="tab-btn-cst" onclick="switchRvTab('embed',this)">📋 Embed Code</button>
<button class="tab-btn-cst" onclick="switchRvTab('api',this)">🔌 API</button>
</div>

<div id="rv-reviews">
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
<h3 style="color:var(--accent);margin:0">Customer Reviews</h3>
</div>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(340px,1fr));gap:12px">
<?php foreach ($reviews as $r): ?>
<div class="card" style="margin-bottom:0;padding:16px;border-left:3px solid <?php echo $r->approved ? '#4ade80' : '#facc15'; ?>">
<div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:6px">
<strong style="font-size:14px"><?php echo htmlspecialchars($r->name); ?></strong>
<span style="color:#facc15;font-size:14px"><?php echo str_repeat('★', (int)$r->rating) . str_repeat('☆', 5 - (int)$r->rating); ?></span>
</div>
<div style="font-size:12px;color:var(--text-secondary);margin-bottom:4px"><?php echo nl2br(htmlspecialchars($r->text ?? '')); ?></div>
<div style="font-size:11px;color:#94a3b8;margin-bottom:8px"><?php echo $r->title ? htmlspecialchars($r->title) . ' · ' : ''; ?><?php echo date('M j, Y', strtotime($r->created_at)); ?><?php if ($r->service_rating): ?> · Service: <?php echo str_repeat('★', (int)$r->service_rating); ?><?php endif; ?><?php if ($r->support_rating): ?> · Support: <?php echo str_repeat('★', (int)$r->support_rating); ?><?php endif; ?></div>
<div style="display:flex;gap:4px;align-items:center">
<span style="font-size:11px;font-weight:600;color:<?php echo $r->approved ? '#4ade80' : '#facc15'; ?>"><?php echo $r->approved ? '✓ Approved' : '⏳ Pending'; ?></span>
<?php if (!$r->approved): ?><a href="/admin/reviews/approve/<?php echo $r->id; ?>" class="btn btn-sm primary" style="font-size:11px">✓ Approve</a><?php endif; ?>
<a href="/admin/reviews/deny/<?php echo $r->id; ?>" class="btn btn-sm danger" style="font-size:11px" onclick="return confirm('Deny this review?')">✕ Deny</a>
<a href="/admin/reviews/delete/<?php echo $r->id; ?>" class="btn btn-sm danger" style="font-size:11px" onclick="return confirm('Delete?')">🗑</a>
</div>
</div>
<?php endforeach; ?>
</div>
</div>

<div id="rv-embed" style="display:none">
<div class="card" style="padding:20px;margin-bottom:16px">
<h4 style="color:var(--accent);margin:0 0 8px">Review Widget Embed</h4>
<p style="font-size:12px;color:var(--text-secondary);margin-bottom:12px">Add this to your website to display customer reviews:</p>
<div class="code-block" id="rvEmbedCode" style="background:rgba(0,0,0,.4);border:1px solid rgba(255,255,255,.06);border-radius:8px;padding:16px;font-family:monospace;font-size:13px;overflow-x:auto;white-space:pre;color:#e0e0e0;line-height:1.6">&lt;div id="ph-reviews"&gt;&lt;/div&gt;
&lt;script&gt;
  fetch('https://<?php echo htmlspecialchars($_SERVER['HTTP_HOST'] ?? 'planet-hosts.com'); ?>/api/reviews')
    .then(function(r){return r.json()})
    .then(function(data){
      var html = '&lt;div style="max-width:600px;margin:0 auto"&gt;';
      data.reviews.forEach(function(rv){
        html += '&lt;div style="border:1px solid #eee;border-radius:8px;padding:12px;margin-bottom:8px"&gt;' +
          '&lt;strong&gt;' + rv.name + '&lt;/strong&gt; ' + '★'.repeat(rv.rating) + '☆'.repeat(5-rv.rating) +
          '&lt;p&gt;' + rv.text + '&lt;/p&gt;&lt;/div&gt;';
      });
      html += '&lt;/div&gt;';
      document.getElementById('ph-reviews').innerHTML = html;
    });
&lt;/script&gt;</div>
<button class="copy-btn" style="margin-top:8px;background:rgba(0,191,255,.1);border:1px solid rgba(0,191,255,.2);color:#00bfff;padding:6px 14px;border-radius:6px;cursor:pointer;font-size:12px" onclick="navigator.clipboard.writeText(document.getElementById('rvEmbedCode').textContent)">📋 Copy Embed</button>
</div>
</div>

<div id="rv-api" style="display:none">
<div class="card" style="padding:20px;margin-bottom:16px">
<h4 style="color:var(--accent);margin:0 0 8px">Reviews API</h4>
<p style="font-size:12px;color:var(--text-secondary);margin-bottom:12px">Public endpoint to fetch approved reviews:</p>
<div style="background:rgba(0,0,0,.4);border:1px solid rgba(255,255,255,.06);border-radius:8px;padding:16px;font-family:monospace;font-size:13px;overflow-x:auto;line-height:1.6;color:#e0e0e0"><span style="color:#38bdf8">GET</span> https://<?php echo htmlspecialchars($_SERVER['HTTP_HOST'] ?? 'planet-hosts.com'); ?>/api/reviews

Response:
{
  "reviews": [
    {
      "id": 1,
      "name": "John D.",
      "rating": 5,
      "title": "Great service",
      "text": "Amazing hosting...",
      "created_at": "2026-06-01"
    }
  ],
  "average_rating": 4.7,
  "total": 12
}</div>
</div>
</div>

<script>
function switchRvTab(tab, btn) {
    document.querySelectorAll('.tab-btn-cst').forEach(function(b){b.classList.remove('active')});
    if (btn) btn.classList.add('active');
    document.getElementById('rv-reviews').style.display = tab === 'reviews' ? 'block' : 'none';
    document.getElementById('rv-embed').style.display = tab === 'embed' ? 'block' : 'none';
    document.getElementById('rv-api').style.display = tab === 'api' ? 'block' : 'none';
}
</script>