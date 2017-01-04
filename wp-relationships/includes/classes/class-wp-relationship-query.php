<?php
/**
 * Site API: WP_Relationship_Query class
 *
 * @package Plugins/Relationships/Queries
 * @since 0.1.0
 */

/**
 * Core class used for querying relationships.
 *
 * @since 0.1.0
 *
 * @see WP_Relationship_Query::__construct() for accepted arguments.
 */
class WP_Relationship_Query {

	/**
	 * SQL for database query.
	 *
	 * @since 0.1.0
	 * @access public
	 * @var string
	 */
	public $request;

	/**
	 * Stash the database class
	 *
	 * @since 0.1.0
	 */
	private $db;

	/**
	 * SQL query clauses.
	 *
	 * @since 0.1.0
	 * @access protected
	 * @var array
	 */
	protected $sql_clauses = array(
		'select'  => '',
		'from'    => '',
		'where'   => array(),
		'groupby' => '',
		'orderby' => '',
		'limits'  => '',
	);

	/**
	 * Date query container.
	 *
	 * @since 0.1.0
	 * @access public
	 * @var object WP_Date_Query
	 */
	public $date_query = false;

	/**
	 * Meta query container.
	 *
	 * @since 0.1.0
	 * @access public
	 * @var object WP_Date_Query
	 */
	public $meta_query = false;

	/**
	 * Query vars set by the user.
	 *
	 * @since 0.1.0
	 * @access public
	 * @var array
	 */
	public $query_vars;

	/**
	 * Default values for query vars.
	 *
	 * @since 0.1.0
	 * @access public
	 * @var array
	 */
	public $query_var_defaults;

	/**
	 * List of relationships located by the query.
	 *
	 * @since 0.1.0
	 * @access public
	 * @var array
	 */
	public $relationships;

	/**
	 * The amount of found relationships for the current query.
	 *
	 * @since 0.1.0
	 * @access public
	 * @var int
	 */
	public $found_relationships = 0;

	/**
	 * The number of pages.
	 *
	 * @since 0.1.0
	 * @access public
	 * @var int
	 */
	public $max_num_pages = 0;

