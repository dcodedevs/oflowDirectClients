<?php
// get single-language table
$s_single_table = ($basetable->multilanguage == 1 ? substr($basetable->name,0,-7) : $basetable->name);

if(!function_exists("get_menulevel_parrents")) include(__DIR__."/fn_get_menulevel_parrents.php");

$startsearch = 0;
$v_connected_menues = array();
$startname = $fields[$nums][1]."level".$startsearch;
while(isset($_POST[$startname]))
{
	foreach($_POST[$startname] as $l_menulevel_id)
	{
		if($l_menulevel_id != "")
		{
			if(strpos($l_menulevel_id,"_") !== FALSE)
			{
				$l_menulevel_id = explode("_", $l_menulevel_id);
				$l_menulevel_id = $l_menulevel_id[1];
			}
			$v_connected_menues[] = $l_menulevel_id;
		}
	}
	$startsearch++;
	$startname = $fields[$nums][1]."level".$startsearch;
}

$o_main->db->query("UPDATE pageID SET deleted = 1 WHERE contentID = ? AND contentTable = ?", array($basetable->ID, $s_single_table));
foreach($v_connected_menues as $l_menulevel_id)
{
	$o_query = $o_main->db->query('SELECT id FROM pageID WHERE contentID = ? AND contentTable = ? AND deleted = 1', array($basetable->ID, $s_single_table));
	if($o_query && $o_query->num_rows()>0)
	{
		$v_old_row = $o_query->row_array();
		$o_main->db->query("UPDATE pageID SET menulevelID = ?, deleted = 0 WHERE id = ?", array($l_menulevel_id, $v_old_row['id']));
	} else {
		$o_main->db->query("INSERT INTO pageID(contentID, contentTable, menulevelID) VALUES(?, ?, ?)", array($basetable->ID, $s_single_table, $l_menulevel_id));
	}
}
$o_main->db->query("DELETE pageIDcontent FROM pageIDcontent INNER JOIN pageID ON pageID.id = pageIDcontent.pageIDID WHERE pageID.contentID = ? AND pageID.contentTable = ? AND pageID.deleted = 1", array($basetable->ID, $s_single_table));
$o_main->db->query("DELETE pageID FROM pageID WHERE contentID = ? AND contentTable = ? AND deleted = 1", array($basetable->ID, $s_single_table));

$s_default_output_language = "";
$o_query = $o_main->db->query("SELECT languageID FROM language ORDER BY defaultOutputlanguage DESC, outputlanguage DESC, sortnr ASC");
if($o_query && $o_row = $o_query->row()) $s_default_output_language = $o_row->languageID;

unset($curField);
$inputName = 'seourl';
list($r,$r) = explode(",",str_replace(" ","",$fields[$nums][11]));
foreach($fields as $item)
{
	if($item[0]==$inputName) { $curField = $item; break; }
}

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

$o_query = $o_main->db->query('SELECT id FROM language WHERE outputlanguage = 1');
if($o_query && $o_query->num_rows()>1) $doMultiLanguage = true; else $doMultiLanguage = false;

