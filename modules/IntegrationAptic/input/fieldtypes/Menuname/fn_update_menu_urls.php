<?php
function update_menu_urls($modRewriteRow, $char_map, $s_list_prefix, $splitter = ' ')
{
	$o_main = get_instance();
	
	if('' == trim($splitter)) $splitter = ' ';
	$b_empty_url = ($modRewriteRow['urlrewrite'] == "");
	$langID = $modRewriteRow['lang_url_part'];
	$content_name = $modRewriteRow['content_url_part'];
	
	$modRewriteRow['levelname'] = get_menulevel_parrents($modRewriteRow['menulevelID'], $modRewriteRow['languageID'], $splitter).$modRewriteRow['levelname'];
	$level_name = strtolower(str_replace(array_keys($char_map), $char_map,trim($modRewriteRow['levelname'])));
	if('' == trim($splitter)) $level_name = str_replace('/','-',$level_name);
	$level_name = preg_replace('/\-[\-]+/', '-', preg_replace('/[^A-za-z0-9_\/-]+/', '', $level_name."/"));
	$modRewriteName = $langID.$level_name.$content_name;
	$modRewriteList = rtrim($langID.$s_list_prefix.$level_name, '/');
	
	if($modRewriteRow['menulevelID'] > 0 && $modRewriteRow['id'] > 0)
	{
		$o_check = $o_main->db->query("select id from pageIDlist where menulevelID = ? and languageID = ?", array($modRewriteRow['menulevelID'], $modRewriteRow['languageID']));
		if($o_check && $o_check->num_rows()>0)
		{
			$s_sql = "update pageIDlist set listurl = ? where menulevelID = ? and languageID = ?";
			$o_main->db->query($s_sql, array($modRewriteList, $modRewriteRow['menulevelID'], $modRewriteRow['languageID']));
		} else {
			$s_sql = "insert into pageIDlist (menulevelID, languageID, listurl) values(?, ?, ?);";
			$o_main->db->query($s_sql, array($modRewriteRow['menulevelID'], $modRewriteRow['languageID'], $modRewriteList));
		}
		
		$b_handle_content = TRUE;
		$b_handle_multiconnection = FALSE;
		$s_sql = "SELECT id FROM pageID WHERE contentID = ? AND contentTable = ? AND menulevelID > 0 AND deleted != 1";
		$o_check = $o_main->db->query($s_sql, array($modRewriteRow['contentID'], $modRewriteRow['contentTable']));
		if($o_check && $o_check->num_rows()>1)
		{
			$b_handle_multiconnection = TRUE;
			$s_sql = "SELECT id FROM ".$o_main->db_escape_name($modRewriteRow['contentTable'])." WHERE id = ? AND menulevel = ?";
			$o_check = $o_main->db->query($s_sql, array($modRewriteRow['contentID'], $modRewriteRow['menulevelID']));
			if($o_check && $o_check->num_rows()>0)
			{} else {
				$b_handle_content = FALSE;
			}
		}
		//Do not update records where full url edit enabled
		if(1 == $modRewriteRow['full_url_edit']) $b_handle_content = FALSE;
		
		if($b_handle_content)
		{
			if($level_name!="")
			{
				$v_tmp = explode('/',$modRewriteName);
				$s_content = array_pop($v_tmp);
				$s_menu = array_pop($v_tmp);
				if(strpos($s_content,$s_menu)!==false)
				{
					$modRewriteName = $langID.$content_name;
					$level_name = "";
				}
			}
			
			$rand = "";
			if($modRewriteList == $modRewriteName || (!$b_empty_url && $modRewriteName == ""))
			{
				$i=1;
				$rand="-$i";
			}
			$s_sql = "select pc.id from pageIDcontent pc where urlrewrite = ? and pc.pageIDID not in (select p.id from pageID p where p.contentID = ? and p.contentTable = ?)";
			$o_check = $o_main->db->query($s_sql, array($modRewriteName.$rand, $modRewriteRow['contentID'], $modRewriteRow['contentTable']));
			while($o_check && $o_check->num_rows() > 0)
			{
				$i++;
				$rand="-$i";
				$o_check = $o_main->db->query($s_sql, array($modRewriteName.$rand, $modRewriteRow['contentID'], $modRewriteRow['contentTable']));
			}
			
			if($b_handle_multiconnection)
			{
				$v_sql_param = array($modRewriteName.$rand, $langID, $level_name, $content_name.$rand, $modRewriteRow['contentID'], $modRewriteRow['contentTable'], $modRewriteRow['languageID']);
				$modRewriteSql = "UPDATE pageIDcontent pc INNER JOIN pageID p ON p.id = pc.pageIDID SET pc.urlrewrite = ?, pc.lang_url_part = ?, pc.menu_url_part = ?, pc.content_url_part = ? WHERE p.contentID = ? AND p.contentTable = ? AND pc.languageID = ?";
			} else {
				$v_sql_param = array($modRewriteName.$rand, $langID, $level_name, $content_name.$rand, $modRewriteRow['id'], $modRewriteRow['languageID']);
				$modRewriteSql = "update pageIDcontent set urlrewrite = ?, lang_url_part = ?, menu_url_part = ?, content_url_part = ? where pageIDID = ? and languageID = ?";
			}
			$o_main->db->query($modRewriteSql, $v_sql_param);
		}
	}
	
	// Recursive for child elements
	$modRewriteSql = "SELECT pageID.id, menulevelcontent.menulevelID, pageID.contentID, pageID.contentTable, menulevelcontent.languageID, pageIDcontent.urlrewrite, pageIDcontent.lang_url_part, pageIDcontent.menu_url_part, pageIDcontent.content_url_part, pageIDcontent.full_url_edit, menulevelcontent.levelname, pageIDlist.menu_url_splitter FROM menulevelcontent JOIN menulevel AS menulevel_childs ON menulevel_childs.id = menulevelcontent.menulevelID LEFT JOIN pageID ON menulevelcontent.menulevelID = pageID.menulevelID LEFT JOIN pageIDcontent ON pageIDcontent.pageIDID = pageID.id AND menulevelcontent.languageID = pageIDcontent.languageID LEFT OUTER JOIN pageIDlist ON pageIDlist.menulevelID = pageID.menulevelID WHERE menulevel_childs.parentlevelID = ? AND menulevel_childs.parentlevelID > 0 AND menulevelcontent.languageID = ? ORDER BY pageID.id";
	$o_query = $o_main->db->query($modRewriteSql, array($modRewriteRow['menulevelID'], $modRewriteRow['languageID']));
	if($o_query && $o_query->num_rows()>0)
	foreach($o_query->result_array() as $modRewriteRow)
	{
		$modRewriteRow['menu_url_splitter'] = trim($modRewriteRow['menu_url_splitter']);
		$s_list_prefix = "list".('' == $modRewriteRow['menu_url_splitter'] ? '-' : $modRewriteRow['menu_url_splitter']);
		if(strtolower($modRewriteRow['languageID']) == 'no') $s_list_prefix = "liste".('' == $modRewriteRow['menu_url_splitter'] ? '-' : $modRewriteRow['menu_url_splitter']);
		$o_find = $o_main->db->query("SELECT list_url_prefix FROM language WHERE languageID = ?", array($modRewriteRow['languageID']));
		if($o_find && $o_row = $o_find->row())
		{
			if($o_row->list_url_prefix != "")
			{
				$s_list_prefix = $o_row->list_url_prefix.(substr($o_row->list_url_prefix, -1) == "-" ? "" : ('' == $modRewriteRow['menu_url_splitter'] ? '-' : $modRewriteRow['menu_url_splitter']));
			}
		}
		
		update_menu_urls($modRewriteRow, $char_map, $s_list_prefix, $splitter);
	}
}