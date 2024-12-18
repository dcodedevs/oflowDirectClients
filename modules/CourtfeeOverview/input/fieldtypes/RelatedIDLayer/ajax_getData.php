<?php
header('Content-Type: text/html; charset=utf-8');
$field[11] = $_POST['settings'];
$access = $_POST['access'];
$field_ui_id = $_POST['field_ui_id'];
$s_default_output_language = $_POST['s_default_output_language'];
$choosenListInputLang = $_POST['choosenListInputLang'];
$extradir = __DIR__."/../../../";
define('BASEPATH', realpath(__DIR__.'/../../../../../').DIRECTORY_SEPARATOR);
require_once(BASEPATH.'elementsGlobal/cMain.php');
include(__DIR__."/../../includes/readInputLanguage.php");

$exex = explode("(::)",$field[11]);
$dataTable = $o_main->db_escape_name($exex[0]);
$dataID = explode(":",$exex[1]);
$dataID[0] = $o_main->db_escape_name($dataID[0]);
$dataFields = explode(",",$exex[2]);
$l_per_page = (isset($exex[3]) ? intval($exex[3]) : 20);

$dVisible = $dVar = $dLable = $dLink = $dExtra = $sqlSelect = $sqlOrder = array();
foreach($dataFields as $item)
{
	$d = explode(":",$item);
	$dVisible[]		= $d[0]; // v-visible in edit page, d-display in list, c-change link field, i-duplicate image, r-related table, s-subcontent
	$dVar[]			= $d[1];
	$dLabel[]		= $d[2];
	$dLink[]		= $d[3];
	if(isset($d[4])) $dExtra[] = explode('#',$d[4]);
	else $dExtra[] = '';
	
	if(strpos($d[0],'s')===false) $sqlSelect[] = $o_main->db_escape_name($d[1]);
	if(strpos($d[0],'d')!==false) $sqlOrder[] = $o_main->db_escape_name($d[1]);
}
$s_extra_where = "";
if(sizeof($dataID)>2) {
	if($s_extra_where!="") $s_extra_where .= " AND ";
	$s_extra_where .= $o_main->db_escape_name($dataID[3])." = ".$o_main->db->escape($_POST[$dataID[3]]);
}
if($s_extra_where!="") $s_extra_where .= " AND ";
$s_extra_where .= "$dataTable.content_status < 2";
if($o_main->multi_acc && $o_main->db->field_exists('account_id', $dataTable))
{
	$s_extra_where.= " AND $dataTable.account_id = '".$o_main->db->escape_str($o_main->account_id)."'";
}

$convFrom = array("\"","'");
$convTo = array("%22","%27");

