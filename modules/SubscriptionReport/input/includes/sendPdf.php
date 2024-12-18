<?php
$accountinfo = array();
$o_query = $o_main->db->query('SELECT * FROM accountinfo');
if($o_query && $o_query->num_rows()>0) $accountinfo = $o_query->row_array();

if(strlen($variables->loggID)==0)
{
	print ("Module works from Getynet!");
	return;
}
if(!is_file(__DIR__."/../../../../lib/tcpdf/tcpdf.php"))
{
	print ("Configuration error: TCPDF library is missing");
	return;
}
if(!function_exists("APIconnectUser")) include("APIconnect.php");
if(!function_exists("devide_by_uppercase")) include("fnctn_devide_by_upercase.php");
if(!function_exists("get_curent_GET_params")) include('fnctn_get_curent_GET_params.php');

$currentParams=get_curent_GET_params(array('action'));
$currentUserEmail=$variables->loggID;
$templateDir = __DIR__."/../../";

if(!$o_main->db->table_exists('sys_pdfsend'))
{
	$o_main->db->simple_query("CREATE TABLE sys_pdfsend (
		id INT(11) NOT NULL AUTO_INCREMENT,
		`type` TINYINT(4) NOT NULL DEFAULT '1',
		created TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
		sender CHAR(100) NOT NULL,
		sender_email CHAR(100) NOT NULL,
		content_moduleID INT(11) NOT NULL,
		contentID INT(11) NOT NULL,
		content_table CHAR(50) NOT NULL,
		link VARCHAR(1000) NOT NULL,
		PRIMARY KEY (id),
		INDEX content (contentID, content_table, content_moduleID)
	)");
}
if(!$o_main->db->table_exists('sys_pdfsend_userlist'))
{
	$o_main->db->simple_query("CREATE TABLE IF NOT EXISTS sys_pdfsend_userlist (
		id INT(11) NOT NULL AUTO_INCREMENT,
		session CHAR(13) NOT NULL DEFAULT '',
		source CHAR(50) NOT NULL DEFAULT '',
		name CHAR(128) NOT NULL DEFAULT '',
		extra1 CHAR(255) NOT NULL DEFAULT '',
		extra2 CHAR(255) NOT NULL DEFAULT '',
		extra3 CHAR(255) NOT NULL DEFAULT '',
		extra4 CHAR(255) NOT NULL DEFAULT '',
		extra5 CHAR(255) NOT NULL DEFAULT '',
		extra6 CHAR(255) NOT NULL DEFAULT '',
		extra7 CHAR(255) NOT NULL DEFAULT '',
		extra8 CHAR(255) NOT NULL DEFAULT '',
		extra9 CHAR(255) NOT NULL DEFAULT '',
		extra10 CHAR(255) NOT NULL DEFAULT '',
		text varchar(1000) NOT NULL DEFAULT '',
		selected tinyint(1) NOT NULL DEFAULT 1,
		disabled tinyint(1) NOT NULL DEFAULT 0,
		origID INT(11) NULL,
		origgroupID TINYINT(4) NULL DEFAULT NULL,
		PRIMARY KEY (id),
		INDEX idx1 (session, source, name),
		INDEX idx2 (origID)
	)");
	$o_main->db->simple_query("CREATE TABLE IF NOT EXISTS sys_pdfsend_userrelation (
		userfilterID INT(11) NOT NULL,
		userlistID INT(11) NOT NULL,
		INDEX idx1 (userfilterID, userlistID)
	)");
	$o_main->db->simple_query("CREATE TABLE IF NOT EXISTS sys_pdfsend_userfilter (
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
	$o_main->db->simple_query("CREATE TABLE IF NOT EXISTS sys_pdfsend_userlistexpire (
		session CHAR(13) NOT NULL DEFAULT '',
		created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
		UNIQUE INDEX idx1 (session)
	)");
}

if(!isset($_POST['step']) && isset($sendPdfTemplate) && !empty($sendPdfTemplate))
{
	if($sendPdfTemplate == 'default_notify')
	{
		$_POST['template'] = "";
	} else {
		$_POST['template'] = $sendPdfTemplate;
	}
	$_POST['step'] = 1;
}
if($_POST['step']==1)
{
	// Get preloaded users
	if(isset($_GET['preload_session']))
	{
		$sendPdf_session = $_GET['preload_session'];
		$o_query = $o_main->db->query('select session from sys_pdfsend_userlistexpire where session = ?', array($sendPdf_session));
		if(!$o_query || ($o_query && $o_query->num_rows()==0))
		{
			unset($sendPdf_session);
		}
	}
	
	if(!isset($sendPdf_session))
	{
		$sendPdf_session = uniqid();
		$s_sql = 'select session from sys_pdfsend_userlistexpire where session = ?';
		while($o_query = $o_main->db->query($s_sql, array($sendPdf_session)) && $o_query && $o_query->num_rows()>0)
		{
			$sendPdf_session = uniqid();
		}
		if(!$o_main->db->query("insert into sys_pdfsend_userlistexpire(session, created) values(?, NOW())", array($sendPdf_session)))
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
	if(!isset($sendPdfTemplate)) { ?>
	<tr>
		<td valign="top"><?php echo $formText_emailTemplate_sendFromInput;?>:</td>
		<td><i><?php echo ($_POST['template']=='' ? $formText_Notification_sendFromInput : devide_by_uppercase($_POST['template']));?></i></td>
	</tr>
	<?php
	}
	?><input type="hidden" value="<?php echo $_POST['template'];?>" name="template"><?php
	$templateTable = explode(":",$mysqlTableName[0]);
	$templateTable = $templateTable[0];
	$templateID = $_GET['ID'];
	$templateLanguageID = $s_default_output_language;
}





?><tr><td colspan="2" style="border-bottom:1px solid #333333;"><h3 style="margin-bottom:0;"><?php if($_POST['step']<2) print $formText_configuration_sendFromInput;?></h3></td></tr><?php
if(!isset($_POST['step']) or $_POST['step']==0) // ***************************************** choose email template
{
	?><tr>
		<td><?php echo $formText_ChooseEmailTemplate_sendFromInput;?>:</td>
		<td><select name="template">
			<option value=""><?php echo $formText_Notification_sendFromInput;?></option><?php
			//output folders in module with "output_pdfFromModule_[Name]" string.
			if($handle = opendir($templateDir)) 
			{
				while(false !== ($file = readdir($handle)))
				{
					if($file!="." and $file!=".." and is_dir($templateDir."/".$file))
					{
						if(strpos($file,"output_pdfFromModule_")!==false)
						{
							$template = str_replace("output_pdfFromModule_","",$file);
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
		<input type="hidden" name="session" value="<?php echo $sendPdf_session;?>">
		<div id="sendPdf_users">
			<div class="header"><a id="sendPdf_load_users" href="#sendPdf_upopup"><?php echo $formText_selectReceivers_fieldtype;?> +</a></div>
			<div><?php echo $formText_totalUsersSelected_sendFromInput;?>: <span id="sendPdf_total_users">0</span></div>
			<script type="text/javascript">
			var sendPdf_instance;
			var sendPdf_sources;
			var sendPdf_manual_selection_changed;
			var sendPdf_manual_source;
			var sendPdf_manual_source_config;
			var sendPdf_manual_page;
			var sendPdf_userlist_view;
			$(function() {
				$('#sendPdf_load_users').fancybox({ 'mouseWheel' : false, beforeClose: function() { return sendPdf_check_send(); } });
				$('#sendPdf_upopup input.pop-close').on('click', function() {
					if(sendPdf_instance) return;
					$.fancybox.close();
				});
				$('#sendPdf_mpopup input.pop-ok').on('click', function() {
					// Userlist OK button
					if(sendPdf_instance) return;
					
					if(sendPdf_userlist_view)
					{
						$.fancybox.close();
						sendPdf_userlist_view = false;
					} else {
						sendPdf_save_manual_selection('filter');
					}
				});
				$('#sendPdf_addpopup input.pop-ok').on('click', function() {
					// Custom add OK button
					if(sendPdf_instance) return;
					
					sendPdf_save_manual_add();
				});
				
				$('.sendPdf_contactset input').on('change',function() {
					if(sendPdf_instance) return;
					
					var _this = $(this);
					$(this).next('label').find('.sendPdf_contactsetedit').show();
					if($(_this).is('.import'))
					{
						sendPdf_instance_on();
						$(this).removeClass('import');
						$.ajax({
							type: 'POST',
							dataType: 'json',
							url: '<?php echo $extradir;?>/input/includes/ajax_sendPdf_users.php',
							cache: false,
							data: { action: 'import', source: $(this).val(), session: '<?php echo $sendPdf_session;?>', field: 'sendPdf', companyID : '<?php echo $_GET['companyID'];?>', removeunsubscribers: 1, choosenListInputLang: '<?php echo $choosenListInputLang;?>', caID: '<?php echo $_GET['caID'];?>' },
							success: function(data) {
								if(data.id)
								{
									$('#sendPdf_upopup .'+data.id+' .sendPdf_contactsetcount').text(' (<?php echo $formText_selected_fieldtype;?> '+data.selected+' <?php echo $formText_of_fieldtype;?> '+data.total+') ').css('font-weight','bold');
									$(_this).val(data.id);
									if(data.filter) $('#sendPdf_upopup .sendPdf_contactset.'+data.id).append(data.filter);
								}
								sendPdf_instance_off();
								$.fancybox.update();
							}
						});
					} else {
						if(this.checked) $(this).parent().find('.sendPdf_filter').show();
						else $(this).parent().find('.sendPdf_filter').hide();
						sendPdf_change_selection(this, $(this).val(), $(this).val(), 0, this.checked);
					}
				});
				$('.sendPdf_contactset .sendPdf_contactsetedit').on('click',function() {
					if(sendPdf_instance) return;
					
					sendPdf_manual_source = $(this).parent().prevAll('input.source').val();
					sendPdf_manual_source_config = $(this).attr('data-order') + ':' + $(this).attr('data-orderby');
					sendPdf_manual_selection_changed = false;
					$.fancybox.open({ href: '#sendPdf_mpopup', 'mouseWheel' : false, beforeClose: function() { return sendPdf_check_send(); } });
					sendPdf_show_userlist_page(0);
				});
				$('.sendPdf_contactset .sendPdf_contactsetadd').on('click',function() {
					if(sendPdf_instance) return;
					
					$(this).closest('.sendPdf_contactset').find('input.source').attr('checked',1);
					$('#sendPdf_addpopup .pop-data input').val('');
					$.fancybox.open({ href: '#sendPdf_addpopup', 'mouseWheel' : false, beforeClose: function() { return sendPdf_check_send(); } });
				});
				
				$('#sendPdf_total_users').on('click',function() {
					if(sendPdf_instance) return;
					
					sendPdf_userlist_view = true;
					sendPdf_manual_source = '';
					sendPdf_manual_source_config = '1,2:1';
					sendPdf_manual_selection_changed = false;
					$.fancybox.open({ href: '#sendPdf_mpopup', 'mouseWheel' : false, beforeClose: function() { return sendPdf_check_send(); } });
					sendPdf_show_userlist_page(0);
				});
				
				<?php if(isset($_GET['preload_session'])) { ?>
				$('.sendPdf_contactset.preload input.source').trigger('click');
				sendPdf_check_send();
				<?php } ?>
				
				// DO DB CLEANUP
				$.ajax({
					type: 'POST',
					url: '<?php echo $extradir;?>/input/includes/ajax_sendPdf_users.php',
					cache: false,
					data: { action: 'cleanup', caID: '<?php echo $_GET['caID'];?>' }
				});
				
			});
			
			function sendPdf_show_userlist_page(page)
			{
				if(!sendPdf_instance)
				{
					if(page != null) sendPdf_manual_page = page;
					if(sendPdf_manual_selection_changed && confirm('<?php echo $formText_changesWereMadeDoYouWantToSaveThem_fieldtype;?>?'))
					{
						sendPdf_save_manual_selection('page');
						return;
					}
					sendPdf_instance_on();
					sendPdf_manual_selection_changed = false;
					
					$.fancybox.showLoading();
					$.ajax({
						type: 'POST',
						dataType: 'json',
						url: '<?php echo $extradir;?>/input/includes/ajax_sendPdf_users.php',
						cache: false,
						data: { action: 'list', source: sendPdf_manual_source, session: '<?php echo $sendPdf_session;?>', field: 'sendPdf', sourceconfig: sendPdf_manual_source_config, page: sendPdf_manual_page, caID: '<?php echo $_GET['caID'];?>' },
						success: function(data) {
							$('#sendPdf_mpopup .pop-data').html(data.html);
							$.fancybox.update();
							sendPdf_instance_off();
						}
					});
				}
			}
			
			function sendPdf_save_manual_selection(callback)
			{
				if(!sendPdf_instance)
				{
					if(sendPdf_manual_selection_changed)
					{
						sendPdf_instance_on();
						var sendPdf_users_selected = new Array();
						var sendPdf_users_unselected = new Array();
						$('#sendPdf_mpopup .pop-data input:checked').each(function () { sendPdf_users_selected.push($(this).val()); });
						$('#sendPdf_mpopup .pop-data input:not(:checked)').each(function () { sendPdf_users_unselected.push($(this).val()); });
						
						$.ajax({
							type: 'POST',
							dataType: 'json',
							url: '<?php echo $extradir;?>/input/includes/ajax_sendPdf_users.php',
							cache: false,
							data: { action: 'manual_update', selected: sendPdf_users_selected, unselected: sendPdf_users_unselected, caID: '<?php echo $_GET['caID'];?>' },
							success: function(data) {
								if(data.total)
								{
									var _bold = 'normal';
									if(parseInt(data.selected)>0) _bold = 'bold';
									$('#sendPdf_upopup .sendPdf_contactset.'+sendPdf_manual_source+' .sendPdf_filter').hide();
									$('#sendPdf_upopup .sendPdf_contactset.'+sendPdf_manual_source+' input:checked').prop('checked',false);
									$('#sendPdf_upopup .'+sendPdf_manual_source+' .sendPdf_contactsetcount').text(' (<?php echo $formText_manualUserSelection_fieldtype;?> '+data.selected+' <?php echo $formText_of_fieldtype;?> '+data.total+') ').css('font-weight',_bold);
								} else {
									alert('<?php echo $formText_errorOccured_fieldtype;?>');
								}
								sendPdf_manual_selection_changed = false;
								sendPdf_instance_off();
								
								if(callback == 'page') sendPdf_show_userlist_page();
								else if(callback == 'filter') sendPdf_back_to_filter();
							}
						});
					} else {
						if(callback == 'page') sendPdf_show_userlist_page();
						else if(callback == 'filter') sendPdf_back_to_filter();
					}
				}
			}
			
			function sendPdf_save_manual_add()
			{
				if(!sendPdf_instance)
				{
					sendPdf_instance_on();
					$.ajax({
						type: 'POST',
						dataType: 'json',
						url: '<?php echo $extradir;?>/input/includes/ajax_sendPdf_users.php',
						cache: false,
						data: { action: 'manual_add', session: '<?php echo $sendPdf_session;?>', caID: '<?php echo $_GET['caID'];?>', name: $('#sendPdf_addpopup .pop-data input.name').val() },
						success: function(data) {
							if(data.id)
							{
								$('#sendPdf_upopup .'+data.id).find('.sendPdf_contactsetedit').show();
								$('#sendPdf_upopup .'+data.id+' .sendPdf_contactsetcount').text(' (<?php echo $formText_selected_fieldtype;?> '+data.selected+' <?php echo $formText_of_fieldtype;?> '+data.total+') ').css('font-weight','bold');
								if(data.filter) $('#sendPdf_upopup .sendPdf_contactset.'+data.id).append(data.filter);
							}
							sendPdf_instance_off();
							sendPdf_back_to_filter();
						}
					});
				}
			}
			
			function sendPdf_change_selection(_this, _source, changeSource, filterId, checked)
			{
				if(!sendPdf_instance)
				{
					sendPdf_instance_on();
					sendPdf_update_selection(_this, checked);
					if(checked) checked = 1; else checked = 0;
					
					$.fancybox.showLoading();
					$.ajax({
						type: 'POST',
						dataType: 'json',
						url: '<?php echo $extradir;?>/input/includes/ajax_sendPdf_users.php',
						cache: false,
						data: { action: 'change_selection', source: _source, changeSource: changeSource, filterId: filterId, session: '<?php echo $sendPdf_session;?>', checked: checked, caID: '<?php echo $_GET['caID'];?>' },
						success: function(data) {
							var _bold = 'normal';
							if(parseInt(data.selected)>0) _bold = 'bold';
							$('#sendPdf_upopup .'+_source+' .sendPdf_contactsetcount').text(' (<?php echo $formText_selected_fieldtype;?> '+data.selected+' <?php echo $formText_of_fieldtype;?> '+data.total+') ').css('font-weight',_bold);
							sendPdf_instance_off();
						}
					});
				} else {
					$(_this).prop('checked',!checked);
				}
			}
			
			function sendPdf_update_selection(_this, checked)
			{	
				$(_this).nextAll('.sendPdf_filter').find('input').prop('checked',checked);
				if($(_this).not('.source'))
				{
					$(_this).parentsUntil('.setcontainer', '.sendPdf_filter').each(function() {
						if(checked)
						{
							if($(this).find('.sendPdf_filter').children('input:not(:checked)').length == 0)
							{
								$(this).children('input:not(:checked)').prop('checked',checked);
							}
						} else {
							$(this).children('input:checked').prop('checked',checked);
						}
					});
				}
				
			}
			
			function sendPdf_back_to_filter()
			{
				$.fancybox.open({href: '#sendPdf_upopup', 'mouseWheel' : false, beforeClose: function() { return sendPdf_check_send(); } });
			}
			
			function sendPdf_user_change()
			{
				if(!sendPdf_instance)
				{
					sendPdf_manual_selection_changed = true;
				} else {
					$(_this).prop('checked',!checked);
				}
			}
			
			function sendPdf_check_send()
			{
				if(sendPdf_instance) return false;
				$.fancybox.showLoading();
				$.ajax({
					type: 'POST',
					async: false,
					dataType: 'json',
					url: '<?php echo $extradir;?>/input/includes/ajax_sendPdf_users.php',
					cache: false,
					data: { action: 'check_send', session: '<?php echo $sendPdf_session;?>', caID: '<?php echo $_GET['caID'];?>' },
					timeout: 1000,
					error: function(){
						$.fancybox.hideLoading();
						return false;
					},
					success: function(data) {
						$('#sendPdf_total_users').text(data.total);
						if(parseInt(data.total)>0)
							$('#next-btn').attr('disabled',false);
						else
							$('#next-btn').attr('disabled',true);
						$.fancybox.hideLoading();
						return true;
					}
				});
			}
			function sendPdf_instance_on()
			{
				sendPdf_instance = true;
				$.fancybox.showLoading();
				$('#sendPdf_upopup input').attr('disabled',true);
				$('#sendPdf_mpopup input').attr('disabled',true);
			}
			function sendPdf_instance_off()
			{
				$.fancybox.hideLoading();
				$('#sendPdf_upopup input').attr('disabled',false);
				$('#sendPdf_mpopup input').attr('disabled',false);
				sendPdf_instance = false;
			}
			</script>
			<div style="display:none;">
				<div id="sendPdf_upopup">
					<h3><center><?php echo $formText_selectReceivers_fieldtype;?></center></h3>
					<div class="pop-filter">
						<?php
						$uniqueSource = array();
						$sources = json_decode(stripslashes($sendPdfUserSource),true);
						if($sendPdfActivateCustomUsers == 1)
						{
							?><div class="setcontainer"><div class="sendPdf_contactset sendPdf_filter preload"><input type="checkbox" class="source" name="sendPdf_contactset[]" value="preload"><label><?php echo $formText_CustomUserlist_fieldtype;?><span class="sendPdf_contactsetcount"></span>&nbsp;&nbsp;&nbsp;&nbsp;<span class="sendPdf_contactsetadd"><?php echo $formText_Add_fieldtype;?></span>&nbsp;&nbsp;&nbsp;&nbsp;<span class="sendPdf_contactsetedit" data-order="1,2" data-orderby="1"><?php echo $formText_editUserList_fieldtype;?></span></label></div></div><?php
						}
						if($sendPdfActivateGetynetUsers == 1)
						{
							?><div class="setcontainer"><div class="sendPdf_contactset sendPdf_filter getynet"><input type="checkbox" class="source import" name="sendPdf_contactset[]" value="getynet"><label><?php echo $formText_getynetUsers_fieldtype;?><span class="sendPdf_contactsetcount"></span>&nbsp;&nbsp;&nbsp;&nbsp;<span class="sendPdf_contactsetedit" data-order="1,2" data-orderby="1"><?php echo $formText_editUserList_fieldtype;?></span></label></div></div><?php
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
							?><div class="setcontainer"><div class="sendPdf_contactset sendPdf_filter <?php echo $source;?>"><input type="checkbox" class="source import" name="sendPdf_contactset[]" value="<?php echo $source.':'.$item;?>"><label><?php echo $vSource[1];?><span class="sendPdf_contactsetcount"></span>&nbsp;&nbsp;&nbsp;&nbsp;<span class="sendPdf_contactsetedit" data-order="<?php echo $vSource[13];?>" data-orderby="<?php echo $vSource[14];?>"><?php echo $formText_editUserList_fieldtype;?></span></label></div></div><?php
						}
						?>
					</div>
					<div class="pop-btns"><input class="pop-close" type="button" value="<?php echo $formText_Ok_fieldtype;?>">&nbsp;&nbsp;&nbsp;&nbsp;</div>
				</div>
				<div id="sendPdf_mpopup">
					<h3><center><?php echo $formText_selectReceivers_fieldtype;?></center></h3>
					<div class="pop-data"></div>
					<div class="pop-btns"><input class="pop-ok" type="button" value="<?php echo $formText_Ok_fieldtype;?>"></div>
				</div>
				<div id="sendPdf_addpopup">
					<h3><center><?php echo $formText_AddUser_fieldtype;?></center></h3>
					<div class="pop-data">
						<div><label><?php echo $formText_Name_fieldtype;?></label><input class="name" type="text" name="name" value="" /></div>
					</div>
					<div class="pop-btns"><input class="pop-ok" type="button" value="<?php echo $formText_Add_fieldtype;?>"></div>
				</div>
			</div>
		</div>
		</td>
	</tr>
	<tr><td colspan="2">
		<?php if(!isset($sendPdfTemplate)) { ?><input type="button" onClick="javascript: $('#hidden_step').val(0); $('#mailingForm').submit();" value="<?php print($formText_Back_sendFromInput);?>"><?php } ?>
		<input id="next-btn" type="submit" name="send" onClick="javascript: if(!confirm('<?php echo $formText_AreYouSureYouWantToCreatePdfForSelectedUsers_sendFromInput;?>?')) return false;" value="<?php echo $formText_createPdf_sendFromInput;?>" disabled>
		<input type="hidden" id="hidden_step" name="step" value="2">
	</td></tr><?php
} else if($_POST['step'] == 2) { // ************************************ DONE
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



if($_POST['step'] == 2) { // ************************************ create pdf
	
	if(!class_exists("TCPDF"))
	{
		include(__DIR__."/../../../../lib/tcpdf/tcpdf.php");
	}
	
	//pagesize 210 x 297
	class MYPDF extends TCPDF {
		public $backgroundColor = array(177, 0, 93);
		
		public function setBG($c) {
			$this->backgroundColor = $c;
		}
		public function getBG() {
			return $this->backgroundColor;
		}
		
		//Page header
		public function Header() {
			/*// Background color      
			$this->Rect(0,0,210,88.5,'F','',$fill_color = $this->backgroundColor);
			
			// Circles
			$this->Circle(161.10, 16.3, 3.6, 0, 360, 'F', null, array(90, 32, 73));*/
		}
	
		// Page footer
		public function Footer() {
			// Position at 25 mm from bottom
			/*$this->SetY(-20);
			$this->SetFont('272dcc_0_0', '', 8);
			$this->SetColor('text', 255, 255, 255);
			$this->setCellHeightRatio(1.15);
			$this->SetX(12);
			$this->Cell(0, 0, 'Interbev AS', 0, 1, '', 0, '', 0, false, 'T', 'M');
			$this->SetX(12);
			$this->Cell(0, 0, 'Harbitzaléen 2 a, 0275 Oslo', 0, 1, '', 0, '', 0, false, 'T', 'M');*/
		}
		
		public function geth() {
			return $this->h;
		}
		public function gettMargin() {
			return $this->tMargin;
		}
		public function getbMargin() {
			return $this->bMargin;
		}
	}
	
	if($_SESSION['caID_'.$_GET['caID']]['POST_ID'] != $_POST['POST_ID'])
	{
		// create new PDF document
		$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		
		$templateTable = explode(":",$mysqlTableName[0]);
		$templateTable = $templateTable[0];
		$templateID = $_GET['ID'];
		$templateLanguageID = $s_default_output_language;
		
		
		include($templateDir.'output_pdfFromModule_'.$_POST['template'].'/template.php');
		
		$uploadsDir = realpath(__DIR__."/../../../../uploads/");
		$fileid = rand(10000,99999);
		while(is_file(rtrim($uploadsDir,'/')."/output_".$fileid.".pdf"))
		{
			$fileid = rand(10000,99999);
		}
		$pdf->Output(rtrim($uploadsDir,'/')."/output_".$fileid.".pdf", 'F');
		
		$link = "uploads/output_".$fileid.".pdf";
		
		$sql = "INSERT INTO sys_pdfsend (id, `type`, created, sender, sender_email, content_moduleID, contentID, content_table, link) VALUES (NULL, 2, NOW(), ?, ?, ?, ?, ?, ?)";
		$o_main->db->query($sql, array($currentUserName, $variables->loggID, $moduleID, $_GET['ID'], $templateTable, $link));		
		
		
		/*?><a href="<?=$languagedir.$link;?>"><?=$formText_downloadFile_sendFromInput;?></a><?*/
		
		$o_main->db->query("delete from sys_pdfsend_userlist where session = ?", array($_POST['session']));
		$o_main->db->query("delete ur.* from sys_pdfsend_userrelation ur join sys_pdfsend_userfilter uf on uf.id = ur.userfilterID where uf.session = ?", array($_POST['session']));
		$o_main->db->query("delete from sys_pdfsend_userfilter where session = ?", array($_POST['session']));
		$o_main->db->query("delete from sys_pdfsend_userlistexpire where session = ?", array($_POST['session']));
		
		$_SESSION['caID_'.$_GET['caID']]['POST_ID'] = $_POST['POST_ID'];
	} else {
		print "<h3>{$formText_RequestWasProcessedAlready_sendFromInput}</h3>";
	}
}


/*
** REPORT
*/
$s_sql = "select ps.* from sys_pdfsend ps where ps.contentID = ? and ps.content_table = ? and ps.content_moduleID = ? and ps.type = 2 order by ps.id DESC";
$o_query = $o_main->db->query($s_sql, array($_GET['ID'], $templateTable, $moduleID));
if($o_query && $o_query->num_rows()>0)
{
	?><div class="report">
	<table border="0" width="100%" cellpadding="0" cellspacing="0">
	<tr><td colspan="4"><h3><?php echo $formText_SentItems_sendFromInput;?></h3></td></tr>
	<tr class="title">
		<td><?php echo $formText_created_sendFromInput;?></td>
		<td><?php echo $formText_createdBy_sendFromInput;?></td>
		<td></td>
		<td></td>
	</tr>
	<?php
	foreach($o_query->result_array() as $v_row)
	{
		$v_row['created'] = date('d-m-Y H:i',strtotime($v_row['created']));
		?>
		<tr class="item">
			<td class="date"><?php echo $v_row['created'];?></td>
			<td><?php echo $v_row['sender_email'];?></td>
			<td class="link"><a href="<?php echo $languagedir.$v_row['link'];?>"><?php echo $formText_downloadFile_sendFromInput;?></a></td>
			<td class="link"><a href="javascript:;" onClick="sendPdf_delete_report(this, <?php echo "'".$v_row['id']."'";?>);" data-name="<?php echo $v_row['created'].' - '.$v_row['sender_email'];?>"><?php echo $formText_delete_sendFromInput;?></a></td>
		</tr>
		<?php
	}
	?></table>
	<script type="text/javascript">
	function sendPdf_delete_report(_this, id)
	{
		if(confirm("<?php echo str_replace("'","\'",$formText_DeleteItem_input);?>: " + $(_this).attr("data-name") + "?")==true) {
			$.fancybox.showLoading();
			$.ajax({
				type: 'POST',
				dataType: 'json',
				url: '<?php echo $extradir;?>/input/includes/ajax_sendPdf_users.php',
				cache: false,
				data: { action: 'delete_report', id: id, caID: '<?php echo $_GET['caID'];?>' },
				success: function(data) {
					if(data.ok == 1) $(_this).closest('tr.item').remove();
					$.fancybox.hideLoading();
				}
			});
		}
	}
	</script>
	</div><?php
}
?>
<style>
#next-btn { float:right; }

#sendPdf_users { margin:20px 0; padding-left:20px; }
#sendPdf_users .header { font-size:13px; font-weight:bold; line-height:26px; }
#sendPdf_users .header a { color:#000066; text-decoration:none; }
#sendPdf_total_users { font-size:13px; font-weight:bold; cursor:pointer; }

.sendPdf_contactset { margin:3px 0; padding:2px 0; }
.sendPdf_contactset:hover { background-color:#e9e9e9; }
.sendPdf_contactsetedit { display:none; cursor:pointer; color:#000066; text-decoration:none; font-weight:bold; }
.sendPdf_filter.filter { padding-left:30px; }
.sendPdf_filter.filter label { cursor:pointer; }

#sendPdf_upopup { min-width:500px; min-height:500px; }
#sendPdf_mpopup { min-width:900px; min-height:500px; }
#sendPdf_mpopup .pop-data .item { white-space:nowrap; }
#sendPdf_mpopup .pop-data .item:nth-child(even) { background-color:#efefef; }
#sendPdf_mpopup .pop-data .item:hover { background-color:#88ddff; }
#sendPdf_mpopup .pop-data .item td { cursor:pointer; padding:2px 10px 2px 0; line-height:19px; }
#sendPdf_mpopup .pop-data .item td.list_checkbox { cursor:inherit; width:20px; }
#sendPdf_upopup .pop-btns, #sendPdf_mpopup .pop-btns, #sendPdf_addpopup .pop-btns, #sendPdf_mpopup .paging { text-align:center; margin-top:20px; }
#sendPdf_mpopup .paging a { font-size:12px; padding:0 3px; color:#666666; text-decoration:none; }
#sendPdf_mpopup .paging a:hover, #sendPdf_mpopup .paging a.active { color:#000066; font-weight:bold; }
#sendPdf_addpopup { min-width:500px; min-height:100px; }
#sendPdf_addpopup .pop-data div { padding-bottom:5px; }
#sendPdf_addpopup .pop-data label { display:inline-block; width:25%; margin-right:2%; text-align:right; }
#sendPdf_addpopup .pop-data input { width:50%; }

.report { margin:20px 0 20px 5px; }
.report tr.title td { padding-bottom:5px; font-weight:bold; border-bottom:1px solid #333333; }
.report tr.item:nth-child(even) { background-color:#efefef; }
.report tr.item:hover { background-color:#88ddff; }
.report td { vertical-align:top; padding:3px 0px; }
.report td.link { text-align:right; padding-right:1%; width:13%; }
.report td.link a { color:#000066; text-decoration:none; }

.sendPdf_sumarize { padding:5px; font-size:13px; font-weight:bold; }
.sendPdf_paging { padding:5px; text-align:center; }
.sendPdf_paging a { color:#000066; text-decoration:none; }
.sendPdf_report { min-width:700px; padding:2px 5px; }
.sendPdf_report:nth-child(even) { background-color:#efefef; }
.sendPdf_report:hover { background-color:#88ddff; }
.sendPdf_report span.email { width:30%; display:inline-block; }
.sendPdf_report span.name { width:30%; display:inline-block; }
.sendPdf_report span.performed { width:20%; display:inline-block; }
</style>