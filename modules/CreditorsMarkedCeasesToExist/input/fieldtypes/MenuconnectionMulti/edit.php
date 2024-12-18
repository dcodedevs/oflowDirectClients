<?php
include(__DIR__.'/check_db_config.php');
if(!function_exists('include_local')) include(__DIR__."/../../includes/fn_include_local.php");
if(!function_exists('fieldtype_print_menu')) include(__DIR__."/fn_fieldtype_print_menu.php");
if(!function_exists('fieldtype_get_menu_entry')) include(__DIR__."/fn_fieldtype_get_menu_entry.php");

$v_menues_to_show = explode(",",$field[11]);

$v_connected_menulevels = $parents = array();
$o_query = $o_main->db->query("SELECT * FROM pageID, menulevel WHERE pageID.contentID = ? AND pageID.contentTable = ? AND pageID.deleted = 0 AND menulevel.id = pageID.menulevelID AND menulevel.content_status < 2 ORDER BY menulevel.level DESC", array($_GET['ID'], $field[3]));
if($o_query && $listlevels = $o_query->result_array())
{
	foreach($listlevels as $listlevel)
	{
		$v_items = array();
		if(!in_array($listlevel['menulevelID'], $parents) && !in_array($listlevel['menulevelID'], $v_items))
		{
			$v_items[] = $listlevel['menulevelID'];
			$lastParent = $listlevel['menulevelID'];
			$v_connected_menulevels[$listlevel['level']][] = $listlevel['menulevelID'];
		}

		for($a = ($listlevel['level'] - 1); $a >= 0; $a--)
		{
			$o_query = $o_main->db->query("SELECT parentlevelID, (level-1) AS level FROM menulevel WHERE id = ? AND content_status < 2;", array($lastParent));
			if($o_query && $writeParent = $o_query->row_array())
			{
				if($writeParent['level'] >= 0 && !in_array($writeParent['parentlevelID'], $parents) && !in_array($writeParent['parentlevelID'], $v_items))
				{
					$v_items[] = $writeParent['parentlevelID'];
					$lastParent = $writeParent['parentlevelID'];
					$v_connected_menulevels[$writeParent['level']][] = $writeParent['parentlevelID'];
				}
			}
		}
		$v_items = array_reverse($v_items);
		$parents = array_merge($parents, $v_items);
	}
}

$moduleWhereSql = "";
$modulesCounter = 0;
foreach($v_menues_to_show as $moduleName)
{
	$moduleName = trim($moduleName);
	if($moduleName != "")
	{
		$moduleWhereSql .= ($modulesCounter == 0 ? " AND" : " OR")." name = '".$moduleName."'";
		$modulesCounter++;
	}
}
$o_query = $o_main->db->query("SELECT * FROM moduledata WHERE type = '1'".$moduleWhereSql.";");
if($o_query && $parentMenus = $o_query->result_array()) {}
?>
<div class="fieldname linktype" data-toggle="modal" data-target="#<?=$field_ui_id?>menuPopup">
	<?=$formText_editMenuconnection_input;?>
