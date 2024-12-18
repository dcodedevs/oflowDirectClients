<?php
// List btn
require_once __DIR__ . '/list_btn.php';
if(!function_exists("APIconnectAccount")) include_once(__DIR__."/../../input/includes/APIconnect.php");

$sql = "select * from accountinfo";
$o_query = $o_main->db->query($sql);
$v_accountinfo = $o_query ? $o_query->row_array() : array();

$cid = $o_main->db->escape_str($_GET['cid']);

$sql = "SELECT * FROM collecting_cases_collecting_process WHERE id = $cid";
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
						<button class="output-btn small output-edit-process editBtnIcon" data-process-id="<?php echo $cid; ?>"><span class="glyphicon glyphicon-pencil"></span></button>

						<?php if($moduleAccesslevel > 10) { ?><button class="addEntryBtn small output-edit-process-step" data-process-id="<?php echo $cid; ?>"><?php echo $formText_AddStep_output;?></button><?php } ?>
					</div>
					<div class="clear"></div>
				</div>
				<?php

				$sql = "SELECT * FROM collecting_cases_collecting_process_steps WHERE collecting_cases_collecting_process_id = ? ORDER BY sortnr ASC";
				$o_query = $o_main->db->query($sql, array($process['id']));
				$process_steps = $o_query ? $o_query->result_array() : array();
				if(count($process_steps)> 0) {
				?>
				<div class="p_pageDetails">
					<div class="">
						<div class="p_contentBlock dropdown_content noTopPadding">
							<?php

							foreach($process_steps as $process_step) {

						        $s_sql = "SELECT * FROM collecting_cases_main_status_basisconfig WHERE id = ? ";
						        $o_query = $o_main->db->query($s_sql, array($process_step['status_id']));
						        $main_status = ($o_query ? $o_query->row_array() : array());

						        $s_sql = "SELECT * FROM collecting_cases_sub_status_basisconfig WHERE id = ? ";
						        $o_query = $o_main->db->query($s_sql, array($process_step['sub_status_id']));
						        $sub_status = ($o_query ? $o_query->row_array() : array());
								?>
								<div class="processStepBlock">
									<div class="processStepDetails">
										<table class="mainTable" width="100%" border="0" cellpadding="0" cellspacing="0">
								        	<tr>
								                <td class="txt-label"><?php echo $formText_Name_output;?></td>
								                <td class="txt-value">
								                	<?php echo $process_step['name'];?>
								                </td>
								            </tr>
											<tr>
								                <td class="txt-label"><?php echo $formText_DaysAfterDueDate_Output;?></td>
								                <td class="txt-value">
								                	<?php echo $process_step['days_after_due_date'];?>
								                </td>
								            </tr>
											<tr>
												<td class="txt-label"><?php echo $formText_BankAccount_output;?></td>
												<td class="txt-value">
													<?php
													switch(intval($process_step['bank_account_choice'])) {
														case 0:
															echo $formText_OwnCreditorBankAccount_output;
														break;
														case 1:
															echo $formText_CollectingCompanyBankAccount_output;
														break;
													}
													?>
												</td>
											</tr>

											<tr>
												<td class="txt-label"><?php echo $formText_LetterText_output;?></td>
												<td class="txt-value">
													<?php
													$s_sql = "SELECT * FROM collecting_cases_pdftext WHERE id = ? AND content_status < 2 ORDER BY sortnr ASC";
													$o_query = $o_main->db->query($s_sql, array(intval($process_step['collecting_cases_pdftext_id'])));
													$pdfText = $o_query ? $o_query->row_array() : array();

													echo $pdfText['name'];

													?>
												</td>
											</tr>
											<?php /*?>
											<tr>
												<td class="txt-label"><?php echo $formText_EmailText_output;?></td>
												<td class="txt-value">
													<?php
													$s_sql = "SELECT * FROM collecting_cases_emailtext WHERE id = ? AND content_status < 2 ORDER BY sortnr ASC";
													$o_query = $o_main->db->query($s_sql, array(intval($process_step['collecting_cases_emailtext_id'])));
													$pdfText = $o_query ? $o_query->row_array() : array();

													echo $pdfText['subject'];

													?>
												</td>
											</tr>*/?>
											<tr>
												<td class="txt-label"><?php echo $formText_WarningLevel_output;?></td>
												<td class="txt-value">
													<?php
														if($process_step['warning_level']) {
															echo $formText_Yes_output;
														} else {
															echo $formText_No_output;
														}
													?>
												</td>
											</tr>

											<tr>
												<td class="txt-label"><?php echo $formText_LateFee_Output;?></td>
												<td class="txt-value">
													<?php
													switch(intval($process_step['claim_type_2_article'])) {
														case 0:
															echo $formText_No_output;
														break;
													}
													if(intval($process_step['claim_type_2_article']) == 1){
														echo $formText_Yes_Output." x1";
														// $s_sql = "SELECT * FROM debtcollectionlatefee WHERE id = ? AND content_status < 2 ORDER BY sortnr ASC";
														// $o_query = $o_main->db->query($s_sql, array(intval($process_step['claim_type_2_article'])));
														// $article = $o_query ? $o_query->row_array() : array();
														//
														// echo $article['internal_name'];
													} else if(intval($process_step['claim_type_2_article']) == 2){
														echo $formText_Yes_Output." x2";
													}
													?>
												</td>
											</tr>
											<?php if(intval($process_step['claim_type_2_article']) > 0){ ?>
												<tr>
													<td class="txt-label"><?php echo $formText_DefaultFeeLevel_Output;?></td>
													<td class="txt-value">
														<?php
														echo $process_step['default_fee_level'];
														?>
													</td>
												</tr>
											<?php } ?>
											<tr>
												<td class="txt-label"><?php echo $formText_DebtCollectionFeeArticle_output;?></td>
												<td class="txt-value">
													<?php
													switch(intval($process_step['claim_type_3_article'])) {
														case 0:
															echo $formText_No_output;
														break;
														case 1:
															echo $formText_LightFee_output;
														break;
														case 2:
															echo $formText_HeavyFee_output;
														break;
													}
													?>
												</td>
											</tr>
											<tr>
												<td class="txt-label"><?php echo $formText_Action_output;?></td>
												<td class="txt-value">
													<?php
													switch(intval($process_step['sending_action'])) {
														case 0:
															echo $formText_None_output;
														break;
														case 1:
															echo $formText_SendLetter_output;
														break;
														case 2:
															echo $formText_SendEmailIfEmailExistsOrElseLetter_output;
														break;
														case 6:
															echo $formText_SendEmailAndLetter_output;
														break;
													}
													?>
												</td>
											</tr>
											<tr>
												<td class="txt-label"><?php echo $formText_DaysToDueDate_output;?></td>
												<td class="txt-value">
													<?php
													echo $process_step['add_number_of_days_to_due_date'];
													?>
												</td>
											</tr>
											<tr>
												<td class="txt-label"><?php echo $formText_CollectingCompanyLetterType_output;?></td>
												<td class="txt-value">
													<?php
													$s_sql = "SELECT * FROM collecting_company_letter_types WHERE id = ? AND content_status < 2 ORDER BY name ASC";
													$o_query = $o_main->db->query($s_sql, array($process_step['collecting_company_letter_type_id']));
													$collecting_company_letter_type = $o_query ? $o_query->row_array() : array();

													echo $collecting_company_letter_type['name'];
													?>
												</td>
											</tr>
								        </table>
								        <table class="mainTable btn-edit-table" width="100%" border="0" cellpadding="0" cellspacing="0">
								            <tr>
								                <td class="txt-label"></td>
								                <td class="txt-value"></td>
								                <td class="btn-edit" colspan="2">
													<?php if($moduleAccesslevel > 10) { ?>
														<button class="output-btn small output-edit-process-step editBtnIcon" data-process-id="<?php echo $cid; ?>" data-process-step-id="<?php echo $process_step['id']; ?>"><span class="glyphicon glyphicon-pencil"></span></button>
														<button class="output-btn small output-delete-process-step editBtnIcon" data-process-id="<?php echo $cid; ?>" data-process-step-id="<?php echo $process_step['id']; ?>"><span class="glyphicon glyphicon-trash"></span></button>

													<?php } ?></td>
								            </tr>
								        </table>
									</div>
								</div>
								<?php
							}


							$s_sql = "SELECT * FROM collecting_cases_notesandfiles WHERE content_status < 2 AND collecting_case_id = ? ORDER BY created DESC";
							$o_query = $o_main->db->query($s_sql, array($caseData['id']));
							$comments = ($o_query ? $o_query->result_array() : array());
							foreach($comments as $comment) {
								?>
								<div class="commentBlock">
									<table class="table table-borderless">
										<tr>
											<td width="90%" class="createdLabel"><?php echo date("d.m.Y H:i", strtotime($comment['created']));?> | <?php echo $comment['createdBy'];?></td>
											<td width="10%" style="text-align: right;">
												<?php if($moduleAccesslevel > 10) { ?><button class="output-btn small output-edit-comment editBtnIcon" data-case-id="<?php echo $cid; ?>" data-comment-id="<?php echo $comment['id'];?>"><span class="glyphicon glyphicon-pencil"></span></button><?php } ?>
												<?php if($moduleAccesslevel > 110) { ?>
						    					<button class="output-btn small output-delete-comment editBtnIcon" data-case-id="<?php echo $cid; ?>" data-comment-id="<?php echo $comment['id'];?>">
							    					<span class="glyphicon glyphicon-trash"></span>
						    					<?php } ?>
											</td>
										</tr>
										<tr>
											<td colspan="2">
												<?php echo nl2br($comment['text']);?>
												<?php
												$files = json_decode($comment['files']);
												foreach($files as $file) {
													$fileParts = explode('/',$file[1][0]);
													$fileName = array_pop($fileParts);
													$fileParts[] = rawurlencode($fileName);
													$filePath = implode('/',$fileParts);
													$fileUrl = $extradomaindirroot."/../".$file[1][0];
													$fileName = $file[0];
													if(strpos($file[1][0],'uploads/protected/')!==false)
													{
														$fileUrl = $extradomaindirroot."/../".$filePath.'?caID='.$_GET['caID'].'&table=collecting_cases_notesandfiles&field=files&ID='.$comment['id'];
													}
												?>
													<div class="project-file">
														<div class="project-file-file">
															<a href="<?php echo $fileUrl;?>" download><?php echo $fileName;?></a>
														</div>
													</div>
													<?php
												}
												?>
											</td>
										</tr>
									</table>
								</div>
								<?php
							}
							?>
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
		$(this).find("#popupeditboxcontent").html("");
		$(this).removeClass('opened');
		if($(this).is('.close-reload')) {
			loadView("details", {cid:"<?php echo $cid;?>"});
		}
	}
};
$(function(){
	// $(".generatePdf").off("click").on("click", function(e) {
	// 	e.preventDefault();
	// 	var data = {
	// 		caseId: '<?php echo $cid;?>',
	// 	};
	// 	ajaxCall('generatePdf', data, function(json) {
	// 		if(json.data != undefined) {
	// 			var a = document.createElement('a');
	// 			a.href =  '<?php echo $extradomaindirroot."/uploads/";?>'+json.data;
	// 			a.setAttribute('target', '_blank');
	// 			a.click();
	// 		}
	// 	});
	// })



 	$(".output-edit-process").unbind("click").on('click', function(e){
	 	e.preventDefault();
	 	var data = {
	 		processId: $(this).data('process-id')
	 	};
	 	ajaxCall('edit_process', data, function(json) {
	 		$('#popupeditboxcontent').html('');
	 		$('#popupeditboxcontent').html(json.html);
	 		out_popup = $('#popupeditbox').bPopup(out_popup_options);
	 		$("#popupeditbox:not(.opened)").remove();
	 	});
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
	$(".output-edit-process-step-sending").unbind("click").on('click', function(e){
		e.preventDefault();
		var data = {
			processId: $(this).data('process-id'),
			processStepId: $(this).data('process-step-id')
		};
		ajaxCall('edit_process_step_sending', data, function(json) {
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
