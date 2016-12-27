<?php

/**
 * Relationships List Table
 *
 * @package Plugins/Relationships/ListTable
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * List table for relationships
 */
final class WP_Relationships_List_Table extends WP_List_Table {

	/**
	 * Array of relationship types
	 *
	 * @since 0.1.0
	 *
	 * @var array
	 */
	public $types = array();

	/**
	 * Set object properties
	 *
	 * @since 0.1.0
	 *
	 * @param array $args
	 */
	public function __construct( $args = array() ) {
		parent::__construct( $args );

		// Get the type
		$this->types = wp_relationships_get_types();
	}

	/**
	 * Prepare items for the list table
	 *
	 * @since 0.1.0
	 */
	public function prepare_items() {
		$this->items = array();

		// Searching?
		$search = isset( $_GET['s'] )
			? $_GET['s']
			: '';

		// Get relationships
		$relationships = new WP_Relationship_Query( array(
			'search'  => $search,
			'type'    => $this->current_type(),
			'status'  => $this->current_status(),
			'orderby' => $this->current_orderby(),
			'order'   => $this->current_order()
		) );

		// Bail if no relationships
		if ( empty( $relationships->found_relationships ) ) {
			return null;
		}

		if ( ! empty( $relationships ) && ! is_wp_error( $relationships ) ) {
			$this->items = $relationships->relationships;
		}
	}

	/**
	 * Helper to create links to relationships page with params.
	 *
	 * @since 0.1.0
	 * @access protected
	 *
	 * @param array  $args  URL parameters for the link.
	 * @param string $label Link text.
	 * @param string $class Optional. Class attribute. Default empty string.
	 *
	 * @return string The formatted link string.
	 */
	protected function get_edit_link( $args, $label, $class = '' ) {

		// URL
		$url = wp_relationships_admin_url( $args );

		// Class
		$class_html = '';
		if ( ! empty( $class ) ) {
			 $class_html = sprintf(
				' class="%s"',
				esc_attr( $class )
			);
		}

		// Link
		return sprintf(
			'<a href="%s"%s>%s</a>',
			esc_url( $url ),
			$class_html,
			$label
		);
	}

	/**
	 * Relationship view links
	 *
	 * @since 0.1.0
	 *
	 * @return array
	 */
	protected function get_views() {

		// Vars
		$current      = $this->current_status();
		$status_links = array();
		$num_rels     = wp_count_relationships();
		$total_rels   = array_sum( (array) $num_rels );
		$class        = ( $this->is_base_request() || empty( $current ) )
			? 'current'
			: '';

		// All statuses
		$status_links['all'] = $this->get_edit_link( array(), sprintf(
			_nx(
				'All <span class="count">(%s)</span>',
				'All <span class="count">(%s)</span>',
				$total_rels,
				'rels'
			),
			number_format_i18n( $total_rels )
		), $class );

		// Loop through statuses
		foreach ( wp_relationships_get_statuses() as $status ) {
			$class       = '';
			$status_name = $status->status_id;

			// Current
			if ( ! empty( $current ) && ( $current === $status_name ) ) {
				$class = 'current';
			}

			// Args
			$status_args = array(
				'relationship_status' => $status_name
			);

			// Link
			$status_links[ $status_name ] = $this->get_edit_link( $status_args, sprintf(
				_nx(
					$status->status_name . ' <span class="count">(%s)</span>',
					$status->status_name . ' <span class="count">(%s)</span>',
					$num_rels->$status_name,
					'rels'
				),
				number_format_i18n( $num_rels->$status_name )
			), $class );
		}

		return $status_links;
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
			'cb'        => '<input type="checkbox" />',
			'name'      => _x( 'Name',     'object relationship', 'wp-relationships' ),
			'type'      => _x( 'Type',     'object relationship', 'wp-relationships' ),
			'status'    => _x( 'Status',   'object relationship', 'wp-relationships' ),
			'from'      => _x( 'From',     'object relationship', 'wp-relationships' ),
			'to'        => _x( 'To',       'object relationship', 'wp-relationships' ),
			'activity'  => _x( 'Activity', 'object relationship', 'wp-relationships' )
		);

