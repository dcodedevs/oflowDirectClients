<?php
$s_lang_file = __DIR__."/../../../languages/default.php";
if(is_file($s_lang_file)) include($s_lang_file);
$s_lang_file = __DIR__."/../../../languages/".$variables->languageID.".php";
if(is_file($s_lang_file)) include($s_lang_file);
if(!function_exists("APIconnectorAccount")) include(__DIR__."/../../../includes/APIconnector.php");
include(__DIR__."/includes/readAccessElements.php");
$l_edit = 0;
if(isset($_POST['channel_id']) && intval($_POST['channel_id']) > 0)
{
	$s_response = APIconnectorUser("channel_get", $_COOKIE['username'], $_COOKIE['sessionID'], array('CHANNEL_ID'=>$_POST['channel_id']));
	$v_response = json_decode($s_response,true);
	if($v_response['status'] == 1)
	{
		$v_channel = $v_response['channel'];
		$v_access = $v_response['access'];
		$l_edit = 1;
	} else {
		echo $formText_ErrorOccurredRetrievingData_Framework;
		return;
	}
}
$v_companies = $v_accounts = $v_apps = $v_groups = array();
$s_response = APIconnectorUser("company_get_list", $_COOKIE['username'], $_COOKIE['sessionID']);
$v_response = json_decode($s_response,true);
if(isset($v_response['data']))
{
	$v_companies = $v_response['data'];
}
$s_response = APIconnectorUser("group_get_list", $_COOKIE['username'], $_COOKIE['sessionID'], array('company_id'=>$_GET['companyID']));
$v_response = json_decode($s_response,true);
if(isset($v_response['status']) && $v_response['status'] == 1)
{
	$v_groups = $v_response['items'];
}
if($v_channel){
	$fw_return_data = $v_channel['name'];
} else {
	$fw_return_data = "<b>".$formText_AddNewGroupChat_Chat2."</b>";
}
?>
<div class="popupform popupform-<?php echo $v_channel['id'];?>">
	<div id="popup-validate-message" style="display:none;"></div>
    <div class="channel_edit_wrapper">
    	<?php if($variables->useradmin == 1 || $variables->system_admin == 1) { ?>
    	<form  class="output-form main"  name="upadate" action="<?php echo $variables->account_framework_url."index.php?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&getynetaccount=1&module=37&folderfile=ajax.save_channel_settings&folder=output&modulename=Chat";?>" method="POST">
    	<input type="hidden" name="channel_id" value="<?php echo $_POST['channel_id']; ?>" />
        <input type="hidden" name="fwajax" value="1">
		<input type="hidden" name="fw_nocss" value="1">
    	<div class="profile">
            <div class="popupformTitle"><?php if($v_channel){ echo $v_channel['name']; } else{ echo $formText_AddNewGroupChat_Chat2;}?></div>
    		<table border="0" cellpadding="0" cellspacing="0" rules="none" frame="void" width="100%" class="channel_info_table">
    			<tr>
    				<td width="120">
    					<span class="formLabel"><?php echo $formText_ChannelName_Framework;?><span>
    				</td>
    				<td>
    					<input type="hidden" name="channel_type" value="0">
    					<input class="form-control input-sm size-50" type="text" name="channel_name" value="<?php echo $v_channel['name']; ?>"/>
    				</td>
    			</tr>
    			<tr>
    				<td width="120">
    					<span class="formLabel"><?php echo $formText_Status_Framework;?><span>
    				</td>
    				<td>
    					<?php
    					$v_channel_statuses = array(1=>$formText_PlacedInActiveListForAllUsers_Framework, 2=>$formText_PlacedInHiddenListForAllUsers_Framework, 3=>$formText_InactiveNotVisibleForAllUsers_Framework);
    					?>
    					<select class="form-control input-sm size-30" name="channel_status"><?php
    					foreach($v_channel_statuses as $key => $item)
    					{
    						?><option value="<?php echo $key;?>"<?php echo ($v_channel['status']==$key?' selected':'');?>><?php echo $item;?></option><?php
    					}
    					?></select>
    				</td>
    			</tr>
    			<tr>
    				<td colspan="2">
    					<span class="formLabel">
    						<?php echo $formText_DeactivateCommentAsNewMessage_Framework;?>
    					</span>
    					<input type="checkbox" class="checkboxInput input-sm size-30" name="deactivate_comment_as_new_message"  value="1"<?php echo ($v_channel['deactivate_comment_as_new_message']==1?' checked':'');?>>
    				</td>
    			</tr>
    			<tr><td colspan="2">
                    <div class="popupformbtn">
            			<input type="submit" class="fw-btn fw-btn-small fw_button_color" name="sbmbtn" value="<?php echo $formText_Save_Framework; ?>">
            		</div>
    			</td>
    			</tr>
    		</table>

    	</div>
    	</form>
    	<?php } else { ?>
    	<div class="info-message"><?php echo $formText_OnlyCompanyAdministratorsCanAddNewChannel_Framework.". ".$formText_PleaseContactAdministratorsToCompleteThisAction_Framework;?></div>
    	<?php } ?>
    </div>
</div>
<script type="text/javascript" src="getynet_fw/modules/Chat/output/includes/jquery.validate.min.js"></script>
<script type="text/javascript">

    $(".popupform-<?php echo $v_channel['id'];?> form.output-form").validate({
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
					if(data.error !== undefined)
					{
						$.each(data.error, function(index, value){
							$("#popup-validate-message").append("<div>"+value+"</div>").show();
						});
						fw_click_instance = fw_changes_made = false;
					} else {
                        out_popup_chat.addClass("close-reload");
                        out_popup_chat.data("channel-id", data.data.channel_id);
                        out_popup_chat.close();
					}
                }
            }).fail(function() {
                $(".popupform-<?php echo $v_channel['id'];?> #popup-validate-message").html("<?php echo $formText_ErrorOccuredSavingContent_Output;?>", true);
                $(".popupform-<?php echo $v_channel['id'];?> #popup-validate-message").show();
                $('.popupform-<?php echo $v_channel['id'];?> #popupeditbox').css('height', $('.popupform-<?php echo $v_channel['id'];?> #popupeditboxcontent').height());
                fw_loading_end();
            });
        },
        invalidHandler: function(event, validator) {
            var errors = validator.numberOfInvalids();
            if (errors) {
                var message = errors == 1
                ? '<?php echo $formText_YouMissed_validate; ?> 1 <?php echo $formText_field_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>'
                : '<?php echo $formText_YouMissed_validate; ?> ' + errors + ' <?php echo $formText_fields_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>';

                $(".popupform-<?php echo $v_channel['id'];?> #popup-validate-message").html(message);
                $(".popupform-<?php echo $v_channel['id'];?> #popup-validate-message").show();
                $('.popupform-<?php echo $v_channel['id'];?> #popupeditbox').css('height', $('#popupeditboxcontent').height());
            } else {
                $(".popupform-<?php echo $v_channel['id'];?> #popup-validate-message").hide();
            }
            setTimeout(function(){ $('#popupeditbox').height(''); }, 200);
        },
        errorPlacement: function(error, element) {
            if(element.attr("name") == "customerId") {
                error.insertAfter(".popupform-<?php echo $v_channel['id'];?> .selectCustomer");
            }
        },
        messages: {
            customerId: "<?php echo $formText_SelectTheCustomer_output;?>",
        }
    });

</script>
<?php

?>
