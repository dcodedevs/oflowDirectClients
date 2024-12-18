<?php
// get single-language table
$s_signle_table = ($basetable->multilanguage == 1 ? substr($basetable->name,0,-7) : $basetable->name);

if(!function_exists("get_menulevel_parrents")) include(__DIR__."/fn_get_menulevel_parrents.php");

foreach($databases as $fieldbasetable)
{
	foreach($fieldbasetable->fieldNums as $fieldnums)
	{
		if($fields[$fieldnums][4] == 'MenuconnectionAdv')
		{
			mysql_query("DELETE pageIDcontent FROM pageIDcontent INNER JOIN pageID ON pageID.id = pageIDcontent.pageIDID WHERE pageID.contentID = '".$fieldbasetable->ID."' AND pageID.contentTable = '".$fieldbasetable->name."' AND pageID.deleted = 1");
			mysql_query("UPDATE pageID SET deleted = 1 WHERE contentID = '".$fieldbasetable->ID."' AND contentTable = '".$fieldbasetable->name."'");
			foreach($fields[$fieldnums][6]['all'] as $levelID)
			{
				$oldIDfind = mysql_query("SELECT id FROM pageID WHERE contentID = '".$fieldbasetable->ID."' AND contentTable = '".$fieldbasetable->name."' and deleted = 1;");
				if(mysql_num_rows($oldIDfind) == 0)
				{
					mysql_query("INSERT INTO pageID(contentID, contentTable, menulevelID) VALUES('".$fieldbasetable->ID."','".$fieldbasetable->name."','".$levelID."');");
				} else {
					$oldIDwrite = mysql_fetch_array($oldIDfind);
					mysql_query("UPDATE pageID SET menulevelID = '".$levelID."' , deleted = 0 WHERE id = '".$oldIDwrite[0]."';");
				}
			}
		}
	}
}

$row = mysql_fetch_assoc(mysql_query("SELECT languageID FROM language WHERE outputlanguage = 1 ORDER BY defaultOutputlanguage DESC, sortnr ASC"));
$defaultLangID = $row['languageID'];

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

if(mysql_num_rows(mysql_query("SELECT id FROM language WHERE outputlanguage = 1"))>1)
{
	$doMultiLanguage = true;
} else $doMultiLanguage = false;

