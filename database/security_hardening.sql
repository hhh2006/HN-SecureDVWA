-- SecureDVWA security telemetry migration (non-destructive)
CREATE TABLE IF NOT EXISTS security_events (
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
    INDEX idx_security_path (request_path),
    INDEX idx_security_operator (operator)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS security_operations (
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
    INDEX idx_operation_target (target),
    INDEX idx_operation_operator (operator)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
