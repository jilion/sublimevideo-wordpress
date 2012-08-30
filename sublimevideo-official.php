<?php
/**
 * @package SublimeVideo - HTML5 Video Player
 */
/*
Plugin Name: SublimeVideo - HTML5 Video Player
Plugin URI: http://docs.sublimevideo.net/wordpress
Author: SublimeVideo
Author URI: http://sublimevideo.net
Version: 1.4.0
Description: SublimeVideo is the most reliable HTML5 Video Player on the Web. It allows your videos to play flawlessly on any device or browser and in any page.
License: GPLv2 or later
*/

define('SUBLIMEVIDEO_PLUGIN_VERSION', '1.4.0');
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

  // Allowed data- attributes.
  static $data_attributes = array('uid', 'name', 'settings');

  // Allowed "behaviors" (fired through the JS API).
  // These behaviors can be added in the shortcode without value, e.g.: [sublimevideo src1='' loop]
  static $behaviors = array('loop', 'autoplay');

  // Add webm to the uploadable extensions
  static function allow_webm_uploading( $existing_mimes=array() ) {
    $existing_mimes['webm'] = 'video/webm';
    return $existing_mimes;
  }

  public static $instance = null;

  public function __construct() {
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

if ( is_admin() ) {
  // Wrapper to access the SublimeVideo service API
  require_once dirname( __FILE__ ) . '/class-sublimevideo-api.php';

  // Admin settings panel related stuff
  require_once dirname( __FILE__ ) . '/class-sublimevideo-admin.php';
}

?>
