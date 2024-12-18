<?php
$l_rand = rand(1000,9999);
if(isset($_POST['save_module_set']))
{
	$return = $error_msg = array();
	$extradir = __DIR__.'/../';
	define('BASEPATH', realpath(__DIR__.'/../../../').DIRECTORY_SEPARATOR);
	require_once(BASEPATH.'elementsGlobal/cMain.php');
	include(__DIR__."/../input/includes/readInputLanguage.php");
	
	$s_set_name = trim($_POST['set_name']);
	//$s_set_users = trim($_POST['set_users']);
	$b_default_set = (isset($_POST['default_set']) ? 1 : 0);
	$b_hide_modules = (isset($_POST['hide_modules_not_in_set']) ? 1 : 0);
	if(empty($s_set_name)) $error_msg["error_".count($error_msg)] = $formText_SetNameIsEmpty_input;
	//if(empty($s_set_users)) $error_msg["error_".count($error_msg)] = $formText_SetUsersIsEmpty_input;
	
	if(count($error_msg) == 0)
	{
		if($_POST['set_id'] > 0)
		{
			$l_set_id = intval($_POST['set_id']);
			$s_sql = "update sys_modulemenuset set name = ?, default_set = ?, hide_modules_not_in_set = ?, updatedBy = ?, updated = NOW() where id = ?";
			$o_main->db->query($s_sql, array($s_set_name, $b_default_set, $b_hide_modules, $_COOKIE['username'], $l_set_id));
		} else {
			$s_sql = "insert into sys_modulemenuset(id, createdBy, created, name, default_set, hide_modules_not_in_set) values(NULL, ?, NOW(), ?, ?, ?)";
			$o_main->db->query($s_sql, array($_COOKIE['username'], $s_set_name, $b_default_set, $b_hide_modules));
			$l_set_id = $o_main->db->insert_id();
		}
		
		if($b_default_set==1) $o_main->db->query("delete from sys_modulemenuusers where set_id = ?", array($l_set_id));
		$o_main->db->query("delete mml.* from sys_modulemenulink mml join sys_modulemenugroup mmg on mmg.id = mml.group_id where mmg.set_id = ?", array($l_set_id));
		$o_main->db->query("delete mmgc.* from sys_modulemenugroupcontent mmgc join sys_modulemenugroup mmg on mmg.id = mmgc.sys_modulemenugroupID where mmg.set_id = ?", array($l_set_id));
		$o_main->db->query("delete from sys_modulemenugroup where set_id = ?", array($l_set_id));
		$b_do = $b_add_group = false;
		$l_group_id = $l_group_cnt = 0;
		$v_group_names = array();
		foreach($_POST as $s_key => $s_value)
		{
			if($s_key == 'structure_start') $b_do = true;
			
			if($b_do && strpos($s_key,'module_name_') !== false)
			{
				if($l_group_id == 0 || $b_add_group)
				{
					$o_main->db->query("insert into sys_modulemenugroup (id, sortnr, content_status, set_id, collapse) values(NULL, ?, 0, ?, ?)", array($s_idx, $l_set_id, $l_group_collapse));
					$l_group_id = $o_main->db->insert_id();
					$b_add_group = false;
					foreach($v_group_names as $s_lang => $s_group_name)
					{
						$o_main->db->query("insert into sys_modulemenugroupcontent (id, sys_modulemenugroupID, languageID, name) values(NULL, ?, ?, ?)", array($l_group_id, $s_lang, $s_group_name));
					}
				}
				$o_main->db->query("insert into sys_modulemenulink (id, content_status, group_id, module_name) values(NULL, 0, ?, ?)", array($l_group_id, $s_value));
				$l_group_cnt++;
			}
			if($b_do && strpos($s_key,'group_name_') !== false)
			{
				$l_group_cnt = 0;
				$b_add_group = true;
				list($s_group, $s_name, $s_idx, $s_lang) = explode('_', $s_key);
				$v_group_names[$s_lang] = $s_value;
				$l_group_collapse = $_POST['group_collapse_'.$s_idx];
			}
			if($b_do && strpos($s_key,'group_end_') !== false)
			{
				$v_group_names = array();
				$b_add_group = false;
				if($l_group_cnt > 0) $l_group_id = 0;
			}
			if($s_key == 'structure_end') break;
		}
	} else {
		$return["error"] = $error_msg;
	}
	
	$return["url"] = substr($_SERVER['HTTP_REFERER'],0,strpos($_SERVER['HTTP_REFERER'],"?"))."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$_GET['module']."&submodule=".$_GET['submodule']."&includefile=".$_GET['includefile'];
	print json_encode($return);
	exit;
}
if(isset($_GET['delete_set_id']))
{
	$l_set_id = intval($_GET['delete_set_id']);
	$o_main->db->query("delete mml.* from sys_modulemenulink mml join sys_modulemenugroup mmg on mmg.id = mml.group_id where mmg.set_id = ?", array($l_set_id));
	$o_main->db->query("delete from sys_modulemenugroup where set_id = ?", array($l_set_id));
	$o_main->db->query("delete from sys_modulemenuusers where set_id = ?", array($l_set_id));
	$o_main->db->query("delete from sys_modulemenuset where id = ?", array($l_set_id));
	unset($_GET['delete_set_id']);
}
$v_modules = $v_module_menu = array();
$o_query = $o_main->db->query('select name from moduledata order by modulemode, ordernr');
if($o_query && $o_query->num_rows()>0)
foreach($o_query->result_array() as $v_row)
{
	$v_modules[] = $v_row["name"];
}
$v_modules_free = $v_modules;
?>
<div class="module-manager">
<?php
if(isset($_GET["set_id"]))
{
	$v_data = array();
	$o_query = $o_main->db->query('select * from sys_modulemenuset where id = ?', array($_GET["set_id"]));
	if($o_query && $o_query->num_rows()>0) $v_data = $o_query->row_array();
	?>
	<form action="<?php echo $extradir."/addOn_include/".$_GET['includefile'].".php?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&submodule=".$submodule."&includefile=edit_module_menu";?>" method="post">
	<input type="hidden" name="save_module_set" value="1">
	<input type="hidden" name="set_id" value="<?php echo $_GET['set_id'];?>">
	<input type="hidden" name="choosenListInputLang" value="<?php echo $choosenListInputLang;?>">
	<div class="form-group">
		<label><?php echo $formText_Name_input;?></label>
		<input type="text" class="form-control" name="set_name" value="<?php echo $v_data["name"];?>">
	</div>
	<div class="checkbox">
		<label>
			<input type="checkbox" name="default_set"<?php echo ($v_data["default_set"]==1 ? ' checked' : '');?> value="1"> <?php echo $formText_DefaultSet_input;?>
		</label>
	</div>
	<div class="checkbox">
		<label>
			<input type="checkbox" name="hide_modules_not_in_set"<?php echo ($v_data["hide_modules_not_in_set"]==1 ? ' checked' : '');?> value="1"> <?php echo $formText_HideModulesNotInSet_input;?>
		</label>
	</div>
	<?php
	$l_count = 0;
	$o_query = $o_main->db->query('select * from sys_modulemenuusers where set_id = ?', array($v_data['id']));
	if($o_query) $l_count = $o_query->num_rows();
	?>
	<div class="panel panel-default">
		<div class="panel-heading" role="tab">
			<h4 class="panel-title"><a class="script" data-toggle="collapse" href="#set_users_container"><?php echo $formText_ConnectedUsers_input;?> <span class="badge"><?php echo $l_count;?></span></a></h4>
		</div>
		<div id="set_users_container" class="panel-collapse collapse">
			<ul class="list-group">
				<?php
				if($l_count>0)
				foreach($o_query->result_array() as $v_row)
				{
					?><li class="list-group-item"><?php echo $v_row["username"];?></li><?php
				}
				?>
			</ul>
		</div>
	</div>
	<h3>
		<?php echo $formText_GroupOrChangeModuleOrder_input;?>
		<button id="mm_add_group" class="btn btn-info btn-sm pull-right" type="button" role="button"><?php echo $formText_NewGroup_input;?></button>
	</h3>
	<input type="hidden" name="structure_start" value="1" />
	<ul id="mm_sort_modules" class="mm_dragdrop">
	<?php
	$s_sql = "SELECT languageID FROM language WHERE inputlanguage = 1 ORDER BY defaultInputlanguage DESC, sortnr ASC";
	$o_query = $o_main->db->query($s_sql);
	$v_languages = ($o_query ? $o_query->result_array() : array());

	$counter = 1;
	$o_query = $o_main->db->query('select * from sys_modulemenugroup where set_id = ? order by id', array($_GET["set_id"]));
	if($o_query && $o_query->num_rows()>0)
	foreach($o_query->result_array() as $v_row)
	{
		?><li class="ui-state-default is-group">
			<div class="title row">
				<div class="col-md-2"><?php echo $formText_GroupName_input;?>:</div>
				<div class="col-md-4"><?php
				foreach($v_languages as $v_language)
				{
					$o_find = $o_main->db->query("SELECT * FROM sys_modulemenugroupcontent WHERE sys_modulemenugroupID = '".$o_main->db->escape_str($v_row['id'])."' AND languageID = '".$o_main->db->escape_str($v_language['languageID'])."'");
					$v_content = $o_find ? $o_find->row_array() : array();
					?><div><input type="text" name="group_name_<?php echo $counter.'_'.$v_language['languageID'];?>" value="<?php echo $v_content['name'];?>"><?php if(count($v_languages)>0) { ?>&nbsp;<b>[<?php echo $v_language['languageID'];?>]</b><?php } ?></div><?php
				}
				?></div>
				<div class="col-md-2"><?php echo $formText_Collapse_input;?>:</div>
				<div class="col-md-2"><select name="group_collapse_<?php echo $counter;?>">
					<option value="1"><?php echo $formText_Yes_input;?></option>
					<option value="0"<?php echo ($v_row['collapse']==0?" selected":"");?>><?php echo $formText_No_input;?></option>
				</select></div>
				<div class="buttons col-md-2 text-right">
					<a class="btn btn-xs script btn_expand hide" href="#"><span class="glyphicon glyphicon-collapse-down"></span></a>
					<a class="btn btn-xs script btn_collapse" href="#"><span class="glyphicon glyphicon-collapse-up"></span></a>
					<a class="btn btn-xs script delete" href="#"><span class="glyphicon glyphicon-trash"></span></a>
				</div>
			</div>
			<ul class="mm_dragdrop sort_group bg-warning"><?php
			$o_query2 = $o_main->db->query('select * from sys_modulemenulink where group_id = ? order by id', array($v_row["id"]));
			if($o_query2 && $o_query2->num_rows()>0)
			foreach($o_query2->result_array() as $v_row2)
			{
				$l_id = array_search($v_row2["module_name"], $v_modules_free);
				if($l_id !== false) unset($v_modules_free[$l_id]);
				?><li class="ui-state-default is-module">
					<?php echo $v_row2["module_name"];?><input type="hidden" name="module_name_<?php echo $v_row2["module_name"];?>" value="<?php echo $v_row2["module_name"];?>" />
				</li><?php
			}
			?></ul>
			<input type="hidden" name="group_end_<?php echo $counter;?>" value="1" />
		</li><?php
		$counter++;
	}
	?>
	</ul>
	<input type="hidden" name="structure_end" value="1">
	<h3><?php echo $formText_UnlinkedModules_input;?></h3>
	<ul id="mm_free_modules" class="mm_dragdrop">
	<input type="hidden" name="free_starts" value="1"><?php
	foreach($v_modules_free as $s_module)
	{
		?><li class="ui-state-default is-module fw_clear_both">
			<?php echo $s_module;?><input type="hidden" name="module_name_<?php echo $s_module;?>" value="<?php echo $s_module;?>" />
		</li><?php
	}
	?>
	</ul>
	<div class="content_buttons">
		<input id="mm_save_form" class="btn btn-success btn-ms" type="button" value="<?php echo $formText_save_input;?>" />
		<a class="btn btn-default btn-ms optimize" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&submodule=".$submodule."&includefile=edit_module_menu";?>"><?php echo $formText_Cancel_input;?></a>
	</div>
	</form>
	<style>
	#mm_sort_modules, .sort_group, #mm_free_modules {
		border:1px solid #666;
		min-height:20px;
		list-style-type:none;
		margin:0;
		padding:10px 0 5px 0;
	}
	#mm_sort_modules, #mm_free_modules {
		border:3px dashed #666;
	}
	.sort_group.disable {
		background-color:#444;
	}
	#mm_sort_modules .title {
		margin-bottom:5px;
		font-weight:bold;
	}
	#mm_sort_modules .title, #mm_sort_modules .title a {
		color:#000000;
	}
	#mm_sort_modules li, #mm_free_modules li {
		margin:0 10px 10px 10px;
		padding:10px;
		cursor:move;
		font-weight:normal;
	}
	#mm_sort_modules li .buttons, #mm_free_modules li .buttons {
		float:right;
	}
	#mm_sort_modules li.is-module, #mm_free_modules li.is-module {
		margin:0 10px 5px 10px;
		padding:0px 5px;
	}
	#mm_sort_modules li.is-module:hover, #mm_free_modules li.is-module:hover {
		color:#333333;
		background:none;
		background-color:#46b2e2;
		border-color:#000000;
	}
	</style>
	<script type="text/javascript">
	<?php if(isset($ob_javascript)) { ob_start(); } ?>
	var fw_field_counter = parseInt('<?php echo $counter;?>');
	$(function() {
		refresh_sortable()
		$("#mm_add_group").unbind("click").on("click", function() {
			fw_changes_made = true;
			fw_field_counter++;
			$('#mm_sort_modules').append('<li class="ui-state-default is-group"><div class="title row"><div class="col-md-2"><?php echo $formText_GroupName_input;?>:</div><div class="col-md-4"><?php foreach($v_languages as $v_language){?><div><input type="text" name="group_name_'+fw_field_counter+'_<?php echo $v_language['languageID'];?>" value=""><?php if(count($v_languages)>0) { ?>&nbsp;<b>[<?php echo $v_language['languageID'];?>]</b><?php } ?></div><?php } ?></div><div class="col-md-2"><?php echo $formText_Collapse_input;?>:</div><div class="col-md-2"><select name="block_collapse_'+fw_field_counter+'"><option value="1"><?php echo $formText_Yes_input;?></option><option value="0"><?php echo $formText_No_input;?></option></select></div><div class="buttons col-md-2 text-right"><a class="btn btn-xs script btn_expand hide" href="#"><span class="glyphicon glyphicon-collapse-down"></span></a><a class="btn btn-xs script btn_collapse" href="#"><span class="glyphicon glyphicon-collapse-up"></span></a><a class="btn btn-xs script delete" href="#"><span class="glyphicon glyphicon-trash"></span></a></div></div><ul class="sort_group bg-warning"></ul><input type="hidden" name="group_end_'+fw_field_counter+'" value="1" /></li>');
			refresh_sortable();
		});
		$("#mm_save_form").on("click", function(e){
			e.preventDefault();
			var $form = $(this).closest("form");
			$.ajax({
				url: $form.attr("action"),
				cache: false,
				type: "POST",
				dataType: "json",
				data: "fwajax=save&" + $form.serialize(),
				success: function (data) {
					if(data.error !== undefined)
					{
						$.each(data.error, function(index, value){
							var _type = Array("error");
							if(index.length > 0 && index.indexOf("_") > 0) _type = index.split("_");
							fw_info_message_add(_type[0], value);
						});
						fw_info_message_show();
						fw_loading_end();
						fw_click_instance = fw_changes_made = false;
					} else {
						fw_load_ajax(data.url,'',true);
					}
				}
			}).fail(function() {
				fw_info_message_add("error", "<?php echo $formText_ErrorOccuredSavingContent_input;?>", true);
				fw_loading_end();
				fw_click_instance = fw_changes_made = false;
			});
		});
	});
	function refresh_sortable()
	{
		$("#mm_sort_modules li .title .buttons .btn").unbind("click").on("click", function(e) {
			e.preventDefault();
			if($(this).is(".btn_expand")) {
				$(this).addClass("hide").closest("li").children("ul").slideDown("fast");
				$(this).parent().find(".btn_collapse").removeClass("hide");
			}
			if($(this).is(".btn_collapse")) {
				$(this).addClass("hide").closest("li").children("ul").slideUp("fast");
				$(this).parent().find(".btn_expand").removeClass("hide");
			}
			if($(this).is(".delete")) {
				if(!fw_click_instance)
				{
					fw_click_instance = true;
					var _replace = "";
					$obj = $(this).closest("li");
					bootbox.confirm({
						message:"<?php echo $formText_DeleteItem_input;?>?",
						buttons:{confirm:{label:"<?php echo $formText_Yes_input;?>"},cancel:{label:"<?php echo $formText_No_input;?>"}},
						callback: function(result){
							if(result)
							{
								fw_changes_made = true;
								$obj.children("ul").each(function(){ _replace = _replace + $(this).html(); });
								$obj.remove();
								$("#mm_free_modules").append(_replace);
								refresh_sortable();
							}
							fw_click_instance = false;
						}
					});
				}
			}
		});
		$(".mm_dragdrop").sortable({
			connectWith: ".mm_dragdrop",
			items: "> li",
			cancel: ".buttons, input, select",
			stop: function(event, ui) {
				if(ui.item.hasClass("is-group") && $(ui.item).parent().closest('li').hasClass("is-group"))
				{
					$(this).sortable("cancel");
				} else {
					fw_changes_made = true;
				}
			}
		});
	}
	<?php if(isset($ob_javascript)) { $ob_javascript .= " ".ob_get_clean(); } ?>
	</script>
	<?php
} else {
	?>
	<h1><?php echo $formText_ModuleMenuSets_input;?></h1>
	<div class="content_buttons">
		<a href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&submodule=".$submodule."&includefile=edit_module_menu&set_id=0";?>" class="btn btn-info btn-sm optimize"><?php echo $formText_NewSet_input;?></a>
	</div>
	<table class="table">
	<thead>
		<tr>
			<th><?php echo $formText_Name_input;?></th>
			<th><?php echo $formText_Default_input;?></th>
			<th><?php echo $formText_ConnectedUsers_input;?></th>
			<th></th>
		</tr>
	</thead>
	<tbody><?php
	$o_query = $o_main->db->query("select s.*, count(u.set_id) cnt from sys_modulemenuset s left outer join sys_modulemenuusers u on u.set_id = s.id group by s.id order by name");
	if($o_query && $o_query->num_rows()>0)
	foreach($o_query->result_array() as $v_row)
	{
		?><tr>
			<td><?php echo $v_row["name"];?></td>
			<td><span class="glyphicon glyphicon-<?php echo ($v_row["default_set"]==1 ? "check" : "unchecked");?>"></span></td>
			<td><?php echo $v_row["cnt"];?></td>
			<td width="30%" class="text-right">
				<a href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&submodule=".$submodule."&includefile=edit_module_menu&set_id=".$v_row['id']."&_=".$l_rand;?>" class="btn btn-default btn-sm optimize"><?php echo $formText_Edit_input;?></a>
				<a href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&submodule=".$submodule."&includefile=edit_module_menu&delete_set_id=".$v_row['id']."&_=".$l_rand;?>" class="delete-confirm-btn btn btn-danger btn-sm"><?php echo $formText_Delete_input;?></a>
			</td>
		</tr><?php
	}
	?>
	</tbody>
	</table>
	<?php
}
?>
</div>