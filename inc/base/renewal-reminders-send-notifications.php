<?php 
/**
 * @package  RenewalReminders
 */

require SPRR_PLUGIN_DIR . 'templates/renewal-reminders-email.php';


class SPRRSendNotifications
{

    function sprr_send_notifications()
    {
       add_action( 'renewal_reminders', array($this, 'sprr_send_subscriber_notification_emaill') );
    }


    /**
     * find subscribers whose reminder date is today.
     */
    public function sprr_send_subscriber_notification_emaill() 
    {  
        global $wpdb;
        $table_name = $wpdb->prefix . 'renewal_reminders';
        
        //date_default_timezone_set('Asia/Kolkata');
        $today = new DateTime(); //for testing comment this 
        $today = $today->format("Y-m-d"); //for testing comment this also
        
        //$today = "2022-12-16"; //for testing remove comment
        $options_en = stripslashes_deep(esc_attr(get_option( 'en_disable' )));  
        $subscription_details = $wpdb->get_results($wpdb->prepare("SELECT subscription__id FROM $table_name WHERE notification_sent_date = %s",$today));
        foreach($subscription_details as $subscription_detail ) {
            $subscription_id = $subscription_detail -> subscription__id;
            $subscription_details = wcs_get_subscription( $subscription_id );
            $user_id = $subscription_details->get_user_id();
            $user = get_user_by( 'id', $user_id );
            if ($user && $options_en=='on') 
            {
                $subscriber_details['first_name'] = ucfirst($user->first_name) ;
                $subscriber_details['last_name'] = ucfirst($user->last_name);
                $subscriber_details['email'] = $user->user_email;
                $next_payment_date_dt = $subscription_details->get_date( 'next_payment' );
                $next_payment_date = date( 'F d, Y', strtotime( $next_payment_date_dt ) );
                $to = $subscriber_details['email'];
                $subject = stripslashes_deep(esc_attr(get_option( 'email_subject' )));
                $headers = array('Content-Type: text/html; charset=UTF-8'); 
                // $headers = array('From:',$title); 
                
                $body = sprr_renewalremindersemail($subscriber_details['first_name'],$subscriber_details['last_name'],$next_payment_date);

                // Function to change email address
                //code modified on version 1.1.2-for sprr_sender_email cannot redeclare error - #34571
                if (!function_exists('sprr_sender_email')) {
                    function sprr_sender_email( $original_email_address ) {
                    $admin_email = get_bloginfo('admin_email');
                    return  $admin_email;
                }
                }
                
                // Function to change sender name
                if (!function_exists('sp_sender_name')) {
                function sp_sender_name( $original_email_from ) {
                    $title = get_bloginfo();
                    return $title;
                }
                }

                // Hooking up our functions to WordPress filters 
                add_filter( 'wp_mail_from', 'sprr_sender_email' );
                add_filter( 'wp_mail_from_name', 'sp_sender_name' );
                wp_mail( $to, $subject, $body, $headers, array());  
            }
        }                          
    } // end send_subscriber_notification_emaill

}//end class