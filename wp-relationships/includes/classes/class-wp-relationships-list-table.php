<?php

/**
 * Object Relationships List Table
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
			'search' => $search
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
	 * Get the current type selected from the type dropdown.
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
	 * @param string $which
	 */
	protected function extra_tablenav( $which ) {

		?><div class="alignleft actions"><?php

		if ( 'top' === $which ) {
			ob_start();

			$this->types_dropdown();

			/**
			 * Fires before the Filter button on the Posts and Pages list tables.
			 *
			 * The Filter button allows sorting by date and/or category on the
			 * Posts list table, and sorting by date on the Pages list table.
			 *
			 * @since 0.1.0
			 *
			 * @param string $post_type The post type slug.
			 * @param string $which     The location of the extra table nav markup:
			 *                          'top' or 'bottom'.
			 */
			do_action( 'restrict_manage_posts', $this->screen->post_type, $which );

			$output = ob_get_clean();

			if ( ! empty( $output ) ) {
				echo $output;
				submit_button( esc_html__( 'Filter', 'wp-relationships' ), '', 'filter_action', false, array( 'id' => 'relationship-query-submit' ) );
			}
		} ?>

		</div>
<?php
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

		// Get types
		$types  = wp_relationships_get_types();
		$filter = $this->current_type(); ?>

		<label class="screen-reader-text" for="relationship_type"><?php esc_html_e( 'Filter by type', 'wp-relationships' ); ?></label>
		<select name="relationship_type" id="type">
			<option value=""><?php esc_html_e( 'All Types', 'wp-relationships' ); ?></option><?php

			// Loop throug sites
			foreach ( $types as $type ) :

				// Loop through sites
				?><option value="<?php echo esc_attr( $type->type_id ); ?>" <?php selected( $filter, $type->type_id ); ?>><?php echo esc_html( $type->type_name ); ?></option><?php

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
