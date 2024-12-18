<?php
$default_list = "active";
// if(count($customer_listtabs_basisconfig) > 0) {
// 	$default_list = $customer_listtabs_basisconfig[0]['id'];
// }

if ($_POST['list_filter']) $_GET['list_filter'] = $_POST['list_filter'];
if ($_POST['search_filter']) $_GET['search_filter'] = $_POST['search_filter'];
if ($_POST['search_by']) $_GET['search_by'] = $_POST['search_by'];

$list_filter = $_GET['list_filter'] ? ($_GET['list_filter']) : $default_list;
$search_filter = $_GET['search_filter'] ? ($_GET['search_filter']) : '';
$search_by = $_GET['search_by'] ? ($_GET['search_by']) : 1;

$_SESSION['list_filter'] = $list_filter;
$_SESSION['search_filter'] = $search_filter;
$_SESSION['search_by'] = $search_by;

require_once __DIR__ . '/functions.php';
if(!function_exists("APIconnectAccount")) include_once(__DIR__."/../../input/includes/APIconnect.php");

$s_sql = "SELECT * FROM moduledata WHERE name = 'Orders'";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
    $orders_module_id_find = $o_query->row_array();
    $orders_module_id = $orders_module_id_find["uniqueID"];
}

if($list_filter == "all"){
	$itemCount = get_customer_list_count($o_main, 'all', $search_filter, $search_by);
} else {
	$itemCount = get_customer_list_count($o_main, $list_filter, $search_filter, $search_by);
}

if(isset($_POST['page'])) {
	$page = $_POST['page'];
}
if(intval($page) == 0){
	$page = 1;
}
$rowOnly = $_POST['rowOnly'];
$perPage = 100;
$showing = $page * $perPage;
$showMore = false;
$currentCount = $itemCount;
// if($list_filter == 'with_subscriptions'){
// 	$with_subscriptions_count = get_customer_list_count('with_subscriptions',$building_filter, $search_filter, $customergroup_filter);
// 	$currentCount = $with_subscriptions_count;
// }
// if($list_filter == 'with_expired_subscriptions'){
// 	$with_expired_subscriptions_count = get_customer_list_count('with_expired_subscriptions',$building_filter, $search_filter, $customergroup_filter);
// 	$currentCount = $with_expired_subscriptions_count;
// }
$showDefaultList = false;
if($showing < $currentCount){
	$showMore = true;
}
$totalPages = ceil($currentCount/$perPage);

$customerList = get_customer_list($o_main, $list_filter, $search_filter, $search_by, $page, $perPage);

$_SESSION['listpagePerPage'] = $perPage;
$_SESSION['listpagePage'] = $page;

