<?php

/**
 * Register a relationship type.
 *
 * @param array $args
 * @return bool|object False on failure, P2P_Connection_Type instance on success.
 */
function wp_register_relationship_type( $args ) {

	$ctype = P2P_Connection_Type_Factory::register( $args );

	do_action( 'wp_relationships_registered_relationship_type', $ctype, $args );

	return $ctype;
}

/**
 * Get a connection type.
 *
 * @param string $type
 *
 * @return bool|object False if connection type not found, instance on success.
 */
function wp_get_relationship_type( $type = '' ) {
	return P2P_Connection_Type_Factory::get_instance( $type );
}

/**
 * Check if a certain connection exists.
 *
 * @param string $type A valid connection type.
 * @param array $args Query args.
 *
 * @return bool
 */
function wp_relationship_exists( $type, $args = array() ) {
	$args['fields'] = 'count';

	$r = wp_get_relationships( $type, $args );

	return (bool) $r;
}

/**
 * Create a connection
 *
 * @param int $type A valid connection type.
 * @param array $args Connection information.
 *
 * @return bool|int False on failure, wp_relationships_id on success.
 */
function wp_create_relationship( $type, $args ) {
	global $wpdb;

	$args = wp_parse_args( $args, array(
		'direction' => 'from',
		'from'      => false,
		'to'        => false,
		'meta'      => array()
	) );

	list( $from ) = _wp_relationships_normalize( $args['from'] );
	list( $to   ) = _wp_relationships_normalize( $args['to'] );

	if ( empty( $from ) || empty( $to ) ) {
		return false;
	}

	$dirs = array( $from, $to );

	if ( 'to' === $args['direction'] ) {
		$dirs = array_reverse( $dirs );
	}

	$wpdb->insert( $wpdb->p2p, array(
		'wp_relationships_type' => $type,
		'wp_relationships_from' => $dirs[0],
		'wp_relationships_to'   => $dirs[1]
	) );

	$relationship_id = $wpdb->insert_id;

	foreach ( $args['meta'] as $key => $value ) {
		wp_relationships_add_meta( $relationship_id, $key, $value );
	}

	do_action( 'wp_relationships_created_connection', $relationship_id );

	return $relationship_id;
}

/**
 * Delete one or more connections.
 *
 * @param int $type A valid connection type.
 * @param array $args Connection information.
 *
 * @return int Number of connections deleted
 */
function wp_relationships_delete_relationships( $type, $args = array() ) {
	$args['fields'] = 'wp_relationships_id';

	return wp_relationships_delete_relationship( wp_get_relationships( $type, $args ) );
}

/**
 * Delete connections using wp_relationships_ids.
 *
 * @param int|array $relationship_id Connection ids
 *
 * @return int Number of connections deleted
 */
function wp_delete_relationship( $relationship_id ) {
	global $wpdb;

	if ( empty( $relationship_id ) ) {
		return 0;
	}

	$relationship_ids = array_map( 'absint', (array) $relationship_id );

	do_action( 'wp_relationships_delete_connections', $relationship_ids );

	$where = "WHERE wp_relationships_id IN (" . implode( ',', $relationship_ids ) . ")";

	$count = $wpdb->query( "DELETE FROM $wpdb->p2p $where" );
	$wpdb->query( "DELETE FROM $wpdb->p2pmeta $where" );

	return $count;
}

/**
 * List some items.
 *
 * @param object|array A P2P_List instance, a WP_Query instance, or a list of post objects
 * @param array $args (optional)
 */
function wp_relationships_list_posts( $posts, $args = array() ) {
	if ( is_a( $posts, 'P2P_List' ) ) {
		$list = $posts;
	} else {
		if ( is_a( $posts, 'WP_Query' ) ) {
			$posts = $posts->posts;
		}

		$list = new P2P_List( $posts, 'P2P_Item_Post' );
	}

	return P2P_List_Renderer::render( $list, $args );
}

/**
 * Given a list of objects and another list of connected items,
 * distribute each connected item to it's respective counterpart.
 *
 * @param array List of objects
 * @param array List of connected objects
 * @param string Name of connected array property
 */
function wp_relationships_distribute_connected( $items, $connected, $prop_name ) {
	$indexed_list = array();

	foreach ( $items as $item ) {
		$item->$prop_name = array();
		$indexed_list[ $item->ID ] = $item;
	}

	$groups = scb_list_group_by( $connected, '_wp_relationships_get_other_id' );

	foreach ( $groups as $outer_item_id => $connected_items ) {
		$indexed_list[ $outer_item_id ]->$prop_name = $connected_items;
	}
}
