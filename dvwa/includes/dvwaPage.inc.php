<?php

if( !defined( 'DVWA_WEB_PAGE_TO_ROOT' ) ) {
	die( 'DVWA System error- WEB_PAGE_TO_ROOT undefined' );
	exit;
}

if (!file_exists(DVWA_WEB_PAGE_TO_ROOT . 'config/config.inc.php')) {
	die ("DVWA System error - config file not found. Copy config/config.inc.php.dist to config/config.inc.php and configure to your environment.");
}

// Include configs
require_once DVWA_WEB_PAGE_TO_ROOT . 'config/config.inc.php';
require_once DVWA_WEB_PAGE_TO_ROOT . 'dvwa/includes/security.php';

// Declare the $html variable
if( !isset( $html ) ) {
	$html = "";
}

// Valid security levels
$security_levels = array('low', 'medium', 'high', 'impossible');
if( !isset( $_COOKIE[ 'security' ] ) || !in_array( $_COOKIE[ 'security' ], $security_levels ) ) {
	// Set security cookie to impossible if no cookie exists
	if( in_array( $_DVWA[ 'default_security_level' ], $security_levels) ) {
		dvwaSecurityLevelSet( $_DVWA[ 'default_security_level' ] );
	} else {
		dvwaSecurityLevelSet( 'impossible' );
	}
	// If the cookie wasn't set then the session flags need updating.
	dvwa_start_session();
}

/*
 * This function is called after login and when you change the security level.
 * It gets the security level and sets the httponly and samesite cookie flags
 * appropriately.
 *
 * To force an update of the cookie flags we need to update the session id,
 * just setting the flags and doing a session_start() does not change anything.
 * For this, session_id() or session_regenerate_id() can be used.
 * Both keep the existing session values, so nothing is lost,
 * it will just cause a new Set-Cookie header to be sent with the new right
 * flags and the new id (or the same one if we wish to keep it).
*/
function dvwa_start_session() {
	// This will setup the session cookie based on
	// the security level.

	$security_level = dvwaSecurityLevelGet();
	if ($security_level == 'impossible') {
		$httponly = true;
		$samesite = "Strict";
	}
	else {
		$httponly = false;
		$samesite = "";
	}

	$maxlifetime = 86400;
	$secure = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443));
	$domain = parse_url($_SERVER['HTTP_HOST'], PHP_URL_HOST);

	/*
	 * Need to do this as you can't update the settings of a session
	 * while it is open. So check if one is open, close it if needed
	 * then update the values and start it again.
	*/
	if (session_status() == PHP_SESSION_ACTIVE) {
		session_write_close();
	}

	session_set_cookie_params([
		'lifetime' => $maxlifetime,
		'path' => '/',
		'domain' => $domain,
		'secure' => $secure,
		'httponly' => $httponly,
		'samesite' => $samesite
	]);

	/*
	 * We need to force a new Set-Cookie header with the updated flags by updating
	 * the session id, either regenerating it or setting it to a value, because
	 * session_start() might not generate a Set-Cookie header if a cookie already
	 * exists.
	 *
	 * For impossible security level, we regenerate the session id, PHP will
	 * generate a new random id. This is good security practice because it
	 * prevents the reuse of a previous unauthenticated id that an attacker
	 * might have knowledge of (aka session fixation attack).
   *
	 * For lower levels, we want to allow session fixation attacks, so if an id
	 * already exists, we don't want it to change after authentication. We thus
	 * set the id to its previous value using session_id(), which will force
	 * the Set-Cookie header.
	*/
	if ($security_level == 'impossible') {
		session_start();
		session_regenerate_id(); // force a new id to be generated
	}
	else {
		if (isset($_COOKIE[session_name()])) // if a session id already exists
			session_id($_COOKIE[session_name()]); // we keep the same id
		session_start(); // otherwise a new one will be generated here
	}
}

if (array_key_exists ("Login", $_POST) && $_POST['Login'] == "Login") {
	dvwa_start_session();
} else {
	if (!session_id()) {
		session_start();
	}
}

if (!array_key_exists ("default_locale", $_DVWA)) {
	$_DVWA[ 'default_locale' ] = "en";
}

if (!isset($_SESSION['dvwa']) || !isset($_SESSION['dvwa']['locale'])) dvwaLocaleSet($_DVWA['default_locale']);
if (isset($_GET['locale'])) dvwaLocaleSet($_GET['locale']);

// Start session functions --

function &dvwaSessionGrab() {
	if( !isset( $_SESSION[ 'dvwa' ] ) ) {
		$_SESSION[ 'dvwa' ] = array();
	}
	return $_SESSION[ 'dvwa' ];
}


function dvwaPageStartup( $pActions ) {
	// HN SecureDVWA baseline response hardening.
	if (!headers_sent()) {
		header('X-Content-Type-Options: nosniff');
		header('X-Frame-Options: SAMEORIGIN');
		header('Referrer-Policy: no-referrer');
		header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
		header('Cross-Origin-Opener-Policy: same-origin');
		header('Cross-Origin-Resource-Policy: same-origin');
		header('X-Permitted-Cross-Domain-Policies: none');
		header('X-Download-Options: noopen');
		if ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443)) {
			header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
		}
	}
	if (in_array('authenticated', $pActions)) {
		if( !dvwaIsLoggedIn()) {
			dvwaRedirect( DVWA_WEB_PAGE_TO_ROOT . 'login.php' );
		}
	}
}

function dvwaLogin( $pUsername ) {
	$dvwaSession =& dvwaSessionGrab();
	$dvwaSession[ 'username' ] = $pUsername;
}


function dvwaIsLoggedIn() {
	global $_DVWA;

	if (array_key_exists("disable_authentication", $_DVWA) && $_DVWA['disable_authentication']) {
		return true;
	}
	$dvwaSession =& dvwaSessionGrab();
	return isset( $dvwaSession[ 'username' ] );
}


function dvwaLogout() {
	$dvwaSession =& dvwaSessionGrab();
	unset( $dvwaSession[ 'username' ] );
}


