<style>
.apply-card{max-width:500px;margin:20px auto}
.apply-card h3{text-align:center;margin-bottom:16px}
</style>
<h2>🎤 Apply to be a DJ</h2>
<p style="color:#64748b;margin-bottom:16px">Submit your application to become a radio DJ on our station.</p>
<?php
$existing = $hosting ? $this->db->table('radio_djs')->where('stream_id', $streams[0]->id ?? 0)->where('email', $hosting->email)->first() : null;
if ($existing): ?>
<div class="card apply-card"><h3>✅ Already a DJ</h3>
<p style="color:#4ade80;text-align:center">You already have a DJ account: <strong><?php echo htmlspecialchars($existing->name ?? $existing->username);?></strong></p>
<a href="/user/dj-panel" class="btn btn-primary" style="display:block;text-align:center;margin-top:12px">Go to DJ Panel</a></div>
<?php else: ?>
<div class="card apply-card">
<form method="POST" action="/user/dj/apply">
<h3>DJ Application</h3>
<div style="margin-bottom:8px"><label style="font-size:11px;color:#64748b">DJ Name</label>
<input name="dj_name" required placeholder="Your radio name" style="width:100%;padding:8px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:13px;outline:none"></div>
<div style="margin-bottom:8px"><label style="font-size:11px;color:#64748b">Email</label>
<input name="email" type="email" value="<?php echo htmlspecialchars($hosting->email ?? '');?>" style="width:100%;padding:8px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:13px;outline:none"></div>
<div style="margin-bottom:8px"><label style="font-size:11px;color:#64748b">Genres (comma-separated)</label>
<input name="genres" placeholder="House, Trance, Hip Hop" style="width:100%;padding:8px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:13px;outline:none"></div>
<div style="margin-bottom:8px"><label style="font-size:11px;color:#64748b">Bio / About You</label>
<textarea name="bio" rows="4" placeholder="Tell us about yourself..." style="width:100%;padding:8px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:13px;outline:none"></textarea></div>
<button type="submit" class="btn btn-primary" style="width:100%;padding:10px">📤 Submit Application</button>
</form></div>
<?php endif; ?>
