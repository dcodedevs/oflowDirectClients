<?php
$data = array('data'=>json_encode(array('action'=>'get_app_list')));
$url = 'https://s13.getynet.com/accounts/librarymanager/api/index.php';
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$s_response = curl_exec($ch);
curl_close($ch);

$v_data = json_decode($s_response, true);
if(isset($v_data['status']) && $v_data['status'] == 1)
{
	$fw_return_data = "<option value=\"\">".$formText_Choose_Output."</option>";
	foreach($v_data['items'] as $v_app)
	{
		$fw_return_data .= "<option value=\"".$v_app['folder']."\">".($v_app['name']!=""?$v_app['name']:$v_app['folder'])."</option>";
	}
} else {
	echo $formText_ErrorOccuredProcessingRequest_Output;
}
?>