	/**
	 * Sets up the relationship query, based on the query vars passed.
	 *
	 * @since 0.1.0
	 * @access public
	 *
	 * @param string|array $query {
	 *     Optional. Array or query string of relationship query parameters. Default empty.
	 *
	 *     @type int          $ID                   An relationship ID to only return that relationship. Default empty.
	 *     @type array        $relationship__in     Array of relationship IDs to include. Default empty.
	 *     @type array        $relationship__not_in Array of relationship IDs to exclude. Default empty.
	 *     @type int          $author_id            A author ID to only return that author. Default empty.
	 *     @type array        $author__in           Array of author IDs to include. Default empty.
	 *     @type array        $author__not_in       Array of author IDs to exclude. Default empty.
	 *     @type string       $type                 Limit results to those affiliated with a given path.
	 *                                                Default empty.
	 *     @type array        $type__in             Array of paths to include affiliated relationships for. Default empty.
	 *     @type array        $type__not_in         Array of paths to exclude affiliated relationships for. Default empty.
	 *     @type string       $status               Limit results to those affiliated with a given path.
	 *                                              Default empty.
	 *     @type array        $status__in           Array of paths to include affiliated relationships for. Default empty.
	 *     @type array        $status__not_in       Array of paths to exclude affiliated relationships for. Default empty.
	 *     @type string       $slug                 Limit results to those affiliated with a given path.
	 *                                                Default empty.
	 *     @type array        $slug__in             Array of paths to include affiliated relationships for. Default empty.
	 *     @type array        $slug__not_in         Array of paths to exclude affiliated relationships for. Default empty.
	 *     @type int          $parent_id            A parent ID to only return that parent. Default empty.
	 *     @type array        $parent__in           Array of parent IDs to include. Default empty.
	 *     @type array        $parent__not_in       Array of parent IDs to exclude. Default empty.
	 *     @type int          $from_id              A from ID to only return that from. Default empty.
	 *     @type array        $from__in             Array of from IDs to include. Default empty.
	 *     @type array        $from__not_in         Array of from IDs to exclude. Default empty.
	 *     @type int          $to_id                A to ID to only return that to. Default empty.
	 *     @type array        $to__in               Array of to IDs to include. Default empty.
	 *     @type array        $to__not_in           Array of to IDs to exclude. Default empty.
	 *     @type bool         $count                Whether to return a relationship count (true) or array of relationship objects.
	 *                                              Default false.
	 *     @type array        $date_query           Date query clauses to limit relationships by. See WP_Date_Query.
	 *                                              Default null.
	 *     @type array        $meta_query           Meta query clauses to limit relationships by. See WP_Meta_Query.
	 *                                              Default null.
	 *     @type string       $fields               Site fields to return. Accepts 'ids' (returns an array of relationship IDs)
	 *                                              or empty (returns an array of complete relationship objects). Default empty.
	 *     @type int          $number               Maximum number of relationships to retrieve. Default null (no limit).
	 *     @type int          $offset               Number of relationships to offset the query. Used to build LIMIT clause.
	 *                                              Default 0.
	 *     @type bool         $no_found_rows        Whether to disable the `SQL_CALC_FOUND_ROWS` query. Default true.
	 *     @type string|array $orderby              Site status or array of statuses. Accepts 'id', 'domain', 'status',
	 *                                              'created', 'domain_length', 'path_length', or 'site__in'. Also accepts false,
	 *                                              an empty array, or 'none' to disable `ORDER BY` clause.
	 *                                              Default 'id'.
	 *     @type string       $order                How to order retrieved relationships. Accepts 'ASC', 'DESC'. Default 'ASC'.
	 *     @type string       $search               Search term(s) to retrieve matching relationships for. Default empty.
	 *     @type array        $search_columns       Array of column names to be searched. Accepts 'domain' and 'status'.
	 *                                              Default empty array.
	 *
	 *     @type bool         $update_relationship_cache Whether to prime the cache for found relationships. Default false.
	 * }
	 */
	public function __construct( $query = '' ) {
		$this->db = $GLOBALS['wpdb'];

		$this->query_var_defaults = array(
			'fields'                    => '',
			'ID'                        => '',
			'relationship__in'          => '',
			'relationship__not_in'      => '',
			'author_id'                 => '',
			'author__in'                => '',
			'author__not_in'            => '',
			'type'                      => '',
			'type__in'                  => '',
			'type__not_in'              => '',
			'slug'                      => '',
			'slug__in'                  => '',
			'slug__not_in'              => '',
			'status'                    => '',
			'status__in'                => '',
			'status__not_in'            => '',
			'parent'                    => '',
			'parent__in'                => '',
			'parent__not_in'            => '',
			'from_id'                   => '',
			'from__in'                  => '',
			'from__not_in'              => '',
			'to_id'                     => '',
			'to__in'                    => '',
			'to__not_in'                => '',
			'number'                    => 100,
			'offset'                    => '',
			'orderby'                   => 'order, ID',
			'order'                     => 'ASC',
			'search'                    => '',
			'search_columns'            => array(),
			'count'                     => false,
			'date_query'                => null, // See WP_Date_Query
			'meta_query'                => null, // See WP_Meta_Query
			'no_found_rows'             => true,
			'update_relationship_cache' => true,
		);

		if ( ! empty( $query ) ) {
			$this->query( $query );
		}
	}

	/**
	 * Parses arguments passed to the relationship query with default query parameters.
	 *
	 * @since 0.1.0
	 * @access public
	 *
	 * @see WP_Relationship_Query::__construct()
	 *
	 * @param string|array $query Array or string of WP_Relationship_Query arguments. See WP_Relationship_Query::__construct().
	 */
	public function parse_query( $query = '' ) {
		if ( empty( $query ) ) {
			$query = $this->query_vars;
		}

		$this->query_vars = wp_parse_args( $query, $this->query_var_defaults );

		/**
		 * Fires after the relationship query vars have been parsed.
		 *
		 * @since 0.1.0
		 *
		 * @param WP_Relationship_Query &$this The WP_Relationship_Query instance (passed by reference).
		 */
		do_action_ref_array( 'parse_relationships_query', array( &$this ) );
	}

