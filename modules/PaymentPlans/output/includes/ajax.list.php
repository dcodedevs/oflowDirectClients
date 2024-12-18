<?php
$default_list = "unhandled";
$default_list_main = "";

$s_sql = "select * from project_accountconfig";
$o_query = $o_main->db->query($s_sql);
$v_project_accountconfig= ($o_query ? $o_query->row_array() : array());

$filtersList = array("search_filter","search_by", "responsibleperson_filter", "casetype_filter");

if ($_POST['list_filter']) $_GET['list_filter'] = $_POST['list_filter'];
if ($_POST['list_filter_main']) $_GET['list_filter_main'] = $_POST['list_filter_main'];
foreach($filtersList as $filterName){
	if ($_POST[$filterName]) $_GET[$filterName] = $_POST[$filterName];
}

foreach($filtersList as $filterName){
	${$filterName} = isset($_GET[$filterName]) ? ($_GET[$filterName]) : '';
}
$responsibleperson_filter = isset($_GET['responsibleperson_filter']) ? ($_GET['responsibleperson_filter']) : $_SESSION['responsibleperson_filter'];

foreach($filtersList as $filterName){
	$_SESSION[$filterName] = ${$filterName};
}

$s_sql = "SELECT * FROM project_default_role WHERE employeeId = ?";
$o_query = $o_main->db->query($s_sql, array($responsibleperson_filter));
$defaultRole = $o_query ? $o_query->row_array() : array();
if($defaultRole['role'] != ""){
    $default_list_main = $defaultRole['role'];
}

$filters = array();
foreach($filtersList as $filterName){
	if($filterName == "search_filter"){
		$filters[$filterName] = array($search_by, ${$filterName});
	} else if($filterName != "search_by") {
		$filters[$filterName] = ${$filterName};
	}
}
$list_filter = $_GET['list_filter'] ? ($_GET['list_filter']) : $default_list;
$list_filter_main = $_GET['list_filter_main'] ? ($_GET['list_filter_main']) : $default_list_main;

if(isset($_GET['order_direction'])){ $order_direction = $_GET['order_direction']; } else { $order_direction = 0;}
if(isset($_GET['order_field'])){ $order_field = $_GET['order_field']; } else { if($list_filter == "finished") { $order_field = 'completed'; } else { $order_field = 'last_message';  }}

$_SESSION['list_filter'] = $list_filter;
$_SESSION['list_filter_main'] = $list_filter_main;
$_SESSION['order_field'] = $order_field;
$_SESSION['order_direction'] = $order_direction;
require_once __DIR__ . '/functions.php';
if(!function_exists("APIconnectAccount")) include_once(__DIR__."/../../input/includes/APIconnect.php");

$s_sql = "SELECT * FROM moduledata WHERE name = 'Orders'";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
    $orders_module_id_find = $o_query->row_array();
    $orders_module_id = $orders_module_id_find["uniqueID"];
}
$filters['order_field'] = $order_field;
$filters['order_direction'] = $order_direction;

$itemCount = get_customer_list_count($o_main, $list_filter, $list_filter_main, $filters);

$itemCount2 = get_customer_list_count2($o_main, $list_filter, $list_filter_main, $filters);

if(isset($_GET['page'])) {
	$page = $_GET['page'];
}
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
$currentCount = $itemCount2;

if($showing < $currentCount){
	$showMore = true;
}
$totalPages = ceil($currentCount/$perPage);
$customerList = get_customer_list($o_main, $list_filter,$list_filter_main, $filters, $page, $perPage);

$_SESSION['listpagePerPage'] = $perPage;
$_SESSION['listpagePage'] = $page;

$s_sql = "SELECT * FROM accountinfo";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
    $v_accountinfo = $o_query->row_array();
}

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
			<div class="gtable_cell gtable_cell_head dateColumn">
				<div>
                    <?php echo $formText_FirstPaymentDate_Output;?>
                </div>
			</div>
	        <div class="gtable_cell gtable_cell_head">
				<div>
                    <?php echo $formText_MonthlyPayment_Output;?>
                </div>
			</div>
	        <div class="gtable_cell gtable_cell_head">
				<div>
                    <?php echo $formText_CollectingCase_Output;?>
                </div>
			</div>
	        <div class="gtable_cell gtable_cell_head">
				<?php /* ?>
				<div class="orderBy <?php if($order_field == "responsible") echo 'orderActive';?>" data-orderfield="responsible" data-orderdirection="<?php if($order_direction == 0) { echo 1; } else { echo 0; } ?>">
					<?php echo $formText_ResponsiblePerson_output;?>
					<div class="ordering">
						<div class="fas fa-caret-up" <?php if($order_field == "responsible" && $order_direction == 0) { echo 'style="display: none;"';}?>></div>
						<div class="fas fa-caret-down" <?php if($order_field == "responsible" && $order_direction == 1) { echo 'style="display: none;"';}?>></div>
					</div>
				</div>*/?>
				<div>
                    <?php echo $formText_LastSentLetter_Output;?>
                </div>
			</div>

	    </div>
