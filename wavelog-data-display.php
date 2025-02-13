<?php
/*
Plugin Name: Wavelog QSO statistic Display per mode
Description: Retrieves Wavelog data via API and displays the QSO numbers per QSO type via shortcodes. The Wavelog URL, the API key and the station ID can be configured in the admin area.
Version: 1.0
Author: Daniel Beckemeier, DO8YDP
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Prevent direct call
}

/* ====================================
   ADMIN-Area: Settings
==================================== */

// Registration of settings
function wavelog_register_settings() {
    register_setting( 'wavelog_settings_group', 'wavelog_url' );
    register_setting( 'wavelog_settings_group', 'wavelog_api_key' );
    register_setting( 'wavelog_settings_group', 'wavelog_station_id' );
    register_setting( 'wavelog_settings_group', 'wavelog_cache_minutes' );
}
add_action( 'admin_init', 'wavelog_register_settings' );

// Adding setting page to menu "Settings"
function wavelog_add_admin_menu() {
    add_options_page(
        'Wavelog QSO Display Settings',  // Page title
        'Wavelog',                   // Menuetitle
        'manage_options',            // Authorization
        'wavelog-settings',          // Menu-Slug
        'wavelog_settings_page'      // Callback-Function
    );
}
add_action( 'admin_menu', 'wavelog_add_admin_menu' );

// HTML of the settings page
function wavelog_settings_page() {
    ?>
    <div class="wrap">
        <h1>Wavelog QSO Display Settings</h1>
        <form method="post" action="options.php">
            <?php settings_fields( 'wavelog_settings_group' ); ?>
            <?php do_settings_sections( 'wavelog_settings_group' ); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Wavelog URL</th>
                    <td>
                        <input type="text" name="wavelog_url" value="<?php echo esc_attr( get_option( 'wavelog_url' ) ); ?>" style="width: 400px;" />
                        <br /><small>Example: https://your-wavelog-instance.de</small>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">API Key</th>
                    <td>
                        <input type="text" name="wavelog_api_key" value="<?php echo esc_attr( get_option( 'wavelog_api_key' ) ); ?>" style="width: 400px;" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Station ID</th>
                    <td>
                        <input type="text" name="wavelog_station_id" value="<?php echo esc_attr( get_option( 'wavelog_station_id' ) ); ?>" style="width: 200px;" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Cache Duration (minutes)</th>
                    <td>
                        <input type="number" name="wavelog_cache_minutes" min="1" max="1440"
                        value="<?php echo esc_attr( get_option( 'wavelog_cache_minutes', '10' ) ); ?>"
                        style="width: 80px;" />
                          <br /><small>Enter a value from 1 to 1440 minutes (default: 10 minutes).</small>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
        <h2>Available Shortcodes</h2>
        <p>You can use the following shortcodes to display QSO statistics on your pages or posts:</p>
        <ul>
            <li><strong>[wavelog_totalqso]</strong> - Displays the total number of all QSOs.</li>
            <li><strong>[wavelog_ssbqso]</strong> - Displays the total number of SSB QSOs.</li>
            <li><strong>[wavelog_fmqso]</strong> - Displays the total number of FM QSOs.</li>
            <li><strong>[wavelog_amqso]</strong> - Displays the total number of AM QSOs.</li>
            <li><strong>[wavelog_rttyqso]</strong> - Displays the total number of RTTY QSOs.</li>
            <li><strong>[wavelog_ft4qso]</strong> - Displays the total number of FT4 QSOs.</li>
            <li><strong>[wavelog_ft8qso]</strong> - Displays the total number of FT8 QSOs.</li>
            <li><strong>[wavelog_ft8ft4qso]</strong> - Displays the total number of FT8 and FT4 QSOs summed up.</li>
            <li><strong>[wavelog_pskqso]</strong> - Displays the total number of PSK QSOs.</li>
            <li><strong>[wavelog_cwqso]</strong> - Displays the total number of CW QSOs.</li>
            <li><strong>[wavelog_js8qso]</strong> - Displays the total number of JS8 QSOs.</li>
            <li><strong>[wavelog_digiqso]</strong> - Displays the total number of Digimode QSOs (FT8, FT4, PSK, RTTY, JS8, etc.).</li>
            <li><strong>[wavelog_totalqso_year]</strong> - Displays the total number of QSOs for the current year.</li>

        </ul>
    </div>
    <?php
}

/* ====================================
   FRONTEND: API-Call & Shortcodes
==================================== */

/**
 * Retrieves the Wavelog data via API, evaluates the ADIF data and caches the result for 10 minutes.
 *
 * @return array|string Array with the determined QSO numbers or an error message as a string.
 */
