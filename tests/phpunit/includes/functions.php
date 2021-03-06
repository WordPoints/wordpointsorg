<?php

/**
 * Utility functions used in PHPUnit testing.
 *
 * @package WordPointsOrg\Tests
 * @since 1.0.0
 */

/**
 * The module's tests directory.
 *
 * @since 1.0.0
 *
 * @type string
 */
define( 'WORDPOINTSORG_TESTS_DIR', dirname( dirname( __FILE__ ) ) );

/**
 * @since 1.1.0
 */
define( 'WP_HTTP_TC_CACHE_DIR', WORDPOINTSORG_TESTS_DIR . '/cache/wp-http-tc' );

/**
 * Manually load the module.
 *
 * @since 1.0.0
 * @deprecated 1.1.0
 */
function wordpointsorgtests_manually_load_module() {

	_deprecated_function( __FUNCTION__, '1.1.0' );

	require WORDPOINTSORG_TESTS_DIR . '/../../src/wordpointsorg.php';
	require WORDPOINTSORG_DIR . '/admin/admin.php';

	wordpointsorg_activate( is_multisite() && getenv( 'WORDPOINTS_NETWORK_ACTIVE' ) );
}

/**
 * Filter the modules directory to be the test modules folder.
 *
 * @since 1.0.0
 *
 * @filter wordpoints_modules_dir 20 After WordPoints' test modules directory filter.
 */
function wordpointsorgtests_modules_dir() {

	return WORDPOINTSORG_TESTS_DIR . '/data/modules/';
}

// EOF
