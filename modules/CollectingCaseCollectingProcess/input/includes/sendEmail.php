<?php
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
require_once('Exception.php');
require_once('PHPMailer.php');
require_once('SMTP.php');

$accountinfo = array();
$o_query = $o_main->db->query('SELECT * FROM accountinfo');
if($o_query && $o_query->num_rows()>0) $accountinfo = $o_query->row_array();
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
	print ("Configuration error: Webmaster email is missing");
	return;
}
if($v_email_server_config["host"] == "")
{
	print ("Configuration error: Email server not configured");
	return;
}
if(!$o_main->db->table_exists('sys_emailsend'))
{
	print ("Configuration error: EmailSending module not added");
	return;
}

if(!function_exists("APIconnectUser")) include("APIconnect.php");
if(!function_exists("devide_by_uppercase")) include("fnctn_devide_by_upercase.php");
if(!function_exists("get_curent_GET_params")) include('fnctn_get_curent_GET_params.php');
if(!function_exists("sendEmail_get_module_options")) include("fn_sendEmail_get_module_options.php");
if(!function_exists("sendEmail_extract_images")) include("fn_sendEmail_extract_images.php");
if(!function_exists("sendEmail_extract_attachments")) include("fn_sendEmail_extract_attachments.php");

$currentParams=get_curent_GET_params(array('action'));
$currentUserEmail=$variables->loggID;
$emailTemplateDir = __DIR__."/../../";


if(!$o_main->db->table_exists('sys_emailsendunsubscribe'))
{
	$o_main->db->simple_query("CREATE TABLE sys_emailsendunsubscribe (
		id INT(11) NOT NULL AUTO_INCREMENT,
		email CHAR(100) NOT NULL,
		created TIMESTAMP NOT NULL,
		PRIMARY KEY (id),
		UNIQUE INDEX Idx (email)
	)");
}
if(!$o_main->db->table_exists('sys_emailsend_userlist'))
{
	$o_main->db->simple_query("CREATE TABLE IF NOT EXISTS sys_emailsend_userlist (
		id INT(11) NOT NULL AUTO_INCREMENT,
		session CHAR(13) NOT NULL DEFAULT '',
		source CHAR(50) NOT NULL DEFAULT '',
		email CHAR(128) NOT NULL DEFAULT '',
		name CHAR(128) NOT NULL DEFAULT '',
		extra1 CHAR(128) NOT NULL DEFAULT '',
		extra2 CHAR(128) NOT NULL DEFAULT '',
		text varchar(1000) NOT NULL DEFAULT '',
		selected tinyint(1) NOT NULL DEFAULT 1,
		disabled tinyint(1) NOT NULL DEFAULT 0,
		origID INT(11) NULL,
		origgroupID TINYINT(4) NULL,
		PRIMARY KEY (id),
		INDEX idx1 (session, source, email),
		INDEX idx2 (origID)
	)");
	$o_main->db->simple_query("CREATE TABLE IF NOT EXISTS sys_emailsend_userrelation (
		userfilterID INT(11) NOT NULL,
		userlistID INT(11) NOT NULL,
		INDEX idx1 (userfilterID, userlistID)
	)");
	$o_main->db->simple_query("CREATE TABLE IF NOT EXISTS sys_emailsend_userfilter (
		id INT(11) NOT NULL AUTO_INCREMENT,
		session CHAR(13) NOT NULL DEFAULT '',
		source CHAR(50) NOT NULL DEFAULT '',
		parentID INT(11) NOT NULL DEFAULT 0,
		name CHAR(50) NOT NULL DEFAULT '',
		selected tinyint(1) NOT NULL DEFAULT 1,
		hide_empty tinyint(1) NOT NULL DEFAULT 0,
		PRIMARY KEY (id),
		INDEX idx1 (parentID),
		INDEX idx2 (session, source)
	)");
	$o_main->db->simple_query("CREATE TABLE IF NOT EXISTS sys_emailsend_userlistexpire (
		session CHAR(13) NOT NULL DEFAULT '',
		created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
		UNIQUE INDEX idx1 (session)
	)");
}

