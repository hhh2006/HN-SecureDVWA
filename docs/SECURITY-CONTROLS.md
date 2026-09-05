# HN SecureDVWA — Security Controls

| Control | Implementation | Evidence |
|---|---|---|
| SQLi prevention | Prepared statements and input validation | `vulnerabilities/sqli/source/low.php` |
| Blind SQLi prevention | Prepared statements and input validation | `vulnerabilities/sqli_blind/source/low.php` |
| OS command injection prevention | Strict IPv4 validation and shell argument escaping | `vulnerabilities/exec/source/low.php` |
| Reflected XSS prevention | HTML output encoding and detection telemetry | `vulnerabilities/xss_r/source/low.php` |
| DOM XSS prevention | Allow-listed value rendering without `document.write` | `vulnerabilities/xss_d/index.php` |
| Stored XSS prevention | Detection at write path and output encoding at display | `vulnerabilities/xss_s/source/low.php`, `dvwa/includes/dvwaPage.inc.php` |
| File upload hardening | Extension/MIME/size/image validation, re-encoding, random filename | `vulnerabilities/upload/source/low.php` |
| Upload execution defense | Web-server rule denies script execution in uploads | `hackable/uploads/.htaccess` |
| Browser security | `nosniff`, `SAMEORIGIN`, `no-referrer`, restricted permissions | `dvwa/includes/dvwaPage.inc.php` |
| Security telemetry | Central `security_events` table and parameterized insert | `dvwa/includes/security.php` |

## Design principle
Controls are layered. Detection is not treated as a substitute for prevention: the application attempts to reject unsafe input, while telemetry records the security event for review.

## V7 additional hardening
- Response headers now include COOP, CORP, download policy and HTTPS-only HSTS when applicable.
- Session/security cookies set `Secure` automatically when HTTPS is detected.
- Telemetry values pass through a secret-redaction layer before persistence.
- Dashboard security scoring is explicitly weighted and auditable rather than a hard-coded cosmetic value.
