<?php

/**
 * Module API class for EDD Sofware Licensing with free modules too.
 *
 * @package WordPointsOrg
 * @since 1.1.0
 */

/**
 * Module API for channels using EDD Sofware Licensing and a shim for free modules.
 *
 * @since 1.1.0
 */
class WordPoints_EDD_Software_Licensing_Free_Module_API
	extends WordPoints_EDD_Software_Licensing_Module_API {

	/**
	 * @since 1.1.0
	 */
	protected $slug = 'edd-software-licensing-free';

	/**
	 * @since 1.1.0
	 */
	protected $supports = array(
		'updates' => true,
	);

	/**
	 * @since 1.1.0
	 */
	public function module_has_valid_license( $channel, $module_id ) {

		if ( $this->is_free_module( $channel, $module_id ) ) {
			return true;
		}

		return parent::module_has_valid_license( $channel, $module_id );
	}

	/**
	 * Check if a module is free.
	 *
	 * We check if we have the module's info saved, and if not we'll request it from
	 * the channel.
	 *
	 * @since 1.1.0
	 *
	 * @param WordPoints_Module_Channel $channel   The channel for this module.
	 * @param string                    $module_id The module's ID.
	 *
	 * @return bool Whether or not this module is free.
	 */
	public function is_free_module( $channel, $module_id ) {

		$info = $this->get_module_information( $channel, $module_id );

		if ( ! $info ) {

			// The 'name' isn't used by the remote API, but is expected by request().
			$info = $this->request(
				'get_version'
				, $channel
				, array( 'ID' => $module_id, 'name' => '' )
			);

			if ( ! is_array( $info ) ) {
				return false;
			}

			$this->set_module_information( $channel, $module_id, $info );
		}

		return ! empty( $info['is_free'] );
	}

	/**
	 * @since 1.1.0
	 */
	public function wordpoints_after_module_row( $module_file, $module_data ) {

		if ( empty( $module_data['ID'] ) || ! current_user_can( 'update_wordpoints_modules' ) ) {
			return;
		}

		$channel = wordpoints_get_channel_for_module( $module_data );
		$channel = WordPoints_Module_Channels::get( $channel );

		if ( ! $channel || $this !== $channel->get_api() ) {
			return;
		}

		if ( $this->is_free_module( $channel, $module_data['ID'] ) ) {
			return;
		}

		parent::wordpoints_after_module_row( $module_file, $module_data );
	}
}

// EOF
