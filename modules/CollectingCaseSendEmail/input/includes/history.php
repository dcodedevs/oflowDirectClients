<div class="history" style="padding:20px 10px;">
	<h3><?php echo $formText_earlierVersionsStored_input;?>:</h3><?php
	$b_module_table_multi = false;
	if($o_main->db->table_exists($submodule.'content')) $b_module_table_multi = true;
	
	if($b_module_table_multi)
	{
		$s_sql = 'SELECT '.$submodule.'.id cid, '.$submodule.'.*, '.$submodule.'content.* FROM '.$submodule.' LEFT OUTER JOIN '.$submodule.'content ON '.$submodule.'content.'.$submodule.'ID = '.$submodule.'.id WHERE '.$submodule.'.id = '.$o_main->db->escape($ID).' ORDER BY '.$submodule.'content.languageID';
		$s_sql_history = 'SELECT '.$submodule.'.id cid, '.$submodule.'.*, '.$submodule.'content.* FROM '.$submodule.' LEFT OUTER JOIN '.$submodule.'content ON '.$submodule.'content.'.$submodule.'ID = '.$submodule.'.id WHERE '.$submodule.'.origId = '.$o_main->db->escape($ID).' ORDER BY '.$submodule.'.id DESC, '.$submodule.'content.languageID ASC';
	} else {
		$s_sql = 'SELECT id cid, '.$submodule.'.* FROM '.$submodule.' WHERE '.$submodule.'.id = '.$o_main->db->escape($ID);
		$s_sql_history = 'SELECT id cid, '.$submodule.'.* FROM '.$submodule.' WHERE '.$submodule.'.origId = '.$o_main->db->escape($ID).' ORDER BY '.$submodule.'.id DESC';
	}
	$origRow = array();
	$o_query = $o_main->db->query($s_sql);
	if($o_query && $o_query->num_rows()>0)
	{
		foreach($o_query->result_array() as $row)
		{
			unset($row['cid'], $row['id'], $row['moduleID'], $row['created'], $row['createdBy'], $row['updated'], $row['updatedBy'], $row[$submodule.'ID'], $row['origId'], $row['origcontentId']);
			$origRow[$row['languageID']] = $row;
		}
	}
	
	$o_query = $o_main->db->query($s_sql_history);
	if($o_query && $o_query->num_rows()>0)
	{
		foreach($o_query->result_array() as $row)
		{
			$cid = $row['cid'];
			$header = (isset($row['updated']) ? $row['updated'].' '.strtolower($formText_by_input).' '.$row['updatedBy'] : $row['created'].' '.strtolower($formText_by_input).' '.$row['createdBy']);
			unset($row['cid'],$row['id'],$row['moduleID'],$row['created'],$row['createdBy'],$row['updated'],$row['updatedBy'],$row[$submodule.'ID'],$row['origId'],$row['origcontentId'],$row['content_status']);
			$diff = array_diff_assoc($row, $origRow[$row['languageID']]);
			if(sizeof($diff)>0)
			{
				?><div class="history-item">
				<?php if($prevCid != $cid) { ?><h4><?php echo $header;?></h4><?php } ?>
				<table width="100%" border="0" cellpadding="0" cellspacing="0" frame="void" rules="none">
				<?php
				foreach($diff as $key => $item)
				{
					?><tr>
					<td style="padding:right:20px;"><strong><?php echo $fieldsStructure[$key][2];?></strong><?php echo (isset($row['languageID']) ? ' ('.$languageName[$row['languageID']].')' : '');?></td>
					<td style="width:70%;"><?php echo $item;?></td>
					</tr>
					<?php
				}
				?></table>
				</div>
				<?php
			}
			$prevCid = $cid;
			$origRow[$row['languageID']] = $row;
		}
	}
	?>
</div>