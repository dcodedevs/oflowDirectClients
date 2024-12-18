<?php
if($moduleAccesslevel > 10)
{
	if(isset($_POST['output_form_submit']))
	{
		if(isset($_POST['set_default_selfdefined_company'])) {
			$o_main->db->query("UPDATE customer SET selfdefined_company_id = '".$o_main->db->escape_str($_POST['selfdefined_company_id'])."' WHERE id = '".$o_main->db->escape_str($_POST['customer_id'])."'");
		} else if(intval($_POST['deleteResource']) > 0) {
			$v_param = array(
				'ID' => $_POST['deleteResource'],
			);

			$s_response = APIconnectorAccount('companyname_selfdefined_delete', $v_accountinfo['accountname'], $v_accountinfo['password'], $v_param);
			$v_response = json_decode($s_response, TRUE);
			if(isset($v_response['status']) && 1 == $v_response['status'])
			{
				$fw_return_data = $_POST['editResource'];
			}
		} else {
			$v_param = array(
				'NAME' => $_POST['resourceName'],
				'STYLE_SET' => $_POST['resourceStyleSet'],
				'GLOBAL_STYLE_SET' => $_POST['resourceGlobalStyleSet'],
				'INVITATION_CONFIG' => $_POST['resourceInvitationConfig'],
				'INVITATION_CONFIG_ACCESSLEVEL' => $_POST['resourceInvitationConfig2'],
				'INVITATION_CONFIG_GROUPID' => $_POST['resourceInvitationConfig3'],
				'INVITATION_CONFIG_CONTENTIDFIELD' => $_POST['resourceInvitationConfig4'],
				'INVITATION_CONFIG_MODULENAME' => $_POST['resourceInvitationConfig5'],
				'FRONTPAGE_CONFIG' => $_POST['resourceFrontpageConfig'],
			);
			if($_POST['editResource'] > 0) $v_param['ID'] = $_POST['editResource'];

			$s_response = APIconnectorAccount('companyname_selfdefined_set', $v_accountinfo['accountname'], $v_accountinfo['password'], $v_param);
			$v_response = json_decode($s_response, TRUE);
			if(isset($v_response['status']) && 1 == $v_response['status'])
			{
				$fw_return_data = $v_response['id'];
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

	<div class="popupformTitle"><?php echo $formText_AddEditSelfDefinedCompany_output;?></div>
	<div class="errorMessage"></div>
	<div class="resourceList" data-action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=edit_othergroup_order";?>"
	data-action2="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=add_selfdefined_company";?>">
	<?php

	$s_response = APIconnectorAccount('companyname_selfdefined_getlist', $v_accountinfo['accountname'], $v_accountinfo['password']);
	$v_response = json_decode($s_response, TRUE);
	if(isset($v_response['status']) && 1 == $v_response['status'])
	foreach($v_response['items'] as $v_item)
	{
		?>
		<div class="resourceRowSortable" id="sort_<?php echo $v_item['id']?>">
			<div class="resourceRow">
				<div class="column nameColumn">
					<div class="columnWrapper"><?php echo $v_item['name']?></div>
				</div>
				<div class="column actionColumn">
					<div class="columnWrapper">
						<ul class="actions">
							<?php if ($moduleAccesslevel > 10): ?>
								<li class="edit">
									<a href="" data-edit-resource-first="<?php echo $v_item['id']; ?>" alt="<?php echo $formText_Edit_output;?>" title="<?php echo $formText_Edit_output;?>">
										<span class="glyphicon glyphicon-edit"></span>
									</a>
								</li>
							<?php endif; ?>

							<?php if ($moduleAccesslevel > 100): ?>
								<li class="delete">
									<a href="" data-delete-resource-first="<?php echo $v_item['id']; ?>" alt="<?php echo $formText_Delete_output;?>" title="<?php echo $formText_Delete_output;?>">
										<span class="glyphicon glyphicon-trash"></span>
									</a>
								</li>
							<?php endif; ?>
						</ul>
					</div>
				</div>
				<div class="clear"></div>
			</div>

			<div class="deleteRow">
				<ul class="actions">
					<li class="delete">
						<a href="" data-delete-resource-id="<?php echo $v_item['id']; ?>" alt="<?php echo $formText_Delete_output;?>" title="<?php echo $formText_Delete_output;?>">
							<?php echo $formText_Delete_output;?>
						</a>
					</li>
					<li class="cancel">
						<a href="" data-delete-cancel="1"><?php echo $formText_Cancel_output;?></a>
					</li>
				</ul>
			</div>
			<div class="editRow">
				<div class="errorRow"></div>
				<div class="editInnerRow">
					<label><?php echo $formText_CompanyName_output?></label>
					<div class="inputBlock">
						<input type="text" name="resourceName" id="resource<?php echo $v_item['id']?>" value="<?php echo $v_item['name']?>" autocomplete="off"/>
					</div>
				</div>
				<div class="editInnerRow">
					<label><?php echo $formText_StyleSet_output?></label>
					<div class="inputBlock">
						<input type="text" name="resourceStyleSet" id="resourceStyleSet<?php echo $v_item['id']?>" value="<?php echo $v_item['style_set']?>" autocomplete="off"/>
					</div>
				</div>
				<div class="editInnerRow">
					<label><?php echo $formText_GlobalStyleSet_output?></label>
					<div class="inputBlock">
						<input type="text" name="resourceGlobalStyleSet" id="resourceGlobalStyleSet<?php echo $v_item['id']?>" value="<?php echo $v_item['global_style_set']?>" autocomplete="off"/>
					</div>
				</div>
				<div class="editInnerRow">
					<label><?php echo $formText_InvitationConfig_output?></label>
					<div class="inputBlock">
						<input type="text" name="resourceInvitationConfig" id="resourceInvitationConfig<?php echo $v_item['id']?>" value="<?php echo $v_item['invitation_config']?>" autocomplete="off"/>
					</div>
				</div>
				<div class="editInnerRow">
					<label><?php echo $formText_InvitationConfigAccessLevel_output?></label>
					<div class="inputBlock">
						<input type="text" name="resourceInvitationConfig2" id="resourceInvitationConfig2<?php echo $v_item['id']?>" value="<?php echo $v_item['invitation_config_accesslevel']?>" autocomplete="off"/>
					</div>
				</div>
				<div class="editInnerRow">
					<label><?php echo $formText_InvitationConfigGroupId_output?></label>
					<div class="inputBlock">
						<input type="text" name="resourceInvitationConfig3" id="resourceInvitationConfig3<?php echo $v_item['id']?>" value="<?php echo $v_item['invitation_config_groupID']?>" autocomplete="off"/>
					</div>
				</div>
				<div class="editInnerRow">
					<label><?php echo $formText_InvitationConfigContentIdField_output?></label>
					<div class="inputBlock">
						<input type="text" name="resourceInvitationConfig4" id="resourceInvitationConfig4<?php echo $v_item['id']?>" value="<?php echo $v_item['invitation_config_contentIdField']?>" autocomplete="off"/>
					</div>
				</div>
				<div class="editInnerRow">
					<label><?php echo $formText_InvitationConfigModuleName_output?></label>
					<div class="inputBlock">
						<input type="text" name="resourceInvitationConfig5" id="resourceInvitationConfig5<?php echo $v_item['id']?>" value="<?php echo $v_item['invitation_config_moduleName']?>" autocomplete="off"/>
					</div>
				</div>
				<div class="editInnerRow">
					<label><?php echo $formText_FrontpageConfig_output?></label>
					<div class="inputBlock">
						<input type="text" name="resourceFrontpageConfig" id="resourceFrontpageConfig<?php echo $v_item['id']?>" value="<?php echo $v_item['frontpage_config']?>" autocomplete="off"/>
					</div>
				</div>
				<div class="actionRow">
					<div class="save" data-resource-save-id="<?php echo $v_item['id']?>"><?php echo $formText_Save_output?></div>
					<div class="cancel" data-resource-save-cancel="<?php echo $v_item['id']?>"><?php echo $formText_Cancel_output?></div>
				</div>
			</div>

		</div>
		<?php
	}
	?>
	</div>
	<div class="newResource editRow">
		<div class="errorRow"></div>
		<div class="editInnerRow">
			<label><?php echo $formText_CompanyName_output?></label>
			<div class="inputBlock">
				<input type="text" name="resourceName" id="resource0" value="" autocomplete="off"/>
			</div>
		</div>
		<div class="editInnerRow">
			<label><?php echo $formText_StyleSet_output?></label>
			<div class="inputBlock">
				<input type="text" name="resourceStyleSet" id="resourceStyleSet0" value="" autocomplete="off"/>
			</div>
		</div>
		<div class="editInnerRow">
			<label><?php echo $formText_GlobalStyleSet_output?></label>
			<div class="inputBlock">
				<input type="text" name="resourceGlobalStyleSet" id="resourceGlobalStyleSet0" value="" autocomplete="off"/>
			</div>
		</div>
		<div class="editInnerRow">
			<label><?php echo $formText_InvitationConfig_output?></label>
			<div class="inputBlock">
				<input type="text" name="resourceInvitationConfig" id="resourceInvitationConfig0" value="" autocomplete="off"/>
			</div>
		</div>
		<div class="editInnerRow">
			<label><?php echo $formText_InvitationConfigAccessLevel_output?></label>
			<div class="inputBlock">
				<input type="text" name="resourceInvitationConfig2" id="resourceInvitationConfig20" value="" autocomplete="off"/>
			</div>
		</div>
		<div class="editInnerRow">
			<label><?php echo $formText_InvitationConfigGroupId_output?></label>
			<div class="inputBlock">
				<input type="text" name="resourceInvitationConfig3" id="resourceInvitationConfig30" value="" autocomplete="off"/>
			</div>
		</div>
		<div class="editInnerRow">
			<label><?php echo $formText_InvitationConfigContentIdField_output?></label>
			<div class="inputBlock">
				<input type="text" name="resourceInvitationConfig4" id="resourceInvitationConfig40" value="" autocomplete="off"/>
			</div>
		</div>
		<div class="editInnerRow">
			<label><?php echo $formText_InvitationConfigModuleName_output?></label>
			<div class="inputBlock">
				<input type="text" name="resourceInvitationConfig5" id="resourceInvitationConfig50" value="" autocomplete="off"/>
			</div>
		</div>
		<div class="editInnerRow">
			<label><?php echo $formText_FrontpageConfig_output?></label>
			<div class="inputBlock">
				<input type="text" name="resourceFrontpageConfig" id="resourceFrontpageConfig0" value="" autocomplete="off"/>
			</div>
		</div>
		<div class="actionRow">
			<div class="save" data-resource-save-id="0"><?php echo $formText_Save_output?></div>
			<div class="cancel" data-resource-save-cancel="0"><?php echo $formText_Cancel_output?></div>
		</div>
	</div>
	<div class="addNew">
		<div class="plusTextBox active">
			<div class="plusBox addres"><div class="lineH"></div><div class="lineV"></div></div>
			<div class="text"><?php echo $formText_AddSelfDefinedCompany_output; ?></div>
		</div>
		<div class="clear"></div>
	</div>


</div>
<script type="text/javascript" src="../modules/<?php echo $module?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
<script type="text/javascript">

var out_popup2;
var out_popup_options2={
	follow: [true, true],
	followSpeed: 300,
	fadeSpeed: 0,
	modalClose: false,
	escClose: false,
	closeClass:'b-close',
	onOpen: function(){
		$(this).addClass('opened');
		//$(this).find('.b-close').on('click', function(){out_popup.close();});
	},
	onClose: function(){
		$(this).removeClass('opened');
		updateSelfdefinedLists();
	}
};
$(function() {
	bindPopupActions();
	function bindPopupActions(){
		// Edit resource
		$("[data-edit-resource-first]").unbind("click").on('click', function(e){
			e.preventDefault();
			$(this).parents(".resourceRow").next(".deleteRow").hide();
			$(this).parents(".resourceRow").next().next(".editRow").show();
			$(window).resize()
		});
		$("[data-resource-save-cancel").unbind("click").on('click', function(e){
			e.preventDefault();
			$(this).parents(".editRow").hide();
			$(window).resize()
		})
		$("[data-resource-save-id").unbind("click").on('click', function(e){
			e.preventDefault();
			var resourceID = $(this).data('resource-save-id'),
				resourceName = $("#resource"+resourceID).val(),
				resourceStyleSet = $("#resourceStyleSet"+resourceID).val(),
				resourceGlobalStyleSet = $('#resourceGlobalStyleSet'+resourceID).val(),
				resourceInvitationConfig = $('#resourceInvitationConfig'+resourceID).val(),
				resourceFrontpageConfig = $('#resourceFrontpageConfig'+resourceID).val(),
				resourceInvitationConfig2 = $('#resourceInvitationConfig2'+resourceID).val(),
				resourceInvitationConfig3 = $('#resourceInvitationConfig3'+resourceID).val(),
				resourceInvitationConfig4 = $('#resourceInvitationConfig4'+resourceID).val(),
				resourceInvitationConfig5 = $('#resourceInvitationConfig5'+resourceID).val(),
				self = $(this);
			var action = $("#popupeditbox .resourceList").data("action2");
			var errorRow = self.parents(".editRow").find(".errorRow");

		if(resourceName != ""){
				errorRow.html("").hide();
				fw_loading_start();
				$.ajax({
					type: 'POST',
					url: action,
					dataType: 'json',
					cache: false,
					data: 'fwajax=1&fw_nocss=1&editResource=' + resourceID + '&output_form_submit=1&resourceName='+resourceName
					+'&resourceStyleSet='+resourceStyleSet+'&resourceGlobalStyleSet='+resourceGlobalStyleSet
					+'&resourceInvitationConfig='+resourceInvitationConfig+'&resourceInvitationConfig2='+resourceInvitationConfig2+
					'&resourceInvitationConfig3='+resourceInvitationConfig3+'&resourceInvitationConfig4='+resourceInvitationConfig4+
					'&resourceInvitationConfig5='+resourceInvitationConfig5+'&resourceFrontpageConfig='+resourceFrontpageConfig,
					success: function(result){
						if(parseInt(result.html) > 0){
							$.ajax({
								type: 'POST',
								url: action,
								dataType: 'json',
								cache: false,
								data: {fwajax: 1, fw_nocss: 1, cid: 0},
								success: function(obj){
									fw_loading_end();
									$('#popupeditboxcontent').html('');
									$('#popupeditboxcontent').html(obj.html);
									out_popup = $('#popupeditbox').bPopup(out_popup_options);
									$("#popupeditbox:not(.opened)").remove();
								}
							});
						} else {
							fw_loading_end();
							$(".popupform .errorMessage").html("<?php echo $formText_ErrorSavingResource_output;?>").show();
						}
					}
				});
			} else {
				fw_loading_end();
				errorRow.html("<?php echo $formText_PleaseFillInSelfdefinedCompanyName_output;?>").show();
			}
		})

		// Delete resource
		$("[data-delete-resource-first").unbind("click").on('click', function(e){
			e.preventDefault();
			$(this).parents(".resourceRow").next().next(".editRow").hide();
			$(this).parents(".resourceRow").next(".deleteRow").show();
			$(window).resize()
		})
		$("[data-delete-cancel").unbind("click").on('click', function(e){
			e.preventDefault();
			$(this).parents(".deleteRow").hide();
			$(window).resize()
		})
		$("[data-delete-resource-id]").unbind("click").on('click', function(e){
			e.preventDefault();
			fw_loading_start();
			var resourceName = $(this).parents(".resourceRow").find(".nameColumn .columnWrapper").html();
			var self = $(this);
	        var action = $("#popupeditbox .resourceList").data("action2");
			$.ajax({
				type: 'POST',
				url: action,
				dataType: 'json',
				cache: false,
				data: 'output_form_submit=1&fwajax=1&fw_nocss=1&deleteResource=' + $(this).data('delete-resource-id'),
				success: function(result){
					fw_loading_end();
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
			$(window).resize()
		});
	}
});
</script>
<style>
.showInListWrapper {
	display: none;
}
.showInListWrapper.active {
	display: block;
}
.editList {
	display: inline-block;
	vertical-align: middle;
	margin-left: 10px;
	float: none;
	cursor: pointer;
}
.addMoreDropdown {
	display: inline-block;
	vertical-align: middle;
	float: none;
	cursor: pointer;
}
.editInnerRow {
	margin: 10px 0px;
}
.editInnerRow label {
	display: inline-block !important;
	width: 20%;
}
.editInnerRow .inputBlock {
	display: inline-block;
	width: 60%;
}
.errorRow {
	margin-bottom: 10px;
	display: none;
	color: red;
}
.listBlock {
	display: none;
}
.listBlock.active {
	display: block;
}
.listBlockCheckbox {
	display: none;
}
.listBlockCheckbox.active {
	display: block;
}
.actionRow {
	margin-top: 10px;
}
.editCheckbox {
	width: auto !important;
	margin-right: 0px !important;
	margin-top: 0px !important;
	vertical-align: middle;
}
.hiddenTextfieldWrapper {
	display: none;
}
.popupform, .popupeditform {
	width:100%;
	margin:0 auto;
	position:relative;
	border: 0;
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
	margin-left: 20px;
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
</style>
