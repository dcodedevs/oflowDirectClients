
<div class="popupform">
<form class="output-form" action="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=edit_message_debitor";?>" method="post">
	<input type="hidden" name="fwajax" value="1">
	<input type="hidden" name="fw_nocss" value="1">
	<input type="hidden" name="output_form_submit" value="1">

	<div class="inner">
		<?php
		if(isset($_POST['report_id']) && $_POST['report_id'] > 0)
		{
			$s_sql = "SELECT * FROM collecting_cases_report_24so WHERE id = ?";
		    $o_query = $o_main->db->query($s_sql, array($_POST['report_id']));
		    $report = $o_query ? $o_query->row_array() : array();

			$s_sql = "SELECT * FROM collecting_cases_claim_letter WHERE report_id = ? AND report_status = 3";
		    $o_query = $o_main->db->query($s_sql, array($report['id']));
		    $claimletters = $o_query ? $o_query->result_array() : array();
			$cases = array();
			foreach($claimletters as $claimletter) {
				$s_sql = "SELECT collecting_cases.*, CONCAT_WS(' ', c.name, c.middlename, c.lastname) as customerName FROM collecting_cases
				LEFT OUTER JOIN customer c ON c.id = collecting_cases.debitor_id
				WHERE collecting_cases.id = ?";
				$o_query = $o_main->db->query($s_sql, array($claimletter['case_id']));
				$case = $o_query ? $o_query->row_array() : array();
				if($case) {
					$cases[$case['id']] = $case;
				}
			}
			?>
			<table class="table">
				<tr>
					<th><?php echo $formText_CaseId_output;?></th>
					<th><?php echo $formText_Date_output;?></th>
					<th><?php echo $formText_ClosedDate_output;?></th>
					<th><?php echo $formText_CustomerName_output;?></th>
					<th><?php echo $formText_Fee_output;?></th>
					<th><?php echo $formText_Interest_output;?></th>
				</tr>
				<?php
				foreach($cases as $case) {
					$payedFees = 0;
					$payedInterest = 0;
					$s_sql = "SELECT * FROM creditor_transactions WHERE content_status < 2 AND collectingcase_id = ? AND creditor_id = ? ORDER BY created DESC";
					$o_query = $o_main->db->query($s_sql, array($case['id'], $case['creditor_id']));
					$invoice = ($o_query ? $o_query->row_array() : array());

					$s_sql = "SELECT * FROM creditor_transactions  WHERE (system_type='Payment') AND link_id = ? AND creditor_id = ?";
					$o_query = $o_main->db->query($s_sql, array($invoice['link_id'], $invoice['creditor_id']));
					$invoice_payments = ($o_query ? $o_query->result_array() : array());

					$s_sql = "SELECT * FROM creditor_transactions WHERE system_type='InvoiceCustomer' AND link_id = ? AND creditor_id = ? AND (collectingcase_id is null OR collectingcase_id = 0) AND comment LIKE '%\_%' ORDER BY created DESC";
					$o_query = $o_main->db->query($s_sql, array($invoice['link_id'], $invoice['creditor_id']));
					$claim_transactions = ($o_query ? $o_query->result_array() : array());

					if($invoice['open'] == 0) {
						$totalPayments = 0;
						foreach($invoice_payments as $invoice_payment) {
							$totalPayments += $invoice_payment['amount'];
						}
						$totalFeeCharged = 0;
						$chargedFee = 0;
						$chargedInterest = 0;
						foreach($claim_transactions as $claim_transaction){
							$totalFeeCharged += $claim_transaction['amount'];
							$commentArray = explode("_", $claim_transaction['comment']);

							if($commentArray[2] == "interest"){
							   $transactionType = "interest";
							} else if($commentArray[2] == "reminderFee"){
							  $transactionType = "reminderFee";
							} else if($commentArray[0] == "Rente"){
								$transactionType = "interest";
							} else {
								$transactionType = "reminderFee";
							}
							if($transactionType == "interest") {
								$chargedInterest += $claim_transaction['amount'];
							} else if($transactionType == "reminderFee"){
								$chargedFee += $claim_transaction['amount'];
							}
						}
						$feeAmount = ($invoice['amount'] + $totalPayments)*(-1);
						if($feeAmount < 0) {
							$feeAmount = 0;
						}
						if(round($feeAmount, 2) >= round($chargedInterest, 2)) {
							$feeAmount -= $chargedInterest;
							$payedInterest = $chargedInterest;
						} else {
							$feeAmount-=$feeAmount;
							$payedInterest = $feeAmount;
						}
						if(round($feeAmount, 2) >= round($chargedFee, 2)) {
							$feeAmount -= $chargedFee;
							$payedFees = $chargedFee;
						} else {
							$feeAmount -= $feeAmount;
							$payedFees = $feeAmount;
						}
					}
					$s_edit_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=CollectingCases&folderfile=output&folder=output&inc_obj=details&cid=".$case['id'];
					?>
					<tr>
						<td><a href="<?php echo $s_edit_link;?>" target="_blank"><?php echo $case['id'];?></a></td>
						<td><?php echo date("d.m.Y", strtotime($case['created']));?></td>
						<td><?php echo date("d.m.Y", strtotime($case['stopped_date']));?></td>
						<td><?php echo $case['customerName'];?></td>
						<td><?php echo number_format($payedFees, 2, ",", " ");?></td>
						<td><?php echo number_format($payedInterest, 2, ",", " ");?></td>
					</tr>
					<?php
				}
				?>
			</table>
			<?php
		}
		?>

	</div>
	<div class="popupformbtn">
		<button type="button" class="output-btn b-large b-close"><?php echo $formText_Cancel_Output;?></button>
	</div>
</form>
</div>
<style>

.popupform .lineTitle {
	font-weight:700;
	margin-bottom: 10px;
}
.popupform textarea.popupforminput {
	border-radius: 4px;
	padding:5px 10px;
	font-size:12px;
	line-height:17px;
}
.project-file {
	margin-bottom: 4px;
}
.project-file .deleteImage {
	float: right;
}
</style>
