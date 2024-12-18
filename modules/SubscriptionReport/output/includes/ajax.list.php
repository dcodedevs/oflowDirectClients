<?php
if(isset($_POST['list_filter'])) $_GET['list_filter'] = $_POST['list_filter'];
$list_filter = isset($_GET['list_filter']) ? $_GET['list_filter'] : 'active';

$firstSubscriptionType = array();
$o_query = $o_main->db->query("SELECT * FROM subscriptiontype ORDER BY subscriptiontype.name");
if($o_query && $o_query->num_rows()>0) $firstSubscriptionType = $o_query->row_array();

if(isset($_POST['subscription_type'])) $_GET['subscription_type'] = $_POST['subscription_type'];
if(isset($_POST['workgroup_filter'])) $_GET['workgroup_filter'] = $_POST['workgroup_filter'];
if(isset($_POST['status_filter'])) $_GET['status_filter'] = $_POST['status_filter'];
if(isset($_POST['search_filter'])) $_GET['search_filter'] = $_POST['search_filter'];
if(isset($_POST['customerselfdefinedlist_filter'])) $_GET['customerselfdefinedlist_filter'] = $_POST['customerselfdefinedlist_filter'];
if(isset($_POST['ownercompany_filter'])) $_GET['ownercompany_filter'] = $_POST['ownercompany_filter'];
if(isset($_POST['date_filter'])) $_GET['date_filter'] = $_POST['date_filter'];

$subscription_type_filter = isset($_GET['subscription_type']) ? $_GET['subscription_type'] : $firstSubscriptionType['id'];
$workgroup_filter = isset($_GET['workgroup_filter']) ? $_GET['workgroup_filter'] : '';
$status_filter = isset($_GET['status_filter']) ? $_GET['status_filter'] : 2;
$search_filter = isset($_GET['search_filter']) ? $_GET['search_filter'] : '';
$customerselfdefinedlist_filter = isset($_GET['customerselfdefinedlist_filter']) ? $_GET['customerselfdefinedlist_filter'] : '';
$ownercompany_filter = isset($_GET['ownercompany_filter']) ? $_GET['ownercompany_filter'] : '';
$date_filter = isset($_GET['date_filter']) ? $_GET['date_filter'] : '';

$order_field = isset($_POST['order_field']) ? $_POST['order_field'] : '';
$order = isset($_POST['order']) ? $_POST['order'] : '';


require_once __DIR__ . '/functions.php';
if($all_count == null){
	$all_count = get_support_list_count($o_main, 'active', $search_filter, $subscription_type_filter, $status_filter, $customerselfdefinedlist_filter, $ownercompany_filter, $date_filter, $workgroup_filter);
}

if(isset($_POST['page'])) {
	$page = $_POST['page'];
}
if(intval($page) == 0){
	$page = 1;
}
$rowOnly = $_POST['rowOnly'];
$perPage = 1000;
$showing = $page * $perPage;
$showMore = false;
$currentCount = $all_count;
if($list_filter == 'not_started'){
	$not_started_count = get_support_list_count($o_main, 'not_started', $search_filter, $subscription_type_filter, $status_filter, $customerselfdefinedlist_filter, $ownercompany_filter, $date_filter, $workgroup_filter);
	$currentCount = $not_started_count;
}
if($list_filter == 'stopped'){
	$stopped_count = get_support_list_count($o_main, 'stopped', $search_filter, $subscription_type_filter, $status_filter, $customerselfdefinedlist_filter, $ownercompany_filter, $date_filter, $workgroup_filter);
	$currentCount = $stopped_count;
}
if($list_filter == 'future_stop'){
	$future_stop_count = get_support_list_count($o_main, 'future_stop', $search_filter, $subscription_type_filter, $status_filter, $customerselfdefinedlist_filter, $ownercompany_filter, $date_filter, $workgroup_filter);
	$currentCount = $future_stop_count;
}
if($list_filter == 'deleted'){
	$deleted_count = get_support_list_count($o_main, 'deleted', $search_filter, $subscription_type_filter, $status_filter, $customerselfdefinedlist_filter, $ownercompany_filter, $date_filter, $workgroup_filter);
	$currentCount = $deleted_count;
}

$s_sql = "SELECT * FROM subscriptiontype WHERE id = ?";
$o_query = $o_main->db->query($s_sql, array($subscription_type_filter));
$subscriptionType = ($o_query ? $o_query->row_array():array());
if($showing < $currentCount){
	$showMore = true;
}
$totalPages = ceil($currentCount/$perPage);

