<?php

namespace WP_Object_Relationship\CLI;

use WP_CLI;
use WP_CLI_Command;
use WP_CLI\Formatter;
use WP_CLI\Utils;
use WP_Error;

class Alias_Command extends WP_CLI_Command {

	/**
	 * Display a list of aliases
	 *
	 * @param Alias[] $relationships Alias objects to show
	 * @param array $options
	 */
	protected function display( $relationships, $options ) {

		$options = wp_parse_args( $options, array(
			'format' => 'table',
			'fields' => array( 'id', 'domain', 'site_id', 'created', 'status' ),
		) );

		$mapper = function ( WP_Object_Relationship_ $relationship ) {
			return array(
				'id'      => $relationship->id,
				'domain'  => $relationship->domain,
				'site_id' => $relationship->site_id,
				'created' => $relationship->created,
				'status'  => ( 'active' === $relationship->status )
					? __( 'Active',   'wp-object-relationships' )
					: __( 'Inactive', 'wp-object-relationships' )
			);
		};

		$display_items = Utils\iterator_map( $relationships, $mapper );

		$formatter = new Formatter( $options );

		$formatter->display_items( $display_items );
	}

	/**
	 * ## OPTIONS
	 *
	 * [<site>]
	 * : Site ID (defaults to current site, use `--url=...`)
	 *
	 * [--format=<format>]
	 * : Format to display as (table, json, csv, count)
	 *
	 * @subcommand list
	 */
	public function list_( $args, $assoc_args ) {
		$id = empty( $args[0] )
			? get_current_blog_id()
			: absint( $args[0] );

		$relationships = WP_Object_Relationship::get_by_site( $id );

		if ( empty( $relationships ) ) {
			return;
		}

		$this->display( $relationships, $assoc_args );
	}

	/**
	 * Get a single alias
	 *
	 * ## OPTIONS
	 *
	 * <id>
	 * : Alias ID
	 *
	 * [--format=<format>]
	 * : Format to display as (table, json, csv, count)
	 */
	public function get( $args, $assoc_args ) {
		$relationship = WP_Object_Relationship::get_instance( $args[0] );

		if ( empty( $relationship ) ) {
			$relationship = new WP_Error( 'wp_object_relationships_cli_alias_not_found', __( 'Invalid alias ID', 'wp-object-relationships' ) );
		}

		if ( is_wp_error( $relationship ) ) {
			return WP_CLI::error( $relationship->get_error_message() );
		}

		$relationships = array( $relationship );

		$this->display( $relationships, $assoc_args );
	}

	/**
	 * Delete a single alias
	 *
	 * ## OPTIONS
	 *
	 * <id>
	 * : Alias ID
	 */
	public function delete( $args ) {
		$relationship = WP_Object_Relationship::get_instance( $args[0] );

		if ( empty( $relationship ) ) {
			$relationship = new WP_Error( 'wp_object_relationships_cli_alias_not_found', __( 'Invalid alias ID', 'wp-object-relationships' ) );
		}

		if ( is_wp_error( $relationship ) ) {
			return WP_CLI::error( $relationship->get_error_message() );
		}

		$result = $relationship->delete();

		if ( empty( $result ) || is_wp_error( $result ) ) {
			return WP_CLI::error( __( 'Could not delete alias', 'wp-object-relationships' ) );
		}
	}
}
