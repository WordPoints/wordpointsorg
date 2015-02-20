<?php

/**
 * Module API classes.
 *
 * @package WordPointsOrg
 * @since 1.0.0
 */

/**
 * Class representing the types of available update APIs.
 *
 * @since 1.0.0
 */
final class WordPoints_Module_APIs extends WordPoints_Container_Static {

	/**
	 * @since 1.0.0
	 */
	protected static $instance;

	/**
	 * Initialize the container.
	 *
	 * This function must be called before the container can be used.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if the container was initialized, false if it was already.
	 */
	public static function init() {

		if ( ! isset( self::$instance ) ) {

			self::$instance = new self;

			self::register_apis();

			return true;
		}

		return false;
	}

	/**
	 * @since 1.0.0
	 *
	 * @see WordPoints_Container::_add()
	 */
	public static function register( $slug, $item, $class = null ) {
		return self::$instance->_add( $slug, $item, $class );
	}

	/**
	 * @since 1.0.0
	 *
	 * @see WordPoints_Container::_remove()
	 */
	public static function deregister( $slug) {
		return self::$instance->_remove( $slug );
	}

	/**
	 * @since 1.0.0
	 *
	 * @see WordPoints_Container::_contains()
	 */
	public static function is_registered( $slug ) {
		return self::$instance->_contains( $slug );
	}

	/**
	 * @since 1.0.0
	 *
	 * @see WordPoints_Container::_get()
	 *
	 * @return WordPoints_Module_API[]|WordPoints_Module_API
	 */
	public static function get( $slug = null ) {
		return self::$instance->_get( $slug );
	}

	/**
	 * Call the action to register all of the installed APIs.
	 *
	 * @since 1.0.0
	 */
	private static function register_apis() {

		/**
		 * Register the available module APIs.
		 *
		 * @since 1.0.0
		 */
		do_action( 'wordpoints_register_module_apis' );
	}
}
add_action( 'admin_init', 'WordPoints_Module_APIs::init' );

/**
 * Abstract class for representing module update API types.
 *
 * Module update APIs are basically web URL endpoints that supply module updates.
 * Each API might be of a different type, using different GET parameters to identify
 * the module, for example. Each type of update API needs to be handled a little
 * differently, but they all have certain things in common. This class provides a
 * common base for all update API types. Each update API type then provides a common
 * handler for all APIs of that type.
 *
 * @since 1.0.0
 */
abstract class WordPoints_Module_API {

	/**
	 * The slug of the API type.
	 *
	 * @since 1.0.0
	 *
	 * @type string $slug
	 */
	protected $slug;

	/**
	 * The features supported by this API.
	 *
	 * @since 1.0.0
	 *
	 * @type array $supports
	 */
	protected $supports;

	/**
	 * Construct the API type with the slug and other data.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		$this->hooks();
	}

	/**
	 * Check if a given feature is supported by the API.
	 *
	 * @since 1.0.0
	 *
	 * @param string $feature The feature to check support for.
	 *
	 * @return bool Whether the feature is supported.
	 */
	public function supports( $feature ) {

		return isset( $this->supports[ $feature ] );
	}

	/**
	 * Hook up any actions and filters used by this API type.
	 *
	 * @snce 1.0.0
	 */
	public function hooks() {}

	/**
	 * Check for updates for the modules on a channel.
	 *
	 * @since 1.0.0
	 *
	 * @param WordPoints_Module_Channel $channel The channel to check for updates on.
	 *
	 * @return string[] The new versions of modules needing updates, indexed by module file.
	 */
	abstract public function check_for_updates( $channel );

	/**
	 * Get the URL of zip package for the latest version of a module.
	 *
	 * @since 1.0.0
	 *
	 * @param WordPoints_Module_Channel $channel The channel the package should be from.
	 * @param array                     $module  The module's data.
	 *
	 * @return string The package URL.
	 */
	abstract public function get_package_url( $channel, $module );

	/**
	 * Get the changelog for the latest version of a module.
	 *
	 * @since 1.1.0
	 *
	 * @param WordPoints_Module_Channel $channel The channel the changelog should be from.
	 * @param array                     $module  The module's data.
	 *
	 * @return string The changelog URL.
	 */
	abstract public function get_changelog( $channel, $module );
}

// EOF
