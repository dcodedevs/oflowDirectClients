<?php
$connectionId= $_POST['connectionId'];
$customerId = $_POST['customerId'];
$membershipId = $_POST['membershipId'];

if($moduleAccesslevel > 10)
{
    $action = $_POST['action'];

	if(isset($_POST['output_form_submit'])) {
    	if($action == "updateConnection")
    	{
    		if(isset($_POST['connectionId']) && $_POST['connectionId'] > 0)
    		{
    			$s_sql = "SELECT * FROM intranet_membership_customer_connection WHERE id = ?";
    		    $o_query = $o_main->db->query($s_sql, array($_POST['connectionId']));
    		    if($o_query && $o_query->num_rows() == 1) {
    				$s_sql = "UPDATE intranet_membership_customer_connection SET
    				updated = now(),
    				updatedBy= ?,
    				customer_id= ?,
    				membership_id= ?
    				WHERE id = ?";
    				$o_query =  $o_main->db->query($s_sql, array($variables->loggID, $_POST['customerId'], $_POST['membershipId'], $_POST['connectionId']));
                    if($o_query){
                        $fw_return_data = $_POST['connectionId'];
                    }
    			}
    		} else {
    			$s_sql = "INSERT INTO intranet_membership_customer_connection SET
    			id=NULL,
    			moduleID = ?,
    			created = now(),
    			createdBy= ?,
                customer_id= ?,
                membership_id= ?";
    			$o_query = $o_main->db->query($s_sql, array($moduleID, $variables->loggID, $_POST['customerId'], $_POST['membershipId']));
                if($o_query){
                    $fw_return_data = $o_main->db->insert_id();
                }
    		}
    		$fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$_POST['customerId'];
    		return;
    	} else if($action == deleteConnection)
    	{
    		if(isset($_POST['connectionId']) && $_POST['connectionId'] > 0)
    		{
    			$s_sql = "DELETE FROM intranet_membership_customer_connection WHERE id = ?";
    			$o_query = $o_main->db->query($s_sql, array($_POST['connectionId']));
                if($o_query){
                    $fw_return_data = "success";
                }
    		}

    		$fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$_POST['customerId'];
    		return;
    	}
    }
}
$s_sql = "select intranet_membership_customer_connection.* from intranet_membership_customer_connection
where customer_id = ?";
$o_query = $o_main->db->query($s_sql, array($customerData['id']));
$customerMembershipConnections = $o_query ? $o_query->result_array() : array();

$s_sql = "select * from intranet_membership WHERE content_status < 2 ORDER BY name ASC";
$o_query = $o_main->db->query($s_sql, array());
$intranet_memberships = $o_query ? $o_query->result_array() : array();
?>
<div class="popupform">
	<div id="popup-validate-message" style="display:none;"></div>
	<form class="output-form main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=editMembershipConnection";?>" method="post">
		<input type="hidden" name="fwajax" value="1">
		<input type="hidden" name="fw_nocss" value="1">
		<input type="hidden" name="output_form_submit" value="1">
		<input type="hidden" name="customerId" value="<?php echo $customerId;?>">
		<input type="hidden" name="action" value="updateConnection">
		<input type="hidden" name="connectionId" value="<?php echo $connectionId;?>">

        <input type="hidden" name="redirect_url" value="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$customerId; ?>">
		<div class="inner">
			<div class="line">
				<div class="lineTitle"><?php echo $formText_Membership_Output; ?></div>
				<div class="lineInput">
                    <select name="membershipId" class="membershipId" autocomplete="off" required>
                        <option value=""><?php echo $formText_Select_output;?></option>
                        <?php
                        foreach($intranet_memberships as $intranet_membership) {
                            ?>
                            <option value="<?php echo $intranet_membership['id']?>" <?php if($customerMembershipConnection['membership_id'] == $intranet_membership['id']) echo 'selected';?>><?php echo $intranet_membership['name'];?></option>
                            <?php
                        }
                        ?>
                    </select>
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
<script type="text/javascript" src="../modules/<?php echo $module?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
<script type="text/javascript">

$(document).ready(function() {
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
                    if(data.redirect_url !== undefined)
                    {
                        out_popup.addClass("close-reload");
                        out_popup.close();
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
})

</script>
