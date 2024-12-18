<?php
/*
 * -----------------------------------
 *
 * Version: 8.202
 *
 * ----------------------------------
 */
session_start();

//SET ACCOUNT BASE
$s_base_url = "";
$v_parse_url = parse_url(social_selfURL());
$v_path = explode("/",$v_parse_url['path']);
$v_difference = array_diff($v_path, explode("/",__DIR__));
foreach($v_path as $s_item)
{
	if($s_item != "" and !in_array($s_item, $v_difference))
	{
		$s_base_url .= $s_item.DIRECTORY_SEPARATOR;
	}
}
define('ACCOUNTBASE', $s_base_url);
define('BASEPATH', __DIR__.DIRECTORY_SEPARATOR);
require_once("class.phpmailer.php");
require_once(BASEPATH.'elementsGlobal/cMain.php');
require_once(BASEPATH.'fw/account_fw/includes/fn_log_action.php');
if(!isset($_GET['pageID']))
{
	//URL PARSE
	$s_check_url = trim(urldecode(str_replace($s_base_url,'',$v_parse_url['path'])),'/');
	if($s_check_url!='' && $s_check_url != 'index.php' && $s_check_url != 'index.php?')
	{
		//First get single content from one module (newspage) and then multi-content from other module (news)
		$v_param = array($s_check_url, $s_check_url."/");
		$s_sql = 'SELECT h.*, count(p.id) priority FROM sys_htaccess h JOIN pageID p ON p.id = h.pageID WHERE h.urlfrom = ? OR h.urlfrom = ? GROUP BY p.contentTable ORDER BY priority ASC';
		$o_query = $o_main->db->query($s_sql, $v_param);
		if($o_query && $o_query->num_rows()>0)
		{
			$o_row = $o_query->row();
			$v_parse_url = parse_url($o_row->urlto);
			$v_get = explode('&',urldecode($v_parse_url['query']));
			foreach($v_get as $s_item)
			{
				list($s_key,$s_value) = explode("=",$s_item);
				$_GET[$s_key] = $s_value;
			}
		} else {
			include('404.php');
			exit;
		}
	}
}
log_action('page_view');
if(isset($_POST['callback']) )
{
	include(BASEPATH.'modules/Shop/output/includes/callback.php');
	exit;
}

$layoutID = 0;
if(isset($_GET['langID']) and strlen($_GET['langID'])>=2)
{
	$languageID = $_GET['langID'];
}
if(!isset($languageID) || $languageID == '')
{
	$languageDir = "";
	$o_query = $o_main->db->query('SELECT languageID FROM language WHERE outputlanguage = 1 AND defaultOutputlanguage = 1');
	if($o_query && $o_query->num_rows()>0)
	{
		$o_row = $o_query->row();
		$languageID = $o_row->languageID;
	}
}

$pageID = 0;
$choosenLevel = 0;
if(isset($_GET['pageID']) && is_numeric($_GET['pageID']))
{
	$pageID = $_GET['pageID'];
}
if($pageID == 0)
{
	$o_query = $o_main->db->query('SELECT id FROM pageID ORDER BY startpage DESC, id ASC LIMIT 1');
	if($o_query && $o_query->num_rows()>0)
	{
		$o_row = $o_query->row();
		$pageID = $o_row->id;
	}
}

$levels = array();
$contentTable = $contentID = $choosenLevel = '';
$o_query = $o_main->db->query('SELECT contentID, contentTable, menulevelID FROM pageID WHERE id = ?', array($pageID));
if($o_query && $o_query->num_rows()>0)
{
	$o_row = $o_query->row();
	$contentID = $o_row->contentID;
	$contentTable = $o_row->contentTable;
	$choosenLevel = $o_row->menulevelID;
}
if(isset($_GET['openLevel']) && is_numeric($_GET['openLevel']))
{
	$levels[] = $_GET['openLevel'];
} else {
	$levels[] = $choosenLevel;
}

$l_menulevel_id = $levels[0];
while($l_menulevel_id != 0)
{
	$o_query = $o_main->db->query('SELECT parentlevelID FROM menulevel WHERE id = ?', array($l_menulevel_id));
	if($o_query)
	{
		$o_row = $o_query->row();
		if($o_row->parentlevelID != 0)
		{
			array_unshift($levels, $o_row->parentlevelID);
		}
		$l_menulevel_id = $o_row->parentlevelID;
	}
}

