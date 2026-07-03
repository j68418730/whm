<style>
.theme-card{background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.08);border-radius:10px;padding:14px;cursor:pointer;transition:.2s;text-align:center}
.theme-card:hover{border-color:rgba(139,92,246,.3);transform:translateY(-2px)}
.theme-card.selected{border-color:#8b5cf6;box-shadow:0 0 15px rgba(139,92,246,.15)}
.theme-card .preview-box{height:80px;border-radius:6px;margin-bottom:8px;display:flex;align-items:flex-end;padding:8px;position:relative;overflow:hidden}
.theme-card .preview-box .bar{height:30%;border-radius:3px;flex:1;margin:0 2px;opacity:.7}
</style>
<div style="max-width:800px;margin:0 auto">
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
<h3 style="margin:0;font-size:18px"><i class="bi bi-palette2" style="color:#8b5cf6"></i> AI Themes</h3>
<a href="/user/websites/ai" class="btn btn-sm secondary" style="font-size:10px">Back</a>
</div>
<p style="color:#64748b;font-size:12px;margin-bottom:16px">Generate or select a theme for your website. Describe a style or use existing templates.</p>
<div class="card" style="padding:16px;margin-bottom:14px">
<h4 style="font-size:13px;margin-bottom:8px">Describe your desired theme</h4>
<textarea id="themePrompt" rows="2" placeholder="e.g. Dark futuristic theme with neon accents, or Clean minimalist with pastel colors" style="width:100%;padding:8px 12px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#fff;font-size:12px;outline:none;resize:vertical"></textarea>
<button id="generateThemeBtn" class="btn primary" style="font-size:12px;margin-top:8px;padding:8px 20px"><i class="bi bi-magic"></i> Generate Themes</button>
</div>
<div id="generatedThemes" style="display:none">
<h4 style="font-size:13px;margin-bottom:10px">Generated Themes</h4>
<div id="themeGrid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:12px"></div>
</div>
<h4 style="font-size:13px;margin:14px 0 10px">Existing Themes</h4>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:12px">
<?php foreach ($themes as $t): ?>
<div class="theme-card" onclick="applyTheme(<?php echo $t->id; ?>,this)">
<div class="preview-box" style="background:linear-gradient(135deg,<?php echo $t->primary_color ?? '#0A84FF'; ?>,<?php echo $t->secondary_color ?? '#4ade80'; ?>)">
<div style="position:absolute;bottom:4px;left:8px;font-size:9px;color:#fff;text-shadow:0 1px 3px rgba(0,0,0,.5)"><?php echo htmlspecialchars($t->name); ?></div>
</div>
<p style="font-size:10px;color:#94a3b8"><?php echo htmlspecialchars($t->slug ?? ''); ?></p>
</div>
<?php endforeach; ?>
</div>
</div>
<script>
function applyTheme(id,el){el.classList.toggle('selected');
fetch('/user/websites/ai/themes/apply',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},
body:'theme_id='+id+'&site_id=<?php echo $site->id ?? 0; ?>'})
.then(function(r){return r.json();}).then(function(d){if(d.success)alert('Theme applied!');else alert(d.error||'Failed');});
}
document.getElementById('generateThemeBtn').addEventListener('click',function(){
var prompt=document.getElementById('themePrompt').value;if(!prompt)return;
var btn=this;btn.disabled=true;btn.innerHTML='<i class="bi bi-hourglass"></i> Generating...';
fetch('/user/websites/ai/themes/generate',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},
body:'prompt='+encodeURIComponent(prompt)+'&site_id=<?php echo $site->id ?? 0; ?>'})
.then(function(r){return r.json();}).then(function(d){
if(d.success&&d.themes.length){
var g=document.getElementById('themeGrid');g.innerHTML='';
d.themes.forEach(function(th){g.innerHTML+='<div class="theme-card" onclick="applyTheme('+th.id+',this)"><div class="preview-box" style="background:linear-gradient(135deg,'+th.primary_color+','+th.secondary_color+')"><div style="position:absolute;bottom:4px;left:8px;font-size:9px;color:#fff">'+th.name+'</div></div><p style="font-size:9px;color:#94a3b8">'+th.slug+'</p></div>';});
document.getElementById('generatedThemes').style.display='block';
}
btn.disabled=false;btn.innerHTML='<i class="bi bi-magic"></i> Generate Themes';
}).catch(function(){btn.disabled=false;btn.innerHTML='<i class="bi bi-magic"></i> Generate Themes';});
});
</script>
