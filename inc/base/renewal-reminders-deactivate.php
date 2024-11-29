<?php

/**
 *  
 * @Package  RenewalReminders
 * 
 */

class SPRRDeactivate
{
	public static function sprr_deactivate() {
		flush_rewrite_rules();
	}
}