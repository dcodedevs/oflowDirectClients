<?php
// List btn
require_once __DIR__ . '/list_btn.php';
if(!function_exists("APIconnectAccount")) include_once(__DIR__."/../../input/includes/APIconnect.php");

$sql = "select * from accountinfo";
$o_query = $o_main->db->query($sql);
$v_accountinfo = $o_query ? $o_query->row_array() : array();

$cid = $o_main->db->escape_str($_GET['cid']);

$sql = "SELECT * FROM covering_order_and_split WHERE id = $cid";
$o_query = $o_main->db->query($sql);
$process = $o_query ? $o_query->row_array() : array();

function formatHour($hour){
	return str_replace(".", ",", floatval(number_format($hour, 2, ".", "")));
}

$list_filter = $_SESSION['list_filter'] ? ($_SESSION['list_filter']) : 'all';
$responsibleperson_filter = $_SESSION['responsibleperson_filter'] ? ($_SESSION['responsibleperson_filter']) : '';
$list_filter_main = $_SESSION['list_filter_main'] ? ($_SESSION['list_filter_main']) : '';
$search_filter = $_SESSION['search_filter'] ? ($_SESSION['search_filter']) : '';
$casetype_filter = $_SESSION['casetype_filter'] ? $_SESSION['casetype_filter'] : '';
$search_by = $_SESSION['search_by'] ? ($_SESSION['search_by']) : 1;

$s_list_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=list&list_filter=".$list_filter."&search_filter=".$search_filter;

$s_page_reload_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$caseData['id']."&view=".$list_filter_main;

$registered_group_list = array();
$v_membersystem = array();
$v_registered_usernames = array();
$o_query = $o_main->db->query("SELECT * FROM cache_userlist_access");
$v_cache_userlist = $o_query ? $o_query->result_array() : array();
foreach($v_cache_userlist as $v_user_cached_info) {
	$v_membersystem[$v_user_cached_info['username']] = $v_user_cached_info;
    if($v_user_cached_info['user_id'] > 0) $v_registered_usernames[] = $v_user_cached_info['username'];
	$registered_group_list[$v_user_cached_info['username']] = json_decode($v_user_cached_info['groups'], true);
}
?>

<div id="p_container" class="p_container">
	<div class="p_containerInner">
		<div class="p_content">
			<div class="p_pageContent">
                <a href="<?php echo $s_list_link?>" class="output-click-helper optimize back-to-list" style="display: block; margin-bottom:10px;"><?php echo $formText_BackToList_outpup;?></a>

				<div class="p_pageDetailsTitle">
					<div class="" style="float: left">
						<b><?php echo $formText_Process_output;?>:</b>
						<?php echo $process['name'];?>
						<?php if($moduleAccesslevel > 10) { ?><button class="addEntryBtn small output-edit-coverlines" data-cover-id="<?php echo $cid; ?>"><?php echo $formText_Add_output;?></button><?php } ?>
					</div>
					<div class="clear"></div>
				</div>
				<?php

				$sql = "SELECT * FROM covering_order_and_split_lines WHERE covering_order_and_split_id = ? ORDER BY covering_order ASC";
				$o_query = $o_main->db->query($sql, array($process['id']));
				$process_steps = $o_query ? $o_query->result_array() : array();
				if(count($process_steps)> 0) {
				?>
				<div class="p_pageDetails">
					<div class="">
						<div class="p_contentBlock dropdown_content">

							<table class="mainTable" width="100%" border="0" cellpadding="0" cellspacing="0">
								<tr>
									<th><?php echo $formText_CollectingClaimLineType_output;?></th>
									<th><?php echo $formText_CoveringOrder_output;?></th>
									<th><?php echo $formText_CollectionCompanyShare_Output;?></th>
									<th><?php echo $formText_CreditorShare_output;?></th>
									<th></th>
								</tr>
								<?php
								foreach($process_steps as $process_step) {
									$sql = "SELECT * FROM collecting_cases_claim_line_type_basisconfig WHERE id = ? ORDER BY sortnr ASC";
									$o_query = $o_main->db->query($sql, array($process_step['collecting_claim_line_type']));
									$claim_type = $o_query ? $o_query->row_array() : array();
									?>
									<tr>
										<td><?php echo $claim_type['type_name'];?></td>
										<td><?php echo $process_step['covering_order'];?></td>
										<td><?php echo $process_step['collectioncompany_share'];?></td>
										<td><?php echo $process_step['creditor_share'];?></td>
										<td>
											<?php if($moduleAccesslevel > 10) { ?>
												<button class="output-btn small output-edit-coverlines editBtnIcon" data-cover-id="<?php echo $cid; ?>" data-cover-line-id="<?php echo $process_step['id']; ?>"><span class="glyphicon glyphicon-pencil"></span></button>
												<button class="output-btn small output-delete-coverlines editBtnIcon" data-cover-id="<?php echo $cid; ?>" data-cover-line-id="<?php echo $process_step['id']; ?>"><span class="glyphicon glyphicon-trash"></span></button>

											<?php } ?></td>
										</td>
									</tr>
									<?php
								}
								?>
							</table>
						</div>
					</div>
				</div>
				<?php } ?>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
