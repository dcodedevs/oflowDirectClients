<?php
if(!function_exists('include_local')) include(__DIR__."/../../includes/fn_include_local.php");

list($fieldtype,$rest) = explode(":",$field[11],2);

list($selectboxtype,$connectiontype,$menumodulemultiselect) = split(",",str_replace(" ","",$fieldtype));
$selectboxheight = substr($selectboxtype,1);
$selectboxtype = substr($selectboxtype,0,1);

if($selectboxheight == '' && $selectboxtype == 'M') $selectboxheight = 5;
if($selectboxtype == '') $selectboxtype = 'S';
if($connectiontype == '') $connectiontype = 'L';
if($selectboxtype == 'M' && $menumodulemultiselect == 'MS') $menumodulemultiselect = true; else $menumodulemultiselect = false;

$lastParent = $field[6][$langID];
$cID = $_GET['ID'];	   


$parents = $v_menues = array();
if($connectiontype == 'L')
{
	$o_query = $o_main->db->query("SELECT * FROM pageID, menulevel WHERE pageID.contentID = ? AND pageID.contentTable = ? AND pageID.deleted = 0 AND menulevel.id = pageID.menulevelID AND menulevel.content_status < 2", array($cID, $field[3]));
	if($o_query && $o_query->num_rows()>0)
	foreach($o_query->result_array() as $listlevel)
	{
		$parents[] = $listlevel['menulevelID'];
		$lastParent = $listlevel['menulevelID'];
		$v_menues[$listlevel['moduleID']] = 1;
		for($a = ($listlevel['level'] - 1); $a >= 0; $a--)
		{
			$writeParent = array();
			$o_find = $o_main->db->query('SELECT * FROM menulevel WHERE id = ? AND content_status < 2', array($lastParent));
			if($o_find && $o_find->num_rows()>0) $writeParent = $o_find->row_array();
			$parents[] = $writeParent['parentlevelID'];
			$lastParent = $writeParent['parentlevelID'];
			$v_menues[$writeParent['moduleID']] = 1;
		}
	}
} else {
	$o_query = $o_main->db->query("SELECT * FROM pageID, menulevel WHERE pageID.contentID = ? AND pageID.contentTable = ? AND pageID.deleted = 0 AND menulevel.id = pageID.menulevelID AND menulevel.content_status < 2", array($cID, $field[3]));
	if($o_query && $o_query->num_rows()>0)
	foreach($o_query->result_array() as $listlevel)
	{
		$parents[$listlevel['menulevelID']] = 1;
		$v_menues[$listlevel['moduleID']] = 1;
		if($listlevel['level'] > 0)
		{
			//get missing ids
			$l_last_id = $listlevel['menulevelID'];
			for($i=$listlevel['level']; $i>0; $i--)
			{
				$v_row = array();
				$o_find = $o_main->db->query('SELECT * FROM menulevel WHERE id = ? AND content_status < 2', array($l_last_id));
				if($o_find && $o_find->num_rows()>0) $v_row = $o_find->row_array();
				$parents[$v_row['parentlevelID']] = 1;
				$l_last_id = $v_row['parentlevelID'];
			}
		}
	}
	$parents = array_keys($parents);
}
if(!$menumodulemultiselect && count($v_menues)>0)
{
	reset($v_menues);
	$l_key = key($v_menues);
	$v_menues = array($l_key => $v_menues[$l_key]);
}
$v_menues_ids = array_keys($v_menues);
$writeLevel = array();
$o_query = $o_main->db->query('SELECT MAX(level) maxlevel FROM menulevel WHERE content_status < 2');
if($o_query && $o_query->num_rows()>0) $writeLevel = $o_query->row_array();
$level = ($writeLevel['maxlevel'] + 1);

