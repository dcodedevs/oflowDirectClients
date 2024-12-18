<?php
if($moduleAccesslevel > 10) {
	if(isset($_POST['output_form_submit'])) {
		$externalId = intval($_POST['external_id']);
		if($externalId > 0){
			$s_sql = "SELECT * FROM customer_externalsystem_id WHERE ownercompany_id= ?  AND external_id= ?";
			$o_query = $o_main->db->query($s_sql, array($_POST['ownercompany_id'], intval($_POST['external_id'])));
			if($o_query && $o_query->num_rows() == 0) {
				if(isset($_POST['cid']) && $_POST['cid'] > 0) {
					$s_sql = "UPDATE customer_externalsystem_id SET
					updated = now(),
					updatedBy= ?,
					ownercompany_id= ?,
		            external_id= ?
					WHERE id = ?";
					$o_main->db->query($s_sql, array($variables->loggID, $_POST['ownercompany_id'], intval($_POST['external_id']), $_POST['cid']));
				} else {
					$s_sql = "SELECT * FROM customer_externalsystem_id WHERE ownercompany_id= ? AND
		            customer_id= ?";
					$o_query = $o_main->db->query($s_sql, array($_POST['ownercompany_id'], $_POST['customerId']));
					if($o_query && $o_query->num_rows() == 0) {
						$s_sql = "INSERT INTO customer_externalsystem_id SET
						id=NULL,
						moduleID = ?,
						created = now(),
						createdBy=?,
			            ownercompany_id=?,
			            external_id=?,
			            customer_id=?";

						$o_main->db->query($s_sql, array($moduleID, $variables->loggID, $_POST['ownercompany_id'], intval($_POST['external_id']), $_POST['customerId']));

			        } else {
			        	$fw_error_msg = $formText_OwnerCompanyAlreadyAdded_output;
			        }
				}
			} else {
	        	$fw_error_msg = $formText_ChoosenIdAlreadyExistsForThisOwnerCompany_output;
	        }
	    } else {
        	$fw_error_msg = $formText_CantSaveEntryWithGivenExternalId_output;
	    }

		$fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$_POST['customerId'];
		return;
	}


    if(isset($_POST['output_delete'])) {
		if(isset($_POST['cid']) && $_POST['cid'] > 0) {
			$s_sql = "DELETE FROM customer_externalsystem_id WHERE id = ?";
            $o_main->db->query($s_sql, array($_POST['cid']));
		}
		$fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$_POST['customerId'];
		return;
	}
}


if(isset($_POST['cid']) && $_POST['cid'] > 0) {
	$s_sql = "SELECT * FROM customer_externalsystem_id WHERE id = ?";
	$o_query = $o_main->db->query($s_sql, array($_POST['cid']));
	if($o_query && $o_query->num_rows()>0) {
	    $v_data = $o_query->row_array();
		$s_sql = "SELECT * FROM ownercompany WHERE id = ?";
		$o_query = $o_main->db->query($s_sql, array($v_data['ownercompany_id']));
		if($o_query && $o_query->num_rows()>0) {
		    $ownercompanySingle = $o_query->row_array();
		}
	}
}
$ownercompanies = array();
$s_sql = "SELECT * FROM ownercompany";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0) {
    $ownercompanies = $o_query->result_array();
}
?>
<div class="popupform">
<form class="output-form" action="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=editExternalCustomerId";?>" method="post">
	<input type="hidden" name="fwajax" value="1">
	<input type="hidden" name="fw_nocss" value="1">
	<input type="hidden" name="output_form_submit" value="1">
	<input type="hidden" name="cid" value="<?php print $_POST['cid'];?>">
	<input type="hidden" name="customerId" value="<?php print $_POST['customerId'];?>">
	<div id="popup-validate-message"></div>
	<div class="inner">
		<?php if(count($ownercompanies) > 1){ ?>
        	<div class="line">
				<div class="lineTitle"><?php echo $formText_ChooseOwnerCompany_Output; ?></div>
				<div class="lineInput">
				<?php if($ownercompanySingle) { ?>
					<?php echo $ownercompanySingle['name'];?>
            		<input type="hidden" value="<?php echo $ownercompanySingle['id']?>" name="ownercompany_id"  class="buildingOwner"/>
				<?php } else { ?>
					<select name="ownercompany_id" class="buildingOwner popupforminput botspace" required>
	                    <option value=""><?php echo $formText_Select_output;?></option>
						<?php foreach ($ownercompanies as $ownercompany): ?>
							<?php if($ownercompany['customerid_autoormanually'] == 2 || $ownercompany['customerid_autoormanually'] == 3) { ?>
							<option value="<?php echo $ownercompany['id']; ?>" <?php echo $v_data['ownercompany_id'] == $ownercompany['id'] ? 'selected="selected"' : ''; ?>><?php echo $ownercompany['name']; ?></option>
							<?php } ?>
						<?php endforeach; ?>
					</select>
				<?php } ?>
				</div>
				<div class="clear"></div>
			</div>
        <?php } else if(count($ownercompanies) == 1) {  ?>
            <input type="hidden" value="<?php echo $ownercompanies[0]['id']?>" name="ownercompany_id"  class="buildingOwner"/>
        <?php } ?>
		<div class="line">
		<div class="lineTitle"><?php echo $formText_ExternalCustomerId_Output; ?></div>
		<div class="lineInput"><input class="popupforminput botspace" name="external_id" type="text" value="<?php echo $v_data['external_id'];?>" required autocomplete="off"></div>
		<div class="clear"></div>
		</div>

		<div class="clear"></div>
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
	$("#output-contactperson-address-changer").on("change", function(){
		if($(this).val() == 1)
		{
			$("#output-contactperson-address").show();
		} else {
			$("#output-contactperson-address").hide();//.find("input").val('');
		}
	});
	$(".output-access-remove-return").on('click', function(e){
		e.preventDefault();
		var _this = this;
		$($(_this).data("make-writable")+'-msg').text("");
		$.ajax({
			cache: false,
			type: 'POST',
			dataType: 'json',
			url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=remove_access";?>',
			data: { fwajax: 1, fw_nocss: 1, cid: $(this).data('id'), return_data: 1 },
			success: function(obj){
				if(obj.data.result == 1)
				{
					$(_this).remove();
					$($(_this).data("make-writable")).prop("readonly", false).css('background-color','#ffffff');
				} else {
					$($(_this).data("make-writable")+'-msg').text("<?php echo $formText_ErrorOccured_Output;?>");
				}
			}
		});
	});

	$("form.output-form").validate({
		submitHandler: function(form) {
			$.ajax({
				url: $(form).attr("action"),
				cache: false,
				type: "POST",
				dataType: "json",
				data: $(form).serialize(),
				success: function (data) {
					if(data.error !== undefined)
					{
						$("#popup-validate-message").html(data.error);
						$("#popup-validate-message").show();
						fw_loading_end();
						fw_click_instance = fw_changes_made = false;
					} else {
						if(data.redirect_url !== undefined)
						{
							out_popup.addClass("close-reload");
							out_popup.close();
							// fw_load_ajax(data.redirect_url, '', false);//window.location = data.redirect_url;
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
});
</script>
