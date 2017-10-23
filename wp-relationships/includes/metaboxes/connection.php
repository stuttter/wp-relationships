<?php

/**
 * Relationship Connection Metabox
 *
 * @package Plugins/Relationship/Metaboxes/Connection
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Output the metabox used to display item metadata
 *
 * @param object $object
 */
function wp_relationships_connection_metabox( $object = false ) {

	// Bail if no rental
	if ( empty( $object ) ) {
		return;
	}

	// Get object
	$relationship_object = wp_relationships_get_objects( array(
		'object' => get_class( $object )
	) );

	// Bail if no objects for object
	if ( empty( $relationship_object ) ) {
		return;
	}

	// Types (post only for now)
	$type      = 'post';
	$type_type = 'post_post';

	// Relationship query
	$connections = new WP_Relationship_Query( array(
		'type'    => $type_type,
		'from_id' => $object->ID
	) );

	// Relationship types
	$types = wp_get_relationships_of_type( $type );

	// Setup relationships
	$objects = ! empty( $connections->relationships )
		? $connections->relationships
		: array( WP_Relationship::get_instance() );

	// Start an output buffer
	ob_start(); ?>

	<ul class="new-relationship">
		<li class="no-relationships">
			<div class="row-wrapper">
				<a href="" class="add-relationship"><?php esc_html_e( 'Add Relationship', 'wp-relationship' ); ?></a>
			</div>
		</li>
		<?php
		
		// Output the template row
		wp_relationships_connection_metabox_row( $object, null, $types );

		// Output any actual relationship rows
		foreach ( $objects as $relationship ) {
			wp_relationships_connection_metabox_row( $object, $relationship, $types );
		}

		?>
	</ul>

	<?php

	// Output the current buffer
	echo ob_get_clean();
}

function wp_relationships_connection_metabox_row( $object = null, $relationship = null, $types = array() ) {
	$class = ( null === $relationship )
		? 'add-new-relationship'
		: 'relationship'; ?>


	<li class="<?php echo esc_attr( $class ); ?>">
		<div class="row-wrapper">
			<div class="actions">
				<i class="dashicons dashicons-menu"></i>
				<a href="" class="add-relationship dashicons dashicons-plus-alt">
					<span class="screen-reader-text">
						<?php esc_html_e( 'Add Relationship', 'wp-relationships' ); ?>
					</span>
				</a>
				<a href="" class="remove-relationship dashicons dashicons-dismiss">
					<span class="screen-reader-text">
						<?php esc_html_e( 'Remove Relationship', 'wp-relationships' ); ?>
					</span>
				</a>
			</div>

			<select name="relationship_type" id="type">
				<option value="none"><?php esc_html_e( '&mdash; None &mdash;', 'wp-relationships' ); ?></option><?php

				// Loop through types
				foreach ( $types as $type ) :

					// Output type
					?><option value="<?php echo esc_attr( $type->id ); ?>" <?php selected( $type->id, $relationship->relationship_type, true ); ?>><?php echo esc_html( $type->to->name ); ?></option><?php

				endforeach;

			?></select>

			<input type="text"   name="relationship_to_id[]" class="relationship-to-id" value="<?php echo esc_attr( $relationship->relationship_to_id ); ?>" />
			<input type="hidden" name="relationship_from_id[]" class="relationship-from-id" value="<?php echo esc_attr( $object->id ); ?>" />
			<input type="hidden" name="relationship_id[]" class="relationship-id" value="<?php echo esc_attr( $relationship->relationship_id ); ?>" />
		</div>
	</li>

	<?php
}