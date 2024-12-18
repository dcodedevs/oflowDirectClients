<?php
if(!function_exists("APIconnectAccount")) include_once(__DIR__."/../../input/includes/APIconnect.php");

$sql = "select * from accountinfo";
$o_query = $o_main->db->query($sql);
$v_accountinfo = $o_query ? $o_query->row_array() : array();

$cid = $o_main->db->escape_str($_GET['cid']);

$sql = "SELECT * FROM cs_settlement WHERE id = $cid";
$o_query = $o_main->db->query($sql);
$settlement = $o_query ? $o_query->row_array() : array();

$list_filter = $_SESSION['list_filter'] ? ($_SESSION['list_filter']) : 'all';
$search_filter = $_SESSION['search_filter'] ? ($_SESSION['search_filter']) : '';
$search_by = $_SESSION['search_by'] ? ($_SESSION['search_by']) : 1;

require("fnc_get_settlement_sending_info.php");
$s_list_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=list&list_filter=".$list_filter."&search_filter=".$search_filter;

?>
<div id="p_container" class="p_container">
	<div class="p_containerInner">
		<div class="p_content">
			<div class="p_pageContent">
                <a href="<?php echo $s_list_link?>" class="output-click-helper optimize back-to-list" style="display: block; margin-bottom:10px;"><?php echo $formText_BackToList_outpup;?></a>
				<div class="p_pageDetails">
					<div class="p_pageDetailsTitle">
						<div class="" style="float: left">
							<?php echo $formText_Settlement_output;?>
							<div class="caseId"><span class="caseIdText"><?php echo $settlement['id'];?></span></div>
						</div>
						<div class="clear"></div>
                    </div>
                    <div class="p_contentBlock">
					    <div class="caseDetails">
					        <table class="mainTable" width="100%" border="0" cellpadding="0" cellspacing="0">
					        	<tr>
					                <td class="txt-label"><?php echo $formText_Date_output;?></td>
					                <td class="txt-value">
					                	<?php echo date("d.m.Y", strtotime($settlement['date']));?>
					                </td>
					            </tr>
					        </table>

					        <!-- <table class="mainTable btn-edit-table" width="100%" border="0" cellpadding="0" cellspacing="0">
					            <tr>
					                <td class="txt-label"></td>
					                <td class="txt-value"></td>
					                <td class="btn-edit" colspan="2"><?php if($moduleAccesslevel > 10) { ?><button class="output-btn small output-edit-case-detail editBtnIcon" data-case-id="<?php echo $cid; ?>"><span class="glyphicon glyphicon-pencil"></span></button><?php } ?></td>
					            </tr>
					        </table> -->
                        </div>
                    </div>

                    <div class="p_contentBlockWrapper">

                        <div class="p_pageDetailsSubTitle white dropdown_content_show ">
                            <?php echo $formText_CreditorSettlements_Output;?>
                        </div>
                        <div class="p_contentBlock dropdown_content noTopPadding">

                            <table class="table table-borderless claimsTable">
                                <tr>
                                    <th><?php echo $formText_Creditor_Output; ?></th>
                                    <th><?php echo $formText_Bankaccount_Output; ?></th>
                                    <th><?php echo $formText_CreditorLedger_Output; ?></th>
                                    <th><?php echo $formText_DebitorLedger_Output; ?></th>
                                    <th><?php echo $formText_CollectingCompanyLedger_Output; ?></th>
                                    <th><?php echo $formText_CreditorInvoice_Output; ?></th>
                                    <th><?php echo $formText_Balance_output; ?></th>
                                    <th><?php echo $formText_VatSpecification_output; ?></th>
                                    <th></th>
                                </tr>
                                <?php
                                $s_sql = "SELECT * FROM cs_settlement_line WHERE content_status < 2 AND cs_settlement_id = ? ORDER BY created DESC";
                                $o_query = $o_main->db->query($s_sql, array($settlement['id']));
                                $creditor_settlements = ($o_query ? $o_query->result_array() : array());
								$creditorLedgerSum_total = 0;
								$debitorLedgerSum_total = 0;
								$collectingLedgerSum_total = 0;
								$creditorInvoiceSum_total = 0;

                                foreach($creditor_settlements as $creditor_settlement)
                                {
									$creditorLedgerSum = 0;
									$debitorLedgerSum = 0;
									$collectingLedgerSum = 0;
									$creditorVatSum = 0;

									$s_sql = "SELECT c.*, ccp.email AS contact_person_email FROM creditor AS c
									LEFT JOIN creditor_contact_person AS ccp ON ccp.creditor_id = c.id AND ccp.receive_settlement_reports = 1
									WHERE c.id = ?";
									$o_query = $o_main->db->query($s_sql, array($creditor_settlement['creditor_id']));
									$creditor = ($o_query ? $o_query->row_array() : array());

									$s_sql = "SELECT cmv.*, cmv.case_id, CONCAT_WS(' ',deb.name, deb.middlename, deb.lastname) as debitorName FROM cs_mainbook_voucher cmv
									JOIN collecting_company_cases cc ON cc.id = cmv.case_id
									JOIN customer deb ON deb.id = cc.debitor_id
									WHERE IFNULL(cmv.settlement_id, 0) = ? AND cc.creditor_id = ?";
								    $o_query = $o_main->db->query($s_sql, array($settlement['id'], $creditor['id']));
								    $payments = $o_query ? $o_query->result_array() : array();

									$errorWithChecksum = false;
									$casesWithErrors = array();
									foreach($payments as $v_row){
										$totalMain = 0;
										$totalLedger = 0;
								        $s_sql = "SELECT * FROM cs_mainbook_transaction WHERE cs_mainbook_voucher_id = ?";
								        $o_query = $o_main->db->query($s_sql, array($v_row['id']));
								        $transactions = $o_query ? $o_query->result_array() : array();

										foreach($transactions as $transaction) {
											if($transaction['bookaccount_id'] == 15) {
												$debitorLedgerSum+=$transaction['amount'];
											} else if($transaction['bookaccount_id'] == 16) {
												$creditorLedgerSum+=$transaction['amount'];
											} else if($transaction['bookaccount_id'] == 22) {
												$collectingLedgerSum+=$transaction['amount'];
											} else if($transaction['bookaccount_id'] == 27){
												$creditorVatSum += $transaction['amount'];
											}
										}
										$transactionsToShow = array();
										foreach($transactions as $transaction) {
											$s_sql = "SELECT * FROM cs_bookaccount WHERE id = ?";
											$o_query = $o_main->db->query($s_sql, array($transaction['bookaccount_id']));
											$bookaccount = $o_query ? $o_query->row_array() : array();
											if($transaction['bookaccount_id'] == 20){
												$totalMain += $transaction['amount'];
											} else if($bookaccount['summarize_on_ledger'] == 2) {
												$transactionsToShow[] = $transaction;
											}
										}
										foreach($transactionsToShow as $transaction) {
											$s_sql = "SELECT * FROM cs_bookaccount WHERE id = ?";
											$o_query = $o_main->db->query($s_sql, array($transaction['bookaccount_id']));
											$bookaccount = $o_query ? $o_query->row_array() : array();
											$totalMain += $transaction['amount'];
										}

										foreach($transactions as $transaction) {
											if($transaction['bookaccount_id'] == 16){
												$totalLedger+=$transaction['amount'];
												break;
											}
										}
										if(floatval($totalLedger, 2) != floatval($totalMain, 2)) {
											$errorWithChecksum = true;
											$casesWithErrors[] = $v_row['case_id'];
										}
									}
									$s_edit_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details_creditor&creditorId=".$creditor_settlement['creditor_id']."&settlementId=".$settlement['id'];
									$creditorLedgerSum_total += $creditorLedgerSum;
									$debitorLedgerSum_total += $debitorLedgerSum;
									$collectingLedgerSum_total += $collectingLedgerSum;
									if($creditorLedgerSum <= 0){
										$creditorInvoiceSum = 0;
									} else {
										$creditorLedgerSum -= $creditorVatSum;
										$creditorInvoiceSum = $creditorVatSum;
									}
									$creditorInvoiceSum_total+= $creditorInvoiceSum;
                                    ?>
                                        <tr class="output-click-helper"  data-href="<?php echo $s_edit_link;?>">
                                            <td><?php echo $creditor['companyname'];?>
												<div class="othercolor"><?php echo (''!=$creditor['contact_person_email']?$creditor['contact_person_email']:$creditor['companyEmail']);?></div>
											</td>
                                            <td><?php echo $creditor['bank_account'];?></td>
                                            <td><?php echo number_format($creditorLedgerSum, 2, ",", " ");?></td>
                                            <td><?php echo number_format($debitorLedgerSum, 2, ",", " ");?></td>
                                            <td><?php echo number_format($collectingLedgerSum, 2, ",", " ");?></td>
                                            <td><?php echo number_format($creditorInvoiceSum, 2, ",", " ");?></td>
                                            <td><?php  $balance_result = get_settlement_sending_info($o_main, $settlement['id'], $creditor['id']);
												if($balance_result['error'] == ""){													
													echo $formText_Valid_output; 
												} else {													
													echo $formText_ErrorWithSum_output; 
												}
											?>
											</td>
											<td>
												<a class="download_vat_specification" href="#" data-settlement-id="<?php echo $settlement['id'];?>" data-creditor-id="<?php echo $creditor['id'];?>">
													<?php echo $formText_Download_Output;?>
												</a>
											</td>
                                            <td>
												<div class="project-file">
													<div class="project-file-file">
														<?php if(!$errorWithChecksum) { ?>
															<a class="download_report" href="#" data-settlement-id="<?php echo $settlement['id'];?>" data-creditor-id="<?php echo $creditor['id'];?>">
																<?php echo $formText_Download_Output;?>
															</a>
															<?php 
															if($variables->developeraccess > 5) { ?>
															<br/>
																<a href="#" class="send_settlement" data-settlement-id="<?php echo $settlement['id'];?>" data-creditor-id="<?php echo $creditor['id'];?>"><?php echo $formText_SendSettlement_output;?></a>
															<?php } ?>
														<?php } else {
															echo $formText_ErrorWithChecksum_output;

															foreach($casesWithErrors as $caseWithErrors){
																$s_edit_link_case = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=CollectingCompanyCases&folderfile=output&folder=output&inc_obj=details&cid=".$caseWithErrors;

																?>
																<div> <a href="<?php echo $s_edit_link_case?>" target="_blank"><?php echo $caseWithErrors;?></a></div>
																<?php
															}
														}?>
													</div>
												</div>
											</td>
                                        </tr>
                                    <?php
                                }
                                ?>
								<tr>
									<td><?php echo $formText_Total_output;?></td>
									<td></td>
									<td><?php echo number_format($creditorLedgerSum_total, 2, ",", " ");?></td>
									<td><?php echo number_format($debitorLedgerSum_total, 2, ",", " ");?></td>
									<td><?php echo number_format($collectingLedgerSum_total, 2, ",", " ");?></td>
									<td><?php echo number_format($creditorInvoiceSum_total, 2, ",", " ");?></td>									
									<td></td>					
									<td></td>					
									<td></td>
								</tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
	.generatePdf {
		color: #46b2e2;
		cursor: pointer;
	}
	.totalSum {
		background: #f0f0f0;
	}
	.spaceWrapper td,
	.totalSum td {
		border: 0 !important;
	}
	.totalSum td.first {
		padding: 10px 10px !important;
	}
	.totalSum td.second {
		padding: 10px 0px !important;
	}
	.caseDetails .txt-label {
		width:30%;
	}
	.p_pageContent .btn-edit {
		text-align: right;
		margin-top: -15px;
	}
	.p_pageContent .btn-edit-table {
		margin-top: -25px;
	}
	.p_pageDetailsTitle .caseId {
		display: inline-block;
	}
	.caseStatus {
		float: right;
	}
	.p_contentBlockWrapper {
		position: relative;
		border-bottom: 2px solid #316896;
	}
	.p_contentBlockWrapper .p_contentBlock {
		border-bottom:0;
	}
	.p_contentBlockWrapper .p_pageDetailsSubTitle .showArrow {
	    float: right;
	    cursor: pointer;
	    color: #2996E7;
	    margin-left: 10px;
	    position: absolute;
	    right: 10px;
	    top: 12px;
	}
	.p_contentBlock.noTopPadding {
		padding-top: 0;
	}

	.table-borderless > tbody > tr > td,
	.table-borderless > tbody > tr > th,
	.table-borderless > tfoot > tr > td,
	.table-borderless > tfoot > tr > th,
	.table-borderless > thead > tr > td,
	.table-borderless > thead > tr > th {
		border: 0;
	}
	.commentBlock {
		border-bottom: 1px solid #ddd;
		border-radius: 0px;
		padding: 10px 0px;
	}
	.commentBlock .createdLabel {
		color: #8f8f8f !important;
	}
	.commentBlock .table {
		margin-bottom: 0;
	}
	.feedbackBlock {
		background: #f0f0f0;
	}
	#p_container .commentBlock td {
		padding: 0px 0px;
	}

	.ticketCommentBlock {
	    text-align: left;
	    width: 70%;
		float: right;
	}
	.ticketCommentBlock .inline_info {
	    float: right;
	    margin-left: 10px;
	}
	.ticketCommentBlock .table {
		display: block;
	    margin-bottom: 0;
		border: 1px solid #ddd;
	    border-radius: 5px;
	    margin-bottom: 10px;
	    padding: 7px 15px;
		margin-top: 5px;
	    background: #f0f0f0;
	}
	.ticketCommentBlock.from_customer {
	    text-align: left;
	    float: left;
	}
	.ticketCommentBlock.from_customer .table {
	    background: #bcdef7;
	}
	.ticketCommentBlock.from_customer .inline_info {
	    float: left;
	    margin-right: 10px;
	    margin-left: 0;
	}

	.employeeImage {
		width: 40px;
		height: 40px;
		overflow: hidden;
		position: relative;
		border-radius: 20px;
		overflow: hidden;
	    float: right;
	    margin-left: 10px;
	}
	.employeeImage img {
		width: calc(100% + 4px);
		height: auto;
		position: absolute;
	  	left: 50%;
	  	top: 50%;
	  	transform: translate(-50%, -50%);
	}
	.employeeInfo {
	    float: right;
	    width: calc(100% - 50px);
	}
	.ticketCommentBlock.from_customer .employeeImage {
	    float: left;
	    margin-left: 0;
	    margin-right: 10px;
	}
	.ticketCommentBlock.from_customer .employeeInfo {
	    float: left;
	}
	.detailContainer {
		margin-bottom: 10px;
	}
	.claimsTable > tbody > tr > td,
	.claimsTable > tbody > tr > th,
	.claimsTable > tfoot > tr > td,
	.claimsTable > tfoot > tr > th,
	.claimsTable > thead > tr > td,
	.claimsTable > thead > tr > th {
		border-bottom: 1px solid #ddd;
		padding: 5px 0px;
	}
	.caseDetails {
		position: relative;
	}
	.caseDetails .mainTable {
		width: 60%;
	}
	.collectinglevelDisplay {
		position: absolute;
		top: 0;
		right: 0;
		padding: 10px 15px;
		border: 2px solid #80d88a;
		border-radius: 5px;
	}
	.levelText {
		font-weight: bold;
		float: right;
		margin-left: 30px;
		color: #80d88a;
	}
	.paymentPlanTable {
		width: 60%;
	}
	.othercolor {
		color: #cecece;
	}
	.send_settlement {
		margin-left: 15px;
	}
