<?php
function fieldtype_print_menu($menulevel, $parentMenu, $orderby, $menuOutputName, $parents, $s_default_output_language, $o_main, $parentMenuID = 0)
{		
	$extra_where= "";
	if($parentMenuID != 0)
	{
		$extra_where = " AND menulevel.parentlevelID = ".$o_main->db->escape($parentMenuID);
	}
	$sql = "SELECT menulevel.id as lID, menulevel.*, menulevelcontent.* 
	FROM menulevel, menulevelcontent 
	WHERE menulevel.moduleID = ".$o_main->db->escape($parentMenu['id'])." AND menulevelcontent.languageID = ".$o_main->db->escape($s_default_output_language)." 
	AND menulevel.level = ".$o_main->db->escape($menulevel)." AND menulevelcontent.menulevelID = menulevel.id $extra_where AND menulevel.content_status < 2 
	$orderby;";
	$o_query = $o_main->db->query($sql);
	if($o_query)
	{
		$resultCount = $o_query->num_rows();
	}
	if($resultCount > 0)
	{
		$results = $o_query->result_array();
		foreach($results as $currentMenu)
		{
			$menuOutputName[$menulevel] = $currentMenu['levelname'];
			?>
			<div class="menuItem menulevel<?=$menulevel?><?php if(in_array($currentMenu['lID'], $parents)) echo ' active';?>" data-menuid="<?=$currentMenu['lID']?>"
				data-menuentry="<?=implode(" / ", $menuOutputName)?>" data-menulevel="<?=$menulevel;?>">
				<?=$currentMenu['levelname']?>
			</div>
			<?php
			fieldtype_print_menu($menulevel+1, $parentMenu, $orderby, $menuOutputName, $parents, $s_default_output_language, $o_main, $currentMenu['lID']);				
		}
	}
}
?>