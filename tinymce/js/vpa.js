sublime.ready(function() {
  setupPosterOriginObservers()
  if (posterThumbs.length > 0) {
    setupPosterObservers();
  } else { // No images in the media library
    $('internal_poster_box').update("<p class='desc'>You don't have any images in your media library, please <a href='"+ adminURL +"' onclick='window.open(this); return false'>add some first</a>.</p>");
  }

  setupSourceOriginObservers()
  var sourceCount = 0;
  $$('.video_internal_src').each(function(sourcesSelect) {
    sourceCount += sourcesSelect.length - 1;
  });

  if (sourceCount > 0) {
    setupInternalSourceObservers()
  } else { // No sources in the media library
    $$('.video_internal_src_box').invoke('update', "<p class='desc no_assets'>You don't have any videos in your media library, please <a href='"+ adminURL +"' onclick='window.open(this); return false'>add some first</a>.</p>");
  }

  setupExternalSourceObservers()
  setupYouTubeSourceObservers()
  setupExternalPosterObservers()
  setupDimensionsObservers()
  setupKeepRatioObservers()
});

// Load SublimeVideo JS API since no videos are in the DOM on page load
sublime.load()

function updateLivePreview() {
  if ($('live_preview_video')) {
    sublime.unprepare('live_preview_video');
    $('live_preview_video').remove();
  }
  var poster  = $($('poster_source').value + '_poster_url') ? $($('poster_source').value + '_poster_url').value : null;
  var youTube = $('source_origin_youtube').checked;
  var sources = [];

  var dimensions = [$('final_width').value || 300, $('final_height').value || 200];
  if (!youTube) {
    formats.each(function(pair) {
      pair.value.each(function(quality) {
        var format_quality = pair.key + '_' + quality;
        var sourceUrl = $($(format_quality + '_source').value + '_' + format_quality);

        if (sourceUrl && (sourceUrl.value != '') && (!$(format_quality + '_add') || $(format_quality + '_add').checked)) {
          sources.push(new Element('source', { src: sourceUrl.value, 'data-quality': (quality == 'hd' ? 'hd' : null) }));
        }
      });
    });
  }
  var showVideoTag = youTube || sources.length > 0;

  if (showVideoTag) {
    var video = new Element('video', {
      id: 'live_preview_video',
      className: 'sv_html5_fullscreen',
      poster: poster,
      width: dimensions[0],
      height: dimensions[1],
      preload: 'none'
    });

    if (youTube) {
      video.writeAttribute('data-youtube-id', $('source_youtube_src').value);
    }
    else {
      sources.each(function(source) {
        video.insert(source);
      });
    }

    if (video.height > 0) $('live_preview_video_wrap').setStyle({ height: video.height + 'px' });
    $('live_preview_video_wrap').insert(video);

    sublime.prepare('live_preview_video');

    showVideoTag ? $('live_preview_wrap').show() : $('live_preview_wrap').hide();
  }
}

function validUrl(url) {
  return /^https?:\/\/.+\.\w+(\?+.*)?$/.test(url);
}

