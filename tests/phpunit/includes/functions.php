<?php

/**
 * Utility functions used in PHPUnit testing.
 *
 * @package WordPointsOrg\Tests
 * @since 1.0.0
 */

/**
 * Manually load the module.
 *
 * @since 1.0.0
 */
function wordpointsorgtests_manually_load_module() {

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
tests_add_filter( 'wordpoints_modules_dir', 'wordpointsorgtests_modules_dir', 20 );

// EOF
