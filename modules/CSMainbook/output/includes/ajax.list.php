<?php
$sql_where = "";
$order_field =  isset($_GET['order_field']) ? $_GET['order_field'] : "id";
$order_direction = isset($_GET['order_direction']) ? $_GET['order_direction'] : 0;

if(isset($_GET['checksum'])){
	$_SESSION['checksum'] = 1;
} else {
	unset($_SESSION['checksum']);
}
$order_sql = " ORDER BY cs_mainbook_voucher.id";
if($order_field == "id"){
	$order_sql = " ORDER BY cs_mainbook_voucher.id";
} else if($order_field == "date") {
	$order_sql = " ORDER BY cs_mainbook_voucher.date";
}
if($order_direction == 0){
	$order_sql .= " DESC";
} else {
	$order_sql .= " ASC";
}

if(!isset($v_collecting_system_settings))
{
	$sql = "SELECT * FROM collecting_system_settings ORDER BY id";
	$result = $o_main->db->query($sql);
	$v_collecting_system_settings = $result ? $result->row_array(): array();
	if('0000-00-00' == $v_collecting_system_settings['accounting_close_last_date']) $v_collecting_system_settings['accounting_close_last_date'] = '';
}
$l_accounting_close_last_date = (''!=$v_collecting_system_settings['accounting_close_last_date']?strtotime($v_collecting_system_settings['accounting_close_last_date']):0);
$perPage = 200;
$page = intval($_GET['page']);
if($page == 0){
	$page = 1;
}
$offset = " ".($page-1)*$perPage;

$limit_sql = " LIMIT ".$perPage." OFFSET ".$offset;

$s_sql = "SELECT cs_mainbook_voucher.*, cmt_bank.amount FROM cs_mainbook_voucher
LEFT OUTER JOIN cs_mainbook_transaction cmt_bank ON cmt_bank.cs_mainbook_voucher_id = cs_mainbook_voucher.id AND cmt_bank.bookaccount_id = 1
WHERE cs_mainbook_voucher.content_status < 2 ";
$o_query = $o_main->db->query($s_sql.$sql_where." GROUP BY cs_mainbook_voucher.id ".$having_sql.$order_sql);
$totalCount = ($o_query ? $o_query->num_rows() : 0);
$totalPages = ceil($totalCount/$perPage);


$o_query = $o_main->db->query($s_sql.$sql_where." GROUP BY cs_mainbook_voucher.id ".$having_sql.$order_sql.$limit_sql);
$notProcessedCustomerList = ($o_query ? $o_query->result_array() : array());
$customerList = array();

