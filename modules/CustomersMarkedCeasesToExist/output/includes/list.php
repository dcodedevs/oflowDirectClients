<?php
//error_reporting(E_ALL | E_STRICT); ini_set("display_errors", 1);
require_once __DIR__ . '/list_btn.php';
$list_filter = isset($_GET['list_filter']) ? $_GET['list_filter'] : 'not_processed';
$current_page = isset($_GET['current_page']) ? $_GET['current_page'] : 1;

$creditor_id = $_GET['creditor_id'] ?? 0;
$collecting_company_case_id = $_GET['collecting_company_case_id'] ?? 0;
$s_sql = "SELECT creditor.id, creditor.companyname, cccc.collecting_company_case_id FROM creditor
JOIN creditor_collecting_company_chat cccc ON cccc.creditor_id = creditor.id
WHERE creditor.id = ? AND cccc.collecting_company_case_id = ?";
$o_query = $o_main->db->query($s_sql, array($creditor_id, $collecting_company_case_id));
$selected_creditor = ($o_query ? $o_query->row_array() : array());

$fwaFileuploadConfigs = array(
	array (
	  'module_folder' => 'CreditorChat', // module id in which this block is used
	  'id' => 'articleimageeuploadpopup',
	  'upload_type'=>'file',
	  'content_table' => 'creditor_collecting_company_chat',
	  'content_field' => 'files',
	  'content_id' => $cid,
	  'content_module_id' => $moduleID, // id of module
	  'dropZone' => 'block',
	  'callbackAll' => 'callBackOnUploadAll',
	  'callbackStart' => 'callbackOnStart',
	  'callbackDelete' => 'callbackOnDelete'
	),
);
$not_processed_count = 0;
$processed_count = 0;

$s_sql = "SELECT customer.id, customer.name FROM customer
WHERE customer.customer_marked_ceases_to_exist_date != '0000-00-00' AND IFNULL(customer_ceases_to_exist_handled, 0) = 0";
$o_query = $o_main->db->query($s_sql);
$not_processed_count = ($o_query ? $o_query->num_rows() : 0);

$s_sql = "SELECT customer.id, customer.name FROM customer
WHERE customer.customer_marked_ceases_to_exist_date != '0000-00-00' AND customer_ceases_to_exist_handled = 1";
$o_query = $o_main->db->query($s_sql);
$processed_count = ($o_query ? $o_query->num_rows() : 0);

