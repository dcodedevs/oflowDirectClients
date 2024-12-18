<?php
if(!function_exists("ftp_file_put_content")) require_once(__DIR__."/ftp_commands.php");
if(isset($_POST['save_content_access']))
{
	define('BASEPATH', realpath(__DIR__.'/../../../../').DIRECTORY_SEPARATOR);
	require_once(BASEPATH.'elementsGlobal/cMain.php');
	if(!function_exists("log_action")) include(__DIR__."/fn_log_action.php");
	log_action("save_content_access");
	$s_file = __DIR__."/../settings/access/content_access.php";
	$s_ftp_file = "modules/".$_GET['module']."/input/settings/access/content_access.php";
	
	if(!is_file($s_file)) ftp_file_put_content($s_ftp_file,'<?php'.PHP_EOL.'$v_content_access = array'.PHP_EOL.'('.PHP_EOL.');'.PHP_EOL);
	include($s_file);
	
	$s_file_content = '';
	foreach($_POST['access_id'] as $l_key => $l_access_id)
	{
		$l_access_id = intval($l_access_id);
		if($l_access_id==0) continue;
		$s_name = base64_encode($_POST['access_name'][$l_key]);
		$l_always_checked = intval($_POST['always_checked'][$l_key]);
		if($s_file_content!="") $s_file_content .= ",";
		$s_file_content .= PHP_EOL."\tarray(".$l_access_id.", \"".$s_name."\", ".$l_always_checked.")";
	}
	ftp_file_put_content($s_ftp_file,'<?php'.PHP_EOL.'$v_content_access = array'.PHP_EOL.'('.$s_file_content.PHP_EOL.');'.PHP_EOL);
	
	$s_file = __DIR__."/../settings/access/extended_content_access.php";
	$s_ftp_file = "modules/".$_GET['module']."/input/settings/access/extended_content_access.php";
	
	if(!is_file($s_file)) ftp_file_put_content($s_ftp_file,'<?php'.PHP_EOL.'$v_extended_content_access = array'.PHP_EOL.'('.PHP_EOL.');'.PHP_EOL);
	include($s_file);
	
	$s_file_content = '';
	foreach($_POST['extended_access_id'] as $l_key => $l_access_id)
	{
		$l_access_id = intval($l_access_id);
		if($l_access_id==0) continue;
		$s_name = base64_encode($_POST['extended_access_name'][$l_key]);
		$l_always_checked = intval($_POST['extended_always_checked'][$l_key]);
		if($s_file_content!="") $s_file_content .= ",";
		$s_file_content .= PHP_EOL."\tarray(".$l_access_id.", \"".$s_name."\", ".$l_always_checked.")";
	}
	ftp_file_put_content($s_ftp_file,'<?php'.PHP_EOL.'$v_extended_content_access = array'.PHP_EOL.'('.$s_file_content.PHP_EOL.');'.PHP_EOL);

	header("Location: ".substr($_SERVER['HTTP_REFERER'],0,strpos($_SERVER['HTTP_REFERER'],"?"))."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$_GET['module']."&submodule=".$_GET['submodule']."&includefile=".$_GET['includefile']);
	exit;
}

?>
<form class="form-horizontal" action="<?php echo $extradir."/input/includes/".$_GET['includefile'].".php?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$_GET['module']."&submodule=".$_GET['submodule']."&includefile=".$_GET['includefile'];?>" method="post">
	<input type="hidden" name="save_content_access" value="1">
	<div>
		<h3><?php echo $formText_EditContentAccess_input;?></h3>
		<div style="margin-bottom:5px;">
			<button id="content_access_add" type="button" class="btn btn-default btn-sm">
				<span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
			</button>
		</div>
		<div id="content_access_sortable">
		<?php
		include(__DIR__."/../settings/access/content_access.php");
		foreach($v_content_access as $v_item)
		{
			if(base64_decode($v_item[1], true) !== false)
			{
				$v_item[1] = base64_decode($v_item[1]);
			}
			?><table width="100%" border="0" cellpadding="0" cellspacing="0" style="margin-bottom:8px;">
				<tr>
					<td width="5%" style="text-transform:uppercase; font-weight:bold;"><?php echo $formText_Id_input;?>:</td>
					<td width="10%"><input class="form-control" type="text" name="access_id[]" value="<?php echo $v_item[0];?>"></td>
					<td width="3%"></td>
					<td width="10%"><?php echo $formText_Name_input;?>:</td>
					<td width="35%"><input class="form-control" type="text" name="access_name[]" value="<?php echo $v_item[1];?>"></td>
					<td width="2%"></td>
					<td width="15%"><input type="hidden" name="always_checked[]" value="<?php echo $v_item[2];?>"><input type="checkbox"<?php echo ($v_item[2]==1?" checked":"");?> onChange="$(this).prev().val(this.checked?1:0);"> <label><?php echo $formText_AlwaysChecked_input;?></label></td>
					<td align="right">
						<button type="button" class="btn btn-danger btn-sm" onClick="$(this).closest('table').remove();"><span class="glyphicon glyphicon-trash"></span></button>
						<button type="button" class="btn btn-default btn-sm" disabled><span class="glyphicon glyphicon-sort"></span></button>
					</td>
				</tr>
			</table><?php
		}
		?>
		</div>
		<h3><?php echo $formText_EditExtendedContentAccess_input;?></h3>
		<div style="margin-bottom:5px;">
			<button id="extended_content_access_add" type="button" class="btn btn-default btn-sm">
				<span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
			</button>
		</div>
		<div id="extended_content_access_sortable">
		<?php
		include(__DIR__."/../settings/access/extended_content_access.php");
		foreach($v_extended_content_access as $v_item)
		{
			if(base64_decode($v_item[1], true) !== false)
			{
				$v_item[1] = base64_decode($v_item[1]);
			}
			?><table width="100%" border="0" cellpadding="0" cellspacing="0" style="margin-bottom:8px;">
				<tr>
					<td width="5%" style="text-transform:uppercase; font-weight:bold;"><?php echo $formText_Id_input;?>:</td>
					<td width="10%"><input class="form-control" type="text" name="extended_access_id[]" value="<?php echo $v_item[0];?>"></td>
					<td width="3%"></td>
					<td width="10%"><?php echo $formText_Name_input;?>:</td>
					<td width="35%"><input class="form-control" type="text" name="extended_access_name[]" value="<?php echo $v_item[1];?>"></td>
					<td width="2%"></td>
					<td width="15%"><input type="hidden" name="extended_always_checked[]" value="<?php echo $v_item[2];?>"><input type="checkbox" <?php echo ($v_item[2]==1?" checked":"");?> onChange="$(this).prev().val(this.checked?1:0);"> <label><?php echo $formText_AlwaysChecked_input;?></label></td>
					<td align="right">
						<button type="button" class="btn btn-danger btn-sm" onClick="$(this).closest('table').remove();"><span class="glyphicon glyphicon-trash"></span></button>
						<button type="button" class="btn btn-default btn-sm" disabled><span class="glyphicon glyphicon-sort"></span></button>
					</td>
				</tr>
			</table><?php
		}
		?>
		</div>
	</div>
	<div style="margin-top:20px;">
		<button type="submit" class="btn btn-success"><?php echo $formText_save_input;?></button>
		<a class="btn btn-default optimize" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$_GET['module']."&submodule=".$_GET['submodule'];?>"><?php echo $formText_Cancel_input;?></a>
	</div>
</form>
<div id="content_access_clone" style="display:none;">
<table width="100%" border="0" cellpadding="0" cellspacing="0" style="margin-bottom:8px;">
	<tr>
		<td width="5%" style="text-transform:uppercase; font-weight:bold;"><?php echo $formText_Id_input;?></td>
		<td width="10%"><input class="form-control" type="text" name="access_id[]" value=""></td>
		<td width="3%"></td>
		<td width="10%"><?php echo $formText_Name_input;?></td>
		<td width="35%"><input class="form-control" type="text" name="access_name[]" value=""></td>
		<td width="2%"></td>
		<td width="15%"><input type="hidden" name="always_checked[]" value="0"><input type="checkbox" onChange="$(this).prev().val(this.checked?1:0);"> <label><?php echo $formText_AlwaysChecked_input;?></label></td>
		<td align="right">
			<button type="button" class="btn btn-danger btn-sm" onClick="$(this).closest('table').remove();"><span class="glyphicon glyphicon-trash"></span></button>
			<button type="button" class="btn btn-default btn-sm" disabled><span class="glyphicon glyphicon-sort"></span></button>
		</td>
	</tr>
</table>
</div>
<div id="extended_content_access_clone" style="display:none;">
<table width="100%" border="0" cellpadding="0" cellspacing="0" style="margin-bottom:8px;">
	<tr>
		<td width="5%" style="text-transform:uppercase; font-weight:bold;"><?php echo $formText_Id_input;?></td>
		<td width="10%"><input class="form-control" type="text" name="extended_access_id[]" value=""></td>
		<td width="3%"></td>
		<td width="10%"><?php echo $formText_Name_input;?></td>
		<td width="35%"><input class="form-control" type="text" name="extended_access_name[]" value=""></td>
		<td width="2%"></td>
		<td width="15%"><input type="hidden" name="extended_always_checked[]" value="0"><input type="checkbox" onChange="$(this).prev().val(this.checked?1:0);"> <label><?php echo $formText_AlwaysChecked_input;?></label></td>
		<td align="right">
			<button type="button" class="btn btn-danger btn-sm" onClick="$(this).closest('table').remove();"><span class="glyphicon glyphicon-trash"></span></button>
			<button type="button" class="btn btn-default btn-sm" disabled><span class="glyphicon glyphicon-sort"></span></button>
		</td>
	</tr>
</table>
</div>
<script type="text/javascript">
<?php if(isset($ob_javascript)) { ob_start(); } ?>
$(function(){
	$("#content_access_sortable").sortable();
	$("#content_access_add").on("click", function(){
		$("#content_access_sortable").append( $("#content_access_clone table").clone() );
	});
	$("#extended_content_access_sortable").sortable();
	$("#extended_content_access_add").on("click", function(){
		$("#extended_content_access_sortable").append( $("#extended_content_access_clone table").clone() );
	});
});
<?php if(isset($ob_javascript)) { $ob_javascript .= " ".ob_get_clean(); } ?>
</script>