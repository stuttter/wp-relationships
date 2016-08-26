<?php

/**
 * Object Relationships List Table
 *
 * @package Plugins/Site/Aliases/ListTable
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * List table for aliases
 */
final class WP_Relationships_List_Table extends WP_List_Table {

	/**
	 * Prepare items for the list table
	 *
	 * @since 0.1.0
	 */
	public function prepare_items() {
		$this->items = array();

		if ( empty( $this->_args['site_id'] ) ) {
			return;
		}

		// Searching?
		$search = isset( $_GET['s'] ) ? $_GET['s'] : '';

		// Network list
		if ( wp_object_relationships_is_network_list() ) {

			// Get site IDs
			$sites    = wp_object_relationships_get_sites();
			$site_ids = wp_list_pluck( $sites, 'blog_id' );

			// Get aliases
			$relationships = new WP_Object_Relationship_Query( array(
				'site__in' => $site_ids,
				'search'   => $search
			) );

			// Bail if no aliases
			if ( empty( $relationships->found_site_aliases ) ) {
				return null;
			}

		// Site list
		} else {

			// Get aliases
			$relationships = new WP_Object_Relationship_Query( array(
				'site_id' => (int) $this->_args['site_id'],
				'search'  => $search
			) );

			// Bail if no aliases
			if ( empty( $relationships->found_site_aliases ) ) {
				return null;
			}
		}

		if ( ! empty( $relationships ) && ! is_wp_error( $relationships ) ) {
			$this->items = $relationships->aliases;
		}
	}

	/**
	 * Get columns for the table
	 *
	 * @since 0.1.0
	 *
	 * @return array Map of column ID => title
	 */
	public function get_columns() {

		// Universal columns
		$columns = array(
			'cb'      => '<input type="checkbox" />',
			'domain'  => _x( 'Domain',  'site aliases', 'wp-object-relationships' ),
			'status'  => _x( 'Status',  'site aliases', 'wp-object-relationships' ),
			'created' => _x( 'Created', 'site aliases', 'wp-object-relationships' )
		);

		// Add "Site" column for network admin
		if ( is_network_admin() && wp_object_relationships_is_network_list() ) {
			$columns['site'] = _x( 'Site', 'site aliases', 'wp-object-relationships' );
		}

		// Return columns
		return $columns;
	}

	/**
	 * Get an associative array ( option_name => option_title ) with the list
	 * of bulk actions available on this table.
	 *
	 * @since 0.1.0
	 * @access protected
	 *
	 * @return array
	 */
	protected function get_bulk_actions() {
		return apply_filters( 'wp_object_relationships_bulk_actions', array(
			'activate'   => esc_html__( 'Activate',   'wp-object-relationships' ),
			'deactivate' => esc_html__( 'Deactivate', 'wp-object-relationships' ),
			'delete'     => esc_html__( 'Delete',     'wp-object-relationships' )
		) );
	}

	/**
	 * Display the bulk actions dropdown.
	 *
	 * @since 0.1.0
	 * @access protected
	 *
	 * @param string $which The location of the bulk actions: 'top' or 'bottom'.
	 *                      This is designated as optional for backwards-compatibility.
	 */
	protected function bulk_actions( $which = '' ) {
		if ( is_null( $this->_actions ) ) {
			$no_new_actions = $this->_actions = $this->get_bulk_actions();
			/**
			 * Filter the list table Bulk Actions drop-down.
			 *
			 * The dynamic portion of the hook name, $this->screen->id, refers
			 * to the ID of the current screen, usually a string.
			 *
			 * This filter can currently only be used to remove bulk actions.
			 *
			 * @since 3.5.0
			 *
			 * @param array $actions An array of the available bulk actions.
			 */
			$this->_actions = apply_filters( "bulk_actions-{$this->screen->id}", $this->_actions );
			$this->_actions = array_intersect_assoc( $this->_actions, $no_new_actions );
			$two = '';
			echo '<input type="hidden" name="site_id" value="' . esc_attr( $this->_args['site_id'] ) . '" />';
			wp_nonce_field( "site_aliases-bulk-{$this->_args['site_id']}" );
		} else {
			$two = '2';
		}

		if ( empty( $this->_actions ) ) {
			return;
		}

		echo "<label for='bulk-action-selector-" . esc_attr( $which ) . "' class='screen-reader-text'>" . __( 'Select bulk action' ) . "</label>";
		echo "<select name='bulk_action$two' id='bulk-action-selector-" . esc_attr( $which ) . "'>\n";
		echo "<option value='-1' selected='selected'>" . __( 'Bulk Actions' ) . "</option>\n";

		foreach ( $this->_actions as $name => $title ) {
			$class = 'edit' == $name ? ' class="hide-if-no-js"' : '';

			echo "\t<option value='{$name}'{$class}>{$title}</option>\n";
		}

		echo "</select>\n";
		submit_button( __( 'Apply' ), 'action', false, false, array( 'id' => "doaction{$two}" ) );
		echo "\n";
	}

