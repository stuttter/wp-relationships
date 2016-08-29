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
	 * Relationship author.
	 *
	 * @since 0.1.0
	 * @access public
	 * @var int
	 */
	public $relationship_author = 0;

	/**
	 * Name of relationship.
	 *
	 * @since 0.1.0
	 * @access public
	 * @var string
	 */
	public $relationship_name = '';

	/**
	 * Relationship content.
	 *
	 * @since 0.1.0
	 * @access public
	 * @var string
	 */
	public $relationship_content = '';

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
	 * From ID.
	 *
	 * @since 0.1.0
	 * @access public
	 * @var int
	 */
	public $from_id = 0;

	/**
	 * Type of object this is relationship is from.
	 *
	 * @since 0.1.0
	 * @access public
	 * @var string
	 */
	public $from_type = '';

	/**
	 * To ID.
	 *
	 * @since 0.1.0
	 * @access public
	 * @var int
	 */
	public $to_id = 0;

	/**
	 * Type of object this relationship is to.
	 *
	 * @since 0.1.0
	 * @access public
	 * @var string
	 */
	public $to_type = '';

	/**
	 * Creates a new WP_Object_Relationship object.
	 *
	 * Will populate object properties from the object provided and assign other
	 * default properties based on that information.
	 *
	 * @since 0.1.0
	 * @access public
	 *
	 * @param WP_Object_Relationship|object $relationship A relationship object.
	 */
	public function __construct( $relationship ) {
		foreach( get_object_vars( $relationship ) as $key => $value ) {
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
			case 'relationship_name':
				return ! empty( $this->relationship_name )
					? $this->relationship_name
					: 'No name';
			default :
				return $this->{$key};
		}

		return null;
	}

	/**
	 * Isset-er.
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
	 * @param array|stdClass $args Relationship fields (associative array or object properties)
	 *
	 * @return bool|WP_Error True if we updated, false if we didn't need to, or WP_Error if an error occurred
	 */
	public function update( $args = array() ) {
		global $wpdb;

		$fields = self::sanitize( $args );
		if ( empty( $fields ) || is_wp_error( $fields ) ) {
			return new WP_Error( 'update_failed' );
		}

		$relationship_id = $this->relationship_id;
		$where        = array( 'relationship_id' => $relationship_id );
		$where_format = array( '%d' );
		$result       = $wpdb->update( $wpdb->relationships, $fields, $where, self::format(), $where_format );

		if ( empty( $result ) && ! empty( $wpdb->last_error ) ) {
			return new WP_Error( 'update_failed' );
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
		$relationship_id = $this->relationship_id;
		$where           = array( 'relationship_id' => $relationship_id );
		$where_format    = array( '%d' );
		$result          = $wpdb->delete( $wpdb->relationships, $where, $where_format );

		// Bail if no alias to delete
		if ( empty( $result ) ) {
			return new WP_Error( 'delete_failed' );
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
		$_relationship = wp_cache_get( $relationship_id, 'object-relationships' );

		// No cached alias
		if ( false === $_relationship ) {
			$_relationship = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->relationships} WHERE id = %d LIMIT 1", $relationship_id ) );

			// Bail if no alias found
			if ( empty( $_relationship ) || is_wp_error( $_relationship ) ) {
				return false;
			}

			// Add alias to cache
			wp_cache_add( $relationship_id, $_relationship, 'object-relationships' );
			wp_cache_set( 'last_changed', microtime(), 'object-relationships' );
		}

		// Return alias object
		return new WP_Object_Relationship( $_relationship );
	}

	/**
	 * Get alias by relationship ID
	 *
	 * @since 0.1.0
	 *
	 * @param int|stdClass $relationship Relationship
	 *
	 * @return WP_Object_Relationship|WP_Error|null Alias on success, WP_Error if error occurred, or null if no alias found
	 */
	public static function get_by_id( $relationship = null ) {

		// Allow passing a site object in
		if ( is_object( $relationship ) && isset( $relationship->relationship_id ) ) {
			$relationship = $relationship->relationship_id;
		}

		if ( ! is_numeric( $relationship ) ) {
			return new WP_Error( 'invalid_id' );
		}

		// Get aliases
		$relationships = new WP_Object_Relationship_Query();

		// Bail if no aliases
		if ( empty( $relationships->found_relationships ) ) {
			return null;
		}

		return $relationships->relationships;
	}

	/**
	 * Create a new relationship
	 *
	 * @param array  $args
	 *
	 * @return WP_Object_Relationship|WP_Error
	 */
	public static function create( $args = array() ) {
		global $wpdb;

		// Parse the args
		$r = self::sanitize( $args );
		if ( empty( $r ) || is_wp_error( $r ) ) {
			return new WP_Error( 'insert_failed' );
		}

		// Create the alias!
		$prev_errors = ! empty( $GLOBALS['EZSQL_ERROR'] ) ? $GLOBALS['EZSQL_ERROR'] : array();
		$suppress    = $wpdb->suppress_errors( true );
		$result      = $wpdb->insert( $wpdb->relationships, $r, self::format() );

		$wpdb->suppress_errors( $suppress );

		// Other error. We suppressed errors before, so we need to make sure
		// we handle that now.
		if ( empty( $result ) ) {
			$recent_errors = array_diff_key( $GLOBALS['EZSQL_ERROR'], $prev_errors );

			while ( count( $recent_errors ) > 0 ) {
				$error = array_shift( $recent_errors );
				$wpdb->print_error( $error['error_str'] );
			}

			return new WP_Error( 'insert_failed' );
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

	/**
	 * Sanitize values for saving
	 *
	 * @since 0.1.0
	 *
	 * @param array $args
	 *
	 * @return \WP_Error
	 */
	public static function sanitize( $args = array() ) {

		// We're at now, now
		$now = time();

		// Parse the arguments
		$r = wp_parse_args( $args, array(
			'relationship_id'       => 0,
			'relationship_author'   => get_current_user_id(),
			'relationship_name'     => '',
			'relationship_content'  => '',
			'relationship_type'     => '',
			'relationship_status'   => 'active',
			'relationship_created'  => $now,
			'relationship_modified' => $now,
			'relationship_parent'   => 0,
			'relationship_order'    => 0,
			'from_id'               => 0,
			'from_type'             => '',
			'to_id'                 => 0,
			'to_type'               => ''
		) );

		// Sanitize
		$r['relationship_id']       = (int) $r['relationship_id'];
		$r['relationship_author']   = (int) $r['relationship_author'];
		$r['relationship_name']     = wp_kses_data( $r['relationship_name'] );
		$r['relationship_content']  = wp_kses_data( $r['relationship_content'] );
		$r['relationship_type']     = sanitize_key( $r['relationship_type'] );
		$r['relationship_status']   = sanitize_key( $r['relationship_status'] );
		$r['relationship_created']  = gmdate( 'Y-m-d H:i:s', $r['relationship_created'] );
		$r['relationship_modified'] = gmdate( 'Y-m-d H:i:s', $r['relationship_modified'] );
		$r['relationship_parent']   = (int) $r['relationship_parent'];
		$r['relationship_order']    = (int) $r['relationship_order'];

		// From
		$r['from_id']   = (int) $r['from_id'];
		$r['from_type'] = sanitize_key( $r['from_type'] );

		// To
		$r['to_id']   = (int) $r['to_id'];
		$r['to_type'] = sanitize_key( $r['to_type'] );

		// Validate status
		if ( ! in_array( $r['relationship_status'], array( 'active', 'inactive' ), true ) ) {
			return new WP_Error( 'create_status' );
		}

		// Remove keys that definitely don't belong, but might be part of submissions
		unset( $r['action'], $r['_wpnonce'], $r['_wp_http_referer'], $r['submit'] );

		return $r;
	}

	/**
	 * Return an array of keys used to define database columns
	 *
	 * @since 0.1.0
	 *
	 * @return array
	 */
	public static function format() {
		return array(
			'%d', // ID
			'%d', // Author
			'%s', // Name
			'%s', // Description
			'%s', // Type
			'%s', // Status
			'%d', // Parent
			'%d', // Order
			'%s', // Created
			'%s', // Modified
			'%d', // From ID
			'%s', // From Type
			'%d', // To ID
			'%s'  // To Type
		);
	}
}
