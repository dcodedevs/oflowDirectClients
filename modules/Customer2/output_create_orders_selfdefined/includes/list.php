<?php
$page = 1;
require_once __DIR__ . '/list_btn.php';
$display_option = intval($_GET['display_option']);
$selfdefined_field_id= $_GET['selfdefined_field_id'];
$selfdefined_value_type= $_GET['selfdefined_value_type'];
$selfdefined_price_per_piece = floatval(str_replace(" ", "", str_replace(",", ".", $_GET['selfdefined_price_per_piece'])));
$article_id = $_GET['article_id'];
$ownercompany_id = $_GET['ownercompany'];

$s_sql = "SELECT * FROM customer_selfdefined_fields WHERE id = ? ORDER BY customer_selfdefined_fields.sortnr";
$o_query = $o_main->db->query($s_sql, array($selfdefined_field_id));
$selected_selfdefinedField =$o_query ? $o_query->row_array() : array();

$s_sql = "SELECT * FROM article WHERE id = ?";
$o_query = $o_main->db->query($s_sql, array($article_id));
$article =$o_query ? $o_query->row_array() : array();

$s_sql = "SELECT * FROM customer_selfdefined_fields ORDER BY customer_selfdefined_fields.sortnr";
$o_query = $o_main->db->query($s_sql);
$selfdefinedFields =$o_query ? $o_query->result_array() : array();

$selectedInvoicesArray = array();
if(isset($_GET['selectedInvoices'])){
	$selectedInvoicesArray = explode(",", $_GET['selectedInvoices']);
}

