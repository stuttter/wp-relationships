<?php

/**
 * Relationships Type
 *
 * @package Plugins/Relationships/Type/Class
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * relationship Class
 *
 * @since 0.1.0
 */
final class WP_Relationship_Type {

	/**
	 * Type ID.
	 *
	 * @since 0.1.0
	 * @access public
	 * @var string
	 */
	public $type_id;

	/**
	 * Name.
	 *
	 * @since 0.1.0
	 * @access public
	 * @var string
	 */
	public $type_name;

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

		// Convert to array
		if ( is_object( $type ) ) {
			$type = get_object_vars( $type );
		}

		// Set values
		if ( ! empty( $type ) && is_array( $type ) ) {
			foreach ( $type as $key => $value ) {
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
			case 'type_id':
				return sanitize_key( $this->type_id );
			case 'type_name':
				return $this->type_name;
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
			case 'type_id' :
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
			case 'type_id' :
				$this->type_id = sanitize_key( $value );
				break;
			default:
				$this->{$key} = $value;
		}
	}
}
