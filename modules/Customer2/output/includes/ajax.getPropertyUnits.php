<?php
$ownercompany_id = $_POST['ownercompany_id'] ? ($_POST['ownercompany_id']) : 0;
$property_id =  $_POST['property_id'] ? ($_POST['property_id']) : 0;
$propertypartId =  $_POST['propertypartId'] ? ($_POST['propertypartId']) : 0;
$subscriptionId = $_POST['subscriptionId'] ? ($_POST['subscriptionId']) : 0;

if($propertypartId && $subscriptionId) {
	$s_sql = "SELECT * FROM subscriptionofficespaceconnection WHERE officeSpaceId = ? AND subscriptionId = ?";
	$o_query = $o_main->db->query($s_sql, array($propertypartId, $subscriptionId));
	$connectionData = ($o_query ? $o_query->row_array() : array());
}

$s_sql = "SELECT * FROM property WHERE id = ?";
$o_query = $o_main->db->query($s_sql, array($property_id));
$property = ($o_query ? $o_query->row_array() : array());
$date = date("Y-m-d", time());

$s_sql = "SELECT * FROM property_unit WHERE property_id = ? AND rentarea_or_commonarea = 1 AND (dateTo >= str_to_date(?, '%Y-%m-%d') OR dateTo = '0000-00-00' OR dateTo is null) ORDER BY propertypart_id, name ASC";
$o_query = $o_main->db->query($s_sql, array($property_id, $date));
$propertyUnits = ($o_query ? $o_query->result_array() : array());
if($property && $propertyUnits) {
	?>
<div class="popupform">
	<div id="popup-validate-message2" style="display:none;"></div>
	<form class="output-form2 main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=getPropertyUnits";?>" method="post">
		<input type="hidden" name="fwajax" value="1">
		<input type="hidden" name="fw_nocss" value="1">
		<input type="hidden" name="output_form_submit" value="1">
		<input type="hidden" name="subscriptionId" value="<?php echo $_POST['subscriptionId'];?>">
        <input type="hidden" name="redirect_url" value="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$_POST['customerId']; ?>">
		<input type="hidden" name="connect_each_unit_to_subscriptionline" value="<?php echo $_POST['connect_each_unit_to_subscriptionline'];?>"/>
		<div class="inner">
			<div class="line">
				<div class="lineTitle"><?php echo $formText_Office_Output; ?></div>
				<div class="lineInput">
			        <div class="officeSelect">
						<select name="officeSpaceId[]" required>
						    <option value=""><?php echo $formText_SelectOffice_output; ?></option>
						    <?php
							$dateMarker = date("Y-m-d", time());

						    foreach($propertyUnits as $unit){
								$s_sql = "SELECT * FROM property_part WHERE id = ".$unit['propertypart_id'];
								$o_query = $o_main->db->query($s_sql);
								$propertyPart = ($o_query ? $o_query->row_array() : array());

                                $s_sql = "SELECT * FROM property WHERE id = ".$unit['property_id'];
                                $o_query = $o_main->db->query($s_sql);
                                $property = ($o_query ? $o_query->row_array() : array());

								$unavailableText = "";
							 	$s_sql = "SELECT subscriptionmulti.* FROM subscriptionofficespaceconnection
					            LEFT OUTER JOIN subscriptionmulti ON subscriptionmulti.id = subscriptionofficespaceconnection.subscriptionId
					            LEFT OUTER JOIN customer ON customer.id = subscriptionmulti.customerId
					            LEFT OUTER JOIN property_unit ON property_unit.id = subscriptionofficespaceconnection.officeSpaceId
					            WHERE customer.id is not null AND subscriptionmulti.id is not null and property_unit.id is not null AND property_unit.id = ?
								AND str_to_date(subscriptionmulti.startDate,'%Y-%m-%d') > str_to_date('0000-00-00','%Y-%m-%d')
								AND str_to_date(subscriptionmulti.startDate,'%Y-%m-%d') <= str_to_date('".$dateMarker."','%Y-%m-%d')
								AND (stoppedDate = '0000-00-00' OR (str_to_date(subscriptionmulti.stoppedDate,'%Y-%m-%d') > str_to_date('".$dateMarker."','%Y-%m-%d')))
								 AND subscriptionmulti.content_status = 0";
					            $o_query = $o_main->db->query($s_sql, array($unit['id']));
					            if($o_query && $o_query->num_rows()>0){
					            	$unavailableText = " - ".$formText_Unavailable_output;
					            	$subscription = $o_query->row_array();
					            	if($subscription['stoppedDate'] != "0000-00-00"){
					            		$unavailableText = " - ".$formText_AvailableFrom_output." ".date("d.m.Y", strtotime("+ 1 day",strtotime($subscription['stoppedDate'])));
					            	}
					            }
								?>
							    <option value="<?php echo $unit['id']; ?>" data-unitsize='<?php echo $unit['size'];?>' data-unitname='<?php echo $unit['name'];?>' data-propertypartname='<?php echo $propertyPart['name'];?>' data-propertyname='<?php echo $property['name'];?>'
									<?php if($connectionData['officeSpaceId'] == $unit['id']) echo 'selected';?>>
									<?php if($propertyPart) echo $propertyPart['name']. " - "; ?>
									<?php echo $unit['name']; ?>
									<?php echo $unavailableText." ";?>
									<?php if($unit['dateFrom'] > $date) {
										echo " / ".$formText_FutureStart_output." ".date("d.m.Y", strtotime($unit['dateFrom']));
									} ?>
								</option>
						<?php } ?>
						</select>
					    <div class="arrowDown"></div>
			        </div>
			    </div>
				<div class="clear"></div>
			</div>
			<?php if($_POST['connect_each_unit_to_subscriptionline']) {?>
				<div class="line">
					<div class="lineTitle"><?php echo $formText_ConnectToSubscriptionline_Output; ?></div>
					<div class="lineInput">
						<select class="subscriptionlineSelect" name="subscriptionline_id[]" required>
						    <option value=""><?php echo $formText_SelectSubscriptionline_output; ?></option>
							<?php

							$s_sql = "SELECT * FROM subscriptionline WHERE subscribtionId = ?";
						   	$o_query = $o_main->db->query($s_sql, array($_POST['subscriptionId']));
						   	$subrows = $o_query ? $o_query->result_array() : array();
							foreach($subrows as $subscriptionline){
							?>
							    <option value="<?php echo $subscriptionline['id']?>" data-name='<?php echo $subscriptionline['articleName'];?>'>
									<?php echo $subscriptionline['articleName'];?>
								</option>
							<?php } ?>
						</select>
					</div>
					<div class="clear"></div>
				</div>
			<?php } ?>
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
	$("form.output-form2").validate({
		submitHandler: function(form) {
			fw_loading_start();
			var officeId = $(".officeSelect select").val();
			var propertyName = $(".officeSelect select option:selected").data("propertyname");
			var propertyPartName = $(".officeSelect select option:selected").data("propertypartname");
			var unitName = $(".officeSelect select option:selected").data("unitname");
			var unitSize = $(".officeSelect select option:selected").data("unitsize");

			var subscriptionlineId = $(".subscriptionlineSelect").val();
			var subscriptionlineName = $(".subscriptionlineSelect option:selected").data("name");
			var liWrapper = $(".popupform .subscription-office-list").find('li[data-propertyunitid="'+officeId+'"]');

			var html = '<span class="subscription-office-list-name">'+
						'<input type="hidden" name="officeSpaceId[]" value="'+officeId+'"/>'+
						propertyName+' - '+
						propertyPartName+' - '+unitName+
						'<input type="hidden" name="subscriptionline_id[]" value="'+subscriptionlineId+'"/>';

			if(subscriptionlineName != undefined){
				html += '<br/><span class="subscriptionlineName">'+subscriptionlineName+'</span> ';
			}
			html += '</span>'+
					' <span class="subscription-office-list-size">'+
						unitSize+'<?php echo " ".$formText_Squaremeter_output; ?>'+
						'<span class="glyphicon glyphicon-pencil addOffice" data-subscription-id="<?php echo $_POST['subscriptionId']?>" data-propertypart-id="'+officeId+'"></span>'+
						'<span class="glyphicon glyphicon-trash delete-office"></span>'+
					'</span>  ';
			if(liWrapper.length == 0){
				$(".popupform .subscription-office-list").append('<li data-propertyunitid="'+officeId+'">'+html+'</li>');
		   	} else {
				liWrapper.html(html);
		   	}

		    $(".delete-office").unbind("click").bind("click", function(){
		    	var deleteOffice = $(this).parents("li");
		    	deleteOffice.remove();
		    })
			out_popup2.close();
			fw_loading_end();
		},
		invalidHandler: function(event, validator) {
			var errors = validator.numberOfInvalids();
			if (errors) {
				var message = errors == 1
				? '<?php echo $formText_YouMissed_validate; ?> 1 <?php echo $formText_field_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>'
				: '<?php echo $formText_YouMissed_validate; ?> ' + errors + ' <?php echo $formText_fields_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>';

				$("#popup-validate-message2").html(message);
				$("#popup-validate-message2").show();
				$('#popupeditbox2').css('height', $('#popupeditboxcontent').height());
			} else {
				$("#popup-validate-message2").hide();
			}
			setTimeout(function(){ $('#popupeditbox2').height(''); }, 200);
		}
	});
})
</script>
<?php } ?>