function dvwaPageReload() {
	if  ( array_key_exists( 'HTTP_X_FORWARDED_PREFIX' , $_SERVER )) {
		dvwaRedirect( $_SERVER[ 'HTTP_X_FORWARDED_PREFIX' ] . $_SERVER[ 'PHP_SELF' ] );
	}
	else {
		dvwaRedirect( $_SERVER[ 'PHP_SELF' ] );
	}
}

function dvwaCurrentUser() {
	$dvwaSession =& dvwaSessionGrab();
	return ( isset( $dvwaSession[ 'username' ]) ? $dvwaSession[ 'username' ] : 'Unknown') ;
}

// -- END (Session functions)

function &dvwaPageNewGrab() {
	$returnArray = array(
		'title'           => 'Damn Vulnerable Web Application (DVWA)',
		'title_separator' => ' :: ',
		'body'            => '',
		'page_id'         => '',
		'help_button'     => '',
		'source_button'   => '',
	);
	return $returnArray;
}


function dvwaThemeGet() {
	if (isset($_COOKIE['theme'])) {
		return $_COOKIE[ 'theme' ];
	}
	return 'light';
}


function dvwaSecurityLevelGet() {
	global $_DVWA;

	// If there is a security cookie, that takes priority.
	if (isset($_COOKIE['security'])) {
		return $_COOKIE[ 'security' ];
	}

	// If not, check to see if authentication is disabled, if it is, use
	// the default security level.
	if (array_key_exists("disable_authentication", $_DVWA) && $_DVWA['disable_authentication']) {
		return $_DVWA[ 'default_security_level' ];
	}

	// Worse case, set the level to impossible.
	return 'impossible';
}

function dvwaSecurityLevelSet( $pSecurityLevel ) {
	if( $pSecurityLevel == 'impossible' ) {
		$httponly = true;
	}
	else {
		$httponly = false;
	}

	setcookie( 'security', $pSecurityLevel, 0, '/', '', ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443)), $httponly );
	$_COOKIE['security'] = $pSecurityLevel;
}

function dvwaLocaleGet() {
	$dvwaSession =& dvwaSessionGrab();
	return $dvwaSession[ 'locale' ];
}

function dvwaSQLiDBGet() {
	global $_DVWA;
	return $_DVWA['SQLI_DB'];
}

function dvwaLocaleSet( $pLocale ) {
	$dvwaSession =& dvwaSessionGrab();
	$locales = array('en', 'ar', 'zh');
	if( in_array( $pLocale, $locales) ) {
		$dvwaSession[ 'locale' ] = $pLocale;
	} else {
		$dvwaSession[ 'locale' ] = 'en';
	}
}

function dvwaIsArabic() { return dvwaLocaleGet() === 'ar'; }

function dvwaText($english, $arabic) { return dvwaIsArabic() ? $arabic : $english; }

function dvwaLocaleUrl($locale) {
	$path = isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : '';
	$params = $_GET; $params['locale'] = $locale;
	return $path . '?' . http_build_query($params);
}

function dvwaSecurityLevelLabel($level) {
	$map=array('low'=>'منخفض','medium'=>'متوسط','high'=>'مرتفع','impossible'=>'محكم');
	return dvwaIsArabic() && isset($map[$level]) ? $map[$level] : $level;
}

function dvwaMenuLabel($key,$english) {
	$map=array(
		'home'=>'الرئيسية','instructions'=>'التعليمات','setup'=>'الإعداد وإعادة قاعدة البيانات','setup_guest'=>'إعداد التطبيق',
		'brute'=>'تخمين كلمات المرور','exec'=>'حقن الأوامر','csrf'=>'تزوير الطلبات','fi'=>'تضمين الملفات','upload'=>'رفع الملفات',
		'captcha'=>'اختبار التحقق غير الآمن','sqli'=>'حقن قواعد البيانات','sqli_blind'=>'حقن قواعد البيانات الأعمى','weak_id'=>'معرّفات الجلسات الضعيفة',
		'xss_d'=>'حقن النصوص في واجهة الصفحة','xss_r'=>'حقن النصوص المنعكس','xss_s'=>'حقن النصوص المخزن','csp'=>'تجاوز سياسة حماية المحتوى',
		'javascript'=>'هجمات جافاسكربت','authbypass'=>'تجاوز الصلاحيات','open_redirect'=>'إعادة التوجيه المفتوحة','encryption'=>'التشفير','api'=>'واجهة البرمجة',
		'security'=>'إعدادات الحماية','security_dashboard'=>'لوحة المراقبة الأمنية','phpinfo'=>'معلومات PHP','about'=>'حول المشروع','logout'=>'تسجيل الخروج'
	);
	return dvwaIsArabic() && isset($map[$key]) ? $map[$key] : $english;
}

// Start message functions --

function dvwaMessagePush( $pMessage ) {
	$dvwaSession =& dvwaSessionGrab();
	if( !isset( $dvwaSession[ 'messages' ] ) ) {
		$dvwaSession[ 'messages' ] = array();
	}
	$dvwaSession[ 'messages' ][] = $pMessage;
}


function dvwaMessagePop() {
	$dvwaSession =& dvwaSessionGrab();
	if( !isset( $dvwaSession[ 'messages' ] ) || count( $dvwaSession[ 'messages' ] ) == 0 ) {
		return false;
	}
	return array_shift( $dvwaSession[ 'messages' ] );
}


function messagesPopAllToHtml() {
	$messagesHtml = '';
	while( $message = dvwaMessagePop() ) {   // TODO- sharpen!
		$messagesHtml .= "<div class=\"message\">{$message}</div>";
	}

	return $messagesHtml;
}

// --END (message functions)

