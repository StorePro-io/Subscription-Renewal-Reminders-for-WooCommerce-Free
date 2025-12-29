<?php
/**
 * Marketing Features Handler
 * 
 * @package RenewalReminders
 */

class SPRRMarketing
{
    public function sprr_register()
    {
        add_action('wp_ajax_sprr_send_winback_emails', array($this, 'send_winback_emails'));
        add_action('wp_ajax_sprr_toggle_automation_rule', array($this, 'toggle_automation_rule'));
        add_action('wp_ajax_sprr_cancel_upcoming_email', array($this, 'cancel_upcoming_email'));
        add_action('wp_ajax_sprr_save_email_template', array($this, 'save_email_template'));
    }

    /**
     * Toggle automation rule enabled/disabled
     */
    public function toggle_automation_rule()
    {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'sprr_toggle_rule_nonce')) {
            wp_send_json_error(array('message' => __('Security check failed.', 'subscriptions-renewal-reminders')));
            return;
        }

        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('You do not have permission to perform this action.', 'subscriptions-renewal-reminders')));
            return;
        }

        // Check if user has premium access
        if (!sprr_is_premium_active()) {
            wp_send_json_error(array('message' => __('Automation rules are a premium feature. Please upgrade to Pro.', 'subscriptions-renewal-reminders')));
            return;
        }

        $rule_id = sanitize_text_field($_POST['rule_id']);
        $enabled = intval($_POST['enabled']);

        $automation_rules = get_option('sprr_automation_rules', array());

        if (isset($automation_rules[$rule_id])) {
            $automation_rules[$rule_id]['enabled'] = $enabled;
            update_option('sprr_automation_rules', $automation_rules);
            wp_send_json_success(array('message' => __('Rule status updated.', 'subscriptions-renewal-reminders')));
        } else {
            wp_send_json_error(array('message' => __('Rule not found.', 'subscriptions-renewal-reminders')));
        }
    }

    /**
     * Send win-back emails to selected subscribers
     */
    public function send_winback_emails()
    {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'sprr_winback_nonce')) {
            wp_send_json_error(array('message' => __('Security check failed.', 'subscriptions-renewal-reminders')));
            return;
        }

        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('You do not have permission to perform this action.', 'subscriptions-renewal-reminders')));
            return;
        }

        $subscribers = isset($_POST['subscribers']) ? $_POST['subscribers'] : array();

        if (empty($subscribers)) {
            wp_send_json_error(array('message' => __('No subscribers selected.', 'subscriptions-renewal-reminders')));
            return;
        }

        $sent_count = 0;
        $failed_count = 0;

        foreach ($subscribers as $subscriber) {
            $email = sanitize_email($subscriber['email']);
            $first_name = sanitize_text_field($subscriber['first_name']);
            $last_name = sanitize_text_field($subscriber['last_name']);
            $subscription_id = intval($subscriber['subscription_id']);

            if (!is_email($email)) {
                $failed_count++;
                continue;
            }

            // Get win-back email settings
            $subject = stripslashes_deep(esc_attr(get_option('sprr_winback_email_subject', __('We Miss You! Special Offer Inside', 'subscriptions-renewal-reminders'))));
            $headers = array('Content-Type: text/html; charset=UTF-8');

            // Generate email body
            $body = $this->get_winback_email_body($first_name, $last_name, $subscription_id);

            // Send email
            $sent = wp_mail($email, $subject, $body, $headers);

            if ($sent) {
                $sent_count++;
            } else {
                $failed_count++;
            }
        }

        $message = sprintf(
            __('Successfully sent %d email(s). %d failed.', 'subscriptions-renewal-reminders'),
            $sent_count,
            $failed_count
        );

        wp_send_json_success(array(
            'message' => $message,
            'sent' => $sent_count,
            'failed' => $failed_count
        ));
    }

    /**
     * Generate win-back email body
     */
    private function get_winback_email_body($first_name, $last_name, $subscription_id)
    {
        $rr_bg = stripslashes_deep(get_option('woocommerce_email_background_color', '#f7f7f7'));
        $rr_body = stripslashes_deep(get_option('woocommerce_email_body_background_color', '#ffffff'));
        $rr_text = stripslashes_deep(get_option('woocommerce_email_text_color', '#3c3c3c'));
        $rr_base = stripslashes_deep(get_option('woocommerce_email_base_color', '#96588a'));
        $rrbase_text = wc_light_or_dark($rr_base, '#202020', '#ffffff');

        $content = stripslashes_deep(get_option('sprr_winback_email_content', ''));
        
        // Default content if not set
        if (empty($content)) {
            $content = __("Hi {first_name} {last_name},\n\nWe noticed your subscription has ended and we'd love to have you back!\n\nAs a valued customer, we're offering you an exclusive discount to reactivate your subscription.\n\nClick below to view your subscription and restart anytime:\n{subscription_link}\n\nWe hope to see you again soon!\n\nBest regards,\nThe Team", 'subscriptions-renewal-reminders');
        }

        // Replace placeholders
        $content = str_replace('{first_name}', esc_html($first_name), $content);
        $content = str_replace('{last_name}', esc_html($last_name), $content);
        
        $subscription_link = admin_url('post.php?post=' . $subscription_id . '&action=edit');
        $my_account_link = wc_get_page_permalink('myaccount');
        $subscription_view_link = trailingslashit($my_account_link) . 'view-subscription/' . $subscription_id;
        
        $content = str_replace('{subscription_link}', '<a href="' . esc_url($subscription_view_link) . '">' . esc_html__('View My Subscription', 'subscriptions-renewal-reminders') . '</a>', $content);

        ob_start();
        ?>
        <!DOCTYPE html>
        <html lang="en-US">
        <head>
            <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
            <title><?php echo esc_html__('Win-Back Offer', 'subscriptions-renewal-reminders'); ?></title>
        </head>
        <body style="margin: 0; padding: 0;">
            <div style="background-color:<?php echo esc_attr($rr_bg); ?>; margin: 0; padding: 70px 0;">
                <table border="0" cellpadding="0" cellspacing="0" width="600" style="background-color:<?php echo esc_attr($rr_base); ?>; border-radius: 3px; margin: auto;">
                    <tr>
                        <td align="center" valign="top">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color:<?php echo esc_attr($rr_base); ?>; color: #fff; border-bottom: 0; font-weight: bold; line-height: 100%; vertical-align: middle; border-radius: 3px 3px 0 0;">
                                <tr>
                                    <td style="padding: 36px 48px;">
                                        <h1 style="font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; font-size: 30px; font-weight: 400; line-height: 150%; margin: 0; text-align: left; color:<?php echo esc_attr($rrbase_text); ?>;">
                                            <?php echo esc_html__('💝 We Miss You!', 'subscriptions-renewal-reminders'); ?>
                                        </h1>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" valign="top">
                            <table border="0" cellpadding="0" cellspacing="0" width="600">
                                <tr>
                                    <td valign="top" style="background-color: <?php echo esc_attr($rr_body); ?>">
                                        <table border="0" cellpadding="20" cellspacing="0" width="100%">
                                            <tr>
                                                <td valign="top" style="padding: 48px 48px 32px;">
                                                    <div style="background-color:<?php echo esc_attr($rr_body); ?>; color:<?php echo esc_attr($rr_text); ?>; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; font-size: 14px; line-height: 150%; text-align: left;">
                                                        <?php echo nl2br($content); ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }

    /**
     * Cancel an upcoming email
     */
    public function cancel_upcoming_email()
    {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'sprr_cancel_email')) {
            wp_send_json_error('Security check failed.');
            return;
        }

        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error('You do not have permission to perform this action.');
            return;
        }

        $index = isset($_POST['index']) ? intval($_POST['index']) : -1;
        
        if ($index < 0) {
            wp_send_json_error('Invalid email index.');
            return;
        }

        $upcoming_emails = get_option('sprr_upcoming_emails', array());
        
        if (isset($upcoming_emails[$index])) {
            // Remove the email from upcoming
            array_splice($upcoming_emails, $index, 1);
            update_option('sprr_upcoming_emails', $upcoming_emails);
            
            wp_send_json_success('Email cancelled successfully.');
        } else {
            wp_send_json_error('Email not found.');
        }
    }

    /**
     * Save email template
     */
    public function save_email_template()
    {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'sprr_save_template')) {
            wp_send_json_error('Security check failed.');
            return;
        }

        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error('You do not have permission to perform this action.');
            return;
        }

        $template_id = isset($_POST['template_id']) && !empty($_POST['template_id']) 
                       ? sanitize_text_field($_POST['template_id']) 
                       : uniqid('template_');
        
        $template_name = isset($_POST['template_name']) ? sanitize_text_field($_POST['template_name']) : '';
        $template_subject = isset($_POST['template_subject']) ? sanitize_text_field($_POST['template_subject']) : '';
        $template_content = isset($_POST['template_content']) ? wp_kses_post($_POST['template_content']) : '';

        if (empty($template_name) || empty($template_subject) || empty($template_content)) {
            wp_send_json_error('All fields are required.');
            return;
        }

        $custom_templates = get_option('sprr_custom_templates', array());
        
        $is_new = !isset($custom_templates[$template_id]);
        
        $custom_templates[$template_id] = array(
            'id' => $template_id,
            'name' => $template_name,
            'subject' => $template_subject,
            'content' => $template_content,
            'created' => $is_new ? current_time('mysql') : $custom_templates[$template_id]['created'],
            'modified' => current_time('mysql'),
        );

        update_option('sprr_custom_templates', $custom_templates);
        
        wp_send_json_success(array(
            'message' => 'Template saved successfully!',
            'template_id' => $template_id
        ));
    }
}
