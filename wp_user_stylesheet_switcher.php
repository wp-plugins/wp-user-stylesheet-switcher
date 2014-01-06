<?php
/*
Plugin Name: WP User Stylesheet Switcher
Version: v1.0.1
Plugin URI: http://web.globulesverts.org
Author: Stéphane Groleau
Author URI: http://web.globulesverts.org
Description: Adds a list of stylesheets in the frontend to allow visitors to choose a different stylesheet.
*/

if(!isset($_SESSION)){
    session_start();
}	

if (!defined('WP_USER_STYLESHEET_SWITCHER_VERSION'))
    define('WP_USER_STYLESHEET_SWITCHER_VERSION', '1.0.1');


/*
 * Adds the selected stylesheet file to the header
 * 
 * */
function wp_user_stylesheet_switcher_addcss()
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
 * Shows the dropdown list in the webpage
 * Function used directly in php
 * 
 * */
function show_wp_user_stylesheet_switcher($list_type = array('list_type'=>'dropdown'))
{
    echo create_wp_user_stylesheet_switcher($list_type);
}

/*
 * Creates the dropdown list and returns it.
 * 
 * */
function create_wp_user_stylesheet_switcher($attributes)
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
 * Creates and display the option page in the setting menu
 * Deals also with the submitted form to update the plugin options.
 * 
 * */
function show_wp_user_stylesheet_switcher_options()
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
    
	echo '<div class="wrap">'.screen_icon( ).'<h2>'.(__("WP User Stylesheet Switcher Options", "WUSC")).'</h2>';
	
	?>
	
	<form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>" >
    <?php wp_nonce_field('wp_user_stylesheet_switcher_update'); ?>
    <input type="hidden" name="info_update" id="info_update" value="true" />  
	<?php 
	echo '
	<table class="form-table">
	<tr valign="top">
	<th scope="row">'.(__("Default label for the public dropdown list ", "WUSC")).'</th>
	<td><input type="text" name="wp_user_stylesheet_switcher_title" value="'.$settings['title'].'" size="20" maxlength="40"/></td>
	</tr></table>';
	
	echo '<table class="form-table">';
	$no = 0;
	foreach ($settings['options'] as $option) {
		echo '<tr valign="top"><th scope="row">'.(__("Stylesheet option".($no+1), "WUSC")).'</th><td><label for="wp_user_stylesheet_switcher_name'.$no.'">'.(__("Option name ", "WUSC")).' </label><input type="text" name="wp_user_stylesheet_switcher_name'.$no.'" value="'.$option['name'].'" size="20" maxlength="40"/></td>
		<td><label for="wp_user_stylesheet_switcher_file'.$no.'">'.(__("CSS file name (including .CSS extension)", "WUSC")). ' </label><input type="text" name="wp_user_stylesheet_switcher_file'.$no.'" value="'.$option['file'].'" size="20" maxlength="40"/></td>
		<td><label for="wp_user_stylesheet_switcher_icon'.$no.'">'.(__("Optional icon file (.jpg, .gif or .png)", "WUSC")). ' </label><input type="text" name="wp_user_stylesheet_switcher_icon'.$no.'" value="'.$option['icon'].'" size="20" maxlength="40"/></td></tr>';
		$no++;
	}
			
	echo '</table>';
	echo'<div class="submit">
        <input type="submit" class="button-primary" name="add_stylesheet_option" value="'.(__("+ Add another stylesheet option", "WUSC")).'" />
        <input type="submit" class="button-primary" name="delete_last_stylesheet_option" value="'.(__("- Delete last stylesheet option", "WUSC")).'" />
    </div>';	
	echo '<input type="hidden" name="wp_user_stylesheet_switcher_number" value="'.$nbStylesheets.'">';
	
	echo '<table class="form-table"><tr valign="top">
	<th scope="row">'.(__("Default stylesheet", "WUSC")).'</th>
	<td><select name="wp_user_stylesheet_switcher_default">';
	
	$noOption=0;
	foreach ($settings['options'] as $option) {	
		if (($option['file'] != '') && (($option['name'] != '')))
			echo '<option '.($settings['default']==$noOption?'selected="selected"':"").' value="'.$noOption.'">'.$option['name'].'</option>';
		$noOption++;
	}
	echo '</select> '.(__("<em>To update this dropdown list, update and save the options first</em>", "WUSC")).'</td>
	</tr>
	</table>
	<div class="submit">
        <input type="submit" class="button-primary" name="info_update" value="'.(__("Update Options &raquo;", "WUSC")).'" />
    </div>						
	</form>';
}

