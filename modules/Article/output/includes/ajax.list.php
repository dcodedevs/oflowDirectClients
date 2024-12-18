<?php

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

if(isset($_POST['priceMatrix'])){ if($_POST['priceMatrix']){ $_GET['priceMatrix'] = $_POST['priceMatrix']; } }
if(isset($_POST['discountMatrix'])){ if($_POST['discountMatrix']) $_GET['discountMatrix'] = $_POST['discountMatrix']; }

if(isset($_GET['search_filter'])){ if($_GET['search_filter']) $_POST['search_filter'] = $_GET['search_filter']; }
if(isset($_GET['customergroup_filter'])){ if($_GET['customergroup_filter']) $_POST['customergroup_filter'] = $_GET['customergroup_filter']; }
if(isset($_GET['building_filter'])){ if($_GET['building_filter']) $_POST['building_filter'] = $_GET['building_filter']; }

if(isset($_POST['list_filter'])){ if($_POST['list_filter']) $_GET['list_filter'] = $_POST['list_filter']; }
if(isset($_GET['list_filter'])){ $list_filter = $_GET['list_filter']; } else { $list_filter = 'active'; }
//$list_filter = $_GET['list_filter'] ? ($_GET['list_filter']) : 'active';

if(isset($_POST['building_filter'])){ $building_filter = $_POST['building_filter']; } else { $building_filter = 0; }
//$building_filter = $_POST['building_filter'] ? ($_POST['building_filter']) : 0;
if(isset($_POST['customergroup_filter'])){ $customergroup_filter = $_POST['customergroup_filter']; } else { $customergroup_filter = 0; }
//$customergroup_filter = $_POST['customergroup_filter'] ? ($_POST['customergroup_filter']) : 0;
if(isset($_POST['search_filter'])){ $search_filter = $_POST['search_filter']; } else { $search_filter = ''; }
//$search_filter = $_POST['search_filter'] ? ($_POST['search_filter']) : '';
if(isset($_GET['order_direction'])){ $order_direction = $_GET['order_direction']; } else { $order_direction = '';}
if(isset($_GET['order_field'])){ $order_field = $_GET['order_field']; } else { $order_field = '';}

require_once __DIR__ . '/functions.php';
if($all_count == null){
	// $with_subscriptions_count = get_customer_list_count('with_subscriptions',$building_filter, $search_filter, $customergroup_filter);
	// $with_expired_subscriptions_count = get_customer_list_count('with_expired_subscriptions',$building_filter, $search_filter, $customergroup_filter);
	// $with_orders_count = get_customer_list_count('with_orders',$building_filter, $search_filter, $customergroup_filter);

	$all_count = get_support_list_count('active', $company_product_set_id, $search_filter);
}

if(isset($_POST['page'])) {
	$page = $_POST['page'];
}
if(intval($page) == 0){
	$page = 1;
}
if(isset($_POST['rowOnly'])){ $rowOnly = $_POST['rowOnly']; } else { $rowOnly = ''; }
$perPage = 500;
$showing = $page * $perPage;
$showMore = false;
$currentCount = $all_count;
if($list_filter == 'deleted'){
	$deleted_count = get_support_list_count('deleted', $company_product_set_id, $search_filter);
	$currentCount = $deleted_count;
}
if($showing < $currentCount){
	$showMore = true;
}
$totalPages = ceil($currentCount/$perPage);

