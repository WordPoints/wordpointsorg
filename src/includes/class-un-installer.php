<?php

/**
 * Class to un/install the module.
 *
 * @package WordPointsOrg
 * @since 1.0.0
 */

/**
 * Un/install the module.
 *
 * @since 1.0.0
 */
class WordPointsOrg_Un_Installer extends WordPoints_Un_Installer_Base {

	/**
	 * @since 1.1.3
	 */
	protected $type = 'module';

	/**
	 * @since 1.1.3
	 */
	protected $custom_caps_getter = 'wordpointsorg_get_custom_caps';

	/**
	 * @since 1.1.3
	 */
	protected $uninstall = array(
		'global' => array(
			'options' => array(
				'wordpoints_edd_sl_module_licenses',
				'wordpoints_edd_sl_module_info',
			),
		),
	);

	/**
	 * @since 1.1.3
	 */
	protected $updates = array(
		'1.1.3' => array( 'network' => true ),
	);

	/**
	 * @since 1.0.0
	 */
	protected function load_dependencies() {

		require_once( dirname( __FILE__ ) . '/constants.php' );
		require_once( WORDPOINTSORG_DIR . '/includes/functions.php' );
	}

	/**
	 * @since 1.0.0
	 */
	protected function uninstall_network() {

		parent::uninstall_network();

		delete_site_transient( 'wordpoints_module_updates' );
	}

	/**
	 * @since 1.0.0
	 */
	protected function uninstall_single() {

		parent::uninstall_single();

		delete_transient( 'wordpoints_module_updates' );
	}

	/**
	 * @since 1.1.3
	 */
	protected function before_update() {

		parent::before_update();

		if ( 1 === version_compare( '1.1.3', $this->updating_from ) ) {
			$this->option_prefix = 'wordpointsorg_';
		}
	}

	/**
	 * Update the network to 1.1.3.
	 *
	 * @since 1.1.3
	 */
	protected function update_network_to_1_1_3() {

		update_site_option(
			'wordpoints_module_wordpointsorg_installed_sites'
			, wordpoints_get_array_option( 'wordpointsorg_installed_sites', 'site' )
		);

		delete_site_option( 'wordpointsorg_installed_sites' );

		if ( get_site_option( 'wordpointsorg_network_installed' ) ) {
			unset( $this->option_prefix );
			$this->set_network_installed();
			delete_site_option( 'wordpointsorg_network_installed' );
		}
	}
}

return 'WordPointsOrg_Un_Installer';

// EOF
