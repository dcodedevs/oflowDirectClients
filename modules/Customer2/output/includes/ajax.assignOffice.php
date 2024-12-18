<?php
if ($_POST['connectionId']) {
    $sql = "DELETE FROM subscriptionofficespaceconnection WHERE id = ?";
    $o_main->db->query($sql, array($_POST['connectionId']));
}

if($moduleAccesslevel > 10) {
	if(isset($_POST['output_form_submit'])) {
		if(intval($_POST['officeSpaceId']) > 0){
			$fw_redirect_url = "";
			if(intval($_POST['forceSave']) == 0){				
				$s_sql = "SELECT * FROM subscriptionmulti WHERE id = ?";
				$o_query = $o_main->db->query($s_sql, array($_POST['subscriptionId']));
				$subscriptionData = ($o_query ? $o_query->row_array() : array());

				$s_sql = "SELECT * FROM subscriptionmulti JOIN subscriptionofficespaceconnection c ON c.subscriptionId = subscriptionmulti.id
				JOIN property_unit ON property_unit.id =  c.officeSpaceId
				WHERE subscriptionmulti.startDate < ? AND ((subscriptionmulti.stoppedDate >= ? AND subscriptionmulti.stoppedDate <> '0000-00-00' AND subscriptionmulti.stoppedDate is not null) OR subscriptionmulti.stoppedDate IS NULL OR subscriptionmulti.stoppedDate = '0000-00-00')
				AND property_unit.id = ?";

				$o_query = $o_main->db->query($s_sql, array($subscriptionData['startDate'], $subscriptionData['startDate'], $_POST['officeSpaceId']));
				$activeUnitSubscriptions = ($o_query ? $o_query->num_rows() : 0);
				if($activeUnitSubscriptions > 0 ){
					$fw_redirect_url = 'showWarning';
				}
			}		
			if($fw_redirect_url == ''){

				$s_sql = "INSERT INTO subscriptionofficespaceconnection SET
				id=NULL,
				moduleID = ?,
				created = now(),
				createdBy= ?,
				officeSpaceId= ?,
				subscriptionId= ?";

				$o_main->db->query($s_sql, array($moduleID, $variables->loggID, $_POST['officeSpaceId'], $_POST['subscriptionId']));

				$s_sql = "SELECT * FROM property_unit LEFT OUTER JOIN property ON property.id = property_unit.property_id WHERE property_unit.id = ?";
				$o_query = $o_main->db->query($s_sql, array($_POST['officeSpaceId']));
				$propertyData = ($o_query ? $o_query->row_array() : array());

				$s_sql = "UPDATE subscriptionmulti SET projectId = ? WHERE id = ?";
				$o_main->db->query($s_sql, array($propertyData['accountingproject_id'], $_POST['subscriptionId']));

				$fw_redirect_url = $_POST['redirect_url'];
	        	$fw_return_data = $s_sql;
			}
			return;
		} else {

		}
	}
}