function dvwaHtmlEcho( $pPage ) {
	$menuBlocks = array();

	$menuBlocks[ 'home' ] = array();
	if( dvwaIsLoggedIn() ) {
		$menuBlocks[ 'home' ][] = array( 'id' => 'home', 'name' => dvwaMenuLabel('home','Home'), 'url' => '.' );
		$menuBlocks[ 'home' ][] = array( 'id' => 'instructions', 'name' => dvwaMenuLabel('instructions','Instructions'), 'url' => 'instructions.php' );
		$menuBlocks[ 'home' ][] = array( 'id' => 'setup', 'name' => dvwaMenuLabel('setup','Setup / Reset DB'), 'url' => 'setup.php' );
	}
	else {
		$menuBlocks[ 'home' ][] = array( 'id' => 'setup', 'name' => dvwaMenuLabel('setup_guest','Setup DVWA'), 'url' => 'setup.php' );
		$menuBlocks[ 'home' ][] = array( 'id' => 'instructions', 'name' => dvwaMenuLabel('instructions','Instructions'), 'url' => 'instructions.php' );
	}

	if( dvwaIsLoggedIn() ) {
		$menuBlocks[ 'vulnerabilities' ] = array();
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'brute', 'name' => dvwaMenuLabel('brute','Brute Force'), 'url' => 'vulnerabilities/brute/' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'exec', 'name' => dvwaMenuLabel('exec','Command Injection'), 'url' => 'vulnerabilities/exec/' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'csrf', 'name' => dvwaMenuLabel('csrf','CSRF'), 'url' => 'vulnerabilities/csrf/' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'fi', 'name' => dvwaMenuLabel('fi','File Inclusion'), 'url' => 'vulnerabilities/fi/.?page=include.php' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'upload', 'name' => dvwaMenuLabel('upload','File Upload'), 'url' => 'vulnerabilities/upload/' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'captcha', 'name' => dvwaMenuLabel('captcha','Insecure CAPTCHA'), 'url' => 'vulnerabilities/captcha/' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'sqli', 'name' => dvwaMenuLabel('sqli','SQL Injection'), 'url' => 'vulnerabilities/sqli/' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'sqli_blind', 'name' => dvwaMenuLabel('sqli_blind','SQL Injection (Blind)'), 'url' => 'vulnerabilities/sqli_blind/' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'weak_id', 'name' => dvwaMenuLabel('weak_id','Weak Session IDs'), 'url' => 'vulnerabilities/weak_id/' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'xss_d', 'name' => dvwaMenuLabel('xss_d','XSS (DOM)'), 'url' => 'vulnerabilities/xss_d/' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'xss_r', 'name' => dvwaMenuLabel('xss_r','XSS (Reflected)'), 'url' => 'vulnerabilities/xss_r/' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'xss_s', 'name' => dvwaMenuLabel('xss_s','XSS (Stored)'), 'url' => 'vulnerabilities/xss_s/' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'csp', 'name' => dvwaMenuLabel('csp','CSP Bypass'), 'url' => 'vulnerabilities/csp/' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'javascript', 'name' => dvwaMenuLabel('javascript','JavaScript Attacks'), 'url' => 'vulnerabilities/javascript/' );
		if (dvwaCurrentUser() == "admin") {
			$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'authbypass', 'name' => dvwaMenuLabel('authbypass','Authorisation Bypass'), 'url' => 'vulnerabilities/authbypass/' );
		}
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'open_redirect', 'name' => dvwaMenuLabel('open_redirect','Open HTTP Redirect'), 'url' => 'vulnerabilities/open_redirect/' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'encryption', 'name' => dvwaMenuLabel('encryption','Cryptography'), 'url' => 'vulnerabilities/cryptography/' );
		$menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'api', 'name' => dvwaMenuLabel('api','API'), 'url' => 'vulnerabilities/api/' );
		# $menuBlocks[ 'vulnerabilities' ][] = array( 'id' => 'bac', 'name' => 'Broken Access Control', 'url' => 'vulnerabilities/bac/' );
	}

	$menuBlocks[ 'meta' ] = array();
	if( dvwaIsLoggedIn() ) {
		$menuBlocks[ 'meta' ][] = array( 'id' => 'security', 'name' => dvwaMenuLabel('security','DVWA Security'), 'url' => 'security.php' );
	$menuBlocks[ 'meta' ][] = array( 'id' => 'security_dashboard', 'name' => dvwaMenuLabel('security_dashboard','Security Dashboard'), 'url' => 'security/dashboard.php' );
		$menuBlocks[ 'meta' ][] = array( 'id' => 'security_health', 'name' => dvwaText('Security Health','صحة الأمن'), 'url' => 'security/health.php' );
		$menuBlocks[ 'meta' ][] = array( 'id' => 'security_alerts', 'name' => dvwaText('Alert Center','مركز التنبيهات'), 'url' => 'security/notifications.php' );
		$menuBlocks[ 'meta' ][] = array( 'id' => 'security_timeline', 'name' => dvwaText('Threat Timeline','الخط الزمني للتهديدات'), 'url' => 'security/timeline.php' );
		$menuBlocks[ 'meta' ][] = array( 'id' => 'security_risk', 'name' => dvwaText('Risk Matrix','مصفوفة المخاطر'), 'url' => 'security/risk.php' );
		$menuBlocks[ 'meta' ][] = array( 'id' => 'security_controls', 'name' => dvwaText('Security Controls','ضوابط الأمن'), 'url' => 'security/controls.php' );
		$menuBlocks[ 'meta' ][] = array( 'id' => 'security_assurance', 'name' => dvwaText('Assurance Studio','استوديو التحقق'), 'url' => 'security/assurance.php' );
		$menuBlocks[ 'meta' ][] = array( 'id' => 'security_report', 'name' => dvwaText('Security Report','التقرير الأمني'), 'url' => 'security/report.php' );
		$menuBlocks[ 'meta' ][] = array( 'id' => 'phpinfo', 'name' => dvwaMenuLabel('phpinfo','PHP Info'), 'url' => 'phpinfo.php' );
	}
	$menuBlocks[ 'meta' ][] = array( 'id' => 'about', 'name' => dvwaMenuLabel('about','About'), 'url' => 'about.php' );

	if( dvwaIsLoggedIn() ) {
		$menuBlocks[ 'logout' ] = array();
		$menuBlocks[ 'logout' ][] = array( 'id' => 'logout', 'name' => dvwaMenuLabel('logout','Logout'), 'url' => 'logout.php' );
	}

	$menuHtml = '';

	$menuSectionLabels = array(
		'home' => dvwaText('CONTROL DECK','مركز التحكم'),
		'vulnerabilities' => dvwaText('ATTACK SURFACE','سطح الهجوم'),
		'meta' => dvwaText('SECURITY INTELLIGENCE','استخبارات الأمن'),
		'logout' => dvwaText('SESSION','الجلسة'),
	);
	foreach( $menuBlocks as $menuBlockKey => $menuBlock ) {
		$menuBlockHtml = '';
		foreach( $menuBlock as $menuItem ) {
			$selectedClass = ( $menuItem[ 'id' ] == $pPage[ 'page_id' ] ) ? 'selected' : '';
			$fixedUrl = DVWA_WEB_PAGE_TO_ROOT.$menuItem[ 'url' ];
			$menuBlockHtml .= "<li id=\"{$menuItem[ 'id' ]}\" class=\"{$selectedClass}\"><a href=\"{$fixedUrl}\" data-nav-id=\"{$menuItem[ 'id' ]}\" title=\"{$menuItem[ 'name' ]}\">{$menuItem[ 'name' ]}</a></li>
";
		}
		$sectionLabel = isset($menuSectionLabels[$menuBlockKey]) ? $menuSectionLabels[$menuBlockKey] : $menuBlockKey;
		$menuHtml .= "<section class=\"hn-nav-section hn-nav-section-{$menuBlockKey}\" data-nav-section=\"{$menuBlockKey}\"><div class=\"hn-nav-section-title\"><span>{$sectionLabel}</span><i></i></div><ul class=\"menuBlocks\">{$menuBlockHtml}</ul></section>";
	}

	// Get security cookie --
	$securityLevelHtml = '';
	switch( dvwaSecurityLevelGet() ) {
		case 'low':
			$securityLevelHtml = dvwaSecurityLevelLabel('low');
			break;
		case 'medium':
			$securityLevelHtml = dvwaSecurityLevelLabel('medium');
			break;
		case 'high':
			$securityLevelHtml = dvwaSecurityLevelLabel('high');
			break;
		default:
			$securityLevelHtml = dvwaSecurityLevelLabel('impossible');
			break;
	}
	// -- END (security cookie)

	$userInfoHtml = '<em>Username:</em> ' . dvwaCurrentUser();
	$securityLevelHtml = '<em>Security Level:</em> ' . $securityLevelHtml;
	$localeHtml = '<em>Language:</em> ' . dvwaLocaleGet();
	$sqliDbHtml = '<em>SQLi DB:</em> ' . dvwaSQLiDBGet();


	$messagesHtml = messagesPopAllToHtml();
	if( $messagesHtml ) {
		$messagesHtml = "<div class=\"body_padded\">{$messagesHtml}</div>";
	}
	$showWelcome = !empty($_SESSION['hn_show_welcome']);
	if ($showWelcome) unset($_SESSION['hn_show_welcome']);

	$systemInfoHtml = "";
	if( dvwaIsLoggedIn() )
		$systemInfoHtml = "<div align=\"left\">{$userInfoHtml}<br />{$securityLevelHtml}<br />{$localeHtml}<br />{$sqliDbHtml}</div>";
	if( $pPage[ 'source_button' ] ) {
		$systemInfoHtml = dvwaButtonSourceHtmlGet( $pPage[ 'source_button' ] ) . " $systemInfoHtml";
	}
	if( $pPage[ 'help_button' ] ) {
		$systemInfoHtml = dvwaButtonHelpHtmlGet( $pPage[ 'help_button' ] ) . " $systemInfoHtml";
	}

	// Send Headers + main HTML code
	$documentLang = dvwaIsArabic() ? 'ar' : 'en-GB';
	$documentDir = dvwaIsArabic() ? 'rtl' : 'ltr';
	$cinematicCss = DVWA_WEB_PAGE_TO_ROOT . 'dvwa/css/cinematic.css';
	$arabicCss = '';
	$footerText = dvwaText('Damn Vulnerable Web Application (DVWA) — HN SecureDVWA','تطبيق DVWA — مختبر HN SecureDVWA');
	$projectBarText = dvwaText('HARDENED WEB SECURITY LABORATORY','مختبر أمن تطبيقات الويب المحصّن');
	$languageSwitch = '<div class="hn-language-switch"><a href="' . dvwaLocaleUrl('en') . '" class="' . (dvwaLocaleGet()==='en'?'active':'') . '">EN</a><span>/</span><a href="' . dvwaLocaleUrl('ar') . '" class="' . (dvwaLocaleGet()==='ar'?'active':'') . '">العربية</a></div>';
	$welcomeHtml = $showWelcome ? '<div class="hn-welcome" role="dialog" aria-modal="true" aria-label="HN SecureDVWA team welcome"><div class="hn-welcome-orbit hn-orbit-a"></div><div class="hn-welcome-orbit hn-orbit-b"></div><div class="hn-welcome-core"><span class="hn-welcome-mark">HN</span><span class="hn-welcome-kicker">' . dvwaText('SECURE ACCESS ESTABLISHED','تم تأمين الوصول بنجاح') . '</span><strong>SECUREDVWA</strong><em>HN BLACKBOX · CYBER DEFENSE LAB</em><div class="hn-welcome-team"><div class="hn-team-card"><b>SECUREDVWA</b><small>WEB SECURITY HARDENING</small></div><div class="hn-team-card"><b>SECURITY INTELLIGENCE</b><small>MONITOR · VERIFY · REPORT</small></div><div class="hn-team-card"><b>LOCAL DEFENSE LAB</b><small>DVWA · EDUCATIONAL ENVIRONMENT</small></div></div><div class="hn-welcome-foot"><span>HARDENED WEB LAB</span><span>VISUAL SECURITY HUD</span><span>LOCAL / EDUCATIONAL</span></div><button class="hn-welcome-close" type="button">ENTER SECUREDVWA</button></div></div>' : '';
	Header( 'Cache-Control: no-cache, must-revalidate');
	Header( 'Content-Type: text/html;charset=utf-8' );
	Header( 'Expires: Tue, 23 Jun 2009 12:00:00 GMT' );

	echo "<!DOCTYPE html>
