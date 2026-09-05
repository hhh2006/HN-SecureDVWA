<?php

define( 'DVWA_WEB_PAGE_TO_ROOT', '../' );
require_once DVWA_WEB_PAGE_TO_ROOT . 'dvwa/includes/dvwaPage.inc.php';

dvwaPageStartup( array( 'authenticated' ) );

$page = dvwaPageNewGrab();
$page[ 'title' ] = dvwaText('Help','المساعدة') . $page[ 'title_separator' ] . $page[ 'title' ];

$id = isset($_GET['id']) ? (string)$_GET['id'] : '';
$security = isset($_GET['security']) ? (string)$_GET['security'] : '';
$locale = isset($_GET['locale']) ? (string)$_GET['locale'] : dvwaLocaleGet();

$help = '';
if ($id !== '' && $security !== '') {
    $helpFile = ($locale === 'en')
        ? DVWA_WEB_PAGE_TO_ROOT . "vulnerabilities/{$id}/help/help.php"
        : DVWA_WEB_PAGE_TO_ROOT . "vulnerabilities/{$id}/help/help.{$locale}.php";
    if (file_exists($helpFile)) {
        ob_start();
        eval( '?>' . file_get_contents($helpFile) . '<?php ' );
        $help = ob_get_contents();
        ob_end_clean();
    } else {
        $help = '<p class="locale-note">' . dvwaText('Arabic documentation is not available for this module yet. English documentation is shown below.','لا توجد ترجمة عربية لهذا القسم بعد؛ لذلك سيتم عرض التوثيق الإنجليزي.') . '</p>';
        $englishFile = DVWA_WEB_PAGE_TO_ROOT . "vulnerabilities/{$id}/help/help.php";
        if (file_exists($englishFile)) {
            ob_start();
            eval( '?>' . file_get_contents($englishFile) . '<?php ' );
            $help .= ob_get_contents();
            ob_end_clean();
        }
    }
} else {
    $help = '<p>' . dvwaText('Not Found','غير موجود') . '</p>';
}

$page[ 'body' ] .= "
<div class=\"body_padded\">
    <section class=\"help-shell\">
        <div class=\"help-kicker\">HN / " . dvwaText('SECURITY KNOWLEDGE BASE','قاعدة المعرفة الأمنية') . "</div>
        <div class=\"console-toolbar\">
            <span class=\"console-path\">{$id} / {$security}</span>
            <span class=\"console-badge\">" . dvwaText('DOCUMENTATION','التوثيق') . "</span>
        </div>
        {$help}
    </section>
</div>\n";

dvwaHelpHtmlEcho( $page );

?>