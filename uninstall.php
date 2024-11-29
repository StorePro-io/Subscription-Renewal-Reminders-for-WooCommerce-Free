<?php

/**
 * Trigger this file on plugin uninstall
 * 
 * @Package  RenewalReminders
 */


if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
}


global $wpdb;
$table_name = $wpdb->prefix . 'renewal_reminders';
$wpdb->query( "DROP TABLE IF EXISTS {$table_name}" );
wp_unschedule_hook( 'renewal_reminders' );