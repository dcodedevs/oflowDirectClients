<?php
$s_sql = "SELECT * FROM article_accountconfig";
$o_query = $o_main->db->query($s_sql);
$article_accountconfig = $o_query ? $o_query->row_array() : array();

$s_sql = "SELECT * FROM ownercompany_accountconfig";
$o_query = $o_main->db->query($s_sql);
$ownercompany_accountconfig = $o_query ? $o_query->row_array() : array();

$s_sql = "SELECT * FROM company_product_set ORDER BY name ASC";
$o_query = $o_main->db->query($s_sql);
$company_product_sets = $o_query ? $o_query->result_array() : array();

$employees = array();
$resultOnly = false;
$search = trim($o_main->db->escape_like_str($_POST['search']));
if($_POST['supplier_id'] > 0){
	$s_sql = "SELECT * FROM article
	WHERE article.content_status < 2 AND article.article_supplier_id = ".$o_main->db->escape($_POST['supplier_id'])." AND (article.name LIKE '%".$search."%' OR article.articleCode LIKE '%".$search."%') ORDER BY article.name ASC LIMIT 100";
} else {
	$s_sql = "SELECT * FROM article
	WHERE article.content_status < 2 AND (article.article_supplier_id is null OR article.article_supplier_id = 0) AND (article.name LIKE '%".$search."%' OR article.articleCode LIKE '%".$search."%') ORDER BY article.name ASC";
}
$o_query = $o_main->db->query($s_sql);
$employees = ($o_query ? $o_query->result_array() : array());

$s_sql = "SELECT * FROM customer WHERE id = ?";
$o_query = $o_main->db->query($s_sql, array($_POST['customerId']));
$customer = $o_query ? $o_query->row_array() : array();

$o_query = $o_main->db->get('customer_basisconfig');
$customer_basisconfig = $o_query ? $o_query->row_array() : array();

