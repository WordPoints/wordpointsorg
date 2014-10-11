<?php

/**
 * Module Installer List Table class.
 *
 * @package WordPoints\Administration\Modules
 * @since $ver$
 * @access private
 */

/**
 * Display a list table for module installation.
 *
 * @since $ver$
 */
final class WordPoints_Module_Install_List_Table extends WP_List_Table {

	/**
	 * Checks whether the user has the required capabilities.
	 *
	 * @since $ver$
	 *
	 * @return bool Whether the user can install modules.
	 */
	function ajax_user_can() {

		return current_user_can( 'install_wordpoints_modules' );
	}

	/**
	 * Prepare the modules for display.
	 *
	 * @since $ver$
	 */
	function prepare_items() {

		global $tabs, $tab, $paged, $type, $term;

		wp_reset_vars( array( 'tab' ) );

		$paged = $this->get_pagenum();

		$per_page = 30;

		// These are the tabs which are shown on the page
		$tabs = array(
			'dashboard' => __( 'Search', 'wordpoints' ),
			'upload'    => __( 'Upload', 'wordpoints' ),
			'new'       => _x( 'Newest', 'Module Installer', 'wordpoints' ),
		);

		if ( 'search' === $tab ) {
			$tabs['search']	= __( 'Search Results', 'wordpoints' );
		}

		// Valid actions to perform which do not have a Menu item.
		$nonmenu_tabs = array( 'module-information' );

		/**
		 * Filter the tabs shown on the Module Install screen.
		 *
		 * @since $ver$
		 *
		 * @param array $tabs The tabs shown on the Module Install screen. Defaults are 'dashboard', 'search',
		 *                    'upload', and 'new'.
		 */
		$tabs = apply_filters( 'wordpoints_install_modules_tabs', $tabs );

		/**
		 * Filter tabs not associated with a menu item on the Module Install screen.
		 *
		 * @since $ver$
		 *
		 * @param array $nonmenu_tabs The tabs that don't have a Menu item on the Module Install screen.
		 */
		$nonmenu_tabs = apply_filters( 'wordpoints_install_modules_nonmenu_tabs', $nonmenu_tabs );

		// If a non-valid menu tab has been selected, And it's not a non-menu action.
		if ( empty( $tab ) || ( ! isset( $tabs[ $tab ] ) && ! in_array( $tab, (array) $nonmenu_tabs ) ) ) {
			$tab = key( $tabs );
		}

		$args = array( 'offset' => ( $paged - 1 ) * $per_page, 'posts_per_page' => $per_page );

		switch ( $tab ) {

			case 'search':
				if ( isset( $_REQUEST['s'] ) ) {
					$args['s'] = wp_unslash( $_REQUEST['s'] );
				}
			break;

			case 'dashboard':
			case 'new': break;

			default:
				$args = false;
		}

		/**
		 * Filter API request arguments for each Module Install screen tab.
		 *
		 * The dynamic portion of the hook name, $tab, refers to the module install tabs.
		 * Default tabs are 'dashboard', 'search', 'upload', and 'new'.
		 *
		 * @since $ver$
		 *
		 * @param array|bool $args Module Install API arguments.
		 */
		$args = apply_filters( "wordpoints_install_modules_table_api_args_{$tab}", $args );

		if ( ! $args ) {
			return;
		}

		$api = wordpointsorg_modules_api( $args );

		if ( is_wp_error( $api ) ) {
			wp_die( $api->get_error_message() . '</p> <p class="hide-if-no-js"><a href="#" onclick="document.location.reload(); return false;">' . esc_html__( 'Try again', 'wordpoints' ) . '</a>' );
		}

		$this->items = $api['modules'];

		$this->set_pagination_args(
			array(
				'total_items' => $api['total'],
				'per_page'    => $per_page,
			)
		);
	}

	/**
	 * Display a message for when no modules are found.
	 *
	 * @since $ver$
	 */
	function no_items() {

		esc_html_e( 'No modules match your request.', 'wordpoints' );
	}

	/**
	 * Get the table's tabs.
	 *
	 * @since $ver$
	 *
	 * @return array
	 */
	function get_views() {

		global $tabs, $tab;

		$display_tabs = array();

		foreach ( (array) $tabs as $action => $text ) {

			$href = self_admin_url( 'admin.php?page=wordpoints_install_modules&tab=' . $action );
			$display_tabs[ 'module-install-' . $action ] = '<a href="' . esc_attr( esc_url( $href ) ) . '"' . ( $action === $tab ? ' class="current"' : '' ) . '>' . esc_html( $text ) . '</a>';
		}

		return $display_tabs;
	}

	/**
	 * Display the table navigation.
	 *
	 * @since $ver$
	 *
	 * @param string $which Which navigation set to display, the top or bottom.
	 */
	function display_tablenav( $which ) {

		if ( 'top' ==  $which ) {

			?>

			<div class="tablenav top">
				<div class="alignleft actions">
					<?php
					/**
					 * Fires before the Module Install table header pagination is displayed.
					 *
					 * @since $ver$
					 */
					do_action( 'wordpoints_install_modules_table_header' );
					?>
				</div>
				<?php $this->pagination( $which ); ?>
				<br class="clear" />
			</div>

			<?php

		} else {

			?>

			<div class="tablenav bottom">
				<?php $this->pagination( $which ); ?>
				<br class="clear" />
			</div>

			<?php
		}
	}

