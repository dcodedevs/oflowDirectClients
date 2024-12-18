<?php
$caseId = $_POST['caseId'] ? $o_main->db->escape_str($_POST['caseId']) : 0;
$action = $_POST['action'] ? $_POST['action'] : '';
$status = $_POST['status'] ? $_POST['status'] : 0;
$canSendEmail = false;
$sql = "SELECT accountinfo_emailsender_accountconfig.* FROM accountinfo_emailsender_accountconfig ORDER BY id ASC";
$o_query = $o_main->db->query($sql);
$accountinfo_emailsender_accountconfig = $o_query ? $o_query->row_array() : array();

$people_contactperson_type = 2;
$sql = "SELECT * FROM accountinfo_basisconfig ORDER BY id";
$o_query = $o_main->db->query($sql);
$accountinfo_basisconfig = $o_query ? $o_query->row_array() : array();
if(intval($accountinfo_basisconfig['contactperson_type_to_use_in_people']) > 0){
	$people_contactperson_type = intval($accountinfo_basisconfig['contactperson_type_to_use_in_people']);
}

$o_query = $o_main->db->query("SELECT * FROM sys_emailserverconfig WHERE default_server = 1 AND name != '' AND host != ''");
$emailSettings = $o_query ? $o_query->row_array() : array();
if(count($emailSettings) > 0){
	if($accountinfo_emailsender_accountconfig['email'] != "" && $accountinfo_emailsender_accountconfig['name'] != ""){
		$canSendEmail = true;
	}
}
if($moduleAccesslevel > 10) {
	if(isset($_POST['output_form_submit'])) {
	    $sql = "SELECT * FROM case_crm WHERE id = $caseId";
		$o_query = $o_main->db->query($sql);
	    $caseData = $o_query ? $o_query->row_array() : array();
		$correctFields = true;

		if($_POST['subject'] == "" || intval($_POST['projectLeader']) == 0) {
			$correctFields = false;
		}
		if(!$caseData){
			if($_POST['description'] == ""){
				$correctFields = false;
			}
		}
		if($correctFields){
			$disableSending = $_POST['disable_sending'];

			    $sql = "SELECT * FROM case_crm WHERE id = $caseId";
				$o_query = $o_main->db->query($sql);
			    $caseData = $o_query ? $o_query->row_array() : array();

	        if ($caseData) {
	            $sql = "UPDATE case_crm SET
	            updated = now(),
	            updatedBy='".$variables->loggID."',
	            subject='".$o_main->db->escape_str($_POST['subject'])."',
				customer_id  = '".$o_main->db->escape_str($_POST['customerId'])."',
				responsible_person_id  = '".$o_main->db->escape_str($_POST['projectLeader'])."',
				customer_contactperson_email  = '".$o_main->db->escape_str($_POST['contactPerson'])."',
				case_access = '".$o_main->db->escape_str($_POST['case_access'])."',
				status = '".$o_main->db->escape_str($_POST['status'])."'
	            WHERE id = ".$caseData['id'];

				$insert_id = $caseData['id'];
				$o_query = $o_main->db->query($sql);
	            $fw_redirect_url = $_POST['redirect_url'];
	        } else {
	            $sql = "INSERT INTO case_crm SET
	            created = now(),
	            createdBy='".$variables->loggID."',
	            subject='".$o_main->db->escape_str($_POST['subject'])."',
				customer_id = '".$o_main->db->escape_str($_POST['customerId'])."',
				responsible_person_id = '".$o_main->db->escape_str($_POST['projectLeader'])."',
				customer_contactperson_email = '".$o_main->db->escape_str($_POST['contactPerson'])."',
				case_type = '".$o_main->db->escape_str($_POST['case_type'])."',
				case_access = '".$o_main->db->escape_str($_POST['case_access'])."',
				status = '".$o_main->db->escape_str($_POST['status'])."'";
				$o_query = $o_main->db->query($sql);
	            $insert_id = $o_main->db->insert_id();
				if($insert_id > 0) {
					$sql = "INSERT INTO case_crm_message SET
					created = now(),
					createdBy='".$variables->loggID."',
					case_id='".$o_main->db->escape_str($insert_id)."',
					message='".$o_main->db->escape_str($_POST['description'])."'";
					$o_query = $o_main->db->query($sql);

		            $case_crm_message_id = $o_main->db->insert_id();
					if($_POST['case_access'] == 1) {
						$company_name = $variables->companyname;
						$subject = $formText_NewCaseMessage_output;
						$emailOutput = $formText_ANewCaseMessageWasAdded_email;
						if($canSendEmail && !$disableSending) {
							include("fnc_sendTicketMessageEmail.php");
							sendTicketMessageEmail($emailSettings, $subject, $emailOutput,  $accountinfo_emailsender_accountconfig, $variables->loggID, $company_name, array($insert_id, $case_crm_message_id), $_POST['description'], $formText_Company_email, $formText_Case_email, $formText_User_email, $formText_Message_email);
						}
					}
					$fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$insert_id;

				}

         	}
			return;
		} else {
			$fw_return_data = array($formText_MissingFields_output);
		}
	}
}
if($action == "statusChange" && $caseId) {

    $sql = "SELECT * FROM case_crm WHERE id = $caseId";
	$o_query = $o_main->db->query($sql);
    $caseData = $o_query ? $o_query->row_array() : array();

	if($caseData['responsible_person_id'] > 0) {
		$approved = 0;
		if(intval($_POST['approved']) == 1){
			$o_query = $o_main->db->query("SELECT * FROM sys_emailserverconfig WHERE default_server = 1 AND name != '' AND host != ''");
			$emailSettings = $o_query ? $o_query->row_array() : array();

			$sql = "SELECT accountinfo_emailsender_accountconfig.* FROM accountinfo_emailsender_accountconfig ORDER BY id ASC";
			$o_query = $o_main->db->query($sql);
			$accountinfo_emailsender_accountconfig = $o_query ? $o_query->row_array() : array();
			$company_name = $variables->companyname;

			$sql = "INSERT INTO case_crm_message SET
			created = now(),
			createdBy='".$variables->loggID."',
			case_id='".$o_main->db->escape_str($_POST['caseId'])."',
			message='".$o_main->db->escape_str($_POST['comment'])."'";
			$o_query = $o_main->db->query($sql);

			if(!$_POST['disable_sending']){
				$subject = $formText_StatusChanged_output;
				$emailOutput = "";


				require_once("fnc_sendTicketMessageEmail.php");
				sendTicketMessageEmail($emailSettings, $subject, $emailOutput,  $accountinfo_emailsender_accountconfig, $variables->loggID, $company_name, $_POST['caseId'], $_POST['comment'], $formText_Company_email, $formText_Case_email, $formText_User_email, $formText_Message_email);
				$approved = 1;
			} else {
				$approved = 1;
			}
		}
		if($_POST['status'] > 0 && $approved == 0 && $caseData['case_access'] == 1) {
			$s_sql = "SELECT * FROM contactperson WHERE contactperson.email = ?";
			$o_query = $o_main->db->query($s_sql, array($caseData['customer_contactperson_email']));
			$contactPersonItem = ($o_query ? $o_query->row_array() : array());

			$s_sql = "SELECT * FROM contactperson  WHERE contactperson.id = ?";
			$o_query = $o_main->db->query($s_sql, array($caseData['responsible_person_id']));
			$ticketResponsible = ($o_query ? $o_query->row_array() : array());

			$fw_return_data = "message";
			$statusArray = array($formText_Unhandled_output, $formText_Finished_output, $formText_UnderWork_output);
			?>
			<div class="popupform">
				<form class="output-form" action="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=editCase";?>" method="post">
					<input type="hidden" name="fwajax" value="1">
					<input type="hidden" name="fw_nocss" value="1">
					<input type="hidden" name="caseId" value="<?php print $_POST['caseId'];?>">
					<input type="hidden" name="action" value="<?php print $_POST['action'];?>">
					<input type="hidden" name="status" value="<?php print $_POST['status'];?>">
					<input type="hidden" name="approved" value="1">
					<div class="inner">
						<div class="popupformTitle">
							<?php echo $formText_ChangingStatusFrom_Output." ".$statusArray[$caseData['status']]." ".$formText_To_output." ".$statusArray[$_POST['status']]; ?>
						</div>
						<div class="line">
							<div class="lineTitle"><?php echo $formText_Message_Output; ?></div>
							<div class="lineInput">
								<textarea class="popupforminput botspace" name="comment" required><?php
								if($_POST['status'] == 1) {
									echo $formText_CaseStatusWasChangedToFinished_Output;
								} else if($_POST['status'] == 2) {
									echo $formText_CaseStatusWasChangedToUnderWork_Output;
								}
								?></textarea>
							</div>
							<div class="clear"></div>
						</div>
						<div class="line">
							<div class="lineTitle"><?php echo $formText_DoNotSendEmailNotification_Output; ?></div>
							<div class="lineInput">
								<input type="checkbox" class="popupforminput checkbox disableSendingCheckbox" name="disable_sending"/>
								<span class="contactpersonSending">
									<?php
									echo $formText_MessagesWillBeSentTo_output.": <span class='personItemWrapper'>".$contactPersonItem['email']."</span>";?>
								</span>
							</div>
							<div class="clear"></div>
						</div>
						<div class="popupformbtn">
							<button type="button" class="output-btn b-large b-close"><?php echo $formText_Cancel_Output;?></button>
							<input type="submit" name="sbmbtn" value="<?php echo $formText_Save_Output; ?>">
						</div>
					</div>
				</form>
				<style>
				.contactpersonSending {
					margin-top: 10px;
				}
				</style>
				<script type="text/javascript" src="../modules/<?php echo $module?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
				<script type="text/javascript">
				$(function(){
					$("form.output-form").validate({
						submitHandler: function(form) {
							fw_loading_start();

							var formdata = $(form).serializeArray();
							var data = {};
							$(formdata ).each(function(index, obj){
								if(data[obj.name] != undefined) {
									if(Array.isArray(data[obj.name])){
										data[obj.name].push(obj.value);
									} else {
										data[obj.name] = [data[obj.name], obj.value];
									}
								} else {
									data[obj.name] = obj.value;
								}
							});

							$.ajax({
								url: $(form).attr("action"),
								cache: false,
								type: "POST",
								dataType: "json",
								data: data,
								success: function (data) {
									fw_loading_end();
									out_popup.addClass("close-reload");
									out_popup.close();
								}
							}).fail(function() {
								$("#popup-validate-message").html("<?php echo $formText_ErrorOccuredSavingContent_Output;?>", true);
								$("#popup-validate-message").show();
								$('#popupeditbox').css('height', $('#popupeditboxcontent').height());
								fw_loading_end();
							});
						},
						invalidHandler: function(event, validator) {
							var errors = validator.numberOfInvalids();
							if (errors) {
								var message = errors == 1
								? '<?php echo $formText_YouMissed_validate; ?> 1 <?php echo $formText_field_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>'
								: '<?php echo $formText_YouMissed_validate; ?> ' + errors + ' <?php echo $formText_fields_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>';

								$("#popup-validate-message").html(message);
								$("#popup-validate-message").show();
								$('#popupeditbox').css('height', $('#popupeditboxcontent').height());
							} else {
								$("#popup-validate-message").hide();
							}
							setTimeout(function(){ $('#popupeditbox').height(''); }, 200);
						}
					});
				})
				</script>
			</div>
			<?php
			return;
		} else {
			$confirmStatusChange = true;
		}
		if($confirmStatusChange){
			if($_POST['status']) {
				$completed_sql = ", completed_date = NOW()";
			} else {
				$completed_sql = ", completed_date = '0000-00-00'";
			}

		 	$sql = "UPDATE case_crm SET
		    updated = now(),
		    updatedBy='".$variables->loggID."',
		 	status = ".$_POST['status'].$completed_sql."
		    WHERE id = $caseId";
		    $o_query = $o_main->db->query($sql);
			return;
		}
	} else {
		$fw_return_data = "message";
		?>
		<div class="line">
			<?php echo $formText_CanNotChangeStatus_Output; ?>
			<div>
				<label><?php echo $formText_SelectResponsiblePerson_output?></label>
			</div>
		</div>
		<?php
		return;
	}
}

