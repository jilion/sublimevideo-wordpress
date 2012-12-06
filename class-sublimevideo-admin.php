<?php
class SublimeVideoAdmin {

  static function init() {
    wp_register_style('sublimevideo.css', SUBLIMEVIDEO_PLUGIN_URL.'assets/css/settings.css');
    wp_enqueue_style('sublimevideo.css');

    $settings = array('sv_site_token', 'sv_site_domain', 'sv_player_width', 'sv_player_stage');
    // register our settings
    foreach ($settings as $setting) {
      register_setting('sv-settings-group', $setting);
    }
  }

  public function __construct($api) {
    $this->api = $api;
    if (!get_option('sv_player_width')) update_option('sv_player_width', SublimeVideoUtils::video_default_width());
    if (!get_option('sv_player_stage')) update_option('sv_player_stage', SublimeVideo::$default_player_stage);
  }

  /* We need to handle hostname with subdomains (even www.).
  *  In my.sublimevideo.net, the "special" www. subdomain is removed though...
  */
  public function available_sites() {
    if (!$this->available_sites) {
      $sites = $this->api->sites();
      $this->available_sites = array();

      if (isset($sites)) {
        $http_host = self::clean_http_host();
        foreach ($sites as $site) {
          $added = false;
          $i = 0;
          $public_domains = array_merge(array($site->main_domain), $site->extra_domains);
          while (!$added && $i < count($public_domains)) {
            if (
                ($site->wildcard && strstr($http_host, $public_domains[$i])) ||
                ($http_host == $public_domains[$i])
               ) {
              $this->available_sites[] = $site;
              $added = true;
            }
            $i++;
          }

          $i = 0;
          while (!$added && $i < count($site->dev_domains)) {
            if ($http_host == $site->dev_domains[$i]) {
              $this->available_sites[] = $site;
              $added = true;
            }
            $i++;
          }
        }
      }
    }
    return $this->available_sites;
  }

  /* We need to handle hostname with subdomains (even www.).
  *  In my.sublimevideo.net, the "special" www. subdomain is removed though...
  */
  public function alpha_stage_accessible() {
    if (!$this->alpha_stage_accessible) {
      $available_sites = self::available_sites();

      $this->alpha_stage_accessible = false;
      if (isset($available_sites)) {
        foreach ($available_sites as $available_site) {
          if ($available_site->accessible_stage == 'alpha') {
            $this->alpha_stage_accessible = true;
            break;
          }
        }
      }
    }
    return $this->alpha_stage_accessible;
  }

  static function clean_http_host() {
    return str_replace(':'.$_SERVER['SERVER_PORT'], '', preg_replace('/(www\.)(.+)/i', '$2', $_SERVER['HTTP_HOST']));
  }

  static function update_site_info($site=array()) {
    $token  = isset($site->token) ? $site->token : '';
    $domain = isset($site->main_domain) && isset($site->wildcard) && isset($site->path) ? SublimeVideoUtils::site_with_wildcard_and_path($site) : '';

    update_option('sv_site_token', $token);
    update_option('sv_site_domain', $domain);
  }

}

// Register the settings function
add_action('admin_init', array('SublimeVideoAdmin', 'init'));

// Add an item in the plugins menu
add_action('admin_menu', array('SublimeVideoUI', 'config_page'));

// Add a link in the plugins list
add_filter('plugin_action_links', array('SublimeVideoUI', 'plugin_action_links'), 10, 2);

?>
