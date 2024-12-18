<?php
$o_query = $o_main->db->get('customer_basisconfig');
$customer_basisconfig = $o_query ? $o_query->row_array() : array();

$subscriptionLineId = $_POST['subscriptionLineId'] ? ($_POST['subscriptionLineId']) : 0;
$subscriptionId = $_POST['subscriptionId'] ? ($_POST['subscriptionId']) : 0;
$customerId = $_POST['customerId'] ? ($_POST['customerId']) : 0;
$action = $_POST['action'] ? ($_POST['action']) : '';


if ($action == 'deleteSubscriptionLine' && $moduleAccesslevel > 110) {
    $sql = "DELETE FROM subscriptionline WHERE id = ?";
    $o_main->db->query($sql, array($subscriptionLineId));
}

if($moduleAccesslevel > 10) {
	if(isset($_POST['output_form_submit'])) {
        if ($subscriptionLineId) {
            $s_sql = "UPDATE subscriptionline SET
            updated = now(),
            updatedBy= ?,
            articleOrIndividualPrice= ?,
            articleName= ?,
            articleNumber= ?,
            discountPercent= ?,
            amount = ?,
            pricePerPiece = ?,
            subscribtionId= ?,
            prepaidCommonCost = ?,
            bookaccountCode = ?,
            vatCode = ?
            WHERE id = ?";
            $o_main->db->query($s_sql, array($variables->loggID, $_POST['articleOrIndividualPrice'], $_POST['articleName'], $_POST['articleNumber'], str_replace(",", ".", $_POST['discountPercent']), str_replace(",", ".", $_POST['amount']), str_replace(",", ".", $_POST['pricePerPiece']), $subscriptionId, $_POST['prepaidCommonCost'], $_POST['bookaccountNr'], $_POST['vatCode'], $subscriptionLineId));
            $fw_redirect_url = $_POST['redirect_url'];
        } else {
            $s_sql = "INSERT INTO subscriptionline SET
            created = now(),
            createdBy= ?,
            articleOrIndividualPrice= ?,
            articleName= ?,
            articleNumber= ?,
            discountPercent= ?,
            amount = ?,
            pricePerPiece = ?,
            subscribtionId= ?,
            prepaidCommonCost = ?,
            bookaccountCode = ?,
            vatCode = ?";
            $o_main->db->query($s_sql, array($variables->loggID, $_POST['articleOrIndividualPrice'], $_POST['articleName'], $_POST['articleNumber'], str_replace(",", ".", $_POST['discountPercent']), str_replace(",", ".", $_POST['amount']), str_replace(",", ".", $_POST['pricePerPiece']), $subscriptionId, $_POST['prepaidCommonCost'], $_POST['bookaccountNr'], $_POST['vatCode'],));
            $fw_return_data = $s_sql;
            $fw_redirect_url = $_POST['redirect_url'];
        }
	}
}
if($subscriptionId) {
    $s_sql = "SELECT subscriptiontype.* FROM subscriptionmulti LEFT OUTER JOIN subscriptiontype ON subscriptiontype.id = subscriptionmulti.subscriptiontype_id WHERE subscriptionmulti.id = ?";
    $o_query = $o_main->db->query($s_sql, array($subscriptionId));
    if($o_query && $o_query->num_rows()>0) {
        $subscriptionData = $o_query->row_array();
    }
}
if($subscriptionLineId) {

    $s_sql = "SELECT * FROM subscriptionline WHERE id = ?";
    $o_query = $o_main->db->query($s_sql, array($subscriptionLineId));
    if($o_query && $o_query->num_rows()>0) {
        $subscriptionLineData = $o_query->row_array();
    }
}
?>