</style>
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
				var data2 = {
					cid: '<?php echo $cid;?>'
				}
            	loadView("details", data2);
            }
          // window.location.reload();
        }
		$(this).removeClass('opened');
	}
};
function submit_post_via_hidden_form(url, params) {
	var f = $("<form method='POST' target='_blank' style='display:none;'></form>").attr({
		action: url
	}).appendTo(document.body);
	for (var i in params) {
		if (params.hasOwnProperty(i)) {
			$('<input type="hidden" />').attr({
				name: i,
				value: params[i]
			}).appendTo(f);
		}
	}
	f.submit();
	f.remove();
}
$(function() {
	$(document).off('click', '.output-click-helper').on('click', '.output-click-helper', function(e){
		if(e.target.nodeName == 'TD') fw_load_ajax($(this).data('href'),'',true);
	});
	$(".send_settlement").off("click").on("click", function(){
		
		var data = {creditorId:  $(this).data("creditor-id"), settlementId: $(this).data("settlement-id")};
		loadView('send_settlement_to_24', data);
	})
	$('.download_report').on('click', function(e) {
		e.preventDefault();
		var data = {
			fwajax: 1,
			fw_nocss: 1,
			settlementId: $(this).data("settlement-id"),
			creditorId: $(this).data("creditor-id")
		};

		// ajaxCall("check_settlement_pdf", data, function(json) {
		// 	if(json.error !== undefined) {
		// 		var _msg = '';
		// 		$.each(json.error, function(index, value){
		// 			var _type = Array("error");
		// 			if(index.length > 0 && index.indexOf("_") > 0) _type = index.split("_");
		// 			_msg = _msg + '<div class="msg-' + _type[0] + '">' + value + '</div>';
		// 		});
		// 		$('#popupeditboxcontent').html('');
		// 		$('#popupeditboxcontent').html(_msg);
		// 		out_popup = $('#popupeditbox').bPopup(out_popup_options);
		// 		$("#popupeditbox:not(.opened)").remove();
		// 	} else {
				submit_post_via_hidden_form('<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=download_settlement_pdf"; ?>', data);
		// 	}
		// });
	});
	$(".download_vat_specification").on('click', function(e) {
		e.preventDefault();
		var data = {
			fwajax: 1,
			fw_nocss: 1,
			settlementId: $(this).data("settlement-id"),
			creditorId: $(this).data("creditor-id")
		};

		submit_post_via_hidden_form('<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=download_vat_specification_pdf"; ?>', data);
		
	});
})
</script>