// Check if subscription already has some office assigned. If yes, then limit officepsaces to that particular building
// One subscription can be connected to multiple offices IF they are from the smame building
$s_sql = "SELECT c.id connectionId, property_unit.id, property_unit.property_id FROM property_unit
LEFT JOIN subscriptionofficespaceconnection c ON c.officeSpaceId = property_unit.id
WHERE c.subscriptionId = ?";
$o_query = $o_main->db->query($s_sql, array($_POST['subscriptionId']));
if($o_query && $o_query->num_rows()>0) {
    $first_connected_office_data = $o_query->row_array();
}
$s_sql = "SELECT * FROM subscriptionmulti WHERE id = ?";
$o_query = $o_main->db->query($s_sql, array($_POST['subscriptionId']));
$subscriptionData = ($o_query ? $o_query->row_array() : array());
?>
<div class="popupform">
	<div id="popup-validate-message" style="display:none;"></div>
	<form class="output-form main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=assignOffice";?>" method="post">
		<input type="hidden" name="fwajax" value="1">
		<input type="hidden" name="fw_nocss" value="1">
		<input type="hidden" name="output_form_submit" value="1">
		<input type="hidden" name="subscriptionId" value="<?php echo $_POST['subscriptionId'];?>">
        <input type="hidden" name="redirect_url" value="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$_POST['customerId']; ?>">

		<div class="inner">
			<div class="line">
        		<div class="lineTitle"><?php echo $formText_Property_Output; ?></div>
        		<div class="lineInput">
        			 <div class="selectDiv selectDivModified">
                        <div class="selectDivWrapper">
                            <select name="propertyId" required class="propertySelect">
                            	<?php 
                            		if($first_connected_office_data) {
	                            		$s_sql = "SELECT * FROM property WHERE owner_id = ? AND id = ?";
							  			$o_query = $o_main->db->query($s_sql, array($subscriptionData['ownercompany_id'], $first_connected_office_data['property_id']));
	                            	} else {
	                            		$s_sql = "SELECT * FROM property WHERE owner_id = ? ORDER BY name ASC";
							  			$o_query = $o_main->db->query($s_sql, array($subscriptionData['ownercompany_id']));
	                            	}
							  		$properties = ($o_query ? $o_query->result_array() : array());
							  		if(count($properties) == 1){
							  			?>
                                		<option value="<?php echo $properties[0]['id']; ?>"><?php echo $properties[0]['name']; ?></option>
							  			<?php
							  		} else {
							  			?>
                                		<option value=""><?php echo $formText_ChooseProperty_output; ?></option>
							  			<?php 
							  			foreach($properties as $property) {
							  				?>
							  				<option value="<?php echo $property['id']; ?>">
												<?php echo $property['name']; ?>
											</option>
							  				<?php
							  			}
							  		}
                            	?>
                            </select>
                            <div class="arrowDown"></div>
                        </div>
                    </div>
                </div>
        		<div class="clear"></div>
            </div>
    		<div class="line">
        		<div class="lineTitle"><?php echo $formText_Office_Output; ?></div>
        		<div class="lineInput">
                    <div class="selectDiv selectDivModified officeSelect">
                        <div class="selectDivWrapper">
                            
                        </div>
                    </div>
    				<div class="clear"></div>
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
							if(data.redirect_url === 'showWarning') {
								bootbox.confirm('<?php echo $formText_PropertyUnitHasAttachementToDifferentSubscriptionWithGivenPeriod_output; ?>. <?php echo $formText_DoYouWantToAttachItAnyway_output;?>?', function(result) {
									if (result) {
										fw_loading_start();
										$(form).append("<input type='hidden' name='forceSave' value='1'/>");
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
										})
									}
								}).css({"z-index":"1000000"});
							} else {
								out_popup.addClass("close-reload");
								out_popup.close();
							}
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
 	$(".propertySelect").change(function(){
        var data = {
            ownercompany_id: '<?php echo $subscriptionData['ownercompany_id'];?>',
            property_id: $(this).val()
        };
        ajaxCall('getPropertyUnits', data, function(json) {
            $('.officeSelect .selectDivWrapper').html(json.html);
        });
    })
    $(".propertySelect").change();
});

</script>
<style>
.popupform .line {
	margin-bottom: 10px;
}
.selectDivModified {
    display:block;
}
.popupform, .popupeditform {
	width:100%;
	margin:0 auto;
	border:1px solid #e8e8e8;
	position:relative;
}
label.error { display: none !important; }
.popupform .popupforminput.error { border-color:#c11 !important;}
#popup-validate-message, .error-msg { font-weight:bold; color:#c11; padding-bottom:10px; }
/* css for timepicker */
.ui-timepicker-div .ui-widget-header { margin-bottom: 8px; }
.ui-timepicker-div dl { text-align: left; }
.ui-timepicker-div dl dt { height: 25px; margin-bottom: -25px; }
.ui-timepicker-div dl dd { margin: 0 10px 10px 65px; }
.ui-timepicker-div td { font-size: 90%; }
.ui-tpicker-grid-label { background: none; border: none; margin: 0; padding: 0; }
.clear {
	clear:both;
}
.inner {
	padding:10px;
}
.pplineV {
	position:absolute;
	top:0;bottom:0;left:70%;
	border-left:1px solid #e8e8e8;
}
.popupform input.popupforminput, .popupform textarea.popupforminput, .popupform select.popupforminput, .col-md-8z input {
	width:100%;
	border-radius: 4px;
	padding:5px 10px;
	font-size:12px;
	line-height:17px;
	color:#3c3c3f;
	background-color:transparent;
	-webkit-box-sizing: border-box;
	   -moz-box-sizing: border-box;
		 -o-box-sizing: border-box;
			box-sizing: border-box;
	font-weight:400;
	border: 1px solid #cccccc;
}
.popupformname {
	font-size:12px;
	font-weight:bold;
	padding:5px 0px;
}
.popupforminput.botspace {
	margin-bottom:10px;
}
textarea {
	min-height:50px;
	max-width:100%;
	min-width:100%;
	width:100%;
}
.popupformname {
	font-weight: 700;
	font-size: 13px;
}
.popupformbtn {
	text-align:right;
	margin:10px;
}
.popupformbtn input {
	border-radius:4px;
	border:1px solid #0393ff;
	background-color:#0393ff;
	font-size:13px;
	line-height:0px;
	padding: 20px 35px;
	font-weight:700;
	color:#FFF;
	margin-left:10px;
}
.error {
	border: 1px solid #c11;
}
.popupform .lineTitle {
	font-weight:700;
}
.popupform .line .lineTitle {
	width:30%;
	float:left;
	font-weight:700;
	padding:5px 0;
}
.popupform .line .lineInput {
	width:70%;
	float:left;
}
</style>
