<?php

/**
 * Testcase for the 1.1.3 update.
 *
 * @package WordPointsOrg
 * @since 1.1.3
 */

/**
 * Tests updating the module to version 1.1.3.
 *
 * @since 1.1.3
 *
 * @group update
 */
class WordPointsOrg_Updates_1_1_3_Test extends WordPoints_PHPUnit_TestCase {

	/**
	 * @since 1.1.3
	 */
	protected $previous_version = '1.1.2';

	/**
	 * The module slug.
	 *
	 * @since 1.1.3
	 *
	 * @var string
	 */
	protected $wordpoints_module = 'wordpointsorg';

	/**
	 * Test that the function returns an array of allowed tags.
	 *
	 * @since 1.1.3
	 *
	 * @requires WordPoints network-active
	 *
	 * @covers WordPointsOrg_Un_Installer::update_network_to_1_1_3
	 */
	public function test_imports_options() {

		remove_filter( 'wordpoints_modules_dir', 'wordpointsorgtests_modules_dir' );

		update_site_option( 'wordpointsorg_installed_sites', array( 1, 5 ) );
		update_site_option( 'wordpointsorg_network_installed', true );

		$this->update_module();

		$this->assertFalse( get_site_option( 'wordpointsorg_installed_sites' ) );
		$this->assertFalse( get_site_option( 'wordpointsorg_network_installed' ) );

		$this->assertSame(
			array( 1, 5 )
			, get_site_option( 'wordpoints_module_wordpointsorg_installed_sites' )
		);

		$network_installed = get_site_option( 'wordpoints_network_installed' );

		$this->assertTrue( $network_installed['module']['wordpointsorg'] );
	}
}

// EOF