$customerList = get_support_list($o_main, $list_filter, $search_filter, $subscription_type_filter, $status_filter,$customerselfdefinedlist_filter, $ownercompany_filter, $date_filter, $page, $perPage, $order_field, $order, $workgroup_filter);
?>
<?php if (!$rowOnly) { ?>
<?php include(__DIR__."/list_filter.php"); ?>
<div class="gtable" id="gtable_search">
    <div class="gtable_row">
        <div class="gtable_cell gtable_cell_head c1 filterByColumn" data-orderfield="customerName" data-order="<?php if($order_field == "customerName") { if($order == "ASC") { echo 'DESC'; } else { echo 'ASC';}} else { echo 'ASC';}?>"><?php echo $formText_CustomerName_output;?>
	        <?php if($order_field == "customerName") {
	        	if($order == "ASC") { ?>
	        	<span class="glyphicon glyphicon-sort-by-attributes"></span>
	        	<?php } else { ?>
	        	<span class="glyphicon glyphicon-sort-by-attributes-alt"></span>
	        	<?php }	?>
	    	<?php }  else {
				?>
				<span class="glyphicon glyphicon-sort-by-attributes <?php if($order_field != "") echo 'inactive';?>"></span>
				<?php
			} ?>
        </div>
        <div class="gtable_cell gtable_cell_head c2 filterByColumn" data-orderfield="subscriptionName" data-order="<?php if($order_field == "subscriptionName")  { if($order == "ASC") { echo 'DESC';} else { echo 'ASC';}} else { echo 'ASC';}?>"><?php echo $formText_SubscriptionName_output;?>
        	<?php if($order_field == "subscriptionName") {
	        	if($order == "ASC") { ?>
	        	<span class="glyphicon glyphicon-sort-by-attributes"></span>
	        	<?php } else { ?>
	        	<span class="glyphicon glyphicon-sort-by-attributes-alt"></span>
	        	<?php }	?>
	    	<?php }  else {
				?>
				<span class="glyphicon glyphicon-sort-by-attributes inactive"></span>
				<?php
			} ?>
        </div>
        <div class="gtable_cell gtable_cell_head c3"><?php echo $formText_Status_output;?></div>
        <div class="gtable_cell gtable_cell_head filterByColumn"  data-orderfield="useArticlePrice" data-order="<?php if($order_field == "useArticlePrice")  { if($order == "ASC") { echo 'DESC';} else { echo 'ASC';}} else { echo 'ASC';}?>">
			<?php echo $formText_UserArticlePrice_output;?>
			<?php if($order_field == "useArticlePrice") {
	        	if($order == "ASC") { ?>
	        	<span class="glyphicon glyphicon-sort-by-attributes"></span>
	        	<?php } else { ?>
	        	<span class="glyphicon glyphicon-sort-by-attributes-alt"></span>
	        	<?php }	?>
	    	<?php }  else {
				?>
				<span class="glyphicon glyphicon-sort-by-attributes inactive"></span>
				<?php
			} ?>
		</div>
        <div class="gtable_cell gtable_cell_head c4 filterByColumn" data-orderfield="summaryPerMonth" data-order="<?php if($order_field == "summaryPerMonth")  { if($order == "ASC") { echo 'DESC';} else { echo 'ASC';}}else { echo 'ASC';}?>">
			<?php
			if($subscriptionType['periodUnit'] == 0) {
			   echo $formText_SummaryPerMonth_output;
			} else {
			   echo $formText_SummaryPerYear_output;
		   	}
		   ?>
        	<?php if($order_field == "summaryPerMonth") {
	        	if($order == "ASC") { ?>
	        	<span class="glyphicon glyphicon-sort-by-attributes"></span>
	        	<?php } else { ?>
	        	<span class="glyphicon glyphicon-sort-by-attributes-alt"></span>
	        	<?php }	?>
	    	<?php }  else {
				?>
				<span class="glyphicon glyphicon-sort-by-attributes inactive"></span>
				<?php
			} ?>
        </div>
        <?php if ($list_filter == "active" || $list_filter == "not_started") { ?>
	        <div class="gtable_cell gtable_cell_head filterByColumn" data-orderfield="startDate" data-order="<?php if($order_field == "startDate"){ if($order == "ASC") { echo 'DESC';} else { echo 'ASC';}} else { echo 'ASC';}?>"><?php echo $formText_StartDate_output;?>
	        	<?php if($order_field == "startDate") {
		        	if($order == "ASC") { ?>
		        	<span class="glyphicon glyphicon-sort-by-attributes"></span>
		        	<?php } else { ?>
		        	<span class="glyphicon glyphicon-sort-by-attributes-alt"></span>
		        	<?php }	?>
		    	<?php } else {
					?>
					<span class="glyphicon glyphicon-sort-by-attributes inactive"></span>
					<?php
				} ?>
	        </div>
	        <div class="gtable_cell gtable_cell_head filterByColumn" data-orderfield="nextRenewalDate" data-order="<?php if($order_field == "nextRenewalDate"){ if($order == "ASC") { echo 'DESC';} else { echo 'ASC';}} else { echo 'ASC';}?>"><?php echo $formText_NextRenewalDate_output;?>
	        	<?php if($order_field == "nextRenewalDate") {
		        	if($order == "ASC") { ?>
		        	<span class="glyphicon glyphicon-sort-by-attributes"></span>
		        	<?php } else { ?>
		        	<span class="glyphicon glyphicon-sort-by-attributes-alt"></span>
		        	<?php }	?>
		    	<?php }  else {
					?>
					<span class="glyphicon glyphicon-sort-by-attributes inactive"></span>
					<?php
				} ?>
	        </div>
        <?php } else if ($list_filter == "stopped" || $list_filter == "future_stop") { ?>
	        <div class="gtable_cell gtable_cell_head filterByColumn" data-orderfield="startDate" data-order="<?php if($order_field == "startDate"){ if($order == "ASC") { echo 'DESC';} else { echo 'ASC';}} else { echo 'ASC';}?>"><?php echo $formText_StartDate_output;?>
	        	<?php if($order_field == "startDate") {
		        	if($order == "ASC") { ?>
		        	<span class="glyphicon glyphicon-sort-by-attributes"></span>
		        	<?php } else { ?>
		        	<span class="glyphicon glyphicon-sort-by-attributes-alt"></span>
		        	<?php }	?>
		    	<?php } else {
					?>
					<span class="glyphicon glyphicon-sort-by-attributes inactive"></span>
					<?php
				} ?>
	        </div>
	        <div class="gtable_cell gtable_cell_head filterByColumn" data-orderfield="stoppedDate" data-order="<?php if($order_field == "stoppedDate") { if($order == "ASC") { echo 'DESC';} else { echo 'ASC';}}else { echo 'ASC';}?>"><?php echo $formText_StoppedDate_output;?>
	        	<?php if($order_field == "stoppedDate") {
		        	if($order == "ASC") { ?>
		        	<span class="glyphicon glyphicon-sort-by-attributes"></span>
		        	<?php } else { ?>
		        	<span class="glyphicon glyphicon-sort-by-attributes-alt"></span>
		        	<?php }	?>
		    	<?php } else {
					?>
					<span class="glyphicon glyphicon-sort-by-attributes inactive"></span>
					<?php
				} ?>
	        </div>
        <?php } ?>

		<div class="gtable_cell gtable_cell_head filterByColumn" data-orderfield="price_adjustment" data-order="<?php if($order_field == "price_adjustment") { if($order == "ASC") { echo 'DESC';} else { echo 'ASC';}}else { echo 'ASC';}?>"><?php echo $formText_PriceAdjustment_output;?>
			<?php if($order_field == "price_adjustment") {
				if($order == "ASC") { ?>
				<span class="glyphicon glyphicon-sort-by-attributes"></span>
				<?php } else { ?>
				<span class="glyphicon glyphicon-sort-by-attributes-alt"></span>
				<?php }	?>
			<?php } else {
				?>
				<span class="glyphicon glyphicon-sort-by-attributes inactive"></span>
				<?php
			} ?>
		</div>
    </div>
<?php } ?>
<?php
$priceAdjustmentArray = array(0=>$formText_No_output, 1=>$formText_PercentagePriceAdjustment_output, 2=>$formText_CPIPriceAdjustment_output, 3=>$formText_ManualPriceAdjustment_output);
foreach($customerList as $v_row)
{
	$status = get_subscription_status($o_main, $v_row);
	$statusText = "";
	switch($status){
		case 1:
			$statusText = $formText_StatusNotStarted_output;
		break;
		case 2:
			$statusText = $formText_StatusActive_output;
		break;
		case 3:
			$statusText = $formText_StatusStopped_output;
		break;
		case 4:
			$statusText = $formText_StatusFutureStop_output;
		break;
	}
	// $summaryPerMonth = get_subscription_summary_per_month($v_row);
	$summaryPerMonth = $v_row['summaryPerMonth'];
	$s_edit_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$v_row['id'];

	$sql = "SELECT * FROM subscriptionline WHERE subscribtionId = ?";
	$o_query = $o_main->db->query($sql, array($v_row['id']));
	$subscriptionlines = $o_query ? $o_query->result_array() : array();
	$articlePriceCount = 0;

	foreach($subscriptionlines as $subscriptionline){
		if($subscriptionline['articleOrIndividualPrice']){
			$articlePriceCount++;
		}
	}
	?>
 	<div class="gtable_row click" data-href="<?php echo $s_edit_link;?>" data-subscriptionid="<?php echo $v_row['id']?>">
        <div class="gtable_cell"><?php echo $v_row['customerName'];?></div>
        <div class="gtable_cell "><?php echo $v_row['subscriptionName'];?></div>
        <div class="gtable_cell"><?php echo $statusText;?></div>
        <div class="gtable_cell"><?php if($articlePriceCount > 0) { echo $formText_Yes_output;} else { echo $formText_No_output;}?></div>
        <div class="gtable_cell"><?php if($v_row['freeNoBilling']) { echo $formText_FreeNoBilling_Output; } else { echo number_format($summaryPerMonth, 2, ',', ''); }?></div>
        <?php if ($list_filter == "active" || $list_filter == "not_started") {  ?>
	        <div class="gtable_cell"><?php if($v_row['startDate'] != "0000-00-00") echo date('d.m.Y', strtotime($v_row['startDate']));?></div>
	        <div class="gtable_cell"><?php if($v_row['nextRenewalDate'] != "0000-00-00") echo date('d.m.Y', strtotime($v_row['nextRenewalDate']));?></div>
        <?php } else if ($list_filter == "stopped" || $list_filter == "future_stop") { ?>
	        <div class="gtable_cell"><?php if($v_row['startDate'] != "0000-00-00") echo date('d.m.Y', strtotime($v_row['startDate']));?></div>
	        <div class="gtable_cell"><?php if($v_row['stoppedDate'] != "0000-00-00") echo date('d.m.Y', strtotime($v_row['stoppedDate']));?></div>
        <?php } ?>
		<div class="gtable_cell"><?php echo $priceAdjustmentArray[intval($v_row['priceAdjustmentType'])]; if(intval($v_row['priceAdjustmentType']) == 1) echo " ".$v_row['annualPercentageAdjustment']."%";?></div>
    	<div class="gtable_dropdown"><div class="dropdown_wrapper"></div></div>
    </div>
	<?php

} ?>
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
	}
	asort($pages);
	?>
	<?php foreach($pages as $page) {?>
		<a href="#" data-page="<?php echo $page?>" class="page-link"><?php echo $page;?></a>
	<?php } ?>
	<?php /*
    <div class="showMoreCustomers"><?php echo $formText_Showing_Output;?> <span class="showing"><?php echo $showing;?></span> <?php echo $formText_Of_output." ".$currentCount;?> <a href="#" class="showMoreCustomersBtn"><?php echo $formText_ShowMore_output;?></a> </div>*/?>
