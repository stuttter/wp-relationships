<?php

/**
 * Relationship Status Metabox
 *
 * @package Plugins/Relationships/Metaboxes/Status
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Render the status metabox for relationship screen
 *
 * @since 0.1.0
 *
 * @param WP_Relationship $relationship The WP_Relationship object to be edited.
 */
function wp_relationships_publish_metabox( $relationship = null ) {

	// Dropdown data
	$types    = wp_relationships_get_types();
	$statuses = wp_relationships_get_statuses();
	$action   = ! empty( $relationship->relationship_id )
		? 'edit'
		: 'add'; ?>

	<div class="submitbox">
		<div id="minor-publishing">
			<div id="misc-publishing-actions">
				<div class="misc-pub-section" id="relationship-type-select">
					<label for="relationship_status"><?php esc_html_e( 'Type', 'wp-relationships' ); ?></label>
					<select name="relationship_type" id="type"><?php

						// Loop throug sites
						foreach ( $types as $type ) :

							// Maybe selected
							$selected = ! empty( $relationship )
								? selected( $type->type_id, $relationship->relationship_status )
								: '';

							// Loop through sites
							?><option value="<?php echo esc_attr( $type->type_id ); ?>" <?php echo $selected; ?>><?php echo esc_html( $type->type_name ); ?></option><?php

						endforeach;

					?></select>
				</div>

				<div class="misc-pub-section" id="relationship-status-select">
					<label for="relationship_status"><?php esc_html_e( 'Status', 'wp-relationships' ); ?></label>
					<select name="relationship_status" id="status"><?php

						// Loop through sites
						foreach ( $statuses as $status ) :

							// Maybe selected
							$selected = ! empty( $relationship )
								? selected( $status->status_id, $relationship->relationship_status )
								: '';

							// Loop through sites
							?><option value="<?php echo esc_attr( $status->status_id ); ?>" <?php echo $selected; ?>><?php echo esc_html( $status->status_name ); ?></option><?php

						endforeach;

					?></select>
				</div>
			</div>

			<div class="clear"></div>
		</div>

		<div id="major-publishing-actions">
			<div id="publishing-action"><?php

				// Add
				if ( 'add' === $action ) {
					wp_nonce_field( 'relationship_add' );
					$submit_text = esc_html__( 'Add Relationship', 'wp-relationships' );

				// Edit
				} else {
					wp_nonce_field( "relationship_edit-{$relationship->relationship_id}" );
					$submit_text = esc_html__( 'Save Relationship', 'wp-relationships' );
				}

				submit_button( $submit_text, 'primary', 'save', false ); ?>
				<input type="hidden" name="action"           value="<?php echo esc_attr( $action                        ); ?>">
				<input type="hidden" name="relationship_ids" value="<?php echo esc_attr( $relationship->relationship_id ); ?>">
			</div>
			<div class="clear"></div>
		</div>
	</div>

	<?php
}