$customersSelected = 0;
$customersCountWithErrors = 0;
$adjustPricePercent = isset($_GET['adjustPrice']) ? str_replace(",", ".", $_GET['adjustPrice']): 0;
ob_start();
if($selfdefined_field_id != "" && $article_id != "" && $article_id != "" && $ownercompany_id != "" && (($selfdefined_value_type == 1 && $selfdefined_price_per_piece > 0) || $selfdefined_value_type == 0) ){

	$customers = array();
	$sql_join = "";
	$sql_where = "";
	if($display_option == 0){
		$sql_join .= " JOIN subscriptionmulti s ON c.id = s.customerId";
		$sql_where .= " AND (s.content_status = 0 OR s.content_status is null)
		AND s.startDate <= NOW() AND (s.stoppedDate is null OR s.stoppedDate = '0000-00-00' OR (s.stoppedDate is not null && s.stoppedDate <> '0000-00-00' && s.stoppedDate > NOW()))";
	} else {
		$sql_join .= "";
		$sql_where .= "";
	}
	$s_sql = "SELECT c.*, csv.value as pricePerPiece
		 FROM customer c
		 LEFT OUTER JOIN customer_selfdefined_values csv ON csv.customer_id = c.id
		 ".$sql_join."
		 WHERE csv.selfdefined_fields_id = ? AND (csv.value is not null AND csv.value <> 0) AND csv.active = 1".$sql_where."
		 GROUP BY c.id
		 ORDER BY c.name ASC";
	$o_query = $o_main->db->query($s_sql, array($selected_selfdefinedField['id']));
	if($o_query && $o_query->num_rows()>0){
		$customers = $o_query->result_array();
	}
	foreach($customers as $customer) {
		$errors = array();
		$block_group_id = $customer['id'];
		$subscriptionLines = array();
		$subscriptionline = array();
		if($selfdefined_value_type == 0){
			$subscriptionline['pricePerPiece'] = $customer['pricePerPiece'];
			$subscriptionline['amount'] = 1;
		} else if($selfdefined_value_type == 1){
			$subscriptionline['pricePerPiece'] = $selfdefined_price_per_piece;
			$subscriptionline['amount'] = $customer['pricePerPiece'];
		}
		$subscriptionline['articleNumber'] = $article['id'];
		$subscriptionline['discountPercent'] = $article['discountPercent'];
		$subscriptionline['articleName'] = $_GET['articlename'];


		$subscriptionLines[] = $subscriptionline;

		if (!count($errors)) {
			$customersSelected++;
		}
		else {
			$customersCountWithErrors++;
		}
		?>
		<div class="item-customer">
			<div class="item-title">
				<div>
					<?php if(!count($errors) && !count($customer_errors) && !$allowInvoice_error): ?>
					<input type="checkbox" value="<?php echo $block_group_id;?>" name="customer[]" autocomplete="off" <?php if(isset($_GET['selectedAll']) && $_GET['selectedAll']){ echo 'checked';} else if(isset($_GET['selectedInvoices'])) { if(in_array($block_group_id,$selectedInvoicesArray)) echo 'checked'; } else { echo 'checked';}?>/>
					<?php endif; ?>
					<?php echo $customer['name'];?>
				</div>
				<br clear="all">
			</div>
			<div class="item-order">
				<table class="table table-condensed">
					<thead>
						<tr>
							<th>&nbsp;</th>
							<th><?php echo $formText_Text_Output;?></th>
							<th><?php echo $formText_Price_Output;?></th>
							<th><?php echo $formText_Amount_Output;?></th>
							<th><?php echo $formText_Discount_Output;?></th>
							<th>&nbsp;</th>
							<th class="text-right"><?php echo $formText_TotalPrice_Output;?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						foreach($subscriptionLines as $subscriptionLine) {
							$pricePerPiece = $subscriptionLine['pricePerPiece'];
							$priceTotal = $pricePerPiece*$subscriptionLine['amount']*(1-$subscriptionLine['discountPercent']/100);

							$subscriptionNameString = "";//$selected_selfdefinedField['name']." - ";
			                $articleName = $subscriptionNameString.$subscriptionLine['articleName'].$date_string;
							?>
							<tr>
								<td>&nbsp;</td>
								<td width="40%"><?php echo $articleName;?></td>
								<td><?php
									echo number_format($pricePerPiece, 2, ",", " ");
								?></td>
								<td><?php echo number_format($subscriptionLine['amount'], 2, ",", " ");?></td>
								<td><?php echo number_format($subscriptionLine['discountPercent'], 2, ",", " ");?></td>
								<td>&nbsp;</th>
								<td class="text-right totalPrice" data-price="<?php echo $priceTotal;?>"><?php
									echo number_format($priceTotal, 2, ",", " ");
								?></td>
							</tr>
							<?php
						}
						?>
					</tbody>
				</table>
			</div>
		</div>
		<?php
	}
	$customersCount = count($customers);
} else {

}
$listBuffer = ob_get_clean();
?>
<div id="p_container" class="p_container <?php echo $folderName; ?>">
	<div class="p_containerInner">
		<div class="p_content">
			<div class="p_pageContent">
				<div class="p_pageDetailsTitle"><?php echo $formText_CreateOrdersFromSelfdefinedFields_output;?></div>
				<div class="p_contentBlock">
					<div class="filter_row">
						<select class="display_option"  autocomplete="off">
							<option value="0" <?php if($display_option == 0) echo 'selected';?>><?php echo $formText_MembersOnly_output;?></option>
							<option value="1" <?php if($display_option == 1) echo 'selected';?>><?php echo $formText_All_output;?></option>
						</select>
						<span class="column_span">
							<label><?php echo $formText_SelfdefinedField_output;?></label>
							<select class="selfdefined_field_id" autocomplete="off">
								<option value=""><?php echo $formText_Select_output;?></option>
								<?php foreach($selfdefinedFields as $selfdefinedField) { ?>
									<option value="<?php echo $selfdefinedField['id']?>" <?php if($selected_selfdefinedField['id'] == $selfdefinedField['id']) echo 'selected';?>><?php echo $selfdefinedField['name'];?></option>
								<?php } ?>
							</select>
							<br/>
							<?php
							$valueType1Array = explode("<span", $formText_PutValueIntoPrice_output, 2);
							$valueType2Array = explode("<span", $formText_PutValueIntoAmountAndSpecifyPricePerPiece_output, 2);
							?>
							<select class="selfdefined_value_type" autocomplete="off">
								<option value="0" <?php if($selfdefined_value_type == 0) echo 'selected';?>><?php echo $valueType1Array[0];?></option>
								<option value="1" <?php if($selfdefined_value_type == 1) echo 'selected';?>><?php echo $valueType2Array[0];?></option>
							</select>
							<span class="selfdefined_value_type_0_info" <?php if($selfdefined_value_type == 0) echo 'style="display:inline-block;"'?>>
								<?php echo "<span".$valueType1Array[1]?>
							</span>
							<span class="selfdefined_value_type_1_info" <?php if($selfdefined_value_type == 1) echo 'style="display:inline-block;"'?>>
								<?php echo "<span".$valueType2Array[1]?>
							</span>
							<br/>
							<span class="selfdefined_price_per_piece" <?php if($selfdefined_value_type == 0) { ?> style="display:none;" <?php } ?>>
								<span class="price_per_piece" data-price="<?php echo $selfdefined_price_per_piece;?>"><?php echo number_format($selfdefined_price_per_piece, 2, ",", "");?></span>
								<span class="glyphicon glyphicon-pencil edit_price_per_piece"></span>
							</span>
						</span>
						<label><?php echo $formText_Article_output;?></label>
						<span class="selectArticleItem" data-id="<?php echo $article['id'];?>" data-articlename="<?php echo htmlentities($_GET['articlename'])?>"><?php if($article) { echo $_GET['articlename']; } else { echo $formText_SelectArticle_output;}?></span>

						<?php
						$s_sql = "SELECT * FROM ownercompany WHERE content_status < 2 ORDER BY id";
						$o_query = $o_main->db->query($s_sql);
						$ownerCompanyDatas = $o_query ? $o_query->result_array(): array();
						if (count($ownerCompanyDatas) > 1) {
						?>
							<label><?php echo $formText_OwnerCompany_output;?></label>
							<select class="ownercompany"  autocomplete="off">
								<option value=""><?php echo $formText_Select_output; ?></option>
								<?php
								foreach($ownerCompanyDatas as $ownerCompanyData) {
									?>
									<option value="<?php echo $ownerCompanyData['id'];?>" <?php if($ownercompany_id == $ownerCompanyData['id']) echo 'selected';?>><?php echo $ownerCompanyData['name']; ?></option>
									<?php
								}
								?>
							</select>
						<?php } else {
							$s_sql = "SELECT * FROM ownercompany WHERE content_status < 2 ORDER BY id";
							$o_query = $o_main->db->query($s_sql);
							$ownerCompanyData = $o_query ? $o_query->row_array(): array();
							?>
							<input type="hidden" class="ownercompany" value="<?php echo $ownerCompanyData['id']; ?>"/>
						<?php } ?>
					</div>
					<?php if($_GET['successfullyUpdated'] > 0) {
						?>
						<div style="font-size: 14px; margin-top: 10px;">
						<?php
						echo $formText_SuccessfullyCreatedOrdersFor_output." ".intval($_GET['successfullyUpdated'])." ".$formText_Customers_Output;
						?>
						<div class="createMore">
							<?php
							echo $formText_CreateMoreOrders_Output;
							?>
						</div>
						</div>
					<?php
					} else { ?>
						<div id="out-customer-list">
							<?php if ($customersCountWithErrors > 0): ?>
								<div id="out-error-box">
									<div class="alert alert-danger"><?php echo $customersCountWithErrors. ' ' . $formText_CustomerInvoicesHasErrors_output; ?></div>
								</div>
							<?php endif; ?>
							<div class="out-select-all">
								<?php if ($customersCount > 0): ?>
									<input type="checkbox" id="selectDeselectAll"  autocomplete="off" <?php if(isset($_GET['selectedAll'])) { if($_GET['selectedAll']) echo 'checked';} else { if (($customersCount - $customersCountWithErrors) == $customersSelected) echo 'checked="checked"'; } ?>> <?php echo $formText_SelectAll_output; ?>
									(<?php echo $customersCount." ".$formText_Customers_output;?>)
								<?php endif; ?>
							</div>
							<div class="clear"></div>

							<div class="out-dynamic">
								<?php echo $listBuffer; ?>
							</div>
							<div id="out-hook-error"></div>
							<div class="out-buttons">
						        <span class="totalSelectedInvoices"><?php echo $formText_Total_output;?>: <span class="totalInvoicesSelected"></span></span>
								<button id="out-update-orders" class="btn btn-default"><?php echo $formText_CreateOrders_Output;?></button>
							</div>
						</div>

						<div id="out-process-progress">
							<div class="progress-title"><span class="processed">0</span> / <span class="total">0</span></div>
							<div class="progress-bar2">
								<div class="progress-bar2-fill"></div>
							</div>
						</div>
					<?php } ?>
				</div>

			</div>
		</div>
	</div>
