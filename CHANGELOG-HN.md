# HN SecureDVWA Changelog

## v1.0.0 — Hardened Security Lab
- Hardened seven selected DVWA vulnerability areas.
- Added centralized security event telemetry.
- Added Security Dashboard and Security Log.
- Added calculated prevention score and attack statistics.
- Added security response headers.
- Added local security regression tests.
- Added GitHub Actions syntax/security CI.
- Added threat model and security test matrix documentation.
- Added HN-branded cinematic security-center presentation.

## HN SecureDVWA V6 — Command Center Experience
- Rebuilt the application navigation as a premium command-center sidebar with named sections, module search, keyboard shortcut `/`, collapsible desktop mode, and mobile drawer mode.
- Added a global Command Palette (`Ctrl+K` / `Cmd+K`) generated from the existing native navigation links.
- Replaced navigation interception with non-blocking visual progress feedback; native navigation remains intact.
- Upgraded the header with local-lab status, command access, and mobile navigation control.
- Reworked the Security Dashboard into a richer monitoring surface with real-telemetry KPI cards, live clock, manual/auto refresh controls, 24-hour activity visualization, data-driven attack composition, attack distribution, recent event stream, quick log filters, and improved log operations.
- Dashboard composition graphics are generated from recorded `security_events` data; no synthetic security events are introduced.
- Preserved the existing DVWA vulnerability/security logic and local-lab scope.
- Added reduced-motion fallbacks for the new animation layer.

## V7 — Security Intelligence Upgrade
- Added explicit Security Control Score with weighted, auditable criteria.
- Added Operational Security Score combining verified controls (60%) and blocked-event ratio (40%) when telemetry exists.
- Added live threat level, 24h/1h activity, unique attack types, unique sources and peak-hour indicators.
- Added attack-composition donut visualization and enhanced animated telemetry panels.
- Added one-click refresh, optional 30-second auto-refresh and safe CSV export of the existing security log fields.
- Added additional response hardening headers and HTTPS-aware Secure cookie handling.
- Added telemetry redaction for common password/token/authorization/session secrets before persistence.

## V35 Dashboard Performance + Cinematic Polish
- Security dashboard live telemetry polling is now fixed at 30 seconds.
- New telemetry no longer triggers a full page reload; the live banner updates in place.
- Auto-refresh remains an explicit 30-second option and pauses while the tab is hidden.
- Added lightweight 3D/parallax dashboard presentation, premium lighting, depth, hover elevation, HUD sheen and animated scan effects.
- Respects `prefers-reduced-motion` and avoids continuous high-cost DOM effects.

## 3.7.0 — Ultra UI Experience
- Unified premium cinematic visual layer across navigation and content surfaces.
- Added color-coded module icons, collapsible navigation sections, reveal transitions, ambient particles, focus/motion controls, and interaction ripples.
- Preserved lightweight CSS/JS behavior and reduced-motion accessibility.
