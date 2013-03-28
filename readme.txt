=== SublimeVideo - HTML5 Video Player ===
Contributors: sublimevideo
Tags: html5, video, player, sublimevideo, horizon, framework
Requires at least: 3.0
Tested up to: 3.5.1
Stable tag: 1.8.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

SublimeVideo is the most reliable HTML5 Video Player on the Web. It allows your videos to play flawlessly on any device or browser and in any page.

== Description ==

SublimeVideo is the most reliable, pain-free way to truly enable HTML5 Video on your site, and allows your videos to play flawlessly on any device or browser and in any existing web page.

- Built-in support for video features including [HD Switching](http://sublimevideo.net/modular-player#hd), [Playlists](http://sublimevideo.net/modular-player#playlist), [Lightboxes](http://sublimevideo.net/modular-player#lightbox), [Keyboard Control](http://sublimevideo.net/modular-player#keyboard) and [much more](http://sublimevideo.net/modular-player#player_features).
- Many add-ons available such as [Your Custom Logo](http://sublimevideo.net/modular-player#on_video_logo), [Social Sharing](http://sublimevideo.net/modular-player#social_sharing), [Real-Time Statistics](http://sublimevideo.net/modular-player#real_time_stats), [Embed](http://sublimevideo.net/modular-player#embed) and [much more](http://sublimevideo.net/modular-player#player_features).
- The [cloud model](http://sublimevideo.net/modular-player#service_features) ensures SublimeVideo Player is always up-to-date and makes it quick and easy to use and add to your site.

[Sign up for a free and unlimited account now](http://sublimevideo.net/signup)!

This plugin allows you to easily integrate SublimeVideo Player with your WordPress site. It provides an [easy-to-use visual editor](http://wordpress.org/extend/plugins/sublimevideo-official/screenshots) that will help you generate shortcodes for your videos.

Please find the full documentation at [http://docs.sublimevideo.net/wordpress](http://docs.sublimevideo.net/beta/wordpress).

== Installation ==

1. Download the plugin from the WordPress Plugin Directory and upload the `sublimevideo-official` folder to the `/wp-content/plugins/` directory. Alternatively, you can install the plugin via the Install Plugins section in your WordPress admin area.
2. Activate the plugin through the ‘Plugins’ menu item in your WordPress admin area.
3. Go to the SublimeVideo plugin settings page and authorize the plugin to access some of your SublimeVideo data.
4. Once the authorization is done, you will see your SublimeVideo sites listed in a select menu in the plugin settings page; you’ll have to choose the one matching the domain of your WordPress site. If you only have one site registered in your SublimeVideo account, it’ll be selected automatically.
5. In the plugin setting page you can also set the default width for all the videos you will embed.
6. Go to the post editor, you should see the new "SV" button next to the "Add Media" button – the plugin’s installation is now complete. If you don’t see it, please refer to the [Troubleshooting](http://docs.sublimevideo.net/wordpress#troubleshooting) section.

Please find the full documentation at [http://docs.sublimevideo.net/wordpress](http://docs.sublimevideo.net/beta/wordpress).

== Screenshots ==

1. The plugin's video embed builder.

== Frequently Asked Questions ==

= My theme is responsive, how can I make my videos responsive too? =

Simply add `settings="autoresize:fit"` (or `settings="autoresize:fill"`) in your shortcode, as follow:

`[sublimevideo src1="..." width="..." height="..." settings="autoresize:fit"]`

Learn more about the `autoresize` player settings in the [SublimeVideo documentation](http://docs.sublimevideo.net/settings/player-settings#autoresize).

= How can I play a YouTube video with the SublimeVideo WordPress plugin? =

Let's say you want to play the following video: https://www.youtube.com/watch?v=abcd1234

Simply add `settings="youtube-id:abcd1234"` in your shortcode, as follow:

`[sublimevideo src1="..." width="..." height="..." settings="youtube-id:abcd1234"]`

Learn more about the `youtube-id` player settings in the [SublimeVideo documentation](http://docs.sublimevideo.net/settings/player-settings#youtube-id).

= Why don't I see any site in the Settings page? =

* It's probably because your WordPress site's domain is not properly registered in your [SublimeVideo](https://my.sublimevideo.net/sites) account. You may find other possible reasons for this problem in the [Troubleshooting](http://docs.sublimevideo.net/troubleshooting#not-properly-registered) page.
* Another possibility is that you previously linked the plugin with another [SublimeVideo](https://my.sublimevideo.net/sites) account account. The easiest solution is to deactivate the plugin and re-activate it right away. Then follow the [Installation steps](http://docs.sublimevideo.net/wordpress#installation) (skipping the two first steps, obviously).

== Changelog ==

= 1.8.0 =
* Add support for [YouTube](http://docs.sublimevideo.net/youtube) videos in the visual editor.
* Add support for the [`title`](http://docs.sublimevideo.net/wordpress#shortcodes-attributes-video-metadata-settings) shortcode attribute.
* Deprecate the [`data_name`](http://docs.sublimevideo.net/wordpress#shortcodes-attributes-video-metadata-settings) shortcode attribute.

* Add support for YouTube videos in the visual editor.
* Add support for the `title` shortcode attribute.

= 1.7.0 =
* New `lightbox_settings` shortcode attribute to allow to pass [lightbox specific settings](http://docs.sublimevideo.net/settings/lightbox-settings).
* When a `youtube-id` is set in the `settings` attribute, the `uid` is set to the YouTube video id.

= 1.6.0 =
* Unique DOM id are now generated for all new video elements.
* Unique data-uid are now generated for all new video elements.
* Allow nested shortcode for the "src" attributes.
* Remove code that is not necessary anymore with the new SublimeVideo player.
* Fix a "Undefined property" warning visible in debug mode.
* Refactor the SublimeVideoShortcodes class.

= 1.5.2 =
* Fix a warning visible in debug mode.
* Fix a button design issue.

= 1.5.1 =
* Fix issue: Added a missing file.

= 1.5.0 =
* Add support for the [New SublimeVideo Player](http://sublimevideo.net/modular-player).
* Add support for the `uid`, `name` and `settings` attributes (mapping to `data-uid`, `data-name` and `data-settings` HTML attribute). See http://docs.sublimevideo.net/settings/usage to learn more about these attributes.
* Improvement: From now on, when you deactivate the plugin, its settings are deleted.

= 1.4.1 =
* Fix an issue that was occuring for old PHP versions.

= 1.4.0 =
* Add support for the `data_settings` attribute (mapping to the `data-settings` HTML attribute).

= 1.3.2 =
* Fix issue: Autoplay and loop features didn't work in IE < 9.

= 1.3.1 =
* Update: The lightbox shortcode is now `[sublimevideo-lightbox][/sublimevideo-lightbox]`.

= 1.3.0 =
* Fix issue: `src1` declared after `src2` was leading to a wrong sources' order in the generated code.
* Add support for the floating lightbox feature in shortcode, see http://docs.sublimevideo.net/wordpress for usage.

= 1.2.1 =
* Fix issue: HD switch wasn't displayed in the live preview.
* Add support for the `style` attribute in shortcode, see http://docs.sublimevideo.net/wordpress for usage.

= 1.2.0 =
* Fix issue: SSL certificate verification.
* Fix issue: Custom port was preventing the plugin from working properly.
* Add support for the `data_uid` and `data_name` attributes (mapping to `data-uid` and `data-name` HTML attribute). See http://docs.sublimevideo.net/optimize-for-stats for details.
* Add support for HD sources. See http://docs.sublimevideo.net/hd-switching for details.

= 1.1.0 =
* Update for the new API endpoint which is https://api.sublimevideo.net.

= 1.0.0 =
* Initial release.
