<?php
function sys_get_button_config($writeFile, $account_absolute_path)
{
	$return = array();
	$prebuttonconfig = '';
	$file = $account_absolute_path.$writeFile;
	if(is_file($file))
	{
		ftp_file_put_content($writeFile,str_replace("[%]preb","\$preb",str_replace("$","[%]",file_get_contents($file))));
		include($file);
		ftp_file_put_content($writeFile,str_replace("[%]","$",file_get_contents($file)));
		
		if(stristr($prebuttonconfig,"¤"))
		{
			$variabSplit = explode("¤",$prebuttonconfig);
			
			foreach($variabSplit as $ab)
			{
				$ut = str_replace("\"","",$ab);
				if(stristr($ut,":"))
				{
					$ubSplit = explode(":",$ut);
					$tempvariab = new Button();
					$tempvariab->init($ubSplit[0],str_replace("$","[%]",$ubSplit[1]),$ubSplit[2],$ubSplit[3],$ubSplit[4],$ubSplit[5],$ubSplit[6],$ubSplit[7]);
					$return[$ubSplit[0]] = $tempvariab;
				}
			}
		}
	}
	return $return;
}
?>