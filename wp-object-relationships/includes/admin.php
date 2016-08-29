<?php

/**
 * Object Relationships Admin
 *
 * @package Plugins/Site/Aliases/Admin
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Add menus in network and site dashboards
 *
 * @since 0.1.0
 */
function wp_object_relationships_add_menu_item() {

	// Define empty array
	$hooks = array();

	if ( is_blog_admin() ) {
		$hooks[] = add_menu_page( esc_html__( 'Relationships', 'wp-object-relationships' ), esc_html__( 'Relationships', 'wp-object-relationships' ), 'manage_relationships', 'manage_relationships', 'wp_object_relationships_output_list_page', 'dashicons-networking', 5 );
		$hooks[] = add_submenu_page( 'manage_relationships', esc_html__( 'Add New', 'wp-object-relationships' ), esc_html__( 'Add New', 'wp-object-relationships' ), 'edit_relationships', 'relationship_edit', 'wp_object_relationships_output_edit_page' );
	}

	// Load the list table
	foreach ( $hooks as $hook ) {
		add_action( "load-{$hook}", 'wp_object_relationships_handle_actions'       );
		add_action( "load-{$hook}", 'wp_object_relationships_load_site_list_table' );
	}
}

/**
 * Get any admin actions
 *
 * @since 0.1.0
 *
 * @return string
 */
function wp_object_relationships_get_admin_action() {

	$action = false;

	// Regular action
	if ( ! empty( $_REQUEST['action'] ) ) {
		$action = sanitize_key( $_REQUEST['action'] );

	// Bulk action (top)
	} elseif ( ! empty( $_REQUEST['bulk_action'] ) ) {
		$action = sanitize_key( $_REQUEST['bulk_action'] );

	// Bulk action (bottom)
	} elseif ( ! empty( $_REQUEST['bulk_action2'] ) ) {
		$action = sanitize_key( $_REQUEST['bulk_action2'] );
	}

	return $action;
}

/**
 * Load the list table and populate some essentials
 *
 * @since 0.1.0
 */
function wp_object_relationships_load_site_list_table() {
	global $wp_list_table;

	// Include the list table class
	require_once dirname( __FILE__ ) . '/class-wp-relationships-list-table.php';

	// Create a new list table object
	$wp_list_table = new WP_Relationships_List_Table();

	$wp_list_table->prepare_items();
}

/**
 * Output the admin page header
 *
 * @since 0.1.0
 */
function wp_object_relationships_output_page_header() {

	?><div class="wrap">
		<h1 id="edit-relationship"><?php esc_html_e( 'Object Relationships', 'wp-object-relationships' ); ?></h1><?php

	// Admin notices
	do_action( 'wp_object_relationships_admin_notices' );
}

/**
 * Close the .wrap div
 *
 * @since 0.1.0
 */
function wp_object_relationships_output_page_footer() {
	?></div><?php
}

/**
 * Handle submission of the list page
 *
 * Handles bulk actions for the list page. Redirects back to itself after
 * processing, and exits.
 *
 * @since 0.1.0
 *
 * @param  string  $action  Action to perform
 */
