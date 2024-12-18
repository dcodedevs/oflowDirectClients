<?php
$l_item_count = 0;
$customerId = $_POST['customer_id'] ? $_POST['customer_id'] : '';
$s_sql = "SELECT * FROM getynet_event_client";
$o_query = $o_main->db->query($s_sql);
$getynet_event_client = ($o_query ? $o_query->row_array():array());

if($getynet_event_client['ge_account_url'] != "" && $getynet_event_client['ge_account_token'] != "")
{
    $params = array(
        'api_url' => $getynet_event_client['ge_account_url'],
        'access_token'=> $getynet_event_client['ge_account_token'],
        'module' => 'GetynetEventProvider',
        'action' => 'customer_get_participants',
        'params' => array(
            'languageID' => $languageID,
            'customer_id'=> $customerId,
            'ge_provider_id' => $getynet_event_client['ge_provider_id']
        )
    );
    $response = fw_api_call($params, false);
    if($response['status'])
	{
        $l_item_count = count($response['attendees']);
    }
}

echo ' ('.$l_item_count.')';