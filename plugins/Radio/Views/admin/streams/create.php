<div class="card">
<div class="card-header d-flex justify-content-between align-items-center">
<h3 class="mb-0"><i class="bi bi-magic"></i> Create New Stream</h3>
<span class="badge bg-secondary" id="stepIndicator">Step 1 of 4</span>
</div>
<div class="card-body">

<!-- Progress Bar -->
<div class="progress mb-4" style="height:6px">
<div class="progress-bar" id="progressBar" style="width:25%;background:var(--primary_color)"></div>
</div>

<!-- Step Nav Tabs -->
<ul class="nav nav-pills mb-4 justify-content-center" id="stepTabs">
<li class="nav-item"><a class="nav-link active" data-step="1" href="#">1. Customer</a></li>
<li class="nav-item" style="padding:0 8px;line-height:38px;color:var(--text_muted)"><i class="bi bi-chevron-right"></i></li>
<li class="nav-item"><a class="nav-link" data-step="2" href="#">2. Stream</a></li>
<li class="nav-item" style="padding:0 8px;line-height:38px;color:var(--text_muted)"><i class="bi bi-chevron-right"></i></li>
<li class="nav-item"><a class="nav-link" data-step="3" href="#">3. Network</a></li>
<li class="nav-item" style="padding:0 8px;line-height:38px;color:var(--text_muted)"><i class="bi bi-chevron-right"></i></li>
<li class="nav-item"><a class="nav-link" data-step="4" href="#">4. Review</a></li>
</ul>

<form action="/admin/streams/create" method="post" id="streamWizardForm">
<input type="hidden" name="wizard" value="1">

<!-- ====== STEP 1: CUSTOMER ====== -->
<div class="step step-1">
<h5 class="mb-3"><i class="bi bi-person"></i> Account Information</h5>
<div class="row g-3">
<div class="col-md-6">
<div class="form-group"><label class="required">Client</label>
<select name="user_id" class="form-select" required>
<option value="">Select client...</option>
<?php foreach ($users as $u): ?>
<option value="<?php echo $u->id; ?>" data-username="<?php echo htmlspecialchars($u->username); ?>"><?php echo htmlspecialchars($u->username); ?> (<?php echo htmlspecialchars($u->email); ?>)</option>
<?php endforeach; ?>
</select></div>
</div>
<div class="col-md-6">
<div class="form-group"><label class="required">Package</label>
<select name="package_id" class="form-select" id="packageSelect" required>
<option value="">Select package...</option>
<?php foreach ($packages as $p): ?>
<?php $pFeats = is_string($p->features) ? json_decode($p->features, true) ?? [] : ($p->features ?? []); $sp = $pFeats['streaming_package'] ?? []; ?>
<option value="<?php echo $p->id; ?>"
data-type="<?php echo $p->type; ?>"
data-price="<?php echo $p->monthly_price; ?>"
data-billing="<?php echo $p->billing_cycle; ?>"
data-listeners="<?php echo $sp['max_listeners'] ?? 0; ?>"
data-bitrate="<?php echo $sp['max_bitrate'] ?? 128; ?>"
data-djs="<?php echo $sp['max_djs'] ?? 0; ?>"
data-disk="<?php echo $p->disk_space ?? 10; ?>"
data-bandwidth="<?php echo $p->bandwidth ?? 0; ?>"><?php echo htmlspecialchars($p->name); ?> (<?php echo $p->type; ?> - $<?php echo $p->monthly_price; ?>/mo)</option>
<?php unset($pFeats, $sp); ?>
<?php endforeach; ?>
</select></div>
</div>
<div class="col-md-6">
<div class="form-group"><label class="required">Server Node</label>
<select name="node" class="form-select" required>
<option value="">Select node...</option>
<?php foreach ($nodes as $n): ?>
<option value="<?php echo htmlspecialchars($n); ?>"><?php echo htmlspecialchars($n); ?></option>
<?php endforeach; ?>
</select></div>
</div>
<div class="col-md-6">
<div class="form-group"><label class="required">Stream Name</label>
<input name="server_name" class="form-control" placeholder="e.g. Main Radio" required></div>
</div>
</div>
<div class="mt-4 text-end">
<button type="button" class="btn btn-primary next-step" data-next="2">Next: Stream Settings <i class="bi bi-arrow-right"></i></button>
</div>
</div>

