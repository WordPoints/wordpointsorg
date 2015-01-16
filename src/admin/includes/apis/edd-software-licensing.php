<?php

/**
 * Module API class for Easy Digital Downloads and Sofware Licensing extension.
 *
 * @package WordPointsOrg
 * @since 1.0.0
 */

/**
 * Module API for channels using Easy Digital Downloads and Sofware Licensing.
 *
 * @since 1.0.0
 */
class WordPoints_EDD_Software_Licensing_Module_API extends WordPoints_Module_API {

	/**
	 * @since 1.0.0
	 */
	protected $slug = 'edd-software-licensing';

	/**
	 * @since 1.0.0
	 */
	protected $supports = array(
		'updates' => true,
	);

	/**
	 * Get the licenses for modules that use this API.
	 *
	 * @since 1.0.0
	 *
	 * @param string $channel The channel to get module licenses for.
	 *
	 * @return array[] The module license data.
	 */
	public function get_licenses( $channel ) {

		$licenses = wordpoints_get_array_option(
			'wordpoints_edd_sl_module_licenses'
			, 'network'
		);

		if ( ! isset( $licenses[ $channel->url ] ) ) {
			return array();
		}

		return $licenses[ $channel->url ];
	}

	/**
	 * Get the license data for a module.
	 *
	 * @since 1.0.0
	 *
	 * @param string $channel The channel to get module licenses for.
	 * @param string $module_id The module's unique ID.
	 *
	 * @return string[] {
	 *         The license data for this module.
	 *
	 *         @type string $license The license key.
	 *         @type string $status  The license key's status.
	 * }
	 */
	public function get_module_license_data( $channel, $module_id ) {

		$licenses = $this->get_licenses( $channel );

		if ( ! isset( $licenses[ $module_id ] ) ) {
			return array();
		}

		return $licenses[ $module_id ];
	}

	/**
	 * Check whether a module has a valid license.
	 *
	 * @since 1.1.0
	 *
	 * @param string $channel   The channel to get module licenses for.
	 * @param string $module_id The module's unique ID.
	 *
	 * @return bool Whether the module has a valid license.
	 */
	public function module_has_valid_license( $channel, $module_id ) {

		$license_data = $this->get_module_license_data( $channel, $module_id );

		if (
			isset( $license_data['license'], $license_data['status'] )
			&& 'valid' === $license_data['status']
		) {
			return true;
		}

		return false;
	}

	/**
	 * Activate a module's license key.
	 *
	 * @since 1.0.0
	 *
	 * @see self::update_license_activation()
	 */
	public function activate_license( $channel, $module ) {
		return $this->update_license_activation( $channel, $module, 'activate' );
	}

	/**
	 * Deactivate a module's license key.
	 *
	 * @since 1.0.0
	 *
	 * @see self::update_license_activation()
	 */
	public function deactivate_license( $channel, $module ) {
		return $this->update_license_activation( $channel, $module, 'deactivate' );
	}

	/**
	 * Update a module license's activation status.
	 *
	 * When the $status is 'activate', the return value will be 'valid' or 'invalid'.
	 * If the $status is 'deactivate', the return value will be 'deactivate' or
	 * 'failed'.
	 *
	 * @since 1.0.0
	 *
	 * @param WordPoints_Module_Channel $channel The module channel.
	 * @param array                     $module  The module whose license's status to update.
	 * @param string                    $status  The status to give the module license.
	 *
	 * @return string|false False on failure, or a string result status.
	 */
	protected function update_license_activation( $channel, $module, $status ) {

		$response = $this->request(
			"{$status}_license"
			, $channel
			, $module
		);

		if ( false === $response ) {
			return false;
		}

		$licenses = wordpoints_get_array_option( 'wordpoints_edd_sl_module_licenses', 'network' );

		if ( 'activate' === $status ) {
			// This is actually the status, and will be "valid" or "invalid".
			$licenses[ $channel->url ][ $module['ID'] ]['status'] = $response['license'];
		} elseif ( 'deactivated' === $response['license'] ) {
			// This is actually the status, and will be "deactivated" or "failed".
			$licenses[ $channel->url ][ $module['ID'] ]['status'] = $response['license'];
		}

		wordpoints_update_network_option( 'wordpoints_edd_sl_module_licenses', $licenses );

		return $response['license'];
	}

