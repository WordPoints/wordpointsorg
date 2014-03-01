<?php

/**
 * Module Name: WordPoints.org Modules
 * Author:      WordPoints
 * Author URI:  http://wordpoints.org/
 * Plugin URI:  http://wordpoints.org/
 * Version:     1.0.0-alpha
 * License:     GPLv2+
 * Description: Install and update modules from WordPoints.org through your admin panel.
 *
 * @package WordPointsOrg
 * @version 1.0.0-alpha
 * @license GPLv2+
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
wordpoints_register_module_activation_hook( __FILE__, 'wordpointsorg_activate' );

/**
 * Module constants.
 *
 * @since 1.0.0
 */
require_once dirname( __FILE__ ) . '/includes/constants.php';

/**
 * General functions.
 *
 * @since 1.0.0
 */
require_once WORDPOINTSORG_DIR . '/includes/functions.php';

if ( is_admin() ) {

	/**
	 * The administration related code.
	 *
	 * @since 1.0.0
	 */
	require_once WORDPOINTSORG_DIR . '/admin/admin.php';
}
