=== WP User Stylesheet Switcher ===
Contributors: vgstef
Donate link: http://web.globulesverts.org
Tags: stylesheet, customize, CSS
Requires at least: 3.0
Tested up to: 3.8
Stable tag: v1.5.1
License: GPLv2 or later


Adds a list of stylesheets in the frontend to allow visitors to choose a different visual look for the website.


== Description ==

Sometimes, we just want to offer visitors simple variations of our website theme. Sometimes, we simply want to offer a stylesheet with improved accessbility. There are plugins that let you choose a different theme, but this plugin offers you to change only the stylesheet. In the admin settings, you can configure as many different stylesheets as you want. Those possibilities are offered in a list on the front page.

The list of available stylesheets can be shown in a dropdown list or as a series of icons. It can be shown using the widget or in a page/post using the shortcode, or directly in the template using the php function.

On the frontend, when a choice is made in the dropdown list, the webpage is reloaded using the chosen stylesheet.

= Plugin Features =
* Easy installation/setup
* Any number of stylesheet options
* Set a default stylesheet
* Multiple instances of stylesheet lists can be present on the same page.
* Choice between a dropdown or icon list for each list
* Can be used with a shortcode in a post/page, with the widget and with a php function in the theme
* For each list, possibility to show/hide the title
* Ready for internationalization
* Languages already available : English, French, Spanish (thanks to Andrew Kurtis)
* Complete uninstall (removes options and widgets)


== Installation ==

1. Place the wp_user_stylesheet_switcher folder in the wp-content/plugins folder.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Go in Settings->WP User Stylesheet Switcher to setup the alternative stylesheet files. The CSS files should be in the same folder as the other CSS files. Most commonly, this is the theme folder or the child-theme folder.
4. Add an optional icon file for each stylesheet if you want to use the icon list instead of the dropdown list
5. Tell Wordpress to show the stylesheet list by adding the shortcode [wp_user_stylesheet_switcher] in a page/post or put the widget in a sidebar. Alternatively, you can use the php function show_wp_user_stylesheet_switcher() in your theme, for example to have the list in the footer on every pages of your website (see details below).
6. If using icons, customize the look of the list in the CSS files.

= Options for the shortcode  =
* list_title : Used to set a title to the list of stylesheets
* list_type : Select between "dropdown" or "icon". The dropdown list is set by default.
* show_list_title : Set to "false" if you don't want any list title. "true" by default.

Example : `[wp_user_stylesheet_switcher list_title="Les styles en icons " list_type="icon" show_list_title="false"]`

If using the php function show_wp_user_stylesheet_switcher(), you can customize the list using an array of variables (similar to the shortcode) : `array('list_title'=>'Available styles', 'show_list_title'=>'true', 'list_type'=>'icon')`

By default `<?php show_wp_user_stylesheet_switcher(); ?>` will show a dropdown list with the default list title. But you can also pass an array like this :
`<?php show_wp_user_stylesheet_switcher(array('list_title'=>'Available styles', 'show_list_title'=>'true', 'list_type'=>'icon'));?>`

To customize the icon list, place the icons in your the theme folder (where the CSS are).
You can give a different look for the icon list for each CSS files.
If no icon files are specified in the admin settings, the buttons will show the name of the stylesheet.

= CSS classes to use =
* button.wp_user_stylesheet_switcher_button  : for the general buttons aspect
* img.wp_user_stylesheet_switcher_icon  : for the image inside the buttons
* button.wp_user_stylesheet_switcher_button:active  : for the button being pressed
* button.wp_user_stylesheet_switcher_active_option  : for the active stylesheet

Here an example:
`button.wp_user_stylesheet_switcher_button {
	padding: 0;
	margin: 1px;
	border: none;
}

img.wp_user_stylesheet_switcher_icon {
	border: none;
	padding: 0px;
	margin: 0px;
	width: 30px;
	height: 30px;
	vertical-align:middle;
}

button.wp_user_stylesheet_switcher_button:active {
	padding: 0;
	margin: 1px;
}

button.wp_user_stylesheet_switcher_active_option {
	padding-bottom: 1px;
	border-bottom: 3px rgb(185, 50, 7) solid;
	border-radius: 0px;
}`


== Frequently Asked Questions ==
= Why this plugin? =

I couldn't find this solution in other plugin, so I developped it. This plugin is useful when developping a website, so we can keep a few alternative stylesheet and switch back and forth, or let a client chose his favorite one.

= How do you setup the css files for a child theme =

In my child theme folder, my style.css file only contains the link to the original theme css:  @import url("../twentythirteen/style.css");

Then my other files only need to override the original styles.


== Screenshots ==
1. Setup page in admin->settings
2. Widget options
3. Dropdown list and icon list visible in the frontend


== Changelog ==
= 1.5.1 =
* Adds Spanish translation

= 1.5.0 =
* Internationalization of this plugin
* Internal update toward OOP (class for the plugin and for the widget)

= 1.0.1 =
* Set defaults to php function show_wp_user_stylesheet_switcher()

= 1.0.0 =
* Possibility to choose between an icon list of a dropdown list
* Add option to the shortcode and the widget
* Fixes layout positioning bug with Twentythirteen theme
* Manage uninstall to remove options/widgets

= 0.2.0 =
* No limits for the number of stylesheets to offer.

= 0.1.0 = 
* First stable version released.


== Upgrade Notice ==
= 1.5.1 =
* Adds Spanish translation

= 1.5.0 =
* Internationalization of this plugin
* Internal update toward OOP (class for the plugin and for the widget)

= 1.0.1 =
* Set defaults to php function show_wp_user_stylesheet_switcher()

= 1.0.0 =
* Possibility to choose between an icon list of a dropdown list
* Add option to the shortcode and the widget
* Fixes layout positioning bug with Twentythirteen theme
* Manage uninstall to remove options/widgets

= 0.2.0 =
* No limits for the number of stylesheets to offer.

= 0.1.0 = 
* First stable version released.
