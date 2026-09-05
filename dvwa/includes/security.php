<?php
/* SecureDVWA security telemetry and input-detection helpers. */

function dvwaSecurityEnsureSchema() {
    static $ready = false;
    if ($ready) return true;
    if (!isset($GLOBALS["___mysqli_ston"]) || !is_object($GLOBALS["___mysqli_ston"])) return false;

    $sql = "CREATE TABLE IF NOT EXISTS security_events (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        event_time DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        attack_type VARCHAR(60) NOT NULL,
        target VARCHAR(120) NOT NULL,
        source_ip VARCHAR(45) NULL,
        result VARCHAR(20) NOT NULL,
        reason VARCHAR(255) NULL,
        request_method VARCHAR(10) NULL,
        request_path VARCHAR(255) NULL,
        input_preview VARCHAR(255) NULL,
        input_hash CHAR(64) NULL,
        operator VARCHAR(60) NULL,
        PRIMARY KEY (id),
        INDEX idx_security_time (event_time),
        INDEX idx_security_type (attack_type),
        INDEX idx_security_result (result),
        INDEX idx_security_target (target),
        INDEX idx_security_path (request_path),
        INDEX idx_security_operator (operator)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    try {
        if (!mysqli_query($GLOBALS["___mysqli_ston"], $sql)) return false;
        // Recover events from a legacy incorrectly localized table name if it exists.
        $legacy = "security_" . "أحداث";
        $legacyCheck = mysqli_query($GLOBALS["___mysqli_ston"], "SHOW TABLES LIKE '" . $legacy . "'");
        if ($legacyCheck && mysqli_num_rows($legacyCheck) > 0) {
            @mysqli_query($GLOBALS["___mysqli_ston"], "INSERT IGNORE INTO security_events (id,event_time,attack_type,target,source_ip,result,reason) SELECT id,event_time,attack_type,target,source_ip,CASE WHEN result='تم المنع' THEN 'Blocked' WHEN result='نجحت' THEN 'Successful' ELSE result END,reason FROM `" . $legacy . "`");
        }
    } catch (Throwable $e) { return false; }
    $opSql = "CREATE TABLE IF NOT EXISTS security_operations (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        event_time DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        operation_type VARCHAR(60) NOT NULL,
        target VARCHAR(120) NOT NULL,
        source_ip VARCHAR(45) NULL,
        result VARCHAR(20) NOT NULL DEFAULT 'Successful',
        details VARCHAR(255) NULL,
        operator VARCHAR(60) NULL,
        PRIMARY KEY (id),
        INDEX idx_operation_time (event_time),
        INDEX idx_operation_type (operation_type),
        INDEX idx_operation_operator (operator)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    try { if (!mysqli_query($GLOBALS["___mysqli_ston"], $opSql)) return false; } catch (Throwable $e) { return false; }

    // Backward-compatible migrations for existing lab databases.
    // IMPORTANT: CREATE TABLE above already contains these columns for new installs.
    // Only ALTER a table when the column is genuinely missing, so existing databases
    // do not fail with "Duplicate column name" under mysqli exception mode.
    $ensureColumn = function ($table, $column, $definition) {
        $db = $GLOBALS["___mysqli_ston"];
        $table = (string)$table;
        $column = (string)$column;
        try {
            $stmt = mysqli_prepare($db,
                "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?");
            if (!$stmt) return false;
            mysqli_stmt_bind_param($stmt, 'ss', $table, $column);
            if (!mysqli_stmt_execute($stmt)) { mysqli_stmt_close($stmt); return false; }
            mysqli_stmt_bind_result($stmt, $count);
            mysqli_stmt_fetch($stmt);
            mysqli_stmt_close($stmt);
            if ((int)$count > 0) return true;
            return mysqli_query($db, "ALTER TABLE `" . str_replace('`', '``', $table) . "` ADD COLUMN `" . str_replace('`', '``', $column) . "` " . $definition) !== false;
        } catch (Throwable $e) {
            return false;
        }
    };

    $migrations = [
        ['security_events', 'request_method', 'VARCHAR(10) NULL'],
        ['security_events', 'request_path', 'VARCHAR(255) NULL'],
        ['security_events', 'input_preview', 'VARCHAR(255) NULL'],
        ['security_events', 'input_hash', 'CHAR(64) NULL'],
        ['security_events', 'operator', 'VARCHAR(60) NULL'],
        ['security_operations', 'operator', 'VARCHAR(60) NULL'],
    ];
    foreach ($migrations as $migration) {
        if (!$ensureColumn($migration[0], $migration[1], $migration[2])) return false;
    }
    $ready = true;
    return true;
}

