<?php
//error_reporting(E_ALL | E_STRICT); ini_set("display_errors", 1);
$current_page = $_GET['current_page'] ?? 1;
require_once __DIR__ . '/list_btn.php';
$list_filter = isset($_GET['list_filter']) ? $_GET['list_filter'] : 'cases_will_expire';

$creditor_id = $_GET['creditor_id'] ?? 0;
$collecting_company_case_id = $_GET['collecting_company_case_id'] ?? 0;

$per_page = 200;
$offset = ($current_page-1)*$per_page;
$pager = " LIMIT ".$per_page." OFFSET ".$offset;
$s_sql_where = "";
if($list_filter == 'cases_will_expire') {
	$s_sql_where .= " AND IFNULL(approved_to_expire, 0) = 0";
} else if($list_filter == 'approved_to_expire') {
	$s_sql_where .= " AND IFNULL(approved_to_expire, 0) = 1";
}
$s_sql = "SELECT 
ccc.*,
cred.companyname creditorName,
concat_ws(' ', debitor.name, debitor.middlename, debitor.lastname) as debitorName
FROM collecting_company_cases ccc
JOIN creditor cred ON cred.id = ccc.creditor_id
JOIN customer debitor ON debitor.id = ccc.debitor_id
WHERE (ccc.case_closed_date = '0000-00-00' OR ccc.case_closed_date IS NULL) AND ccc.content_status < 2
".$s_sql_where."
ORDER BY ccc.case_limitation_date ASC";
$o_query = $o_main->db->query($s_sql.$pager);
$active_collecting_company_cases = ($o_query ? $o_query->result_array() : array());
$o_query = $o_main->db->query($s_sql);
$company_case_count = ($o_query ? $o_query->num_rows() : 0);

$totalPages = ceil($company_case_count/$per_page);

$cases_will_expire_count = 0;
$approved_to_expire_count = 0;

$s_sql = "SELECT ccc.id FROM collecting_company_cases ccc
WHERE (ccc.case_closed_date = '0000-00-00' OR ccc.case_closed_date IS NULL) AND IFNULL(approved_to_expire, 0) = 0  AND ccc.content_status < 2";
$o_query = $o_main->db->query($s_sql);
$cases_will_expire_count = ($o_query ? $o_query->num_rows() : 0);

$s_sql = "SELECT ccc.id FROM collecting_company_cases ccc
WHERE (ccc.case_closed_date = '0000-00-00' OR ccc.case_closed_date IS NULL) AND IFNULL(approved_to_expire, 0) = 1  AND ccc.content_status < 2";
$o_query = $o_main->db->query($s_sql);
$approved_to_expire_count = ($o_query ? $o_query->num_rows() : 0);

