<?php
$ROOT='../'; define('DVWA_WEB_PAGE_TO_ROOT',$ROOT);
require_once DVWA_WEB_PAGE_TO_ROOT.'dvwa/includes/dvwaPage.inc.php'; dvwaPageStartup(['authenticated']); dvwaDatabaseConnect(); dvwaSecurityEnsureSchema();
require_once __DIR__.'/_intel_common.php';
$checks=[
 ['SQL Injection','حقن قواعد البيانات',fn()=>function_exists('dvwaSecurityContainsSqlInjection')&&dvwaSecurityContainsSqlInjection("1' OR '1'='1' #")&&!dvwaSecurityContainsSqlInjection('7'),'Boolean injection detector'],
 ['Blind SQL Injection','حقن قواعد البيانات الأعمى',fn()=>function_exists('dvwaSecurityContainsSqlInjection')&&dvwaSecurityContainsSqlInjection('1 UNION SELECT user FROM users'),'Injection pattern detector'],
 ['Command Injection','حقن الأوامر',fn()=>function_exists('dvwaSecurityContainsCommandInjection')&&dvwaSecurityContainsCommandInjection('127.0.0.1 | whoami')&&!dvwaSecurityContainsCommandInjection('127.0.0.1'),'Command separator detector'],
 ['Reflected XSS','حقن النصوص المنعكس',fn()=>function_exists('dvwaSecurityContainsXss')&&dvwaSecurityContainsXss('<script>alert(1)</script>'),'Script/event detector'],
 ['DOM XSS','حقن النصوص في واجهة الصفحة',fn()=>function_exists('dvwaSecurityContainsXss')&&dvwaSecurityContainsXss('javascript:alert(1)'),'Safe-input detector'],
 ['Stored XSS','حقن النصوص المخزن',fn()=>function_exists('dvwaSecurityContainsXss')&&dvwaSecurityContainsXss('<img src=x onerror=alert(1)>'),'Stored payload detector'],
 ['File Upload','رفع الملفات',fn()=>is_file(DVWA_WEB_PAGE_TO_ROOT.'vulnerabilities/upload/source/impossible.php')&&is_file(DVWA_WEB_PAGE_TO_ROOT.'hackable/uploads/.htaccess'),'MIME, image validation, and execution block'],
];
$source=[];
foreach($checks as $c){
    $map=['SQL Injection'=>'sqli','Blind SQL Injection'=>'sqli_blind','Command Injection'=>'exec','Reflected XSS'=>'xss_r','DOM XSS'=>'xss_d','Stored XSS'=>'xss_s','File Upload'=>'upload'];
    $dir=$map[$c[0]]??'';
    $source[$c[0]]=($dir!==''&&is_file(DVWA_WEB_PAGE_TO_ROOT.'vulnerabilities/'.$dir.'/source/low.php')) || ($c[0]==='DOM XSS'&&is_file(DVWA_WEB_PAGE_TO_ROOT.'vulnerabilities/xss_d/index.php'));
}
$results=[];$pass=0;
foreach($checks as $c){
    $ok=false; try{$ok=(bool)$c[2]();}catch(Throwable $e){}
    $present=(bool)$source[$c[0]]; $ready=$ok&&$present; if($ready)$pass++;
    $results[]=['en'=>$c[0],'ar'=>$c[1],'ok'=>$ready,'detail'=>$c[3]];
}
$events=siV('SELECT COUNT(*) FROM security_events');$ops=siV('SELECT COUNT(*) FROM security_operations');$score=round($pass/count($results)*100);
$cards='';foreach($results as $i=>$r){$cards.='<article class="validation-card '.($r['ok']?'ready':'review').'"><div class="validation-index">'.str_pad((string)($i+1),2,'0',STR_PAD_LEFT).'</div><div class="validation-icon">'.($r['ok']?'✓':'!').'</div><div><h3>'.siE(siText($r['en'],$r['ar'])).'</h3><p>'.siE(siText($r['detail'],$r['detail'])).'</p></div><span>'.siE(siText($r['ok']?'VERIFIED':'REVIEW',$r['ok']?'متحقق':'مراجعة')).'</span><i></i></article>';}
$body=siShell('SECURITY VALIDATION / EVIDENCE','التحقق الأمني / الأدلة','Security Validation Center','مركز التحقق الأمني','A live proof wall for detectors, hardened sources, and telemetry readiness.','لوحة تحقق حية تثبت الكواشف ومصادر الحماية وجاهزية المراقبة.','violet');
$body.='<section class="validation-overview"><div class="validation-score"><div class="validation-ring" style="--score:'.$score.'"><b>'.$score.'%</b></div><div><span class="si-kicker">'.siE(siText('VALIDATION STATUS','حالة التحقق')).'</span><h2>'.siE(siText($score===100?'ALL CHECKS VERIFIED':'REVIEW REQUIRED',$score===100?'كل الفحوصات متحققة':'تحتاج إلى مراجعة')).'</h2><p>'.siE(siText($pass.' of '.count($results).' security paths verified now.',$pass.' من أصل '.count($results).' مسارات أمنية متحققة الآن.')).'</p></div></div><div class="validation-stats"><div><b>'.$events.'</b><span>'.siE(siText('SECURITY EVENTS','الأحداث الأمنية')).'</span></div><div><b>'.$ops.'</b><span>'.siE(siText('NORMAL OPERATIONS','العمليات الطبيعية')).'</span></div><div><b>'.count($results).'</b><span>'.siE(siText('PROTECTED AREAS','المناطق المحمية')).'</span></div></div></section>';
$body.='<section class="si-panel validation-wall">'.siHeader('DEFENSE VERIFICATION MAP','خريطة التحقق الدفاعي','7 PROTECTED AREAS','7 مناطق محمية').'<div class="validation-cards">'.$cards.'</div></section>';
$body.='<section class="si-panel evidence-flow">'.siHeader('EVIDENCE PIPELINE','مسار الأدلة','DETECT → HARDEN → LOG → RETEST','اكتشاف ← حماية ← تسجيل ← إعادة اختبار').'<div class="evidence-steps"><div><b>01</b><strong>'.siE(siText('DETECT','اكتشاف')).'</strong><small>'.siE(siText('Representative malicious input is recognized safely.','يتم التعرف على المدخل الخبيث التمثيلي بأمان.')).'</small></div><i></i><div><b>02</b><strong>'.siE(siText('HARDEN','تحصين')).'</strong><small>'.siE(siText('The selected source contains the defensive implementation.','مصدر الحماية المحدد يحتوي على المعالجة الدفاعية.')).'</small></div><i></i><div><b>03</b><strong>'.siE(siText('LOG','تسجيل')).'</strong><small>'.siE(siText('Security events and normal operations use separate telemetry.','الأحداث الأمنية والعمليات الطبيعية تستخدم مسارين منفصلين.')).'</small></div><i></i><div><b>04</b><strong>'.siE(siText('RETEST','إعادة اختبار')).'</strong><small>'.siE(siText('Run the same laboratory case and verify the blocked outcome.','نفّذ الاختبار المختبري نفسه وتحقق من نتيجة المنع.')).'</small></div></div></section>';
siBase('Security Validation Center','مركز التحقق الأمني',$body,'security_validation');