$moduleID = $layoutID = $outputFolder = '';
if($contentTable != '')
{
	$s_sql = 'SELECT md.name, md.id, ct.layoutid FROM moduledata md JOIN '.$contentTable.' ct ON ct.moduleID = md.id JOIN pageID p ON p.contentID = ct.id WHERE p.id = ?';
	$o_query = $o_main->db->query($s_sql, array($pageID));
	if($o_query)
	{
		$o_row = $o_query->row();
		$moduleID = $o_row->id;
		$layoutID = $o_row->layoutid;
		$outputFolder = $o_row->name;
	}
}

$variables = new Variables();
$variables->start($languageDir, $languageID, $levels, $contentTable, $pageID, $outputFolder, $contentID, $choosenLevel);
$o_query = $o_main->db->query('SELECT id FROM language WHERE outputlanguage = 1');
if($o_query && $o_query->num_rows()>0) $variables->multilanguage = true;

if(is_file(BASEPATH.'modules/GetynetIDLogin/output/getynetIDcheck.php'))
	include(BASEPATH.'modules/GetynetIDLogin/output/getynetIDcheck.php');
if(is_file(BASEPATH.'modules/'.$variables->outputFolder.'/'.$variables->outputTheme.'/output_head.php'))
	include(BASEPATH.'modules/'.$variables->outputFolder.'/'.$variables->outputTheme.'/output_head.php');

// seo code starts here
$o_seo_data = (object) array('seoTitle'=>'', 'seoDescription'=>'', 'seoKeywords'=>'');
if($moduleID != '')
{
	$s_sql = 'SELECT sc.* FROM seodata s JOIN seodatacontent sc ON s.id = sc.seodataID WHERE s.contentID = ? AND s.contentModuleID = ? AND sc.languageID = ?';
	$o_query = $o_main->db->query($s_sql, array($variables->contentID, $moduleID, $variables->languageID));
	if($o_query && $o_query->num_rows()==0)
	{
		$s_sql = 'SELECT sc.* FROM seodata s JOIN seodatacontent sc ON s.id = sc.seodataID JOIN menulevel m ON s.menulevelID = m.id AND s.menuID = m.moduleID WHERE s.menulevelID = ? and sc.languageID = ?';
		$o_query = $o_main->db->query($s_sql, array($variables->choosenLevel, $variables->languageID));
		if($o_query && $o_query->num_rows()==0)
		{
			$s_sql = 'SELECT sc.* FROM seodata s JOIN seodatacontent sc ON s.id = sc.seodataID WHERE s.menuID = 0 AND s.menulevelID = 0 AND s.contentID = 0 AND sc.languageID = ?';
			$o_query = $o_main->db->query($s_sql, array($variables->languageID));
		}
	}
	if($o_query) $o_seo_data = $o_query->row();
}
$s_seo_icon = '';
if(isset($_GET['showList']))
{
	$o_query = $o_main->db->query("SELECT levelname FROM menulevelcontent WHERE menulevelID = ? AND languageID = ?", array($variables->levels[0], $languageID));
	if($o_query && $o_row = $o_query->row()) $o_seo_data->seoTitle = $o_row->levelname;
}
//seo code ends here
?>
<!DOCTYPE html>
<html>
<head>
<title><?php print $o_seo_data->seoTitle;?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="format-detection" content="telephone=no">
<meta name="description" content="<?php echo $o_seo_data->seoDescription;?>">
<meta name="keywords" content="<?php echo $o_seo_data->seoKeywords;?>">
<?php if($s_seo_icon != '') { ?><link rel="image_src" href="<?php echo $s_seo_icon;?>" /><?php } ?>
<?php
if(is_file(BASEPATH.'modules/'.$variables->outputFolder.'/'.$variables->outputTheme.'/output_meta.php'))
	include(BASEPATH.'modules/'.$variables->outputFolder.'/'.$variables->outputTheme.'/output_meta.php');
