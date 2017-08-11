<?php

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

$output = 'names'; // names or objects, note names is the default
$operator = 'and'; // 'and' or 'or'

$post_types = get_post_types( $args, $output, $operator ); 
$post_types2 = get_post_types( $args2, $output, $operator ); 

if( isset( $_POST['submit'] ) ) {

	global $wpdb;
	$table_name = $wpdb->prefix . "sitemap";

	$excludeposts 	= '';
	$excludeCounter = 0;

	foreach ( $_POST['post'] as $key ) {
		$excludeposts .= $key.', ';
		$excludeCounter++;
	}

	$wpdb->query( " UPDATE $table_name SET onoroff = '$excludeposts' WHERE name = 'exclude' " );
	echo '<div id="message" class="updated"><p><b>'.__('Succes', 'companion-sitemap-generator').' &ndash;</b> '.sprintf( esc_html__( '%1$s posts will no longer be included in your sitemap', 'companion-sitemap-generator' ), $excludeCounter ).'.</p></div>';
}

?>

<h2 class='screen-reader-text'><?php _e('Available post types:', 'companion-sitemap-generator');?></h2>

<ul class="subsubsub">
	<?php
	if( !isset( $_GET['ptt'] ) ) {
		$ptt = 'post';
	} else {
		$ptt = $_GET['ptt'];
	}
	foreach ( $post_types  as $post_type ) {
		if( $post_type != 'attachment' ) {
			echo '<li><a data-table="table-'.$post_type.'" class="showtable table-'.$post_type.'';
			if( $post_type == 'post' ) {
				echo ' current';
			}
			echo '">'.$post_type .'</a> | </li>';
		}
	}
	foreach ( $post_types2  as $post_type ) {
		echo '<li><a data-table="table-'.$post_type.'" class="showtable table-'.$post_type.'" >'.$post_type .'</a> | </li>';
	} 

	?>
</ul>

<br class="clear" />
<p><?php _e('Here you can select posts that you do not want to include in your sitemap.', 'companion-sitemap-generator');?> <br />

<form method="POST">

	<script>
		jQuery( '.showtable' ).click(function() {

			jQuery( '.showtable' ).removeClass( 'current' );
			jQuery(this).addClass( 'current' );

			var thisClass = jQuery(this).attr( 'data-table' );

			jQuery( '.wp-list-table' ).hide();
			jQuery( '.'+thisClass ).show();

			console.log( '.wp-list-table .'+thisClass );


		});
	</script>

	<?php 

	submit_button();

	foreach ( $post_types  as $post_type ) {
		if( $post_type != 'attachment' ) {
			create_table( $post_type );
		}
	} 
	foreach ( $post_types2  as $post_type ) {
		create_table( $post_type );
	}

	function create_table( $postType ) { ?>

		<table class="wp-list-table widefat striped table-<?php echo $postType; ?>">
			<thead>
				<tr>
					<td  id='cb' class='manage-column column-cb check-column'><label class="screen-reader-text" for="cb-select-all-1">Select All</label><input id="cb-select-all-1" type="checkbox" /></td>
					<th scope="col" id='title' class='manage-column column-title column-primary'><?php _e('Title', 'companion-sitemap-generator'); ?></th>
					<th scope="col" id='permalink' class='manage-column'><?php _e('Permalink', 'companion-sitemap-generator'); ?></th>
				</tr>
			</thead>

			<tbody id="the-list">
				<?php 

				// Create empty string
				$csg_sitemap_content = '';

				// Arguments for selecting pages
				$csg_sitemap_args = array(
					'sortby'			=> 'date',
					'order' 			=> 'desc',
					'post_type' 		=> $postType, 
					'posts_per_page' 	=> '-1',
					'post_status' 		=> 'publish'
				);

				$frontpageid = get_option('page_on_front');

				// The Query
				query_posts( $csg_sitemap_args );

				// The Loop
				if( have_posts() ) :
				while ( have_posts() ) : the_post();

					global $wpdb;
					$table_name = $wpdb->prefix . "sitemap-excludes"; 

					if( in_array( get_the_id(), csg_exclude() ) ) {
						$checked = 'CHECKED';
					}

					if( get_the_id() == $frontpageid ) {
						$showmore = '<span class="post-state">&dash; '.__( "Front page" , "companion-sitemap-generator" ).'</span>';
					}

					echo '
					<tr id="post-'.get_the_id().'">
						<th scope="row" class="check-column">			
							<label class="screen-reader-text" for="cb-select-'.get_the_id().'">Select '. get_the_title() .'</label>
							<input id="cb-select-'.get_the_id().'" type="checkbox" name="post[]" value="'.get_the_id().'" '.$checked.'/>
						</th>
						<td class="title column-title column-primary page-title" data-colname="Title">
							<strong><a href="'. get_the_permalink() .'" target="_blank">'. get_the_title() .'</a> '.$showmore.'</strong>
							<div class="row-actions"><a href="'. get_the_permalink() .'" target="_blank">'. __( "View" , "companion-sitemap-generator" ) .'</a></div>
						</td>
						<td class="permalink column-permalink column-secondar page-permalink" data-colname="Permalink">
							'. get_the_permalink() .'
						</td>
					</tr>';

					$showmore = '';
					$checked = '';

				endwhile;

				else:

					echo '<tr class="no-items"><td class="colspanchange" colspan="3">';
					echo sprintf( esc_html__( 
						'Nothing found in %1$s.', 'companion-sitemap-generator' 
					), $postType );
					echo '</td></tr>';

				endif;

				// Reset Query
				wp_reset_query();

				?>
			</tbody>
		</table>

	<?php }
	submit_button(); ?>

</form>