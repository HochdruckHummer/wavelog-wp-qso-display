# QSO Statistics for Wavelog

**Made by Daniel Beckemeier, DL8YDP**

WordPress plugin, formerly "Wavelog QSO statistic Display per mode". Not affiliated with the Wavelog project.

Retrieves Wavelog data via API and displays the QSO numbers per operating mode via shortcodes in Wordpress. The Wavelog URL, the API key and the station ID(s) can be configured in the admin area of Wordpress. Example site: https://dl8ydp.de:

<img width="1158" alt="Bildschirmfoto 2025-02-12 um 15 19 52" src="https://github.com/user-attachments/assets/a9fdffc9-c294-407e-bda1-6bd79f546676" />

**Attention:** This plugin uses static 10 minutes caching time to reduce API calls to the Wavelog instance. So allthough this is nearly real-time, there can be a delay of up to ten minutes.

## Compatibility

This script requires at least Wavelog version 2.0.1. to work, as there was an API implemented for this tool.


## Installation:

1. Just install the latest Zip-file as a plugin in your wordpress instance and activate the plugin.


2. Go to Settings-Menu in Wordpress and click on "Wavelog":

<img width="360" alt="Wordpress settings menu" src="https://github.com/user-attachments/assets/857d4aba-90ae-4ee2-be52-bb4476d6919a" />


3. Enter your Wavelog URL, API key (please only use a read-only key) and enter your station ID, which should be "1" in most cases. You can also add multiple station IDs, seaparated by comma (example: 1,3,10,15), these will be summep up then.

<img width="830" alt="Backend settings" src="https://github.com/user-attachments/assets/b878078f-66f6-4e74-ac0a-252a20644377" />


4. Use the following shortcodes to show QSO numbers anywhere on your site:

*[wavelog_totalqso]*
*[wavelog_ssbqso]*
*[wavelog_fmqso]*
*[wavelog_amqso]*
*[wavelog_ft8ft4qso]*
*[wavelog_ft8qso]*
*[wavelog_ft4qso]*
*[wavelog_digiqso]*
*[wavelog_cwqso]*
*[wavelog_rttyqso]*
*[wavelog_pskqso]*
*[wavelog_js8qso]*
*[wavelog_totalqso_year]*




## Tips and hints:

You can use TablePress to create nice looking tables to use the shortcodes in, like in this example:
<img width="1004" alt="Shortcodes inside a table" src="https://github.com/user-attachments/assets/df1ac1e0-c673-48e1-9b4b-ecc98a380ae1" />


You can also use a block, with groups inside to achieve something like this:
<img width="1158" alt="Shortcodes grouped inside a block" src="https://github.com/user-attachments/assets/a9fdffc9-c294-407e-bda1-6bd79f546676" />

## Changelog

### 2.4.1

- First release in the WordPress.org plugin directory: the plugin is now available at https://wordpress.org/plugins/qso-statistics-for-wavelog/ and can be updated directly from the WordPress admin.
- The release workflow deploys to WordPress.org independently of the GitHub release, so a missing deployment can be caught up by re-running the workflow.
- No functional changes.

### 2.4.0

- Prepared for the WordPress.org plugin directory: new name "QSO Statistics for Wavelog", `readme.txt`, complete plugin header (license, requirements, text domain).
- All texts in the admin area and error messages are translatable (text domain `qso-statistics-for-wavelog`).
- Settings are removed from the database when the plugin is deleted (`uninstall.php`).
- The release ZIP now unpacks into the folder `qso-statistics-for-wavelog`, the same as the WordPress.org version. If you installed an earlier ZIP, the plugin may show up twice after uploading this one: deactivate and delete the old copy, the settings are kept.
- Shortcodes, option names and the output are unchanged.

### 2.3.0

- API errors are cached for 2 minutes, so an unreachable Wavelog instance is not queried again by every shortcode. The API timeout was reduced from 15 to 5 seconds.
- Technical error messages (e.g. connection errors, station IDs) are only shown to logged-in administrators (`manage_options`). Visitors see "–" instead.
- Settings are now sanitized on save: the Wavelog URL must be a valid `https://` URL, the cache duration is limited to 1–1440 minutes, station IDs may only contain digits and commas, and the API key is sanitized as text.
- The API key field on the settings page is now a password field.
- Shortcodes, option names and the output with a working API are unchanged.
- Releases are now created automatically on GitHub, including an installable plugin ZIP.

##  Support the development

Did this application help you?
If you like, you can send me a beer via PayPal:

<a href="https://paypal.me/DanielBeckemeier" target="_blank" rel="nofollow sponsored noopener">
  <img
    width="300"
    height="50"
    alt="Donate a beer"
    src="https://github.com/user-attachments/assets/7c223db3-f267-447e-9207-4fe1cc72f829"
  />
</a>



