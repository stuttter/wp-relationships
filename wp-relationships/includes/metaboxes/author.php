<?php

/**
 * Relationships Author Metabox
 *
 * @package Plugins/Relationships/Metaboxes/Author
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Render the relationships metabox for relationship screen
 *
 * @since 0.1.0
 *
 * @param WP_Relationship $relationship The WP_Relationship being edited.
 */
function wp_relationships_author_metabox( $relationship = null ) {
?>

	<table class="form-table">
		<tr class="relationship-author-wrap">
			<th><label for="relationship_author"><?php esc_html_e( 'Author', 'wp-relationships' ); ?></label></th>
			<td><?php

				wp_dropdown_users( array(
					'who'              => 'authors',
					'name'             => 'relationship_author',
					'selected'         => ! empty( $relationship->relationship_author )
												? $relationship->relationship_author
												: get_current_user_id(),
					'include_selected' => true,
					'show'             => 'display_name_with_login',
				) );

			?> </td>
		</tr>
	</table>

	<?php
}