<?php } ?>
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
			$(this).removeClass('opened');
		}
	};
	$(function() {
		$(document).off('click', '.output-click-helper').on('click', '.output-click-helper', function(e){
			if(e.target.nodeName == 'DIV') fw_load_ajax($(this).data('href'),'',true);
		});
	});

    $(".page-link").on('click', function(e) {
	    page = $(this).data("page");
	    e.preventDefault();
	    var data = {
	        subscription_type: $('.subscriptionTypeFilter').val(),
            list_filter: '<?php echo $list_filter; ?>',
            search_filter: $('.searchFilter').val(),
            status_filter: $('.statusFilter').val(),
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
	        subscription_type: $('.subscriptionTypeFilter').val(),
            list_filter: '<?php echo $list_filter; ?>',
            search_filter: $('.searchFilter').val(),
            status_filter: $('.statusFilter').val(),
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
    $(".gtable_row").unbind("click").bind("click", function(e){
    	var thisEl = $(this);
		thisEl.find('.gtable_dropdown').css("top", $(this).position().top + $(this).height());
    	if($(e.target).parents(".gtable_dropdown").length == 0){
	        e.preventDefault();
	        var wrapper = thisEl.find('.gtable_dropdown .dropdown_wrapper');
	    	var element = thisEl.find('.gtable_dropdown');
	    	if(element.is(":visible")){
	       		element.slideToggle();
	    	} else {
	    		$('.gtable_dropdown').hide();
				if(wrapper.html() == ""){
			        var data = {
			        	subscriptionId: $(this).data("subscriptionid")
			        };
			        ajaxCall('getSubscriptionLines', data, function(json) {

		           		wrapper.html(json.html);
		           		element.slideToggle();

		        	});
		        } else {
		       		element.slideToggle();
		       	}
		    }
	    }
    })
    $(".filterByColumn").unbind("click").bind("click", function(){
    	var thisEl = $(this);
    	var orderField = $(this).data("orderfield");
    	var order = $(this).data("order");
		var data = {
            subscription_type: $('.subscriptionTypeFilter').val(),
            status_filter: $('.statusFilter').val(),
            list_filter: '<?php echo $list_filter; ?>',
            search_filter: $('.searchFilter').val(),
            order_field: orderField,
            order: order
        };
        ajaxCall('list', data, function(json) {
            $('.p_pageContent').html(json.html);
            if(order == "ASC") {
            	thisEl.data("order", "DESC");
            } else if(order == "DESC") {
            	thisEl.data("order", "ASC");
            }
        });
    })
</script>
<?php } ?>