	/**
	 * Sets up the WordPress query for retrieving relationships.
	 *
	 * @since 0.1.0
	 * @access public
	 *
	 * @param string|array $query Array or URL query string of parameters.
	 * @return array|int List of relationships, or number of relationships when 'count' is passed as a query var.
	 */
	public function query( $query ) {
		$this->query_vars = wp_parse_args( $query );

		return $this->get_relationships();
	}

	/**
	 * Retrieves a list of relationships matching the query vars.
	 *
	 * @since 0.1.0
	 * @access public
	 *
	 * @return array|int List of relationships, or number of relationships when 'count' is passed as a query var.
	 */
	public function get_relationships() {
		$this->parse_query();

		/**
		 * Fires before relationships are retrieved.
		 *
		 * @since 0.1.0
		 *
		 * @param WP_Relationship_Query &$this Current instance of WP_Relationship_Query, passed by reference.
		 */
		do_action_ref_array( 'pre_get_relationships', array( &$this ) );

		// $args can include anything. Only use the args defined in the query_var_defaults to compute the key.
		$key = md5( serialize( wp_array_slice_assoc( $this->query_vars, array_keys( $this->query_var_defaults ) ) ) );
		$last_changed = wp_cache_get( 'last_changed', 'object-relationships' );

		if ( false === $last_changed ) {
			$last_changed = microtime();
			wp_cache_set( 'last_changed', $last_changed, 'object-relationships' );
		}

		$cache_key   = "get_relationships:{$key}:{$last_changed}";
		$cache_value = wp_cache_get( $cache_key, 'object-relationships' );

		if ( false === $cache_value ) {
			$relationship_ids = $this->get_relationship_ids();
			if ( $relationship_ids ) {
				$this->set_found_relationships( $relationship_ids );
			}

			$cache_value = array(
				'relationship_ids'    => $relationship_ids,
				'found_relationships' => $this->found_relationships,
			);
			wp_cache_add( $cache_key, $cache_value, 'object-relationships' );
		} else {
			$relationship_ids = $cache_value['relationship_ids'];
			$this->found_relationships = $cache_value['found_relationships'];
		}

		if ( $this->found_relationships && $this->query_vars['number'] ) {
			$this->max_num_pages = ceil( $this->found_relationships / $this->query_vars['number'] );
		}

		// If querying for a count only, there's nothing more to do.
		if ( $this->query_vars['count'] ) {
			// $relationship_ids is actually a count in this case.
			return intval( $relationship_ids );
		}

		$relationship_ids = array_map( 'intval', $relationship_ids );

		if ( 'ids' == $this->query_vars['fields'] ) {
			$this->relationships = $relationship_ids;

			return $this->relationships;
		}

		// Prime site network caches.
		if ( $this->query_vars['update_relationship_cache'] ) {
			_prime_object_relationship_caches( $relationship_ids );
		}

		// Fetch full relationship objects from the primed cache.
		$_relationships = array();
		foreach ( $relationship_ids as $relationship_id ) {
			$_relationship = get_object_relationship( $relationship_id );
			if ( ! empty( $_relationship ) ) {
				$_relationships[] = $_relationship;
			}
		}

		/**
		 * Filters the site query results.
		 *
		 * @since 0.1.0
		 *
		 * @param array         $results An array of relationships.
		 * @param WP_Relationship_Query &$this   Current instance of WP_Relationship_Query, passed by reference.
		 */
		$_relationships = apply_filters_ref_array( 'the_relationships', array( $_relationships, &$this ) );

		// Convert to WP_Relationship_ instances.
		$this->relationships = array_map( 'get_object_relationship', $_relationships );

		return $this->relationships;
	}

