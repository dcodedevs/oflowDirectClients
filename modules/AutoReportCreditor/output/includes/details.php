<?php
// Create & check folders
// $f_check_sql = "SELECT * FROM customer ORDER BY name";
// require_once __DIR__ . '/filearchive_functions.php';
// check_filearchive_folder('Kunder', $f_check_sql, 'customer', 'name');
// create_subscription_folders();
$report_status = $_GET['report_status'] ? $_GET['report_status'] : 0;
$page = 1;
require_once __DIR__ . '/list_btn.php';

$sql = "SELECT * FROM accountinfo";
$result = $o_main->db->query($sql);
$v_accountinfo = $result ? $result->row_array(): array();

$s_sql = "SELECT * FROM autoreportcreditor WHERE id = ?";
$o_query = $o_main->db->query($s_sql, array($_GET['cid']));
$autoreportcreditor = $o_query ? $o_query->row_array() : array();

$s_sql = "SELECT * FROM autoreportcreditor_report WHERE id = ?";
$o_query = $o_main->db->query($s_sql, array($_GET['report_id']));
$report = $o_query ? $o_query->row_array() : array();
$list_filter = $_GET['list_filter'] ? $_GET['list_filter'] : 'open';
?>
<div id="p_container" class="p_container <?php echo $folderName; ?>">
	<div class="p_containerInner">
		<div class="p_content">
			<div class="p_pageContent">
				<?php
				if(!$autoreportcreditor && !$report) {
					echo $formText_MissingReport_output;
				} else {
					$sql_where = "";
					if($report_status == 1) {
						$sql_where = " AND IFNULL(autoreportcreditor_report.created, '0000-00-00') <> '0000-00-00'";
					} else if($report_status == 2) {
						$sql_where = " AND IFNULL(autoreportcreditor_report.created, '0000-00-00') = '0000-00-00'";
					}
					if($report['closed_report']){
						$sql = "SELECT autoreportcreditor_lines.*, autoreportcreditor_report.created as reported_to_creditor_date, CONCAT_WS(',', deb.name, deb.middlename, deb.lastname) as debitorName
						FROM autoreportcreditor_lines
						LEFT OUTER JOIN autoreportcreditor_report ON autoreportcreditor_report.id = autoreportcreditor_lines.case_closed_autoreport_id
						LEFT OUTER JOIN collecting_company_cases ccc ON ccc.id = autoreportcreditor_lines.case_id
						LEFT OUTER JOIN customer deb ON deb.id = ccc.debitor_id
						WHERE autoreportcreditor_lines.autoreportcreditor_id = ? AND autoreportcreditor_report.id = ?".$sql_where." ORDER BY debitorName ASC";
						$result = $o_main->db->query($sql, array($autoreportcreditor['id'], $report['id']));
						$autoreportcreditor_lines = $result ? $result->result_array(): array();

					} else {
						$sql = "SELECT autoreportcreditor_lines.*, autoreportcreditor_report.created as reported_to_creditor_date, CONCAT_WS(',', deb.name, deb.middlename, deb.lastname) as debitorName
						FROM autoreportcreditor_lines
						LEFT OUTER JOIN autoreportcreditor_report ON autoreportcreditor_report.id = autoreportcreditor_lines.autoreportcreditor_report_id
						LEFT OUTER JOIN collecting_company_cases ccc ON ccc.id = autoreportcreditor_lines.case_id
						LEFT OUTER JOIN customer deb ON deb.id = ccc.debitor_id
						WHERE autoreportcreditor_lines.autoreportcreditor_id = ? AND autoreportcreditor_report.id = ?".$sql_where." ORDER BY debitorName ASC";
						$result = $o_main->db->query($sql, array($autoreportcreditor['id'], $report['id']));
						$autoreportcreditor_lines = $result ? $result->result_array(): array();
					}
					?>
					<div class="filter_row">
						<div class="back_to_reports"><?php echo $formText_BackToReports_output;?></div>
					</div>
					<table class="table">
						<tr>
							<th><?php echo $formText_Created_output;?></th>
							<th><?php echo $formText_CaseId_output;?></th>
							<th><?php echo $formText_Bankaccount_output;?></th>
							<th><?php echo $formText_Kidnumber_output;?></th>
							<th><?php echo $formText_DebitorCustomerNr_output;?></th>
							<th><?php echo $formText_DebitorCustomerName_output;?></th>
							<th><?php echo $formText_InvoiceNumbers_output;?></th>
							<th><?php echo $formText_TotalOutstandingOflow_output;?></th>
							<th><?php echo $formText_ReportedToCreditorDate_output;?></th>
							<th></th>
						</tr>
						<?php foreach($autoreportcreditor_lines as $autoreportcreditor_line) { ?>
							<tr>
								<td><?php echo date("d.m.Y", strtotime($autoreportcreditor_line['created']));?></td>
								<td><?php echo $autoreportcreditor_line['case_id'];?></td>
								<td><?php echo $autoreportcreditor_line['bankaccount'];?></td>
								<td><?php echo $autoreportcreditor_line['kidnumber'];?></td>
								<td><?php echo $autoreportcreditor_line['debitor_customer_nr'];?></td>
								<td><?php echo $autoreportcreditor_line['debitorName'];?></td>
								<td><?php echo $autoreportcreditor_line['invoice_numbers'];?></td>
								<td><?php echo $autoreportcreditor_line['total_outstanding_oflow'];?></td>
								<td><?php if($autoreportcreditor_line['reported_to_creditor_date'] != "0000-00-00" && $autoreportcreditor_line['reported_to_creditor_date'] != "") echo date("d.m.Y", strtotime($autoreportcreditor_line['reported_to_creditor_date']));?></td>
								<td></th>
							</tr>
						<?php } ?>
					</table>
				<?php } ?>
			</div>
		</div>
	</div>
