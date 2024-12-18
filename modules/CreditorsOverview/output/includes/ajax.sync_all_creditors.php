<?php
include(__DIR__."/import_scripts/sync_all_creditors.php");

$s_sql = "SELECT creditor.* FROM creditor WHERE integration_module <> '' AND IFNULL(onboarding_incomplete, 0) = 0 AND sync_from_accounting = 1 AND DATE(sync_started_time) < '".date("Y-m-d")."' ORDER BY id";
$o_query = $o_main->db->query($s_sql);
$leftCreditors = ($o_query ? $o_query->result_array() : array());

if(count($leftCreditors) > 0) {
	echo count($leftCreditors)." ".$formText_CreditorsLeftToBeSynced_output."</br><div class='sync_creditors'>".$formText_SyncMoreCustomers_output."</div>";
	?>
	<style>
	.sync_creditors {
        color: #46b2e2;
		cursor: pointer;
		margin-top: 20px;
	}
	</style>
	<script type="text/javascript">
	$(function(){
		$(".sync_creditors").off("click").on("click", function(){
			var data = {}
			ajaxCall('sync_all_creditors', data, function(json) {
				$('#popupeditboxcontent').html('');
				$('#popupeditboxcontent').html(json.html);
				out_popup = $('#popupeditbox').bPopup(out_popup_options);
				$("#popupeditbox:not(.opened)").remove();
			});
		})
	})
	</script>
	<?php
} else {
	echo $formText_NoCreditorsThatDidNotSyncDuringAutoSync_output;
}
?>
