<?php

/**
 * Relationships Hooks
 *
 * @package Plugins/Relationships/Hooks
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

// Capabilities
add_filter( 'map_meta_cap', 'wp_relationships_map_meta_cap', 10, 4 );

// Navigation
add_action( 'admin_menu', 'wp_relationships_add_menu_item', 30 );

// Notices
add_action( 'wp_relationships_admin_notices', 'wp_relationships_output_admin_notices' );

// Add metaboxes
add_action( 'wp_relationships_add_meta_boxes', 'wp_relationships_register_metaboxes', 10, 2 );

// Always enqueue scripts
add_action( 'admin_enqueue_scripts', 'wp_relationships_admin_enqueue_scripts' );

// Temporary posts
add_action( 'add_meta_boxes', 'wp_relatationships_add_object_metaboxes' );