	/**
	 * Get the current action selected from the bulk actions dropdown.
	 *
	 * @since 0.1.0
	 *
	 * @return string|bool The action name or False if no action was selected
	 */
	public function current_action() {

		if ( isset( $_REQUEST['bulk_action'] ) && -1 != $_REQUEST['bulk_action'] ) {
			return $_REQUEST['bulk_action'];
		}

		if ( isset( $_REQUEST['bulk_action2'] ) && -1 != $_REQUEST['bulk_action2'] ) {
			return $_REQUEST['bulk_action2'];
		}

		return false;
	}

	/**
	 * Get cell value for the checkbox column
	 *
	 * @since 0.1.0
	 * @access protected
	 *
	 * @param WP_Object_Relationship $relationship Current alias item
	 * @return string HTML for the cell
	 */
	protected function column_cb( $relationship ) {
		return '<label class="screen-reader-text" for="cb-select-' . esc_attr( $relationship->id ) . '">'
			. sprintf( __( 'Select %s' ), esc_html( $relationship->domain ) ) . '</label>'
			. '<input type="checkbox" name="alias_ids[]" value="' . esc_attr( $relationship->id )
			. '" id="cb-select-' . esc_attr( $relationship->id ) . '" />';
	}

	/**
	 * Get cell value for the domain column
	 *
	 * @since 0.1.0
	 * @access protected
	 *
	 * @param WP_Object_Relationship $relationship Current alias item
	 * @return string HTML for the cell
	 */
	protected function column_domain( $relationship ) {

		// Default empty actions
		$actions = array();

		// Strip www.
		$relationship_id = $relationship->id;
		$site_id  = $relationship->site_id;
		$domain   = $relationship->domain;
		$status   = $relationship->status;

		// Edit
		$edit_link = wp_object_relationships_admin_url( array(
			'id'        => $site_id,
			'relationship_ids' => array( $relationship_id ),
			'page'      => 'alias_edit_site',
			'referrer'  => wp_object_relationships_is_network_list()
				? 'network'
				: 'site'
		) );

		// Active/Deactive
		if ( 'active' === $status ) {
			$text   = _x( 'Deactivate', 'site aliases', 'wp-object-relationships' );
			$action = 'deactivate';
		} else {
			$text   = _x( 'Activate', 'site aliases', 'wp-object-relationships' );
			$action = 'activate';
		}

		// Default args
		$args = array(
			'id'        => $site_id,
			'action'    => $action,
			'relationship_ids' => array( $relationship_id ),
			'_wpnonce'  => wp_create_nonce( "site_aliases-bulk-{$this->_args['site_id']}" )
		);

		$status_link = wp_object_relationships_admin_url( $args );

		// Delete
		$delete_args           = $args;
		$delete_args['action'] = 'delete';
		$delete_link           = wp_object_relationships_admin_url( $delete_args );

		// Edit
		if ( current_user_can( 'edit_alias', $relationship_id ) ) {
			$actions['edit'] = sprintf( '<a href="%s">%s</a>', esc_url( $edit_link ), esc_html__( 'Edit', 'wp-object-relationships' ) );
		}

		// Activate/deactivate
		if ( current_user_can( "{$action}_alias", $relationship_id ) ) {
			$actions[ $action ] = sprintf( '<a href="%s">%s</a>', esc_url( $status_link ), esc_html( $text ) );
		}

		// Delete
		if ( current_user_can( 'delete_alias', $relationship_id ) ) {
			$actions['delete'] = sprintf( '<a href="%s" class="submitdelete">%s</a>', esc_url( $delete_link ), esc_html__( 'Delete', 'wp-object-relationships' ) );
		}

		// Get HTML from actions
		$action_html = $this->row_actions( $actions, false );

		return '<strong>' . esc_html( $domain ) . '</strong>' . $action_html;
	}

	/**
	 * Get value for the status column
	 *
	 * @since 0.1.0
	 * @access protected
	 *
	 * @param WP_Object_Relationship $relationship Current alias item
	 * @return string HTML for the cell
	 */
	protected function column_status( $relationship ) {
		return ( 'active' === $relationship->status )
			? esc_html_x( 'Active',   'site aliases', 'wp-object-relationships' )
			: esc_html_x( 'Inactive', 'site aliases', 'wp-object-relationships' );
	}

	/**
	 * Get value for the status column
	 *
	 * @since 0.1.0
	 * @access protected
	 *
	 * @param WP_Object_Relationship $relationship Current alias item
	 *
	 * @return string HTML for the cell
	 */
	protected function column_created( $relationship ) {
		return mysql2date( get_option( 'date_format' ), $relationship->created ) . '<br>' .
			   mysql2date( get_option( 'time_format' ), $relationship->created );
	}

	/**
	 * Get value for the site column
	 *
	 * @since 0.1.0
	 * @access protected
	 *
	 * @param WP_Object_Relationship $relationship Current alias item
	 *
	 * @return string HTML for the cell
	 */
	protected function column_site( $relationship ) {
		echo get_site( $relationship->site_id )->home;
	}
}
