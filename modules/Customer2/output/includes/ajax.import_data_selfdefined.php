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


$s_sql = "SELECT * FROM customer_accountconfig ORDER BY id";
$o_query = $o_main->db->query($s_sql);
$v_customer_accountconfig = $o_query ? $o_query->row_array(): array();

$module="Customer2";
$v_module_access = json_decode($fw_session['cache_menu'],true);
$l_access = $v_module_access[$module][2];

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

	$comparing_select = $_POST['comparing_select'];
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
	$customCheckbox = array();
	foreach($_POST['customCheckbox'] as $customCheckboxItem=>$valu1) {
		$customCheckbox[] = $customCheckboxItem;
	}
	foreach($csv as $row) {
		if(sizeof($customLabels)) {
			$set = $customLabels;
		}
		$set[] = "created = NOW()";
		$set[] = "createdby = ".$o_main->db->escape("imported - ".$user);
		$set[] = "customerType = 0";

		$set2[] = "created = NOW()";
		$set2[] = "createdby = ".$o_main->db->escape("imported - ".$user);

		$setSelfdefined = array();
		$prospectValue = "";
		$prospectInfo = "";
		$contactpersonEmail = "";
		$name = "";
		foreach($relation as $dbField=>$csvField) {
			if(trim($row[$csvField]) != ""){
				if(strpos($dbField, "selfdefined") === false) {
					if(strpos($dbField, "contactperson") === false) {

					} else {
						$rowData = $row[$csvField];
						$realDbFieldArray = explode("_", $dbField, 2);
						$dbField = $realDbFieldArray[1];
						if($dbField == "email") {
							$contactpersonEmail = $rowData;
						}
						$set2[] = $o_main->db_escape_name($dbField)." = ".$o_main->db->escape($rowData);
					}
				} else {
					$realDbFieldArray = explode("_", $dbField, 2);

					$rowData = $row[$csvField];
					$rowData = str_replace(array("\n", "\t", "\r"), '', $rowData);

					$setSelfdefined[] = array($realDbFieldArray[1]=>$rowData);
				}
				if($dbField == "comparing_field") {
					$idField = $dbField;
					$idValue = preg_replace("/[^0-9]/", "", $row[$csvField]);
				}


			}
		}

		if($idValue > 0 ) {
			$insertTable = $o_main->db_escape_name($insertTable);
			$idField = $o_main->db_escape_name($idField);
			if($comparing_select == 0) {
				$o_query = $o_main->db->query("SELECT customer.* FROM customer WHERE id = ?", array($idValue));
				$foundItem = $o_query ? $o_query->row_array() : array();
			} else if($comparing_select == 1){
				$ownercompanyIdPost = $_POST['ownercompany'];
				$o_query = $o_main->db->query("SELECT customer.* FROM customer JOIN customer_externalsystem_id ON customer_externalsystem_id.customer_id
					WHERE customer_externalsystem_id.external_id= ? AND customer_externalsystem_id.ownercompany_id = ?", array($idValue, $ownercompanyIdPost));
				$foundItem = $o_query ? $o_query->row_array() : array();
			} else if($comparing_select == 2){
				$o_query = $o_main->db->query("SELECT customer.* FROM customer WHERE publicRegisterId= ?", array($idValue));
				$foundItem = $o_query ? $o_query->row_array() : array();
			}
			$customerId = $foundItem['id'];
			if($customerId > 0) {

				if($_POST['import_type'] == 0){
					foreach($setSelfdefined as $selfdefinedRow){
						foreach($selfdefinedRow as $selfdefinedId => $sefdefinedValue){
							if(in_array("selfdefined_".$selfdefinedId, $customCheckbox)){
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
				} else if($_POST['import_type'] == 1){
					if(count($set2) > 2){
						$set2[] = "customerId = ".$customerId;
						$set2[] = "type = 1";
						$contactpersonPrivate = array();
						if($contactpersonEmail != ""){
							$s_sql = "SELECT * FROM contactperson WHERE customerId = ? AND email = ?";
							$o_query = $o_main->db->query($s_sql, array($customerId, $contactpersonEmail));
							$contactpersonPrivate = $o_query ? $o_query->row_array() : array();
						}
						if(!$contactpersonPrivate) {
							$s_sql  = "INSERT INTO contactperson SET ".implode(", ", $set2).";";
							if(!$o_main->db->query($s_sql)) die($o_main->db->error());
						} else {
							$s_sql  = "UPDATE contactperson SET ".implode(", ", $set2)." WHERE id = ?";
							if(!$o_main->db->query($s_sql, array($contactpersonPrivate['id']))) die($o_main->db->error());
						}
					}
				}
			}
		}
		unset($setSelfdefined);
		unset($set2);
	}
	if($_SERVER['HTTP_REFERER']) {
		header("Location: " . $_SERVER['HTTP_REFERER']);
	} else {
		die('UNKNOWN HTTP_REFERER');
	}
}

if($developeraccess >= 5 )
{
	?>
	<div class="btnStyle">
		<div class="text"><a href="#import" data-toggle="modal" data-target="#exampleModal3"><?php echo $formText_ImportSelfdefined_Output;?></a></div>
	</div>
	<style>
	#importForm3 {
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
	#csvimportfields3 {
		*display: none;
	}

	#dbFields3 {
		min-height: 100px;
		width: 200px;
	}
	#dbFields3:after{
		display:block;
		content:"";
		clear:both;
	}
	#dbFields3 div {
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
	#csvimportfields3 .droppable {
		width: 90%;
		clear: both;
		border: 1px dotted black; margin: 2px 0px;
		min-height: 26px;
		position: relative;

	}
	#csvimportfields3 .draggable {
		background: #DDD;
		float: right;
		width: 200px;
	}
	.contactpersons {
		display: none;
	}
	</style>


	<script type="text/javascript" src="../modules/<?php echo $module?>/output/elementsOutput/encoding.min.js"></script>
	<script>
	$(".fancybox-wrap").unbind('mousewheel.fb');
	function check_customer_occurance(){
		var data = {
			field_name: $("#csv_comparing_field").find(".draggable").data("scope"),
			csv: $('#importForm3 input[name="csv"]').val(),
			ownercompany: $('#importForm3 select[name="ownercompany"]').val(),
			comparing_select: $('#importForm3 select[name="comparing_select"]').val(),
			spliter: $("#importForm3 #spliter").val()
		};
		ajaxCall('check_customer_occurance', data, function(json) {
			$(".comparing_text").html("<?php echo $formText_CustomersMatched_output?>: "+json.data[0]+"</br>"+"<?php echo $formText_CustomersNotMatched_output?>: "+json.data[1]);
		});
	}
	function generateDragable3(f,s) {
		$('#importForm3 #csvimportfields3 .draggable').remove();
		if(s == "t"){
			fields = f.split("\t");
		} else{
			fields = f.split(s);
		}
		$("#importForm3 #dbFields3").html('');
		$.each(fields , function(i, val) {
			if(val.trim() != ""){
				$("#importForm3 #dbFields3").append(' <div class="draggable" id="field_'+val+'" data-scope="'+val+'">'+val+'</div>');
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

					if(!$(this).attr('id')==='dbFields3'){
						if(!x.length){
							$(this).append(ui.draggable);
						}else{

						}
					}else{
						$('#importForm3 #dbFields3').append($('.ui-draggable', this));

						$(this).append(ui.draggable);
						$(this).find('div.label').hide();

						var idName = $(this).prop("id");

						if(idName = "csv_comparing_field") {
							check_customer_occurance();
						}
					}

				}
			});
		});

	}

	$(document).ready(function () {
		$('#importForm3 select[name="comparing_select"]').change(function(){
			check_customer_occurance();
		})
		$(".import_type").change(function(){
			if($(this).val() == 0){
				$(".selfdefined_fields").show();
				$(".contactpersons").hide();
			} else if($(this).val() == 1){
				$(".selfdefined_fields").hide();
				$(".contactpersons").show();
			}
		}).change();

		var spliter = ",";


		$("#importForm3 #separator").change(function(e) {
			$("#importForm3 #spliter").val( $(this).val() );
			spliter = $(this).val();
			generateDragable3($('#importForm3 input[name="csvFields"]').val(), spliter);
		});

		$("#importForm3 #filenameImport3").change(function(e) {
			var ext = $("#importForm3 input#filenameImport3").val().split(".").pop().toLowerCase();
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
							$('#importForm3 input[name="csv"]').val(e.target.result);
							var csvval=e.target.result.split("\n");
							var csvvalue=csvval[0];
							$('#importForm3 input[name="csvFields"]').val(csvvalue);
							generateDragable3($('#importForm3 input[name="csvFields"]').val(), spliter);
						};
						reader2.readAsText(file);
					} else {
						alert("<?php echo $formText_PossibleToUploadOnlyFilesInUTF8Encoding_output;?>");
					}
				};
				reader.readAsArrayBuffer(file);

			}
			$('#importForm3 #importButton3').prop("disabled", false);
			$('#importForm3 #csvimportfields3 a.label').show();
			return false;

		});


		$('#importForm3').submit(function(e){

			$('#importForm3 #csvimportfields3 .boxes input.fieldsToCheck').each(function(index, div) {
				if(!$(this).val() || $(this).parent().parent().find('.draggable').length) {
					$(this).remove();
				}
			});

			$('#importForm3 #csvimportfields3 .droppable').each(function(index, div) {
				var csvField = $(this).attr('id').replace('csv_','');
				var dbFiled = 0;
				if($(this).find('.draggable').length) {
					dbFiled = $(this).find('.draggable').attr('id').replace('field_','');
					if($(this).hasClass("contactPerson1")){
						$('#importForm3 input[name="field[contactperson1_'+csvField+']"]').val(dbFiled);
					} else if ($(this).hasClass("contactPerson2")) {
						$('#importForm3 input[name="field[contactperson2_'+csvField+']"]').val(dbFiled);
					} else if ($(this).hasClass("ownerFromDate")) {
						$('#importForm3 input[name="field['+csvField+']"]').val(dbFiled);
					} else if ($(this).hasClass("selfdefined")) {
						$('#importForm3 input[name="field['+csvField+']"]').val(dbFiled);
					} else if ($(this).hasClass("comparing_field")) {
						$('#importForm3 input[name="field[comparing_field]"]').val(dbFiled);
					} else {
						$('#importForm3 input[name="field['+csvField+']"]').val(dbFiled);
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
		<div id="importForm3">
			<form method="post" action="/accounts/<?=$_GET['accountname']?>/modules/<?=$_GET['module']?>/output/includes/ajax.import_data_selfdefined.php?caID=<?=$_GET['caID']?>" accept-charset="UTF-8">
				<p align="right">
					<select name="import_type" class="import_type" autocomplete="off" required>
						<option value="0"><?php echo $formText_ImportSelfdefinedFields_output?></option>
						<option value="1"><?php echo $formText_ImportContactPersons_output?></option>
					</select>
				</p>
				<p align="center">
					<b><?php echo $formText_PleaseSelectCsvFileForImport_output;?>:</b> <input type="file" name="filenameImport" id="filenameImport3">
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
					<div id="dbFields3" class="droppable bank">
					</div>
				</div>


				<div class="half2" id="csvimportfields3">
					<input type="hidden" name="developeraccess" value="<?php echo $variables->developeraccess;?>" autocomplete="off">
					<div>
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
						<div>
							<label><?php echo $formText_SelectComparingField_output;?></label>
							<select name="comparing_select" required autocomplete="off">
								<option value="0"><?php echo $formText_CustomerId_Output;?></option>
								<option value="1"><?php echo $formText_ExternalCustomerId_Output;?></option>
								<option value="2"><?php echo $formText_PublicRegisterId_Output;?></option>
							</select>
						</div>

						<?php
						$field = "comparing_field";
						?>
						<div id="csv_<?=$field?>" class="droppable comparing_field home" data-scope="<?=$field?>">
							<?=$formText_ComparingField_output;?> <sup><a class="label" href="#label_<?=$field?>" onclick="$($(this).attr('href')).parent().show(); return false;">label</a></sup>
							<div class="label">
								<input type="text" class="fieldsToCheck" name="customlabel[<?=$field?>]" id="label_<?=$field?>" value="" placeholder="<?=$field?>" autocomplete="off">
								<a href="#label_<?=$field?>" onclick="$($(this).attr('href')).val('').parent().hide(); return false;">[x]</a>
							</div>
						</div>
						<div class="comparing_text"></div>
					</div>
					<br/>
					<div class="boxes">
						<div class="selfdefined_fields">
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
									<input type="checkbox" name="customCheckbox[<?=$field?>]" value=""/>
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
						<div class="contactpersons">
							<div class="contactPersonTitle"><?php echo $formText_ContactPerson_output;?></div>
							<?php
							$defaultFields2 = array('name', 'middlename', 'lastname', 'mobile', 'email', 'fullname_for_import_comparing');
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

					<input type="submit" name="" id="importButton3" value="IMPORT" disabled="disabled">
					<input type="hidden" name="csv" value="">
					<input type="hidden" name="spliter" id="spliter" value=",">
					<input type="hidden" name="table" value="customer">

					<?php foreach($selfdefinedFields as $selfdefinedField) {
						$field = "selfdefined_".$selfdefinedField['id']; ?>
						<input type="hidden" name="field[<?=$field?>]" value="">
					<?php } ?>
					<?php foreach($dbfields2 as $field) {
						$field = "contactperson_".$field; ?>
						<input type="hidden" name="field[<?=$field?>]" value="">
					<?php } ?>
					<input type="hidden" name="field[comparing_field]" value="">

					</div>
				</div>
				<div style="clear: both;"></div>
			</form>
		</div>
	<?php
	$s_popup = ob_get_clean();
	?>
	<div id="exampleModal3" class="modal fade" tabindex="-1" role="dialog">
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