</div>
<style>
.column_span {
	display: inline-block;
	vertical-align: top;
}
.selfdefined_value_type_0_info {
	display: none;
}
.selfdefined_value_type_1_info {
	display: none;
}
</style>
<?php $list_filter = $_GET['list_filter'] ? $_GET['list_filter'] : 'all'; ?>
<script type="text/javascript">
var out_popup;
var out_popup_options={
	follow: [true, true],
	followSpeed: 300,
	fadeSpeed: 0,
	modalClose: false,
	escClose: false,
	closeClass:'b-close',
	onOpen: function(){
		$(this).addClass('opened');
		//$(this).find('.b-close').on('click', function(){out_popup.close();});
	},
	onClose: function(){
		if($(this).is('.close-reload')) {
			var redirectUrl = $(this).data("redirect");
			var percent = $(this).data("percent");
			if(redirectUrl !== undefined && redirectUrl != ""){
				document.location.href = redirectUrl;
			} else {
				reloadPage();
            }
          // window.location.reload();
        }
		$(this).removeClass('opened');
	}
};
function reloadPage(successfullyUpdated2){
	var selectedAll = $("#selectDeselectAll").is(":checked");
	var selectedInvoices = $(".item-title input:checked");
	var selectedInvoicesString = "";
	var successfullyUpdated = 0;
	var display_option = $(".display_option").val();
	var selfdefined_field_id = $(".selfdefined_field_id").val();
	var article_id = $(".selectArticleItem").data("id");
	var articlename = $(".selectArticleItem").data("articlename");
	var ownercompany = $(".ownercompany").val();
	var selfdefined_value_type = $(".selfdefined_value_type").val();
	var selfdefined_price_per_piece = $(".price_per_piece").data("price");
	if(successfullyUpdated2 > 0){
		successfullyUpdated = successfullyUpdated2;
	}
	if(selectedAll){
		selectedAll = 1;
	} else {
		selectedAll = 0;
	}
	if(!selectedAll){
		var selectedInvoicesArray = selectedInvoices.serializeArray();
		var selectedInvoicesValueArray = new Array();
		$(selectedInvoicesArray).each(function(index, value){
			selectedInvoicesValueArray.push(value.value);
		})
		selectedInvoicesString = selectedInvoicesValueArray.join(",");
	}
	var data = {
		selectedInvoices: selectedInvoicesString,
		selectedAll: selectedAll,
		display_option: display_option,
		selfdefined_field_id: selfdefined_field_id,
		article_id: article_id,
		articlename: articlename,
		ownercompany: ownercompany,
		selfdefined_value_type: selfdefined_value_type,
		selfdefined_price_per_piece: selfdefined_price_per_piece,
		successfullyUpdated: successfullyUpdated
	}

	loadView("list", data);
}

