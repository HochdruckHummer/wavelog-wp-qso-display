<?php
/*
Plugin Name: Wavelog QSO statistic Display per mode
Description: Retrieves Wavelog data via dedicated Wavelog-Wordpress-API and displays the QSO numbers per QSO type via shortcodes. The Wavelog URL, the API key and the station ID(s) can be configured in the admin area. Station IDs can be comma-separated to aggregate multiple stations.
Version: 2.2.1
Author: Daniel Beckemeier, DL8YDP, www.dl8ydp.de
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
    register_setting( 'wavelog_settings_group', 'wavelog_station_id' ); // comma-separated list supported
    register_setting( 'wavelog_settings_group', 'wavelog_cache_minutes' );
}
add_action( 'admin_init', 'wavelog_register_settings' );

// Adding setting page to menu "Settings"
function wavelog_add_admin_menu() {
    add_options_page(
        'Wavelog QSO Display Settings',  // Page title
        'Wavelog',                      // Menu title
        'manage_options',               // Capability
        'wavelog-settings',             // Menu slug
        'wavelog_settings_page'         // Callback
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
                    <th scope="row">Station ID(s)</th>
                    <td>
                        <input type="text" name="wavelog_station_id" value="<?php echo esc_attr( get_option( 'wavelog_station_id' ) ); ?>" style="width: 200px;" />
                        <br /><small>Single ID (e.g. 1) or comma-separated list (e.g. 1,2,5). Totals will be summed across all given station IDs.</small>
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
                <hr style="margin: 24px 0;">

        <div style="text-align:center; margin: 18px 0 6px;">
            <a href="https://paypal.me/DanielBeckemeier"
               target="_blank"
               rel="nofollow sponsored noopener"
               title="Donate a beer via PayPal">
                <img
                    src="<?php echo esc_url( plugins_url( 'assets/donate.png', __FILE__ ) ); ?>"
                    alt="Donate a beer"
                    width="250"
                    style="height:auto; max-width:100%;"
                />
            </a>
            <p style="margin: 10px 0 0; font-size: 13px; opacity: .85;">
                If this plugin helps you, feel free to donate a beer 🍺
            </p>
        </div>

    </div>
    <?php
}

/* ====================================
   FRONTEND: API-Call & Shortcodes
==================================== */

/**
 * Parse station IDs from option (supports comma-separated list)
 *
 * @return array Array of station ids as strings (trimmed), empty if none
 */
function wavelog_parse_station_ids() {
    $raw = (string) get_option( 'wavelog_station_id' );
    $ids = array_filter( array_map( 'trim', explode( ',', $raw ) ) );

    // Optionally: keep only digits (Station IDs are typically numeric)
    $clean = [];
    foreach ( $ids as $id ) {
        $id = preg_replace( '/[^0-9]/', '', $id );
        if ( $id !== '' ) {
            $clean[] = $id;
        }
    }

    // Remove duplicates while preserving order
    $clean = array_values( array_unique( $clean ) );

    return $clean;
}

/**
 * Retrieves the Wavelog data via API and caches the aggregated result.
 *
 * Supports multiple station IDs (comma-separated) - totals will be summed.
 *
 * @return array|string Array with the determined QSO numbers or an error message as a string.
 */
