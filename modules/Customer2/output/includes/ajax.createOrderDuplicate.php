<?php
// $s_sql = "SELECT * FROM moduledata WHERE name = 'Orders'";
// $o_query = $o_main->db->query($s_sql);
// if($o_query && $o_query->num_rows()>0) {
//     $orders_module_id_find = $o_query->row_array();
// 	$orders_module_id = $orders_module_id_find["uniqueID"];
// }

$s_sql = "SELECT * FROM customer_basisconfig ORDER BY id DESC";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
    $customer_basisconfig = $o_query->row_array();
}
$s_sql = "SELECT * FROM customer_accountconfig ORDER BY id DESC";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
    $v_customer_accountconfig = $o_query->row_array();
}
if($v_customer_accountconfig['activateCreateCollectingOrders'] > 0) {
    if($v_customer_accountconfig['activateCreateCollectingOrders'] == 1){
        $customer_basisconfig['activateCreateCollectingOrders'] = 1;
    }
    if($v_customer_accountconfig['activateCreateCollectingOrders'] == 2){
        $customer_basisconfig['activateCreateCollectingOrders'] = 0;
    }
}
$customerId = $_POST['customerId'];

$s_sql = "SELECT * FROM customer_collectingorder WHERE invoiceNumber = ?";
$o_query = $o_main->db->query($s_sql, array($_POST['invoiceNumber']));
if($o_query && $o_query->num_rows()>0) {

	$collectingOrders = $o_query->result_array();
    $collectingOrderFirst = $collectingOrders[0];
    $sql_addon = "";
    $createOrder = true;
    if($collectingOrderFirst['projectId'] > 0 || $collectingOrderFirst['repeatingorderId'] > 0 || $collectingOrderFirst['project2Id'] > 0 || $collectingOrderFirst['project2PeriodId'] > 0){
        $createOrder = false;
        if($_POST['confirmed']) {
            $createOrder = true;
            if(isset($_POST['save_connections'])){
                $sql_addon.=", projectId = '".$collectingOrderFirst['projectId']."',
                repeatingorderId = '".$collectingOrderFirst['repeatingorderId']."',
                project2Id = '".$collectingOrderFirst['project2Id']."',
                project2PeriodId = '".$collectingOrderFirst['project2PeriodId']."'";
            }
        } else {
            $warningMessage = $formText_CollectingOrderConnectedTo_output.": ";
            if($collectingOrderFirst['projectId'] > 0){
                $warningMessage.=" ".$formText_Project_output;
            }
            if($collectingOrderFirst['project2Id'] > 0 || $collectingOrderFirst['project2PeriodId'] > 0) {
                $warningMessage.=" ".$formText_Project2_output;
            }
            if($collectingOrderFirst['repeatingorderId'] > 0){
                $warningMessage.=" ".$formText_RepeatingOrder_output;
            }
        }
    }

    if($createOrder){
        if(!function_exists("duplicate_images")) include(__DIR__."/fn_duplicate_images.php");
        $newAttachedFiles = duplicate_images($collectingOrders[0]['files_attached_to_invoice']);

    	$sql = "INSERT INTO customer_collectingorder SET
        created = now(),
        createdBy='".$variables->loggID."',
        moduleID ='".$moduleID."',
        date = now(),
        contactpersonId = ?,
        customerId = ?,
        accountingProjectCode =  ?,
        department_for_accounting_code = ?,
        ownercompanyId = ?,
        reference = ?,
        delivery_date = ?,
        delivery_address_line_1 = ?,
        delivery_address_line_2 = ?,
        delivery_address_city = ?,
        delivery_address_country = ?,
        delivery_address_postal_code = ?,
        files_attached_to_invoice = ?".$sql_addon;

        $o_query2 = $o_main->db->query($sql, array($collectingOrders[0]['contactpersonId'], $collectingOrders[0]['customerId'], $collectingOrders[0]['accountingProjectCode'], $collectingOrders[0]['department_for_accounting_code'],
        $collectingOrders[0]['ownercompanyId'], $collectingOrders[0]['reference'], $collectingOrders[0]['delivery_date'],$collectingOrders[0]['delivery_address_line_1'],$collectingOrders[0]['delivery_address_line_2'],$collectingOrders[0]['delivery_address_city'],
        $collectingOrders[0]['delivery_address_country'],$collectingOrders[0]['delivery_address_postal_code'], $newAttachedFiles));
    	$collectingorderId = $o_main->db->insert_id();

        foreach($collectingOrders as $collectingOrder) {

        	$s_sql = "SELECT * FROM orders WHERE collectingorderId = ?";
        	$o_query = $o_main->db->query($s_sql, array($collectingOrder['id']));
        	$orders = $o_query ? $o_query->result_array() : array();

        	foreach($orders as $order){
        		$addOnInvoice = $order['addOnInvoice'];
        		if($collectingorderId > 0){
        			$addOnInvoice = 1;
        		}
        		$o_main->db->query("INSERT INTO orders SET moduleID = ?, createdBy = ?, created = NOW(), projectLeader = ?,
        			contactPerson = ?,  articleNumber = ?,
        			articleName = ?, describtion = ?, amount = ?,
        			pricePerPiece = ?, discountPercent = ?, priceTotal = ?,
        			delieveryDate = ?, expectedTimeuseMinutes = ?,
        			monthsInvoicedFromStart = ?, dateFrom = ?, dateTo = ?,
        			content_status = ?, vatCode = ?, vatPercent = ?,
        			bookaccountNr = ?, gross = ?,  subscribtionId = ?, currencyId = ?, projectCode = ?,prepaidCommonCost=?, collectingorderId = ?, periodization = ?, periodizationMonths = ?",
                    array(0, $variables->loggID, $order['projectLeader'], $order['contactPerson'], $order['articleNumber'], $order['articleName'], $order['describtion'],
                    $order['amount'], $order['pricePerPiece'], $order['discountPercent'], ($order['amount'])*$order['pricePerPiece']*(100-$order['discountPercent'])/100, $order['delieveryDate'], $order['expectedTimeuseMinutes'],
                     $order['monthsInvoicedFromStart'], $order['dateFrom'], $order['dateTo'], $order['content_status'], $order['vatCode'], $order['vatPercent'], $order['bookaccountNr'],
                      $order['gross'], $order['subscribtionId'], $order['currencyId'], $order['projectCode'], $order['prepaidCommonCost'], $collectingorderId, $order['periodization'], $order['periodizationMonths']));

                // echo $o_main->db->last_query();
        		// ALI - total overview message feed
        		$l_element_id = $o_main->db->insert_id();
        		$o_query = $o_main->db->query("SELECT id FROM employee WHERE email = ?", array($variables->loggID));
        		$l_sender_id = (($o_query && $o_row = $o_query->row()) ? $o_row->id : "");
        		$l_receiver_id = $order['projectLeader'];
        		$s_message = "";
        		$s_action = "created order duplicate";
        		$s_element_table = "orders";
        		if($l_sender_id != $l_receiver_id)
        		{
        			$o_main->db->query("INSERT INTO totaloverviewmessages (id, createdBy, created, senderId, receiverId, message, elementId, `action`, elementTable) VALUES (NULL, ?, NOW(), ?, ?, ?, ?, ?, ?)", array($variables->loggID, $l_sender_id, $l_receiver_id, $s_message, $l_element_id, $s_action, $s_element_table));
        		}
        	}
        }
    }
}
if($warningMessage != ""){
	?>
	<div class="popupform">
		<div id="popup-validate-message" style="display:none;"></div>
		<form class="output-form output-worker-form main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=createOrderDuplicate";?>" method="post">
            <input type="hidden" name="fwajax" value="1"/>
            <input type="hidden" name="fw_nocss" value="1"/>
            <input type="hidden" name="customerId" value="<?php echo $_POST['customerId'];?>"/>
            <input type="hidden" name="invoiceNumber" value="<?php echo $_POST['invoiceNumber'];?>"/>
            <input type="hidden" name="confirmed" value="1"/>
            <div class="inner">
	            <div class="warningMessage">
	                <?php echo $warningMessage;?>
	            </div>
			</div>
			<div class="popupformbtn">
				<button type="button" class="output-btn b-large b-close"><?php echo $formText_Cancel_Output;?></button>
				<button type="submit" class="output-btn b-large"><?php echo $formText_RemoveConnection_Output; ?></button>
                <button type="submit" name="save_connections" class="output-btn b-large" ><?php echo $formText_KeepConnection_Output; ?></button>

			</div>
		</form>
	</div>
    <script type="text/javascript" src="../modules/<?php echo $module?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
    <script type="text/javascript">
    $("form.output-worker-form").validate({
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
                    out_popup.addClass("close-reload");
                    out_popup.close();
                }
            }).fail(function() {
                <?php if($_POST['from_popup']) { ?>
                    $("#popup-validate-message2").html("<?php echo $formText_ErrorOccuredSavingContent_Output;?>", true);
                    $("#popup-validate-message2").show();
                    $('#popupeditbox2').css('height', $('#popupeditboxcontent2').height());
                <?php } else { ?>
                    $("#popup-validate-message").html("<?php echo $formText_ErrorOccuredSavingContent_Output;?>", true);
                    $("#popup-validate-message").show();
                    $('#popupeditbox').css('height', $('#popupeditboxcontent').height());
                <?php } ?>
                fw_loading_end();
            });
        },
        invalidHandler: function(event, validator) {
            var errors = validator.numberOfInvalids();
            if (errors) {
                var message = errors == 1
                ? '<?php echo $formText_YouMissed_validate; ?> 1 <?php echo $formText_field_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>'
                : '<?php echo $formText_YouMissed_validate; ?> ' + errors + ' <?php echo $formText_fields_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>';


                <?php if($_POST['from_popup']) { ?>
                    $("#popup-validate-message2").html("<?php echo $formText_ErrorOccuredSavingContent_Output;?>", true);
                    $("#popup-validate-message2").show();
                    $('#popupeditbox2').css('height', $('#popupeditboxcontent2').height());
                <?php } else { ?>
                    $("#popup-validate-message").html("<?php echo $formText_ErrorOccuredSavingContent_Output;?>", true);
                    $("#popup-validate-message").show();
                    $('#popupeditbox').css('height', $('#popupeditboxcontent').height());
                <?php } ?>
            } else {
                <?php if($_POST['from_popup']) { ?>
                    $("#popup-validate-message2").hide();
                <?php } else { ?>
                    $("#popup-validate-message").hide();
                <?php } ?>
            }
            setTimeout(function(){
                <?php if($_POST['from_popup']) { ?>
                    $('#popupeditbox2').height('');
                <?php } else { ?>
                    $('#popupeditbox').height('');
                <?php } ?>
            }, 200);
        }
    });
    </script>
	<?php
	return;
}
?>
