<?php

/**
 * A test case for wordpoints_check_for_module_updates().
 *
 * @package WordPointsOrg\Tests
 * @since 1.0.0
 */

/**
 * Test wordpoints_check_for_module_updates().
 *
 * @since 1.0.0
 *
 * @covers ::wordpoints_check_for_module_updates
 */
class WordPoints_Check_For_Module_Updates_Test extends WordPointsOrg_HTTP_UnitTestCase {

	/**
	 * @since 1.0.0
	 */
	public static function setUpBeforeClass() {

		parent::setUpBeforeClass();

		// Initialize the modules API, because it is hooked to admin_init which
		// doesn't fire before the tests run.
		WordPoints_Module_APIs::init();

		wordpoints_register_module_channels();
	}

	/**
	 * @since 1.0.0
	 */
	public function setUp() {

		parent::setUp();

		$this->wordpointsorg_modules = WordPoints_Module_Channels::get( 'wordpoints.org' )
			->modules->get();

		$this->http_responder = array( $this, 'respond_get_channel_api_header' );
	}

	/**
	 * @since 1.0.0
	 */
	public function tearDown() {

		$wordpoints_org = WordPoints_Module_Channels::get( 'wordpoints.org' );

		foreach ( $wordpoints_org->modules->get() as $file => $module ) {

			if ( ! isset( $this->wordpointsorg_modules[ $file ] ) ) {
				$wordpoints_org->modules->remove( $file );
			}
		}

		$to_add_back = array_diff_key(
			$this->wordpointsorg_modules
			, $wordpoints_org->modules->get()
		);

		foreach ( $to_add_back as $file => $module ) {
			$wordpoints_org->modules->add( $file, $module );
		}

		parent::tearDown();
	}

	public function respond_get_channel_api_header( $request, $url ) {

		if ( false !== strpos( $url, 'wordpoints.org' ) ) {

			return array(
				'headers' => array(
					'x-wordpoints-module-api' => 'edd-software-licensing',
				),
			);
		}
	}

	/**
	 * Test that it returns false if there are no modules supporting updates.
	 *
	 * @since 1.0.0
	 */
	public function test_no_modules_supporting_updates() {

		// Deregister the module that supports updates.
		WordPoints_Module_Channels::get( 'wordpoints.org' )
			->modules->remove( 'module-7/module-7.php' );

		$this->assertFalse( wordpoints_check_for_module_updates() );
	}

	/**
	 * Test that it bails out if recently checked and no module versions have changed.
	 *
	 * @since 1.0.0
	 */
	public function test_no_module_versions_changed() {

		set_site_transient(
			'wordpoints_module_updates'
			, array(
				'last_checked' => time(),
				'checked' => array( 'module-7/module-7.php' => '1.0.0' ),
			)
		);

		$this->assertFalse( wordpoints_check_for_module_updates() );
	}

	/**
	 * Test that it doesn't bail out if recently checked and module versions have changed.
	 *
	 * @since 1.0.0
	 */
	public function test_module_versions_changed() {

		set_site_transient(
			'wordpoints_module_updates'
			, array(
				'last_checked' => time(),
				'checked' => array( 'module-7/module-7.php' => '0.9.0' ),
			)
		);

		$this->assertNull( wordpoints_check_for_module_updates() );
	}

	/**
	 * Test that it doesn't bail out if recently checked and module been deleted.
	 *
	 * @since 1.0.0
	 */
	public function test_modules_deleted() {

		set_site_transient(
			'wordpoints_module_updates'
			, array(
				'last_checked' => time(),
				'checked' => array( 'module-7/module-7.php' => '0.9.0' ),
				'response' => array( 'module-34/module-34.php' => '0.5.5' ),
			)
		);

		$this->assertNull( wordpoints_check_for_module_updates() );
	}

	/**
	 * Test that it updates module updates cache.
	 *
	 * @since 1.0.0
	 */
	public function test_updates_module_updates_cache() {

		$this->http_responder = array( $this, 'respond_get_version' );

		update_site_option(
			'wordpoints_edd_sl_module_licenses'
			, array(
				'wordpoints.org' => array(
					'7' => array( 'license' => 'lkdjljlkj', 'status' => 'valid' )
				)
			)
		);

		$this->assertNull( wordpoints_check_for_module_updates() );

		$cache = get_site_transient( 'wordpoints_module_updates' );

		$this->assertInternalType( 'array', $cache );

		$this->assertArrayHasKey( 'checked', $cache );
		$this->assertInternalType( 'array', $cache['checked'] );
		$this->assertArrayHasKey( 'module-7/module-7.php', $cache['checked'] );
		$this->assertEquals( '1.0.0', $cache['checked']['module-7/module-7.php'] );

		$this->assertArrayHasKey( 'response', $cache );
		$this->assertInternalType( 'array', $cache['response'] );
		$this->assertArrayHasKey( 'module-7/module-7.php', $cache['response'] );
		$this->assertEquals( '1.1.0', $cache['response']['module-7/module-7.php'] );

		$this->assertArrayHasKey( 'last_checked', $cache );
		$this->assertInternalType( 'int', $cache['last_checked'] );
		$this->assertLessThanOrEqual( time() + 1, $cache['last_checked'] );
		$this->assertGreaterThanOrEqual( time() - 1, $cache['last_checked'] );
	}

	public function respond_get_version( $request, $url ) {

		if (
			isset( $request['body']['edd_action'] )
			&& 'get_version' === $request['body']['edd_action']
		) {

			return array(
				'body' => json_encode( array( 'new_version' => '1.1.0' ) )
			);

		} else {

			return $this->respond_get_channel_api_header( $request, $url );
		}
	}
}

// EOF
