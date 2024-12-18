<?php
$page = 1;
require_once __DIR__ . '/list_btn.php';

$s_sql = "SELECT * FROM customer_accountconfig ORDER BY id DESC";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
    $v_customer_accountconfig = $o_query->row_array();
}

$selectedInvoicesArray = array();
if(isset($_GET['selectedInvoices'])){
	$selectedInvoicesArray = explode(",", $_GET['selectedInvoices']);
}
$s_sql = "SELECT * FROM customer_selfdefined_fields WHERE type = 1 AND id = ? ORDER BY name ASC";
$o_query = $o_main->db->query($s_sql, array($_GET['selfdefinedFilter']));
$selfdefinedFieldSelected = $o_query ? $o_query->row_array(): array();

$selfdefinedfield_filter_join = "";
$selfdefinedfield_filter_sql = "";

if($selfdefinedFieldSelected) {
	$selfdefinedfield_filter_join = " LEFT OUTER JOIN customer_selfdefined_values csv ON csv.customer_id = c.id AND csv.selfdefined_fields_id = '".$o_main->db->escape_str($selfdefinedFieldSelected['id'])."'";
	if($_GET['selfdefinedFilterValue'] == "-1"){
		$selfdefinedfield_filter_sql = " AND (csv.selfdefined_fields_id is null)";
	} else {
		$selfdefinedfield_filter_sql = " AND (csv.value = '".$o_main->db->escape_str($_GET['selfdefinedFilterValue'])."')";
	}
}
if($_GET['departmentCodeFilter'] != ""){
	$selfdefinedfield_filter_sql .= " AND s.departmentCode = '".$o_main->db->escape_str($_GET['departmentCodeFilter'])."'";
}
$subscriptions = array();

