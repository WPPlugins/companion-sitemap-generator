<?php
/*
 * Plugin Name: Companion Sitemap Generator
 * Plugin URI: https://qreative-web.com
 * Description: Easy to use XML & HTML sitemap generator and robots editor.
 * Version: 3.0.6
 * Author: Qreative-Web
 * Author URI: http://papinschipper.nl
 * Contributors: papin
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: companion-sitemap-generator
 * Domain Path: /languages/
*/

// Disable direct access
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

// Load translations
function csg_load_translations() {
	load_plugin_textdomain( 'companion-sitemap-generator', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' ); 
}
add_action( 'init', 'csg_load_translations' );

// Install db
function csg_install() {
	csg_database_creation(); // Db handle
	if (! wp_next_scheduled ( 'csg_create_sitemap' )) wp_schedule_event(time(), 'hourly', 'csg_create_sitemap'); //Set schedule

	// Get files needed for this plugin to work
	$csg_website_root = get_home_path();
	$csg_sitemap_file = $csg_website_root.'/sitemap.xml';
	$csg_robots_file = $csg_website_root.'/robots.txt';

	// Create sitemap file (if it doesn't exist)
	if ( !file_exists( $csg_sitemap_file ) ) $csg_myfile = fopen( $csg_sitemap_file, "w" );

	// Create robots file (if it doesn't exist)
	if ( !file_exists( $csg_robots_file ) ) $csg_robots_myfile = fopen( $csg_robots_file, "w" );
}
add_action('csg_create_sitemap', 'csg_sitemap');

function csg_database_creation() {

	global $wpdb;
	global $csg_db_version;

	$csg_db_version = '1.0.0';

	// Create db table
	$table_name = $wpdb->prefix . "sitemap"; 

	$sql = "CREATE TABLE $table_name (
		id INT(9) NOT NULL AUTO_INCREMENT,
		name VARCHAR(255) NOT NULL,
		onoroff VARCHAR(255) NOT NULL,
		UNIQUE KEY id (id)
	) $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );

	// Database version
	add_option( "csg_db_version", "$csg_db_version" );

	// Insert data
	csg_install_data();

	// Updating..
	$installed_ver = get_option( "csg_db_version" );
	if ( $installed_ver != $csg_db_version ) update_option( "csg_db_version", $csg_db_version );

}

// Check if database table exists before creating
function csg_check_if_exists( $whattocheck ) {

	global $wpdb;
	$table_name = $wpdb->prefix . "sitemap"; 

	$rows 	= $wpdb->get_col( "SELECT COUNT(*) as num_rows FROM $table_name WHERE name = '$whattocheck'" );
	$check 	= $rows[0];

	if( $check > 0) {
		return true;
	} else {
		return false;
	}

}

// Insert Data
function csg_install_data() {

	global $wpdb;
	$table_name = $wpdb->prefix . "sitemap"; 

	if( !csg_check_if_exists( 'exclude' ) ) $wpdb->insert( $table_name, array( 'name' => 'exclude', 'onoroff' => '' ) );

}
register_activation_hook( __FILE__, 'csg_install' );

// Clear everything
function csg_remove() {
	global $wpdb;
	$table_name = $wpdb->prefix . "sitemap"; 
	$wpdb->query( "DROP TABLE IF EXISTS $table_name" );
	wp_clear_scheduled_hook('csg_create_sitemap');
}
register_deactivation_hook(  __FILE__, 'csg_remove' );

// Update
function csg_update_db_check() {
    global $csg_db_version;
    if ( get_site_option( 'csg_db_version' ) != $csg_db_version ) {
        csg_database_creation();
    }
}
add_action( 'plugins_loaded', 'csg_update_db_check' );

