<div class="popupform">
	<div id="popup-validate-message2" style="display:none;"></div>
	<form class="output-form-second" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=".$s_inc_obj."&inc_act=".$s_inc_act;?>" method="post">
		<div class="inner">
			<h3><?php echo $formText_AddSignerManually_Output;?></h3>
			
			<div class="line">
				<div class="lineTitle"><?php echo $formText_Name_Output; ?></div>
				<div class="lineInput"><input type="text" class="popupforminput botspace signer_name" name="signer_name" autocomplete="off" value="" required></div>
				<div class="clear"></div>
			</div>
			<div class="line">
				<div class="lineTitle"><?php echo $formText_Email_Output; ?></div>
				<div class="lineInput"><input type="text" class="popupforminput botspace signer_email" name="signer_email" autocomplete="off" value="" required></div>
				<div class="clear"></div>
			</div>
			<div class="line">
				<div class="lineTitle"><?php echo $formText_Mobile_Output; ?></div>
				<div class="lineInput"><input type="text" class="popupforminput botspace signer_mobile" name="signer_mobile" autocomplete="off" value=""></div>
				<div class="clear"></div>
			</div>
			
		</div>
		<div class="popupformbtn"><input type="submit" name="sbmbtn" value="<?php echo $formText_Add_Output; ?>"></div>
	</form>
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
$(function() {
	$("form.output-form-second").validate({
		submitHandler: function(form) {
			$('.popupform-signer-item-<?php echo $_POST['id'];?>').replaceWith('<div class="line">' +
				'<div class="lineTitle"><?php echo $formText_ManuallyAddedSigner_Output;?></div>' +
				'<div class="lineInput">' +
					$(form).find('.signer_name').val() + ' (' + $(form).find('.signer_email').val() + ')' +
					' <a href="#" onClick="$(this).closest(\'.line\').remove(); return false;"><span class="glyphicon glyphicon-trash"></span></a>' +
					'<input type="hidden" name="signer_info[]" value="' + $(form).find('.signer_name').val() + '[::]' + $(form).find('.signer_email').val() + '[::]' + $(form).find('.signer_mobile').val() + '[::]0' + '">' +
				'</div>' +
			'<div class="clear"></div>' +
			'</div>');
			$('#popupeditboxcontent2').html('');
			$("#popupeditbox2 .b-close").click();
		},
		invalidHandler: function(event, validator) {
			var errors = validator.numberOfInvalids();
			if (errors) {
				var message = errors == 1
				? '<?php echo $formText_YouMissed_validate; ?> 1 <?php echo $formText_field_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>'
				: '<?php echo $formText_YouMissed_validate; ?> ' + errors + ' <?php echo $formText_fields_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>';

				$("#popup-validate-message2").html(message);
				$("#popup-validate-message2").show();
				$(window).resize();
			} else {
				$("#popup-validate-message2").hide();
			}
			setTimeout(function(){ $('#popupeditbox2').height(''); }, 200);
		}
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
#popup-validate-message2, .error-msg { font-weight:bold; color:#c11; padding-bottom:10px; }
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
