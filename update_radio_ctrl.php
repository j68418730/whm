<?php
$c = file_get_contents('/var/www/radiohosting/user/Controllers/RadioController.php');
if (strpos($c, 'station_ids') !== false) { echo "Already updated\n"; exit; }

$new = <<<'NEWCODE'
    public function createDj()
    {
        if (!$this->auth->check()) exit;
        $station = $this->getStation();
        if (!$station) { header("Location: /user/radio"); exit; }
        $username = strtolower(preg_replace("/[^a-z0-9]/", "", $_POST["username"] ?? ""));
        $password = $_POST["password"] ?? "";
        $name = $_POST["name"] ?? $username;
        $role = $_POST["role"] ?? "dj";
        if ($username && $password) {
            try {
                $realStationId = $station->streaming_id ?? $station->id;
                $djId = $this->db->table("radio_djs")->insertGetId([
                    "stream_id" => $realStationId, "username" => $username,
                    "password" => password_hash($password, PASSWORD_DEFAULT),
                    "name" => $name, "email" => $_POST["email"] ?? "",
                    "status" => "active",
                ]);

                $stationIds = $_POST["station_ids"] ?? [];
                if (!empty($stationIds)) {
                    foreach ($stationIds as $stationId) {
                        $sid = (int)$stationId;
                        if ($sid > 0 && $this->canManageStation($sid)) {
                            try {
                                $this->db->table("radio_dj_streams")->insert([
                                    "dj_id" => $djId,
                                    "stream_id" => $sid,
                                    "assigned_by" => $this->auth->user()->id,
                                ]);
                            } catch (Exception $e) {}
                        }
                    }
                } else {
                    $stationId = (int)$_POST["station_id"];
                    if ($stationId && $this->canManageStation($stationId)) {
                        try {
                            $this->db->table("radio_dj_streams")->insert([
                                "dj_id" => $djId,
                                "stream_id" => $stationId,
                                "assigned_by" => $this->auth->user()->id,
                            ]);
                        } catch (Exception $e) {}
                    }
                }

                try {
                    $ss = $this->db->table("streaming_stations")->where("id", $realStationId)->first();
                    if ($ss) {
                        $tenant = $this->db->table("chatbox_tenants")->where("hosting_user_id", $ss->user_id)->first();
                        if ($tenant) {
                            $existing = $this->db->table("chatbox_users")->where("tenant_id", $tenant->id)->where("username", $username)->first();
                            if (!$existing) {
                                $this->db->table("chatbox_users")->insertGetId([
                                    "tenant_id" => $tenant->id, "username" => $username,
                                    "password_hash" => password_hash($password, PASSWORD_DEFAULT),
                                    "display_name" => $name, "role" => $role === "mod" ? "mod" : "member",
                                    "email" => $_POST["email"] ?? "", "created_at" => date("Y-m-d H:i:s"),
                                ]);
                            }
                        }
                    }
                } catch (Exception $e) {}
                try {
                    $ss = $this->db->table("streaming_stations")->where("id", $realStationId)->first();
                    if ($ss) {
                        $hu = $this->db->table("hosting_users")->where("id", $ss->user_id)->first();
                        if ($hu) {
                            $djDir = "/home/{$hu->username}/radio/dj/{$username}";
                            @mkdir($djDir, 0755, true);
                            @mkdir($djDir . "/gallery", 0755, true);
                            @chmod($djDir, 0755);
                        }
                    }
                } catch (Exception $e) {}
                $_SESSION["success"] = "DJ '" . $name . "' created.";
            } catch (Exception $e) { $_SESSION["error"] = "Username already exists."; }
        }
        header("Location: /user/radio?tab=djs&station_id=" . $station->id); exit;
    }
NEWCODE;

$c = file_get_contents('/var/www/radiohosting/user/Controllers/RadioController.php');
if (strpos($c, 'station_ids') !== false) { echo "Already updated\n"; exit; }
$pos = strpos($c, 'public function createDj()');
$end = strpos($c, 'public function ', $pos + 20);
if ($end === false) $end = strlen($c);
$c = substr($c, 0, $pos) . $new . substr($c, $end);
file_put_contents('/var/www/radiohosting/user/Controllers/RadioController.php', $c);
echo "Updated\n";
