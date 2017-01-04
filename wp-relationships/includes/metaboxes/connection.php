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

	// Get child relationships
	$default = array( (object) array( 'relationship_to_id' => 0, 'name' => '' ) );

	// Relationship query
	$connections = new WP_Relationship_Query( array(
		'relationship_type' => 'post_post',
		'from'                => $object->id
	) );

	// Setup relationships
	$objects = ! empty( $connections )
		? $connections
		: $default; ?>

	<p class="description"><?php esc_html_e( 'Obligations can have multiple due-dates, each with a different amount. If this is a single relationship, only enter one due-date.', 'wp-relationships' ); ?></p>
	<ul class="new-relationship">
		
		<?php foreach ( $objects as $_object ) :
			$_object = get_post( $_object->relationship_to_id ); ?>

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

				<input type="text"   name="relationship-to-id[]" class="relationship-to-id" value="<?php echo esc_attr( $_object->id ); ?>" />
				<input type="hidden" name="relationship-id[]" class="relationship-id" value="<?php echo esc_attr( $_object->id ); ?>" />
			</li>

		<?php endforeach; ?>

	</ul>

	<?php
}
