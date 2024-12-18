<?php
/* ALLOWED INCLUDES */
$v_include = array(
	"ajax",
	"list",
    "details"
);

$v_include_default = 'list';

$baselink = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output";
$folderName = 'SubscriptionReport';

if(!function_exists("include_local")) include(__DIR__."/../input/includes/fn_include_local.php");
include(__DIR__."/../input/includes/readInputLanguage.php");

$s_default_output_language = "";
$o_query = $o_main->db->query("SELECT languageID FROM language ORDER BY defaultOutputlanguage DESC, outputlanguage DESC, sortnr ASC");
if($o_query && $o_row = $o_query->row()) $s_default_output_language = $o_row->languageID;

$headmodule = "";
$submods = $v_module_main_tables = array();
if($findBase = opendir(__DIR__."/../input/settings/tables"))
{
	while($writeBase = readdir($findBase))
	{
		$fieldParts = explode(".",$writeBase);
		if($fieldParts[1] != "LCK" && $fieldParts[1] == "php" && $fieldParts[0] != "")
		{
			$submods[] = $fieldParts[0];
			$vars = include_local(__DIR__."/../input/settings/tables/".$fieldParts[0].".php", $v_language_variables);

			if($vars['tableordernr'] == "1")
			{
				$headmodule = $fieldParts[0];
				$v_module_main_tables[1] = array($fieldParts[0], $vars['preinputformName'], $vars['moduletype']);
			}
			else if($vars['moduleMainTable'] == "1" && intval($vars['moduleTableAccesslevel'])<=$fw_session['developeraccess'])
			{
				$l_id = intval($vars['tableordernr']);
				if(array_key_exists($vars['tableordernr'], $v_module_main_tables)) $l_id += 20;
				$v_module_main_tables[$l_id] = array($fieldParts[0], $vars['preinputformName'], $vars['moduletype']);
			}
		}
	}
	if($headmodule == "")
	{
		$headmodule = $submods[0];
	}
	if(count($v_module_main_tables)==0)
	{
		$vars = include_local(__DIR__."/../input/settings/tables/".$submods[0].".php", $v_language_variables);
		$v_module_main_tables[1] = array($submods[0], $vars['preinputformName'], $vars['moduletype']);
	}
	if(is_file(__DIR__."/../input/settings/tables/".$headmodule.".php")) include(__DIR__."/../input/settings/tables/".$headmodule.".php");
	closedir($findBase);
}
$submodule = $headmodule;
$fields = array();
include(__DIR__."/../input/settings/fields/".$submodule."fields.php");
foreach($prefields as $s_field)
{
	$v_field = explode("¤",$s_field);
	$fields[$v_field[0]] = $v_field;
}

ob_start();
if(count($v_module_main_tables)>0)
{
	?><ul class="list-inline"><?php
	$v_keys = array_keys($v_module_main_tables);
	sort($v_keys);
	foreach($v_keys as $l_key)
	{
		?><li<?=($v_module_main_tables[$l_key][0]==$submodule?' class="active"':'');?>><a href="<?=$_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&submodule=".$v_module_main_tables[$l_key][0].(isset($_GET['relationID'])?"&relationID=".$_GET['relationID']:"").(isset($_GET['relationfield'])?"&relationfield=".$_GET['relationfield']:"").(!is_numeric($v_module_main_tables[$l_key][2])?"&folderfile=output&folder=".$v_module_main_tables[$l_key][2]:"");?>" class="optimize"><?=($v_module_main_tables[$l_key][1]!=""?$v_module_main_tables[$l_key][1]:$v_module_main_tables[$l_key][0]);?></a></li><?php
	}
	?></ul><?php
}
$fw_module_head = ob_get_clean();

$s_page_reload_url = "";
$error_msg = json_decode($fw_session['error_msg'],true);
if(!isset($_POST['output_form_submit']) && sizeof($error_msg)>0)
{
	$print = "";
	foreach($error_msg as $key => $message)
	{
		list($class,$rest) = explode("_",$key);
		$print .= ' fw_info_message_add("'.$class.'", "'.$message.'"); ';
	}
	$print .= ' fw_info_message_show(); ';
	if(isset($ob_javascript))
	{
		$ob_javascript .= ' $(function(){'.$print.'});';
	} else {
		?><script type="text/javascript" language="javascript"><?='$(function(){'.$print.'});';?></script><?php
	}
	$fw_session['error_msg'] = array();
	$o_main->db->query("update session_framework set error_msg = '' where companyaccessID = ? and session = ? and username = ?", array($_GET['caID'], $variables->sessionID, $variables->loggID));
}

ob_start();
include_once(__DIR__."/includes/readOutputLanguage.php");

if(isset($_GET['inc_obj']) && in_array($_GET['inc_obj'], $v_include)) $s_inc_obj = $_GET['inc_obj']; else $s_inc_obj = $v_include_default;
if($s_inc_obj != "ajax")
{
	?>
	<div id="output-content-container">
		<?php
		if(is_file(__DIR__."/includes/".$s_inc_obj.".php")) include(__DIR__."/includes/".$s_inc_obj.".php");
		?>
	</div>
	<div id="popupeditbox" class="popupeditbox">
		<span class="button b-close"><span>X</span></span>
		<div id="popupeditboxcontent"></div>
	</div>
	<script type="text/javascript">
	function output_reload_page()
	{
		fw_load_ajax('<?php echo $s_page_reload_url;?>', '', false);
	}
	</script>
	<?php require_once __DIR__ . '/output_javascript.php'; ?>
	<?php
} else {
	$s_inc_act = "";
	if(is_string($_GET['inc_act'])) $s_inc_act = $_GET['inc_act'];
	if(is_file(__DIR__."/includes/".$s_inc_obj.".".$s_inc_act.".php")) include(__DIR__."/includes/".$s_inc_obj.".".$s_inc_act.".php");
}

if(!isset($_POST['output_form_submit'])) print ob_get_clean();
?>
