<?php
$default_list = "unhandled";
$default_list_main = "";

$s_sql = "select * from bookaccount_accountconfig";
$o_query = $o_main->db->query($s_sql);
$v_bookaccount_accountconfig = ($o_query ? $o_query->row_array() : array());

$sql_where = "";
if($company_product_set_id > 0) {
	$sql_where .= " AND bookaccount.company_product_set_id = '".$o_main->db->escape_str($company_product_set_id)."'";
} else if($company_product_set_id == 0) {
	$sql_where .= " AND (bookaccount.company_product_set_id = 0 OR bookaccount.company_product_set_id is null)";
} else {
	$s_sql = "SELECT * FROM company_product_set ORDER BY name ASC";
	$o_query = $o_main->db->query($s_sql);
	$company_product_sets = $o_query ? $o_query->result_array() : array();
	if(count($company_product_sets) > 0) {
		$sql_where .= " AND bookaccount.company_product_set_id = '".$o_main->db->escape_str($company_product_set_id)."'";
	}
}

$s_sql = "SELECT * FROM bookaccount WHERE 1=1 ".$sql_where." ORDER BY created ASC";
$o_query = $o_main->db->query($s_sql);
$customerList = ($o_query ? $o_query->result_array() : array());

include("list_filter.php");
?>
<div class="resultTableWrapper">
	<div class="gtable" id="gtable_search">
	    <div class="gtable_row">
			<div class="gtable_cell gtable_cell_head">
				<div class="orderBy <?php if($order_field == "accountnr") echo 'orderActive';?>" data-orderfield="accountnr" data-orderdirection="<?php if($order_direction == 0) { echo 1; } else { echo 0; } ?>">
					<?php echo $formText_BookaccountNr_output;?>
					<!-- <div class="ordering">
						<div class="fas fa-caret-up" <?php if($order_field == "vatcode" && $order_direction == 0) { echo 'style="display: none;"';}?>></div>
						<div class="fas fa-caret-down" <?php if($order_field == "vatcode" && $order_direction == 1) { echo 'style="display: none;"';}?>></div>
					</div> -->
				</div>
			</div>
	        <div class="gtable_cell gtable_cell_head">
				<div class="orderBy <?php if($order_field == "name") echo 'orderActive';?>" data-orderfield="name" data-orderdirection="<?php if($order_direction == 0) { echo 1; } else { echo 0; } ?>">
                    <?php echo $formText_Name_output;?>
                    <!-- <div class="ordering">
                        <div class="fas fa-caret-up" <?php if($order_field == "name" && $order_direction == 0) { echo 'style="display: none;"';}?>></div>
                        <div class="fas fa-caret-down" <?php if($order_field == "name" && $order_direction == 1) { echo 'style="display: none;"';}?>></div>
                    </div> -->
                </div>

			</div>
			<div class="gtable_cell gtable_cell_head">
			</div>

	    </div>
	    <?php
	    foreach($customerList as $v_row){
	        // $s_edit_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$v_row['id']."&view=".$list_filter_main;

	        ?>
	        <div class="gtable_row">
				<div class="gtable_cell">
					<?php echo $v_row['accountNr'];?>
				</div>
		        <div class="gtable_cell">
					<?php echo $v_row['name'];?>
				</div>
				<div class="gtable_cell">
					<span class="glyphicon glyphicon-pencil editVatCode" data-bookaccountcode="<?php echo $v_row['id'];?>"></span>
					<span class="glyphicon glyphicon-trash deleteVatCode" data-bookaccountcode="<?php echo $v_row['id'];?>"></span>
				</div>
	        </div>
		<?php } ?>
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
</div>
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
		$(".editVatCode").off("click").on("click", function(e){
			e.preventDefault();
	        var data = {
	            bookaccountId: $(this).data("bookaccountcode")
	        };
	        ajaxCall('editBookaccount', data, function(json) {
	            $('#popupeditboxcontent').html('');
	            $('#popupeditboxcontent').html(json.html);
	            out_popup = $('#popupeditbox').bPopup(out_popup_options);
	            $("#popupeditbox:not(.opened)").remove();
	        });
		})
		$(".deleteVatCode").off("click").on("click", function(e){
			e.preventDefault();
	        var data = {
	            bookaccountId: $(this).data("bookaccountcode"),
				action: "deleteBookaccount"
	        };
			bootbox.confirm({
				message:"<?php echo $formText_ConfirmDelete_Output;?>",
				buttons:{confirm:{label:"<?php echo $formText_Yes_Output;?>"},cancel:{label:"<?php echo $formText_No_Output;?>"}},
				callback: function(result){
					if(result)
					{

						ajaxCall('editBookaccount', data, function(json) {
							var data = {
				                set_id: '<?php echo $company_product_set_id;?>'
							};
							loadView("list", data);
						});
					}
					fw_click_instance = false;
				},
			});
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
