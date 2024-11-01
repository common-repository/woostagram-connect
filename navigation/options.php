<?php
/**
 * Displaying the option menu
 *
 * @author: Asep.co
 */
namespace AC\Navigation;

defined('ABSPATH') or die("No script kiddies please!");

use AC\OAuth as OAuth;

class Options {
  
  private $plugin_path;

  public function __construct($path = '') {
    add_action('wp_ajax_add_product', array($this, 'handling_ajax'));
    add_action('wp_ajax_nopriv_add_product', array($this, 'handling_ajax'));
    add_action('admin_menu', array($this, 'plugin_menu'));

    $this->plugin_path = $path;
  }
   
  /**
   * Show the plugin menu
   *
   * @param none
   * @return void
   */
  public function plugin_menu() {
    add_menu_page('Woostagram Connect', 
      'Woostagram Connect', 
      'manage_options', 
      'woostagram_connect', 
      array($this, 'plugin_options'),
      $this->plugin_path."instagram.png");

    add_submenu_page('woostagram_connect',
      'Settings',
      'Settings',
      'manage_options',
      'woostagram_connect_settings',
      array($this, 'plugin_options'));

    add_action('admin_init', array($this, 'update_plugin_options'));
  }
  
  /**
   * Display the corresponding page for each menu.
   *
   * @param none
   * @return void
   */
  public function plugin_options() {
    switch($_GET['page']) {
      case 'woostagram_connect_settings': 
        include "view_woostagram_connect.php";

        if(isset($_GET['code'])) {
          if(get_option('ac_access_token') == false) {
            $auth = new OAuth\Auth($_GET['code'], get_option('ac_client_id'), get_option('ac_client_secret'), get_option('ac_redirect_uri'));
            $response = json_decode($auth->code_exchange());
            
            if($response != false) {
              $option = add_option('ac_access_token', $response->access_token, '', 'yes'); 
          
              if($option == true) {
                echo "<div class=\"updated\">";
		            echo "<p>Woostagram is connected to your Instagram account.</p>";
		            echo "</div>";

                return true;
              } else {
                return false;
              }  
            } else {
              // add admin notice if the curl fail
              echo "<div class=\"error\">";
		          echo "<p>Failed to obtain access token. Please check your php-curl installation on your server or your Instagram's client settings in Instagram developer page.</p>";
		          echo "</div>";

		          if (isset($_GET['activate'])) {
			          unset($_GET['activate']);
		          }
            }
          }
        }
        break;
      case 'woostagram_connect':
        include 'view_feed_woostagram.php';

        break;
      default:
        break;
    }
  }

  /**
   * Update plugin settings
   *
   * @param none
   * @return void
   */
  public function update_plugin_options() {
    register_setting('woostagram_connect_settings', 'ac_client_id');
    register_setting('woostagram_connect_settings', 'ac_client_secret'); 
  }

  /**
   * Create ajax handler in this function
   *
   * @param none
   * @return void
   */
   public function handling_ajax() {
    ob_clean();
    $post = array(
      'post_author' => 1,
      'post_content' => $_POST['caption'],
      'post_status' => "publish",
      'post_title' => $_POST['id'],
      'post_parent' => '',
      'post_type' => "product",
    );
    
    //Create post
    $post_id = wp_insert_post( $post, $wp_error );
    
    // Add Featured Image to Post
    $image_url  = $_POST['image_url']; // Define the image URL here
    $upload_dir = wp_upload_dir(); // Set upload folder
    $image_data = file_get_contents($image_url); // Get image data
    $filename   = basename($image_url); // Create image file name

    // Check folder permission and define file location
    if( wp_mkdir_p( $upload_dir['path'] ) ) {
        $file = $upload_dir['path'] . '/' . $filename;
    } else {
        $file = $upload_dir['basedir'] . '/' . $filename;
    }

    // Create the image  file on the server
    file_put_contents( $file, $image_data );

    // Check image file type
    $wp_filetype = wp_check_filetype( $filename, null );

    // Set attachment data
    $attachment = array(
        'post_mime_type' => $wp_filetype['type'],
        'post_title'     => sanitize_file_name( $filename ),
        'post_content'   => '',
        'post_status'    => 'inherit'
    );

    // Create the attachment
    $attach_id = wp_insert_attachment( $attachment, $file, $post_id );

    // Include image.php
    require_once(ABSPATH . 'wp-admin/includes/image.php');

    // Define attachment metadata
    $attach_data = wp_generate_attachment_metadata( $attach_id, $file );

    // Assign metadata to attachment
    wp_update_attachment_metadata( $attach_id, $attach_data );

    // And finally assign featured image to post
    set_post_thumbnail( $post_id, $attach_id );
    
    echo $post_id;    
    die();
  }
}
