=== Plugins version info ===
Tags: plugin, info, version, update
Requires at least: 3.3
Tested up to: 3.5.1
Stable tag: 0.9
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin retrieves the activated plugins data to provide feedback to the admin.

== Description ==

This plugin displays some information about the currently activated plugins to compliment the info in the plugin screen such as:

* Current intalled version of the plugin.
* Last version of the plugin available in the Wordpress.org repository.
* When was the plugin last updated.
* If the plugin is compatible with the Wordpress version you are using.

== Installation ==
1. Upload `plugins-version-info.php` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to the subpage called "Plugins version info" under the Plugins section on the Wordpress Dashboard.

== Frequently asked questions ==

= Does this plugin increase the memory usage of Wordpress? =
No, it only works when you access its page in the dashboard.

= What permissions are required to run this plugin? =
This plugin requires "manage_options" permissions, usually associated to administrators.

== Screenshots ==
1. The plugin working.

== Changelog ==

= 0.9 =
* Fixed line endings to avoid "headers already sent message".
* Styles are now queued by Wordpress.
* Table now uses Wordpress Table API
* Plugin name changed
* Errors and remote requests are better handled (hope so)
* Updated screenshot
* Texts can be lozalized

= 0.71.2 =
* First Release.
