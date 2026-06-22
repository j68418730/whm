<style>
.term-container{background:#0d1117;border:1px solid rgba(255,255,255,.1);border-radius:10px;overflow:hidden;font-family:'Cascadia Code','Fira Code','Consolas',monospace;font-size:14px;line-height:1.6;box-shadow:0 4px 24px rgba(0,0,0,.4)}
.term-bar{background:rgba(255,255,255,.04);padding:10px 16px;display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid rgba(255,255,255,.06)}
.term-bar .term-title{color:#8b949e;font-size:12px;display:flex;align-items:center;gap:8px}
.term-bar .term-title .dot{width:10px;height:10px;border-radius:50%;display:inline-block}
.term-bar .term-title .dot.red{background:#ff5f56}
.term-bar .term-title .dot.yellow{background:#ffbd2e}
.term-bar .term-title .dot.green{background:#27c93f}
.term-bar .term-info{color:#8b949e;font-size:11px;display:flex;gap:12px}
.term-body{padding:16px;min-height:300px;max-height:500px;overflow-y:auto;background:rgba(0,0,0,.3)}
.term-body pre{margin:0;color:#c9d1d9;white-space:pre-wrap;word-break:break-all}
.term-body pre .prompt{color:#4ade80}
.term-body pre .error{color:#f87171}
.term-body pre .dim{color:#484f58}
.term-input-wrap{display:flex;align-items:center;gap:8px;padding:10px 16px;border-top:1px solid rgba(255,255,255,.06);background:rgba(0,0,0,.2)}
.term-input-wrap .prompt-symbol{color:#4ade80;font-family:monospace;font-size:14px;white-space:nowrap}
.term-input-wrap input{flex:1;background:transparent;border:none;color:#c9d1d9;font-family:monospace;font-size:14px;outline:none}
.term-input-wrap input::placeholder{color:#484f58}
.term-input-wrap .exec-btn{background:rgba(0,140,255,.15);border:1px solid rgba(0,140,255,.2);color:#008cff;padding:6px 14px;border-radius:6px;cursor:pointer;font-size:12px;font-weight:600;transition:.15s}
.term-input-wrap .exec-btn:hover{background:rgba(0,140,255,.25)}
.term-cwd{display:block;padding:8px 16px;background:rgba(255,255,255,.02);color:#8b949e;font-size:11px;border-top:1px solid rgba(255,255,255,.04)}
</style>

<div class="term-container">
<div class="term-bar">
<div class="term-title">
<span class="dot red"></span><span class="dot yellow"></span><span class="dot green"></span>
SSH Terminal — root@<?php echo htmlspecialchars($hostname); ?>
</div>
<div class="term-info">
<span id="termCwdDisplay"><?php echo htmlspecialchars($cwd); ?></span>
</div>
</div>
<div class="term-body" id="termBody">
<pre id="termOutput"><span class="prompt">root@<?php echo htmlspecialchars($hostname); ?>:~$ </span><span class="dim">Welcome. Type a command and press Enter.</span></pre>
</div>
<div class="term-cwd" id="cwdBar">📁 <span id="cwdPath"><?php echo htmlspecialchars($cwd); ?></span></div>
<div class="term-input-wrap">
<span class="prompt-symbol">$</span>
<input type="text" id="termInput" placeholder="Enter command..." autofocus autocomplete="off" spellcheck="false">
<button class="exec-btn" id="execBtn">Execute</button>
</div>
</div>

<script>
(function() {
    var input = document.getElementById('termInput');
    var output = document.getElementById('termOutput');
    var cwdPath = document.getElementById('cwdPath');
    var cwdDisplay = document.getElementById('termCwdDisplay');
    var execBtn = document.getElementById('execBtn');
    var hostname = 'root@<?php echo htmlspecialchars($hostname); ?>';
    var currentCwd = '<?php echo htmlspecialchars($cwd); ?>';

    function executeCommand(cmd) {
        if (!cmd.trim()) return;
        output.innerHTML += '\n<span class="prompt">' + hostname + ':$~ </span>' + cmd.replace(/</g,'&lt;');
        output.scrollTop = output.scrollHeight;

        var x = new XMLHttpRequest();
        x.open('POST', '/admin/server/terminal/exec', true);
        x.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        x.onload = function() {
            try {
                var r = JSON.parse(x.responseText);
                var txt = (r.output || '').replace(/</g,'&lt;');
                if (r.code) {
                    output.innerHTML += '\n<span class="error">' + txt + '</span>';
                } else if (txt) {
                    output.innerHTML += '\n' + txt;
                }
                if (r.cwd) {
                    currentCwd = r.cwd;
                    cwdPath.textContent = r.cwd;
                    cwdDisplay.textContent = r.cwd;
                }
                output.innerHTML += '\n<span class="prompt">' + hostname + ':$~ </span>';
                output.scrollTop = output.scrollHeight;
            } catch(e) {
                output.innerHTML += '\n<span class="error">Error parsing response</span>';
            }
        };
        x.onerror = function() {
            output.innerHTML += '\n<span class="error">Connection error</span>';
        };
        x.send('command=' + encodeURIComponent(cmd) + '&cwd=' + encodeURIComponent(currentCwd));
    }

    input.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            executeCommand(this.value);
            this.value = '';
        }
    });

    execBtn.addEventListener('click', function() {
        executeCommand(input.value);
        input.value = '';
        input.focus();
    });
})();
</script>
