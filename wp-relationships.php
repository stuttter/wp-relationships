<?php

/**
 * Plugin Name: WP Relationships
 * Plugin URI:  http://wordpress.org/plugins/wp-relationships/
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
$plugin_path = wp_relationships_get_plugin_path();

// Classes
require_once $plugin_path . 'includes/classes/class-wp-relationship.php';
require_once $plugin_path . 'includes/classes/class-wp-relationship-object.php';
require_once $plugin_path . 'includes/classes/class-wp-relationship-type.php';
require_once $plugin_path . 'includes/classes/class-wp-relationship-status.php';
require_once $plugin_path . 'includes/classes/class-wp-relationship-query.php';
require_once $plugin_path . 'includes/classes/class-wp-relationships-db-table.php';

// Required Files
require_once $plugin_path . 'includes/functions/admin.php';
require_once $plugin_path . 'includes/functions/api.php';
require_once $plugin_path . 'includes/functions/assets.php';
require_once $plugin_path . 'includes/functions/cache.php';
require_once $plugin_path . 'includes/functions/capabilities.php';
require_once $plugin_path . 'includes/functions/common.php';
require_once $plugin_path . 'includes/functions/metadata.php';
require_once $plugin_path . 'includes/functions/metaboxes.php';
require_once $plugin_path . 'includes/functions/hooks.php';

// Metaboxes
require_once $plugin_path . 'includes/metaboxes/author.php';
require_once $plugin_path . 'includes/metaboxes/content.php';
require_once $plugin_path . 'includes/metaboxes/connection.php';
require_once $plugin_path . 'includes/metaboxes/position.php';
require_once $plugin_path . 'includes/metaboxes/name.php';
require_once $plugin_path . 'includes/metaboxes/relationship.php';
require_once $plugin_path . 'includes/metaboxes/slug.php';
require_once $plugin_path . 'includes/metaboxes/status.php';

// Clean up the plugin path
unset( $plugin_path );

/**
 * Return the plugin root file
 *
 * @since 0.1.0
 *
 * @return string
 */
function wp_relationships_get_plugin_file() {
	return __FILE__;
}

/**
 * Return the plugin path
 *
 * @since 0.1.0
 *
 * @return string
 */
function wp_relationships_get_plugin_path() {
	return plugin_dir_path( wp_relationships_get_plugin_file() ) . 'wp-relationships/';
}

/**
 * Return the plugin URL
 *
 * @since 0.1.0
 *
 * @return string
 */
function wp_relationships_get_plugin_url() {
	return plugin_dir_url( wp_relationships_get_plugin_file() ) . 'wp-relationships/';
}

/**
 * Return the asset version
 *
 * @since 0.1.0
 *
 * @return int
 */
function wp_relationships_get_asset_version() {
	return 201612230001;
}
