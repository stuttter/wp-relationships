<?php

/**
 * Object Relationships Functions
 *
 * @package Plugins/Site/Aliases/Functions
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Return the site ID being modified
 *
 * @since 0.1.0
 *
 * @return int
 */
function wp_object_relationships_get_site_id() {

	// Set the default
	$default_id = is_blog_admin()
		? get_current_blog_id()
		: 0;

	// Get site ID being requested
	$site_id = isset( $_REQUEST['id'] )
		? intval( $_REQUEST['id'] )
		: $default_id;

	// Look for alias ID requests
	if ( empty( $site_id ) ) {
		$relationship_id = wp_object_relationships_sanitize_relationship_ids( true );

		// Found an alias ID
		if ( ! empty( $relationship_id ) ) {
			$relationship   = WP_Object_Relationship::get_instance( $relationship_id );
			$site_id = $relationship->site_id;
		}
	}

	// No site ID
	if ( empty( $site_id ) && ! wp_object_relationships_is_network_list() ) {
		wp_die( esc_html__( 'Invalid site ID.', 'wp-object-relationships' ) );
	}

	// Get the blog details
	$details = get_blog_details( $site_id );

	// No blog details
	if ( empty( $details ) ) {
		wp_die( esc_html__( 'Invalid site ID.', 'wp-object-relationships' ) );
	}

	// Return the blog ID
	return (int) $details->blog_id;
}

/**
 * Validate alias parameters
 *
 * @since 0.1.0
 *
 * @param  array  $args  Raw input parameters
 *
 * @return array|WP_Error Validated parameters on success, WP_Error otherwise
 */
function wp_object_relationships_validate_alias_parameters( $args = array() ) {

	// Parse the args
	$r = wp_parse_args( $args, array(
		'site_id' => 0,
		'domain'  => '',
		'status'  => '',
	) );

	// Cast site ID to int
	$r['site_id'] = (int) $r['site_id'];

	// Remove all whitespace from domain
	$r['domain'] = preg_replace( '/\s+/', '', $r['domain'] );

	// Strip schemes from domain
	$r['domain'] = preg_replace( '#^https?://#', '', rtrim( $r['domain'], '/' ) );

	// Make domain lowercase
	$r['domain'] = strtolower( $r['domain'] );

	// Bail if site ID is not valid
	if ( empty( $r['site_id'] ) ) {
		return new WP_Error( 'wp_object_relationships_alias_invalid_id', esc_html__( 'Invalid site ID', 'wp-object-relationships' ) );
	}

	// Prevent debug notices
	if ( empty( $r['domain'] ) ) {
		return new WP_Error( 'wp_object_relationships_domain_empty', esc_html__( 'Aliases require a domain name', 'wp-object-relationships' ) );
	}

	// Bail if no domain name
	if ( ! strpos( $r['domain'], '.' ) ) {
		return new WP_Error( 'wp_object_relationships_domain_requires_tld', esc_html__( 'Aliases require a top-level domain', 'wp-object-relationships' ) );
	}

	// Bail if domain name using invalid characters
	if ( ! preg_match( '#^[a-z0-9\-.]+$#i', $r['domain'] ) ) {
		return new WP_Error( 'wp_object_relationships_domain_invalid_chars', esc_html__( 'Aliases can only contain alphanumeric characters, dashes (-) and periods (.)', 'wp-object-relationships' ) );
	}

	// Validate status
	if ( ! in_array( $r['status'], array( 'active', 'inactive' ), true ) ) {
		return new WP_Error( 'wp_object_relationships_domain_invalid_status', esc_html__( 'Status must be active or inactive', 'wp-object-relationships' ) );
	}

	return $r;
}

/**
 * Wrapper for admin URLs
 *
 * @since 0.1.0
 *
 * @param array $args
 * @return array
 */
function wp_object_relationships_admin_url( $args = array() ) {

	// Parse args
	$r = wp_parse_args( $args, array(
		'page' => 'object_relationships',
	) );

	// Location
	$admin_url = admin_url( 'index.php' );

	// Add query args
	$url = add_query_arg( $r, $admin_url );

	// Add args and return
	return apply_filters( 'wp_object_relationships_admin_url', $url, $admin_url, $r, $args );
}

/**
 * Clear aliases for a site when it's deleted
 *
 * @param int $site_id Site being deleted
 */
function wp_object_relationships_clear_on_delete( $site_id = 0 ) {
	$relationships = WP_Object_Relationship::get_by_site( $site_id );

	// Bail if no aliases
	if ( empty( $relationships ) ) {
		return;
	}

	// Loop through aliases & delete them one by one
	foreach ( $relationships as $relationship ) {
		$error = $relationship->delete();

		if ( is_wp_error( $error ) ) {
			$message = sprintf(
				__( 'Unable to delete alias %d for site %d', 'wp-object-relationships' ),
				$relationship->id,
				$site_id
			);
			trigger_error( $message, E_USER_WARNING );
		}
	}
}

/**
 * Get all available site alias statuses
 *
 * @since 0.1.0
 *
 * @return array
 */
function wp_object_relationships_get_statuses() {
	return apply_filters( 'wp_object_relationships_get_statuses', array(
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
function wp_object_relationships_sanitize_relationship_ids( $single = false ) {

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
	return apply_filters( 'wp_object_relationships_sanitize_alias_ids', $retval );
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
		$_alias = $relationship;
	} elseif ( is_object( $relationship ) ) {
		$_alias = new WP_Object_Relationship( $relationship );
	} else {
		$_alias = WP_Object_Relationship::get_instance( $relationship );
	}

	if ( ! $_alias ) {
		return null;
	}

	/**
	 * Fires after a site alias is retrieved.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Object_Relationship $_alias Site alias data.
	 */
	$_alias = apply_filters( 'get_object_relationship', $_alias );

	return $_alias;
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
		wp_cache_add( $relationship->id, $relationship, 'object-relationships' );
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

	wp_cache_delete( $relationship->id , 'object-relationships' );

	/**
	 * Fires immediately after a site alias has been removed from the object cache.
	 *
	 * @since 0.1.0
	 *
	 * @param int     $relationship_id Alias ID.
	 * @param WP_Site $relationship    Alias object.
	 */
	do_action( 'clean_object_relationship_cache', $relationship->id, $relationship );

	wp_cache_set( 'last_changed', microtime(), 'object-relationships' );
}
