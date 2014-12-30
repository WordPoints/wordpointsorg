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
	extends WordPointsOrg_HTTP_UnitTestCase {

	/**
	 * @since 1.0.0
	 */
	public static function setUpBeforeClass() {

		parent::setUpBeforeClass();

		WordPoints_Module_APIs::init();
	}

	/**
	 * @since 1.0.0
	 */
	public function setUp() {

		parent::setUp();

		$this->channel = WordPoints_Module_Channels::register(
			'wordpoints.test'
			, true
		);

		$this->api = WordPoints_Module_APIs::get( 'edd-software-licensing' );
	}

	/**
	 * @since 1.0.0
	 */
	public function tearDown() {

		WordPoints_Module_Channels::deregister( 'wordpoints.test' );

		parent::tearDown();
	}

	/**
	 * Test that the EDD module API is registered.
	 *
	 * @since 1.0.0
	 */
	public function test_is_registered() {

		$this->assertArrayHasKey( 'edd-software-licensing', WordPoints_Module_APIs::get() );
	}

	/**
	 * Test that it supports the expected API functions.
	 *
	 * @since 1.0.0
	 */
	public function test_supports() {

		$this->assertTrue( $this->api->supports( 'updates' ) );
	}

	//
	// Responders.
	//

	/**
	 * Respond positively to a request to check if a channel supports this API.
	 *
	 * @since 1.0.0
	 */
	public function channel_supports_response( $request, $url ) {

		// First, we need to verify this request.
		$this->assertTrue( isset( $request['body']['is_free_supported'] ) );

		return $this->simulate_response( $request );
	}

	/**
	 *
	 *
	 * @since 1.0.0
	 */
	protected function simulate_response( $request, $url ) {

		$this->backup['_SERVER'] = $_SERVER;

		$_SERVER['USER_AGENT'] = $request['user-agent'];
		$_SERVER['REQUEST_URI'] = $url;

		$_POST = array();
		$_GET = array();
		$_COOKIES = array();

		if ( 'POST' === $request['method'] ) {
			$_POST = $request['body'];
		} else {
			$_GET = $request['body'];
		}

		$_REQUEST = array_merge( $_POST, $_GET, $_COOKIES );

		ob_start();

		try {
			$this->route_request();
		} catch ( WP_HTTP_Die_Exception $e ) {

		}

		return $this->simulated_response;
	}

	/**
	 *
	 *
	 * @since 1.0.0
	 */
	protected function route_request() {

		if ( isset( $_POST['edd_action'] ) ) {

			// This is so that we can hook into wp_die().
			if ( ! defined( 'DOING_AJAX' ) ) {
				define( 'DOING_AJAX', true );
			}

			add_filter( 'wp_die_ajax_handler', array( $this, 'wp_die_ajax_handler' ) );

			do_action( 'edd_' . $_POST['edd_action'], $_POST );

			return;
		}

		parent::route_request();
	}

	/**
	 *
	 *
	 * @since 1.0.0
	 */
	public function wp_die_ajax_handler( $message ) {

		$this->simulated_response = ob_get_clean();

		if ( ! is_scalar( $message ) ) {
			$message = '0';
		}

		$this->simulated_response .= $message;

		remove_filter( 'wp_die_ajax_handler', array( $this, __FUNCTION__ ) );

		throw new WP_HTTP_Die_Exception;
	}
}

class WP_HTTP_Die_Exception extends Exception {}

// EOF
