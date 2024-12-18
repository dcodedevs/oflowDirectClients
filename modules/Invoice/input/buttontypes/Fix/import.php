<?php	
	if(isset($_POST['submitImportData'])) {		
		$invoiceFrom = $_POST['invoiceFrom'];
		$invoiceTo = $_POST['invoiceTo'];
		if($invoiceFrom != "" && $invoiceTo != ""){
			$sqlCheckCustomer = "SELECT * FROM invoice WHERE external_invoice_nr >= ? AND external_invoice_nr <= ?";					
			$o_query = $o_main->db->query($sqlCheckCustomer, array($invoiceFrom, $invoiceTo));
			$invoices = $o_query ? $o_query->result_array() : array();
			
			foreach($invoices as $invoice) {
				$s_sql = "SELECT * FROM orders WHERE invoiceNumber = ?";					
				$o_query = $o_main->db->query($s_sql, array($invoice['id']));
				$orderlines = $o_query ? $o_query->result_array() : array();
				$invoiceSum = 0;
				$invoiceSumInclVat = 0;
				$invoiceVat = 0;
				foreach($orderlines as $orderline) {
					$l_sum = $orderline['priceTotal'];
					$totalSumInclVat = $orderline['gross'];
					$vat = $totalSumInclVat - $l_sum;

					$invoiceSum += $l_sum;
					$invoiceSumInclVat += $totalSumInclVat;
					$invoiceVat += $vat;
				}

				$s_sql = "UPDATE invoice SET totalExTax = ?, tax = ?, totalInclTax = ? WHERE id = ?";					
				$o_query = $o_main->db->query($s_sql, array($invoiceSum, $invoiceVat, $invoiceSumInclVat, $invoice['id']));
				if($o_query){
					echo 'Invoice '.$invoice['external_invoice_nr'].' updated<br/>';
				} else {
					echo 'FAILED to update Invoice '.$invoice['external_invoice_nr'].'<br/>';

				}
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
			<input type="submit" name="submitImportData" value="Fix invoices">
		</div>
	</form>
</div>