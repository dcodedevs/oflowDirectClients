<?php
//session_start();
$user = $variables->loggID?$variables->loggID:$_COOKIE['username'];

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
	$rows = explode("\n",$_POST['csv']);
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
		$set[] = "created = NOW()";
		$set[] = "createdby = ".$o_main->db->escape("imported - ".$user);
		$set[] = "customerType = 1";

		$set2[] = "created = NOW()";
		$set2[] = "createdby = ".$o_main->db->escape("imported - ".$user);
		$set2[] = "privatePersonCustomer = 1";


		$setSelfdefined = array();

		$name = "";
		foreach($relation as $dbField=>$csvField) {
			if(trim($row[$csvField]) != ""){
				if(strpos($dbField, "selfdefined") === false && strpos($dbField, "contactperson") === false) {
					$rowData = $row[$csvField];
					$rowData = str_replace(array("\n", "\t", "\r"), '', $rowData);
					if($dbField == 'rentalUnit' || $dbField == 'wantInfoElectronic' || $dbField == 'mustReceiveInfoOnPaper') {
						if(strtolower($row[$csvField]) == "x"){
							$rowData = 1;
						} else {
							$rowData = 0;
						}
					}
					$set[] = $o_main->db_escape_name($dbField)." = ".$o_main->db->escape($rowData);
				} else if(strpos($dbField, "contactperson") === false) {
					$realDbFieldArray = explode("_", $dbField, 2);

					$rowData = $row[$csvField];
					$rowData = str_replace(array("\n", "\t", "\r"), '', $rowData);

					$setSelfdefined[] = array($realDbFieldArray[1]=>$rowData);
				} else {
					if($dbField == "contactpersonemail") {
						$set2[] = "email = ".$o_main->db->escape(str_replace(array("\n", "\t", "\r"), '', $row[$csvField]));
					}
					if($dbField == "contactpersonmobile") {
						$set2[] = "mobile = ".$o_main->db->escape(str_replace(array("\n", "\t", "\r"), '', $row[$csvField]));
					}
				}
				if($dbField == "personnumber") {
					$idField = $dbField;
					$idValue = $row[$csvField];
				}
				if($dbField == "invoiceEmail" && $rowData != "") {
					$set[] = "invoiceBy = 1";
				}
				if($dbField == "name" || $dbField == "middlename" || $dbField == "lastname") {
					$set2[] = $o_main->db_escape_name($dbField)." = ".$o_main->db->escape($rowData);
				}
			}
		}
		if(count($set) > 2){
			$set[] = "moduleID = 41";
			$insertTable = $o_main->db_escape_name($insertTable);
			$idField = $o_main->db_escape_name($idField);
			$o_query = $o_main->db->query("SELECT * FROM ".$insertTable." WHERE ".$idField." = ?", array($idValue));
			if(!$o_query || $o_query->num_rows() == 0){
				$s_sql  = "INSERT INTO ".$insertTable." SET ".implode(", ", $set).";";
				if(!$o_main->db->query($s_sql)) die($o_main->db->error());
				$customerId = $o_main->db->insert_id();
			} else {
				$foundItem = $o_query->row_array();
				$customerId = $foundItem['id'];
			}
			if($customerId > 0) {
				$set2[] = "customerId = ".$customerId;
				$s_sql = "SELECT * FROM contactperson WHERE customerId = ? AND privatePersonCustomer = 1";
	            $o_query = $o_main->db->query($s_sql, array($customerId));
	            $contactpersonPrivate = $o_query ? $o_query->row_array() : array();
	            if(!$contactpersonPrivate) {
					$s_sql  = "INSERT INTO contactperson SET ".implode(", ", $set2).";";
					if(!$o_main->db->query($s_sql)) die($o_main->db->error());
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
		}
		unset($set);
		unset($set2);
	}
	if($_SERVER['HTTP_REFERER']) {
		header("Location: " . $_SERVER['HTTP_REFERER']);
	} else {
		die('UNKNOWN HTTP_REFERER');
	}
}

