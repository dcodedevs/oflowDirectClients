<?php
session_start();
if(isset($_COOKIE['username'], $_COOKIE['sessionID'], $_POST['method']))
{
	if(!isset($_SESSION['user_image_orientation'])) $_SESSION['user_image_orientation'] = array();
	// Loading API lib
	$apiLib = __DIR__ .'/../../../includes/APIconnector.php';
	if(!function_exists("APIconnectorUser") && file_exists($apiLib)) require_once ($apiLib);

	// Variables
	$username = $_COOKIE['username'];
	$sessionID = $_COOKIE['sessionID'];
	$method = $_POST['method'];
	$data = json_encode(array());
	$from = $_POST['from'];
	$limit = $_POST['limit'];
	$caID = $_POST['caID'];

	// Handle method
	switch($method)
	{
		// Get user details
		case 'get_user_info':
			$data = json_decode(APIconnectorUser("userdetailsget", $username, $sessionID, array('USER_ID' => $_POST['user_id'])));
			$data->image = json_decode($data->image);
			$data->image = $data->image[0];
			if(!isset($_SESSION['user_image_orientation'][$_POST['user_id']]))
			{
				$v_size = getimagesize('https://pics.getynet.com/profileimages/'.$data->image);
				$_SESSION['user_image_orientation'][$_POST['user_id']] = ($v_size[0] < $v_size[1]);
			}
			$data->portrait = $_SESSION['user_image_orientation'][$_POST['user_id']];
			$data->fullname = trim(preg_replace('!\s+!', ' ', $data->name.' '.$data->middle_name.' '.$data->last_name));
			$data = json_encode($data);
		break;

		// Change company
		case 'change_company':
			$data = APIconnectorUser("user_main_company_set", $username, $sessionID, array('COMPANY_ID'=> $_POST['company_id']));
		break;

		// Get recent chat list
		case 'getRecents':
			$recents = json_decode(APIconnectorUser("chatunreadget", $username, $sessionID, array('FROM'=> $from, 'LIMIT'=> $limit, 'NEW' => 1)));
			$data = array();
			if($recents)
			{
				foreach($recents->recent as $key => $user)
				{
					$data[$key] = $user;
					if($data[$key]->image == "group") {
						$data[$key]->group = 1;
					}
					$data[$key]->name = trim(preg_replace('!\s+!', ' ', $data[$key]->name.' '.$data[$key]->middle_name.' '.$data[$key]->last_name));
					$data[$key]->image = json_decode($data[$key]->image);
					$data[$key]->image = $data[$key]->image[0];
					if(!isset($_SESSION['user_image_orientation'][$data[$key]->sender]))
					{
						$v_size = getimagesize('https://pics.getynet.com/profileimages/'.$data[$key]->image);
						$_SESSION['user_image_orientation'][$data[$key]->sender] = ($v_size[0] < $v_size[1]);
					}
					$data[$key]->portrait = $_SESSION['user_image_orientation'][$data[$key]->sender];
				}
				$recents->recent = $data;
			}
			$data = json_encode($recents);
		break;

		// Get chat with user
		case 'getChat':

			$beforeDate = '-';
			if($_POST['beforeDate']) $beforeDate = $_POST['beforeDate'];
			$v_messages = json_decode(APIconnectorUser("chat_message_get", $username, $sessionID, array('USER_ID'=>$_POST['userid'],'BEFORE_DATE'=>$beforeDate)),true);
			$v_new_messages = array();
			if($v_messages)
			{
				foreach($v_messages['data'] as $v_message)
				{
					if(!isset($_SESSION['user_image_orientation'][$v_message['sender']]))
					{
						$v_size = getimagesize('https://pics.getynet.com/profileimages/'.$v_message['sender_image_large']);
						$_SESSION['user_image_orientation'][$v_message['sender']] = ($v_size[0] < $v_size[1]);
					}
					$v_message['portrait'] = $_SESSION['user_image_orientation'][$v_message['sender']];
					$v_message['message'] = nl2br($v_message['message']);
					$v_new_messages[] = $v_message;
				}
				$v_messages['data'] = $v_new_messages;
			}

			$data = json_encode($v_messages);
		break;


		// Update chat
		case 'updateChat':

			$v_messages = json_decode(APIconnectorUser("chat_message_get", $username, $sessionID, array('USER_ID'=>$_POST['userid'])),true);
			$v_new_messages = array();
			if($v_messages)
			{
				foreach($v_messages['data'] as $v_message)
				{
					if(!isset($_SESSION['user_image_orientation'][$v_message['sender']]))
					{
						$v_size = getimagesize('https://pics.getynet.com/profileimages/'.$v_message['sender_image_large']);
						$_SESSION['user_image_orientation'][$v_message['sender']] = ($v_size[0] < $v_size[1]);
					}
					$v_message['portrait'] = $_SESSION['user_image_orientation'][$v_message['sender']];
					$v_message['message'] = nl2br($v_message['message']);
					$v_new_messages[] = $v_message;
				}
				$v_messages['data'] = $v_new_messages;
			}

			$data = json_encode($v_messages);

		break;

		// Send chat message
		case 'send':
			$data = APIconnectorUser("chatmessagesetget", $username, $sessionID, array('USER_ID'=>$_POST['userID'], 'MESSAGE'=>$_POST['message'], 'COMPANY_ID'=>$_POST['company_id']));
		break;

		// Send files
		case 'sendFiles':

			$returnData = array();

			foreach ($_POST['filesList'] as $file) {
				$post = array();
				$file_path = realpath(__DIR__.'/../../../../../' . urldecode($file['file_path']));
				$post['Filedata'] = new \CURLFile($file_path);
				$post['Filename'] = $file['file_name'];
				$post['timestamp'] = time();
				$post['token'] = md5('fw-chat-upload' . $post['timestamp']);

				$url = 'https://cfiles.getynet.com/upload.php';
				$ch = curl_init($url);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				$response = curl_exec($ch);
				curl_close($ch);

				$returnData[] = $response;

				// Delete file from server
				unlink($file_path);
			}

			$data = json_encode($returnData);

		break;

		// Get channel list
		case 'get_channel_list':
			$v_param = array('TYPE' => 0, 'STATUS' => $_POST['status']);
			$data = APIconnectorUser("channel_list_get", $username, $sessionID, $v_param);
		break;

		// Get channel data
		case 'get_channel':
			$v_param = array('CHANNEL_ID'=>$_POST['channel_id']);
			if($_POST['date_from'] != "") $v_param['DATE_FROM'] = $_POST['date_from'];
			if($_POST['fsp_from'] != "") $v_param['FSP_FROM'] = $_POST['fsp_from'];
			if($_POST['date_to'] != "") $v_param['DATE_TO'] = $_POST['date_to'];
			if($_POST['fsp_to'] != "") $v_param['FSP_TO'] = $_POST['fsp_to'];
			$v_messages = json_decode(APIconnectorUser("channel_message_get", $username, $sessionID, $v_param), TRUE);
			$v_new_messages = array();
			if($v_messages)
			{
				foreach($v_messages['messages'] as $v_message)
				{
					if(!isset($_SESSION['user_image_orientation'][$v_message['created_by_user_id']]))
					{
						$v_size = getimagesize('https://pics.getynet.com/profileimages/'.$v_message['user_image_large']);
						$_SESSION['user_image_orientation'][$v_message['created_by_user_id']] = ($v_size[0] < $v_size[1]);
					}
					$v_message['portrait'] = $_SESSION['user_image_orientation'][$v_message['created_by_user_id']];
					$v_message['message'] = nl2br($v_message['message']);
					$v_new_messages[] = $v_message;
				}
				$v_messages['messages'] = $v_new_messages;
			}
			//get group chat member infos
			if($v_messages['channel']['id']){
				if($v_messages['channel']['type'] == 2){
					$s_response = APIconnectorUser("channel_get", $username, $sessionID, array('CHANNEL_ID'=>$v_messages['channel']['id']));
					$v_response = json_decode($s_response,true);
					if($v_response['status'] == 1)
					{
						$v_channel = $v_response['channel'];
						$v_access = $v_response['access'];
						$v_channel['group_id'] = $v_access[0]['group_id'];
						$l_edit = 1;
				        $channelError = true;

						$data = json_decode(APIconnectorUser("group_user_get_list", $username, $sessionID, array('group_id'=>$v_channel['group_id'])),true);

						if($data['status'] == 1){
					        $membersToPass = array();
					        $isAdmin = false;
					        $isMember = false;
							$tempMembers = $data['items'];
							foreach($tempMembers as $member){
								$image = json_decode($member['image']);
								$realImage = "";
								if(count($image) > 0){
									$realImage = $image[0];
								}
								$member['image'] = $realImage;
								array_push($membersToPass, $member);
					            // $data = json_decode(APIconnectorUser("userdetailsget", $variables->loggID, $variables->sessionID, array('USERNAME'=>$member['username'])),true);
					            // if($data['username']){
						        //     array_push($memberIds, $data['id']);
								//
					            //     //to not call twice already known info
					            //     $chat_usernames[$data['id']]=$member['username'];
					            //     $chat_names[$data['id']]=$member['name']. " ".$member['middle_name']. " ".$member['last_name'];
					            // }
							}
							$v_messages['channel']['groupchat_members'] = $membersToPass;
						}
				    }

				}
			}
			$data = json_encode($v_messages);
		break;

		// Send channel message
		case 'send_channel_message':
			$v_param = array('CHANNEL_ID'=>$_POST['channel_id'], 'MESSAGE'=>$_POST['message']);
			if(isset($_POST['message_id']) && $_POST['message_id'] > 0) $v_param['PARENT_ID'] = $_POST['message_id'];
			$data = APIconnectorUser("channel_message_add", $username, $sessionID, $v_param);
		break;

		// Send channel message
		case 'get_channel_thread':
			$v_param = array('CHANNEL_ID'=>$_POST['channel_id'], 'MESSAGE_ID'=>$_POST['message_id']);
			$v_messages = json_decode(APIconnectorUser("channel_message_thread_get", $username, $sessionID, $v_param), TRUE);
			$v_new_messages = array();
			if($v_messages)
			{
				foreach($v_messages['messages'] as $v_message)
				{
					if(!isset($_SESSION['user_image_orientation'][$v_message['sender']]))
					{
						$v_size = getimagesize('https://pics.getynet.com/profileimages/'.$v_message['user_image_large']);
						$_SESSION['user_image_orientation'][$v_message['sender']] = ($v_size[0] < $v_size[1]);
					}
					$v_message['portrait'] = $_SESSION['user_image_orientation'][$v_message['sender']];
					$v_message['message'] = nl2br($v_message['message']);
					$v_new_messages[] = $v_message;
				}
				$v_messages['messages'] = $v_new_messages;
			}

			$data = json_encode($v_messages);
		break;

	}

	echo $data;
}
