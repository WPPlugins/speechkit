<?php
  $post_id = get_the_ID();
  $info = get_post_meta( $post_id, 'speechkit_info', true );
  $status = get_post_meta( $post_id, 'speechkit_status', true );
  $is_disabled = 1 == get_post_meta( $post_id, 'speechkit_disabled', true );
  $is_published = get_post_status ( $post_id ) == 'publish';
?>

<?php if ($is_published) { ?>
  <p>Status: <b>
    <?php if ($status != Speechkit_Admin::STATUS_PROCESSED) { ?>
      <?php echo (empty($status) ? "New" : $status); ?>
    <?php } else { ?>
      Processed (<a href="<?php echo $info['media'][0]['url']; ?>" target="_blank">listen</a>)
    <?php } ?>
  </b></p>

  <p>Press Hide audio player/Show audio player to remove audio from this post.</p>
  <button class="button button-large sk-metabox-button" id="sk_toggle_action">
    <?php
      if ($is_disabled) {
        echo "Show Audio Player";
      } else {
        echo "Hide Audio Player";
      }
    ?>
  </button>

  <hr />

  <p>Press Update audio to renew audio after you have edited a post.</p>
  <button class="button button-large sk-metabox-button" id="sk_regenerate_action">Update Audio</button>

  <hr />

  <p>Press Refresh if audio player has not appeared after 1 minute.</p>
  <button class="button button-large sk-metabox-button" id="sk_reload_action">Refresh</button><br />

  <hr />


  <p>Having trouble? Email our support at: <a href="mailto:contact@speechkit.io">contact@speechkit.io</a></p>
<?php } else { ?>
  <p>SpeechKit will produce audio once you publish your post.</p>
<?php } ?>