<?php

// read output settings
require_once __DIR__ . '/settingsOutput/settings.php';

//include(__DIR__."/list_btn.php");
$v_address_format = array('paStreet', 'paCity', 'paCountry', 'paPostalNumber');
$o_result = mysql_query("SELECT * FROM invoice_accountconfig");
$v_settings = mysql_fetch_assoc($o_result);
$v_row = mysql_fetch_assoc(mysql_query("SELECT * FROM moduledata WHERE name = 'Orders'"));
$l_orders_module_id = $v_row["uniqueID"];

function getOrderVAT($orderId) {
	// Get VAT
	$cq = "SELECT c.taxFreeSale taxFreeSale FROM orders o LEFT JOIN customer c ON o.customerID = c.id WHERE o.id = '".$orderId."'";
	$res = mysql_query($cq);
	$row = mysql_fetch_array($res);
	$taxFreeSale = $row['taxFreeSale'];

	// Building query
    $q = "SELECT vc.percentRate percentRate, vc.vatCode vatCode, ba.accountNr bookAccountNr FROM orders o";
    $q .= " LEFT JOIN article a ON a.id = o.articleNumber";
	if ($taxFreeSale) {
    	$q .= " LEFT JOIN bookaccount ba ON a.SalesAccountWithoutVat = ba.id";
	} else {
		$q .= " LEFT JOIN bookaccount ba ON a.SalesAccountWithVat = ba.id";
	}
    $q .= " LEFT JOIN vatcode vc ON ba.vatCode = vc.id";
    $q .= " WHERE o.id = '$orderId'";
    $res = mysql_query($q);
    $row = mysql_fetch_array($res);

	// Preping return data
	$data = array(
		'percentRate' => $row['percentRate'],
		'code' => $row['vatCode'],
		'bookAccountNr' => $row['bookAccountNr']
	);
	return $data;
}

