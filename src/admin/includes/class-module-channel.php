<?php

/**
 * Module channel classes.
 *
 * @package WordPointsOrg
 * @since 1.0.0
 */

/**
 * Stores a list of the available module channels.
 *
 * Modules are installed and updated through various channels. This class is used to
 * maintain a list of the available channels.
 *
 * @since 1.0.0
 *
 * @see WordPoints_Module_Channel The object used to represent each channel.
 */
final class WordPoints_Module_Channels extends WordPoints_Container_Static {

	/**
	 * @since 1.0.0
	 */
	protected static $instance;

	/**
	 * @since 1.0.0
	 */
	protected $item_class = 'WordPoints_Module_Channel';

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
	public static function deregister( $slug ) {
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
	 * @return WordPoints_Module_Channel[]|WordPoints_Module_Channel
	 */
	public static function get( $slug = null ) {
		return self::$instance->_get( $slug );
	}
}
WordPoints_Module_Channels::init();

/**
 * Module API channel class.
 *
 * A channel is similar to a channel on TV or radio. In this case, a channel is one
 * of many available module providers. It is a module repository, which offers
 * modules for download, and may also provide updates, etc.
 *
 * A channel object is usually created automatically based on the Channel header of
 * one of the installed modules. This file header specifies the channel URL which
 * should be used with that module.
 *
 * A channel is accessed through an API, which actually handles the requests to the
 * remote URL of this channel. This class is only intended to represent the channel
 * itself.
 *
 * @since 1.0.0
 *
 * @property-read WordPoints_Container_Object $modules
 * @property-read string                      $url
 */
final class WordPoints_Module_Channel {

	/**
	 * The URL of this channel.
	 *
	 * This is the channel URL, though it usually doesn't include the scheme.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	private $url;

	/**
	 * The list of installed modules that use this channel.
	 *
	 * @since 1.0.0
	 *
	 * @var WordPoints_Container_Object
	 */
	private $modules;

	/**
	 * Construct the class.
	 *
	 * @since 1.0.0
	 *
	 * @param string $url The channel's URL, sans the scheme.
	 */
	public function __construct( $url ) {

		$this->url = $url;
		$this->modules = new WordPoints_Container_Object;
	}

	/**
	 * @since 1.0.0
	 */
	public function __get( $var ) {

		if ( 'modules' === $var || 'url' === $var ) {
			return $this->$var;
		}
	}

	/**
	 * Get the full channel URL, including the HTTP scheme.
	 *
	 * @since 1.0.0
	 *
	 * @return string The channel's full URL.
	 */
	public function get_full_url() {

		$url = 'http://' . $this->url;

		if ( $this->is_ssl_accessible() ) {
			$url = set_url_scheme( $url, 'https' );
		}

		return $url;
	}

	/**
	 * Check if this channel is accessible over SSL.
	 *
	 * @since 1.0.0
	 *
	 * @return bool Whether the channel URL can be accessed over SSL.
	 */
	public function is_ssl_accessible() {

		$transient = 'wrdpnts_' . md5( "module_channel_supports_ssl-{$this->url}" );

		$supports_ssl = get_site_transient( $transient );

		// If the transient has expired.
		if ( false === $supports_ssl ) {

			// The cached value is an integer so we can tell when the transient has expired.
			$supports_ssl = 0;

			if ( wp_http_supports( array( 'ssl' ) ) ) {

				$response = wp_remote_get( 'https://' . $this->url, array( 'sslverify' => false ) );

				if ( ! is_wp_error( $response ) ) {

					$status = wp_remote_retrieve_response_code( $response );

					if ( 200 === (int) $status || 401 === (int) $status ) {
						$supports_ssl = 1;
					}
				}
			}

			set_site_transient( $transient, $supports_ssl, WEEK_IN_SECONDS );
		}

		return (bool) $supports_ssl;
	}

	/**
	 * Get the API used by this channel.
	 *
	 * @since 1.0.0
	 *
	 * @return WordPoints_Module_API|false The module API handler, or false if none available.
	 */
	public function get_api() {

		// Check if there is a cached value available.
		$transient = 'wrdpnts_' . md5( "module_channel_supports-{$this->url}" );

		$api = get_site_transient( $transient );

		// If the transient has expired.
		if ( false === $api ) {

			// Get the API specified by the remote URL.
			$api = $this->get_api_header();

			// Save it as a string, so we can tell when it has expired.
			set_site_transient( $transient, (string) $api, WEEK_IN_SECONDS );
		}

		return WordPoints_Module_APIs::get( $api );
	}

	/**
	 * Retrieve and parse the module API header from the remote channel.
	 *
	 * The remote channel can specify the supported API by sending the
	 * x-wordpoints-module-api header. This allows the API to be looked up with
	 * a single HEAD request.
	 *
	 * @since 1.0.0
	 *
	 * @return array|false The slug of the API specified in the header, or false
	 *                     the channel doesn't set this header.
	 */
	protected function get_api_header() {

		$headers = wp_get_http_headers( $this->get_full_url() );

		if ( ! isset( $headers['x-wordpoints-module-api'] ) ) {
			return false;
		}

		return sanitize_key( $headers['x-wordpoints-module-api'] );
	}
}

// EOF
