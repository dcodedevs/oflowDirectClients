<?php
$o_query = $o_main->db->query("SELECT * FROM integration_signant_basisconfig ORDER BY id DESC");
$v_signant_basisconfig = $o_query ? $o_query->row_array() : array();

$o_query = $o_main->db->query("SELECT * FROM integration_signant_accountconfig ORDER BY id DESC");
if($o_query && $o_query->num_rows() > 0)
{
	$v_signant_accountconfig = $o_query->row_array();
	if(2 != $v_signant_accountconfig['set_open'])
	{
		$v_signant_basisconfig['set_open'] = $v_signant_accountconfig['set_open'];
	}
}

if(1 != $v_signant_basisconfig['set_open'])
{
	?><div class="well well-lg"><?php echo $formText_ThisModuleCanGiveYouPossibilityToSendFilesToOthersForDigitalSigning_Output.'. '.$formText_ItWillGiveAnAdditionalCostToUseThisModule_Output.'. '.$formText_PleaseContactGetynetForAnOfferAndAgreement_Output.'.';?></div><?php
	return;
}

if(1 != $accessElementAllow_SendFilesToSignant)
{
	echo $formText_YouDontHaveAccess_Output;
	return;
}

$v_signers = array();
if(isset($_POST['cid']) && $_POST['cid'] > 0)
{
	$s_sql = "SELECT * FROM subscriptionmulti_files WHERE id = '".$o_main->db->escape_str($_POST['cid'])."'";
	$o_query = $o_main->db->query($s_sql);
	$v_files = $o_query ? $o_query->row_array() : array();
	
	$v_file = json_decode($v_files['file'], TRUE);
	$s_filename = $v_file[0][1][0];
	$v_files['filename'] = $v_file[0][0];
	
	$s_sql = "SELECT cp.* FROM contactperson AS cp JOIN subscriptionmulti AS sm ON sm.customerId = cp.customerId WHERE sm.id = '".$o_main->db->escape_str($v_files['subscriptionmulti_id'])."' ORDER BY mainContact DESC";
	$o_query = $o_main->db->query($s_sql);
	if($o_query && $o_query->num_rows()>0)
	foreach($o_query->result_array() as $v_people)
	{
		$nameToDisplay = preg_replace('/\s+/', ' ', $v_people['name'].' '.$v_people['middlename'].' '.$v_people['lastname']);
		$phoneToDisplay = $v_people['mobile'];
		$emailToDisplay = $v_people['email'];
		$s_response = APIconnectorUser("companyaccessget", $_COOKIE['username'], $_COOKIE['sessionID'], array('COMPANY_ID'=>$companyID, 'USERNAME'=>$v_people['email']));
		$v_response = json_decode($s_response, TRUE);
		if(isset($v_response['status']) && 1 == $v_response['status'])
		{
			$member = $v_response['data'];
			$nameToDisplay = preg_replace('/\s+/', ' ', $member['first_name'].' '.$member['middle_name'].' '.$member['last_name']);
			if($nameToDisplay == "")
			{
				if('' != $member['fullname'])
				{
					$nameToDisplay = $member['fullname'];
				} else if('' != $member['users_name'])
				{
					$nameToDisplay = $member['users_name'];
				}else {
					$nameToDisplay = $member['username'];
				}
			}
			$phoneToDisplay = $member['mobile'];
			$emailToDisplay = $member['username'];
		}
		
		$v_signers[] = array(
			'name'=> $nameToDisplay,
			'email'=> $emailToDisplay,
			'mobile'=> $phoneToDisplay,
			'main'=> $v_people['mainContact'],
			'priority'=> 0,
		);
	}
}
if($moduleAccesslevel > 10)
{
	if(isset($_POST['output_form_submit']))
	{
		if(isset($_POST['folder_id']) && 0 < $_POST['folder_id'])
		{
			$v_signers = array();
			foreach($_POST['signer_info'] as $s_signer)
			{
				$v_signer = explode('[::]', $s_signer);
				if('' != $v_signer[0] && '' != $v_signer[1])
				{
					$v_signers[] = array(
						'name'=> $v_signer[0],
						'email'=> $v_signer[1],
						'mobile'=> $v_signer[2],
						'priority'=> $v_signer[3],
					);
				}
			}
			
			if($v_files['id'] > 0 && '' != $s_filename && is_file(BASEPATH.$s_filename))
			{
				$s_signant_file = BASEPATH.'modules/IntegrationSignant/output/includes/class_IntegrationSignant.php';
				if(is_file($s_signant_file))
				{
					include($s_signant_file);
					$o_signat = new IntegrationSignant();
					$b_notify_admins = TRUE;
					$s_signant_description = '';
					
					$s_signant_title = $v_files['filename'];
					
					if(count($v_signers)>0)
					{
						$v_user_profile = json_decode($variables->fw_session['user_profile'], TRUE);
						$s_admin_name = preg_replace('/\s+/', ' ', $v_user_profile['name'].' '.$v_user_profile['middle_name'].' '.$v_user_profile['last_name']);
						$s_admin_mobile = $v_user_profile['mobile_prefix'].$v_user_profile['mobile'];
						
						$v_admins = array(
							array(
								'name'=> $s_admin_name,
								'email'=> $_COOKIE['username'],
								'mobile'=> $s_admin_mobile,
							)
						);
						
						$v_response = $o_signat->createSignPosting($s_signant_title, $v_files['filename'], BASEPATH.$s_filename, $s_signant_description, $v_admins, $v_signers, NULL, NULL, $b_notify_admins, 0, $_POST['folder_id']);
						if(isset($v_response['status']) && 1 == $v_response['status'])
						{
							$o_main->db->query("UPDATE subscriptionmulti_files SET signant_id = '".$o_main->db->escape_str($v_response['signant_id'])."' WHERE id = '".$o_main->db->escape_str($v_files['id'])."'");
						} else {
							$fw_error_msg['error_'.count($fw_error_msg)] = json_encode($v_response);
						}
					} else {
						$fw_error_msg['error_'.count($fw_error_msg)] = $formText_NoSignersSpecifiedOrFound_Output;
					}
				} else {
					$fw_error_msg['error_'.count($fw_error_msg)] = $formText_SignantIntegrationNotFound_Output;
				}
			} else {
				$fw_error_msg['error_'.count($fw_error_msg)] = $formText_FileNotFound_Output;
			}
		} else {
			$fw_error_msg['error_'.count($fw_error_msg)] = $formText_ChooseFolder_Output;
		}
		$fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$v_people['customerId'];
		return;
	}
}
?>
<div class="popupform">
	<div id="popup-validate-message" style="display:none;"></div>
	<form class="output-form main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=".$s_inc_obj."&inc_act=".$s_inc_act;?>" method="post">
		<input type="hidden" name="fwajax" value="1">
		<input type="hidden" name="fw_nocss" value="1">
		<input type="hidden" name="output_form_submit" value="1">
		<input type="hidden" name="cid" value="<?php print $_POST['cid'];?>">
		<div class="inner">
			<h3><?php echo $formText_SendFileForSigning_Output;?></h3>
			
			<div class="line">
        		<div class="lineTitle"><?php echo $formText_Folder_Output; ?></div>
        		<div class="lineInput">
                    <select name="folder_id">
					<option><?php echo $formText_ChooseFolder_Output;?></option>
					<?php
					if(1 == $variables->useradmin)
					{
						$s_sql = "SELECT f.*, IF(fu.id IS NULL, 0, 1) AS is_access, IF(fu.id IS NULL, 0, fu.folder_admin) AS folder_admin FROM integration_signant_folder AS f LEFT OUTER JOIN integration_signant_folder_user AS fu ON fu.folder_id = f.id AND fu.username = '".$o_main->db->escape_str($variables->loggID)."' GROUP BY f.id ORDER BY f.name";
					} else {
						$s_sql = "SELECT f.*, 1 AS is_access, fu.folder_admin FROM integration_signant_folder AS f JOIN integration_signant_folder_user AS fu ON fu.folder_id = f.id AND fu.username = '".$o_main->db->escape_str($variables->loggID)."' GROUP BY f.id ORDER BY f.name";
					}
					$o_query = $o_main->db->query($s_sql);
					if($o_query && $o_query->num_rows()>0)
					foreach($o_query->result_array() as $v_row)
					{
						if(1 == $v_row['is_access'])
						{
							?><option value="<?php echo $v_row['id'];?>"><?php echo $v_row['name'];?></option><?php
						}
					}
					?>
					</select>
                </div>
        		<div class="clear"></div>
    		</div>
			
			<?php
			$v_items = $v_signers;
			foreach($v_items as $v_item)
			{
				$b_fail = (empty($v_item['name']) || empty($v_item['email']));
				if(1 == $v_item['main'] && !$b_fail)
				{
					?><div class="line">
						<div class="lineTitle"><?php echo $formText_SignerFromContactperson_Output; ?></div>
						<div class="lineInput">
							<select name="signer_info[]">
							<option><?php echo $formText_ChooseContactperson_Output;?></option>
							<?php
							foreach($v_signers as $v_row)
							{
								$b_valid = ('' != $v_row['name'] && '' != $v_row['email']);
								?><option value="<?php echo $v_row['name'].'[::]'.$v_row['email'].'[::]'.$v_row['mobile'].'[::]'.$v_row['priority'];?>"<?php echo ($v_row['email']==$v_item['email']?' selected':'').(!$b_valid?' disabled':'');?>><?php echo ('' != $v_row['name']?$v_row['name']:$formText_MissingName_Output).' ('.('' != $v_row['email']?$v_row['email']:$formText_MissingEmailAddress_Output).')';?></option><?php
							}
							?>
							</select>
							<a href="#" onClick="$(this).closest('.line').remove(); return false;"><span class="glyphicon glyphicon-trash"></span></a>
						</div>
						<div class="clear"></div>
					</div><?php
				}
			}
			?>
			
			<div class="popupform-action">
				<a href="#" class="popupform-add-signer-from-contactperson script" onClick=""><?php echo $formText_AddSignerFromContactperson_Output;?></a>
				<a href="#" class="popupform-add-signer-manually script" onClick=""><?php echo $formText_AddSignerManually_Output;?></a>
			</div>
		</div>
		<div class="popupformbtn"><input type="submit" name="sbmbtn" value="<?php echo $formText_Send_Output; ?>"></div>
	</form>
