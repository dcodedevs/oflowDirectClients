<div>
	<span class="view_not_zero_checksum"><?php echo $formText_ViewNotZeroChecksum_output." (".$checksumNotZeroCount.")";?></span>
	<span class="edit_accounting_close_date"><?php echo ('' != $v_collecting_system_settings['accounting_close_last_date'] ? $formText_AccountingCloseDate_Output.': '.date('d.m.Y', strtotime($v_collecting_system_settings['accounting_close_last_date'])):'');?> <span class="fas fa-cog"></span></span>
</div>
<style>
.view_not_zero_checksum {
	color: #46b2e2;
	cursor: pointer;
	margin-bottom: 10px;
}
.edit_accounting_close_date {
    float: right;
    cursor: pointer;
    color: #46b2e2;
}
</style>
<script type="text/javascript">
$(function(){
	$(".view_not_zero_checksum").off("click").on("click", function(e){
	    page = $(this).data("page");
	    e.preventDefault();
        var data = {
			search_filter: $('.searchFilter').val(),
			search_by: $(".searchBy").val(),
			type_filter: '<?php echo $type_filter?>',
			order_field: '<?php echo $order_field?>',
			order_direction:  '<?php echo $order_direction?>',
			checksum: 1
        };
        loadView("list", data);
	});
	$(".edit_accounting_close_date").on('click', function(e){
        e.preventDefault();
        var data = { };
        ajaxCall('edit_accounting_close_last_date', data, function(obj) {
            $('#popupeditboxcontent').html(obj.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    });
})
</script>
