<?php
include($extradir."/input/settings/relations/$buttonbase.php");
$relationField = "";
foreach($prerelations as $prep)
{
	$spPrep = explode("¤",$prep);
	if($spPrep[2] == $buttonRelationModule)
	{
		$relationField = $spPrep[3];
		$relationModule = $spPrep[2];
	}
}
if($ID > 0 && $buttonlisttype == "inputform")
{
	$edit_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&".(isset($_GET['moduleID'])?"moduleID=".$_GET['moduleID']."&":'')."module=".$buttonSubmodule."&includefile=../buttontypes/FixOfferLinks/import&submodule=".$relationModule."&relationID=".$ID."&relationfield=".$relationField.($include_sublist?"&subcontent=1":"").($buttonContentStatus!=""?"&content_status=".$buttonContentStatus:"");
} else {
	$edit_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&".(isset($_GET['moduleID'])?"moduleID=".$_GET['moduleID']."&":'')."module=".$buttonSubmodule."&includefile=../buttontypes/FixOfferLinks/import&submodule=".$submodule.(isset($_GET['relationID'])?"&relationID=".$_GET['relationID']."&relationfield=".$_GET['relationfield']:"").($include_sublist?"&subcontent=1":"").($buttonContentStatus!=""?"&content_status=".$buttonContentStatus:"");
}
$ui_id_counter++;
$button_ui_id = $buttonSubmodule."_".$ui_editform_id."_".$ui_id_counter;
if(isset($include_sublist) || isset($_GET['subcontent']))
{
	?>
	<script type="text/javascript" language="javascript">
	<?php if(isset($ob_javascript)) { ob_start(); } ?>
	$(function(){
		$("#<?php echo $button_ui_id;?>").unbind("click").on("click", function(e) {
			e.preventDefault();
			$(this).closest('.header').find('.content-toggler:not(.open)').trigger('click');
			var oDiv = $('<div/>').uniqueId().attr('class','subcontent-item');
			var oLink = $('<a/>').attr({'href':$(this).attr('href'),'class':'optimize','data-target':'#'+oDiv.attr('id')}).appendTo(oDiv);
			$(this).closest(".subcontent").find(".subcontent-new").prepend(oDiv);
			fw_optimize_urls();
			oLink.trigger("click");
			return false;
		});
	});
	<?php if(isset($ob_javascript)) { $ob_javascript .= " ".ob_get_clean(); } ?>
	</script>
	<?php
}
?><a href="<?php echo $edit_link;?>" id="<?php echo $button_ui_id;?>" class="optimize" role="menuitem"><?php echo $buttonsArray[1];?></a>
