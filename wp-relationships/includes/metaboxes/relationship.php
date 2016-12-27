<?php

/**
 * Relationships Relationship Metabox
 *
 * @package Plugins/Relationships/Metaboxes/Relationship
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Render the relationship metabox for relationship screen
 *
 * @since 0.1.0
 *
 * @param WP_Relationship $relationship The WP_Relationship being edited.
 */
function wp_relationships_relationship_metabox( $relationship = null ) {
?>

	<table class="form-table">
		<tr class="relationship-from-wrap">
			<th><label for="relationship_from_id"><?php esc_html_e( 'From', 'wp-relationships' ); ?></label></th>
			<td><input type="number" name="relationship_from_id" id="relationship_from_id" value="<?php echo esc_attr( $relationship->relationship_from_id ); ?>" class="code" /></td>
		</tr>
		<tr class="relationship-to-wrap">
			<th><label for="relationship_to_id"><?php esc_html_e( 'To', 'wp-relationships' ); ?></label></th>
			<td><input type="number" name="relationship_to_id" id="relationship_to_id" value="<?php echo esc_attr( $relationship->relationship_to_id ); ?>" class="code" /></td>
		</tr>
	</table>

	<?php
}
