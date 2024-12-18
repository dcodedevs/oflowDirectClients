<?php
$default_list = "active";
// if(count($customer_listtabs_basisconfig) > 0) {
// 	$default_list = $customer_listtabs_basisconfig[0]['id'];
// }
$s_sql = "SELECT * FROM employee WHERE email = ?";
$o_query = $o_main->db->query($s_sql, array($variables->loggID));
if($o_query && $o_query->num_rows()>0){
    $currentEmployee = $o_query->row_array();
}

$filtersList = array("search_filter", "responsibleperson_filter", "projecttype_filter", "show_checksum_incorrect");

if ($_POST['list_filter']) $_GET['list_filter'] = $_POST['list_filter'];
if ($_POST['order_field']) $_GET['order_field'] = $_POST['order_field'];
if ($_POST['order_direction']) $_GET['order_direction'] = $_POST['order_direction'];
foreach($filtersList as $filterName){
	if ($_POST[$filterName]) $_GET[$filterName] = $_POST[$filterName];
}

$list_filter = $_GET['list_filter'] ? ($_GET['list_filter']) : $default_list;
if(isset($_GET['order_direction'])){ $order_direction = $_GET['order_direction']; } else { $order_direction = 0;}
if(isset($_GET['order_field'])){ $order_field = $_GET['order_field']; } else { $order_field = 'created';}

foreach($filtersList as $filterName){
	${$filterName} = $_GET[$filterName] ? ($_GET[$filterName]) : '';
}
if($responsibleperson_filter == ''){
    $responsibleperson_filter = $currentEmployee['id'];
}

$_SESSION['list_filter'] = $list_filter;
foreach($filtersList as $filterName){
	$_SESSION[$filterName] = ${$filterName};
}

$filters = array();
foreach($filtersList as $filterName){
	$filters[$filterName] = ${$filterName};
}

$filters['order_field'] = $order_field;
$filters['order_direction'] = $order_direction;

require_once __DIR__ . '/functions.php';
if(!function_exists("APIconnectAccount")) include_once(__DIR__."/../../input/includes/APIconnect.php");

$s_sql = "SELECT * FROM moduledata WHERE name = 'Orders'";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
    $orders_module_id_find = $o_query->row_array();
    $orders_module_id = $orders_module_id_find["uniqueID"];
}
$active_count = get_customer_list_count($o_main, "active", $filters);


$itemCount = get_customer_list_count($o_main, $list_filter, $filters);

if(isset($_POST['page'])) {
	$page = $_POST['page'];
}
if(intval($page) == 0){
	$page = 1;
}
$rowOnly = $_POST['rowOnly'];
$perPage = 500;
$showing = $page * $perPage;
$showMore = false;
$currentCount = $itemCount;

if($showing < $currentCount){
	$showMore = true;
}
$totalPages = ceil($currentCount/$perPage);

$customerList = get_customer_list($o_main, $list_filter, $filters, $page, $perPage);

$_SESSION['listpagePerPage'] = $perPage;
$_SESSION['listpagePage'] = $page;

