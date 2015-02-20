<?php

/**
 * A test case for the EDD Software Licensing module API.
 *
 * @package WordPointsOrg\Tests
 * @since 1.0.0
 */

/**
 * Test that the EDD Software Licensing module API works.
 *
 * @since 1.0.0
 */
class WordPointsOrg_EDD_Software_Licensing_Module_API_Test
	extends WordPointsOrg_Module_API_UnitTestCase {

	/**
	 * @since 1.1.0
	 */
	protected $api_slug = 'edd-software-licensing';

	/**
	 * @since 1.1.0
	 *
	 * @var WordPoints_EDD_Software_Licensing_Module_API
	 */
	protected $api;

	/**
	 * Test that the EDD module API is registered.
	 *
	 * @since 1.0.0
	 *
	 * @coversNothing
	 */
	public function test_is_registered() {

		$this->assertArrayHasKey( $this->api_slug, WordPoints_Module_APIs::get() );
	}

	/**
	 * Test that it supports the expected API functions.
	 *
	 * @since 1.0.0
	 *
	 * @coversNothing
	 */
	public function test_supports() {

		$this->assertTrue( $this->api->supports( 'updates' ) );
	}

	/**
	 * Test that the get_licenses() method gets all licenses from the database.
	 *
	 * @since 1.1.0
	 *
	 * @covers WordPoints_EDD_Software_Licensing_Module_API::get_licenses
	 */
	public function test_get_all_licenses() {

		$licenses = $this->add_module_licenses_option();

		$this->assertEquals( $licenses, $this->api->get_licenses() );
	}

	/**
	 * Test that the get_licenses() method can return licenses for just a channel.
	 *
	 * @since 1.1.0
	 *
	 * @covers WordPoints_EDD_Software_Licensing_Module_API::get_licenses
	 */
	public function test_get_channel_licenses() {

		$licenses = $this->add_module_licenses_option();

		$this->assertEquals(
			$licenses[ $this->channel->url ]
			, $this->api->get_licenses( $this->channel )
		);
	}

	/**
	 * Test that get_licenses() returns an empty array if the channel isn't set.
	 *
	 * @since 1.1.0
	 *
	 * @covers WordPoints_EDD_Software_Licensing_Module_API::get_licenses
	 */
	public function test_get_nonexistant_channel_licenses() {

		$this->assertEquals( array(), $this->api->get_licenses( $this->channel ) );
	}

	/**
	 * Test that update_licenses() saves the licenses in the database.
	 *
	 * @since 1.1.0
	 *
	 * @covers WordPoints_EDD_Software_Licensing_Module_API::update_licenses
	 */
	public function test_update_channel_licenses() {

		$licenses = array( '123' => array( 'status' => 'valid', 'license' => 'l' ) );

		$this->api->update_licenses( $this->channel, $licenses );

		$this->assertEquals(
			array( $this->channel->url => $licenses )
			, get_site_option( 'wordpoints_edd_sl_module_licenses' )
		);

		$this->assertEquals( $licenses, $this->api->get_licenses( $this->channel ) );
	}

	/**
	 * Test that get_module_license_data() returns the license data for a module.
	 *
	 * @since 1.1.0
	 *
	 * @covers WordPoints_EDD_Software_Licensing_Module_API::get_module_license_data
	 */
	public function test_get_module_license_data() {

		$licenses = $this->add_module_licenses_option();

		$this->assertEquals(
			$licenses[ $this->channel->url ]['123']
			, $this->api->get_module_license_data( $this->channel, '123' )
		);
	}

	/**
	 * Test that get_module_license_data() returns empty array if nonexistant module.
	 *
	 * @since 1.1.0
	 *
	 * @covers WordPoints_EDD_Software_Licensing_Module_API::get_module_license_data
	 */
	public function test_get_nonexistant_modules_license_data() {

		$this->add_module_licenses_option();

		$this->assertEquals(
			array()
			, $this->api->get_module_license_data( $this->channel, '545' )
		);
	}

	/**
	 * Test that get_module_license_data() can get just a piece of data.
	 *
	 * @since 1.1.0
	 *
	 * @covers WordPoints_EDD_Software_Licensing_Module_API::get_module_license_data
	 */
	public function test_get_module_license_data_by_key() {

		$licenses = $this->add_module_licenses_option();

		$this->assertEquals(
			$licenses[ $this->channel->url ]['123']['status']
			, $this->api->get_module_license_data( $this->channel, '123', 'status' )
		);
	}

	/**
	 * Test that get_module_license_data() returns null when getting a piece of data
	 * for a nonexistant module.
	 *
	 * @since 1.1.0
	 *
	 * @covers WordPoints_EDD_Software_Licensing_Module_API::get_module_license_data
	 */
	public function test_get_nonexistant_modules_license_data_by_key() {

		$this->assertEquals(
			null
			, $this->api->get_module_license_data( $this->channel, '123', 'status' )
		);
	}

	/**
	 * Test that get_module_license_data() returns null when getting a piece of data
	 * for a nonexistant key.
	 *
	 * @since 1.1.0
	 *
	 * @covers WordPoints_EDD_Software_Licensing_Module_API::get_module_license_data
	 */
	public function test_get_module_license_data_by_nonexistant_key() {

		$this->assertEquals(
			null
			, $this->api->get_module_license_data( $this->channel, '123', 'none' )
		);
	}

	/**
	 * Test that update_module_license_data() saves the data in the database.
	 *
	 * @since 1.1.0
	 *
	 * @covers WordPoints_EDD_Software_Licensing_Module_API::update_module_license_data
	 */
	public function test_update_module_license_data() {

		$data = array( 'status' => 'valid', 'license' => 'llll' );

		$this->api->update_module_license_data( $this->channel, '123', $data );

		$this->assertEquals(
			array( $this->channel->url => array( '123' => $data ) )
			, get_site_option( 'wordpoints_edd_sl_module_licenses' )
		);

		$this->assertEquals(
			$data
			, $this->api->get_module_license_data( $this->channel, '123' )
		);
	}

	/**
	 * Test that update_module_license_data() can save just a piece of data.
	 *
	 * @since 1.1.0
	 *
	 * @covers WordPoints_EDD_Software_Licensing_Module_API::update_module_license_data
	 */
	public function test_update_module_license_data_by_key() {

		$this->add_module_licenses_option();

		$this->api->update_module_license_data(
			$this->channel
			, '123'
			, __METHOD__
			, 'license'
		);

		$licenses = get_site_option( 'wordpoints_edd_sl_module_licenses' );

		$this->assertEquals(
			__METHOD__
			, $licenses[ $this->channel->url ]['123']['license']
		);

		$this->assertEquals(
			__METHOD__
			, $this->api->get_module_license_data( $this->channel, '123', 'license' )
		);
	}

	/**
	 * Test that module_has_valid_license() returns true if a module has a valid license.
	 *
	 * @since 1.1.0
	 *
	 * @covers WordPoints_EDD_Software_Licensing_Module_API::module_has_valid_license
	 */
	public function test_module_has_valid_license() {

		$this->add_module_licenses_option();

		$this->assertTrue(
			$this->api->module_has_valid_license( $this->channel, '123' )
		);
	}

	/**
	 * Test that module_has_valid_license() returns false if a module doesn't exist.
	 *
	 * @since 1.1.0
	 *
	 * @covers WordPoints_EDD_Software_Licensing_Module_API::module_has_valid_license
	 */
	public function test_nonexistant_module_has_valid_license() {

		$this->assertFalse(
			$this->api->module_has_valid_license( $this->channel, '123' )
		);
	}

	/**
	 * Test that module_has_valid_license() returns if the 'license' key isn't set.
	 *
	 * @since 1.1.0
	 *
	 * @covers WordPoints_EDD_Software_Licensing_Module_API::module_has_valid_license
	 */
	public function test_module_has_no_valid_license() {

		$this->api->update_module_license_data(
			$this->channel
			, '123'
			, 'valid'
			, 'status'
		);

		$this->assertFalse(
			$this->api->module_has_valid_license( $this->channel, '123' )
		);
	}

	/**
	 * Test that module_has_valid_license() returns false if the license is invalid.
	 *
	 * @since 1.1.0
	 *
	 * @covers WordPoints_EDD_Software_Licensing_Module_API::module_has_valid_license
	 */
	public function test_module_has_invalid_license() {

		$this->api->update_module_license_data(
			$this->channel
			, '123'
			, array( 'status' => 'expired', 'license' => 'lkjkjkj' )
		);

		// The status must be 'valid', this license is expired.
		$this->assertFalse(
			$this->api->module_has_valid_license( $this->channel, '123' )
		);
	}

	/**
	 * Test that activate_license() saves the license's 'valid' status in the db.
	 *
	 * @since 1.1.0
	 *
	 * @covers WordPoints_EDD_Software_Licensing_Module_API::activate_license
	 */
	public function test_activate_valid_license() {

		$this->api->update_module_license_data(
			$this->channel
			, '123'
			, 'testkey'
			, 'license'
		);

		$result = $this->api->activate_license(
			$this->channel
			, array( 'ID' => '123', 'name' => '' )
		);

		$this->assertEquals( 'valid', $result );

		$this->assertEquals(
			'valid'
			, $this->api->get_module_license_data( $this->channel, '123', 'status' )
		);
	}

	/**
	 * Test that activate_license() saves the license's 'invalid' status in the db.
	 *
	 * @since 1.1.0
	 *
	 * @covers WordPoints_EDD_Software_Licensing_Module_API::activate_license
	 */
	public function test_activate_invalid_license() {

		$result = $this->api->activate_license(
			$this->channel
			, array( 'ID' => '123', 'name' => '' )
		);

		$this->assertEquals( 'invalid', $result );

		$this->assertEquals(
			'invalid'
			, $this->api->get_module_license_data( $this->channel, '123', 'status' )
		);
	}

	/**
	 * Test that deactivate_license() saves the license's 'deactivated' status in the db.
	 *
	 * @since 1.1.0
	 *
	 * @covers WordPoints_EDD_Software_Licensing_Module_API::deactivate_license
	 */
	public function test_deactivate_valid_license() {

		$this->api->update_module_license_data(
			$this->channel
			, '123'
			, 'testkey_2'
			, 'license'
		);

		$result = $this->api->deactivate_license(
			$this->channel
			, array( 'ID' => '123', 'name' => '' )
		);

		$this->assertEquals( 'deactivated', $result );

		$this->assertEquals(
			'deactivated'
			, $this->api->get_module_license_data( $this->channel, '123', 'status' )
		);
	}

	/**
	 * Test that deactivate_license() doesn't save an invalid license's status in the db.
	 *
	 * @since 1.1.0
	 *
	 * @covers WordPoints_EDD_Software_Licensing_Module_API::deactivate_license
	 */
	public function test_deactivate_invalid_license() {

		$this->api->update_module_license_data(
			$this->channel
			, '123'
			, 'lkjljl'
			, 'license'
		);

		$result = $this->api->deactivate_license(
			$this->channel
			, array( 'ID' => '123', 'name' => '' )
		);

		$this->assertEquals( 'failed', $result );

		$this->assertEquals(
			''
			, $this->api->get_module_license_data( $this->channel, '123', 'status' )
		);
	}

	/**
	 * Test that check_license() returns a license's 'valid' status.
	 *
	 * @since 1.1.0
	 *
	 * @covers WordPoints_EDD_Software_Licensing_Module_API::check_license
	 */
	public function test_check_valid_active_license() {

		$this->api->update_module_license_data(
			$this->channel
			, '123'
			, 'testkey_2'
			, 'license'
		);

		$result = $this->api->check_license(
			$this->channel
			, array( 'ID' => '123', 'name' => '' )
		);

		$this->assertEquals( 'valid', $result );
	}

	/**
	 * Test that check_license() returns a license's 'inactive' status.
	 *
	 * @since 1.1.0
	 *
	 * @covers WordPoints_EDD_Software_Licensing_Module_API::check_license
	 */
	public function test_check_inactive_active_license() {

		$this->api->update_module_license_data(
			$this->channel
			, '123'
			, 'testkey'
			, 'license'
		);

		$result = $this->api->check_license(
			$this->channel
			, array( 'ID' => '123', 'name' => '' )
		);

		$this->assertEquals( 'inactive', $result );
	}

	//
	// Helpers.
	//

	/**
	 * Add module licenses to the database.
	 *
	 * @since 1.1.0
	 *
	 * @return array The module licenses that were added.
	 */
	protected function add_module_licenses_option() {

		$licenses = array(
			$this->channel->url => array(
				'123' => array( 'status' => 'valid', 'license' => 'lkjlkjklj' ),
				'45'  => array( 'status' => 'invalid' ),
			),
			'example.com' => array(
				'the_plug' => array( 'status' => 'valid', 'license' => 'lkjjjjj' ),
			),
		);

		update_site_option( 'wordpoints_edd_sl_module_licenses', $licenses );

		return $licenses;
	}
}

// EOF
