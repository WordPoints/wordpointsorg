<?php




//			'downloading_package'  => __( 'Downloading install package from <span class="code">%s</span>&#8230;', 'wordpoints' ),



	/**
	 * Called before install.
	 *
	 * @since $ver$
	 */
	public function before() {

		if ( ! empty( $this->api ) ) {
			$this->upgrader->strings['process_success'] = sprintf( __( 'Successfully installed the module <strong>%s %s</strong>.', 'wordpoints' ), $this->api->name, $this->api->version );
		}
	}





	if ( $clear_update_cache )
		delete_site_transient( 'wordpoints_update_modules' ); // TODO move this to .org





			'<p>' . sprintf( __( 'You can find additional modules for your site by using the <a href="%1$s">module Browser/Installer</a> functionality or by browsing the <a href="%2$s" target="_blank">WordPress Module Directory</a> directly and installing new modules manually. To manually install a module you generally just need to upload the module file into your <code>/wp-content/wordpoints-modules</code> directory. Once a module has been installed, you can activate it here.', 'wordpoints' ), 'module-install.php', 'http://wordpress.org/modules/') . '</p>' .






add_action( 'wordpoints_deleted_modules', 'remove_deleted_modules_from_updates_transient' );



wp_enqueue_script( 'module-install' );
add_thickbox();




/**
 * Display module information in dialog box form.
 *
 * @since 1.0.0
 */
