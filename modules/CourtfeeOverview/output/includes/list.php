<?php
//error_reporting(E_ALL | E_STRICT); ini_set("display_errors", 1);
$current_page = $_GET['current_page'] ?? 1;
require_once __DIR__ . '/list_btn.php';
$list_filter = isset($_GET['list_filter']) ? $_GET['list_filter'] : 'not_released';
$sublist_filter = isset($_GET['sublist_filter']) ? $_GET['sublist_filter'] : 'not_invoiced';

// $creditor_id = $_GET['creditor_id'] ?? 0;
// $collecting_company_case_id = $_GET['collecting_company_case_id'] ?? 0;

$per_page = 200;
$offset = ($current_page-1)*$per_page;
$pager = " LIMIT ".$per_page." OFFSET ".$offset;
$s_sql_where = "";
if($list_filter == 'not_released') {
	$s_sql_where .= "AND (cccl.claim_type = 9 OR cccl.claim_type = 10) AND IFNULL(cccl.court_fee_released_date, '0000-00-00') = '0000-00-00'";
	$s_order_by = " ORDER BY cccl.created DESC";
} else if($list_filter == 'released') {
	$s_sql_where .= " AND (cccl.claim_type = 9 OR cccl.claim_type = 10) AND IFNULL(cccl.court_fee_released_date, '0000-00-00') <> '0000-00-00'";
	$s_order_by = " ORDER BY cccl.court_fee_released_date DESC";
} else if($list_filter == "invoicing_to_creditor") {
	$s_sql_where .= " AND (cccl.claim_type = 9)";
	$s_order_by = " ORDER BY cccl.court_fee_released_date DESC";
	if($sublist_filter == "not_invoiced") {
		$s_sql_where .= " AND IFNULL(cccl.court_fee_invoiced_creditor_invoice_nr, 0) = 0";
	} else if($sublist_filter == "invoiced") {
		$s_sql_where .= " AND IFNULL(cccl.court_fee_invoiced_creditor_invoice_nr, 0) > 0";
	}
} else {
	$s_sql_where = "1=2";
}

$s_sql = "SELECT 
cccl.*,
cred.companyname creditorName,
concat_ws(' ', debitor.name, debitor.middlename, debitor.lastname) as debitorName,
ccltb.type_name 
FROM collecting_company_cases_claim_lines cccl
JOIN collecting_company_cases ccc ON ccc.id = cccl.collecting_company_case_id
JOIN creditor cred ON cred.id = ccc.creditor_id
JOIN customer debitor ON debitor.id = ccc.debitor_id
JOIN collecting_cases_claim_line_type_basisconfig ccltb ON ccltb.id = cccl.claim_type
WHERE ccc.content_status < 2 
".$s_sql_where.$s_order_by;
$o_query = $o_main->db->query($s_sql.$pager);
$claim_lines = ($o_query ? $o_query->result_array() : array());
$o_query = $o_main->db->query($s_sql);
$company_case_count = ($o_query ? $o_query->num_rows() : 0);
$totalPages = ceil($company_case_count/$per_page);

$s_sql = "SELECT cccl.id FROM collecting_company_cases_claim_lines cccl
WHERE (cccl.claim_type = 9 OR cccl.claim_type = 10) AND IFNULL(cccl.court_fee_released_date, '0000-00-00') = '0000-00-00'";
$o_query = $o_main->db->query($s_sql);
$not_released_count = ($o_query ? $o_query->num_rows() : 0);

$s_sql = "SELECT cccl.id FROM collecting_company_cases_claim_lines cccl
WHERE (cccl.claim_type = 9 OR cccl.claim_type = 10) AND IFNULL(cccl.court_fee_released_date, '0000-00-00') <> '0000-00-00'";
$o_query = $o_main->db->query($s_sql);
$released_count = ($o_query ? $o_query->num_rows() : 0);