function wp_object_relationships_handle_actions() {

	// Look for actions
	$action = wp_object_relationships_get_admin_action();

	// Bail if no action
	if ( false === $action ) {
		return;
	}

	// Get action
	$action      = sanitize_key( $action );
	$redirect_to = remove_query_arg( array( 'did_action', 'processed', 'relationship_ids', 'referrer', '_wpnonce' ), wp_get_referer() );

	// Maybe fallback redirect
	if ( empty( $redirect_to ) ) {
		$redirect_to = wp_object_relationships_admin_url();
	}

	// Get aliases being bulk actioned
	$processed        = array();
	$relationship_ids = wp_object_relationships_sanitize_relationship_ids();

	// Redirect args
	$args = array(
		'relationship_ids' => $relationship_ids,
		'did_action'       => $action,
		'page'             => 'manage_relationships'
	);

	// What's the action?
	switch ( $action ) {

		// Bulk activate
		case 'activate':
			check_admin_referer( 'relationships-bulk' );

			foreach ( $relationship_ids as $relationship_id ) {
				$relationship = WP_Object_Relationship::get_instance( $relationship_id );

				// Skip erroneous aliases
				if ( is_wp_error( $relationship ) ) {
					$args['did_action'] = $relationship->get_error_code();
					continue;
				}

				// Process switch
				$result = $relationship->set_status( 'active' );
				if ( is_wp_error( $result ) ) {
					$args['did_action'] = $result->get_error_code();
					continue;
				}

				// Success
				$processed[] = $relationship_id;
			}
			break;

		// Bulk deactivate
		case 'deactivate':
			check_admin_referer( 'relationships-bulk' );

			foreach ( $relationship_ids as $relationship_id ) {
				$relationship = WP_Object_Relationship::get_instance( $relationship_id );

				// Skip erroneous aliases
				if ( is_wp_error( $relationship ) ) {
					$args['did_action'] = $relationship->get_error_code();
					continue;
				}

				// Process switch
				$result = $relationship->set_status( 'inactive' );
				if ( is_wp_error( $result ) ) {
					$args['did_action'] = $result->get_error_code();
					continue;
				}

				// Success
				$processed[] = $relationship_id;
			}
			break;

		// Single/Bulk Delete
		case 'delete':
			check_admin_referer( 'relationships-bulk' );

			$args['domains'] = array();

			foreach ( $relationship_ids as $relationship_id ) {
				$relationship = WP_Object_Relationship::get_instance( $relationship_id );

				// Skip erroneous aliases
				if ( is_wp_error( $relationship ) ) {
					$args['did_action'] = $relationship->get_error_code();
					continue;
				}

				// Try to delete
				$result = $relationship->delete();
				if ( is_wp_error( $result ) ) {
					$args['did_action'] = $result->get_error_code();
					continue;
				}

				// Success
				$processed[] = $relationship_id;
			}

			break;

		// Single Add
		case 'add' :
			check_admin_referer( 'relationship_add' );

			// Add
			$relationship = WP_Object_Relationship::create( wp_unslash( $_POST ) );

			// Bail if an error occurred
			if ( is_wp_error( $relationship ) ) {
				$args['did_action'] = $relationship->get_error_code();
				continue;
			}

			$processed[] = $relationship->id;

			break;

		// Single Edit
		case 'edit' :
			check_admin_referer( 'relationship_edit' );

			// Check that the parameters are correct first
			$relationship_id = $relationship_ids[0];
			$relationship    = WP_Object_Relationship::get_instance( $relationship_id );

			// Error messages
			if ( is_wp_error( $relationship ) ) {
				$args['did_action'] = $relationship->get_error_code();
				continue;
			}

			// Update
			$result = $relationship->update( wp_unslash( $_POST ) );

			// Error messages
			if ( is_wp_error( $result ) ) {
				$args['did_action'] = $result->get_error_code();
				continue;
			}

			$processed[] = $relationship_id;

			break;

		// Any other bingos
		default:
			check_admin_referer( 'relationships-bulk' );
			do_action_ref_array( "relationships_bulk_action-{$action}", array( $relationship_ids, &$processed, $action ) );

			break;
	}

	// Add processed aliases to redirection
	$args['processed'] = $processed;
	$redirect_to = add_query_arg( $args, $redirect_to );

	// Redirect
	wp_safe_redirect( $redirect_to );
	exit();
}

/**
 * Output alias editing page
 *
 * @since 0.1.0
 */
function wp_object_relationships_output_edit_page() {

	// Vars
	$relationship_id = wp_object_relationships_sanitize_relationship_ids( true );
	$relationship    = WP_Object_Relationship::get_instance( $relationship_id );
	$action          = ! empty( $relationship ) ? 'edit' : 'add';

	// URL
	$action_url = wp_object_relationships_admin_url( array(
		'action' => $action
	) );

	// Add
	if ( empty( $relationship ) || ! empty( $_POST['_wpnonce'] ) ) {
		$active = ! empty( $_POST['active'] );

	// Edit
	} else {
		$active = ( 'active' === $relationship->relationship_status );
	}

	// Output the header, maybe with network site tabs
	wp_object_relationships_output_page_header();

	?><form method="post" action="<?php echo esc_url( $action_url ); ?>">
		<table class="form-table">
			<tr>
				<th scope="row">
					<label for="relationship_type"><?php echo esc_html_x( 'Relationship Type', 'field name', 'wp-object-relationships' ); ?></label>
				</th>
				<td>
					<input type="text" class="regular-text code" name="domain" id="relationship_type" value="<?php echo esc_attr( '' ); ?>">
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php echo esc_html_x( 'Status', 'field name', 'wp-object-relationships' ); ?>
				</th>
				<td>
					<select name="status" id="status"><?php

						$statuses = wp_object_relationships_get_statuses();

						// Loop throug sites
						foreach ( $statuses as $status ) :

							// Loop through sites
							?><option value="<?php echo esc_attr( $status->id ); ?>" <?php selected( $status->id, $relationship->relationship_status ); ?>><?php echo esc_html( $status->name ); ?></option><?php

						endforeach;

					?></select>
				</td>
			</tr>
		</table>

		<input type="hidden" name="action"           value="<?php echo esc_attr( $action          ); ?>">
		<input type="hidden" name="relationship_ids" value="<?php echo esc_attr( $relationship_id ); ?>"><?php

		// Add
		if ( 'add' === $action ) {
			wp_nonce_field( 'relationship_add' );
			$submit_text = esc_html__( 'Add Relationship', 'wp-object-relationships' );

		// Edit
		} else {
			wp_nonce_field( "relationship_edit-{$relationship_id}" );
			$submit_text = esc_html__( 'Save Relationship', 'wp-object-relationships' );
		}

		// Submit button
		submit_button( $submit_text );

	?></form><?php

	// Footer
	wp_object_relationships_output_page_footer();
}

