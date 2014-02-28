<?php

/**
 * Administration panels related functions.
 *
 * @package WordPointsOrg
 * @since 1.0.0
 */

/**
 * Handle module update requests on update.php.
 *
 * @since 1.0.0
 *
 * @action update-custom_update-selected-wordpoints-modules
 */
function wordpointsorg_update_modules() {

	if ( ! current_user_can( 'update_wordpoints_modules' ) ) {
		wp_die( __( 'You do not have sufficient permissions to update modules for this site.', 'wordpointsorg' ) );
	}

	check_admin_referer( 'bulk-update-modules' );

	if ( isset( $_GET['modules'] ) ) {
		$modules = explode( ',', stripslashes( $_GET['modules'] ) );
	} elseif ( isset( $_POST['checked'] ) ) {
		$modules = (array) $_POST['checked'];
	} else {
		$modules = array();
	}

	$modules = array_map( 'urldecode', $modules );

	wp_enqueue_script( 'jquery' );
	iframe_header();

	$upgrader = new WordPoints_Module_Upgrader(
		new WordPoints_Bulk_Module_Upgrader_Skin(
			array(
				'nonce' => 'bulk-update-modules',
				'url'   => 'update.php?action=update-selected-wordpoints-modules&amp;modules=' . urlencode( implode( ',', $modules ) ),
			)
		)
	);

	$upgrader->bulk_upgrade( $modules );

	iframe_footer();
}
add_action( 'update-custom_update-selected-wordpoints-modules', 'wordpointsorg_update_modules' );

/**
 * Upgrade a module.
 *
 * @since 1.0.0
 *
 * @action update-custom_upgrade-wordpoints-module
 */
function wordpointsorg_upgrade_module() {

	if ( ! current_user_can( 'update_wordpoints_modules' ) ) {
		wp_die( __( 'You do not have sufficient permissions to update modules for this site.', 'wordpointsorg' ) );
	}

	$module = ( isset( $_REQUEST['module'] ) ) ? $_REQUEST['module'] : '';

	check_admin_referer( 'upgrade-module_' . $module );

	$title = __( 'Update Module' );
	$parent_file = 'admin.php';

	require_once ABSPATH . 'wp-admin/admin-header.php';

	$upgrader = new WordPoints_Module_Upgrader(
		new WordPoints_Module_Upgrader_Skin(
			array(
				'title'  => $title,
				'nonce'  => 'upgrade-module_' . $module,
				'url'    => 'update.php?action=upgrade-wordpoints-module&module=' . urlencode( $module ),
				'module' => $module,
			)
		)
	);

	$upgrader->upgrade( $module );

	include ABSPATH . 'wp-admin/admin-footer.php';
}
add_action( 'update-custom_upgrade-wordpoints-module', 'wordpointsorg_upgrade_module' );

/**
 * Install a module.
 *
 * @since 1.0.0
 *
 * @action update-custom_install-wordpoints-module
 */
function wordpointsorg_install_module() {

	if ( ! current_user_can( 'install_wordpoints_modules' ) ) {
		wp_die( __( 'You do not have sufficient permissions to install modules on this site.' ) );
	}

	$module = ( isset( $_REQUEST['module'] ) ) ? $_REQUEST['module'] : '';

	check_admin_referer( 'install-module_' . $module );

	$api = wordpointsorg_modules_api(
		'module_information'
		, array( 'slug' => $module, 'fields' => array( 'sections' => false ) )
	);

	if ( is_wp_error( $api ) ) {
		wp_die( $api );
	}

	$title = __( 'Module Install' );
	$parent_file = 'admin.php';

	require_once ABSPATH . 'wp-admin/admin-header.php';

	$url = 'update.php?action=install-module&module=' . urlencode( $module );

	if ( isset( $_GET['from'] ) ) {
		$url .= '&from=' . urlencode( stripslashes( $_GET['from'] ) );
	}

	$upgrader = new WordPoints_Module_Upgrader(
		new WordPoints_Module_Installer_Skin(
			array(
				'title'  => sprintf( __( 'Installing Module: %s' ), $api->name . ' ' . $api->version ),
				'url'    => $url,
				'nonce'  => 'install-module_' . $module,
				'module' => $module,
				'api'    => $api,
				'type'   => 'web',
			)
		)
	);

	$upgrader->install( $api->download_link );

	include ABSPATH . 'wp-admin/admin-footer.php';
}
add_action( 'update-custom_install-wordpoints-module', 'wordpointsorg_install_module' );

