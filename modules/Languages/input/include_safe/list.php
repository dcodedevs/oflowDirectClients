<?php
//check user permissions
if($variables->developeraccess >= 20)
{
	//default include
	include(dirname(__FILE__)."/../includes/list.php");
} else {
	?><div id="hovedfeltStrek"><table style="width:100%"><tr><td class="notAccessField"><?=$formText_YouHaveNoAccessToThisModule_input;?></td></tr></table></div><?php
}
?>