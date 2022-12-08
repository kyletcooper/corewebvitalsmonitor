<?php

namespace corewebvitalsmonitor;

use WP_Date_Query;

class Metrics_Table {

	private static $instance = null;

	private string $table_name;

	private array $columns = array(
		'score_id',
		'metric',
		'value',
		'url',
		'connection_speed',
		'created_at',
	);

	private array $metrics = array( 'CLS', 'FCP', 'FID', 'INP', 'LCP', 'TTFB' );

	private function __construct() {
		global $wpdb;

		$this->table_name = $wpdb->base_prefix . 'corewebvitalscores';
	}

	public static function get_instance(): self {
		if ( self::$instance === null ) {
			self::$instance = new Metrics_Table();
		}

		return self::$instance;
	}

	public function create_table(): void {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		$table_name      = $this->table_name;

		$sql = "CREATE TABLE IF NOT EXISTS `$table_name` (
            score_id INT NOT NULL AUTO_INCREMENT,
            metric VARCHAR(8) NOT NULL,
            value FLOAT unsigned NOT NULL,
            url TEXT(255) NOT NULL,
            connection_speed FLOAT NOT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

            PRIMARY KEY  (score_id)
        ) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	public function drop_table(): void {
		global $wpdb;
		$table_name = $this->table_name;
		$sql        = "DROP TABLE IF EXISTS $table_name";
		$wpdb->query( $sql );
	}

	private function standardise_url( $url ): string {
		$url_parts = wp_parse_url( $url );

		$scheme  = array_key_exists( 'scheme', $url_parts ) ? $url_parts['scheme'] : 'https';
		$host    = $url_parts['host'];
		$path    = array_key_exists( 'path', $url_parts ) ? $url_parts['path'] : '';
		$new_url = $scheme . '://' . $host . $path;

		return trailingslashit( $new_url );
	}

	public function insert( array $data ): bool {
		global $wpdb;

		if ( ! $data['metric'] || ! in_array( $data['metric'], $this->metrics, true ) ) {
			new \Exception( __( 'metric is required and must in list.', 'corewebvitalsmonitor' ) );
			return false;
		}

		if ( ! $data['value'] || ! is_float( $data['value'] ) ) {
			new \Exception( __( 'value is required and must be a float.', 'corewebvitalsmonitor' ) );
			return false;
		}

		if ( ! $data['url'] || ! filter_var( $data['url'], FILTER_VALIDATE_URL ) ) {
			new \Exception( __( 'url is required and must be a valid URL.', 'corewebvitalsmonitor' ) );
			return false;
		}

		if ( ! $data['connection_speed'] || ! is_numeric( $data['connection_speed'] ) ) {
			new \Exception( __( 'connection_speed is required and must be a float.', 'corewebvitalsmonitor' ) );
			return false;
		}

		$data['url'] = $this->standardise_url( $data['url'] );

		return (bool) $wpdb->insert( $this->table_name, $data );
	}


	private function _validate_column( string $column, string $fallback = 'score_id' ) {
		if ( in_array( $column, $this->columns, true ) ) {
			return "$this->table_name.$column";
		}

		if ( in_array( $fallback, $this->columns, true ) ) {
			return "$this->table_name.$fallback";
		}

		return 'ERROR';
	}

	private function _parse_query_equals_clause( string $column_name, string $find ) {
		global $wpdb;

		$column_name = $this->_validate_column( $column_name, 'metric' );
		$clause      = " AND $column_name = %s";

		return $wpdb->prepare($clause, $find); // phpcs:ignore -- This sql variable is strictly validated with a whitelist of column names.
	}

	private function _parse_query_in_clause( string $column_name, array $in_values ) {
		global $wpdb;

		$column_name = $this->_validate_column( $column_name, 'metric' );

		$placeholders = str_repeat( '%s, ', count( $in_values ) );
		$placeholders = substr( $placeholders, 0, -2 ); // Remove last comma.
		$clause       = " AND $column_name IN ($placeholders)";

		return $wpdb->prepare($clause, $in_values); // phpcs:ignore -- This sql variable is strictly validated with a whitelist of column names.
	}

