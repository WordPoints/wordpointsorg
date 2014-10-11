<?php

/**
 * A test case for the WordPoints.org modules upgrader class' upgrader.
 *
 * @package WordPointsOrg\Tests
 * @since 1.0.0
 */

/**
 * Test that modules are upgraded correctly.
 *
 * @since 1.0.0
 */
class WordPointsOrg_Module_Upgrader_Upgrade_Test extends WordPointsOrg_Module_Upgrader_UnitTestCase {

	/**
	 * Set up for each test.
	 *
	 * @since 1.0.0
	 */
	public function setUp() {

		parent::setUp();

		set_site_transient(
			'wordpointsorg_update_modules'
			, array(
				'response' => array(
					'7' => array(
						'ID' => '7',
						'github_id' => 'WordPoints/module-7',
						'version' => '1.0.1',
					),
				),
			)
		);

		wp_cache_delete( 'wordpoints_modules', 'wordpoints_modules' );
	}

	/**
	 * Clean up after each test.
	 *
	 * @since 1.0.0
	 */
	public function tearDown() {

		global $wp_filesystem;

		if ( substr( $this->package_name, -7, 7 ) === '-update' && $wp_filesystem ) {

			$module_name = substr( $this->package_name, 0, -7 );

			$wp_filesystem->copy(
				WORDPOINTSORG_TESTS_DIR . '/data/module-packages/' . $module_name . '/' . $module_name . '.php'
				, wordpoints_modules_dir() . $module_name . '/' . $module_name . '.php'
				, true
			);
		}

		parent::tearDown();
	}

	/**
	 * Test the upgrader.
	 *
	 * @since 1.0.0
	 */
	public function test_upgrade() {

		$result = $this->upgrade_test_module(
			'module-7/module-7.php'
			, 'module-7-update'
		);

		$this->assertTrue( $result );

		$this->assertCount( 0, $this->skin->errors );
		$this->assertEquals( 1, $this->skin->header_shown );
		$this->assertEquals( 1, $this->skin->footer_shown );
	}

	/**
	 * Test with a package that doesn't contain a module.
	 *
	 * @since 1.0.0
	 */
	public function test_package_with_no_module() {

		$result = $this->upgrade_test_module(
			'module-7/module-7.php'
			, 'no-module'
		);

		$this->assertWPError( $result );
		$this->assertEquals( 'incompatible_archive_no_modules', $result->get_error_code() );

		$this->assertCount( 1, $this->skin->errors );
	}

	/**
	 * Test the clear_update_cache argument.
	 *
	 * @since 1.0.0
	 */
	public function test_clear_update_cache() {

		$result = $this->upgrade_test_module(
			'module-7/module-7.php'
			, 'module-7-update'
			, array()
			, array( 'clear_update_cache' => false )
		);

		$this->assertTrue( $result );

		// Check that the module updates cache is not cleared.
		$this->assertArrayHasKey( 'response', get_site_transient( 'wordpointsorg_update_modules' ) );

		// The modules cache is still cleared though.
		$this->assertFalse( wp_cache_get( 'wordpoints_modules', 'wordpoints_modules' ) );
	}

	/**
	 * Test with a module that isn't installed.
	 *
	 * @since 1.0.0
	 */
	public function test_not_installed() {

		$result = $this->upgrade_test_module(
			'module-6/module-6.php'
			, 'module-6'
			, array( 'ID' => 6 )
		);

		$this->assertFalse( $result );
		$this->assertCount( 1, $this->skin->errors );
		$this->assertEquals( 'not_installed', $this->skin->errors[0] );
	}

	/**
	 * Test with a module that is already up to date.
	 *
	 * @since 1.0.0
	 */
	public function test_up_to_date() {

		$result = $this->upgrade_test_module(
			'module-8/module-8.php'
			, 'module-8-not-really'
			, array( 'ID' => 8 )
		);

		$this->assertFalse( $result );
		$this->assertCount( 1, $this->skin->errors );
		$this->assertEquals( 'up_to_date', $this->skin->errors[0] );
	}

	//
	// Helpers.
	//

	/**
	 * Upgrade a test module.
	 *
	 * @since 1.0.0
	 *
	 * @param string $module       The basename module path.
	 * @param string $package_name The filename of the package to use.
	 * @param array  $api          Optionally override the default API array used.
	 * @param array  $args         Optional arguments passed to upgrade().
	 */
	public function upgrade_test_module( $module, $package_name, $api = array(), $args = array() ) {

		$this->package_name = $package_name;

		$api = array_merge(
			array(
				'ID'        => 7,
				'github_id' => 'WordPoints/test-module',
				'version'   => '1.0.0',
			)
			, $api
		);

		$this->skin = new WordPointsOrg_Tests_Module_Upgrader_Skin(
			array(
				'title'  => 'Installing module',
				'url'    => '',
				'nonce'  => 'install-module_' . $api['ID'],
				'module' => $module,
				'type'   => 'web',
				'api'    => $api,
			)
		);

		$upgrader = new WordPointsOrg_Module_Upgrader( $this->skin );

		return $upgrader->upgrade( $module, $args );
	}
}

// EOF
