<?php
function formatDate($date) {
    global $formText_NotSet_output;
    if ($date == '0000-00-00' || !$date || empty($date)) return $formText_NotSet_output;
    return date('d.m.Y', strtotime($date));
}

$customerId = $_POST['customerId'];

$s_sql = "SELECT * FROM customer_basisconfig ORDER BY id DESC";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
    $customer_basisconfig = $o_query->row_array();
}

$showAll = false;
if(isset($_POST['showAll']) && $_POST['showAll']){
    $showAll = true;
}
$defaultCount = 10;
$perPageDefault = 50;
$showUntil = $defaultCount;
if(isset($_POST['showUntil']) && intval($_POST['showUntil'])>0){
    $showUntil = intval($_POST['showUntil']);
}


$sqlNoLitmit = "SELECT customer_collectingorder.*, i.external_invoice_nr external_invoice_nr FROM customer_collectingorder
LEFT OUTER JOIN customer ON customer.id = customer_collectingorder.customerId
LEFT JOIN invoice i ON i.id = customer_collectingorder.invoiceNumber
WHERE customer.id is not null AND customer.id = ? AND customer_collectingorder.invoiceNumber > 0 GROUP BY customer_collectingorder.id ORDER BY customer_collectingorder.id DESC";


$invoiced_orders_count = 0;
$o_query = $o_main->db->query($sqlNoLitmit, array($customerId));
if($o_query && $o_query->num_rows()>0) {
    $invoiced_orders_count = $o_query->num_rows();
} 

