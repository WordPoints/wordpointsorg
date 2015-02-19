<?php

/**
 * Testcase for the wordpointsorg_iframe_module_changelog() function.
 *
 * @package WordPointsOrg
 * @since 1.1.0
 */

/**
 * Tests for wordpointsorg_iframe_module_changelog().
 *
 * @since 1.1.0
 *
 * @covers ::wordpointsorg_iframe_module_changelog
 */
class WordPointsOrg_Iframe_Module_Changelog_Test extends WP_UnitTestCase {

	/**
	 * @since 1.1.0
	 */
	public function setUp() {

		parent::setUp();

		WordPoints_Module_APIs::init();

		WordPoints_Module_Channels::register(
			'wordpoints.org'
			, true
		);

		WordPoints_Module_APIs::register(
			'test'
			, ''
			, 'WordPoints_Module_API_Mock'
		);

		$transient = 'wrdpnts_' . md5( 'module_channel_supports-wordpoints.org' );
		set_site_transient( $transient, 'test', WEEK_IN_SECONDS );

		$user = $this->factory->user->create_and_get();
		$user->add_cap( 'update_wordpoints_modules' );

		// On multisite the user must be a super admin.
		if ( is_multisite() ) {
			grant_super_admin( $user->ID );
		}

		wp_set_current_user( $user->ID );

		$_GET['module'] = urlencode( 'module-7/module-7.php' );

		// iframe_header() needs this.
		global $current_screen, $wp_scripts, $_wp_admin_css_colors;

		$current_screen = WP_Screen::get( 'test' );
		$wp_scripts = new WP_Scripts();
		$_wp_admin_css_colors = array( 'fresh' => (object) array( 'url' => '' ) );
	}

	/**
	 * @since 1.1.0
	 */
	public function tearDown() {

		WordPoints_Module_Channels::deregister( 'wordpoints.org' );
		WordPoints_Module_APIs::deregister( 'test' );

		parent::tearDown();
	}

	/**
	 * Test that it works.
	 *
	 * @since 1.1.0
	 */
	public function test_displays_changelog() {

		$this->expectOutputRegex( '/WordPoints_Module_API_Mock::get_changelog/' );
		wordpointsorg_iframe_module_changelog();
	}

	/**
	 * Test that it requires that the user have certain capabilities.
	 *
	 * @since 1.1.0
	 *
	 * @expectedException WPDieException
	 */
	public function test_requires_update_modules_cap() {

		wp_set_current_user( $this->factory->user->create() );
		wordpointsorg_iframe_module_changelog();
	}

	/**
	 * Test that it requires that a module be supplied.
	 *
	 * @since 1.1.0
	 *
	 * @expectedException WPDieException
	 */
	public function test_requires_module() {

		unset( $_GET['module'] );
		wordpointsorg_iframe_module_changelog();
	}

	/**
	 * Test that it requires a valid module.
	 *
	 * @since 1.1.0
	 *
	 * @expectedException WPDieException
	 */
	public function test_requires_valid_module() {

		$_GET['module'] = 'invalid/invalid.php';
		wordpointsorg_iframe_module_changelog();
	}

	/**
	 * Test that the module must specify a channel.
	 *
	 * @since 1.1.0
	 *
	 * @expectedException WPDieException
	 */
	public function test_requires_module_with_channel() {

		$_GET['module'] = 'test-2.php';
		wordpointsorg_iframe_module_changelog();
	}

	/**
	 * Test that the module must specify a channel that uses a supported API.
	 *
	 * @since 1.1.0
	 *
	 * @expectedException WPDieException
	 */
	public function test_requires_module_with_supported_api() {

		$transient = 'wrdpnts_' . md5( 'module_channel_supports-wordpoints.org' );
		set_site_transient( $transient, 'unknown', WEEK_IN_SECONDS );

		wordpointsorg_iframe_module_changelog();
	}
}

// EOF
