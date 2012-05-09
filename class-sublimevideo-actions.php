<?php

/**
* This class has 2 methods that inject the license and some JavaScript code to enable the loop and autoplay features.
*/
class SublimeVideoFrontendActions {

  // insert the SublimeVideo javascript into the header
  static function inject_license() {
    echo '<script type="text/javascript" src="'.(is_ssl() ? 'https://4076.voxcdn.com' : 'http://cdn.sublimevideo.net').'/js/'.get_option('sv_site_token').'.js"></script>';
  }

  // insert JavaScript before </body> to enable autoplay and/or loop feature using SublimeVideo's JS API
  static function inject_javascript_integration() {
    if ( !is_admin() || is_preview() ) {
      echo <<<_end_
      <script type='text/javascript'>
        sublimevideo.ready(function(){for(var c=document.getElementsByTagName("video"),a,d,e=[],b=0;b<c.length;b++)if(a=c[b].getAttribute("data-sublime-wp"),void 0!=a){a=a.split(/\s/);for(var f=0;f<a.length;f++)void 0==d&&-1!=a[f].indexOf("autoplay")&&(d=c[b]),-1!=a[f].indexOf("loop")&&e.push(c[b])}d&&sublimevideo.prepareAndPlay(d);if(0<e.length)sublimevideo.onEnd(function(a){for(var b=0;b<e.length;b++)e[b]==a.element&&sublimevideo.play(a.element)})});
      </script>
_end_;
  // // http://closure-compiler.appspot.com/home
  // <script type='text/javascript'>
  //   sublimevideo.ready(function() {
  //     var videos = document.getElementsByTagName('video'), settings, autoplayVideo, loopVideo = [];
  //
  //     for (var i = 0; i < videos.length; i++) {
  //       settings = videos[i].getAttribute('data-sublime-wp');
  //       if (settings != undefined) {
  //         settings = settings.split(/\s/);
  //         for (var j = 0; j < settings.length; j++) {
  //           if (autoplayVideo == undefined && settings[j].indexOf('autoplay') != -1) autoplayVideo = videos[i];
  //           if (settings[j].indexOf('loop') != -1) loopVideo.push(videos[i]);
  //         }
  //       }
  //     };
  //
  //     if (autoplayVideo) sublimevideo.prepareAndPlay(autoplayVideo);
  //
  //     if (loopVideo.length > 0) {
  //       sublimevideo.onEnd(function(sv){
  //         for (var i = 0; i < loopVideo.length; i++) {
  //           if (loopVideo[i] == sv.element) sublimevideo.play(sv.element);
  //         }
  //       });
  //     }
  //   });
  // </script>
    }
  }
}
add_action('wp_head', array( 'SublimeVideoFrontendActions', 'inject_license' ));
add_action('wp_footer', array( 'SublimeVideoFrontendActions', 'inject_javascript_integration' ));


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