<?php

/**
 * Relationships Slug Metabox
 *
 * @package Plugins/Relationships/Metaboxes/Slug
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Render the slug metabox for relationship screen
 *
 * @since 0.1.0
 *
 * @param WP_Relationship $relationship The WP_Relationship being edited.
 */
function wp_relationships_slug_metabox( $relationship = null ) {
?>

	<table class="form-table">
		<tr class="relationship-slug-wrap">
			<th><label for="relationship_slug"><?php esc_html_e( 'Slug', 'wp-relationships' ); ?></label></th>
			<td><input type="text" name="relationship_slug" id="relationship_slug" value="<?php echo esc_attr( $relationship->relationship_slug ); ?>" class="code" /></td>
		</tr>
	</table>

	<?php
}
