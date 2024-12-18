<?php
$s_sql = "SELECT * FROM company_product_set ORDER BY name ASC";
$o_query = $o_main->db->query($s_sql);
$company_product_sets = $o_query ? $o_query->result_array() : array();
if(count($company_product_sets) == 0){
	$_POST['set_id'] = 0;
}
if(isset($_POST['articleId'])){ $articleId = $_POST['articleId']; } else { $articleId = 0; }
if(isset($_POST['set_id'])){ $set_id = $_POST['set_id']; } else { $set_id = 0; }
//$articleId = $_POST['articleId'] ? $_POST['articleId'] : 0;
if(isset($_POST['action'])){ $action = $_POST['action']; } else { $action = ''; }
//$action = $_POST['action'] ? $_POST['action'] : '';
if($set_id == -1 && count($company_product_sets) > 0){
	echo $formText_PleaseSelectSet_output;
	return;
}
$s_sql = "SELECT * FROM article_basisconfig";
$o_query = $o_main->db->query($s_sql);
$article_basisconfig = $o_query ? $o_query->row_array() : array();
if($article_basisconfig == null){
	$article_basisconfig = array();
}

$s_sql = "SELECT * FROM article_accountconfig";
$o_query = $o_main->db->query($s_sql);
$article_accountconfig = $o_query ? $o_query->row_array() : array();

