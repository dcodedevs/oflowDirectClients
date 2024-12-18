<?php
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$per_page = isset($_GET['per_page']) ? $_GET['per_page'] : 50;

$offset = ($page-1)*$per_page;
if($offset < 0){
	$offset = 0;
}
$amountFromFilter = isset($_GET['amountFrom']) ? str_replace(",",".",$_GET['amountFrom']): '';
$amountToFilter = isset($_GET['amountTo']) ? str_replace(",",".",$_GET['amountTo']): '';
$dateFromFilter = isset($_GET['dateFrom']) ? $_GET['dateFrom']: '';
$dateToFilter = isset($_GET['dateTo']) ? $_GET['dateTo']: '';

$limit_sql = " LIMIT ".$per_page ." OFFSET ".$offset;
$subscriptiontype_filter = intval($_GET['subscriptiontype_filter']);

$s_sql = "SELECT * FROM subscriptiontype WHERE id = ? ORDER BY name";
$o_query = $o_main->db->query($s_sql, array($subscriptiontype_filter));
$subscriptionType = $o_query ? $o_query->row_array() : array();

$s_sql = "SELECT * FROM subscriptiontype_subtype WHERE subscriptiontype_id = ? ORDER BY name";
$o_query = $o_main->db->query($s_sql, array($subscriptiontype_filter));
$subscriptionSubTypes = $o_query ? $o_query->result_array() : array();

$list_filter = isset($_GET['list_filter']) ? $_GET['list_filter'] : 'absence';

$sql = "SELECT * FROM collecting_cases_claim_letter WHERE content_status < 2";
$o_query = $o_main->db->query($sql);
$all_count = $o_query ? $o_query->num_rows() : 0;

$sql = "SELECT * FROM collecting_cases_claim_letter WHERE content_status < 2 AND (sending_status = 1)";
$o_query = $o_main->db->query($sql);
$processed_count = $o_query ? $o_query->num_rows() : 0;

$sql = "SELECT * FROM collecting_cases_claim_letter WHERE content_status < 2 AND (sending_status is null OR sending_status = 0)";
$o_query = $o_main->db->query($sql);
$not_processed_count = $o_query ? $o_query->num_rows() : 0;

$sql = "SELECT * FROM collecting_cases_claim_letter WHERE content_status < 2 AND sending_status = 2";
$o_query = $o_main->db->query($sql);
$failed_count = $o_query ? $o_query->num_rows() : 0;

$sql = "SELECT * FROM collecting_cases_claim_letter WHERE content_status < 2 AND sending_status = 3";
$o_query = $o_main->db->query($sql);
$canceled_count = $o_query ? $o_query->num_rows() : 0;

$sql = "SELECT * FROM collecting_cases_claim_letter WHERE content_status < 2 AND sending_status < 0";
$o_query = $o_main->db->query($sql);
$under_process_count = $o_query ? $o_query->num_rows() : 0;

$sql = "SELECT * FROM collecting_cases_claim_letter WHERE content_status < 2 AND sending_status = 4";
$o_query = $o_main->db->query($sql);
$for_download_count = $o_query ? $o_query->num_rows() : 0;

$sql = "SELECT * FROM collecting_cases_claim_letter WHERE content_status < 2 AND sending_status = 5";
$o_query = $o_main->db->query($sql);
$demo_count = $o_query ? $o_query->num_rows() : 0;
?>
<?php
include(__DIR__."/list_filter.php");

