<input type="hidden" name="connection_id" value="<?php echo (isset($connection_id) && !empty($connection_id)) ? $connection_id : time().$wpdb->blogid.$post->ID;?>" />
<div class="wpmp-sites"><ul class="wpmp-site-list">
<?php foreach( apply_filters('wpmp-site-list', $sites) as $site):?>
	<li>
		<?php $is_connected = (bool)in_array($site->blog_id, $connected_sites) ? true : false;?>
		<input type="checkbox" name="wpmp_sites[]" value="<?php echo $site->blog_id;?>" id="<?php echo $site->blog_id;?>" value="1" <?php checked($is_connected, true, true);?>/>
		<label for="<?php echo $site->blog_id;?>">
			<?php echo $site->blogname;?> (<?php echo $site->blog_id;?>)
		</label>
		<?php //pr($site);?>
		<?php if($is_connected):?>
			<?php $post_id = $this->get_post_id_by_connection_id($connection_id, $site->blog_id);?>
			<a href="<?php echo $site->siteurl;?>/wp-admin/post.php?post=<?php echo $post_id;?>&action=edit" target="_blank"><?php _e('View');?></a>
		<?php endif;?>
	</li>
	
	<?php 			
		$cnt++;
		$col_counter++;
	?>		
	
	<?php if($col_counter >= $per_column && $cnt < $site_count ):?>
		<?php $col_counter = 0;?>
		</ul><ul class="wpmp-site-list">
	<?php endif;?>
<?php endforeach;?>
</ul></div>