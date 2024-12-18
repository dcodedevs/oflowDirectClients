<?php
$o_query = $o_main->db->get('ownercompany_accountconfig');
$ownercompany_accountconfig = $o_query ? $o_query->row_array() : array();
?>
<div class="p_headerLine"><?php
if($moduleAccesslevel > 10)
{
	if($_GET['inc_obj'] != 'import_invoices') {
	?>
		<div class="backToCreditorList btnStyle">
			<div class="plusTextBox active">
				<div class="text"><?php echo $formText_BackToCreditorList_Output; ?></div>
			</div>
			<div class="clear"></div>
		</div>
		<div class="viewReportPage btnStyle">
			<div class="plusTextBox active">
				<div class="text"><?php echo $formText_ViewReportPage_Output; ?></div>
			</div>
			<div class="clear"></div>
		</div>
		<!-- <div class="addNewArticleBtn btnStyle">
			<div class="plusTextBox active">
				<div class="plusBox addres"><div class="lineH"></div><div class="lineV"></div></div>
				<div class="text"><?php echo $formText_ImportInvoices_Output; ?></div>
			</div>
			<div class="clear"></div>
		</div>
		<div class="importPaymentsBtn btnStyle">
			<div class="plusTextBox active">
				<div class="plusBox addres"><div class="lineH"></div><div class="lineV"></div></div>
				<div class="text"><?php echo $formText_ImportPayments_Output; ?></div>
			</div>
			<div class="clear"></div>
		</div> -->
	<?php } else { ?>
		<div class="backToList btnStyle">
			<div class="plusTextBox active">
				<div class="text"><?php echo $formText_BackToList_Output; ?></div>
			</div>
			<div class="clear"></div>
		</div>
	<?php } ?>
	<div class="clear"></div>
	<?php
}
?></div>


<script type="text/javascript">

$(".addEditProcessSteps").on('click', function(e){
	e.preventDefault();
	var data = { };
    ajaxCall('edit_process_steps', data, function(obj) {
        $('#popupeditboxcontent').html('');
        $('#popupeditboxcontent').html(obj.html);
        out_popup = $('#popupeditbox').bPopup(out_popup_options);
        $("#popupeditbox:not(.opened)").remove();
    });
});

$(".backToCreditorList").off('click').on('click', function(e){
    e.preventDefault();
    var data = {
    };

    loadView({module_file:'list', module_folder: 'output_creditor'}, data);
});
$(".backToList").on('click', function(e){
    e.preventDefault();
    var data = {
    };

    loadView("list", data);
});
$(".addNewArticleBtn").on('click', function(e){
    e.preventDefault();
    var data = {
    };

    loadView("import_invoices", data);
});
$(".importPaymentsBtn").on('click', function(e){
    e.preventDefault();
    var data = {
    };

    loadView("import_payments", data);
});
$(".viewReportPage").off("click").on("click", function(e){
	e.preventDefault();
    var data = {
    };

    loadView("creditors_report", data);
})
</script>
<style>
	.p_headerLine .btnStyle.addEditCustomerGroup {
		margin-left: 40px;
	}
	.p_headerLine .btnStyle.importPaymentsBtn {
		margin-left: 40px;
	}
	.p_headerLine .btnStyle.addPriceList {
		margin-left: 20px;
	}
</style>