<html lang=\"{$documentLang}\" dir=\"{$documentDir}\">
	<head>
		<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />
		<meta name=\"viewport\" content=\"width=device-width, initial-scale=1\" />
		<title>{$pPage[ 'title' ]}</title>
		<link rel=\"stylesheet\" type=\"text/css\" href=\"" . DVWA_WEB_PAGE_TO_ROOT . "dvwa/css/main.css\" />
		<link rel=\"stylesheet\" type=\"text/css\" href=\"{$cinematicCss}\" />
		<link rel=\"stylesheet\" type=\"text/css\" href=\"" . DVWA_WEB_PAGE_TO_ROOT . "dvwa/css/hn_cinematic_ultra.css\" /><link rel=\"stylesheet\" type=\"text/css\" href=\"" . DVWA_WEB_PAGE_TO_ROOT . "dvwa/css/hn_graphics.css\" /><link rel=\"stylesheet\" type=\"text/css\" href=\"" . DVWA_WEB_PAGE_TO_ROOT . "dvwa/css/hn_visual_core.css\" />
		{$arabicCss}
		<link rel=\"icon\" type=\"image/ico\" href=\"" . DVWA_WEB_PAGE_TO_ROOT . "favicon.ico\" />
		<script type=\"text/javascript\" src=\"" . DVWA_WEB_PAGE_TO_ROOT . "dvwa/js/dvwaPage.js\"></script>
		<script type=\"text/javascript\" src=\"" . DVWA_WEB_PAGE_TO_ROOT . "dvwa/js/hn_cinematic_transitions.js\"></script>
	</head>
	<body class=\"home hud-interface\" data-locale=\"{$documentLang}\">
		{$welcomeHtml}
		<div id=\"container\">
			<div id=\"header\">
				<button class=\"hn-sidebar-reopen\" type=\"button\" aria-label=\"Open sidebar\" title=\"Open sidebar\">›</button>
				<button class=\"hn-nav-toggle\" type=\"button\" aria-label=\"Toggle navigation\" aria-expanded=\"false\"><span></span><span></span><span></span></button>
				<img src=\"" . DVWA_WEB_PAGE_TO_ROOT . "dvwa/images/logo.png\" alt=\"Damn Vulnerable Web Application\" />
				<div class=\"hn-header-actions\"><button class=\"hn-command-trigger\" type=\"button\" title=\"Command palette\"><span>⌘K</span><b>COMMAND</b></button><div class=\"hn-header-pulse\"><i></i><span>LOCAL LAB</span></div></div>
			</div>
			<div id=\"main_menu\"><div class=\"hn-sidebar-head\"><div class=\"hn-sidebar-brand\"><span class=\"hn-sidebar-mark\">HN</span><span><b>SECUREDVWA</b><small>CYBER DEFENSE LAB</small></span></div><button class=\"hn-sidebar-collapse\" type=\"button\" aria-label=\"Collapse sidebar\" title=\"Collapse sidebar\">‹</button></div><div class=\"hn-nav-tools\"><label class=\"hn-nav-search\"><span>⌕</span><input id=\"hn-nav-search-input\" type=\"search\" placeholder=\"Search module…\" autocomplete=\"off\" /></label><span class=\"hn-nav-key\">/</span></div><div id=\"main_menu_padded\">{$menuHtml}</div><div class=\"hn-sidebar-footer\"><span class=\"hn-sidebar-signal\"><i></i><b>READY</b><small>LOCAL ONLY</small></span><span class=\"hn-sidebar-version\">V8.0</span></div></div>
			<div id=\"main_body\">
				<div class=\"hn-project-bar\"><span class=\"hud-brand\">HN / SECUREDVWA</span><span>{$projectBarText}</span>{$languageSwitch}<span class=\"hud-live\"><i></i> LOCAL LAB</span></div>
				{$pPage[ 'body' ]}{$messagesHtml}
			</div>
			<div class=\"clear\"></div>
			<div id=\"system_info\">{$systemInfoHtml}</div>
			<div id=\"footer\"><p>{$footerText}</p><script src='" . DVWA_WEB_PAGE_TO_ROOT . "dvwa/js/add_event_listeners.js'></script><script src='" . DVWA_WEB_PAGE_TO_ROOT . "dvwa/js/hn_navigation.js'></script><script src='" . DVWA_WEB_PAGE_TO_ROOT . "dvwa/js/hn_presentation.js'></script><script src='" . DVWA_WEB_PAGE_TO_ROOT . "dvwa/js/hn_ultra_ui.js'></script><script src='" . DVWA_WEB_PAGE_TO_ROOT . "dvwa/js/hn_cinematic_ultra.js'></script><script src='" . DVWA_WEB_PAGE_TO_ROOT . "dvwa/js/hn_graphics.js'></script><script src='" . DVWA_WEB_PAGE_TO_ROOT . "dvwa/js/hn_welcome.js'></script></div>
		</div>
	</body>
