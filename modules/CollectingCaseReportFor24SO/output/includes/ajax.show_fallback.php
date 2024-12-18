
<div class="popupform">
<form class="output-form" action="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=edit_message_debitor";?>" method="post">
	<input type="hidden" name="fwajax" value="1">
	<input type="hidden" name="fw_nocss" value="1">
	<input type="hidden" name="output_form_submit" value="1">

	<div class="inner">
		<?php
		if(isset($_POST['report_id']) && $_POST['report_id'] > 0)
		{
			$status_sql = " AND cccl.report_status = 2";
			if($_POST['on_creation']){
				$status_sql = " AND cccl.report_status = 1";				
			}
			$s_sql = "SELECT * FROM collecting_cases_report_24so WHERE id = ?";
		    $o_query = $o_main->db->query($s_sql, array($_POST['report_id']));
		    $report = $o_query ? $o_query->row_array() : array();
			$s_sql = "SELECT cccl.*, CONCAT_WS(' ', c.name, c.middlename, c.lastname) as customerName FROM collecting_cases_claim_letter cccl
			LEFT OUTER JOIN collecting_cases cc ON cc.id = cccl.case_id
			LEFT OUTER JOIN customer c ON c.id = cc.debitor_id
			WHERE cccl.report_id = ?".$status_sql;
		    $o_query = $o_main->db->query($s_sql, array($report['id']));
		    $claimletters = $o_query ? $o_query->result_array() : array();
			?>
			<table class="table">
				<tr>
					<th><?php echo $formText_LetterId_output;?></th>
					<th><?php echo $formText_Date_output;?></th>
					<th><?php echo $formText_DueDate_output;?></th>
					<th><?php echo $formText_CustomerName_output;?></th>
					<th><?php echo $formText_TotalAmount_output;?></th>
				</tr>
				<?php
				foreach($claimletters as $claimletter) {

					$s_edit_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=CollectingCases&folderfile=output&folder=output&inc_obj=details&cid=".$claimletter['case_id'];
					?>
					<tr>
						<td><a href="<?php echo $s_edit_link;?>" target="_blank"><?php echo $claimletter['id'];?></a></td>
						<td><?php echo date("d.m.Y", strtotime($claimletter['created']));?></td>
						<td><?php echo date("d.m.Y", strtotime($claimletter['due_date']));?></td>
						<td><?php echo $claimletter['customerName'];?></td>
						<td><?php echo number_format($claimletter['total_amount'], 2, ","," ");?></td>
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
