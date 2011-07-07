<?php

class Sharekoube_Admin {
	
	public function __construct() {
	}
	
	public function sharing_head() {
		wp_enqueue_script( 'sharekoube-js', plugin_dir_path( __FILE__ ) . 'admin-sharing.js', array( 'sharing-js' ), 1 );
		wp_enqueue_style( 'sharekoube', plugin_dir_path( __FILE__ ) . 'admin-sharing.css', false, WP_SHARING_PLUGIN_VERSION );

		add_thickbox();
	}
}

function sharekoube_admin_init() {
	global $sharing_admin, $sharekoube_admin;

	if ( isset( $sharing_admin ) && $sharing_admin instanceof Sharing_Admin ) {
		$sharekoube_admin = new Sharekoube_Admin();
	} else {
		$sharekoube_admin = null;
	}
}

add_action( 'init', 'sharekoube_admin_init', 20 );
