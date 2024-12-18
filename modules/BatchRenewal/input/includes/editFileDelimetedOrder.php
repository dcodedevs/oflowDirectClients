<?php
if(isset($_POST['editOrder']))
{
	define('BASEPATH', realpath(__DIR__.'/../../../../').DIRECTORY_SEPARATOR);
	require_once(BASEPATH.'elementsGlobal/cMain.php');
	require_once(__DIR__."/ftp_commands.php");
	if(!function_exists("log_action")) include(__DIR__."/fn_log_action.php");
	log_action("save_settings");
	$module_absolute_path = realpath(__DIR__.'/../../');
	$account_absolute_path = realpath(__DIR__.'/../../../../');
	
	$savefile = $_POST['file'];
	$file = $account_absolute_path.$_POST['file']; 
	
	ftp_file_put_content($savefile,str_replace("[%]".$_POST['arrayname'],"$".$_POST['arrayname'],str_replace("$","[%]",file_get_contents($file))));
	include($file);
	$filearray = explode("¤",$prebuttonconfig);
	array_pop($filearray);
	$newarray = array();
	foreach($_POST['contentID'] as $key => $value) 
	{
		$compare_length = strlen($value.':');
		for($x = 0; $x<count($filearray); $x++)
		{
			if(substr($filearray[$x],0,$compare_length) == $value.":")
			{
				$arraynumber = $x;
				break;
			}
		}
		$newarray[] = $filearray[$arraynumber];
	}
	
	$newFile = '$'.$_POST['arrayname'].' = "';
	while(list($x,$rest) = each($newarray))
	{
		$newFile .= str_replace("[%]","\$",$rest)."¤";
	}
	
	$newFile .="\";";
	ftp_file_put_content($savefile,"<?php\n{$newFile}\n?>");
	
	header("Location: ".substr($_SERVER['HTTP_REFERER'],0,strpos($_SERVER['HTTP_REFERER'],"?"))."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$_GET['module']."&submodule=".$_GET['submodule']."&includefile=".$_GET['includefile']);
	exit;
}
?>
<script type="text/javascript">
$(document).ready(function() {
	$("#sortable").sortable().disableSelection();
});
</script>
<form method="post" action="<?php echo $extradir."/input/includes/editFileDelimetedOrder.php?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$_GET['module']."&submodule=".$_GET['submodule']."&includefile=".$_GET['includefile']; ?>">
	<input type="hidden" name="submodule" value="<?php echo $submodule;?>" />
	<input type="hidden" name="module" value="<?php echo $module;?>" />
	<input type="hidden" name="moduleID" value="<?php echo $moduleID;?>" />
	<input type="hidden" name="pageID" value="<?php echo $_GET['pageID'];?>" />
	<input type="hidden" name="extradir" value="<?php echo $extradir;?>" />
	<input type="hidden" name="parentdir" value="<?php echo $parentdir;?>" />
	<input type="hidden" name="choosenListInputLang" value="<?php echo $choosenListInputLang;?>">
	<input type="hidden" name="choosenAdminLang" value="<?php echo $choosenAdminLang;?>">
	<input type="hidden" name="languageID" value="<?php echo $choosenInputLang;?>" />
	<input type="hidden" name="extraimagedir" value="<?php echo $extraimagedir;?>" />
	<input type="hidden" name="loginType" value="<?php echo ($access<211?1:2) ?>" />
	<input type="hidden" name="editOrder" value="1" />
	<input type="hidden" name="file" value="<?php echo $file;?>" />
	<input type="hidden" name="arrayname" value="<?php echo $arrayname;?>" />
	<ul id="sortable">
	<?php
	foreach($filearray as $key => $value)
	{
		?><li class="ui-state-default"><span class="ui-icon ui-icon-arrowthick-2-n-s"></span><input type="hidden" name="contentID[]" value="<?php echo $key;?>" /><?php echo $value->buttonnamelist;?></li><?php
	
	}
	?>
	</ul>
	<div class="inputfeltbuttonLagre"><input name="submbtn" value="<?php echo $formText_save_input;?>" type="submit"></div>
</form>