<?php

# HN SecureDVWA local configuration template.
# Copy this file to config.inc.php when installing manually.
# For Docker, values are supplied through environment variables.

$DBMS = getenv('DBMS') ?: 'MySQL';

$_DVWA = array();
$_DVWA['db_server']   = getenv('DB_SERVER') ?: '127.0.0.1';
$_DVWA['db_database'] = getenv('DB_DATABASE') ?: 'dvwa';
$_DVWA['db_user']     = getenv('DB_USER') ?: 'root';
$_DVWA['db_password'] = getenv('DB_PASSWORD') ?: '';
$_DVWA['db_port']     = getenv('DB_PORT') ?: '3306';

$_DVWA['recaptcha_public_key']  = getenv('RECAPTCHA_PUBLIC_KEY') ?: '';
$_DVWA['recaptcha_private_key'] = getenv('RECAPTCHA_PRIVATE_KEY') ?: '';

$_DVWA['default_security_level'] = getenv('DEFAULT_SECURITY_LEVEL') ?: 'impossible';
$_DVWA['default_locale'] = getenv('DEFAULT_LOCALE') ?: 'en';
$_DVWA['disable_authentication'] = getenv('DISABLE_AUTHENTICATION') ?: false;

define('MYSQL', 'mysql');
define('SQLITE', 'sqlite');

$_DVWA['SQLI_DB'] = getenv('SQLI_DB') ?: MYSQL;
$_DVWA['SQLITE_DB'] = getenv('SQLITE_DB') ?: 'sqli.db';

?>
