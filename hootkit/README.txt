=== HootKit ===
Contributors: wphoot
Tags: widgets, wphoot, demo content, slider
Requires at least: 6.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 2.0.21
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.en.html

HootKit is a great companion plugin for WordPress themes by wpHoot.

== Description ==

HootKit is a great companion plugin for WordPress themes by wpHoot.
This plugin adds extra widgets and features to your theme. Though it will work with any theme, HootKit is primarily developed to work in sync with WordPress themes by wpHoot.

Get free support at <a href="https://wphoot.com/support" target="_blank">wpHoot Support</a>

== Installation ==

1. In your wp-admin (WordPress dashboard), go to Plugins Menu > Add New
2. Search for 'Hootkit' in search field on top right.
3. In the search results, click on 'Install Now' button next to Hootkit result.
4. Once the installation is complete, click Activate button.

You can also install the plugin manually by following these steps:
1. Download the plugin zip file from https://wordpress.org/plugins/hootkit/
2. In your wp-admin (WordPress dashboard), go to Plugins Menu > Add New
3. Click the 'Upload Plugin' button at the top.
4. Upload the zip file you downloaded in Step 1.
5. Once the upload is finish, click on Activate.

== Frequently Asked Questions ==

= What is the plugin license? =

This plugin is released under a GPL license.

= Which themes does HootKit work with? =

The plugin supports all themes, but works best with wpHoot Themes. A few options are available only in compatible wpHoot Themes.

== Changelog ==

= 2.0.21 =
* List widgets - Add class for style0
* Grid widgets - Add display for 1x2 non standard unit
* Update 'supports_version' to use array (refactor directory structure)

= 2.0.20 =
* Fix "Function _load_textdomain_just_in_time was called incorrectly" warning

= 2.0.19 =
* Add align options for Social Widget
* Content Grid - Display Content if only buttons available
* Icons List - Icon Color Option
* Support for new themes

= 2.0.18 =
* Fix customizer notices for latest themes

= 2.0.17 =
* Version Bump

= 2.0.16 =
* Provide option to hide WC Offscreen Cart
* Add option to enable 'classic-widgets' for supporting themes
* Add customizer options mods for supporting themes
* Add AOS support for supporting themes

= 2.0.15 =
* Minor css changes for admin screens
* Update WooCommerce label in Settings page
* Read More link text compatibility with theme customizer live changes
* Add v2 templates for supporting themes
* Fix admin wp.media bug with WooPaymets plugin (Ticket#12065)

= 2.0.14 =
* Add X (twitter) to Social Profiles

= 2.0.13 =
* Fix lightSlider script for jquery >= 3.0 (Ticket#11596)

= 2.0.12 =
* Add discord and whatsapp options in social profile

= 2.0.11 =
* Fixed bug in content-block view when only image is present Ticket#10774
* Fixed bug in slider (image) caption area view when no content to be displayed Ticket#11190

= 2.0.10 =
* Added icon options to Content Blocks widget for supporting themes
* Added social icons alt color option for supporting themes
* Added Image slider Style 3 and Subtitle option for supporting themes

= 2.0.9 =
* Updated Font Awesome to 5.15.3 (increase icons from 991 to 1608 )
* Added TikTok to Social Icons List

= 2.0.8 =
* Fix "Indirect access to variables, properties and methods" bug with older PHP version (< 7.0) on Settings screen #10560

= 2.0.7 =
* Fix widget name issue with SiteOrigins Page Builder

= 2.0.6 =
* Fixed hootSetupWidget not running on saving widget in classic widgets screen

= 2.0.5 =
* Reinstate widgets for non hoot themes
* Update to WordPress 5.8 compatibility

= 2.0.4 =
* Fix syntax issue with older PHP versions (7.0-7.3)

= 2.0.3 =
* Add 'load_hootkit' filter to selectively load plugin modules
* Refactored config['modules'] to separately allow widgets, blocks and miscallaneous
* Refactored assets class on how assets are added for loading
* Refactored helper mod data set (include assets and adminasset attributes)
* Updated structure and values stored in 'hootkit-activate' option
* Added Settings page to enable/disable modules
* Compatibility with Legacy_Widget_Block_WP5.8_beta1 (checked upto RC1)
* Fixed widget group bug (pass $this->number to js to assign correct fieldnames)

= 2.0.2 =
* Add filters to allow force load hootkit for non hoot themes

= 2.0.1 =
* Refactored dirname() usage for compatibility with PHP < 7.0

= 2.0.0 =
* Refactored internal code for more modularity for blocks implementation
* Add hk-gridunit-imglink class to content-grid-link (css bug fix for background image link)
* Added offset option to post grid widget

[See changelog.txt for all versions]