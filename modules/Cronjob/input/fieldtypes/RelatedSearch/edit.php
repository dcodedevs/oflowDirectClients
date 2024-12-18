<?php
//get single-language table
$s_single_table = $field[3];
foreach($databases as $tmp_key => $tmp_value)
{
	$s_check_table = substr($field[3],0,strlen($tmp_key));
	$s_check_rest = substr($field[3],strlen($tmp_key));
	if($tmp_value->multilanguage == 0 && $s_check_table == $tmp_key && ($s_check == "" || $s_check == "content"))
	{
		$s_single_table = $tmp_key;
		break;
	}
}

$options = explode(":",$field[11]);
$options = array_map('trim',$options);
$options[0] = $o_main->db_escape_name($options[0]);
$options[1] = $o_main->db_escape_name($options[1]);
$options[3] = $o_main->db_escape_name($options[3]);
$options[4] = $o_main->db_escape_name($options[4]);
$options[5] = $o_main->db_escape_name($options[5]);

$extraSelect = $extraJoin = "";
//check if table is set
if($options[6] != 1)
{
	if(!$o_main->db->table_exists($options[3]))
	{ 
		for($x=7;$x<=50;$x++)
		{
			if($options[$x]!= '')
			{
				$extrafieldcreate .=" ".$o_main->db_escape_name($options[$x])." char(255) NOT NULL,";
			}
			else
				break;
		}
		$b_table_created = $o_main->db->query("CREATE TABLE `{$options[3]}` (
			`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
			`sortnr` INT(11) NOT NULL,
			`{$options[4]}` INT(11) NOT NULL,
			`{$options[5]}` INT(11) NOT NULL,".$extrafieldcreate."			
			`contentTable` CHAR(100) NOT NULL,
			INDEX `Relation` (`{$options[4]}`, `{$options[5]}`, `contentTable`)
		)");
		if(!$b_table_created)
		{
			echo $formText_RelationTableIsNotCreated_Fieldtype;
			return;
		}
	}
	$extraSelect = ', r.'.$options[4];
	$extraJoin = 'LEFT OUTER JOIN '.$options[3].' r on c.'.$options[1].' = r.'.$options[5].' and r.'.$options[4].' = '.$o_main->db->escape($ID).' and r.contentTable = '.$o_main->db->escape($s_single_table);
}
if(isset($ob_javascript))
{
	$ob_javascript .= " ".file_get_contents(__DIR__."/js/actb.js");
} else {
	?><script language="javascript" type="text/javascript" src="<?php echo $extradir;?>/input/fieldtypes/<?php echo $field[4];?>/js/actb.js"></script><?php
}
?><script type="text/javascript"><?php
if(isset($ob_javascript)) { ob_start(); }

$relatedName = $relatedID = array();
if($options[6] == 1) $relatedID = json_decode($field[6][$langID],true);

if($o_main->db->table_exists($options[0].'content'))
{
	$sql = "select c.{$options[1]} cid, c.*, cc.* $extraSelect from {$options[0]} c LEFT OUTER JOIN {$options[0]}content cc ON c.id = cc.{$options[0]}ID AND cc.languageID = ".$o_main->db->escape($s_default_output_language)." $extraJoin WHERE c.content_status < 2 ORDER BY r.sortnr";
	
} else {
	$sql = "select c.{$options[1]} cid, c.* $extraSelect from {$options[0]} c $extraJoin WHERE c.content_status < 2 ORDER BY r.sortnr";
}
$o_query = $o_main->db->query($sql);
$first = true;
print "var ".$field_ui_id."SearchArray = new Array(";
if($o_query && $o_query->num_rows()>0)
foreach($o_query->result_array() as $row)
{
	if($options[6] == 1)
	{
		$x = array_search($row['cid'],$relatedID);
		if($x !== false)
		{
			$relatedName[$x] = $row[$options[2]];
		}
	} else if($ID > 0 and $row[$options[4]] > 0)
	{
		$relatedID[$row['cid']] = $row['cid'];
		$relatedName[] = $row[$options[2]];
	}
	if($access >= 10)
	{
		if(!$first) print ", ";
		print "'".$row['cid']." | ".addslashes($row[$options[2]]).($row['content_status']==1?" - [inactive]":"")."'";
		$first = false;
	}
}
print ");";

?>
function addForm<?php echo $field_ui_id;?>()
{
	var id = $("#<?php echo $field_ui_id;?>_ids").val();
	id = (id - 1) + 2;
	var appendval = "<div id='<?php echo $field_ui_id;?>_item"+id+"' class='<?php echo $field_ui_id;?>_item list-group-item'><label for='<?php echo $field_ui_id;?>_ID_"+id+"'>ID:</label><input style='margin-left:10px; margin-right:10px; width:80px;' name='<?php echo $field[1].$ending;?>[]' onFocus='actb(this,event,<?php echo $field_ui_id."SearchArray";?>,\"<?php echo $field_ui_id;?>_name"+id+"\");' id='<?php echo $field_ui_id;?>_ID_"+id+"' />";
	<?php for($x=7;$x<count($options);$x++)
	{
		?>appendval +="<label for='<?php echo $field_ui_id."_".$options[$x]."_ID_".$i;?>'><?php print $options[$x]; ?>:</label><input type='text' style='margin-left:10px; margin-right:10px; margin-right:10px; width:80px;' name='<?php echo $field[1]."_".$options[$x]."".$ending;?>[]' id='<?php echo $field_ui_id."_".$options[$x]."_ID_".$i;?>' >";
		<?php 
	}?>
	appendval +="<span style='padding-left:10px; font-weight:bold;' id='<?php echo $field_ui_id;?>_name"+id+"'></span><a class='remove_<?php echo $field_ui_id;?> script' href='javascript:;' onclick='removeForm<?php echo $field_ui_id;?>(\"#<?php echo $field_ui_id;?>_item"+id+"\"); return false;'><span class='glyphicon glyphicon-trash' aria-hidden='true'></span></a></div>";
	$("#<?php echo $field_ui_id;?>_items").append(appendval);
	
	$("#<?php echo $field_ui_id;?>_ids").val(id);
}

function removeForm<?php echo $field_ui_id;?>(id)
{
	$(id).remove();
}
<?php if(isset($ob_javascript)) { $ob_javascript .= " ".ob_get_clean(); } ?>
</script>

<div class="<?php echo ($field[9]==1?"hide":""); ?>"><?php
if($field[10] != 1 and $access >= 10)
{
	?><a class="add_<?php echo $field_ui_id;?> script" href="javascript:addForm<?php echo $field_ui_id;?>();"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> <?php echo $formText_Add_RelatedSearch;?></a><?php
}
?></div>
<div id="<?php echo $field_ui_id; ?>_items" class="list-group ui-sortable">
<?php
$i=0;
foreach($relatedID as $l_related_id)
{
	?><div id="<?php echo $field_ui_id;?>_item<?php echo $i;?>" class="<?php echo $field_ui_id;?>_item list-group-item">
	<label for="<?php echo $field_ui_id;?>_ID_<?php echo $i;?>">ID:</label><?php
	if($field[10] != 1 and $access >= 10)
	{
		$s_sql = "SELECT * FROM ".$options[3]." WHERE ".$options[5]." = ? AND ".$options[4]." = ?";
		$o_query = $o_main->db->query($s_sql, array($l_related_id, $_GET['ID']));
		$v_extra_value = $o_query->row_array();
		?><input style="margin-left:10px; width:80px;" name="<?php echo $field[1].$ending;?>[]" id="<?php echo $field_ui_id;?>_ID_<?php echo $i;?>" value="<?php echo $l_related_id;?>" onFocus="actb(this,event,<?php echo $field_ui_id."SearchArray";?>,'<?php echo $field_ui_id;?>_name<?php echo $i;?>');" /> <?php
		for($x=7;$x<count($options);$x++)
		{
			?><label for="<?php echo $field_ui_id."_".$options[$x]."_ID_".$i;?>"><?php print $options[$x]; ?>:</label><input type="text" style="margin-left:10px; margin-right:10px; width:80px;" name="<?php echo $field[1]."_".$options[$x]."".$ending;?>[]" value="<?php print $v_extra_value[$options[$x]]; ?>" id="<?php echo $field_ui_id."_".$options[$x]."_ID_".$i;?>" >
			<?php 
		}
		?><span style="padding-left:10px; font-weight:bold;" id="<?php echo $field_ui_id;?>_name<?php echo $i;?>"><?php echo $relatedName[$i];?></span><a class="remove_<?php echo $field_ui_id;?> script" href="javascript:;" onclick="removeForm<?php echo $field_ui_id;?>('#<?php echo $field_ui_id;?>_item<?php echo $i;?>'); return false;"><span class="glyphicon glyphicon-trash" aria-hidden="true"></span></a><?php
	} else {
		?><input id="<?php echo $field_ui_id;?>_ID_<?php echo $i;?>" type="hidden" name="<?php echo $field[1].$ending;?>[]" value="<?php echo $l_related_id;?>" /><?php
		print $l_related_id." - ".$relatedName[$i];
	}
	?></div><?php
	$i++;
}

if($i == 0 and $field[10] != 1 and $access >= 10)
{
	?><div id="<?php echo $field_ui_id;?>_item<?php echo $i;?>" class="<?php echo $field_ui_id;?>_item">
	<label for="<?php echo $field_ui_id;?>_ID_<?php echo $i;?>">ID:</label><input style="margin-left:10px; margin-right:10px; width:80px;" name="<?php echo $field[1].$ending;?>[]" onFocus="actb(this,event,<?php echo $field_ui_id."SearchArray";?>,'<?php echo $field_ui_id;?>_name<?php echo $i;?>');" id="<?php echo $field_ui_id;?>_ID_<?php echo $i;?>" />
    <?php for($x=7;$x<count($options);$x++)
	{
		?><label for="<?php echo $field_ui_id."_".$options[$x]."_ID_".$i;?>"><?php print $options[$x]; ?>:</label><input type="text" style="margin-left:10px; margin-right:10px; width:80px;" name="<?php echo $field[1]."_".$options[$x]."".$ending;?>[]" id="<?php echo $field_ui_id."_".$options[$x]."_ID_".$i;?>" >
		<?php 
	}?><span style="padding-left:10px; font-weight:bold;" id="<?php echo $field_ui_id;?>_name<?php echo $i;?>"></span><a class="remove_<?php echo $field_ui_id;?> script" href="javascript:;" onclick="removeForm<?php echo $field_ui_id;?>('#<?php echo $field_ui_id;?>_item<?php echo $i;?>'); return false;"><span class="glyphicon glyphicon-trash" aria-hidden="true"></span></a>
	</div><?php
}

?></div>
<input type="hidden" id="<?php echo $field_ui_id;?>_ids" value="<?php echo $i; ?>" />
<style>
#<?php echo $field_ui_id;?>_items { _border:3px double #505050; padding:5px; background-color:#CCC;}
.<?php echo $field_ui_id;?>_item { _border: 1px solid #999999; margin: 2px; padding: 2px; background-color:#FFFFFF; }
.<?php echo $field_ui_id;?>_item img { margin-right:10px; vertical-align:middle;}
/*.label_group { margin-top:5px; padding:4px; background-color:#c8d6f6; border:1px solid #006; width:360px; }
.image_labels input { width:300px; }
.image_labels div { padding-top:3px; }*/
.remove_<?php echo $field_ui_id;?> { float:right; padding-top:3px; padding-right:5px; }
.add_<?php echo $field_ui_id;?> { font-weight:bold; text-decoration:none; }
.add_<?php echo $field_ui_id;?> img { vertical-align: middle;}
</style>
<script type="text/javascript">
$(function(){
	$("#<?php echo $field_ui_id; ?>_items").sortable();
});
</script>