<?php } ?>
    <?php
    foreach($customerList as $v_row){
        $s_edit_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$v_row['id']."&view=".$list_filter_main;

        ?>
        <div class="gtable_row output-click-helper <?php if($v_row['urgent'] && $list_filter == "open_ticket") echo 'urgent';?>" data-href="<?php echo $s_edit_link;?>">
        <?php
      	// Show default columns
      	 ?>
		 	<div class="gtable_cell dateColumn"><?php
				echo date("d.m.Y", strtotime($v_row['first_payment_date']))
			 ?></div>
	        <div class="gtable_cell">
				<?php echo $v_row['monthly_payment'];?>
            </div>
	        <div class="gtable_cell">
                <?php echo $v_row['customerName'];?>
			</div>
	        <div class="gtable_cell">

			</div>
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
			if(e.target.nodeName == 'DIV') fw_load_ajax($(this).data('href'),'',true);
		});
        $("#p_container .set_as_completed_all").off("click").on("click", function(){
            if($(this).is(":checked")){
                $(".set_as_completed").prop("checked", true).change();
            } else {
                $(".set_as_completed").prop("checked", false).change();
            }
        })
        $("#p_container .set_as_completed").off("change").on("change", function(){
            var length = $(".set_as_completed:checked").length;
            $(".set_as_completed_input .checked_count").html(length);
        })
        $("#p_container .set_as_completed_input").off("click").on("click", function(){
            bootbox.confirm('<?php echo $formText_ConfirmSetAsCompleted_output; ?>', function(result) {
                if (result) {
                    fw_loading_start();
                    var _data = {
                        set_as_completed: $(".set_as_completed").serializeArray(),
                        action: "setProjectCompleted"
                    }
                    ajaxCall('editProject', _data, function(json) {
                        var data = {
							main_filter: 'case',
                            list_filter: '<?php echo $list_filter?>',
                            list_filter_main: '<?php echo $list_filter_main?>',
                            responsibleperson_filter: $(".responsiblePersonFilter").val(),
                            projecttype_filter: $(".projectTypeFilter").val(),
                            projectcategory_filter: $(".projectCategoryFilter").val(),
                            invoiceperson_filter: $(".invoiceResponsibleFilter").val(),
                            search_filter: $('.searchFilter').val(),
                            search_by: $(".searchBy").val(),
                        };
                        loadView("list", data);
                    });
                }
            }).css({"z-index": 10000});
        })
	});

    $(".page-link").on('click', function(e) {
	    page = $(this).data("page");
	    e.preventDefault();
        var data = {
			main_filter: 'case',
            list_filter: '<?php echo $list_filter; ?>',
            responsibleperson_filter: $(".responsiblePersonFilter").val(),
            projecttype_filter: $(".projectTypeFilter").val(),
            search_filter: $('.searchFilter').val(),
            search_by: $(".searchBy").val(),
	        page: page
        };
        loadView("list", data);
    });
    $('.showMoreCustomersBtn').on('click', function(e) {
        page = parseInt(page)+1;
        e.preventDefault();
        var data = {
			main_filter: 'case',
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

	$(".orderBy").off("click").on("click", function(){
		var order_field = $(this).data("orderfield");
		var order_direction = $(this).data("orderdirection");

		var data = {

			main_filter: 'case',
			list_filter: '<?php echo $list_filter; ?>',
			responsibleperson_filter: $(".responsiblePersonFilter").val(),
			search_filter: $('.searchFilter').val(),
			search_by: $(".searchBy").val(),
			type_filter: '<?php echo $type_filter?>',
			order_field: order_field,
			order_direction: order_direction
		}
		loadView("list", data);
	})
</script>
<?php } ?>