		// Return columns
		return $columns;
	}

	/**
	 * Get sortable columns for table
	 *
	 * @since 0.1.0
	 */
	public function get_sortable_columns() {
		return array(
			'name'     => 'name',
			'type'     => 'type',
			'status'   => 'status',
			'from'     => 'from_id',
			'to'       => 'to_id',
			'activity' => 'updated'
		);
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
		return apply_filters( 'wp_relationships_bulk_actions', array(
			'activate'   => esc_html__( 'Activate',   'wp-relationships' ),
			'deactivate' => esc_html__( 'Deactivate', 'wp-relationships' ),
			'delete'     => esc_html__( 'Delete',     'wp-relationships' )
		) );
	}

	/**
	 * Get a list of CSS classes for the WP_List_Table table tag.
	 *
	 * @since 0.1.0
	 * @access protected
	 *
	 * @return array List of CSS classes for the table tag.
	 */
	protected function get_table_classes() {
		return array( 'relationships', 'widefat', 'fixed', 'striped', $this->_args['plural'] );
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
			wp_nonce_field( 'relationships-bulk' );
		} else {
			$two = '2';
		}

		if ( empty( $this->_actions ) ) {
			return;
		}

		echo "<label for='bulk-action-selector-" . esc_attr( $which ) . "' class='screen-reader-text'>" . __( 'Select bulk action' ) . "</label>";
		echo "<select name='bulk_action{$two}' id='bulk-action-selector-" . esc_attr( $which ) . "'>\n";
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
	 * Get the current type
	 *
	 * @since 0.1.0
	 *
	 * @return string|bool The type or False if no type was selected
	 */
	public function current_type() {
		return ! empty( $_REQUEST['relationship_type'] )
			? sanitize_key( $_REQUEST['relationship_type'] )
			: false;
	}

	/**
	 * Get the current status
	 *
	 * @since 0.1.0
	 *
	 * @return string|bool The type or False if no status was selected
	 */
	public function current_status() {
		return ! empty( $_REQUEST['relationship_status'] )
			? sanitize_key( $_REQUEST['relationship_status'] )
			: false;
	}

	/**
	 * Get the current orderby
	 *
	 * @since 0.1.0
	 *
	 * @return string|bool The type or False if no status was selected
	 */
	public function current_orderby() {
		return ! empty( $_REQUEST['orderby'] )
			? sanitize_key( $_REQUEST['orderby'] )
			: false;
	}

	/**
	 * Get the current order
	 *
	 * @since 0.1.0
	 *
	 * @return string|bool The type or False if no status was selected
	 */
	public function current_order() {
		return ! empty( $_REQUEST['order'] )
			? sanitize_key( $_REQUEST['order'] )
			: false;
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
	 * Output additional table filter UI
	 *
	 * @since 0.1.0
	 * @access protected
	 *
	 * @param string $which
	 */
	protected function extra_tablenav( $which ) {

		?><div class="alignleft actions"><?php

		if ( 'top' === $which ) {
			ob_start();

			$this->types_dropdown();

			$output = ob_get_clean();

			if ( ! empty( $output ) ) {
				echo $output;
				submit_button( esc_html__( 'Filter', 'wp-relationships' ), '', 'filter_action', false, array( 'id' => 'relationship-query-submit' ) );
			}
		}

		?></div><?php

		/**
		 * Fires immediately following the closing "actions" div in the tablenav for the posts
		 * list table.
		 *
		 * @since 0.1.0
		 *
		 * @param string $which The location of the extra table nav markup: 'top' or 'bottom'.
		 */
		do_action( 'manage_relationships_extra_tablenav', $which );
	}

	/**
	 * Output the types dropdown
	 *
	 * @since 0.1.0
	 * @access private
	 */
	private function types_dropdown() {

		// Bail if no types
		$types = wp_relationships_get_types();
		if ( empty( $types ) ) {
			return;
		}

		// Current type
		$filter = $this->current_type(); ?>

		<label class="screen-reader-text" for="relationship_type"><?php esc_html_e( 'Filter by type', 'wp-relationships' ); ?></label>
		<select name="relationship_type" id="type">
			<option value=""><?php esc_html_e( 'All Types', 'wp-relationships' ); ?></option><?php

			// Loop through types
			foreach ( $types as $type ) :

				// Output type
				?><option value="<?php echo esc_attr( $type->type_id ); ?>" <?php selected( $filter, $type->type_id, true ); ?>><?php echo esc_html( $type->type_name ); ?></option><?php

			endforeach;

		?></select><?php
	}

	/**
	 * Get cell value for the checkbox column
	 *
	 * @since 0.1.0
	 * @access protected
	 *
	 * @param WP_Relationship $relationship Current relationship item
	 * @return string HTML for the cell
	 */
	protected function column_cb( $relationship ) {
		return '<label class="screen-reader-text" for="cb-select-' . esc_attr( $relationship->relationship_id ) . '">'
			. esc_html__( 'Select Relationship' ) . '</label>'
			. '<input type="checkbox" name="relationship_ids[]" value="' . esc_attr( $relationship->relationship_id )
			. '" id="cb-select-' . esc_attr( $relationship->relationship_id ) . '" />';
	}

	/**
	 * Get cell value for the domain column
	 *
	 * @since 0.1.0
	 * @access protected
	 *
	 * @param WP_Relationship $relationship Current relationship item
	 * @return string HTML for the cell
	 */
	protected function column_name( $relationship ) {

		// Default empty actions
		$actions = array();

		// Strip www.
		$relationship_id = $relationship->relationship_id;
		$status          = $relationship->relationship_status;

		// Edit
		$edit_link = wp_relationships_admin_url( array(
			'relationship_ids' => array( $relationship_id ),
			'page'             => 'relationship_edit',
		) );

		// Active/Deactive
		if ( 'active' === $status ) {
			$text   = _x( 'Deactivate', 'object relationship', 'wp-relationships' );
			$action = 'deactivate';
		} else {
			$text   = _x( 'Activate', 'object relationship', 'wp-relationships' );
			$action = 'activate';
		}

		// Default args
		$args = array(
			'action'           => $action,
			'relationship_ids' => array( $relationship_id ),
			'_wpnonce'         => wp_create_nonce( 'relationships-bulk' )
		);

		$status_link = wp_relationships_admin_url( $args );

		// Delete
		$delete_args           = $args;
		$delete_args['action'] = 'delete';
		$delete_link           = wp_relationships_admin_url( $delete_args );

		// Edit
		if ( current_user_can( 'edit_relationship', $relationship_id ) ) {
			$actions['edit'] = sprintf( '<a href="%s">%s</a>', esc_url( $edit_link ), esc_html__( 'Edit', 'wp-relationships' ) );
		}

		// Activate/deactivate
		if ( current_user_can( "{$action}_relationship", $relationship_id ) ) {
			$actions[ $action ] = sprintf( '<a href="%s">%s</a>', esc_url( $status_link ), esc_html( $text ) );
		}

		// Delete
		if ( current_user_can( 'delete_relationship', $relationship_id ) ) {
			$actions['delete'] = sprintf( '<a href="%s" class="submitdelete">%s</a>', esc_url( $delete_link ), esc_html__( 'Delete', 'wp-relationships' ) );
		}

		// Get HTML from actions
		$action_html = $this->row_actions( $actions, false );

		return '<strong>' . esc_html( $relationship->relationship_name ) . '</strong>' . $action_html;
	}

	/**
	 * Get value for the status column
	 *
	 * @since 0.1.0
	 * @access protected
	 *
	 * @param WP_Relationship $relationship Current relationship item
	 * @return string HTML for the cell
	 */
	protected function column_type( $relationship ) {

		// Get types
		$type = wp_filter_object_list( $this->types, array(
			'type_id' => $relationship->relationship_type
		), 'and', 'type_name' );

		// Return the type name
		return ! empty( $type )
			? reset( $type )
			: esc_html__( 'Unknown', 'wp-relationships' );
	}

	/**
	 * Get value for the status column
	 *
	 * @since 0.1.0
	 * @access protected
	 *
	 * @param WP_Relationship $relationship Current relationship item
	 * @return string HTML for the cell
	 */
	protected function column_status( $relationship ) {
		return ( 'active' === $relationship->relationship_status )
			? esc_html_x( 'Active',   'object relationship', 'wp-relationships' )
			: esc_html_x( 'Inactive', 'object relationship', 'wp-relationships' );
	}

	/**
	 * Get value for the from column
	 *
	 * @since 0.1.0
	 * @access protected
	 *
	 * @param WP_Relationship $relationship Current relationship item
	 *
	 * @return string HTML for the cell
	 */
	protected function column_from( $relationship ) {
		echo $relationship->relationship_from_id;
	}

	/**
	 * Get value for the to column
	 *
	 * @since 0.1.0
	 * @access protected
	 *
	 * @param WP_Relationship $relationship Current relationship item
	 *
	 * @return string HTML for the cell
	 */
	protected function column_to( $relationship ) {
		echo $relationship->relationship_to_id;
	}

	/**
	 * Get value for the activity column
	 *
	 * @since 0.1.0
	 * @access protected
	 *
	 * @param WP_Relationship $relationship Current relationship item
	 *
	 * @return string HTML for the cell
	 */
	protected function column_activity( $relationship ) {
		return mysql2date( get_option( 'date_format' ), $relationship->relationship_created ) . '<br>' .
			   mysql2date( get_option( 'time_format' ), $relationship->relationship_created );
	}
}
