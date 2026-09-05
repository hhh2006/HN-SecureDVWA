# SecureDVWA — V10.1 Cinematic Performance Pass

- Security Dashboard live telemetry polling is throttled to 30 seconds.
- Live telemetry does not reload the page; it updates the live status banner and notification path only.
- Optional `AUTO 30S` mode performs a full page refresh at a controlled 30-second interval when enabled by the user.
- Dashboard visuals received a cinematic depth pass: layered lighting, 3D perspective, glass panels, HUD scan effects, animated sheen, chart sweep, depth hover and reduced-motion support.
- Security behavior and existing dashboard routes are preserved.

Validation:
- `php -l security/dashboard.php` — PASS
- `php -l security/api_events.php` — PASS
- `node --check dvwa/js/hn_dashboard.js` — PASS
- `tests/project_structure_check.php` — PASS
- `tests/security_regression.php` — 18/18 PASS
- `tests/final_release_check.php` — PASS