$s_sql = "SELECT * FROM accountinfo";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
    $v_accountinfo = $o_query->row_array();
}
$statusArray = array($formText_Active_output, $formText_Finished_output, $formText_Objection_output, $formText_Canceled_output);
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
			<div class="gtable_cell gtable_cell_head " >
				<?php echo $formText_Id_output;?>
			</div>
			<div class="gtable_cell gtable_cell_head orderBy <?php if($order_field == "created") echo 'orderActive';?>" data-orderfield="created" data-orderdirection="<?php if($order_direction == 0) { echo 1; } else { echo 0; } ?>">
				<?php echo $formText_Created_output;?>
				<div class="ordering">
					<div class="fas fa-caret-up" <?php if($order_field == "created" && $order_direction == 0) { echo 'style="display: none;"';}?>></div>
					<div class="fas fa-caret-down" <?php if($order_field == "created" && $order_direction == 1) { echo 'style="display: none;"';}?>></div>
				</div>
			</div>
	        <div class="gtable_cell gtable_cell_head orderBy <?php if($order_field == "creditorname") echo 'orderActive';?>" data-orderfield="creditorname" data-orderdirection="<?php if($order_direction == 0) { echo 1; } else { echo 0; } ?>">
				<?php echo $formText_Creditor_output;?>
				<div class="ordering">
					<div class="fas fa-caret-up" <?php if($order_field == "creditorname" && $order_direction == 0) { echo 'style="display: none;"';}?>></div>
					<div class="fas fa-caret-down" <?php if($order_field == "creditorname" && $order_direction == 1) { echo 'style="display: none;"';}?>></div>
				</div>
			</div>
	        <div class="gtable_cell gtable_cell_head orderBy <?php if($order_field == "autosync") echo 'orderActive';?>" data-orderfield="autosync" data-orderdirection="<?php if($order_direction == 0) { echo 1; } else { echo 0; } ?>">
				<?php echo $formText_LastSyncedDate_output;?>
				<div class="ordering">
					<div class="fas fa-caret-up" <?php if($order_field == "autosync" && $order_direction == 0) { echo 'style="display: none;"';}?>></div>
					<div class="fas fa-caret-down" <?php if($order_field == "autosync" && $order_direction == 1) { echo 'style="display: none;"';}?>></div>
				</div>
			</div>
	        <div class="gtable_cell gtable_cell_head orderBy <?php if($order_field == "controlsum") echo 'orderActive';?>" data-orderfield="controlsum" data-orderdirection="<?php if($order_direction == 0) { echo 1; } else { echo 0; } ?>">
				<?php echo $formText_ControlSumCorrect_output;?>
				<div class="ordering">
					<div class="fas fa-caret-up" <?php if($order_field == "controlsum" && $order_direction == 0) { echo 'style="display: none;"';}?>></div>
					<div class="fas fa-caret-down" <?php if($order_field == "controlsum" && $order_direction == 1) { echo 'style="display: none;"';}?>></div>
				</div>
			</div>
	        <div class="gtable_cell gtable_cell_head orderBy <?php if($order_field == "autosync") echo 'orderActive';?>" data-orderfield="autosync" data-orderdirection="<?php if($order_direction == 0) { echo 1; } else { echo 0; } ?>">
				<?php echo $formText_AutosyncFailed_output;?>
				<div class="ordering">
					<div class="fas fa-caret-up" <?php if($order_field == "autosync" && $order_direction == 0) { echo 'style="display: none;"';}?>></div>
					<div class="fas fa-caret-down" <?php if($order_field == "autosync" && $order_direction == 1) { echo 'style="display: none;"';}?>></div>
				</div>
			</div>
	        <div class="gtable_cell gtable_cell_head orderBy <?php if($order_field == "demo") echo 'orderActive';?>" data-orderfield="demo" data-orderdirection="<?php if($order_direction == 0) { echo 1; } else { echo 0; } ?>">
				<?php echo $formText_Demo_output;?>
				<div class="ordering">
					<div class="fas fa-caret-up" <?php if($order_field == "demo" && $order_direction == 0) { echo 'style="display: none;"';}?>></div>
					<div class="fas fa-caret-down" <?php if($order_field == "demo" && $order_direction == 1) { echo 'style="display: none;"';}?>></div>
				</div>
			</div>
			<div class="gtable_cell gtable_cell_head orderBy <?php if($order_field == "skip") echo 'orderActive';?>" data-orderfield="skip" data-orderdirection="<?php if($order_direction == 0) { echo 1; } else { echo 0; } ?>">
				<?php echo $formText_SkipReminderGoDirectlyToCollecting_output;?>
				<div class="ordering">
					<div class="fas fa-caret-up" <?php if($order_field == "skip" && $order_direction == 0) { echo 'style="display: none;"';}?>></div>
					<div class="fas fa-caret-down" <?php if($order_field == "skip" && $order_direction == 1) { echo 'style="display: none;"';}?>></div>
				</div>
			</div>
			<div class="gtable_cell gtable_cell_head orderBy <?php if($order_field == "progress") echo 'orderActive';?>" data-orderfield="progress" data-orderdirection="<?php if($order_direction == 0) { echo 1; } else { echo 0; } ?>">
				<?php echo $formText_Progress_output;?>
				<div class="ordering">
					<div class="fas fa-caret-up" <?php if($order_field == "progress" && $order_direction == 0) { echo 'style="display: none;"';}?>></div>
					<div class="fas fa-caret-down" <?php if($order_field == "progress" && $order_direction == 1) { echo 'style="display: none;"';}?>></div>
				</div>
			</div>
			<div class="gtable_cell gtable_cell_head orderBy <?php if($order_field == "reminder_from") echo 'orderActive';?>" data-orderfield="reminder_from" data-orderdirection="<?php if($order_direction == 0) { echo 1; } else { echo 0; } ?>">
				<?php echo $formText_ReminderOnlyFromInvoiceNr_output;?>
				<div class="ordering">
					<div class="fas fa-caret-up" <?php if($order_field == "reminder_from" && $order_direction == 0) { echo 'style="display: none;"';}?>></div>
					<div class="fas fa-caret-down" <?php if($order_field == "reminder_from" && $order_direction == 1) { echo 'style="display: none;"';}?>></div>
				</div>
			</div>
			<?php /*?>
	        <div class="gtable_cell gtable_cell_head orderBy <?php if($order_field == "controlsum") echo 'orderActive';?>" data-orderfield="controlsum" data-orderdirection="<?php if($order_direction == 0) { echo 1; } else { echo 0; } ?>"></div>
			<div class="gtable_cell gtable_cell_head orderBy <?php if($order_field == "controlsum") echo 'orderActive';?>" data-orderfield="controlsum" data-orderdirection="<?php if($order_direction == 0) { echo 1; } else { echo 0; } ?>"></div>
			*/?>
		</div>
