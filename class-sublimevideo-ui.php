<?php
class SublimeVideoUI {

  static function config_page() {
    if (function_exists('add_submenu_page'))
      add_submenu_page('plugins.php', __('SublimeVideo Settings'), __('SublimeVideo Settings'), 'manage_options', 'sublimevideo-settings', array('SublimeVideoUI', 'settings_page'));
  }

  static function plugin_action_links($links, $file) {
    if ($file == plugin_basename(dirname(__FILE__).'/sublimevideo-official.php')) {
      $links[] = '<a href="plugins.php?page=sublimevideo-settings">'.__('Settings').'</a>';
    }

    return $links;
  }

  // create the actual contents of the settings page
  static function settings_page() {
    global $sublimevideo;

    if (!current_user_can('manage_options')) wp_die(__('You do not have sufficient permissions to access this page.'));

    if (false === strpos($_SERVER['QUERY_STRING'], 'page=sublimevideo-settings')) return;

    $api   = new SublimeVideoAPI();
    $admin = new SublimeVideoAdmin($api);

    $redirect_uri = admin_url('plugins.php?page=sublimevideo-settings');

    // received authorization code, exchange it for an access token
    if (isset($_GET['code'])) {
      $params   = array('code' => $_GET['code'], 'redirect_uri' => $redirect_uri);
      $response = $api->accessToken($params);

      if (isset($response->error)) {
        echo "The following error occurred: ".$response->error;
      }
      elseif (isset($response->access_token)) {
        update_option('sv_oauth_token', $response->access_token);
        $api = new SublimeVideoAPI(); // Refresh the API with the latest API token
      }
    }
  ?>
  <div id="sv_settings">
    <div class="wrap">
      <a href="http://sublimevideo.net" onclick='window.open(this); return false'><h2 class="logo"><span>SublimeVideo</span></h2></a>
      <?php
      if (isset($_GET['settings-updated']) && $_GET['settings-updated'] == 'true') {
        echo $sublimevideo->t('settings_saved');
      }
      ?>
      <div class="breakline"></div>
      <?php
      $available_sites = $admin->available_sites();
      $clean_http_host = SublimeVideoAdmin::clean_http_host();

      if (isset($_GET['debug'])) {
        echo "OAUTH TOKEN: <code>".get_option('sv_oauth_token')."</code>";
        echo "<br />LAST API CALL RESP.: <code>".$api->code."</code>";
        echo "<br />SITE DOMAIN: <code>".get_option('sv_site_domain')."</code>";
        echo "<br />SITE TOKEN: <code>".get_option('sv_site_token')."</code>";
        echo "<br />DEFAULT PLAYER WIDTH: <code>".get_option('sv_player_width')."</code>";
        echo "<br />PLAYER STAGE: <code>".get_option('sv_player_stage')."</code>";
      }

      if ($api->code == 401) {
        echo SublimeVideoUtils::authorize_form($api->authorizationUrl($redirect_uri));
      }
      else {
      ?>
        <form method="post" action="options.php">
          <?php settings_fields('sv-settings-group'); ?>
          <h3>Site</h3>
          <div>
            <?php
            // No sites is available on my.sublimevideo.net
            if ($api->code != 200) {
              echo $sublimevideo->t('api_issue');
              echo "<p class='desc'>".sprintf($sublimevideo->t('site'), get_option('sv_site_domain'), strlen(get_option('sv_site_domain')))." ".sprintf($sublimevideo->t('view_site'), get_option('sv_site_token'))."</p>";
              echo "<input type='hidden' name='sv_site_token' value='".get_option('sv_site_token')."' />";
              echo "<input type='hidden' name='sv_site_domain' value='".get_option('sv_site_domain')."' />";
            }
            elseif (empty($available_sites)) {
              echo sprintf($sublimevideo->t('no_sites'), $clean_http_host, $clean_http_host);
              SublimeVideoAdmin::update_site_info(); // clear current token and domain
            }

            // Only one site available on my.sublimevideo.net
            elseif (1 == count($available_sites)) {
              // No token yet and only one site, save it right away!
              if (!get_option('sv_site_token') || get_option('sv_site_domain') != SublimeVideoUtils::site_with_wildcard_and_path($available_sites[0])) SublimeVideoAdmin::update_site_info($available_sites[0]);

              echo sprintf($sublimevideo->t('site'), get_option('sv_site_domain'), strlen(get_option('sv_site_domain')));
              echo "<input type='hidden' name='sv_site_token' value='".get_option('sv_site_token')."' />";
              echo "<input type='hidden' name='sv_site_domain' value='".get_option('sv_site_domain')."' />";
              echo sprintf($sublimevideo->t('another_site?'), 'site', $clean_http_host);
            }

            // Multiple sites available on my.sublimevideo.net
            else {
              echo "<p class='desc'><select id='sv_site_token_select' name='sv_site_token'>";
              echo "<option value=''>Choose a site</option>";
              $selected_site = null;
              foreach ($available_sites as $site) {
                // No token yet and the current hostname match the domain of the site, save it right away!
                if (
                    (!get_option('sv_site_token') && $clean_http_host == $site->main_domain) ||
                    (get_option('sv_site_domain') && get_option('sv_site_token') == $site->token && get_option('sv_site_domain') != SublimeVideoUtils::site_with_wildcard_and_path($site))
                   ) {
                  SublimeVideoAdmin::update_site_info($site);
                }
                if (get_option('sv_site_token') == $site->token) {
                  $selected_site = $site;
                }

                echo "<option value='".$site->token."' ".selected($selected_site == $site, true, false)." class='".$site->accessible_stage."_stage'>".SublimeVideoUtils::site_with_wildcard_and_path($site)."</option>";
              }
              echo "</select>";
              echo "<input type='hidden' name='sv_site_domain' id='sv_site_domain' value='".SublimeVideoUtils::site_with_wildcard_and_path($selected_site)."' />";
              echo " <span id='view_site' style='".(get_option('sv_site_token') ? '' : 'display:none;')."'>".sprintf($sublimevideo->t('view_site'), get_option('sv_site_token'))."</span></p>";
              echo sprintf($sublimevideo->t('another_site?'), 'sites', $clean_http_host);
            }
            ?>
          </div>
          <div class="breakline"></div>

          <h3>Default player width</h3>
          <p>
            <input type="text" name="sv_player_width" value="<?php echo esc_attr( get_option('sv_player_width') ); ?>" class="code" style="width:50px;text-align:center;" />
            px
          </p>
          <?php echo $sublimevideo->t('default_width_note'); ?>
          <div class="breakline"></div>

          <h3>Player version</h3>
          <p class='desc'>
            <select name='sv_player_stage' id='sv_player_stage_select'>
              <?php
              echo "<option value='stable' ".selected(get_option('sv_player_stage') == 'stable', true, false).">Stable</option>";
              echo "<option value='beta' ".selected(get_option('sv_player_stage') == 'beta', true, false).">Beta</option>";
              if ($admin->alpha_stage_accessible()) {
                echo "<option id='alpha_stage_option' value='alpha' ".disabled(get_option('sv_player_stage') != 'alpha', true, false)." ".selected(get_option('sv_player_stage') == 'alpha', true, false).">Alpha</option>";
              }
              ?>
            </select>
          </p>
          <?php echo $sublimevideo->t('player_stage_note'); ?>
          <div class="breakline"></div>
          <p class="submit"><input type="submit" class="button-primary" value="Save changes" /></p>
        </form>
      </div>
      <div class="breakline"></div>
      <?php echo $sublimevideo->t('more_information'); ?>

      <script type="text/javascript">
        var siteTokenSelect = document.getElementById('sv_site_token_select');

        if (siteTokenSelect !== null) {
          siteTokenSelect.addEventListener('change', function(e) {
            var selectedTokenOption = siteTokenSelect[siteTokenSelect.selectedIndex];
            var viewSiteDiv         = document.getElementById('view_site');
            if (e.target.value != '') {
              document.getElementById('view_site_link').href = "https://my.sublimevideo.net/sites/" + e.target.value + "/edit";
              viewSiteDiv.style.display = 'inline';
            }
            else {
              viewSiteDiv.style.display = 'none';
            }
            document.getElementById('sv_site_domain').value = (selectedTokenOption.value == '' ? '' : selectedTokenOption.innerText);

            // Update stage select
            var playerStageSelect = document.getElementById('sv_player_stage_select')
            var selectedStage     = playerStageSelect[playerStageSelect.selectedIndex];
            var alphaStageOption  = document.getElementById('alpha_stage_option');

            console.log(alphaStageOption);
            if (alphaStageOption !== null) {
              if (selectedTokenOption.className == 'alpha_stage') {
                alphaStageOption.disabled = null;
              }
              else {
                alphaStageOption.disabled = 'disabled';
                if (selectedStage.value == 'alpha') {
                  playerStageSelect.selectedIndex = 1; // Select beta if alpha is currently selected
                }
              }
            }
          });
        }
      </script>
    <?php
    }
  }

}

// Register the settings function
add_action('admin_init', array('SublimeVideoAdmin', 'init'));

// Add an item in the plugins menu
add_action('admin_menu', array('SublimeVideoAdmin', 'config_page'));

// Add a link in the plugins list
add_filter('plugin_action_links', array('SublimeVideoAdmin', 'plugin_action_links'), 10, 2);

?>
