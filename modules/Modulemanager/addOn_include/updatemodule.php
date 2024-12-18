<?php
if(!function_exists('get_curent_GET_params')) include(__DIR__.'/../input/includes/fnctn_get_curent_GET_params.php');
if(!function_exists("ftp_ext_file_put_content")) include(__DIR__."/ftp_commandsExternal.php");

$updatemodule = $_GET['update'];
$s = array('_','.ver');
$r = array('.','');
//check module
$update = array();
$modulebasedir = __DIR__."/../../".$updatemodule."/input/";
if(is_dir($modulebasedir))
{
	$d1 = scandir($modulebasedir);
	foreach($d1 as $entry1)
	{
		if($entry1 == "." || $entry1 == "..") continue;
		//echo $entry1."\n";
		if($entry1 == 'buttontypes' || $entry1 == 'fieldtypes')
		{
			$childs = array();
			$d2 = scandir($modulebasedir.$entry1.'/');
			foreach($d2 as $entry2)
			{
				if($entry2 == "." || $entry2 == "..") continue;
				//echo "- ".$entry2."\n";
				if(is_dir($modulebasedir.$entry1.'/'.$entry2.'/'))
				{
					$ver = "";
					$d3 = scandir($modulebasedir.$entry1.'/'.$entry2.'/');
					foreach($d3 as $entry3)
					{
						if($entry3 == "." || $entry3 == "..") continue;
						//echo "- - ".$entry3."\n";
						if(strpos($entry3,'.ver') !== false)
						{
							$ver = str_replace($s,$r,$entry3);
							break;
						}
					}
					$childs[$entry2] = array($entry2, '', 0, $ver, '', array());
				}
			}
			$update[$entry1] = array($entry1, '', 0, '', '', $childs);
		}
		else if(strpos($entry1,'.ver') !== false)
		{
			$update['standard'] = array($formText_StandardInput_input, '', 0, str_replace($s,$r,$entry1), '', array());
		}
	}
}

//get recommended version
$data = array('data'=>json_encode(array('action'=>'get_recommended_update_version', 'object'=>'modules/defaultmodule', 'version'=>$update['standard'][3])));
$url = 'https://s13.getynet.com/accounts/librarymanager/api/index.php';
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$v_data = json_decode($response,true);
if(isset($v_data['status']) && $v_data['status'] == 1)
{
	$sourcemodule = 'defaultmodule/'.$v_data['version'].'/defaultmodule';
} else {
	$sourcemodule = 'defaultmodule/CurrentVersion/defaultmodule';
}

//check library
$sourcemodulepath = $sourcemodule;
if(isset($_GET['sourcemodule']))
{
	$v_items = ftp_ext_get_filelist('',"/oldmodules_folder/OLDER_MODULES_folder/".$_GET['sourcemodule']);
	foreach($v_items as $v_item)
    {
		if($v_item['name'] == 'input')
		{
			$sourcemodule = $_GET['sourcemodule'];
			$sourcemodulepath = "../oldmodules_folder/OLDER_MODULES_folder/".$_GET['sourcemodule'];
			continue;
		}
	}
	$v_items = ftp_ext_get_filelist('',"/modules/".$_GET['sourcemodule']);
	foreach($v_items as $v_item)
    {
		if($v_item['name'] == 'input')
		{
			$sourcemodule = $sourcemodulepath = $_GET['sourcemodule'];
			continue;
		}
	}
}
$modulebasedir = "modules/".$sourcemodulepath;
$modulebasedirfiles = ftp_ext_get_filelist('',"modules/".$sourcemodulepath);
//$modulebasedir = __DIR__."/../../../../../modules/".$sourcemodulepath."/input/";
foreach($modulebasedirfiles as $name)
{
	if($name['name'] == 'input')
	{ 
		//$path = realpath($modulebasedir);
		//$d1 = scandir($modulebasedir);
		
		$d1 = ftp_ext_get_filelist('',$modulebasedir."/".$name['name']);
		foreach($d1 as $entry1)
		{
			
			if($entry1['name'] == 'buttontypes' || $entry1['name'] == 'fieldtypes')
			{  
				$childs = array();
				//$d2 = scandir($modulebasedir.$entry1.'/');
				 
				$d2 = ftp_ext_get_filelist('',$modulebasedir."/".$name['name']."/".$entry1['name']);
				foreach($d2 as $entry2)
				{
					 
					if(array_key_exists("dir",$entry2))//($modulebasedir.$entry1.'/'.$entry2.'/'))
					{
						$over = "";
						//$d3 = scandir($modulebasedir.$entry1.'/'.$entry2.'/');
						$d3 = ftp_ext_get_filelist('',$modulebasedir."/".$name['name']."/".$entry1['name'].'/'.$entry2['name']);
						foreach($d3 as $entry3)
						{
							
							if(strpos($entry3['name'],'.ver') !== false)
							{
								//print_r($entry3);exit;
								$ver = str_replace($s,$r,$entry3['name']);
								//echo "ver = $ver<br>";
								break;
								//continue;
							}
						}
						//echo "<br>updateentry = ".$modulebasedir."/".$name['name']."/".$entry1['name'].'/'.$entry2['name'];
						$update[$entry1['name']][5][$entry2['name']] = array($entry2['name'],$modulebasedir."/".$name['name']."/".$entry1['name'].'/'.$entry2['name'].":input/".$entry1['name'].'/'.$entry2['name'].':1', 1, $update[$entry1['name']][5][$entry2['name']][3], $ver, array());
					}
				}
				//$update[$entry1] = array($entry1, '', 0, '', '', $childs);
			}
			else if(strpos($entry1['name'],'.ver') !== false)
			{

				$update['standard'] = array($formText_StandardInput_input, $modulebasedir.'/input:input:0::'.$modulebasedir.'/input/includes:input/includes:1::'.$modulebasedir.'/input/settings:input/settings:0', 1, $update['standard'][3], str_replace($s,$r,$entry1['name']), array());
			}
		}
		continue;
	}
}

