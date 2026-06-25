<?php

namespace Admin\Services;

class DnsManager
{
    protected $db;
    protected $ns1 = 'ns1.planet-hosts.com';
    protected $ns2 = 'ns2.planet-hosts.com';
    protected $serverIp = 'planet-hosts.com';

    public function __construct()
    {
        $app = \Core\Application::getInstance();
        $this->db = $app->get('db');
        $ns = $this->db->table('dns_nameservers')->get();
        if ($ns && count($ns) >= 2) {
            $this->ns1 = $ns[0]->nameserver;
            $this->ns2 = $ns[1]->nameserver;
        }
    }

    public function getZones() { return $this->db->table('dns_zones')->get() ?: []; }
    public function getZone($id) { return $this->db->table('dns_zones')->where('id', $id)->first(); }
    public function getRecords($zoneId) { return $this->db->table('dns_records')->where('zone_id', $zoneId)->get() ?: []; }
    public function getNameservers() { return $this->db->table('dns_nameservers')->get() ?: []; }

    public function addRecord($zoneId, $name, $type, $value, $ttl = 14400, $priority = null)
    {
        return $this->db->table('dns_records')->insertGetId([
            'zone_id' => $zoneId, 'name' => $name, 'type' => strtoupper($type),
            'value' => $value, 'ttl' => $ttl, 'priority' => $priority,
        ]);
    }

    public function updateRecord($id, $data) { $this->db->table('dns_records')->where('id', $id)->update($data); }
    public function deleteRecord($id) { $this->db->table('dns_records')->where('id', $id)->delete(); }

    public function deleteZone($id)
    {
        $this->db->table('dns_records')->where('zone_id', $id)->delete();
        $this->db->table('dns_zones')->where('id', $id)->delete();
    }

    public function createZone($domain, $ns1 = null, $ns2 = null, $adminEmail = null)
    {
        $zoneId = $this->db->table('dns_zones')->insertGetId([
            'domain' => $domain, 'ns1' => $ns1 ?: $this->ns1, 'ns2' => $ns2 ?: $this->ns2,
            'admin_email' => $adminEmail ?: "admin@{$domain}",
            'serial' => date('Ymd') . '01', 'refresh' => 3600, 'retry' => 1800,
            'expire' => 86400, 'ttl' => 14400,
        ]);
        return $zoneId;
    }

    public function provisionDomain($domain, $serverIp = null, $adminEmail = null)
    {
        $ip = $serverIp ?: $this->serverIp;
        $email = $adminEmail ?: "admin@{$domain}";
        $ns1 = $this->ns1;
        $ns2 = $this->ns2;

        // 1. Create zone
        $zoneId = $this->createZone($domain, $ns1, $ns2, $email);
        $zone = $this->getZone($zoneId);
        $serial = $zone->serial;

        // 2. SOA Record
        $this->addRecord($zoneId, '@', 'SOA', "{$ns1} {$email} {$serial} 3600 1800 86400 14400", 14400);

        // 3. NS Records
        $this->addRecord($zoneId, '@', 'NS', $ns1, 14400);
        $this->addRecord($zoneId, '@', 'NS', $ns2, 14400);

        // 4. A Records
        $this->addRecord($zoneId, '@', 'A', $ip, 14400);
        $this->addRecord($zoneId, 'www', 'A', $ip, 14400);

        // 5. Mail Records
        $this->addRecord($zoneId, 'mail', 'A', $ip, 14400);
        $this->addRecord($zoneId, '@', 'MX', 'mail.' . $domain, 14400, 10);

        // 6. SPF Record
        $this->addRecord($zoneId, '@', 'TXT', "v=spf1 a mx ip4:{$ip} ~all", 14400);

        // 7. DKIM Record (placeholder - generate on email setup)
        $this->addRecord($zoneId, 'default._domainkey', 'TXT', "v=DKIM1; h=sha256; p=", 14400);

        // 8. DMARC Record
        $this->addRecord($zoneId, '_dmarc', 'TXT', "v=DMARC1; p=none; rua=mailto:dmarc@{$domain}", 14400);

        // 9. Reload DNS
        $this->reloadDns();

        return $zoneId;
    }

    public function reloadDns()
    {
        $bind = trim(shell_exec('systemctl is-active bind9 2>/dev/null') ?: '');
        if ($bind === 'active') {
            shell_exec('rndc reload 2>/dev/null || systemctl reload bind9 2>/dev/null || true');
        }
    }

    public function setNameservers($ns1, $ns2, $ns3 = null, $ns4 = null)
    {
        $this->db->table('dns_nameservers')->where('1', 1)->delete();
        foreach ([$ns1, $ns2, $ns3, $ns4] as $ns) {
            if ($ns) $this->db->table('dns_nameservers')->insertGetId(['nameserver' => $ns]);
        }
    }
}

