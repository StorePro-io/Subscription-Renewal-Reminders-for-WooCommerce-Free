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
        $sent_count = 0;
        $failed_count = 0;
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
                // Prefer selected template subject; fallback to legacy option; then safe default
                $selected_id = get_option('sprr_selected_template', 'default_template');
                $custom_templates = get_option('sprr_custom_templates', array());
                if (isset($custom_templates[$selected_id]) && !empty($custom_templates[$selected_id]['subject'])) {
                    $subject = $custom_templates[$selected_id]['subject'];
                } else {
                    $subject = stripslashes_deep(get_option('email_subject', ''));
                }
                // If still empty, apply final default
                if (!is_string($subject) || trim($subject) === '') {
                    $subject = __('Subscription Renewal Reminder', 'subscriptions-renewal-reminders');
                }
                // Replace placeholders in subject if present
                $subject = str_replace('{first_name}', $subscriber_details['first_name'], $subject);
                $subject = str_replace('{last_name}', $subscriber_details['last_name'], $subject);
                $subject = str_replace('{next_payment_date}', $next_payment_date, $subject);
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
                $result = wp_mail( $to, $subject, $body, $headers, array());
                if ($result) {
                    $sent_count++;
                } else {
                    $failed_count++;
                }
            }
        }                          
        // Log to email history (Pro only)
        if (function_exists('sprr_is_premium_active') && sprr_is_premium_active()) {
            $history = get_option('sprr_email_history', array());
            $history[] = array(
                'type' => 'renewal',
                'subject' => $subject,
                'template' => get_option('sprr_selected_template', 'default_template'),
                'sent' => $sent_count,
                'failed' => $failed_count,
                'retained' => 0,
                'timestamp' => current_time('mysql'),
            );
            update_option('sprr_email_history', $history);
        }
    } // end send_subscriber_notification_emaill

}//end class