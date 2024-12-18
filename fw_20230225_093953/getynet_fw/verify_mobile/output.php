<div style="max-width:50rem; margin:10rem auto;">
	<div class="panel panel-default">
		<div class="panel-heading">
			<h3 class="panel-title"><?php echo $formText_EnterCodeYouReceivedOnMobile_Framework;?></h3>
		</div>
		<div class="panel-body">
			<?php if(1 == $fw_verify_mobile_code['status'] || 10 == $fw_verify_mobile_code['status']) { ?>
			<form method="post" action="<?php echo fwCurrentPageUrl();?>">
				<input type="hidden" name="fw_verify_mobile" value="<?php echo $fw_verify_mobile_code['mobile'];?>">
				<input type="hidden" name="fw_verify_mobile_id" value="<?php echo $fw_verify_mobile_code['id'];?>">
				<div class="form-group">
					<label><?php print $formText_Code_Framework;?></label>
					<input type="text" class="form-control" name="fw_verify_mobile_code" placeholder="<?php print $formText_EnterCode_Framework;?>">
				</div>
				<?php if(10 === $fw_verify_mobile_code['status']) { ?>
				<div class="alert alert-danger" role="alert"><?php echo $formText_CodeIsInvalid_Framework;?></div>
				<?php } ?>
				<div class="buttons">
					<button type="button" class="btn btn-default verify ld-ext-right" onClick="$(this).addClass('running');$(this).closest('form').submit();">
						<?php echo $formText_Verify_Framework;?>
						<div class="ld ld-ring ld-spin"></div>
					</button>
				</div>
			</form>
			<?php
			} else if(20 == $fw_verify_mobile_code['status']) {
				echo '<div>'.$formText_SmsAuthenticationIsNeededLoLoginToThisAccount_Framework.'.</dvi>';
				echo '<div>'.$formText_YourMobileNumberIsNotVerifiedAndYouNeedToDoThatFirst_Framework.'.</dvi>';
				echo '<div>'.$formText_YouCanVerifyYourMobileNumberInYourProfilePageIn_Framework.' <a href="https://www.getynet.com">www.getynet.com</a></dvi>';
			} else {
				echo $formText_ErrorOccurredSendingCodeToYourMobile_Framework;
			}
			?>
		</div>
	</div>
</div>