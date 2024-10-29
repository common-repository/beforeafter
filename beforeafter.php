<?php
/*
Plugin Name: Before/After
Plugin URI: http://www.keighl.com/plugins/before-after/
Description: A before and after portfolio generator.
Version: 0.2
Author: Kyle Truscott
Author URI: http://www.keighl.com
*/

/*  Copyright 2009 Kyle Truscott  (email : keighl@keighl.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

register_activation_hook(__FILE__,'ba_install');

$beforeafter = new BeforeAfter();

class BeforeAfter {

	function BeforeAfter() {

		add_action("admin_print_scripts", array(&$this, 'ba_js_libs') , 7);
		add_action('admin_menu', array(&$this, 'ba_admin') , 7);
		
		add_action('wp_ajax_ba_return_media_library', array(&$this, 'ba_return_media_library') , 7);
		add_action('wp_ajax_ba_return_beforeafter', array(&$this, 'ba_return_beforeafter') , 7);
		add_action('wp_ajax_ba_add', array(&$this, 'ba_add') , 7);
		add_action('wp_ajax_ba_remove', array(&$this, 'ba_remove'), 7);
		add_action('wp_ajax_ba_sort', array(&$this, 'ba_sort'), 7);
		
	}
	
	// Template Tags
	
	function is_gallery($id = null) {
		
		global $wpdb;
		
		//$wpdb->show_errors();
		$table_name = $wpdb->prefix . "ba";
		
		if (!isset($id)) :
			return false;
		endif;
		
		$post_id_is_gallery = $id;
		
		$images = $wpdb->get_results("SELECT * FROM $table_name WHERE post = $post_id_is_gallery ORDER BY ba_order ASC", ARRAY_A);
		
		if ($images) :
			return true;
		else :
			return false;
		endif;
		
	}
	
	function gallery($type = 'after' , $id = 0 , $file = 'thumb' , $links = true, $list = false , $rel = "beforeafter" , $limit = false) {
		
		global $post;
		global $wpdb;
				
		$table_name = $wpdb->prefix . "ba";
		
		if ($id == 0) :
			$query = "SELECT * FROM $table_name WHERE cat = '$type' ORDER BY ba_order ASC";
			if ($limit) :
				$query = $query . " LIMIT " . $limit;
			endif;
		else :
			$query = "SELECT * FROM $table_name WHERE post = $id AND cat = '$type' ORDER BY ba_order ASC";
			if ($limit) :
				$query = $query . " LIMIT " . $limit;
			endif;
		endif;
		
		$images = $wpdb->get_results($query, ARRAY_A);
		
			
			if ($images) :
			
				foreach ($images as $image) :
				
					$id = $image['id'];
					$wpid = $image['wpid'];
					$file_path = wp_get_attachment_url($wpid);
					$thumb_path = wp_get_attachment_thumb_url($wpid);
					$order = $image['ba_order'];
					?>
					
						<?php if ($list) : ?> 
							<li>
						<?php endif; ?>
					
							<?php if ($links) : ?> 
								<a href="<?php echo $file_path; ?>" rel="<?php echo $rel; ?>">
							<?php endif; ?>
						
								<img  src="<?php if ($file == 'file') : echo $file_path; else : echo $thumb_path; endif; ?>" />
						
							<?php if ($links) : ?> 
								</a>
							<?php endif; ?>
						
						<?php if ($list) : ?> 
							</li>
						<?php endif; ?>
					
					<?php
				
				endforeach;
			
			endif;
		
	}
	
	// Admin
	
	function ba_post_box() {

		$this->ba_style();
		$this->ba_scripts();
		
		global $post;
		
		?>
	    <div class="ba_box">
	        
			<?php if ($post->ID == 0) : ?>
			
				<div class="ba_alert">
					To organize a Before/After gallery, please <strong>save or publish your post</strong>.
				</div>
				
			<?php else : ?>
			
				
				<div class="ba_use">
					<strong>Drag and drop</strong> items from the media library into <em>Before</em> or <em>After</em>. <strong>Double-click</strong> items to remove them. 
				</div>
				
				<div class="ba_column ba_media">
		            <h5 class="ba">Media Library <a href="media-new.php">(Add New)</a></h5>
		            <ul id="ba_media">

		            </ul>
		            <div class="ba_clear"></div>
		        </div>

		        <div class="ba_column">
		            <h5 class="ba">Before</h5>
		            <ul id="ba_before">

		            </ul>
		        </div>

		        <div class="ba_column">
		            <h5 class="ba">After</h5>
		            <ul id="ba_after">

		            </ul>
		        </div>
				
			<? endif; ?>
			
	        <div class="ba_clear"></div>
	    </div>

		<?php

	}
	
	function ba_style() {

		?>
	    <style>

			.ba_clear {
			clear:both;
			visibility:hidden;
			}
			
			.ba_box {
				width:620px;
				margin:15px;
			}
			
			.ba_box li {
				cursor:move;
			}
			
			.ba_use {
				color:green;
				margin-bottom:20px;
			}
			

			h5.ba {
			font-size:1em;
			font-weight:bold;
			border-bottom:#CCCCCC solid 1px;
			padding-bottom:8px;
			margin-bottom:8px;
			margin-top:0px;
			background-color:#FFFFFF;
			}

			.ba_column {
				float:left;
				width:80px;
				margin-right:15px;
			}

			.ba_media {
			width:400px;
			}



			#ba_before, #ba_after {
			padding-bottom:75px;
			clear:both;
			}

			ul#ba_media li {
				float:left;
				width:75px;
				height:75px;
				margin-bottom:5px;
				margin-right:5px;

			}



		</style>
	    <?php 

	}

	function ba_scripts() {

		global $post;

		?>

		<script type="text/javascript">

			jQuery(document).ready(function($) {

				// Init
				ba_return_everything();
					
				// Jquery
				
				$("#ba_after").sortable({
					dropOnEmpty: true,
					receive: function(event, ui) {
						var wpid = $(ui.helper).attr('id');
						$.post(
							"<?php echo get_option('siteurl'); ?>/wp-admin/admin-ajax.php", 
							{action:"ba_add" , wpid:wpid, cat:'after', post:'<?php echo $post->ID;?>' },
							function(str) {
								ba_return_everything();
							}
						);
					} , 
					update: function(event, ui) {
						var ba_order = $(this).sortable("serialize");
						$.post(
							"<?php echo get_option('siteurl'); ?>/wp-admin/admin-ajax.php", 
							{action:"ba_sort", cat:'after' , post:'<?php echo $post->ID;?>' , ba_order:ba_order},
							function(str) {
								ba_return_everything();
							}
						);
					}
				});	
				
				$("#ba_before").sortable({
					dropOnEmpty: true,
					receive: function(event, ui) {
						var wpid = $(ui.helper).attr('id');
						$.post(
							"<?php echo get_option('siteurl'); ?>/wp-admin/admin-ajax.php", 
							{action:"ba_add" , wpid:wpid, cat:'before', post:'<?php echo $post->ID;?>' },
							function(str) {
							   ba_return_everything();
							}
						);
						
					} ,
					update: function(event, ui) {
						var ba_order = $(this).sortable("serialize");
						$.post(
							"<?php echo get_option('siteurl'); ?>/wp-admin/admin-ajax.php", 
							{action:"ba_sort", cat:'before' , post:'<?php echo $post->ID;?>' , ba_order:ba_order},
							function(str) {
								ba_return_everything();
							}
						);
					}
				});	
				
				$('.ba_list_item').live("dblclick", 
					function () {
						var id = $(this).attr("id");
						$.post(
							"<?php echo get_option('siteurl'); ?>/wp-admin/admin-ajax.php", 
							{action:"ba_remove" , id:id},
							function(str) {
							   ba_return_everything();
							}
						);
					}
				);
				
				// Methods
				function ba_return_everything() {
					$('.ba_box').fadeOut(300);
					//Media Library
					$.post(
						"<?php echo get_option('siteurl'); ?>/wp-admin/admin-ajax.php", 
						{action:"ba_return_media_library"},
						function(str) {
							$('#ba_media').html(str);
							$("#ba_media li").draggable({ 
								revert : 'invalid',
								refreshPositions: true,
								connectToSortable:'#ba_before, #ba_after' 
							});
						}
					);
					// Before
					$.post(
						"<?php echo get_option('siteurl'); ?>/wp-admin/admin-ajax.php", 
						{action:"ba_return_beforeafter" , cat:"before" , post:<?php echo $post->ID; ?>},
						function(str) {
						   $('#ba_before').html(str);
						}
					);
					// After
					$.post(
						"<?php echo get_option('siteurl'); ?>/wp-admin/admin-ajax.php", 
						{action:"ba_return_beforeafter" , cat:"after" , post:<?php echo $post->ID; ?>},
						function(str) {
						   $('#ba_after').html(str);
						}
					);
					$('.ba_box').fadeIn(300);
					
				}

			});

		</script>

	    <?php
	}
	
	// Methods
	
	function ba_add() {

		$wpid = $_POST['wpid'];
			$wpid = trim($wpid, 'ba_wpid_');
		
		$cat = $_POST['cat'];
		$post = $_POST['post'];

		global $wpdb;
		$wpdb->show_errors();

		$table_name = $wpdb->prefix . "ba";
		
		// find the highest order thus far
		
		$items = $wpdb->get_results("SELECT ba_order FROM $table_name WHERE post = $post AND cat = '$cat'", ARRAY_A);
		
		if ($items) :
			$order_set = array();
			foreach ($items as $item) :
			  $order_set[] = $item['ba_order'];
			endforeach;
			$highest_order = max($order_set);
		else :
			$highest_order = 0;
	    endif;
	
		$highest_order++;

		$data_array = array(
						'wpid' => $wpid,
						'cat' => $cat,
						'post' => $post,
						'ba_order' => $highest_order
						);

		$wpdb->insert($table_name, $data_array );

		exit();

	}

	function ba_remove() {

		$id = $_POST['id'];
		$id = $wpid = trim($id, 'ba_id_');

		global $wpdb;
		$wpdb->show_errors();

		$table_name = $wpdb->prefix . "ba";

		$wpdb->query("DELETE from $table_name WHERE id = $id");

		exit();

	}
	
	function ba_sort() {
		
		global $wpdb;
		$wpdb->show_errors();
		$table_name = $wpdb->prefix . "ba";
		
		$post = $_POST['post'];
		$cat  = $_POST['cat'];
		
		$ids = $_POST['ba_order'];
		$ids = explode('ba_id[]=', $ids);
		
		$ba_order = -1;
		
		foreach ($ids as $id) :

			$ba_order++;

			$pattern = "/&/";
			$id = preg_replace($pattern, '' , $id);

			$data_array = array(
				"ba_order" => $ba_order
				);
			$where = array('id' => $id);
			$wpdb->update($table_name, $data_array, $where );

		endforeach;
		
		exit();
		
	}
	
	// Models
	
	function ba_return_media_library() {
		global $wpdb;
		$wpdb->show_errors();
		$table_name = $wpdb->prefix . "ba";

		// which images are already in a before/after list?
		$used_items = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);

		if ($used_items) :	

			$useds = '';

			foreach ($used_items as $used) :

				$useds = $useds . $used['wpid'] . ',';

			endforeach;

			$useds = ltrim($useds);
			$useds = substr($useds, 0, -1);

			$images = $wpdb->get_results("SELECT * FROM $wpdb->posts WHERE post_type = 'attachment' AND id NOT IN($useds)");

		else :

			$images = $wpdb->get_results("SELECT * FROM $wpdb->posts WHERE post_type = 'attachment'");

		endif;

		if ($images) :	

			foreach ($images as $image) :
				$id = $image->ID;
				$file = wp_get_attachment_thumb_url($id);

				?>
	            <li id="ba_wpid_<?php echo $id; ?>">
				<img  src="<?php echo $file ?>" width="75" height="75" />
	            </li>
				<?php

			endforeach;

		endif;

		exit();

	}

	function ba_return_beforeafter() {

		global $wpdb;
		$wpdb->show_errors();
		$table_name = $wpdb->prefix . "ba";

		$post = $_POST['post'];
		$cat = $_POST['cat'];

		$images = $wpdb->get_results("SELECT * FROM $table_name WHERE cat = '$cat' AND post = $post ORDER BY ba_order ASC", ARRAY_A);

		if ($images) :

			foreach ($images as $image) :

				$id = $image['id'];
				$wpid = $image['wpid'];
				$file = wp_get_attachment_thumb_url($wpid);
				$order = $image['ba_order'];

				?>
				<li id="ba_id_<?php echo $id; ?>" class="ba_list_item">
	            	<img  src="<?php echo $file ?>" width="75" height="75" />
	            </li>
				<?php

			endforeach;

		endif;

		exit();

	}
	
	// Init
	
	function ba_admin() {

		add_meta_box( 'beforeafter', __( 'Before/After', 'beforeafter' ), array(&$this, 'ba_post_box'), 'post', 'advanced');

	}
	
	function ba_js_libs() {

		wp_enqueue_script('jquery');
		wp_enqueue_script('jquery-ui-core');
		wp_enqueue_script('jquery-ui-sortable');
		wp_enqueue_script('jquery-ui-draggable');
		wp_enqueue_script('jquery-ui-droppable');
		wp_enqueue_script('jquery-ui-selectable');

	}
	
}

// Install

function ba_install() {

	 global $wpdb;
	 $wpdb->show_errors();

	 $table_name = $wpdb->prefix . "ba";

	 if($wpdb->get_var("show tables like '$table_name'") != $table_name) :

	 	$sql = "CREATE TABLE " . $table_name . " (
	 		id int NOT NULL AUTO_INCREMENT,
	 		wpid int NOT NULL,
	 		post int NOT NULL,
			cat text NOT NULL,
			ba_order int NOT NULL,
	 		PRIMARY  KEY id (id)
	 		);";

	 	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	 	dbDelta($sql);

	 endif;
}

?>