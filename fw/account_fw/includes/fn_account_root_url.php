<?php
// Get the full URL of account root
function account_root_url()
{
	$v_tmp = explode("/",ACCOUNT_PATH);
	$s_accountname = array_pop($v_tmp);
    $page_url   = 'http';
    if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on'){
        $page_url .= 's';
    }
    return $page_url.'://'.$_SERVER['SERVER_NAME'].'/accounts/'.$s_accountname.'/';
}
?>