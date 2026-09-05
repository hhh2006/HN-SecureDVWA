<?php
$root=dirname(__DIR__);$errors=[];
function check($cond,$msg){global $errors;if(!$cond)$errors[]=$msg;}
$dash=file_get_contents($root.'/security/dashboard.php');$page=file_get_contents($root.'/dvwa/includes/dvwaPage.inc.php');$sec=file_get_contents($root.'/dvwa/includes/security.php');$css=file_get_contents($root.'/dvwa/css/cinematic.css');
check(strpos($dash,'security_أحداث')===false,'Dashboard contains a corrupted Arabic table identifier.');
check(strpos($dash,'security_events')!==false,'Dashboard must use security_events.');
check(strpos($dash,"result='Blocked'")!==false,'Dashboard must use internal result Blocked.');
check(strpos($dash,"result='Successful'")!==false,'Dashboard must use internal result Successful.');
check(strpos($page,'cinematic.css')!==false,'Global cinematic stylesheet is not loaded.');
check(strpos($page,'class=\\"home hud-interface\\"')!==false,'Main interface must use the fixed HUD theme.');
check(strpos($page,'dvwaLocaleUrl(\'ar\')')!==false,'Arabic language switch must remain available.');
check(strpos($css,'backdrop-filter')!==false,'Glass UI layer is missing.');
check(strpos($css,'@keyframes scan')!==false,'HUD scan animation is missing.');
check(strpos($css,'grid-template-columns: var(--sidebar) minmax(0, 1fr) !important')!==false,'Main canvas must use the unified full-width grid.');
check(strpos($css,'.source-shell')!==false && strpos($css,'.help-shell')!==false,'Source/Help console design system is missing.');
check(strpos($css,'@media (prefers-reduced-motion: reduce)')!==false,'Reduced-motion accessibility rule is missing.');

check(strpos($css,'#main_body')!==false && strpos($css,'min-width: 0 !important')!==false,'Main canvas must use a fluid body area without fixed-width overflow.');
check(strpos($css,'.sd .grid { display: grid; grid-template-columns: minmax(280px, .82fr) minmax(0, 1.45fr)')!==false,'Security dashboard must use a horizontal analytics layout on desktop.');
check(strpos($css,'.sd .table-wrap { max-height: clamp(280px, 47vh, 590px)')!==false,'Security log must use an internal scroll region to avoid excessive page height.');
check(strpos($sec,'CREATE TABLE IF NOT EXISTS security_events')!==false,'Security schema missing.');
check(is_file($root.'/dvwa/js/hn_locale.js'),'Compatibility localization script missing.');
check(is_file($root.'/dvwa/css/cinematic.css'),'Cinematic stylesheet missing.');
if($errors){fwrite(STDERR,implode(PHP_EOL,$errors).PHP_EOL);exit(1);}echo "FINAL RELEASE CHECK: PASS\n";
?>