</html>";

}


function dvwaHelpHtmlEcho( $pPage ) {
	$documentLang = dvwaIsArabic() ? 'ar' : 'en-GB';
	$documentDir = dvwaIsArabic() ? 'rtl' : 'ltr';
	// Send Headers
	Header( 'Cache-Control: no-cache, must-revalidate');   // HTTP/1.1
	Header( 'Content-Type: text/html;charset=utf-8' );     // TODO- proper XHTML headers...
	Header( 'Expires: Tue, 23 Jun 2009 12:00:00 GMT' );    // Date in the past

	echo "<!DOCTYPE html>

<html lang=\"{$documentLang}\" dir=\"{$documentDir}\">

	<head>

		<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />

		<title>{$pPage[ 'title' ]}</title>

		<link rel=\"stylesheet\" type=\"text/css\" href=\"" . DVWA_WEB_PAGE_TO_ROOT . "dvwa/css/help.css\" />

		<link rel=\"stylesheet\" type=\"text/css\" href=\"" . DVWA_WEB_PAGE_TO_ROOT . "dvwa/css/cinematic.css\" />
		<script type=\"text/javascript\" src=\"" . DVWA_WEB_PAGE_TO_ROOT . "dvwa/js/hn_cinematic_transitions.js\"></script>

		<link rel=\"icon\" type=\"\image/ico\" href=\"" . DVWA_WEB_PAGE_TO_ROOT . "favicon.ico\" />

	</head>

	<body class=\"hud-interface hud-document\">

	<div id=\"container\" class=\"help-console\">

			{$pPage[ 'body' ]}

		</div>

	</body>

</html>";
}


