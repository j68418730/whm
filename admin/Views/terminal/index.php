<style>
.terminal-wrap{background:#0d1117;border:1px solid rgba(255,255,255,.1);border-radius:8px;overflow:hidden;font-family:'Cascadia Code','Fira Code','Consolas',monospace;font-size:14px;line-height:1.6}
.terminal-bar{background:rgba(255,255,255,.04);padding:8px 14px;display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid rgba(255,255,255,.06)}
.terminal-bar span{color:#8b949e;font-size:12px}
.terminal-body{padding:14px}
.terminal-body pre{color:#c9d1d9;margin:0;min-height:200px;max-height:400px;overflow-y:auto;white-space:pre-wrap;word-break:break-all}
.terminal-body pre .prompt{color:#4ade80}
.terminal-body pre .error{color:#f87171}
.terminal-input-wrap{display:flex;align-items:center;gap:8px;padding:8px 14px;border-top:1px solid rgba(255,255,255,.06)}
.terminal-input-wrap .prompt-symbol{color:#4ade80;font-family:monospace;font-size:14px}
.terminal-input-wrap input{flex:1;background:transparent;border:none;color:#c9d1d9;font-family:monospace;font-size:14px;outline:none}
.terminal-input-wrap input::placeholder{color:#484f58}
</style>
<div class="terminal-wrap">
<div class="terminal-bar"><span>bash — root@<?php echo trim(shell_exec('hostname') ?: 'localhost'); ?>: ~</span><span style="color:#8b949e">/admin/terminal</span></div>
<div class="terminal-body"><pre id="termOutput"><span class="prompt">root@<?php echo trim(shell_exec('hostname') ?: 'localhost'); ?>:~$ </span><span style="color:#8b949e">type a command below</span></pre></div>
<div class="terminal-input-wrap"><span class="prompt-symbol">$</span>
<input type="text" id="termInput" placeholder="Enter command..." autofocus></div>
</div>
<script>
document.getElementById('termInput').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
        var cmd = this.value.trim();
        if (!cmd) return;
        var out = document.getElementById('termOutput');
        var host = 'root@<?php echo trim(shell_exec('hostname') ?: 'localhost'); ?>';
        out.innerHTML += '\n<span class="prompt">' + host + ':$~ </span>' + cmd.replace(/</g,'&lt;');
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
                out.innerHTML += '\n<span class="prompt">' + host + ':$~ </span>';
                out.scrollTop = out.scrollHeight;
            } catch(e) { out.innerHTML += '\n<span class="error">Error parsing response</span>'; }
        };
        x.send('command=' + encodeURIComponent(cmd));
    }
});
</script>
