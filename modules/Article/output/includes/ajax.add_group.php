<?php
if($moduleAccesslevel > 10)
{
	if(isset($_POST['output_form_submit']))
	{

		if($_POST['editResource'] > 0) {
			$s_sql = "UPDATE article_group SET
			updated = now(),
			updatedBy= ?,
			name= ?
			WHERE id = ?";
			$o_main->db->query($s_sql, array($variables->loggID, $_POST['resourceName'], $_POST['editResource']));
			$fw_return_data = $_POST['editResource'];
		}
		else if(intval($_POST['deleteResource']) == 0) {
			$s_sql = "INSERT INTO article_group SET
			id=NULL,
			moduleID = ?,
			created = now(),
			createdBy= ?,
			name= ?";
			$o_main->db->query($s_sql, array($moduleID, $variables->loggID, $_POST['resourceName']));
			$fw_return_data = $o_main->db->insert_id();
		} else {
			$s_sql = "DELETE article_group FROM article_group WHERE article_group.id = ?";
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

	<div class="popupformTitle"><?php echo $formText_AddEditArticleGroup_output;?></div>
	<div class="errorMessage"></div>
	<div class="resourceList" data-action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=add_group";?>"
	data-action2="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=add_group";?>">
	<?php
	$resources = array();
	$s_sql = "SELECT * FROM article_group WHERE content_status < 2 ORDER BY name ASC";
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

	                            <?php if ($moduleAccesslevel > 100):
									if(true) {
		                            	 ?>
		                                <?php if (!$owneraccess || ($owneraccess && $_GET['userID'] == $resource['createdBy'])): ?>
		    								<li class="delete">
		    									<a href="" data-delete-resource-first="<?php echo $resource['id']; ?>" alt="<?php echo $formText_Delete_output;?>" title="<?php echo $formText_Delete_output;?>">
		    										<span class="glyphicon glyphicon-trash"></span>
		    									</a>
		    								</li>
		                                <?php endif; ?>
		                            <?php } ?>
	                            <?php endif; ?>
							</ul>
						<?php } ?>
					</div>
				</div>
				<div class="clear"></div>
			</div>
			<?php if(!$resource['disabledForEditing']) { ?>
				<div class="deleteRow">
					<div class="errorRow"><?php echo $formText_CanNotDeleteWithActiveConnections_output;?></div>
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
			<div class="text"><?php echo $formText_AddArticleGroup_output; ?></div>
		</div>
		<div class="clear"></div>
	</div>

	<!-- <div class="explanation"><?php echo $formText_DragAndDropToChangeOrder_output;?></div> -->
</div>
<div id="popupeditbox2" class="popupeditbox">
	<span class="button b-close"><span>X</span></span>
	<div id="popupeditboxcontent2"></div>
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
		$("[data-resource-save-cancel").unbind("click").on('click', function(e){
			e.preventDefault();
			$(this).parents(".editRow").hide();
		})
		$("[data-resource-save-id").unbind("click").on('click', function(e){
			e.preventDefault();
			var resourceID = $(this).data('resource-save-id'),
				resourceName = $("#resource"+resourceID).val(),
				self = $(this);
		        var action = $(".resourceList").data("action2");
			$.ajax({
				type: 'POST',
				url: action,
				cache: false,
      			dataType: "json",
				data: 'fwajax=1&fw_nocss=1&editResource=' + resourceID + '&output_form_submit=1&resourceName='+resourceName,
				success: function(result){
					if(parseInt(result.html) > 0){
						ajaxCall('add_group', {}, function(obj) {
				            $('#popupeditboxcontent').html('');
				            $('#popupeditboxcontent').html(obj.html);
				            out_popup = $('#popupeditbox').bPopup(out_popup_options);
				            $("#popupeditbox:not(.opened)").remove();
							out_popup.addClass("close-reload");
				        });
					} else {
						$(".popupform .errorMessage").html("<?php echo $formText_ErrorSavingResource_output;?>").show();
					}
				}
			});
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
			var resourceName = $(this).parents(".resourceRowSortable").find(".nameColumn .columnWrapper").html();
			var self = $(this);
	        var action = $(".resourceList").data("action2");
	        if($(this).parents(".resourceRowSortable").find(".showArticles").data("count") > 0){
	        	$(this).parents(".deleteRow").find(".errorRow").addClass("active");
			} else {
	        	$(this).parents(".deleteRow").find(".errorRow").removeClass("active");
				$.ajax({
					type: 'POST',
					url: action,
					cache: false,
	      			dataType: "json",
					data: 'output_form_submit=1&fwajax=1&fw_nocss=1&deleteResource=' + $(this).data('delete-resource-id'),
					success: function(result){
						out_popup.addClass("close-reload");
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
			}
		});
		$(".popupform .addNew").unbind("click").bind("click", function(){
			$(".newResource").show();
		})
		$(".showArticles").unbind("click").bind("click", function(){
			var data = {
				"resourceId": $(this).data("resource-id"),
				"matrix": "discount"
			}
			ajaxCall('show_customers', data, function(obj) {
	            $('#popupeditboxcontent2').html('');
	            $('#popupeditboxcontent2').html(obj.html);
	            out_popup = $('#popupeditbox2').bPopup(out_popup_options);
	            $("#popupeditbox2:not(.opened)").remove();
	        });
		})
	}
});
</script>
<style>
.errorRow {
	display: none;
	color: red;
	margin-bottom: 5px;
}
.errorRow.active {
	display: block;
}
.showArticles {
	cursor: pointer;
	margin-left: 15px;
	color: #46b2e2;
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