if($o_main->db->table_exists($dataTable.'content'))
{
	$sql = "SELECT $dataTable.id cid, $dataTable.{$dataID[0]}, $dataTable.moduleID, ".implode(",",$sqlSelect)." FROM $dataTable LEFT JOIN {$dataTable}content ON {$dataTable}content.{$dataTable}ID = $dataTable.id AND {$dataTable}content.languageID = ".$o_main->db->escape($s_default_output_language).($o_main->multi_acc?" AND {$dataTable}content.account_id = '".$o_main->db->escape_str($o_main->account_id)."'":"").($s_extra_where!="" ? " WHERE ".$s_extra_where : "");
} else {
	$sql = "SELECT $dataTable.id cid, $dataTable.{$dataID[0]}, $dataTable.moduleID, ".implode(",",$sqlSelect)." FROM $dataTable ".($s_extra_where!="" ? " WHERE ".$s_extra_where : "");
}
if(isset($_POST['data_search']) and !empty($_POST['data_search']))
{
	$s_search = $o_main->db->escape_like_str($_POST['data_search']);
	$sql .= ($s_extra_where=="" ? " WHERE ($dataTable.{$dataID[0]} LIKE '".$s_search."%' ESCAPE '!'" : " AND ($dataTable.{$dataID[0]} LIKE '%".$s_search."%' ESCAPE '!'");
	foreach($sqlSelect as $s_item)
	{
		$sql .= " OR $s_item LIKE '%".$s_search."%' ESCAPE '!'";
	}
	$sql .= ")";
}
$l_total_count = $l_showing = 0;
$o_query = $o_main->db->query($sql);
if($o_query) $l_total_count = $o_query->num_rows();
$l_page = (isset($_POST['data_page']) ? intval($_POST['data_page']) : 0);
$l_total_pages = ceil($l_total_count/$l_per_page);
if($l_total_pages > 1)
{
	$sql .= " order by ".implode(",",$sqlOrder)." LIMIT ".($l_page*$l_per_page).", $l_per_page";
	$o_query = $o_main->db->query($sql);
}
if($o_query) $l_showing = $o_query->num_rows();
?>
<table class="table table-striped table-condensed">
<tbody><?php
if($l_total_pages==0)
{
	?><tr><td><?php echo $formText_NoContentFound_fieldtype;?></td></tr><?php
}
if($o_query && $o_query->num_rows()>0)
foreach($o_query->result_array() as $v_row)
{
	if($v_row[$dataID[0]] == $field[6][$langID]) $value = array($v_row[$dataID[0]]);
	
	$v_data = array();
	$s_js = $s_name = "";
	if($v_row['content_status']==1) $s_name .= "[inactive] - ";
	for($x=0;$x<sizeof($dVar);$x++)
	{
		if(strpos($dVisible[$x],'r')!==false)
		{
			$rRow = array();
			$s_sql = "SELECT ".$o_main->db_escape_name($dExtra[$x][2])." FROM ".$o_main->db_escape_name($dExtra[$x][0])." WHERE ".$o_main->db_escape_name($dExtra[$x][1])." = ".$o_main->db->escape($v_row[$dVar[$x]])." ".($o_main->multi_acc?" AND account_id = '".$o_main->db->escape_str($o_main->account_id)."'":"")." LIMIT 1";
			$o_find = $o_main->db->query($s_sql);
			if($o_find && $o_find->num_rows()>0) $rRow = $o_find->row_array();
			$v_row[$dVar[$x]] = $rRow[$dExtra[$x][2]];
		}
		if(strpos($dVisible[$x],'c')!==false || strpos($dVisible[$x],'v')!==false) $v_data[$dVar[$x]] = htmlspecialchars($v_row[$dVar[$x]]);
		else if(strpos($dVisible[$x],'i')!==false) $v_data[$dVar[$x]] = htmlspecialchars($v_row[$dVar[$x]]);
		if(strpos($dVisible[$x],'d')!==false) $s_name .= $v_row[$dVar[$x]]." ";
	}
	
	$v_module = array();
	$o_find = $o_main->db->query('SELECT name FROM moduledata WHERE uniqueiD = ?', array($v_row['moduleID']));
	if($o_find && $o_find->num_rows()>0) $v_module = $o_find->row_array();
	?><tr>
		<td><?php
		if($access>=10)
		{
			print '<a href="#" class="script" onClick="javascript:change_'.$field_ui_id.'(this); return false;" data-id="'.$v_row['cid'].'" data-url="&module='.$v_module['name'].'&ID='.$v_row['cid'].'"';
			foreach($v_data as $s_key => $s_value)
			{
				print ' data-'.strtolower($s_key).'="'.$s_value.'"';
			}
			print '>'.$s_name.'</a>';
		} else {
			print $s_name;
		}
		?></td>
	</tr><?php
}
?></tbody>
</table>
<?php
if($l_total_pages > 1)
{
	?><ul class="pagination pagination-sm" style="margin:0;"><?php
	for($l_x = 0; $l_x < $l_total_pages; $l_x++)
	{
		if($l_x < 1 || ($l_x > ($l_page - 7) && $l_x < ($l_page + 7)) || $l_x >= ($l_total_pages - 1))
		{
			$b_print_space = true;
			?><li<?php echo ($l_page==$l_x ? ' class="active"' : '');?>><a href="#" class="script" onClick="javascript:<?php echo ($l_page==$l_x ? '' : 'load_'.$field_ui_id.'(\''.$l_x.'\'); ');?>return false;"><?php echo ($l_x+1);?></a></li><?php
		} else if($b_print_space) {
			$b_print_space = false;
			?><li><a class="script" onClick="javascript:return false;">...</a></li><?php
		}
	}
	?></ul><?php
}