<?php
$o_query = $o_main->db->get('ownercompany_accountconfig');
$ownercompany_accountconfig = $o_query ? $o_query->row_array() : array();
?>
<div class="p_headerLine">
</div>


<script type="text/javascript">
	$(function(){
		$(".view_objection").off("click").on("click", function(){
			var data = {};
			loadView("objection_report", data);
		})
	})
</script>
<style>
	.p_headerLine .btnStyle.addEditCustomerGroup {
		margin-left: 40px;
	}
	.p_headerLine .btnStyle.importPayments {
		margin-left: 40px;
	}
</style>
