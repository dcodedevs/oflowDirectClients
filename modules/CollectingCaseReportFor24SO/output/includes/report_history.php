<?php
$page = 1;
require_once __DIR__ . '/list_btn.php';
?>
<div id="p_container" class="p_container <?php echo $folderName; ?>">
	<div class="p_containerInner">
		<div class="p_content">
			<div class="p_pageContent">
				<div class="reportListTitle"><?php echo $formText_ReportList_output;?></div>
				<?php
				$sql = "SELECT collecting_cases_report_24so.*, CONCAT_WS(' ', c.name, c.middlename, c.lastname) as creditorName FROM collecting_cases_report_24so
				LEFT OUTER JOIN creditor ON creditor.id = collecting_cases_report_24so.creditor_id
				LEFT OUTER JOIN customer c ON c.id = creditor.customer_id
				ORDER BY date DESC";
				$o_query = $o_main->db->query($sql);
				$reports = $o_query ? $o_query->result_array() : array();

				foreach($reports as $report) {

					?>
					<div class="report_block">
						<?php echo $report['creditorName'];?>
						<?php echo date("d.m.Y", strtotime($report['date']));?>
						<span class="delete_report glyphicon glyphicon-trash" data-id="<?php echo $report['id'];?>"></span>
					</div>
					<?php
				}
				?>

			</div>
		</div>
	</div>
</div>
<style>
.p_pageContent {
	background: #fff;
}
.delete_report {
	cursor: pointer;
	color: #46b2e2;
	float: right;
}
.report_block {
	padding: 5px;
}
.reportListTitle {
	padding: 5px;
	font-size: 16px;
}
</style>

<?php $list_filter = $_GET['list_filter'] ? $_GET['list_filter'] : 'all'; ?>
<script type="text/javascript">
var out_popup;
var out_popup_options={
	follow: [true, true],
	followSpeed: 0,
	fadeSpeed: 0,
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
            	loadView("report_history");
            }
          // window.location.reload();
        }
		$(this).removeClass('opened');
	}
};


$(document).ready(function() {

    var page = '<?php echo $page?>';
    // On customer row click
	$(document).off('click', '.output-click-helper').on('click', '.output-click-helper', function(e){
		if(e.target.nodeName == 'DIV'){
		 	fw_load_ajax($(this).data('href'),'',true);
		}
	});

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
	$(".delete_report").off("click").on("click", function(){

		var data = {
			report_id: $(this).data("id")
		}
		bootbox.confirm({
            message:"<?php echo $formText_DeleteReport_output?>",
            buttons:{confirm:{label:"<?php echo $formText_Yes_Output;?>"},cancel:{label:"<?php echo $formText_No_Output;?>"}},
            callback: function(result){
                if(result)
                {
					ajaxCall("delete_report", data, function(json) {

		            	loadView("report_history");
					});
				}
			}
		})
	})
});
</script>
