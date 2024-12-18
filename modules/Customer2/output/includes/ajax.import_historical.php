<?php
//session_start();
$user = $variables->loggID?$variables->loggID:$_COOKIE['username'];
$developeraccess = $variables->developeraccess?$variables->developeraccess:$_POST['developeraccess'];

if(!isset($o_main))
{
	define('BASEPATH', realpath(__DIR__."/../../../../").DIRECTORY_SEPARATOR);
	include(BASEPATH."elementsGlobal/cMain.php");
}

$v_path = explode("/", realpath(__DIR__."/../"));
$s_module = array_pop($v_path);

$s_sql = "select * from session_framework where companyaccessID = ? and session = ? and username = ?";
$o_query = $o_main->db->query($s_sql, array($_GET['caID'], $_COOKIE['sessionID'], $_COOKIE['username']));
if($o_query && $o_query->num_rows()>0){
	$fw_session = $o_query->row_array();
}
$people_contactperson_type = 2;
$sql = "SELECT * FROM accountinfo_basisconfig ORDER BY id";
$o_query = $o_main->db->query($sql);
$accountinfo_basisconfig = $o_query ? $o_query->row_array() : array();
if(intval($accountinfo_basisconfig['contactperson_type_to_use_in_people']) > 0){
	$people_contactperson_type = intval($accountinfo_basisconfig['contactperson_type_to_use_in_people']);
}
$o_query = $o_main->db->get('accountinfo');
$accountinfo = $o_query ? $o_query->row_array() : array();
if(intval($accountinfo['contactperson_type_to_use_in_people']) > 0)
{
	$people_contactperson_type = $accountinfo['contactperson_type_to_use_in_people'];
}


$s_sql = "SELECT * FROM customer_accountconfig ORDER BY id";
$o_query = $o_main->db->query($s_sql);
$v_customer_accountconfig = $o_query ? $o_query->row_array(): array();

$module="Customer2";
$v_module_access = json_decode($fw_session['cache_menu'],true);
$l_access = $v_module_access[$module][2];
$dbfields = array();
include(__DIR__."/../../input/settings/fields/customerhistoryextsystemfields.php");
foreach($prefields as $fieldinfo)
{
	$fieldinfo = explode('¤', $fieldinfo);
	$dbfields[] = $fieldinfo[0];
}

