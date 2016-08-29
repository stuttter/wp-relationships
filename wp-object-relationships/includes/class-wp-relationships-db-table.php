<?php

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Main WP Object Relationships class
 *
 * This class facilitates the following functionality:
 *
 * - Creates & maintains the `wp_relationship_typees` table
 * - Deletes all aliases for sites when sites are deleted
 * - Adds `wp_relationship_typees` to the main database object when appropriate
 *
 * @since 0.1.0
 */
final class WP_Relationships_DB {

	/**
	 * @var string Plugin version
	 */
	public $version = '0.1.0';

	/**
	 * @var string Database version
	 */
	public $db_version = 201608290001;

	/**
	 * @var string Database version key
	 */
	public $db_version_key = 'wpdb_object_relationships_version';

	/**
	 * @var object Database object (usually $GLOBALS['wpdb'])
	 */
	private $db = false;

	/** Methods ***************************************************************/

	/**
	 * Hook into queries, admin screens, and more!
	 *
	 * @since 0.1.0
	 */
	public function __construct() {

		// Activation hook
		register_activation_hook( __FILE__, array( $this, 'activate' ) );

		// Setup plugin
		$this->db = $GLOBALS['wpdb'];

		// Force table on to the global database object
		add_action( 'init',           array( $this, 'add_table_to_db_object' ) );
		add_action( 'switch_to_blog', array( $this, 'add_table_to_db_object' ) );

		// Check if DB needs upgrading
		if ( is_admin() ) {
			add_action( 'admin_init', array( $this, 'admin_init' ) );
		}
	}

	/**
	 * Administration area hooks
	 *
	 * @since 0.1.0
	 */
	public function admin_init() {
		$this->upgrade_database();
	}

	/**
	 * Modify the database object and add the table to it
	 *
	 * This is necessary to do directly because WordPress does have a mechanism
	 * for manipulating them safely. It's pretty fragile, but oh well.
	 *
	 * @since 0.1.0
	 */
	public function add_table_to_db_object() {
		$this->db->relationships    = "{$this->db->get_blog_prefix()}relationships";
		$this->db->relationshipmeta = "{$this->db->get_blog_prefix()}relationshipmeta";
		$this->db->tables[]         = "relationships";
		$this->db->tables[]         = "relationshipmeta";
	}

	/**
	 * Install this plugin on a specific site
	 *
	 * @since 0.1.0
	 *
	 * @param int $site_id
	 */
	public function install() {
		$this->upgrade_database();
	}

	/**
	 * Activation hook
	 *
	 * Handles both single & multi site installations
	 *
	 * @since 0.1.0
	 *
	 * @param   bool    $network_wide
	 */
	public function activate() {
		$this->install();
	}

	/**
	 * Create the database table
	 *
	 * @since 0.1.0
	 */
	private function upgrade_database() {

		// Old version
		$old_version = get_option( $this->db_version_key );

		// The main column alter
		if ( version_compare( (int) $old_version, $this->db_version, '>=' ) ) {
			return;
		}

		// Create term table
		$this->create_tables();

		// Update the DB version
		update_option( $this->db_version_key, $this->db_version );
	}

	/**
	 * Create the table
	 *
	 * @since 0.1.0
	 */
	private function create_tables() {

		// Vars
		$sql              = array();
		$max_index_length = 191;
		$charset_collate  = '';

		if ( ! empty( $this->db->charset ) ) {
			$charset_collate = "DEFAULT CHARACTER SET {$this->db->charset}";
		}

		if ( ! empty( $this->db->collate ) ) {
			$charset_collate .= " COLLATE {$this->db->collate}";
		}

		// Check for `dbDelta`
		if ( ! function_exists( 'dbDelta' ) ) {
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		}

		// Relationships
		$sql[] = "CREATE TABLE {$this->db->relationships} (
			relationship_id bigint(20) NOT NULL auto_increment,
			relationship_author bigint(20) NOT NULL default '0',
			relationship_name varchar(200) NOT NULL default '',
			relationship_content longtext NOT NULL,
			relationship_type varchar(20) NOT NULL default 'active',
			relationship_status varchar(20) NOT NULL default 'active',
			relationship_parent bigint(20) NOT NULL default '0',
			relationship_order bigint(20) NOT NULL default '0',
			relationship_created datetime NOT NULL default '0000-00-00 00:00:00',
			relationship_modified datetime NOT NULL default '0000-00-00 00:00:00',
			from_id bigint(20) NOT NULL,
			fromtype varchar(20) NOT NULL,
			to_id bigint(20) NOT NULL,
			to_type varchar(20) NOT NULL,
			PRIMARY KEY (relationship_id),
			KEY relationship_id (relationship_id,from_id,from_type(20),relationship_status(20)),
			KEY relationship_status (relationship_status(20)),
			KEY from_id (from_type),
			KEY from_type (from_type(20))
		) {$charset_collate};";

		// Relationship meta
		$sql[] = "CREATE TABLE {$this->db->relationshipmeta} (
			id bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
			relationship_id bigint(20) NOT NULL,
			meta_key varchar(255) DEFAULT NULL,
			meta_value longtext DEFAULT NULL,
			KEY relationship_id (relationship_id),
			KEY meta_key (meta_key({$max_index_length}))
		) {$charset_collate};";

		dbDelta( $sql );

		// Make doubly sure the global database object is modified
		$this->add_table_to_db_object();
	}
}

/**
 * Load the DB as early as possible, but after WordPress core is included
 *
 * @since 0.1.0
 */
function wp_object_relationships_db() {
	new WP_Relationships_DB();
}
add_action( 'plugins_loaded', 'wp_object_relationships_db', -PHP_INT_MAX );