foreach($article_accountconfig as $key=>$value) {
	if($value > 0){
		$article_basisconfig[$key] = intval($value) - 1;
	}
}
$activateSystemArticleTypes = false;
if((isset($article_basisconfig['activate_prepaid_commoncost_type']) && $article_basisconfig['activate_prepaid_commoncost_type'])
|| (isset($article_basisconfig['activate_marketing_contribution_type']) && $article_basisconfig['activate_marketing_contribution_type'])
|| (isset($article_basisconfig['activate_parking_rent_type']) && $article_basisconfig['activate_parking_rent_type'])
|| (isset($article_basisconfig['activate_item_sales']) && $article_basisconfig['activate_item_sales'])){
	$activateSystemArticleTypes = true;
}
function isJson($string) {
	json_decode($string);
	return (json_last_error() == JSON_ERROR_NONE);
}
// Save
if($_POST['fix_articles']){
	$s_sql = "UPDATE article SET company_product_set_id = 0 WHERE company_product_set_id = -1";
	$o_query = $o_main->db->query($s_sql);
	return;
}
if($moduleAccesslevel > 10) {
    if(isset($_POST['output_form_submit'])) {
        $_POST['costPrice'] = str_replace(",", ".", $_POST['costPrice']);
        $_POST['price'] = str_replace(",", ".", $_POST['price']);
        $noError = true;
        $vatCodeError = false;
        $bookAccountError = false;
        $articleCodeError = false;
        $integrationHookError = false;
        $integrationHookErrorMessage = '';

        $orderIsSingleActivity = false;
        $s_sql = "SELECT * FROM vatcode WHERE vatCode = ?";
        $o_query = $o_main->db->query($s_sql, array($_POST['VatCodeWithVat']));
        $vatcodeItem = $o_query ? $o_query->row_array() : array();
        if(!$vatcodeItem){
            $noError = false;
            $vatCodeError = true;
        }

        $s_sql = "SELECT * FROM bookaccount WHERE accountNr = ?";
        $o_query = $o_main->db->query($s_sql, array($_POST['SalesAccountWithVat']));
        $bookaccountItem = $o_query ? $o_query->row_array() : array();
        if(!$bookaccountItem){
            $noError = false;
            $bookAccountError = true;
        }
        // $s_sql = "SELECT * FROM bookaccount WHERE accountNr = ?";
        // $o_query = $o_main->db->query($s_sql, array($_POST['SalesAccountWithoutVat']));
        // $bookaccountItem = $o_query ? $o_query->row_array() : array();
        // if(!$bookaccountItem){
        //     $noError = false;
        //     $bookAccountError = true;
        // }
        if($article_accountconfig['activateArticleCode'] == 1) {
			if($set_id > 0){
	            $s_sql = "SELECT * FROM article WHERE articleCode = ? AND company_product_set_id = ?";
	            $o_query = $o_main->db->query($s_sql, array($_POST['articleCode'], $set_id));
	            $article = $o_query ? $o_query->row_array() : array();
			} else {
	            $s_sql = "SELECT * FROM article WHERE articleCode = ?";
	            $o_query = $o_main->db->query($s_sql, array($_POST['articleCode']));
	            $article = $o_query ? $o_query->row_array() : array();
			}

            if($article){
                if($articleId){
                    if($article['id'] != $articleId) {
                        $noError = false;
                        $articleCodeError = true;
                    }
                } else {
                    $noError = false;
                    $articleCodeError = true;
                }
            }
        }

        // Get current external_sys_id
        if ($articleId) {
            $sql = "SELECT * FROM article WHERE id = ?";
            $o_query = $o_main->db->query($sql, array($articleId));
            $row_data = $o_query && $o_query->num_rows() ? $o_query->row_array() : array();
            $external_sys_id = $row_data['external_sys_id'];
        }
		if($article_accountconfig['show_external_sys_id']){
			$external_sys_id = $_POST['external_sys_id'];
		}

        // HOOK: activate_check_all_articles_in_external_sys_on_module_load
        if ($article_accountconfig['activate_check_article_in_external_sys_before_save']) {

			$sql = "SELECT * FROM ownercompany WHERE company_product_set_id = ?";
            $o_query = $o_main->db->query($sql, array($set_id));
            $ownercompany = $o_query && $o_query->num_rows() ? $o_query->row_array() : array();
            $hook_params = array(
				'ownercompany_id'=> $ownercompany['id'],
                'id' => $articleId,
                'account' => $_POST['SalesAccountWithVat'],
                'vat' => $_POST['VatCodeWithVat'],
                'articleCode' => $_POST['articleCode'] ? $_POST['articleCode'] : 0,
                'name' => $_POST['name'],
                'costWithoutVat' => $_POST['costPrice'],
                'priceWithoutVat' => $_POST['price']
            );

            $hook_file = __DIR__ . '/../../../../' . $article_accountconfig['path_check_article_in_external_sys_before_save'];
            if (file_exists($hook_file)) {
                require_once $hook_file;
                if (is_callable($run_hook)) {
                    $hook_result = $run_hook($hook_params);
                    unset($run_hook);
                }
            }
            // Error message
            if ($hook_result['error']) {
                $noError = false;
                $integrationHookError = true;
                $integrationHookErrorMessage = $hook_result['message'];
				if(isJson($integrationHookErrorMessage)){
					$integrationHookErrorMessageArray = json_decode($integrationHookErrorMessage, true);
					$integrationHookErrorMessage = "";
					foreach($integrationHookErrorMessageArray as $integrationHookErrorMessageSingle){
						$integrationHookErrorMessage .= $integrationHookErrorMessageSingle['field'].": ".$integrationHookErrorMessageSingle['message']."</br>";
					}
				}
            } else {
                $external_sys_id = $hook_result['product_data']['id'];
            }
        }

        if($noError){
            if(!$article_accountconfig['activateArticleCode']) {
                $_POST['articleCode'] = null;
            }

            if ($articleId) {
                $s_sql = "UPDATE article SET
                updated = now(),
                updatedBy=?,
                name= ?,
                costPrice = ?,
                price = ?,
                SalesAccountWithVat = ?,
                VatCodeWithVat = ?,
                articleCode = ?,
				accounting_reference_for_invoice_receiver = ?,
                external_sys_id = ?,
                comment = ?,
                articlegroup_id = ?,
                system_article_type = ?,
				company_product_set_id = ?
                WHERE id = ?";

                $o_main->db->query($s_sql, array($variables->loggID, $_POST['name'], $_POST['costPrice'], $_POST['price'], $_POST['SalesAccountWithVat'],  $_POST['VatCodeWithVat'], $_POST['articleCode'], $_POST['accounting_reference_for_invoice_receiver'], $external_sys_id, $_POST['comment'], $_POST['article_group'], $_POST['system_article_type'], $_POST['set_id'], $articleId));
                $fw_return_data = $s_sql;
                $fw_redirect_url = $_POST['redirect_url'];
            } else {
                $s_sql = "INSERT INTO article SET
                created = now(),
                createdBy= ?,
                name= ?,
                costPrice = ?,
                price = ?,
                SalesAccountWithVat = ?,
                VatCodeWithVat = ?,
                articleCode = ?,
                accounting_reference_for_invoice_receiver = ?,
                external_sys_id = ?,
                comment = ?,
                articlegroup_id = ?,
                system_article_type = ?,
				company_product_set_id = ?";

                $o_main->db->query($s_sql, array($variables->loggID, $_POST['name'], $_POST['costPrice'], $_POST['price'], $_POST['SalesAccountWithVat'], $_POST['VatCodeWithVat'], $_POST['articleCode'], $_POST['accounting_reference_for_invoice_receiver'], $external_sys_id, $_POST['comment'], $_POST['article_group'], $_POST['system_article_type'], $_POST['set_id']));
                $fw_return_data = $s_sql;
                $articleId =$o_main->db->insert_id();
                $fw_redirect_url = $_POST['redirect_url'];
            }
        } else {
            if($vatCodeError){
                $fw_error_msg = $formText_VatCodeNotFound_output;
            } else if($bookAccountError){
                $fw_error_msg = $formText_BookAccountNotFound_output;
            } else if($articleCodeError) {
                $fw_error_msg = $formText_ArticleCodeShouldBeUnique_output;
            }
            else if($integrationHookError) {
                $fw_error_msg = $integrationHookErrorMessage;
            }
        }
    }
}

