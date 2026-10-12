<?php

/**
 * Focused compatibility checks for legacy PHP notices.
 */

define( 'ABSPATH', dirname( __DIR__ ) . '/' );

set_error_handler( function( $severity, $message, $file, $line ) {
	throw new ErrorException( $message, 0, $severity, $file, $line );
} );

class WP_Relationship_Object {
	public $id;

	public function __construct( $args ) {
		$this->id = $args['id'];
	}
}

class WP_Relationship_Type extends WP_Relationship_Object {
}

function _x( $text ) {
	return $text;
}

function wp_list_filter( $objects, $args ) {
	$filtered = array();

	foreach ( $objects as $object ) {
		$matches = true;

		foreach ( $args as $property => $value ) {
			if ( ! isset( $object->{$property} ) || $value !== $object->{$property} ) {
				$matches = false;
				break;
			}
		}

		if ( $matches ) {
			$filtered[] = $object;
		}
	}

	return $filtered;
}

function apply_filters( $hook, $value ) {
	return $value;
}

require_once dirname( __DIR__ ) . '/wp-relationships/includes/functions/common.php';

$object = wp_relationships_get_object( array( 'id' => 'post' ) );

if ( ! $object instanceof WP_Relationship_Object || 'post' !== $object->id ) {
	throw new RuntimeException( 'wp_relationships_get_object() did not return the requested object.' );
}

$type = wp_relationships_get_type( array( 'id' => 'user_user' ) );

if ( ! $type instanceof WP_Relationship_Type || 'user_user' !== $type->id ) {
	throw new RuntimeException( 'wp_relationships_get_type() did not return the requested type.' );
}

require_once dirname( __DIR__ ) . '/wp-relationships/includes/functions/admin.php';
require_once dirname( __DIR__ ) . '/wp-relationships/includes/classes/class-wp-relationship.php';

$relationship                      = new WP_Relationship( new stdClass() );
$relationship->relationship_status = 'active';

if ( 'active' !== $relationship->status ) {
	throw new RuntimeException( 'WP_Relationship status access did not return the stored status.' );
}

restore_error_handler();

echo "Compatibility checks passed.\n";
