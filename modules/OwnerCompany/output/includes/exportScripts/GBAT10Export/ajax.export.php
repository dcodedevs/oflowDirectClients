<?php
$ownercompany_id = $_POST['ownercompany_id'];
$activate_global_export = $_POST['activate_global_export'];
$from_id = $_POST['from_id'];
$from_number = $_POST['from_number'];
$to_id = $_POST['to_id'];
$to_number = $_POST['to_number'];
$redirect_to = $_POST['redirect_to'];
$export2 = 0;
if($_POST['export2']) {
	$export2 = 1;
}

if ($activate_global_export) {
    $o_query = $o_main->db->query("SELECT * FROM ownercompany_accountconfig");
} else {
    $o_query = $o_main->db->query("SELECT * FROM ownercompany WHERE id = ?", array($ownercompany_id));
}

$settingsData = ($o_query ? $o_query->row_array() : array());
$scriptName = basename(__DIR__);
?>

<?php if (!$settingsData['accountRoundingsOnInvoice']): ?>
	<div style="color:red;"><?php echo $formText_PleaseSetRoundingAccountInSettings_output; ?></div>
<?php endif; ?>
<div class="popupform">
	<div class="popupformTitle"><?php echo $formText_ExportUsingScript_output;?> <?php echo $scriptName;?></div>
	<form class="output-form" action="<?php print $extradir; ?>/output/includes/exportScripts/<?php echo $scriptName;?>/export.php" method="get">
        <input type="hidden" name="redirect_to" value="<?php echo $redirect_to; ?>">
        <input type="hidden" name="activate_global_export" value="<?php echo $activate_global_export; ?>">
        <input type="hidden" name="ownercompany_id" value="<?php echo $ownercompany_id; ?>">
        <input type="hidden" name="export2" value="<?php echo $export2; ?>">
		<div class="inner">
			<div class="line">
                <div class="lineTitle"><?php echo $activate_global_export ? $formText_FromInvoiceId_Output : $formText_FromInvoiceNumber_output; ?></div>
    			<div class="lineInput"><input class="popupforminput botspace" name="from" type="text" value="<?php echo $activate_global_export ? $from_id : $from_number; ?>"></div>
			<div class="clear"></div>
			</div>
			<div class="line">
                <div class="lineTitle"><?php echo $activate_global_export ? $formText_ToInvoiceId_Output : $formText_ToInvoiceNumber_output; ?></div>
    			<div class="lineInput"><input class="popupforminput botspace" name="to" type="text" value="<?php echo $activate_global_export ? $to_id : $to_number; ?>"></div>
			<div class="clear"></div>
			</div>
		</div>
		<div class="popupformbtn">
			<button type="button" class="output-btn b-large b-close"><?php echo $formText_Cancel_Output;?></button>
			<input type="submit" name="sbmbtn" value="<?php echo $formText_Export_Output; ?>">
		</div>
	</form>
</div>
<script type="text/javascript">
	$(".output-form").submit(function( event ) {
		out_popup.close();
	});
</script>
<style>

.selectDivModified {
    display:block;
}
.popupform, .popupeditform {
	width:100%;
	margin:0 auto;
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
</style>
