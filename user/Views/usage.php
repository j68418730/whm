<div class="card"><h3 style="color:var(--accent)">Resource Usage</h3>
<div style="margin-top:12px">
<div style="margin-bottom:16px"><div style="display:flex;justify-content:space-between;font-size:13px"><span>Disk Storage</span><span><?php echo $diskUsed ?? 0; ?> GB / <?php echo $diskTotal ?? 10; ?> GB</span></div>
<div style="height:8px;background:rgba(255,255,255,.08);border-radius:4px;overflow:hidden;margin-top:4px"><div style="height:100%;width:<?php echo $diskPct ?? 0; ?>%;background:<?php echo ($diskPct ?? 0) > 90 ? '#f87171' : '#4ade80'; ?>;border-radius:4px"></div></div>
</div>
<div><div style="display:flex;justify-content:space-between;font-size:13px"><span>Bandwidth</span><span>0 GB / N/A</span></div>
<div style="height:8px;background:rgba(255,255,255,.08);border-radius:4px;overflow:hidden;margin-top:4px"><div style="height:100%;width:0%;background:#4ade80;border-radius:4px"></div></div>
</div>
</div>
</div>
