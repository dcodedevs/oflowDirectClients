<div style="max-width:50rem; margin:10rem auto;">
	<div class="panel panel-default">
		<div class="panel-heading">
			<h3 class="panel-title"><?php echo $formText_EnterCodeYouReceivedOnMobile_Framework;?></h3>
		</div>
		<div class="panel-body">
			<?php if(1 == $fw_verify_mobile_code['status'] || 10 == $fw_verify_mobile_code['status']) { ?>
			<form method="post" action="<?php echo fwCurrentPageUrl();?>">
				<input type="hidden" name="fw_verify_mobile_id" value="<?php echo $fw_verify_mobile_code['id'];?>">
				<input type="hidden" name="fw_verify_device_id" value="<?php echo $fw_verify_mobile_code['device_id'];?>">
				<?php if($b_do_mobile_verification) { ?>
				<input type="hidden" name="fw_verify_mobile" value="<?php echo $fw_verify_mobile_code['mobile'];?>">
				<div class="twofa_header_info"><?php echo $formText_CodeHasBeenSentTo_LID48660.': '.($b_do_mobile_verification?$fw_verify_mobile_code['mobile']:$fw_verify_mobile_code['email']).'. '.$formText_IsItIncorrect_LID48661.'? ';?><a href="https://getynet.com/<?php echo $variables->languageID;?>/settings"><?php echo $formText_ChangeItInYourUserProfile_LID48662;?></a></div>
				<?php } else if($b_do_google_verification) { ?>
				<input type="hidden" name="fw_verify_google" value="1">
				<?php } else { ?>
				<input type="hidden" name="fw_verify_email" value="<?php echo $fw_verify_mobile_code['email'];?>">
				<?php } ?>
				<div class="form-group">
					<label><?php print $formText_Code_Framework;?></label>
					<input type="text" class="form-control" name="fw_verify_mobile_code" placeholder="<?php print $formText_EnterCode_Framework;?>">
				</div>
				<?php if(10 === $fw_verify_mobile_code['status']) { ?>
				<div class="alert alert-danger" role="alert"><?php echo $formText_CodeIsInvalid_Framework;?></div>
				<?php } ?>
				<div class="buttons">
					<button type="button" class="btn btn-primary verify ld-ext-right" onClick="$(this).addClass('running');$(this).closest('form').submit();">
						<?php echo $formText_Verify_Framework;?>
						<div class="ld ld-ring ld-spin"></div>
					</button>
					<button type="button" class="btn btn-default pull-right verify-resend"><?php echo $formText_Resend_Framework;?></button>
				</div>
			</form>
			<?php
			} else if(20 == $fw_verify_mobile_code['status']) {
				echo '<div>'.$formText_SmsAuthenticationIsNeededLoLoginToThisAccount_Framework.'.</dvi>';
				echo '<div>'.$formText_YourMobileNumberIsNotVerifiedAndYouNeedToDoThatFirst_Framework.'.</dvi>';
				echo '<div>'.$formText_YouCanVerifyYourMobileNumberInYourProfilePageIn_Framework.' <a href="https://www.getynet.com">www.getynet.com</a></dvi>';
			} else if(21 == $fw_verify_mobile_code['status']) {
				echo '<div>'.$formText_YouNeedToConfigureTwoFactorAuthenticationInYourProfile_LID48330.'</div>';
			} else {
				if($b_do_mobile_verification) {
					echo $formText_ErrorOccurredSendingCodeToYourMobile_Framework;
				} else {
					echo $formText_ErrorOccurredSendingCodeToYourEmail_LID48331;
				}
			}
			?>
			<div style="margin-top: 20px;"><?php echo $formText_BackTo_LID48669;?> <a href="https://www.getynet.com">www.getynet.com</a></div>
		</div>
	</div>
</div>
<script type="text/javascript">
$(function(){
	$('button.verify-resend').off('click').on('click', function(e){
		var $form = $(this).closest('form');
		$form.find('input[name="fw_verify_mobile_id"]').val('resend');
		$form.submit();
	})
});
</script>