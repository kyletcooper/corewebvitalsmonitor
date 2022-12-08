<?php

namespace corewebvitalsmonitor;

class Metabox {


	public function __construct() {
		 add_action( 'add_meta_boxes', array( $this, 'register_metaboxes' ) );
	}

	public function register_metaboxes(): void {
		add_meta_box(
			'corewebvitalsmonitor',
			__( 'Core Web Vitals', 'popbot' ),
			array( $this, 'render' ),
			null,
			'side',
			'high'
		);
	}

	public function render(): void {
		$url = get_the_permalink();
		echo '<core-web-vitals url="' . esc_url( $url ) . '"></core-web-vitals>';
	}
}