$sql_where = "";
if($list_filter == "not_processed"){
    $sql_where .= " AND (sending_status is null OR sending_status = 0)";
} else if($list_filter == "processed"){
    $sql_where .= " AND sending_status = 1";
} else if($list_filter == "failed"){
    $sql_where .= " AND sending_status = 2";
} else if($list_filter == "canceled") {
	$sql_where .= " AND sending_status = 3";
} else if($list_filter == "under_process") {
	$sql_where .= " AND sending_status < 0";
} else if($list_filter == "for_download") {
	$sql_where .= " AND sending_status = 4";
} else if($list_filter == "demo") {
	$sql_where .= " AND sending_status = 5";
}
if($amountFromFilter != "") {
	if($amountToFilter != "") {
		$sql_where .= " AND ccl.total_amount >= '".$amountFromFilter."' AND ccl.total_amount <= '".$amountToFilter."'";
	} else {
		$sql_where .= " AND ccl.total_amount = '".$amountFromFilter."'";
	}
} else {
	if($amountToFilter != "") {
		$sql_where .= " AND ccl.total_amount <= '".$amountToFilter."'";
	}
}
if($dateFromFilter != "") {
	$sql_where .= " AND ccl.created >= '".date("Y-m-d", strtotime($dateFromFilter))."'";
	if($dateToFilter != ""){
		$sql_where .= " AND ccl.created <= '".date("Y-m-d", strtotime($dateToFilter))."'";
	}
}
if($actiontype_filter > 0){
	$sql_where .= " AND ccl.sending_action = '".$o_main->db->escape_str($actiontype_filter)."'";
}

$sql = "SELECT ccl.*, c.invoiceEmail, c.phone, IF(ccs.id is null, concat_ws(' ', c.name, c.middlename, c.lastname), concat_ws(' ', c_cc.name, c_cc.middlename, c_cc.lastname)) as debitorName,
IF(ccs.id is null, cred.companyname, concat_ws(' ', c_cc2.name, c_cc2.middlename, c_cc2.lastname)) as creditorName,
IFNULL(c.extra_language, c_cc.extra_language) as debitorLanguage, c_cc.extra_invoice_email  FROM collecting_cases_claim_letter ccl
LEFT OUTER JOIN collecting_cases cs ON cs.id = ccl.case_id
LEFT OUTER JOIN customer c ON c.id = cs.debitor_id
LEFT OUTER JOIN creditor cred ON cred.id = cs.creditor_id
LEFT OUTER JOIN collecting_company_cases ccs ON ccs.id = ccl.collecting_company_case_id
LEFT OUTER JOIN customer c_cc ON c_cc.id = ccs.debitor_id
LEFT OUTER JOIN creditor cred_cc ON cred_cc.id = ccs.creditor_id
LEFT OUTER JOIN customer c_cc2 ON c_cc2.id = cred_cc.customer_id
WHERE ccl.content_status < 2".$sql_where." ORDER BY ccl.created DESC";
$o_query = $o_main->db->query($sql);
$nolimit_count = $o_query ? $o_query->num_rows() : 0;
$o_query = $o_main->db->query($sql.$limit_sql."");
$letters = $o_query ? $o_query->result_array() : array();

$totalPages = ceil($nolimit_count/$per_page);

$action_text = array(0=>$formText_SendLetter_output, 1=>$formText_SendEmail_output, 2=>$formText_SelfPrint_output, 3=>$formText_ManualPdfDownload_output, 4=>$formText_SendSms_output);