$(document).ready(function() {

    var page = '<?php echo $page?>';
    // On customer row click
	$(document).off('click', '.output-click-helper').on('click', '.output-click-helper', function(e){
		if(e.target.nodeName == 'DIV'){
		 	fw_load_ajax($(this).data('href'),'',true);
			if($("body.alternative").length == 0) {
			 	if($(this).parents(".tinyScrollbar.col1")){
				 	var $scrollbar6 = $('.tinyScrollbar.col1');
				    $scrollbar6.tinyscrollbar();

				    var scrollbar6 = $scrollbar6.data("plugin_tinyscrollbar");
			        scrollbar6.update(0);
			    }
			}
		}
	});

	$(".selectArticleItem").off("click").on("click", function(){
		var data = {
			article_text: $(this).data("articlename"),
			article_id: $(this).data("id")
        };
        ajaxCall('edit_article', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
	})
	$(".display_option").change(function(){
		reloadPage();
	})
	$(".selfdefined_field_id").change(function(){
		reloadPage();
	})
	$(".ownercompany").change(function(){
		reloadPage();
	})
	$(".createMore").click(function(){
		reloadPage();
	})
	$(".selfdefined_value_type").change(function(){
		if($(this).val() == 1) {
			$(".selfdefined_price_per_piece").show();
		} else {
			$(".selfdefined_price_per_piece").hide();
		}
		reloadPage();
	})
	$(".selfdefined_price_per_piece").change(function(){
		reloadPage();
	})
	$('#selectDeselectAll').on('change', function(event) {
		fw_loading_start();
		var totalToProcess = $('[name="customer[]"]:visible').length;
		var processed = 0;
		var _this = $(this);
		setTimeout(function() {
			if (_this.prop('checked')) {
				$('[name="customer[]"]:visible').each(function (index,item) {
					if(!$(this).prop('checked')) $(this).trigger('click');
					processed++;
				});
			}
			else {
				$('[name="customer[]"]:visible').each(function (index,item) {
					if($(this).prop('checked')) $(this).trigger('click');
					processed++;
				});
			}
			fw_loading_end();
		}, 100);
	});
	$(".adjustPricesInput").change(function(){
		var data = {
			adjustPrice: $(this).val()
		}
		loadView("list", data);
	})
	$(".edit_price_per_piece").unbind("click").bind("click", function(e){
		e.preventDefault();
        var data = {
            pricePerPiece: '<?php echo $selfdefined_price_per_piece;?>'
        };
        ajaxCall('edit_price', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
	})
	$(".edit_adjustment_percent").unbind("click").bind("click", function(e){
 		e.preventDefault();
        var data = {
            adjustPrice: '<?php echo $adjustPricePercent;?>'
        };
        ajaxCall('edit_adjustment_percent', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
	})
	$("#out-update-orders").on("click", function(){
		if(!fw_click_instance)
		{
			fw_click_instance = true;
			var customerList = $("#out-customer-list").clone();
			customerList.find(".item-customer.hidden").remove();
			if(customerList.find("input").length > 0){
				var serializedInput = customerList.find("input").serialize();
				fw_loading_start();
				$.ajax({
					cache: false,
					type: 'POST',
					dataType: 'json',
					url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output_create_orders_selfdefined&inc_obj=ajax&inc_act=create_orders";?>',
					data: "fwajax=1&fw_nocss=1&display_option="+$(".display_option").val()+"&selfdefined_field_id="+$(".selfdefined_field_id").val()+"&ownercompany="+$(".ownercompany").val()+"&selfdefined_value_type="+$(".selfdefined_value_type").val()+"&selfdefined_price_per_piece="+$(".price_per_piece").data("price")+"&article_id="+$(".selectArticleItem").data("id")+"&article_name="+$(".selectArticleItem").data("articlename")+"&"+serializedInput,
					success: function(obj){
						if (obj.error) {
							fw_click_instance = false;
							$('#out-hook-error').html("");
							$(obj.error).each(function(index, el){
								$('#out-hook-error').html(el);
							})
							$('#out-hook-error').css({ color: 'red' });
							fw_loading_end();
						} else {
							fw_loading_end();
							reloadPage(obj.html);
						}
					}
				}).fail(function() {
					fw_info_message_add("error", "<?php echo $formText_ErrorOccuredPleaseContactSupport_framework;?>", true, true);
					fw_click_instance = false;
					fw_loading_end();
				});
			}
		}
	});
	$(".item-customer .item-title input").change(function(){
		calculateTotal();
	})
	function calculateTotal(){
		var selectedCustomers = $(".item-customer:not(.hidden) .item-title input:checked");
		var totalPrice = 0;
		$(selectedCustomers).each(function(){
			totalPrice += parseFloat($(this).parents(".item-customer").find(".totalPrice").data("price"));
		})
		$(".totalInvoicesSelected").html(totalPrice.toFixed(2).toString().replace(".", ","));
	}
	calculateTotal();
});
</script>
