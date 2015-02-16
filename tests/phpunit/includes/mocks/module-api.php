<?php

/**
 * Class to mock a module API.
 *
 * @package WordPointsOrg\Tests
 * @since 1.1.0
 */

/**
 * Mock for modules APIs.
 *
 * @since 1.0.0
 */
class WordPoints_Module_API_Mock extends WordPoints_Module_API {

	/**
	 * @since 1.0.0
	 */
	public function check_for_updates( $channel ) {
		return __METHOD__;
	}

	/**
	 * @since 1.0.0
	 */
	public function get_package_url( $channel, $module ) {
		return __METHOD__;
	}

	/**
	 * @since 1.1.0
	 */
	public function get_changelog( $channel, $module ) {
		return __METHOD__;
	}
}

// EOF
