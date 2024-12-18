<?php
// List btn
// require_once __DIR__ . '/list_btn.php';
if(!function_exists("APIconnectAccount")) include_once(__DIR__."/../../input/includes/APIconnect.php");

$sql = "select * from accountinfo";
$o_query = $o_main->db->query($sql);
$v_accountinfo = $o_query ? $o_query->row_array() : array();

$cid = $o_main->db->escape_str($_GET['cid']);

$sql = "SELECT * FROM article_supplier WHERE id = $cid";
$o_query = $o_main->db->query($sql);
$caseData = $o_query ? $o_query->row_array() : array();

$s_sql = "SELECT * FROM customer WHERE customer.id = ?";
$o_query = $o_main->db->query($s_sql, array($caseData['customer_id']));
$customer = ($o_query ? $o_query->row_array() : array());

if($caseData['customer_contactperson_email'] != ""){
	$s_sql = "SELECT * FROM contactperson WHERE contactperson.email = ? AND contactperson.customerId = ?";
	$o_query = $o_main->db->query($s_sql, array($caseData['customer_contactperson_email'], $customer['id']));
	$contactPersonItem = ($o_query ? $o_query->row_array() : array());
}

function formatHour($hour){
	return str_replace(".", ",", floatval(number_format($hour, 2, ".", "")));
}

$list_filter = $_SESSION['list_filter'] ? ($_SESSION['list_filter']) : 'all';
$responsibleperson_filter = $_SESSION['responsibleperson_filter'] ? ($_SESSION['responsibleperson_filter']) : '';
$list_filter_main = $_SESSION['list_filter_main'] ? ($_SESSION['list_filter_main']) : '';
$search_filter = $_SESSION['search_filter'] ? ($_SESSION['search_filter']) : '';
$projecttype_filter = $_SESSION['projecttype_filter'] ? $_SESSION['projecttype_filter'] : '';
$projectcategory_filter = $_SESSION['projectcategory_filter'] ? $_SESSION['projectcategory_filter'] : '';
$search_by = $_SESSION['search_by'] ? ($_SESSION['search_by']) : 1;
$s_list_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=list&main_filter=case&list_filter=supplier&search_filter=".$search_filter."&search_by=".$search_by;

$s_page_reload_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$projectData['id']."&view=".$list_filter_main;

$registered_group_list = array();
$v_membersystem = array();
$v_registered_usernames = array();
$o_query = $o_main->db->query("SELECT * FROM cache_userlist_access");
$v_cache_userlist = $o_query ? $o_query->result_array() : array();
foreach($v_cache_userlist as $v_user_cached_info) {
	$v_membersystem[$v_user_cached_info['username']] = $v_user_cached_info;
    if($v_user_cached_info['user_id'] > 0) $v_registered_usernames[] = $v_user_cached_info['username'];
	$registered_group_list[$v_user_cached_info['username']] = json_decode($v_user_cached_info['groups'], true);
}

$s_sql = "SELECT * FROM article_supplier WHERE id = ? ORDER BY created DESC";
$o_query = $o_main->db->query($s_sql, array($cid));
$projectData = ($o_query ? $o_query->row_array() : array());

?>
<?php
require_once __DIR__ . '/functions.php';
if($all_count == null){
	 $all_count = get_support_list_count($cid, $search_filter);
}
if(isset($_POST['page'])) {
	 $page = $_POST['page'];
}
if(intval($page) == 0){
	 $page = 1;
}
if(isset($_POST['rowOnly'])){ $rowOnly = $_POST['rowOnly']; } else { $rowOnly = ''; }
$perPage = 500;
$showing = $page * $perPage;
$showMore = false;
$currentCount = $all_count;

$customerList = get_support_list($cid, $search_filter, $page, $perPage, $order_field, $order_direction);

$totalPages = ceil($currentCount/$perPage);

