<?php

/**
 * Administration side functions
 *
 * @package WordPointsOrg
 * @since 1.0.0
 */

/**
 * Register admin-side scripts and styles.
 *
 * @since 1.1.0
 */
function wordpointsorg_admin_register_scripts() {

	$assets_url = wordpoints_modules_url( 'admin/assets', WORDPOINTSORG_DIR . '/wordpointsorg.php' );

	wp_register_style(
		'wordpointsorg-module-list-tables'
		, $assets_url . '/css/list-tables.css'
	);
}
add_action( 'admin_init', 'wordpointsorg_admin_register_scripts' );

/**
 * Get the channel for a module.
 *
 * @since 1.0.0
 *
 * @param array $module The module to get the channel for.
 *
 * @return string|false The URL of the channel to use for this module.
 */
function wordpoints_get_channel_for_module( $module ) {

	$channel = false;

	if ( isset( $module['channel'] ) ) {
		$channel = $module['channel'];
	}

	/**
	 * Filter the channel to use for a module.
	 *
	 * @since 1.0.0
	 *
	 * @param string|false $channel The channel to use, or false for none.
	 * @param array        $module  The module's header data.
	 */
	return apply_filters( 'wordpoints_channel_for_module', $channel, $module );
}

/**
 * Register the module channels.
 *
 * @since 1.0.0
 */
function wordpoints_register_module_channels() {

	$modules = wordpoints_get_modules();

	foreach ( $modules as $file => $module ) {

		$channel_url = wordpoints_get_channel_for_module( $module );

		if ( empty( $channel_url ) ) {
			continue;
		}

		$channel = WordPoints_Module_Channels::get( $channel_url );

		if ( empty( $channel ) ) {
			$channel = WordPoints_Module_Channels::register( $channel_url, true );
		}

		$channel->modules->add( $file, $module );
	}

	/**
	 * Registering the module channels.
	 *
	 * @since 1.0.0
	 */
	do_action( 'wordpoints_register_module_channels' );
}
add_action( 'wordpoints_register_module_apis', 'wordpoints_register_module_channels', 100 );

/**
 * Register the default module APIs.
 *
 * @since 1.0.0
 */
function wordpoints_register_default_module_apis() {

	WordPoints_Module_APIs::register(
		'edd-software-licensing'
		, null
		, 'WordPoints_EDD_Software_Licensing_Module_API'
	);

	WordPoints_Module_APIs::register(
		'edd-software-licensing-free'
		, null
		, 'WordPoints_EDD_Software_Licensing_Free_Module_API'
	);
}
add_action( 'wordpoints_register_module_apis', 'wordpoints_register_default_module_apis' );

/**
 * Get all of the installed modules which support a given thing.
 *
 * @since 1.0.0
 *
 * @param string $thing The thing to get mdules which support. E.g., 'updates'.
 *
 * @return array The installed modules that support this.
 */
function wordpoints_get_modules_supporting( $thing ) {

	$modules = array();

	foreach ( WordPoints_Module_Channels::get() as $channel ) {

		$api = $channel->get_api();

		if ( false === $api || false === $api->supports( $thing ) ) {
			continue;
		}

		$modules = array_merge( $modules, $channel->modules->get() );
	}

	return $modules;
}

/**
 * Schedule module update checks.
 *
 * @since 1.0.0
 */
function wordpoints_schedule_module_update_checks() {

	if ( ! wp_next_scheduled( 'wordpoints_check_for_module_updates' ) ) {
		wp_schedule_event( time(), 'twicedaily', 'wordpoints_check_for_module_updates' );
	}
}
add_action( 'init', 'wordpoints_schedule_module_update_checks' );

/**
 * Check for modules updates.
 *
 * @since 1.0.0
 *
 * @WordPress\action wordpoints_check_for_module_updates Cron event registered by
 *                   wordpoints_schedule_module_update_checks().
 *
 * @param int $timeout Maximum acceptable age for the cache. If the cache is older
 *                     than this, a it will be updated. The default is 12 hours.
 *
 * @return false|void Nothing, or false if the update check was skipped.
 */
