<?php

/**
 * Update/Install Module administration panel.
 *
 * @package WordPointsOrg
 * @since 1.0.0
 */

/**
 * Bulk update modules.
 *
 * @since 1.0.0
 *
 * @WordPress\action update-custom_wordpoints-update-selected
 */
function wordpointsorg_admin_bulk_upgrade_modules() {

	global $title;

	if ( ! current_user_can( 'update_wordpoints_modules' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to update WordPoints modules for this site.', 'wordpointsorg' ) );
	}

	check_admin_referer( 'wordpoints-bulk-update-modules' );

	if ( isset( $_GET['modules'] ) ) {
		$modules = explode( ',', wp_unslash( $_GET['modules'] ) );
	} elseif ( isset( $_POST['checked'] ) ) {
		$modules = (array) wp_unslash( $_POST['checked'] );
	} else {
		$modules = array();
	}

	$modules = array_map( 'urldecode', $modules );

	wp_enqueue_script( 'jquery' );

	iframe_header();

	$upgrader = new WordPointsOrg_Module_Upgrader(
		new WordPointsOrg_Bulk_Module_Upgrader_Skin(
			array(
				'nonce' => 'wordpoints-bulk-update-modules',
				'url'   => 'update.php?action=wordpoints-update-selected&amp;modules=' . urlencode( implode( ',', $modules ) ),
			)
		)
	);

	$upgrader->bulk_upgrade( $modules );

	iframe_footer();
}
add_action( 'update-custom_wordpoints-update-selected', 'wordpoints_admin_module_upgrades' );

/**
 * Download an updated version of a module.
 *
 * @since 1.0.0
 *
 * @WordPress\action update-custom_wordpoints-upgrade-module
 */
function wordpointsorg_admin_upgrade_module() {

	global $title;

	if ( ! current_user_can( 'update_wordpoints_modules' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to update WordPoints modules for this site.', 'wordpointsorg' ) );
	}

	if ( ! isset( $_GET['module_id'] ) ) {
		wp_die( esc_html__( 'You need to choose which module to upgrade.', 'wordpointsorg' ) );
	}

	$module = (int) $_GET['module'];

	check_admin_referer( "upgrade-module_{$module}" ) ;

	$title = __( 'Update Module', 'wordpointsorg' );

	require_once( ABSPATH . 'wp-admin/admin-header.php' );

	$upgrader = new WordPoints_Module_Upgrader(
		new WordPoints_Module_Upgrader_Skin(
			array(
				'title'  => $title,
				'nonce'  => "upgrade-module_{$module_id}",
				'url'    => 'update.php?action=wordpoints-upgrade-module&module_id=' . $module,
				'module' => $module, // TODO Maybe module_id => instead.
			)
		)
	);

	$upgrader->upgrade( $module );

	include( ABSPATH . 'wp-admin/admin-footer.php' );
}
add_action( 'update-custom_wordpoints-upgrade-module' , 'wordpointsorg_admin_upgrade_module' );
