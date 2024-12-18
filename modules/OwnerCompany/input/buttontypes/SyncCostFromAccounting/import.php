<?php

	if(isset($_POST['syncCost'])) {
		$dateFrom = $_POST['dateFrom'];
		$dateTo = $_POST['dateTo'];
		include(__DIR__ . '/../../../../IntegrationTripletex/internal_api/load.php');
		$api = new IntegrationTripletex(array(
            'ownercompany_id' => 1,
            'o_main' => $o_main
        ));
		$data = array();
		$data['dateFrom'] = date("Y-m-d", strtotime($dateFrom));
		$data['dateTo'] = date("Y-m-d", strtotime($dateTo));
		$data['from'] = 0;
		$data['count'] = 3000;

		$transactions = $api->get_posting_list($data);

		$all_transactions = array();
		foreach($transactions as $transaction) {
			if($transaction['supplier']) {
				$all_transactions[] = $transaction;
			}
		}
		do {
			$data['from'] += $data['count'];
			$transactions = $api->get_posting_list($data);
			foreach($transactions as $transaction) {
				if($transaction['supplier']) {
					$all_transactions[] = $transaction;
				}
			}
		} while(count($transactions) == $data['count']);

		$updatedCount = 0;
		$createdCount = 0;
		
		foreach($all_transactions as $all_transaction) {
			$sql = "SELECT * FROM cost_from_accounting_system WHERE external_id = '".$o_main->db->escape_str($all_transaction['transactionNr'])."'";
			$o_query = $o_main->db->query($sql);
			$transactionExist = $o_query ? $o_query->row_array() : array();
			if($transactionExist){
				$sql = "UPDATE cost_from_accounting_system SET moduleID='".$o_main->db->escape_str($moduleID)."', updated = NOW(), supplier_name = '".$o_main->db->escape_str($all_transaction['supplier']['name'])."',
				amount='".$o_main->db->escape_str($all_transaction['amount'])."',
				invoice_number='".$o_main->db->escape_str($all_transaction['invoiceNr'])."',
				date='".$o_main->db->escape_str($all_transaction['date'])."',
				type='".$o_main->db->escape_str($all_transaction['type'])."',
				description='".$o_main->db->escape_str($all_transaction['description'])."',
				project_for_accounting_code='".$o_main->db->escape_str($all_transaction['project']['number'])."',
				department_for_accounting_code='".$o_main->db->escape_str($all_transaction['department']['departmentNumber'])."'
				WHERE id = '".$o_main->db->escape_str($transactionExist['id'])."'";
				$o_query = $o_main->db->query($sql);
				if($o_query){
					$updatedCount++;
				}
			} else {
				$sql = "INSERT INTO cost_from_accounting_system SET moduleID='".$o_main->db->escape_str($moduleID)."', updated = NOW(), supplier_name = '".$o_main->db->escape_str($all_transaction['supplier']['name'])."',
				amount='".$o_main->db->escape_str($all_transaction['amount'])."',
				invoice_number='".$o_main->db->escape_str($all_transaction['invoiceNr'])."',
				date='".$o_main->db->escape_str($all_transaction['date'])."',
				type='".$o_main->db->escape_str($all_transaction['type'])."',
				description='".$o_main->db->escape_str($all_transaction['description'])."',
				project_for_accounting_code='".$o_main->db->escape_str($all_transaction['project']['number'])."',
				department_for_accounting_code='".$o_main->db->escape_str($all_transaction['department']['departmentNumber'])."',
				external_id='".$o_main->db->escape_str($all_transaction['transactionNr'])."'";
				$o_query = $o_main->db->query($sql);
				if($o_query){
					$createdCount++;
				}
			}

		}

		echo $createdCount . " ".$formText_Created_input."<br/>";
		echo $updatedCount . " ".$formText_Updated_input;
	}
?>
<div>
	<form name="importData" method="post" enctype="multipart/form-data"  action="" >

		<div class="formRow submitRow">
			<?php echo $formText_SyncCostFromTripletex_output;?>
			<div class=""><input type="text" class="datepicker" autocomplete="off" name="dateFrom" value="<?php echo $_POST['dateFrom']?>"/></div>
			<div class=""><input type="text" class="datepicker" autocomplete="off" name="dateTo"  value="<?php echo $_POST['dateTo']?>"/></div>
			<input type="submit" name="syncCost" value="Sync cost from accounting">

		</div>
	</form>
	<script type="text/javascript">
	$(".datepicker").datepicker({
		firstDay: 1,
		dateFormat: "dd.mm.yy"
	})
	</script>
</div>
