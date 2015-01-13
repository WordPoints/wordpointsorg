<?php

/**
 * Abstract classes to act as item containers.
 *
 * @package WordPointsOrg
 * @since 1.0.0
 */

/**
 * Abstract class to be a container for items.
 *
 * @since 1.0.0
 */
abstract class WordPoints_Container {

	/**
	 * The items in this container.
	 *
	 * @since 1.0.0
	 *
	 * @type array $items
	 */
	protected $items = array();

	/**
	 * A class to instantiate new items as.
	 *
	 * @since 1.0.0
	 *
	 * @type string $item_class
	 */
	protected $item_class;

	/**
	 * Add an item to the collection.
	 *
	 * @since 1.0.0
	 *
	 * @param string $slug The unique identifier for this item.
	 * @param mixed  $item The item being added.
	 *
	 * @return bool|object Whether the item was added successfully, or the item
	 *                     object if self::$item_class is used.
	 */
	protected function _add( $slug, $item, $class = null ) {

		if ( $this->_contains( $slug ) ) {
			return false;
		}

		if ( isset( $class ) ) {
			$this->items[ $slug ] = new $class( $slug, $item );
			return $this->items[ $slug ];
		} elseif ( isset( $this->item_class ) ) {
			$this->items[ $slug ] = new $this->item_class( $slug, $item );
			return $this->items[ $slug ];
		}

		$this->items[ $slug ] = $item;

		return true;
	}

	/**
	 * Remove an item from the collection.
	 *
	 * @since 1.0.0
	 *
	 * @param string $slug The slug of the item to remove.
	 */
	protected function _remove( $slug ) {
		unset( $this->items[ $slug ] );
	}

	/**
	 * Check if this collection contains a specific item.
	 *
	 * @since 1.0.0
	 *
	 * @param string $slug The slug of the item to check for.
	 *
	 * @return bool Whether the item is present.
	 */
	protected function _contains( $slug ) {
		return isset( $this->items[ $slug ] );
	}

	/**
	 * Get an item from the collection.
	 *
	 * @since 1.0.0
	 *
	 * @param string $slug The slug of the item to get. If ommitted, all items are
	 *                     returned.
	 *
	 * @return mixed The item or the entire collection if $slug is ommitted. False
	 *               if the item does not exist.
	 */
	protected function _get( $slug = null ) {

		if ( ! isset( $slug ) ) {
			return $this->items;
		}

		if ( ! $this->_contains( $slug ) ) {
			return false;
		}

		return $this->items[ $slug ];
	}
}

/**
 * Class to be a item container object.
 *
 * @since 1.0.0
 */
class WordPoints_Container_Object extends WordPoints_Container {

	/**
	 * @since 1.0.0
	 *
	 * @see WordPoints_Container::_add()
	 */
	public function add( $slug, $item ) {
		return $this->_add( $slug, $item );
	}

	/**
	 * @since 1.0.0
	 *
	 * @see WordPoints_Container::_remove()
	 */
	public function remove( $slug ) {
		return $this->_remove( $slug );
	}

	/**
	 * @since 1.0.0
	 *
	 * @see WordPoints_Container::_contains()
	 */
	public function contains( $slug ) {
		return $this->_contains( $slug );
	}

	/**
	 * @since 1.0.0
	 *
	 * @see WordPoints_Container::_get()
	 */
	public function get( $slug = null ) {
		return $this->_get( $slug );
	}
}

/**
 * Class to be a static container for items.
 *
 * Note that this API cannot be fully implemented for static classes until PHP 5.3.
 *
 * @since 1.0.0
 */
class WordPoints_Container_Static extends WordPoints_Container {

	/**
	 * The container instance.
	 *
	 * @since 1.0.0
	 *
	 * @type WordPoints_Container_Static $instance
	 */
	protected static $instance;

	/**
	 * The name of the child class which is extending this one.
	 *
	 * @since 1.0.0
	 *
	 * @type string $child_class
	 */
	protected static $child_class = __CLASS__;

	/**
	 * Construction is not allowed.
	 *
	 * @since 1.0.0
	 */
	protected function __construct() {}

	/**
	 * We sleep the perpetual sleep.
	 *
	 * @since 1.0.0
	 */
	private function __wakeup() {}

	/**
	 * There can be no others.
	 *
	 * @since 1.0.0
	 */
	private function __clone() {}
}

// EOF