	/**
	 * Used internally to get a list of relationship IDs matching the query vars.
	 *
	 * @since 0.1.0
	 * @access protected
	 *
	 * @return int|array A single count of relationship IDs if a count query. An array of relationship IDs if a full query.
	 */
	protected function get_relationship_ids() {

		$order = $this->parse_order( $this->query_vars['order'] );

		// Disable ORDER BY with 'none', an empty array, or boolean false.
		if ( in_array( $this->query_vars['orderby'], array( 'none', array(), false ), true ) ) {
			$orderby = '';
		} elseif ( ! empty( $this->query_vars['orderby'] ) ) {
			$ordersby = is_array( $this->query_vars['orderby'] ) ?
				$this->query_vars['orderby'] :
				preg_split( '/[,\s]/', $this->query_vars['orderby'] );

			$orderby_array = array();
			foreach ( $ordersby as $_key => $_value ) {
				if ( empty( $_value ) ) {
					continue;
				}

				if ( is_int( $_key ) ) {
					$_orderby = $_value;
					$_order   = $order;
				} else {
					$_orderby = $_key;
					$_order   = $_value;
				}

				$parsed = $this->parse_orderby( $_orderby );

				if ( empty( $parsed ) ) {
					continue;
				}

				if ( 'relationship__in' === $_orderby ) {
					$orderby_array[] = $parsed;
					continue;
				}

				$orderby_array[] = $parsed . ' ' . $this->parse_order( $_order );
			}

			$orderby = implode( ', ', $orderby_array );
		} else {
			$orderby = "id {$order}";
		}

		$number = absint( $this->query_vars['number'] );
		$offset = absint( $this->query_vars['offset'] );

		if ( ! empty( $number ) ) {
			if ( $offset ) {
				$limits = 'LIMIT ' . $offset . ',' . $number;
			} else {
				$limits = 'LIMIT ' . $number;
			}
		}

		if ( $this->query_vars['count'] ) {
			$fields = 'COUNT(*)';
		} else {
			$fields = 'relationship_id';
		}

		// Parse site relationship IDs for an IN clause.
		$relationship_id = absint( $this->query_vars['ID'] );
		if ( ! empty( $relationship_id ) ) {
			$this->sql_clauses['where']['ID'] = $this->db->prepare( 'relationship_id = %d', $relationship_id );
		}

		// Parse site relationship IDs for an IN clause.
		if ( ! empty( $this->query_vars['relationship__in'] ) ) {
			$this->sql_clauses['where']['relationship__in'] = "relationship_id IN ( " . implode( ',', wp_parse_id_list( $this->query_vars['site__in'] ) ) . ' )';
		}

		// Parse site relationship IDs for a NOT IN clause.
		if ( ! empty( $this->query_vars['relationship__not_in'] ) ) {
			$this->sql_clauses['where']['relationship__not_in'] = "relationship_id NOT IN ( " . implode( ',', wp_parse_id_list( $this->query_vars['site__not_in'] ) ) . ' )';
		}

		if ( ! empty( $this->query_vars['type'] ) ) {
			$this->sql_clauses['where']['type'] = $this->db->prepare( 'relationship_type = %s', $this->query_vars['type'] );
		}

		// Parse relationship type for an IN clause.
		if ( is_array( $this->query_vars['type__in'] ) ) {
			$this->sql_clauses['where']['type__in'] = "relationship_type IN ( '" . implode( "', '", $this->db->_escape( $this->query_vars['type__in'] ) ) . "' )";
		}

		// Parse relationship type for a NOT IN clause.
		if ( is_array( $this->query_vars['type__not_in'] ) ) {
			$this->sql_clauses['where']['type__not_in'] = "relationship_type NOT IN ( '" . implode( "', '", $this->db->_escape( $this->query_vars['type__not_in'] ) ) . "' )";
		}

		if ( ! empty( $this->query_vars['slug'] ) ) {
			$this->sql_clauses['where']['slug'] = $this->db->prepare( 'relationship_slug = %s', $this->query_vars['slug'] );
		}

		// Parse relationship slug for an IN clause.
		if ( is_array( $this->query_vars['slug__in'] ) ) {
			$this->sql_clauses['where']['slug__in'] = "relationship_slug IN ( '" . implode( "', '", $this->db->_escape( $this->query_vars['slug__in'] ) ) . "' )";
		}

		// Parse relationship slug for a NOT IN clause.
		if ( is_array( $this->query_vars['slug__not_in'] ) ) {
			$this->sql_clauses['where']['slug__not_in'] = "relationship_slug NOT IN ( '" . implode( "', '", $this->db->_escape( $this->query_vars['slug__not_in'] ) ) . "' )";
		}

		if ( ! empty( $this->query_vars['status'] ) ) {
			$this->sql_clauses['where']['status'] = $this->db->prepare( 'relationship_status = %s', $this->query_vars['status'] );
		}

		// Parse relationship status for an IN clause.
		if ( is_array( $this->query_vars['status__in'] ) ) {
			$this->sql_clauses['where']['status__in'] = "relationship_status IN ( '" . implode( "', '", $this->db->_escape( $this->query_vars['status__in'] ) ) . "' )";
		}

		// Parse relationship status for a NOT IN clause.
		if ( is_array( $this->query_vars['status__not_in'] ) ) {
			$this->sql_clauses['where']['status__not_in'] = "relationship_status NOT IN ( '" . implode( "', '", $this->db->_escape( $this->query_vars['status__not_in'] ) ) . "' )";
		}

		// Falsey search strings are ignored.
		if ( strlen( $this->query_vars['search'] ) ) {
			$search_columns = array();

			if ( $this->query_vars['search_columns'] ) {
				$search_columns = array_intersect( $this->query_vars['search_columns'], array( 'relationship_name', 'relationship_content' ) );
			}

			if ( empty( $search_columns ) ) {
				$search_columns = array( 'relationship_name', 'relationship_content' );
			}

			/**
			 * Filters the columns to search in a WP_Relationship_Query search.
			 *
			 * The default columns include 'domain' and 'path.
			 *
			 * @since 0.1.0
			 *
			 * @param array         $search_columns Array of column names to be searched.
			 * @param string        $search         Text being searched.
			 * @param WP_Relationship_Query $this           The current WP_Relationship_Query instance.
			 */
			$search_columns = apply_filters( 'relationship_search_columns', $search_columns, $this->query_vars['search'], $this );

			$this->sql_clauses['where']['search'] = $this->get_search_sql( $this->query_vars['search'], $search_columns );
		}

		$date_query = $this->query_vars['date_query'];
		if ( ! empty( $date_query ) && is_array( $date_query ) ) {
			$this->date_query = new WP_Date_Query( $date_query, 'registered' );
			$this->sql_clauses['where']['date_query'] = preg_replace( '/^\s*AND\s*/', '', $this->date_query->get_sql() );
		}

		$where = implode( ' AND ', $this->sql_clauses['where'] );

		$pieces = array( 'fields', 'join', 'where', 'orderby', 'limits', 'groupby' );

		/**
		 * Filters the relationship query clauses.
		 *
		 * @since 0.1.0
		 *
		 * @param array $pieces A compacted array of relationship query clauses.
		 * @param WP_Relationship_Query &$this Current instance of WP_Relationship_Query, passed by reference.
		 */
		$clauses = apply_filters_ref_array( 'relationship_clauses', array( compact( $pieces ), &$this ) );

		$fields  = isset( $clauses['fields']  ) ? $clauses['fields']  : '';
		$join    = isset( $clauses['join']    ) ? $clauses['join']    : '';
		$where   = isset( $clauses['where']   ) ? $clauses['where']   : '';
		$orderby = isset( $clauses['orderby'] ) ? $clauses['orderby'] : '';
		$limits  = isset( $clauses['limits']  ) ? $clauses['limits']  : '';
		$groupby = isset( $clauses['groupby'] ) ? $clauses['groupby'] : '';

		if ( $where ) {
			$where = "WHERE {$where}";
		}

		if ( $groupby ) {
			$groupby = "GROUP BY {$groupby}";
		}

		if ( $orderby ) {
			$orderby = "ORDER BY {$orderby}";
		}

		$found_rows = '';
		if ( ! $this->query_vars['no_found_rows'] ) {
			$found_rows = 'SQL_CALC_FOUND_ROWS';
		}

		$this->sql_clauses['select']  = "SELECT {$found_rows} {$fields}";
		$this->sql_clauses['from']    = "FROM {$this->db->relationships} {$join}";
		$this->sql_clauses['groupby'] = $groupby;
		$this->sql_clauses['orderby'] = $orderby;
		$this->sql_clauses['limits']  = $limits;

		$this->request = "{$this->sql_clauses['select']} {$this->sql_clauses['from']} {$where} {$this->sql_clauses['groupby']} {$this->sql_clauses['orderby']} {$this->sql_clauses['limits']}";

		if ( $this->query_vars['count'] ) {
			return intval( $this->db->get_var( $this->request ) );
		}

		$relationship_ids = $this->db->get_col( $this->request );

		return array_map( 'intval', $relationship_ids );
	}

