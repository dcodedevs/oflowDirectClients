<?php
if(!function_exists("APIconnectUser")) include(__DIR__."/../../input/includes/APIconnect.php");

$o_query = $o_main->db->query("SELECT * FROM accountinfo");
$v_accountinfo = $o_query ? $o_query->row_array() : array();

$o_query = $o_main->db->query("SELECT * FROM customer_accountconfig ORDER BY id DESC");
$v_customer_accountconfig = $o_query ? $o_query->row_array() : array();

$o_query = $o_main->db->query("SELECT * FROM customer WHERE id = '".$o_main->db->escape_str($_POST['customer_id'])."'");
$v_data = $o_query ? $o_query->row_array() : array();

$b_enable_grant_admin = ($v_customer_accountconfig['getynet_grant_admin_access'] == 1);
$b_enable_grant_system_admin = ($v_customer_accountconfig['getynet_grant_system_admin_access'] == 1);
$b_enable_grant_designer = ($v_customer_accountconfig['getynet_grant_designer_access'] == 1);
$b_enable_grant_developer = ($v_customer_accountconfig['getynet_grant_developer_access'] == 1);

if($moduleAccesslevel > 10)
{
	if(isset($_POST['output_form_submit']) && $v_data['getynet_customer_id'] > 0)
	{
		$v_data = array(
			"PARTNER_ID"=>$v_customer_accountconfig['getynet_partner_id'],
			"PARTNER_PWD"=>$v_customer_accountconfig['getynet_partner_pw'],
			"COMPANY_ID"=>$v_data['getynet_customer_id'],
			"ADMIN"=>((isset($_POST['admin'])&&1==$_POST['admin'])?1:($b_enable_grant_admin?2:0)),
			"SYSTEM_ADMIN"=>((isset($_POST['sysadmin'])&&1==$_POST['sysadmin'])?1:($b_enable_grant_system_admin?2:0)),
			"DEVELOPER_ACCESS"=>((isset($_POST['developer'])&&1==$_POST['developer'])?1:($b_enable_grant_developer?2:0)),
			"TEMP_ACCESS_EXPIRE_DAYS"=>$_POST['temp_access'],
		);
		
		$s_response = APIconnectUser("accountaccessaddtomyself", $_COOKIE['username'], $_COOKIE['sessionID'], $v_data);
		$v_response = json_decode($s_response, true);
		if(array_key_exists('data', $v_response) && $v_response['data'] == 'OK')
		{
			$fw_return_data = array('message' => $formText_AccessGranted_Output);
		} else {
			$fw_error_msg['error_'.count($fw_error_msg)] = $formText_ErrorOccurredGrantingAccess_Output;
		}
		$fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$_POST['customer_id'];
		return;
	}
}
?>
<div class="popupform">
	<div id="popup-validate-message"></div>
	<form class="output-form" action="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=".$s_inc_obj."&inc_act=".$s_inc_act;?>" method="post">
	<input type="hidden" name="fwajax" value="1">
	<input type="hidden" name="fw_nocss" value="1">
	<input type="hidden" name="output_form_submit" value="1">
	<input type="hidden" name="customer_id" value="<?php echo $_POST['customer_id'];?>">
	<div class="inner">
		<h4><center><?php
		if($moduleAccesslevel > 10)
		{
			if($v_data['getynet_customer_id'] == 0)
			{
				echo $formText_CustomerNotFound_Output;
			} else {
				$b_access = $b_admin = $b_sysadmin = $b_developer = FALSE;
				$s_response = APIconnectUser("companyaccesscheck", $_COOKIE['username'], $_COOKIE['sessionID'], array("COMPANY_ID"=>$v_data['getynet_customer_id'], "ACCESS_LEVEL"=>1));
				$v_response = json_decode($s_response, TRUE);
				if(isset($v_response['status']) && 1 == $v_response['status'])
				{
					foreach($v_response['data'] as $v_item)
					{
						if('' != $v_item['deactivatedOn'] && '0000-00-00' != $v_item['deactivatedOn']) continue;
						if(1 == $v_item['accesslevel']) $b_access = TRUE;
						if(1 == $v_item['admin']) $b_admin = TRUE;
						if(1 == $v_item['system_admin']) $b_sysadmin = TRUE;
						if(20 == $v_item['developeraccess']) $b_developer = TRUE;
					}
				}
				
				if($b_enable_grant_admin)
				{
					?>
					<div class="line">
					<div class="lineTitle"><?php echo $formText_AdminAccess_Output; ?></div>
					<div class="lineInput"><input type="checkbox" class="popupforminput" name="admin" value="1" style="width:auto !important;"<?php echo ($b_admin?' checked':'');?>></div>
					<div class="clear"></div>
					</div>
					<?php
				}
				if($b_enable_grant_system_admin)
				{
					?>
					<div class="line">
					<div class="lineTitle"><?php echo $formText_SystemAdminAccess_Output; ?></div>
					<div class="lineInput"><input type="checkbox" class="popupforminput" name="sysadmin" value="1" style="width:auto !important;"<?php echo ($b_sysadmin?' checked':'');?>></div>
					<div class="clear"></div>
					</div>
					<?php
				}
				if($b_enable_grant_developer)
				{
					?>
					<div class="line">
					<div class="lineTitle"><?php echo $formText_DeveloperAccess_Output; ?></div>
					<div class="lineInput"><input type="checkbox" class="popupforminput" name="developer" value="1" style="width:auto !important;"<?php echo ($b_developer?' checked':'');?>></div>
					<div class="clear"></div>
					</div>
					<?php
				}
				?>
				<div class="line">
				<div class="lineTitle"><?php echo $formText_GiveAccessFor_Output; ?></div>
				<div class="lineInput">
					<select class="popupforminput" name="temp_access">
						<option value="1"><?php echo $formText_OneDay_Output;?></option>
						<option value="7"><?php echo $formText_OneWeek_Output;?></option>
						<option value="30"><?php echo $formText_OneMonth_Output;?></option>
						<option value="-1"><?php echo $formText_Permanent_Output;?></option>
					</select>
				</div>
				<div class="clear"></div>
				</div>
				<?php
				
			}
		} else {
			echo $formText_YouDontHaveAccess_Output;
		}
		?>
		</center></h4>
	</div>
	<div class="popupformbtn">
		<button type="button" class="output-btn b-large b-close"><?php echo $formText_Close_Output;?></button>
		<?php if($moduleAccesslevel > 10) { ?>
		<input type="submit" name="sbmbtn" value="<?php echo $formText_Save_Output; ?>">
		<?php } ?>
	</div>
	</form>