if($action == "deleteProject" && $caseId) {
    $sql = "DELETE FROM case_crm
    WHERE id = $caseId";
    $o_query = $o_main->db->query($sql);
}
if($caseId) {
    $sql = "SELECT * FROM case_crm WHERE id = $caseId";
	$o_query = $o_main->db->query($sql);
    $caseData = $o_query ? $o_query->row_array() : array();

    $s_sql = "SELECT * FROM customer  WHERE customer.id = ?";
    $o_query = $o_main->db->query($s_sql, array($caseData['customer_id']));
    $customer = ($o_query ? $o_query->row_array() : array());

    $s_sql = "SELECT * FROM contactperson  WHERE contactperson.id = ?";
    $o_query = $o_main->db->query($s_sql, array($caseData['responsible_person_id']));
    $employee = ($o_query ? $o_query->row_array() : array());

} else {
    $s_sql = "SELECT * FROM contactperson WHERE email = ? AND type = ?";
    $o_query = $o_main->db->query($s_sql, array($variables->loggID, $people_contactperson_type));
    if($o_query && $o_query->num_rows()>0){
        $currentEmployee = $o_query->row_array();
    }
    if(!$owner){
        $s_sql = "SELECT * FROM contactperson WHERE contactperson.id = ?";
        $o_query = $o_main->db->query($s_sql, array($currentEmployee['id']));
        $owner = ($o_query ? $o_query->row_array() : array());
    }
}
if(!$employee){
	if(!$caseData){
		$s_sql = "SELECT * FROM contactperson  WHERE contactperson.email = ? AND type = ?";
	    $o_query = $o_main->db->query($s_sql, array($variables->loggID, $people_contactperson_type));
	    $employee = ($o_query ? $o_query->row_array() : array());
	}
}
$s_sql = "select * from project_accountconfig";
$o_query = $o_main->db->query($s_sql);
$v_project_accountconfig= ($o_query ? $o_query->row_array() : array());
?>

