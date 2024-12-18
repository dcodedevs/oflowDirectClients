<?php
$accountinfo = array();
$o_query = $o_main->db->query('SELECT * FROM accountinfo');
if($o_query && $o_query->num_rows()>0) $accountinfo = $o_query->row_array();
$v_sms_service_config = array();
$o_query = $o_main->db->query('SELECT * FROM sys_smsserviceconfig ORDER BY default_config desc');
if($o_query && $o_query->num_rows()>0) $v_sms_service_config = $o_query->row_array();
$v_email_server_config = array();
$o_query = $o_main->db->query('SELECT * FROM sys_emailserverconfig ORDER BY default_server desc');
if($o_query && $o_query->num_rows()>0) $v_email_server_config = $o_query->row_array();

if(strlen($variables->loggID)==0)
{
	print ("Module works from Getynet!");
	return;
}
if($sys_webmaster_email == "")
{
	print ("Configuration error: missing webmaster email");
	return;
}
if($v_sms_service_config["username"] == "" || $v_sms_service_config["password"] == "")
{
	print ("Configuration error: sms service not configured");
	return;
}
if(!$o_main->db->table_exists('sys_smssend'))
{
	print ("Configuration error: SmsSending module is not added");
	return;
}
if(!class_exists("PHPMailer")) include("class.phpmailer.php");
if(!function_exists("APIconnectUser")) include("APIconnect.php");
if(!function_exists("devide_by_uppercase")) include("fnctn_devide_by_upercase.php");
if(!function_exists("get_curent_GET_params")) include('fnctn_get_curent_GET_params.php');
if(!function_exists("send_email")) include_once("fnctn_send_email.php");

$currentParams=get_curent_GET_params(array('action'));
$currentUserEmail=$variables->loggID;
$smsTemplateDir = __DIR__."/../../";

if(!$o_main->db->table_exists('sys_smssend_userlist'))
{
	$o_main->db->simple_query("CREATE TABLE IF NOT EXISTS sys_smssend_userlist (
		id INT(11) NOT NULL AUTO_INCREMENT,
		session CHAR(13) NOT NULL DEFAULT '',
		source CHAR(50) NOT NULL DEFAULT '',
		mobile CHAR(128) NOT NULL DEFAULT '',
		name CHAR(128) NOT NULL DEFAULT '',
		extra1 CHAR(128) NOT NULL DEFAULT '',
		extra2 CHAR(128) NOT NULL DEFAULT '',
		text varchar(1000) NOT NULL DEFAULT '',
		selected tinyint(1) NOT NULL DEFAULT 1,
		disabled tinyint(1) NOT NULL DEFAULT 0,
		origID INT(11) NULL,
		origgroupID TINYINT(4) NULL DEFAULT NULL,
		PRIMARY KEY (id),
		INDEX idx1 (session, source, mobile),
		INDEX idx2 (origID)
	)");
	$o_main->db->simple_query("CREATE TABLE IF NOT EXISTS sys_smssend_userrelation (
		userfilterID INT(11) NOT NULL,
		userlistID INT(11) NOT NULL,
		INDEX idx1 (userfilterID, userlistID)
	)");
	$o_main->db->simple_query("CREATE TABLE IF NOT EXISTS sys_smssend_userfilter (
		id INT(11) NOT NULL AUTO_INCREMENT,
		session CHAR(13) NOT NULL DEFAULT '',
		source CHAR(50) NOT NULL DEFAULT '',
		parentID INT(11) NOT NULL DEFAULT 0,
		name CHAR(50) NOT NULL DEFAULT '',
		selected tinyint NOT NULL DEFAULT 1,
		hide_empty tinyint(1) NOT NULL DEFAULT 0,
		PRIMARY KEY (id),
		INDEX idx1 (parentID),
		INDEX idx2 (session, source)
	)");
	$o_main->db->simple_query("CREATE TABLE IF NOT EXISTS sys_smssend_userlistexpire (
		session CHAR(13) NOT NULL DEFAULT '',
		created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
		UNIQUE INDEX idx1 (session)
	)");
}

