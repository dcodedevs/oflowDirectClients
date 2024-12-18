<?php
// NOTE: For this sync to work actual export csv file does not really matter

$integration = 'IntegrationTripletex';
$integration_file = __DIR__ . '/../../../'. $integration .'/internal_api/load.php';
if (file_exists($integration_file)) {
    require_once $integration_file;
    if (class_exists($integration)) {
        if ($api) unset($api);
        $api = new $integration(array(
            'o_main' => $o_main,
            'ownercompany_id' => 1 // TODO fix this
        ));
    }
}

if($moduleAccesslevel > 10) {
	if(isset($_POST['output_form_submit'])) {
        // Actual sync happening here
        $idFrom = $_POST['idFrom'];
        $idTo = $_POST['idTo'];

        // Sync all customers that are connected to selected invoices
        $sql = "SELECT c.*,
        cei.external_sys_id external_sys_id
        FROM invoice i
        LEFT JOIN customer c ON c.id = i.customerId
        LEFT JOIN customer_externalsystem_id cei ON cei.customer_id = i.customerId
        WHERE i.id >= ? AND i.id <= ?";
        $o_query = $o_main->db->query($sql, array($idFrom, $idTo));
        $results = $o_query && $o_query->num_rows() ? $o_query->result_array() : array();

        $syncedCustomerIds = array();   

        foreach ($results as $row) {
            if (!in_array($row['id'], $syncedCustomerIds)) {
                if ($row['external_sys_id']) {
                    $api->update_customer(array(
                        'id' => $row['external_sys_id'],
                        'name' => $row['name']
                    ));           
                }
                else {
                    // Add on API
                    $new_customer_data = $api->add_customer(array(
                        'name' => $row['name']
                    ));

                    // Save externalsystem id and number
                    $o_main->db->insert('customer_externalsystem_id', array(
                        'moduleID' => $moduleID,
                        'created' => date('Y-m-d H:i:s'),
                        'createdBy' => $variables->loggID,
                        'ownercompany_id' => 0,
                        'customer_id' => $row['id'],
                        'external_id' => $new_customer_data['value']['customerNumber'],
                        'external_sys_id' => $new_customer_data['value']['id']
                    ));
                }

                array_push($syncedCustomerIds, $row['id']);    
            }
        }

        // Get invoices
        $sql = "SELECT i.*,
        cei.external_sys_id external_sys_id
        FROM invoice i
        LEFT JOIN customer_externalsystem_id cei ON cei.customer_id = i.customerId
        WHERE i.id >= ? AND i.id <= ?";
        $o_query = $o_main->db->query($sql, array($idFrom, $idTo));
        $results = $o_query && $o_query->num_rows() ? $o_query->result_array() : array();

        foreach ($results as $row) {
            // Get orders
            $sql = "SELECT * FROM orders WHERE invoiceNumber = ?";
            $o_query = $o_main->db->query($sql, array($row['id']));
            $orders = $o_query && $o_query->num_rows() ? $o_query->result_array() : array();

            $order_lines = array();
            foreach ($orders as $order) {
                array_push($order_lines, array(
                    'description' => $order['articleName'],
                    'unitPriceExcludingVatCurrency' => $order['pricePerPiece'],
                    'vatType' => $order['vatCode'], // TODO fix vat code
                    'discount' => $order['discountPercent'],
                    'count' => $order['amount']
                ));        
            }

            $order_data = array(
                'customerSysId' => $row['external_sys_id'],
                'date' => $row['invoiceDate'],
                'invoiceDueDate' => $row['dueDate'],
                'invoiceNr' => $row['external_invoice_nr'],
                'kid' => $row['kidNumber'],
                'lines' => $order_lines
            );

            $api->add_invoice($order_data);
        }

        // Mark as sent
        $o_main->db->where('id', $exportId);
        $o_main->db->update('invoice_export_history', array('sentTime' => date('Y-m-d H:i:s')));

        // Redirect
        $fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output_export_history&inc_obj=list";
	}
}
?>
<h1 class="popupformTitle"><?php echo $formText_SyncWithTripletex_output; ?></h1>

<div class="popupform">
	<div id="popup-validate-message" style="display:none;"></div>
	<form class="output-form main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output_export_history&inc_obj=ajax&inc_act=sendExport";?>" method="post">
		<input type="hidden" name="fwajax" value="1">
		<input type="hidden" name="fw_nocss" value="1">
		<input type="hidden" name="output_form_submit" value="1">
		<input type="hidden" name="exportId" value="<?php echo $exportId;?>">
		<input type="hidden" name="idFrom" value="<?php echo $_POST['idFrom'];?>">
		<input type="hidden" name="idTo" value="<?php echo $_POST['idTo'];?>">
		<div class="inner">
            <div class="line">
                <div class="lineTitle"><?php echo $formText_From_Output; ?></div>
                <div class="lineInput"><?php echo $_POST['idFrom']; ?></div>
                <div class="clear"></div>
            </div>
            <div class="line">
                <div class="lineTitle"><?php echo $formText_To_Output; ?></div>
                <div class="lineInput"><?php echo $_POST['idTo']; ?></div>
                <div class="clear"></div>
            </div>
		</div>
		<div class="popupformbtn">
			<button type="button" class="output-btn b-large b-close"><?php echo $formText_Cancel_Output;?></button>
			<input type="submit" name="sbmbtn" value="<?php echo $formText_Sync_Output; ?>">
		</div>
	</form>
</div>

<script type="text/javascript" src="../modules/<?php echo $module?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
<script type="text/javascript">
$(document).ready(function() {
    // Submit form
    $("form.output-form").validate({
        submitHandler: function(form) {
            fw_loading_start();
            $.ajax({
                url: $(form).attr("action"),
                cache: false,
                type: "POST",
                dataType: "json",
                data: $(form).serialize(),
                success: function (data) {
                    fw_loading_end();
                    if(data.redirect_url !== undefined)
                    {
                        out_popup.addClass("close-reload").data("redirect", data.redirect_url);
                        out_popup.close();
                    }
                }
            }).fail(function() {
                $("#popup-validate-message").html("<?php echo $formText_ErrorOccuredSavingContent_Output;?>", true);
                $("#popup-validate-message").show();
                $('#popupeditbox').css('height', $('#popupeditboxcontent').height());
                fw_loading_end();
            });
        },
        invalidHandler: function(event, validator) {
            var errors = validator.numberOfInvalids();
            if (errors) {
                var message = errors == 1
                ? '<?php echo $formText_YouMissed_validate; ?> 1 <?php echo $formText_field_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>'
                : '<?php echo $formText_YouMissed_validate; ?> ' + errors + ' <?php echo $formText_fields_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>';

                $("#popup-validate-message").html(message);
                $("#popup-validate-message").show();
                $('#popupeditbox').css('height', $('#popupeditboxcontent').height());
            } else {
                $("#popup-validate-message").hide();
            }
            setTimeout(function(){ $('#popupeditbox').height(''); }, 200);
        }
    });
});
</script>
