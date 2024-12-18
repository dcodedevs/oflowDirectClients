<?php
if($moduleAccesslevel > 10)
{
	if(isset($_POST['output_form_submit']))
	{
		if(isset($_POST['editResource']) && $_POST['editResource'] > 0) {
			$s_sql = "UPDATE people_position SET
			updated = now(),
			updatedBy= ?,
			name= ?
			WHERE id = ?";
			$o_main->db->query($s_sql, array($variables->loggID, $_POST['resourceName'], $_POST['editResource']));
			$fw_return_data = $_POST['editResource'];
			$fieldId = $fw_return_data;
		} else if(isset($_POST['deleteResource']) && intval($_POST['deleteResource']) > 0) {
			$s_sql = "DELETE people_position  FROM people_position WHERE people_position.id = ?";
			$o_main->db->query($s_sql, array($_POST['deleteResource']));
			$fw_return_data = $_POST['deleteResource'];
		} else {
			$s_sql = "INSERT INTO people_position SET
			id=NULL,
			moduleID = ?,
			created = now(),
			createdBy= ?,
			name= ?";
			$o_main->db->query($s_sql, array($moduleID, $variables->loggID, $_POST['resourceName']));
			$fw_return_data = $o_main->db->insert_id();
			$fieldId = $fw_return_data;
		}

		echo $fw_return_data;
		$fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=list";
		return;
	}
}

?>
<div class="popupform">
	<div id="popup-validate-message" style="display:none;"></div>

	<div class="popupformTitle"><?php echo $formText_AddEditPosition_output;?></div>
	<div class="errorMessage"></div>
	<div class="resourceList" data-action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=edit_position";?>"
	data-action2="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=edit_position";?>">
	<?php

	$resources = array();
	$s_sql = "SELECT * FROM people_position WHERE content_status < 2 ORDER BY name";
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
	    										<span class="glyphicon glyphicon-edit fw_delete_edit_icon_color"></span>
	    									</a>
	    								</li>
	                                <?php endif; ?>
	                            <?php endif; ?>

	                            <?php if ($moduleAccesslevel > 100): ?>
	                                <?php if (!$owneraccess || ($owneraccess && $_GET['userID'] == $resource['createdBy'])): ?>
	    								<li class="delete">
	    									<a href="" data-delete-resource-first="<?php echo $resource['id']; ?>" alt="<?php echo $formText_Delete_output;?>" title="<?php echo $formText_Delete_output;?>">
	    										<span class="glyphicon glyphicon-trash fw_delete_edit_icon_color"></span>
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
						<li class="delete fw_button_color">
							<a href="" data-delete-resource-id="<?php echo $resource['id']; ?>" alt="<?php echo $formText_Delete_output;?>" title="<?php echo $formText_Delete_output;?>">
								<?php echo $formText_Delete_output;?>
							</a>
						</li>
						<li class="cancel fw_button_not_filled_color">
							<a href="" data-delete-cancel="1"><?php echo $formText_Cancel_output;?></a>
						</li>
					</ul>
				</div>
				<div class="editRow">
					<div class="errorRow"></div>
					<div class="editInnerRow">
						<label><?php echo $formText_PositionName_output?></label>
						<div class="inputBlock">
							<input type="text" name="resourceName" id="resource<?php echo $resource['id']?>" value="<?php echo $resource['name']?>" autocomplete="off"/>
						</div>
					</div>
					<div class="actionRow">
						<div class="save fw_button_color" data-resource-save-id="<?php echo $resource['id']?>"><?php echo $formText_Save_output?></div>
						<div class="cancel fw_button_not_filled_color" data-resource-save-cancel="<?php echo $resource['id']?>"><?php echo $formText_Cancel_output?></div>
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
			<label><?php echo $formText_PositionName_output?></label>
			<div class="inputBlock">
				<input type="text" name="resourceName" id="resource0" value="" autocomplete="off"/>
			</div>
		</div>
		<div class="actionRow">
			<div class="save fw_button_color" data-resource-save-id="0"><?php echo $formText_Save_output?></div>
			<div class="cancel fw_button_not_filled_color" data-resource-save-cancel="0"><?php echo $formText_Cancel_output?></div>
		</div>
	</div>
	<div class="addNew">
		<div class="plusTextBox active fw_text_link_color">
			<div class="text">+ <?php echo $formText_AddPosition_output; ?></div>
		</div>
		<div class="clear"></div>
	</div>
	<!-- <div class="explanation"><?php echo $formText_DragAndDropToChangeOrder_output;?></div> -->

