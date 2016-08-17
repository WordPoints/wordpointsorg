<?php

/**
 * A test case for the WordPoints.org modules upgrader class' installer.
 *
 * @package WordPointsOrg\Tests
 * @since 1.0.0
 */

/**
 * Test that modules are installed correctly.
 *
 * @since 1.0.0
 *
 * @covers WordPointsOrg_Module_Upgrader::install
 */
class WordPointsOrg_Module_Upgrader_Install_Test extends WordPointsOrg_Module_Upgrader_UnitTestCase {

	/**
	 * Set up for each test.
	 *
	 * @since 1.0.0
	 */
	public function setUp() {

		parent::setUp();

		set_site_transient( 'wordpoints_module_updates', array( 'test' ) );
		wp_cache_set( 'wordpoints_modules', array( 'test' ), 'wordpoints_modules' );
	}

	/**
	 * Clean up after each test.
	 *
	 * @since 1.0.0
	 */
	public function tearDown() {

		global $wp_filesystem;

		if ( $wp_filesystem && $wp_filesystem->exists( wordpoints_modules_dir() . '/' . $this->package_name ) ) {
			$wp_filesystem->delete( wordpoints_modules_dir() . '/' . $this->package_name, true );
		}

		parent::tearDown();
	}

	/**
	 * Test that installation works.
	 *
	 * @since 1.0.0
	 */
	public function test_install() {

		$result = $this->install_test_package( 'module-6' );

		$this->assertTrue( $result );

		$this->assertFileExists( wordpoints_modules_dir() . '/module-6/module-6.php' );

		// Check that the module updates cache is cleared.
		$this->assertFalse( get_site_transient( 'wordpoints_module_updates' ) );
		$this->assertFalse( wp_cache_get( 'wordpoints_modules', 'wordpoints_modules' ) );

		$this->assertCount( 0, $this->skin->errors );
		$this->assertEquals( 1, $this->skin->header_shown );
		$this->assertEquals( 1, $this->skin->footer_shown );
	}

	/**
	 * Test the clear_update_cache argument.
	 *
	 * @since 1.0.0
	 */
	public function test_clear_update_cache() {

		$result = $this->install_test_package(
			'module-6'
			, array( 'clear_update_cache' => false )
		);

		$this->assertTrue( $result );

		$this->assertFileExists( wordpoints_modules_dir() . '/module-6/module-6.php' );

		// Check that the module updates cache is not cleared.
		$this->assertEquals( array( 'test' ), get_site_transient( 'wordpoints_module_updates' ) );

		// The modules cache is still cleared though.
		$this->assertFalse( wp_cache_get( 'wordpoints_modules', 'wordpoints_modules' ) );
	}

	/**
	 * Test that the package must contain a module to be installed.
	 *
	 * @since 1.0.0
	 */
	public function test_package_with_no_module() {

		$result = $this->install_test_package( 'no-module' );

		$this->assertInstanceOf( 'WP_Error', $result );
		$this->assertEquals( 'incompatible_archive_no_modules', $result->get_error_code() );

		$this->assertFileNotExists( wordpoints_modules_dir() . '/on-module/plugin.php' );
	}

	/**
	 * Test that it doesn't overwrite existing modules.
	 *
	 * @since 1.0.0
	 */
	public function test_doesnt_overwrite_existing() {

		$result = $this->install_test_package( 'module-7' );

		$this->assertInstanceOf( 'WP_Error', $result );
		$this->assertEquals( 'folder_exists', $result->get_error_code() );

		$this->assertFileExists( wordpoints_modules_dir() . '/module-7/module-7.php' );

		$this->package_name = 'do_not_delete';
	}

	//
	// Helpers.
	//

	/**
	 * Run the installer with one of the test packages.
	 *
	 * @since 1.0.0
	 *
	 * @param string $package_name The name of the package file, without extension.
	 * @param array  $args         Optional arguments to pass to install().
	 *
	 * @return mixed The result from the upgrader.
	 */
	protected function install_test_package( $package_name, $args = array() ) {

		$this->package_name = $package_name;

		$api = array(
			'ID'        => 15,
			'github_id' => 'WordPoints/test-module',
			'version'   => '1.0.0',
		);

		$upgrader = new WordPointsOrg_Module_Upgrader(
			$this->skin = new WordPointsOrg_Tests_Module_Installer_Skin(
				array(
					'title'  => 'Installing module',
					'url'    => '',
					'nonce'  => 'install-module_' . $api['ID'],
					'module' => $api['ID'],
					'type'   => 'web',
					'api'    => $api,
				)
			)
		);

		return $upgrader->install(
			WORDPOINTSORG_TESTS_DIR . '/data/module-packages/' . $package_name . '.zip'
			, $args
		);
	}
}

// EOF
