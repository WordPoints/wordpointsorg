<?php

/**
 * Bulk WordPoints.org module updgrader skin class.
 *
 * @package WordPointsOrg
 * @since 1.0.0
 */

/**
 * Bulk WordPoints module upgrader skin.
 *
 * @since 1.0.0
 */
class WordPointsOrg_Bulk_Module_Upgrader_Skin extends Bulk_Upgrader_Skin {

	//
	// Public Vars.
	//

	/**
	 * The module data.
	 *
	 * This is filled in by WordPointsOrg_Module_Upgrader::bulk_upgrade().
	 *
	 * @since 1.0.0
	 *
	 * @type array $module_info
	 */
	public $module_info = array();

	//
	// Public Methods.
	//

	/**
	 * Add the string's skins.
	 *
	 * @since 1.0.0
	 */
	public function add_strings() {

		parent::add_strings();
		$this->upgrader->strings['skin_before_update_header'] = __( 'Updating Module %1$s (%2$d/%3$d)', 'wordpointsorg' );
	}

	/**
	 * @since 1.0.0
	 */
	public function before( $title = '' ) {

		parent::before( $this->module_info['name'] );
	}

	/**
	 * @since 1.0.0
	 */
	public function after( $title = '' ) {

		parent::after( $this->module_info['name'] );
	}

	/**
	 * Display the footer.
	 *
	 * @since 1.0.0
	 */
	public function bulk_footer() {

		parent::bulk_footer();

		$update_actions = array(
			'modules_page' => '<a href="' . esc_url( self_admin_url( 'admin.php?page=wordpoints_modules' ) ) . '" target="_parent">' . esc_html__( 'Return to Modules page', 'wordpointsorg' ) . '</a>',
			'updates_page' => '<a href="' . esc_url( self_admin_url( 'update-core.php' ) ) . '" target="_parent">' . esc_html__( 'Return to WordPress Updates', 'wordpointsorg' ) . '</a>',
		);

		if ( ! current_user_can( 'activate_wordpoints_modules' ) ) {
			unset( $update_actions['modules_page'] );
		}

		/**
		 * The action links for the bulk module update footer.
		 *
		 * @since 1.0.0
		 *
		 * @param array $update_actions {
		 *        HTML for links to appear in the bulk module updates footer.
		 *
		 *        @type string $modules_page Go to the modules page. Not available if
		 *                                   the user doesn't have the
		 *                                   'activate_wordpoints_modules' capability.
		 *        @type string $updates_page Go to the WordPress updates page.
		 * }
		 * @param array $module_info The module's data.
		 */
		$update_actions = apply_filters( 'wordpointsorg_bulk_update_modules_complete_actions', $update_actions, $this->module_info );

		if ( ! empty( $update_actions ) ) {
			$this->feedback( implode( ' | ', (array) $update_actions ) );
		}
	}

} // class WordPoints_Bulk_Module_Upgrader_Skin

// EOF
