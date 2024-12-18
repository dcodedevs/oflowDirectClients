<?php
$default_list = "all";
// if(count($customer_listtabs_basisconfig) > 0) {
// 	$default_list = $customer_listtabs_basisconfig[0]['id'];
// }
$s_sql = "SELECT * FROM employee WHERE email = ?";
$o_query = $o_main->db->query($s_sql, array($variables->loggID));
if($o_query && $o_query->num_rows()>0){
    $currentEmployee = $o_query->row_array();
}

$filtersList = array("search_filter","search_by", "responsibleperson_filter");

if ($_POST['list_filter']) $_GET['list_filter'] = $_POST['list_filter'];
foreach($filtersList as $filterName){
	if ($_POST[$filterName]) $_GET[$filterName] = $_POST[$filterName];
}

$list_filter = $_GET['list_filter'] ? ($_GET['list_filter']) : $default_list;
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
	if($filterName == "search_filter"){
		$filters[$filterName] = array($search_by, ${$filterName});
	} else if($filterName != "search_by") {
		$filters[$filterName] = ${$filterName};
	}
}
require_once __DIR__ . '/functions.php';
if(!function_exists("APIconnectAccount")) include_once(__DIR__."/../../input/includes/APIconnect.php");

$s_sql = "SELECT * FROM moduledata WHERE name = 'Orders'";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
    $orders_module_id_find = $o_query->row_array();
    $orders_module_id = $orders_module_id_find["uniqueID"];
}

$itemCount = get_customer_list_count($o_main, $list_filter, $filters);

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
	        <div class="gtable_cell gtable_cell_head c1"><?php echo $formText_Id_output;?></div>
	        <div class="gtable_cell gtable_cell_head"><?php echo $formText_Name_output;?></div>
            <div class="gtable_cell gtable_cell_head"></div>
	    </div>
<?php } ?>
    <?php
    foreach($customerList as $v_row){
        $s_edit_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$v_row['id'];
        ?>
        <div class="gtable_row output-click-helper" data-href="<?php echo $s_edit_link;?>">
        <?php
      	// Show default columns
      	 ?>
	        <div class="gtable_cell c1"><?php echo $v_row['id'];?></div>
	        <div class="gtable_cell"><?php echo $v_row['name'];?></div>
            <div class="gtable_cell output-list-buttons">
    			<a href="#"><span class="glyphicon glyphicon-duplicate duplicate_cover" data-cover-id="<?php echo $v_row['id']?>"></span></a>
    			<a href="#"><span class="glyphicon glyphicon-pencil edit_cover" data-cover-id="<?php echo $v_row['id']?>"></span></a>
    			<a href="#" class="output-delete-list-item" data-cover-id="<?php echo $v_row['id'];?>"><span class="glyphicon glyphicon-trash"></span></a>

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
			$(this).removeClass('opened');
		}
	};
	$(function() {
		$(document).off('click', '.output-click-helper').on('click', '.output-click-helper', function(e){
			if(e.target.nodeName == 'DIV' && !$(e.target).hasClass("output-list-buttons") && $(e.target).parents(".output-list-buttons").length == 0){
                 fw_load_ajax($(this).data('href'),'',true);
            }
		});
	});

    $(".edit_cover").on('click', function(e){
    	e.preventDefault();
    	var data = { processId: $(this).data("cover-id") };
        ajaxCall('edit_cover', data, function(obj) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(obj.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    });
	$(".duplicate_cover").on('click', function(e){
    	e.preventDefault();
    	var data = { processId: $(this).data("cover-id"), action:"duplicate" };
        ajaxCall('edit_cover', data, function(obj) {			
			var data = {
				list_filter: '<?php echo $list_filter;?>',
			};
			loadView('list', data);
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
    $('.output-delete-list-item').off('click').on('click', function(e) {
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
					        processId: $_this.data('cover-id'),
					        action: 'deleteProcess',
					    };
					    ajaxCall('edit_cover', data, function(json) {
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
									list_filter: '<?php echo $list_filter;?>',
								};
								loadView('list', data);
                            }
					    });
                    }
                    fw_click_instance = false;
                }
            });
        }
    });
</script>
<?php } ?>
