<?php
$s_sql = "SELECT * FROM customer_basisconfig ORDER BY id DESC";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
    $customer_basisconfig = $o_query->row_array();
}
$s_sql = "SELECT * FROM customer_accountconfig ORDER BY id DESC";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
    $v_customer_accountconfig = $o_query->row_array();
}

if($v_customer_accountconfig['intranet_membership_object_table'] != "")
{
	$object_table = $v_customer_accountconfig['intranet_membership_object_table'];
} else {
	$object_table =  $customer_basisconfig['intranet_membership_object_table'];
}
if($v_customer_accountconfig['intranet_membership_objectgroup_table'] != "")
{
	$objectgroup_table = $v_customer_accountconfig['intranet_membership_objectgroup_table'];
} else {
	$objectgroup_table =  $customer_basisconfig['intranet_membership_objectgroup_table'];
}

$intranet_objects = array();
$intranet_objectgroups = array();

$s_sql = "SELECT * FROM ".$object_table." WHERE content_status < 2 ORDER BY id ASC";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0) {
	$intranet_objects = $o_query->result_array();
}
$s_sql = "SELECT * FROM ".$objectgroup_table." WHERE content_status < 2 ORDER BY id ASC";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0) {
	$intranet_objectgroups = $o_query->result_array();
}

