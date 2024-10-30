<?php
/*
Plugin Name: Chauhan Comments
Plugin URI: https://event.eletsonline.com/egov/smartcity-summit-kanpur
Description: This plugin integrates Facebook Comments at the posts, pages of your WordPress website
Version: 1.0
Author: Gautam Singh Chauhan
Author URI: https://event.eletsonline.com/egov/smartcity-summit-kanpur
Text Domain: chauhan-comments
Domain Path: /languages
License: GPL2+
*/
defined( 'ABSPATH' ) or die( "Cheating........Uh!!" );

// If this file is called directly, halt.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'CFB_VERSION', '1.0' );

/**
 * Save default plugin options
 */
function cfb_save_default_options() {

	// default options
	add_option( 'cfb_options', array(
	   'app_id' => '',
	   'colorscheme' => 'light',
	   'href' => '',
	   'Mobile' => 'true',
	   'num_posts' => '10',
	   'order_by' => 'social',
	   'order_width' => '',
	   'title' => 'Post comment below',
	   'title_color' => '',
	   'title_font_size' => '',
	   'title_font_family' => '',
	   'show_post' => '1',
	   'show_page' => '1'
	) );

	// plugin version
	add_option( 'cfb_version', CFB_VERSION );

}

/**
 * Plugin activation function
 */
function cfb_activate_plugin( $network_wide ) {

	if ( ! current_user_can( 'activate_plugins' ) ) {
        return;
    }

	global $wpdb;

	if ( function_exists( 'is_multisite' ) && is_multisite() ) {
		if ( $network_wide ) {
			$old_blog =  $wpdb->blogid;
			//Get all blog ids
			$blog_ids =  $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );

			foreach ( $blog_ids as $blog_id ) {
				switch_to_blog( $blog_id );
				cfb_save_default_options();
			}
			switch_to_blog( $old_blog );
			return;
		}
	}
	cfb_save_default_options();

}
register_activation_hook( __FILE__, 'cfb_activate_plugin' );

/**
 * Render Facebook Comments
 */
function cfb_render_fb_comments( $content ) {
	global $options;
	if ( ( is_page() && isset( $options['show_page'] ) ) || ( is_single() && isset( $options['show_post'] ) ) || ( is_front_page() && isset( $options['show_home'] ) ) ) {
		global $post; 
		$url = get_permalink( $post->ID );
		if ( $options['href'] != '' ) {
			$url = $options['href'];
		}
		$fb_comments = '<script async defer crossorigin="anonymous" src="https://connect.facebook.net/en_GB/sdk.js#xfbml=1&version=v4.0&appId=' . $options['app_id'] . '&autoLogAppEvents=1"></script>';
		$fb_comments .= "<div><h2 style='float:left; font-family:" . $options["title_font_family"] . ";color:" . $options['title_color'] . "; font-size:" . $options['title_font_size'] . "px;'>" . $options['title'] . "</h2></div>";
		$fb_comments .= "<div style='clear: both;'></div>";
		$fb_comments .= '<div class="fb-comments" data-href="' . $url . '" data-width="' . $options['order_width'] . '" data-numposts="' . $options['num_posts'] . '" data-colorscheme="' . $options['colorscheme'] . '" data-mobile="' . $options['Mobile'] . '" ></div>';
		return $fb_comments . $content;
	}

	return $content;
}
add_filter( 'the_content','cfb_render_fb_comments' );

/**
 * Create plugin menu in admin
 */	
function cfb_create_top_admin_menu() {
	$page = add_menu_page( 'Chauhan Comments', 'Chauhan Comments', 'manage_options', 'chauhan-comments', 'cfb_options_page', plugins_url('images/logo.png', __FILE__) );
}
add_action( 'admin_menu', 'cfb_create_top_admin_menu' );

/**
 * Register plugin options via option form
 */	
function cfb_register_plugin_options() {
	register_setting( 'cfb_plugin_options', 'cfb_options', 'cfb_validate_plugin_options' );
}
add_action( 'admin_init', 'cfb_register_plugin_options' );

/**
 * Validate plugin options
 */