function setupPosterObservers() {
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

///////////////
// Observers //
///////////////

function setupPosterOriginObservers() {
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
}

function setupExternalPosterObservers() {
  $('external_poster_url').observe('keyup', conditionalLivePreviewUpdateForPoster, false);
  $('external_poster_url').observe('blur', conditionalLivePreviewUpdateForPoster, false);
}

function conditionalLivePreviewUpdateForPoster(e) {
  if(!$('external_poster_url').getAttribute('data-last_url') || ($('external_poster_url').getAttribute('data-last_url') != e.target.value)) {
    $('external_poster_url').setAttribute('data-last_url', e.target.value);
    updateLivePreview();
  }
}

function setupSourceOriginObservers() {
  $$('input[name="source_origin"]').each(function(radio) {
    radio.observe('click', function(e) {
      if (e.target.value == 'youtube') {
        $('youtube').show();
        $('sources').hide();
        if ($('source_origin_youtube').checked) $('source_youtube_src').focus();
        setVideoDimensionToInputFields('', { width: 480, height: 270 });
      }
      else {
        $('youtube').hide();
        $('sources').show();
        updateLivePreview();
      }
    });
  });

  formats.each(function(pair) {
    pair.value.each(function(quality) {
      var format_quality = pair.key + '_' + quality;

      // Video source switch observer
      $('toggle_' + format_quality + '_source').observe('click', function(e) {
        e.stop();
        if ($('external_' + format_quality).visible()) {
          $('external_' + format_quality).hide();
          $('internal_' + format_quality + '_box').show();
          $(format_quality + '_source').value = 'internal';
          $('toggle_' + format_quality + '_source').update('Or use an external URL');
        }
        else {
          $('internal_' + format_quality + '_box').hide();
          $('external_' + format_quality).show();
          $('external_' + format_quality).focus();
          $(format_quality + '_source').value = 'external';
          $('toggle_' + format_quality + '_source').update('Choose a video from your media library');
        }
        if (format_quality == 'mp4_normal') getVideoDimensionsFromVideoAndThemToInputFields($($(format_quality + '_source').value + '_mp4_normal').value);
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
}

function setupInternalSourceObservers() {
  $('internal_mp4_normal').observe('change', function(e) {
    getVideoDimensionsFromVideoAndThemToInputFields(e.target.value);
  }, false);

  $$('.video_internal_src').each(function(select) {
    select.observe('change', function(e) {
      updateLivePreview();
    }, false);
  });
}

function setupExternalSourceObservers() {
  $('external_mp4_normal').observe('keyup', conditionalLivePreviewUpdateForMp4Source);
  $('external_mp4_normal').observe('blur', conditionalLivePreviewUpdateForMp4Source);

  $$('.video_external_src').each(function(input) {
    input.observe('blur', function(e) {
      updateLivePreview();
    }, false);
  });
}

function setupYouTubeSourceObservers() {
  $('source_youtube_src').observe('change', function(e) {
    var matches = e.target.value.match(/(youtube\.com\/.+v=|youtu\.be\/)([\w\-]+)(&|$)/);
    if (matches != undefined) {
      e.target.value = matches[2];
    }
    updateLivePreview();
  }, false);
}

function conditionalLivePreviewUpdateForMp4Source(e) {
  if(!$('external_mp4_normal').getAttribute('data-last_url') || ($('external_mp4_normal').getAttribute('data-last_url') != e.target.value)) {
    $('external_mp4_normal').setAttribute('data-last_url', e.target.value);
    getVideoDimensionsFromVideoAndThemToInputFields(e.target.value);
  }
}

function setupDimensionsObservers() {
  $$('input[name="final_width"]').each(function(input) {
    input.observe('keyup', function(e) {
      var cleanValue = cleanValueAsInteger(e.target.value);
      if (cleanValue !== e.target.value) {
        input.value = cleanValue;
      }
      if ($('keep_ratio').checked) {
        updateDimensionField('height', cleanValue);
      }
    });
  });

  $$('input[name="final_height"]').each(function(input) {
    input.observe('keyup', function(e) {
      var cleanValue = cleanValueAsInteger(e.target.value);
      if (cleanValue !== e.target.value) {
        input.value = cleanValue;
      }
      if ($('keep_ratio').checked) {
        updateDimensionField('width', cleanValue);
      }
    });
  });

  ['width', 'height'].each(function(id) {
    $$('input[name="final_' + id + '"]').each(function(input) {
      input.observe('blur', function(e) {
        updateLivePreview();
      }, false);
    });
  });
}

function cleanValueAsInteger(string) {
  return string.replace(/[^\d]/g, '');
}

function setupKeepRatioObservers() {
  $('keep_ratio').observe('click', function(e) {
    // If the "keep ratio" check box has been checked, reset the right ratio to the current final dimensions
    if (e.target.checked) {
      updateDimensionField('height', $('final_width').value);
      updateLivePreview();
    }
  });
}

function getVideoDimensionsFromVideoAndThemToInputFields(url) {
  if (!validUrl(url)) {
    $('final_dimensions').hide();
  }
  else {
    if ($('video-dimensions-ajax-loading')) {
      $('video-dimensions-ajax-loading').show();
    }
    else {
      var spinner = new Element('img', {
        id: 'video-dimensions-ajax-loading',
        src: spinnerImageURL,
        className: 'ajax-loading'
      });
      $('mp4_normal_title_and_select').appendChild(spinner);
    }

    SublimeVideoSizeChecker.getVideoSize(url, setVideoDimensionToInputFields);
  }
}

function setVideoDimensionToInputFields(url, dimensions) {
  var new_width  = (dimensions == undefined ? '???' : dimensions.width);
  var new_height = (dimensions == undefined ? '???' : dimensions.height);
  $('original_width').update(new_width);
  $('original_height').update(new_height);
  if ($('video-dimensions-ajax-loading')) $('video-dimensions-ajax-loading').hide();

  $('original_dimensions').style.display = (dimensions == undefined || $('source_origin_youtube').checked ? 'none' : 'inline');
  $('keep_ratio_box').style.display = (dimensions == undefined && !$('source_origin_youtube').checked ? 'none' : 'inline');

  if ($('final_width').value == '') {
    $('final_width').value = (playerWidth ? playerWidth : $('original_width').innerHTML);
  }
  updateDimensionField('height', $('final_width').value);

  updateLivePreview();

  $('final_dimensions').show();
}

function updateDimensionField(field, otherFieldSize) {
  var theOtherField = (field == 'width' ? 'height' : 'width');
  if ($('original_width').innerHTML !== '???' && $('final_width').value !== '') {
    var ratio = parseInt($('original_' + field).innerHTML) / parseInt($('original_' + theOtherField).innerHTML);
    $('final_' + field).value = Math.round(otherFieldSize * ratio);
  }
}