</div>
<div class="popupform-contactperson-clone" style="display:none;">
	<div class="line">
		<div class="lineTitle"><?php echo $formText_SignerFromContactperson_Output; ?></div>
		<div class="lineInput">
			<select name="signer_info[]">
			<option><?php echo $formText_ChooseContactperson_Output;?></option>
			<?php
			foreach($v_signers as $v_row)
			{
				$b_valid = ('' != $v_row['name'] && '' != $v_row['email']);
				?><option value="<?php echo $v_row['name'].'[::]'.$v_row['email'].'[::]'.$v_row['mobile'].'[::]'.$v_row['priority'];?>"<?php echo (!$b_valid?' disabled':'');?>><?php echo ('' != $v_row['name']?$v_row['name']:$formText_MissingName_Output).' ('.('' != $v_row['email']?$v_row['email']:$formText_MissingEmailAddress_Output).')';?></option><?php
			}
			?>
			</select>
			<a href="#" onClick="$(this).closest('.line').remove(); return false;"><span class="glyphicon glyphicon-trash"></span></a>
		</div>
		<div class="clear"></div>
	</div>
</div>
<?php
$v_script = array(
  'modules/'.$module.'/output/elementsOutput/jquery.validate/jquery.validate.min.js',
);

foreach($v_script as $s_item)
{
  $l_time = filemtime(BASEPATH.$s_item);
  ?><script type="text/javascript" src="<?php echo $variables->account_root_url.$s_item.'?v='.$l_time;?>"></script><?php
}
?>
<script type="text/javascript">
function popupformBindBtns()
{
	$('.popupform-cancel-add-signer').off('click').on('click', function(e){
		e.preventDefault();
		$(this).closest('.popupform-signer-item').remove();
		$(window).resize();
	});
	$('.popupform-save-add-signer').off('click').on('click', function(e){
		e.preventDefault();
		var $parent = $(this).closest('.popupform-signer-item');
		$parent.find('.signer_info').val($parent.find('.signer_name').val() + '[::]' + $parent.find('.signer_email').val() + '[::]' + $parent.find('.signer_mobile').val() + '[::]0');
		$parent.find('.signer_info').before('<b><?php echo $formText_ManuallyAddedSigner_Output;?>:</b> ' + $parent.find('.signer_name').val() + ' (' + $parent.find('.signer_email').val() + ') <a href="#" onClick="$(this).closest(\'.popupform-signer-item\').remove(); return false;"><span class="glyphicon glyphicon-trash"></span></a>');
		$parent.find('.line, .popupform-action-local').remove();
		$(window).resize();
	});
}
$(function() {
	var uniqId = (function(){
		var i=0;
		return function() {
			return i++;
		}
	})();

	$('.popupform-add-signer-from-contactperson').off('click').on('click', function(e){
		e.preventDefault();
		$('.popupform-contactperson-clone .line').clone().insertBefore('.popupform-action');
		$(window).resize();
	});
	$('.popupform-add-signer-manually').off('click').on('click', function(e){
		e.preventDefault();
		var id = uniqId();
		$('<div class="popupform-signer-item-' + id + '"></div>').insertBefore('.popupform-action');
		ajaxCall('send_to_signant_add_signer_manually', { id: id }, function(json) {
            $('#popupeditboxcontent2').html(json.html);
			out_popup2 = $('#popupeditbox2').bPopup(out_popup_options);
			$("#popupeditbox2:not(.opened)").remove();
        });
	});
	
	$("form.output-form").validate({
		submitHandler: function(form) {
			if($(form).find('.popupform-action-local').length)
			{
				$("#popup-validate-message").html("<?php echo $formText_SaveSignerFirst_Output;?>", true);
				$("#popup-validate-message").show();
				$(window).resize();
			} else {
				fw_loading_start();
				$.ajax({
					url: $(form).attr("action"),
					cache: false,
					type: "POST",
					dataType: "json",
					data: $(form).serialize(),
					success: function (json) {
						fw_loading_end();
						if(json.error !== undefined)
						{
							var _msg = '';
							$.each(json.error, function(index, value){
								var _type = Array('error');
								if(index.length > 0 && index.indexOf('_') > 0) _type = index.split('_');
								_msg = _msg + '<div class="msg-' + _type[0] + '">' + value + '</div>';
							});
							$('#popup-validate-message').html(_msg, true);
							$('#popup-validate-message').show();
						} else {
							if(json.redirect_url !== undefined)
							{
								out_popup.addClass("close-reload");
								out_popup.close();
							}
						}
					}
				}).fail(function() {
					$("#popup-validate-message").html("<?php echo $formText_ErrorOccurredHandlingRequest_Output;?>", true);
					$("#popup-validate-message").show();
					$(window).resize();
					fw_loading_end();
				});
			}
		},
		invalidHandler: function(event, validator) {
			var errors = validator.numberOfInvalids();
			if (errors) {
				var message = errors == 1
				? '<?php echo $formText_YouMissed_validate; ?> 1 <?php echo $formText_field_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>'
				: '<?php echo $formText_YouMissed_validate; ?> ' + errors + ' <?php echo $formText_fields_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>';

				$("#popup-validate-message").html(message);
				$("#popup-validate-message").show();
				$(window).resize();
			} else {
				$("#popup-validate-message").hide();
			}
			setTimeout(function(){ $('#popupeditbox').height(''); }, 200);
		}
	});
	$(".datepicker").datepicker({
		firstDay: 1,
        dateFormat: 'dd.mm.yy',
	});
});
</script>
<style>
.popupform-action {
	margin:10px 0;
}
.popupform-action-local {
	margin:0 0 10px 0;
	text-align:right;
}
.popupform-action a, .popupform-action-local a {
	padding-right:20px;
}
.popupform input.popupforminput.checkbox {
	width: auto;
}
.popupform, .popupeditform {
	width:100%;
	margin:0 auto;
	border:1px solid #e8e8e8;
	position:relative;
}
label.error { display: none !important; }
input.error { border-color:#c11; }
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
.popupform input.popupforminput, .popupform textarea.popupforminput, .col-md-8z input {
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
	border-radius: 4px;
	border:0px none;
	background-color:#0393ff;
	font-size:13px;
	line-height:0px;
	padding: 20px 35px;
	font-weight:700;
	color:#FFF;
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
.popupform .line .lineInput {
	width:70%;
	float:left;
}
</style>