/**
 * Output alias editing page
 *
 * @since 0.1.0
 */
function wp_object_relationships_output_list_page() {
	global $wp_list_table;

	// Get site ID being requested
	$search  = isset( $_GET['s']    ) ? $_GET['s']                    : '';
	$page    = isset( $_GET['page'] ) ? sanitize_key( $_GET['page'] ) : 'manage_relationships';

	// Action URLs
	$form_url = $action_url = wp_object_relationships_admin_url();

	// Output header, maybe with tabs
	wp_object_relationships_output_page_header(); ?>

	<div id="col-container" style="margin-top: 20px;">
		<div id="col-right">
			<div class="col-wrap">
				<form class="search-form wp-clearfix" method="get" action="<?php echo esc_url( $form_url ); ?>">
					<input type="hidden" name="page" value="<?php echo esc_attr( $page ); ?>" />
					<input type="hidden" name="relationship_id" value="<?php echo esc_attr( '' ); ?>" />
					<p class="search-box">
						<label class="screen-reader-text" for="relationship-search-input"><?php esc_html_e( 'Search Relationships:', 'wp-object-relationships' ); ?></label>
						<input type="search" id="relationship-search-input" name="s" value="<?php echo esc_attr( $search ); ?>">
						<input type="submit" id="search-submit" class="button" value="<?php esc_html_e( 'Search Relationships', 'wp-object-relationships' ); ?>">
					</p>
				</form>

				<div class="form-wrap">
					<form method="post" action="<?php echo esc_url( $form_url ); ?>">
						<?php $wp_list_table->display(); ?>
					</form>
				</div>
			</div>
		</div>
		<div id="col-left">
			<div class="col-wrap">
				<div class="form-wrap">
					<h2><?php esc_html_e( 'Add New Relationship', 'wp-object-relationships' ); ?></h2>
					<form method="post" action="<?php echo esc_url( $action_url ); ?>">
						<div class="form-field form-required name-wrap">
							<label for="relationship_name"><?php echo esc_html_x( 'Relationship Name', 'field name', 'wp-object-relationships' ); ?></label>
							<input type="text" class="regular-text" name="relationship_name" id="relationship_name" value="">
							<p><?php esc_html_e( 'Name of relationship, to make it easy to identify.', 'wp-object-relationships' ); ?></p>
						</div>

						<div class="form-field form-required content-wrap">
							<label for="relationship_content"><?php echo esc_html_x( 'Relationship Description', 'field name', 'wp-object-relationships' ); ?></label>
							<textarea class="regular-text" name="relationship_content" id="relationship_content"></textarea>
							<p><?php esc_html_e( 'Describe this relationship, Totally optional...', 'wp-object-relationships' ); ?></p>
						</div>

						<div class="form-field form-required type-wrap">
							<label for="relationship_type"><?php echo esc_html_x( 'Type', 'field name', 'wp-object-relationships' ); ?></label>
							<select name="relationship_type" id="relationship_type"><?php

								$types = wp_object_relationships_get_types();

								// Loop throug sites
								foreach ( $types as $type ) :

									// Loop through sites
									?><option value="<?php echo esc_attr( $type->id ); ?>"><?php echo esc_html( $type->name ); ?></option><?php

								endforeach;

							?></select>
							<p><?php esc_html_e( 'What types of objects are related to each other.', 'wp-object-relationships' ); ?></p>
						</div>
						<div class="form-field form-required status-wrap">
							<label for="relationship_status"><?php echo esc_html_x( 'Status', 'field name', 'wp-object-relationships' ); ?></label>
							<select name="relationship_status" id="relationship_status"><?php

								$statuses = wp_object_relationships_get_statuses();

								// Loop throug sites
								foreach ( $statuses as $status ) :

									// Loop through sites
									?><option value="<?php echo esc_attr( $status->id ); ?>"><?php echo esc_html( $status->name ); ?></option><?php

								endforeach;

							?></select>
							<p><?php esc_html_e( 'Whether this relationship is currently active.', 'wp-object-relationships' ); ?></p>
						</div>

						<div class="form-field form-required from-wrap">
							<label for="from_id"><?php echo esc_html_x( 'From', 'field name', 'wp-object-relationships' ); ?></label>
							<input type="text" class="regular-text code" name="from_id" id="from_id" value="">
							<input type="hidden" name="from_type" id="from_type" value="">
							<p><?php esc_html_e( 'ID of object to relate to.', 'wp-object-relationships' ); ?></p>
						</div>

						<div class="form-field form-required to-wrap">
							<label for="to_id"><?php echo esc_html_x( 'To', 'field name', 'wp-object-relationships' ); ?></label>
							<input type="text" class="regular-text code" name="to_id" id="to_id" value="">
							<input type="hidden" name="to_type" id="to_type" value="">
							<p><?php esc_html_e( 'ID of object relating to.', 'wp-object-relationships' ); ?></p>
						</div>

						<div class="form-field form-required parent-wrap">
							<label for="relationship_parent"><?php echo esc_html_x( 'Relationship Parent', 'field name', 'wp-object-relationships' ); ?></label>
							<input type="text" class="regular-text code" name="relationship_parent" id="relationship_parent" value="0">
							<p><?php esc_html_e( 'Relationships can have a hierarchy. You might have a Post relationship, and under that have child relationships for other objects. Totally optional..', 'wp-object-relationships' ); ?></p>
						</div>

						<div class="form-field form-required order-wrap">
							<label for="relationship_order"><?php echo esc_html_x( 'Relationship Order', 'field name', 'wp-object-relationships' ); ?></label>
							<input type="number" class="regular-text code" name="relationship_order" id="relationship_order" value="0">
							<p><?php esc_html_e( 'Relationships can have an order. You might want one relationship to appear before or after another. Totally optional..', 'wp-object-relationships' ); ?></p>
						</div>

						<input type="hidden" name="relationship_author" value="<?php echo get_current_user_id(); ?>">
						<input type="hidden" name="action" value="add"><?php

						wp_nonce_field( 'relationship_add' );

						submit_button( esc_html__( 'Add New Relationship', 'wp-object-relationships' ) );

					?></form>
				</div>
			</div>
		</div>
	</div><?php

	// Footer
	wp_object_relationships_output_page_footer();
}

