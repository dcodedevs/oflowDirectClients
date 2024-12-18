<?php
ob_start();
require_once(__DIR__."/../../../includes/APIconnector.php");
$data = json_decode(APIconnectorUser("accountbycompanyidgetlist", $_COOKIE['username'], $_COOKIE['sessionID'], array('COMPANY_ID'=>$_GET['companyID'])),true);
$apiAccounts = $data['data'];
?>
<table border="0" cellpadding="0" cellspacing="0" rules="none" frame="void" style="background-color:#EEEEEE; width:100%">
<?php
foreach($apiAccounts as $apiAccount)
{
	?><tr>
		<td style="padding-left:30px; width:120px;"><?php echo $apiAccount['accountname'];?></td>
		<td width="400" align="left">
			<select name="accountaccess_<?php echo $apiAccount['id'];?>" id="accountaccess_<?php echo $apiAccount['id'];?>_id" onChange="javascript:fw_useradmin_updateaccountlevel('<?php echo $apiAccount['id'];?>','<?php echo $_GET['extradir'];?>','<?php echo $_GET['module'];?>','<?php echo $_GET['inputlang'];?>','<?php echo $_GET['module'];?>','<?php echo $_GET['listname'];?>','<?php echo $_GET['listname2'];?>','<?php echo $apiAccount['accountname'];?>','<?php echo $apiAccount['accounttype'];?>');">
				<option value="1"><?php echo $_GET['selectvalue1'];?></option>
				<option value="2" ><?php echo $_GET['selectvalue2'];?></option>
				<option value="0" selected="selected"><?php echo $_GET['selectvalue3'];?></option>
			</select>
		</td>
	</tr>
	<tr><td colspan="2"><div id="accountmodulesaccesslistid_<?php echo $apiAccount['id']; ?>"></div></td></tr>
	<?php
}
?></table><?php
$return = array();
$return['html'] = ob_get_clean();
print json_encode($return);
?>
