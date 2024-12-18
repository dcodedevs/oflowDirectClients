<?php
if(isset($_POST['send']))
{
	define('BASEPATH', realpath(__DIR__.'/../../../../').DIRECTORY_SEPARATOR);
	require_once(BASEPATH.'elementsGlobal/cMain.php');
	
	$v_param = array('companyaccessID' => $_GET['caID'], 'session' => $_COOKIE['sessionID'], 'username' => $_COOKIE['username']);
	$o_query = $o_main->db->get_where('session_framework', $v_param);
	if($o_query) $fw_session = $o_query->row_array();
	$menuaccess = json_decode($fw_session['cache_menu'],true);
	$access = $menuaccess[$_GET['module']][2];
	if($access >= 10)
	{
		$o_query = $o_main->db->query('SELECT languageID, name FROM language WHERE outputlanguage = 1 ORDER BY defaultOutputlanguage DESC, sortnr ASC');
		if($o_query && $o_query->num_rows()>0)
		{
			foreach($o_query->result_array() as $writeLanguage)
			{
				$o_check = $o_main->db->query('select * from seodata where contentID = ? and moduleID = ?', array($_POST['ID'], $_POST['moduleID']));
				if(!$o_check || ($o_check && $o_check->num_rows() == 0))
				{
					$o_main->db->query("insert into seodata set contentID = ?, moduleID = ?", array($_POST['ID'], $_POST['moduleID']));
					$o_find = $o_main->db->query('select * from seodata where contentID = ? and moduleID = ?', array($_POST['ID'], $_POST['moduleID']));
					if($o_find) $seocontentID = $o_find->row_array();
				}
				if($_POST['seodataID'] == '')
				{
					$o_main->db->query("insert into seodatacontent set  seodataID = ?, languageID = ?, seoTitle = ?, seoDescription = ?, seoKeywords = ?", array($seocontentID['id'], $writeLanguage['languageID'], $_POST['contentTitle_'.$writeLanguage['languageID']], $_POST['contentDescription_'.$writeLanguage['languageID']], $_POST['contentKeywords_'.$writeLanguage['languageID']]));
				} else {
					$o_main->db->query("update seodatacontent set seoTitle = ?, seoDescription = ?, seoKeywords = ? where seodataID = ? and languageID = ?", array($_POST['contentTitle_'.$writeLanguage['languageID']], $_POST['contentDescription_'.$writeLanguage['languageID']], $_POST['contentKeywords_'.$writeLanguage['languageID']], $_POST['seodataID'], $writeLanguage['languageID']));
				}
			}
		}
		
		if(isset($_POST['startpage']))
		{
			$o_main->db->query("update pageID set startpage = 0");
			$o_check = $o_main->db->query('select * from pageID where contentID = ?', array($_POST['ID']));
			if(!$o_check || ($o_check && $o_check->num_rows() == 0))
				$o_main->db->query("insert into pageID set contentID = ?, contentTable = ?, startpage = ?", array($_POST['ID'], $_GET['submodule'], 1));
			else
				$o_main->db->query("update pageID set startpage = ? where contentID = ? and contentTable = ?", array(1, $_POST['ID'], $_GET['submodule']));
		}
		header("Location: ".substr($_SERVER['HTTP_REFERER'],0,strpos($_SERVER['HTTP_REFERER'],"?"))."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&submodule=".$_GET['submodule']."");
	} else {
		?><div id="hovedfeltStrek"><table style="width:100%"><tr><td class="notAccessField" >You have no access to this module</td></tr></table></div><?php
	}
}

$contentDescription = $contentTitle = $contentKeywords = array();
$o_query = $o_main->db->query('select * from seodata, seodatacontent where seodata.contentID = ? and seodatacontent.seodataID = seodata.id and seodata.moduleID = ?', array($_GET['ID'], $moduleID));
if($o_query && $o_query->num_rows()>0)
{
	foreach($o_query->result_array() as $listseodata)
	{
		$contentTitle[$listseodata['languageID']] = $listseodata['seoTitle'];
		$contentDescription[$listseodata['languageID']] = $listseodata['seoDescription'];
		$contentKeywords[$listseodata['languageID']] = $listseodata['seoKeywords'];
		$seodataID = $listseodata['seodataID'];
	}
}

$startpage = array();
$o_query = $o_main->db->query('select * from pageID where contentID = ? and contentTable = ?', array($_GET['ID'], $_GET['submodule']));
if($o_query) $startpage = $o_query->row_array();
?>
<div style="font-size:16px;"></div>
<form action="../modules/<?php echo $_GET['module']."/input/includes/".$_GET['includefile'].".php?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$_GET['module']."&submodule=".$_GET['submodule']."&includefile=".$_GET['includefile'];?>" method="post">
	<input type="hidden" name="ID" value="<?php print $_GET['ID']; ?>" />
	<input type="hidden" name="seodataID" value="<?php print $seodataID; ?>" />
	<input type="hidden" name="moduleID" value="<?php print $moduleID; ?>" />
	<input type="hidden" name="loginType" value="<?php print ($access < 211?1:2) ?>" />
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr><td width="15%">Set as startpage</td><td width="85%" class="fieldholder"><input type="checkbox" name="startpage" value="1" <?php if($startpage['startpage'] == 1){ ?> checked="checked"<?php } ?> style="width:auto;" /></td></tr>
	<?php
	$o_query = $o_main->db->query('SELECT languageID, name FROM language WHERE outputlanguage = 1 ORDER BY defaultOutputlanguage DESC, sortnr ASC');
	if($o_query && $o_query->num_rows()>0)
	{
		foreach($o_query->result_array() as $writeLanguage)
		{
			?>
			<tr><td colspan="2"><strong><?php print $writeLanguage['name']; ?></strong></td></tr>
			<tr><td>Title</td><td class="fieldholder"><input type="text" name="contentTitle_<?php print $writeLanguage['languageID']; ?>" value="<?php print $contentTitle[$writeLanguage['languageID']]; ?>" /></td></tr>
			<tr><td valign="top">Description</td><td class="fieldholder"><textarea name="contentDescription_<?php print $writeLanguage['languageID']; ?>"><?php print $contentDescription[$writeLanguage['languageID']]; ?></textarea></td></tr>
			<tr><td valign="top">Keywords</td><td class="fieldholder"><textarea name="contentKeywords_<?php print $writeLanguage['languageID']; ?>"><?php print $contentKeywords[$writeLanguage['languageID']]; ?></textarea></td></tr>
			<?php
		}
	}
	?>
	</table>
	<div class="fieldholder" style="padding-top:5px; padding-left:10px;">
		<input style="background-color:#cccccc; font-family:Verdana, Arial, Helvetica, sans-serif; border:1px solid #000000; font-size:10px; font-weight:bold; padding-left:17px; padding-right:17px; line-height:20px;" type="submit" name="send" value="Save" />
	</div>  
</form>

