<?php
$s_extra_text = '';
if(is_file(BASEPATH.'db_upgrade_dump.sql'))
{
	$s_extra_text = ' (<b style="color:yellow;">'.$formText_LastDump_Settings.': '.date("F d Y H:i:s", filemtime(BASEPATH.'db_upgrade_dump.sql')).'</b>)';
}
?>
<a href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&".(isset($_GET['moduleID'])?"moduleID=".$_GET['moduleID']."&":'')."module=".$buttonSubmodule."&includefile=dump_db";?>" class="optimize" role="menuitem"><?php echo $buttonsArray[1].$s_extra_text;?></a>