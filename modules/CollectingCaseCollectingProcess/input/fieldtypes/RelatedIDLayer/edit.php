<?php
$exex = explode("(::)",$field[11]);
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
?>
<script type="text/javascript">
<?php if(isset($ob_javascript)) { ob_start(); } ?>
var timeout_<?php echo $field_ui_id;?>;
$(function () {
	$('#<?php echo $field_ui_id;?>modal').on('show.bs.modal', function () {
		$('#<?php echo $field_ui_id;?>search').val('');
		load_<?php echo $field_ui_id;?>();
	}).on('shown.bs.modal', function () {
		$('#<?php echo $field_ui_id;?>search').focus();
	});
	$('#<?php echo $field_ui_id;?>search').on('keyup', function(e) {
		clearTimeout(timeout_<?php echo $field_ui_id;?>);
		timeout_<?php echo $field_ui_id;?> = setTimeout(load_<?php echo $field_ui_id;?>,500);
	});
});
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
	var base_url = '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&submodule=".$dataTable."&includefile=edit";?>';
	var id = $(_this).data('id');
	$("#<?php echo $field_ui_id;?>").val(id);
	$("#<?php echo $field_ui_id."_".$dataID[0];?>").text(id);
	<?php
	for($x=0;$x<sizeof($dVar);$x++)
	{
		if(strpos($dVisible[$x],'v')!==false) {
			?>$("#<?php echo $field_ui_id."_".$dVar[$x];?>").attr("href",base_url+$(_this).data('url')).text($(_this).data('<?php echo strtolower($dVar[$x]);?>'));<?php
		}
		if(strpos($dVisible[$x],'c')!==false) {
			?>
			if($("#<?php echo $fieldsStructure[$dLink[$x]]['ui_id'.$ending];?>").is('textarea')) {
				if(CKEDITOR.instances.<?php echo $fieldsStructure[$dLink[$x]]['ui_id'.$ending];?>) {
					editor = CKEDITOR.instances.<?php echo $fieldsStructure[$dLink[$x]]['ui_id'.$ending];?>;
					editor.setData($(_this).data('<?php echo strtolower($dVar[$x]);?>'));
				} else {
					$("#<?php echo $fieldsStructure[$dLink[$x]]['ui_id'.$ending];?>").text($(_this).data('<?php echo strtolower($dVar[$x]);?>'));
				}
			} else {
				$("#<?php echo $fieldsStructure[$dLink[$x]]['ui_id'.$ending];?>").val($(_this).data('<?php echo strtolower($dVar[$x]);?>'));
			}<?php
		}
		if(strpos($dVisible[$x],'i')!==false) {
			?>
			var img_counter = parseInt($('#<?php echo $fieldsStructure[$dLink[$x]]['ui_id'.$ending];?>counter').val());
			$.ajax({
				type: 'POST',
				url: '<?php echo $extradir."/input/fieldtypes/".$field[4]."/ajax_copyImage.php?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID'];?>',
				cache: false,
				data: {
					field_ui_id: '<?php echo $field_ui_id;?>',
					settings: '<?php echo implode('#',$dExtra[$x]);?>',
					access: '<?php echo $access;?>',
					choosenListInputLang: '<?php echo $choosenListInputLang;?>',
					s_default_output_language: '<?php echo $s_default_output_language;?>',
					value: $(_this).data('<?php echo strtolower($dVar[$x]);?>'),
					extraimagedir: '<?php echo (isset($editordir) ? $editordir."/" : $extraimagedir);?>',
					extradir: '<?php echo $extradir;?>',
					img_counter: img_counter,
					image_fieldname: '<?php echo $field[3].$dLink[$x];?>',
					extradomaindirroot: '<?php echo $extradomaindirroot;?>'
				},
				success: function(data) {
					$('#<?php echo $fieldsStructure[$dLink[$x]]['ui_id'.$ending];?>_files').append(data);
					$('#<?php echo $fieldsStructure[$dLink[$x]]['ui_id'.$ending];?>counter').val(img_counter+30);
				}
			});
			<?php
		}
	}
	?>
	$('#<?php echo $field_ui_id;?>modal').modal('hide');
	
	//trigger changed event
	if(typeof changed_<?php echo $field_ui_id;?>=='function')
	{
		changed_<?php echo $field_ui_id;?>(id);
	}
}
<?php if(isset($ob_javascript)) { $ob_javascript .= " ".ob_get_clean(); } ?>
</script>
<div style="display:none;"><div id="<?php echo $field_ui_id;?>_list" style="min-width:300px; line-height:20px;"></div></div><?php
if($o_main->db->table_exists($dataTable.'content'))
{
	$s_sql = "SELECT $dataTable.id cid, $dataTable.{$dataID[0]}, $dataTable.moduleID, ".implode(",",$sqlSelect)." FROM $dataTable LEFT OUTER JOIN {$dataTable}content ON {$dataTable}content.{$dataTable}ID = $dataTable.id AND {$dataTable}content.languageID = ".$o_main->db->escape($s_default_output_language)." WHERE $dataTable.$dataID[0] = ".$o_main->db->escape($field[6][$langID])." AND $dataTable.content_status < 2 order by ".implode(",",$sqlOrder).";";
} else {
	$s_sql = "SELECT $dataTable.id cid, $dataTable.{$dataID[0]}, $dataTable.moduleID, ".implode(",",$sqlSelect)." FROM $dataTable WHERE $dataTable.$dataID[0] = ".$o_main->db->escape($field[6][$langID])." AND $dataTable.content_status < 2 order by ".implode(",",$sqlOrder).";";
}
$o_query = $o_main->db->query($s_sql, $v_param);
if(!$o_query)
{
	 echo '<b style="color:red;">Incorrect settings</b><br/>';
	 return;
}
$v_row = $o_query->row_array();
$v_module = array();
$o_query = $o_main->db->query('select name from moduledata where uniqueiD = ?', array($v_row['moduleID']));
if($o_query && $o_query->num_rows()>0) $v_module = $o_query->row_array();
$value = array($v_row[$dataID[0]]);
for($x=0;$x<sizeof($dVar);$x++)
{
	if(strpos($dVisible[$x],'r')!==false)
	{
		$rRow = array();
		$s_sql = "SELECT ".$o_main->db_escape_name($dExtra[$x][2])." FROM ".$o_main->db_escape_name($dExtra[$x][0])." WHERE ".$o_main->db_escape_name($dExtra[$x][1])." = ".$o_main->db->escape($v_row[$dVar[$x]])." LIMIT 1";
		$o_find = $o_main->db->query($s_sql);
		if($o_find && $o_find->num_rows()>0) $rRow = $o_find->row_array();
		$v_row[$dVar[$x]] = $rRow[$dExtra[$x][2]];
	}
	if(strpos($dVisible[$x],'v')!==false) $value[$x+1] = $v_row[$dVar[$x]];
}
?>
<input <?php echo $field_attributes;?> id="<?php echo $field_ui_id;?>" type="hidden" name="<?php echo $field[1].$ending;?>" value="<?php echo htmlspecialchars($field[6][$langID]);?>" />
<span><b><?php echo ($dataID[1]!="" ? $dataID[1].": " : '');?></b></span><span id="<?php echo $field_ui_id."_".$dataID[0];?>"><?php echo $value[0];?></span> <?php
for($x=0;$x<sizeof($dVar);$x++)
{
	if(strpos($dVisible[$x],'v')!==false)
	{
		?><span><b><?php echo ($dLabel[$x]!="" ? $dLabel[$x].": ":"");?></b></span><a id="<?php echo $field_ui_id."_".$dVar[$x];?>" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$v_module['name']."&submodule=".$dataTable."&includefile=edit&ID=".$v_row['cid'];?>"><?php echo $value[$x+1];?></a> <?php
	}
}
if($field[10] != 1 and $access >= 10)
{
	?><button type="button" class="btn btn-default btn-xs" data-toggle="modal" data-target="#<?php echo $field_ui_id;?>modal" style="margin-left:20px;"><?php echo $formText_choose_fieldtype;?></button>
	<div class="modal fade" id="<?php echo $field_ui_id;?>modal" tabindex="-1" role="dialog" aria-labelledby="<?php echo $field_ui_id;?>modal" aria-hidden="true">
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
<style>#<?php echo $field_ui_id;?>_loader{position:absolute;top:0;width:90%;height:64px;background-image:url("<?php echo $extradir;?>/input/fieldtypes/<?php echo $field[4];?>/images/ajax-loader.gif");background-repeat:no-repeat; background-position:center center;}</style>