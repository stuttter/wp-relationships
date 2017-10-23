<?php

/**
 * Relationships Status
 *
 * @package Plugins/Relationships/Status/Class
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * relationship Class
 *
 * @since 0.1.0
 */
final class WP_Relationship_Status extends WP_Relationship_Base {

	/**
	 * Creates a new WP_Relationship_Status object.
	 *
	 * Will populate object properties from the object provided and assign other
	 * default properties based on that information.
	 *
	 * @since 0.1.0
	 * @access public
	 *
	 * @param WP_Relationship_Status|object|array $status A status object.
	 */
	public function __construct( $status ) {
		parent::__construct( $status );
	}
}
