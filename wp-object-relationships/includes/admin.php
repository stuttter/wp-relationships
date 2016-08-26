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
		$hooks[] = add_submenu_page( 'manage_relationships', esc_html__( 'Add New', 'wp-object-relationships' ), esc_html__( 'Add New', 'wp-object-relationships' ), 'edit_relationships', 'edit_relationships', 'wp_object_relationships_output_edit_page' );
	}

	// Load the list table
	foreach ( $hooks as $hook ) {
		add_action( "load-{$hook}", 'wp_object_relationships_handle_actions'       );
		add_action( "load-{$hook}", 'wp_object_relationships_load_site_list_table'      );
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
 *
 * @param  int  $site_id  Site ID
 */
function wp_object_relationships_output_page_header() {

	?><div class="wrap">
		<h1 id="edit-site"><?php esc_html_e( 'Object Relationships', 'wp-object-relationships' ); ?></h1><?php

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
	$site_id     = 0;
	$redirect_to = remove_query_arg( array( 'did_action', 'processed', 'relationship_ids', 'referrer', '_wpnonce' ), wp_get_referer() );

	// Maybe fallback redirect
	if ( empty( $redirect_to ) ) {
		$redirect_to = wp_object_relationships_admin_url();
	}

	// Get aliases being bulk actioned
	$processed = array();
	$relationship_ids = wp_object_relationships_sanitize_relationship_ids();

	// Redirect args
	$args = array(
		'id'         => $site_id,
		'did_action' => $action,
		'page'       => 'site_aliases'
	);

	// What's the action?
	switch ( $action ) {

		// Bulk activate
		case 'activate':
			foreach ( $relationship_ids as $relationship_id ) {
				$relationship = WP_Object_Relationship::get_instance( $relationship_id );

				// Skip erroneous aliases
				if ( is_wp_error( $relationship ) ) {
					$args['did_action'] = $relationship->get_error_code();
					continue;
				}

				// Process switch
				if ( $relationship->set_status( 'active' ) ) {
					$processed[] = $relationship_id;
				}
			}
			break;

		// Bulk deactivate
		case 'deactivate':
			foreach ( $relationship_ids as $relationship_id ) {
				$relationship = WP_Object_Relationship::get_instance( $relationship_id );

				// Skip erroneous aliases
				if ( is_wp_error( $relationship ) ) {
					$args['did_action'] = $relationship->get_error_code();
					continue;
				}

				// Process switch
				if ( $relationship->set_status( 'inactive' ) ) {
					$processed[] = $relationship_id;
				}
			}
			break;

		// Single/Bulk Delete
		case 'delete':
			$args['domains'] = array();

			foreach ( $relationship_ids as $relationship_id ) {
				$relationship = WP_Object_Relationship::get_instance( $relationship_id );

				// Skip erroneous aliases
				if ( is_wp_error( $relationship ) ) {
					$args['did_action'] = $relationship->get_error_code();
					continue;
				}

				// Aliases don't exist after we delete them, so pass the
				// domain for messages and such
				if ( $relationship->delete() ) {
					$args['domains'][] = $relationship->domain;
					$processed[]       = $relationship_id;
				}
			}

			break;

		// Single Add
		case 'add' :
			check_admin_referer( "site_alias_add-{$site_id}" );

			// Check that the parameters are correct first
			$params = wp_object_relationships_validate_relationship_parameters( wp_unslash( $_POST ) );

			// Error
			if ( is_wp_error( $params ) ) {
				$args['did_action'] = $params->get_error_code();
				continue;
			}

			// Add
			$relationship = WP_Object_Relationship::create(
				$params['site_id'],
				$params['domain'],
				$params['status']
			);

			// Bail if an error occurred
			if ( is_wp_error( $relationship ) ) {
				$args['did_action'] = $relationship->get_error_code();
				continue;
			}

			$processed[] = $relationship->id;

			break;

		// Single Edit
		case 'edit' :
			check_admin_referer( "site_alias_edit-{$site_id}" );

			// Check that the parameters are correct first
			$params = wp_object_relationships_validate_relationship_parameters( wp_unslash( $_POST ) );

			// Error messages
			if ( is_wp_error( $params ) ) {
				$args['did_action'] = $params->get_error_code();
				continue;
			}

			$relationship_id = $relationship_ids[0];
			$relationship    = WP_Object_Relationship::get_instance( $relationship_id );

			// Error messages
			if ( is_wp_error( $relationship ) ) {
				$args['did_action'] = $relationship->get_error_code();
				continue;
			}

			// Update
			$result = $relationship->update( $params );

			// Error messages
			if ( is_wp_error( $result ) ) {
				$args['did_action'] = $result->get_error_code();
				continue;
			}

			$processed[] = $relationship_id;

			break;

		// Any other bingos
		default:
			check_admin_referer( "site_aliases-bulk-{$site_id}" );
			do_action_ref_array( "aliases_bulk_action-{$action}", array( $relationship_ids, &$processed, $action ) );

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
	$site_id         = 0;
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
		$domain = ! empty( $_POST['domain'] )
			? wp_unslash( $_POST['domain'] )
			: '';

	// Edit
	} else {
		$active = ( 'active' === $relationship->relationship_status );
		$domain = $relationship->domain;
	}

	// Output the header, maybe with network site tabs
	wp_object_relationships_output_page_header( $site_id );

	?><form method="post" action="<?php echo esc_url( $action_url ); ?>">
		<table class="form-table">
			<tr>
				<th scope="row">
					<label for="blog_alias"><?php echo esc_html_x( 'Domain Name', 'field name', 'wp-object-relationships' ); ?></label>
				</th>
				<td>
					<input type="text" class="regular-text code" name="domain" id="blog_alias" value="<?php echo esc_attr( $domain ); ?>">
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

		<input type="hidden" name="action"    value="<?php echo esc_attr( $action   ); ?>">
		<input type="hidden" name="site_id"   value="<?php echo esc_attr( $site_id  ); ?>">
		<input type="hidden" name="alias_ids" value="<?php echo esc_attr( $relationship_id ); ?>"><?php

		// Add
		if ( 'add' === $action ) {
			wp_nonce_field( "site_alias_add-{$site_id}" );
			$submit_text = esc_html__( 'Add Alias', 'wp-object-relationships' );

		// Edit
		} else {
			wp_nonce_field( "site_alias_edit-{$site_id}" );
			$submit_text = esc_html__( 'Save Alias', 'wp-object-relationships' );
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
	$site_id = 0;
	$search  = isset( $_GET['s']    ) ? $_GET['s']                    : '';
	$page    = isset( $_GET['page'] ) ? sanitize_key( $_GET['page'] ) : 'aliases_site';

	// Action URLs
	$form_url = $action_url = wp_object_relationships_admin_url();

	// Output header, maybe with tabs
	wp_object_relationships_output_page_header( $site_id ); ?>

	<div id="col-container" style="margin-top: 20px;">
		<div id="col-right">
			<div class="col-wrap">

				<form class="search-form wp-clearfix" method="get" action="<?php echo esc_url( $form_url ); ?>">
					<input type="hidden" name="page" value="<?php echo esc_attr( $page ); ?>" />
					<input type="hidden" name="id" value="<?php echo esc_attr( $site_id ); ?>" />
					<p class="search-box">
						<label class="screen-reader-text" for="alias-search-input"><?php esc_html_e( 'Search Aliases:', 'wp-object-relationships' ); ?></label>
						<input type="search" id="alias-search-input" name="s" value="<?php echo esc_attr( $search ); ?>">
						<input type="submit" id="search-submit" class="button" value="<?php esc_html_e( 'Search Aliases', 'wp-object-relationships' ); ?>">
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
					<h2><?php esc_html_e( 'Add New Alias', 'wp-object-relationships' ); ?></h2>
					<form method="post" action="<?php echo esc_url( $action_url ); ?>">
						<div class="form-field form-required domain-wrap">
							<label for="blog_alias"><?php echo esc_html_x( 'Domain Name', 'field name', 'wp-object-relationships' ); ?></label>
							<input type="text" class="regular-text code" name="domain" id="blog_alias" value="">
							<p><?php esc_html_e( 'The fully qualified domain name that this site should load for.', 'wp-object-relationships' ); ?></p>
						</div>
						<div class="form-field form-required status-wrap">
							<label for="status"><?php echo esc_html_x( 'Status', 'field name', 'wp-object-relationships' ); ?></label>
							<select name="status" id="status"><?php

								$statuses = wp_object_relationships_get_statuses();

								// Loop throug sites
								foreach ( $statuses as $status ) :

									// Loop through sites
									?><option value="<?php echo esc_attr( $status->id ); ?>"><?php echo esc_html( $status->name ); ?></option><?php

								endforeach;

							?></select>
							<p><?php esc_html_e( 'Whether this domain is ready to accept incoming requests.', 'wp-object-relationships' ); ?></p>
						</div>

						<input type="hidden" name="action"  value="add">
						<input type="hidden" name="site_id" value="<?php echo esc_attr( $site_id ); ?>"><?php

						wp_nonce_field( "site_alias_add-{$site_id}" );

						submit_button( esc_html__( 'Add New Alias', 'wp-object-relationships' ) );

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
		'activate'   => _n( '%s alias activated.',   '%s aliases activated.',   $count, 'wp-object-relationships' ),
		'deactivate' => _n( '%s alias deactivated.', '%s aliases deactivated.', $count, 'wp-object-relationships' ),
		'delete'     => _n( '%s alias deleted.',     '%s aliases deleted.',     $count, 'wp-object-relationships' ),
		'add'        => _n( '%s alias added.',       '%s aliases added.',       $count, 'wp-object-relationships' ),
		'edit'       => _n( '%s alias updated.',     '%s aliases updated.',     $count, 'wp-object-relationships' ),

		// Failure messages
		'wp_object_relationships_domain_exists'         => _x( 'That domain is already registered.', 'object relationship', 'wp-object-relationships' ),
		'wp_object_relationships_update_failed'         => _x( 'Update failed.',                     'object relationship', 'wp-object-relationships' ),
		'wp_object_relationships_delete_failed'         => _x( 'Delete failed.',                     'object relationship', 'wp-object-relationships' ),
		'wp_object_relationships_invalid_id'            => _x( 'Invalid site ID.',                   'object relationship', 'wp-object-relationships' ),
		'wp_object_relationships_domain_empty'          => _x( 'Alias missing domain.',              'object relationship', 'wp-object-relationships' ),
		'wp_object_relationships_domain_requires_tld'   => _x( 'Alias missing a top-level domain.',  'object relationship', 'wp-object-relationships' ),
		'wp_object_relationships_domain_invalid_chars'  => _x( 'Alias contains invalid characters.', 'object relationship', 'wp-object-relationships' ),
		'wp_object_relationships_domain_invalid_status' => _x( 'Status must be active or inactive',  'object relationship', 'wp-object-relationships' )
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