if($moduleAccesslevel > 10)
{
	if(isset($_POST['output_form_submit']))
	{
		if($_POST['editSubSub']){
			$objectId = intval($_POST['objectId']);
			$setting_modulename = $_POST['modulename'];
			$setting_accesslevel = $_POST['accesslevel'];
			if(isset($_POST['editResource']) && $_POST['editResource'] > 0) {
				if($setting_modulename !="" && $setting_accesslevel != "") {
					$s_sql = "UPDATE intranet_membership_attached_object_setting SET
					updated = now(),
					updatedBy= ?,
					intranet_membership_attached_object_id= ?,
					module_name = ?,
					access_level = ?
					WHERE id = ?";
					$o_query = $o_main->db->query($s_sql, array($variables->loggID, $objectId, $setting_modulename, $setting_accesslevel, $_POST['editResource']));

					$fw_return_data = $_POST['editResource'];
				}
			}
			else if(intval($_POST['deleteResource']) == 0) {
				if($setting_modulename !="" && $setting_accesslevel != "") {
					$s_sql = "INSERT INTO intranet_membership_attached_object_setting SET
					id=NULL,
					moduleID = ?,
					created = now(),
					createdBy= ?,
					intranet_membership_attached_object_id= ?,
					module_name = ?,
					access_level = ?";
					$o_query = $o_main->db->query($s_sql, array($moduleID, $variables->loggID, $objectId, $setting_modulename, $setting_accesslevel));

					$fw_return_data = $o_main->db->insert_id();
				}
			} else {
				$s_sql = "DELETE intranet_membership_attached_object_setting FROM intranet_membership_attached_object_setting WHERE intranet_membership_attached_object_setting.id = ?";
				$o_query = $o_main->db->query($s_sql, array($_POST['deleteResource']));
				$fw_return_data = $_POST['deleteResource'];
			}
		} else if($_POST['editSub']){
			$membershipId = intval($_POST['membershipId']);
			$objectId = intval($_POST['objectId']);
			$objectgroupId = intval($_POST['objectgroupId']);
			if($_POST['editResource'] > 0) {
				if($membershipId > 0 && ($objectId > 0 || $objectgroupId)) {
					$s_sql = "UPDATE intranet_membership_attached_object SET
					updated = now(),
					updatedBy= ?,
					membership_id= ?,
					object_id = ?,
					objectgroup_id = ?
					WHERE id = ?";
					$o_query = $o_main->db->query($s_sql, array($variables->loggID, $membershipId, $objectId, $objectgroupId, $_POST['editResource']));

					$fw_return_data = $_POST['editResource'];
				}
			}
			else if(intval($_POST['deleteResource']) == 0) {
				if($membershipId > 0 && ($objectId > 0 || $objectgroupId)) {
					$s_sql = "INSERT INTO intranet_membership_attached_object SET
					id=NULL,
					moduleID = ?,
					created = now(),
					createdBy= ?,
					membership_id= ?,
					object_id = ?,
					objectgroup_id = ?";
					$o_query = $o_main->db->query($s_sql, array($moduleID, $variables->loggID, $membershipId, $objectId, $objectgroupId));

					$fw_return_data = $o_main->db->insert_id();
				}
			} else {
				$s_sql = "DELETE intranet_membership_attached_object FROM intranet_membership_attached_object WHERE intranet_membership_attached_object.id = ?";
				$o_query = $o_main->db->query($s_sql, array($_POST['deleteResource']));
				$fw_return_data = $_POST['deleteResource'];
			}
		} else {
			if($_POST['editResource'] > 0) {
				$s_sql = "UPDATE intranet_membership SET
				updated = now(),
				updatedBy= ?,
				name= ?
				WHERE id = ?";
				$o_main->db->query($s_sql, array($variables->loggID, $_POST['resourceName'], $_POST['editResource']));
				$fw_return_data = $_POST['editResource'];
			}
			else if(intval($_POST['deleteResource']) == 0) {
				$s_sql = "INSERT INTO intranet_membership SET
				id=NULL,
				moduleID = ?,
				created = now(),
				createdBy= ?,
				name= ?";
				$o_main->db->query($s_sql, array($moduleID, $variables->loggID, $_POST['resourceName']));
				$fw_return_data = $o_main->db->insert_id();
			} else {
				// $s_sql = "SELECT * FROM subscriptionmulti WHERE subscriptiontypeId = ?";
				// $o_query = $o_main->db->query($s_sql, array($_POST['deleteResource']));
				// if($o_query && $o_query->num_rows() == 0) {
					$s_sql = "DELETE intranet_membership FROM intranet_membership WHERE intranet_membership.id = ?";
					$o_main->db->query($s_sql, array($_POST['deleteResource']));
				// }
				$fw_return_data = $_POST['deleteResource'];
			}
		}

		echo $fw_return_data;
		$fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=list";
		return;
	}
}
?>
<div class="popupform">
	<div id="popup-validate-message" style="display:none;"></div>

	<div class="popupformTitle"><?php echo $formText_AddEditSubscriptionType_output;?> <?php echo $resource['name']?></div>
	<div class="errorMessage"></div>
	<div class="resourceList" data-action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=add_intranet_membership";?>"
	data-action2="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=add_intranet_membership";?>">
	<?php
	$resources = array();
	$s_sql = "SELECT * FROM intranet_membership WHERE content_status < 2 ORDER BY name ASC";
	$o_query = $o_main->db->query($s_sql);
	if($o_query && $o_query->num_rows()>0) {
	    $resources = $o_query->result_array();
	}

	foreach($resources as $resource){
			$s_sql = "SELECT imao.*, IFNULL(".$object_table.".name, ".$objectgroup_table.".name) as objectName FROM intranet_membership_attached_object imao
			LEFT OUTER JOIN ".$object_table." ON ".$object_table.".id = imao.object_id
			LEFT OUTER JOIN ".$objectgroup_table." ON ".$objectgroup_table.".id = imao.objectgroup_id
			WHERE imao.membership_id = ?";
			$o_query = $o_main->db->query($s_sql, array($resource['id']));
			$attached_objects = ($o_query ? $o_query->result_array() : array());
		?>
		<div class="resourceRowSortable" id="sort_<?php echo $resource['id']?>">
			<div class="resourceRow">
				<div class="column nameColumn">
					<div class="columnWrapper"><?php echo $resource['name']?></div>
				</div>
				<div class="column">
					<div class="columnWrapper">
						<div class="addObject">
							<div class="plusTextBox active">
								<div class="plusBox addres"><div class="lineH"></div><div class="lineV"></div></div>
								<div class="text"><?php echo $formText_AddObject_output;?></div>
							</div>
							<div class="clear"></div>
						</div>
					</div>
				</div>
                <div class="column">
					<div class="columnWrapper">
						<div class="addObjectGroup">
							<div class="plusTextBox active">
								<div class="plusBox addres"><div class="lineH"></div><div class="lineV"></div></div>
								<div class="text"><?php echo $formText_AddObjectGroup_output;?></div>
							</div>
							<div class="clear"></div>
						</div>
					</div>
				</div>
				<div class="column actionColumn">
					<div class="columnWrapper">
						<?php if(!$resource['disabledForEditing']) { ?>
							<ul class="actions">
	                            <?php if ($moduleAccesslevel > 10): ?>
	                                <?php if (!$owneraccess || ($owneraccess && $_GET['userID'] == $resource['createdBy'])): ?>
	    								<li class="edit">
	    									<a href="" data-edit-resource-first="<?php echo $resource['id']; ?>" alt="<?php echo $formText_Edit_output;?>" title="<?php echo $formText_Edit_output;?>">
	    										<span class="glyphicon glyphicon-edit"></span>
	    									</a>
	    								</li>
	                                <?php endif; ?>
	                            <?php endif; ?>

	                            <?php if ($moduleAccesslevel > 100):
	                            	// $s_sql = "SELECT * FROM subscriptionmulti WHERE subscriptiontypeId = ?";
									// $o_query = $o_main->db->query($s_sql, array($resource['id']));
									// if($o_query && $o_query->num_rows() == 0) {
		                            	 ?>
		                                <?php if (!$owneraccess || ($owneraccess && $_GET['userID'] == $resource['createdBy'])): ?>
		    								<li class="delete">
		    									<a href="" data-delete-resource-first="<?php echo $resource['id']; ?>" alt="<?php echo $formText_Delete_output;?>" title="<?php echo $formText_Delete_output;?>">
		    										<span class="glyphicon glyphicon-trash"></span>
		    									</a>
		    								</li>
		                                <?php endif; ?>
		                            <?php //} ?>
	                            <?php endif; ?>
							</ul>
						<?php } ?>
					</div>
				</div>
				<div class="clear"></div>
				<div class="objectBlock">
				<?php foreach($attached_objects as $attached_object) {

					$s_sql = "SELECT imaos.* FROM intranet_membership_attached_object_setting imaos
					WHERE imaos.intranet_membership_attached_object_id = ?";
					$o_query = $o_main->db->query($s_sql, array($attached_object['id']));
					$attached_object_settings = ($o_query ? $o_query->result_array() : array());

					?>
					<div class="objectRow">
						<div class="column nameColumn">
							<div class="columnWrapper">
								<?php echo $attached_object['objectName']?>
							</div>
						</div>
						<div class="column">
							<div class="columnWrapper">
								<div class="addObjectSetting">
									<div class="plusTextBox active">
										<div class="plusBox addres"><div class="lineH"></div><div class="lineV"></div></div>
										<div class="text"><?php echo $formText_AddSetting_output;?></div>
									</div>
									<div class="clear"></div>
								</div>
							</div>
						</div>
						<div class="column actionColumn">
							<div class="columnWrapper">
								<?php if(!$resource['disabledForEditing']) { ?>
									<ul class="actions">
			                            <?php if ($moduleAccesslevel > 10): ?>
			                                <?php if (!$owneraccess || ($owneraccess && $_GET['userID'] == $attached_object['createdBy'])): ?>
			    								<li class="edit">
			    									<a href="" data-edit-object-first="<?php echo $attached_object['id']; ?>" alt="<?php echo $formText_Edit_output;?>" title="<?php echo $formText_Edit_output;?>">
			    										<span class="glyphicon glyphicon-edit"></span>
			    									</a>
			    								</li>
			                                <?php endif; ?>
			                            <?php endif; ?>

			                            <?php if ($moduleAccesslevel > 100 ): ?>
			                                <?php if (!$owneraccess || ($owneraccess && $_GET['userID'] == $attached_object['createdBy'])): ?>
			    								<li class="delete">
			    									<a href="" data-delete-object-first="<?php echo $attached_object['id']; ?>" alt="<?php echo $formText_Delete_output;?>" title="<?php echo $formText_Delete_output;?>">
			    										<span class="glyphicon glyphicon-trash"></span>
			    									</a>
			    								</li>
			                                <?php endif; ?>
			                            <?php endif; ?>
									</ul>
								<?php } ?>
							</div>
						</div>
						<div class="clear"></div>
						<div class="objectSettingBlock">
						<?php foreach($attached_object_settings as $attached_object_setting) { ?>
							<div class="objectSettingRow">
								<div class="column">
									<div class="columnWrapper">
										<?php echo $attached_object_setting['module_name'] . " - ".$attached_object_setting['access_level']?>
									</div>
								</div>
								<div class="column actionColumn">
									<div class="columnWrapper">
										<?php if(!$resource['disabledForEditing']) { ?>
											<ul class="actions">
					                            <?php if ($moduleAccesslevel > 10): ?>
					                                <?php if (!$owneraccess || ($owneraccess && $_GET['userID'] == $attached_object_setting['createdBy'])): ?>
					    								<li class="edit">
					    									<a href="" data-edit-objectsetting-first="<?php echo $attached_object_setting['id']; ?>" alt="<?php echo $formText_Edit_output;?>" title="<?php echo $formText_Edit_output;?>">
					    										<span class="glyphicon glyphicon-edit"></span>
					    									</a>
					    								</li>
					                                <?php endif; ?>
					                            <?php endif; ?>

					                            <?php if ($moduleAccesslevel > 100 ): ?>
					                                <?php if (!$owneraccess || ($owneraccess && $_GET['userID'] == $attached_object_setting['createdBy'])): ?>
					    								<li class="delete">
					    									<a href="" data-delete-objectsetting-first="<?php echo $attached_object_setting['id']; ?>" alt="<?php echo $formText_Delete_output;?>" title="<?php echo $formText_Delete_output;?>">
					    										<span class="glyphicon glyphicon-trash"></span>
					    									</a>
					    								</li>
					                                <?php endif; ?>
					                            <?php endif; ?>
											</ul>
										<?php } ?>
									</div>
								</div>
								<div class="clear"></div>
								<div class="deleteRow">
									<ul class="actions">
										<li class="delete">
											<a href="" data-delete-objectsetting-id="<?php echo $attached_object_setting['id']; ?>" alt="<?php echo $formText_Delete_output;?>" title="<?php echo $formText_Delete_output;?>">
												<?php echo $formText_Delete_output;?>
											</a>
										</li>
										<li class="cancel">
											<a href="" data-delete-cancel="1"><?php echo $formText_Cancel_output;?></a>
										</li>
									</ul>
								</div>
								<div class="editRow">
									<div style="margin-bottom: 5px;">
										<label class="namelabel"><?php echo $formText_ModuleName_output;?></label><input type="text" id="objectSetting<?php echo $attached_object['id']?><?php echo $attached_object_setting['id']?>" value="<?php echo $attached_object_setting['module_name']?>" autocomplete="off"/>
									</div>
									<div>
										<label class="namelabel"><?php echo $formText_AccessLevel_output;?></label><input type="text" id="objectSettingAccess<?php echo $attached_object['id']?><?php echo $attached_object_setting['id']?>" value="<?php echo $attached_object_setting['access_level']?>" autocomplete="off"/>
									</div>
									<div class="save" data-objectsetting-save-id="<?php echo $attached_object_setting['id']?>" data-object-id="<?php echo $attached_object['id']?>"><?php echo $formText_Save_output?></div>
									<div class="cancel" data-objectsetting-save-cancel="<?php echo $attached_object_setting['id']?>"><?php echo $formText_Cancel_output?></div>
								</div>
							</div>
						<?php } ?>
							<div class="newObjectSetting editRow">
								<div style="margin-bottom: 5px;">
									<label class="namelabel"><?php echo $formText_ModuleName_output;?></label><input type="text" id="objectSetting<?php echo $attached_object['id']?>0" value="" autocomplete="off"/>
								</div>
								<div>
									<label class="namelabel"><?php echo $formText_AccessLevel_output;?></label><input type="text" id="objectSettingAccess<?php echo $attached_object['id']?>0" value="" autocomplete="off"/>
								</div>
								<div class="save" data-objectsetting-save-id="0" data-object-id="<?php echo $attached_object['id']?>"><?php echo $formText_Save_output?></div>
								<div class="cancel" data-objectsetting-save-cancel="0"><?php echo $formText_Cancel_output?></div>
							</div>
						</div>
						<div class="deleteRow">
							<ul class="actions">
								<li class="delete">
									<a href="" data-delete-object-id="<?php echo $attached_object['id']; ?>" alt="<?php echo $formText_Delete_output;?>" title="<?php echo $formText_Delete_output;?>">
										<?php echo $formText_Delete_output;?>
									</a>
								</li>
								<li class="cancel">
									<a href="" data-delete-cancel="1"><?php echo $formText_Cancel_output;?></a>
								</li>
							</ul>
						</div>
						<div class="editRow">
                            <?php if($attached_object['object_id'] > 0){?>
    							<select name="objectId" id="object<?php echo $resource['id']?><?php echo $attached_object['id']?>">
    								<option value=""><?php echo $formText_SelectObject_output; ?></option>
    							<?php foreach($intranet_objects as $intranet_object) { ?>
    								<option value="<?php echo $intranet_object['id']?>" <?php if($intranet_object['id'] == $attached_object['object_id']) echo 'selected';?>><?php echo $intranet_object['name']?></option>
    							<?php } ?>
    							</select>
                            <?php } else { ?>
    							<select name="objectgroupId" id="objectgroup<?php echo $resource['id']?><?php echo $attached_object['id']?>">
    								<option value=""><?php echo $formText_SelectObjectGroup_output; ?></option>
    							<?php foreach($intranet_objectgroups as $intranet_objectgroup) { ?>
    								<option value="<?php echo $intranet_objectgroup['id']?>" <?php if($intranet_objectgroup['id'] == $attached_object['objectgroup_id']) echo 'selected';?>><?php echo $intranet_objectgroup['name']?></option>
    							<?php } ?>
    							</select>
                            <?php } ?>
							<div class="save" data-object-save-id="<?php echo $attached_object['id']?>" data-membership-id="<?php echo $resource['id']?>"><?php echo $formText_Save_output?></div>
							<div class="cancel" data-object-save-cancel="<?php echo $attached_object['id']?>"><?php echo $formText_Cancel_output?></div>
						</div>
					</div>
				<?php } ?>
					<div class="newObject editRow">
						<select class="objectSelect" name="objectId" id="object<?php echo $resource['id']?>0">
							<option value=""><?php echo $formText_SelectObject_output; ?></option>
						<?php foreach($intranet_objects as $intranet_object) { ?>
							<option value="<?php echo $intranet_object['id']?>"><?php echo $intranet_object['name']?></option>
						<?php } ?>
						</select>

						<select class="objectGroupSelect" name="objectgroupId" id="objectgroup<?php echo $resource['id']?>0">
							<option value=""><?php echo $formText_SelectObjectGroup_output; ?></option>
						<?php foreach($intranet_objectgroups as $intranet_objectgroup) { ?>
							<option value="<?php echo $intranet_objectgroup['id']?>"><?php echo $intranet_objectgroup['name']?></option>
						<?php } ?>
						</select>
						<div class="save" data-object-save-id="0" data-membership-id="<?php echo $resource['id']?>"><?php echo $formText_Save_output?></div>
						<div class="cancel" data-object-save-cancel="0"><?php echo $formText_Cancel_output?></div>
					</div>
				</div>
			</div>
			<?php if(!$resource['disabledForEditing']) { ?>
				<div class="deleteRow">
					<ul class="actions">
						<li class="delete">
							<a href="" data-delete-resource-id="<?php echo $resource['id']; ?>" alt="<?php echo $formText_Delete_output;?>" title="<?php echo $formText_Delete_output;?>">
								<?php echo $formText_Delete_output;?>
							</a>
						</li>
						<li class="cancel">
							<a href="" data-delete-cancel="1"><?php echo $formText_Cancel_output;?></a>
						</li>
					</ul>
				</div>
				<div class="editRow">
					<input type="text" name="resourceName" id="resource<?php echo $resource['id']?>" value="<?php echo $resource['name']?>" autocomplete="off"/>

					<div class="save" data-resource-save-id="<?php echo $resource['id']?>"><?php echo $formText_Save_output?></div>
					<div class="cancel" data-resource-save-cancel="<?php echo $resource['id']?>"><?php echo $formText_Cancel_output?></div>
				</div>
			<?php } ?>

		</div>
		<?php
	}
	?>
	</div>
	<div class="newResource editRow">
		<input type="text" name="resourceName" id="resource0" value="" autocomplete="off"/>
		<div class="save" data-resource-save-id="0"><?php echo $formText_Save_output?></div>
		<div class="cancel" data-resource-save-cancel="0"><?php echo $formText_Cancel_output?></div>
	</div>
	<div class="addNew">
		<div class="plusTextBox active">
			<div class="plusBox addres"><div class="lineH"></div><div class="lineV"></div></div>
			<div class="text"><?php echo $formText_AddIntranetMembership_output; ?></div>
		</div>
		<div class="clear"></div>
	</div>

	<!-- <div class="explanation"><?php echo $formText_DragAndDropToChangeOrder_output;?></div> -->