function wavelog_get_data() {
    // Retrieve settings from the database
    $wavelog_url   = trim( (string) get_option( 'wavelog_url' ) );
    $api_key       = trim( (string) get_option( 'wavelog_api_key' ) );
    $station_ids   = wavelog_parse_station_ids();
    $cache_minutes = get_option( 'wavelog_cache_minutes', 10 ); // default 10

    if ( empty( $wavelog_url ) || empty( $api_key ) || empty( $station_ids ) ) {
        return 'Please configure the Wavelog settings in the admin area (URL, API key, Station ID(s)).';
    }

    // Validate the cache minutes (ensure it's between 1 and 1440)
    $cache_minutes = intval( $cache_minutes );
    if ( $cache_minutes < 1 || $cache_minutes > 1440 ) {
        $cache_minutes = 10;
    }

    // Cache key must depend on URL + station list (otherwise wrong results when changing IDs)
    $transient_key = 'wavelog_data_cache_' . md5( $wavelog_url . '|' . implode( ',', $station_ids ) );
    $cached_data   = get_transient( $transient_key );
    if ( $cached_data !== false ) {
        return $cached_data;
    }

    // Aggregation buckets
    $total_qso      = 0;
    $total_qso_year = 0;

    // Modes aggregated across all stations:
    // e.g. 'SSB' => 123, 'FT8' => 456 ...
    $mode_counts = [];

    // Define which modes count as "digital total"
    $digi_modes = array( "FT8", "FT4", "PSK", "RTTY", "JS8", "JT65", "JT9", "OLIVIA", "CONTESTI", "ROS" );

    // Per-station API calls
    foreach ( $station_ids as $station_id ) {

        $body = array(
            'key'         => $api_key,
            'station_id'  => $station_id,
            'fetchfromid' => 0
        );

        $args = array(
            'method'  => 'POST',
            'body'    => wp_json_encode( $body ),
            'headers' => array(
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json'
            ),
            'timeout' => 15
        );

        $response = wp_remote_post( trailingslashit( $wavelog_url ) . 'index.php/api/get_wp_stats', $args );

        if ( is_wp_error( $response ) ) {
            return "Error in the API query for station_id {$station_id}: " . $response->get_error_message();
        }

        $response_body = wp_remote_retrieve_body( $response );
        $data = json_decode( $response_body, true );

        if ( ! is_array( $data ) || ( $data['status'] ?? '' ) !== 'successful' ) {
            return "Error: Something went wrong with fetching data for station_id {$station_id}.";
        }

        // Totals addieren
        $total_qso      += intval( $data['statistics']['totalalltime'][0]['count'] ?? 0 );
        $total_qso_year += intval( $data['statistics']['totalthisyear'][0]['count'] ?? 0 );

        // Modi addieren
        if ( ! empty( $data['statistics']['totalgroupedmodes'] ) && is_array( $data['statistics']['totalgroupedmodes'] ) ) {
            foreach ( $data['statistics']['totalgroupedmodes'] as $row ) {
                $col_mode    = strtoupper( trim( (string) ( $row['col_mode'] ?? '' ) ) );
                $col_submode = strtoupper( trim( (string) ( $row['col_submode'] ?? '' ) ) );
                $count       = intval( $row['count'] ?? 0 );

                if ( $count <= 0 ) {
                    continue;
                }

                // Prefer col_mode. If empty, fall back to submode.
                $key = $col_mode !== '' ? $col_mode : $col_submode;
                if ( $key === '' ) {
                    continue;
                }

                $mode_counts[ $key ] = ( $mode_counts[ $key ] ?? 0 ) + $count;
            }
        }
    }

    // Derive per-mode totals from aggregated dictionary
    $ssb_qso  = intval( $mode_counts['SSB'] ?? 0 );
    $cw_qso   = intval( $mode_counts['CW'] ?? 0 );
    $fm_qso   = intval( $mode_counts['FM'] ?? 0 );
    $am_qso   = intval( $mode_counts['AM'] ?? 0 );
    $rtty_qso = intval( $mode_counts['RTTY'] ?? 0 );

    $ft8_qso = intval( $mode_counts['FT8'] ?? 0 );
    $ft4_qso = intval( $mode_counts['FT4'] ?? 0 );
    $ft8ft4_qso = $ft8_qso + $ft4_qso;

    // Some installations might store JS8 as JS8 or JS8CALL; try both
    $js8_qso = intval( $mode_counts['JS8'] ?? 0 );
    if ( $js8_qso === 0 ) {
        $js8_qso = intval( $mode_counts['JS8CALL'] ?? 0 );
    }

    // PSK variants might appear as PSK31 etc.; keep base PSK plus sum variants starting with PSK
    $psk_qso = intval( $mode_counts['PSK'] ?? 0 );
    foreach ( $mode_counts as $m => $c ) {
        if ( $m !== 'PSK' && strpos( $m, 'PSK' ) === 0 ) {
            $psk_qso += intval( $c );
        }
    }

    // Digi total: sum configured digital modes; also include variants starting with each digi_mode
    $digi_qso = 0;
    foreach ( $digi_modes as $dm ) {
        $dm = strtoupper( $dm );
        foreach ( $mode_counts as $m => $c ) {
            if ( $m === $dm || strpos( $m, $dm ) === 0 ) {
                $digi_qso += intval( $c );
            }
        }
    }

    $result = array(
        'total_qso_year'=> $total_qso_year,
        'total_qso'     => $total_qso,
        'ssb_qso'       => $ssb_qso,
        'fm_qso'        => $fm_qso,
        'am_qso'        => $am_qso,
        'rtty_qso'      => $rtty_qso,
        'ft8_qso'       => $ft8_qso,
        'ft4_qso'       => $ft4_qso,
        'ft8ft4_qso'    => $ft8ft4_qso,
        'psk_qso'       => $psk_qso,
        'cw_qso'        => $cw_qso,
        'js8_qso'       => $js8_qso,
        'digi_qso'      => $digi_qso,
    );

    // Cache result
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
 * Returns the total number of RTTY-QSOs.
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
 * Returns the total number of FT4-QSOs.
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
 * Returns the total number of FT8-QSOs.
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
 * Returns the total number of PSK-QSOs.
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
 * Returns the total number of CW-QSOs.
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
