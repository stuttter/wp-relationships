<?php

/**
 * Object Relationships Hooks
 *
 * @package Plugins/Site/Aliases/Hooks
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

// Assets
add_action( 'admin_enqueue_scripts', 'wp_object_relationships_admin_enqueue_scripts' );

// Capabilities
add_filter( 'map_meta_cap', 'wp_object_relationships_map_meta_cap', 10, 4 );

// Site delete
add_action( 'delete_blog', 'wp_object_relationships_clear_on_delete' );

// Navigation
add_action( 'admin_menu', 'wp_object_relationships_add_menu_item', 30 );

// Notices
add_action( 'wp_object_relationships_admin_notices', 'wp_object_relationships_output_admin_notices' );
