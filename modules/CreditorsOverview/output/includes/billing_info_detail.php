<?php
$cid = isset($_GET['cid']) ? $_GET['cid'] : 0;

$sql = "SELECT * FROM creditor WHERE id = ?";
$o_query = $o_main->db->query($sql, array($cid));
$creditor = $o_query ? $o_query->row_array() : array();
if($creditor){

$s_list_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=billing_info&cid=".$cid;

?>
<div id="p_container" class="p_container <?php echo $folderName; ?>">
	<div class="p_containerInner">
		<div class="p_content">
			<div class="p_pageContent">
				<a href="<?php echo $s_list_link?>" class="output-click-helper optimize back-to-list" style="display: block; margin-bottom:10px; float: left;"><?php echo $formText_BackToBillingDates_outpup;?></a>
				<div class="clear"></div>
				<?php

				$status_sql = " AND IFNULL(cccl.fees_status, 0) = 1";
				$s_sql = "SELECT cccl.*, CONCAT_WS(' ', c.name, c.middlename, c.lastname) as customerName, cc.payed_fee_amount, cc.payed_interest_amount, ccrs.date as reportDate
				FROM collecting_cases_claim_letter cccl
				LEFT OUTER JOIN collecting_cases cc ON cc.id = cccl.case_id
				LEFT OUTER JOIN customer c ON c.id = cc.debitor_id
				JOIN collecting_cases_report_24so ccrs ON ccrs.id = cccl.billing_report_id
				WHERE MONTH(ccrs.date) = ? AND YEAR(ccrs.date) = ?  AND ccrs.creditor_id = ?".$status_sql;
				$o_query = $o_main->db->query($s_sql, array(date("m", strtotime($_GET['month'])), date("Y", strtotime($_GET['month'])), $creditor['id']));
				$claimletters_sent_without_fees = $o_query ? $o_query->result_array() : array();

				$status_sql = " AND IFNULL(cccl.fees_status, 0) = 2";
				$s_sql = "SELECT cccl.*, CONCAT_WS(' ', c.name, c.middlename, c.lastname) as customerName, cc.payed_fee_amount, cc.payed_interest_amount, ccrs.date as reportDate FROM collecting_cases_claim_letter cccl
				LEFT OUTER JOIN collecting_cases cc ON cc.id = cccl.case_id
				LEFT OUTER JOIN customer c ON c.id = cc.debitor_id
				JOIN collecting_cases_report_24so ccrs ON ccrs.id = cccl.billing_report_id
				WHERE MONTH(ccrs.date) = ? AND YEAR(ccrs.date) = ?  AND ccrs.creditor_id = ?".$status_sql;
				$o_query = $o_main->db->query($s_sql, array(date("m", strtotime($_GET['month'])), date("Y", strtotime($_GET['month'])), $creditor['id']));
				$claimletters_fees_forgiven = $o_query ? $o_query->result_array() : array();


				$status_sql = " AND IFNULL(cccl.fees_status, 0) = 0";
				$s_sql = "SELECT cccl.*, CONCAT_WS(' ', c.name, c.middlename, c.lastname) as customerName, cc.payed_fee_amount, cc.payed_interest_amount, ccrs.date as reportDate FROM collecting_cases_claim_letter cccl
				LEFT OUTER JOIN collecting_cases cc ON cc.id = cccl.case_id
				LEFT OUTER JOIN customer c ON c.id = cc.debitor_id
				JOIN collecting_cases_report_24so ccrs ON ccrs.id = cccl.billing_report_id
				WHERE MONTH(ccrs.date) = ? AND YEAR(ccrs.date) = ?  AND ccrs.creditor_id = ?".$status_sql;
				$s_sql .= " GROUP BY cc.id";
				$o_query = $o_main->db->query($s_sql, array(date("m", strtotime($_GET['month'])), date("Y", strtotime($_GET['month'])), $creditor['id']));
				$claimletters_fees_billed = $o_query ? $o_query->result_array() : array();

				$status_sql = " AND IFNULL(performed_action, 0) = 0 AND sent_to_external_company = 1 AND sending_status = 1";
				$s_sql = "SELECT cccl.*, CONCAT_WS(' ', c.name, c.middlename, c.lastname) as customerName, cc.payed_fee_amount, cc.payed_interest_amount, ccrs.date as reportDate FROM collecting_cases_claim_letter cccl
				LEFT OUTER JOIN collecting_cases cc ON cc.id = cccl.case_id
				LEFT OUTER JOIN customer c ON c.id = cc.debitor_id
				JOIN collecting_cases_report_24so ccrs ON ccrs.id = cccl.billing_report_id
				WHERE MONTH(ccrs.date) = ? AND YEAR(ccrs.date) = ?  AND ccrs.creditor_id = ?".$status_sql;
				$o_query = $o_main->db->query($s_sql, array(date("m", strtotime($_GET['month'])), date("Y", strtotime($_GET['month'])), $creditor['id']));
				$claimletters_printed = $o_query ? $o_query->result_array() : array();

				$sentWithoutFeesCount = count($claimletters_sent_without_fees);
				$feesForgivenCount = count($claimletters_fees_forgiven);
				$feesBilledCount = count($claimletters_fees_billed);
				$printedCount = count($claimletters_printed);
				?>
				<div class="billing_block">
					<div class="billing_block_title">
						<?php echo $formText_SentWithoutFees_output;?> (<?php echo $sentWithoutFeesCount?>)
					</div>
					<div class="billing_block_content">
						<table class="table">
							<tr>
								<th><?php echo $formText_BilledDate_output;?></th>
								<th><?php echo $formText_CaseId_output;?></th>
								<th><?php echo $formText_Date_output;?></th>
								<th><?php echo $formText_DueDate_output;?></th>
								<th><?php echo $formText_CustomerName_output;?></th>
								<th><?php echo $formText_TotalAmount_output;?></th>
							</tr>
							<?php
							foreach($claimletters_sent_without_fees as $claimletter) {
								$s_edit_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=CollectingCases&folderfile=output&folder=output&inc_obj=details&cid=".$claimletter['case_id'];
								?>
								<tr>
									<td><?php echo date("d.m.Y", strtotime($claimletter['reportDate']));?></td>
									<td><a href="<?php echo $s_edit_link;?>" target="_blank"><?php echo $claimletter['case_id'];?></a></td>
									<td><?php echo date("d.m.Y", strtotime($claimletter['created']));?></td>
									<td><?php echo date("d.m.Y", strtotime($claimletter['due_date']));?></td>
									<td><?php echo $claimletter['customerName'];?></td>
									<td><?php echo number_format($claimletter['total_amount'], 2, ","," ");?></td>
								</tr>
								<?php
							}
							?>
						</table>
					</div>
				</div>
				<div class="billing_block">
					<div class="billing_block_title">
						<?php echo $formText_FeesForgiven_output;?> (<?php echo $feesForgivenCount?>)
					</div>
					<div class="billing_block_content">
						<table class="table">
							<tr>
								<th><?php echo $formText_BilledDate_output;?></th>
								<th><?php echo $formText_CaseId_output;?></th>
								<th><?php echo $formText_Date_output;?></th>
								<th><?php echo $formText_DueDate_output;?></th>
								<th><?php echo $formText_CustomerName_output;?></th>
								<th><?php echo $formText_TotalAmount_output;?></th>
							</tr>
							<?php
							foreach($claimletters_fees_forgiven as $claimletter) {
								$s_edit_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=CollectingCases&folderfile=output&folder=output&inc_obj=details&cid=".$claimletter['case_id'];
								?>
								<tr>
									<td><?php echo date("d.m.Y", strtotime($claimletter['reportDate']));?></td>
									<td><a href="<?php echo $s_edit_link;?>" target="_blank"><?php echo $claimletter['case_id'];?></a></td>
									<td><?php echo date("d.m.Y", strtotime($claimletter['created']));?></td>
									<td><?php echo date("d.m.Y", strtotime($claimletter['due_date']));?></td>
									<td><?php echo $claimletter['customerName'];?></td>
									<td><?php echo number_format($claimletter['total_amount'], 2, ","," ");?></td>
								</tr>
								<?php
							}
							?>
						</table>
					</div>
				</div>
				<div class="billing_block">
					<div class="billing_block_title">
						<?php echo $formText_FeesBilled_output;?> (<?php echo $feesBilledCount?>)
					</div>
					<div class="billing_block_content">
						<table class="table">
							<tr>
								<th><?php echo $formText_BilledDate_output;?></th>
								<th><?php echo $formText_CaseId_output;?></th>
								<th><?php echo $formText_Date_output;?></th>
								<th><?php echo $formText_DueDate_output;?></th>
								<th><?php echo $formText_CustomerName_output;?></th>
								<th><?php echo $formText_TotalAmount_output;?></th>
								<th><?php echo $formText_FeePayed_output;?></th>
								<th><?php echo $formText_InterestPayed_output;?></th>
								<th><?php echo $formText_Billed_output;?></th>
							</tr>
							<?php
							foreach($claimletters_fees_billed as $claimletter) {
								$s_edit_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=CollectingCases&folderfile=output&folder=output&inc_obj=details&cid=".$claimletter['case_id'];
								?>
								<tr>
									<td><?php echo date("d.m.Y", strtotime($claimletter['reportDate']));?></td>
									<td><a href="<?php echo $s_edit_link;?>" target="_blank"><?php echo $claimletter['case_id'];?></a></td>
									<td><?php echo date("d.m.Y", strtotime($claimletter['created']));?></td>
									<td><?php echo date("d.m.Y", strtotime($claimletter['due_date']));?></td>
									<td><?php echo $claimletter['customerName'];?></td>
									<td><?php echo number_format($claimletter['total_amount'], 2, ","," ");?></td>
									<td><?php echo number_format($claimletter['payed_fee_amount'], 2, ","," ");?></td>
									<td><?php echo number_format($claimletter['payed_interest_amount'], 2, ","," ");?></td>
									<td><?php echo number_format(round(($claimletter['payed_interest_amount'] + $claimletter['payed_fee_amount'])/2), 2, ","," ");?></td>
								</tr>
								<?php
							}
							?>
						</table>
					</div>
				</div>
				<div class="billing_block">
					<div class="billing_block_title">
						<?php echo $formText_Printed_output;?> (<?php echo $printedCount?>)
					</div>
					<div class="billing_block_content">
						<table class="table">
							<tr>
								<th><?php echo $formText_BilledDate_output;?></th>
								<th><?php echo $formText_CaseId_output;?></th>
								<th><?php echo $formText_Date_output;?></th>
								<th><?php echo $formText_DueDate_output;?></th>
								<th><?php echo $formText_CustomerName_output;?></th>
								<th><?php echo $formText_TotalAmount_output;?></th>
							</tr>
							<?php
							foreach($claimletters_printed as $claimletter) {
								$s_edit_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=CollectingCases&folderfile=output&folder=output&inc_obj=details&cid=".$claimletter['case_id'];
								?>
								<tr>
									<td><?php echo date("d.m.Y", strtotime($claimletter['reportDate']));?></td>
									<td><a href="<?php echo $s_edit_link;?>" target="_blank"><?php echo $claimletter['case_id'];?></a></td>
									<td><?php echo date("d.m.Y", strtotime($claimletter['created']));?></td>
									<td><?php echo date("d.m.Y", strtotime($claimletter['due_date']));?></td>
									<td><?php echo $claimletter['customerName'];?></td>
									<td><?php echo number_format($claimletter['total_amount'], 2, ","," ");?></td>
								</tr>
								<?php
							}
							?>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<style>
	.billing_block {
		background: #fff;
		margin-bottom: 10px;
	}
	.billing_block_title {
		padding: 10px 10px;
		cursor: pointer;
		font-weight: bold;
	}
	.billing_block_content {
		display: none;

	}
</style>
<script type="text/javascript">
	$(function(){
		$(".billing_block_title").off("click").on("click", function(){
			var parent = $(this).parents(".billing_block");
			parent.find(".billing_block_content").toggle();
		})
	})
</script>
<?php } ?>
