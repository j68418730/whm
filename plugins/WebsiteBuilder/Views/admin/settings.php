<div class="card" style="margin-bottom:16px">
<div><h3 style="margin:0">Builder Settings</h3><p style="color:var(--text_muted);font-size:12px;margin:2px 0 0">Global website builder configuration</p></div>
</div>
<div class="card">
<form method="POST" action="/admin/websitebuilder/settings/save">
<div style="margin-bottom:12px">
<label style="display:block;font-size:12px;color:var(--text_muted);margin-bottom:4px">Default Domain Suffix</label>
<input type="text" name="default_domain" class="form-control" value="websitebuilder.example.com" style="max-width:300px">
</div>
<div style="margin-bottom:12px">
<label style="display:block;font-size:12px;color:var(--text_muted);margin-bottom:4px">Max Sites Per User</label>
<input type="number" name="max_sites" class="form-control" value="10" style="max-width:150px">
</div>
<div style="margin-bottom:12px">
<label style="display:block;font-size:12px;color:var(--text_muted);margin-bottom:4px">Storage Limit Per Site (MB)</label>
<input type="number" name="storage_limit" class="form-control" value="100" style="max-width:150px">
</div>
<div style="margin-bottom:12px">
<label style="display:block;font-size:12px;color:var(--text_muted);margin-bottom:4px">Allowed File Types</label>
<input type="text" name="allowed_types" class="form-control" value="jpg,jpeg,png,gif,webp,svg,pdf,doc,docx,mp3,mp4,ogg" style="max-width:400px">
</div>
<button type="submit" class="btn btn-sm btn-primary">Save Settings</button>
</form>
</div>
