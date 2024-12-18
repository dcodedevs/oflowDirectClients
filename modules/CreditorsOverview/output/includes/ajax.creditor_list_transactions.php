<?php
	$customer_filter = $_GET['customer_filter'] ? $_GET['customer_filter'] : 0;
	$transaction_status = $_GET['transaction_status'] ? $_GET['transaction_status'] : 0;
	if($customer_filter > 0){
		$filters['customer_filter'] = $customer_filter;
		$filters['transaction_status'] = $transaction_status;
		$itemCount = get_transaction_count2($o_main, $cid, $mainlist_filter, $filters);

		if(isset($_GET['page'])) {
			$page = $_GET['page'];
		}
		if(intval($page) == 0){
			$page = 1;
		}
		$perPage = 500;
		$showing = $page * $perPage;
		$showMore = false;
		$currentCount = $itemCount;

		if($showing < $currentCount){
			$showMore = true;
		}
		$totalPages = ceil($currentCount/$perPage);

		$invoicesTransactions = get_transaction_list($o_main, $cid, $mainlist_filter, $filters, $page, $perPage);
		// $groupedTransactions = array();
		// $totalSum = 0;
		// if($mainlist_filter == "all_transactions"){
		// 	foreach($invoicesTransactions as $invoicesTransaction) {
		// 	$totalSum+=$invoicesTransaction['amount'];
		// 	$groupedTransactions[0][] = $invoicesTransaction;
		// }
		// } else {
		// 	foreach($invoicesTransactions as $invoicesTransaction) {
		// 		$totalSum+=$invoicesTransaction['amount'];
		// 		$customerId = $invoicesTransaction['external_customer_id'];
		// 		$s_sql = "SELECT * FROM customer WHERE creditor_customer_id = ? AND creditor_id = ? ORDER BY created DESC";
		// 		$o_query = $o_main->db->query($s_sql, array($customerId, $cid));
		// 		$customer = ($o_query ? $o_query->row_array() : array());
		// 		if($_GET['orderBy'] == 1){
		// 			$groupedTransactions[$customerId][] = $invoicesTransaction;
		// 		} else {
		// 			$customerName = $customer['name'];
		// 			$groupedTransactions[$customerName."_".$customerId][] = $invoicesTransaction;
		// 		}
		// 	}
		// 	ksort($groupedTransactions);
		// }
	}
	?>
	<div class="resultTableWrapper">
		<table class="gtable" id="gtable_search">
			<tr>
				<th class="gtable_cell"><?php echo $formText_Type_output;?></th>
				<th class="gtable_cell"><?php echo $formText_Date_output;?><br/><?php echo $formText_DueDate_output;?></th>
				<th class="gtable_cell"><?php echo $formText_InvoiceNr_output;?></th>
				<th class="gtable_cell"><?php echo $formText_KidNumber_output;?></th>
				<th class="gtable_cell"><?php echo $formText_LinkId_output;?></th>
				<th class="gtable_cell"><?php echo $formText_DateChanged_output;?></th>
				<th class="gtable_cell"><?php echo $formText_Amount_output;?></th>
				<th class="gtable_cell"><?php echo $formText_Status_output;?></th>
			</tr>
			<?php
			foreach($invoicesTransactions as $invoicesTransaction) {
				$totalSum+=$invoicesTransaction['amount'];
				$totalCustomerAmount += $invoicesTransaction['amount'];
				?>
				<tr>
					<td class="gtable_cell"><?php echo $invoicesTransaction['system_type'];?></td>
					<td class="gtable_cell"><?php echo date("d.m.Y", strtotime($invoicesTransaction['date']));?><br/><?php if($invoicesTransaction['due_date']!="" && $invoicesTransaction['due_date'] != "0000-00-00") echo date("d.m.Y", strtotime($invoicesTransaction['due_date']));?></td>
					<td class="gtable_cell"><?php echo $invoicesTransaction['invoice_nr'];?></td>
					<td class="gtable_cell"><?php echo $invoicesTransaction['kid_number'];?></td>
					<td class="gtable_cell"><?php echo $invoicesTransaction['link_id'];?></td>
					<td class="gtable_cell"><?php echo date("d.m.Y", strtotime($invoicesTransaction['date_changed']));?></td>
					<td class="gtable_cell"><?php echo $invoicesTransaction['amount'];?></td>
						<td class="gtable_cell"><?php
						if($invoicesTransaction['open']) {
							echo $formText_Open_output;
						} else {
							echo $formText_Closed_output;
						}
						?></td>
				</tr>
				<?php
			}
			?>			
		</table>
		
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
				<a href="#" data-page="<?php echo $single_page?>" class="page-link <?php if($single_page == $page) echo ' active';?>"><?php echo $single_page;?></a>
			<?php } ?>
	<?php } ?>
	</div>
	<style>
		.page-link.active{
			text-decoration: underline;
		}
	</style>
	<script type="text/javascript">
		$(function(){
			
			$(".page-link").on('click', function(e) {
				page = $(this).data("page");
				e.preventDefault();
				var data = {
					building_filter:$(".buildingFilter").val(),
					customergroup_filter: $(".customerGroupFilter").val(),
					mainlist_filter: '<?php echo $mainlist_filter; ?>',
					list_filter: '<?php echo $list_filter; ?>',
					cid: '<?php echo $cid;?>',
					search_filter: $('.searchFilter').val(),
					search_by: $(".searchBy").val(),
					order_field: '<?php echo $order_field;?>',
					order_direction: '<?php echo $order_direction;?>',
					transaction_status: '<?php echo $transaction_status?>',
					customer_filter: '<?php echo $customer_filter?>',
					page: page
				}
				loadView("creditor_list", data);
			});
		})
	</script>
	<?php
?>
