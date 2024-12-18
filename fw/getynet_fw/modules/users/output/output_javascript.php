<script language="javascript" type="text/javascript">
function fw_useradmin_updateaccesslevel(companyID,extradir,module,inputlang,module,listname,listname2,selectvalue1,selectvalue2,selectvalue3){

	if($('#companyaccessID').val() == 2)
	{
		$.ajax({
			cache: false,
			dataType: 'json',
			url: extradir+'/output/getaccountlist.php?companyID='+companyID+'&extradir='+extradir+'&inputlang='+inputlang+'&module='+module+'&listname='+listname+'&listname2='+listname2+'&selectvalue1='+selectvalue1+'&selectvalue2='+selectvalue2+'&selectvalue3='+selectvalue3,
			success: function(data){
				if(data.error)
				{
					fw_info_message_add("error", data.error, true, true);
				} else {
					$('#accountaccesslistid').html(data.html);
					$(window).trigger('resize');
				}
			}
		});
	} else {
		$('#accountaccesslistid').html("");
	}
}
function fw_useradmin_updateaccountlevel(accountID,extradir,module,inputlang,module,listname,listname2,accountname,companyid)
{
	if($('#accountaccess_'+accountID+'_id').val() == 2)
	{
		fw_loading_start();
		$.ajax({
			cache: false,
			dataType: 'json',
			url: extradir +'/output/getmodulelistbyaccount.php?accountID='+accountID+'&accountname='+accountname+'&extradir='+extradir+'&inputlang='+inputlang+'&module='+module+'&listname='+listname+'&listname2='+listname2+'&companyID='+companyid,
			success: function(data){
				if(data.error)
				{
					$('#accountmodulesaccesslistid_'+data.accountID).html(data.error);
				} else {
					$('#accountmodulesaccesslistid_'+data.accountID).html(data.html);
				}
				$(window).trigger('resize');
				fw_loading_end();
			}
		}).fail(function(){
			fw_loading_end();
		});
	} else {
		$('#accountmodulesaccesslistid_'+accountID).html("");
	}
}
function fw_useradmin_updateaccountmodules(extradir,module,inputlang,module,listname,listname2)
{
	if($('#group_accountaccess_id').val() == 2)
	{
		var tmp = $('#group_account_id').val().split(':');
		$.ajax({
			cache: false,
			dataType: 'json',
			url: extradir +'/output/getmodulelistbyaccount.php?accountID='+tmp[0]+'&accountname='+tmp[1]+'&extradir='+extradir+'&inputlang='+inputlang+'&module='+module+'&listname='+listname+'&listname2='+listname2,
			success: function(data){
				if(data.error)
				{
					$('#accountmodulesaccesslistid').html(data.error);
				} else {
					$('#accountmodulesaccesslistid').html(data.html);
				}
				$(window).trigger('resize');
			}
		});
	} else {
		$('#accountmodulesaccesslistid').html("");
	}
}
function fw_useradmin_deleteuserconfirmbtn(deletemessage,user)
{
	bootbox.confirm({
		message:deletemessage+" "+user+"?",
		buttons:{confirm:{label:"<?php echo $formText_Yes_usersOutput;?>"},cancel:{label:"<?php echo $formText_No_usersOutput;?>"}},
		callback: function(result){
			if(result)
			{
				$("#formsendtypeid").val(2);
				$("#userupdateformid").submit();
			}
		}
	});
	return false;
}
function fw_useradmin_deleteuserconfirmlink(userID,name)
{
	bootbox.confirm({
		message:"<?php echo $formText_confirmDeleteMessage_usersOutput;?> "+name+"?",
		buttons:{confirm:{label:"<?php echo $formText_Yes_usersOutput;?>"},cancel:{label:"<?php echo $formText_No_usersOutput;?>"}},
		callback: function(result){
			if(result)
			{
				$("#fw_useradmin_deleteusermark_"+userID).val(1);
				$("#fw_useradmin_deleteuser_"+userID).submit();
			}
		}
	});
	return false;
}
function fw_useradmin_deletegroupconfirm(groupID,name)
{
	bootbox.confirm({
		message:"<?php echo $formText_confirmDeleteMessageGroup_usersOutput;?> "+name+"?",
		buttons:{confirm:{label:"<?php echo $formText_Yes_usersOutput;?>"},cancel:{label:"<?php echo $formText_No_usersOutput;?>"}},
		callback: function(result){
			if(result)
			{
				$("#fw_useradmin_deletegroupmark_"+groupID).val(1);
				$("#fw_useradmin_deletegroup_"+groupID).submit();
			}
		}
	});
	return false;
}
function fw_useradmin_deletechannelconfirm(channel_id, name)
{
	bootbox.confirm({
		message:"<?php echo $formText_AreYouSureYouWantToDeleteItem_Framework;?> "+name+"?",
		buttons:{confirm:{label:"<?php echo $formText_Yes_usersOutput;?>"},cancel:{label:"<?php echo $formText_No_usersOutput;?>"}},
		callback: function(result){
			if(result)
			{
				$("#fw_useradmin_deletechannelmark_"+channel_id).val(1);
				$("#fw_useradmin_deletechannel_"+channel_id).submit();
			}
		}
	});
	return false;
}
function fw_useradmin_update_extended(_this, companyID, extradir)
{
	if(_this.value == 1)
	{
		fw_loading_start();
		$.ajax({
			cache: false,
			dataType: 'json',
			url: extradir+'/output/get_extended_access.php?companyID='+companyID,
			success: function(data){
				if(data.error)
				{
					fw_info_message_add('error', data.error, true, true);
				} else {
					$('#contentaccess_extended').html(data.html);
					$(window).trigger('resize');
				}
				fw_loading_end();
			}
		}).fail(function(){fw_loading_end();});
	} else {
		$('#contentaccess_extended').html('');
	}
}


