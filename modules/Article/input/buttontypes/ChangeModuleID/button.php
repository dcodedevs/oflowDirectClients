<?php
$returnString = substr($_SERVER['HTTP_REFERER'],0,strpos($_SERVER['HTTP_REFERER'],"?"))."?".(isset($_GET['pageID'])?"pageID=".$_GET['pageID'].'&':'').(isset($_SESSION['caID_'.$_GET['caID']]['fwbaseurl'])?$_SESSION['caID_'.$_GET['caID']]['fwbaseurl']."&":'')."module=$module&submodule=$submodule&includefile=list";
if(isset($_GET['relationID']))
{
	$returnString .="&relationID=".$_GET['relationID']."&relationfield=".$_GET['relationfield'];
}
//echo $returnString;

$findModule = mysql_query("select id from moduledata where name = '{$buttonSubmodule}'");
$findModule = mysql_fetch_array($findModule);
?>
<span class="buttonlink">
	<a href="<?php print $_SERVER['PHP_SELF']; ?>?<?=(isset($_GET['pageID'])?"pageID=".$_GET['pageID'].'&':'').(isset($_SESSION['caID_'.$_GET['caID']]['fwbaseurl'])?$_SESSION['caID_'.$_GET['caID']]['fwbaseurl']."&":'')."module={$module}&amp;includefile=buttoninclude&amp;buttontype=ChangeModuleID&amp;executefile=save&amp;table={$basetable->name}&amp;ID={$ID}&newModuleID={$findModule['id']}"; if($submodule) print "&amp;submodule={$submodule}"; if(isset($_GET['relationID'])) print "&relationID={$_GET['relationID']}&amp;relationfield={$_GET['relationfield']}"; print "&return=".urlencode($returnString); ?>" onClick="return confirmMessage('<?php print $formText_ChangeModuleId_Input; ?>?');">
		<?php print $buttonsArray[1]; ?>
	</a>
</span>
<script type="text/javascript">
function confirmMessage(msg) {
	var answer = confirm(msg);
	return answer;
}
</script>