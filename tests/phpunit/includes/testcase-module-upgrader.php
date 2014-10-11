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
		add_filter( 'wordpointsorg_github_module_package_url', array( $this, 'module_package' ) );

		set_site_transient( 'wordpointsorg_update_modules', array( 'test' ) );
		wp_cache_set( 'wordpoints_modules', array( 'test' ), 'wordpoints_modules' );
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

		return WORDPOINTSORG_TESTS_DIR . '/data/module-packages/' . $this->package_name . '.zip';
	}
}

// EOF
