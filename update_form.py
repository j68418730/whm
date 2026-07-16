import re

content = open('/var/www/radiohosting/user/Views/radio/index.php').read()

new_form = (
'  <div class="card"><h3>Add DJ</h3>\n'
'  <form method="post" action="/user/radio/dj/create">\n'
'    <input type="hidden" name="station_id" value="<?=$stationId?>">\n'
'    <div class="form-row"><div class="form-group"><label>Username</label><input class="inp inp-sm" name="username" required></div><div class="form-group"><label>Password</label><input class="inp inp-sm" type="password" name="password" required></div></div>\n'
'    <div class="form-row"><div class="form-group"><label>Display Name</label><input class="inp inp-sm" name="name"></div><div class="form-group"><label>Email</label><input class="inp inp-sm" type="email" name="email"></div></div>\n'
'    <div class="form-group"><label>Bio</label><textarea class="inp inp-sm" name="bio" rows="2"></textarea></div>\n'
'    <div class="form-group"><label>Role</label><div style="display:flex;gap:12px"><label style="display:flex;align-items:center;gap:4px;font-size:11px;color:#c0c0c0"><input type="radio" name="role" value="dj" checked> DJ</label><label style="display:flex;align-items:center;gap:4px;font-size:11px;color:#c0c0c0"><input type="radio" name="role" value="mod"> Mod</label></div></div>\n'
'\n'
'    <!-- Multi-Station Assignment -->\n'
'    <div style="margin-top:16px;padding:16px;background:rgba(251,146,60,.08);border:1px solid rgba(251,146,60,.2);border-radius:8px">\n'
'      <label style="display:block;margin-bottom:12px;font-size:13px;font-weight:600;color:var(--text-secondary)">Assigned Stations</label>\n'
'      <p style="font-size:12px;color:var(--text-muted);margin-bottom:12px">Select stations this DJ can access. Primary station is the one currently being viewed.</p>\n'
'      <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:8px">\n'
'        <?php\n'
'        $allStations = $stations ?? [];\n'
'        $currentStationId = (int)$stationId;\n'
'        foreach ($allStations as $st):\n'
'            $isCurrent = ($st->id ?? 0) == $currentStationId;\n'
'            $checked = $isCurrent ? "checked" : "";\n'
'            $disabled = $isCurrent ? "disabled" : "";\n'
'        ?>\n'
'        <label style="display:flex;align-items:center;gap:8px;padding:8px;background:rgba(0,0,0,.2);border:1px solid rgba(255,255,255,.08);border-radius:6px;cursor:pointer;font-size:13px;color:#e0e0e0;transition:all .15s">\n'
'          <input type="checkbox" name="station_ids[]" value="<?php echo $st->id; ?>" ' + ('checked ' if True else '') + ('disabled ' if True else '') + 'style="margin:0;transform:scale(1.1)">\n'
'          <span><?php echo htmlspecialchars($st->name ?? "Stream #" . ($st->id ?? 0)) . (" (Primary)" if True else ""); ?></span>\n'
'        </label>\n'
'        <?php endforeach; ?>\n'
'      </div>\n'
'    </div>\n'
'\n'
'    <button class="btn btn-sm btn-primary">Add DJ</button>\n'
'  </form></div>'
)

content = open('/var/www/radiohosting/user/Views/radio/index.php', 'r').read()

# Find the form start
start = content.find('<div class="card"><h3>Add DJ</h3>')
if start == -1:
    print("ERROR: Could not find form")
    exit(1)

# Find the end of the form (after </form></div>)
form_end = content.find('</form>', start)
div_end = content.find('</div>', content.find('</form>', content.find('<div class="card"><h3>Add DJ</h3>'))) + 6

new_content = content[:content.index('<div class="card"><h3>Add DJ</h3>')] + new_form + content[div_end:]

if 'Assigned Stations' in new_content:
    open('/var/www/radiohosting/user/Views/radio/index.php', 'w').write(new_content)
    print("SUCCESS: Form updated with multi-station checkboxes")
else:
    print("ERROR: Update failed")