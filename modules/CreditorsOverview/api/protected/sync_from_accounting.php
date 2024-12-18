<?php
$customer_id = $v_data['params']['customer_id'];
$creditor_filter = $v_data['params']['creditor_filter'];
$username= $v_data['params']['username'];
$languageID = $v_data['params']['languageID'];
if($languageID == "") {
	$languageID = "no";
}

if($creditor_filter > 0){
	$s_sql = "SELECT * FROM creditor WHERE id = ?";
	$o_query = $o_main->db->query($s_sql, array($creditor_filter));
	$creditor = ($o_query ? $o_query->row_array() : array());
} else {
	$s_sql = "SELECT * FROM creditor WHERE customer_id = ?";
	$o_query = $o_main->db->query($s_sql, array($customer_id));
	$creditor = ($o_query ? $o_query->row_array() : array());
}
if($creditor) {
	$s_sql = "SELECT creditor.* FROM creditor WHERE sync_started_time is not null AND sync_started_time < (NOW() - INTERVAL 30 MINUTE) AND sync_status = 1 AND id = ?";
	$o_query = $o_main->db->query($s_sql, array($creditor['id']));
	$creditor_time_over = ($o_query ? $o_query->row_array() : array());

    if($creditor['sync_status'] != 1 || $creditor_time_over) {
        $s_sql = "UPDATE creditor SET sync_status = 1, sync_started_time = NOW() WHERE id = ?";
        $o_query = $o_main->db->query($s_sql, array($creditor['id']));
        if($o_query){
            if(is_file(__DIR__."/../../output/includes/import_scripts/import_cases2.php")){
                ob_start();
                include(__DIR__."/../../output/languagesOutput/default.php");
                if(is_file(__DIR__."/../../output/languagesOutput/no.php")){
                    include(__DIR__."/../../output/languagesOutput/no.php");
                }
				try {
	                $creditorId = $creditor['id'];
	                include(__DIR__."/../../output/includes/import_scripts/import_cases2.php");
	                // include(__DIR__."/../../output/includes/create_cases.php");
	                $result_output = ob_get_contents();
	                $result_output = trim(preg_replace('/\s\s+/', '', $result_output));
	                ob_end_clean();

	                $s_sql = "UPDATE creditor SET sync_status = 0 WHERE id = ?";
	                $o_query = $o_main->db->query($s_sql, array($creditor['id']));

			        $s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), creditor_id = ?, log = ?, creditor_syncing_id = ?";
			        $o_query = $o_main->db->query($s_sql, array($creditor['id'], 'Sync finished', $creditor_syncing_id));

					if(!$connection_error){
		                $v_return['html'] = $result_output;
		                $v_return['status'] = 1;
					} else {
						$v_return['error'] = $result_output;
					}
				} catch(Exception $e) {
					$s_sql = "UPDATE creditor SET sync_status = 0 WHERE id = ?";
	                $o_query = $o_main->db->query($s_sql, array($creditor['id']));
	                $v_return['error'] = 'Connection error. Please try again';

			        $s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), creditor_id = ?, log = ?, creditor_syncing_id = ?";
			        $o_query = $o_main->db->query($s_sql, array($creditor['id'], 'Error '.$e->getMessage(), $creditor_syncing_id));
				}
            } else {
                $v_return['error'] = 'Missing sync script. Contact system developer';
            }
        }
    } else {
        $v_return['error'] = 'Sync already running. If sync wasn\'t finished please contact system developer';
    }
} else {
    $v_return['error'] = 'Missing customer. Contact system developer';
}
?>