$s_sql = "SELECT cccl.id FROM collecting_company_cases_claim_lines cccl
WHERE (cccl.claim_type = 9)";
$o_query = $o_main->db->query($s_sql);
$invoicing_to_creditor_count = ($o_query ? $o_query->num_rows() : 0);

if($list_filter == "invoicing_to_creditor"){
	$s_sql = "SELECT cccl.id FROM collecting_company_cases_claim_lines cccl
	WHERE (cccl.claim_type = 9) AND IFNULL(cccl.court_fee_invoiced_creditor_invoice_nr, 0) = 0";
	$o_query = $o_main->db->query($s_sql);
	$not_invoiced_count = ($o_query ? $o_query->num_rows() : 0);
	
	$s_sql = "SELECT cccl.id FROM collecting_company_cases_claim_lines cccl
	WHERE (cccl.claim_type = 9) AND IFNULL(cccl.court_fee_invoiced_creditor_invoice_nr, 0) > 0";
	$o_query = $o_main->db->query($s_sql);
	$invoiced_count = ($o_query ? $o_query->num_rows() : 0);
}
?>
<div id="p_container" class="p_container <?php echo $folderName; ?>">
	<div class="p_containerInner">
		<div class="p_content">
			<div class="p_pageContent">
				<div class="output-filter">
					<ul>
						<li class="item<?php echo ($list_filter == 'not_released' ? ' active':'');?>">
							<a class="topFilterlink" data-listfilter="not_released" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&list_filter=not_released"; ?>">
								<span class="link_wrapper">
									<span class="count"><?php echo $not_released_count; ?></span>
									<?php echo $formText_NotReleased_output;?>
								</span>
							</a>
						</li>
						<li class="item<?php echo ($list_filter == 'released' ? ' active':'');?>">
							<a class="topFilterlink" data-listfilter="released" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&list_filter=released"; ?>">
								<span class="link_wrapper">
									<span class="count"><?php echo $released_count; ?></span>
									<?php echo $formText_Released_output;?>
								</span>
							</a>
						</li>
						<li class="item<?php echo ($list_filter == 'invoicing_to_creditor' ? ' active':'');?>">
							<a class="topFilterlink" data-listfilter="invoicing_to_creditor" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&list_filter=invoicing_to_creditor"; ?>">
								<span class="link_wrapper">
									<span class="count"><?php echo $invoicing_to_creditor_count; ?></span>
									<?php echo $formText_InvoicingToCreditor_output;?>
								</span>
							</a>
						</li>
					</ul>
				</div>
				<?php 
				if($list_filter == "invoicing_to_creditor"){
				?>
				<div class="output-filter">
					<ul>
						<li class="item<?php echo ($sublist_filter == 'not_invoiced' ? ' active':'');?>">
							<a class="topFilterlink" data-listfilter="not_invoiced" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&list_filter=invoicing_to_creditor&sublist_filter=not_invoiced"; ?>">
								<span class="link_wrapper">
									<span class="count"><?php echo $not_invoiced_count; ?></span>
									<?php echo $formText_NotInvoiced_output;?>
								</span>
							</a>
						</li>
						<li class="item<?php echo ($sublist_filter == 'invoiced' ? ' active':'');?>">
							<a class="topFilterlink" data-listfilter="invoiced" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&list_filter=invoicing_to_creditor&sublist_filter=invoiced"; ?>">
								<span class="link_wrapper">
									<span class="count"><?php echo $invoiced_count; ?></span>
									<?php echo $formText_Invoiced_output;?>
								</span>
							</a>
						</li>
					</ul>
				</div>
				<div class="creditor_list">
					<table class="table">
						<tr>
							<td><?php echo $formText_CaseId_output;?></td>
							<td><?php echo $formText_CreditorName_output;?></td>
							<td><?php echo $formText_DebitorName_output;?></td>
							<td><?php echo $formText_Type_output;?></td>
							<td><?php echo $formText_Amount_output;?></td>
							<td></td>
						</tr>
						<?php 
						foreach($claim_lines as $claim_line) {
							$s_edit_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=CollectingCompanyCases&folderfile=output&folder=output&inc_obj=details&cid=".$claim_line['collecting_company_case_id'];

							?>
							<tr class="output-click-helper" data-href="<?php echo $s_edit_link;?>">
								<td><?php echo $claim_line['collecting_company_case_id'];?></td>
								<td><?php echo $claim_line['creditorName'];?></td>
								<td><?php echo $claim_line['debitorName'];?></td>
								<td><?php echo $claim_line['type_name'];?></td>
								<td><?php echo $claim_line['amount'];?></td>
								<td><span class="edit_invoice_nr" data-id="<?php echo $claim_line['id']?>"><?php echo $formText_editInvoiceNr_output; ?></span></td>
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
				<?php
				} else {
				?>
				<div class="creditor_list">
					<table class="table">
						<tr>
							<td><?php echo $formText_CaseId_output;?></td>
							<td><?php echo $formText_CreditorName_output;?></td>
							<td><?php echo $formText_DebitorName_output;?></td>
							<td><?php echo $formText_Type_output;?></td>
							<td><?php echo $formText_Amount_output;?></td>
							<td></td>
						</tr>
						<?php 
						foreach($claim_lines as $claim_line) {
							$s_edit_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=CollectingCompanyCases&folderfile=output&folder=output&inc_obj=details&cid=".$claim_line['collecting_company_case_id'];
							
							$s_sql = "SELECT cmt.* FROM cs_mainbook_transaction cmt 
							JOIN cs_mainbook_voucher cmv ON cmv.id = cmt.cs_mainbook_voucher_id
							WHERE cmt.bookaccount_id = 33 AND cmv.case_id = ?";
							$o_query = $o_main->db->query($s_sql, array($claim_line['collecting_company_case_id']));
							$transactionsProtectedCourtFee = ($o_query ? $o_query->result_array() : array());
							?>
							<tr class="output-click-helper" data-href="<?php echo $s_edit_link;?>">
								<td><?php echo $claim_line['collecting_company_case_id'];?></td>
								<td><?php echo $claim_line['creditorName'];?></td>
								<td><?php echo $claim_line['debitorName'];?></td>
								<td><?php echo $claim_line['type_name'];?></td>
								<td><?php echo number_format($claim_line['amount'], 2, ",", "");?></td>
								<td><span class="edit_release_date" data-id="<?php echo $claim_line['id']?>"><?php echo $formText_EditReleaseDate_output; ?></span></td>
							</tr>
							<?php 
							if(count($transactionsProtectedCourtFee) > 0) {
							?>
							<tr><td colspan="5">
								<b><?php echo $formText_TransactionsConnectedToTheProtectedCourtFee_output;?></b>
								<div>
									<?php foreach($transactionsProtectedCourtFee as $transaction) { 
										echo $transaction['text']." - ".$transaction['amount'];?><br/>
									<?php } ?>
								</div>
							</td></tr>
							<?php
							}
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
				<?php } ?>
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
	.edit_release_date {
		cursor: pointer;
		color: #46b2e2
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
					list_filter: '<?php echo $list_filter;?>',					
					sublist_filter: '<?php echo $sublist_filter;?>'
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
	$(".edit_release_date").off("click").on("click", function(e){
		e.preventDefault();
		var data = {
			claimline_id: $(this).data("id")
		};
		ajaxCall({module_file: 'edit_release_date', module_folder: 'output'}, data, function(json) {			
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();	
		});
	})
	$(".edit_invoice_nr").off("click").on("click", function(e){
		e.preventDefault();
		var data = {
			claimline_id: $(this).data("id")
		};
		ajaxCall({module_file: 'edit_invoice_nr', module_folder: 'output'}, data, function(json) {			
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();	
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