	/**
	 * Check a module's license key.
	 *
	 * @since 1.0.0
	 *
	 * @param WordPoints_Module_Channel $channel The module channel.
	 * @param array                     $module  The module whose license to check.
	 *
	 * @return string|false The license's status, or false on failure.
	 */
	function check_license( $channel, $module ) {

		$response = $this->request( 'check_license', $channel, $module );

		if ( ! isset( $response['license'] ) ) {
			return false;
		}

		return $response['license'];
	}

	/**
	 * Set up the actions and filters for this update API.
	 *
	 * @since 1.0.0
	 */
	public function hooks() {

		add_action( 'wordpoints_modules_list_table_items', array( $this, 'wordpoints_modules_list_table_items' ) );
		add_action( 'wordpoints_after_module_row', array( $this, 'wordpoints_after_module_row' ), 10, 2 );
	}

	/**
	 * Save module license forms on submit.
	 *
	 * @since 1.0.0
	 *
	 * @WordPress\action wordpoints_modules_list_table_items Added by self::hooks().
	 */
	public function wordpoints_modules_list_table_items( $modules ) {

		foreach ( $modules['all'] as $module ) {

			$channel = wordpoints_get_channel_for_module( $module );
			$channel = WordPoints_Module_Channels::get( $channel );

			if ( ! $channel || $this !== $channel->get_api() ) {
				continue;
			}

			$url = str_replace( '.', '_', $channel->url );

			if ( isset( $_POST[ "license_key-{$url}-{$module['ID']}" ] ) ) {

				$licenses = wordpoints_get_array_option( 'wordpoints_edd_sl_module_licenses', 'network' );
				$licenses[ $channel->url ][ $module['ID'] ]['license'] = sanitize_key( $_POST[ "license_key-{$url}-{$module['ID']}" ] );
				wordpoints_update_network_option( 'wordpoints_edd_sl_module_licenses', $licenses );
			}

			if (
				isset( $_POST['edd-activate-license'], $_POST[ "wordpoints_activate_license_key-{$module['ID']}" ] )
				&& wp_verify_nonce( $_POST[ "wordpoints_activate_license_key-{$module['ID']}" ], "wordpoints_activate_license_key-{$module['ID']}" )
			) {
				$result = $this->activate_license( $channel, $module );

				if ( false === $result ) {
					wordpoints_show_admin_error( esc_html__( 'There was an error while trying to activate the license. Please try again.', 'wordpointsorg' ) );
				} elseif ( 'invalid' === $result ) {
					wordpoints_show_admin_error( esc_html__( 'That license key is invalid.', 'wordpointsorg' ) );
				} else {
					wordpoints_show_admin_message( esc_html__( 'License activated.', 'wordpointsorg' ) );
				}

			} elseif (
				isset( $_POST['edd-deactivate-license'], $_POST[ "wordpoints_deactivate_license_key-{$module['ID']}" ] )
				&& wp_verify_nonce( $_POST[ "wordpoints_deactivate_license_key-{$module['ID']}" ], "wordpoints_deactivate_license_key-{$module['ID']}" )
			) {

				$result = $this->deactivate_license( $channel, $module );

				if ( false === $result ) {
					wordpoints_show_admin_error( esc_html__( 'There was an error while trying to deactivate the license. Please try again.', 'wordpointsorg' ) );
				} elseif ( 'failed' === $result ) {
					wordpoints_show_admin_error( esc_html__( 'There was an error while trying to deactivate the license. Your license may be expired or invalid, or may already be deactivated.', 'wordpointsorg' ) );
				} else {
					wordpoints_show_admin_message( esc_html__( 'License deactivated.', 'wordpointsorg' ) );
				}
			}
		}

		return $modules;
	}

