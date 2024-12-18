<?php
//session_start();
$user = $variables->loggID?$variables->loggID:$_COOKIE['username'];
$cid = $_GET['cid'];

if(!isset($o_main))
{
	define('BASEPATH', realpath(__DIR__."/../../../../").DIRECTORY_SEPARATOR);
	include(BASEPATH."elementsGlobal/cMain.php");
}
if(!function_exists("APIconnectorUser")) include(__DIR__."/../../../../fw/account_fw/includes/APIconnector.php");

$v_path = explode("/", realpath(__DIR__."/../"));
$s_module = array_pop($v_path);

$s_sql = "select * from session_framework where companyaccessID = ? and session = ? and username = ?";
$o_query = $o_main->db->query($s_sql, array($_GET['caID'], $_COOKIE['sessionID'], $_COOKIE['username']));
if($o_query && $o_query->num_rows()>0){
	$fw_session = $o_query->row_array();
}

$s_sql = "SELECT * FROM customer_accountconfig ORDER BY id DESC";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
    $v_customer_accountconfig = $o_query->row_array();
}

if(1 == $v_customer_accountconfig['activateKeycardsSystem'])
{
	$v_amadeus_systems = array();
	$integration = 'IntegrationAmadeus';
	$integration_file = __DIR__ . '/../../../'. $integration .'/internal_api/load.php';
	if (file_exists($integration_file)) {
		require_once $integration_file;
		if (class_exists($integration)) {
			$o_query = $o_main->db->query("SELECT * FROM integration_amadeus ORDER BY id");
			if($o_query && $o_query->num_rows()>0)
			foreach($o_query->result_array() as $v_row)
			{
				$v_amadeus_systems[$v_row['id']] = array(
					'name' => $v_row['name'],
					'api' => new $integration(array(
						'o_main' => $o_main,
						'config_id' => $v_row['id'],
					))
				);
			}
		}
	}
}

