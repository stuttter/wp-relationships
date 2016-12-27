<?php

/**
 * Relationships Cache
 *
 * @package Plugins/Relationships/Cache
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

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

	// Add each relationship to the cache
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
