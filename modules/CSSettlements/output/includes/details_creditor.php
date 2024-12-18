<?php
if(!function_exists("APIconnectAccount")) include_once(__DIR__."/../../input/includes/APIconnect.php");

$sql = "select * from accountinfo";
$o_query = $o_main->db->query($sql);
$v_accountinfo = $o_query ? $o_query->row_array() : array();

$settlementId = $o_main->db->escape_str($_GET['settlementId']);
$creditorId = $o_main->db->escape_str($_GET['creditorId']);


$s_sql = "SELECT * FROM cs_settlement_line WHERE content_status < 2 AND cs_settlement_id = ? AND creditor_id = ? ORDER BY created DESC";
$o_query = $o_main->db->query($s_sql, array($settlementId, $creditorId));
$creditor_settlement = ($o_query ? $o_query->row_array() : array());

$s_sql = "SELECT creditor.* FROM creditor WHERE creditor.id = ?";
$o_query = $o_main->db->query($s_sql, array($creditor_settlement['creditor_id']));
$creditor = ($o_query ? $o_query->row_array() : array());

$sql = "SELECT * FROM cs_settlement WHERE id = $settlementId";
$o_query = $o_main->db->query($sql);
$settlement = $o_query ? $o_query->row_array() : array();


$s_list_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$settlementId;

?>
<div id="p_container" class="p_container">
	<div class="p_containerInner">
		<div class="p_content">
			<div class="p_pageContent">
                <a href="<?php echo $s_list_link?>" class="output-click-helper optimize back-to-list" style="display: block; margin-bottom:10px;"><?php echo $formText_BackToDetails_outpup;?></a>
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
                        </div>
                    </div>

                    <div class="p_contentBlockWrapper">

                        <div class="p_pageDetailsSubTitle white dropdown_content_show ">
                            <?php echo $formText_CreditorPayments_Output;?>
                        </div>
                        <div class="p_contentBlock dropdown_content noTopPadding">
							<table class="table table-borderless claimsTable">
								<tr>
                                    <th><?php echo $formText_Date_output; ?></th>
                                    <th><?php echo $formText_Amount_output; ?> / <?php echo $formText_DebitorName_output; ?> - <?php echo $formText_CaseId_output; ?> </th>
                                </tr>
								<?php
								$s_sql = "SELECT cmv.*, cmv.case_id, CONCAT_WS(' ',deb.name, deb.middlename, deb.lastname) as debitorName FROM cs_mainbook_voucher cmv
								JOIN collecting_company_cases cc ON cc.id = cmv.case_id
								JOIN customer deb ON deb.id = cc.debitor_id
								WHERE IFNULL(cmv.settlement_id, 0) = ? AND cc.creditor_id = ?";
							    $o_query = $o_main->db->query($s_sql, array($settlement['id'], $creditor['id']));
							    $payments = $o_query ? $o_query->result_array() : array();

								foreach($payments as $v_row){
							        $s_sql = "SELECT * FROM cs_mainbook_transaction WHERE cs_mainbook_voucher_id = ?";
							        $o_query = $o_main->db->query($s_sql, array($v_row['id']));
							        $transactions = $o_query ? $o_query->result_array() : array();

									$s_list_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=CollectingCompanyCases&folderfile=output&folder=output&inc_obj=details&cid=".$v_row['case_id'];

							         ?>
							        <tr>
							        <?php
							      	// Show default columns
							      	 ?>
								        <td><?php echo date("d.m.Y", strtotime($v_row['date']));?></td>
								        <td>
							                <?php echo number_format($v_row['amount'], 2, ",", " ");?> / <?php echo $v_row['debitorName'];?> - <a href="<?php echo $s_list_link;?>" target="_blank"><?php echo $v_row['case_id'];?></a>
							                <br/>
											<b><?php echo $formText_Transactions_output;?></b><br/><br/>
											<table class="table">
							                    <tr>
							                        <th><?php echo $formText_Type_output;?></th>
							                        <th><?php echo $formText_Bookaccount_output;?></th>
							                        <th class="rightAligned"><?php echo $formText_Amount_Output;?></th>
							                    </tr>
							                    <?php
							                    $debitor_share = 0;
							                    foreach( $transactions as $transaction) {
													if($transaction['bookaccount_id'] != 22 && $transaction['bookaccount_id'] != 16 && $transaction['bookaccount_id'] != 15 ) {
								                        $s_sql = "SELECT * FROM collecting_cases_claim_line_type_basisconfig WHERE id = ?";
								                        $o_query = $o_main->db->query($s_sql, array($transaction['collecting_claim_line_type']));
								                        $claim_line_type = $o_query ? $o_query->row_array() : array();

								                        $s_sql = "SELECT * FROM cs_bookaccount WHERE id = ?";
								                        $o_query = $o_main->db->query($s_sql, array($transaction['bookaccount_id']));
								                        $bookaccount = $o_query ? $o_query->row_array() : array();

								                         ?>
								                         <tr>
								                             <td><?php echo $claim_line_type['type_name'];?></td>
								                             <td><?php echo $bookaccount['name']; ?></td>
								                             <td class="rightAligned"><?php echo number_format($transaction['amount'], 2, ",", " "); ?></td>
								                         </tr>
							                    <?php }
												}
							                    ?>
							                </table>
											<b><?php echo $formText_Ledger_output;?></b><br/><br/>
											<table class="table">
							                    <tr>
							                        <th><?php echo $formText_Bookaccount_output;?></th>
							                        <th class="rightAligned"><?php echo $formText_Amount_Output;?></th>
							                    </tr>
							                    <?php
							                    $debitor_share = 0;
							                    foreach( $transactions as $transaction) {
													if($transaction['bookaccount_id'] != 22 && $transaction['bookaccount_id'] != 16 && $transaction['bookaccount_id'] != 15 ) {
													} else {

								                        $s_sql = "SELECT * FROM cs_bookaccount WHERE id = ?";
								                        $o_query = $o_main->db->query($s_sql, array($transaction['bookaccount_id']));
								                        $bookaccount = $o_query ? $o_query->row_array() : array();

								                         ?>
								                         <tr>
								                             <td><?php echo $bookaccount['name']; ?></td>
								                             <td class="rightAligned"><?php echo number_format($transaction['amount'], 2, ",", " "); ?></td>
								                         </tr>
							                    <?php }
												}
							                    ?>
							                </table>
							            </td>
							        </div>
								<?php } ?>
							</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
	.rightAligned {
		text-align: right;
	}
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
