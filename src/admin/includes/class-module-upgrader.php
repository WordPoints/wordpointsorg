<?php

/**
 * WordPoints.org module upgrader class.
 *
 * @package WordPointsOrg
 * @since 1.0.0
 */

/**
 * The WordPress upgreaders.
 *
 * @since 1.0.0
 */
include_once( ABSPATH . '/wp-admin/includes/class-wp-upgrader.php' );

/**
 * The WordPoints module installer class.
 *
 * @since 1.0.0
 */
include_once( WORDPOINTS_DIR . '/admin/includes/class-wordpoints-module-installer.php' );

/**
 * Clean the WordPoints modules cache.
 *
 * @since 1.0.0
 *
 * @param bool $clear_update_cache Whether to clear the updates cache.
 */
function wordpointsorg_clean_modules_cache( $clear_update_cache = true ) {

	if ( $clear_update_cache ) {
		delete_site_transient( 'wordpoints_module_updates' );
	}

	wp_cache_delete( 'wordpoints_modules', 'wordpoints_modules' );
}

/**
 * WordPoints.org module upgrader class.
 *
 * This class is based on the WordPress Plugin_Upgrader class, and is designed to
 * upgrade/install modules from a local zip, remote zip URL, or uploaded zip file.
 *
 * @see WP_Upgrader The WP Upgrader class.
 *
 * @since 1.0.0
 */
final class WordPointsOrg_Module_Upgrader extends WordPoints_Module_Installer {

	//
	// Public Vars.
	//

	/**
	 * Whether we are performing a bulk upgrade.
	 *
	 * @since 1.0.0
	 *
	 * @type bool $bulk
	 */
	public $bulk = false;

	/**
	 * Whether the upgrade routine bailed out early.
	 *
	 * @since 1.0.0
	 *
	 * @var bool
	 */
	public $bailed_early = false;

	//
	// Private Methods.
	//

	/**
	 * Set up the strings for a module upgrade.
	 *
	 * @since 1.0.0
	 */
	private function upgrade_strings() {

		$upgrade_strings = array(
			'up_to_date'          => __( 'The module is at the latest version.', 'wordpointsorg' ),
			'no_package'          => __( 'Update package not available.', 'wordpointsorg' ),
			'no_channel'          => __( 'That module cannot be updated, because there is no channel specified to receive updates through.', 'wordpointsorg' ),
			'api_not_found'       => __( 'That module cannot be updated, because there is no API installed that can communicate with that channel.', 'wordpointsorg' ),
			// translators: Update package URL.
			'downloading_package' => sprintf( __( 'Downloading update from %s&#8230;', 'wordpointsorg' ), '<span class="code">%s</span>' ),
			'unpack_package'      => __( 'Unpacking the update&#8230;', 'wordpointsorg' ),
			'remove_old'          => __( 'Removing the old version of the module&#8230;', 'wordpointsorg' ),
			'remove_old_failed'   => __( 'Could not remove the old module.', 'wordpointsorg' ),
			'process_failed'      => __( 'Module update failed.', 'wordpointsorg' ),
			'process_success'     => __( 'Module updated successfully.', 'wordpointsorg' ),
			'not_installed'       => __( 'That module cannot be updated, because it is not installed.', 'wordpointsorg' ),
		);

		$this->strings = array_merge( $this->strings, $upgrade_strings );
	}

	/**
	 * Set up the strings for a module install.
	 *
	 * @since 1.0.0
	 */
	private function install_strings() {

		$install_strings = array(
			'no_package'          => __( 'Install package not available.', 'wordpointsorg' ),
			// translators: Module package URL.
			'downloading_package' => sprintf( __( 'Downloading install package from %s&#8230;', 'wordpointsorg' ), '<span class="code">%s</span>' ),
			'unpack_package'      => __( 'Unpacking the package&#8230;', 'wordpointsorg' ),
			'installing_package'  => __( 'Installing the module&#8230;', 'wordpointsorg' ),
			'no_files'            => __( 'The module contains no files.', 'wordpointsorg' ),
			'process_failed'      => __( 'Module install failed.', 'wordpointsorg' ),
			'process_success'     => __( 'Module installed successfully.', 'wordpointsorg' ),
		);

		$this->strings = array_merge( $this->strings, $install_strings );
	}

