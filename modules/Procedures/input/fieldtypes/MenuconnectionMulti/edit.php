<?php
if(!function_exists('include_local')) include(__DIR__."/../../includes/fn_include_local.php");
if(!function_exists('fieldtype_print_menu')) include(__DIR__."/fn_fieldtype_print_menu.php");
if(!function_exists('fieldtype_get_menu_entry')) include(__DIR__."/fn_fieldtype_get_menu_entry.php");

$modulesToShow = explode(",",$field[11]);
$lastParent = $field[6][$langID];
$startAt = 0;
$parentMenu = 0;
$o_query = $o_main->db->query("SELECT level FROM menulevel WHERE contentID = ? AND contentTable = ? AND content_status < 2", array($_GET['ID'], $field[3]));
if($o_query && $writeFirst = $o_query->row_array())
{
	if($writeFirst[0] != "")
	{
		$startAt = $writeFirst[0];
	}
}

$parents = array();
$o_query = $o_main->db->query("SELECT * FROM pageID, menulevel WHERE pageID.contentID = ? AND pageID.contentTable = ? AND pageID.deleted = 0 AND menulevel.id = pageID.menulevelID AND menulevel.content_status < 2", array($_GET['ID'], $field[3]));
if($o_query && $listlevels = $o_query->result_array())
{
	foreach($listlevels as $listlevel)
	{
		if(!in_array($listlevel['menulevelID'], $parents))
		{
			$parents[] = $listlevel['menulevelID'];
			$lastParent = $listlevel['menulevelID'];
		}
		
		for($a = ($listlevel['level'] - 1); $a >= 0; $a--)
		{
			$o_query = $o_main->db->query("SELECT parentlevelID FROM menulevel WHERE id = ? AND content_status < 2;", array($lastParent));
			if($o_query && $writeParent = $o_query->row_array()) {
				if(!in_array($writeParent['parentlevelID'], $parents)){
					$parents[] = $writeParent['parentlevelID'];				
					$lastParent = $writeParent['parentlevelID'];
				}
			}				
		}
	}  
}

$o_query = $o_main->db->query("SELECT MAX(level) FROM menulevel WHERE content_status < 2;");
if($o_query && $writeLevel = $o_query->row_array()) {}
$level = ($writeLevel[0] + 1);

$o_query = $o_main->db->query("SELECT moduleID FROM menulevel WHERE id = ? AND content_status < 2;", array($parents[0]));
if($o_query && $menuwrite = $o_query->row_array()) {}
if($menuwrite[0] != "")
{
	$parentMenu = $menuwrite[0];
}

