<?php
$o_query = $o_main->db->get('customer_basisconfig');
$customer_basisconfig = $o_query ? $o_query->row_array() : array();

$subscriptionLineId = $_POST['subscriptionLineId'] ? ($_POST['subscriptionLineId']) : 0;
$subscriptionId = $_POST['subscriptionId'] ? ($_POST['subscriptionId']) : 0;
$customerId = $_POST['customerId'] ? ($_POST['customerId']) : 0;
$action = $_POST['action'] ? ($_POST['action']) : '';


$s_sql = "SELECT * FROM batch_renewal_basisconfig ORDER BY id DESC";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
    $batch_renewal_basisconfig = $o_query->row_array();
}
$s_sql = "SELECT * FROM batch_renewal_accountconfig";
$o_query = $o_main->db->query($s_sql);
$batch_renewal_accountconfig = $o_query ? $o_query->row_array() : array();

$s_sql = "SELECT * FROM customer_accountconfig ORDER BY id DESC";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
    $v_customer_accountconfig = $o_query->row_array();
}

if($batch_renewal_accountconfig['activateCheckForProjectNr'] == 1){
    $batch_renewal_basisconfig['activateCheckForProjectNr'] = 1;
} else if($batch_renewal_accountconfig['activateCheckForProjectNr'] == 2) {
    $batch_renewal_basisconfig['activateCheckForProjectNr'] = 0;
}

if ($action == 'deleteSubscriptionLine' && $moduleAccesslevel > 110) {
    $sql = "DELETE FROM subscriptionline WHERE id = ?";
    $o_main->db->query($sql, array($subscriptionLineId));
}


if($subscriptionId) {
    $s_sql = "SELECT subscriptiontype.*, subscriptionmulti.priceAdjustmentType, subscriptionmulti.ownercompany_id FROM subscriptionmulti LEFT OUTER JOIN subscriptiontype ON subscriptiontype.id = subscriptionmulti.subscriptiontype_id WHERE subscriptionmulti.id = ?";
    $o_query = $o_main->db->query($s_sql, array($subscriptionId));
    if($o_query && $o_query->num_rows()>0) {
        $subscriptionData = $o_query->row_array();
    }

    $s_sql = "SELECT * FROM subscriptiontype WHERE id = ?";
    $o_query = $o_main->db->query($s_sql, array($subscriptionData['subscriptiontype_id']));
    if($o_query && $o_query->num_rows()>0) {
        $subscriptiontype = $o_query->row_array();
    }
}

