<?php
$updatemodule = $_GET['update'];
?>
<div class="module-manager">
<h3><?php echo $formText_CheckingIsThereIso88591EncodingInModule_input;?>: <?php echo $updatemodule;?></h3>
<form name="changeEncForm" action="<?php echo $extradir."/addOn_include/updatemoduleencoding.php?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID'];?>" method="POST">
<?php
$counter = 0;
$excludes = array('seo','languages','mainlayout','modulemanager','designmanager');
$dir = dirname(__FILE__)."/../../".$updatemodule."/";

if(is_dir($dir))
{
	$dir = rtrim(realpath($dir),'/').'/';
	$scan = scandir($dir);
	foreach($scan as $item)
	{
		if($item[0] == "." or in_array(strtolower($item),$excludes)) continue;
		//echo $item."\n";
		
		if(is_file($dir.$item))
		{
			$r = exec("file -bi ".$dir.$item);
			if((strpos($r,'text/plain')!==false or strpos($r,'text/x-php')!==false) and strpos($r,'iso-8859-1')!==false)
			{
				$encoding = explode('charset=',$r);
				print $encoding[1].':  '.$dir.$item.'<br/><br/>';
				/*print 'cp '.$dir.$item.' '.$dir.$item.'.enc_bckp<br/>';
				print 'iconv -f '.$encoding[1].' -t utf-8 '.$dir.$item.' > '.$dir.$item.'.utf8<br/>';
				print 'cp '.$dir.$item.'.utf8 '.$dir.$item.'<br/><br/>';*/
				$counter++;
			}
		}
		if(is_dir($dir.$item.'/'))
		{
			$scan2 = scandir($dir.$item.'/');
			foreach($scan2 as $item2)
			{
				if($item2[0] == ".") continue;
				//echo $item."\n";
				
				if(is_file($dir.$item.'/'.$item2))
				{
					$r = exec("file -bi ".$dir.$item.'/'.$item2);
					if((strpos($r,'text/plain')!==false or strpos($r,'text/x-php')!==false) and strpos($r,'iso-8859-1')!==false)
					{
						$encoding = explode('charset=',$r);
						$url = $dir.$item.'/'.$item2; 
						print $encoding[1].':  '.$url.'<br/><br/>';
						/*print 'cp '.$dir.$item.'/'.$item2.' '.$dir.$item.'/'.$item2.'.enc_bckp<br/>';
						print 'iconv -f '.$encoding[1].' -t utf-8 '.$dir.$item.'/'.$item2.' > '.$dir.$item.'/'.$item2.'.utf8<br/>';
						print 'cp '.$dir.$item.'/'.$item2.'.utf8 '.$dir.$item.'/'.$item2.'<br/><br/>';*/
						$counter++;
					}
				}
				
				if(is_dir($dir.$item.'/'.$item2))
				{
					$scan3 = scandir($dir.$item.'/'.$item2.'/');
					foreach($scan3 as $item3)
					{
						if($item3[0] == ".") continue;
						//echo $item."\n";
						
						if(is_file($dir.$item.'/'.$item2.'/'.$item3))
						{
							$r = exec("file -bi ".$dir.$item.'/'.$item2.'/'.$item3);
							if((strpos($r,'text/plain')!==false or strpos($r,'text/x-php')!==false) and strpos($r,'iso-8859-1')!==false)
							{
								$url = $dir.$item.'/'.$item2.'/'.$item3;
								$encoding = explode('charset=',$r);  
								print $encoding[1].':  '.$dir.$item.'/'.$item2.'/'.$item3.'<br/><br/>';
								/*print 'cp '.$dir.$item.'/'.$item2.'/'.$item3.' '.$dir.$item.'/'.$item2.'/'.$item3.'.enc_bckp<br/>';
								print 'iconv -f '.$encoding[1].' -t utf-8 '.$dir.$item.'/'.$item2.'/'.$item3.' > '.$dir.$item.'/'.$item2.'/'.$item3.'.utf8<br/>';
								print 'cp '.$dir.$item.'/'.$item2.'/'.$item3.'.utf8 '.$dir.$item.'/'.$item2.'/'.$item3.'<br/><br/>';*/
								$counter++;
							}
						}
					}
				}
				
			}
		}
	}
}
print '<h2>'.$formText_Found_input.' '.$counter.' '.$formText_files_input.'. '.($counter>0 ? $formText_FilesShouldBeUpdated_input : $formText_ThatsGood_input).'!</h2>';
if(1==0 and $counter>0)
{
	?><input type="submit" class="btn btn-success" name="submbtn" value="<?php echo $formText_ConvertSelectedFiles_input;?>"><?php
}
?>
</form>
<br>
<div><a href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&submodule=".$submodule."&folder=input&folderfile=input";?>" class="btn btn-default optimize"><?php echo $formText_Back_input;?></a></div>
</div>