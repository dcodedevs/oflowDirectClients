<?php
header('Content-Type: text/html; charset=utf-8');
$field[11] = $_POST['settings'];
$ID = $_POST['id'];
$s_single_table = $_POST['s_single_table'];
$access = $_POST['access'];
$field_ui_id = $_POST['field_ui_id'];
$s_default_output_language = $_POST['s_default_output_language'];
$choosenListInputLang = $_POST['choosenListInputLang'];
$extradir = __DIR__."/../../../";
define('BASEPATH', realpath(__DIR__.'/../../../../../').DIRECTORY_SEPARATOR);
require_once(BASEPATH.'elementsGlobal/cMain.php');
include(__DIR__."/../../includes/readInputLanguage.php");

$options = explode(":",$field[11]);
$options = array_map('trim',$options);
$options[0] = $o_main->db_escape_name($options[0]);
$options[1] = $o_main->db_escape_name($options[1]);
$options[2] = explode(",", $options[2]);
foreach($options[2] as $l_key => $s_value) $options[2][$l_key] = $o_main->db_escape_name($s_value);
$options[3] = $o_main->db_escape_name($options[3]);
$options[4] = $o_main->db_escape_name($options[4]);
$options[5] = $o_main->db_escape_name($options[5]);
$l_per_page = 20;

$extraSelect = $extraJoin = "";
if($options[6] != 1)
{
	$extraSelect = ', r.'.$options[4];
	$extraJoin = ' LEFT OUTER JOIN '.$options[3].' r ON c.'.$options[1].' = r.'.$options[5].' AND r.'.$options[4].' = '.$o_main->db->escape($ID).' AND r.contentTable = '.$o_main->db->escape($s_single_table);
}
$s_sql_where = "";
if($o_main->multi_acc && $o_main->db->field_exists('account_id', $options[0]))
{
	$s_sql_where = "c.account_id = '".$o_main->db->escape_str($o_main->account_id)."'";
}
if($o_main->db->table_exists($options[0].'content'))
{
	$s_sql = "SELECT c.".$options[1]." cid, c.*, cc.*".$extraSelect." FROM ".$options[0]." c LEFT OUTER JOIN ".$options[0]."content cc ON c.id = cc.".$options[0]."ID AND cc.languageID = ".$o_main->db->escape($s_default_output_language).$extraJoin." WHERE ".$s_sql_where;
	
} else {
	$s_sql = "SELECT c.".$options[1]." cid, c.*".$extraSelect." FROM ".$options[0]." c".$extraJoin." WHERE ".$s_sql_where;
}
$s_sql_search = "";
if(isset($_POST['data_search']) and !empty($_POST['data_search']))
{
	$s_search = $o_main->db->escape_like_str($_POST['data_search']);
	foreach($options[2] as $s_item)
	{
		$s_sql_search .= ($s_sql_search!=""?" OR ":" AND (").$s_item." LIKE '%".$s_search."%' ESCAPE '!'";
	}
	$s_sql_search .= ")";
	$s_sql .= $s_sql_search;
}
$s_sql .= " AND c.content_status < 2";

$l_total_count = $l_showing = 0;
$o_query = $o_main->db->query($s_sql);
if($o_query) $l_total_count = $o_query->num_rows();
$l_page = (isset($_POST['data_page']) ? intval($_POST['data_page']) : 0);
$l_total_pages = ceil($l_total_count/$l_per_page);
if($l_total_pages > 1)
{
	$s_sql .= " ORDER BY ".($options[6] != 1 ? 'r':'c').".sortnr LIMIT ".($l_page*$l_per_page).", ".$l_per_page;
	$o_query = $o_main->db->query($s_sql);
}

if($o_query) $l_showing = $o_query->num_rows();
?>
<table class="table table-striped table-condensed table-hover">
<thead><tr><th></th><th></th><?php foreach($options[2] as $s_item) { ?><th></th><?php } ?></tr></thead>
<tbody>
<?php
if($l_total_pages==0)
{
	?><tr><td colspan="<?php echo count($options[2])+2;?>"><?php echo $formText_NoContentFound_fieldtype;?></td></tr><?php
} else {
	foreach($o_query->result_array() as $v_row)
	{
		$v_data = array();
		$s_js = $s_name = "";
		$s_link_prefix = '<a href="javascript:;" class="script" onClick="javascript:change_'.$field_ui_id.'(this); return false;" data-id="'.$v_row['cid'].'"';
		foreach($options[2] as $s_item) $s_link_prefix .= ' data-'.strtolower($s_item).'="'.$v_row[$s_item].'"';
		$s_link_prefix .= '>';
		$s_link_sufix = '</a>';
		?><tr>
			<td style="text-align:right;"><?php echo $s_link_prefix.$v_row['cid'].$s_link_sufix;?></td>
			<?php
			foreach($options[2] as $s_item)
			{
				?><td><?php echo $s_link_prefix.$v_row[$s_item].$s_link_sufix;?></td><?php
			}
			?>
			<td><?php if($v_row['content_status']==1) echo '[inactive]';?></td>
		</tr><?php
	}
}
?></tbody>
</table>
<?php
if($l_total_pages > 1)
{
	?><div class="text-center"><ul class="pagination pagination-sm" style="margin:0; text-align:center;"><?php
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
	?></ul></div><?php
}
