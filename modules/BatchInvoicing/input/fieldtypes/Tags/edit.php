<?php
if(!function_exists("tags_get_parents")) include(__DIR__."/fn_tags_get_parents.php");
if(!function_exists("tags_print_tree")) include(__DIR__."/fn_tags_print_tree.php");

if(!$o_main->db->table_exists('sys_tag'))
{
	$o_main->db->simple_query("CREATE TABLE sys_tag (
		`id` INT(11) NOT NULL AUTO_INCREMENT,
		`moduleID` INT(11) NULL DEFAULT NULL,
		`createdBy` CHAR(255) NULL DEFAULT NULL,
		`created` DATETIME NULL DEFAULT NULL,
		`updatedBy` CHAR(255) NULL DEFAULT NULL,
		`updated` DATETIME NULL DEFAULT NULL,
		`sortnr` INT(11) NULL DEFAULT NULL,
		`parentID` INT(11) NULL DEFAULT NULL,
		`setID` TINYINT(4) NULL,
		`name` TEXT NULL,
		`type` TINYINT(4) NULL,
		PRIMARY KEY (`id`),
		INDEX parentIdx (parentID)
	)");
	$o_main->db->simple_query("CREATE TABLE `sys_tagcontent` (
		`id` INT(11) NOT NULL AUTO_INCREMENT,
		`sys_tagID` INT(11) NULL DEFAULT NULL,
		`languageID` CHAR(15) NULL DEFAULT NULL,
		`tagname` TEXT NULL,
		PRIMARY KEY (`id`),
		INDEX relIdx (sys_tagID)
	)");
	$o_main->db->simple_query("CREATE TABLE `sys_tagrelation` (
		`tagID` INT(11) NULL DEFAULT NULL,
		`contentID` INT(11) NULL DEFAULT NULL,
		`contentTable` CHAR(255) NULL,
		`sortnr` INT(11) NULL,
		INDEX relIdx (tagID, contentID, contentTable)
	)");
}
$setID = 0;
if($field[11]!="") $setID = $field[11];