</div>
<style>
.popupform .resourceList {

	margin-bottom: 20px;

}

.popupform .resourceRow .column {

	float: left;

}

.popupform .resourceRow .column .columnWrapper {

	padding: 5px 4px;

}

.popupform .resourceRow .nameColumn {

	width: 30%;

	font-size: 14px;

}

.popupform .resourceRow .typeColumn {

	width: 30%;

	font-size: 14px;

}

.popupform .resourceRow .statusColumn {

	width: 30%;

}

.popupform .resourceRow .actionColumn {

	width: 15%;

	text-align: right;

}

.popupform .resourceRow .statusColumn .selectDiv select {

	padding: 5px 30px 4px 10px;

}

.popupform .resourceRow .statusColumn .selectDiv .arrowDown {

	top: 14px;

}

.popupform .resourceRow .statusColumn .selectDiv .active {

	color: #0091e8;

}

.popupform .resourceRow .statusColumn .selectDiv .inactive {

	color: #F7640B;

}

.popupform .resourceRow .statusColumn .selectDiv.active {

	border: 1px solid #0091e8;

	font-weight: bold;

	-webkit-border-radius: 3px;

	-moz-border-radius: 3px;

	border-radius: 3px;

	color: #0091e8;

}

.popupform .resourceRow .statusColumn .selectDiv.active .arrowDown {

	border-left: 5px solid transparent;

	border-right: 5px solid transparent;

	border-top: 5px solid #0091e8;

}

.popupform .resourceRow .statusColumn .selectDiv.inactive {

	border: 1px solid #F7640B;

	font-weight: bold;

	-webkit-border-radius: 3px;

	-moz-border-radius: 3px;

	border-radius: 3px;

	color: #F7640B;

}

.popupform .resourceRow .statusColumn .selectDiv.inactive .arrowDown {

	border-left: 5px solid transparent;

	border-right: 5px solid transparent;

	border-top: 5px solid #F7640B;

}

.popupform .resourceRow .actions li {

	display: inline-block;

	vertical-align: middle;

	margin: 0px 5px;

}

.popupform .deleteRow {

	display: none;

	padding: 5px 0px;

}

.popupform .deleteRow .actions li a {

	text-decoration: none;

	color: inherit;

}

.popupform .deleteRow .actions li.delete {

	display: inline-block;

	border: 0px none;

	font-size: 13px;

	text-transform: uppercase;

	padding: 5px 15px;

	font-weight: 700;

	color: #FFF;

	-webkit-border-radius: 2px;

	-moz-border-radius: 2px;

	border-radius: 2px;

	margin-right: 10px;

}

.popupform .deleteRow .actions li.cancel {

	display: inline-block;

	font-size: 13px;

	text-transform: uppercase;

	padding: 5px 15px;

	background: #FFF none repeat scroll 0% 0%;

	cursor: pointer;

	display: inline-block;

	-webkit-border-radius: 2px;

	-moz-border-radius: 2px;

	border-radius: 2px;

}

.popupform .editRow {

	display: none;

	padding: 5px 0px;

}

.popupform .editRow a {

	text-decoration: none;

	color: inherit;

}

