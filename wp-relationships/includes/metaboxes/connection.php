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

	// Relationship types
	$types = wp_get_types_from_relationship_object( array(
		'object' => get_class( $object )
	) );

	// Relationship query
	$connections = new WP_Relationship_Query( array(
		'type__in' => wp_list_pluck( $types, 'id' ),
		'from_id'  => $object->ID
	) );

	// Setup relationships
	$relationships = ! empty( $connections->relationships )
		? $connections->relationships
		: array( WP_Relationship::get_instance() );

	// Start an output buffer
	ob_start(); ?>

	<ul class="new-relationship">
		<li class="no-relationships">
			<div class="row-wrapper">
				<span><?php esc_html_e( 'No relationships.', 'wp-relationship' ); ?></span>
				<a href="" class="add-relationship"><?php esc_html_e( 'Add New', 'wp-relationship' ); ?></a>
			</div>
		</li>

		<?php

		// Output the template row
		wp_relationships_connection_metabox_row( $object, null, $types );

		// Output any actual relationship rows
		foreach ( $relationships as $relationship ) {
			wp_relationships_connection_metabox_row( $object, $relationship, $types );
		}

		?>
	</ul>

	<?php

	// Output the current buffer
	echo ob_get_clean();
}

/**
 * DRY method for getting meta-box rows
 *
 * @since 0.1.0
 *
 * @param object $object
 * @param object $relationship
 * @param array  $types
 */
function wp_relationships_connection_metabox_row( $object = null, $relationship = null, $types = array() ) {

	// Null is the "Add New" row
	$class = ( null === $relationship )
		? 'add-new-relationship'
		: 'relationship';

	// Start an output buffer
	ob_start(); ?>

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
					?><option value="<?php echo esc_attr( $type->id ); ?>" <?php selected( $type->id, $relationship->relationship_type, true ); ?>><?php echo esc_html( wp_relationships_get_object( array( 'id' => $type->to ) )->name ); ?></option><?php

				endforeach;

			?></select>

			<input type="text"   name="relationship_to_id[]" class="relationship-to-id" value="<?php echo esc_attr( $relationship->relationship_to_id ); ?>" />
			<input type="hidden" name="relationship_from_id[]" class="relationship-from-id" value="<?php echo esc_attr( $object->id ); ?>" />
			<input type="hidden" name="relationship_id[]" class="relationship-id" value="<?php echo esc_attr( $relationship->relationship_id ); ?>" />
		</div>
	</li>

	<?php

	// Output the row
	echo ob_get_clean();
}