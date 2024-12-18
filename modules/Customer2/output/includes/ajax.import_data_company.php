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
include(__DIR__."/../../input/settings/fields/customerfields.php");
foreach($prefields as $fieldinfo)
{
	$fieldinfo = explode('¤', $fieldinfo);
	$dbfields[] = $fieldinfo[0];
}
$dbfields2 = array();
include(__DIR__."/../../input/settings/fields/contactpersonfields.php");
foreach($prefields as $fieldinfo)
{
	$fieldinfo = explode('¤', $fieldinfo);
	$dbfields2[] = $fieldinfo[0];
}
if(isset($_POST['table']))
{
	if($l_access < 10){
		if($_SERVER['HTTP_REFERER']) {
			header("Location: " . $_SERVER['HTTP_REFERER']);
		} else {
			die('UNKNOWN HTTP_REFERER');
		}
	}
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
			$csv[$i][trim($headers[$j])] = $rowValues[$j];
		}
		//break;
	}

	$relation = (array_filter($_POST['field']));
	if( is_array($_POST['customlabel']) && sizeof($_POST['customlabel']) ) {
		foreach($_POST['customlabel'] as $dbField=>$label) {
			$customLabels[] = $o_main->db_escape_name($dbField)." = ".$o_main->db->escape($label);
		}
	}
	foreach($csv as $row) {
		if(sizeof($customLabels)) {
			$set = $customLabels;
		}

		if($_POST['createProspect'] == 2){
			$set[] = "updated = NOW()";
			$set[] = "updatedby = ".$o_main->db->escape("imported - ".$user);
		} else {
			$set[] = "created = NOW()";
			$set[] = "createdby = ".$o_main->db->escape("imported - ".$user);
			$set[] = "customerType = 0";
		}

		$set2[] = "created = NOW()";
		$set2[] = "createdby = ".$o_main->db->escape("imported - ".$user);

		$setSelfdefined = array();
		$setExtrenal = array();
		$setProspect = array();
		$prospectValue = "";
		$prospectInfo = "";
		$contactpersonEmail = "";
		$name = "";
		$fullname_for_import_comparing = "";
		$subscription_start_date = "0000-00-00";
		foreach($relation as $dbField=>$csvField) {
			if(trim($row[$csvField]) != ""){
				if(strpos($dbField, "selfdefined") === false) {
					if(strpos($dbField, "contactperson") === false) {
						if(strpos($dbField, "prospect") === false) {
							if(strpos($dbField, "groupconnection") === false) {
								if(strpos($dbField, "subscription") === false) {
									$rowData = $row[$csvField];
									$rowData = str_replace(array("\n", "\t", "\r"), '', $rowData);
									if($dbField == 'rentalUnit' || $dbField == 'wantInfoElectronic' || $dbField == 'mustReceiveInfoOnPaper') {
										if(strtolower($row[$csvField]) == "x"){
											$rowData = 1;
										} else {
											$rowData = 0;
										}
									}
									if($dbField != "customerNumber"){
										$set[] = $o_main->db_escape_name($dbField)." = ".$o_main->db->escape($rowData);
									} else {
										if($rowData > 0){
											$setExtrenal[] = $rowData;
										}
									}
								} else {
									$rowData = $row[$csvField];
									$realDbFieldArray = explode("_", $dbField, 2);
									$dbField = $realDbFieldArray[1];
									if($dbField == "start_date"){
										$subscription_start_date = $rowData;
									}
								}
							} else {
								$rowData = $row[$csvField];
								$realDbFieldArray = explode("_", $dbField, 2);
								$dbField = $realDbFieldArray[1];
								if($dbField == "fullname_for_comparing"){
									$fullname_for_import_comparing = $rowData;
								}
							}
						} else {
							$rowData = $row[$csvField];
							$realDbFieldArray = explode("_", $dbField, 2);
							$dbField = $realDbFieldArray[1];
							if($dbField == "value") {
								$prospectValue = $rowData;
							} else if($dbField == "info"){
								$prospectInfo = $rowData;
							}
						}
					} else {
						$rowData = $row[$csvField];
						$realDbFieldArray = explode("_", $dbField, 2);
						$dbField = $realDbFieldArray[1];
						if($dbField == "email") {
							$contactpersonEmail = $rowData;
						}
						if($dbField == "fullname_for_import_comparing"){
							$fullname_for_import_comparing = $rowData;
						}
						$set2[] = $o_main->db_escape_name($dbField)." = ".$o_main->db->escape($rowData);
					}
				} else {
					$realDbFieldArray = explode("_", $dbField, 2);

					$rowData = $row[$csvField];
					$rowData = str_replace(array("\n", "\t", "\r"), '', $rowData);

					$setSelfdefined[] = array($realDbFieldArray[1]=>$rowData);
				}
				if($dbField == "publicRegisterId") {
					$idField = $dbField;
					$idValue = preg_replace("/[^0-9]/", "", $row[$csvField]);
				}
				if($dbField == "invoiceEmail" && $rowData != "") {
					$set[] = "invoiceBy = 1";
				}

			}
		}
		if($_POST['createProspect'] == 2){
			$ownercompanyIdPost = $_POST['ownercompany'];
			if(count($setExtrenal) > 0){
				foreach($setExtrenal as $external_sys_id){
					$o_query = $o_main->db->query("SELECT * FROM customer_externalsystem_id WHERE external_id = ? AND ownercompany_id = ?", array($external_sys_id, $ownercompanyIdPost));
					if($o_query && $o_query->num_rows()>0)
					{
						$external_item = $o_query->row_array();
						$o_query = $o_main->db->query("SELECT * FROM customer WHERE id = ?", array($external_item['customer_id']));
						$foundItem = $o_query ? $o_query->row_array() : array();
						if($foundItem){
							$s_sql  = "UPDATE ".$insertTable." SET ".implode(", ", $set)." WHERE id = ".$foundItem['id'].";";
							if(!$o_main->db->query($s_sql)) die($o_main->db->error());
						}
					}
				}
			}
		} else if($_POST['createProspect'] == 3) {
			$ownercompanyIdPost = $_POST['ownercompany'];
			if(count($setExtrenal) > 0){
				foreach($setExtrenal as $external_sys_id){
					$o_query = $o_main->db->query("SELECT * FROM customer_externalsystem_id WHERE external_id = ? AND ownercompany_id = ?", array($external_sys_id, $ownercompanyIdPost));
					if($o_query && $o_query->num_rows()>0)
					{
						$external_item = $o_query->row_array();
						$o_query = $o_main->db->query("SELECT * FROM customer WHERE id = ?", array($external_item['customer_id']));
						$foundItem = $o_query ? $o_query->row_array() : array();
						if($foundItem){
							if(count($set2) > 2){
								$set2[] = "type = 1";
								$set2[] = "customerId = ".$foundItem['id'];
								$s_sql = "SELECT * FROM contactperson WHERE customerId = ? AND fullname_for_import_comparing = ?";
								$o_query = $o_main->db->query($s_sql, array($foundItem['id'], $fullname_for_import_comparing));
								$contactpersonPrivate = $o_query ? $o_query->row_array() : array();
								if(!$contactpersonPrivate) {
									$s_sql  = "INSERT INTO contactperson SET ".implode(", ", $set2).";";
									if(!$o_main->db->query($s_sql)) die($o_main->db->error());
								} else {
									// $s_sql  = "UPDATE contactperson SET ".implode(", ", $set2)." WHERE id = ".$contactpersonPrivate['id'].";";
									// if(!$o_main->db->query($s_sql)) die($o_main->db->error());
								}
							}
						}
					}
				}
			}
		} else if($_POST['createProspect'] == 4) {
			$ownercompanyIdPost = $_POST['ownercompany'];
			if(count($setExtrenal) > 0){
				foreach($setExtrenal as $external_sys_id){
					$o_query = $o_main->db->query("SELECT * FROM customer_externalsystem_id WHERE external_id = ? AND ownercompany_id = ?", array($external_sys_id, $ownercompanyIdPost));
					if($o_query && $o_query->num_rows()>0)
					{
						$external_item = $o_query->row_array();
						$o_query = $o_main->db->query("SELECT * FROM customer WHERE id = ?", array($external_item['customer_id']));
						$foundItem = $o_query ? $o_query->row_array() : array();
						if($foundItem){
							$s_sql  = "INSERT INTO subscriptionmulti SET created = NOW(), createdby = ".$o_main->db->escape("imported - ".$user).", customerId = ?, startDate = ?, subscriptionName = ?, subscriptiontype_id = ?, periodNumberOfMonths = ?, stoppedDate = '0000-00-00', nextRenewalDate = '0000-00-00';";
							if(!$o_main->db->query($s_sql, array($foundItem['id'], date("Y-m-d", strtotime($subscription_start_date)), $foundItem['name'], $_POST['subscription_type'], $_POST['subscription_period_length']))) die($o_main->db->error());
						}
					}
				}
			}
		} else if($_POST['createProspect'] == 5) {
			$ownercompanyIdPost = $_POST['ownercompany'];
			if(count($setExtrenal) > 0){
				foreach($setExtrenal as $external_sys_id){
					$o_query = $o_main->db->query("SELECT * FROM customer_externalsystem_id WHERE external_id = ? AND ownercompany_id = ?", array($external_sys_id, $ownercompanyIdPost));
					if($o_query && $o_query->num_rows()>0)
					{
						$external_item = $o_query->row_array();
						$o_query = $o_main->db->query("SELECT * FROM customer WHERE id = ?", array($external_item['customer_id']));
						$foundItem = $o_query ? $o_query->row_array() : array();
						if($foundItem){
							$s_sql = "SELECT * FROM contactperson WHERE customerId = ? AND fullname_for_import_comparing = ?";
							$o_query = $o_main->db->query($s_sql, array($foundItem['id'], $fullname_for_import_comparing));
							$contactpersonPrivate = $o_query ? $o_query->row_array() : array();
							if($contactpersonPrivate){
								$o_query = $o_main->db->query("INSERT INTO contactperson_group_user SET
									created = NOW(),
									createdBy = ?,
									contactperson_group_id = ?,
									contactperson_id = ?,
									type = 1,
									status = 0", array("imported - ".$user, $_POST['group_id'], $contactpersonPrivate['id']));
								if(!$o_query) die($o_main->db->error());
							}
						}
					}
				}
			}
		} else {
			if(count($set) > 3){
				$set[] = "moduleID = 41";
				$insertTable = $o_main->db_escape_name($insertTable);
				$idField = $o_main->db_escape_name($idField);
				$o_query = $o_main->db->query("SELECT * FROM ".$insertTable." WHERE ".$idField." = ?", array($idValue));
				$foundItem = $o_query ? $o_query->row_array() : array();
				$customerId = 0;
				$createNewCustomer = false;
				if($_POST['notAllowDuplicatePublicRegisterId']){
					if($foundItem){
						$createNewCustomer = false;
					} else {
						$createNewCustomer = true;
					}
				} else {
					$createNewCustomer = true;
				}
				if($createNewCustomer){
					$s_sql  = "INSERT INTO ".$insertTable." SET ".implode(", ", $set).";";
					if(!$o_main->db->query($s_sql)) die($o_main->db->error());
					$customerId = $o_main->db->insert_id();
				} else {
					$foundItem = $o_query->row_array();
					$customerId = $foundItem['id'];
				}
				if($customerId > 0) {
					if(count($set2) > 2){
						$set2[] = "type = 1";
						$set2[] = "customerId = ".$customerId;
						$s_sql = "SELECT * FROM contactperson WHERE customerId = ? AND email = ?";
						$o_query = $o_main->db->query($s_sql, array($customerId, $contactpersonEmail));
						$contactpersonPrivate = $o_query ? $o_query->row_array() : array();
						if(!$contactpersonPrivate) {
							$s_sql  = "INSERT INTO contactperson SET ".implode(", ", $set2).";";
							if(!$o_main->db->query($s_sql)) die($o_main->db->error());
						}
					}

					if($_POST['ownercompany'] > 0) {
						$ownercompanyIdPost = $_POST['ownercompany'];
						foreach($setExtrenal as $external_sys_id){
							$o_query = $o_main->db->query("SELECT * FROM customer_externalsystem_id WHERE external_id = ? AND ownercompany_id = ? AND customer_id = ?", array($external_sys_id, $ownercompanyIdPost, $customerId));
							if($o_query && $o_query->num_rows()>0)
							{
								$selfdefinedFieldValue = $o_query->row_array();
								$o_main->db->query("UPDATE customer_externalsystem_id SET  external_id = ?, ownercompany_id = ?, customer_id = ? WHERE id = ?", array($external_sys_id, $ownercompanyIdPost, $customerId, $selfdefinedFieldValue['id']));
							} else {
								$o_main->db->query("INSERT INTO customer_externalsystem_id SET  external_id = ?, ownercompany_id = ?, customer_id = ?", array($external_sys_id, $ownercompanyIdPost, $customerId));
							}
						}
					}

					foreach($setSelfdefined as $selfdefinedRow){
						foreach($selfdefinedRow as $selfdefinedId => $sefdefinedValue){
							$valueCheck = 0;
							$valueString = "";
							if(strtolower(trim($sefdefinedValue)) == "x"){
								$valueCheck = 1;
							} else {
								$valueCheck = 1;
								$valueString = $sefdefinedValue;
							}
							$o_query = $o_main->db->query("SELECT * FROM customer_selfdefined_values WHERE customer_id = ? AND selfdefined_fields_id = ?", array($customerId, $selfdefinedId));
							if($o_query && $o_query->num_rows()>0)
							{
								$selfdefinedFieldValue = $o_query->row_array();
								$o_main->db->query("UPDATE customer_selfdefined_values SET text = ?, active = ? WHERE id = ?", array($valueString, $valueCheck, $selfdefinedFieldValue['id']));
							} else {
								$o_main->db->query("INSERT INTO customer_selfdefined_values SET customer_id = ?, selfdefined_fields_id = ?, value = ?, active = ?", array($customerId, $selfdefinedId, $valueString, $valueCheck));
							}
						}
					}
				}

				if($_POST['createProspect']) {
					$prospectType = $_POST['prospectType'];
					$employeeId = $_POST['employeeId'];
					if($prospectType != "" && $employeeId != "") {
						$s_sql = "INSERT INTO prospect SET
						id=NULL,
						moduleID = ?,
						created = now(),
						createdBy= ?,
						customerId = ?,
						prospecttypeId = ?,
						employeeId = ?,
						value=?,
						info = ?";
						$o_main->db->query($s_sql, array($moduleID, $variables->loggID, $customerId, $prospectType, $employeeId, $prospectValue, $prospectInfo));
					}
				}
			}
		}
		unset($set);
		unset($set2);
		unset($setSelfdefined);
		unset($setExtrenal);
		unset($setProspect);
	}
	if($_SERVER['HTTP_REFERER']) {
		header("Location: " . $_SERVER['HTTP_REFERER']);
	} else {
		die('UNKNOWN HTTP_REFERER');
	}
}

