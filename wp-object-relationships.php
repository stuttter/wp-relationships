<?php

/**
 * Plugin Name: WP Object Relationships
 * Plugin URI:  http://wordpress.org/plugins/wp-object-relationships/
 * Author:      John James Jacoby
 * Author URI:  https://profiles.wordpress.org/johnjamesjacoby/
 * License:     GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Description: Object relationships for your WordPress sites
 * Version:     0.1.0
 * Text Domain: wp-object-relationships
 * Domain Path: /assets/lang/
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

// Define the table variables
if ( empty( $GLOBALS['wpdb']->relationships ) ) {
	$GLOBALS['wpdb']->relationships    = "{$GLOBALS['wpdb']->get_blog_prefix()}relationships";
	$GLOBALS['wpdb']->relationshipmeta = "{$GLOBALS['wpdb']->get_blog_prefix()}relationshipmeta";
	$GLOBALS['wpdb']->tables[]         = 'relationships';
	$GLOBALS['wpdb']->tables[]         = 'relationshipmeta';
}

// Get the plugin path
$plugin_path = dirname( __FILE__ ) . '/';

// Classes
require_once $plugin_path . 'wp-object-relationships/includes/class-wp-relationship.php';
require_once $plugin_path . 'wp-object-relationships/includes/class-wp-relationship-query.php';
require_once $plugin_path . 'wp-object-relationships/includes/class-wp-relationships-db-table.php';

// Required Files
require_once $plugin_path . 'wp-object-relationships/includes/admin.php';
require_once $plugin_path . 'wp-object-relationships/includes/assets.php';
require_once $plugin_path . 'wp-object-relationships/includes/capabilities.php';
require_once $plugin_path . 'wp-object-relationships/includes/functions.php';
require_once $plugin_path . 'wp-object-relationships/includes/metadata.php';
require_once $plugin_path . 'wp-object-relationships/includes/hooks.php';

// Clean up the plugin path
unset( $plugin_path );

/**
 * Return the plugin's root file
 *
 * @since 0.1.0
 *
 * @return string
 */
function wp_object_relationships_get_plugin_file() {
	return __FILE__;
}

/**
 * Return the plugin's URL
 *
 * @since 0.1.0
 *
 * @return string
 */
function wp_object_relationships_get_plugin_url() {
	return plugin_dir_url( wp_object_relationships_get_plugin_file() );
}

/**
 * Return the asset version
 *
 * @since 0.1.0
 *
 * @return int
 */
function wp_object_relationships_get_asset_version() {
	return 201608260001;
}
