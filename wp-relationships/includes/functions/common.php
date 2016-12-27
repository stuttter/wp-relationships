<?php

/**
 * Object Relationships Functions
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
		'page' => 'manage_relationships',
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

/**
 * Adds any relationships from the given IDs to the cache that do not already
 * exist in cache.
 *
 * @since 2.0.0
 * @access private
 *
 * @see update_site_cache()
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @param array $ids ID list.
 */
function _prime_object_relationship_caches( $ids = array() ) {
	global $wpdb;

	$non_cached_ids = _get_non_cached_ids( $ids, 'object-relationships' );
	if ( ! empty( $non_cached_ids ) ) {
		$fresh_relationships = $wpdb->get_results( sprintf( "SELECT * FROM {$wpdb->relationships} WHERE relationship_id IN (%s)", join( ",", array_map( 'intval', $non_cached_ids ) ) ) );

		update_object_relationship_cache( $fresh_relationships );
	}
}

/**
 * Updates relationships in cache.
 *
 * @since 2.0.0
 *
 * @param array $relationships Array of relationship objects.
 */
function update_object_relationship_cache( $relationships = array() ) {

	// Bail if no relationships
	if ( empty( $relationships ) ) {
		return;
	}

	// Add each relatioship to the cache
	foreach ( $relationships as $relationship ) {
		wp_cache_add( $relationship->relationship_id, $relationship, 'object-relationships' );
	}
}

/**
 * Clean the relationship cache
 *
 * @since 0.1.0
 *
 * @param WP_Relationship $relationship The relationship details as returned from get_object_relationship()
 */
function clean_object_relationship_cache( WP_Relationship $relationship ) {

	wp_cache_delete( $relationship->relationship_id, 'object-relationships' );

	/**
	 * Fires immediately after a relationship has been removed from the object cache.
	 *
	 * @since 0.1.0
	 *
	 * @param int     $relationship_id Alias ID.
	 * @param WP_Site $relationship    Alias object.
	 */
	do_action( 'clean_object_relationship_cache', $relationship->relationship_id, $relationship );

	wp_cache_set( 'last_changed', microtime(), 'object-relationships' );
}
