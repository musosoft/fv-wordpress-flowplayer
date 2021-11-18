<?php

abstract class FV_Player_Conversion_Base {

  protected $matchers = array();
  protected $title = 'Conversion';
  protected $slug = 'conversion-slug';
  protected $screen;

  abstract function convert_one($post);

  abstract function get_posts_with_shortcode( $offset, $limit );

  abstract function get_count();

  abstract function conversion_button();

  function __construct( $args ) {
    $this->title = $args['title'];
    $this->slug = $args['slug'];
    $this->matchers = $args['matchers'];
    $this->screen = 'fv_player_conversion_' . $this->slug;

    add_action('admin_menu', array( $this, 'admin_page' ) );
    add_action( 'admin_notices', array( $this, 'screen') );
    add_action( 'wp_ajax_fv_player_conversion_'.$this->slug, array( $this, 'ajax_convert') );
    add_action( 'fv_player_conversion_buttons', array( $this, 'conversion_button') );
  }

  function admin_page() {
    add_submenu_page( 'fv_player', 'FV Player Migration', 'FV Player Migration', 'edit_posts', $this->screen, array($this, 'tools_panel') );
    remove_submenu_page( 'fv_player', $this->screen );
  }

  function ajax_convert() {
    if ( current_user_can( 'administrator' ) && check_ajax_referer( $this->slug ) ) {
      $html = '';
      $offset = intval($_POST['offset']);
      $offset = 0 + intval($_POST['offset2']) + $offset;

      $limit = intval($_POST['limit']);

      $total = $this->get_count();

      $posts = $this->get_posts_with_shortcode( $offset, $limit );

      foreach( $posts AS $post ) {
        $result = $this->convert_one($post);
        $html = $result['status'];
        $post_id = wp_update_post( array( 'ID' => $post->ID, 'post_content' => $result['new_content'] ) );
      }

      $percent_done = round ( (($offset + $limit) / $total) * 100 );

      echo json_encode( 
        array(
          'display' => $html,
          'percent_done' => $percent_done
        )
      );
    }

    die();
  }

  function screen() {
    if( current_user_can('manage_options') && isset($_GET['fv_player_conversion_' . $this->slug]) && wp_verify_nonce($_GET['fv_player_conversion_' . $this->slug] ,'fv_player_conversion_' . $this->slug) ) {
      global $fv_wp_flowplayer_ver;
      wp_enqueue_script('fv-player-convertor', flowplayer::get_plugin_url().'/js/admin-shortcode-convertor.js', array('jquery'), $fv_wp_flowplayer_ver );

      ?>
        <div class="wrap">
          <p>
            <input type="hidden" name="action" value="rebuild" />
            <input class="button-primary" type="submit" name="convert" value="Convert shortcodes" />
          </p>
          <div id="wrapper"></div>
          <p><a href="#" onclick='clearmessages(); return false'>Clear messages</a></p>
          <div id="messages">
          </div>
        </div>

        <script type="text/javascript" charset="utf-8">
          jQuery(function() {
            jQuery('#wrapper').Progressor( {
              start:    jQuery('input[name=convert]'),
              cancel:   '<?php echo 'Cancel'; ?>',
              url:      '<?php echo admin_url('admin-ajax.php') ?>',
              nonce:    '<?php echo wp_create_nonce($this->slug)?>',
              finished: '<?php echo 'Finished'; ?>'
            });
          });
        </script>
      <?php

    }
  }

}