/*
 * Handle the options page display
 * 
 * */
function wp_user_stylesheet_switcher_options_page () 
{
    add_options_page(__("WP User Stylesheet Switcher", "WUSC"), __("WP User Stylesheet Switcher", "WUSC"), 'manage_options', 'wp_user_stylesheet_switcher', 'show_wp_user_stylesheet_switcher_options'); 
}

/*
 * Initializes the options when the plugin is installed
 * 
 * */
function wp_user_stylesheet_switcher_plugin_install()
{
    //General options
    $settings['title'] = __("Stylesheet choice", "WUSC");
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
	$settings['iconwidth'] = 30;
	$settings['iconheight'] = 30;
	add_option('wp_user_stylesheet_switcher_settings', $settings);
}

register_activation_hook(__FILE__,'wp_user_stylesheet_switcher_plugin_install');

/*
 * Adds the widget to the list of the available widgets
 * 
 * */
function wp_user_stylesheet_switcher_load_widgets()
{
	register_widget('WP_User_Stylesheet_Switcher');
}

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
				
		  $defaults = array('title' => 'Stylesheets','show_title' => 'true', 'list_title' => 'Available styles','show_list_title' => 'true','list_type' => 'dropdown');
		  $instance = wp_parse_args( (array) $instance, $defaults );

		  echo '<p><label for="'.$this->get_field_id('title').'">'.(__("Widget title", "WUSC")).' </label>
		  <input type="text" id="'.$this->get_field_id('title').'" name="'.$this->get_field_name('title').'" value="'.$instance['title'].'" size="20" maxlength="40"/>
		  </p>';
		  ?>
		  <p>
		   <label for="<?php echo $this->get_field_id('show_title'); ?>">Show widget title</label>
		   <input type="checkbox" id="<?php echo $this->get_field_id('show_title'); ?>" name="<?php echo $this->get_field_name('show_title'); ?>" <?php if ($instance['show_title']=="true") echo 'checked="checked"';
		  echo '/></p>
		  <p><label for="'.$this->get_field_id('list_title').'">'.(__("List title", "WUSC")).' </label>
		  <input type="text" id="'.$this->get_field_id('list_title').'" name="'.$this->get_field_name('list_title').'" value="'.$instance['list_title'].'" size="20" maxlength="40"/>
		  </p>';
		  ?>
		  <p>
		   <label for="<?php echo $this->get_field_id('show_list_title'); ?>">Show list title</label>
		   <input type="checkbox" id="<?php echo $this->get_field_id('show_list_title'); ?>" name="<?php echo $this->get_field_name('show_list_title'); ?>" <?php if ($instance['show_list_title']=="true") echo 'checked="checked"' ?> />
		  </p>
		  <label for="<?php echo $this->get_field_id('list_type') ?>"> <?php echo (__("List type", "WUSC")); ?> </label>
		  <select id="<?php echo $this->get_field_id('list_type'); ?>" name="<?php echo $this->get_field_name('list_type') ?>">';
		  
		    <option value="dropdown" <?php if ("dropdown"==$instance['list_type']) echo ' selected="selected"'; ?> >Dropdow list</option>
			<option value="icon" <?php if ("icon"==$instance['list_type']) echo ' selected="selected"'; ?> >Icon list</option>
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
		show_wp_user_stylesheet_switcher($instance);
		echo $after_widget;
	}
}

/*
 * Adds the settings link
 * 
 * */
function wp_user_stylesheet_switcher_add_settings_link($links, $file) 
{
	if ($file == plugin_basename(__FILE__)){
		$settings_link = '<a href="options-general.php?page=wp_user_stylesheet_switcher">'.(__("Settings", "WUSC")).'</a>';
		array_unshift($links, $settings_link);
	}
	return $links;
}

add_filter('plugin_action_links', 'wp_user_stylesheet_switcher_add_settings_link', 10, 2 );

// Insert the options page to the admin menu
add_action('admin_menu','wp_user_stylesheet_switcher_options_page');

add_action('widgets_init','wp_user_stylesheet_switcher_load_widgets');

add_action('init','wp_user_stylesheet_switcher_plugin_install');

add_shortcode('wp_user_stylesheet_switcher', 'create_wp_user_stylesheet_switcher');

add_action('wp_head', 'wp_user_stylesheet_switcher_addcss');