$s_sql = "SELECT s.*, c.name as customerName
	 FROM subscriptionmulti s
	 LEFT OUTER JOIN subscriptiontype st ON st.id = s.subscriptiontype_id
	 JOIN customer c ON c.id = s.customerId
	 ".$selfdefinedfield_filter_join."
	 WHERE (s.content_status = 0 OR s.content_status is null)
	 AND (st.subscription_category = 0 OR st.subscription_category is null OR st.subscription_category = 1)
	 AND (s.stoppedDate is null OR s.stoppedDate = '0000-00-00' OR (s.stoppedDate is not null AND s.stoppedDate <> '0000-00-00' AND s.stoppedDate > NOW()))
	 ".$selfdefinedfield_filter_sql."
	 ORDER BY customerName ASC";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
	$subscriptions = $o_query->result_array();
}
$customersSelected = 0;
$customersCountWithErrors = 0;
$adjustPricePercent = isset($_GET['adjustPrice']) ? str_replace(",", ".", $_GET['adjustPrice']): 0;
ob_start();
foreach($subscriptions as $subscription) {
	$errors = array();
	$block_group_id = $subscription['id'];
	$subscriptionLines = array();

	$s_sql_select = "SELECT sl.* FROM subscriptionline sl";
	$s_sql_join = " ";
	$s_sql_where = " WHERE sl.subscribtionId = ".$subscription['id'];
	$s_sql_group = "";

	$s_sql = $s_sql_select.$s_sql_join.$s_sql_where.$s_sql_group;
	$o_query = $o_main->db->query($s_sql);
	if($o_query && $o_query->num_rows()>0){
		$subscriptionLines = $o_query->result_array();
	}

    $sql = "SELECT * FROM customer_externalsystem_id WHERE customer_id = ? AND ownercompany_id = ?";
    $o_query = $o_main->db->query($sql, array($subscription['customerId'], $subscription['ownercompany_id']));
    $customer_externalsystem_id = $o_query ? $o_query->row_array() : array();

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
				<input type="checkbox" class="customerMainSelect" value="<?php echo $block_group_id;?>" autocomplete="off" <?php if(isset($_GET['selectedAll']) && $_GET['selectedAll']){ echo 'checked';} else if(isset($_GET['selectedInvoices'])) { if(in_array($block_group_id,$selectedInvoicesArray)) echo 'checked'; } else { echo 'checked';}?>/>
				<?php endif; ?>
				<?php echo $subscription['customerName']." - ".$subscription['subscriptionName'];?>
                <?php if ($activateMultiOwnerCompanies):
                	$s_sql = "SELECT * FROM ownercompany WHERE id = ?";
					$o_query = $o_main->db->query($s_sql, array($v_customer['ownercompany_id']));
					$ownerCompanyData = $o_query ? $o_query->row_array(): array();
                ?>
                    <div>
                        <small>
                            (<?php echo $formText_OwnerCompany_output; ?>: <?php echo $ownerCompanyData['name']; ?>)
                        </small>
                    </div>
                <?php endif; ?>
			</div>
			<?php if(intval($customer_externalsystem_id['external_id']) > 0) {?>
				<span class="external_customer_wrapper">
					<?php echo $formText_ExternalCustomerId_output." ".$customer_externalsystem_id['external_id'];?>
				</span>
			<?php } ?>
			<br clear="all">
		</div>
		<div class="item-order">
			<table class="table table-condensed">
				<thead>
					<tr>
						<th>&nbsp;</th>
						<th><?php echo $formText_Text_Output;?></th>
						<th><?php echo $formText_PreviousAdjustmentDate_Output;?></th>
						<th><?php echo $formText_PreviousAdjustmentPrice_Output;?></th>
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
                        $block_group_sub_id = $block_group_id."-".$subscriptionLine['id'];
						$pricePerPiece = $subscriptionLine['pricePerPiece'];
						$newPricePerPiece = number_format($pricePerPiece * (1+$adjustPricePercent/100), 2,".", "");
						if($_GET['round_prices']){
							$newPricePerPiece = round($newPricePerPiece);
						}
						$priceTotal = $pricePerPiece*$subscriptionLine['amount']*(1-$subscriptionLine['discountPercent']/100);
						$newPriceTotal = $newPricePerPiece*$subscriptionLine['amount']*(1-$subscriptionLine['discountPercent']/100);

						?>
						<tr>
							<td>
                                <?php if(!count($errors) && !count($customer_errors) && !$allowInvoice_error): ?>
                				<input type="checkbox" class="orderlineSelect article_orderline_<?php echo $subscriptionLine['articleNumber']?>" value="<?php echo $block_group_sub_id;?>" name="customer[]" autocomplete="off" <?php if(isset($_GET['selectedAll']) && $_GET['selectedAll']){ echo 'checked';} else if(isset($_GET['selectedInvoices'])) { if(in_array($block_group_id,$selectedInvoicesArray)) echo 'checked'; } else { echo 'checked';}?>/>
                				<?php endif; ?>
                            &nbsp;</td>
							<td width="40%"><?php echo $subscriptionLine['articleName'];?></td>
							<td width="5%"><?php if($subscriptionLine['previous_adjustment_date'] != null && $subscriptionLine['previous_adjustment_date'] != "0000-00-00") echo date("d.m.Y", strtotime($subscriptionLine['previous_adjustment_date']));?></td>
							<td width="5%"><?php echo number_format($subscriptionLine['previous_adjustment_price'], 2, ",", " "); ?></td>
							<td><?php
							if($adjustPricePercent != 0){
								echo "<strike>".number_format($pricePerPiece, 2, ",", " ")."</strike> ".number_format($newPricePerPiece, 2, ",", " ");
							} else {
								echo number_format($pricePerPiece, 2, ",", " ");
							}
							?></td>
							<td><?php echo number_format($subscriptionLine['amount'], 2, ",", " ");?></td>
							<td><?php echo number_format($subscriptionLine['discountPercent'], 2, ",", " ");?></td>
							<td>&nbsp;</th>
							<td class="text-right"><?php
							if($adjustPricePercent != 0){
								echo "<strike>".number_format($priceTotal, 2, ",", " ")."</strike> ".number_format($newPriceTotal, 2, ",", " ");
							} else {
								echo number_format($priceTotal, 2, ",", " ");
							}
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
$customersCount = count($subscriptions);
$listBuffer = ob_get_clean();
?>
<div id="p_container" class="p_container <?php echo $folderName; ?>">
	<div class="p_containerInner">
		<div class="p_content">
			<div class="p_pageContent">

				<div class="p_pageDetailsTitle"><?php echo $formText_RepeatingOrders_output;?><span class="specific_article"><?php echo $formText_CheckOrUncheckSpecificArticle_output;?></span></div>
				<div class="p_contentBlock">
					<?php if($_GET['successfullyUpdated'] > 0) {
						echo $formText_SuccessfullyAdjustedPricesFor_output." ".intval($_GET['successfullyUpdated'])." ".$formText_Subscriptions_Output." ".$formText_By_output." ".$adjustPricePercent."%";
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
									(<?php echo $customersCount." ".$formText_ActiveSubscriptions_output;?>)
								<?php endif; ?>
							</div>
							<div class="adjustPriceWrapper"><?php echo $formText_AdjustPrices_output;?> <?php echo $_GET['adjustPrice'];?>% <span class="glyphicon glyphicon-pencil edit_adjustment_percent"></span></div>
							<div class="roundAdjustment">
								<input type="checkbox" value="1" class="round_prices" <?php if($_GET['round_prices']) echo 'checked';?> id="round_prices" autocomplete="off"/><label for="round_prices"><?php echo $formText_RoundPrices_output;?></label>
							</div>
							<div class="filterBySelfdefined">
								<?php echo $formText_SelfdefinedField_output;?>
								<?php

			                	$s_sql = "SELECT * FROM customer_selfdefined_fields WHERE type = 1 ORDER BY name ASC";
								$o_query = $o_main->db->query($s_sql);
								$selfdefinedFields = $o_query ? $o_query->result_array(): array();
								?>
								<select class="selfdefinedfield_select" autocomplete="off">
									<option value=""><?php echo $formText_Select_output;?></option>
									<?php foreach($selfdefinedFields as $selfdefinedField) { ?>
										<option value="<?php echo $selfdefinedField['id'];?>" <?php if($_GET['selfdefinedFilter'] == $selfdefinedField['id']) echo 'selected';?>><?php echo $selfdefinedField['name']?></option>
									<?php } ?>
								</select>
								&nbsp;&nbsp;&nbsp;
								<?php echo $formText_Value_output;?>
								<?php

			                	$s_sql = "SELECT * FROM customer_selfdefined_list_lines  WHERE list_id = ? ORDER BY name ASC";
								$o_query = $o_main->db->query($s_sql, array($selfdefinedFieldSelected['list_id']));
								$selfdefinedFieldValues = $o_query ? $o_query->result_array(): array();
								?>
								<select class="selfdefinedfieldvalue_select" autocomplete="off">
									<option value=""><?php echo $formText_Select_output;?></option>
									<?php foreach($selfdefinedFieldValues as $selfdefinedFieldValue) { ?>
										<option value="<?php echo $selfdefinedFieldValue['id'];?>" <?php if($_GET['selfdefinedFilterValue'] == $selfdefinedFieldValue['id']) echo 'selected';?>><?php echo $selfdefinedFieldValue['name']?></option>
									<?php } ?>
									<option value="-1" <?php if($_GET['selfdefinedFilterValue'] == -1) echo 'selected';?>><?php echo $formText_NoValue_output;?></option>
								</select>
							</div>
							<?php if($v_customer_accountconfig['activate_priceadjustment_department_code_filter']) {
								$s_sql = "SELECT * FROM departmentforaccounting
								WHERE departmentforaccounting.content_status < 2 ORDER BY departmentforaccounting.departmentnumber ASC";
								$o_query = $o_main->db->query($s_sql);
								$departmentCodes = $o_query ? $o_query->result_array(): array();
								?>
								<div class="filterByDepartment">
									<?php echo $formText_DepartmentCode_output;?>
									<select class="department_select" autocomplete="off">
										<option value=""><?php echo $formText_All_output;?></option>
										<?php foreach($departmentCodes as $departmentCode) { ?>
											<option value="<?php echo $departmentCode['departmentnumber'];?>" <?php if($_GET['departmentCodeFilter'] == $departmentCode['departmentnumber']) echo 'selected';?>><?php echo $departmentCode['departmentnumber']. " ".$departmentCode['name'];?></option>
										<?php } ?>
									</select>
								</div>
							<?php } ?>
							<div class="clear"></div>

							<div class="out-dynamic">
								<?php echo $listBuffer; ?>
							</div>
							<div id="out-hook-error"></div>
							<div class="out-buttons">
								<button id="out-update-orders" class="btn btn-default"><?php echo $formText_UpdateOrderlines_Output;?></button>
						        <span class="totalSelectedInvoices"><?php echo $formText_SubscriptionsSelected_output;?>: <span class="totalInvoicesSelected"></span></span>
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

<?php $list_filter = $_GET['list_filter'] ? $_GET['list_filter'] : 'all'; ?>
<style>
.roundAdjustment {
	margin-left: 15px;
	float: left;
	margin-top: 5px;
}
.roundAdjustment input {
	vertical-align: middle;
	display: inline-block;
	margin-right: 10px;
	margin-top: 0;
}
.roundAdjustment label {
	vertical-align: middle;
	display: inline-block;
	margin-right: 10px;
}
.filterBySelfdefined {
	float: left;
	margin-top: 5px;
	margin-left: 15px;
}
.filterByDepartment {
	float: left;
	margin-left: 10px;
	margin-top: 5px;
}
.selfdefinedfield_select {
	width: 100px;
}
.selfdefinedfieldvalue_select {
	width: 60px;
}
.external_customer_wrapper {
	float: right;
}
.specific_article {
    cursor: pointer;
    color: #46b2e2;
    margin-left: 10px;
    font-weight: normal;
    font-size: 12px;
}
</style>
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
    		 	fw_load_ajax(redirectUrl,'',true);
			} else {
				reloadPage(percent);
            }
          // window.location.reload();
        }
		$(this).removeClass('opened');
	}
};
function reloadPage(percent){
	var selectedAll = $("#selectDeselectAll").is(":checked");
	var selectedInvoices = $(".item-title input:checked");
	var selectedInvoicesString = "";
	var adjustPercent = '<?php echo $adjustPricePercent;?>';
	if(percent != undefined){
		adjustPercent = percent;
	}
	var round_prices = 0;
	if($(".round_prices").is(":checked")){
		round_prices = 1;
	}
	var selfdefinedFilter = $(".selfdefinedfield_select").val();
	var selfdefinedFilterValue = $(".selfdefinedfieldvalue_select").val();
	var departmentCodeFilter = $(".department_select").val();
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
		adjustPrice: adjustPercent,
		round_prices: round_prices,
		selfdefinedFilter: selfdefinedFilter,
		selfdefinedFilterValue: selfdefinedFilterValue,
		departmentCodeFilter: departmentCodeFilter
	}

	loadView("list", data);
}