	/**
	 * Populates found_relationships and max_num_pages properties for the current query
	 * if the limit clause was used.
	 *
	 * @since 0.1.0
	 * @access private
	 *
	 * @global wpdb  $this->db WordPress database abstraction object.
	 * @param  array $relationship_ids Optional array of relationship IDs
	 */
	private function set_found_relationships( $relationship_ids = array() ) {
		if ( ! empty( $this->query_vars['number'] ) && ! empty( $this->query_vars['no_found_rows'] ) ) {
			/**
			 * Filters the query used to retrieve found relationship count.
			 *
			 * @since 0.1.0
			 *
			 * @param string              $found_relationships_query SQL query. Default 'SELECT FOUND_ROWS()'.
			 * @param WP_Relationship_Query $relationship_query         The `WP_Relationship_Query` instance.
			 */
			$found_relationships_query = apply_filters( 'found_relationships_query', 'SELECT FOUND_ROWS()', $this );

			$this->found_relationships = (int) $this->db->get_var( $found_relationships_query );
		} elseif ( ! empty( $relationship_ids ) ) {
			$this->found_relationships = count( $relationship_ids );
		}
	}

	/**
	 * Used internally to generate an SQL string for searching across multiple columns.
	 *
	 * @since 0.1.0
	 * @access protected
	 *
	 * @param string $string  Search string.
	 * @param array  $columns Columns to search.
	 * @return string Search SQL.
	 */
	protected function get_search_sql( $string, $columns ) {

		if ( false !== strpos( $string, '*' ) ) {
			$like = '%' . implode( '%', array_map( array( $this->db, 'esc_like' ), explode( '*', $string ) ) ) . '%';
		} else {
			$like = '%' . $this->db->esc_like( $string ) . '%';
		}

		$searches = array();
		foreach ( $columns as $column ) {
			$searches[] = $this->db->prepare( "$column LIKE %s", $like );
		}

		return '(' . implode( ' OR ', $searches ) . ')';
	}

