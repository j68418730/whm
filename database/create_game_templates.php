<?php
/**
 * Migration: Create game_templates table with 135+ Steam game entries
 * Run: php database/create_game_templates.php
 */

define('BASE_PATH', realpath(__DIR__ . '/..'));
require BASE_PATH . '/core/helpers.php';
// Load .env for CLI
$envFile = BASE_PATH . '/.env';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        putenv(trim($line));
    }
}

$config = require __DIR__ . '/../config/database.php';

$dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}";
$pdo = new PDO($dsn, $config['username'], $config['password'], [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

$pdo->exec("CREATE TABLE IF NOT EXISTS `game_templates` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(200) NOT NULL,
    `appid` VARCHAR(50) NOT NULL DEFAULT '',
    `engine` VARCHAR(50) NOT NULL DEFAULT 'Native',
    `category` VARCHAR(50) NOT NULL DEFAULT 'FPS',
    `steamcmd_login` VARCHAR(50) NOT NULL DEFAULT 'anonymous',
    `steam_client` TINYINT(1) NOT NULL DEFAULT 0,
    `anonymous_login` TINYINT(1) NOT NULL DEFAULT 1,
    `requires_game_purchase` TINYINT(1) NOT NULL DEFAULT 0,
    `supports_linux` TINYINT(1) NOT NULL DEFAULT 1,
    `supports_windows` TINYINT(1) NOT NULL DEFAULT 0,
    `install_script` TEXT,
    `start_command` TEXT,
    `stop_command` TEXT,
    `restart_command` TEXT,
    `query_port` INT NOT NULL DEFAULT 27015,
    `game_port` INT NOT NULL DEFAULT 27015,
    `rcon_port` INT NOT NULL DEFAULT 27020,
    `default_slots` INT NOT NULL DEFAULT 16,
    `min_slots` INT NOT NULL DEFAULT 10,
    `max_slots` INT NOT NULL DEFAULT 64,
    `description` TEXT,
    `notes` TEXT,
    `config_template` TEXT,
    `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_appid` (`appid`),
    INDEX `idx_engine` (`engine`),
    INDEX `idx_category` (`category`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// Check if we already have data
$stmt = $pdo->query("SELECT COUNT(*) AS cnt FROM `game_templates`");
$row = $stmt->fetch(PDO::FETCH_OBJ);
if ($row && (int)$row->cnt > 0) {
    echo "game_templates table already has {$row->cnt} entries. Skipping insert.\n";
    exit(0);
}

$installScript = "steamcmd +force_install_dir {INSTALL_DIR} +login {STEAMCMD_LOGIN} +app_update {APPID} validate +quit";
$configTemplate = "// {SERVER_NAME} - Auto-generated server configuration\n// Game: {GAME_NAME}\nhostname \"{SERVER_NAME}\"\nsv_contact \"\"\nsv_region -1\nsv_lan 0\nsv_password \"{PASSWORD}\"\nrcon_password \"{RCON_PASSWORD}\"\nrcon_port {RCON_PORT}\nmaxplayers {MAX_PLAYERS}\nport {PORT}\nmap {MAP}\nexec banned_ip.cfg\n";
$startCmd = "#!/bin/bash\ncd {INSTALL_DIR}\n./{SERVER_BINARY} -port {PORT} +maxplayers {MAX_PLAYERS} +map {MAP} +exec server.cfg";
$stopCmd = "#!/bin/bash\nkill $(cat {INSTALL_DIR}/server.pid 2>/dev/null) 2>/dev/null || pkill -f {SERVER_BINARY}";
$restartCmd = "#!/bin/bash\n{STOP_COMMAND}\nsleep 2\n{START_COMMAND}";

$games = [
    // ─── SOURCE ENGINE - FPS ───
    ['name'=>'Counter-Strike 2','appid'=>'730','engine'=>'Source','category'=>'FPS','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'supports_linux'=>1,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>16,'min_slots'=>10,'max_slots'=>64,'description'=>'Counter-Strike 2 dedicated server. Official competitive FPS from Valve.'],
    ['name'=>'Counter-Strike: Source','appid'=>'232330','engine'=>'Source','category'=>'FPS','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>16,'min_slots'=>10,'max_slots'=>32,'description'=>'Counter-Strike: Source dedicated server. Classic team-based FPS.'],
    ['name'=>'Counter-Strike 1.6','appid'=>'90','engine'=>'Source','category'=>'FPS','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>16,'min_slots'=>10,'max_slots'=>32,'description'=>'Counter-Strike 1.6 dedicated server. The original CS experience.'],
    ['name'=>'Counter-Strike: Condition Zero','appid'=>'80','engine'=>'Source','category'=>'FPS','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>16,'min_slots'=>10,'max_slots'=>32,'description'=>'Counter-Strike: Condition Zero dedicated server.'],
    ['name'=>'Team Fortress 2','appid'=>'232250','engine'=>'Source','category'=>'FPS','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>24,'min_slots'=>12,'max_slots'=>32,'description'=>'Team Fortress 2 dedicated server. Class-based multiplayer FPS.'],
    ['name'=>'Left 4 Dead 2','appid'=>'222860','engine'=>'Source','category'=>'FPS','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>8,'min_slots'=>4,'max_slots'=>32,'description'=>'Left 4 Dead 2 dedicated server. Co-op zombie survival FPS.'],
    ['name'=>'Left 4 Dead','appid'=>'17710','engine'=>'Source','category'=>'FPS','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>8,'min_slots'=>4,'max_slots'=>32,'description'=>'Left 4 Dead dedicated server. Original co-op zombie shooter.'],
    ['name'=>'Half-Life 2: Deathmatch','appid'=>'232370','engine'=>'Source','category'=>'FPS','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>16,'min_slots'=>8,'max_slots'=>32,'description'=>'Half-Life 2: Deathmatch dedicated server.'],
    ['name'=>'Half-Life Deathmatch: Source','appid'=>'260','engine'=>'Source','category'=>'FPS','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>16,'min_slots'=>8,'max_slots'=>32,'description'=>'Half-Life Deathmatch: Source dedicated server.'],
    ['name'=>'Day of Defeat: Source','appid'=>'232290','engine'=>'Source','category'=>'FPS','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>16,'min_slots'=>10,'max_slots'=>32,'description'=>'Day of Defeat: Source dedicated server. WWII-themed FPS.'],
    ['name'=>'Day of Defeat 1.6','appid'=>'30','engine'=>'Source','category'=>'FPS','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>16,'min_slots'=>10,'max_slots'=>32,'description'=>'Day of Defeat 1.6 dedicated server. Classic WWII shooter.'],
    ['name'=>'Team Fortress Classic','appid'=>'20','engine'=>'Source','category'=>'FPS','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>16,'min_slots'=>10,'max_slots'=>32,'description'=>'Team Fortress Classic dedicated server. Original class-based shooter.'],

    // ─── SOURCE ENGINE - SURVIVAL ───
    ['name'=>'Rust','appid'=>'258550','engine'=>'Unity','category'=>'Survival','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>28015,'game_port'=>28015,'rcon_port'=>28016,'default_slots'=>50,'min_slots'=>10,'max_slots'=>500,'description'=>'Rust dedicated server using SteamCMD. Open-world survival game.'],  // Note: Rust is Unity, not Source
    ['name'=>'SCP: Secret Laboratory','appid'=>'700330','engine'=>'Unity','category'=>'Survival','steamcmd_login'=>'anonymous','steam_client'=>0,'anonymous_login'=>1,'requires_game_purchase'=>0,'query_port'=>7777,'game_port'=>7777,'rcon_port'=>7778,'default_slots'=>20,'min_slots'=>10,'max_slots'=>64,'description'=>'SCP Secret Laboratory dedicated server.'],
    ['name'=>'The Forrest','appid'=>'246520','engine'=>'Unity','category'=>'Survival','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>4,'min_slots'=>2,'max_slots'=>16,'description'=>'The Forest dedicated server. Open-world survival horror.'],
    ['name'=>'7 Days to Die','appid'=>'294420','engine'=>'Unity','category'=>'Survival','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>26900,'game_port'=>26900,'rcon_port'=>26901,'default_slots'=>8,'min_slots'=>4,'max_slots'=>32,'description'=>'7 Days to Die dedicated server. Zombie survival crafting game.'],

    // ─── SOURCE ENGINE - SANDBOX ───
    ['name'=>'Garry\'s Mod','appid'=>'4020','engine'=>'Source','category'=>'Sandbox','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>16,'min_slots'=>8,'max_slots'=>32,'description'=>'Garry\'s Mod dedicated server. Physics sandbox with endless possibilities.'],
    ['name'=>'Source SDK Base 2013','appid'=>'243750','engine'=>'Source','category'=>'Sandbox','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>0,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>16,'min_slots'=>4,'max_slots'=>32,'description'=>'Source SDK Base 2013 dedicated server. Multiplayer Source engine base.'],
    ['name'=>'Source SDK Base 2007','appid'=>'218','engine'=>'Source','category'=>'Sandbox','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>0,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>16,'min_slots'=>4,'max_slots'=>32,'description'=>'Source SDK Base 2007 dedicated server. Legacy Source engine base.'],
    ['name'=>'Space Engineers','appid'=>'298740','engine'=>'Source','category'=>'Sandbox','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27016,'game_port'=>27016,'rcon_port'=>27017,'default_slots'=>16,'min_slots'=>4,'max_slots'=>32,'description'=>'Space Engineers dedicated server. Engineering sandbox in space.'],
    ['name'=>'Stormworks: Build and Rescue','appid'=>'573090','engine'=>'Unity','category'=>'Sandbox','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>25564,'game_port'=>25564,'rcon_port'=>25565,'default_slots'=>16,'min_slots'=>4,'max_slots'=>64,'description'=>'Stormworks dedicated server. Vehicle rescue and building sandbox.'],
    ['name'=>'From the Depths','appid'=>'268650','engine'=>'Unity','category'=>'Sandbox','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>26214,'game_port'=>26214,'rcon_port'=>26215,'default_slots'=>4,'min_slots'=>2,'max_slots'=>16,'description'=>'From the Depths dedicated server. Vehicle building sandbox.'],

    // ─── UNREAL ENGINE - FPS ───
    ['name'=>'Team Fortress 2 Classic','appid'=>'0','engine'=>'Source','category'=>'FPS','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>16,'min_slots'=>10,'max_slots'=>32,'description'=>'Team Fortress 2 Classic mod dedicated server.'],
    ['name'=>'Unreal Tournament 2004','appid'=>'0','engine'=>'Unreal','category'=>'FPS','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>7777,'game_port'=>7777,'rcon_port'=>7778,'default_slots'=>16,'min_slots'=>8,'max_slots'=>32,'description'=>'Unreal Tournament 2004 dedicated server. Classic arena FPS.'],
    ['name'=>'Unreal Tournament 3','appid'=>'13210','engine'=>'Unreal','category'=>'FPS','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>7777,'game_port'=>7777,'rcon_port'=>7778,'default_slots'=>16,'min_slots'=>8,'max_slots'=>32,'description'=>'Unreal Tournament 3 dedicated server.'],
    ['name'=>'Unreal Tournament GOTY','appid'=>'0','engine'=>'Unreal','category'=>'FPS','steamcmd_login'=>'anonymous','steam_client'=>0,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>7777,'game_port'=>7777,'rcon_port'=>7778,'default_slots'=>8,'min_slots'=>4,'max_slots'=>16,'description'=>'Unreal Tournament Game of the Year Edition server.'],
    ['name'=>'Insurgency: Modern Infantry Combat','appid'=>'17710','engine'=>'Source','category'=>'FPS','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>16,'min_slots'=>8,'max_slots'=>32,'description'=>'Insurgency dedicated server. Tactical FPS.'],

    // ─── SURVIVAL ───
    ['name'=>'Valheim','appid'=>'896660','engine'=>'Unity','category'=>'Survival','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>2456,'game_port'=>2456,'rcon_port'=>2457,'default_slots'=>10,'min_slots'=>4,'max_slots'=>64,'description'=>'Valheim dedicated server. Viking survival and exploration game.'],
    ['name'=>'V Rising','appid'=>'1829350','engine'=>'Unity','category'=>'Survival','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>40,'min_slots'=>10,'max_slots'=>80,'description'=>'V Rising dedicated server. Vampire survival game.'],
    ['name'=>'ARK: Survival Evolved','appid'=>'376030','engine'=>'Unreal','category'=>'Survival','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27015,'game_port'=>7777,'rcon_port'=>27020,'default_slots'=>70,'min_slots'=>10,'max_slots'=>100,'description'=>'ARK: Survival Evolved dedicated server. Dinosaur survival game.'],
    ['name'=>'ARK: Survival Ascended','appid'=>'2430930','engine'=>'Unreal','category'=>'Survival','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>7777,'game_port'=>7777,'rcon_port'=>27020,'default_slots'=>70,'min_slots'=>10,'max_slots'=>100,'description'=>'ARK: Survival Ascended dedicated server.'],
    ['name'=>'Conan Exiles','appid'=>'443030','engine'=>'Unreal','category'=>'Survival','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27015,'game_port'=>7777,'rcon_port'=>27020,'default_slots'=>40,'min_slots'=>10,'max_slots'=>100,'description'=>'Conan Exiles dedicated server. Open-world survival game.'],
    ['name'=>'DayZ','appid'=>'223350','engine'=>'Enfusion','category'=>'Survival','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>2302,'game_port'=>2302,'rcon_port'=>2303,'default_slots'=>60,'min_slots'=>20,'max_slots'=>128,'description'=>'DayZ dedicated server. Open-world zombie survival simulation.'],
    ['name'=>'SCUM','appid'=>'513710','engine'=>'Unreal','category'=>'Survival','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27015,'game_port'=>7777,'rcon_port'=>27020,'default_slots'=>60,'min_slots'=>10,'max_slots'=>100,'description'=>'SCUM dedicated server. Prisoner survival game.'],
    ['name'=>'Minecraft','appid'=>'0','engine'=>'Java','category'=>'Survival','steamcmd_login'=>'','steam_client'=>0,'anonymous_login'=>1,'requires_game_purchase'=>0,'supports_linux'=>1,'supports_windows'=>1,'query_port'=>25565,'game_port'=>25565,'rcon_port'=>25575,'default_slots'=>20,'min_slots'=>5,'max_slots'=>500,'description'=>'Minecraft Java Edition server. Vanilla or modded survival.'],
    ['name'=>'Project Zomboid','appid'=>'380870','engine'=>'Java','category'=>'Survival','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>16261,'game_port'=>16261,'rcon_port'=>16262,'default_slots'=>16,'min_slots'=>4,'max_slots'=>64,'description'=>'Project Zomboid dedicated server. Isometric zombie survival.'],
    ['name'=>'Unturned','appid'=>'1117100','engine'=>'Unity','category'=>'Survival','steamcmd_login'=>'anonymous','steam_client'=>0,'anonymous_login'=>1,'requires_game_purchase'=>0,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>24,'min_slots'=>8,'max_slots'=>64,'description'=>'Unturned dedicated server. Free zombie survival game.'],
    ['name'=>'Dont Starve Together','appid'=>'343050','engine'=>'Unity','category'=>'Survival','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>10999,'game_port'=>10999,'rcon_port'=>10998,'default_slots'=>6,'min_slots'=>2,'max_slots'=>64,'description'=>'Don\'t Starve Together dedicated server. Co-op wilderness survival.'],
    ['name'=>'Hurtworld','appid'=>'405100','engine'=>'Unity','category'=>'Survival','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>12871,'game_port'=>12871,'rcon_port'=>12872,'default_slots'=>60,'min_slots'=>10,'max_slots'=>100,'description'=>'Hurtworld dedicated server. Hardcore multiplayer survival.'],
    ['name'=>'Mist Survival','appid'=>'914620','engine'=>'Unity','category'=>'Survival','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>8,'min_slots'=>4,'max_slots'=>32,'description'=>'Mist Survival dedicated server. Survival horror simulation.'],

    // ─── SANDBOX ───
    ['name'=>'Terraria','appid'=>'105600','engine'=>'Native','category'=>'Sandbox','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>7777,'game_port'=>7777,'rcon_port'=>7778,'default_slots'=>8,'min_slots'=>2,'max_slots'=>255,'description'=>'Terraria dedicated server. 2D sandbox adventure game.'],
    ['name'=>'Starbound','appid'=>'367580','engine'=>'Native','category'=>'Sandbox','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>21025,'game_port'=>21025,'rcon_port'=>21026,'default_slots'=>8,'min_slots'=>2,'max_slots'=>64,'description'=>'Starbound dedicated server. Space exploration sandbox.'],
    ['name'=>'Stardew Valley','appid'=>'413150','engine'=>'Native','category'=>'Sandbox','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>24642,'game_port'=>24642,'rcon_port'=>24643,'default_slots'=>4,'min_slots'=>2,'max_slots'=>8,'description'=>'Stardew Valley dedicated server. Farming simulation multiplayer.'],
    ['name'=>'No Mans Sky','appid'=>'0','engine'=>'Native','category'=>'Sandbox','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>18000,'game_port'=>18000,'rcon_port'=>18001,'default_slots'=>32,'min_slots'=>4,'max_slots'=>64,'description'=>'No Man\'s Sky server. Procedural universe exploration.'],

    // ─── RPG ───
    ['name'=>'Skyrim Together','appid'=>'0','engine'=>'Native','category'=>'RPG','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>8,'min_slots'=>2,'max_slots'=>64,'description'=>'Skyrim Together Reborn dedicated server. Multiplayer mod for Skyrim.'],
    ['name'=>'The Elder Scrolls Online','appid'=>'0','engine'=>'Native','category'=>'RPG','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>24100,'game_port'=>24100,'rcon_port'=>24101,'default_slots'=>100,'min_slots'=>10,'max_slots'=>2000,'description'=>'TESO server.'],
    ['name'=>'Neverwinter Nights','appid'=>'0','engine'=>'Native','category'=>'RPG','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>5121,'game_port'=>5121,'rcon_port'=>5122,'default_slots'=>32,'min_slots'=>4,'max_slots'=>96,'description'=>'Neverwinter Nights Enhanced Edition dedicated server.'],
    ['name'=>'Portal Knights','appid'=>'422400','engine'=>'Unity','category'=>'RPG','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>4,'min_slots'=>2,'max_slots'=>16,'description'=>'Portal Knights dedicated server. Action RPG sandbox.'],
    ['name'=>'Guild Wars 2','appid'=>'0','engine'=>'Native','category'=>'RPG','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>100,'min_slots'=>10,'max_slots'=>2000,'description'=>'Guild Wars 2 server.'],

    // ─── SIMULATION ───
    ['name'=>'Euro Truck Simulator 2','appid'=>'227300','engine'=>'Native','category'=>'Simulation','steamcmd_login'=>'anonymous','steam_client'=>0,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>8,'min_slots'=>2,'max_slots'=>32,'description'=>'Euro Truck Simulator 2 multiplayer via TruckersMP.'],
    ['name'=>'American Truck Simulator','appid'=>'270880','engine'=>'Native','category'=>'Simulation','steamcmd_login'=>'anonymous','steam_client'=>0,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>8,'min_slots'=>2,'max_slots'=>32,'description'=>'American Truck Simulator multiplayer server.'],
    ['name'=>'Microsoft Flight Simulator','appid'=>'0','engine'=>'Native','category'=>'Simulation','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>64,'min_slots'=>10,'max_slots'=>500,'description'=>'Microsoft Flight Simulator dedicated server.'],
    ['name'=>'Cities: Skylines','appid'=>'0','engine'=>'Unity','category'=>'Simulation','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>25,'min_slots'=>5,'max_slots'=>100,'description'=>'Cities: Skylines multiplayer mod server.'],
    ['name'=>'Farming Simulator 22','appid'=>'2137720','engine'=>'Native','category'=>'Simulation','steamcmd_login'=>'anonymous','steam_client'=>0,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>10823,'game_port'=>10823,'rcon_port'=>10824,'default_slots'=>6,'min_slots'=>2,'max_slots'=>16,'description'=>'Farming Simulator 22 dedicated server.'],
    ['name'=>'Farming Simulator 19','appid'=>'805550','engine'=>'Native','category'=>'Simulation','steamcmd_login'=>'anonymous','steam_client'=>0,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>10823,'game_port'=>10823,'rcon_port'=>10824,'default_slots'=>6,'min_slots'=>2,'max_slots'=>16,'description'=>'Farming Simulator 19 dedicated server.'],
    ['name'=>'Farming Simulator 25','appid'=>'3010520','engine'=>'Native','category'=>'Simulation','steamcmd_login'=>'anonymous','steam_client'=>0,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>10823,'game_port'=>10823,'rcon_port'=>10824,'default_slots'=>6,'min_slots'=>2,'max_slots'=>16,'description'=>'Farming Simulator 25 dedicated server.'],
    ['name'=>'Kerbal Space Program','appid'=>'220200','engine'=>'Unity','category'=>'Simulation','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>8,'min_slots'=>2,'max_slots'=>32,'description'=>'Kerbal Space Program dedicated server.'],

    // ─── RACING ───
    ['name'=>'Assetto Corsa','appid'=>'244210','engine'=>'Native','category'=>'Racing','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>9600,'game_port'=>9600,'rcon_port'=>9601,'default_slots'=>16,'min_slots'=>2,'max_slots'=>64,'description'=>'Assetto Corsa dedicated server. Realistic racing simulator.'],
    ['name'=>'Assetto Corsa Competizione','appid'=>'805550','engine'=>'Unreal','category'=>'Racing','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>9600,'game_port'=>9600,'rcon_port'=>9601,'default_slots'=>30,'min_slots'=>2,'max_slots'=>64,'description'=>'Assetto Corsa Competizione dedicated server. GT racing sim.'],
    ['name'=>'iRacing','appid'=>'0','engine'=>'Native','category'=>'Racing','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>40,'min_slots'=>2,'max_slots'=>64,'description'=>'iRacing dedicated server. Online racing simulation.'],
    ['name'=>'rFactor 2','appid'=>'339200','engine'=>'Native','category'=>'Racing','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>64297,'game_port'=>64297,'rcon_port'=>64298,'default_slots'=>32,'min_slots'=>2,'max_slots'=>64,'description'=>'rFactor 2 dedicated server. Advanced racing simulation.'],
    ['name'=>'RaceRoom Racing Experience','appid'=>'0','engine'=>'Native','category'=>'Racing','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>30,'min_slots'=>2,'max_slots'=>64,'description'=>'RaceRoom Racing Experience server.'],
    ['name'=>'Project CARS 2','appid'=>'0','engine'=>'Native','category'=>'Racing','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>16,'min_slots'=>2,'max_slots'=>32,'description'=>'Project CARS 2 dedicated server.'],
    ['name'=>'Trackmania Nations Forever','appid'=>'11020','engine'=>'Native','category'=>'Racing','steamcmd_login'=>'anonymous','steam_client'=>0,'anonymous_login'=>1,'requires_game_purchase'=>0,'query_port'=>2350,'game_port'=>2350,'rcon_port'=>2351,'default_slots'=>32,'min_slots'=>2,'max_slots'=>64,'description'=>'Trackmania Nations Forever server. Free arcade racing.'],
    ['name'=>'Trackmania 2 Stadium','appid'=>'232910','engine'=>'Native','category'=>'Racing','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>2350,'game_port'=>2350,'rcon_port'=>2351,'default_slots'=>32,'min_slots'=>2,'max_slots'=>64,'description'=>'Trackmania 2 Stadium dedicated server.'],
    ['name'=>'Trackmania 2020','appid'=>'0','engine'=>'Native','category'=>'Racing','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>2345,'game_port'=>2345,'rcon_port'=>2346,'default_slots'=>32,'min_slots'=>2,'max_slots'=>64,'description'=>'Trackmania 2020 dedicated server.'],

    // ─── MILITARY ───
    ['name'=>'Arma 3','appid'=>'233780','engine'=>'Real Virtuality','category'=>'Military','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>2302,'game_port'=>2302,'rcon_port'=>2303,'default_slots'=>64,'min_slots'=>10,'max_slots'=>128,'description'=>'Arma 3 dedicated server. Military simulation FPS.'],
    ['name'=>'Arma 2: Operation Arrowhead','appid'=>'33930','engine'=>'Real Virtuality','category'=>'Military','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>2302,'game_port'=>2302,'rcon_port'=>2303,'default_slots'=>64,'min_slots'=>10,'max_slots'=>128,'description'=>'Arma 2 Operation Arrowhead dedicated server.'],
    ['name'=>'Arma Reforger','appid'=>'1874880','engine'=>'Enfusion','category'=>'Military','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>2001,'game_port'=>2001,'rcon_port'=>2002,'default_slots'=>64,'min_slots'=>10,'max_slots'=>128,'description'=>'Arma Reforger dedicated server. Next-gen military sim.'],
    ['name'=>'Squad','appid'=>'393380','engine'=>'Unreal','category'=>'Military','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>7787,'game_port'=>7787,'rcon_port'=>7788,'default_slots'=>80,'min_slots'=>20,'max_slots'=>100,'description'=>'Squad dedicated server. Tactical military FPS.'],
    ['name'=>'Post Scriptum','appid'=>'778980','engine'=>'Unreal','category'=>'Military','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>80,'min_slots'=>20,'max_slots'=>100,'description'=>'Post Scriptum dedicated server. WWII tactical shooter.'],
    ['name'=>'Hell Let Loose','appid'=>'686810','engine'=>'Unreal','category'=>'Military','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>50,'min_slots'=>20,'max_slots'=>100,'description'=>'Hell Let Loose dedicated server. WWII platoon-based FPS.'],
    ['name'=>'War Thunder','appid'=>'0','engine'=>'Dagor','category'=>'Military','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>0,'query_port'=>8111,'game_port'=>8111,'rcon_port'=>8112,'default_slots'=>32,'min_slots'=>8,'max_slots'=>64,'description'=>'War Thunder dedicated server. Military vehicle combat.'],
    ['name'=>'World of Tanks','appid'=>'0','engine'=>'Native','category'=>'Military','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>0,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>30,'min_slots'=>10,'max_slots'=>60,'description'=>'World of Tanks server. Team-based tank combat.'],
    ['name'=>'World of Warships','appid'=>'0','engine'=>'Native','category'=>'Military','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>0,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>24,'min_slots'=>10,'max_slots'=>48,'description'=>'World of Warships server. Naval warfare combat.'],
    ['name'=>'Escape from Tarkov','appid'=>'0','engine'=>'Unity','category'=>'Military','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>12,'min_slots'=>4,'max_slots'=>16,'description'=>'Escape from Tarkov server. Hardcore FPS/TPS.'],
    ['name'=>'Rising Storm 2: Vietnam','appid'=>'418460','engine'=>'Unreal','category'=>'Military','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27015,'game_port'=>7777,'rcon_port'=>27020,'default_slots'=>64,'min_slots'=>16,'max_slots'=>64,'description'=>'Rising Storm 2 Vietnam dedicated server.'],
    ['name'=>'Insurgency: Sandstorm','appid'=>'581320','engine'=>'Unreal','category'=>'Military','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>28,'min_slots'=>8,'max_slots'=>64,'description'=>'Insurgency Sandstorm dedicated server. Tactical FPS.'],
    ['name'=>'Operation Flashpoint','appid'=>'0','engine'=>'Real Virtuality','category'=>'Military','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>2302,'game_port'=>2302,'rcon_port'=>2303,'default_slots'=>32,'min_slots'=>8,'max_slots'=>64,'description'=>'Operation Flashpoint dedicated server.'],
    ['name'=>'DCS World','appid'=>'223750','engine'=>'Native','category'=>'Military','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>0,'query_port'=>10308,'game_port'=>10308,'rcon_port'=>10309,'default_slots'=>64,'min_slots'=>4,'max_slots'=>500,'description'=>'DCS World dedicated server. Combat flight sim.'],
    ['name'=>'Wargame: Red Dragon','appid'=>'0','engine'=>'Native','category'=>'Military','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>20,'min_slots'=>2,'max_slots'=>40,'description'=>'Wargame Red Dragon server. Real-time military tactics.'],

    // ─── MORE FPS ───
    ['name'=>'Call of Duty: Black Ops 3','appid'=>'311210','engine'=>'Native','category'=>'FPS','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>18,'min_slots'=>8,'max_slots'=>32,'description'=>'Call of Duty Black Ops 3 dedicated server.'],
    ['name'=>'Call of Duty: Modern Warfare 2','appid'=>'0','engine'=>'Native','category'=>'FPS','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>18,'min_slots'=>8,'max_slots'=>32,'description'=>'Call of Duty Modern Warfare 2 server.'],
    ['name'=>'Call of Duty: Modern Warfare 3','appid'=>'42690','engine'=>'Native','category'=>'FPS','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>18,'min_slots'=>8,'max_slots'=>32,'description'=>'Call of Duty Modern Warfare 3 dedicated server.'],
    ['name'=>'Call of Duty: World at War','appid'=>'10090','engine'=>'Native','category'=>'FPS','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>28960,'game_port'=>28960,'rcon_port'=>28961,'default_slots'=>18,'min_slots'=>8,'max_slots'=>32,'description'=>'Call of Duty World at War dedicated server.'],
    ['name'=>'Call of Duty 4: Modern Warfare','appid'=>'7940','engine'=>'Native','category'=>'FPS','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>28960,'game_port'=>28960,'rcon_port'=>28961,'default_slots'=>18,'min_slots'=>8,'max_slots'=>32,'description'=>'Call of Duty 4: Modern Warfare dedicated server.'],
    ['name'=>'Battlefield 2','appid'=>'0','engine'=>'Native','category'=>'FPS','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>4711,'game_port'=>4711,'rcon_port'=>4712,'default_slots'=>32,'min_slots'=>8,'max_slots'=>64,'description'=>'Battlefield 2 dedicated server.'],
    ['name'=>'Battlefield 4','appid'=>'0','engine'=>'Frostbite','category'=>'FPS','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>32,'min_slots'=>8,'max_slots'=>64,'description'=>'Battlefield 4 server.'],
    ['name'=>'Battlefield 1942','appid'=>'0','engine'=>'Native','category'=>'FPS','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>14567,'game_port'=>14567,'rcon_port'=>14568,'default_slots'=>32,'min_slots'=>8,'max_slots'=>64,'description'=>'Battlefield 1942 dedicated server.'],
    ['name'=>'Battlefield: Bad Company 2','appid'=>'0','engine'=>'Frostbite','category'=>'FPS','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>16,'min_slots'=>8,'max_slots'=>32,'description'=>'Battlefield Bad Company 2 server.'],
    ['name'=>'Battlefield 3','appid'=>'0','engine'=>'Frostbite','category'=>'FPS','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>32,'min_slots'=>8,'max_slots'=>64,'description'=>'Battlefield 3 server.'],
    ['name'=>'Battlefield 1','appid'=>'0','engine'=>'Frostbite','category'=>'FPS','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>32,'min_slots'=>8,'max_slots'=>64,'description'=>'Battlefield 1 server.'],
    ['name'=>'Battlefield V','appid'=>'0','engine'=>'Frostbite','category'=>'FPS','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>32,'min_slots'=>8,'max_slots'=>64,'description'=>'Battlefield V server.'],
    ['name'=>'Titanfall 2','appid'=>'1237970','engine'=>'Source','category'=>'FPS','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>37015,'game_port'=>37015,'rcon_port'=>37020,'default_slots'=>12,'min_slots'=>4,'max_slots'=>24,'description'=>'Titanfall 2 dedicated server. High-speed mech FPS.'],
    ['name'=>'Apex Legends','appid'=>'0','engine'=>'Source','category'=>'FPS','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>0,'query_port'=>37015,'game_port'=>37015,'rcon_port'=>37020,'default_slots'=>60,'min_slots'=>20,'max_slots'=>120,'description'=>'Apex Legends server. Battle royale FPS.'],
    ['name'=>'Overwatch 2','appid'=>'0','engine'=>'Native','category'=>'FPS','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>0,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>10,'min_slots'=>4,'max_slots'=>12,'description'=>'Overwatch 2 server. Team-based hero FPS.'],
    ['name'=>'Destiny 2','appid'=>'1085660','engine'=>'Native','category'=>'FPS','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>0,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>6,'min_slots'=>2,'max_slots'=>12,'description'=>'Destiny 2 server.'],
    ['name'=>'Quake Live','appid'=>'282440','engine'=>'id Tech','category'=>'FPS','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27960,'game_port'=>27960,'rcon_port'=>27961,'default_slots'=>8,'min_slots'=>2,'max_slots'=>16,'description'=>'Quake Live dedicated server. Arena FPS.'],
    ['name'=>'Quake 3 Arena','appid'=>'0','engine'=>'id Tech','category'=>'FPS','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27960,'game_port'=>27960,'rcon_port'=>27961,'default_slots'=>8,'min_slots'=>2,'max_slots'=>16,'description'=>'Quake 3 Arena dedicated server. Classic arena FPS.'],
    ['name'=>'Quake Champions','appid'=>'0','engine'=>'id Tech','category'=>'FPS','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>0,'query_port'=>27960,'game_port'=>27960,'rcon_port'=>27961,'default_slots'=>8,'min_slots'=>2,'max_slots'=>16,'description'=>'Quake Champions server.'],
    ['name'=>'Doom (2016)','appid'=>'0','engine'=>'id Tech','category'=>'FPS','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>12,'min_slots'=>4,'max_slots'=>16,'description'=>'Doom 2016 server.'],
    ['name'=>'Doom Eternal','appid'=>'0','engine'=>'id Tech','category'=>'FPS','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>12,'min_slots'=>4,'max_slots'=>16,'description'=>'Doom Eternal server.'],
    ['name'=>'Wolfenstein: Enemy Territory','appid'=>'0','engine'=>'id Tech','category'=>'FPS','steamcmd_login'=>'anonymous','steam_client'=>0,'anonymous_login'=>1,'requires_game_purchase'=>0,'query_port'=>27960,'game_port'=>27960,'rcon_port'=>27961,'default_slots'=>16,'min_slots'=>8,'max_slots'=>32,'description'=>'Wolfenstein Enemy Territory dedicated server.'],
    ['name'=>'Medal of Honor: Allied Assault','appid'=>'0','engine'=>'id Tech','category'=>'FPS','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>12203,'game_port'=>12203,'rcon_port'=>12204,'default_slots'=>16,'min_slots'=>8,'max_slots'=>32,'description'=>'Medal of Honor Allied Assault server.'],
    ['name'=>'PlanetSide 2','appid'=>'0','engine'=>'Native','category'=>'FPS','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>0,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>200,'min_slots'=>20,'max_slots'=>2000,'description'=>'PlanetSide 2 server. Massive scale MMOFPS.'],

    // ─── MORE SURVIVAL ───
    ['name'=>'Raft','appid'=>'648800','engine'=>'Unity','category'=>'Survival','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>8,'min_slots'=>2,'max_slots'=>32,'description'=>'Raft dedicated server. Ocean survival crafting.'],
    ['name'=>'Green Hell','appid'=>'815370','engine'=>'Unity','category'=>'Survival','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>4,'min_slots'=>2,'max_slots'=>16,'description'=>'Green Hell dedicated server. Jungle survival.'],
    ['name'=>'Subsistence','appid'=>'468700','engine'=>'Unity','category'=>'Survival','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>32,'min_slots'=>4,'max_slots'=>100,'description'=>'Subsistence dedicated server. Hardcore sandbox survival.'],
    ['name'=>'Medieval Dynasty','appid'=>'1129580','engine'=>'Unreal','category'=>'Survival','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>4,'min_slots'=>2,'max_slots'=>64,'description'=>'Medieval Dynasty dedicated server.'],
    ['name'=>'Soulmask','appid'=>'2262320','engine'=>'Unreal','category'=>'Survival','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>40,'min_slots'=>'10','max_slots'=>'70','description'=>'Soulmask dedicated server. Tribal survival game.'],
    ['name'=>'Enshrouded','appid'=>'1203620','engine'=>'Native','category'=>'Survival','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>16,'min_slots'=>4,'max_slots'=>32,'description'=>'Enshrouded dedicated server. Action survival RPG.'],
    ['name'=>'Palworld','appid'=>'2395090','engine'=>'Unreal','category'=>'Survival','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>8211,'game_port'=>8211,'rcon_port'=>8212,'default_slots'=>32,'min_slots'=>4,'max_slots'=>64,'description'=>'Palworld dedicated server. Creature-collecting survival.'],

    // ─── MORE SANDBOX ───
    ['name'=>'Besiege','appid'=>'0','engine'=>'Unity','category'=>'Sandbox','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>4,'min_slots'=>2,'max_slots'=>16,'description'=>'Besiege dedicated server. Physics building sandbox.'],
    ['name'=>'Scrap Mechanic','appid'=>'387990','engine'=>'Native','category'=>'Sandbox','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>6,'min_slots'=>2,'max_slots'=>16,'description'=>'Scrap Mechanic dedicated server. Creative engineering sandbox.'],
    ['name'=>'Trailmakers','appid'=>'585420','engine'=>'Unity','category'=>'Sandbox','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>4,'min_slots'=>2,'max_slots'=>16,'description'=>'Trailmakers dedicated server. Vehicle building sandbox.'],

    // ─── MORE RPG ───
    ['name'=>'Divinity: Original Sin 2','appid'=>'435150','engine'=>'Native','category'=>'RPG','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>4,'min_slots'=>2,'max_slots'=>8,'description'=>'Divinity Original Sin 2 dedicated server. RPG.'],
    ['name'=>'Baldurs Gate 3','appid'=>'0','engine'=>'Native','category'=>'RPG','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>4,'min_slots'=>2,'max_slots'=>8,'description'=>'Baldurs Gate 3 server.'],
    ['name'=>'Grim Dawn','appid'=>'219990','engine'=>'Native','category'=>'RPG','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>4,'min_slots'=>2,'max_slots'=>16,'description'=>'Grim Dawn server. Action RPG.'],
    ['name'=>'Titan Quest','appid'=>'475150','engine'=>'Native','category'=>'RPG','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>6,'min_slots'=>2,'max_slots'=>16,'description'=>'Titan Quest dedicated server. Mythological action RPG.'],
    ['name'=>'Path of Exile','appid'=>'0','engine'=>'Native','category'=>'RPG','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>0,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>6,'min_slots'=>2,'max_slots'=>16,'description'=>'Path of Exile server.'],
    ['name'=>'Diablo II: Resurrected','appid'=>'0','engine'=>'Native','category'=>'RPG','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>8,'min_slots'=>2,'max_slots'=>16,'description'=>'Diablo II Resurrected server.'],
    ['name'=>'Diablo III','appid'=>'0','engine'=>'Native','category'=>'RPG','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>4,'min_slots'=>2,'max_slots'=>8,'description'=>'Diablo III server.'],
    ['name'=>'Borderlands 2','appid'=>'49520','engine'=>'Unreal','category'=>'RPG','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>4,'min_slots'=>2,'max_slots'=>8,'description'=>'Borderlands 2 server. Looter shooter RPG.'],
    ['name'=>'Borderlands 3','appid'=>'397540','engine'=>'Unreal','category'=>'RPG','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>4,'min_slots'=>2,'max_slots'=>8,'description'=>'Borderlands 3 server.'],

    // ─── MORE SIMULATION ───
    ['name'=>'BeamNG.drive','appid'=>'284160','engine'=>'Native','category'=>'Simulation','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>8,'min_slots'=>2,'max_slots'=>32,'description'=>'BeamNG.drive dedicated server. Soft-body physics simulation.'],
    ['name'=>'SimplePlanes','appid'=>'0','engine'=>'Unity','category'=>'Simulation','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>4,'min_slots'=>2,'max_slots'=>16,'description'=>'SimplePlanes dedicated server. Aircraft building sandbox.'],
    ['name'=>'Car Mechanic Simulator 2021','appid'=>'1190340','engine'=>'Unity','category'=>'Simulation','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>4,'min_slots'=>2,'max_slots'=>16,'description'=>'Car Mechanic Simulator 2021 server.'],
    ['name'=>'PC Building Simulator','appid'=>'621060','engine'=>'Unity','category'=>'Simulation','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>4,'min_slots'=>2,'max_slots'=>16,'description'=>'PC Building Simulator server.'],
    ['name'=>'House Flipper','appid'=>'613100','engine'=>'Unity','category'=>'Simulation','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>4,'min_slots'=>2,'max_slots'=>16,'description'=>'House Flipper server.'],
    ['name'=>'PowerWash Simulator','appid'=>'1290000','engine'=>'Unreal','category'=>'Simulation','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>4,'min_slots'=>2,'max_slots'=>16,'description'=>'PowerWash Simulator server.'],
    ['name'=>'Gas Station Simulator','appid'=>'1142800','engine'=>'Unreal','category'=>'Simulation','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>4,'min_slots'=>2,'max_slots'=>'16','description'=>'Gas Station Simulator server.'],

    // ─── MORE RACING ───
    ['name'=>'Dirt Rally 2.0','appid'=>'690790','engine'=>'Native','category'=>'Racing','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>8,'min_slots'=>2,'max_slots'=>64,'description'=>'Dirt Rally 2.0 dedicated server. Rally racing simulation.'],
    ['name'=>'Dirt 4','appid'=>'421020','engine'=>'Native','category'=>'Racing','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>8,'min_slots'=>2,'max_slots'=>64,'description'=>'Dirt 4 dedicated server. Off-road racing.'],
    ['name'=>'Forza Horizon 5','appid'=>'0','engine'=>'Native','category'=>'Racing','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>12,'min_slots'=>'2','max_slots'=>'72','description'=>'Forza Horizon 5 server. Open-world racing.'],
    ['name'=>'Forza Motorsport 7','appid'=>'0','engine'=>'Native','category'=>'Racing','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>24,'min_slots'=>2,'max_slots'=>64,'description'=>'Forza Motorsport 7 server.'],
    ['name'=>'Grand Turismo Sport','appid'=>'0','engine'=>'Native','category'=>'Racing','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>16,'min_slots'=>2,'max_slots'=>32,'description'=>'Gran Turismo Sport server.'],

    // ─── SOURCE ENGINE - ADDITIONAL ───
    ['name'=>'Half-Life 2: Capture the Flag','appid'=>'0','engine'=>'Source','category'=>'FPS','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>16,'min_slots'=>8,'max_slots'=>32,'description'=>'Half-Life 2 CTF mod dedicated server.'],
    ['name'=>'Synergy','appid'=>'17520','engine'=>'Source','category'=>'FPS','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>8,'min_slots'=>2,'max_slots'=>32,'description'=>'Synergy dedicated server. Co-op HL2 mod.'],
    ['name'=>'Obsidian Conflict','appid'=>'0','engine'=>'Source','category'=>'FPS','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>8,'min_slots'=>2,'max_slots'=>32,'description'=>'Obsidian Conflict dedicated server. Co-op HL2 mod.'],
    ['name'=>'Eternal Silence','appid'=>'0','engine'=>'Source','category'=>'FPS','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>16,'min_slots'=>8,'max_slots'=>32,'description'=>'Eternal Silence dedicated server. Space combat Source mod.'],
    ['name'=>'Dystopia','appid'=>'0','engine'=>'Source','category'=>'FPS','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>16,'min_slots'=>8,'max_slots'=>32,'description'=>'Dystopia dedicated server. Cyberpunk Source mod.'],
    ['name'=>'Zombie Panic! Source','appid'=>'17505','engine'=>'Source','category'=>'FPS','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>16,'min_slots'=>8,'max_slots'=>32,'description'=>'Zombie Panic Source dedicated server. Co-op zombie Source mod.'],
    ['name'=>'Pirates Vikings and Knights II','appid'=>'0','engine'=>'Source','category'=>'FPS','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>16,'min_slots'=>8,'max_slots'=>32,'description'=>'Pirates Vikings and Knights II server. Medieval Source mod.'],
    ['name'=>'Battalion Wars','appid'=>'0','engine'=>'Source','category'=>'FPS','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>16,'min_slots'=>8,'max_slots'=>32,'description'=>'Battalion Wars Source mod dedicated server.'],
    ['name'=>'Neotokyo','appid'=>'16030','engine'=>'Source','category'=>'FPS','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>16,'min_slots'=>8,'max_slots'=>32,'description'=>'Neotokyo dedicated server. Cyberpunk tactical Source mod.'],
    ['name'=>'Double Action: Boogaloo','appid'=>'0','engine'=>'Source','category'=>'FPS','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>16,'min_slots'=>8,'max_slots'=>32,'description'=>'Double Action Boogaloo server. Action-movie Source mod.'],
    ['name'=>'The Hidden: Source','appid'=>'0','engine'=>'Source','category'=>'FPS','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>12,'min_slots'=>4,'max_slots'=>24,'description'=>'The Hidden Source dedicated server. Async multiplayer Source mod.'],

    // ─── NATIVE/OTHER ───
    ['name'=>'Soldat','appid'=>'0','engine'=>'Native','category'=>'FPS','steamcmd_login'=>'anonymous','steam_client'=>0,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>23073,'game_port'=>23073,'rcon_port'=>23074,'default_slots'=>16,'min_slots'=>2,'max_slots'=>32,'description'=>'Soldat dedicated server. 2D multiplayer shooter.'],
    ['name'=>'Cube 2: Sauerbraten','appid'=>'0','engine'=>'Native','category'=>'FPS','steamcmd_login'=>'anonymous','steam_client'=>0,'anonymous_login'=>1,'requires_game_purchase'=>0,'query_port'=>28785,'game_port'=>28785,'rcon_port'=>28786,'default_slots'=>8,'min_slots'=>2,'max_slots'=>64,'description'=>'Cube 2 Sauerbraten server. Open-source FPS.'],
    ['name'=>'Xonotic','appid'=>'0','engine'=>'Native','category'=>'FPS','steamcmd_login'=>'anonymous','steam_client'=>0,'anonymous_login'=>1,'requires_game_purchase'=>0,'query_port'=>26000,'game_port'=>26000,'rcon_port'=>26001,'default_slots'=>8,'min_slots'=>2,'max_slots'=>32,'description'=>'Xonotic dedicated server. Free arena FPS.'],
    ['name'=>'OpenArena','appid'=>'0','engine'=>'id Tech','category'=>'FPS','steamcmd_login'=>'anonymous','steam_client'=>0,'anonymous_login'=>1,'requires_game_purchase'=>0,'query_port'=>27960,'game_port'=>27960,'rcon_port'=>27961,'default_slots'=>8,'min_slots'=>2,'max_slots'=>16,'description'=>'OpenArena server. Free Quake 3 Arena clone.'],
    ['name'=>'Unvanquished','appid'=>'0','engine'=>'Native','category'=>'FPS','steamcmd_login'=>'anonymous','steam_client'=>0,'anonymous_login'=>1,'requires_game_purchase'=>0,'query_port'=>27960,'game_port'=>27960,'rcon_port'=>27961,'default_slots'=>16,'min_slots'=>4,'max_slots'=>32,'description'=>'Unvanquished server. Open-source RTS/FPS hybrid.'],

    // ─── JAVA EDITION ───
    ['name'=>'Minecraft: Paper','appid'=>'0','engine'=>'Java','category'=>'Sandbox','steamcmd_login'=>'','steam_client'=>0,'anonymous_login'=>1,'requires_game_purchase'=>0,'supports_linux'=>1,'supports_windows'=>1,'query_port'=>25565,'game_port'=>25565,'rcon_port'=>25575,'default_slots'=>20,'min_slots'=>5,'max_slots'=>500,'description'=>'Minecraft Paper server. High-performance PaperMC fork.'],
    ['name'=>'Minecraft: Spigot','appid'=>'0','engine'=>'Java','category'=>'Sandbox','steamcmd_login'=>'','steam_client'=>0,'anonymous_login'=>1,'requires_game_purchase'=>0,'supports_linux'=>1,'supports_windows'=>1,'query_port'=>25565,'game_port'=>25565,'rcon_port'=>25575,'default_slots'=>20,'min_slots'=>5,'max_slots'=>500,'description'=>'Minecraft Spigot server. Popular Bukkit fork.'],
    ['name'=>'Minecraft: Fabric','appid'=>'0','engine'=>'Java','category'=>'Sandbox','steamcmd_login'=>'','steam_client'=>0,'anonymous_login'=>1,'requires_game_purchase'=>0,'supports_linux'=>1,'supports_windows'=>1,'query_port'=>25565,'game_port'=>25565,'rcon_port'=>25575,'default_slots'=>20,'min_slots'=>5,'max_slots'=>500,'description'=>'Minecraft Fabric server. Modding-focused server.'],
    ['name'=>'Minecraft: Forge','appid'=>'0','engine'=>'Java','category'=>'Sandbox','steamcmd_login'=>'','steam_client'=>0,'anonymous_login'=>1,'requires_game_purchase'=>0,'supports_linux'=>1,'supports_windows'=>1,'query_port'=>25565,'game_port'=>25565,'rcon_port'=>25575,'default_slots'=>20,'min_slots'=>5,'max_slots'=>500,'description'=>'Minecraft Forge server. Modded Minecraft.'],
    ['name'=>'Minecraft: Vanilla','appid'=>'0','engine'=>'Java','category'=>'Sandbox','steamcmd_login'=>'','steam_client'=>0,'anonymous_login'=>1,'requires_game_purchase'=>0,'supports_linux'=>1,'supports_windows'=>1,'query_port'=>25565,'game_port'=>25565,'rcon_port'=>25575,'default_slots'=>20,'min_slots'=>5,'max_slots'=>500,'description'=>'Minecraft Vanilla server. Official Mojang server jar.'],
    ['name'=>'KaetServer','appid'=>'0','engine'=>'Java','category'=>'Sandbox','steamcmd_login'=>'','steam_client'=>0,'anonymous_login'=>1,'requires_game_purchase'=>0,'supports_linux'=>1,'supports_windows'=>1,'query_port'=>25565,'game_port'=>25565,'rcon_port'=>25575,'default_slots'=>20,'min_slots'=>5,'max_slots'=>500,'description'=>'KaetServer for Minecraft. Multi-platform.'],

    // ─── ADDITIONAL UNREAL ───
    ['name'=>'H1Z1: Just Survive','appid'=>'438100','engine'=>'Native','category'=>'Survival','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>100,'min_slots'=>10,'max_slots'=>200,'description'=>'H1Z1 Just Survive dedicated server.'],
    ['name'=>'Miscreated','appid'=>'299740','engine'=>'CryEngine','category'=>'Survival','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27015,'game_port'=>64090,'rcon_port'=>27020,'default_slots'=>36,'min_slots'=>10,'max_slots'=>'64','description'=>'Miscreated dedicated server. Post-apocalyptic survival.'],
    ['name'=>'Deadside','appid'=>'895400','engine'=>'Unreal','category'=>'Survival','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>50,'min_slots'=>10,'max_slots'=>100,'description'=>'Deadside dedicated server. Open-world survival shooter.'],
    ['name'=>'Stalker: Anomaly','appid'=>'0','engine'=>'Native','category'=>'Survival','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>0,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>32,'min_slots'=>4,'max_slots'=>128,'description'=>'Stalker Anomaly dedicated server. Free standalone survival mod.'],
    ['name'=>'Ashes of the Singularity','appid'=>'228380','engine'=>'Unreal','category'=>'Military','steamcmd_login'=>'anonymous','steam_client'=>1,'anonymous_login'=>1,'requires_game_purchase'=>1,'query_port'=>27015,'game_port'=>27015,'rcon_port'=>27020,'default_slots'=>8,'min_slots'=>2,'max_slots'=>16,'description'=>'Ashes of the Singularity dedicated server. RTS game.'],
];

// Map game_type names to descriptions from existing data
$typeDescriptions = [];
try {
    $gtStmt = $pdo->query("SELECT name, description FROM game_types WHERE is_active = 1");
    while ($gt = $gtStmt->fetch(PDO::FETCH_OBJ)) {
        $typeDescriptions[strtolower(trim($gt->name))] = $gt->description;
    }
} catch (Exception $e) {
    // game_types table might not exist
}

$insertSql = "INSERT INTO `game_templates` 
    (`name`, `appid`, `engine`, `category`, `steamcmd_login`, `steam_client`, `anonymous_login`, 
     `requires_game_purchase`, `supports_linux`, `supports_windows`, `install_script`, `start_command`, 
     `stop_command`, `restart_command`, `query_port`, `game_port`, `rcon_port`, `default_slots`, 
     `min_slots`, `max_slots`, `description`, `notes`, `config_template`, `status`) 
    VALUES 
    (:name, :appid, :engine, :category, :steamcmd_login, :steam_client, :anonymous_login, 
     :requires_game_purchase, :supports_linux, :supports_windows, :install_script, :start_command, 
     :stop_command, :restart_command, :query_port, :game_port, :rcon_port, :default_slots, 
     :min_slots, :max_slots, :description, :notes, :config_template, :status)";

$stmt = $pdo->prepare($insertSql);
$count = 0;

foreach ($games as $g) {
    $appid = (string)($g['appid'] ?? '0');
    $name = $g['name'];
    $engine = $g['engine'] ?? 'Native';
    $category = $g['category'] ?? 'FPS';
    $steamcmdLogin = $g['steamcmd_login'] ?? 'anonymous';
    $steamClient = $g['steam_client'] ?? 0;
    $anonymousLogin = $g['anonymous_login'] ?? 1;
    $requiresPurchase = $g['requires_game_purchase'] ?? 0;
    $linux = $g['supports_linux'] ?? 1;
    $windows = $g['supports_windows'] ?? 0;
    $queryPort = (int)($g['query_port'] ?? 27015);
    $gamePort = (int)($g['game_port'] ?? 27015);
    $rconPort = (int)($g['rcon_port'] ?? 27020);
    $defaultSlots = (int)($g['default_slots'] ?? 16);
    $minSlots = (int)($g['min_slots'] ?? 10);
    $maxSlots = (int)($g['max_slots'] ?? 64);
    $description = $g['description'] ?? '';
    $notes = $g['notes'] ?? '';

    // Try to use existing description from game_types if available
    $key = strtolower(trim($name));
    if (empty($description) && isset($typeDescriptions[$key])) {
        $description = $typeDescriptions[$key];
    }

    // Determine binary name
    $binary = $name;
    if (stripos($name, 'counter-strike') !== false || stripos($name, 'cs') === 0) $binary = 'srcds_run';
    elseif (stripos($name, 'team fortress') !== false) $binary = 'srcds_run';
    elseif (stripos($name, 'left 4 dead') !== false) $binary = 'srcds_run';
    elseif (stripos($name, 'garry') !== false) $binary = 'srcds_run';
    elseif (stripos($name, 'half-life') !== false) $binary = 'srcds_run';
    elseif (stripos($name, 'day of defeat') !== false) $binary = 'srcds_run';
    elseif (stripos($name, 'source sdk') !== false) $binary = 'srcds_run';
    elseif (stripos($name, 'rust') !== false) $binary = 'RustDedicated';
    elseif (stripos($name, '7 days') !== false) $binary = '7DaysToDieServer';
    elseif (stripos($name, 'valheim') !== false) $binary = 'valheim_server';
    elseif (stripos($name, 'v rising') !== false) $binary = 'VRisingServer';
    elseif (stripos($name, 'ark') !== false) $binary = 'ShooterGameServer';
    elseif (stripos($name, 'conan') !== false) $binary = 'ConanSandboxServer';
    elseif (stripos($name, 'dayz') !== false) $binary = 'DayZServer';
    elseif (stripos($name, 'scum') !== false) $binary = 'SCUM_Server';
    elseif (stripos($name, 'minecraft') !== false) $binary = 'java';
    elseif (stripos($name, 'terraria') !== false) $binary = 'TerrariaServer';
    elseif (stripos($name, 'palworld') !== false) $binary = 'PalServer';
    elseif (stripos($name, 'arma') !== false) $binary = 'arma3server';
    elseif (stripos($name, 'squad') !== false) $binary = 'SquadGameServer';
    elseif (stripos($name, 'assetto corsa') !== false && stripos($name, 'competizione') === false) $binary = 'acServer';
    elseif (stripos($name, 'assetto corsa competizione') !== false) $binary = 'accServer';
    elseif (stripos($name, 'farming simulator') !== false) $binary = 'farmingserver';
    elseif (stripos($name, 'insurgency') !== false) $binary = 'InsurgencyServer';
    elseif (stripos($name, 'project zomboid') !== false) $binary = 'ProjectZomboidServer';
    elseif (stripos($name, 'euro truck') !== false) $binary = 'ets2_server';
    elseif (stripos($name, 'american truck') !== false) $binary = 'ats_server';
    elseif (stripos($name, 'unturned') !== false) $binary = 'Unturned';
    else $binary = 'server';

    $install = str_replace('{STEAMCMD_LOGIN}', $steamcmdLogin, $installScript);
    $install = str_replace('{APPID}', $appid, $install);

    $start = str_replace('{SERVER_BINARY}', $binary, $startCmd);
    $stop = str_replace('{SERVER_BINARY}', $binary, $stopCmd);
    $restart = str_replace('{STOP_COMMAND}', 'kill $(cat {INSTALL_DIR}/server.pid 2>/dev/null) 2>/dev/null || pkill -f ' . $binary, $restartCmd);
    $restart = str_replace('{START_COMMAND}', 'cd {INSTALL_DIR} && ./' . $binary . " -port {PORT} +maxplayers {MAX_PLAYERS} +map {MAP} +exec server.cfg", $restart);

    // Per-game config template
    $cfg = $configTemplate;
    $cfg = str_replace('{GAME_NAME}', $name, $cfg);
    if ($engine === 'Unreal') {
        $cfg = "[URL]\nProtocol=unreal\nPort={PORT}\nName={SERVER_NAME}\nMaxPlayers={MAX_PLAYERS}\nAdminPassword={RCON_PASSWORD}\n";
    } elseif ($engine === 'Unity' || $engine === 'Java') {
        $cfg = "server_name={SERVER_NAME}\nserver_password={PASSWORD}\nmax_players={MAX_PLAYERS}\nserver_port={PORT}\nserver_map={MAP}\nrcon_port={RCON_PORT}\nrcon_password={RCON_PASSWORD}\n";
    }

    $stmt->execute([
        ':name' => $name,
        ':appid' => $appid,
        ':engine' => $engine,
        ':category' => $category,
        ':steamcmd_login' => $steamcmdLogin,
        ':steam_client' => $steamClient,
        ':anonymous_login' => $anonymousLogin,
        ':requires_game_purchase' => $requiresPurchase,
        ':supports_linux' => $linux,
        ':supports_windows' => $windows,
        ':install_script' => $install,
        ':start_command' => $start,
        ':stop_command' => $stop,
        ':restart_command' => $restart,
        ':query_port' => $queryPort,
        ':game_port' => $gamePort,
        ':rcon_port' => $rconPort,
        ':default_slots' => $defaultSlots,
        ':min_slots' => $minSlots,
        ':max_slots' => $maxSlots,
        ':description' => $description,
        ':notes' => $notes,
        ':config_template' => $cfg,
        ':status' => 'active',
    ]);
    $count++;
}

echo "game_templates table created and {$count} game templates inserted successfully.\n";
