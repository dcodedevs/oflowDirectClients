<?php
ini_set('opcache.revalidate_freq', 0);
$v_index_types = array("key" => $formText_Key_input, "unique" => $formText_Unique_input, "fulltext" => $formText_Fulltext_input);//, "spatial" => $formText_Spatial_input, "primary" => $formText_Primary_input);
if(!function_exists("ftp_file_put_content")) require_once(__DIR__."/ftp_commands.php");
if(isset($_POST['save_index']))
{
	define('BASEPATH', realpath(__DIR__.'/../../../../').DIRECTORY_SEPARATOR);
	require_once(BASEPATH.'elementsGlobal/cMain.php');
	if(!function_exists("log_action")) include(__DIR__."/fn_log_action.php");
	log_action("save_settings");
	$s_file = __DIR__."/../settings/indexes/".$_GET['submodule'].".php";
	$s_ftp_file = "modules/".$_GET['module']."/input/settings/indexes/".$_GET['submodule'].".php";
	
	if(!is_file($s_file)) ftp_file_put_content($s_ftp_file,'<?php'.PHP_EOL.'$sys_table_indexes = array();'.PHP_EOL.'?>');
	include($s_file);
	
	$_POST['index_name'] = preg_replace('#[^A-za-z0-9_]+#', '', str_replace(" ", "_", $_POST['index_name']));
	$v_fields = array();
	foreach($_POST['index_field'] as $s_field)
	{
		if(trim($s_field)=="") continue;
		list($s_index_table,$s_field) = explode(":", $s_field);
		$v_fields[$s_index_table][] = $s_field;
	}
	if(count($v_fields)==1)
	{
		$s_new_index = $_POST['index_name'].":".$_POST['index_type'].":".$s_index_table.":".implode(",", $v_fields[$s_index_table]);
		
		if($_GET['indexedit'] > -1)
		{
			$v_old_index = explode(":",$sys_table_indexes[$_GET['indexedit']]);
			if($v_old_index[0] != $_POST['index_name'])
			{
				$s_sql = "ALTER TABLE ".$s_index_table." DROP INDEX ".$v_old_index[0];
				if(!$o_main->db->query($s_sql))
				{
					$s_new_index = $v_old_index[0].":".$_POST['index_type'].":".$s_index_table.":".implode(",", $v_fields[$s_index_table]);
					$error_msg["error_0"] = "Index was not renamed.";
				}
			}
			$sys_table_indexes[$_GET['indexedit']] = $s_new_index;
		} else {
			$sys_table_indexes[] = $s_new_index;
		}
		
		$s_new_file = "";
		foreach($sys_table_indexes as $l_key => $s_item)
		{
			if($s_new_file!="") $s_new_file .= ", ";
			$s_new_file .= "'$s_item'";
		}
		
		ftp_file_put_content($s_ftp_file,'<?php'.PHP_EOL.'$sys_table_indexes = array('.$s_new_file.');'.PHP_EOL.'?>');
	} else {
		if(count($v_fields)>1) $error_msg["error_0"] = "Index cannot contain fields from both tables (single and multi language)!";
		if(count($v_fields)==0) $error_msg["error_0"] = "Index should have at least one field!";
	}
	if(isset($error_msg) && count($error_msg)>0)
	{
		$v_param = array('companyaccessID' => $_GET['caID'], 'session' => $_COOKIE['sessionID'], 'username' => $_COOKIE['username']);
		$o_main->db->update('session_framework', array('error_msg' => json_encode($error_msg)), $v_param);
	}
	
	header("Location: ".substr($_SERVER['HTTP_REFERER'],0,strpos($_SERVER['HTTP_REFERER'],"?"))."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$_GET['module']."&submodule=".$_GET['submodule']."&includefile=editIndexes");
	exit;
}

if(isset($_POST['delete_index']))
{
	define('BASEPATH', realpath(__DIR__.'/../../../../').DIRECTORY_SEPARATOR);
	require_once(BASEPATH.'elementsGlobal/cMain.php');
	if(!function_exists("log_action")) include(__DIR__."/fn_log_action.php");
	log_action("save_settings");
	$s_file = __DIR__."/../settings/indexes/".$_GET['submodule'].".php";
	$s_ftp_file = "modules/".$_GET['module']."/input/settings/indexes/".$_GET['submodule'].".php";
	include($s_file);
	$v_index = array_splice($sys_table_indexes, $_POST['delete_index'], 1);
	$s_new_file = "";
	foreach($sys_table_indexes as $l_key => $s_item)
	{
		if($s_new_file!="") $s_new_file .= ", ";
		$s_new_file .= "'$s_item'";
	}
	
	if($_POST['delete_type']==1)
	{
		$v_index = explode(":", $v_index[0]);
		$s_sql = "ALTER TABLE ".$v_index[2]." DROP INDEX ".$v_index[0];
		if(!$o_main->db->query($s_sql))
		{
			$error_msg["error_0"] = "[DB] Index ".$v_index[0]." is not removed from database: ".json_encode($o_main->db->error());
			$v_param = array('companyaccessID' => $_GET['caID'], 'session' => $_COOKIE['sessionID'], 'username' => $_COOKIE['username']);
			$o_main->db->update('session_framework', array('error_msg' => json_encode($error_msg)), $v_param);
		} else {
			ftp_file_put_content($s_ftp_file,'<?php'.PHP_EOL.'$sys_table_indexes = array('.$s_new_file.');'.PHP_EOL.'?>');
		}
	} else {
		ftp_file_put_content($s_ftp_file,'<?php'.PHP_EOL.'$sys_table_indexes = array('.$s_new_file.');'.PHP_EOL.'?>');
	}
	
	header("Location: ".substr($_SERVER['HTTP_REFERER'],0,strpos($_SERVER['HTTP_REFERER'],"?"))."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$_GET['module']."&submodule=".$_GET['submodule']."&includefile=editIndexes");
	exit;
}

$l_rand_id = rand(10000,999999);
$s_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&submodule=".$submodule."&includefile=editIndexes";
?><h3><?php echo $formText_EditIndexesForTable_input.' '.$submodule;?></h3><?php
if(!isset($_GET['indexedit']))
{
	$s_file = __DIR__."/../settings/indexes/".$submodule.".php";
	include($s_file);
	?>
	<div class="module-manager">
	<a class="btn btn-success btn-sm optimize" href="<?php echo $s_url."&indexedit=-1";?>"><?php echo $formText_NewIndex_input;?></a><br /><br />
	<table class="table table-striped table-hover table-condensed">
	<thead>
		<tr>
			<th><?php echo $formText_Name_input;?></th>
			<th><?php echo $formText_Type_input;?></th>
			<th><?php echo $formText_Table_input;?></th>
			<th><?php echo $formText_FieldCount_input;?></th>
			<th></th>
		</tr>
	</thead>
	<tbody><?php
	foreach($sys_table_indexes as $l_key => $s_row)
	{
		$v_row = explode(":",$s_row);
		?><tr>
			<td><?php echo $v_row[0];?></td>
			<td><?php echo $v_index_types[$v_row[1]];?></td>
			<td><?php echo $v_row[2];?></td>
			<td><?php echo count(explode(",",$v_row[3]));?></td>
			<td align="right">
				<form action="<?php echo $extradir."/input/includes/editIndexes.php?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&submodule=".$submodule;?>" method="post">
				<input type="hidden" name="delete_index" value="<?php echo $l_key;?>">
				<input type="hidden" name="delete_type" value="0">
				<a class="btn btn-default btn-xs optimize" href="<?php echo $s_url."&indexedit=".$l_key."&_=".$l_rand_id;?>"><span class="glyphicon glyphicon-edit"></span> <?php echo $formText_Edit_input;?></a>
				<button type="button" class="btn btn-warning btn-xs delete_index" data-name="<?php echo $formText_DeleteIndexDefinition_input.": ".$v_row[0];?>?" data-type="0" title="<?php echo $formText_Delete_input;?>"><span class="glyphicon glyphicon-remove"></span> <?php echo $formText_Delete_input;?></button>
				<button type="button" class="btn btn-danger btn-xs delete_index" data-name="<?php echo $formText_DeleteIndexDefinitionAndAlsoRemoveFromDatabase_input.": ".$v_row[0];?>" data-type="1" title="<?php echo $formText_DeleteAlsoFromDatabase_input;?>"><span class="glyphicon glyphicon-trash"></span> <?php echo $formText_Drop_input;?></button>
				</form>
			</td>
		</tr><?php
	}
	?></tbody>
	</table>
	<br/>
	<table class="table table-striped table-condensed">
	<thead>
		<tr>
			<th colspan="4"><?php echo $formText_IndexesInDatabase_input;?></th>
		</tr>
		<tr>
			<th><?php echo $formText_Table_input;?></th>
			<th><?php echo $formText_Name_input;?></th>
			<th><?php echo $formText_Unique_input;?></th>
			<th><?php echo $formText_Fields_input;?></th>
		</tr>
	</thead>
	<tbody><?php
	$v_db_indexes = array();
	$v_db_indexes[$submodule] = get_table_indexes_from_db($submodule);
	$v_db_indexes[$submodule."content"] = get_table_indexes_from_db($submodule."content");
	foreach($v_db_indexes as $s_table => $v_indexes)
	{
		foreach($v_indexes as $s_index => $v_index)
		{
			$s_fields = "";
			foreach($v_index["fields"] as $l_x => $s_field)
			{
				if($s_fields != "") $s_fields .= ", ";
				$s_fields .= $s_field.($v_index["lenght"][$l_x]>0 ? "(".$v_index["lenght"][$l_x].")" : "");
			}
			?><tr>
				<td><?php echo $s_table;?></td>
				<td><?php echo $s_index;?></td>
				<td><?php echo ($v_index["uniq"] ? $formText_Yes_input : $formText_No_input);?></td>
				<td><?php echo $s_fields;?></td>
			</tr><?php
			$s_table = "";
		}
	}
	?></tbody>
	</table>
	<a class="btn btn-default optimize" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$_GET['module']."&submodule=".$_GET['submodule']."&includefile=chooseToUpdate";?>"><?php echo $formText_Cancel_input;?></a>
	</div>
	<script type="text/javascript">
	<?php if(isset($ob_javascript)) { ob_start(); } ?>
	$(function() {
		$("button.delete_index").unbind("click").on("click",function(e){
			e.preventDefault();
			if(!fw_changes_made && !fw_click_instance)
			{
				fw_click_instance = true;
				var $_this = $(this);
				$(this).closest("form").find("input[name=delete_type]").val($(this).data("type"));
				bootbox.confirm({
					message:$_this.attr("data-name"),
					buttons:{confirm:{label:"<?php echo $formText_Yes_input;?>"},cancel:{label:"<?php echo $formText_No_input;?>"}},
					callback: function(result){
						if(result)
						{
							$_this.closest("form").submit();
						}
						fw_click_instance = false;
					}
				});
			}
		});
	});
	<?php if(isset($ob_javascript)) { $ob_javascript .= " ".ob_get_clean(); } ?>
	</script><?php 

} else {
	
	$v_index = array();
	if($_GET['indexedit'] != '-1')
	{
		include(__DIR__."/../settings/indexes/$submodule.php");
		$v_index = explode(":",$sys_table_indexes[$_GET['indexedit']]);
	}
	include(__DIR__."/../settings/fields/".$submodule."fields.php");
	$v_table_fields = array();
	foreach($prefields as $v_item)
	{
		$v_field = explode("¤",$v_item);
		$v_table_fields[$v_field[3]][$v_field[0]] = $v_field[2]." (".$v_field[0].")";
	}
	?>
	<form class="form-horizontal" action="<?php echo $extradir."/input/includes/editIndexes.php?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&submodule=".$submodule."&indexedit=".$_GET['indexedit'];?>" method="post">
	<input type="hidden" name="save_index" value="1">
	<div class="form-group">
		<label for="index_name" class="col-sm-3 control-label"><?php echo $formText_Name_input;?></label>
		<div class="col-sm-9">
			<input type="text" class="form-control" id="index_name" name="index_name" value="<?php echo $v_index[0];?>">
		</div>
	</div>
	<div class="form-group">
		<label for="index_type" class="col-sm-3 control-label"><?php echo $formText_Type_input;?></label>
		<div class="col-sm-9">
			<select class="form-control" id="index_type" name="index_type"><?php
			foreach($v_index_types as $s_key => $s_value)
			{
				?><option value="<?php echo $s_key;?>"<?php echo ($v_index[1]==$s_key ? ' selected':'');?>><?php echo $s_value;?></option><?php
			}
			?></select>
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-3 control-label"><?php echo $formText_Fields_input;?></label>
		<div class="col-sm-9">
			<div style="margin-bottom:5px;">
				<button id="index_field_add" type="button" class="btn btn-default btn-sm">
					<span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
				</button>
			</div>
			<div id="indexes_sortable">
			<?php
			$v_index_fields = explode(",",$v_index[3]);
			foreach($v_index_fields as $s_index_field)
			{
				?><table width="100%" border="0" cellpadding="0" cellspacing="0"><tr>
					<td width="80%">
						<select class="form-control" name="index_field[]">
							<?php
							foreach($v_table_fields as $s_table => $v_fields)
							{
								?><option value="" disabled><?php echo $s_table;?></option><?php
								foreach($v_fields as $s_field => $s_field_text)
								{
									?><option value="<?php echo $s_table.":".$s_field;?>"<?php echo (($v_index[2]==$s_table && $s_index_field==$s_field) ? ' selected':'');?> style="padding-left:20px;"><?php echo $s_field_text;?></option><?php
								}
							}
							?>
						</select>
					</td>
					<td align="right">
						<button type="button" class="btn btn-danger btn-sm" onClick="$(this).closest('table').remove();"><span class="glyphicon glyphicon-trash"></span></button>
						<button type="button" class="btn btn-default btn-sm" disabled><span class="glyphicon glyphicon-sort"></span></button>
					</td>
				</tr></table><?php
			}
			?>
			</div>
		</div>
	</div>
	<div class="form-group">
		<div class="col-sm-offset-3 col-sm-9">
			<button type="submit" class="btn btn-success"><?php echo $formText_save_input;?></button>
			<a class="btn btn-default optimize" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$_GET['module']."&submodule=".$_GET['submodule']."&includefile=editIndexes";?>"><?php echo $formText_Cancel_input;?></a>
		</div>
	</div>
</form>
<div id="index_field_clone" style="display:none;">
<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr>
	<td width="80%">
		<select class="form-control" name="index_field[]">
			<?php
			foreach($v_table_fields as $s_table => $v_fields)
			{
				?><option value="" disabled><?php echo $s_table;?></option><?php
				foreach($v_fields as $s_field => $s_field_text)
				{
					?><option value="<?php echo $s_table.":".$s_field;?>" style="padding-left:20px;"><?php echo $s_field_text;?></option><?php
				}
			}
			?>
		</select>
	</td>
	<td align="right">
		<button type="button" class="btn btn-danger btn-sm" onClick="$(this).closest('table').remove();"><span class="glyphicon glyphicon-trash"></span></button>
		<button type="button" class="btn btn-default btn-sm" disabled><span class="glyphicon glyphicon-sort"></span></button>
	</td>
</tr></table>
</div>

<script type="text/javascript">
<?php if(isset($ob_javascript)) { ob_start(); } ?>
$(function(){
	$("#indexes_sortable").sortable();
	$("#index_field_add").on("click", function(){
		$("#indexes_sortable").append( $("#index_field_clone table").clone() );
	});
});
<?php if(isset($ob_javascript)) { $ob_javascript .= " ".ob_get_clean(); } ?>
</script>
<?php
}
?>