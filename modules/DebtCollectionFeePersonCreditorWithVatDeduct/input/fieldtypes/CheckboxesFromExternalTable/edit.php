<?php
if(!function_exists("print_checkboxes_from_external_table")) include(__DIR__."/fn_print_checkboxes_from_external_table.php");
$options = explode(":",$field[11]);
$options[0] = $o_main->db_escape_name($options[0]);
$options[1] = $o_main->db_escape_name($options[1]);
$options[2] = $o_main->db_escape_name($options[2]);
$options[3] = $o_main->db_escape_name($options[3]);
$options[4] = $o_main->db_escape_name($options[4]);
$options[5] = $o_main->db_escape_name($options[5]);
$options[6] = $o_main->db_escape_name($options[6]);

if(!$o_main->db->table_exists($options[3]))
{
	$b_table_created = $o_main->db->simple_query('CREATE TABLE '.$options[3].'(
		'.$options[4].' INT(11) NOT NULL,
		'.$options[5].' INT(11) NOT NULL,
		contentTable CHAR(100) NOT NULL,
		INDEX relation_idx ('.$options[4].', '.$options[5].', contentTable)
	)');
	if(!$b_table_created)
	{
		echo $formText_RelationTableIsNotCreated_Fieldtype;
		return;
	}
}
if($access >= 10 && $field[10] != 1 && $field[9] != 1)
{
	?><div <?php echo $field_attributes;?> id="<?php echo $field_ui_id;?>"><?php
}

$tablename = ($basetable->multilanguage == 1 ? substr($basetable->name,0,-7) : $basetable->name);
if($o_main->db->table_exists($options[0].'content') && $o_main->db->field_exists($options[2], $options[0].'content'))
{
	$s_sql = 'SELECT c.'.$options[1].' col0, cc.'.$options[2].' col1, r.'.$options[4].' col2, c.content_status FROM '.$options[0].' c JOIN '.$options[0].'content cc ON cc.'.$options[0].'ID = c.id AND cc.languageID = '.$o_main->db->escape($s_default_output_language).' LEFT JOIN '.$options[3].' r on c.'.$options[1].' = r.'.$options[5].' AND r.'.$options[4].' = '.$o_main->db->escape($ID).' AND r.contentTable = '.$o_main->db->escape($tablename);
} else {
	$s_sql = 'SELECT c.'.$options[1].' col0, c.'.$options[2].' col1, r.'.$options[4].' col2, c.content_status FROM '.$options[0].' c LEFT JOIN '.$options[3].' r ON c.'.$options[1].' = r.'.$options[5].' AND r.'.$options[4].' = '.$o_main->db->escape($ID).' AND r.contentTable = '.$o_main->db->escape($tablename);
}

$recursive = false;
$s_sql_where = " WHERE 1=1";
$v_param = array();
if(isset($options[6]) and strlen($options[6])>0)
{
	$recursive = true;
	if(isset($options[7]) and strlen($options[7])>0)
	{
		$v_param[] = explode(',', $options[7]);
		$s_sql_where .= ' AND c.'.$options[6].' IN ?';
	} else {
		$s_sql_where .= ' AND c.'.$options[6].' = 0';
	}
}
$s_sql_where .= " AND c.content_status < 2";
$o_query = $o_main->db->query($s_sql.$s_sql_where, $v_param);

if($o_query && $o_query->num_rows()>0)
{
	foreach($o_query->result_array() as $row)
	{
		if($field[9] == 1)
		{
			if($row['col2']>0)
			{
				?><input type="hidden" name="<?php echo $field[1].$ending;?>[]" value="<?php echo htmlspecialchars($row['col0']);?>"><?php
			}
			if($recursive) $x = print_checkboxes_from_external_table(1, $s_sql, $options, $row['col0'], $field, $ending, $access);
		} else {
			?><div>
				<input type="checkbox" name="<?php echo $field[1].$ending;?>[]" value="<?php echo htmlspecialchars($row['col0']);?>"<?php echo ($access<10||$field[10]==1?' readonly':'').($row['col2']>0?' checked':'');?>>
				<label><?php echo $row['col1'].($row['content_status']==1 ? " - [inactive]":"");?></label><?php
				if($recursive) $x = print_checkboxes_from_external_table(1, $s_sql, $options, $row['col0'], $field, $ending, $access);
			?></div><?php
		}
	}
}
if($access >= 10 && $field[10] != 1 && $field[9] != 1)
{
	?></div><?php
}

if($recursive && $access >= 10 && $field[10] != 1 && $field[9] != 1) { ?>
<script type="text/javascript">
<?php if(isset($ob_javascript)) { ob_start(); } ?>
$(function() {
	$('#<?php echo $field_ui_id;?> input').on('change',function() {
		if(this.checked) {
			$(this).parentsUntil('#<?php echo $field_ui_id;?>', 'div').children('input').prop('checked',true);
		}
	});
});
<?php if(isset($ob_javascript)) { $ob_javascript .= " ".ob_get_clean(); } ?>
</script>
<?php } ?>
<style>
#<?php echo $field_ui_id;?> { height:200px; overflow:auto; border:1px solid #CCC; list-style:none; padding:3px; margin:0px; line-height:20px; }
#<?php echo $field_ui_id;?> div label { width:90%; display:inline-block; }
#<?php echo $field_ui_id;?> div input[type="checkbox"] { width:20px !important; }
#<?php echo $field_ui_id;?> div.space-left { padding-left:30px; }
</style>