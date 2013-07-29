<?php
// http://codex.wordpress.org/TinyMCE_Custom_Buttons
// http://tinymce.moxiecode.com/wiki.php/API3:class.tinymce.Plugin
/**
 * @package TinyMCE
 * @author
 * @copyright Copyright © 2011-2013, Jilion SA
 */

/** @ignore */
require_once('../../../../wp-load.php');
require_once('../../../../wp-admin/includes/admin.php');

if ( ! current_user_can('administrator') &&
     ! current_user_can('editor') &&
     ! current_user_can('contributor')
 ) wp_die( __( 'You do not have sufficient permissions to access this page.' ) );

if ( ! isset( $_GET['inline'] ) ) define( 'IFRAME_REQUEST' , true );

if ( isset($_POST['insert_sv_shortcode_into_post']) ) {
  media_send_to_editor(SublimeVideoShortcodes::create_shortcode_from_params($_POST));
}
else {
  header('Content-Type: text/html; charset=' . get_bloginfo('charset'));
  ?>
  <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
  <html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
  <head>
  <meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php echo get_option('blog_charset'); ?>" />
  <title><?php _e('Configure your video to use with SublimeVideo') ?></title>
  <link rel="stylesheet" href="<?php echo SUBLIMEVIDEO_PLUGIN_URL; ?>tinymce/css/content.css" />
  <script type="text/javascript" src="<?php echo SUBLIMEVIDEO_PLUGIN_URL; ?>tinymce/js/prototype.js"></script>
  <script type="text/javascript" src="<?php echo SUBLIMEVIDEO_PLUGIN_URL; ?>tinymce/js/s2.js"></script>

  <?php
  $stage = '';
  if (get_option('sv_player_stage') && get_option('sv_player_stage') != 'stable') {
    $stage = '-'.get_option('sv_player_stage');
  }
  echo '<script type="text/javascript" src="//cdn.sublimevideo.net/js/'.get_option('sv_site_token').$stage.'.js"></script>';
  ?>
  <script type="text/javascript" src="<?php echo SUBLIMEVIDEO_PLUGIN_URL; ?>tinymce/js/vpa.js"></script>
  <script type="text/javascript" src="<?php echo SUBLIMEVIDEO_PLUGIN_URL; ?>vendor/size-checker/sublimevideo-size-checker.min.js"></script>
  <script type="text/javascript">
    if (!document.createElement('video')['canPlayType']) {
      document.createElement("video");
      document.createElement("source");
    }

    SublimeVideoSizeChecker.setRoot("<?php echo SUBLIMEVIDEO_PLUGIN_URL.'vendor/size-checker'; ?>");
    var assetsBasePath = "<?php $upload_dir = wp_upload_dir(); echo $upload_dir['baseurl'] ?>";
  </script>
  <?php
  if ( is_rtl() ) : ?>
  <style type="text/css">
    #wphead, #tabs {
      padding-left: auto;
      padding-right: 15px;
    }
    #flipper {
      margin: 5px 0 3px 10px;
    }
    .keys .left, .top, .action { text-align: right; }
    .keys .right { text-align: left; }
    td b { font-family: Tahoma, "Times New Roman", Times, serif }
  </style>
  <?php endif; ?>
  </head>
  <body>
    <div id="global">

      <h2 class="logo"><a href="http://sublimevideo.net" onclick='window.open(this); return false'><span>SublimeVideo</span></a></h2>

      <div id="flipper" class="wrap">
        <div id="content1">
          <p class="desc">Poster frames and video sources are automatically taken from your Media Library. Before adding a video to your post, please upload your files using the <a href="<?php echo admin_url('media-new.php'); ?>" onclick="window.open(this); return false">media upload form</a>.</p>

          <form action="sv-insert.php" method="post" id="file-form">
            <?php
              $images_urls = SublimeVideoUtils::images();
              $videos_urls = SublimeVideoUtils::videos_urls();
            ?>
            <h2>Poster frame</h2>
            <div class="breakline" style="margin-bottom:22px"></div>
            <div id="internal_poster_box">
              <a href="" class="blue_button" id="show_poster_gallery">Choose a poster frame from your media library</a>
              <div id="posters_thumbs_viewer" style="display:none">
                <div class="spacer"></div>
                <a href="" id="load_next_poster_thumbs">Load 5 next</a>
              </div>
              <div id="selected_poster_box" style="display:none">
                <input type="hidden" name="internal_poster_url" id="internal_poster_url" value="" />
                <div class="left">
                  <img src="" width="80" height="80" />
                  <a href="" id="change_poster">Change</a>
                </div>
                <div class="right">
                  <h3 class="name"></h3>
                  <h4 class="size"></h4>
                </div>
                <div class="spacer"></div>
              </div>
            </div>

            <div class="external_poster_box">
              <input id="external_poster_url" name="external_poster_url" class="text" type="text" value="" size="50" style="display:none;" />
            </div>
            <p class="external_url"><a href="#" id="toggle_poster_source">Or use an external URL</a></p>
            <input type="hidden" name="poster_source" id="poster_source" value="internal" />

            <h2>Video sources</h2>
            <div class="breakline"></div>
            <div id="source_origin">
              <div class="radio">
                <input type="radio" name="source_origin" value="own" id="source_origin_own" checked />
                <label for="source_origin_own">My video is in the Media Library or external</label>
              </div>
              <div class="radio">
                <input type="radio" name="source_origin" value="youtube" id="source_origin_youtube" />
                <label for="source_origin_youtube">My video is on YouTube</label>
              </div>
            </div>

            <div id="youtube" style="display:none">
              <label for="source_youtube_src">Your YouTube video ID</label>
              <br />
              <input type="text" name="source_youtube_src" id="source_youtube_src" placeholder="YouTube video ID" />
              <p class="desc"><a href="http://docs.sublimevideo.net/youtube#additional-information" onclick='window.open(this); return false'>Where can I find the video ID?</a></p>
            </div>

            <ul id="sources">
            <?php
            foreach (SublimeVideo::$formats as $format => $infos) {
              echo "<li><h4 class='type'>".$infos[SublimeVideo::FORMAT_TITLE].($format != 'mp4' ? " <em>(Optional)</em>" : '')."</h4>";
                echo "<ul>";
                foreach ($infos[SublimeVideo::FORMAT_QUALITIES] as $quality) {
                  $format_quality = strtolower($format).'_'.strtolower($quality);
                  if ( $quality != 'Normal' ) {
                    echo '<div class="checkbox"><input type="checkbox" id="'.$format_quality.'_add" name="'.$format_quality.'_add" value="1" />';
                    echo '<label for="'.$format_quality.'_add">';
                    switch ($quality) {
                      case 'Mobile':
                        echo 'Add a mobile version (lower resolution/bitrate)';
                        break;
                      case 'HD':
                        echo 'Add an HD version';
                        break;
                    }
                    echo '</label></div>';
                  }

                  echo "<li id='".$format_quality."_item' ".($quality != 'Normal' ? ' style="display:none"' : '').">";
                    echo "<div id='".$format_quality."_title_and_select' class='source_type'>";
                    if ( $quality != 'Normal' ) echo "<strong>".$quality."</strong>";
                    echo "<div id='internal_".$format_quality."_box' class='video_internal_src_box ".strtolower($quality)."'><select class='video_internal_src' id='internal_".$format_quality."' name='internal_".$format_quality."'>";
                      echo SublimeVideoUtils::options_from_videos_urls($videos_urls, $format);
                    echo "</select></div>";
                    echo "<input class='video_external_src' id='external_".$format_quality."' name='external_".$format_quality."' class='text' type='text' value='' size='50' style='display:none;' />";
                    echo "</div>";

                    echo "<p class='external_url'><a href='#' id='toggle_".$format_quality."_source'>Or use an external URL</a></p>";
                    echo "<input type='hidden' name='".$format_quality."_source' id='".$format_quality."_source' value='internal' />";
                  echo "</li>";
                }
                echo "</ul>";
              echo "</li>";
            }
            ?>
            </ul>

            <div id="final_dimensions" class="dimensions" style="display:none;">Embed size<span id="original_dimensions"> (original is <span id="original_width"></span> x <span id="original_height"></span>)</span>:
              <input id="final_width" name="final_width" class="text" type="text" value="" size="4" maxlength="4" />
               x
              <input id="final_height" name="final_height" class="text" type="text" value="" size="4" maxlength="4" />
              <span id="keep_ratio_box"><input type="checkbox" id="keep_ratio" name="keep_ratio" value="1" class="checkbox" checked /> <label for="keep_ratio">Keep original ratio</label></span>
            </div>

            <div id="live_preview_wrap" style="display:none">
              <h2>Live preview</h2>
              <div class="breakline"></div>
              <div id="live_preview_video_wrap"></div>
            </div>
            <div class="breakline"></div>
            <div class="submit"><input type="submit" name="insert_sv_shortcode_into_post" value="Add to my post" class="blue_button light" /></div>
          </form>
        </div>
        <div class="breakline"></div>
        <p class="desc">For more information, please consult the <a href="http://docs.sublimevideo.net/wordpress" onclick='window.open(this); return false'><strong>plugin documentation</strong></a>.</p>
      </div>
    </div>

    <script type="text/javascript">
      var formats = $H({
        <?php
        $hash_elements = array();
        foreach ( SublimeVideo::$formats as $format => $infos ) {
          $hash_elements[] = "'$format': [".join( ",", array_map( array( 'SublimeVideoUtils', 'strtolower_quoted' ), $infos[SublimeVideo::FORMAT_QUALITIES] ) )."]";
        }
        echo join( ",", $hash_elements )."\n";
        ?>
      });
      var posterThumbs = <?php echo $images_urls ?>;
      var playerWidth = <?php echo get_option('sv_player_width') ? get_option('sv_player_width') : 'undefined' ?>;
      var adminURL = "<?php echo admin_url('media-new.php'); ?>";
      var spinnerImageURL = "<?php echo esc_url( admin_url( "images/wpspin_light.gif" ) ); ?>";
    </script>
  </body>
  </html>
<?php } ?>
