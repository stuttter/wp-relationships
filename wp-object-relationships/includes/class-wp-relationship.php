<?php

/**
 * Object Relationships Class
 *
 * @package Plugins/Site/Aliases/Class
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Site Alias Class
 *
 * @since 0.1.0
 */
final class WP_Object_Relationship {

	/**
	 * Alias ID.
	 *
	 * @since 0.1.0
	 * @access public
	 * @var int
	 */
	public $relationship_id;

	/**
	 * Type of relationship.
	 *
	 * @since 0.1.0
	 * @access public
	 * @var string
	 */
	public $relationship_type = '';

	/**
	 * Status of relationship.
	 *
	 * @since 0.1.0
	 * @access public
	 * @var string
	 */
	public $relationship_status = '';

	/**
	 * The date on which the relationship was created.
	 *
	 * @since 0.1.0
	 * @access public
	 * @var string Date in MySQL's datetime format.
	 */
	public $relationship_created = '0000-00-00 00:00:00';

	/**
	 * The date on which the relationship was modified.
	 *
	 * @since 0.1.0
	 * @access public
	 * @var string Date in MySQL's datetime format.
	 */
	public $relationship_modified = '0000-00-00 00:00:00';

	/**
	 * Parent relationship.
	 *
	 * @since 0.1.0
	 * @access public
	 * @var int
	 */
	public $relationship_parent = 0;

	/**
	 * Relationship order.
	 *
	 * @since 0.1.0
	 * @access public
	 * @var int
	 */
	public $relationship_order = 0;

	/**
	 * Primary ID.
	 *
	 * @since 0.1.0
	 * @access public
	 * @var int
	 */
	public $primary_id = 0;

	/**
	 * Type of primary object.
	 *
	 * @since 0.1.0
	 * @access public
	 * @var string
	 */
	public $primary_type = '';

	/**
	 * Primary ID.
	 *
	 * @since 0.1.0
	 * @access public
	 * @var int
	 */
	public $secondary_id = 0;

	/**
	 * Type of secondary object.
	 *
	 * @since 0.1.0
	 * @access public
	 * @var string
	 */
	public $secondary_type = '';

	/**
	 * Creates a new WP_Object_Relationship object.
	 *
	 * Will populate object properties from the object provided and assign other
	 * default properties based on that information.
	 *
	 * @since 0.1.0
	 * @access public
	 *
	 * @param WP_Object_Relationship|object $site A site object.
	 */
	public function __construct( $site ) {
		foreach( get_object_vars( $site ) as $key => $value ) {
			$this->{$key} = $value;
		}
	}

	/**
	 * Converts an object to array.
	 *
	 * @since 0.1.0
	 * @access public
	 *
	 * @return array Object as array.
	 */
	public function to_array() {
		return get_object_vars( $this );
	}

	/**
	 * Getter.
	 *
	 * Allows current multisite naming conventions when getting properties.
	 * Allows access to extended site properties.
	 *
	 * @since 0.1.0
	 * @access public
	 *
	 * @param string $key Property to get.
	 * @return mixed Value of the property. Null if not available.
	 */
	public function __get( $key = '' ) {
		switch ( $key ) {
			case 'id':
			case 'relationship_id':
				return (int) $this->relationship_id;
			default :
				return $this->{$key};
		}

		return null;
	}

	/**
	 * Isset-er.
	 *
	 * Allows current multisite naming conventions when checking for properties.
	 * Checks for extended site properties.
	 *
	 * @since 0.1.0
	 * @access public
	 *
	 * @param string $key Property to check if set.
	 * @return bool Whether the property is set.
	 */
	public function __isset( $key = '' ) {
		switch ( $key ) {
			case 'id' :
			case 'relationship_id' :
				return true;
			default :
				return isset( $this->{$key} );
		}

		return false;
	}

	/**
	 * Setter.
	 *
	 * Allows current multisite naming conventions while setting properties.
	 *
	 * @since 0.1.0
	 * @access public
	 *
	 * @param string $key   Property to set.
	 * @param mixed  $value Value to assign to the property.
	 */
	public function __set( $key, $value ) {
		switch ( $key ) {
			case 'id' :
			case 'relationship_id' :
				$this->relationship_id = (int) $value;
				break;
			default:
				$this->{$key} = $value;
		}
	}

