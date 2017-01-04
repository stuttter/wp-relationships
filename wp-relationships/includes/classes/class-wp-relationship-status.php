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
final class WP_Relationship_Status {

	/**
	 * Status ID.
	 *
	 * @since 0.1.0
	 * @access public
	 * @var string
	 */
	public $id;

	/**
	 * Name.
	 *
	 * @since 0.1.0
	 * @access public
	 * @var string
	 */
	public $name;

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

		// Convert to array
		if ( is_object( $status ) ) {
			$status = get_object_vars( $status );
		}

		// Set values
		if ( ! empty( $status ) && is_array( $status ) ) {
			foreach ( $status as $key => $value ) {
				$this->{$key} = $value;
			}
		}
	}

	/**
	 * Converts an object to array.
	 *
	 * @since 0.1.0
	 * @access public
	 *
	 * @return array Object as array.
	 */
	public function to_array() {
		return get_object_vars( $this );
	}

	/**
	 * Getter.
	 *
	 * @since 0.1.0
	 * @access public
	 *
	 * @param string $key Property to get.
	 * @return mixed Value of the property. Null if not available.
	 */
	public function __get( $key = '' ) {
		switch ( $key ) {
			case 'id':
				return sanitize_key( $this->id );
			case 'name':
				return $this->name;
			default :
				return isset( $this->{$key} )
					? $this->{$key}
					: null;
		}

		return null;
	}

	/**
	 * Isset-er.
	 *
	 * @since 0.1.0
	 * @access public
	 *
	 * @param string $key Property to check if set.
	 * @return bool Whether the property is set.
	 */
	public function __isset( $key = '' ) {
		switch ( $key ) {
			case 'id' :
				return true;
			default :
				return isset( $this->{$key} );
		}

		return false;
	}

	/**
	 * Setter.
	 *
	 * @since 0.1.0
	 * @access public
	 *
	 * @param string $key   Property to set.
	 * @param mixed  $value Value to assign to the property.
	 */
	public function __set( $key, $value ) {
		switch ( $key ) {
			case 'id' :
				$this->id = sanitize_key( $value );
				break;
			default:
				$this->{$key} = $value;
		}
	}
}
