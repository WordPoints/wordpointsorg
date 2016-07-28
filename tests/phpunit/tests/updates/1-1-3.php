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

		update_site_option( 'wordpointsorg_installed_sites', array( 1, 5 ) );
		update_site_option( 'wordpointsorg_network_installed', true );

		$this->update_module();

		$this->assertFalse( get_site_option( 'wordpointsorg_installed_sites' ) );
		$this->assertFalse( get_site_option( 'wordpointsorg_network_installed' ) );

		$this->assertEquals(
			array( 1, 5 )
			, get_site_option( 'wordpoints_module_wordpointsorg_installed_sites' )
		);

		$network_installed = get_site_option( 'wordpoints_network_installed' );

		$this->assertTrue( $network_installed['module']['wordpointsorg'] );
	}

	/**
	 * Run an update for a module.
	 *
	 * @since 1.1.3
	 *
	 * @param string $module The slug of the module to update.
	 * @param string $from   The version to update from.
	 */
	protected function update_module( $module = null, $from = null ) {

		// See https://github.com/WordPoints/wordpoints/issues/430.
		WordPoints_Installables::register(
			'module'
			, 'wordpointsorg'
			, array(
				'version'      => WORDPOINTSORG_VERSION,
				'network_wide' => is_wordpoints_network_active(),
				'un_installer' => WORDPOINTSORG_DIR . '/includes/class-un-installer.php',
			)
		);

		if ( ! isset( $module ) ) {
			$module = $this->wordpoints_module;
		}

		if ( ! isset( $from ) ) {
			$from = $this->previous_version;
		}

		$this->set_module_db_version( $module, $from, is_wordpoints_network_active() );

		// Make sure that the module is marked as active in the database.
		wordpoints_update_maybe_network_option(
			'wordpoints_active_modules'
			, array( $module => 1 )
		);

		// Run the update.
		WordPoints_Installables::maybe_do_updates();
	}
}

// EOF
