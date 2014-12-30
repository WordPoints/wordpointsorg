<?php

/**
 * A parent test case for the WordPoints.org modules upgrader class.
 *
 * @package WordPointsOrg\Tests
 * @since 1.0.0
 */

/**
 * Parent test case for the WordPoints.org module upgrader.
 *
 * @since 1.0.0
 */
abstract class WordPointsOrg_Module_Upgrader_UnitTestCase extends WordPointsOrg_HTTP_UnitTestCase {

	/**
	 * The name of the module package to use in the test.
	 *
	 * @since 1.0.0
	 *
	 * @type string $package_name
	 */
	protected $package_name;

	/**
	 * Set up before the class.
	 *
	 * @since 1.0.0
	 */
	public static function setUpBeforeClass() {

		parent::setUpBeforeClass();

		require_once( WORDPOINTSORG_DIR . '/admin/admin.php' );
		require_once( WORDPOINTS_DIR . '/admin/includes/class-wordpoints-module-installer-skin.php' );

		/**
		 * The module upgrader.
		 *
		 * @since 1.0.0
		 */
		require_once( WORDPOINTSORG_DIR . '/admin/includes/class-module-upgrader.php' );

		/**
		 * The module upgrader skin.
		 *
		 * @since 1.0.0
		 */
		require_once( WORDPOINTSORG_DIR . '/admin/includes/class-module-upgrader-skin.php' );

		/**
		 * The bulk module upgrader skin.
		 *
		 * @since 1.0.0
		 */
		require_once( WORDPOINTSORG_DIR . '/admin/includes/class-bulk-module-upgrader-skin.php' );

		/**
		 * A child class of the module installer skin to use in the tests.
		 *
		 * @since 1.0.0
		 */
		require_once( WORDPOINTSORG_TESTS_DIR . '/includes/module-installer-skin.php' );

		/**
		 * A child class of the module upgrader skin to use in the tests.
		 *
		 * @since 1.0.0
		 */
		require_once( WORDPOINTSORG_TESTS_DIR . '/includes/module-upgrader-skin.php' );

		/**
		 * A child class of the module bulk upgrader skin to use in the tests.
		 *
		 * @since 1.0.0
		 */
		require_once( WORDPOINTSORG_TESTS_DIR . '/includes/module-bulk-upgrader-skin.php' );

		// Initialize the modules API, because it is hooked to admin_init which
		// doesn't fire before the tests run.
		WordPoints_Module_APIs::init();

		wordpoints_register_module_channels();
	}

	/**
	 * Set up for each test.
	 *
	 * @since 1.0.0
	 */
	public function setUp() {

		parent::setUp();

		add_filter( 'filesystem_method', array( $this, 'use_direct_filesystem_method' ) );
		add_filter( 'upgrader_pre_download', array( $this, 'module_package' ) );

		set_site_transient( 'wordpoints_module_updates', array( 'test' ) );
		wp_cache_set( 'wordpoints_modules', array( 'test' ), 'wordpoints_modules' );

		$this->http_responder = array( $this, 'http_responder' );
	}

	/**
	 * Clean up after each test.
	 *
	 * @since 1.0.0
	 */
	public function tearDown() {

		global $wp_filesystem;

		if ( $wp_filesystem && $wp_filesystem->exists( wordpoints_modules_dir() . '/' . $this->package_name ) ) {
			$wp_filesystem->delete( wordpoints_modules_dir() . '/' .  $this->package_name, true );
		}

		ob_start();

		parent::tearDown();

		remove_filter( 'filesystem_method', array( $this, 'use_direct_filesystem_method' ) );
		remove_filter( 'upgrader_pre_download', array( $this, 'module_package' ) );
	}

	//
	// Helpers.
	//

	/**
	 * Use the direct filesystem method.
	 *
	 * @since 1.0.0
	 *
	 * @WordPress\filter filesystem_method Added by self::setUp().
	 */
	public function use_direct_filesystem_method() {

		return 'direct';
	}

	/**
	 * Get the path to the module package to use in the tests.
	 *
	 * @since 1.0.0
	 *
	 * @WordPress\filter upgrader_pre_download Added by self::setUp().
	 */
	public function module_package() {

		$package_name = WORDPOINTSORG_TESTS_DIR . '/data/module-packages/' . $this->package_name;

		if ( ! file_exists( $package_name . '.zip' ) ) {
			copy( $package_name . '.bk.zip', $package_name  . '.zip' );
		}

		return $package_name . '.zip';
	}

	public function http_responder( $request, $url ) {

		if ( 'HEAD' === $request['method'] && 'wordpoints.org' === $url ) {

			return array(
				'headers' => array(
					'x-wordpoints-module-api' => 'edd-software-licensing'
				)
			);
		}

		if ( null === $request['body'] && 'https://wordpoints.org' === $url ) {
			return array( 'response' => array( 'code' => 200 ) );
		}

		if ( isset( $request['body']['item_id'] ) && '7' === $request['body']['item_id'] ) {

			return array(
				'response' => array(
					'body' => json_encode(
						array(
							'new_version'   => '1.0.1',
							'name'          => 'Module 7',
							'slug'          => 'module-7',
							'url'           => 'https://wordpoints.org/modules/module-7/changelog/',
							'homepage'      => 'https://wordpoints.org/modules/module-7/',
							'package'       => 'https://wordpoints.org/?edd_action=package_download',
							'download_link' => 'https://wordpoints.org/?edd_action=package_download',
							'sections'      => array(
								'description' => 'A module, from seventh heaven.',
								'changelog'   => 'Things change.',
							),
						)
					),
				),
			);
		}

		if ( 0 === strpos( $url, 'https://api.wordpress.org/' ) ) {
			return array();
		}
	}
}

// EOF