</div>
<input type="hidden" id="<?=$field_ui_id?>main_connected" name="<?=$field[1].$ending."main_connected";?>" value="<?php echo $field[6][$langID];?>">
<?php
$o_query = $o_main->db->query("SELECT MAX(level) max_level FROM menulevel WHERE content_status < 2;");
$l_max_levels = (($o_query && $o_row = $o_query->row()) ? $o_row->max_level : 0)+1;
for($l_level=0; $l_level < $l_max_levels; $l_level++)
{
	?><select id="<?=$field_ui_id?>level<?=$l_level;?>" name="<?=$field[1].$ending."level".$l_level."[]";?>" multiple style="display:none !important;"><?php
	if(isset($v_connected_menulevels[$l_level]))
	{
		foreach($v_connected_menulevels[$l_level] as $l_connected_menulevel)
		{
			?><option value="<?php echo $l_level."_".$l_connected_menulevel;?>" selected><?php echo $l_connected_menulevel;?></option><?php
		}
	}
	?></select><?php
}
?>
<div class="contentMenuConnections" id="<?=$field_ui_id?>menuconnection" data-fielduniqueid="<?=$field_ui_id?>">
	<?php
	foreach($parents as $l_menulevel_id)
	{
		if($l_menulevel_id != "" && $l_menulevel_id != 0)
		{
			$o_query = $o_main->db->query("SELECT * FROM menulevel WHERE id = ".$l_menulevel_id);
			if($o_query && $parentLevelItem = $o_query->row_array()) {}
			$l_menulevel_level = $parentLevelItem['level'];
			$l_menulevel_module_id = $parentLevelItem['moduleID'];
			$parentEntry = fieldtype_get_menu_entry($l_menulevel_id, $s_default_output_language, $o_main);
			?>
			<div class="menuConnectionEntry" data-level="<?php echo $l_menulevel_level;?>" data-menuid="<?php echo $l_menulevel_id;?>" data-menumoduleid="<?php echo $l_menulevel_module_id;?>">
				<?php echo $parentEntry;?>
				<button type="button" class="close" aria-label="Close"><span aria-hidden="true">×</span></button>
			</div>
			<?php
		}
	}
	?>
