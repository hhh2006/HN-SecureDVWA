<?php
define('DVWA_WEB_PAGE_TO_ROOT','../');
require_once DVWA_WEB_PAGE_TO_ROOT.'dvwa/includes/dvwaPage.inc.php';
dvwaPageStartup(array('authenticated')); dvwaDatabaseConnect(); dvwaSecurityEnsureSchema();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('X-Content-Type-Options: nosniff');
function apiE($v){return dvwaSecurityEscape($v);}
$since=isset($_GET['since'])?max(0,(int)$_GET['since']):0;
$rows=[];
try{
 $sql=$since>0 ? "SELECT id,event_time,attack_type,target,result,reason FROM security_events WHERE id > {$since} ORDER BY id ASC LIMIT 20" : "SELECT id,event_time,attack_type,target,result,reason FROM security_events ORDER BY id DESC LIMIT 20";
 $r=mysqli_query($GLOBALS['___mysqli_ston'],$sql); while($r&&($x=mysqli_fetch_assoc($r))){$rows[]=['id'=>(int)$x['id'],'time'=>$x['event_time'],'attack_type'=>$x['attack_type'],'target'=>$x['target'],'result'=>$x['result'],'reason'=>$x['reason']??''];}
}catch(Throwable $e){}
$latest=0; foreach($rows as $x)$latest=max($latest,$x['id']);
echo json_encode(['ok'=>true,'latest_id'=>$latest,'events'=>$rows],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
