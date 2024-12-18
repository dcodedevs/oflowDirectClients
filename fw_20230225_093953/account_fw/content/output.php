<?php
$getcode = explode("&",urldecode($variables->fw_session['urlpath']));
for($x=0;$x<count($getcode);$x++)
{
	list($name,$value) = explode("=",$getcode[$x]);
	${$name} = str_replace("%2F","/",$value);
}
reset($_GET);
foreach($_GET as $name=>$value)
{
	${$name} = str_replace("%2F","/",$value);
}

$access = 0;
if(isset($editCompany) && $editCompany > 0) $companyID = $editcompany;
if(isset($module) && is_numeric($module))
{
	$moduleID = $module;
} else {
	$o_query = $o_main->db->query('SELECT * FROM moduledata WHERE name = ?', array($module));
	if($o_query && $v_row = $o_query->row_array())
	{
		$moduleID = ((isset($v_row['uniqueID']) && $v_row['uniqueID'] > 0) ? $v_row['uniqueID'] : $v_row['id']);
	} else {
		echo $formText_ModuleNotFound_ModuleContent;
		return;
	}
}
if(!isset($_GET['modulename'])) $modulename = $module;
$username = $variables->loggID;

// Virtual module check
$variables->is_virtual_module = FALSE;
$o_query = $o_main->db->query("SELECT * FROM moduledata WHERE name = '".$o_main->db->escape_str($modulename)."' AND virtual_module_source IS NOT NULL AND virtual_module_source != ''");
if($o_query && $o_query->num_rows()>0)
{
	$v_row = $o_query->row_array();
	$variables->is_virtual_module = TRUE;
	$variables->virtual_module = $modulename;
	$variables->virtual_module_name = $v_row['local_name'];
	$modulename = $module = $v_row['virtual_module_source'];
	$moduleID = ((isset($v_row['uniqueID']) && $v_row['uniqueID'] > 0) ? $v_row['uniqueID'] : $v_row['id']);
}

$moduleAccesslevel = (isset($variables->menu_access[$module]) ? $variables->menu_access[$module][2] : 0);

if(isset($_GET['getynetaccount']))
{
	$adminmodules = array("","37");
	if($variables->useradminaccess == 1 && $usercompanyaccess > 0 && $_GET['folder'] == 'output' && array_search($_GET['module'],$adminmodules)>0)
		$moduleAccesslevel = 1;
	else if(stristr($_GET['folderfile'],'outputprofile'))
		$moduleAccesslevel = 1;
	else if(stristr($_GET['modulename'],'Appstore') && $_GET['folder'] == 'output' && array_search($_GET['module'],$adminmodules)>0 )
		$moduleAccesslevel = 1;
	else
		$moduleAccesslevel = 0;
}

