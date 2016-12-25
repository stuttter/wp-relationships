<?php

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Add meta data field to a relationship.
 *
 * @since 0.1.0
 *
 * @param int    $id         Relationship ID.
 * @param string $meta_key   Metadata name.
 * @param mixed  $meta_value Metadata value. Must be serializable if non-scalar.
 * @param bool   $unique     Optional. Whether the same key should not be added.
 *                           Default false.
 * @return int|false Meta ID on success, false on failure.
 */
function add_relationship_meta( $id, $meta_key, $meta_value, $unique = false ) {
	return add_metadata( 'relationship', $id, $meta_key, $meta_value, $unique );
}

/**
 * Remove metadata matching criteria from a relationship.
 *
 * You can match based on the key, or key and value. Removing based on key and
 * value, will keep from removing duplicate metadata with the same key. It also
 * allows removing all metadata matching key, if needed.
 *
 * @since 0.1.0
 *
 * @param int    $id         Relationship ID.
 * @param string $meta_key   Metadata name.
 * @param mixed  $meta_value Optional. Metadata value. Must be serializable if
 *                           non-scalar. Default empty.
 * @return bool True on success, false on failure.
 */
function delete_relationship_meta( $id, $meta_key, $meta_value = '' ) {
	return delete_metadata( 'relationship', $id, $meta_key, $meta_value );
}

/**
 * Retrieve relationship meta field for a relationship.
 *
 * @since 0.1.0
 *
 * @param int    $id        Relationship ID.
 * @param string $meta_key  Optional. The meta key to retrieve. By default, returns
 *                          data for all keys. Default empty.
 * @param bool   $single    Optional. Whether to return a single value. Default false.
 * @return mixed Will be an array if $single is false. Will be value of meta data
 *               field if $single is true.
 */
function get_relationship_meta( $id, $meta_key = '', $single = false ) {
	return get_metadata( 'relationship', $id, $meta_key, $single );
}

/**
 * Update relationship meta field based on relationship ID.
 *
 * Use the $prev_value parameter to differentiate between meta fields with the
 * same key and relationship ID.
 *
 * If the meta field for the relationship does not exist, it will be added.
 *
 * @since 0.1.0
 *
 * @param int    $id         Relationship ID.
 * @param string $meta_key   Metadata key.
 * @param mixed  $meta_value Metadata value. Must be serializable if non-scalar.
 * @param mixed  $prev_value Optional. Previous value to check before removing.
 *                           Default empty.
 * @return int|bool Meta ID if the key didn't exist, true on successful update,
 *                  false on failure.
 */
function update_relationship_meta( $id, $meta_key, $meta_value, $prev_value = '' ) {
	return update_metadata( 'relationship', $id, $meta_key, $meta_value, $prev_value );
}

