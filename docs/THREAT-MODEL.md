# HN SecureDVWA — Threat Model

## Assets
- DVWA user account/session state
- Application data and database records
- Uploaded media
- Security event telemetry
- Source code and configuration

## Trust boundaries
`Browser -> Apache/PHP -> Application Logic -> Database / Filesystem`

## Threats addressed
- SQL query manipulation
- OS command injection
- Cross-site scripting in reflected, stored, and DOM contexts
- Unsafe file upload and executable file placement
- Excessive information exposure through security telemetry

## Security controls
1. Parameterized SQL queries.
2. Strict input validation where input has a narrow expected format.
3. Output encoding at HTML rendering boundaries.
4. DOM allow-listing / safe DOM construction.
5. File type, size, MIME, content, and filename controls.
6. Upload-directory execution restrictions.
7. Response security headers.
8. Security event logging without sensitive secrets.
9. Regression checks to detect accidental control removal.

## Residual risk
No local hardening layer can prove that a web application is mathematically impossible to exploit. The project reduces the selected attack surfaces and validates the implemented controls against the documented local test cases.