	/**
	 * Add the license key rows to the modules list table.
	 *
	 * @since 1.0.0
	 *
	 * @WordPress\action wordpoints_after_module_row Added by self::hooks().
	 */
	public function wordpoints_after_module_row( $module_file, $module_data ) {

		$channel = wordpoints_get_channel_for_module( $module_data );
		$channel = WordPoints_Module_Channels::get( $channel );

		if ( ! $channel || $this !== $channel->get_api() ) {
			return;
		}

		$license = $status = false;

		$license_data = $this->get_module_license_data( $channel, $module_data['ID'] );

		if ( isset( $license_data['status'] ) ) {
			$status = $license_data['status'];
		}

		if ( isset( $license_data['license'] ) ) {
			$license = $license_data['license'];
		}

		?>
		<tr>
			<td colspan="<?php echo (int) WordPoints_Modules_List_Table::instance()->get_column_count(); ?>" style="border-bottom: 1px solid #ddd;" class="colspanchange">
				<label class="description" for="license_key-<?php echo esc_attr( $channel->url ); ?>-<?php echo esc_attr( $module_data['ID'] ); ?>">
					<?php esc_html_e( 'License key', 'wordpointsorg' ); ?>
				</label>
				<input
					id="license_key-<?php echo esc_attr( $channel->url ); ?>-<?php echo esc_attr( $module_data['ID'] ); ?>"
					name="license_key-<?php echo esc_attr( $channel->url ); ?>-<?php echo esc_attr( $module_data['ID'] ); ?>"
					type="password"
					class="regular-text"
					autocomplete="off"
					value="<?php echo esc_attr( $license ); ?>"
				/>
				<?php if ( false !== $status && 'valid' === $status ) : ?>
					<span style="color:green;"><?php esc_html_e( 'active', 'wordpointsorg' ); ?></span>
					<?php wp_nonce_field( "wordpoints_deactivate_license_key-{$module_data['ID']}", "wordpoints_deactivate_license_key-{$module_data['ID']}" ); ?>
					<input type="submit" name="edd-deactivate-license" class="button-secondary" value="<?php esc_attr_e( 'Deactivate License', 'wordpointsorg' ); ?>" />
				<?php else : ?>
					<?php wp_nonce_field( "wordpoints_activate_license_key-{$module_data['ID']}", "wordpoints_activate_license_key-{$module_data['ID']}" ); ?>
					<input type="submit"name="edd-activate-license"  class="button-secondary" value="<?php esc_attr_e( 'Activate License', 'wordpointsorg' ); ?>" />
				<?php endif; ?>
			</td>
		</tr>
		<?php
	}

	/**
	 * @since 1.0.0
	 */
	public function check_for_updates( $channel ) {

		$modules = $channel->modules->get();

		$updates = array();

		if ( empty( $modules ) ) {
			return $updates;
		}

		$info = wordpoints_get_array_option( 'wordpoints_edd_sl_module_info', 'network' );

		foreach ( $modules as $file => $module ) {

			if ( ! $this->module_has_valid_license( $channel, $module['ID'] ) ) {
				continue;
			}

			$response = $this->request( 'get_version', $channel, $module );

			if (
				is_array( $response )
				&& isset( $response['new_version'] )
				&& version_compare( $module['version'], $response['new_version'], '<' )
			) {
				$updates[ $file ] = $response['new_version'];
				$info[ $channel->url ][ $module['ID'] ] = $response;
			}
		}

		wordpoints_update_network_option( 'wordpoints_edd_sl_module_info', $info );

		return $updates;
	}

	/**
	 * @since 1.0.0
	 */
	public function get_package_url( $channel, $module ) {

		return $this->get_module_information( $channel, $module['ID'], 'package' );
	}

	/**
	 * @since 1.0.0
	 */
	public function get_changelog_url( $channel, $module ) {

		return $this->get_module_information( $channel, $module['ID'], 'url' );
	}

	/**
	 * Disable SSL verification in order to prevent download update failures.
	 *
	 * @since 1.0.0
	 */
	public function http_request_args( $args, $url ) {

		if ( false !== strpos( $url, 'https://' ) && strpos( $url, 'edd_action=package_download' ) ) {
			$args['sslverify'] = false;
		}

		return $args;
	}