function wavelog_get_data() {
    // Retrieve settings from the database
    $wavelog_url    = get_option( 'wavelog_url' );
    $api_key        = get_option( 'wavelog_api_key' );
    $station_id     = get_option( 'wavelog_station_id' );
    $cache_minutes  = get_option( 'wavelog_cache_minutes', 10 ); // default to 10 if not set

    if ( empty( $wavelog_url ) || empty( $api_key ) || empty( $station_id ) ) {
        return 'Please configure the Wavelog settings in the admin area.';
    }

    // Validate the cache minutes (ensure it's between 1 and 1440)
   $cache_minutes = intval( $cache_minutes );
   if ( $cache_minutes < 1 || $cache_minutes > 1440 ) {
       $cache_minutes = 10;
   }

   // Results for caching
    $transient_key = 'wavelog_data_cache';
    $cached_data = get_transient( $transient_key );
    if ( $cached_data !== false ) {
        return $cached_data;
    }



    // Request body structure
    $body = array(
        'key'         => $api_key,
        'station_id'  => $station_id,
        'fetchfromid' => 0
    );

    $args = array(
        'method'  => 'POST',
        'body'    => json_encode( $body ),
        'headers' => array(
            'Content-Type' => 'application/json',
            'Accept'       => 'application/json'
        ),
        'timeout' => 15
    );

    // API call (pay attention to the trailing slash, if required)
    $response = wp_remote_post( trailingslashit( $wavelog_url ) . 'index.php/api/get_wp_stats', $args );

    if ( is_wp_error( $response ) ) {
        return "Error in the API query: " . $response->get_error_message();
    }

    $response_body = wp_remote_retrieve_body( $response );
    $data = json_decode( $response_body, true );

    if ($data['status'] != "successful" ) {
        return "Error: Something went wrong with fetching data.";
    }

    $adif_data = $data['adif'];

    // Total number of all QSOs
    $total_qso = $data['statistics']['totalalltime'][0]['count'];

    // This years number of QSOs
    $total_qso_year = $data['statistics']['totalthisyear'][0]['count'];

    $digi_modes = array("FT8", "FT4", "PSK", "RTTY", "JS8", "JT65", "JT9", "OLIVIA", "CONTESTI", "ROS");
    
    $digi_qso = 0;
    $ft8ft4_qso = 0;
    $ssb_qso = 0;
    $cw_qso = 0;
    $fm_qso = 0;
    $rtty_qso = 0;
    $js8_qso = 0;
    $psk_qso = 0;   
    
    if (isset($data['statistics']['totalgroupedmodes'])) {
        foreach ($data  ['statistics']['totalgroupedmodes'] as $mode) {
            $col_mode = $mode['col_mode'];
            $col_submode = $mode['col_submode'] ?? '';
    
            if ($col_mode === 'SSB') {
                $ssb_qso = $mode['count'];
            } elseif ($col_mode === 'CW') {
                $cw_qso = $mode['count'];
            } elseif ($col_mode === 'FM') {
                $fm_qso = $mode['count'];
            } elseif ($col_mode === 'RTTY') {
                $rtty_qso = $mode['count'];
            }
    
            // FT8/FT4 combined
            if ($col_mode === 'FT8' || $col_submode === 'FT4') {
                $ft8ft4_qso += $mode['count'];
            }
    
            // JS8 mode
            if (stripos($col_mode, 'JS8') === 0) {
                $js8_qso = $mode['count'];
            }

            // PSK mode
            if (stripos($col_mode, 'PSK') === 0) {
                $psk_qso = $mode['count'];
            }
            
            // Digital modes total
            foreach ($digi_modes as $digi_mode) {
                if (
                    stripos($col_mode, $digi_mode) === 0 || 
                    stripos($col_submode, $digi_mode) === 0
                ) {
                    $digi_qso += $mode['count'];
                    break; // Count once per record
                }
            }
        }
    }

    $result = array(
        'total_qso_year'=> $total_qso_year,
        'total_qso'     => $total_qso,
        'ssb_qso'       => $ssb_qso,
        'fm_qso'        => $fm_qso,
        'rtty_qso'      => $rtty_qso,
        'ft8ft4_qso'    => $ft8ft4_qso,
        'psk_qso'       => $psk_qso,
        'cw_qso'        => $cw_qso,
        'js8_qso'       => $js8_qso,
        'digi_qso'      => $digi_qso,
    );

    // Cache result for 10 minutes
    set_transient( $transient_key, $result, $cache_minutes * MINUTE_IN_SECONDS );

    return $result; 
}

/* ============================
   Shortcode-Functions
============================ */

/**
 * Returns the total number of all QSOs.
 * Shortcode: [wavelog_totalqso]
 */
function wavelog_totalqso_shortcode() {
    $data = wavelog_get_data();
    if ( is_string( $data ) ) {
        return esc_html( $data );
    }
    return intval( $data['total_qso'] );
}
add_shortcode( 'wavelog_totalqso', 'wavelog_totalqso_shortcode' );

/**
 * Returns the total number of SSB-QSOs.
 * Shortcode: [wavelog_ssbqso]
 */