<!-- ====== STEP 2: STREAM ====== -->
<div class="step step-2" style="display:none">
<h5 class="mb-3"><i class="bi bi-music-note"></i> Stream Settings</h5>
<div class="row g-3">
<div class="col-12">
<label class="required">Streaming Engine</label>
<div class="d-flex gap-3 mt-2" id="engineRadios">
<div class="form-check form-check-inline">
<input class="form-check-input" type="radio" name="engine" id="engShoutcast" value="shoutcast" checked>
<label class="form-check-label" for="engShoutcast"><i class="bi bi-megaphone"></i> SHOUTcast v2</label>
</div>
<div class="form-check form-check-inline">
<input class="form-check-input" type="radio" name="engine" id="engShoutcast1" value="shoutcast1">
<label class="form-check-label" for="engShoutcast1"><i class="bi bi-megaphone"></i> SHOUTcast v1</label>
</div>
<div class="form-check form-check-inline">
<input class="form-check-input" type="radio" name="engine" id="engIcecast" value="icecast">
<label class="form-check-label" for="engIcecast"><i class="bi bi-mic"></i> Icecast</label>
</div>
</div>
<small class="text-muted">SHOUTcast v2 recommended. Port auto-assigned: v2=9000-10000, v1=11000-12000, Icecast=8000-9000.</small>
</div>
<div class="col-md-3">
<div class="form-group"><label class="required">Codec</label>
<select name="format" class="form-select" required>
<option value="mp3">MP3</option>
<option value="aac">AAC</option>
<option value="aacp">AAC+</option>
<option value="opus">Opus</option>
</select></div>
</div>
<div class="col-md-3">
<div class="form-group"><label class="required">Bitrate</label>
<select name="bitrate" class="form-select" id="bitrateSelect" required>
<option value="32">32 kbps</option>
<option value="48">48 kbps</option>
<option value="64">64 kbps</option>
<option value="96">96 kbps</option>
<option value="128" selected>128 kbps</option>
<option value="192">192 kbps</option>
<option value="256">256 kbps</option>
<option value="320">320 kbps</option>
</select></div>
</div>
<div class="col-md-3">
<div class="form-group"><label class="required">Max Listeners</label>
<select name="max_listeners" class="form-select" id="listenersSelect" required>
<?php for ($i = 10; $i <= 500; $i += 10): ?>
<option value="<?php echo $i; ?>" <?php echo $i === 100 ? 'selected' : ''; ?>><?php echo $i; ?></option>
<?php endfor; ?>
</select></div>
</div>
<div class="col-md-3">
<div class="form-group"><label>Mount Point</label>
<input name="mount_point" class="form-control" value="/live" placeholder="/live" id="mountPointInput">
<small class="text-muted">Icecast only. Not used by SHOUTcast.</small>
</div>
</div>
<div class="col-md-6">
<div class="form-group"><label>Genres</label>
<input name="genres" class="form-control" placeholder="e.g. Pop, Rock, Talk"></div>
</div>
<div class="col-md-6">
<div class="form-group"><label>Stream Description</label>
<input name="description" class="form-control" placeholder="Brief description of the stream"></div>
</div>
<div class="col-md-4">
<div class="form-group"><label>Language</label>
<select name="language" class="form-select">
<option value="en">English</option>
<option value="es">Spanish</option>
<option value="fr">French</option>
<option value="de">German</option>
<option value="pt">Portuguese</option>
<option value="other">Other</option>
</select></div>
</div>
<div class="col-md-4">
<div class="form-group"><label>Public Directory</label>
<select name="public_server" class="form-select">
<option value="1">Yes - List in public directory</option>
<option value="0">No - Private stream</option>
</select></div>
</div>
<div class="col-md-4">
<div class="form-group"><label>AutoDJ</label>
<select name="autodj_enabled" class="form-select">
<option value="1">Enabled</option>
<option value="0" selected>Disabled</option>
</select>
</div>
</div>
</div>
<div class="mt-4 d-flex justify-content-between">
<button type="button" class="btn btn-secondary prev-step" data-prev="1"><i class="bi bi-arrow-left"></i> Back</button>
<button type="button" class="btn btn-primary next-step" data-next="3">Next: Network <i class="bi bi-arrow-right"></i></button>
</div>
</div>

