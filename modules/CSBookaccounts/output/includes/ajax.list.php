<?php
$sql_where = "";

$s_sql = "SELECT * FROM cs_bookaccount WHERE content_status < 2 ".$sql_where." ORDER BY number ASC";
$o_query = $o_main->db->query($s_sql);
$customerList = ($o_query ? $o_query->result_array() : array());

include("list_filter.php");
?>
<div class="resultTableWrapper">
	<div class="gtable" id="gtable_search">
	    <div class="gtable_row">
			<div class="gtable_cell gtable_cell_head">
				<div class="orderBy <?php if($order_field == "number") echo 'orderActive';?>" data-orderfield="number" data-orderdirection="<?php if($order_direction == 0) { echo 1; } else { echo 0; } ?>">
                    <?php echo $formText_Number_output;?>
                    <!-- <div class="ordering">
                        <div class="fas fa-caret-up" <?php if($order_field == "name" && $order_direction == 0) { echo 'style="display: none;"';}?>></div>
                        <div class="fas fa-caret-down" <?php if($order_field == "name" && $order_direction == 1) { echo 'style="display: none;"';}?>></div>
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
				<div class="orderBy <?php if($order_field == "percent") echo 'orderActive';?>"  data-orderfield="percent" data-orderdirection="<?php if($order_direction == 0) { echo 1; } else { echo 0; } ?>">
					<?php echo $formText_IsDebitorLedger_Output;?>
					<!-- <div class="ordering">
						<div class="fas fa-caret-up" <?php if($order_field == "percent" && $order_direction == 0) { echo 'style="display: none;"';}?>></div>
						<div class="fas fa-caret-down" <?php if($order_field == "percent" && $order_direction == 1) { echo 'style="display: none;"';}?>></div>
					</div> -->
				</div>
			</div>
			<div class="gtable_cell gtable_cell_head">
				<div class="orderBy <?php if($order_field == "ehf") echo 'orderActive';?>"  data-orderfield="ehf" data-orderdirection="<?php if($order_direction == 0) { echo 1; } else { echo 0; } ?>">
					<?php echo $formText_IsCreditorLedger_output;?>
					<!-- <div class="ordering">
						<div class="fas fa-caret-up" <?php if($order_field == "ehf" && $order_direction == 0) { echo 'style="display: none;"';}?>></div>
						<div class="fas fa-caret-down" <?php if($order_field == "ehf" && $order_direction == 1) { echo 'style="display: none;"';}?>></div>
					</div> -->
				</div>
			</div>
			<div class="gtable_cell gtable_cell_head">
				<div class="orderBy <?php if($order_field == "ehf") echo 'orderActive';?>"  data-orderfield="ehf" data-orderdirection="<?php if($order_direction == 0) { echo 1; } else { echo 0; } ?>">
					<?php echo $formText_SummarizeOnLedger_Output;?>
					<!-- <div class="ordering">
						<div class="fas fa-caret-up" <?php if($order_field == "ehf" && $order_direction == 0) { echo 'style="display: none;"';}?>></div>
						<div class="fas fa-caret-down" <?php if($order_field == "ehf" && $order_direction == 1) { echo 'style="display: none;"';}?>></div>
					</div> -->
				</div>
			</div>
			<div class="gtable_cell gtable_cell_head">
			</div>

	    </div>
	    <?php
		$summarize_on_ledger_array = array($formText_None_output,$formText_CollectingCompany_output, $formText_Creditor_output, $formText_Debitor_output);
	    foreach($customerList as $v_row){
	        // $s_edit_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$v_row['id']."&view=".$list_filter_main;

	        ?>
	        <div class="gtable_row">
				<div class="gtable_cell">
					<?php echo $v_row['number'];?>
				</div>
		        <div class="gtable_cell">
					<?php echo $v_row['name'];?>
				</div>
		        <div class="gtable_cell">
					<?php if($v_row['is_debitor_ledger']) { echo $formText_Yes_output; } else { echo $formText_No_Output;}?>
				</div>
				<div class="gtable_cell">
					<?php if($v_row['is_creditor_ledger']) { echo $formText_Yes_output; } else { echo $formText_No_Output;}?>
				</div>
				<div class="gtable_cell">
					<?php echo $summarize_on_ledger_array[intval($v_row['summarize_on_ledger'])];?>
				</div>

				<div class="gtable_cell">
					<span class="glyphicon glyphicon-pencil editVatCode" data-bookaccount-id="<?php echo $v_row['id'];?>"></span>
					<span class="glyphicon glyphicon-trash deleteVatCode" data-bookaccount-id="<?php echo $v_row['id'];?>"></span>
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
	            bookaccountId: $(this).data("bookaccount-id")
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
	            bookaccountId: $(this).data("bookaccount-id"),
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
            building_filter: $(".buildingFilter").val(),
            customergroup_filter: $(".customerGroupFilter").val(),
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
