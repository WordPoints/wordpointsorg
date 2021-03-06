<?php

/**
 * A test case for the container classes.
 *
 * @package WordPoints\Tests
 * @since 1.0.0
 */

/**
 * Test that the container classes work correctly.
 *
 * @since 1.0.0
 */
class WordPoints_Container_Test extends WP_UnitTestCase {

	/**
	 * Test that the object container works.
	 *
	 * @since 1.0.0
	 *
	 * @covers WordPoints_Container_Object
	 */
	public function test_object_container() {

		$container = new WordPoints_Container_Object_Test;

		$this->assertSame( array(), $container->get() );
		$this->assertSame( false, $container->get( 'one' ) );
		$this->assertFalse( $container->contains( 'one' ) );

		$container->add( 'one', 1 );

		$this->assertTrue( $container->contains( 'one' ) );
		$this->assertSame( 1, $container->get( 'one' ) );
		$this->assertSame( array( 'one' => 1 ), $container->get() );

		$container->add( 'two', 2 );

		$this->assertTrue( $container->contains( 'one' ) );
		$this->assertTrue( $container->contains( 'two' ) );
		$this->assertSame( 1, $container->get( 'one' ) );
		$this->assertSame( 2, $container->get( 'two' ) );
		$this->assertSame( array( 'one' => 1, 'two' => 2 ), $container->get() );

		$container->remove( 'one' );

		$this->assertFalse( $container->contains( 'one' ) );
		$this->assertTrue( $container->contains( 'two' ) );
		$this->assertFalse( $container->get( 'one' ) );
		$this->assertSame( 2, $container->get( 'two' ) );
		$this->assertSame( array( 'two' => 2 ), $container->get() );
	}

	/**
	 * Test that the object container works.
	 *
	 * @since 1.0.0
	 *
	 * @covers WordPoints_Container_Static
	 */
	public function test_static_container() {

		$this->markTestSkipped( 'Static containers will not be fully implemented until 5.3' );

		WordPoints_Container_Static_Test::init();

		$this->assertSame( null, WordPoints_Container_Static_Test::get() );
		$this->assertSame( false, WordPoints_Container_Static_Test::get( 'one' ) );
		$this->assertFalse( WordPoints_Container_Static_Test::is_registered( 'one' ) );

		WordPoints_Container_Static_Test::register( 'one', 1 );

		$this->assertTrue( WordPoints_Container_Static_Test::is_registered( 'one' ) );
		$this->assertSame( 1, WordPoints_Container_Static_Test::get( 'one' ) );
		$this->assertSame( array( 'one' => 1 ), WordPoints_Container_Static_Test::get() );

		WordPoints_Container_Static_Test::register( 'two', 2 );

		$this->assertTrue( WordPoints_Container_Static_Test::is_registered( 'one' ) );
		$this->assertTrue( WordPoints_Container_Static_Test::is_registered( 'two' ) );
		$this->assertSame( 1, WordPoints_Container_Static_Test::get( 'one' ) );
		$this->assertSame( 2, WordPoints_Container_Static_Test::get( 'two' ) );
		$this->assertSame( array( 'one' => 1, 'two' => 2 ), WordPoints_Container_Static_Test::get() );

		WordPoints_Container_Static_Test::deregister( 'one' );

		$this->assertFalse( WordPoints_Container_Static_Test::is_registered( 'one' ) );
		$this->assertTrue( WordPoints_Container_Static_Test::is_registered( 'two' ) );
		$this->assertSame( null, WordPoints_Container_Static_Test::get( 'one' ) );
		$this->assertSame( 2, WordPoints_Container_Static_Test::get( 'two' ) );
		$this->assertSame( array( 'two' => 2 ), WordPoints_Container_Static_Test::get() );
	}
}

if ( class_exists( 'WordPoints_Container_Object' ) ) {

	/**
	 * Mock for use in the object container tests.
	 *
	 * @since 1.0.0
	 */
	class WordPoints_Container_Object_Test extends WordPoints_Container_Object {}

	/**
	 * Mock for use in the object container tests.
	 *
	 * @since 1.0.0
	 */
	class WordPoints_Container_Static_Test extends WordPoints_Container_Static {}
}

// EOF
