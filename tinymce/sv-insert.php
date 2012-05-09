<?php
// http://codex.wordpress.org/TinyMCE_Custom_Buttons
// http://tinymce.moxiecode.com/wiki.php/API3:class.tinymce.Plugin
/**
 * @package TinyMCE
 * @author
 * @copyright Copyright © 2011 SublimeVideo
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
  echo '<script type="text/javascript" src="'.( is_ssl() ? 'https://4076.voxcdn.com' : 'http://cdn.sublimevideo.net' ).'/js/'.get_option('sv_site_token').'.js"></script>';
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
                    if ( $quality != 'Normal' )  echo "<strong>".$quality."</strong>";
                    echo "<select class='video_internal_src' id='internal_".$format_quality."' name='internal_".$format_quality."'>";
                      echo SublimeVideoUtils::options_from_videos_urls($videos_urls, $format);
                    echo "</select>";
                    echo "<input class='video_external_src' id='external_".$format_quality."' name='external_".$format_quality."' class='text' type='text' value='' size='50' style='display:none;' />";
                    echo "</div>";

                    echo "<p class='external_url'><a href='#' id='toggle_".$format_quality."_source'>Or use an external URL</a></p>";
                    echo "<input type='hidden' name='".$format_quality."_source' id='".$format_quality."_source' value='internal' />";

                    if ( $format == 'mp4' && $quality == 'Normal' ) echo SublimeVideoUtils::input_text_for_final_dimensions();
                  echo "</li>";
                }
                echo "</ul>";
              echo "</li>";
            }
            ?>
            </ul>

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

      document.observe("dom:loaded", function() {
        // Load SublimeVideo JS API since no videos are in the DOM on page load
        sublimevideo.load()

        if (posterThumbs.length > 0) {
          ////////////
          // Poster //
          ////////////

          // "Choose a poster frame from your media library" button observer
          $('show_poster_gallery').observe('click', function(e) {
            e.stop()
            var viewer = $('posters_thumbs_viewer');
            viewer.toggle();
            this.toggleClassName('down');
          });

          // "Change poster" button observer
          $('change_poster').observe('click', function(e) {
            e.stop();
            $('posters_thumbs_viewer').show();
            $('show_poster_gallery').show();
            $('selected_poster_box').hide();
          });

          // "Load 5 next" button observer
          $('load_next_poster_thumbs').observe('click', function(e) {
            e.stop();
            addPosterThumbsToViewer(5);
          });

          var posterThumbsBox = new Element('div', { id : 'poster_thumbs' });
          posterThumbsBox.insert(new Element('ul', { style: 'width:100%' }));
          $('posters_thumbs_viewer').insert({ top: posterThumbsBox });
          addPosterThumbsToViewer(5);
        }
        // No posterframe available
        else {
          $('internal_poster_box').update("<p class='desc'>You don't have any images in your media library, please <a href='<?php echo admin_url('media-new.php'); ?>' onclick='window.open(this); return false'>add some first</a>.</p>");
        }

        // Manual poster observer
        $('toggle_poster_source').observe('click', function(e) {
          e.stop();
          if (!$('external_poster_url').visible()) {
            $('internal_poster_box').hide();
            $('external_poster_url').show();
            $('external_poster_url').focus();
            $('poster_source').value = 'external';
            $('toggle_poster_source').update('Or choose a poster frame from your media library');
          }
          else {
            $('external_poster_url').hide();
            $('internal_poster_box').show();
            $('poster_source').value = 'internal';
            $('toggle_poster_source').update('Or use an external URL');
          }
          updateLivePreview();
        }, false);

        /////////////////////////////
        // Final dimensions update //
        /////////////////////////////

        // Normal MP4 source <select> change observer
        $('internal_mp4_normal').observe('change', function(e) {
          setVideoDimensionsToInputFields(e.target.value);
        }, false);

        // External video URL <input> change observer
        function conditionalLivePreviewUpdateForMp4Source(event) {
          if(!$('external_mp4_normal').getAttribute('data-last_url') || ($('external_mp4_normal').getAttribute('data-last_url') != e.target.value)) {
            $('external_mp4_normal').setAttribute('data-last_url', event.target.value);
            setVideoDimensionsToInputFields(event.target.value);
          }
        }

        $('external_mp4_normal').observe('keyup', conditionalLivePreviewUpdateForMp4Source);
        $('external_mp4_normal').observe('blur', conditionalLivePreviewUpdateForMp4Source);

        // Final width <input> keyup observer
        $('final_width').observe('keyup', function(e) {
          if (!/\d+/.test(e.target.value)) {
            e.target.value = '';
          }
          else if ($('keep_ratio').checked) {
            updateHeightField(e.target.value);
          }
        });

        // Final height <input> keyup observer
        $('final_height').observe('keyup', function(e) {
          if (!/\d+/.test(e.target.value)) {
            e.target.value = '';
          }
          else if ($('keep_ratio').checked) {
            updateWidthField(e.target.value);
          }
        });

        /////////////////////////
        // Live preview update //
        /////////////////////////

        // Live preview update when blur external poster frame
        function conditionalLivePreviewUpdateForPoster(event) {
          if(!$('external_poster_url').getAttribute('data-last_url') || ($('external_poster_url').getAttribute('data-last_url') != event.target.value)) {
            $('external_poster_url').setAttribute('data-last_url', event.target.value);
            updateLivePreview();
          }
        }

        $('external_poster_url').observe('keyup', conditionalLivePreviewUpdateForPoster, false);
        $('external_poster_url').observe('blur', conditionalLivePreviewUpdateForPoster, false);

        // Internal sources <select> change observer
        $$('.video_internal_src').each(function(select) {
          select.observe('change', function(e) {
            updateLivePreview();
          }, false);
        });

        // External sources <input> change observer
        $$('.video_external_src').each(function(input) {
          input.observe('blur', function(e) {
            updateLivePreview();
          }, false);
        });

        // Live preview update when blur dimensions fields
        ['width', 'height'].each(function(id) {
          $('final_' + id).observe('blur', function(e) {
            updateLivePreview();
          }, false);
        });

        // "Keep original ratio" <input> change observer
        $('keep_ratio').observe('click', function(e) {
          // If the "keep ratio" check box has been checked, reset the right ratio to the current final dimensions
          if (e.target.checked) {
            updateHeightField($('final_width').value);
            updateLivePreview();
          }
        });

        formats.each(function(pair) {
          pair.value.each(function(quality) {
            var format_quality = pair.key + '_' + quality;

            // Video source switch observer
            $('toggle_' + format_quality + '_source').observe('click', function(e) {
              e.stop();
              if ($('external_' + format_quality).visible()) {
                $('external_' + format_quality).hide();
                $('internal_' + format_quality).show();
                $(format_quality + '_source').value = 'internal';
                $('toggle_' + format_quality + '_source').update('Or use an external URL');
              }
              else {
                $('internal_' + format_quality).hide();
                $('external_' + format_quality).show();
                $('external_' + format_quality).focus();
                $(format_quality + '_source').value = 'external';
                $('toggle_' + format_quality + '_source').update('Choose a video from your media library');
              }
              if (format_quality == 'mp4_normal') setVideoDimensionsToInputFields($($(format_quality + '_source').value + '_mp4_normal').value);
              updateLivePreview();
            }, false);

            // "Add [mobile|hd] source" <input> change observer
            if (quality != 'normal') {
              $(format_quality + '_add').observe('click', function(e) {
                $(format_quality + '_item').style.display = e.target.checked ? 'block' : 'none';
                updateLivePreview();
              });
            }
          });
        });

      });

      function addPosterThumbsToViewer(count) {
        if (posterThumbs.length > 0) {
          var ul = $$('#posters_thumbs_viewer ul')[0];

          for (var i = Math.min(count, posterThumbs.length); i > 0; i--) {
            var el = posterThumbs.shift();

            var li = new Element('li');
            var a = new Element('a', {
              href : assetsBasePath + '/' + el.file,
              'data-name': el.file.substr(el.file.lastIndexOf("/") + 1),
              'data-size': "Size: " + el.width + "x" + el.height,
              'data-thumb-src' : assetsBasePath + '/' + el.file.substr(0, el.file.lastIndexOf("/")) + '/' + el.sizes.thumbnail.file
            });
            var img = new Element('img', {
              width: 80,
              height: 80,
              src: a.readAttribute('data-thumb-src')
            });
            ul.insert(li.insert(a.insert(img)));
            if ($$('#posters_thumbs_viewer ul li').length > 5) {
              ul.setStyle({ width: parseInt(ul.getStyle('width'), 10) + 20 + "%" });
            }

            a.observe("click", function(e) {
              e.stop();
              // Set the value of the hidden field
              $('internal_poster_url').value = this.href;

              var posterBox = $('selected_poster_box');

              // Update selected poster image
              var img = posterBox.down('.left img');
              img.src = this.readAttribute('data-thumb-src');

              // Update selected poster infos
              posterBox.down('.right h3.name').update(this.readAttribute('data-name'));
              posterBox.down('.right h4.size').update(this.readAttribute('data-size'));

              $('posters_thumbs_viewer').hide();
              $('show_poster_gallery').hide();

              posterBox.appear({ duration:0.7 });

              updateLivePreview();
            });
          };
          var posterThumbsWrap = $('poster_thumbs');
          // posterThumbsWrap.scrollLeft = posterThumbsWrap.scrollWidth - posterThumbsWrap.clientWidth;
          new S2.FX.Attribute(
            posterThumbsWrap,
            posterThumbsWrap.scrollLeft,
            posterThumbsWrap.scrollWidth - posterThumbsWrap.clientWidth,
            { duration: 0.3 },
            function(l){
              posterThumbsWrap.scrollLeft = l.round();
            }
          ).play();

          if (posterThumbs.length === 0) $('load_next_poster_thumbs').fade();
        }
      }

      function updateLivePreview() {
        if ($('live_preview_video')) {
          sublimevideo.unprepare('live_preview_video');
          $('live_preview_video').remove();
        }
        var poster = $($('poster_source').value + '_poster_url') ? $($('poster_source').value + '_poster_url').value : null;
        var dimensions = [$('final_width').value || 300, $('final_height').value || 200];
        var sources = [];
        formats.each(function(pair) {
          pair.value.each(function(quality) {
            var format_quality = pair.key + '_' + quality;

            if (
                ($($(format_quality + '_source').value + '_' + format_quality).value != '') && (!$(format_quality + '_add') || $(format_quality + '_add').checked)
               ) {
                 sources.push(new Element('source', { src: $($(format_quality + '_source').value + '_' + format_quality).value, 'data-quality': (quality == 'hd' ? 'hd' : null) }));
             }
          });
        });
        var showVideoTag = sources.length > 0;

        if (showVideoTag) {
          var video = new Element('video', {
            id: 'live_preview_video',
            className: 'sv_html5_fullscreen',
            poster: poster,
            width: dimensions[0],
            height: dimensions[1],
            preload: 'none'
          });
          sources.each(function(source) {
            video.insert(source);
          });

          if (video.height > 0) $('live_preview_video_wrap').setStyle({ height: video.height + 'px' });
          $('live_preview_video_wrap').insert(video);

          sublimevideo.prepare('live_preview_video');

          showVideoTag ? $('live_preview_wrap').show() : $('live_preview_wrap').hide();
        }
      }

      function setVideoDimensionsToInputFields(url) {
        if (!validUrl(url)) $('final_dimensions').style.display = 'none';
        else {
          if ($('video-dimensions-ajax-loading')) {
            $('video-dimensions-ajax-loading').show();
          }
          else {
            var spinner = new Element('img', {
              id: 'video-dimensions-ajax-loading',
              src: '<?php echo esc_url( admin_url( "images/wpspin_light.gif" ) ); ?>',
              className: 'ajax-loading'
            });
            $('mp4_normal_title_and_select').appendChild(spinner);
          }

          SublimeVideoSizeChecker.getVideoSize(url, function(url, dimensions) {
            var new_width  = dimensions == undefined ? '???' : dimensions.width;
            var new_height = dimensions == undefined ? '???' : dimensions.height;
            $('original_width').update(new_width);
            $('original_height').update(new_height);
            $('video-dimensions-ajax-loading').hide();

            $('original_dimensions').style.display = dimensions == undefined ? 'none' : 'inline';
            $('keep_ratio_box').style.display = dimensions == undefined ? 'none' : 'inline';

            if ($('final_width').value == '') {
              $('final_width').value = <?php echo get_option( 'sv_player_width' ) ? get_option( 'sv_player_width' ) : "$('original_width').innerHTML"; ?>;
            }
            updateHeightField($('final_width').value);
            updateLivePreview();

            $('final_dimensions').style.display = 'block';
          });
        }
      }

      function updateHeightField(width) {
        if ($('original_width').innerHTML !== '???' && $('final_width').value !== '') {
          var ratio = parseInt($('original_height').innerHTML) / parseInt($('original_width').innerHTML);
          $('final_height').value = Math.round(width * ratio);
        }
      }

      function updateWidthField(height) {
        if ($('original_width').innerHTML !== '???' && $('final_width').value !== '') {
          var ratio = parseInt($('original_width').innerHTML) / parseInt($('original_height').innerHTML);
          $('final_width').value = Math.round(height * ratio);
        }
      }

      function validUrl(url) {
        return /^https?:\/\/.+\.\w+(\?+.*)?$/.test(url);
      }
    </script>
  </body>
  </html>
<?php } ?>