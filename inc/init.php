<?php

/**
 *  
 * @Package  RenewalReminders
 * 
 */

require SPRR_PLUGIN_DIR . 'inc/base/renewal-reminders-base-controller.php';
require SPRR_PLUGIN_DIR . 'inc/pages/renewal-reminders-admin.php';
require SPRR_PLUGIN_DIR . 'inc/base/renewal-reminders-enqueue.php';
require SPRR_PLUGIN_DIR . 'inc/base/renewal-reminders-settings-links.php';
require SPRR_PLUGIN_DIR . 'inc/base/renewal-reminders-table-operations.php';
require SPRR_PLUGIN_DIR . 'inc/base/renewal-reminders-send-notifications.php';



final class SPRRInit
{
	/**
	 * Store all the classes inside an array
	 * @return array Full list of classes
	 */
	public static function sprr_get_services() 
	{

		$SettingsLinks = new SPRRSettingsLinks;  
		$SettingsLinks->sprr_register();

		$Admin = new SPRRAdmin; 
		$Admin->sprr_register();

		$Enqueue = new SPRREnqueue;  
		$Enqueue->sprr_register();

		$SPRRBaseController = new SPRRBaseController;  
		$SPRRBaseController->sprr_init_hooks();

		$TableOperations = new SPRRTableOperations;  
		$TableOperations->sprr_table_operations();

		$SendNotifications = new SPRRSendNotifications;  
		$SendNotifications->sprr_send_notifications();

	}

	/**
	 * Loop through the classes, initialize them, 
	 * and call the sprr_register() method if it exists
	 * @return
	 */
	public static function sprr_register_services() 
	{
		foreach ( self::sprr_get_services() as $class ) 
		{
			$service = self::sprr_instantiate( $class );

			if ( method_exists( $service, 'sprr_register' ) ) {
				$service->sprr_register();
			}

			if ( method_exists( $service, 'sprr_tableoperations' ) ) {
				$service->sprr_tableoperations();
			}

			if ( method_exists( $service, 'sprr_sendnotifications' ) ) {
				$service->sprr_sendnotifications();
			}

		}
	}


	/**
	 * Initialize the class
	 * @param  class $class    class from the services array
	 * @return class instance  new instance of the class
	 */
	private static function sprr_instantiate( $class )
	{
		$service = new $class();
		return $service;
	}


}