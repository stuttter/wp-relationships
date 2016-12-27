<?php

/**
 * Relationship Metaboxes
 *
 * @package Plugins/Relationships/Metaboxes
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Add all of the Relationship metaboxes
 *
 * @since 0.2.0
 */
function wp_relationships_add_meta_boxes() {

	// Get the relationship ID being edited
	$relationship_id = ! empty( $_GET['relationship_id'] )
		? (int) $_GET['relationship_id']
		: 0;

	// Get the relationship being edited & bail if relationship does not exist
	$relationship = new WP_Relationship( $relationship_id );
	if ( empty( $relationship ) ) {
		wp_die( esc_html__( 'Invalid relationship ID.', 'wp-relationships' ) );
	}

	// Adjust the hook for user/network dashboards and pass into the action
	$hook = $GLOBALS['page_hook'];
	wp_relationships_walk_hooknames( $hook );

	// Do generic metaboxes
	do_action( 'wp_relationships_add_meta_boxes', $hook, $relationship );
}

/**
 * Walk array and add maybe add network or user suffix
 *
 * This function exists to be used byRef by array_walk() to manipulate screen
 * hooks so that user & network dashboard integration is possible without a
 * bunch of additional work.
 *
 * @since 0.1.7
 *
 * @param string $value
 */
function wp_relationships_walk_hooknames( &$value = '' ) {
	if ( is_network_admin() && substr( $value, -8 ) !== '-network' ) {
		$value .= '-network';
	} elseif ( is_user_admin() && substr( $value, -5 ) != '-user' ) {
		$value .= '-user';
	}
}

/**
 * Register relationships metaboxes
 *
 * @since 0.1.0
 */
function wp_relationships_register_metaboxes( $screen = '', $relationship = null ) {

	// Relationship
	add_meta_box(
		'password',
		_x( 'Relationship', 'relationships edit screen', 'wp-relationships' ),
		'wp_relationships_relationship_metabox',
		$screen,
		'normal',
		'high',
		$relationship
	);

	// Name
	add_meta_box(
		'name',
		_x( 'Name', 'relationships edit screen', 'wp-relationships' ),
		'wp_relationships_name_metabox',
		$screen,
		'normal',
		'high',
		$relationship
	);

	// Content
	add_meta_box(
		'content',
		_x( 'Content', 'relationships edit screen', 'wp-relationships' ),
		'wp_relationships_content_metabox',
		$screen,
		'normal',
		'high',
		$relationship
	);

	// Status
	add_meta_box(
		'submitdiv',
		_x( 'Status', 'users user-admin edit screen', 'wp-relationships' ),
		'wp_relationships_publish_metabox',
		$screen,
		'side',
		'high',
		$relationship
	);

	// Position
	add_meta_box(
		'position',
		_x( 'Position', 'users user-admin edit screen', 'wp-relationships' ),
		'wp_relationships_position_metabox',
		$screen,
		'side',
		'low',
		$relationship
	);

	// Slug
	add_meta_box(
		'slug',
		_x( 'Slug', 'users user-admin edit screen', 'wp-relationships' ),
		'wp_relationships_slug_metabox',
		$screen,
		'side',
		'low',
		$relationship
	);
}