	//
	// Public Methods.
	//

	/**
	 * Install a module.
	 *
	 * @since 1.0.0
	 *
	 * @param string $package URL of the zip package of the module source.
	 * @param array  $args    {
	 *        Optional arguments.
	 *
	 *        @type bool $clear_update_cache Whether the to clear the update cache.
	 *                                       The default is true.
	 * }
	 *
	 * @return bool|WP_Error True on success, false or a WP_Error on failure.
	 */
	public function install( $package, $args = array() ) {

		$args = wp_parse_args( $args, array( 'clear_update_cache' => true ) );

		$this->init();
		$this->install_strings();

		add_filter( 'upgrader_source_selection', array( $this, 'check_package' ) );

		$result = $this->run(
			array(
				'package'           => $package,
				'destination'       => wordpoints_modules_dir(),
				'clear_destination' => false,
				'clear_working'     => true,
				'hook_extra'        => array(),
			)
		);

		remove_filter( 'upgrader_source_selection', array( $this, 'check_package' ) );

		if ( ! $result || is_wp_error( $result ) ) {
			return $result;
		}

		if ( ! $this->result || is_wp_error( $this->result ) ) {
			return $this->result;
		}

		// Force refresh of module update cache.
		wordpointsorg_clean_modules_cache( $args['clear_update_cache'] );

		/**
		 * This action is documented in /wp-admin/includes/class-wp-upgrader.php.
		 */
		do_action( 'upgrader_process_complete', $this, array( 'action' => 'install', 'type' => 'wordpoints_module' ), $package );

		return true;
	}

	/**
	 * Upgrade a module.
	 *
	 * @since 1.0.0
	 *
	 * @param string $module_file Basename path to the module file.
	 * @param array  $args        {
	 *        Optional arguments.
	 *
	 *        @type bool $clear_update_cache Whether the to clear the update cache.
	 *                                       The default is true.
	 * }
	 *
	 * @return bool|WP_Error True on success, false or a WP_Error on failure.
	 */
	public function upgrade( $module_file, $args = array() ) {

		$args = $this->_before_upgrade( $args );
		$result = $this->_upgrade( $module_file );
		$this->_after_upgrade( $module_file, $args );

		if ( ! $result || is_wp_error( $result ) ) {
			return $result;
		}

		return true;
	}

	/**
	 * Perform a bulk upgrade.
	 *
	 * @since 1.0.0
	 *
	 * @param string[] $modules Array of basename paths to the modules.
	 * @param array    $args {
	 *        @type bool $clear_update_cache Whether the to clear the update cache.
	 *                                       Default is true.
	 * }
	 *
	 * @return array The result of each update, indexed by module.
	 */
	public function bulk_upgrade( $modules, $args = array() ) {

		$this->bulk = true;

		$args = $this->_before_upgrade( $args );

		$this->skin->header();

		// Connect to the Filesystem first.
		if ( ! $this->fs_connect( array( WP_CONTENT_DIR, wordpoints_modules_dir() ) ) ) {

			$this->skin->footer();
			return false;
		}

		$this->skin->bulk_header();

		$this->maybe_start_maintenance_mode( $modules );

		$results = array();

		$this->update_count = count( $modules );
		$this->update_current = 0;

		foreach ( $modules as $module ) {

			$this->update_current++;

			$results[ $module ] = $this->_upgrade( $module );

			// Prevent credentials auth screen from displaying multiple times.
			if ( false === $results[ $module ] && ! $this->bailed_early ) {
				break;
			}
		}

		$this->maintenance_mode( false );

		$this->skin->bulk_footer();
		$this->skin->footer();

		$this->_after_upgrade( $modules, $args );

		return $results;

	} // End public function bulk_upgrade().

