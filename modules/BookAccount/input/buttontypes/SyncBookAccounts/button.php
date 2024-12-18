<?php
$s_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$buttonSubmodule."&includefile=edit&submodule=".$buttonRelationModule."&relationID=".$l_id.($include_sublist?"&subcontent=1":"")."&includefile=buttoninclude&buttontype=SyncBookAccounts&executefile=sync";
?>

<a href="<?php echo $s_link; ?>" id="<?php echo $button_ui_id;?>" class="optimize" role="menuitem">
    <?php echo $formText_SyncBookAccounts; ?>
</a>
