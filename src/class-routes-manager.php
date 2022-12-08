<?php

namespace corewebvitalsmonitor;

class Routes_Manager {

	function __construct() {
		require_once plugin_dir_path( Plugin_Manager::FILE ) . 'src/routes/route-metric.php';

		$route_metric = new Route_Metric();
		$route_metric->register_routes();
	}
}