function restricted_content_change(_this)
{
	var $_parent = $(_this).parent();
	if(_this.checked)
	{
		$_parent.find('.content_access').removeClass('hide');
	} else {
		$_parent.find('.content_access').addClass('hide').find('input[type=checkbox]').not("[disabled]").removeProp('checked');
	}
}
function readAll(_class)
{
	var i = 0;
	$("input."+_class+".1").each(function (index, value) {
		if(!$(this).is(':checked')) i++;
	});

	if(i==0) $("input."+_class).removeProp('checked').nextAll('input.4').addClass('hide').nextAll('label.l4').addClass('hide');
	else $("input."+_class+".1").prop('checked',true);
}
function writeAll(_class)
{
	var i = 0;
	$("input."+_class+".2").each(function (index, value) {
		if(!$(this).is(':checked')) i++;
	});

	if(i==0) {
		$("input."+_class+".3").removeProp('checked').nextAll('input.4').removeProp('checked').addClass('hide').nextAll('label.l4').addClass('hide');
		$("input."+_class+".2").removeProp('checked');
	} else {
		$("input."+_class+".2").prop('checked',true).nextAll('input.4').removeClass('hide').nextAll('label.l4').removeClass('hide');
		$("input."+_class+".1").prop('checked',true);
	}
}
function deleteAll(_class)
{
	var i = 0;
	$("input."+_class+".3").each(function (index, value) {
		if(!$(this).is(':checked')) i++;
	});

	if(i==0) {
		$("input."+_class+".3").removeProp('checked');
	} else {
		$("input."+_class+":not(.4)").prop('checked',true).nextAll('input.4').removeClass('hide').nextAll('label.l4').removeClass('hide');
	}
}
function ownerAll(_class)
{
	var i = 0;
	$("input."+_class+".4").each(function (index, value) {
		if($(this).is('.hide')) return;
		if(!$(this).is(':checked')) i++;
	});

	if(i==0) {
		$("input."+_class+".4").removeProp('checked');
	} else {
		$("input."+_class+".4").prop('checked',true);
	}
}
function changeModuleAccess(_this)
{
	if($(_this).is('.1') && !$(_this).is(':checked')) {
		$(_this).nextAll('input').removeProp('checked');
		$(_this).nextAll('input.4').removeProp('checked').addClass('hide').nextAll('label.l4').addClass('hide');
	}
	if($(_this).is('.2')) {
		if($(_this).is(':checked')) {
			$(_this).prevAll('input').prop('checked',true);
			$(_this).nextAll('input.4').removeClass('hide').nextAll('label.l4').removeClass('hide');
		} else {
			$(_this).nextAll('input').removeProp('checked');
			$(_this).nextAll('input.4').removeProp('checked').addClass('hide').nextAll('label.l4').addClass('hide');
		}
	}
	if($(_this).is('.3') && $(_this).is(':checked')) {
		$(_this).prevAll('input').prop('checked',true);
		$(_this).nextAll('input.4').removeClass('hide').nextAll('label.l4').removeClass('hide');
	}
}
function accessElementAll(_class)
{
	var i = 0;
	$("input."+_class).each(function (index, value) {
		if(!$(this).is(':checked')) i++;
	});

	if(i==0) {
		$("input."+_class).removeProp('checked');
	} else {
		$("input."+_class).prop('checked',true);
	}
}
</script>
