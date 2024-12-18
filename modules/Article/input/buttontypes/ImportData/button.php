<?php
session_start();
set_time_limit(600);
@include('../../../../../dbConnect.php');
if(!function_exists("sys_get_table_fields")) include(__DIR__."/../../includes/fn_sys_get_table_fields.php");
$user = $variables->loggID ? $variables->loggID : $_COOKIE['username'];

if(sizeof($_POST) && isset($_POST['import_data'])) {
	$insertTable = $_POST['table']; //'overview';
	//print_r($_POST); die();
	$spliter = $_POST['spliter'];
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
			$customLabels[] = "{$dbField} = '{$label}'";
		}
	}
	
//print_r($relation); 
	
	if( $_POST['uniq'] && in_array($_POST['uniq'], array_keys($relation) ) ) {
		//print 'applying uniq...';
		$uniq = $_POST['uniq'];
	} else {
		//print 'uniq field not applyed';
		$uniq = false;
	}


	foreach($csv as $row) {
		if(sizeof($customLabels)) {
			$set = $customLabels;
		}
		
		$set[] = "created = NOW()";
		$set[] = "createdby = 'imported - {$user}'";

		foreach($relation as $dbField=>$csvField) {
			if ( strpos($csvField, '|') !== false ) {
				$groupFields = array_filter(explode('|',$csvField));
				
				foreach ($groupFields as $toGroup) {
					$groupped[] = $row[$toGroup];
				}
				$groupped = implode( $_POST['separators'][$dbField], $groupped );
				$val = mysql_real_escape_string($groupped);				
				$set[] = "{$dbField} = '{$val}'";
				unset($groupped);
			} else {
				$val = mysql_real_escape_string($row[$csvField]);
				$set[] = "{$dbField} = '{$val}'";
			}

			if($uniq && $dbField == $uniq) {
				$searchUniq = array_shift(mysql_fetch_assoc(mysql_query("SELECT count($uniq) as found FROM $insertTable WHERE $uniq = '{$row[$csvField]}' LIMIT 1 ")));
				if( $searchUniq > 0 ) { continue 2; }
			}
		}

		$SQLINSERT  = " INSERT INTO {$insertTable} SET ".implode(', ', $set)." ;"; // ON DUPLICATE KEY UPDATE tag=tag;
		//print($SQLINSERT).'<br>'; 
		mysql_query($SQLINSERT) or die(mysql_error());
		unset($set);
	}
	//die();
	if($_SERVER['HTTP_REFERER']) {
		header('Location: ' . $_SERVER['HTTP_REFERER']);
	} else {
		die('UNKNOWN HTTP_REFERER');
	}
}
	
$dbfields = sys_get_table_fields($buttonsArray[6], $buttonsArray[3], $choosenListInputLang);

if(@$_SESSION['reload']) {
	unset($_SESSION['reload']);
	print "<script>document.location.reload(true)</script>";
}

@include($extradir."/input/settings/relations/$buttonbase.php");
$relationField = "";
if(sizeof(@$prerelations))
{
	foreach($prerelations as $prep)
	{
		$spPrep = explode("¤",$prep);
		if($spPrep[2] == $buttonRelationModule)
		{
			$relationField = $spPrep[3];
			$relationModule = $spPrep[2];
		}
	}
} 
$ui_id_counter++;
$button_ui_id = $buttonSubmodule."_".$ui_editform_id."_".$ui_id_counter;
$importLink = "#importLink";
?>
<a href="<?=$importLink?>" id="<?=$button_ui_id;?>" class="script" role="menuitem" onclick="$.fancybox({type: 'inline', href: '#importForm'}); return false;"><?=$buttonsArray[1];?></a>
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
	width: 35%;
	float: left;
}
.half2 {
	width: 65%;
	float: left;
}
#csvimportfields {
	*display: none;
}

#dbFields {
	min-height: 100px;
	width: 200px;
}
#dbFields div {
	float: none;
	width: inherit;
	text-indent: 5px;
}
.draggable { cursor: move; }
.droppable div { border: 1px dotted black; margin: 2px; background: #EEE; }
.droppable, .draggable { height: 20px; line-height: 20px; text-indent: 5px; }
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
	display: table;
}
#csvimportfields .draggable {
	background: #DDD;
	float: right;
	width: 200px;
	clear: right;
}
</style>


<script>
function generateDragable(f,s) {
	$('#csvimportfields .draggable').remove();
	
	fields = f.split(s);
	$("#dbFields").html('');
	$.each(fields , function(i, val) { 
		$("#dbFields").append(' <div class="draggable" id="field_'+val+'" data-scope="'+val+'">'+val+'</div>');
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
				//alert('dropped');
				//$('#dbFields').append($('.ui-draggable', this)); //SINGLE ELEMENT ALLOWED
				
				$(this).append(ui.draggable);
				$(this).find('div.label').hide();
				
			}            
		}
	});
});

}

