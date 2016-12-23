<?php

/**
 * Object Relationships Functions
 *
 * @package Plugins/Site/Aliases/Functions
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
 * Get all available site alias statuses
 *
 * @since 0.1.0
 *
 * @return array
 */
function wp_relationships_get_types() {
	return apply_filters( 'wp_relationships_get_types', array(
		(object) array(
			'id'   => 'post_taxonomy_term',
			'name' => _x( 'Taxonomy Terms to Posts', 'object relationships', 'wp-object-relationships' )
		),
		(object) array(
			'id'   => 'post_post',
			'name' => _x( 'Posts to Posts', 'object relationships', 'wp-object-relationships' )
		)
	) );
}

/**
 * Get all available site alias statuses
 *
 * @since 0.1.0
 *
 * @return array
 */
function wp_relationships_get_statuses() {
	return apply_filters( 'wp_relationships_get_statuses', array(
		(object) array(
			'id'   => 'active',
			'name' => _x( 'Active', 'object relationships', 'wp-object-relationships' )
		),
		(object) array(
			'id'   => 'inactive',
			'name' => _x( 'Inactive', 'object relationships', 'wp-object-relationships' )
		),
	) );
}

/**
 * Sanitize requested alias ID values
 *
 * @since 0.1.0
 *
 * @param bool $single
 * @return mixed
 */
function wp_relationships_sanitize_relationship_ids( $single = false ) {

	// Default value
	$retval = array();

	// Map IDs to integers
	if ( isset( $_REQUEST['relationship_ids'] ) ) {
		$retval = array_map( 'absint', (array) $_REQUEST['relationship_ids'] );
	}

	// Return the first item
	if ( true === $single ) {
		$retval = reset( $retval );
	}

	// Filter & return
	return apply_filters( 'wp_relationships_sanitize_relationship_ids', $retval );
}

/**
 * Retrieves site alias data given a site alias ID or site alias object.
 *
 * Site alias data will be cached and returned after being passed through a filter.
 *
 * @since 2.0.0
 *
 * @param WP_Object_Relationship|int|null $relationship Optional. Site alias to retrieve.
 * @return WP_Object_Relationship|null The site object or null if not found.
 */
function get_object_relationship( $relationship = null ) {
	if ( empty( $relationship ) ) {
		return null;
	}

	if ( $relationship instanceof WP_Object_Relationship ) {
		$_relationship = $relationship;
	} elseif ( is_object( $relationship ) ) {
		$_relationship = new WP_Object_Relationship( $relationship );
	} else {
		$_relationship = WP_Object_Relationship::get_instance( $relationship );
	}

	if ( ! $_relationship ) {
		return null;
	}

	/**
	 * Fires after a site alias is retrieved.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Object_Relationship $_relationship Site alias data.
	 */
	$_relationship = apply_filters( 'get_object_relationship', $_relationship );

	return $_relationship;
}

/**
 * Adds any site aliases from the given ids to the cache that do not already
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
		$fresh_aliases = $wpdb->get_results( sprintf( "SELECT * FROM {$wpdb->relationships} WHERE relationship_id IN (%s)", join( ",", array_map( 'intval', $non_cached_ids ) ) ) );

		update_object_relationship_cache( $fresh_aliases );
	}
}

/**
 * Updates site aliases in cache.
 *
 * @since 2.0.0
 *
 * @param array $relationships Array of site alias objects.
 */
function update_object_relationship_cache( $relationships = array() ) {
	if ( empty( $relationships ) ) {
		return;
	}

	foreach ( $relationships as $relationship ) {
		wp_cache_add( $relationship->relationship_id, $relationship, 'object-relationships' );
	}
}

/**
 * Clean the site alias cache
 *
 * @since 0.1.0
 *
 * @param WP_Object_Relationship $relationship The alias details as returned from get_object_relationship()
 */
function clean_object_relationship_cache( WP_Object_Relationship $relationship ) {

	wp_cache_delete( $relationship->relationship_id, 'object-relationships' );

	/**
	 * Fires immediately after a site alias has been removed from the object cache.
	 *
	 * @since 0.1.0
	 *
	 * @param int     $relationship_id Alias ID.
	 * @param WP_Site $relationship    Alias object.
	 */
	do_action( 'clean_object_relationship_cache', $relationship->relationship_id, $relationship );

	wp_cache_set( 'last_changed', microtime(), 'object-relationships' );
}
