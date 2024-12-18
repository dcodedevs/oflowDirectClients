<?php
$maxFieldNumber = 10;
if($moduleAccesslevel > 10)
{
	if(isset($_POST['output_form_submit']))
	{
        $sql_set = array();
        for($x=1; $x<= $maxFieldNumber; $x++){
            $sql_set[] = "field_".$x."_label = '".$_POST['field'][$x]."'";
        }
		$fields = $_POST['fields'];
		$resousceID = $_POST['resourceID'];

		if($resousceID > 0) {
			$s_sql = "UPDATE customerhistoryextsystemcategory SET
			updated = now(),
			updatedBy= ?,
			".implode(',',$sql_set).",
			name= ?
			WHERE id = ?";
			$o_main->db->query($s_sql, array($variables->loggID, $_POST['resourceName'], $_POST['resourceID']));
			$fw_return_data = $_POST['resourceID'];
		}
		else if(intval($_POST['deleteResource']) == 0) {
			$s_sql = "INSERT INTO customerhistoryextsystemcategory SET
			id=NULL,
			moduleID = ?,
			created = now(),
			createdBy= ?,
			".implode(',',$sql_set).",
			name= ?";
			$o_main->db->query($s_sql, array($moduleID, $variables->loggID, $_POST['resourceName']));
			$fw_return_data = $o_main->db->insert_id();
		} else {
			$s_sql = "DELETE customerhistoryextsystemcategory FROM customerhistoryextsystemcategory  WHERE customerhistoryextsystemcategory.id = ?";
			$o_main->db->query($s_sql, array($_POST['deleteResource']));
			$fw_return_data = $_POST['deleteResource'];
		}
		echo $fw_return_data;
		$fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=list";
		return;
	}
}
if(isset($_POST['cid']) && $_POST['cid'] > 0)
{
	$s_sql = "SELECT * FROM customerhistoryextsystemcategory WHERE id = ?";
	$o_query = $o_main->db->query($s_sql, array($_POST['cid']));
	if($o_query && $o_query->num_rows()>0) {
	    $v_data = $o_query->row_array();
	}
}
?>
<div class="popupform">
	<div id="popup-validate-message" style="display:none;"></div>

	<div class="popupformTitle"><?php echo $formText_AddEditHistoricalCategory_output;?> <?php echo $resource['name']?></div>
	<div class="errorMessage"></div>
	<div class="resourceList" data-action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=edit_history_category";?>"
	data-action2="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=edit_history_category";?>">
	<?php
	$resources = array();
	$s_sql = "SELECT * FROM customerhistoryextsystemcategory WHERE content_status < 2 ORDER BY name ASC";
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
				<div class="column">
					<div class="columnWrapper">
					</div>
				</div>
				<div class="column actionColumn">
					<div class="columnWrapper">
						<?php if(!$resource['disabledForEditing']) { ?>
							<ul class="actions">
	                            <?php if ($moduleAccesslevel > 10): ?>
	                                <?php if (!$owneraccess || ($owneraccess && $_GET['userID'] == $resource['createdBy'])): ?>
	    								<li class="edit">
	    									<a href="" data-edit-resource2-first="<?php echo $resource['id']; ?>" alt="<?php echo $formText_Edit_output;?>" title="<?php echo $formText_Edit_output;?>">
	    										<span class="glyphicon glyphicon-edit"></span>
	    									</a>
	    								</li>
	                                <?php endif; ?>
	                            <?php endif; ?>

	                            <?php if ($moduleAccesslevel > 100): ?>
	                                <?php if (!$owneraccess || ($owneraccess && $_GET['userID'] == $resource['createdBy'])): ?>
	    								<li class="delete">
	    									<a href="" data-delete-resource2-first="<?php echo $resource['id']; ?>" alt="<?php echo $formText_Delete_output;?>" title="<?php echo $formText_Delete_output;?>">
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
							<a href="" data-delete-resource2-id="<?php echo $resource['id']; ?>" alt="<?php echo $formText_Delete_output;?>" title="<?php echo $formText_Delete_output;?>">
								<?php echo $formText_Delete_output;?>
							</a>
						</li>
						<li class="cancel">
							<a href="" data-delete-cancel="1"><?php echo $formText_Cancel_output;?></a>
						</li>
					</ul>
				</div>
				<div class="editRow">
					<div class="inputWrapper">
						<label><?php echo $formText_Name_output;?></label>
						<input type="hidden" name="resourceID" value="<?php echo $resource['id']?>" autocomplete="off"/>
						<input type="text" name="resourceName" id="res<?php echo $resource['id']?>" value="<?php echo $resource['name']?>" autocomplete="off"/>
				        <?php
				        for($x=1; $x<= $maxFieldNumber; $x++){
							?>
							<div>
								<label><?php echo $formText_Field_output." ".$x;?></label>
								<input type="text" name="field[<?php echo $x;?>]" value="<?php echo $resource['field_'.$x.'_label']?>" autocomplete="off"/>
							</div>
							<?php
				        }
				        ?>
						<div class="save" data-resource2-save-id="0"><?php echo $formText_Save_output?></div>
						<div class="cancel" data-resource2-save-cancel="0"><?php echo $formText_Cancel_output?></div>
					</div>
				</div>
			<?php } ?>

		</div>
		<?php
	}
	?>
	</div>
	<div class="newResource editRow">
		<div class="inputWrapper">
			<label><?php echo $formText_Name_output;?></label>
			<input type="text" name="resourceName" id="res0" value="" autocomplete="off"/>
	        <?php
	        for($x=1; $x<= $maxFieldNumber; $x++){
				?>
				<div>
					<label><?php echo $formText_Field_output." ".$x;?></label>
					<input type="text" name="field[<?php echo $x;?>]"  value="" autocomplete="off"/>
				</div>
				<?php
	        }
	        ?>
			<div class="save" data-resource2-save-id="0"><?php echo $formText_Save_output?></div>
			<div class="cancel" data-resource2-save-cancel="0"><?php echo $formText_Cancel_output?></div>
		</div>
	</div>
	<div class="addNew">
		<div class="plusTextBox active">
			<div class="plusBox addres"><div class="lineH"></div><div class="lineV"></div></div>
			<div class="text"><?php echo $formText_AddHistoricalCategory_output; ?></div>
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
	bindPopupActions2();
	function bindPopupActions2(){
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
		$("[data-edit-resource2-first]").unbind("click").on('click', function(e){
			e.preventDefault();
			$(this).parents(".resourceRow").next(".deleteRow").hide();
			$(this).parents(".resourceRow").next().next(".editRow").show();
			$(window).resize();
		});
		$("[data-resource2-save-cancel").unbind("click").on('click', function(e){
			e.preventDefault();
			$(this).parents(".editRow").hide();
		})
		$("[data-resource2-save-id").unbind("click").on('click', function(e){
			e.preventDefault();
			var formdata = $(this).parents(".inputWrapper").find("input").serializeArray();
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

			data.output_form_submit = 1;
			ajaxCall('edit_history_category', data, function(json) {
				if(parseInt(json.html) > 0){
					var data = { };
					ajaxCall('edit_history_category', data, function(obj) {
						$('#popupeditboxcontent').html('');
						$('#popupeditboxcontent').html(obj.html);
						out_popup = $('#popupeditbox').bPopup(out_popup_options);
						$("#popupeditbox:not(.opened)").remove();
					});
				} else {
					$(".popupform .errorMessage").html("<?php echo $formText_ErrorSavingResource_output;?>").show();
				}
	        });
		})

		// Delete resource
		$("[data-delete-resource2-first").unbind("click").on('click', function(e){
			e.preventDefault();
			$(this).parents(".resourceRow").next().next(".editRow").hide();
			$(this).parents(".resourceRow").next(".deleteRow").show();
			$(window).resize();
		})
		$("[data-delete-cancel").unbind("click").on('click', function(e){
			e.preventDefault();
			$(this).parents(".deleteRow").hide();
		})
		$("[data-delete-resource2-id]").unbind("click").on('click', function(e){
			e.preventDefault();
			var resourceName = $(this).parents(".resourceRow").find(".nameColumn .columnWrapper").html();
			var self = $(this);
	        var action = $("#popupeditbox .resourceList").data("action2");

			var data = {};
			data.output_form_submit = 1;
			data.deleteResource = $(this).data('delete-resource2-id');
			ajaxCall('edit_history_category', data, function(json) {
				if(parseInt(json.html) > 0){
					var data = { };
					ajaxCall('edit_history_category', data, function(obj) {
						$('#popupeditboxcontent').html('');
						$('#popupeditboxcontent').html(obj.html);
						out_popup = $('#popupeditbox').bPopup(out_popup_options);
						$("#popupeditbox:not(.opened)").remove();
					});
				} else {
					$(".popupform .errorMessage").html("<?php echo $formText_ErrorSavingResource_output;?>").show();
				}
	        });
		});


		$("#popupeditbox .popupform .addNew").unbind("click").bind("click", function(){
			$("#popupeditbox .newResource").show();
			$(window).resize();
		})

	}
});
</script>
<style>
.editRow label {
	display: inline-block !important;
	vertical-align: middle;
	width: 200px;
}
.addWorkLeader {
	cursor: pointer;
}
.workleaderBlock {
	margin-left: 10px;
	max-height: 200px;
	overflow: auto;
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
</style>
