<div class="wrap">

	<h1><?php _e('Robots', 'companion-sitemap-generator'); ?></h1>

	<table class="form-table">
		<tr>
			<th scope="row">
				<?php _e('Edit Robots', 'companion-sitemap-generator');?>
				<p class='description' style='font-weight: normal;'>
					<?php _e('While a sitemap allows search engines to scan pages faster, a robots.txt file disallows search engines from scanning certain pages.', 'companion-sitemap-generator');?>
				</p>
			</th>
			<td>
				<fieldset>

					<form method="POST">
						<textarea name="csg_robots_content" rows="12" cols="45"><?php csg_read_robots(); ?></textarea>
						<p><button type='submit' name='csg_saveRobots' class='button button-primary'><?php _e('Save robots', 'companion-sitemap-generator');?></button></p>
					</form>

				</fieldset>
			</td>
		</tr>
	</table>

	<?php

	// Read robots.txt file
	function csg_read_robots() {

		$csg_websiteRoot = get_home_path();
		$csg_robotsFile = $csg_websiteRoot.'/robots.txt';

		$robotLines = array();

		$readRobots = fopen($csg_robotsFile, "r");

		if ($readRobots) {
			while (($lineRobot = fgets($readRobots)) !== false) {
			    array_push( $robotLines, $lineRobot );
			}
			fclose($readRobots);
		}

		foreach ($robotLines as $robotLine) { 
			echo $robotLine; echo ''; 
		}
	}

	// Write to the robots.txt file
	function csg_write_robots( $contentToWrite = '' ) {

		$csg_websiteRoot = get_home_path();
		$csg_robotsFile = $csg_websiteRoot.'/robots.txt';

		$filename = $csg_robotsFile;

		if (is_writable($filename)) {

		    if (!$handle = fopen($filename, 'w')) {
		         errorMSG("Cannot open file robots.txt");
		         exit;
		    }

		    if (fwrite($handle, $contentToWrite) === FALSE) {
		        errorMSG("Something went wrong.");
		        exit;
		    }

		    succesMSG("Your robots file has been updated succesfully. Be sure to reload the page before making further adjustments.");

		    fclose($handle);

		} else {
		    errorMSG("The file robots.txt is not writable");
		}
	}

	if( isset( $_POST['csg_saveRobots'] ) ) {
		csg_write_robots( $_POST['csg_robots_content'] ); 
	}

	?>

	<table class="form-table">
		<tr>
			<th scope="row">
				<?php _e('Basic Example', 'companion-sitemap-generator');?>
				<p class='description' style='font-weight: normal;'>
					<?php _e('Here\'s an example of what a robots file could look like.', 'companion-sitemap-generator');?>
				</p>
			</th>
			<td>
				<fieldset>

					User-agent: *<br />
					Disallow: /wp-admin/<br />
					Disallow: /wp-includes/<br />
					Disallow: /feed/<br />
					Disallow: */feed/<br /><br />
					
					<a href='https://support.google.com/webmasters/answer/6062608' rel='nofollow' target='_blank' class='button'><?php _e('Read more about robots', 'companion-sitemap-generator');?></a>

				</fieldset>
			</td>
		</tr>
	</table>
</div>