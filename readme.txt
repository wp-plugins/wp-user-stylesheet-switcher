=== WP User Stylesheet Switcher ===
Contributors: vgstef
Donate link: http://web.globulesverts.org
Tags: stylesheet, customize, CSS
Requires at least: 3.0
Tested up to: 3.8
Stable tag: v0.2.0
License: GPLv2 or later


Adds a dropdown list in the frontend to allow visitors to choose a different stylesheet.

== Description ==

Sometimes, we just want to offer visitors simple variations of our website theme. Sometimes, we simply want to offer a stylesheet with improved accessbility. There are plugins that let you choose a different theme, but this plugin offers you to change only the stylesheet. In the admin settings, you can configure up to 5 different stylesheets. Those possibilities are offered in a dropdown list on the front page.

The dropdown list can be put in a widget or in a page/post using the shortcode, or directly in the template using the php function.

On the frontend, when a choice is made in the dropdown list, the webpage is reload using the chosen stylesheet.

Features
* Easy installation/setup
* Up to 5 different stylesheets
* Set a default stylesheet
* Set the label for the dropdown list
* Can be used with a shortcode in a post/page, as a widget and with a php function in the theme
* Multiple instances of the dropdown list can be present on the same page.


== Installation ==

1. Place the wp_user_stylesheet_switcher folder in the wp-content/plugins folder.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Go in Settings->WP User Stylesheet Switcher to setup the alternative stylesheet files. The CSS files should be in the same folder as the other CSS files. Most commonly, this is the theme folder or the child-theme folder.
4. Tell wordpress to show the dropdown list by adding the shortcode [wp_user_stylesheet_switcher] in a page/post or put the widget in a sidebar. Alternatively, you can use the php function show_wp_user_stylesheet_switcher() in your theme. For example, put edit you footer.php file and add <?php show_wp_user_stylesheet_switcher();?> near the end to have the dropdown list is the footer of every pages of your website.


== Frequently Asked Questions ==
= Why this plugin? =

I couldn't find this solution in other plugin, so I developped it. This plugin is useful when developping a website, so we can keep a few alternative stylesheet and switch back and forth, or let a client chose his favorite one.

= How do you setup the css files for a child theme =

In my child theme folder, my style.css file only contains the link to the original theme css:  @import url("../twentythirteen/style.css");

Then my other files only need to override the original styles.


== Screenshots ==
1. Setup page in admin->settings
2. Dropdown list visible in the Frontend.

== Changelog ==
= 0.2.0 =
* No limits for the number of stylesheets to offer.

= 0.1.0 = 
* First stable version released.


== Upgrade Notice ==
= 0.2.0 =
* No limits for the number of stylesheets to offer.

= 0.1.0 = 
* First stable version released.
