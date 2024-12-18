<?php
$fw_return_data = array('status'=>0);

$v_response = json_decode(APIconnectorOpen("countrylistget"), TRUE);
if(isset($v_response['status']) && $v_response['status'] == 1)
{
	$fw_return_data['results'] = array();
	foreach($v_response['data'] as $v_item)
	{
		if(stripos($v_item['phonecode'], $_POST['text']) === FALSE && stripos($v_item['name'], $_POST['text']) === FALSE) continue;
		$fw_return_data['results'][] = array('code' => $v_item['phonecode'], 'name' => $v_item['phonecode'].' ('.$v_item['name'].')');
		$fw_return_data['status'] = 1;
	}
}
