<?php
if(!function_exists('include_local')) include(__DIR__."/../../includes/fn_include_local.php");

$lastParent = $field[6][$langID];
$parentMenu = 0;
if($field[6][$langID] == "" and $field[11] != "")
{
	if(is_numeric($field[11]))
	{
		$lastParent = $field[11];
	} else {
		$v_row = array();
		$o_query = $o_main->db->query("select * from pageID where contentID = ? AND contentTable = ?", array($_GET['relationID'], $field[11]));
		if($o_query && $o_query->num_rows()>0) $v_row = $o_query->row_array();
		$lastParent = $v_row['menulevelID'];
	}
	$field[6][$langID] = $lastParent;
} else if($field[6][$langID] == "" && intval($_GET['ID']) > 0)
{
	$v_row = array();
	$o_query = $o_main->db->query("select * from pageID where contentID = ? AND contentTable = ?", array($_GET['ID'], $field[3]));
	if($o_query && $o_query->num_rows()>0) $v_row = $o_query->row_array();
	$lastParent = $v_row['menulevelID'];
}

$parents = array($lastParent);
$o_query = $o_main->db->query("SELECT parentlevelID FROM menulevel WHERE id = ? AND content_status < 2 LIMIT 1", array($lastParent));
while($o_query && $row = $o_query->row_array())
{
	if($row['parentlevelID'] > 0)
	{
		array_unshift($parents,$row['parentlevelID']);
		$lastParent = $row['parentlevelID'];
		$o_query = $o_main->db->query("SELECT parentlevelID FROM menulevel WHERE id = ? AND content_status < 2 LIMIT 1", array($lastParent));
	} else {
		break;
	}
}

$v_row = array();
$o_query = $o_main->db->query('SELECT MAX(level) maxlevel FROM menulevel');
if($o_query && $o_query->num_rows()>0) $v_row = $o_query->row_array();
$level = ($v_row['maxlevel'] + 1);

$v_row = array();
$o_query = $o_main->db->query('SELECT moduleID FROM menulevel WHERE id = ?', array($parents[0]));
if($o_query && $o_query->num_rows()>0) $v_row = $o_query->row_array();
if($v_row["moduleID"] != "") $parentMenu = $v_row["moduleID"];
$o_query = $o_main->db->query('SELECT * FROM moduledata WHERE type = 1');
?><div class="fieldname" <?php print ($field[10]==1||$access<10?" style='display:none;'":""); ?>><?php echo $formText_chooseMenu_fieldtype;?></div>
<select <?php echo $field_attributes;?> id="<?php echo $field_ui_id."level-1";?>" onchange="javascript:fill_menulevel_<?php echo $field_ui_id;?>('<?php echo $field_ui_id."level"; ?>','-1');" <?php echo ($field[10]==1||$access<10?" style='display:none;'":"");?>><?php
if($o_query && $o_query->num_rows()>0)
foreach($o_query->result_array() as $row)
{
	if($parentMenu == 0)
	{
		$parentMenu = $row['id'];
		$parentMenuName = $row['name'];
	}
	else if($parentMenu == $row['id'])
	{
		$parentMenuName = $row['name'];
	}
	?><option value="<?php echo $row['id'];?>"<?php echo ($parentMenu==$row['id']?" selected":"");?>><?php echo $row['name'];?></option><?php
}	
?></select><?php


?><table border="0" cellspacing="0" cellpadding="0" <?php print ($field[10]==1||$access<10?" style='display:none;'":""); ?>>
<tr><?php	   
for($t = 0; $t < $level; $t++)
{
	?><td class="fieldmenu"><div class="fieldname"><?php echo $formText_chooseMenulevel_fieldtype; ?><?php  print " ".($t + 1);  ?></div></td><?php
}	
?></tr><tr><?php      
$table = "menulevel";
    $selectedname = '';