	/**
	 * Get the classes for the table.
	 *
	 * @since $ver$
	 *
	 * @return string[] An array of classes th table should have.
	 */
	function get_table_classes() {

		return array( 'widefat', $this->_args['plural'] );
	}

	/**
	 * Get the titles for the table's columns.
	 *
	 * @since $ver$
	 *
	 * @return array The titles of the table's columns.
	 */
	function get_columns() {

		return array(
			'name'        => _x( 'Name', 'module name', 'wordpoints' ),
			'version'     => __( 'Version', 'wordpoints' ),
			'description' => __( 'Description', 'wordpoints' ),
		);
	}

	/**
	 * Display the table's rows.
	 *
	 * @since $ver$
	 */
	function display_rows() {

		$modules_allowed_tags = array(
			'a'       => array( 'title' => array(), 'href' => array(), 'target' => array() ),
			'abbr'    => array( 'title' => array() ),
			'acronym' => array( 'title' => array() ),
			'code'    => array(),
			'pre'     => array(),
			'em'      => array(),
			'strong'  => array(),
			'ul'      => array(),
			'ol'      => array(),
			'li'      => array(),
			'p'       => array(),
			'br'      => array()
		);

		list( $columns, $hidden ) = $this->get_column_info();

		$style = array();

		foreach ( $columns as $column_name => $column_display_name ) {

			$style[ $column_name ] = in_array( $column_name, $hidden ) ? 'style="display:none;"' : '';
		}

		foreach ( (array) $this->items as $module ) {

			if ( is_object( $module ) ) {
				$module = (array) $module;
			}

			$title = wp_kses( $module['title'], $modules_allowed_tags );

			/*
			 * Limit descriptions to 400char, and remove any HTML. Then remove any
			 * trailing entities, strip trailing/leading whitespace and condense
			 * multiple consecutive newlines. Finally, convert newline characters to
			 * <br> tabs.
			 */
			$description = strip_tags( $module['content'] );

			if ( strlen( $description ) > 400 ) {
				$description = mb_substr( $description, 0, 400 ) . '&#8230;';
			}

			$description = preg_replace( '/&[^;\s]{0,6}$/', '', $description );
			$description = trim( $description );
			$description = preg_replace( "|(\r?\n)+|", "\n", $description );
			$description = nl2br( $description );

			$version = wp_kses( $module['version'], $modules_allowed_tags );

			$name = strip_tags( $title . ' ' . $version );

			$author = $module['author_name'];

			if ( ! empty( $module['author'] ) ) {
				$author = ' <cite>' . sprintf( __( 'By %s' ), $author ) . '.</cite>';
			}

			$author = wp_kses( $author, $modules_allowed_tags );

			$action_links = array();
			$action_links[] = '<a href="' . esc_attr( esc_url( $module['link'] ) ). '"'
				. ' aria-label="' . esc_attr( sprintf( __( 'More information about %s', 'wordpoints' ), $name ) ). '"'
				. ' target="_blank">' . __( 'Details', 'wordpoints' ) . '</a>';

			if ( current_user_can( 'install_wordpoints_modules' ) || current_user_can( 'update_wordpoints_modules' ) ) {

				$status = wordpointsorg_module_install_status( $module );

				switch ( $status['status'] ) {

					case 'install':
						if ( $status['url'] )
							$action_links[] = '<a class="install-now" href="' . esc_attr( esc_url( $status['url'] ) ) . '">' . esc_html__( 'Install Now' ) . '</a>';
					break;

					case 'update_available':
						if ( $status['url'] )
							$action_links[] = '<a href="' . esc_attr( esc_url( $status['url'] ) ) . '">' . esc_hrml__( 'Update Now', 'wordpoints' ) . '</a>';
					break;

					case 'latest_installed':
					case 'newer_installed':
						$action_links[] = '<span>' . esc_html_x( 'Installed', 'module', 'wordpoints' ) . '</span>';
					break;
				}
			}

			/**
			 * Filter the install action links for a module.
			 *
			 * @since $ver$
			 *
			 * @param array $action_links An array of module action hyperlinks. Defaults are links to Details and Install Now.
			 * @param array $module       The module currently being listed.
			 */
			$action_links = apply_filters( 'wordpoints_module_install_action_links', $action_links, $module );

			?>

			<tr>
				<td class="name column-name"<?php echo $style['name']; ?>>
					<strong><?php echo $title; ?></strong>
					<div class="action-links">
						<?php if ( ! empty( $action_links ) ) : ?>
							<?php echo implode( ' | ', $action_links ); ?>
						<?php endif; ?>
					</div>
				</td>
				<td class="vers column-version"<?php echo $style['version']; ?>>
					<?php echo $version; ?>
				</td>
				<td class="desc column-description"<?php echo $style['description']; ?>>
					<?php echo $description, $author; ?>
				</td>
			</tr>

			<?php
		}
	}
}
