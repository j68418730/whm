<style>
.term-wrap{background:#0d1117;border:1px solid rgba(255,255,255,.1);border-radius:8px;overflow:hidden;font-family:'Cascadia Code','Fira Code','Consolas',monospace;font-size:14px;line-height:1.6}
.term-bar{background:rgba(255,255,255,.04);padding:8px 14px;display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid rgba(255,255,255,.06)}
.term-bar span{color:#8b949e;font-size:12px}
.term-body{padding:14px}
.term-body pre{color:#c9d1d9;margin:0;min-height:260px;max-height:420px;overflow-y:auto;white-space:pre-wrap;word-break:break-all}
.term-body pre .prompt{color:#4ade80}
.term-body pre .error{color:#f87171}
.term-input-wrap{display:flex;align-items:center;gap:8px;padding:8px 14px;border-top:1px solid rgba(255,255,255,.06)}
.term-input-wrap .prompt-sym{color:#4ade80;font-family:monospace;font-size:14px}
.term-input-wrap input{flex:1;background:transparent;border:none;color:#c9d1d9;font-family:monospace;font-size:14px;outline:none}
.term-input-wrap input::placeholder{color:#484f58}
</style>
<div class="card" style="padding:0;overflow:hidden">
<div class="term-wrap">
<div class="term-bar"><span>bash — root@<?php echo htmlspecialchars($hostname ?? 'localhost'); ?></span><span style="color:#8b949e">/admin/terminal</span></div>
<div class="term-body"><pre id="termOutput"><span class="prompt">root@<?php echo htmlspecialchars($hostname ?? 'localhost'); ?>:<span class="cwdDisplay"><?php echo htmlspecialchars($cwd ?? '/root'); ?></span>$ </span><span style="color:#8b949e">type a command below</span></pre></div>
<div class="term-input-wrap"><span class="prompt-sym">$</span>
<input type="text" id="termInput" placeholder="Enter command..." autofocus></div>
</div>
</div>
<script>
var currentCwd = '<?php echo addslashes($cwd ?? '/root'); ?>';
var hostName = 'root@<?php echo htmlspecialchars($hostname ?? 'localhost'); ?>';
document.getElementById('termInput').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
        var cmd = this.value.trim();
        if (!cmd) return;
        var out = document.getElementById('termOutput');
                out.innerHTML += '\n<span class="prompt">' + hostName + ':<span class="cwdDisplay">' + currentCwd + '</span>$ </span>' + cmd.replace(/</g,'&lt;');
                this.value = '';
                out.scrollTop = out.scrollHeight;
                var x = new XMLHttpRequest();
                x.open('POST', '/admin/terminal/exec', true);
                x.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
                x.onload = function() {
                    try {
                        var r = JSON.parse(x.responseText);
                        var txt = (r.output || '').replace(/</g,'&lt;');
                        out.innerHTML += '\n' + (r.code ? '<span class="error">' + txt + '</span>' : txt);
                        if (r.cwd) currentCwd = r.cwd;
                        out.innerHTML += '\n<span class="prompt">' + hostName + ':<span class="cwdDisplay">' + currentCwd + '</span>$ </span>';
                        // Update cwd in all prompt spans
                        var cwds = document.querySelectorAll('.cwdDisplay');
                        for (var i=0;i<cwds.length;i++) cwds[i].textContent = currentCwd;
                        out.scrollTop = out.scrollHeight;
            } catch(e) { out.innerHTML += '\n<span class="error">Error parsing response</span>'; }
        };
        x.send('command=' + encodeURIComponent(cmd) + '&cwd=' + encodeURIComponent(currentCwd));
    }
});
</script>