	/**
	 * Set up before running an upgrade.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args The arguments passed to the upgrader.
	 *
	 * @return array The parsed upgrader arguments.
	 */
	protected function _before_upgrade( $args ) {

		$args = wp_parse_args( $args, array( 'clear_update_cache' => true ) );

		$this->init();
		$this->upgrade_strings();

		add_filter( 'upgrader_clear_destination', array( $this, 'delete_old_module' ), 10, 4 );
		add_filter( 'upgrader_source_selection', array( $this, 'check_package' ) );
		add_filter( 'upgrader_source_selection', array( $this, 'correct_module_dir_name' ), 10, 3 );

		if ( ! $this->bulk ) {
			add_filter( 'upgrader_pre_install', array( $this, 'deactivate_module_before_upgrade' ), 10, 2 );
		}

		return $args;
	}

	/**
	 * Upgrade a module.
	 *
	 * This is the real meat of upgrade functions.
	 *
	 * @since 1.0.0
	 *
	 * @param string $module_file Basename path to the module file.
	 *
	 * @return mixed Returns true or an array on success, false or a WP_Error on failure.
	 */
	protected function _upgrade( $module_file ) {

		$this->bailed_early = false;

		$modules = wordpoints_get_modules();

		if ( ! isset( $modules[ $module_file ] ) ) {
			$this->_bail_early( 'not_installed' );
			return false;
		}

		$module_data = $modules[ $module_file ];

		if ( $this->bulk ) {

			$this->skin->module = $module_file;
			$this->skin->module_info = wordpoints_get_module_data(
				wordpoints_modules_dir() . $module_file
			);
			$this->skin->module_active = is_wordpoints_module_active( $module_file );
		}

		$current = get_site_transient( 'wordpoints_module_updates' );

		if ( ! isset( $current['response'][ $module_file ] ) ) {
			$this->_bail_early( 'up_to_date', 'feedback' );
			return true;
		}

		$channel = wordpoints_get_channel_for_module( $module_data );
		$channel = WordPoints_Module_Channels::get( $channel );

		if ( ! $channel ) {
			$this->_bail_early( 'no_channel' );
			return false;
		}

		$api = $channel->get_api();

		if ( false === $api ) {
			$this->_bail_early( 'api_not_found' );
			return false;
		}

		return $this->run(
			array(
				'package'           => $api->get_package_url( $channel, $module_data ),
				'destination'       => wordpoints_modules_dir(),
				'clear_destination' => true,
				'clear_working'     => true,
				'is_multi'          => $this->bulk,
				'hook_extra'        => array(
					'wordpoints_module' => $module_file,
				),
			)
		);

	} // End protected function _upgrade().

	/**
	 * Clean up after an upgrade.
	 *
	 * @since 1.0.0
	 *
	 * @param string|string[] $modules The module(s) being upgraded.
	 * @param array           $args    The arguments passed to the upgrader.
	 */
	protected function _after_upgrade( $modules, $args ) {

		remove_filter( 'upgrader_source_selection', array( $this, 'check_package' ) );
		remove_filter( 'upgrader_source_selection', array( $this, 'correct_module_dir_name' ) );
		remove_filter( 'upgrader_clear_destination', array( $this, 'delete_old_module' ) );

		if ( ! $this->bulk ) {

			remove_filter( 'upgrader_pre_install', array( $this, 'deactivate_module_before_upgrade' ) );

			if ( ! $this->skin->result || is_wp_error( $this->skin->result ) ) {
				return;
			}
		}

		// Force refresh of module update cache.
		wordpointsorg_clean_modules_cache( $args['clear_update_cache'] );

		$details = array(
			'action' => 'update',
			'type'   => 'wordpoints_module',
			'bulk'   => $this->bulk,
		);

		/**
		 * This action is documented in /wp-admin/includes/class-wp-upgrader.php.
		 */
		do_action( 'upgrader_process_complete', $this, $details, $modules );
	}

