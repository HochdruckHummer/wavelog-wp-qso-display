# wavelog-wp-qso-display

Made by Daniel Beckemeier, DO8YDP

Retrieves Wavelog data via API and displays the QSO numbers per QSO type via shortcodes. The Wavelog URL, the API key and the station ID can be configured in the admin area. Example site: https://do8ydp.de

This plugin uses static 10 minutes caching time to reduce API calls to the Wavelog instance. So allthough this is nearly real-time, there can be a delay of up to ten minutes.


Installation:

1. Just install the Zip-file as a plugin in your wordpress instance and activate the plugin.
2. Go to Settings-Menu in Wordpress and click on "Wavelog"
3. Enter your Wavelog URL, API key (please only use a read-only key) and enter your station ID, which should be "1" in most cases.
4. Use the following shortcodes to show QSO numbers anywhere on your site

D


