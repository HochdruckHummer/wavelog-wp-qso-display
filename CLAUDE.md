# CLAUDE.md

WordPress plugin that shows QSO statistics from a Wavelog instance via shortcodes. The whole plugin lives in `wavelog-data-display.php`.

## Requirements

- Must run on PHP 8.2, 8.3 and 8.4 and on the current WordPress release. Avoid deprecated PHP features and WordPress functions.
- The CI workflow (`.github/workflows/php-lint.yml`) runs `php -l` on all three PHP versions; keep it green.

## Security standards (WordPress)

- **Escaping:** escape all output late with the matching function (`esc_html`, `esc_attr`, `esc_url`, ...).
- **Sanitizing:** sanitize all input; settings use a `sanitize_callback` in `register_setting`.
- **Nonces:** every form or action that changes data must verify a nonce (the settings page uses `settings_fields()` for this).
- **Capabilities:** check capabilities (`manage_options` for admin pages and for showing technical error messages).
- Never expose API keys, WP_Error texts or other internals to visitors.

## Compatibility

- Shortcode names and option names (`wavelog_url`, `wavelog_api_key`, `wavelog_station_id`, `wavelog_cache_minutes`) are public interface — don't rename them. The same applies to the main file name `wavelog-data-display.php`: renaming it deactivates the plugin on existing sites.

## Releases

- Bump the `Version:` in the plugin header with every change (semantic versioning), and set `Stable tag:` in `readme.txt` to the same version.
- Add a changelog entry for every version in both files: `### <version>` under `## Changelog` in `README.md` (used for the GitHub release notes) and `= <version> =` under `== Changelog ==` in `readme.txt` (shown on WordPress.org). The `Checks` workflow fails if one of them is missing.
- Keep `Tested up to:` in `readme.txt` at the current WordPress major version.
- Releases are automatic: when `main` contains a version without a release, `.github/workflows/release.yml` creates the tag `v<version>`, a GitHub release with the changelog entry as notes and the plugin ZIP. With the secrets `SVN_USERNAME`/`SVN_PASSWORD` set, it also deploys every version that is not yet under `tags/<version>` in the WordPress.org SVN repository (https://plugins.svn.wordpress.org/qso-statistics-for-wavelog). A missed deployment is caught up by running the Release workflow manually. Don't create releases or SVN commits by hand.
- Plugin slug and text domain are `qso-statistics-for-wavelog`. All user-facing strings use the translation functions with this text domain.
- Icon, banner and screenshots for the WordPress.org page live in `.wordpress-org/` (file names as required by WordPress.org, screenshot captions under `== Screenshots ==` in `readme.txt`). They are uploaded with the next version deployment.
- Files that go into the plugin ZIP are listed in the "Build plugin ZIP" step of `release.yml`; add new plugin files there.
