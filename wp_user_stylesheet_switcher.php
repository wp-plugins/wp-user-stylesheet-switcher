<?php
/*
Plugin Name: WP User Stylesheet Switcher
Version: v0.1.0
Plugin URI: http://web.globulesverts.org
Author: Stéphane Groleau
Author URI: http://web.globulesverts.org
Description: Adds a dropdown list in the frontend to allow visitors to choose a different stylesheet.
*/

if(!isset($_SESSION)){
    session_start();
}	

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

	echo '<link type="text/css" rel="stylesheet" href="'.get_stylesheet_directory_uri().'/'.$fileCSS.'" />'."\n";
}

/*
 * Shows the dropdown list in the webpage
 * Function used directly in php
 * 
 * */
function show_wp_user_stylesheet_switcher()
{
    echo create_wp_user_stylesheet_switcher();
}

/*
 * Creates the dropdown list and returns it.
 * 
 * */
function create_wp_user_stylesheet_switcher()
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
	
	$output = '<div class="wp_user_stylesheet_switcher"><form method="post" action="'.str_replace( '%7E', '~', $_SERVER['REQUEST_URI']).'" id="wp_user_stylesheet_switcher_form'.$wp_user_stylesheet_switcher_nbform.'" name="wp_user_stylesheet_switcher_form'.$wp_user_stylesheet_switcher_nbform.'" style="display: inline">'.$settings['title'].'<select name="user_stylesheet_choice"  onchange="document.wp_user_stylesheet_switcher_form'.$wp_user_stylesheet_switcher_nbform.'.submit();">';
	
	$noOption=0;
	foreach ($settings['options'] as $option) {	
		if (($option['file'] != '') && (($option['name'] != '')))
			$output .= '<option '.($stylesheet_choice==$noOption?'selected="selected"':"").' value="'.$noOption.'">'.$option['name'].'</option>';
		$noOption++;
	}
	$output .= '</select></form></div>';
	
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
    if (isset($_POST['info_update']))
    {
    	$nonce = $_REQUEST['_wpnonce'];
		if ( !wp_verify_nonce($nonce, 'wp_user_stylesheet_switcher_update')){
			wp_die('Error! Nonce Security Check Failed! Go back to settings menu and save the settings again.');
		}

        $settings['title'] = $_POST["wp_user_stylesheet_switcher_title"];
        $settings['default'] = $_POST["wp_user_stylesheet_switcher_default"];
        
        for ($i = 0; $i<5; $i++) {
			$Option = array(
				'name' => $_POST["wp_user_stylesheet_switcher_name".$i],
				'file' => $_POST["wp_user_stylesheet_switcher_file".$i]
			);
			$settings['options'][$i] = $Option;
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
	<th scope="row">'.(__("Label for the list ", "WUSC")).'</th>
	<td><input type="text" name="wp_user_stylesheet_switcher_title" value="'.$settings['title'].'" size="20" maxlength="40"/></td>
	</tr></table>';
	
	echo '<table class="form-table">';
	$no = 0;
	foreach ($settings['options'] as $option) {
		echo '<tr valign="top"><th scope="row">'.(__("Stylesheet option".($no+1), "WUSC")).'</th><td><label for="wp_user_stylesheet_switcher_name'.$no.'">'.(__("Option name ", "WUSC")).' </label><input type="text" name="wp_user_stylesheet_switcher_name'.$no.'" value="'.$option['name'].'" size="20" maxlength="40"/></td><td><label for="wp_user_stylesheet_switcher_file'.$no.'">'.(__("CSS file name (including .CSS extension)", "WUSC")). ' </label><input type="text" name="wp_user_stylesheet_switcher_file'.$no.'" value="'.$option['file'].'" size="20" maxlength="40"/></td></tr>';
		$no++;
	}
	echo '</table>';
	
	echo '<table class="form-table"><tr valign="top">
	<th scope="row">'.(__("Stylesheet default", "WUSC")).'</th>
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
    $settings['default'] = "";
    
    for ($i = 0; $i<5; $i++) {
		$Option = array(
			'name' => '',
			'file' => ''
		);
		$settings['options'][$i] = $Option;
	}
	
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
	}
	function update($new_instance, $old_instance) {
		// processes widget options to be saved
	}
	function widget($args, $instance) {
		// outputs the content of the widget
		extract( $args );
		
		$settings = get_option('wp_user_stylesheet_switcher_settings');
		$choice_title = $settings['default'];
		if (empty($choice_title)) $choice_title = __("Stylesheet choice", "WUSC");
		
	    show_wp_user_stylesheet_switcher();
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

