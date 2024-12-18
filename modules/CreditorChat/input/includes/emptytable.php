<?php
define('BASEPATH', realpath(__DIR__.'/../../../../').DIRECTORY_SEPARATOR);
require_once(BASEPATH.'elementsGlobal/cMain.php');
$module_absolute_path = realpath(__DIR__.'/../../');
$account_absolute_path = realpath(__DIR__.'/../../../../');
require_once(__DIR__."/ftp_commands.php");

sanitize_escape($_GET['module'], 'string', $module);
sanitize_escape($_GET['submodule'], 'string', $submodule);

if(isset($_GET['emptyTable']))
{
	sanitize_escape($_GET['emptyTable'], 'string', $emptyTable);
	$s_sql = "DELETE FROM ".$emptyTable." WHERE account_id = '".$o_main->db->escape_str($o_main->account_id)."'";
	$o_query = $o_main->db->query($s_sql);
}

header("Location: ".substr($_SERVER['HTTP_REFERER'],0,strpos($_SERVER['HTTP_REFERER'],"?"))."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$_GET['module']."&submodule=".$_GET['submodule']."&includefile=".$_GET['includefile']);
exit;
