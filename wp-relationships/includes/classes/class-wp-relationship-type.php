<?php

/**
 * Relationships Type
 *
 * @package Plugins/Relationships/Type/Class
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Relationship Type Class
 *
 * @since 0.1.0
 */
final class WP_Relationship_Type extends WP_Relationship_Base {

	/**
	 * Object containing "from" properties
	 *
	 * @since 0.1.0
	 * @access public
	 * @var WP_Relationship_Object
	 */
	public $from;

	/**
	 * Object containing "to" properties
	 *
	 * @since 0.1.0
	 * @access public
	 * @var WP_Relationship_Object
	 */
	public $to;

	/**
	 * Creates a new WP_Relationship_Type object.
	 *
	 * Will populate object properties from the object provided and assign other
	 * default properties based on that information.
	 *
	 * @since 0.1.0
	 * @access public
	 *
	 * @param WP_Relationship_Type|object|array $type A status object.
	 */
	public function __construct( $type ) {
		parent::__construct( $type );
	}
}
