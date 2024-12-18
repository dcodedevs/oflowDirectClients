<?php
$o_query = $o_main->db->query("SELECT * FROM article_supplier WHERE id = '".$o_main->db->escape_str($_POST['supplier_id'])."'");
$v_collectingorder = $o_query ? $o_query->row_array() : array();
if($moduleAccesslevel > 10)
{
	if(isset($_POST['output_form_submit']))
	{
		if(isset($_POST['supplier_id']) && $_POST['supplier_id'] > 0)
		{

			$s_sql = "UPDATE article_supplier SET
			updated = NOW(),
			updatedBy = '".$o_main->db->escape_str($variables->loggID)."',
			name = '".$o_main->db->escape_str($_POST['name'])."',
			supplier_prefix = '".$o_main->db->escape_str($_POST['supplier_prefix'])."',
			defaultSalesAccountWithVat = '".$o_main->db->escape_str($_POST['defaultSalesAccountWithVat'])."',
			defaultVatCodeWithVat = '".$o_main->db->escape_str($_POST['defaultVatCodeWithVat'])."',
			main_article = '".$o_main->db->escape_str($_POST['articleId'])."'
			WHERE id = '".$o_main->db->escape_str($_POST['supplier_id'])."'";
			$o_main->db->query($s_sql);
            $supplier_id = $_POST['supplier_id'];
		} else {
            $s_sql = "INSERT INTO article_supplier SET
			created = NOW(),
			createdBy = '".$o_main->db->escape_str($variables->loggID)."',
			name = '".$o_main->db->escape_str($_POST['name'])."',
			supplier_prefix = '".$o_main->db->escape_str($_POST['supplier_prefix'])."',
			defaultSalesAccountWithVat = '".$o_main->db->escape_str($_POST['defaultSalesAccountWithVat'])."',
			defaultVatCodeWithVat = '".$o_main->db->escape_str($_POST['defaultVatCodeWithVat'])."',
			main_article = '".$o_main->db->escape_str($_POST['articleId'])."'";
			$o_main->db->query($s_sql);
            $supplier_id = $o_main->db->insert_id();
        }

		$fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details_supplier&cid=".$supplier_id;
		return;
	}
}

$s_sql = "SELECT * FROM article WHERE id = ?";
$o_query = $o_main->db->query($s_sql, array($v_collectingorder['main_article']));
$main_article = ($o_query ? $o_query->row_array() : array());
?>
<div class="popupform">
	<div id="popup-validate-message" style="display:none;"></div>
<form class="output-form" action="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=edit_supplier";?>" method="post">
	<input type="hidden" name="fwajax" value="1">
	<input type="hidden" name="fw_nocss" value="1">
	<input type="hidden" name="output_form_submit" value="1">
	<input type="hidden" name="supplier_id" value="<?php print $_POST['supplier_id'];?>">

	<div class="inner">
		<div class="line">
			<div class="lineTitle"><?php echo $formText_Name_Output; ?></div>
			<div class="lineInput"><input type="text" class="popupforminput botspace" autocomplete="off" name="name" value="<?php echo $v_collectingorder['name'];?>"></div>
			<div class="clear"></div>
		</div>
		<div class="line">
			<div class="lineTitle"><?php echo $formText_MainArticle_Output; ?></div>
			<div class="lineInput">
				<?php if($main_article) { ?>
				<a href="#" class="selectArticle"><?php echo $main_article['name']?></a>
				<?php } else { ?>
				<a href="#" class="selectArticle"><?php echo $formText_SelectArticle_Output;?></a>
				<?php } ?>
				<input type="hidden" name="articleId" id="articleId" value="<?php print $main_article['id'];?>" required>

			</div>
			<div class="clear"></div>
		</div>
		<?php /*
		<div class="line">
			<div class="lineTitle"><?php echo $formText_SupplierPrefix_Output; ?></div>
			<div class="lineInput"><input type="text" class="popupforminput botspace" autocomplete="off" name="supplier_prefix" value="<?php echo $v_collectingorder['supplier_prefix'];?>"></div>
			<div class="clear"></div>
		</div>
		<div class="line">
			<div class="lineTitle"><?php echo $formText_DefaultSalesAccountWithVat_Output; ?></div>
			<div class="lineInput"><input type="text" class="popupforminput botspace" autocomplete="off" name="defaultSalesAccountWithVat" value="<?php echo $v_collectingorder['defaultSalesAccountWithVat'];?>"></div>
			<div class="clear"></div>
		</div>
		<div class="line">
			<div class="lineTitle"><?php echo $formText_DefaultVatCodeWithVat_Output; ?></div>
			<div class="lineInput"><input type="text" class="popupforminput botspace" autocomplete="off" name="defaultVatCodeWithVat" value="<?php echo $v_collectingorder['defaultVatCodeWithVat'];?>"></div>
			<div class="clear"></div>
		</div>*/ ?>

	</div>
	<div class="popupformbtn">
		<button type="button" class="output-btn b-large b-close"><?php echo $formText_Cancel_Output;?></button>
		<input type="submit" name="sbmbtn" value="<?php echo $formText_Save_Output; ?>">
	</div>
</form>
</div>
<style>

.popupform label.error {
	display: none !important;
}
.popupform .lineTitle {
	font-weight:700;
	margin-bottom: 10px;
}
.popupform textarea.popupforminput {
	border-radius: 4px;
	padding:5px 10px;
	font-size:12px;
	line-height:17px;
}
</style>
<script type="text/javascript" src="../modules/<?php echo $module?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
<script type="text/javascript">
$(function() {
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
					/*if(data.error !== undefined)
					{
						$.each(data.error, function(index, value){
							var _type = Array("error");
							if(index.length > 0 && index.indexOf("_") > 0) _type = index.split("_");
							fw_info_message_add(_type[0], value);
						});
						fw_info_message_show();
						fw_loading_end();
						fw_click_instance = fw_changes_made = false;
					} else {*/
						if(data.redirect_url !== undefined)
						{
							out_popup.addClass("close-reload").data("redirect", data.redirect_url);;
							out_popup.close();
						}
					//}
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
	$(".datepicker").datepicker({
		firstDay: 1,
		dateFormat: 'dd.mm.yy',
		showButtonPanel: true,
		 closeText: 'Clear',
		 onClose: function (dateText, inst) {
			var event = arguments.callee.caller.caller.arguments[0];
			// If "Clear" gets clicked, then really clear it
			if ($(event.delegateTarget).hasClass('ui-datepicker-close')) {
				$(this).val('');
			}
		 }
	});
	$(".popupform .selectArticle").unbind("click").bind("click", function(e){
		e.preventDefault();
		fw_loading_start();
		var _data = { fwajax: 1, fw_nocss: 1};
		$.ajax({
			cache: false,
			type: 'POST',
			dataType: 'json',
			url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=get_articles";?>',
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
});
</script>