function cfb_validate_plugin_options( $options ) {
	return $options;
}

$options = get_option( 'cfb_options' );

/**
 * Options page
 */
function cfb_options_page() {
	global $options;
	?>
	<form action="options.php" method="post">
	<style>
	input.button{
		font-size: 18px!important;   background: #0085ba!important;
	    border-color: #0073aa #006799 #006799!important;
	    color: #fff!important;
	    text-decoration: none!important;
	}
	input.button:hover {
		background-color: #f44336!important;
		color: white!important;
		border-color: #f44336!important;
	}
	</style>
	<table align="left" width="50%"  cellspacing="0" cellpadding="10" border=" 1px solid" style="background: #EBC285;float:left">
			
	<?php settings_fields( 'cfb_plugin_options' ); ?>

	<tr>
		<td colspan="2" style="font-size: 22px"><b>Settings</b></td>
	</tr>
	<tr>
		<td><label>App Id<img style="vertical-align:bottom" src="<?php echo plugins_url( 'images/info.png', __FILE__ ) ?>" title="<?php _e('Facebook App ID', 'chauhan-comments') ?>" /></label></td>
		<td><input type="text" name="cfb_options[app_id]" value="<?php echo $options['app_id']?>"></td>
	</tr>
	<tr>
	<td><label>Color scheme<img style="vertical-align:bottom" src="<?php echo plugins_url( 'images/info.png', __FILE__ ) ?>" title="<?php _e('Color theme for Facebook Comments Box', 'chauhan-comments') ?>" /></label></td>
	<td><select name="cfb_options[colorscheme]">
	<option value=''>-select-</option>
	<option value='light'<?php if ($options['colorscheme']=='light') {
		echo "selected";
	}?>
	>Light</option>
	<option value='dark'<?php if ($options['colorscheme']=='dark') {
		echo "selected";
	} ?>>Dark</option>
	</select></td>	
	</tr>
	<tr>
		<td><label>Comment URL<img style="vertical-align:bottom" src="<?php echo plugins_url('images/info.png', __FILE__) ?>" title="<?php _e('Url for which Facebook comments will be loaded', 'chauhan-comments') ?>" /></label></td>
	<td><input type="text" name="cfb_options[href]" value="<?php echo $options['href']?>"><br></td>
	</tr>

	<tr>
	<td><label>Optimized for mobile<img style="vertical-align:bottom" src="<?php echo plugins_url('images/info.png', __FILE__) ?>" title="<?php _e('Auto-optimize Facebook comments for mobile devices', 'chauhan-comments') ?>" /></label></td>
	<td><select name="cfb_options[Mobile]">
	<option value=''>-select-</option>
	<option value='true' <?php if ( $options['Mobile'] == 'true' ) {
		echo "selected";
	}?>>on</option>
	<option value='false'<?php if ( $options['Mobile'] == 'false' ) {
		echo "selected";
	}?>>off</option>
	</select></td>
	</tr>

	<tr>
	<td><label><?php _e( 'Number of Comments', 'chauhan-comments' ); ?><img style="vertical-align:bottom" src="<?php echo plugins_url( 'images/info.png', __FILE__ ) ?>" title="<?php _e( 'Number of comments to display by default', 'chauhan-comments' ) ?>" /></label></td>
	<td><input type="text" name="cfb_options[num_posts]" value="<?php echo $options['num_posts'] ?>"></td>
	<br></tr>
	<tr>
	<td><label><?php _e( 'order by', 'chauhan-comments' ) ?><img style="vertical-align:bottom" src="<?php echo plugins_url('images/info.png', __FILE__) ?>" title="<?php _e( 'Default ordering of comments', 'chauhan-comments' ) ?>" /></label></td>
	<td><select name="cfb_options[order_by]">
	<option value=''>-select-</option>
	<option value='social' <?php if ( $options['order_by'] == 'social' ) {
		echo "selected";
	}?>>social</option>
	<option value='reverse_time' <?php if ( $options['order_by'] == 'reverse_time' ) {
		echo "selected";
	}?>>reverse_time</option>
	<option value='time' <?php if ( $options['order_by'] == 'time' ) {
		echo "selected";
	}?>>time</option>
	</select></td>
	</tr>

	<tr>
	<td><label>order width<img style="vertical-align:bottom" src="<?php echo plugins_url( 'images/info.png', __FILE__ ) ?>" title="<?php _e( 'Width of Facebook Comments box. Leave empty for responsive width', 'chauhan-comments' ) ?>" /></label></td>
	<td><input type="text" name="cfb_options[order_width]" value="<?php echo $options['order_width']?>"></td>

	</tr>

	<tr>
	<td><label>Title<img style="vertical-align:bottom" src="<?php echo plugins_url('images/info.png', __FILE__) ?>" title="<?php _e('Title to show above Facebook comments box', 'chauhan-comments') ?>" /></label></td>
	<td><input type="text" name="cfb_options[title]" value="<?php echo $options['title']?>"></td>

	</tr>

	<tr>
	<td><label><?php _e( "Title text color", "chauhan-comments" ) ?><img style="vertical-align:bottom" src="<?php echo plugins_url('images/info.png', __FILE__) ?>" title="<?php _e('Text-color of the title appearing above Facebook Comments box', 'chauhan-comments') ?>" /></label></td>
	<td><input type="color" name="cfb_options[title_color]" value="<?php echo $options['title_color']?>"></td>

	</tr>

	<tr>
	<td><label><?php _e( "Title font size", "chauhan-comments" ) ?><img style="vertical-align:bottom" src="<?php echo plugins_url( 'images/info.png', __FILE__ ) ?>" title="<?php _e( 'Font-size of the title appearing above Facebook comments box', 'chauhan-comments' ) ?>" /></label></td>
	<td><input type="text" name="cfb_options[title_font_size]" value="<?php echo $options['title_font_size']?>"></td>

	</tr>
	<tr>

	<td><label>Title font family<img style="vertical-align:bottom" src="<?php echo plugins_url( 'images/info.png', __FILE__ ) ?>" title="<?php _e( 'Font-family of the title appearing above Facebook comments box', 'chauhan-comments' ) ?>" /></label></td>
	<td><select name="cfb_options[title_font_family]">

	<option value="Arial,Helvetica Neue,Helvetica,sans-serif" <?php if($options["title_font_family"]=="Arial,Helvetica Neue,Helvetica,sans-serif"){echo "selected";} ?>>Arial</option>
	<option value="Arial Black,Arial Bold,Arial,sans-serif" <?php if($options["title_font_family"]=="Arial Black,Arial Bold,Arial,sans-serif"){echo "selected";} ?>>Arial Black</option>
	<option value="Arial Narrow,Arial,Helvetica Neue,Helvetica,sans-serif" <?php if($options["title_font_family"]=="Arial Narrow,Arial,Helvetica Neue,Helvetica,sans-serif"){echo "selected";} ?>>Arial Narrow</option>
	<option value="Courier,Verdana,sans-serif" <?php if($options["title_font_family"]=="Courier,Verdana,sans-serif"){echo "selected";} ?>>Courier</option>
	<option value="Georgia,Times New Roman,Times,serif" <?php if($options["title_font_family"]=="Georgia,Times New Roman,Times,serif"){echo "selected";} ?>>Georgia</option>
	<option value="Times New Roman,Times,Georgia,serif" <?php if($options["title_font_family"]=="Times New Roman,Times,Georgia,serif"){echo "selected";} ?>>Times New Roman</option>
	<option value="Trebuchet MS,Lucida Grande,Lucida Sans Unicode,Lucida Sans,Arial,sans-serif" <?php if($options["title_font_family"]=="Trebuchet MS,Lucida Grande,Lucida Sans Unicode,Lucida Sans,Arial,sans-serif"){echo "selected";} ?>>Trebuchet MS</option>
	<option value="Verdana,sans-serif" <?php if($options["title_font_family"]=="Verdana,sans-serif"){echo "selected";} ?>>Verdana</option>
	<option value="American Typewriter,Georgia,serif" <?php if($options["title_font_family"]=="American Typewriter,Georgia,serif"){echo "selected";} ?>>American Typewriter</option>
	<option value="Andale Mono,Consolas,Monaco,Courier,Courier New,Verdana,sans-serif" <?php if($options["title_font_family"]=="Andale Mono,Consolas,Monaco,Courier,Courier New,Verdana,sans-serif"){echo "selected";} ?>>Andale Mono</option>
	<option value="Baskerville,Times New Roman,Times,serif" <?php if($options["title_font_family"]=="Baskerville,Times New Roman,Times,serif"){echo "selected";} ?>>Baskerville</option>
	<option value="Bookman Old Style,Georgia,Times New Roman,Times,serif" <?php if($options["title_font_family"]=="Bookman Old Style,Georgia,Times New Roman,Times,serif"){echo "selected";} ?>>Bookman Old Style</option>
	<option value="Calibri,Helvetica Neue,Helvetica,Arial,Verdana,sans-serif" <?php if($options["title_font_family"]=="Calibri,Helvetica Neue,Helvetica,Arial,Verdana,sans-serif" ){echo "selected";} ?>>Calibri</option>
	<option value="Cambria,Georgia,Times New Roman,Times,serif" <?php if($options["title_font_family"]=="Cambria,Georgia,Times New Roman,Times,serif"){echo "selected";} ?>>Cambria</option>
	<option value="Candara,Verdana,sans-serif" <?php if($options["title_font_family"]=="Candara,Verdana,sans-serif"){echo "selected";} ?>>Candara</option>
	<option value="Century Gothic,Apple Gothic,Verdana,sans-serif" <?php if($options["title_font_family"]=="Century Gothic,Apple Gothic,Verdana,sans-serif"){echo "selected";} ?>>Century Gothic</option>
	<option value="Century Schoolbook,Georgia,Times New Roman,Times,serif" <?php if($options["title_font_family"]=="Century Schoolbook,Georgia,Times New Roman,Times,serif"){echo "selected";} ?>>Century Schoolbook</option>
	<option value="Consolas,Andale Mono,Monaco,Courier,Courier New,Verdana,sans-serif" <?php if($options["title_font_family"]=="Consolas,Andale Mono,Monaco,Courier,Courier New,Verdana,sans-serif"){echo "selected";} ?>>Consolas</option>
	<option value="Constantia,Georgia,Times New Roman,Times,serif" <?php if($options["title_font_family"]=="Constantia,Georgia,Times New Roman,Times,serif"){echo "selected";} ?>>Constantia</option>
	<option value="Corbel,Lucida Grande,Lucida Sans Unicode,Arial,sans-serif" <?php if($options["title_font_family"]=="Corbel,Lucida Grande,Lucida Sans Unicode,Arial,sans-serif"){echo "selected";} ?>>Corbel</option>
	<option value="Franklin Gothic Medium,Arial,sans-serif" <?php if($options["title_font_family"]=="Franklin Gothic Medium,Arial,sans-serif"){echo "selected";} ?>>Franklin Gothic Medium</option>
	<option value="Garamond,Hoefler Text,Times New Roman,Times,serif" <?php if($options["title_font_family"]=="Garamond,Hoefler Text,Times New Roman,Times,serif" ){echo "selected";} ?>>Garamond</option>
	<option value="Gill Sans MT,Gill Sans,Calibri,Trebuchet MS,sans-serif" <?php if($options["title_font_family"]=="Gill Sans MT,Gill Sans,Calibri,Trebuchet MS,sans-serif"){echo "selected";} ?>>Gill Sans MT</option>
	<option value="Helvetica Neue,Helvetica,Arial,sans-serif" <?php if($options["title_font_family"]=="Helvetica Neue,Helvetica,Arial,sans-serif"){echo "selected";} ?>>Helvetica Neue</option>
	<option value="Hoefler Text,Garamond,Times New Roman,Times,sans-serif" <?php if($options["title_font_family"]=="Hoefler Text,Garamond,Times New Roman,Times,sans-serif"){echo "selected";} ?>>Hoefler Text</option>
	<option value="Lucida Bright,Cambria,Georgia,Times New Roman,Times,serif" <?php if($options["title_font_family"]=="Lucida Bright,Cambria,Georgia,Times New Roman,Times,serif"){echo "selected";} ?>>Lucida Bright</option>
	<option value="Lucida Grande,Lucida Sans,Lucida Sans Unicode,sans-serif" <?php if($options["title_font_family"]=="Lucida Grande,Lucida Sans,Lucida Sans Unicode,sans-serif"){echo "selected";} ?>>Lucida Grande</option>
	<option value="monospace" <?php if($options["title_font_family"]=="monospace"){echo "selected";} ?>>monospace</option>
	<option value="Palatino Linotype,Palatino,Georgia,Times New Roman,Times,serif" <?php if($options["title_font_family"]=="Palatino Linotype,Palatino,Georgia,Times New Roman,Times,serif"){echo "selected";} ?>>Palatino Linotype</option>
	<option value="Tahoma,Geneva,Verdana,sans-serif" <?php if($options["title_font_family"]=="Tahoma,Geneva,Verdana,sans-seriff"){echo "selected";} ?>>Tahoma</option>
	<option value="Rockwell, Arial Black, Arial Bold, Arial, sans-serif" <?php if($options["title_font_family"]=="Rockwell, Arial Black, Arial Bold, Arial, sans-serif"){echo "selected";} ?>>Rockwell</option>


	</select></td>
	</tr>

	<tr>
	<td><label><?php _e( "Display comments on", "chauhan-comments" ) ?><img style="vertical-align:bottom" src="<?php echo plugins_url('images/info.png', __FILE__) ?>" title="<?php _e('Page-groups to integrate Facebook Comments at', 'chauhan-comments') ?>" /></label></td>
	<td colspan="2"><input type="checkbox" name="cfb_options[show_home]" value="1" <?php if (isset($options["show_home"])){echo "checked";}?>/><label>Home</label>
	<input type="checkbox" name="cfb_options[show_post]" value="1" <?php if (isset($options["show_post"])){echo "checked";}?>/><label>Post</label>
	<input type="checkbox" name="cfb_options[show_page]" value="1" <?php if (isset($options["show_page"])){echo "checked";}?>/><label>Page</label></td>

	</tr>
		
	<tr>

	<td colspan="2" style="text-align:center" ><input type="submit" name="submit" value="<?php _e( 'Submit', 'chauhan-comments' ) ?>" class="button" /></td>
	</tr>
	</table>
	</form>

	<div style="width:45%;margin-left:10px;border:1px black solid;background:#e48d8d;float:left;padding:0 8px;">
		<p><?php _e( "Here's a short user manual to help you integrate Facebook Comments with your website.", "chauhan-comments" ) ?></p>
	<p style="font-weight:bolder"><span style="color:red"><?php _e( "APP ID", "chauhan-comments" ) ?></span> - <?php _e( "You can create your App Id on this page" ) ?> - <a style="color:#0073aa" target="_blank" href="https://developers.facebook.com/apps">https://developers.facebook.com/apps.</a>
	<?php _e( "Also, here is another tutorial(from other source) of creating App Id, you can check it", 'chauhan-comments' ) ?> - <a style="color:#0073aa" target="_blank" href="https://help.yahoo.com/kb/SLN18861.html">https://help.yahoo.com/kb/SLN18861.html</a>.</p>
	<p>
	<?php _e( "You can use shortcode <strong>[chauhan-comments]</strong> to place Facebook Comments box in content of your webpages", 'chauhan-comments' ) ?>	
	</p>
	</div>
	<?php
}

