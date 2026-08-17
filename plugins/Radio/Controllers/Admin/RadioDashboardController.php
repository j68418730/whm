<?php
namespace Plugins\Radio\Controllers\Admin;

use Core\Controller;

class RadioDashboardController extends Controller
{
    protected $auth, $request, $response, $db;

    public function __construct()
    {
        $app = \Core\Application::getInstance();
        $this->auth = $app->get('auth');
        $this->request = $app->get('request');
        $this->response = $app->get('response');
        $this->db = $app->get('db');
    }

    public function index()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $user = $this->auth->user();
        $streams = $this->db->table('radio_streams')->get() ?: [];
        $total = count($streams);
        $active = 0;
        $totalListeners = 0;
        foreach ($streams as $s) {
            if ($s->status === 'running') $active++;
            $totalListeners += $s->listener_count ?? 0;
            // Get user name
            $u = $this->db->table('hosting_users')->where('id', $s->user_id)->first();
            $s->user_name = $u ? $u->username : 'N/A';
        }
        $djs = $this->db->table('radio_djs')->get() ?: [];
        // Use streaming_stations for names (radio_streams.server_name is often empty)
        $nameRows = $this->db->table('streaming_stations')->get() ?: [];
        $nameMap = [];
        foreach ($nameRows as $n) $nameMap[(int)$n->id] = $n->name ?? ('Stream #' . $n->id);
        // Build dj -> assigned streams map (junction + primary)
        $djStreams = [];
        foreach ($djs as $d) {
            $djStreams[$d->id] = [];
            $primaryId = (int)$d->stream_id;
            if ($primaryId > 0) {
                $djStreams[$d->id][$primaryId] = (object)['stream_id' => $primaryId, 'name' => $nameMap[$primaryId] ?? ('Stream #' . $primaryId), 'is_primary' => true];
            }
            try {
                $jRows = $this->db->table('radio_dj_streams')->where('dj_id', $d->id)->where('is_active', 'yes')->get() ?: [];
                foreach ($jRows as $jr) {
                    $sid = (int)$jr->stream_id;
                    if (isset($djStreams[$d->id][$sid])) continue;
                    $djStreams[$d->id][$sid] = (object)['stream_id' => $sid, 'name' => $nameMap[$sid] ?? ('Stream #' . $sid), 'is_primary' => false];
                }
            } catch (\Exception $e) {}
        }
        return $this->view('Plugins.Radio.Views.admin.radio_dashboard.index', [
            'user' => $user, 'streams' => $streams, 'djs' => $djs, 'djStreams' => $djStreams,
            'total' => $total, 'active' => $active, 'totalListeners' => $totalListeners,
            'theme_settings' => json_decode($user->theme_settings ?? '{}', true), 'title' => 'Radio Dashboard'
        ]);
    }

    public function widgets()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) { $this->response->redirect('/admin/login'); exit; }
        $user = $this->auth->user();
        $streams = $this->db->table('radio_streams')->get() ?: [];
        return $this->view('Plugins.Radio.Views.admin.radio_dashboard.widgets', [
            'user' => $user, 'streams' => $streams, 'title' => 'Radio Widgets'
        ]);
    }
}