$sending_action_text = array(1=>$formText_SendLetter_output, 2=>$formText_SendEmail_output, 3=>$formText_SelfPrint_output, 4=>$formText_SendSms_output, 5=>$formText_SendEhf_output, 6=>$formText_SendEmailAndLetter_output);
$action_text_icons = array(1=>'<i class="fas fa-file"></i>', 2=>'<i class="fas fa-at"></i>');
$status_array = array(-2=>$formText_MarkedToBeProcessed_output, -1=>$formText_Processing_output, 0 => $formText_NotProcessed_output, 1=> $formText_Completed_output, 2=>$formText_Failed_output, 3=>$formText_Canceled_output, 4=>$formText_ForDownload_output, 5=>$formText_DemoNotForSending_output);
?>
<div id="p_container" class="p_container">
	<div class="p_containerInner">
		<div class="p_content">
			<?php if($list_filter == "all") { ?>
				<div class="filter_row">
					<span class="filter_label"><?php echo $formText_Amount_output; ?></span> <input type="text" class="amountFrom popupforminput botspace" name="amountFrom" value="<?php echo number_format($amountFromFilter, 2,",","")?>"/> - <input type="text" class="amountTo popupforminput botspace" name="amountTo" value="<?php echo $amountToFilter?>"/><br/>
					<span class="filter_label"><?php echo $formText_Date_output;?></span> <input type="text" class="dateFrom datepicker popupforminput botspace" name="dateFrom" value="<?php echo number_format($dateFromFilter, 2,",","")?>" /> - <input type="text" class="dateTo datepicker popupforminput botspace" name="dateTo" value="<?php echo $dateToFilter?>"/>

					<span class="filter_button"><?php echo $formText_Filter_output;?></span>
					<span class="filtered_span"><?php echo $formText_FilteredCount_output." ".$nolimit_count;?></span>
				</div>
			<?php } ?>
			<div class="p_pageContent">
                <div class="p_pageDetails">
                    <div class="gtable" >
                        <div class="gtable_row">
                            <div class="gtable_cell gtable_cell_head" style="width: 30px;"><input type="checkbox" class="selectAll" autocomplete="off"/></div>
                            <div class="gtable_cell gtable_cell_head"><?php echo $formText_Created_output;?></div>
                            <div class="gtable_cell gtable_cell_head"><?php echo $formText_Claim_output;?></div>
                            <div class="gtable_cell gtable_cell_head"><?php echo $formText_Customer_output;?><br/><?php echo $formText_Creditor_output;?></div>
                            <div class="gtable_cell gtable_cell_head" style="width: 60px;"><?php echo $formText_CollectingCaseId_output;?></div>
                            <div class="gtable_cell gtable_cell_head"><?php echo $formText_SendingAction_output;?></div>
                            <div class="gtable_cell gtable_cell_head"><?php echo $formText_SendingStatus_output;?></div>
                            <div class="gtable_cell gtable_cell_head"><?php echo $formText_PerformedAction_output;?></div>
                            <div class="gtable_cell gtable_cell_head"><?php echo $formText_PerformedDate_output;?></div>
                            <div class="gtable_cell gtable_cell_head"><?php echo $formText_Letter_output;?></div>
                        </div>
                        <?php
                        foreach($letters as $letter) {
							$currencyName = "";
							if($letter['case_id'] > 0){
								$s_sql = "SELECT * FROM collecting_cases WHERE id = ?";
								$o_query = $o_main->db->query($s_sql, array($letter['case_id']));
								$case = $o_query ? $o_query->row_array() : array();
							} else {
								$s_sql = "SELECT * FROM collecting_company_cases WHERE id = ?";
								$o_query = $o_main->db->query($s_sql, array($letter['collecting_company_case_id']));
								$case = $o_query ? $o_query->row_array() : array();
							}
							if($case){
								$s_sql = "SELECT creditor.* FROM creditor WHERE creditor.id = ?";
								$o_query = $o_main->db->query($s_sql, array($case['creditor_id']));
								$creditor = ($o_query ? $o_query->row_array() : array());

								if($letter['case_id'] > 0){
									$s_sql = "SELECT * FROM creditor_transactions WHERE collectingcase_id = ?";
									$o_query = $o_main->db->query($s_sql, array($case['id']));
									$creditor_invoice = $o_query ? $o_query->row_array() : array();

									if($creditor_invoice['currency'] == 'LOCAL') {
										$currencyName = trim($creditor['default_currency']);
									} else {
										$currencyName = trim($creditor_invoice['currency']);
									}
								}
							}
                            ?>
                            <div class="gtable_row">
                                <div class="gtable_cell">
                                    <?php
									$selectable_checkbox = false;
									if(strpos(mb_strtolower($letter['creditorName']), "(demo)") === false){
										if($letter['sending_action'] > 0){
											if($letter['pdf'] != "" && file_exists(__DIR__."/../../../../".$letter['pdf'])) {
												if($letter['total_amount'] > 0) {
													if($letter['due_date'] == "0000-00-00" || $letter['due_date'] == "" || $letter['due_date'] == "1970-01-01") {

													} else {
														if(($letter['performed_date'] == null OR $letter['performed_date'] == "0000-00-00 00:00:00") && $letter['sending_status'] != 5) {
															$selectable_checkbox = true;
														 }
													}
												}
											}
										}
									}
									if($list_filter == "failed"){
										$selectable_checkbox = true;
									}
									if($selectable_checkbox){
										?>
										<input type="checkbox" class="checkboxesGenerate" name="casesToGenerate" autocomplete="off" value="<?php echo $letter['id'];?>" />
										<?php
									}
									?>
                                </div>
                                <div class="gtable_cell"><?php echo date("d.m.Y", strtotime($letter['created']))."<br/>";
								if($letter['due_date'] == "0000-00-00" || $letter['due_date'] == "" || $letter['due_date'] == "1970-01-01") { echo '<span style="color:red;">'.$formText_DueDateMissing_output.'</span>'; } else { echo date("d.m.Y", strtotime($letter['due_date']));}?></div>
                                <div class="gtable_cell"><span <?php if($letter['total_amount'] <= 100) echo 'style="color:red;"';?>><?php echo number_format($letter['total_amount'], 2, ",", " ")." ".$currencyName;?></span></div>
                                <div class="gtable_cell"><?php echo $letter['debitorName'];?>
									<?php if($letter['debitorLanguage'] == 1) { ?>
										<span class="otherLanguage"><?php echo $formText_English_Output;?></span>
									<?php } ?>
									<br/><?php echo $letter['creditorName'];?>								
								</div>
                                <div class="gtable_cell"><?php
								if($letter['case_id'] > 0) {
									$s_edit_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=CollectingCases&folderfile=output&folder=output&inc_obj=details&cid=".$letter['case_id'];

									echo "<a href='".$s_edit_link."' target='_blank'>".$letter['case_id']."</a>";
								} else {
									$s_edit_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=CollectingCompanyCases&folderfile=output&folder=output&inc_obj=details&cid=".$letter['collecting_company_case_id'];

									echo "<a href='".$s_edit_link."' target='_blank'>".$letter['collecting_company_case_id']."</a>";
								}
								?></div>
                                <div class="gtable_cell"><?php
									if($letter['performed_date'] == null OR $letter['performed_date'] == "0000-00-00 00:00:00") {
										?>
										<select autocomplete="off" class="send_action send_action<?php echo $letter['id'];?>" data-letter-id="<?php echo $letter['id'];?>" name="send_action">
											<option value=""><?php echo $formText_Select_output;?></option>
											<?php foreach($sending_action_text as $index=>$sending_action) {
												$show = true;
												if($index == 1){
													$show = false;
												}
												if($creditor['print_reminders'] == 1) {
													$show = true;
												}
												if($show){
													?>
													<option value="<?php echo $index;?>" <?php if($index == $letter['sending_action']) echo 'selected';?>><?php echo $sending_action;?></option>
												<?php }
												}

											 ?>
										</select><?php
										if($letter['sending_action'] == 2 && (preg_replace('/\xc2\xa0/', '', trim($letter['invoiceEmail']))!= "" || preg_replace('/\xc2\xa0/', '', trim($letter['extra_invoice_email']))!= "")){
											if($letter['collecting_company_case_id'] > 0) {
												echo "<div class='send_action_email'>".preg_replace('/\xc2\xa0/', '', trim($letter['extra_invoice_email']))."</div>";
											} else {
												echo "<div class='send_action_email'>".preg_replace('/\xc2\xa0/', '', trim($letter['invoiceEmail']))."</div>";
											}
										}


										if($letter['sending_action'] == 4 && $letter['phone']!= ""){
											echo "<div class='send_action_email'>".$letter['phone']."</div>
											<div class='hoverEye'>".$formText_ShowSmsText_output;
												echo '<div class="hoverInfo hoverInfoSmall">';
												if($creditor['sms_sendername'] !=""){
													$s_sender = preg_replace('#[^A-za-z0-9]+#', '', $creditor['sms_sendername']);
												} else {
													$s_sender = preg_replace('#[^A-za-z0-9]+#', '', $creditor['companyname']);
												}
												echo '<b>'.$formText_Sender_output.':</b> '.$s_sender."<br/>";

												$s_sql = "SELECT * FROM collecting_cases_process_steps WHERE id = ?";
												$o_query = $o_main->db->query($s_sql, array($letter['step_id']));
												$process_step = ($o_query ? $o_query->row_array() : array());

												$s_sql = "SELECT * FROM creditor_reminder_custom_profile_values WHERE collecting_cases_process_step_id = ? AND creditor_reminder_custom_profile_id = ?";
												$o_query = $o_main->db->query($s_sql, array($process_step['id'], $case['reminder_profile_id']));
												$step_profile_value = ($o_query ? $o_query->row_array() : array());
												$extra_sms_text = "";
												if($step_profile_value){
													$extra_sms_text = $step_profile_value['extra_text_in_sms'];
												}
												$totalAmount = $letter['total_amount'];
												$invoiceNumber = $creditor_invoice['invoice_nr'];
												$bankaccount_nr = $creditor['bank_account'];
												$kidNumber = $creditor_invoice['kid_number'];
												$company_name = $creditor['companyname'];
												$smsMessage = "Hei ".$letter['debitorName']."! Har du glemt oss? ".$extra_sms_text." Det gjenstår kr ".number_format($totalAmount, 2, ",", "")." å betale på faktura ".$invoiceNumber.". Vennligst betal til kontonr ".$bankaccount_nr." med KID ".$kidNumber." omgående. Om ikke kan ekstra kostnader påløpe. Mvh ".$company_name;

												echo '<b>'.$formText_Text_output.':</b> '.$smsMessage."<br/>";
												echo "</div>";
											echo "</div>";
										}
										?>
										<?php
									} else {
										if($letter['sending_action'] == 2) {
											echo '<span class="hoverEye">';
		                                    echo $sending_action_text[$letter['sending_action']];
											if($letter['collecting_company_case_id'] > 0) {
												echo '<div class="hoverInfo hoverInfoSmall">';
												echo preg_replace('/\xc2\xa0/', '', trim($letter['extra_invoice_email']));
												echo '</div>';
											} else {
												echo '<div class="hoverInfo hoverInfoSmall">';
												echo preg_replace('/\xc2\xa0/', '', trim($letter['invoiceEmail']));
												echo '</div>';
											}
											echo '</span>';
										} else {
											if($creditor['print_reminders'] == 0) {
												$letter['sending_action'] = 3;
											}
		                                    echo $sending_action_text[$letter['sending_action']];
										}
									}
								?></div>
                                <div class="gtable_cell">
									<select class="change_sending_status" data-letterid="<?php echo $letter['id'];?>">
										<?php foreach($status_array as $key=>$status) { ?>
											<option value="<?php echo $key;?>" <?php if($letter['sending_status'] == $key) echo 'selected';?>><?php echo $status;?></option>
										<?php } ?>
									</select>
									<?php
									if($letter['sending_status'] == 2) {
										echo substr($letter['sending_error_log'], 0, 50);
										if(strlen($letter['sending_error_log']) > 50) {
											echo '<span class="show_full_error"> ...<span class="full_error">'.$letter['sending_error_log'].'</span></span>';
										}
									}
									?>
								</div>
                                <div class="gtable_cell"><?php
									if($letter['performed_action'] == 1) {
										echo '<span class="hoverEye">';
										echo $action_text[$letter['performed_action']];										
										if($letter['collecting_company_case_id'] > 0) {
											echo '<div class="hoverInfo hoverInfoSmall">';
											echo preg_replace('/\xc2\xa0/', '', trim($letter['extra_invoice_email']));
											echo '</div>';
										} else {
											echo '<div class="hoverInfo hoverInfoSmall">';
											echo preg_replace('/\xc2\xa0/', '', trim($letter['invoiceEmail']));
											echo '</div>';
										}
										echo '</span>';
									} else {
										echo $action_text[$letter['performed_action']];
									}
								?></div>
                                <div class="gtable_cell"><?php if($letter['performed_date'] != "0000-00-00"){ echo $letter['performed_date'];};?></div>
                                <div class="gtable_cell">
                                    <?php if($letter['pdf'] != "") {
											$fileParts = explode('/',$letter['pdf']);
											$fileName = array_pop($fileParts);
											$fileParts[] = rawurlencode($fileName);
											$filePath = implode('/',$fileParts);
											$fileUrl = $extradomaindirroot.$letter['pdf'];
											$fileName = basename($letter['pdf']);
											if(strpos($letter['pdf'],'uploads/protected/')!==false)
											{
												$fileUrl = $extradomaindirroot."/../".$filePath.'?caID='.$_GET['caID'].'&table=collecting_cases_claim_letter&field=pdf&ID='.$letter['id'];
											}
											if(file_exists(__DIR__."/../../../../".$letter['pdf'])) {
												?>
		                                        <div class="project-file">
		                                            <div class="project-file-file">
		                                                <a href="<?php echo $fileUrl;?>" target="_blank"><?php if($letter['rest_note']){ echo $formText_RestNote_output; } else { echo $letter['step_name'];} echo " ".$formText_Download_Output;?></a>
		                                            </div>
		                                        </div>
											<?php } else {
												echo $formText_MissingPdfFile_output;
											} ?>
                                    <?php } ?>
                                    <?php if($variables->developeraccess >=20) {?>
                                        <div class="generatePdf" data-letterid="<?php echo $letter['id'];?>"><?php echo $formText_GeneratePdf_output;?></div>
                                    <?php } ?>
                                </div>
                            </div>
                            <?php
                        }

                        ?>
                    </div>
					<div class="paging">
						<?php if($totalPages > 1) {
							for($x = 1; $x <= $totalPages; $x++) {
							?>
								<div class="page <?php if($x == $page) echo 'active';?>" data-page="<?php echo $x;?>"><?php echo $x;?></div>
							<?php } ?>
						<?php } ?>
					</div>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
