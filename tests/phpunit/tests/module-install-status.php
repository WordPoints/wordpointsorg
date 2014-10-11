<?php

/**
 * A test case for wordpointsorg_module_install_status.
 *
 * @package WordPointsOrg\Tests
 * @since 1.0.0
 */

/**
 * Test the wordpointsorg_module_install_status() function.
 *
 * @since 1.0.0
 */
class WordPointsOrg_Module_Install_Status_Test extends WP_UnitTestCase {

	/**
	 * Set up for each test.
	 *
	 * @since 1.0.0
	 */
	public function setUp() {

		parent::setUp();

		add_filter(
			'pre_site_transient_wordpointsorg_update_modules'
			, array( $this, 'filter_wordpointsorg_update_modules_site_transient' )
		);
	}

	/**
	 * Tear down after each test.
	 *
	 * @since 1.0.0
	 */
	public function tearDown() {

		parent::tearDown();

		remove_filter(
			'pre_site_transient_wordpointsorg_update_modules'
			, array( $this, 'filter_wordpoints_update_modules_site_transient' )
		);
	}

	//
	// Tests.
	//

	/**
	 * Test that it returns 'not_installed' for a module that isn't installed.
	 *
	 * @since 1.0.0
	 */
	public function test_not_installed() {

		$module = array(
			'ID' => 1,
			'update_api' => 'wordpoints.org',
		);

		$this->assertEquals(
			array( 'status' => 'not_installed', 'url' => '' )
			, wordpointsorg_module_install_status( $module )
		);
	}

	/**
	 * Test that it returns 'update_available' for a module with available updates.
	 *
	 * @since 1.0.0
	 */
	public function test_update_available() {

		$module = array(
			'ID'         => 2,
			'update_api' => 'wordpoints.org',
			'version'    => '1.0.0',
		);

		$this->assertEquals(
			array( 'status' => 'update_available', 'url' => '', 'version' => '1.0.1' )
			, wordpointsorg_module_install_status( $module )
		);
	}

	/**
	 * Test that it returns 'latest_installed' for same version.
	 *
	 * @since 1.0.0
	 */
	public function test_latest_installed() {

		$module = array(
			'ID'         => 3,
			'update_api' => 'wordpoints.org',
			'version'    => '1.0.0',
		);

		$this->assertEquals(
			array( 'status' => 'latest_installed', 'url' => false )
			, wordpointsorg_module_install_status( $module )
		);
	}

	/**
	 * Test that it returns 'newer_installed' for newer version.
	 *
	 * @since 1.0.0
	 */
	public function test_newer_installed() {

		$module = array(
			'ID'         => 4,
			'update_api' => 'wordpoints.org',
			'version'    => '2.0.0',
		);

		$this->assertEquals(
			array( 'status' => 'newer_installed', 'url' => false, 'version' => '1.0.0' )
			, wordpointsorg_module_install_status( $module )
		);
	}

	/**
	 * Test that it returns 'wrong_service' for newer version.
	 *
	 * @since 1.0.0
	 */
	public function test_wrong_service() {

		$module = array(
			'ID'         => 5,
			'update_api' => 'github',
			'version'    => '1.0.0',
		);

		$this->assertEquals(
			array( 'status' => 'wrong_service', 'url' => '' )
			, wordpointsorg_module_install_status( $module )
		);
	}

	//
	// Helpers.
	//

	/**
	 * Override the module updates transient.
	 *
	 * @since 1.0.0
	 *
	 * @WordPress\filter pre_site_transient_wordpointsorg_update_modules Added by self::setUp();
	 */
	public function filter_wordpointsorg_update_modules_site_transient() {

		return array(
			'response' => array(
				array(
					'ID'         => 2,
					'update_api' => 'wordpoints.org',
					'version'    => '1.0.1',
				),
			),
		);
	}
}

// EOF