<!-- ====== STEP 3: NETWORK ====== -->
<div class="step step-3" style="display:none">
<h5 class="mb-3"><i class="bi bi-diagram-3"></i> Network & Authentication</h5>
<div class="row g-3">
<div class="col-md-4">
<div class="form-group"><label>Stream Port</label>
<select name="port" class="form-select" id="portSelect" required>
<option value="">Loading available ports...</option>
</select>
<small class="text-muted">Auto-populated with available ports from the engine range. First port is auto-selected.</small>
</div>
</div>
<div class="col-md-4">
<div class="form-group"><label class="admin-only">Manual Port Override (Admin)</label>
<input name="manual_port" class="form-control" placeholder="Leave blank for auto" type="number" min="1024" max="65535">
<small class="text-muted">Enter a specific port to bypass auto-selection.</small>
</div>
</div>
<div class="col-md-4">
<div class="form-group"><label>SSL</label>
<select name="ssl_enabled" class="form-select">
<option value="1">Enabled</option>
<option value="0" selected>Disabled</option>
</select></div>
</div>
<div class="col-md-6">
<div class="form-group"><label>Hostname</label>
<input name="hostname" class="form-control" value="planet-hosts.com" placeholder="stream.example.com"></div>
</div>
<div class="col-md-6">
<div class="form-group"><label>IP Address</label>
<select name="ip_address" class="form-select" id="serverIpSelect">
<option value="">Loading IPs...</option>
</select>
<small class="text-muted">Select the server IP for the stream.</small>
</div>
</div>
<div class="col-md-6">
<div class="form-group"><label class="required">Source Password</label>
<div class="input-group">
<input name="password" class="form-control" id="sourcePassword" required>
<button type="button" class="btn btn-outline-secondary" id="genSourcePw"><i class="bi bi-dice"></i></button>
</div>
</div>
</div>
<div class="col-md-6">
<div class="form-group"><label>Admin Password</label>
<div class="input-group">
<input name="admin_password" class="form-control" id="adminPassword">
<button type="button" class="btn btn-outline-secondary" id="genAdminPw"><i class="bi bi-dice"></i></button>
</div>
<small class="text-muted">Auto-generated. Used for SHOUTcast admin panel access.</small>
</div>
</div>
</div>
<div class="mt-4 d-flex justify-content-between">
<button type="button" class="btn btn-secondary prev-step" data-prev="2"><i class="bi bi-arrow-left"></i> Back</button>
<button type="button" class="btn btn-primary next-step" data-next="4">Next: Review <i class="bi bi-arrow-right"></i></button>
</div>
</div>

<!-- ====== STEP 4: REVIEW ====== -->
<div class="step step-4" style="display:none">
<h5 class="mb-3"><i class="bi bi-check-circle"></i> Review & Create</h5>
<div class="card bg-light">
<div class="card-body">
<table class="table table-bordered table-sm mb-0" id="reviewTable">
<tr><th style="width:180px">Client</th><td id="reviewClient">-</td></tr>
<tr><th>Package</th><td id="reviewPackage">-</td></tr>
<tr><th>Engine</th><td id="reviewEngine">-</td></tr>
<tr><th>Node</th><td id="reviewNode">-</td></tr>
<tr><th>Stream Name</th><td id="reviewName">-</td></tr>
<tr><th>Port</th><td id="reviewPort">-</td></tr>
<tr><th>SSL</th><td id="reviewSsl">-</td></tr>
<tr><th>Bitrate</th><td id="reviewBitrate">-</td></tr>
<tr><th>Listeners</th><td id="reviewListeners">-</td></tr>
<tr><th>Codec</th><td id="reviewCodec">-</td></tr>
<tr><th>AutoDJ</th><td id="reviewAutodj">-</td></tr>
<tr><th>Mount Point</th><td id="reviewMount">-</td></tr>
<tr><th>Public</th><td id="reviewPublic">-</td></tr>
</table>
</div>
</div>
<div class="mt-4 d-flex justify-content-between">
<button type="button" class="btn btn-secondary prev-step" data-prev="3"><i class="bi bi-arrow-left"></i> Back</button>
<button type="submit" class="btn btn-success btn-lg"><i class="bi bi-check-circle"></i> Create Stream</button>
</div>
</div>

