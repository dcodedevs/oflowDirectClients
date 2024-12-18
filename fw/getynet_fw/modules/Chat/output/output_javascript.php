<script type="text/javascript">
var sendFilesListProxy = [];
var fwchat;
var out_popup_chat;
var out_popup_options_chat={
	follow: [true, true],
	fadeSpeed: 0,
	modalClose: false,
	escClose: false,
	closeClass:'b-close',
	onOpen: function(){
		$(this).addClass('opened');
		//$(this).find('.b-close').on('click', function(){out_popup.close();});
	},
	onClose: function(){
		if($(this).hasClass("close-reload")){
			fwchat.update();
			fwchat.update_channel(true);

			if($(this).data("channel-id") > 0){
				fwchat.show_channel($(this).data('channel-id'), $(this).data('access-level'));
			}
		}
		$(this).removeClass('opened');
	}
};

function chatcallbackOnFileUpload(data)
{
	sendFilesListProxy.push({
		'upload_id': data.result.chatfileupload_files[0].upload_id,
		'file_path': data.result.chatfileupload_files[0].url,
		'file_name': data.result.chatfileupload_files[0].name
	});
}

$(document).ready(function()
{

	var origTitle = document.title;
	$(window).on('focus', function() {
	    document.title = origTitle;
	});
	/*
	---------------------------------------------------------------------

	Properties

	---------------------------------------------------------------------
	*/

	var FWChat = function () {

		// API
		this.apiUrl = '<?php echo $variables->account_framework_url; ?>getynet_fw/modules/Chat/output/output_ajax.php'; // API URL

		// Operational variables
		this.currentThreadID = 0; // User id with whom currently there is active chat window
		this.current_channel_id = 0;
		this.current_channel_thread = 0;
		this.message_id = 0;
		this.recentListLoaded = false;
		this.unread = 0;
		this.recentLimit = 100; // this should be update to load new recents on scroll (infinity scroll)
		this.checkfrom = 0;
		this.checkto = 0;
		this.currentThreadHtml = '';
		this.manualScroll = false;
		this.loading = false;
		this.recentsLoaded = false; // must be added
		this.contactListHidden = false;
		this.sendFilesList = sendFilesListProxy;
		this.channels_loaded = false; // must be added
		this.company_id = '<?php echo $l_main_company_id;?>';


		this.channel_date_from_cmp = new Date();
		this.channel_date_to_cmp = '';
		this.channel_date_from = '';
		this.channel_date_to = '';
		this.channel_fsp_from = 0;
		this.channel_fsp_to = 0;
		this.channel_status_filter = 1;
		this.channel_access_level = 0;
		this.channel_deactivate_comment_as_message = 0;


		// Refresh related variables
		this.interval = 0;
		this.timeSinceRefresh = 0; // Keep track sinche last refresh
		this.refreshTime = 2000; // When active thread is opened
		this.refreshTimeLong = 30000; // When not in active chat

		this.interval_channel = 0;
		this.time_since_refresh_channel = 0; // Keep track sinche last refresh
		this.refresh_time_channel = 2000; // When active thread is opened
		this.refresh_time_long_channel = 30000; // When not in active channel

		this.userlist_per_page = 100;
		this.userlist_page = 1;
		this.userlist_search = '';
	}

	/*
	---------------------------------------------------------------------

	Make HTML

	---------------------------------------------------------------------
	*/

	FWChat.prototype.makeHTML = function(type, data, options) {

		var self = this;
		var output = '';
		var image_default = '<img src="<?php echo $variables->account_root_url;?>elementsGlobal/avatar_placeholder.jpg">';
		var image_default_group = '<img src="<?php echo $variables->account_root_url;?>elementsGlobal/avatar_placeholder.jpg">';

		if(typeof options === 'undefined') options = {};

		if (type == 'message') {
			for (key in data) {
				// Image
				var imageHtml = image_default;
				if (data[key].sender_image_large) {
					imageHtml = '<img' + (data[key].portrait ? ' class="portrait"' : '') + ' src="https://pics.getynet.com/profileimages/' + data[key].sender_image_large + '">';
				}
				if(typeof data[key].companyname === 'undefined' || !data[key].companyname) data[key].companyname = '';
				output +=
					'<li class="main received">' +
						'<div class="image">' +
							'<div class="user_image">'+
								imageHtml +
							'</div>' +
						'</div>' +
						'<div class="user">' +
							'<span class="sender">' + data[key].sender_name +
							(data[key].msgdatetime != '' ? ', ' + data[key].msgdatetime : '') +
							(data[key].companyname != '' ? ' | ' + data[key].companyname : '') +
							'</span>' +
						'</div>' +
						'<div class="message">' +
							'<div class="blob">' + data[key].message + '</div>' +
						'</div>' +
					'</li>';
			}
		}

		if (type == 'recent') {
			for (var key in data.recent) {

				var activeClass = '';
				var unread = 0;

				// Is chat active
				if (data.recent[key].sender == self.currentThreadID) {
					activeClass = 'active';
				}

				// Is there unread messages?
				if(data.data) {
					for(var i = 0; i < data.data.length; i++) {
						if (data.data[i].sender == data.recent[key].sender) {
							unread = parseInt(data.data[i].unread);
						}
					}
				}

				// Image
				var imageHtml = image_default;
				if (data.recent[key].image) {
					imageHtml = '<img' + (data.recent[key].portrait ? ' class="portrait"' : '') + ' src="https://pics.getynet.com/profileimages/' + data.recent[key].image + '">';
				}
				//group chat
				var groupChat = '';
				if(data.recent[key].group) {
					groupChat = 'groupChat';
					imageHtml = image_default_group;
				}

				// Output
				output +=
					'<li class="' + (unread > 0 ? 'unreadThread' : '') + '">' +
						'<a href="" data-user-id="' + data.recent[key].sender + '" class="' + activeClass + ' '+groupChat+'">' +
							'<span class="image">' +
								'<span class="user_image">' +
									imageHtml +
								'</span>' +
							'</span>' +

							'<span class="name">' + data.recent[key].name + '</span>' +
							(unread > 0 ? '<span class="unread fw_load_button_color">' + unread  + '</span>' : '');

							if(!data.recent[key].group) {
								output += '<span class="status status-' + data.recent[key].status + '"></span>';
							}
				output +=	'</a>' +
					'</li>';
			}
		}

		if (type == 'user_details')
		{
			// Image
			var imageHtml = image_default;
			if (data.image) {
				imageHtml = '<img' + (data.portrait ? ' class="portrait"' : '') + ' src="https://pics.getynet.com/profileimages/' + data.image + '">';
			}
			// Output
			output +=
				'<span class="user">' +
					'<span class="image">' +
						'<span class="user_image">' +
							imageHtml +
						'</span>' +
					'</span>' +

					'<span class="name">' + data.fullname + '</span>' +
					// '<span class="status status-' + data.status + '"></span>' +
				'</span><span class="fas fa-user-plus addGroupChat fw_icon_color" data-id="'+data.id+'" title="<?php echo $formText_AddGroup_groupchat;?>"></span>';
		}

		if (type == 'channel') {
			for (var key in data.channels) {

				var activeClass = '';
				var unread = 0;

				// Is chat active
				if (data.channels[key].id == self.current_channel_id) {
					activeClass = 'active';
				}

				// Image
				/*var imageHtml = image_default;
				if (data.channels[key].image) {
					imageHtml = '<img src="https://pics.getynet.com/profileimages/' + data.channels[key].image[2] + '">';
				}*/

				// Output
				output +=
					'<li class="' + (data.channels[key].unread > 0 ? 'unreadThread' : '') + '">' +
						'<a href="" data-channel-id="' + data.channels[key].id + '" data-access-level="' + data.channels[key].access_level + '" class="' + activeClass + '">' +
							/*'<span class="image">' +
								'<span class="crop">' +
									imageHtml +
								'</span>' +
							'</span>' +*/

							'<span class="name">' + data.channels[key].name + '</span>' +
							(data.channels[key].unread > 0 ? '<span class="unread">' + data.channels[key].unread  + '</span>' : '') +
							<?php /*
							(data.channels[key].admin_access == 1 ? '<span class="channel-settings fw_text_icon_color actionIcon glyphicon glyphicon-edit" title="<?php echo $formText_ChannelAdminSettings_Framework;?>"></span>' : '') + */?>
							'<span class="user-settings glyphicon glyphicon-pencil fw_text_icon_color actionIcon" title="<?php echo $formText_UserSettings_Framework;?>"></span>' +
						'</a>' +
					'</li>';
			}
		}

		if(type == 'channel_message')
		{
			var msg = '';
			var msg_hide;
			var msg_date;
			var msg_class;
			var msg_limit = 3;
			var msg_count = data.length;
			var msg_regular_output;

			for(key in data)
			{
				msg_regular_output = (self.message_id == 0 || (self.message_id > 0 && data[key].parent_id > 0 && data[key].is_detached > 0));

				// Image
				var imageHtml = image_default;
				if (data[key].user_image_large) {
					imageHtml = '<img' + (data[key].portrait ? ' class="portrait"' : '') + ' src="https://pics.getynet.com/profileimages/' + data[key].user_image_large + '">';
				} else {
					//imageHtml = '<span class="initials">' + data[key].initials + '</span>';
				}
				msg_hide = ((data[key].parent_id > 0 && data[key].is_detached == 0 && (msg_count - key) > msg_limit) ? true : false);
				msg_class = (data[key].author == 1 ? ' author' : '') + (data[key].messages ? ' is_childs' : '');
				msg_class+= ((data[key].level > 0 && data[key].is_detached == 0) ? ' child-' + data[key].level : '') + (options.parent_id ? ' thread-' + options.parent_id : '');
				msg_class+= ((msg_hide || options.hide) ? ' toggle_hide' : '');

				msg_date = new Date(data[key].created);
				if(msg_date > self.channel_date_to_cmp || (msg_date == self.channel_date_to_cmp && data[key].fsp > self.channel_fsp_to))
				{
					self.channel_date_to = data[key].created;
					self.channel_date_to_cmp = msg_date;
					self.channel_fsp_to = data[key].fsp;
				}
				if(msg_date < self.channel_date_from_cmp || (msg_date == self.channel_date_from_cmp && data[key].fsp < self.channel_fsp_from))
				{
					self.channel_date_from = data[key].created;
					self.channel_date_from_cmp = msg_date;
					self.channel_fsp_from = data[key].fsp;
				}

				if(data[key].parent_id == 0 || (self.channel_deactivate_comment_as_message == 0 && self.current_channel_thread == 0))
				{
					output +=
					'<li class="main received' + msg_class + ' msg-' + data[key].message_id + '">' +
						'<div class="image">' +
							'<div class="user_image">'+
								imageHtml +
							'</div>' +
						'</div>' +
						'<div class="user">' +
							'<span class="sender">' + data[key].created_by +
							(data[key].date_short != '' ? ', ' + data[key].date_short : '') +
							(data[key].is_detached > 0 ? ' | <span class="open-thread" data-id="' + data[key].message_id + '"><?php echo $formText_CommentOnPreviousPost_Chat;?></span>' : '') +
							'</span>' +
						'</div>' +
						'<div class="message">' +
							'<div class="blob">' + data[key].message + '</div>' +
						'</div>' +
						'<div class="action"><span class="glyphicon glyphicon-comment do-comment"></span></div>' +
						'<div class="comment"><textarea data-id="' + data[key].message_id + '"></textarea><button class="add-comment"><?php echo $formText_Send_Chat2; ?> <i class="fas fa-paper-plane"></i></button></div>' +
					'</li>';
				}
				if(data[key].parent_id > 0 && $('#fw_chat_messages .child-' + data[key].level + '.msg-' + data[key].message_id).length == 0)
				{
					msg_class+= (data[key].level > 0 ? ' child-' + data[key].level : '');
					msg =
					'<li class="main received' + msg_class + ' msg-' + data[key].message_id + '">' +
						'<div class="image">' +
							'<div class="user_image">'+
								imageHtml +
							'</div>' +
						'</div>' +
						'<div class="user">' +
							'<span class="sender">' + data[key].created_by +
							(data[key].date_short != '' ? ', ' + data[key].date_short : '') +
							'</span>' +
						'</div>' +
						'<div class="message">' +
							'<div class="blob">' + data[key].message + '</div>' +
						'</div>' +
						'<div class="action"><span class="glyphicon glyphicon-comment do-comment"></span></div>' +
						'<div class="comment"><textarea data-id="' + data[key].message_id + '"></textarea><button class="add-comment"><?php echo $formText_Send_Chat2; ?> <i class="fas fa-paper-plane"></i></button></div>' +
					'</li>'+
					'<li class="new-comment-' + data[key].message_id + '"></li>';
					$('#fw_chat_messages .new-comment-' + self.message_id).before(msg);
				}

				if(data[key].messages)
				{
					if(data[key].level == 0 && data[key].messages.length > 3)
					{
						output += '<li class="show_previous_comments" data-id="' + data[key].message_id + '">Show previous comments (' + (data[key].messages.length - msg_limit) + ')</li>';
					}
					output += self.makeHTML('channel_message', data[key].messages, { hide: (msg_hide || options.hide), parent_id: (options.parent_id ? options.parent_id : data[key].message_id) });
				}
				if(data[key].parent_id == 0 || (self.channel_deactivate_comment_as_message == 0 && data[key].is_detached == 0))
				output += '<li class="new-comment-' + data[key].message_id + '"></li>';
			}
		}

		return output;

	}

	/*
	---------------------------------------------------------------------

	Show chat alert

	---------------------------------------------------------------------
	*/
	FWChat.prototype.add_alert = function(type, message, show_alert, make_empty)
	{
		var self = this;
		if(make_empty !== undefined) $('#fw_chat_alerts_container .chat_alerts').slideUp(100).empty();
		if(type != 'clean') $('#fw_chat_alerts_container .chat_alerts').append('<div class="item ui-corner-all ' + type + '" role="alert"><button type="button" class="close"><span>&times;</span></button>' + message + '</div>');
		if(show_alert !== undefined) self.show_alert();
	}
	FWChat.prototype.show_alert = function()
	{
		if($('#fw_chat_alerts_container .chat_alerts').length) $('#fw_chat_alerts_container .chat_alerts').slideDown(500);
	}

	FWChat.prototype.add_group_chat = function(user_id)
	{
		var active_groups = this.getActiveGroups();

		var self = this;
		fw_loading_start();
		var data = { fwajax: 1, fw_nocss: 1, user_id: user_id, active_sets: active_groups, fw_url:"<?php echo $variables->account_framework_url;?>", current_user_id: '<?php echo $variables->userID?>', languageID: '<?php echo $variables->languageID?>', languageDir: '<?php echo $variables->languageDir?>', defaultLanguageID: '<?php echo $variables->defaultLanguageID?>'}
		$.ajax({
			type: 'POST',
			cache: false,
			dataType: 'json',
			url: "<?php echo $variables->account_framework_url?>getynet_fw/modules/Chat/output/ajax.add_group_chat.php?pageID=<?php echo $_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".(!$variables->fw_url_share?$_GET['caID']:$_COOKIE[$_GET['accountname'].'_caID']);?>",
			data: data,
			success: function(json) {
				fw_click_instance = false;
				if(json.error !== undefined)
				{
					$.each(json.error, function(index, value){
						var _type = Array("error");
						if(index.length > 0 && index.indexOf("_") > 0) _type = index.split("_");
						self.add_alert(_type[0], value);
					});
					self.show_alert();
					fw_loading_end();
				} else {
					$('#popupeditboxcontent').html('');
					$('#popupeditboxcontent').html(json.html);
					out_popup_chat = $('#popupeditbox').bPopup(out_popup_options_chat);
					$("#popupeditbox:not(.opened)").remove();
				}
			}
		}).fail(function() {
			self.add_alert("error", "<?php echo $formText_ErrorOccurredRetrievingData_Framework;?>", true, true);
			fw_loading_end();
			fw_click_instance = false;
		});
	}
	FWChat.prototype.add_user_to_group_chat = function(channel_id)
	{
		var active_groups = this.getActiveGroups();
		var self = this;
		fw_loading_start();
		var data = { fwajax: 1, fw_nocss: 1, channel_id: channel_id, active_sets: active_groups, fw_url:"<?php echo $variables->account_framework_url;?>", current_user_id: '<?php echo $variables->userID?>', languageID: '<?php echo $variables->languageID?>', languageDir: '<?php echo $variables->languageDir?>', defaultLanguageID: '<?php echo $variables->defaultLanguageID?>'  }
		$.ajax({
			type: 'POST',
			cache: false,
			dataType: 'json',
			url: "<?php echo $variables->account_framework_url?>getynet_fw/modules/Chat/output/ajax.add_group_chat.php?pageID=<?php echo $_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".(!$variables->fw_url_share?$_GET['caID']:$_COOKIE[$_GET['accountname'].'_caID']);?>",
			data: data,
			success: function(json) {
				fw_click_instance = false;
				if(json.error !== undefined)
				{
					$.each(json.error, function(index, value){
						var _type = Array("error");
						if(index.length > 0 && index.indexOf("_") > 0) _type = index.split("_");
						self.add_alert(_type[0], value);
					});
					self.show_alert();
					fw_loading_end();
				} else {
					$('#popupeditboxcontent').html('');
					$('#popupeditboxcontent').html(json.html);
					out_popup_chat = $('#popupeditbox').bPopup(out_popup_options_chat);
					$("#popupeditbox:not(.opened)").remove();
				}
			}
		}).fail(function() {
			self.add_alert("error", "<?php echo $formText_ErrorOccurredRetrievingData_Framework;?>", true, true);
			fw_loading_end();
			fw_click_instance = false;
		});
	}

	/*
	---------------------------------------------------------------------

	Get recent conversations

	---------------------------------------------------------------------
	*/
	FWChat.prototype.getRecent = function(callback) {
		$.ajax({
			type: "POST",
			url: this.apiUrl,
			data: {
				'method': 'getRecents',
				'caID': '<?php echo (!$variables->fw_url_share?$_GET['caID']:$_COOKIE[$_GET['accountname'].'_caID']);?>',
				'limit': this.recentLimit,
				'from': 0
			},
			cache: false,
			dataType: "json",
			success: function(data)
			{
				callback(data);
			}
		});
	}

	/*
	---------------------------------------------------------------------

	Get channel list

	---------------------------------------------------------------------
	*/

	FWChat.prototype.get_channel_list = function(from, callback) {
		$.ajax({
			type: "POST",
			url: this.apiUrl,
			data: {
				'method': 'get_channel_list',
				'caID': '<?php echo (!$variables->fw_url_share?$_GET['caID']:$_COOKIE[$_GET['accountname'].'_caID']);?>',
				'limit': this.recentLimit,
				'from': 0,
				'status': this.channel_status_filter
			},
			cache: false,
			dataType: "json",
			success: function(data)
			{
				callback(data);
			}
		});
	}

	/*
	---------------------------------------------------------------------

	Load more recents

	---------------------------------------------------------------------
	*/

	FWChat.prototype.loadMoreRecents = function() {

	}

	/*
	---------------------------------------------------------------------

	Get chat data

	---------------------------------------------------------------------
	*/
	FWChat.prototype.getChat = function(options, callback) {
		$.ajax({
			type: "POST",
			url: this.apiUrl,
			data: {
				'method': 'getChat',
				'caID': '<?php echo (!$variables->fw_url_share?$_GET['caID']:$_COOKIE[$_GET['accountname'].'_caID']);?>',
				'userid': options.userid,
				'beforeDate': options.beforeDate
			},
			cache: false,
			dataType: "json",
			success: function(data)
			{
				callback(data);
			}
		});
	}

	/*
	---------------------------------------------------------------------

	Get channel data

	---------------------------------------------------------------------
	*/
	FWChat.prototype.get_channel = function(options, callback) {
		$.ajax({
			type: "POST",
			url: this.apiUrl,
			data: {
				'method': 'get_channel',
				'caID': '<?php echo (!$variables->fw_url_share?$_GET['caID']:$_COOKIE[$_GET['accountname'].'_caID']);?>',
				'channel_id': options.channel_id,
				'date_from': options.date_from,
				'fsp_from': options.fsp_from,
				'date_to': options.date_to,
				'fsp_to': options.fsp_to
			},
			cache: false,
			dataType: "json",
			success: function(data)
			{
				callback(data);
			}
		});
	}

	/*
	---------------------------------------------------------------------

	Get channel data

	---------------------------------------------------------------------
	*/
	FWChat.prototype.get_channel_thread = function(options, callback) {
		$.ajax({
			type: "POST",
			url: this.apiUrl,
			data: {
				'method': 'get_channel_thread',
				'caID': '<?php echo (!$variables->fw_url_share?$_GET['caID']:$_COOKIE[$_GET['accountname'].'_caID']);?>',
				'channel_id': options.channel_id,
				'message_id': options.message_id
			},
			cache: false,
			dataType: "json",
			success: function(data)
			{
				callback(data);
			}
		});
	}

	/*
	---------------------------------------------------------------------

	Show recent

	---------------------------------------------------------------------
	*/
	FWChat.prototype.showRecent = function(selector) {
		var self = this;
		self.getRecent(function(data) {
			$(selector).html(self.makeHTML('recent', data));
			self.recentsLoaded = true;
		});
	}

	/*
	---------------------------------------------------------------------

	Show user details

	---------------------------------------------------------------------
	*/
	FWChat.prototype.show_user_details = function(){
		var self = this;

		if(self.currentThreadID > 0)
		{
			$.ajax({
				type: "POST",
				url: this.apiUrl,
				data: {
					'method': 'get_user_info',
					'user_id': self.currentThreadID
				},
				cache: false,
				dataType: "json",
				success: function(data){
					$('#fw_chat_message_header').html(self.makeHTML('user_details', data));
				},
				cache: false
			});
		} else {
			$('#fw_chat_message_header').html('');
		}
	}

	/*
	---------------------------------------------------------------------

	Show user details

	---------------------------------------------------------------------
	*/
	FWChat.prototype.show_channel_details = function(channel){
		var self = this;
		var output = '';
		if(self.current_channel_id > 0)
		{
			<?php
			 /*?>var img_html = '';
			if(channel.image)
			{
				img_html = '<img src="https://pics.getynet.com/profileimages/' + data.image[0] + '">';
			}
			*/?>
			// Output
			output +=
				'<span class="channel_info">' +
					<?php /*?>'<span class="image"><span class="crop">' + img_html + '</span></span>' + */?>

					'<span class="name">' + channel.name + '</span>';
			if(channel.type == 2){
				output += '<span class="fas fa-user-plus addGroupChat fw_icon_color" data-channel-id="'+self.current_channel_id+'"  title="<?php echo $formText_EditGroup_groupchat;?>"></span>';

				output += '<span class="current_group_chat_info">'+channel.groupchat_members.length+' <?php echo $formText_InGroup_chat2;?><span class="current_group_chat_info_hover">';
				$(channel.groupchat_members).each(function(index, itemCur){
					output += '<div class=""><span class="image"><span class="user_image"><img src="https://pics.getynet.com/profileimages/'+itemCur.image+'" alt="" border="0" /></span></span><span class="name">'+itemCur.name+" "+itemCur.middle_name+" "+itemCur.last_name+'</span><div class="clear"></div></div>';
				})
				output += '</span></span>';
			}
			output += '</span>';
		}
		$('#fw_chat_message_header').html(output);

		$(".current_group_chat_info").on('hover', function(){
			$(".current_group_chat_info").addClass("active");
		}, function(){
			$(".current_group_chat_info").removeClass("active");
		});
	}

	/*
	---------------------------------------------------------------------

	Show channel list

	---------------------------------------------------------------------
	*/
	FWChat.prototype.show_channel_list = function(from, selector) {
		var self = this;
		self.get_channel_list(from, function(data) {
			$(selector).html(self.makeHTML('channel', data));
			self.channels_loaded = true;
		});
	}

	/*
	---------------------------------------------------------------------

	Show contacts

	---------------------------------------------------------------------
	*/
	FWChat.prototype.showContacts = function(append) {
		var chatEl = this;
		var refresh = 1;
		var active_groups = this.getActiveGroups();
		if($(".fw_chat_left .fw_chat_contact_list_search input").length > 0) {
			chatEl.userlist_search = $(".fw_chat_left .fw_chat_contact_list_search input").val();
		}
		$.ajax({
			type: "POST",
			cache: false,
			url: "<?php echo $variables->account_framework_url; ?>getynet_fw/modules/Chat/output/ajax.get_userlist.php",
			data: {
				set: active_groups.set,
				company: active_groups.company,
				accountname: '<?php echo $_GET['accountname'];?>',
				caID: '<?php echo (!$variables->fw_url_share?$_GET['caID']:$_COOKIE[$_GET['accountname'].'_caID']);?>',
				dlang: '<?php echo $variables->defaultLanguageID; ?>',
				lang: '<?php echo $variables->languageID;?>',
				refresh: refresh,
				page: this.userlist_page,
				per_page: this.userlist_per_page,
				companyID: '<?php echo $_GET['companyID']?>',
  			  	search: this.userlist_search,
				show_department: 1
			},
			success: function (data) {
				if(append){
					$(".fw_chat_left .fw_chat_list_tab_contacts_list .showMoreInChatWrapper").remove();
					$('.fw_chat_left .fw_chat_list_tab_contacts_list').append(data);
				} else {
					$('.fw_chat_left .fw_chat_list_tab_contacts_list').html(data);
				}
				$(".fw_chat_left .fw_peopleSearchInfo .fw_totalPeople").html($(".fw_chat_left .fw_chat_list_tab_contacts_list #fwcl_list").data("total-count"));
				if(chatEl.userlist_search != ""){
					$(".fw_chat_left .fw_peopleSearchInfo .fw_filteredPeople").html($(".fw_chat_left .fw_chat_list_tab_contacts_list #fwcl_list").data("search-count"));
					$(".fw_chat_left .fw_peopleSearchInfo .fw_searched_span").show();
					$(".fw_chat_left .fw_peopleSearchInfo .fw_totalpeople_span").hide();
				} else {
					$(".fw_chat_left .fw_peopleSearchInfo .fw_searched_span").hide();
					$(".fw_chat_left .fw_peopleSearchInfo .fw_totalpeople_span").show();
				}
				$(".fw_chat_left .fw_chat_list_tab_contacts_list .showMoreInChat").off("click").on("click", function(){
					chatEl.userlist_page++;
				  	$(this).parents(".showMoreInChatWrapper").html('<div class="loading"><div class="lds-ring"><div></div><div></div><div></div><div></div></div></div>').css({"margin-top":"-25px"});
					chatEl.showContacts(true);
				})

				var timeoutNew = null;
				$(".fw_chat_left .fw_chat_list_tab_contacts_list .departmentInfo").off("mouseenter").on("mouseenter", function(e){
					e.stopPropagation();
					$(this).parents("li").find(".departments").addClass("active");
					clearTimeout(timeoutNew);
				})
				$(".fw_chat_left .fw_chat_list_tab_contacts_list .departmentInfo").off("mouseleave").on("mouseleave", function(e){
					e.stopPropagation();
					var el = $(this);
					timeoutNew = setTimeout(function(){
						el.parents("li").find(".departments").removeClass("active");
					}, 200)
				})
				$(".fw_chat_left .fw_chat_list_tab_contacts_list .departments").off("mouseover").on("mouseover", function(e){
					e.stopPropagation();
					$(this).addClass("active");
					clearTimeout(timeoutNew);
				})
				$(".fw_chat_left .fw_chat_list_tab_contacts_list .departments").off("mouseleave").on("mouseleave", function(e){
					e.stopPropagation();
					var el = $(this);
					timeoutNew = setTimeout(function(){
						el.removeClass("active");
					}, 200)
				})

			},
			cache: false
		});
	}

	/*
	---------------------------------------------------------------------

	Show contactsets

	---------------------------------------------------------------------
	*/
	FWChat.prototype.showContactset = function() {
		$.ajax({
			url: "<?php echo $variables->account_framework_url; ?>getynet_fw/modules/Chat/output/ajax.get_contactset.php",
			data: {
				accountname: '<?php echo $_GET['accountname'];?>',
				caID: '<?php echo (!$variables->fw_url_share?$_GET['caID']:$_COOKIE[$_GET['accountname'].'_caID']);?>',
				lang: '<?php echo $variables->languageID;?>',
				preselectAll: 1
			},
			success: function (data) {
				$('.fw_chat_left .fw_chat_contact_list_filter').html(data);

				$('.fw_chat_left #fwcl_chat_groups input').on('change', function() {
					var active_groups = FWChat.prototype.getActiveGroups();
					if(!$(this).hasClass('showall')) {

						FWChat.prototype.showContacts();
						FWChat.prototype.updateActiveGroupCount(active_groups);
						if (active_groups.count < active_groups.countAll) {
							$('.fw_chat_left #fwcl_chat_groups .showall').prop('checked', false);
						}
						else {
							$('.fw_chat_left #fwcl_chat_groups .showall').prop('checked', true);
						}

					}
					if($(this).hasClass('showall')) {
						if($(this).prop('checked')) $('.fw_chat_left #fwcl_chat_groups input').prop('checked', true);
						else $('.fw_chat_left #fwcl_chat_groups input').prop('checked', false);
						active_groups = FWChat.prototype.getActiveGroups();
						FWChat.prototype.showContacts();
						FWChat.prototype.updateActiveGroupCount(active_groups);
					}
				});

			},
			cache: false
		});
	}

	/*
	---------------------------------------------------------------------

	Show main company box

	---------------------------------------------------------------------
	*/
	FWChat.prototype.showMainCompany = function() {
		$.ajax({
			url: "<?php echo $variables->account_framework_url; ?>getynet_fw/modules/Chat/output/ajax.get_main_company.php",
			data: {
				accountname: '<?php echo $_GET['accountname'];?>',
				caID: '<?php echo (!$variables->fw_url_share?$_GET['caID']:$_COOKIE[$_GET['accountname'].'_caID']);?>',
				lang: '<?php echo $variables->languageID;?>',
				main_company_id: this.company_id
			},
			success: function (data) {
				$('.fw_chat_input_controls_right').prepend(data);
			},
			cache: false
		});
	}

	/*
	---------------------------------------------------------------------

	Get active groups

	---------------------------------------------------------------------
	*/
	FWChat.prototype.getActiveGroups = function() {
			var set = [];
			var company = [];
			var count = 0;
			var countAll = $('.fw_chat_left #fwcl_chat_groups input').length - 1;

			$(".fw_chat_left #fwcl_chat_groups input:checked").each(function () {
				if(!$(this).hasClass('showall')) {
					set.push($(this).attr('data-setid'));
					company.push($(this).attr('data-companyid'));
					count++;
				}

			});

			var active_groups = {
				set: set,
				company: company,
				count: count,
				countAll: countAll
			};

			return active_groups;
	}

	/*
	---------------------------------------------------------------------

	Update active group count

	---------------------------------------------------------------------
	*/
	FWChat.prototype.updateActiveGroupCount = function(active_groups) {
		$('.fw_chat_left #fwcl_chat_groups_button .selected').html(active_groups.count);
	}
	/*
	---------------------------------------------------------------------

	Show chat

	---------------------------------------------------------------------
	*/
	FWChat.prototype.change_company = function (company_id) {

		var self = this;

		// Set current company id
		self.company_id = company_id;

		$.ajax({
			type: "POST",
			url: this.apiUrl,
			data: {
				'method': 'change_company',
				'caID': '<?php echo (!$variables->fw_url_share?$_GET['caID']:$_COOKIE[$_GET['accountname'].'_caID']);?>',
				'company_id': company_id
			},
			cache: false,
			dataType: "json",
			success: function(data){}
		});
	}

	/*
	---------------------------------------------------------------------

	Show chat

	---------------------------------------------------------------------
	*/
	FWChat.prototype.showChat = function (user_id, callback) {

		$('#fw_chat_message_header').html('');
		$('#fw_account').css({'visibility':'hidden'});
		$('#fw_getynet').css({'visibility':'hidden'});
		$('body').addClass("modal-open");

		var self = this;

		// Set current thread (user) ID
		self.currentThreadID = user_id;

		// Deactivate rest
		self.current_channel_id = 0;
		$('#fwcl_channel_list a.active').removeClass('active');


		// If callback doesnt exist
		if (typeof(callback) !== 'function') var callback = function() { };

		// Show chat window
		$('#fw_chat').show();

		// Fix size
		self.fixSize();

		// Show recent list
		self.showRecent('#fwcl_chat_recents_list');

		// "Real" user id provided
		if (user_id > 0) {

			fw_loading_start();
			$('.fw_chat_input').addClass("active");
			// Get chat data
			self.getChat({userid: user_id}, function(data) {
				self.show_user_details();

				// Set vars
				self.checkfrom = data.checkfrom;
				self.checkto = data.checkto;

				// Make HTML
				if(data.is_more == 1)
				{
					self.currentThreadHtml = '<div class="fw_chat_current_thread_info"><a href="" id="fwChatloadPrevious"><?php echo $formText_LoadOlderMessages_Chat2; ?></a></div>';
				} else {
					if(data.data.length > 0)
					{
						self.currentThreadHtml = '<div class="fw_chat_current_thread_info"><?php echo $formText_OlderMessagesNotFound_Chat; ?></div>';
					} else {
						self.currentThreadHtml = '<div class="fw_chat_current_thread_info"><?php echo $formText_MessagesNotFound_Chat; ?></div>';
					}
				}
				self.currentThreadHtml += '<ul>';
				self.currentThreadHtml += self.makeHTML('message', data.data);
				self.currentThreadHtml += '</ul>';

				// Output HTML
				$('#fw_chat_messages').html(self.currentThreadHtml);

				$('#fw_chat_input_controls_send_file').show();
				$('.fw_chat_input_message').show();

				if (fw_collapsed_to_mobile) {
					$('.fw_chat_recents').hide();
					$('.fw_chat_box').show();
					$('.fw_chat_header .back_button').show();
					$('.fw_chat_header .all_messages').hide();
				}

				// Scroll down
				self.scrollToLatest();

				// Callback
				callback();
				fw_loading_end();
			});

		}

		// Show empty chat box
		else {
			if (fw_collapsed_to_mobile) {
				$('.fw_chat_recents').show();
				$('.fw_chat_box').hide();
				$('.fw_chat_header .back_button').hide();
				$('.fw_chat_header .all_messages').show();
			}
			$('#fw_chat_messages').html('<span class="fas fa-comments start-conversation-icon"></span><h3 style="text-align:center; font-size: 20px; line-height: 30px;"><?php echo $formText_NoChatSelected_Chat2; ?></h3>');
			$(".fw_chat_input").removeClass("active");
			callback();
		}
	}

	/*
	---------------------------------------------------------------------

	Show channel

	---------------------------------------------------------------------
	*/
	FWChat.prototype.show_channel = function (channel_id, access_level, callback) {
		$('#fw_account').css({'visibility':'hidden'});
		$('#fw_getynet').css({'visibility':'hidden'});
		$('body').addClass("modal-open");

		var self = this;

		// Set current channel ID
		self.current_channel_id = channel_id;
		self.channel_access_level = access_level;

		// Deactivate rest
		self.currentThreadID = 0;
		$('#fwcl_chat_recents_list a.active').removeClass('active');

		// If callback doesnt exist
		if (typeof(callback) !== 'function') var callback = function() { };

		// Show chat window
		$('#fw_chat').show();

		// Fix size
		self.fixSize();

		// Show channel list
		self.show_channel_list(0, '#fwcl_channel_list');


		// "Selected" channel id provided
		if (channel_id > 0) {
			fw_loading_start();
			$('.fw_chat_input').addClass("active");
			// Get channel data
			self.get_channel({channel_id: channel_id}, function(data) {
				if(data.error){
					self.add_alert("error", data.error, true, true);
					fw_loading_end();
				} else {
					self.channel_deactivate_comment_as_message = data.channel.deactivate_comment_as_new_message;

					self.show_channel_details(data.channel);

					// Make HTML
					if(data.is_more == 1)
					{
						self.currentThreadHtml = '<div class="fw_chat_current_thread_info"><a href="" id="fwChatloadPrevious"><?php echo $formText_LoadOlderMessages_Chat2; ?></a></div>';
					} else {
						if(data.messages.length > 0)
						{
							self.currentThreadHtml = '<div class="fw_chat_current_thread_info"><?php echo $formText_OlderMessagesNotFound_Chat; ?></div>';
						} else {
							self.currentThreadHtml = '<div class="fw_chat_current_thread_info"><?php echo $formText_MessagesNotFound_Chat; ?></div>';
						}
					}
					self.currentThreadHtml += '<ul>';
					self.currentThreadHtml += self.makeHTML('channel_message', data.messages);
					self.currentThreadHtml += '</ul>';

					// Output HTML
					$('#fw_chat_messages').html(self.currentThreadHtml);

					if(access_level == 2) {
						$('#fw_chat_input_controls_send_file').hide();
						$('.fw_chat_input_message').hide();
					} else {
						$('#fw_chat_input_controls_send_file').show();
						$('.fw_chat_input_message').show();
					}

					if (fw_collapsed_to_mobile) {
						$('.fw_chat_recents').hide();
						$('.fw_chat_box').show();
						$('.fw_chat_header .back_button').show();
						$('.fw_chat_header .all_messages').hide();
					}

					// Scroll down
					self.scrollToLatest();
					self.fixSize();

					//update recent
					self.showRecent('#fwcl_chat_recents_list');

					// Callback
					callback();
					fw_loading_end();
				}
			});

		}

		// Show empty chat box
		else {
			if (fw_collapsed_to_mobile) {
				$('.fw_chat_recents').show();
				$('.fw_chat_box').hide();
				$('.fw_chat_header .back_button').hide();
				$('.fw_chat_header .all_messages').show();
			}
			$('#fw_chat_messages').html('<h3><?php echo $formText_NoChatSelected_Chat2; ?></h3><p><?php echo $formText_NoChatSelectedDetails_Chat2; ?></p>');
			$(".fw_chat_input").removeClass("active");
			callback();
		}
	}

	/*
	---------------------------------------------------------------------

	Show thread

	---------------------------------------------------------------------
	*/
	FWChat.prototype.show_thread = function (message_id, callback) {

		var self = this;

		// If callback doesnt exist
		if(typeof(callback) !== 'function') var callback = function(){};

		// "Selected" message id provided
		if(message_id > 0)
		{
			// Get thread data
			self.get_channel_thread({channel_id: self.current_channel_id, message_id: message_id}, function(data){
				// Make HTML
				self.currentThreadHtml = '<div class="fw_chat_current_thread_back"><i class="fas fa-caret-left"></i> <?php echo $formText_BackToChannel_Chat; ?></div>';
				self.currentThreadHtml += '<ul>';
				self.currentThreadHtml += self.makeHTML('channel_message', data.messages);
				self.currentThreadHtml += '</ul>';

				self.current_channel_thread = message_id;

				// Output HTML
				$('#fw_chat_messages').html(self.currentThreadHtml);
				$('.fw_chat_input_message').hide();
				self.fixSize();

				if (fw_collapsed_to_mobile) {
					$('.fw_chat_recents').hide();
					$('.fw_chat_box').show();
					$('.fw_chat_header .back_button').show();
					$('.fw_chat_header .all_messages').hide();
				}

				// Scroll down
				self.scrollToLatest();

				// Callback
				callback();
			});

		}

		// Show empty chat box
		else {
			if (fw_collapsed_to_mobile) {
				$('.fw_chat_recents').show();
				$('.fw_chat_box').hide();
				$('.fw_chat_header .back_button').hide();
				$('.fw_chat_header .all_messages').show();
			}
			$('#fw_chat_messages').html('<h3><?php echo $formText_ThreadNotFound_Chat; ?></h3><p><?php echo $formText_ErrorOccurredHandlingRequest_Chat; ?></p>');
			callback();
		}
	}

	/*
	---------------------------------------------------------------------

	Edit channel settings

	---------------------------------------------------------------------
	*/
	//Channel setting editing and adding new channels removed, use only edit channels in groups People module
	FWChat.prototype.edit_channel_settings = function(channel_id, type) {
		var self = this;
		if(!fw_click_instance)
		{
			$("#fwcl_channel_list li a").removeClass("active");
			$('#fwcl_channel_list li a[data-channel-id="'+channel_id+'"]').addClass("active");
			fw_click_instance = true;
			fw_loading_start();
			var data = { fwajax: 1, fw_nocss: 1, fw_url:"<?php echo $variables->account_framework_url;?>" }
			if(channel_id > 0){
				data.channel_id = channel_id;
				$.ajax({
					type: 'POST',
					cache: false,
					dataType: 'json',
					url: "<?php echo $variables->account_framework_url?>getynet_fw/modules/Chat/output/ajax."+type+".php",
					data: data,
					success: function(json) {
						fw_click_instance = false;
						if(json.error !== undefined)
						{
							$.each(json.error, function(index, value){
								var _type = Array("error");
								if(index.length > 0 && index.indexOf("_") > 0) _type = index.split("_");
								self.add_alert(_type[0], value);
							});
							self.show_alert();
						} else {
							$('.fw_chat_input_message').hide();
							$('#fw_chat_messages').html(json.html);
							if(json.data == undefined) {
								json.data = "";
							}
							var headOutput =
								'<span class="channel_info">' +
									<?php /*?>'<span class="image"><span class="crop">' + img_html + '</span></span>' + */?>

									'<span class="name">' + json.data + '</span>' +
								'</span>';
							$('#fw_chat_message_header').html(headOutput);
							self.fixSize();
						}
						fw_loading_end();
					}
				}).fail(function() {
					self.add_alert("error", "<?php echo $formText_ErrorOccurredRetrievingData_Framework;?>", true, true);
					fw_loading_end();
					fw_click_instance = false;
				});
			} else {
				// type+="_popup";
				// $.ajax({
				// 	type: 'POST',
				// 	cache: false,
				// 	dataType: 'json',
				// 	url: "<?php echo $variables->account_framework_url."index.php?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".(!$variables->fw_url_share?$_GET['caID']:$_COOKIE[$_GET['accountname'].'_caID'])."&getynetaccount=1&module=37&folder=output&modulename=Chat&folderfile=ajax.";?>" + type,
				// 	data: data,
				// 	success: function(json) {
				// 		fw_click_instance = false;
				// 		if(json.error !== undefined)
				// 		{
				// 			$.each(json.error, function(index, value){
				// 				var _type = Array("error");
				// 				if(index.length > 0 && index.indexOf("_") > 0) _type = index.split("_");
				// 				self.add_alert(_type[0], value);
				// 			});
				// 			self.show_alert();
				// 		} else {
				// 			$('#popupeditboxcontent').html('');
				// 			$('#popupeditboxcontent').html(json.html);
				// 			out_popup = $('#popupeditbox').bPopup(out_popup_options);
				// 			$("#popupeditbox:not(.opened)").remove();
				// 		}
				// 		fw_loading_end();
				// 	}
				// }).fail(function() {
				// 	self.add_alert("error", "<?php echo $formText_ErrorOccurredRetrievingData_Framework;?>", true, true);
				// 	fw_loading_end();
				// 	fw_click_instance = false;
				// });
			}

		}
	}

	/*
	---------------------------------------------------------------------

	Save channel settings

	---------------------------------------------------------------------
	*/

	FWChat.prototype.save_channel_settings = function() {
		var self = this;
		if(!fw_click_instance)
		{
			fw_loading_start();
			fw_click_instance = true;
			self.add_alert('clean', '', false, true);

			var $form = $('#channel-update-form');
			$.ajax({
				url: $form.attr("action"),
				cache: false,
				type: "POST",
				dataType: "json",
				data: "fwajax=1&fw_nocss=1&" + $form.serialize(),
				success: function (data) {
					if(data.error !== undefined)
					{
						$.each(data.error, function(index, value){
							var _type = Array("error");
							if(index.length > 0 && index.indexOf("_") > 0) _type = index.split("_");
							self.add_alert(_type[0], value);
						});
						self.show_alert();
					} else {
						self.update_channel(true);
						self.add_alert("info", "<?php echo $formText_ChannelSettingsSavedSuccessfully_Framework;?>", true, true);
					}
					fw_loading_end();
					fw_click_instance = false;
				}
			}).fail(function() {
				self.add_alert("error", "<?php echo $formText_ErrorOccurredSavingChannel_Framework;?>", true, true);
				fw_loading_end();
				fw_click_instance = false;
			});
		}
	}

	/*
	---------------------------------------------------------------------

	Force open contact list (without mouseenter)

	---------------------------------------------------------------------
	*/

	FWChat.prototype.openContactList = function () {

		// Remove transition temporary, so it doesnt screw up fixSize function
		var transition = $('.fw_contact_list').css('transition');
		$('.fw_contact_list').css('transition', '0s');

		// Open contact list
		$('.fw_contact_list').removeClass('collapsed').css('width', '250px').addClass('collapseonleave expanded');

		// If contact list hidden by default
		if ($('.fw_contact_list_hidden').length) {
			this.contactListHidden = true;
			$('.fw_contact_list').removeClass('fw_contact_list_hidden');
		}

		// Add transition back after timeout
		setTimeout(function() {
			$('.fw_contact_list').css('transition', transition)
		}, 1000);

	}

	/*
	---------------------------------------------------------------------

	Reset contact list state (expandes / collapsed) classes (used on chat close)

	---------------------------------------------------------------------
	*/

	FWChat.prototype.resetContactListState = function () {

		if($('.fw_contact_list').hasClass('collapseonleave') && !$('#fw_chat').is(':visible'))
			$('.fw_contact_list').addClass('collapsed').removeClass('collapseonleave expanded').css('width', '70px');

		if (this.contactListHidden)
			$('.fw_contact_list').addClass('fw_contact_list_hidden');

	}

	/*
	---------------------------------------------------------------------

	Update chat

	---------------------------------------------------------------------
	*/
	FWChat.prototype.updateChat = function(callback) {
		var self = this;

		$.ajax({
			type: "POST",
			url: this.apiUrl,
			data: {
				'method': 'updateChat',
				'caID': '<?php echo (!$variables->fw_url_share?$_GET['caID']:$_COOKIE[$_GET['accountname'].'_caID']);?>',
				'limit': this.recentLimit,
				'userid': this.currentThreadID,
				'from': 0
			},
			cache: false,
			dataType: "json",
			success: function(data)
			{
				$('#fw_chat_messages ul').append(self.makeHTML('message', data.data));
				self.scrollToLatest();
				callback();
			}
		});
	}

	/*
	---------------------------------------------------------------------

	Update channel messages

	---------------------------------------------------------------------
	*/
	FWChat.prototype.update_channel_messages = function(callback) {
		var self = this;

		$.ajax({
			type: "POST",
			url: this.apiUrl,
			data: {
				'method': 'get_channel',
				'channel_id': this.current_channel_id,
				'date_from': self.channel_date_to,
				'fsp_from': self.channel_fsp_to
			},
			cache: false,
			dataType: "json",
			success: function(data)
			{
				$('#fw_chat_messages ul').append(self.makeHTML('channel_message', data.messages));
				self.scrollToLatest();
				callback();
			}
		});
	}

	/*
	---------------------------------------------------------------------

	Load previous

	---------------------------------------------------------------------
	*/

	FWChat.prototype.loadPrevious = function(callback) {
		var self = this;

		// This should yet be to integrated correctly
		// self.setScrollStatus('manual');

		if (!self.loading) {
			self.loading = true;
			fw_loading_start();

			setTimeout(function() {
				var firstListBeforeLoad = $('#fw_chat_messages li').first();
				if(self.currentThreadID > 0)
				{
					self.getChat({userid: self.currentThreadID, beforeDate: self.checkfrom}, function(data) {
						self.checkfrom = data.checkfrom;
						if (data.data.length > 0) {
							$('#fw_chat_messages ul').prepend(self.makeHTML('message', data.data) + '<li><hr></li>');
							$('#fw_chat_messages').scrollTop(firstListBeforeLoad.offset().top - 250);
						}
						if(data.is_more == 0) {
							$('#fwChatloadPrevious').replaceWith('<?php echo $formText_OlderMessagesNotFound_Chat;?>');
						}
						setTimeout(function() {
							self.loading = false;
						}, 250);
						fw_loading_end();
					});
				} else {
					self.get_channel({channel_id: self.current_channel_id, date_to: self.channel_date_from, fsp_to: self.channel_fsp_from}, function(data) {
						if(data.error){
							self.add_alert("error", data.error, true, true);
							fw_loading_end();
						} else{
							if (data.messages.length > 0) {
								$('#fw_chat_messages ul').prepend(self.makeHTML('channel_message', data.messages) + '<li><hr></li>');
								$('#fw_chat_messages').scrollTop(firstListBeforeLoad.offset().top - 250);
							}
							if(data.is_more == 0) {
								$('#fwChatloadPrevious').replaceWith('<?php echo $formText_OlderMessagesNotFound_Chat;?>');
							}
							setTimeout(function() {
								self.loading = false;
							}, 250);
							fw_loading_end();
						}
					});
				}
			}, 50);

		}

	}

	/*
	---------------------------------------------------------------------

	Update

	---------------------------------------------------------------------
	*/

	FWChat.prototype.update = function(callback) {
		var self = this;

		// If callback doesnt exist
		if (typeof(callback) !== 'function') var callback = function() { };

		// Get recent
		self.getRecent(function(data) {
			
			if(typeof(data.session_expire) !== 'undefined' && data.session_expire == 1)
			{
				window.location = 'https://www.getynet.com';
				return;
			}

			// Local unread var
			var unread = 0;

			// Check if there are new messages
			if(data.data) {
				for(var i = 0; i < data.data.length; i++) {
					unread += parseInt(data.data[i].unread);
				}
			}
			var totalUnread = parseInt(parseInt(self.unread_channel)+parseInt(self.unread));
			if(totalUnread > 0 && !document.hasFocus()){
				document.title = "("+totalUnread+") "+origTitle;
			} else {
				document.title = origTitle;
			}
			// If local unread count differs from global we need to update
			if (self.unread != unread) {

				// Update unread count
				self.unread = unread;
				$('#fw_chat_header_button .message-count').text(self.unread);
				if(self.unread == 0){
					$('body.alternative #fw_chat_header_button .message-count').hide();
				} else {
					$('body.alternative #fw_chat_header_button .message-count').show();
				}

				// Apply notifcation class for chat button
				if (self.unread > 0 || self.unread_channel) $('#fw_chat_header_button').addClass('unread');
				else $('#fw_chat_header_button').removeClass('unread');

				// Update recent list
				self.showRecent('#fwcl_chat_recents_list');

				// Update chat
				if (self.currentThreadID > 0) {
					self.updateChat(function() {
						callback();
					});
				}
				else {
					callback();
				}
			}
			// If unread count hasn't changed
			else {
				callback();
			}

		});
	}

	FWChat.prototype.update_channel = function(force, callback) {
		var self = this;

		// If callback doesnt exist
		if (typeof(callback) !== 'function') var callback = function() { };
		// If force parameter does not exist
		if (typeof(force) === 'undefined') var force = false;

		// Get channel list
		self.get_channel_list(0, function(data) {

			// Local unread var
			var unread = 0;

			// Check if there are new messages
			if(data.channels) {
				for(var i = 0; i < data.channels.length; i++) {
					// ALI - Disable channels
					//unread += parseInt(data.channels[i].unread);
				}
			}
			var totalUnread = parseInt(parseInt(self.unread_channel)+parseInt(self.unread));
			if(totalUnread > 0 && !document.hasFocus()){
				document.title = "("+totalUnread+") "+origTitle;
			} else {
				document.title = origTitle;
			}
			// If local unread count differs from global we need to update
			if (force || self.unread_channel != unread) {

				// Update unread count
				self.unread_channel = unread;
				$('#fw_chat_header_button .channel-count').text(self.unread_channel);
				if(self.unread_channel == 0){
					$('body.alternative #fw_chat_header_button .channel-count').hide();
				} else {
					$('body.alternative #fw_chat_header_button .channel-count').show();
				}
				// Apply notifcation class for chat button
				if (self.unread > 0 || self.unread_channel) $('#fw_chat_header_button').addClass('unread');
				else $('#fw_chat_header_button').removeClass('unread');

				// Update channel list
				self.show_channel_list(0, '#fwcl_channel_list');

				// Update channel
				if (self.current_channel_id > 0) {
					self.update_channel_messages(function() {
						callback();
					});
				}
				else {
					callback();
				}
			}
			// If unread count hasn't changed
			else {
				callback();
			}
		});
	}

	/*
	---------------------------------------------------------------------

	Send message

	---------------------------------------------------------------------
	*/

	FWChat.prototype.send = function(message, callback) {

		var self = this;

		// If callback doesnt exist
		if(typeof(callback) !== 'function') var callback = function() { };

		if(self.currentThreadID > 0)
		{
			var messages = [];
			messages.push(message);
			$.ajax({
				type: "POST",
				url: this.apiUrl,
				data: {
					'method': 'send',
					'message': messages,
					'userID': self.currentThreadID,
					'company_id': self.company_id
				},
				cache: false,
				dataType: "json",
				success: function(data){
					self.showChat(self.currentThreadID, function() {
						self.setScrollStatus('auto');
						self.scrollToLatest();
					});
					callback();
				}
			});
		} else {
			$.ajax({
				type: "POST",
				url: this.apiUrl,
				data: {
					'method': 'send_channel_message',
					'message': message,
					'channel_id': self.current_channel_id,
					'message_id': self.message_id
				},
				cache: false,
				dataType: "json",
				success: function(data){
					if(data.error)
					{
						self.add_alert('error', data.error, true, true);
					} else {
						self.show_channel(self.current_channel_id, 1, function() {
							self.setScrollStatus('auto');
							self.scrollToLatest();
						});
						callback();
					}
				}
			});
		}
	}

	/*
	---------------------------------------------------------------------

	Send files

	---------------------------------------------------------------------
	*/

	FWChat.prototype.sendFiles = function(callback) {

		var self = this;

		var data = {
			'method': 'sendFiles',
			'filesList': this.sendFilesList,
			'userID': this.currentThreadID
		};
		console.log(data);
		$.ajax({
			type: "POST",
			url: this.apiUrl,
			data: data,
			cache: false,
			dataType: "json",
			success: function(data){
				var message = '';console.log(data);
				data.forEach(function(item, index){
					var itemData = item.split('::');
					message += '<a href="' + itemData[1] + '">' + itemData[2] +'</a>' + "\n";
				});

				self.send(message);

				self.sendFilesList.length = 0;

				$('.fw_chat_filesend .fwaFileupload_FilesList li').remove();
			}
		});

	}

	/*
	---------------------------------------------------------------------

	Fix chat size

	---------------------------------------------------------------------
	*/

	FWChat.prototype.fixSize = function() {
		var wh = window.innerHeight;
		var getynetBluelineHeight = 0;
		if($('body.alternative').length == 0){
		   getynetBluelineHeight = $('#fw_getynet').outerHeight(true);
		}
		var height = wh - getynetBluelineHeight - $('#fw_chat').css('padding-top').replace('px','') - $('.fw_chat_main').css('margin-top').replace('px','') - $('.fw_chat_main').css('margin-bottom').replace('px','') - $('.fw_chat_content').outerHeight(true) + $('.fw_chat_content').height()-10;
		$('.fw_chat_content').height(height);

		// Fix message box size
		this.fixMessageBoxSize();
	}

	/*
	---------------------------------------------------------------------

	Fix message box size

	---------------------------------------------------------------------
	*/

	FWChat.prototype.fixMessageBoxSize = function() {
		$('.fw_chat_messages').css('bottom', $('.fw_chat_input').outerHeight() + 'px');
	}

	/*
	---------------------------------------------------------------------

	Scroll To Latest

	---------------------------------------------------------------------
	*/

	FWChat.prototype.scrollToLatest = function() {
		// This should yet bet integrated and tested, currently not used
		// Idea is no to let chat scroll to bottom when you are reading older
		// messages and new messge comes in (manulScroll is switched on)
		if (!this.manualScroll) {
			$('#fw_chat_messages').scrollTop($('#fw_chat_messages ul').height());
		}
	}

	/*
	---------------------------------------------------------------------

	Scroll To Latest

	---------------------------------------------------------------------
	*/

	FWChat.prototype.setScrollStatus = function(status) {
		if (typeof(status) !== 'undefined') {
			this.manualScroll = (status == 'manual' ? true : false);
		}
		else {
			this.manualScroll = true;
		}
	}

	/*
	---------------------------------------------------------------------

	Auto Refresh

	---------------------------------------------------------------------
	*/

	FWChat.prototype.autoRefresh = function() {
		var self = this;

		// Reset time spent
		self.timeSinceRefresh = 0;

		// Master clock
		self.interval = setInterval(function() {

			// Time since last "real" refresh
			self.timeSinceRefresh += self.refreshTime;

			// If we are in chat, we use short refresh time
			if(self.currentThreadID && self.timeSinceRefresh >= self.refreshTime) {

				// Clear interval
				clearInterval(self.interval);

				// Update recent

				self.update(function() {
					// Refresh
					self.autoRefresh();
				});

			}

			// If we are not in chat we use longer refresh time
			if (self.currentThreadID == 0 && self.timeSinceRefresh >= self.refreshTimeLong) {

				// Clear interval
				clearInterval(self.interval);

				// Update
				self.update(function() {
					// Refresh
					self.autoRefresh();
				});
			}

		}, self.refreshTime); // We use short refresh time for "master clock"

	}


	/*
	---------------------------------------------------------------------

	Auto Refresh Channel

	---------------------------------------------------------------------
	*/

	FWChat.prototype.auto_refresh_channel = function() {
		var self = this;

		// Reset time spent
		self.time_since_refresh_channel = 0;

		// Master clock
		self.interval_channel = setInterval(function() {

			// Time since last "real" refresh
			self.time_since_refresh_channel += self.refresh_time_channel;

			// If we are in chat, we use short refresh time
			if(self.current_channel_id && self.time_since_refresh_channel >= self.refresh_time_channel) {

				// Clear interval
				clearInterval(self.interval_channel);

				// Update recent

				self.update_channel(false, function() {
					// Refresh
					self.auto_refresh_channel();
				});

			}

			// If we are not in chat we use longer refresh time
			if (self.current_channel_id == 0 && self.time_since_refresh_channel >= self.refresh_time_long_channel) {

				// Clear interval
				clearInterval(self.interval_channel);

				// Update
				self.update_channel(false, function() {
					// Refresh
					self.auto_refresh_channel();
				});
			}

		}, self.refresh_time_channel); // We use short refresh time for "master clock"

	}

	/*
	---------------------------------------------------------------------

	Restart autorefresh

	---------------------------------------------------------------------
	*/

	FWChat.prototype.restartAutoRefresh = function() {

		// Clear interval
		clearInterval(this.interval);

		// Start autorefresh
		this.autoRefresh();
	}

	/*
	---------------------------------------------------------------------

	Restart autorefresh_channel

	---------------------------------------------------------------------
	*/

	FWChat.prototype.restart_auto_refresh_channel = function() {

		// Clear interval
		clearInterval(this.interval_channel);

		// Start autorefresh
		this.auto_refresh_channel();
	}

	/*
	---------------------------------------------------------------------

	Close chat

	---------------------------------------------------------------------
	*/

	FWChat.prototype.close = function() {

		// Reset current chat id
		this.currentThreadID = 0;

		// Hide chat
		$('#fw_chat').hide();

		// Reset contact list state
		this.resetContactListState();
	}

	/*
	---------------------------------------------------------------------

	Reset style

	---------------------------------------------------------------------
	*/

	FWChat.prototype.resetStyle = function() {
		$('.fw_chat_box').removeAttr('style');
		$('.fw_chat_recents').removeAttr('style');
	}

	/*
	---------------------------------------------------------------------

	Add listeners

	---------------------------------------------------------------------
	*/

	FWChat.prototype.addListeners = function() {

		var self = this;


		// Click on header button
		$('#fw_chat_header_button').on('click', function(e) {
			e.preventDefault();
			self.showChat(0);
			self.update_channel(true);
		});

		// Close chat alert
		$(document).on('click', '#fw_chat_alerts_container .chat_alerts .item .close',function(e){
			e.preventDefault();
			$(this).closest('.item').remove();
		});

		// Channel status filter change
		$(document).on('change', '#fw_channel_status_filter', function(e) {
			self.channel_status_filter = $(this).val();
			self.update_channel(true);
		});

		// Channel item click
		$(document).on('click', '#fwcl_channel_list a', function(e) {
			e.preventDefault();
			if(!$(e.target).hasClass('user-settings') && !$(e.target).hasClass('channel-settings'))
			{
				self.current_channel_thread = 0;
				self.channel_date_from_cmp = new Date();
				self.channel_date_to_cmp = '';
				self.channel_date_from = '';
				self.channel_date_to = '';
				self.channel_fsp_from = 0;
				self.channel_fsp_to = 0;
				self.show_channel($(this).data('channel-id'), $(this).data('access-level'));
			}
		});

		// Channel user settings click
		$(document).on('click', '#fwcl_channel_list a span.user-settings', function(e) {
			e.preventDefault();
			self.edit_channel_settings($(this).parent().data('channel-id'), 'edit_channel_user_settings');
		});

		// $(document).on('click', '.fw_channel_header .add', function(e) {
		// 	e.preventDefault();
		// 	self.edit_channel_settings(0, 'edit_channel_settings');
		// });

		// Channel settings click
		// $(document).on('click', '#fwcl_channel_list a span.channel-settings', function(e) {
		// 	e.preventDefault();
		// 	self.edit_channel_settings($(this).parent().data('channel-id'), 'edit_channel_settings');
		// });
		$(document).on('click', '#fw_chat_messages .channel-access-add', function(e){
			e.preventDefault();
			$('#fw_chat_messages .channel-access-container').append($('#fw_chat_messages .channel-access-origin').val());
		});
		$(document).on('change', '#fw_chat_messages .channel-access select.channel-type', function(){
			var $_parent = $(this).closest('.channel-access');
			$_parent.find('.choice').addClass('hide');
			$_parent.find('.choice.'+$(this).val()).removeClass('hide');
		});
		$(document).on('click', '#channel-update-form a.fw-btn', function(e) {
			e.preventDefault();
			self.save_channel_settings();
		});

		// Click on contact in contact list
		$(document).on('click', '.fw_chat_left #fwcl_list a', function(e) {
			e.preventDefault();
			self.showChat($(this).data('user-id'));

		});

		// Load previous messages link
		$(document).on('click', '#fwChatloadPrevious', function(e) {
			e.preventDefault();
			self.loadPrevious();
		});

		// Recent chat click
		$(document).on('click', '#fwcl_chat_recents_list a', function(e) {
			e.preventDefault();
			if(!$(this).hasClass("groupChat")){
				self.showChat($(this).data('user-id'));
			} else {
				self.current_channel_thread = 0;
				self.channel_date_from_cmp = new Date();
				self.channel_date_to_cmp = '';
				self.channel_date_from = '';
				self.channel_date_to = '';
				self.channel_fsp_from = 0;
				self.channel_fsp_to = 0;
				self.show_channel($(this).data('user-id'), 1, function(){}, true);
			}
		});

		// Close button
		$(document).on('click', '#fw_chat_close_button', function(e) {
			e.preventDefault();
			self.close();
			$('#fw_account').css({'visibility':'visible'});
			$('#fw_getynet').css({'visibility':'visible'});
			$('body').removeClass("modal-open");
			$(window).trigger('resize');
		});

		// Back button
		$(document).on('click', '#fw_chat_back_button', function(e) {
			e.preventDefault();
			$('.fw_chat_box').hide();
			$('.fw_chat_recents').show();
			$('.fw_chat_header .back_button').hide();
			$('.fw_chat_header .all_messages').show();
		});

		// Return key (enter) in chat input box
		$(document).on('keydown', '#fw_chat_input_textarea', function(e) {
			if(e.which == 13) {
				if ($('#fw_chat_input_controls_checkbox').is(':checked')) {
					e.preventDefault();
					self.message_id = 0;
					self.send($(this).val());
					$(this).val('');
				}
			}
		});

		$(document).on('click', '#fw_chat_messages .user .open-thread', function(e){
			e.preventDefault();
			self.show_thread($(this).data('id'));
		});
		$(document).on('click', '#fw_chat_message_header .addGroupChat', function(e){
			e.preventDefault();
			if($(this).data("channel-id") > 0){
				self.add_user_to_group_chat($(this).data('channel-id'));
			} else {
				self.add_group_chat($(this).data('id'));
			}
		});

		$(document).on('click', '#fw_chat_messages .show_previous_comments', function(e){
			e.preventDefault();
			$('#fw_chat_messages .thread-' + $(this).data('id') + '.toggle_hide').slideToggle();
			$(this).toggle();
		});

		$(document).on('click', '.fw_chat_current_thread_back', function(e){
			e.preventDefault();
			if(self.current_channel_id > 0)
			{
				self.current_channel_thread = 0;
				self.channel_date_from_cmp = new Date();
				self.channel_date_to_cmp = '';
				self.channel_date_from = '';
				self.channel_date_to = '';
				self.channel_fsp_from = 0;
				self.channel_fsp_to = 0;
				self.show_channel(self.current_channel_id, self.channel_access_level);
			}
			if(self.currentThreadID > 0)
			{
				self.showChat(self.currentThreadID);
			}
		});

		$(document).on('click', '#fw_chat_messages .action .do-comment', function(e){
			e.preventDefault();
			var $comment = $(this).closest('li').find('.comment');
			if($(this).is('.open'))
			{
				$comment.hide();
			} else {
				$comment.show().find('textarea').focus();
			}
			$(this).toggleClass('open');
		});

		// Send channel comment on button
		$(document).on('click', '#fw_chat_messages .comment .add-comment', function(e){
			e.preventDefault();
			var $text = $(this).parent().find('textarea');
			self.message_id = $text.data('id');
			self.send($text.val(), function(){ $text.parent().hide(); self.update_channel(true); });
			$text.val('');
		});
		// Send message on send button
		$('#fw_chat_input_controls_button').on('click', function(event) {
			event.preventDefault();
			self.message_id = 0;
			self.send($('#fw_chat_input_textarea').val());
			$('#fw_chat_input_textarea').val('');
		});

		// Change company on company select
		$(document).on('change', '#fw_chat_company_id', function(event) {
			self.change_company($(this).val());
		});

		// Open file upload box
		$('#fw_chat_input_controls_send_file').on('click', function(event) {
			event.preventDefault();
			$('.fw_chat_input_message').hide();
			$('.fw_chat_filesend').show();
			$(window).trigger('resize');
		});

		// Chat file send cancel
		$('#fw_chat_filesend_cancel').on('click', function(event) {
			event.preventDefault();
			$('.fw_chat_filesend').hide();
			$('.fw_chat_input_message').show();
			$(window).trigger('resize');
		});

		// Chat file send
		$('.fw_chat_filesend_button').on('click', function(event) {
			event.preventDefault();
			self.sendFiles(function(data){
			});
			// self.send(JSON.stringify(chatSendFiles));
			$('.fw_chat_filesend').hide();
			$('.fw_chat_input_message').show();
			$(window).trigger('resize');
		});

		// On chat scroll
		$('#fw_chat_messages').on('scroll', function() {
			// self.loadPrevious();
			// This must be remade
		});

		// Chat list switch
		$('#fw_chat_list_changer').on('change', function() {
			// Hide all tabs
			$('[data-chat-list-tab]').hide();

			// Show active tab
			var showTab = $(this).val();
			$('[data-chat-list-tab="' + showTab + '"]').show();

			if(showTab == 'contacts') self.showContacts();

			// Change active states on controls
			$('[data-chat-list-switch-tab]').removeClass('active');
			$(this).addClass('active');

			// Clear chat box
			$('#fw_chat_messages').html('');
			$("#fw_chat_message_header").html('');
			$(".fw_chat_input").removeClass("active");
		});

		// Contact groups filter
		$(document).on('click', '#fwcl_chat_groups_button', function(e) {
			e.preventDefault();
			$(this).parents('.fw_chat_contact_list_filter.filter_groups').find('ul').slideToggle();
			$(this).parents('.fw_chat_contact_list_filter.filter_groups').find('.button').toggleClass('opened');
			$(this).parents('.fw_chat_contact_list_filter.filter_groups').find('.button2').toggleClass('opened');
		});
		var hoveredOverInfo = false;
		$(".channelInfoTrigger").on('hover', function(){
			$(".channelinfo_hover1").addClass("active");
		}, function(){
			setTimeout(function(){
				if(!hoveredOverInfo) {
					$(".channelinfo_hover1").removeClass("active");
				}
			}, 300)
		});
		$(".channelinfo_hover1").on('hover', function(){ hoveredOverInfo = true; }, function(){
			$(".channelinfo_hover1").removeClass("active");
			hoveredOverInfo = false;
		})

		var hoveredOverInfo2 = false;
		$(".channelInfoTrigger2").on('hover', function(){
			$(".channelinfo_hover2").addClass("active");
		}, function(){
			setTimeout(function(){
				if(!hoveredOverInfo2) {
					$(".channelinfo_hover2").removeClass("active");
				}
			}, 300)
		});
		$(".channelinfo_hover2").on('hover', function(){ hoveredOverInfo2 = true; }, function(){
			$(".channelinfo_hover2").removeClass("active");
			hoveredOverInfo2 = false;
		})

		// On window resize keep fixing sizes
		$(window).on('resize', function() {
			self.fixSize();
			self.resetStyle();
		});


		var fw_typingTimer;                //timer identifier
		var fw_doneTypingInterval = 300;  //time in ms, 5 second for example
		var $fw_input = $('.fw_chat_contact_list_search input');

		//on keyup, start the countdown
		$fw_input.off('keyup').on('keyup', function () {
		  clearTimeout(fw_typingTimer);
		  fw_typingTimer = setTimeout(fw_doneTypingSearchContacts, fw_doneTypingInterval);
		});

		//on keydown, clear the countdown
		$fw_input.off('keydown').on('keydown', function () {
		  clearTimeout(fw_typingTimer);
		});

		//user is "finished typing," do something
		function fw_doneTypingSearchContacts () {
		  //do something
		  var searchValue = $fw_input.val();
		  self.userlist_page = 1;
		  self.userlist_search = searchValue;
		  $(".fw_chat_left .fw_chat_list_tab_contacts_list").html('<div class="loading"><div class="lds-ring"><div></div><div></div><div></div><div></div></div></div>');
		  self.showContacts();
		}

		$(".fw_peopleSearchInfo .fw_resetFilter").off("click").on("click", function(){
			$fw_input.val("").keyup();
		})
	}


	/*
	---------------------------------------------------------------------

	Init

	---------------------------------------------------------------------
	*/
	FWChat.prototype.init = function() {

		// Update
		fwchat.update();
		fwchat.update_channel();

		// Starting refresher
		fwchat.autoRefresh();

		// Starting channel refresher
		fwchat.auto_refresh_channel();

		// Load contactset
		fwchat.showContactset();
		// Load main company
		fwchat.showMainCompany();
		// Adding listeners
		fwchat.addListeners();
		// Load all contacts
		//fwchat.showContacts();

	}

	/*
	---------------------------------------------------------------------

	Initialize

	---------------------------------------------------------------------
	*/

	fwchat = new FWChat();
	fwchat.init();
});
</script>
