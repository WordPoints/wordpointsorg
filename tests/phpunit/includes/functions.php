<?php

/**
 * Utility functions used in PHPUnit testing.
 *
 * @package WordPointsOrg\Tests
 * @since 1.0.0
 */

/**
 * Manually load the module.
 *
 * @since 1.0.0
 */
function wordpointsorgtests_manually_load_module() {

	require WORDPOINTSORG_TESTS_DIR . '/../../src/wordpointsorg.php';

	wordpointsorg_activate( is_multisite() && getenv( 'WORDPOINTS_NETWORK_ACTIVE' ) );
}
