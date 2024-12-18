<?php
$peopleId = isset($_POST['cid']) ? $o_main->db->escape_str($_POST['cid']) : 0;

if($moduleAccesslevel > 10)
{
	if(isset($_POST['output_form_submit']))
	{
		$b_update = TRUE;
		$_POST['password'] = trim($_POST['password']);
		$_POST['password_old'] = trim($_POST['password_old']);
		$_POST['password_check'] = trim($_POST['password_check']);
		if(isset($_POST['password_old']) && strlen($_POST['password_old']) == 0)
		{
			$b_update = FALSE;
			$fw_error_msg = array($formText_CurrentPassowrdCannotBeEmpty_Output);
		}
		if($b_update && isset($_POST['password']) && strlen($_POST['password']) <= 6)
		{
			$b_update = FALSE;
			$fw_error_msg = array($formText_NewPasswordShouldBeAtLeast7Characters_Output);
		}
		if($b_update && isset($_POST['password']) && isset($_POST['password_check']) && $_POST['password'] != $_POST['password_check'])
		{
			$b_update = FALSE;
			$fw_error_msg = array($formText_NewPasswordsDoesNotMatch_Output);
		}
		if($b_update)
		{
			$v_param = array(
				'password_old' => $_POST['password_old'],
				'password_check' => $_POST['password_check'],
				'password' => $_POST['password']
			);
			$v_response = json_decode(APIconnectorUser('userprofileset', $_COOKIE['username'], $_COOKIE['sessionID'], $v_param), TRUE);
			if(isset($v_response['status']) && $v_response['status'] == 1)
			{
				$fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$peopleId;;
			} else {
				$fw_error_msg = array($formText_ErrorOccurredHandlingRequest_Output.': '.$v_response['error']);
			}
		}
		return;
	}
}
?>

<div class="popupform popupform-<?php echo $peopleId;?>">
	<div id="popup-validate-message" style="display:none;"></div>
	<form class="output-form main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=change_password";?>" method="post">
		<input type="hidden" name="fwajax" value="1">
		<input type="hidden" name="fw_nocss" value="1">
		<input type="hidden" name="output_form_submit" value="1">
		<input type="hidden" name="cid" value="<?php echo $peopleId;?>">
        <div class="popupformTitle"><?php echo $formText_ChangePassword_Output;?></div>
        <div class="inner">
			<div class="line">
				<div class="lineTitle"><?php echo $formText_CurrentPassword_Output; ?></div>
				<div class="lineInput">
                    <input class="popupforminput botspace" name="password_old" type="password" value="" required autocomplete="off">
				</div>
				<div class="clear"></div>
			</div>
			<div class="line">
				<div class="lineTitle"><?php echo $formText_NewPassword_Output; ?></div>
				<div class="lineInput">
                    <input class="popupforminput botspace" name="password" type="password" value="" required autocomplete="off">
				</div>
				<div class="clear"></div>
			</div>
			<div class="line">
				<div class="lineTitle"><?php echo $formText_RetryPassword_Output; ?></div>
				<div class="lineInput">
                    <input class="popupforminput botspace" name="password_check" type="password" value="" required autocomplete="off">
				</div>
				<div class="clear"></div>
			</div>
		</div>

		<div class="popupformbtn">
			<button type="button" class="output-btn b-large b-close fw_button_not_filled_color"><?php echo $formText_Cancel_Output;?></button>
			<input type="submit" class="fw_button_color" name="sbmbtn" value="<?php echo $formText_Save_Output; ?>">
		</div>
	</form>
</div>

<?php

$s_path = $variables->account_root_url;

$v_script = array(
  'modules/'.$module.'/output/elementsOutput/jquery.validate/jquery.validate.min.js',
);

foreach($v_script as $s_item)
{
  $l_time = filemtime(BASEPATH.$s_item);
  ?><script type="text/javascript" src="<?php echo $s_path.$s_item.'?v='.$l_time;?>"></script><?php
}

?>

<!-- <script type="text/javascript" src="../modules/<?php echo $module;?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script> -->
<script type="text/javascript">

$(document).ready(function() {
    $(".popupform-<?php echo $peopleId;?> form.output-form").validate({
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
	                    if(data.redirect_url !== undefined)
	                    {
	                        out_popup.addClass("close-reload").data("redirect", data.redirect_url);
	                        out_popup.close();
	                    }
					}
                }
            }).fail(function() {
                $(".popupform-<?php echo $peopleId;?> #popup-validate-message").html("<?php echo $formText_ErrorOccuredSavingContent_Output;?>", true);
                $(".popupform-<?php echo $peopleId;?> #popup-validate-message").show();
                $('.popupform-<?php echo $peopleId;?> #popupeditbox').css('height', $('.popupform-<?php echo $peopleId;?> #popupeditboxcontent').height());
                fw_loading_end();
            });
        },
        invalidHandler: function(event, validator) {
            var errors = validator.numberOfInvalids();
            if (errors) {
                var message = errors == 1
                ? '<?php echo $formText_YouMissed_validate; ?> 1 <?php echo $formText_field_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>'
                : '<?php echo $formText_YouMissed_validate; ?> ' + errors + ' <?php echo $formText_fields_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>';

                $(".popupform-<?php echo $peopleId;?> #popup-validate-message").html(message);
                $(".popupform-<?php echo $peopleId;?> #popup-validate-message").show();
                $('.popupform-<?php echo $peopleId;?> #popupeditbox').css('height', $('#popupeditboxcontent').height());
            } else {
                $(".popupform-<?php echo $peopleId;?> #popup-validate-message").hide();
            }
            setTimeout(function(){ $('#popupeditbox').height(''); }, 200);
        },
        errorPlacement: function(error, element) {
            if(element.attr("name") == "customerId") {
                error.insertAfter(".popupform-<?php echo $peopleId;?> .selectCustomer");
            }
            if(element.attr("name") == "projectLeader") {
                error.insertAfter(".popupform-<?php echo $peopleId;?> .selectEmployee");
            }
            if(element.attr("name") == "projectOwner") {
                error.insertAfter(".popupform-<?php echo $peopleId;?> .selectOwner");
            }
        },
        messages: {
            customerId: "<?php echo $formText_SelectTheCustomer_output;?>",
            projectLeader: "<?php echo $formText_SelectProjectLeader_output;?>",
            projectOwner: "<?php echo $formText_SelectProjectOwner_output;?>"
        }
    });
});
</script>
