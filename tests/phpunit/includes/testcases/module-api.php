<?php

/**
 * Parent class for the module API test cases.
 *
 * @package WordPointsOrg\Tests
 * @since 1.1.0
 */

/**
 * Parent test case for the module API tests.
 *
 * @since 1.1.0
 */
class WordPointsOrg_Module_API_UnitTestCase extends WordPointsOrg_HTTP_UnitTestCase {

	/**
	 * The slug of the API being tested.
	 *
	 * @since 1.1.0
	 *
	 * @var string
	 */
	protected $api_slug;

	/**
	 * The object for the API being tested.
	 *
	 * @since 1.1.0
	 *
	 * @var WordPoints_Module_API
	 */
	protected $api;

	/**
	 * The object for the channel to use in the tests.
	 *
	 * @since 1.1.0
	 *
	 * @var WordPoints_Module_Channel
	 */
	protected $channel;

	/**
	 * @since 1.1.0
	 */
	public static function setUpBeforeClass() {

		parent::setUpBeforeClass();

		if ( ! did_action( 'wordpoints_register_module_apis' ) ) {
			WordPoints_Module_APIs::init();
		}
	}

	/**
	 * @since 1.1.0
	 */
	public function setUp() {

		parent::setUp();

		add_filter( 'http_request_args', array( $this, 'add_module_api_header' ) );

		$this->channel = WordPoints_Module_Channels::register(
			'wordpoints.test'
			, true
		);

		$this->api = WordPoints_Module_APIs::get( $this->api_slug );

		$transient = 'wrdpnts_' . md5( "module_channel_supports_ssl-{$this->channel->url}" );
		set_site_transient( $transient, 0, WEEK_IN_SECONDS );
	}

	/**
	 * @since 1.1.0
	 */
	public function tearDown() {

		WordPoints_Module_Channels::deregister( 'wordpoints.test' );

		parent::tearDown();
	}

	/**
	 * Add a request header for the module API.
	 *
	 * @since 1.1.0
	 *
	 * @WordPress\filter http_request_args Added by self::setUp().
	 */
	public function add_module_api_header( $request ) {

		$request['headers']['X_WORDPOINTSORG_TESTS_API'] = $this->api_slug;

		// Normalize the URL.
		if ( isset( $request['body']['url'] ) ) {
			$request['body']['url'] = 'http://example.org';
		}
var_dump( $request );
		return $request;
	}
}

// EOF
