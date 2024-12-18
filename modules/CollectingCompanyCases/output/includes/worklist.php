<?php
$page = 1;
require_once __DIR__ . '/list_btn.php';


$s_sql = "SELECT * FROM case_worklist ORDER BY name ASC";
$o_query = $o_main->db->query($s_sql, array());
$worklists = ($o_query ? $o_query->result_array() : array());
$default_list = $worklists[0]['id'];
$list_filter = $_GET['list_filter'] ? ($_GET['list_filter']) : $default_list;
$_SESSION['list_filter'] = $list_filter;
$sql_join = " JOIN case_worklist_connection cwc ON cwc.collecting_company_case_id = p.id";
$sql_where = " AND cwc.case_worklist_id = ".$o_main->db->escape($list_filter);

$sql = "SELECT p.*, c.name as creditorName, c2.name as debitorName,
	DATE_ADD(IFNULL(p.due_date, '2000-01-01'), INTERVAL IFNULL(nextStep.days_after_due_date, 0)+IF(step2.id > 0, 0,cred.days_overdue_startcase) DAY) as nextStepDate,
		IF(nextStep.id > 0, nextStep.name, '') as nextStepName, IF(nextStep.id > 0, nextStep.sending_action, '') as nextStepActionType,
		c.invoiceEmail, step2.name as processStepName, p.due_date as currentStepDate, nextStep.id as nextStepId
		 FROM collecting_company_cases p
		 LEFT JOIN creditor cred ON cred.id = p.creditor_id
		 LEFT JOIN customer c ON cred.customer_id = c.id
		 LEFT JOIN customer c2 ON c2.id = p.debitor_id
		 LEFT JOIN collecting_cases_collecting_process_steps step2 ON step2.id = p.collecting_cases_process_step_id AND step2.collecting_cases_collecting_process_id = p.collecting_process_id
		 LEFT JOIN collecting_cases_collecting_process_steps nextStep ON nextStep.sortnr = (IFNULL(step2.sortnr, 0)+1) AND nextStep.collecting_cases_collecting_process_id = p.collecting_process_id
		".$sql_join."
		WHERE p.content_status < 2 ".$sql_where;
    $o_query = $o_main->db->query($sql);
	$customerList = $o_query ? $o_query->result_array() :array();