	private function _parse_query_date_clause( string $column_name, string $start_date, string $end_date ) {
		$column_name = $this->_validate_column( $column_name, 'created_at' );

		$date_query = new WP_Date_Query(
			array(
				array(
					'column' => $column_name,
					'before' => $end_date,
					'after'  => $start_date,
				),
			)
		);

		return $date_query->get_sql();
	}

	private function _parse_query_like_clause( string $column_name, string $like ) {
		global $wpdb;

		$column_name   = $this->_validate_column( $column_name, 'url' );
		$like_wildcard = '%' . $wpdb->esc_like( $like ) . '%';
		$clause        = " AND `$column_name` LIKE %s";

		return $wpdb->prepare($clause, $like_wildcard); // phpcs:ignore -- This sql variable is strictly validated with a whitelist of column names.
	}

	private function _parse_query_clauses( array $args, bool $include_count = true, bool $include_order = false ) {
		global $wpdb;
		$where = array();

		// Parse args
		$args = wp_parse_args(
			$args,
			array(
				'date_start' => date( 'c', strtotime( '-28 days' ) ),
				'date_end'   => date( 'c' ),
				'metric'     => array( 'CLS', 'FCP', 'FID', 'INP', 'LCP', 'TTFB' ),
				'url'        => '',
				'count'      => 10,
				'orderby'    => 'score_id',
				'order'      => 'ASC',
			)
		);

		if ( ! is_array( $args['metric'] ) ) {
			$args['metric'] = array( $args['metric'] );
		}

		if ( is_nan( $args['count'] ) ) {
			$args['count'] = 250;
		}
		if ( $args['count'] > 500 ) {
			$args['count'] = 500;
		}

		$args['orderby'] = $this->_validate_column( $args['orderby'], 'score_id' );

		$args['order'] = strtoupper( $args['order'] );
		if ( $args['order'] !== 'ASC' && $args['order'] !== 'DESC' ) {
			$args['order'] = 'ASC';
		}

		$where[] = $this->_parse_query_date_clause( 'created_at', $args['date_start'], $args['date_end'] );
		$where[] = $this->_parse_query_in_clause( 'metric', $args['metric'] );

		if ( $args['url'] ) {
			$args['url'] = $this->standardise_url( $args['url'] );
			$where[]     = $this->_parse_query_equals_clause( 'url', $args['url'] );
		}

		$query = ' WHERE 1=1';

		foreach ( $where as $w ) {
			$query .= $w;
		}

		if ( $include_count ) {
			$query .= $wpdb->prepare( ' LIMIT %d', $args['count'] );
		}

		if ( $include_order ) {
			// This has been thoroughly sanitized to limited options
			$query .= ' ORDERBY ' . $args['orderby'] . ' ' . $args['order'];
		}

		return $query;
	}

	public function select( array $args ) {
		global $wpdb;

		$clauses = $this->_parse_query_clauses( $args );
		$sql     = "SELECT * FROM $this->table_name $clauses";
		return $wpdb->get_results( $sql );
	}

	public function select_average( array $args ): float {
		global $wpdb;

		$clauses = $this->_parse_query_clauses( $args, false, false );
		$sql     = "SELECT AVG($this->table_name.value) FROM $this->table_name $clauses";
		$avg     = $wpdb->get_var( $sql );

		if ( ! $avg ) {
			$avg = 0;
		}

		return (float) $avg;
	}

	public function select_count( array $args ): float {
		global $wpdb;

		$clauses = $this->_parse_query_clauses( $args, false, false );
		$sql     = "SELECT COUNT($this->table_name.score_id) FROM $this->table_name $clauses";
		return (float) $wpdb->get_var( $sql );
	}

	public function select_plot( array $args ): array {
		error_reporting( 1 );
		global $wpdb;

		$clauses = $this->_parse_query_clauses( $args, false, false );
		$sql     = "SELECT ROUND($this->table_name.value, -1) as `value`, COUNT($this->table_name.score_id) as `count` FROM $this->table_name $clauses GROUP BY ROUND($this->table_name.value, -1)";
		// return $sql;
		return $wpdb->get_results( $sql );
	}
}
