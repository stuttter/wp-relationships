<?php

/**
 * Object Relationships Capabilities
 *
 * @package Plugins/Relationships/Capabilities
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Map relationship meta capabilites
 *
 * @since 0.1.0
 *
 * @param array   $caps
 * @param string  $cap
 * @param int     $user_id
 */
function wp_relationships_map_meta_cap( $caps = array(), $cap = '', $user_id = 0, $args = array() ) {

	// One of our caps?
	switch ( $cap ) {

		// Site edit (single)
		case 'edit_relationship' :
		case 'activate_relationship' :
		case 'deactivate_relationship' :
		case 'delete_relationship' :

		// Site edit (many)
		case 'manage_relationships' :
		case 'edit_relationships' :
		case 'create_relationships' :
		case 'activate_relationships' :
		case 'deactivate_relationships' :
		case 'delete_relationships' :
			$caps = array( 'manage_options' );
			break;
	}

	// Filter and return
	return apply_filters( 'wp_relationships_map_meta_cap', $caps, $cap, $user_id, $args );
}
