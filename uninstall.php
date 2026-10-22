<?php
/**
 * Removes the plugin settings when the plugin is deleted.
 *
 * Cached API results are transients with an expiration, WordPress
 * removes them automatically once they have expired.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit; // Prevent direct call
}

delete_option( 'wavelog_url' );
delete_option( 'wavelog_api_key' );
delete_option( 'wavelog_station_id' );
delete_option( 'wavelog_cache_minutes' );