	/**
	 * Conditionally start maintenance mode, only if necessary.
	 *
	 * Used when performing bulk updates.
	 *
	 * Only start maintenance mode if:
	 * - running Multisite and there are one or more modules specified, OR
	 * - a module with an update available is currently active.
	 *
	 * @since 1.0.0
	 *
	 * @param string[] $modules The modules being upgraded in bulk.
	 */
	public function maybe_start_maintenance_mode( $modules ) {

		if ( is_multisite() && ! empty( $modules ) ) {

			$this->maintenance_mode( true );

		} else {

			$current = get_site_transient( 'wordpoints_module_updates' );

			foreach ( $modules as $module ) {

				if (
					is_wordpoints_module_active( $module )
					&& isset( $current['response'][ $module ] )
				) {
					$this->maintenance_mode( true );
					break;
				}
			}
		}
	}

	/**
	 * Check if the source package actually contains a module.
	 *
	 * @since 1.0.0
	 *
	 * @WordPress\filter upgrader_source_selection Added by self::install().
	 *
	 * @uses $wp_filesystem
	 *
	 * @param string|WP_Error $source The path to the source package.
	 *
	 * @return string|WP_Error The path to the source package, or a WP_Error.
	 */
	public function check_package( $source ) {

		global $wp_filesystem;

		if ( is_wp_error( $source ) ) {
			return $source;
		}

		$working_directory = str_replace(
			$wp_filesystem->wp_content_dir()
			, trailingslashit( WP_CONTENT_DIR )
			, $source
		);

		if ( ! is_dir( $working_directory ) ) {
			return $source;
		}

		$modules_found = false;

		$files = glob( $working_directory . '*.php' );

		if ( false === $files ) {
			return $source;
		}

		foreach ( $files as $file ) {

			$module_data = wordpoints_get_module_data( $file, false, false );

			if ( ! empty( $module_data['name'] ) ) {
				$modules_found = true;
				break;
			}
		}

		if ( ! $modules_found ) {

			return new WP_Error(
				'incompatible_archive_no_modules'
				, $this->strings['incompatible_archive']
				, __( 'No valid modules were found.', 'wordpointsorg' )
			);
		}

		return $source;
	}

	/**
	 * Get the file which contains the module info.
	 *
	 * Not used within the class, but is called by the installer skin.
	 *
	 * @since 1.0.0
	 *
	 * @return string|false The module path or false on failure.
	 */
	public function module_info() {

		if ( ! is_array( $this->result ) || empty( $this->result['destination_name'] ) ) {
			return false;
		}

		$module = wordpoints_get_modules( '/' . $this->result['destination_name'] );

		if ( empty( $module ) ) {
			return false;
		}

		return $this->result['destination_name'] . '/' . key( $module );
	}

	/**
	 * Make sure a module is inactive before it is upgraded.
	 *
	 * @since 1.0.0
	 *
	 * @WordPress\filter upgrader_pre_install Added by self::upgrade().
	 *
	 * @param bool|WP_Error $return True if we should do the upgrade, a WP_Error otherwise.
	 * @param array         $data   Data about the upgrade: what module is being upgraded.
	 *
	 * @return bool|WP_Error A WP_Error on failure, otherwise nothing.
	 */
	public function deactivate_module_before_upgrade( $return, $data ) {

		if ( is_wp_error( $return ) ) {
			return $return;
		}

		if ( empty( $data['wordpoints_module'] ) ) {
			return new WP_Error( 'bad_request', $this->strings['bad_request'] );
		}

		if ( is_wordpoints_module_active( $data['wordpoints_module'] ) ) {

			// Deactivate the module silently (the actions won't be fired).
			wordpoints_deactivate_modules( array( $data['wordpoints_module'] ), true );
		}

		return $return;
	}

