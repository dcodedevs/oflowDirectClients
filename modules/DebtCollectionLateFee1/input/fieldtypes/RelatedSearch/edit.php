<?php
//get single-language table
$s_single_table = ($basetable->multilanguage == 1 ? substr($basetable->name,0,-7) : $basetable->name);

$options = explode(":",$field[11]);
$options = array_map('trim',$options);
$options[0] = $o_main->db_escape_name($options[0]);
$options[1] = $o_main->db_escape_name($options[1]);
$options[2] = explode(",", $options[2]);
foreach($options[2] as $l_key => $s_value) $options[2][$l_key] = $o_main->db_escape_name($s_value);
$options[3] = $o_main->db_escape_name($options[3]);
$options[4] = $o_main->db_escape_name($options[4]);
$options[5] = $o_main->db_escape_name($options[5]);

$v_related_id = array();
if($options[6] == 1) $v_related_id = json_decode($field[6][$langID],true);

$s_sql_select = $s_sql_join = $s_sql_where = "";
if($o_main->multi_acc && $o_main->db->field_exists('account_id', $options[0]))
{
	$s_sql_where = " c.account_id = '".$o_main->db->escape_str($o_main->account_id)."'";
}
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
	$s_sql_select = ', r.'.$options[4];
	$s_sql_join = ' JOIN '.$options[3].' r on c.'.$options[1].' = r.'.$options[5].' and r.'.$options[4].' = '.$o_main->db->escape($ID).' and r.contentTable = '.$o_main->db->escape($s_single_table);
} else {
	
	$s_sql_where .= (''!=$s_sql_where?' AND':'').' c.'.$options[1].' IN('.implode(',', $v_related_id).')';
}
$s_sql_where .= (''!=$s_sql_where?' AND':'')." c.content_status < 2";

