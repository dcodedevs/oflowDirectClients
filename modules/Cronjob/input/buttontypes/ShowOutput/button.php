<?php
include($extradir."/input/settings/relations/$buttonbase.php");
$relationField = "";
foreach($prerelations as $prep)
{
	$spPrep = explode("¤",$prep);
	if($spPrep[1] == $buttonSubmodule)
	{
		$relationField = "&relationfield=".$spPrep[3]."&relationID=".$ID;
		$relationModule =$spPrep[2];
	}
}
$edit_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."module=".$buttonSubmodule."&folder=output&folderfile=output&modulename=".$buttonSubmodule."".(($buttonlisttype == 'inputform' && $relationModule.$relationField!="")?"&submodule=".$relationModule.$relationField:'').($buttonContentStatus!=""?"&content_status=".$buttonContentStatus:"")."&updatepath=1";
?><a href="<?php echo $edit_link;?>" class="optimize" role="menuitem"><?php echo $buttonsArray[1];?></a>