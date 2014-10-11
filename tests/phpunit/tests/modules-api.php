<?php

/**
 * A test case for wordpointsorg_modules_api().
 *
 * @package WordPointsOrg\Tests
 * @since 1.0.0
 */

/**
 * Test wordpointsorg_modules_api().
 *
 * @since 1.0.0
 */
class WordPointsOrg_Modules_API_Test extends WordPointsOrg_HTTP_UnitTestCase {

	/**
	 * Test a multiple module request.
	 *
	 * @since 1.0.0
	 */
	public function test_multiple_module_request() {

		$this->http_responder = array( $this, 'multiple_module_response' );

		$response = wordpointsorg_modules_api( array( 'post__in' => array( 3, 4 ) ) );

		$this->assertCount( 1, $this->http_requests );

		$this->assertEquals( 'https://wordpoints.org/wp-json/modules/', $this->http_requests[0]['url'] );

		$request = $this->http_requests[0]['request'];

		$this->assertArrayHasKey( 'post__in', $request['body'] );
		$this->assertCount( 2, $request['body']['post__in'] );
		$this->assertContains( 3, $request['body']['post__in'] );
		$this->assertContains( 4, $request['body']['post__in'] );

		$this->assertInternalType( 'array', $response );
		$_response = $this->multiple_module_response();
		$this->assertEquals( json_decode( $_response['body'], true ), $response );
	}

	/**
	 * Test a single module request.
	 *
	 * @since 1.0.0
	 */
	public function test_single_module_request() {

		$this->http_responder = array( $this, 'single_module_response' );

		$response = wordpointsorg_modules_api( array(), array( 'id' => 3 ) );

		$this->assertCount( 1, $this->http_requests );

		$this->assertEquals( 'https://wordpoints.org/wp-json/modules/3', $this->http_requests[0]['url'] );

		$request = $this->http_requests[0]['request'];

		$this->assertEquals( array(), $request['body'] );

		$this->assertInternalType( 'array', $response );
		$_response = $this->single_module_response();
		$this->assertEquals( json_decode( $_response['body'], true ), $response );
	}

	//
	// Helpers.
	//

	/**
	 * Get a mock response from the wordpoints.org API for a multi-module request.
	 *
	 * @since 1.0.0
	 */
	protected function multiple_module_response() {

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

	/**
	 * Get a mock response from the wordpoints.org API for a single-module request.
	 *
	 * @since 1.0.0
	 */
	protected function single_module_response() {

		return array(
			'body' => '{
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
				}',
		);
	}
}

// EOF
