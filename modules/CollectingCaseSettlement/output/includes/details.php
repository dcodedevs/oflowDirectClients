<?php
if(!function_exists("APIconnectAccount")) include_once(__DIR__."/../../input/includes/APIconnect.php");

$sql = "select * from accountinfo";
$o_query = $o_main->db->query($sql);
$v_accountinfo = $o_query ? $o_query->row_array() : array();

$cid = $o_main->db->escape_str($_GET['cid']);

$sql = "SELECT * FROM collectingcompany_settlement WHERE id = $cid";
$o_query = $o_main->db->query($sql);
$settlement = $o_query ? $o_query->row_array() : array();

$list_filter = $_SESSION['list_filter'] ? ($_SESSION['list_filter']) : 'all';
$search_filter = $_SESSION['search_filter'] ? ($_SESSION['search_filter']) : '';
$search_by = $_SESSION['search_by'] ? ($_SESSION['search_by']) : 1;

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
					        	<tr>
					                <td class="txt-label"><?php echo $formText_CollectingCompanyAmount_output;?></td>
					                <td class="txt-value">
					                	<?php echo number_format($settlement['collectingcompany_total_amount'], 2, ",", "");?>
					                </td>
					            </tr>
								<tr>
					                <td class="txt-label"><?php echo $formText_CreditorAmount_output;?></td>
					                <td class="txt-value">
					                	<?php echo number_format($settlement['creditor_total_amount'], 2, ",", "");?>
					                </td>
					            </tr>
					            <tr>
					                <td class="txt-label"><?php echo $formText_DebitorAmount_output;?></td>
					                <td class="txt-value"><?php echo number_format($settlement['debitor_total_amount'], 2, ",", "");?></td>
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
                                    <th><?php echo $formText_CollectingCompanyAmount_output; ?></th>
                                    <th><?php echo $formText_CreditorAmount_output; ?></th>
                                    <th><?php echo $formText_DebitorAmount_output; ?></th>
                                    <th></th>
                                </tr>
                                <?php
                                $s_sql = "SELECT * FROM creditor_settlement WHERE content_status < 2 AND collectingcompany_settlement_id = ? ORDER BY created DESC";
                                $o_query = $o_main->db->query($s_sql, array($settlement['id']));
                                $creditor_settlements = ($o_query ? $o_query->result_array() : array());

                                foreach($creditor_settlements as $creditor_settlement)
                                {
                                    $s_sql = "SELECT customer.* FROM customer LEFT JOIN creditor ON creditor.customer_id = customer.id  WHERE creditor.id = ?";
                                    $o_query = $o_main->db->query($s_sql, array($creditor_settlement['creditor_id']));
                                    $creditor = ($o_query ? $o_query->row_array() : array());
									$s_edit_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details_creditor&creditorId=".$creditor_settlement['creditor_id']."&settlementId=".$settlement['id'];

                                    ?>
                                        <tr class="output-click-helper"  data-href="<?php echo $s_edit_link;?>">
                                            <td><?php echo $creditor['name']." ".$creditor['middlename']." ".$creditor['lastname'];?></td>
                                            <td><?php echo number_format($creditor_settlement['collectingcompany_amount'], 2, ",", "");?></td>
                                            <td><?php echo number_format($creditor_settlement['creditor_amount'], 2, ",", "");?></td>
                                            <td><?php echo number_format($creditor_settlement['debitor_amount'], 2, ",", "");?></td>
                                            <td>
												<?php if($creditor_settlement['pdf'] != "") { ?>
													<div class="project-file">
														<div class="project-file-file">
															<a href="<?php echo $extradomaindirroot.$creditor_settlement['pdf'];?>" download><?php echo $formText_Download_Output;?></a>
														</div>
													</div>
												<?php } ?>
											</td>
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
</style>
<script type="text/javascript">

$(function() {
	$(document).off('click', '.output-click-helper').on('click', '.output-click-helper', function(e){
		if(e.target.nodeName == 'TD') fw_load_ajax($(this).data('href'),'',true);
	});
})
</script>
