import pathlib
p = pathlib.Path("/var/www/radiohosting/plugins/GameServers/Controllers/Admin/GameServersController.php")
c = p.read_text()

# Replace session-based tab redirects with GET param redirects
pairs = [
    ('$this->db->table("game_server_players")->where("server_id", $id)->orderBy("last_seen","DESC")->limit(50)->get() ?: [];',
     '$this->db->table("game_server_players")->where("server_id", $id)->orderBy("last_seen","DESC")->limit(50)->get() ?: [];'),
]

# Redirect tab methods to use ?tab=
import re
c = re.sub(r'\$_SESSION\["games_tab"\]="(players)";\s*\$this->response->redirect\("/admin/games/show/".\$id\);', r'$this->response->redirect("/admin/games/show/".$id."?tab=\1");', c)
c = re.sub(r'\$_SESSION\["games_tab"\]="(bans)";\s*\$this->response->redirect\("/admin/games/show/".\$id\);', r'$this->response->redirect("/admin/games/show/".$id."?tab=\1");', c)
c = re.sub(r'\$_SESSION\["games_tab"\]="(maps)";\s*\$this->response->redirect\("/admin/games/show/".\$id\);', r'$this->response->redirect("/admin/games/show/".$id."?tab=\1");', c)
c = re.sub(r'\$_SESSION\["games_tab"\]="(backups)";\s*\$this->response->redirect\("/admin/games/show/".\$id\);', r'$this->response->redirect("/admin/games/show/".$id."?tab=\1");', c)
c = re.sub(r'\$_SESSION\["games_tab"\]="(firewall)";\s*\$this->response->redirect\("/admin/games/show/".\$id\);', r'$this->response->redirect("/admin/games/show/".$id."?tab=\1");', c)
c = re.sub(r'\$_SESSION\["games_tab"\]="(network)";\s*\$this->response->redirect\("/admin/games/show/".\$id\);', r'$this->response->redirect("/admin/games/show/".$id."?tab=\1");', c)
c = re.sub(r'\$_SESSION\["games_tab"\]="(tasks)";\s*\$this->response->redirect\("/admin/games/show/".\$id\);', r'$this->response->redirect("/admin/games/show/".$id."?tab=\1");', c)
c = re.sub(r'\$_SESSION\["games_tab"\]="(notifications)";\s*\$this->response->redirect\("/admin/games/show/".\$id\);', r'$this->response->redirect("/admin/games/show/".$id."?tab=\1");', c)
c = re.sub(r'\$_SESSION\["games_tab"\]="(sub-users)";\s*\$this->response->redirect\("/admin/games/show/".\$id\);', r'$this->response->redirect("/admin/games/show/".$id."?tab=\1");', c)
c = re.sub(r'\$_SESSION\["games_tab"\]="(voice)";\s*\$this->response->redirect\("/admin/games/show/".\$id\);', r'$this->response->redirect("/admin/games/show/".$id."?tab=\1");', c)
c = re.sub(r'\$_SESSION\["games_tab"\]="(workshop)";\s*\$this->response->redirect\("/admin/games/show/".\$id\);', r'$this->response->redirect("/admin/games/show/".$id."?tab=\1");', c)

# Fix show() method to accept tab from GET or session
old_show = '''public function show(\$id)
    {
        \$this->requireAdmin();
        \$server = \$this->loadServer(\$id);
        \$user = \$this->auth->user();
        \$owner = \$this->db->table("hosting_users")->where("id", \$server->user_id)->first();
        \$gameType = \$this->db->table("game_types")->where("name", \$server->game_type)->first();
        \$hostingUsers = \$this->db->table("hosting_users")->orderBy("username", "ASC")->get() ?: [];
        \$configPath = \$server->config_path ?: \$server->install_path . "/server.cfg";
        \$configContent = file_exists(\$configPath) ? file_get_contents(\$configPath) : "";
        \$logFile = \$server->install_path . "/console.log";
        \$consoleLog = file_exists(\$logFile) ? file_get_contents(\$logFile) : "";'''

new_show = '''public function show(\$id)
    {
        \$this->requireAdmin();
        \$server = \$this->loadServer(\$id);
        \$user = \$this->auth->user();
        \$activeTab = \$_GET["tab"] ?? \$_SESSION["games_tab"] ?? "overview";
        unset(\$_SESSION["games_tab"]);
        \$owner = \$this->db->table("hosting_users")->where("id", \$server->user_id)->first();
        \$gameType = \$this->db->table("game_types")->where("name", \$server->game_type)->first();
        \$hostingUsers = \$this->db->table("hosting_users")->orderBy("username", "ASC")->get() ?: [];
        \$configPath = \$server->config_path ?: \$server->install_path . "/server.cfg";
        \$configContent = file_exists(\$configPath) ? file_get_contents(\$configPath) : "";
        \$logFile = \$server->install_path . "/console.log";
        \$consoleLog = file_exists(\$logFile) ? file_get_contents(\$logFile) : "";'''

c = c.replace(old_show, new_show)

# Add activeTab to the view data in show()
old_view = '''"voices"=>\$voices,"workshop"=>\$workshop,"title"=>"Game: " . \$server->server_name'''
new_view = '''"voices"=>\$voices,"workshop"=>\$workshop,"activeTab"=>\$activeTab,"title"=>"Game: " . \$server->server_name'''
c = c.replace(old_view, new_view)

p.write_text(c)
print("Fixed controller - tab redirects use ?tab= parameter")
