<?php
// List btn
//require_once __DIR__ . '/list_btn.php';

$list_filter = $_SESSION['list_filter'] ? ($_SESSION['list_filter']) : 'not_sent';
$search_filter = $_SESSION['search_filter'] ? ($_SESSION['search_filter']) : '';

$s_list_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=list&list_filter=".$list_filter."&search_filter=".$search_filter;
$v_send_status = array(
	$formText_Waiting_Output,
	$formText_SentSuccessfully_Output,
	$formText_FailedSending_Output,
	$formText_BlockedUsersFromUnsubscriberLists
);
$v_status_icon = array(0=>"time",1=>"ok-sign",2=>"exclamation-sign");
$v_status_text = array(0=>"",1=>"text-success",2=>"text-danger");
?>
<div id="p_container" class="p_container">
	<div class="p_containerInner">
		<div class="p_content">
			<div class="p_pageContent">
                <a href="<?php echo $s_list_link?>" class="output-click-helper optimize back-to-list" style="display: block; margin-bottom:10px;"><?php echo $formText_BackToList_outpup;?></a>

				<div class="p_pageDetailsTitle">
					<div class="" style="float: left">
						<b><?php echo $formText_BatchId_output;?>:</b>
						<?php echo $_GET['cid'];?>
					</div>
					<div class="clear"></div>
				</div>
        				<?php
						$s_sql = "SELECT es.batch_id, es.content_id, es.subject, es.text, est.* FROM sys_emailsend es LEFT OUTER JOIN sys_emailsendto est ON est.emailsend_id = es.id WHERE content_table = 'collecting_cases' AND batch_id = '".$o_main->db->escape_str($_GET['cid'])."' ORDER BY est.receiver_email";
						$o_query = $o_main->db->query($s_sql);
						if($o_query && $o_query->num_rows()>0)
						{
							?><div class="gtable">
								<div class="gtable_row">
								<div class="gtable_cell gtable_cell_head">#</div>
								<div class="gtable_cell gtable_cell_head"><?php echo $formText_Debitor_Output;?></div>
								<div class="gtable_cell gtable_cell_head"><?php echo $formText_Name_Output;?></div>
								<div class="gtable_cell gtable_cell_head"><?php echo $formText_Email_Output;?></div>
								<div class="gtable_cell gtable_cell_head"><?php echo $formText_Message_Output;?></div>
								<div class="gtable_cell gtable_cell_head"><?php echo $formText_Sent_Output;?></div>
								<div class="gtable_cell gtable_cell_head"><?php echo $formText_Status_Output;?></div>
							</div>
							<?php
							$l_counter = 1;
							foreach($o_query->result_array() as $v_row)
							{
								$sql = "SELECT p.*, c2.name as debitorName, c.name as creditorName FROM collecting_cases p
								LEFT JOIN customer c2 ON c2.id = p.debitor_id
								LEFT JOIN customer c ON c.id = p.creditor_id
								WHERE p.id = ? ORDER BY p.sortnr ASC";
								$o_query = $o_main->db->query($sql, array($v_row['content_id']));
								$case = $o_query ? $o_query->row_array() : array();
								?>
								<div class="gtable_row">
									<div class="gtable_cell"><?php echo $l_counter;?></div>
									<div class="gtable_cell"><a href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=CollectingCases&folderfile=output&folder=output&inc_obj=details&cid=".$case['id']?>"><?php echo $case['debitorName'];?></a></div>
									<div class="gtable_cell"><?php echo $v_row['receiver'];?></div>
									<div class="gtable_cell"><?php echo $v_row['receiver_email'];?></div>
									<div class="gtable_cell"><?php echo (strlen($v_row['subject'])>17?substr(substr($v_row['subject'],0,17),0,strrpos(substr($v_row['subject'],0,17)," "))."...":$v_row['subject']);?> <a href="#" class="output-show-email-message" data-subject="<?php echo $v_row['subject'];?>" data-message="<?php echo $v_row['text'];?>"><span class="glyphicon glyphicon-info-sign"></span></a></div>
									<div class="gtable_cell"><?php echo date("d.m.Y H:i", strtotime($v_row['perform_time']));?></div>
									<div class="gtable_cell"><span class="glyphicon glyphicon-<?=$v_status_icon[$v_row['status']];?> <?=$v_status_text[$v_row['status']];?>" aria-hidden="true"></span></div>
								</div>
								<?php
								$l_counter++;
							}
							?></div><?php
						}
        				?>
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
 	$(".output-show-email-message").unbind("click").on('click', function(e){
	 	e.preventDefault();
	 	$('#popupeditboxcontent').html('<h3><?php echo $formText_EmailMessage_Output;?></h3><div><b><?php echo $formText_Subject_Output;?>:</b></div><div>' + $(this).data('subject') + '</div><div style="margin-top:10px;"><b><?php echo $formText_Message_Output;?>:</b></div><div>' + $(this).data('message') + '</div>');
		out_popup = $('#popupeditbox').bPopup(out_popup_options);
		$("#popupeditbox:not(.opened)").remove();
 	});
	$(".output-edit-process-step").unbind("click").on('click', function(e){
		e.preventDefault();
		var data = {
			processId: $(this).data('process-id'),
			processStepId: $(this).data('process-step-id')
		};
		ajaxCall('edit_process_step', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	});
	$(".output-edit-process-step-content").unbind("click").on('click', function(e){
		e.preventDefault();
		var data = {
			processId: $(this).data('process-id'),
			processStepId: $(this).data('process-step-id')
		};
		ajaxCall('edit_process_step_content', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	});
	$(".output-edit-process-step-action").unbind("click").on('click', function(e){
		e.preventDefault();
		var data = {
			processId: $(this).data('process-id'),
			processStepId: $(this).data('process-step-id')
		};
		ajaxCall('edit_process_step_action', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	});

	$(".output-delete-process-step").unbind("click").on('click', function(e){
		e.preventDefault();
		var self = $(this);
		var data = {
			processStepId: self.data('process-step-id'),
			processId: self.data('process-id'),
			action: "deleteProcess"
		};
		bootbox.confirm('<?php echo $formText_ConfirmDelete_output; ?>', function(result) {
			if (result) {
				ajaxCall('edit_process_step', data, function(json) {
					loadView("details", {cid:"<?php echo $cid;?>"});
				});
			}
		});
	});
	$(".output-edit-process-step-action").unbind("click").on('click', function(e){
		e.preventDefault();
		var data = {
			processId: $(this).data('process-id'),
			processStepId: $(this).data('process-step-id'),
			processStepActionId: $(this).data('process-step-action-id')
		};
		ajaxCall('edit_process_step_action', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	});
	$(".output-delete-process-step-action").unbind("click").on('click', function(e){
		e.preventDefault();
		var self = $(this);
		var data = {
			processStepId: self.data('process-step-id'),
			processId: self.data('process-id'),
			processStepActionId: $(this).data('process-step-action-id'),
			action: "deleteAction"
		};
		bootbox.confirm('<?php echo $formText_ConfirmDelete_output; ?>', function(result) {
			if (result) {
				ajaxCall('edit_process_step_action', data, function(json) {
					loadView("details", {cid:"<?php echo $cid;?>"});
				});
			}
		});
	});




   $(".caseStatusChange").on('change', function(e){
	   e.preventDefault();
	   var caseId  = $(this).data('case-id');
	   var data = {
		   caseId: caseId,
		   action:"statusChange",
		   status: $(this).val()
	   };
	   ajaxCall('edit_case', data, function(json) {
		   loadView("details", {cid:"<?php echo $cid;?>"});
	   });
   });

	$(".output-delete-comment").unbind("click").on('click', function(e){
		e.preventDefault();
		var self = $(this);
		var data = {
			cid: self.data('comment-id'),
			caseId: self.data('case-id'),
			output_delete: 1
		};
		bootbox.confirm('<?php echo $formText_ConfirmDelete_output; ?>', function(result) {
			if (result) {
				ajaxCall('edit_comment', data, function(json) {
					loadView("details", {cid:"<?php echo $cid;?>"});
				});
			}
		});
	});


	$(".output-edit-payment").unbind("click").on('click', function(e){
		e.preventDefault();
		var data = {
			caseId: $(this).data('case-id'),
			cid: $(this).data('comment-id')
		};
		ajaxCall('edit_payment', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	});
	$(".output-edit-payment-plan").unbind("click").on('click', function(e){
		e.preventDefault();
		var data = {
			caseId: $(this).data('case-id'),
			cid: $(this).data('paymentplan-id')
		};
		ajaxCall('edit_payment_plan', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	});
	$(".output-edit-claims").unbind("click").on('click', function(e){
		e.preventDefault();
		var data = {
			caseId: $(this).data('case-id'),
			cid: $(this).data('claim-id')
		};
		ajaxCall('edit_claims', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	});
	$(".output-delete-claims").unbind("click").on('click', function(e){
		e.preventDefault();
		var self = $(this);
		var data = {
			cid: self.data('claim-id'),
			caseId: self.data('case-id'),
			output_delete: 1
		};
		bootbox.confirm('<?php echo $formText_ConfirmDelete_output; ?>', function(result) {
			if (result) {
				ajaxCall('edit_claims', data, function(json) {
					loadView("details", {cid:"<?php echo $cid;?>"});
				});
			}
		});
	});


    $(".dropdown_content_show").unbind("click").bind("click", function(e){
        var parent = $(this);
        if($(e.target).hasClass("dropdown_content_show") || $(e.target).hasClass("showArrow") || $(e.target).parent().hasClass("showArrow")){
            var dropdown = parent.next(".p_contentBlock.dropdown_content");
            if(dropdown.is(":visible")) {
                dropdown.slideUp();
                parent.find(".showArrow .glyphicon").addClass("glyphicon-triangle-right");
                parent.find(".showArrow .glyphicon").removeClass("glyphicon-triangle-bottom");
            } else {
                if(parent.hasClass("autoload")) {
                    dropdown.slideDown(0);
                    parent.removeClass("autoload");
                } else {
                    dropdown.slideDown();
                }
                parent.find(".showArrow .glyphicon").removeClass("glyphicon-triangle-right");
                parent.find(".showArrow .glyphicon").addClass("glyphicon-triangle-bottom");
            }
        }
    })

	$(".output-edit-messages-creditor").unbind("click").on('click', function(e){
		e.preventDefault();
		var data = {
			caseId: $(this).data('case-id'),
			cid: $(this).data('message-id')
		};
		ajaxCall('edit_message_creditor', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	});

	$(".output-delete-messages-creditor").unbind("click").on('click', function(e){
		e.preventDefault();
		var self = $(this);
		var data = {
			cid: self.data('message-id'),
			caseId: self.data('case-id'),
			output_delete: 1
		};
		bootbox.confirm('<?php echo $formText_ConfirmDelete_output; ?>', function(result) {
			if (result) {
				ajaxCall('edit_message_creditor', data, function(json) {
					loadView("details", {cid:"<?php echo $cid;?>"});
				});
			}
		});
	});

	$(".output-edit-messages-debitor").unbind("click").on('click', function(e){
		e.preventDefault();
		var data = {
			caseId: $(this).data('case-id'),
			cid: $(this).data('message-id')
		};
		ajaxCall('edit_message_debitor', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	});

	$(".output-delete-messages-debitor").unbind("click").on('click', function(e){
		e.preventDefault();
		var self = $(this);
		var data = {
			cid: self.data('message-id'),
			caseId: self.data('case-id'),
			output_delete: 1
		};
		bootbox.confirm('<?php echo $formText_ConfirmDelete_output; ?>', function(result) {
			if (result) {
				ajaxCall('edit_message_debitor', data, function(json) {
					loadView("details", {cid:"<?php echo $cid;?>"});
				});
			}
		});
	});

	$(".output-edit-otherpart").unbind("click").on('click', function(e){
		e.preventDefault();
		var data = {
			caseId: $(this).data('case-id'),
			cid: $(this).data('otherpart-id')
		};
		ajaxCall('edit_other_part', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	});
	$(".output-delete-otherpart").unbind("click").on('click', function(e){
		e.preventDefault();
		var self = $(this);
		var data = {
			caseId: $(this).data('case-id'),
			cid: $(this).data('otherpart-id'),
			action: "delete"
		};
		bootbox.confirm('<?php echo $formText_ConfirmDelete_output; ?>', function(result) {
			if (result) {
				ajaxCall('edit_other_part', data, function(json) {
					loadView("details", {cid:"<?php echo $cid;?>"});
				});
			}
		});
	});
})
</script>
<style>
    .p_pageDetails {
        background: #fff;
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
		font-weight: normal !important;
	}
	.processStepDetails {

		border-bottom: 1px solid #efefef;
		margin-bottom: 0px;
	}
	.processStepDetails .txt-label {
		width: 20%;
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
