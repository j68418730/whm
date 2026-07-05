<div style="max-width:800px;margin:0 auto">
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
<h3 style="margin:0;font-size:18px"><i class="bi bi-palette" style="color:#8b5cf6"></i> AI Branding</h3>
<a href="/user/websites/ai" class="btn btn-sm secondary" style="font-size:10px">Back</a>
</div>
<p style="color:#64748b;font-size:12px;margin-bottom:16px">Generate a complete brand identity — colors, typography, and logo suggestions.</p>
<div class="card" style="padding:20px;margin-bottom:14px">
<h4 style="font-size:14px;margin-bottom:8px">Describe your brand</h4>
<textarea id="brandDesc" rows="3" placeholder="e.g. Modern tech startup focusing on AI solutions. Target audience: young professionals. Professional yet friendly tone." style="width:100%;padding:10px 14px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#fff;font-size:12px;outline:none;resize:vertical"><?php echo $context['writing_style'] ?? ''; ?></textarea>
<div style="display:flex;gap:8px;margin-top:10px">
<button id="generateBrandBtn" class="btn primary" style="font-size:12px;padding:8px 20px"><i class="bi bi-magic"></i> Generate Brand Identity</button>
</div>
</div>
<div id="brandResult" style="display:none">
<div class="card" style="padding:20px;margin-bottom:14px">
<h4 style="font-size:14px;margin-bottom:10px">Color Palette</h4>
<div id="paletteDisplay" style="display:flex;gap:8px;flex-wrap:wrap"></div>
</div>
<div class="card" style="padding:20px;margin-bottom:14px">
<h4 style="font-size:14px;margin-bottom:10px">Typography</h4>
<div id="fontsDisplay" style="font-size:13px;color:#e0e0e0"></div>
</div>
</div>
<?php if (!empty($context['brand_colors'])): ?>
<div class="card" style="padding:14px">
<h4 style="font-size:12px;color:#94a3b8;margin-bottom:8px">Current Brand Memory</h4>
<div style="display:flex;gap:8px;flex-wrap:wrap">
<?php foreach (($context['brand_colors'] ?? []) as $k=>$v): if (!is_string($v)) continue; ?>
<span style="display:flex;align-items:center;gap:4px;padding:3px 8px;background:rgba(0,0,0,.15);border-radius:4px;font-size:10px"><span style="display:inline-block;width:12px;height:12px;border-radius:2px;background:<?php echo $v; ?>"></span><?php echo $k; ?>: <?php echo $v; ?></span>
<?php endforeach; ?>
</div></div>
<?php endif; ?>
</div>
<script>
document.getElementById('generateBrandBtn').addEventListener('click',function(){
var desc=document.getElementById('brandDesc').value;if(!desc)desc='Modern professional brand';
var btn=this;btn.disabled=true;btn.innerHTML='<i class="bi bi-hourglass"></i> Generating...';
fetch('/user/websites/ai/branding/generate',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},
body:'site_id=<?php echo ($site->id ?? 0); ?>&description='+encodeURIComponent(desc)})
.then(function(r){return r.json();}).then(function(d){
if(d.success){
var pd=document.getElementById('paletteDisplay');pd.innerHTML='';
Object.keys(d.palette).forEach(function(k){if(typeof d.palette[k]==='string'&&d.palette[k].startsWith('#')){
pd.innerHTML+='<div style="text-align:center"><div style="width:50px;height:50px;border-radius:8px;background:'+d.palette[k]+';border:1px solid rgba(255,255,255,.1)"></div><span style="font-size:9px;color:#94a3b8">'+k+'<br>'+d.palette[k]+'</span></div>';}});
document.getElementById('fontsDisplay').innerHTML='Heading: <strong>'+d.fonts.heading_font+'</strong> &mdash; Body: <strong>'+d.fonts.body_font+'</strong>';
document.getElementById('brandResult').style.display='block';
}
btn.disabled=false;btn.innerHTML='<i class="bi bi-magic"></i> Generate Brand Identity';
}).catch(function(){btn.disabled=false;btn.innerHTML='<i class="bi bi-magic"></i> Generate Brand Identity';});
});
</script>
