<?php

/**
 * A test case for the uninstall script
 *
 * @package WordPointsOrg\Tests
 * @since 1.0.0
 */

/**
 * Test that the module installs and uninstalls itself properly.
 *
 * @since 1.0.0
 *
 * @group uninstall
 */
class WordPointsOrg_Uninstall_Test extends WordPoints_Module_Uninstall_UnitTestCase {

	/**
	 * The module's install function.
	 *
	 * @since 1.0.0
	 *
	 * @type callable $install_function
	 */
	protected $install_function = 'wordpointsorg_activate';

	/**
	 * Set up for the tests.
	 *
	 * @since 1.0.0
	 */
	public function setUp() {

		$this->module_file = WORDPOINTSORG_TESTS_DIR . '/../../src/wordpointsorg.php';

		parent::setUp();
	}

	/**
	 * Test that install and uninstall work as expected.
	 *
	 * @since 1.0.0
	 */
	public function test_uninstall() {

		// Check that the custom capabilities were added on install.
		$this->assertTrue( get_role( 'administrator' )->has_cap( 'update_wordpoints_modules' ) );

		// Uninstall.
		$this->uninstall();

		// Override the roles "cache".
		unset( $GLOBALS['wp_roles'] );

		// Check that the custom capabilities were removed.
		$this->assertFalse( get_role( 'administrator' )->has_cap( 'update_wordpoints_modules' ) );
	}
}
