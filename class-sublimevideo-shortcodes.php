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

    foreach (SublimeVideo::$data_attributes as $data_attribute) {
      if (isset($params['data_'.$data_attribute])) $attributes[] = 'data-'.$data_attribute.'="'.$params['data_'.$data_attribute].'"';
    }

    foreach (SublimeVideo::$behaviors as $behavior) {
      if (isset($params[$behavior]) && $params[$behavior] == '1') $attributes[] = $behavior;
    }

    return '['.$shortcode_base.' '.join(" ", $attributes).']';
  }

  // function to process the shortcode
  static function normal($attributes) {
    $atts = shortcode_atts(self::default_array(), $attributes);

    $html = "<video ".self::attributes($atts)." ".self::data_attributes($atts)." ".self::behaviors($attributes).">\n";
    foreach (self::extract_sources($attributes) as $source) {
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

  static function default_array() {
    $array = array(
      'id'      => '',
      'class'   => 'sublime',
      'style'   => '',
      'width'   => esc_attr(get_option('sv_player_width')),
      'height'  => '',
      'poster'  => '',
      'preload' => 'none'
    );

    foreach (SublimeVideo::$data_attributes as $data_attribute) {
      $array['data_'.$data_attribute] = '';
    }

    return $array;
  }

  static function attributes($attributes) {
    $attrs = array();

    foreach (array('id', 'class', 'style', 'width', 'height', 'poster', 'preload') as $key) {
      if ($attributes[$key] != '') $attrs[] = $key."='".$attributes[$key]."'";
    }

    return join(" ", $attrs);
  }

  static function data_attributes($attributes) {
    $data = array();

    foreach (SublimeVideo::$data_attributes as $data_attribute) {
      if ($attributes['data_'.$data_attribute] != '') $data[] = "data-".$data_attribute."='".$attributes['data_'.$data_attribute]."'";
    }

    return join(" ", $data);
  }

  static function behaviors($attributes) {
    $behaviors = array();

    foreach (SublimeVideo::$behaviors as $behavior) {
      if (in_array($behavior, $attributes)) $behaviors[] = $behavior;
    }

    return empty($behaviors) ? "" : "data-sublime-wp='".join(' ', $behaviors)."'";
  }

  static function extract_sources($hash) {
    ksort($hash);
    $sources = array();
    foreach ($hash as $key => $value) {
      if (preg_match('/^src/i', $key) && $value != '') {
        preg_match('/(\((\w+)\))?(.+)/', $value, $matches);
        $sources[] = array('quality' => $matches[2], 'src' => $matches[3]);
      }
    }

    return $sources;
  }

}

// tell wordpress to register the sublimevideo shortcode
add_shortcode('sublimevideo-lightbox', array('SublimeVideoShortcodes', 'lightbox'));

// tell wordpress to register the sublimevideo shortcode
add_shortcode('sublimevideo', array('SublimeVideoShortcodes', 'normal'));

?>
