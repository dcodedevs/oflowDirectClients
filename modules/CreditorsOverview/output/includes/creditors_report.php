<?php
$date_sql = "";
if($_GET['date_from'] != "") {
	$date_sql .= " AND creditor_transactions.date >= '".$o_main->db->escape_str(date("Y-m-d", strtotime($_GET['date_from'])))."'";
}
if($_GET['date_to'] != "") {
	$date_sql .= " AND creditor_transactions.date <= '".$o_main->db->escape_str(date("Y-m-d", strtotime($_GET['date_to'])))."'";
}
?>
<div class="report_date_filter">
	<label for="date_from"><?php echo $formText_DateFrom_output;?></label>
	<input type="text" autocomplete="off" class="datepicker dateFrom" name="date_from" value="<?php if($_GET['date_from'] != "") echo date("d.m.Y", strtotime($_GET['date_from']));?>"/>
	<label for="date_to"><?php echo $formText_DateTo_output;?></label>
	<input type="text" autocomplete="off" class="datepicker dateTo" name="date_to" value="<?php if($_GET['date_to'] != "") echo date("d.m.Y", strtotime($_GET['date_to']));?>"/>
</div>
<?php
$sql = "SELECT creditor.*, CONCAT_WS(' ', c.name, c.middlename, c.lastname) as customerName FROM creditor JOIN customer c ON c.id = creditor.customer_id WHERE creditor.content_status < 2 ORDER BY creditor.id";
$o_query = $o_main->db->query($sql);
$creditors = $o_query ? $o_query->result_array() : array();

foreach($creditors as $creditor) {
	$customersWithStoppedCases = array();
	$sql = "SELECT * FROM creditor_transactions WHERE content_status < 2 AND creditor_id = ? AND collectingcase_id > 0".$date_sql;
	$o_query = $o_main->db->query($sql, array($creditor['id']));
	$transactions = $o_query ? $o_query->result_array() : array();

	foreach($transactions as $transaction) {
		$s_sql = "SELECT * FROM collecting_cases WHERE id = ?";
		$o_query = $o_main->db->query($s_sql, array($transaction['collectingcase_id']));
		$case = $o_query ? $o_query->row_array() : array();
		if($case){
			if($case['stopped_date'] != "0000-00-00" && $case['stopped_date'] != "") {

				$sql = "SELECT * FROM customer WHERE customer.creditor_customer_id = ? AND customer.creditor_id = ?";
				$o_query = $o_main->db->query($sql, array($transaction['external_customer_id'], $creditor['id']));
				$customerExist = $o_query ? $o_query->row_array() : array();
				if($customerExist){
					$noFeeError3 = true;
					$s_sql = "SELECT * FROM creditor_transactions WHERE system_type = 'InvoiceCustomer' AND link_id = ? AND creditor_id = ? AND open = 0 AND (collectingcase_id is null OR collectingcase_id = 0) AND comment LIKE '%_%'";
					$o_query = $o_main->db->query($s_sql, array($transaction['link_id'], $transaction['creditor_id']));
					$fee_transactions = $o_query ? $o_query->result_array() : array();
					$interestAmount = 0;
					$reminderAmount = 0;
					foreach($fee_transactions as $fee_transaction) {
						$commentArray = explode("_",$fee_transaction['comment']);
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
							$interestAmount += $fee_transaction['amount'];
						} else if($transactionType == "reminderFee"){
							$reminderAmount += $fee_transaction['amount'];
						}
					}
					$customersWithStoppedCases[$customerExist['creditor_customer_id']]['customerName'] = $customerExist['name'];
					$customersWithStoppedCases[$customerExist['creditor_customer_id']]['totalInterestAmount'] += $interestAmount;
					$customersWithStoppedCases[$customerExist['creditor_customer_id']]['totalReminderAmount'] += $reminderAmount;
					$customersWithStoppedCases[$customerExist['creditor_customer_id']]['totalCaseCount']++;
				}

			}
		}
	}

	if(count($customersWithStoppedCases) > 0) {
		?>
		<div class="creditor_wrapper">
			<div class="creditor_title"><?php echo $creditor['customerName'];?></div>
			<div class="creditor_report_wrapper">
				<table class="table">
					<tr>
						<th><?php echo $formText_CustomerName_output;?></th>
						<th><?php echo $formText_InterestAmount_output;?></th>
						<th><?php echo $formText_ReminderAmount_output;?></th>
						<th><?php echo $formText_CasesCount_output;?></th>
					</tr>
					<?php
					foreach($customersWithStoppedCases as $customerWithStoppedCases){
						?>
						<tr>
							<td><?php echo $customerWithStoppedCases['customerName'];?></td>
							<td><?php echo number_format($customerWithStoppedCases['totalInterestAmount'], 2, ","," ");?></td>
							<td><?php echo number_format($customerWithStoppedCases['totalReminderAmount'], 2, ","," ");?></td>
							<td><?php echo $customerWithStoppedCases['totalCaseCount'];?></td>
						</tr>
						<?php
					}
					?>
				</table>
			</div>
		</div>
		<style>
		.creditor_wrapper {
			background: #fff;
			margin-bottom: 10px;
		}
		.creditor_title {
			padding: 10px 15px 10px;
			font-size: 16px;
			font-weight: bold;
			border-bottom: 1px solid #cecece;
		}
		</style>
		<?php
	}
}
?>
<style>
.report_date_filter {
	margin: 10px 0px;
	
}
</style>
<script type="text/javascript">
$(function(){
	$(".datepicker").datepicker({
		firstDay: 1,
		dateFormat: "d.m.yy",
		onClose: function() {
			var data = {
				date_from: $(".dateFrom").val(),
				date_to: $(".dateTo").val()
			}
			loadView("creditors_report", data);
		}
	});
})
</script>