</div>
<div class="modal fade in" id="<?=$field_ui_id?>menuPopup">
	<div class="modal-dialog modal-lg">
		<div class="modal-content allowScrollWrapper">
			<div class="modal-header">
				<div class="menuchooseTabTitle"><?php echo $formText_EditMenuconnections_fieldtype;?></div>
				<div class="menuchooseTab">
					<?php
					if($access>=10)
					{
						foreach($parentMenus as $parentMenu)
						{
							?>
							<div class="<?=$field_ui_id;?> parentMenuChoose" data-menumoduleid="<?=$parentMenu['id']?>">
								<?=$parentMenu['name'];?>
							</div>
							<?php
						}
					}
					?>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
				</div>
				<div class="clear"></div>
				<div class="menuchooseSearch">
					<input type="text" class="menuchooseSearchInput" value="" placeholder="<?php echo $formText_SearchMenuHere_input;?>"/>
				</div>
			</div>
			<div class="modal-body allowScroll">
				<div class="menuchooseContent">
					<?php foreach($parentMenus as $parentMenu) { ?>
						<div id="<?=$field_ui_id."menu".$parentMenu['id'];?>" class="<?=$field_ui_id."menu"?> popupmenucontent" data-fielduniqueid="<?=$field_ui_id?>"  data-menumoduleid="<?=$parentMenu['id']?>">
							<div class="menuchooseInfo">
								<span class="collapseAll"><?php echo $formText_HideAll_input;?></span>
								<span class="expandAll"><?php echo $formText_SeeAll_input;?></span>
								<span class="checkedInfo"><span class="checkedIcon"></span> =<?php echo $formText_Connected_input;?></span>
							 </div>
							<?php
								$parentMenuName = $parentMenu['name'];
								$v_settings = include_local(__DIR__."/../../../../".$parentMenuName."/input/settings/tables/menulevel.php");
								$s_disp_field = $v_settings['prefieldInList'];
								$orderby = '';
								if($v_settings['orderByField'] != "") $orderby = "ORDER BY menulevel.".$v_settings['orderByField'];

								$menulevel = 0;
								$resultCount = 0;
								$menuOutputName = array();
								$menuOutputName[-1] = $parentMenuName;
								fieldtype_print_menu($menulevel, $parentMenu, $orderby, $menuOutputName,$parents, $s_default_output_language, $o_main, $moduleID);
							?>
						</div>
					<?php } ?>
				</div>
				<div class="menuchooseBottom">
					<div class="close-btn">
						<?php echo $formText_Ok_output;?>
					</div>
					<div class="clear"></div>
				</div>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
	$(".menuchooseSearchInput").keyup(function(){
		var search = $(this).val();
		if(search.length > 2){
			$(".menuItemOneWrapper").hide();
			$(".menuItem").addClass("notSearched").removeAttr("style");
			$(".menuItem").each(function(){
				var menuSearch = $(this).data("menusearch");
				var searchResult = menuSearch.toLowerCase().search(search.toLowerCase());
				if(searchResult >= 0){
					$(this).parents(".menuItemOneWrapper").show();
					$(this).removeClass("notSearched");
					var menulevel = $(this).data("menulevel");
					var childElement = $(this);
					while(menulevel > 0){
						menulevel--;
						elementToShow = $(childElement.prevAll(".menulevel"+menulevel)[0]);
						if(elementToShow.length > 0){
							childElement.removeClass("notSearched").show();
							elementToShow.removeClass("notSearched").addClass("toggled");
							childElement = elementToShow;
						}
					}

				}
			})
		} else {
			$(".menuItemOneWrapper").show();
			$(".menuItem").removeClass("notSearched").removeAttr("style").removeClass("toggled");
		}
	})
	$("#<?=$field_ui_id?>menuconnection").parents(".twofield").prev(".twoinput").hide();
	$(".menuchooseBottom .close-btn").off("click").on("click", function(){
		$(this).parents(".modal-content").find(".modal-header .close span").click();
	})
	$(".menuchooseContent .expandAll").off("click").on("click", function(){
		var parent = $(this).parents(".popupmenucontent");
		parent.find(".menuItem:not(.notSearched)").show().addClass("toggled");
	})
	$(".menuchooseContent .collapseAll").off("click").on("click", function(){
		var parent = $(this).parents(".popupmenucontent");
		parent.find(".menuItem:not(.notSearched):not(.menulevel0)").hide();
		parent.find(".menuItem:not(.notSearched)").removeClass("toggled")
	})

	$(".menuchooseContent .menuItem").off("click").on("click", function(ev){
		if($(ev.target).hasClass("slideToggler")){
			var parent = $(ev.target).parent();
			var menulevel = parent.data("menulevel");
			parent.toggleClass("toggled");
			if(parent.hasClass("toggled")){
				var newTest = parent.nextUntil(".menulevel"+(menulevel)+":not(.notSearched)", ".menulevel"+(menulevel+1)+":not(.notSearched)");
				newTest.show();
			} else {
				parent.nextUntil(".menulevel"+(menulevel)+":not(.notSearched)", ".menulevel"+(menulevel+1)+":not(.notSearched)").hide().removeClass("toggled");
				parent.nextUntil(".menulevel"+(menulevel)+":not(.notSearched)", ".menulevel"+(menulevel+2)+":not(.notSearched)").hide().removeClass("toggled");
				parent.nextUntil(".menulevel"+(menulevel)+":not(.notSearched)", ".menulevel"+(menulevel+3)+":not(.notSearched)").hide().removeClass("toggled");
			}
		} else if($(this).hasClass("active")){
			var parent = $(this);
			var menuID = parent.data("menuid");
			var menuItemInList = $("#tab_<?=$field_ui_id?> .menuConnectionEntry[data-menuid="+menuID+"]");
			if(menuItemInList.length > 0){
				var menulevel = menuItemInList.data('level');
				$('#<?=$field_ui_id?>level'+menulevel+' option[value="'+menulevel+'_'+menuID+'"]').remove();
				$('#<?=$field_ui_id?>level0').trigger('change');
				menuItemInList.remove();
				parent.removeClass("active");
			}
		} else {
			var menuID = $(this).data("menuid");
			var menulevel = menulevel_iterator = $(this).data("menulevel");
			var menuEntry = $(this).data("menuentry");
			var menuModuleID = $(this).parent().parent().data("menumoduleid");
			var fieldUniqueID = $(this).parent().parent().data("fielduniqueid");
			if(menuID != undefined && menuEntry != undefined){
				if(!$(this).hasClass("active")){
					if($("#<?=$field_ui_id?>menuconnection .menuConnectionEntry[data-menuid="+menuID+"]").length == 0){
						while(menulevel_iterator > 0){
							var parentItems = $(this).prevAll(".menulevel"+(menulevel_iterator-1));
							if(parentItems.length > 0){
								var parentItem = $(parentItems[0]);
								if(!parentItem.hasClass("active")){
									var parentMenuID = parentItem.data("menuid");
									var parentMenulevel = parentItem.data("menulevel");
									var parentMenuEntry = parentItem.data("menuentry");
									$("#<?=$field_ui_id?>menuconnection").append('<div class="menuConnectionEntry" data-level="'+menulevel_iterator+'" data-menuid="'+parentMenuID+'" data-menumoduleid="'+menuModuleID+'">'+parentMenuEntry+'<button type="button" class="close" aria-label="Close"><span aria-hidden="true">×</span></button></div>');
									$('#<?=$field_ui_id?>level'+parentMenulevel).append($('<option>', {value: parentMenulevel+'_'+parentMenuID, text: parentMenuID}).prop('selected',true));
									parentItem.addClass("active");
								}
							}
							menulevel_iterator--;
						}
						$("#<?=$field_ui_id?>menuconnection").append('<div class="menuConnectionEntry" data-level="'+menulevel+'" data-menuid="'+menuID+'" data-menumoduleid="'+menuModuleID+'">'+menuEntry+'<button type="button" class="close" aria-label="Close"><span aria-hidden="true">×</span></button></div>');
						$('#<?=$field_ui_id?>level'+menulevel).append($('<option>', {value: menulevel+'_'+menuID, text: menuID}).prop('selected',true));
						$('#<?=$field_ui_id?>level'+menulevel).trigger('change');
						$(this).addClass("active");
					}
					bindRemoveEntry();
				}
			}
		}
	})
	$(".menuchooseTab").each(function(index, el){
		$(el).find(".parentMenuChoose").first().addClass("active");
	})
	$(".<?=$field_ui_id;?>").off("click").on("click", function(){
		var parent = $(this).parents(".modal-content");
		var menuModuleID = $(this).data("menumoduleid");
		parent.find(".parentMenuChoose").removeClass("active");
		$(this).addClass("active");
		parent.find(".popupmenucontent").hide();
		parent.find(".popupmenucontent[data-menumoduleid="+menuModuleID+"]").show();
	})
	$(".parentMenuChoose.active").click();
	function bindRemoveEntry(){
		$(".menuConnectionEntry .close").off("click").on("click", function(){
			var parent =$(this).parent();
			var menuModuleID = parent.data("menumoduleid");
			var menulevel = parent.data("level");
			var menuID = parent.data("menuid");
			var menuItemInPopup = $("#<?=$field_ui_id?>menu"+menuModuleID+" .menuItem[data-menuid="+menuID+"]");
			if(menuItemInPopup.length > 0){
				menuItemInPopup.removeClass("active");
				parent.remove();
				$('#<?=$field_ui_id?>level'+menulevel+' option[value="'+menulevel+'_'+menuID+'"]').remove();
				$('#<?=$field_ui_id?>level0').trigger('change');
			}
		})
	}
	bindRemoveEntry();
