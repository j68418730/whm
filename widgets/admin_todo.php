<?php
return [
    'key' => 'admin_todo',
    'name' => 'ToDo List',
    'description' => 'Admin tasks and progress — quick view + add',
    'icon' => 'bi-check2-square',
    'defaultZone' => 'side',
    'defaultSort' => 1,
    'height' => 2,
    'render' => function($uw) {
        try {
            $pdo = new \PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');
            $todos = $pdo->query("SELECT * FROM todos WHERE TRIM(title) <> '' ORDER BY (status='completed'), created_at DESC LIMIT 20")->fetchAll(\PDO::FETCH_OBJ) ?: [];
        } catch (\Exception $e) {
            $todos = [];
        }

        $pending = array_filter($todos, fn($t) => $t->status !== 'completed');
        $done = array_filter($todos, fn($t) => $t->status === 'completed');
        $total = count($todos);
        $pct = $total > 0 ? round((count($done) / $total) * 100) : 0;

        $html = '<style>
        .td-w{font-size:13px}
        .td-sum{display:flex;align-items:center;gap:12px;margin-bottom:10px}
        .td-sum .td-pct{font-size:22px;font-weight:800;color:var(--accent,#0A84FF)}
        .td-bar{flex:1;height:6px;background:rgba(255,255,255,.08);border-radius:3px;overflow:hidden}
        .td-bar i{display:block;height:100%;background:linear-gradient(90deg,#0A84FF,#00E5FF);border-radius:3px;transition:width .3s}
        .td-item{display:flex;align-items:center;gap:8px;padding:7px 0;border-bottom:1px solid rgba(255,255,255,.05);font-size:13px}
        .td-item:last-child{border-bottom:none}
        .td-item .td-title{flex:1;color:#e0e0e0;text-decoration:none}
        .td-item .td-title:hover{color:var(--accent)}
        .td-item .td-cat{font-size:10px;color:#64748b;padding:2px 8px;border-radius:999px;background:rgba(0,140,255,.1);white-space:nowrap}
        .td-item .td-status{font-size:10px;font-weight:700;white-space:nowrap}
        .td-item .td-status.pending{color:#facc15}
        .td-item .td-status.in_progress{color:#38bdf8}
        .td-item .td-status.completed{color:#4ade80}
        .td-item .td-prog{width:34px;text-align:right;font-size:11px;color:#94a3b8}
        .td-add{display:flex;gap:6px;margin-top:10px}
        .td-add input{flex:1;padding:7px 10px;background:rgba(0,0,0,.3);border:1px solid var(--border,rgba(255,255,255,.1));border-radius:6px;color:#fff;font-size:12px;outline:none}
        .td-add button{padding:7px 14px;background:linear-gradient(135deg,#0A84FF,#00E5FF);border:none;border-radius:6px;color:#fff;font-weight:600;font-size:12px;cursor:pointer}
        .td-empty{color:#64748b;font-size:13px;padding:10px 0}
        </style>';

        $html .= '<div class="td-w">';
        $html .= '<div class="td-sum"><span class="td-pct">' . $pct . '%</span>'
              . '<div class="td-bar"><i style="width:' . $pct . '%"></i></div>'
              . '<span style="font-size:11px;color:#64748b">' . count($pending) . ' open / ' . $total . '</span></div>';

        if (empty($todos)) {
            $html .= '<div class="td-empty">No tasks yet. Add one below.</div>';
        } else {
            $shown = array_slice($pending, 0, 5);
            foreach ($shown as $t) {
                $html .= '<div class="td-item">'
                      . '<span class="td-status ' . htmlspecialchars($t->status) . '">' . ($t->status === 'in_progress' ? '●' : '○') . '</span>'
                      . '<a class="td-title" href="/admin/todo" title="' . htmlspecialchars($t->description ?? '') . '">' . htmlspecialchars($t->title ?: 'Untitled') . '</a>'
                      . '<span class="td-cat">' . htmlspecialchars($t->category ?? 'General') . '</span>'
                      . '<span class="td-prog">' . (int)$t->progress . '%</span></div>';
            }
            if (count($done) > 0) {
                $html .= '<div style="margin-top:6px"><a href="/admin/todo" style="color:#64748b;font-size:11px;text-decoration:none">✓ ' . count($done) . ' completed — view all →</a></div>';
            }
        }

        $html .= '<form class="td-add" method="POST" action="/admin/todo" onsubmit="var i=this.querySelector(\'input[name=title]\');if(!i.value.trim()){i.focus();return false}setTimeout(function(){location.reload()},400)">
              <input name="title" placeholder="Add a task..." required>
              <button type="submit">Add</button></form>';
        $html .= '<div style="margin-top:8px"><a href="/admin/todo" style="color:var(--accent);font-size:11px;text-decoration:none">Open ToDo Board →</a></div>';
        $html .= '</div>';
        return $html;
    },
];
