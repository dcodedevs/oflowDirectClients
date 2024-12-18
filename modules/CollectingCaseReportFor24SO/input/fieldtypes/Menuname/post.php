<?php
include(__DIR__.'/check_db_config.php');
if(!function_exists("update_menu_urls")) include(__DIR__."/fn_update_menu_urls.php");
if(!function_exists("get_menulevel_parrents")) include(__DIR__."/fn_get_menulevel_parrents.php");
		
$char_map = array(
// Special
' ' => '-',
// Latin
'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'AE', 'Ç' => 'C',
'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I',
'Ð' => 'D', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ő' => 'O',
'Ø' => 'O', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ű' => 'U', 'Ý' => 'Y', 'Þ' => 'TH',
'ß' => 'ss',
'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'ae', 'ç' => 'c',
'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
'ð' => 'd', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ő' => 'o',
'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u', 'ű' => 'u', 'ý' => 'y', 'þ' => 'th',
'ÿ' => 'y',
// Latin symbols
'©' => '(c)',
// Greek
'Α' => 'A', 'Β' => 'B', 'Γ' => 'G', 'Δ' => 'D', 'Ε' => 'E', 'Ζ' => 'Z', 'Η' => 'H', 'Θ' => '8',
'Ι' => 'I', 'Κ' => 'K', 'Λ' => 'L', 'Μ' => 'M', 'Ν' => 'N', 'Ξ' => '3', 'Ο' => 'O', 'Π' => 'P',
'Ρ' => 'R', 'Σ' => 'S', 'Τ' => 'T', 'Υ' => 'Y', 'Φ' => 'F', 'Χ' => 'X', 'Ψ' => 'PS', 'Ω' => 'W',
'Ά' => 'A', 'Έ' => 'E', 'Ί' => 'I', 'Ό' => 'O', 'Ύ' => 'Y', 'Ή' => 'H', 'Ώ' => 'W', 'Ϊ' => 'I',
'Ϋ' => 'Y',
'α' => 'a', 'β' => 'b', 'γ' => 'g', 'δ' => 'd', 'ε' => 'e', 'ζ' => 'z', 'η' => 'h', 'θ' => '8',
'ι' => 'i', 'κ' => 'k', 'λ' => 'l', 'μ' => 'm', 'ν' => 'n', 'ξ' => '3', 'ο' => 'o', 'π' => 'p',
'ρ' => 'r', 'σ' => 's', 'τ' => 't', 'υ' => 'y', 'φ' => 'f', 'χ' => 'x', 'ψ' => 'ps', 'ω' => 'w',
'ά' => 'a', 'έ' => 'e', 'ί' => 'i', 'ό' => 'o', 'ύ' => 'y', 'ή' => 'h', 'ώ' => 'w', 'ς' => 's',
'ϊ' => 'i', 'ΰ' => 'y', 'ϋ' => 'y', 'ΐ' => 'i',
// Turkish
'Ş' => 'S', 'İ' => 'I', 'Ç' => 'C', 'Ü' => 'U', 'Ö' => 'O', 'Ğ' => 'G',
'ş' => 's', 'ı' => 'i', 'ç' => 'c', 'ü' => 'u', 'ö' => 'o', 'ğ' => 'g',
// Russian
'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'Yo', 'Ж' => 'Zh',
'З' => 'Z', 'И' => 'I', 'Й' => 'J', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O',
'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C',
'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Sh', 'Ъ' => '', 'Ы' => 'Y', 'Ь' => '', 'Э' => 'E', 'Ю' => 'Yu',
'Я' => 'Ya',
'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'yo', 'ж' => 'zh',
'з' => 'z', 'и' => 'i', 'й' => 'j', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o',
'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c',
'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sh', 'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu',
'я' => 'ya',
// Ukrainian
'Є' => 'Ye', 'І' => 'I', 'Ї' => 'Yi', 'Ґ' => 'G',
'є' => 'ye', 'і' => 'i', 'ї' => 'yi', 'ґ' => 'g',
// Czech
'Č' => 'C', 'Ď' => 'D', 'Ě' => 'E', 'Ň' => 'N', 'Ř' => 'R', 'Š' => 'S', 'Ť' => 'T', 'Ů' => 'U',
'Ž' => 'Z',
'č' => 'c', 'ď' => 'd', 'ě' => 'e', 'ň' => 'n', 'ř' => 'r', 'š' => 's', 'ť' => 't', 'ů' => 'u',
'ž' => 'z',
// Polish
'Ą' => 'A', 'Ć' => 'C', 'Ę' => 'e', 'Ł' => 'L', 'Ń' => 'N', 'Ó' => 'o', 'Ś' => 'S', 'Ź' => 'Z',
'Ż' => 'Z',
'ą' => 'a', 'ć' => 'c', 'ę' => 'e', 'ł' => 'l', 'ń' => 'n', 'ó' => 'o', 'ś' => 's', 'ź' => 'z',
'ż' => 'z',
// Latvian
'Ā' => 'A', 'Č' => 'C', 'Ē' => 'E', 'Ģ' => 'G', 'Ī' => 'i', 'Ķ' => 'k', 'Ļ' => 'L', 'Ņ' => 'N',
'Š' => 'S', 'Ū' => 'u', 'Ž' => 'Z',
'ā' => 'a', 'č' => 'c', 'ē' => 'e', 'ģ' => 'g', 'ī' => 'i', 'ķ' => 'k', 'ļ' => 'l', 'ņ' => 'n',
'š' => 's', 'ū' => 'u', 'ž' => 'z',
//Iceland
'Ó' => 'O', 'ó' => 'o'
);

