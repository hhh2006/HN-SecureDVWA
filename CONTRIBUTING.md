# Contributing

HN SecureDVWA is an educational web-application security lab. Contributions are welcome when they improve correctness, reproducibility, defensive security, accessibility, or documentation without turning the project into an unverified collection of claims.

## Before submitting changes

1. Keep changes scoped and explain the security or engineering reason.
2. Do not add real credentials, API keys, session tokens, personal data, or production secrets.
3. Test PHP syntax for changed PHP files.
4. Run `php tests/security_regression.php`.
5. Run `php tests/project_structure_check.php` and `php tests/final_release_check.php`.
6. If Docker-related files change, run the Docker lab and document the observed result.
7. Update documentation when behavior, installation, or verification status changes.

## Pull requests

Describe what changed, how it was tested, and any limitations. Security-sensitive changes should include a reproducible local-lab verification path.