if ($action == 'deleteArticle' && $moduleAccesslevel > 110) {
    $sql = "DELETE article FROM article WHERE article.id = ?";
    $o_main->db->query($sql, array($articleId));
}

if ($action == 'syncArticle') {
	if($articleId > 0 && $_POST['externalId'] > 0){
		$SalesAccountWithVat = $_POST['sales'];
		$VatCodeWithVat = $_POST['vat'];
		if($SalesAccountWithVat != "" && $VatCodeWithVat != "" ){
			$price = $_POST['price'];
			$cost = $_POST['cost'];
		    $sql = "UPDATE article SET external_sys_id = ?, SalesAccountWithVat = ?, VatCodeWithVat = ?, price = ?, costPrice = ? WHERE article.id = ?";
		    $o_main->db->query($sql, array($_POST['externalId'], $SalesAccountWithVat, $VatCodeWithVat, $price,$cost,  $articleId));
		} else {
			$fw_error_msg[]= $formText_MissingVatOrSalesAccount_output;
		}
	} else {
		$fw_error_msg[]= $formText_MissingExternalArticleId_output;
	}
	return;
}

$s_sql = "SELECT * FROM article WHERE id = ?";
$o_query = $o_main->db->query($s_sql, array($articleId));
$articleData = $o_query ? $o_query->row_array() : array();

function formatDate($date) {
    global $formText_NotSet_output;
    if ($date == '0000-00-00' || !$date || empty($date)) return '';
    return date('d.m.Y', strtotime($date));
}

function unformatDate($date) {
    $d = explode('.', $date);
    return $d[2].'-'.$d[1].'-'.$d[0];
}
$bookaccounts = array();
$filter_set_id = $set_id;
if($articleData) {
	$filter_set_id = $articleData['company_product_set_id'];
}
if(intval($filter_set_id) > 0) {
	$set_where = " AND (company_product_set_id = '".$o_main->db->escape_str($filter_set_id)."')";
} else {
	$set_where = " AND (company_product_set_id = 0 OR company_product_set_id is null)";
}

$s_sql = "SELECT * FROM bookaccount WHERE content_status < 2 ".$set_where." ORDER BY bookaccount.accountNr ASC";

