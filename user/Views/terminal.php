<div class="card">
<h3>Web Terminal</h3>
<p style="color:#64748b;font-size:13px;margin-bottom:16px">Execute commands on your hosting account via browser-based terminal.</p>
<div style="background:#000;border:1px solid rgba(0,191,255,.1);border-radius:8px;padding:12px;min-height:300px;font-family:'Courier New',monospace;font-size:13px;color:#4ade80;overflow-y:auto" id="termOutput">
<div>Web Terminal v1.0 — Type 'help' for commands</div>
<div id="termCursor" style="display:flex;align-items:center;gap:4px;margin-top:4px">
<span style="color:#0A84FF">$</span>
<input type="text" id="termInput" style="flex:1;background:transparent;border:none;color:#4ade80;font-family:'Courier New',monospace;font-size:13px;outline:none" autofocus placeholder="type a command...">
</div>
</div>
</div>
<script>
var termOut = document.getElementById('termOutput');
var termIn = document.getElementById('termInput');
termIn.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
        var cmd = termIn.value.trim();
        termIn.value = '';
        termOut.insertBefore(document.createElement('div'), termOut.lastElementChild).textContent = '$ ' + cmd;
        if (cmd === 'help') {
            termOut.insertBefore(document.createElement('div'), termOut.lastElementChild).textContent = 'Available: ls, pwd, whoami, df -h, free -m, uptime';
        } else if (cmd === 'clear') {
            termOut.innerHTML = '<div>Web Terminal v1.0 — Type \'help\' for commands</div>';
            termOut.appendChild(termOut.lastElementChild);
        } else if (cmd) {
            var x = new XMLHttpRequest();
            x.open('POST', '/admin/server/terminal/exec', true);
            x.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            x.onload = function() {
                try { var d = JSON.parse(x.responseText); termOut.insertBefore(document.createElement('div'), termOut.lastElementChild).textContent = d.output || 'No output'; } catch(e) { termOut.insertBefore(document.createElement('div'), termOut.lastElementChild).textContent = x.responseText; }
            };
            x.onerror = function() { termOut.insertBefore(document.createElement('div'), termOut.lastElementChild).textContent = 'Connection error'; };
            x.send('command=' + encodeURIComponent(cmd));
        }
        termOut.scrollTop = termOut.scrollHeight;
    }
});
</script>
