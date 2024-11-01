<?php
/**
 * This is the html template for Woostagram Connect's settings page.
 *
 * @author: Asep.co
 */ 
namespace AC\Navigation;
defined('ABSPATH') or die("No script kiddies please!");
?>

<div class="wrap">
  <h2>Woostagram Connect's Settings Page</h2>
  <p>Connect your Instagram account.</p>
  <p>You can obtain client id, client secret, and setup the redirect url at <a href="http://instagram.com/developer/clients/manage/" target="_blank">http://instagram.com/developer/clients/manage/</a></p>
  <form method="post" action="options.php">
  <?php
  settings_fields('woostagram_connect_settings');
  do_settings_sections('woostagram_connect_settings');
  ?>
    <table class="form-table">
      <tbody>
        <tr>
          <th><label for="client_id">Client ID</label></th>
          <td><input id="client_id" class="regular-text" type="text" name="ac_client_id" value="<?php echo get_option('ac_client_id');  ?>"></td>
        </tr>
        <tr>
          <th><label for="client_secret">Client Secret</label></th>
          <td><input id="client_secret" class="regular-text" type="text" name="ac_client_secret" value="<?php echo get_option('ac_client_secret');  ?>"></td>
        </tr>
        <tr>
          <th><label for="redirect_uri">Redirect URI</label></th>
          <td>
            <p>
              <?php echo admin_url('admin.php?page=woostagram_connect_settings'); ?>
            </p>
          </td>
        </tr>
      </tbody>
    </table>
  <?php
  submit_button();
  ?>
  </form>
  <?php
  if(get_option('ac_access_token') == false) {
    if(get_option('ac_client_id') != false && get_option('ac_redirect_uri') != false && get_option('ac_client_secret') != false) {
  ?>
  <p>Now you can connect your Instagram account</p>
  <p><a href="https://api.instagram.com/oauth/authorize/?client_id=<?php echo get_option('ac_client_id'); ?>&redirect_uri=<?php echo get_option('ac_redirect_uri'); ?>&response_type=code">Login with Instagram</a><p>
  <?php
    }
  }
  ?>
</div>
