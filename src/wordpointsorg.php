<?php

/**
 * Module Name: WordPoints.org Modules
 * Author:      WordPoints
 * Author URI:  http://wordpoints.org/
 * Plugin URI:  http://wordpoints.org/
 * Version:     1.0.0
 * License:     GPLv2+
 * Description: Install and update modules from WordPoints.org through your admin panel.
 *
 * @package WordPointsOrg
 * @version 1.0.0
 * @license GPLv2+
 */

if ( is_admin() ) {

	/**
	 * The administration related code.
	 *
	 * @since 1.0.0
	 */
	require_once dirname( __FILE__ ) . '/admin/admin.php';
}
