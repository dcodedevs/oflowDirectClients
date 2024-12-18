
<div class="popupform">
<form class="output-form" action="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=edit_message_debitor";?>" method="post">
	<input type="hidden" name="fwajax" value="1">
	<input type="hidden" name="fw_nocss" value="1">
	<input type="hidden" name="output_form_submit" value="1">

	<div class="inner">
		<?php
		if($_POST['creditor_id'] > 0 && $_POST['start_time'] != "" && $_POST['end_time'] != "")
		{
			$creditor_id = $_POST['creditor_id'];
			$month_start = $_POST['start_time'];
			$month_end = $_POST['end_time'];

			$status_sql = "";
			if($_POST['with_fees']) {
				$status_sql = " AND IFNULL(cccl.fees_status, 0) = 0 AND IFNULL(cc.fees_forgiven, 0) = 0";
			} else if($_POST['without_fees']) {
				$status_sql = " AND IFNULL(cccl.fees_status, 0) = 1";
			} else if($_POST['fees_forgiven']) {
				$status_sql = " AND IFNULL(cccl.fees_status, 0) = 0  AND IFNULL(cc.fees_forgiven, 0) = 1";
			} else if($_POST['printed']){
				$status_sql = " AND IFNULL(performed_action, 0) = 0 AND sent_to_external_company = 1 AND sending_status = 1";
			}
			if($status_sql != ""){
				if($_POST['with_fees']) {
					$s_sql = "SELECT cc.*, CONCAT_WS(' ', c.name, c.middlename, c.lastname) as customerName FROM collecting_cases cc
					LEFT OUTER JOIN customer c ON c.id = cc.debitor_id
					WHERE cc.reminder_process_started >= ? AND cc.reminder_process_started <= ? AND cc.creditor_id = ? AND IFNULL(cc.fees_forgiven, 0) = 0 GROUP BY cc.id";
				} else {
					if($_POST['fees_forgiven']) {
						$s_sql = "SELECT cccl.*, CONCAT_WS(' ', c.name, c.middlename, c.lastname) as customerName, cc.payed_fee_amount, cc.payed_interest_amount FROM collecting_cases_claim_letter cccl
						LEFT OUTER JOIN collecting_cases cc ON cc.id = cccl.case_id
						LEFT OUTER JOIN customer c ON c.id = cc.debitor_id
						WHERE cc.reminder_process_started >= ? AND cc.reminder_process_started <= ? AND cc.creditor_id = ?".$status_sql;
					} else {
						$s_sql = "SELECT cccl.*, CONCAT_WS(' ', c.name, c.middlename, c.lastname) as customerName, cc.payed_fee_amount, cc.payed_interest_amount FROM collecting_cases_claim_letter cccl
						LEFT OUTER JOIN collecting_cases cc ON cc.id = cccl.case_id
						LEFT OUTER JOIN customer c ON c.id = cc.debitor_id
						WHERE cc.reminder_process_started >= ? AND cc.reminder_process_started <= ? AND cc.creditor_id = ?".$status_sql;
					}
				}
				$o_query = $o_main->db->query($s_sql, array($month_start,$month_end, $creditor_id));
				$claimletters = $o_query ? $o_query->result_array() : array();
				?>
				<table class="table">
					<tr>
					<?php if($_POST['with_fees']) { ?>
						<th><?php echo $formText_CaseId_output;?></th>
					<?php } else { ?>
						<th><?php echo $formText_LetterId_output;?></th>
					<?php } ?>
						<th><?php echo $formText_Date_output;?></th>
						<th><?php echo $formText_DueDate_output;?></th>
						<th><?php echo $formText_CustomerName_output;?></th>
						<th><?php echo $formText_TotalAmount_output;?></th>
						<?php if($_POST['with_fees']) { ?>
							<th><?php echo $formText_FeePayed_output;?></th>
							<th><?php echo $formText_InterestPayed_output;?></th>
						<?php } ?>
					</tr>
					<?php
					foreach($claimletters as $claimletter) {
						$s_edit_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=CollectingCases&folderfile=output&folder=output&inc_obj=details&cid=".$claimletter['case_id'];

						?>
						<tr>
							<?php if($_POST['with_fees']) { ?>
								<td><a href="<?php echo $s_edit_link;?>" target="_blank"><?php echo $claimletter['id'];?></a></td>
							<?php } else { ?>
								<td><a href="<?php echo $s_edit_link;?>" target="_blank"><?php echo $claimletter['case_id'];?></a></td>
							<?php } ?>
							<td><?php echo date("d.m.Y", strtotime($claimletter['created']));?></td>
							<td><?php echo date("d.m.Y", strtotime($claimletter['due_date']));?></td>
							<td><?php echo $claimletter['customerName'];?></td>
							<td><?php echo number_format($claimletter['total_amount'], 2, ","," ");?></td>
							<?php if($_POST['with_fees']) { ?>
								<td><?php echo number_format($claimletter['payed_fee_amount'], 2, ","," ");?></td>
								<td><?php echo number_format($claimletter['payed_interest_amount'], 2, ","," ");?></td>
							<?php } ?>
						</tr>
						<?php
					}
					?>
				</table>
				<?php
			} else {
				echo $formText_WrongStatus_output;
			}
		}
		?>

	</div>
	<div class="popupformbtn">
		<button type="button" class="output-btn b-large b-close"><?php echo $formText_Close_Output;?></button>
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
