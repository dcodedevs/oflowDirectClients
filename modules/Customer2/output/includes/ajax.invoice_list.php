<?php
function formatDate($date) {
    global $formText_NotSet_output;
    if ($date == '0000-00-00' || !$date || empty($date)) return $formText_NotSet_output;
    return date('d.m.Y', strtotime($date));
}
$customerId = $_POST['customerId'];
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

$sqlNoLitmit = "SELECT * FROM invoice WHERE customerId = ? ORDER BY id DESC";
$invoice_count = 0;
$o_query = $o_main->db->query($sqlNoLitmit, array($customerId));
if($o_query && $o_query->num_rows()>0) {
    $invoice_count = $o_query->num_rows();
}

$v_country = array();
$v_response = json_decode(APIconnectorOpen("countrylistget"), TRUE);
if(isset($v_response['status']) && $v_response['status'] == 1)
{
	foreach($v_response['data'] as $v_item)
	{
		$v_country[$v_item['countryID']] = $v_item['name'];
	}
}

if($showAll){
    $o_query = $o_main->db->query($sqlNoLitmit, array($customerId));
} else  {
    $sql = "SELECT * FROM invoice WHERE customerId = ? ORDER BY id DESC LIMIT ".$showUntil." OFFSET 0";
    $o_query = $o_main->db->query($sql, array($customerId));
}
if($o_query) {
    $showingNow = $o_query->num_rows();
    $rows = $o_query->result_array();
}
$v_log_types = array(
    1 => $formText_Paper_Output,
    2 => $formText_Email_Output,
    3 => $formText_Ehf_Output,
);
$v_log_status = array(
    1 => $formText_Success_Output,
    2 => $formText_Fail_Output,
);
?>
<table class="table table-bordered table-striped">
    <tr>
        <th><?php echo $formText_InvoiceNr_output; ?></th>
        <th><?php echo $formText_Date_output; ?></th>
        <th><?php echo $formText_Info_output; ?></th>
        <th><?php echo $formText_TotalInclTax_output; ?></th>
        <?php if($moduleAccesslevel > 10) { ?>
        <th></th>
        <?php } ?>
        <th></th>
        <th><?php echo $formText_Pdf_output; ?></th>
        <th></th>
    </tr>
    <?php
    foreach($rows as $row){
        $s_sql = "SELECT customer_collectingorder.*, CONCAT(cp.name, ' ', cp.middlename, ' ', cp.lastname) as contactPersonName FROM customer_collectingorder
        LEFT OUTER JOIN contactperson cp ON cp.id = customer_collectingorder.contactpersonId
        WHERE customer_collectingorder.invoiceNumber = ?  GROUP BY customer_collectingorder.id ORDER BY customer_collectingorder.id DESC";
        $o_query = $o_main->db->query($s_sql, array($row['id']));
        $collecting_orders = ($o_query ? $o_query->result_array() : array());
        ?>
        <tr>
            <td>
                <?php echo $row['external_invoice_nr']; ?></br>
                <?php if(($row['created'] != "0000-00-00 00:00:00" && $row['created'] != null) || $row['updated'] != "0000-00-00 00:00:00" && $row['updated'] != null){?>
                    <span class="glyphicon glyphicon-info-sign hoverEyeCreated">
                        <div class="hoverInfo">
                            <?php
                            $createdShown = false;
                            if($row['created'] != "0000-00-00 00:00:00" && $row['created'] != null){
                                echo $formText_CreatedBy_output?>: <?php echo $row['createdBy']. " ".date("d.m.Y H:i:s", strtotime($row['created']));
                                $createdShown = true;
                            }
                            ?>
                            <?php
                            if($row['updated'] != "0000-00-00 00:00:00" && $row['updated'] != null){
                                if($createdShown) {
                                    echo " | ";
                                }
                                echo $formText_UpdatedBy_output?>: <?php echo $row['updatedBy']. " ".date("d.m.Y H:i:s", strtotime($row['updated']));
                            }
                            ?>
                        </div>
                    </span>
                <?php } ?>
            </td>
            <td><?php echo formatDate($row['invoiceDate']); ?></td>
            <td>
                <?php
                foreach($collecting_orders as $collecting_order) { ?>
                    <?php if(!empty($collecting_order['contactPersonName'])) { ?>
                    <div><?php echo $collecting_order['contactPersonName'];?></div>
                    <?php } ?>
                    <?php if(!empty($collecting_order['reference'])) { ?>
                    <div><?php echo $collecting_order['reference'];?></div>
                    <?php } ?>
                    <?php if(!empty($collecting_order['delivery_date']) && $collecting_order['delivery_date'] != '0000-00-00') { ?>
                    <div><?php echo date('d.m.Y', strtotime($collecting_order['delivery_date']));?></div>
                    <?php } ?>
                    <?php
                    $s_delivery_address = trim(preg_replace('/\s+/', ' ', $collecting_order['delivery_address_line_1'].' '.$collecting_order['delivery_address_line_2'].' '.$collecting_order['delivery_address_city'].' '.$collecting_order['delivery_address_postal_code'].' '.$v_country[$collecting_order['delivery_address_country']]));
                    if(!empty($s_delivery_address)) { ?>
                    <div><?php echo $s_delivery_address;?></div>
                    <?php } ?>
                <?php } ?>
            </td>
            <td><?php echo number_format($row['totalInclTax'], 2, ",", ""); ?></td>
            <?php if($moduleAccesslevel > 10) { ?>
            <td>
                <?php if(intval($row['not_processed']) == 0) { ?>
                    <a href="#" class="createCreditOrder" data-invoice-id="<?php echo $row['id']?>"><?php echo $formText_createCreditOrder_output;?></a><br/>
                    <a href="#" class="createOrderDuplicate" data-invoice-id="<?php echo $row['id']?>"><?php echo $formText_createOrderDuplicate_output;?></a>
                <?php } ?>
            </td>
            <?php } ?>
            <td>
                <?php
                if(intval($row['not_processed']) == 0) {
                    ?>
                    <a href="#" class="sendInvoice" data-invoice-id="<?php echo $row['id'];?>"><?php echo $formText_SendInvoice_output;?></a>
                <?php } ?>
            </td>
            <td>
                <?php if($row['invoiceFile'] != ""){ ?>
                <a href="../<?php echo $row['invoiceFile']; ?>?caID=<?php echo $_GET['caID']; ?>&table=invoice&field=invoiceFile&ID=<?php echo $row['id']; ?>"><?php echo $formText_DownloadPdf_output; ?></a>
                <?php }?>
                <div>
                    <a href="#" class="showOrderlines" data-invoice-id="<?php echo $row['id'];?>"><?php echo $formText_ShowOrderlines_output;?></a>
                </div>
            </td>
            <td>
                <?php
                $s_sql = "SELECT * FROM invoice_send_log WHERE invoice_id = '".$o_main->db->escape_str($row['id'])."' ORDER BY id DESC";
        		$o_log = $o_main->db->query($s_sql);
        		$b_is_sending_log = ($o_log && $o_log->num_rows() > 0);
                if($b_is_sending_log) {
                    ?>
                    <span class="glyphicon glyphicon-info-sign hoverEye"><div class="hoverInfo">

    				<div class="container-fluid">
    				<div class="row">
    					<div class="col-xs-3"><strong><?php echo $formText_InvoicingTime_Output;?></strong></div>
    					<div class="col-xs-6"><strong><?php echo $formText_InvoicedBy_Output;?></strong></div>
    					<div class="col-xs-3"><strong><?php echo $formText_Status_Output;?></strong></div>
    				</div>
    				<?php
    				if($o_log && $o_log->num_rows()>0)
    				{
    					foreach($o_log->result_array() as $v_log)
    					{
                            if($v_log['send_emails'] == ""){
                                $v_log['send_emails'] = $row['sentByEmail'];
                            }
    						?>
    						<div class="row">
    							<div class="col-xs-3"><?php echo date("d.m.Y H:i", strtotime($v_log['created']));?></div>
    							<div class="col-xs-6"><?php echo $v_log_types[$v_log['send_type']].(2==$v_log['send_type']?': '.$v_log['send_emails']:'');?></div>
    							<div class="col-xs-3"><?php echo $v_log_status[$v_log['send_status']];?></div>
    						</div>
    						<?php
    					}
    				} else {
    					?><div class="row">
    						<div class="col-xs-12"><?php echo $formText_NoRecords_Output;?></div>
    					</div><?php
    				}
                    ?>

        			</div>
        			</div></span>
                    <?php
                } else if($row['for_sending']){
                    ?>
                    <span class="glyphicon glyphicon-info-sign hoverEye"><div class="hoverInfo">
                        <?php echo $formText_Sending_output;?>
        			</div></span>
                    <?php
                }
				?>
            </td>
        </tr>
    <?php }; ?>