</form>
</div>
</div>

<script>
(function() {
    // Step navigation
    const steps = [1, 2, 3, 4];
    let currentStep = 1;

    function showStep(step) {
        steps.forEach(s => {
            document.querySelector('.step-' + s).style.display = s === step ? 'block' : 'none';
            document.querySelector('[data-step="' + s + '"]')?.classList.toggle('active', s === step);
        });
        document.getElementById('stepIndicator').textContent = 'Step ' + step + ' of 4';
        document.getElementById('progressBar').style.width = (step / 4 * 100) + '%';
        currentStep = step;
    }

    document.querySelectorAll('.next-step').forEach(btn => {
        btn.addEventListener('click', function() {
            var next = parseInt(this.dataset.next);
            // Basic validation on current step
            var stepEl = document.querySelector('.step-' + currentStep);
            var required = stepEl.querySelectorAll('[required]');
            var valid = true;
            required.forEach(function(el) {
                if (!el.value.trim()) {
                    el.classList.add('is-invalid');
                    valid = false;
                } else {
                    el.classList.remove('is-invalid');
                }
            });
            if (!valid) return;
            // If going to step 4, populate review
            if (next === 4) populateReview();
            showStep(next);
        });
    });

    document.querySelectorAll('.prev-step').forEach(btn => {
        btn.addEventListener('click', function() {
            showStep(parseInt(this.dataset.prev));
        });
    });

    document.querySelectorAll('[data-step]').forEach(tab => {
        tab.addEventListener('click', function(e) {
            e.preventDefault();
            // Only allow going back to previous steps or current
            var step = parseInt(this.dataset.step);
            if (step <= currentStep) showStep(step);
        });
    });

    // Password generators
    function genPw(len) {
        var chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*';
        var pw = '';
        for (var i = 0; i < len; i++) pw += chars.charAt(Math.floor(Math.random() * chars.length));
        return pw;
    }

    document.getElementById('genSourcePw').addEventListener('click', function() {
        document.getElementById('sourcePassword').value = genPw(16);
    });
    document.getElementById('genAdminPw').addEventListener('click', function() {
        document.getElementById('adminPassword').value = genPw(16);
    });

    // Auto-fill passwords on load
    if (!document.getElementById('sourcePassword').value) {
        document.getElementById('sourcePassword').value = genPw(16);
    }
    if (!document.getElementById('adminPassword').value) {
        document.getElementById('adminPassword').value = genPw(16);
    }

    // Engine selection affects mount point
    document.querySelectorAll('input[name="engine"]').forEach(function(radio) {
        radio.addEventListener('change', function() {
            var mountInput = document.getElementById('mountPointInput');
            var isIcecast = this.value === 'icecast';
            mountInput.disabled = !isIcecast;
            mountInput.required = isIcecast;
            mountInput.closest('.col-md-3').style.opacity = isIcecast ? '1' : '0.5';
        });
    });

    // Package change updates listener/bitrate limits
    document.getElementById('packageSelect').addEventListener('change', function() {
        var opt = this.options[this.selectedIndex];
        if (opt && opt.dataset.listeners) {
            var maxListeners = parseInt(opt.dataset.listeners);
            if (maxListeners > 0) {
                var sel = document.getElementById('listenersSelect');
                for (var i = 0; i < sel.options.length; i++) {
                    if (parseInt(sel.options[i].value) > maxListeners) {
                        sel.options[i].disabled = true;
                    } else {
                        sel.options[i].disabled = false;
                    }
                }
            }
        }
    });

    // Load available ports from API
    function loadAvailablePorts(engine) {
        var sel = document.getElementById('portSelect');
        sel.innerHTML = '<option value="">Loading ports...</option>';
        sel.disabled = true;
        var x = new XMLHttpRequest();
        x.open('GET', '/admin/api/streaming/available-ports?engine=' + encodeURIComponent(engine), true);
        x.onload = function() {
            try {
                var d = JSON.parse(x.responseText);
                sel.innerHTML = '';
                if (d.ports && d.ports.length > 0) {
                    d.ports.forEach(function(port, idx) {
                        var opt = document.createElement('option');
                        opt.value = port;
                        opt.textContent = port + (idx === 0 ? ' (recommended)' : '');
                        if (idx === 0) opt.selected = true;
                        sel.appendChild(opt);
                    });
                    sel.disabled = false;
                } else {
                    sel.innerHTML = '<option value="">No ports available</option>';
                }
            } catch(e) {
                sel.innerHTML = '<option value="">Error loading ports</option>';
            }
        };
        x.onerror = function() {
            sel.innerHTML = '<option value="">Error loading ports</option>';
        };
        x.send();
    }

    // Load ports when engine changes (Step 2)
    document.querySelectorAll('input[name="engine"]').forEach(function(radio) {
        radio.addEventListener('change', function() {
            // Ports will be loaded when user navigates to Step 3
        });
    });

    // When navigating to Step 3, load ports for selected engine
    document.querySelectorAll('[data-next="3"]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var engine = document.querySelector('input[name="engine"]:checked');
            if (engine) loadAvailablePorts(engine.value);
        });
    });

    // Load server IPs into dropdown
    function loadServerIps() {
        var x = new XMLHttpRequest();
        x.open('GET', '/admin/api/streaming/server-ip', true);
        x.onload = function() {
            try {
                var d = JSON.parse(x.responseText);
                var sel = document.getElementById('serverIpSelect');
                sel.innerHTML = '';
                if (d.ips && d.ips.length > 0) {
                    d.ips.forEach(function(ip) {
                        var opt = document.createElement('option');
                        opt.value = ip;
                        opt.textContent = ip;
                        sel.appendChild(opt);
                    });
                } else if (d.ip) {
                    var opt = document.createElement('option');
                    opt.value = d.ip;
                    opt.textContent = d.ip;
                    sel.appendChild(opt);
                } else {
                    sel.innerHTML = '<option value="">No IPs detected</option>';
                }
            } catch(e) {
                document.getElementById('serverIpSelect').innerHTML = '<option value="">Failed to load IPs</option>';
            }
        };
        x.onerror = function() {
            document.getElementById('serverIpSelect').innerHTML = '<option value="">Failed to load IPs</option>';
        };
        x.send();
    }
    loadServerIps();

    // Populate review from form data
    function populateReview() {
        var userId = document.querySelector('select[name="user_id"]');
        var userName = userId.options[userId.selectedIndex]?.dataset?.username || userId.options[userId.selectedIndex]?.text || '-';
        document.getElementById('reviewClient').textContent = userName;

        var pkg = document.getElementById('packageSelect');
        document.getElementById('reviewPackage').textContent = pkg.options[pkg.selectedIndex]?.text || '-';

        var engine = document.querySelector('input[name="engine"]:checked');
        var engineLabels = { shoutcast: 'SHOUTcast v2', shoutcast1: 'SHOUTcast v1', icecast: 'Icecast' };
        document.getElementById('reviewEngine').textContent = engineLabels[engine?.value] || '-';

        document.getElementById('reviewNode').textContent = document.querySelector('select[name="node"]')?.value || '-';
        document.getElementById('reviewName').textContent = document.querySelector('input[name="server_name"]')?.value || '-';
        document.getElementById('reviewPort').textContent = document.getElementById('portSelect').value || 'Auto-assigned';
        document.getElementById('reviewSsl').textContent = document.querySelector('select[name="ssl_enabled"]')?.value === '1' ? 'Enabled' : 'Disabled';
        document.getElementById('reviewBitrate').textContent = document.querySelector('select[name="bitrate"]')?.value + ' kbps';
        document.getElementById('reviewListeners').textContent = document.querySelector('select[name="max_listeners"]')?.value;
        document.getElementById('reviewCodec').textContent = document.querySelector('select[name="format"]')?.value?.toUpperCase();
        document.getElementById('reviewAutodj').textContent = document.querySelector('select[name="autodj_enabled"]')?.value === '1' ? 'Enabled' : 'Disabled';
        document.getElementById('reviewMount').textContent = document.querySelector('input[name="mount_point"]')?.value || '-';
        document.getElementById('reviewPublic').textContent = document.querySelector('select[name="public_server"]')?.value === '1' ? 'Yes' : 'No';
    }
})();
</script>
