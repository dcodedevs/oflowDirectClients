<?php
//session_start();
$user = $variables->loggID?$variables->loggID:$_COOKIE['username'];

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
		$setExtrenal = array();
		$departmentId = 0;
		$name = "";
		foreach($relation as $dbField=>$csvField)
		{
			if('keycard_access' == substr($dbField, 0, 14)) continue;
			$csvField = trim($csvField);
			if(trim($row[$csvField]) != "")
			{
				if($dbField != "customerNumber"){
					$rowData = $row[$csvField];
					$rowData = str_replace(array("\n", "\t", "\r"), '', $rowData);

					$set[] = $o_main->db_escape_name($dbField)." = ".$o_main->db->escape($rowData);
					if($dbField == "fullname_for_import_comparing") {
						$idField = $dbField;
						$idValue = $row[$csvField];
					}
				} else {
					if($rowData > 0){
						$setExtrenal[] = $rowData;
					}
				}
			}
		}
		if($_POST['ownercompany'] > 0){
			if(count($set) > 2){
				$ownercompanyIdPost = $_POST['ownercompany'];
				foreach($setExtrenal as $external_sys_id){
					$o_query = $o_main->db->query("SELECT * FROM customer_externalsystem_id WHERE external_id = ? AND ownercompany_id = ?", array($external_sys_id, $ownercompanyIdPost));
					if($o_query && $o_query->num_rows()>0)
					{
						$external_item = $o_query->row_array();
						$o_query = $o_main->db->query("SELECT * FROM customer WHERE id = ?", array($external_item['customer_id']));
						$foundItem = $o_query ? $o_query->row_array() : array();
					}
				}
				if($foundItem){

					$set[] = "moduleID = 41";
					$set[] = "customerId = '".$foundItem['id']."'";
					$set[] = "type = 1";
					$insertTable = $o_main->db_escape_name($insertTable);
					$idField = $o_main->db_escape_name($idField);
					$o_query = $o_main->db->query("SELECT * FROM ".$insertTable." WHERE customerId = ? AND fullname_for_import_comparing = ?", array($idValue));
					$foundItem = $o_query->row_array();
					if($o_query){
						$foundItem = $o_query->row_array();
						if($foundItem){

						}
					}
					// if($customerId > 0)
					// {
					// 	$synced = true;
			        //     if(!$synced){
			        //         $fw_error_msg[] = $formText_ErrorUpdatingPersonOnAccount_output . " ".$v_customer_accountconfig['linked_insider_account'];
			        //     } else {
					// 		$fw_return_data = 1;
					// 	}
					// }
				}
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
	<div class="btnStyle">
		<div class="text"><a href="#import" data-toggle="modal" data-target="#exampleModalCPGroup"><?php echo $formText_ImportGroupConnection_Output;?></a></div>
	</div>
	<style>
	#importFormCPGroup {
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
		$('#importFormCPGroup #csvimportfields .draggable').remove();
		if(s == "t"){
			fields = f.split("\t");
		} else{
			fields = f.split(s);
		}
		$("#importFormCPGroup #dbFields").html('');
		$.each(fields , function(i, val) {
			if(val.trim() != ""){
				$("#importFormCPGroup #dbFields").append(' <div class="draggable" id="field_'+val+'" data-scope="'+val+'">'+val+'</div>');
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
					$('#importFormCPGroup #dbFields').append($('.ui-draggable', this));
					$(this).append(ui.draggable);
					$(this).find('div.label').hide();
				}
			}
		});
	}

	$(document).ready(function () {

		var spliter = ";";

		$('#importFormCPGroup #output-keycard-access').off('change').on('change', function(e){
			$('.keycard-access-group').hide();
			if('' != $(this).val()) $('.keycard-access-group.group-' + $(this).val()).show();
		});


		$("#importFormCPGroup #separator").change(function(e) {
			$("#importFormCPGroup #spliter").val( $(this).val() );
			spliter = $(this).val();
			generateDragable($('#importFormCPGroup input[name="csvFields"]').val(), spliter);
		});

		$("#importFormCPGroup #filenameImport").change(function(e) {
			var ext = $("#importFormCPGroup input#filenameImport").val().split(".").pop().toLowerCase();
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
							$('#importFormCPGroup input[name="csv"]').val(e.target.result);
							var csvval=e.target.result.split("\n");
							var csvvalue=csvval[0];
							$('#importFormCPGroup input[name="csvFields"]').val(csvvalue);
							generateDragable($('#importFormCPGroup input[name="csvFields"]').val(), spliter);
						};
						reader2.readAsText(file);
					} else {
						alert("<?php echo $formText_PossibleToUploadOnlyFilesInUTF8Encoding_output;?>");
					}
				};
				reader.readAsArrayBuffer(file);

			}

			$('#importFormCPGroup #importButton').prop("disabled", false);
			$('#importFormCPGroup #csvimportfields a.label').show();
			return false;

		});


		$('#importFormCPGroup #importButton').on('click', function(e){
			$('#importFormCPGroup #csvimportfields .boxes input').each(function(index, div) {
				if(!$(this).val() || $(this).parent().parent().find('.draggable').length) {
					$(this).remove();
				}
			});


			$('#importFormCPGroup #csvimportfields .droppable').each(function(index, div) {
				var csvField = $(this).attr('id').replace('csv_','');
				var dbFiled = 0;
				if($(this).find('.draggable').length) {
					dbFiled = $(this).find('.draggable').attr('id').replace('field_','');
					if($(this).hasClass("contactPerson1")){
						$('#importFormCPGroup input[name="field[contactperson1_'+csvField+']"]').val(dbFiled);
					} else if ($(this).hasClass("contactPerson2")) {
						$('#importFormCPGroup input[name="field[contactperson2_'+csvField+']"]').val(dbFiled);
					} else if ($(this).hasClass("ownerFromDate")) {
						$('#importFormCPGroup input[name="field['+csvField+']"]').val(dbFiled);
					} else if ($(this).hasClass("selfdefined")) {
						$('#importFormCPGroup input[name="field['+csvField+']"]').val(dbFiled);
					} else {
						$('#importFormCPGroup input[name="field['+csvField+']"]').val(dbFiled);
					}
				}
			});
			e.preventDefault();
			
		});

	});
	</script>
	<?php
	ob_start();
	?>
		<div id="importFormCPGroup">
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
			<p align="center">
				<input type="hidden" name="csvFields" value="">
			</p>

			<div class="half1">
				<div id="dbFields" class="droppable bank">
				</div>
			</div>


			<div class="half2" id="csvimportfields">
			<form method="post" action="/accounts/<?=$_GET['accountname']?>/modules/<?=$_GET['module']?>/output/includes/ajax.import_data_contactperson_compare.php?caID=<?=$_GET['caID']?>&2" accept-charset="UTF-8">

			<div>
				<?php
				$data = json_decode(APIconnectorUser("group_get_list", $variables->loggID, $variables->sessionID, array('company_id'=>$_GET['companyID'])),true);
				$groups = array();
				$departments = array();
				if($data['status'] == 1){
					foreach($data['items'] as $item){
						if(intval($item['department']) == 1){
							array_push($departments, $item);
						} else {
							array_push($groups, $item);
						}
					}
				}
				?>
				<div>
					<label><?php echo $formText_Group_output;?></label>
					<select name="group_id" required autocomplete="off">
						<option value=""><?php echo $formText_SelectGroup_Output;?></option>
						<?php foreach($groups as $group) { ?>
							<option value="<?php echo $group['id'];?>"><?php echo $group['name'];?></option>
						<?php } ?>
					</select>
				</div>
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
				</div>
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
			<div class="boxes">
			<?php
			$defaultFields = array('fullname_for_import_comparing');

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
	<div id="exampleModalCPGroup" class="modal fade" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg allowScroll" role="document">
    <div class="modal-content">
      <div class="modal-header">
	  	<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
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
