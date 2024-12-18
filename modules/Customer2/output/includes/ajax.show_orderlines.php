<?php
$invoice_id = $_POST['invoiceId'];

$sqlNoLitmit = "SELECT * FROM invoice i WHERE i.id = ?";
$o_query = $o_main->db->query($sqlNoLitmit, array($invoice_id));
$invoice = $o_query ? $o_query->row_array() : array();

$sqlNoLitmit = "SELECT customer_collectingorder.*, i.external_invoice_nr external_invoice_nr FROM customer_collectingorder
LEFT OUTER JOIN customer ON customer.id = customer_collectingorder.customerId
LEFT JOIN invoice i ON i.id = customer_collectingorder.invoiceNumber
WHERE customer.id is not null AND customer_collectingorder.invoiceNumber = ? GROUP BY customer_collectingorder.id ORDER BY customer_collectingorder.id DESC";

$o_query = $o_main->db->query($sqlNoLitmit, array($invoice_id));
$collectingOrders = $o_query ? $o_query->result_array() : array();

require_once("fnc_getMaxDecimalAmount.php");
?>
<div class="popupform">
	<div class="popupformTitle"><?php echo $formText_Invoice_output?> <?php echo $invoice['external_invoice_nr']?></div>
	<?php
	foreach($collectingOrders as $collectingOrder) {

		$totalOrderPrice = 0;
		$s_sql = "SELECT * FROM orders WHERE orders.collectingorderId = ? AND orders.content_status = 0 ORDER BY orders.id ASC";
		$o_query = $o_main->db->query($s_sql, array($collectingOrder['id']));
		$orders = ($o_query ? $o_query->result_array() : array());

		$s_sql = "SELECT * FROM contactperson WHERE id = ?";
		$o_query = $o_main->db->query($s_sql, array($collectingOrder['contactpersonId']));
		$collectingOrderContactPerson = $o_query ? $o_query->row_array() : array();
		?>
		<div class="collectingOrder">
			<table class="table table-bordered">
				<tr>
					<th colspan="7" >
						<?php if($collectingOrder['date'] != "" && $collectingOrder['date'] != "0000-00-00" ) { ?>
						<div style="float: left; margin-right: 10px;">
								<?php echo $formText_OrderDate_output;?>: <span><?php echo date("d.m.Y", strtotime($collectingOrder['date']));?></span>
						</div>
						<?php } ?>
						<div style="float: left; margin-right: 10px;">
							<?php echo $formText_OrderId_output;?>: <span><?php echo $collectingOrder['id'];?></span>

						</div>
						<?php if($collectingOrderContactPerson != "") { ?>
						<div style="float: left">
							<?php echo $formText_YourContact_output;?>: <span><?php echo $collectingOrderContactPerson['name']." ".$collectingOrderContactPerson['middlename']." ".$collectingOrderContactPerson['lastname'];?></span>
						</div>
						<?php } ?>
						<div style="float: right;">
						</div>
						<div class="clear"></div>
					</th>
				</tr>
				<tr>
					<th colspan="7">
						<table style="width: 100%; table-layout: fixed;">
							<tr>
								<td class="tableInfoTd" width="180px"><?php echo $formText_Reference_Output;?></td>
								<td class="tableInfoTd">
									<?php if(!empty($collectingOrder['reference'])) { ?>
									<span class="tableInfoLabel"><?php echo $collectingOrder['reference'];?></span>
									<?php } ?>
								</td>
							</tr>
							<tr>
								<td class="tableInfoTd"><?php echo $formText_DeliveryDate_Output;?></td>
								<td class="tableInfoTd">
									<?php if(!empty($collectingOrder['delivery_date']) && $collectingOrder['delivery_date'] != '0000-00-00') { ?>
									<span class="tableInfoLabel"><?php echo date('d.m.Y', strtotime($collectingOrder['delivery_date']));?></span>
									<?php } ?>
								</td>
							</tr>
							<tr>
								<td class="tableInfoTd"><?php echo $formText_DeliveryAddress_Output;?></td>
								<td class="tableInfoTd">
									<?php
									$s_delivery_address = trim(preg_replace('/\s+/', ' ', $collectingOrder['delivery_address_line_1'].' '.$collectingOrder['delivery_address_line_2'].' '.$collectingOrder['delivery_address_city'].' '.$collectingOrder['delivery_address_postal_code'].' '.$v_country[$collectingOrder['delivery_address_country']]));
									if(!empty($s_delivery_address)) { ?>
									<span class="tableInfoLabel"><?php echo $s_delivery_address;?></span>
									<?php } ?>
								</td>
							</tr>
							<tr>
								<td class="tableInfoTd"><?php echo $formText_AttachFilesForInvoice_output;?></td>
								<td class="tableInfoTd">
									<div class="filesAttachedToInvoice">
										<div class="attachedFiles">
											<table style="width: 100%; table-layout: fixed;">
											<?php
											$attachedFiles = json_decode($collectingOrder['files_attached_to_invoice'], true);

											foreach($attachedFiles as $file){
												$fileUrl = $extradomaindirroot.'/../'.$file[1][0].'?caID='.$_GET['caID'].'&table=customer_collectingorder&field=files_attached_to_invoice&ID='.$collectingOrder['id'];

												?>
													<tr>
														<td style="padding: 0;" width="90%"><a href="<?php echo $fileUrl?>" download><?php echo $file[0];?></a></td>
														<td style="padding: 0 10px;" width="10%" style="text-align: right;">
														</td>
													</tr>
												<?php
											}
											?>
											</table>
										</div>
									</div>
								</td>
							</tr>
						</table>



					</th>
				</tr>
				<tr>
					<th><b><?php echo $formText_ArticleNr_output;?></b></th>
					<th><b><?php echo $formText_ProductName_output;?></b></th>
					<th><b><?php echo $formText_Accounting_output;?></b></th>
					<th><b><?php echo $formText_Quantity_output;?></b></th>
					<th class="rightAligned"><b><?php echo $formText_PricePerPiece_output;?></b></th>
					<th><b><?php echo $formText_Discount_output;?></b></th>
					<th class="rightAligned"><b><?php echo $formText_PriceTotal_output;?></b></th>
				</tr>
				<?php

				foreach($orders as $order){
					$totalOrderPrice += $order['priceTotal'];

					$decimalNumber = getMaxDecimalAmount($order['amount']);
				?>
				<tr>
					<td><?php echo $order['articleNumber'];?></td>
					<td><?php echo $order['articleName'];?></td>
					<td><?php echo $order['bookaccountNr']." - ".$order['vatCode'];?></td>
					<td><?php echo number_format($order['amount'], $decimalNumber, ",", " ");?></td>
					<td class="rightAligned"><?php echo number_format($order['pricePerPiece'], 2, ",", " ");?></td>
					<td><?php echo number_format($order['discountPercent'], 2, ",", " ");?></td>
					<td class="rightAligned"><?php echo number_format($order['priceTotal'], 2, ",", " ");?></td>
				</tr>
				<?php } ?>
			</table>
			<div class="totalRow"><span><?php echo $formText_Total_output;?>:</span> <?php echo number_format($totalOrderPrice, 2, ",", " ");?></div>
			<div class="clear"></div>
		</div>
		<?php
	}
	?>
</div>
<style>
.popupeditbox .popupform {
	border: 0;
}
.popupform .collectingOrder {
	margin: 10px 0px;
}
</style>
