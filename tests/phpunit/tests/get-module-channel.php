<?php

/**
 * A test case for wordpoints_get_channel_for_module().
 *
 * @package WordPoints\Tests
 * @since 1.0.0
 */

/**
 * Test wordpoints_get_channel_for_module().
 *
 * @since 1.0.0
 *
 * @covers ::wordpoints_get_channel_for_module
 */
class WordPoints_Get_Channel_For_Module_Test extends WP_UnitTestCase {

	/**
	 * Test that it returns the channel.
	 *
	 * @since 1.0.0
	 */
	public function test_returns_channel() {

		$module = array( 'channel' => 'wordpoints.org' );

		$this->assertEquals(
			$module['channel']
			, wordpoints_get_channel_for_module( $module )
		);
	}

	/**
	 * Test that it calls the wordpoints_channel_for_module filter.
	 *
	 * @since 1.0.0
	 */
	public function test_calls_filter() {

		add_filter( 'wordpoints_channel_for_module', array( $this, 'wordpoints_channel_for_module' ), 10, 2 );
		$channel = wordpoints_get_channel_for_module( array( 'channel' => 'wordpoints.org' ) );
		remove_filter( 'wordpoints_channel_for_module', array( $this, 'wordpoints_channel_for_module' ), 10, 2 );

		$this->assertEquals( __CLASS__, $channel );
	}

	/**
	 * @since 1.0.0
	 *
	 * @see self::test_calls_filter()
	 */
	public function wordpoints_channel_for_module( $channel, $module ) {

		$this->assertEquals( 'wordpoints.org', $channel );
		$this->assertEquals( array( 'channel' => 'wordpoints.org' ), $module );

		return __CLASS__;
	}
}

// EOF