$s_sql = "SELECT * FROM article WHERE id = ?";
$o_query = $o_main->db->query($s_sql, array($projectData['main_article']));
$main_article = ($o_query ? $o_query->row_array() : array());
?>
<div id="p_container" class="p_container">
	<div class="p_containerInner">
		<div class="p_content">
			<div class="p_pageContent">
                <a href="<?php echo $s_list_link?>" class="output-click-helper optimize back-to-list" style="display: block; margin-bottom:10px;"><?php echo $formText_BackToList_outpup;?></a>
				<div class="p_pageDetails">
					<div class="p_contentBlock_wrapper">

						<div class="p_contentBlock">
						    <div class="projectDetails">
								<div class="caseLabel">
									<?php echo $formText_Supplier_output;?>
								</div>
								<table class="mainTable" width="100%" border="0" cellpadding="0" cellspacing="0">
									<tr>
										<td class="txt-label"><?php echo $formText_Name_output;?></td>
										<td class="txt-value"><?php echo $projectData['name'];?></td>
									</tr>
									<tr>
										<td class="txt-label"><?php echo $formText_MainArticle_output;?></td>
										<td class="txt-value"><?php echo $main_article['articleCode']." ".$main_article['name'];?></td>
									</tr>
									<tr>
										<td class="txt-label"><?php echo $formText_UploadFile_output;?></td>
										<td class="txt-value"><span class="fas fa-plus uploadFile"></span></td>
									</tr>
								<tr>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
								</tr>
								<tr>
								   <td class="txt-label"><?php echo $formText_Products_output;?></td>
								   <td class="txt-value"><?php echo $all_count;?></td>
							  </tr>
							   </table>
							   <table class="mainTable btn-edit-table" width="100%" border="0" cellpadding="0" cellspacing="0">
				   	            <tr>
				   	                <td class="txt-label"></td>
				   	                <td class="txt-value"></td>
				   	                <td class="btn-edit" colspan="2"><?php if($moduleAccesslevel > 10) { ?><span class="output-edit-project-detail glyphicon glyphicon-pencil"  data-project-id="<?php echo $cid; ?>" ></span><?php } ?></td>
				   	            </tr>
				   	        </table>
						   </div>
					   </div>
					   <div class="">
						   <table class="table table-fixed">
							   <tr class="gtable_row">
								   <th><?php echo $formText_ArticleCode_output;?></th>
								   <th><?php echo $formText_ArticleName_output;?></th>
								   <th><?php echo $formText_SalesUnit_output;?></th>
								   <th><?php echo $formText_ProductGroup_output;?></th>
								   <th><?php echo $formText_ProductCategory_output;?></th>
								   <th><?php echo $formText_CostPrice_output;?></th>
								   <th><?php echo $formText_Price_output;?></th>
								   <th></th>
							   </tr>
							   <?php
							  	foreach($customerList as $v_row)
						   		{
						   		?>
						   	 	<tr class="gtable_row <?php echo $article_has_error ? 'gtable_row_error' : '' ?>">
					   	        	<td class="gtable_cell c3"><?php echo $v_row['articleCode']?></td>
						   	        <td class="gtable_cell c3">
						   				<?php echo $v_row['name'];?>
						   				<?php if ($article_has_error): ?>
						   					<?php foreach($article_errors[$v_row['id']]['errors'] as $article_error): ?>
						   						<div style="color:#a94442"><?php echo $article_error['message']; ?></div>
						   					<?php endforeach; ?>
						   				<?php endif;?>
						   			</td>
									<td class="gtable_cell">
										<?php echo $v_row['sales_unit'];?>
									</td>
									<td class="gtable_cell">
										<?php echo $v_row['supplier_product_group'];?>
									</td>
									<td class="gtable_cell">
										<?php echo $v_row['supplier_product_category'];?>
									</td>
						   	        <td class="gtable_cell c2"><?php echo number_format($v_row['costPrice'], 2, ",", "");?></td>
						   	        <td class="gtable_cell c2"><?php echo number_format($v_row['price'], 2, ",", "");?></td>
						   	        <td class="gtable_cell cEdit"><span class="edit-article editBtnIcon" data-article-id="<?php echo $v_row['id']?>"><span class="glyphicon glyphicon-pencil"></span></span><span class="delete-article editBtnIcon" data-article-id="<?php echo $v_row['id']?>"><span class="glyphicon glyphicon-trash"></span></span></td>
						   	    </div>
						   		<?php
						   	} ?>
						   </table>

					   </div>
					   <?php /*?>
						<div class="p_pageDetailsSubTitle white">
							<?php echo $projectData['subject'];?>

						</div> */?>
					</div>
					<?php if($totalPages > 1) {
					 $currentPage = $page;
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
					 for($x = 1; $x <= 3;$x++){
						 $prevPage = $page - $x;
						 $nextPage = $page + $x;
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
					 <?php foreach($pages as $single_page) { ?>
						 <a href="#" data-page="<?php echo $single_page?>"  class="page-link<?php if($single_page == $page) echo ' active';?>"><?php echo $single_page;?></a>
					 <?php } ?>
					 <?php /*
					 <div class="showMoreCustomers"><?php echo $formText_Showing_Output;?> <span class="showing"><?php echo $showing;?></span> <?php echo $formText_Of_output." ".$currentCount;?> <a href="#" class="showMoreCustomersBtn"><?php echo $formText_ShowMore_output;?></a> </div>*/?>
				 <?php } ?>

				</div>
			</div>
		</div>
	</div>
</div>
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
			<?php
			if(isset($_POST['projectId']) && isset($_POST['mainProjectId'])) {
				?>
				loadView("details_supplier", {cid:"<?php echo $_POST['mainProjectId'];?>", "subprojectId": "<?php echo $_POST['projectId'];?>", view:"<?php echo $list_filter_main?>"});
			<?php
			} else {
			?>
				loadView("details_supplier", {cid:"<?php echo $cid;?>", view:"<?php echo $list_filter_main?>"});
			<?php
			} ?>
		}
	}
};
$(function(){

	$('.edit-article').on('click', function(e) {
		e.preventDefault();
		var data = {
			articleId: $(this).data("article-id")
		};
		ajaxCall('editArticle', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	});
	$('.delete-article').on('click', function(e) {
		e.preventDefault();
		if(!fw_click_instance)
		{
			fw_click_instance = true;
			var $_this = $(this);
			bootbox.confirm({
				message:"<?php echo $formText_ConfirmDelete_output;?>",
				buttons:{confirm:{label:"<?php echo $formText_Yes_Output;?>"},cancel:{label:"<?php echo $formText_No_Output;?>"}},
				callback: function(result){
					if(result)
					{
						fw_loading_start();
						var data = {
							articleId: $_this.data("article-id"),
							action: "deleteArticle"
						};
						ajaxCall('editArticle', data, function(json) {
							if(json.error !== undefined)
								{
								fw_info_message_empty();
								$.each(json.error, function(index, value){
									var _type = Array("error");
									if(index.length > 0 && index.indexOf("_") > 0) _type = index.split("_");
									fw_info_message_add(_type[0], value);
								});
								fw_info_message_show();
								fw_loading_end();
							} else {
								var data = {
									cid: '<?php echo $cid;?>'
								};
								loadView("details_supplier", data);
							}
						});
					}
					fw_click_instance = false;
				}
			});
		}
	});

	$(".page-link").on('click', function(e) {
		page = $(this).data("page");
		e.preventDefault();
		var data = {
			cid: '<?php echo $_GET['cid'];?>',
			page: page
		};
		loadView("details_supplier", data);
	});

    $(".uploadFile").off("click").on("click", function(e){
        e.preventDefault();
        var data = {
			supplier_id: "<?php echo $cid;?>"
        };
        ajaxCall('uploadFile', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    })
	$(".caseAccess").unbind("click").on('click', function(e){
        e.preventDefault();
        var data = {
			caseId: "<?php echo $cid;?>"
        };
        ajaxCall('editCaseAccess', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
	});
	var initDropdownValue = $(".caseStatusChange").val();

	$(".caseStatusChange").on('change', function(e){
		e.preventDefault();
		var caseId  = $(this).data('case-id');
		var data = {
			caseId: caseId,
			action:"statusChange",
			status: $(this).val()
		};
		ajaxCall('editCase', data, function(json) {
			if(json.data == "message") {
				$('#popupeditboxcontent').html('');
				$('#popupeditboxcontent').html(json.html);
				out_popup = $('#popupeditbox').bPopup(out_popup_options);
				$("#popupeditbox:not(.opened)").remove();
				$(".caseStatusChange").val(initDropdownValue);
			} else {
				loadView("details", {cid:"<?php echo $cid;?>"});
			}
		});
	});

	$(".dropdown_content_show").unbind("click").bind("click", function(e){
		var parent = $(this);
		if($(e.target).hasClass("dropdown_content_show") || $(e.target).hasClass("showArrow") || $(e.target).parent().hasClass("showArrow")){
			var dropdown = parent.next(".p_contentBlock.dropdown_content");
			if(dropdown.is(":visible")) {
				dropdown.slideUp();
				parent.find(".showArrow .glyphicon").addClass("glyphicon-triangle-right");
				parent.find(".showArrow .glyphicon").removeClass("glyphicon-triangle-bottom");
			} else {
				if(parent.hasClass("autoload")) {
					dropdown.slideDown(0);
					parent.removeClass("autoload");
				} else {
					dropdown.slideDown();
				}
				parent.find(".showArrow .glyphicon").removeClass("glyphicon-triangle-right");
				parent.find(".showArrow .glyphicon").addClass("glyphicon-triangle-bottom");
			}
		}
	})

	$(".output-edit-project-detail").unbind("click").on('click', function(e){
        e.preventDefault();
        var data = {
			supplier_id: "<?php echo $cid;?>"
        };
        ajaxCall('edit_supplier', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
	});
	$(".output-edit-ticket-message").unbind("click").on('click', function(e){
		e.preventDefault();
		var data = {
			case_id: $(this).data('case-id'),
			cid: $(this).data('casemessage-id'),
			project_id: $(this).data('project-id')
		};
		ajaxCall('editCaseMessage', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	});
	$(".output-delete-ticket-message").unbind("click").on('click', function(e){
		e.preventDefault();
		var self = $(this);
		var data = {
			cid: self.data('casemessage-id'),
			ticket_id: self.data('ticket-id'),
			output_delete: 1
		};
		bootbox.confirm('<?php echo $formText_ConfirmDeleteMessage_output; ?>', function(result) {
			if (result) {
				ajaxCall('editCaseMessage', data, function(json) {
					<?php
					if(isset($_POST['projectId']) && isset($_POST['mainProjectId'])) {
						?>
						loadView("details", {cid:"<?php echo $_POST['mainProjectId'];?>", "subprojectId": "<?php echo $_POST['projectId'];?>", view:"<?php echo $list_filter_main?>"});
					<?php
					} else {
					?>
						loadView("details", {cid:"<?php echo $cid;?>", view:"<?php echo $list_filter_main?>"});
					<?php
					} ?>
				});
			}
		});
	});
	$(".output-add-tickets").unbind("click").on('click', function(e){
		e.preventDefault();
		var data = {
			case_id: $(this).data('case-id'),
			customer_id: $(this).data('customer-id')
		};
		ajaxCall('editCaseMessage', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	});
	$(".fancybox").fancybox();

})
</script>
<style>

.alertinfo {
	color: red;
	margin-left: 5px;
	position: relative;
}
.alertinfo .hover {
	color: #333;
	position: absolute;
	top: 100%;
	padding: 4px 3px;
	border: 1px  solid #cecece;
	background: #fff;
	z-index: 1;
	font-family: 'PT Sans', sans-serif;
	font-size: 12px;
	width: 120px;
	left: -40px;
	font-weight: normal;
	display: none;
}
.alertinfo:hover .hover {
	display: block;
}

.hoverEye {
	position: relative;
	color: #0284C9;
	float: right;
	margin-top: 2px;
}
.hoverEye .hoverInfo {
    font-family: 'PT Sans', sans-serif;
    width: 450px;
    display: none;
    color: #000;
    position: absolute;
    right: 0%;
    top: 100%;
    padding: 5px 10px;
    background: #fff;
    border: 1px solid #ccc;
    z-index: 1;
	max-height: 300px;
	overflow: auto;
}
.hoverEye:hover .hoverInfo {
	display: block;
}
	.caseLabel {
		float: left;
		margin-right: 5px;
		font-size: 16px;
	}
	.caseAccess {
		float: left;
		background: #eee;
		border: 1px solid #ddd;
		border-radius: 5px;
		padding: 5px 10px;
		cursor: pointer;
		margin-bottom: 20px;
	}
	.caseAccess.red {
		background: red;
		color: #fff;
		border: 0;
	}
	.caseAccess.green {
		background: green;
		color: #fff;
		border: 0;
	}
	.buttonWrapper {
		text-align: center;
	}
	.buttonWrapper .addEntryBtn {
		margin-left: 0;
		font-size: 16px;
	}
	.buttonWrapper .addEntryBtn::before {
		font-size: 20px;
	}
	.p_contentBlock  .output-edit-elements,
	.p_contentBlock  .output-delete-elements {
		float: right;
		cursor: pointer;
		color: #0284C9
	}
	.p_contentBlock  .output-delete-elements {
		margin-left: 10px;
	}
	.p_contentBlock .project_element_row {
		padding: 4px 0px;
	}
	.projectDetails {
		position: relative;
	}
	.totalTimeused {
		position: absolute;
		top: 0px;
		right: 0px;
		cursor: pointer;
		color: #0284C9;
	}
	.mainProjectApproveChanger {
		position: absolute;
		top: 20px;
		right: 0px;
		cursor: pointer;
	}
	.workplanlineBlock {
		border: 1px solid #cecece;
		padding: 5px 5px;
		margin-bottom: 5px;
	}
	.output-delete-connection {
		float: right;
	}

	.collectingOrder {
	    margin-bottom: 15px;
	}
	.collectingOrder .table {
	    margin-bottom: 5px;
	}
	.collectingOrder th {
	    background: #fafafa;
	}
	.collectingOrder th span {
	    font-weight: normal;
	}
	.approvedForBatchInvoicingWrapper {
	    float: left;
	}
	.seperatedInvoiceWrapper {
	    float: left;
	    margin-left: 20px;
	}
	.totalRow {
	    float: right;
	    text-align: right;
	    margin-bottom: 10px;
	}
	.totalRow span {
	    font-weight: bold;
	}
	.output-btn-filled {
	    padding: 10px 15px;
	    background: #2893e2;
	    color: #fff;
	    font-weight: bold;
	    display: inline-block;
	    cursor: pointer;
	    border-radius: 5px;
	}
	.orderConfirmations {
	    float: left;
	    width: 58%;
	}
	.orderButtons {
	    float: right;
	    width: 40%;
	    text-align: right;
	}
	.createInvoice {
	    float: right;
	    margin-right: 40px;
	}

	.collectingOrder .rightAligned {
		text-align: right;
	}
	.orderConfirmationRow {
		margin-bottom: 5px;
	}
	.orderConfirmationRow .errorRow {
		color: #f85050;
		font-weight: bold;
	}
	.orderConfirmationRow span {
		font-weight: bold;
	}
	.show_collectingorder_details {
		color: #46b2e2;
		cursor: pointer;
	}
	.show_collectingorder_details:hover {
		color: #0284C9;
	}
	.seeMoreCollectingOrder {
		color: #46b2e2;
		cursor: pointer;
	}
	.seeMoreCollectingOrder:hover {
		color: #0284C9;
	}
	.hideMoreCollectingOrder {
		color: #46b2e2;
		cursor: pointer;
		display: none;
	}
	.hideMoreCollectingOrder:hover {
		color: #0284C9;
	}
	.p_pageDetailsTitle .projectId {
		float: none;
		display: inline-block;
		margin-left: 5px;
	}
	.p_pageDetailsTitle .projectCreated {
		float: none;
		font-weight: normal;
	}
	.p_pageDetailsTitle .projectId span {
		font-weight: 500;
	}
	.p_pageDetailsTitle .projectStatus {
		float: right;
		margin-right: 15px;
	}
	.table-borderless > tbody > tr > td,
	.table-borderless > tbody > tr > th,
	.table-borderless > tfoot > tr > td,
	.table-borderless > tfoot > tr > th,
	.table-borderless > thead > tr > td,
	.table-borderless > thead > tr > th {
		border: 0;
	}
	.commentBlock {
		border: 1px solid #ddd;
		border-radius: 5px;
		margin-bottom: 10px;
		padding: 7px 0px;
	}
	.commentBlock .table {
		margin-bottom: 0;
	}
	.feedbackBlock {
		background: #f0f0f0;
	}
	#p_container .commentBlock td {
		padding: 2px 10px;
	}
	.ticketBlock {

	}
	.ticketBlock .urgent .urgentText {
		display: inline-block;
	}

	.ticketCommentBlock {
	    text-align: left;
	    width: 70%;
		float: right;
	}
	.ticketCommentBlock .inline_info {
	    float: right;
	    margin-left: 10px;
	}
	.ticketCommentBlock .table {
		display: block;
	    margin-bottom: 0;
		border: 1px solid #ddd;
	    border-radius: 5px;
	    margin-bottom: 10px;
	    padding: 7px 15px;
		margin-top: 5px;
	    background: #f0f0f0;
		word-break: break-all;
	}
	.ticketCommentBlock.from_customer {
	    text-align: left;
	    float: left;
	}
	.ticketCommentBlock.from_customer .table {
	    background: #d6f8fe;
	}
	.ticketCommentBlock.from_customer .inline_info {
	    float: left;
	    margin-right: 10px;
	    margin-left: 0;
	}
	.collectingOrder {
		margin-bottom: 15px;
	}
	.collectingOrder .table {
		margin-bottom: 5px;
	}
	.collectingOrder th {
		background: #fafafa;
	}
	.collectingOrder th span {
		font-weight: normal;
	}

	.approvedForBatchInvoicingWrapper {
	    float: left;
	}
	.totalRow {
	    float: right;
	    text-align: right;
	    margin-bottom: 10px;
	}
	.totalRow span {
	    font-weight: bold;
	}
	.output-btn-filled {
		padding: 10px 15px;
		background: #2893e2;
		color: #fff;
		font-weight: bold;
		display: inline-block;
		cursor: pointer;
		border-radius: 5px;
	}
	.orderConfirmations {
		float: left;
		width: 58%;
	}
	.orderButtons {
		float: right;
		width: 40%;
		text-align: right;
	}
	.createInvoice {
	    float: right;
	    margin-right: 40px;
	    color: #46b2e2;
		cursor: pointer;
		display: inline-block;
		vertical-align: middle;
		background: none;
		border: 0;
	}
	.addEntryBtn {
		margin-left: 20px;
	}
	.projectDetails .txt-label {
	    width:30%;
	}
	.p_pageDetailsSubTitle {
		position: relative;
	}
    .p_contentBlock {
        position: relative;
    }
	.p_contentBlock_wrapper .showArrow {
	    cursor: pointer;
	    color: #2996E7;
	    margin-left: 10px;
	    position: absolute;
	    right: 10px;
	    top: 10px;
	}
	.p_contentBlock.dropdown_content {
		display: block;
	}
	.p_pageContent .btn-edit {
		text-align: right;
        margin-top: -15px;
	}
    .p_pageContent .btn-edit-table {
        margin-top: -25px;
    }
	.p_pageDetails .submonth {
		padding-left: 20px;
	}
	.p_pageDetails .realcolumn {
		display: none;
	}
    .sharedFacilitators {
        float: left;
        max-width: 90%;
    }
    .output-share-project {
        float: right;
    }
    .projectDetails .mainTable td {
    	padding: 3px 0px;
    }
    .table-bordered > tbody > tr > th,
    .table-bordered > thead > tr > th {
    	border: 0;
    }


    .workPlanMonth {
		font-size: 12px;
		float: right;
	}
	.startMonth {
		font-size: 12px;
		float: right;
		margin-right: 20px;
	}
	.startMonthPicker  {
		width: 65px;
		padding-left: 7px;
		border: 1px solid #dedede;
	}
	.workPlanMonth span {
		display: inline-block;
		vertical-align: middle;
	}
	.monthCounter {
		position: relative;
		display: inline-block;
		vertical-align: middle;
		padding: 3px 20px 3px 4px;
		line-height: 14px;
		background: #fff;
		border: 1px solid #dedede;
		border-radius: 5px;
	}
	.monthCounter .monthCounterChange {
		position: absolute;
		top: 0;
		right: 0;
		width: 16px;
		height: 100%;
		border-radius: 5px;
		overflow: hidden;
	}
	.monthCounter .monthCounterChange .monthCounterUp {
		height: 50%;
		text-align: center;
		background: #0093e7;
		cursor: pointer;
		color: #fff;
		font-size: 8px;
	}
	.monthCounter .monthCounterChange .monthCounterDown {
		height: 50%;
		text-align: center;
		background: #0093e7;
		cursor: pointer;
		color: #fff;
		font-size: 8px;
	}
	.monthCounter .monthCounterChange .glyphicon {
		top: 3px;
		vertical-align: top;
	}
	.monthCounter .monthCounterChange .monthCounterDown .glyphicon {
		top: -1px;
	}
	#p_container .p_contentBlock .calendarViewWrapper {
		padding: 0px;
	}
	#p_container .p_contentBlock .calendarView {
		width: 100%;
	}
	#p_container .p_contentBlock .calendarView .rowHeader td {
		font-weight: bold;
		color: #545454;
		padding: 7px 8px;
	}
	#p_container .p_contentBlock .calendarView td {
		border: 1px solid #f6f6f6;
		width: 13%;
		padding: 7px 0px;
	    vertical-align: top;
	    color: #a5ada7;
	}
	#p_container .p_contentBlock .calendarView td.firstColumn {
		width: 9%;
	}
	#p_container .p_contentBlock .calendarView td.weekendDay {
		background: #fafafa;
	}
	#p_container .p_contentBlock .calendarView td.approvedDay {
		background: #ECFFE6;
	}
	#p_container .p_contentBlock .calendarView td.approvedDay.weekendDay {
		background: #E2F7DC;
	}
	#p_container .p_contentBlock .calendarView td.differentBorder {
		border-top: 2px solid #e8e8e8;
	}
	#p_container .p_contentBlock .calendarView td.differentBorder.differentBorderLeft {
		border-left: 2px solid #e8e8e8;
	}
	#p_container .p_contentBlock .calendarView .dateRow {
		margin-bottom: 10px;
		padding: 0px 8px;
		font-weight: bold;
	}
	#p_container .p_contentBlock .calendarView .dateRow:hover .buttonRow {
		display: block;
	}
	#p_container .p_contentBlock .calendarView .buttonRow {
		float: right;
		display: none;
	}
	#p_container .p_contentBlock .calendarView .output-btn {
		border: 0;
		padding: 0;
		margin: 0;
	}
	#p_container .p_contentBlock .calendarView .numberRow {
		padding: 0px 8px;
	}
	#p_container .p_contentBlock .calendarView .workerRow {
		padding: 0px 8px;
		margin-bottom: 3px;
	    overflow: hidden;
	    text-overflow: ellipsis;
	    white-space: nowrap;
	    color: #4E4E4E;
	}
	#p_container .p_contentBlock .calendarView .workerRow:hover {
		white-space: normal;
		background: #fafafa;
	}
	#p_container .p_contentBlock .calendarView .workerRow:hover .buttonRow {
		display: block;
	}
	#p_container .p_contentBlock .calendarView .workerRow.absent {
		color: #f09486;
	}
	#p_container .p_contentBlock .calendarView .workerRow.absent.unpaidAbbsence {
		color: orange;
	}
	#p_container .p_contentBlock .calendarView .workerRow.replacement {
		color: #6ccf47;;
	}
	/*Work plan week START */
	.p_contentInner.diff {
		padding: 0px 0px 0px;
	}
	.p_contentBlock .repeatingOrderWorkLine .day {
		font-weight: 700;
	}
	.repeatingOrderWorkLine {
		padding: 10px 0px;
	}
	.repeatingOrderWorklineWorker {
		padding: 10px 10px;
	}
	.workplanlineworker {
		padding: 10px 10px;
	}
	.workplanlineworker.sub {
		padding: 5px 10px 5px 30px;
	}
	.workplanlineworker.differentWorker {
		color: blue;
	}
	.workplanlineworker .differentTime {
		color: red;
	}
	.p_contentBlock .workPlanWeekTitle {

		padding: 10px 25px;
		background: #eaeaea;
		font-weight: 700;
		border-top: 1px solid #cecece;
		border-bottom: 1px solid #cecece;
	}
	.p_contentBlock .workPlanWeek {
	}
	.p_contentBlock .workplanlineWrapper {
		padding: 10px 30px;
		border-bottom: 1px solid #cecece;

	}
	.p_contentBlock .workplanlineWrapper.differentDay {
		background: #d6dce3;
	}
	.p_contentBlock .workPlanWeek .output-btn {
		/*float: right;*/
	}
	.p_contentBlock .workPlanWeek .workPlanWeekLabel {
		float: left;
	}
	.p_contentBlock .workPlanWeek .differentInfo {
		float: right;
	}
	.add-screenshot {
		color: #0284C9;
		cursor: pointer;
	}
	.screenshot-icon {
		color: #0284C9;
		cursor: pointer;
		display: inline-block;
		margin-bottom: 2px;
		cursor: pointer;
		padding: 2px 4px;
		border: 1px solid #46b2e2;
		border-radius: 4px;
	}
	.screenshot-icon-all {
	}
	.subWrapperIcon {
		color: #0284C9;
		cursor: pointer;
		display: inline-block;
		margin-bottom: 2px;
		cursor: pointer;
		padding: 2px 4px;
		border: 1px solid #46b2e2;
		border-radius: 4px;
		line-height: 17px;
	}
	.screenshot-icon-ticket {
		color: #0284C9;
		cursor: pointer;
		display: inline-block;
		margin-bottom: 2px;
		cursor: pointer;
		padding: 2px 4px;
		border: 1px solid #46b2e2;
		border-radius: 4px;
	}
	.project-file {
		margin-bottom: 4px;
	}
	.project-file-file {
	    display: inline-block;
	    vertical-align: bottom;
	}
	.project-file-button {
		float: right;
	}
	.table-bordered > tbody > tr > td.actionColumn {
		width: 25%;
	}
	.projectSubRow.hideFinished .finishedClass {
		display: none;
	}
	.p_pageDetailsSubTitle .rightFloatItems {
		float: right;
		font-weight: normal;
		margin-right: 20px;
	}
	.p_pageDetailsSubTitle  .subMenuDropdown {
		display: none;
		position: absolute;
		background: #fff;
		padding: 5px 10px;
		text-align: right;
		-webkit-box-shadow: 0px 0px 5px 0px rgba(0,0,0,0.1);
		-moz-box-shadow: 0px 0px 5px 0px rgba(0,0,0,0.1);
		box-shadow: 0px 0px 5px 0px rgba(0,0,0,0.1);
		z-index: 1;
		right: 0px;
	}
	.p_pageDetailsSubTitle  .projectSubColumn .subMenuDropdown button.output-btn {
		display: block;
		margin-left: 0;
		color: #333;
		text-align: left;
	}

	.projectSubOriginal {
		background: #f9f9f9;
		margin-bottom: 10px;
	}
	.projectSub {
		margin-bottom: 10px;
		background: #f0f0f0;
		border-left: 5px solid #727272;
		position: relative;
	}

	.projectSub.subproject {
		border-left: 5px solid #216eb8;
	}

	.projectSub .smallInfoColumn .subWrapperIcon {
		/* color: #fff; */
		/* background: #52b2e4; */
		/* padding: 0px 4px; */
		font-weight: bold;
		/* border-radius: 40px; */
		font-size: 12px;
		line-height: 14px;
		border: 0;
		vertical-align: middle;
		color: #727272;
	}
	.projectSub .projectSubColumn.smallInfoColumn .projectSubIconWrapper {
		color: #727272;
	}

	.projectSub .actionColumnWrapper {
		float: right;
		display: none;
	}
	.projectSub:hover .actionColumnWrapper {
		display: block;
	}
	.projectSub .projectSubRow {
		background-color: #fff;
		padding: 10px 0px 0px 10px;
	}
	.projectSub .projectSubColumn {
		float: left;
		width: 14.5%;
		padding: 10px 1%;
	}
	.projectSub .projectSubColumn.planColumn {
		width: 10.5%;
	}
	.projectSub .projectSubColumn.planColumn select {
		width: 100%;
	}
	.projectSub .projectSubColumn.planColumn input {
		width: 100%;
	}
	.projectSub .projectSubColumn.estimatedColumn {
		width: 7.5%;
	}
	.projectSub .projectSubColumn.actionColumn {
		float: left;
		width: 11.5%;
		padding: 10px 1%;
	}
	.projectSub .projectSubColumn.actionColumn .output-btn {
		margin-left: 5px;
	}
	.projectSub .subMenuDropdown {
		display: none;
		position: absolute;
		background: #fff;
		padding: 5px 10px;
		text-align: right;
		-webkit-box-shadow: 0px 0px 5px 0px rgba(0,0,0,0.1);
		-moz-box-shadow: 0px 0px 5px 0px rgba(0,0,0,0.1);
		box-shadow: 0px 0px 5px 0px rgba(0,0,0,0.1);
		z-index: 1;
	}
	.projectSub .projectSubColumn .subMenuDropdown button.output-btn {
		display: block;
		margin-left: 0;
		color: #333;
		text-align: left;
	}
	.projectSub .subMenuDropdown .margin-bottom {
		margin-bottom: 6px;
	}

	.projectSub .projectSubColumn.changeApprovedColumn {
		float: left;
		width: 10%;
		padding: 10px 1%;
	}
	.projectSub .projectSubColumn.changeApprovedColumn select {
		width: 100%;
	}
	.projectSub .projectSubColumn.subcolumnName {
		width: 20%;
	}
	.projectSub .projectSubColumn.statusColumn {
		text-align: center;
		width: 10%;
		margin-right: 0%;
		float: right;
	}
	.projectSub .projectSubColumn.statusColumn div {
		padding: 5px 0px;
		border-radius: 5px;
	}
	.projectSub .projectSubColumn.activeColor div {
		background: orange;
		color: #fff;
	}
	.projectSub .projectSubColumn.inactiveColor div {
		background: gray;
		color: #fff;
	}
	.projectSub .projectSubColumn.finishedColor div {
		background: green;
		color: #fff;
	}

	.projectSub .projectSubColumn.finishedNotApprovedColor div {
		background: #30e60b;
		color: #fff;
	}
	.projectSub .projectSubColumn.notReleasedColor div {
		background: #45a1ff;
		color: #fff;
	}

	.projectSub .projectSubColumn .projectSubIconWrapper {
		padding: 2px 0px;
		border: 1px solid #46b2e2;
		border-radius: 4px;
	    color: #0284C9;
		margin-left: 2px;
		cursor: pointer;
		position: relative;
		line-height: 23px;
	}
	.projectSub .projectSubIconWrapper.noBorder {
		border: 0;
	}
	.projectSub .projectSubIconWrapper .projectSubIconHover {
		display: none;
		position: absolute;
		-webkit-box-shadow: 0px 0px 5px 0px rgba(0,0,0,0.1);
		-moz-box-shadow: 0px 0px 5px 0px rgba(0,0,0,0.1);
		box-shadow: 0px 0px 5px 0px rgba(0,0,0,0.1);
		z-index: 1;
		background: #fff;
		padding: 5px 10px;
		min-width: 200px;
		color: #333;
		left: 0%;
		top: 100%;
	}
	.projectSub .projectSubIconWrapper:hover .projectSubIconHover {
		display: block;
	}
	.projectSub .projectSubColumn.smallColumn {
		width: 125px;
		padding: 10px 0;
	}
	.projectSub .projectSubColumn.smallColumn .smallInfoWrapper {
		float: left;
		width: 40px;
	}

	.tableSubprojects {
		margin-bottom: 0;
	}
	.subpageWrapper {
		border: 1px solid #ddd;
		border-bottom: 0;
	}
	.showFinished {
		cursor: pointer;
		margin-top: 10px;
	}
	.tableSubprojectsFinished {
		display: none;
		margin-top: 10px;
	}
	.projectSub.hideFinished .finishedClass {
		display: none;
	}
	.otherStatuses {
		text-align: right;
		font-weight: normal;
		float: left;
		margin-right: 10px;
	}

	#p_container .otherStatuses [type="checkbox"]:checked + label::after {
		left: 0px;
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
	.employeeImage {
		width: 40px;
		height: 40px;
		overflow: hidden;
		position: relative;
		border-radius: 20px;
		overflow: hidden;
	    float: right;
	    margin-left: 10px;
	}
	.employeeImage img {
		width: calc(100% + 4px);
		height: auto;
		position: absolute;
	  	left: 50%;
	  	top: 50%;
	  	transform: translate(-50%, -50%);
	}
	.employeeInfo {
	    float: right;
	    width: calc(100% - 50px);
	}
	.ticketCommentBlock.from_customer .employeeImage {
	    float: left;
	    margin-left: 0;
	    margin-right: 10px;
	}
	.ticketCommentBlock.from_customer .employeeInfo {
	    float: left;
	}
	.detailContainer {
		margin-bottom: 10px;
	}
	.hoverSpan {
		position: relative;
	}
	.hoverSpan .hoverInfo {
	    color: #333;
	    position: absolute;
	    top: 100%;
	    padding: 4px 3px;
	    border: 1px solid #cecece;
	    background: #fff;
	    z-index: 1;
	    font-family: 'PT Sans', sans-serif;
	    font-size: 12px;
	    width: 120px;
	    left: -40px;
	    font-weight: normal;
	    display: none;
	}
	.hoverSpan:hover .hoverInfo {
		display: block;
	}
	.hoverSpan.estimatedTimeuseHoursSpan {
		margin-right: 10px;
	}
	.subprojectTitle {
		margin-top: 20px;
		margin-bottom: 5px;
		font-weight: bold;
	}
	.showFinishedCorrections {
		cursor: pointer;
	}
	.finishedCorrectionsWrapper {
		display: none;
	}
	.showHiddenProjects {
		cursor: pointer;
	}
	.caseStatus {
		float: right;
	}
	.page-link.active {
		text-decoration: underline;
	}
	.table-fixed {
		table-layout: fixed;
	}
</style>
