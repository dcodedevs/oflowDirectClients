<?php
$cid = isset($_GET['cid']) ? $_GET['cid'] : 0;

$sql = "SELECT * FROM creditor WHERE id = ?";
$o_query = $o_main->db->query($sql, array($cid));
$creditor = $o_query ? $o_query->row_array() : array();
if($creditor){
	$s_list_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=creditor_list&cid=".$cid;

?>
<div id="p_container" class="p_container <?php echo $folderName; ?>">
	<div class="p_containerInner">
		<div class="p_content">
			<div class="p_pageContent">
				<a href="<?php echo $s_list_link?>" class="output-click-helper optimize back-to-list" style="display: block; margin-bottom:10px; float: left;"><?php echo $formText_BackToCreditor_outpup;?></a>
			</div>
			<?php

			$s_sql = "SELECT * FROM collecting_cases_report_24so WHERE date >= ? AND creditor_id = ? GROUP BY YEAR(date), MONTH(date) ORDER BY date DESC";
			$o_query = $o_main->db->query($s_sql, array(date("Y-m-d", strtotime("-6 months")), $creditor['id']));
			$report_dates = $o_query ? $o_query->result_array() : array();

			?>
			<table class="table billing_date_table">
				<tr>
					<th><?php echo $formText_Date_output;?></th>
					<th></th>
				</tr>
				<?php foreach($report_dates as $report_date) {
					$s_edit_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=billing_info_detail&cid=".$creditor['id']."&month=".$report_date['date'];
					?>
					<tr class="output-click-helper" data-href="<?php echo $s_edit_link;?>" >
						<td><?php echo date("m.Y", strtotime($report_date['date']));?></td>
						<td></td>
					</tr>
				<?php } ?>
			</table>
		</div>
	</div>
</div>
<style>
.billing_date_table {
	background: #fff;
}
</style>
<script type="text/javascript">
	$(function() {
		$(document).off('click', '.output-click-helper').on('click', '.output-click-helper', function(e){
			if(e.target.nodeName == 'TD') fw_load_ajax($(this).data('href'),'',true);
		});
	});
</script>
<?php } ?>
