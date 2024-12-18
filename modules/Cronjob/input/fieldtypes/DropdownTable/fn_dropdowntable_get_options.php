<?php
function dropdowntable_get_options($i, $maxLevel, $contentSQL, $dataParentField, $dataParentLimit, $dataParentID, $dataID, $dataNames, $access, $selected)
{
	$o_main = get_instance();
	$sqlWhere .= " AND $dataParentField = ".$o_main->db->escape($dataParentID);
	$o_query = $o_main->db->query($contentSQL.$sqlWhere);
	if($o_query && $o_query->num_rows()>0)
	foreach($o_query->result_array() as $v_row)
	{
		$outName = "";
		foreach($dataNames as $dataName) $outName .= $v_row[$dataName]." ";
		
		?><option value="<?php echo $v_row[$dataID];?>"<?php echo ($v_row[$dataID] == $selected ? ' selected' : '');?>><? for($x=1;$x<=$i;$x++){if($x==$i) print '&rArr;'; print '&nbsp;&nbsp;&nbsp;&nbsp;';}?><?php echo $outName.($v_row['content_status']==1 ? ' - [inactive]':'');?></option><?php
		if($dataParentField!="" and ($dataParentLimit != 1 or ($dataParentLimit==1 and $maxLevel>$i+1)))
		{
			dropdowntable_get_options($i+1, $maxLevel, $contentSQL, $dataParentField, $dataParentLimit, $v_row[$dataID], $dataID, $dataNames, $access, $selected);
		}
	}
}
?>