// !! Reading all customers and orders in ob_start() buffer
//
$s_sql = "SELECT c.* FROM customer c JOIN orders o ON o.customerID = c.id AND o.addOnInvoice = 1 AND o.moduleID = '".$l_orders_module_id."' AND o.content_status = 0 GROUP BY c.id ORDER BY c.name";
$customersCount = 0;
$customersCountWithErrors = 0;
$customersSelected = 0;
$o_customers = mysql_query($s_sql);
ob_start();
while($v_customer = mysql_fetch_assoc($o_customers))
{
	$s_address = "";
	foreach($v_address_format as $s_key)
	{
		if($v_customer[$s_key] != "")
		{
			if($s_address != "") $s_address .= ", ";
			$s_address .= $v_customer[$s_key];
		}
	}
	$l_vat = $v_settings['vat'];
	if($v_customer['taxFreeSale'] == 1) $l_vat = 0;

	$s_sql = "SELECT * FROM orders WHERE customerID = '".$v_customer['id']."' AND addOnInvoice = 1 AND moduleID = $l_orders_module_id AND content_status = 0";
	$o_orders = mysql_query($s_sql);

	/**
	 * Check if there are any order related issues
	 */

	$errors = array();

	while($v_order = mysql_fetch_array($o_orders)) {
		$orderId = $v_order['id'];
		$articleCount = mysql_num_rows(mysql_query("SELECT * FROM article WHERE id = '".$v_order['articleNumber']."'"));
		$vatData = getOrderVAT($orderId);

		// Check for articleNumber
		if ($invoiceCheck_ArticleNumber) {
			if (!$articleCount) {
				$errors[$orderId][] = array(
					'orderId' => $orderId,
					'errorMsg' => $formText_InvalidArticleNumber_output
				);
			}
		}

		// Check VAT code
		if ($invoiceCheck_VatCode) {
			if(!is_numeric($vatData['code'])) {
				$errors[$orderId][] = array(
					'orderId' => $orderId,
					'errorMsg' => $formText_InvalidArticleBookAccountOrVatCode_output
				);
			}
		}

		// Check for projectFAccNumber
		if ($invoiceCheck_ProjectFAccNumber) {
			$fAccCount = mysql_num_rows(mysql_query("SELECT * FROM projectforaccounting WHERE id = '".$v_order['projectFAccNumber']."'"));
			if (!$fAccCount) {
				$errors[$orderId][] = array(
					'orderId' => $orderId,
					'errorMsg' => $formText_InvalidProjectFAccNumber_output
				);
			}
		}

	}
	mysql_data_seek($o_orders, 0);

	// Customer count & selected count
	$customersCount++;
	if (!count($errors)) {
		$customersSelected++;
	}
	else {
		$customersCountWithErrors++;
	}

	?><div class="item-customer">
		<div class="item-title">
			<div>
				<?php if(!count($errors)): ?>
				<input type="checkbox" value="<?php echo $v_customer['id'];?>" name="customer[]" checked />
				<?php endif; ?>
				<?php echo $v_customer['name'];?>
			</div>
			<div class="out-address"><?php echo $s_address;?></div>
			<br clear="all">
		</div>
		<?php if(count($errors)): ?>
		<div class="item-error">
			<div class="alert alert-danger"><?php echo $formText_CustomerHasOrderErrors_output; ?>
				<ul style="margin:0; padding:0 15px;">
					<?php foreach($errors as $orderErrorList): ?>
						<?php foreach($orderErrorList as $error): ?>
							<li><a href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=Orders&ID=".$error['orderId']."&includefile=edit&submodule=orders"; ?>"><?php echo $formText_Order_output; ?> #<?php echo $error['orderId']; ?></a> - <?php echo $error['errorMsg']; ?></li>
						<?php endforeach; ?>
					<?php endforeach; ?>
				</ul>
			</div>
		</div>
		<?php endif; ?>
		<div class="item-order">
			<table class="table table-condensed">
			<thead>
				<tr>
					<th>&nbsp;</th>
					<th><?php echo $formText_Text_Output;?></th>
					<th><?php echo $formText_Price_Output;?></th>
					<th><?php echo $formText_Amount_Output;?></th>
					<th><?php echo $formText_Discount_Output;?></th>
					<th><?php echo $formText_Vat_Output;?></th>
					<th class="text-right"><?php echo $formText_TotalPrice_Output;?></th>
				</tr>
			</thead>
			<tbody>
				<?php

				$l_sum = 0;
				$vatTotal = 0;
				while($v_order = mysql_fetch_array($o_orders))
				{
					$orderId = $v_order['id'];
					$vatData = getOrderVAT($orderId);
					$orderHasError = (count($errors[$orderId]) ? true : false);

					// Calc totals
					$vat = $v_order['priceTotal'] * ($vatData['percentRate']/100);
					if (!$orderHasError){
						$l_sum += $v_order['priceTotal'];
						$vatTotal += $vat;
					}

					?><tr <?php echo ($orderHasError ? 'class="orderHasError"' : ''); ?>>
						<td>
							<?php if(!count($errors)): ?>
							<input type="hidden" name="orders[<?php echo $v_customer['id']; ?>][list][<?php echo $orderId; ?>][orderId]" value="<?php echo $orderId; ?>">
							<input type="hidden" name="orders[<?php echo $v_customer['id']; ?>][list][<?php echo $orderId; ?>][articleName]" value="<?php echo $v_order['articleName']; ?>">
							<input type="hidden" name="orders[<?php echo $v_customer['id']; ?>][list][<?php echo $orderId; ?>][vatPercentRate]" value="<?php echo $vatData['percentRate']; ?>">
							<input type="hidden" name="orders[<?php echo $v_customer['id']; ?>][list][<?php echo $orderId; ?>][vatCode]" value="<?php echo $vatData['code']; ?>">
							<input type="hidden" name="orders[<?php echo $v_customer['id']; ?>][list][<?php echo $orderId; ?>][bookAccountNr]" value="<?php echo $vatData['bookAccountNr']; ?>">
							<input type="hidden" name="orders[<?php echo $v_customer['id']; ?>][list][<?php echo $orderId; ?>][pricePerPiece]" value="<?php echo $v_order['pricePerPiece']; ?>">
							<input type="hidden" name="orders[<?php echo $v_customer['id']; ?>][list][<?php echo $orderId; ?>][amount]" value="<?php echo $v_order['amount']; ?>">
							<input type="hidden" name="orders[<?php echo $v_customer['id']; ?>][list][<?php echo $orderId; ?>][discountPercent]" value="<?php echo $v_order['discountPercent']; ?>">
							<input type="hidden" name="orders[<?php echo $v_customer['id']; ?>][list][<?php echo $orderId; ?>][priceTotal]" value="<?php echo $v_order['priceTotal']; ?>">
							<input type="hidden" name="orders[<?php echo $v_customer['id']; ?>][list][<?php echo $orderId; ?>][vat]" value="<?php echo $vat; ?>">
							<input type="hidden" name="orders[<?php echo $v_customer['id']; ?>][list][<?php echo $orderId; ?>][gross]" value="<?php echo $v_order['priceTotal'] + $vat; ?>">
							<input type="checkbox" value="<?php echo $v_order['id'];?>" name="order_number[]" <?php echo (!$orderHasError ? 'checked' : ''); ?> data-total="<?php echo $v_order['priceTotal']; ?>" data-vat="<?php echo $vat; ?>" />
							<?php endif; ?>
						</td>
						<td><?php echo $v_order['articleName']; ?></td>
						<td><?php echo $v_order['pricePerPiece']; ?></td>
						<td><?php echo $v_order['amount']; ?></td>
						<td><?php echo round($v_order['discountPercent']); ?>%</td>
						<td><?php echo $vatData['percentRate']; ?>%</td>
						<td class="text-right"><?php echo $v_order['priceTotal']; ?></td>
					</tr><?php
				}
				?>
				<tr>
					<td colspan="7" class="item-totals text-right">
						<?php if(!count($errors)): ?>
						<span class="spacer"><?php echo $formText_SumWithoutVat_Output." NOK: ";?><span class="total-sum"><?php echo $l_sum;?></span></span>
						<span class="spacer"><?php echo $formText_Vat_Output.": ";?><span class="total-vat"><?php echo $vatTotal;?></span></span>
						<span class="spacer"><?php echo $formText_Total_Output." NOK: ";?><span class="total-total"><?php echo $l_sum + $vatTotal;?></span></span>
						<input type="hidden" class="total-sum-hidden" value="<?php echo $l_sum; ?>" name="orders[<?php echo $v_customer['id']; ?>][totals][totalSum]" />
						<input type="hidden" class="total-vat-hidden" value="<?php echo $vatTotal; ?>" name="orders[<?php echo $v_customer['id']; ?>][totals][totalVat]" />
						<input type="hidden" class="total-total-hidden"value="<?php echo $vatTotal + $l_sum; ?>" name="orders[<?php echo $v_customer['id']; ?>][totals][total]" />
						<?php endif; ?>
						<!-- <input type="hidden" value="<?php echo $_POST['invoiceDate'];  ?>" name="invoiceDate" /> -->
					</td>
				</tr>
			</tbody>
			</table>
			<div><i><?php
			if($v_customer["invoiceBy"] == 1)
		   {
			   echo $formText_InvoiceWillBeSentToEmail_Output.": ".$v_customer["invoiceEmail"];
		   } else {
			   echo $formText_InvoiceWillBePrinted_Output;
		   }
		   ?></i></div>
		</div>
	</div><?php
}
$listBuffer = ob_get_clean();
?>

