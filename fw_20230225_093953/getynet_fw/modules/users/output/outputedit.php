<div class="module_customized"><?php
$includeFile = __DIR__."/../../../languages/default.php";
if(is_file($includeFile)) include($includeFile);
$includeFile = __DIR__."/../../../languages/".$variables->languageID.".php";
if(is_file($includeFile)) include($includeFile);
$include_file = __DIR__."/../../../includes/include.developeraccess.php";
if(is_file($include_file)) include($include_file);
$includeFile = __DIR__."/output_javascript.php";
if(is_file($includeFile)) include($includeFile);

if(!function_exists("APIconnectorAccount")) include(__DIR__."/../../../includes/APIconnector.php");
$o_query = $o_main->db->query('SELECT * FROM accountinfo');
$v_accountinfo = $o_query->row_array();

if(isset($_GET['error']))
{
	$class = "error";
	$print = addslashes('<div class="item ui-corner-all '.$class.'">'.urldecode($_GET['error']).'</div>');
	if(isset($ob_javascript))
	{
		$ob_javascript .= ' $(function(){$(".fw_info_messages").html("'.$print.'").slideDown();});';
	} else {
		?><script type="text/javascript" language="javascript"><?php echo '$(function(){$(".fw_info_messages").html("'.$print.'").slideDown();});';?></script><?php
	}
	unset($_GET['error']);
}
$userAccess = array('id'=>'', 'username'=>'', 'id'=>'', 'accessID'=>'', 'users_name'=>'', 'first_name'=>'', 'middle_name'=>'', 'last_name'=>'', 'mobile'=>'', 'mobile_prefix'=>'', 'admin'=>'', 'developeraccess'=>'', 'invitationsent'=>'', 'accesslevel'=>'', 'deactivated'=>'');
if(isset($_GET['username']))
{
	$data = json_decode(APIconnectorUser("companyaccessget", $_COOKIE['username'], $_COOKIE['sessionID'], array('ACCESSID'=>$_GET['accessID'])),true);
	if(isset($data['data'])) $userAccess = $data['data'];
	$edit = 2;
} else {
	$edit = 1;
}
?>
<div class="panel panel-default">
	<div class="panel-heading">
  		<h3 class="panel-title"><?php echo ($edit==1?$formText_addUser_usersOutputLink:$formText_editUser_usersOutputLink);?></h3>
	</div>
	<div class="panel-body">
		<form id="userupdateformid" name="upadate" action="<?php echo $extradir;?>/output/outputreg.php" method="POST">
		<input type="hidden" name="pageID" value="<?php echo $_GET['pageID'];?>">
		<input type="hidden" name="accountname" value="<?php echo $_GET['accountname'];?>">
		<input type="hidden" name="companyID" value="<?php echo $_GET['companyID'];?>">
		<input type="hidden" name="caID" value="<?php echo (!$variables->fw_url_share?$_GET['caID']:$_COOKIE[$_GET['accountname'].'_caID']);?>">
		<input type="hidden" name="userID" value="<?php echo $userAccess['id']; ?>" />
		<input type="hidden" name="username" value="<?php echo $userAccess['username']; ?>" />
		<input type="hidden" name="accessID" value="<?php echo (isset($_GET['accessID']) ? $_GET['accessID'] : ''); ?>" />
		<input type="hidden" name="editedBy" value="<?php echo $variables->loggID; ?>" />
		<input type="hidden" name="languageID" value="<?php echo $variables->languageID; ?>" />
		<input type="hidden" name="defaultLanguageID" value="<?php echo $variables->defaultLanguageID;?>" />
		<input type="hidden" name="extradir" value="<?php echo $extradir; ?>" />
		<input type="hidden" name="module" value="<?php echo $module; ?>" />
		<input type="hidden" name="edituser" value="<?php echo $edit; ?>" />
		<input type="hidden" name="formsendtype" value="0" id="formsendtypeid">
		<input type="hidden" name="fw_domain_url" value="<?php echo $fw_domain_url."&module=".$_GET['module']."&folder=output&modulename=users&getynetaccount=1";?>">
		<div class="profile">
			<table border="0" cellpadding="0" cellspacing="0" rules="none" frame="void" width="100%">
				<tr>
					<td width="120"><?php echo $formText_FirstName_usersOutputLink;?></td>
					<td>
						<input type="text" name="first_name" value="<?php echo $userAccess['first_name']; ?>"/>
					</td>
				</tr>
				<tr>
					<td width="120"><?php echo $formText_MiddleName_usersOutputLink;?></td>
					<td>
						<input type="text" name="middle_name" value="<?php echo $userAccess['middle_name']; ?>"/>
					</td>
				</tr>
				<tr>
					<td width="120"><?php echo $formText_LastName_usersOutputLink;?></td>
					<td>
						<input type="text" name="last_name" value="<?php echo $userAccess['last_name']; ?>"/>
					</td>
				</tr>
				<tr><td><?php echo $formText_userlistEmail_usersOutputLink;?></td><td><?php if($userAccess['username'] != ''){ print $userAccess['username']; ?><input type="hidden" name="username" value="<?php echo $userAccess['username']; ?>" /><?php }else{ ?><input type="text" name="username" value="<?php echo $userAccess['username']; ?>" /><?php } ?> </td></tr>
				<tr><td><?php echo $formText_MobilePrefix_usersOutputLink;?></td><td><input type="text" name="mobile_prefix" value="<?php echo $userAccess['mobile_prefix'];?>" /></td></tr>
				<tr><td><?php echo $formText_userlistMobile_usersOutputLink;?></td><td><input type="text" name="mobile" value="<?php echo $userAccess['mobile'];?>" /></td></tr>
				<tr><td><?php echo $formText_userlistAdmin_usersOutputLink;?></td><td align="left"><input type="checkbox" name="admin" value="1"<?php echo ($userAccess['admin']=='1'?' checked="checked"':'');?> style="width:auto;"></td></tr>
				<?php /*<tr><td><?php echo $formText_SystemAdmin_usersOutputLink;?></td><td align="left"><?php
					if($variables->fw_session['system_admin'] == '1')
					{
						?><input type="checkbox" name="system_admin" value="1"<?php echo ($userAccess['system_admin']=='1'?' checked="checked"':'');?> style="width:auto;"><?php
					} else {
						?><input type="checkbox" <?php echo ($userAccess['system_admin']=='1'?' checked="checked"':'');?> disabled style="width:auto;">
						<input type="hidden" name="system_admin" value="<?php echo ($userAccess['system_admin']=='1'?1:0);?>"><?php
					}
				?></td></tr>*/?>
				<?php
				if($variables->fw_session["developeraccessoriginal"]>0)
				{
					?><tr><td><?php echo $formText_DeveloperAccess_users;?></td><td align="left">
					<select name="developeraccess"><?php
					foreach($developeraccesslevels as $key => $item)
					{
						if($key > $variables->fw_session["developeraccessoriginal"]) break;
						?><option value="<?php echo $key;?>" <?php echo ($userAccess['developeraccess']==$key?' selected':'');?>><?php echo $item;?></option><?php
					}
					?></select></td></tr><?php
				} else {
					?><input type="hidden" name="developeraccess" value="0" /><?php
				}
				$o_query = $o_main->db->query('SELECT id, name FROM sys_modulemenuset WHERE default_set <> 1');
				if($o_query && $o_query->num_rows()>0)
				{
					$l_sel_id = '';
					$o_check = $o_main->db->query('SELECT s.id FROM sys_modulemenuset s JOIN sys_modulemenuusers u ON u.set_id = s.id WHERE u.username = ?', array($userAccess['username']));
					if($o_check && $o_row = $o_check->row()) $l_sel_id = $o_row->id;
					?><tr><td><?php echo $formText_ModuleMenu_users;?></td><td align="left">
					<select name="modulemenuset">
					<option><?php echo $formText_None_users;?></option><?php
					foreach($o_query->result() as $o_row)
					{
						?><option value="<?php echo $o_row->id;?>" <?php echo ($l_sel_id==$o_row->id?' selected':'');?>><?php echo $o_row->name;?></option><?php
					}
					?></select></td></tr><?php
				}
				?>
				<tr><td><?php echo $formText_userlistDeactivated_usersOutputLink;?></td><td align="left"><input id="user_deactivated" type="date" name="deactivated" value="<?php echo $userAccess['deactivated'];?>"></td></tr>
				<?php if($userAccess['invitationsent'] != ''){ ?><tr><td><?php echo $formText_invitationsent_usersOutputLink;?></td><td><?php echo $userAccess['invitationsent']; ?></td></tr><?php } ?>


				<!-- REGULAR ACCESS -->
				<tr><td><?php echo $formText_userlistAccesslevel_usersOutputLink;?></td><td align="left"><select autocomplete="off" required name="companyaccess" id="companyaccessID" onChange="javascript:fw_useradmin_updateaccesslevel('<?php echo $companyID;?>','<?php echo $extradir;?>','<?php echo $module;?>','<?php echo $variables->languageID;?>','<?php echo $module;?>','<?php echo $formText_modulenamelistaccess_usersOutputLink;?>','<?php echo $formText_moduleaccessheader_usersOutputLink;?>','<?php echo $formText_moduleLevelAll_usersOutputLink;?>','<?php echo $formText_moduleLevelRestricted_usersOutputLink;?>','<?php echo $formText_moduleLevelDenied_usersOutputLink;?>');">
				<?php
				/*$data = json_decode(APIconnectorUser("groupcompanyaccessbycompanyidget", $_COOKIE['username'], $_COOKIE['sessionID'], array('COMPANY_ID'=>$companyID)),true);
				$groupAccess = $data['data'];
				foreach($groupAccess as $groupItem)
				{
					?><option value="3_<?=$groupItem['id'];?>"<?=($userAccess['accesslevel']==3 && $userAccess['groupID']==$groupItem['id']?' selected="selected"':'');?>><?=$formText_accessLevelGroups_usersOutputLink." - ".$groupItem['groupname'];?></option><?php
				}*/?>

				<option value="" <?php if($userAccess['accesslevel'] == "") echo 'selected';?>><?php echo $formText_SelectAccess_usersOutputLink;?></option>
				<?php
				$data = json_decode(APIconnectorUser("groupcompanyaccessbycompanyidget", $_COOKIE['username'], $_COOKIE['sessionID'], array('COMPANY_ID'=>$companyID)),true);
				$groupAccess = $data['data'];
				foreach($groupAccess as $groupItem)
				{
					?><option value="3_<?php echo $groupItem['id'];?>"<?php echo ($userAccess['accesslevel']==3 && $userAccess['groupID']==$groupItem['id']?' selected="selected"':'');?>><?php echo $formText_accessLevelGroups_usersOutputLink." - ".$groupItem['groupname'];?></option><?php
				}
				?>
				<option value="1"<?php echo (($userAccess['accesslevel'] != "" && $userAccess['accesslevel']==1)?' selected="selected"':'');?>><?php echo $formText_accessLevelAll_usersOutputLink;?></option>
				<option value="2"<?php echo (($userAccess['accesslevel'] != "" && $userAccess['accesslevel']==2)?' selected="selected"':'');?>><?php echo $formText_accessLevelRestricted_usersOutputLink;?></option>
				<option value="0"<?php echo (($userAccess['accesslevel'] != "" && $userAccess['accesslevel']==0)?' selected="selected"':'');?>><?php echo $formText_accessLevelNoAccess_usersOutputLink;?></option>
				</select></td></tr>
				<tr><td colspan="2" style="padding-bottom:5px;">
				<div id="accountaccesslistid" style="width:100%;">
				<?php


				if($userAccess['id'] != '' && $userAccess['accesslevel'] == 2)
				{
					include("accountaccesslist.php");
				}
				?></div></td></tr>




				<!-- EXTENED ACCESS -->
				<?php
				/*
				$b_extended = FALSE;
				$v_extended_access = array();
				$s_response = APIconnectorUser("contentaccess_extended_get", $_COOKIE['username'], $_COOKIE['sessionID'], array('companyaccess_id'=>$_GET['accessID']));
				$v_response = json_decode($s_response, TRUE);
				if($v_response['status'] == 1)
				{
					$b_extended = TRUE;
					$v_extended_access = $v_response['access'];
				}
				?>
				<tr><td><?php echo $formText_ExtendedAccess;?></td><td align="left"><select onChange="javascript:fw_useradmin_update_extended(this, '<?php echo $companyID;?>', '<?php echo $extradir;?>');">
				<option value="1"<?php echo ($b_extended?' selected="selected"':'');?>><?php echo $formText_accessLevelRestricted_usersOutputLink;?></option>
				<option value="0"<?php echo (!$b_extended?' selected="selected"':'');?>><?php echo $formText_accessLevelNoAccess_usersOutputLink;?></option>
				</select></td></tr>
				<tr><td colspan="2" style="padding-bottom:5px;">
				<div id="contentaccess_extended" style="width:100%;">
					<?php if($b_extended) include(__DIR__."/get_extended_access.php"); ?>
				</div>
				</td></tr>*/
				?>
				<tr><td colspan="2">
					<a class="btn btn-sm btn-success script" id="test-this" href="javascript:$(this).attr('disabled',true);  document.getElementById('userupdateformid').submit();"><?php echo $formText_saveButton_usersOutputEdit;?></a>
					<a class="btn btn-sm btn-success script" href="javascript:;" onClick="if(!$(this).is('.clicked')) { $(this).addClass('clicked'); $('#formsendtypeid').val(1); $('#userupdateformid').submit(); }"><?php echo $formText_saveAndSend_usersOutputLink;?></a>
					<?php if($edit==2){ ?><a class="btn btn-sm btn-danger script" href="#" onClick="javascript:return fw_useradmin_deleteuserconfirmbtn('<?php echo $formText_confirmDeleteMessage_usersOutput; ?>','<?php if($userAccess['users_name'] == ''){print $userAccess['fullname'];}else{print $userAccess['users_name']; }?>'); "><?php echo $formText_deleteUser_usersOutputLink;?></a><?php } ?>
					<a class="btn btn-sm btn-default optimize" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID'].(!$variables->fw_url_share?'&caID='.$_GET['caID']:'')."&module=".$_GET['module']."&getynetaccount=1&folder=output&folderfile=output&modulename=users";?>"><?php echo $formText_Cancel_Framework;?></a>
				</td>
				</tr>
			</table>
		</div>
		</form>
	</div>
