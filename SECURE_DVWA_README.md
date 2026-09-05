# HN SecureDVWA

**Web Application Security Hardening & Monitoring Lab**

HN SecureDVWA is a security-engineering layer built on the exact DVWA environment used in the course. The project preserves DVWA as the educational base while adding real defensive controls, telemetry, verification, and professional documentation.

## What is implemented
- SQL Injection hardening
- Blind SQL Injection hardening
- Command Injection hardening
- Reflected XSS hardening
- DOM XSS hardening
- Stored XSS hardening
- File Upload hardening
- Security Dashboard
- Security Log
- Attack statistics and 24-hour timeline
- Prevention score
- Recent-event alert
- Security headers
- Local regression test suite
- GitHub Actions security CI
- Threat model and test matrix

## Verification philosophy
The repository must distinguish between static verification and runtime verification. PHP syntax checks and local regression tests can be automated here; browser/database integration checks must still be run in the local DVWA environment before claiming a release is runtime-verified.

## Local testing
```text
php tests/security_regression.php
```

Or on Windows:
```text
scripts\security_test.bat
```

## Dashboard
Open the authenticated Security Dashboard from the DVWA navigation menu. Dashboard metrics are read from the `security_events` database table; no demo attack counts are hard-coded.

## Academic mapping
The project directly addresses the required workflow: original vulnerability analysis, practical local proof, source-code remediation, repeat verification, security logging, dashboard monitoring, and documentation.