$s_sql = "SELECT * FROM accountinfo";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
    $v_accountinfo = $o_query->row_array();
}
?>
<?php if (!$rowOnly) { ?>
	<?php if(!$_POST['updateOnlyList']){?>
		<?php include(__DIR__."/list_filter.php"); ?>
	<?php } ?>
	<?php if(!$_POST['updateOnlyList']){?>
	<div class="resultTableWrapper">
	<?php } ?>
	<div class="gtable" id="gtable_search">
	    <div class="gtable_row">
	        <div class="gtable_cell gtable_cell_head"><?php echo $formText_PhoneNumber_output;?></div>
	        <div class="gtable_cell gtable_cell_head"><?php echo $formText_DateFrom_output;?></div>
	        <div class="gtable_cell gtable_cell_head"><?php echo $formText_DateTill_output;?></div>
			<div class="gtable_cell gtable_cell_head"><?php echo $formText_Customer_output;?></div>
			<div class="gtable_cell gtable_cell_head"><?php echo $formText_ContactPerson_output;?></div>
	    </div>
<?php } ?>
    <?php
    foreach($customerList as $v_row){

        $s_edit_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$v_row['customerId'];
        ?>
        <div class="gtable_row">
            <div class="gtable_cell"><?php echo $v_row['phone'];?></div>
            <div class="gtable_cell"><?php echo date("d.m.Y", strtotime($v_row['created']));?></div>
            <div class="gtable_cell"><?php if($v_row['deleted'] == null || $v_row['deleted'] == '0000-00-00 00:00:00') { echo '<span class="activeKeycard">'.$formText_Active_outputKeycard.'</span>'; } else { echo date("d.m.Y", strtotime($v_row['deleted'])); }?></div>
            <div class="gtable_cell"><?php echo $v_row['customerName'];?></div>
            <div class="gtable_cell"><?php echo $v_row['contactPersonName'];?></div>
        </div>
    <?php } ?>
	<?php if (!$rowOnly) { ?>
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
			}echo '<!--section-->';
			asort($pages);
			?>
			<?php foreach($pages as $page) {?>
				<a href="#" data-page="<?php echo $page?>" class="page-link"><?php echo $page;?></a>
			<?php } ?>
			<?php /*
		    <div class="showMoreCustomers"><?php echo $formText_Showing_Output;?> <span class="showing"><?php echo $showing;?></span> <?php echo $formText_Of_output." ".$currentCount;?> <a href="#" class="showMoreCustomersBtn"><?php echo $formText_ShowMore_output;?></a> </div>*/?>
	<?php } ?>
	<?php if(!$_POST['updateOnlyList']){ ?>
	</div>
	<?php } ?>
    <style>
        .activeKeycard {
            background: #5cb85c;
            display: inline-block;
            padding: 2px 6px;
            color: #fff;
            border-radius: 3px;
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
			$(this).removeClass('opened');
		}
	};
	$(function() {
		$(document).off('click', '.output-click-helper').on('click', '.output-click-helper', function(e){
			if(e.target.nodeName == 'DIV') fw_load_ajax($(this).data('href'),'',true);
		});
		// $(document).off('mouseenter mouseleave', '.output-access-changer')
		// .on('mouseenter', '.output-access-changer', function(){
		// 	$(this).find(".output-access-dropdown").show();
		// }).on('mouseleave', '.output-access-changer', function(){
		// 	$(this).find(".output-access-dropdown").hide();
		// });
		$(".editInvitationButton").on('click', function(e){
			$.ajax({
				cache: false,
				type: 'POST',
				dataType: 'json',
				url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=edit_invitation";?>',
				data: { fwajax: 1, fw_nocss: 1, cid: $(this).data('id') },
				success: function(obj){
					$('#popupeditboxcontent').html('');
					$('#popupeditboxcontent').html(obj.html);
					out_popup = $('#popupeditbox').bPopup(out_popup_options);
					$("#popupeditbox:not(.opened)").remove();
				}
			});
		});
	});

    $(".page-link").on('click', function(e) {
	    page = $(this).data("page");
	    e.preventDefault();
	    var data = {
	        building_filter:$(".buildingFilter").val(),
	        customergroup_filter: $(".customerGroupFilter").val(),
	        list_filter: '<?php echo $list_filter; ?>',
	        search_filter: $('.searchFilter').val(),
            search_by: $(".searchBy").val(),
	        page: page
	    };
	    ajaxCall('list', data, function(json) {
	        $('.p_pageContent').html(json.html);
	        if(json.html.replace(" ", "") == ""){
	            $(".showMoreCustomersBtn").hide();
	        }

	    });
    });
    $('.showMoreCustomersBtn').on('click', function(e) {
        page = parseInt(page)+1;
        e.preventDefault();
        var data = {
            building_filter: $(".buildingFilter").val(),
            customergroup_filter: $(".customerGroupFilter").val(),
            list_filter: '<?php echo $list_filter; ?>',
            search_filter: $('.searchFilter').val(),
            search_by: $(".searchBy").val(),
            page: page,
            rowOnly: 1
        };
        ajaxCall('list', data, function(json) {
            $('.p_pageContent .gtable').append(json.html);
            $(".showMoreCustomers .showing").html($(".gtable .gtable_row.output-click-helper").length);
            if(json.html.replace(" ", "") == ""){
                $(".showMoreCustomersBtn").hide();
            }
        });
    });
</script>
<?php } ?>