</div>


<script type="text/javascript">
<?php if(isset($ob_javascript)) { ob_start(); } ?>
$(function() {
	$('#user_deactivated').datepicker({
		dateFormat: "yy-mm-dd"
	});
	//$('#test-this').observe('click', handler);
});
var ListenerTracker=new function(){
    var is_active=false;
    // listener tracking datas
    var _elements_  =[];
    var _listeners_ =[];
    this.init=function(){
        if(!is_active){//avoid duplicate call
            intercep_events_listeners();
        }
        is_active=true;
    };
    // register individual element an returns its corresponding listeners
    var register_element=function(element){
        if(_elements_.indexOf(element)==-1){
            // NB : split by useCapture to make listener easier to find when removing
            var elt_listeners=[{/*useCapture=false*/},{/*useCapture=true*/}];
            _elements_.push(element);
            _listeners_.push(elt_listeners);
        }
        return _listeners_[_elements_.indexOf(element)];
    };
    var intercep_events_listeners = function(){
        // backup overrided methods
        var _super_={
            "addEventListener"      : HTMLElement.prototype.addEventListener,
            "removeEventListener"   : HTMLElement.prototype.removeEventListener
        };

        Element.prototype["addEventListener"]=function(type, listener, useCapture){
            var listeners=register_element(this);
            // add event before to avoid registering if an error is thrown
            _super_["addEventListener"].apply(this,arguments);
            // adapt to 'elt_listeners' index
            useCapture=useCapture?1:0;

            if(!listeners[useCapture][type])listeners[useCapture][type]=[];
            listeners[useCapture][type].push(listener);
        };
        Element.prototype["removeEventListener"]=function(type, listener, useCapture){
            var listeners=register_element(this);
            // add event before to avoid registering if an error is thrown
            _super_["removeEventListener"].apply(this,arguments);
            // adapt to 'elt_listeners' index
            useCapture=useCapture?1:0;
            if(!listeners[useCapture][type])return;
            var lid = listeners[useCapture][type].indexOf(listener);
            if(lid>-1)listeners[useCapture][type].splice(lid,1);
        };
        Element.prototype["getEventListeners"]=function(type){
            var listeners=register_element(this);
            // convert to listener datas list
            var result=[];
            for(var useCapture=0,list;list=listeners[useCapture];useCapture++){
                if(typeof(type)=="string"){// filtered by type
                    if(list[type]){
                        for(var id in list[type]){
                            result.push({"type":type,"listener":list[type][id],"useCapture":!!useCapture});
                        }
                    }
                }else{// all
                    for(var _type in list){
                        for(var id in list[_type]){
                            result.push({"type":_type,"listener":list[_type][id],"useCapture":!!useCapture});
                        }
                    }
                }
            }
            return result;
        };
    };
}();
ListenerTracker.init();
<?php if(isset($ob_javascript)) { $ob_javascript .= " ".ob_get_clean(); } ?>
</script>
</div>