</script>
<style type="text/css">

.modal-header {
	background: #009dff;
	padding: 5px 15px;
}
.fieldname.linktype {
	color: #089ceb;
	cursor: pointer;
}
.modal-header .menuchooseSearch {
	margin: 4px 0px;
}
.modal-header .menuchooseSearch .menuchooseSearchInput {
	width: 100%;
	border: 1px solid #e5e5e5;
	height: auto;
	padding: 5px 40px 5px 10px;
	border-radius: 3px;
	background: #fff url("<?php echo $extradir."/input/fieldtypes/".$field[4];?>/search_icon_grey.svg") no-repeat right 10px center;
	background-size: 20px 17px;
}
.menuItem .close {
	display: none;
}
.menuItem.active .close {
	display: block;
	color: #6c6c6c;
	opacity: 1;
}
.menuItem .slideToggler {
	float: right;
	margin-top: 3px;
	margin-left: 10px;
	cursor: pointer;
	color: #2590be;
}
.menuItem .checkmark {
	float: right;
	display: none;
	height: 20px;
	width: 18px;
	margin-top: 3px;
	margin-right: 15px;
	background: url("<?php echo $extradir."/input/fieldtypes/".$field[4];?>/checkmark_green.svg") no-repeat;
}
.menuItem.active .checkmark {
	display: block;
}
.menuItem .glyphicon-menu-down,
.menuItem .glyphicon-menu-right {
	display: none;
}
.menuItem.hasChildren .glyphicon-menu-right {
	display: block;
}
.menuItem.hasChildren.toggled {
	background: #efefef;
}
.menuItem.hasChildren.toggled .glyphicon-menu-right {
	display: none;
}
.menuItem.hasChildren.toggled .glyphicon-menu-down {
	display: block;
}
.menuItem.notSearched {
	display: none;
}