if(($variables->loggID != '0' && $variables->loggID != '' && isset($folder) && isset($folderfile)))
{
	$extradiraccountname = $variables->languageDir2."".$accountname;
	if(isset($_GET['getynetaccount']))
	{
		$fw_path = explode("/accounts/",realpath(__DIR__."/../../"));
		$fw_path = $fw_path[1];
		$languagedir = "getynet_fw/";
		$languagedir = $accountname."/";
		$extraabsdir = $_SERVER['DOCUMENT_ROOT']."/accounts/".$fw_path."/getynet_fw/modules/".$modulename."/".$folder."/";
		$extradomaindir = $variables->account_framework_url."getynet_fw/modules/".$modulename."/".$folder."/";
		$tmp = explode("/",$_SERVER['PHP_SELF']);
		$script_name = array_pop($tmp);
		$fw_domain_url = $variables->account_framework_url.$script_name."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID'].(!$variables->fw_url_share?"&caID=".$_GET['caID']:'');
		$testvariable = 7;
		$extradir = "getynet_fw/modules/".$modulename."";
		$editordir = $accountname;

	} else {
		$extradir = $languagedir = $variables->languageDir2."../";
		$extraabsdir = realpath(__DIR__."/../../../")."/modules/".$modulename."/".$folder."/";
		$extradomaindir = $variables->account_root_url."modules/".$modulename."/".$folder."/";
		$extradomaindirroot = $variables->account_root_url;
		$versiondir = $extradir."modules/".$modulename."/input";
		if($vd = opendir($versiondir))
		{
			while(($versionfile = readdir($vd)) !== false)
			{
				if(strpos($versionfile,".ver") > 0 && !stristr($versionfile,"LCK"))
				{
					$modefile = $versionfile;
					$modulemode = substr($versionfile,0,1);
					$versionfile2 = substr($versionfile,0);
					$moduleversion = floatval(str_replace("_",".",substr($versionfile2,0,strpos($versionfile2,".ver"))));
				}
			}
		}

		$testvariable = $moduleversion;
		if($testvariable >= 6.22 || (isset($external) && $external != ''))
		{
			if(isset($external) && $external != '')
			{
				$extradir = $variables->languageDir2."".$external;
				$editordir = substr($external,0,strpos($external,"/"));
			} else {
				if(isset($_GET['getynetaccount']))
				{
					$extradir = $accountname."/modules/".$modulename."";
					$editordir = $accountname;
				} else {
					$extradir = $variables->languageDir2."../modules/".$modulename."";
					$editordir = $extradir;
				}
			}
			$parentdir = $extradir;
			$extraimagedir = $extradir;
			$extraoutputimagedir = $extradir;
		}
	}

	$dir = realpath(__DIR__."/../../../")."/modules/";
	$developerLanguageID = $variables->accountinfo['developerlanguageID'];
	$choosenAdminLang = $variables->accountinfo['customerlanguageID'];
	$choosenListInputLang = $variables->accountinfo['customerlanguageID'];
	$accounttype = $variables->accountinfo['accounttype'];
	$choosenInputLang = (strtolower($accounttype) == 'web' ? 'all' : $variables->accountinfo['customerlanguageID']);

	$module = $modulename;
	$folderfile = 'input';
	$folder = 'input';
	if(isset($_GET['folder'])) $folder = $_GET['folder'];
	if(isset($_GET['folderfile'])) $folderfile = $_GET['folderfile'];
	if($folder != "input") $fw_column = $fw_columns = 1;

	if(!isset($_POST['fw_nocss']) && !isset($_GET['fw_nocss']))
	{
		if(isset($variables->accountinfo['bannerimage']) && $variables->accountinfo['bannerimage'] != '')
		{
			?><img src="<?php echo $languagedir.$variables->accountinfo['bannerimage']; ?>" alt="" border="0" /><?php
		}
		if($testvariable >= 6.22 || (isset($external) && $external != ''))
		{
			if(is_file($extradir."/".$folder."/".$folderfile.".css"))
			{
				$l_time = filemtime($extradir."/".$folder."/".$folderfile.".css");
				?><link href="<?php echo $variables->account_framework_url.$extradir."/".$folder."/".$folderfile.".css?v=".$l_time; ?>" rel="stylesheet" type="text/css"><?php
			}
		} else {
			if(is_file($extradir."modules/".$modulename."/".$folder."/".$folderfile.".css"))
			{
				$l_time = filemtime($extradir."modules/".$modulename."/".$folder."/".$folderfile.".css");
				?><link href="<?php echo $variables->account_root_url."modules/".$modulename."/".$folder."/".$folderfile.".css?v=".$l_time; ?>" rel="stylesheet" type="text/css"><?php
			}
		}
	}
	
	if($testvariable >= 6.22 || (isset($external) && $external != ''))
	{
		include($extradir."/".$folder."/".$folderfile.".php");
	} else {
		include($extradir."modules/".$modulename."/".$folder."/".$folderfile.".php");
	}
} else {

	print $formText_NoAccessToThisModule_ModuleContent;
}