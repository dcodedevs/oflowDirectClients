<?php
$default_list = "default";
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

$sql = "SELECT * FROM process_step_types ORDER BY name ASC";
$o_query = $o_main->db->query($sql);
$types = $o_query ? $o_query->result_array() : array();

$itemCount = get_customer_list_count($o_main, $list_filter, $filters);

$default_count = get_customer_list_count($o_main, "default", $filters);
$customized_count = get_customer_list_count($o_main, "customized", $filters);

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
$processedCompanyList = array();
$processedPersonList = array();

foreach($customerList as $single_customer){
	if($single_customer['available_for'] == 1){
		$processedPersonList[$single_customer['process_step_type_id']][] = $single_customer;
	} else if($single_customer['available_for'] == 2){
		$processedCompanyList[$single_customer['process_step_type_id']][] = $single_customer;
	}
}

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

<?php } ?>
	<div class="available_for_title"><?php echo $formText_Person_output;?></div>
	<?php foreach($types as $type){ ?>
		<div class="type_title"><?php echo $type['name'];?>  <span class="edit_process_order" data-person-type="1" data-id="<?php echo $type['id'];?>"><?php echo $formText_EditProcessOrder_output;?></span></div>

		<div class="gtable" id="gtable_search">
			<div class="gtable_row">
				<div class="gtable_cell gtable_cell_head c1"><?php echo $formText_Id_output;?></div>
				<div class="gtable_cell gtable_cell_head"><?php echo $formText_Name_output;?></div>
				<?php if($list_filter == "customized") { ?>
					<div class="gtable_cell gtable_cell_head"><?php echo $formText_Creditor_output;?></div>
				<?php } ?>
				<div class="gtable_cell gtable_cell_head"><?php echo $formText_Published_output;?></div>
				<div class="gtable_cell gtable_cell_head"><?php echo $formText_MoveTo_output;?></div>
				<div class="gtable_cell gtable_cell_head"><?php echo $formText_AfterDays_output;?></div>
			</div>
	    <?php
		$customerList = $processedPersonList[$type['id']];
	    foreach($customerList as $v_row){
			$s_sql = "SELECT * FROM collecting_cases_collecting_process WHERE id = ?";
			$o_query = $o_main->db->query($s_sql, array($v_row['collecting_process_move_to']));
			$collecting_process = ($o_query ? $o_query->row_array() : 0);
			
			$s_sql = "SELECT * FROM collecting_cases_collecting_process WHERE id = ?";
			$o_query = $o_main->db->query($s_sql, array($v_row['alternative_process_move_to']));
			$alternative_process = ($o_query ? $o_query->row_array() : 0);

	        $s_edit_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$v_row['id'];

			$sql = "SELECT * FROM process_step_types WHERE id = ?";
			$o_query = $o_main->db->query($sql, array($v_row['process_step_type_id']));
			$step_type = $o_query ? $o_query->row_array() : array();
			?>
	        <div class="gtable_row output-click-helper" data-href="<?php echo $s_edit_link;?>">
	        <?php
	      	// Show default columns
	      	 ?>
		        <div class="gtable_cell c1"><?php echo $v_row['id'];?></div>
		        <div class="gtable_cell"><?php if($v_row['fee_level_name'] != "") {
					echo $v_row['fee_level_name']." ".$step_type['name']; } else { echo $v_row['name'];}?></div>
				<?php if($list_filter == "customized") { ?>
			        <div class="gtable_cell"><?php echo $v_row['creditorName'];?></div>
				<?php } ?>
		        <div class="gtable_cell"><?php echo $v_row['published'];?></div>
				<div class="gtable_cell"><?php echo $collecting_process['name'];
				if($alternative_process) echo "<br/>".$alternative_process['name'];?></div>
				<div class="gtable_cell"><?php echo $v_row['days_after_due_date_move_to_collecting'];?></div>
	        </div>
	    <?php } ?>
		</div>
	<?php } ?>
	<br/>
	<div class="available_for_title"><?php echo $formText_Company_output;?></div>
	<?php foreach($types as $type){ ?>
		<div class="type_title"><?php echo $type['name'];?>  <span class="edit_process_order" data-person-type="2" data-id="<?php echo $type['id'];?>"><?php echo $formText_EditProcessOrder_output;?></span></div>

		<div class="gtable" id="gtable_search">
			<div class="gtable_row">
				<div class="gtable_cell gtable_cell_head c1"><?php echo $formText_Id_output;?></div>
				<div class="gtable_cell gtable_cell_head"><?php echo $formText_Name_output;?></div>
				<?php if($list_filter == "customized") { ?>
					<div class="gtable_cell gtable_cell_head"><?php echo $formText_Creditor_output;?></div>
				<?php } ?>
				<div class="gtable_cell gtable_cell_head"><?php echo $formText_Published_output;?></div>
				<div class="gtable_cell gtable_cell_head"><?php echo $formText_MoveTo_output;?></div>
				<div class="gtable_cell gtable_cell_head"><?php echo $formText_AfterDays_output;?></div>
			</div>
	    <?php
		$customerList = $processedCompanyList[$type['id']];
	    foreach($customerList as $v_row){			
			$s_sql = "SELECT * FROM collecting_cases_collecting_process WHERE id = ?";
			$o_query = $o_main->db->query($s_sql, array($v_row['collecting_process_move_to']));
			$collecting_process = ($o_query ? $o_query->row_array() : 0);			
			$s_sql = "SELECT * FROM collecting_cases_collecting_process WHERE id = ?";
			$o_query = $o_main->db->query($s_sql, array($v_row['alternative_process_move_to']));
			$alternative_process = ($o_query ? $o_query->row_array() : 0);

			$s_sql = "SELECT * FROM collecting_cases_collecting_process WHERE id = ?";
			$o_query = $o_main->db->query($s_sql, array($v_row['collecting_process_move_to']));
			$collecting_process = ($o_query ? $o_query->row_array() : 0);
	        $s_edit_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$v_row['id'];

			$sql = "SELECT * FROM process_step_types WHERE id = ?";
			$o_query = $o_main->db->query($sql, array($v_row['process_step_type_id']));
			$step_type = $o_query ? $o_query->row_array() : array();
			?>
	        <div class="gtable_row output-click-helper" data-href="<?php echo $s_edit_link;?>">
	        <?php
	      	// Show default columns
	      	 ?>
		        <div class="gtable_cell c1"><?php echo $v_row['id'];?></div>
		        <div class="gtable_cell"><?php if($v_row['fee_level_name'] != "") {
					echo $v_row['fee_level_name']." ".$step_type['name']; } else { echo $v_row['name'];}?></div>
				<?php if($list_filter == "customized") { ?>
			        <div class="gtable_cell"><?php echo $v_row['creditorName'];?></div>
				<?php } ?>
		        <div class="gtable_cell"><?php echo $v_row['published'];?></div>
				<div class="gtable_cell"><?php echo $collecting_process['name'];
				if($alternative_process) echo "<br/>".$alternative_process['name'];?></div>
				<div class="gtable_cell"><?php echo $v_row['days_after_due_date_move_to_collecting'];?></div>
	        </div>
	    <?php } ?>
		</div>
	<?php } ?>

	<?php if (!$rowOnly) { ?>
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
			if(e.target.nodeName == 'DIV') fw_load_ajax($(this).data('href'),'',true);
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
	$(".edit_process_order").off("click").on("click", function(e){
		e.preventDefault();
		var data = {
			type_id: $(this).data('id'),
			person_type: $(this).data("person-type")
		};
		ajaxCall('edit_process_order', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	})
</script>
<?php } ?>