function wordpoints_check_for_module_updates( $timeout = null ) {

	if ( defined( 'WP_INSTALLING' ) ) {
		return false;
	}

	// Check if there are any modules that use channels supporting updates.
	$modules = wordpoints_get_modules_supporting( 'updates' );

	if ( empty( $modules ) ) {
		return false;
	}

	$current = get_site_transient( 'wordpoints_module_updates' );

	if ( ! is_array( $current ) ) {
		$current = array();
	}

	$new_option = array();
	$new_option['last_checked'] = time();
	$new_option['checked'] = array();
	$new_option['response'] = array();

	if ( is_null( $timeout ) ) {
		$timeout = 12 * HOUR_IN_SECONDS;
	}

	$new_option['checked'] = wp_list_pluck( $modules, 'version' );
	ksort( $new_option['checked'] );

	if ( isset( $current['last_checked'] ) && $timeout > ( time() - $current['last_checked'] ) ) {

		/*
		 * We have checked recently, so let's see if any module versions have
		 * changed since the last check, and if not, we'll bail out.
		 */

		$modules_changed = false;

		if ( ! isset( $current['checked'] ) ) {
			$current['checked'] = array();
		}

		ksort( $current['checked'] );

		// If no module versions have changed, bail out.
		if ( $new_option['checked'] !== $current['checked'] ) {
			$modules_changed = true;
		}

		// If no modules have been deleted, bail out.
		if (
			! $modules_changed
			&& isset( $current['response'] )
			&& is_array( $current['response'] )
			&& ! array_diff_key( $new_option['checked'], $current['response'] )
		) {
			$modules_changed = true;
		}

		if ( ! $modules_changed ) {
			return false;
		}
	}

	// Update last_checked for current to prevent multiple requests if request hangs.
	$current['last_checked'] = time();
	set_site_transient( 'wordpoints_module_updates', $current );

	// Check each channel to see if it supports one of the available APIs.
	foreach ( WordPoints_Module_Channels::get() as $channel ) {

		$api = $channel->get_api();

		// This channel doesn't support any of the available APIs.
		if ( false === $api ) {
			continue;
		}

		// Attempt the updates for this channel through this API.
		$result = $api->check_for_updates( $channel );

		if ( false === $result ) {
			continue;
		}

		$new_option['response'] = array_merge( $new_option['response'], $result );
	}

	set_site_transient( 'wordpoints_module_updates', $new_option );
}
add_action( 'wordpoints_check_for_module_updates', 'wordpoints_check_for_module_updates' );

/**
 * Handle module update requests on update.php.
 *
 * @since 1.0.0
 */
