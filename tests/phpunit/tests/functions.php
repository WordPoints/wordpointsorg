<?php

/**
 * Testcase for the module's general functions.
 *
 * @package WordPointsOrg
 * @since 1.1.0
 */

/**
 * Tests for the module's general functions.
 *
 * @since 1.1.0
 */
class WordPointsOrg_Functions_Test extends WP_UnitTestCase {

	/**
	 * Test that the function returns an array of allowed tags.
	 *
	 * @since 1.1.0
	 *
	 * @covers ::wordpointsorg_module_changelog_allowed_html
	 */
	public function test_module_changelog_allowed_html_returns_array() {

		$this->assertInternalType(
			'array'
			, wordpointsorg_module_changelog_allowed_html(
				false
				, 'wordpoints_module_changelog'
			)
		);
	}

	/**
	 * Test that the function returns the first parameter if context isn't correct.
	 *
	 * @since 1.1.0
	 *
	 * @covers ::wordpointsorg_module_changelog_allowed_html
	 */
	public function test_module_changelog_allowed_html_returns_first_param() {

		$this->assertEquals(
			__METHOD__
			, wordpointsorg_module_changelog_allowed_html( __METHOD__, 'other' )
		);
	}

	/**
	 * Test that the function is hooked to the correct KSES filter.
	 *
	 * @since 1.1.0
	 *
	 * @covers ::wordpointsorg_module_changelog_allowed_html
	 */
	public function test_module_changelog_allowed_html_hook_to_filter() {

		$message = '<p>Hello world!</p><script>alert("ha!");</script>';

		// Normally, the paragraph tags would be stripped.
		$this->assertEquals(
			'<p>Hello world!</p>alert("ha!");'
			, wp_kses( $message, 'wordpoints_module_changelog' )
		);
	}
}

// EOF