function dvwaSourceHtmlEcho( $pPage ) {
	$documentLang = dvwaIsArabic() ? 'ar' : 'en-GB';
	$documentDir = dvwaIsArabic() ? 'rtl' : 'ltr';
	// Send Headers
	Header( 'Cache-Control: no-cache, must-revalidate');   // HTTP/1.1
	Header( 'Content-Type: text/html;charset=utf-8' );     // TODO- proper XHTML headers...
	Header( 'Expires: Tue, 23 Jun 2009 12:00:00 GMT' );    // Date in the past

	echo "<!DOCTYPE html>

<html lang=\"{$documentLang}\" dir=\"{$documentDir}\">

	<head>

		<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />

		<title>{$pPage[ 'title' ]}</title>

		<link rel=\"stylesheet\" type=\"text/css\" href=\"" . DVWA_WEB_PAGE_TO_ROOT . "dvwa/css/source.css\" />
		<link rel=\"stylesheet\" type=\"text/css\" href=\"" . DVWA_WEB_PAGE_TO_ROOT . "dvwa/css/cinematic.css\" />
		<script type=\"text/javascript\" src=\"" . DVWA_WEB_PAGE_TO_ROOT . "dvwa/js/hn_cinematic_transitions.js\"></script>

		<link rel=\"icon\" type=\"\image/ico\" href=\"" . DVWA_WEB_PAGE_TO_ROOT . "favicon.ico\" />

	</head>

	<body class=\"hud-interface hud-document\">

		<div id=\"container\" class=\"source-console\">

			{$pPage[ 'body' ]}

		</div>

	</body>

</html>";
}

// To be used on all external links --
function dvwaExternalLinkUrlGet( $pLink,$text=null ) {
	if(is_null( $text ) || $text == "") {
		return '<a href="' . $pLink . '" target="_blank">' . $pLink . '</a>';
	}
	else {
		return '<a href="' . $pLink . '" target="_blank">' . $text . '</a>';
	}
}
// -- END ( external links)

function dvwaButtonHelpHtmlGet( $pId ) {
	$security = dvwaSecurityLevelGet();
	$locale = dvwaLocaleGet();
	return "<input type=\"button\" value=\"" . dvwaSecurityEscape(dvwaText('View Help','عرض المساعدة')) . "\" class=\"popup_button\" id='help_button' data-help-url='" . DVWA_WEB_PAGE_TO_ROOT . "vulnerabilities/view_help.php?id={$pId}&security={$security}&locale={$locale}' )\">";
}


function dvwaButtonSourceHtmlGet( $pId ) {
	$security = dvwaSecurityLevelGet();
	return "<input type=\"button\" value=\"" . dvwaSecurityEscape(dvwaText('View Source','عرض المصدر')) . "\" class=\"popup_button\" id='source_button' data-source-url='" . DVWA_WEB_PAGE_TO_ROOT . "vulnerabilities/view_source.php?id={$pId}&security={$security}' )\">";
}


// Database Management --

if( $DBMS == 'MySQL' ) {
	$DBMS = htmlspecialchars(strip_tags( $DBMS ));
}
elseif( $DBMS == 'PGSQL' ) {
	$DBMS = htmlspecialchars(strip_tags( $DBMS ));
}
else {
	$DBMS = "No DBMS selected.";
}

