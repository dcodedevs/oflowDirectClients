<?php
$counter = 0;
$include_sublist = true;
$s_file = $extradir."/input/settings/relations/$submodule.php";
if(is_file($s_file))
{
	include($s_file);
	$old_extradir = $extradir;
	$extradir_account = explode("/",$extradir);
	array_pop($extradir_account);
	array_pop($extradir_account);
	$extradir_account = implode("/",$extradir_account);
	for($x = 0; $x < sizeof($prerelations); $x++)
	{
		$prerelationsarray = explode("¤",$prerelations[$x]);

		if($showMe[$prerelationsarray[2]] != 1)
		{
			if($prerelationsarray[4] == 1)
			{
				$s_content_status_filter = $prerelationsarray[8];
				foreach($listFieldVariables as $var) unset(${$var});
				unset($preShowDeleteAllSubcontentButton);
				$extradir = $extradir_account."/modules/".$prerelationsarray[1];
				$relationvalue = array();
				$o_query = $o_main->db->query("SELECT ".$submodule.".* FROM ".$submodule." WHERE ".$submodule.".id = ?".($o_main->multi_acc?" AND account_id = '".$o_main->db->escape_str($o_main->account_id)."'":""), array($_GET['ID']));
				if($o_query && $o_query->num_rows()>0) $relationvalue = $o_query->row_array();
				include(ACCOUNT_PATH."/modules/".$prerelationsarray[1]."/input/languagesInput/empty.php");
				include(ACCOUNT_PATH."/modules/".$prerelationsarray[1]."/input/languagesInput/default.php");
				if($fw_session['developeraccess']!=20) include(ACCOUNT_PATH."/modules/".$prerelationsarray[1]."/input/languagesInput/".$choosenListInputLang.".php");

				$prelistButtonCreate = $preinputformName = $preexpandSublist = $relation_link_to_module_id = $close_opened_items_after_reload = "";
				$childmodule = array();
				$childmodulename = array();
				$prefields = array();
				$databases = array();
				$fields = array();
				$file = ACCOUNT_PATH."/modules/".$prerelationsarray[1]."/input/settings/tables/".$prerelationsarray[2].".php";
				include($file);
				if(isset($prerelationsarray[9]) && $prerelationsarray[9] != "") $preinputformName = $prerelationsarray[9];
				if(isset($prerelationsarray[10]) && $prerelationsarray[10] > 0) $preperPage = $prerelationsarray[10];
                else $preperPage = 20;
				if(isset($prerelationsarray[11])) $preexpandSublist = $prerelationsarray[11];
				if(isset($prerelationsarray[13])) $relation_link_to_module_id = $prerelationsarray[13];
				if(isset($prerelationsarray[14])) $close_opened_items_after_reload = $prerelationsarray[14];

				foreach($mysqlTableName as $child)
				{
					$subValue = array();
					$subChild = explode(":",$child);
					foreach($subChild as $outname)
					{
						$subValue[] = $outname;
					}
					$datbas = new DatabaseTable();
					$datbas->start($subValue[0],$subValue[1],$subValue[2],$subValue[3]);
					if($settingsChoice_maxLevel_inputMenuLevels < $subValue[3])
					{
						$settingsChoice_maxLevel_inputMenuLevels = $subValue[3];
					}
					$databases[$subValue[0]] = $datbas;
				}
				$fieldCounter = 0;
				include(ACCOUNT_PATH."/modules/".$prerelationsarray[1]."/input/settings/fields/".$prerelationsarray[2]."fields.php");
				foreach($prefields as $child)
				{
					$addToPre = explode("¤",$child);
					$tempre = $addToPre[6];
					$addToPre[6] = array();
					$addToPre[6]['all'] = $tempre;
					$addToPre[7] = $databases[$addToPre[3]]->multilanguage;
					$fields[] = $addToPre;
					$databases[$addToPre[3]]->fieldNums[] = $fieldCounter;
					$fieldCounter++;
				}

				$relationstable = $o_main->db_escape_name($prerelationsarray[2]);
				if($preorderByField != '') $orderbyfield = " ORDER BY ".$relationstable.".".$o_main->db_escape_name($preorderByField);
				if($preorderByDesc == 1) $orderbyfield .= " DESC";

				$extraWhere = ($o_main->multi_acc?" AND account_id = '".$o_main->db->escape_str($o_main->account_id)."'":"");
				if($relation_link_to_module_id == 1)
				{
					$extraWhere .= " AND ".$relationstable.".moduleID = ".$o_main->db->escape($moduleID);
				} else if($linkToModuleID==1)
				{
					$moduleRow = array();
					$o_query = $o_main->db->query('SELECT * FROM moduledata WHERE name = ?', array($prerelationsarray[1]));
					if($o_query && $o_query->num_rows()>0) $moduleRow = $o_query->row_array();
					$extraWhere .= " AND ".$relationstable.".moduleID = ".$o_main->db->escape($moduleRow['uniqueID']?$moduleRow['uniqueID']:$moduleRow['id']);
				}
				$extraWhere .= " AND ".$relationstable.".content_status ".($s_content_status_filter!="" ? "= ".$o_main->db->escape($s_content_status_filter) : "< 2");

				$module_multi_table = false;
				if($o_main->db->table_exists($relationstable.'content')) $module_multi_table = true;

				if($module_multi_table)
				{
					$sqlSelect = "";
					$sqlExcludeColumns = array('id','moduleID','created','createdBy','updated','updatedBy','sortnr','origId');
					$o_fields = $o_main->db->list_fields($relationstable.'content');
					foreach($o_fields as $s_field)
					{
						if(!in_array($s_field,$sqlExcludeColumns)) $sqlSelect .= ', '.$relationstable.'content.'.$s_field;
					}

					$listSQL = "SELECT ".$relationstable.".id as linkID, ".$relationstable.".* ".$sqlSelect." FROM ".$relationstable."
					LEFT OUTER JOIN ".$relationstable."content ON ".$relationstable."content.".$relationstable."ID = ".$relationstable.".id AND ".$relationstable."content.languageID = ".$o_main->db->escape($s_default_output_language)."
					JOIN ".$submodule." ON ".$relationstable.".".$o_main->db_escape_name($prerelationsarray[3])." = ".$submodule.".".$o_main->db_escape_name($prerelationsarray[0])."
					WHERE ".$submodule.".id = ".$o_main->db->escape($_GET['ID']).$extraWhere.$orderbyfield;
				} else {
					$listSQL = "SELECT ".$relationstable.".id as linkID, ".$relationstable.".* FROM ".$relationstable."
					JOIN ".$submodule." ON ".$relationstable.".".$o_main->db_escape_name($prerelationsarray[3])." = ".$submodule.".".$o_main->db_escape_name($prerelationsarray[0])."
					WHERE ".$submodule.".id = ".$o_main->db->escape($_GET['ID']).$extraWhere.$orderbyfield;
				}

				$ordersql = htmlspecialchars(urlencode(base64_encode($listSQL)));
				$content_rows = 0;
				$o_query = $o_main->db->query($listSQL);
				$s_sql_all_rows = $listSQL;
				if($o_query) $content_rows = $o_query->num_rows();
				$content_pages = ceil($content_rows / $preperPage);
				$listSQL .= ($preperPage>0?" LIMIT 0,".$preperPage:"");
				$getContent = $o_main->db->query($listSQL);
				//echo "listSQL = $listSQL<br />";

				if($preinputformName == "") $preinputformName = $prerelationsarray[1].' ('.$relationstable.')';

				$prebuttonconfig = '';
				include(ACCOUNT_PATH."/modules/".$prerelationsarray[1]."/input/settings/buttonconfig/$relationstable"."list.php");
				ob_start();
				$startArray = $mainArray = $buttonsArray = array();
				$startArray = explode("¤",$prebuttonconfig);
				foreach($startArray as $ckArray)
				{
					$formArray = explode(":",$ckArray);
					if(sizeof($formArray) > 2 )
					{
						$mainArray[] = $formArray;
					}
				}
				foreach($mainArray as $buttonsArray)
				{
					$buttonSubmodule = $buttonsArray[6];
					$buttonModule = $buttonsArray[0];
					$buttonInclude = $buttonsArray[2];
					$buttonRelationModule = $buttonsArray[3];
					$buttonMode = $buttonsArray[4];
					if($buttonMode == 0)
					{
						?><li><?php
						include(ACCOUNT_PATH."/modules/".$prerelationsarray[1]."/input/buttontypes/$buttonInclude/button.php");
						?></li><?php
					}
				}
				if(isset($preShowDeleteAllSubcontentButton) && $preShowDeleteAllSubcontentButton == "1")
				{
					$ui_id_counter++;
					$button_ui_id = $buttonSubmodule."_".$ui_editform_id."_".$ui_id_counter;
					$s_subcontent_ids = '';
					$o_query = $o_main->db->query($s_sql_all_rows);
					if($o_query && $o_query->num_rows()>0)
					foreach($o_query->result() as $o_row)
					{
						$s_subcontent_ids .= ($s_subcontent_ids==''?'':',').$o_row->linkID;
					}
					if($s_subcontent_ids!="")
					{
						?><li>
							<script type="text/javascript" language="javascript">
							<?php if(isset($ob_javascript)) { ob_start(); } ?>
							$(function(){
								$("#<?php echo $button_ui_id;?>").off('click').on('click', function(e){
									var _current_location = window.location;
									e.preventDefault();
									if(!fw_changes_made && !fw_click_instance)
									{
										fw_click_instance = true;
										var s_msg_sufix = "";
										if($(this).data("name")) s_msg_sufix = ": " + $(this).data("name");
										bootbox.confirm({
											message:"<?php echo $formText_DeleteAllItems_input;?>" + s_msg_sufix + "?",
											buttons:{confirm:{label:"<?php echo $formText_Yes_input;?>"},cancel:{label:"<?php echo $formText_No_input;?>"}},
											callback: function(result){
												if(result)
												{
													fw_loading_start();
													remove_all_<?php echo $button_ui_id;?>([<?php echo $s_subcontent_ids;?>], _current_location);
												} else {
													fw_click_instance = false;
												}
											}
										});
									}
								});
							});
							function remove_all_<?php echo $button_ui_id;?>(v_items, _current_location)
							{
								var content_id = v_items.pop();
								$.ajax({
									url: "<?php echo $extradir."/input/delete.php?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$prerelationsarray[1]."&parentID=".$_GET['ID']."&deletemodule=".$prerelationsarray[2]."&submodule=".$prerelationsarray[2]."&relationID=".$relationvalue[$prerelationsarray[0]]."&relationfield=".$prerelationsarray[3]."&relationstable=".$relationstable.($s_content_status_filter!="" ? "&content_status=".$s_content_status_filter : "").($relation_link_to_module_id==1 ? "&relation_module_id=".$moduleID : "")."&deleteID=";?>"+content_id,
									cache: false,
									type: "GET",
									dataType: "json",
									success: function (data) {
										if(data.error !== undefined)
										{
											$.each(data.error, function(index, value){
												var _type = Array("error");
												if(index.length > 0 && index.indexOf("_") > 0) _type = index.explode("_");
												fw_info_message_add(_type[0], value);
											});
											fw_info_message_show();
											fw_loading_end();
											fw_click_instance = false;
										} else {
											if(v_items.length>0)
											{
												remove_all_<?php echo $button_ui_id;?>(v_items, _current_location);
											} else {
												fw_load_ajax(_current_location, '', false);
											}
										}
									}
								}).fail(function() {
									fw_info_message_add("error", "<?php echo $formText_ErrorOccuredDeletingContent_input;?>", true);
									fw_loading_end();
									fw_click_instance = false;
								});
							}
							<?php if(isset($ob_javascript)) { $ob_javascript .= " ".ob_get_clean(); } ?>
							</script>
							<a id="<?php echo $button_ui_id;?>" data-name="<?php echo $preinputformName;?>" role="menuitem"><?php echo $formText_DeleteAll_input;?></a>
						</li><?php
					}
				}
				$ob_buttons = ob_get_clean();
				$rand_id = rand(1,999);
				ob_start();
				if($ob_buttons!="")
				{
					?><div class="btn btn-sm dropdown"><a id="<?php echo "sl_btn_".$prerelationsarray[1]."_".$prerelationsarray[2]."_".$rand_id;?>" class="script" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="glyphicon glyphicon-menu-hamburger glyphicon-white"></span></a><ul class="dropdown-menu" role="menu" aria-labelledby="<?php echo "sl_btn_".$prerelationsarray[1]."_".$prerelationsarray[2]."_".$rand_id;?>"><?php echo $ob_buttons;?></ul></div><?php
				}
				$ob_buttons = ob_get_clean();

				?>
				<style>
				.subcontent-list.sublist<?php echo $x?> .column {
					float: left;
					word-wrap: break-word;
				}
				.subcontent-list.sublist<?php echo $x?> .row {
					margin-left: 0;
					margin-right: 0;
					padding: 2px 3px;
					-webkit-border-radius: 2px;
					-moz-border-radius: 2px;
					border-radius: 2px;
				}
				.subcontent-list.sublist<?php echo $x?> .input_list_item_body .row:hover {
					background-color: #eeeeee;
				}
				.subcontent-list.sublist<?php echo $x?> .inputListSet {
					display: none;
					float: left;
					width: 90%;
				}
				.subcontent-list.sublist<?php echo $x?> .head .col-md-1 {<?php /*?>This breaks Image fieldtype design!!! Added .head<?php */?>
					width: 10%;
					padding: 0;
				}
				.subcontent-list.sublist<?php echo $x?>.set1 .inputListSet1 {
					display: block;
				}
				.subcontent-list.sublist<?php echo $x?>.set2 .inputListSet2 {
					display: block;
				}
				.subcontent-list.sublist<?php echo $x?>.set3 .inputListSet3 {
					display: block;
				}
				.subcontent-list.sublist<?php echo $x?>.set4 .inputListSet4 {
					display: block;
				}
				<?php
				if($presubFieldInListWidth != null && $presubFieldInListWidth != ""){
					?>
					.subcontent-list.sublist<?php echo $x?> .column0 {
						width: <?php echo $presubFieldInListWidth?>%;
						margin-right: 0;
					}
					<?php
				}
				if($presubSecondFieldInListWidth != null && $presubSecondFieldInListWidth != ""){
					?>
					.subcontent-list.sublist<?php echo $x?> .column1 {
						width: <?php echo $presubSecondFieldInListWidth?>%;
						margin-right: 0;
					}
					<?php
				}
				if($presubThirdFieldInListWidth != null && $presubThirdFieldInListWidth != ""){
					?>
					.subcontent-list.sublist<?php echo $x?> .column2 {
						width: <?php echo $presubThirdFieldInListWidth?>%;
						margin-right: 0;
					}
					<?php
				}
				if($presubForthFieldInListWidth != null && $presubForthFieldInListWidth != ""){
					?>
					.subcontent-list.sublist<?php echo $x?> .column3 {
						width: <?php echo $presubForthFieldInListWidth?>%;
						margin-right: 0;
					}
					<?php
				}
				if($presubFifthFieldInListWidth != null && $presubFifthFieldInListWidth != ""){
					?>
					.subcontent-list.sublist<?php echo $x?> .column4 {
						width: <?php echo $presubFifthFieldInListWidth?>%;
						margin-right: 0;
					}
					<?php
				}
				if($presubSixthFieldInListWidth != null && $presubSixthFieldInListWidth != ""){
					?>
					.subcontent-list.sublist<?php echo $x?> .column5 {
						width: <?php echo $presubSixthFieldInListWidth?>%;
						margin-right: 0;
					}
					<?php
				}
				for($xyz = 2; $xyz<= 4; $xyz++){
					$variableFields = "preSet".$xyz."subNumberOfFields";
					$variableOne = "preSet".$xyz."subFieldInListWidth";
					$variableTwo = "preSet".$xyz."subSecondFieldInListWidth";
					$variableThree = "preSet".$xyz."subThirdFieldInListWidth";
					$variableFour = "preSet".$xyz."subForthFieldInListWidth";
					$variableFive = "preSet".$xyz."subFifthFieldInListWidth";
					$variableSix = "preSet".$xyz."subSixthFieldInListWidth";
					$variableUseAfter = "preSet".$xyz."subUseAfterWidth";
					if($$variableFields > 0){
						if(intval($$variableUseAfter) > 0){
							if($$variableOne != null && $$variableOne != ""){
							?>
								.subcontent-list.sublist<?php echo $x?>.set<?php echo $xyz;?> .column0 {
									width: <?php echo $$variableOne?>%;
									margin-right: 0;
								}
								<?php
							}
							if($$variableTwo != null && $$variableTwo != ""){
								?>
								.subcontent-list.sublist<?php echo $x?>.set<?php echo $xyz;?> .column1 {
									width: <?php echo $$variableTwo?>%;
									margin-right: 0;
								}
								<?php
							}
							if($$variableThree != null && $$variableThree != ""){
								?>
								.subcontent-list.sublist<?php echo $x?>.set<?php echo $xyz;?> .column2 {
									width: <?php echo $$variableThree?>%;
									margin-right: 0;
								}
								<?php
							}
							if($$variableFour != null && $$variableFour != ""){
								?>
								.subcontent-list.sublist<?php echo $x?>.set<?php echo $xyz;?> .column3 {
									width: <?php echo $$variableFour?>%;
									margin-right: 0;
								}
								<?php
							}
							if($$variableFive != null && $$variableFive != ""){
								?>
								.subcontent-list.sublist<?php echo $x?>.set<?php echo $xyz;?> .column4 {
									width: <?php echo $$variableFive?>%;
									margin-right: 0;
								}
								<?php
							}
							if($$variableSix != null && $$variableSix != ""){
								?>
								.subcontent-list.sublist<?php echo $x?>.set<?php echo $xyz;?> .column5 {
									width: <?php echo $$variableSix?>%;
									margin-right: 0;
								}
								<?php
							}
						}
					}
				}
				?>
				</style>
				<script type="text/javascript">
					function resizeColumns<?php echo $x?>(){
						$(".subcontent-list.sublist<?php echo $x?>").removeClass("set1 set2 set3 set4 set5")
						<?php
						for($xyz = 2; $xyz<= 4; $xyz++){
							$variableFields = "preSet".$xyz."subNumberOfFields";
							$variableOne = "preSet".$xyz."subFieldInListWidth";
							$variableTwo = "preSet".$xyz."subSecondFieldInListWidth";
							$variableThree = "preSet".$xyz."subThirdFieldInListWidth";
							$variableFour = "preSet".$xyz."subForthFieldInListWidth";
							$variableFive = "preSet".$xyz."subFifthFieldInListWidth";
							$variableSix = "preSet".$xyz."subSixthFieldInListWidth";
							$variableUseAfter = "preSet".$xyz."subUseAfterWidth";
							if($$variableFields > 0){
								if(intval($$variableUseAfter) > 0){
							?>
								if($(".subcontent-list.sublist<?php echo $x?>").width() <= <?php echo intval($$variableUseAfter)?>){
									$(".subcontent-list.sublist<?php echo $x?>").removeClass("set1 set2 set3 set4 set5")
									$(".subcontent-list.sublist<?php echo $x?>").addClass("set<?php echo $xyz?>");
								}
							<?php
								}
							}
						}
						?>
						//set default list set
						if(!$(".subcontent-list.sublist<?php echo $x?>").hasClass("set2") && !$(".subcontent-list.sublist<?php echo $x?>").hasClass("set3") && !$(".subcontent-list.sublist<?php echo $x?>").hasClass("set4")){
							$(".subcontent-list.sublist<?php echo $x?>").addClass("set1");
						}
					}
					resizeColumns<?php echo $x?>();
					$(document).ready(function(){
						resizeColumns<?php echo $x?>();
					})
					$(window).on('resize', resizeColumns<?php echo $x?>);

					$(function(){
						$(".subcontent.<?php echo $relationstable?> .subcontent-content").each(function(){
							if($(this).find(".input_list_item.load").length > 0){
								$(this).show();
							}
						})
						$(".subcontent.<?php echo $relationstable?> .subcontent-content .input_list_item.load").each(function(){
							var link = $(this).find(".column0 a").attr("href");
							var target = $(this).find(".column0 a").data("target");
							if(!$(target).hasClass("loaded")){
								fw_load_ajax(link, target);
							}
						})
					})
				</script>
				<div class="subcontent ui-corner-all toggle-parent <?php echo $relationstable;?>" data-table="<?php echo $relationstable;?>">
					<div class="header"><span class="ptr" onClick="$(this).closest('.header').find('.content-toggler span:not(.hide)').trigger('click');"><?php echo $preinputformName;?></span>&nbsp;<span class="badge"><?php echo $content_rows;?></span><?php if($prelistButtonCreate==1) { ?><div class="btn btn-sm add-new" data-href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&"."module=".$prerelationsarray[1]."&parentID=".$_GET['ID']."&subcontent=1&includefile=edit&submodule=".$prerelationsarray[2]."&relationID=".$relationvalue[$prerelationsarray[0]]."&relationfield=".$prerelationsarray[3].($s_content_status_filter!="" ? "&content_status=".$s_content_status_filter : "").($relation_link_to_module_id==1 ? "&relation_module_id=".$moduleID : "");?>"><span class="glyphicon glyphicon-plus"></span></div><?php } echo $ob_buttons;?>
						<div class="content-toggler buttons btn btn-sm<?php echo ($preexpandSublist==1?' open':'');?>"><span class="toggler collapse-down glyphicon glyphicon-collapse-down<?php echo ($preexpandSublist==1?' hide':'');?>"></span><span class="toggler collapse-up glyphicon glyphicon-collapse-up<?php echo ($preexpandSublist!=1?' hide':'');?>"></span></div></div>

					<div class="subcontent-content toggle-content ui-corner-all fw_clear_both"<?php echo ($preexpandSublist!=1?' style="display:none;"':'');?>>
						<div class="subcontent-new"></div>
						<div class="subcontent-list sublist<?php echo $x?>"><?php
							if($getContent && $getContent->num_rows() > 0)
							{
								?><div class="input_list_item head row"><?php

									$linkers = array();
									foreach($listFieldInSubmoduleVariables as $var) if(isset(${$var}) && !empty(${$var})) $linkers[0][] = ${$var};
									foreach($listSet2FieldInSubmoduleVariables as $var) if(isset(${$var}) && !empty(${$var})) $linkers[1][] = ${$var};
									foreach($listSet3FieldInSubmoduleVariables as $var) if(isset(${$var}) && !empty(${$var})) $linkers[2][] = ${$var};
									foreach($listSet4FieldInSubmoduleVariables as $var) if(isset(${$var}) && !empty(${$var})) $linkers[3][] = ${$var};

									$column_count = count($linkers);
									$column_width = floor(11/$column_count);
									$column_width_extra = 11 - ($column_width*$column_count);
									$linkersField = array();
									$setCounter = 1;
									foreach($linkers as $key => $outlink)
									{
										$counter=0;
										?>
										<div class="inputListSet inputListSet<?php echo $setCounter;?>">
											<?php

											foreach($outlink as $key=>$outlink) {
												foreach($fields as $outfield)
												{
													if($outfield[0] == $outlink)
													{
														$linkersField[$key] = $outfield;
														if($outfield[4] == "Dropdown" or $outfield[4] == "GroupDropdownField")
														{
															$varName = $outfield[0]."ListArray";
															${$varName} = array();
															${$varName}["type"] = "dropdown";
															$tmp = explode("::",$outfield[11]);
															foreach($tmp as $tmpv)
															{
																list($lKey,$lValue) = explode(":",$tmpv);
																${$varName}[$lKey] = $lValue;
															}
														}
														elseif($outfield[4] == "RadioButtonAdv")
														{
															$varName = $outfield[0]."ListArray";
															${$varName} = array();
															${$varName}["type"] = "dropdown";
															$tmp = explode("::",$outfield[11]);
															foreach($tmp as $tmpv)
															{
																list($lKey,$lValue) = explode(":",$tmpv);
																${$varName}[$lKey] = $lValue;
															}
														}
														elseif($outfield[4] == "DropdownTable")
														{
															$varName = $outfield[0]."ListArray";
															${$varName} = array();
															${$varName}["type"] = "dropdown";
															$expl = explode(":",$outfield[11]);
															$dataTable = $expl[0];
															$dataID = $expl[1];
															$dataNames = explode(",",$expl[2]);
															$dataFilter = $expl[3];

															//TODO: ALI - security_check - filter
															$s_sql = "SELECT * FROM ".$o_main->db_escape_name($dataTable);
															if($o_main->multi_acc)
															{
																$dataFilter .= (""!=$dataFilter?" AND ":"")."account_id = '".$o_main->db->escape_str($o_main->account_id)."'";
															}
															if($dataFilter!="") $s_sql .= ' WHERE '.$dataFilter;
															$o_query = $o_main->db->query($s_sql);
															if($o_query && $o_query->num_rows()>0)
															{
																foreach($o_query->result() as $o_row)
																{
																	$outName = "";
																	foreach($dataNames as $dataName) $outName .= $o_row->$dataName." ";
																	${$varName}[$o_row->$dataID] = $outName;
																}
															}
														}
														else if($outfield[4] == "Number" and $outfield[11] != "")
														{
															list($relTable, $relID, $relName) = explode(":",$outfield[11]);
															$o_query = $o_main->db->query('SELECT '.$o_main->db_escape_name($relID).', '.$o_main->db_escape_name($relName).' FROM '.$o_main->db_escape_name($relTable).($o_main->multi_acc?" WHERE account_id = '".$o_main->db->escape_str($o_main->account_id)."'":""));
															if($o_query && $o_query->num_rows()>0)
															{
																$varName = $outfield[0]."ListArray";
																${$varName} = array();
																${$varName}["type"] = "relation";

																foreach($o_query->result() as $o_row)
																{
																	${$varName}[$o_row->$relID] = $o_row->$relName;
																}
															}
														}
														else if($outfield[4] == "Checkbox")
														{
															$varName = $outfield[0]."ListArray";
															${$varName} = array();
															${$varName}["type"] = "checkbox";
															${$varName}[''] = ${$varName}[0] = "<div class=\"adm-ui-checkbox-empty\"></div>";
															${$varName}[1] = "<div class=\"adm-ui-checkbox-full\"></div>";
														}
														else if(in_array($outfield[4],array("Image","File","FileOrImage")))
														{
															$varName = $outfield[0]."Json";
															${$varName} = array();
															${$varName}["type"] = "json";
															${$varName}['levels'] = array(0);
														}
														else if($outfield[4] == "Comment")
														{
															$varName = $outfield[0]."Json";
															${$varName} = array();
															${$varName}["type"] = "json";
															${$varName}['levels'] = array(1);
														}
														else if($outfield[4] == "InvoiceNumber")
														{
															$varName = $outfield[0]."invoiceSearch";
															${$varName} = "SEARCH";
														}
														else if($outfield[4] == "ShowInvoice")
														{
															$varName = $outfield[0]."invoiceSearch";
															${$varName} = "OWNSEARCH";
														}
													}
												}
												$column = $column_width+($column_width_extra-$counter>0?1:0);
												?><div class="column column<?php echo $counter;?>">
													<a href="#" class="input_list_item_order" data-href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$prerelationsarray[1]."&includefile=sublistpage&submodule=".$prerelationsarray[2]."&sub_parentID=".$_GET['ID']."&sub_relationID=".$relationvalue[$prerelationsarray[0]]."&sub_relationfield=".$prerelationsarray[3]."&sub_relationstable=".$relationstable."&parentfield=".$prerelationsarray[0]."&parenttable=".$submodule."&perPage=".$preperPage."".($s_content_status_filter!="" ? "&content_status=".$s_content_status_filter : "").($relation_link_to_module_id==1 ? "&relation_module_id=".$moduleID : "");?>" data-direction="ASC" data-sublistorder="<?php echo $linkersField[$key][0]; ?>">
														<?php echo htmlentities($linkersField[$key][2]);?>
														<span class="glyphicon glyphicon-white fieldordericon"></span>
													</a>
												</div><?php
												$counter++;
											}
											$setCounter++;
											?>
										</div>
										<?php
									}
									?><div class="col-md-1"></div>
								</div><?php
							}
							$prebuttonconfig = '';
							$content_buttons = array();
							include(ACCOUNT_PATH."/modules/".$prerelationsarray[1]."/input/settings/buttonconfig/".$prerelationsarray[2]."inputform.php");
							$buttons = explode("¤",$prebuttonconfig);

							foreach($buttons as $button)
							{
								$items = explode(":",$button);
								if(count($items) > 2 )
								{
									$content_buttons[] = $items;
								}
							}
							?>
							<div class="input_list_item_body">
								<?php
								if($getContent && $getContent->num_rows()>0)
								foreach($getContent->result_array() as $writeContent)
								{
									$content_id = $writeContent['linkID'];
									$editLink = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&"."module=".$prerelationsarray[1]."&ID=".$content_id."&parentID=".$_GET['ID']."&subcontent=1&includefile=edit&submodule=".$prerelationsarray[2]."&relationID=".$relationvalue[$prerelationsarray[0]]."&relationfield=".$prerelationsarray[3].($s_content_status_filter!="" ? "&content_status=".$s_content_status_filter : "").($relation_link_to_module_id==1 ? "&relation_module_id=".$moduleID : "");
									$edit_link_attr = ' data-target="#content_'.$prerelationsarray[1].'_'.$prerelationsarray[2].'_'.$content_id.'"';
									$sublist_opened = "";
									if(1 != $close_opened_items_after_reload && $_SESSION['subcontent'][$prerelationsarray[1]][$_GET['ID']][$prerelationsarray[2]][$content_id] == "opened"){
										$sublist_opened = " load";
									}
									?><div class="input_list_item row<?php echo $sublist_opened;?>" data-group="input_list_item"><?php
										ob_start();
										$setCounter = 1;
										$item_name = "";
										foreach($linkers as $key => $finList)
										{
											$counter = 0;
											?>
											<div class="inputListSet inputListSet<?php echo $setCounter;?>">
												<?php
												foreach($finList as $key=>$finList)
												{
													$varJson = $finList."Json";
													$varName = $finList."ListArray";
													$column = $column_width+($column_width_extra-$counter>0?1:0);
													?><div class="column column<?php echo $counter;?>"><?php
													if(is_file(ACCOUNT_PATH."/modules/".$prerelationsarray[1]."/input/fieldtypes/".$linkersField[$key][4]."/customlistdisplay.php"))
													{
														include(ACCOUNT_PATH."/modules/".$prerelationsarray[1]."/input/fieldtypes/".$linkersField[$key][4]."/customlistdisplay.php");
													} else {
														$invoiceName = $finList."invoiceSearch";
														if(isset(${$invoiceName}))
														{
															$invoiceID = $content_id;
															if(${$invoiceName} == "SEARCH")
															{
																$invoiceID = $writeContent[$finList];
															}
															$o_query = $o_main->db->query("SELECT invoiceFile FROM invoice WHERE id = ?".($o_main->multi_acc?" AND account_id = '".$o_main->db->escape_str($o_main->account_id)."'":""), array($invoiceID));
															if($o_query && $o_row = $o_query->row())
															{
																if($o_row->invoiceFile != "")
																{
																	$v_path_split = explode("uploads/",$o_row->invoiceFile);
																	$filepathTo = $extradomaindirroot."uploads/".$v_path_split[1];
																	echo '<a href="'.$filepathTo.(strpos($filepathTo,'uploads/protected/')!==false ? '?caID='.$_GET['caID'].'&table=invoice&field=invoiceFile&ID='.$invoiceID : '').'" target="_blank">'.$formText_openPdf_input.'</a>';
																}
															}
														} else {
															$value = "";
															if(isset(${$varJson}))
															{
																$data = json_decode($writeContent[$finList]);
																foreach($data as $item0)
																{
																	if(sizeof(${$varJson}['levels'])>1)
																	{
																		foreach($item0[${$varJson}['levels'][0]] as $item1)
																		{
																			if($value!="") $value.="; ";
																			$value .= html_entity_decode($item1[${$varJson}['levels'][1]]);
																		}
																	} else {
																		if($value!="") $value.="; ";
																		$value .= html_entity_decode($item0[${$varJson}['levels'][0]]);
																	}
																}
															} else if(isset(${$varName}))
															{
																$value = ${$varName}[$writeContent[$finList]];
															} else {
																$value = $writeContent[$finList];
															}
															if(trim($value) == "")
															{
																$value = "...";
															}
															echo '<a href="'.$_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&"."module=".$prerelationsarray[1]."&ID=".$content_id."&parentID=".$_GET['ID']."&subcontent=1&includefile=edit&submodule=".$prerelationsarray[2]."&relationID=".$relationvalue[$prerelationsarray[0]]."&relationfield=".$prerelationsarray[3].($s_content_status_filter!="" ? "&content_status=".$s_content_status_filter : "").($relation_link_to_module_id==1 ? "&relation_module_id=".$moduleID : "").'" class="optimize" data-target="#content_'.$prerelationsarray[1].'_'.$prerelationsarray[2].'_'.$content_id.'"> '.htmlentities($value).'</a>';

															if($item_name == "") $item_name = htmlspecialchars(strip_tags($value));
														}
													}
													?></div><?php
													$counter++;
												}
												?>
											</div>
											<?php
											$setCounter++;
										}
										if($item_name == "")
										{
											ob_clean();
											$setCounter = 1;
											$item_name = "noname";
											foreach($linkers as $key => $finList)
											{
												$counter = 0;
												?><div class="inputListSet inputListSet<?php echo $setCounter;?>"><?php
												foreach($finList as $key=>$finList)
												{
													?><div class="column column<?php echo $counter;?>"><?php
													if($counter==0) echo '<a href="'.$_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&"."module=".$prerelationsarray[1]."&ID=".$content_id."&parentID=".$_GET['ID']."&subcontent=1&includefile=edit&submodule=".$prerelationsarray[2]."&relationID=".$relationvalue[$prerelationsarray[0]]."&relationfield=".$prerelationsarray[3].($s_content_status_filter!="" ? "&content_status=".$s_content_status_filter : "").($relation_link_to_module_id==1 ? "&relation_module_id=".$moduleID : "").'" class="optimize" data-target="#content_'.$prerelationsarray[1].'_'.$prerelationsarray[2].'_'.$content_id.'"> '.htmlentities($item_name).'</a>';
													?></div><?php
													$counter++;
												}
												?></div><?php
												$setCounter++;
											}
										}
										echo ob_get_clean();
										?>
										<div class="list_buttons popup_button_box col-md-1 dropdown">
											<a id="<?php echo "sl_btn_".$prerelationsarray[1]."_".$prerelationsarray[2]."_".$content_id;?>" class="script" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
											<span class="glyphicon glyphicon-menu-hamburger glyphicon-white"></span>
											</a>
											<ul class="dropdown-menu" role="menu" aria-labelledby="<?php echo "sl_btn_".$prerelationsarray[1]."_".$prerelationsarray[2]."_".$content_id;?>"><?php
											foreach($content_buttons as $buttonsArray)
											{
												$buttonSubmodule = $buttonsArray[6];
												$buttonModule = $buttonsArray[0];
												$buttonInclude = $buttonsArray[2];
												$buttonRelationModule = $buttonsArray[3];
												$buttonMode = $buttonsArray[4];
												if($buttonMode == 0)
												{
													?><li><?php
													include(ACCOUNT_PATH."/modules/".$prerelationsarray[1]."/input/buttontypes/$buttonInclude/button.php");
													?></li><?php
												}
											}

											if($orderManualOrByField == '0' and $access >= 10){ ?><li><a role="menuitem" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$prerelationsarray[1]."&ID=".$content_id."&includefile=orderContent&submodule=".$prerelationsarray[2].(array_key_exists('menulevel',$writeContent) ? "&menulevel=".$writeContent['menulevel']:"")."&relationID=".$relationvalue[$prerelationsarray[0]]."&relationfield=".$prerelationsarray[3]."&relationstable=".$relationstable.($s_content_status_filter!="" ? "&content_status=".$s_content_status_filter : "").($relation_link_to_module_id==1 ? "&relation_module_id=".$moduleID : "")."&parenttable=".$submodule."&ordersql=".$ordersql;?>" class="optimize order_sublist_item"><?php echo $formText_order_list;?></a></li><?php }

											if($prelistButtonDelete == 1 and $access >= 100){ ?><li><a role="menuitem" class="delete" data-name="<?php echo $item_name;?>" href="<?php echo $extradir."/input/delete.php?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$prerelationsarray[1]."&parentID=".$_GET['ID']."&deleteID=".$content_id."&deletemodule=".$prerelationsarray[2]."&submodule=".$prerelationsarray[2]."&relationID=".$relationvalue[$prerelationsarray[0]]."&relationfield=".$prerelationsarray[3]."&relationstable=".$relationstable.($s_content_status_filter!="" ? "&content_status=".$s_content_status_filter : "").($relation_link_to_module_id==1 ? "&relation_module_id=".$moduleID : "");?>"><?php echo $formText_delete_sublist;?></a></li><?php }

											$counter++;
											?>
											</ul>
										</div>
									</div>
									<div id="<?php echo "content_".$prerelationsarray[1]."_".$prerelationsarray[2]."_".$content_id;?>" class="input_list_form"></div><?php
								}
								?>
							</div>
						</div>
						<?php if($content_pages > 1) { ?>
						<div class="more-rows text-center"><button class="btn btn-sm btn-default" data-href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$prerelationsarray[1]."&includefile=sublistpage&submodule=".$prerelationsarray[2]."&sub_parentID=".$_GET['ID']."&sub_relationID=".$relationvalue[$prerelationsarray[0]]."&sub_relationfield=".$prerelationsarray[3]."&sub_relationstable=".$relationstable."&parentfield=".$prerelationsarray[0]."&parenttable=".$submodule."&perPage=".$preperPage."&descUse=ASC".($s_content_status_filter!="" ? "&content_status=".$s_content_status_filter : "").($relation_link_to_module_id==1 ? "&relation_module_id=".$moduleID : "");?>" data-nextpage="1" data-totalpages="<?php echo $content_pages;?>"><?php echo $formText_moreRows_list;?></button></div>
						<?php } ?>
						<div class="subcontent_order_wrapper"></div>
					</div>
				</div><?php
			}
		}
	}

	$extradir = $old_extradir;
	?>
	<script type="text/javascript">
	<?php if(isset($ob_javascript)) { ob_start(); } ?>
		$(".subcontent .subcontent-content .more-rows button").on("click",function(e){
			e.preventDefault();
			var nextpage = parseInt($(this).data('nextpage')),
				totalpages = parseInt($(this).data('totalpages'));
			if(nextpage >= totalpages)
			{
				$(this).hide();
				return;
			}

			if(!fw_changes_made && !fw_click_instance)
			{
				var direction = $(this).data('direction');
				var fieldname = $(this).data('sublistorder');
				var extraOrderParameters = "";
				if(fieldname != undefined && fieldname != ""){
					extraOrderParameters = '&sublistorder='+fieldname+'&sublistorderdir='+direction;
				}
				fw_click_instance = true;
				var oLink = $('<a/>').uniqueId().attr({'href':$(this).data('href')+'&start='+nextpage+extraOrderParameters,'class':'optimize','data-replace':1});
				oLink.attr('data-target','#'+oLink.attr('id'));
				$(this).closest('.subcontent-content').children('.subcontent-list').find('.input_list_item_body').append(oLink);
				nextpage = nextpage + 1;
				$(this).data('nextpage',nextpage);
				if(nextpage >= totalpages) $(this).hide();
				fw_optimize_urls();
				fw_click_instance = false;
				oLink.trigger("click");
			}
		});
		$(".subcontent .input_list_item_order").on("click",function(e){
			e.preventDefault();
			if(!fw_changes_made && !fw_click_instance)
			{
				var direction = $(this).data('direction');
				var fieldname = $(this).data('sublistorder');
				if(direction == ""){
					fieldname = "";
				}
				fw_click_instance = true;
				var oLink = $('<a/>').uniqueId().attr({'href':$(this).data('href')+'&start=0&sublistorder='+fieldname+'&sublistorderdir='+direction,'class':'optimize','data-replace':1});
				oLink.attr('data-target','#'+oLink.attr('id'));
				$(this).closest('.subcontent-content').children('.subcontent-list').find('.input_list_item_body').html(oLink);
				fw_optimize_urls();
				var moreRows = $(this).parents(".subcontent-content").find(".more-rows .btn");
				if(moreRows.length > 0 ){
					moreRows.data('direction', direction);
					moreRows.data('sublistorder', fieldname);
					moreRows.data('nextpage', 1);
					moreRows.show();
				}
				var allFieldorderIcon = $(this).parents(".subcontent-content").find(".fieldordericon");
				var allFieldorderItems = $(this).parents(".subcontent-content").find(".input_list_item_order");
				var fieldordericon = $(this).parents(".column").find(".fieldordericon");

				if(direction == "ASC") {
					direction = "DESC";
					allFieldorderIcon.removeClass("glyphicon-sort-by-attributes-alt").removeClass("glyphicon-sort-by-attributes");
					allFieldorderItems.data('direction', "ASC");
					fieldordericon.addClass("glyphicon-sort-by-attributes");
				} else if(direction == "DESC") {
					allFieldorderIcon.removeClass("glyphicon-sort-by-attributes-alt").removeClass("glyphicon-sort-by-attributes");
					allFieldorderItems.data('direction', "ASC");
					fieldordericon.addClass("glyphicon-sort-by-attributes-alt");
					direction = "";
				} else if(direction == "") {
					direction = "ASC";
					allFieldorderIcon.removeClass("glyphicon-sort-by-attributes-alt").removeClass("glyphicon-sort-by-attributes");
					allFieldorderItems.data('direction', "ASC");
				}
				$(this).data('direction',direction);

				fw_click_instance = false;
				oLink.trigger("click");
			}
		});
		$(document).on("click",".subcontent-list .input_list_item .delete",function(e){
			var _current_location = window.location;
			e.preventDefault();
			if(!fw_changes_made && !fw_click_instance)
			{
				fw_click_instance = true;
				var $_this = $(this);
				var s_msg_sufix = "";
				if($(this).data("name")) s_msg_sufix = ": " + $(this).data("name");
				bootbox.confirm({
					message:"<?php echo $formText_DeleteItem_input;?>" + s_msg_sufix + "?",
					buttons:{confirm:{label:"<?php echo $formText_Yes_input;?>"},cancel:{label:"<?php echo $formText_No_input;?>"}},
					callback: function(result){
						if(result)
						{
							fw_loading_start();
							$.ajax({
								url: $_this.attr("href"),
								cache: false,
								type: "GET",
								dataType: "json",
								success: function (data) {
									if(data.error !== undefined)
									{
										$.each(data.error, function(index, value){
											var _type = Array("error");
											if(index.length > 0 && index.indexOf("_") > 0) _type = index.explode("_");
											fw_info_message_add(_type[0], value);
										});
										fw_info_message_show();
										fw_loading_end();
										fw_click_instance = false;
									} else {
										//window.location = data.url;
										fw_load_ajax(_current_location, '', false);
									}
								}
							}).fail(function() {
								fw_info_message_add("error", "<?php echo $formText_ErrorOccuredDeletingContent_input;?>", true);
								fw_loading_end();
								fw_click_instance = false;
							});
						} else {
							fw_click_instance = false;
						}
					}
				});
			}
		});
		$(".subcontent .header .btn.add-new").on("click",function(e){
			e.preventDefault();
			$(this).closest('.header').find('.content-toggler:not(.open)').trigger('click');
			var oDiv = $('<div/>').uniqueId().attr('class','subcontent-item'),
				oLink = $('<a/>').attr({'href':$(this).attr('data-href'),'class':'optimize','data-target':'#'+oDiv.attr('id')}).appendTo(oDiv);
			$(this).closest(".subcontent").find(".subcontent-new").prepend(oDiv);
			fw_optimize_urls();
			oLink.trigger("click");
			return false;
		});
		$(".subcontent .order_sublist_item").on("click",function(e){
			e.preventDefault();
			var content = $(this).parents(".subcontent-content");

			// $(this).closest('.header').find('.content-toggler:not(.open)').trigger('click');
			var oDiv = $('<div/>').uniqueId().attr('class','subcontent-content-order'),
				oLink = $('<a/>').attr({'href':$(this).attr('href'),'class':'optimize','data-target':'#'+oDiv.attr('id')}).appendTo(oDiv);
			content.find(".subcontent_order_wrapper").html(oDiv);
			fw_optimize_urls();
			oLink.trigger("click");
			return false;
		});
	<?php if(isset($ob_javascript)) { $ob_javascript .= " ".ob_get_clean(); } ?>
	</script>
	<?php
}