if($settingsVar_definedMenuID_picturegallery >0) $parentMenu = $settingsVar_definedMenuID_picturegallery;
$moduleWhereSql = "";
$modulesCounter = 0;
foreach($modulesToShow as $moduleName)
{
	$moduleName = trim($moduleName);
	if($moduleName != "")
	{
		if($modulesCounter == 0)
		{
			$moduleWhereSql .= " AND ";
		} else {
			$moduleWhereSql .= " OR";
		}
		$moduleWhereSql .= " name = '".$moduleName."'";
		$modulesCounter++;
	}
}
$o_query = $o_main->db->query("SELECT * FROM moduledata WHERE type = '1'".$moduleWhereSql.";");
if($o_query && $parentMenus = $o_query->result_array()) {}
?>
<style>
.fieldname.linktype {
	color: #089ceb;
	cursor: pointer;
}
.parentMenuChoose {
	display: inline-block;
	vertical-align: middle;
	cursor: pointer;
	color: #089ceb;
	padding: 5px 10px;
}
.parentMenuChoose.active {
	color: #5D5D5D;
}
.menuchooseContent .menuItem {
	color: #089ceb;
	cursor: pointer;	
	padding: 4px 10px;
}
.menuchooseContent .menuItem.active {
	color: #fff;
	cursor: auto;
	background: #000;
}
.menulevel1 {
	margin-left: 30px;
}
.menulevel2 {
	margin-left: 60px;
}
.menulevel3 {
	margin-left: 90px;
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
</style>
<div class="fieldname linktype" data-toggle="modal" data-target="#<?=$field_ui_id?>menuPopup">
	<?=$formText_editMenuconnection_input;?>
</div>
<div class="contentMenuConnections" id="<?=$field_ui_id?>menuconnection" data-fielduniqueid="<?=$field_ui_id?>" >
	<?php foreach($parents as $parent){
		if($parent != "" && $parent != 0){
			$o_query = $o_main->db->query("SELECT * FROM menulevel WHERE id = ".$parent);
			if($o_query && $parentLevelItem = $o_query->row_array()) {}
			$parentLevel = $parentLevelItem['level'];
			$o_query = $o_main->db->query("SELECT * FROM moduledata WHERE moduledata.id = ".$parentLevelItem['moduleID']);
			if($o_query && $moduleItem = $o_query->row_array()) {}
			$moduleID = $moduleItem['id'];
			$parentEntry = fieldtype_get_menu_entry($parent, $s_default_output_language, $o_main);
		?>
		<div class="menuConnectionEntry" data-menuid="<?=$parent;?>" data-menumoduleid="<?=$moduleID?>">
			<?=$parentEntry;?>
			<button type="button" class="close" aria-label="Close"><span aria-hidden="true">×</span></button>
			<input type="hidden" id="<?=$field_ui_id?>level<?=$parentLevel?>" name="<?=$field[1].$ending."level0[]";?>" value="<?=$parentLevel;?>_<?=$parent;?>"/>
		</div>
		<?php } ?>
	<?php } ?>
</div>
<div class="modal fade in" id="<?=$field_ui_id?>menuPopup">
	<div class="modal-dialog modal-lg">
		<div class="modal-content allowScrollWrapper">
			<div class="modal-header">
				<div class="menuchooseTab">
					<?php 
					if($access>=10) {
						foreach($parentMenus as $parentMenu){
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
			</div>
			<div class="modal-body allowScroll">
				<div class="menuchooseContent">
					<?php foreach($parentMenus as $parentMenu) { ?>
						<div id="<?=$field_ui_id."menu".$parentMenu['id'];?>" class="<?=$field_ui_id."menu"?> popupmenucontent" data-fielduniqueid="<?=$field_ui_id?>"  data-menumoduleid="<?=$parentMenu['id']?>">
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
								fieldtype_print_menu($menulevel, $parentMenu, $orderby, $menuOutputName,$parents, $s_default_output_language, $o_main);	
							?>
						</div>
					<?php } ?>
				</div>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
	$("#<?=$field_ui_id?>menuconnection").parents(".twofield").prev(".twoinput").hide();

	$(".menuchooseContent .menuItem").unbind("click").bind("click", function(){
		var menuID = $(this).data("menuid");
		var menulevel = $(this).data("menulevel");
		var menuEntry = $(this).data("menuentry");
		var menuModuleID = $(this).parent().data("menumoduleid");
		var fieldUniqueID = $(this).parent().data("fielduniqueid");
		if(menuID != undefined && fieldUniqueID != undefined && menuEntry != undefined){
			if(!$(this).hasClass("active")){
				if($("#"+fieldUniqueID+"menuconnection .menuConnectionEntry[data-menuid="+menuID+"]").length == 0){
					while(menulevel > 0){
						var parentItems = $(this).prevAll(".menulevel"+(menulevel-1));
						if(parentItems.length > 0){
							var parentItem = $(parentItems[0]);
							if(!parentItem.hasClass("active")){
								var parentMenuID = parentItem.data("menuid");
								var parentMenulevel = parentItem.data("menulevel");
								var parentMenuEntry = parentItem.data("menuentry");
								$("#"+fieldUniqueID+"menuconnection").append('<div class="menuConnectionEntry" data-menuid="'+parentMenuID+'" data-menumoduleid="'+menuModuleID+'">'
									+parentMenuEntry+'<button type="button" class="close" aria-label="Close"><span aria-hidden="true">×</span></button><input type="hidden" id="<?=$field_ui_id?>level'+parentMenulevel+'" name="<?=$field[1].$ending."level0[]";?>" value="'+parentMenulevel+"_"+parentMenuID+'"/></div>');
								parentItem.addClass("active");
							}
						}
						menulevel--;
					}
					$("#"+fieldUniqueID+"menuconnection").append('<div class="menuConnectionEntry" data-menuid="'+menuID+'" data-menumoduleid="'+menuModuleID+'">'
						+menuEntry+'<button type="button" class="close" aria-label="Close"><span aria-hidden="true">×</span></button><input type="hidden" id="<?=$field_ui_id?>level'+menulevel+'" name="<?=$field[1].$ending."level0[]";?>" value="'+menulevel+"_"+menuID+'"/></div>');
					
					$(this).addClass("active");
				}
				bindRemoveEntry();
			}
		}
	})
	$(".menuchooseTab").each(function(index, el){
		$(el).find(".parentMenuChoose").first().addClass("active");
	})
	$(".<?=$field_ui_id;?>").unbind("click").bind("click", function(){
		var parent = $(this).parents(".modal-content");
		var menuModuleID = $(this).data("menumoduleid");
		parent.find(".parentMenuChoose").removeClass("active");
		$(this).addClass("active");
		parent.find(".popupmenucontent").hide();
		parent.find(".popupmenucontent[data-menumoduleid="+menuModuleID+"]").show();
	})
	$(".parentMenuChoose.active").click();
	function bindRemoveEntry(){		
		$(".menuConnectionEntry .close").unbind("click").bind("click", function(){
			var parent =$(this).parent();
			var menuModuleID = parent.data("menumoduleid");
			var menuID = parent.data("menuid");
			var fieldUniqueID = parent.parent().data("fielduniqueid");
			var menuItemInPopup = $("#"+fieldUniqueID + "menu"+menuModuleID+" .menuItem[data-menuid="+menuID+"]");
			if(menuItemInPopup.length > 0){
				menuItemInPopup.removeClass("active");
				parent.remove();
			}
		})
	}
	bindRemoveEntry();
</script>