if($list_filter == "supplier"){
	$s_sql = "SELECT * FROM article_supplier WHERE content_status < 2 ORDER BY id ASC";
	$o_query = $o_main->db->query($s_sql);
	$customerList = $o_query ? $o_query->result_array() : array();
	?>
	<?php if (!$rowOnly) { ?>
		<div class="gtable" id="gtable_search">
		    <div class="gtable_row">
	        	<div class="gtable_cell gtable_cell_head" data-orderfield="article_code" data-orderdirection="<?php if($order_direction == 0) { echo 1; } else { echo 0; } ?>">
					<?php echo $formText_Name_output;?>
				</div>
			</div>
	<?php } ?>
	<?php
	foreach($customerList as $v_row)
	{
		$article_has_error = 0;
		$s_edit_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details_supplier&cid=".$v_row['id'];
		if(isset($article_errors)){ if(isset($article_errors[$v_row['id']]['errors'])) $article_has_error = count($article_errors[$v_row['id']]['errors']); } else { $article_has_error = array(); }
		//$article_has_error = '';
		?>
	 	<div class="gtable_row">
        	<div class="gtable_cell" >
				<a class="optimize" href="<?php echo $s_edit_link;?>"><?php echo $v_row['name']?></a>

			</div>
		</div>
	<?php
	}
	if(!$rowOnly) {
		?>
		</div>
		<?php
	}
} else {
	$customerList = get_support_list($list_filter, $company_product_set_id, $search_filter, $page, $perPage, $order_field, $order_direction);


	// HOOK: activate_check_all_articles_in_external_sys_on_module_load
	if ($article_accountconfig['activate_check_all_articles_in_external_sys_on_module_load']) {
		$hook_params = array();

		$sql = "SELECT * FROM ownercompany WHERE company_product_set_id = ?";
        $o_query = $o_main->db->query($sql, array($company_product_set_id));
        $ownercompany = $o_query && $o_query->num_rows() ? $o_query->row_array() : array();
		$hook_params['ownercompany_id'] = $ownercompany['id'];
		$hook_file = __DIR__ . '/../../../../' . $article_accountconfig['path_check_all_articles_in_external_sys_on_module_load'];
		if (file_exists($hook_file)) {
			require_once $hook_file;
			if (is_callable($run_hook)) {
				$hook_result = $run_hook($hook_params);
				unset($run_hook);
			}
		}

		// Error message
		if ($hook_result['error']) {
			$article_errors = $hook_result['data'];
		?>
			<div class="alert alert-danger" style="margin-top: 25px"><?php echo $hook_result['message']; ?></div>
		<?php } else { $article_errors = array(); }
	}
	?>
	<?php if (!$rowOnly) { ?>

	<div class="gtable" id="gtable_search">
	    <div class="gtable_row">
	        <?php if($article_accountconfig['activateArticleCode']) { ?>
	        	<div class="gtable_cell gtable_cell_head c3 orderBy" data-orderfield="article_code" data-orderdirection="<?php if($order_direction == 0) { echo 1; } else { echo 0; } ?>">
					<?php echo $formText_ArticleCode_output;?>
					<div class="ordering">
	                    <div class="fas fa-caret-up" <?php if($order_field == "article_code" && $order_direction == 0) { echo 'style="display: none;"';}?>></div>
	                    <div class="fas fa-caret-down" <?php if($order_field == "article_code" && $order_direction == 1) { echo 'style="display: none;"';}?>></div>
	                </div>
				</div>
	        <?php } ?>
	        <div class="gtable_cell gtable_cell_head c3 orderBy" data-orderfield="name" data-orderdirection="<?php if($order_direction == 0) { echo 1; } else { echo 0; } ?>">
				<?php echo $formText_Name_output;?>
				<div class="ordering">
					<div class="fas fa-caret-up" <?php if($order_field == "name" && $order_direction == 0) { echo 'style="display: none;"';}?>></div>
					<div class="fas fa-caret-down" <?php if($order_field == "name" && $order_direction == 1) { echo 'style="display: none;"';}?>></div>
				</div>
			</div>
			<?php if($article_accountconfig['activate_article_group']) { ?>
				<div class="gtable_cell gtable_cell_head c3 orderBy" data-orderfield="group" data-orderdirection="<?php if($order_direction == 0) { echo 1; } else { echo 0; } ?>">
					<?php echo $formText_Group_Output;?>
					<div class="ordering">
						<div class="fas fa-caret-up" <?php if($order_field == "group" && $order_direction == 0) { echo 'style="display: none;"';}?>></div>
						<div class="fas fa-caret-down" <?php if($order_field == "group" && $order_direction == 1) { echo 'style="display: none;"';}?>></div>
					</div>
				</div>
			<?php } ?>
	        <?php if($article_accountconfig['activate_comment_field']) { ?>
	        	<div class="gtable_cell gtable_cell_head c3 orderBy" data-orderfield="comment" data-orderdirection="<?php if($order_direction == 0) { echo 1; } else { echo 0; } ?>">
					<?php echo $formText_Comment_Output;?>
					<div class="ordering">
	                    <div class="fas fa-caret-up" <?php if($order_field == "comment" && $order_direction == 0) { echo 'style="display: none;"';}?>></div>
	                    <div class="fas fa-caret-down" <?php if($order_field == "comment" && $order_direction == 1) { echo 'style="display: none;"';}?>></div>
	                </div>
				</div>
			<?php } ?>

	        <?php if($activateSystemArticleTypes) { ?>
	        	<div class="gtable_cell gtable_cell_head c3 orderBy" data-orderfield="system_article_type" data-orderdirection="<?php if($order_direction == 0) { echo 1; } else { echo 0; } ?>">
					<?php echo $formText_SystemArticleType_Output;?>
					<div class="ordering">
	                    <div class="fas fa-caret-up" <?php if($order_field == "system_article_type" && $order_direction == 0) { echo 'style="display: none;"';}?>></div>
	                    <div class="fas fa-caret-down" <?php if($order_field == "system_article_type" && $order_direction == 1) { echo 'style="display: none;"';}?>></div>
	                </div>
				</div>
			<?php } ?>
	        <div class="gtable_cell gtable_cell_head c2 orderBy" data-orderfield="cost_price" data-orderdirection="<?php if($order_direction == 0) { echo 1; } else { echo 0; } ?>">
				<?php echo $formText_CostPrice_output;?>
				<div class="ordering">
					<div class="fas fa-caret-up" <?php if($order_field == "cost_price" && $order_direction == 0) { echo 'style="display: none;"';}?>></div>
					<div class="fas fa-caret-down" <?php if($order_field == "cost_price" && $order_direction == 1) { echo 'style="display: none;"';}?>></div>
				</div>
			</div>
	        <div class="gtable_cell gtable_cell_head c2 orderBy" data-orderfield="price" data-orderdirection="<?php if($order_direction == 0) { echo 1; } else { echo 0; } ?>">
				<?php echo $formText_Price_output;?>
				<div class="ordering">
					<div class="fas fa-caret-up" <?php if($order_field == "price" && $order_direction == 0) { echo 'style="display: none;"';}?>></div>
					<div class="fas fa-caret-down" <?php if($order_field == "price" && $order_direction == 1) { echo 'style="display: none;"';}?>></div>
				</div>
			</div>
	        <?php if(isset($_GET['priceMatrix'])){ if($article_accountconfig['activateArticlePriceMatrix'] && $_GET['priceMatrix'] > 0) { ?>
	        	<div class="gtable_cell gtable_cell_head cPriceColumn " data-orderfield="price_matrix" data-orderdirection="<?php if($order_direction == 0) { echo 1; } else { echo 0; } ?>">
					<?php echo $formText_PriceMatrix_Output;?>
				</div>
	        <?php } } ?>
	        <?php if(isset($_GET['discountMatrix'])){ if($article_accountconfig['activateArticleDiscountMatrix'] && $_GET['discountMatrix']  > 0) { ?>
	        	<div class="gtable_cell gtable_cell_head cDiscountColumn " data-orderfield="discount_matrix" data-orderdirection="<?php if($order_direction == 0) { echo 1; } else { echo 0; } ?>">
					<?php echo $formText_DiscountMatrix_Output;?>
				</div>
	        <?php } } ?>
	        <div class="gtable_cell gtable_cell_head c2 orderBy" data-orderfield="sales_account" data-orderdirection="<?php if($order_direction == 0) { echo 1; } else { echo 0; } ?>">
				<?php echo $formText_SalesAccountWithVat_output;?>
				<div class="ordering">
					<div class="fas fa-caret-up" <?php if($order_field == "sales_account" && $order_direction == 0) { echo 'style="display: none;"';}?>></div>
					<div class="fas fa-caret-down" <?php if($order_field == "sales_account" && $order_direction == 1) { echo 'style="display: none;"';}?>></div>
				</div>
			</div>
	        <div class="gtable_cell gtable_cell_head c2 orderBy" data-orderfield="vat_code" data-orderdirection="<?php if($order_direction == 0) { echo 1; } else { echo 0; } ?>">
				<?php echo $formText_VatCodeWithVat_Output;?>
				<div class="ordering">
					<div class="fas fa-caret-up" <?php if($order_field == "vat_code" && $order_direction == 0) { echo 'style="display: none;"';}?>></div>
					<div class="fas fa-caret-down" <?php if($order_field == "vat_code" && $order_direction == 1) { echo 'style="display: none;"';}?>></div>
				</div>
			</div>

	        <?php if($article_accountconfig['show_external_sys_id']) { ?>
	        	<div class="gtable_cell gtable_cell_head c3 orderBy" data-orderfield="external_id" data-orderdirection="<?php if($order_direction == 0) { echo 1; } else { echo 0; } ?>">
					<?php echo $formText_ExternalId_Output;?>
					<div class="ordering">
	                    <div class="fas fa-caret-up" <?php if($order_field == "external_id" && $order_direction == 0) { echo 'style="display: none;"';}?>></div>
	                    <div class="fas fa-caret-down" <?php if($order_field == "external_id" && $order_direction == 1) { echo 'style="display: none;"';}?>></div>
	                </div>
				</div>
			<?php } ?>
	        <div class="gtable_cell gtable_cell_head cEdit">&nbsp;</div>
	    </div>
	<?php } ?>
	<?php
	foreach($customerList as $v_row)
	{
		$article_has_error = 0;
		$s_edit_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$v_row['id'];
		if(isset($article_errors)){ if(isset($article_errors[$v_row['id']]['errors'])) $article_has_error = count($article_errors[$v_row['id']]['errors']); } else { $article_has_error = array(); }
		//$article_has_error = '';
		?>
	 	<div class="gtable_row <?php echo $article_has_error ? 'gtable_row_error' : '' ?>">
	        <?php if($article_accountconfig['activateArticleCode']) { ?>
	        	<div class="gtable_cell c3"><?php echo $v_row['articleCode']?></div>
	        <?php } ?>
	        <div class="gtable_cell c3">
				<?php echo $v_row['name'];?>
				<?php if ($article_has_error): ?>
					<?php foreach($article_errors[$v_row['id']]['errors'] as $article_error): ?>
						<div style="color:#a94442"><?php echo $article_error['message']; ?></div>
					<?php endforeach; ?>
					<?php
					if($article_errors[$v_row['id']]['suggested_external_products']){
						echo '<div class="syncSuggestionsTitle">'.$formText_Suggestions_output.':</div>';
						foreach($article_errors[$v_row['id']]['suggested_external_products'] as $suggested_external_product): ?>
							<div class="syncWithArticle" data-price="<?php echo $suggested_external_product['price'];?>" data-cost="<?php echo $suggested_external_product['cost'];?>" data-sales="<?php echo $suggested_external_product['SalesAccountWithVat'];?>" data-vat="<?php echo $suggested_external_product['VatCodeWithVat'];?>" data-article-id="<?php echo $v_row['id'];?>" data-external-id="<?php echo $suggested_external_product['id'];?>"><?php echo $formText_SyncWith_output." ".$suggested_external_product['name']; ?></div>
						<?php endforeach;
					} ?>
				<?php endif;?>
			</div>
			<?php if($article_accountconfig['activate_article_group']) { ?>
				<div class="gtable_cell c3">
					<?php echo $v_row['groupName'];?>
				</div>
			<?php } ?>
			<?php if($article_accountconfig['activate_comment_field']) { ?>
				<div class="gtable_cell c3">
					<?php echo $v_row['comment'];?>
				</div>
			<?php } ?>
			<?php if($activateSystemArticleTypes) { ?>
				<div class="gtable_cell c2"><?php
					switch(intval($v_row['system_article_type'])){
						case 1:
							echo $formText_PrepaidCommonCost_output;
						break;
						case 2:
							echo $formText_MarketingContribution_output;
						break;
						case 3:
							echo $formText_ParkingRent_output;
						break;
						case 4:
							echo $formText_ItemSales_output;
						break;
					}
				?></div>
			<?php } ?>
	        <div class="gtable_cell c2"><?php echo number_format($v_row['costPrice'], 2, ",", "");?></div>
	        <div class="gtable_cell c2"><?php echo number_format($v_row['price'], 2, ",", "");?></div>
	        <?php if(isset($_GET['priceMatrix'])){ if($article_accountconfig['activateArticlePriceMatrix'] && $_GET['priceMatrix'] > 0) {
	        	$priceMatrixId = $_GET['priceMatrix'];
				$s_sql = "SELECT * FROM articlepricematrixlines WHERE articleId = ? AND articlePriceMatrixId = ?";
				$o_query = $o_main->db->query($s_sql, array($v_row['id'], $priceMatrixId));
				$priceMatrixLine = $o_query ? $o_query->row_array() : array();
	        ?>
	        	<div class="gtable_cell cPriceColumn"><?php echo number_format($priceMatrixLine['price'], 2, ",", "");?><span class="edit-price-matrix editBtnIcon" data-article-id="<?php echo $v_row['id']?>" data-pricematrix-id="<?php echo $priceMatrixId;?>" data-pricematrixline-id="<?php echo $priceMatrixLine['id'];?>"><span class="glyphicon glyphicon-pencil"></span></span></div>
	        <?php } } ?>
	        <?php if(isset($_GET['discountMatrix'])){ if($article_accountconfig['activateArticleDiscountMatrix'] && $_GET['discountMatrix']  > 0) {
				$s_sql = "SELECT * FROM articlediscountmatrixlines WHERE articleId = ? AND articleDiscountMatrixId = ?";
				$o_query = $o_main->db->query($s_sql, array($v_row['id'], $_GET['discountMatrix']));
				$discountMatrixLine = $o_query ? $o_query->row_array() : array();

	        ?>
	        	<div class="gtable_cell cDiscountColumn"><?php echo number_format($discountMatrixLine['discountPercent'], 2, ",", "");?><span class="edit-discount-matrix editBtnIcon" data-article-id="<?php echo $v_row['id']?>" data-discountmatrix-id="<?php echo $_GET['discountMatrix'];?>" data-discountmatrixline-id="<?php echo $discountMatrixLine['id'];?>"><span class="glyphicon glyphicon-pencil"></span></span></div>
	        <?php } } ?>
	        <div class="gtable_cell c2">
				<?php echo $v_row['SalesAccountWithVat'];?>
				<?php
				$vatCount = 0;
				$s_sql = "SELECT * FROM bookaccount WHERE accountNr = ?";
				$o_query = $o_main->db->query($s_sql, array($v_row['SalesAccountWithVat']));
				if($o_query && $o_query->num_rows()>0){
					$vatCount = $o_query->num_rows();
				}
				if(!$vatCount){
					?>
					<span class="alertinfo fas fa-exclamation-triangle"><span class="hover"><?php echo $formText_NoSalesAccountWithVatFound_output;?></span></span>
					<?php
				}
				?>
			</div>
	        <div class="gtable_cell c2">
				<?php echo $v_row['VatCodeWithVat'];?>
				<?php
				$vatCount = 0;
				$s_sql = "SELECT * FROM vatcode WHERE vatCode = ?";
				$o_query = $o_main->db->query($s_sql, array($v_row['VatCodeWithVat']));
				if($o_query && $o_query->num_rows()>0){
					$vatCount = $o_query->num_rows();
				}
				if(!$vatCount){
					?>
					<span class="alertinfo fas fa-exclamation-triangle"><span class="hover"><?php echo $formText_NoVatCodeFound_output;?></span></span>
					<?php
				}
				?>
			</div>
			<?php if($article_accountconfig['show_external_sys_id']) { ?>
	        	<div class="gtable_cell">
					<?php echo $v_row['external_sys_id'];?>
				</div>
			<?php } ?>
	        <div class="gtable_cell cEdit">
				<?php
					if((!$article_accountconfig['deactivate_product_editing_in_customermode'] && $variables->developeraccess == 0) || $variables->developeraccess > 0) { ?>
					<span class="edit-article editBtnIcon" data-article-id="<?php echo $v_row['id']?>"><span class="glyphicon glyphicon-pencil"></span></span><span class="delete-article editBtnIcon" data-article-id="<?php echo $v_row['id']?>"><span class="glyphicon glyphicon-trash"></span></span>
				<?php } ?>
			</div>
	    </div>
		<?php

	} ?>
	<?php if (!$rowOnly) { ?>
	</div>
	<?php if($totalPages > 1) {
		$currentPage = $page;
		$pages = array();
		array_push($pages, 1);
		if(!in_array($currentPage, $pages)){
			array_push($pages, $currentPage);
		}
		if(!in_array($totalPages, $pages)){
			array_push($pages, $totalPages);
		}
		for ($y = 10; $y <= $totalPages; $y+=10){
			if(!in_array($y, $pages)){
				array_push($pages, $y);
			}
		}
		for($x = 1; $x <= 3;$x++){
			$prevPage = $page - $x;
			$nextPage = $page + $x;
			if($prevPage > 0){
				if(!in_array($prevPage, $pages)){
					array_push($pages, $prevPage);
				}
			}
			if($nextPage <= $totalPages){
				if(!in_array($nextPage, $pages)){
					array_push($pages, $nextPage);
				}
			}
		}
		asort($pages);
		?>
		<?php foreach($pages as $page) {?>
			<a href="#" data-page="<?php echo $page?>" class="page-link"><?php echo $page;?></a>
		<?php } ?>
		<?php /*
	    <div class="showMoreCustomers"><?php echo $formText_Showing_Output;?> <span class="showing"><?php echo $showing;?></span> <?php echo $formText_Of_output." ".$currentCount;?> <a href="#" class="showMoreCustomersBtn"><?php echo $formText_ShowMore_output;?></a> </div>*/?>
	<?php } ?>
	<script type="text/javascript">
	var out_popup;
	var out_popup_options={
		follow: [true, true],
		fadeSpeed: 0,
		followSpeed: 200,
		modalClose: false,
		escClose: false,
		closeClass:'b-close',
		onOpen: function(){
			$(this).addClass('opened');
			//$(this).find('.b-close').on('click', function(){out_popup.close();});
		},
		onClose: function(){
			$(this).removeClass('opened');
		}
	};
	$(function() {
		$(document).off('click', '.output-click-helper').on('click', '.output-click-helper', function(e){
			if(e.target.nodeName == 'DIV') fw_load_ajax($(this).data('href'),'',true);
		});
		$(document).off('mouseenter mouseleave', '.output-access-changer')
		.on('mouseenter', '.output-access-changer', function(){
			$(this).find(".output-access-dropdown").show();
		}).on('mouseleave', '.output-access-changer', function(){
			$(this).find(".output-access-dropdown").hide();
		});
		$(".editInvitationButton").on('click', function(e){
			$.ajax({
				cache: false,
				type: 'POST',
				dataType: 'json',
				url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=edit_invitation";?>',
				data: { fwajax: 1, fw_nocss: 1, cid: $(this).data('id') },
				success: function(obj){
					$('#popupeditboxcontent').html('');
					$('#popupeditboxcontent').html(obj.html);
					out_popup = $('#popupeditbox').bPopup(out_popup_options);
					$("#popupeditbox:not(.opened)").remove();
				}
			});
		});

		$(".orderBy").off("click").on("click", function(){
	        var order_field = $(this).data("orderfield");
	        var order_direction = $(this).data("orderdirection");

	        var data = {
	            list_filter: '<?php echo $list_filter?>',
	            category_filter: $(".filterCategory").val(),
		        building_filter:$(".buildingFilter").val(),
		        customergroup_filter: $(".customerGroupFilter").val(),
		        list_filter: '<?php echo $list_filter; ?>',
		        search_filter: $('.searchFilter').val(),
				priceMatrix: '<?php if(isset($_GET['priceMatrix'])){ echo $_GET['priceMatrix']; } ?>',
				discountMatrix: '<?php if(isset($_GET['discountMatrix'])){ echo $_GET['discountMatrix']; } ?>',
	            order_field: order_field,
	            order_direction: order_direction,
				set_id: '<?php echo $company_product_set_id;?>'
	        }
	        loadView("list", data);
	    })
	});

	    $(".page-link").on('click', function(e) {
		    page = $(this).data("page");
		    e.preventDefault();
		    var data = {
		        building_filter:$(".buildingFilter").val(),
		        customergroup_filter: $(".customerGroupFilter").val(),
		        list_filter: '<?php echo $list_filter; ?>',
		        search_filter: $('.searchFilter').val(),
				priceMatrix: '<?php if(isset($_GET['priceMatrix'])){ echo $_GET['priceMatrix']; } ?>',
				discountMatrix: '<?php if(isset($_GET['discountMatrix'])){ echo $_GET['discountMatrix']; } ?>',
		        page: page
		    };
		    ajaxCall('list', data, function(json) {
		        $('.p_pageContent').html(json.html);
		        if(json.html.replace(" ", "") == ""){
		            $(".showMoreCustomersBtn").hide();
		        }

		    });
	    });
	    $('.edit-article').on('click', function(e) {
	        e.preventDefault();
		    var data = {
		        articleId: $(this).data("article-id")
		    };
		    ajaxCall('editArticle', data, function(json) {
		        $('#popupeditboxcontent').html('');
		        $('#popupeditboxcontent').html(json.html);
		        out_popup = $('#popupeditbox').bPopup(out_popup_options);
		        $("#popupeditbox:not(.opened)").remove();
		    });
	    });
	    $('.delete-article').on('click', function(e) {
	    	e.preventDefault();
	        if(!fw_click_instance)
	        {
	            fw_click_instance = true;
	            var $_this = $(this);
	            bootbox.confirm({
	                message:"<?php echo $formText_ConfirmDelete_output;?>",
	                buttons:{confirm:{label:"<?php echo $formText_Yes_Output;?>"},cancel:{label:"<?php echo $formText_No_Output;?>"}},
	                callback: function(result){
	                    if(result)
	                    {
	                        fw_loading_start();
	                        var data = {
						        articleId: $_this.data("article-id"),
						        action: "deleteArticle"
						    };
						    ajaxCall('editArticle', data, function(json) {
						    	if(json.error !== undefined)
	                                {
	                                fw_info_message_empty();
	                                $.each(json.error, function(index, value){
	                                    var _type = Array("error");
	                                    if(index.length > 0 && index.indexOf("_") > 0) _type = index.split("_");
	                                    fw_info_message_add(_type[0], value);
	                                });
	                                fw_info_message_show();
	                                fw_loading_end();
	                            } else {
									var order_field = '<?php echo $order_field;?>';
							        var order_direction = '<?php echo $order_direction;?>';

							        var data = {
							            list_filter: '<?php echo $list_filter?>',
							            category_filter: $(".filterCategory").val(),
								        building_filter:$(".buildingFilter").val(),
								        customergroup_filter: $(".customerGroupFilter").val(),
								        list_filter: '<?php echo $list_filter; ?>',
								        search_filter: $('.searchFilter').val(),
										priceMatrix: '<?php if(isset($_GET['priceMatrix'])){ echo $_GET['priceMatrix']; } ?>',
										discountMatrix: '<?php if(isset($_GET['discountMatrix'])){ echo $_GET['discountMatrix']; } ?>',
							            order_field: order_field,
							            order_direction: order_direction,
										set_id: '<?php echo $company_product_set_id;?>'
							        }
							        loadView("list", data);
	                            }
						    });
	                    }
	                    fw_click_instance = false;
	                }
	            });
	        }
	    });

	    $('.edit-price-matrix').on('click', function(e) {
	        e.preventDefault();
		    var data = {
		        articleId: $(this).data("article-id"),
		        priceMatrixId: $(this).data("pricematrix-id"),
		        priceMatrixLineId: $(this).data("pricematrixline-id"),
		       	discountMatrixId: '<?php if(isset($_GET['discountMatrix'])){ echo $_GET['discountMatrix']; } ?>',
		       	list_filter: '<?php echo $list_filter;?>',
		       	search_filter: '<?php echo $search_filter;?>'
		    };
		    ajaxCall('edit_price', data, function(json) {
		        $('#popupeditboxcontent').html('');
		        $('#popupeditboxcontent').html(json.html);
		        out_popup = $('#popupeditbox').bPopup(out_popup_options);
		        $("#popupeditbox:not(.opened)").remove();
		    });
	    });
	    $('.edit-discount-matrix').on('click', function(e) {
	        e.preventDefault();
		    var data = {
		        articleId: $(this).data("article-id"),
		       	discountMatrixId: $(this).data("discountmatrix-id"),
		        discountMatrixLineId: $(this).data("discountmatrixline-id"),
		       	priceMatrixId: '<?php if(isset($_GET['priceMatrix'])){ echo $_GET['priceMatrix']; } ?>',
		       	list_filter: '<?php echo $list_filter;?>',
		       	search_filter: '<?php echo $search_filter;?>'
		    };
		    ajaxCall('edit_discount', data, function(json) {
		        $('#popupeditboxcontent').html('');
		        $('#popupeditboxcontent').html(json.html);
		        out_popup = $('#popupeditbox').bPopup(out_popup_options);
		        $("#popupeditbox:not(.opened)").remove();
		    });
	    });
		$(".syncWithArticle").off("click").on("click", function(e){
			 e.preventDefault();
			 if(!fw_click_instance)
  	        {
  	            fw_click_instance = true;
  	            var $_this = $(this);
  	            bootbox.confirm({
  	                message:"<?php echo $formText_ConfirmSync_output;?>",
  	                buttons:{confirm:{label:"<?php echo $formText_Yes_Output;?>"},cancel:{label:"<?php echo $formText_No_Output;?>"}},
  	                callback: function(result){
  	                    if(result)
  	                    {
  	                        fw_loading_start();
  	                        var data = {
								articleId: $_this.data("article-id"),
  						        externalId: $_this.data("external-id"),
  						        sales: $_this.data("sales"),
  						        vat: $_this.data("vat"),
  						        price: $_this.data("price"),
  						        action: "syncArticle"
  						    };
  						    ajaxCall('editArticle', data, function(json) {
  						    	if(json.error !== undefined)
  	                                {
  	                                fw_info_message_empty();
  	                                $.each(json.error, function(index, value){
  	                                    var _type = Array("error");
  	                                    if(index.length > 0 && index.indexOf("_") > 0) _type = index.split("_");
  	                                    fw_info_message_add(_type[0], value);
  	                                });
  	                                fw_info_message_show();
  	                                fw_loading_end();
  	                            } else {
									var order_field = '<?php echo $order_field;?>';
							        var order_direction = '<?php echo $order_direction;?>';

							        var data = {
							            list_filter: '<?php echo $list_filter?>',
							            category_filter: $(".filterCategory").val(),
								        building_filter:$(".buildingFilter").val(),
								        customergroup_filter: $(".customerGroupFilter").val(),
								        list_filter: '<?php echo $list_filter; ?>',
								        search_filter: $('.searchFilter').val(),
										priceMatrix: '<?php if(isset($_GET['priceMatrix'])){ echo $_GET['priceMatrix']; } ?>',
										discountMatrix: '<?php if(isset($_GET['discountMatrix'])){ echo $_GET['discountMatrix']; } ?>',
							            order_field: order_field,
							            order_direction: order_direction
							        }
							        loadView("list", data);
  	                            }
  						    });
  	                    }
  	                    fw_click_instance = false;
  	                }
  	            });
  	        }
		})
	</script>
	<?php } ?>
	<style>
		.edit-discount-matrix {
			position: absolute;
			right: 5px;
		}
		.edit-price-matrix {
			position: absolute;
			right: 5px;
		}
		.gtable_cell.cPriceColumn {
			padding: 10px 25px 10px 10px;
			background: #ffffbb !important;
		}
		.gtable_cell.cDiscountColumn {
			padding: 10px 25px 10px 10px;
			background: #ffffbb !important;
		}
		.gtable_cell.cDiscountColumn {
			padding: 10px 25px 10px 10px;
		}

		.gtable_row_error .gtable_cell {
			background: #eccece;
		}
		.gtable_row_error:hover .gtable_cell {
			background: #eccece;
		}
		.alertinfo {
			color: red;
			margin-left: 5px;
			position: relative;
		}
		.alertinfo .hover {
			color: #333;
			position: absolute;
			top: 100%;
			padding: 4px 3px;
			border: 1px  solid #cecece;
			background: #fff;
			z-index: 1;
			font-family: 'PT Sans', sans-serif;
			font-size: 12px;
			width: 120px;
			left: -40px;
			font-weight: normal;
			display: none;
		}
		.alertinfo:hover .hover {
			display: block;
		}
		.syncWithArticle {
			border-radius: 5px;
			background: #0095E4;
			color: #fff;
			padding: 3px 5px;
			cursor: pointer;
			margin-top: 5px;
		}
		.syncSuggestionsTitle {
			margin-top: 5px;
		}
	</style>
<?php } ?>
