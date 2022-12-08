<?php

namespace corewebvitalsmonitor;

class Admin_Dashboard {

	function __construct() {
		add_action( 'wp_dashboard_setup', array( $this, 'add_dashboard_widget' ) );
	}

	function add_dashboard_widget() {
		wp_add_dashboard_widget( 'corewebvitalsmonitor', __( 'Core Web Vitals Monitor', 'cwvm' ), array( $this, 'render_dashboard_widget' ) );
	}

	function render_dashboard_widget() {
		echo '<core-web-vitals>Loading...</core-web-vitals>';
	}
}
