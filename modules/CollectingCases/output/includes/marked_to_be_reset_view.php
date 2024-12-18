<?php

$list_filter = $_SESSION['list_filter'] ? ($_SESSION['list_filter']) : '1';
$mainlist_filter = $_SESSION['mainlist_filter'] ? ($_SESSION['mainlist_filter']) : 'reminderLevel';
$responsibleperson_filter = $_SESSION['responsibleperson_filter'] ? ($_SESSION['responsibleperson_filter']) : '';
$list_filter_main = $_SESSION['list_filter_main'] ? ($_SESSION['list_filter_main']) : '';
$search_filter = $_SESSION['search_filter'] ? ($_SESSION['search_filter']) : '';
$casetype_filter = $_SESSION['casetype_filter'] ? $_SESSION['casetype_filter'] : '';
$search_by = $_SESSION['search_by'] ? ($_SESSION['search_by']) : 1;
$s_list_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=list&list_filter=".$list_filter."&search_filter=".$search_filter."&mainlist_filter=".$mainlist_filter;

?>
<div id="p_container" class="p_container <?php echo $folderName; ?>">
	<div class="p_containerInner">
		<div class="p_content">
			<a href="<?php echo $s_list_link?>" class="output-click-helper optimize back-to-list" style="display: block; margin-bottom:10px;"><?php echo $formText_BackToList_outpup;?></a>

			<div class="p_pageContent">
				<table class="table">
					<tr>
						<th><?php echo $formText_collectingCase_output;?></th>
					</tr>
                    <?php 
                    $sql = "SELECT * FROM collecting_cases WHERE marked_to_be_reset = 1";
                    $o_query = $o_main->db->query($sql);
                    $collecting_cases = $o_query ? $o_query->result_array() : array();
                    foreach($collecting_cases as $collecting_case) {
                        $s_detail_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$collecting_case['id'];
                        ?>
                        <tr>
                            <td><a href="<?php echo $s_detail_link?>"><?php echo $collecting_case['id'];?></a></td>
                        </tr>
                        <?php
                    }
                    ?>
				</table>
			</div>
		</div>
		<div class="clear"></div>
	</div>
</div>
<style>
	.p_pageContent {
		background: #fff;
	}
	.viewReminderLevel {
		cursor: pointer;
		color: #46b2e2;
	}
	.viewCollectingLevel {
		cursor: pointer;
		color: #46b2e2;
	}
	.processCollectingLevel {
		padding: 5px 10px;
		cursor: pointer;
		background-color: #194273;
		color: #FFF;
		padding: 6px 15px;
		border-radius: 3px;
		border: 1px solid transparent;
		float: right;
		margin-left: 15px;
		margin-top: 15px;
	}
	.processReminderLevel {
		padding: 5px 10px;
		cursor: pointer;
		background-color: #194273;
		color: #FFF;
		padding: 6px 15px;
		border-radius: 3px;
		border: 1px solid transparent;
		float: right;
		margin-top: 15px;
		margin-left: 15px;
	}
</style>
<script type="text/javascript">
var out_popup;
var out_popup_options={
	follow: [true, true],
	followSpeed:0,
	fadeSpeed: 0,
	modalClose: false,
	escClose: false,
	closeClass:'b-close',
	onOpen: function(){
		$(this).addClass('opened');
	},
	onClose: function(){
		$(this).removeClass('opened');
		if($(this).is('.close-reload')) {
			loadView("process_cases_view");
		}
	}
};
	$(function(){
		$(".processCollectingLevel").off("click").on("click", function(e){
			e.preventDefault();
			var self = $(this);
			var data = {
				action: "processCollecting"
			};
			bootbox.confirm('<?php echo $formText_LaunchingCollectingProcessOfAllCreditor_output; ?>', function(result) {
				if (result) {
					ajaxCall('process_cases', data, function(json) {
						if(json.html != ""){
							$('#popupeditboxcontent').html('');
							$('#popupeditboxcontent').html(json.html);
							out_popup = $('#popupeditbox').bPopup(out_popup_options);
							$("#popupeditbox:not(.opened)").remove();
							out_popup.addClass("close-reload");
						} else {
							loadView("process_cases_view");
						}
					});
				}
			});
		})
		$(".processReminderLevel").off("click").on("click", function(e){
			e.preventDefault();
			var self = $(this);
			var data = {
				action: "processReminder"
			};
			bootbox.confirm('<?php echo $formText_LaunchingReminderProcessOfAllCreditor_output; ?>', function(result) {
				if (result) {
					ajaxCall('process_cases', data, function(json) {
						if(json.html != ""){
							$('#popupeditboxcontent').html('');
							$('#popupeditboxcontent').html(json.html);
							out_popup = $('#popupeditbox').bPopup(out_popup_options);
							$("#popupeditbox:not(.opened)").remove();
							out_popup.addClass("close-reload");
						} else {
							loadView("process_cases_view");
						}
					});
				}
			});
		})

		$(".viewReminderLevel").off("click").on("click", function(e){
			e.preventDefault();
			var data = {
				creditor_id: $(this).data('creditor-id'),
				level: "reminderLevel"
			};
			ajaxCall('view_ready_collecting_cases', data, function(json) {
				$('#popupeditboxcontent').html('');
				$('#popupeditboxcontent').html(json.html);
				out_popup = $('#popupeditbox').bPopup(out_popup_options);
				$("#popupeditbox:not(.opened)").remove();
			});
		})
		$(".viewCollectingLevel").off("click").on("click", function(e){
			e.preventDefault();
			var data = {
				creditor_id: $(this).data('creditor-id'),
				level: "collectingLevel"
			};
			ajaxCall('view_ready_collecting_cases', data, function(json) {
				$('#popupeditboxcontent').html('');
				$('#popupeditboxcontent').html(json.html);
				out_popup = $('#popupeditbox').bPopup(out_popup_options);
				$("#popupeditbox:not(.opened)").remove();
			});
		})
	})
</script>
