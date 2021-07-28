/*
 * Improve the fullscreen calling to make sure the video covers the full visible viewport of Google Pixel 4 or iPhone Pro which have a special viewport shape
 */
flowplayer(function(player, root) {
  // if the fullscreen is not supported do not alter the Flowplayer behavior in any way
  if ( jQuery(root).data('fullscreen') != false && !flowplayer.support.fullscreen && player.conf.native_fullscreen && typeof flowplayer.common.createElement('video').webkitEnterFullScreen === 'function' ) {
    return;
  }

  player.on("ready", function (e,api,video) {
    jQuery(root).find('.fp-header').append('<a class="fp-fullscreen fp-icon"></a>');
  });

  //  copy of original Flowplayer variable declarations
  var FS_ENTER = "fullscreen",
    FS_EXIT = "fullscreen-exit",
    FS_SUPPORT = flowplayer.support.fullscreen,
    win = window,
    scrollX,
    scrollY;

  //  copy of original Flowplayer function with some subtle changes
  player.fullscreen = function(flag) {
    var wrapper = jQuery(root).find('.fp-player')[0];
    
    if (player.disabled) return;

    if (flag === undefined) flag = !player.isFullscreen;

    if (flag) {
      scrollY = win.scrollY;
      scrollX = win.scrollX;
    }

    if (FS_SUPPORT) {

       if (flag) {
          ['requestFullScreen', 'webkitRequestFullScreen', 'mozRequestFullScreen', 'msRequestFullscreen'].forEach(function(fName) {
             if (typeof wrapper[fName] === 'function') {
                wrapper[fName]({
                  navigationUI: "hide"  // hides the white bar on Google Pixel 4 etc.
                });
                if (fName === 'webkitRequestFullScreen' && !document.webkitFullscreenElement)  {
                   wrapper[fName]();
                }
             }
          });

       } else {
          ['exitFullscreen', 'webkitCancelFullScreen', 'mozCancelFullScreen', 'msExitFullscreen'].forEach(function(fName) {
            if (typeof document[fName] === 'function') {
              document[fName]();
            }
          });
       }

    } else {
       player.trigger(flag ? FS_ENTER : FS_EXIT, [player]);
    }

    return player;
  };


  //  copy of original Flowplayer variable declarations and FS events
  var lastClick, common = flowplayer.common;
  
  player.on("mousedown.fs", function() {
    if (+new Date() - lastClick < 150 && player.ready) player.fullscreen();
    lastClick = +new Date();
  });

  player.on(FS_ENTER, function() {
      common.addClass(root, 'is-fullscreen');
      common.toggleClass(root, 'fp-minimal-fullscreen', common.hasClass(root, 'fp-minimal'));
      common.removeClass(root, 'fp-minimal');

      if (!FS_SUPPORT) common.css(root, 'position', 'fixed');
      player.isFullscreen = true;

   }).on(FS_EXIT, function() {
      var oldOpacity;
      common.toggleClass(root, 'fp-minimal', common.hasClass(root, 'fp-minimal-fullscreen'));
      common.removeClass(root, 'fp-minimal-fullscreen');
      if (!FS_SUPPORT && player.engine === "html5") {
        oldOpacity = root.css('opacity') || '';
        common.css(root, 'opacity', 0);
      }
      if (!FS_SUPPORT) common.css(root, 'position', '');

      common.removeClass(root, 'is-fullscreen');
      if (!FS_SUPPORT && player.engine === "html5") setTimeout(function() { root.css('opacity', oldOpacity); });
      player.isFullscreen = false;

      if( player.engine.engineName != 'fvyoutube' ){ // youtube scroll ignore
        win.scrollTo(scrollX, scrollY);
      } 
   }).on('unload', function() {
     if (player.isFullscreen) player.fullscreen();
   });

   player.on('shutdown', function() {
     FULL_PLAYER = null;
     common.removeNode(wrapper);
   });
});