if(isset($curField))
{
	$b_single_lang_url = (array_key_exists("all",$curField[6]) ? true : false);
	//check if pageIDcontent is set
	if(mysql_num_rows(mysql_query("show tables like 'pageIDcontent'")) == 0)
	{
		$sql = "CREATE TABLE `pageIDcontent` (
					`id` INT(11) NOT NULL AUTO_INCREMENT,
					`pageIDID` INT(11) NOT NULL,
					`languageID` CHAR(50) NOT NULL,
					`urlrewrite` VARCHAR(255) NOT NULL,
					PRIMARY KEY (`id`),
					INDEX `Relation` (`pageIDID`, `languageID`),
					INDEX `Search2` (`urlrewrite`)
				)";
		mysql_query($sql);
	}
	mysql_query("ALTER TABLE `pageIDcontent` DROP INDEX `Search`, ADD INDEX `Search2` (`urlrewrite`);");
	mysql_query("ALTER TABLE `pageIDcontent`
					ADD COLUMN `lang_url_part` CHAR(10) NOT NULL AFTER `urlrewrite`,
					ADD COLUMN `menu_url_part` CHAR(100) NOT NULL AFTER `lang_url_part`,
					ADD COLUMN `content_url_part` CHAR(100) NOT NULL AFTER `menu_url_part`;");
	if(mysql_num_rows(mysql_query("show tables like 'sys_htaccess'")) == 0)
	{
		$sql = "CREATE TABLE sys_htaccess (
				pageID INT(11) NOT NULL,
				languageID CHAR(50) NOT NULL,
				urlfrom VARCHAR(1000) NOT NULL,
				urlto VARCHAR(1000) NOT NULL,
				redirect TINYINT NOT NULL DEFAULT 0,
				INDEX Relation (pageID, languageID),
				INDEX RelationUrl (urlfrom(255))
			)";
		mysql_query($sql);
	}
	if(mysql_num_rows(mysql_query("show tables like 'pageIDlist'")) == 0)
	{
		$sql = "CREATE TABLE pageIDlist (
				id INT(11) NOT NULL AUTO_INCREMENT,
				menulevelID INT(11) NOT NULL,
				languageID CHAR(10) NOT NULL,
				listurl CHAR(255) NOT NULL,
				PRIMARY KEY (id),
				INDEX Relation (menulevelID, languageID),
				INDEX Search2 (listurl)
			);";
		mysql_query($sql);
	}
	
	$moduleID = mysql_real_escape_string($_POST['moduleID']);
	$row = mysql_fetch_assoc(mysql_query("select id from moduledata where name = 'SEO'"));
	$seo_moduleID = $row['id'];
	
	//check if single/multi language field
	if($curField[3]==$s_signle_table) {
		$modRewriteSql = "select {$s_signle_table}.{$curField[0]} url_rewrite_name, {$s_signle_table}.seotitle, {$s_signle_table}.seodescription, pageID.menulevelID, menulevelcontent.levelname, '{$defaultLangID}' languageID, pageID.id pageID from pageID join {$s_signle_table} on {$s_signle_table}.id = pageID.contentID AND pageID.contentTable = '{$s_signle_table}' left outer join menulevelcontent on pageID.menulevelID = menulevelcontent.menulevelID AND menulevelcontent.languageID = '{$defaultLangID}' WHERE {$s_signle_table}.id = '{$basetable->ID}' AND pageID.deleted != 1";
		$s_seo_field_cleanup_sql = "update {$s_signle_table} set seourl='', seotitle='', seodescription = '' where id = '{$basetable->ID}'";
	} else {
		$modRewriteSql = "select {$s_signle_table}content.{$curField[0]} url_rewrite_name, {$s_signle_table}content.seotitle, {$s_signle_table}content.seodescription, pageID.menulevelID, menulevelcontent.levelname, {$s_signle_table}content.languageID, pageID.id pageID from pageID join {$s_signle_table}content on {$s_signle_table}content.{$s_signle_table}ID = pageID.contentID AND pageID.contentTable = '{$s_signle_table}' left outer join menulevelcontent on pageID.menulevelID = menulevelcontent.menulevelID AND {$s_signle_table}content.languageID = menulevelcontent.languageID WHERE {$s_signle_table}content.{$s_signle_table}ID = '{$basetable->ID}' AND pageID.deleted != 1";
		$s_seo_field_cleanup_sql = "update {$s_signle_table}content set seourl='', seotitle='', seodescription = '' where {$s_signle_table}ID = '{$basetable->ID}'";
	}
	//print $modRewriteSql;
	
	$modRewriteResult = mysql_query($modRewriteSql);
	while($modRewriteRow = mysql_fetch_array($modRewriteResult))
	{
		$s_list_prefix = "list-";
		if(strtolower($modRewriteRow['languageID']) == 'no') $s_list_prefix = "liste-";
		$o_find = mysql_query("SELECT list_url_prefix FROM language WHERE languageID = '".$modRewriteRow['languageID']."'");
		if($o_find && mysql_num_rows($o_find)>0)
		{
			$v_row = mysql_fetch_assoc($o_find);
			if($v_row['list_url_prefix'] != "")
			{
				$s_list_prefix = $v_row['list_url_prefix'].(substr($v_row['list_url_prefix'], -1) == "-" ? "" : "-");
			}
		}
		
		if($doMultiLanguage) $langID = $modRewriteRow['languageID']."/"; else $langID = "";
		$modRewriteRow['levelname'] = get_menulevel_parrents($modRewriteRow['menulevelID'], $modRewriteRow['languageID']).$modRewriteRow['levelname'];
		$levelName = strtolower(str_replace(array_keys($char_map), $char_map, trim($modRewriteRow['levelname'])));
		$levelName = str_replace('/','-',$levelName);
		$modRewriteList = preg_replace('/\-[\-]+/','-',preg_replace('/[^A-za-z0-9_\/-]+/','',$langID.$s_list_prefix.$levelName));
		$s_content_url = strtolower(str_replace(array_keys($char_map), $char_map, trim($modRewriteRow['url_rewrite_name'])));
		$modRewriteName = preg_replace('/\-[\-]+/','-',preg_replace('/[^A-za-z0-9_\/-]+/','',$s_content_url));
		
		if($modRewriteRow['menulevelID'] > 0)
		{
			$o_result = mysql_query("select id from pageIDlist where menulevelID = '".$modRewriteRow['menulevelID']."' and languageID = '".$modRewriteRow['languageID']."'");
			if(mysql_num_rows($o_result)==0)
			{
				$s_sql = "insert into pageIDlist (menulevelID, languageID, listurl) values('".$modRewriteRow['menulevelID']."', '".$modRewriteRow['languageID']."', '".addslashes($modRewriteList)."');";
				mysql_query($s_sql);
			} else {
				$s_sql = "update pageIDlist set listurl = '".addslashes($modRewriteList)."' where menulevelID = '".$modRewriteRow['menulevelID']."' and languageID = '".$modRewriteRow['languageID']."';";
				mysql_query($s_sql);
			}
		}
		
		$i=0;
		$rand = "";
		if($modRewriteList == $modRewriteName || ($enableEmptySeoUrl != 1 && $modRewriteName == ""))
		{
			$i=1;
			$rand="-$i";
		}
		while(mysql_num_rows(mysql_query("select pc.id from pageIDcontent pc where urlrewrite = '{$modRewriteName}{$rand}' and pc.pageIDID not in (select p.id from pageID p where p.contentID = '{$basetable->ID}' and p.contentTable = '{$s_signle_table}')"))>0)
		{
			$i++;
			$rand="-$i";
		}
		
		$s_key = $modRewriteRow['languageID'];
		if($b_single_lang_url) $s_key = "";
		
		$s_lang_url_part = $_POST[$submodule."seourl".$s_key."lang"];
		$s_menu_url_part = $_POST[$submodule."seourl".$s_key."menu"];
		$s_content_url_part = $_POST[$submodule."seourl".$s_key."content"];
		
		if(mysql_num_rows(mysql_query("select id from pageIDcontent where pageIDID = '{$modRewriteRow['pageID']}' and languageID = '{$modRewriteRow['languageID']}'"))==0)
		{
			$modRewriteSql = "insert into pageIDcontent (pageIDID, languageID, urlrewrite, lang_url_part, menu_url_part, content_url_part) values({$modRewriteRow['pageID']}, '{$modRewriteRow['languageID']}', '{$modRewriteName}{$rand}', '$s_lang_url_part', '$s_menu_url_part', '$s_content_url_part');";
		} else {
			$modRewriteSql = "update pageIDcontent set urlrewrite = '{$modRewriteName}{$rand}', lang_url_part = '$s_lang_url_part', menu_url_part = '$s_menu_url_part', content_url_part = '$s_content_url_part' where pageIDID = '{$modRewriteRow['pageID']}' and languageID = '{$modRewriteRow['languageID']}';";
		}
		//print $modRewriteSql;
		mysql_query($modRewriteSql);
		
		$rs = mysql_query("select id from seodata where contentID = '{$basetable->ID}' and contentModuleID = '{$moduleID}'");
		if(mysql_num_rows($rs)==0)
		{
			mysql_query("INSERT INTO seodata (moduleID, contentID, contentModuleID, menuID, menulevelID) VALUES ('{$seo_moduleID}', '{$basetable->ID}', '{$moduleID}', 0, 0)");
			$insertID = mysql_insert_id();
		} else {
			$row = mysql_fetch_array($rs);
			$insertID = $row['id'];
		}
		$rs = mysql_query("select id from seodatacontent where seodataID = '".$insertID."' and languageID = '".$modRewriteRow['languageID']."'");
		if(mysql_num_rows($rs)==0)
		{
			mysql_query("INSERT INTO seodatacontent (seodataID, languageID, seoTitle, seoDescription) VALUES ('".$insertID."', '".$modRewriteRow['languageID']."', '".$modRewriteRow['seotitle']."', '".$modRewriteRow['seodescription']."')");
		} else {
			mysql_query("UPDATE seodatacontent SET seoTitle = '".$modRewriteRow['seotitle']."', seoDescription = '".$modRewriteRow['seodescription']."' WHERE seodataID = '".$insertID."' AND languageID = '".$modRewriteRow['languageID']."'");
		}
	}
	
	mysql_query($s_seo_field_cleanup_sql);
	
	mysql_query("TRUNCATE TABLE sys_htaccess");
	
	$modRewriteSql = "select p.id, p.menulevelID, p.contentID, pc.languageID, pc.urlrewrite, mc.levelname, pl.listurl from pageID p join pageIDcontent pc on pc.pageIDID = p.id LEFT OUTER JOIN menulevelcontent mc on mc.menulevelID = p.menulevelID and mc.languageID = pc.languageID LEFT OUTER JOIN pageIDlist pl ON pl.menulevelID = p.menulevelID AND pl.languageID = pc.languageID WHERE p.deleted != 1 AND p.contentID > 0 order by p.id";
	//print $modRewriteSql;exit;
	$modRewriteResult = mysql_query($modRewriteSql);
	while($modRewriteRow = mysql_fetch_array($modRewriteResult))
	{
		mysql_query("INSERT INTO sys_htaccess (pageID, languageID, urlfrom, urlto)
		VALUES ('".$modRewriteRow['id']."', '".$modRewriteRow['languageID']."', '".trim($modRewriteRow['urlrewrite'],"/")."/', 'index.php?pageID=".$modRewriteRow['id']."&openLevel=".$modRewriteRow['menulevelID']."&langID=".$modRewriteRow['languageID']."')");
		if($modRewriteRow['listurl'] != "")
		{
			mysql_query("INSERT INTO sys_htaccess (pageID, languageID, urlfrom, urlto)
		VALUES ('".$modRewriteRow['id']."', '".$modRewriteRow['languageID']."', '".$modRewriteRow['listurl']."', 'index.php?pageID=".$modRewriteRow['id']."&openLevel=".$modRewriteRow['menulevelID']."&langID=".$modRewriteRow['languageID']."&showList=1')");
		}
	}
}
?>