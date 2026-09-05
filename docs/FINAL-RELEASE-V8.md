# SecureDVWA V8 — Security Intelligence Release

## Delivered
- Fixed desktop sidebar collapse/re-open with a dedicated reopen control.
- Fixed sidebar scroll behavior with a dedicated scroll region, stable scrollbar gutter, and overscroll containment.
- Preserved mobile navigation behavior and made collapse state desktop-only.
- Added Security Health Center with explicit control scoring.
- Added Threat Timeline with chronological telemetry stream.
- Added Risk Matrix using observed frequency + successful-event impact heuristic.
- Added Security Controls library with defense-in-depth presentation.
- Added Assurance Studio for honest before/after lab demonstration without fabricated retest claims.
- Added print-ready Security Report with browser "Print / Save PDF" flow.
- Added shared animated intelligence visuals: radar, signal sweep, orbit rings, reveal motion, hover feedback, and responsive layouts.
- Extended the security navigation to expose all intelligence modules.

## Scoring model
The health posture uses explicit installed-control checks. The dashboard continues to combine posture with observed blocked-event effectiveness when telemetry exists. Risk Matrix is a transparent heuristic, not CVSS.

## Validation
- PHP lint: 182/182 PASS
- JavaScript syntax: PASS
- CSS structural balance: PASS
- Security regression: 18/18 PASS
- Project structure: PASS
- Final release check: PASS

## Runtime note
Live XAMPP/MySQL browser validation still must be performed in the user's local laboratory environment. No live results are fabricated by this release.
