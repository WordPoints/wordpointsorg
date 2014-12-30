<?php

/**
 * PHPUnit tests bootstrap for the module.
 *
 * @package WordPointsOrg\Tests
 * @since 1.0.0
 */

if ( ! getenv( 'WP_TESTS_DIR' ) ) {
	exit( '$_ENV["WP_TESTS_DIR"] is not set.' . PHP_EOL );
} elseif ( ! getenv( 'WORDPOINTS_TESTS_DIR' ) ) {
	exit( '$_ENV["WORDPOINTS_TESTS_DIR"] is not set.' . PHP_EOL );
}

/**
 * The module's tests directory.
 *
 * @since 1.0.0
 *
 * @type string
 */
define( 'WORDPOINTSORG_TESTS_DIR', dirname( dirname( __FILE__ ) ) );

/**
 * We're running tests for a module.
 *
 * We need to tell WordPoints' tests bootstrap this so that it won't load it's plugin
 * uninstall tester.
 *
 * @since 1.0.0
 */
define( 'RUNNING_WORDPOINTS_MODULE_TESTS', true );

/**
 * The plugin uninstall testing functions.
 *
 * @since 1.0.0
 */
require_once WORDPOINTSORG_TESTS_DIR . '/../../vendor/jdgrimes/wp-plugin-uninstall-tester/includes/functions.php';

/**
 * The WordPoints modules uninstall testing functions.
 *
 * @since 1.0.0
 */
require_once WORDPOINTSORG_TESTS_DIR . '/../../vendor/wordpoints/module-uninstall-tester/includes/functions.php';

/**
 * The WordPress tests functions.
 *
 * We are loading this so that we can add our tests filter to load the module and
 * WordPoints, using tests_add_filter().
 *
 * @since 1.0.0
 */
require_once getenv( 'WP_TESTS_DIR' ) . 'includes/functions.php';

/**
 * The module's utilitiy functions for the tests.
 *
 * @since 1.0.0
 */
require_once WORDPOINTSORG_TESTS_DIR . '/includes/functions.php';

// If we aren't running the uninstall tests, we need to hook in to load the module.
if ( ! running_wordpoints_module_uninstall_tests() ) {

	// Hook to load WordPoints.
	tests_add_filter( 'muplugins_loaded', 'wordpointstests_manually_load_plugin' );

	// Hook to load the module.
	tests_add_filter( 'wordpoints_modules_loaded', 'wordpointsorgtests_manually_load_module', 5 );
}

/**
 * The WordPoints tests bootstrap.
 *
 * @since 1.0.0
 */
require getenv( 'WORDPOINTS_TESTS_DIR' ) . '/includes/bootstrap.php';

/**
 * The plugin uninstall testing bootstrap.
 *
 * @since 1.0.0
 */
require_once WORDPOINTSORG_TESTS_DIR . '/../../vendor/jdgrimes/wp-plugin-uninstall-tester/bootstrap.php';

/**
 * The WordPoints modules uninstall testing bootstrap.
 *
 * @since 1.0.0
 */
require_once WORDPOINTSORG_TESTS_DIR . '/../../vendor/wordpoints/module-uninstall-tester/bootstrap.php';

/**
 * A parent test case for tests involving HTTP requests.
 *
 * @since 1.0.0
 */
require_once( WORDPOINTSORG_TESTS_DIR . '/includes/testcase-http.php' );

/**
 * A parent test case for tests involving the module upgrader.
 *
 * @since 1.0.0
 */
require_once( WORDPOINTSORG_TESTS_DIR . '/includes/testcase-module-upgrader.php' );

// EOF