function wordpointsorg_install_module_information() {

	global $tab;

	$api = wordpointsorg_modules_api(
		array( 'id' => (int) $_GET['module_id'] )
	);

	if ( is_wp_error( $api ) ) {
		wp_die( $api );
	}

	$modules_allowedtags = array(
		'a' => array( 'href' => array(), 'title' => array(), 'target' => array() ),
		'abbr' => array( 'title' => array() ), 'acronym' => array( 'title' => array() ),
		'code' => array(), 'pre' => array(), 'em' => array(), 'strong' => array(),
		'div' => array( 'class' => array() ), 'span' => array( 'class' => array() ),
		'p' => array(), 'ul' => array(), 'ol' => array(), 'li' => array(),
		'h1' => array(), 'h2' => array(), 'h3' => array(), 'h4' => array(), 'h5' => array(), 'h6' => array(),
		'img' => array( 'src' => array(), 'class' => array(), 'alt' => array() )
	);

	$modules_section_titles = array(
		'description'  => _x( 'Description',  'Module installer section title' ),
		'installation' => _x( 'Installation', 'Module installer section title' ),
		'faq'          => _x( 'FAQ',          'Module installer section title' ),
		'screenshots'  => _x( 'Screenshots',  'Module installer section title' ),
		'changelog'    => _x( 'Changelog',    'Module installer section title' ),
		'other_notes'  => _x( 'Other Notes',  'Module installer section title' )
	);

	// Sanitize HTML.
	foreach ( (array) $api->sections as $section_name => $content ) {
		$api->sections[ $section_name ] = wp_kses( $content, $modules_allowedtags );
	}

	foreach ( array( 'version', 'author', 'requires', 'tested', 'homepage', 'downloaded', 'slug' ) as $key ) {
		if ( isset( $api->$key ) ) {
			$api->$key = wp_kses( $api->$key, $modules_allowedtags );
		}
	}

	$section = isset( $_REQUEST['section'] )
		? wp_unslash( $_REQUEST['section'] )
		: 'description';

	if ( empty( $section ) || ! isset( $api->sections[ $section ] ) ) {
		$section_titles = array_keys( (array) $api->sections );
		$section = array_shift( $section_titles );
	}

	iframe_header( __( 'Module Install', 'wordpointsorg' ) );

	$_with_banner = '';

	if (
		! empty( $api->banners )
		&& (
			! empty( $api->banners['low'] )
			|| ! empty( $api->banners['high'] )
		)
	) {

		$_with_banner = 'with-banner';
		$low  = empty( $api->banners['low'] ) ? $api->banners['high'] : $api->banners['low'];
		$high = empty( $api->banners['high'] ) ? $api->banners['low'] : $api->banners['high'];

		?>

		<style type="text/css">
			#module-information-title.with-banner {
				background-image: url( <?php echo esc_url( $low ); ?> );
			}
			@media only screen and ( -webkit-min-device-pixel-ratio: 1.5 ) {
				#module-information-title.with-banner {
					background-image: url( <?php echo esc_url( $high ); ?> );
				}
			}
		</style>

		<?php
	}

	?>

	<div id="wordpoints-module-information-scrollable">
		<div id="<?php echo esc_attr( $tab ); ?>-title" class="<?php echo esc_attr( $_with_banner ); ?>">
			<div class='vignette'></div>
			<h2><?php echo esc_attr( $api['name'] ); ?></h2>
		</div>

		<div id="<?php echo esc_attr( $tab ); ?>-tabs" class="<?php echo esc_attr( $_with_banner ); ?>">

			<?php

			foreach ( (array) $api->sections as $section_name => $content ) {

				if (
					'reviews' === $section_name
					&& (
						empty( $api->ratings )
						|| 0 === array_sum( (array) $api->ratings )
					)
				) {
					continue;
				}

				if ( isset( $modules_section_titles[ $section_name ] ) ) {
					$title = $modules_section_titles[ $section_name ];
				} else {
					$title = ucwords( str_replace( '_', ' ', $section_name ) );
				}

				$href = add_query_arg(
					array( 'tab' => $tab, 'section' => $section_name )
				);

				?>

				<a name="<?php echo esc_attr( $section_name ); ?>" href="<?php echo esc_attr( esc_url( $href ) ); ?>"<?php echo ( $section_name === $section ) ? ' class="current"' : ''; ?>><?php echo esc_html( $title ); ?></a>

				<?php
			}

			?>

		</div>

		<div id="<?php echo esc_attr( $tab ); ?>-content" class="<?php echo esc_attr( $_with_banner ); ?>">
			<div class="fyi">
				<ul>
					<li><strong><?php esc_html_e( 'Version:' ); ?></strong> <?php echo esc_html( $api['version'] ); ?></li>
					<li><strong><?php esc_html_e( 'Author:' ); ?></strong> <?php echo links_add_target( wp_kses( $api['author'] ), '_blank' ); ?></li>
				<?php if ( ! empty( $api->last_updated ) ) { ?>
					<li><strong><?php _e( 'Last Updated:' ); ?></strong> <span title="<?php echo $api->last_updated; ?>">
						<?php printf( __( '%s ago' ), human_time_diff( strtotime( $api->last_updated ) ) ); ?>
					</span></li>
				<?php } if ( ! empty( $api->requires ) ) { ?>
					<li><strong><?php _e( 'Requires WordPress Version:' ); ?></strong> <?php printf( __( '%s or higher' ), $api->requires ); ?></li>
				<?php } if ( ! empty( $api->tested ) ) { ?>
					<li><strong><?php _e( 'Compatible up to:' ); ?></strong> <?php echo $api->tested; ?></li>
				<?php } if ( ! empty( $api->downloaded ) ) { ?>
					<li><strong><?php _e( 'Downloaded:' ); ?></strong> <?php printf( _n( '%s time', '%s times', $api->downloaded ), number_format_i18n( $api->downloaded ) ); ?></li>
					<li><a target="_blank" href="<?php echo esc_attr( esc_url( $api['homepage'] ) ); ?>/"><?php esc_html_e( 'Module Page &#187;' ); ?></a></li>
				<?php } if ( ! empty( $api->homepage ) ) { ?>
					<li><a target="_blank" href="<?php echo esc_url( $api->url ); ?>"><?php _e( 'Module Homepage &#187;' ); ?></a></li>
				<?php } ?>
				<?php if ( ! empty( $api['donate_link'] ) ) : ?>
					<li><a target="_blank" href="<?php echo esc_attr( esc_url( $api['donate_link'] ) ); ?>"><?php _e( 'Donate to this module &#187;' ); ?></a></li>
				<?php endif; ?>
				</ul>
			</div>

			<div id="section-holder" class="wrap">
				<?php

				if (
					! empty( $api->tested )
					&& version_compare( substr( $GLOBALS['wp_version'], 0, strlen( $api->tested ) ), $api->tested, '>' )
				) {

					wordpoints_show_admin_error( __( '<strong>Warning:</strong> This module has <strong>not been tested</strong> with your current version of WordPress.' ) );

				} elseif (
					! empty( $api->requires )
					&& version_compare( substr( $GLOBALS['wp_version'], 0, strlen( $api->requires ) ), $api->requires, '<' )
				) {

					wordpoints_show_admin_error( __( '<strong>Warning:</strong> This plugin has <strong>not been marked as compatible</strong> with your version of WordPress.' ) );
				}

				foreach ( (array) $api['sections'] as $section_name => $content ) {

					$content = links_add_base_url( $content, $api['url'] );
					$content = links_add_target( $content, '_blank' );

					$display = ( $section_name === $section ) ? 'block' : 'none';

					?>

					<div id="section-<?php esc_attr( $section_name ); ?>" class="section" style="display: <?php echo esc_attr( $display ); ?>;">
						<?php echo wp_kses( $content, $modules_allowedtags ); ?>
					</div>

					<?php
				}

				?>

			</div>
		</div>
	</div>

	<div id="<?php echo esc_attr( $tab ); ?>-footer">
		<?php

		if ( ! empty( $api->download_link ) && ( current_user_can( 'install_wordpoints_modules' ) || current_user_can( 'update_wordpoints_modules' ) ) ) {
			$status = wordpointsorg_module_install_status( $api );
			switch ( $status['status'] ) {
				case 'install':
					if ( $status['url'] ) {
						?><a class="button button-primary right" href="<?php echo esc_attr( esc_url( $status['url'] ) ); ?>" target="_parent"><?php esc_html_e( 'Install Now' ); ?></a><?php
					}
				break;

				case 'update_available':
					if ( $status['url'] ) {
						?><a class="button button-primary right" href="<?php echo esc_attr( esc_url( $status['url'] ) ); ?>" target="_parent"><?php esc_html_e( 'Install Update Now' ); ?></a><?php
					}
				break;

				case 'newer_installed':
					?><a class="button button-primary right disabled"><?php echo esc_html( sprintf( __( 'Newer Version (%s) Installed'), $status['version'] ) ); ?></a><?php
				break;

				case 'latest_installed':
					?><a class="button button-primary right disabled"><?php esc_html_e( 'Latest Version Installed' ); ?></a><?php
				break;
			}
		}

		?>
	</div>

	<?php

	iframe_footer();
	exit;
}
add_action( 'wordpoints_install_modules_pre-module-information', 'wordpointsorg_install_module_information' );


			$action_links[] = '<a href="' . esc_attr( esc_url( self_admin_url( 'admin.php?page=wordpoints_install_modules&amp;tab=module-information&amp;module=' . $module['ID'] .
								'&amp;TB_iframe=true&amp;width=600&amp;height=550' ) ) ) . '" class="thickbox" aria-label="' .
								esc_attr( sprintf( __( 'More information about %s', 'wordpoints' ), $name ) ) . '">' . __( 'Details', 'wordpoints' ) . '</a>';

