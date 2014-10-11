<?php

/**
 * A test case for wordpointsorg_check_for_module_updates().
 *
 * @package WordPointsOrg\Tests
 * @since 1.0.0
 */

/**
 * Test wordpointsorg_check_for_module_updates().
 *
 * @since 1.0.0
 */
class WordPointsOrg_Check_For_Module_Updates_Test extends WordPointsOrg_HTTP_UnitTestCase {

	/**
	 * Test that request has the right data.
	 *
	 * @since 1.0.0
	 */
	public function test_request() {

		wordpointsorg_check_for_module_updates();

		$this->assertCount( 1, $this->http_requests );

		$this->assertEquals( 'https://wordpoints.org/wp-json/modules/', $this->http_requests[0]['url'] );

		$request = $this->http_requests[0]['request'];

		$this->assertArrayHasKey( 'post__in', $request['body'] );
		$this->assertCount( 2, $request['body']['post__in'] );
		$this->assertContains( 3, $request['body']['post__in'] );
		$this->assertContains( 4, $request['body']['post__in'] );
	}

	/**
	 * Test that the response is handled properly.
	 *
	 * @since 1.0.0
	 */
	public function test_reponse_handling() {

		$this->http_responder = array( $this, 'module_update_response' );

		$before = time();
		wordpointsorg_check_for_module_updates();
		$after = time();

		$updates = get_site_transient( 'wordpointsorg_update_modules' );

		$this->assertInternalType( 'array', $updates );

		$this->assertArrayHasKey( 'last_checked', $updates );
		$this->assertInternalType( 'int', $updates['last_checked'] );
		$this->assertGreaterThanOrEqual( $before, $updates['last_checked'] );
		$this->assertLessThanOrEqual( $after, $updates['last_checked'] );

		$this->assertArrayHasKey( 'response', $updates );
		$this->assertInternalType( 'array', $updates['response'] );

		$response = $this->module_update_response();
		$this->assertEquals( json_decode( $response['body'], true ), $updates['response'] );
	}

	//
	// Helpers.
	//

	/**
	 * Returns a mock response from the wordpoints.org module update API endpoint.
	 *
	 * @since 1.0.0
	 */
	protected function module_update_response() {

		return array(
			'body' => '[
					{
						"ID":3,
						"title":"Test 1",
						"author":1,
						"content":"<p>A test module.<\/p>\n",
						"link":"http:\/\/wordpoints.local\/modules\/test-1\/",
						"date":"2014-05-24T20:06:17+00:00",
						"modified":"2014-05-24T21:23:34+00:00",
						"slug":"test-1",
						"guid":"http:\/\/wordpoints.local\/?post_type=wordpoints_module&#038;p=3",
						"excerpt":"<p>A test module.<\/p>\n",
						"date_tz":"UTC",
						"date_gmt":"2014-05-24T20:06:17+00:00",
						"modified_tz":"UTC",
						"modified_gmt":"2014-05-24T21:23:34+00:00",
						"version":"1.0.0",
						"meta":{
							"links":{
								"self":"http:\/\/wordpoints.local\/wp-json\/modules\/3",
								"author":"http:\/\/wordpoints.local\/wp-json\/users\/1",
								"collection":"http:\/\/wordpoints.local\/wp-json\/modules"
							}
						}
					},
					{
						"ID":4,
						"title":"Test 2",
						"author":1,
						"content":"<p>A test module.<\/p>\n",
						"link":"http:\/\/wordpoints.local\/modules\/test-2\/",
						"date":"2014-05-24T20:06:17+00:00",
						"modified":"2014-05-24T21:23:34+00:00",
						"slug":"test-2",
						"guid":"http:\/\/wordpoints.local\/?post_type=wordpoints_module&#038;p=4",
						"excerpt":"<p>A test module.<\/p>\n",
						"date_tz":"UTC",
						"date_gmt":"2014-05-24T20:06:17+00:00",
						"modified_tz":"UTC",
						"modified_gmt":"2014-05-24T21:23:34+00:00",
						"version":"1.1.0",
						"meta":{
							"links":{
								"self":"http:\/\/wordpoints.local\/wp-json\/modules\/4",
								"author":"http:\/\/wordpoints.local\/wp-json\/users\/1",
								"collection":"http:\/\/wordpoints.local\/wp-json\/modules"
							}
						}
					}
				]',
		);
	}
}

// EOF
