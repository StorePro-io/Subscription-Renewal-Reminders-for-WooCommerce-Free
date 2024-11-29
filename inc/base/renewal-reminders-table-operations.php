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