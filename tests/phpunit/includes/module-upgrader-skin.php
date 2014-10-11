<?php

/**
 * WordPoints.org module upgrader skin class for use in the PHPUnit tests.
 *
 * @package WordPointsOrg\Tests
 * @since 1.0.0
 */

/**
 * WordPoints.org module upgrader skin.
 *
 * @since 1.0.0
 */
class WordPointsOrg_Tests_Module_Upgrader_Skin extends WordPointsOrg_Module_Upgrader_Skin {

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

	public function header() {
		$this->header_shown++;
	}

	public function footer() {
		$this->footer_shown++;
	}

	public function error( $errors ) {
		$this->errors[] = $errors;
	}

	public function feedback( $string ) {
		$this->feedback[] = $string;
	}
}

// EOF
