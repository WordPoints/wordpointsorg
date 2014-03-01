<?php

/**
 * Uninstall the module.
 *
 * @since 1.0.0
 */

// Include dependencies.
require_once dirname( __FILE__ ) . '/includes/functions.php';

$custom_caps = array_keys( wordpointsorg_get_custom_caps() );

if ( is_multisite() ) {

	global $wpdb;

	$blog_ids = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->blogs}" );

	foreach ( $blog_ids as $blog_id ) {

		switch_to_blog( $blog_id );
		wordpoints_remove_custom_caps( $custom_caps );
		restore_current_blog();
	}

} else {

	wordpoints_remove_custom_caps( $custom_caps );
}