function dvwaSecurityOperation($operationType, $target, $result = 'Successful', $details = null) {
    if (!dvwaSecurityEnsureSchema()) return false;
    $operationType = substr((string)$operationType, 0, 60);
    $target = substr(dvwaSecurityRedact($target), 0, 120);
    $result = substr((string)$result, 0, 20);
    $details = is_null($details) ? null : dvwaSecurityRedact($details);
    $ip = dvwaSecurityClientIp();
    $operator = function_exists('dvwaCurrentUser') ? substr((string)dvwaCurrentUser(), 0, 60) : null;
    $stmt = mysqli_prepare($GLOBALS['___mysqli_ston'],
        "INSERT INTO security_operations (operation_type,target,source_ip,result,details,operator) VALUES (?,?,?,?,?,?)");
    if (!$stmt) return false;
    mysqli_stmt_bind_param($stmt, 'ssssss', $operationType, $target, $ip, $result, $details, $operator);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function dvwaSecurityClientIp() {
    // Local lab: use REMOTE_ADDR and do not trust spoofable forwarding headers.
    return isset($_SERVER['REMOTE_ADDR']) ? substr($_SERVER['REMOTE_ADDR'], 0, 45) : null;
}

function dvwaSecurityRedact($value) {
    $v = (string)$value;
    $v = preg_replace('/(?i)(password|passwd|token|secret|authorization|cookie|session[_-]?id)\s*[=:]\s*[^\s,;]+/', '$1=[REDACTED]', $v);
    $v = preg_replace('/(?i)(bearer\s+)[A-Za-z0-9._~+\/-]+/', '$1[REDACTED]', $v);
    return substr($v, 0, 255);
}

function dvwaSecurityEvent($attackType, $target, $result = 'Blocked', $reason = null) {
    // Mark the current request as a security event so deferred normal telemetry
    // cannot double-count the same request as a functional operation.
    $GLOBALS['dvwa_security_event_recorded'] = true;
    if (!dvwaSecurityEnsureSchema()) return false;
    $attackType = substr((string)$attackType, 0, 60);
    $target = substr(dvwaSecurityRedact($target), 0, 120);
    $result = substr((string)$result, 0, 20);
    $reason = is_null($reason) ? null : dvwaSecurityRedact($reason);
    $ip = dvwaSecurityClientIp();
    $requestMethod = isset($_SERVER['REQUEST_METHOD']) ? substr((string)$_SERVER['REQUEST_METHOD'], 0, 10) : null;
    $requestPath = isset($_SERVER['REQUEST_URI']) ? substr((string)parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), 0, 255) : null;
    $inputPreview = dvwaSecurityRequestPreview();
    $inputHash = $inputPreview !== '' ? hash('sha256', $inputPreview) : null;
    $operator = function_exists('dvwaCurrentUser') ? substr((string)dvwaCurrentUser(), 0, 60) : null;

    $stmt = mysqli_prepare($GLOBALS["___mysqli_ston"],
        "INSERT INTO security_events (attack_type,target,source_ip,result,reason,request_method,request_path,input_preview,input_hash,operator) VALUES (?,?,?,?,?,?,?,?,?,?)");
    if (!$stmt) return false;
    mysqli_stmt_bind_param($stmt, 'ssssssssss', $attackType, $target, $ip, $result, $reason, $requestMethod, $requestPath, $inputPreview, $inputHash, $operator);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function dvwaSecurityRequestPreview() {
    $parts = [];
    foreach (['GET' => $_GET, 'POST' => $_POST] as $method => $data) {
        foreach ($data as $key => $value) {
            if (is_array($value)) $value = '[array]';
            $key = substr((string)$key, 0, 40);
            $value = dvwaSecurityRedact($value);
            if ($method === 'POST' && preg_match('/^(password|passwd|token|secret)$/i', (string)$key)) {
                $value = '[REDACTED]';
            }
            $parts[] = $method . '[' . $key . ']=' . $value;
            if (count($parts) >= 6) break;
        }
        if (count($parts) >= 6) break;
    }
    if (isset($_FILES) && is_array($_FILES)) {
        foreach ($_FILES as $key => $file) {
            if (!is_array($file)) continue;
            $name = isset($file['name']) ? basename((string)$file['name']) : '';
            if ($name !== '') {
                $parts[] = 'FILE[' . substr((string)$key, 0, 40) . ']=' . substr($name, 0, 80);
            }
            if (count($parts) >= 6) break;
        }
    }
    return substr(implode(' | ', $parts), 0, 255);
}

function dvwaSecurityContainsXss($value) {
    $v = (string)$value;
    return (bool)preg_match('/(?:<\s*script\b|javascript\s*:|on[a-z]+\s*=|<\s*(?:img|svg|iframe|object|embed|style|link)\b)/i', $v);
}

function dvwaSecurityContainsSqlInjection($value) {
    $v = (string)$value;
    return (bool)preg_match('/(?:\bOR\b|\bAND\b).*(?:=|\bLIKE\b)|(?:UNION\s+(?:ALL\s+)?SELECT)|(?:--|#|\/\*)|(?:\bSLEEP\s*\(|\bBENCHMARK\s*\()/i', $v);
}

function dvwaSecurityContainsCommandInjection($value) {
    $v = (string)$value;
    return (bool)preg_match('/(?:[;&|`$<>\r\n]|\b(?:whoami|ipconfig|ifconfig|cat|type|id|uname|powershell|cmd)\b)/i', $v);
}

function dvwaSecurityEscape($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
?>
