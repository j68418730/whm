<?php
$content = file_get_contents('/var/www/radiohosting/user/Views/radio/index.php');

$newForm = '
  <div class="card"><h3>Add DJ</h3>
  <form method="post" action="/user/radio/dj/create">
    <input type="hidden" name="station_id" value="<?=$stationId?>">
    <div class="form-row"><div class="form-group"><label>Username</label><input class="inp inp-sm" name="username" required></div><div class="form-group"><label>Password</label><input class="inp inp-sm" type="password" name="password" required></div></div>
    <div class="form-row"><div class="form-group"><label>Display Name</label><input class="inp inp-sm" name="name"></div><div class="form-group"><label>Email</label><input class="inp inp-sm" type="email" name="email"></div></div>
    <div class="form-group"><label>Bio</label><textarea class="inp inp-sm" name="bio" rows="2"></textarea></div>
    <div class="form-group"><label>Role</label><div style="display:flex;gap:12px"><label style="display:flex;align-items:center;gap:4px;font-size:11px;color:#c0c0c0"><input type="radio" name="role" value="dj" checked> DJ</label><label style="display:flex;align-items:center;gap:4px;font-size:11px;color:#c0c0c0"><input type="radio" name="role" value="mod"> Mod</label></div></div>

    <!-- Multi-Station Assignment -->
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
          <input type="checkbox" name="station_ids[]" value="<?php echo $st->id; ?>" ' . ($isCurrent ? "checked" : "") . ' ' . ($isCurrent ? "disabled" : "") . ' style="margin:0;transform:scale(1.1)">
          <span><?php echo htmlspecialchars($st->name ?? "Stream #" . ($st->id ?? 0)); ?> ' . ($isCurrent ? "(Primary)" : "") . '</span>
        </label>
        <?php endforeach; ?>
      </div>
    </div>

    <button class="btn btn-sm btn-primary">Add DJ</button>
  </form></div>';

$content = file_get_contents('/var/www/radiohosting/user/Views/radio/index.php');
if (strpos($content, '<div class="card"><h3>Add DJ</h3>') !== false) {
    $pattern = '/<div class="card"><h3>Add DJ<\/h3>.*?<\/form><\/div>/s';
    $content = preg_replace($pattern, $newForm, $content, 1);
    
    if (strpos($content, 'Assigned Stations') !== false) {
        file_put_contents('/var/www/radiohosting/user/Views/radio/index.php', $content);
        echo "SUCCESS: Form updated\n";
    } else {
        echo "ERROR: Replacement failed\n";
    }
} else {
    echo "ERROR: Form not found\n";
}