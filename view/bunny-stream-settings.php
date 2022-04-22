<?php
global $fv_fp;
?>

<style>
.form-table tr > td:first-child {
  width: 200px;
}
.regular-text {
  width: 100%;
  max-width: 50em;
}
.tabs-panel {
  max-width: 48em;
}

</style>

<form method="POST">
  <table class='form-table'>
    <?php
    $fv_fp->_get_input_text( array(
      'key' => array( 'bunny_stream', 'lib_id' ),
      'name' => __('Stream Library ID', 'fv-player-bunny_stream'),
      'class' => 'regular-text code'
    ) );

    $fv_fp->_get_input_text( array(
      'key' => array( 'bunny_stream', 'api_key' ),
      'name' => __('Stream Library API Key', 'fv-player-bunny_stream'),
      'class' => 'regular-text code'
    ) );

    $fv_fp->_get_input_text( array(
      'key' => array( 'bunny_stream', 'cdn_hostname' ),
      'name' => __('Stream Library CDN Hostname', 'fv-player-bunny_stream'),
      'class' => 'regular-text code'
    ) );

    $fv_fp->_get_checkbox(__('Enable Token Authentication', 'fv-wordpress-flowplayer'), array('bunny_stream', 'video_token'), __('Improves video download protection.', 'fv-wordpress-flowplayer'));

    $fv_fp->_get_input_text( array(
      'key' => array( 'bunny_stream', 'security_token' ),
      'name' => __('Security Token', 'fv-player-bunny_stream'),
      'class' => 'regular-text code'
    ) );

    ob_start();
    ?>
    <p><img width="400" src="<?php echo plugins_url( 'models/bunny-stream-wizard/images/bunnycdn-api.png', dirname(__FILE__) ); ?>" srcset="<?php echo plugins_url( 'models/bunny-stream-wizard/images/bunnycdn-api.png', dirname(__FILE__) ); ?> 1x, <?php echo plugins_url( 'models/bunny-stream-wizard/images/bunnycdn-api-2x.png', dirname(__FILE__) ); ?> 2x" /></p>
    <?php
    $help = ob_get_clean();

    $fv_fp->_get_input_text( array(
      'key' => array( 'bunny_stream', 'api_access_key' ),
      'name' => __('API Access Key (Not Stored)', 'fv-player-bunny_stream'),
      'class' => 'regular-text code',
      'help' => $help
    ) );

    ?>
    <tr>
      <td></td>
      <td>
        <?php wp_nonce_field( 'fv_player_bunny_stream_settings_nonce', 'fv_player_bunny_stream_settings_nonce' ); ?>
        <input type="submit" class="button button-primary" value="Save" />
      </td>
    </tr>
  </table>
</form>

<script>
  jQuery(function() {
    var api_access_key_input = jQuery('input[name="bunny_stream[api_access_key]"]'),
      api_access_key_row = api_access_key_input.closest('tr'),
      security_token_input = jQuery('input[name="bunny_stream[security_token]"]'),
      security_token_row = security_token_input.closest('tr'),
      pro_compatible = <?php echo json_encode(FV_Player_Bunny_Stream()->fv_player_pro_compatible()); ?>,
      checkbox = jQuery('input[name="bunny_stream[video_token]"]:checkbox');

    api_access_key_row.hide(); // do not show

    if(!pro_compatible) {
      checkbox.prop('checked', false);
    }

    if(security_token_input.val()) { // check if already set
      security_token_row.show();
    } else {
      security_token_row.hide();
    }

    checkbox.on('click', function(e) {
      if(!pro_compatible) {
        api_access_key_row.html('<td></td><td><p>Video protection is only supported if you install FV Player Pro. You can purchase it <a href="https://foliovision.com/downloads/fv-player-license" target="_blank">here</a>.</p></td>');
        api_access_key_row.show();

        jQuery(this).prop('checked', false);
      } else {
        if(jQuery(this).prop('checked') === true) {
          api_access_key_row.show();
        } else {
          // wipe data
          security_token_input.val('');
          api_access_key_row.hide();
          security_token_row.hide();
        }
      }
    });
  });
</script>