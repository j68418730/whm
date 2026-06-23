<style>
.req-item{display:flex;justify-content:space-between;align-items:center;padding:8px 10px;border:1px solid rgba(255,255,255,.04);border-radius:6px;margin-bottom:4px;font-size:12px}
.req-item:hover{background:rgba(255,255,255,.02)}
</style>
<?php
$streamId = $_GET['stream_id'] ?? ($streams[0]->id ?? 0);
$reqs = [];
if ($streamId) {
    try { $reqs = $this->db->table('radio_requests')->where('stream_id', $streamId)->orderBy('created_at', 'DESC')->limit(50)->get() ?: []; } catch(\Exception $e) {}
}
$pending = array_filter($reqs, function($r){return $r->status==='pending';});
$history = array_filter($reqs, function($r){return $r->status!=='pending';});
?>
<h3>🙋 Song Requests <span style="font-size:12px;color:#64748b;font-weight:400">(<?php echo count($pending);?> pending)</span></h3>
<div class="r-card">
<?php if (empty($pending)):?><p style="color:#64748b;font-size:12px;text-align:center;padding:10px">No pending requests.</p>
<?php else: foreach($pending as $r):?>
<div class="req-item">
<div><strong><?php echo htmlspecialchars($r->song);?></strong> <?php if($r->artist):?>by <em><?php echo htmlspecialchars($r->artist);?></em><?php endif;?>
<br><span style="color:#64748b;font-size:10px">From: <?php echo htmlspecialchars($r->requester_name ?? 'Anonymous');?> · <?php echo date('M j g:i a', strtotime($r->created_at));?></span></div>
<div style="display:flex;gap:4px">
<a href="/user/radio/request/approve/<?php echo $r->id;?>" class="btn btn-sm btn-success">✓</a>
<a href="/user/radio/request/reject/<?php echo $r->id;?>" class="btn btn-sm btn-danger">✕</a>
</div></div>
<?php endforeach; endif;?>
</div>
<div class="r-card"><h3>History <span>(<?php echo count($history);?>)</span></h3>
<?php if(empty($history)):?><p style="color:#64748b;font-size:12px;text-align:center;padding:10px">No history.</p>
<?php else: foreach($history as $r):?>
<div class="req-item"><div><strong><?php echo htmlspecialchars($r->song);?></strong> — <span style="color:<?php echo $r->status==='approved'?'#4ade80':($r->status==='played'?'#0A84FF':'#64748b');?>"><?php echo $r->status;?></span></div></div>
<?php endforeach; endif;?>
</div>
