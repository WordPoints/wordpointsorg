<?php

/**
 * An extension of PHPUnit's commandline option parsing utility.
 *
 * @package WordPoints_Module_Uninstall_Tester
 * @since 0.1.0
 */

/**
 * Check the 'group' long option to see if we are running the uninstall group.
 *
 * @since 0.1.0
 */
class WordPoints_Module_Uninstall_Tester_PHPUnit_Util_Getopt extends WP_Plugin_Uninstall_Tester_PHPUnit_Util_Getopt {

	/**
	 * Parse the options to see if we are running the uninstall group.
	 *
	 * @since 0.1.0
	 *
	 * @param array $argv The commandline arguments.
	 */
	public function __construct( $argv ) {

		ob_start();
		parent::__construct( $argv );
		ob_end_clean();

		if ( ! $this->uninstall_group ) {
			echo 'Not running module install/uninstall tests... To execute these, use --group uninstall.' . PHP_EOL;
		}
	}
}
