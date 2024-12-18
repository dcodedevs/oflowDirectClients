<?php
if(!function_exists('get_developer_access')){
function get_developer_access($caID = '', $original = false)
{
	$o_main = get_instance();
	if($caID=='')
	{
		if(isset($_POST['caID'])) $caID = $_POST['caID'];
		else $caID = $_GET['caID'];
	}
	if($caID>0)
	{
		$fw_session = array();
		$v_param = array('companyaccessID' => $caID, 'session' => $_COOKIE['sessionID'], 'username' => $_COOKIE['username']);
		$o_query = $o_main->db->get_where('session_framework', $v_param);
		if($o_query) $fw_session = $o_query->row_array();
	} else {
		return 0;
	}
	return $fw_session['developeraccess'.($original?'original':'')];
}
}