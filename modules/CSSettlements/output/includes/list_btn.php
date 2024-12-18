<?php
$o_query = $o_main->db->get('ownercompany_accountconfig');
$ownercompany_accountconfig = $o_query ? $o_query->row_array() : array();
?>
<div class="p_headerLine"><?php
if($moduleAccesslevel > 10)
{
	if(intval($_GET['cid']) == 0) {
	?>
	<!-- <div class="addEditProcessSteps btnStyle">
		<div class="plusTextBox active">
			<div class="plusBox addres"><div class="lineH"></div><div class="lineV"></div></div>
			<div class="text"><?php echo $formText_AddEditProcessSteps_Output; ?></div>
		</div>
		<div class="clear"></div>
	</div> -->
	<?php } ?>
	<div class="clear"></div>
	<?php
}
?></div>


<script type="text/javascript">
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
$(".addPayment").on('click', function(e){
	e.preventDefault();
	var data = { };
    ajaxCall('edit_payment', data, function(obj) {
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
	.p_headerLine .btnStyle.addPayment {
		margin-left: 40px;
	}
</style>
