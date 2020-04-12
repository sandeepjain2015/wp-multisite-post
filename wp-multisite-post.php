<?php
/**
 * @package multisite
 */
/*
 Plugin Name: WP Multisite Post 
 Plugin URI: 
 Description: Create and edit single posts for multiple sites in your WordPress network.
 Author: Sandeep jain
 Version: 1.0
 Author URI: http://bestthemeandplugins.com/
 */

define( 'WPMP_TABLENAME', 'multisite_post' );
// We only need this in the admin.
if ( is_admin() ) {
	include_once 'inc/class-wpmp-admin.php';
	new WPMP_Admin();
}
define( 'WPMP__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
register_activation_hook( __FILE__, 'wpmp_install_table' );

if ( ! function_exists( 'wpmp_install_table' ) ) {
	function wpmp_install_table() {
		global $wpdb;
		
		$table = $wpdb->base_prefix . WPMP_TABLENAME;
		
		if ( $wpdb->get_var( "show tables like $table" ) != $table ) {
			$sql = "CREATE TABLE $table ( 
				`id` MEDIUMINT NOT NULL AUTO_INCREMENT , 
				`blog_id` MEDIUMINT UNSIGNED NOT NULL , 
				`post_id` MEDIUMINT UNSIGNED NOT NULL , 
				`connection_id` VARCHAR(25) NOT NULL , 
				PRIMARY KEY (`id`)
			)";
		
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );
		}
	}
}

// Older versions of WP do on not have this function.
if ( ! function_exists( 'wp_get_sites' ) ) {
	function wp_get_sites( $args = array() ) {
		global $wpdb;

		if ( wp_is_large_network() ) {
			return array();
		}

		$defaults = array(
			'network_id' => $wpdb->siteid,
			'public'     => null,
			'archived'   => null,
			'mature'     => null,
			'spam'       => null,
			'deleted'    => null,
			'limit'      => 99,
			'offset'     => 0,
		);

		$args = wp_parse_args( $args, $defaults );

		$query = "SELECT * FROM $wpdb->blogs WHERE 1=1 ";

		if ( isset( $args['network_id'] ) && ( is_array( $args['network_id'] ) || is_numeric( $args['network_id'] ) ) ) {
			$network_ids = implode( ',', wp_parse_id_list( $args['network_id'] ) );
			$query      .= "AND site_id IN ($network_ids) ";
		}

		if ( isset( $args['public'] ) ) {
			$query .= $wpdb->prepare( 'AND public = %d ', $args['public'] );
		}

		if ( isset( $args['archived'] ) ) {
			$query .= $wpdb->prepare( 'AND archived = %d ', $args['archived'] );
		}

		if ( isset( $args['mature'] ) ) {
			$query .= $wpdb->prepare( 'AND mature = %d ', $args['mature'] );
		}

		if ( isset( $args['spam'] ) ) {
			$query .= $wpdb->prepare( 'AND spam = %d ', $args['spam'] );
		}

		if ( isset( $args['deleted'] ) ) {
			$query .= $wpdb->prepare( 'AND deleted = %d ', $args['deleted'] );
		}

		if ( isset( $args['limit'] ) && $args['limit'] ) {
			if ( isset( $args['offset'] ) && $args['offset'] ) {
				$query .= $wpdb->prepare( 'LIMIT %d , %d ', $args['offset'], $args['limit'] );
			} else {
				$query .= $wpdb->prepare( 'LIMIT %d ', $args['limit'] );
			}
		}

		$site_results = $wpdb->get_results( $query, ARRAY_A );

		return $site_results;
	}
}
if ( ( isset( $_GET['post'] ) && ! empty( $_GET['post'] ) ) ) {
	add_action( 'admin_footer', 'trash_click_error', 11 );
}
function trash_click_error() {
	?>
<script>
	jQuery(function($) {
	  jQuery('#delete-action a').unbind();
		jQuery('#delete-action a').click(function(event) {
		  var post_id =jQuery('[name=connection_id]').val();
	  var multi_array =[];
		   jQuery('.wpmp-site-list input:checkbox:checked').each(function(){
			   multi_array.push(jQuery(this).val());
			  });
		  jQuery.ajax({type:'post',url:'<?php echo admin_url( 'admin-ajax.php' ); ?>',data:{action:'send_multisite_id',
					post_id:post_id,site_id:multi_array},
				success:function(msg){
				  }
			})
		 });
	});
</script>
	<?php
}
add_action( 'wp_ajax_no_priv_send_multisite_id', 'send_multisite_id' );
add_action( 'wp_ajax_send_multisite_id', 'send_multisite_id' );
function send_multisite_id() {
	global $wpdb;
	$connection_id = $_POST['post_id'];
	$site_id       = $_POST['site_id'];
	if ( ! empty( $site_id ) ) {
		$site_id_new = implode( ',', $site_id );
		$table       = $wpdb->base_prefix . WPMP_TABLENAME;
		$sql         = "select blog_id,post_id from $table where connection_id=$connection_id and blog_id IN($site_id_new) ";
		global $wpdb;
		$data = $wpdb->get_results( $sql );
		foreach ( $data as $multi_new_id ) {
				   $sql_delete = "delete from $wpdb->base_prefix{$multi_new_id->blog_id}_posts where ID={$multi_new_id->post_id}";
				   $wpdb->query( $sql_delete );
		}
	}}
?>