//check extra
$modulebasedir = "";
$d1 = ftp_ext_get_filelist('',$modulebasedir);
foreach($d1 as $entry1)
{
 
	if($entry1['name'] == 'buttontypes' || $entry1['name'] == 'fieldtypes')
	{
		$update['extra'.$entry1['name']] = array($formText_Extra_input.' '.$entry1['name'], '', 0, '', '', $childs);
		//$d2 = scandir($modulebasedir.$entry1.'/');
		$d2 = ftp_ext_get_filelist('',$entry1['name']);
		foreach($d2 as $entry2)
		{
			if(isset($entry2['dir']))
			{
				$over = "";
				$d3 = ftp_ext_get_filelist('',$modulebasedir."/".$entry1['name'].'/'.$entry2['name']);
				//$d3 = scandir($modulebasedir.$entry1.'/'.$entry2.'/');
				foreach($d3 as $entry3)
				{
					if(strpos($entry3['name'],'.ver') !== false)
					{
						$ver = str_replace($s,$r,$entry3['name']);
						break;
					}
				}
				$update['extra'.$entry1['name']][5][$entry2['name']] = array($entry2['name'], $modulebasedir.'/'.$entry1['name'].'/'.$entry2['name'].":input/".$entry1['name'].'/'.$entry2['name'].':1', 1, $update[$entry1['name']][5][$entry2['name']][3], $ver, array());
				//remove from standard if in custom library
				if(isset($update[$entry1['name']][5][$entry2['name']])) unset($update[$entry1['name']][5][$entry2['name']]);
			}
		}
	}
	 
}

