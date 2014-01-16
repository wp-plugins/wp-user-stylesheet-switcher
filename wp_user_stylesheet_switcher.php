<?php
/*
Plugin Name: WP User Stylesheet Switcher
Version: v1.5.1
Plugin URI: http://wordpress.org/plugins/wp-user-stylesheet-switcher/
Author: Stéphane Groleau
Author URI: http://web.globulesverts.org
Description: Adds a list of stylesheets in the frontend to allow visitors to choose a different stylesheet.
Text Domain: wp-user-stylesheet-switcher

LICENSE
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if(!isset($_SESSION)){
    session_start();
}	

if (!defined('WP_USER_STYLESHEET_SWITCHER_VERSION'))
    define('WP_USER_STYLESHEET_SWITCHER_VERSION', '1.5.0');

class WPUserStylesheetSwitcher {

	/*
	 * Adds the selected stylesheet file to the header
	 * 
	 * */
	public function wp_user_stylesheet_switcher_addcss()
	{
		$settings = get_option('wp_user_stylesheet_switcher_settings');
		$stylesheet_choice = $settings['default'];
		if (isset($_POST['user_stylesheet_choice']))
		{
			$stylesheet_choice = $_POST['user_stylesheet_choice'];
			$_SESSION['user_stylesheet_switcher'] = $stylesheet_choice;
		} else if (isset($_SESSION['user_stylesheet_switcher']))
					$stylesheet_choice = $_SESSION['user_stylesheet_switcher'];
				else {
					$_SESSION['user_stylesheet_switcher'] = $stylesheet_choice;
				}

		if ((!is_numeric($stylesheet_choice)) || ($stylesheet_choice < 0) || ($stylesheet_choice > 4)) {
			$stylesheet_choice = $settings['default'];
			$_SESSION['user_stylesheet_switcher'] = $stylesheet_choice;
		}

		$fileCSS = $settings['options'][$stylesheet_choice]['file'];

		wp_register_style( 'wp_user_stylesheet_switcher_'.$fileCSS, get_stylesheet_directory_uri().'/'.$fileCSS );
		wp_enqueue_style( 'wp_user_stylesheet_switcher_'.$fileCSS);
	}

	/*
	 * Creates the list and returns it.
	 * 
	 * */
	public function create_wp_user_stylesheet_switcher($attributes)
	{
		global $wp_user_stylesheet_switcher_nbform;
		if (isset ($wp_user_stylesheet_switcher_nbform))
			$wp_user_stylesheet_switcher_nbform++;
		else
			$wp_user_stylesheet_switcher_nbform = 1;

		$settings = get_option('wp_user_stylesheet_switcher_settings');

		if (!isset($_SESSION['user_stylesheet_switcher']))
			$_SESSION['user_stylesheet_switcher'] = $settings['default'];  // Default choice
		$stylesheet_choice = $_SESSION['user_stylesheet_switcher'];
		
		// get optional attributes and assign default values if not present
		extract( shortcode_atts( array(
			'list_title' => $settings['title'],
			'show_list_title' => "true",
			'list_type' => 'dropdown',
		), $attributes ) );

		if (!isset($attributes['list_title']))
			$attributes['list_title'] = $settings['title'];
		
		if (!isset($attributes['show_list_title']))
			$attributes['show_list_title'] = "true";
		
		if ("icon" == $list_type) {		
			$output = '<span class="wp_user_stylesheet_switcher"><form method="post" action="'.str_replace( '%7E', '~', $_SERVER['REQUEST_URI']).'" id="wp_user_stylesheet_switcher_form'.$wp_user_stylesheet_switcher_nbform.'" name="wp_user_stylesheet_switcher_form'.$wp_user_stylesheet_switcher_nbform.'" style="display: inline">';
			if (("true" == $attributes['show_list_title']) || ("on" == $attributes['show_list_title'])) $output .= $attributes['list_title'];
		
			$noOption=0;
			foreach ($settings['options'] as $option) {	
				if (($option['file'] != '') && ($option['name'] != '') && ($option['icon'] != ''))
					$output .= '<button class="'.($stylesheet_choice==$noOption?'wp_user_stylesheet_switcher_active_option':'').' wp_user_stylesheet_switcher_button" id="wp_user_stylesheet_switcher_button'.$noOption.'" type="submit" name="user_stylesheet_choice" value="'.$noOption.'" title="'.$option['name'].'"><img class="wp_user_stylesheet_switcher_icon" src="'.get_stylesheet_directory_uri().'/'.$option['icon'].'"  alt="'.$option['name'].'"></button>';
				$noOption++;
			}
			$output .= '</select><input type="hidden" name="wp_user_stylesheet_switcher_list_type" value="icon"></form></span>';

		} else 
		{
			$output = '<div class="wp_user_stylesheet_switcher"><form method="post" action="'.str_replace( '%7E', '~', $_SERVER['REQUEST_URI']).'" id="wp_user_stylesheet_switcher_form'.$wp_user_stylesheet_switcher_nbform.'" name="wp_user_stylesheet_switcher_form'.$wp_user_stylesheet_switcher_nbform.'" style="display: inline">';
			if (("true" == $attributes['show_list_title']) || ("on" == $attributes['show_list_title'])) $output .= $attributes['list_title'];
		
			$output .= '<select name="user_stylesheet_choice"  onchange="document.wp_user_stylesheet_switcher_form'.$wp_user_stylesheet_switcher_nbform.'.submit();">';
			
			$noOption=0;
			foreach ($settings['options'] as $option) {	
				if (($option['file'] != '') && (($option['name'] != '')))
					$output .= '<option '.($stylesheet_choice==$noOption?'selected="selected"':"").' value="'.$noOption.'">'.$option['name'].'</option>';
				$noOption++;
			}
			$output .= '</select><input type="hidden" name="wp_user_stylesheet_switcher_list_type" value="dropdown"></form></div>';
		}
			
		return $output;
	}

	/*
	 * Shows the dropdown list in the webpage
	 * Function used directly in php
	 * 
	 * */
	public function show_wp_user_stylesheet_switcher($list_type = array('list_type'=>'dropdown'))
	{
		echo $this->create_wp_user_stylesheet_switcher($list_type);
	}
	
	/*
	 * Creates and display the option page in the setting menu
	 * Deals also with the submitted form to update the plugin options.
	 * 
	 * */
	public function show_wp_user_stylesheet_switcher_options()
	{
		$settings = get_option('wp_user_stylesheet_switcher_settings');
		if (!isset($settings['version'])) $settings['version'] = 0;
		
		if ($settings['version'] != WP_USER_STYLESHEET_SWITCHER_VERSION) {
			// Upgrade plugin options
			foreach ($settings['options'] as $key => $option) {
				if (!isset($option['icon'])) {
					$NewOption = array(
						'name' => $option['name'],
						'file' => $option['file'],
						'icon' => ''
					);
					$settings['options'][$key] = $NewOption;
				}
			}
			
			$settings['version'] = WP_USER_STYLESHEET_SWITCHER_VERSION;
			update_option('wp_user_stylesheet_switcher_settings', $settings);
		}
		$nbStylesheets = count($settings['options']);
		
		if ((isset($_POST['info_update'])) || (isset($_POST['add_stylesheet_option'])) || (isset($_POST['delete_last_stylesheet_option'])))
		{
			$nonce = $_REQUEST['_wpnonce'];
			if ( !wp_verify_nonce($nonce, 'wp_user_stylesheet_switcher_update')){
				wp_die('Error! Nonce Security Check Failed! Go back to settings menu and save the settings again.');
			}

			$settings['title'] = $_POST["wp_user_stylesheet_switcher_title"];
			$settings['default'] = $_POST["wp_user_stylesheet_switcher_default"];
			$nbStylesheets = intval($_POST["wp_user_stylesheet_switcher_number"]);
			
			if (isset($_POST['delete_last_stylesheet_option']) && ($nbStylesheets > 1)) 
			{
				$nbStylesheets--;
				unset($settings['options'][$nbStylesheets]);
			}
			
			for ($i=0; $i<$nbStylesheets; $i++) {
				$Option = array(
					'name' => $_POST["wp_user_stylesheet_switcher_name".$i],
					'file' => $_POST["wp_user_stylesheet_switcher_file".$i],
					'icon' => $_POST["wp_user_stylesheet_switcher_icon".$i]
				);
				$settings['options'][$i] = $Option;
			}
			
			if (isset($_POST['add_stylesheet_option']))
			{
				$Option = array(
						'name' => '',
						'file' => '',
						'icon' => ''
					);
				$settings['options'][$nbStylesheets] = $Option;
				$nbStylesheets++;
			}
			
			update_option('wp_user_stylesheet_switcher_settings', $settings);
		}
		
		echo '<div class="wrap">'.screen_icon( ).'<h2>'.(__("WP User Stylesheet Switcher Options", "wp-user-stylesheet-switcher")).'</h2>';
		
		?>
		
		<form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>" >
		<?php wp_nonce_field('wp_user_stylesheet_switcher_update'); ?>
		<input type="hidden" name="info_update" id="info_update" value="true" />  
		<?php 
		echo '
		<table class="form-table">
		<tr valign="top">
		<th scope="row">'.(__("Default label for the public list ", "wp-user-stylesheet-switcher")).'</th>
		<td><input type="text" name="wp_user_stylesheet_switcher_title" value="'.$settings['title'].'" size="20" maxlength="40"/></td>
		</tr></table>';
		
		echo '<table class="form-table">';
		$no = 0;
		foreach ($settings['options'] as $option) {
			$optionNumber = sprintf(__("Stylesheet option %d", "wp-user-stylesheet-switcher"), ($no+1));
			echo '<tr valign="top"><th scope="row">'.$optionNumber.'</th><td><label for="wp_user_stylesheet_switcher_name'.$no.'">'.(__("Option name ", "wp-user-stylesheet-switcher")).' </label><input type="text" name="wp_user_stylesheet_switcher_name'.$no.'" value="'.$option['name'].'" size="20" maxlength="40"/></td>
			<td><label for="wp_user_stylesheet_switcher_file'.$no.'">'.(__("CSS file name (including .CSS extension)", "wp-user-stylesheet-switcher")). ' </label><input type="text" name="wp_user_stylesheet_switcher_file'.$no.'" value="'.$option['file'].'" size="20" maxlength="40"/></td>
			<td><label for="wp_user_stylesheet_switcher_icon'.$no.'">'.(__("Optional icon file (.jpg, .gif or .png)", "wp-user-stylesheet-switcher")). ' </label><input type="text" name="wp_user_stylesheet_switcher_icon'.$no.'" value="'.$option['icon'].'" size="20" maxlength="40"/></td></tr>';
			$no++;
		}
				
		echo '</table>';
		echo'<div class="submit">
			<input type="submit" class="button-primary" name="add_stylesheet_option" value="'.(__("+ Add another stylesheet option", "wp-user-stylesheet-switcher")).'" />
			<input type="submit" class="button-primary" name="delete_last_stylesheet_option" value="'.(__("- Delete last stylesheet option", "wp-user-stylesheet-switcher")).'" />
		</div>';	
		echo '<input type="hidden" name="wp_user_stylesheet_switcher_number" value="'.$nbStylesheets.'">';
		
		echo '<table class="form-table"><tr valign="top">
		<th scope="row">'.(__("Default stylesheet", "wp-user-stylesheet-switcher")).'</th>
		<td><select name="wp_user_stylesheet_switcher_default">';
		
		$noOption=0;
		foreach ($settings['options'] as $option) {	
			if (($option['file'] != '') && (($option['name'] != '')))
				echo '<option '.($settings['default']==$noOption?'selected="selected"':"").' value="'.$noOption.'">'.$option['name'].'</option>';
			$noOption++;
		}
		echo '</select><em> '.(__("To update the content of this dropdown list, update options first", "wp-user-stylesheet-switcher")).'</em></td>
		</tr>
		</table>
		<div class="submit">
			<input type="submit" class="button-primary" name="info_update" value="'.(__("Save options", "wp-user-stylesheet-switcher")).'" />
		</div>						
		</form>';
		echo sprintf(__('If you use or if you like this plugin, please consider <a href="%s">making a donation</a>. This helps me keep motivation to update and develop plugins. Thanks!', "wp-user-stylesheet-switcher"), "http://web.globulesverts.org/wp-user-stylesheet-switcher/");
	}

	/*
	 * Handle the options page display
	 * 
	 * */
	public function wp_user_stylesheet_switcher_options_page () 
	{
		add_options_page(__("WP User Stylesheet Switcher", "wp-user-stylesheet-switcher"), __("WP User Stylesheet Switcher", "wp-user-stylesheet-switcher"), 'manage_options', 'wp_user_stylesheet_switcher', array( $this, 'show_wp_user_stylesheet_switcher_options')); 
	}

	/*
	 * Initializes the options when the plugin is installed
	 * 
	 * */
	public function wp_user_stylesheet_switcher_plugin_install()
	{
		//General options
		$settings['title'] = __("Stylesheet choice", "wp-user-stylesheet-switcher");
		$settings['version'] = WP_USER_STYLESHEET_SWITCHER_VERSION;
		$settings['default'] = "";
		
		for ($i = 0; $i<5; $i++) {
			$Option = array(
				'name' => '',
				'file' => '',
				'icon' => ''
			);
			$settings['options'][$i] = $Option;
		}
		add_option('wp_user_stylesheet_switcher_settings', $settings);
	}

	/*
	 * Adds the widget to the list of the available widgets
	 * 
	 * */
	public function wp_user_stylesheet_switcher_load_widgets()
	{
		register_widget('WP_User_Stylesheet_Switcher');
	}

	/*
	 * Adds the settings link
	 * 
	 * */
	public function wp_user_stylesheet_switcher_add_settings_link($links, $file) 
	{
		if ($file == plugin_basename(__FILE__)){
			$settings_link = '<a href="options-general.php?page=wp_user_stylesheet_switcher">'.(__("Settings", "wp-user-stylesheet-switcher")).'</a>';
			array_unshift($links, $settings_link);
		}
		return $links;
	}

	/*
	 * Loads custom language file if present
	 * 
	 * */
	public function load_custom_language_files_wp_user_stylesheet_switcher($domain, $mofile)
	{
		// Note: the plugin directory check is needed to prevent endless function nesting
		// since the new load_textdomain() call will apply the same hooks again.
		if ('wp-user-stylesheet-switcher' === $domain && plugin_dir_path($mofile) === WP_PLUGIN_DIR.'/wp-user-stylesheet-switcher/languages/')
		{
			load_textdomain('wp-user-stylesheet-switcher', WP_LANG_DIR.'/wp-user-stylesheet-switcher/'.$domain.'-'.get_locale().'.mo');
		}
	}

	/*
	* this function loads my plugin translation files
	*/
	public function load_plugin_textdomain() {
		$domain = 'wp-user-stylesheet-switcher';
		// The "plugin_locale" filter is also used in load_plugin_textdomain()
		$locale = apply_filters('plugin_locale', get_locale(), $domain);

		load_textdomain($domain, WP_LANG_DIR.'/wp-user-stylesheet-switcher/'.$domain.'-'.$locale.'.mo');
		load_plugin_textdomain($domain, FALSE, dirname(plugin_basename(__FILE__)).'/languages/');
	}

	public function __construct() {
		add_action('init', array($this, 'load_plugin_textdomain'));
		$text = __('I will not be translated!', 'wp-user-stylesheet-switcher');

		//	add_filter( 'the_content', array( $this, 'the_content' ) );

		add_filter('plugin_action_links', array( $this, 'wp_user_stylesheet_switcher_add_settings_link'), 10, 2 );

		// Insert the options page to the admin menu
		add_action('admin_menu',array( $this, 'wp_user_stylesheet_switcher_options_page'));

		add_action('widgets_init',array( $this, 'wp_user_stylesheet_switcher_load_widgets'));

		add_action('init', array( $this, 'wp_user_stylesheet_switcher_plugin_install'));

		add_shortcode('wp_user_stylesheet_switcher', array( $this, 'create_wp_user_stylesheet_switcher'));

		add_action('wp_head', array( $this, 'wp_user_stylesheet_switcher_addcss'));

		add_action('load_textdomain', array( $this, 'load_custom_language_files_wp_user_stylesheet_switcher') , 10, 2);

		register_activation_hook(__FILE__, array( $this, 'wp_user_stylesheet_switcher_plugin_install'));
	}
}