</div>
<script type="text/javascript" src="../modules/<?php echo $module?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
<script type="text/javascript">
$(function() {
	$("form.output-form").validate({
		submitHandler: function(form) {
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
						$('#popup-validate-message').html(_msg, true).show();
						fw_loading_end();
					} else {
						if(json.redirect_url !== undefined)
						{
							out_popup.addClass("close-reload");
							out_popup.close();
						}
					}
				}
			}).fail(function() {
				$("#popup-validate-message").html("<?php echo $formText_ErrorOccuredSavingContent_Output;?>", true).show();
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
});
</script>
<style>
.popupform input.popupforminput.checkbox {
    width: auto;
}
.popupform .inlineInput input.popupforminput {
    display: inline-block;
    width: auto;
    vertical-align: middle;
    margin-right: 20px;
}
.popupform .inlineInput label {
    display: inline-block !important;
    vertical-align: middle;
}
.selectDivModified {
    display:block;
}
.popupform, .popupeditform {
    width:100%;
    margin:0 auto;
    border:1px solid #e8e8e8;
    position:relative;
}
.invoiceEmail {
    display: none;
}
.selectDivModified {
    display:block;
}
.popupform, .popupeditform {
	width:100%;
	margin:0 auto;
	border:1px solid #e8e8e8;
	position:relative;
}
label.error { display: none !important; }
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
	text-align:left;
}
</style>