// Creates the sitemap
function csg_sitemap() {

	$csg_sitemap_file = get_home_path().'/sitemap.xml';

	if ( file_exists( $csg_sitemap_file ) ) {

		if ( is_writable( $csg_sitemap_file ) ) {

				// First clear + write sitemap
				file_put_contents( $csg_sitemap_file, '' );
				file_put_contents( $csg_sitemap_file, csg_sitemap_content() );

				// Succes
				succesMSG( '<b>'.__( 'Your sitemap has been updated', 'companion-sitemap-generator' ).'</b> '.__( 'Check it out', 'companion-sitemap-generator' ).': <a href="'. get_site_url() .'/sitemap.xml" target="_blank">'. get_site_url() .'/sitemap.xml</a>' );

		} else {

			// Error when sitemap.xml is not writable
		    errorMSG( __( 'Your sitemap file is not writable', 'companion-sitemap-generator').'</p></div>' );

			$subject 		= __('Sitemap Error', 'companion-sitemap-generator');
			$message 		= __( 'Something went wrong while updating your sitemap: ', 'companion-sitemap-generator' );
			$message 		.= __( 'Your sitemap file is not writable.', 'companion-sitemap-generator' );

			wp_mail( get_option('admin_email') , $subject, $message, $headers );

		}

	} else {

		// Error if sitemap.xml doesn't exist
		errorMSG('<p>'.__( 'We weren\'t able to locate a sitemap file in your website\'s root folder. ', 'companion-sitemap-generator' ).'</p>');

		$subject 		= __('Sitemap Error', 'companion-sitemap-generator');
		$message 		= __( 'Something went wrong while updating your sitemap: ', 'companion-sitemap-generator' );
		$message 		.= __( 'We weren\'t able to locate a sitemap file in your website\'s root folder.', 'companion-sitemap-generator' );

		wp_mail( get_option('admin_email') , $subject, $message, $headers );

	}

}

// This function writes to the sitemap file
function csg_sitemap_content() {

	// Basic XML output
	$csg_sitemap_content = '<?xml version="1.0" encoding="UTF-8"?>
	<urlset xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

	// Arguments for selecting pages
	$csg_sitemap_args = array(
		'post_type' 		=> csg_get_post_types(), 
		'posts_per_page' 	=> '-1',
		'post_status' 		=> 'publish',
		'post__not_in' 		=> csg_exclude()
	);

	// The Query
	query_posts( $csg_sitemap_args );

	// The Loop
	while ( have_posts() ) : the_post();

		$csg_sitemap_content .= '
			<url>
				<loc>'. get_the_permalink() .'</loc>
			</url>
		';

	endwhile;

	// Reset Query
	wp_reset_query();

	$csg_sitemap_content .= '</urlset>';

	// Return sitemap-string but first filter any text containing illegal named entities
	return ent2ncr( $csg_sitemap_content );

}

// Get all post types
function csg_get_post_types() {

	// Get all exisiting default post types
	$args = array(
	   'public'   => true,
	   '_builtin' => true
	);

	// Get all exisiting custom post types
	$args2 = array(
	   'public'   => true,
	   '_builtin' => false
	);

	$output 	= 'names'; // names or objects, note names is the default
	$operator 	= 'and'; // 'and' or 'or'

	$post_types 	= get_post_types( $args, $output, $operator ); 
	$post_types2 	= get_post_types( $args2, $output, $operator ); 

	$post_type_array = array();

	foreach ( $post_types  as $post_type ) array_push( $post_type_array , $post_type );
	foreach ( $post_types2  as $post_type ) array_push( $post_type_array , $post_type );

	return $post_type_array;

}

// Custom functions for handling messages
function succesMSG( $content ) {
	echo '<div id="message" class="updated"><p>'.$content.'</p></div>';
}
function errorMSG( $content ) {
	echo '<div id="message" class="error"><p>'.$content.'</p></div>';
}

// Add to menu
function csg_menu_items(){

	add_submenu_page( 'tools.php', __('Sitemap', 'companion-sitemap-generator'), __('Sitemap', 'companion-sitemap-generator'), 'manage_options', 'csg-sitemap', 'csg_dashboard' );
	add_submenu_page( 'tools.php', __('Robots.txt', 'companion-sitemap-generator'), __('Robots.txt', 'companion-sitemap-generator'), 'manage_options', 'csg-robots', 'csg_robots_dashboard' );

}
add_action( 'admin_menu', 'csg_menu_items' );


// Sitemap dashboard
function csg_dashboard() {
	include_once( 'sitemap.php' );
}

// Robots dashboard
function csg_robots_dashboard() {
	include_once( 'robots.php' );
}

// Create widget
include_once( 'widget.php' );

