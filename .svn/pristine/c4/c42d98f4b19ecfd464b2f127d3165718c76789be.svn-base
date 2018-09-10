<?php
if (!defined('ABSPATH')) {
    exit;
}

class Estimated_Delivery_Log 
{
    public static function init_log()
    {
        $content="<------------------- Estimated Delivery Log File  ------------------->\n";
        return $content;
    }
    public static function log_update($msg,$title)
    {
        $check=  get_option('wf_estimated_delivery_record_log');
        if('yes' === $check)
        {
            $log=new WC_Logger();
            $head="<------------------- ( ".$title." ) ------------------->\n";
            $log_text=$head.print_r((object)$msg,true);
            $log->add("estimated_delivery_log",$log_text);
        }
    }
}
