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

- Shortcode names and option names (`wavelog_url`, `wavelog_api_key`, `wavelog_station_id`, `wavelog_cache_minutes`) are public interface — don't rename them.

## Releases

- Bump the `Version:` in the plugin header with every change (semantic versioning).
- Add an entry to the changelog in `README.md` for every version.
