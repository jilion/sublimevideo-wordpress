<?php

/**
* This class has 2 methods that inject the license and some JavaScript code to enable the loop and autoplay features.
*/
class SublimeVideoFrontendActions {

  // insert the SublimeVideo javascript into the header
  static function inject_license() {
    $stage = '';
    if (get_option('sv_player_stage') && get_option('sv_player_stage') != 'stable') {
      $stage = '-'.get_option('sv_player_stage');
    }
    echo '<script type="text/javascript" src="//cdn.sublimevideo.net/js/'.get_option('sv_site_token').$stage.'.js"></script>';
  }
}
add_action('wp_head', array( 'SublimeVideoFrontendActions', 'inject_license' ));


class SublimeVideoBackendActions {

  // The black "SV" button
  static function register_button() {
    $iframe_src = SUBLIMEVIDEO_PLUGIN_URL."tinymce/sv-insert.php?' webkitAllowFullScreen='1TB_iframe=1";
    $link = "<a href='".esc_url($iframe_src)."' id='add_sv' class='thickbox' title='Add SublimeVideo'><img src='".esc_url(SUBLIMEVIDEO_PLUGIN_URL.'assets/img/sv_toolbar_icon.png')."' alt='Add SublimeVideo' onclick='return false;' /></a>";

    print($link);
  }

}
// Add the black "SV" button in the WYSIWYG toolbar next to the media insertion buttons, with low priority (99)
if ( get_option('sv_oauth_token') && get_option('sv_site_token') ) {
  add_action('media_buttons', array('SublimeVideoBackendActions', 'register_button'), 99);
}
?>
