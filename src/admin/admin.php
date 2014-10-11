<?php

/**
 * Administration panels related functions.
 *
 * @package WordPointsOrg
 * @since 1.0.0
 */

/**
 * Automatically load the upgrade and installer classes when needed.
 *
 * @since 1.0.0
 *
 * @param string $class The name of a class that needs to be loaded.
 */
function wordpointsorg_upgrader_class_autoloader( $class ) {

	if ( $class{0} !== 'W' || substr( $class, 0, 13 ) !== 'WordPointsOrg' ) {
		return;
	}

	$class = substr( $class, 14 );

	switch ( $class ) {

		case 'Bulk_Module_Upgrader_Skin':
		case 'Module_Upgrader':
		case 'Module_Upgrader_Skin':
			$file = strtolower( str_replace( '_', '-', $class ) );
			include( WORDPOINTSORG_DIR . '/admin/includes/class-' . $file . '.php' );
		break;
	}
}
spl_autoload_register( 'wordpointsorg_upgrader_class_autoloader' );

/**
 * Set up for the Module Install administration screen.
 *
 * @since 1.0.0
 */
function wordpointsorg_module_install_admin_screen_load() {

	if ( ! current_user_can( 'install_wordpoints_modules' ) ) {
		wp_die( __( 'You do not have sufficient permissions to install WordPoints modules on this site.', 'wordpoints' ) );
	}

	if ( is_multisite() && ! is_network_admin() ) {
		wp_redirect( network_admin_url( 'admin.php?page=wordpoints_install_modules' ) );
		exit();
	}

	$screen = get_current_screen();

	$screen->add_help_tab(
		array(
			'id'		=> 'adding-modules',
			'title'		=> __( 'Adding Modules', 'wordpoints' ),
			'content'	=>
				'<p>' . __( 'If you know what you&#8217;re looking for, Search is your best bet.', 'wordpoints' ) . '</p>' .
				'<p>' . __( 'If you just want to get an idea of what&#8217;s available, you can browse the Newest modules by using the link in the upper left of the screen. This section rotates regularly.', 'wordpoints' ) . '</p>' .
				'<p>' . __( 'If you want to install a module that you&#8217;ve downloaded elsewhere, click the Upload link in the upper left. You will be prompted to upload the .zip package, and once uploaded, you can activate the new module.', 'wordpoints' ) . '</p>'
		)
	);

	$screen->set_help_sidebar(
		'<p><strong>' . __( 'For more information:', 'wordpoints' ) . '</strong></p>' .
		'<p>' . sprintf( __( '<a href="%s" target="_blank">Developer Documentation</a>', 'wordpoints' ), 'http://wordpoints.org/developer-guide/modules/' ) . '</p>' .
		'<p>' . sprintf( __( '<a href="%s" target="_blank">Support Forums</a>', 'wordpoints' ), 'http://wordpress.org/support/plugin/wordpoints' ) . '</p>'
	);

	add_screen_option(
		'per_page'
		, array(
			'label'   => _x( 'Modules', 'modules per page (screen options)', 'wordpoints' ),
			'default' => 999,
			'option'  =>'wordpoints_install_modules_per_page',
		)
	);
var_log( $screen->get_option( 'per_page' ) );
	register_column_headers(
		$screen
		, array(
			'name'        => _x( 'Name', 'module name', 'wordpoints' ),
			'version'     => __( 'Version', 'wordpoints' ),
			'description' => __( 'Description', 'wordpoints' ),
		)
	);
}
add_action( 'load-admin_page_wordpoints_install_modules', 'wordpointsorg_module_install_admin_screen_load' );

/**
 * Save the screen option value.
 *
 * @since 1.0.0
 */
function wordpointsorg_module_install_set_screen_option( $status, $option, $value ) {

	if ( 'wordpoints_install_modules_per_page' === $option ) {
		return absint( $value );
	}
}
add_filter( 'set-screen-option', 'wordpointsorg_module_install_set_screen_option', 10, 3 );

/**
 * Handle module update requests on update.php.
 *
 * @since 1.0.0
 *
 * @action update-custom_update-selected-wordpoints-modules
 */
function wordpointsorg_update_modules() {

	if ( ! current_user_can( 'update_wordpoints_modules' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to update modules for this site.', 'wordpointsorg' ) );
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
function wordpointsorg_module_upgrades_filter_link( $text, $count ) {

	return _n( 'Update Available <span class="count">(%s)</span>', 'Update Available <span class="count">(%s)</span>', (int) $count );
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
add_filter( 'wordpoints_module_bulk_actions', 'wordpointsorg_module_upgrade_bulk_action_link' );

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

/**
 * Set up the list table and display the view tabs of the module install screen.
 *
 * @since 1.0.0
 */
function wordpointsorg_install_modules_screen() {

	global $wp_list_table;

	require_once( WORDPOINTSORG_DIR . '/admin/includes/class-module-install-list-table.php' );

	$wp_list_table = new WordPoints_Module_Install_List_Table();
	$wp_list_table->prepare_items();

	if ( 'upload' !== $_GET['tab'] ) {
		wordpointsorg_module_install_search_form();
	}

	$wp_list_table->views();
}
add_action( 'wordpoints_install_modules_screen', 'wordpointsorg_install_modules_screen' );

/**
 * Display search form for searching modules.
 *
 * @since 1.0.0
 */
function wordpointsorg_module_install_search_form() {

	$term = isset( $_REQUEST['s'] ) ? wp_unslash( $_REQUEST['s'] ) : '';

	?>

	<form method="post" style="float: right;">
		<input type="hidden" name="tab" value="search" />
		<label>
			<span class="screen-reader-text"><?php esc_html_e( 'Search Modules', 'wordpointsorg' ); ?></span>
			<input type="search" name="s" value="<?php echo esc_attr( $term ); ?>" placeholder="<?php esc_attr__( 'Search Modules', 'wordpointsorg' ); ?>" />
		</label>
		<?php submit_button( __( 'Search Modules' ), 'button', false, false, array( 'id' => 'search-submit' ) ); ?>
	</form>

	<?php
}

/**
 * Display the module install dashboard.
 *
 * @since 1.0.0
 */
function wordpointsorg_module_install_dashboard() {

	?>
	<p><?php esc_html_e( 'Here you can browse and the available modules on WordPoints.org. The newest modules are displayed below. If you already know what you are a looking for, you can use the search box. If you already have a module package to upload, click on the &#8220;Upload&#8221; tab above.' ); ?></p>
	<?php
}
add_action( 'wordpoints_install_modules-dashboard', 'wordpointsorg_module_install_dashboard' );

/**
 * Display the module install list table content.
 *
 * @since 1.0.0
 */
function wordpointsorg_display_module_install_table() {

	global $wp_list_table;

	$wp_list_table->display();
}
add_action( 'wordpoints_install_modules-search',  'wordpointsorg_display_module_install_table' );
add_action( 'wordpoints_install_modules-new',     'wordpointsorg_display_module_install_table' );
add_action( 'wordpoints_install_modules-dashboard', 'wordpointsorg_display_module_install_table' );

// EOF
