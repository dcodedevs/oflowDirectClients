<a href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&".(isset($_GET['moduleID'])?"moduleID=".$_GET['moduleID']."&":'')."module=".$buttonSubmodule."&includefile=addlibrary&type=accounts&dir=".urlencode('accounts/'.$accountname.'/modules')."&duplicate=1";?>" class="optimize" role="menuitem"><?php echo $buttonsArray[1];?></a>