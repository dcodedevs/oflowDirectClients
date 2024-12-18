<?php
$o_query = $o_main->db->get('ownercompany_accountconfig');
$ownercompany_accountconfig = $o_query ? $o_query->row_array() : array();
?>
<div class="p_headerLine"><?php
if($moduleAccesslevel > 10)
{
	/*
	if(intval($_GET['transaction_log']) == 0) {
	?>
	<div class="reminderTransactions btnStyle">
		<div class="plusTextBox active">
			<div class="text"><?php echo $formText_ReminderTransactionLogs_Output; ?></div>
		</div>
		<div class="clear"></div>
	</div>
<?php } else { ?>
	<div class="mainLogs btnStyle">
		<div class="plusTextBox active">
			<div class="text"><?php echo $formText_BackToMainLogs_Output; ?></div>
		</div>
		<div class="clear"></div>
	</div>
<?php } ?>
	<div class="clear"></div>
	<?php
	*/
}
?></div>


<script type="text/javascript">
$(".reminderTransactions").on("click", function(e){
	e.preventDefault();
	var data = {
		transaction_log: 1
	}
	loadView("list", data);
})
$(".mainLogs").on("click", function(e){
	e.preventDefault();
	var data = {
	}
	loadView("list", data);
})
</script>
<style>
	.p_headerLine .btnStyle.addEditCustomerGroup {
		margin-left: 40px;
	}
	.p_headerLine .btnStyle.importPayments {
		margin-left: 40px;
	}
	.p_headerLine .btnStyle.addPayment {
		margin-left: 40px;
	}
</style>