	/**
	 * Set the status for the alias
	 *
	 * @since 0.1.0
	 *
	 * @param  bool $status Status should be 'active' or 'inactive'
	 *
	 * @return bool|WP_Error True if we updated, false if we didn't need to, or WP_Error if an error occurred
	 */
	public function set_status( $status = 'active' ) {
		return $this->update( array(
			'relationship_status' => $status,
		) );
	}

	/**
	 * Update the alias
	 *
	 * See also, {@see set_domain} and {@see set_status} as convenience methods.
	 *
	 * @since 0.1.0
	 *
	 * @param array|stdClass $data Alias fields (associative array or object properties)
	 *
	 * @return bool|WP_Error True if we updated, false if we didn't need to, or WP_Error if an error occurred
	 */
	public function update( $data = array() ) {
		global $wpdb;

		$data    = (array) $data;
		$fields  = array();
		$formats = array();

		// Were we given a status (and is it not the current one?)
		if ( ! empty( $data['status'] ) && ( $this->relationship_status !== $data['status'] ) ) {
			$fields['status'] = sanitize_key( $data['status'] );
			$formats[]        = '%s';
		}

		// Do we have things to update?
		if ( empty( $fields ) ) {
			return false;
		}

		$relationship_id     = $this->id;
		$where        = array( 'id' => $relationship_id );
		$where_format = array( '%d' );
		$result       = $wpdb->update( $wpdb->relationships, $fields, $where, $formats, $where_format );

		if ( empty( $result ) && ! empty( $wpdb->last_error ) ) {
			return new WP_Error( 'wp_object_relationships_update_failed' );
		}

		// Clone this object
		$old_alias = clone( $this );

		// Update internal state
		foreach ( $fields as $key => $val ) {
			$this->{$key} = $val;
		}

		// Update the alias caches
		wp_cache_set( $relationship_id, $this, 'object-relationships' );
		wp_cache_set( 'last_changed', microtime(), 'object-relationships' );

		/**
		 * Fires after a alias has been updated.
		 *
		 * @param  WP_Object_Relationship  $relationship  The alias object.
		 * @param  WP_Object_Relationship  $relationship  The previous alias object.
		 */
		do_action( 'wp_object_relationships_updated', $this, $old_alias );

		return true;
	}

	/**
	 * Delete the alias
	 *
	 * @since 0.1.0
	 *
	 * @return bool|WP_Error True if we updated, false if we didn't need to, or WP_Error if an error occurred
	 */
	public function delete() {
		global $wpdb;

		// Try to delete the alias
		$relationship_id = $this->id;
		$where           = array( 'id' => $relationship_id );
		$where_format    = array( '%d' );
		$result          = $wpdb->delete( $wpdb->relationships, $where, $where_format );

		// Bail if no alias to delete
		if ( empty( $result ) ) {
			return new WP_Error( 'wp_object_relationships_delete_failed' );
		}

		// Update the cache
		wp_cache_delete( $relationship_id, 'object-relationships' );

		// Ensure the cache is flushed
		wp_cache_set( 'last_changed', microtime(), 'object-relationships' );

		/**
		 * Fires after a alias has been delete.
		 *
		 * @param  WP_Object_Relationship  $relationship The alias object.
		 */
		do_action( 'wp_object_relationships_deleted', $this );

		return true;
	}

	/**
	 * Retrieves a site alias from the database by its ID.
	 *
	 * @static
	 * @since 2.0.0
	 * @access public
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param int $relationship_id The ID of the site to retrieve.
	 * @return WP_Object_Relationship|false The site alias's object if found. False if not.
	 */
	public static function get_instance( $relationship_id = 0 ) {
		global $wpdb;

		$relationship_id = (int) $relationship_id;
		if ( empty( $relationship_id ) ) {
			return false;
		}

		// Check cache first
		$_alias = wp_cache_get( $relationship_id, 'object-relationships' );

		// No cached alias
		if ( false === $_alias ) {
			$_alias = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->relationships} WHERE id = %d LIMIT 1", $relationship_id ) );

			// Bail if no alias found
			if ( empty( $_alias ) || is_wp_error( $_alias ) ) {
				return false;
			}

			// Add alias to cache
			wp_cache_add( $relationship_id, $_alias, 'object-relationships' );
			wp_cache_set( 'last_changed', microtime(), 'object-relationships' );
		}