if($moduleAccesslevel > 10) {
	if(isset($_POST['output_form_submit'])) {

        $s_sql = "SELECT * FROM customer WHERE customer.id = ?";
        $o_query = $o_main->db->query($s_sql, array($customerId));
        $customer = ($o_query ? $o_query->row_array() : array());

        $s_sql = "SELECT * FROM article_accountconfig";
        $o_query = $o_main->db->query($s_sql);
        $article_accountconfig = $o_query ? $o_query->row_array() : array();


        if ($subscriptionData) {
            // $sql = "UPDATE project SET
            // updated = now(),
            // updatedBy='".$variables->loggID."',
            // date='".date("Y-m-d", strtotime($_POST['date']))."',
            // name='".$_POST['projectName']."',
            // projectLeader='".$_POST['projectLeader']."',
            // status = '".$_POST['status']."',
            // notProject = '".$_POST['notProject']."',
            // contactpersonId = '".$_POST['contactPerson']."',
            // customerId = '".$_POST['customerId']."'
            // WHERE id = ?";
            // $o_query = $o_main->db->query($sql, $projectId);
            $globalError = false;
            foreach($_POST['articleId'] as $key=>$articleId){
                $pricePerPieceError = false;
                $pricePerPiece = str_replace(",", ".", $_POST['pricePerPiece'][$key]);
                if($pricePerPiece < 0){
                    $globalError = true;
                    $fw_error_msg[$key] = $formText_PricePerPieceCanNotBeNegativeUseNegativeAmount_output;
                }
            }
            if(!$globalError){
                foreach($_POST['articleId'] as $key=>$articleId){
                    $s_sql = "SELECT * FROM article WHERE article.id = ?";
                    $o_query = $o_main->db->query($s_sql, array($articleId));
                    $article = ($o_query ? $o_query->row_array() : array());
                    if($article){
                        $subscriptionlineItem = array();
                        if(isset($_POST['subscriptionlinesId'][$key])){
                            $s_sql = "SELECT * FROM subscriptionline WHERE subscriptionline.id = ?";
                            $o_query = $o_main->db->query($s_sql, array($_POST['subscriptionlinesId'][$key]));
                            $subscriptionlineItem = ($o_query ? $o_query->row_array() : array());
                        }
                        $customerId = $customer['id'];

                        $vatPercent = 0;

                        $noError = true;

                        $vatCodeError = false;
                        $bookAccountError = false;
                        $articleError = false;
                        $projectError = false;

                        $vatCode = $article['VatCodeWithVat'];
                        $bookaccountNr = $article['SalesAccountWithVat'];

                        if($v_customer_accountconfig['subscription_activate_edit_bookaccount_and_vatcode']){

                            $vatCode = $_POST['vatCode'][$key];
                            $bookaccountNr = $_POST['bookaccountNr'][$key];
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
                        }
                        if($vatCode == ""){
                            $vatCode = $article_accountconfig['defaultVatCodeForArticle'];
                        }
                        if($bookaccountNr == ""){
                            $bookaccountNr = $article_accountconfig['defaultSalesAccountWithVat'];
                        }

                        $s_sql = "SELECT * FROM article WHERE id = ?";
                        $o_query = $o_main->db->query($s_sql, array($articleId));
                        $bookaccountItem = $o_query ? $o_query->row_array() : array();
                        if(!$bookaccountItem){
                            $noError = false;
                            $articleError = true;
                        }

                        if($noError){
                            $articleName = $_POST['articleName'][$key];
                            $pricePerPiece = str_replace(",", ".", $_POST['pricePerPiece'][$key]);
                            $amount = str_replace(",", ".", $_POST['quantity'][$key]);
                            $discountPercent = str_replace(",", ".", $_POST['discount'][$key]);
                            $articleOrIndividualPrice = $_POST['articleOrIndividualPrice'][$key];
                            if(isset($_POST['cpiAdjustmentFactor'])){
                                $cpiAdjustmentFactor =  round(str_replace(",", ".", $_POST['cpiAdjustmentFactor'][$key]));
                            } else {
                                $cpiAdjustmentFactor = "100";
                            }


                            if($articleOrIndividualPrice){
                                $pricePerPiece = 0;
                            }

                            if($subscriptionlineItem){
                                $s_sql = "UPDATE subscriptionline SET
    				            updated = now(),
    				            updatedBy= ?,
    				            articleOrIndividualPrice= ?,
    				            articleNumber= ?,
    				            articleName= ?,
    				            amount = ?,
    				            pricePerPiece = ?,
    				            discountPercent= ?,
    				            subscribtionId= ?,
                                cpiAdjustmentFactor = ?,
                                bookaccountCode = ?,
                                vatCode = ?
    				            WHERE id = ?";
                                $o_main->db->query($s_sql, array($variables->loggID, $articleOrIndividualPrice, $article['id'], $articleName, str_replace(",", ".", $amount), str_replace(",", ".", $pricePerPiece), str_replace(",", ".", $discountPercent), $subscriptionId, $cpiAdjustmentFactor,$bookaccountNr, $vatCode, $subscriptionlineItem['id']));
                                $orderId = $subscriptionlineItem['id'];
                            } else {
                                $s_sql = "INSERT INTO subscriptionline SET
    				            created = now(),
    				            createdBy= ?,
    				            articleOrIndividualPrice= ?,
    				            articleNumber= ?,
    				            articleName= ?,
    				            amount = ?,
    				            pricePerPiece = ?,
    				            discountPercent= ?,
    				            subscribtionId= ?,
                                cpiAdjustmentFactor = ?,
                                bookaccountCode = ?,
                                vatCode = ?";
                                $o_main->db->query($s_sql, array($variables->loggID, $articleOrIndividualPrice, $article['id'], $articleName, str_replace(",", ".", $amount), str_replace(",", ".", $pricePerPiece), str_replace(",", ".", $discountPercent), $subscriptionId, $cpiAdjustmentFactor,$bookaccountNr, $vatCode));
                                $orderId = $o_main->db->insert_id();
                            }
                        } else {
                            if($vatCodeError){
                                $fw_error_msg[$key] = $formText_VatCodeDoesntExist_output;
                            }
                            if($bookAccountError){
                                $fw_error_msg[$key] = $formText_BookAccountDoesntExist_output;
                            }
                            if($articleError){
                                $fw_error_msg[$key] = $formText_InvalidArticleNumber_output;
                            }
                            if($projectError){
                                $fw_error_msg[$key] = $formText_InvalidProjectFAccNumber_output;
                            }
                        }
                    }
                }
            }
            if($fw_error_msg == "" || empty($fw_error_msg)){
                $fw_return_data = $o_main->db->insert_id();
                $fw_redirect_url = $_POST['redirect_url'];
            }

        }

    }
}