var out_popup;
var out_popup_options={
	follow: [true, true],
	followSpeed:0,
	fadeSpeed: 0,
	modalClose: false,
	escClose: false,
	closeClass:'b-close',
	onOpen: function(){
		$(this).addClass('opened');
	},
	onClose: function(){
		$(this).removeClass('opened');
		if($(this).is('.close-reload')) {
			loadView("details", {cid:"<?php echo $cid;?>"});
		}
	}
};
$(function(){
 	$(".output-edit-coverlines").unbind("click").on('click', function(e){
	 	e.preventDefault();
	 	var data = {
	 		cover_id: $(this).data('cover-id'),
			coverline_id: $(this).data('cover-line-id')
	 	};
	 	ajaxCall('edit_cover_lines', data, function(json) {
	 		$('#popupeditboxcontent').html('');
	 		$('#popupeditboxcontent').html(json.html);
	 		out_popup = $('#popupeditbox').bPopup(out_popup_options);
	 		$("#popupeditbox:not(.opened)").remove();
	 	});
 	});

	$(".output-delete-coverlines").unbind("click").on('click', function(e){
		e.preventDefault();
		var self = $(this);
		var data = {
			coverline_id: self.data('cover-line-id'),
			cover_id: self.data('cover-id'),
			action: "deleteCoverlines"
		};
		bootbox.confirm('<?php echo $formText_ConfirmDelete_output; ?>', function(result) {
			if (result) {
				ajaxCall('edit_cover_lines', data, function(json) {
					loadView("details", {cid:"<?php echo $cid;?>"});
				});
			}
		});
	});


})
</script>
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
		font-weight: normal !important;
	}
	.processStepDetails {
		border-bottom: 1px solid #efefef;
		margin-bottom: 0px;
	}
	.processStepDetails .txt-label {
		width: 30%;
		font-weight: normal !important;
	}
	.processStepContent .txt-label {
		width:30%;
		font-weight: normal !important;
	}
	.processStepAction .txt-label {
		width:30%;
		font-weight: normal !important;
	}
	.p_pageDetailsSubTitle2  {
		font-weight: bold;
		color: #888888;
	}
	.processStepContent {
		padding: 10px 0px;
	}
	.processStepAction {
		padding: 10px 0px;
	}
	.processStepDetails {
		padding: 10px 0px;
	}
	.processStepDetails td.txt-value {
		font-weight: bold !important;
	}


	.p_pageContent .btn-edit {
		text-align: right;
		margin-top: -15px;
	}
	.p_pageContent .btn-edit-table {
		margin-top: -20px;
	}
	.p_pageDetailsTitle {
		background: #fff;
		margin-bottom: 15px;
		border: 1px solid #cecece;
		font-weight: normal;
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
