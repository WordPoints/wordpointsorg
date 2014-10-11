<?php

/**
 * General functions of the module.
 *
 * @since 1.0.0
 */

/**
 * Activate the module.
 *
 * @since 1.0.0
 *
 * @param bool $network_active Whether the plugin is being network activated.
 */
function wordpointsorg_activate( $network_active ) {

	$wordpoints_data = wordpoints_get_array_option( 'wordpoints_data', 'network' );

	if ( ! isset( $wordpoints_data['modules']['wordpointsorg']['version'] ) ) {

		/**
		 * The module's install script.
		 *
		 * @since 1.0.0
		 */
		require WORDPOINTSORG_DIR . '/install.php';
	}
}
wordpoints_register_module_activation_hook( WORDPOINTSORG_DIR . 'wordpointsorg.php', 'wordpointsorg_activate' );

/**
 * Get the custom capabilities added by the module.
 *
 * The custom caps are keys, the corresponding core caps are values.
 *
 * @since 1.0.0
 *
 * @return array The module's custom capabilities.
 */
function wordpointsorg_get_custom_caps() {

	return array( 'update_wordpoints_modules' => 'update_plugins' );
}

/**
 * Map custom meta capabilities of the module.
 *
 * @since 1.0.0
 *
 * @filter map_meta_cap
 *
 * @param array  $caps The user's capabilities.
 * @param string $cap  The current capability in question.
 *
 * @return array The user's capabilities.
 */
function wordpointsorg_map_custom_meta_caps( $caps, $cap ) {

	switch ( $cap ) {
		case 'update_wordpoints_modules':
			if ( defined( 'DISALLOW_FILE_MODS' ) && DISALLOW_FILE_MODS ) {
				$caps[] = 'do_not_allow';
			} elseif ( is_multisite() && ! is_super_admin( $user_id ) ) {
				$caps[] = 'do_not_allow';
			} else {
				$caps[] = $cap;
			}
		break;
	}
}
add_filter( 'map_meta_cap', 'wordpointsorg_map_custom_meta_caps', 10, 2 );

/**
 * Determine the status we can perform on a module.
 *
 * @since 1.0.0
 *
 * @param array $api  The response for this module from the module API.
 * @param bool  $loop Whether the function is being called recursively.
 *
 * @return array {
 *         The module's status.
 *
 *         @type string $status  The install status of this module. Possible values:
 *                               'not_installed', 'latest_installed',
 *                               'newer_installed', 'update_available', and
 *                               'wrong_service'.
 *         @type string|false $url A URL to which the user may be redirected to take
 *                                 action, e.g., update or install the module. Only
 *                                 available if the user has the necessary caps to
 *                                 perform an available action, false otherwise.
 *         @type string $version The version of the update if available. Otherwise
 *                               not set.
 * }
 */
function wordpointsorg_module_install_status( $api, $loop = false ) {

	// Default to a "new" module.
	$status = 'not_installed';
	$url = false;

	// Check if this module is installed and has an update awaiting it.
	$update_modules = get_site_transient( 'wordpointsorg_update_modules' );

	if ( isset( $update_modules['response'] ) && is_array( $update_modules['response'] ) ) {

		foreach ( $update_modules['response'] as $module ) {

			if ( (int) $module['ID'] === (int) $api['ID'] ) {

				$status = 'update_available';
				$version = $module['version'];

				if ( current_user_can( 'update_wordpoints_modules' ) ) {
					$url = wp_nonce_url( self_admin_url( 'admin.php?page=wordpoints_install_modules&action=upgrade-module&module=' . $module['ID'] ), 'upgrade-module_' . $module['ID'] );
				}

				break;
			}
		}
	}

	if ( 'not_installed' === $status ) {

		$modules = wordpoints_get_modules();

		foreach ( $modules as $module ) {

			if (
				isset( $module['update_api'], $module['ID'] )
				&& 'wordpoints.org' === $module['update_api']
				&& (int) $module['ID'] === (int) $api['ID']
			) {

				switch ( version_compare( $api['version'], $module['version'] ) ) {

					// Versions are equal.
					case 0:
						$status = 'latest_installed';
					break;

					// Installed version is greater.
					case 1:
						$status = 'newer_installed';
						$version = $module['version'];
					break;

					// The API version is greater.
					default:
						if ( ! $loop ) {

							/* There should be an update for this module, but the
							 * above update check failed. That probably means that
							 * the update checker has out-of-date information, so
							 * we'll force a refresh.
							 */

							delete_site_transient( 'wordpoints_update_modules' );
							wordpointsorg_check_for_module_updates();
							return wordpoints_module_install_status( $api, true );
						}
				}
			}
		}

		if ( 'not_installed' === $status ) {
			if ( current_user_can( 'install_wordpoints_modules' ) ) {
				$url = wp_nonce_url(
					self_admin_url( 'admin.php?page=wordpoints_install_modules&action=install-module&update-api=wordpoints.org&module=' . $api['ID'] )
					, 'install-module_' . $api['ID']
				);
			}
		}
	}

	// TODO why?
	if ( $url && isset( $_GET['from'] ) ) {
		$url .= '&amp;from=' . urlencode( wp_unslash( $_GET['from'] ) );
	}

	return compact( 'status', 'url', 'version' );
}