if($subscriptionData){
?>
 <div class="popupform">
    <div id="popup-validate-message" style="display:none;"></div>
    <form class="output-form main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=editSubscriptionLineDetailNew";?>" method="post">
        <input type="hidden" name="fwajax" value="1">
        <input type="hidden" name="fw_nocss" value="1">
        <input type="hidden" name="output_form_submit" value="1">
        <input type="hidden" name="subscriptionId" value="<?php echo $subscriptionId;?>" id="subscriptionId">
        <input type="hidden" class="ownercompanyId" name="ownercompany_id" value="<?php echo $subscriptionData['ownercompany_id'];?>">
        <input type="hidden" name="redirect_url" value="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$customerId; ?>">
        <div class="defaultForm">
            <div class="inner">
                <div class="popupformTitle"><?php echo $formText_SubscriptionLines_output;?></div>
            </div>
            <div class="articleTable">
                <table class="table table-bordered articleTableWrapper">
                    <tr>
                        <th width="10%"><?php echo $formText_ArticleNr_output;?></th>
                        <th width="25%"><?php echo $formText_ArticleName_output;?></th>
                        <th width="20%"><?php echo $formText_Accounting_output;?></th>
                        <th width="10%">
                            <?php
                            if($subscriptionType['periodUnit'] == 0){
                                echo $formText_AmountPerMonth_output."";
                            } else {
                                echo $formText_AmountPerYear_output."";
                            }
                            ?>
                        </th>
                        <th width="5%">
                            <?php
                            echo $formText_UsePriceFromArticle_output."";
                            ?>
                        </th>
                        <th width="10%">
                            <?php
                            if($subscriptionType['periodUnit'] == 0){
                                echo $formText_PricePerMonth_output."";
                            } else {
                                echo $formText_PricePerYear_output."";
                            }
                            ?>
                        </th>
                        <th width="10%"><?php echo $formText_Discount_output;?> </th>
            			<?php if ($subscriptionData['priceAdjustmentType'] == 2): ?>
                        <th width="5%"><?php echo $formText_CpiAdjustmentFactor_output;?></th>
                    	<?php endif;?>
                        <th width="10%"><?php echo $formText_PricePerPeriod_output;?></th>
                    </tr>
                    <?php
                    $s_sql = "SELECT * FROM subscriptionline WHERE subscriptionline.subscribtionId = ? ORDER BY subscriptionline.id ASC";
                    $o_query = $o_main->db->query($s_sql, array($subscriptionId));
                    $subscriptionLineDatas = ($o_query ? $o_query->result_array() : array());

                    $s_sql = "SELECT * FROM article_accountconfig";
                    $o_query = $o_main->db->query($s_sql);
                    $article_accountconfig = $o_query ? $o_query->row_array() : array();

                    foreach($subscriptionLineDatas as $subscriptionLineData){
                        $periodising = false;
                        $s_sql = "SELECT * FROM article WHERE article.id = ?";
                        $o_query = $o_main->db->query($s_sql, array($subscriptionLineData['articleNumber']));
                        $article = ($o_query ? $o_query->row_array() : array());
                        $o_query = $o_main->db->query("SELECT * FROM article_supplier WHERE id = '".$o_main->db->escape_str($article['article_supplier_id'])."'");
                        $articleSupplier = $o_query ? $o_query->row_array() : array();
                    ?>
                    <tr class='articleRow'>
                        <td width="10%">
                            <input type='hidden' name='subscriptionlinesId[]' value='<?php echo $subscriptionLineData['id']; ?>'/>
                            <div class="articleIDwrapper">
                                <span class="articleID"> <?php if($article_accountconfig['activateArticleCode']){ if($articleSupplier['supplier_prefix'] != "") echo $articleSupplier['supplier_prefix'].'_'; echo $article['articleCode']; } else { echo $article['id']; }?> </span>
                                <input type='hidden' name='articleId[]' class="articleIdInput" value='<?php echo $subscriptionLineData['articleNumber']; ?>'/>
                                <span class="glyphicon glyphicon-pencil edit-articleid"></span>
                            </div>
                            <div class='employeeSearch' style="display: none;">
                                <span class='glyphicon glyphicon-search'></span>
                                <input type='text' placeholder='<?php echo $formText_Search_output;?>' class='articleName' style='width:100%;' autocomplete="off"/>
                                <span class='glyphicon glyphicon-triangle-right'></span>
                                <div class='employeeSearchSuggestions allowScroll'></div>
                            </div>
                        </td>
                        <td width="25%"  class='articleNameTd'>
                            <input type='text' name='articleName[]' class='articleNameInput' value='<?php echo $subscriptionLineData['articleName']; ?>' autocomplete="off"/>

                            <span class="articleNameArticle" style="display: none;"><?php echo $article['name']; ?></span>
                        </td>
                        <td width="20%" class="accountingInfoTable">
                            <div>
                                <div class="errorText" style="display: none;"></div>
                                <span class="accountingInfo"><?php
                                if($v_customer_accountconfig['subscription_activate_edit_bookaccount_and_vatcode']) {
                                    echo $subscriptionLineData['bookaccountCode']; if($subscriptionLineData['vatCode'] != "") echo " - ".$subscriptionLineData['vatCode'];
                                } else {
                                    echo $article['SalesAccountWithVat']; if($article['VatCodeWithVat'] != "") echo " - ".$article['VatCodeWithVat'];
                                }
                                if($periodising) echo " - P";?></span>
                                <?php if($v_customer_accountconfig['subscription_activate_edit_bookaccount_and_vatcode']) { ?>
                                    <span class="glyphicon glyphicon-pencil edit-accountingInfo"></span>
                                <?php } ?>
                            </div>
                            <?php if($v_customer_accountconfig['subscription_activate_edit_bookaccount_and_vatcode']) { ?>
                            <div class="accountinfoWrapper" style="display: none;">
                                <label><?php echo $formText_BookAccountNr_Output; ?></label>
                                <span class="bookaccountNrWrapper accountInfoSelect">
                                    <select name="bookaccountNr[]" <?php if($article_accountconfig['activateVatcodeMandatory']){ ?> required <?php } ?>>
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
                                </span><br/>
                                <label><?php echo $formText_VatCode_Output; ?></label>
                                <span class="vatCodeWrapper accountInfoSelect">
                                    <select name="vatCode[]" <?php if($article_accountconfig['activateVatcodeMandatory']){ ?> required <?php } ?>>
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
                                </span><br/>
                                <div class="clear"></div>
                                <div class="output-btn close-accountinginfo"><?php echo $formText_Close_output;?></div>
                            </div>
                            <?php } ?>
                        </td>
                        <td width='10%' class='quantity' data-value="<?php echo number_format($subscriptionLineData['amount'], 2, ".", ""); ?>">

                            <input type='text' name='quantity[]' class='quantityInput' value='<?php echo number_format($subscriptionLineData['amount'], 2, ",", ""); ?>' autocomplete="off"/>
                        </td>
                        <th width="5%" class='usePriceFromArticle' data-value="<?php echo $subscriptionLineData['articleOrIndividualPrice'] ? 1 : 0;?>" data-article-price="<?php echo number_format($article['price'], 2, ".", ""); ?>">
                            <input type="checkbox" name="usePriceFromArticleCheckbox" class='usePriceFromArticleCheckbox' <?php echo $subscriptionLineData['articleOrIndividualPrice'] ? 'checked="checked"' : ''; ?>>
                            <input type="hidden" name="articleOrIndividualPrice[]" class='usePriceFromArticleCheckboxInput' value="<?php echo $subscriptionLineData['articleOrIndividualPrice'] ? '1' : '0'; ?>">

                        </th>
                        <td width='10%' class='pricePerPiece' data-value="<?php echo number_format($subscriptionLineData['pricePerPiece'], 2, ".", ""); ?>">
                            <input type='text' name='pricePerPiece[]' class='pricePerPieceInput' value='<?php echo number_format($subscriptionLineData['pricePerPiece'], 2, ",", ""); ?>' autocomplete="off"/>
                            <span class="pricePerPieceArticle" style="display: none;"><?php echo number_format($article['price'], 2, ",", ""); ?></span>
                        </td>
                        <td width='10%' class='discount' data-value="<?php echo number_format($subscriptionLineData['discountPercent'], 2, ".", "");?>">

                            <input type='text' name='discount[]' class='discountInput' value='<?php echo number_format($subscriptionLineData['discountPercent'], 2, ",", ""); ?>' autocomplete="off"/>

                        </td>
            			<?php if ($subscriptionData['priceAdjustmentType'] == 2): ?>
                        	<td width='5%' class='cpiAdjustmentFactor' data-value="<?php if($subscriptionLineData['cpiAdjustmentFactor'] == null) { echo '100'; } else { echo number_format($subscriptionLineData['cpiAdjustmentFactor'], 0, ".", "");}?>">
                            	<input type="text" name="cpiAdjustmentFactor[]" class='cpiAdjustmentFactorInput' value="<?php if($subscriptionLineData['cpiAdjustmentFactor'] == null) { echo '100'; } else { echo number_format($subscriptionLineData['cpiAdjustmentFactor'], 0, ",", "");} ?>">
                        	</td>
                        <?php endif;?>
                        <td width='10%'>
                            <span class="priceTotal">
                                <?php echo number_format($subscriptionLineData['priceTotal'], 2, ",", ""); ?>
                            </span>

                            <span class="output-delete-orderline output-btn small editBtnIcon" data-order-id="<?php echo $subscriptionLineData['id'];?>" data-subscription-id="<?php echo $subscriptionId;?>"><span class="glyphicon glyphicon-trash"></span></span>
                        </td>
                    </tr>
                    <?php } ?>

                </table>
                <!-- <div class="addEntryBtn small output-add-article" data-project-id="<?php echo $cid; ?>"><?php echo $formText_AddSubscriptionlines_output;?></div> -->

                <div class='employeeSearch addNewOrderlineSearch'>
                    <span class='glyphicon glyphicon-search'></span>
                    <input type='text' placeholder='<?php echo $formText_SearchAndAdd_output;?>' class='articleName' style='width:100%;' autocomplete="off"/>
                    <span class='glyphicon glyphicon-triangle-right'></span>
                    <div class='employeeSearchSuggestions allowScroll'></div>
                </div>
                <div class="totalPriceBlock">
                    <?php echo $formText_Total_output;?>:
                    <div class="grandTotalPrice"></div>
                    <div class="clear"></div>
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
<script type="text/javascript" src="../modules/<?php echo $module;?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
<script type="text/javascript">

function numberWithSpaces(x) {
    var parts = x.toString().split(".");
    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, " ");
    return parts.join(".");
}
function calculateTotal(){
    var globalTotal = 0;
    $(".articleTable .articleRow").each(function(){
        var pricePerPiece = $(this).find(".pricePerPiece").data("value").toString().replace(",", ".");
        var quantity = $(this).find(".quantity").data("value").toString().replace(",", ".");
        var discount = $(this).find(".discount").data("value").toString().replace(",", ".");
        var totalPerRow = pricePerPiece * quantity * (100-discount)/100;
        totalPerRow = parseFloat(totalPerRow).toFixed(2)
        globalTotal += parseFloat(totalPerRow);
        totalPerRow = numberWithSpaces(totalPerRow);
        totalPerRow = totalPerRow.toString().replace(".", ",");
        $(this).find(".priceTotal").html(totalPerRow);
    })
    globalTotal = parseFloat(globalTotal).toFixed(2);
    globalTotal = numberWithSpaces(globalTotal);
    globalTotal = globalTotal.toString().replace(".", ",");
    $(".grandTotalPrice").html(globalTotal);
}
function rebindTable(){
    $(".prepaidCheckbox").change(function(){
        if($(this).is(":checked")){
            $(this).next(".prepaidCheckboxInput").val("1");
        } else {
            $(this).next(".prepaidCheckboxInput").val("0");
        }
    })
    $(".marketingContributionCheckbox").change(function(){
        if($(this).is(":checked")){
            $(this).next(".marketingContributionCheckboxInput").val("1");
        } else {
            $(this).next(".marketingContributionCheckboxInput").val("0");
        }
    })
    $(".usePriceFromArticleCheckbox").change(function(){
        if($(this).is(":checked")){
            var articlePrice = $(this).parents(".usePriceFromArticle").data("article-price");
            $(this).next(".usePriceFromArticleCheckboxInput").val("1");
            var pricePerPieceElement = $(this).parents("tr").find(".pricePerPiece");
            pricePerPieceElement.data("value", articlePrice);
            pricePerPieceElement.find(".pricePerPieceInput").hide().val(articlePrice.toString().replace(".", ","));
            pricePerPieceElement.find(".pricePerPieceArticle").show();
            <?php if($v_customer_accountconfig['use_articlename_when_use_articleprice']) { ?>
                var articleNameElement = $(this).parents("tr").find(".articleNameTd");
                articleNameElement.find(".articleNameArticle").show();
                articleNameElement.find(".articleNameInput").hide();
            <?php } ?>
            calculateTotal();
        } else {
            $(this).next(".usePriceFromArticleCheckboxInput").val("0");
            var pricePerPieceElement = $(this).parents("tr").find(".pricePerPiece");
            var price = pricePerPieceElement.data("value");
            pricePerPieceElement.data("value", price);
            pricePerPieceElement.find(".pricePerPieceInput").show().val(price.toString().replace(".", ","));
            pricePerPieceElement.find(".pricePerPieceArticle").hide();
            <?php if($v_customer_accountconfig['use_articlename_when_use_articleprice']) { ?>
                var articleNameElement = $(this).parents("tr").find(".articleNameTd");
                articleNameElement.find(".articleNameArticle").hide();
                articleNameElement.find(".articleNameInput").show();
            <?php } ?>

            calculateTotal();
        }

    })


    $(".accountInfoSelect").change(function(){
        var parent = $(this).parents("td");
        var label = parent.find(".accountingInfo");
        var bookaccountNr = parent.find(".bookaccountNrWrapper select").val();
        var vatcode = parent.find(".vatCodeWrapper select").val();
        var periodising = parent.find(".periodisingWrapper select").val();
        var finalText =bookaccountNr;
        if(vatcode != ""){
            finalText += " - "+vatcode;
        }
        if(periodising > 0){
            finalText += " - P";
        }
        label.html(finalText);
    })
    $(".close-accountinginfo").unbind("click").bind("click", function(){
        var parent = $(this).parents("td");
        var input = parent.find(".accountinfoWrapper");
        if(input.is(":visible")){
            input.hide();
        } else {
            input.show();
        }
    })
    $(".edit-articleid").unbind("click").bind("click", function(){
        var parent = $(this).parents("td");
        var label = parent.find(".articleNameText");
        var input = parent.find(".employeeSearch");
        if(input.is(":visible")){
            input.hide();
        } else {
            input.show();
        }
    })
    $(".edit-accountingInfo").unbind("click").bind("click", function(){
        var parent = $(this).parents("td");
        var input = parent.find(".accountinfoWrapper");
        if(input.is(":visible")){
            input.hide();
        } else {
            input.show();
        }
    })
    $(".output-delete-neworderline").off("click").on("click", function(){
        $(this).parents(".articleRow").remove();
    })

    $(".quantityInput").bind('keyup change', function(){
        $(this).parents(".quantity").data("value", $(this).val());
        calculateTotal();
    })
    $(".pricePerPieceInput").bind('keyup change', function(){
        $(this).parents(".pricePerPiece").data("value", $(this).val());
        calculateTotal();
    })
    $(".discountInput").bind('keyup change', function(){
        if($(this).val() < 0){
            $(this).val(0);
        }
        if($(this).val() > 100){
            $(this).val(100);
        }
        $(this).parents(".discount").data("value", $(this).val());
        calculateTotal();
    })

    var loadingArticle2 = false;
    var $input2 = $(".articleTableWrapper .articleName");
    var article_search_value2;
    $input2.each(function(index, el){
       var parent = $(el).parents("tr");
       $(el).unbind("focusin").on('focusin', function () {
           searchArticleSuggestions2(parent, true);
           $(".output-form").unbind("click").bind("click", function (ev) {
               if($(ev.target).parents(".employeeSearch").length == 0){
                   $(".employeeSearchSuggestions").hide();
               }
           });
       })
       //on keyup, start the countdown
       $(el).unbind("keyup").on('keyup', function () {
           searchArticleSuggestions2(parent, true);
       });

       //on keydown, clear the countdown
       $(el).unbind("keydown").on('keydown', function () {
           searchArticleSuggestions2(parent, true);
       });
    })
    function searchArticleSuggestions2 (parent, addLoading){
        if(!loadingArticle2) {
            if(article_search_value2 != parent.find(".articleName").val() || article_search_value2 == "") {
                loadingArticle2 = true;
                article_search_value2 = parent.find(".articleName").val();
                if(addLoading){
                    parent.find('.employeeSearchSuggestions').html('<div class="article-loading lds-ring"><div></div><div></div><div></div><div></div></div>').show();
                }
                var _data = { fwajax: 1, fw_nocss: 1, search: parent.find(".articleName").val(), customerId: '<?php echo $customer['id'];?>', cpiActive: '<?php if($subscriptionData['priceAdjustmentType'] == 2) echo 1; ?>', ownercompany_id: $(".ownercompanyId").val()};
                $.ajax({
                    cache: false,
                    type: 'POST',
                    dataType: 'json',
                    url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=get_articles_suggestions";?>',
                    data: _data,
                    success: function(obj){
                        loadingArticle2 = false;
                        if(parent.find(".articleName").val() != "" && article_search_value2 != parent.find(".articleName").val()){
                            searchArticleSuggestions2(parent, false);
                        } else {
                            $('.employeeSearchSuggestions').hide().html('');
                            parent.find('.employeeSearchSuggestions').html(obj.html).show();
                        }
                        <?php if(!$v_customer_accountconfig['subscription_activate_edit_bookaccount_and_vatcode']) {?>
                            $(".accountinfoWrapper").remove();
                        <?php } ?>
                    }
                }).fail(function(){
                    loadingArticle2 = false;
                })
            }
        }
    }
}
$(document).ready(function() {

    $(".notProjectCheckbox").change(function(){
        if($(this).is(":checked")) {
            $(".projectNameWrapper").find("input").prop('required', false);
            $(".projectLeaderWrapper").find("input").prop('required', false);
            $(".projectNameWrapper").hide();
            $(".projectLeaderWrapper").hide();

            $(".projectStatusWrapper").hide();
        } else {
            $(".projectNameWrapper").show();
            $(".projectLeaderWrapper").show();
            $(".projectNameWrapper").find("input").prop('required', true);
            $(".projectLeaderWrapper").find("input").prop('required', true);

            $(".projectStatusWrapper").show();
        }
    })
    $(".notProjectCheckbox").change();
    $("form.output-form").validate({
        ignore: [],
        submitHandler: function(form) {
            fw_loading_start();
            $(".errorText").hide().html("");
            var serializeValue = $(form).serialize();

            $.ajax({
                url: $(form).attr("action"),
                cache: false,
                type: "POST",
                dataType: "json",
                data: serializeValue,
                success: function (data) {
                    fw_loading_end();
                    if(data.data == "confirmation") {
                        $(".popupform .output-form").append("<input type='hidden' name='forceUpdate' value='1'/>");
                        $(".popupform .defaultForm").hide();
                        $(".popupform .confirmationForm").show();
                    } else if(data.data == "deletedOrderline") {
                        out_popup.addClass("close-reload");
                        out_popup2.addClass("deleted").data("order-id", <?php echo $_POST['orderlineid'];?>);
                        out_popup2.close();
                    } else if(data.data == "deletedOrder") {
                        out_popup.addClass("close-reload");
                        out_popup.close();
                    } else {
                        if(data.error !== undefined)
                        {
                            if(data.data !== undefined) {
                                $("#subscriptionId").val(data.data);
                            }
                            $.each(data.error, function(index, value){
                                var _type = Array("error");
                                if(index.length > 0 && index.indexOf("_") > 0) _type = index.split("_");
                                $(".articleTableWrapper .articleRow").eq(index).find(".accountingInfoTable .errorText").html(value).show();
                            });
                            $("#popup-validate-message").show();
                            fw_loading_end();
                            fw_click_instance = fw_changes_made = false;
                        } else {
                            if(data.redirect_url !== undefined)
                            {
                                out_popup.addClass("close-reload");
                                out_popup.close();
                            }
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
        },
        errorPlacement: function(error, element) {
            if(element.attr("name") == "customerId") {
                error.insertAfter(".selectCustomer");
            }
            if(element.attr("name") == "projectLeader") {
                error.insertAfter(".popupform .selectEmployee");
            }
        },
        messages: {
            customerId: "<?php echo $formText_SelectTheCustomer_output;?>",
            projectLeader: "<?php echo $formText_SelectProjectLeader_output;?>",
        }
    });
    $(".datefield").datepicker({
        firstDay: 1,
        dateFormat: "dd.mm.yy"
    })

    $(".selectCustomer").unbind("click").bind("click", function(){
        fw_loading_start();
        var _data = { fwajax: 1, fw_nocss: 1};
        $.ajax({
            cache: false,
            type: 'POST',
            dataType: 'json',
            url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=get_customers";?>',
            data: _data,
            success: function(obj){
                $('#popupeditboxcontent2').html('');
                $('#popupeditboxcontent2').html(obj.html);
                out_popup2 = $('#popupeditbox2').bPopup(out_popup_options);
                $("#popupeditbox2:not(.opened)").remove();
                fw_loading_end();
            }
        });
    })
    $(".output-add-article").unbind("click").bind("click", function(){

        $(".articleTableWrapper").append("<tr class='articleRow'><td width='10%'><div class='articleIDwrapper' style='display:none;'><span class='articleID'></span><input type='hidden' name='articleId[]' class='articleIdInput' value=''/><span class='glyphicon glyphicon-pencil edit-articleid'></span></div>"+
            "<div class='employeeSearch'>"+
            "<span class='glyphicon glyphicon-search'></span>"+
            "<input type='text' placeholder='<?php echo $formText_Search_output;?>' class='articleName' style='width:100%;' autocomplete='off'/>"+
            "<span class='glyphicon glyphicon-triangle-right'></span>"+
            "<div class='employeeSearchSuggestions allowScroll'></div>"+
            "</div></td>"+
            "<td width='30%'><input type='text' name='articleName[]' class='articleNameInput' value='' style='display: none; margin-bottom: 5px;' autocomplete='off'/>"+
            "<td width='20%' class='accountingInfoTable'>"+
            "</td>"+
            "<td width='10%' class='pricePerPiece' data-value=''></td><td width='10%' class='quantity' data-value=''></td><td width='10%' class='discount' data-value=''></td><td width='10%' class='priceTotal'></td></tr>");
        rebindTable();

    })
    $(".quantityInput").bind('keyup change', function(){
        $(this).parents(".quantity").data("value", $(this).val());
        calculateTotal();
    })
    $(".pricePerPieceInput").bind('keyup change', function(){
        $(this).parents(".pricePerPiece").data("value", $(this).val());
        calculateTotal();
    })
    $(".discountInput").bind('keyup change', function(){
        if($(this).val() < 0){
            $(this).val(0);
        }
        if($(this).val() > 100){
            $(this).val(100);
        }
        $(this).parents(".discount").data("value", $(this).val());
        calculateTotal();
    })
    $(".cpiAdjustmentFactorInput").bind('keyup change', function(){
        if($(this).val() < 0){
            $(this).val(0);
        }
        if($(this).val() > 100){
            $(this).val(100);
        }
        $(this).parents(".cpiAdjustmentFactor").data("value", $(this).val());
    })

    $(".output-delete-orderline").unbind("click").on('click', function(e){
        e.preventDefault();
        var self = $(this);
        var data = {
            subscriptionLineId: self.data('order-id'),
            subscriptionId: self.data('subscription-id'),
            action: 'deleteSubscriptionLine'
        };
        bootbox.confirm('<?php echo $formText_ConfirmDeleteSubscriptionline_output; ?>', function(result) {
            if (result) {
                ajaxCall('editSubscriptionLineDetailNew', data, function(json) {
                    if(json.data == "confirmation"){
                        $('#popupeditboxcontent2').html('');
                        $('#popupeditboxcontent2').html(json.html);
                        out_popup2 = $('#popupeditbox2').bPopup(out_popup_options);
                        $("#popupeditbox2:not(.opened)").remove();
                    } else {
                        self.closest('tr').remove();
                        out_popup.addClass("close-reload");
                    }
                });
            }
        }).css({"z-index": 99999});
    });

    var loadingArticle = false;
    var $input = $('.addNewOrderlineSearch .articleName');
    var article_search_value;
    var parent = $input.parents(".addNewOrderlineSearch");
    $input.on('focusin', function () {
        searchArticleSuggestions(parent, true);
        $(".output-form").unbind("click").bind("click", function (ev) {
            if($(ev.target).parents(".employeeSearch").length == 0){
                $(".employeeSearchSuggestions").hide();
            }
        });
    })
    //on keyup, start the countdown
    $input.on('keyup', function () {
        searchArticleSuggestions(parent, true);
    });
    //on keydown, clear the countdown
    $input.on('keydown', function () {
        searchArticleSuggestions(parent, true);
    });
    function searchArticleSuggestions (parent, addLoading){
        if(!loadingArticle) {
            if(article_search_value != parent.find(".articleName").val() || article_search_value == "") {
                loadingArticle = true;
                article_search_value = parent.find(".articleName").val();
                if(addLoading){
                    parent.find('.employeeSearchSuggestions').html('<div class="article-loading lds-ring"><div></div><div></div><div></div><div></div></div>').show();
                }
                var _data = { fwajax: 1, fw_nocss: 1, search: parent.find(".articleName").val(), customerId: '<?php echo $customer['id'];?>', addNewRow: 1,  cpiActive: '<?php if($subscriptionData['priceAdjustmentType'] == 2) echo 1; ?>', ownercompany_id: $(".ownercompanyId").val()};
                $.ajax({
                    cache: false,
                    type: 'POST',
                    dataType: 'json',
                    url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=get_articles_suggestions";?>',
                    data: _data,
                    success: function(obj){
                        loadingArticle = false;
                        if(parent.find(".articleName").val() != "" && article_search_value != parent.find(".articleName").val()){
                            searchArticleSuggestions(parent, true);
                        } else {
                            $('.employeeSearchSuggestions').hide().html('');
                            parent.find('.employeeSearchSuggestions').show().html(obj.html);
                        }
                        <?php if(!$v_customer_accountconfig['subscription_activate_edit_bookaccount_and_vatcode']) {?>
                            $(".accountinfoWrapper").remove();
                        <?php } ?>
                    }
                }).fail(function(){
                    loadingArticle = false;
                })
            }
        }
    }

    rebindTable();
    $(".usePriceFromArticleCheckbox").change();
    calculateTotal();
    $(".popupform .selectEmployee").unbind("click").bind("click", function(){
        fw_loading_start();
        var _data = { fwajax: 1, fw_nocss: 1, leader: 1};
        $.ajax({
            cache: false,
            type: 'POST',
            dataType: 'json',
            url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=get_employees";?>',
            data: _data,
            success: function(obj){
                fw_loading_end();
                $('#popupeditboxcontent2').html('');
                $('#popupeditboxcontent2').html(obj.html);
                out_popup2 = $('#popupeditbox2').bPopup(out_popup_options);
                $("#popupeditbox2:not(.opened)").remove();
            }
        });
})
    <?php if (!$subscriptionId) { ?>
        $(".popupform .addEntryBtn").click();
    <?php } ?>
});

</script>
<style>
.popupform input.popupforminput.shortinput {
    width: auto;
}
.articleIDwrapper {
    margin-bottom: 5px;
    line-height: 17px;
}
.edit-articleid {
    color: #46b2e2;
    float: right;
    cursor: pointer;
    margin-top: 4px;
}
.edit-accountingInfo {
    color: #46b2e2;
    float: right;
    cursor: pointer;
    margin-top: 4px;
}
#popupeditbox.popupeditbox {
    max-width: 1024px;
    width: 1024px;
}
.popupform .confirmationForm {
    display: none;
}
.output-delete-orderline {
    cursor: pointer;
    margin:0;
    float: right;
    margin-top: 2px;
}
.totalPriceBlock {
    font-weight: bold;
    float: right;
    margin-right: 15px;
}
.totalPriceBlock .grandTotalPrice {
    font-weight: normal;
    float: right;
    padding-left: 15px;
}
.articleTableWrapper .articleName {
    border: 1px solid #cecece;
}
.articleTableWrapper .accountinfoWrapper {
    background: #fff;
    padding: 5px;
    float: right;
    position: relative;
    width: 200%;
    margin-right: -100%;
    border: 1px solid #cecece;
    margin-top: 5px;
}
.articleTableWrapper .accountinfoWrapper span {
    display: inline-block;
}
.articleTableWrapper .accountinfoWrapper label {
    display: inline-block !important;
    width: 100px;
}
.articleTable .employeeSearch {
    float: right;
    width: 300%;
    position: relative;
    margin-bottom: 0;
    margin-right: -200%;
}
.articleTable .addNewOrderlineSearch {
    width: 300px;
    margin-right: 0px;
    float: left;
}
.articleTable .employeeSearch .employeeSearchSuggestions {
    display: none;
    background: #fff;
    position: absolute;
    width: 100%;
    max-height: 200px;
    overflow: auto;
    z-index: 2;
    border: 1px solid #dedede;
    border-top: 0;
}
.articleTable .employeeSearch .employeeSearchSuggestions table {
    margin-bottom: 0;
}
#p_container .p_contentBlock .articleTable .employeeSearch .employeeSearchSuggestions td {
    padding: 5px 10px;
}
.articleTable .employeeSearch .glyphicon-triangle-right {
    position: absolute;
    top: 7px;
    right: 4px;
    color: #048fcf;
}
.articleTable .employeeSearch .glyphicon-search {
    position: absolute;
    top: 7px;
    left: 6px;
    color: #048fcf;
}
.articleTable .articleName {
    width: 100%;
    border: 1px solid #dedede;
    padding: 3px 15px 3px 25px;
}
.articleTable .employeeSearchInputBefore {
    width: 150px;
    border: 1px solid #dedede;
    padding: 3px 10px 3px 10px;
}
.articleTable .employeeSearchBtn {
    background: #0093e7;
    border-radius: 5px;
    margin-left: 3px;
    color: #fff;
    padding: 5px 15px;
    cursor: pointer;
    border: 0;
}
.articleRow .articleNameInput {
    width: 100%;
    padding: 3px 5px;
    border: 1px solid #dedede;
}
.articleRow .quantityInput {
    width: 100%;
    padding: 3px 5px;
    border: 1px solid #dedede;
}
.articleRow .pricePerPieceInput {
    width: 100%;
    padding: 3px 5px;
    border: 1px solid #dedede;
}
.articleRow .discountInput {
    width: 80%;
    padding: 3px 5px;
    border: 1px solid #dedede;
}
.articleRow .cpiAdjustmentFactorInput {
    width: 80%;
    padding: 3px 5px;
    border: 1px solid #dedede;
}
.output-add-article {
    cursor: pointer;
}
.lineInput .otherInput {
    margin-top: 10px;
}
.lineInput input[type="radio"]{
    margin-right: 10px;
    vertical-align: middle;
}
.lineInput input[type="radio"] + label {
    margin-right: 10px;
    vertical-align: middle;
}
.popupform .inlineInput input.popupforminput {
    display: inline-block;
    width: auto;
    vertical-align: middle;
    margin-right: 20px;
}
.popupform .inlineInput label {
    display: inline-block !important;
    vertical-align: middle;
}
.popupform .lineInput.lineWhole {
    font-size: 14px;
}
.popupform .lineInput.lineWhole label {
    font-weight: normal !important;
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
.invoiceEmail {
    display: none;
}
label.error {
    color: #c11;
    margin-left: 10px;
    border: 0;
    display: inline !important;
}
.errorText {
    color: #c11;
    font-size: 11px;
    margin-bottom: 5px;
}
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
    padding: 5px 0;
}
.close-accountinginfo {
    margin-left: 0;
    cursor: pointer;
}
</style>
<?php } ?>
