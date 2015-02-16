<?php

/**
 * A test case for wordpoints_get_api_for_module().
 *
 * @package WordPoints\Tests
 * @since 1.1.0
 */

/**
 * Test wordpoints_get_api_for_module().
 *
 * @since 1.1.0
 *
 * @covers ::wordpoints_get_api_for_module
 */
class WordPoints_Get_API_For_Module_Test extends WP_UnitTestCase {

	/**
	 * The channel used in the tests.
	 *
	 * @since 1.1.0
	 *
	 * @var WordPoints_Module_Channel
	 */
	protected $channel;

	/**
	 * The API used in the tests.
	 *
	 * @since 1.1.0
	 *
	 * @var WordPoints_Module_API
	 */
	protected $api;

	/**
	 * @since 1.1.0
	 */
	public function setUp() {

		parent::setUp();

		WordPoints_Module_APIs::init();

		$this->channel = WordPoints_Module_Channels::register(
			'wordpoints.test'
			, true
		);

		$this->api = WordPoints_Module_APIs::register(
			'test'
			, ''
			, 'WordPoints_Module_API_Mock'
		);

		$transient = 'wrdpnts_' . md5( 'module_channel_supports-wordpoints.test' );
		set_site_transient( $transient, 'test', WEEK_IN_SECONDS );
	}

	/**
	 * @since 1.1.0
	 */
	public function tearDown() {

		WordPoints_Module_Channels::deregister( 'wordpoints.test' );
		WordPoints_Module_APIs::deregister( 'test' );

		parent::tearDown();
	}

	/**
	 * Test that it returns the API.
	 *
	 * @since 1.1.0
	 */
	public function test_returns_api() {

		$module = array( 'channel' => $this->channel->url );

		$this->assertEquals(
			$this->api
			, wordpoints_get_api_for_module( $module )
		);
	}

	/**
	 * Test that it accepts a module file name instead of an array of module data.
	 *
	 * @since 1.1.0
	 */
	public function test_accepts_module_file() {

		$this->assertEquals(
			$this->api
			, wordpoints_get_api_for_module(
				WORDPOINTSORG_TESTS_DIR . '/data/test-module.php'
			)
		);
	}

	/**
	 * Test that it returns false if the channel doesn't exist.
	 *
	 * @since 1.1.0
	 */
	public function test_invalid_channel() {

		$this->assertFalse(
			wordpoints_get_api_for_module( array( 'channel' => 'invalid.com' ) )
		);
	}

	/**
	 * Test that it returns false if the API isn't supported.
	 *
	 * @since 1.1.0
	 */
	public function test_unsupported_api() {

		$transient = 'wrdpnts_' . md5( 'module_channel_supports-wordpoints.test' );
		set_site_transient( $transient, 'invalid', WEEK_IN_SECONDS );

		$this->assertFalse(
			wordpoints_get_api_for_module( array( 'channel' => 'wordpoints.test' ) )
		);
	}
}

// EOF