if($_POST['output_form_submit'] == 1)
{
	if($l_access < 10){
		$fw_error_msg = array($formText_NoAccess_output);
		return;
	}
	$approved = $_POST['approved'];

	$insertTable = $_POST['table']; //'overview';
	// echo $_SERVER['HTTP_REFERER'];
	// print_r($_POST);	die();
	$spliter = $_POST['spliter'];
	if($spliter == "t"){
		$spliter = "\t";
	}
	$rows = explode("\n", str_replace("\"", "", $_POST['csv']));
	$header = $rows[0];
	$headers = explode($spliter, $header);
	unset($rows[0]);

	for ( $i = 1; $i <= sizeof($rows); $i++ ) {
		$rowValues = explode($spliter,$rows[$i]);
		for ( $j = 0; $j < sizeof($headers); $j++ ) {
			$csv[$i][trim($headers[$j])] = trim($rowValues[$j]);
		}
		//break;
	}

	$relation = (array_filter($_POST['field']));
	if( is_array($_POST['customlabel']) && sizeof($_POST['customlabel']) ) {
		foreach($_POST['customlabel'] as $dbField=>$label) {
			$customLabels[] = $o_main->db_escape_name($dbField)." = ".$o_main->db->escape($label);
		}
	}
	$matches_found = array();
	$nomatches_found = array();
    $successfullyUpdatedCount = 0;

	$rowNumber = 0;
    $category_id = $_POST['category_id'];

    $ownercompanyIdPost = $_POST['ownercompany'];
    if($ownercompanyIdPost > 0){
        if($category_id > 0){
        	foreach($csv as $row) {
        		if(sizeof($customLabels)) {
        			$set = $customLabels;
        		}

        		$set[] = "created = NOW()";
        		$set[] = "createdby = ".$o_main->db->escape("imported - ".$user);

                $customer_external_id = "";

        		foreach($relation as $dbField=>$csvField) {
        			if(trim($row[$csvField]) != ""){
        				$rowData = $row[$csvField];
        				$rowData = str_replace(array("\n", "\t", "\r"), '', $rowData);
                        if($dbField != "customerNumber"){
                            $set[] = $o_main->db_escape_name($dbField)." = ".$o_main->db->escape($rowData);
                        } else {
							if($rowData!=""){
	                            $customer_external_id = $rowData;
							}
                        }
        			}
        		}

        		if(count($set) > 2 && $customer_external_id != ""){
					if($_POST['customerCompareField'] == 0){
						$o_query = $o_main->db->query("SELECT * FROM customer_externalsystem_id WHERE external_id = ? AND ownercompany_id = ?", array($customer_external_id, $ownercompanyIdPost));
					} else if($_POST['customerCompareField'] == 1){
						$o_query = $o_main->db->query("SELECT customer.id as customer_id FROM customer WHERE name = ?", array($customer_external_id));
					}
                    if($o_query && $o_query->num_rows()>0)
                    {
                        $external_item = $o_query->row_array();
                        $o_query = $o_main->db->query("SELECT * FROM customer WHERE id = ?", array($external_item['customer_id']));
                        $foundItem = $o_query ? $o_query->row_array() : array();
                        if($foundItem){
                            $matches_found[] = $customer_external_id;

                			$set[] = "moduleID = 41";
                			$set[] = "history_category_id = ".$o_main->db->escape($category_id);
                			$set[] = "customer_id = ".$o_main->db->escape($foundItem['id']);

                			$insertTable = $o_main->db_escape_name($insertTable);
                            if($approved){
                    			$s_sql  = "INSERT INTO ".$insertTable." SET ".implode(", ", $set).";";
                                if(!$o_main->db->query($s_sql)){
                                    $fw_error_msg[] = $o_main->db->error();
                                } else {
                                    $successfullyUpdatedCount++;
                                }
                            }
                        } else {
                            $nomatches_found[] = $customer_external_id;
                        }
                    } else {
						$nomatches_found[] = $customer_external_id;
					}
        		}
        		unset($set);
        		$rowNumber++;
        	}
        } else {
            $fw_error_msg[] = $formText_MissingCategory_output;
        }
    } else {
        $fw_error_msg[] = $formText_MissingOwnercompany_output;
    }
}