$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
    $bookaccounts = $o_query->result_array();
}
$vatcodes = array();
$s_sql = "SELECT * FROM vatcode WHERE content_status < 2 ".$set_where." ORDER BY vatcode.vatCode ASC";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
    $vatcodes = $o_query->result_array();
}
?>

<div class="popupform">
    <div id="popup-validate-message" style="display:none;"></div>
    <form class="output-form main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=editArticle";?>" method="post">
        <input type="hidden" name="fwajax" value="1">
        <input type="hidden" name="fw_nocss" value="1">
        <input type="hidden" name="output_form_submit" value="1">
        <input type="hidden" name="articleId" value="<?php echo $articleId;?>">
		<?php if($articleData) { ?>
			<input type="hidden" name="set_id" value="<?php echo $articleData['company_product_set_id'];?>">
		<?php } else { ?>
        	<input type="hidden" name="set_id" value="<?php echo $set_id;?>">
		<?php } ?>
        <input type="hidden" name="redirect_url" value="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=list"; ?>">
        <div class="inner">
            <?php if($article_accountconfig['activate_article_group']) {
                $article_groups = array();
                $s_sql = "SELECT * FROM article_group ORDER BY article_group.name ASC";
                $o_query = $o_main->db->query($s_sql);
                if($o_query && $o_query->num_rows()>0){
                    $article_groups = $o_query->result_array();
                }
                ?>
                <div class="line">
                    <div class="lineTitle"><?php echo $formText_ArticleGroup_Output; ?></div>
                    <div class="lineInput">
                        <select name="article_group" <?php if($article_accountconfig['activate_article_group'] == 2) echo 'required';?>>
                            <option value=""><?php echo $formText_Select_output;?></option>
                            <?php
                            foreach($article_groups as $article_group) { ?>
                                <option value="<?php echo $article_group['id'];?>" <?php if($articleData['articlegroup_id'] == $article_group['id']) { echo 'selected'; } ?>><?php echo $article_group['name'];?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="clear"></div>
                </div>

            <?php } ?>
            <?php if($article_accountconfig['activateArticleCode']) { ?>
                <div class="line">
                    <div class="lineTitle"><?php echo $formText_ArticleCode_Output; ?></div>
                    <div class="lineInput">
                        <input type="text" class="popupforminput botspace" name="articleCode" value="<?php echo $articleData['articleCode']; ?>" <?php if($article_accountconfig['activateArticleCode'] == 1) { ?> required <?php } ?> autocomplete="off">
                    </div>
                    <div class="clear"></div>
                </div>

            <?php } ?>

            <?php if($activateSystemArticleTypes) { ?>
                <div class="line">
                    <div class="lineTitle"><?php echo $formText_SystemArticleType_Output; ?></div>
                    <div class="lineInput">
                        <select name="system_article_type">
                            <option value=""><?php echo $formText_None_output;?></option>
                            <?php
                            if($article_basisconfig['activate_prepaid_commoncost_type']){
                                ?>
                                <option value="1" <?php if($articleData['system_article_type'] == 1) { echo 'selected'; } ?>><?php echo $formText_PrepaidCommonCost_output;?></option>
                                <?php
                            }
                            if($article_basisconfig['activate_marketing_contribution_type']){
                                ?>
                                <option value="2" <?php if($articleData['system_article_type'] == 2) { echo 'selected'; } ?>><?php echo $formText_MarketingContribution_output;?></option>
                                <?php
                            }
							if($article_basisconfig['activate_parking_rent_type']){
                                ?>
                                <option value="3" <?php if($articleData['system_article_type'] == 3) { echo 'selected'; } ?>><?php echo $formText_ParkingRent_output;?></option>
                                <?php
                            }
							if($article_basisconfig['activate_item_sales']){
                                ?>
                                <option value="4" <?php if($articleData['system_article_type'] == 4) { echo 'selected'; } ?>><?php echo $formText_ItemSales_output;?></option>
                                <?php
                            }
                            ?>
                        </select>
                    </div>
                    <div class="clear"></div>
                </div>

            <?php } ?>
            <div class="line">
                <div class="lineTitle"><?php echo $formText_Name_Output; ?></div>
                <div class="lineInput">
                    <input type="text" class="popupforminput botspace" name="name" value="<?php echo $articleData['name']; ?>" required autocomplete="off">
                </div>
                <div class="clear"></div>
            </div>
            <div class="line">
                <div class="lineTitle"><?php echo $formText_CostPrice_Output; ?></div>
                <div class="lineInput">
                    <input type="text" class="popupforminput botspace" name="costPrice" value="<?php echo number_format($articleData['costPrice'], 2, ",", ""); ?>" autocomplete="off">
                </div>
                <div class="clear"></div>
            </div>
            <div class="line">
                <div class="lineTitle"><?php echo $formText_Price_Output; ?></div>
                <div class="lineInput">
                    <input type="text" class="popupforminput botspace" name="price" value="<?php echo number_format($articleData['price'], 2, ",", ""); ?>" autocomplete="off">
                </div>
                <div class="clear"></div>
            </div>
            <div class="line">
                <div class="lineTitle"><?php echo $formText_SalesAccountWithVat_Output; ?></div>
                <div class="lineInput">
                    <select name="SalesAccountWithVat" required>
                        <option value=""><?php echo $formText_Select_output;?></option>
                        <?php
                        $selected = false;
                        if($articleData) {
                            $selected = true;
                        }
                        foreach($bookaccounts as $bookaccount) { ?>
                            <option value="<?php echo $bookaccount['accountNr'];?>" <?php if($articleData['SalesAccountWithVat'] == $bookaccount['accountNr']) { echo 'selected'; $selected = true;} else if($article_accountconfig['defaultSalesAccountWithVat'] == $bookaccount['accountNr'] && !$selected) { echo 'selected'; $selected = true;}?>><?php echo $bookaccount['accountNr']." - ".$bookaccount['name'];?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="clear"></div>
            </div>
            <div class="line">
                <div class="lineTitle"><?php echo $formText_VatCodeWithVat_Output; ?></div>
                <div class="lineInput">

                    <select name="VatCodeWithVat" required>
                        <option value=""><?php echo $formText_Select_output;?></option>
                        <?php
                        $selected = false;
                        if($articleData) {
                            $selected = true;
                        }
                        foreach($vatcodes as $vatcode) { ?>
                            <option value="<?php echo $vatcode['vatCode'];?>" <?php if($articleData['VatCodeWithVat'] == $vatcode['vatCode']) { echo 'selected'; $selected = true;} else if($article_accountconfig['defaultVatCodeForArticle'] == $vatcode['vatCode'] && !$selected) { echo 'selected'; $selected = true;}?>><?php echo $vatcode['vatCode']." - ".$vatcode['name'];?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="clear"></div>
            </div>
			<div class="line">
                <div class="lineTitle"><?php echo $formText_AccountingReferenceForInvoiceReceiver_Output; ?></div>
                <div class="lineInput">
                    <input type="text" class="popupforminput botspace" name="accounting_reference_for_invoice_receiver" value="<?php echo $articleData['accounting_reference_for_invoice_receiver']; ?>" autocomplete="off">
                </div>
                <div class="clear"></div>
            </div>
            <?php if($article_accountconfig['activate_comment_field']) {?>
                <div class="line">
                    <div class="lineTitle"><?php echo $formText_Comment_Output; ?></div>
                    <div class="lineInput">
                        <input type="text" class="popupforminput botspace" name="comment" value="<?php echo $articleData['comment']; ?>" autocomplete="off">
                    </div>
                    <div class="clear"></div>
                </div>
            <?php } ?>

            <?php if($article_accountconfig['show_external_sys_id']) {?>
				<div class="line">
	                <div class="lineTitle"><?php echo $formText_ExternalId_Output; ?></div>
	                <div class="lineInput">
	                    <input type="text" class="popupforminput botspace" name="external_sys_id" value="<?php echo $articleData['external_sys_id']; ?>" required autocomplete="off">
	                </div>
	                <div class="clear"></div>
	            </div>
			<?php } ?>

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
</style>
