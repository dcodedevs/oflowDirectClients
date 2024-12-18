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
		$s_sql = "SELECT * FROM session_framework WHERE companyaccessID = '".$o_main->db->escape_str($caID)."' AND session = '".$o_main->db->escape_str($_COOKIE['sessionID'])."' AND username = '".$o_main->db->escape_str($_COOKIE['username'])."'".($o_main->multi_acc?" AND account_id = '".$o_main->db->escape_str($o_main->account_id)."'":"")." LIMIT 1";
		$o_query = $o_main->db->query($s_sql);
		$fw_session = ($o_query && $o_query->num_rows()>0) ? $o_query->row_array() : array();
	} else {
		return 0;
	}
	return $fw_session['developeraccess'.($original?'original':'')];
}
}