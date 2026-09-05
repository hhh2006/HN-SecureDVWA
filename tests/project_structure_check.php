<?php
$root = realpath(__DIR__ . '/..');
$required = [
    'Security dashboard' => 'security/dashboard.php',
    'Security telemetry helper' => 'dvwa/includes/security.php',
    'Regression suite' => 'tests/security_regression.php',
    'Security CI' => '.github/workflows/security.yml',
    'Threat model' => 'docs/THREAT-MODEL.md',
    'Security controls' => 'docs/SECURITY-CONTROLS.md',
    'Security test matrix' => 'docs/SECURITY-TEST-MATRIX.md',
    'Demo runbook' => 'docs/DEMO-RUNBOOK.md',
    'Final compliance checklist' => 'docs/FINAL-PROJECT-COMPLIANCE.md',
];
$failed=[];
foreach($required as $name=>$rel){
  if(!is_file($root.DIRECTORY_SEPARATOR.$rel)) $failed[]=$name.' -> '.$rel;
  else echo "PASS | {$name}\n";
}
// Bonus-feature implementation checks.
$dash=file_get_contents($root.'/security/dashboard.php');
test('Security Score', strpos($dash,'$blockedRate')!==false && strpos($dash,'BLOCKED')!==false);
test('Charts / visual statistics', strpos($dash,'threat-bars')!==false && strpos($dash,'composition-list')!==false && strpos($dash,'24H')!==false);
test('Recent-event alert', strpos($dash,'$recent')!==false && strpos($dash,'event-stream')!==false);
function test($name,$ok){global $failed; if($ok) echo "PASS | {$name}\n"; else $failed[]=$name;}
if($failed){echo "FAILURES:\n"; foreach($failed as $f) echo " - {$f}\n"; exit(1);} echo "PROJECT STRUCTURE: PASS\n";
