<?php

/**
 * Relationships API
 *
 * @package Plugins/Relationships/API
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Register a relationship type.
 *
 * @since 0.1.0
 *
 * @param array $args
 * @return WP_Relationship_Type|bool False on failure, object on success.
 */
function wp_register_relationship_type( $args = array() ) {

	$type = false;

	do_action( 'wp_register_relationship_type', $type, $args );

	return $type;
}

/**
 * Get a relationship type.
 *
 * @param string $type
 *
 * @return WP_Relationship_Type|bool False if no relationship, instance on success.
 */
function wp_get_relationship_type( $type = '' ) {
	$types = wp_relationships_get_types();
	$type  = wp_list_filter( $types, array( 'id' => $type ) );
	return reset( $type );
}

/**
 * Create a relationship
 *
 * @since 0.1.0
 *
 * @param array $args Relationship information.
 * @param array $meta Relationship metadata.
 *
 * @return WP_Relationship|bool False on failure, object on success.
 */
function wp_create_relationship( $args = array(), $meta = array() ) {

	// Try to create a relationship
	$relationship = WP_Relationship::create( $args );

	// Bail if no relationship was created
	if ( empty( $relationship ) || is_wp_error( $relationship ) ) {
		return false;
	}

	// Maybe add metadata
	foreach ( $meta as $key => $value ) {
		add_relationship_meta( $relationship->relationship_id, $key, $value );
	}

	do_action( 'wp_created_relationship', $relationship, $args, $meta );

	return $relationship;
}

/**
 * Delete relationship using relationship IDs.
 *
 * @since 0.1.0
 *
 * @param int|array $relationship_ids Relationship IDs
 *
 * @return int Number of relationships deleted
 */
function wp_delete_relationship( $relationship_ids = '' ) {

	// Default count
	$count = 0;

	// Bail if no relationship
	if ( empty( $relationship_ids ) ) {
		return $count;
	}

	// Map to int
	$relationship_ids = array_map( 'absint', (array) $relationship_ids );

	do_action( 'wp_delete_relationships', $relationship_ids );

	// Delete relationships
	foreach ( $relationship_ids as $relationship_id ) {
		$relationship = WP_Relationship::get_instance( $relationship_id );
		$deleted      = $relationship->delete();

		// Bump count
		if ( ! empty( $deleted ) && !is_wp_error( $deleted ) ) {
			++$count;
		}
	}

	do_action( 'wp_deleted_relationships', $relationship_ids );

	return $count;
}

/**
 * Delete relationships. Alias of wp_delete_relationship().
 *
 * @since 0.1.0
 *
 * @param int|array $relationship_ids Relationship IDs
 *
 * @return int Number of relationships deleted
 */
function wp_delete_relationships( $relationship_ids = array() ) {
	return wp_delete_relationship( $relationship_ids );
}