$o_query = $o_main->db->query('SELECT * FROM moduledata WHERE type = 1');
?><div class="fieldname"><?php echo $formText_chooseMenu_picturegalleryinput;?></div>
<div class="fieldholder"><?php
if($access>=10)
{
	?><select id="<?php echo $field_ui_id."level-1";?>" name="<?php echo $field[1].$ending."level-1".($menumodulemultiselect?"[]":"");?>"<?php echo ($menumodulemultiselect ? ' multiple="multiple" size="'.$selectboxheight.'"' : '');?> onChange="javascript:fill_menulevel_<?php echo $field_ui_id;?>('<?php echo $field_ui_id."level";?>','-1');"><?php
}
if($o_query && $o_query->num_rows()>0)
foreach($o_query->result_array() as $v_row)
{
	$parentMenuName = $v_row['name'];
	if($access>=10)
	{
		if(count($v_menues) == 0)
		{
			$v_menues[$v_row['id']] = $v_row['name'];
			$v_menues_ids = array_keys($v_menues);
		}
		else if(in_array($v_row['id'],$v_menues_ids))
		{
			$v_menues[$v_row['id']] = $v_row['name'];
		}
		
		?><option value="<?php echo $v_row['id'];?>"<?php echo (in_array($v_row['id'],$v_menues_ids) ? " selected" : "");?>><?php echo $v_row['name'];?></option><?php
	} else {
		if(in_array($v_row['id'],$v_menues_ids)) print $v_row['name'];
		print '<input type="hidden" name="'.$field[1].$ending.'level-1" value="'.$v_row['id'].'">';
	}
}	
if($access>=10)
{
	?></select><?php
}

?><table border="0" cellspacing="0" cellpadding="0">
<tr><?php	   
for($t = 0; $t < $level; $t++)
{
	?><td class="fieldmenu"><div class="fieldname"><?php echo $formText_chooseMenulevel_picturegalleryinput." ".($t + 1);?></div></td><?php
}	
?></tr><tr><?php
$parentkey = false;
$extra_where = "";
$parentnamelistID = $parentnamelistName = array();
$v_settings = include_local(__DIR__."/../../../../".$parentMenuName."/input/settings/tables/menulevel.php");
$s_disp_field = $v_settings['prefieldInList'];
$orderby = '';
if($v_settings['orderByField'] != "") $orderby = "ORDER BY menulevel.".$o_main->db_escape_name($v_settings['orderByField']);
for($t = 0; $t < $level; $t++)
{
	if($t > 0 && ($t - 1) < sizeof($parents))
	{
		$extra_where = "AND (";
		for($x=0;$x<count($parents);$x++)
		{
			$extra_where .=  "menulevel.parentlevelID = ".$o_main->db->escape($parents[$x])." OR ";
		}
		$extra_where = substr($extra_where,0,strlen($extra_where) -3);
		$extra_where .= ")";
	}
	else if(($t - 1) >= sizeof($parents))
	{
		$extra_where = "AND 1 = 2";
	}
	
	$sql = "SELECT menulevel.id as lID, menulevel.*, menulevelcontent.* FROM menulevel, menulevelcontent WHERE menulevel.moduleID IN (".implode(",",$v_menues_ids).") AND menulevelcontent.languageID = ".$o_main->db->escape($s_default_output_language)." AND menulevel.level = ".$o_main->db->escape($t)." and menulevelcontent.menulevelID = menulevel.id $extra_where AND menulevel.content_status < 2 $orderby;";
	$l_counter = 0;
	$o_query = $o_main->db->query($sql);
	if($o_query) $l_counter = $o_query->num_rows();
	$extra_where = "";
	?><td class="menudropdown"><?php
	if($access>=10)
	{
		?><select id="<?php echo $field_ui_id."level".$t;?>" name="<?php echo $field[1].$ending."level".$t.($selectboxtype == "M"?"[]":"");?>"<?php echo ($selectboxtype=='M' ? ' multiple="multiple" size="'.$selectboxheight.'"' : '').($t < ($level-1) ? 'onchange="javascript:fill_menulevel_'.$field_ui_id.'(\''.$field_ui_id.'level\',\''.$t.'\');"' : '');?>>
		<option value="0"><?php echo ($l_counter==0 ? $formText_none_fieldtype : $formText_choose_fieldtype);?></option><?php
	}
	$i=0;
	if($l_counter>0)
	foreach($o_query->result_array() as $v_row)
	{
		if($t > 0)
		{
			$parentkey = array_search($v_row['parentlevelID'], $parentnamelistID[($t-1)]);
		}
		if($access>=10)
		{
			?><option value="<?php echo $v_row['parentlevelID']."_".$v_row['lID'];?>"<?php echo (in_array($v_row['lID'],$parents) ? " selected":"");?>><?php echo ($v_row['content_status']==1 ? '[inactive] - ':'').(($v_row['parentlevelID']==0 && $menumodulemultiselect) ? $v_menues[$v_row['moduleID']]." - " : "").($parentkey !== false ? $parentnamelistName[($t -1)][$parentkey]." - ":"").$v_row[$s_disp_field];?></option><?php
		} else if(in_array($v_row['lID'],$parents)) {
			if($i>0) print "<br/>";
			print ($v_row['content_status']==1 ? '[inactive] - ':'').(($v_row['parentlevelID']==0 && $menumodulemultiselect) ? $v_menues[$v_row['moduleID']]." - " : "").($parentkey !== false ? $parentnamelistName[($t -1)][$parentkey]." - ":"").$v_row[$s_disp_field];
			print '<input type="hidden" name="'.$field[1].$ending."level".$t.($selectboxtype == "M"?"[]":"").'" value="'.$v_row['parentlevelID']."_".$v_row['lID'].'">';
			$i++;
		}
		$parentnamelistID[$t][] = $v_row['lID'];
		$parentnamelistName[$t][] = $v_row[$s_disp_field];
	}
	if($access>=10)
	{
		?></select><?php
	}
	?></td><?php
}
?>
</tr>
</table>

