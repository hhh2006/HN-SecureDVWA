<?php
// Client-side telemetry bridge for DOM-only lab signals (e.g. URL fragments).
define('DVWA_WEB_PAGE_TO_ROOT','../');
require_once DVWA_WEB_PAGE_TO_ROOT.'dvwa/includes/dvwaPage.inc.php';
dvwaPageStartup(array('authenticated'));
dvwaDatabaseConnect();
dvwaSecurityEnsureSchema();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok'=>false,'error'=>'POST required']);
    exit;
}

$event = isset($_POST['event']) ? (string)$_POST['event'] : '';
if ($event !== 'dom_hash_payload') {
    http_response_code(400);
    echo json_encode(['ok'=>false,'error'=>'Unsupported telemetry event']);
    exit;
}

// Keep this endpoint deliberately narrow: it accepts only a fixed DOM XSS signal
// and never stores the actual fragment/payload.
$ok = dvwaSecurityEvent(
    'DOM XSS',
    'vulnerabilities/xss_d',
    'Blocked',
    'Client-side DOM payload pattern detected in URL fragment'
);

echo json_encode(['ok'=>(bool)$ok], JSON_UNESCAPED_UNICODE);
