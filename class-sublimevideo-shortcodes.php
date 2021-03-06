<?php

/**
* This class defines the shortcode available with the SublimeVideo plugin
*/
class SublimeVideoShortcodes {

  static function create_shortcode_from_params($params) {
    $shortcode_base = 'sublimevideo';
    $attributes     = array();
    if ($params[$params['poster_source'].'_poster_url']) {
      $attributes[] = 'poster="'.$params[$params['poster_source'].'_poster_url'].'"';
    }

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
    if (isset($uid)) $attributes[] = 'uid="'.$uid.'"';
    if (isset($uid)) $attributes[] = 'id="'.$uid.'"';
    if ($params['source_origin'] == 'youtube') {
      $attributes[] = 'settings="youtube-id:'.$params['source_youtube_src'].'"';
    }

    $dimensions = array('width' => 640, 'height' => 360);
    foreach ($dimensions as $dimension => $default_size) {
      $size = $params['final_'.$dimension] ? $params['final_'.$dimension] : $default_size;
      $attributes[] = $dimension.'="'.$size.'"';
    }
    var_dump($attributes);

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

    foreach (SublimeVideo::$allowed_data_attributes as $shortcode_attribute => $data_attribute) {
      $array[$shortcode_attribute] = '';
      $array['data_'.$shortcode_attribute] = '';
    }

    return $array;
  }

  static function behaviors($attributes) {
    $behaviors       = array();
    $data_attributes = array();

    foreach (SublimeVideo::$allowed_behaviors as $behavior) {
      if (in_array($behavior, $attributes)) {
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

  // function to process the shortcode
  static function video($attributes) {
    $shortcode = new SublimeVideoShortcodes($attributes);

    return $shortcode->generate_video_code();
  }

  // Process the shortcode for the floating lightbox feature
  static function lightbox($atts, $content='') {
    $attributes = array_merge(array(
      'style' => 'display:none;',
      'class' => '',
      'lightbox_settings' => ''
    ), $atts);
    $video = new SublimeVideoShortcodes($attributes);

    return "<a class='sublime' href='#".$video->video_attributes['id']."' data-settings='".$attributes['lightbox_settings']."'>".do_shortcode($content)."</a>".$video->generate_video_code();
  }

  public function __construct($attributes=array()) {
    $this->video_attributes = shortcode_atts(self::default_video_attributes(), $attributes);
    $this->video_settings   = shortcode_atts(self::default_video_settings(), $attributes);
    $this->video_behaviors  = self::behaviors($attributes);
    $this->sources          = self::sources($attributes);

    $this->generate_id();
    $this->generate_uid();
  }

  public function generate_id() {
    if ($this->video_attributes['id'] == '') {
      if (preg_match('/youtube-id:([^;\s]+)/i', $this->video_settings['settings'], $matches)) {
        $this->video_attributes['id'] = $matches[1];
      } else if ($this->sources[0]) {
        $this->video_attributes['id'] = self::generateUID($this->sources[0]['src']);
      }
    }
  }

  public function generate_uid() {
    if ($this->video_settings['uid'] == '' && $this->video_attributes['id'] != '') {
      $this->video_settings['uid'] = $this->video_attributes['id'];
    }
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

    foreach (SublimeVideo::$allowed_data_attributes as $shortcode_attribute => $data_attribute) {
      $data = null;
      if ($this->video_settings[$shortcode_attribute] != '') {
        $data = $this->video_settings[$shortcode_attribute];
      } else if ($this->video_settings['data_'.$shortcode_attribute] != '') {
        $data = $this->video_settings['data_'.$shortcode_attribute];
      }

      if ($data) $data_attributes[] = $data_attribute."='".$data."'";
    }

    return join(' ', $data_attributes);
  }

}

// tell wordpress to register the sublimevideo shortcode
add_shortcode('sublimevideo-lightbox', array('SublimeVideoShortcodes', 'lightbox'));

// tell wordpress to register the sublimevideo shortcode
add_shortcode('sublimevideo', array('SublimeVideoShortcodes', 'video'));

?>
