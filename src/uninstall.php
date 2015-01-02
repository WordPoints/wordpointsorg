<?php

/**
 * Uninstall the module.
 *
 * @package WordPointsOrg
 * @since 1.0.0
 */

/**
 * The module's un/installer.
 *
 * @since 1.0.0
 */
require_once dirname( __FILE__ ) . '/includes/class-un-installer.php';

$uninstaller = new WordPointsOrg_Un_Installer();
$uninstaller->uninstall();

// EOF
