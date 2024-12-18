<?php
$orderId = isset($_POST['orderId']) ? $_POST['orderId'] : 0;
$customerId = isset($_POST['customerId']) ? $_POST['customerId'] : 0;
$action = isset($_POST['action']0 ? $_POST['action'] : '';

$ordersModuleId = "";
$o_query = $o_main->db->query("SELECT * FROM moduledata WHERE name = 'Support'");
if($o_query && $o_row = $o_query->row()) $ordersModuleId = $o_row->uniqueID;

// Ownercompanies
$ownercompanyBasisconfig = array();
$o_query = $o_main->db->query("SELECT * FROM ownercompany_basisconfig");
if($o_query && $o_query->num_rows()>0) $ownercompanyBasisconfig = $o_query->row_array();
$activateMultiOwnerCompanies = $ownercompanyBasisconfig['activateMultiOwnerCompanies'];
$ownercompanies = array();

$sql = "SELECT * FROM ownercompany";
$o_query = $o_main->db->query($sql);
if($o_query && $o_query->num_rows()>0)
foreach($o_query->result_array() as $row)
{
   array_push($ownercompanies, $row);
}

if($moduleAccesslevel > 10) {
	if(isset($_POST['output_form_submit'])) {
        if ($orderId) {
            $s_sql = "UPDATE orders SET
            updated = now(), updatedBy=?, contactPerson=?, articleNumber=?, articleName=?, describtion=?, amount=?, pricePerPiece=?, discountPercent=?,
            priceTotal=?, addOnInvoice=?, ownercompany_id=?, projectId=?, customerId=?
            WHERE id = ?";
            $o_main->db->query($s_sql, array($variables->loggID, $_POST['contactPerson'], $_POST['articleNumber'], $_POST['articleName'], $_POST['describtion'], str_replace(",", ".", $_POST['amount']), str_replace(",", ".", $_POST['pricePerPiece']), str_replace(",", ".", $_POST['discountPercent']), str_replace(",", ".", $_POST['priceTotal']), str_replace(",", ".", $_POST['priceTotal']), $_POST['addOnInvoice'], $_POST['ownercompany_id'], $_POST['projectId'], $_POST['customerId'], $orderId));
            $fw_return_data = $s_sql;
            $fw_redirect_url = $_POST['redirect_url'];
        } else {
			$s_sql = "INSERT INTO orders SET
			moduleID = ?, created = now(), createdBy=?, contactPerson=?, articleNumber=?, articleName=?, describtion=?, amount=?, pricePerPiece=?,
            discountPercent=?, priceTotal=?, addOnInvoice=?, ownercompany_id=?, projectId=?, customerId=?";
            $o_main->db->query($s_sql, array($ordersModuleId, $variables->loggID, $_POST['contactPerson'], $_POST['articleNumber'], $_POST['articleName'], $_POST['describtion'], str_replace(",", ".", $_POST['amount']), str_replace(",", ".", $_POST['pricePerPiece']), str_replace(",", ".", $_POST['discountPercent']), str_replace(",", ".", $_POST['priceTotal']), $_POST['addOnInvoice'], $_POST['ownercompany_id'], $_POST['projectId'], $_POST['customerId']));
            $fw_return_data = $s_sql;
            $fw_redirect_url = $_POST['redirect_url'];
		}
	}

    if(isset($_POST['updateAddOnInvoice'])) {
        $orderId = $_POST['orderId'] ? $_POST['orderId'] : 0;
        if($orderId > 0){
            $addOnInvoice = 0;
            if($_POST['addOnInvoice'] == "true"){
                $addOnInvoice = 1;
            }
            $s_sql = "UPDATE orders SET updated = now(), updatedBy=?, addOnInvoice=? WHERE id = ?";
            $o_main->db->query($s_sql, array($variables->loggID, $addOnInvoice, $orderId));
            $fw_return_data = $s_sql;
        }
        $fw_redirect_url = $_POST['redirect_url'];
    }
}

if ($action == 'deleteOrder' && $moduleAccesslevel > 110) {
    $sql = "DELETE FROM orders WHERE id = ?";
    $o_main->db->query($sql, array($orderId));
}

if($orderId) {
    $sql = "SELECT * FROM orders WHERE id = ?";
    $o_query = $o_main->db->query($sql, array($orderId));
	if($o_query && $o_query->num_rows()>0) $ordersData = $o_query->row_array();
}

function formatDate($date) {
    global $formText_NotSet_output;
    if ($date == '0000-00-00' || !$date || empty($date)) return '';
    return date('d.m.Y', strtotime($date));
}

function unformatDate($date) {
    $d = explode('.', $date);
    return $d[2].'-'.$d[1].'-'.$d[0];
}
?>

<div class="popupform">
	<div id="popup-validate-message" style="display:none;"></div>
	<form class="output-form main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=editOrder";?>" method="post">
		<input type="hidden" name="fwajax" value="1">
		<input type="hidden" name="fw_nocss" value="1">
		<input type="hidden" name="output_form_submit" value="1">
		<input type="hidden" name="orderId" value="<?php echo $orderId;?>">
		<input type="hidden" name="customerId" value="<?php echo $customerId;?>">
        <input type="hidden" name="redirect_url" value="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$orderId; ?>">
		<div class="inner">
            <?php if(count($ownercompanies) > 1){ ?>
            	<div class="line">
    				<div class="lineTitle"><?php echo $formText_ChooseOwnerCompany_Output; ?></div>
    				<div class="lineInput">
    				<select name="ownercompany_id" class="buildingOwner" required>
                        <option value=""><?php echo $formText_Select_output;?></option>
    					<?php foreach ($ownercompanies as $ownercompany): ?>
    						<option value="<?php echo $ownercompany['id']; ?>" <?php echo $ordersData['ownercompany_id'] == $ownercompany['id'] ? 'selected="selected"' : ''; ?>><?php echo $ownercompany['name']; ?></option>
    					<?php endforeach; ?>
    				</select>
    				</div>
    				<div class="clear"></div>
    			</div>
            <?php } else if(count($ownercompanies) == 1) {  ?>
                <input type="hidden" value="<?php echo $ownercompanies[0]['id']?>" name="ownercompany_id"  class="buildingOwner"/>
            <?php } ?>
            <div class="line">
                <div class="lineTitle"><?php echo $formText_Project_Output; ?></div>
                <div class="lineInput projectWrapper">

                </div>
                <div class="clear"></div>
            </div>
            <div class="line articleLine">
                <div class="lineTitle"><?php echo $formText_ContactPerson_Output; ?></div>
                <div class="lineInput">
                    <select name="contactPerson">
                        <option value=""><?php echo $formText_ContactPerson_output; ?></option>
                        <?php
                        $sql = "SELECT * FROM contactperson WHERE customerId = ? ORDER BY name ASC";
						$o_query = $o_main->db->query($sql, array($customerId));
						if($o_query && $o_query->num_rows()>0)
						foreach($o_query->result_array() as $row)
						{
							?><option value="<?php echo $row['id']; ?>" <?php echo $row['id'] == $ordersData['contactPerson'] ? 'selected="selected"' : ''; ?>>
                                <?php echo $row['name']; ?>
                            </option><?php
						}
						?>
                    </select>
                </div>
                <div class="clear"></div>
            </div>
            <div class="line articleLine">
                <div class="lineTitle"><?php echo $formText_ChooseArticle_Output; ?></div>
                <div class="lineInput">
                    <select name="articleNumber" required>
                        <option value=""><?php echo $formText_ChooseArticle_output; ?></option>
                        <?php
                        $sql = "SELECT * FROM article ORDER BY name ASC";
                        $o_query = $o_main->db->query($sql);
						if($o_query && $o_query->num_rows()>0)
						foreach($o_query->result_array() as $row)
						{
							?><option data-name="<?php echo $row['name']; ?>" data-price="<?php echo $row['price']; ?>" value="<?php echo $row['id']; ?>" <?php echo $row['id'] == $ordersData['articleNumber'] ? 'selected="selected"' : ''; ?>>
                                <?php echo $row['name']; ?>
                            </option><?php
						}
						?>
                    </select>
                </div>
                <div class="clear"></div>
            </div>
    		<div class="line">
        		<div class="lineTitle"><?php echo $formText_ArticleName_Output; ?></div>
        		<div class="lineInput">
                    <input type="text" class="popupforminput botspace" name="articleName" value="<?php echo $ordersData['articleName']; ?>" required>
                </div>
        		<div class="clear"></div>
    		</div>
            <div class="line">
                <div class="lineTitle"><?php echo $formText_Description_Output; ?></div>
                <div class="lineInput">
                    <textarea class="popupforminput botspace autoheight"  name="describtion"><?php echo $ordersData['describtion']; ?></textarea>
                </div>
                <div class="clear"></div>
            </div>
			<div class="line">
				<div class="lineTitle"><?php echo $formText_Amount_Output; ?></div>
				<div class="lineInput">
					<input type="text" class="popupforminput botspace" name="amount" value="<?php if($ordersData['amount'] != "") { echo  number_format($ordersData['amount'], 2, ",", ""); } else { echo  number_format(1, 2, ",", ""); }; ?>">
				</div>
				<div class="clear"></div>
			</div>
			<div class="line">
				<div class="lineTitle"><?php echo $formText_PricePerPice_Output; ?></div>
				<div class="lineInput">
					<input type="text" class="popupforminput botspace" name="pricePerPiece" value="<?php echo number_format($ordersData['pricePerPiece'], 2, ",", ""); ?>">
				</div>
				<div class="clear"></div>
			</div>
			<div class="line">
				<div class="lineTitle"><?php echo $formText_Discount_Output; ?> %</div>
				<div class="lineInput">
					<input type="text" class="popupforminput botspace" name="discountPercent" value="<?php echo number_format($ordersData['discountPercent'], 2, ",", ""); ?>">
				</div>
				<div class="clear"></div>
			</div>
			<div class="line priceTotalLine">
				<div class="lineTitle"><?php echo $formText_PriceTotal_Output; ?></div>
				<div class="lineInput">
					<input type="text" class="popupforminput botspace" name="priceTotal" value="<?php echo number_format($ordersData['priceTotal'], 2, ",", ""); ?>" readonly="true">
				</div>
				<div class="clear"></div>
			</div>
			<div class="line">
				<div class="lineTitle"><?php echo $formText_AddOnInvoice_Output; ?></div>
				<div class="lineInput">
					<input type="checkbox" class="popupforminput checkbox botspace" name="addOnInvoice" value="1" <?php echo $ordersData['addOnInvoice'] ? 'checked="checked"' : ''; ?>>
				</div>
				<div class="clear"></div>
			</div>
		</div>

		<div class="popupformbtn">
			<button type="button" class="output-btn b-large b-close"><?php echo $formText_Cancel_Output;?></button>
			<input type="submit" name="sbmbtn" value="<?php echo $formText_Save_Output; ?>">
		</div>
	</form>
</div>
<script type="text/javascript" src="../modules/<?php echo $module?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
<script type="text/javascript">

$(document).ready(function() {
    $("form.output-form").validate({
        submitHandler: function(form) {
            $.ajax({
                url: $(form).attr("action"),
                cache: false,
                type: "POST",
                dataType: "json",
                data: $(form).serialize(),
                success: function (data) {
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
    // $('.output-form').on('submit', function(e) {
    //     e.preventDefault();
    //     var data = {};
    //     $(this).serializeArray().forEach(function(item, index) {
    //         data[item.name] = item.value;
    //     });
    //     ajaxCall('editOrder', data, function (json) {
    //         if (json.redirect_url) document.location.href = json.redirect_url;
    //         else out_popup.close();
    //     });
    // });

    $('.datefield').datepicker({
        dateFormat: 'dd.mm.yy',
        firstDay: 1
    });


    $('[name="articleNumber"]').on('change', function(e) {
        var option = $(this).find(':selected');

        var discount = String($('[name="discountPercent"]').val()).replace(",",".");
        if (!discount) discount = 0;

        var amount = $('[name="amount"]').val();
        if (!amount) amount = 1;

        $('[name="articleName"]').val(option.data('name'));
        $('[name="pricePerPiece"]').val(option.data('price'));
        $('[name="discountPercent"]').val(discount);
        $('[name="amount"]').val(amount);

        calculateTotal();
    });

    $('[name="amount"]').on('change', function(e) {
        $(this).val(String($(this).val()).replace(".",","));
        calculateTotal();
    });

    $('[name="pricePerPiece"]').on('change', function(e) {
        $(this).val(String($(this).val()).replace(".",","));
        calculateTotal();
    });

    $('[name="discountPercent"]').on('change', function(e) {
        $(this).val(String($(this).val()).replace(".",","));
        calculateTotal();
    });

    function calculateTotal() {
        var discount = String($('[name="discountPercent"]').val()).replace(",",".");
        var multiplier = 1 - (discount / 100);
        var amount = String($('[name="amount"]').val()).replace(",",".");
        var pricePerPiece = String($('[name="pricePerPiece"]').val()).replace(",",".");
        var priceTotal = amount * String(pricePerPiece).replace(",",".");
        if (multiplier) priceTotal = priceTotal * multiplier;
        $('[name="priceTotal"]').val(String(priceTotal.toFixed(2)).replace(".",","));
    }

    $(".buildingOwner").change(function(){
        var data = {
            buildingOwnerId: $(this).val(),
            projectId: '<?php echo $ordersData['projectId']?>'
        };
        ajaxCall('getProjects', data, function(json) {
            $('.projectWrapper').html(json.html);
        });
    })
    $(".buildingOwner").change();

    function h(e) {
        $(e).css({'height':'auto','overflow-y':'hidden'}).height(e.scrollHeight);
    }
    $('.autoheight').each(function () {
        h(this);
    }).on('input', function () {
        h(this);
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
.priceTotalLine .popupforminput {
    border: none !important;
}
.popupform input.popupforminput.checkbox {
    width: auto;
}
</style>
