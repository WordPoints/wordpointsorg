<?php

/**
 * A test case for the EDD Software Licensing Free module API.
 *
 * @package WordPointsOrg\Tests
 * @since 1.1.0
 */

/**
 * Test that the EDD Software Licensing Free module API works.
 *
 * @since 1.1.0
 */
class WordPointsOrg_EDD_Software_Licensing_Free_Module_API_Test
	extends WordPointsOrg_Module_API_UnitTestCase {

	/**
	 * @since 1.1.0
	 */
	protected $api_slug = 'edd-software-licensing-free';

	/**
	 * @since 1.1.0
	 *
	 * @var WordPoints_EDD_Software_Licensing_Free_Module_API
	 */
	protected $api;

	/**
	 * Test that it supports the expected API functions.
	 *
	 * @since 1.1.0
	 *
	 * @coversNothing
	 */
	public function test_supports() {

		$this->assertTrue( $this->api->supports( 'updates' ) );
	}

	/**
	 * Test that is_free_module() uses the cached module info when available.
	 *
	 * @since 1.1.0
	 *
	 * @covers WordPoints_EDD_Software_Licensing_Free_Module_API::is_free_module
	 */
	public function test_is_free_module_cached_free() {

		update_site_option(
			'wordpoints_edd_sl_module_info'
			, array(
				$this->channel->url => array( '124' => array( 'is_free' => true ) )
			)
		);

		$this->assertTrue( $this->api->is_free_module( $this->channel, '124' ) );
	}

	/**
	 * Test that is_free_module() uses the cached module info when available.
	 *
	 * @since 1.1.0
	 *
	 * @covers WordPoints_EDD_Software_Licensing_Free_Module_API::is_free_module
	 */
	public function test_is_free_module_cached_not_free() {

		update_site_option(
			'wordpoints_edd_sl_module_info'
			, array(
				$this->channel->url => array( '123' => array( 'version' => '3' ) )
			)
		);

		$this->assertFalse( $this->api->is_free_module( $this->channel, '123' ) );
	}

	/**
	 * Test that is_free_module() makes a request to the channel when no cache.
	 *
	 * @since 1.1.0
	 *
	 * @covers WordPoints_EDD_Software_Licensing_Free_Module_API::is_free_module
	 */
	public function test_is_free_module_free() {

		$this->assertTrue( $this->api->is_free_module( $this->channel, '124' ) );

		$cache = get_site_option( 'wordpoints_edd_sl_module_info' );

		$this->assertArrayHasKey( 'wordpoints.test', $cache );
		$this->assertArrayHasKey( '124', $cache['wordpoints.test'] );
		$this->assertArrayHasKey( 'is_free', $cache['wordpoints.test']['124'] );
	}

	/**
	 * Test that is_free_module() makes a request to the channel when no cache.
	 *
	 * @since 1.1.0
	 *
	 * @covers WordPoints_EDD_Software_Licensing_Free_Module_API::is_free_module
	 */
	public function test_is_free_module_not_free() {

		$this->assertFalse( $this->api->is_free_module( $this->channel, '123' ) );

		$cache = get_site_option( 'wordpoints_edd_sl_module_info' );

		$this->assertArrayHasKey( 'wordpoints.test', $cache );
		$this->assertArrayHasKey( '123', $cache['wordpoints.test'] );
		$this->assertArrayNotHasKey( 'is_free', $cache['wordpoints.test']['123'] );
	}

	/**
	 * Test that module_has_valid_license() returns true for free modules.
	 *
	 * @since 1.1.0
	 *
	 * @covers WordPoints_EDD_Software_Licensing_Free_Module_API::module_has_valid_license
	 */
	public function test_free_modules_always_have_valid_licenses() {

		$this->assertTrue(
			$this->api->module_has_valid_license( $this->channel, '124' )
		);
	}
}

// EOF