if($variables->developeraccess >= 5)
{
	?>
	<div class="btnStyle">
		<div class="text"><a href="#import" data-toggle="modal" data-target="#exampleModal"><?php echo $formText_Import_Output;?></a></div>
	</div>
	<style>
	#importForm {
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
	#csvimportfields {
		*display: none;
	}

	#dbFields {
		min-height: 100px;
		width: 200px;
	}
	#dbFields:after{
		display:block;
		content:"";
		clear:both;
	}
	#dbFields div {
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
	#csvimportfields .droppable {
		width: 90%;
		clear: both;
		border: 1px dotted black; margin: 2px 0px;
		min-height: 26px;
		position: relative;

	}
	#csvimportfields .draggable {
		background: #DDD;
		float: right;
		width: 200px;
	}
	</style>


	<script type="text/javascript" src="../modules/<?php echo $module?>/output/elementsOutput/encoding.min.js"></script>
	<script>
	$(".fancybox-wrap").unbind('mousewheel.fb');
	function generateDragable(f,s) {
		$('#importForm #csvimportfields .draggable').remove();
		if(s == "t"){
			fields = f.split("\t");
		} else{
			fields = f.split(s);
		}
		$("#importForm #dbFields").html('');
		$.each(fields , function(i, val) {
			if(val.trim() != ""){
				$("#importForm #dbFields").append(' <div class="draggable" id="field_'+val+'" data-scope="'+val+'">'+val+'</div>');
			}
		});


		$('.draggable').on('mousedown', function(e) {
			$('.draggable').each(function(index, div) {
				var scope = $(this).attr('data-scope');
				$(div).draggable({

					stop: function() {
						$('.droppable').droppable('option', 'disabled', false);
					},

					helper: 'clone'
				});
			});

			$('.droppable').droppable({

				drop: function(event, ui) {

					var x = $(this).find('.draggable');

					if(!$(this).attr('id')==='dbFields'){
						if(!x.length){
							$(this).append(ui.draggable);
						}else{

						}
					}else{
						$('#importForm #dbFields').append($('.ui-draggable', this));

						$(this).append(ui.draggable);
						$(this).find('div.label').hide();

					}
				}
			});
		});

	}

	$(document).ready(function () {

		var spliter = ",";


		$("#importForm #separator").change(function(e) {
			$("#importForm #spliter").val( $(this).val() );
			spliter = $(this).val();
			generateDragable($('#importForm input[name="csvFields"]').val(), spliter);
		});

		$("#importForm #filenameImport").change(function(e) {
			var ext = $("#importForm input#filenameImport").val().split(".").pop().toLowerCase();
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
							$('#importForm input[name="csv"]').val(e.target.result);
							var csvval=e.target.result.split("\n");
							var csvvalue=csvval[0];
							$('#importForm input[name="csvFields"]').val(csvvalue);
							generateDragable($('#importForm input[name="csvFields"]').val(), spliter);
						};
						reader2.readAsText(file);
					} else {
						alert("<?php echo $formText_PossibleToUploadOnlyFilesInUTF8Encoding_output;?>");
					}
				};
				reader.readAsArrayBuffer(file);

			}

			$('#importForm #importButton').prop("disabled", false);
			$('#importForm #csvimportfields a.label').show();
			return false;

		});


		$('#importForm #importButton').on('click', function(){
			$('#importForm #csvimportfields .boxes input').each(function(index, div) {
				if(!$(this).val() || $(this).parent().parent().find('.draggable').length) {
					$(this).remove();
				}
			});

			$('#importForm #csvimportfields .droppable').each(function(index, div) {
				var csvField = $(this).attr('id').replace('csv_','');
				var dbFiled = 0;
				if($(this).find('.draggable').length) {
					dbFiled = $(this).find('.draggable').attr('id').replace('field_','');
					if($(this).hasClass("contactPerson1")){
						$('#importForm input[name="field[contactperson1_'+csvField+']"]').val(dbFiled);
					} else if ($(this).hasClass("contactPerson2")) {
						$('#importForm input[name="field[contactperson2_'+csvField+']"]').val(dbFiled);
					} else if ($(this).hasClass("ownerFromDate")) {
						$('#importForm input[name="field['+csvField+']"]').val(dbFiled);
					} else if ($(this).hasClass("selfdefined")) {
						$('#importForm input[name="field['+csvField+']"]').val(dbFiled);
					} else {
						$('#importForm input[name="field['+csvField+']"]').val(dbFiled);
					}

				}
			});
			//alert('importing...');	return false;
		});

	});
	</script>
	<?php
	ob_start();
	?>
		<div id="importForm">
			<p align="center">
				<b>Please select CSV file for import:</b> <input type="file" name="filenameImport" id="filenameImport">
			</p>

			<p align="center">
				<b>Please select CSV file separator value:</b>
				<select name="separator" id="separator">
					<option value=",">, (commas)</option>
					<option value=";">; (semi-colons)</option>
					<option value=":">: (colons)</option>
					<option value="|">| (pipes)</option>
					<option value="t">&nbsp;&nbsp;(tab)</option>
				</select>
				<br/>
				<?php echo $formText_FirstLineOfFileWillNotBeImported_output;?>
			</p>
			<p align="center">
				<input type="hidden" name="csvFields" value="">
			</p>

			<div class="half1">
				<div id="dbFields" class="droppable bank">
				</div>
			</div>


			<div class="half2" id="csvimportfields">
			<form method="post" action="/accounts/<?=$_GET['accountname']?>/modules/<?=$_GET['module']?>/output/includes/ajax.import_data.php?caID=<?=$_GET['caID']?>" accept-charset="UTF-8">
			<div class="boxes">
			<?php
			// $defaultFields = array('id','moduleID','createdBy','created','updatedBy','updated','origId','sortnr','seotitle','seodescription','seourl', 'content_status',
			// 'companyType', 'publicRegisterId', 'notOverwriteByImport', 'consideredIrrelevant', 'creditApproved', 'creditLimit', 'textVisibleInMyProfie',
			// 'numberOfUnits', 'associationId', 'housingcooperativeType', 'getynet_customer_id', 'create_filearchive_folder', 'articlePriceMatrixId', 'articleDiscountMatrixId',
			// 'user_registration', 'user_registration_link', 'user_registration_token', 'user_registration_domain', 'ownerFromDate', 'industries','financialYear', 'revenue',
			// 'municipalityName', 'publicRegisterContactperson', 'publicRegisterContactpFunction', 'revenueManuallyAdded', 'revenueManuallyAddedYear', 'numberOfEmplyees', 'comments',
			// 'textVisibleInMyProfile', 'industryCode', 'industryText', 'customerType', 'iaStreet1', 'iaStreet2', 'iaPostalNumber', 'iaCity', 'iaCountry', 'useOwnInvoiceAdress',
			// 'phone', 'mobile', 'fax',
			// );
			$defaultFields = array('publicRegisterId', "name", "paStreet", "paPostalNumber", "paCity", "paCountry", "vaStreet", "vaPostalNumber", "vaCity", "vaCountry",
			"phone", "email", "homepage", "invoiceBy", "invoiceEmail", "vaStreet2", "paStreet2", "credittimeDays", "overrideAdminFeeDefault", "extra1", "extra2", "extra3", "extra4");

			foreach($dbfields as $field) {
					if(in_array($field, $defaultFields) ) {
			?>
				<div id="csv_<?=$field?>" class="droppable home" data-scope="<?=$field?>">
					<?=$field?> <sup><a class="label" href="#label_<?=$field?>" onclick="$($(this).attr('href')).parent().show(); return false;">label</a></sup>
					<div class="label">
						<input type="text" name="customlabel[<?=$field?>]" id="label_<?=$field?>" value="" placeholder="<?=$field?>" autocomplete="off">
						<a href="#label_<?=$field?>" onclick="$($(this).attr('href')).val('').parent().hide(); return false;">[x]</a>
					</div>
				</div>
			<?php }
			}
			?>
			<div id="csv_contactpersonemail" class="droppable home" data-scope="contactpersonemail">
				contactpersonemail <sup><a class="label" href="#label_contactpersonemail" onclick="$($(this).attr('href')).parent().show(); return false;">label</a></sup>
				<div class="label">
					<input type="text" name="customlabel[contactpersonemail]" id="label_contactpersonemail" value="" placeholder="contactpersonemail" autocomplete="off">
					<a href="#label_contactpersonemail" onclick="$($(this).attr('href')).val('').parent().hide(); return false;">[x]</a>
				</div>
			</div>
			<div id="csv_contactpersonmobile" class="droppable home" data-scope="contactpersonmobile">
				contactpersonmobile <sup><a class="label" href="#label_contactpersonmobile" onclick="$($(this).attr('href')).parent().show(); return false;">label</a></sup>
				<div class="label">
					<input type="text" name="customlabel[contactpersonmobile]" id="label_contactpersonmobile" value="" placeholder="<?=$field?>" autocomplete="off">
					<a href="#label_contactpersonmobile" onclick="$($(this).attr('href')).val('').parent().hide(); return false;">[x]</a>
				</div>
			</div>

			<div class="contactPersonTitle">Selfdefined fields</div>
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
						<input type="text" name="customlabel[<?=$field?>]" id="label_<?=$field?>" value="" placeholder="<?=$field?>" autocomplete="off">
						<a href="#label_<?=$field?>" onclick="$($(this).attr('href')).val('').parent().hide(); return false;">[x]</a>
					</div>
				</div>
				<?php
			}
			?>

			</div>

			<input type="submit" name="" id="importButton" value="IMPORT" disabled="disabled">
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
			<input type="hidden" name="field[contactpersonemail]" value="">
			<input type="hidden" name="field[contactpersonmobile]" value="">
			</form>

			</div>

			<div style="clear: both;"></div>
		</div>
	<?php
	$s_popup = ob_get_clean();
	?>
	<div id="exampleModal" class="modal fade" tabindex="-1" role="dialog">
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