<?php } ?>
    <?php
    foreach($customerList as $v_row){
        $s_edit_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=creditor_list&cid=".$v_row['id'];
        ?>
        <div class="gtable_row output-click-helper" data-href="<?php echo $s_edit_link;?>">
        <?php
      	// Show default columns
      	 ?>
		 	<div class="gtable_cell"><?php echo $v_row['id'];?></div>
		 	<div class="gtable_cell"><?php echo date("d.m.Y H:i:s", strtotime($v_row['created']));?></div>
	        <div class="gtable_cell"><?php echo $v_row['creditorName'];?></div>
   	        <div class="gtable_cell"><?php if($v_row['sync_from_accounting']) { if($v_row['onboarding_incomplete']) { echo $formText_OnboardingIncomplete_output; } else { if($v_row['lastImportedDate'] != "" && $v_row['lastImportedDate'] != "0000-00-00 00:00:00") { echo date("d.m.Y H:i:s", strtotime($v_row['lastImportedDate'])); } }} else { echo $formText_NotForSyncing_output;}?></div>
			<div class="gtable_cell"><?php if($v_row['sync_from_accounting']) { if($v_row['onboarding_incomplete']) { echo $formText_OnboardingIncomplete_output; } else { if($v_row['control_sum_correct'] != "" && $v_row['control_sum_correct'] != "0000-00-00 00:00:00") { echo date("d.m.Y H:i:s", strtotime($v_row['control_sum_correct'])); } else { echo 'not correct';}} }else { echo $formText_NotForSyncing_output;}?></div>
			<div class="gtable_cell"><?php if($v_row['sync_from_accounting']) { if($v_row['onboarding_incomplete']) { echo $formText_OnboardingIncomplete_output; } else { if($v_row['autosyncing_not_working_date'] != "" && $v_row['autosyncing_not_working_date'] != "0000-00-00 00:00:00") { echo date("d.m.Y H:i:s", strtotime($v_row['autosyncing_not_working_date'])); } else { echo 'successful';}} }else { echo $formText_NotForSyncing_output;}?></div>
			<div class="gtable_cell"><?php if($v_row['is_demo']) { echo $formText_Demo_output;}?></div>
			<div class="gtable_cell"><?php if($v_row['skip_reminder_go_directly_to_collecting'] == 2) { 
				echo $formText_YesShowProcessingPortal_output;
			} else if($v_row['skip_reminder_go_directly_to_collecting'] == 1) {
				echo $formText_YesHideProcessingPortal_output;
			} else {
				echo $formText_No_output;
			} ?></div>			
			<div class="gtable_cell"><?php 
			if($v_row['choose_progress_of_reminderprocess'] == 1){ 
				echo $formText_Automatic_output; 
			} else if($v_row['choose_progress_of_reminderprocess'] == 0) {
				echo $formText_Manual_output; 				
			}?><br/><?php 
			if($v_row['choose_move_to_collecting_process'] == 1){ 
				echo $formText_Automatic_output; 
			} else if($v_row['choose_move_to_collecting_process'] == 0) {
				echo $formText_Manual_output; 				
			}?></div>
			<div class="gtable_cell"><?php echo $v_row['reminder_only_from_invoice_nr'];?></div>
			<?php /*?>
			<div class="gtable_cell">
				<?php
				$sql = "SELECT collecting_cases.id FROM collecting_cases WHERE creditor_id = ?";
				$o_query = $o_main->db->query($sql, array($v_row['id']));
				$collectingCaseCount = $o_query ? $o_query->num_rows() : 0;

				$sql = "SELECT collecting_company_cases.id FROM collecting_company_cases WHERE creditor_id = ?";
				$o_query = $o_main->db->query($sql, array($v_row['id']));
				$collectingCompanyCaseCount = $o_query ? $o_query->num_rows() : 0;

				if($collectingCaseCount == 0 && $collectingCompanyCaseCount == 0) {
					?>
					<div class="reset_transactions" data-creditorid="<?php echo $v_row['id'];?>"><?php echo $formText_ResetTransactions_output;?></div>
					<?php
				}
				?>
			</div>
			<div class="gtable_cell">
				<div class="resync_open_transactions" data-creditorid="<?php echo $v_row['id'];?>"><?php echo $formText_DeleteTransactionsWithoutCaseAndSyncOpen_output;?></div>
			</div>*/?>
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
			if($(e.target).hasClass("reset_transactions") || $(e.target).hasClass("resync_open_transactions")){
				e.preventDefault();
			} else {
				if(e.target.nodeName == 'DIV') fw_load_ajax($(this).data('href'),'',true);
			}
		});
	});

    $(".orderBy").off("click").on("click", function(){
        var order_field = $(this).data("orderfield");
        var order_direction = $(this).data("orderdirection");

        var data = {
            list_filter: '<?php echo $list_filter; ?>',
            list_filter_main: '<?php echo $list_filter_main?>',
            responsibleperson_filter: $(".responsiblePersonFilter").val(),
            orderowner_filter: $(".orderOwnerFilter").val(),
            search_filter: $('.searchFilter').val(),
            search_by: $(".searchBy").val(),
            type_filter: '<?php echo $type_filter?>',
            order_field: order_field,
            order_direction: order_direction
        }
        loadView("list", data);
    })

    $(".page-link").on('click', function(e) {
	    page = $(this).data("page");
	    e.preventDefault();
	    var data = {
	        building_filter:$(".buildingFilter").val(),
	        customergroup_filter: $(".customerGroupFilter").val(),
	        list_filter: '<?php echo $list_filter; ?>',
	        search_filter: $('.searchFilter').val(),
            search_by: $(".searchBy").val(),
	        page: page,
            order_field: '<?php echo $order_field; ?>',
            order_direction: '<?php echo $order_direction; ?>'
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
	$(".reset_transactions").off("click").on("click", function(e){
		e.preventDefault();
		var data = {
			creditor_id: $(this).data("creditorid")
		}
		bootbox.confirm('<?php echo $formText_ThisWillDeleteAllTransactionsAndSetCreditorToSyncFromStart_output; ?>?', function(result) {
			if (result) {
				ajaxCall('reset_all_transactions', data, function(json) {
					$('#popupeditboxcontent').html('');
					$('#popupeditboxcontent').html(json.html);
					out_popup = $('#popupeditbox').bPopup(out_popup_options);
					$("#popupeditbox:not(.opened)").remove();
				});
			}
		});
	})
	$(".resync_open_transactions").off("click").on("click", function(e){
		e.preventDefault();
		var data = {
			creditor_id: $(this).data("creditorid")
		}
		bootbox.confirm('<?php echo $formText_LaunchTheScript_output; ?>?', function(result) {
			if (result) {
				ajaxCall('resync_open_transactions', data, function(json) {
					$('#popupeditboxcontent').html('');
					$('#popupeditboxcontent').html(json.html);
					out_popup = $('#popupeditbox').bPopup(out_popup_options);
					$("#popupeditbox:not(.opened)").remove();
				});
			}
		});
	})
</script>
<?php } ?>
