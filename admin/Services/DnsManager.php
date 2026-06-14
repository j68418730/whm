<?php

namespace Admin\Services;

class DnsManager
{
    protected $db;

    public function __construct()
    {
        $app = \Core\Application::getInstance();
        $this->db = $app->get('db');
    }

    public function getZones()
    {
        return $this->db->table('dns_zones')->get() ?: [];
    }

    public function getZone($id)
    {
        return $this->db->table('dns_zones')->where('id', $id)->first();
    }

    public function createZone($domain, $ns1 = null, $ns2 = null, $adminEmail = null)
    {
        $id = $this->db->table('dns_zones')->insertGetId([
            'domain' => $domain,
            'ns1' => $ns1 ?: "ns1.{$domain}",
            'ns2' => $ns2 ?: "ns2.{$domain}",
            'admin_email' => $adminEmail ?: "admin@{$domain}",
            'serial' => date('Ymd') . '01',
            'refresh' => 3600,
            'retry' => 1800,
            'expire' => 86400,
            'ttl' => 300,
        ]);
        // Add default SOA and NS records
        $this->addRecord($id, '@', 'SOA', "{$domain}. admin.{$domain}. {$id}01 3600 1800 86400 300", 300);
        $this->addRecord($id, '@', 'NS', $ns1 ?: "ns1.{$domain}", 300);
        $this->addRecord($id, '@', 'NS', $ns2 ?: "ns2.{$domain}", 300);
        return $id;
    }

    public function deleteZone($id)
    {
        $this->db->table('dns_records')->where('zone_id', $id)->delete();
        $this->db->table('dns_zones')->where('id', $id)->delete();
    }

    public function getRecords($zoneId)
    {
        return $this->db->table('dns_records')->where('zone_id', $zoneId)->get() ?: [];
    }

    public function addRecord($zoneId, $name, $type, $value, $ttl = 300, $priority = null)
    {
        return $this->db->table('dns_records')->insertGetId([
            'zone_id' => $zoneId,
            'name' => $name,
            'type' => strtoupper($type),
            'value' => $value,
            'ttl' => $ttl,
            'priority' => $priority,
        ]);
    }

    public function updateRecord($id, $data)
    {
        $this->db->table('dns_records')->where('id', $id)->update($data);
    }

    public function deleteRecord($id)
    {
        $this->db->table('dns_records')->where('id', $id)->delete();
    }

    public function getNameservers()
    {
        return $this->db->table('dns_nameservers')->get() ?: [];
    }

    public function setNameservers($ns1, $ns2, $ns3 = null, $ns4 = null)
    {
        $this->db->table('dns_nameservers')->where('1', 1)->delete();
        foreach ([$ns1, $ns2, $ns3, $ns4] as $ns) {
            if ($ns) $this->db->table('dns_nameservers')->insertGetId(['nameserver' => $ns]);
        }
    }
}
