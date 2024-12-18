<?php
$relationField = $relationModule = "";
$s_relation_file = $extradir."/input/settings/relations/$buttonbase.php";
if(is_file($s_relation_file))
{
	include($s_relation_file);
	foreach($prerelations as $prep)
	{
		$spPrep = explode("¤",$prep);
		if($spPrep[1] == $buttonSubmodule)
		{
			$relationField = "&relationfield=".$spPrep[3]."&relationID=".$ID;
			$relationModule =$spPrep[2];
		}
	}
}
$edit_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$buttonSubmodule."&includefile=list".(($ID > 0 && $buttonlisttype == "inputform" && $relationModule.$relationField!="")?"&submodule=".$relationModule.$relationField:'').($buttonContentStatus!=""?"&content_status=".$buttonContentStatus:"");
?><a href="<?php echo $edit_link;?>" class="optimize" role="menuitem"><?php echo $buttonsArray[1];?></a>