// Add generate sitemap link on plugin page
function csg_settings_link( $links ) { 
	$settings_link = '<a href="tools.php?page=csg-sitemap">'.__('Settings', 'companion-sitemap-generator' ).'</a>'; 
	array_unshift($links, $settings_link); 
	return $links; 
}
$plugin = plugin_basename(__FILE__); 
add_filter("plugin_action_links_$plugin", 'csg_settings_link' );

// Load admin styles
function load_csg_styles( $hook ) {

        if( $hook != 'tools_page_csg-sitemap' ) return;
        wp_enqueue_style( 'csg_admin_styles', plugins_url('backend/style.css', __FILE__) );

}
add_action( 'admin_enqueue_scripts', 'load_csg_styles' );

// Get PAGES for html sitemap
function html_pages() {

	$pages = '<h2>'.__( "Pages", "companion-sitemap-generator" ).'</h2>
	<ul>';

	$csg_sitemap_args = array( 
		'order' => 'asc', 
		'post_type' => 'page', 
		'posts_per_page' => '-1', 
		'post_status' => 'publish', 
		'post__not_in' => csg_exclude()
	);

	query_posts( $csg_sitemap_args );

	while ( have_posts() ) {
		the_post(); 
		$pages .= '<li><a href="'. get_the_permalink() .'" title="'. get_the_title() .'">'. get_the_title() .'</a></li>';
	}

	wp_reset_query();

	$pages .= '</ul>';

	return $pages;

}

// Get POSTS for html sitemap
function html_posts() {

	$posts = '<h2>'.__( "Posts", "companion-sitemap-generator" ).'</h2>
	<ul>';

	$csg_sitemap_args = array( 
		'order' => 'asc', 
		'post_type' => 'post', 
		'posts_per_page' => '-1', 
		'post_status' => 'publish', 
		'post__not_in' => csg_exclude()
	);


	query_posts( $csg_sitemap_args );

	while ( have_posts() ) {
		the_post(); 
		$posts .= '<li><a href="'. get_the_permalink() .'" title="'. get_the_title() .'">'. get_the_title() .'</a></li>';
	}

	wp_reset_query();

	$posts .= '</ul>';

	return $posts;

}

// Get all other post types for html sitemap
function html_posttypes() {

	$args2 = array(
	   'public'   => true,
	   '_builtin' => false
	);

	$output 	= 'names'; // names or objects, note names is the default
	$operator 	= 'and'; // 'and' or 'or'

	$post_types2 	= get_post_types( $args2, $output, $operator ); 


	foreach ( $post_types2  as $post_type ) {

		$posts = '<h2>'.__( "Post type", "companion-sitemap-generator" ).': '.$post_type.'</h2>
		<ul>';

		$csg_sitemap_args = array( 
			'order' => 'asc', 
			'post_type' => $post_type, 
			'posts_per_page' => '-1', 
			'post_status' => 'publish', 
			'post__not_in' => csg_exclude()
		);


		query_posts( $csg_sitemap_args );

		while ( have_posts() ) {
			the_post(); 
			$posts .= '<li><a href="'. get_the_permalink() .'" title="'. get_the_title() .'">'. get_the_title() .'</a></li>';
		}

		wp_reset_query();

		$posts .= '</ul>';

	}

	return $posts;

}

// Create shortcode
function htmlsitemap() {

	$csg_sitemap_content = html_pages();
	$csg_sitemap_content .= html_posts();
	$csg_sitemap_content .= html_posttypes();

	return $csg_sitemap_content;
}
add_shortcode( 'html-sitemap' , 'htmlsitemap' );


// Exclude these posts from the sitemap
function csg_exclude() {

	global $wpdb;
	$table_name 	= $wpdb->prefix . "sitemap"; 
	$config 		= $wpdb->get_results( "SELECT * FROM $table_name WHERE name = 'exclude'");

	$list 			= $config[0]->onoroff;
	$list 			= explode( ", ", $list );
	$returnList 	= array();

	foreach ( $list as $key ) array_push( $returnList, $key );
	
	return $returnList;

}

function change_footer_admin ( ) {  
	echo 'Keep your WordPress site secure and up-to-date with <a href="https://wordpress.org/plugins/companion-auto-update" target="_blank">Companion Auto Update</a>.'; 
}  

if( $_GET['page'] == 'csg-sitemap' ) {
	add_filter('admin_footer_text', 'change_footer_admin');
} 


?>