if(!isset($_POST['step']) && isset($sendSmsTemplate) && !empty($sendSmsTemplate))
{
	if($sendSmsTemplate == 'default_notify')
	{
		$_POST['sms_template'] = "";
	} else {
		$_POST['sms_template'] = $sendSmsTemplate;
	}
	$_POST['step'] = 1;
}
if($_POST['step']==1)
{
	// Get preloaded users
	if(isset($_GET['preload_session']))
	{
		$sendSms_session = $_GET['preload_session'];
		$o_query = $o_main->db->query('select session from sys_smssend_userlistexpire where session = ?', array($sendSms_session));
		if(!$o_query || ($o_query && $o_query->num_rows()==0))
		{
			unset($sendSms_session);
		}
	}
	
	if(!isset($sendSms_session))
	{
		$sendSms_session = uniqid();
		$s_sql = 'select session from sys_smssend_userlistexpire where session = ?';
		while($o_query = $o_main->db->query($s_sql, array($sendSms_session)) && $o_query && $o_query->num_rows()>0)
		{
			$sendEmail_session = uniqid();
		}
		if(!$o_main->db->query("insert into sys_smssend_userlistexpire(session, created) values(?, NOW())", array($sendSms_session)))
		{
			print $formText_errorOccuredPleaseReloadPage_sendFromInput;
			return;
		}
	}
}
?>
<form action="?<?php print($currentParams);?>" method="POST" id="mailingForm">
<input type="hidden" name="POST_ID" value="<?php if($_POST['step']==2) print $_POST['POST_ID']; else print(rand(0,30000));?>">
<table width="100%" border="0">
<tr><td width="25%"></td><td></td></tr>
<?php
if($_POST['step'] >= 1)
{
	if(!isset($sendSmsTemplate)) { ?>
	<tr>
		<td valign="top"><?php echo $formText_emailTemplate_sendFromInput;?>:</td>
		<td><i><?php echo ($_POST['sms_template']=='' ? $formText_Notification_sendFromInput : devide_by_uppercase($_POST['sms_template']));?></i></td>
	</tr>
	<?php
	}
	?><input type="hidden" value="<?php echo $_POST['sms_template'];?>" name="sms_template"><?php
	$templateTable = explode(":",$mysqlTableName[0]);
	$templateTable = $templateTable[0];
	$templateID = $_GET['ID'];
	$templateLanguageID = $s_default_output_language;
	if($_POST['sms_template']=='')
	{
		$smsMessage = "{$formText_NewContentWasAddedIn_sendFromInput} {$preinputformName} ".strtolower($formText_module_sendFromInput).".";
	} else {
		include($smsTemplateDir.'output_smsFromModule_'.$_POST['sms_template'].'/template.php');
	}
	?>
	<tr><td colspan="2" style="border-bottom:1px solid #333333;"><h3 style="margin-bottom:0;"><?php echo $formText_preview_sendFromInput;?></h3></td></tr>
	<tr>
		<td valign="top"><?php echo $formText_message_sendFromInput;?></td>
		<td><?php echo $smsMessage;?></td>
	</tr>
	<?php
}