require_once("fnc_rewritebasisconfig.php");
rewriteCustomerBasisconfig();
if($ownercompany_accountconfig['activate_company_product_sets']  && count($company_product_sets) > 0) {
	if($_POST['ownercompany_id'] == 0){
		echo "<div style='padding: 5px;'>".$formText_SelectOwnercompanyBeforeAddingArticle_output."</div>";
		return;
	}
}
?>
<table class="table table-striped table-condensed">
	<tbody>
	<?php foreach($employees as $employee) {
		$o_query = $o_main->db->query("SELECT * FROM article_supplier WHERE id = '".$o_main->db->escape_str($employee['article_supplier_id'])."'");
		$articleSupplier = $o_query ? $o_query->row_array() : array();

        $price = $employee['price'];
        $discount = 0;

        if($article_accountconfig['activateArticlePriceMatrix'] && $customer['articlePriceMatrixId'] > 0){
            $s_sql = "SELECT * FROM articlepricematrixlines WHERE articleId = ? AND articlePriceMatrixId = ?";
            $o_query = $o_main->db->query($s_sql, array($employee['id'], $customer['articlePriceMatrixId']));
            if($o_query && $o_query->num_rows()>0){
                $priceMatrixValue = $o_query->row_array();
                $price = $priceMatrixValue['price'];
            }
        }
        if($article_accountconfig['activateArticleDiscountMatrix'] && $customer['articleDiscountMatrixId'] > 0){
            $s_sql = "SELECT * FROM articlediscountmatrixlines WHERE articleId = ? AND articleDiscountMatrixId = ?";
            $o_query = $o_main->db->query($s_sql, array($employee['id'], $customer['articleDiscountMatrixId']));
            if($o_query && $o_query->num_rows()>0){
                $discountMatrixValue = $o_query->row_array();
                $discount = $discountMatrixValue['discountPercent'];
            }
        }

		$main_article = array();
		$oldArticle = $employee;
		if($articleSupplier){
			$s_sql = "SELECT * FROM article WHERE id = ?";
			$o_query = $o_main->db->query($s_sql, array($articleSupplier['main_article']));
			$main_article = ($o_query ? $o_query->row_array() : array());
			$employee['name'] = $employee['articleCode']." ".$employee['name'];

			$employee['articleCode'] = $main_article['articleCode'];
			$employee['id'] = $main_article['id'];
			$employee['VatCodeWithVat'] = $main_article['VatCodeWithVat'];
			$employee['SalesAccountWithVat'] = $main_article['SalesAccountWithVat'];
		}
        $bookaccountNr = '';
        $vatCode = '';
        $vatPercent = '';

        // $taxFreeSale = $customer['taxFreeSale'];

        // if($taxFreeSale && !$employee['forceVat']) {
        //     $vatCode = $article_accountconfig['vatcode_default_when_without_vat'];
        //     $bookaccountNr = $employee['SalesAccountWithoutVat'];
        // } else {
            $vatCode = $employee['VatCodeWithVat'];
            $bookaccountNr = $employee['SalesAccountWithVat'];
        // }

        if($vatCode == "") {
            // if($taxFreeSale && !$employee['forceVat']) {
            //     $vatCode = $article_accountconfig['vatcode_default_when_without_vat'];
            // } else {
                $vatCode = $article_accountconfig['defaultVatCodeForArticle'];
            // }
        }

		$s_sql = "SELECT * FROM vatcode WHERE vatCode = ?";
		$o_query = $o_main->db->query($s_sql, array($vatCode));
		$vatItem = $o_query ? $o_query->row_array() : array();
		$vatPercent = $vatItem['percentRate'];
        if($bookaccountNr == ""){
            // if($taxFreeSale && !$employee['forceVat']) {
            //     $bookaccountNr = $article_accountconfig['defaultSalesAccountWithoutVat'];
            // } else {
                $bookaccountNr = $article_accountconfig['defaultSalesAccountWithVat'];
            // }
        }
	?>
	<tr>
		<td>
			<a href="#" class="tablescript" onclick="triggerSuggesstionClick(event, this)" data-employeeid="<?php echo $employee['id']?>"
				data-employeename="<?php echo htmlspecialchars($employee['name']);?>"
				data-articlenr="<?php if($article_accountconfig['activateArticleCode']){ echo $employee['articleCode']; } else { echo $employee['id']; } ?>"
				data-priceperpiece="<?php echo $price;?>"
				data-article-price="<?php echo number_format($employee['price'], 2, ".", ""); ?>"
				data-vatpercent="<?php echo $vatPercent;?>"
				data-quantity="<?php echo number_format(1, 4,",","")?>"
				data-discount="<?php echo number_format($discount, 2,",","")?>">
				<?php
				if($article_accountconfig['activateArticleCode']){
					echo "<span style='color: grey;'>". $oldArticle['articleCode']."</span> ". $oldArticle['name'];
				} else {
					echo $oldArticle['name'];
				}
				?>
				<div class="accountingInitWrapper" style="display: none;">
					<div>
	                    <div class="errorText" style="display: none;"></div>
	                    <span class="accountingInfo"><?php echo $bookaccountNr; if($vatCode != "") echo " - ".$vatCode; if($periodising) echo " - P";?></span>
	                    <span class="glyphicon glyphicon-pencil edit-accountingInfo"></span>
	                </div>
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
	                                <option value="<?php echo $row['accountNr']; ?>" <?php echo $row['accountNr'] == $bookaccountNr ? 'selected="selected"' : ''; ?>>
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
	                            foreach($rows as $row){
									?>
	                                <option data-percent="<?php echo $row['percentRate'];?>" value="<?php echo $row['vatCode']; ?>" <?php echo $row['vatCode'] == $vatCode ? 'selected="selected"' : ''; ?>>
	                                    <?php echo $row['vatCode']." - ".$row['name']; ?>
	                                </option>
	                            <?php
	                            } ?>
	                        </select>
	                    </span><br/>
	                    <label><?php echo $formText_Periodising_Output; ?></label>
	                    <span class="periodisingWrapper accountInfoSelect">
	                        <select name="periodization[]" class="periodization">
	                            <option value="0" <?php if($order['periodization'] == 0) { echo 'selected';}?>><?php echo $formText_None_output; ?></option>
	                            <option value="1" <?php if($order['periodization'] == 1) { echo 'selected';}?>><?php echo $formText_DivideOnMonths_output; ?></option>
	                            <option value="2" <?php if($order['periodization'] == 2) { echo 'selected';}?>><?php echo $formText_DivideOnDays_output; ?></option>
	                        </select>
	                    </span>
	                    <div class="clear"></div>
	                    <div class="output-btn close-accountinginfo"><?php echo $formText_Close_output;?></div>
	                </div>
	            </div>
			</a>
		</td>
	</tr>
	<?php } ?>
	</tbody>