.p_pageDetails {
    background: #fff;
}
.edit_workplanlineworker {
    cursor: pointer;
    color: #46b2e2;
}
.gtable_cell_head {
    font-weight: bold;
}
.gtable {
    margin-top: 0px;
}
.generatePdf {
    cursor: pointer;
    color: #46b2e2;
}
.hoverEye {
	position: relative;
	color: #0284C9;
	margin-top: 2px;
}
.hoverEye .hoverInfo {
	font-family: 'PT Sans', sans-serif;
	width: 200px;
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
.hoverEye.hover .hoverInfo {
	display: block;
}
.cancelSending {
	margin-top: 10px;
    cursor: pointer;
    color: #46b2e2;

}
.send_action_email {
	width:100px;
	word-break: break-all;
}
.change_sending_status {
	width: 120px;
}
.paging {
	padding: 10px 0px;
}
.paging .page {
	display: inline-block;
	vertical-align: middle;
	padding: 5px;
	cursor: pointer;
	color: #46b2e2;
}
.paging .page.active {
	font-weight: bold;
	text-decoration: underline;
}
.filter_row {
	margin-bottom: 10px;
}
.filter_row .filter_label {
	display: inline-block;
	width: 70px;
	vertical-align: middle;
}
.filter_row .popupforminput {
	padding: 5px;
	border: 1px solid #cecece;
	border-radius: 5px;
}
.filter_row .botspace {
	margin-bottom: 5px;
}
.filter_row .filter_button {
	color: #fff;
	background: #0497e5;
	border: 1px solid #0497e5;
	cursor: pointer;
	padding: 5px 10px;
	border-radius: 5px;
	margin-left: 10px;
}
.filtered_span {
	margin-left: 15px;
}
.otherLanguage {
	margin-left: 10px;
	color: red;
}
.show_full_error {
	cursor: pointer;
	color: #46b2e2;
}
.full_error {
	display: none;
}
</style>
<script type="text/javascript">

$(function() {
	$(".datepicker").datepicker({
		firstDay: 1,
		dateFormat: "dd.mm.yy"
	})
	$(".paging .page").off("click").on("click", function(){
		var page = $(this).data("page");
		var data = {
			list_filter: '<?php echo $list_filter;?>',
			actiontype_filter: '<?php echo $actiontype_filter;?>',
			page: page
		};
		loadView('list', data);
	})
    $(".hoverEye").hover(
        function(){$(this).addClass("hover");},
        function(){
            var item = $(this);
            setTimeout(function(){
                if(item.is(":hover")){

                } else {
                    item.removeClass("hover");
                }
            }, 300)
        }
    )
    $(".selectAll").on("click", function(){
        if($(this).is(":checked")){
            $(".checkboxesGenerate").prop("checked", true);
        } else {
            $(".checkboxesGenerate").prop("checked", false);
        }
        $(".selected_letters").html($(".checkboxesGenerate:checked").length);
    });
    $(".checkboxesGenerate").off("click").on("click", function(){
        // $(".launchPdfGenerate .selected_letters").html($(".checkboxesGenerate:checked").length);
        // $(".downloadPdfs .selected_letters").html($(".checkboxesGenerate:checked").length);
        // $(".markForDownload .selected_letters").html($(".checkboxesGenerate:checked").length);
        // $(".markAsSent .selected_letters").html($(".checkboxesGenerate:checked").length);

		
        $(".selected_letters").html($(".checkboxesGenerate:checked").length);
    })
	$(".change_sending_status").on("change", function(e){
		e.preventDefault();
		var data = {
			letter_id: $(this).data("letterid"),
			status: $(this).val()
		}
		bootbox.confirm('<?php echo $formText_ChangeSendingStatus_output; ?>', function(result) {
            if (result) {
		        ajaxCall("change_sending_status", data, function(json) {
		            var data = {
		                list_filter: '<?php echo $list_filter;?>',
						actiontype_filter: '<?php echo $actiontype_filter;?>',
		            };
		            loadView('list', data);
		        });
			}
		})
	})
	$(".send_action").on("change", function(e){
		e.preventDefault();
		var data = {
			letter_id: $(this).data("letter-id"),
			action: $(this).val()
		}
        ajaxCall("change_sending_action", data, function(json) {
            var data = {
                list_filter: '<?php echo $list_filter;?>',
				actiontype_filter: '<?php echo $actiontype_filter;?>'
            };
            loadView('list', data);
        });
	})
	
    $(".change_all_sending_action").on("click", function(e){
        e.preventDefault();
		if($(".changeSendingTypeAll").val() != "") {
			bootbox.confirm('<?php echo $formText_ChangeSendingActionOnAllSelected_output; ?>', function(result) {
				if (result) {
					var casesToGenerate = [];
					$(".checkboxesGenerate").each(function(index, el){
						if($(el).is(":checked")){
							casesToGenerate.push($(el).val());
						}
					})
					var data = {
						casesToGenerate: casesToGenerate,
						status_action: $(".changeSendingTypeAll").val()
					}
					ajaxCall('change_sending_action_all', data, function(json) {;
						var data = {
							list_filter: '<?php echo $list_filter;?>',
							actiontype_filter: '<?php echo $actiontype_filter;?>'
						};
						loadView('list', data);
					});
				}
			});
		}
    })
	$(".change_all_sending_status").on("click", function(e){
		console.log(1);
        e.preventDefault();
		if($(".changeSendingStatusAll").val() != "") {
			bootbox.confirm('<?php echo $formText_ChangeSendingStatusOnAllSelected_output; ?>', function(result) {
				if (result) {
					var casesToGenerate = [];
					$(".checkboxesGenerate").each(function(index, el){
						if($(el).is(":checked")){
							casesToGenerate.push($(el).val());
						}
					})
					var data = {
						casesToGenerate: casesToGenerate,
						status_action: $(".changeSendingStatusAll").val()
					}
					ajaxCall('change_sending_status_all', data, function(json) {;
						var data = {
							list_filter: '<?php echo $list_filter;?>',
							actiontype_filter: '<?php echo $actiontype_filter;?>'
						};
						loadView('list', data);
					});
				}
			});
		}
    })
    $(".launchPdfGenerate").on("click", function(e){
        e.preventDefault();
        bootbox.confirm('<?php echo $formText_ProcessActions_output; ?>', function(result) {
            if (result) {
                var casesToGenerate = [];
                $(".checkboxesGenerate").each(function(index, el){
                    if($(el).is(":checked")){
                        casesToGenerate.push($(el).val());
                    }
                })

                var data = {
                    casesToGenerate: casesToGenerate
                }
                ajaxCall('process_letters', data, function(json) {
                    // var win = window.open('<?php echo $extradomaindirroot.'/modules/CollectingCases/output/ajax.download.php?ID=';?>' + json.data.batch_id, '_blank');
                    // win.focus();
                    var data = {
                        list_filter: '<?php echo $list_filter;?>',
						actiontype_filter: '<?php echo $actiontype_filter;?>'
                    };
                    loadView('list', data);
                });
            }
        });
    })
    $(document).off('click', '.output-click-helper').on('click', '.output-click-helper', function(e){
        if(e.target.nodeName == 'td'){
            <?php if($b_selection_mode && $totalPagesFiltered == 1) { ?>
            $(this).closest('.gtable_row').find('.selection-switch-btn').trigger('click');
            <?php } else { ?>
            fwAbortXhrPool();
            fw_load_ajax($(this).data('href'),'',true);
            if($("body.alternative").length == 0) {
                if($(this).parents(".tinyScrollbar.col1")){
                    var $scrollbar6 = $('.tinyScrollbar.col1');
                    $scrollbar6.tinyscrollbar();

                    var scrollbar6 = $scrollbar6.data("plugin_tinyscrollbar");
                    scrollbar6.update(0);
                }
            }
            <?php } ?>
        }
    });
    $(".edit_workplanlineworker").off("click").on("click", function(e){
        e.preventDefault();
        var data = {
            workplanlineworkerId: $(this).data("id"),
            repeatingOrderId: $(this).data("repeatingorderid"),
            projectId: $(this).data("projectid")
        }
        ajaxCall({module_file:'edit_workplanlineworker', module_name: 'EmployeePlanOverview', module_folder: 'output'}, data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    })
	$(".show_full_error").off("click").on("click", function(e){
		$('#popupeditboxcontent').html('');
		$('#popupeditboxcontent').html($(this).find(".full_error").html());
		out_popup = $('#popupeditbox').bPopup(out_popup_options);
		$("#popupeditbox:not(.opened)").remove();
	})
    $(".generatePdf").off("click").on("click", function(e){
        e.preventDefault();
        var data = {
            letter_id: $(this).data("letterid")
        }
        ajaxCall("generatePdfOnly", data, function(json) {
            var data = {
                list_filter: '<?php echo $list_filter;?>',
				actiontype_filter: '<?php echo $actiontype_filter;?>'
            };
            loadView('list', data);
        });
    })
	$(".filter_button").off("click").on("click", function(){
		var data = {
			list_filter: '<?php echo $list_filter;?>',
			actiontype_filter: '<?php echo $actiontype_filter;?>',
			amountFrom: $(".amountFrom").val(),
			amountTo: $(".amountTo").val(),
			dateFrom: $(".dateFrom").val(),
			dateTo: $(".dateTo").val()
		};
		loadView('list', data);
	})
})
</script>