if($developeraccess >= 5 || $v_customer_accountconfig['activateCompanyImportingForAll'])
{
	?>
	<div class="btnStyle">
		<div class="text"><a href="#import" data-toggle="modal" data-target="#exampleModal2"><?php echo $formText_ImportCompany_Output;?></a></div>
	</div>
	<style>
	.labelSpan {
		display: inline-block;
		width: 140px;
	}
	#importForm2 {
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
	</style>


	<script type="text/javascript" src="../modules/<?php echo $module?>/output/elementsOutput/encoding.min.js"></script>
	<script>
	$(".fancybox-wrap").unbind('mousewheel.fb');
	function generateDragable2(f,s) {
		$('#importForm2 #csvimportfields2 .draggable').remove();
		if(s == "t"){
			fields = f.split("\t");
		} else{
			fields = f.split(s);
		}
		$("#importForm2 #dbFields2").html('');
		$.each(fields , function(i, val) {
			if(val.trim() != ""){
				$("#importForm2 #dbFields2").append(' <div class="draggable" id="field_'+val+'" data-scope="'+val+'">'+val+'</div>');
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
						$('#importForm2 #dbFields2').append($('.ui-draggable', this));

						$(this).append(ui.draggable);
						$(this).find('div.label').hide();

					}
				}
			});
		});

	}

	$(document).ready(function () {
		// $(".subscription_start_date").datepicker({
		// 	firstDay: 1,
        //     dateFormat: 'dd.mm.yy',
		// })
		var spliter = ",";


		$("#importForm2 #separator").change(function(e) {
			$("#importForm2 #spliter").val( $(this).val() );
			spliter = $(this).val();
			generateDragable2($('#importForm2 input[name="csvFields"]').val(), spliter);
		});

		$("#importForm2 #filenameImport2").change(function(e) {
			var ext = $("#importForm2 input#filenameImport2").val().split(".").pop().toLowerCase();
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
							$('#importForm2 input[name="csv"]').val(e.target.result);
							var csvval=e.target.result.split("\n");
							var csvvalue=csvval[0];
							$('#importForm2 input[name="csvFields"]').val(csvvalue);
							generateDragable2($('#importForm2 input[name="csvFields"]').val(), spliter);
						};
						reader2.readAsText(file);
					} else {
						alert("<?php echo $formText_PossibleToUploadOnlyFilesInUTF8Encoding_output;?>");
					}
				};
				reader.readAsArrayBuffer(file);

			}
			$('#importForm2 #importButton2').prop("disabled", false);
			$('#importForm2 #csvimportfields2 a.label').show();
			return false;

		});

		// $('#importButton2').off("click").on("click", function(e){
		// 	e.preventDefault();
		// 	$("#importForm2 form").submit()
		// })
		$('#importForm2 form').submit(function(e){

			$('#importForm2 #csvimportfields2 .boxes input.fieldsToCheck').each(function(index, div) {
				if(!$(this).val() || $(this).parent().parent().find('.draggable').length) {
					$(this).remove();
				}
			});

			$('#importForm2 #csvimportfields2 .droppable').each(function(index, div) {
				var csvField = $(this).attr('id').replace('csv_','');
				var dbFiled = 0;
				if($(this).find('.draggable').length) {
					dbFiled = $(this).find('.draggable').attr('id').replace('field_','');
					if($(this).hasClass("contactPerson1")){
						$('#importForm2 input[name="field[contactperson1_'+csvField+']"]').val(dbFiled);
					} else if ($(this).hasClass("contactPerson2")) {
						$('#importForm2 input[name="field[contactperson2_'+csvField+']"]').val(dbFiled);
					} else if ($(this).hasClass("ownerFromDate")) {
						$('#importForm2 input[name="field['+csvField+']"]').val(dbFiled);
					} else if ($(this).hasClass("selfdefined")) {
						$('#importForm2 input[name="field['+csvField+']"]').val(dbFiled);
					} else if ($(this).hasClass("customerNumber")) {
						$('#importForm2 input[name="field[customerNumber]"]').val(dbFiled);
					} else {
						$('#importForm2 input[name="field['+csvField+']"]').val(dbFiled);
					}

				}
			});
			//alert('importing...');	return false;
		});

		$(".createProspectSelect").change(function(){
			var mainFields = $("#csvimportfields2");

			$("#csvimportfields2 select").prop("required", false);
			$("#csvimportfields2 input:not(.fieldsToCheck)").prop("required", false);
			var value = $(this).val();
			if(value == 1) {
				mainFields.find(".subscriptionWrapper").hide();
				mainFields.find(".subscriptionWrapper select").prop("required", false);
				mainFields.find(".subscriptionWrapper input:not(.fieldsToCheck)").prop("required", false);

				mainFields.find(".groupConnectionWrapper").hide();
				mainFields.find(".groupConnectionWrapper select").prop("required", false);

				mainFields.find(".prospectTypesSelect").show();
				mainFields.find(".employeeIdSelect").show();
				mainFields.find(".prospectValue").show();
				mainFields.find(".prospectTypesSelect .prospectType").prop("required", true);
				mainFields.find(".employeeIdSelect .employeeId").prop("required", true);
				mainFields.find(".insertWrapper").show();
				mainFields.find(".notCpImportWrapper").show();
			} else if(value == 2) {
				mainFields.find(".subscriptionWrapper").hide();
				mainFields.find(".subscriptionWrapper select").prop("required", false);
				mainFields.find(".subscriptionWrapper input:not(.fieldsToCheck)").prop("required", false);

				mainFields.find(".groupConnectionWrapper").hide();
				mainFields.find(".groupConnectionWrapper select").prop("required", false);

				mainFields.find(".prospectTypesSelect").hide();
				mainFields.find(".employeeIdSelect").hide();
				mainFields.find(".prospectValue").hide();
				mainFields.find(".prospectTypesSelect .prospectType").prop("required", false);
				mainFields.find(".employeeIdSelect .employeeId").prop("required", false);
				mainFields.find(".insertWrapper").hide();
				mainFields.find(".notCpImportWrapper").show();
			} else if(value == 3){
				mainFields.find(".subscriptionWrapper").hide();
				mainFields.find(".subscriptionWrapper select").prop("required", false);
				mainFields.find(".subscriptionWrapper input:not(.fieldsToCheck)").prop("required", false);

				mainFields.find(".groupConnectionWrapper").hide();
				mainFields.find(".groupConnectionWrapper select").prop("required", false);

				mainFields.find(".insertWrapper").hide();
				mainFields.find(".prospectTypesSelect").hide();
				mainFields.find(".employeeIdSelect").hide();
				mainFields.find(".prospectValue").hide();
				mainFields.find(".prospectTypesSelect .prospectType").prop("required", false);
				mainFields.find(".employeeIdSelect .employeeId").prop("required", false);

				mainFields.find(".notCpImportWrapper").hide();
				mainFields.find(".cpWrapper").show();
			} else if(value == 4){
				mainFields.find(".groupConnectionWrapper").hide();
				mainFields.find(".groupConnectionWrapper select").prop("required", false);

				mainFields.find(".insertWrapper").hide();
				mainFields.find(".prospectTypesSelect").hide();
				mainFields.find(".employeeIdSelect").hide();
				mainFields.find(".prospectValue").hide();
				mainFields.find(".prospectTypesSelect .prospectType").prop("required", false);
				mainFields.find(".employeeIdSelect .employeeId").prop("required", false);
				mainFields.find(".notCpImportWrapper").hide();

				mainFields.find(".subscriptionWrapper").show();
				mainFields.find(".subscriptionWrapper select").prop("required", true);
				mainFields.find(".subscriptionWrapper input:not(.fieldsToCheck)").prop("required", true);
			} else if(value == 5){
				mainFields.find(".subscriptionWrapper").hide();
				mainFields.find(".subscriptionWrapper select").prop("required", false);
				mainFields.find(".subscriptionWrapper input:not(.fieldsToCheck)").prop("required", false);

				mainFields.find(".insertWrapper").hide();
				mainFields.find(".prospectTypesSelect").hide();
				mainFields.find(".employeeIdSelect").hide();
				mainFields.find(".prospectValue").hide();
				mainFields.find(".prospectTypesSelect .prospectType").prop("required", false);
				mainFields.find(".employeeIdSelect .employeeId").prop("required", false);
				mainFields.find(".notCpImportWrapper").hide();

				mainFields.find(".groupConnectionWrapper").show();
				mainFields.find(".groupConnectionWrapper select").prop("required", true);
			} else {
				mainFields.find(".subscriptionWrapper").hide();
				mainFields.find(".subscriptionWrapper select").prop("required", false);
				mainFields.find(".subscriptionWrapper input:not(.fieldsToCheck)").prop("required", false);

				mainFields.find(".groupConnectionWrapper").hide();
				mainFields.find(".groupConnectionWrapper select").prop("required", false);

				mainFields.find(".prospectTypesSelect").hide();
				mainFields.find(".employeeIdSelect").hide();
				mainFields.find(".prospectValue").hide();
				mainFields.find(".prospectTypesSelect .prospectType").prop("required", false);
				mainFields.find(".employeeIdSelect .employeeId").prop("required", false);
				mainFields.find(".insertWrapper").show();
				mainFields.find(".notCpImportWrapper").show();
			}
		})

	});
	</script>
	<?php
	ob_start();
	?>
		<div id="importForm2">
			<form method="post" action="/accounts/<?=$_GET['accountname']?>/modules/<?=$_GET['module']?>/output/includes/ajax.import_data_company.php?caID=<?=$_GET['caID']?>" accept-charset="UTF-8">
			<p align="right">
				<?php echo $formText_Import_output;?>:
				<select name="createProspect" class="createProspectSelect" autocomplete="off" required>
					<option value=""><?php echo $formText_Choose_output;?></option>
					<option value="0"><?php echo $formText_ImportOnlyCompanies_output?></option>
					<option value="1"><?php echo $formText_ImportProspects_output?></option>
					<option value="2"><?php echo $formText_UpdatingImport_output?></option>
					<option value="3"><?php echo $formText_ImportContactPersons_output?></option>
					<option value="4"><?php echo $formText_ImportSubscriptions_output?></option>
					<option value="5"><?php echo $formText_ImportPersonGroupConnection_output?></option>
				</select>
			</p>

			<p align="center">
				<b><?php echo $formText_PleaseSelectCsvFileForImport_output;?>:</b> <input type="file" name="filenameImport" id="filenameImport2">
			</p>

			<p align="center">
				<b><?php echo $formText_PleaseSelectCsvFileSeperatorValue_output;?>:</b>
				<select name="separator" id="separator" autocomplete="off">
					<option value=",">, (commas)</option>
					<option value=";">; (semi-colons)</option>
					<option value=":">: (colons)</option>
					<option value="|">| (pipes)</option>
					<option value="t">&nbsp;&nbsp;(tab)</option>
				</select>
				<br/>
				<br/>
				<?php echo $formText_FirstLineOfFileWillNotBeImported_output;?>
			</p>
			<p align="center">
				<input type="hidden" name="csvFields" value="">
			</p>

			<div class="half1">
				<div id="dbFields2" class="droppable bank">
				</div>
			</div>


			<div class="half2" id="csvimportfields2">
				<?php

				$s_sql = "SELECT * FROM contactperson WHERE content_status < 2 AND type = ? ORDER BY sortnr";
				$o_query = $o_main->db->query($s_sql, array($people_contactperson_type));
				$employees = $o_query ? $o_query->result_array() : array();

				$s_sql = "SELECT * FROM prospecttype WHERE content_status < 2 ORDER BY sortnr";
				$o_query = $o_main->db->query($s_sql);
				$prospecttypes = $o_query ? $o_query->result_array() : array();


				?>

				<input type="hidden" name="developeraccess" value="<?php echo $variables->developeraccess;?>" autocomplete="off">
				<div class="prospectTypesSelect">
					<?php echo $formText_ProspectType_output;?>:
					<select name="prospectType" class="prospectType" autocomplete="off">
						<option value=""><?php echo $formText_Choose_output;?></option>
						<?php
						foreach($prospecttypes as $prospecttype) {
							?>
							<option value="<?php echo $prospecttype['id']?>"><?php echo $prospecttype['name'];?></option>
							<?php
						}
						?>
					</select>
				</div>
				<div class="employeeIdSelect" >
					<?php echo $formText_Employee_output;?>:
					<select name="employeeId" class="employeeId" autocomplete="off">
						<option value=""><?php echo $formText_Choose_output;?></option>
						<?php
						foreach($employees as $employee) {
							?>
							<option value="<?php echo $employee['id']?>"><?php echo $employee['name']." ".$employee['middlename']." ".$employee['lastname'];?></option>
							<?php
						}
						?>
					</select>
				</div>
				<div class="prospectValue">
					<?php
					$field = "prospect_value";
					?>
					<div id="csv_<?=$field?>" class="droppable prospect_value home" data-scope="<?=$field?>">
						<?=$formText_ProspectValue_output;?> <sup><a class="label" href="#label_<?=$field?>" onclick="$($(this).attr('href')).parent().show(); return false;">label</a></sup>
						<div class="label">
							<input type="text" name="<?=$field?>" id="label_<?=$field?>" value="" placeholder="<?=$field?>" autocomplete="off">
							<a href="#label_<?=$field?>" onclick="$($(this).attr('href')).val('').parent().hide(); return false;">[x]</a>
						</div>
					</div>
					<?php
					$field = "prospect_info";
					?>
					<div id="csv_<?=$field?>" class="droppable prospect_value home" data-scope="<?=$field?>">
						<?=$formText_ProspectInfo_output;?> <sup><a class="label" href="#label_<?=$field?>" onclick="$($(this).attr('href')).parent().show(); return false;">label</a></sup>
						<div class="label">
							<input type="text" name="<?=$field?>" id="label_<?=$field?>" value="" placeholder="<?=$field?>" autocomplete="off">
							<a href="#label_<?=$field?>" onclick="$($(this).attr('href')).val('').parent().hide(); return false;">[x]</a>
						</div>
					</div>
				</div>
				<div class="insertWrapper">
					<label><?php echo $formText_NotAllowDuplicatePublicRegisterId_output;?></label> <input type="checkbox" name="notAllowDuplicatePublicRegisterId" id="notAllowDuplicatePublicRegisterId"/>
				</div><br/>
				<div>
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
							<option value="<?php echo $ownercompany['id'];?>" <?php if(count($ownercompanies) == 1 && $default_own['id'] == $ownercompany['id']) { echo 'selected';}?>><?php echo $ownercompany['name'];?></option>
						<?php } ?>
					</select>
					<?php
					$field = "customerNumber";
					?>
					<div id="csv_<?=$field?>" class="droppable customerNumber home" data-scope="<?=$field?>">
						<?=$formText_CustomerNumber_output;?> <sup><a class="label" href="#label_<?=$field?>" onclick="$($(this).attr('href')).parent().show(); return false;">label</a></sup>
						<div class="label">
							<input type="text" name="<?=$field?>" id="label_<?=$field?>" value="" placeholder="<?=$field?>" autocomplete="off">
							<a href="#label_<?=$field?>" onclick="$($(this).attr('href')).val('').parent().hide(); return false;">[x]</a>
						</div>
					</div>
				</div>
				<br/>
				<div class="boxes">

				<div class="notCpImportWrapper">
					<?php
					// $defaultFields = array('id','moduleID','createdBy','created','updatedBy','updated','origId','sortnr','seotitle','seodescription','seourl', 'content_status',
					// 'companyType', 'notOverwriteByImport', 'consideredIrrelevant', 'creditApproved', 'creditLimit', 'textVisibleInMyProfie',
					// 'numberOfUnits', 'associationId', 'housingcooperativeType', 'getynet_customer_id', 'create_filearchive_folder', 'articlePriceMatrixId', 'articleDiscountMatrixId',
					// 'user_registration', 'user_registration_link', 'user_registration_token', 'user_registration_domain', 'ownerFromDate', 'industries','financialYear', 'revenue',
					// 'municipalityName', 'publicRegisterContactperson', 'publicRegisterContactpFunction', 'revenueManuallyAdded', 'revenueManuallyAddedYear', 'numberOfEmplyees', 'comments',
					// 'textVisibleInMyProfile', 'industryCode', 'industryText', 'customerType', 'iaStreet1', 'iaStreet2', 'iaPostalNumber', 'iaCity', 'iaCountry', 'useOwnInvoiceAdress',
					// 'mobile', 'fax', 'middlename', 'lastname', 'personnumber', 'birthdate'
					// );
					$defaultFields = array('publicRegisterId', "name", "paStreet", "paPostalNumber", "paCity", "paCountry", "vaStreet", "vaPostalNumber", "vaCity", "vaCountry",
					"phone", "email", "homepage", "invoiceBy", "invoiceEmail", "vaStreet2", "paStreet2", "credittimeDays", "overrideAdminFeeDefault", "extra1", "extra2", "extra3", "extra4");
					foreach($dbfields as $field) {
							if(in_array($field, $defaultFields) ) {
					?>
						<div id="csv_<?=$field?>" class="droppable home" data-scope="<?=$field?>">
							<?=$field?> <sup><a class="label" href="#label_<?=$field?>" onclick="$($(this).attr('href')).parent().show(); return false;">label</a></sup>
							<div class="label">
								<input type="text" class="fieldsToCheck" name="customlabel[<?=$field?>]" id="label_<?=$field?>" value="" placeholder="<?=$field?>" autocomplete="off">
								<a href="#label_<?=$field?>" onclick="$($(this).attr('href')).val('').parent().hide(); return false;">[x]</a>
							</div>
						</div>
					<?php }
					}
					?>
				</div>
				<div class="notCpImportWrapper insertWrapper">
					<div class="contactPersonTitle"><?php echo $formText_SelfDefinedFields_Output;?></div>
					<?php
					$selfdefinedFields = array();
					$o_query = $o_main->db->query("SELECT * FROM customer_selfdefined_fields ORDER BY name");
					if($o_query && $o_query->num_rows()>0)
					foreach($o_query->result_array() as $v_row)
					{
						array_push($selfdefinedFields, $v_row);
					}
					foreach($selfdefinedFields as $selfdefinedField)
					{
						$field = "selfdefined_".$selfdefinedField['id'];
						?>
						<div id="csv_<?=$field?>" class="droppable selfdefined home" data-scope="<?=$field?>">
							<?=$selfdefinedField['name']?> <sup><a class="label" href="#label_<?=$field?>" onclick="$($(this).attr('href')).parent().show(); return false;">label</a></sup>
							<div class="label">
								<input type="text" class="fieldsToCheck" name="customlabel[<?=$field?>]" id="label_<?=$field?>" value="" placeholder="<?=$field?>" autocomplete="off">
								<a href="#label_<?=$field?>" onclick="$($(this).attr('href')).val('').parent().hide(); return false;">[x]</a>
							</div>
						</div>
						<?php
					}
					?>
				</div>
				<?php
				?>
				<div class="cpWrapper insertWrapper">
					<div class="contactPersonTitle"><?php echo $formText_ContactPerson_output;?></div>
					<?php
					$defaultFields2 = array('name', 'middlename', 'lastname', 'mobile', 'email',  'title', 'fullname_for_import_comparing');
					foreach($dbfields2 as $field) {
						if(in_array($field, $defaultFields2) ) {
							$field = "contactperson_".$field;
					?>
						<div id="csv_<?=$field?>" class="droppable home" data-scope="<?=$field?>">
							<?php
							switch($field) {
								case "contactperson_name":
									echo $formText_FirstName_output;
								break;
								case "contactperson_middlename":
									echo $formText_MiddleName_output;
								break;
								case "contactperson_lastname":
									echo $formText_Lastname_output;
								break;
								case "contactperson_email":
									echo $formText_Email_output;
								break;
								case "contactperson_mobile":
									echo $formText_Mobile_output;
								break;
								case "contactperson_title":
									echo $formText_Title_output;
								break;
								case "contactperson_fullname_for_import_comparing":
									echo $formText_FullNameForImportComparing_output;
								break;
								default:
									echo $field;
								break;
							}
							?>
							<sup><a class="label" href="#label_<?=$field?>" onclick="$($(this).attr('href')).parent().show(); return false;">label</a></sup>
							<div class="label">
								<input type="text" class="fieldsToCheck" name="customlabel[<?=$field?>]" id="label_<?=$field?>" value="" placeholder="<?=$field?>" autocomplete="off">
								<a href="#label_<?=$field?>" onclick="$($(this).attr('href')).val('').parent().hide(); return false;">[x]</a>
							</div>
						</div>
					<?php }
					}
					?>
				</div>
				<div class="subscriptionWrapper" style="display: none;">
					<?php
					$field = "subscription_start_date";
					?>
					<div id="csv_<?=$field?>" class="droppable home" data-scope="<?=$field?>">
						<?php
						switch($field) {
							case "subscription_start_date":
								echo $formText_StartDate_output;
							break;
							default:
								echo $field;
							break;
						}
						?>
						<sup><a class="label" href="#label_<?=$field?>" onclick="$($(this).attr('href')).parent().show(); return false;">label</a></sup>
						<div class="label">
							<input type="text" class="fieldsToCheck" name="customlabel[<?=$field?>]" id="label_<?=$field?>" value="" placeholder="<?=$field?>" autocomplete="off">
							<a href="#label_<?=$field?>" onclick="$($(this).attr('href')).val('').parent().hide(); return false;">[x]</a>
						</div>
					</div>

					<div style="margin-bottom: 5px;">
						<span class="labelSpan">
							<?php echo $formText_PeriodLength_output;?>:
						</span>
						<input type="text" name="subscription_period_length" value="" class="subscription_period_length" autocomplete="off"/>
					</div>
					<div style="margin-bottom: 5px;">
						<span class="labelSpan">
							<?php echo $formText_SubscriptionType_output;?>:
						</span>
						<select name="subscription_type" class="subscription_type" autocomplete="off">
							<option value=""><?php echo $formText_Choose_output;?></option>
							<?php
		                    $s_sql = "SELECT * FROM subscriptiontype WHERE content_status < 2 ORDER BY name";
		                    $o_query = $o_main->db->query($s_sql);
		                    $subscriptionTypes = ($o_query ? $o_query->result_array():array());

							foreach($subscriptionTypes as $subscriptionType) {
								?>
								<option value="<?php echo $subscriptionType['id']?>"><?php echo $subscriptionType['name'];?></option>
								<?php
							}
							?>
						</select>
					</div>
				</div>
				<div class="groupConnectionWrapper" style="display: none;">
					<?php
					$field = "groupconnection_fullname_for_comparing";
					?>
					<div id="csv_<?=$field?>" class="droppable home" data-scope="<?=$field?>">
						<?php
						echo $formText_FullNameForImportComparing_output;
						?>
						<sup><a class="label" href="#label_<?=$field?>" onclick="$($(this).attr('href')).parent().show(); return false;">label</a></sup>
						<div class="label">
							<input type="text" class="fieldsToCheck" name="customlabel[<?=$field?>]" id="label_<?=$field?>" value="" placeholder="<?=$field?>" autocomplete="off">
							<a href="#label_<?=$field?>" onclick="$($(this).attr('href')).val('').parent().hide(); return false;">[x]</a>
						</div>
					</div>

					<div style="margin-bottom: 5px;">
						<span class="labelSpan">
							<?php echo $formText_Group_output;?>:
						</span>
						<select name="group_id" class="group_id" autocomplete="off">
							<option value=""><?php echo $formText_Choose_output;?></option>
							<?php
							$s_sql = "SELECT * FROM contactperson_group WHERE status = 1 ORDER BY name";
							$o_query = $o_main->db->query($s_sql);
							$groups = ($o_query ? $o_query->result_array():array());

							foreach($groups as $group) {
								?>
								<option value="<?php echo $group['id']?>"><?php echo $group['name'];?></option>
								<?php
							}
							?>
						</select>
					</div>
				</div>

				<input type="submit" name="" id="importButton2" value="IMPORT" disabled="disabled">
				<input type="hidden" name="csv" value="">
				<input type="hidden" name="spliter" id="spliter" value=",">
				<input type="hidden" name="table" value="customer">

				<?php foreach($dbfields as $field) { ?>
					<input type="hidden" name="field[<?=$field?>]" value="">
				<?php } ?>
				<?php foreach($selfdefinedFields as $selfdefinedField) {
					$field = "selfdefined_".$selfdefinedField['id']; ?>
					<input type="hidden" name="field[<?=$field?>]" value="">
				<?php } ?>
				<?php foreach($dbfields2 as $field) {
					$field = "contactperson_".$field; ?>
					<input type="hidden" name="field[<?=$field?>]" value="">
				<?php } ?>
				<input type="hidden" name="field[groupconnection_fullname_for_comparing]" value="">
				<input type="hidden" name="field[customerNumber]" value="">
				<input type="hidden" name="field[prospect_value]" value="">
				<input type="hidden" name="field[subscription_start_date]" value="">


				</div>
			</div>
			<div style="clear: both;"></div>
			</form>
		</div>
	<?php
	$s_popup = ob_get_clean();
	?>
	<div id="exampleModal2" class="modal fade" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg allowScroll" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Modal title</h4>
      </div>
      <div class="modal-body">
        <?php echo $s_popup;?>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<?php
}
?>