<div class="popupform popupform-<?php echo $caseId;?>">
	<div id="popup-validate-message" style="display:none;"></div>
	<form class="output-form main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=editCase";?>" method="post">
		<input type="hidden" name="fwajax" value="1">
		<input type="hidden" name="fw_nocss" value="1">
		<input type="hidden" name="output_form_submit" value="1">
		<input type="hidden" name="caseId" value="<?php echo $caseId;?>">
        <input type="hidden" name="redirect_url" value="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$caseId; ?>">
		<div class="popupformTitle"><?php
		if($caseId> 0){
			echo $formText_EditCase_output;
		} else {
			echo $formText_AddCase_output;
		}
		?></div>
		<div class="inner">
			<div class="line ">
        		<div class="lineTitle"><?php echo $formText_CaseStatus_Output; ?></div>
        		<div class="lineInput ">
                    <select name="status">
                        <option value="0" <?php if($caseData['status'] == 0) echo 'selected';?>><?php echo $formText_Unhandled_output;?></option>
                        <option value="2" <?php if($caseData['status'] == 2) echo 'selected';?>><?php echo $formText_UnderWork_output;?></option>
                        <option value="1" <?php if($caseData['status'] == 1) echo 'selected';?>><?php echo $formText_Finished_output;?></option>
                    </select>
                </div>
        		<div class="clear"></div>
    		</div>
    		<div class="line ">
        		<div class="lineTitle"><?php echo $formText_CaseAccess_Output; ?></div>
        		<div class="lineInput ">
                    <select name="case_access" class="caseAccessChanger">
                        <option value="0" <?php if($caseData['case_access'] == 0) echo 'selected';?>><?php echo $formText_CasesWillOnlyBeVisibleInternalForEmployees_output;?></option>
                        <option value="1" <?php if($caseData['case_access'] == 1) echo 'selected';?>><?php echo $formText_MessagesWillBeSendAndVisibleForCustomerInCustomerPortal_output;?></option>
                        <option value="2" <?php if($caseData['case_access'] == 2) echo 'selected';?>><?php echo $formText_CasesWillOnlyBeVisibleForResponsiblePerson_output;?></option>
                    </select>
                </div>
        		<div class="clear"></div>
    		</div>
            <div class="line customerInputWrapper">
                <div class="lineTitle"><?php echo $formText_Customer_Output; ?></div>
                <div class="lineInput">
                    <?php if($customer) { ?>
                    <a href="#" class="selectCustomer"><?php echo $customer['name']?></a>
                    <?php } else { ?>
                    <a href="#" class="selectCustomer"><?php echo $formText_SelectCustomer_Output;?></a>
                    <?php } ?>
                    <input type="hidden" name="customerId" id="customerId" value="<?php print $customer['id'];?>" <?php if($caseData['case_access'] == 1) echo 'required';?>>
					<span class="reset_customer"><?php echo $formText_ResetCustomer_output;?></span>
                </div>
                <div class="clear"></div>
            </div>
    		<div class="line contactpersonWrapper">
        		<div class="lineTitle"><?php echo $formText_ContactPerson_Output; ?></div>
        		<div class="lineInput contactPersonSelectWrapper">
					<?php
					if($customer){
	                    $resources = array();

	                    $s_sql = "SELECT * FROM contactperson WHERE content_status < 2 AND customerId =  ? ORDER BY name ASC";
	                    $o_query = $o_main->db->query($s_sql, array($customer['id']));
	                    if($o_query && $o_query->num_rows()>0) {
	                        $resources = $o_query->result_array();
	                    }
	                    ?>
	                    <select name="contactPerson" class="contactPersonChanger">
	                        <option value=""><?php echo $formText_None_output;?></option>
	                        <?php foreach($resources as $resource) {
								if($resource['email'] != ""){
								?>
	                        		<option data-email="<?php echo $resource['email']?>" value="<?php echo $resource['email']?>" <?php if($caseData['customer_contactperson_email'] == $resource['email']) echo 'selected';?>><?php echo $resource['name']." ".$resource['middlename']." ".$resource['lastname']?></option>
	                        	<?php
								}
							} ?>
	                    </select>
					<?php } ?>
                </div>
        		<div class="clear"></div>
    		</div>

            <div class="line">
                <div class="lineTitle"><?php echo $formText_ResponsiblePerson_output; ?></div>
                <div class="lineInput">
                    <?php if($employee) { ?>
                    <a href="#" class="selectEmployee"><?php echo $employee['name']." ".$employee['middlename']." ".$employee['lastname']?></a>
                    <?php } else { ?>
                    <a href="#" class="selectEmployee"><?php echo $formText_SelectResponsiblePerson_Output;?></a>
                    <?php } ?>
                    <input type="hidden" name="projectLeader" id="projectLeader" value="<?php print $employee['id'];?>" required>
					<span class="reset_responsible_person"><?php echo $formText_ResetResponsiblePerson_output;?></span>
                </div>
                <div class="clear"></div>
            </div>

    		<div class="line">
        		<div class="lineTitle"><?php echo $formText_Subject_Output; ?></div>
        		<div class="lineInput">
                    <input type="text" class="popupforminput botspace" name="subject" value="<?php echo $caseData['subject']; ?>" autocomplete="off" required>
                </div>
        		<div class="clear"></div>
    		</div>
			<?php if(!$caseData){?>
	            <div class="line">
	                <div class="lineTitle"><?php echo $formText_Description_Output; ?></div>
	                <div class="lineInput">
	                    <textarea name="description" required class="popupforminput botspace"><?php echo $caseData['description']; ?></textarea>
	                </div>
	                <div class="clear"></div>
	            </div>
			<?php } ?>
			<div class="line emailSendingWrapper">
				<div class="lineTitle"><?php echo $formText_DoNotSendEmailNotification_Output; ?></div>
				<div class="lineInput">
					<input type="checkbox" class="popupforminput checkbox disableSendingCheckbox" name="disable_sending"/>
					<span class="contactpersonSending">
						<?php
						$contactPersonText = $contactPersonItem['email'];
						if($contactPersonText == ""){
							$contactPersonText = '<span style="color: red;">&nbsp;&nbsp;'.$formText_NoContactPersonChosenOnThisCase_output.'</span>';
						}
						echo $formText_MessagesWillBeSentTo_output.": <span class='personItemWrapper'>".$contactPersonText."</span>";

						if($contactPersonItem['email'] != ""){
							if(!function_exists("APIconnectAccount")) include_once(__DIR__."/../../input/includes/APIconnect.php");

							$s_sql = "select * from customer_stdmembersystem_basisconfig";
							$o_query = $o_main->db->query($s_sql);
							if($o_query && $o_query->num_rows()>0){
								$v_membersystem_config = $o_query->row_array();
							}
							$s_sql = "select * from accountinfo";
							$o_query = $o_main->db->query($s_sql);
							if($o_query && $o_query->num_rows()>0){
								$v_accountinfo = $o_query->row_array();
							}

							$hasAccess = false;
							$v_item = array();
							unset($o_membersystem);
							if($contactPersonItem['email']!="")
							{
								$o_membersystem = json_decode(APIconnectAccount("membersystemcompanyaccessusernameget", $v_accountinfo['accountname'], $v_accountinfo['password'], array("COMPANY_ID"=>$companyID, "USER"=>$contactPersonItem["email"], "MEMBERSYSTEMID"=>$caseData['customer_id'], "ACCESSLEVEL"=>$v_membersystem_config['access_level'], "MODULE"=>$module)));
							}

							$l_membersystem_id = $contactPersonItem[$v_membersystem_config['content_id_field']];
							$imgToDisplay = "";
							$member = $o_membersystem->data;

							if($member)
							{
								$response = json_decode(APIconnectorUser("companyaccessbycompanyidget", $variables->loggID, $variables->sessionID, array('COMPANY_ID'=>$companyID)));
								$v_membersystem = array();
								foreach($response->data as $writeContent)
								{
									if($contactPersonItem['email'] == $writeContent->username){
										$hasAccess = true;
										break;
									}
								}
							}
							if(!$hasAccess){
								echo '<div style="color: red;">'.$formText_ContactPersonDoesNotHaveAccess_output.' - '.$contactPersonItem['email'].'</div>';
							}
						}
						?>
					</span>
				</div>
				<div class="clear"></div>
			</div>
		</div>

		<div class="popupformbtn">
			<button type="button" class="output-btn b-large b-close"><?php echo $formText_Cancel_Output;?></button>
			<input type="submit" name="sbmbtn" value="<?php echo $formText_Save_Output; ?>">
		</div>
	</form>
