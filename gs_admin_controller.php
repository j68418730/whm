<?php
namespace Plugins\GameServers\Controllers\Admin;

use Core\Controller;

class GameServersController extends Controller
{
    protected $auth, $db, $response, $request;

    public function __construct()
    {
        $app = \Core\Application::getInstance();
        $this->auth = $app->get("auth");
        $this->db = $app->get("db");
        $this->response = $app->get("response");
        $this->request = $app->get("request");
    }

    protected function requireAdmin()
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) {
            $this->response->redirect("/admin/login"); exit;
        }
    }

    protected function loadServer($id)
    {
        $s = $this->db->table("game_servers")->where("id", (int)$id)->first();
        if (!$s) { $this->response->redirect("/admin/games"); exit; }
        return $s;
    }

    public function index()
    {
        $this->requireAdmin();
        $user = $this->auth->user();
        $servers = $this->db->table("game_servers")->orderBy("created_at", "DESC")->get() ?: [];
        $gameTypes = $this->db->table("game_types")->where("is_active", 1)->orderBy("name", "ASC")->get() ?: [];
        $hostingUsers = $this->db->table("hosting_users")->orderBy("username", "ASC")->get() ?: [];
        $templates = $this->db->table("game_templates")->where("status", "active")->get() ?: [];
        return $this->view("Plugins.GameServers.Views.admin.index", [
            "user" => $user, "servers" => $servers, "gameTypes" => $gameTypes,
            "hostingUsers" => $hostingUsers, "templates" => $templates, "title" => "Game Servers"
        ]);
    }

    public function show($id)
    {
        $this->requireAdmin();
        $server = $this->loadServer($id);
        $user = $this->auth->user();
        $owner = $this->db->table("hosting_users")->where("id", $server->user_id)->first();
        $gameType = $this->db->table("game_types")->where("name", $server->game_type)->first();
        $hostingUsers = $this->db->table("hosting_users")->orderBy("username", "ASC")->get() ?: [];
        $configPath = $server->config_path ?: $server->install_path . "/server.cfg";
        $configContent = file_exists($configPath) ? file_get_contents($configPath) : "";
        $logFile = $server->install_path . "/console.log";
        $consoleLog = file_exists($logFile) ? file_get_contents($logFile) : "";
        $files = [];
        if (is_dir($server->install_path)) {
            $dir = new \DirectoryIterator($server->install_path);
            foreach ($dir as $f) {
                if ($f->isDot() || $f->getFilename()[0] === ".") continue;
                $files[] = ["name"=>$f->getFilename(),"size"=>$f->isDir()?0:$f->getSize(),"is_dir"=>$f->isDir(),"mtime"=>date("Y-m-d H:i",$f->getMTime())];
            }
            usort($files, fn($a,$b) => $b["is_dir"] <=> $a["is_dir"] ?: strcasecmp($a["name"],$b["name"]));
        }
        $players = $this->db->table("game_server_players")->where("server_id", $id)->orderBy("last_seen","DESC")->limit(50)->get() ?: [];
        $bans = $this->db->table("game_server_bans")->where("server_id", $id)->where("is_active",1)->orderBy("created_at","DESC")->get() ?: [];
        $maps = $this->db->table("game_server_maps")->where("server_id", $id)->orderBy("name","ASC")->get() ?: [];
        $backups = $this->db->table("game_server_backups")->where("server_id", $id)->orderBy("created_at","DESC")->get() ?: [];
        $firewall = $this->db->table("game_server_firewall_rules")->where("server_id", $id)->orderBy("created_at","DESC")->get() ?: [];
        $tasks = $this->db->table("game_server_scheduled_tasks")->where("server_id", $id)->orderBy("created_at","DESC")->get() ?: [];
        $notifs = $this->db->table("game_server_notifications")->where("server_id", $id)->orderBy("created_at","DESC")->get() ?: [];
        $subs = $this->db->table("game_server_sub_users")->where("server_id", $id)->orderBy("created_at","DESC")->get() ?: [];
        $voices = $this->db->table("game_server_voice_servers")->where("server_id", $id)->orderBy("created_at","DESC")->get() ?: [];
        $workshop = $this->db->table("game_server_workshop_items")->where("server_id", $id)->orderBy("created_at","DESC")->get() ?: [];
        return $this->view("Plugins.GameServers.Views.admin.show", [
            "user"=>$user,"server"=>$server,"owner"=>$owner,"gameType"=>$gameType,
            "hostingUsers"=>$hostingUsers,"configContent"=>$configContent,"consoleLog"=>$consoleLog,
            "files"=>$files,"players"=>$players,"bans"=>$bans,"maps"=>$maps,"backups"=>$backups,
            "firewall"=>$firewall,"tasks"=>$tasks,"notifs"=>$notifs,"subs"=>$subs,
            "voices"=>$voices,"workshop"=>$workshop,"title"=>"Game: " . $server->server_name
        ]);
    }

    public function create()
    {
        $this->requireAdmin();
        $name = trim($this->request->post("name",""));
        $gameTypeName = $this->request->post("game_type","");
        $templateId = (int)$this->request->post("template_id",0);
        $userId = (int)$this->request->post("user_id",0);
        $port = (int)$this->request->post("port",0);
        $maxPlayers = (int)$this->request->post("max_players",16);
        $appId = $this->request->post("app_id","");
        $steamUser = $this->request->post("steam_username","");
        $steamPass = $this->request->post("steam_password","");
        $steamGuard = $this->request->post("steam_guard_code","");
        if (!$name || !$userId) { $_SESSION["error_message"]="Server name and user required."; $this->response->redirect("/admin/games"); exit; }
        $template = null;
        if ($templateId) { require_once BASE_PATH."/services/GameTemplateEngine.php"; $engine=new \Services\GameTemplateEngine(); $template=$engine->getTemplateById($templateId); if ($template&&!$appId) $appId=$template->appid; }
        if ($port<=0) { require_once BASE_PATH."/core/PortManager.php"; $pm=new \Core\PortManager(); $alloc=$pm->allocate("game_server"); $port=$alloc?:27015; }
        $userRow = $this->db->table("hosting_users")->where("id",$userId)->first();
        $username = $userRow->username ?? "gameservers";
        $slug = preg_replace("/[^a-z0-9]/","",strtolower($name));
        $installDir = "/home/{$username}/gameservers/{$slug}";
        $serverId = $this->db->table("game_servers")->insertGetId([
            "user_id"=>$userId,"game_type"=>$gameTypeName?:($template->name??"Custom"),
            "server_name"=>$name,"port"=>$port,"max_players"=>$maxPlayers,"status"=>"installing",
            "install_status"=>"pending","install_progress"=>0,"install_path"=>$installDir,
            "ftp_path"=>$installDir,"steam_login_username"=>$steamUser,"steam_login_password"=>$steamPass,
            "steam_guard_code"=>$steamGuard,"is_demo"=>$appId?0:1,
        ]);
        @mkdir($installDir,0755,true);
        if ($appId) {
            $login = $steamUser?"{$steamUser} {$steamPass}":"anonymous";
            $guard = $steamGuard?" +set_steam_guard_code {$steamGuard}":"";
            $script = "#!/bin/bash\nexport HOME=/home/{$username}\ncd {$installDir}\necho '5' > {$installDir}/.install_progress\n/usr/games/steamcmd +force_install_dir {$installDir} +login {$login}{$guard} +app_update {$appId} validate +quit > {$installDir}/install.log 2>&1\necho '90' > {$installDir}/.install_progress\necho 'Installation complete.' >> {$installDir}/install.log\necho '100' > {$installDir}/.install_progress\n";
            file_put_contents("{$installDir}/install.sh",$script); chmod("{$installDir}/install.sh",0755);
            file_put_contents("{$installDir}/install.log","Starting...\n"); file_put_contents("{$installDir}/.install_progress","5");
            exec("nohup bash {$installDir}/install.sh > {$installDir}/install_run.log 2>&1 &");
            if ($template) {
                require_once BASE_PATH."/services/GameTemplateEngine.php"; $engine=new \Services\GameTemplateEngine();
                $sd=["server_name"=>$name,"port"=>$port,"max_players"=>$maxPlayers,"install_path"=>$installDir,"map_name"=>"de_dust2","password"=>"","rcon_password"=>bin2hex(random_bytes(8)),"rcon_port"=>$template->rcon_port??27020,"query_port"=>$template->query_port??$port,"motd"=>"Welcome to ".$name,"ip"=>"0.0.0.0"];
                file_put_contents("{$installDir}/start.sh",$engine->generateStartScript($template,$sd)); @chmod("{$installDir}/start.sh",0755);
                file_put_contents("{$installDir}/stop.sh",$engine->generateStopScript($template,$sd)); @chmod("{$installDir}/stop.sh",0755);
                file_put_contents("{$installDir}/restart.sh",$engine->generateRestartScript($template,$sd)); @chmod("{$installDir}/restart.sh",0755);
                $cp = "{$installDir}/server.cfg"; file_put_contents($cp,$engine->generateConfig($template,$sd));
                $this->db->table("game_servers")->where("id",$serverId)->update(["config_path"=>$cp,"rcon_password"=>$sd["rcon_password"],"rcon_port"=>$sd["rcon_port"],"query_port"=>$sd["query_port"]]);
            }
            $_SESSION["success_message"]="Server created. Installing via SteamCMD (App {$appId}).";
        } else {
            $this->db->table("game_servers")->where("id",$serverId)->update(["install_status"=>"completed","install_progress"=>100,"status"=>"stopped"]);
            $_SESSION["success_message"]="Demo server created.";
        }
        $this->response->redirect("/admin/games");
    }

    public function start($id) { return $this->doAction($id, "start"); }
    public function stop($id) { return $this->doAction($id, "stop"); }
    public function restart($id) { $mg = new \Plugins\GameServers\Services\GameServerManager(); $mg->stop((int)$id); sleep(1); $mg->start((int)$id); $this->response->redirect("/admin/games"); }
    public function suspend($id) { $this->requireAdmin(); $this->db->table("game_servers")->where("id",(int)$id)->update(["status"=>"suspended"]); $_SESSION["success_message"]="Server suspended."; $this->response->redirect("/admin/games"); }
    public function unsuspend($id) { $this->requireAdmin(); $this->db->table("game_servers")->where("id",(int)$id)->update(["status"=>"stopped"]); $_SESSION["success_message"]="Server unsuspended."; $this->response->redirect("/admin/games"); }
    public function assign($id) { $this->requireAdmin(); $uid=(int)$this->request->post("user_id",0); if($uid){$this->db->table("game_servers")->where("id",(int)$id)->update(["user_id"=>$uid]);$_SESSION["success_message"]="Reassigned.";} $this->response->redirect("/admin/games/show/".(int)$id); }

    public function status($id) {
        $this->requireAdmin();
        $m = new \Plugins\GameServers\Services\GameServerManager();
        $status = $m->getStatus((int)$id);
        $server = $this->db->table("game_servers")->where("id",(int)$id)->first();
        if ($server) {
            $status["install_status"]=$server->install_status;$status["install_progress"]=(int)$server->install_progress;
            $pf=$server->install_path."/install.log"; if(file_exists($pf))$status["install_log"]=file_get_contents($pf);
            $pf2=$server->install_path."/.install_progress"; if(file_exists($pf2)){$pct=(int)trim(file_get_contents($pf2));$status["install_progress"]=$pct;if($pct>=100){$this->db->table("game_servers")->where("id",(int)$id)->update(["install_status"=>"completed","install_progress"=>100,"status"=>"stopped"]);}}
        }
        $this->response->json($status); $this->response->send(); exit;
    }

    public function command($id) { $this->requireAdmin(); $s=$this->loadServer($id); if($_POST&&isset($_POST["cmd"])){$p=escapeshellarg($s->install_path);exec("cd {$p} && ".escapeshellcmd($_POST["cmd"])." >> {$p}/console.log 2>&1 &");$_SESSION["success_message"]="Command executed.";} $this->response->redirect("/admin/games/show/".$id); }
    public function saveConfig($id) { $this->requireAdmin(); $s=$this->loadServer($id); if($_POST&&isset($_POST["config_content"])){$p=$s->config_path?:$s->install_path."/server.cfg";@mkdir(dirname($p),0755,true);file_put_contents($p,$_POST["config_content"]);if(!$s->config_path)$this->db->table("game_servers")->where("id",$id)->update(["config_path"=>$p]);$_SESSION["success_message"]="Configuration saved.";} $this->response->redirect("/admin/games/show/".$id); }
    public function uninstall($id) { return $this->doAction($id, "uninstall"); }
    public function settings() { $this->requireAdmin();$user=$this->auth->user();return $this->view("Plugins.GameServers.Views.admin.settings",["user"=>$user,"title"=>"Game Server Settings","steam_username"=>($this->db->table("automation_settings")->where("setting_key","steam_username")->first()->setting_value??"planet_hosts_dev"),"steam_password"=>($this->db->table("automation_settings")->where("setting_key","steam_password")->first()->setting_value??""),"game_install_dir"=>($this->db->table("automation_settings")->where("setting_key","game_install_dir")->first()->setting_value??"/home/gameservers"),"game_default_port"=>($this->db->table("automation_settings")->where("setting_key","game_default_port")->first()->setting_value??"27015")]); }
    public function settingsSave() { $this->requireAdmin();foreach(["steam_username","steam_password","game_install_dir","game_default_port"]as $k){$v=$this->request->post($k,"");$e=$this->db->table("automation_settings")->where("setting_key",$k)->first();if($e){$this->db->table("automation_settings")->where("setting_key",$k)->update(["setting_value"=>$v]);}else{$this->db->table("automation_settings")->insertGetId(["setting_key"=>$k,"setting_value"=>$v]);}} $_SESSION["success_message"]="Settings saved.";$this->response->redirect("/admin/game-catalog/settings"); }
    public function types() { $this->requireAdmin();$user=$this->auth->user();$types=$this->db->table("game_types")->orderBy("sort_order","ASC")->orderBy("name","ASC")->get()?:[];return $this->view("Plugins.GameServers.Views.admin.catalog",["user"=>$user,"types"=>$types,"title"=>"Game Catalog","activeTab"=>"types"]); }
    public function typesStore() { $this->requireAdmin();$id=(int)$this->request->post("id",0);$d=["name"=>$this->request->post("name",""),"description"=>$this->request->post("description",""),"icon"=>$this->request->post("icon","gamepad"),"pricing_model"=>$this->request->post("pricing_model","slot"),"min_slots"=>(int)$this->request->post("min_slots",10),"max_slots"=>(int)$this->request->post("max_slots",100),"price_per_slot"=>(float)$this->request->post("price_per_slot",0.50),"setup_fee"=>(float)$this->request->post("setup_fee",0),"billing_cycle"=>$this->request->post("billing_cycle","monthly"),"is_active"=>(int)$this->request->post("is_active",1),"sort_order"=>(int)$this->request->post("sort_order",0),"game_id"=>$this->request->post("game_id",""),"steam_app_id"=>$this->request->post("steam_app_id",""),"steam_login_required"=>(int)$this->request->post("steam_login_required",0),"default_map"=>$this->request->post("default_map",""),"default_max_players"=>(int)$this->request->post("default_max_players",16),"startup_parameters"=>$this->request->post("startup_parameters",""),"workshop_support"=>(int)$this->request->post("workshop_support",0),"workshop_collection_id"=>$this->request->post("workshop_collection_id",""),"rcon_port_default"=>(int)$this->request->post("rcon_port_default",27015),"query_port_default"=>(int)$this->request->post("query_port_default",27016),"install_command"=>$this->request->post("install_command",""),"install_script"=>$this->request->post("install_script",""),"update_command"=>$this->request->post("update_command",""),"validate_command"=>$this->request->post("validate_command",""),"beta_branch"=>$this->request->post("beta_branch",""),"beta_password"=>$this->request->post("beta_password",""),"steam_username"=>$this->request->post("steam_username",""),"steam_password"=>$this->request->post("steam_password","")];if($id){$this->db->table("game_types")->where("id",$id)->update($d);$_SESSION["success_message"]="Game type updated.";}else{$this->db->table("game_types")->insertGetId($d);$_SESSION["success_message"]="Game type created.";}$this->response->redirect("/admin/game-catalog"); }
    public function typesDelete($id) { $this->requireAdmin();$this->db->table("game_types")->where("id",(int)$id)->delete();$_SESSION["success_message"]="Game type deleted.";$this->response->redirect("/admin/game-catalog"); }
    public function pricing() { $this->requireAdmin();$user=$this->auth->user();$types=$this->db->table("game_types")->orderBy("name","ASC")->get()?:[];$pricing=$this->db->table("game_slot_pricing")->orderBy("game_type_id","ASC")->orderBy("min_slots","ASC")->get()?:[];return $this->view("Plugins.GameServers.Views.admin.catalog",["user"=>$user,"types"=>$types,"pricing"=>$pricing,"title"=>"Slot Pricing","activeTab"=>"pricing"]); }
    public function pricingStore() { $this->requireAdmin();$id=(int)$this->request->post("id",0);$d=["game_type_id"=>(int)$this->request->post("game_type_id",0),"min_slots"=>(int)$this->request->post("min_slots",1),"max_slots"=>(int)$this->request->post("max_slots",0),"price_per_slot"=>(float)$this->request->post("price_per_slot",0.50)];if($id){$this->db->table("game_slot_pricing")->where("id",$id)->update($d);}else{$this->db->table("game_slot_pricing")->insertGetId($d);}$_SESSION["success_message"]="Pricing saved.";$this->response->redirect("/admin/game-catalog/pricing"); }
    public function pricingDelete($id) { $this->requireAdmin();$this->db->table("game_slot_pricing")->where("id",(int)$id)->delete();$_SESSION["success_message"]="Pricing deleted.";$this->response->redirect("/admin/game-catalog/pricing"); }
    public function packages() { $this->requireAdmin();$user=$this->auth->user();$types=$this->db->table("game_types")->orderBy("name","ASC")->get()?:[];$packages=$this->db->table("game_packages")->orderBy("game_type_id","ASC")->orderBy("price","ASC")->get()?:[];return $this->view("Plugins.GameServers.Views.admin.catalog",["user"=>$user,"types"=>$types,"packages"=>$packages,"title"=>"Game Packages","activeTab"=>"packages"]); }
    public function packagesStore() { $this->requireAdmin();$id=(int)$this->request->post("id",0);$d=["game_type_id"=>(int)$this->request->post("game_type_id",0),"name"=>$this->request->post("name",""),"description"=>$this->request->post("description",""),"slots"=>(int)$this->request->post("slots",10),"price"=>(float)$this->request->post("price",0),"setup_fee"=>(float)$this->request->post("setup_fee",0),"billing_cycle"=>$this->request->post("billing_cycle","monthly"),"is_active"=>(int)$this->request->post("is_active",1)];if($id){$this->db->table("game_packages")->where("id",$id)->update($d);}else{$this->db->table("game_packages")->insertGetId($d);}$_SESSION["success_message"]="Package saved.";$this->response->redirect("/admin/game-catalog/packages"); }
    public function packagesDelete($id) { $this->requireAdmin();$this->db->table("game_packages")->where("id",(int)$id)->delete();$_SESSION["success_message"]="Package deleted.";$this->response->redirect("/admin/game-catalog/packages"); }

    // ── Players ──
    public function players($id) { $this->requireAdmin(); $server=$this->loadServer($id); $_SESSION["games_tab"]="players"; $this->response->redirect("/admin/games/show/".$id); }
    public function playerAction($id) { $this->requireAdmin(); $server=$this->loadServer($id); $playerId=(int)$this->request->post("player_id",0); $action=$this->request->post("action",""); $player=$this->db->table("game_server_players")->where("id",$playerId)->where("server_id",$id)->first(); if(!$player){$_SESSION["error_message"]="Player not found.";$this->response->redirect("/admin/games/show/".$id);exit;}
        if($action==="ban"){$reason=$this->request->post("reason","Banned by admin"); $this->db->table("game_server_players")->where("id",$playerId)->update(["is_banned"=>1,"ban_reason"=>$reason,"banned_by"=>$this->auth->user()->id??0,"banned_at"=>date("Y-m-d H:i:s")]); $this->db->table("game_server_bans")->insertGetId(["server_id"=>$id,"player_name"=>$player->player_name,"steam_id"=>$player->steam_id,"ip_address"=>$player->ip_address,"reason"=>$reason,"banned_by"=>$this->auth->user()->id??0]); $_SESSION["success_message"]="Player banned.";}
        elseif($action==="unban"){$this->db->table("game_server_players")->where("id",$playerId)->update(["is_banned"=>0]); $this->db->table("game_server_bans")->where("server_id",$id)->where("steam_id",$player->steam_id)->update(["is_active"=>0]); $_SESSION["success_message"]="Player unbanned.";}
        elseif($action==="setadmin"){$this->db->table("game_server_players")->where("id",$playerId)->update(["is_admin"=>1]); $_SESSION["success_message"]="Admin flag set.";}
        elseif($action==="removeadmin"){$this->db->table("game_server_players")->where("id",$playerId)->update(["is_admin"=>0]); $_SESSION["success_message"]="Admin flag removed.";}
        $this->response->redirect("/admin/games/show/".$id);
    }
    public function bans($id) { $this->requireAdmin(); $server=$this->loadServer($id); $_SESSION["games_tab"]="bans"; $this->response->redirect("/admin/games/show/".$id); }
    public function addBan($id) { $this->requireAdmin(); $server=$this->loadServer($id); $this->db->table("game_server_bans")->insertGetId(["server_id"=>$id,"player_name"=>$this->request->post("player_name",""),"steam_id"=>$this->request->post("steam_id",""),"ip_address"=>$this->request->post("ip_address",""),"reason"=>$this->request->post("reason",""),"banned_by"=>$this->auth->user()->id??0,"expires_at"=>$this->request->post("expires_at",null)?:null]); $_SESSION["success_message"]="Ban added.";$this->response->redirect("/admin/games/show/".$id); }
    public function unban($serverId, $banId) { $this->requireAdmin(); $this->db->table("game_server_bans")->where("id",(int)$banId)->where("server_id",(int)$serverId)->update(["is_active"=>0]); $_SESSION["success_message"]="Unbanned.";$this->response->redirect("/admin/games/show/".(int)$serverId); }

    // ── Maps ──
    public function maps($id) { $this->requireAdmin(); $_SESSION["games_tab"]="maps"; $this->response->redirect("/admin/games/show/".$id); }
    public function uploadMap($id) { $this->requireAdmin(); $server=$this->loadServer($id); if(isset($_FILES["map_file"])&&$_FILES["map_file"]["error"]===0){$name=$_FILES["map_file"]["name"];$target=$server->install_path."/maps/".$name;@mkdir(dirname($target),0755,true);if(move_uploaded_file($_FILES["map_file"]["tmp_name"],$target)){$size=filesize($target);$this->db->table("game_server_maps")->insertGetId(["server_id"=>$id,"name"=>pathinfo($name,PATHINFO_FILENAME),"file_path"=>$target,"file_size"=>$size]);$_SESSION["success_message"]="Map uploaded.";}else{$_SESSION["error_message"]="Upload failed.";}} $this->response->redirect("/admin/games/show/".$id); }
    public function deleteMap($serverId,$mapId) { $this->requireAdmin(); $m=$this->db->table("game_server_maps")->where("id",(int)$mapId)->where("server_id",(int)$serverId)->first(); if($m&&$m->file_path&&file_exists($m->file_path))@unlink($m->file_path); $this->db->table("game_server_maps")->where("id",(int)$mapId)->delete(); $_SESSION["success_message"]="Map deleted.";$this->response->redirect("/admin/games/show/".(int)$serverId); }

    // ── Backups ──
    public function backups($id) { $this->requireAdmin(); $_SESSION["games_tab"]="backups"; $this->response->redirect("/admin/games/show/".$id); }
    public function createBackup($id) { $this->requireAdmin(); $server=$this->loadServer($id); $mg=new \Plugins\GameServers\Services\GameServerManager(); $result=$mg->createBackup((int)$id); $_SESSION[$result["success"]?"success_message":"error_message"]=$result["message"]; $this->response->redirect("/admin/games/show/".$id); }
    public function restoreBackup($serverId,$backupId) { $this->requireAdmin(); $mg=new \Plugins\GameServers\Services\GameServerManager(); $result=$mg->restoreBackup((int)$serverId,(int)$backupId); $_SESSION[$result["success"]?"success_message":"error_message"]=$result["message"]; $this->response->redirect("/admin/games/show/".(int)$serverId); }
    public function deleteBackup($serverId,$backupId) { $this->requireAdmin(); $b=$this->db->table("game_server_backups")->where("id",(int)$backupId)->where("server_id",(int)$serverId)->first(); if($b&&$b->file_path&&file_exists($b->file_path))@unlink($b->file_path); $this->db->table("game_server_backups")->where("id",(int)$backupId)->delete(); $_SESSION["success_message"]="Backup deleted.";$this->response->redirect("/admin/games/show/".(int)$serverId); }
    public function backupSettings($id) { $this->requireAdmin(); $server=$this->loadServer($id); $schedule=$this->request->post("schedule_cron",""); $retention=(int)$this->request->post("retention_days",30); $this->db->table("game_servers")->where("id",$id)->update(["backup_schedule"=>$schedule]); $_SESSION["success_message"]="Backup settings saved.";$this->response->redirect("/admin/games/show/".$id); }

    // ── Firewall ──
    public function firewallRules($id) { $this->requireAdmin(); $_SESSION["games_tab"]="firewall"; $this->response->redirect("/admin/games/show/".$id); }
    public function addFirewallRule($id) { $this->requireAdmin(); $server=$this->loadServer($id); $this->db->table("game_server_firewall_rules")->insertGetId(["server_id"=>$id,"rule_type"=>$this->request->post("rule_type","allow"),"protocol"=>$this->request->post("protocol","both"),"port_start"=>(int)$this->request->post("port_start",0), "port_end"=>(int)$this->request->post("port_end",0), "source_ip"=>$this->request->post("source_ip","0.0.0.0/0"),"description"=>$this->request->post("description","")]); $mg=new \Plugins\GameServers\Services\GameServerManager(); $mg->applyFirewall((int)$id); $_SESSION["success_message"]="Firewall rule added.";$this->response->redirect("/admin/games/show/".$id); }
    public function deleteFirewallRule($serverId,$ruleId) { $this->requireAdmin(); $this->db->table("game_server_firewall_rules")->where("id",(int)$ruleId)->where("server_id",(int)$serverId)->delete(); $mg=new \Plugins\GameServers\Services\GameServerManager(); $mg->applyFirewall((int)$serverId); $_SESSION["success_message"]="Rule deleted.";$this->response->redirect("/admin/games/show/".(int)$serverId); }

    // ── Network ──
    public function network($id) { $this->requireAdmin(); $_SESSION["games_tab"]="network"; $this->response->redirect("/admin/games/show/".$id); }

    // ── Scheduled Tasks ──
    public function scheduledTasks($id) { $this->requireAdmin(); $_SESSION["games_tab"]="tasks"; $this->response->redirect("/admin/games/show/".$id); }
    public function addScheduledTask($id) { $this->requireAdmin(); $server=$this->loadServer($id); $this->db->table("game_server_scheduled_tasks")->insertGetId(["server_id"=>$id,"name"=>$this->request->post("name",""),"task_type"=>$this->request->post("task_type","restart"),"cron_expression"=>$this->request->post("cron_expression","0 4 * * *"),"command"=>$this->request->post("command","")]); $_SESSION["success_message"]="Task added.";$this->response->redirect("/admin/games/show/".$id); }
    public function deleteScheduledTask($serverId,$taskId) { $this->requireAdmin(); $this->db->table("game_server_scheduled_tasks")->where("id",(int)$taskId)->where("server_id",(int)$serverId)->delete(); $_SESSION["success_message"]="Task deleted.";$this->response->redirect("/admin/games/show/".(int)$serverId); }

    // ── Notifications ──
    public function notifications($id) { $this->requireAdmin(); $_SESSION["games_tab"]="notifications"; $this->response->redirect("/admin/games/show/".$id); }
    public function addNotification($id) { $this->requireAdmin(); $server=$this->loadServer($id); $this->db->table("game_server_notifications")->insertGetId(["server_id"=>$id,"type"=>$this->request->post("type","email"),"event"=>$this->request->post("event","server_stopped"),"destination"=>$this->request->post("destination","")]); $_SESSION["success_message"]="Notification added.";$this->response->redirect("/admin/games/show/".$id); }
    public function deleteNotification($serverId,$notifId) { $this->requireAdmin(); $this->db->table("game_server_notifications")->where("id",(int)$notifId)->where("server_id",(int)$serverId)->delete(); $_SESSION["success_message"]="Notification deleted.";$this->response->redirect("/admin/games/show/".(int)$serverId); }

    // ── Sub-users ──
    public function subUsers($id) { $this->requireAdmin(); $_SESSION["games_tab"]="sub-users"; $this->response->redirect("/admin/games/show/".$id); }
    public function addSubUser($id) { $this->requireAdmin(); $server=$this->loadServer($id); $pass=password_hash($this->request->post("password","gameserver"),PASSWORD_DEFAULT); $perms=json_encode($this->request->post("permissions",["console","config"])); $this->db->table("game_server_sub_users")->insertGetId(["server_id"=>$id,"username"=>$this->request->post("username","subuser"),"password"=>$pass,"permissions"=>$perms]); $_SESSION["success_message"]="Sub-user added.";$this->response->redirect("/admin/games/show/".$id); }
    public function deleteSubUser($serverId,$subId) { $this->requireAdmin(); $this->db->table("game_server_sub_users")->where("id",(int)$subId)->where("server_id",(int)$serverId)->delete(); $_SESSION["success_message"]="Sub-user deleted.";$this->response->redirect("/admin/games/show/".(int)$serverId); }

    // ── Voice ──
    public function voiceServers($id) { $this->requireAdmin(); $_SESSION["games_tab"]="voice"; $this->response->redirect("/admin/games/show/".$id); }
    public function addVoiceServer($id) { $this->requireAdmin(); $server=$this->loadServer($id); $this->db->table("game_server_voice_servers")->insertGetId(["server_id"=>$id,"type"=>$this->request->post("type","discord"),"server_address"=>$this->request->post("server_address",""),"server_port"=>(int)$this->request->post("server_port",0),"server_password"=>$this->request->post("server_password",""),"channel_name"=>$this->request->post("channel_name",""),"bot_token"=>$this->request->post("bot_token","")]); $_SESSION["success_message"]="Voice server linked.";$this->response->redirect("/admin/games/show/".$id); }
    public function deleteVoiceServer($serverId,$voiceId) { $this->requireAdmin(); $this->db->table("game_server_voice_servers")->where("id",(int)$voiceId)->where("server_id",(int)$serverId)->delete(); $_SESSION["success_message"]="Voice server unlinked.";$this->response->redirect("/admin/games/show/".(int)$serverId); }

    // ── Workshop ──
    public function workshopItems($id) { $this->requireAdmin(); $_SESSION["games_tab"]="workshop"; $this->response->redirect("/admin/games/show/".$id); }
    public function addWorkshopItem($id) { $this->requireAdmin(); $server=$this->loadServer($id); $wid=$this->request->post("workshop_id",""); $name=$this->request->post("name",""); $this->db->table("game_server_workshop_items")->insertGetId(["server_id"=>$id,"workshop_id"=>$wid,"name"=>$name]); $_SESSION["success_message"]="Workshop item added.";$this->response->redirect("/admin/games/show/".$id); }
    public function deleteWorkshopItem($serverId,$itemId) { $this->requireAdmin(); $this->db->table("game_server_workshop_items")->where("id",(int)$itemId)->where("server_id",(int)$serverId)->delete(); $_SESSION["success_message"]="Workshop item removed.";$this->response->redirect("/admin/games/show/".(int)$serverId); }
    public function syncWorkshop($id) { $this->requireAdmin(); $server=$this->loadServer($id); $mg=new \Plugins\GameServers\Services\GameServerManager(); $result=$mg->syncWorkshopItems((int)$id); $_SESSION[$result["success"]?"success_message":"error_message"]=$result["message"]; $this->response->redirect("/admin/games/show/".$id); }

    // ── Steam Ownership Verification ──
    public function verifySteamOwnership($id) {
        $this->requireAdmin();
        $server=$this->loadServer($id);
        $steamId=$this->request->post("steam_id","");
        $appId=$server->game_type;
        $type=$this->db->table("game_types")->where("name",$appId)->first();
        $steamAppId=$type->steam_app_id??$type->game_id??"";
        if(!$steamAppId||!$steamId){$_SESSION["error_message"]="Steam ID and App ID required.";$this->response->redirect("/admin/games/show/".$id);exit;}
        // Check via Steam API
        $key=$this->db->table("automation_settings")->where("setting_key","steam_web_api_key")->first();
        $apiKey=$key->setting_value??"";
        if(!$apiKey){$_SESSION["error_message"]="Steam Web API key not configured.";$this->response->redirect("/admin/games/show/".$id);exit;}
        $url="https://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key={$apiKey}&steamids={$steamId}";
        $ctx=stream_context_create(["http"=>["timeout"=>10]]);
        $resp=@file_get_contents($url,false,$ctx);
        if(!$resp){$_SESSION["error_message"]="Failed to contact Steam API.";$this->response->redirect("/admin/games/show/".$id);exit;}
        $data=json_decode($resp,true);
        $owns=false;
        if(!empty($data["response"]["players"][0])){
            $url2="https://api.steampowered.com/IPlayerService/GetOwnedGames/v0001/?key={$apiKey}&steamid={$steamId}&include_appinfo=0&format=json";
            $resp2=@file_get_contents($url2,false,$ctx);
            if($resp2){$data2=json_decode($resp2,true);if(!empty($data2["response"]["games"])){foreach($data2["response"]["games"]as$g){if($g["appid"]==$steamAppId){$owns=true;break;}}}}
        }
        if($owns){$this->db->table("game_servers")->where("id",$id)->update(["steam_owner_id"=>$steamId,"steam_owned"=>1]);$_SESSION["success_message"]="Steam ownership verified.";}else{$_SESSION["error_message"]="User does not own this game on Steam.";}
        $this->response->redirect("/admin/games/show/".$id);
    }

    protected function doAction($id, $m) { $this->requireAdmin(); $mg=new \Plugins\GameServers\Services\GameServerManager(); $mg->$m((int)$id); $this->response->redirect("/admin/games"); }
}