if($showAll){
    $o_query = $o_main->db->query($sqlNoLitmit, array($customerId));
} else  {
    $sql = $sqlNoLitmit." LIMIT ".$showUntil." OFFSET 0";
    $o_query = $o_main->db->query($sql, array($customerId));
}
if($o_query) {
    $showingNow = $o_query->num_rows();
    $collectingOrders = $o_query->result_array();
} 
if(count($collectingOrders) > 0 ){
?>
<table class="table table-bordered">
    <tr>
        <th><?php echo $formText_OrderDate_output;?></th>
        <th><?php echo $formText_OrderId_output;?></th>
        <th><?php echo $formText_InvoiceNumber_output;?></th>
        <th><?php echo $formText_ContactPerson_output;?></th>
        <th><?php echo $formText_InvoicedOrderCount_output;?></th>
        <th><?php echo $formText_Total_output;?></th>
    </tr>
    <?php
    foreach($collectingOrders as $collectingOrder){
        $totalOrderPrice = 0;
        $s_sql = "SELECT * FROM orders WHERE orders.collectingorderId = ? ORDER BY orders.id ASC";
        $o_query = $o_main->db->query($s_sql, array($collectingOrder['id']));
        $orders = ($o_query ? $o_query->result_array() : array());

        $s_sql = "SELECT * FROM contactperson WHERE id = ?";
        $o_query = $o_main->db->query($s_sql, array($collectingOrder['contactpersonId']));
        $collectingOrderContactPerson = $o_query ? $o_query->row_array() : array();
        foreach($orders as $order){
            $totalOrderPrice += $order['priceTotal'];
        }
        ?>
        <tr class="collectingOrder">           
            <td><?php echo date("d.m.Y", strtotime($collectingOrder['date']));?></td>
            <td><?php echo $collectingOrder['id'];?></td>
            <td><?php echo $collectingOrder['external_invoice_nr']; ?></td>
            <td><?php echo $collectingOrderContactPerson['name'];?></td>
            <td><?php echo $formText_InvoicedOrderCount_output." ". count($orders); ?></td>
            <td style="text-align: right;">
                <?php echo number_format($totalOrderPrice, 2, ",", "");?>
                &nbsp;&nbsp;&nbsp;
                <span class="glyphicon glyphicon-triangle-right showinvoicedorderlines"></span>
            </td>
        </tr>
        <tr class="orderlinesInvoiced"  style="display: none;">
            <td colspan="6">
                <table class="table table-bordered">
                    <tr>
                        <th><b><?php echo $formText_ProductName_output;?></b></th>
                        <th><b><?php echo $formText_PricePerPiece_output;?></b></th>
                        <th><b><?php echo $formText_Quantity_output;?></b></th>
                        <th><b><?php echo $formText_Discount_output;?></b></th>
                        <th><b><?php echo $formText_PriceTotal_output;?></b></th>
                        <th><b><?php echo $formText_VatPercent_output;?></b></th>
                        <th><b><?php echo $formText_Gross_output;?></b></th>
                    </tr>
                    <?php foreach($orders as $order){
                    ?>
                    <tr>
                        <td><?php echo $order['articleName'];?></td>
                        <td><?php echo number_format($order['pricePerPiece'], 2, ",", "");?></td>
                        <td><?php echo number_format($order['amount'], 2, ",", "");?></td>
                        <td><?php echo number_format($order['discountPercent'], 2, ",", "");?></td>
                        <td><?php echo number_format($order['priceTotal'], 2, ",", "");?></td>
                        <td><?php echo number_format($order['vatPercent'], 2, ",", ""); ?>%</td>
                        <td><?php echo number_format($order['gross'], 2, ",", ""); ?></td> 
                    </tr>
                    <?php } ?>
                </table>            
            </td>
        </tr>
        <?php
    }
    ?>
</table>
<?php } ?>
<?php if($invoiced_orders_count > $showingNow) {?>
<div class="dropdownShowRow">
    <?php echo $formText_Showing_output." ".$showingNow." ".$formText_Of_output." ".$invoiced_orders_count;?>
    <?php if($invoiced_orders_count-$showingNow >= $perPageDefault){?>
        <a href="#" class="orderedInvoiceShowNext"><?php echo $formText_Show_output." ".$perPageDefault." ".$formText_More_output;?></a>
    <?php } ?>
    <a href="#" class="orderedInvoiceShowAll"><?php echo $formText_ShowAll_output;?></a>
</div>
<?php } ?>
<script type="text/javascript">
    $(".showinvoicedorderlines").unbind("click").bind("click", function(e){
        e.preventDefault();
        var parent = $(this).parents(".collectingOrder");
        var orderlinesTable = parent.next(".orderlinesInvoiced");
        if(orderlinesTable.is(":visible")){
            $(this).removeClass("glyphicon-triangle-bottom").addClass("glyphicon-triangle-right");
            orderlinesTable.hide();
        } else {
            $(this).removeClass("glyphicon-triangle-right").addClass("glyphicon-triangle-bottom");
            orderlinesTable.show();
        }
    })
    $(".orderedInvoiceShowAll").unbind("click").bind("click", function(e){
        e.preventDefault();
        var data = {
            customerId: <?php echo $customerId;?>,
            showAll: true
        };
        ajaxCall('ordered_invoice_list', data, function(json) {
            $(".ordered_invoices_content").html(json.html).slideDown();
        });
    })
    $(".orderedInvoiceShowNext").unbind("click").bind("click", function(e){
        e.preventDefault();
        var data = {
            customerId: <?php echo $customerId;?>,
            showUntil: <?php echo $showingNow+$perPageDefault;?>
        };
        ajaxCall('ordered_invoice_list', data, function(json) {
            $(".ordered_invoices_content").html(json.html).slideDown();
        });
    })

    $(".output-edit-order-workline").on('click', function(e){
        e.preventDefault();
        var data = {
            customerId: $(this).data('customer-id'),
            orderId: $(this).data('order-id'),
            workplanlineworkerId: $(this).data('orderline-id'),
        };
        ajaxCall('edit_orderlineworker', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    });

    $(".output-delete-order-workline").on('click', function(e){
        e.preventDefault();
        var self = $(this);

        bootbox.confirm('<?php echo $formText_ConfirmDelete_output; ?>', function(result) {
            if (result) {
                var data = {
                    customerId: self.data('customer-id'),
                    workplanlineworkerId: self.data('orderline-id'),
                    deleteWorker: 1
                };
                ajaxCall('edit_orderlineworker', data, function(json) {
                    // self.closest('tr').remove();                    
                    fw_load_ajax(json.redirect_url,'',true);
                });
            }
        });
    });
</script>
