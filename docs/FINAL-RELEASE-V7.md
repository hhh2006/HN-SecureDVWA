# SecureDVWA V7 — Security Intelligence Release

## Security upgrades
- Security response hardening: `nosniff`, `SAMEORIGIN`, strict referrer policy, permissions policy, COOP, CORP, download policy, and HSTS when HTTPS is active.
- HTTPS-aware `Secure` cookie flag for the DVWA security-level cookie and session cookies.
- Security telemetry remains parameterized and centralised in `security_events`.
- Common secret-bearing values are redacted before security-event persistence.

## Security scoring model
The dashboard exposes two related values:

1. **Control Posture** — 100 points across eight explicit criteria:
   - Security response headers: 15
   - CSRF protection: 15
   - Session hardening: 15
   - Parameterized telemetry: 15
   - Input detection: 10
   - Output escaping: 10
   - Upload validation: 10
   - Sensitive-data minimization: 10
2. **Operational Security Score** — when recorded events exist: `60% Control Posture + 40% Blocked-event ratio`. With no telemetry, the score remains the verified control posture rather than inventing an event result.

This is a transparent laboratory metric, not a guarantee of immunity.

## Visual intelligence
- 24-hour security activity line chart.
- Attack composition donut.
- Attack distribution bars.
- Animated KPI counters.
- Threat-level indicator.
- Recent event stream.
- Live clock.
- Refresh and optional 30-second auto-refresh.
- Safe CSV export using only existing log fields.

## Validation
- PHP lint: 175/175 PASS
- JavaScript syntax: PASS
- Security regression: 18/18 PASS
- Project structure: PASS
- Final release check: PASS

Live XAMPP/MySQL/browser validation still must be performed on the user's local lab environment.
