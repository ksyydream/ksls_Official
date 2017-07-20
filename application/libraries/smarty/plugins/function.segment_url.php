<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
 
function smarty_function_segment_url($params, &$smarty) {
        $CI= &get_instance();
        if (!isset($params['c'])) {
        	$params['c'] = 1;
        }
		return $CI->uri->segment($params['c']);
}