function wavelog_ssbqso_shortcode() {
    $data = wavelog_get_data();
    if ( is_string( $data ) ) {
        return esc_html( $data );
    }
    return intval( $data['ssb_qso'] );
}
add_shortcode( 'wavelog_ssbqso', 'wavelog_ssbqso_shortcode' );

/**
 * Returns the total number of FM-QSO.
 * Shortcode: [wavelog_fmqso]
 */
function wavelog_fmqso_shortcode() {
    $data = wavelog_get_data();
    if ( is_string( $data ) ) {
        return esc_html( $data );
    }
    return intval( $data['fm_qso'] );
}
add_shortcode( 'wavelog_fmqso', 'wavelog_fmqso_shortcode' );

/**
 * Returns the total number of AM-QSO.
 * Shortcode: [wavelog_amqso]
 */
function wavelog_amqso_shortcode() {
    $data = wavelog_get_data();
    if ( is_string( $data ) ) {
        return esc_html( $data );
    }
    return intval( $data['am_qso'] );
}
add_shortcode( 'wavelog_amqso', 'wavelog_amqso_shortcode' );

/**
 * Returns the total number of RTTY-QSOs zurück.
 * Shortcode: [wavelog_rttyqso]
 */
function wavelog_rttyqso_shortcode() {
    $data = wavelog_get_data();
    if ( is_string( $data ) ) {
        return esc_html( $data );
    }
    return intval( $data['rtty_qso'] );
}
add_shortcode( 'wavelog_rttyqso', 'wavelog_rttyqso_shortcode' );

/**
 * Returns the total number of FT4-QSOs zurück.
 * Shortcode: [wavelog_ft4qso]
 */
function wavelog_ft4qso_shortcode() {
    $data = wavelog_get_data();
    if ( is_string( $data ) ) {
        return esc_html( $data );
    }
    return intval( $data['ft4_qso'] );
}
add_shortcode( 'wavelog_ft4qso', 'wavelog_ft4qso_shortcode' );

/**
 * Returns the total number of FT8-QSOs zurück.
 * Shortcode: [wavelog_ft8qso]
 */
function wavelog_ft8qso_shortcode() {
    $data = wavelog_get_data();
    if ( is_string( $data ) ) {
        return esc_html( $data );
    }
    return intval( $data['ft8_qso'] );
}
add_shortcode( 'wavelog_ft8qso', 'wavelog_ft8qso_shortcode' );

/**
 * Returns the total number of FT8 and FT4 QSOs summed up.
 * Shortcode: [wavelog_ft8ft4qso]
 */
function wavelog_ft8ft4qso_shortcode() {
    $data = wavelog_get_data();
    if ( is_string( $data ) ) {
        return esc_html( $data );
    }
    return intval( $data['ft8ft4_qso'] );
}
add_shortcode( 'wavelog_ft8ft4qso', 'wavelog_ft8ft4qso_shortcode' );

/**
 * GReturns the total number of PSK-QSOs.
 * Shortcode: [wavelog_pskqso]
 */
function wavelog_pskqso_shortcode() {
    $data = wavelog_get_data();
    if ( is_string( $data ) ) {
        return esc_html( $data );
    }
    return intval( $data['psk_qso'] );
}
add_shortcode( 'wavelog_pskqso', 'wavelog_pskqso_shortcode' );

/**
 * GReturns the total number of CW-QSOs.
 * Shortcode: [wavelog_cwqso]
 */
function wavelog_cwqso_shortcode() {
    $data = wavelog_get_data();
    if ( is_string( $data ) ) {
        return esc_html( $data );
    }
    return intval( $data['cw_qso'] );
}
add_shortcode( 'wavelog_cwqso', 'wavelog_cwqso_shortcode' );

/**
 * Returns the total number of JS8-QSOs.
 * Shortcode: [wavelog_js8qso]
 */
function wavelog_js8qso_shortcode() {
    $data = wavelog_get_data();
    if ( is_string( $data ) ) {
        return esc_html( $data );
    }
    return intval( $data['js8_qso'] );
}
add_shortcode( 'wavelog_js8qso', 'wavelog_js8qso_shortcode' );


/**
 * Returns the total number of all Digimode-QSOs.
 * Shortcode: [wavelog_digiqso]
 */
function wavelog_digiqso_shortcode() {
    $data = wavelog_get_data();
    if ( is_string( $data ) ) {
        return esc_html( $data );
    }
    return intval( $data['digi_qso'] );
}
add_shortcode( 'wavelog_digiqso', 'wavelog_digiqso_shortcode' );

/**
 * Returns the total number of QSOs for the current year
 * Shortcode: [wavelog_totalqso_year]
 */
function wavelog_totalqso_year_shortcode() {
    $data = wavelog_get_data();
    if ( is_string( $data ) ) {
        return esc_html( $data );
    }
    return intval( $data['total_qso_year'] );
}
add_shortcode( 'wavelog_totalqso_year', 'wavelog_totalqso_year_shortcode' );
