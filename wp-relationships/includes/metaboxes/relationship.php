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
			<th><label for="relationship_from"><?php esc_html_e( 'From', 'wp-relationships' ); ?></label></th>
			<td><input type="text" name="relationship_from" id="relationship_from" value="<?php echo esc_attr( $relationship->relationship_from ); ?>" class="regular-text" /></td>
		</tr>
		<tr class="relationship-top-wrap">
			<th><label for="relationship_top"><?php esc_html_e( 'To', 'wp-relationships' ); ?></label></th>
			<td><input type="text" name="relationship_top" id="relationship_to" value="<?php echo esc_attr( $relationship->relationship_to ); ?>" class="regular-text" /></td>
		</tr>
	</table>

	<?php
}