.menuchooseTitle {
	float: left;
	color: #fff;
	padding: 5px 10px;
	font-size: 14px;
}
.menuchooseTab {
	float: right;
	font-size: 14px;
}
.menuchooseTab .close {
	color: #fff;
	font-size: 30px;
	opacity: 1;
}
.menuchooseTabTitle {
	float: left;
	font-size: 14px;
	color: #fff;
	margin-top: 5px;
}
.parentMenuChoose {
	display: inline-block;
	vertical-align: middle;
	cursor: pointer;
	color: #bce5ff;
	padding: 5px 10px;
}
.parentMenuChoose.active {
	color: #fff;
}
.menuchooseContent .menuItemOneWrapper {
	border: 1px solid #e3e3e3;
	margin-bottom: 5px;
	border-radius: 5px;
}
.menuchooseContent {
	margin-top: -10px;
}
.menuchooseContent .menuItem {
	color: #111912;
	min-height: 30px;
	cursor: pointer;
	padding: 6px 10px;
	border-top: 1px solid #e3e3e3;
}
.menuchooseContent .menuItemOneWrapper .menuItem:first-child {
	border-top: 0px;
}
.menuchooseContent .menuItem.active {
	color: #111912;
	cursor: pointer;
	background: #9de3a4;
	-webkit-box-shadow: 0px 0px 2px 0px rgba(0,0,0,0.3);
	-moz-box-shadow: 0px 0px 2px 0px rgba(0,0,0,0.3);
	box-shadow: 0px 0px 2px 0px rgba(0,0,0,0.3);
}
.menuchooseContent .menuItem.menulevel1 {
	padding-left: 30px;
	margin-left: 0;
	display: none;
}
.menuchooseContent .menuItem.menulevel2 {
	padding-left: 60px;
	margin-left: 0;
	display: none;
}
.menuchooseContent .menuItem.menulevel3 {
	padding-left: 90px;
	margin-left: 0;
	display: none;
}
.menuchooseContent .menuchooseInfo {
	margin-bottom: 15px;
	text-align: right;
	color: #a5a6a8;
}
.menuchooseContent .menuchooseInfo .collapseAll {
	margin-left: 5px;
	cursor: pointer;
}
.menuchooseContent .menuchooseInfo .expandAll {
	margin-left: 5px;
	cursor: pointer;
}
.menuchooseContent .menuchooseInfo .checkedInfo {
	margin-left: 20px;
}
.menuchooseContent .menuchooseInfo .checkedInfo .checkedIcon {
	display: inline-block;
	vertical-align: middle;
	height: 15px;
	width: 13px;
	background: url("<?php echo $extradir."/input/fieldtypes/".$field[4];?>/checkmark_green.svg") no-repeat;
}
.contentMenuConnections {

}
.contentMenuConnections .menuConnectionEntry {
	position: relative;
	color: #089ceb;
	padding: 5px 0px;
}
.popupmenucontent {
	display: none;
}
.menuchooseBottom {
	margin-top: 10px;
}
.menuchooseBottom .close-btn {
	float: right;
	background: #009dff;
	padding: 10px 25px;
	border-radius: 6px;
	opacity: 1;
	color: #fff;
	text-transform: uppercase;
	cursor: pointer;
}
.clear {
	clear: both;
}
</style>
