<?php

/**
* This class defines the shortcode available with the SublimeVideo plugin
*/
class SublimeVideoShortcodes {

  static function create_shortcode_from_params($params) {
    $shortcode_base = 'sublimevideo';
    $attributes     = array();
    $attributes[]   = 'poster="'.$params[$params['poster_source'].'_poster_url'].'"';

    $i = 1;
    foreach (SublimeVideo::$formats as $format => $infos) {
      foreach ($infos[SublimeVideo::FORMAT_QUALITIES] as $quality) {
        $origin = $params[strtolower($format.'_'.$quality).'_source'];
        $source = $params[$origin.'_'.strtolower($format.'_'.$quality)];
        if ($source != '') {
          if (!isset($uid)) $uid = self::generateUID($source);
          $quality_prefix = !in_array(strtolower($quality), array('normal', 'mobile')) ? '('.strtolower($quality).')' : '';
          $attributes[] = 'src'.$i.'="'.$quality_prefix.$source.'"';
        }
        $i++;
      }
    }
    $attributes[] = 'uid="'.$uid.'"';
    $attributes[] = 'id="'.$uid.'"';

    $dimensions = array( 'width', 'height' );
    foreach ($dimensions as $dimension) {
      $attributes[] = $dimension.'="'.$params['final_'.$dimension].'"';
    }

    return '['.$shortcode_base.' '.join(" ", $attributes).']';
  }

  static function generateUID($string) {
    return dechex(crc32($string));
  }

  static function default_video_attributes() {
    $array = array(
      'id'      => '',
      'class'   => 'sublime',
      'style'   => '',
      'width'   => esc_attr(get_option('sv_player_width')),
      'height'  => '',
      'poster'  => '',
      'preload' => 'none'
    );

    return $array;
  }

  static function default_video_settings() {
    $array = array();

    foreach (SublimeVideo::$allowed_data_attributes as $data_attribute) {
      $array[$data_attribute] = '';
      $array['data_'.$data_attribute] = '';
    }

    return $array;
  }

  static function behaviors($attributes) {
    $behaviors       = array();
    $data_attributes = array();

    foreach (SublimeVideo::$allowed_behaviors as $behavior) {
      if (in_array($behavior, $attributes)) {
        if (get_option('sv_player_stage') == 'stable') {
          $behaviors[] = $behavior;
        }
        else {
          switch ($behavior) {
            case 'autoplay':
              $data_attributes[] = "data-autoplay='true'";
              break;
            case 'loop':
              $data_attributes[] = "data-on-end='replay'";
              break;
          }
        }
      }
    }

    return empty($behaviors) ? join(' ', $data_attributes) : "data-sublime-wp='".join(' ', $behaviors)."'";
  }

  static function sources($hash) {
    ksort($hash);
    $sources = array();
    foreach ($hash as $key => $value) {
      if (preg_match('/^src/i', $key) && $value != '') {
        preg_match('/(\((\w+)\))?(.+)/', $value, $matches);
        $sources[] = array('quality' => $matches[2], 'src' => do_shortcode($matches[3]));
      }
    }

    return $sources;
  }

  public function __construct($attributes=array()) {
    $this->video_attributes = shortcode_atts(self::default_video_attributes(), $attributes);
    $this->video_settings   = shortcode_atts(self::default_video_settings(), $attributes);
    $this->video_behaviors  = self::behaviors($attributes);
    $this->sources          = self::sources($attributes);

    if ($this->video_attributes['id'] == '') $this->video_attributes['id'] = self::generateUID($this->sources[0]['src']);
    if ($this->video_settings['uid'] == '') $this->video_settings['uid'] = $this->video_attributes['id'];
  }

  // function to process the shortcode
  static function video($attributes) {
    $shortcode = new SublimeVideoShortcodes($attributes);

    return $shortcode->generate_video_code();
  }

  public function generate_video_code() {
    $html = "<video ".$this->write_video_attributes()." ".$this->write_data_settings()." ".$this->video_behaviors.">\n";
    foreach ($this->sources as $source) {
      $data_quality = $source['quality'] != '' ? " data-quality='".$source['quality']."'" : '';
      $html .= "\t<source src='".$source['src']."'".$data_quality." />\n";
    }
    $html .= "</video>\n";

    return $html;
  }

  function write_video_attributes() {
    $attrs = array();

    foreach (array('id', 'class', 'style', 'width', 'height', 'poster', 'preload') as $key) {
      if ($this->video_attributes[$key] != '') $attrs[] = $key."='".$this->video_attributes[$key]."'";
    }

    return join(' ', $attrs);
  }

  function write_data_settings() {
    $data_attributes = array();

    foreach (SublimeVideo::$allowed_data_attributes as $data_attribute) {
      $data = null;
      if ($this->video_settings[$data_attribute] != '') {
        $data = $this->video_settings[$data_attribute];
      } else if ($this->video_settings['data_'.$data_attribute] != '') {
        $data = $this->video_settings['data_'.$data_attribute];
      }

      if ($data) $data_attributes[] = "data-".$data_attribute."='".$data."'";
    }

    return join(' ', $data_attributes);
  }

  // Process the shortcode for the floating lightbox feature
  static function lightbox($atts, $content='') {
    $attributes = array_merge(array(
      'style' => 'display:none;',
      'class' => ''
    ), $atts);
    if (get_option('sv_player_stage') == 'stable') {
      $attributes['class'] = 'sublime lightbox';
    }

    $video = new SublimeVideoShortcodes($attributes);

    return "<a class='sublime' href='#".$video->video_attributes['id']."'>".do_shortcode($content)."</a>".$video->generate_video_code();
  }

}

// tell wordpress to register the sublimevideo shortcode
add_shortcode('sublimevideo-lightbox', array('SublimeVideoShortcodes', 'lightbox'));

// tell wordpress to register the sublimevideo shortcode
add_shortcode('sublimevideo', array('SublimeVideoShortcodes', 'video'));

?>