/**
 * Handle updating multiple modules on the modules administration screen.
 *
 * @since 1.0.0
 *
 * @action wordpoints_modules_screen-update-selected
 */
function wordpointsorg_update_selected_modules() {

	check_admin_referer( 'bulk-modules' );

	if ( isset( $_GET['modules'] ) ) {
		$modules = explode( ',', $_GET['modules'] );
	} elseif ( isset( $_POST['checked'] ) ) {
		$modules = (array) $_POST['checked'];
	} else {
		$modules = array();
	}

	$title = __( 'Update Modules', 'wordpoints' );
	$parent_file = 'admin.php';

	require_once ABSPATH . 'wp-admin/admin-header.php';

	echo '<div class="wrap">';
	screen_icon();
	echo '<h2>' . esc_html( $title ) . '</h2>';

	$url = self_admin_url( 'update.php?action=update-selected-wordpoints-modules&amp;modules=' . urlencode( implode( ',', $modules ) ) );
	$url = wp_nonce_url( $url, 'bulk-update-modules' );

	echo "<iframe src='{$url}' style='width: 100%; height:100%; min-height:850px;'></iframe>";
	echo '</div>';

	require_once ABSPATH . 'wp-admin/admin-footer.php';

	exit;
}
add_action( 'wordpoints_modules_screen-update-selected', 'wordpointsorg_update_selected_modules' );

/**
 * Run when the modules page is being loaded.
 *
 * @since 1.0.0
 *
 * @action {@todo}
 */
function wordpoints_admin_modules_screen_pre() {

	$tab = wordpoints_admin_get_current_tab();

	// TODO route this pages via a specific iframe handler instead of the do_action below
	if ( ! defined( 'IFRAME_REQUEST' ) && 'module-information' == $tab )
		define( 'IFRAME_REQUEST', true );

	if ( ! current_user_can( 'install_plugins' ) )
		wp_die( __( 'You do not have sufficient permissions to install WordPoints modules on this site.', 'wordpoints' ) );

	if ( is_multisite() && ! is_network_admin() ) {

		// TODO
		wp_redirect( network_admin_url( 'plugin-install.php' ) );
		exit();
	}

	wp_enqueue_script( 'plugin-install' );

	if ( 'module-information' != $tab )
		add_thickbox();

	$screen = get_current_screen();
	// TODO filter this somehow?
	$screen->add_help_tab(
		array(
			'id'		=> 'adding-modules',
			'title'		=> __( 'Adding Modules', 'wordpoints' ),
			'content'	=>
				'<p>' . __( 'If you know what you&#8217;re looking for, Search is your best bet. The Search screen has options to search the WordPoints.org Module Directory for a particular Term, Author, or Tag. You can also search the directory by selecting popular tags. Tags in larger type mean more modules have been labeled with that tag.', 'wordpoints' ) . '</p>' .
				'<p>' . __( 'If you just want to get an idea of what&#8217;s available, you can browse Featured, Popular, and Newest modules by using the links in the upper left of the screen. These sections rotate regularly.', 'wordpoints' ) . '</p>' .
				'<p>' . __( 'You can also browse a user&#8217;s favorite modules, by using the Favorites link in the upper left of the screen and entering their WordPoints.org username.', 'wordpoints' ) . '</p>' .
				'<p>' . __( 'If you want to install a module that you&#8217;ve downloaded elsewhere, click the Upload link in the upper left. You will be prompted to upload the .zip package, and once uploaded, you can activate the new module.', 'wordpoints' ) . '</p>'
		)
	);
}
add_action( 'load-toplevel_page_wordpoints_modules', 'wordpoints_admin_modules_screen_pre' );

