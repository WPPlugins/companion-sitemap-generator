<div class="wrap">

	<?php 

	if( $_GET['tabbed'] == 'select' ) {
		
		echo '<h1><a href="tools.php?page=csg-sitemap"><span class="dashicons dashicons-arrow-left-alt returnbutton"></span> '.__( "Return to dashboard", "companion-sitemap-generator").'</a></h1>';
		require_once( 'select-posts.php' );

	} else { 
		
		if( isset( $_POST['csg_generate'] ) ) csg_sitemap();

		?>

		<h1><?php _e('Sitemap', 'companion-sitemap-generator'); ?></h1>

		<table class="form-table">
			<tr>
				<th scope="row"><?php _e('Sitemap link', 'companion-sitemap-generator');?></th>
				<td>
					<fieldset>

						<p><?php echo site_url(); ?>/sitemap.xml</p>
						<p><a href='<?php echo site_url(); ?>/sitemap.xml' class='button' target='_blank'><?php _e('View sitemap', 'companion-sitemap-generator');?></a></p>

					</fieldset>
				</td>
			</tr>

			<tr>
				<th scope="row"><?php _e('Update Sitemap', 'companion-sitemap-generator');?></th>
				<td>
					<fieldset>
						<p><?php _e('We update your sitemap every hour, but in case you\'d like to update it manually you can do that here.', 'companion-sitemap-generator');?>.</p>
						<form method="POST">
							<p><button type="submit" name="csg_generate" class="button button-alt"><?php echo csg_dynamic_button(); ?></button></a></p>
						</form>
					</fieldset>
				</td>
			</tr>

			<tr>
				<th scope="row"><?php _e('Exclude Posts', 'companion-sitemap-generator');?></th>
				<td>
					<fieldset>
						<p><?php _e('If you want to exclude posts from your sitemap ', 'companion-sitemap-generator');?>:</p>
						<p><a href='tools.php?page=csg-sitemap&amp;tabbed=select' class='button'><?php _e('Click here', 'companion-sitemap-generator');?></a></p>
					</fieldset>
				</td>

			<tr>
				<th scope="row"><?php _e('HTML Sitemap', 'companion-sitemap-generator');?></th>
				<td>
					<fieldset>

						<p><?php _e('Use this shortcode to display an HTML sitemap:', 'companion-sitemap-generator');?></p>
						<p><code>[html-sitemap]</code></p>

					</fieldset>
				</td>
			</tr>

			<tr>
				<th scope="row"><?php _e('Sitemap & Robots', 'companion-sitemap-generator');?></th>
				<td>
					<fieldset>

						<p><?php _e('Use this line if you\'d like to add the sitemap link to the robots file (good for SEO):', 'companion-sitemap-generator');?></p>
						<p><code>Sitemap: <?php echo site_url(); ?>/sitemap.xml</code></p>

					</fieldset>
				</td>
			</tr>
			</tr>
		</table>

	<?php } ?>

</div>