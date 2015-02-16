<?php

/**
 * Set up on a remote server for the HTTP tests.
 *
 * @package WordPointsOrg
 * @since 1.1.0
 */

/**
 * Class to handle the remote simulators.
 *
 * @since 1.1.0
 */
class WordPointsOrg_Tests_Remote_Simulators {

	/**
	 * The registered simulators.
	 *
	 * @since 1.1.0
	 *
	 * @var WordPointsOrg_Tests_Remote_Simulator[]
	 */
	protected static $simulators;

	/**
	 * Register a simulator.
	 *
	 * @since 1.1.0
	 *
	 * @param string $slug  The unique slug for this simulator.
	 * @param string $class The name of the simulator's class.
	 */
	public static function register( $slug, $class ) {
		self::$simulators[ $slug ] = $class;
	}

	/**
	 * Initialize the simulators.
	 *
	 * @since 1.1.0
	 */
	public static function init() {

		$simulator = getenv( 'HTTP_X_WORDPOINTSORG_TESTS_API' );

		if ( ! isset( self::$simulators[ $simulator ] ) ) {
			return;
		}

		new self::$simulators[ $simulator ];
	}
}

/**
 * Class to simulate a particular site configuration on the remote server.
 *
 * @since 1.1.0
 */
abstract class WordPointsOrg_Tests_Remote_Simulator {

	/**
	 * @since 1.1.0
	 */
	public function __construct() {

		add_action( 'muplugins_loaded', array( $this, 'load_dependencies' ) );
		add_action( 'init', array( $this, 'start' ) );
		add_action( 'shutdown', array( $this, 'shutdown' ) );
	}

	/**
	 * Load any dependencies needed to respond to the request.
	 *
	 * @since 1.1.0
	 *
	 * @WordPress\action muplugins_loaded Added by the constructor.
	 */
	public function load_dependencies() {}

	/**
	 * Begin the simulation.
	 *
	 * @since 1.1.0
	 *
	 * @WordPress\action init Added by the constructor.
	 */
	public function start() {

		global $wpdb;

		$wpdb->query( 'START TRANSACTION' );

		$this->setup();
	}

	/**
	 * Set up for handling the request once the simulator has started.
	 *
	 * @since 1.1.0
	 */
	public function setup() {}

	/**
	 * End the simulation.
	 *
	 * @since 1.1.0
	 */
	public function stop() {

		global $wpdb;

		$wpdb->query( 'ROLLBACK' );
	}

	/**
	 * Restore the site to vanilla on shutdown.
	 *
	 * @since 1.1.0
	 *
	 * @WordPress\action shutdown Added by the constructor.
	 */
	function shutdown () {
		$this->stop();
	}
}

/**
 * Set up the remote server to handle an EDD Software Licenses request.
 *
 * @since 1.1.0
 */
class WordPointsOrg_Tests_Remote_EDD_Software_Licenses_Simulator
	extends WordPointsOrg_Tests_Remote_Simulator {

	/**
	 * @since 1.1.0
	 */
	public function load_dependencies() {

		include_once( WP_PLUGIN_DIR . '/easy-digital-downloads/easy-digital-downloads.php' );
		include_once( WP_PLUGIN_DIR . '/edd-software-licensing/edd-software-licenses.php' );
	}

	/**
	 * @since 1.1.0
	 */
	public function setup() {

		// Create the download.
		wp_insert_post(
			array(
				'import_id'   => 123,
				'post_type'   => 'download',
				'post_status' => 'publish',
				'post_title'  => 'Test Download',
			)
		);

		add_post_meta( 123, 'edd_price', '100.00' );

		// Create the license.
		$license_id = wp_insert_post(
			array( 'post_type' => 'edd_license', 'post_status' => 'publish' )
		);

		add_post_meta( $license_id, '_edd_sl_key', 'testkey' );
		add_post_meta( $license_id, '_edd_sl_download_id', 123 );
		add_post_meta( $license_id, '_edd_sl_expiration', time() + DAY_IN_SECONDS );

		// Add a second license.
		$license_id = wp_insert_post(
			array( 'post_type' => 'edd_license', 'post_status' => 'publish' )
		);

		add_post_meta( $license_id, '_edd_sl_key', 'testkey_2' );
		add_post_meta( $license_id, '_edd_sl_download_id', 123 );
		add_post_meta( $license_id, '_edd_sl_expiration', time() + DAY_IN_SECONDS );
		add_post_meta( $license_id, '_edd_sl_status', 'active' );

		edd_software_licensing()->insert_site( $license_id, $_POST['url'] );
	}
}
WordPointsOrg_Tests_Remote_Simulators::register(
	'edd-software-licensing'
	, 'WordPointsOrg_Tests_Remote_EDD_Software_Licenses_Simulator'
);

/**
 * Set up the remote server to handle an EDD Software Licenses Free requests.
 *
 * @since 1.1.0
 */
class WordPointsOrg_Tests_Remote_EDD_Software_Licenses_Free_Simulator
	extends WordPointsOrg_Tests_Remote_EDD_Software_Licenses_Simulator {

	/**
	 * @since 1.1.0
	 */
	public function load_dependencies() {

		parent::load_dependencies();

		include_once( WP_PLUGIN_DIR . '/edd-sl-free/edd-sl-free.php' );
	}

	/**
	 * @since 1.1.0
	 */
	public function setup() {

		// Create the download.
		wp_insert_post(
			array(
				'import_id'   => 124,
				'post_type'   => 'download',
				'post_status' => 'publish',
				'post_title'  => 'Free Download',
			)
		);

		parent::setup();
	}
}
WordPointsOrg_Tests_Remote_Simulators::register(
	'edd-software-licensing-free'
	, 'WordPointsOrg_Tests_Remote_EDD_Software_Licenses_Free_Simulator'
);

WordPointsOrg_Tests_Remote_Simulators::init();

// EOF
