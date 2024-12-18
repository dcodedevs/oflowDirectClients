<?php
	if(isset($_GET['subcontent'])){
		$_SESSION['subcontent'][$_GET['module']][$_GET['parentID']][$_GET['submodule']][$_GET['ID']] = 'opened';
	} else {
		//delete session related to other modules or other content
		if(isset($_SESSION['subcontent'][$_GET['module']])){
			if(!isset($_SESSION['subcontent'][$_GET['module']][$_GET['ID']])){
				unset($_SESSION['subcontent'][$_GET['module']]);
			}
		} else {
			unset($_SESSION['subcontent']);
		}
	}
?>
<?php
function br2nl($string)
{
    return preg_replace('/\<br(\s*)?\/?\>/i', "\n", $string);
}
if(!isset($_GET['subcontent']))
{
?><script type="text/javascript" language="javascript">
<?php if(isset($ob_javascript)) { ob_start(); } ?>
var fw_reload_url;
var out_popup_options={
	follow: [true, true],
	followSpeed: 300,
	fadeSpeed: 0,
	modalClose: false,
	escClose: false,
	closeClass:'b-close',
	onOpen: function() {
		$(this).addClass('opened');
	},
	onClose: function(){
		$(this).removeClass('opened');
	}
};
$(function(){
	apply_content_load();

	<?php if(strpos($submodule, "_accountconfig") > 0 || strpos($submodule, "_basisconfig") > 0) { ?>
	    $(".hoverElement").hover(
	        function(){$(this).addClass("hover");},
	        function(){
	            var item = $(this);
	            setTimeout(function(){
	                if(item.is(":hover")){

	                } else {
	                    item.removeClass("hover");
	                }
	            }, 200)
	        }
	    )
		$(".edit_input_description").off("click").on("click", function(){
			var edit_element = $(this);
			var index = edit_element.data("field-index");
			var description = edit_element.data("field-description");
			if(description == undefined){
				description = "";
			}
			var title = edit_element.data("field-title");
			if(title == undefined){
				title = "";
			}
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html('<div class="popupform"><div class="inner"><div class="line"><div class="lineTitle"><?php echo $formText_OverrideLabel_output ?></div><div class="lineInput"><input name="title" class="input_title popupforminput botspace" autocomplete="off" value="'+title+'"/></div></div><div class="line"><div class="lineTitle"><?php echo $formText_Description_output ?></div><div class="lineInput"><textarea name="description" class="input_description popupforminput botspace">'+description+'</textarea></div></div></div>'+
			'<div class="popupformbtn">'+
				'<button type="button" class="output-btn b-large b-close"><?php echo $formText_Cancel_Output;?></button>'+
				'<input class="input_description_save" type="submit" name="sbmbtn" value="<?php echo $formText_Save_Output; ?>">'+
			'</div></div>');
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
			$(".input_description_save").off("click").on("click", function(){
				var inputDescription = $(".input_description").val();
				var inputTitle = $(".input_title").val();
				var parentDiv = edit_element.parent("div");
				parentDiv.find(".input_title_hidden").val(inputTitle);
				parentDiv.find(".input_title_wrapper").html(inputTitle);
				parentDiv.find(".input_description_hidden").html(inputDescription);
				parentDiv.find(".input_description_wrapper .hoveredInfo").html(nl2br(inputDescription));
				if(inputDescription != ""){
					parentDiv.find(".input_description_wrapper").show();
				} else {
					parentDiv.find(".input_description_wrapper").hide();
				}
				edit_element.data("field-description", inputDescription);
				edit_element.data("field-title", inputTitle);
				out_popup.close();
			})
		});
	<?php } ?>
});
function nl2br (str, is_xhtml) {
    if (typeof str === 'undefined' || str === null) {
        return '';
    }
    var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';
    return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
}
function apply_content_load()
{
	$(document).off("click", ".edit_field_container .edit_block .header").on("click", ".edit_field_container .edit_block .header", function(e){
		if(e.target.nodeName == 'DIV' && !$(e.target).is(".content-toggler")) $(this).find('.content-toggler').trigger('click');
	});
	$(document).off("click", ".content-toggler").on("click", ".content-toggler", function() {
		if($(this).is(".open")) {
			$(this).find(".collapse-down").removeClass("hide");
			$(this).removeClass("open").closest(".toggle-parent").children(".toggle-content").slideUp("fast");
			$(this).find(".collapse-up").addClass("hide");
		} else {
			$(this).find(".collapse-up").removeClass("hide");
			$(this).addClass("open").closest(".toggle-parent").children(".toggle-content").slideDown("fast");
			$(this).find(".collapse-down").addClass("hide");
		}
	});

	$(document).off("click", "input.sys-edit-save-form").on("click", "input.sys-edit-save-form", function(e)
	{
		e.preventDefault();
		if(!fw_click_instance && !fw_editing_instance)
		{
			fw_click_instance = true;
			fw_loading_start();
			fw_info_message_empty();

			var $caller_form = $("#" + $(this).attr("data-form")),
			$main_form = $(".edit_form.main"),
			_check = check_mandatory($main_form);

			var _button = '';
			if($(this).is(".save2")) _button = 'save2';
			if($(this).is(".duplicate")) _button = 'duplicate';
			$caller_form.children("input.firedbuttonID").val(_button);

			$(".edit_form:not(.main)").each(function(){
				_check = _check && check_mandatory(this);
			});

			if(_check)
			{
				fw_reload_url = "";
				fw_edit_form_saver($main_form);
			} else {
				fw_info_message_show();
				fw_loading_end();
				fw_click_instance = fw_changes_made = false;
			}
		} else {
			if(fw_editing_instance)
			{
				fw_info_message_add('warn', '<?php echo $formText_PleaseWait_Input.'! '.$formText_SomeContentIsStillBeingProcessed_Output;?>', true, true);
			}
		}
		return false;
	});
	$(document).off("click", "input.sys-edit-save-form-close").on("click", "input.sys-edit-save-form-close", function(e)
	{
		e.preventDefault();
		if(!fw_click_instance)
		{
			fw_info_message_empty();
			var $form = $(this).closest("div.edit_container").children("form.edit_form");
			if(check_mandatory($form))
			{
				$form.closest(".input_list_form").slideUp();
			} else {
				fw_info_message_show();
				fw_loading_end();
			}
			fw_click_instance = fw_changes_made = false;
		}
	});
	$(document).off("click", "input.sys-edit-save-form-cancel").on("click", "input.sys-edit-save-form-cancel", function(e)
	{
		e.preventDefault();
		if(!fw_click_instance)
		{
			var $_item = $(this).closest('.subcontent-item');
			bootbox.confirm({
				message:"<?php echo $formText_DeleteItem_input;?>?",
				buttons:{confirm:{label:"<?php echo $formText_Yes_input;?>"},cancel:{label:"<?php echo $formText_No_input;?>"}},
				callback: function(result){
					if(result)
					{
						$_item.remove();
						fw_changes_made = true;
					}
					fw_click_instance = false;
				}
			});
		}
	});
	$('ul.edit-page-language-changer li a').on('click',function(e){
		e.preventDefault();
		if($(this).closest('li').is('.active')) return false;
		$('#form_<?php echo $module."_".$submodule."_".$ui_editform_id;?> .edit_field ul.nav-tabs li a.lang_' + $(this).data('lang')).trigger('click');
	});
}
function fw_edit_form_save_next()
{
	var $_forms = $(".edit_form:not(.saved)");
	if($_forms.length > 0){
		// save next form
		fw_edit_form_saver($_forms.get(0));
	} else {
		// saving finished - reload
		window.location = fw_reload_url;
		//fw_load_ajax(fw_reload_url);
		//console.log("load: " + fw_reload_url);
	}
}
function fw_edit_form_saver(_form)
{
	var $form = $(_form);
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
				$form.addClass("saved");
				if(data.url !== undefined) fw_reload_url = data.url;
				fw_edit_form_save_next();
			}
		}
	}).fail(function() {
		fw_info_message_add("error", "<?php echo $formText_ErrorOccuredSavingContent_input;?>", true);
		fw_loading_end();
		fw_click_instance = fw_changes_made = false;
	});
}
function check_mandatory(_form)
{
	for(var instanceName in CKEDITOR.instances)
		CKEDITOR.instances[instanceName].updateElement();

	var _return = true;
	$(_form).removeClass("saved").parent().find(".mandatory").removeClass("error").each(function(index, value){
		if($(this).val() == "")
		{
			_return = false;
			var _field = $(this).addClass("error").closest('.edit_field').find('.fieldname').text();
			if($(this).data('mandatory-parent') && $(this).data('mandatory-field')) _field = _field + ' (' + $(this).addClass("error").closest($(this).data('mandatory-parent')).find($(this).data('mandatory-field')).text() + ')';
			var _msg = "<strong>" + _field + "</strong>";
			if($(this).data('mandatory-text'))
			{
				_msg = _msg + ': ' + $(this).data('mandatory-text');
			} else {
				_msg = _msg + " <?php echo $formText_isMandatory_input;?>";
			}
			fw_info_message_add("error", _msg);
		}
	});

	return _return;
}
<?php if(isset($ob_javascript)) { $ob_javascript .= " ".ob_get_clean(); } ?>
</script>
<?php } ?>
<div class="edit_container">
	<?php
	$b_multilanguage_table = false;
	foreach($databases as $basetable)
	{
		if($basetable->multilanguage==1)
		{
			$b_multilanguage_table = true;
		}
	}
	$v_check = $languageName;
	unset($v_check['all']);
	if($b_multilanguage_table && count($v_check)>1 && $showTranslateLanguageButton == 1)
	{
		$x=0;
		?>
		<div class="btn-group">
			<button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?php echo $formText_ChooseLanguageToAllFields_input;?> <span class="caret"></span></button>
			<ul class="dropdown-menu edit-page-language-changer"><?php
			foreach($languageName as $langID => $langName)
			{
				if($langID=="" || $langID=="all") continue;
				?><li><a class="script" href="#<?php echo $langID;?>" data-lang="<?php echo $langID;?>"><?php echo $langName;?></a></li><?php
				$x++;
			}
			?></ul>
		</div>
		<?php
	}
	?>
	<form id="form_<?php echo $module."_".$submodule."_".$ui_editform_id;?>" class="edit_form<?php echo (!isset($_GET['subcontent'])?" main":"");?>" onSubmit="javascript: return false;" method="post" enctype="multipart/form-data" action="<?php echo $extradir."/input/contentreg.php?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID'];?>">
	<input type="hidden" name="form_type" value="<?php echo (!isset($_GET['subcontent'])?"main":"subcontent");?>" />
	<input type="hidden" name="submodule" value="<?php echo $submodule;?>" />
	<input type="hidden" name="ID" value="<?php echo $ID;?>" />
	<input type="hidden" name="parentID" value="<?php echo $parentID;?>" />
	<input type="hidden" name="module" value="<?php echo $module;?>" />
	<input type="hidden" name="moduleID" value="<?php echo $moduleID;?>" />
	<input type="hidden" name="pageID" value="<?php echo $_GET['pageID'];?>" />
	<input type="hidden" name="usernamelogg" value="<?php echo $variables->loggID;?>" />
	<input type="hidden" name="extradir" value="<?php echo $extradir;?>" />
	<input type="hidden" name="firedbutton" class="firedbuttonID" value="" />
	<input type="hidden" name="parentdir" value="<?php echo $parentdir;?>" />
	<input type="hidden" name="choosenListInputLang" value="<?php echo $choosenListInputLang;?>">
	<input type="hidden" name="choosenAdminLang" value="<?php echo $choosenAdminLang;?>">
	<input type="hidden" name="languageID" value="<?php echo $choosenInputLang;?>" />
	<input type="hidden" name="extraimagedir" value="<?php echo $extraimagedir;?>" />
	<?php
	if(isset($_GET['return_url']))
	{
		?><input type="hidden" name="return_url" value="<?php echo urldecode($_GET['return_url']);?>" /><?php
	}
	if(isset($_GET['relationID']))
	{
		?><input type="hidden" name="relationfield" value="<?php echo $_GET['relationfield'];?>" />
		<input type="hidden" name="relationID" value="<?php echo $_GET['relationID'];?>" /><?php
	}
	if(isset($_GET['content_status']))
	{
		?><input type="hidden" name="content_status_filter" value="<?php echo $_GET['content_status'];?>" /><?php
	}
	foreach($databases as $basetable)
	{
		?><input type="hidden" value="<?php echo $basetable->ID;?>" name="<?php echo $basetable->name;?>ID" /><?php
	}
	?>
	<div id="roundFields" class="edit_field_container">
		<?php
		$sizeNumber = sizeof($fields);
		$free_fields = array_keys($fieldsStructure);
		foreach($preblocks as $fieldid => $block)
		{
			if(is_array($block) && isset($block['sys_name']))
			{
				foreach($block['sys_childs'] as $fieldid => $item)
				{
					if(isset($item['sys_childs']) && is_array($item['sys_childs']) && sizeof($item['sys_childs'])>0)
					{
						// group
						foreach($item['sys_childs'] as $fieldid => $item)
						{
							// field
							$idx = array_search($fieldid, $free_fields);
							if($idx !== false) unset($free_fields[$idx]);
						}
					} else {
						// field
						$idx = array_search($fieldid, $free_fields);
						if($idx !== false) unset($free_fields[$idx]);
					}
				}
			} else if(is_array($block) && isset($block['sys_columns']))
			{
				// group
				foreach($block['sys_childs'] as $fieldid => $item)
				{
					// field
					$idx = array_search($fieldid, $free_fields);
					if($idx !== false) unset($free_fields[$idx]);
				}
			} else {
				// field
				$idx = array_search($fieldid, $free_fields);
				if($idx !== false) unset($free_fields[$idx]);
			}
		}
		if(count($free_fields)>0)
		{
			$v_free_fields = array_combine($free_fields,array_keys($free_fields));
			$preblocks = array_merge($preblocks, $v_free_fields);
		}
		//print_r($preblocks);
		foreach($preblocks as $fieldid => $block)
		{
			if(is_array($block) && isset($block['sys_name']))
			{
				// block
				?><div class="edit_block ui-corner-all toggle-parent">
					<div class="header">
						<?php echo (isset($block['sys_name']) ? $block['sys_name'] : '');?>
						<div class="content-toggler buttons btn btn-xs<?php echo ($block['sys_collapse']==1?"":" open");?>">
							<span class="toggler collapse-down glyphicon glyphicon-collapse-down<?php echo ($block['sys_collapse']==1?"":" hide");?>"></span>
							<span class="toggler collapse-up glyphicon glyphicon-collapse-up<?php echo ($block['sys_collapse']==1?" hide":"");?>"></span>
						</div>
					</div>
					<div class="edit_fields toggle-content ui-corner-all fw_clear_both"<?php echo ($block['sys_collapse']==1?' style="display:none;"':"");?>><?php
					foreach($block['sys_childs'] as $fieldid => $item)
					{
						if(isset($item['sys_childs']) && is_array($item['sys_childs']) && sizeof($item['sys_childs'])>0)
						{
							// group
							?><div class="edit_group <?php echo ($item['sys_columns'] > 0 ? " edit_group_col".$item['sys_columns'] : "");?>"><?php
							foreach($item['sys_childs'] as $fieldid => $item)
							{
								include(__DIR__."/editField.php");
							}
							?><div class="fw_clear_both"></div></div><?php
						} else {
							include(__DIR__."/editField.php");
						}
					}
					?></div>
				</div><?php
			} else if(is_array($block) && isset($block['sys_columns']))
			{
				// group
				?><div class="edit_group <?php echo ($block['sys_columns'] > 0 ? " edit_group_col".$block['sys_columns'] : "");?>"><?php
				foreach($block['sys_childs'] as $fieldid => $item)
				{
					include(__DIR__."/editField.php");
				}
				?><div class="fw_clear_both"></div></div><?php
			} else {
				include(__DIR__."/editField.php");
			}
		}

		if($activateSeo == "1")
		{
			$o_query = $o_main->db->query('SELECT * FROM accountinfo');
			$v_accountinfo = $o_query->row_array();
			$seoAutoFill = array();
			$b_collapse_seo = (isset($expandSeoSettings) && '1' == $expandSeoSettings) ? FALSE : TRUE;
			?>
			<style>
			.sys-seo { border:1px solid #999999; background-color:#f5f5f5; padding:10px 5px; }
			.sys-seo .sys-seo-label { padding-right:15px; }
			.sys-seo .sys-seo-input input { width:100%; }
			.sys-seo .sys-seo-input i { font-size:10px; }
			.sys-seo .sys-seo-input h3 { margin:0 0 5px 0; color:#2519A8; }
			.sys-seo .sys-seo-input .sys-seo-link { color:#095D10; cursor:pointer; }
			.sys-seo .sys-seo-preview { background-color:#ffffff; padding:5px; }
			</style>
			<div class="edit_block ui-corner-all toggle-parent">
				<div class="header">
					<?php echo $formText_SeoSettings_Input;?>
					<div class="content-toggler buttons btn btn-xs<?php echo ($b_collapse_seo?"":" open");?>">
						<span class="toggler collapse-down glyphicon glyphicon-collapse-down<?php echo ($b_collapse_seo==1?"":" hide");?>"></span>
						<span class="toggler collapse-up glyphicon glyphicon-collapse-up<?php echo ($b_collapse_seo?" hide":"");?>"></span>
					</div>
				</div>
				<div class="sys-seo toggle-content ui-corner-all fw_clear_both"<?php echo ($b_collapse_seo==1?' style="display:none;"':"");?>>
			<?php
			$v_seo_url_data = array();
			$s_sql = 'select pc.* from pageID p JOIN pageIDcontent pc ON pc.pageIDID = p.id WHERE p.contentID = ? AND p.contentTable = ? AND p.deleted = ?';
			$o_query = $o_main->db->query($s_sql, array($ID, $submodule, 0));
			if($o_query && $o_query->num_rows()>0)
			{
				foreach($o_query->result_array() as $row)
				{
					if($row['urlrewrite']!="")
					{
						$s_key = $row['languageID'];
						if(array_key_exists("all",$fieldsStructure['seourl'][6])) $s_key = "all";
						$fieldsStructure['seourl'][6][$s_key] = $row['urlrewrite'];
						$v_seo_url_data['lang'][$s_key] = $row['lang_url_part'];
						$v_seo_url_data['menu'][$s_key] = $row['menu_url_part'];
						$v_seo_url_data['content'][$s_key] = $row['content_url_part'];
					}
				}
			}
			$s_sql = 'select sc.* from seodata s join seodatacontent sc on s.id = sc.seodataID where s.contentID = ? and s.contentModuleID = ?';
			$o_query = $o_main->db->query($s_sql, array($ID, $moduleID));
			if($o_query && $o_query->num_rows()>0)
			{
				foreach($o_query->result_array() as $row)
				{
					if($row['seoTitle']!="")
					{
						if(array_key_exists("all",$fieldsStructure['seotitle'][6]))
						{
							$fieldsStructure['seotitle'][6]['all'] = $row['seoTitle'];
						} else {
							$fieldsStructure['seotitle'][6][$row['languageID']] = $row['seoTitle'];
						}
					}
					if($row['seoDescription']!="")
					{
						if(array_key_exists("all",$fieldsStructure['seodescription'][6]))
						{
							$fieldsStructure['seodescription'][6]['all'] = $row['seoDescription'];
						} else {
							$fieldsStructure['seodescription'][6][$row['languageID']] = $row['seoDescription'];
						}
					}
					if($row['seoKeywords']!="")
					{
						if(array_key_exists("all",$fieldsStructure['seokeywords'][6]))
						{
							$fieldsStructure['seokeywords'][6]['all'] = $row['seoKeywords'];
						} else {
							$fieldsStructure['seokeywords'][6][$row['languageID']] = $row['seoKeywords'];
						}
					}
				}
			}

			foreach($fieldsStructure['seotitle'][6] as $key => $value)
			{
				if($key=='all') $key = '';
				$obj_ui_id = $fieldsStructure['seotitle']['ui_id'.$key];



				?><div>
					<div class="oneinput sys-seo-label"><b><?php echo $formText_seoTitle_input;?></b><?php echo ($key!='' ? ' '.$languageName[$key] : '');?></div>
					<div class="onefield sys-seo-input"><input type="text" id="<?php echo $obj_ui_id;?>" name="<?php echo $submodule.'seotitle'.$key;?>" value="<?php echo $value;?>" maxlength="70"> <i><?php echo $formText_length_input;?> <span class="counter"><?php echo strlen($value);?></span> <?php echo $formText_of_input;?> <span class="total">70</span></i></div>
				</div><?php
				$seoAutoFill[] = array($obj_ui_id, $fieldsStructure[$seoTitleField]['ui_id'.$key], $key, 2);
			}
			foreach($fieldsStructure['seodescription'][6] as $key => $value)
			{
				if($key=='all') $key = '';
				$obj_ui_id = $fieldsStructure['seodescription']['ui_id'.$key];
				?><div>
					<div class="oneinput sys-seo-label"><b><?php echo $formText_seoDescription_input;?></b><?php echo ($key!='' ? ' '.$languageName[$key] : '');?></div>
					<div class="onefield sys-seo-input"><input type="text" id="<?php echo $obj_ui_id;?>" name="<?php echo $submodule.'seodescription'.$key;?>" value="<?php echo $value;?>" maxlength="300"> <i><?php echo $formText_length_input;?> <span class="counter"><?php echo strlen($value);?></span> <?php echo $formText_of_input;?> <span class="total">300</span></i></div>
				</div><?php
				$seoAutoFill[] = array($obj_ui_id, $fieldsStructure[$seoDescriptionField]['ui_id'.$key], $key);
			}
			foreach($fieldsStructure['seourl'][6] as $key => $value)
			{
				if($key=='all') $key = '';
				$obj_ui_id = $fieldsStructure['seourl']['ui_id'.$key];
				if($seoUrlEditType == 2)
				{
					// Full url editable
					?><div>
						<div class="oneinput sys-seo-label"><b><?php echo $formText_seoUrl_input;?></b><?php echo ($key!='' ? ' '.$languageName[$key] : '');?></div>
						<div class="onefield sys-seo-input">
							<input type="hidden" id="<?php echo $obj_ui_id;?>lang" name="<?php echo $submodule.'seourl'.$key;?>lang" value="">
							<input type="hidden" id="<?php echo $obj_ui_id;?>menu" name="<?php echo $submodule.'seourl'.$key;?>menu" value="">
							<input type="hidden" id="<?php echo $obj_ui_id;?>content" name="<?php echo $submodule.'seourl'.$key;?>content" class="form-control input-sm" value="">
							<input type="text" id="<?php echo $obj_ui_id;?>" name="<?php echo $submodule.'seourl'.$key;?>" value="<?php echo $value;?>">
						</div>
					</div><?php
				} else if($seoUrlEditType == 1) {
					// Content part editable
					?><div>
						<div class="oneinput sys-seo-label"><b><?php echo $formText_seoUrl_input;?></b><?php echo ($key!='' ? ' '.$languageName[$key] : '');?></div>
						<div class="onefield sys-seo-input input-group">
							<span id="<?php echo $obj_ui_id;?>langmenu" class="input-group-addon"><?php echo $v_seo_url_data['lang'][$key].$v_seo_url_data['menu'][$key];?></span>
							<input type="hidden" id="<?php echo $obj_ui_id;?>lang" name="<?php echo $submodule.'seourl'.$key;?>lang" value="<?php echo $v_seo_url_data['lang'][$key];?>">
							<input type="hidden" id="<?php echo $obj_ui_id;?>menu" name="<?php echo $submodule.'seourl'.$key;?>menu" value="<?php echo $v_seo_url_data['menu'][$key];?>">
							<input type="text" id="<?php echo $obj_ui_id;?>content" name="<?php echo $submodule.'seourl'.$key;?>content" class="form-control input-sm" value="<?php echo $v_seo_url_data['content'][$key];?>">
							<input type="hidden" id="<?php echo $obj_ui_id;?>" name="<?php echo $submodule.'seourl'.$key;?>" value="<?php echo $value;?>">
						</div>
					</div><?php
				} else {
					// Url not editable
					?><input type="hidden" id="<?php echo $obj_ui_id;?>lang" name="<?php echo $submodule.'seourl'.$key;?>lang" value="<?php echo $v_seo_url_data['lang'][$key];?>">
					<input type="hidden" id="<?php echo $obj_ui_id;?>menu" name="<?php echo $submodule.'seourl'.$key;?>menu" value="<?php echo $v_seo_url_data['menu'][$key];?>">
					<input type="hidden" id="<?php echo $obj_ui_id;?>content" name="<?php echo $submodule.'seourl'.$key;?>content" value="<?php echo $v_seo_url_data['content'][$key];?>">
					<input type="hidden" id="<?php echo $obj_ui_id;?>" name="<?php echo $submodule.'seourl'.$key;?>" value="<?php echo $value;?>"><?php
				}
				$seoAutoFill[] = array($obj_ui_id, $fieldsStructure[$seoUrlField]['ui_id'.$key], $key, 1);
			}
			foreach($fieldsStructure['seourl'][6] as $lid => $value)
			{
				$key = ($lid=="all"?"":$lid);
				?><div>
					<div class="oneinput sys-seo-label"><b><?php echo $formText_seoGooglePreview_input;?></b><?php echo ($key!='' ? ' '.$languageName[$key] : '');?></div>
					<div class="onefield sys-seo-input">
						<div class="sys-seo-preview">
							<h3 id="<?php echo $fieldsStructure['seotitle']['ui_id'.$key];?>preview"><?php echo $fieldsStructure['seotitle'][6][$lid];?></h3>
							<div class="sys-seo-link" data-href="<?php echo rtrim(($v_accountinfo['domain']!="" ? 'http://'.$v_accountinfo['domain'] : $languagedir),'/');?>/"><?php echo rtrim(($v_accountinfo['domain']!="" ? $v_accountinfo['domain'] : 'www.mydomain.com'),'/');?>/<span id="<?php echo $fieldsStructure['seourl']['ui_id'.$key];?>preview"><?php echo $value;?></span></div>
							<div id="<?php echo $fieldsStructure['seodescription']['ui_id'.$key];?>preview"><?php echo $fieldsStructure['seodescription'][6][$lid];?></div>
						</div>
					</div>
				</div><?php
			}

			if($activateSeoKeywords == "1")
			{
				foreach($fieldsStructure['seokeywords'][6] as $key => $value)
				{
					if($key=='all') $key = '';
					?><div>
						<div class="oneinput sys-seo-label"><b><?php echo $formText_seoKeywords_input;?></b><?php echo ($key!='' ? ' '.$languageName[$key] : '');?></div>
						<div class="onefield sys-seo-input"><input type="text" id="seokeywords<?php echo $key;?>" name="<?php echo $submodule.'seokeywords'.$key;?>" value="<?php echo $value;?>"></div>
					</div><?php
				}
			}

			if($activateSeoHeadingPreview == 1)
			{
				$row = array();
				$o_query = $o_main->db->query("select * from pageID WHERE contentID = ? AND contentTable = ? AND deleted = ?", array($ID, $submodule, 0));
				if($o_query) $row = $o_query->row_array();
				if($row['id']>0)
				{
					$ch = curl_init($extradomaindirroot."index.php?pageID=".$row["id"]);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					$response = curl_exec($ch);
					curl_close($ch);

					if(!function_exists("get_text_between_tags")) include(__DIR__.'/fn_get_text_between_tags.php');

					$h1tags = get_text_between_tags('h1',$response);
					$h2tags = get_text_between_tags('h2',$response);
					?>
					<div><b><?php echo $formText_Heading_input;?></b> (<i><?php echo $formText_SaveContentToGetActualText_input;?></i>)</div>
					<div>
						<div class="oneinput sys-seo-label"><b>H1</b></div>
						<div class="onefield sys-seo-input"><?php
						if(sizeof($h1tags)==0) print '-';
						foreach($h1tags as $item)
						{
							?><div><?php echo $item;?></div><?php
						}
						?></div>
					</div>
					<div>
						<div class="oneinput sys-seo-label"><b>H2</b></div>
						<div class="onefield sys-seo-input"><?php
						if(sizeof($h2tags)==0) print '-';
						foreach($h2tags as $item)
						{
							?><div><?php echo $item;?></div><?php
						}
						?></div>
					</div><?php
				}
			}
			?>
			<br clear="all">
			</div>
			</div>
			<script type="text/javascript">
			<?php if(isset($ob_javascript)) { ob_start(); } ?>
			$(function () {
				$('.sys-seo-link').on('click', function() {
					var _win = window.open($(this).data("href") + $(this).find('span').text(), '_blank');
					_win.focus();
				});
				<?php
				foreach($seoAutoFill as $item)
				{
					if($item[3] == 1) //seo url
					{
						if($seoMenuField!="")
						{
							for($i=0;$i<5;$i++)
							{
								?>
								$('#<?php echo $fieldsStructure[$seoMenuField]['ui_id'].'level'.$i;?>').on('change',function() {
									sys_updateSeoUrl('#<?php echo $item[0];?>', '<?php echo $item[2];?>');
								});<?php
							}
						}
						?>
						// On field change
						$('#<?php echo $item[1];?>').on('change',function() {
							$('#<?php echo $item[0].($seoUrlEditType < 2 ? 'content':'');?>').val($(this).val()).trigger('change');
						});
						// On Seo change
						$('#<?php echo $item[0].($seoUrlEditType < 2 ? 'content':'');?>').on('change',function() {
							sys_updateSeoUrl('#<?php echo $item[0];?>', '<?php echo $item[2];?>');
						});
						if($('#<?php echo $item[0];?>').val()=="") $('#<?php echo $item[0].($seoUrlEditType < 2 ? 'content':'');?>').val($('#<?php echo $item[1];?>').val()).trigger('change');<?php
					} else if($item[3] == 2 and $mergeHeaderAndMenulevelInTitle=="1") //seo title
					{
						if($seoMenuField!="")
						{
							for($i=0;$i<5;$i++)
							{
								?>
								$('#<?php echo $fieldsStructure[$seoMenuField]['ui_id'].'level'.$i;?>').on('change',function() {
									sys_updateSeoTitle('#<?php echo $item[0];?>', '#<?php echo $item[1];?>', '<?php echo $item[2];?>');
								});<?php
							}
						}
						?>
						// On field change
						$('#<?php echo $item[1];?>').on('change',function() {
							sys_updateSeoTitle('#<?php echo $item[0];?>', '#<?php echo $item[1];?>', '<?php echo $item[2];?>');
						});
						// On Seo change
						$('#<?php echo $item[0];?>').on('change',function() {
							if($(this).attr('maxlength'))
							{
								var length = $(this).val().length
								var max_l = parseInt($(this).attr('maxlength'));
								if(length > max_l)
								{
									length = max_l;
									$(this).val($(this).val().substr(0,max_l));
								}
								$(this).parent().find('span.counter').text(length);
							}
							$('#<?php echo $item[0];?>preview').text($(this).val());
						});
						if($('#<?php echo $item[0];?>').val()=="") $('#<?php echo $item[0];?>').val($('#<?php echo $item[1];?>').val()).trigger('change');<?php
					} else { // other fields
						?>
						// On field change
						$('#<?php echo $item[1];?>').on('change',function() {
							$('#<?php echo $item[0];?>').val($(this).val()).trigger('change');

						});
						// On Seo change
						$('#<?php echo $item[0];?>').on('keyup change',function() {
							if($(this).attr('maxlength'))
							{
								var length = $(this).val().length
								var max_l = parseInt($(this).attr('maxlength'));
								if(length > max_l)
								{
									length = max_l;
									$(this).val($(this).val().substr(0,max_l));
								}
								$(this).parent().find('span.counter').text(length);
							}
							$('#<?php echo $item[0];?>preview').text($(this).val());
							$(window).resize();
						});
						if($('#<?php echo $item[0];?>').val()=="") $('#<?php echo $item[0];?>').val($('#<?php echo $item[1];?>').val()).trigger('change');<?php
					}
				}
				?>
			});
			function sys_updateSeoUrl(seoUrlField, languageID)
			{
				$(seoUrlField+'<?php echo ($seoUrlEditType < 2 ? 'content':'');?>').attr('disabled',true);
				var _menulevelID = 0;
				<?php if($seoMenuField!="") { ?>
				for(i = 0; i < 5; i++)
				{
					if($('#<?php echo $fieldsStructure[$seoMenuField]['ui_id'];?>level'+i).length && $('#<?php echo $fieldsStructure[$seoMenuField]['ui_id'];?>level'+i).val())
					{
						var _value = $('#<?php echo $fieldsStructure[$seoMenuField]['ui_id'];?>level'+i).val();
						if($.isArray(_value)) _value = _value[0];
						if((_value.indexOf('_') === -1 && _value > 0) || (_value.indexOf('_') >= 0 && _value.length > 0))
						{
							_menulevelID = _value;
						}
					}
				}
				if($('#<?php echo $fieldsStructure[$seoMenuField]['ui_id'];?>main_connected').length)
				{
					$('#<?php echo $fieldsStructure[$seoMenuField]['ui_id'];?>main_connected').val(_menulevelID);
				}
				<?php } ?>
				$.ajax({
					type: 'POST',
					dataType: 'json',
					url: '<?php echo $extradir;?>/input/fieldtypes/SeoUrl/ajax_parseUrl.php',
					cache: false,
					data: { id: '<?php echo $ID;?>', table: '<?php echo $submodule;?>', menulevelID: _menulevelID, data : $(seoUrlField+'<?php echo ($seoUrlEditType < 2 ? 'content':'');?>').val(), languageID: languageID, allowEmpty: '<?php echo ($enableEmptySeoUrl==1?1:0);?>', seoUrlEditType: '<?php echo $seoUrlEditType;?>' },
					success: function(data) {
						$(seoUrlField).val(data.parsed).attr('disabled',false);
						<?php if($seoUrlEditType < 2) { ?>
						$(seoUrlField+'langmenu').text(data.langmenupart);
						$(seoUrlField+'lang').val(data.langpart);
						$(seoUrlField+'menu').val(data.menupart);
						$(seoUrlField+'content').val(data.contentpart).attr('disabled',false);
						<?php } ?>
						$(seoUrlField+'preview').text(data.parsed);
						$(window).resize();
					}
				});
			}
			<?php if($mergeHeaderAndMenulevelInTitle=="1") { ?>
			function sys_updateSeoTitle(seoTitleField, titleField, languageID)
			{
				$(seoTitleField).attr('disabled',true);
				var _menulevelID = 0;
				<?php if($seoMenuField!="") { ?>
				for(i = 0; i < 5; i++)
				{
					if($('#<?php echo $fieldsStructure[$seoMenuField]['ui_id'];?>level'+i) && $('#<?php echo $fieldsStructure[$seoMenuField]['ui_id'];?>level'+i).val()>0)
					{
						_menulevelID = $('#<?php echo $fieldsStructure[$seoMenuField]['ui_id'];?>level'+i).val();
					}
				}
				<?php } ?>
				$.ajax({
					type: 'POST',
					dataType: 'json',
					url: '<?php echo $extradir;?>/input/fieldtypes/SeoUrl/ajax_parseTitle.php',
					cache: false,
					data: { id: '<?php echo $ID;?>', table: '<?php echo $submodule;?>', menulevelID: _menulevelID, data : $(titleField).val(), languageID: languageID },
					success: function(data) {
						$(seoTitleField).val(data.parsed).attr('disabled',false);
						if($(seoTitleField).attr('maxlength')) $(seoTitleField).parent().find('span.counter').text($(seoTitleField).val().length);
						$(seoTitleField + 'preview').text($(this).val());
						$(window).resize();
					}
				});
			}
			<?php } ?>
			<?php if(isset($ob_javascript)) { $ob_javascript .= " ".ob_get_clean(); } ?>
			</script>
			<?php
		}
		?>
		<?php
		$ob_created_updated = "";
		if($showUpdatedCreatedBy == 1)
		{
			ob_start();
			$subData = array();
			$o_query = $o_main->db->query('SELECT createdBy, created, updatedBy, updated FROM '.$submodule.' WHERE id =  ?', array($ID));
			if($o_query) $subData = $o_query->row_array();
			?><div class="createdByField">
			<table border="0" cellspacing="0" cellpadding="0">
			<tr><td style="padding-right:14px;"><?php echo $formText_createdBy_input;?>: </td><td><?php echo $subData['createdBy']." ".$subData['created'];?></td></tr>
			<tr><td style="padding-right:14px;"><?php echo $formText_updatedBy_input;?>: </td><td><?php echo $subData['updatedBy']." ".$subData['updated'];?></td></tr>
			</table>
			</div><?php
			$ob_created_updated = ob_get_clean();
		}
		//special javascript load for each table field
		ob_start();
		foreach($fields as $field)
		{
			$javascript_file = "/input/settings/fieldsJS/".$field[1].".php";
			if(is_file(__DIR__."/../../".$javascript_file))
			{
				include(__DIR__."/../../".$javascript_file);
			}
		}
		$js_code = ob_get_clean();

		//edit page buttons
		ob_start();
		if(!isset($_GET['no_buttons']))
		{
			if(!isset($_GET['subcontent']))
			{
				?><table><tr><?php
				ob_start();
				if($fieldsStructure['content_status'][6]['all'] == 3)
				{
					?><td><input class="content_button ui-corner-all" onClick="javascript: window.history.back();" type="button" value="<?php echo $formText_Back_input;?>" /></td><?php
				} else {
					if($inputButtonSave == 1 and $access >= 10)
					{
						?><td><input class="content_button ui-corner-all sys-edit-save-form" data-form="form_<?php echo $module."_".$submodule."_".$ui_editform_id;?>" type="submit" name="send" value="<?php echo $formText_save_input;?>" /></td><?php
					}
					if($showDuplicate == 1 and $access >= 10)
					{
						?><td><input class="content_button ui-corner-all sys-edit-save-form duplicate" data-form="form_<?php echo $module."_".$submodule."_".$ui_editform_id;?>" type="submit" name="send" value="<?php echo $formText_duplicate_input;?>" /></td><?php
					}
					if($showSave2button == 1 and $access >= 10)
					{
						?><td><input class="content_button ui-corner-all sys-edit-save-form save2" data-form="form_<?php echo $module."_".$submodule."_".$ui_editform_id;?>" type="submit" name="send" value="<?php echo $formText_save2button_input;?>" /></td><?php
					}
				}
				$ob_save_buttons = ob_get_flush();
				?>
				</tr></table>
				<section id="sys-edit-button-panel" class="txt-highlight-color bg-color bg-pattern ui-corner-right" style="width:0px;padding-left:0;padding-right:0;">
					<table><tr>
						<?php echo $ob_save_buttons;?>
						<td><button id="sys-edit-button-panel-trigger" type="button" class="btn btn-xs btn-info" data-hide="-150" data-show="0">
							<span class="glyphicon glyphicon glyphicon-menu-right"></span>
							<span class="glyphicon glyphicon glyphicon-menu-left"></span>
						</button></td>
					</tr></table>
				</section>
				<script type="text/javascript">
				<?php if(isset($ob_javascript)) { ob_start(); } ?>
				$(function(){
					$('#sys-edit-button-panel-trigger').on('click', function(){
						var $$ = $(this);
						if( $$.is('.open') ){
							$('#sys-edit-button-panel').animate({width:1,'padding-left':0,'padding-right':0}, 100).find('input').hide();
							$$.removeClass('open');
						} else {
							$('#sys-edit-button-panel').removeAttr('style').css('left', $$.data('show')).find('input').show();
							$$.addClass('open');
						}
					});
				});
				<?php if(isset($ob_javascript)) { $ob_javascript .= " ".ob_get_clean(); } ?>
				</script>
				<style>
				#sys-edit-button-panel{position:fixed;bottom:2px;left:0;height:50px;padding:10px;background-color:#fff;border:1px solid rgba(0, 0, 0, 0.15);border-left:0;z-index:4;}
				#sys-edit-button-panel-trigger{position:absolute;top:10px;right:-19px;color:#fff;width:20px;height:30px;}
				#sys-edit-button-panel-trigger .glyphicon-menu-left{display:none;}
				#sys-edit-button-panel-trigger.open .glyphicon-menu-right{display:none;}
				#sys-edit-button-panel-trigger.open .glyphicon-menu-left{display:block;}
				</style><?php
			} else {
				if($showSave2button == 1 and $access >= 10)
				{
					?><input class="content_button ui-corner-all sys-edit-save-form save2" data-form="form_<?php echo $module."_".$submodule."_".$ui_editform_id;?>" onClick="javascript: return false;" type="submit" name="send" value="<?php echo $formText_save2button_input;?>" /><?php
				}
				if($ID>0)
				{
					?><input class="content_button ui-corner-all local sys-edit-save-form-close" type="button" name="close" value="<?php echo $formText_Close_input;?>" /><?php
				} else {
					?><input class="content_button ui-corner-all local sys-edit-save-form-cancel" type="button" name="cancel" value="<?php echo $formText_Cancel_input;?>" /><?php
				}
			}
		}
		$ob_edit_buttons = ob_get_clean();

		// edit page extra buttons
		ob_start();
		if($showPrint == 1)
		{
			?><br /><br /><a href="<?php echo $extradir;?>/input/print.php?ID=<?php echo $ID;?>"><?php echo $formText_print_list;?></a>&nbsp;&nbsp;&nbsp;<?php
		}
		if($ID > 0 && $activateHistory == "1")
		{
			?><div style="text-align:right; padding:15px 0 10px 0"><a href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&moduleID=".$moduleID."&ID=".$ID."&includefile=edit&submodule=".$submodule.(isset($_GET['relationID']) ? "&relationID=".$_GET['relationID']."&parentID=".$_GET['relationID']:"").(isset($_GET['relationfield']) ? "&relationfield=".$_GET['relationfield']:"").(!isset($_GET['showHistory']) ? "&showHistory=1":"");?>" class="optimize" style="text-decoration:none; color:#000066;"><?php echo (isset($_GET['showHistory']) ? $formText_hideHistory_inputEdit : $formText_showHistory_inputEdit);?></a></div><?php
		}
		$ob_extra_buttons = ob_get_clean();
		?>
	</div>
	</form>
	<div class="form_container"><?php
		if($ID > 0)
		{
			include("sublist.php");
		}
	?></div>
	<?php echo $ob_created_updated;?>
	<div class="content_buttons">
		<?php echo $ob_edit_buttons;?><div class="fw_clear_both"></div>
	</div>
	<div><?php echo $ob_extra_buttons;?></div>
	<?php
	if($js_code!="")
	{
		if(strpos($js_code,"[:replace:")!==false) $js_code = str_replace(array_keys($fields_replace), $fields_replace, $js_code);
		if(isset($ob_javascript))
		{
			$ob_javascript .= $js_code;
		} else {
			?><script type="text/javascript"><?php echo $js_code;?></script><?php
		}
	}
	if(isset($_GET['showHistory']))
	{
		include("history.php");
	}
	?>
</div>
<div id="popupeditbox" class="popupeditbox">
	<span class="button b-close"><span>X</span></span>
	<div id="popupeditboxcontent"></div>
</div>
<style type="text/css">
.edit_field_container .edit_block .header {
	cursor:pointer;
}
.edit_input_description {
	cursor: pointer;
	color: #cecece;
}
.hoverElement {
	position: relative;
	color: #cecece;
	margin-top: 2px;
}
.hoverElement .hoveredInfo {
    font-family: 'PT Sans', sans-serif;
    width: 300px;
    display: none;
    color: #000;
    position: absolute;
    left: 0%;
    top: 100%;
    padding: 5px 10px;
    background: #fff;
    border: 1px solid #ccc;
    z-index: 1;
	max-height: 300px;
	overflow: auto;
}
.hoverElement.hover .hoveredInfo {
	display: block;
}
.input_description_wrapper {
	display: none;
}
.input_description_hidden {
	display: none;
}
.form-control[disabled], .form-control[readonly], fieldset[disabled] .form-control {
cursor: not-allowed;
/* background-color: #eee; */
opacity: 0.5;
}
</style>
