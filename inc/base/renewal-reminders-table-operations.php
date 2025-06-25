<?php 
/**
 * @package  RenewalReminders
 * 
 */

class SPRRTableOperations 
{
  public function sprr_table_operations() 
	{
    $this->sprr_create_table();
  }

  public function sprr_create_table()
  {
    global $wpdb;
    $table_name = $wpdb->prefix . "renewal_reminders"; 
    $charset_collate = $wpdb->get_charset_collate();

    #Check to see if the table exists already, if not, then create it
    if($wpdb->get_var( "show tables like '$table_name'" ) != $table_name)
    {
      $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        subscription__id mediumint(9) NOT NULL,
        subscription__name text NOT NULL,
        next_payment_date date DEFAULT '0000-00-00' NOT NULL,
        notification_sent_date date DEFAULT '0000-00-00' NOT NULL,
        PRIMARY KEY  (id)
      ) $charset_collate;";          
      require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
      dbDelta( $sql );  
    }
  } 
  
  public static function update_subscription_record($subscription_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . "renewal_reminders";

    // Log the start of processing - only log the ID
    // error_log("Processing subscription update for ID: " . (is_object($subscription_id) ? $subscription_id->id : $subscription_id));

    // Retrieve the subscription by ID
    $subscription = wcs_get_subscription($subscription_id);
    if (!$subscription) {
        error_log("Error: Subscription not found for ID: " . (is_object($subscription_id) ? $subscription_id->id : $subscription_id));
        return;
    }

    // Get customer name details
    $billing_first_name = $subscription->get_billing_first_name();
    $billing_last_name = $subscription->get_billing_last_name();
    $customer_name = trim($billing_first_name . ' ' . $billing_last_name);

    // Retrieve the number of days to notify before renewal
    $notify_days_count = stripslashes_deep(esc_attr(get_option('notify_renewal')));

    // Retrieve the next payment date
    $next_payment_date_dt = $subscription->get_date('next_payment');
    
    // Calculate dates only if a next payment date exists
    if ($next_payment_date_dt) {
        $next_payment_date = date('Y-m-d', strtotime($next_payment_date_dt));
        $notify_days_before = date('Y-m-d', strtotime($next_payment_date . '-' . $notify_days_count . ' day'));
    }

    $status = $subscription->get_status();
    $sub_id = $subscription->get_id(); // Get the actual ID for logging

    // If the subscription is active and has a next payment date, insert or update the record
    if ('active' === $status && $next_payment_date_dt) {
        $subscription_details = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT next_payment_date, notification_sent_date FROM $table_name WHERE subscription__id = %d",
                $sub_id
            )
        );

        if ($subscription_details) {
            // Update the existing record
            $result = $wpdb->update(
                $table_name,
                [
                    'next_payment_date' => $next_payment_date,
                    'notification_sent_date' => $notify_days_before,
                ],
                ['subscription__id' => $sub_id]
            );
            // if ($result === false) {
            //     error_log("Error: Failed to update subscription record for ID: $sub_id");
            // } else {
            //     error_log("Successfully updated subscription record for ID: $sub_id");
            // }
        } else {
            // Insert a new record
            $result = $wpdb->insert(
                $table_name,
                [
                    'subscription__id' => $sub_id,
                    'subscription__name' => $customer_name,
                    'next_payment_date' => $next_payment_date,
                    'notification_sent_date' => $notify_days_before,
                ]
            );
            // if ($result === false) {
            //     error_log("Error: Failed to insert new subscription record for ID: $sub_id");
            // } else {
            //     error_log("Successfully inserted new subscription record for ID: $sub_id");
            // }
        }
    }
    // If the subscription is inactive, remove its record
    elseif (in_array($status, ['cancelled', 'expired', 'pending-cancel', 'on-hold'], true)) {
        $result = $wpdb->delete($table_name, ['subscription__id' => $sub_id]);
        // if ($result === false) {
        //     error_log("Error: Failed to delete inactive subscription record for ID: $sub_id");
        // } else {
        //     error_log("Successfully deleted inactive subscription record for ID: $sub_id");
        // }
    }
}
  /**
   * Function to fetch active subscription details
   */
  public static function sprr_active_subscription_list($from_date=null, $to_date=null) 
  {
    global $wpdb, $woocommerce;
    $table_name = $wpdb->prefix . "renewal_reminders"; 
    $subscriptions = wcs_get_subscriptions(['subscriptions_per_page' => -1]);
    $db_count = 0;
    $notify_days_count = stripslashes_deep(esc_attr(get_option( 'notify_renewal' )));  
     //Going through each current customer orders
    foreach ( $subscriptions as $subscription ) {
      $subscription_data = wcs_get_subscription( $subscription );
      $subscription_id = $subscription->get_ID();
      $customer_id = $subscription->get_user_id();
      $billing_first_name = $subscription-> get_billing_first_name();
      $billing_last_name  = $subscription-> get_billing_last_name();
      $customer_name = $billing_first_name . ' ' . $billing_last_name;
      // $next_payment_date_dt = $subscription-> get_date( 'end' );
      $next_payment_date_dt = $subscription->get_date( 'next_payment' );
      $next_payment_date = date( 'Y-m-d', strtotime( $next_payment_date_dt ) );
      $notify_days_before = date( 'Y-m-d', strtotime( $next_payment_date . '-'.$notify_days_count.' day' ) );  
      $subscription_details = $wpdb->get_results($wpdb->prepare("SELECT next_payment_date, notification_sent_date FROM $table_name WHERE subscription__id = %d",$subscription_id));
        if ( $subscription->get_status() == 'active' && $next_payment_date_dt ) 
        {
          if($subscription_details ) 
            {
              $wpdb->update($table_name, array('next_payment_date'=>$next_payment_date, 'notification_sent_date'=>$notify_days_before), array('subscription__id'=>$subscription_id));
            }else{
              $wpdb->insert( 
                $table_name, 
                array( 
                  'subscription__id' => $subscription_id, 
                  'subscription__name' => $customer_name, 
                  'next_payment_date' => $next_payment_date,
                  'notification_sent_date' => $notify_days_before, 
                ) 
              );
            }
        }elseif( $subscription->get_status() == 'cancelled' || $subscription->get_status() == 'expired'|| $subscription->get_status() == 'pending-cancel' || $subscription->get_status() == 'on-hold') {
          $wpdb->delete($table_name,  array('subscription__id'=>$subscription_id));
        }
    } 
  } 
}