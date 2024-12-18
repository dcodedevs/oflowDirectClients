<?php
if(!function_exists("generateRandomString")){
	function generateRandomString($length = 8) {
	    $characters = '0123456789abcdefghijklmnopqrs092u3tuvwxyzaskdhfhf9882323ABCDEFGHIJKLMNksadf9044OPQRSTUVWXYZ';
	    $charactersLength = strlen($characters);
	    $randomString = '';
	    for ($i = 0; $i < $length; $i++) {
	        $randomString .= $characters[rand(0, $charactersLength - 1)];
	    }
	    return $randomString;
	}
}

$s_sql = "SELECT c.*, a.id AS action_id FROM collecting_cases_handling_action a JOIN collecting_cases c ON c.id = a.collecting_case_id
LEFT OUTER JOIN customer cust ON cust.id = c.debitor_id
WHERE (a.performed_date IS NULL OR a.performed_date = '0000-00-00')
AND (a.action_type = 1 OR (a.action_type = 4 AND (cust.invoiceEmail = '' or cust.invoiceEmail is null))) AND a.collecting_cases_process_steps_action_id is not null
ORDER BY c.id";
$o_query = $o_main->db->query($s_sql);
$cases = $o_query ? $o_query->result_array() : array();
$casesToGenerate = $_POST['casesToGenerate'];

if(count($cases) > 0){
	do{
		$code = generateRandomString(10);
		$batch_check = null;
		$s_sql = "SELECT * FROM collecting_cases_batch WHERE code = ?";
		$o_query = $o_main->db->query($s_sql, array($code));
		if($o_query){
			$batch_check = $o_query->row_array();
		}
	} while($batch_check != null);

	$s_sql = "INSERT INTO collecting_cases_batch SET id=NULL, createdBy='".$o_main->db->escape_str($variables->loggID)."', created=NOW(), code = ?";
	$o_query = $o_main->db->query($s_sql, array($code));
	$batch_id = $o_main->db->insert_id();

	$created_letters = 0;
	foreach($cases as $case)
	{
		if(in_array($case['id'], $casesToGenerate)){
			$o_curl = curl_init();
			$s_url = $extradomaindirroot.'modules/CollectingCases/output/includes/generatePdf.php?caseId='.$case['id'].'&batch_id='.$batch_id.'&action_id='.$case['action_id'];
			curl_setopt($o_curl, CURLOPT_URL, $s_url);
			curl_setopt($o_curl, CURLOPT_RETURNTRANSFER, true);
			$s_response = curl_exec($o_curl);
			curl_close($o_curl);
		    if($s_response !== FALSE)
			{
				$v_response = json_decode($s_response, TRUE);
				if(isset($v_response['status']) && 1 == $v_response['status'])
				{
					$s_sql = "UPDATE collecting_cases_handling_action SET performed_date = NOW() WHERE id = '".$o_main->db->escape_str($case['action_id'])."'";
		    		$o_query = $o_main->db->query($s_sql);
					if($o_query){
						$created_letters++;
					}
				}
			}
		}
	}
}
if($created_letters > 0){
	$fw_return_data = array(
		'status' => 1,
		'batch_id' => $batch_id,
	);
} else {
	$fw_error_msg[] = $formText_NoPdfCreated_output;
}
