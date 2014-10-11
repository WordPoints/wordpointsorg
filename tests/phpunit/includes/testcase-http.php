<?php

/**
 * A test case parent for testing HTTP requests.
 *
 * @package WordPointsOrg\Tests
 * @since 1.0.0
 */

/**
 * Parent test case for tests involving HTTP requests.
 *
 * @since 1.0.0
 */
abstract class WordPointsOrg_HTTP_UnitTestCase extends WP_UnitTestCase {

	/**
	 * The HTTP requests caught.
	 *
	 * @since 1.0.0
	 *
	 * @type array $http_requests {{
	 *       @type string $url     The URL for the request.
	 *       @type array  $request The request arguements.
	 * }}
	 */
	protected $http_requests;

	/**
	 * A function to simulate responses to requests.
	 *
	 * @since 1.0.0
	 *
	 * @type callable|false $http_responder
	 */
	protected $http_responder;

	/**
	 * Set up for each test.
	 *
	 * @since 1.0.0
	 */
	public function setUp() {

		 parent::setUp();

		 $this->http_requests = array();
		 $this->http_responder = false;

		 add_filter( 'pre_http_request', array( $this, 'http_request_listner' ), 10, 3 );
	}

	/**
	 * Clean up the filters after each test.
	 *
	 * @sicne 1.0.0
	 */
	public function tearDown() {

		parent::tearDown();

		remove_filter( 'pre_http_request', array( $this, 'http_request_listner' ), 10, 3 );
	}

	//
	// Helpers.
	//

	/**
	 * Mock responses to HTTP requests coming from WordPress.
	 *
	 * @since 1.0.0
	 *
	 * @WordPress\filter pre_http_request Added by self::setUp().
	 *
	 * @param mixed  $preempt Response to the request, or false to not preempt it.
	 * @param array  $request The request arguments.
	 * @param string $url     The URL the request is being made to.
	 *
	 * @return mixed A response, or false.
	 */
	public function http_request_listner( $preempt, $request, $url ) {

		$this->http_requests[] = array( 'url' => $url, 'request' => $request );

		if ( $this->http_responder ) {
			$preempt = call_user_func( $this->http_responder, $request, $url );
		}

		return $preempt;
	}
}

// EOF