	/**
	 * Ensures that a module folder will have the correct name.
	 *
	 * @since 1.0.0
	 *
	 * @WordPress\filter upgrader_source_selection Added by self::upgrade().
	 *
	 * @param string      $source        The path to the module source.
	 * @param array       $remote_source The remote source of the module.
	 * @param WP_Upgrader $upgrader      The upgrader instance.
	 *
	 * @return string The module folder.
	 */
	public function correct_module_dir_name( $source, $remote_source, $upgrader ) {

		global $wp_filesystem;

		if ( is_wp_error( $source ) ) {
			return $source;
		}

		if ( ! isset( $upgrader->skin->module ) ) {
			return $source;
		}

		$source_name = basename( $source );
		$module_name = dirname( $upgrader->skin->module );

		if ( '.' === $module_name || $source_name === $module_name ) {
			return $source;
		}

		$correct_source = dirname( $source ) . '/' . $module_name;

		$moved = $wp_filesystem->move( $source, $correct_source );

		if ( ! $moved ) {
			return new WP_Error( 'wordpointsorg_incorrect_source_name', $this->strings['incorrect_source_name'] );
		}

		return $correct_source;
	}

	/**
	 * Delete the old module before installing the new one.
	 *
	 * @since 1.0.0
	 *
	 * @WordPress\filter upgrader_clear_destination Added by self::upgrade() and
	 *                                              self::bulk_upgrade().
	 *
	 * @param true|WP_Error $removed            Whether the destination folder has been removed.
	 * @param string        $local_destination  The local path to the destination folder.
	 * @param string        $remote_destination The remote path to the destination folder.
	 * @param array         $data               Data for the upgrade: what module is being upgraded.
	 *
	 * @return true|WP_Error True on success, a WP_Error on failure.
	 */
	public function delete_old_module( $removed, $local_destination, $remote_destination, $data ) {

		global $wp_filesystem;

		if ( is_wp_error( $removed ) ) {
			return $removed;
		}

		if ( empty( $data['wordpoints_module'] ) ) {
			return new WP_Error( 'bad_request', $this->strings['bad_request'] );
		}

		$modules_dir = $wp_filesystem->find_folder( wordpoints_modules_dir() );
		$this_module_dir = trailingslashit( dirname( $modules_dir . $data['wordpoints_module'] ) );

		// Make sure it hasn't already been removed somehow.
		if ( ! $wp_filesystem->exists( $this_module_dir ) ) {
			return $removed;
		}

		/*
		 * If the module is in its own directory, recursively delete the directory.
		 * Do a base check on if the module includes the directory separator AND that
		 * it's not the root modules folder. If not, just delete the single file.
		 */
		if ( strpos( $data['wordpoints_module'], '/' ) && $this_module_dir !== $modules_dir ) {
			$deleted = $wp_filesystem->delete( $this_module_dir, true );
		} else {
			$deleted = $wp_filesystem->delete( $modules_dir . $data['wordpoints_module'] );
		}

		if ( ! $deleted ) {
			return new WP_Error( 'remove_old_failed', $this->strings['remove_old_failed'] );
		}

		return true;
	}

	//
	// Private Functions.
	//

	/**
	 * Bail early before finishing a a process normally.
	 *
	 * @since 1.0.0
	 *
	 * @param string $message Slug for the message to show the user.
	 * @param string $type    The type of message, 'error' (default), or 'feedback'.
	 */
	private function _bail_early( $message, $type = 'error' ) {

		$this->bailed_early = true;

		$this->skin->before();
		$this->skin->set_result( false );

		if ( 'feedback' === $type ) {
			$this->skin->feedback( $message );
		} else {
			$this->skin->error( $message );
		}

		$this->skin->after();
	}

} // class WordPoints_Module_Upgrader

// EOF
