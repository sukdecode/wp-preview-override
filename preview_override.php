<?php
/**
 * Plugin Name: Preview Override
 * Description: Replaces the preview url
 * Version: 1.0
 * Author: Suk
 */

function get_new_preview_url(){
	$post = get_post( get_the_ID() );
	$post_id = $post->ID;
	$args = array(
		'post_type' => 'any',
		'p' => $post_id,
	); 
	$post_query = new WP_Query( $args );
	while ( $post_query->have_posts() ) {
		$post_query->the_post();
		$url = get_permalink();
		
		$current_domain = 'https://' . $_SERVER['HTTP_HOST'];
		$new_preview_domain = get_option('new_preview_url');
		$new_link = str_replace($current_domain,$new_preview_domain,$url);	
		return $new_link;	
	}
}

function fix_preview_link_on_draft() {
	$new_preview_url = get_new_preview_url();
	$current_domain = 'https://' . $_SERVER['HTTP_HOST'];
	$new_prod_domain = get_option('new_prod_url');
	echo '<script type="text/javascript">
		jQuery(document).ready(function () {
			const checkPreviewInterval = setInterval(checkPreview, 1000);
			function checkPreview() {
				const editorPreviewButton = jQuery(".edit-post-header-preview__button-external");
				const editorPostSaveDraft = jQuery(".editor-post-save-draft");

				if (editorPreviewButton.attr("href") !=="' . $new_preview_url . '") {
					editorPreviewButton.attr("href", "' . $new_preview_url . '");
					editorPreviewButton.off();
					editorPreviewButton.click(false);
					editorPreviewButton.on("click", function() {
						setTimeout(function() { 
							const win = window.open("' . $new_preview_url . '", "_blank");
							if (win) {
								win.focus();
							}
						}, 1000);
					});
				}
			}
			let content_array = document.getElementsByClassName("view");
			for (i = 0; i < content_array.length; i++) {
				let original_href = content_array[i].getElementsByTagName("a")[0].href;
				let new_href = original_href.replace("'. $current_domain .'", "'. $new_prod_domain .'");				
				content_array[i].getElementsByTagName("a")[0].href = new_href;
			}
		});
	</script>';
}
add_action('admin_footer', 'fix_preview_link_on_draft');


function preview_override_admin_menu() {
	add_options_page(
		__( 'Preview Override'),
		__( 'Preview Override'),
		'manage_options',
		'preview_override_page',
		'preview_override_page',
	);
}

function preview_override_page() {
	?>
		<div class="wrap">
			<h1>
				<?php esc_html_e( 'Welcome to my custom admin page.', 'preview_override' ); ?>
			</h1>
			<form method="post" action="options.php">
				<?php 
					settings_fields( 'preview-url-options-group' ); 
					do_settings_sections( 'preview-url-options-group' );
				?>
				<table class="form-table">
					<tr valign="top">
						<th scope="row">New Preview Base URL</th>
						<td><input style="width: 500px;" type="text" name="new_preview_url" value="<?php echo esc_attr( get_option('new_preview_url') ); ?>" /></td>
					</tr>
					<tr valign="top">
						<th scope="row">New Prod Base URL</th>
						<td><input style="width: 500px;" type="text" name="new_prod_url" value="<?php echo esc_attr( get_option('new_prod_url') ); ?>" /></td>
					</tr>
				</table>
				<?php submit_button(); ?>
			</form>
		</div>
	<?php
}

if ( is_admin() ){
	add_action( 'admin_menu', 'preview_override_admin_menu' );
	add_action( 'admin_init', 'register_mysettings' );
}

function register_mysettings() {
	register_setting( 'preview-url-options-group', 'new_preview_url' );
	register_setting( 'preview-url-options-group', 'new_prod_url' );
}