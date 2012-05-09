<?php

/**
* This class defines the shortcode available with the SublimeVideo plugin
*/
class SublimeVideoShortcodes {

  static function create_shortcode_from_params($params) {
    $shortcode_base = 'sublimevideo';

    // Floating lightbox feature
    // if ( isset($params['lightbox']) && $params['lightbox'] == '1' ) {
    //   $shortcode_base .= '-lightbox';
    // }

    $attributes = array();

    $attributes[] = 'poster="'.$params[$params['poster_source'].'_poster_url'].'"';

    $i = 1;
    foreach (SublimeVideo::$formats as $format => $infos) {
      foreach ($infos[SublimeVideo::FORMAT_QUALITIES] as $quality) {
        $source = $params[strtolower($format.'_'.$quality).'_source'];
        $param_to_add = $params[$source.'_'.strtolower($format.'_'.$quality)];
        if ($param_to_add != '') {
          $quality_prefix = !in_array(strtolower($quality), array('normal', 'mobile')) ? '('.strtolower($quality).')' : '';
          $attributes[] = 'src'.$i.'="'.$quality_prefix.$param_to_add.'"';
        }
        $i++;
      }
    }

    $dimensions = array( 'width', 'height' );
    foreach ($dimensions as $dimension) {
      $attributes[] = $dimension.'="'.$params['final_'.$dimension].'"';
    }

    if (isset($params['data_uid'])) $attributes[] = 'data_uid="'.$params['data_uid'].'"';
    if (isset($params['data_name'])) $attributes[] = 'data_name="'.$params['data_name'].'"';

    foreach (SublimeVideo::$behaviors as $behavior) {
      if (isset($params[$behavior]) && $params[$behavior] == '1') $attributes[] = $behavior;
    }

    return '['.$shortcode_base.' '.join(" ", $attributes).']';
  }

  // function to process the shortcode
  static function normal($attributes) {
    $behaviors = array();
    $i = 0;
    $total = count(SublimeVideo::$behaviors);
    while (isset($attributes[$i])) {
      if (in_array($attributes[$i], SublimeVideo::$behaviors)) {
        $behaviors[] = $attributes[$i];
      }
      $i++;
    }

    $atts = shortcode_atts(array(
      'id'        => '',
      'class'     => 'sublime',
      'style'     => '',
      'width'     => esc_attr(get_option('sv_player_width')),
      'height'    => '',
      'poster'    => '',
      'preload'   => 'none',
      'data_uid'  => '',
      'data_name' => ''
    ), $attributes);

    $id        = $atts['id'] != '' ? " id='".$atts['id']."'" : '';
    $class     = $atts['class'] != '' ? " class='".$atts['class']."'" : '';
    $style     = $atts['style'] != '' ? " style='".$atts['style']."'" : '';
    $width     = " width='".$atts['width']."'";
    $height    = " height='".$atts['height']."'";
    $poster    = " poster='".$atts['poster']."'";
    $preload   = " preload='".$atts['preload']."'";
    $data_uid  = $atts['data_uid'] != '' ? " data-uid='".$atts['data_uid']."'" : '';
    $data_name = $atts['data_name'] != '' ? " data-name='".$atts['data_name']."'" : '';
    $behaviors = !empty($behaviors) ? " data-sublime-wp='".join(' ', $behaviors)."'" : '';

    $html = "<video".$id.$class.$style.$width.$height.$poster.$preload.$data_uid.$data_name.$behaviors.">\n";
    foreach (SublimeVideoUtils::extract_shortcode_src_from_hash($attributes) as $source) {
      $data_quality = $source['quality'] != '' ? " data-quality='".$source['quality']."'" : '';
      $html .= "\t<source src='".$source['src']."'".$data_quality." />\n";
    }
    $html .= "</video>\n";

    return $html;
  }

  // Process the shortcode for the floating lightbox feature
  static function lightbox($attributes, $content='') {
    $atts = array_merge(array(
      'class' => 'sublime zoom',
      'style' => 'display:none;'
    ), $attributes);

    return "<a class='sublime' href=''>$content</a>".SublimeVideoShortcodes::normal($atts);
  }

}

// tell wordpress to register the sublimevideo shortcode
add_shortcode('sublimevideo-lightbox', array('SublimeVideoShortcodes', 'lightbox'));

// tell wordpress to register the sublimevideo shortcode
add_shortcode('sublimevideo', array('SublimeVideoShortcodes', 'normal'));

?>