/**
 * Output admin notices
 *
 * @since 0.1.0
 *
 * @global type $wp_list_table
 */
function wp_object_relationships_output_admin_notices() {

	// Add messages for bulk actions
	if ( empty( $_REQUEST['did_action'] ) ) {
		return;
	}

	// Vars
	$did_action = sanitize_key( $_REQUEST['did_action'] );
	$processed  = ! empty( $_REQUEST['processed'] ) ? wp_parse_id_list( (array) $_REQUEST['processed'] ) : array();
	$processed  = array_map( 'absint', $processed );
	$count      = count( $processed );
	$output     = array();
	$messages   = array(

		// Success messages
		'activate'   => _n( '%s relationship activated.',   '%s relationships activated.',   $count, 'wp-object-relationships' ),
		'deactivate' => _n( '%s relationship deactivated.', '%s relationships deactivated.', $count, 'wp-object-relationships' ),
		'delete'     => _n( '%s relationship deleted.',     '%s relationships deleted.',     $count, 'wp-object-relationships' ),
		'add'        => _n( '%s relationship added.',       '%s relationships added.',       $count, 'wp-object-relationships' ),
		'edit'       => _n( '%s relationship updated.',     '%s relationships updated.',     $count, 'wp-object-relationships' ),

		// Failure messages
		'create_failed' => _x( 'Create failed.', 'object relationship', 'wp-object-relationships' ),
		'update_failed' => _x( 'Update failed.', 'object relationship', 'wp-object-relationships' ),
		'delete_failed' => _x( 'Delete failed.', 'object relationship', 'wp-object-relationships' ),
	);

	// Insert the placeholder
	if ( ! empty( $messages[ $did_action ] ) ) {
		$output[] = sprintf( $messages[ $did_action ], number_format_i18n( $count ) );
	}

	// Bail if no messages
	if ( empty( $output ) ) {
		return;
	}

	// Get success keys
	$success = array_keys( array_slice( $messages, 0, 5 ) );

	// Which class
	$notice_class = in_array( $did_action, $success )
		? 'notice-success'
		: 'notice-warning';

	// Start a buffer
	ob_start();

	?><div id="message" class="notice <?php echo esc_attr( $notice_class ); ?> is-dismissible">
		<p><?php echo implode( '</p><p>', $output ); ?></p>
	</div><?php

	// Output the buffer
	ob_end_flush();
}