function dvwaSecurityRecordNormalRequest() {
    static $registered = false;
    if ($registered || !function_exists('dvwaSecurityOperation')) return;

    $path = isset($_SERVER['REQUEST_URI']) ? (string)parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) : '';
    if ($path === '' || strpos($path, '/security/') !== false) return;

    // Normal-operations telemetry is intentionally narrow: it only tracks
    // successful, meaningful actions performed inside the seven selected DVWA
    // vulnerability labs. Login, navigation, dashboard pages, redirects and
    // ordinary application requests are never counted here.
    $relative = trim(str_replace('\\', '/', $path), '/');
    if (!preg_match('#(?:^|/)vulnerabilities/(sqli|sqli_blind|exec|xss_r|xss_d|xss_s|upload)(?:/|$)#i', $relative, $m)) return;
    $slug = strtolower($m[1]);
    $method = isset($_SERVER['REQUEST_METHOD']) ? strtoupper((string)$_SERVER['REQUEST_METHOD']) : 'GET';
    $success = false;
    $operation = '';
    $target = 'vulnerabilities/' . $slug;

    // A security event in this request always wins: the same request must not
    // appear again in Normal Operations.
    if (!empty($GLOBALS['dvwa_security_event_recorded'])) return;

    switch ($slug) {
        case 'sqli':
            if (isset($_REQUEST['Submit']) && isset($_REQUEST['id'])) {
                $id = trim((string)$_REQUEST['id']);
                $success = ($id !== '' && ctype_digit($id));
                $operation = 'SQL Injection / Valid User ID lookup';
                $target = 'vulnerabilities/sqli';
            }
            break;
        case 'sqli_blind':
            if (isset($_GET['Submit']) && isset($_GET['id'])) {
                $id = trim((string)$_GET['id']);
                $success = ($id !== '' && ctype_digit($id));
                $operation = 'Blind SQL Injection / Valid User ID check';
                $target = 'vulnerabilities/sqli_blind';
            }
            break;
        case 'exec':
            if (isset($_POST['Submit'])) {
                $ip = trim((string)($_POST['ip'] ?? $_REQUEST['ip'] ?? ''));
                $success = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false
                    && !(function_exists('dvwaSecurityContainsCommandInjection') && dvwaSecurityContainsCommandInjection($ip));
                $operation = 'Command Execution / Valid IPv4 input';
                $target = 'vulnerabilities/exec';
            }
            break;
        case 'xss_r':
            if (array_key_exists('name', $_GET)) {
                $name = (string)$_GET['name'];
                $success = !dvwaSecurityContainsXss($name);
                $operation = 'Reflected XSS / Safe reflection';
                $target = 'vulnerabilities/xss_r';
            }
            break;
        case 'xss_d':
            if (array_key_exists('default', $_GET)) {
                $allowed = ['English', 'French', 'Spanish', 'German'];
                $candidate = (string)$_GET['default'];
                $success = in_array($candidate, $allowed, true) && !dvwaSecurityContainsXss($candidate);
                $operation = 'DOM XSS / Allowed language selection';
                $target = 'vulnerabilities/xss_d';
            }
            break;
        case 'xss_s':
            if (isset($_POST['btnSign'])) {
                $message = trim((string)($_POST['mtxMessage'] ?? ''));
                $name = trim((string)($_POST['txtName'] ?? ''));
                $success = !dvwaSecurityContainsXss($message) && !dvwaSecurityContainsXss($name);
                $operation = 'Stored XSS / Guestbook entry';
                $target = 'vulnerabilities/xss_s';
            }
            break;
        case 'upload':
            if (isset($_POST['Upload']) && isset($_FILES['uploaded'])) {
                $file = $_FILES['uploaded'];
                $ok = isset($file['error'], $file['name'], $file['size']) && (int)$file['error'] === UPLOAD_ERR_OK;
                if ($ok) {
                    $name = basename((string)$file['name']);
                    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                    $allowedExt = ['jpg', 'jpeg', 'png'];
                    $mimeOk = true;
                    if (class_exists('finfo') && isset($file['tmp_name'])) {
                        try {
                            $f = new finfo(FILEINFO_MIME_TYPE);
                            $mime = $f->file((string)$file['tmp_name']);
                            $mimeOk = in_array($mime, ['image/jpeg', 'image/png'], true);
                        } catch (Throwable $e) { $mimeOk = false; }
                    }
                    $imgOk = isset($file['tmp_name']) && function_exists('getimagesize') && @getimagesize((string)$file['tmp_name']) !== false;
                    $success = in_array($ext, $allowedExt, true) && (int)$file['size'] <= 100000 && $mimeOk && $imgOk;
                }
                $operation = 'File Upload / Valid image upload';
                $target = 'vulnerabilities/upload';
            }
            break;
    }

    if (!$success || $operation === '') return;

    $registered = true;
    register_shutdown_function(function() use ($operation, $target) {
        if (!empty($GLOBALS['dvwa_security_event_recorded'])) return;
        $input = function_exists('dvwaSecurityRequestPreview') ? dvwaSecurityRequestPreview() : '';
        $details = $input !== '' ? $input : 'Valid functional input';
        dvwaSecurityOperation($operation, $target, 'Successful', $details);
    });
}


function dvwaDatabaseConnect() {
	global $_DVWA;
	global $DBMS;
	//global $DBMS_connError;
	global $db;
	global $sqlite_db_connection;

	if( $DBMS == 'MySQL' ) {
		if( !@($GLOBALS["___mysqli_ston"] = mysqli_connect( $_DVWA[ 'db_server' ],  $_DVWA[ 'db_user' ],  $_DVWA[ 'db_password' ], "", $_DVWA[ 'db_port' ] ))
		|| !@((bool)mysqli_query($GLOBALS["___mysqli_ston"], "USE " . $_DVWA[ 'db_database' ])) ) {
			//die( $DBMS_connError );
			dvwaLogout();
			dvwaMessagePush( 'Unable to connect to the database.<br />' . mysqli_error($GLOBALS["___mysqli_ston"]));
			dvwaRedirect( DVWA_WEB_PAGE_TO_ROOT . 'setup.php' );
		}
		// MySQL PDO Prepared Statements (for impossible levels)
		$db = new PDO('mysql:host=' . $_DVWA[ 'db_server' ].';dbname=' . $_DVWA[ 'db_database' ].';port=' . $_DVWA['db_port'] . ';charset=utf8', $_DVWA[ 'db_user' ], $_DVWA[ 'db_password' ]);
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
	}
	elseif( $DBMS == 'PGSQL' ) {
		//$dbconn = pg_connect("host={$_DVWA[ 'db_server' ]} dbname={$_DVWA[ 'db_database' ]} user={$_DVWA[ 'db_user' ]} password={$_DVWA[ 'db_password' ]}"
		//or die( $DBMS_connError );
		dvwaMessagePush( 'PostgreSQL is not currently supported.' );
		dvwaPageReload();
	}
	else {
		die ( "Unknown {$DBMS} selected." );
	}

	if ($_DVWA['SQLI_DB'] == SQLITE) {
		$location = DVWA_WEB_PAGE_TO_ROOT . "database/" . $_DVWA['SQLITE_DB'];
		$sqlite_db_connection = new SQLite3($location);
		$sqlite_db_connection->enableExceptions(true);
	#	print "sqlite db setup";
	}

	// Record one safe normal-operation telemetry event for the current request.
	// Security-intelligence pages are excluded inside the recorder itself.
	if (function_exists('dvwaSecurityRecordNormalRequest')) {
		dvwaSecurityRecordNormalRequest();
	}
}

// -- END (Database Management)


function dvwaRedirect( $pLocation ) {
	session_commit();
	header( "Location: {$pLocation}" );
	exit;
}