	/**
	 * Retrieve the information for a module.
	 *
	 * @since 1.0.0
	 *
	 * @param WordPoints_Module_Channel $channel   The module channel.
	 * @param string                    $module_id The module's ID.
	 * @param string                    $key       The piece of info to get.
	 *
	 * @return mixed The remote information for this module.
	 */
	protected function get_module_information( $channel, $module_id, $key = null ) {

		// Back-compat for pre-1.1.0.
		if ( is_array( $module_id ) ) {
			_deprecated_argument( __METHOD__, '1.1.0', 'The $module parameter is now expected to be a module ID.' );
			$module_id = $module_id['ID'];
		}

		$all_info = wordpoints_get_array_option( 'wordpoints_edd_sl_module_info', 'network' );

		if ( ! isset( $all_info[ $channel->url ][ $module_id ] ) ) {
			return false;
		}

		if ( isset( $key ) ) {
			if ( isset( $all_info[ $channel->url ][ $module_id ][ $key ] ) ) {
				return $all_info[ $channel->url ][ $module_id ][ $key ];
			} else {
				return false;
			}
		} else {
			return $all_info[ $channel->url ][ $module_id ];
		}
	}

	/**
	 * Save the information for a module.
	 *
	 * @since 1.1.0
	 *
	 * @param WordPoints_Module_Channel $channel   The module channel.
	 * @param string                    $module_id The module's ID.
	 * @param mixed                     $info      The information to save.
	 * @param string                    $key       The piece of info to get.
	 */
	protected function set_module_information( $channel, $module_id, $info, $key = null ) {

		$all_info = wordpoints_get_array_option( 'wordpoints_edd_sl_module_info', 'network' );

		if ( isset( $key ) ) {
			$all_info[ $channel->url ][ $module_id ][ $key ] = $info;
		} else {
			$all_info[ $channel->url ][ $module_id ] = $info;
		}

		wordpoints_update_network_option( 'wordpoints_edd_sl_module_info', $all_info );
	}

	/**
	 * Perform a request to a remote channel.
	 *
	 * The possible actions include the following:
	 * - get_information    Get the information for a module.
	 * - get_version        Same as above.
	 * - activate_license   Activate the license for a module.
	 * - deactivate_license Dectivate the license for a module.
	 * - check_license      Check the status of a module's license.
	 *
	 * For all of these you shoul just pass in an array of a module's data as the
	 * $data argument.
	 *
	 * @since 1.0.0
	 *
	 * @param string                    $action  The action name for this request.
	 * @param WordPoints_Module_Channel $channel The channel to make this request on.
	 * @param array                     $data    {
	 *        Other optional data to send in the request (if applicable).
	 *
	 *        @type string $ID          The ID of the module the request is for.
	 *        @type string $module_name The name of the module the request is for.
	 *        @type string $author      The name of the author of the module the request is for.
	 * }
	 *
	 * @return array|false The response, or false on error.
	 */
	private function request( $action, $channel, $data ) {

		$params = array();

		switch ( $action ) {

			case 'get_version':
			case 'get_information':
			case 'activate_license':
			case 'deactivate_license':
			case 'check_license':
				$license_data = $this->get_module_license_data( $channel, $data['ID'] );

				$params = array(
					'license'   => isset( $license_data['license'] ) ? $license_data['license'] : '',
					'item_name' => $data['name'],
					'item_id'   => $data['ID'],
				);
			break;
		}

		$defaults = array( 'edd_action' => $action, 'url' => home_url() );
		$params = array_merge( $params, $defaults );

		return $this->_remote_post( $channel, $params );
	}

	/**
	 * Perform a remote POST request to a channel.
	 *
	 * @since 1.0.0
	 *
	 * @param WordPoints_Module_Channel $channel The channel to make the request to.
	 * @param array                     $params  The parameters for the request.
	 *
	 * @return array|false The decoded JSON response, or false if there was an error.
	 */
	private function _remote_post( $channel, $params ) {

		$args = array( 'timeout' => 15, 'sslverify' => false, 'body' => $params );

		$response = wp_remote_post( $channel->get_full_url(), $args );

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$response = json_decode( wp_remote_retrieve_body( $response ), true );

		if (
			$response
			&& isset( $response['sections'] )
			&& is_string( $response['sections'] )
			&& 1 !== preg_match( '~O:\d~', $response['sections'] ) // No object injection.
		) {
			$response['sections'] = maybe_unserialize( $response['sections'] );
		}

		return $response;
	}
}

// EOF
