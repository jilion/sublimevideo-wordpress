<?php

class SublimeVideoLocales {

  public function __construct() {
    $this->locales = array(
      'en' => array(

        'authorize_text' => "<p class='desc'>
                            The SublimeVideo plugin needs your authorization to access some of your information and usage data.
                            All of this information will be accessed privately using the <a href='http://oauth.net' onclick='window.open(this); return false'>OAuth protocol</a>. We will never store any of your <a href='https://my.sublimevideo.net' onclick='window.open(this); return false'>SublimeVideo</a> credentials (email or password) on this blog.</p>",

        'authorize_form' => "<p class='submit'><a href='%s' class='button'>Authorize this plugin</a></p>",

        'settings_saved' => "<div class='updated fade'><p class='desc'>Your settings were successfully saved.</p></div>",

        'no_sites' => "<p class='desc'>
                        You don't have any site licensed for <strong>%s</strong> on <a href='https://my.sublimevideo.net/sites' onclick='window.open(this); return false'>SublimeVideo</a>.
                       </p>
                       <p class='desc'>
                        Please add <strong>%s</strong> as the main, extra or development domain to an <a href='https://my.sublimevideo.net/sites' onclick='window.open(this); return false'>existing site</a> or a <a href='https://my.sublimevideo.net/assistant/new-site' onclick='window.open(this); return false'>new site</a>.</p>",

        'site' => "<input type='text' value='%s' readonly size='%d' />",
        'view_site' => "Domain retrieved from <a id='view_site_link' href='https://my.sublimevideo.net/sites/%s/edit' onclick='window.open(this); return false'>SublimeVideo</a>.",

        'another_site?' => "<p class='desc'>Not the %s you were looking for? Please make sure to add <strong>%s</strong> as the main, alias or staging domain (or as a development domain if you are on a local installation of WordPress) to a <a href='https://my.sublimevideo.net/assistant/new-site' onclick='window.open(this); return false'>new</a> or <a href='https://my.sublimevideo.net/sites' onclick='window.open(this); return false'>existing site</a>.</p>",

        'api_issue' => "<div class='updated fade'>
                          <p class='desc'>There seems to be a problem contacting the SublimeVideo API to retrieve your information, please retry in a few minutes.
                          </p>
                          <p class='desc'>Please note that the delivery of your SublimeVideo Players <strong>is not affected</strong> by this problem.
                          </p></div>",

        'default_width_note' => "<p class='desc'>Note: This will default to the maximum width of your theme (you can also change it when adding a video to your post).</p>",

        'player_stage_note' => "<p class='desc'>The <a href='http://sublimevideo.net/modular-player' onclick='window.open(this); return false'>New SublimeVideo Player</a> (beta) powered by <a href='http://sublimevideo.net/horizon-framework' onclick='window.open(this); return false'>SublimeVideo Horizon</a>.</p>",

        'more_information' => "<p class='desc'>For more information, please <a href='http://docs.sublimevideo.net/wordpress' onclick='window.open(this); return false'><strong>consult the documentation</strong></a> or <a href='http://sublimevideo.net/help' onclick='window.open(this); return false'><strong>ask for some help</strong></a>.</p>"

      )
    );
  }

  public function t($key, $lang='en') {
    return $this->locales[$lang][$key];
  }

}

?>
