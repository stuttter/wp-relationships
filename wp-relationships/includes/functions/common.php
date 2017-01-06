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
	$admin_url = admin_url( 'admin.php' );

	// Add query args
	$url = add_query_arg( $r, $admin_url );

	// Add args and return
	return apply_filters( 'wp_relationships_admin_url', $url, $admin_url, $r, $args );
}

/**
 * Get relationship objects
 *
 * @since 0.1.0
 *
 * @param string $id ID to get an object by
 *
 * @return array
 */
function wp_relationships_get_objects( $id = '' ) {
	static $objects = null;

	// Register types
	if ( null === $objects ) {
		$objects = array();

		// User
		$objects[] = new WP_Relationship_Type( array(
			'id'     => 'user',
			'name'   => _x( 'User', 'object relationships', 'wp-relationships' ),
			'object' => 'WP_User',
			'query'  => 'WP_User_Query',
		) );

		// Post
		$objects[] = new WP_Relationship_Type( array(
			'id'     => 'post',
			'name'   => _x( 'Post', 'object relationships', 'wp-relationships' ),
			'object' => 'WP_Post',
			'query'  => 'WP_Query',
		) );

		// Term
		$objects[] = new WP_Relationship_Type( array(
			'id'     => 'term',
			'name'   => _x( 'Term', 'object relationships', 'wp-relationships' ),
			'object' => 'WP_Term',
			'query'  => 'WP_Term_Query',
		) );

		// Comment
		$objects[] = new WP_Relationship_Type( array(
			'id'     => 'comment',
			'name'   => _x( 'Comment', 'object relationships', 'wp-relationships' ),
			'object' => 'WP_Comment',
			'query'  => 'WP_Comment_Query',
		) );
	}

	// Get by ID
	$retval = ! empty( $id )
		? reset( wp_list_filter( $objects, array( 'id' => $id ) ) )
		: $objects;

	// Filter & return
	return apply_filters( 'wp_relationships_get_object', $retval, $objects, $id );
}

/**
 * Get relationship statuses
 *
 * @since 0.1.0
 *
 * @param string $id ID to get a type by
 *
 * @return array
 */
function wp_relationships_get_types( $id = '' ) {
	static $types = null;

	// Register types
	if ( null === $types ) {
		$types = array();

		// Post/Post
		$types[] = new WP_Relationship_Type( array(
			'id'   => 'post_post',
			'name' => _x( 'Post to Post', 'object relationships', 'wp-relationships' ),
		) );

		// Term/Post
		$types[] = new WP_Relationship_Type( array(
			'id'   => 'term_post',
			'name' => _x( 'Term to Post', 'object relationships', 'wp-relationships' )
		) );

		// Comment/Post
		$types[] = new WP_Relationship_Type( array(
			'id'   => 'comment_post',
			'name' => _x( 'Comment to Post', 'object relationships', 'wp-relationships' )
		) );

		// User/Post
		$types[] = new WP_Relationship_Type( array(
			'id'   => 'user_post',
			'name' => _x( 'User to Post', 'object relationships', 'wp-relationships' )
		) );

		// User/Comment
		$types[] = new WP_Relationship_Type( array(
			'id'   => 'user_comment',
			'name' => _x( 'User to Comment', 'object relationships', 'wp-relationships' )
		) );

		// Term/Term
		$types[] = new WP_Relationship_Type( array(
			'id'   => 'term_term',
			'name' => _x( 'Term to Term', 'object relationships', 'wp-relationships' )
		) );

		// User/Term
		$types[] = new WP_Relationship_Type( array(
			'id'   => 'user_term',
			'name' => _x( 'User to Term', 'object relationships', 'wp-relationships' )
		) );

		// User/User
		$types[] = new WP_Relationship_Type( array(
			'id'   => 'user_user',
			'name' => _x( 'User to User', 'object relationships', 'wp-relationships' )
		) );
	}

	// Get by ID
	$retval = ! empty( $id )
		? reset( wp_list_filter( $types, array( 'id' => $id ) ) )
		: $types;

	// Filter & return
	return apply_filters( 'wp_relationships_get_types', $retval, $types, $id );
}

/**
 * Get relationship statuses
 *
 * @since 0.1.0
 *
 * @param string $id ID to get a type by
 *
 * @return array
 */
function wp_relationships_get_statuses( $id = '' ) {
	static $statuses = null;

	if ( is_null( $statuses ) ) {
		$statuses = array();

		// Active
		$statuses[] = new WP_Relationship_Status( array(
			'id'   => 'active',
			'name' => _x( 'Active', 'object relationships', 'wp-relationships' )
		) );

		// Inactive
		$statuses[] = new WP_Relationship_Status( array(
			'id'   => 'inactive',
			'name' => _x( 'Inactive', 'object relationships', 'wp-relationships' )
		) );
	}

	// Get by ID
	$retval = ! empty( $id )
		? reset( wp_list_filter( $statuses, array( 'id' => $id ) ) )
		: $statuses;

	// Filter & return
	return apply_filters( 'wp_relationships_get_statuses', $retval, $statuses, $id );
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

	// Map ID to integer
	if ( empty( $retval ) && isset( $_REQUEST['relationship_id'] ) ) {
		$retval = array_map( 'absint', (array) $_REQUEST['relationship_id'] );
	}

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