//get older input versions
$libmodules = array();
$modulebasedirfiles = ftp_ext_get_filelist('',"modules/defaultmodule/");
$modulebasedir = "/modules/defaultmodule/";
foreach($modulebasedirfiles as $entry1)
{
	 //echo $modulebasedir."/".$entry1['name'];
	$d2 =  ftp_ext_get_filelist('',$modulebasedir."/".$entry1['name']."/defaultmodule");
	foreach($d2 as $entry2)
	{
		if($entry2['name'] == 'input')
		{//print_r($entry2['name']);
			$d3 =  ftp_ext_get_filelist('',$modulebasedir."/".$entry1['name']."/defaultmodule/input");
			foreach($d3 as $entry3)
			{
				if(strpos($entry3['name'],'.ver') !== false)
				{
					$libmodules["defaultmodule/".$entry1['name']."/defaultmodule"] = $entry1['name'].' ('.$formText_InputVersion_input.' '.str_replace($s,$r,$entry3['name']).')';
					continue;
				}
			}
			continue;
		}
	}
	
}
$libmodules = array_reverse($libmodules);
$modulebasedirfiles = ftp_ext_get_filelist('',"/oldmodules_folder/OLDER_MODULES_folder");
$modulebasedir = "/oldmodules_folder/OLDER_MODULES_folder/";
foreach($modulebasedirfiles as $entry1)
{
	 //echo $modulebasedir."/".$entry1['name'];
	$d2 =  ftp_ext_get_filelist('',$modulebasedir."/".$entry1['name']);
	foreach($d2 as $entry2)
	{
		if($entry2['name'] == 'input')
		{//print_r($entry2['name']);
			$d3 =  ftp_ext_get_filelist('',$modulebasedir."/".$entry1['name']."/input");
			foreach($d3 as $entry3)
			{
				if(strpos($entry3['name'],'.ver') !== false)
				{
					$libmodules[$entry1['name']] = $entry1['name'].' ('.$formText_InputVersion_input.' '.str_replace($s,$r,$entry3['name']).')';
					continue;
				}
			}
			continue;
		}
	}
	
}
?>
<div class="module-manager">
<div style="float:right;"><a href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&submodule=".$submodule."&folder=input&folderfile=input";?>" class="btn btn-default optimize"><?php echo $formText_Cancel_input;?></a></div>
<h1><?php echo $formText_UpdateModule_input;?>: <?php echo $updatemodule;?></h1>
<form method="post" action="<?php echo $extradir."/addOn_include/addlibrary.php?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&update=1";?>">
<input type="hidden" name="updatemodule" value="<?php echo $updatemodule;?>">
<input type="hidden" name="sourcemodule_original" value="<?php echo $sourcemodule;?>">
<div><b><?php echo $formText_UpdateFromModuleVersion_input;?>:</b> <select name="sourcemodule" id="mm_sourcemodule"><?php
foreach($libmodules as $key=>$libmodule)
{
	?><option value="<?php echo $key;?>"<?php echo ($sourcemodule==$key ? ' selected="selected"':'');?>><?php echo $libmodule;?></option><?php
}
?></select></div>
<?php
// Verify is library slave/main server synced
$b_server_is_synced = FALSE;
$data = array('data'=>json_encode(array("action"=>"is_server_synced", "server"=>$_SESSION['mm_library_host'])));
//call api
$url = 'https://s13.getynet.com/accounts/librarymanager/api/index.php';
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$s_response = curl_exec($ch);
curl_close($ch);