</table>
<?php if($invoice_count > $showingNow) {?>
<div class="dropdownShowRow">
    <?php echo $formText_Showing_output." ".$showingNow." ".$formText_Of_output." ".$invoice_count;?>
    <?php if($invoice_count-$showingNow >= $perPageDefault){ ?>
        <a href="#" class="invoiceShowNext"><?php echo $formText_Show_output." ".$perPageDefault." ".$formText_More_output;?></a>
    <?php } ?>
    <a href="#" class="invoiceShowAll"><?php echo $formText_ShowAll_output;?></a>
</div>
<?php } ?>
<script type="text/javascript">
    $(".invoiceShowAll").unbind("click").bind("click", function(e){
        e.preventDefault();
        var data = {
            customerId: <?php echo $customerId;?>,
            showAll: true
        };
        ajaxCall('invoice_list', data, function(json) {
            $(".invoices_content").html(json.html).slideDown();
        });
    })
    $(".invoiceShowNext").unbind("click").bind("click", function(e){
        e.preventDefault();
        var data = {
            customerId: <?php echo $customerId;?>,
            showUntil: <?php echo $showingNow+$perPageDefault;?>
        };
        ajaxCall('invoice_list', data, function(json) {
            $(".invoices_content").html(json.html).slideDown();
        });
    })
    $(".createCreditOrder").unbind("click").bind("click", function(e){
        e.preventDefault();
        var invoiceNumber = $(this).data("invoice-id");
        var data = {
            invoiceNumber: invoiceNumber,
            customerId: '<?php echo $customerId;?>'
        };
        ajaxCall('createCreditInvoice', data, function(json) {
            if(json.html == ""){
                output_reload_page();
            } else {
                $('#popupeditboxcontent').html('');
                $('#popupeditboxcontent').html(json.html);
                out_popup = $('#popupeditbox').bPopup(out_popup_options);
                $("#popupeditbox:not(.opened)").remove();
            }
        });
    })
    $(".createOrderDuplicate").unbind("click").bind("click", function(e){
        e.preventDefault();
        var invoiceNumber = $(this).data("invoice-id");
        var data = {
            invoiceNumber: invoiceNumber,
            customerId: '<?php echo $customerId;?>'
        };
        ajaxCall('createOrderDuplicate', data, function(json) {
            if(json.html == ""){
                output_reload_page();
            } else {
                $('#popupeditboxcontent').html('');
                $('#popupeditboxcontent').html(json.html);
                out_popup = $('#popupeditbox').bPopup(out_popup_options);
                $("#popupeditbox:not(.opened)").remove();
            }
        });
    })

    $(".sendInvoice").unbind("click").bind("click", function(e){
        e.preventDefault();
        var data = {
            invoiceId: $(this).data('invoice-id')
        };
        ajaxCall('sendInvoice', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    });
    $(".showOrderlines").off("click").on("click", function(e){
        e.preventDefault();
        var data = {
            invoiceId: $(this).data('invoice-id')
        };
        ajaxCall('show_orderlines', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    })
</script>
