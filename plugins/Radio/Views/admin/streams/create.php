<div class="card">
<h3>Create New Stream</h3>
<p style="color:var(--text_muted);font-size:13px;margin-bottom:14px">Creates a new Icecast stream assigned to a client. Port is auto-assigned from available range (6000-10000).</p>

<form action="/admin/streams/create" method="post">
<div class="row g-3">
<div class="col-md-6">
<div class="form-group"><label>Stream Name</label><input name="server_name" class="form-control" placeholder="e.g. Main Radio" required></div>
</div>
<div class="col-md-6">
<div class="form-group"><label>Assign to Client</label>
<select name="user_id" class="form-select" required>
<option value="">Select a client...</option>
<?php foreach ($users as $u): ?>
<option value="<?php echo $u->id; ?>"><?php echo htmlspecialchars($u->username); ?> (<?php echo htmlspecialchars($u->email); ?>)</option>
<?php endforeach; ?>
</select></div>
</div>
<div class="col-md-4">
<div class="form-group"><label>Mount Point</label><input name="mount_point" class="form-control" value="/live" placeholder="/live"></div>
</div>
<div class="col-md-4">
<div class="form-group"><label>Bitrate</label>
<select name="bitrate" class="form-select">
<option value="64">64 kbps</option><option value="96">96 kbps</option><option value="128" selected>128 kbps</option>
<option value="192">192 kbps</option><option value="256">256 kbps</option><option value="320">320 kbps</option>
</select></div>
</div>
<div class="col-md-4">
<div class="form-group"><label>Format</label>
<select name="format" class="form-select">
<option value="mp3">MP3</option><option value="aac">AAC</option><option value="ogg">OGG</option>
</select></div>
</div>
<div class="col-12">
<div class="form-group"><label>Source Password</label>
<input name="password" class="form-control" placeholder="Leave blank for auto-generated">
<small style="color:var(--text_muted);font-size:11px">If blank, a random 16-char password is generated.</small>
</div>
</div>
<div class="col-12">
<button type="submit" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Create Stream</button>
<a href="/admin/streams" class="btn btn-secondary">Cancel</a>
</div>
</div>
</form>
</div>
