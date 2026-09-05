# HN SecureDVWA — Demo Runbook

## Demo objective
Show one complete security-engineering loop without relying on fake evidence.

## Sequence

1. Open the modified DVWA and authenticate.
2. Open **HN SecureDVWA Security Center**.
3. Show the current Security Score and event counters.
4. Open one selected vulnerability.
5. Run the corresponding local attack test against the local DVWA instance.
6. Show that the hardened implementation rejects/prevents the attack.
7. Return to the Security Center and show the matching event in the Security Log.
8. Open the relevant source file and explain the control used.
9. Run the local regression suite and show the PASS result.

## Best demonstration candidates

- SQL Injection: explain prepared statements.
- Command Injection: explain strict IPv4 validation and shell escaping.
- Stored XSS: explain write-path detection plus output encoding at the render boundary.
- File Upload: explain content validation, image re-encoding, random filename, and upload execution denial.

## Evidence discipline
Never claim that a browser/runtime test passed unless it was actually run in the local environment. Keep the before/after screenshots required by the course separate from automated code-level regression evidence.
