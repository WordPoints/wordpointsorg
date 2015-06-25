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
	 * The module's capabilities.
	 *
	 * Used to hold the list of capabilities during install and uninstall, so that
	 * they don't have to be retrieved all over again for each site (if multisite).
	 *
	 * @since 1.0.0
	 *
	 * @type array $capabilties
	 */
	protected $capabilities;

	/**
	 * @since 1.1.2
	 */
	public function __construct() {

		if ( version_compare( WORDPOINTS_VERSION, '1.10.0', '<=' ) ) {

			$this->option_prefix = 'wordpointsorg_';

		} else {

			$this->load_dependencies();

			parent::__construct(
				plugin_basename( WORDPOINTSORG_DIR . '/wordpointsorg.php' )
				, WORDPOINTSORG_VERSION
			);
		}
	}

	/**
	 * @since 1.0.0
	 */
	public function before_install() {

		$this->capabilities = wordpointsorg_get_custom_caps();
	}

	/**
	 * @since 1.0.0
	 */
	protected function before_uninstall() {

		$this->capabilities = array_keys( wordpointsorg_get_custom_caps() );
	}

	/**
	 * @since 1.0.0
	 */
	protected function install_network() {

		$wordpoints_data = wordpoints_get_array_option( 'wordpoints_data', 'network' );
		$wordpoints_data['modules']['wordpointsorg']['version'] = WORDPOINTSORG_VERSION;
		wordpoints_update_network_option( 'wordpoints_data', $wordpoints_data );
	}

	/**
	 * @since 1.0.0
	 */
	protected function install_site() {
		wordpoints_add_custom_caps( $this->capabilities );
	}

	/**
	 * @since 1.0.0
	 */
	protected function install_single() {

		$this->install_network();
		$this->install_site();
	}

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

		$wordpoints_data = wordpoints_get_array_option( 'wordpoints_data', 'network' );
		unset( $wordpoints_data['modules']['wordpointsorg'] );
		wordpoints_update_network_option( 'wordpoints_data', $wordpoints_data );

		delete_site_option( 'wordpoints_edd_sl_module_licenses' );
		delete_site_option( 'wordpoints_edd_sl_module_info' );

		delete_site_transient( 'wordpoints_module_updates' );
	}

	/**
	 * @since 1.0.0
	 */
	protected function uninstall_site() {
		wordpoints_remove_custom_caps( $this->capabilities );
	}

	/**
	 * @since 1.0.0
	 */
	protected function uninstall_single() {

		$this->uninstall_network();
		$this->uninstall_site();
	}
}

// EOF
