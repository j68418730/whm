<style>
.wizard-step{display:none}.wizard-step.active{display:block}
.wizard-progress{display:flex;gap:6px;margin-bottom:20px}
.wizard-progress .dot{width:10px;height:10px;border-radius:50%;background:rgba(255,255,255,.1);transition:.3s}
.wizard-progress .dot.active{background:#8b5cf6;box-shadow:0 0 8px rgba(139,92,246,.5)}
.wizard-progress .dot.done{background:#4ade80}
</style>
<div style="max-width:680px;margin:0 auto">
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
<h3 style="margin:0"><i class="bi bi-magic" style="color:#8b5cf6"></i> AI Website Wizard</h3>
<a href="/user/websites/ai" class="btn btn-sm secondary" style="font-size:10px">Back</a>
</div>
<p style="color:#64748b;font-size:12px;margin-bottom:16px">Answer a few questions and AI will build your complete website.</p>
<div class="wizard-progress" id="progress"><span class="dot active"></span><span class="dot"></span><span class="dot"></span><span class="dot"></span><span class="dot"></span><span class="dot"></span></div>
<form id="wizardForm" method="POST" action="/user/websites/ai/wizard/generate">
<div class="wizard-step active" data-step="1"><div class="card" style="padding:20px">
<h4 style="margin-bottom:12px">What is your business called?</h4>
<input name="business_name" id="q_name" placeholder="e.g. John's Plumbing" style="width:100%;padding:10px 14px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#fff;font-size:14px;outline:none">
<p style="font-size:11px;color:#64748b;margin-top:6px">This will be your website name and domain.</p>
</div></div>
<div class="wizard-step" data-step="2"><div class="card" style="padding:20px">
<h4 style="margin-bottom:12px">What type of business?</h4>
<select name="business_type" id="q_type" style="width:100%;padding:10px 14px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#fff;font-size:14px">
<option value="">Select...</option><option>Restaurant</option><option>Technology</option><option>Healthcare</option><option>Construction</option><option>Legal</option><option>Real Estate</option><option>Education</option><option>Entertainment</option><option>E-Commerce</option><option>Portfolio</option><option>Photography</option><option>Music</option><option>Gaming</option><option>Streaming</option><option>Radio</option><option>Nonprofit</option><option>Consulting</option><option>Agency</option><option>Other</option>
</select>
</div></div>
<div class="wizard-step" data-step="3"><div class="card" style="padding:20px">
<h4 style="margin-bottom:12px">Describe your business</h4>
<textarea name="description" id="q_desc" rows="4" placeholder="Tell us about your services, what makes you unique, your target audience..." style="width:100%;padding:10px 14px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#fff;font-size:13px;outline:none;resize:vertical"></textarea>
</div></div>
<div class="wizard-step" data-step="4"><div class="card" style="padding:20px">
<h4 style="margin-bottom:12px">Brand color</h4>
<div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap">
<label style="font-size:13px;color:#94a3b8">Pick your primary color:</label>
<input name="primary_color" id="q_color" type="color" value="#0A84FF" style="width:60px;height:60px;border-radius:8px;border:none;cursor:pointer;background:none">
<span id="colorPreview" style="font-size:11px;color:#64748b">#0A84FF</span>
</div>
</div></div>
<div class="wizard-step" data-step="5"><div class="card" style="padding:20px">
<h4 style="margin-bottom:12px">What features do you need?</h4>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
<label style="display:flex;align-items:center;gap:8px;font-size:13px;padding:8px 12px;background:rgba(0,0,0,.15);border-radius:6px;cursor:pointer"><input type="checkbox" name="need_store" value="1"> Online Store</label>
<label style="display:flex;align-items:center;gap:8px;font-size:13px;padding:8px 12px;background:rgba(0,0,0,.15);border-radius:6px;cursor:pointer"><input type="checkbox" name="need_booking" value="1"> Booking System</label>
<label style="display:flex;align-items:center;gap:8px;font-size:13px;padding:8px 12px;background:rgba(0,0,0,.15);border-radius:6px;cursor:pointer"><input type="checkbox" name="need_blog" value="1"> Blog</label>
<label style="display:flex;align-items:center;gap:8px;font-size:13px;padding:8px 12px;background:rgba(0,0,0,.15);border-radius:6px;cursor:pointer"><input type="checkbox" name="need_chat" value="1"> Live Chat</label>
<label style="display:flex;align-items:center;gap:8px;font-size:13px;padding:8px 12px;background:rgba(0,0,0,.15);border-radius:6px;cursor:pointer"><input type="checkbox" name="need_newsletter" value="1"> Newsletter</label>
</div>
</div></div>
<div class="wizard-step" data-step="6"><div class="card" style="padding:20px;text-align:center">
<h4 style="margin-bottom:12px">Ready to generate!</h4>
<p style="color:#94a3b8;font-size:13px;margin-bottom:16px">AI will build a complete website with pages, content, and design based on your answers.</p>
<button type="submit" class="btn primary" style="padding:12px 40px;font-size:15px;font-weight:600"><i class="bi bi-magic"></i> Generate My Website</button>
</div></div>
</form>
<div style="display:flex;justify-content:space-between;margin-top:14px">
<button id="prevBtn" class="btn btn-sm secondary" style="font-size:11px;padding:6px 16px;display:none"><i class="bi bi-arrow-left"></i> Previous</button>
<button id="nextBtn" class="btn btn-sm primary" style="font-size:11px;padding:6px 16px">Next <i class="bi bi-arrow-right"></i></button>
</div>
</div>
<script>
(function(){var s=1,t=6;var f=function(){for(var i=1;i<=t;i++){var e=document.querySelector('.wizard-step[data-step="'+i+'"]');if(e)e.classList.toggle('active',i===s);}var d=document.querySelectorAll('.wizard-progress .dot');d.forEach(function(el,j){el.classList.toggle('active',j+1===s);el.classList.toggle('done',j+1<s);});document.getElementById('prevBtn').style.display=s>1?'inline-block':'none';document.getElementById('nextBtn').textContent=s>=t?'Finish':'Next '+(s>=t?'':'');
if(s>=t){document.getElementById('nextBtn').style.display='none';}};
document.getElementById('nextBtn').addEventListener('click',function(){if(s<t){s++;f();}});
document.getElementById('prevBtn').addEventListener('click',function(){if(s>1){s--;f();}});
document.getElementById('q_color').addEventListener('input',function(){document.getElementById('colorPreview').textContent=this.value;});
})();</script>