</div>
<script type="text/javascript" src="../modules/<?php echo $module;?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
<script type="text/javascript">

$(document).ready(function() {
    $(".popupform-<?php echo $caseId;?> form.output-form").validate({
        ignore: [],
        submitHandler: function(form) {
            fw_loading_start();
            $.ajax({
                url: $(form).attr("action"),
                cache: false,
                type: "POST",
                dataType: "json",
                data: $(form).serialize(),
                success: function (data) {
                    fw_loading_end();
                    if(data.redirect_url !== undefined)
                    {
                        out_popup.addClass("close-reload").data("redirect", data.redirect_url);
                        out_popup.close();
                    }
                }
            }).fail(function() {
                $(".popupform-<?php echo $caseId;?> #popup-validate-message").html("<?php echo $formText_ErrorOccuredSavingContent_Output;?>", true);
                $(".popupform-<?php echo $caseId;?> #popup-validate-message").show();
                $('.popupform-<?php echo $caseId;?> #popupeditbox').css('height', $('.popupform-<?php echo $caseId;?> #popupeditboxcontent').height());
                fw_loading_end();
            });
        },
        invalidHandler: function(event, validator) {
            var errors = validator.numberOfInvalids();
            if (errors) {
                var message = errors == 1
                ? '<?php echo $formText_YouMissed_validate; ?> 1 <?php echo $formText_field_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>'
                : '<?php echo $formText_YouMissed_validate; ?> ' + errors + ' <?php echo $formText_fields_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>';

                $(".popupform-<?php echo $caseId;?> #popup-validate-message").html(message);
                $(".popupform-<?php echo $caseId;?> #popup-validate-message").show();
                $('.popupform-<?php echo $caseId;?> #popupeditbox').css('height', $('#popupeditboxcontent').height());
            } else {
                $(".popupform-<?php echo $caseId;?> #popup-validate-message").hide();
            }
            setTimeout(function(){ $('#popupeditbox').height(''); }, 200);
        },
        errorPlacement: function(error, element) {
            if(element.attr("name") == "customerId") {
                error.insertAfter(".popupform-<?php echo $caseId;?> .selectCustomer");
            }
            if(element.attr("name") == "projectLeader") {
                error.insertAfter(".popupform-<?php echo $caseId;?> .selectEmployee");
            }
            if(element.attr("name") == "projectOwner") {
                error.insertAfter(".popupform-<?php echo $caseId;?> .selectOwner");
            }
            if(element.attr("name") == "invoiceResponsible") {
                error.insertAfter(".popupform-<?php echo $caseId;?> .invoiceResponsible");
            }
        },
        messages: {
            customerId: "<?php echo $formText_SelectTheCustomer_output;?>",
            projectLeader: "<?php echo $formText_SelectResponsiblePerson_output;?>",
            invoiceResponsible: "<?php echo $formText_SelectInvoiceResponsible_output;?>"
        }
    });

    $(".popupform-<?php echo $caseId;?> .selectCustomer").unbind("click").bind("click", function(e){
		e.preventDefault();
        fw_loading_start();
        var _data = { fwajax: 1, fw_nocss: 1};
        $.ajax({
            cache: false,
            type: 'POST',
            dataType: 'json',
            url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=get_customers";?>',
            data: _data,
            success: function(obj){
                fw_loading_end();
                $('#popupeditboxcontent2').html('');
                $('#popupeditboxcontent2').html(obj.html);
                out_popup2 = $('#popupeditbox2').bPopup(out_popup_options);
                $("#popupeditbox2:not(.opened)").remove();
            }
        });
    })
    $(".popupform-<?php echo $caseId;?> .selectEmployee").unbind("click").bind("click", function(e){
		e.preventDefault();
        fw_loading_start();
        var _data = { fwajax: 1, fw_nocss: 1, owner: 0};
        $.ajax({
            cache: false,
            type: 'POST',
            dataType: 'json',
            url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=get_employees";?>',
            data: _data,
            success: function(obj){
                fw_loading_end();
                $('#popupeditboxcontent2').html('');
                $('#popupeditboxcontent2').html(obj.html);
                out_popup2 = $('#popupeditbox2').bPopup(out_popup_options);
                $("#popupeditbox2:not(.opened)").remove();
            }
        });
    })
    $(".popupform-<?php echo $caseId;?> .selectInvoiceResponsible").unbind("click").bind("click", function(e){
		e.preventDefault();
        fw_loading_start();
        var _data = { fwajax: 1, fw_nocss: 1, invoiceresponsible: 1};
        $.ajax({
            cache: false,
            type: 'POST',
            dataType: 'json',
            url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=get_employees";?>',
            data: _data,
            success: function(obj){
                fw_loading_end();
                $('#popupeditboxcontent2').html('');
                $('#popupeditboxcontent2').html(obj.html);
                out_popup2 = $('#popupeditbox2').bPopup(out_popup_options);
                $("#popupeditbox2:not(.opened)").remove();
            }
        });
    })


    $(".popupform-<?php echo $caseId;?> .selectOwner").unbind("click").bind("click", function(e){
		e.preventDefault();
        fw_loading_start();
        var _data = { fwajax: 1, fw_nocss: 1, owner: 1};
        $.ajax({
            cache: false,
            type: 'POST',
            dataType: 'json',
            url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=get_employees";?>',
            data: _data,
            success: function(obj){
                fw_loading_end();
                $('#popupeditboxcontent2').html('');
                $('#popupeditboxcontent2').html(obj.html);
                out_popup2 = $('#popupeditbox2').bPopup(out_popup_options);
                $("#popupeditbox2:not(.opened)").remove();
            }
        });
    })
    $(".projectTypeSelect").change(function(){
    	if($(this).val() == 2 || $(this).val() == 3 || $(this).val() == 8) {
    		$(".customerInputWrapper").show();
			$("#customerId").prop("required", true);
    	} else {
    		$(".customerInputWrapper").hide();
			$("#customerId").prop("required", false);
    		$(".selectCustomer").html('<?php echo $formText_SelectCustomer_Output;?>');
    		$("#customerId").val("");
    	}
		if($(this).val() == 3) {
			$(".invoiceResponsibleWrapper").show();
			<?php if($v_project_accountconfig['activateInvoiceResponsible'] == 2) { ?>
				$(".invoiceResponsibleWrapper #invoiceResponsible").prop("required", true);
			<?php } else { ?>
				$(".invoiceResponsibleWrapper #invoiceResponsible").prop("required", false);
			<?php } ?>
		} else {
			$(".invoiceResponsibleWrapper").hide();
			$(".invoiceResponsibleWrapper #invoiceResponsible").prop("required", false);
		}
		if($(this).val() == 4){
			$(".contactpersonWrapper").hide();
		} else {
			$(".contactpersonWrapper").show();
		}
		var value = $(this).val();

		fw_loading_start();
        var _data = { fwajax: 1, fw_nocss: 1, project_category_type: value, project_category_id: '<?php echo $caseData['project_category']?>'};
        $.ajax({
            cache: false,
            type: 'POST',
            dataType: 'json',
            url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=get_categories";?>',
            data: _data,
            success: function(obj){
                fw_loading_end();
				$(".categoryWrapper .lineInput").html(obj.html)
				if(obj.html != ""){
					$(".categoryWrapper").show();
				} else {
					$(".categoryWrapper").hide();

				}
            }
        });
    })
    $(".projectTypeSelect").change();
	$(".projectTypeSubSelect").change(function(){
		if($(this).val() == 7) {
			$(".projectOwnerData").show();
			$(".projectOwnerData #projectOwner").prop("required", true);
			$(".outsideProjectTaskWrapper").hide();
		} else {
			$(".outsideProjectTaskWrapper").show();
			if(!$(".toBeApprovedCheckbox").is(":checked")){
				$(".projectOwnerData").hide();
				$(".projectOwnerData #projectOwner").prop("required", false);
			} else {
				$(".projectOwnerData").show();
				$(".projectOwnerData #projectOwner").prop("required", true);
			}
		}
	}).change();
	$(".toBeApprovedCheckbox").off("click").on("click", function(){
		updateProjectOwner();
	})
	$(".projectStatusSelect").change(function(){
		if($(this).val() == 3) {
			$(".giveBriefingWrapper").show();
		} else {
			$(".giveBriefingWrapper").hide();
		}
	}).change();


	function updateProjectOwner() {
		if($(".toBeApprovedCheckbox").is(":checked")){
			$(".projectOwnerData").show();
			$(".projectOwnerData #projectOwner").prop("required", true);
		} else {
			if($(".projectTypeSubSelect").val() == 7) {
				$(".projectOwnerData").show();
				$(".projectOwnerData #projectOwner").prop("required", true);
			} else {
				$(".projectOwnerData").hide();
				$(".projectOwnerData #projectOwner").prop("required", false);
			}
		}
	}
	$(".resetInvoiceResponsible").on("click", function(){
		$("#invoiceResponsible").val("");
		$(".selectInvoiceResponsible").html("<?php echo $formText_SelectInvoiceResponsible_Output;?>");
	})
	$(".estimatedTimeuseType").change(function(){
		var type = $(this).val();
		var estimatedTimeuseHours = $(".estimatedTimeuseHours").val().replace(",", ".");
		if(type == 0){
			estimatedTimeuseHoursNew = estimatedTimeuseHours/60;
			$(".estimatedTimeuseHours").val(estimatedTimeuseHoursNew.toFixed(2).toString().replace(".", ","));
		} else if(type == 1){
			estimatedTimeuseHoursNew = estimatedTimeuseHours*60;
			$(".estimatedTimeuseHours").val(estimatedTimeuseHoursNew.toFixed(2).toString().replace(".", ","));
		}
	})
	$(".reset_responsible_person").off("click").on("click", function(){
		$(".selectEmployee").html("<?php echo $formText_SelectResponsiblePerson_output;?>");
		$("#projectLeader").val("");
	})
	$(".reset_customer").off("click").on("click", function(){
		$(".selectCustomer").html("<?php echo $formText_SelectCustomer_output;?>");
		$(".contactPersonSelectWrapper").html("");
		$("#customerId").val("");
	})
	$(".caseAccessChanger").change(function(){
		if($(this).val() == 1){
			$(".emailSendingWrapper").show();
			$("#customerId").prop("required", true);
		} else {
			$(".emailSendingWrapper").hide();
			$("#customerId").prop("required", false);
		}
	})
	$(".contactPersonChanger").change(function(){
		var selectedOption = $(".contactPersonChanger option:selected").data("email");
		if(selectedOption == "" || selectedOption == undefined){
			$(".personItemWrapper").html('<span style="color: red;">&nbsp;&nbsp;<?php echo $formText_NoContactPersonChosenOnThisCase_output; ?></span>');
		} else {
			$(".personItemWrapper").html(selectedOption);
		}
	})

});

