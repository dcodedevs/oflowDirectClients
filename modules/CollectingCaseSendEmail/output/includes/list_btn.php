<?php
$o_query = $o_main->db->get('ownercompany_accountconfig');
$ownercompany_accountconfig = $o_query ? $o_query->row_array() : array();
?>
<div class="p_headerLine"><?php
if($moduleAccesslevel > 10)
{
	if(1==0) {
	?>
	<div class="launchPdfGenerate btnStyle">
		<div class="plusTextBox active">
			<div class="text"><?php echo $formText_LaunchPdfGenerateScript_output; ?></div>
		</div>
		<div class="clear"></div>
	</div>
	<?php } ?>
	<div class="clear"></div>
	<?php
}
?></div>


<script type="text/javascript">
$(".launchPdfGenerate").on("click", function(e){
	e.preventDefault();
	ajaxCall('generate_pdf', { cid: '<?php echo $cid;?>' }, function(json) {
		var win = window.open('<?php echo $extradomaindirroot.'/modules/CollectingCases/output/ajax.download.php?ID=';?>' + json.data.batch_id, '_blank');
		win.focus();
	});
})
$(".importPayments").on('click', function(e){
	e.preventDefault();
	var data = { };
    ajaxCall('importPayments', data, function(obj) {
        $('#popupeditboxcontent').html('');
        $('#popupeditboxcontent').html(obj.html);
        out_popup = $('#popupeditbox').bPopup(out_popup_options);
        $("#popupeditbox:not(.opened)").remove();
    });
});
</script>
<style>
	.p_headerLine .btnStyle.addEditCustomerGroup {
		margin-left: 40px;
	}
	.p_headerLine .btnStyle.importPayments {
		margin-left: 40px;
	}
</style>