		// Return alias object
		return new WP_Object_Relationship( $_alias );
	}

	/**
	 * Get alias by site ID
	 *
	 * @since 0.1.0
	 *
	 * @param int|stdClass $site Site ID, or site object from {@see get_blog_details}
	 *
	 * @return WP_Object_Relationship|WP_Error|null Alias on success, WP_Error if error occurred, or null if no alias found
	 */
	public static function get_by_site( $site = null ) {

		// Allow passing a site object in
		if ( is_object( $site ) && isset( $site->blog_id ) ) {
			$site = $site->blog_id;
		}

		if ( ! is_numeric( $site ) ) {
			return new WP_Error( 'wp_object_relationships_invalid_id' );
		}

		// Get aliases
		$relationships = new WP_Object_Relationship_Query();

		// Bail if no aliases
		if ( empty( $relationships->found_site_aliases ) ) {
			return null;
		}

		return $relationships->aliases;
	}

	/**
	 * Get alias by domain(s)
	 *
	 * @since 0.1.0
	 *
	 * @param string|array $domains Domain(s) to match against
	 * @return WP_Object_Relationship|WP_Error|null Alias on success, WP_Error if error occurred, or null if no alias found
	 */
	public static function get_by_domain( $domains = array() ) {

		// Get aliases
		$relationships = new WP_Object_Relationship_Query( array(
			'domain__in' => (array) $domains
		) );

		// Bail if no aliases
		if ( empty( $relationships->found_site_aliases ) ) {
			return null;
		}

		return reset( $relationships->aliases );
	}

	/**
	 * Create a new domain alias
	 *
	 * @param mixed  $site   Site ID, or site object from {@see get_blog_details}
	 * @param string $domain Domain
	 * @param status $status Status of alias
	 *
	 * @return WP_Object_Relationship|WP_Error
	 */
	public static function create( $site = 0, $domain = '', $status = 'active' ) {
		global $wpdb;

		// Allow passing a site object in
		if ( is_object( $site ) && isset( $site->blog_id ) ) {
			$site = $site->blog_id;
		}

		// Bail if no site
		if ( ! is_numeric( $site ) ) {
			return new WP_Error( 'wp_object_relationships_invalid_id' );
		}

		$site   = (int) $site;
		$status = sanitize_key( $status );

		// Did we get a full URL?
		if ( strpos( $domain, '://' ) !== false ) {
			$domain = parse_url( $domain, PHP_URL_HOST );
		}

		// Does this domain exist already?
		$existing = static::get_by_domain( $domain );
		if ( is_wp_error( $existing ) ) {
			return $existing;

		// Domain exists already...
		} elseif ( ! empty( $existing ) ) {
			return new WP_Error( 'wp_object_relationships_domain_exists', esc_html__( 'That alias is already in use.', 'wp-object-relationships' ) );
		}

		// Create the alias!
		$prev_errors = ! empty( $GLOBALS['EZSQL_ERROR'] ) ? $GLOBALS['EZSQL_ERROR'] : array();
		$suppress    = $wpdb->suppress_errors( true );
		$result      = $wpdb->insert(
			$wpdb->relationships,
			array(
				'blog_id' => $site,
				'domain'  => $domain,
				'created' => current_time( 'mysql' ),
				'status'  => $status
			),
			array( '%d', '%s', '%s', '%s' )
		);

		$wpdb->suppress_errors( $suppress );

		// Other error. We suppressed errors before, so we need to make sure
		// we handle that now.
		if ( empty( $result ) ) {
			$recent_errors = array_diff_key( $GLOBALS['EZSQL_ERROR'], $prev_errors );

			while ( count( $recent_errors ) > 0 ) {
				$error = array_shift( $recent_errors );
				$wpdb->print_error( $error['error_str'] );
			}

			return new WP_Error( 'wp_object_relationships_insert_failed' );
		}

		// Ensure the cache is flushed
		wp_cache_set( 'last_changed', microtime(), 'object-relationships' );

		// Get the alias, and prime the caches
		$relationship = static::get_instance( $wpdb->insert_id );

		/**
		 * Fires after a alias has been created.
		 *
		 * @param  WP_Object_Relationship  $relationship  The alias object.
		 */
		do_action( 'wp_object_relationships_created', $relationship );

		return $relationship;
	}
}
