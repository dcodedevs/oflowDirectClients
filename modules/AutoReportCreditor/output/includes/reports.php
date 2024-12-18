<?php
// Create & check folders
// $f_check_sql = "SELECT * FROM customer ORDER BY name";
// require_once __DIR__ . '/filearchive_functions.php';
// check_filearchive_folder('Kunder', $f_check_sql, 'customer', 'name');
// create_subscription_folders();
$report_status = $_GET['report_status'] ? $_GET['report_status'] : 0;
$list_filter = $_GET['list_filter'] ? $_GET['list_filter'] : 'open';
$page = 1;
require_once __DIR__ . '/list_btn.php';

$sql = "SELECT * FROM accountinfo";
$result = $o_main->db->query($sql);
$v_accountinfo = $result ? $result->row_array(): array();

$s_sql = "SELECT * FROM autoreportcreditor WHERE id = ?";
$o_query = $o_main->db->query($s_sql, array($_GET['cid']));
$autoreportcreditor = $o_query ? $o_query->row_array() : array();
?>
<div id="p_container" class="p_container <?php echo $folderName; ?>">
	<div class="p_containerInner">
		<div class="p_content">
			<div class="p_pageContent">
				<?php
				if(!$autoreportcreditor) {
					echo $formText_MissingReport_output;
				} else {
					if($list_filter == "open") {
						$s_sql = "SELECT * FROM autoreportcreditor_report WHERE autoreportcreditor_id = ? AND IFNULL(closed_report, 0) = 0";
						$o_query = $o_main->db->query($s_sql, array($autoreportcreditor['id']));
						$autoreportcreditor_reports = $o_query ? $o_query->result_array() : array();
					} else if($list_filter == "closed") {
						$s_sql = "SELECT * FROM autoreportcreditor_report WHERE autoreportcreditor_id = ? AND IFNULL(closed_report, 0) = 1";
						$o_query = $o_main->db->query($s_sql, array($autoreportcreditor['id']));
						$autoreportcreditor_reports = $o_query ? $o_query->result_array() : array();
					}

					$s_sql = "SELECT * FROM autoreportcreditor_report WHERE autoreportcreditor_id = ? AND IFNULL(closed_report, 0) = 0";
					$o_query = $o_main->db->query($s_sql, array($autoreportcreditor['id']));
					$case_open_count = $o_query ? $o_query->num_rows() : 0;

					$s_sql = "SELECT * FROM autoreportcreditor_report WHERE autoreportcreditor_id = ? AND IFNULL(closed_report, 0) = 1";
					$o_query = $o_main->db->query($s_sql, array($autoreportcreditor['id']));
					$case_closed_count = $o_query ? $o_query->num_rows() : 0;


					?>
					<div class="filter_row">
						<div class="back_to_details"><?php echo $formText_BackToList_output;?></div>
						<div class="create_report"><?php echo $formText_CreateReport_output;?></div>
						<div class="create_closed_report"><?php echo $formText_CreateClosedReport_output;?></div>
						<div class="clear"></div>
					</div>
					<div class="output-filter">
						<ul>
							<li class="item<?php echo ($list_filter == 'open' ? ' active':'');?>">
								<a class="topFilterlink" data-listfilter="open" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=reports&cid=".$cid."&list_filter=open"; ?>">
									<span class="link_wrapper">
										<span class="count"><?php echo $case_open_count; ?></span>
										<?php echo $formText_CaseOpenReports_output;?>
									</span>
								</a>
							</li>
							<li class="item<?php echo ($list_filter == 'closed' ? ' active':'');?>">
								<a class="topFilterlink" data-listfilter="closed" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=reports&cid=".$cid."&list_filter=closed"; ?>">
									<span class="link_wrapper">
										<span class="count"><?php echo $case_closed_count; ?></span>
										<?php echo $formText_CaseClosedReports_output;?>
									</span>
								</a>
							</li>
						</ul>
					</div>
					<form class="output-form-export"  action="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=".$_GET['folderfile']."&folder=".$_GET['folder']."&inc_obj=ajax&inc_act=exportExcel";?>" method="post" target="_blank">
						<input type="hidden" name="fwajax" value="1">
						<input type="hidden" name="fw_nocss" value="1">
						<input type="hidden" class="reportId" name="reportId" autocomplete="off"/>
						<table class="table">
							<tr>
								<th><?php echo $formText_Created_output;?></th>
								<th><?php echo $formText_Lines_output;?></th>
								<th></th>
							</tr>
							<?php foreach($autoreportcreditor_reports as $autoreportcreditor_report) {
								$detailPageLink = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&list_filter=".$list_filter."&cid=".$autoreportcreditor['id']."&report_id=".$autoreportcreditor_report['id'];
								
								if($list_filter == "open") {
									$sql = "SELECT autoreportcreditor_lines.*, autoreportcreditor_report.created as reported_to_creditor_date, CONCAT_WS(',', deb.name, deb.middlename, deb.lastname) as debitorName
									FROM autoreportcreditor_lines
									JOIN autoreportcreditor_report ON autoreportcreditor_report.id = autoreportcreditor_lines.autoreportcreditor_report_id
									LEFT OUTER JOIN collecting_company_cases ccc ON ccc.id = autoreportcreditor_lines.case_id
									LEFT OUTER JOIN customer deb ON deb.id = ccc.debitor_id
									WHERE autoreportcreditor_lines.autoreportcreditor_id = ? AND autoreportcreditor_report.id = ?".$sql_where;
									$result = $o_main->db->query($sql, array($autoreportcreditor['id'], $autoreportcreditor_report['id']));
									$autoreportcreditor_lines = $result ? $result->result_array(): array();
								} else if($list_filter == "closed") {
									$sql = "SELECT autoreportcreditor_lines.*, autoreportcreditor_report.created as reported_to_creditor_date, CONCAT_WS(',', deb.name, deb.middlename, deb.lastname) as debitorName
									FROM autoreportcreditor_lines
									JOIN autoreportcreditor_report ON autoreportcreditor_report.id = autoreportcreditor_lines.case_closed_autoreport_id
									LEFT OUTER JOIN collecting_company_cases ccc ON ccc.id = autoreportcreditor_lines.case_id
									LEFT OUTER JOIN customer deb ON deb.id = ccc.debitor_id
									WHERE autoreportcreditor_lines.autoreportcreditor_id = ? AND autoreportcreditor_report.id = ?".$sql_where;
									$result = $o_main->db->query($sql, array($autoreportcreditor['id'], $autoreportcreditor_report['id']));
									$autoreportcreditor_lines = $result ? $result->result_array(): array();
								}
								?>
								<tr>
									<td><?php echo date("d.m.Y H:i:s", strtotime($autoreportcreditor_report['created'])); ?></td>
									<td><?php echo count($autoreportcreditor_lines);?></td>
									<td>

										<a class="show_details optimize" href="<?php echo $detailPageLink;?>"><?php echo $formText_DetailPage_output;?></a>
										<a class="export" data-id="<?php echo $autoreportcreditor_report['id'];?>"><?php echo $formText_Export_output; ?></a>
									</td>
								</tr>
							<?php } ?>
						</table>
					</form>
				<?php } ?>
			</div>
		</div>
	</div>
