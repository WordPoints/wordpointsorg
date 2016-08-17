<?php

/**
 * WordPoints.org module bulk upgrader skin class for use in the PHPUnit tests.
 *
 * @package WordPointsOrg\Tests
 * @since 1.0.0
 */

/**
 * WordPoints.org module bulk upgrader skin.
 *
 * @since 1.0.0
 */
class WordPointsOrg_Tests_Module_Bulk_Upgrader_Skin extends WordPointsOrg_Bulk_Module_Upgrader_Skin {

	/**
	 * A count of the number of times the header was shown.
	 *
	 * @since 1.0.0
	 *
	 * @type int $header_shown
	 */
	public $header_shown = 0;

	/**
	 * A count of the number of times the footer was shown.
	 *
	 * @since 1.0.0
	 *
	 * @type int $footer_shown
	 */
	public $footer_shown = 0;

	/**
	 * A count of the number of times the bulk header was shown.
	 *
	 * @since 1.0.0
	 *
	 * @type int $bulk_header_shown
	 */
	public $bulk_header_shown = 0;

	/**
	 * A count of the number of times the bulk footer was shown.
	 *
	 * @since 1.0.0
	 *
	 * @type int $bulk_footer_shown
	 */
	public $bulk_footer_shown = 0;

	/**
	 * A list of errors reported by the skin.
	 *
	 * @since 1.0.0
	 *
	 * @type string[] $errors
	 */
	public $errors = array();

	/**
	 * A list of the feedback displayed to the user.
	 *
	 * @since 1.0.0
	 *
	 * @type string[]
	 */
	public $feedback;

	/**
	 * @since 1.0.0
	 */
	public function header() {
		$this->header_shown++;
	}

	/**
	 * @since 1.0.0
	 */
	public function bulk_header() {
		$this->bulk_header_shown++;
	}

	/**
	 * @since 1.0.0
	 */
	public function footer() {
		$this->footer_shown++;
	}

	/**
	 * @since 1.0.0
	 */
	public function bulk_footer() {
		$this->bulk_footer_shown++;
	}

	/**
	 * @since 1.0.0
	 */
	public function before( $title = '' ) {}

	/**
	 * @since 1.0.0
	 */
	public function after( $title = '' ) {}

	/**
	 * @since 1.0.0
	 */
	public function error( $errors ) {
		$this->errors[] = $errors;
	}

	/**
	 * @since 1.0.0
	 */
	public function feedback( $string ) {
		$this->feedback[] = $string;
	}
}

// EOF
