<?php
$month = isset($_GET['month']) ? "01-".$_GET['month'] : date("d-m-Y");

$firstDay = date("Y-m-01", strtotime($month));
$lastDay = date("Y-m-t", strtotime($month));

$start = new DateTime($firstDay);
$interval = DateInterval::createFromDateString('1 day');
$end = new DateTime($lastDay);
$end->setTime(0,0,1);
$period = new DatePeriod($start, $interval, $end);


?>
<div class="filterRow">
	<div class="startMonth">
		<?php echo $formText_Month_output; ?>
		<input type="text" class="startMonthPicker" value="<?php if($_GET['month']!="") { echo $_GET['month']; } else { echo date('m-Y'); }?>" autocomplete="off"/>
		<div class="clear"></div>
	</div>
</div>
<table class="table">
	<tr>
		<th><?php echo $formText_Date_output;?></th>
		<th><?php echo $formText_CasesStarted_output;?></th>
		<th><?php echo $formText_TotalFees_output;?></th>
		<th><?php echo $formText_TotalInterest_output;?></th>
	</tr>
	<?php
	foreach ($period as $dt) {
		$casesCreated = 0;
		$totalFees = 0;
		$totalInterest = 0;
		$nextPeriod = new DateTime($dt->format('Y-m-d'));
		$nextPeriod->add($interval);
		// var_dump($period[($index+1)]);
		$s_sql = "SELECT * FROM collecting_cases WHERE content_status < 2 AND DATE_FORMAT(reminder_process_started, '%Y-%m-%d') >= ? AND DATE_FORMAT(reminder_process_started, '%Y-%m-%d') < ?";
		$o_query = $o_main->db->query($s_sql, array($dt->format('Y-m-d'), $nextPeriod->format('Y-m-d')));
		$collecting_cases = $o_query ? $o_query->result_array() : array();
		foreach($collecting_cases as $collecting_case) {
			$casesCreated++;

			$s_sql = "SELECT creditor.* FROM creditor WHERE creditor.id = ?";
			$o_query = $o_main->db->query($s_sql, array($collecting_case['creditor_id']));
			$creditor = ($o_query ? $o_query->row_array() : array());
			$reminder_bookaccount = 8070;
			$interest_bookaccount = 8050;
			if($creditor['reminder_bookaccount'] != ""){
			    $reminder_bookaccount = $creditor['reminder_bookaccount'];
			}
			if($creditor['interest_bookaccount'] != ""){
			    $interest_bookaccount = $creditor['interest_bookaccount'];
			}

			$s_sql = "SELECT * FROM creditor_transactions WHERE creditor_id = ? AND collectingcase_id = ?";
			$o_query = $o_main->db->query($s_sql, array($collecting_case['creditor_id'], $collecting_case['id']));
			$invoice = $o_query ? $o_query->row_array() : array();
			if($invoice){
				$s_sql = "SELECT * FROM creditor_transactions WHERE open = 1 AND system_type='InvoiceCustomer' AND link_id = ? AND creditor_id = ? AND (collectingcase_id is null OR collectingcase_id = 0) AND comment LIKE '%\_%' ORDER BY created DESC";
				$o_query = $o_main->db->query($s_sql, array($invoice['link_id'], $invoice['creditor_id']));
				$claim_transactions = ($o_query ? $o_query->result_array() : array());
				foreach($claim_transactions as $claim_transaction) {
					$commentArray = explode("_", $claim_transaction['comment']);
					if($commentArray[1] == $reminder_bookaccount){
						$totalFees+= $claim_transaction['amount'];
					} else if($commentArray[1] == $interest_bookaccount){
						$totalInterest+= $claim_transaction['amount'];
					}
				}
			}
		}
		?>
		<tr>
			<td><?php echo $dt->format('d.m.Y');?></td>
			<td><?php echo $casesCreated; ?></td>
			<td><?php echo number_format($totalFees, 2, ",", " "); ?></td>
			<td><?php echo number_format($totalInterest, 2, ",", " "); ?></td>
		</tr>
		<?php
	}
	?>
</table>
<script type="text/javascript">


function updateListByMonth(){
	var data = {
		month: $(".startMonthPicker").val()
	}
	loadView("list", data);
}
$(".startMonthPicker").datepicker({
 	dateFormat: "mm-yy",
    changeMonth: true,
    changeYear: true,
    showButtonPanel: true,
    onClose: function(dateText, inst) {
        var month = $("#ui-datepicker-div .ui-datepicker-month :selected").val();
        var year = $("#ui-datepicker-div .ui-datepicker-year :selected").val();
        $(this).datepicker('setDate', new Date(year, month, 1));
        updateListByMonth();
    },
    // onClose: function(dateText, inst) {


    //     function isDonePressed(){
    //         return ($('#ui-datepicker-div').html().indexOf('ui-datepicker-close ui-state-default ui-priority-primary ui-corner-all ui-state-hover') > -1);
    //     }

    //     if (isDonePressed()){
    //         var month = $("#ui-datepicker-div .ui-datepicker-month :selected").val();
    //         var year = $("#ui-datepicker-div .ui-datepicker-year :selected").val();
    //         $(this).datepicker('setDate', new Date(year, month, 1)).trigger('change');

    //          $('.startMonthPicker').focusout()//Added to remove focus from datepicker input box on selecting date
    //          updateListByMonth();
    //     }
    // },
    beforeShow : function(input, inst) {
        inst.dpDiv.addClass('month_year_datepicker');

        if ((datestr = $(this).val()).length > 0) {
            year = datestr.substring(datestr.length-4, datestr.length);
            month = datestr.substring(0, 2);
            $(this).datepicker('option', 'defaultDate', new Date(year, month-1, 1));
    	}
    }
})
</script>
<style>
.filterRow {
	padding: 10px 5px;
	margin-bottom: 10px;
}
.p_pageContent {
	background: #ffffff;
}
.month_year_datepicker .ui-datepicker-calendar {
    display: none;
}
</style>
