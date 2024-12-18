<?php
$s_include_file = __DIR__."/../languages/default.php";
if(is_file($s_include_file)) include($s_include_file);
$s_include_file = __DIR__."/../languages/".$variables->languageID.".php";
if(is_file($s_include_file)) include($s_include_file);
?>
<div id="fw_account_upgrade_line"<?php echo ($b_fw_account_upgrade ? '':' style="display:none;"');?>>
	<div class="msg_line" style="border-top:1px solid #aaaaaa; border-bottom:1px solid #aaaaaa; padding:0 30px; background-color:#FF9; text-align:center;"><h3 style="margin-top:10px;"><?php echo $formText_AccountUpgradeWillBePerformedOn_Framework.": ".(isset($s_fw_account_upgrade_time) ? $s_fw_account_upgrade_time : '');?></h3></div>
</div>