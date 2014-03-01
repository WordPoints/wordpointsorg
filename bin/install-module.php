<?php

/**
 * Install a module remotely.
 *
 * @package WordPoints_Module_Uninstall_Tester
 * @since 0.1.0
 */

$module_file      = $argv[1];
$install_function = $argv[2];
$config_file_path = $argv[3];
$is_multisite     = $argv[4];
$wp_pu_tester_dir = $argv[5];

require_once getenv( 'WP_TESTS_DIR' ) . '/includes/functions.php';

/**
 * Loads WordPoints during module install.
 *
 * @since 0.1.0
 *
 * @action muplugins_loaded
 */
function _wordpoints_module_uninstall_tester_load_wordpoints() {

	require getenv( 'WORDPOINTS_TESTS_DIR' ) . '/../../src/wordpoints.php';
}
tests_add_filter( 'muplugins_loaded', '_wordpoints_module_uninstall_tester_load_wordpoints' );

require $wp_pu_tester_dir . '/bin/bootstrap.php';

require $module_file;

add_action( 'wordpoints_module_activate-' . $module_file, $install_function );

do_action( 'wordpoints_module_activate-' . $module_file, false );
