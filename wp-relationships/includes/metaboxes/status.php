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
	$statuses = wp_relationships_get_statuses(); ?>

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
			<div id="publishing-action">
				<?php submit_button( esc_html__( 'Update', 'wp-relationships' ), 'primary', 'save', false ); ?>
				<input type="hidden" name="action" value="update" />
				<input type="hidden" name="user_id" id="user_id" value="<?php echo esc_attr( $relationship->ID ); ?>" />
			</div>
			<div class="clear"></div>
		</div>
	</div>

	<?php
}