if($developeraccess >= 5) {
	?>
	<style>
	.labelSpan {
		display: inline-block;
		width: 140px;
	}
	#importFormHistorical {
		width: 800px;
		min-height: 300px;
	}
	.half {
		width: 50%;
		float: left;
	}
	.half1 {
		width: 45%;
		float: left;
	}
	.half2 {
		width: 55%;
		float: left;
	}
	#csvimportfields2 {
		*display: none;
	}

	#dbFields2 {
		min-height: 100px;
		width: 200px;
	}
	#dbFields2:after{
		display:block;
		content:"";
		clear:both;
	}
	#dbFields2 div {
		float: none;
		width: inherit;
		text-indent: 5px;
	}
	.draggable { cursor: move; }
	.droppable div { border: 1px dotted black; margin: 2px; background: #EEE; }
	.draggable { height: 20px; line-height: 20px; text-indent: 5px; }
	.label {
		display: none;
	}
	div.label {
		position: absolute;
		top: 0px;
		right: 0px;
		font-size: 9px;
		width: 180px;
		display: none;
	}
	#csvimportfields2 .droppable {
		width: 90%;
		clear: both;
		border: 1px dotted black; margin: 2px 0px;
		min-height: 26px;
		position: relative;

	}
	#csvimportfields2 .draggable {
		background: #DDD;
		float: right;
		width: 200px;
	}
	.prospectTypesSelect {
		display: none;
	}
	.employeeIdSelect {
		display: none;
	}
	.prospectValue {
		display: none;
	}
	.customerNameWrapper {
		display: none;
	}
	.customerNumberWrapper {
		display: block;
	}
	</style>


	<script type="text/javascript" src="../modules/<?php echo $module?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
	<script type="text/javascript" src="../modules/<?php echo $module?>/output/elementsOutput/encoding.min.js"></script>
	<script>
	$(".fancybox-wrap").unbind('mousewheel.fb');
	function generateDragable2(f,s) {
		$('#importFormHistorical #csvimportfields2 .draggable').remove();
		if(s == "t"){
			fields = f.split("\t");
		} else{
			fields = f.split(s);
		}
		$("#importFormHistorical #dbFields2").html('');
		$.each(fields , function(i, val) {
			if(val.trim() != ""){
				$("#importFormHistorical #dbFields2").append(' <div class="draggable" id="field_'+val+'" data-scope="'+val+'">'+val+'</div>');
			}
		});

		$('.draggable').each(function(index, div) {
			var scope = $(this).attr('data-scope');
			$(div).draggable({

				stop: function() {
					$('.droppable').droppable('option', 'disabled', false);
				},

				helper: 'clone'
			});
		});

		$('.draggable').on('mousedown', function(e) {

			$('.droppable').droppable({

				drop: function(event, ui) {

					var x = $(this).find('.draggable');

					if(!$(this).attr('id')==='dbFields2'){
						if(!x.length){
							$(this).append(ui.draggable);
						}else{

						}
					}else{
						$('#importFormHistorical #dbFields2').append($('.ui-draggable', this));

						$(this).append(ui.draggable);
						$(this).find('div.label').hide();

					}
				}
			});
		});
		<?php foreach($_POST['field'] as $key=>$value) {
			if(trim($value) != ""){
			?>
			$('#dbFields2 .draggable[data-scope="<?php echo $value?>"]').eq(0).appendTo($("#csv_<?php echo $key;?>"));
		<?php }
		}
	 	?>
		$(window).resize();
	}

	$(document).ready(function () {
		// $(".subscription_start_date").datepicker({
		// 	firstDay: 1,
        //     dateFormat: 'dd.mm.yy',
		// })
		var spliter = ",";
		$(".customerCompareField").change(function(){
			if($(this).val() == 0) {
				$(".customerNameWrapper").hide();
				$(".customerNumberWrapper").show();
			} else if($(this).val() == 1) {
				$(".customerNumberWrapper").hide();
				$(".customerNameWrapper").show();
			}
		}).change();
		$("#cancelimportButtonHistorical").off("click").on("click", function(e){
			e.preventDefault();
			$(".output_form_submit").val("0");
			$(".approved").val("0");
			$("form.output-form").submit();
		})
		$("#importFormHistorical #separator").change(function(e) {
			$("#importFormHistorical #spliter").val( $(this).val() );
			spliter = $(this).val();
			generateDragable2($('#importFormHistorical input[name="csvFields"]').val(), spliter);
		}).change();

		$("#importFormHistorical #filenameImport2").change(function(e) {
			var ext = $("#importFormHistorical input#filenameImport2").val().split(".").pop().toLowerCase();
			if($.inArray(ext, ["csv"]) == -1) {
				alert('Please upload CSV file!');
				return false;
			}

			if (e.target.files != undefined) {
				var file = e.target.files.item(0)
				var reader = new FileReader();
				reader.onload = function(e) {
					var codes = new Uint8Array(e.target.result);
					var encoding = Encoding.detect(codes);
					if(encoding == "UTF8" || encoding == "UNICODE" || encoding == "UTF32"){
						var reader2 = new FileReader();
						reader2.onload = function(e) {
							$('#importFormHistorical input[name="csv"]').val(e.target.result);
							var csvval=e.target.result.split("\n");
							var csvvalue=csvval[0];
							$('#importFormHistorical input[name="csvFields"]').val(csvvalue);
							generateDragable2($('#importFormHistorical input[name="csvFields"]').val(), spliter);
						};
						reader2.readAsText(file);
					} else {
						alert("<?php echo $formText_PossibleToUploadOnlyFilesInUTF8Encoding_output;?>");
					}
				};
				reader.readAsArrayBuffer(file);

			}
			$('#importFormHistorical #importButtonHistorical').prop("disabled", false);
			$('#importFormHistorical #csvimportfields2 a.label').show();

			return false;
		});

        $("form.output-form").validate({
    		submitHandler: function(form) {
    			fw_loading_start();

                $('#importFormHistorical #csvimportfields2 .boxes input.fieldsToCheck').each(function(index, div) {
    				if(!$(this).val() || $(this).parent().parent().find('.draggable').length) {
    					$(this).remove();
    				}
    			});

    			$('#importFormHistorical #csvimportfields2 .droppable').each(function(index, div) {
    				var csvField = $(this).attr('id').replace('csv_','');
    				var dbFiled = 0;
    				if($(this).find('.draggable').length) {
    					dbFiled = $(this).find('.draggable').attr('id').replace('field_','');
    					if($(this).hasClass("contactPerson1")){
    						$('#importFormHistorical input[name="field[contactperson1_'+csvField+']"]').val(dbFiled);
    					} else if ($(this).hasClass("contactPerson2")) {
    						$('#importFormHistorical input[name="field[contactperson2_'+csvField+']"]').val(dbFiled);
    					} else if ($(this).hasClass("ownerFromDate")) {
    						$('#importFormHistorical input[name="field['+csvField+']"]').val(dbFiled);
    					} else if ($(this).hasClass("selfdefined")) {
    						$('#importFormHistorical input[name="field['+csvField+']"]').val(dbFiled);
    					} else if ($(this).hasClass("customerNumber")) {
    						$('#importFormHistorical input[name="field[customerNumber]"]').val(dbFiled);
    					} else {
    						$('#importFormHistorical input[name="field['+csvField+']"]').val(dbFiled);
    					}

    				}
    			});
				$("#popup-validate-message").hide();
    			$.ajax({
    				url: $(form).attr("action"),
    				cache: false,
    				type: "POST",
    				dataType: "json",
    				data: $(form).serialize(),
    				success: function (data) {
    					fw_loading_end();
    					if(data.error !== undefined)
    					{
    						$.each(data.error, function(index, value){
    							var _type = Array("error");
    							if(index.length > 0 && index.indexOf("_") > 0) _type = index.split("_");
								$("#popup-validate-message").html(value, true);
    						});
		    				$("#popup-validate-message").show();
    						fw_click_instance = fw_changes_made = false;
    					} else {
							$('#popupeditboxcontent').html('');
							$('#popupeditboxcontent').html(data.html);
							out_popup = $('#popupeditbox').bPopup(out_popup_options);
							$("#popupeditbox:not(.opened)").remove();
    					}
    				}
    			}).fail(function() {
    				$("#popup-validate-message").html("<?php echo $formText_ErrorOccuredSavingContent_Output;?>", true);
    				$("#popup-validate-message").show();
    				$('#popupeditbox').css('height', $('#popupeditboxcontent').height());
    				fw_loading_end();
    			});
    		},
    		invalidHandler: function(event, validator) {
    			var errors = validator.numberOfInvalids();
    			if (errors) {
    				var message = errors == 1
    				? '<?php echo $formText_YouMissed_validate; ?> 1 <?php echo $formText_field_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>'
    				: '<?php echo $formText_YouMissed_validate; ?> ' + errors + ' <?php echo $formText_fields_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>';

    				$("#popup-validate-message").html(message);
    				$("#popup-validate-message").show();
    				$('#popupeditbox').css('height', $('#popupeditboxcontent').height());
    			} else {
    				$("#popup-validate-message").hide();
    			}
    			setTimeout(function(){ $('#popupeditbox').height(''); }, 200);
    		}
    	});

        $(".category_id").change(function(){
            var category_id = $(this).val();
            $(".category_label").hide();
            $(".category_label"+category_id).show();
            $(".fieldWrapper .category_droppable").show();
            $(".fieldWrapper .category_droppable").each(function(){
                var visibleLength = 0
                $(this).find(".category_label").each(function(){
                    if($(this).is(":visible")){
                        visibleLength++
                    }
                })
                if(visibleLength == 0){
                    $(this).hide();
                } else {
                    $(this).show();
                }
            })
        }).change();
	});
	</script>
	<?php
	ob_start();
	?>
		<div class="popupform" id="importFormHistorical">
			<form class="output-form"  method="post" action="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=import_historical";?>" accept-charset="UTF-8">
            <input type="hidden" name="fwajax" value="1">
        	<input type="hidden" name="fw_nocss" value="1">
        	<input type="hidden" name="output_form_submit" class="output_form_submit" value="1">

			<?php
			if($_POST['output_form_submit'] == 1){
                if($_POST['approved']){
					echo $successfullyUpdatedCount. " ".$formText_EntriesSuccessfullyUpdated_output;
				} else {
    				?>

                    <input type="hidden" name="approved" class="approved" value="1">

                    <b><?php echo $formText_MatchesFound_output." ".count($matches_found);?></b><br/>
                    <b><?php echo $formText_NoMatchesFound_output." ".count($nomatches_found);?></b><br/>
					<?php
					foreach($nomatches_found as $nomatche_found){
						echo $nomatche_found."</br>";
					}
					?>

    				<input type="button" name="" id="cancelimportButtonHistorical" value="<?php echo $formText_Cancel_output;?>">
    				<input type="submit" name="" id="importButtonHistorical" value="<?php echo $formText_Approve_output;?>">
    				<?php
                }
			}
			?>
			<div style="<?php if($_POST['output_form_submit'] == 1){ echo 'display: none;'; } ?>">
				<p align="center">
					<b><?php echo $formText_PleaseSelectCsvFileForImport_output;?>:</b> <input type="file" value="<?php echo $_POST['filenameImport'];?>" name="filenameImport" id="filenameImport2">
				</p>

				<p align="center">
					<b><?php echo $formText_PleaseSelectCsvFileSeperatorValue_output;?>:</b>
					<select name="separator" id="separator" autocomplete="off">
						<option value="," <?php if($_POST['separator'] == ",") echo 'selected';?>>, (commas)</option>
						<option value=";" <?php if($_POST['separator'] == ";") echo 'selected';?>>; (semi-colons)</option>
						<option value=":" <?php if($_POST['separator'] == ":") echo 'selected';?>>: (colons)</option>
						<option value="|" <?php if($_POST['separator'] == "|") echo 'selected';?>>| (pipes)</option>
						<option value="t" <?php if($_POST['separator'] == "t") echo 'selected';?>>&nbsp;&nbsp;(tab)</option>
					</select>
					<br/>
					<br/>
					<?php echo $formText_FirstLineOfFileWillNotBeImported_output;?>
				</p>
				<p align="center">
					<input type="hidden" name="csvFields" value="<?php echo htmlspecialchars($_POST['csvFields'])?>">
				</p>

				<div class="half1">
					<div id="dbFields2" class="droppable bank">
					</div>
				</div>


				<div class="half2" id="csvimportfields2">
					<div id="popup-validate-message"></div>


					<input type="hidden" name="developeraccess" value="<?php echo $variables->developeraccess;?>" autocomplete="off">

					<div class="boxes">
                        <div class="fieldWrapper ownercompanyWrapper">
    						<label><?php echo $formText_OwnerCompany_output;?></label>
    						<?php
    						$ownercompanies = array();
    						$s_sql = "SELECT * FROM ownercompany";
    						$o_query = $o_main->db->query($s_sql);
    						if($o_query && $o_query->num_rows()>0) {
    							$ownercompanies = $o_query->result_array();
    						}
    						$default_own = $ownercompanies[0];
    						?>
    						<select name="ownercompany" required autocomplete="off">
    							<option value=""><?php echo $formText_SelectOwnerCompany_Output;?></option>
    							<?php foreach($ownercompanies as $ownercompany) { ?>
    								<option value="<?php echo $ownercompany['id'];?>" <?php if(count($ownercompanies) == 1 && $default_own['id'] == $ownercompany['id']) { echo 'selected';}?> <?php if(isset($_POST['ownercompany']) && $_POST['ownercompany'] == $ownercompany['id']) echo 'selected'; ?>><?php echo $ownercompany['name'];?></option>
    							<?php } ?>
    						</select>
							<div>
	    						<label><?php echo $formText_CompareField_output;?></label>
								<select name="customerCompareField" class="customerCompareField" required autocomplete="off">
	    							<option value="0" <?php if(isset($_POST['customerCompareField']) && $_POST['customerCompareField'] == 0) echo 'selected'; ?>><?php echo $formText_CustomerNumber_Output;?></option>
	    							<option value="1" <?php if(isset($_POST['customerCompareField']) && $_POST['customerCompareField'] == 1) echo 'selected'; ?>><?php echo $formText_CustomerName_Output;?></option>
	    						</select>
							</div>
    						<?php
    						$field = "customerNumber";
    						?>
    						<div id="csv_<?=$field?>" class="droppable customerNumber home" data-scope="<?=$field?>">
    							<span class="customerNumberWrapper"><?=$formText_CustomerNumber_output;?></span>
    							<span class="customerNameWrapper"><?=$formText_CustomerName_output;?></span>
								<sup><a class="label" href="#label_<?=$field?>" onclick="$($(this).attr('href')).parent().show(); return false;">label</a></sup>
    							<div class="label">
    								<input type="text" name="<?=$field?>" id="label_<?=$field?>" value="<?php echo $_POST[$field];?>" placeholder="<?=$field?>" autocomplete="off">
    								<a href="#label_<?=$field?>" onclick="$($(this).attr('href')).val('').parent().hide(); return false;">[x]</a>
    							</div>
    						</div>
    					</div>
                        <div style="margin-bottom: 5px;">
							<span class="labelSpan">
								<?php echo $formText_Category_output;?>:
							</span>
							<select name="category_id" class="category_id" autocomplete="off" required>
								<option value="0"><?php echo $formText_Choose_output;?></option>
								<?php
								$s_sql = "SELECT * FROM customerhistoryextsystemcategory WHERE content_status < 2 ORDER BY name";
								$o_query = $o_main->db->query($s_sql);
								$groups = ($o_query ? $o_query->result_array():array());

								foreach($groups as $group) {
									?>
									<option value="<?php echo $group['id']?>" <?php if($_POST['category_id'] == $group['id']) echo 'selected';?>><?php echo $group['name'];?></option>
									<?php
								}
								?>
							</select>
						</div>
					<div class="fieldWrapper">
						<?php
						// $defaultFields = array('id','moduleID','createdBy','created','updatedBy','updated','origId','sortnr','seotitle','seodescription','seourl', 'content_status',
						// 'companyType', 'notOverwriteByImport', 'consideredIrrelevant', 'creditApproved', 'creditLimit', 'textVisibleInMyProfie',
						// 'numberOfUnits', 'associationId', 'housingcooperativeType', 'getynet_customer_id', 'create_filearchive_folder', 'articlePriceMatrixId', 'articleDiscountMatrixId',
						// 'user_registration', 'user_registration_link', 'user_registration_token', 'user_registration_domain', 'ownerFromDate', 'industries','financialYear', 'revenue',
						// 'municipalityName', 'publicRegisterContactperson', 'publicRegisterContactpFunction', 'revenueManuallyAdded', 'revenueManuallyAddedYear', 'numberOfEmplyees', 'comments',
						// 'textVisibleInMyProfile', 'industryCode', 'industryText', 'customerType', 'iaStreet1', 'iaStreet2', 'iaPostalNumber', 'iaCity', 'iaCountry', 'useOwnInvoiceAdress',
						// 'mobile', 'fax', 'middlename', 'lastname', 'personnumber', 'birthdate'
						// );
						$defaultFields = array('field_1', 'field_2', 'field_3', 'field_4', 'field_5', 'field_6', 'field_7', 'field_8', 'field_9', 'field_10');
						foreach($dbfields as $field) {
							if(in_array($field, $defaultFields) ) {
						?>
							<div id="csv_<?=$field?>" class="droppable category_droppable home" data-scope="<?=$field?>">
								<span class="category_label"><?=$field?></span>
                                <?php foreach($groups as $group) {
                                    if($group[$field."_label"] != ""){
                                        ?>
                                        <span class="category_label category_label<?php echo $group['id']?>"><?=$group[$field."_label"];?></span>
                                        <?php
                                    }
                                }?>
                                <sup><a class="label" href="#label_<?=$field?>" onclick="$($(this).attr('href')).parent().show(); return false;">label</a></sup>
								<div class="label">
									<input type="text" class="fieldsToCheck" name="customlabel[<?=$field?>]" id="label_<?=$field?>" value="<?php echo $_POST['customlabel'][$field];?>" placeholder="<?=$field?>" autocomplete="off">
									<a href="#label_<?=$field?>" onclick="$($(this).attr('href')).val('').parent().hide(); return false;">[x]</a>
								</div>
							</div>
						<?php }
						}
						?>
					</div>

					<input type="submit" name="" id="importButtonHistorical" value="IMPORT" <?php if(!isset($_POST['output_form_submit'])) { ?>disabled="disabled"<?php } ?>>
					<input type="hidden" name="csv" value="<?php echo htmlspecialchars($_POST['csv']);?>">
					<input type="hidden" name="spliter" id="spliter" value=",">
					<input type="hidden" name="table" value="customerhistoryextsystem">
                    <input type="hidden" name="field[customerNumber]" value="<?php echo $_POST['field']['customerNumber'];?>">

					<?php foreach($dbfields as $field) { ?>
						<input type="hidden" name="field[<?=$field?>]" value="<?php echo $_POST['field'][$field];?>">
					<?php } ?>


					</div>
				</div>
			</div>
			<div style="clear: both;"></div>
			</form>
		</div>
	<?php
	$s_popup = ob_get_clean();
	?>

    <?php echo $s_popup;?>
<?php
}
?>
