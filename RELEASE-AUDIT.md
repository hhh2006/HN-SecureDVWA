# HN SecureDVWA — Release Audit

**Release:** 3.11.0
**Audit date:** 2026-09-05

## Result

The packaged source passed the repository's automated security regression, structure, and final-release checks after release cleanup.

| Check | Result |
|---|---|
| PHP syntax audit | PASS |
| Security regression | PASS — 18/18 |
| Project structure | PASS |
| Final release check | PASS |
| Backup/temp artifact scan | PASS |
| Configuration template present | PASS |
| Docker source-build configuration | PASS — static review |
| Docker runtime integration | NOT EXECUTED in this audit environment |
| Browser/XAMPP runtime integration | NOT EXECUTED in this audit environment |

## Release cleanup performed

- Removed `config/config.inc.php.bak` from the distributable source.
- Removed the local SQLite test database `database/sqli.db` from the distributable source; runtime/generated databases remain ignored by Git.
- Added a clean `config/config.inc.php.dist` template based on environment variables.
- Updated the active configuration to use environment-driven database settings with safe local defaults.
- Updated Docker Compose to build the checked-out source locally and wait for a healthy MariaDB service.
- Added a repository `.gitignore` for local configuration, generated databases, uploads, logs, editor state, and archives.
- Added `CONTRIBUTING.md` with reproducible verification requirements.
- Updated the main README with installation, Docker, verification, support boundaries, attribution, and responsible-use information.
- Corrected the project-structure test expectations to match the current Security Center implementation.

## Important verification boundary

No claim is made here that a browser, Apache, MySQL/MariaDB, XAMPP, or Docker runtime was executed during this packaging audit. Those environments require execution on the target host. The automated source checks are intentionally separated from runtime verification.