if(is_file(BASEPATH.'lib/loadOutput.php')) include(BASEPATH.'lib/loadOutput.php');
if(is_file(BASEPATH.'modules/GoogleAnalytics/output/output.php')) include(BASEPATH.'modules/GoogleAnalytics/output/output.php');
if(is_file(BASEPATH.'modules/MetaAddon/output/output.php')) include(BASEPATH.'modules/MetaAddon/output/output.php');
?>
</head>
<body>
<?php if(is_file(BASEPATH.'modules/MetaAddon/output/outputBody.php')) include(BASEPATH.'modules/MetaAddon/output/outputBody.php'); ?>
<?php
if(is_file(BASEPATH.'modules/'.$variables->outputFolder.'/'.$variables->outputTheme.'/output_floatlayer.php'))
	include(BASEPATH.'modules/'.$variables->outputFolder.'/'.$variables->outputTheme.'/output_floatlayer.php');

/*
--------------------------------------------------------------------

Layout manager class

--------------------------------------------------------------------
*/

class Layoutmanager {
	/*
	--------------------------------------------------------------------

	Get blocks for layout id

	--------------------------------------------------------------------
	*/

	function getBlocks($o_main, $l_layout_id, $l_parent_id = 0)
	{
		$v_return = array();
		
		$o_query = $o_main->db->query('SELECT * FROM sys_layoutblock WHERE layout_id = ? AND parent = ? ORDER BY sortnr ASC', array($l_layout_id, $l_parent_id));
		if($o_query && $o_query->num_rows() > 0)
		{
			foreach($o_query->result() as $o_row)
			{
				$v_return[] = array(
					'id' => $o_row->id,
					'name' => $o_row->name,
					'type' => $o_row->block_type,
					'parent' => $o_row->parent,
					'class' => $o_row->css_class,
					'assigned_module' => $o_row->assigned_module_id,
					'assigned_output' => $o_row->assigned_module_output
				);
				
				$s_sql = 'SELECT * FROM sys_layoutblock WHERE layout_id = ? AND parent = ? ORDER BY sortnr ASC';
				$o_query_child = $o_main->db->query($s_sql, array($l_layout_id, $o_row->id));
				if($o_query_child->num_rows() > 0)
				{
					$v_return[sizeof($v_return)-1]['child'] = $this->getBlocks($o_main, $l_layout_id, $o_row->id);
				}
			}
		}
		
		return $v_return;
	}

	/*
	--------------------------------------------------------------------

	Show blocks received by get blocks function

	--------------------------------------------------------------------
	*/

	function showBlocks($o_main, $blocks, $variables)
	{
		foreach($blocks as $block)
		{
			$class = $block['class'];

			echo '<div id="block-'.$block['id'].'" class="'.$class.'">';

				if($block['assigned_module'] > 0)
				{
					$this->loadModuleContent($o_main, $block['assigned_module'], $block['assigned_output'], $variables);
				}

				if($block['child']) $this->showBlocks($o_main, $block['child'], $variables);

			echo '</div>';
		}
	}

	/*
	--------------------------------------------------------------------

	Load module content

	--------------------------------------------------------------------
	*/

	function loadModuleContent($o_main, $moduleID, $s_output = 'output', $variables)
	{
		if(trim($s_output) == '') $s_output = 'output';
		$o_query = $o_main->db->query('SELECT * FROM moduledata WHERE id = ?', array($moduleID));
		if($o_query && $o_query->num_rows() > 0)
		{
			$content = $o_query->row();
			if($content->name)
			{
				// Load language file
				$s_file = __DIR__.'/modules/'.$content->name.'/'.$s_output.'/languagesOutput/'.$variables->languageID.'.php';
				if(is_file($s_file)) require($s_file);
				
				// Load module content
				$s_file = __DIR__ . '/modules/'.$content->name.'/'.$s_output.'/output.php';
				if(is_file($s_file)) require($s_file);
			}
		}
	}

	/*
	--------------------------------------------------------------------

	Show layout

	--------------------------------------------------------------------
	*/

	function showLayout($o_main, $l_layout_id, $variables)
	{
		$blocks = $this->getBlocks($o_main, $l_layout_id);
		$this->showBlocks($o_main, $blocks, $variables);
	}
}
/*
--------------------------------------------------------------------

Init & Show

--------------------------------------------------------------------
*/

$layoutmanager = new Layoutmanager();
$layoutmanager->showLayout($o_main, $layoutID, $variables);

?>
</body>
</html>
<?php
$o_main->db->close();

/*
--------------------------------------------------------------------

Variables - used in modules

--------------------------------------------------------------------
*/

