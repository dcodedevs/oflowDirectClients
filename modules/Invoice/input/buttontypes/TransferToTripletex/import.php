<?php
	if(isset($_POST['submitImportData'])) {
		$invoiceFrom = $_POST['invoiceFrom'];
		$invoiceTo = $_POST['invoiceTo'];
		if($invoiceFrom != "" && $invoiceTo != ""){

			$sqlCheckCustomer = "SELECT * FROM invoice WHERE external_invoice_nr >= ? AND external_invoice_nr <= ?";
			$o_query = $o_main->db->query($sqlCheckCustomer, array($invoiceFrom, $invoiceTo));
			$invoices = $o_query ? $o_query->result_array() : array();

			$s_sql = "SELECT * FROM batch_invoicing_accountconfig";
			$o_query = $o_main->db->query($s_sql);
			$batchinvoicing_accountconfig = $o_query ? $o_query->row_array() : array();
			if(count($invoices) > 0) {
				if ($batchinvoicing_accountconfig['activate_syncing_of_customer_and_invoice']) {
					foreach($invoices as $invoice) {
						$hook_params = array(
							'invoice_id' => $invoice['id']
						);

						$hook_file = __DIR__ . '/../../../../../' . $batchinvoicing_accountconfig['path_syncing_of_customer_and_invoice'];
						if (file_exists($hook_file)) {
							require $hook_file;
							if (is_callable($run_hook)) {
								$hook_result = $run_hook($hook_params);
								unset($run_hook);
								if(isset($hook_result['invoice_sync_result']['invoice']['validationMessages'])){
									foreach($hook_result['invoice_sync_result']['invoice']['validationMessages'] as $validationMessages){
										echo $validationMessages['message']."</br>";
									}
								} else {
									echo 'Successfully synced invoice '.$invoice['external_invoice_nr'];
								}
							}
						}
					}
				} else {
					echo 'Missing config';
				}
			} else {
				echo 'No invoices found';
			}
		} else {
			echo 'Select invoices';
		}
	}
?>
<div>
	<form name="importData" method="post"  action="" >
		<div class="formRow">
			<label>Invoice From</label>
			<input type="text" name="invoiceFrom"><br/>
			<label>Invoice To</label>
			<input type="text" name="invoiceTo">
		</div>
		<div class="formRow submitRow">
			<input type="submit" name="submitImportData" value="Transfer To Tripletex">
		</div>
	</form>
</div>
