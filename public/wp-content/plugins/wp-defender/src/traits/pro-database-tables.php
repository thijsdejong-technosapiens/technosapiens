<?php
/**
 * Pro-only database table creation methods.
 *
 * @package WP_Defender\Traits
 */

namespace WP_Defender\Traits;

/**
 * Provides database table creation for Pro-only features (Audit Logging, Quarantine).
 */
trait Pro_Database_Tables {

	/**
	 * Table name for quarantine.
	 *
	 * @var string
	 */
	private $quarantine_table = 'defender_quarantine';

	/**
	 * Table name for scan item.
	 *
	 * @var string
	 */
	private $scan_item_table = 'defender_scan_item';

	/**
	 * Check is all quarantine dependent table is having storage engine InnoDB.
	 *
	 * @return bool True if all dependent table is InnoDB else false.
	 */
	private function is_quarantine_dependent_tables_innodb(): bool {
		global $wpdb;

		$tables      = array( $wpdb->users, $wpdb->base_prefix . $this->scan_item_table );
		$total_table = count( $tables );

		return $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				"SELECT COUNT(`ENGINE`) = %d FROM information_schema.TABLES WHERE TABLE_SCHEMA = %s AND `ENGINE` = %s AND TABLE_NAME IN ( '{$wpdb->users}', '{$wpdb->base_prefix}defender_scan_item' );",
				$total_table,
				$wpdb->dbname,
				'innodb',
			)
		) === '1';
	}

	/**
	 * Creates the audit log table used for caching audit events.
	 */
	protected function create_table_audit_log(): void {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		// Audit log table. Though our data mainly store on API side, we will need a table for caching.
		$sql = "CREATE TABLE IF NOT EXISTS {$wpdb->base_prefix}defender_audit_log (
 `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
 `timestamp` int NOT NULL,
 `event_type` varchar(255) NOT NULL,
 `action_type` varchar(255) NOT NULL,
 `site_url` varchar(255) NOT NULL,
 `user_id` int NOT NULL,
 `context` varchar(255) NOT NULL,
 `ip` varchar(45) NOT NULL,
 `msg` varchar(255) NOT NULL,
 `blog_id` int NOT NULL,
 `synced` int NOT NULL,
 `ttl` int NOT NULL,
 PRIMARY KEY  (`id`),
 KEY `event_type` (`event_type`),
 KEY `action_type` (`action_type`),
 KEY `user_id` (`user_id`),
 KEY `context` (`context`),
 KEY `ip` (`ip`)
) $charset_collate;";
		$wpdb->query( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}

	/**
	 * Creates the quarantine table if it doesn't exist.
	 *
	 * @return void
	 */
	public function create_table_quarantine(): void {
		global $wpdb;

		// Define table names and charset.
		$quarantine_table = $wpdb->base_prefix . $this->quarantine_table;
		$scan_item_table  = $wpdb->base_prefix . $this->scan_item_table;
		$charset_collate  = $wpdb->get_charset_collate();
		$unique_id        = uniqid( $wpdb->prefix );

		$common_columns = <<<'SQL'
		`id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
		`defender_scan_item_id` int UNSIGNED DEFAULT NULL,
		`file_hash` char(53) NOT NULL,
		`file_full_path` text NOT NULL,
		`file_original_name` tinytext NOT NULL,
		`file_extension` varchar(16) DEFAULT NULL,
		`file_mime_type` varchar(64) DEFAULT NULL,
		`file_rw_permission` smallint UNSIGNED DEFAULT NULL,
		`file_owner` varchar(255) DEFAULT NULL,
		`file_group` varchar(255) DEFAULT NULL,
		`file_version` varchar(32) DEFAULT NULL,
		`file_category` tinyint UNSIGNED DEFAULT 0,
		`file_modified_time` datetime NOT NULL,
		`source_slug` varchar(255) NOT NULL,
		`created_time` datetime NOT NULL,
		`created_by` bigint UNSIGNED DEFAULT NULL,
		PRIMARY KEY (`id`)
		SQL;

		// Define key names.
		$scan_item_key  = "{$unique_id}_defender_scan_item_id";
		$created_by_key = "{$unique_id}_created_by";

		// Build the SQL statement based on the storage engine.
		if ( $this->is_quarantine_dependent_tables_innodb() ) {
			$sql = <<<SQL
			CREATE TABLE IF NOT EXISTS `{$quarantine_table}` (
				$common_columns,
				CONSTRAINT `{$scan_item_key}`
				FOREIGN KEY (`defender_scan_item_id`) REFERENCES {$scan_item_table}(`id`)
				ON UPDATE CASCADE ON DELETE SET NULL,
				CONSTRAINT `{$created_by_key}`
				FOREIGN KEY (`created_by`) REFERENCES {$wpdb->users}(`ID`)
				ON UPDATE CASCADE ON DELETE SET NULL
			) {$charset_collate};
			SQL;
		} else {
			$sql = <<<SQL
			CREATE TABLE IF NOT EXISTS `{$quarantine_table}` (
				$common_columns,
				KEY `{$scan_item_key}` (`defender_scan_item_id`),
				KEY `{$created_by_key}` (`created_by`)
			) {$charset_collate};
			SQL;
		}

		// Execute the SQL query.
		$wpdb->query( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}
}