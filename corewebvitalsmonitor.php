<?php

namespace corewebvitalsmonitor;

/**
 * Plugin Name:       Core Web Vitals Monitor
 * Description:       Get page speed statistics for real users who visit your site.
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.4.0
 * Author:            Web Results Direct
 * Author URI:        https://wrd.studio
 * License:           GPL v3 or later
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       cwvm
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Plugin_Manager {

	const DIR     = __DIR__;
	const FILE    = __FILE__;
	const VERSION = '1.0.0';

	function __construct() {
		require_once plugin_dir_path( static::FILE ) . '/src/class-metrics-table.php';

		register_activation_hook( __FILE__, array( static::class, 'activate' ) );
		register_uninstall_hook( __FILE__, array( static::class, 'uninstall' ) );

		if ( ! is_admin() ) {
			add_action( 'init', array( $this, 'init_public' ) );
		}

		add_action( 'init', array( $this, 'init' ) );
		add_action( 'admin_init', array( $this, 'init_admin' ) );
		add_action( 'rest_api_init', array( $this, 'init_rest' ) );
	}

	function init() {
		require_once plugin_dir_path( static::FILE ) . '/src/class-assets-manager.php';
		$this->asset_manager = new Assets_Manager();

		require_once plugin_dir_path( static::FILE ) . '/src/class-admin-dashboard.php';
		$this->admin_dashboard = new Admin_Dashboard();
	}

	function init_admin() {
		 require_once plugin_dir_path( static::FILE ) . '/src/class-metabox.php';
		$this->metabox = new Metabox();
	}

	function init_public() {    }

	function init_rest() {
		require_once plugin_dir_path( static::FILE ) . '/src/class-routes-manager.php';
		$this->routes_manager = new Routes_Manager();
	}

	static function activate() {
		$metrics_table = Metrics_Table::get_instance();
		$metrics_table->create_table();

		update_option( 'corewebvitalsmonitor_version', static::VERSION );
	}

	static function uninstall() {
		$metrics_table = Metrics_Table::get_instance();
		$metrics_table->drop_table();

		delete_option( 'corewebvitalsmonitor_version' );
	}
}

function corewebvitalsmonitor() {
	global $corewebvitalsmonitor;

	if ( ! isset( $corewebvitalsmonitor ) ) {
		$corewebvitalsmonitor = new Plugin_Manager();
	}

	return $corewebvitalsmonitor;
}
corewebvitalsmonitor();