</table>
<?php
if($l_total_pages > 1)
{
	?><ul class="pagination pagination-sm" style="margin:0;"><?php
	for($l_x = 0; $l_x < $l_total_pages; $l_x++)
	{
		if($l_x < 1 || ($l_x > ($l_page - 7) && $l_x < ($l_page + 7)) || $l_x >= ($l_total_pages - 1))
		{
			$b_print_space = true;
			?><li<?php echo ($l_page==$l_x ? ' class="active"' : '');?>><a href="#"><?php echo ($l_x+1);?></a></li><?php
		} else if($b_print_space) {
			$b_print_space = false;
			?><li><a onClick="javascript:return false;">...</a></li><?php
		}
	}
	?></ul><?php
}?>
<script type="text/javascript">
	function triggerSuggesstionClick(ev, element){
		ev.preventDefault();
		var parent = $(element).parents(".articleRow");
		var employeeID = $(element).data("employeeid");
		var employeeName = $(element).data("employeename");
		var articleNr = $(element).data("articlenr");
		var articleprice = $(element).data("article-price").toString();
		var pricePerPiece = $(element).data("priceperpiece");
		var quantity = $(element).data("quantity");
		var discount = $(element).data("discount");
		var taxPercent = $(element).data("vatpercent");
		if(employeeID > 0){
			pricePerPiece = pricePerPiece.toString().replace(".", ",");
			quantity = quantity.toString().replace(".", ",");
			discount = discount.toString().replace(".", ",");
			prepaidCommonCost = $(element).data("prepaidcommoncost");
			marketingContribution = $(element).data("marketingcontribution");
			<?php if($_POST['addNewRow']) { ?>
                $(".articleTableWrapper").append("<tr class='articleRow' data-tax='"+taxPercent+"'><td width='10%'><div class='articleIDwrapper'><span class='articleID'>"+articleNr+"</span><input type='hidden' name='articleId[]' class='articleIdInput' value='"+employeeID+"'/><span class='glyphicon glyphicon-pencil edit-articleid'></span></div>"+
                    "<div class='employeeSearch' style='display: none;'>"+
                    "<span class='glyphicon glyphicon-search'></span>"+
                    "<input type='text' placeholder='<?php echo $formText_Search_output;?>' class='articleName' style='width:100%;' autocomplete='off'/>"+
                    "<span class='glyphicon glyphicon-triangle-right'></span>"+
                    "<div class='employeeSearchSuggestions allowScroll'></div>"+
                    "</div></td>"+
                    "<td width='30%' class='articleNameTd'><input type='text' name='articleName[]' class='articleNameInput' value='"+employeeName+"' style='margin-bottom: 5px;' autocomplete='off'/><span class='articleNameArticle' style='display: none;'>"+employeeName+"</span>"+
                    "<td width='20%' class='accountingInfoTable'>"+$(element).find(".accountingInitWrapper").html()+
                    "</td>"+
					"<td width='10%' class='quantity' data-value='"+quantity+"' autocomplete='off'><input type='text' name='quantity[]' class='numberInput quantityInput' data-decimal_amount='4' value='"+quantity+"' autocomplete='off'/></td>"+
					<?php if(intval($_POST['customerId']) == 0) { ?>
					"<td width='5%' class='usePriceFromArticle' data-article-price='"+articleprice+"'>"+
						"<input type='checkbox' name='usePriceFromArticleCheckbox' class='usePriceFromArticleCheckbox'>"+
						"<input type='hidden' name='articleOrIndividualPrice[]' class='usePriceFromArticleCheckboxInput' value='0'>"+
					"</td>"+
					<?php } ?>
					"<td width='10%' class='pricePerPiece' data-value='"+pricePerPiece+"'>"+
						"<input type='text' name='pricePerPiece[]' class='numberInput pricePerPieceInput' data-decimal_amount='2' value='"+pricePerPiece+"'/>"+
						"<span class='pricePerPieceArticle' style='display: none;'>"+articleprice.replace(".", ",")+"</span>"+
					"</td>"+
					"<td width='10%' class='discount' data-value='"+discount+"'><input type='text' name='discount[]' class='numberInput discountInput' data-decimal_amount='2' value='"+discount+"' autocomplete='off'/>&nbsp;%</td>"+
					<?php if ($_POST['cpiActive']): ?>
						"<td width='5%' class='cpiAdjustmentFactor'>"+
							"<input type='text' name='cpiAdjustmentFactor[]' class='numberInput cpiAdjustmentFactorInput' data-decimal_amount='2' value='100' autocomplete='off'>"+
						"</td>"+
					<?php endif;?>
					"<td class='rightAligned' width='10%'><span class='priceTotal'></span><span class='output-delete-neworderline output-btn small editBtnIcon'><span class='glyphicon glyphicon-trash'></span></span></td></tr>");

				$(".addNewOrderlineSearch .employeeSearchSuggestions").html("").hide();
				$(".addNewOrderlineSearch .employeeSearch").hide();
				$(".addNewOrderlineSearch .articleName ").val("");
			<?php } else { ?>
				parent.find(".articleNameInput").val(employeeName).show();
				parent.find(".articleNameArticle").html(employeeName);
				parent.find(".articleID").html(articleNr);
				parent.find(".articleIdInput").val(employeeID);
				parent.find(".articleIDwrapper").show();
				parent.find(".accountingInfoTable").html($(element).find(".accountingInitWrapper").html());
				parent.find(".usePriceFromArticle").html("<input type='checkbox' name='usePriceFromArticleCheckbox' class='usePriceFromArticleCheckbox'>"+
				"<input type='hidden' name='articleOrIndividualPrice[]' class='usePriceFromArticleCheckboxInput' value='0'>");
				parent.find(".quantity").html("<input type='text' name='quantity[]' class='quantityInput numberInput' data-decimal_amount='4' value='"+quantity+"' autocomplete='off'/>");
				parent.find(".pricePerPiece").html("<input type='text' name='pricePerPiece[]' class='pricePerPieceInput numberInput' data-decimal_amount='2' value='"+pricePerPiece+"' autocomplete='off'/>"+
				"<span class='pricePerPieceArticle' style='display: none;'>"+articleprice.replace(".", ",")+"</span>");
				parent.find(".discount").html("<input type='text' name='discount[]' class='discountInput numberInput' data-decimal_amount='2' value='"+discount+"' autocomplete='off'/>&nbsp;%");

				<?php if ($_POST['cpiActive']): ?>
					parent.find(".cpiAdjustmentFactor").html("<input type='text' name='cpiAdjustmentFactor[]' class='cpiAdjustmentFactorInput' value='100' autocomplete='off'>");
				<?php endif;?>
				parent.find(".usePriceFromArticle").data("article-price", articleprice);
				parent.find(".pricePerPiece").data("value", pricePerPiece);
				parent.find(".quantity").data("value", quantity);
				parent.find(".discount").data("value", discount);
				parent.find(".employeeSearchSuggestions").html("").hide();
				parent.find(".employeeSearch").hide();
				parent.find(".articleName").val("");
			<?php } ?>
			$(window).resize();
			calculateTotal();
	        rebindTable();
		}
	}
</script>
