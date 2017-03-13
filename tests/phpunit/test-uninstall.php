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
 */
class WordPointsOrg_Uninstall_Test
	extends WordPoints_PHPUnit_TestCase_Module_Uninstall {

	/**
	 * Test that install and uninstall work as expected.
	 *
	 * @since 1.0.0
	 *
	 * @covers WordPointsOrg_Un_Installer
	 */
	public function test_uninstall() {

		unset( $GLOBALS['wp_roles'] );

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
