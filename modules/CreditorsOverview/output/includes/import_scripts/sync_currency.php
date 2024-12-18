<?php
$languageID = 'no';
include(__DIR__."/../../languagesOutput/default.php");
if(is_file(__DIR__."/../../languagesOutput/".$languageID.".php")){
	include(__DIR__."/../../languagesOutput/".$languageID.".php");
}

$sql = "SELECT cr.*, cr.companyname as creditorName FROM creditor cr
WHERE cr.integration_module <> '' AND cr.sync_from_accounting = 1
AND default_currency IS null
ORDER BY cr.id LIMIT 20";
$o_query = $o_main->db->query($sql);
$creditors = $o_query ? $o_query->result_array() : array();
foreach($creditors as $creditor) {
	$s_sql = "UPDATE creditor SET currency_sync_start_time = NOW() WHERE id = ?";
	$o_query = $o_main->db->query($s_sql, array($creditor['id']));
	if(!class_exists("Integration24SevenOffice")){
		require_once __DIR__ . '/../../../../'.$creditor['integration_module'].'/internal_api/load.php';
	}
	$s_sql = "SELECT * FROM integration24sevenoffice_session WHERE creditor_id = '".$o_main->db->escape_str($creditor['id'])."' ORDER BY created DESC";
	$o_query = $o_main->db->query($s_sql);
	$integration_sessions = $o_query ? $o_query->result_array() : array();
	$username = "";
	$session_id = "";
	echo "<div style='padding: 5px 0px; border-bottom:1px solid #cecece;'><div><b>".$creditor['creditorName']." (id: ".$creditor['id'].")</b></div>";
	foreach($integration_sessions as $integration_session) {
		$v_config = array(
			'ownercompany_id' => 1,
			'creditorId' => $creditor['id'],
			'o_main' => $o_main
		);
		$v_config['session_id'] = $integration_session['session_id'];
		try {
			$connect_tries = 0;
			$connectedSuccessfully = false;
			do {
				$connect_tries++;
				$api = new Integration24SevenOffice($v_config);
				if($api->error == "") {
					$connectedSuccessfully = true;
					$username = $integration_session['username'];
					$session_id = $integration_session['session_id'];
					break;
				} else  {
					// echo $api->error;
				}
			} while($connect_tries < 2);
			if($connectedSuccessfully) {
				break;
			}
		} catch(Exception $e) {
			echo  "Critical error with exception. ".$e->getMessage();
		}
	}

	if($username != "") {

		$data['changedAfter'] = date("Y-m-d", strtotime("01.01.2012"));
		$data['ShowOpenEntries'] = null;

		$transactionData = array();
		$transactionData['DateSearchParameters'] = 'DateChangedUTC';
		$transactionData['date_start'] = $data['changedAfter'];
		$transactionData['date_end'] =date('Y-m-t', time() + 60*60*24);
		$transactionData['bookaccountStart'] = 1500;
		$transactionData['bookaccountEnd'] = 1529;
		$transactionData['ShowOpenEntries'] = $data['ShowOpenEntries'];
		//
		$connect_tries = 0;
		$connectedSuccessfully = false;
		do {
			$connect_tries++;
		    $invoicesTransactions = $api->get_transactions($transactionData);
			if($invoicesTransactions !== null){
				$connectedSuccessfully = true;
				break;
			}
		} while($connect_tries < 11);

		$importedSuccessfully = 0;
		foreach($invoicesTransactions as $invoicesTransaction) {
			$sql = "SELECT * FROM creditor_transactions WHERE transaction_id = ? AND creditor_id = ?";
			$o_query = $o_main->db->query($sql, array($invoicesTransaction['id'], $creditor['id']));
			$local_transaction = $o_query ? $o_query->row_array() : array();
			if($local_transaction) {
				$sql = "UPDATE creditor_transactions SET currency = ?, currency_rate = ?, currency_unit = ?  WHERE id = ?";
				$o_query = $o_main->db->query($sql, array($invoicesTransaction['currency'], $invoicesTransaction['currencyRate'], $invoicesTransaction['currencyUnit'], $local_transaction['id']));
				if($o_query) {
					$importedSuccessfully++;
				}
			}
		}
		echo $importedSuccessfully . " transactions were updated";

		$defaultCurrency = "";
		$successfullyConnected = false;
		do {
			$connect_tries++;
			$company_info = $api->get_company_info();
			if($company_info['GetClientInformationResult']){
				$successfullyConnected = true;
				$company_info_array = $company_info['GetClientInformationResult'];
				$defaultCurrency = $company_info_array['DefaultCurrency'];
				break;
			}
		} while($connect_tries < 10);

		if($successfullyConnected) {
			$s_sql = "UPDATE creditor SET default_currency = ? WHERE id = ?";
			$o_query = $o_main->db->query($s_sql, array($defaultCurrency, $creditor['id']));
		}
	}
}
?>
