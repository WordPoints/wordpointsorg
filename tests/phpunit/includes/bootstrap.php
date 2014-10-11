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
 * The WP plugin uninstall testing functions.
 *
 * We need this because it is a dependency of the module uninstall tester.
 *
 * @since 1.0.0
 */
require_once WORDPOINTSORG_TESTS_DIR . '/library/plugin-uninstall/includes/functions.php';

/**
 * The WordPoints modules uninstall testing functions.
 *
 * @since 1.0.0
 */
require_once WORDPOINTSORG_TESTS_DIR . '/library/module-uninstall/includes/functions.php';

/**
 * The WordPress tests functions.
 *
 * We are loading this so that we can add our tests filter to load the module and
 * WordPoints, using tests_add_filter().
 *
 * @since 1.0.0
 */
require_once getenv( 'WP_TESTS_DIR' ) . 'includes/functions.php';

if ( ! defined( 'WORDPOINTS_TESTS_DIR' ) ) {

	/**
	 * The WordPoints tests directory.
	 *
	 * We define it here if it isn't already defined, because it is used by the
	 * function that loads the plugin.
	 *
	 * @since 1.0.0
	 *
	 * @type string
	 */
	define( 'WORDPOINTS_TESTS_DIR', getenv( 'WORDPOINTS_TESTS_DIR' ) );
}

/**
 * The WordPoints tests functions.
 *
 * We need to load this so that we can load the plugin.
 *
 * @since 1.0.0
 */
require_once getenv( 'WORDPOINTS_TESTS_DIR' ) . 'includes/functions.php';

// Hook to load WordPoints.
tests_add_filter( 'muplugins_loaded', 'wordpointstests_manually_load_plugin' );

/**
 * The module's utilitiy functions for the tests.
 *
 * @since 1.0.0
 */
require_once WORDPOINTSORG_TESTS_DIR . '/includes/functions.php';

// If we aren't running the uninstall tests, we need to hook in to load the module.
if ( ! running_wordpoints_module_uninstall_tests() ) {
	tests_add_filter( 'muplugins_loaded', 'wordpointsorgtests_manually_load_module' );
}

/**
 * Sets up the WordPress test environment.
 *
 * We've got our action set up, so we can load this now, and it will load WordPress
 * and its test framework.
 *
 * @since 1.0.0
 */
require getenv( 'WP_TESTS_DIR' ) . '/includes/bootstrap.php';

/**
 * The WP plugin uninstall testing bootstrap.
 *
 * We need this because it is a dependency of the module uninstall tester.
 *
 * @since 1.0.0
 */
require_once WORDPOINTSORG_TESTS_DIR . '/library/plugin-uninstall/bootstrap.php';

/**
 * The WordPoints modules uninstall testing bootstrap.
 *
 * @since 1.0.0
 */
require_once WORDPOINTSORG_TESTS_DIR . '/library/module-uninstall/bootstrap.php';

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