if(isset($curField))
{
	$b_single_lang_url = (array_key_exists("all",$curField[6]) ? true : false);
	//check if pageIDcontent is set
	if(!$o_main->db->table_exists('pageIDcontent'))
	{
		$o_main->db->simple_query("CREATE TABLE `pageIDcontent` (
			`id` INT(11) NOT NULL AUTO_INCREMENT,
			`pageIDID` INT(11) NOT NULL,
			`languageID` CHAR(50) NOT NULL,
			`urlrewrite` VARCHAR(255) NOT NULL,
			PRIMARY KEY (`id`),
			INDEX `Relation` (`pageIDID`, `languageID`),
			INDEX `Search2` (`urlrewrite`)
		)");
	}
	$o_main->db->simple_query("ALTER TABLE `pageIDcontent` DROP INDEX `Search`, ADD INDEX `Search2` (`urlrewrite`);");
	$o_main->db->simple_query("ALTER TABLE `pageIDcontent`
					ADD COLUMN `lang_url_part` CHAR(10) NOT NULL AFTER `urlrewrite`,
					ADD COLUMN `menu_url_part` CHAR(100) NOT NULL AFTER `lang_url_part`,
					ADD COLUMN `content_url_part` CHAR(100) NOT NULL AFTER `menu_url_part`;");
	$o_main->db->simple_query("ALTER TABLE `pageIDcontent` ADD COLUMN `menu_url_splitter` CHAR(10) NOT NULL AFTER `content_url_part`;");
	if(!$o_main->db->table_exists('sys_htaccess'))
	{
		$o_main->db->simple_query("CREATE TABLE sys_htaccess (
			pageID INT(11) NOT NULL,
			languageID CHAR(50) NOT NULL,
			urlfrom VARCHAR(1000) NOT NULL,
			urlto VARCHAR(1000) NOT NULL,
			redirect TINYINT NOT NULL DEFAULT 0,
			INDEX Relation (pageID, languageID),
			INDEX RelationUrl (urlfrom(255))
		)");
	}
	if(!$o_main->db->table_exists('pageIDlist'))
	{
		$o_main->db->simple_query("CREATE TABLE pageIDlist (
			id INT(11) NOT NULL AUTO_INCREMENT,
			menulevelID INT(11) NOT NULL,
			languageID CHAR(10) NOT NULL,
			listurl CHAR(255) NOT NULL,
			PRIMARY KEY (id),
			INDEX Relation (menulevelID, languageID),
			INDEX Search2 (listurl)
		)");
	}
	$o_main->db->simple_query("ALTER TABLE `pageIDlist` ADD COLUMN `menu_url_splitter` CHAR(10) NOT NULL AFTER `listurl`;");
	
	$moduleID = $_POST['moduleID'];
	$row = array();
	$o_query = $o_main->db->query("select id from moduledata where name = 'SEO'");
	if($o_query && $o_query->num_rows()>0) $row = $o_query->row_array();
	$seo_moduleID = $row['id'];
	
	//check if single/multi language field
	if($curField[3]==$s_single_table) {
		$modRewriteSql = "select {$s_single_table}.{$curField[0]} url_rewrite_name, {$s_single_table}.seotitle, {$s_single_table}.seodescription, pageID.menulevelID, menulevelcontent.levelname, ".$o_main->db->escape($s_default_output_language)." languageID, pageID.id pageID from pageID join {$s_single_table} on {$s_single_table}.id = pageID.contentID AND pageID.contentTable = ".$o_main->db->escape($s_single_table)." left outer join menulevelcontent on pageID.menulevelID = menulevelcontent.menulevelID AND menulevelcontent.languageID = ".$o_main->db->escape($s_default_output_language)." WHERE {$s_single_table}.id = ".$o_main->db->escape($basetable->ID)." AND pageID.deleted != 1";
		$s_seo_field_cleanup_sql = "update {$s_single_table} set seourl='', seotitle='', seodescription = '' where id = ".$o_main->db->escape($basetable->ID);
	} else {
		$modRewriteSql = "select {$s_single_table}content.{$curField[0]} url_rewrite_name, {$s_single_table}content.seotitle, {$s_single_table}content.seodescription, pageID.menulevelID, menulevelcontent.levelname, {$s_single_table}content.languageID, pageID.id pageID from pageID join {$s_single_table}content on {$s_single_table}content.{$s_single_table}ID = pageID.contentID AND pageID.contentTable = ".$o_main->db->escape($s_single_table)." left outer join menulevelcontent on pageID.menulevelID = menulevelcontent.menulevelID AND {$s_single_table}content.languageID = menulevelcontent.languageID WHERE {$s_single_table}content.{$s_single_table}ID = ".$o_main->db->escape($basetable->ID)." AND pageID.deleted != 1";
		$s_seo_field_cleanup_sql = "update {$s_single_table}content set seourl='', seotitle='', seodescription = '' where {$s_single_table}ID = ".$o_main->db->escape($basetable->ID);
	}
	//print $modRewriteSql;
	
	$o_query = $o_main->db->query($modRewriteSql);
	if($o_query && $o_query->num_rows()>0)
	foreach($o_query->result_array() as $modRewriteRow)
	{
		$s_list_prefix = "list".('' == trim($seoUrlMenuSplitter) ? '-' : trim($seoUrlMenuSplitter));
		if(strtolower($modRewriteRow['languageID']) == 'no') $s_list_prefix = "liste".('' == trim($seoUrlMenuSplitter) ? '-' : trim($seoUrlMenuSplitter));
		$o_find = $o_main->db->query("SELECT list_url_prefix FROM language WHERE languageID = ?", array($modRewriteRow['languageID']));
		if($o_find && $o_row = $o_find->row())
		{
			if($o_row->list_url_prefix != "")
			{
				$s_list_prefix = $o_row->list_url_prefix.(substr($o_row->list_url_prefix, -1) == "-" ? "" : ('' == trim($seoUrlMenuSplitter) ? '-' : trim($seoUrlMenuSplitter)));
			}
		}
		
		if($doMultiLanguage) $langID = $modRewriteRow['languageID']."/"; else $langID = "";
		$modRewriteRow['levelname'] = get_menulevel_parrents($modRewriteRow['menulevelID'], $modRewriteRow['languageID'], $seoUrlMenuSplitter).$modRewriteRow['levelname'];
		$levelName = strtolower(str_replace(array_keys($char_map), $char_map, trim($modRewriteRow['levelname'])));
		if('' == trim($seoUrlMenuSplitter)) $levelName = str_replace('/','-',$levelName);
		$modRewriteList = preg_replace('/\-[\-]+/','-',preg_replace('/[^A-za-z0-9_\/-]+/','',rtrim($langID.$s_list_prefix.$levelName, '/')));
		if('' != trim($seoUrlMenuSplitter)) $modRewriteList = preg_replace('~'.trim($seoUrlMenuSplitter).'+~',trim($seoUrlMenuSplitter),$modRewriteList);
		$s_content_url = strtolower(str_replace(array_keys($char_map), $char_map, trim($modRewriteRow['url_rewrite_name'])));
		$modRewriteName = preg_replace('/\-[\-]+/','-',preg_replace('/[^A-za-z0-9_\/-]+/','',$s_content_url));
		
		if($modRewriteRow['menulevelID'] > 0)
		{
			$o_check = $o_main->db->query("select id from pageIDlist where menulevelID = ? and languageID = ?", array($modRewriteRow['menulevelID'], $modRewriteRow['languageID']));
			if($o_check && $o_check->num_rows()>0)
			{
				$s_sql = "update pageIDlist set listurl = ?, menu_url_splitter = ? where menulevelID = ? and languageID = ?";
				$o_main->db->query($s_sql, array($modRewriteList, $seoUrlMenuSplitter, $modRewriteRow['menulevelID'], $modRewriteRow['languageID']));
			} else {
				$s_sql = "insert into pageIDlist (menulevelID, menu_url_splitter, languageID, listurl) values(?, ?, ?);";
				$o_main->db->query($s_sql, array($modRewriteRow['menulevelID'], $seoUrlMenuSplitter, $modRewriteRow['languageID'], $modRewriteList));
			}
		}
		
		$i=0;
		$rand = "";
		if($modRewriteList == $modRewriteName || ($enableEmptySeoUrl != 1 && $modRewriteName == ""))
		{
			$i=1;
			$rand="-$i";
		}
		$s_sql = "select pc.id from pageIDcontent pc where urlrewrite = ? and pc.pageIDID not in (select p.id from pageID p where p.contentID = ? and p.contentTable = ?)";
		$o_check = $o_main->db->query($s_sql, array($modRewriteName.$rand, $basetable->ID, $s_single_table));
		while($o_check && $o_check->num_rows()>0)
		{
			$i++;
			$rand="-$i";
			$o_check = $o_main->db->query($s_sql, array($modRewriteName.$rand, $basetable->ID, $s_single_table));
		}
		
		$s_key = $modRewriteRow['languageID'];
		if($b_single_lang_url) $s_key = "";
		
		$s_lang_url_part = $_POST[$submodule."seourl".$s_key."lang"];
		$s_menu_url_part = $_POST[$submodule."seourl".$s_key."menu"];
		$s_content_url_part = $_POST[$submodule."seourl".$s_key."content"];
		
		$o_check = $o_main->db->query("select id from pageIDcontent where pageIDID = ? and languageID = ?", array($modRewriteRow['pageID'], $modRewriteRow['languageID']));
		if($o_check && $o_check->num_rows()>0)
		{
			$o_main->db->query("update pageIDcontent set urlrewrite = ?, lang_url_part = ?, menu_url_part = ?, content_url_part = ?, menu_url_splitter = ? where pageIDID = ? and languageID = ?", array($modRewriteName.$rand, $s_lang_url_part, $s_menu_url_part, $s_content_url_part, $seoUrlMenuSplitter, $modRewriteRow['pageID'], $modRewriteRow['languageID']));
		} else {
			$o_main->db->query("insert into pageIDcontent (pageIDID, languageID, urlrewrite, lang_url_part, menu_url_part, content_url_part, menu_url_splitter) values(?, ?, ?, ?, ?, ?, ?)", array($modRewriteRow['pageID'], $modRewriteRow['languageID'], $modRewriteName.$rand, $s_lang_url_part, $s_menu_url_part, $s_content_url_part, $seoUrlMenuSplitter));
		}
		
		$o_check = $o_main->db->query("select id from seodata where contentID = ? and contentModuleID = ?", array($basetable->ID, $moduleID));
		if($o_check && $o_check->num_rows()>0)
		{
			$row = $o_check->row_array();
			$insertID = $row['id'];
		} else {
			$o_main->db->query("INSERT INTO seodata (moduleID, contentID, contentModuleID, menuID, menulevelID) VALUES (?, ?, ?, 0, 0)", array($seo_moduleID, $basetable->ID, $moduleID));
			$insertID = $o_main->db->insert_id();
		}
		
		$o_check = $o_main->db->query("select id from seodatacontent where seodataID = ? and languageID = ?", array($insertID, $modRewriteRow['languageID']));
		if($o_check && $o_check->num_rows()>0)
		{
			$o_main->db->query("UPDATE seodatacontent SET seoTitle = ?, seoDescription = ? WHERE seodataID = ? AND languageID = ?", array($modRewriteRow['seotitle'], $modRewriteRow['seodescription'], $insertID, $modRewriteRow['languageID']));
		} else {
			$o_main->db->query("INSERT INTO seodatacontent (seodataID, languageID, seoTitle, seoDescription) VALUES (?, ?, ?, ?)", array($insertID, $modRewriteRow['languageID'], $modRewriteRow['seotitle'], $modRewriteRow['seodescription']));
		}
	}
	
	$o_main->db->query($s_seo_field_cleanup_sql);
	
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
}
?>