</div>

<script type="text/javascript" src="../modules/<?php echo $module?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
<script type="text/javascript">
$(function() {
	resizePopupEdit();
	function resizePopupEdit(){

	}
	$(window).resize(resizePopupEdit);
	bindPopupActions();
	function bindPopupActions(){
		// $(".resourceList").sortable({
		// 	update: function(event, ui) {
		//         var info = $(this).sortable("serialize");
		//         var action = $(this).data("action");
		//         $.ajax({
		// 			type: 'POST',
		// 			url: action,
		// 			data: info,
		// 			success: function(data){
		// 				var result = $.parseJSON(data);
		// 				// success
		// 				if(result.result == 1){
		// 					$(".popupform .errorMessage").hide();
		// 				} else {
		// 					$(".popupform .errorMessage").html("<?php echo $formText_ErrorChangingResourceOrder_output;?>").show();
		// 				}
		// 			}
		// 		});
		//     }
		// });
		// Edit resource
		$("[data-edit-resource-first]").unbind("click").on('click', function(e){
			e.preventDefault();
			$(this).parents(".resourceRow").next(".deleteRow").hide();
			$(this).parents(".resourceRow").next().next(".editRow").show();
		});
		$("[data-resource-save-cancel]").unbind("click").on('click', function(e){
			e.preventDefault();
			$(this).parents(".editRow").hide();
		})
		$("[data-resource-save-id]").unbind("click").on('click', function(e){
			e.preventDefault();
			var resourceID = $(this).data('resource-save-id'),
				resourceName = $("#resource"+resourceID).val(),
				self = $(this);
		        var action = $(".resourceList").data("action2");
			$.ajax({
				type: 'POST',
				url: action,
				cache: false,
				data: 'fwajax=1&fw_nocss=1&editResource=' + resourceID + '&output_form_submit=1&resourceName='+resourceName,
				success: function(result){
					if(parseInt(result.html) > 0){
						ajaxCall('add_intranet_membership', {}, function(obj) {
				            $('#popupeditboxcontent').html('');
				            $('#popupeditboxcontent').html(obj.html);
				            out_popup = $('#popupeditbox').bPopup(out_popup_options);
				            $("#popupeditbox:not(.opened)").remove();
				        });
					} else {
						$(".popupform .errorMessage").html("<?php echo $formText_ErrorSavingResource_output;?>").show();
					}
				}
			});
		})

		// Delete resource
		$("[data-delete-resource-first]").unbind("click").on('click', function(e){
			e.preventDefault();
			$(this).parents(".resourceRow").next().next(".editRow").hide();
			$(this).parents(".resourceRow").next(".deleteRow").show();
		})
		$("[data-delete-cancel]").unbind("click").on('click', function(e){
			e.preventDefault();
			$(this).parents(".deleteRow").hide();
		})
		$("[data-delete-resource-id]").unbind("click").on('click', function(e){
			e.preventDefault();
			var resourceName = $(this).parents(".resourceRow").find(".nameColumn .columnWrapper").html();
			var self = $(this);
	        var action = $(".resourceList").data("action2");
			$.ajax({
				type: 'POST',
				url: action,
				cache: false,
				data: 'output_form_submit=1&fwajax=1&fw_nocss=1&deleteResource=' + $(this).data('delete-resource-id'),
				success: function(result){
					if(parseInt(result.html) != 0){
						var deleteRow = self.parents(".deleteRow");
						deleteRow.hide();
						deleteRow.prev('.resourceRow').remove();
						$(".popupform .errorMessage").hide();
					} else {
						$(".popupform .errorMessage").html("<?php echo $formText_ErrorDeletingResource_output;?>").show();
					}
				}
			});
		});
		$(".popupform .addNew").unbind("click").bind("click", function(){
			$(".newResource").show();
		})
		$(".popupform .addObject").unbind("click").bind("click", function(){
			$(this).parents(".resourceRow").find(".newObject").show();
            $(this).parents(".resourceRow").find(".newObject").find(".objectGroupSelect").hide();
            $(this).parents(".resourceRow").find(".newObject").find(".objectSelect").show();
		})
		$(".popupform .addObjectGroup").unbind("click").bind("click", function(){
			$(this).parents(".resourceRow").find(".newObject").show();
            $(this).parents(".resourceRow").find(".newObject").find(".objectGroupSelect").show();
            $(this).parents(".resourceRow").find(".newObject").find(".objectSelect").hide();
		})
		$(".popupform .addObjectSetting").unbind("click").bind("click", function(){
			$(this).parents(".objectRow").find(".newObjectSetting").show();
		})


		$("[data-edit-object-first]").unbind("click").on('click', function(e){
			e.preventDefault();
			$(this).parents(".objectRow").find(".deleteRow").last().hide();
			$(this).parents(".objectRow").find(".editRow").last().show();
		});
		$("[data-object-save-cancel]").unbind("click").on('click', function(e){
			e.preventDefault();
			$(".popupform .errorMessage").hide();
			$(this).parents(".editRow").hide();
		})

		$("[data-object-save-id]").unbind("click").on('click', function(e){
			e.preventDefault();
			var resourceID = $(this).data('object-save-id'),
				membershipId = $(this).data('membership-id'),
				objectId = $("#object"+membershipId+resourceID).val(),
				objectgroupId = $("#objectgroup"+membershipId+resourceID).val(),
				self = $(this);
	        var action = $(".resourceList").data("action2");
			if(objectId > 0 || objectgroupId > 0){
				fw_loading_start();
				$.ajax({
					type: 'POST',
					url: action,
					cache: false,
					dataType: 'json',
					data: 'fwajax=1&fw_nocss=1&editResource=' + resourceID + '&membershipId='+membershipId+'&editSub=1&output_form_submit=1&objectId='+objectId+'&objectgroupId='+objectgroupId,
					success: function(result){
						fw_loading_end();
						if(parseInt(result.html) > 0){
							ajaxCall('add_intranet_membership', {}, function(obj) {
					            $('#popupeditboxcontent').html('');
					            $('#popupeditboxcontent').html(obj.html);
					            out_popup = $('#popupeditbox').bPopup(out_popup_options);
					            $("#popupeditbox:not(.opened)").remove();
					        });
						} else {
							$(".popupform .errorMessage").html("<?php echo $formText_ErrorSavingResource_output;?>").show();
						}
					}
				});
			} else {
				$(".popupform .errorMessage").html("<?php echo $formText_SelectObjectOrObjectGroup_output;?>").show();
			}
		})

		// Delete resource
		$("[data-delete-object-first").unbind("click").on('click', function(e){
			e.preventDefault();
			$(this).parents(".objectRow").find(".editRow").last().hide();
			$(this).parents(".objectRow").find(".deleteRow").last().show();
		})
		$("[data-delete-cancel").unbind("click").on('click', function(e){
			e.preventDefault();
			$(this).parents(".deleteRow").hide();
		})
		$("[data-delete-object-id]").unbind("click").on('click', function(e){
			e.preventDefault();
			var self = $(this);
	        var action = $(".resourceList").data("action2");
			fw_loading_start();
			$.ajax({
				type: 'POST',
				url: action,
				cache: false,
				dataType: 'json',
				data: 'output_form_submit=1&fwajax=1&fw_nocss=1&editSub=1&deleteResource=' + $(this).data('delete-object-id'),
				success: function(result){
					fw_loading_end();
					if(parseInt(result.html) != 0){
						var deleteRow = self.parents(".deleteRow");
						deleteRow.hide();
						deleteRow.parents('.objectRow').remove();
						$(".popupform .errorMessage").hide();
					} else {
						$(".popupform .errorMessage").html("<?php echo $formText_ErrorDeletingResource_output;?>").show();
					}
				}
			});
		});

		$("[data-edit-objectsetting-first]").unbind("click").on('click', function(e){
			e.preventDefault();
			$(this).parents(".objectSettingRow").find(".deleteRow").hide();
			$(this).parents(".objectSettingRow").find(".editRow").show();
		});
		$("[data-objectsetting-save-cancel]").unbind("click").on('click', function(e){
			e.preventDefault();
			$(".popupform .errorMessage").hide();
			$(this).parents(".editRow").hide();
		})

		$("[data-objectsetting-save-id]").unbind("click").on('click', function(e){
			e.preventDefault();
			var resourceID = $(this).data('objectsetting-save-id'),
				objectId = $(this).data('object-id'),
				modulename = $("#objectSetting"+objectId+resourceID).val(),
				accesslevel = $("#objectSettingAccess"+objectId+resourceID).val(),
				self = $(this);
	        var action = $(".resourceList").data("action2");
			if(accesslevel != "" && modulename != ""){
				fw_loading_start();
				$.ajax({
					type: 'POST',
					url: action,
					cache: false,
					dataType: 'json',
					data: 'fwajax=1&fw_nocss=1&editResource=' + resourceID + '&objectId='+objectId+'&editSubSub=1&output_form_submit=1&modulename='+modulename+'&accesslevel='+accesslevel,
					success: function(result){
						fw_loading_end();
						if(parseInt(result.html) > 0){
							ajaxCall('add_intranet_membership', {}, function(obj) {
					            $('#popupeditboxcontent').html('');
					            $('#popupeditboxcontent').html(obj.html);
					            out_popup = $('#popupeditbox').bPopup(out_popup_options);
					            $("#popupeditbox:not(.opened)").remove();
					        });
						} else {
							$(".popupform .errorMessage").html("<?php echo $formText_ErrorSavingResource_output;?>").show();
						}
					}
				});
			} else {
				$(".popupform .errorMessage").html("<?php echo $formText_FillInModuleNameAndAccessLevel_output;?>").show();
			}
		})

		// Delete resource
		$("[data-delete-objectsetting-first").unbind("click").on('click', function(e){
			e.preventDefault();
			$(this).parents(".objectSettingRow").find(".editRow").hide();
			$(this).parents(".objectSettingRow").find(".deleteRow").show();
		})
		$("[data-delete-cancel").unbind("click").on('click', function(e){
			e.preventDefault();
			$(this).parents(".deleteRow").hide();
		})
		$("[data-delete-objectsetting-id]").unbind("click").on('click', function(e){
			e.preventDefault();
			var self = $(this);
	        var action = $(".resourceList").data("action2");
			fw_loading_start();
			$.ajax({
				type: 'POST',
				url: action,
				cache: false,
				dataType: 'json',
				data: 'output_form_submit=1&fwajax=1&fw_nocss=1&editSubSub=1&deleteResource=' + $(this).data('delete-objectsetting-id'),
				success: function(result){
					fw_loading_end();
					if(parseInt(result.html) != 0){
						var deleteRow = self.parents(".deleteRow");
						deleteRow.hide();
						deleteRow.parents('.objectSettingRow').remove();
						$(".popupform .errorMessage").hide();
					} else {
						$(".popupform .errorMessage").html("<?php echo $formText_ErrorDeletingResource_output;?>").show();
					}
				}
			});
		});

	}
});
</script>
<style>
.resourceRow .nameColumn {
	width: 45%;
}
.addObject {
	cursor: pointer;
}
.addObjectGroup {
    cursor: pointer;
    margin-left: 20px;
}
.addObjectSetting {
	cursor: pointer;
}
.columnInputWrapper {
	margin: 5px 0px;
}
.columnWrapper label {
	margin-right: 10px;
	margin-bottom: 0;
}
.addWorkLeader {
	cursor: pointer;
}
.workleaderBlock {
	margin-left: 10px;
}
.popupform, .popupeditform {
	width:100%;
	margin:0 auto;
	position:relative;
	border: 0;
}
.popupform .addNew {
	margin-left: 20px;
}
.namelabel {
	width: 20%;
	display: inline-block !important;
}
label.error { display: none !important; }
input.error { border-color:#c11; }
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
.popupform input.popupforminput, .popupform textarea.popupforminput, .col-md-8z input {
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
	border-radius: 4px;
	border:0px none;
	background-color:#0393ff;
	font-size:13px;
	line-height:0px;
	padding: 20px 35px;
	font-weight:700;
	color:#FFF;
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
.addNew {
	cursor: pointer;
}
.addNew .plusTextBox {
	float: none;
}
.subDepartments {
	margin-left: 30px;
}
.actions {
	margin-left: 0;
	padding-left: 0;
}
.objectBlock {
	margin-left: 10px;
}
.objectSettingBlock {
	margin-left: 20px;
}
.popupform .errorMessage {
	color: red;
}
</style>
