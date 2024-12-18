<?php
define('BASEPATH', realpath(__DIR__.'/../../../../../').DIRECTORY_SEPARATOR);
require_once(BASEPATH.'elementsGlobal/cMain.php');
$filter = $_GET['filter'];
$param = $_GET['param'];
list($relationField,$relationDataField,$dataTable,$dataField,$dataShowFields) = explode(":",$param);
$relationField = $o_main->db_escape_name($relationField);
$relationDataField = $o_main->db_escape_name($relationDataField);
$dataTable = $o_main->db_escape_name($dataTable);
$dataField = $o_main->db_escape_name($dataField);
$v_items = explode(',', $dataShowFields);
foreach($v_items as $l_key => $s_item) $v_items[$l_key] = $o_main->db_escape_name($s_item);
$dataShowFields = implode(',', $v_items);

$relationSql="";
if($relationField!="" && $relationDataField!="")
{
	$relationSql = "AND $relationDataField = ".$o_main->db->escape($filter);
}
$sql = "SELECT $dataField, $dataShowFields, $dataTable.content_status FROM $dataTable WHERE $dataTable.content_status < 2 ".$relationSql.";";
$o_query = $o_main->db->query($sql);
if($o_query && $o_query->num_rows()>0)
{
	foreach($o_query->result_array() as $v_row)
	{
		$items = array();
		$keys = explode(",",$dataField.",".$dataShowFields);
		foreach($keys as $key)
		{
			$item2 = explode("¤",$v_row[$key]);
			for($i=0;$i<sizeof($item2);$i++)
			{
				$items[$i][$key] = $item2[$i];
			}
		}
		foreach($items as $item)
		{
			list($dropID, $rest) = explode(",",implode(",",$item),2);
			$dropArray = explode(",",$rest);
			?><option value="<?php echo $dropID;?>"><?php echo implode(", ",$dropArray).($v_row['content_status']==1 ? ' - [inactive]':'');?></option><?php
		}
	}
}
?>