/**
 * Check module versions against the latest versions hosted on WordPoints.org.
 *
 * Gets the latest info for all installed modules that have specified to use the
 * wordpoints.org Update API in the module header from wordpoints.org.
 *
 * The data is stored in a transient, which is updated by calling this function if it
 * has expired, or modules have been installed or updated.
 *
 * @since 1.0.0
 *
 * @return false|void Returns false if the check is too soon, or the request fails.
 */
function wordpointsorg_check_for_module_updates() {

	$modules = wp_list_filter(
		wordpoints_get_modules()
		, array( 'update_api' => 'wordpoints.org' )
	);

	$current = get_site_transient( 'wordpointsorg_update_modules' );

	if ( ! is_array( $current ) ) {
		$current = array();
	}

	$new_option = array();
	$new_option['last_checked'] = time();

	// Check for update on a different schedule, depending on the page.
	switch ( current_filter() ) {

		case 'upgrader_process_complete' :
			$timeout = 0;
		break;
// TODO actions
		case 'load-update-core.php' :
			$timeout = MINUTE_IN_SECONDS;
		break;

		case 'load-plugins.php' :
		case 'load-update.php' :
			$timeout = HOUR_IN_SECONDS;
		break;

		default :
			$timeout = 12 * HOUR_IN_SECONDS;
	}

	if ( isset( $current['last_checked'] ) && $timeout > ( time() - $current['last_checked'] ) ) {

		/* We have checked recently, so let's see if any modules' versions have
		 * changed since the last check, and if not, we'll bail out.
		 */

		$module_changed = false;

		foreach ( $modules as $module ) {

			$new_option['checked'][ $module['update_id'] ] = $module['version'];

			if (
				! isset( $current['checked'][ $module['update_id'] ] )
				|| $current['checked'][ $module['update_id'] ] !== $module['version']
			) {

				// This module is new, or it has been updated since we checked last.
				$module_changed = true;
				break;
			}
		}

		if ( ! $module_changed && isset( $current['response'] ) && is_array( $current['response'] ) ) {

			foreach ( $current['response'] as $module ) {

				if ( ! isset( $new_option['checked'][ $module['ID'] ] ) ) {

					// A module has been deleted, use it as an excuse to check again.
					$module_changed = true;
					break;
				}
			}
		}

		// Bail since we've checked recently and if nothing has changed.
		if ( ! $module_changed ) {
			return false;
		}
	}

	// Update last_checked for current to prevent multiple requests if request hangs.
	$current['last_checked'] = time();
	set_site_transient( 'wordpointsorg_update_modules', $current );

	$request = array();

	if ( isset( $new_option['checked'] ) ) {
		$request['post__in'] = array_keys( $new_option['checked'] );
	} else {
		$request['post__in'] = wp_list_pluck( $modules, 'ID' );
	}

	$response = wordpointsorg_modules_api( $request );

	if ( is_wp_error( $response ) ) {
		return false;
	}

	if ( is_array( $response ) ) {

		$new_option['response'] = $response;

	} else {

		$new_option['response'] = array();
	}

	set_site_transient( 'wordpointsorg_update_modules', $new_option );

} // function wordpoints_update_modules()

/**
 * Get installed translations for WordPoints extensions.
 *
 * Currently the only translations are for WordPoints Modules.
 *
 * @since $ver$
 *
 * @param string $type The type of extension to retrieve translations for.
 *
 * @return array An array of language data.
 */
