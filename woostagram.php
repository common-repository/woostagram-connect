<?php
/**
 * Plugin Name: Woostagram Connect
 * Plugin URI: http://asep.co/portfolio/woostagram-connect
 * Description: Import your Instagram photos to Woocommerce's product.
 * Version: 1.0.2
 * Author: Asep Bagja Priandana
 * Author URI: http://asep.co
 * License: GPL2
 */

/*  Copyright 2014  Asep Bagja Priandana  (email : asep@asep.co)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
include_once "navigation/options.php";
include_once "oauth/auth.php";

use AC\Navigation as Navigation;

if (!defined('ABSPATH')) { 
	exit; // Exit if accessed directly
}

class Woostagram_Connect {
				
	public function __construct() {
    ob_start();
    // load the necessary scrips
    add_action('admin_enqueue_scripts', array($this, 'load_scripts'));
    
    // do something when plugin deactivate
    register_deactivation_hook(__FILE__, array($this, 'reset_all_settings'));
    
    // add redirect uri after plugin activated
    register_activation_hook(__FILE__, array($this, 'load_default_setting')); 
    // check if the plugin is connected
    if(isset($_GET['page'])) {
      if($_GET['page'] == 'woostagram_connect_settings') {
        if(get_option('ac_access_token')) {
          add_action('admin_notices', function() {
            echo "<div class=\"updated\">";
		        echo "<p>Woostagram is connected to your Instagram account.</p>";
		        echo "</div>";
          });
        }
      }
    }
  }

	public function init() {
		// Check if WooCommerce is active
		if (in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' )))) {
      // register the integrations
      $options = new Navigation\Options(plugins_url("/img/", __FILE__));
    } else {
			throw new Exception("Woocommerce plugin has not been activated.");
		}
  }

  /**
   * Load all scripts that we need.
   *
   * @param none
   * @return void
   */
  public function load_scripts() {
    wp_enqueue_script('ajaxprogress', plugins_url("/js/jquery.ajax-progress.js", __FILE__), array('jquery'));
  }
  
  /**
   * Clear all setting option.
   *
   * @param none
   * @return void
   */
  public function reset_all_settings() {
    delete_option('ac_client_id');
    delete_option('ac_client_secret');
    delete_option('ac_redirect_uri');
    delete_option('ac_access_token');
  }

  /**
   * Load the default setting after plugin activation.
   *
   * @param none
   * @return void
   */
  public function load_default_setting() {
    add_option('ac_redirect_uri', admin_url('admin.php?page=woostagram_connect_settings'), '', 'yes');
  }
}

// Run the plugin!
try {
  $main = new Woostagram_Connect;
  $main->init();

  add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'add_action_links' );
  function add_action_links ( $links ) {
    $mylinks = array(
      '<a href="' . admin_url( 'admin.php?page=woostagram_connect_settings' ) . '">Settings</a>',
    );
    return array_merge( $links, $mylinks );
  }
}

catch(Exception $e) {
	add_action('admin_init', 'my_plugin_deactivate');
	add_action('admin_notices', 'my_plugin_admin_notice');

	function my_plugin_deactivate() {
		deactivate_plugins(plugin_basename(__FILE__));
	}

	function my_plugin_admin_notice() {
    echo "<div class=\"error\">";
		echo "<p>You have to activate WooCommerce plugin before activate Woostagram.</p>";
		echo "</div>";

		if (isset($_GET['activate'])) {
			unset($_GET['activate']);
    }
  }
}
