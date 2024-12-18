<?php
if($access >= 10)
{
	$l_rand_num = rand(1,999999);
	if(is_file(__DIR__."/../../addOn_include/buttons.php")) include(__DIR__."/../../addOn_include/buttons.php");
	
	$buttonbase = $submodule;
	if($buttonbase == "")
	{
		$buttonbase = $headmodule;
	}
	
	$buttonlisttype = '';
	/*if($includefile == 'edit')
		$buttonlisttype = 'inputform';
	else if(($fw_showCustomized && (!isset($includefile) || $includefile == 'list')) || $fw_showList || $includefile == 'showmodulebuttons')
		$buttonlisttype = 'list';
	else if(isset($_GET["buttontype"]))
		$buttonlisttype = $_GET["buttontype"];*/
	if($includefile == 'edit')
		$buttonlisttype = 'inputform';
	else if(($fw_showCustomized && (!isset($includefile) || $includefile == 'list')) || $fw_showList || $includefile == 'showmodulebuttons')
		$buttonlisttype = 'list';
	else if(isset($_GET["buttontype"]))
		$buttonlisttype = $_GET["buttontype"];
	
	$buttonsArray = $buttonlist = array();
	if(is_file(__DIR__."/../settings/buttonconfig/$buttonbase".$buttonlisttype.".php"))
	{
		include(__DIR__."/../settings/buttonconfig/$buttonbase".$buttonlisttype.".php");
		$mainArray = explode("¤",$prebuttonconfig);
		//print_r($mainArray);
		foreach($mainArray as $ckArray)
		{
			$formArray = explode(":",$ckArray);
			
			if(sizeof($formArray) > 2)
			{
				$buttonlist[] = $formArray;
			}
		}
		//print_r($buttonlist);
		foreach($buttonlist as $buttonsArray)
		{
			$buttonSubmodule = $buttonsArray[6];
			$buttonModule = $buttonsArray[0];
			$buttonInclude = $buttonsArray[2];
			$buttonRelationModule = $buttonsArray[3];
			$buttonMode = $buttonsArray[4];
			if($buttonsArray[5] == 1 && $buttonsArray[6] == $module)
				$buttonMode = 1;
			$buttonContentStatus = $buttonsArray[7];
			
			if($buttonInclude == "Invoicecreate" ||
				($buttonMode == 1 && isset($_GET['relationID'])) ||
				($buttonMode == 0  && 
					(($buttonlisttype == 'inputform' && isset($_GET['ID'])) || $buttonlisttype == 'list')
				))
			{
				ob_start();
				include(__DIR__."/../buttontypes/$buttonInclude/button.php");
				$ob_button = trim(ob_get_clean());
				if($ob_button != "") print "<li>".$ob_button."</li>";
			}
		}
	}
	
	
	if(($fw_showCustomized && !isset($_GET['ID'])) || $buttonlisttype == "list")
	{
		if($variables->developeraccess >= 10) //Designer access
		{
			?><li><a class="optimize" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&actionType=changeInputLanguage";?>"><?php echo $formText_editInputLanguage_inputButton;?></a></li><?php
		}
		if($variables->developeraccess >= 10) //Designer access
		{
			if(!function_exists('count_empty_language_variables')) include_once(__DIR__.'/fn_count_empty_language_variables.php');
			?><li><a class="optimize" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&actionType=changeOutputLanguage";?>"><?php echo $formText_editOutputLanguage_inputButton." (".count_empty_language_variables().")";?></a></li><?php
		}
		if($variables->developeraccess >= 20) //Developer access
		{
			?><li><a class="optimize" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&submodule=".$submodule."&includefile=chooseToUpdate";?>"><?php echo $formText_EditTableSettings_input;?></a></li><?php
		}
		if($variables->developeraccess >= 20) //Developer access
		{
			?><li><a class="optimize" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&submodule=".$submodule."&includefile=edit_content_access";?>"><?php echo $formText_EditContentAccess_input;?></a></li><?php
		}
		if($variables->developeraccess >= 20) //Developer access
		{
			?><li><a class="optimize" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&submodule=".$submodule."&includefile=edit_helppage";?>"><?php echo $formText_EditHelppage_input;?></a></li><?php
		}
	}
	if($buttonlisttype == "inputform" && isset($_GET['ID']))
	{
		if($showSendMail == 1 and $access >= 10)
		{
			?><li><a href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&ID=".$ID."&includefile=sendEmail&submodule=".$submodule.(isset($_GET['relationID']) ? "&relationID=".$_GET['relationID']."&parentID=".$_GET['relationID'] : "").(isset($_GET['relationfield']) ? "&relationfield=".$_GET['relationfield'] : "");?>" class="optimize"><?php echo $formText_sendEmail_list;?></a></li><?php
		}
		
		if($showSendSms == 1 and $access >= 10)
		{
			?><li><a href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&ID=".$ID."&includefile=sendSms&submodule=".$submodule.(isset($_GET['relationID']) ? "&relationID=".$_GET['relationID']."&parentID=".$_GET['relationID'] : "").(isset($_GET['relationfield']) ? "&relationfield=".$_GET['relationfield'] : "");?>" class="optimize"><?php echo $formText_sendSms_list;?></a></li><?php
		}
		
		if($showSendPdf == 1 and $access >= 10)
		{
			?><li><a href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&ID=".$ID."&includefile=sendPdf&submodule=".$submodule.(isset($_GET['relationID']) ? "&relationID=".$_GET['relationID']."&parentID=".$_GET['relationID'] : "").(isset($_GET['relationfield']) ? "&relationfield=".$_GET['relationfield'] : "");?>" class="optimize"><?php echo $formText_sendPdf_list;?></a></li><?php
		}
		
		if($orderManualOrByField == '0' and $access >= 10 && !$jumpfirstpage)
		{
            if(is_array($writeContent) && array_key_exists('menulevel',$writeContent)) 
                $extratext = "&menulevel=".$writeContent['menulevel'] ;
            else
               $extratext = "";
			?><li><a href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&ID=".$ID."&includefile=orderContent&submodule=".$submodule.$extratext.(isset($_GET['relationID']) ? "&relationID=".$_GET['relationID']."&relationfield=".$_GET['relationfield']."&list=1" : "")."&_=".$l_rand_num;?>" class="optimize"><?php echo $formText_order_list;?></a></li><?php
		} 
		
		if($listButtonContentSettings == 1 and $access >= 10)
		{
			?><li><a href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&ID=".$ID."&includefile=editContentSettings&submodule=".$submodule;?>" class="optimize"><?php echo $formText_contentsettings_list;?></a></li><?php
		}
		
		if($showShowpagebutton == 1)
		{
			$v_pageID = array();
			$o_query = $o_main->db->query('SELECT p.id, pc.urlrewrite FROM pageID p LEFT OUTER JOIN pageIDcontent pc ON pc.pageIDID = p.id AND pc.languageID = ? WHERE p.contentID = ? AND p.contentTable = ?', array($s_default_output_language, $ID, $submodule));
			if($o_query) $v_pageID = $o_query->row_array();
			?><li><a href="<?php echo (isset($languagedir) ? $languagedir : "../").((isset($v_pageID['urlrewrite']) and $v_pageID['urlrewrite'] != "") ? $v_pageID['urlrewrite'] : "index.php?pageID=".$v_pageID['id']);?>" target="_blank"><?php echo $formText_showPage_list;?></a></li><?php
		}
	
		
		if($listButtonDelete == 1 and $access >= 100 && !$jumpfirstpage)
		{
			?><li><a class="delete-content-confirm-btn" href="<?php echo $extradir."/input/delete.php?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&deleteID=".$ID."&deletemodule=".$submodule."&submodule=".$submodule."&choosenListInputLang=".$choosenListInputLang.(isset($_GET['relationID'])?"&relationID=".$_GET['relationID']."&parentID=".$_GET['relationID']:"").(isset($_GET['relationfield'])?"&relationfield=".$_GET['relationfield']:"")."&_=".$l_rand_num;?>"><?php echo $formText_delete_list;?></a></li><?php
		}
	}
}
?>
<script type="text/javascript" language="javascript">
<?php if(isset($ob_javascript)) { ob_start(); } ?>
$("a.delete-content-confirm-btn").on("click", function(e){
	e.preventDefault();
	if(!fw_changes_made && !fw_click_instance)
	{
		fw_click_instance = true;
		var $_this = $(this);
		var s_msg_sufix = "";
		if($(this).data("name")) s_msg_sufix = ": " + $(this).data("name");
		bootbox.confirm({
			message:"<?php echo $formText_DeleteItem_input;?>" + s_msg_sufix + "?",
			buttons:{confirm:{label:"<?php echo $formText_Yes_input;?>"},cancel:{label:"<?php echo $formText_No_input;?>"}},
			callback: function(result){
				if(result)
				{
					fw_loading_start();
					$.ajax({
						url: $_this.attr("href"),
						cache: false,
						type: "GET",
						dataType: "json",
						success: function (data) {
							if(data.error !== undefined)
							{
								$.each(data.error, function(index, value){
									var _type = Array("error");
									if(index.length > 0 && index.indexOf("_") > 0) _type = index.explode("_");
									fw_info_message_add(_type[0], value);
								});
								fw_info_message_show();
								fw_loading_end();
								fw_click_instance = false;
							} else {
								window.location = data.url;
							}
						}
					}).fail(function() {
						fw_info_message_add("error", "<?php echo $formText_ErrorOccuredDeletingContent_input;?>", true);
						fw_loading_end();
						fw_click_instance = false;
					});
				} else {
					fw_click_instance = false;
				}
			}
		});
	}
});
<?php if(isset($ob_javascript)) { $ob_javascript .= " ".ob_get_clean(); } ?>
</script>