?>
<div id="p_container" class="p_container <?php echo $folderName; ?>">
	<div class="p_containerInner">
		<div class="p_content">
			<div class="p_pageContent">
				<div class="output-filter">
				    <ul>
						<?php foreach($worklists as $worklist) { ?>
							<li class="item<?php echo ($list_filter == $worklist['id'] ? ' active':'');?>">
								<a class="topFilterlink" data-listfilter="all" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=worklist&list_filter=".$worklist['id']; ?>">
									<span class="link_wrapper">
										<span class="count"><?php echo $worklist_count[$worklist['id']]; ?></span>
										<?php echo $worklist['name'];?>
									</span>
								</a>
							</li>
						<?php } ?>
					</ul>
				</div>
				<div class="gtable" id="gtable_search">
				    <div class="gtable_row">
				        <div class="gtable_cell gtable_cell_head c1"><?php echo $formText_CaseNumber_output;?></div>
				        <div class="gtable_cell gtable_cell_head"><?php echo $formText_Debitor_output;?></div>
				        <div class="gtable_cell gtable_cell_head"><?php echo $formText_Creditor_output;?></div>
				        <div class="gtable_cell gtable_cell_head"><?php echo $formText_DueDate_output;?></div>
				        <div class="gtable_cell gtable_cell_head"><?php echo $formText_MainClaim_output;?></div>
				        <div class="gtable_cell gtable_cell_head"><?php echo $formText_Balance_output;?></div>
				        <div class="gtable_cell gtable_cell_head"><?php echo $formText_WillBeSentNow_output;?></div>
				        <div class="gtable_cell gtable_cell_head"><?php echo $formText_SubStatus_output;?></div>
				    </div>
					<?php
					foreach($customerList as $v_row){
						$s_sql = "SELECT * FROM collecting_cases_sub_status_basisconfig WHERE id = ? ";
						$o_query = $o_main->db->query($s_sql, array($v_row['sub_status']));
						$sub_status = ($o_query ? $o_query->row_array() : array());

						$s_edit_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$v_row['id']."&backToWorklist=1";

						$mainClaim = $v_row['original_main_claim'];

						$s_sql = "SELECT cccl.* FROM collecting_company_cases_claim_lines cccl
						LEFT OUTER JOIN collecting_cases_claim_line_type_basisconfig bconfig ON bconfig.id = cccl.claim_type
						WHERE cccl.content_status < 2 AND cccl.collecting_company_case_id = ? AND IFNULL(bconfig.not_include_in_claim, 0) = 0
						ORDER BY cccl.claim_type ASC, cccl.created DESC";
						$o_query = $o_main->db->query($s_sql, array($v_row['id']));
						$claims = ($o_query ? $o_query->result_array() : array());

						$s_sql = "SELECT * FROM collecting_cases_payments WHERE collecting_case_id = ? ORDER BY created DESC";
						$o_query = $o_main->db->query($s_sql, array($v_row['id']));
						$payments = ($o_query ? $o_query->result_array() : array());
						$balance = 0;

						foreach($claims as $claim) {
							$balance += $claim['amount'];
						}
						foreach($payments as $payment) {
							$balance -= $payment['amount'];
						}
						?>
						<div class="gtable_row output-click-helper" data-href="<?php echo $s_edit_link;?>">
						<?php
						// Show default columns
						 ?>
							<div class="gtable_cell c1"><?php echo $v_row['id'];?></div>
							<div class="gtable_cell"><?php echo $v_row['debitorName'];?></div>
							<div class="gtable_cell"><?php echo $v_row['creditorName'];?></div>
							<div class="gtable_cell"><?php if($v_row['due_date'] != "0000-00-00" && $v_row['due_date'] != ""){ echo date("d.m.Y", strtotime($v_row['due_date'])); }?></div>
							<div class="gtable_cell rightAlign"><?php echo number_format($mainClaim, 2, ",", " ");?></div>
							<div class="gtable_cell rightAlign">
								<?php echo number_format($balance, 2, ",", " ");?>

								<span class="glyphicon glyphicon-info-sign hoverEye">
									<div class="hoverInfo hoverInfo2 hoverInfoFull">
										<table class="table smallTable">
											<?php
											if(count($claims) > 0){ ?>
												<?php
												foreach($claims as $claim) {
													?>
													<tr>
														<td><b><?php echo $claim['name'];?></b></td>
														<td><?php if($claim['date'] != "0000-00-00" && $claim['date'] != "") echo date("d.m.Y", strtotime($claim['date']));?></td>
														<td><?php echo number_format($claim['amount'], 2, ",", " ");?></td>
													</tr>
													<?php
												}
											}
											if(count($payments) > 0){
											?>
												<?php
												foreach($payments as $payment) {
													?>
													<tr>
														<td><b><?php echo $formText_Payment_output;?></b></td>
														<td><?php if($payment['date'] != "0000-00-00" && $payment['date'] != "") echo date("d.m.Y", strtotime($payment['date']));?></td>
														<td><?php echo number_format($payment['amount'], 2, ",", " ");?></td>
													</tr>
													<?php
												}
											}
											?>
											<tr class="balance_row">
												<td><b><?php echo $formText_Balance_output;?></b></td>
												<td></td>
												<td><?php echo number_format($balance, 2, ",", " ");?></td>
											</tr>
										</table>
										<?php if(count($transaction_fees) > 0){ ?>
											<div class="resetTheCase" data-caseid="<?php echo $v_row['id'];?>"><?php echo $formText_ResetFees_output;?></div>
										<?php } ?>
										<?php
										if(count($transaction_payments) > 0){
											?>
											<div class="createRestNote" data-caseid="<?php echo $v_row['id'];?>"><?php echo $formText_CreateRestNote_output;?></div>
											<?php
										}
										?>
									</div>
								</span>
							</div>
							<div class="gtable_cell">
								<?php
								if($v_row['nextStepDate'] != "") echo date("d.m.Y", strtotime($v_row['nextStepDate']))."<br/>";

								echo $v_row['nextStepName'];
								?>
							</div>
							<div class="gtable_cell">
								<?php
								echo $sub_status['name'];
								?>
							</div>
						</div>
					<?php } ?>
				</div>

			</div>
		</div>
	</div>
</div>

<?php $list_filter = $_GET['list_filter'] ? $_GET['list_filter'] : 'all'; ?>
<script type="text/javascript">
var out_popup;
var out_popup_options={
	follow: [true, true],
	followSpeed: 0,
	fadeSpeed: 0,
	modalClose: false,
	escClose: false,
	closeClass:'b-close',
	onOpen: function(){
		$(this).addClass('opened');
		//$(this).find('.b-close').on('click', function(){out_popup.close();});
	},
	onClose: function(){
		if($(this).is('.close-reload')) {
			var redirectUrl = $(this).data("redirect");
			if(redirectUrl !== undefined && redirectUrl != ""){
				document.location.href = redirectUrl;
			} else {
            	loadView("worklist");
            }
          // window.location.reload();
        }
		$(this).removeClass('opened');
	}
};


$(document).ready(function() {

    var page = '<?php echo $page?>';
    // On customer row click
	$(document).off('click', '.output-click-helper').on('click', '.output-click-helper', function(e){
		if(e.target.nodeName == 'DIV'){
		 	fw_load_ajax($(this).data('href'),'',true);
		}
	});
});
</script>

<style>
.gtable_cell {
	position: relative;
}
.hoverEye {
	position: relative;
	color: #0284C9;
	margin-top: 2px;
}
.hoverEye .hoverInfo {
	font-family: 'PT Sans', sans-serif;
	width: 450px;
	display: none;
	color: #000;
	position: absolute;
	right: 0%;
	top: 100%;
	padding: 5px 10px;
	background: #fff;
	border: 1px solid #ccc;
	z-index: 1;
	max-height: 300px;
	overflow: auto;
}
.hoverEye .hoverInfo2 {
	width: 400px;
}
.hoverEye .hoverInfo3 {
	width: 300px;
}
.hoverEye .hoverInfoSmall {
	width: 200px;
}
.hoverEye.hover .hoverInfo {
	display: block;
}
</style>
