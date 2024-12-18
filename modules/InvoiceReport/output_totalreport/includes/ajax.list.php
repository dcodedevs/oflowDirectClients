<?php
$activateMultiOwnerCompanies = false;

$s_sql = "SELECT * FROM ownercompany_accountconfig";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
	$ownercompanyAccountconfig = $o_query->row_array();
	$activateMultiOwnerCompaniesItem = intval($ownercompanyAccountconfig['max_number_ownercompanies']);
	if ($activateMultiOwnerCompaniesItem > 1) {
		$activateMultiOwnerCompanies = true;
	}
}

$generateReport = isset($_GET['generateReport']) ? 1 : 0;
$viewReport = isset($_GET['reportId']) ? intval($_GET['reportId']) : 0;


if($generateReport) {
	$s_sql = "SELECT customer.*, invoice.*, ownercompany.name as ownercompanyName, customer.name as customerName FROM invoice 
	LEFT OUTER JOIN customer ON customer.id = invoice.customerId
	LEFT OUTER JOIN ownercompany ON ownercompany.id = invoice.ownercompany_id 
	WHERE (invoice.totalreportId is null OR invoice.totalreportId = 0) ORDER BY invoice.id";
	$o_query = $o_main->db->query($s_sql);

} else if($viewReport) {
	$s_sql = "SELECT customer.*, invoice.*, ownercompany.name as ownercompanyName, customer.name as customerName FROM invoice 
	LEFT OUTER JOIN customer ON customer.id = invoice.customerId
	LEFT OUTER JOIN ownercompany ON ownercompany.id = invoice.ownercompany_id 
	WHERE invoice.totalreportId = ? ORDER BY invoice.id";
	$o_query = $o_main->db->query($s_sql, $viewReport);
}

$page = 1;
if(isset($_GET['page'])) {
	$page = intval($_GET['page']);
	if($page == 0){
		$page = 1;
	}
}
$perPage = 50;

$totalCount = $o_query ? $o_query->num_rows() : 0;
$totalPages = round($totalCount / $perPage);

$offset = $perPage * $page - $perPage;
$initSql = $s_sql;
$pagination = " LIMIT ".$perPage ." OFFSET ".$offset;
if($generateReport) {
	$s_sql = $initSql.$pagination;
	$o_query = $o_main->db->query($s_sql);
} else if($viewReport) {
	$s_sql = $initSql.$pagination;
	$o_query = $o_main->db->query($s_sql, $viewReport);
}	

$invoices = $o_query ? $o_query->result_array() : array();

if($generateReport) {
	if(count($invoices) > 0) {
		$s_sql = "INSERT INTO totalreport SET created = NOW(), createdBy = ?";
		$o_query = $o_main->db->query($s_sql, $variables->loggID);
		$totalreportId = $o_main->db->insert_id();
		if($totalreportId > 0){
			$invoiceIdsToUpdate = array();
			for($xpage = 1; $xpage <= $totalPages+1; $xpage++){
				$offset = $perPage * $xpage - $perPage;
				$pagination = " LIMIT ".$perPage ." OFFSET ".$offset;

				$s_sql = "SELECT id FROM invoice WHERE (invoice.totalreportId is null OR invoice.totalreportId = 0) ".$pagination;
				$o_query = $o_main->db->query($s_sql);
				$currentPageInvoices = $o_query ? $o_query->result_array() : array();
				foreach($currentPageInvoices as $currentPageInvoice){
					array_push($invoiceIdsToUpdate, $currentPageInvoice['id']);
				}
			}
			foreach($invoiceIdsToUpdate as $invoiceIdToUpdate) {			
				$s_sql = "UPDATE invoice SET totalreportId = ? WHERE id = ?";
				$o_query = $o_main->db->query($s_sql, array($totalreportId, $invoiceIdToUpdate));
			}
		}
	}
}
?>

