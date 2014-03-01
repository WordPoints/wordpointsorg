<?php

/**
 * Installs the module.
 *
 * @since 1.0.0
 */

/** @type array $wordpoints_data The 'wordpoints_data' option, from the including function. */
$wordpoints_data;

/** @type bool $network_active Whether the module is being network activated */
$network_active;

$custom_caps = wordpointsorg_get_custom_caps();

if ( $network_active ) {

	global $wpdb;

	$blog_ids = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->blogs}" );

	foreach ( $blog_ids as $blog_id ) {

		switch_to_blog( $blog_id );
		wordpoints_add_custom_caps( $custom_caps );
		restore_current_blog();
	}

} else {

	wordpoints_add_custom_caps( $custom_caps );
}

$wordpoints_data['modules']['wordpointsorg']['version'] = WORDPOINTSORG_VERSION;

wordpoints_update_network_option( 'wordpoints_data', $wordpoints_data );