class Variables
{  
	var $languageDir;
	var $languageID;
	var $multilanguage = false;
	var $levels;
	var $contentTable;
	var $pageID;
	var $outputFolder;
	var $outputTheme = "output";
	var $contentID;
	var $choosenLevel;
	var $logget;
	var $loggID;
	var $sessionID;
	
	function start($lang,$langID, $lev,$cont, $pag, $opfolder, $contID,$choosenLev)
	{
		$this->languageDir = $lang;
		$this->languageID = $langID;
		$this->levels = $lev;
		$this->contentTable = $cont;
		$this->pageID = $pag;
		$this->outputFolder = $opfolder;
		$this->contentID = $contID;
		$this->choosenLevel = $choosenLev;
		$this->logget = 0;
	}
}

/*
--------------------------------------------------------------------

parseLink

--------------------------------------------------------------------
*/

function parseLink($link)
{
	return preg_replace('/^(?!http:\/\/)(?!https:\/\/)(?!mailto:)(?!www.)(?!\/)|^\/(?!http:\/\/)(?!https:\/\/)(?!mailto:)(?!www.)(?!\/)/', '/'.ACCOUNTBASE, $link);
}

/*
--------------------------------------------------------------------

parseContentLinks

--------------------------------------------------------------------
*/

function parseContentLinks($content)
{
	$content = preg_replace('/href=\"(?!http:\/\/)(?!https:\/\/)(?!mailto:)(?!www.)(?!\/)|href=\"\/(?!http:\/\/)(?!https:\/\/)(?!mailto:)(?!www.)/', 'href="/'.ACCOUNTBASE, $content);
	$content = preg_replace('/href=\"\/[\/]+/', 'href="/', $content);
	$content = preg_replace('/src=\"(?!http:\/\/)(?!https:\/\/)(?!mailto:)(?!www.)(?!\/)|src=\"\/(?!http:\/\/)(?!https:\/\/)(?!mailto:)(?!www.)/', 'src="/'.ACCOUNTBASE, $content);
	$content = preg_replace('/src=\"\/[\/]+/', 'src="/', $content);
	return preg_replace('/href=\"www./', 'href="http://www.', $content);
}

/*
--------------------------------------------------------------------

social_selfURL

--------------------------------------------------------------------
*/

function social_selfURL()
{ 
	$s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : ""; 
	$protocol = social_strleft(strtolower($_SERVER["SERVER_PROTOCOL"]), "/").$s; 
	$port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]); 
	return $protocol."://".$_SERVER['SERVER_NAME'].$port.$_SERVER['REQUEST_URI']; 
} 

/*
--------------------------------------------------------------------

social_strleft

--------------------------------------------------------------------
*/


function social_strleft($s1, $s2) 
{ 
	return substr($s1, 0, strpos($s1, $s2)); 
}

/*
--------------------------------------------------------------------

formatDisplayDate

--------------------------------------------------------------------
*/

function formatDisplayDate($dates)
{
	$return = '';
	$from = strtotime($dates[0]);
	$months = array('','januar','februar','mars','april','mai','juni','juli','august','september','oktober','november','desember');
	foreach($dates as $key => $date)
	{
		$to = strtotime($date);
		if($from == $to) {
			if($return!='') $return.=', ';
			$return .= date('j',$from).'.';
		} else if(date('n',$from) == date('n',$to)) { //matching months
			if($return!='') $return.=', ';
			$return .= date('j',$to).'.';
		} else { // month doesn't match
			if($return!='') $return.= ' '.$months[date('n',$from)].', ';
			$return .= date('j',$to).'.';
		}
		if(sizeof($dates)== ($key+1)) $return.= ' '.$months[date('n',$from)];
	}
	return $return;
}

/*
--------------------------------------------------------------------

object2array

--------------------------------------------------------------------
*/

function object2array($object)
{
   $return = NULL;
   if(is_array($object))
   {
       foreach($object as $key=>$value)
           $return[$key] = object2array($value);
   } else {
       $var = get_object_vars($object);
       if($var)
       {
           foreach($var as $key => $value)
               $return[$key] = object2array($value);
       } else {
           return strval($object); // strval and everything is fine
	   }
   }

   return $return;
}

/*
--------------------------------------------------------------------

productPrice

--------------------------------------------------------------------
*/

function productPrice($price)
{
	return preg_replace("/[^0-9]/","",$price);
}
?>