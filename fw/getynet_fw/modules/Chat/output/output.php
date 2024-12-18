<?php
return;
?>
<div class="fw_chat" id="fw_chat">
	<div class="fw_chat_main">

		<div id="fw_chat_alerts_container"><div class="chat_alerts"></div></div>
		<div class="fw_chat_header">
			<ul class="fw_chat_header_buttons">
				<li class="back_button"><a href="#" id="fw_chat_back_button"><span class="glyphicon glyphicon-arrow-left"></span> <?php echo $formText_Back_Chat2; ?></a></li>
				<li class="all_messages"><a href="#" class="active"><?php echo $formText_AllMessages_Chat2; ?></a></li>
				<li><a href="#" class="close_button" id="fw_chat_close_button"><?php echo $formText_CloseChat_Chat2;?><span class="">X</span></a></li>
			</ul>
		</div>
		<div class="chat_info_message_wraper"><div class="chat_info_messages"></div></div>

		<div class="fw_chat_search">
			<a href="#" id="fw_chat_search_button"><?php echo $formText_SearchInMessages_Chat2; ?> <span class="icon icon-arrow-right"></span></a>
		</div>

		<div class="fw_chat_content">

			<div class="channelinfo_hover channelinfo_hover1"><?php echo $formText_ChannelInfoPart1_Framework;?><div class="divider"></div><?php echo $formText_ChannelInfoPart2_Framework;?></div>
			<div class="channelinfo_hover channelinfo_hover2"><?php echo $formText_StatusInfoPart1_Framework;?><div class="divider"></div><?php echo $formText_StatusInfoPart2_Framework;?><div class="divider"></div><?php echo $formText_StatusInfoPart3_Framework;?></div>

			<div class="fw_chat_left">

				<div class="fw_channel_recents">
					<div class="fw_channel_header">
						<span class="glyphicon glyphicon-bullhorn fw_icon_color"></span>
						<?php echo $formText_Channels_Chat2;?>
						<span class="fas fa-info-circle fw_icon_color channelInfoTrigger"></span>
						<?php /*
						<span class="add fw_button_color"><?php echo $formText_Add_Framework;?></span>
						*/?>
						<span class="fas fa-info-circle fw_icon_color channelInfoTrigger2 pull-right"></span>
						<select id="fw_channel_status_filter" class="pull-right">
							<option value="1" selected><?php echo $formText_ActiveAll_Framework;?></option>
							<option value="2"><?php echo $formText_HiddenAll_Framework;?></option>
							<?php if($variables->useradmin == 1 || $variables->system_admin == 1) { ?>
							<option value="3"><?php echo $formText_InactiveAll_Framework;?></option>
							<?php } ?>
						</select>
					</div>

					<!--- Channel list -->
					<div class="fw_channel_list_tab">
						<ul class="fw_chat_recents_list channel" id="fwcl_channel_list"></ul>
					</div>
				</div>

				<div class="fw_chat_recents">
					<div class="fw_channel_header">
						<span class="icon icon-chat fw_icon_color"></span>
						<?php echo $formText_DirectMessages_Framework;?>
						<select id="fw_chat_list_changer" class="pull-right">
							<option value="recents" selected><?php echo $formText_RecentChats_Chat2;?></option>
							<option value="contacts"><?php echo $formText_AllContacts_Chat2;?></option>
						</select>
					</div>

					<!--- Recents -->
					<div class="fw_chat_list_tab_recents" data-chat-list-tab="recents">
						<ul class="fw_chat_recents_list" id="fwcl_chat_recents_list"></ul>
					</div>

					<!--- Contacts -->
					<div class="fw_chat_list_tab_contacts" data-chat-list-tab="contacts">
						<div class="fw_chat_contact_list_search">
							<input type="text" class="" value="" placeholder="<?php echo $formText_Search_chat2;?>"/>
						</div>
						<div class="fw_peopleSearchInfo">
							<span class="fw_searched_span" style="display:none;"><span class="fw_filteredPeople">0</span> <?php echo $formText_Of_chat2?> </span>
							<span class="fw_totalPeople">0</span> <span class="fw_totalpeople_span"><?php echo $formText_InTotal_chat2?></span>
							<span class="fw_searched_span" style="display:none;"><?php echo $formText_InSelection_chat2?> <span class="fw_resetFilter fw_text_link_color"><?php echo $formText_ResetSearch_chat2;?></span></span>
						</div>
						<div class="fw_chat_contact_list_filter filter_groups" style="display:none;"></div>
						<div class="fw_chat_list_tab_contacts_list">
			    			<div class="loading"><div class="lds-ring"><div></div><div></div><div></div><div></div></div></div>
						</div>
					</div>
				</div>
			</div>

			<!-- Chat box-->
			<div class="fw_chat_box">

				<!-- Message header  -->
				<div class="fw_chat_message_header" id="fw_chat_message_header"></div>

				<!-- Messages  -->
				<div class="fw_chat_messages" id="fw_chat_messages"></div>

				<!-- Chat input  -->
				<div class="fw_chat_input">

					<!-- Message -->
					<div class="fw_chat_input_message">
						<div class="fw_chat_input_textarea">
							<textarea id="fw_chat_input_textarea" placeholder="<?php echo $formText_TypeYourMessageHere_Chat2;?>.."></textarea>
						</div>
						<div class="fw_chat_input_controls">
							<div class="fw_chat_input_controls_left">
								<input type="checkbox" id="fw_chat_input_controls_checkbox" checked="checked"> <?php echo $formText_SendOnEnter_Chat2; ?>
							</div>
							<div class="fw_chat_input_controls_right">
								<a href="#" class="fw_chat_input_controls_send_file" id="fw_chat_input_controls_send_file">
									<span class="glyphicon glyphicon-paperclip"></span>
									<?php echo $formText_SendFile_Chat2; ?>
								</a>
								<button class="fw_chat_input_controls_button" id="fw_chat_input_controls_button"><?php echo $formText_Send_Chat2; ?></button>
							</div>
						</div>
					</div>

					<!-- File  -->
					<div class="fw_chat_filesend">
						<?php
						$fwaFileuploadConfig = array (
						  'id' => 'chatfileupload',
						  'content_table' => 'chat_upload',
						  'content_field' => 'file',
						  'content_module_id' => 0,
						  'dropZone' => 'block',
						  'callback' => 'chatcallbackOnFileUpload',
						//   'callbackStart' => 'chatcallbackOnFileUploadStart',
						//   'callbackAll' => 'chatcallbackOnFileUploadAll',
						//   'callbackDelete' => 'callbackOnFileDelete'
						);
						  require __DIR__ . '/includes/fileuploadchat/output.php';
						?>

						<div class="fw_chat_filesend_controls">
							<a href="#" class="fw_chat_filesend_cancel" id="fw_chat_filesend_cancel"><?php echo $formText_Cancel_Chat2; ?></a>
							<button class="fw_chat_filesend_button" id="fw_chat_filesend_button"><?php echo $formText_SendFiles_Chat2; ?></button>
						</div>

					</div>

				</div>

			</div>

		</div>
	</div>
</div>
<div id="popupeditbox" class="popupeditbox">
	<span class="button b-close fw_popup_x_color"><span>X</span></span>
	<div id="popupeditboxcontent"></div>
</div>

<?php require __DIR__ . '/output_javascript.php'; ?>
