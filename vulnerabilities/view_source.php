<?php

define( 'DVWA_WEB_PAGE_TO_ROOT', '../' );
require_once DVWA_WEB_PAGE_TO_ROOT . 'dvwa/includes/dvwaPage.inc.php';

dvwaPageStartup( array( 'authenticated' ) );

$page = dvwaPageNewGrab();
$page[ 'title' ] .= dvwaText('Source','المصدر') . $page[ 'title_separator' ] . $page[ 'title' ];

if (array_key_exists ('id', $_GET) && array_key_exists ('security', $_GET)) {
	$id       = $_GET[ 'id' ];
	$security = $_GET[ 'security' ];

	switch ($id) {
		case 'fi': $vuln = 'File Inclusion'; break;
		case 'brute': $vuln = 'Brute Force'; break;
		case 'csrf': $vuln = 'CSRF'; break;
		case 'exec': $vuln = 'Command Injection'; break;
		case 'sqli': $vuln = 'SQL Injection'; break;
		case 'sqli_blind': $vuln = 'SQL Injection (Blind)'; break;
		case 'upload': $vuln = 'File Upload'; break;
		case 'xss_d': $vuln = 'DOM XSS'; break;
		case 'xss_r': $vuln = 'Reflected XSS'; break;
		case 'xss_s': $vuln = 'Stored XSS'; break;
		case 'weak_id': $vuln = 'Weak Session IDs'; break;
		case 'javascript': $vuln = 'JavaScript'; break;
		case 'authbypass': $vuln = 'Authorisation Bypass'; break;
		case 'open_redirect': $vuln = 'Open HTTP Redirect'; break;
		case 'bac': $vuln = 'Vulnerability: Broken Access Control'; break;
		default: $vuln = 'Unknown Vulnerability';
	}

	$sourcePath = DVWA_WEB_PAGE_TO_ROOT . "vulnerabilities/{$id}/source/{$security}.php";
	$source = @file_get_contents($sourcePath);
	$source = str_replace(array('$html .='), array('echo'), (string)$source);

	$js_html = '';
	$jsPath = DVWA_WEB_PAGE_TO_ROOT . "vulnerabilities/{$id}/source/{$security}.js";
	if (file_exists($jsPath)) {
		$js_source = @file_get_contents($jsPath);
		$js_html = "
		<section class=\"source-block\">
			<div class=\"source-block-head\"><span>vulnerabilities/{$id}/source/{$security}.js</span></div>
			<div class=\"source-code\">" . highlight_string($js_source, true) . "</div>
		</section>
		";
	}

	$page[ 'body' ] .= "
	<div class=\"body_padded\">
		<section class=\"source-shell\">
			<div class=\"source-kicker\">HN / " . dvwaText('SOURCE INTELLIGENCE CONSOLE','وحدة تحليل المصدر') . "</div>
			<h1 class=\"source-title\">" . dvwaSecurityEscape($vuln) . " " . dvwaText('Source','المصدر') . "</h1>
			<div class=\"console-toolbar\">
				<span class=\"console-path\">" . dvwaSecurityEscape(str_replace('\\', '/', $sourcePath)) . "</span>
				<span class=\"console-badge\">" . dvwaText('READ ONLY','قراءة فقط') . "</span>
			</div>
			<section class=\"source-block\">
				<div class=\"source-block-head\"><span>" . dvwaSecurityEscape(str_replace('\\', '/', $sourcePath)) . "</span></div>
				<div class=\"source-code\">" . highlight_string($source, true) . "</div>
			</section>
			{$js_html}
			<div class=\"source-actions\">
				<input type=\"button\" value=\"" . dvwaSecurityEscape(dvwaText('Compare All Levels','مقارنة جميع المستويات')) . "\" onclick=\"window.location.href='view_source_all.php?id=" . rawurlencode($id) . "'\">
			</div>
		</section>
	</div>\n";
} else {
	$page['body'] = '<p>' . dvwaText('Not found','غير موجود') . '</p>';
}

dvwaSourceHtmlEcho($page);
?>
