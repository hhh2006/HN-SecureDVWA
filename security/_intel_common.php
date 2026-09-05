<?php
/* Shared security-intelligence shell, bilingual and defensive. */
function siText($en, $ar) { return dvwaText($en, $ar); }
function siQ($sql) {
    $rows = [];
    try {
        $r = mysqli_query($GLOBALS['___mysqli_ston'], $sql);
        while ($r && ($row = mysqli_fetch_assoc($r))) { $rows[] = $row; }
    } catch (Throwable $e) {}
    return $rows;
}
function siV($sql) {
    try {
        $r = mysqli_query($GLOBALS['___mysqli_ston'], $sql);
        $row = $r ? mysqli_fetch_row($r) : null;
        return $row ? (int)$row[0] : 0;
    } catch (Throwable $e) { return 0; }
}
function siE($value) { return dvwaSecurityEscape($value); }
function siAttackLabel($value) {
    $v=(string)$value;
    $map=[
        'SQL Injection'=>['SQL Injection','حقن قواعد البيانات'],
        'Blind SQL Injection'=>['Blind SQL Injection','حقن قواعد البيانات الأعمى'],
        'Command Injection'=>['Command Injection','حقن الأوامر'],
        'Reflected XSS'=>['Reflected XSS','حقن النصوص المنعكس'],
        'DOM XSS'=>['DOM XSS','حقن النصوص في واجهة الصفحة'],
        'Stored XSS'=>['Stored XSS','حقن النصوص المخزن'],
        'File Upload'=>['File Upload','رفع الملفات'],
        'File Upload normal operation'=>['File Upload normal operation','عملية رفع ملفات طبيعية'],
    ];
    if(isset($map[$v])) return siText($map[$v][0],$map[$v][1]);
    return $v;
}
function siResultLabel($value) {
    return ((string)$value)==='Blocked' ? siText('Blocked','محجوب') : (((string)$value)==='Successful' ? siText('Successful','ناجح') : (string)$value);
}

function siBtn($labelEn, $labelAr, $href, $kind='') {
    $class = 'si-btn'.($kind ? ' '.siE($kind) : '');
    return '<a class="'.$class.'" href="'.siE($href).'">'.siE(siText($labelEn,$labelAr)).'</a>';
}
function siBase($titleEn, $titleAr, $body, $pageId) {
    $body = '<style>@import url("../dvwa/css/hn_cinematic_final.css");</style>'.$body;
    global $page;
    $page = dvwaPageNewGrab();
    $page['title'] = siText($titleEn,$titleAr).$page['title_separator'].$page['title'];
    $page['page_id'] = $pageId;
    $intelNav = [
        ['security/dashboard.php','Dashboard','لوحة المراقبة الأمنية','security_dashboard'],
        ['security/validation.php','Validation','التحقق الأمني','security_validation'],
        ['security/health.php','Health','صحة الأمن','security_health'],
        ['security/notifications.php','Alerts','التنبيهات','security_alerts'],
        ['security/timeline.php','Timeline','الخط الزمني','security_timeline'],
        ['security/investigation.php','Investigation','التحقيق','security_investigation'],
        ['security/risk.php','Risk','المخاطر','security_risk'],
        ['security/report.php','Report','التقرير','security_report'],
    ];
    $intelNavHtml='<nav class="si-intel-nav" aria-label="'.siE(siText('Security intelligence','استخبارات الأمن')).'">';
    foreach($intelNav as $n){ $active=$pageId===$n[3]?' active':''; $intelNavHtml.='<a class="si-intel-nav-link'.$active.'" href="../'.siE($n[0]).'"><span>'.siE(siText($n[1],$n[2])).'</span><small>'.siE($n[3]).'</small></a>'; }
    $intelNavHtml.='</nav>';
    $page['body'] = '<main class="si-page" data-intel-page="'.siE($pageId).'">'.$intelNavHtml.$body.'</main>';
    $page['body'] .= '<script src="../dvwa/js/hn_security_intel.js"></script>';
    dvwaHtmlEcho($page);
}
function siShell($eyebrowEn,$eyebrowAr,$titleEn,$titleAr,$descEn,$descAr,$accent='cyan') {
    return '<section class="si-hero si-accent-'.$accent.'">'
        .'<div class="si-hero-noise"></div><div class="si-hero-grid"></div><div class="si-hero-orbit si-orbit-a"></div><div class="si-hero-orbit si-orbit-b"></div>'
        .'<div class="si-hero-scan"></div><div class="si-hero-copy">'
        .'<span class="si-kicker">'.siE(siText($eyebrowEn,$eyebrowAr)).'</span>'
        .'<h1>'.siE(siText($titleEn,$titleAr)).'</h1>'
        .'<p>'.siE(siText($descEn,$descAr)).'</p></div>'
        .'<div class="si-hero-status"><i></i><span>'.siE(siText('LOCAL LAB / LIVE DATA','مختبر محلي / بيانات حية')).'</span></div>'
        .'</section>';
}
function siHeader($labelEn,$labelAr,$metaEn='LIVE',$metaAr='مباشر') {
    return '<header class="si-section-header"><div><span>'.siE(siText($labelEn,$labelAr)).'</span></div><b>'.siE(siText($metaEn,$metaAr)).'</b></header>';
}
function siEmpty($en,$ar) {
    return '<div class="si-empty"><span class="si-empty-orb"></span><strong>'.siE(siText($en,$ar)).'</strong></div>';
}
