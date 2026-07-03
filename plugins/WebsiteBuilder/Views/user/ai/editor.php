<style>
.chat-bubble{background:rgba(139,92,246,.12);border:1px solid rgba(139,92,246,.2);border-radius:12px;padding:14px;margin-bottom:10px;font-size:13px;color:#e0e0e0}
.chat-bubble.user{background:rgba(10,132,255,.1);border-color:rgba(10,132,255,.2);text-align:right}
.chat-history{max-height:400px;overflow-y:auto;padding:10px;margin-bottom:12px}
.example-prompts{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:12px}
.example-prompts button{font-size:10px;padding:5px 10px;border-radius:6px;border:1px solid rgba(139,92,246,.2);background:rgba(139,92,246,.08);color:#c4b5fd;cursor:pointer}
.example-prompts button:hover{background:rgba(139,92,246,.15)}
</style>
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;flex-wrap:wrap;gap:8px">
<div><h3 style="margin:0;font-size:16px"><i class="bi bi-chat-dots" style="color:#8b5cf6"></i> AI Edit: <?php echo htmlspecialchars($site->name); ?></h3>
<p style="color:#64748b;font-size:11px;margin:2px 0 0">Type what you want changed in natural language.</p></div>
<a href="/user/websites/<?php echo $site->id; ?>" class="btn btn-sm secondary" style="font-size:10px"><i class="bi bi-arrow-left"></i> Back</a>
</div>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
<div class="card" style="padding:16px">
<h4 style="font-size:13px;margin-bottom:8px;color:var(--accent)">AI Chat</h4>
<div class="chat-history" id="chatHistory">
<div class="chat-bubble">Hello! I can edit your website. Try: "Make it blue", "Add a pricing section", "Rewrite the homepage text".</div>
</div>
<div class="example-prompts" id="examplePrompts">
<button onclick="sendPrompt('Make the website more modern and professional')">Modernize design</button>
<button onclick="sendPrompt('Change the color scheme to dark mode')">Dark mode</button>
<button onclick="sendPrompt('Add a pricing section with 3 plans')">Add pricing</button>
<button onclick="sendPrompt('Rewrite all content to be more engaging')">Rewrite content</button>
</div>
<form id="aiChatForm" style="display:flex;gap:6px">
<input type="hidden" name="site_id" value="<?php echo $site->id; ?>">
<select id="pageSelector" style="padding:6px 10px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#fff;font-size:11px">
<?php foreach ($pages as $p): ?>
<option value="<?php echo $p->id; ?>"><?php echo htmlspecialchars($p->title); ?></option>
<?php endforeach; ?>
</select>
<input type="text" id="aiInstruction" placeholder="Tell AI what to change..." style="flex:1;padding:8px 12px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.4);color:#fff;font-size:12px;outline:none">
<button type="submit" class="btn btn-sm primary" style="font-size:10px;padding:6px 12px"><i class="bi bi-send"></i></button>
</form>
</div>
<div class="card" style="padding:16px">
<h4 style="font-size:13px;margin-bottom:8px;color:var(--accent)">Preview</h4>
<div id="aiPreview" style="background:rgba(0,0,0,.2);border-radius:8px;padding:12px;min-height:300px;font-size:11px;color:#94a3b8">
<p>Your changes will appear here after AI processes them.</p>
</div>
</div>
</div>
<?php if (!empty($context)): ?>
<div class="card" style="margin-top:12px;padding:12px">
<h4 style="font-size:11px;color:#64748b;margin-bottom:6px">AI Project Context</h4>
<div style="font-size:10px;color:#94a3b8;display:flex;gap:10px;flex-wrap:wrap">
<?php if (!empty($context['brand_colors'])): ?><span>Colors: <?php echo json_encode($context['brand_colors']); ?></span><?php endif; ?>
<?php if (!empty($context['writing_style'])): ?><span>Style: <?php echo htmlspecialchars($context['writing_style']); ?></span><?php endif; ?>
</div></div>
<?php endif; ?>
<script>
function sendPrompt(msg){document.getElementById('aiInstruction').value=msg;document.getElementById('aiChatForm').dispatchEvent(new Event('submit'));}
document.getElementById('aiChatForm').addEventListener('submit',function(e){e.preventDefault();
var inst=document.getElementById('aiInstruction').value;if(!inst)return;
var pageId=document.getElementById('pageSelector').value;
var h=document.getElementById('chatHistory');var b=document.createElement('div');
b.className='chat-bubble user';b.textContent=inst;h.appendChild(b);
document.getElementById('aiInstruction').value='';
fetch('/user/websites/ai/edit/apply',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},
body:'site_id=<?php echo $site->id; ?>&page_id='+pageId+'&instruction='+encodeURIComponent(inst)})
.then(function(r){return r.json();}).then(function(d){
var rb=document.createElement('div');rb.className='chat-bubble';
if(d.success){rb.innerHTML='<span style="color:#4ade80">✓ Done!</span> Blocks updated.';document.getElementById('aiPreview').innerHTML='<pre style="color:#4ade80;font-size:9px">'+JSON.stringify(d.blocks||[],null,2).substring(0,2000)+'...</pre>';}
else{rb.innerHTML='<span style="color:#f87171">Error:</span> '+(d.error||'Failed');}
h.appendChild(rb);h.scrollTop=h.scrollHeight;
}).catch(function(){var rb=document.createElement('div');rb.className='chat-bubble';rb.innerHTML='<span style="color:#f87171">Connection error</span>';h.appendChild(rb);});
});
</script>
