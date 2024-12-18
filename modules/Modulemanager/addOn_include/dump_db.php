<?php
if(isset($_POST['dump_db']))
{
	$s_data_tables = "";
	if(isset($_POST['data_tables']))
	{
		foreach($_POST['data_tables'] as $s_table)
		{
			if($s_data_tables != "") $s_data_tables .= " ";
			$s_data_tables .= $s_table;
		}
	}
	//dump account db by selection
	$v_accountinfo = array();
	$o_query = $o_main->db->query('SELECT * FROM accountinfo');
	if($o_query && $o_query->num_rows()>0) $v_accountinfo = $o_query->row_array();
	if(!function_exists('APIconnectAccount')) include(__DIR__.'/../input/includes/APIconnect.php');
	$s_response = APIconnectAccount("account_dump_db", $v_accountinfo['accountname'], $v_accountinfo['password'], array('DATA_TABLES'=>$s_data_tables));
	if(trim($s_response) == "OK")
	{
		$error_msg["info_".count($error_msg)] = "Account DB dump will be created in few minutes.";
	} else {
		$error_msg["error_".count($error_msg)] = "Error occured creating account DB dump. Try again.";
	}
	
	if(count($error_msg)>0)
	{
		$v_param = array('companyaccessID' => $_GET['caID'], 'session' => $_COOKIE['sessionID'], 'username' => $_COOKIE['username']);
		$o_main->db->update('session_framework', array('error_msg' => json_encode($error_msg)), $v_param);
	}
	
	header("Location: ".substr($_SERVER['HTTP_REFERER'],0,strpos($_SERVER['HTTP_REFERER'],"?"))."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=Modulemanager");
}
?>
<div class="module-manager">
<h2><?php echo $formText_DumpAccontDatabaseWithEmptyStructureInFile_input;?>: db_upgrade_dump.sql</h2>
<form method="post" action="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&".(isset($_GET['moduleID'])?"moduleID=".$_GET['moduleID']."&":'')."module=".$module."&includefile=dump_db";?>">
<input type="hidden" name="dump_db" value="1">
<br />
<h4><?php echo $formText_InExtraReplaceDataForTables_input;?>: db_upgrade_dump_replace.sql</h4>
<div class="parent">
	<input class="upgrade" type="checkbox" name="data_parent" value="1"> <span class=""><?php echo $formText_Tables_input;?></span>
</div>
<div class="childs"><?php
	$v_find_auto_replace = array
	(
		'language',
		'moduledata',
		'sys_emailserverconfig',
		'sys_smsserviceconfig',
		'sys_modulemenugroup',
		'sys_modulemenugroupcontent',
		'sys_modulemenulink',
		'sys_modulemenuset',
		'sys_modulemenuusers'
	);
	$v_tables = $o_main->db->list_tables();
	foreach($v_tables as $s_table)
	{
		$b_checked = ((in_array($s_table, $v_find_auto_replace) || substr($s_table, -12) == '_basisconfig' || substr($s_table, -19) == '_basisconfigcontent') ? true : false);
		?><div class="child">
			<input class="upgrade" type="checkbox" name="data_tables[]" value="<?php echo $s_table;?>"<?php echo ($b_checked?' checked':'');?>> <span class="<?php echo ($b_checked?'go':'');?>"><?php echo $s_table;?></span>
		</div><?php
	}
?></div>
<br/>
<div>
	<input type="submit" class="btn btn-success" name="submbtn" value="<?php echo $formText_MakeDatabaseDump_input;?>">
	<a href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&submodule=".$submodule."&folder=input&folderfile=input";?>" class="btn btn-default optimize"><?php echo $formText_Cancel_input;?></a>
</div>
</form>
</div>
<script type="text/javascript">
$(function() {
	$('.module-manager div.parent span').on('click',function() {
		$(this).prev('input:checkbox').trigger('click');
	});
	$('.module-manager div.parent input:checkbox').on('change',function() {
		if($(this).parent().next('.childs').children('.child').children('input:checkbox').is(':checked'))
			$(this).parent().next('.childs').children('.child').children('input:checkbox:checked').trigger('click');
		else
			$(this).parent().next('.childs').children('.child').children('input:checkbox:not(:checked)').trigger('click');
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
});
</script>