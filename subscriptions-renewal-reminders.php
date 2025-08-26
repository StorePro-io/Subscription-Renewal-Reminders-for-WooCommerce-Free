<?php
/**
 * Plugin Name:       Subscriptions Renewal Reminders 
 * Plugin URI:        https://storepro.io/subscription-renewal-premium/
 * Description:       Renewal Reminders for Subscriptions automatically send your subscribers a courtesy reminder via email X days before their subscription renews. Shortcodes to be used for updating the subscriber's First and Last Names are {first_name} and {last_name} respectively.
 * Version:           1.3.9
 * Author:            StorePro
 * Author URI:        https://storepro.io/
 * Text Domain:       subscriptions-renewal-reminders
 * Domain Path:       /languages
 * 
 * WC requires at least: 3.0
 * WC tested up to:10.1.1
 * 
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

/**
 * @Package  RenewalReminders
 */

/*
    Renewal Reminders for Subscriptions, automatically send your subscribers a courtesy reminder via email X days before their subscription renews.
    Copyright (C) 2022  StorePro  

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

// If this file is called firectly, abort!!!
defined( 'ABSPATH' ) or die( 'Hey, what are you doing here?' );


/**
 * Check if WooCommerce is active. if it isn't, disable Renewal Reminders.
 */
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

if( !is_plugin_active( 'woocommerce/woocommerce.php') ){
    function sprr_is_woo_plugin_active() {
        ?>
        <div class="error notice">
            <p><?php esc_html_e( 'Subscriptions Renewal Reminders is inactive.WooCommerce plugin must be active for Renewal Reminders to work. Please install & activate WooCommerce »', 'renewal-reminders-sp' ); ?></p>
        </div>
        <?php
    }
    add_action( 'admin_notices', 'sprr_is_woo_plugin_active' );
    deactivate_plugins(plugin_basename(__FILE__));
    unset($_GET['activate']);
    return;
}

/**
 * Check if WooCommerce Subscriptions plugin is active. if it isn't, disable Renewal Reminders.
 */
if( !is_plugin_active( 'woocommerce-subscriptions/woocommerce-subscriptions.php') ){
    function sprr_is_subscription_plugin_active() {
        ?>
        <div class="error notice">
            <p><?php esc_html_e( 'Subscriptions Renewal Reminders is inactive. WooCommerce Subscriptions plugin must be active for Renewal Reminders to work. Please install & activate WooCommerce Subscriptions »', 'renewal-reminders-sp' ); ?></p>
        </div>
        <?php
    }
    add_action( 'admin_notices', 'sprr_is_subscription_plugin_active' );
    deactivate_plugins(plugin_basename(__FILE__));
    unset($_GET['activate']);
    return;
}

/**
 * Check if Renewal Reminders Pro plugin is active. if it isn't, enable Renewal Reminders.
 */

 if( is_plugin_active( 'subscriptions-renewal-reminders-premium/subscriptions-renewal-reminders.php') ){
    function sprr_is_pro_plugin_active() {
        ?>
        <div class="error notice">
            <p><?php esc_html_e( 'Subscriptions Renewal Reminders Pro is active. Please deactivate the Pro Plugin to activate the free version »', 'renewal-reminders-sp' ); ?></p>
        </div>
        <?php
    }
    add_action( 'admin_notices', 'sprr_is_pro_plugin_active' );
    deactivate_plugins(plugin_basename(__FILE__));
    unset($_GET['activate']);
    return;
}

/**
 * Load plugin textdomain for translations.
 */