</div>

<?php $list_filter = $_GET['list_filter'] ? $_GET['list_filter'] : 'all'; ?>
<script type="text/javascript">
var out_popup;
var out_popup_options={
	follow: [true, false],
	modalClose: false,
	escClose: false,
	closeClass:'b-close',
	onOpen: function(){
		$(this).addClass('opened');
		//$(this).find('.b-close').on('click', function(){out_popup.close();});
	},
	onClose: function(){
		if($(this).is('.close-reload')) {
			var redirectUrl = $(this).data("redirect");
			if(redirectUrl !== undefined && redirectUrl != ""){
				document.location.href = redirectUrl;
			} else {
            	loadView("list");
            }
          // window.location.reload();
        }
		$(this).removeClass('opened');
	}
};


$(document).ready(function() {
	$(".reportStatusChange").change(function() {
		var data = { cid: "<?php echo $cid;?>",report_status: $(this).val()};
		loadView("details", data);
	})

	$(".back_to_reports").off("click").on("click", function(){
		var data = {  cid: "<?php echo $cid;?>", list_filter: "<?php echo $list_filter;?>" };
		loadView("reports", data);
	})
    var page = '<?php echo $page?>';
    // On customer row click
	$(document).off('click', '.output-click-helper').on('click', '.output-click-helper', function(e){
		if(e.target.nodeName == 'DIV'){
		 	fw_load_ajax($(this).data('href'),'',true);
			if($("body.alternative").length == 0) {
			 	if($(this).parents(".tinyScrollbar.col1")){
				 	var $scrollbar6 = $('.tinyScrollbar.col1');
				    $scrollbar6.tinyscrollbar();

				    var scrollbar6 = $scrollbar6.data("plugin_tinyscrollbar");
			        scrollbar6.update(0);
			    }
			}
		}
	});
	$(".reports").off("click").on("click", function(){
		var data = { cid: "<?php echo $cid;?>"};
		loadView("reports", data);
	})
    // Add new (old not fixed)
	$(".addNewButton").on('click', function(e){
		$.ajax({
			cache: false,
			type: 'POST',
			dataType: 'json',
			url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=edit_home";?>',
			data: { fwajax: 1, fw_nocss: 1, cid: 0 },
			success: function(obj){
				$('#popupeditboxcontent').html('');
				$('#popupeditboxcontent').html(obj.html);
				out_popup = $('#popupeditbox').bPopup(out_popup_options);
				$("#popupeditbox:not(.opened)").remove();
			}
		});
	});
});
</script>
<style>
.run_script {
	cursor: pointer;
	color: #46b2e2;
	margin-right: 10px;
}
.filter_row {
	padding: 5px 10px;
	margin-bottom: 10px;
}
.reportStatusChange {
	margin-left: 10px;
}
.create_report {
	float: right;
	cursor: pointer;
	color: #46b2e2;
}
.reports {
	float: right;
	cursor: pointer;
	color: #46b2e2;
	margin-right: 10px;
}
.back_to_reports {
	cursor: pointer;
	color: #46b2e2;
}
</style>
