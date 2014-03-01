<?php

/**
 * Bootstrap to load the classes.
 *
 * Include this in the bootstrap for your module tests.
 *
 * @package WordPoints_Module_Uninstall_Tester
 * @since 0.1.0
 */

$dir = dirname( __FILE__ );

/**
 * The commandline options parser.
 */
require_once $dir . '/includes/wordpoints-module-uninstall-tester-phpunit-util-getopt.php';

/**
 * General functions.
 */
require_once $dir . '/includes/functions.php';

/**
 * The module install/uninstall test case.
 */
require_once $dir . '/includes/wordpoints-module-uninstall-unittestcase.php';

unset( $dir );
