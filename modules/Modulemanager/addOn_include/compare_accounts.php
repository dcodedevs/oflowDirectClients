<?php
if(!function_exists('get_curent_GET_params')) include(__DIR__.'/../input/includes/fnctn_get_curent_GET_params.php');
if(!function_exists('APIconnectAccount')) include(__DIR__.'/../input/includes/APIconnect.php');
$v_error_msg = array();


if(!$o_main->db->table_exists('sys_compare_folder'))
{
	$b_table_created = $o_main->db->query("CREATE TABLE sys_compare_folder (
		id INT NOT NULL AUTO_INCREMENT,
		status TINYINT NOT NULL DEFAULT '0',
		compare_path_left TEXT NOT NULL DEFAULT '',
		compare_path_right TEXT NOT NULL DEFAULT '',
		difference LONGTEXT NOT NULL DEFAULT '',
		PRIMARY KEY (id)
	)");
	if(!$b_table_created)
	{
		echo 'error_occured_on_account_install'.'<br>';
		return;
	}
}

if(isset($_POST['compare_account']))
{
	$s_path = realpath(__DIR__."/../../../");
	$v_item = array('path'=>$s_path, 'skip'=>array($s_path."/backup", $s_path."/uploads"));
	$s_compare_left = json_encode($v_item);
	$s_path = realpath(__DIR__."/../../../../".preg_replace('#[^A-za-z0-9_-]+#', '', $_POST['compare_account'])."/");
	if(is_dir($s_path))
	{
		$v_item = array('path'=>$s_path, 'skip'=>array($s_path."/backup", $s_path."/uploads"));
		$s_compare_right = json_encode($v_item);
		
		
		$o_query = $o_main->db->query("INSERT INTO sys_compare_folder (id, `status`, compare_path_left, compare_path_right, difference) VALUES (NULL, 0, ?, ?, '')", array($s_compare_left, $s_compare_right));
		if($o_query)
		{
			$l_sys_compare_folder_id = $o_main->db->insert_id();
			$v_param = array(
				'l_sys_compare_folder_id' => $l_sys_compare_folder_id
			);
			$s_time = date('YmdHi');
			
			$o_query = $o_main->db->query("INSERT INTO sys_cronjob (id, moduleID, createdBy, created, perform_time, script_path, `status`, parameters, log, content_id) VALUES (NULL, 0, ?, NOW(), STR_TO_DATE(?, '%Y%m%d%H%i'), 'modules/Modulemanager/output_cronjob/compare_folders.php', 0, ?, '', ?)", array($variables->loggID, $s_time, json_encode($v_param), $l_sys_compare_folder_id));
			if($o_query)
			{
				$l_cronjob_id = $o_main->db->insert_id();
				$o_query = $o_main->db->get('accountinfo');
				$v_accountinfo = $o_query ? $o_query->row_array() : array();
				$s_response = APIconnectAccount("cronjobtaskcreate", $v_accountinfo['accountname'], $v_accountinfo['password'], array('TYPE'=>'script', 'TIME'=>$s_time, 'DATA'=>array('l_cronjob_id'=>$l_cronjob_id)));
				if($s_response == 'OK')
				{
					do {
						sleep(30);
						set_time_limit(30);
						$o_query = $o_main->db->get_where('sys_compare_folder', array('id'=>$l_sys_compare_folder_id, 'status'=>1));
					} while ($o_query && $o_query->num_rows() == 0);
					
					header("Location: ".$_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&submodule=".$submodule.(isset($_GET['relationID']) ? "&relationID=".$_GET['relationID'] : '').(isset($_GET['relationfield']) ? "&relationfield=".$_GET['relationfield'] : '')."&includefile=compare_accounts&compare_id=".$l_sys_compare_folder_id);
					
				} else {
					echo $formText_ErrorOccurredProcessingRequest_Modulemanager;
				}
			}
		}
	} else {
		echo $formText_AccountNotFound_Modulemanager;
	}
}
?>
<div class="module-manager">
<h1><?php echo $formText_CompareAccounts_Modulemanager;?></h1>
<?php if(!isset($_GET['compare_id'])) { ?>
<form method="POST" action="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&submodule=".$submodule.(isset($_GET['relationID']) ? "&relationID=".$_GET['relationID'] : '').(isset($_GET['relationfield']) ? "&relationfield=".$_GET['relationfield'] : '')."&includefile=compare_accounts";?>">
<input type="text" name="compare_account" value="">
<div style="padding-top:20px;">
	<input type="submit" class="btn btn-success" name="submbtn" value="<?php echo $formText_Compare_Modulemanager;?>" onClick="$(this).attr('disabled', true);">
	<a href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&submodule=".$submodule."&folder=input&folderfile=input";?>" class="btn btn-default optimize"><?php echo $formText_GoBack_input;?></a>
</div>
</form>
<?php } else { ?>
<table class="table">
<thead>
	<tr>
		<th><?php echo $formText_Object_input;?></th>
		<th colspan="2"><?php echo $formText_Status_input;?></th>
	</tr>
</thead>
<tbody><?php
	$v_difference = array();
	$o_query = $o_main->db->get_where('sys_compare_folder', array('id'=>$_GET['compare_id'], 'status'=>1));
	if($o_query && $o_query->num_rows()>0)
	{
		$v_row = $o_query->row_array();
		$v_difference = json_decode($v_row['difference'], TRUE);
	}
	
	if(count($v_difference)>0)
	{
		$v_status_icon = array("A" => "plus", "C" => "pencil", "D" => "minus");
		$v_status_class = array("A" => "info", "C" => "warning", "D" => "danger");
		$v_status = array("A" => $formText_Added_input, "C" => $formText_Changed_input, "D" => $formText_Deleted_input);
		foreach($v_difference as $s_object => $s_status)
		{
			?><tr class="<?php echo $v_status_class[$s_status];?>">
				<td><?php echo $s_object;?></td>
				<td width="5%"><span class="glyphicon glyphicon-<?php echo $v_status_icon[$s_status];?>"></span></td>
				<td width="15%"><?php echo $v_status[$s_status];?></td>
				<?php /*?><td width="5%"><?php if($s_status=='C') { ?><a href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&submodule=".$submodule."&includefile=compare_files&comparefiles=".base64_encode($s_lib_path.$s_object.'[:]'.$s_local_path_relative.$s_object)."&returl=".base64_encode($_SERVER['PHP_SELF']."?".get_curent_GET_params());?>" class="optimize"><span class="glyphicon glyphicon-eye-open"></span></a><?php } ?></td><?php */?>
			</tr><?php
		}
	} else {
		if($v_difference === NULL)
		{
			?><tr><td colspan="3"><?php echo $formText_ErrorOccuredReadingCompareResult_Modulemanager;?></td></tr><?php
		} else {
			?><tr><td colspan="3"><?php echo $formText_NoDifferenceHasBeenFound_input;?></td></tr><?php
		}
	}
	?>
</tbody>
</table>
<?php /*?><?php } else {
	foreach($v_error_msg as $s_msg)
	{
		?><div class="alert alert-danger"><?php echo $s_msg;?></div><?php
	}
} ?><?php */?>
<div>
	<a href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&submodule=".$submodule."&folder=input&folderfile=input";?>" class="btn btn-default optimize"><?php echo $formText_GoBack_input;?></a>
</div>
<?php } ?>
</div>