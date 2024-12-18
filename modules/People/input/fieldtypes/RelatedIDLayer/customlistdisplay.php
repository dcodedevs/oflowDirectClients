<?php
//print_r($linkersField[$key]);
$exex = explode("(::)",$linkersField[$key][11]);
$dataTable = $o_main->db_escape_name($exex[0]);
$dataID = explode(":",$exex[1]);
$dataID[0] = $o_main->db_escape_name($dataID[0]);
$dataFields = explode(",",$exex[2]);
$dVisible = $dParam = $dVar = $dLable = $dLink = $dExtra = $sqlSelect = $sqlOrder = array();
foreach($dataFields as $item)
{
	$d = explode(":",$item);
	$dVisible[]		= $d[0]; // v-visible in edit page, d-display in list, c-change link field, i-duplicate image, r-related table
	$dVar[]			= $d[1];
	$dLabel[]		= $d[2];
	$dLink[]		= $d[3];
	if(strpos($d[0],'c')!==false || strpos($d[0],'v')!==false || strpos($d[0],'i')!==false) $dParam[] = $d[1];
	
	if(isset($d[4])) $dExtra[] = explode('#',$d[4]);
	else $dExtra[] = '';
	
	$sqlSelect[] = $o_main->db_escape_name($d[1]);
	if(strpos($d[0],'d')!==false) $sqlOrder[] = $o_main->db_escape_name($d[1]);
}
$value = array();

if($o_main->db->table_exists($dataTable.'content'))
{
	$s_sql = "SELECT $dataTable.id cid, $dataTable.{$dataID[0]}, $dataTable.moduleID, ".implode(",",$sqlSelect)." FROM $dataTable LEFT OUTER JOIN {$dataTable}content ON {$dataTable}content.{$dataTable}ID = $dataTable.id AND {$dataTable}content.languageID = ".$o_main->db->escape($s_default_output_language)." WHERE $dataTable.$dataID[0] = ".$o_main->db->escape($writeContent[$finList])." AND $dataTable.content_status < 2 order by ".implode(",",$sqlOrder).";";
} else {
	$s_sql = "SELECT $dataTable.id cid, $dataTable.{$dataID[0]}, $dataTable.moduleID, ".implode(",",$sqlSelect)." FROM $dataTable WHERE $dataTable.$dataID[0] = ".$o_main->db->escape($writeContent[$finList])." AND $dataTable.content_status < 2 order by ".implode(",",$sqlOrder).";";
}

$o_query = $o_main->db->query($s_sql);
if(!$o_query)
{
	?><span style="color:red;"><?php echo $formText_IncorrectFieldSettings_Fieldtype;?></span><?php
	return;
}
$v_row = $o_query->row_array();
$value = array($v_row[$dataID[0]]);

for($l_field_x=0;$l_field_x<sizeof($dVar);$l_field_x++)
{
	if(strpos($dVisible[$l_field_x],'r')!==false)
	{
		$rRow = array();
		$s_sql = "SELECT ".$o_main->db_escape_name($dExtra[$l_field_x][2])." FROM ".$o_main->db_escape_name($dExtra[$l_field_x][0])." WHERE ".$o_main->db_escape_name($dExtra[$l_field_x][1])." = ".$o_main->db->escape($v_row[$dVar[$l_field_x]])." LIMIT 1";
		$o_find = $o_main->db->query($s_sql);
		if($o_find && $o_find->num_rows()>0) $rRow = $o_find->row_array();
		$v_row[$dVar[$l_field_x]] = $rRow[$dExtra[$l_field_x][2]];
	}
	if(strpos($dVisible[$l_field_x],'v')!==false) $value[$l_field_x+1] = $v_row[$dVar[$l_field_x]];
}
?>
<a class="optimize" href="<?php echo $editLink;?>"<?php echo $edit_link_attr;?>>
<span><b><?php echo ($dataID[1]!="" ? $dataID[1].": " : '');?></b></span><?php echo $value[0];?> <?php
for($l_field_x=0;$l_field_x<sizeof($dVar);$l_field_x++)
{
	if(strpos($dVisible[$l_field_x],'v')!==false)
	{
		?><span><b><?php echo ($dLabel[$l_field_x]!="" ? $dLabel[$l_field_x].": ":"");?></b></span><?php echo $value[$l_field_x+1];?> <?php
	}
}
?>
</a>