</script>
<style>
.emailSendingWrapper {
	display: none;
}
.reset_customer {
	cursor: pointer;
}
.reset_responsible_person {
	cursor: pointer;
}
.rightWrapper {
	position: absolute;
	top: 0;
	right: 0px;
	width: 30%;
}
.popupeditbox .popupform .rightWrapper .line .lineTitle {
	width: calc(100% - 40px);
}
.popupeditbox .popupform .rightWrapper .line .lineInput {
	width: 40px;
}
.projectOwnerData {
	display: none;
}
.categoryWrapper {
	display: none;
}
.resetInvoiceResponsible {
	margin-left: 20px;
}
.lineInput .otherInput {
    margin-top: 10px;
}
.lineInput input[type="radio"]{
    margin-right: 10px;
    vertical-align: middle;
}
.lineInput input[type="radio"] + label {
    margin-right: 10px;
    vertical-align: middle;
}
.popupform .inlineInput input.popupforminput {
    display: inline-block;
    width: auto;
    vertical-align: middle;
    margin-right: 20px;
}
.popupform .inlineInput select {
    display: inline-block;
    width: auto;
    vertical-align: middle;
	margin-bottom: 10px;
}
.popupform .inlineInput label {
    display: inline-block !important;
    vertical-align: middle;
}
.popupform .lineInput.lineWhole {
	font-size: 14px;
}
.popupform .lineInput.lineWhole label {
	font-weight: normal !important;
}
.selectDivModified {
    display:block;
}
.popupform, .popupeditform {
	width:100%;
	margin:0 auto;
	position:relative;
}
.invoiceEmail {
    display: none;
}
label.error {
    color: #c11;
    margin-left: 10px;
    border: 0;
    display: inline !important;
}
.popupform .popupforminput.error { border-color:#c11 !important;}
#popup-validate-message, .error-msg { font-weight:bold; color:#c11; padding-bottom:10px; }
/* css for timepicker */
.ui-timepicker-div .ui-widget-header { margin-bottom: 8px; }
.ui-timepicker-div dl { text-align: left; }
.ui-timepicker-div dl dt { height: 25px; margin-bottom: -25px; }
.ui-timepicker-div dl dd { margin: 0 10px 10px 65px; }
.ui-timepicker-div td { font-size: 90%; }
.ui-tpicker-grid-label { background: none; border: none; margin: 0; padding: 0; }
.clear {
	clear:both;
}
.inner {
	padding:10px;
}
.pplineV {
	position:absolute;
	top:0;bottom:0;left:70%;
	border-left:1px solid #e8e8e8;
}
.popupform input.popupforminput, .popupform textarea.popupforminput, .popupform select.popupforminput, .col-md-8z input {
	width:100%;
	border-radius: 4px;
	padding:5px 10px;
	font-size:12px;
	line-height:17px;
	color:#3c3c3f;
	background-color:transparent;
	-webkit-box-sizing: border-box;
	   -moz-box-sizing: border-box;
		 -o-box-sizing: border-box;
			box-sizing: border-box;
	font-weight:400;
	border: 1px solid #cccccc;
}
.popupformname {
	font-size:12px;
	font-weight:bold;
	padding:5px 0px;
}
.popupforminput.botspace {
	margin-bottom:10px;
}
textarea {
	min-height:50px;
	max-width:100%;
	min-width:100%;
	width:100%;
}
.popupformname {
	font-weight: 700;
	font-size: 13px;
}
.popupformbtn {
	text-align:right;
	margin:10px;
}
.popupformbtn input {
	border-radius:4px;
	border:1px solid #0393ff;
	background-color:#0393ff;
	font-size:13px;
	line-height:0px;
	padding: 20px 35px;
	font-weight:700;
	color:#FFF;
	margin-left:10px;
}
.error {
	border: 1px solid #c11;
}
.popupform .lineTitle {
	font-weight:700;
}
.popupform .line .lineTitle {
	width:30%;
	float:left;
	font-weight:700;
	padding:5px 0;
}

.popupform .line .lineTitleWithSeperator {
    width:100%;
    margin: 20px 0;
    padding:0 0 10px;
    border-bottom:1px solid #EEE;
}

.popupform .line .lineInput {
	width:70%;
	float:left;
}
.addSubProject {
    margin-bottom: 10px;
}
</style>