<script type="text/javascript">
function fill_menulevel_<?php echo $field_ui_id;?>(field_ui_id, level)
{
	level = Number(level);
	var cur_values = '';
	var next_values = '';
	var next_select = document.getElementById(field_ui_id + (level + 1));
	if(next_select != null)
	{
		for (i=0; i<next_select.options.length; i++)
		{
			if (next_select.options[i].selected)
			{
				if(next_values.length>0) next_values = next_values + ':';
				next_values = next_values + next_select.options[i].value
			}
		}
		var cur_select = document.getElementById(field_ui_id + level);
		for (i=0; i<cur_select.options.length; i++)
		{
			if (cur_select.options[i].selected)
			{
				if(cur_values.length>0) cur_values = cur_values + ':';
				cur_values = cur_values + cur_select.options[i].value
			}
		}
		$.ajax({
			type: "GET",
			cache: false,
			url: "<?php echo $extradir;?>/input/fieldtypes/<?php echo $field[4];?>/ajax_menulevel.php",
			data: { level: (level + 1), parentID: cur_values, selected: next_values<?php echo ($selectboxtype == 'M' ? ', multi: 1' : '').($menumodulemultiselect ? ', menumodulemultiselect: 1' : '');?>, s_default_output_language: '<?php echo $s_default_output_language;?>' },
			success: function(data) {
				var obj = document.getElementById(field_ui_id + (level + 1));
				obj.options.length = 0;
				if(data != "NONE")
				{
					obj.options[0] = new Option("<?php echo $formText_choose_fieldtype;?>","0");
					var splitstring = data.split(";");
					var subsplit = "";
					for(e = 0; e < splitstring.length; e++)
					{
						subsplit = splitstring[e].split(":");
						obj.options[obj.length] = new Option(subsplit[1],subsplit[0],(subsplit[2]== '1' ? true : false));
					}
				} else {
					obj.options[0] = new Option("<?php echo $formText_none_fieldtype;?>","0");
				}
				fill_menulevel_<?php echo $field_ui_id;?>(field_ui_id, (level + 1));
			}
		});
	}
}
</script>