if($o_main->db->table_exists($options[0].'content'))
{
	$sql = "SELECT c.".$options[1]." cid, c.*, cc.*".$s_sql_select." FROM ".$options[0]." c LEFT OUTER JOIN ".$options[0]."content cc ON c.id = cc.".$options[0]."ID AND cc.languageID = ".$o_main->db->escape($s_default_output_language).$s_sql_join." WHERE ".$s_sql_where." ORDER BY ".($options[6] != 1 ? 'r':'c').".sortnr";
	
} else {
	$sql = "SELECT c.".$options[1]." cid, c.*".$s_sql_select." FROM ".$options[0]." c".$s_sql_join." WHERE ".$s_sql_where." ORDER BY ".($options[6] != 1 ? 'r':'c').".sortnr";
}
$o_query = $o_main->db->query($sql);
?><script type="text/javascript">
<?php if(isset($ob_javascript)) { ob_start(); } ?>
var timeout_<?php echo $field_ui_id;?>;
$(function(){
	$('#<?php echo $field_ui_id;?>modal').on('show.bs.modal', function (e){
		$('#<?php echo $field_ui_id;?>search').val('');
		load_<?php echo $field_ui_id;?>();
	}).on('shown.bs.modal', function () {
		$('#<?php echo $field_ui_id;?>search').focus();
	});
	$('#<?php echo $field_ui_id;?>search').on('keyup', function(e) {
		clearTimeout(timeout_<?php echo $field_ui_id;?>);
		timeout_<?php echo $field_ui_id;?> = setTimeout(load_<?php echo $field_ui_id;?>,500);
	});
	<?php if($field[10] != 1 and $access >= 10) { ?>
	$("#<?php echo $field_ui_id; ?>_items").sortable();
	<?php } ?>
});
function show_modal_<?php echo $field_ui_id;?>(_this)
{
	$('.<?php echo $field_ui_id;?>_item').removeClass('lookup');
	$(_this).parent().addClass('lookup');
	$('#<?php echo $field_ui_id;?>modal').modal('show');
}
function load_<?php echo $field_ui_id;?>(_page, _search)
{
	$('#<?php echo $field_ui_id;?>modal .modal-body .data-result').append('<div id="<?php echo $field_ui_id;?>_loader"></div>');
	if(typeof _search === 'undefined') _search = $('#<?php echo $field_ui_id;?>search').val();
	if(typeof _page === 'undefined') _page = 0;
	$.ajax({
		type: 'POST',
		url: '<?php echo $extradir."/input/fieldtypes/".$field[4]."/ajax_getData.php?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID'];?>',
		cache: false,
		data: {
			field_ui_id: '<?php echo $field_ui_id;?>',
			settings: '<?php echo $field[11];?>',
			id: '<?php echo $ID;?>',
			s_single_table: '<?php echo $s_single_table;?>',
			access: '<?php echo $access;?>',
			choosenListInputLang: '<?php echo $choosenListInputLang;?>',
			s_default_output_language: '<?php echo $s_default_output_language;?>',
			<?php if(sizeof($dataID)>2) print "'".$dataID[3]."': $('#".$fieldsStructure[$dataID[2]]['ui_id'.$ending]."').val(),"; ?>
			data_page: _page,
			data_search: _search
		},
		success: function(data) {
			$('#<?php echo $field_ui_id;?>modal .modal-body .data-result').html(data);
		}
	});
	return false;
}
function change_<?php echo $field_ui_id;?>(_this)
{
	var id = $(_this).data('id');
	$('.<?php echo $field_ui_id;?>_item.lookup input.input-id').val(id);
	$('.<?php echo $field_ui_id;?>_item.lookup span.field-id').text(id);
	<?php
	foreach($options[2] as $s_item)
	{
		$s_item = strtolower($s_item);
		?>$('.<?php echo $field_ui_id;?>_item.lookup span.field-<?php echo $s_item;?>').text($(_this).data('<?php echo $s_item;?>'));<?php
	}
	?>
	
	$('#<?php echo $field_ui_id;?>modal').modal('hide');
	
	//trigger changed event
	if(typeof changed_<?php echo $field_ui_id;?>=='function')
	{
		changed_<?php echo $field_ui_id;?>(id);
	}
}
function addForm<?php echo $field_ui_id;?>()
{
	$("#<?php echo $field_ui_id;?>_items").append('<div class="<?php echo $field_ui_id;?>_item"><label>ID:</label><input type="hidden" name="<?php echo $field[1].$ending;?>[]" class="input-id"/><span class="field-id"></span><a class="find script" href="javascript:;" onClick="show_modal_<?php echo $field_ui_id;?>(this);"><span class="glyphicon glyphicon-search" aria-hidden="true"></span></a><?php
	for($x=7;$x<count($options);$x++)
	{
		?><label class="extra-input"><?php echo $options[$x]; ?>:</label><input type="text" name="<?php echo $field[1]."_".$options[$x].$ending;?>[]"><?php 
	}
	foreach($options[2] as $s_item)
	{
		$s_item = strtolower($s_item);
		?><span class="field field-<?php echo $s_item;?>"></span><?php
	}
	?><a class="remove_<?php echo $field_ui_id;?> script" href="javascript:;" onclick="removeForm<?php echo $field_ui_id;?>(this); return false;"><span class="glyphicon glyphicon-trash" aria-hidden="true"></span></a></div>');
}

function removeForm<?php echo $field_ui_id;?>(_this)
{
	bootbox.confirm({
		message:"<?php echo $formText_DeleteItem_input;?>: " + $(_this).parent().find('span.field-id').text() + "?",
		buttons:{confirm:{label:"<?php echo $formText_Yes_input;?>"},cancel:{label:"<?php echo $formText_No_input;?>"}},
		callback: function(result){
			if(result)
			{
				$(_this).parent().remove();
			}
		}
	});
}
<?php if(isset($ob_javascript)) { $ob_javascript .= " ".ob_get_clean(); } ?>
</script>

<div class="<?php echo ($field[9]==1?"hide":""); ?>"><?php
if($field[10] != 1 and $access >= 10)
{
	?><a class="add_<?php echo $field_ui_id;?> script" href="javascript:addForm<?php echo $field_ui_id;?>();"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> <?php echo $formText_Add_RelatedSearch;?></a><?php
}
?></div>
<?php
if($field[10] != 1 and $access >= 10)
{
	?><div class="modal fade" id="<?php echo $field_ui_id;?>modal" tabindex="-1" role="dialog" aria-labelledby="<?php echo $field_ui_id;?>modal" aria-hidden="true">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="<?php echo $formText_Close_fieldtype;?>"><span aria-hidden="true">&times;</span></button>
					<div class="modal-title">
					<table width="100%" border="0" cellpadding="0" cellspacing="0">
					<tr>
						<td><input id="<?php echo $field_ui_id;?>search" type="text" class="form-control input-sm" placeholder="<?php echo $formText_Search_fieldtype;?>"></td>
						<td><button type="button" class="btn btn-default btn-sm" onClick="load_<?php echo $field_ui_id;?>();"><?php echo $formText_Search_fieldtype;?></button></td>
					</tr>
					</table>
					</div>
				</div>
				<div class="modal-body">
					
					<div class="data-result"></div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $formText_Cancel_fieldtype;?></button>
				</div>
			</div>
		</div>

	</div><?php
}
?>
<div id="<?php echo $field_ui_id; ?>_items" class="list-group ui-sortable<?php echo ($field[9]==1?" hide":""); ?>">
<?php
$i=0;
if($o_query && $o_query->num_rows()>0)
foreach($o_query->result_array() as $v_row)
{
	
	?><div class="<?php echo $field_ui_id;?>_item list-group-item"><label>ID:</label><input type="hidden" name="<?php echo $field[1].$ending;?>[]" class="input-id" value="<?php echo $v_row['cid'];?>"/><span class="field-id"><?php echo $v_row['cid'];?></span><?php
	if($field[10] != 1 and $access >= 10)
	{
		?><a class="find script" href="javascript:;" onClick="show_modal_<?php echo $field_ui_id;?>(this);"><span class="glyphicon glyphicon-search" aria-hidden="true"></span></a><?php
	}
	$s_sql = "SELECT * FROM ".$options[3]." WHERE ".$options[5]." = ? AND ".$options[4]." = ?";
	$o_find = $o_main->db->query($s_sql, array($v_row['cid'], $_GET['ID']));
	$v_extra_value = $o_find ? $o_find->row_array() : array();
	for($x=7;$x<count($options);$x++)
	{
		?><label class="extra-input"><?php echo $options[$x]; ?>:</label><input type="text" name="<?php echo $field[1]."_".$options[$x].$ending;?>[]" value="<?php print $v_extra_value[$options[$x]];?>"<?php if($field[10] == 1 || $access < 10) echo " readonly";?>><?php 
	}
	foreach($options[2] as $s_item)
	{
		?><span class="field field-<?php echo strtolower($s_item);?>"><?php echo $v_row[$s_item];?></span><?php
	}
	if($field[10] != 1 and $access >= 10)
	{
		?><a class="remove_<?php echo $field_ui_id;?> script" href="javascript:;" onclick="removeForm<?php echo $field_ui_id;?>(this); return false;"><span class="glyphicon glyphicon-trash" aria-hidden="true"></span></a><?php
	}
	?></div><?php
	$i++;
}

if($i == 0 and $field[10] != 1 and $access >= 10)
{
	?><div class="<?php echo $field_ui_id;?>_item"><label>ID:</label><input type="hidden" name="<?php echo $field[1].$ending;?>[]" class="input-id"/><span class="field-id"></span><a class="find script" href="javascript:;" onClick="show_modal_<?php echo $field_ui_id;?>(this);"><span class="glyphicon glyphicon-search" aria-hidden="true"></span></a><?php
	for($x=7;$x<count($options);$x++)
	{
		?><label class="extra-input"><?php echo $options[$x]; ?>:</label><input type="text" name="<?php echo $field[1]."_".$options[$x].$ending;?>[]"><?php 
	}
	foreach($options[2] as $s_item)
	{
		$s_item = strtolower($s_item);
		?><span class="field field-<?php echo $s_item;?>"></span><?php
	}
	?><a class="remove_<?php echo $field_ui_id;?> script" href="javascript:;" onclick="removeForm<?php echo $field_ui_id;?>(this); return false;"><span class="glyphicon glyphicon-trash" aria-hidden="true"></span></a></div><?php
}

?></div>
<style type="text/css">
#<?php echo $field_ui_id;?>_items { padding:5px; background-color:#CCC;}
.<?php echo $field_ui_id;?>_item { margin:2px; padding:3px; background-color:#FFFFFF; }
.<?php echo $field_ui_id;?>_item span.field { margin-left:20px; }
.<?php echo $field_ui_id;?>_item .find { margin-left:5px; margin-right:5px; }
.<?php echo $field_ui_id;?>_item .extra-input { margin-left:20px; margin-right:5px; }
.<?php echo $field_ui_id;?>_item input { width:<?php echo intval(40 / (count($options)-7));?>%; max-width:30%; }
.remove_<?php echo $field_ui_id;?> { float:right; padding-top:3px; padding-right:5px; }
.add_<?php echo $field_ui_id;?> { font-weight:bold; text-decoration:none; }
.add_<?php echo $field_ui_id;?> img { vertical-align: middle;}
#<?php echo $field_ui_id;?>_loader{position:absolute;top:0;width:90%;height:64px;background-image:url("<?php echo $extradir;?>/input/fieldtypes/<?php echo $field[4];?>/images/ajax-loader.gif");background-repeat:no-repeat; background-position:center center;}
</style>