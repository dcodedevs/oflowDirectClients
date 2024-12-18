<?php

$jsonReturn = array();
//refresh session data
$_SESSION['jsonReturn'] = $jsonReturn;

// Get Batchinvoicing data
$s_sql = "SELECT * FROM batch_invoicing_accountconfig";
$o_query = $o_main->db->query($s_sql);
$batchinvoicing_accountconfig = $o_query ? $o_query->row_array() : array();
if(!$v_proc_variables['dontLaunch'] && !$v_proc_variables['emergencyLock']){
	// If this is "createProcLines" call
	if ($v_proc_variables['createProcLines']) {

		$jsonReturn['procrunID'] = $procrunID;

		$activateMultiOwnerCompanies = false;
		$s_sql = "SELECT * FROM ownercompany_accountconfig";
		$o_query = $o_main->db->query($s_sql);
		if($o_query && $o_query->num_rows()>0){
			$ownercompanyAccountconfig = $o_query->row_array();
			$activateMultiOwnerCompaniesItem = intval($ownercompanyAccountconfig['max_number_ownercompanies']);
			if ($activateMultiOwnerCompaniesItem > 1) {
				$activateMultiOwnerCompanies = true;
			}
		}

		// HOOK: check_before_invoicing
		// Also can be used to pre-sync articles, vat codes, etc
	    if ($batchinvoicing_accountconfig['activate_check_before_invoicing']) {
	        $hook_file = __DIR__ . '/../../../../../' . $batchinvoicing_accountconfig['path_check_before_invoicing'];
	        if (file_exists($hook_file)) {
	            require_once $hook_file;
	            if (is_callable($run_hook)) {
	                $hook_result = $run_hook($hook_params);
	                unset($run_hook);
	            }
	        }
		}

	    if ($hook_result['error']) {
	        $jsonReturn['error'] = array(
	            'message' => $hook_result['message']
	        );
	    }
	    else {
	        foreach ($_POST['orders'] as $key => $data) {

	            if(in_array($key, $_POST['customer'])) {
	                // Save lines
	                $keyArr = explode("-", $key, 2);
	                $recordID = $keyArr[0];
	                $extra = "";
	                if(isset($keyArr[1])){
	                    $extra = $keyArr[1];
	                }
	                $o_main->db->query("INSERT INTO sys_procrunline SET procrunID = ?, status = 0, recordID = ?, extra = ? ", array($procrunID, $recordID, $extra));

	                // Prepare return JSON with lines for AJAX processing script
	                $jsonReturn['lines'][] = array(
	                    'recordID' => $key,
	                    'procrunlineID' => $o_main->db->insert_id()
	                );
	            }

	        }
	    }

		//end batch	if any error from hooks
		if(count($jsonReturn['error']) > 0){
			$s_sql = "SELECT id, log FROM batch_invoicing WHERE id = ?";
			$o_query = $o_main->db->query($s_sql, array($v_proc_variables['batch_invoice_id']));
			$v_batch_item = $o_query ? $o_query->row_array() : array();
			$log = $v_batch_item['log'];
			$o_main->db->query("UPDATE batch_invoicing SET status = 0, log = ? WHERE id = ?", array($log." \nBatch ended - ".date("d.m.Y H:i:s"), $v_proc_variables['batch_invoice_id']));
		}
		// Return JSON
		if(!$v_proc_variables['subscriptionInvoicing']) {
			echo json_encode($jsonReturn);
		} else {
			$_SESSION['jsonReturn'] = $jsonReturn;
		}
	}
} else {
	// type 1 - Active batch invoicing already running
	// type 2 - Invoicing on emergency lock-down! No invoices can be made! For some intergrations can be triggerd by failed invoice sync

	if ($v_proc_variables['emergencyLock']) {
		$jsonReturn['error'] = array(
			'type' => 2,
			'message' => $formText_InvoicingFunctionalityLockedPleaseContactSupport_output
		);
	}
	elseif ($v_proc_variables['dontLaunch']) {

		$jsonReturn['error'] = array(
			'type' => 1,
			'message' => $formText_AnotherBatchInvoicingAlreadyRunningPleaseWaitUntilItIsDone_output
		);
	}

	if(!$v_proc_variables['subscriptionInvoicing']) {
		echo json_encode($jsonReturn);
	} else {
		$_SESSION['jsonReturn'] = $jsonReturn;
	}
}

?>
