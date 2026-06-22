<?php
namespace Services;

class GameTemplateEngine
{
    protected $pdo;

    public function __construct()
    {
        $config = require dirname(__DIR__) . '/config/database.php';
        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}";
        $this->pdo = new \PDO($dsn, $config['username'], $config['password'], [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ,
        ]);
    }

    public function getTemplate($appId)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM game_templates WHERE appid = ? AND status = 'active' LIMIT 1");
        $stmt->execute([(string)$appId]);
        return $stmt->fetch();
    }

    public function getTemplateById($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM game_templates WHERE id = ?");
        $stmt->execute([(int)$id]);
        return $stmt->fetch();
    }

    public function getAll($category = null)
    {
        if ($category && $category !== 'all') {
            $stmt = $this->pdo->prepare("SELECT * FROM game_templates WHERE status = 'active' AND category = ? ORDER BY name ASC");
            $stmt->execute([$category]);
        } else {
            $stmt = $this->pdo->query("SELECT * FROM game_templates WHERE status = 'active' ORDER BY category ASC, name ASC");
        }
        return $stmt->fetchAll();
    }

    public function getCategories()
    {
        $stmt = $this->pdo->query("SELECT DISTINCT category FROM game_templates WHERE status = 'active' ORDER BY category ASC");
        $rows = $stmt->fetchAll();
        $cats = [];
        foreach ($rows as $r) $cats[] = $r->category;
        return $cats;
    }

    public function generateInstallScript($template, $installDir = '{INSTALL_DIR}')
    {
        $vars = [
            '{INSTALL_DIR}' => $installDir,
            '{STEAMCMD_LOGIN}' => $template->steamcmd_login ?? 'anonymous',
            '{APPID}' => $template->appid ?? '0',
            '{SERVER_NAME}' => '{SERVER_NAME}',
            '{PORT}' => '{PORT}',
            '{MAX_PLAYERS}' => '{MAX_PLAYERS}',
            '{MAP}' => '{MAP}',
        ];
        $script = $template->install_script ?? "steamcmd +force_install_dir {INSTALL_DIR} +login {STEAMCMD_LOGIN} +app_update {APPID} validate +quit";
        return str_replace(array_keys($vars), array_values($vars), $script);
    }

    public function generateStartScript($template, $server = [])
    {
        $serverName = $server['server_name'] ?? $server->server_name ?? '{SERVER_NAME}';
        $port = $server['port'] ?? $server->port ?? '{PORT}';
        $maxPlayers = $server['max_players'] ?? $server->max_players ?? '{MAX_PLAYERS}';
        $installDir = $server['install_path'] ?? $server->install_path ?? '{INSTALL_DIR}';
        $map = $server['map_name'] ?? $server->map_name ?? '{MAP}';
        $password = $server['password'] ?? $server->password ?? '{PASSWORD}';

        $script = $template->start_command ?? "#!/bin/bash\ncd {INSTALL_DIR}\n./{SERVER_BINARY} -port {PORT} +maxplayers {MAX_PLAYERS} +map {MAP} +exec server.cfg";
        $replacements = [
            '{SERVER_NAME}' => $serverName,
            '{PORT}' => $port,
            '{QUERY_PORT}' => $server['query_port'] ?? $template->query_port ?? $port,
            '{RCON_PORT}' => $server['rcon_port'] ?? $template->rcon_port ?? 27020,
            '{MAX_PLAYERS}' => $maxPlayers,
            '{INSTALL_DIR}' => $installDir,
            '{MAP}' => $map,
            '{PASSWORD}' => $password,
            '{RCON_PASSWORD}' => $server['rcon_password'] ?? $server->rcon_password ?? '{RCON_PASSWORD}',
            '{MOTD}' => $server['motd'] ?? $server->motd ?? '',
            '{SERVER_IP}' => $server['ip'] ?? $server->ip ?? '0.0.0.0',
            '{ADMIN_LIST}' => $server['admin_list'] ?? $server->admin_list ?? '',
            '{WORKSHOP_COLLECTIONS}' => $server['workshop_collections'] ?? $server->workshop_collections ?? '',
        ];
        return str_replace(array_keys($replacements), array_values($replacements), $script);
    }

    public function generateStopScript($template, $server = [])
    {
        $installDir = $server['install_path'] ?? $server->install_path ?? '{INSTALL_DIR}';
        $script = $template->stop_command ?? "#!/bin/bash\nkill $(cat {INSTALL_DIR}/server.pid 2>/dev/null) 2>/dev/null || pkill -f {SERVER_BINARY}";
        $script = str_replace('{INSTALL_DIR}', $installDir, $script);
        $script = str_replace('{SERVER_BINARY}', $this->getBinaryName($template), $script);
        return $script;
    }

    public function generateRestartScript($template, $server = [])
    {
        $start = $this->generateStartScript($template, $server);
        $stop = $this->generateStopScript($template, $server);
        $script = $template->restart_command ?? "#!/bin/bash\n{STOP_COMMAND}\nsleep 2\n{START_COMMAND}";
        $script = str_replace('{STOP_COMMAND}', $stop, $script);
        $script = str_replace('{START_COMMAND}', $start, $script);
        return $script;
    }

    public function generateConfig($template, $server = [])
    {
        $serverName = $server['server_name'] ?? $server->server_name ?? '{SERVER_NAME}';
        $port = $server['port'] ?? $server->port ?? '{PORT}';
        $maxPlayers = $server['max_players'] ?? $server->max_players ?? '{MAX_PLAYERS}';
        $map = $server['map_name'] ?? $server->map_name ?? '{MAP}';
        $password = $server['password'] ?? $server->password ?? '{PASSWORD}';
        $rconPassword = $server['rcon_password'] ?? $server->rcon_password ?? '{RCON_PASSWORD}';
        $rconPort = $server['rcon_port'] ?? $template->rcon_port ?? 27020;
        $motd = $server['motd'] ?? $server->motd ?? 'Welcome to {SERVER_NAME}';
        $queryPort = $server['query_port'] ?? $template->query_port ?? $port;

        $cfg = $template->config_template ?? "hostname \"{SERVER_NAME}\"\nmaxplayers {MAX_PLAYERS}\nport {PORT}\nrcon_password \"{RCON_PASSWORD}\"\nrcon_port {RCON_PORT}\nsv_password \"{PASSWORD}\"\nmap {MAP}\n";
        $replacements = [
            '{SERVER_NAME}' => $serverName,
            '{PORT}' => $port,
            '{QUERY_PORT}' => $queryPort,
            '{RCON_PORT}' => $rconPort,
            '{MAX_PLAYERS}' => $maxPlayers,
            '{INSTALL_DIR}' => $server['install_path'] ?? $server->install_path ?? '{INSTALL_DIR}',
            '{MAP}' => $map,
            '{PASSWORD}' => $password,
            '{RCON_PASSWORD}' => $rconPassword,
            '{MOTD}' => $motd,
            '{SERVER_IP}' => $server['ip'] ?? $server->ip ?? '0.0.0.0',
            '{ADMIN_LIST}' => $server['admin_list'] ?? $server->admin_list ?? '',
            '{WORKSHOP_COLLECTIONS}' => $server['workshop_collections'] ?? $server->workshop_collections ?? '',
            '{GAME_NAME}' => $template->name ?? 'Game',
        ];
        return str_replace(array_keys($replacements), array_values($replacements), $cfg);
    }

    public function save($data)
    {
        $id = (int)($data['id'] ?? 0);
        $fields = ['name','appid','engine','category','steamcmd_login','steam_client','anonymous_login',
                    'requires_game_purchase','supports_linux','supports_windows','install_script','start_command',
                    'stop_command','restart_command','query_port','game_port','rcon_port','default_slots',
                    'min_slots','max_slots','description','notes','config_template','status'];
        $values = [];
        foreach ($fields as $f) {
            if (isset($data[$f])) {
                $values[$f] = $data[$f];
            }
        }
        if ($id) {
            $sets = [];
            $params = [];
            foreach ($values as $col => $val) {
                $sets[] = "`{$col}` = ?";
                $params[] = $val;
            }
            $params[] = $id;
            $sql = "UPDATE game_templates SET " . implode(', ', $sets) . " WHERE id = ?";
            $this->pdo->prepare($sql)->execute($params);
            return $id;
        } else {
            $cols = '`' . implode('`, `', array_keys($values)) . '`';
            $phs = ':' . implode(', :', array_keys($values));
            $sql = "INSERT INTO game_templates ({$cols}) VALUES ({$phs})";
            $stmt = $this->pdo->prepare($sql);
            foreach ($values as $k => $v) $stmt->bindValue(":{$k}", $v);
            $stmt->execute();
            return (int)$this->pdo->lastInsertId();
        }
    }

    public function delete($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM game_templates WHERE id = ?");
        return $stmt->execute([(int)$id]);
    }

    public function importFromGameTypes()
    {
        try {
            $types = $this->pdo->query("SELECT * FROM game_types WHERE is_active = 1")->fetchAll();
        } catch (\Exception $e) {
            return 0;
        }
        $imported = 0;
        foreach ($types as $t) {
            $name = $t->name ?? 'Unknown';
            $existing = $this->pdo->prepare("SELECT id FROM game_templates WHERE name = ? LIMIT 1");
            $existing->execute([$name]);
            if ($existing->fetch()) continue;

            $appid = $t->game_id ?? '0';
            $engine = $this->detectEngine($name, $appid);
            $category = $this->detectCategory($name);
            $steamcmdLogin = $appid && $appid !== '0' ? 'anonymous' : '';
            $installScript = "steamcmd +force_install_dir {INSTALL_DIR} +login {STEAMCMD_LOGIN} +app_update {APPID} validate +quit";
            if (!$appid || $appid === '0') {
                $installScript = "echo 'Manual install required for {$name}' > {INSTALL_DIR}/README.txt";
            }
            $binary = $this->getBinaryNameFromString($name);

            $this->pdo->prepare("INSERT INTO game_templates 
                (name, appid, engine, category, steamcmd_login, steam_client, anonymous_login,
                 requires_game_purchase, supports_linux, supports_windows, install_script, start_command,
                 stop_command, restart_command, query_port, game_port, rcon_port, default_slots,
                 min_slots, max_slots, description, config_template, status) VALUES
                (?, ?, ?, ?, ?, ?, ?, ?, 1, 0, ?, ?, ?, ?, 27015, 27015, 27020, 16, 10, 64, ?, ?, 'active')")
                ->execute([
                    $name,
                    $appid,
                    $engine,
                    $category,
                    $steamcmdLogin,
                    $t->requires_steam ?? 0,
                    $steamcmdLogin === 'anonymous' ? 1 : 0,
                    $appid && $appid !== '0' ? 0 : 0,
                    $installScript,
                    "#!/bin/bash\ncd {INSTALL_DIR}\n./{$binary} -port {PORT} +maxplayers {MAX_PLAYERS} +map {MAP} +exec server.cfg",
                    "#!/bin/bash\nkill $(cat {INSTALL_DIR}/server.pid 2>/dev/null) 2>/dev/null || pkill -f {$binary}",
                    "#!/bin/bash\nkill $(cat {INSTALL_DIR}/server.pid 2>/dev/null) 2>/dev/null || pkill -f {$binary}\nsleep 2\ncd {INSTALL_DIR} && ./{$binary} -port {PORT} +maxplayers {MAX_PLAYERS} +map {MAP} +exec server.cfg",
                    $t->description ?? '',
                    "hostname \"{SERVER_NAME}\"\nmaxplayers {MAX_PLAYERS}\nport {PORT}\nrcon_password \"{RCON_PASSWORD}\"\nrcon_port {RCON_PORT}\nmap {MAP}\nsv_password \"{PASSWORD}\"\n",
                ]);
            $imported++;
        }
        return $imported;
    }

    protected function getBinaryName($template)
    {
        return $this->getBinaryNameFromString($template->name ?? 'server');
    }

    protected function getBinaryNameFromString($name)
    {
        $n = strtolower($name);
        if (strpos($n, 'counter-strike') !== false || strpos($n, 'cs:') !== false) return 'srcds_run';
        if (strpos($n, 'team fortress') !== false) return 'srcds_run';
        if (strpos($n, 'left 4 dead') !== false) return 'srcds_run';
        if (strpos($n, 'garry') !== false) return 'srcds_run';
        if (strpos($n, 'half-life') !== false) return 'srcds_run';
        if (strpos($n, 'day of defeat') !== false) return 'srcds_run';
        if (strpos($n, 'source sdk') !== false) return 'srcds_run';
        if (strpos($n, 'rust') !== false) return 'RustDedicated';
        if (strpos($n, '7 days') !== false) return '7DaysToDieServer';
        if (strpos($n, 'valheim') !== false) return 'valheim_server';
        if (strpos($n, 'v rising') !== false) return 'VRisingServer';
        if (strpos($n, 'ark') !== false) return 'ShooterGameServer';
        if (strpos($n, 'conan') !== false) return 'ConanSandboxServer';
        if (strpos($n, 'dayz') !== false) return 'DayZServer';
        if (strpos($n, 'scum') !== false) return 'SCUM_Server';
        if (strpos($n, 'minecraft') !== false) return 'java';
        if (strpos($n, 'terraria') !== false) return 'TerrariaServer';
        if (strpos($n, 'palworld') !== false) return 'PalServer';
        if (strpos($n, 'arma') !== false) return 'arma3server_x64';
        if (strpos($n, 'squad') !== false) return 'SquadGameServer';
        if (strpos($n, 'assetto corsa competizione') !== false) return 'accServer';
        if (strpos($n, 'assetto corsa') !== false) return 'acServer';
        if (strpos($n, 'farming simulator') !== false) return 'farmingserver';
        if (strpos($n, 'insurgency') !== false) return 'InsurgencyServer';
        if (strpos($n, 'project zomboid') !== false) return 'ProjectZomboidServer';
        if (strpos($n, 'unturned') !== false) return 'Unturned';
        if (strpos($n, 'starbound') !== false) return 'starbound_server';
        if (strpos($n, 'dont starve') !== false) return 'dontstarve_dedicated_server';
        if (strpos($n, 'scp') !== false) return 'SCPSL';
        if (strpos($n, 'the forest') !== false) return 'TheForestDedicatedServer';
        if (strpos($n, 'hurtworld') !== false) return 'HurtworldServer';
        return 'server';
    }

    protected function detectEngine($name, $appid)
    {
        $n = strtolower($name);
        if (strpos($n, 'source') !== false || strpos($n, 'cs:') !== false || strpos($n, 'counter-strike') !== false) return 'Source';
        if (strpos($n, 'unreal') !== false || strpos($n, 'ut2') !== false || strpos($n, 'ut3') !== false) return 'Unreal';
        if (strpos($n, 'minecraft') !== false) return 'Java';
        if (strpos($n, 'terraria') !== false) return 'Native';
        if (strpos($n, 'rust') !== false || strpos($n, '7 days') !== false) return 'Unity';
        if (strpos($n, 'arma') !== false) return 'Real Virtuality';
        if ($appid && $appid !== '0') return 'Source';
        return 'Native';
    }

    protected function detectCategory($name)
    {
        $n = strtolower($name);
        $survival = ['rust','dayz','survival','zomboid','ark','conan','scum','valheim','v rising','7 days','the forest','unturned','hurtworld','raft','green hell'];
        $sandbox = ['garry','sandbox','terraria','starbound','space engineers','stormworks','scrap mechanic','besiege','trailmakers','from the depths'];
        $rpg = ['rpg','skyrim','divinity','baldur','grim dawn','titan quest','portal knights','path of exile','diablo','borderlands'];
        $racing = ['assetto','racing','trackmania','dirt','forza','rfactor','project cars','iracing','race','gran turismo'];
        $military = ['arma','squad','post scriptum','hell let loose','war thunder','world of tanks','world of warships','rising storm','dcs','wargame'];
        $simulation = ['simulator','simulation','farming','euro truck','american truck','kerbal','msfs','flight sim','beamng'];

        foreach ($military as $k) { if (strpos($n, $k) !== false) return 'Military'; }
        foreach ($survival as $k) { if (strpos($n, $k) !== false) return 'Survival'; }
        foreach ($sandbox as $k) { if (strpos($n, $k) !== false) return 'Sandbox'; }
        foreach ($rpg as $k) { if (strpos($n, $k) !== false) return 'RPG'; }
        foreach ($racing as $k) { if (strpos($n, $k) !== false) return 'Racing'; }
        foreach ($simulation as $k) { if (strpos($n, $k) !== false) return 'Simulation'; }
        return 'FPS';
    }
}
