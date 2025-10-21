=== Fetch SPAD ===

Contributors: pjaudiomv, bmltenabled
Tags: spad, spiritual principle a day, narcotics anonymous, na
Requires PHP: 8.1
Tested up to: 6.8
Stable tag: 1.4.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

⚠️ **DEPRECATED PLUGIN - PLEASE MIGRATE**

This plugin is being retired. Please use **Fetch Meditation** instead: https://wordpress.org/plugins/fetch-meditation/

Fetch SPAD is a plugin that pulls the Spiritual Principle A Day from https://spadna.org and puts it on your page or post.

== Description ==

⚠️ **PLUGIN RETIREMENT NOTICE**

This plugin is being retired and will no longer receive updates or support. We strongly recommend migrating to the **Fetch Meditation** plugin which provides the same SPAD functionality plus additional features.

**Migration Information:**
- New Plugin: https://wordpress.org/plugins/fetch-meditation/
- The new plugin supports both "Just For Today" (JFT) and "Spiritual Principle A Day" (SPAD) meditations
- Full backward compatibility: your existing `[spad]` shortcodes will continue to work
- Includes additional language support and timezone features
- Actively maintained and supported

**Original Description:**
Fetch SPAD is a plugin that pulls the Spiritual Principle A Day from https://spadna.org and puts it on your page or post.

*Third Party Disclosure*

This plugin pulls from the SPADNA service at https://spadna.org to get the current days meditation. This is a GET request only and no information is posted to this third party service. This service itself has no privacy policy, but you can view NAs privacy policy here https://na.org/privacy-policy/


SHORTCODE
Basic: [spad]
Layout: table, block [spad layout=&quot;&quot;]

EXAMPLES

<a href="https://www.mvana.org/a-spiritual-principle-a-day/">https://www.mvana.org/a-spiritual-principle-a-day/</a>

MORE INFORMATION

<a href="https://github.com/bmlt-enabled/fetch-spad" target="_blank">https://github.com/bmlt-enabled/fetch-spad</a>

== Installation ==

This section describes how to install the plugin and get it working.

1. Download and install the plugin from WordPress dashboard. You can also upload the entire Fetch SPAD Plugin folder to the /wp-content/plugins/ directory
2. Activate the plugin through the Plugins menu in WordPress
3. Add [spad] shortcode to your Wordpress page/post.

== Screenshots ==

1. screenshot-1.png

== Changelog ==

= 1.4.0 =

* Plugin retirement notice added - please migrate to Fetch Meditation plugin: https://wordpress.org/plugins/fetch-meditation/
* Full backward compatibility maintained with existing [spad] shortcodes in new plugin.

= 1.3.3 =

* Change SPAD url.

= 1.3.2 =

* Updated fetch meditation library.

= 1.3.1 =

* Added Larger Time Zone selection list.

= 1.3.0 =

* Added Time Zone and Language selection.

= 1.2.4 =

* Add Deutsch SPAD.

= 1.2.3 =

* Update dependencies.

= 1.2.2 =

* Added fallback server.

= 1.2.1 =

* WP Bump.

= 1.2.0 =

* Requires PHP 8.1.
* Migrated to fetch meditation lib.

= 1.1.1 =

* Added a settings link to the plugins page.

= 1.1.0 =

* Refactored codebase.

= 1.0.2 =

* Fix for multiple Page ref.

= 1.0.1 =

* CSS Tweak.

= 1.0.0 =

* Initial Release
