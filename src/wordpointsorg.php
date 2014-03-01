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
 * Module constants.
 *
 * @since 1.0.0
 */
require_once dirname( __FILE__ ) . '/includes/constants.php';

if ( is_admin() ) {

	/**
	 * The administration related code.
	 *
	 * @since 1.0.0
	 */
	require_once WORDPOINTSORG_DIR . '/admin/admin.php';
}