$module="Customer2";
$v_module_access = json_decode($fw_session['cache_menu'],true);
$l_access = $v_module_access[$module][2];
$dbfields = array();
include(__DIR__."/../../input/settings/fields/contactpersonfields.php");
foreach($prefields as $fieldinfo)
{
	$fieldinfo = explode('¤', $fieldinfo);
	$dbfields[] = $fieldinfo[0];
}
if(isset($_POST['table']))
{
	$cid = $_POST['cid'];
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


		$setSelfdefined = array();
		$departmentId = 0;
		$name = "";
		foreach($relation as $dbField=>$csvField)
		{
			if('keycard_access' == substr($dbField, 0, 14)) continue;
			$csvField = trim($csvField);
			if(trim($row[$csvField]) != "")
			{
				if(strpos($dbField, "department") === false) {
					$rowData = $row[$csvField];
					$rowData = str_replace(array("\n", "\t", "\r"), '', $rowData);
					if($dbField == 'rentalUnit' || $dbField == 'wantInfoElectronic' || $dbField == 'mustReceiveInfoOnPaper') {
						if(strtolower(trim($row[$csvField])) == "x"){
							$rowData = 1;
						} else {
							$rowData = 0;
						}
					}
					// Handle access card number
					if($dbField == "access_card_number_on_card")
					{
						$s_hex_converted = '';
						$s_hex = strtoupper(str_pad($rowData, 8, '0', STR_PAD_LEFT));
						for($i=-1; $i>=-4; $i--)
						{
							$s_tmp = substr($s_hex, 2*$i, 2);
							$s_hex_converted .= $s_tmp;
						}
						$s_dec = hexdec($s_hex_converted);
						//echo 'RAW: '.$rowData.' HEX: '.$s_hex.' DEC: '.$s_dec;
						$rowData = $s_hex;
						$set[] = "access_card_number = ".$o_main->db->escape($s_dec);
					}

					$set[] = $o_main->db_escape_name($dbField)." = ".$o_main->db->escape($rowData);
					if($dbField == "external_employee_id") {
						$idField = $dbField;
						$idValue = $row[$csvField];
					}
				} else {
					if($dbField == "email") {
						$rowData = preg_replace('/\s+/', '', $row[$csvField]);
					} else {
						$rowData = $row[$csvField];
					}
					$rowData = str_replace(array("\n", "\t", "\r"), '', $rowData);
					$departmentId = intval($rowData);
				}
			}
		}

		if(count($set) > 2){
			$set[] = "moduleID = 41";
			$set[] = "customerId = '".$cid."'";
			$set[] = "type = 1";
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
			if($customerId > 0)
			{
				if(1 == $v_customer_accountconfig['activateKeycardsSystem'])
				{
					$o_find = $o_main->db->query("SELECT * FROM ".$insertTable." WHERE id = '".$o_main->db->escape_str($customerId)."'");
					$v_cp = $o_find ? $o_find->row_array() : array();
					$o_find = $o_main->db->query("SELECT * FROM customer WHERE id = '".$o_main->db->escape_str($v_cp['customerId'])."'");
					$v_customer = $o_find ? $o_find->row_array() : array();
					// Divide name in parts
					$name_parts = explode(" ", preg_replace('/\s+/', ' ', $v_cp['name'].' '.$v_cp['middlename'].' '.$v_cp['lastname']));
					$first_name = $name_parts[0];
					$last_name = '';
					for($i = 1; $i < count($name_parts); $i++) {
						$last_name .= $name_parts[$i] . ' ';
					}
					$last_name = trim($last_name);
					$external_locksystem2_person_id = ($v_cp['external_locksystem2_person_id'] != '' ? $v_cp['external_locksystem2_person_id'] : 'D' . $v_cp['id']);

					foreach($v_amadeus_systems as $l_key => $v_amadeus)
					{
						set_time_limit(300);
						$v_groups = array();
						$v_items = $v_amadeus['api']->get_all_access_categories();
						if(sizeof($v_items))
						foreach($v_items as $v_item)
						{
							$csvField = trim(isset($relation['keycard_access'.$l_key.'_'.$v_item['ID']]) ? $relation['keycard_access'.$l_key.'_'.$v_item['ID']] : '');
							if('' != $csvField)
							{
								$row[$csvField] = str_replace(array("\n", "\t", "\r"), '', trim($row[$csvField]));
								if(strtolower($row[$csvField]) == "x")
								{
									$v_groups[] = $v_item['Name'];
								}
							}
						}

						if(0 < sizeof($v_groups))
						{
							$v_amadeus['api']->add_update_person($external_locksystem2_person_id, $first_name, $last_name, $v_cp['external_locksystem_pin'], $v_customer['name']);
							$v_keycards = $v_amadeus['api']->get_keycard($v_cp['access_card_number_on_card']);
							if(0 < count($v_keycards))
							{
								$v_amadeus['api']->delete_keycard($v_cp['access_card_number_on_card']);
							}
							$b_status = $v_amadeus['api']->add_keycard($v_cp['access_card_number_on_card'], $external_locksystem2_person_id);
							$v_amadeus['api']->update_person_access_categories($external_locksystem2_person_id, $v_groups);
						}
					}
					$o_main->db->query("UPDATE ".$insertTable." SET external_locksystem2_person_id = '".$o_main->db->escape_str($external_locksystem2_person_id)."' WHERE id = '".$o_main->db->escape_str($customerId)."'");
				}

				$fw_return_data = 1;
			}
		}
		unset($set);
	}
	if($_SERVER['HTTP_REFERER']) {
		header("Location: " . $_SERVER['HTTP_REFERER']);
	} else {
		die('UNKNOWN HTTP_REFERER');
	}
}
if($variables->developeraccess >= 0)
{
	?>
	<div class="addEntryBtn">
		<div class="text "><a href="" data-toggle="modal" data-target="#exampleModal4" class="fw_text_link_color"><?php echo $formText_Import_Output;?></a></div>
	</div>
	<style>
	#importForm4 {
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
		$('#importForm4 #csvimportfields .draggable').remove();
		if(s == "t"){
			fields = f.split("\t");
		} else{
			fields = f.split(s);
		}
		$("#importForm4 #dbFields").html('');
		$.each(fields , function(i, val) {
			if(val.trim() != ""){
				$("#importForm4 #dbFields").append(' <div class="draggable" id="field_'+val+'" data-scope="'+val+'">'+val+'</div>');
			}
		});
		updateDraggable();
		$('.draggable').on('mousedown', function(e) {
			updateDraggable();
		});

	}
	function updateDraggable(){

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
					$('#importForm4 #dbFields').append($('.ui-draggable', this));
					$(this).append(ui.draggable);
					$(this).find('div.label').hide();
				}
			}
		});
	}

	$(document).ready(function () {

		var spliter = ";";

		$('#importForm4 #output-keycard-access').off('change').on('change', function(e){
			$('.keycard-access-group').hide();
			if('' != $(this).val()) $('.keycard-access-group.group-' + $(this).val()).show();
		});


		$("#importForm4 #separator").change(function(e) {
			$("#importForm4 #spliter").val( $(this).val() );
			spliter = $(this).val();
			generateDragable($('#importForm4 input[name="csvFields"]').val(), spliter);
		});

		$("#importForm4 #filenameImport").change(function(e) {
			var ext = $("#importForm4 input#filenameImport").val().split(".").pop().toLowerCase();
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
							$('#importForm4 input[name="csv"]').val(e.target.result);
							var csvval=e.target.result.split("\n");
							var csvvalue=csvval[0];
							$('#importForm4 input[name="csvFields"]').val(csvvalue);
							generateDragable($('#importForm4 input[name="csvFields"]').val(), spliter);
						};
						reader2.readAsText(file);
					} else {
						alert("<?php echo $formText_PossibleToUploadOnlyFilesInUTF8Encoding_output;?>");
					}
				};
				reader.readAsArrayBuffer(file);

			}

			$('#importForm4 #importButton').prop("disabled", false);
			$('#importForm4 #csvimportfields a.label').show();
			return false;

		});


		$('#importForm4 #importButton').on('click', function(){
			$('#importForm4 #csvimportfields .boxes input').each(function(index, div) {
				if(!$(this).val() || $(this).parent().parent().find('.draggable').length) {
					$(this).remove();
				}
			});


			$('#importForm4 #csvimportfields .droppable').each(function(index, div) {
				var csvField = $(this).attr('id').replace('csv_','');
				var dbFiled = 0;
				if($(this).find('.draggable').length) {
					dbFiled = $(this).find('.draggable').attr('id').replace('field_','');
					if($(this).hasClass("contactPerson1")){
						$('#importForm4 input[name="field[contactperson1_'+csvField+']"]').val(dbFiled);
					} else if ($(this).hasClass("contactPerson2")) {
						$('#importForm4 input[name="field[contactperson2_'+csvField+']"]').val(dbFiled);
					} else if ($(this).hasClass("ownerFromDate")) {
						$('#importForm4 input[name="field['+csvField+']"]').val(dbFiled);
					} else if ($(this).hasClass("selfdefined")) {
						$('#importForm4 input[name="field['+csvField+']"]').val(dbFiled);
					} else {
						$('#importForm4 input[name="field['+csvField+']"]').val(dbFiled);
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
		<div id="importForm4">
			<p align="center">
				<b>Please select CSV file for import:</b> <input type="file" name="filenameImport" id="filenameImport">
			</p>

			<p align="center">
				<b>Please select CSV file separator value:</b>
				<select name="separator" id="separator">
					<option value=";">; (semi-colons)</option>
					<option value=",">, (commas)</option>
					<option value=":">: (colons)</option>
					<option value="|">| (pipes)</option>
					<option value="t">&nbsp;&nbsp;(tab)</option>
				</select>
				<br/>
				<?php echo $formText_FirstLineOfFileWillNotBeImported_output;?>
			</p>
			<div>

			<?php
			$s_buffer_drop = '';
			$s_buffer_fields = '';
			if($v_customer_accountconfig['activateKeycardsSystem'])
			{
				?><p align="center"><?php echo $formText_AddKeycardSystemAdditionalFields_Output;?> <select name="keycard_access" id="output-keycard-access"><option value=""><?php echo $formText_ChooseAccess_Output;?></option><?php
				foreach($v_amadeus_systems as $l_key => $v_amadeus)
				{
					?><option value="<?php echo $l_key;?>"><?php echo $v_amadeus['name'];?></option><?php

					$s_buffer_drop .= '<div class="keycard-access-group group-'.$l_key.'" style="display:none;">';
					$v_items = $v_amadeus['api']->get_all_access_categories();
					if(sizeof($v_items))
					foreach($v_items as $v_item)
					{
						$s_buffer_drop .= '<div class="droppable home" id="csv_keycard_access'.$l_key.'_'.$v_item['ID'].'" data-scope="field'.$l_key.'_'.$v_item['ID'].'">'.$v_item['Name'].'</div>';
						$s_buffer_fields .= '<input type="hidden" name="field[keycard_access'.$l_key.'_'.$v_item['ID'].']">';
					}
					$s_buffer_drop .= '</div>';
				}
				?></select></p><?php
			}
			?>
			</div>
			<p align="center">
				<input type="hidden" name="csvFields" value="">
			</p>

			<div class="half1">
				<div id="dbFields" class="droppable bank">
				</div>
			</div>


			<div class="half2" id="csvimportfields">
			<form method="post" action="/accounts/<?=$_GET['accountname']?>/modules/<?=$_GET['module']?>/output/includes/ajax.import_data_contactperson.php?caID=<?=$_GET['caID']?>&2" accept-charset="UTF-8">
			<div class="boxes">
			<?php
			$defaultFields = array('name', 'middlename', 'lastname', 'mobile', 'email', 'fullname_for_import_comparing');

			foreach($dbfields as $field) {
				if(in_array($field, $defaultFields) ) {


			?>
				<div id="csv_<?=$field?>" class="droppable home" data-scope="<?=$field?>">
					<?php
					switch($field) {
						case "name":
							echo $formText_FirstName_output;
						break;
						case "middlename":
							echo $formText_MiddleName_output;
						break;
						case "lastname":
							echo $formText_Lastname_output;
						break;
						case "email":
							echo $formText_Email_output;
						break;
						case "mobile":
							echo $formText_Mobile_output;
						break;
						case "fullname_for_import_comparing":
							echo $formText_FullNameForImportComparing_output;
						break;
						default:
							echo $field;
						break;
					}
					?> <sup><a class="label" href="#label_<?=$field?>" onclick="$($(this).attr('href')).parent().show(); return false;">label</a></sup>
					<div class="label">
						<input type="text" name="customlabel[<?=$field?>]" id="label_<?=$field?>" value="" placeholder="<?=$field?>" autocomplete="off">
						<a href="#label_<?=$field?>" onclick="$($(this).attr('href')).val('').parent().hide(); return false;">[x]</a>
					</div>
				</div>
			<?php }
			}
			echo $s_buffer_drop;
			?>
			</div>

			<input type="submit" name="" id="importButton" value="IMPORT" disabled="disabled">
			<input type="hidden" name="csv" value="">
			<input type="hidden" name="spliter" id="spliter" value=";">
			<input type="hidden" name="table" value="contactperson">
			<input type="hidden" name="cid" value="<?php echo $cid;?>">

			<?php foreach($dbfields as $field) { ?>
				<input type="hidden" name="field[<?=$field?>]" value="">
			<?php } ?>
			<?php echo $s_buffer_fields;?>
			</form>

			</div>

			<div style="clear: both;"></div>
		</div>
	<?php
	$s_popup = ob_get_clean();
	?>
	<div id="exampleModal4" class="modal fade" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg allowScroll" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close fw_button_color" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?php echo $formText_Import_output;?></h4>
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
