<?php

/**
 * PHPUnit tests bootstrap for the module.
 *
 * @package WordPointsOrg\Tests
 * @since 1.0.0
 */

if ( ! WordPoints_PHPUnit_Bootstrap_Loader::instance()->running_uninstall_tests() ) {

	WP_HTTP_TestCase::init();

	add_filter( 'wordpoints_modules_dir', 'wordpointsorgtests_modules_dir', 20 );
}

/**
 * A parent test case for tests involving HTTP requests.
 *
 * @since 1.0.0
 */
require_once( WORDPOINTSORG_TESTS_DIR . '/includes/testcases/http.php' );

/**
 * A parent test case for the module API tests.
 *
 * @since 1.1.0
 */
require_once( WORDPOINTSORG_TESTS_DIR . '/includes/testcases/module-api.php' );

/**
 * A parent test case for tests involving the module upgrader.
 *
 * @since 1.0.0
 */
require_once( WORDPOINTSORG_TESTS_DIR . '/includes/testcases/module-upgrader.php' );

if ( class_exists( 'WordPoints_Module_APIs' ) ) {

	WordPoints_Module_APIs::init();

	/**
	 * A mock for module APIs.
	 *
	 * @since 1.0.0
	 */
	require_once( WORDPOINTSORG_TESTS_DIR . '/includes/mocks/module-api.php' );
}

// EOF
