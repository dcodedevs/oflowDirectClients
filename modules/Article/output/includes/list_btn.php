<?php
$s_sql = "SELECT * FROM article_accountconfig";
$o_query = $o_main->db->query($s_sql);
$article_accountconfig = $o_query ? $o_query->row_array() : array();
?>
<div class="p_headerLine"><?php
if($moduleAccesslevel > 10)
{

	//if(intval($_GET['cid']) == 0) {
	?>
	<div class="addNewCustomerBtn btnStyle">
		<div class="plusTextBox active">
			<div class="plusBox addres"><div class="lineH"></div><div class="lineV"></div></div>
			<div class="text"><?php echo $formText_AddNew_Output; ?></div>
		</div>
		<div class="clear"></div>
	</div>
	<?php //} ?>

    <?php if($article_accountconfig['activateArticlePriceMatrix']) { ?>
		<div class="addEditPriceMatrix btnStyle">
			<div class="plusTextBox active">
				<div class="plusBox addres"><div class="lineH"></div><div class="lineV"></div></div>
				<div class="text"><?php echo $formText_AddEditPriceMatrix_Output; ?></div>
			</div>
			<div class="clear"></div>
		</div>
    <?php } ?>
    <?php if($article_accountconfig['activateArticleDiscountMatrix']) { ?>
		<div class="addEditDiscountMatrix btnStyle">
			<div class="plusTextBox active">
				<div class="plusBox addres"><div class="lineH"></div><div class="lineV"></div></div>
				<div class="text"><?php echo $formText_AddEditDiscountMatrix_Output; ?></div>
			</div>
			<div class="clear"></div>
		</div>
    <?php } ?>
    <?php if($article_accountconfig['activate_article_group']) { ?>
		<div class="addEditArticleGroup btnStyle">
			<div class="plusTextBox active">
				<div class="plusBox addres"><div class="lineH"></div><div class="lineV"></div></div>
				<div class="text"><?php echo $formText_AddEditArticleGroup_Output; ?></div>
			</div>
			<div class="clear"></div>
		</div>
    <?php } ?>
	<div style="display:none;" class="boxed">
		<div id="exportForm"><?php
		$tableName = "article";
		?><form method="post" action="/accounts/<?=$_GET['accountname']?>/modules/<?=$_GET['module']?>/input/buttontypes/ExportIfbHomes/button.php" accept-charset="UTF-8">
			<p align="center">
			<?php print 'Eksport fra tabellen "'.$_GET['module'].'"'; ?>
			</p>
			<p align="center">
				<input type="hidden" value="<?=$submodule ?>" name="table">
				<input type="hidden" value="<?=$choosenListInputLang ?>" name="languageID">
				<input type="submit" value="Export!">
			</p>
		</form>

		</div>
	</div>
	<?php include(__DIR__.'/ajax.import_data.php'); ?>

	<div class="btnStyle">
		<div class="plusTextBox active">
			<div class="text">
				<a href="<?php echo $_SERVER['PHP_SELF'].'/../../modules/'.$module.'/output/exportArticles.php?time='.time();?>" target="_blank">
					<?php echo $formText_ExportArticles_output;?>
				</a>
			</div>
		</div>
		<div class="clear"></div>
	</div>


	<?php if($variables->developeraccess) { ?>
		<div class="syncArticles btnStyle">
			<div class="plusTextBox active">
				<div class="text"><?php echo $formText_SyncAllArticles_Output; ?></div>
			</div>
			<div class="clear"></div>
		</div>
	<?php } ?>


    <?php if($variables->developeraccess > 5) { ?>
		<div class="importFromIntegration btnStyle">
			<div class="plusTextBox active">
				<div class="text"><?php echo $formText_ImportFromIntegration_Output; ?></div>
			</div>
			<div class="clear"></div>
		</div>
    <?php } ?>

	<?php if($article_accountconfig['activate_order_sync']) { ?>
		<div class="syncOrders btnStyle">
			<div class="plusTextBox active">
				<div class="text"><?php echo $formText_SyncOrders_Output; ?></div>
			</div>
			<div class="clear"></div>
		</div>
	<?php } ?>
	<div class="clear"></div>
	<?php
}
?></div>