function wordpointsorg_update_modules() {

	if ( ! current_user_can( 'update_wordpoints_modules' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to update modules for this site.', 'wordpointsorg' ), '', array( 'response' => 403 ) );
	}

	check_admin_referer( 'bulk-update-modules' );

	$modules = array();

	if ( isset( $_GET['modules'] ) ) {
		$modules = explode( ',', sanitize_text_field( wp_unslash( $_GET['modules'] ) ) );
	}

	$modules = array_map( 'urldecode', $modules );

	wp_enqueue_script( 'jquery' );
	iframe_header();

	require_once( WORDPOINTSORG_DIR . '/admin/includes/class-module-upgrader.php' );
	require_once( WORDPOINTSORG_DIR . '/admin/includes/class-bulk-module-upgrader-skin.php' );

	$upgrader = new WordPointsOrg_Module_Upgrader(
		new WordPointsOrg_Bulk_Module_Upgrader_Skin(
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
 */
function wordpointsorg_upgrade_module() {

	if ( ! current_user_can( 'update_wordpoints_modules' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to update modules for this site.', 'wordpointsorg' ), '', array( 'response' => 403 ) );
	}

	$module = ( isset( $_REQUEST['module'] ) )
		? sanitize_text_field( wp_unslash( $_REQUEST['module'] ) )
		: '';

	check_admin_referer( 'upgrade-module_' . $module );

	$title = __( 'Update Module', 'wordpointsorg' );
	$parent_file = 'admin.php';

	require_once( ABSPATH . 'wp-admin/admin-header.php' );

	require_once( WORDPOINTSORG_DIR . '/admin/includes/class-module-upgrader.php' );
	require_once( WORDPOINTSORG_DIR . '/admin/includes/class-module-upgrader-skin.php' );

	$upgrader = new WordPointsOrg_Module_Upgrader(
		new WordPointsOrg_Module_Upgrader_Skin(
			array(
				'title'  => $title,
				'nonce'  => 'upgrade-module_' . $module,
				'url'    => 'update.php?action=wordpoints-upgrade-module&module=' . urlencode( $module ),
				'module' => $module,
			)
		)
	);

	$upgrader->upgrade( $module );

	include( ABSPATH . 'wp-admin/admin-footer.php' );
}
add_action( 'update-custom_wordpoints-upgrade-module', 'wordpointsorg_upgrade_module' );

/**
 * Handle updating multiple modules on the modules administration screen.
 *
 * @since 1.0.0
 */
function wordpointsorg_update_selected_modules() {

	if ( ! current_user_can( 'update_wordpoints_modules' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to update modules for this site.', 'wordpointsorg' ), '', array( 'response' => 403 ) );
	}

	check_admin_referer( 'bulk-modules' );

	if ( isset( $_GET['modules'] ) ) {
		$modules = explode( ',', sanitize_text_field( wp_unslash( $_GET['modules'] ) ) );
	} elseif ( isset( $_POST['checked'] ) ) {
		$modules = array_map( 'sanitize_text_field', wp_unslash( (array) $_POST['checked'] ) );
	} else {
		$modules = array();
	}

	$url = self_admin_url( 'update.php?action=update-selected-wordpoints-modules&amp;modules=' . urlencode( implode( ',', $modules ) ) );
	$url = wp_nonce_url( $url, 'bulk-update-modules' );

	$parent_file = 'admin.php';

	require_once( ABSPATH . 'wp-admin/admin-header.php' );

	?>

	<div class="wrap">
		<h1><?php esc_html_e( 'Update Modules', 'wordpointsorg' ); ?></h1>

		<iframe src="<?php echo esc_attr( $url ); ?>" style="width: 100%; height:100%; min-height:850px;"></iframe>
	</div>

	<?php

	require_once( ABSPATH . 'wp-admin/admin-footer.php' );

	exit;
}
add_action( 'wordpoints_modules_screen-update-selected', 'wordpointsorg_update_selected_modules' );
add_action( 'update-core-custom_do-wordpoints-module-upgrade', 'wordpointsorg_update_selected_modules' );

/**
 * Add support for the channel module file header.
 *
 * @since 1.0.0
 */
function wordpointsorg_extra_module_headers( $extra ) {

	$extra[] = 'channel';

	return $extra;
}
add_filter( 'extra_module_headers', 'wordpointsorg_extra_module_headers' );

/**
 * Add the 'upgrade' module status to the reconginzed module statuses.
 *
 * @since 1.0.0
 *
 * @param array $statuses The module statuses.
 *
 * @return string[] The module statuses with 'upgrade' added.
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

		$current = get_site_transient( 'wordpoints_module_updates' );

		foreach ( (array) $modules['all'] as $module_file => $module_data ) {

			if ( isset( $current['response'][ $module_file ] ) ) {

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

	return _n( 'Update Available <span class="count">(%s)</span>', 'Update Available <span class="count">(%s)</span>', (int) $count, 'wordpointsorg' );
}
add_filter( 'wordpoints_modules_status_link_text-upgrade', 'wordpointsorg_module_upgrades_filter_link', 10, 2 );

/**
 * Add the 'Upgrade' bulk action to the modules table.
 *
 * @since 1.0.0
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
 * @param string $class The class for the module's row in the table.
 * @param string $module_file The module's main file.
 *
 * @return string The class, with 'update' added if the moduel has an update.
 */
function wordpointsorg_module_row_update_class( $class, $module_file ) {

	$current = get_site_transient( 'wordpoints_module_updates' );

	if ( ! empty( $current['response'][ $module_file ] ) ) {
		$class .= ' update';
	}

	return $class;
}
add_filter( 'wordpoints_module_list_row_class', 'wordpointsorg_module_row_update_class', 10, 2 );

/**
 * Set up the action hooks to display the module update rows.
 *
 * @since 1.0.0
 */
function wordpointsorg_module_update_rows() {

	if ( ! current_user_can( 'update_wordpoints_modules' ) ) {
		return;
	}

	$current = get_site_transient( 'wordpoints_module_updates' );

	if ( isset( $current['response'] ) && is_array( $current['response'] ) ) {

		foreach ( $current['response'] as $module_file => $version ) {
			add_action( "wordpoints_after_module_row_{$module_file}", 'wordpointsorg_module_update_row', 10, 2 );
		}
	}
}
add_action( 'admin_init', 'wordpointsorg_module_update_rows' );

/**
 * Display the update message for a module in the modules list table.
 *
 * @since 1.0.0
 *
 * @WordPress\action wordpoints_after_module_row_{$module_file} Added by
 *                   wordpointsorg_module_update_rows().
 */
function wordpointsorg_module_update_row( $file, $module_data ) {

	$current = get_site_transient( 'wordpoints_module_updates' );

	if ( ! isset( $current['response'][ $file ] ) ) {
		return false;
	}

	$channel = wordpoints_get_channel_for_module( $module_data );
	$channel = WordPoints_Module_Channels::get( $channel );

	if ( ! $channel ) {
		return false;
	}

	$api = $channel->get_api();

	if ( ! $api ) {
		return false;
	}

	if ( is_network_admin() || ! is_multisite() ) {

		wp_enqueue_script( 'thickbox' );
		wp_enqueue_style( 'thickbox' );

		$new_version = $current['response'][ $file ];

		$modules_allowed_tags = array(
			'a' => array( 'href' => array(), 'title' => array() ),
			'abbr' => array( 'title' => array() ),
			'acronym' => array( 'title' => array() ),
			'code' => array(),
			'em' => array(),
			'strong' => array(),
		);

		$details_url = admin_url(
			'update.php?action=wordpoints-iframe-module-changelog&module=' . urlencode( $file )
		);

		?>

		<tr class="plugin-update-tr wordpoints-module-update-tr <?php echo ( is_wordpoints_module_active( $file ) ) ? 'active' : 'inactive'; ?>">
			<td colspan="<?php echo (int) WordPoints_Modules_List_Table::instance()->get_column_count(); ?>" class="plugin-update wordpoints-module-update colspanchange">
				<div class="update-message notice inline notice-warning notice-alt">
					<p>

					<?php

					if ( ! current_user_can( 'update_wordpoints_modules' ) ) {
						printf(
							wp_kses(
								__( 'There is a new version of %1$s available. <a href="%2$s" class="thickbox" title="%3$s">View version %4$s details</a>.', 'wordpointsorg' )
								, array( 'a' => array( 'href' => array(), 'class' => array(), 'title' => array() ) )
							)
							, wp_kses( $module_data['name'], $modules_allowed_tags )
							, esc_attr( esc_url( $details_url ) )
							, esc_attr( wp_kses( $module_data['name'], $modules_allowed_tags ) )
							, esc_html( $new_version )
						);
					} else {
						printf(
							wp_kses(
								__( 'There is a new version of %1$s available. <a href="%2$s" class="thickbox" title="%3$s">View version %4$s details</a> or <a href="%5$s">update now</a>.', 'wordpointsorg' )
								, array( 'a' => array( 'href' => array(), 'class' => array(), 'title' => array() ) )
							)
							, wp_kses( $module_data['name'], $modules_allowed_tags )
							, esc_url( $details_url )
							, esc_attr( wp_kses( $module_data['name'], $modules_allowed_tags ) )
							, esc_html( $new_version )
							, esc_attr( wp_nonce_url( self_admin_url( 'update.php?action=wordpoints-upgrade-module&module=' ) . $file, 'upgrade-module_' . $file ) )
						);
					}

					/**
					 * Fires at the end of the update message container in each
					 * row of the modules list table.
					 *
					 * The dynamic portion of the hook name, `$file`, refers to the path
					 * of the module's primary file relative to the modules directory.
					 *
					 * @since 1.0.0
					 *
					 * @param array  $module_data The module's data.
					 * @param string $new_version The new version of the module.
					 */
					do_action( "wordpoints_in_module_update_message-{$file}", $module_data, $new_version );

					?>

					</p>
				</div>
			</td>
		</tr>

		<?php

	} // End if ( is_network_admin() || ! is_multisite() ).
}

/**
 * Display the changelog for a module.
 *
 * @since 1.1.0
 */
function wordpointsorg_iframe_module_changelog() {

	if ( ! defined( 'IFRAME_REQUEST' ) ) {
		define( 'IFRAME_REQUEST', true );
	}

	if ( ! current_user_can( 'update_wordpoints_modules' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to update modules for this site.', 'wordpointsorg' ), '', array( 'response' => 403 ) );
	}

	if ( empty( $_GET['module'] ) ) {
		wp_die( esc_html__( 'No module supplied.', 'wordpointsorg' ), '', array( 'response' => 200 ) );
	}

	$module_file = sanitize_text_field( urldecode( wp_unslash( $_GET['module'] ) ) ); // WPCS: sanitization OK.

	$modules = wordpoints_get_modules();

	if ( ! isset( $modules[ $module_file ] ) ) {
		wp_die( esc_html__( 'That module does not exist.', 'wordpointsorg' ), '', array( 'response' => 200 ) );
	}

	$channel = WordPoints_Module_Channels::get(
		wordpoints_get_channel_for_module( $modules[ $module_file ] )
	);

	if ( ! $channel ) {
		wp_die( esc_html__( 'There is no channel specified for this module.', 'wordpointsorg' ), '', array( 'response' => 200 ) );
	}

	$api = $channel->get_api();

	if ( ! $api ) {
		wp_die( esc_html__( 'The channel for this module uses an unsupported API.', 'wordpointsorg' ), '', array( 'response' => 200 ) );
	}

	iframe_header();

	echo '<div style="margin-left: 10px;">';
	echo wp_kses(
		$api->get_changelog( $channel, $modules[ $module_file ] )
		, 'wordpoints_module_changelog'
	);
	echo '</div>';

	iframe_footer();
}
add_action( 'update-custom_wordpoints-iframe-module-changelog', 'wordpointsorg_iframe_module_changelog' );

/**
 * Get all of the modules with updates.
 *
 * In addition to the usual data for a modules, the 'new_version' key contains the
 * new version available for each module.
 *
 * @since 1.1.0
 *
 * @return array[] The data arrays for the modules with updates, indexed by module
 *                 file.
 */
function wordpoints_get_module_updates() {

	$all_modules = wordpoints_get_modules();
	$update_modules = array();
	$module_updates = get_site_transient( 'wordpoints_module_updates' );

	foreach ( $all_modules as $module_file => $module_data ) {

		if ( ! isset( $module_updates['response'][ $module_file ] ) ) {
			continue;
		}

		$update_modules[ $module_file ] = $module_data;
		$update_modules[ $module_file ]['new_version'] = $module_updates['response'][ $module_file ];
	}

	return $update_modules;
}

/**
 * List the available module updates.
 *
 * @since 1.1.0
 */
function wordpoints_list_module_updates() {

	wp_enqueue_style( 'wordpointsorg-module-list-tables' );

	$modules = wordpoints_get_module_updates();

	?>

	<h2><?php esc_html_e( 'WordPoints Modules', 'wordpointsorg' ); ?></h2>

	<?php if ( empty( $modules ) ) : ?>
		<p><?php esc_html_e( 'Your modules are all up to date.', 'wordpointsorg' ); ?></p>
		<?php return; ?>
	<?php endif; ?>

	<p><?php esc_html_e( 'The following modules have new versions available. Check the ones you want to update and then click &#8220;Update Modules&#8221;.', 'wordpointsorg' ); ?></p>

	<form method="post" action="update-core.php?action=do-wordpoints-module-upgrade" name="upgrade-wordpoints-modules" class="upgrade">
		<?php wp_nonce_field( 'bulk-modules' ); ?>

		<p><input id="upgrade-wordpoints-modules" class="button" type="submit" value="<?php esc_attr_e( 'Update Modules', 'wordpointsorg' ); ?>" name="upgrade" /></p>

		<table class="widefat" id="update-wordpoints-modules-table">
			<thead>
				<tr>
					<td scope="col" class="manage-column check-column">
						<input type="checkbox" id="wordpoints-modules-select-all" />
					</td>
					<th scope="col" class="manage-column">
						<label for="wordpoints-modules-select-all"><?php esc_html_e( 'Select All', 'wordpointsorg' ); ?></label>
					</th>
				</tr>
			</thead>

			<tbody class="wordpoints-modules">
				<?php foreach ( $modules as $module_file => $module_data ) : ?>
					<tr>
						<th scope="row" class="check-column">
							<input type="checkbox" name="checked[]" value="<?php echo esc_attr( $module_file ); ?>" />
						</th>
						<td>
							<p>
								<strong><?php echo esc_html( $module_data['name'] ); ?></strong>
								<br />
								<?php echo esc_html( sprintf( __( 'You have version %1$s installed. Update to %2$s.', 'wordpointsorg' ), $module_data['version'], $module_data['new_version'] ) ); ?>
								<a href="<?php echo esc_attr( self_admin_url( 'update.php?action=wordpoints-iframe-module-changelog&module=' . urlencode( $module_file ) . '&TB_iframe=true&width=640&height=662' ) ); ?>" class="thickbox" title="<?php echo esc_attr( $module_data['name'] ); ?>">
									<?php echo esc_html( sprintf( __( 'View version %1$s details.', 'wordpointsorg' ), $module_data['new_version'] ) ); ?>
								</a>
							</p>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>

			<tfoot>
				<tr>
					<td scope="col" class="manage-column check-column">
						<input type="checkbox" id="wordpoints-modules-select-all-2" />
					</td>
					<th scope="col" class="manage-column">
						<label for="wordpoints-modules-select-all-2"><?php esc_html_e( 'Select All', 'wordpointsorg' ); ?></label>
					</th>
				</tr>
			</tfoot>
		</table>
		<p><input id="upgrade-wordpoints-modules-2" class="button" type="submit" value="<?php esc_attr_e( 'Update Modules', 'wordpointsorg' ); ?>" name="upgrade" /></p>
	</form>

	<?php
}
add_action( 'core_upgrade_preamble', 'wordpoints_list_module_updates' );

// EOF
