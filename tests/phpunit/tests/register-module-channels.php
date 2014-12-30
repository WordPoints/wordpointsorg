<?php

/**
 * A test case for wordpoints_register_module_channels().
 *
 * @package WordPoints\Tests
 * @since 1.0.0
 */

/**
 * Test wordpoints_register_module_channels().
 *
 * @since 1.0.0
 */
class WordPoints_Register_Module_Channels_Test
	extends WordPointsOrg_HTTP_UnitTestCase {

	/**
	 * @since 1.0.0
	 */
	public static function setUpBeforeClass() {

		parent::setUpBeforeClass();

		// Initialize the modules API, because it is hooked to admin_init which
		// doesn't fire before the tests run.
		WordPoints_Module_APIs::init();

		wordpoints_register_module_channels();
	}

	/**
	 * Test that the channels are registered.
	 *
	 * @since 1.0.0
	 */
	public function test_channels_registered() {

		wordpoints_register_module_channels();

		$this->assertCount( 2, WordPoints_Module_Channels::get() );

		$this->assertTrue(
			WordPoints_Module_Channels::is_registered( 'wordpoints.org' )
		);

		$modules = WordPoints_Module_Channels::get( 'wordpoints.org' )
			->modules->get();

		$this->assertCount( 1, $modules );
		$this->assertArrayHasKey( 'module-7/module-7.php', $modules );

		$this->assertTrue(
			WordPoints_Module_Channels::is_registered( 'github.com/WordPoints' )
		);

		$modules = WordPoints_Module_Channels::get( 'github.com/WordPoints' )
			->modules->get();

		$this->assertCount( 1, $modules );
		$this->assertArrayHasKey( 'test-1.php', $modules );
	}

	/**
	 * Test wordpoints_get_modules_supporting().
	 *
	 * @since 1.0.0
	 */
	public function test_get_modules_supporting_updates() {

		$this->http_responder = array( $this, 'respond_get_module_supporting_updates' );

		$modules = wordpoints_get_modules_supporting( 'updates' );

		$this->assertCount( 1, $modules );
		$this->assertArrayHasKey( 'module-7/module-7.php', $modules );
	}

	public function respond_get_module_supporting_updates( $request, $url ) {

		if ( false !== strpos( $url, 'wordpoints.org' ) ) {

			return array(
				'headers' => array(
					'x-wordpoints-module-api' => 'edd-software-licensing',
				),
			);
		}
	}
}

// EOF
