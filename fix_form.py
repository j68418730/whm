import re

new_form = """  <div class="card"><h3>Add DJ</h3>
  <form method="post" action="/user/radio/dj/create">
    <input type="hidden" name="station_id" value="<?=$stationId?>">
    <div class="form-row"><div class="form-group"><label>Username</label><input class="inp inp-sm" name="username" required></div><div class="form-group"><label>Password</label><input class="inp inp-sm" type="password" name="password" required></div></div>
    <div class="form-row"><div class="form-group"><label>Display Name</label><input class="inp inp-sm" name="name"></div><div class="form-group"><label>Email</label><input class="inp inp-sm" type="email" name="email"></div></div>
    <div class="form-group"><label>Bio</label><textarea class="inp inp-sm" name="bio" rows="2"></textarea></div>
    <div class="form-group"><label>Role</label><div style="display:flex;gap:12px"><label style="display:flex;align-items:center;gap:4px;font-size:11px;color:#c0c0c0"><input type="radio" name="role" value="dj" checked> DJ</label><label style="display:flex;align-items:center;gap:4px;font-size:11px;color:#c0c0c0"><input type="radio" name="role" value="mod"> Mod</label></div></div>

    <div style="margin-top:16px;padding:16px;background:rgba(251,146,60,.08);border:1px solid rgba(251,146,60,.2);border-radius:8px">
      <label style="display:block;margin-bottom:12px;font-size:13px;font-weight:600;color:var(--text-secondary)">Assigned Stations</label>
      <p style="font-size:12px;color:var(--text-muted);margin-bottom:12px">Select stations this DJ can access. Primary station is the one currently being viewed.</p>
      <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:8px">
        <?php
        $allStations = $stations ?? [];
        $currentStationId = (int)$stationId;
        foreach ($allStations as $st):
            $isCurrent = ($st->id ?? 0) == $currentStationId;
            $checked = $isCurrent ? "checked" : "";
            $disabled = $isCurrent ? "disabled" : "";
        ?>
        <label style="display:flex;align-items:center;gap:8px;padding:8px;background:rgba(0,0,0,.2);border:1px solid rgba(255,255,255,.08);border-radius:6px;cursor:pointer;font-size:13px;color:#e0e0e0;transition:all .15s">
          <input type="checkbox" name="station_ids[]" value="<?php echo $st->id; ?>" <?php echo $isCurrent ? "checked" : ""; ?> <?php echo $isCurrent ? "disabled" : ""; ?> style="margin:0;transform:scale(1.1)">
          <span><?php echo htmlspecialchars($st->name ?? "Stream #" . ($st->id ?? 0)); ?></span>
        </label>
        <?php endforeach; ?>
      </div>
    </div>

    <button class="btn btn-sm btn-primary">Add DJ</button>
  </form></div>"""

with open("/var/www/radiohosting/user/Views/radio/index.php", "r") as f:
    content = f.read()

if '<div class="card"><h3>Add DJ</h3>' in content:
    pattern = re.compile(r'<div class="card"><h3>Add DJ</h3>.*?</form></div>', re.DOTALL)
    new_content = pattern.sub(new_form, content, count=1)
    
    if "Assigned Stations" in new_content:
        with open("/var/www/radiohosting/user/Views/radio/index.php", "w") as f:
            f.write(new_content)
        print("SUCCESS: Form updated")
    else:
        print("ERROR: Replacement failed")
else:
    print("ERROR: Form not found")