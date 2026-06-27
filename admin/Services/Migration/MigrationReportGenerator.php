<?php

namespace Admin\Services\Migration;

class MigrationReportGenerator
{
    protected $db;

    public function __construct()
    {
        $app = \Core\Application::getInstance();
        $this->db = $app->get('db');
    }

    public function generateHtml(array $job, array $analysis, array $preflight, array $compat, array $conversion, array $validation): string
    {
        $reportTitle = 'Migration Report - Job #' . $job['id'];
        $generatedAt = date('Y-m-d H:i:s');

        $summaryRows = '';
        $rows = [
            ['Source Panel', $job['source_type']],
            ['Source Host', $job['source_host']],
            ['Migration Type', $job['migration_type']],
            ['Transport', $job['source_transport']],
            ['Status', $job['status']],
            ['Items Migrated', $job['items_migrated'] . ' / ' . $job['total_items']],
            ['Started', $job['created_at']],
            ['Completed', $job['completed_at'] ?? 'N/A'],
        ];
        foreach ($rows as $r) {
            $summaryRows .= "<tr><td style='padding:6px 10px;border:1px solid #ddd;color:#555'>{$r[0]}</td><td style='padding:6px 10px;border:1px solid #ddd;font-weight:600'>{$r[1]}</td></tr>";
        }

        $analysisHtml = '';
        if (!empty($analysis['summary'])) {
            $s = $analysis['summary'];
            $analysisHtml = '<h3 style="color:#2563eb;margin-top:20px">Analysis Summary</h3>';
            $analysisHtml .= '<table style="width:100%;border-collapse:collapse;font-size:13px">';
            $analysisHtml .= "<tr><td style='padding:4px 8px'>Accounts</td><td style='padding:4px 8px;font-weight:600'>{$s['total_accounts']}</td>";
            $analysisHtml .= "<td style='padding:4px 8px'>Databases</td><td style='padding:4px 8px;font-weight:600'>{$s['total_databases']}</td></tr>";
            $analysisHtml .= "<tr><td style='padding:4px 8px'>Domains</td><td style='padding:4px 8px;font-weight:600'>{$s['total_domains']}</td>";
            $analysisHtml .= "<td style='padding:4px 8px'>Email Accounts</td><td style='padding:4px 8px;font-weight:600'>{$s['total_email_accounts']}</td></tr>";
            $analysisHtml .= "<tr><td style='padding:4px 8px'>Disk Used</td><td style='padding:4px 8px;font-weight:600'>{$s['total_disk_used_gb']} GB</td>";
            $analysisHtml .= "<td style='padding:4px 8px'>Est. Time</td><td style='padding:4px 8px;font-weight:600'>{$analysis['estimated_time_human']}</td></tr>";
            $analysisHtml .= '</table>';
        }

        $issuesHtml = '';
        if (!empty($analysis['potential_issues'])) {
            $issuesHtml = '<h3 style="color:#2563eb;margin-top:20px">Potential Issues</h3>';
            foreach ($analysis['potential_issues'] as $issue) {
                $color = $issue['severity'] === 'warning' ? '#f59e0b' : '#3b82f6';
                $issuesHtml .= "<div style='padding:8px 12px;margin-bottom:6px;border-left:3px solid {$color};background:#f9fafb;border-radius:4px'>";
                $issuesHtml .= "<strong>{$issue['icon']} {$issue['title']}</strong><br>";
                $issuesHtml .= "<span style='color:#666;font-size:12px'>{$issue['detail']}</span></div>";
            }
        }

        $validationHtml = '';
        if (!empty($validation['results'])) {
            $validationHtml = '<h3 style="color:#2563eb;margin-top:20px">Verification Results</h3>';
            $validationHtml .= '<table style="width:100%;border-collapse:collapse;font-size:13px">';
            $validationHtml .= '<tr style="background:#f1f5f9"><th style="padding:8px;border:1px solid #ddd;text-align:left">Check</th><th style="padding:8px;border:1px solid #ddd;text-align:center">Status</th><th style="padding:8px;border:1px solid #ddd;text-align:center">Passed</th></tr>';
            foreach ($validation['results'] as $type => $result) {
                $statusColor = $result['passed'] ? '#16a34a' : '#dc2626';
                $statusIcon = $result['passed'] ? '✓' : '✗';
                $validationHtml .= "<tr><td style='padding:6px 10px;border:1px solid #ddd;text-transform:capitalize'>{$type}</td>";
                $validationHtml .= "<td style='padding:6px 10px;border:1px solid #ddd;text-align:center;color:{$statusColor}'>{$statusIcon}</td>";
                $validationHtml .= "<td style='padding:6px 10px;border:1px solid #ddd;text-align:center'>{$result['passed_count']}/{$result['total']}</td></tr>";
            }
            $validationHtml .= '</table>';
        }

        $recos = '';
        if (!empty($analysis['recommendations'])) {
            $recos = '<h3 style="color:#2563eb;margin-top:20px">Recommendations</h3><ul>';
            foreach ($analysis['recommendations'] as $r) {
                $recos .= "<li style='padding:4px 0;font-size:13px'>{$r['text']}</li>";
            }
            $recos .= '</ul>';
        }

        $html = <<<HTML
<!DOCTYPE html>
<html><head><meta charset="utf-8"><title>{$reportTitle}</title></head>
<body style="font-family:Arial,sans-serif;max-width:800px;margin:0 auto;padding:20px;color:#333">
<h1 style="color:#2563eb;border-bottom:2px solid #2563eb;padding-bottom:8px">{$reportTitle}</h1>
<p style="color:#666;font-size:13px">Generated: {$generatedAt}</p>
<h3 style="color:#2563eb;margin-top:20px">Job Summary</h3>
<table style="width:100%;border-collapse:collapse;font-size:13px">{$summaryRows}</table>
{$analysisHtml}
{$issuesHtml}
{$validationHtml}
{$recos}
<p style="margin-top:30px;padding-top:10px;border-top:1px solid #ddd;color:#999;font-size:11px">Planet Hosts Migration & Restore Center</p>
</body></html>
HTML;

        return $html;
    }

    public function generatePdf(array $job, array $analysis, array $preflight, array $compat, array $conversion, array $validation): string
    {
        $html = $this->generateHtml($job, $analysis, $preflight, $compat, $conversion, $validation);
        $filename = "migration_report_{$job['id']}_" . date('Ymd_His') . ".html";
        $dir = sys_get_temp_dir() . '/planet_hosts_reports';
        if (!is_dir($dir)) @mkdir($dir, 0755, true);
        $path = $dir . '/' . $filename;
        file_put_contents($path, $html);

        $pdfPath = str_replace('.html', '.pdf', $path);
        exec("wkhtmltopdf " . escapeshellarg($path) . " " . escapeshellarg($pdfPath) . " 2>/dev/null", $out, $code);
        if ($code === 0 && file_exists($pdfPath)) {
            return $pdfPath;
        }
        return $path;
    }

    public function streamReport(array $job, array $analysis, array $preflight, array $compat, array $conversion, array $validation)
    {
        $html = $this->generateHtml($job, $analysis, $preflight, $compat, $conversion, $validation);
        header('Content-Type: text/html; charset=utf-8');
        echo $html;
        exit;
    }
}
