# HN SecureDVWA — Security Test Matrix

## Scope
All security tests are intended for the local DVWA laboratory only.

| Control | Before | Hardened control | Verification |
|---|---|---|---|
| SQL Injection | Vulnerable query construction | Prepared statements + input validation | Regression + local manual retest |
| Blind SQL Injection | Vulnerable query construction | Prepared statements + input validation | Regression + local manual retest |
| Command Injection | Shell command concatenation | Strict IPv4 validation + `escapeshellarg()` | Regression + local manual retest |
| Reflected XSS | Raw request output | Context-appropriate HTML output encoding | Regression + local manual retest |
| DOM XSS | URL data written with `document.write` | Safe option creation / allow-listing | Regression + local manual retest |
| Stored XSS | Stored content later rendered as HTML | Input detection + output encoding | Regression + local manual retest |
| File Upload | Name-only move to executable uploads directory | Extension/MIME/size/image validation + random filename + execution deny rule | Regression + local manual retest |
| HTTP hardening | Minimal headers | `nosniff`, `SAMEORIGIN`, `no-referrer`, restricted permissions | Header inspection |
| Security telemetry | No central event model | `security_events` table + Security Log | Dashboard verification |

## Pass criteria
A test passes only when the malicious local-lab input is prevented and normal application functionality remains usable.

## Logging requirements
Security telemetry must not store passwords, full session identifiers, authentication tokens, or complete attack payloads.
