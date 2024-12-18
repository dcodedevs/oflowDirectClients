<?php
while(list($param,$value) = each($_POST))
{ 
	if(stristr($param,"check"))
	{	
		echo "iconv --from-code=ISO-8859-1 --to-code=UTF-8 ".$value." -o ".$value."<br>";
		echo passthru("iconv --from-code=ISO-8859-1 --to-code=UTF-8 ".$value." -o ".$value);
	}
}
header("Location: ".substr($_SERVER['HTTP_REFERER'],0,strpos($_SERVER['HTTP_REFERER'],"?"))."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=Modulemanager&updenc=1");
exit;
?>