if(!isset($_POST['step']) && isset($sendEmailTemplate) && !empty($sendEmailTemplate))
{
	if($sendEmailTemplate == 'default_notify')
	{
		$_POST['email_template'] = "";
	} else {
		$_POST['email_template'] = $sendEmailTemplate;
	}
	$_POST['step'] = 1;
}
if($_POST['step']==1)
{
	// Get preloaded users
	if(isset($_GET['preload_session']))
	{
		$sendEmail_session = $_GET['preload_session'];
		$o_query = $o_main->db->query('select session from sys_emailsend_userlistexpire where session = ?', array($sendEmail_session));
		if(!$o_query || ($o_query && $o_query->num_rows()==0))
		{
			unset($sendEmail_session);
		}
	}
	
	if(!isset($sendEmail_session))
	{
		$sendEmail_session = uniqid();
		$s_sql = 'select session from sys_emailsend_userlistexpire where session = ?';
		while($o_query = $o_main->db->query($s_sql, array($sendEmail_session)) && $o_query && $o_query->num_rows()>0)
		{
			$sendEmail_session = uniqid();
		}
		if(!$o_main->db->query("insert into sys_emailsend_userlistexpire(session, created) values(?, NOW())", array($sendEmail_session)))
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
	if(!isset($sendEmailTemplate)) { ?>
	<tr>
		<td valign="top"><?php echo $formText_emailTemplate_sendFromInput;?>:</td>
		<td><i><?php echo ($_POST['email_template']=='' ? $formText_Notification_sendFromInput : devide_by_uppercase($_POST['email_template']));?></i></td>
	</tr>
	<?php
	}
	?><input type="hidden" value="<?php echo $_POST['email_template'];?>" name="email_template"><?php
	$templateTable = explode(":",$mysqlTableName[0]);
	$templateTable = $templateTable[0];
	$templateID = $_GET['ID'];
	$templateLanguageID = $s_default_output_language;
	
	$data = json_decode(APIconnectAccount("accountcompanyinfoget", $accountinfo['accountname'], $accountinfo['password'], array()),true);
	$emailCompanyName = $data['data']['companyname'];
	
	$data = json_decode(APIconnectAccount("currentaccountnameget", $accountinfo['accountname'], $accountinfo['password'], array()),true);
	$emailAccountName = " - ".($data['data']['friendlyaccountname'] != "" ? $data['data']['friendlyaccountname'] : $data['data']['accountname']);
	$currentUser = json_decode(APIconnectUser("userinfoget", $variables->loggID, $variables->sessionID, array('SEARCH_USERNAME'=>$variables->loggID,'COMPANY_ID'=>$companyID)),true);
	$currentUserName = ($currentUser['data']['name'] != "" ? $currentUser['data']['name'] : $emailCompanyName.$emailAccountName);
	
	$o_query = $o_main->db->query("select id from sys_emailsend_userlist where session = ? and selected = ? LIMIT 1", array($_POST['session'], 1));
	if($_POST['step']==2 and $o_query && $o_query->num_rows() > 0)
	{
		$s_sql = "INSERT INTO sys_emailsend (id, created, createdBy, `type`, sender, sender_email, subscriberlist_id, unsubscriberlist_id, content_id, content_table, content_module_id, sending_limit, subject, text) VALUES (NULL, NOW(),?,?,?,?,?,?,?,?,?,?,?,?);";
		$o_main->db->query($s_sql, array($variables->loggID, 2, $currentUserName, $variables->loggID, 0, 0, $_GET['ID'], $templateTable, $moduleID, 0, $emailSubjectData, $emailBodyData));
		$emailSentID = $o_main->db->insert_id();
	}
	
	if($_POST['email_template']=='')
	{
		$emailSubjectData = $emailCompanyName.$emailAccountName.": ".$formText_newContentAdded_sendFromInput;
		$emailBodyData = "{$formText_NewContentWasAddedIn_sendFromInput} {$preinputformName} ".strtolower($formText_module_sendFromInput).".";
	} else {
		include($emailTemplateDir.'output_emailFromModule_'.$_POST['email_template'].'/template.php');
	}
	$emailBodyData = str_replace(array("\n","\r","\t"),"",$emailBodyData);
	//print_r(sendEmail_extract_images($emailBodyData));
	?>
	<tr><td colspan="2" <?php /*?>style="border-bottom:1px solid #333333;"<?php */?>>
		<h3 style="margin-bottom:0;"><a href="#email_preview" class="fancyEmailPreview"><?php echo $formText_emailPreview_sendFromInput;?></a></h3>
		<div style="display:none;">
			<div id="email_preview">
			<div style="border-bottom:1px solid #e9e9e9; padding-bottom:10px; margin-bottom:20px;"><b><?php echo $emailSubjectData;?></b></div>
			<div style="background:#fff;"><?php echo $emailBodyData;?></div>
			</div>
		</div>
		<script type="text/javascript">
		$(document).ready(function () {
			$("a.fancyEmailPreview").fancybox({ 'mouseWheel' : false });
			<?php /*?>alert('<?=str_replace("'","\'",$emailBodyData);?>');<?php */?>
		});
		</script>
		<br><br>
	</td></tr>
	<?php /*?><tr><td colspan="2"><b><?=$emailSubjectData;?></b><br/><br/><br/></td></tr>
	<tr><td colspan="2"><?=$emailBodyData;?></td></tr>
	<tr>
		<td valign="top"><?=$formText_emailSubject_sendFromInput;?>:</td>
		<td><strong><?=$emailSubjectData;?></strong></td>
	</tr>
	<tr>
		<td valign="top"><?=$formText_emailBody_sendFromInput;?></td>
		<td><?=$emailBodyData;?></td>
	</tr><?php */?>
	<?php
}





?><tr><td colspan="2" style="border-bottom:1px solid #333333;"><h3 style="margin-bottom:0;"><?php if($_POST['step']<2) print $formText_configuration_sendFromInput;?></h3></td></tr><?php
if(!isset($_POST['step']) or $_POST['step']==0) // ***************************************** choose email template
{
	?><tr>
		<td><?php echo $formText_ChooseEmailTemplate_sendFromInput;?>:</td>
		<td><select name="email_template">
			<option value=""><?php echo $formText_Notification_sendFromInput;?></option><?php
			//output folders in module with "output_emailFromModule_[Name]" string.
			if($handle = opendir($emailTemplateDir)) 
			{
				while(false !== ($file = readdir($handle)))
				{
					if($file!="." and $file!=".." and is_dir($emailTemplateDir."/".$file))
					{
						if(strpos($file,"output_emailFromModule_")!==false)
						{
							$template = str_replace("output_emailFromModule_","",$file);
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
		<input type="hidden" name="session" value="<?php echo $sendEmail_session;?>">
		<div id="sendEmail_users">
			<div class="header"><a id="sendEmail_load_users" href="#sendEmail_upopup"><?php echo $formText_selectReceivers_fieldtype;?> +</a></div>
			<div><?php echo $formText_totalUsersSelected_sendFromInput;?>: <span id="sendEmail_total_users">0</span></div>
			<script type="text/javascript">
			var sendEmail_instance;
			var sendEmail_sources;
			var sendEmail_manual_selection_changed;
			var sendEmail_manual_source;
			var sendEmail_manual_source_config;
			var sendEmail_manual_page;
			var sendEmail_userlist_view;
			$(function() {
				$('#sendEmail_load_users').fancybox({ 'mouseWheel' : false, beforeClose: function() { return sendEmail_check_send(); } });
				$('#sendEmail_upopup input.pop-close').on('click', function() {
					if(sendEmail_instance) return;
					$.fancybox.close();
				});
				$('#sendEmail_mpopup input.pop-ok').on('click', function() {
					// Userlist OK button
					if(sendEmail_instance) return;
					
					if(sendEmail_userlist_view)
					{
						$.fancybox.close();
						sendEmail_userlist_view = false;
					} else {
						sendEmail_save_manual_selection('filter');
					}
				});
				$('#sendEmail_addpopup input.pop-ok').on('click', function() {
					// Custom add OK button
					if(sendEmail_instance) return;
					
					sendEmail_save_manual_add();
				});
				
				
				$('.sendEmail_contactset input').on('change',function() {
					if(sendEmail_instance) return;
					
					var _this = $(this);
					$(this).next('label').find('.sendEmail_contactsetedit').show();
					if($(_this).is('.import'))
					{
						sendEmail_instance_on();
						$(this).removeClass('import');
						$.ajax({
							type: 'POST',
							dataType: 'json',
							url: '<?php echo $extradir;?>/input/includes/ajax_sendEmail_users.php',
							cache: false,
							data: { action: 'import', source: $(this).val(), session: '<?php echo $sendEmail_session;?>', field: 'sendEmail', companyID : '<?php echo $_GET['companyID'];?>', removeunsubscribers: 1, choosenListInputLang: '<?php echo $choosenListInputLang;?>', caID: '<?php echo $_GET['caID'];?>' },
							success: function(data) {
								if(data.id)
								{
									$('#sendEmail_upopup .'+data.id+' .sendEmail_contactsetcount').text(' (<?php echo $formText_selected_fieldtype;?> '+data.selected+' <?php echo $formText_of_fieldtype;?> '+data.total+') ').css('font-weight','bold');
									$(_this).val(data.id);
									if(data.filter) $('#sendEmail_upopup .sendEmail_contactset.'+data.id).append(data.filter);
								}
								sendEmail_instance_off();
								$.fancybox.update();
							}
						});
					} else {
						if(this.checked) $(this).parent().find('.sendEmail_filter').show();
						else $(this).parent().find('.sendEmail_filter').hide();
						sendEmail_change_selection(this, $(this).val(), $(this).val(), 0, this.checked);
					}
				});
				$('.sendEmail_contactset .sendEmail_contactsetedit').on('click',function() {
					if(sendEmail_instance) return;
					
					sendEmail_manual_source = $(this).parent().prevAll('input.source').val();
					sendEmail_manual_source_config = $(this).attr('data-order') + ':' + $(this).attr('data-orderby');
					sendEmail_manual_selection_changed = false;
					$.fancybox.open({ href: '#sendEmail_mpopup', 'mouseWheel' : false, beforeClose: function() { return sendEmail_check_send(); } });
					sendEmail_show_userlist_page(0);
				});
				$('.sendEmail_contactset .sendEmail_contactsetadd').on('click',function() {
					if(sendEmail_instance) return;
					
					$(this).closest('.sendEmail_contactset').find('input.source').attr('checked',1);
					$('#sendEmail_addpopup .pop-data input').val('');
					$.fancybox.open({ href: '#sendEmail_addpopup', 'mouseWheel' : false, beforeClose: function() { return sendEmail_check_send(); } });
				});
				
				$('#sendEmail_total_users').on('click',function() {
					if(sendEmail_instance) return;
					
					sendEmail_userlist_view = true;
					sendEmail_manual_source = '';
					sendEmail_manual_source_config = '1,2:1';
					sendEmail_manual_selection_changed = false;
					$.fancybox.open({ href: '#sendEmail_mpopup', 'mouseWheel' : false, beforeClose: function() { return sendEmail_check_send(); } });
					sendEmail_show_userlist_page(0);
				});
				
				<?php if(isset($_GET['preload_session'])) { ?>
				$('.sendEmail_contactset.preload input.source').trigger('click');
				sendEmail_check_send();
				<?php } ?>
				
				// DO DB CLEANUP
				$.ajax({
					type: 'POST',
					url: '<?php echo $extradir;?>/input/includes/ajax_sendEmail_users.php',
					cache: false,
					data: { action: 'cleanup', caID: '<?php echo $_GET['caID'];?>' }
				});
			});
			
			function sendEmail_show_userlist_page(page)
			{
				if(!sendEmail_instance)
				{
					if(page != null) sendEmail_manual_page = page;
					if(sendEmail_manual_selection_changed && confirm('<?php echo $formText_changesWereMadeDoYouWantToSaveThem_fieldtype;?>?'))
					{
						sendEmail_save_manual_selection('page');
						return;
					}
					sendEmail_instance_on();
					sendEmail_manual_selection_changed = false;
					
					$.fancybox.showLoading();
					$.ajax({
						type: 'POST',
						dataType: 'json',
						url: '<?php echo $extradir;?>/input/includes/ajax_sendEmail_users.php',
						cache: false,
						data: { action: 'list', source: sendEmail_manual_source, session: '<?php echo $sendEmail_session;?>', field: 'sendEmail', sourceconfig: sendEmail_manual_source_config, page: sendEmail_manual_page, caID: '<?php echo $_GET['caID'];?>' },
						success: function(data) {
							$('#sendEmail_mpopup .pop-data').html(data.html);
							$.fancybox.update();
							sendEmail_instance_off();
						}
					});
				}
			}
			
			function sendEmail_save_manual_selection(callback)
			{
				if(!sendEmail_instance)
				{
					if(sendEmail_manual_selection_changed)
					{
						sendEmail_instance_on();
						var sendEmail_users_selected = new Array();
						var sendEmail_users_unselected = new Array();
						$('#sendEmail_mpopup .pop-data input:checked').each(function () { sendEmail_users_selected.push($(this).val()); });
						$('#sendEmail_mpopup .pop-data input:not(:checked)').each(function () { sendEmail_users_unselected.push($(this).val()); });
						
						$.ajax({
							type: 'POST',
							dataType: 'json',
							url: '<?php echo $extradir;?>/input/includes/ajax_sendEmail_users.php',
							cache: false,
							data: { action: 'manual_update', selected: sendEmail_users_selected, unselected: sendEmail_users_unselected, caID: '<?php echo $_GET['caID'];?>' },
							success: function(data) {
								if(data.total)
								{
									var _bold = 'normal';
									if(parseInt(data.selected)>0) _bold = 'bold';
									$('#sendEmail_upopup .sendEmail_contactset.'+sendEmail_manual_source+' .sendEmail_filter').hide();
									$('#sendEmail_upopup .sendEmail_contactset.'+sendEmail_manual_source+' input:checked').prop('checked',false);
									$('#sendEmail_upopup .'+sendEmail_manual_source+' .sendEmail_contactsetcount').text(' (<?php echo $formText_manualUserSelection_fieldtype;?> '+data.selected+' <?php echo $formText_of_fieldtype;?> '+data.total+') ').css('font-weight',_bold);
								} else {
									alert('<?php echo $formText_errorOccured_fieldtype;?>');
								}
								sendEmail_manual_selection_changed = false;
								sendEmail_instance_off();
								
								if(callback == 'page') sendEmail_show_userlist_page();
								else if(callback == 'filter') sendEmail_back_to_filter();
							}
						});
					} else {
						if(callback == 'page') sendEmail_show_userlist_page();
						else if(callback == 'filter') sendEmail_back_to_filter();
					}
				}
			}
			
			function sendEmail_save_manual_add()
			{
				if(!sendEmail_instance)
				{
					sendEmail_instance_on();
					$.ajax({
						type: 'POST',
						dataType: 'json',
						url: '<?php echo $extradir;?>/input/includes/ajax_sendEmail_users.php',
						cache: false,
						data: { action: 'manual_add', session: '<?php echo $sendEmail_session;?>', caID: '<?php echo $_GET['caID'];?>', name: $('#sendEmail_addpopup .pop-data input.name').val(), email: $('#sendEmail_addpopup .pop-data input.email').val() },
						success: function(data) {
							if(data.id)
							{
								$('#sendEmail_upopup .'+data.id).find('.sendEmail_contactsetedit').show();
								$('#sendEmail_upopup .'+data.id+' .sendEmail_contactsetcount').text(' (<?php echo $formText_selected_fieldtype;?> '+data.selected+' <?php echo $formText_of_fieldtype;?> '+data.total+') ').css('font-weight','bold');
								if(data.filter) $('#sendEmail_upopup .sendEmail_contactset.'+data.id).append(data.filter);
							}
							sendEmail_instance_off();
							sendEmail_back_to_filter();
						}
					});
				}
			}
			
			function sendEmail_change_selection(_this, _source, changeSource, filterId, checked)
			{
				if(!sendEmail_instance)
				{
					sendEmail_instance_on();
					sendEmail_update_selection(_this, checked);
					if(checked) checked = 1; else checked = 0;
					
					$.fancybox.showLoading();
					$.ajax({
						type: 'POST',
						dataType: 'json',
						url: '<?php echo $extradir;?>/input/includes/ajax_sendEmail_users.php',
						cache: false,
						data: { action: 'change_selection', source: _source, changeSource: changeSource, filterId: filterId, session: '<?php echo $sendEmail_session;?>', checked: checked, caID: '<?php echo $_GET['caID'];?>' },
						success: function(data) {
							var _bold = 'normal';
							if(parseInt(data.selected)>0) _bold = 'bold';
							$('#sendEmail_upopup .'+_source+' .sendEmail_contactsetcount').text(' (<?php echo $formText_selected_fieldtype;?> '+data.selected+' <?php echo $formText_of_fieldtype;?> '+data.total+') ').css('font-weight',_bold);
							sendEmail_instance_off();
						}
					});
				} else {
					$(_this).prop('checked',!checked);
				}
			}
			
			function sendEmail_update_selection(_this, checked)
			{	
				$(_this).nextAll('.sendEmail_filter').find('input').prop('checked',checked);
				if($(_this).not('.source'))
				{
					$(_this).parentsUntil('.setcontainer', '.sendEmail_filter').each(function() {
						if(checked)
						{
							if($(this).find('.sendEmail_filter').children('input:not(:checked)').length == 0)
							{
								$(this).children('input:not(:checked)').prop('checked',checked);
							}
						} else {
							$(this).children('input:checked').prop('checked',checked);
						}
					});
				}
				
			}
			
			function sendEmail_back_to_filter()
			{
				$.fancybox.open({href: '#sendEmail_upopup', 'mouseWheel' : false, beforeClose: function() { return sendEmail_check_send(); } });
			}
			
			function sendEmail_user_change()
			{
				if(!sendEmail_instance)
				{
					sendEmail_manual_selection_changed = true;
				} else {
					$(_this).prop('checked',!checked);
				}
			}
			
			function sendEmail_check_send()
			{
				if(sendEmail_instance) return false;
				$.fancybox.showLoading();
				$.ajax({
					type: 'POST',
					async: false,
					dataType: 'json',
					url: '<?php echo $extradir;?>/input/includes/ajax_sendEmail_users.php',
					cache: false,
					data: { action: 'check_send', session: '<?php echo $sendEmail_session;?>', caID: '<?php echo $_GET['caID'];?>' },
					timeout: 1000,
					error: function(){
						$.fancybox.hideLoading();
						return false;
					},
					success: function(data) {
						$('#sendEmail_total_users').text(data.total);
						if(parseInt(data.total)>0)
							$('#next-btn').attr('disabled',false);
						else
							$('#next-btn').attr('disabled',true);
						$.fancybox.hideLoading();
						return true;
					}
				});
			}
			function sendEmail_instance_on()
			{
				sendEmail_instance = true;
				$.fancybox.showLoading();
				$('#sendEmail_upopup input').attr('disabled',true);
				$('#sendEmail_mpopup input').attr('disabled',true);
			}
			function sendEmail_instance_off()
			{
				$.fancybox.hideLoading();
				$('#sendEmail_upopup input').attr('disabled',false);
				$('#sendEmail_mpopup input').attr('disabled',false);
				sendEmail_instance = false;
			}
			</script>
			<div style="display:none;">
				<div id="sendEmail_upopup">
					<h3><center><?php echo $formText_selectReceivers_fieldtype;?></center></h3>
					<div class="pop-filter">
						<?php
						$uniqueSource = array();
						$sources = json_decode(stripslashes($sendEmailUserSource),true);
						
						if($sendEmailActivateCustomUsers == 1)
						{
							?><div class="setcontainer"><div class="sendEmail_contactset sendEmail_filter preload"><input type="checkbox" class="source" name="sendEmail_contactset[]" value="preload"><label><?php echo $formText_CustomUserlist_fieldtype;?><span class="sendEmail_contactsetcount"></span>&nbsp;&nbsp;&nbsp;&nbsp;<span class="sendEmail_contactsetadd"><?php echo $formText_Add_fieldtype;?></span>&nbsp;&nbsp;&nbsp;&nbsp;<span class="sendEmail_contactsetedit" data-order="1,2" data-orderby="1"><?php echo $formText_editUserList_fieldtype;?></span></label></div></div><?php
						}
						if($sendEmailActivateGetynetUsers == 1)
						{
							?><div class="setcontainer"><div class="sendEmail_contactset sendEmail_filter getynet"><input type="checkbox" class="source import" name="sendEmail_contactset[]" value="getynet"><label><?php echo $formText_getynetUsers_fieldtype;?><span class="sendEmail_contactsetcount"></span>&nbsp;&nbsp;&nbsp;&nbsp;<span class="sendEmail_contactsetedit" data-order="1,2" data-orderby="1"><?php echo $formText_editUserList_fieldtype;?></span></label></div></div><?php
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
							?><div class="setcontainer"><div class="sendEmail_contactset sendEmail_filter <?php echo $source;?>"><input type="checkbox" class="source import" name="sendEmail_contactset[]" value="<?php echo $source.':'.$item;?>"><label><?php echo $vSource[1];?><span class="sendEmail_contactsetcount"></span>&nbsp;&nbsp;&nbsp;&nbsp;<span class="sendEmail_contactsetedit" data-order="<?php echo $vSource[6];?>" data-orderby="<?php echo $vSource[7];?>"><?php echo $formText_editUserList_fieldtype;?></span></label></div></div><?php
						}
						?>
					</div>
					<div class="pop-btns"><input class="pop-close" type="button" value="<?php echo $formText_Ok_fieldtype;?>">&nbsp;&nbsp;&nbsp;&nbsp;</div>
				</div>
				<div id="sendEmail_mpopup">
					<h3><center><?php echo $formText_selectReceivers_fieldtype;?></center></h3>
					<div class="pop-data"></div>
					<div class="pop-btns"><input class="pop-ok" type="button" value="<?php echo $formText_Ok_fieldtype;?>"></div>
				</div>
				<div id="sendEmail_addpopup">
					<h3><center><?php echo $formText_AddUser_fieldtype;?></center></h3>
					<div class="pop-data">
						<div><label><?php echo $formText_Name_fieldtype;?></label><input class="name" type="text" name="name" value="" /></div>
						<div><label><?php echo $formText_Email_fieldtype;?></label><input class="email" type="text" name="email" value="" /></div>
					</div>
					<div class="pop-btns"><input class="pop-ok" type="button" value="<?php echo $formText_Add_fieldtype;?>"></div>
				</div>
			</div>
		</div>
		</td>
	</tr>
	<tr><td colspan="2">
		<?php if(!isset($sendEmailTemplate)) { ?><input type="button" onClick="javascript: $('#hidden_step').val(0); $('#mailingForm').submit();" value="<?php print($formText_Back_sendFromInput);?>"><?php } ?>
		<input type="button" onClick="javascript: $('#hidden_step').val(1); $('#hidden_myself').attr('name','sendToMySelf').val('1'); $('#mailingForm').submit();" value="<?php echo $formText_sendTestToMySelf_sendFromInput;?>">
		<input type="hidden" id="hidden_myself" name="_hidden" value="">
		<input id="next-btn" type="submit" name="send" onClick="javascript: if(!confirm('<?php echo $formText_AreYouSureYouWantToSendThisEmailToSelectedUsers_sendFromInput;?>?')) return false;" value="<?php echo $formText_Send_sendFromInput;?>" disabled>
		<input type="hidden" id="hidden_step" name="step" value="2">
	</td></tr><?php
} else if($_POST['step'] == 2) { // ************************************ EMAILS ADDED TO QUEUE - DONE
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


$sendFromInputSuccess=0;  
$sendFromInputFailed=0;
if($_POST['sendToMySelf']=='1')
{
	$i=0;
	$imgAttach = array();
	$imgReplace = sendEmail_extract_images($emailBodyData);
	$fileAttach = sendEmail_extract_attachments($emailBodyData);
	foreach($imgReplace as $image)
	{
		if(is_file(__DIR__.'/../../../../'.str_replace(array('accounts/'.$accountname.'/','/accounts/'.$accountname.'/'),'',$image)))
		{
			$emailBodyData = str_replace($image, 'cid:img'.$i, $emailBodyData);
			$imgAttach[] = $image;
			$i++;
		}
	}
	if(count($fileAttach)>0)
	{
		$dom = new DOMDocument;
		$dom->loadHTML($emailBodyData);
		$xPath = new DOMXPath($dom);
		$nodes = $xPath->query('//*[@class="email-attachment"]');
		foreach($nodes as $key=>$node)
		{
			$nodes->item($key)->parentNode->removeChild($nodes->item($key));
		}
		$emailBodyData = $dom->saveHTML();
	}
	
	$currentUser = json_decode(APIconnectUser("userinfoget", $variables->loggID, $variables->sessionID, array('SEARCH_USERNAME'=>$variables->loggID,'COMPANY_ID'=>$companyID)),true);
	
	$mail = new PHPMailer;
	$mail->CharSet	= 'UTF-8';
	if($v_email_server_config['host'] != "")
	{
		$mail->Host	= $v_email_server_config['host'];
		if($v_email_server_config['port'] != "") $mail->Port = $v_email_server_config['port'];
		
		if($v_email_server_config['username'] != "" and $v_email_server_config['password'] != "")
		{
			$mail->SMTPAuth	= true;
			$mail->Username	= $v_email_server_config['username'];
			$mail->Password	= $v_email_server_config['password'];

		}
	} else {
		$mail->Host = "mail.dcode.no";
	}
	$mail->IsSMTP(true);
	$mail->From		= $variables->loggID;
	$mail->FromName	= /*utf8_decode !!!! WE SEND UTF-8 EMAILS NOT ISO-8859-1 !!!*/(html_entity_decode($currentUser['data']['name'] != "" ? $currentUser['data']['name'] : $emailCompanyName.$emailAccountName));
	$mail->Subject	= html_entity_decode($emailSubjectData, ENT_QUOTES, 'UTF-8');
	$mail->Body		= $emailBodyData;
	$mail->isHTML(true);
	$mail->AddAddress($variables->loggID);
	foreach($imgAttach as $key => $attach)
	{
		$mail->AddEmbeddedImage(__DIR__.'/../../../../'.str_replace(array('accounts/'.$accountname.'/','/accounts/'.$accountname.'/'),'',$attach), 'img'.$key);
	}
	foreach($fileAttach as $key => $attach)
	{
		if(is_file(__DIR__.'/../../../../'.str_replace(array('accounts/'.$accountname.'/','/accounts/'.$accountname.'/'),'',$attach)))
		{
			$mail->AddAttachment(__DIR__.'/../../../../'.str_replace(array('accounts/'.$accountname.'/','/accounts/'.$accountname.'/'),'',$attach));
		}
	}
	
	if($mail->Send())
	{
		$sendFromInputSuccess++;
	} else {
		$sendFromInputFailed++;
	}
}


if($_POST['step'] == 2) { // ************************************ send emails
	if($_SESSION['caID_'.$_GET['caID']]['POST_ID'] != $_POST['POST_ID'])
	{
		$s_email_time = date("d-m-Y H:i");
		$emailBodyData = str_replace("SYS_EMAIL_SEND_ID",$emailSentID,$emailBodyData);
		//$emailBodyData = str_replace("SYS_EMAIL_UNSUBSCRIBELIST_ID",$v_email_data['unsubscribe_to_list'],$emailBodyData);
		$s_sql = "UPDATE sys_emailsend SET send_on = STR_TO_DATE('".$s_email_time."','%d-%m-%Y %H:%i'), subject = ?, text = ? WHERE id = ?";
		$o_main->db->query($s_sql, array($emailSubjectData, $emailBodyData, $emailSentID));
		
		$o_query = $o_main->db->query("select * from sys_emailsend_userlist where session = ? and selected = ? and disabled = ? group by email", array($_POST['session'], 1, 0));
		if($o_query && $o_query->num_rows() > 0)
		{
			foreach($o_query->result_array() as $v_row)
			{
				$s_sql = "INSERT INTO sys_emailsendto (id, emailsend_id, receiver, receiver_email, `status`, status_message, perform_time, perform_count) VALUES (NULL, ?, ?, ?, 0, '', NULL, 0)";
				$o_main->db->query($s_sql, array($emailSentID, $v_row['name'], $v_row['email']));
			}
		}
		
		if(!function_exists("APIconnectAccount")) include_once(__DIR__."/APIconnect.php");
		$s_response = APIconnectAccount("cronjobtaskcreate", $accountinfo['accountname'], $accountinfo['password'], array('TYPE'=>'email', 'TIME'=>date('YmdHi',strtotime($s_email_time)), 'DATA'=>array('l_emailsend_id'=>$emailSentID)));
		
		$o_main->db->query("delete from sys_emailsend_userlist where session = ?", array($_POST['session']));
		$o_main->db->query("delete ur.* from sys_emailsend_userrelation ur join sys_emailsend_userfilter uf on uf.id = ur.userfilterID where uf.session = ?", array($_POST['session']));
		$o_main->db->query("delete from sys_emailsend_userfilter where session = ?", array($_POST['session']));
		$o_main->db->query("delete from sys_emailsend_userlistexpire where session = ?", array($_POST['session']));
		
		$_SESSION['caID_'.$_GET['caID']]['POST_ID'] = $_POST['POST_ID'];
	} else {
		print "<h3>".$formText_RequestWasProcessedAlready_sendFromInput."</h3>";
	}
}

if($sendFromInputSuccess > 0 or $sendFromInputFailed > 0) print "<h3>".($formText_sendingWasSuccessfullTo_sendFromInput." $sendFromInputSuccess ".$formText_of_fieldtype." ".($sendFromInputFailed+$sendFromInputSuccess))."</h3>";

if(sizeof($inserted) > 0) print "<h3>".sizeof($inserted).' '.$formText_emailsWasAddedToSendingQueue_sendFromInput."</h3>";

/*
** REPORT
*/
$s_sql = "select * from sys_emailsend where content_id = ? and content_table = ? and content_module_id = ? and type = 2 order by send_on DESC";
$o_query = $o_main->db->query($s_sql, array($_GET['ID'], $templateTable, $moduleID));
if($o_query && $o_query->num_rows() > 0)
{
	?><div class="report">
	<table border="0" width="100%" cellpadding="0" cellspacing="0">
	<tr><td colspan="4"><h3><?php echo $formText_SentItems_sendFromInput;?></h3></td></tr>
	<tr class="title">
		<td><?php echo $formText_created_sendFromInput;?></td>
		<td><?php echo $formText_subject_sendFromInput;?></td>
		<td></td>
		<td></td>
	</tr>
	<?php
	foreach($o_query->result_array() as $v_row)
	{
		$v_row['send_on'] = date('d-m-Y H:i',strtotime($v_row['send_on']));
		?>
		<tr class="item">
			<td class="date"><?php echo $v_row['send_on'];?></td>
			<td class="subject"><?php echo $v_row['subject'];?></td>
			<td class="link"><a href="javascript:;" onClick="show_email_input(<?php echo "'".$_GET['ID']."', '".$templateTable."', '".$moduleID."', '".$v_row['send_on']."'";?>);"><?php /*?><img border="0" src="<?=$extradir;?>/input/includes/images/document.png" align="texttop" /> <?php */?><?php echo $formText_showEmail_sendFromInput;?></a></td>
			<td class="link"><a href="javascript:;" onClick="show_report_input(<?php echo "'".$_GET['ID']."', '".$templateTable."', '".$moduleID."', '".$v_row['send_on']."'";?>);"><?php /*?><img border="0" src="<?=$extradir;?>/input/includes/images/document.png" align="texttop" /> <?php */?><?php echo $formText_showReport_sendFromInput;?></a></td>
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
			url: '<?php echo $extradir;?>/input/fieldtypes/ReminderEmail/ajax_report.php',
			cache: false,
			data: { field_ui_id: 'sendEmail', id : id, table: table, moduleid: moduleid, time: time, dir: '<?php echo $extradir;?>/input/fieldtypes/ReminderEmail/', languageID: '<?php echo $choosenListInputLang;?>', type: 2, page: page, caID: '<?php echo $_GET['caID'];?>' },
			success: function(data) {
				$.fancybox('<h2><?php echo $formText_ReportFor_sendFromInput.': ';?>' + time + '</h2>' + data, { 'mouseWheel': false });
			}
		});
	}
	function show_email_input(id, table, moduleid, time)
	{
		$.fancybox.showLoading();
		$.ajax({
			type: 'POST',
			url: '<?php echo $extradir;?>/input/fieldtypes/ReminderEmail/ajax_preview.php',
			cache: false,
			data: { field_ui_id: 'sendEmail', id : id, table: table, moduleid: moduleid, time: time, dir: '<?php echo $extradir;?>/input/fieldtypes/ReminderEmail/', languageID: '<?php echo $choosenListInputLang;?>', type: 2, caID: '<?php echo $_GET['caID'];?>' },
			success: function(data) {
				$.fancybox(data, { 'mouseWheel': false });
			}
		});
	}
	</script>
	</div><?php
}

?>
<style>
#next-btn { float:right; }

#sendEmail_users { margin:20px 0; padding-left:20px; }
#sendEmail_users .header { font-size:13px; font-weight:bold; line-height:26px; }
#sendEmail_users .header a { color:#000066; text-decoration:none; }
#sendEmail_total_users { font-size:13px; font-weight:bold; cursor:pointer; }

.sendEmail_contactset { margin:3px 0; padding:2px 0; }
.sendEmail_contactset:hover { background-color:#e9e9e9; }
.sendEmail_contactsetedit, .sendEmail_contactsetadd { cursor:pointer; color:#000066; text-decoration:none; font-weight:bold; }
.sendEmail_contactsetedit { display:none; }
.sendEmail_filter.filter { padding-left:30px; }
.sendEmail_filter.filter label { cursor:pointer; }

#sendEmail_upopup { min-width:500px; min-height:500px; }
#sendEmail_mpopup { min-width:900px; min-height:500px; }
#sendEmail_mpopup .pop-data .item { white-space:nowrap; }
#sendEmail_mpopup .pop-data .item:nth-child(even) { background-color:#efefef; }
#sendEmail_mpopup .pop-data .item:hover { background-color:#88ddff; }
#sendEmail_mpopup .pop-data .item td { cursor:pointer; padding:2px 10px 2px 0; line-height:19px; }
#sendEmail_mpopup .pop-data .item td.list_checkbox { cursor:inherit; width:20px; }
#sendEmail_upopup .pop-btns, #sendEmail_mpopup .pop-btns, #sendEmail_addpopup .pop-btns, #sendEmail_mpopup .paging { text-align:center; margin-top:20px; }
#sendEmail_mpopup .paging a { font-size:12px; padding:0 3px; color:#666666; text-decoration:none; }
#sendEmail_mpopup .paging a:hover, #sendEmail_mpopup .paging a.active { color:#000066; font-weight:bold; }
#sendEmail_addpopup { min-width:500px; min-height:100px; }
#sendEmail_addpopup .pop-data div { padding-bottom:5px; }
#sendEmail_addpopup .pop-data label { display:inline-block; width:25%; margin-right:2%; text-align:right; }
#sendEmail_addpopup .pop-data input { width:50%; }

.report { margin:20px 0 20px 5px; }
.report tr.title td { padding-bottom:5px; font-weight:bold; border-bottom:1px solid #333333; }
.report tr.item:nth-child(even) { background-color:#efefef; }
.report tr.item:hover { background-color:#88ddff; }
.report td { vertical-align:top; padding:3px 0px; }
.report td.name { width:20%; }
.report td.subject { width:53%; }
.report td.link { text-align:right; padding-right:1%; width:13%; }
.report td.link a { color:#000066; text-decoration:none; }

.sendEmail_sumarize { padding:5px; font-size:13px; font-weight:bold; }
.sendEmail_paging { padding:5px; text-align:center; }
.sendEmail_paging a { color:#000066; text-decoration:none; }
.sendEmail_report, .sendEmail_head { min-width:900px; padding:2px 5px; }
.sendEmail_head { background-color:#66bbdd; }
.sendEmail_report:nth-child(even) { background-color:#efefef; }
.sendEmail_report:hover { background-color:#88ddff; }
.sendEmail_report span.email, .sendEmail_head span.email { width:20%; display:inline-block; }
.sendEmail_report span.name, .sendEmail_head span.name { width:25%; display:inline-block; }
.sendEmail_report span.performed, .sendEmail_head span.performed { width:20%; display:inline-block; }
.sendEmail_report span.status, .sendEmail_head span.status { width:15%; display:inline-block; }
.sendEmail_report span.count, .sendEmail_head span.count { width:10%; display:inline-block; }
</style>