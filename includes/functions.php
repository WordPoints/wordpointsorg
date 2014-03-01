<?php

/**
 * General functions.
 *
 * @package WordPoints_Module_Uninstall_Tester
 * @since 0.1.0
 */

/**
 * Pull in the option parser if we haven't already.
 */
require_once dirname( __FILE__ ) . '/wordpoints-module-uninstall-tester-phpunit-util-getopt.php';

/**
 * Check if the module uninstall unit tests are being run.
 *
 * @since 0.1.0
 *
 * @return bool Whether the module uninstall group is being run.
 */
function running_wordpoints_module_uninstall_tests() {

	static $uninstall_tests;

	if ( ! isset( $uninstall_tests ) ) {

		global $argv;

		$option_parser = new WordPoints_Module_Uninstall_Tester_PHPUnit_Util_Getopt( $argv );

		$uninstall_tests = $option_parser->running_uninstall_group();
	}

	return $uninstall_tests;
}