// XSS Stored guestbook function --
function dvwaGuestbook() {
	$query  = "SELECT name, comment FROM guestbook";
	$result = mysqli_query($GLOBALS["___mysqli_ston"],  $query );

	$guestbook = '';

	while( $row = mysqli_fetch_row( $result ) ) {
		if( dvwaSecurityLevelGet() == 'impossible' ) {
			$name    = htmlspecialchars( $row[0] );
			$comment = htmlspecialchars( $row[1] );
		}
		else {
			$name    = dvwaSecurityEscape($row[0]);
			$comment = dvwaSecurityEscape($row[1]);
		}

		$guestbook .= "<div id=\"guestbook_comments\">Name: {$name}<br />" . "Message: {$comment}<br /></div>\n";
	}
	return $guestbook;
}
// -- END (XSS Stored guestbook)


// Token functions --
function checkToken( $user_token, $session_token, $returnURL ) {  # Validate the given (CSRF) token
	global $_DVWA;

	if (array_key_exists("disable_authentication", $_DVWA) && $_DVWA['disable_authentication']) {
		return true;
	}

	if( $user_token !== $session_token || !isset( $session_token ) ) {
		dvwaMessagePush( 'CSRF token is incorrect' );
		dvwaRedirect( $returnURL );
	}
}

function generateSessionToken() {  # Generate a brand new (CSRF) token
	if( isset( $_SESSION[ 'session_token' ] ) ) {
		destroySessionToken();
	}
	$_SESSION[ 'session_token' ] = md5( uniqid() );
}

function destroySessionToken() {  # Destroy any session with the name 'session_token'
	unset( $_SESSION[ 'session_token' ] );
}

function tokenField() {  # Return a field for the (CSRF) token
	return "<input type='hidden' name='user_token' value='{$_SESSION[ 'session_token' ]}' />";
}
// -- END (Token functions)


// Setup Functions --
$PHPUploadPath    = realpath( getcwd() . DIRECTORY_SEPARATOR . DVWA_WEB_PAGE_TO_ROOT . "hackable" . DIRECTORY_SEPARATOR . "uploads" ) . DIRECTORY_SEPARATOR;
$PHPCONFIGPath       = realpath( getcwd() . DIRECTORY_SEPARATOR . DVWA_WEB_PAGE_TO_ROOT . "config");


$phpDisplayErrors = 'PHP function display_errors: <span class="' . ( ini_get( 'display_errors' ) ? 'success">Enabled' : 'failure">Disabled' ) . '</span>';                                                  // Verbose error messages (e.g. full path disclosure)
$phpDisplayStartupErrors = 'PHP function display_startup_errors: <span class="' . ( ini_get( 'display_startup_errors' ) ? 'success">Enabled' : 'failure">Disabled' ) . '</span>';                                                  // Verbose error messages (e.g. full path disclosure)
$phpDisplayErrors = 'PHP function display_errors: <span class="' . ( ini_get( 'display_errors' ) ? 'success">Enabled' : 'failure">Disabled' ) . '</span>';                                                  // Verbose error messages (e.g. full path disclosure)
$phpURLInclude    = 'PHP function allow_url_include: <span class="' . ( ini_get( 'allow_url_include' ) ? 'success">Enabled' : 'failure">Disabled' ) . '</span> - Feature deprecated in PHP 7.4, see lab for more information';                                   // RFI
$phpURLFopen      = 'PHP function allow_url_fopen: <span class="' . ( ini_get( 'allow_url_fopen' ) ? 'success">Enabled' : 'failure">Disabled' ) . '</span>';                                       // RFI
$phpGD            = 'PHP module gd: <span class="' . ( ( extension_loaded( 'gd' ) && function_exists( 'gd_info' ) ) ? 'success">Installed' : 'failure">Missing - Only an issue if you want to play with captchas' ) . '</span>';                    // File Upload
$phpMySQL         = 'PHP module mysql: <span class="' . ( ( extension_loaded( 'mysqli' ) && function_exists( 'mysqli_query' ) ) ? 'success">Installed' : 'failure">Missing' ) . '</span>';                // Core DVWA
$phpPDO           = 'PHP module pdo_mysql: <span class="' . ( extension_loaded( 'pdo_mysql' ) ? 'success">Installed' : 'failure">Missing' ) . '</span>';                // SQLi
$DVWARecaptcha    = 'reCAPTCHA key: <span class="' . ( ( isset( $_DVWA[ 'recaptcha_public_key' ] ) && $_DVWA[ 'recaptcha_public_key' ] != '' ) ? 'success">' . $_DVWA[ 'recaptcha_public_key' ] : 'failure">Missing' ) . '</span>';

$DVWAUploadsWrite = 'Writable folder ' . $PHPUploadPath . ': <span class="' . ( is_writable( $PHPUploadPath ) ? 'success">Yes' : 'failure">No' ) . '</span>';                                     // File Upload
$bakWritable = 'Writable folder ' . $PHPCONFIGPath . ': <span class="' . ( is_writable( $PHPCONFIGPath ) ? 'success">Yes' : 'failure">No' ) . '</span>';   // config.php.bak check                                  // File Upload

$DVWAOS           = 'Operating system: <em>' . ( strtoupper( substr (PHP_OS, 0, 3)) === 'WIN' ? 'Windows' : '*nix' ) . '</em>';
$SERVER_NAME      = 'Web Server SERVER_NAME: <em>' . $_SERVER[ 'SERVER_NAME' ] . '</em>';                                                                                                          // CSRF

$MYSQL_USER       = 'Database username: <em>' . $_DVWA[ 'db_user' ] . '</em>';
$MYSQL_PASS       = 'Database password: <em>' . ( ($_DVWA[ 'db_password' ] != "" ) ? '******' : '*blank*' ) . '</em>';
$MYSQL_DB         = 'Database database: <em>' . $_DVWA[ 'db_database' ] . '</em>';
$MYSQL_SERVER     = 'Database host: <em>' . $_DVWA[ 'db_server' ] . '</em>';
$MYSQL_PORT       = 'Database port: <em>' . $_DVWA[ 'db_port' ] . '</em>';
// -- END (Setup Functions)

?>
