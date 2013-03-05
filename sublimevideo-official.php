<?php
/**
 * @package SublimeVideo - HTML5 Video Player
 */
/*
Plugin Name: SublimeVideo - HTML5 Video Player
Plugin URI: http://docs.sublimevideo.net/wordpress
Author: SublimeVideo
Author URI: http://sublimevideo.net
Version: 1.6.0
Description: SublimeVideo is the most reliable HTML5 Video Player on the Web. It allows your videos to play flawlessly on any device or browser and in any page.
License: GPLv2 or later
*/

define('SUBLIMEVIDEO_PLUGIN_VERSION', '1.6.0');
define('SUBLIMEVIDEO_PLUGIN_URL', plugin_dir_url( __FILE__ ));

// A class with translations
require_once dirname( __FILE__ ) . '/class-sublimevideo-locales.php';

class SublimeVideo {

  // format => [title, array_of_qualities]
  static $formats = array(
    'mp4'      => array( 'MP4',         array( 'Normal', 'HD', 'Mobile' ) ),
    'webm_ogg' => array( 'WebM or Ogg', array( 'Normal', 'HD' ) )
  );
  const FORMAT_TITLE     = 0;
  const FORMAT_QUALITIES = 1;

  // Settings
  static $user_editable_settings = array('sv_site_token', 'sv_site_domain', 'sv_player_width', 'sv_player_stage');
  static $non_editable_settings  = array('sv_oauth_token');

  // Allowed data- attributes.
  static $allowed_data_attributes = array('uid', 'name', 'settings');

  // Allowed "behaviors" (fired through the JS API).
  // These behaviors can be added in the shortcode without value, e.g.: [sublimevideo src1='' loop]
  static $allowed_behaviors = array('loop', 'autoplay');

  // Default player stage
  static $default_player_stage = 'stable';

  // Add webm to the uploadable extensions
  static function allow_webm_uploading( $existing_mimes=array() ) {
    $existing_mimes['webm'] = 'video/webm';
    return $existing_mimes;
  }

  public function install() {
    update_option('sv_player_width', SublimeVideoUtils::video_default_width());
    update_option('sv_player_stage', SublimeVideo::$default_player_stage);
  }

  public function uninstall() {
    foreach (SublimeVideo::$user_editable_settings as $setting) {
      delete_option($setting);
    }
    foreach (SublimeVideo::$non_editable_settings as $setting) {
      delete_option($setting);
    }
  }

  public static $instance = null;

  public function __construct() {
    register_activation_hook( __FILE__, array( $this, 'install' ) );
    register_deactivation_hook( __FILE__, array( $this, 'uninstall' ) );
    $this->locales  = new SublimeVideoLocales( 'en' );
    self::$instance = $this;
  }

  private static function instance() {
    if ( self::$instance ) return self::$instance;
    else return new SublimeVideo();
  }

  public static function t( $key, $lang='en' ) {
    return self::instance()->locales->t( $key, $lang );
  }
}

$sublimevideo = new SublimeVideo();

// Add webm to the authorized MIME Type for upload ( http://chrismeller.com/2007/07/modifying-allowed-upload-types-in-wordpress )
add_filter('upload_mimes', array( 'SublimeVideo', 'allow_webm_uploading' ) );

// A class with some methods to do mostly various WordPress-related stuff
require_once dirname( __FILE__ ) . '/class-sublimevideo-utils.php';

// A class with some methods that hooks into WordPress core
require_once dirname( __FILE__ ) . '/class-sublimevideo-actions.php';

// Shortcode definition / generation
require_once dirname( __FILE__ ) . '/class-sublimevideo-shortcodes.php';

// A class that renders all the UIs
require_once dirname( __FILE__ ) . '/class-sublimevideo-ui.php';

if ( is_admin() ) {
  // Wrapper to access the SublimeVideo service API
  require_once dirname( __FILE__ ) . '/class-sublimevideo-api.php';

  // Admin settings panel related stuff
  require_once dirname( __FILE__ ) . '/class-sublimevideo-admin.php';
}

?>
