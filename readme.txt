=== QSO Statistics for Wavelog ===
Contributors: hochdruckhummer
Donate link: https://paypal.me/DanielBeckemeier
Tags: wavelog, ham radio, amateur radio, qso, shortcode
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 8.2
Stable tag: 2.4.1
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Shows QSO statistics from your Wavelog logbook on your website via shortcodes: total QSOs, QSOs this year and QSOs per mode.

== Description ==

QSO Statistics for Wavelog retrieves the QSO statistics of your own [Wavelog](https://github.com/wavelog/wavelog) instance and displays them anywhere on your site via shortcodes.

* Total number of QSOs and QSOs of the current year
* QSOs per mode: SSB, FM, AM, CW, RTTY, PSK, FT8, FT4, JS8 and all digital modes combined
* Multiple station IDs can be combined, totals are summed up
* Results are cached (1–1440 minutes, default 10) to keep the load on your Wavelog instance low
* Visitors never see technical error messages; administrators do

This plugin is not affiliated with or endorsed by the Wavelog project.

= Available shortcodes =

* `[wavelog_totalqso]` – total number of all QSOs
* `[wavelog_totalqso_year]` – total number of QSOs in the current year
* `[wavelog_ssbqso]` – SSB QSOs
* `[wavelog_fmqso]` – FM QSOs
* `[wavelog_amqso]` – AM QSOs
* `[wavelog_cwqso]` – CW QSOs
* `[wavelog_rttyqso]` – RTTY QSOs
* `[wavelog_pskqso]` – PSK QSOs (including variants like PSK31)
* `[wavelog_ft8qso]` – FT8 QSOs
* `[wavelog_ft4qso]` – FT4 QSOs
* `[wavelog_ft8ft4qso]` – FT8 and FT4 QSOs summed up
* `[wavelog_js8qso]` – JS8 QSOs
* `[wavelog_digiqso]` – all digital mode QSOs (FT8, FT4, PSK, RTTY, JS8, etc.)

= External services =

This plugin connects to the Wavelog instance you configure in the settings. Wavelog is a self-hosted logbook application for amateur radio, so this is usually your own server.

* **What is sent:** your Wavelog API key and the configured station ID(s), as a POST request to `<your Wavelog URL>/index.php/api/get_wp_stats`.
* **When:** whenever a page with one of the shortcodes is rendered and no cached result is available (at most once per configured cache duration; after an error at most once every 2 minutes).
* **What is received:** aggregated QSO counts. No data about your visitors is sent.

The terms and privacy policy of the Wavelog instance are those of its operator. Information about the Wavelog software: https://github.com/wavelog/wavelog

== Installation ==

1. Install the plugin via *Plugins → Add New* or upload the ZIP file, then activate it.
2. Go to *Settings → Wavelog*.
3. Enter your Wavelog URL (https only), an API key (please use a read-only key) and your station ID, which is "1" in most cases. Multiple station IDs can be separated by commas (e.g. 1,3,10).
4. Add one of the shortcodes to any page, post or block.

Requires Wavelog 2.0.1 or newer.

== Frequently Asked Questions ==

= Why don't the numbers update immediately? =

Results are cached to reduce API calls. Changes in Wavelog show up after the configured cache duration (default 10 minutes).

= The shortcode only shows "–" =

The data could not be retrieved. Log in as an administrator and open the page: you will see the actual error message instead of the dash.

= Which API key should I use? =

Create a read-only API key in Wavelog. The plugin only reads statistics.

== Changelog ==

= 2.4.1 =
* First release in the WordPress.org plugin directory. No functional changes.

= 2.4.0 =
* Prepared for the WordPress.org plugin directory: new name "QSO Statistics for Wavelog", readme.txt, complete plugin header.
* All texts in the admin area and error messages are translatable.
* Settings are removed from the database when the plugin is deleted.

= 2.3.0 =
* API errors are cached for 2 minutes; API timeout reduced from 15 to 5 seconds.
* Technical error messages are only shown to administrators; visitors see "–".
* All settings are sanitized on save (https-only URL, cache 1–1440 minutes, numeric station IDs).
* The API key field is now a password field.

= 2.2.1 =
* Multiple station IDs possible.
* The main plugin file no longer changes its name with every version.

== Upgrade Notice ==

= 2.4.1 =
Now available in the WordPress.org plugin directory. No functional changes.

= 2.4.0 =
Plugin renamed to "QSO Statistics for Wavelog". Shortcodes and settings are unchanged.