$modRewriteSql = "SELECT pageID.id, menulevelcontent.menulevelID, pageID.contentID, pageID.contentTable, menulevelcontent.languageID, pageIDcontent.urlrewrite, pageIDcontent.lang_url_part, pageIDcontent.menu_url_part, pageIDcontent.content_url_part, menulevelcontent.levelname, pageIDlist.menu_url_splitter FROM menulevelcontent LEFT JOIN pageID ON menulevelcontent.menulevelID = pageID.menulevelID LEFT JOIN pageIDcontent ON pageIDcontent.pageIDID = pageID.id AND menulevelcontent.languageID = pageIDcontent.languageID LEFT OUTER JOIN pageIDlist ON pageIDlist.menulevelID = pageID.menulevelID WHERE menulevelcontent.menulevelID = ? ORDER BY pageID.id";
$o_query = $o_main->db->query($modRewriteSql, array($ID));
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
	
	update_menu_urls($modRewriteRow, array_keys($char_map), $char_map, $s_list_prefix, $modRewriteRow['menu_url_splitter']);
}

$o_main->db->query("TRUNCATE TABLE sys_htaccess");

$v_batch_insert = array();
$modRewriteSql = "SELECT p.id, p.menulevelID, p.contentID, pc.languageID, pc.urlrewrite, mc.levelname, pl.listurl FROM pageID p JOIN pageIDcontent pc ON pc.pageIDID = p.id LEFT OUTER JOIN menulevelcontent mc ON mc.menulevelID = p.menulevelID AND mc.languageID = pc.languageID LEFT OUTER JOIN pageIDlist pl ON pl.menulevelID = p.menulevelID AND pl.languageID = pc.languageID WHERE p.deleted != 1 AND p.contentID > 0 ORDER BY p.id";
$o_query = $o_main->db->query($modRewriteSql);
if($o_query && $o_query->num_rows()>0)
foreach($o_query->result_array() as $modRewriteRow)
{
	$v_batch_insert[] = array(
		'pageID' => $modRewriteRow['id'],
		'languageID' => $modRewriteRow['languageID'],
		'urlfrom' => trim($modRewriteRow['urlrewrite'],"/")."/",
		'urlto' => "index.php?pageID=".$modRewriteRow['id']."&openLevel=".$modRewriteRow['menulevelID']."&langID=".$modRewriteRow['languageID'],
	);
	//$o_main->db->query("INSERT INTO sys_htaccess (pageID, languageID, urlfrom, urlto) VALUES (?, ?, ?, ?)", array($modRewriteRow['id'], $modRewriteRow['languageID'], trim($modRewriteRow['urlrewrite'],"/")."/", "index.php?pageID=".$modRewriteRow['id']."&openLevel=".$modRewriteRow['menulevelID']."&langID=".$modRewriteRow['languageID']));
	if($modRewriteRow['listurl'] != "")
	{
		$v_batch_insert[] = array(
			'pageID' => $modRewriteRow['id'],
			'languageID' => $modRewriteRow['languageID'],
			'urlfrom' => $modRewriteRow['listurl'],
			'urlto' => "index.php?pageID=".$modRewriteRow['id']."&openLevel=".$modRewriteRow['menulevelID']."&langID=".$modRewriteRow['languageID']."&showList=1",
		);
		//$o_main->db->query("INSERT INTO sys_htaccess (pageID, languageID, urlfrom, urlto) VALUES (?, ?, ?, ?)", array($modRewriteRow['id'], $modRewriteRow['languageID'], $modRewriteRow['listurl'], "index.php?pageID=".$modRewriteRow['id']."&openLevel=".$modRewriteRow['menulevelID']."&langID=".$modRewriteRow['languageID']."&showList=1"));
	}
	
	if(count($v_batch_insert) > 1000)
	{
		$o_main->db->insert_batch('sys_htaccess', $v_batch_insert);
		$v_batch_insert = array();
	}
}
if(count($v_batch_insert) > 0)
{
	$o_main->db->insert_batch('sys_htaccess', $v_batch_insert);
	$v_batch_insert = array();
}