$v_response = json_decode($s_response,true);
if(isset($v_response['status'], $v_response['synced']) && 1 == $v_response['status'] && 1 == $v_response['synced'])
{
	$b_server_is_synced = TRUE;
}
if(!$b_server_is_synced)
{
	?><div class="alert alert-warning"><?php echo $formText_LibraryServerIsNotCompletelySynced_Modulemanager;?></div><?php
}
?>
<br/><br/><?php
foreach($update as $key1=>$item1)
{
	$ver = "";
	$checked = $downgrade = $custom = false;
	if($item1[3] != "" and $item1[4] != "")
	{
		if($item1[3] == $item1[4])
		{
			$ver = " (".$item1[3].")";
		} else {
			if(strpos($item1[3],'.cus') === false)
			{
				if(floatval($item1[3]) < floatval($item1[4]))
				{
					$checked = true;
				} else {
					$downgrade = true;
				}
			} else {
				$custom = true;
			}
			$ver = " (".$item1[3]." -> ".$item1[4].")";
		}
	}
	?><div class="parent"><?php
	if($item1[2]==1)
	{
		?><input class="<?php echo ($downgrade?'downgrade':($custom?'custom':'upgrade'));?>" type="checkbox" name="update_folder_<?php echo $key1;?>" value="<?php echo $item1[1];?>" <?php echo ($checked?'checked':'');?>><?php
	} else print '<br>';
	?><span class="<?php echo ($downgrade||$custom?'warn':($checked?'go':'')).($item1[2]==0 ? ' group':'');?>"><?php
	print $item1[0].$ver;
	
	?></span><?php echo ($downgrade||$custom?'<img border="0" src="'.$extradir.'/addOn_include/elementsInput/warning_small.jpg" alt="" align="texttop" height="14">':'');?></div>
	<div class="childs"><?php
	
	if($key1 == 'fieldtypes' || $key1 == 'buttontypes' || $key1 == 'extrafieldtypes' || $key1 == 'extrabuttontypes')
	{
		foreach($item1[5] as $key2=>$item2)
		{
			$ver = "";
			$checked = $downgrade = $custom = false;
			if($item2[3] != "" and $item2[4] != "")
			{
				if($item2[3] == $item2[4])
				{
					$ver = " (".$item2[3].")";
				} else {
					if(strpos($item2[3],'.cus') === false)
					{
						if(floatval($item2[3]) < floatval($item2[4]))
						{
							$checked = true;
						} else {
							$downgrade = true;
						}
					} else {
						$custom = true;
					}
					$ver = " (".$item2[3]." -> ".$item2[4].")";
				}
			} else if($item2[3] == "" && $item2[4] == "")
			{
				$custom = true;
			} else if($item2[3] == "")
			{
				if($key1 == 'fieldtypes' || $key1 == 'buttontypes') $checked = true;
				$ver = " (".$item2[4].")";
			} else if($item2[4] == "")
			{
				$ver = " (".$item2[3].")";
				$custom = true;
			}
			?><div class="child"><?php
			if($item2[2]==1)
			{
				?><input class="<?php echo ($downgrade?'downgrade':($custom?'custom':'upgrade'));?>" type="checkbox" name="update_folder_<?php echo $key1.$key2;?>" value="<?php echo $item2[1];?>" <?php echo ($checked?'checked':'');?>><?php
			}
			?><span class="<?php echo ($downgrade||$custom?'warn':($checked?'go':''));?>"><?php
			print $item2[0].$ver;
			?></span><?php echo ($downgrade||$custom?'<img border="0" src="'.$extradir.'/addOn_include/elementsInput/warning_small.jpg" alt="" align="texttop" height="14">':'');?></div><?php
		}
	}
	?></div><?php
}
?>
<div>
	<input type="submit" class="btn btn-success" name="submbtn" value="<?php echo $formText_UpdateSelected_input;?>">
	<a href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&submodule=".$submodule."&folder=input&folderfile=input";?>" class="btn btn-default optimize"><?php echo $formText_Cancel_input;?></a>
</div>
</form>
</div>
<script type="text/javascript">
$(function() {
	$('.module-manager div.parent span').on('click',function() {
		if($(this).is('.group'))
		{
			if($(this).parent().next('.childs').find('input:checkbox').is(':checked'))
				$(this).parent().next('.childs').find('input:checkbox:checked').trigger('click');
			else
				$(this).parent().next('.childs').find('input:checkbox:not(:checked)').trigger('click');
		} else {
			$(this).prev('input:checkbox').trigger('click');
		}
	});
	$('.module-manager div.parent input:checkbox').on('change',function() {
		if($(this).parent().next('.childs').find('input:checkbox').is(':checked'))
			$(this).parent().next('.childs').find('input:checkbox:checked').trigger('click');
		else
			$(this).parent().next('.childs').find('input:checkbox:not(:checked)').trigger('click');
	});
	$('.module-manager div.child span').on('click',function() {
		$(this).prev('input:checkbox').trigger('click');
	});
	$('.module-manager input.upgrade').on('change',function() {
		if($(this).is(':checked'))
		{
			$(this).next('span').addClass('go');
		} else
			$(this).next('span').removeClass('go');
	});
	$('.module-manager input.downgrade').on('change',function() {
		if($(this).is(':checked'))
		{
			if(!confirm('<?php echo $formText_Downgrade_input;?>: ' + $(this).next('span').text() + '?')){ $(this).removeAttr('checked'); return; }
			$(this).next('span').addClass('go');
		} else
			$(this).next('span').removeClass('go');
	});
	$('.module-manager input.custom').on('change',function() {
		if($(this).is(':checked'))
		{
			if(!confirm('<?php echo $formText_ThisObjectIsCustomizedAreYouSureYouWantToUpdate_input;?>: ' + $(this).next('span').text() + '?')){ $(this).removeAttr('checked'); return; }
			$(this).next('span').addClass('go');
		} else
			$(this).next('span').removeClass('go');
	});
	$('#mm_sourcemodule').on('change',function(){
		var $obj = $("<a>").addClass("optimize").attr("href","<?php echo $_SERVER['PHP_SELF']."?".get_curent_GET_params(array('sourcemodule'));?>&sourcemodule=" + $(this).val());
		$(".module-manager").append($obj);
		$obj.trigger("click");
	});
});
</script>