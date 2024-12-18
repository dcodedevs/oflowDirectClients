<!-- <div class="p_headerLine"><?php
if($moduleAccesslevel > 10)
{
	if(intval($_GET['cid']) == 0) {
	?>
	<div class="addNewCustomerBtn btnStyle">
		<div class="plusTextBox active">
			<div class="plusBox addres"><div class="lineH"></div><div class="lineV"></div></div>
			<div class="text"><?php echo $formText_AddNew_Output; ?></div>
		</div>
		<div class="clear"></div>
	</div>
	<?php } ?>
	<div style="display:none;" class="boxed">
		<div id="exportForm"><?php

		?><form method="post" action="/accounts/<?=$_GET['accountname']?>/modules/<?=$_GET['module']?>/input/buttontypes/ExportIfbHomes/button.php" accept-charset="UTF-8">
			<p align="center">
			<?php print 'Eksport fra tabellen "'.$_GET['module'].'"'; ?>
			</p>
			<p align="center">
				<input type="hidden" value="<?=$submodule ?>" name="table">
				<input type="hidden" value="<?=$choosenListInputLang ?>" name="languageID">
				<input type="submit" value="Export!">
			</p>
		</form>

		</div>
	</div>

	<div class="clear"></div>
	<?php
}
?></div>


<script type="text/javascript">
$(".addNewCustomerBtn").on('click', function(e){
    e.preventDefault();
    var data = {
        supportId: 0
    };
    ajaxCall('editOrder', data, function(json) {
        $('#popupeditboxcontent').html('');
        $('#popupeditboxcontent').html(json.html);
        out_popup = $('#popupeditbox').bPopup(out_popup_options);
        $("#popupeditbox:not(.opened)").remove();
    });
});
</script>
<style>
	.p_headerLine .btnStyle.addEditCustomerGroup {
		margin-left: 40px;
	}	
	.p_headerLine .btnStyle.addEditSelfDefinedFields {
		margin-left: 40px;
	}	
</style>
 -->