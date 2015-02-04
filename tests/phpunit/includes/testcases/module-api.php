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

		$this->channel = WordPoints_Module_Channels::register(
			'wordpoints.test'
			, true
		);

		$this->api = WordPoints_Module_APIs::get( $this->api_slug );
	}

	/**
	 * @since 1.1.0
	 */
	public function tearDown() {

		WordPoints_Module_Channels::deregister( 'wordpoints.test' );

		parent::tearDown();
	}
}

// EOF
