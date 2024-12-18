<?php
$settingsFind = mysql_query("SELECT * FROM invoice_accountconfig;");
$settingsData = mysql_fetch_array($settingsFind);
?>

<?php if (!$settingsData['accountRoundingsOnInvoice']): ?>
	<div style="color:red;"><?php echo $formText_PleaseSetRoundingAccountInSettings_output; ?></div>
<?php endif; ?>
<form action="<?php print $extradir; ?>/addOn_include/sendfaktura.php" method="get">
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr><td>From invoice number</td><td><input type="text" value="" name="fra" /></td></tr>
	<tr><td>To invoice number</td><td><input type="text" value="" name="til" /></td></tr>
	<tr><td colspan="2"><input type="submit" name="submit" value="Export" /></td></tr>
	</table>	
	<div class="popupformbtn">

		<button type="button" class="output-btn b-large b-close"><?php echo $formText_Cancel_Output;?></button>

		<button type="button" class="submitbtn"><?php echo $formText_Export_output;?></button>

	</div>
</form>
