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
            </table>
            <?php submit_button(); ?>
        </form>
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

    if ( empty( $wavelog_url ) || empty( $api_key ) || empty( $station_id ) ) {
        return 'Please configure the Wavelog settings in the admin area.';
    }

    // Results for 10 minutes caching
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
    $response = wp_remote_post( trailingslashit( $wavelog_url ) . 'index.php/api/get_contacts_adif', $args );

    if ( is_wp_error( $response ) ) {
        return "Error in the API query: " . $response->get_error_message();
    }

    $response_body = wp_remote_retrieve_body( $response );
    $data = json_decode( $response_body, true );

    if ( ! isset( $data['adif'] ) ) {
        return "Error: No ADIF data received.";
    }

    $adif_data = $data['adif'];

    // Total number of all QSOs (counting the <CALL: tag)
    preg_match_all( '/<CALL:/', $adif_data, $total_matches );
    $total_qso = count( $total_matches[0] );

    // Count SSB-QSOs
    preg_match_all( '/<MODE:\d+>SSB/', $adif_data, $ssb_matches );
    $ssb_qso = count( $ssb_matches[0] );

    // Count FM-QSOs
    preg_match_all( '/<MODE:\d+>FM/', $adif_data, $fm_matches );
    $fm_qso = count( $fm_matches[0] );

    // Count RTTY-QSOs
    preg_match_all( '/<MODE:\d+>RTTY/', $adif_data, $rtty_matches );
    $rtty_qso = count( $rtty_matches[0] );

    // Count FT8 and FT4 QSOs summed up
    preg_match_all( '/<MODE:\d+>FT8/', $adif_data, $ft8_matches );
    preg_match_all( '/<MODE:\d+>FT4/', $adif_data, $ft4_matches );
    $ft8ft4_qso = count( $ft8_matches[0] ) + count( $ft4_matches[0] );

    // Count PSK-QSOs
    preg_match_all( '/<MODE:\d+>PSK/', $adif_data, $psk_matches );
    $psk_qso = count( $psk_matches[0] );

    // Count CW-QSOs
    preg_match_all( '/<MODE:\d+>CW/', $adif_data, $cw_matches );
    $cw_qso = count( $cw_matches[0] );

    // Count JS8-QSOs
    preg_match_all( '/<MODE:\d+>JS8/', $adif_data, $js8_matches );
    $js8_qso = count( $js8_matches[0] );

    // Count all Digimode-QSOs (digital Modes: FT8, FT4, PSK, RTTY, JS8, JT65, JT9, OLIVIA, CONTESTI, ROS)
    $digi_modes = array( "FT8", "FT4", "PSK", "RTTY", "JS8", "JT65", "JT9", "OLIVIA", "CONTESTI", "ROS" );
    $digi_qso = 0;
    foreach ( $digi_modes as $mode ) {
        preg_match_all( '/<MODE:\d+>' . preg_quote( $mode, '/' ) . '/', $adif_data, $matches );
        $digi_qso += count( $matches[0] );
    }

    $result = array(
        'total_qso'   => $total_qso,
        'ssb_qso'     => $ssb_qso,
        'fm_qso'      => $fm_qso,
        'rtty_qso'    => $rtty_qso,
        'ft8ft4_qso'  => $ft8ft4_qso,
        'psk_qso'     => $psk_qso,
        'cw_qso'      => $cw_qso,
        'js8_qso'     => $js8_qso,
        'digi_qso'    => $digi_qso,
    );

    // Cache result for 10 minutes
    set_transient( $transient_key, $result, 10 * MINUTE_IN_SECONDS );

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
 * GReturns the total number of FT8 and FT4 QSOs summed up.
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
