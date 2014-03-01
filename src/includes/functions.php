<?php

/**
 * General functions of the module.
 *
 * @since 1.0.0
 */

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
 *
 * @filter map_meta_cap
 *
 * @param array  $caps The user's capabilities.
 * @param string $cap  The current capability in question.
 *
 * @return array The user's capabilities.
 */
function wordpointsorg_map_custom_meta_caps( $caps, $cap ) {

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
}
add_filter( 'map_meta_cap', 'wordpointsorg_map_custom_meta_caps', 10, 2 );
