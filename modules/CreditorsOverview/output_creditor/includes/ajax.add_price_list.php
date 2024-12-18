<?php

if($moduleAccesslevel > 10)
{
	if(isset($_POST['output_form_submit']))
	{
		if($_POST['editResource'] > 0) {
			$s_sql = "UPDATE creditor_price_list SET
			updated = now(),
			updatedBy= ?,
			price_per_print = ?,
			price_per_fee = ?,
			price_per_ehf = ?,
			date_from = ?
			WHERE id = ?".($o_main->multi_acc?" AND account_id = '".$o_main->db->escape_str($o_main->account_id)."'":"")."";
			$o_main->db->query($s_sql, array($variables->loggID, $_POST['price_per_print'],$_POST['price_per_fee'],$_POST['price_per_ehf'],date("Y-m-d", strtotime($_POST['date_from'])), $_POST['editResource']));
			$fw_return_data = $_POST['editResource'];
		}
		else if(intval($_POST['deleteResource']) == 0) {
			$s_sql = "INSERT INTO creditor_price_list SET
			id=NULL,
			moduleID = ?,
			created = now(),
			createdBy= ?,
			price_per_print = ?,
			price_per_fee = ?,
			price_per_ehf = ?,
			date_from = ?".($o_main->multi_acc?", account_id = '".$o_main->db->escape_str($o_main->account_id)."'":"")."";
			$o_main->db->query($s_sql, array($moduleID, $variables->loggID, $_POST['price_per_print'],$_POST['price_per_fee'],$_POST['price_per_ehf'],date("Y-m-d", strtotime($_POST['date_from']))));
			$fw_return_data = $o_main->db->insert_id();
		} else {
			$s_sql = "DELETE creditor_price_list FROM creditor_price_list WHERE id = ?".($o_main->multi_acc?" AND account_id = '".$o_main->db->escape_str($o_main->account_id)."'":"")."";
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

	<div class="popupformTitle"><?php echo $formText_AddPriceList;?> </div>
	<div class="errorMessage"></div>
	<div class="resourceList" data-action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=add_price_list";?>"
	data-action2="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=".$_GET['folderfile']."&folder=".$_GET['folder']."&inc_obj=ajax&inc_act=add_price_list";?>">
	<?php
	$resources = array();
	$s_sql = "SELECT * FROM creditor_price_list WHERE".($o_main->multi_acc?" account_id = '".$o_main->db->escape_str($o_main->account_id)."' AND":"")." content_status < 2 ORDER BY date_from ASC";
	$o_query = $o_main->db->query($s_sql);
	if($o_query && $o_query->num_rows()>0) {
	    $resources = $o_query->result_array();
	}

	foreach($resources as $resource) {
		?>
		<div class="resourceRowSortable" id="sort_<?php echo $resource['id']?>">
			<div class="resourceRow">
				<div class="column nameColumn">
					<div class="columnWrapper"><?php echo $resource['price_per_print']." - ".$resource['price_per_fee']." - ".date("d.m.Y", strtotime($resource['date_from']));?></div>
				</div>
				<div class="column actionColumn">
					<div class="columnWrapper">
						<?php if(!$resource['disabledForEditing']) { ?>
							<ul class="actions">
	                            <?php if ($moduleAccesslevel > 10): ?>
	                                <?php if (!$owneraccess || ($owneraccess && $_GET['userID'] == $resource['createdBy'])): ?>
	    								<li class="edit">
	    									<a href="" data-edit-resource-first="<?php echo $resource['id']; ?>" alt="<?php echo $formText_Edit_LID8837;?>" title="<?php echo $formText_Edit_LID8885;?>">
	    										<span class="glyphicon glyphicon-edit"></span>
	    									</a>
	    								</li>
	                                <?php endif; ?>
	                            <?php endif; ?>

	                            <?php if ($moduleAccesslevel > 100): ?>
									<?php if (!$owneraccess || ($owneraccess && $_GET['userID'] == $resource['createdBy'])): ?>
										<li class="delete">
											<a href="" data-delete-resource-first="<?php echo $resource['id']; ?>" alt="<?php echo $formText_Delete_LID8982;?>" title="<?php echo $formText_Delete_LID8990;?>">
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
							<a href="" data-delete-resource-id="<?php echo $resource['id']; ?>" alt="<?php echo $formText_Delete_LID8998;?>" title="<?php echo $formText_Delete_LID9006;?>">
								<?php echo $formText_Delete_LID9013;?>
							</a>
						</li>
						<li class="cancel">
							<a href="" data-delete-cancel="1"><?php echo $formText_Cancel_LID8924;?></a>
						</li>
					</ul>
				</div>
				<div class="editRow">
					<b><?php echo $formText_PricePerPrint_output;?></b><br/><input type="text" name="price_per_print" id="resource<?php echo $resource['id']?>" value="<?php echo $resource['price_per_print']?>" autocomplete="off"/><br/>
					<b><?php echo $formText_PricePerFee_output;?></b><br/><input type="text" name="price_per_fee" id="resource_fee<?php echo $resource['id']?>" value="<?php echo $resource['price_per_fee']?>" autocomplete="off"/><br/>
					<b><?php echo $formText_PricePerEhf_output;?></b><br/><input type="text" name="price_per_fee" id="resource_ehf<?php echo $resource['id']?>" value="<?php echo $resource['price_per_ehf']?>" autocomplete="off"/><br/>					
					<b><?php echo $formText_DateFrom_output;?></b><br/><input type="text" name="date_from" class="datepicker" id="resource_date<?php echo $resource['id']?>" value="<?php echo date("d.m.Y", strtotime($resource['date_from']))?>" autocomplete="off"/>
					<div class="save" data-resource-save-id="<?php echo $resource['id']?>"><?php echo $formText_Save_LID8855?></div>
					<div class="cancel" data-resource-save-cancel="<?php echo $resource['id']?>"><?php echo $formText_Cancel_LID8943?></div>
				</div>
			<?php } ?>

		</div>
		<?php
	}
	?>
	</div>
	<div class="newResource editRow">

		<b><?php echo $formText_PricePerPrint_output;?></b><br/><input type="text" name="price_per_print" id="resource0" value="" autocomplete="off"/><br/>
		<b><?php echo $formText_PricePerFee_output;?></b><br/><input type="text" name="price_per_fee" id="resource_fee0" value="" autocomplete="off"/><br/>
		<b><?php echo $formText_PricePerEhf_output;?></b><br/><input type="text" name="price_per_ehf" id="resource_ehf0" value="" autocomplete="off"/><br/>
		<b><?php echo $formText_DateFrom_output;?></b><br/><input type="text" class="datepicker" name="date_from" id="resource_date0" value="" autocomplete="off"/>

		<div class="save" data-resource-save-id="0"><?php echo $formText_Save_LID8895?></div>
		<div class="cancel" data-resource-save-cancel="0"><?php echo $formText_Cancel_LID8959?></div>
	</div>
	<div class="addNew">
		<div class="plusTextBox active">
			<div class="plusBox addres"><div class="lineH"></div><div class="lineV"></div></div>
			<div class="text"><?php echo $formText_AddPriceList_LID30425; ?></div>
		</div>
		<div class="clear"></div>
	</div>

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
		// 					$(".popupform .errorMessage").html("<?php echo $formText_ErrorChangingResourceOrder_LID4473;?>").show();
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
				resourceDate = $("#resource_date"+resourceID).val(),
				resourceFee = $("#resource_fee"+resourceID).val(),
				resourceEhf = $("#resource_ehf"+resourceID).val(),
				self = $(this);
		        var action = $(".resourceList").data("action2");
			$.ajax({
				type: 'POST',
				url: action,
				cache: false,
				data: 'fwajax=1&fw_nocss=1&editResource=' + resourceID + '&output_form_submit=1&price_per_print='+resourceName+'&price_per_fee='+resourceFee+'&price_per_ehf='+resourceEhf+'&date_from='+resourceDate,
				success: function(result){
					if(parseInt(result.html) > 0){
						ajaxCall('add_price_list', {}, function(obj) {
				            $('#popupeditboxcontent').html('');
				            $('#popupeditboxcontent').html(obj.html);
				            out_popup = $('#popupeditbox').bPopup(out_popup_options);
				            $("#popupeditbox:not(.opened)").remove();
				        });
					} else {
						$(".popupform .errorMessage").html("<?php echo $formText_ErrorSavingResource_LID4483;?>").show();
					}
				}
			});
		})

		$("[data-resource-save2-cancel").unbind("click").on('click', function(e){
			e.preventDefault();
			$(this).parents(".editRow").hide();
		})
        $("[data-edit-resource2-first]").unbind("click").on('click', function(e){
			e.preventDefault();
			$(this).parents(".resourceRow2").next(".deleteRow").hide();
			$(this).parents(".resourceRow2").next().next(".editRow").show();
		});
        // Delete resource
		$("[data-delete-resource2-first").unbind("click").on('click', function(e){
			e.preventDefault();
			$(this).parents(".resourceRow2").next().next(".editRow").hide();
			$(this).parents(".resourceRow2").next(".deleteRow").show();
		})
		$("[data-delete2-cancel").unbind("click").on('click', function(e){
			e.preventDefault();
			$(this).parents(".deleteRow").hide();
		})
		$("[data-delete-resource2-id]").unbind("click").on('click', function(e){
			e.preventDefault();
			var resourceName = $(this).parents(".resourceRow2").find(".nameColumn .columnWrapper").html();
			var self = $(this);
	        var action = $(".resourceList").data("action2");
			$.ajax({
				type: 'POST',
				url: action,
				cache: false,
				data: 'output_form_submit=1&fwajax=1&fw_nocss=1&deleteSubResource=' + $(this).data('delete-resource2-id'),
				success: function(result){
					if(parseInt(result.html) != 0){
						var deleteRow = self.parents(".deleteRow");
						deleteRow.hide();
						deleteRow.prev('.resourceRow2').remove();
						$(".popupform .errorMessage").hide();
					} else {
						$(".popupform .errorMessage").html("<?php echo $formText_ErrorDeletingResource_LID4496;?>").show();
					}
				}
			});
		});

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
				success: function(result){
					if(parseInt(result.html) != 0){
						var deleteRow = self.parents(".deleteRow");
						deleteRow.hide();
						deleteRow.prev('.resourceRow').remove();
						$(".popupform .errorMessage").hide();
					} else {
						$(".popupform .errorMessage").html("<?php echo $formText_ErrorDeletingResource_LID8126;?>").show();
					}
				}
			});
		});

		$(".popupform .addNew").unbind("click").bind("click", function(){
			$(".newResource").show();
		})
        $(".popupform .addNewSubType").unbind("click").bind("click", function(){
            $(this).parents(".subtypes").find(".newResource2").show();
		})
        $(".popupform .addNewSelfdefined").unbind("click").bind("click", function(){
            $(this).parents(".subtypes").find(".newResource3").show();
		})
		
		$('.datepicker').datepicker({
			firstDay: 1,
			dateFormat: 'dd.mm.yy'
		})  
	}
});
</script>
<style>
.subtypeWrapper {
    margin-top: 10px;
    border-top: 1px solid #cecece;
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
.addNewSubType .plusTextBox {
    float: none;
    margin-top: 10px;
    margin-left: 15px;
    cursor: pointer;
}
.addNewSelfdefined .plusTextBox {
    float: none;
    margin-top: 10px;
    margin-left: 15px;
    cursor: pointer;
}
.resourceList {
	margin-bottom: 20px;
}
.resourceRow .column {
	float: left;
}
.resourceRow .column .columnWrapper {
	padding: 5px 4px;
}
.resourceRow .nameColumn {
	width: 30%;
	font-size: 14px;
}
.resourceRow .typeColumn {
	width: 30%;
	font-size: 14px;
}
.resourceRow .statusColumn {
	width: 30%;
}
.resourceRow .actionColumn {
	width: 15%;
	text-align: right;
}
.resourceRow .statusColumn .selectDiv select {
	padding: 5px 30px 4px 10px;
}
.resourceRow .statusColumn .selectDiv .arrowDown {
	top: 14px;
}
.resourceRow .statusColumn .selectDiv .active {
	color: #0091e8;
}
.resourceRow .statusColumn .selectDiv .inactive {
	color: #F7640B;
}
.resourceRow .statusColumn .selectDiv.active {
	border: 1px solid #0091e8;
	font-weight: bold;
	-webkit-border-radius: 3px;
	-moz-border-radius: 3px;
	border-radius: 3px;
	color: #0091e8;
}
.resourceRow .statusColumn .selectDiv.active .arrowDown {
	border-left: 5px solid transparent;
	border-right: 5px solid transparent;
	border-top: 5px solid #0091e8;
}
.resourceRow .statusColumn .selectDiv.inactive {
	border: 1px solid #F7640B;
	font-weight: bold;
	-webkit-border-radius: 3px;
	-moz-border-radius: 3px;
	border-radius: 3px;
	color: #F7640B;
}
.resourceRow .statusColumn .selectDiv.inactive .arrowDown {
	border-left: 5px solid transparent;
	border-right: 5px solid transparent;
	border-top: 5px solid #F7640B;
}
.resourceRow .actions li {
	display: inline-block;
	vertical-align: middle;
	margin: 0px 5px;
}
.deleteRow, .deleteChildRow {
	display: none;
	padding: 5px 0px;
}
.deleteRow .actions li a, .deleteChildRow .actions li a {
	text-decoration: none;
	color: inherit;
}
.deleteRow .actions li.delete, .deleteChildRow .actions li.delete {
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
	margin-right: 10px;
}
.deleteRow .actions li.cancel, .deleteChildRow .actions li.cancel {
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
.editRow, .editChildRow {
	display: none;
	padding: 5px 0px;
}
.editRow a, .editChildRow a {
	text-decoration: none;
	color: inherit;
}
.editRow input, .editChildRow input {
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
.editRow input.checkbox, .editChildRow input.checkbox {
	width: auto;
	display: inline-block;
	vertical-align: middle;
	margin-top: 0px;
}
.editRow .editRowDivider, .editChildRow .editRowDivider {
	margin-bottom: 10px;
}
.editRow textarea, .editChildRow textarea {
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
.editRow .save, .editChildRow .save {
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
.editRow .cancel, .editChildRow .cancel {
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
</style>