$(document).ready(function () {
	
	var spliter = ",";

	$("#separator").change(function(e) {
		$("#spliter").val( $(this).val() );
		spliter = $(this).val();
		generateDragable($('input[name="csvFields"]').val(), spliter);
	});
	
	$("#filename").change(function(e) {
		var ext = $("input#filename").val().split(".").pop().toLowerCase();

		if($.inArray(ext, ["csv"]) == -1) {
			alert('Please upload CSV file!');
			return false;
		}
			
		if (e.target.files != undefined) {
			var reader = new FileReader();
			reader.onload = function(e) {
				$('input[name="csv"]').val(e.target.result);
				var csvval=e.target.result.split("\n");
				var csvvalue=csvval[0].split(spliter);
				$('input[name="csvFields"]').val(csvvalue);
				generateDragable($('input[name="csvFields"]').val(), spliter);
			};
			reader.readAsText(e.target.files.item(0));

		}
		
		$('#importButton').prop("disabled", false);
		$('#csvimportfields a.label').show();
		return false;

	});


	$('#importButton').on('click', function(){
		$('#csvimportfields .boxes input').each(function(index, div) {
			if(!$(this).val() || $(this).parent().parent().find('.draggable').length) {
				$(this).remove();
			}
		});
		
		$('select.separators').each(function(index, div) {
			if ( $(this).parent().parent().find('.draggable').length < 2 ) { //|| !$(this).val()
				$(this).remove();
			}

		});
		
		$('#csvimportfields .droppable').each(function(index, div) {
			var csvField = $(this).attr('id').replace('csv_','');
			var dbFiled = '';
			if( $(this).find('.draggable').length ) {
				if( $(this).find('.draggable').length > 1 ) {
					dbFiledArr = new Array();
					$(this).find('.draggable').each(function() {
						dbFiledArr.push( $(this).attr('id').replace('field_','') );
					});
					
					dbFiled = dbFiledArr.join('|');
					//alert(dbFiled);
				} else {
					dbFiled = $(this).find('.draggable').attr('id').replace('field_','');
				}
				$('input[name="field['+csvField+']"]').val(dbFiled);
			} 
		});
		//alert('importing...');	return false;
	});
	
});
</script>

<div style="display:none;" class="boxed">
	<div id="importForm">
<?
//print_r($variables);
?>


		<p align="center">
			<b>Please select CSV file for import:</b> <input type="file" name="filename" id="filename">	
		</p>
		
		<p align="center">
			<b>Please select CSV file separator value:</b>
			<select name="separator" id="separator">
				<option value=",">, (commas)</option>
				<option value=";">; (semi-colons)</option>
				<option value=":">: (colons)</option>
				<option value="|">| (pipes)</option>
			</select>
		</p>
		<p align="center">
			<input type="hidden" name="csvFields" value="">
		</p>

		<div class="half1">
			<div id="dbFields" class="droppable bank">
			</div>
		</div>
		
		<div class="half2" id="csvimportfields">
<form method="post" action="/accounts/<?=$_GET['accountname']?>/modules/<?=$_GET['module']?>/input/buttontypes/ImportData/button.php" accept-charset="UTF-8">
<input type="hidden" name="import_data" value="1" />
<div class="boxes">

<? 
$defaultFields = array('id','moduleID','createdBy','created','updatedBy','updated','origId','sortnr','seotitle','seodescription','seourl');
$defaultFields = array('id','createdBy','created','updatedBy','updated','origId','sortnr','seotitle','seodescription','seourl');

foreach($dbfields as $field) { 
		if( !in_array($field[0], $defaultFields) ) {
		
			$options .= '<option value="'.$field[0].'">'.$field[1].'</option>';
?>
	<div id="csv_<?=$field[0]?>" class="droppable home" data-scope="<?=$field[0]?>">
		<div style="float: left; background: none; border: none;">
			<?=$field[1]?> <sup><a class="label" href="#label_<?=$field[0]?>" onclick="$($(this).attr('href')).parent().show(); return false;">label</a></sup>
			<select name="separators[<?=$field[0]?>]" style="font-size: 9px; line-height: 11px;" class="separators">
				<option value=" ">" " - space</option>
				<option value=",">"," - coma</option>
				<option value="¤">"¤" - star</option>
			</select>
		</div>
		<div class="label">
			<input type="text" name="customlabel[<?=$field[0]?>]" id="label_<?=$field[0]?>" value="<?=$field[0]=='moduleID'?$_GET['moduleID']:''?>" placeholder="<?=$field[0]?>">
			<a href="#label_<?=$field[0]?>" onclick="$($(this).attr('href')).val('').parent().hide(); return false;">[x]</a>
		</div>
	</div>
<? } } ?>
</div>
<? 
$prebuttonconfigArray = (explode(':',$prebuttonconfig)); 
$moduleTable = $prebuttonconfigArray[3]; 
?>

	<input type="hidden" name="csv" value="">
	<input type="hidden" name="spliter" id="spliter" value=",">
	<input type="hidden" name="table" value="<?=$moduleTable?>">
<? foreach($dbfields as $field) { ?>
	<input type="hidden" name="field[<?=$field[0]?>]" value="">
<? } ?>

<p>
<label> 
<select name="uniq">
	<option value="">- none -</option>
	<?=$options?>
</select> - Choose field with no duplicates
</label>
</p>
<p> <input type="submit" name="" id="importButton" value="IMPORT" disabled="disabled"> </p> 

</form>

		</div>
		
		<div style="clear: both;"></div>
	</div>
</div>
