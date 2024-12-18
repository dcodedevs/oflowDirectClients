<?php
if(isset($_POST['cid']) && intval($_POST['cid']) > 0){
	if($moduleAccesslevel > 10)
	{

		$predefinedFields = array();
		$s_sql = "SELECT * FROM customer_selfdefined_fields ORDER BY customer_selfdefined_fields.sortnr";
		$o_query = $o_main->db->query($s_sql);
		if($o_query && $o_query->num_rows()>0) {
		    $predefinedFields = $o_query->result_array();
		}
		if(isset($_POST['output_form_submit']))
		{
			if($_POST['editResource'] > 0) {

				foreach($predefinedFields as $predefinedField) {
					$s_sql = "SELECT * FROM customer_selfdefined_values WHERE homes_id = ? AND selfdefined_fields_id = ?";
				    $o_query = $o_main->db->query($s_sql, array($_POST['cid'], $predefinedField['id']));
				    if($o_query && $o_query->num_rows()>0) {
				        $predefinedFieldValue = $o_query->row_array();
				    }

					if($predefinedFieldValue) {
						$s_sql = "UPDATE customer_selfdefined_values SET
						updated = now(),
						updatedBy= ?,
						active = ?,
					 	text = ?,
						homes_ID = ?,
						selfdefined_fields_id =  ?
						WHERE id = ?";
						$o_main->db->query($s_sql, array($variables->loggID, $_POST['selfdefinedchbx'.$predefinedField['id']], $_POST['selfdefinedvalue'.$predefinedField['id']], $_POST['cid'], $predefinedField['id'], $predefinedFieldValue['id']));
					} else {
						$s_sql = "INSERT INTO customer_selfdefined_values
						SET
						id=NULL,
						moduleID = ?,
						created = now(),
						createdBy=?,
						active = ?,
						text = ?,
						homes_ID = ?,
						selfdefined_fields_id =  ?";

						$o_main->db->query($s_sql, array($moduleID, $variables->loggID, $_POST['selfdefinedchbx'.$predefinedField['id']], $_POST['selfdefinedvalue'.$predefinedField['id']], $_POST['cid'], $predefinedField['id']));
					}
				}

			}
			echo "1";
			$fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=list";
			return;
		}
	}
	?>
	<div class="popupform">
		<div id="popup-validate-message" style="display:none;"></div>

		<div class="popupformTitle"><?php echo $formText_AddEditSelfDefinedField_output;?></div>
		<div class="errorMessage"></div>
		<div class="resourceList" data-action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=edit_othergroup_order";?>"
		data-action2="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=edit_selfdefined";?>">
			<?php
			foreach($predefinedFields as $predefinedField) {
				$s_sql = "SELECT * FROM customer_selfdefined_values WHERE homes_id = ? AND selfdefined_fields_id = ?";
			    $o_query = $o_main->db->query($s_sql, array($_POST['cid'], $predefinedField['id']));
			    if($o_query && $o_query->num_rows()>0) {
			        $predefinedFieldValue = $o_query->row_array();
			    }
				?>
				<div class="resourceRow">
					<input type="checkbox" name="selfdefinedchbx<?php echo $predefinedField['id']?>" class="fieldnameCheckbox" value="1" <?php if($predefinedFieldValue['active']) echo 'checked';?> />
					<label for="fieldtext<?php echo $predefinedField['id']?>" class="firstLabel"><?php echo $predefinedField['name']?></label>
					<input id="fieldtext<?php echo $predefinedField['id']?>" type="text" name="selfdefinedvalue<?php echo $predefinedField['id']?>" class="fieldnameText" value="<?php echo $predefinedFieldValue['text'];?>"  autocomplete="off"/>

				</div>
				<?php
			}
			?>

		</div>
		<div class="editRow" style="display: block;">
			<div class="save saveForm"><?php echo $formText_Save_output?></div>
		</div>


	</div>

<script type="text/javascript" src="../modules/<?php echo $module?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
	<script type="text/javascript">
	$(function() {
		$(".saveForm").unbind("click").on('click', function(e){
			e.preventDefault();
	        var action = $(".resourceList").data("action2");
	        var serialized = $('.resourceList :input').serialize();
            fw_loading_start();
			$.ajax({
				type: 'POST',
				url: action,
				cache: false,
				data: serialized+'&fwajax=1&fw_nocss=1&editResource=1&output_form_submit=1&cid=<?php echo $_POST["cid"]?>',
				success: function(data){
                	fw_loading_end();
					var result = $.parseJSON(data);
					if(parseInt(result.html) > 0){
						out_popup.addClass("close-reload");
						out_popup.close();
					} else {
						$(".popupform .errorMessage").html("<?php echo $formText_ErrorSavingResource_output;?>").show();
					}
				}
			});
		})
		// $(".resourceList .fieldTrigger").change(function(){
		// 	var isChecked = $(this).is(":checked");
		// 	var parent = $(this).parent(".resourceRow");
		// 	var fieldRow = parent.find(".fieldRow");
		// 	if(isChecked){
		// 		fieldRow.show();
		// 	} else {
		// 		fieldRow.hide();
		// 	}
		// })
		// $(".resourceList .fieldTrigger:checked").change();
		$(".resourceList .fieldnameCheckbox").change(function(){
			var isChecked = $(this).is(":checked");
			var parent = $(this).parent(".resourceRow");
			var fieldRow = parent.find(".fieldnameText");
			if(isChecked){
				fieldRow.show();
			} else {
				fieldRow.hide();
			}
		})
		$(".resourceList .fieldnameCheckbox:checked").change();
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
					data: 'fwajax=1&fw_nocss=1&editResource=' + resourceID + '&output_form_submit=1&resourceName='+resourceName,
					success: function(data){
						var result = $.parseJSON(data);
						if(parseInt(result.html) > 0){
							ajaxCall('add_selfdefined', data, function(obj) {
					            $('#popupeditboxcontent').html('');
					            $('#popupeditboxcontent').html(obj.html);
					            wetPopupAC = $('#popupeditbox').bPopup(wetPopupOptionsAC);
					            $("#popupeditbox:not(.opened)").remove();
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
				var resourceName = $(this).parents(".resourceRow").find(".nameColumn .columnWrapper").html();
				var self = $(this);
		        var action = $(".resourceList").data("action2");
				$.ajax({
					type: 'POST',
					url: action,
					cache: false,
					data: 'output_form_submit=1&fwajax=1&fw_nocss=1&deleteResource=' + $(this).data('delete-resource-id'),
					success: function(data){
						var result = $.parseJSON(data);
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
	.popupform .fieldnameCheckbox {
		vertical-align: top;
		margin-right: 10px;
	}
	.popupform .fieldnameText {
		display: none;
	}
	.popupform .resourceList .resourceRow {
		height: 30px;
	}
	.popupform .resourceList label {
		margin-bottom: 0px;
		vertical-align: top;
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

<?php } ?>