<script type="text/javascript">
$(".addNewCustomerBtn").on('click', function(e){
    e.preventDefault();
    var data = {
        articleId: 0,
		set_id: '<?php echo $company_product_set_id?>'
    };
    ajaxCall('editArticle', data, function(json) {
        $('#popupeditboxcontent').html('');
        $('#popupeditboxcontent').html(json.html);
        out_popup = $('#popupeditbox').bPopup(out_popup_options);
        $("#popupeditbox:not(.opened)").remove();
    });
});
$(".syncArticles").on('click', function(e){
    e.preventDefault();
    var data = {
        articleId: 0
    };
    ajaxCall('sync_all_articles', data, function(json) {
        $('#popupeditboxcontent').html('');
        $('#popupeditboxcontent').html(json.html);
        out_popup = $('#popupeditbox').bPopup(out_popup_options);
        $("#popupeditbox:not(.opened)").remove();
    });
});
$(".addEditPriceMatrix").on('click', function(e){
	e.preventDefault();
	var data = { };
    ajaxCall('add_price_matrix', data, function(obj) {
        $('#popupeditboxcontent').html('');
        $('#popupeditboxcontent').html(obj.html);
        out_popup = $('#popupeditbox').bPopup(out_popup_options);
        $("#popupeditbox:not(.opened)").remove();
    });
});
$(".addEditDiscountMatrix").on('click', function(e){
	e.preventDefault();
	var data = { };
    ajaxCall('add_discount_matrix', data, function(obj) {
        $('#popupeditboxcontent').html('');
        $('#popupeditboxcontent').html(obj.html);
        out_popup = $('#popupeditbox').bPopup(out_popup_options);
        $("#popupeditbox:not(.opened)").remove();
    });
});
$(".importFromIntegration").off("click").on("click", function(e){
	e.preventDefault();
	var data = { };
	ajaxCall('import_from_integration', data, function(obj) {
		$('#popupeditboxcontent').html('');
		$('#popupeditboxcontent').html(obj.html);
		out_popup = $('#popupeditbox').bPopup(out_popup_options);
		$("#popupeditbox:not(.opened)").remove();
	});
})
$(".addEditArticleGroup").on('click', function(e){
	e.preventDefault();
	var data = { };
    ajaxCall('add_group', data, function(obj) {
        $('#popupeditboxcontent').html('');
        $('#popupeditboxcontent').html(obj.html);
        out_popup = $('#popupeditbox').bPopup(out_popup_options);
        $("#popupeditbox:not(.opened)").remove();
    });
});
$(".syncFromAccounting").off("click").on("click", function(e){
	e.preventDefault();
	var data = { };
    ajaxCall('syncFromAccounting', data, function(obj) {
        $('#popupeditboxcontent').html('');
        $('#popupeditboxcontent').html(obj.html);
        out_popup = $('#popupeditbox').bPopup(out_popup_options);
        $("#popupeditbox:not(.opened)").remove();
    });
})
$(".syncOrders").off("click").on("click", function(e){
	e.preventDefault();
	var data = { };
    ajaxCall('syncOrders', data, function(obj) {
        $('#popupeditboxcontent').html('');
        $('#popupeditboxcontent').html(obj.html);
        out_popup = $('#popupeditbox').bPopup(out_popup_options);
        $("#popupeditbox:not(.opened)").remove();
    });
})
</script>
<style>
	.p_headerLine .btnStyle.addEditPriceMatrix {
		margin-left: 40px;
	}
	.p_headerLine .btnStyle.addEditDiscountMatrix {
		margin-left: 40px;
	}
	.p_headerLine .btnStyle.addEditArticleGroup {
		margin-left: 40px;
	}
</style>
