<?php

/**
 * A test case for the EDD Software Licensing Free module API.
 *
 * @package WordPointsOrg\Tests
 * @since 1.1.0
 */

/**
 * Test that the EDD Software Licensing Free module API works.
 *
 * @since 1.1.0
 */
class WordPointsOrg_EDD_Software_Licensing_Free_Module_API_Test
	extends WordPointsOrg_Module_API_UnitTestCase {

	/**
	 * @since 1.1.0
	 */
	protected $api_slug = 'edd-software-licensing-free';

	/**
	 * Test that it supports the expected API functions.
	 *
	 * @since 1.0.0
	 */
	public function test_supports() {

		$this->assertTrue( $this->api->supports( 'updates' ) );
	}
}

// EOF
