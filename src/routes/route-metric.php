<?php

namespace corewebvitalsmonitor;

class Route_Metric {

	public string $namespace = 'corewebvitalsmonitor/v1';
	public string $base      = 'metric';

	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->base . '/',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_metric' ),
					'permission_callback' => '__return_true',
					'args'                => $this->get_metric_args(),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->base . '/average',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_metric_average' ),
					'permission_callback' => '__return_true',
					'args'                => $this->get_metric_args(),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->base . '/plot',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_metric_plot' ),
					'permission_callback' => '__return_true',
					'args'                => $this->get_metric_args(),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->base . '/count',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_metric_count' ),
					'permission_callback' => '__return_true',
					'args'                => $this->get_metric_args(),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->base . '/',
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_metric' ),
					'permission_callback' => '__return_true',
					'args'                => $this->create_metric_args(),
				),
			)
		);
	}


	// Get Metrics

	public function get_metric_args() {
		return array(
			'date_start' => array(
				'type'    => 'string',
				'format'  => 'date-time',
				'default' => date( 'c', strtotime( '-28 days' ) ),
			),

			'date_end'   => array(
				'type'    => 'string',
				'format'  => 'date-time',
				'default' => date( 'c' ),
			),

			'metric'     => array(
				'type'    => 'array',
				'items'   => array(
					'type' => 'string',
					'enum' => array(
						'CLS',
						'FCP',
						'FID',
						'INP',
						'LCP',
						'TTFB',
					),
				),
				'default' => array( 'CLS', 'FCP', 'FID', 'INP', 'LCP', 'TTFB' ),
			),

			'url'        => array(
				'type'    => 'string',
				'format'  => 'uri',
				'default' => '',
			),

			'count'      => array(
				'type'    => 'integer',
				'minimum' => 0,
				'maximum' => 500,
				'default' => 250,
			),

			'orderby'    => array(
				'type'    => 'string',
				'enum'    => array(
					'score_id',
					'metric',
					'value',
					'url',
					'connection_speed',
					'created_at',
				),
				'default' => 'score_id',
			),

			'order'      => array(
				'type'    => 'string',
				'num'     => array(
					'ASC',
					'DESC',
				),
				'default' => 'ASC',
			),
		);
	}

	public function get_metric( \WP_REST_Request $request ) {
		$metrics_table = Metrics_Table::get_instance();
		return $metrics_table->select( $request->get_params() );
	}

	public function get_metric_average( \WP_REST_Request $request ) {
		$metrics_table = Metrics_Table::get_instance();
		return $metrics_table->select_average( $request->get_params() );
	}

	public function get_metric_plot( \WP_REST_Request $request ) {
		$metrics_table = Metrics_Table::get_instance();
		return $metrics_table->select_plot( $request->get_params() );
	}

	public function get_metric_count( \WP_REST_Request $request ) {
		$metrics_table = Metrics_Table::get_instance();
		return $metrics_table->select_count( $request->get_params() );
	}


	// Create Metric

	public function create_metric_args() {
		return array(
			'metric'           => array(
				'required' => true,
				'type'     => 'string',
				'enum'     => array(
					'CLS',
					'FCP',
					'FID',
					'INP',
					'LCP',
					'TTFB',
				),
			),

			'value'            => array(
				'required' => true,
				'type'     => 'number',
			),

			'url'              => array(
				'required' => true,
				'type'     => 'string',
				'format'   => 'uri',
			),

			'connection_speed' => array(
				'required' => true,
				'type'     => 'number',
			),
		);
	}

	public function create_metric( \WP_REST_Request $request ) {
		$metrics_table = Metrics_Table::get_instance();

		return $metrics_table->insert( $request->get_params() );
	}
}