// Update SEO data
if($fields[$nums][11] == 1)
{
	$seo_moduleID = '';
	$moduleID = $_POST['moduleID'];
	$o_query = $o_main->db->query("SELECT id FROM moduledata WHERE name = 'SEO'");
	if($o_query && $o_row = $o_query->row()) $seo_moduleID = $o_row->id;
	
	$o_query = $o_main->db->query('SELECT * FROM menulevel JOIN menulevelcontent ON menulevelcontent.menulevelID = menulevel.id WHERE menulevel.id = ?', array($ID));
	if($o_query && $o_query->num_rows()>0)
	foreach($o_query->result_array() as $menu)
	{
		$o_check = $o_main->db->query("SELECT id FROM seodata WHERE menulevelID = ? AND menuID = ?", array($ID, $menu['moduleID']));
		if($o_query && $o_query->num_rows()>0)
		{
			$o_row = $o_check->row();
			$insertID = $o_row->id;
		} else {
			$o_main->db->query("INSERT INTO seodata (moduleID, contentID, contentModuleID, menuID, menulevelID) VALUES (?, ?, ?, ?, ?)", array($seo_moduleID, 0, 0, $menu['moduleID'], $ID));
			$insertID = $o_main->db->insert_id();
		}
		$o_check = $o_main->db->query("select id from seodatacontent where seodataID = ? and languageID = ", array($insertID, $menu['languageID']));
		if($o_query && $o_query->num_rows()>0)
		{
			$o_main->db->query("UPDATE seodatacontent SET seoTitle = ?".(isset($menu['seoDescription']) ? ", seoDescription = ".$o_main->db->escape($menu['seoDescription']) : '')." WHERE seodataID = ? AND languageID = ?", array($menu['levelname'], $insertID, $menu['languageID']));
		} else {
			$o_main->db->query("INSERT INTO seodatacontent (seodataID, languageID, seoTitle, seoDescription) VALUES(?, ?, ?, ?)", array($insertID, $menu['languageID'], $menu['levelname'], (isset($menu['seoDescription']) ? $o_main->db->escape($menu['seoDescription']) : '')));
		}
	}
}