.popupform .editRow input {

	padding: 5px 10px;

	font-size: 12px;

	line-height: 20px;

	color: #3C3C3F;

	background-color: transparent;

	box-sizing: border-box;

	z-index: 2;

	font-weight: 400;

	margin-right: 10px;

	-webkit-border-radius: 2px;

	-moz-border-radius: 2px;

	border-radius: 2px;

	width: 200px;

	border: 1px solid #CCC;

}
.popupform .editRow textarea {
	padding: 5px 10px;
	font-size: 12px;
	line-height: 20px;
	color: #3C3C3F;
	background-color: transparent;
	box-sizing: border-box;
	z-index: 2;
	font-weight: 400;
	margin-right: 10px;
	-webkit-border-radius: 2px;
	-moz-border-radius: 2px;
	border-radius: 2px;
	border: 1px solid #CCC;
}
.editRow .save {

	display: inline-block;

	border: 0px none;

	background-color: #0393FF;

	font-size: 13px;

	text-transform: uppercase;

	padding: 5px 15px;

	font-weight: 700;

	color: #FFF;

	-webkit-border-radius: 2px;

	-moz-border-radius: 2px;

	border-radius: 2px;

	cursor: pointer;

	margin-right: 5px;

}

.popupform .editRow .cancel {

	display: inline-block;

	color: #0497E5;

	font-size: 13px;

	text-transform: uppercase;

	border: 1px solid #0497E5;

	padding: 5px 15px;

	background: #FFF none repeat scroll 0% 0%;

	cursor: pointer;

	display: inline-block;

	-webkit-border-radius: 2px;

	-moz-border-radius: 2px;

	border-radius: 2px;

}

.popupform .addNew {

	padding: 15px 0px;

}

.popupform .addNew .plusTextBox {
	float: left;
	margin-left: 0px;
	font-size: 14px;
	cursor: pointer;
}

.popupform .addNew .plusTextBox .plusBox {

	top: 5px;

}

.popupform .explanation {

}

.popupform .newResource {
	display: none;
}

.popupform .errorMessage {
	padding: 10px 0px;
	display: none;
	color: #F7640B;
}.showInListWrapper {
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
.subDepartments {
	margin-left: 30px;
}
.actions {
	margin-left: 0;
	padding-left: 0;
}

</style>

<?php

$s_path = $variables->account_root_url;

$v_script = array(
  'modules/'.$module.'/output/elementsOutput/jquery.validate/jquery.validate.min.js',
);

foreach($v_script as $s_item)
{
  $l_time = filemtime(BASEPATH.$s_item);
  ?><script type="text/javascript" src="<?php echo $s_path.$s_item.'?v='.$l_time;?>"></script><?php
}

?>

<!-- <script type="text/javascript" src="../modules/<?php echo $module?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script> -->
<script type="text/javascript">

function updateSelfdefinedLists(){
	$(".selectList").each(function(){
		var _this = $(this);
		var selectedId = _this.data("listid");
		var data = {selected: selectedId};
	    ajaxCall('get_selfdefinedlist', data, function(obj) {
	        _this.html(obj.html);
	    });
	})
}
var out_popup2;
var out_popup_options2={
	follow: [true, false],
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
				resourceDescription = $("#resourceDescription"+resourceID).val(),
				self = $(this);
		        var action = $("#popupeditbox .resourceList").data("action2");
		        var errorRow = self.parents(".editRow").find(".errorRow");



	        if(resourceName != ""){
				errorRow.html("").hide();
				fw_loading_start();
				$.ajax({
					type: 'POST',
					url: action,
					cache: false,
					data: 'fwajax=1&fw_nocss=1&editResource=' + resourceID + '&output_form_submit=1&resourceDescription='+resourceDescription+'&resourceName='+resourceName,
					success: function(data){
						if(parseInt(data.html) > 0){
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
	        var action = $("#popupeditbox .resourceList").data("action2");
			$.ajax({
				type: 'POST',
				url: action,
				cache: false,
				data: 'output_form_submit=1&fwajax=1&fw_nocss=1&deleteResource=' + $(this).data('delete-resource-id'),
				success: function(data){
					fw_loading_end();
					if(parseInt(data.html) != 0){
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
