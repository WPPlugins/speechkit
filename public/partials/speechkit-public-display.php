<?php

/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       https://speechkit.io
 * @since      1.0.0
 *
 * @package    Speechkit
 * @subpackage Speechkit/public/partials
 */
?>

<?php
  $post_disabled = get_post_meta(get_the_ID(), "speechkit_disabled", true);
  $sk_enabled = get_option('sk_enabled');
  $article = get_post_meta(get_the_ID(), "speechkit_info", true);
  unset($article['body']);
  unset($article['summary']);
  $meta = json_encode($article, JSON_UNESCAPED_SLASHES);
  $analyticsId = get_option('sk_analytics_id');
  $analyticsKey = get_option('sk_analytics_key');
?>

<?php if (!$post_disabled && $sk_enabled) { ?>
  <div id="speechkit-player"></div>

  <script type="text/javascript">
    var article = <?php echo $meta ?>;
    new SpeechKit.players.MinimalPlayer({
      renderNode: 'speechkit-player',
      height: 45,
      width: 45,
      analyticsId: "<?php echo $analyticsId ?>",
      analyticsKey: "<?php echo $analyticsKey ?>",
      article: article
    }).load();
  </script>
<?php } ?>