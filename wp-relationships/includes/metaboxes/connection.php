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

	// Relationship query
	$connections = new WP_Relationship_Query( array(
		'type'    => 'post_post',
		'from_id' => $object->ID
	) );

	// Relationship types
	$types = wp_get_relationships_of_type( 'post' );

	// Setup relationships
	$objects = ! empty( $connections->relationships )
		? $connections->relationships
		: array( WP_Relationship::get_instance() ); ?>

	<ul class="new-relationship">

		<?php foreach ( $objects as $_object ) : ?>

			<li class="add-new-relationship">
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

				<select name="relationship_type" id="type"><?php

					// Loop through types
					foreach ( $types as $type ) :

						// Output type
						?><option value="<?php echo esc_attr( $type->id ); ?>" <?php selected( $type->id, $_object->relationship_type, true ); ?>><?php echo esc_html( $type->name ); ?></option><?php

					endforeach;

				?></select>

				<input type="hidden" name="relationship_from_id[]" class="relationship-from-id" value="<?php echo esc_attr( $object->id ); ?>" />
				<input type="text"   name="relationship_to_id[]" class="relationship-to-id" value="<?php echo esc_attr( $_object->relationship_to_id ); ?>" />
				<input type="hidden" name="relationship_id[]" class="relationship-id" value="<?php echo esc_attr( $_object->relationship_id ); ?>" />
			</li>

		<?php endforeach; ?>

	</ul>

	<?php
}