function sprr_load_textdomain() {
    $loaded = load_plugin_textdomain( 'subscriptions-renewal-reminders', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
    

    /**
     * Initialize all the core classes of the plugin
    */
    require_once SPRR_PLUGIN_DIR . '/inc/init.php';
    if ( class_exists( 'SPRRInit' ) ) 
    {
        SPRRInit::sprr_get_services();
    }
}
add_action( 'plugins_loaded', 'sprr_load_textdomain' );



function get_email_subject_placeholder() {
    return __('Write Something Here!', 'subscriptions-renewal-reminders');
}

function get_email_subject_default_value() {
    return esc_attr( stripslashes_deep( get_option( 'email_subject', __( 'Renewal Reminders Subscription', 'subscriptions-renewal-reminders' ) ) ) );
}


function get_blank_content_reminder_text() {
     $text = __("Hi {first_name} {last_name}, 
        This is an email just to let you know, your subscription expires on {next_payment_date}! 
        You can avoid this if already renewed.
        Thanks!", 'subscriptions-renewal-reminders');
    return $text;
}


/**
 * Woocommerce Version Check
 */
if(!function_exists('sprr_wc_version_check')){
    function sprr_wc_version_check( $version = '3.0' ) {
        if ( class_exists( 'WooCommerce' ) ) {
            global $woocommerce;
            if ( version_compare( $woocommerce->version, $version, ">=" ) ) {
                return true;
            }
        }
        return false;
    }
}


/**
 * Define plugin directory path
 */
if (!defined('SPRR_PLUGIN_DIR'))
define('SPRR_PLUGIN_DIR', plugin_dir_path( __FILE__ ));


/*
 * The code that runs during plugin activation
 */
function sprr_free_activate_storepro_plugin() {
	require_once SPRR_PLUGIN_DIR . 'inc/base/renewal-reminders-activate.php';
	SPRRActivate::sprr_activate();
    $time_value = stripslashes_deep(esc_attr( get_option( 'email_time' ) ));
    
        if ( ! wp_next_scheduled( 'renewal_reminders' ) ) {
        wp_schedule_event( strtotime($time_value), 'daily', 'renewal_reminders' );
    }else {
        wp_clear_scheduled_hook( 'renewal_reminders' );
        wp_schedule_event( strtotime($time_value), 'daily', 'renewal_reminders' );
    }
}
register_activation_hook( __FILE__, 'sprr_free_activate_storepro_plugin' );


/**
 * The code that runs during plugin deactivation
 */
function sprr_free_deactivate_storepro_plugin() {
	require_once SPRR_PLUGIN_DIR . 'inc/base/renewal-reminders-deactivate.php';
	SPRRDeactivate::sprr_deactivate();
    
	wp_clear_scheduled_hook( 'renewal_reminders' );
}
register_deactivation_hook( __FILE__, 'sprr_free_deactivate_storepro_plugin' );


/**
 * Initialize all the core classes of the plugin
*/
// require_once SPRR_PLUGIN_DIR . '/inc/init.php';
// if ( class_exists( 'SPRRInit' ) ) 
// {
// 	SPRRInit::sprr_get_services();
// }

/**
 * function to load data into database
 */

function renew_get_data_test() {

    require_once SPRR_PLUGIN_DIR . 'inc/base/renewal-reminders-table-operations.php';
   SPRRTableOperations::sprr_active_subscription_list();
   
}

add_action( 'wp_ajax_renew_get_data_test', 'renew_get_data_test' );


/**
 * function to update the database on change of subscription status
 */

function renew_sunscription_change_db_update($subscription_id) {

    require_once SPRR_PLUGIN_DIR . 'inc/base/renewal-reminders-table-operations.php';
    SPRRTableOperations::update_subscription_record($subscription_id);
}
add_action('woocommerce_subscription_status_updated','renew_sunscription_change_db_update');

/**
 * function to update scheduled event time
 */

function do_after_update_email_time() {

    $time_value = stripslashes_deep(esc_attr( get_option( 'email_time' )) );
   

    if ( ! wp_next_scheduled( 'renewal_reminders' ) ) {
        wp_schedule_event( strtotime($time_value), 'daily', 'renewal_reminders' );
    }else {
        wp_clear_scheduled_hook( 'renewal_reminders' );
        wp_schedule_event( strtotime($time_value), 'daily', 'renewal_reminders' );
    }
 
  }
add_action('update_option_email_time','do_after_update_email_time', 10, 2);

function dismiss_sp_notice() {
    $dismissed_key = isset($_POST['dismissed_key']) ? sanitize_key($_POST['dismissed_key']) : '';
    if ($dismissed_key) {
        update_user_meta(get_current_user_id(), $dismissed_key, '1');
        wp_send_json_success();
    } else {
        wp_send_json_error();
    }
}

add_action('wp_ajax_dismiss_sp_notice', 'dismiss_sp_notice');


// custom code to visible "upgrade to pro version now" Aug-09-2024
function sprr_add_custom_action_links( $links ) {
    $custom_link = '<a href="https://storepro.io/subscription-renewal-premium/" style="color: #fbb03b; font-weight: 700;">' . __('Upgrade to Pro Version Now', 'subscriptions-renewal-reminders') . '</a>';
    array_unshift( $links, $custom_link ); 
    return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'sprr_add_custom_action_links' );

// Add custom text after the plugin description in the plugin row meta
function sprr_add_custom_plugin_row_meta($plugin_meta, $plugin_file) {
    if ($plugin_file == plugin_basename(__FILE__)) {
        $custom_text = '<a href="https://storepro.io/subscription-renewal-premium/" target="_blank" style="color: #fbb03b; font-weight: 700;">' . __('Get Renewal Reminder Subscriptions Premium Pro Version!', 'subscriptions-renewal-reminders') . '</a>';
        $plugin_meta[] = $custom_text;
    }
    return $plugin_meta;
}
add_filter('plugin_row_meta', 'sprr_add_custom_plugin_row_meta', 10, 2);

//HPOS compatibility check 
add_action( 'before_woocommerce_init', function() {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', 'subscriptions-renewal-reminders/subscriptions-renewal-reminders.php', true );
	}
} );