/**
 * Widget for Facebook Comments
 */
class ChauhanFacebookComments extends WP_Widget { 
	/** constructor */ 
	public function __construct() { 
		parent::__construct( 
			'ChauhanFacebookComments', //unique id 
			__( 'Chauhan Comments' ), //title displayed at admin panel
			array(  
				'description' => __( 'Integrate Facebook Comments', 'chauhan-comments' ) ) 
			); 
	}
	
	/** This is rendered widget content */ 
	public function widget( $args, $instance ) {
		//extract( $args );
		echo $before_widget;
		if( !empty( $instance['before_widget_content'] ) ){ 
			echo '<div>' . $instance['before_widget_content'] . '</div>';
		}
		global $options;
		global $post; 
		$url = get_permalink( $post->ID );
		if ( $options['href'] != '' ) {
			$url = $options['href'];
		}
		echo '<div>' . $instance['title'] . '</div>';
		?>
		<script async defer crossorigin="anonymous" src="https://connect.facebook.net/en_GB/sdk.js#xfbml=1&version=v4.0&appId=<?php echo $options['app_id']?>&autoLogAppEvents=1"></script>
		<div class="fb-comments" data-href="<?php echo "$url";?>"
data-width="<?php echo $options['order_width']?>" data-numposts="<?php echo $options['num_posts'] ?>" data-colorscheme="<?php echo $options['colorscheme'] ?>" data-mobile="<?php echo $options['Mobile'] ?>"></div>
		<?php
		echo '<div style="clear:both"></div>';
		if ( ! empty( $instance['after_widget_content'] ) ) { 
			echo '<div>' . $instance['after_widget_content'] . '</div>';
		}
		echo $after_widget;
	}  

