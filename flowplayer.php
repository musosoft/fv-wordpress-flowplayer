<?php
/*
Plugin Name: FV Player
Plugin URI: http://foliovision.com/wordpress/plugins/fv-wordpress-flowplayer
Description: Formerly FV WordPress Flowplayer. Supports MP4, HLS, MPEG-DASH, WebM and OGV. Advanced features such as overlay ads or popups. Uses Flowplayer 7.2.8.
Version: 7.5.21.728
Author URI: http://foliovision.com/
License: GPL-3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.txt
*/

/* FV Player - HTML5 video player with Flash fallback  
	Copyright (C) 2020  Foliovision
		
	This program is free software: you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation, either version 3 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

global $fv_wp_flowplayer_ver;
$fv_wp_flowplayer_ver = '7.5.21.728.999';
$fv_wp_flowplayer_core_ver = '7.2.8';
include_once( dirname( __FILE__ ) . '/includes/extra-functions.php' );
if( file_exists( dirname( __FILE__ ) . '/includes/module.php' ) ) {
  include_once( dirname( __FILE__ ) . '/includes/module.php' );
}

include_once( dirname( __FILE__ ) . '/models/checker.php' );

global $FV_Player_Checker;
$FV_Player_Checker = new FV_Player_Checker();

include_once(dirname( __FILE__ ) . '/models/flowplayer.php');
include_once(dirname( __FILE__ ) . '/models/flowplayer-frontend.php');

include_once(dirname( __FILE__ ) . '/models/lightbox.php');
include_once(dirname( __FILE__ ) . '/models/facebook-share.php');

include_once(dirname( __FILE__ ) . '/models/custom-videos.php');

include_once(dirname( __FILE__ ) . '/models/seo.php');

include_once(dirname( __FILE__ ) . '/models/subtitles.php');

include_once(dirname( __FILE__ ) . '/models/users-ultra-pro.php');

include_once(dirname( __FILE__ ) . '/models/widget.php');

include_once(dirname( __FILE__ ) . '/models/email-subscription.php');
include_once(dirname( __FILE__ ) . '/models/video-intelligence.php');
include_once(dirname( __FILE__ ) . '/models/player-position-save.php');

include_once(dirname( __FILE__ ) . '/models/db-player.php');
include_once(dirname( __FILE__ ) . '/models/db-video.php');
include_once(dirname( __FILE__ ) . '/models/db-video-meta.php');
include_once(dirname( __FILE__ ) . '/models/db-player-meta.php');
include_once(dirname( __FILE__ ) . '/models/db.php');

global $FV_Player_Db;
$FV_Player_Db = new FV_Player_Db();

include_once(dirname( __FILE__ ).'/models/cdn.class.php');
include_once(dirname( __FILE__ ).'/models/digitalocean-spaces.class.php');
include_once(dirname( __FILE__ ).'/models/linode-object-storage.class.php');

include_once(dirname( __FILE__ ).'/models/learndash.php');

include_once(dirname( __FILE__ ) . '/models/list-table.php');

include_once(dirname( __FILE__ ) . '/models/xml-video-sitemap.php');

global $fv_fp;
$fv_fp = new flowplayer_frontend();

if( is_admin() ) {
  include_once( dirname( __FILE__ ) . '/controller/backend.php' );
  include_once( dirname( __FILE__ ) . '/controller/editor.php' );
  include_once( dirname( __FILE__ ) . '/controller/settings.php' );
  if( version_compare(phpversion(),'5.5.0') != -1 ) {
    include_once(dirname( __FILE__ ) . '/models/media-browser.php');
    include_once(dirname( __FILE__ ) . '/models/media-browser-s3.php');
  }
  include_once(dirname( __FILE__ ) . '/models/system-info.php');

  include_once(dirname( __FILE__ ). '/models/conversion/conversion-base.class.php');
  include_once(dirname( __FILE__ ). '/models/conversion/shortcode2DB.class.php');
  include_once(dirname( __FILE__ ) . '/models/conversion.php');

  register_deactivation_hook( __FILE__, 'flowplayer_deactivate' );
}

include_once( dirname( __FILE__ ) . '/controller/frontend.php' );
include_once( dirname( __FILE__ ) . '/controller/shortcodes.php');

include_once( dirname( __FILE__ ) . '/models/avada-builder-bridge.php' );
include_once( dirname( __FILE__ ) . '/models/gutenberg.php' );

include_once(dirname( __FILE__ ). '/models/migration-wizard.class.php');
include_once(dirname( __FILE__ ). '/models/migration-wizard.php');

include_once(dirname( __FILE__ ) . '/models/stats.php');

add_action('plugins_loaded', 'fv_player_bunny_stream_include' );

if( !function_exists( 'fv_player_bunny_stream_include' ) && version_compare(PHP_VERSION, '5.2.17') >= 0 ) {
  function fv_player_bunny_stream_include() {
    do_action( 'fv_player_load_video_encoder_libs' );
    if ( class_exists( 'FV_Player_Video_Encoder' ) ) {
      require_once( dirname( __FILE__ ).'/models/class.fv-player-bunny_stream.php' );
    }
  }
}
