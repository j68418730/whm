<div style="max-width:800px;margin:0 auto">
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
<h3 style="margin:0;font-size:18px"><i class="bi bi-images" style="color:#8b5cf6"></i> AI Images</h3>
<a href="/user/websites/ai" class="btn btn-sm secondary" style="font-size:10px">Back</a>
</div>
<p style="color:#64748b;font-size:12px;margin-bottom:16px">Generate images for your website using AI.</p>
<div class="card" style="padding:20px;margin-bottom:14px">
<h4 style="font-size:14px;margin-bottom:8px">Image Prompt</h4>
<textarea id="imagePrompt" rows="3" placeholder="e.g. A modern office with team collaborating, digital art style" style="width:100%;padding:10px 14px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#fff;font-size:12px;outline:none;resize:vertical"></textarea>
<div style="display:flex;gap:8px;margin-top:10px;flex-wrap:wrap;align-items:center">
<select id="imageModel" style="padding:6px 10px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#fff;font-size:11px">
<option value="openai">OpenAI DALL-E</option>
<option value="stability">Stable Diffusion (placeholder)</option>
<option value="flux">Flux (placeholder)</option>
</select>
<select id="imageSize" style="padding:6px 10px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#fff;font-size:11px">
<option value="1024x1024">Square</option><option value="1792x1024">Landscape</option><option value="1024x1792">Portrait</option>
</select>
<button id="generateImageBtn" class="btn primary" style="font-size:12px;padding:8px 20px"><i class="bi bi-magic"></i> Generate</button>
</div>
</div>
<div id="imageResult" style="display:none;text-align:center" class="card" style="padding:20px">
<img id="generatedImage" src="" alt="Generated image" style="max-width:100%;border-radius:10px;max-height:400px">
<div style="margin-top:10px">
<button class="btn btn-sm primary" onclick="var i=document.getElementById('generatedImage');fetch(i.src).then(function(r){return r.blob()}).then(function(b){var a=document.createElement('a');a.href=URL.createObjectURL(b);a.download='ai-image.png';a.click()})"><i class="bi bi-download"></i> Download</button>
</div>
</div>
<div id="imageLoading" style="display:none;text-align:center;padding:40px">
<div style="font-size:40px;color:#8b5cf6"><i class="bi bi-arrow-repeat spinning"></i></div>
<p style="color:#94a3b8;font-size:13px;margin-top:8px">Generating your image...</p>
</div>
</div>
<script>
document.getElementById('generateImageBtn').addEventListener('click',function(){
var prompt=document.getElementById('imagePrompt').value;if(!prompt){alert('Enter a prompt');return;}
var model=document.getElementById('imageModel').value;var size=document.getElementById('imageSize').value;
var btn=this;btn.disabled=true;document.getElementById('imageLoading').style.display='block';document.getElementById('imageResult').style.display='none';
fetch('/user/websites/ai/images/generate',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},
body:'prompt='+encodeURIComponent(prompt)+'&model='+model+'&size='+size})
.then(function(r){return r.json();}).then(function(d){
document.getElementById('imageLoading').style.display='none';
if(d.success){document.getElementById('generatedImage').src=d.url;document.getElementById('imageResult').style.display='block';}
else{alert(d.error||'Failed');}
btn.disabled=false;
}).catch(function(){document.getElementById('imageLoading').style.display='none';btn.disabled=false;alert('Connection error');});
});
</script>
