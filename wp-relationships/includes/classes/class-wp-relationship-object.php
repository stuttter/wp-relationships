<?php

/**
 * Relationships Object
 *
 * @package Plugins/Relationships/Objects/Class
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Relationship Object Class
 *
 * @since 0.1.0
 */
class WP_Relationship_Object extends WP_Relationship_Base {

	/**
	 * Class that defines a single object.
	 *
	 * @since 0.1.0
	 * @access public
	 * @var string
	 */
	public $object = '';

	/**
	 * Class that defines a query object.
	 *
	 * @since 0.1.0
	 * @access public
	 * @var string
	 */
	public $query = '';

	/**
	 * Creates a new WP_Relationship_Object object.
	 *
	 * Will populate object properties from the object provided and assign other
	 * default properties based on that information.
	 *
	 * @since 0.1.0
	 * @access public
	 *
	 * @param WP_Relationship_Object|object|array $object A status object.
	 */
	public function __construct( $object ) {
		parent::__construct( $object );
	}
}