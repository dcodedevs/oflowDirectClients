<?php
define('BASEPATH', realpath(__DIR__.'/../../../../../').DIRECTORY_SEPARATOR);
require_once(BASEPATH.'elementsGlobal/cMain.php');
if(!function_exists("get_menulevel_parrents")) include(__DIR__."/fn_get_menulevel_parrents.php");

$return = array();
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

$_POST['seoUrlMenuSplitter'] = trim($_POST['seoUrlMenuSplitter']);
if($_POST['seoUrlEditType'] == 2)
{
	$content_name = $_POST['data'];
	$content_name = strtolower(str_replace(array_keys($char_map), $char_map, trim($content_name)));
	$return['parsed'] = preg_replace('#\-[\-]+#', '-', preg_replace('#[^A-za-z0-9_/-]+#', '',$content_name));
	$tmp = explode('/',$return['parsed']);
	$content = array_pop($tmp);
	$return['langmenupart'] = $s_lang_menu_part = implode("/",$tmp);
	$return['parsedList'] = rtrim($return['langmenupart'], '/');
} else {
	if(strlen($_POST['languageID'])>0)
	{
		$langID = $_POST['languageID'];
	} else {
		$row = array();
		$o_query = $o_main->db->query('SELECT languageID FROM language WHERE outputlanguage = 1 ORDER BY defaultOutputlanguage DESC, sortnr ASC');
		if($o_query && $o_query->num_rows()>0) $row = $o_query->row_array();
		$langID = $row['languageID'];
	}
	
	$s_list_prefix = "list".('' == $_POST['seoUrlMenuSplitter'] ? '-' : $_POST['seoUrlMenuSplitter']);
	if(strtolower($langID) == 'no') $s_list_prefix = "liste".('' == $_POST['seoUrlMenuSplitter'] ? '-' : $_POST['seoUrlMenuSplitter']);
	$o_find = $o_main->db->query("SELECT list_url_prefix FROM language WHERE languageID = ?", array($langID));
	if($o_find && $o_row = $o_find->row())
	{
		if($o_row->list_url_prefix != "")
		{
			$s_list_prefix = $o_row->list_url_prefix.(substr($o_row->list_url_prefix, -1) == "-" ? "" : ('' == $_POST['seoUrlMenuSplitter'] ? '-' : $_POST['seoUrlMenuSplitter']));
		}
	}
	
	$o_query = $o_main->db->query('SELECT id FROM language WHERE outputlanguage = 1');
	if($o_query && $o_query->num_rows() > 1)
		$languageID = ($_POST['languageID']!=""?$_POST['languageID']."/":"");
	else
		$languageID = "";
	
	$content_name = $_POST['data'];
	
	if(strpos($_POST['menulevelID'],'_')!==false) list($rest, $_POST['menulevelID']) = explode('_',$_POST['menulevelID']);
	
	$row = array();
	$o_query = $o_main->db->query('select levelname from menulevelcontent where menulevelID = ? AND languageID = ?', array($_POST['menulevelID'], $langID));
	if($o_query && $o_query->num_rows()>0) $row = $o_query->row_array();
	$levelName = strtolower(str_replace(array_keys($char_map), $char_map, trim(get_menulevel_parrents($_POST['menulevelID'], $langID, $_POST['seoUrlMenuSplitter']).$row['levelname'])));
	if('' == $_POST['seoUrlMenuSplitter']) $levelName = str_replace('/','-',$levelName);
	$content_name = strtolower(str_replace(array_keys($char_map), $char_map, trim($content_name)));
	$content_name = str_replace('/','-',$content_name);
	
	$return['langpart'] = preg_replace('#\-[\-]+#', '-', preg_replace('#[^A-za-z0-9_/-]+#', '',$languageID));
	$return['menupart'] = preg_replace('#\-[\-]+#', '-', preg_replace('#[^A-za-z0-9_/-]+#', '',($levelName!="" ? $levelName."/" : "")));
	$return['contentpart'] = preg_replace('#\-[\-]+#', '-', preg_replace('#[^A-za-z0-9_/-]+#', '',$content_name));
	$return['langmenupart'] = $s_lang_menu_part = $return['langpart'].$return['menupart'];
	$return['parsed'] = $return['langmenupart'].$return['contentpart'];
	if('' != $_POST['seoUrlMenuSplitter']) $return['parsed'] = preg_replace('~'.$_POST['seoUrlMenuSplitter'].'+~', $_POST['seoUrlMenuSplitter'], $return['parsed']);
	if($levelName!='')
	{
		$tmp = explode('/',$return['parsed']);
		$content = array_pop($tmp);
		$menu = array_pop($tmp);
		if(strpos($content,$menu)!==false)
		{
			$return['parsed'] = $return['langpart'].$return['contentpart'];
			$return['langmenupart'] = $return['langpart'];
			$return['menupart'] = "";
		}
	}
	$return['parsedList'] = rtrim($return['langpart'].$s_list_prefix.$return['menupart'], '/');
}

$row = array();
$o_query = $o_main->db->query('SELECT id FROM pageID WHERE contentID = ? AND contentTable = ?', array($_POST['id'], $_POST['table']));
if($o_query && $o_query->num_rows()>0) $row = $o_query->row_array();
$rand = "";
if($return['parsed']==$return['parsedList'] || ($_POST['allowEmpty'] != 1 && $return['parsed'] == ""))
{
	$i=1;
	$rand="-$i";
}
$s_sql = "select pc.id from pageIDcontent pc where urlrewrite = ? and pc.pageIDID not in (select p.id from pageID p where p.contentID = ? and p.contentTable = ?)";
$o_check = $o_main->db->query($s_sql, array($return['parsed'].$rand, $_POST['id'], $_POST['table']));
while($o_check && $o_check->num_rows() > 0)
{
	$i++;
	$rand="-$i";
	$o_check = $o_main->db->query($s_sql, array($return['parsed'].$rand, $_POST['id'], $_POST['table']));
}
$return['parsed'] .= $rand;
$return['contentpart'] .= $rand;

print json_encode($return);