?>
<div id="p_container" class="p_container <?php echo $folderName; ?>">
	<div class="p_containerInner">
		<div class="p_content">
			<div class="p_pageContent">
				<div class="output-filter">
					<ul>
						<li class="item<?php echo ($list_filter == 'cases_will_expire' ? ' active':'');?>">
							<a class="topFilterlink" data-listfilter="cases_will_expire" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&list_filter=cases_will_expire"; ?>">
								<span class="link_wrapper">
									<span class="count"><?php echo $cases_will_expire_count; ?></span>
									<?php echo $formText_CasesWillExpire_output;?>
								</span>
							</a>
						</li>
						<li class="item<?php echo ($list_filter == 'approved_to_expire' ? ' active':'');?>">
							<a class="topFilterlink" data-listfilter="approved_to_expire" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&list_filter=approved_to_expire"; ?>">
								<span class="link_wrapper">
									<span class="count"><?php echo $approved_to_expire_count; ?></span>
									<?php echo $formText_ApprovedToExpire_output;?>
								</span>
							</a>
						</li>
					</ul>
				</div>
				<div class="creditor_list">
					<table class="table">
						<tr>
							<td><?php echo $formText_CaseLimitationDate_output;?></td>
							<td><?php echo $formText_CaseId_output;?></td>
							<td><?php echo $formText_CreditorName_output;?></td>
							<td><?php echo $formText_DebitorName_output;?></td>
						</tr>
						<?php 
						foreach($active_collecting_company_cases as $active_collecting_company_case) {
							$s_edit_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=CollectingCompanyCases&folderfile=output&folder=output&inc_obj=details&cid=".$active_collecting_company_case['id'];

							?>
							<tr class="output-click-helper" data-href="<?php echo $s_edit_link;?>">
								<td><?php if($active_collecting_company_case['case_limitation_date'] != "0000-00-00" && $active_collecting_company_case['case_limitation_date'] != "") echo date("d.m.Y", strtotime($active_collecting_company_case['case_limitation_date']));?></td>
								<td><?php echo $active_collecting_company_case['id'];?></td>
								<td><?php echo $active_collecting_company_case['creditorName'];?></td>
								<td><?php echo $active_collecting_company_case['debitorName'];?></td>
							</tr>
							<?php
						}
						?>
					</table>
					<?php if($totalPages > 1) {
						$currentPage = $current_page;
						$pages = array();
						array_push($pages, 1);
						if(!in_array($currentPage, $pages)){
							array_push($pages, $currentPage);
						}
						if(!in_array($totalPages, $pages)){
							array_push($pages, $totalPages);
						}
						for ($y = 10; $y <= $totalPages; $y+=10){
							if(!in_array($y, $pages)){
								array_push($pages, $y);
							}
						}
						for($x = 1; $x <= 5;$x++){
							$prevPage = $current_page - $x;
							$nextPage = $current_page + $x;
							if($prevPage > 0){
								if(!in_array($prevPage, $pages)){
									array_push($pages, $prevPage);
								}
							}
							if($nextPage <= $totalPages){
								if(!in_array($nextPage, $pages)){
									array_push($pages, $nextPage);
								}
							}
						}
						asort($pages);
						?>
						<?php foreach($pages as $page) {?>
							<a href="#" data-page="<?php echo $page?>" class="page-link<?php if($current_page == $page) echo ' active';?>"><?php echo $page;?></a>
						<?php } ?>
					<?php } ?>
				</div>
				<div class="clear"></div>
			</div>
		</div>
	</div>
</div>
<style>
	.creditor_selected_block {
		padding: 5px;
	}
	.creditor_selecting_wrapper {
		width: 25%;
		float: left;
	}
	.creditor_selecting_block {
		height: 600px;
		overflow-y: scroll;
	}
	.creditor_message_wrapper {
		width: 75%;
		float: right;
	}
	.creditor_chat_wrapper {
		margin-left: 15px;
		margin-right: 15px;
	}
	.creditor_chat_wrapper .creditor_chat {
		width: 100%;
	}
	.creditor_selection {
		padding: 5px;
		background: #fff;
		border: 1px solid #cecece;
		cursor: pointer;
	}
	.creditor_selection.active {
		background: #46b2e2;
		color: #fff;
	}
	.creditor_message_wrapper .send_message {
		display: inline-block;
		border: none;
		border-radius: 4px;
		padding: 5px 10px;
		color: #FFF;
		background: #124171;
		outline: none;
		margin-top: 10px;
		cursor:pointer;
	}
	.creditor_chat_messages {
		margin-top: 10px;
		padding: 5px 15px;
	}
	.creditor_chat_messages .chat_message {
		display: block;
	    margin-bottom: 10px;	
		margin-top: 5px;
		float: left;
		width: 65%;
		text-align: left;		
	}
	.creditor_chat_messages .chat_message_info {
		border: 1px solid #ddd;
	    border-radius: 5px;
		word-break: break-all;
	    padding: 5px 7px;
		background: #6edaed;
	}
	.creditor_chat_messages .chat_message.from_oflow {
	    float: right;
		text-align: right;
	}
	.creditor_chat_messages .chat_message.from_oflow .chat_message_info{
	    background: #f0f0f0;
	}
	.creditor_chat_messages .message_info {
		color: #bbbbbb;
	}
	.show_case {
		margin-left: 10px;
	}
	.screenshot-view {
	    display: inline-block;
	    vertical-align: top;
	    width: 50px;
	    margin-right: 10px;
	    border: 1px solid #cecece;
	    cursor: pointer;
	}
	.screenshot-view img {
	    width: 100%;
	}
	.unread_indicator {
		background-color: red;
		width: 6px;
		height: 6px;
		display: inline-block;
		margin-right: 5px;
		border-radius: 10px;
		vertical-align: middle;;
	}
	.page-link.active {
		text-decoration: underline;
	}