$(document).ready(function() {
    $(".specific_article").off("click").on("click", function(e){
        e.preventDefault();
        var data = {
        };
        ajaxCall('edit_specific_article', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    })
    $(".customerMainSelect").off("click").on("click", function(){
        var parent = $(this).parents(".item-customer");
        if($(this).is(":checked")){
            parent.find(".orderlineSelect").prop("checked", true);
        } else {
            parent.find(".orderlineSelect").prop("checked", false);
        }
    })
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
	$(".round_prices").change(function(){
		reloadPage();
	})
	$(".selfdefinedfield_select").change(function(){
		reloadPage();
	})
	$(".selfdefinedfieldvalue_select").change(function(){
		reloadPage();
	})
	$(".department_select").change(function(){
		reloadPage();
	})

	$('#selectDeselectAll').on('change', function(event) {
		fw_loading_start();
		var totalToProcess = $('.customerMainSelect:visible').length;
		var processed = 0;
		var _this = $(this);
		setTimeout(function() {
			if (_this.prop('checked')) {
				$('.out-dynamic input[type="checkbox"]:visible').each(function (index,item) {
					if(!$(this).prop('checked')) $(this).trigger('click');
					processed++;
				});
			}
			else {
				$('.out-dynamic input[type="checkbox"]:visible').each(function (index,item) {
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
			var serializedInput = customerList.find("input").serialize();
			var round_prices = 0;
			if($(".round_prices").is(":checked")){
				round_prices = 1;
			}
			fw_loading_start();
			$.ajax({
				cache: false,
				type: 'POST',
				dataType: 'json',
				url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output_adjustprices&inc_obj=ajax&inc_act=adjust_prices";?>',
				data: "fwajax=1&fw_nocss=1&adjustPrice=<?php echo $adjustPricePercent;?>&round_prices="+round_prices+"&" + serializedInput,
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
						var data = {
							successfullyUpdated: obj.html,
							adjustPrice: '<?php echo $adjustPricePercent;?>'
						}
						loadView("list", data);
					}
				}
			}).fail(function() {
				fw_info_message_add("error", "<?php echo $formText_ErrorOccuredPleaseContactSupport_framework;?>", true, true);
				fw_click_instance = false;
				fw_loading_end();
			});
		}
	});
	$(".item-customer .item-title input").change(function(){
		calculateTotal();
	})
	function calculateTotal(){
		var selectedCustomerCount = $(".item-customer:not(.hidden) .item-title input:checked").length;
		$(".totalInvoicesSelected").html(selectedCustomerCount);
	}
	calculateTotal();
});
</script>