<div id="out-info-box">
	<ul>
		<li><span class="number customersSelectedCountSpan"><?php echo $customersSelected; ?></span> <?php echo $formText_CustomersSelected_output; ?></li>
		<li><span class="number customersListedCountSpan"><?php echo $customersCount; ?></span> <?php echo $formText_CustomersListed_output; ?></li>
	</ul>

</div>
<div id="out-error-box">
	<?php if ($customersCountWithErrors > 0): ?>
	<div class="alert alert-danger"><?php echo $customersCountWithErrors. ' ' . $formText_CustomerInvoicesHasErrors_output; ?></div>
	<?php endif; ?>
</div>
<div id="out-customer-list">
	<div class="out-dynamic">
		<?php echo $listBuffer; ?>
	</div>
	<div class="out-buttons">
		<button id="out-invoice-create" class="btn btn-default"><?php echo $formText_CreateInvoices_Output;?></button>
		<a class="btn btn-default pull-right optimize" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=history";?>"><?php echo $formText_History_Output;?></a>
	</div>
</div>

<script type="text/javascript">
$(function() {
	var _vat = parseInt("<?php echo $l_vat;?>");

	$(".item-customer .item-title input[type=checkbox]").on("change", function(){
		$(this).closest(".item-customer").find(".item-order input[type=checkbox]").prop("checked", $(this).is(":checked"));
		out_calculate(this);
	});
	$(".item-customer .item-order input[type=checkbox]").on("change", function(){
		out_calculate(this);
	});
	function out_calculate(_this)
	{
		var _total = 0;
		var _vat = 0;
		var _checked = false;
		var _customer = $(_this).closest(".item-customer");
		_customer.find(".item-order input[type=checkbox]").each(function(){
			if($(this).is(":checked"))
			{
				_checked = true;
				_total = _total + parseFloat($(this).data("total"));
				_vat = _vat + parseFloat($(this).data('vat'));
			}
		});
		_customer.find(".item-title input[type=checkbox]").prop("checked", _checked);
		_customer.find(".item-totals .total-sum").text(_total);
		_customer.find(".item-totals .total-vat").text(_vat);
		_customer.find(".item-totals .total-total").text(_total + _vat);
		_customer.find(".item-totals .total-sum-hidden").val(_total);
		_customer.find(".item-totals .total-vat-hidden").val(_vat);
		_customer.find(".item-totals .total-total-hidden").val(_total + _vat);

		$('.customersSelectedCountSpan').html($('.item-title input[type=checkbox]:checked').length);
	}
	$("#out-invoice-create").on("click", function(){
		$.ajax({
			cache: false,
			type: 'POST',
			dataType: 'json',
			url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=create_invoices";?>',
			data: "fwajax=1&fw_nocss=1&" + $("#out-customer-list input").serialize(),
			success: function(obj){
				$('#out-customer-list .out-dynamic').html(obj.html);
			}
		});
	});

});
</script>
