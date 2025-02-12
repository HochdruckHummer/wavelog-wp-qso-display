# wavelog-wp-qso-display

Made by Daniel Beckemeier, DO8YDP

Retrieves Wavelog data via API and displays the QSO numbers per operating mode via shortcodes in Wordpress. The Wavelog URL, the API key and the station ID can be configured in the admin area of Wordpress. Example site: https://do8ydp.de:

<img width="1158" alt="Bildschirmfoto 2025-02-12 um 15 19 52" src="https://github.com/user-attachments/assets/a9fdffc9-c294-407e-bda1-6bd79f546676" />

Attention: This plugin uses static 10 minutes caching time to reduce API calls to the Wavelog instance. So allthough this is nearly real-time, there can be a delay of up to ten minutes.


Installation:

1. Just install the Zip-file as a plugin in your wordpress instance and activate the plugin.
2. Go to Settings-Menu in Wordpress and click on "Wavelog":

<img width="360" alt="Bildschirmfoto 2025-02-12 um 11 16 34" src="https://github.com/user-attachments/assets/857d4aba-90ae-4ee2-be52-bb4476d6919a" />


3. Enter your Wavelog URL, API key (please only use a read-only key) and enter your station ID, which should be "1" in most cases:

<img width="830" alt="Bildschirmfoto 2025-02-12 um 11 17 18" src="https://github.com/user-attachments/assets/b878078f-66f6-4e74-ac0a-252a20644377" />


4. Use the following shortcodes to show QSO numbers anywhere on your site:

[wavelog_totalqso]
[wavelog_ssbqso]
[wavelog_fmqso]
[wavelog_ft8ft4qso]
[wavelog_digiqso]
[wavelog_cwqso]
[wavelog_rttyqso]
[wavelog_pskqso]
[wavelog_js8qso]



Tips and hints:

You can use TablePress to create nice looking tables to use the shortcodes in, like in this example:
<img width="1004" alt="Bildschirmfoto 2025-02-12 um 10 45 54" src="https://github.com/user-attachments/assets/df1ac1e0-c673-48e1-9b4b-ecc98a380ae1" />


You can also use a block, with groups inside to achieve something like this:
<img width="1158" alt="Bildschirmfoto 2025-02-12 um 15 19 52" src="https://github.com/user-attachments/assets/a9fdffc9-c294-407e-bda1-6bd79f546676" />