foreach($notProcessedCustomerList as $notProcessedCustomer){
	$checksum = 0;
	$checksumLedger = 0;

	$s_sql = "SELECT cmt.* FROM cs_mainbook_transaction cmt WHERE cmt.cs_mainbook_voucher_id = '".$o_main->db->escape_str($notProcessedCustomer['id'])."'";
	$o_query = $o_main->db->query($s_sql);
	$transactions = ($o_query ? $o_query->result_array() : array());
	$hasOtherTransactionThanLedger = true;
	// foreach($transactions as $transaction) {
	// 	if($transaction['bookaccount_id'] != 1 && $transaction['bookaccount_id'] != 15 && $transaction['bookaccount_id'] != 16 && $transaction['bookaccount_id'] != 22) {
	// 		$hasOtherTransactionThanLedger = true;
	// 	}
	// }
	foreach($transactions as $transaction){
		if($transaction['bookaccount_id'] == 1){
			if($hasOtherTransactionThanLedger){
				$checksum += $transaction['amount'];
			}
			$checksumLedger+= $transaction['amount'];
		} else {
			if($transaction['bookaccount_id'] == 15 OR $transaction['bookaccount_id'] == 16 OR $transaction['bookaccount_id'] == 22) {
				$checksumLedger+= $transaction['amount'];
			} else {
				$checksum += $transaction['amount'];
			}
		}
	}

	$notProcessedCustomer['checksum'] = $checksum;
	$notProcessedCustomer['checksumLedger'] = $checksumLedger;
	$customerList[] = $notProcessedCustomer;
	if($checksum != 0 || $checksumLedger) {
		$checksumNotZeroCount++;
	}
}
include("list_filter.php");
?>
<div class="resultTableWrapper">
	<div class="gtable" id="gtable_search">
	    <div class="gtable_row">
			<div class="gtable_cell gtable_cell_head">
				<div class="orderBy <?php if($order_field == "id") echo 'orderActive';?>"  data-orderfield="id" data-orderdirection="<?php if($order_direction == 0) { echo 1; } else { echo 0; } ?>">
					<?php echo $formText_Id_Output;?>
					<div class="ordering">
						<div class="fas fa-caret-up" <?php if($order_field == "id" && $order_direction == 0) { echo 'style="display: none;"';}?>></div>
						<div class="fas fa-caret-down" <?php if($order_field == "id" && $order_direction == 1) { echo 'style="display: none;"';}?>></div>
					</div>
				</div>
			</div>
			<div class="gtable_cell gtable_cell_head">
				<div class="orderBy <?php if($order_field == "date") echo 'orderActive';?>"  data-orderfield="date" data-orderdirection="<?php if($order_direction == 0) { echo 1; } else { echo 0; } ?>">
					<?php echo $formText_Date_Output;?>
					<div class="ordering">
						<div class="fas fa-caret-up" <?php if($order_field == "date" && $order_direction == 0) { echo 'style="display: none;"';}?>></div>
						<div class="fas fa-caret-down" <?php if($order_field == "date" && $order_direction == 1) { echo 'style="display: none;"';}?>></div>
					</div>
				</div>
			</div>
	        <div class="gtable_cell gtable_cell_head">
				<div class="<?php if($order_field == "name") echo 'orderActive';?>" data-orderfield="name" data-orderdirection="<?php if($order_direction == 0) { echo 1; } else { echo 0; } ?>">
                    <?php echo $formText_Text_output;?>
                    <!-- <div class="ordering">
                        <div class="fas fa-caret-up" <?php if($order_field == "name" && $order_direction == 0) { echo 'style="display: none;"';}?>></div>
                        <div class="fas fa-caret-down" <?php if($order_field == "name" && $order_direction == 1) { echo 'style="display: none;"';}?>></div>
                    </div> -->
                </div>

			</div>
			<div class="gtable_cell gtable_cell_head">
				<div class="<?php if($order_field == "ehf") echo 'orderActive';?>"  data-orderfield="ehf" data-orderdirection="<?php if($order_direction == 0) { echo 1; } else { echo 0; } ?>">
					<?php echo $formText_Amount_output;?>
					<!-- <div class="ordering">
						<div class="fas fa-caret-up" <?php if($order_field == "ehf" && $order_direction == 0) { echo 'style="display: none;"';}?>></div>
						<div class="fas fa-caret-down" <?php if($order_field == "ehf" && $order_direction == 1) { echo 'style="display: none;"';}?>></div>
					</div> -->
				</div>
			</div>
			<div class="gtable_cell gtable_cell_head">
				<div class="<?php if($order_field == "ehf") echo 'orderActive';?>"  data-orderfield="ehf" data-orderdirection="<?php if($order_direction == 0) { echo 1; } else { echo 0; } ?>">
					<?php echo $formText_Checksum_output;?>
				</div>
			</div>
			<div class="gtable_cell gtable_cell_head">
				<div class="<?php if($order_field == "ehf") echo 'orderActive';?>"  data-orderfield="ehf" data-orderdirection="<?php if($order_direction == 0) { echo 1; } else { echo 0; } ?>">
					<?php echo $formText_ChecksumLedger_output;?>
				</div>
			</div>
			<div class="gtable_cell gtable_cell_head" style="width:300px">
				<div class="<?php if($order_field == "ehf") echo 'orderActive';?>"  data-orderfield="ehf" data-orderdirection="<?php if($order_direction == 0) { echo 1; } else { echo 0; } ?>">
					<?php echo $formText_CustomerInfo_output;?>
				</div>
			</div>
			<div class="gtable_cell gtable_cell_head" style="width:60px">
			</div>

	    </div>
	    <?php
		$closed_reasons = array($formText_FullyPaid_output, $formText_PayedWithLessAmountForgiven_output, $formText_ClosedWithoutAnyPayment_output,$formText_ClosedWithPartlyPayment_output,$formText_CreditedByCreditor_output,$formText_DrawnByCreditorToDeleteFees_output);

	    foreach($customerList as $v_row){
	        $b_closed = FALSE;
			if($v_row['date'] != "" && $v_row['date'] != "0000-00-00")
			{
				$b_closed = $l_accounting_close_last_date >= strtotime($v_row['date']);
			}
			$s_edit_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$v_row['id'];

			$sql = "SELECT * FROM collecting_company_cases WHERE id = '".$o_main->db->escape_str($v_row['case_id'])."'";
			$o_query = $o_main->db->query($sql);
			$caseData = $o_query ? $o_query->row_array() : array();

			$sql = "SELECT * FROM cs_mainbook_voucher WHERE case_id = '".$o_main->db->escape_str($v_row['case_id'])."'";
			$o_query = $o_main->db->query($sql);
			$connectedPaymentCount = $o_query ? $o_query->num_rows() : 0;
			$allCorrect = false;
			if($connectedPaymentCount == 1) {
				if($caseData['case_closed_date'] != "0000-00-00" AND  $caseData['case_closed_date'] != "" && intval($caseData['case_closed_reason']) == 0) {
					if($caseData['forgivenAmountOnMainClaim'] == 0 AND $caseData['forgivenAmountExceptMainClaim'] == 0 AND $caseData['overpaidAmount'] == 0){
						$allCorrect = true;
					}
				}
			}
		    ?>
	        <div class="gtable_row output-click-helper<?php if($allCorrect) echo ' greenBackground'?>" data-href="<?php echo $s_edit_link;?>">
				<div class="gtable_cell">
					<?php echo $v_row['id'];?>
				</div>
		        <div class="gtable_cell">
					<?php if($v_row['date'] != "" && $v_row['date'] != "0000-00-00") { echo date("d.m.Y", strtotime($v_row['date'])); }?>
				</div>
		        <div class="gtable_cell">
					<?php echo $v_row['text'];?>
				</div>
				<div class="gtable_cell">
					<?php echo number_format($v_row['amount'], 2, ",", "");?>
				</div>
				<div class="gtable_cell">
					<?php echo number_format($v_row['checksum'], 2, ",", "");?>
				</div>
				<div class="gtable_cell">
					<?php echo number_format($v_row['checksumLedger'], 2, ",", "");?>
				</div>
				<div class="gtable_cell">
					<?php
					echo $formText_PaymentsCountForCase_output;?> (<?php echo $connectedPaymentCount;?>)
					<div class="levelText">
						<?php
						if($caseData['collecting_case_surveillance_date'] != '0000-00-00' && $caseData['collecting_case_surveillance_date'] != ''){
							if(($caseData['case_closed_date'] == "0000-00-00" OR $caseData['case_closed_date'] == "")){
								echo $formText_Surveillance_output." (".$formText_Started_output." ".date("d.m.Y", strtotime($caseData['collecting_case_surveillance_date'])).")";
							} else {
								echo $formText_ClosedInSurveillance_output;
							}
						} else if($caseData['collecting_case_manual_process_date'] != '0000-00-00' && $caseData['collecting_case_manual_process_date'] != ''){
							if(($caseData['case_closed_date'] == "0000-00-00" OR $caseData['case_closed_date'] == "")){
								echo $formText_ManualProcess_output." (".$formText_Started_output." ".date("d.m.Y", strtotime($caseData['collecting_case_manual_process_date'])).")";
							} else {
								echo $formText_ClosedInManualProcess_output;
							}
						} else if($caseData['collecting_case_created_date'] != '0000-00-00' && $caseData['collecting_case_created_date'] != ''){
							if(($caseData['case_closed_date'] == "0000-00-00" OR $caseData['case_closed_date'] == "")){
								echo $formText_CollectingLevel_output." (".$formText_Started_output." ".date("d.m.Y", strtotime($caseData['collecting_case_created_date'])).")";
							} else {
								echo $formText_ClosedInCollectingLevel_output;
							}
						} else if($caseData['warning_case_created_date'] != '0000-00-00' && $caseData['warning_case_created_date'] != '') {
							if(($caseData['case_closed_date'] == "0000-00-00" OR $caseData['case_closed_date'] == "")){
								echo $formText_WarningLevel_output." (".$formText_Started_output." ".date("d.m.Y", strtotime($caseData['warning_case_created_date'])).")";
							} else {
								echo $formText_ClosedInWarningLevel_output;
							}
						}
						?>
					</div>
					<?php
					if($caseData['case_closed_date'] != "0000-00-00" && $caseData['case_closed_date'] != ""){
						echo $formText_ClosedDate_output." ".date("d.m.Y", strtotime($caseData['case_closed_date']))."<br/>";
						if($caseData['case_closed_reason'] >= 0){
							echo $closed_reasons[$caseData['case_closed_reason']]."<br/>";
						}
					}
					echo "<br/>";
					echo $formText_ForgivenAmountOnMainClaim_output." ".number_format($caseData['forgivenAmountOnMainClaim'], 2, ",", "")."<br/>";
					echo $formText_ForgivenAmountExceptMainClaim_output." ".number_format($caseData['forgivenAmountExceptMainClaim'], 2, ",", "")."<br/>";
					echo $formText_OverpaidAmount_output." ".number_format($caseData['overpaidAmount'], 2, ",", "");
					?>
				</div>
				<div class="gtable_cell">
					<span class="glyphicon glyphicon-pencil editVatCode" data-bookaccount-id="<?php echo $v_row['id'];?>"></span>
					<?php if(!$b_closed) { ?>
					<span class="glyphicon glyphicon-trash deleteVatCode" data-bookaccount-id="<?php echo $v_row['id'];?>"></span>
					<?php } ?>
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
		<?php foreach($pages as $single_page) {?>
			<a href="#" data-page="<?php echo $single_page?>" class="page-link<?php if($single_page == $page) echo ' active';?>"><?php echo $single_page;?></a>
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
	            id: $(this).data("bookaccount-id")
	        };
	        ajaxCall('editVoucher', data, function(json) {
	            $('#popupeditboxcontent').html('');
	            $('#popupeditboxcontent').html(json.html);
	            out_popup = $('#popupeditbox').bPopup(out_popup_options);
	            $("#popupeditbox:not(.opened)").remove();
	        });
		})
		$(".deleteVatCode").off("click").on("click", function(e){
			e.preventDefault();
	        var data = {
	            id: $(this).data("bookaccount-id"),
				action: "deleteVoucher"
	        };
			bootbox.confirm({
				message:"<?php echo $formText_ConfirmDelete_Output;?>",
				buttons:{confirm:{label:"<?php echo $formText_Yes_Output;?>"},cancel:{label:"<?php echo $formText_No_Output;?>"}},
				callback: function(result){
					if(result)
					{

						ajaxCall('editVoucher', data, function(json) {
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
<style>
.levelText {
	font-weight: bold;
}
.page-link.active {
	text-decoration: underline;
	font-weight: bold;
}
.gtable_row.greenBackground .gtable_cell {
	background-color: #90EE90;
}
</style>
