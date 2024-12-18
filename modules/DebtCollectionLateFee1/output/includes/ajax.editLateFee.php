<?php
if($moduleAccesslevel > 10)
{
	if(isset($_POST['output_form_submit']))
	{
		if(isset($_POST['cid']) && $_POST['cid'] > 0)
		{
			$s_sql = "SELECT * FROM debtcollectionlatefee_main WHERE id = ?";
		    $o_query = $o_main->db->query($s_sql, array($_POST['cid']));
		    if($o_query && $o_query->num_rows() == 1) {
				$s_sql = "UPDATE debtcollectionlatefee_main SET
				updated = now(),
				updatedBy= ?,
				internal_name= ?,
				article_name= ?
				WHERE id = ?";
				$o_main->db->query($s_sql, array($variables->loggID, $_POST['internal_name'], $_POST['article_name'], $_POST['cid']));
			}
		} else {
			$s_sql = "INSERT INTO debtcollectionlatefee_main SET
			id=NULL,
			moduleID = ?,
			created = now(),
			createdBy= ?,
			internal_name = ?,
			article_name= ?";
			$o_main->db->query($s_sql, array($moduleID, $variables->loggID, $_POST['internal_name'], $_POST['article_name']));
			$_POST['cid'] = $o_main->db->insert_id();
		}

		$fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$_POST['caseId'];
		return;
	} else if(isset($_POST['output_delete']))
	{
		if(isset($_POST['cid']) && $_POST['cid'] > 0)
		{
			$s_sql = "DELETE debtcollectionlatefee_main, debtcollectionlatefee_amount FROM debtcollectionlatefee_main
			LEFT OUTER JOIN debtcollectionlatefee_amount ON debtcollectionlatefee_amount.debtcollectionlatefee_main_id = debtcollectionlatefee_main.id
			WHERE debtcollectionlatefee_main.id = ?";
			$o_main->db->query($s_sql, array($_POST['cid']));
		}
		$fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$_POST['caseId'];
		return;
	}
}

if(isset($_POST['cid']) && $_POST['cid'] > 0)
{
	$s_sql = "SELECT * FROM debtcollectionlatefee_main WHERE id = ?";
    $o_query = $o_main->db->query($s_sql, array($_POST['cid']));
    if($o_query && $o_query->num_rows()>0) {
        $v_data = $o_query->row_array();
    }
}
?>
<div class="popupform">
<form class="output-form" action="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=".$s_inc_act;?>" method="post">
	<input type="hidden" name="fwajax" value="1">
	<input type="hidden" name="fw_nocss" value="1">
	<input type="hidden" name="output_form_submit" value="1">
	<input type="hidden" name="cid" value="<?php print $_POST['cid'];?>">

	<div class="inner">
		<div class="line">
			<div class="lineTitle"><?php echo $formText_InternalName_Output; ?></div>
			<div class="lineInput">
				<input type="text" name="internal_name" autocomplete="off" class="popupforminput botspace" value="<?php echo $v_data['internal_name']?>"/>
			</div>
			<div class="clear"></div>
		</div>
		<div class="line">
			<div class="lineTitle"><?php echo $formText_ArticleName_Output; ?></div>
			<div class="lineInput">
				<input type="text" name="article_name" autocomplete="off" class="popupforminput botspace" value="<?php echo $v_data['article_name']?>"/>
			</div>
			<div class="clear"></div>
		</div>
	</div>
	<div class="popupformbtn"><input type="submit" name="sbmbtn" value="<?php echo $formText_Save_Output; ?>"></div>
</form>
</div>
<style>

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
.project-file {
	margin-bottom: 4px;
}
.project-file .deleteImage {
	float: right;
}
</style>
<script type="text/javascript" src="../modules/<?php echo $module?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
<script type="text/javascript">

$(function() {
	$("form.output-form").validate({
		submitHandler: function(form) {
			fw_loading_start();

			if(!fw_click_instance){
				// $('textarea.ckeditor').each(function () {
				// 	var $textarea = $(this);
				// 	$textarea.val(CKEDITOR.instances[$textarea.attr('id')].getData());
				// });

				$("#popup-validate-message").html("");
				var formdata = $(form).serializeArray();
				var data = {};
				$(formdata ).each(function(index, obj){
					if(data[obj.name] != undefined) {
						if(Array.isArray(data[obj.name])){
							data[obj.name].push(obj.value);
						} else {
							data[obj.name] = [data[obj.name], obj.value];
						}
					} else {
						data[obj.name] = obj.value;
					}
				});

				$.ajax({
					url: $(form).attr("action"),
					cache: false,
					type: "POST",
					dataType: "json",
					data: data,
					success: function (data) {
						fw_click_instance = false;
						fw_loading_end();
						if(data.error !== undefined)
						{
							$.each(data.error, function(index, value){
								$("#popup-validate-message").append("<div>"+value+"</div>").show();
							});
							fw_click_instance = fw_changes_made = false;
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
			}
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
});
</script>
