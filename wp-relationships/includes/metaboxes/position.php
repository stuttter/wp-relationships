<?php

/**
 * Relationships Position Metabox
 *
 * @package Plugins/Relationships/Metaboxes/Position
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Render the position metabox for relationship screen
 *
 * @since 0.1.0
 *
 * @param WP_Relationship $relationship The WP_Relationship being edited.
 */
function wp_relationships_position_metabox( $relationship = null ) {
?>

	<table class="form-table">
		<tr class="relationship-parent-wrap">
			<th><label for="relationship_parent"><?php esc_html_e( 'Parent', 'wp-relationships' ); ?></label></th>
			<td><input type="text" name="relationship_parent" id="relationship_parent" value="<?php echo esc_attr( $relationship->relationship_parent ); ?>" /></td>
		</tr>
		<tr class="relationship-order-wrap">
			<th><label for="relationship_order"><?php esc_html_e( 'Order', 'wp-relationships' ); ?></label></th>
			<td><input type="text" name="relationship_order" id="relationship_order" value="<?php echo esc_attr( $relationship->relationship_order ); ?>" /></td>
		</tr>
	</table>

	<?php
}
