<?php
/*
Plugin Name: Sharekoube
Description: Extend Sharedaddy
Version: 0.8
Author: nemooon
Author URI: http://nemooon.jp/
Plugin URI: http://nemooon.jp/plugins/sharekoube/
*/

define( 'SHAREKOUBE_PLUGIN_VERSION', '0.8' );

require_once plugin_dir_path( __FILE__ ) . 'sharekoube-admin.php';

function sharekoube_init() {
	load_plugin_textdomain( 'sharekoube', false, basename( dirname( __FILE__ ) ).'/languages/' );
	
	if ( get_option( 'sharedaddy_disable_resources' ) ) {
		remove_action( 'wp_head', 'sharekoube_add_header', 1 );
	}
}

function sharekoube_include_sharing_sources() {
	if ( class_exists( 'Sharing_Advanced_Source' ) )
		include_once plugin_dir_path( __FILE__ ) . 'sharing-sources.php';
}

function sharekoube_admin_head() {
	wp_enqueue_style( 'sharekoube', plugin_dir_url( __FILE__ ) . 'admin-sharekoube.css', false, SHAREKOUBE_PLUGIN_VERSION );
}

function sharekoube_add_header() {
	$sharer = new Sharing_Service();
	$enabled = $sharer->get_blog_services();

	if ( count( $enabled['all'] ) > 0 )
		wp_enqueue_style( 'sharekoube', plugin_dir_url( __FILE__ ) .'sharekoube.css' );
}

function sharekoube_sharing_services( $services ) {
	$services['twitter']         = 'Sharekoube_Twitter';
	$services['mixi-check']      = 'Sharekoube_Mixi_Check';
	$services['hatena-bookmark'] = 'Sharekoube_Hatena_Bookmark';
	$services['evernote']        = 'Sharekoube_Evernote';
	$services['google-plus']     = 'Sharekoube_Google_Plus';
	$services['google-buzz']     = 'Sharekoube_Google_Buzz';
	return $services;
}

if ( version_compare( phpversion(), '5.0', '>=' ) ) {
	add_action( 'init', 'sharekoube_init' );
	add_action( 'init', 'sharekoube_include_sharing_sources', 20 );
	add_action( 'admin_init', 'sharekoube_admin_head' );
	add_action( 'wp_head', 'sharekoube_add_header', 1 );
	add_filter( 'sharing_services', 'sharekoube_sharing_services' );
}