$wpUserStylesheetSwitcher = new WPUserStylesheetSwitcher();

/*
* Definition of the new class created for the WP_User_Stylesheet_Switcher widget
* 
* */
class WP_User_Stylesheet_Switcher extends WP_Widget {
	function WP_User_Stylesheet_Switcher() {
		parent::WP_Widget('wp_user_stylesheet_switcher_widgets', 'WP User Stylesheet Switcher', array('description' => 'WP User Stylesheet Switcher') );
	}
	
	function form($instance) {
		// outputs the options form on admin
				
		  $defaults = array('title' => 'Stylesheets','show_title' => 'true', 'list_title' => __("Stylesheet choice", "wp-user-stylesheet-switcher"),'show_list_title' => 'true','list_type' => 'dropdown');
		  $instance = wp_parse_args( (array) $instance, $defaults );

		  echo '<p><label for="'.$this->get_field_id('title').'">'.(__("Widget title", "wp-user-stylesheet-switcher")).' </label>
		  <input type="text" id="'.$this->get_field_id('title').'" name="'.$this->get_field_name('title').'" value="'.$instance['title'].'" size="20" maxlength="40"/>
		  </p>';
		  ?>
		  <p>
		   <label for="<?php echo $this->get_field_id('show_title'); ?>"><?php _e('Show widget title', "wp-user-stylesheet-switcher");?></label>
		   <input type="checkbox" id="<?php echo $this->get_field_id('show_title'); ?>" name="<?php echo $this->get_field_name('show_title'); ?>" <?php if ($instance['show_title']=="true") echo 'checked="checked"';
		  echo '/></p>
		  <p><label for="'.$this->get_field_id('list_title').'">'.(__("List title", "wp-user-stylesheet-switcher")).' </label>
		  <input type="text" id="'.$this->get_field_id('list_title').'" name="'.$this->get_field_name('list_title').'" value="'.$instance['list_title'].'" size="20" maxlength="40"/>
		  </p>';
		  ?>
		  <p>
		   <label for="<?php echo $this->get_field_id('show_list_title'); ?>"><?php _e('Show list title', "wp-user-stylesheet-switcher");?></label>
		   <input type="checkbox" id="<?php echo $this->get_field_id('show_list_title'); ?>" name="<?php echo $this->get_field_name('show_list_title'); ?>" <?php if ($instance['show_list_title']=="true") echo 'checked="checked"' ?> />
		  </p>
		  <label for="<?php echo $this->get_field_id('list_type') ?>"> <?php echo (__("List type", "wp-user-stylesheet-switcher")); ?> </label>
		  <select id="<?php echo $this->get_field_id('list_type'); ?>" name="<?php echo $this->get_field_name('list_type') ?>">';
		  
			<option value="dropdown" <?php if ("dropdown"==$instance['list_type']) echo ' selected="selected"'; ?> ><?php _e("Dropdown list", "wp-user-stylesheet-switcher");?></option>
			<option value="icon" <?php if ("icon"==$instance['list_type']) echo ' selected="selected"'; ?> ><?php _e("Icon list", "wp-user-stylesheet-switcher");?></option>
		  </select>
		  </p>
		   
		  <?php
	}
	function update($new_instance, $old_instance) {
		// processes widget options to be saved
		$instance = $old_instance;

		$instance['title'] = $new_instance['title'];
		$instance['show_title'] = $new_instance['show_title']=="on"?"true":"false";
		$instance['list_title'] = $new_instance['list_title'];
		$instance['show_list_title'] = $new_instance['show_list_title']=="on"?"true":"false";
		$instance['list_type'] = $new_instance['list_type'];
		
		return $instance;
	}
	function widget($args, $instance) {
		// outputs the content of the widget
		extract( $args );
		
		$title = $instance['title'];
		$list_type = $instance['list_type'];

		echo $before_widget;
		echo $before_title;
		if ($instance['show_title']=="true") echo $title;
		echo $after_title;
		
		global $wpUserStylesheetSwitcher;
		$wpUserStylesheetSwitcher->show_wp_user_stylesheet_switcher($instance);
		echo $after_widget;
	}
}
