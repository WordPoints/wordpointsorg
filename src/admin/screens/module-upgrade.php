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

// TODO is this needed?
function wordpointsorg_admin_activate_module() {

		case 'wordpoints-activate-module':
			if ( ! current_user_can( 'update_plugins' ) ) {
				wp_die( __( 'You do not have sufficient permissions to update plugins for this site.', 'wordpoints' ) );
			}

			check_admin_referer( 'activate-plugin_' . $module );

			$wordpoints_modules = WordPoints_Module::instance();

			if ( ! isset( $_GET['failure'] ) && ! isset( $_GET['success'] ) ) {

				wp_redirect( admin_url( 'update.php?action=activate-plugin&failure=true&plugin=' . urlencode( $module ) . '&_wpnonce=' . $_GET['_wpnonce'] ) );
				$wordpoints_modules->activate( $module, '', ! empty( $_GET['networkwide'] ), true );
				wp_redirect( admin_url( 'update.php?action=activate-plugin&success=true&plugin=' . urlencode( $module ) . '&_wpnonce=' . $_GET['_wpnonce'] ) );

				die();
			}

			iframe_header( __( 'Module Reactivation', 'wordpoints' ), true );

			if ( isset( $_GET['success'] ) ) {
				echo '<p>' . __( 'Module reactivated successfully.', 'wordpoints' ) . '</p>';
			}

			if ( isset( $_GET['failure'] ) ) {

				echo '<p>' . __( 'Module failed to reactivate due to a fatal error.', 'wordpoints' ) . '</p>';

				error_reporting( E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_ERROR | E_WARNING | E_PARSE | E_USER_ERROR | E_USER_WARNING | E_RECOVERABLE_ERROR );
				@ini_set( 'display_errors', true ); // Ensure that Fatal errors are displayed.
				include( $wordpoints_modules->get_dir() . '/' . $module );
			}

			iframe_footer();
		break;
}
add_action( 'update-custom_wordpoints-upload-module'  , 'wordpoints_admin_module_upgrades' );

/**
 * Download a new module to the site.
 *
 * @since 1.0.0
 *
 * @WordPress\action update-custom_wordpoints-install-module
 */
function wordpointsorg_admin_install_module() {

	global $title;

	if ( ! current_user_can( 'install_wordpoints_modules' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to install modules on this site.', 'wordpointsorg' ) );
	}

	if ( ! isset( $_GET['module_id'] ) ) {
		wp_die( esc_html__( 'You need to choose which module to install.', 'wordpointsorg' ) );
	}

	$module_id = (int) $_GET['module_id'];

	check_admin_referer( "install-module_{$module_id}" );

	$api = wordpointsorg_modules_api( array(), array( 'id' => $module_id ) );

	if ( is_wp_error( $api ) ) {
		wp_die( $api );
	}

	$title = __( 'Module Install', 'wordpointsorg' );

	require_once( ABSPATH . 'wp-admin/admin-header.php' );

	$url = 'update.php?action=wordpoints-install-module&module_id=' . $module_id;
	if ( isset( $_GET['from'] ) ) {
		$url .= '&from=' . urlencode( stripslashes( $_GET['from'] ) );
	}

	$upgrader = new WordPointsOrg_Module_Upgrader(
		new WordPoints_Module_Installer_Skin(
			array(
				'title' => sprintf( __( 'Installing Module: %s', 'wordpointsorg' ), $api->title . ' ' . $api->version ),
				'url'   => $url,
				'nonce' => 'install-module_' . $module_id,
				'type'  => 'web',
				'api'   => $api,
			)
		)
	);

	$upgrader->install( $upgrader->get_github_package_url( $api ) );

	include( ABSPATH . 'wp-admin/admin-footer.php' );
}
add_action( 'update-custom_wordpoints-install-module' , 'wordpointsorg_admin_install_module' );


// TODO This is probalby not needed.
function wordpointsorg_admin_upload_module() {

		case 'upload-plugin':
			if ( ! current_user_can( 'install_plugins' ) )
				wp_die( __( 'You do not have sufficient permissions to install plugins on this site.', 'wordpoints' ) );

			check_admin_referer( 'module-upload' );

			$file_upload = new File_Upload_Upgrader( 'modulezip', 'package' );

			$title = __( 'Upload Module', 'wordpoints' );
			$parent_file = 'plugins.php';
			$submenu_file = 'plugin-install.php';
			require_once( ABSPATH . 'wp-admin/admin-header.php' );

			$upgrader = new WordPoints_Module_Upgrader(
				new WordPoints_Module_Installer_Skin(
					array(
						'type'  => 'upload',
						'title' => sprintf( __( 'Installing Module from uploaded file: %s', 'wordpoints' ), esc_html( basename( $file_upload->filename ) ) ),
						'nonce' => 'plugin-upload',
						'url'   => add_query_arg( array( 'package' => $file_upload->id ), 'update.php?action=upload-plugin' ),
					)
				)
			);

			$result = $upgrader->install( $file_upload->package );

			if ( $result || is_wp_error( $result ) )
				$file_upload->cleanup();

			include( ABSPATH . 'wp-admin/admin-footer.php' );
		break;

	} // switch ( $action )
}
add_action( 'update-custom_wordpoints-activate-module', 'wordpoints_admin_module_upgrades' );