function wordpoints_get_installed_translations( $type ) {

	if ( $type !== 'modules' )
		return array();

	$dir = "/wordpoints-{$type}";

	if ( ! is_dir( WP_LANG_DIR ) || ! is_dir( WP_LANG_DIR . $dir ) )
		return array();

	$files = scandir( WP_LANG_DIR . $dir );

	if ( ! $files )
		return array();

	$language_data = array();

	foreach ( $files as $file ) {

		if ( '.' === $file[0] || is_dir( $file ) || substr( $file, -3 ) !== '.po' )
			continue;

		if ( ! preg_match( '/(?:(.+)-)?([A-Za-z_]{2,6}).po/', $file, $match ) )
			continue;

		list( , $textdomain, $language ) = $match;

		if ( '' === $textdomain )
			$textdomain = 'default';

		$language_data[ $textdomain ][ $language ] = wp_get_pomo_file_data( WP_LANG_DIR . "$dir/$file" );
	}

	return $language_data;
}

/**
 * Retrieve plugin installer pages from WordPress Plugins API.
 *
 * It is possible for a module to override the Module API result with three
 * filters. Assume this is for modules, which can extend on the Module Info to
 * offer more choices. This is very powerful and must be used with care, when
 * overriding the filters.
 *
 * @since 1.0.0
 *
 * @param array $request {
 *       Optional request parameters to POST to the API endpoint.
 *
 *       @link todo
 *
 *       @type int $per_page The number of modules per page, default is 24.
 * }
 * @param array $args {
 *        Option arguments defining the type of request being made.
 *
 *        @type int         $id   The ID of a specific module to retrieve. Default:
 *                                null.
 * }
 *
 * @return object API response object on success, WP_Error on failure.
 */
function wordpointsorg_modules_api( array $request = array(), array $args = array() ) {

	if ( ! isset( $request['posts_per_page'] ) && ! isset( $args['id'] ) ) {
		$request['posts_per_page'] = 24;
	}

	/**
	 * Override the Module Install API request parameters.
	 *
	 * @since 1.0.0
	 *
	 * @param array $request Module API arguments.
	 * @param array $args    The type of information being requested from the Module
	 *                       Install API.
	 */
	$request = apply_filters( 'wordpointsorg_modules_api_request', $request, $args );

	/**
	 * Allows a plugin to override the WordPoints.org Module Install API entirely.
	 *
	 * @since 1.0.0
	 *
	 * @param false|array $response The result object. Default is false.
	 * @param array       $request  Module API arguments.
	 * @param array       $args     The type of information being requested from the
	 *                              Module Install API.
	 */
	$response = apply_filters( 'wordpointsorg_modules_api', false, $request, $args );

	if ( false === $response ) {
// TODO local
		$url = 'http://wordpoints.local/wp-json/modules/';
/* TODO HTTPS
		if ( wp_http_supports( array( 'ssl' ) ) ) {
			$url = set_url_scheme( $url, 'https' );
		}
*/
		if ( ! empty( $args['id'] ) ) {
			$url .= $args['id'];
		} else {
			$request = array( 'filter' => $request );
		}

		if ( false === $response ) {

			$_request = wp_remote_get(
				add_query_arg( $request, $url )
				, array( 'timeout' => 15 )
			);

			if ( is_wp_error( $_request ) ) {

				$response = new WP_Error( 'wordpointsorg_modules_api_failed', sprintf( __( 'An unexpected error occurred. Something may be wrong with WordPoints.org or this server&#8217;s configuration. If you continue to have problems, please try the <a href="%s">support forums</a>.', 'wordpoints' ), 'http://wordpress.org/support/' ), $_request->get_error_message() );

			} else {

				$response = json_decode( wp_remote_retrieve_body( $_request ), true );

				if ( ! is_array( $response ) ) {
					$response = new WP_Error( 'wordpointsorg_modules_api_failed', sprintf( __( 'An unexpected error occurred. Something may be wrong with WordPoints.org or this server&#8217;s configuration. If you continue to have problems, please try the <a href="%s">support forums</a>.', 'wordpoints' ), 'http://wordpress.org/support/' ), wp_remote_retrieve_body( $_request ) );
				} elseif ( empty( $args['id'] ) ) {
					$response = array( 'modules' => $response, 'total' => wp_remote_retrieve_header( $_request, 'x-wp-total' ) );
				}
			}
		}

	} elseif ( ! is_wp_error( $response ) ) {

		$response->external = true;
	}

	/**
	 * Filter the Module Install API response results.
	 *
	 * @since 1.0.0
	 *
	 * @param object|WP_Error $response Response object or WP_Error.
	 * @param array           $request  Module API arguments.
	 * @param array           $args     The type of information being requested from the Module Install API.
	 */
	return apply_filters( 'wordpoints_modules_api_result', $response, $request, $args );

} // function wordpoints_modules_api()

// EOF