<div class="mainResult">
	<div class="p_pageDetails">		
		<div class="p_pageDetailsTitle"><?php echo $formText_ReportHistory_output;?></div>		
		<div class="p_contentBlock">
			<?php

				$s_sql = "SELECT * FROM totalreport ORDER BY id DESC";
				$o_query = $o_main->db->query($s_sql);
				$reports = $o_query ? $o_query->result_array() : array();
				?>
				<table class="table table-bordered">
					<tr>
						<td><?php echo $formText_ReportId_output;?></td>
						<td><?php echo $formText_Created_output?></td>
						<td><?php echo $formText_InvoicesCount_output;?></td>
						<td></td>
					</tr>
					<?php
					foreach($reports as $report) {
						$s_sql = "SELECT invoice.id FROM invoice 
						WHERE invoice.totalreportId = ? ORDER BY invoice.id";

						$o_query = $o_main->db->query($s_sql, $report['id']);
						$invoiceCount = $o_query ? $o_query->num_rows() : 0;
						?>
						<tr class="<?php if($viewReport == $report['id']) { echo 'activeReportLine';} ?>">
							<td><?php echo $report['id']?></td>
							<td><?php echo date("d.m.Y H:m:i", strtotime($report['created']));?></td>
							<td><?php echo $invoiceCount?></td>
							<td>
								<?php if($viewReport == $report['id']) {
									echo $formText_CurrentReport_output;
								} else { ?>
									<a href="<?php echo $baselink?>&reportId=<?php echo $report['id'];?>"><?php echo $formText_ViewReport_output;?></a>
								<?php } ?>
							</td>
						</tr>
						<?php
					}
					?>
				</table>
				<?php
			?>
			<?php 
			if(!$generateReport){
				$s_sql = "SELECT invoice.id FROM invoice 
				WHERE (invoice.totalreportId is null OR invoice.totalreportId = 0) ORDER BY invoice.id";
				$o_query = $o_main->db->query($s_sql, $report['id']);
				$notReportedInvoices = $o_query ? $o_query->num_rows() : 0;
				?>
				<div>
					<?php
					echo $formText_NumberOfNewlyAddedInvoices_output.": ".$notReportedInvoices;
					?>
				</div>
				<?php
			}
			?>
			<div class="createReport output-btn blue">
				<a href="<?php echo $baselink?>&generateReport=1"><?php echo $formText_CreateReportForNewlyAddedElements_output;?></a>
			</div>
		</div>
	</div>
	<?php 
	if($generateReport || $viewReport){
	?>
	<div class="p_pageDetails">
		<div class="p_pageDetailsTitle"><?php echo $formText_Invoices_output;?></div>
		<div class="p_contentBlock">
		
			<?php
			if($generateReport){
				$baselink .= "&reportId=".$totalreportId;
			} else {
				$baselink .= "&reportId=".$viewReport;
			}

			if($totalPages > 1)
			{
				?><ul class="pagination pagination-sm" style="margin:0;"><?php
				for($l_x = 0; $l_x < $totalPages; $l_x++)
				{
					if($l_x < 1 || ($l_x > ($page - 7) && $l_x < ($page + 7)) || $l_x >= ($totalPages - 1))
					{
						$b_print_space = true;
						?><li<?php echo ($page==$l_x+1 ? ' class="active"' : '');?>><a href="<?php echo $baselink;?>&page=<?php echo $l_x+1?>"><?php echo ($l_x+1);?></a></li><?php
					} else if($b_print_space) {
						$b_print_space = false;
						?><li><a onClick="javascript:return false;">...</a></li><?php
					}
				}
				?></ul><?php
			}?>
			<?php

			$v_address_format = array('paStreet', 'paCity', 'paCountry', 'paPostalNumber');

			foreach($invoices as $invoice) {

				$s_address = "";
				foreach($v_address_format as $s_key)
				{
					if($invoice[$s_key] != "")
					{
						if($s_address != "") $s_address .= ", ";
						$s_address .= $invoice[$s_key];
					}
				}

				$contactPersons = array();
					$s_sql = "SELECT * FROM contactperson WHERE customerId = ?";
					$o_query = $o_main->db->query($s_sql, array($invoice['customerId']));
					$contactPersonData = array();
					if($o_query && $o_query->num_rows()>0){
						$contactPersonData = $o_query->row_array();
					}
				$contactPersons[$invoice['customerId']] = $contactPersonData['name'];
				?>
				<div class="invoiceWrapper">
					<div class="item-title">
						<div class="title">
							<?php echo $invoice['customerName'];?>
				            <?php if ($activateMultiOwnerCompanies):
				            	?>
					            <div>
					                <small>
					                    (<?php echo $formText_OwnerCompany_output; ?>: <?php echo $invoice['ownercompanyName']; ?>)
					                </small>
					            </div>
				            <?php endif; ?>   
				        </div>
						<div class="out-address"><?php echo $s_address;?></div>
						<?php if(count($contactPersons) > 0 ) { ?>
						<div class="out-ref">Ref: <?php echo join(', ', $contactPersons); ?> </div>
						<?php } ?>
						<br clear="all">
					</div>
					<div class="item-order">
						<table class="table table-condensed">
							<thead>
								<tr>
									<th><?php echo $formText_Text_Output;?></th>
									<th><?php echo $formText_Price_Output;?></th>
									<th><?php echo $formText_Amount_Output;?></th>
									<th><?php echo $formText_Discount_Output;?></th>
									<th><?php echo $formText_Vat_Output;?></th>
									<th>&nbsp;</th>
									<th class="text-right"><?php echo $formText_TotalPrice_Output;?></th>
								</tr>
							</thead>
							<tbody>
								<?php
								$dateValShow = date("d.m.Y", strtotime($invoice['invoiceDate']));
								$dateExpireShow = date("d.m.Y", strtotime($invoice['dueDate']));

			                    if ($dateValShow) {
			                        echo '<span style="margin: 5px 5px 5px 0; display:inline-block;"><b>' . $formText_InvoiceDate . '</b>: ' . $dateValShow . '</span>';
			                    }
			                    if ($dateExpireShow) {
			                        echo '<span style="margin: 5px 5px 5px 0; display:inline-block;"><b>' . $formText_DueDate . '</b>: ' . $dateExpireShow . '</span>';
			                    }
			                    
			                    $s_sql = "SELECT * FROM orders WHERE invoiceNumber = '".$invoice['id']."'";
								$o_query = $o_main->db->query($s_sql);
								$v_orders = $o_query ? $o_query->result_array(): array();

								foreach($v_orders as $v_order)
								{

									$orderId = $v_order['id'];  

									$bookaccountNr = $v_order['bookAccountNr'];
		        					$vatCode = $v_order['vatcodeId'];
								    $s_sql = "SELECT * FROM vatcode WHERE vatCode = ?";
								    $o_query = $o_main->db->query($s_sql, array($vatCode));
								    $vatItem = $o_query ? $o_query->row_array() : array();
								    $vatPercent = $vatItem['percentRate'];

									// Preping return data
									$vatData = array(
										'percentRate' => $vatPercent,
										'code' => $vatCode,
										'bookAccountNr' => $bookaccountNr
									);

									$orderHasError = (count($errors[$orderId]) ? true : false);
									// if(!$orderHasError){
									//  	$orderHasError = (count($customer_errors) ? true : false);
									// }
									if ($currentCurrencyId != $v_order['currencyId'] && $isMultiCurrencyAccount) continue;

									// Calc totals
									$vat = $v_order['priceTotal'] * ($vatData['percentRate']/100);
									if (!$orderHasError){
										$l_sum += $v_order['priceTotal'];
										$vatTotal += $vat;
									}
									?><tr <?php echo ($orderHasError ? 'class="orderHasError"' : ''); ?>>

										<td><?php echo $v_order['articleName']; ?></td>
										<td><?php echo number_format(floatval($v_order['pricePerPiece']),2,',',''); ?></td>
										<td><?php echo number_format(floatval($v_order['amount']),2,',',''); ?></td>
										<td><?php echo round($v_order['discountPercent'], 2); ?>%</td>
										<td><?php echo $vatData['percentRate']; ?>%</td>
										<td>
											<?php
											if(intval($v_order['periodization']) > 0) {
												echo '<span class="periodizationLabel">P';
												if(intval($v_order['periodization']) == 2){
													echo '<div class="periodizationHover">'.$formText_DivideOnDays_outputper.'<br/>'.$formText_DateFrom_output.': '.date("d.m.Y", strtotime($v_order['dateFrom'])).'<br/>'.$formText_DateTo_output.': '.date("d.m.Y", strtotime($v_order['dateTo'])).'</div>';
												}
												if(intval($v_order['periodization']) == 1){
													$monthFrom = date("m.Y", strtotime($v_order['dateFrom']));
													$monthTo = date("m.Y", strtotime($v_order['dateTo']));
													if($monthFrom == $monthTo) {
														echo '<div class="periodizationHover">'.$formText_DivideOnMonths_outputper.'<br/>'.$formText_Month_output.': '.$monthFrom.'</div>';
													} else {
														echo '<div class="periodizationHover">'.$formText_DivideOnMonths_outputper.'<br/>'.$formText_MonthFrom_output.': '.$monthFrom.'<br/>'.$formText_MonthTo_output.': '.$monthTo.'</div>';
													}										
												}
												echo '</span>';
											}
											?>								
										</td>
										<td class="text-right"><?php echo number_format(floatval($v_order['priceTotal']),2,',',''); ?></td>
									</tr><?php

									$totalSumInclVat = $vatTotal + $l_sum;
									$l_sum = round($l_sum, $decimalPlaces);
									$totalSumInclVat = round($totalSumInclVat, $decimalPlaces);
									$vatTotal = $totalSumInclVat - $l_sum;
									
								}
								?>
								<tr class="date_different date_<?php echo md5($dateInit.$dateValShow."/".$dateExpireShow);?>" data-key="<?php echo md5($dateInit.$dateValShowInit."/".$dateExpireShowInit);?>">
									<td colspan="8" class="item-totals text-right">
										<?php if(!count($errors)): ?>
										<span class="spacer"><?php echo $formText_SumWithoutVat_Output.' '. $currentCurrency.': ';?><span class="total-sum"><?php echo number_format(floatval($l_sum),$decimalPlaces,',','');?></span></span>
										<span class="spacer"><?php echo $formText_Vat_Output.' '. $currentCurrency.': ';?><span class="total-vat"><?php echo number_format(floatval($vatTotal),$decimalPlaces,',','');?></span></span>
										<span class="spacer"><?php echo $formText_Total_Output.' '. $currentCurrency.': ';?><span class="total-total"><?php echo number_format(floatval($totalSumInclVat),$decimalPlaces,',','');?></span></span>
										<input type="hidden" class="total-sum-hidden" value="<?php echo $l_sum; ?>" name="orders[<?php echo $block_group_id; ?>][<?php echo $currentCurrencyIndexForArray; ?>][totals][totalSum]" />
										<input type="hidden" class="total-vat-hidden" value="<?php echo $vatTotal; ?>" name="orders[<?php echo $block_group_id; ?>][<?php echo $currentCurrencyIndexForArray; ?>][totals][totalVat]" />
										<input type="hidden" class="total-total-hidden" value="<?php echo $vatTotal + $l_sum; ?>" name="orders[<?php echo $block_group_id; ?>][<?php echo $currentCurrencyIndexForArray; ?>][totals][total]" />
										<input type="hidden" value="<?php echo $currentCurrencyCode; ?>" name="orders[<?php echo $block_group_id; ?>][<?php echo $currentCurrencyIndexForArray; ?>][currencyCode]" />
										<?php endif; ?>
										<!-- <input type="hidden" value="<?php echo $_POST['invoiceDate'];  ?>" name="invoiceDate" /> -->
									</td>
								</tr>
							</tbody>
							</table>
					</div>
				</div>
				<?php
			}
			if(count($invoices) == 0) {
				if($generateReport) {
					echo $formText_NoNewlyCreatedInvoices_output;
				}
			}
			?>
		</div>
	</div>
	<?php } ?>
</div>
<style>

	.createReport {
		margin-left: 0;
		cursor: pointer;
	}
	.createReport a {
		color: #fff;
	}
	.invoiceWrapper {
		margin: 10px 0px;
		padding: 10px 15px;
		border-radius: 3px;
		border: 1px solid #D9D6D6;
		background: #fff;
	}
	.invoiceWrapper .item-order {
		padding-left: 30px;
		margin-top: 10px;
	}
	.invoiceWrapper .table {
		margin-bottom: 0;
	}
	.invoiceWrapper .item-title {
		font-size: 14px;
		font-weight: bold;		
	}
	.invoiceWrapper .item-title .title {
		float: left;
	}
	.invoiceWrapper .item-title .out-address {
		float: right;
		width: 20%;
	}
	.invoiceWrapper .item-title .out-ref {
		float: right;
		width: 20%;
	}
	.activeReportLine {
		background: #f9f9f9;
	}
</style>