for($t = 0; $t < $level; $t++)
{
	$extra_where = $orderby = "";
	if($t > 0 && ($t - 1) < sizeof($parents))
	{
		$extra_where .= "AND {$table}.parentlevelID = ".$o_main->db->escape($parents[$t - 1]);
	} else if(($t - 1) >= sizeof($parents))
	{
		$extra_where .= "AND 1 = 2";
	}
	$extra_where .= " AND {$table}.content_status < 2";
	$sql = "SELECT $table.id cid, $table.*, {$table}content.* FROM $table JOIN {$table}content ON {$table}content.{$table}ID = $table.id AND {$table}content.languageID = ".$o_main->db->escape($s_default_output_language)." WHERE $table.moduleID = ".$o_main->db->escape($parentMenu)." AND $table.level = ".$o_main->db->escape($t)." ".$extra_where;
	
	$menu_settings = include_local(__DIR__."/../../../../".$parentMenuName."/input/settings/tables/menulevel.php");
	if($menu_settings['orderByField'] != "") $sql .= " ORDER BY $table.".$o_main->db_escape_name($menu_settings['orderByField']);
	
	$l_count = 0;
	$o_query = $o_main->db->query($sql);
	if($o_query) $l_count = $o_query->num_rows();
    
	?>            
	<td class="menudropdown">
		<select id="<?php echo $field_ui_id."level".$t;?>" <?php echo ($t < ($level - 1) ? "onChange=\"javascript:fill_menulevel_".$field_ui_id."('".$field_ui_id."level"."','".$t."');\"":"onchange=\"javascript:$('#".$field_ui_id."').val($(this).val());\"").($field[10]==1||$access<10?" style='display:none;'":"");?>>
		<option value="0"><?php echo ($l_count==0 ? $formText_none_fieldtype : $formText_choose_fieldtype);?></option><?php
		if($o_query && $o_query->num_rows()>0)
		foreach($o_query->result_array() as $row)
		{
			?><option value="<?php echo $row['cid'];?>"<?php if($parents[$t]==$row['cid']){ print " selected"; $selectedname = $row[$menu_settings['prefieldInList']]; }?>><?php echo $row[$menu_settings['prefieldInList']].($row['content_status']==1 ? " - [inactive]" : ""); ?></option><?php
		}
		?></select>
	</td><?php
}	
?>
</tr>
</table>
<?php
if($field[10]==1){?>
<div style="padding-bottom: 10px; font-weight: bold;" class="readonlydisplayname"><input class=" form-control" type="text" readonly value="<?Php print $selectedname; ?>"/></div><?php } ?>
<input <?php echo $field_attributes;?> id="<?php echo $field_ui_id;?>" type="hidden" name="<?php echo $field[1].$ending;?>" value="<?php echo htmlspecialchars($field[6][$langID]);?>" />
<script type="text/javascript">
<?php if(isset($ob_javascript)) { ob_start(); } ?>
<?php if($field[10]==1){?>
    $( document ).ready(function() {
        console.log('<?php print $selectedname; ?>');
    $('readonlydisplayname').text('<?php print $selectedname; ?>');
});
    
    <?php } ?>
function fill_menulevel_<?php echo $field_ui_id;?>(field_ui_id, level)
{
	level = Number(level);
	var selected_id = $("#"+field_ui_id+level).val();
	$("#<?php echo $field_ui_id;?>").val(selected_id);
	
	var obj_counter = level + 1;
	var obj = document.getElementById(field_ui_id + obj_counter);
	while(obj != null)
	{
		obj.options.length = 0;
		obj.options[0] = new Option("<?php echo $formText_none_fieldtype;?>",0);
		obj_counter++;
		obj = document.getElementById(field_ui_id + obj_counter);
	}
	$.ajax({
		type: "GET",
		cache: false,
		url: "<?php echo $extradir;?>/input/fieldtypes/<?php echo $field[4];?>/ajax_menulevel.php",
		data: { level: (level + 1), parentID: selected_id, s_default_output_language: '<?php echo $s_default_output_language;?>' },
		success: function(data) {
			if(data != "NONE")
			{
				var obj = document.getElementById(field_ui_id + (level + 1)),
				splitstring = data.split(";"), subsplit;
				
				obj.options.length = 0;
				obj.options[obj.length] = new Option("<?php echo $formText_choose_fieldtype;?>",0);
						
				for(e = 0; e < splitstring.length; e++)
				{
					subsplit = splitstring[e].split(":");
					obj.options[obj.length] = new Option(subsplit[1],subsplit[0]);
				}
			}
		}
	});
}
<?php if(isset($ob_javascript)) { $ob_javascript .= " ".ob_get_clean(); } ?>
</script>