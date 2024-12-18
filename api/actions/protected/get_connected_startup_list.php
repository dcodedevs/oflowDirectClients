<?php
$v_startup = array();

// Module basis config and api url
$sql = "SELECT * FROM connectedstartups_basisconfig";
$o_query = $o_main->db->query($sql);
$moduleBasisConfig = $o_query ? $o_query->row_array() : array();
$api_url = rtrim($moduleBasisConfig['centralAccountUrl'], '/') . '/api/';

// Get facilitators list from api
$api_req = array(
    'api_url' => $api_url,
    'module' => 'Facilitator',
    'action' => 'get_list_of_signed_up_startups'
);
$api_res = fw_api_call($api_req, true);
$startup_list = $api_res['result'];

$filtered_startup_list = array();
foreach($startup_list as $startup)
{
	if($startup['status'] == 1) array_push($v_startup, $startup['name']);
}

$v_return['data'] = $v_startup;
?>