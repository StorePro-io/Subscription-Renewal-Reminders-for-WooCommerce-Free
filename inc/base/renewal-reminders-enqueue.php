<?php 
/**
 * @package  RenewalReminders
 */



/**
* 
*/
class SPRREnqueue extends SPRRBaseController
{
	public function sprr_register() {
		add_action('admin_enqueue_scripts', array($this, 'sprr_enqueue_files'));
	}

	function sprr_enqueue_files($hook) {
		// Only enqueue on our plugin pages
		if (strpos($hook, 'sp-renewal-reminders') === false) {
			return;
		}

		// Enqueue standard WordPress editor scripts (including TinyMCE) 
		// specifically for pages using the email builder
		if (strpos($hook, 'sp-renewal-reminders-templates') !== false) {
			wp_enqueue_editor();
			wp_enqueue_media();
		}

		wp_enqueue_style('renpluginstyle', $this->plugin_url . 'assets/css/style.css');
		wp_enqueue_script('renpluginscript', $this->plugin_url . 'assets/js/custom.js', array('jquery'), '1.0', true);
	}
}