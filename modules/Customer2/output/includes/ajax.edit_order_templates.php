<?php
if($moduleAccesslevel > 10)
{
	if(isset($_POST['output_form_submit']))
	{
		$showInPopup = 0;
		if($_POST['showInPopup'] == "true"){
			$showInPopup = 1;
		}
		$showInList = 0;
		if($_POST['showInList'] == "true"){
			$showInList = 1;
		}
		$hideTextfield = 0;
		if($_POST['hideTextfield'] == "true"){
			$hideTextfield = 1;
		}
		$listIdPost = $_POST['list_id'];
		if(is_array($_POST['list_id'])){
			$listIdPost = 0;
		}
		if($_POST['editResource'] > 0) {
			$s_sql = "UPDATE customer_collectingorder_confirmations_templates SET
			updated = now(),
			updatedBy= ?,
			name=?,
			headline= ?,
			intro_text= ?,
			end_text= ?
			WHERE id = ?";
			$o_main->db->query($s_sql, array($variables->loggID, $_POST['resourceName'],$_POST['resourceHeadline'], $_POST['resourceText'], $_POST['resourceText2'], $_POST['editResource']));
			$fw_return_data = $_POST['editResource'];
			$fieldId = $fw_return_data;
		}
		else if(intval($_POST['deleteResource']) == 0) {
			$s_sql = "INSERT INTO customer_collectingorder_confirmations_templates SET
			id=NULL,
			moduleID = ?,
			created = now(),
			createdBy= ?,
			name=?,
			headline= ?,
			intro_text= ?,
			end_text= ?";
			$o_main->db->query($s_sql, array($moduleID, $variables->loggID, $_POST['resourceName'],$_POST['resourceHeadline'], $_POST['resourceText'], $_POST['resourceText2']));
			$fw_return_data = $o_main->db->insert_id();
			$fieldId = $fw_return_data;
		} else {
			$s_sql = "DELETE customer_collectingorder_confirmations_templates FROM customer_collectingorder_confirmations_templates WHERE customer_collectingorder_confirmations_templates.id = ?";
			$o_main->db->query($s_sql, array($_POST['deleteResource']));
			$fw_return_data = $_POST['deleteResource'];
		}
		echo $fw_return_data;
		$fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=list";
		return;
	}
}
?>
<div class="popupform">
	<div id="popup-validate-message" style="display:none;"></div>

	<div class="popupformTitle"><?php echo $formText_AddEditOrderConfirmationTemplates_output;?></div>
	<div class="errorMessage"></div>
	<div class="resourceList" data-action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=edit_othergroup_order";?>"
	data-action2="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=edit_order_templates";?>">
	<?php

	$resources = array();
	$s_sql = "SELECT * FROM customer_collectingorder_confirmations_templates WHERE content_status < 2 ORDER BY sortnr";
	$o_query = $o_main->db->query($s_sql);
	if($o_query && $o_query->num_rows()>0) {
	    $resources = $o_query->result_array();
	}
	foreach($resources as $resource){

		?>
		<div class="resourceRowSortable" id="sort_<?php echo $resource['id']?>">
			<div class="resourceRow">
				<div class="column nameColumn">
					<div class="columnWrapper"><?php echo $resource['name']?></div>
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

	                            <?php if ($moduleAccesslevel > 100): ?>
	                                <?php if (!$owneraccess || ($owneraccess && $_GET['userID'] == $resource['createdBy'])): ?>
	    								<li class="delete">
	    									<a href="" data-delete-resource-first="<?php echo $resource['id']; ?>" alt="<?php echo $formText_Delete_output;?>" title="<?php echo $formText_Delete_output;?>">
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
					<div class="errorRow"></div>
					<div class="editInnerRow">
						<label><?php echo $formText_OfferName_Output?></label>
						<div class="inputBlock">
							<input type="text" name="resourceName" id="resource<?php echo $resource['id']?>" value="<?php echo $resource['name']?>" autocomplete="off"/>
						</div>
					</div>
					<div class="editInnerRow">
						<label><?php echo $formText_OfferHeadline_Output?></label>
						<div class="inputBlock">
							<input type="text" name="resourceHeadline" id="resourceHeadline<?php echo $resource['id']?>" value="<?php echo $resource['headline']?>" autocomplete="off"/>
						</div>
					</div>
                    <div class="editInnerRow">
						<label><?php echo $formText_OfferIntroText_Output?></label>
						<div class="inputBlock">
                            <textarea name="resourceText" id="resourceText<?php echo $resource['id']?>"><?php echo $resource['intro_text'];?></textarea>
						</div>
					</div>
                    <div class="editInnerRow">
						<label><?php echo $formText_OfferEndText_Output?></label>
						<div class="inputBlock">
                            <textarea name="resourceText2" id="resourceText2<?php echo $resource['id']?>"><?php echo $resource['end_text'];?></textarea>
						</div>
					</div>
					<div class="actionRow">
						<div class="save" data-resource-save-id="<?php echo $resource['id']?>"><?php echo $formText_Save_output?></div>
						<div class="cancel" data-resource-save-cancel="<?php echo $resource['id']?>"><?php echo $formText_Cancel_output?></div>
					</div>
				</div>
			<?php } ?>

		</div>
		<?php
	}
	?>
	</div>
	<div class="newResource editRow">
		<div class="errorRow"></div>
		<div class="editInnerRow">
			<label><?php echo $formText_OfferName_Output?></label>
			<div class="inputBlock">
				<input type="text" name="resourceName" id="resource0" value="" autocomplete="off"/>
			</div>
		</div>
		<div class="editInnerRow">
			<label><?php echo $formText_OfferHeadline_Output?></label>
			<div class="inputBlock">
				<input type="text" name="resourceHeadline" id="resourceHeadline0" value="" autocomplete="off"/>
			</div>
		</div>
        <div class="editInnerRow">
            <label><?php echo $formText_OfferIntroText_Output?></label>
            <div class="inputBlock">
                <textarea name="resourceText" id="resourceText0"></textarea>
            </div>
        </div>
        <div class="editInnerRow">
            <label><?php echo $formText_OfferEndText_Output?></label>
            <div class="inputBlock">
                <textarea name="resourceText2" id="resourceText20"></textarea>
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
			<div class="text"><?php echo $formText_AddTemplate_output; ?></div>
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
	}
};
$(function() {
	$(".hideTextfieldChanger").change(function(){
		var checked = $(this).is(":checked");
		if(checked){
			$(this).parents(".listBlockCheckbox").find(".hiddenTextfieldWrapper").show();
		} else {
			$(this).parents(".listBlockCheckbox").find(".hiddenTextfieldWrapper").hide();
		}
	})
	resizePopupEdit();
	function resizePopupEdit(){

	}
	$(window).resize(resizePopupEdit);
	bindPopupActions();
	function bindPopupActions(){
		// Edit resource
		$("[data-edit-resource-first]").unbind("click").on('click', function(e){
			e.preventDefault();
			$(this).parents(".resourceRow").next(".deleteRow").hide();
			$(this).parents(".resourceRow").next().next(".editRow").show();
		});
		$("[data-resource-save-cancel").unbind("click").on('click', function(e){
			e.preventDefault();
			$(this).parents(".editRow").hide();
		})
		$("[data-resource-save-id").unbind("click").on('click', function(e){
			e.preventDefault();
			var resourceID = $(this).data('resource-save-id'),
				resourceName = $("#resource"+resourceID).val(),
				resourceHeadline = $("#resourceHeadline"+resourceID).val(),
				resourceText = $("#resourceText"+resourceID).val(),
				resourceText2 = $("#resourceText2"+resourceID).val(),
				self = $(this);
		        var action = $("#popupeditbox2 .resourceList").data("action2");
		        var errorRow = self.parents(".editRow").find(".errorRow");





	        if(resourceName != ""){
				errorRow.html("").hide();
				fw_loading_start();
				$.ajax({
					type: 'POST',
					url: action,
					dataType: 'json',
					cache: false,
					data: 'fwajax=1&fw_nocss=1&editResource=' + resourceID + '&output_form_submit=1&resourceName='+resourceName+'&resourceHeadline='+resourceHeadline+'&resourceText='+resourceText+'&resourceText2='+resourceText2,
					success: function(result){
						if(parseInt(result.html) > 0){
                            out_popup3.addClass("refresh");
							$.ajax({
								type: 'POST',
								url: action,
								dataType: 'json',
								cache: false,
								data: {fwajax: 1, fw_nocss: 1, cid: 0},
								success: function(obj){
									fw_loading_end();
									$('#popupeditboxcontent2').html('');
						            $('#popupeditboxcontent2').html(obj.html);
						            out_popup = $('#popupeditbox2').bPopup(out_popup_options);
						            $("#popupeditbox2:not(.opened)").remove();
								}
							});
						} else {
							$(".popupform .errorMessage").html("<?php echo $formText_ErrorSavingResource_output;?>").show();
						}
					}
				});
			} else {
				errorRow.html("<?php echo $formText_PleaseFillInSelfdefinedFieldName_output;?>").show();
			}
		})

		// Delete resource
		$("[data-delete-resource-first").unbind("click").on('click', function(e){
			e.preventDefault();
			$(this).parents(".resourceRow").next().next(".editRow").hide();
			$(this).parents(".resourceRow").next(".deleteRow").show();
		})
		$("[data-delete-cancel").unbind("click").on('click', function(e){
			e.preventDefault();
			$(this).parents(".deleteRow").hide();
		})
		$("[data-delete-resource-id]").unbind("click").on('click', function(e){
			e.preventDefault();
			fw_loading_start();
			var resourceName = $(this).parents(".resourceRow").find(".nameColumn .columnWrapper").html();
			var self = $(this);
	        var action = $("#popupeditbox2 .resourceList").data("action2");
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
		})
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
