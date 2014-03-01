<?php

/**
 * WordPoints module uninstall test case.
 *
 * @package WordPoints_Module_Uninstall_Tester
 * @since 0.1.0
 */

/**
 * Test WordPoints module installation and uninstallation.
 *
 * @since 0.1.0
 */
abstract class WordPoints_Module_Uninstall_UnitTestCase extends WP_Plugin_Uninstall_UnitTestCase {

	//
	// Protected properties.
	//

	/**
	 * The full path to the main module file.
	 *
	 * @since 0.1.0
	 *
	 * @type string $module_file
	 */
	protected $module_file;

	//
	// Methods.
	//

	/**
	 * Run the module's install script.
	 *
	 * Called by the setUp() method.
	 *
	 * Installation is run seperately, so the module is never actually loaded in this
	 * process. This provides more realistic testing of the uninstall process, since
	 * it is run while the module is inactive, just like in "real life".
	 *
	 * @since 0.1.0
	 */
	protected function install() {

		// Activate the WordPoints plugin.
		$path = WORDPOINTS_TESTS_DIR . '/../../src/wordpoints.php';
		wp_register_plugin_realpath( $path );
		$plugins = wordpoints_get_array_option( 'active_plugins' );
		$plugins[] = plugin_basename( $path );
		update_option( 'active_plugins', $plugins );

		system(
			WP_PHP_BINARY
			. ' ' . escapeshellarg( dirname( dirname( __FILE__ ) ) . '/bin/install-module.php' )
			. ' ' . escapeshellarg( $this->module_file )
			. ' ' . escapeshellarg( $this->install_function )
			. ' ' . escapeshellarg( $this->locate_wp_tests_config() )
			. ' ' . (int) is_multisite()
			. ' ' . escapeshellarg( WP_PLUGIN_UNINSTALL_TESTER_DIR )
		);
	}

	/**
	 * Run the module's uninstall script.
	 *
	 * Call it and then run your uninstall assertions. You should always test
	 * installation before testing uninstallation.
	 *
	 * @since 0.1.0
	 */
	public function uninstall() {

		if ( empty( $this->module_file ) ) {
			exit( 'Error: $module_file property not set.' . PHP_EOL );
		}

		$this->plugin_file = $this->module_file;
		parent::uninstall();
	}
}