?>
<div id="p_container" class="p_container <?php echo $folderName; ?>">
	<div class="p_containerInner">
		<div class="p_content">
			<div class="p_pageContent">
				<div class="output-filter">
					<ul>
						<li class="item<?php echo ($list_filter == 'not_processed' ? ' active':'');?>">
							<a class="topFilterlink" data-listfilter="not_processed" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&list_filter=not_processed"; ?>">
								<span class="link_wrapper">
									<span class="count"><?php echo $not_processed_count; ?></span>
									<?php echo $formText_NotProcessed_output;?>
								</span>
							</a>
						</li>
						<li class="item<?php echo ($list_filter == 'processed' ? ' active':'');?>">
							<a class="topFilterlink" data-listfilter="processed" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&list_filter=processed"; ?>">
								<span class="link_wrapper">
									<span class="count"><?php echo $processed_count; ?></span>
									<?php echo $formText_Processed_output;?>
								</span>
							</a>
						</li>
					</ul>
				</div>
				<div class="creditor_list">
					<table class="table">
						<tr>
							<td><?php echo $formText_CustomerId_output;?></td>
							<td><?php echo $formText_CustomerName_output;?></td>
							<td><?php echo $formText_CreditorName_output;?></td>
							<td width="80px"><?php echo $formText_CustomerMarkedCeasesToExistDate_output;?></td>
							<td><?php echo $formText_Reason_output;?></td>
							<td width="250xp"><?php echo $formText_Note_output;?></td>
							<td width="50px"><?php echo $formText_ActiveCollectingCompanyCases_output;?></td>
							<td></td>
						</tr>
						<?php 
						$per_page = 200;
						$offset = ($current_page-1)*$per_page;
						$pager = " LIMIT ".$per_page." OFFSET ".$offset;
						if($list_filter == "processed"){
							$s_sql = "SELECT customer.id, customer.name, customer.customer_marked_ceases_to_exist_date, 
							customer.customer_marked_ceases_to_exist_reason, creditor.companyname, customer.active_company_case_count,
							customer.customer_marked_ceases_to_exist_note,
							customer_ceases_to_exist_handled_date, customer_ceases_to_exist_handled_by FROM customer
							JOIN creditor ON creditor.id = customer.creditor_id
							WHERE IFNULL(customer.customer_marked_ceases_to_exist_date, '0000-00-00') != '0000-00-00' AND IFNULL(customer_ceases_to_exist_handled, 0) = 1
							ORDER BY customer_ceases_to_exist_handled_date DESC
							".$pager;
							$o_query = $o_main->db->query($s_sql);
							$creditors = ($o_query ? $o_query->result_array() : array());
							$totalPages = ceil($processed_count/$per_page);
						} else {
							$s_sql = "SELECT customer.id, customer.name, customer.customer_marked_ceases_to_exist_date, 
							customer.customer_marked_ceases_to_exist_reason, creditor.companyname, customer.active_company_case_count,
							customer.customer_marked_ceases_to_exist_note,
							customer_ceases_to_exist_handled_date, customer_ceases_to_exist_handled_by FROM customer
							JOIN creditor ON creditor.id = customer.creditor_id
							WHERE IFNULL(customer.customer_marked_ceases_to_exist_date, '0000-00-00') != '0000-00-00' AND IFNULL(customer_ceases_to_exist_handled, 0) = 0
							".$pager;
							$o_query = $o_main->db->query($s_sql);
							$creditors = ($o_query ? $o_query->result_array() : array());
							$totalPages = ceil($not_processed_count/$per_page);
						}
							
						foreach($creditors as $creditor) {
							?>
							<tr>
								<td><?php echo $creditor['id'];?></td>
								<td><?php echo $creditor['name'];?></td>
								<td><?php echo $creditor['companyname'];?></td>
								<td><?php echo date("d.m.Y", strtotime($creditor['customer_marked_ceases_to_exist_date']));?></td>
								<td><?php echo $creditor['customer_marked_ceases_to_exist_reason'];?></td>
								<td>
									<?php if($list_filter == "processed") { 
										echo $creditor['customer_ceases_to_exist_handled_by']."<br/>";
										if($creditor['customer_ceases_to_exist_handled_date'] != "0000-00-00" && $creditor['customer_ceases_to_exist_handled_date'] != ""){
											echo date("d.m.Y H:i:s", strtotime($creditor['customer_ceases_to_exist_handled_date']))."<br/>";
										}
									} ?>
									<?php echo nl2br($creditor['customer_marked_ceases_to_exist_note']);?>							
								</td>
								<td><?php echo $creditor['active_company_case_count'];?></td>
								<td>
									<a class="marked_as_handled" href="#" data-id="<?php echo $creditor['id'];?>"><span class="glyphicon glyphicon-pencil"></span></a>
								</td>
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
		font-weight: bold;
	}
</style>

<?php if(isset($_GET['list_filter'])){ $list_filter = $_GET['list_filter']; } else { $list_filter = 'not_processed'; } ?>
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
                    creditor_id: '<?php echo $selected_creditor['id'];?>',
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
	$(".creditor_selection").off("click").on("click", function(){
		var data = {
			creditor_id: $(this).data("creditor-id"),
			collecting_company_case_id: $(this).data('collecting_company_case_id-id')
		}
		loadView("list", data);
	})
	$(".fancybox").fancybox();
	$(".marked_as_handled").off("click").on("click", function(e){
		e.preventDefault();
		var data = {
			customer_id: $(this).data("id")
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
			current_page: page,
			list_filter:'<?php echo $list_filter?>'
		}
		loadView("list", data);
	});
})
</script>