	/** Everything which should happen when user edit widget at admin panel */ 
	public function update( $new_instance, $old_instance ) { 
		$instance = $old_instance; 
		$instance['title'] = strip_tags( $new_instance['title'] ); 
		$instance['before_widget_content'] = $new_instance['before_widget_content']; 
		$instance['after_widget_content'] = $new_instance['after_widget_content']; 

		return $instance; 
	}  

	/** Widget options in admin panel */ 
	public function form( $instance ) { 
		/* Set up default widget settings. */ 
		$defaults = array( 'title' => __( 'Facebook Comments', '' ), 'before_widget_content' => '', 'after_widget_content' => '' );  

		foreach( $instance as $key => $value ) {  
			if ( is_string( $value ) ) {
				$instance[ $key ] = esc_attr( $value );  
			}
		}

		$instance = wp_parse_args( (array)$instance, $defaults ); 
		?> 
		<p> 
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title (before login):', 'super-socializer' ); ?></label> 
			<input style="width: 95%" class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $instance['title']; ?>" /> 
			<label for="<?php echo $this->get_field_id( 'before_widget_content' ); ?>"><?php _e( 'Before widget content:', 'super-socializer' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'before_widget_content' ); ?>" name="<?php echo $this->get_field_name( 'before_widget_content' ); ?>" type="text" value="<?php echo $instance['before_widget_content']; ?>" /> 
			<label for="<?php echo $this->get_field_id( 'after_widget_content' ); ?>"><?php _e( 'After widget content:', 'super-socializer' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'after_widget_content' ); ?>" name="<?php echo $this->get_field_name( 'after_widget_content' ); ?>" type="text" value="<?php echo $instance['after_widget_content']; ?>" />  
		</p> 
		<?php 
	} 
} 
add_action( 'widgets_init', function() { return register_widget( "ChauhanFacebookComments" ); } ); 

/** 
 * Shortcode for Facebook Comments
 */ 
function cfb_shortcode( $params ) {
	extract( shortcode_atts( array(
		'style' => '',
		'url' => get_permalink(),
		'num_posts' => '',
		'width' => '',
		'language' => get_locale(),
		'title' => ''
	), $params ) );
	$html = '<div style="' . $style . '" id="cfb_shortcode_commenting">';
	if ( $title != '' ) {
		$html .= '<div style="font-weight:bold">' . ucfirst( $title ) . '</div>';
	}
	global $options;
	global $post; 
	if ( $url == '' ) {
		$url = get_permalink( $post->ID );
	}
	$html .= '<div>' . $title . '</div>';
	
	$html .= '<script async defer crossorigin="anonymous" src="https://connect.facebook.net/en_GB/sdk.js#xfbml=1&version=v4.0&appId=' . $options['app_id'] . '&autoLogAppEvents=1"></script><div class="fb-comments" data-href="' . $url . '" data-width="" data-numposts="" data-colorscheme="" data-mobile=""></div>';
	
	return $html;
}
add_shortcode( 'chauhan-comments', 'cfb_shortcode' );