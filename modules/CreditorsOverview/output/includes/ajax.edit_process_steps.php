<?php
$s_sql = "SELECT * FROM collecting_cases_pdftext WHERE content_status < 2 ORDER BY sortnr ASC";
$o_query = $o_main->db->query($s_sql);
$pdfs = $o_query ? $o_query->result_array() : array();

$s_sql = "SELECT * FROM debtcollectionlatefee WHERE content_status < 2 ORDER BY sortnr ASC";
$o_query = $o_main->db->query($s_sql);
$articles = $o_query ? $o_query->result_array() : array();


if($moduleAccesslevel > 10)
{
	if(isset($_POST['output_form_submit']))
	{
        if($_POST['editSub']){
			$parentId = intval($_POST['parent_id']);
            if($_POST['editResource'] > 0) {
    			$s_sql = "UPDATE collecting_cases_process_steps SET
    			updated = now(),
    			updatedBy= ?,
    			name= ?,
                days_after_prev_step = ?,
                collecting_cases_process_id = ?,
				collecting_cases_pdftext_id = ?,
				claim_type_2_article = ?,
				claim_type_3_article = ?
    			WHERE id = ?";
    			$o_main->db->query($s_sql, array($variables->loggID, $_POST['resourceName'], $_POST['resourceDays'], $parentId, $_POST['resourcePdftext'], $_POST['resourceArticle'], $_POST['resourceArticle2'], $_POST['editResource']));
    			$fw_return_data = $_POST['editResource'];
    		}
    		else if(intval($_POST['deleteResource']) == 0) {

    			$s_sql = "INSERT INTO collecting_cases_process_steps SET
    			id=NULL,
    			moduleID = ?,
    			created = now(),
    			createdBy= ?,
    			name= ?,
                days_after_prev_step = ?,
                collecting_cases_process_id = ?,
				collecting_cases_pdftext_id = ?,
				claim_type_2_article = ?,
				claim_type_3_article = ?";
    			$o_main->db->query($s_sql, array($moduleID, $variables->loggID, $_POST['resourceName'], $_POST['resourceDays'], $parentId, $_POST['resourcePdftext'], $_POST['resourceArticle'], $_POST['resourceArticle2']));
    			$fw_return_data = $o_main->db->insert_id();
    		} else {
    			$s_sql = "DELETE collecting_cases_process_steps FROM collecting_cases_process_steps WHERE collecting_cases_process_steps.id = ?";
    			$o_main->db->query($s_sql, array($_POST['deleteResource']));
    			$fw_return_data = $_POST['deleteResource'];
    		}
		} else {
    		if($_POST['editResource'] > 0) {
    			$s_sql = "UPDATE collecting_cases_process SET
    			updated = now(),
    			updatedBy= ?,
    			name= ?
    			WHERE id = ?";
    			$o_main->db->query($s_sql, array($variables->loggID, $_POST['resourceName'], $_POST['editResource']));
    			$fw_return_data = $_POST['editResource'];
    		}
    		else if(intval($_POST['deleteResource']) == 0) {

    			$s_sql = "INSERT INTO collecting_cases_process SET
    			id=NULL,
    			moduleID = ?,
    			created = now(),
    			createdBy= ?,
    			name= ?";
    			$o_main->db->query($s_sql, array($moduleID, $variables->loggID, $_POST['resourceName']));
    			$fw_return_data = $o_main->db->insert_id();
    		} else {
    			$s_sql = "DELETE collecting_cases_process, collecting_cases_process_steps FROM collecting_cases_process LEFT JOIN collecting_cases_process_steps ON collecting_cases_process_steps.collecting_cases_process_id = collecting_cases_process.id WHERE collecting_cases_process.id = ?";
    			$o_main->db->query($s_sql, array($_POST['deleteResource']));
    			$fw_return_data = $_POST['deleteResource'];
    		}
        }
		echo $fw_return_data;
		$fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=list";
		return;
	}
    if(isset($_POST['sort'])) {
        $sortnr = 1;
        foreach($_POST['sort'] as $stepId){
            $s_sql = "UPDATE collecting_cases_process SET
            updated = now(),
            updatedBy= ?,
            sortnr = ?
            WHERE id = ?";
            $o_main->db->query($s_sql, array($variables->loggID, $sortnr, $stepId));
            $sortnr++;
        }
    }
    if(isset($_POST['sortSub'])) {
        $sortnr = 1;
        foreach($_POST['sortSub'] as $stepId){
            $s_sql = "UPDATE collecting_cases_process_steps SET
            updated = now(),
            updatedBy= ?,
            sortnr = ?
            WHERE id = ?";
            $o_main->db->query($s_sql, array($variables->loggID, $sortnr, $stepId));
            $sortnr++;
        }
    }
}
?>
<div class="popupform">
	<div id="popup-validate-message" style="display:none;"></div>

	<div class="popupformTitle"><?php echo $formText_AddEditProcessSteps_output;?> <?php echo $resource['name']?></div>
	<div class="errorMessage"></div>

	<div class="resourceList" data-action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=edit_process_steps";?>"
	data-action2="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=edit_process_steps";?>">
	<?php
	$resources = array();
	$s_sql = "SELECT * FROM collecting_cases_process ORDER BY sortnr ASC";
	$o_query = $o_main->db->query($s_sql);
	if($o_query && $o_query->num_rows()>0) {
	    $resources = $o_query->result_array();
	}

	foreach($resources as $resource){
        $subResources = array();
        $s_sql = "SELECT * FROM collecting_cases_process_steps WHERE collecting_cases_process_id = ? ORDER BY sortnr ASC";
        $o_query = $o_main->db->query($s_sql, array($resource['id']));
        if($o_query && $o_query->num_rows()>0) {
            $subResources = $o_query->result_array();
        }
		?>
		<div class="resourceRowSortable" id="sort_<?php echo $resource['id']?>">
			<div class="resourceRow">
				<div class="column nameColumn">
					<div class="columnWrapper"><?php echo $resource['name']?></div>
				</div>
                <div class="column">
					<div class="columnWrapper">
						<div class="addSubResource">
							<div class="plusTextBox active">
								<div class="plusBox addres"><div class="lineH"></div><div class="lineV"></div></div>
								<div class="text"><?php echo $formText_AddStep_output;?></div>
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
                <div class="resourceSubBlock">
                    <?php foreach($subResources as $subResource) {?>
                        <div class="resourceSubRowSortable" id="sortSub_<?php echo $subResource['id']?>">
                			<div class="resourceSubRow">
                				<div class="column nameColumn">
                					<div class="columnWrapper"><?php echo $subResource['name']?></div>
                				</div>
                				<div class="column nameColumn">
                					<div class="columnWrapper"><?php echo $subResource['days_after_prev_step']?></div>
                				</div>
                				<div class="column actionColumn">
                					<div class="columnWrapper">
            							<ul class="actions">
            	                            <?php if ($moduleAccesslevel > 10): ?>
            	                                <?php if (!$owneraccess || ($owneraccess && $_GET['userID'] == $subResource['createdBy'])): ?>
            	    								<li class="edit">
            	    									<a href="" data-edit-resource2-first="<?php echo $subResource['id']; ?>" alt="<?php echo $formText_Edit_output;?>" title="<?php echo $formText_Edit_output;?>">
            	    										<span class="glyphicon glyphicon-edit"></span>
            	    									</a>
            	    								</li>
            	                                <?php endif; ?>
            	                            <?php endif; ?>

            	                            <?php if ($moduleAccesslevel > 100): ?>
            	                                <?php if (!$owneraccess || ($owneraccess && $_GET['userID'] == $subResource['createdBy'])): ?>
            	    								<li class="delete">
            	    									<a href="" data-delete-resource2-first="<?php echo $subResource['id']; ?>" alt="<?php echo $formText_Delete_output;?>" title="<?php echo $formText_Delete_output;?>">
            	    										<span class="glyphicon glyphicon-trash"></span>
            	    									</a>
            	    								</li>
            	                                <?php endif; ?>
            	                            <?php endif; ?>
            							</ul>
                					</div>
                				</div>
                				<div class="clear"></div>
                            </div>
                			<?php if(!$subResource['disabledForEditing']) { ?>
                				<div class="deleteRow">
                					<ul class="actions">
                						<li class="delete">
                							<a href="" data-delete-resource2-id="<?php echo $subResource['id']; ?>" alt="<?php echo $formText_Delete_output;?>" title="<?php echo $formText_Delete_output;?>">
                								<?php echo $formText_Delete_output;?>
                							</a>
                						</li>
                						<li class="cancel">
                							<a href="" data-delete-cancel="1"><?php echo $formText_Cancel_output;?></a>
                						</li>
                					</ul>
                				</div>
                				<div class="editRow">
                                    <div class="editRowDiv">
                    					<label class="editRowLabel"><?php echo $formText_Name_output;?></label>
                                        <input type="text" name="resourceName" id="resSub<?php echo $resource['id']?>_<?php echo $subResource['id']?>" value="<?php echo $subResource['name']?>" autocomplete="off"/>
                                    </div>
                                    <div class="editRowDiv">
                    					<label class="editRowLabel"><?php echo $formText_DaysAfterPreviousStep_output;?></label>
                	                   <input type="text" name="resourceDays" id="resDays<?php echo $resource['id']?>_<?php echo $subResource['id']?>" value="<?php echo $subResource['days_after_prev_step']?>" autocomplete="off"/>
                                    </div>
									<?php /*
                                    <div class="editRowDiv">
                                        <label class="editRowLabel"><?php echo $formText_ScriptName_output;?></label>
                	                   <input type="text" name="resourceScript" id="resScript<?php echo $resource['id']?>_<?php echo $subResource['id']?>" value="<?php echo $subResource['script_name']?>" autocomplete="off"/>
                                   </div>*/?>

								   <div class="editRowDiv">
									   <label class="editRowLabel"><?php echo $formText_PdfText_output;?></label>
									   <select name="resourcePdftext" id="resPdf<?php echo $resource['id']?>_<?php echo $subResource['id']?>" autocomplete="off" >
										  <option value=""><?php echo $formText_Select_output;?></option>
										  <?php
										  foreach($pdfs as $pdf) {
											  ?>
											  <option value="<?php echo $pdf['id'];?>" <?php if($pdf['id'] == $subResource['collecting_cases_pdftext_id']) echo 'selected';?>><?php echo $pdf['title'];?></option>
											  <?php
										  }
										  ?>
									  </select>
								  </div>
								  <div class="editRowDiv">
									  <label class="editRowLabel"><?php echo $formText_LateFeeArticle_output;?></label>
									  <select name="resourceArticle" id="resArticle<?php echo $resource['id']?>_<?php echo $subResource['id']?>" autocomplete="off" >
										 <option value=""><?php echo $formText_None_output;?></option>
										 <?php

										 foreach($articles as $article) {
											 ?>
											 <option value="<?php echo $article['id'];?>" <?php if($article['id'] == $subResource['claim_type_2_article']) echo 'selected';?>><?php echo $article['article_name'];?></option>
											 <?php
										 }
										 ?>
									 </select>
								 </div>
								 <div class="editRowDiv">
									 <label class="editRowLabel"><?php echo $formText_DebtCollectionFeeArticle_output;?></label>
									 <select name="resourceArticle2" id="resArticle2<?php echo $resource['id']?>_<?php echo $subResource['id']?>" autocomplete="off" >
										<option value=""><?php echo $formText_None_output;?></option>
			   							<option value="1" <?php if(1 == $subResource['claim_type_3_article']) echo 'selected';?>><?php echo $formText_LightFee_output;?></option>
			   							<option value="2" <?php if(2 == $subResource['claim_type_3_article']) echo 'selected';?>><?php echo $formText_HeavyFee_output;?></option>
									</select>
								</div>
                					<div class="save" data-resource2-save-id="<?php echo $subResource['id']?>" data-parent-id="<?php echo $resource['id']?>"><?php echo $formText_Save_output?></div>
                					<div class="cancel" data-resource2-save-cancel="<?php echo $subResource['id']?>"><?php echo $formText_Cancel_output?></div>
                				</div>
                			<?php } ?>

                		</div>
                    <?php } ?>

					<div class="newSubResource editRow">
                        <div class="editRowDiv">
                            <label class="editRowLabel"><?php echo $formText_Name_output;?></label>
                            <input type="text" name="resourceName" id="resSub<?php echo $resource['id']?>_0" value="" autocomplete="off"/>
                        </div>
                        <div class="editRowDiv">
                            <label class="editRowLabel"><?php echo $formText_DaysAfterPreviousStep_output;?></label>
                           <input type="text" name="resourceDays" id="resDays<?php echo $resource['id']?>_0" value="" autocomplete="off"/>
                        </div>
						<?php /*
                        <div class="editRowDiv">
                            <label class="editRowLabel"><?php echo $formText_ScriptName_output;?></label>
                           <input type="text" name="resourceScript" id="resScript<?php echo $resource['id']?>_0" value="" autocomplete="off"/>
                       </div> */ ?>
					   <div class="editRowDiv">
						   <label class="editRowLabel"><?php echo $formText_PdfText_output;?></label>
						   <select name="resourcePdftext"  id="resPdf<?php echo $resource['id']?>_0" autocomplete="off">
							   <option value=""><?php echo $formText_Select_output;?></option>
							   <?php
							   foreach($pdfs as $pdf) {
								   ?>
								   <option value="<?php echo $pdf['id'];?>"><?php echo $pdf['title'];?></option>
								   <?php
							   }
							   ?>
						   </select>
					  </div>

					  <div class="editRowDiv">
						  <label class="editRowLabel"><?php echo $formText_LateFeeArticle_output;?></label>
						  <select name="resourceArticle" id="resArticle<?php echo $resource['id']?>_0" autocomplete="off" >
							 <option value=""><?php echo $formText_None_output;?></option>
							 <?php

							 foreach($articles as $article) {
								 ?>
								 <option value="<?php echo $article['id'];?>"><?php echo $article['article_name'];?></option>
								 <?php
							 }
							 ?>
						 </select>
					 </div>
					 <div class="editRowDiv">
						 <label class="editRowLabel"><?php echo $formText_DebtCollectionFeeArticle_output;?></label>
						 <select name="resourceArticle2" id="resArticle2<?php echo $resource['id']?>_0" autocomplete="off" >
							<option value=""><?php echo $formText_None_output;?></option>
   							<option value="1"><?php echo $formText_LightFee_output;?></option>
   							<option value="2"><?php echo $formText_HeavyFee_output;?></option>
						</select>
					</div>
						<div class="save" data-resource2-save-id="0" data-parent-id="<?php echo $resource['id']?>"><?php echo $formText_Save_output?></div>
						<div class="cancel" data-resource2-save-cancel="0"><?php echo $formText_Cancel_output?></div>
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
                    <div class="editRowDiv">
    					<label class="editRowLabel"><?php echo $formText_Name_output;?></label>
                        <input type="text" name="resourceName" id="res<?php echo $resource['id']?>" value="<?php echo $resource['name']?>" autocomplete="off"/>
                    </div>
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
        <div class="editRowDiv">
            <label class="editRowLabel"><?php echo $formText_Name_output;?></label>
    		<input type="text" name="resourceName" id="res0" value="" autocomplete="off"/>
        </div>
		<div class="save" data-resource-save-id="0"><?php echo $formText_Save_output?></div>
		<div class="cancel" data-resource-save-cancel="0"><?php echo $formText_Cancel_output?></div>
	</div>
	<div class="explanation"><?php echo $formText_DragAndDropToChangeOrder_output;?></div>
	<div class="addNew">
		<div class="plusTextBox active">
			<div class="plusBox addres"><div class="lineH"></div><div class="lineV"></div></div>
			<div class="text"><?php echo $formText_AddProcessStep_output; ?></div>
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
	bindPopupActions2();
	function bindPopupActions2(){
		$(".resourceList").sortable({
			update: function(event, ui) {
		        var info = $(this).sortable("serialize");
		        var action = $(this).data("action");
		        $.ajax({
					type: 'POST',
					url: action,
					data: info,
					success: function(result){
						// // success
						// if(result.result == 1){
						// 	$(".popupform .errorMessage").hide();
						// } else {
						// 	$(".popupform .errorMessage").html("<?php echo $formText_ErrorChangingResourceOrder_output;?>").show();
						// }
					}
				});
		    }
		});
        $(".resourceSubBlock").sortable({
			update: function(event, ui) {
		        var info = $(this).sortable("serialize");
		        var action = $(this).parents(".resourceList").data("action");
		        $.ajax({
					type: 'POST',
					url: action,
					data: info,
					success: function(result){
					}
				});
		    }
		});
		// Edit resource
		$("[data-edit-resource2-first]").unbind("click").on('click', function(e){
			e.preventDefault();
			$(this).parents(".resourceSubRow").next(".deleteRow").hide();
			$(this).parents(".resourceSubRow").next().next(".editRow").show();
			$(window).resize();
		});
		$("[data-resource2-save-cancel").unbind("click").on('click', function(e){
			e.preventDefault();
			$(this).parents(".editRow").hide();
			$(window).resize();
		})
		$("[data-resource2-save-id").unbind("click").on('click', function(e){
			e.preventDefault();
            var parent_id = $(this).data('parent-id');
			var resourceID = $(this).data('resource2-save-id'),
				resourceName = $("#resSub"+parent_id+"_"+resourceID).val(),
				resourceDays = $("#resDays"+parent_id+"_"+resourceID).val(),
				resourceScript = $("#resScript"+parent_id+"_"+resourceID).val(),
				resourcePdftext = $("#resPdf"+parent_id+"_"+resourceID).val(),
				resourceArticle = $("#resArticle"+parent_id+"_"+resourceID).val(),
				resourceArticle2 = $("#resArticle2"+parent_id+"_"+resourceID).val(),
				self = $(this);
		        var action = $("#popupeditbox .resourceList").data("action2");

            fw_loading_start();
			$.ajax({
				type: 'POST',
				url: action,
				dataType: 'json',
				cache: false,
				data: 'fwajax=1&fw_nocss=1&editResource=' + resourceID + '&editSub=1&output_form_submit=1&resourceName='+resourceName+'&resourceDays='+resourceDays+'&resourceScript='+resourceScript+'&parent_id='+parent_id+'&resourcePdftext='+resourcePdftext+'&resourceArticle='+resourceArticle+'&resourceArticle2='+resourceArticle2,
				success: function(result){
                    fw_loading_end();
					if(parseInt(result.html) > 0){
						var data = { };
						ajaxCall('edit_process_steps', data, function(obj) {
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
		$("[data-delete-resource2-first").unbind("click").on('click', function(e){
			e.preventDefault();
			$(this).parents(".resourceSubRow").next().next(".editRow").hide();
			$(this).parents(".resourceSubRow").next(".deleteRow").show();
			$(window).resize();
		})
		$("[data-delete-cancel").unbind("click").on('click', function(e){
			e.preventDefault();
			$(this).parents(".deleteRow").hide();
			$(window).resize();
		})
		$("[data-delete-resource2-id]").unbind("click").on('click', function(e){
			e.preventDefault();
			var resourceName = $(this).parents(".resourceSubRow").find(".nameColumn .columnWrapper").html();
			var self = $(this);
	        var action = $("#popupeditbox .resourceList").data("action2");
            fw_loading_start();
			$.ajax({
				type: 'POST',
				url: action,
				dataType: 'json',
				cache: false,
				data: 'output_form_submit=1&fwajax=1&fw_nocss=1&editSub=1&deleteResource=' + $(this).data('delete-resource2-id'),
				success: function(result){
                    fw_loading_end();
					if(parseInt(result.html) != 0){
						var deleteRow = self.parents(".deleteRow");
						deleteRow.hide();
						deleteRow.prev('.resourceSubRow').remove();
						$(".popupform .errorMessage").hide();
					} else {
						$(".popupform .errorMessage").html("<?php echo $formText_ErrorDeletingResource_output;?>").show();
					}
				}
			});
		});



		$("#popupeditbox .popupform .addNew").unbind("click").bind("click", function(){
			$("#popupeditbox .newResource").show();
		})

		$(".popupform .addSubResource").unbind("click").bind("click", function(){
			$(this).parents(".resourceRow").find(".newSubResource").show();
		})


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
				resourceName = $("#res"+resourceID).val(),
				self = $(this);
		        var action = $("#popupeditbox .resourceList").data("action2");
            fw_loading_start();
			$.ajax({
				type: 'POST',
				url: action,
				dataType: 'json',
				cache: false,
				data: 'fwajax=1&fw_nocss=1&editResource=' + resourceID + '&output_form_submit=1&resourceName='+resourceName,
				success: function(result){
                    fw_loading_end();
					if(parseInt(result.html) > 0){
						var data = { };
						ajaxCall('edit_process_steps', data, function(obj) {
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
		$("[data-delete-resource-first").unbind("click").on('click', function(e){
			e.preventDefault();
			$(this).parents(".resourceRow").next().next(".editRow").hide();
			$(this).parents(".resourceRow").next(".deleteRow").show();
			$(window).resize();
		})
		$("[data-delete-cancel").unbind("click").on('click', function(e){
			e.preventDefault();
			$(this).parents(".deleteRow").hide();
			$(window).resize();
		})
		$("[data-delete-resource-id]").unbind("click").on('click', function(e){
			e.preventDefault();
			var resourceName = $(this).parents(".resourceRow").find(".nameColumn .columnWrapper").html();
			var self = $(this);
	        var action = $("#popupeditbox .resourceList").data("action2");
            fw_loading_start();
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
	}
});
</script>
<style>
.addSubResource {
    cursor: pointer;
}
.resourceSubBlock {
    margin-left: 10px;
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
.deleteRow {
	display: none;
	padding: 5px 0px;
}
.deleteRow .actions li a {
	text-decoration: none;
	color: inherit;
}
.deleteRow .actions li.delete {
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
.deleteRow .actions li.cancel {
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
.editRow {
	display: none;
	padding: 5px 0px;
}
.editRowDiv {
    margin-bottom: 5px;
}
.editRowLabel {
    width: 150px;
    display: inline-block !important;
    vertical-align: middle;
}
.editRow a {
	text-decoration: none;
	color: inherit;
}
.editRow input {
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
.editRow .cancel {
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
.form .addNew {
	padding: 15px 0px;
}
.form .addNew .plusTextBox {
	float: left;
	margin-left: 20px;
	font-size: 14px;
	cursor: pointer;
}
.form .addNew .plusTextBox .plusBox {
	top: 5px;
}
.form .explanation {
}
.form .newResource {
	display: none;
}
.form .errorMessage {
	padding: 10px 0px;
	display: none;
	color: #F7640B;
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
