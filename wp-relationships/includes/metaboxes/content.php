<?php


/**
 * Output the event duration metabox
 *
 * @since  1.1.0
 *
 * @param WP_Relationship $relationship The post
*/
function wp_relationships_content_metabox( $relationship = null ) {
	wp_editor( $relationship->post_content, 'relationship_content' );
}