?><tr><td colspan="2" style="border-bottom:1px solid #333333;"><h3 style="margin-bottom:0;"><?php if($_POST['step']<2) print $formText_configuration_sendFromInput;?></h3></td></tr><?php
if(!isset($_POST['step']) or $_POST['step']==0) // ***************************************** choose sms template
{
	?><tr>
		<td><?php echo $formText_ChooseSmsTemplate_sendFromInput;?>:</td>
		<td><select name="sms_template">
			<option value=""><?php echo $formText_Notification_sendFromInput;?></option><?php
			//output folders in module with "output_smsFromModule_[Name]" string.
			if($handle = opendir($smsTemplateDir)) 
			{
				while(false !== ($file = readdir($handle)))
				{
					if($file!="." and $file!=".." and is_dir($smsTemplateDir."/".$file))
					{
						if(strpos($file,"output_smsFromModule_")!==false)
						{
							$template = str_replace("output_smsFromModule_","",$file);
							?><option value="<?php echo $template;?>"><?php echo devide_by_uppercase($template);?></option><?php
						}
					}
				}
				closedir($handle); 
			}
			?>
		</select></td>
	</tr>
	<tr><td colspan="2">
		<input id="next-btn" type="submit" name="send" value="<?php echo $formText_Next_sendFromInput;?>">
		<input type="hidden" id="hidden_step" name="step" value="1">
	</td></tr><?php
} else if($_POST['step'] == 1) { // ************************************ choose users
	?>
	<tr>
		<td colspan="2">
		<input type="hidden" name="session" value="<?php echo $sendSms_session;?>">
		<div id="sendSms_users">
			<div class="header"><a id="sendSms_load_users" href="#sendSms_upopup"><?php echo $formText_selectReceivers_fieldtype;?> +</a></div>
			<div><?php echo $formText_totalUsersSelected_sendFromInput;?>: <span id="sendSms_total_users">0</span></div>
			<script type="text/javascript">
			var sendSms_instance;
			var sendSms_sources;
			var sendSms_manual_selection_changed;
			var sendSms_manual_source;
			var sendSms_manual_source_config;
			var sendSms_manual_page;
			var sendSms_userlist_view;
			$(function() {
				$('#sendSms_load_users').fancybox({ 'mouseWheel' : false, beforeClose: function() { return sendSms_check_send(); } });
				$('#sendSms_upopup input.pop-close').on('click', function() {
					if(sendSms_instance) return;
					$.fancybox.close();
				});
				$('#sendSms_mpopup input.pop-ok').on('click', function() {
					// Userlist OK button
					if(sendSms_instance) return;
					
					if(sendSms_userlist_view)
					{
						$.fancybox.close();
						sendSms_userlist_view = false;
					} else {
						sendSms_save_manual_selection('filter');
					}
					
					if(sendSms_instance) return;
					sendSms_save_manual_selection('filter');
				});
				$('#sendSms_addpopup input.pop-ok').on('click', function() {
					// Custom add OK button
					if(sendSms_instance) return;
					
					sendSms_save_manual_add();
				});
				
				
				$('.sendSms_contactset input').on('change',function() {
					if(sendSms_instance) return;
					
					var _this = $(this);
					$(this).next('label').find('.sendSms_contactsetedit').show();
					if($(_this).is('.import'))
					{
						sendSms_instance_on();
						$(this).removeClass('import');
						$.ajax({
							type: 'POST',
							dataType: 'json',
							url: '<?php echo $extradir;?>/input/includes/ajax_sendSms_users.php',
							cache: false,
							data: { action: 'import', source: $(this).val(), session: '<?php echo $sendSms_session;?>', field: 'sendSms', companyID : '<?php echo $_GET['companyID'];?>', choosenListInputLang: '<?php echo $choosenListInputLang;?>', caID: '<?php echo $_GET['caID'];?>' },
							success: function(data) {
								if(data.id)
								{
									$('#sendSms_upopup .'+data.id+' .sendSms_contactsetcount').text(' (<?php echo $formText_selected_fieldtype;?> '+data.selected+' <?php echo $formText_of_fieldtype;?> '+data.total+') ').css('font-weight','bold');
									$(_this).val(data.id);
									if(data.filter) $('#sendSms_upopup .sendSms_contactset.'+data.id).append(data.filter);
								}
								sendSms_instance_off();
								$.fancybox.update();
							}
						});
					} else {
						if(this.checked) $(this).parent().find('.sendSms_filter').show();
						else $(this).parent().find('.sendSms_filter').hide();
						sendSms_change_selection(this, $(this).val(), $(this).val(), 0, this.checked);
					}
				});
				$('.sendSms_contactset .sendSms_contactsetedit').on('click',function() {
					if(sendSms_instance) return;
					
					sendSms_manual_source = $(this).parent().prevAll('input.source').val();
					sendSms_manual_source_config = $(this).attr('data-order') + ':' + $(this).attr('data-orderby');
					sendSms_manual_selection_changed = false;
					$.fancybox.open({ href: '#sendSms_mpopup', 'mouseWheel' : false, beforeClose: function() { return sendSms_check_send(); } });
					sendSms_show_userlist_page(0);
				});
				$('.sendSms_contactset .sendSms_contactsetadd').on('click',function() {
					if(sendSms_instance) return;
					
					$(this).closest('.sendSms_contactset').find('input.source').attr('checked',1);
					$('#sendSms_addpopup .pop-data input').val('');
					$.fancybox.open({ href: '#sendSms_addpopup', 'mouseWheel' : false, beforeClose: function() { return sendSms_check_send(); } });
				});
				
				$('#sendSms_total_users').on('click',function() {
					if(sendSms_instance) return;
					
					sendSms_userlist_view = true;
					sendSms_manual_source = '';
					sendSms_manual_source_config = '1,2:1';
					sendSms_manual_selection_changed = false;
					$.fancybox.open({ href: '#sendSms_mpopup', 'mouseWheel' : false, beforeClose: function() { return sendSms_check_send(); } });
					sendSms_show_userlist_page(0);
				});
				
				<?php if(isset($_GET['preload_session'])) { ?>
				$('.sendSms_contactset.preload input.source').trigger('click');
				sendSms_check_send();
				<?php } ?>
				
				// DO DB CLEANUP
				$.ajax({
					type: 'POST',
					url: '<?php echo $extradir;?>/input/includes/ajax_sendSms_users.php',
					cache: false,
					data: { action: 'cleanup', caID: '<?php echo $_GET['caID'];?>' }
				});
				
			});
			
			function sendSms_show_userlist_page(page)
			{
				if(!sendSms_instance)
				{
					if(page != null) sendSms_manual_page = page;
					if(sendSms_manual_selection_changed && confirm('<?php echo $formText_changesWereMadeDoYouWantToSaveThem_fieldtype;?>?'))
					{
						sendSms_save_manual_selection('page');
						return;
					}
					sendSms_instance_on();
					sendSms_manual_selection_changed = false;
					
					$.fancybox.showLoading();
					$.ajax({
						type: 'POST',
						dataType: 'json',
						url: '<?php echo $extradir;?>/input/includes/ajax_sendSms_users.php',
						cache: false,
						data: { action: 'list', source: sendSms_manual_source, session: '<?php echo $sendSms_session;?>', field: 'sendSms', sourceconfig: sendSms_manual_source_config, page: sendSms_manual_page, caID: '<?php echo $_GET['caID'];?>' },
						success: function(data) {
							$('#sendSms_mpopup .pop-data').html(data.html);
							$.fancybox.update();
							sendSms_instance_off();
						}
					});
				}
			}
			
			function sendSms_save_manual_selection(callback)
			{
				if(!sendSms_instance)
				{
					if(sendSms_manual_selection_changed)
					{
						sendSms_instance_on();
						var sendSms_users_selected = new Array();
						var sendSms_users_unselected = new Array();
						$('#sendSms_mpopup .pop-data input:checked').each(function () { sendSms_users_selected.push($(this).val()); });
						$('#sendSms_mpopup .pop-data input:not(:checked)').each(function () { sendSms_users_unselected.push($(this).val()); });
						
						$.ajax({
							type: 'POST',
							dataType: 'json',
							url: '<?php echo $extradir;?>/input/includes/ajax_sendSms_users.php',
							cache: false,
							data: { action: 'manual_update', selected: sendSms_users_selected, unselected: sendSms_users_unselected, caID: '<?php echo $_GET['caID'];?>' },
							success: function(data) {
								if(data.total)
								{
									var _bold = 'normal';
									if(parseInt(data.selected)>0) _bold = 'bold';
									$('#sendSms_upopup .sendSms_contactset.'+sendSms_manual_source+' .sendSms_filter').hide();
									$('#sendSms_upopup .sendSms_contactset.'+sendSms_manual_source+' input:checked').prop('checked',false);
									$('#sendSms_upopup .'+sendSms_manual_source+' .sendSms_contactsetcount').text(' (<?php echo $formText_manualUserSelection_fieldtype;?> '+data.selected+' <?php echo $formText_of_fieldtype;?> '+data.total+') ').css('font-weight',_bold);
								} else {
									alert('<?php echo $formText_errorOccured_fieldtype;?>');
								}
								sendSms_manual_selection_changed = false;
								sendSms_instance_off();
								
								if(callback == 'page') sendSms_show_userlist_page();
								else if(callback == 'filter') sendSms_back_to_filter();
							}
						});
					} else {
						if(callback == 'page') sendSms_show_userlist_page();
						else if(callback == 'filter') sendSms_back_to_filter();
					}
				}
			}
			
			function sendSms_save_manual_add()
			{
				if(!sendSms_instance)
				{
					sendSms_instance_on();
					$.ajax({
						type: 'POST',
						dataType: 'json',
						url: '<?php echo $extradir;?>/input/includes/ajax_sendSms_users.php',
						cache: false,
						data: { action: 'manual_add', session: '<?php echo $sendSms_session;?>', caID: '<?php echo $_GET['caID'];?>', name: $('#sendSms_addpopup .pop-data input.name').val(), mobile: $('#sendSms_addpopup .pop-data input.mobile').val() },
						success: function(data) {
							if(data.id)
							{
								$('#sendSms_upopup .'+data.id).find('.sendSms_contactsetedit').show();
								$('#sendSms_upopup .'+data.id+' .sendSms_contactsetcount').text(' (<?php echo $formText_selected_fieldtype;?> '+data.selected+' <?php echo $formText_of_fieldtype;?> '+data.total+') ').css('font-weight','bold');
								if(data.filter) $('#sendSms_upopup .sendSms_contactset.'+data.id).append(data.filter);
							}
							sendSms_instance_off();
							sendSms_back_to_filter();
						}
					});
				}
			}
			
			function sendSms_change_selection(_this, _source, changeSource, filterId, checked)
			{
				if(!sendSms_instance)
				{
					sendSms_instance_on();
					sendSms_update_selection(_this, checked);
					if(checked) checked = 1; else checked = 0;
					
					$.fancybox.showLoading();
					$.ajax({
						type: 'POST',
						dataType: 'json',
						url: '<?php echo $extradir;?>/input/includes/ajax_sendSms_users.php',
						cache: false,
						data: { action: 'change_selection', source: _source, changeSource: changeSource, filterId: filterId, session: '<?php echo $sendSms_session;?>', checked: checked, caID: '<?php echo $_GET['caID'];?>' },
						success: function(data) {
							var _bold = 'normal';
							if(parseInt(data.selected)>0) _bold = 'bold';
							$('#sendSms_upopup .'+_source+' .sendSms_contactsetcount').text(' (<?php echo $formText_selected_fieldtype;?> '+data.selected+' <?php echo $formText_of_fieldtype;?> '+data.total+') ').css('font-weight',_bold);
							sendSms_instance_off();
						}
					});
				} else {
					$(_this).prop('checked',!checked);
				}
			}
			
			function sendSms_update_selection(_this, checked)
			{	
				$(_this).nextAll('.sendSms_filter').find('input').prop('checked',checked);
				if($(_this).not('.source'))
				{
					$(_this).parentsUntil('.setcontainer', '.sendSms_filter').each(function() {
						if(checked)
						{
							if($(this).find('.sendSms_filter').children('input:not(:checked)').length == 0)
							{
								$(this).children('input:not(:checked)').prop('checked',checked);
							}
						} else {
							$(this).children('input:checked').prop('checked',checked);
						}
					});
				}
				
			}
			
			function sendSms_back_to_filter()
			{
				$.fancybox.open({href: '#sendSms_upopup', 'mouseWheel' : false, beforeClose: function() { return sendSms_check_send(); } });
			}
			
			function sendSms_user_change()
			{
				if(!sendSms_instance)
				{
					sendSms_manual_selection_changed = true;
				} else {
					$(_this).prop('checked',!checked);
				}
			}
			
			function sendSms_check_send()
			{
				if(sendSms_instance) return false;
				$.fancybox.showLoading();
				$.ajax({
					type: 'POST',
					async: false,
					dataType: 'json',
					url: '<?php echo $extradir;?>/input/includes/ajax_sendSms_users.php',
					cache: false,
					data: { action: 'check_send', session: '<?php echo $sendSms_session;?>', caID: '<?php echo $_GET['caID'];?>' },
					timeout: 1000,
					error: function(){
						$.fancybox.hideLoading();
						return false;
					},
					success: function(data) {
						$('#sendSms_total_users').text(data.total);
						if(parseInt(data.total)>0)
							$('#next-btn').attr('disabled',false);
						else
							$('#next-btn').attr('disabled',true);
						$.fancybox.hideLoading();
						return true;
					}
				});
			}
			function sendSms_instance_on()
			{
				sendSms_instance = true;
				$.fancybox.showLoading();
				$('#sendSms_upopup input').attr('disabled',true);
				$('#sendSms_mpopup input').attr('disabled',true);
			}
			function sendSms_instance_off()
			{
				$.fancybox.hideLoading();
				$('#sendSms_upopup input').attr('disabled',false);
				$('#sendSms_mpopup input').attr('disabled',false);
				sendSms_instance = false;
			}
			</script>
			<div style="display:none;">
				<div id="sendSms_upopup">
					<h3><center><?php echo $formText_selectReceivers_fieldtype;?></center></h3>
					<div class="pop-filter">
						<?php
						$uniqueSource = array();
						$sources = json_decode(stripslashes($sendSmsUserSource),true);
						
						if($sendSmsActivateCustomUsers == 1)
						{
							?><div class="setcontainer"><div class="sendSms_contactset sendSms_filter preload"><input type="checkbox" class="source" name="sendSms_contactset[]" value="preload"><label><?php echo $formText_CustomUserlist_fieldtype;?><span class="sendSms_contactsetcount"></span>&nbsp;&nbsp;&nbsp;&nbsp;<span class="sendSms_contactsetadd"><?php echo $formText_Add_fieldtype;?></span>&nbsp;&nbsp;&nbsp;&nbsp;<span class="sendSms_contactsetedit" data-order="1,2" data-orderby="1"><?php echo $formText_editUserList_fieldtype;?></span></label></div></div><?php
						}
						if($sendSmsActivateGetynetUsers == 1)
						{
							?><div class="setcontainer"><div class="sendSms_contactset sendSms_filter getynet"><input type="checkbox" class="source import" name="sendSms_contactset[]" value="getynet"><label><?php echo $formText_getynetUsers_fieldtype;?><span class="sendSms_contactsetcount"></span>&nbsp;&nbsp;&nbsp;&nbsp;<span class="sendSms_contactsetedit" data-order="1,2" data-orderby="1"><?php echo $formText_editUserList_fieldtype;?></span></label></div></div><?php
						}
						foreach($sources as $item)
						{
							list($vSource,$filters) = explode('(:)',$item,2);
							$vSource = explode(':',$vSource);
							$source = $vSource[0];
							$i = 0;
							while(in_array($source,$uniqueSource))
							{
								$i++;
								$source = $vSource[0].$i;
							}
							$uniqueSource[] = $source;
							?><div class="setcontainer"><div class="sendSms_contactset sendSms_filter <?php echo $source;?>"><input type="checkbox" class="source import" name="sendSms_contactset[]" value="<?php echo $source.':'.$item;?>"><label><?php echo $vSource[1];?><span class="sendSms_contactsetcount"></span>&nbsp;&nbsp;&nbsp;&nbsp;<span class="sendSms_contactsetedit" data-order="<?php echo $vSource[6];?>" data-orderby="<?php echo $vSource[7];?>"><?php echo $formText_editUserList_fieldtype;?></span></label></div></div><?php
						}
						?>
					</div>
					<div class="pop-btns"><input class="pop-close" type="button" value="<?php echo $formText_Ok_fieldtype;?>">&nbsp;&nbsp;&nbsp;&nbsp;</div>
				</div>
				<div id="sendSms_mpopup">
					<h3><center><?php echo $formText_selectReceivers_fieldtype;?></center></h3>
					<div class="pop-data"></div>
					<div class="pop-btns"><input class="pop-ok" type="button" value="<?php echo $formText_Ok_fieldtype;?>"></div>
				</div>
				<div id="sendSms_addpopup">
					<h3><center><?php echo $formText_AddUser_fieldtype;?></center></h3>
					<div class="pop-data">
						<div><label><?php echo $formText_Name_fieldtype;?></label><input class="name" type="text" name="name" value="" /></div>
						<div><label><?php echo $formText_Mobile_fieldtype;?></label><input class="mobile" type="text" name="mobile" value="" /></div>
					</div>
					<div class="pop-btns"><input class="pop-ok" type="button" value="<?php echo $formText_Add_fieldtype;?>"></div>
				</div>
			</div>
		</div>
		</td>
	</tr>
	<tr><td colspan="2">
		<?php if(!isset($sendSmsTemplate)) { ?><input type="button" onClick="javascript: $('#hidden_step').val(0); $('#mailingForm').submit();" value="<?php print($formText_Back_sendFromInput);?>"><?php } ?>
		<input id="next-btn" type="submit" name="send" onClick="javascript: if(!confirm('<?php echo $formText_AreYouSureYouWantToSendThisSmsToSelectedUsers_sendFromInput;?>?')) return false;" value="<?php echo $formText_Send_sendFromInput;?>" disabled>
		<input type="hidden" id="hidden_step" name="step" value="2">
	</td></tr><?php
} else if($_POST['step'] == 2) { // ************************************ SMS ADDED TO QUEUE - DONE
	?>
	<tr><td colspan="2">
		<?php /*?><input type="button" onClick="javascript: $('#hidden_step').val(1); $('#mailingForm').submit();" value="<?=$formText_Back_sendFromInput;?>">
		<input type="hidden" id="hidden_myself" name="_hidden" value="">
		<input type="button" id="next-btn" onClick="javascript: $('#hidden_step').val(0); $('#mailingForm').submit();" value="<?=$formText_Reset_sendFromInput;?>">
		<input type="hidden" id="hidden_step" name="step" value="3"><?php */?>
	</td></tr><?php
}
?>
</table>
</form>
<?php


