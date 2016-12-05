<?php

/**
 * Main file of the WordPoints.org Modules module.
 *
 * @package WordPointsOrg
 * @version 1.1.4
 * @license GPLv2+
 */

WordPoints_Modules::register(
	'
		Module Name: WordPoints.org Modules
		Author:      WordPoints
		Author URI:  https://wordpoints.org/
		Module URI:  https://wordpoints.org/modules/wordpoints-org/
		Version:     1.1.4
		License:     GPLv2+
		Description: Update modules from WordPoints.org through your admin panel.
		Text Domain: wordpointsorg
		Domain Path: /languages
		Channel:     wordpoints.org
		ID:          389
	'
	, __FILE__
);

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

/**
 * Item container classes.
 *
 * @since 1.0.0
 */
require_once WORDPOINTSORG_DIR . '/includes/class-item-container.php';

if ( is_admin() ) {

	/**
	 * The administration related code.
	 *
	 * @since 1.0.0
	 */
	require_once WORDPOINTSORG_DIR . '/admin/admin.php';
}

// EOF
