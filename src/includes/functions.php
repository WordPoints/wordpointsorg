<?php

/**
 * General functions of the module.
 *
 * @package WordPointsOrg
 * @since 1.0.0
 */

/**
 * Activate the module.
 *
 * @since 1.0.0
 *
 * @param bool $network_active Whether the plugin is being network activated.
 */
function wordpointsorg_activate( $network_active ) {

	/**
	 * The module's un/installer.
	 *
	 * @since 1.0.0
	 */
	require_once( WORDPOINTSORG_DIR . '/includes/class-un-installer.php' );

	$installer = new WordPointsOrg_Un_Installer();
	$installer->install( $network_active );
}
wordpoints_register_module_activation_hook( WORDPOINTSORG_DIR . '/wordpointsorg.php', 'wordpointsorg_activate' );

/**
 * Deactivate the module.
 *
 * @since 1.0.0
 */
function wordpointsorg_deactivate() {

	$timestamp = wp_next_scheduled( 'wordpoints_check_for_module_updates' );

	if ( $timestamp ) {
		wp_unschedule_event( $timestamp, 'wordpoints_check_for_module_updates' );
	}
}
wordpoints_register_module_deactivation_hook(
	WORDPOINTSORG_DIR . 'wordpointsorg.php'
	, 'wordpointsorg_deactivate'
);

/**
 * Get the custom capabilities added by the module.
 *
 * The custom caps are keys, the corresponding core caps are values.
 *
 * @since 1.0.0
 *
 * @return array The module's custom capabilities.
 */
function wordpointsorg_get_custom_caps() {

	return array( 'update_wordpoints_modules' => 'update_plugins' );
}

/**
 * Map custom meta capabilities of the module.
 *
 * @since 1.0.0
 */
function wordpointsorg_map_custom_meta_caps( $caps, $cap, $user_id ) {

	switch ( $cap ) {
		case 'update_wordpoints_modules':
			if ( defined( 'DISALLOW_FILE_MODS' ) && DISALLOW_FILE_MODS ) {
				$caps[] = 'do_not_allow';
			} elseif ( is_multisite() && ! is_super_admin( $user_id ) ) {
				$caps[] = 'do_not_allow';
			} else {
				$caps[] = $cap;
			}
		break;
	}

	return $caps;
}
add_filter( 'map_meta_cap', 'wordpointsorg_map_custom_meta_caps', 10, 3 );

/**
 * Get installed translations for WordPoints extensions.
 *
 * Currently the only translations are for WordPoints Modules.
 *
 * @since 1.0.0
 *
 * @param string $type The type of extension to retrieve translations for.
 *
 * @return array An array of language data.
 */
function wordpoints_get_installed_translations( $type ) {

	if ( 'modules' !== $type ) {
		return array();
	}

	$dir = "/wordpoints-{$type}";

	if ( ! is_dir( WP_LANG_DIR ) || ! is_dir( WP_LANG_DIR . $dir ) ) {
		return array();
	}

	$files = scandir( WP_LANG_DIR . $dir );

	if ( empty( $files ) ) {
		return array();
	}

	$language_data = array();

	foreach ( $files as $file ) {

		if ( '.' === $file[0] || is_dir( $file ) || substr( $file, -3 ) !== '.po' ) {
			continue;
		}

		if ( ! preg_match( '/(?:(.+)-)?([A-Za-z_]{2,6}).po/', $file, $match ) ) {
			continue;
		}

		list( , $textdomain, $language ) = $match;

		if ( '' === $textdomain ) {
			$textdomain = 'default';
		}

		$language_data[ $textdomain ][ $language ] = wp_get_pomo_file_data( WP_LANG_DIR . "$dir/$file" );
	}

	return $language_data;
}

/**
 * Load the module's text domain.
 *
 * @since 1.0.0
 */
function wordpointsorg_load_textdomain() {

	wordpoints_load_module_textdomain(
		'wordpointsorg'
		, wordpoints_module_basename( WORDPOINTSORG_DIR ) . '/languages'
	);
}
add_action( 'wordpoints_modules_loaded', 'wordpointsorg_load_textdomain' );

/**
 * Supply the list of HTML tags allowed in a module changelog.
 *
 * @since 1.1.0
 *
 * @WordPress\filter wp_kses_allowed_html
 */
function wordpointsorg_module_changelog_allowed_html( $allowed_tags, $context ) {

	if ( 'wordpoints_module_changelog' !== $context ) {
		return $allowed_tags;
	}

	return array(
		'a' => array( 'href' => array(), 'title' => array(), 'target' => array() ),
		'abbr' => array( 'title' => array() ),
		'acronym' => array( 'title' => array() ),
		'code' => array(),
		'pre' => array(),
		'em' => array(),
		'strong' => array(),
		'div' => array( 'class' => array() ),
		'span' => array( 'class' => array() ),
		'p' => array(),
		'ul' => array(),
		'ol' => array(),
		'li' => array(),
		'h1' => array(),
		'h2' => array(),
		'h3' => array(),
		'h4' => array(),
		'h5' => array(),
		'h6' => array(),
		'img' => array( 'src' => array(), 'class' => array(), 'alt' => array() ),
	);
}
add_filter( 'wp_kses_allowed_html', 'wordpointsorg_module_changelog_allowed_html', 10, 2 );

// EOF
