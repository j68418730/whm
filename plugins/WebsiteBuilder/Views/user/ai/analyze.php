<div style="max-width:800px;margin:0 auto">
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
<h3 style="margin:0;font-size:18px"><i class="bi bi-graph-up" style="color:#8b5cf6"></i> AI Site Analysis</h3>
<a href="/user/websites/ai" class="btn btn-sm secondary" style="font-size:10px">Back</a>
</div>
<div class="card" style="padding:20px;margin-bottom:14px">
<h4 style="font-size:14px;margin-bottom:8px">Analyze your website</h4>
<p style="color:#94a3b8;font-size:12px;margin-bottom:10px">Get AI-powered insights on design, SEO, content quality, and performance suggestions.</p>
<button id="analyzeBtn" class="btn primary" style="font-size:12px;padding:8px 20px"><i class="bi bi-search"></i> Analyze My Site</button>
</div>
<div id="analyzeLoading" style="display:none;text-align:center;padding:30px"><div style="font-size:30px;color:#8b5cf6"><i class="bi bi-arrow-repeat spinning"></i></div><p style="color:#94a3b8;margin-top:8px">AI is analyzing your site...</p></div>
<div id="analyzeResult" style="display:none"></div>
</div>
<script>
document.getElementById('analyzeBtn').addEventListener('click',function(){
var btn=this;btn.disabled=true;document.getElementById('analyzeLoading').style.display='block';document.getElementById('analyzeResult').style.display='none';
fetch('/user/websites/ai/analyze/<?php echo $site->id ?? 0; ?>').then(function(r){return r.json();}).then(function(d){
document.getElementById('analyzeLoading').style.display='none';
if(d.success){var r=document.getElementById('analyzeResult');r.innerHTML='';r.style.display='block';
['design','seo','content','performance','recommendations'].forEach(function(cat){
if(d.analysis[cat]){r.innerHTML+='<div class="card" style="padding:14px;margin-bottom:8px"><h5 style="font-size:12px;color:var(--accent);margin-bottom:4px;text-transform:capitalize">'+cat+'</h5><div style="font-size:12px;color:#e0e0e0">'+d.analysis[cat]+'</div></div>';}
});
if(d.analysis.score){r.innerHTML+='<div class="card" style="padding:14px;background:rgba(139,92,246,.08);text-align:center"><strong>Score: '+d.analysis.score+'/100</strong></div>';}
}
btn.disabled=false;}).catch(function(){document.getElementById('analyzeLoading').style.display='none';btn.disabled=false;});
});
</script>