</div>

<?php $list_filter = $_GET['list_filter'] ? $_GET['list_filter'] : 'all'; ?>
<script type="text/javascript" src="../modules/<?php echo $module?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
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
				var data = { };
            	loadView("list", data);
            }
          // window.location.reload();
        }
		$(this).removeClass('opened');
	}
};


$(document).ready(function() {
	$("form.output-form-export").validate({
		submitHandler: function(form) {
			form.submit();
		}
	});
	$('.export').on('click', function(e) {
		$("form.output-form-export .reportId").val($(this).data("id"));
		$("form.output-form-export").submit();
    });
	$(".reportStatusChange").change(function() {
		var data = { cid: "<?php echo $cid;?>",report_status: $(this).val()};
		loadView("details", data);
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
	$(".create_report").off("click").on("click", function(){
		var data = { cid: "<?php echo $cid;?>"};
		ajaxCall('create_report', data, function(obj) {
			var data = { cid: "<?php echo $cid;?>",report_status: $(".reportStatusChange").val(), list_filter: "<?php echo $list_filter;?>"};
			loadView("reports", data);
		});
	})

	$(".create_closed_report").off("click").on("click", function(){
		var data = { cid: "<?php echo $cid;?>", closed: 1};
		ajaxCall('create_report', data, function(obj) {
			var data = { cid: "<?php echo $cid;?>",report_status: $(".reportStatusChange").val(), list_filter: "<?php echo $list_filter;?>"};
			loadView("reports", data);
		});
	})
	$(".back_to_details").off("click").on("click", function(){
		var data = { };
		loadView("list", data);
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
.create_closed_report {
	float: right;
	cursor: pointer;
	color: #46b2e2;
	margin-right: 10px;
}
.back_to_details {
	float: left;
	cursor: pointer;
	color: #46b2e2;
}
.show_details {
	cursor: pointer;
	color: #46b2e2;
	margin-right: 10px;
}
</style>
