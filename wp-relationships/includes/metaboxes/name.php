<?php

/**
 * Relationships Name Metabox
 *
 * @package Plugins/Relationships/Metaboxes/Name
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Render the name metabox for relationship screen
 *
 * @since 0.1.0
 *
 * @param WP_Relationship $relationship The WP_Relationship being edited.
 */
function wp_relationships_name_metabox( $relationship = null ) {
?>

	<div id="titlediv">
		<div id="titlewrap">
			<label class="" id="title-prompt-text" for="title"><?php esc_html_e( 'Enter name here', 'wp-relationships' ); ?></label>
			<input type="text" name="relationship_name" size="30" value="<?php echo esc_attr( $relationship->relationship_name ); ?>" id="title" spellcheck="true" autocomplete="off">
		</div>
	</div>

	<?php
}