$smsSucess=0;  
$smsFailed=0;
if($_POST['step'] == 2)
{
	// ****
	// ****************** SEND SMS
	// ****
	if($_SESSION['caID_'.$_GET['caID']]['POST_ID'] != $_POST['POST_ID'])
	{
		$s_secure = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : ""; 
		list($s_protocol,$s_rest) = explode("/", strtolower($_SERVER["SERVER_PROTOCOL"]),2); 
		$l_port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]);
		$s_account_url = $s_protocol.$s_secure."://".$_SERVER['SERVER_NAME'].$l_port."/accounts/".$_GET['accountname']."/";
		$currentUser = json_decode(APIconnectUser("userinfoget", $variables->loggID, $variables->sessionID, array('SEARCH_USERNAME'=>$variables->loggID,'COMPANY_ID'=>$companyID)),true);
		$currentUserName = ($currentUser['data']['name'] != "" ? $currentUser['data']['name'] : $emailCompanyName.$emailAccountName);
		
		$s_send_on = date("d-m-Y H:i");
		$o_query = $o_main->db->query("select * from sys_smssend_userlist where session = ? and selected = 1 and disabled = 0 group by mobile", array($_POST['session']));
		if($o_query && $o_query->num_rows()>0)
		{
			$s_sql = "INSERT INTO sys_smssend (id, created, createdBy, `type`, send_on, sender, sender_email, content_module_id, content_id, content_table, message) VALUES (NULL, NOW(), ?, 2, STR_TO_DATE('".$s_send_on."','%d-%m-%Y %H:%i'), ?, ?, ?, ?, ?, ?)";
			$o_main->db->query($s_sql, array($variables->loggID, $currentUserName, $variables->loggID, $moduleID, $_GET['ID'], $templateTable, $smsMessage));
			$smsSentID = $o_main->db->insert_id();
		}
		
		if($o_query && $o_query->num_rows()>0)
		foreach($o_query->result_array() as $row)
		{
			$sql = "INSERT INTO sys_smssendto
					(id, smssend_id, receiver, receiver_mobile, `status`, status_message, response, perform_time, perform_count) 
					VALUES (NULL, ?, ?, ?, 0, '', '', '', 0)";
			$o_main->db->query($sql, array($smsSentID, $row['name'], $row['mobile']));
			$lastId = $o_main->db->insert_id();
			
			if(strpos($row['mobile'],'+')===false)
				$row['mobile'] = $v_sms_service_config['prefix'].$row['mobile'];
			
			$data = array('User' => $v_sms_service_config['username'], 'Password' => $v_sms_service_config['password'],
						'LookupOption' => $v_sms_service_config['lookup_option'], 'MessageType' => $v_sms_service_config['type'],
						'Originator' => $v_sms_service_config['originator'], 'RequireAck' => 1, 'AckUrl' => $s_account_url."elementsGlobal/smsack.php",
						'BatchID' => $lastId, 'ChannelID' => 0, 'Msisdn' => $row['mobile'], 'Data' => $smsMessage);
			
			//call api 
			$url = 'http://msgw.linkmobility.com/MessageService.aspx';
			
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url.'?'.http_build_query($data));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			
			$response = curl_exec($ch);
			curl_close($ch);
			
			if(strpos($response,'NOK')===false)
			{
				$smsSucess++;
				$o_main->db->query("update sys_smssendto set status = 1, response = ?, perform_time = NOW(), perform_count = 1 where id = ? and status = 0", array($response, $lastId));
			} else {
				$smsFailed++;
				$o_main->db->query("update sys_smssendto set status = 3, status_message = 'Error occured on sms registration', response = ?, perform_time = NOW(), perform_count = 1 where id = ? and status = 0", array($response, $lastId));
				$s_host = ($v_email_server_config['host'] != "" ? $v_email_server_config['host'] : "mail.dcode.no");
				
				send_email($s_host, $v_email_server_config['port'], $v_email_server_config['username'], $v_email_server_config['password'], $sys_webmaster_email, $sys_webmaster_email, "SMS from module: sms registration error", "Error occured on sms registration: ".$response.'<br>Technical info:<br>'.var_export($data,true));
			}
		}

		$o_main->db->query("delete from sys_smssend_userlist where session = ?", array($_POST['session']));
		$o_main->db->query("delete ur.* from sys_smssend_userrelation ur join sys_smssend_userfilter uf on uf.id = ur.userfilterID where uf.session = ?", array($_POST['session']));
		$o_main->db->query("delete from sys_smssend_userfilter where session = ?", array($_POST['session']));
		$o_main->db->query("delete from sys_smssend_userlistexpire where session = ?", array($_POST['session']));
		$_SESSION['caID_'.$_GET['caID']]['POST_ID'] = $_POST['POST_ID'];
	} else {
		print "<h3>".$formText_RequestWasProcessedAlready_sendFromInput."</h3>";
	}
}


