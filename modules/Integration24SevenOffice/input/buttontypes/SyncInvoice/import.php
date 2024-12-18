<?php

// error_reporting(E_ALL);
// ini_set("display_errors", 1);
	ini_set('max_execution_time', 120);

	// Ownercompany list
	$sql = "SELECT * FROM ownercompany";
	$o_query = $o_main->db->query($sql);
	$ownercompany_list = $o_query && $o_query->num_rows() ? $o_query->result_array() : array();

	if(isset($_POST['migrateData'])) {
		if($_POST['invoiceFrom'] != "" && $_POST['invoiceTo'] != ""){
	        $hook_file = __DIR__ . '/../../../hooks/sync_customer_and_invoice.php';
	        if (file_exists($hook_file)) {
	            require_once $hook_file;
	            if (is_callable($run_hook)) {
					$s_sql = "SELECT * FROM invoice WHERE id >= ? AND id <= ?";
					$o_query = $o_main->db->query($s_sql, array($_POST['invoiceFrom'], $_POST['invoiceTo']));
					$invoices = $o_query ? $o_query->result_array() : array();
					foreach($invoices as $invoice){
						if($invoice['external_invoice_nr'] > 0) {
							$hook_params = array(
			                    'invoice_id' => $invoice['id']
			                );
			                $hook_result = $run_hook($hook_params);
						}
						if(isset($hook_result['invoice_sync_result']['SaveInvoicesResult']['InvoiceOrder']['APIException']) || !isset($hook_result['invoice_sync_result']['SaveInvoicesResult']['InvoiceOrder']['InvoiceId'])) {
							break;
						}

					}
	            }
				unset($run_hook);
	        }
		}
	}
	//test stuff
	// $hook_params = array(
	// 	'transaction_ids' => array("90e2846d-0e53-4edd-9af6-e76a6213d52c", "37cfef34-3193-47ce-851b-95034049446d"),
	// 	'creditor_id'=>"1006",
	// 	'username'=>$username
	// );
	// if($variables->loggID == "byamba@dcode.no"){
	// 	$hook_file = __DIR__ . '/../../../../Integration24SevenOffice/hooks/relink_transaction.php';
	// 	if (file_exists($hook_file)) {
	// 		include $hook_file;
	// 		if (is_callable($run_hook)) {
	// 			var_dump($hook_file);
	// 			$hook_result = $run_hook($hook_params);
	// 			var_dump($hook_result);
	// 			if($hook_result['result']){
	//
	// 			} else {
	// 				// var_dump("deleteError".$hook_result['error']);
	// 			}
	// 		}
	// 		unset($run_hook);
	// 	}
	// }

    $sql = "SELECT * FROM creditor WHERE id = ?";
    $o_query = $o_main->db->query($sql, array(1040));
    $creditorData = $o_query ? $o_query->row_array() : array();
	require_once __DIR__ . '/../../../internal_api/load.php';
	$v_config = array(
		'ownercompany_id' => 1,
		'identityId' => $creditorData['entity_id'],
		'creditorId' => $creditorData['id'],
		'o_main' => $o_main
	);
	$s_sql = "SELECT * FROM integration24sevenoffice_session WHERE username = '".$o_main->db->escape_str($data['username'])."' AND creditor_id = '".$o_main->db->escape_str($creditorData['id'])."'";
	$o_query = $o_main->db->query($s_sql);
	if($o_query && 0 < $o_query->num_rows())
	{
		$v_int_session = $o_query->row_array();
		$v_config['session_id'] = $v_int_session['session_id'];
	}
	$api = new Integration24SevenOffice($v_config);
	$return = array();
	$type_result = $api->getTypeList();
	$types = $type_result['types'];
	foreach($types as $type) {
		if(mb_strpos($type['Title'], "Utgående faktura") !== false) {
			$return['result'] = $type['TypeNo'];
		}
	}
	var_dump($return);
?>
<div>
	<form name="importData" method="post" enctype="multipart/form-data"  action="" >
		<div class="formRow submitRow">
			<?php echo $formText_InvoiceNumberFrom_output;?>
			<input type="text" name="invoiceFrom" value=""/><br/>
			<?php echo $formText_InvoiceNumberTo_output;?>
			<input type="text" name="invoiceTo" value=""/><br/>

			<input type="submit" name="migrateData" value="Sync invoices">

		</div>
	</form>
</div>
