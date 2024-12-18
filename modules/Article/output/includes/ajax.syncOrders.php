<?php

$s_sql = "SELECT * FROM article_accountconfig";
$o_query = $o_main->db->query($s_sql);
$article_accountconfig = $o_query ? $o_query->row_array() : array();
if($article_accountconfig['activate_order_sync']) {
    if($moduleAccesslevel > 10) {
        if(isset($_POST['output_form_submit'])) {
            $hook_file = __DIR__ . '/../../../../modules/Integration24SevenOffice/hooks/get_all_orders.php';
            if (file_exists($hook_file)) {
                include $hook_file;
                if (is_callable($run_hook)) {
                    $hook_result = $run_hook($hook_params);
                    unset($run_hook);
                    $orderlines = $hook_result['orderlines'];
                    foreach($orderlines as $orderline) {
                        $lines= $orderline['orderLines'];
                        $sql = "SELECT c.*,
                        cei.external_sys_id external_sys_id,
                        cei.external_id external_id
                        FROM customer c
                        LEFT JOIN customer_externalsystem_id cei ON cei.customer_id = c.id AND cei.ownercompany_id = ?
                        WHERE cei.external_id = ?";
                        $o_query = $o_main->db->query($sql, array(1, $orderline['customerId']));
                        $customer_data = $o_query && $o_query->num_rows() ? $o_query->row_array() : array();

                        $sql = "SELECT *
                        FROM customer_collectingorder co
                        WHERE co.external_sys_id = ? AND co.content_status < 2";
                        $o_query = $o_main->db->query($sql, array($orderline['orderId']));
                        $collectingorder = $o_query && $o_query->num_rows() ? $o_query->row_array() : array();
                        if(!$collectingorder){
                            if($customer_data) {
                                $sql = "INSERT INTO customer_collectingorder SET
                                created = now(),
                                createdBy='".$variables->loggID."',
                                date = '".date("Y-m-d", strtotime($orderline['date']))."',
                                customerId = '".$customer_data['id']."',
                                accountingProjectCode = '".$_POST['projectCode']."',
                                department_for_accounting_code = '".$_POST['departmentCode']."',
                                ownercompanyId = 1,
                                external_sys_id = '".$orderline['orderId']."'";

                                $o_query = $o_main->db->query($sql);
                                $insert_id = $o_main->db->insert_id();
                                foreach($lines as $line){
                                    $s_sql = "SELECT * FROM article WHERE article.external_sys_id = ?";
                                    $o_query = $o_main->db->query($s_sql, array($line['productId']));
                                    $article = ($o_query ? $o_query->row_array() : array());
                                    if($article){
                                        $articleName = $article['name'];

                                        $amount = $line['quantity'];
                                        $pricePerPiece = $line['price'];
                                        $discountPercent = $line['discountRate'];

                                        $totalRowPrice = $totalAmount * $pricePerPiece * ((100-$discountPercent)/100);
                                        $totalTotal += $totalRowPrice;
                                        $subscriptionNameString = $line['name'];

                                        $customerId = $customer_data['id'];

                                        $priceTotal = round($pricePerPiece * $amount * (100-$discountPercent)/100, 2);

                                        $vatCode = $article['VatCodeWithVat'];
                                        $bookaccountNr = $article['SalesAccountWithVat'];
                                        $vatPercent = 0;

                                        $noError = true;

                                        $vatCodeError = false;
                                        $bookAccountError = false;
                                        $articleError = false;
                                        $projectError = false;

                                        $s_sql = "SELECT * FROM vatcode WHERE vatCode = ?";
                                        $o_query = $o_main->db->query($s_sql, array($vatCode));
                                        $vatcodeItem = $o_query ? $o_query->row_array() : array();
                                        if(!$vatcodeItem){
                                            $noError = false;
                                            $vatCodeError = true;
                                        }

                                        $s_sql = "SELECT * FROM bookaccount WHERE accountNr = ?";
                                        $o_query = $o_main->db->query($s_sql, array($bookaccountNr));
                                        $bookaccountItem = $o_query ? $o_query->row_array() : array();
                                        if(!$bookaccountItem){
                                            $noError = false;
                                            $bookAccountError = true;
                                        }


                                        $s_sql = "INSERT INTO orders SET
                                            moduleID = ?,
                                            created = now(),
                                            createdBy= ?,
                                            articleNumber= ?,
                                            articleName= ?,
                                            describtion= ?,
                                            amount= ?,
                                            pricePerPiece= ?,
                                            discountPercent= ?,
                                            priceTotal= ?,
                                            Status = ?,
                                            bookaccountNr = ?,
                                            vatCode = ?,
                                            vatPercent = ?,
                                            collectingorderId = ?,
                                            dateFrom = ?,
                                            dateTo = ?,
                                            external_sys_id = ?";
                                            $o_main->db->query($s_sql, array(0, $variables->loggID, $article['id'], $articleName, '', str_replace(",", ".", $amount), str_replace(",", ".", $pricePerPiece), str_replace(",", ".", $discountPercent), str_replace(",", ".", $priceTotal), 1, $bookaccountNr, $vatCode, $vatPercent, $insert_id, $dateFrom, $dateTo, $line['id']));

                                    }
                                }
                            }
                        }
                    }

                    $fw_redirect_url = $_POST['redirect_url'];
                }
            }
            return;
        }
    }
    ?>
    <div class="popupform">
        <div id="popup-validate-message" style="display:none;"></div>
        <form class="output-form main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=syncOrders";?>" method="post">
            <input type="hidden" name="fwajax" value="1">
            <input type="hidden" name="fw_nocss" value="1">
            <input type="hidden" name="output_form_submit" value="1">
            <input type="hidden" name="redirect_url" value="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=list"; ?>">
            <div class="inner" id="out-customer-list">
                <?php
                $hook_file = __DIR__ . '/../../../../modules/Integration24SevenOffice/hooks/get_all_orders.php';
                if (file_exists($hook_file)) {
                    include $hook_file;
                    if (is_callable($run_hook)) {
                        $hook_result = $run_hook($hook_params);
                        unset($run_hook);
                        $orderlines = $hook_result['orderlines'];
                        foreach($orderlines as $orderline) {
                            $lines= $orderline['orderLines'];
                            $sql = "SELECT c.*,
                            cei.external_sys_id external_sys_id,
                            cei.external_id external_id
                            FROM customer c
                            LEFT JOIN customer_externalsystem_id cei ON cei.customer_id = c.id AND cei.ownercompany_id = ?
                            WHERE cei.external_id = ?";
                            $o_query = $o_main->db->query($sql, array(1, $orderline['customerId']));
                            $customer_data = $o_query && $o_query->num_rows() ? $o_query->row_array() : array();

                            $sql = "SELECT *
                            FROM customer_collectingorder co
                            WHERE co.external_sys_id = ? AND co.content_status < 2";
                            $o_query = $o_main->db->query($sql, array($orderline['orderId']));
                            $collectingorder = $o_query && $o_query->num_rows() ? $o_query->row_array() : array();
                            if(!$collectingorder) {
                                ?>
                                <div class="item-customer">
                                    <div class="item-title"><div><?php echo $customer_data['name']." ".$customer_data['middlename']." ".$customer_data['lastname']?></div></div>
                                    <div class="item-order">
                                        <table class="table table-condensed">
                    						<thead>
                    							<tr>
                    								<th><?php echo $formText_OrderlineText_Output;?></th>
                    								<th><?php echo $formText_Amount_Output;?></th>
                    								<th><span class="articleInfo">&nbsp;</span><?php echo $formText_PricePerPiece_Output;?></th>
                    								<th><?php echo $formText_Discount_Output;?></th>
                    								<th class="text-right"><?php echo $formText_TotalPrice_Output;?></th>
                    							</tr>
                    						</thead>
                    						<tbody>

                    							<?php
                                                $totalTotal = 0;
                    							foreach($lines as $line){
                                                    if($line['productId'] > 0){
                    									$totalAmount = $line['quantity'];
                        								$pricePerPiece = $line['price'];

                        								$totalRowPrice = $totalAmount * $pricePerPiece * ((100-$line['discountRate'])/100);
                                                        $totalTotal += $totalRowPrice;
                                                        $subscriptionNameString = $line['name'];
                    								?>
                    								<tr>
                    									<td><?php echo $subscriptionNameString; ?></td>
                    									<td><?php echo number_format($totalAmount, 2, ",", " "); ?></td>
                    									<td><?php echo number_format($pricePerPiece, 2, ",", " "); ?></td>
                    									<td><?php echo number_format($line['discountRate'], 2, ",", " "); ?>%</td>
                    									<td class="item-total text-right"><?php echo number_format($totalRowPrice, 2, ",", " "); ?></td>
                    								</tr>
                								<?php
                                                    }
                                                } ?>
                    							<tr>
                    								<td width="60%"></td>
                    								<td width="8%" class="item-price"><?php //echo $l_price; ?></td>
                    								<td width="12%"><?php //echo $v_row['amount']; ?></td>
                    								<td width="8%"><?php //echo $v_row['discountPercent']; ?></td>
                    								<td width="8%" class="item-total text-right last"><?php echo number_format($totalTotal, 2, ",", " "); ?></td>
                    							</tr>
                    						</tbody>
                						</table>

                                    </div>
                                </div>
                                <?php
                            }
                        }
                    }
                }

                ?>
            </div>
            <div class="popupformbtn">
                <button type="button" class="output-btn b-large b-close"><?php echo $formText_Cancel_Output;?></button>
                <input type="submit" name="sbmbtn" value="<?php echo $formText_PerformSync_Output; ?>">
            </div>
        </form>
    </div>
    <script type="text/javascript" src="../modules/<?php echo $module?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
    <script type="text/javascript">

    $(document).ready(function() {
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
                        if(data.error !== undefined){
                            $("#popup-validate-message").html(data.error);
                            $("#popup-validate-message").show();
                        } else {
                            if(data.redirect_url !== undefined)
                            {
                                out_popup.addClass("close-reload");
                                out_popup.close();
                            }
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

        $('.datefield').datepicker({
            dateFormat: 'dd.mm.yy',
            firstDay: 1
        });
    });

    </script>
    <style>

    .selectDivModified {
        display:block;
    }
    .popupform, .popupeditform {
        width:100%;
        margin:0 auto;
        border:1px solid #e8e8e8;
        position:relative;
    }
    label.error { display: none !important; }
    .popupform .popupforminput.error { border-color:#c11 !important;}
    #popup-validate-message, .error-msg { font-weight:bold; color:#c11; padding-bottom:10px; }
    /* css for timepicker */
    .ui-timepicker-div .ui-widget-header { margin-bottom: 8px; }
    .ui-timepicker-div dl { text-align: left; }
    .ui-timepicker-div dl dt { height: 25px; margin-bottom: -25px; }
    .ui-timepicker-div dl dd { margin: 0 10px 10px 65px; }
    .ui-timepicker-div td { font-size: 90%; }
    .ui-tpicker-grid-label { background: none; border: none; margin: 0; padding: 0; }
    .clear {
        clear:both;
    }
    .inner {
        padding:10px;
    }
    .pplineV {
        position:absolute;
        top:0;bottom:0;left:70%;
        border-left:1px solid #e8e8e8;
    }
    .popupform input.popupforminput, .popupform textarea.popupforminput, .popupform select.popupforminput, .col-md-8z input {
        width:100%;
        border-radius: 4px;
        padding:5px 10px;
        font-size:12px;
        line-height:17px;
        color:#3c3c3f;
        background-color:transparent;
        -webkit-box-sizing: border-box;
           -moz-box-sizing: border-box;
             -o-box-sizing: border-box;
                box-sizing: border-box;
        font-weight:400;
        border: 1px solid #cccccc;
    }
    .popupform input.popupforminput.checkbox {
        width: auto;
    }
    .popupformname {
        font-size:12px;
        font-weight:bold;
        padding:5px 0px;
    }
    .popupforminput.botspace {
        margin-bottom:10px;
    }
    textarea {
        min-height:50px;
        max-width:100%;
        min-width:100%;
        width:100%;
    }
    .popupformname {
        font-weight: 700;
        font-size: 13px;
    }
    .popupformbtn {
        text-align:right;
        margin:10px;
    }
    .popupformbtn input {
        border-radius:4px;
        border:1px solid #0393ff;
        background-color:#0393ff;
        font-size:13px;
        line-height:0px;
        padding: 20px 35px;
        font-weight:700;
        color:#FFF;
        margin-left:10px;
    }
    .error {
        border: 1px solid #c11;
    }
    .popupform .lineTitle {
        font-weight:700;
    }
    .popupform .line .lineTitle {
        width:30%;
        float:left;
        font-weight:700;
        padding:5px 0;
    }

    .popupform .line .lineTitleWithSeperator {
        width:100%;
        margin: 20px 0;
        padding:0 0 10px;
        border-bottom:1px solid #EEE;
    }

    .popupform .line .lineInput {
        width:70%;
        float:left;
    }
    #out-customer-list .item-customer {
    	margin:10px 0px;
    	padding:10px;
    	border-radius:3px;
    	border:1px solid #D9D6D6;
    	background: #fff;
    }
    #out-customer-list .item-customer.error {
    	color:#C00;
    }
    #out-customer-list .item-top-title {
    	font-size: 16px;
    }
    #out-customer-list .priceLabel {
    	text-align: center;
    	font-size: 14px;
    	color: #ff1c1c;
    	font-weight: bold;
    }
    #out-customer-list .item-title > div {
    	float:left;
    	font-size:14px;
    	font-weight:bold;
    	margin-left: 5px;
    }
    #out-customer-list .item-title div.out-address {
    	float:right;
    }
    #out-customer-list .item-order {
    	padding-left:15px;
    }
    #out-customer-list .item-order .table > thead > tr > th {
    	border-bottom: 1px solid #ddd;
    }
    #out-customer-list .item-order table.table {
    	margin-bottom:0px;
    }
    #out-customer-list .item-totals span.spacer {
    	font-weight:bold;
    	padding-left:30px;
    }
    #out-customer-list .dividerLine {
    	border-right: 1px solid #ddd
    }
    </style>
    <?php
} else {
    echo $formText_NotActivedSync_output;
}
?>