if($smsSucess > 0 or $smsFailed > 0) print "<h3>".$smsSucess." ".$formText_smsWasAddedToQueue_sendFromInput.". ".$formText_Failed_sendFromInput.": ".$smsFailed.". ".$formText_forDetailsCheckReport_sendFromInput."</h3>";

/*
** REPORT
*/
$s_sql = "select * from sys_smssend where content_id = ? and content_table = ? and content_module_id = ? and type = 2 order by id DESC";
$o_query = $o_main->db->query($s_sql, array($_GET['ID'], $templateTable, $moduleID));
if($o_query && $o_query->num_rows()>0)
{
	?><div class="report">
	<table border="0" width="100%" cellpadding="0" cellspacing="0">
	<tr><td colspan="3"><h3><?php echo $formText_SentItems_sendFromInput;?></h3></td></tr>
	<tr class="title">
		<td><?php echo $formText_created_SendFromInput;?></td>
		<td><?php echo $formText_message_SendFromInput;?></td>
		<td></td>
	</tr>
	<?php
	foreach($o_query->result_array() as $v_row)
	{
		$v_row['send_on'] = date('d-m-Y H:i',strtotime($v_row['send_on']));
		?>
		<tr class="item">
			<td class="date"><?php echo $v_row['send_on'];?></td>
			<td class="subject"><?php echo $v_row['message'];?></td>
			<td class="link"><a href="javascript:;" onClick="show_report_input(<?php echo "'".$_GET['ID']."', '".$templateTable."', '".$moduleID."', '".$v_row['send_on']."'";?>);"><?php /*?><img border="0" src="<?=$extradir;?>/input/includes/images/document.png" align="texttop" /><?php */?><?php echo $formText_showReport_sendFromInput;?></a></td>
		</tr>
		<?php
	}
	?></table>
	<script type="text/javascript">
	function show_report_input(id, table, moduleid, time, page)
	{
		if(!page) page = 0;
		$.fancybox.showLoading();
		$.ajax({
			type: 'POST',
			url: '<?php echo $extradir;?>/input/fieldtypes/ReminderSMS/ajax_report.php',
			cache: false,
			data: { field_ui_id: 'sendSms', id : id, table: table, moduleid: moduleid, time: time, dir: '<?php echo $extradir;?>/input/fieldtypes/ReminderSMS/', languageID: '<?php echo $choosenListInputLang;?>', type: 2, page: page, caID: '<?php echo $_GET['caID'];?>' },
			success: function(data) {
				$.fancybox.hideLoading();
				$.fancybox('<h2><?php echo $formText_ReportFor_sendFromInput.': ';?>' + time + '</h2>' + data);
			}
		});
	}
	</script>
	</div><?php
}

