<?php
$datePair = $_POST['datepair'];
$action = $_POST['action'];
$datePairArray0 = explode("_", $datePair);
$datePairArray = explode("/", $datePairArray0[1]);
$invoiceDate = $datePairArray[0];
$dueDate = $datePairArray[1];
if($action == "reset"){
	unset($_SESSION[$datePair]);
}
if(isset($_SESSION[$datePair])){
	$datePairArray1 = explode("_", $_SESSION[$datePair]);
	if(count($datePairArray1) == 2){
		$datePairArray2 = explode("/", $datePairArray1[1]);
		if(count($datePairArray2) == 2){
			$invoiceDate = $datePairArray2[0];
			$dueDate = $datePairArray2[1];
		}
	}
}

if($invoiceDate != "" && $dueDate != ""){
	if($moduleAccesslevel > 10)
	{
		if(isset($_POST['output_form_submit']))
		{
			$newDueDate = $_POST['dueDate'];
			$newInvoiceDate = $_POST['invoiceDate'];
			if(strtotime($newDueDate) >= strtotime($newInvoiceDate)){
				if($newInvoiceDate."/".$newDueDate == $datePair){
					unset($_SESSION[$datePair]);
				} else {
					$_SESSION[$datePair] = $datePairArray0[0]."_".$newInvoiceDate."/".$newDueDate;
				}
				$fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$_POST['customerId'];
				return;
			} else {
	            $fw_error_msg = $formText_DueDateCanNotBeLessThanInvoiceDate_output;
				return;
			}
		}
	}
	?>
	<div class="popupform">
	<div id="popup-validate-message" style="display:none;"></div>
	<form class="output-form" action="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=overrideDates";?>" method="post">
		<input type="hidden" name="fwajax" value="1">
		<input type="hidden" name="fw_nocss" value="1">
		<input type="hidden" name="output_form_submit" value="1">
		<input type="hidden" name="datepair" value="<?php echo $datePair;?>">

		<div class="inner">

			<div class="line">
			<div class="lineTitle"><?php echo $formText_InvoiceDate_Output; ?></div>
			<div class="lineInput">
	            <input type="text" class="popupforminput botspace datefield" name="invoiceDate" value="<?php echo $invoiceDate;?>" required>
	        </div>
			<div class="clear"></div>
			</div>

			<div class="line">
			<div class="lineTitle"><?php echo $formText_DueDate_Output; ?></div>
			<div class="lineInput">
	            <input type="text" class="popupforminput botspace datefield" name="dueDate" value="<?php echo $dueDate;?>" required>
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
	<script type="text/javascript" src="../modules/<?php echo $module?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
	<script type="text/javascript">
	$(function() {
	    $('.datefield').datepicker({
	        dateFormat: 'dd.mm.yy',
	        firstDay: 1
	    });
		$("form.output-form").validate({
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
						if(data.error !== undefined){
	                        $("#popup-validate-message").html(data.error);
	                        $("#popup-validate-message").show();
	                    } else {
	                        if(data.redirect_url !== undefined)
	                        {
	                            out_popup.addClass("close-reload");
	                            out_popup.close();
	                        }
	                    }
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
	});
	</script>
<?php } ?>
<style>
.popupform .line .lineInput .bookaccountNrWrapper select {
    max-width: 80%;
}
.popupform .line .lineInput select:disabled {
    border: 0;
    -webkit-appearance: none;
    -moz-appearance: none;
    text-indent: 1px;
    text-overflow: '';
    background: #fff;
}
.editBtnIcon {
    color: #46b2e2;
    margin-left: 10px;
    cursor: pointer;
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
}
.popupform .line .lineInput select {
    max-width: 100%;
}
.priceTotalLine .popupforminput {
    border: none !important;
}
.popupform input.popupforminput.checkbox {
    width: auto;
}
</style>
