<?php

/**
 * Relationships Functions
 *
 * @package Plugins/Relationships/Functions
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Wrapper for admin URLs
 *
 * @since 0.1.0
 *
 * @param array $args
 * @return array
 */
function wp_relationships_admin_url( $args = array() ) {

	// Parse args
	$r = wp_parse_args( $args, array(
		'page' => 'relationships',
	) );

	// Location
	$admin_url = admin_url( 'index.php' );

	// Add query args
	$url = add_query_arg( $r, $admin_url );

	// Add args and return
	return apply_filters( 'wp_relationships_admin_url', $url, $admin_url, $r, $args );
}

/**
 * Get all available relationship statuses
 *
 * @since 0.1.0
 *
 * @return array
 */
function wp_relationships_get_types() {
	static $types = null;

	// Register types
	if ( null === $types ) {
		$types = array();

		// Post/Post
		$types[] = new WP_Relationship_Type( array(
			'type_id'   => 'post_post',
			'type_name' => _x( 'Post to Post', 'object relationships', 'wp-relationships' )
		) );

		// Term/Post
		$types[] = new WP_Relationship_Type( array(
			'type_id'   => 'term_post',
			'type_name' => _x( 'Term to Post', 'object relationships', 'wp-relationships' )
		) );

		// Comment/Post
		$types[] = new WP_Relationship_Type( array(
			'type_id'   => 'comment_post',
			'type_name' => _x( 'Comment to Post', 'object relationships', 'wp-relationships' )
		) );

		// User/Post
		$types[] = new WP_Relationship_Type( array(
			'type_id'   => 'user_post',
			'type_name' => _x( 'User to Post', 'object relationships', 'wp-relationships' )
		) );

		// User/Comment
		$types[] = new WP_Relationship_Type( array(
			'type_id'   => 'user_comment',
			'type_name' => _x( 'User to Comment', 'object relationships', 'wp-relationships' )
		) );

		// Term/Term
		$types[] = new WP_Relationship_Type( array(
			'type_id'   => 'term_term',
			'type_name' => _x( 'Term to Term', 'object relationships', 'wp-relationships' )
		) );

		// User/Term
		$types[] = new WP_Relationship_Type( array(
			'type_id'   => 'user_term',
			'type_name' => _x( 'User to Term', 'object relationships', 'wp-relationships' )
		) );

		// User/User
		$types[] = new WP_Relationship_Type( array(
			'type_id'   => 'user_user',
			'type_name' => _x( 'User to User', 'object relationships', 'wp-relationships' )
		) );
	}

	// Filter & return
	return apply_filters( 'wp_relationships_get_types', $types );
}

/**
 * Get all available relationship statuses
 *
 * @since 0.1.0
 *
 * @return array
 */
function wp_relationships_get_statuses() {
	static $statuses = null;

	if ( is_null( $statuses ) ) {
		$statuses = array();

		// Active
		$statuses[] = new WP_Relationship_Status( array(
			'status_id'   => 'active',
			'status_name' => _x( 'Active', 'object relationships', 'wp-relationships' )
		) );

		// Inactive
		$statuses[] = new WP_Relationship_Status( array(
			'status_id'   => 'inactive',
			'status_name' => _x( 'Inactive', 'object relationships', 'wp-relationships' )
		) );
	}

	// Filter & return
	return apply_filters( 'wp_relationships_get_statuses', $statuses );
}

/**
 * Sanitize requested relationship ID values
 *
 * @since 0.1.0
 *
 * @param bool $single To get a single relationship ID
 * @return mixed
 */
function wp_relationships_sanitize_relationship_ids( $single = false ) {

	// Map IDs to integers
	$retval = isset( $_REQUEST['relationship_ids'] )
		? array_map( 'absint', (array) $_REQUEST['relationship_ids'] )
		: array();

	// Return the first item
	if ( true === $single ) {
		$retval = reset( $retval );
	}

	// Filter & return
	return apply_filters( 'wp_relationships_sanitize_relationship_ids', $retval, $single );
}

/**
 * Retrieves relationship data given a relationship ID or relationship object.
 *
 * relationship data will be cached and returned after being passed through a filter.
 *
 * @since 2.0.0
 *
 * @param WP_Relationship|int|null $relationship Optional. relationship to retrieve.
 * @return WP_Relationship|null The site object or null if not found.
 */
function get_object_relationship( $relationship = null ) {

	// Bail if no relationship
	if ( empty( $relationship ) ) {
		return null;
	}

	// Try to get a relationship instance
	if ( $relationship instanceof WP_Relationship ) {
		$_relationship = $relationship;
	} elseif ( is_object( $relationship ) ) {
		$_relationship = new WP_Relationship( $relationship );
	} else {
		$_relationship = WP_Relationship::get_instance( $relationship );
	}

	// Bail if no relationship
	if ( empty( $_relationship ) ) {
		return null;
	}

	/**
	 * Fires after a relationship is retrieved.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Relationship $_relationship relationship data.
	 * @param mixes                  $relationship  the original value.
	 */
	$_relationship = apply_filters( 'get_object_relationship', $_relationship, $relationship );

	return $_relationship;
}