?>
<style>
#next-btn { float:right; }

#sendSms_users { margin:20px 0; padding-left:20px; }
#sendSms_users .header { font-size:13px; font-weight:bold; line-height:26px; }
#sendSms_users .header a { color:#000066; text-decoration:none; }
#sendSms_total_users { font-size:13px; font-weight:bold; cursor:pointer; }

.sendSms_contactset { margin:3px 0; padding:2px 0; }
.sendSms_contactset:hover { background-color:#e9e9e9; }
.sendSms_contactsetedit { display:none; cursor:pointer; color:#000066; text-decoration:none; font-weight:bold; }
.sendSms_filter.filter { padding-left:30px; }
.sendSms_filter.filter label { cursor:pointer; }

#sendSms_upopup { min-width:500px; min-height:500px; }
#sendSms_mpopup { min-width:900px; min-height:500px; }
#sendSms_mpopup .pop-data .item { white-space:nowrap; }
#sendSms_mpopup .pop-data .item:nth-child(even) { background-color:#efefef; }
#sendSms_mpopup .pop-data .item:hover { background-color:#88ddff; }
#sendSms_mpopup .pop-data .item td { cursor:pointer; padding:2px 10px 2px 0; line-height:19px; }
#sendSms_mpopup .pop-data .item td.list_checkbox { cursor:inherit; width:20px; }
#sendSms_upopup .pop-btns, #sendSms_mpopup .pop-btns, #sendSms_addpopup .pop-btns, #sendSms_mpopup .paging { text-align:center; margin-top:20px; }
#sendSms_mpopup .paging a { font-size:12px; padding:0 3px; color:#666666; text-decoration:none; }
#sendSms_mpopup .paging a:hover, #sendSms_mpopup .paging a.active { color:#000066; font-weight:bold; }
#sendSms_addpopup { min-width:500px; min-height:100px; }
#sendSms_addpopup .pop-data div { padding-bottom:5px; }
#sendSms_addpopup .pop-data label { display:inline-block; width:25%; margin-right:2%; text-align:right; }
#sendSms_addpopup .pop-data input { width:50%; }

.report { margin:20px 0 20px 5px; }
.report tr.title td { padding-bottom:5px; font-weight:bold; border-bottom:1px solid #333333; }
.report tr.item:nth-child(even) { background-color:#efefef; }
.report tr.item:hover { background-color:#88ddff; }
.report td { vertical-align:top; padding:3px 0px; }
.report td.name { width:20%; }
.report td.subject { width:53%; }
.report td.link { text-align:right; padding-right:1%; width:13%; }
.report td.link a { color:#000066; text-decoration:none; }

.sendSms_sumarize { padding:5px; font-size:13px; font-weight:bold; }
.sendSms_paging { padding:5px; text-align:center; }
.sendSms_paging a { color:#000066; text-decoration:none; }
.sendSms_report { min-width:700px; padding:2px 5px; }
.sendSms_report:nth-child(even) { background-color:#efefef; }
.sendSms_report:hover { background-color:#88ddff; }
.sendSms_report span.name { width:30%; display:inline-block; }
.sendSms_report span.mobile { width:30%; display:inline-block; }
.sendSms_report span.performed { width:20%; display:inline-block; }
</style>