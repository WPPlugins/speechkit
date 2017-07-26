<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://speechkit.io
 * @since      1.0.0
 *
 * @package    Speechkit
 * @subpackage Speechkit/admin/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<h1>Speechkit Settings</h1>


<div class="wrap">
  <form action="options.php" method="post">
    <?php
      settings_fields( 'speechkit-settings' );
      do_settings_sections( 'speechkit-settings' );
      $cron_post_ids = Speechkit_Admin::get_all_cron_post_ids();
      $unprocessed_post_ids = Speechkit_Admin::get_processing_posts();
      $count_unprocessed = count($unprocessed_post_ids);
    ?>

    <table class="sk-admin-table">
      <tr>
        <th>Enable Speechkit</th>
        <td><input type="checkbox" name="sk_enabled" value="1" <?php checked( 1 == get_option('sk_enabled') ) ?>/></td>
      </tr>
      <tr>
        <th>Site Id</th>
        <td><input type="text" placeholder="Your site id" name="sk_news_site_id" value="<?php echo esc_attr( get_option('sk_news_site_id') ); ?>" size="50" /></td>
      </tr>
      <tr>
        <th>API Key</th>
        <td><input type="text" placeholder="Your api key" name="sk_api_key" value="<?php echo esc_attr( get_option('sk_api_key') ); ?>" size="50" /></td>
      </tr>
      <tr style="display: none;">
        <th>Backend url</th>
        <td><input type="text" placeholder="URL" name="sk_backend" value="<?php echo esc_attr( get_option('sk_backend') ); ?>" size="50" /></td>
      </tr>
      <tr>
        <th>Voice Id</th>
        <td><input type="text" placeholder="" name="sk_voice_id" value="<?php echo esc_attr( get_option('sk_voice_id') ); ?>" size="50" /></td>
      </tr>
      <tr>
        <th>Analytics Id</th>
        <td><input type="text" placeholder="" name="sk_analytics_id" value="<?php echo esc_attr( get_option('sk_analytics_id') ); ?>" size="50" /></td>
      </tr>
      <tr>
        <th>Analytics Key</th>
        <td><input type="text" placeholder="" name="sk_analytics_key" value="<?php echo esc_attr( get_option('sk_analytics_key') ); ?>" size="50" /></td>
      </tr>
      <tr style="display: none;">
        <th>Player version</th>
        <td><input type="text" placeholder="Empty means default" name="sk_player_version" value="<?php echo esc_attr( get_option('sk_player_version') ); ?>" size="50" /></td>
      </tr>
      <tr style="display: none;">
        <th>Content Execution order</th>
        <td><input type="text" placeholder="10" name="sk_execution_order" value="<?php echo esc_attr( get_option('sk_execution_order') ); ?>" size="50" /></td>
      </tr>
      <tr>
        <td><?php submit_button(); ?></td>
      </tr>
    </table>

    <?php if ($count_unprocessed > 0) { ?>
      <p>
        You have <b><?php echo count($unprocessed_post_ids); ?> audio posts</b> that need to be reloaded. Press Refresh all audio to reload.
        Having trouble? Email our support at: <a href="mailto:contact@speechkit.io">contact@speechkit.io</a>
      </p>
      <button class="button button-primary button-large" id="sk_reload_all">Refresh all Audio</button><br />
    <?php } ?>

    <div style="display: none">
      <?php echo "cron post ids: ".print_r($cron_post_ids); ?>
      <?php echo "unprocessed post ids:".print_r($unprocessed_post_ids); ?>
    </div>

  </form>
</div>