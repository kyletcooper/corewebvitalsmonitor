<?php

namespace corewebvitalsmonitor;

class Assets_Manager
{

	function __construct()
	{
		add_action('wp_enqueue_scripts', array($this, 'enqueue_public'));
		add_action('admin_enqueue_scripts', array($this, 'enqueue_admin'));
		add_action('enqueue_block_editor_assets', array($this, 'enqueue_block_editor'));
	}

	function enqueue_public()
	{
		$ver = Plugin_Manager::VERSION;

		wp_enqueue_script('cwvm-monitor', plugin_dir_url(Plugin_Manager::FILE) . 'assets/scripts/dist/public.js', array(), $ver, true);

		wp_localize_script(
			'cwvm-monitor',
			'CWVM',
			array(
				'rest_url'   => rest_url(),
				'rest_nonce' => wp_create_nonce('wp_rest'),
				'admin_url'  => admin_url(),
			)
		);

		$this->set_script_loading_mode('cwvm-monitor', 'async');
	}

	function enqueue_admin()
	{
		$ver = Plugin_Manager::VERSION;

		wp_enqueue_script('cwvm-metabox', plugin_dir_url(Plugin_Manager::FILE) . 'assets/scripts/dist/admin.js', array(), $ver, false);

		wp_localize_script(
			'cwvm-metabox',
			'CWVM',
			array(
				'rest_url'   => rest_url(),
				'rest_nonce' => wp_create_nonce('wp_rest'),
				'admin_url'  => admin_url(),
			)
		);
	}

	function enqueue_block_editor()
	{
	}

	function set_script_loading_mode($handle, $loading_mode = 'sync')
	{
		add_filter(
			'script_loader_tag',
			function ($tag, $found_handle, $src) use ($handle, $loading_mode) {
				if ($found_handle === $handle) {
					$loading_mode = esc_attr($loading_mode);
					return str_replace('></script>', " $loading_mode></script>", $tag);
				}

				return $tag;
			},
			10,
			3
		);
	}
}