</style>

<script type="text/javascript">
var out_popup;
var out_popup_options={
	follow: [true, true],
	followSpeed: 300,
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
                var data = {
					list_filter: '<?php echo $list_filter;?>'
                };
				loadView("list", data);
            }
          // window.location.reload();
        }
		$(this).removeClass('opened');
	}
};

function callBackOnUploadAll(data) {
	// updatePreview();
    $('.creditor_message_wrapper .send_message').val('<?php echo $formText_Send; ?>').prop('disabled',false);

};
function callbackOnStart(data) {
    $('.creditor_message_wrapper .send_message').val('<?php echo $formText_UploadInProgress_output;?>...').prop('disabled',true);
};
function callbackOnDelete(data){
	// updatePreview();
}
$(function(){

	$(document).off('click', '.output-click-helper').on('click', '.output-click-helper', function(e){
		if(e.target.nodeName == 'TD') window.open($(this).data('href'), '_blank');//fw_load_ajax($(this).data('href'),'',true);
	});
	$(".creditor_selection").off("click").on("click", function(){
		var data = {
			creditor_id: $(this).data("creditor-id"),
			collecting_company_case_id: $(this).data('collecting_company_case_id-id')
		}
		loadView("list", data);
	})
	$(".fancybox").fancybox();
	$(".send_message").off("click").on("click", function(){
        var data = {
			creditor_id: '<?php echo $selected_creditor['id']?>',
			collecting_company_case_id: '<?php echo $selected_creditor['collecting_company_case_id']?>',
			message: $(".creditor_chat").val()
        };
		var formdata = $(".messageForm").serializeArray();
		var data = {};
		$(formdata ).each(function(index, obj){
			if(data[obj.name] != undefined) {
				if(Array.isArray(data[obj.name])){
					data[obj.name].push(obj.value);
				} else {
					data[obj.name] = [data[obj.name], obj.value];
				}
			} else {
				data[obj.name] = obj.value;
			}
		});

		$.ajax({
			url: $(".messageForm").attr("action"),
			cache: false,
			type: "POST",
			dataType: "json",
			data: data,
			success: function (data) {
				fw_loading_end();					
				var data = {
					creditor_id: '<?php echo $selected_creditor['id'];?>',
					collecting_company_case_id: '<?php echo $selected_creditor['collecting_company_case_id'];?>',
				};
				loadView("list", data);
			}
		}).fail(function() {
			$("#popup-validate-message").html("<?php echo $formText_ErrorOccuredSavingContent_Output;?>", true);
			$("#popup-validate-message").show();
			$('#popupeditbox').css('height', $('#popupeditboxcontent').height());
			fw_loading_end();
		});

	})
	$(".marked_as_handled").off("click").on("click", function(e){
		e.preventDefault();
		var data = {
			creditor_id: $(this).data("id")
		};
		ajaxCall({module_file: 'mark_as_handled', module_folder: 'output'}, data, function(json) {
			
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();	
		});
	})
	$(".page-link").on('click', function(e) {
		page = $(this).data("page");
		e.preventDefault();
		var data = {
			current_page: page
		}
		loadView("list", data);
	});
})
</script>