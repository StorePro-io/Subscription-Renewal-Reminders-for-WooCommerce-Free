<?php 
/**
 * @package  RenewalReminders
 */
class SPRRBaseController
{
	
	public $plugin_path;

	public $plugin_url;

	public $plugin;

	public function __construct() {
		$this->plugin_path = plugin_dir_path( dirname( __FILE__, 2 ) );
		$this->plugin_url = plugin_dir_url( dirname( __FILE__, 2 ) );
		$this->plugin = plugin_basename( dirname( __FILE__, 3 ) ) . '/subscriptions-renewal-reminders.php';
	}

	public function sprr_init_hooks()
	{
        add_shortcode('subscription_cancel_button', array($this, 'subscription_cancel_button_shortcode'));
    }

	
	 /**
     * Shortcode function to generate the cancel button for the subscription
     */
    public function subscription_cancel_button_shortcode()
	{
		// Check if the cancel button is enabled
		$cancel_button_enabled = get_option('sprr_cancel_button_enabled');
		if ($cancel_button_enabled !== 'on') {
			return ''; // Return nothing if the cancel button is disabled
		}

		// Get the My Account URL
		$my_account_link = wc_get_page_permalink('myaccount');
		// Fetch the cancel button text from settings, with a default fallback
		$cancel_button_text = get_option('sprr_cancel_button_text');

		// Check if the user is logged in
		if (!is_user_logged_in()) {
			// Generate a button that directs logged-out users to the login page (My Account)
			$login_url = wp_login_url($my_account_link); // Redirect back to My Account after login
			$button_html = '<p style="text-align:center;margin-top:20px;">';
			$button_html .= '<a href="' . esc_url($login_url) . '" style="background-color:#7f54b3;color:#fff;padding:10px 20px;text-decoration:none;border-radius:5px;">' .  esc_html($cancel_button_text) . '</a>';
			$button_html .= '</p>';
			return $button_html;
		}

		// Get the current user ID
		$user_id = get_current_user_id();

		// Retrieve the latest subscription for the user (you might need to adjust this logic based on your needs)
		$subscriptions = wcs_get_users_subscriptions($user_id);

		if (empty($subscriptions)) {
			return '<p>No active subscriptions found.</p>';
		}

		// Assuming you want to target the first active subscription (or adjust the logic to fit your needs)
		$subscription = array_shift($subscriptions);
		$subscription_number = $subscription->get_id(); // Get the subscription ID/number

		// Generate the cancel link for the subscription
		$cancel_link = trailingslashit($my_account_link) . 'view-subscription/' . esc_attr($subscription_number) . '/?cancel_subscription=true';

		if (empty($cancel_button_text)) {
			$cancel_button_text = 'Cancel Subscription'; // Default text if option is not set
		}

		// Generate the button HTML
		$button_html = '<p style="text-align:center;margin-top:20px;">';
		$button_html .= '<a href="' . esc_url($cancel_link) . '" style="background-color:#7f54b3;color:#fff;padding:10px 20px;text-decoration:none;border-radius:5px;">' . esc_html($cancel_button_text) . '</a>';
		$button_html .= '</p>';

		return $button_html;
	}
}