<div class="popupform <?php echo $subscriptionLineData['articleOrIndividualPrice'] ? 'individualpriceform' : 'articleform'; ?>">
	<div id="popup-validate-message" style="display:none;"></div>
	<form class="output-form main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=editSubscriptionLineDetail";?>" method="post">
		<input type="hidden" name="fwajax" value="1">
		<input type="hidden" name="fw_nocss" value="1">
		<input type="hidden" name="output_form_submit" value="1">
		<input type="hidden" name="subscriptionLineId" value="<?php echo $subscriptionLineId;?>">
		<input type="hidden" name="subscriptionId" value="<?php echo $subscriptionId;?>">
		<input type="hidden" name="customerId" value="<?php echo $customerId;?>">
        <input type="hidden" name="redirect_url" value="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$customerId; ?>">
		<div class="inner">
			<div class="line articleLine">
				<div class="lineTitle"><?php echo $formText_ChooseArticle_Output; ?></div>
				<div class="lineInput">
					<select name="articleNumber" required>
						<option value=""><?php echo $formText_ChooseArticle_output; ?></option>
						<?php
                        $s_sql = "SELECT * FROM article ORDER BY name ASC";
                        $o_query = $o_main->db->query($s_sql);
                        if($o_query && $o_query->num_rows()>0) {
                            $rows = $o_query->result_array();
                        }
                        $s_sql = "SELECT * FROM article_accountconfig";
                        $o_query = $o_main->db->query($s_sql);
                        $article_accountconfig = $o_query ? $o_query->row_array() : array();

                        $s_sql = "SELECT * FROM customer WHERE id = ?";
                        $o_query = $o_main->db->query($s_sql, array($customerId));
                        $customer = $o_query ? $o_query->row_array() : array();

                        foreach($rows as $row) {
                            //update bookingaccount and vatcode
                            $s_sql = "SELECT * FROM article WHERE id = ?";
                            $o_query = $o_main->db->query($s_sql, array($row['id']));
                            $article = $o_query ? $o_query->row_array() : array();

                            $bookaccountNr = '';
                            $vatCode = '';
                            $vatPercent = '';

                            $taxFreeSale = $customer['taxFreeSale'];

                            if($taxFreeSale && !$article['forceVat']) {
                                $vatCode = $article_accountconfig['vatcode_default_when_without_vat'];
                                $bookaccountNr = $article['SalesAccountWithoutVat'];
                            } else {
                                $vatCode = $article['VatCodeWithVat'];
                                $bookaccountNr = $article['SalesAccountWithVat'];
                            }

                            if($vatCode == ""){
                                if($taxFreeSale && !$article['forceVat']) {
                                    $vatCode = $article_accountconfig['vatcode_default_when_without_vat'];
                                } else {
                                    $vatCode = $article_accountconfig['defaultVatCodeForArticle'];
                                }
                            }
                            if($bookaccountNr == ""){
                                if($taxFreeSale && !$article['forceVat']) {
                                    $bookaccountNr = $article_accountconfig['defaultSalesAccountWithoutVat'];
                                } else {
                                    $bookaccountNr = $article_accountconfig['defaultSalesAccountWithVat'];
                                }
                            }

                            $s_sql = "SELECT * FROM vatcode WHERE vatCode = ?";
                            $o_query = $o_main->db->query($s_sql, array($vatCode));
                            $vatItem = $o_query ? $o_query->row_array() : array();
                            $vatPercent = $vatItem['percentRate'];


                            $price = $row['price'];
                            $discount = '';

                            if($article_accountconfig['activateArticlePriceMatrix'] && $customer['articlePriceMatrixId'] > 0){
                                $s_sql = "SELECT * FROM articlepricematrixlines WHERE articleId = ? AND articlePriceMatrixId = ?";
                                $o_query = $o_main->db->query($s_sql, array($row['id'], $customer['articlePriceMatrixId']));
                                if($o_query && $o_query->num_rows()>0){
                                    $priceMatrixValue = $o_query->row_array();
                                    $price = $priceMatrixValue['price'];
                                }
                            }
                            if($article_accountconfig['activateArticleDiscountMatrix'] && $customer['articleDiscountMatrixId'] > 0){
                                $s_sql = "SELECT * FROM articlediscountmatrixlines WHERE articleId = ? AND articleDiscountMatrixId = ?";
                                $o_query = $o_main->db->query($s_sql, array($row['id'], $customer['articleDiscountMatrixId']));
                                if($o_query && $o_query->num_rows()>0){
                                    $discountMatrixValue = $o_query->row_array();
                                    $discount = $discountMatrixValue['discountPercent'];
                                }
                            }
                             ?>
							<option data-name="<?php echo $row['name']; ?>" data-bookaccountnr="<?php echo $bookaccountNr;?>" data-vatcode="<?php echo $vatCode;?>" data-price="<?php echo $price; ?>" data-discount="<?php echo $discount;?>" value="<?php echo $row['id']; ?>" <?php echo $row['id'] == $subscriptionLineData['articleNumber'] ? 'selected="selected"' : ''; ?>>
								<?php echo $row['name']; ?>
							</option>
						<?php } ?>
					</select>
				</div>
				<div class="clear"></div>
			</div>
    		<div class="line">
        		<div class="lineTitle"><?php echo $formText_Name_Output; ?></div>
        		<div class="lineInput">
                    <input type="text" class="popupforminput botspace" name="articleName" value="<?php echo $subscriptionLineData['articleName']; ?>" autocomplete="off">
                </div>
        		<div class="clear"></div>
    		</div>
            <div class="line">
                <div class="lineTitle"><?php echo $formText_ArticleOrIndividualPrice_Output; ?></div>
                <div class="lineInput">
                    <select name="articleOrIndividualPrice">
                        <option value="0"><?php echo $formText_ArticlePrice_output; ?></option>
                        <option value="1" <?php echo $subscriptionLineData['articleOrIndividualPrice'] ? 'selected="selected"' : ''; ?>><?php echo $formText_IndividualPrice_output; ?></option>
                    </select>
                </div>
                <div class="clear"></div>
            </div>


            <div class="line amountLine">
                <div class="lineTitle">
                    <?php
                    if($subscriptionData['periodUnit'] == 0){
                        echo $formText_AmountSubscriptionPerMonth_Output;
                    } else if($subscriptionData['periodUnit'] == 1) {
                        echo $formText_AmountSubscriptionPerYear_Output;
                    }
                    ?>
                </div>
                <div class="lineInput">
                    <input type="text" class="popupforminput botspace" name="amount" value="<?php echo number_format($subscriptionLineData['amount'], 2, ",", ""); ?>" autocomplete="off">
                </div>
                <div class="clear"></div>
            </div>

            <div class="line pricePerPieceLine">
                <div class="lineTitle"><?php echo $formText_PricePerPiece_Output; ?></div>
                <div class="lineInput">
                    <input type="text" class="popupforminput botspace" name="pricePerPiece" value="<?php echo number_format($subscriptionLineData['pricePerPiece'], 2, ",", ""); ?>" <?php echo $subscriptionLineData['articleOrIndividualPrice'] ? '' : 'readonly="true"'; ?> autocomplete="off">
                </div>
                <div class="clear"></div>
            </div>
            <div class="line discountLine">
                <div class="lineTitle"><?php echo $formText_Discount_Output; ?> %</div>
                <div class="lineInput">
                    <input type="text" class="popupforminput botspace" name="discountPercent" value="<?php echo number_format($subscriptionLineData['discountPercent'], 2, ",", ""); ?>" autocomplete="off">
                </div>
                <div class="clear"></div>
            </div>
            <div class="line priceLine">
                <div class="lineTitle">
                    <?php
                    if($subscriptionData['periodUnit'] == 0){
                        echo $formText_PricePerMonth_Output;
                    } else if($subscriptionData['periodUnit'] == 1) {
                        echo $formText_PricePerYear_Output;
                    }
                    ?>
                </div>
                <div class="lineInput">
                    <input type="text" class="popupforminput botspace" name="pricePerPeriod" value="<?php echo $subscriptionLineData['pricePerPeriod']; ?>" <?php echo $subscriptionLineData['articleOrIndividualPrice'] ? '' : 'readonly="true"'; ?> autocomplete="off">
                </div>
                <div class="clear"></div>
            </div>
            <?php if ($customer_basisconfig['activatePrepaidCommonCost']): ?>
                <div class="line prepaidCommonCostLine">
                    <div class="lineTitle"><?php echo $formText_PrepaidCommonCost_Output; ?></div>
                    <div class="lineInput">
                        <input type="checkbox" name="prepaidCommonCost" value="1" <?php echo $subscriptionLineData['prepaidCommonCost'] ? 'checked="checked"' : ''; ?>>
                    </div>
                    <div class="clear"></div>
                </div>
            <?php endif; ?>
            <div class="line">
                <div class="lineTitle"><?php echo $formText_BookAccountNr_Output; ?></div>
                <div class="lineInput ">
                    <span class="bookaccountNrWrapper">
                        <select name="bookaccountNr" <?php if($article_accountconfig['activateVatcodeMandatory']){ ?> required <?php } ?> disabled>
                            <option value=""><?php echo $formText_SelectBookAccountNr_output; ?></option>
                            <?php
                            $rows = array();
                            $s_sql = "SELECT * FROM bookaccount GROUP BY accountNr ORDER BY accountNr ASC";
                            $o_query = $o_main->db->query($s_sql);
                            if($o_query && $o_query->num_rows()>0) {
                                $rows = $o_query->result_array();
                            }
                            foreach($rows as $row){ ?>
                                <option value="<?php echo $row['accountNr']; ?>" <?php echo $row['accountNr'] == $subscriptionLineData['bookaccountCode'] ? 'selected="selected"' : ''; ?>>
                                    <?php echo $row['accountNr']." - ".$row['name']; ?>
                                </option>
                            <?php
                            } ?>
                        </select>
                    </span>
                    <span class="editBookaccountnr editBtnIcon"><span class="glyphicon glyphicon-pencil"></span></span>
                </div>
                <div class="clear"></div>
            </div>
            <div class="line">
                <div class="lineTitle"><?php echo $formText_VatCode_Output; ?></div>
                <div class="lineInput vatCodeWrapper">
                    <span class="vatCodeWrapper">
                        <select name="vatCode" <?php if($article_accountconfig['activateVatcodeMandatory']){ ?> required <?php } ?> disabled>
                            <option value=""><?php echo $formText_SelectVatCode_output; ?></option>
                            <?php
                            $rows = array();
                            $s_sql = "SELECT * FROM vatcode GROUP BY vatCode ORDER BY vatCode ASC";
                            $o_query = $o_main->db->query($s_sql);
                            if($o_query && $o_query->num_rows()>0) {
                                $rows = $o_query->result_array();
                            }
                            foreach($rows as $row){ ?>
                                <option value="<?php echo $row['vatCode']; ?>" <?php echo $row['vatCode'] == $subscriptionLineData['vatCode'] ? 'selected="selected"' : ''; ?>>
                                    <?php echo $row['vatCode']." - ".$row['name']; ?>
                                </option>
                            <?php
                            } ?>
                        </select>
                    </span>

                    <span class="editVatCode editBtnIcon"><span class="glyphicon glyphicon-pencil"></span></span>
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
            var disabled = $(form).find(':disabled').removeAttr('disabled');

            fw_loading_start();
            $.ajax({
                url: $(form).attr("action"),
                cache: false,
                type: "POST",
                dataType: "json",
                data: $(form).serialize(),
                success: function (data) {
                    disabled.attr('disabled','disabled');
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
    calculateTotal();
	$('[name="articleNumber"]').on('change', function(e) {
		var option = $(this).find(':selected');
		$('[name="articleName"]').val(option.data('name'));
		$('[name="pricePerPiece"]').val(option.data('price'));
        $('[name="amount"]').val(1);
		$('[name="discountPercent"]').val(option.data('discount'));

        $(".bookaccountNrWrapper select").val(option.data('bookaccountnr'));
        $(".vatCodeWrapper select").val(option.data('vatcode'));

        calculateTotal();
	});

	$('[name="articleOrIndividualPrice"]').on('change', function(e) {
		if ($(this).val() === '1') {
			$('[name="pricePerPeriod"]').attr('readonly', false);
            $('[name="pricePerPiece"]').attr('readonly', false);
            $('.popupform').removeClass('articleform').addClass('individualpriceform');
		} else {
			$('[name="pricePerPeriod"]').attr('readonly', true);
            $('[name="pricePerPiece"]').attr('readonly', true);
            $('.popupform').removeClass('individualpriceform').addClass('articleform');
            var option = $('[name="articleNumber"]').find(':selected');
            $('[name="articleName"]').val(option.data('name'));
            $('[name="pricePerPiece"]').val(option.data('price'));
            $('[name="amount"]').val(1);
            $('[name="discountPercent"]').val(0);
		}
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

    $(".editBookaccountnr").unbind("click").bind("click", function(){
        if($(".bookaccountNrWrapper select").prop("disabled")){
            $(".bookaccountNrWrapper select").prop("disabled", false);
        } else {
            $(".bookaccountNrWrapper select").prop("disabled", true);
        }
    })
    $(".editVatCode").unbind("click").bind("click", function(){
        if($(".vatCodeWrapper select").prop("disabled")){
            $(".vatCodeWrapper select").prop("disabled", false);
        } else {
            $(".vatCodeWrapper select").prop("disabled", true);
        }
    })

    function calculateTotal() {
        var discount = String($('[name="discountPercent"]').val()).replace(",",".");
        var multiplier = 1 - (discount / 100);
        var amount = String($('[name="amount"]').val()).replace(",",".");
        var pricePerPiece = String($('[name="pricePerPiece"]').val()).replace(",",".");
        var priceTotal = amount * String(pricePerPiece).replace(",",".");
        if (multiplier) priceTotal = priceTotal * multiplier;
        $('[name="pricePerPeriod"]').val(String(priceTotal.toFixed(2)).replace(".",","));
    }
});

</script>
<style>
.popupform .line .lineInput .bookaccountNrWrapper select {
    max-width: 80%;
}
.popupform .line .lineInput select:disabled {
    border: 0;
    -webkit-appearance: none;
    -moz-appearance: none;
    text-indent: 1px;
    text-overflow: '';
    background: #fff;
}
.editBtnIcon {
    color: #46b2e2;
    margin-left: 10px;
    cursor: pointer;
}
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

.popupform.articleform .pricePerPieceLine .popupforminput {
	border: none;
}
.popupform .priceLine .popupforminput {
	border:none;
}
</style>
