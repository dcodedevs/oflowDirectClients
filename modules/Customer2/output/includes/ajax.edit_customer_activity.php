<?php
if($moduleAccesslevel > 10)
{
	if(isset($_POST['output_form_submit']))
	{
		if(isset($_POST['cid']) && $_POST['cid'] > 0)
		{
			$s_sql = "SELECT * FROM customer_activity WHERE id = ?";
		    $o_query = $o_main->db->query($s_sql, array($_POST['cid']));
		    if($o_query && $o_query->num_rows() == 1) {
				$s_sql = "UPDATE customer_activity SET
				updated = now(),
				updatedBy= ?,
				title= ?,
				description= ?
				WHERE id = ?";
				$o_main->db->query($s_sql, array($variables->loggID, $_POST['title'],$_POST['description'], $_POST['cid']));
			}
		} else {
			$s_sql = "INSERT INTO customer_activity SET
			id=NULL,
			moduleID = ?,
			created = now(),
			createdBy= ?,
            customer_id= ?,
            activity_type_id= ?,
            title= ?,
            description= ?";
			$o_main->db->query($s_sql, array($moduleID, $variables->loggID, $_POST['customerId'], $_POST['typeId'], $_POST['title'],$_POST['description']));
		}

		$fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$_POST['customerId'];
		return;
	} else if(isset($_POST['output_delete']))
	{
		if(isset($_POST['cid']) && $_POST['cid'] > 0)
		{
			$s_sql = "DELETE FROM customer_activity WHERE id = ?";
			$o_main->db->query($s_sql, array($_POST['cid']));
		}

		$fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$_POST['customerId'];
		return;
	}
}

if(isset($_POST['cid']) && $_POST['cid'] > 0)
{
	$s_sql = "SELECT * FROM customer_activity WHERE id = ?";
    $o_query = $o_main->db->query($s_sql, array($_POST['cid']));
    if($o_query && $o_query->num_rows()>0) {
        $v_data = $o_query->row_array();
    }
}
?>
<div class="popupform">
<form class="output-form" action="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=edit_customer_activity";?>" method="post">
	<input type="hidden" name="fwajax" value="1">
	<input type="hidden" name="fw_nocss" value="1">
	<input type="hidden" name="output_form_submit" value="1">
	<input type="hidden" name="cid" value="<?php print $_POST['cid'];?>">
	<input type="hidden" name="typeId" value="<?php print $_POST['typeId'];?>">
	<input type="hidden" name="customerId" value="<?php print $_POST['customerId'];?>">

	<div class="inner">
        <div class="line">
    		<div class="lineTitle"><?php echo $formText_Title_Output; ?></div>
    		<div class="lineInput"><input type="text" class="popupforminput botspace" autocomplete="off" name="title" value="<?php echo $v_data['title'];?>"></div>
    		<div class="clear"></div>
		</div>
		<div class="line">
    		<div class="lineTitle"><?php echo $formText_Description_Output; ?></div>
    		<div class="lineInput"><textarea class="popupforminput" name="description" required><?php echo $v_data['description'];?></textarea></div>
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
							out_popup.addClass("close-reload");
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
});
</script>