<?php
// Dynamic button text for widget
function csg_dynamic_button() {

	$csg_website_root = get_home_path();
	$csg_sitemap_file = $csg_website_root.'/sitemap.xml';
	$emptySM = file_get_contents( $csg_sitemap_file ); 

	if( ( strlen($emptySM) == 0 ) ) $text 	= __( 'Generate Sitemap', 'companion-sitemap-generator' );
	else $text 								= __( 'Update Sitemap', 'companion-sitemap-generator' );

	return $text;

}

// Add a widget to the dashboard.
function csg_sitemap_gen_add_dashboard_widget() {
	if ( current_user_can( 'manage_options' ) ) wp_add_dashboard_widget( 'csg-sitemap-gen', __('Sitemap Generator', 'companion-sitemap-generator'), 'csg_sitemap_gen_dashboard_widget' );	
}
add_action( 'wp_dashboard_setup', 'csg_sitemap_gen_add_dashboard_widget' );

// Create the function to output the contents of our Dashboard Widget.
function csg_sitemap_gen_dashboard_widget() {

	$csg_sitemap_file 	= get_home_path().'/sitemap.xml';
	$emptySM 			= file_get_contents( $csg_sitemap_file ); 

	if( ( strlen($emptySM) == 0 ) ) _e('For some reason your sitemap is still empty, by clicking the button below your sitemap will be generated.', 'companion-sitemap-generator');
	else _e('We update your sitemap every hour, but in case you\'d like to update it manually you can do that here.', 'companion-sitemap-generator');

	if( isset( $_POST['csg_generate'] ) ) csg_sitemap();

	echo '<form method="POST">';
	echo '<p><button type="submit" name="csg_generate" class="button button-primary">'.csg_dynamic_button().'</button> <a href="'.get_site_url().'/sitemap.xml"  class="button button-alt" target="_blank">'.__( "View sitemap", "companion-sitemap-generator" ).'</a></p>';
	echo '</form>';
}
?>