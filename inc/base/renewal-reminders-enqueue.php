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

		global $pagenow;

		//GET sanitization
		$sp_page_name = "";

		if  (isset($_GET['page'])) {  
			$sp_page_name = filter_input(INPUT_POST | INPUT_GET, 'page', FILTER_SANITIZE_SPECIAL_CHARS);
		}
       
		if (!empty( $sp_page_name)) {
			
			//check the page is plugins admin page -prnv_mtn
			if( in_array( $pagenow, array('admin.php','sp-renewal-reminders') ) && ( $sp_page_name == 'admin.php' || $sp_page_name == 'sp-renewal-reminders' ) ) {
		

			add_action( 'admin_enqueue_scripts', array( $this, 'sprr_enqueue_files' ) );
		}
		
	}
}
	//enque here
	function sprr_enqueue_files() {
		// enqueue all our scripts
		wp_enqueue_style( 'renpluginstyle', $this->plugin_url . 'assets/css/style.css' );
		wp_enqueue_script( 'renpluginscript', $this->plugin_url . 'assets/js/custom.js' );
		
	}
}