<?php

/**
 * PHPUnit tests bootstrap for the module.
 *
 * @package WordPointsOrg\Tests
 * @since 1.0.0
 */

/**
 * The WP HTTP testcase bootstrap.
 *
 * @since 1.1.0
 */
require_once WORDPOINTSORG_TESTS_DIR . '/../../vendor/jdgrimes/wp-http-testcase/wp-http-testcase.php';

if ( ! running_wordpoints_module_uninstall_tests() ) {
	WP_HTTP_TestCase::init();
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