/**
 * Add the 'upgrade' module status to the reconginzed module statuses.
 *
 * @since 1.0.0
 *
 * @filter wordpoints_module_statuses
 *
 * @param array $statuses The module statuses.
 *
 * @return array The module statuses with 'upgrade' added.
 */
function wordpointsorg_add_upgrade_module_status( $statuses ) {

	$statuses[] = 'upgrade';

	return $statuses;
}
add_filter( 'wordpoints_module_statuses', 'wordpointsorg_add_upgrade_module_status' );

/**
 * Add the 'upgrade' status to the modules list table.
 *
 * @since 1.0.0
 *
 * @filter wordpoints_modules_list_table_items
 *
 * @param array[] $modules Modules for display in the list table, grouped by status.
 *
 * @return array The modules with the upgrade status added.
 */
function wordpointsorg_add_upgrade_modules_list_table( $modules ) {

	$modules['upgrade'] = array();

	/*
	 * If the user can update modules, add an 'update' key to the module data
	 * for modules that have an available update.
	 */
	if (
		(
			! is_multisite()
			|| (
				is_network_admin()
				&& current_user_can( 'manage_network_wordpoints_modules' )
			)
		)
		&& current_user_can( 'update_wordpoints_modules' )
	) {
		$current = get_site_transient( 'wordpoints_update_modules' );

		foreach ( (array) $modules['all'] as $module_file => $module_data ) {

			if ( isset( $current->response[ $module_file ] ) ) {

				$modules['all'][ $module_file ]['update'] = true;
				$modules['upgrade'][ $module_file ] = $modules['all'][ $module_file ];
			}
		}
	}

	return $modules;
}
add_filter( 'wordpoints_modules_list_table_items', 'wordpointsorg_add_upgrade_modules_list_table' );

/**
 * Add the upgrades filter to the module install table.
 *
 * @since 1.0.0
 *
 * @param string $text  The text for the link.
 * @param string $count The number of modules matching the filter.
 *
 * @return string The text for the upgrades filter link.
 */
function wordpointsorg_module_upgrades_filter_link( $text $count ) {

	return _n( 'Update Available <span class="count">(%s)</span>', 'Update Available <span class="count">(%s)</span>', $count );
}
add_filter( 'wordpoints_modules_status_link_text-upgrade', 'wordpointsorg_module_upgrades_filter_link' );

/**
 * Add the 'Upgrade' bulk action to the modules table.
 *
 * @since 1.0.0
 *
 * @action wordpoints_module_bulk_actions
 *
 * @param array $actions The bulk action links for the modules table.
 *
 * @return array The bulk action links, possibly with 'Upgrade' action added.
 */
function wordpointsorg_module_upgrade_bulk_action_link( $actions ) {

	if ( ( ! is_multisite() || is_network_admin() ) && current_user_can( 'update_wordpoints_modules' ) ) {
		$actions['update-selected'] = __( 'Update', 'wordpointsorg' );
	}

	return $actions;
}
add_filter( 'wordpoints_module_bulk_actions', 'wordpointsorg_module_upgrade_action_link' );

/**
 * Add the 'update' class to modules in the list table that have an update available.
 *
 * @since 1.0.0
 *
 * @action wordpoints_module_list_row_class
 *
 * @param string $class The class for the module's row in the table.
 * @param string $module_file The module's main file.
 * @param string $module_data The data for the module being displayed.
 *
 * @return string The class, with 'update' added if the moduel has an update.
 */
function wordpointsorg_module_row_update_class( $class, $module_file, $module_data ) {

	if ( ! empty( $module_data['update'] ) ) {
		$class .= ' update';
	}

	return $class;
}
add_filter( 'wordpoints_module_list_row_class', 'wordpointsorg_module_row_update_class', 10, 3 );