$v_output_languages = array();
$o_query = $o_main->db->query('SELECT languageID, name FROM language WHERE outputlanguage = 1 ORDER BY defaultOutputlanguage DESC, sortnr ASC');
if($o_query && $o_query->num_rows()>0)
foreach($o_query->result_array() as $row)
{
	$v_output_languages[$row['languageID']] = $row['name'];
}
if($access >= 10)
{
	?><script type="text/javascript">
	<?php if(isset($ob_javascript)) { ob_start(); } ?>
	var _container, _tagid, _tagname, _tagdeep, _tagparent, _taglangID, _item_ids;
	$(function () {
		_container = $("#items_<?php echo $field_ui_id;?>");
		_item_ids = $("#items_ids_<?php echo $field_ui_id;?>");
		
		//$("#items_<?php echo $field_ui_id;?>").sortable();
		
		/*$("#<?php echo $field_ui_id;?>_tagname").keypress(function(e) {
			if(e.which == 13) {
				addFormElement_<?php echo $field_ui_id;?>()
				return false;
			}
		});*/
		
		$("a#show_tag_popup_<?php echo $field_ui_id;?>").fancybox({'mouseWheel' : false});
		$("a#show_add_popup_<?php echo $field_ui_id;?>").fancybox({'mouseWheel' : false});
		
		bind_<?php echo $field_ui_id;?>();
	});
	
	function bind_<?php echo $field_ui_id;?>()
	{
		$(".tag_popup_item_pick_<?php echo $field_ui_id;?>").on('click', function() {
			$.fancybox.showLoading();
			var id = parseInt($(_item_ids).val())+1;
			var _data = { tagid : $(this).find("input.tagid").val(), setID: '<?php echo $setID;?>', s_default_output_language: '<?php echo $s_default_output_language;?>' };
			
			$.ajax({
				type: 'POST',
				dataType: 'json',
				url: '<?php echo $extradir;?>/input/fieldtypes/<?php echo $field[4];?>/ajax_add_tag.php',
				cache: false,
				data: _data,
				success: function(obj) {
					if(obj.id) {
						var div = $('<div class="item_<?php echo $field_ui_id;?>" id="item_<?php echo $field_ui_id;?>_'+id+'">'+obj.name+'</div>');
						$('<input type="hidden" name="<?php echo $field[1].$ending;?>_tagID[]" value="'+obj.id+'" /><a class="remove_<?php echo $field_ui_id;?> script" onClick="remove_<?php echo $field_ui_id;?>(\'#item_<?php echo $field_ui_id;?>_'+id+'\'); return false;" href="javascript:;" title="<?php echo $formText_delete_fieldtype;?>"><img border="0" src="<?php echo $extradir;?>/input/fieldtypes/<?php echo $field[4];?>/images/cross.png"/></a><span class="clear_both"></span>').appendTo(div);
						$(_container).append(div);
						$(_item_ids).val(id);
					}
					$.fancybox.hideLoading();
					$.fancybox.close();
					$(window).resize();
				}
			});
			
			return false;
		});
		
		$("a.tag_popup_item_add_<?php echo $field_ui_id;?>").unbind('click').on('click', function() {
			$("#<?php echo $field_ui_id;?>_tagid").val($(this).prev().find("input.tagid").val());
			$("a#show_add_popup_<?php echo $field_ui_id;?>").trigger('click');
			return false;
		});
		$("a.tag_popup_item_rename_<?php echo $field_ui_id;?>").unbind('click').on('click', function() {
			$.fancybox.showLoading();
			var _data = {
				className: '<?php echo $field_ui_id;?>',
				selectedtagid: $(this).parent().find("input.tagid").val(),
				s_default_output_language: '<?php echo $s_default_output_language;?>',
				label_RenameTitle: '<?php echo $formText_RenameTag_fieldtype;?>',
				label_RenameButton: '<?php echo $formText_rename_fieldtype;?>',
				label_CancelButton: '<?php echo $formText_Cancel_fieldtype;?>',
				label_Tagname: '<?php echo $formText_tagname_fieldtype;?>'
			};
			$.ajax({
				type: 'GET',
				url: '<?php echo $extradir;?>/input/fieldtypes/<?php echo $field[4];?>/ajax_rename_tag.php',
				cache: false,
				data: _data,
				success: function(data) {
					$.fancybox(data);
				}
			});
			return false;
		});
		$("a.tag_popup_item_move_<?php echo $field_ui_id;?>").unbind('click').on('click', function() {
			$.fancybox.showLoading();
			var _data = {
				className: '<?php echo $field_ui_id;?>',
				selectedtagid: $(this).parent().find("input.tagid").val(),
				s_default_output_language: '<?php echo $s_default_output_language;?>'
			};
			$.ajax({
				type: 'GET',
				url: '<?php echo $extradir;?>/input/fieldtypes/<?php echo $field[4];?>/ajax_move_tag.php',
				cache: false,
				data: _data,
				success: function(data) {
					$.fancybox('<h3><?php echo $formText_ChooseTagUnderWhichMovePickedTag_fieldtype;?></h3>' + data + '<div class="<?php echo $field_ui_id;?>_btn"><a class="<?php echo $field_ui_id;?>_btn bold script" onClick="open_tag_popup_<?php echo $field_ui_id;?>();" href="javascript:;"><?php echo $formText_Cancel_fieldtype;?></a></div>');
				}
			});
			return false;
		});
		$("a.tag_popup_item_merge_<?php echo $field_ui_id;?>").unbind('click').on('click', function() {
			$.fancybox.showLoading();
			var _data = {
				className: '<?php echo $field_ui_id;?>',
				selectedtagid: $(this).parent().find("input.tagid").val(),
				s_default_output_language: '<?php echo $s_default_output_language;?>'
			};
			$.ajax({
				type: 'GET',
				url: '<?php echo $extradir;?>/input/fieldtypes/<?php echo $field[4];?>/ajax_merge_tag.php',
				cache: false,
				data: _data,
				success: function(data) {
					$.fancybox('<h3><?php echo $formText_ChooseTagToWhichMovePickedTag_fieldtype;?></h3>' + data + '<div class="<?php echo $field_ui_id;?>_btn"><a class="<?php echo $field_ui_id;?>_btn bold script" onClick="open_tag_popup_<?php echo $field_ui_id;?>();" href="javascript:;"><?php echo $formText_Cancel_fieldtype;?></a></div>');
				}
			});
			return false;
		});
		$("a.tag_popup_item_delete_<?php echo $field_ui_id;?>").unbind('click').on('click', function() {
			$.fancybox.showLoading();
			var _data = {
				className: '<?php echo $field_ui_id;?>',
				selectedtagid: $(this).parent().find("input.tagid").val(),
				label_confirm: '<?php echo $formText_AreYouSureYouWantToDeleteThisTag_fieldtype;?>?',
				label_errorusing: '<?php echo $formText_SomeContentIsUsingThisTagCannotBeDeletedYouCanMergeInstead_fieldtype;?>',
				label_errorparent: '<?php echo $formText_ParentTagCannotBeDeletedDeleteChildsFirst_fieldtype;?>',
				label_YesButton: '<?php echo $formText_Yes_fieldtype;?>',
				label_NoButton: '<?php echo $formText_No_fieldtype;?>',
				label_OkButton: '<?php echo $formText_Ok_fieldtype;?>'
			};
			
			$.ajax({
				type: 'GET',
				url: '<?php echo $extradir;?>/input/fieldtypes/<?php echo $field[4];?>/ajax_delete_tag.php',
				cache: false,
				data: _data,
				success: function(data) {
					$.fancybox(data);
				}
			});
			return false;
		});
	}
	
	function addFormElement_<?php echo $field_ui_id;?>()
	{
		var id = parseInt($(_item_ids).val())+1;
		var _data = { add: 1, tagparent : $("#<?php echo $field_ui_id;?>_tagid").val(), setID: '<?php echo $setID;?>', s_default_output_language: '<?php echo $s_default_output_language;?>'<?php foreach($v_output_languages as $key => $name) { print ', tagname_'.$key.': $("#'.$field_ui_id.'_tagname_'.$key.'").val()'; } ?> };
		
		$.fancybox.showLoading();
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: '<?php echo $extradir;?>/input/fieldtypes/<?php echo $field[4];?>/ajax_add_tag.php',
			cache: false,
			data: _data,
			success: function(obj) {
				if(obj.id) {
					var div = $('<div class="item_<?php echo $field_ui_id;?>" id="item_<?php echo $field_ui_id;?>_'+id+'">'+obj.name+'</div>');
					$('<input type="hidden" name="<?php echo $field[1].$ending;?>_tagID[]" value="'+obj.id+'" /><a class="remove_<?php echo $field_ui_id;?> script" onClick="$(this).parent().remove();" href="javascript:;" title="<?php echo $formText_delete_fieldtype;?>"><img border="0" src="<?php echo $extradir;?>/input/fieldtypes/<?php echo $field[4];?>/images/cross.png"/></a><span class="clear_both"></span>').appendTo(div);
					$(_container).append(div);
					$(_item_ids).val(id);
					
					update_tag_tree_<?php echo $field_ui_id;?>();
					<?php foreach($v_output_languages as $key => $name) { ?>
					$("#<?php echo $field_ui_id.'_tagname_'.$key;?>").val('');
					<?php } ?>
				}
				$.fancybox.hideLoading();
				$.fancybox.close();
			}
		});
	}
	
	function do_action_<?php echo $field_ui_id;?>(_this, _action)
	{
		var _data = { action: _action, selectedtagid: $(_this).find("input.selectedtagid").val(), s_default_output_language: '<?php echo $s_default_output_language;?>' };
		if(_action == 'move' || _action == 'merge') {
			_data['undertagid'] = $(_this).find("input.tagid").val();
		}
		if(_action == 'rename') {
			<?php foreach($v_output_languages as $key => $name) { ?>
			_data['tagname_<?php echo $key;?>'] = $(_this).parent().parent().find("input.<?php echo $field_ui_id;?>_tagname_<?php echo $key;?>").val();
			<?php } ?>
		}
		
		$.ajax({
			type: 'POST',
			url: '<?php echo $extradir;?>/input/fieldtypes/<?php echo $field[4];?>/ajax_' + _action + '_tag.php',
			cache: false,
			data: _data,
			success: function(data) {
				update_tag_tree_<?php echo $field_ui_id;?>(1);
			}
		});
	}
	
	function update_tag_tree_<?php echo $field_ui_id;?>(_showList)
	{
		$.get("<?php echo $extradir;?>/input/fieldtypes/<?php echo $field[4];?>/ajax_update_tree.php", {
			className: '<?php echo $field_ui_id;?>',
			addButton: '<?php echo $formText_addChild_fieldtype;?>',
			renameButton: '<?php echo $formText_rename_fieldtype;?>',
			moveButton: '<?php echo $formText_move_fieldtype;?>',
			mergeButton: '<?php echo $formText_merge_fieldtype;?>',
			deleteButton: '<?php echo $formText_delete_fieldtype;?>',
			s_default_output_language: '<?php echo $s_default_output_language;?>',
			"_": $.now()
		}).done(function(data) {
			$(tag_tree_<?php echo $field_ui_id;?>).empty().append(data);
			bind_<?php echo $field_ui_id;?>();
			
			if(_showList == 1) $("a#show_tag_popup_<?php echo $field_ui_id;?>").trigger('click');
		});
	}
	function open_tag_popup_<?php echo $field_ui_id;?>()
	{
		$("a#show_tag_popup_<?php echo $field_ui_id;?>").trigger('click');
	}
	<?php if(isset($ob_javascript)) { $ob_javascript .= " ".ob_get_clean(); } ?>
	</script><?php
}
?><div class="<?php echo ($field[9]==1?"hide":"");?>"><?php
if($field[10]!=1 and $access >= 10)
{
	?><span style="padding-left:10px;"><a id="show_tag_popup_<?php echo $field_ui_id;?>" class="add_item script" href="#tag_popup_<?php echo $field_ui_id;?>"><img border="0" src="<?php echo $extradir;?>/input/fieldtypes/<?php echo $field[4];?>/images/add.gif" /> <?php echo $formText_Add_Types;?></a></span><?php
}
?></div>
<div class="tags_<?php echo $field_ui_id.($field[9]==1?" hide":"");?>">
	<div id="items_<?php echo $field_ui_id;?>">
	<?php
	$i=0;
	$o_query = $o_main->db->query("select t.id, t.name, t.parentID from sys_tag t join sys_tagrelation tr on tr.tagID = t.id and tr.contentID = ? and tr.contentTable = ? group by t.id order by tr.sortnr", array($ID, $field[3]));
	if($o_query && $o_query->num_rows()>0)
	foreach($o_query->result_array() as $obj)
	{
		$parents = tags_get_parents($obj['parentID'], $s_default_output_language);
		?>
		<div id="item_<?php echo $field_ui_id.'_'.$i;?>" class="item_<?php echo $field_ui_id;?>"><?php echo ($parents==""?'':$parents.': ').$obj['name'];?><?php if($access >= 10) { ?><input type="hidden" value="<?php echo $obj['id'];?>" name="<?php echo $field[1].$ending;?>_tagID[]"><a class="remove_<?php echo $field_ui_id;?> script" onClick="$(this).parent().remove();" href="javascript:;" title="<?php echo $formText_delete_fieldtype;?>"><img border="0" src="<?php echo $extradir;?>/input/fieldtypes/<?php echo $field[4];?>/images/cross.png" /></a><?php } ?><span class="clear_both"></span></div>
		<?php
		$i++;
	}
	?></div><?php
	if($access >= 10)
	{
		?><div style="display:none;">
			<div id="tag_popup_<?php echo $field_ui_id;?>">
				<h2><?php echo $formText_chooseTag_fieldtype;?></h2>
				<div class="tag_popup_base_<?php echo $field_ui_id;?>">
					<a class="script" href="javascript:;">
						<input type="hidden" class="tagid" value="0" />
						<input type="hidden" class="tagparent" value="0" />
					</a>
					<a class="tag_cmd tag_popup_item_add_<?php echo $field_ui_id;?> script"><?php echo $formText_addBaseTag_fieldtype;?></a>
				</div>
				<div id="tag_tree_<?php echo $field_ui_id;?>">
				<?php tags_print_tree(0,0,$field_ui_id, $formText_addChild_fieldtype, $formText_rename_fieldtype, $formText_move_fieldtype, $formText_merge_fieldtype, $formText_delete_fieldtype, $s_default_output_language); ?>
				</div>
				<div class="clear_both"></div>
			</div>
			<a id="show_add_popup_<?php echo $field_ui_id;?>" class="script" href="#new_item_form_<?php echo $field_ui_id;?>"></a>
			<div id="new_item_form_<?php echo $field_ui_id;?>" class="new_item_form">
				<div class="tag_popup_title_<?php echo $field_ui_id;?>"><?php echo $formText_enterNewTagName_fieldtype;?></div>
				<div class="tag_popup_item_<?php echo $field_ui_id;?>">
					<input type="hidden" id="<?php echo $field_ui_id;?>_tagid" name="<?php echo $field[1].$ending;?>_tagid" value="0">
					<?php
					foreach($v_output_languages as $key => $name)
					{
						?><div class="<?php echo $field_ui_id;?>_row"><span><?php echo $formText_tagname_fieldtype." ({$name}): ";?></span><input type="text" id="<?php echo $field_ui_id.'_tagname_'.$key;?>" name="<?php echo $field[1].$ending.'_tagname_'.$key;?>" value="" /></div><?php
					}
					?>
					<div class="<?php echo $field_ui_id;?>_btn"><a class="<?php echo $field_ui_id;?>_btn bold script" href="javascript:;" onClick="addFormElement_<?php echo $field_ui_id;?>(); return false;"><?php echo $formText_Add_fieldtype;?></a>&nbsp;&nbsp;&nbsp;&nbsp;<a class="<?php echo $field_ui_id;?>_btn bold script" href="javascript:;" onClick="open_tag_popup_<?php echo $field_ui_id;?>();"><?php echo $formText_Cancel_fieldtype;?></a></div>
				</div>
				<div class="clear_both"></div>
			</div>
		</div><?php
	}
	?>
