<?php
if($field[11] == '') $field[11] = 'standard';
$v_email_server_conf = array();
$o_query = $o_main->db->query('select * from sys_emailserverconfig order by default_server desc');
if($o_query && $o_query->num_rows()>0) $v_email_server_conf = $o_query->row_array();
if(strlen($variables->loggID)==0)
{
	print ("Fieldtype works from Getynet!");
	return;
}
if($sys_webmaster_email == "")
{
	print ("Configuration error: Webmaster email is missing");
	return;
}
if($v_email_server_conf["host"] == "")
{
	print ("Configuration error: Email server not configured");
	return;
}
if(!$o_main->db->table_exists('sys_emailsend'))
{
	print ("Configuration error: EmailSending module not added");
	return;
}
if(!is_file(__DIR__.'/../../../output_ReminderEmailFieldtype/'.trim($field[11]).'.php'))
{
	print ("Configuration error: Email template is missing");
	return;
}
if(!function_exists("APIconnectUser")) include_once(__DIR__."/../../includes/APIconnect.php");
if(!function_exists("sendEmail_get_module_options")) include_once(__DIR__."/fn_sendEmail_get_module_options.php");

if(!$o_main->db->table_exists('sys_emailsendunsubscribe'))
{
	$o_main->db->simple_query("CREATE TABLE `sys_emailsendunsubscribe` (
		`id` INT(11) NOT NULL AUTO_INCREMENT,
		`email` CHAR(100) NOT NULL,
		`created` TIMESTAMP NOT NULL,
		PRIMARY KEY (`id`),
		UNIQUE INDEX `Idx` (`email`)
	)");
}
if(!$o_main->db->table_exists('sys_emailsend_userlist'))
{
	$o_main->db->simple_query("CREATE TABLE IF NOT EXISTS `sys_emailsend_userlist` (
		`id` INT(11) NOT NULL AUTO_INCREMENT,
		`session` CHAR(13) NOT NULL DEFAULT '',
		`source` CHAR(50) NOT NULL DEFAULT '',
		`email` CHAR(128) NOT NULL DEFAULT '',
		`name` CHAR(128) NOT NULL DEFAULT '',
		`extra1` CHAR(128) NOT NULL DEFAULT '',
		`extra2` CHAR(128) NOT NULL DEFAULT '',
		`text` varchar(1000) NOT NULL DEFAULT '',
		`selected` tinyint(1) NOT NULL DEFAULT 1,
		`disabled` tinyint(1) NOT NULL DEFAULT 0,
		origID INT(11) NULL,
		PRIMARY KEY (`id`),
		INDEX `idx1` (`session`, `source`, `email`),
		INDEX `idx2` (`origID`)
	)");
	$o_main->db->simple_query("CREATE TABLE IF NOT EXISTS `sys_emailsend_userrelation` (
		`userfilterID` INT(11) NOT NULL,
		`userlistID` INT(11) NOT NULL,
		INDEX `idx1` (`userfilterID`, `userlistID`)
	)");
	$o_main->db->simple_query("CREATE TABLE IF NOT EXISTS `sys_emailsend_userfilter` (
		`id` INT(11) NOT NULL AUTO_INCREMENT,
		`session` CHAR(13) NOT NULL DEFAULT '',
		`source` CHAR(50) NOT NULL DEFAULT '',
		`parentID` INT(11) NOT NULL DEFAULT 0,
		`name` CHAR(50) NOT NULL DEFAULT '',
		`selected` tinyint(1) NOT NULL DEFAULT 1,
		`hide_empty` tinyint(1) NOT NULL DEFAULT 0,
		PRIMARY KEY (`id`),
		INDEX `idx1` (`parentID`),
		INDEX `idx2` (`session`, `source`)
	)");
	$o_main->db->simple_query("CREATE TABLE IF NOT EXISTS `sys_emailsend_userlistexpire` (
		`session` CHAR(13) NOT NULL DEFAULT '',
		`created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
		UNIQUE INDEX `idx1` (`session`)
	)");
}
?>
<style type="text/css">
.group_<?php echo $field_ui_id;?> { border:1px solid #666666; padding:1%; margin-bottom:5px; width:94%; }
.group_<?php echo $field_ui_id;?> a { color:#000066; text-decoration:none; }

#items_<?php echo $field_ui_id;?> .header { font-size:12px; font-weight:bold; }
#<?php echo $field_ui_id;?>_times .data div { padding:0px 3px; vertical-align:middle; font-size:12px; margin-bottom:4px; border-radius:5px; line-height:16px; }
#<?php echo $field_ui_id;?>_times .data .date { width:50%; display:inline-block; }
#<?php echo $field_ui_id;?>_times .data .showemail, #<?php echo $field_ui_id;?>_times .data .showreport { width:25%; display:inline-block; text-align:right; }
#<?php echo $field_ui_id;?>_times .data div.done { background-color:#efefef; border:2px solid #efefef;  }
#<?php echo $field_ui_id;?>_times .data div.do { background-color:#8CE18E; border:2px solid #8CE18E; }
#<?php echo $field_ui_id;?>_times .data div.wait { background-color:#FFFFCE; border:2px solid #FFFFCE; }
#<?php echo $field_ui_id;?>_times .data div:hover { background-color:#88ddff; border:2px solid #88ddff; }
#<?php echo $field_ui_id;?>_times .data div.done img, #<?php echo $field_ui_id;?>_times .data div.do img, #<?php echo $field_ui_id;?>_times .data div.wait img { cursor:pointer; }
#<?php echo $field_ui_id;?>_times .time { width:90%; border:none; background:none; font-size:inherit; font-family:inherit; cursor:pointer; }
#items_<?php echo $field_ui_id;?> .delete { cursor:pointer; }

#<?php echo $field_ui_id;?>_total_users { font-size:13px; font-weight:bold; cursor:pointer; }

.<?php echo $field_ui_id;?>_contactset { margin:3px 0; padding:2px 0; }
.<?php echo $field_ui_id;?>_contactset:hover { background-color:#e9e9e9; }
.<?php echo $field_ui_id;?>_contactsetedit { display:none; cursor:pointer; color:#000066; text-decoration:none; font-weight:bold; }
.<?php echo $field_ui_id;?>_filter.filter { padding-left:30px; }
.<?php echo $field_ui_id;?>_filter.filter label { cursor:pointer; }

#<?php echo $field_ui_id;?>_upopup { min-width:500px; min-height:500px; }
#<?php echo $field_ui_id;?>_mpopup { min-width:900px; min-height:500px; }
#<?php echo $field_ui_id;?>_mpopup .pop-data .item { white-space:nowrap; }
#<?php echo $field_ui_id;?>_mpopup .pop-data .item:nth-child(even) { background-color:#efefef; }
#<?php echo $field_ui_id;?>_mpopup .pop-data .item:hover { background-color:#88ddff; }
#<?php echo $field_ui_id;?>_mpopup .pop-data .item td { cursor:pointer; padding:2px 10px 2px 0; }
#<?php echo $field_ui_id;?>_mpopup .pop-data .item td.list_checkbox { cursor:inherit; width:20px; }
#<?php echo $field_ui_id;?>_upopup .pop-btns, #<?php echo $field_ui_id;?>_mpopup .pop-btns, #<?php echo $field_ui_id;?>_addpopup .pop-btns, #<?php echo $field_ui_id;?>_mpopup .paging { text-align:center; margin-top:20px; }
#<?php echo $field_ui_id;?>_mpopup .paging a { font-size:12px; padding:0 3px; color:#666666; text-decoration:none; }
#<?php echo $field_ui_id;?>_mpopup .paging a:hover, #<?php echo $field_ui_id;?>_mpopup .paging a.active { color:#000066; font-weight:bold; }
#<?php echo $field_ui_id;?>_addpopup { min-width:500px; min-height:100px; }
#<?php echo $field_ui_id;?>_addpopup .pop-data div { padding-bottom:5px; }
#<?php echo $field_ui_id;?>_addpopup .pop-data label { display:inline-block; width:25%; margin-right:2%; text-align:right; }
#<?php echo $field_ui_id;?>_addpopup .pop-data input { width:50%; }

.<?php echo $field_ui_id;?>_sumarize { padding:5px; font-size:13px; font-weight:bold; }
.<?php echo $field_ui_id;?>_paging { padding:5px; text-align:center; }
.<?php echo $field_ui_id;?>_paging a { color:#000066; text-decoration:none; }
.<?php echo $field_ui_id;?>_report, .<?php echo $field_ui_id;?>_head { min-width:900px; padding:2px 5px; }
.<?php echo $field_ui_id;?>_head { background-color:#66bbdd; }
.<?php echo $field_ui_id;?>_report:nth-child(even) { background-color:#efefef; }
.<?php echo $field_ui_id;?>_report:hover { background-color:#88ddff; }
.<?php echo $field_ui_id;?>_report span.email, .<?php echo $field_ui_id;?>_head span.email { width:20%; display:inline-block; }
.<?php echo $field_ui_id;?>_report span.name, .<?php echo $field_ui_id;?>_head span.name { width:25%; display:inline-block; }
.<?php echo $field_ui_id;?>_report span.performed, .<?php echo $field_ui_id;?>_head span.performed { width:20%; display:inline-block; }
.<?php echo $field_ui_id;?>_report span.status, .<?php echo $field_ui_id;?>_head span.status { width:15%; display:inline-block; }
.<?php echo $field_ui_id;?>_report span.count, .<?php echo $field_ui_id;?>_head span.count { width:10%; display:inline-block; }
</style>
<div id="selections_<?php echo $field_ui_id;?>" class="group_<?php echo $field_ui_id.($field[9]==1?" hide":"");?>"><?php
//get myself
$myself = json_decode(APIconnectUser("usersessionget", $variables->loggID, $variables->sessionID, array()),true);
$myself = $myself['data'];

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

$counter = 0;
$o_query = $o_main->db->query("select distinct est.receiver_email, est.receiver from sys_emailsendto est join sys_emailsend es on est.emailsend_id = es.id where es.content_id = ? and es.content_table = ? and es.content_module_id = ? and es.type = 1", array($ID, $field[3], $moduleID));
if($o_query)
{
	$counter = $o_query->num_rows();
	foreach($o_query->result_array() as $row)
	{
		$o_main->db->query("insert into sys_emailsend_userlist(id, session, source, email, name, text, origID) values (NULL, ?, ?, ?, ?, ?, ?)", array($sendEmail_session, 'preload', $row['receiver_email'], $row['receiver'], '', 0));
	}
}
?>
<input type="hidden" value="<?php echo $myself['usersName'];?>" name="<?php echo $field[1].$ending;?>_myselfname">
<input type="hidden" value="<?php echo $sendEmail_session;?>" name="<?php echo $field[1].$ending;?>_session">
<div id="items_<?php echo $field_ui_id;?>">
	<div class="item users">
		<div class="input" id="<?php echo $field_ui_id;?>_users">
			<?php if($field[10] != 1 and $access >= 10) { ?>
			<div class="header"><a id="<?php echo $field_ui_id;?>_load_users" class="script" href="#<?php echo $field_ui_id;?>_upopup"><?php echo $formText_selectReceivers_sendFromInput;?> +</a></div>
			<?php } ?>
			<div><?php echo $formText_totalUsersSelected_sendFromInput;?>: <span id="<?php echo $field_ui_id;?>_total_users"><?php echo $counter;?></span></div>
			<script type="text/javascript">
			<?php if(isset($ob_javascript)) { ob_start(); } ?>
			var <?php echo $field_ui_id;?>_instance;
			var <?php echo $field_ui_id;?>_sources;
			var <?php echo $field_ui_id;?>_manual_selection_changed;
			var <?php echo $field_ui_id;?>_manual_source;
			var <?php echo $field_ui_id;?>_manual_source_config;
			var <?php echo $field_ui_id;?>_manual_page;
			var <?php echo $field_ui_id;?>_userlist_view;
			$(function() {
				$('#<?php echo $field_ui_id;?>_load_users').fancybox({ 'mouseWheel' : false, beforeClose: function() { return <?php echo $field_ui_id;?>_check_send(); } });
				$('#<?php echo $field_ui_id;?>_upopup input.pop-close').off('click').on('click', function() {
					if(<?php echo $field_ui_id;?>_instance) return;
					$.fancybox.close();
				});
				$('#<?php echo $field_ui_id;?>_mpopup input.pop-ok').off('click').on('click', function() {
					// Userlist OK button
					if(<?php echo $field_ui_id;?>_instance) return;
					
					if(<?php echo $field_ui_id;?>_userlist_view)
					{
						$.fancybox.close();
						<?php echo $field_ui_id;?>_userlist_view = false;
					} else {
						<?php echo $field_ui_id;?>_save_manual_selection('filter');
					}
				});
				$('#<?php echo $field_ui_id;?>_addpopup input.pop-ok').off('click').on('click', function() {
					// Custom add OK button
					if(<?php echo $field_ui_id;?>_instance) return;
					
					<?php echo $field_ui_id;?>_save_manual_add();
				});
				
				
				$('.<?php echo $field_ui_id;?>_contactset input').on('change',function() {
					if(<?php echo $field_ui_id;?>_instance) return;
					
					var _this = $(this);
					$(this).next('label').find('.<?php echo $field_ui_id;?>_contactsetedit').show();
					if($(_this).is('.import'))
					{
						<?php echo $field_ui_id;?>_instance_on();
						$(this).removeClass('import');
						$.ajax({
							type: 'POST',
							dataType: 'json',
							url: '<?php echo $extradir;?>/input/fieldtypes/<?php echo $field[4];?>/ajax_users.php',
							cache: false,
							data: { action: 'import', source: $(this).val(), session: '<?php echo $sendEmail_session;?>', field_ui_id: '<?php echo $field_ui_id;?>', companyID : '<?php echo $_GET['companyID'];?>', removeunsubscribers: 1, choosenAdminLang: '<?php echo $choosenAdminLang;?>', caID: '<?php echo $_GET['caID'];?>' },
							success: function(data) {
								if(data.id)
								{
									$('#<?php echo $field_ui_id;?>_upopup .'+data.id+' .<?php echo $field_ui_id;?>_contactsetcount').text(' (<?php echo $formText_selected_fieldtype;?> '+data.selected+' <?php echo $formText_of_fieldtype;?> '+data.total+') ').css('font-weight','bold');
									$(_this).val(data.id);
									if(data.filter) $('#<?php echo $field_ui_id;?>_upopup .<?php echo $field_ui_id;?>_contactset.'+data.id).append(data.filter);
								}
								<?php echo $field_ui_id;?>_instance_off();
								$.fancybox.update();
							}
						});
					} else {
						if(this.checked) $(this).parent().find('.<?php echo $field_ui_id;?>_filter').show();
						else $(this).parent().find('.<?php echo $field_ui_id;?>_filter').hide();
						<?php echo $field_ui_id;?>_change_selection(this, $(this).val(), $(this).val(), 0, this.checked);
					}
				});
				
				$('.<?php echo $field_ui_id;?>_contactset .<?php echo $field_ui_id;?>_contactsetedit').off('click').on('click',function() {
					if(<?php echo $field_ui_id;?>_instance) return;
					
					<?php echo $field_ui_id;?>_manual_source = $(this).parent().prevAll('input.source').val();
					<?php echo $field_ui_id;?>_manual_source_config = $(this).attr('data-order') + ':' + $(this).attr('data-orderby');
					<?php echo $field_ui_id;?>_manual_selection_changed = false;
					$.fancybox.open({ href: '#<?php echo $field_ui_id;?>_mpopup', 'mouseWheel' : false, beforeClose: function() { return <?php echo $field_ui_id;?>_check_send(); } });
					<?php echo $field_ui_id;?>_show_userlist_page(0);
				});
				$('.<?php echo $field_ui_id;?>_contactset .<?php echo $field_ui_id;?>_contactsetadd').off('click').on('click',function() {
					if(<?php echo $field_ui_id;?>_instance) return;
					
					$(this).closest('.<?php echo $field_ui_id;?>_contactset').find('input.source').attr('checked',1);
					$('#<?php echo $field_ui_id;?>_addpopup .pop-data input').val('');
					$.fancybox.open({ href: '#<?php echo $field_ui_id;?>_addpopup', 'mouseWheel' : false, beforeClose: function() { return <?php echo $field_ui_id;?>_check_send(); } });
				});
				
				$('#<?php echo $field_ui_id;?>_total_users').off('click').on('click',function() {
					if(<?php echo $field_ui_id;?>_instance) return;
					
					<?php echo $field_ui_id;?>_userlist_view = true;
					<?php echo $field_ui_id;?>_manual_source = '';
					<?php echo $field_ui_id;?>_manual_source_config = '1,2:1';
					<?php echo $field_ui_id;?>_manual_selection_changed = false;
					$.fancybox.open({ href: '#<?php echo $field_ui_id;?>_mpopup', 'mouseWheel' : false, beforeClose: function() { return <?php echo $field_ui_id;?>_check_send(); } });
					<?php echo $field_ui_id;?>_show_userlist_page(0);
				});
				
				<?php if($counter>0) { ?>
				$('.<?php echo $field_ui_id;?>_contactset.preload input.source').trigger('click');
				<?php echo $field_ui_id;?>_check_send();
				<?php } ?>
				
				// DO DB CLEANUP
				$.ajax({
					type: 'POST',
					url: '<?php echo $extradir;?>/input/fieldtypes/<?php echo $field[4];?>/ajax_users.php',
					cache: false,
					data: { action: 'cleanup', caID: '<?php echo $_GET['caID'];?>' }
				});
			});
			
			function <?php echo $field_ui_id;?>_show_userlist_page(page)
			{
				if(!<?php echo $field_ui_id;?>_instance)
				{
					if(page != null) <?php echo $field_ui_id;?>_manual_page = page;
					if(<?php echo $field_ui_id;?>_manual_selection_changed && confirm('<?php echo $formText_changesWereMadeDoYouWantToSaveThem_fieldtype;?>?'))
					{
						<?php echo $field_ui_id;?>_save_manual_selection('page');
						return;
					}
					<?php echo $field_ui_id;?>_instance_on();
					<?php echo $field_ui_id;?>_manual_selection_changed = false;
					
					$.fancybox.showLoading();
					$.ajax({
						type: 'POST',
						dataType: 'json',
						url: '<?php echo $extradir;?>/input/fieldtypes/<?php echo $field[4];?>/ajax_users.php',
						cache: false,
						data: { action: 'list', source: <?php echo $field_ui_id;?>_manual_source, session: '<?php echo $sendEmail_session;?>', field: '<?php echo $field[1].$ending;?>', field_ui_id: '<?php echo $field_ui_id;?>', sourceconfig: <?php echo $field_ui_id;?>_manual_source_config, page: <?php echo $field_ui_id;?>_manual_page, caID: '<?php echo $_GET['caID'];?>' },
						success: function(data) {
							$('#<?php echo $field_ui_id;?>_mpopup .pop-data').html(data.html);
							$.fancybox.update();
							<?php echo $field_ui_id;?>_instance_off();
						}
					});
				}
			}
			
			function <?php echo $field_ui_id;?>_save_manual_selection(callback)
			{
				if(!<?php echo $field_ui_id;?>_instance)
				{
					if(<?php echo $field_ui_id;?>_manual_selection_changed)
					{
						<?php echo $field_ui_id;?>_instance_on();
						var <?php echo $field_ui_id;?>_users_selected = new Array();
						var <?php echo $field_ui_id;?>_users_unselected = new Array();
						$('#<?php echo $field_ui_id;?>_mpopup .pop-data input:checked').each(function () { <?php echo $field_ui_id;?>_users_selected.push($(this).val()); });
						$('#<?php echo $field_ui_id;?>_mpopup .pop-data input:not(:checked)').each(function () { <?php echo $field_ui_id;?>_users_unselected.push($(this).val()); });
						
						$.ajax({
							type: 'POST',
							dataType: 'json',
							url: '<?php echo $extradir;?>/input/fieldtypes/<?php echo $field[4];?>/ajax_users.php',
							cache: false,
							data: { action: 'manual_update', selected: <?php echo $field_ui_id;?>_users_selected, unselected: <?php echo $field_ui_id;?>_users_unselected, caID: '<?php echo $_GET['caID'];?>' },
							success: function(data) {
								if(data.total)
								{
									var _bold = 'normal';
									if(parseInt(data.selected)>0) _bold = 'bold';
									$('#<?php echo $field_ui_id;?>_upopup .<?php echo $field_ui_id;?>_contactset.'+<?php echo $field_ui_id;?>_manual_source+' .<?php echo $field_ui_id;?>_filter').hide();
									$('#<?php echo $field_ui_id;?>_upopup .<?php echo $field_ui_id;?>_contactset.'+<?php echo $field_ui_id;?>_manual_source+' input:checked').prop('checked',false);
									$('#<?php echo $field_ui_id;?>_upopup .'+<?php echo $field_ui_id;?>_manual_source+' .<?php echo $field_ui_id;?>_contactsetcount').text(' (<?php echo $formText_manualUserSelection_fieldtype;?> '+data.selected+' <?php echo $formText_of_fieldtype;?> '+data.total+') ').css('font-weight',_bold);
								} else {
									alert('<?php echo $formText_errorOccured_fieldtype;?>');
								}
								<?php echo $field_ui_id;?>_manual_selection_changed = false;
								<?php echo $field_ui_id;?>_instance_off();
								
								if(callback == 'page') <?php echo $field_ui_id;?>_show_userlist_page();
								else if(callback == 'filter') <?php echo $field_ui_id;?>_back_to_filter();
							}
						});
					} else {
						if(callback == 'page') <?php echo $field_ui_id;?>_show_userlist_page();
						else if(callback == 'filter') <?php echo $field_ui_id;?>_back_to_filter();
					}
				}
			}
			
			function <?php echo $field_ui_id;?>_save_manual_add()
			{
				if(!<?php echo $field_ui_id;?>_instance)
				{
					<?php echo $field_ui_id;?>_instance_on();
					$.ajax({
						type: 'POST',
						dataType: 'json',
						url: '<?php echo $extradir;?>/input/fieldtypes/<?php echo $field[4];?>/ajax_users.php',
						cache: false,
						data: { action: 'manual_add', session: '<?php echo $sendEmail_session;?>', caID: '<?php echo $_GET['caID'];?>', name: $('#<?php echo $field_ui_id;?>_addpopup .pop-data input.name').val(), email: $('#<?php echo $field_ui_id;?>_addpopup .pop-data input.email').val() },
						success: function(data) {
							if(data.id)
							{
								$('#<?php echo $field_ui_id;?>_upopup .'+data.id).find('.<?php echo $field_ui_id;?>_contactsetedit').show();
								$('#<?php echo $field_ui_id;?>_upopup .'+data.id+' .<?php echo $field_ui_id;?>_contactsetcount').text(' (<?php echo $formText_selected_fieldtype;?> '+data.selected+' <?php echo $formText_of_fieldtype;?> '+data.total+') ').css('font-weight','bold');
								if(data.filter) $('#<?php echo $field_ui_id;?>_upopup .<?php echo $field_ui_id;?>_contactset.'+data.id).append(data.filter);
							}
							<?php echo $field_ui_id;?>_instance_off();
							<?php echo $field_ui_id;?>_back_to_filter();
						}
					});
				}
			}
			
			function <?php echo $field_ui_id;?>_change_selection(_this, _source, changeSource, filterId, checked)
			{
				if(!<?php echo $field_ui_id;?>_instance)
				{
					<?php echo $field_ui_id;?>_instance_on();
					<?php echo $field_ui_id;?>_update_selection(_this, checked);
					if(checked) checked = 1; else checked = 0;
					
					$.fancybox.showLoading();
					$.ajax({
						type: 'POST',
						dataType: 'json',
						url: '<?php echo $extradir;?>/input/fieldtypes/<?php echo $field[4];?>/ajax_users.php',
						cache: false,
						data: { action: 'change_selection', source: _source, changeSource: changeSource, filterId: filterId, session: '<?php echo $sendEmail_session;?>', checked: checked, caID: '<?php echo $_GET['caID'];?>' },
						success: function(data) {
							var _bold = 'normal';
							if(parseInt(data.selected)>0) _bold = 'bold';
							$('#<?php echo $field_ui_id;?>_upopup .'+_source+' .<?php echo $field_ui_id;?>_contactsetcount').text(' (<?php echo $formText_selected_fieldtype;?> '+data.selected+' <?php echo $formText_of_fieldtype;?> '+data.total+') ').css('font-weight',_bold);
							<?php echo $field_ui_id;?>_instance_off();
						}
					});
				} else {
					$(_this).prop('checked',!checked);
				}
			}
			
			function <?php echo $field_ui_id;?>_update_selection(_this, checked)
			{	
				$(_this).nextAll('.<?php echo $field_ui_id;?>_filter').find('input').prop('checked',checked);
				if($(_this).not('.source'))
				{
					$(_this).parentsUntil('.setcontainer', '.<?php echo $field_ui_id;?>_filter').each(function() {
						if(checked)
						{
							if($(this).find('.<?php echo $field_ui_id;?>_filter').children('input:not(:checked)').length == 0)
							{
								$(this).children('input:not(:checked)').prop('checked',checked);
							}
						} else {
							$(this).children('input:checked').prop('checked',checked);
						}
					});
				}
				
			}
			
			function <?php echo $field_ui_id;?>_back_to_filter()
			{
				$.fancybox.open({href: '#<?php echo $field_ui_id;?>_upopup', 'mouseWheel' : false, beforeClose: function() { return <?php echo $field_ui_id;?>_check_send(); } });
			}
			
			function <?php echo $field_ui_id;?>_user_change()
			{
				if(!<?php echo $field_ui_id;?>_instance)
				{
					<?php echo $field_ui_id;?>_manual_selection_changed = true;
				} else {
					$(_this).prop('checked',!checked);
				}
			}
			
			function <?php echo $field_ui_id;?>_check_send()
			{
				if(<?php echo $field_ui_id;?>_instance) return false;
				$.fancybox.showLoading();
				$.ajax({
					type: 'POST',
					async: false,
					dataType: 'json',
					url: '<?php echo $extradir;?>/input/fieldtypes/<?php echo $field[4];?>/ajax_users.php',
					cache: false,
					data: { action: 'check_send', session: '<?php echo $sendEmail_session;?>', caID: '<?php echo $_GET['caID'];?>' },
					timeout: 1000,
					error: function(){
						$.fancybox.hideLoading();
						return false;
					},
					success: function(data) {
						$('#<?php echo $field_ui_id;?>_total_users').text(data.total);
						if(parseInt(data.total)>0)
							$('#next-btn').attr('disabled',false);
						else
							$('#next-btn').attr('disabled',true);
						$.fancybox.hideLoading();
						return true;
					}
				});
			}
			function <?php echo $field_ui_id;?>_instance_on()
			{
				<?php echo $field_ui_id;?>_instance = true;
				$.fancybox.showLoading();
				$('#<?php echo $field_ui_id;?>_upopup input').attr('disabled',true);
				$('#<?php echo $field_ui_id;?>_mpopup input').attr('disabled',true);
			}
			function <?php echo $field_ui_id;?>_instance_off()
			{
				$.fancybox.hideLoading();
				$('#<?php echo $field_ui_id;?>_upopup input').attr('disabled',false);
				$('#<?php echo $field_ui_id;?>_mpopup input').attr('disabled',false);
				<?php echo $field_ui_id;?>_instance = false;
			}
			<?php if(isset($ob_javascript)) { $ob_javascript .= " ".ob_get_clean(); } ?>
			</script>
			<div class="hide">
				<div id="<?php echo $field_ui_id;?>_upopup">
					<h3><center><?php echo $formText_selectReceivers_fieldtype;?></center></h3>
					<div class="pop-filter">
						<?php
						$uniqueSource = array();
						$sources = json_decode(stripslashes($sendEmailUserSource),true);
						
						if($sendEmailActivateCustomUsers == 1)
						{
							?><div class="setcontainer"><div class="<?php echo $field_ui_id;?>_contactset <?php echo $field_ui_id;?>_filter preload"><input type="checkbox" class="source" name="<?php echo $field[1].$ending;?>_contactset[]" value="preload"><label><?php echo $formText_CustomUserlist_fieldtype;?><span class="<?php echo $field_ui_id;?>_contactsetcount"></span>&nbsp;&nbsp;&nbsp;&nbsp;<span class="<?php echo $field_ui_id;?>_contactsetadd"><?php echo $formText_Add_fieldtype;?></span>&nbsp;&nbsp;&nbsp;&nbsp;<span class="<?php echo $field_ui_id;?>_contactsetedit" data-order="1,2" data-orderby="1"><?php echo $formText_editUserList_fieldtype;?></span></label></div></div><?php
						}
						if($sendEmailActivateGetynetUsers == 1)
						{
							?><div class="setcontainer"><div class="<?php echo $field_ui_id;?>_contactset <?php echo $field_ui_id;?>_filter getynet"><input type="checkbox" class="source import" name="<?php echo $field[1].$ending;?>_contactset[]" value="getynet"><label><?php echo $formText_getynetUsers_fieldtype;?><span class="<?php echo $field_ui_id;?>_contactsetcount"></span>&nbsp;&nbsp;&nbsp;&nbsp;<span class="<?php echo $field_ui_id;?>_contactsetedit" data-order="1,2" data-orderby="1"><?php echo $formText_editUserList_fieldtype;?></span></label></div></div><?php
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
							?><div class="setcontainer"><div class="<?php echo $field_ui_id;?>_contactset <?php echo $field_ui_id;?>_filter <?php echo $source;?>"><input type="checkbox" class="source import" name="<?php echo $field[1].$ending;?>_contactset[]" value="<?php echo $source.':'.$item;?>"><label><?php echo $vSource[1];?><span class="<?php echo $field_ui_id;?>_contactsetcount"></span>&nbsp;&nbsp;&nbsp;&nbsp;<span class="<?php echo $field_ui_id;?>_contactsetedit" data-order="<?php echo $vSource[6];?>" data-orderby="<?php echo $vSource[7];?>"><?php echo $formText_editUserList_fieldtype;?></span></label></div></div><?php
						}
						?>
					</div>
					<div class="pop-btns"><input class="pop-close" type="button" value="<?php echo $formText_Ok_fieldtype;?>">&nbsp;&nbsp;&nbsp;&nbsp;</div>
				</div>
				<div id="<?php echo $field_ui_id;?>_mpopup">
					<h3><center><?php echo $formText_selectReceivers_fieldtype;?></center></h3>
					<div class="pop-data"></div>
					<div class="pop-btns"><input class="pop-ok" type="button" value="<?php echo $formText_Ok_fieldtype;?>"></div>
				</div>
				<div id="<?php echo $field_ui_id;?>_addpopup">
					<h3><center><?php echo $formText_AddUser_fieldtype;?></center></h3>
					<div class="pop-data">
						<div><label><?php echo $formText_Name_fieldtype;?></label><input class="name" type="text" name="name" value="" /></div>
						<div><label><?php echo $formText_Email_fieldtype;?></label><input class="email" type="text" name="email" value="" /></div>
					</div>
					<div class="pop-btns"><input class="pop-ok" type="button" value="<?php echo $formText_Add_fieldtype;?>"></div>
				</div>
			</div>
		</div>
		<br clear="all"/>
	</div>
	<div class="item times">
		<div class="input" id="<?php echo $field_ui_id;?>_times">
			<?php if($field[10] != 1 and $access >= 10) { ?>
			<div class="header"><a class="script" href="javascript:add_time_<?php echo $field_ui_id;?>();"><?php echo $formText_AddTime_fieldtype;?> +</a></div>
			<?php } ?>
			<div class="data">
				<?php
				$o_query = $o_main->db->query("select distinct es.send_on from sys_emailsendto est join sys_emailsend es on est.emailsend_id = es.id LEFT OUTER JOIN sys_emailsendto est_check ON est.emailsend_id = est_check.emailsend_id AND est_check.status = 0 where es.content_id = ? and es.content_table = ? and es.content_module_id = ? and es.type = 1 and est.status > 0 AND est_check.id IS NULL ORDER BY es.send_on ASC", array($ID, $field[3], $moduleID));
				if($o_query && $o_query->num_rows()>0)
				foreach($o_query->result_array() as $row)
				{
					$row['send_on'] = date('d-m-Y H:i',strtotime($row['send_on']));
					?><div class="done"><span class="date"><?php echo $row['send_on'];?></span><span class="showemail"><a class="script" href="javascript:;" onclick="show_email_<?php echo $field_ui_id;?>('<?php echo $ID;?>','<?php echo $field[3];?>','<?php echo $moduleID;?>','<?php echo $row['send_on'];?>');"><?php echo $formText_showEmail_fieldtype;?></a></span><span class="showreport"><a class="script" href="javascript:;" onclick="show_report_<?php echo $field_ui_id;?>('<?php echo $ID;?>','<?php echo $field[3];?>','<?php echo $moduleID;?>','<?php echo $row['send_on'];?>');"><?php echo $formText_showReport_fieldtype;?></a></span></div><?php
				}
				
				$o_query = $o_main->db->query("select distinct es.send_on from sys_emailsendto est join sys_emailsend es on est.emailsend_id = es.id LEFT OUTER JOIN sys_emailsendto est_check ON est.emailsend_id = est_check.emailsend_id AND est_check.status > 0 where es.content_id = ? and es.content_table = ? and es.content_module_id = ? and es.type = 1 and est.status = 0 AND est_check.id IS NOT NULL ORDER BY es.send_on ASC", array($ID, $field[3], $moduleID));
				if($o_query && $o_query->num_rows()>0)
				foreach($o_query->result_array() as $row)
				{
					$row['send_on'] = date('d-m-Y H:i',strtotime($row['send_on']));
					?><div class="do"><span class="date"><?php echo $row['send_on'];?></span><span class="showemail"></span><span class="showreport<?php echo ($field[10]==1?" hide":"");?>"><a class="script" href="javascript:;" onclick="abort_time_<?php echo $field_ui_id;?>('<?php echo $ID;?>','<?php echo $field[3];?>','<?php echo $moduleID;?>','<?php echo $row['send_on'];?>',this);"><?php echo $formText_AbortSending_fieldtype;?></a></span></div><?php
				}
				
				$o_query = $o_main->db->query("select distinct es.send_on from sys_emailsendto est join sys_emailsend es on est.emailsend_id = es.id LEFT OUTER JOIN sys_emailsendto est_check ON est.emailsend_id = est_check.emailsend_id AND est_check.status > 0 where es.content_id = ? and es.content_table = ? and es.content_module_id = ? and es.type = 1 and est.status = 0 AND est_check.id IS NULL ORDER BY es.send_on ASC", array($ID, $field[3], $moduleID));
				if($o_query && $o_query->num_rows()>0)
				foreach($o_query->result_array() as $row)
				{
					$row['send_on'] = date('d-m-Y H:i',strtotime($row['send_on']));
					?><div class="wait"><input type="hidden" name="<?php echo $field[1].$ending;?>_time_old[]" value="<?php echo $row['send_on'];?>" /><input class="time <?php echo $field_ui_id;?>_time" type="datetime" name="<?php echo $field[1].$ending;?>_time[]" value="<?php echo $row['send_on'];?>" /><img class="delete<?php echo ($field[10]==1?" hide":"");?>" src="<?php echo $extradir;?>/input/fieldtypes/<?php echo $field[4];?>/images/cross.png" border="0" onclick="$(this).parent().remove();" align="right" title="<?php echo $formText_remove_fieldtype;?>" /></div><?php
				}
				?>
			</div>
			<script type="text/javascript">
			<?php if(isset($ob_javascript)) { ob_start(); } ?>
			$(function() {
				$('.<?php echo $field_ui_id;?>_time').datetimepicker({
					dateFormat: 'dd-mm-yy',
					timeFormat: 'HH:mm',
					changeMonth: true,
					changeYear: true,
					minDate: 0,
					minuteMin: 0,
					controlType: 'select',
					gotoCurrent: true
				});
			});
			function add_time_<?php echo $field_ui_id;?>()
			{
				var d = new Date();
				d.setDate(d.getDate()+1)
				d.setHours(12,0,0,0);
				//console.log(d.ddmmyyyyhhss());
				var _input = $('<input class="time <?php echo $field_ui_id;?>_time" type="datetime" name="<?php echo $field[1].$ending;?>_time[]" value="" />');
				var _div = $('<div class="wait">');
				$(_div).append('<input type="hidden" name="<?php echo $field[1].$ending;?>_time_old[]" />');
				$(_div).append(_input);
				$(_div).append('<img src="<?php echo $extradir;?>/input/fieldtypes/<?php echo $field[4];?>/images/cross.png" border="0" onclick="$(this).parent().remove();" align="right" title="<?php echo $formText_remove_fieldtype;?>" />');
				$('#<?php echo $field_ui_id;?>_times .data').append(_div);
				$(window).trigger('resize');
				
				$(_input).datetimepicker({
					dateFormat: 'dd-mm-yy',
					timeFormat: 'hh:mm',
					changeMonth: true,
					changeYear: true,
					minDate: 0,
					minuteMin: 0,
					controlType: 'select',
					gotoCurrent: true
				}).datetimepicker('setDate', d).focus();
			}
			function show_email_<?php echo $field_ui_id;?>(id, table, moduleid, time)
			{
				$.fancybox.showLoading();
				$.ajax({
					type: 'POST',
					url: '<?php echo $extradir;?>/input/fieldtypes/<?php echo $field[4];?>/ajax_preview.php',
					cache: false,
					data: { field_ui_id: '<?php echo $field_ui_id;?>', id : id, table: table, moduleid: moduleid, time: time, dir: '<?php echo $extradir;?>/input/fieldtypes/<?php echo $field[4];?>/', languageID: '<?php echo $choosenAdminLang;?>', caID: '<?php echo $_GET['caID'];?>' },
					success: function(data) {
						$.fancybox.hideLoading();
						$.fancybox(data);
					}
				});
			}
			function show_report_<?php echo $field_ui_id;?>(id, table, moduleid, time, page)
			{
				if(!page) page = 0;
				$.fancybox.showLoading();
				$.ajax({
					type: 'POST',
					url: '<?php echo $extradir;?>/input/fieldtypes/<?php echo $field[4];?>/ajax_report.php',
					cache: false,
					data: { field_ui_id: '<?php echo $field_ui_id;?>', id : id, table: table, moduleid: moduleid, time: time, dir: '<?php echo $extradir;?>/input/fieldtypes/<?php echo $field[4];?>/', languageID: '<?php echo $choosenAdminLang;?>', page: page, caID: '<?php echo $_GET['caID'];?>' },
					success: function(data) {
						$.fancybox.hideLoading();
						$.fancybox('<h2><?php echo $formText_ReportForReminder_fieldtype.': ';?>' + time + '</h2>' + data);
					}
				});
			}
			function abort_time_<?php echo $field_ui_id;?>(id, table, moduleid, time, _this)
			{
				$.fancybox.showLoading();
				$.ajax({
					type: 'POST',
					url: '<?php echo $extradir;?>/input/fieldtypes/<?php echo $field[4];?>/ajax_abort.php',
					cache: false,
					data: { id : id, table: table, moduleid: moduleid, time: time },
					success: function(data) {
						if(data=='OK') {
							data = '<?php echo $formText_AbortCompleted_fieldtype;?>';
							$(_this).attr('src','<?php echo $extradir;?>/input/fieldtypes/<?php echo $field[4];?>/images/document.png');
							$(_this).attr('title','<?php echo $formText_showReport_fieldtype;?>');
							$(_this).attr('onclick',$(_this).attr('onclick').replace('abort_time','show_report'));
							$(_this).parent('div.do').addClass('done').removeClass('do');
						}
						if(data=='ERROR') data = '<?php echo $formText_ErrorOccuredWhileAborting_fieldtype;?>';
						$.fancybox.hideLoading();
						$.fancybox(data);
					}
				});
			}
			//Date.prototype.ddmmyyyyhhss = function() {
			//   var yyyy = this.getFullYear().toString();
			//   var mm = (this.getMonth()+1).toString(); // getMonth() is zero-based
			//   var dd  = this.getDate().toString();
			//   var hh = this.getHours();
			//   var ss = this.getMinutes();
			//   return (dd[1]?dd:"0"+dd[0]) + "-" + (mm[1]?mm:"0"+mm[0]) + "-" + yyyy + " " + hh + ":" + (ss>9?ss:"0"+ss);
			//};
			//var d = new Date();
			//d.setDate(d.getDate()+1)
			//d.setHours(12,0,0,0);
			//console.log(d.ddmmyyyyhhss());
			<?php if(isset($ob_javascript)) { $ob_javascript .= " ".ob_get_clean(); } ?>
			</script>
		</div>
		<br clear="all"/>
	</div>
	<div class="item" style="text-align:right; display:none;"><input type="button" value="<?php echo $formText_AbortSending_fieldtype;?>" disabled style="width:100px;"></div>
</div>
</div>