	/**
	 * Parses and sanitizes 'orderby' keys passed to the relationship query.
	 *
	 * @since 0.1.0
	 * @access protected
	 *
	 * @param string $orderby Alias for the field to order by.
	 * @return string|false Value to used in the ORDER clause. False otherwise.
	 */
	protected function parse_orderby( $orderby ) {

		switch ( $orderby ) {
			case 'id' :
			case 'ID' :
			case 'relationship_id' :
				$parsed = 'relationship_id';
				break;
			case 'relationship__in' :
				$relationship__in = implode( ',', array_map( 'absint', $this->query_vars['relationship__in'] ) );
				$parsed           = "FIELD( {$this->db->relationships}.relationship_id, $relationship__in )";
				break;
			case 'from__in' :
				$from_in = implode( ',', array_map( 'absint', $this->query_vars['from__in'] ) );
				$parsed  = "FIELD( {$this->db->relationships}.relationship_from_id, $from_in )";
				break;
			case 'to__in' :
				$to_in  = implode( ',', array_map( 'absint', $this->query_vars['to__in'] ) );
				$parsed = "FIELD( {$this->db->relationships}.relationship_to_id, $to_in )";
				break;
			case 'from_id' :
			case 'to_id' :
			case 'type' :
			case 'slug' :
			case 'author' :
			case 'status' :
			case 'parent' :
			case 'order' :
			case 'modified' :
			case 'created' :
			case 'updated' :
				$parsed = "{$this->db->relationships}.relationship_{$orderby}";
				break;
			default :
				$parsed = false;
				break;
		}

		return $parsed;
	}

	/**
	 * Parses an 'order' query variable and cast it to 'ASC' or 'DESC' as necessary.
	 *
	 * @since 0.1.0
	 * @access protected
	 *
	 * @param string $order The 'order' query variable.
	 * @return string The sanitized 'order' query variable.
	 */
	protected function parse_order( $order ) {
		if ( ! is_string( $order ) || empty( $order ) ) {
			return 'ASC';
		}

		if ( 'ASC' === strtoupper( $order ) ) {
			return 'ASC';
		} else {
			return 'DESC';
		}
	}
}
