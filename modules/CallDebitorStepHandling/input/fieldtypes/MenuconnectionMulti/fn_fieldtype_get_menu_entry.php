<?php
function fieldtype_get_menu_entry($menuId, $s_default_output_language, $o_main)
{
	$o_query = $o_main->db->query("SELECT * FROM menulevel 
		LEFT OUTER JOIN menulevelcontent ON menulevelcontent.menulevelID = menulevel.id AND menulevelcontent.languageID = ".$o_main->db->escape($s_default_output_language)." 
		WHERE menulevel.id = ".$o_main->db->escape($menuId));
	if($o_query && $currentMenuItem = $o_query->row_array())
	{
		$o_query = $o_main->db->query("SELECT * FROM moduledata WHERE id = ".$o_main->db->escape($currentMenuItem['moduleID']));
		if($o_query && $moduleItem = $o_query->row_array())
		{		
			$menuEntry[-1]=$moduleItem['name'];
			$currentMenuLevel = $currentMenuItem['level'];
			for($x = $currentMenuLevel; $x >= 0; $x--)
			{
				if($x != $currentMenuLevel)
				{
					$o_query = $o_main->db->query("SELECT * FROM menulevel 
					LEFT OUTER JOIN menulevelcontent ON menulevelcontent.menulevelID = menulevel.id AND menulevelcontent.languageID = ".$o_main->db->escape($s_default_output_language)." 
					WHERE menulevel.id = ".$o_main->db->escape($currentMenuItem['parentlevelID']));
					if($o_query && $currentMenuItem = $o_query->row_array()) {}
				}
				$menuEntry[$x] = $currentMenuItem['levelname'];
			}
			ksort($menuEntry);
			return implode(" / ", $menuEntry);
		}
	}
}