</div>
<input type="hidden" id="items_ids_<?php echo $field_ui_id;?>" value="<?php echo $i;?>" />
<style>
.tags_<?php echo $field_ui_id;?> { padding:5px 8px; background-color:#CCC;}
#items_<?php echo $field_ui_id;?> div { margin:3px 0; padding-left:3px; padding-right:3px; line-height:20px; background-color:#FFFFFF; }
/*.edit_<?php echo $field_ui_id;?> { padding:5px; background-color:#FFFFFF; min-width:400px; }*/
.tag_popup_title_<?php echo $field_ui_id;?> { font-weight:bold; font-size:12px; margin:10px 0; }
div.<?php echo $field_ui_id;?>_row { margin-top:5px; }
div.<?php echo $field_ui_id;?>_row span { display:inline-block; width:40%; }
div.<?php echo $field_ui_id;?>_row input { width:59%; }
div.<?php echo $field_ui_id;?>_btn { margin:10px 0; text-align:center; }

.ui-menu li.ui-menu-item a { padding:0.3em 0.4em; line-height:1; }
.ui-menu li.ui-menu-item div { font-size:10px; font-style:italic; padding:0; }
.tag_popup_item_<?php echo $field_ui_id;?> a { color:#444444; text-decoration:none; }
.tag_popup_item_<?php echo $field_ui_id;?>, .tag_popup_base_<?php echo $field_ui_id;?> { cursor:pointer; font-size:13px; margin:1px 0; min-width:400px; }
.tag_popup_base_<?php echo $field_ui_id;?> { margin-bottom:5px; }
a.<?php echo $field_ui_id;?>_btn { padding:2px 6px; font-size:11px; color:#6eb558; text-decoration:none; background-color:#f3f3f3; border-radius:2px; }
a.<?php echo $field_ui_id;?>_btn:hover { background-color:#aaaaaa; color:#ffffff; }
a.<?php echo $field_ui_id;?>_btn.bold { font-weight:bold; }
.remove_<?php echo $field_ui_id;?> { float:right; padding-right:5px; padding-top:3px; }
</style>