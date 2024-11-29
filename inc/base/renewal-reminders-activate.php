<?php

/**
 *  
 * @Package  RenewalReminders
 * 
 */

class SPRRActivate
{
    public static function sprr_activate() 
    {
        flush_rewrite_rules();
    }
    
}