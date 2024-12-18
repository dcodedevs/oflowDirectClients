<?php
session_start();
$s_file = __DIR__."/../../../languages/".$_POST['dlang'].".php";
if(is_file($s_file)) include($s_file);
$s_file = __DIR__."/../../../languages/".$_POST['lang'].".php";
if(is_file($s_file)) include($s_file);

define('BASEPATH', realpath(__DIR__.'/../../../../../').DIRECTORY_SEPARATOR);
require_once(BASEPATH.'elementsGlobal/cMain.php');

if(isset($_COOKIE['username'], $_COOKIE['sessionID'], $_POST['method']))
{
	if(!isset($_SESSION['user_details'])) $_SESSION['user_details'] = array();
	// Loading API lib
	$apiLib = __DIR__ .'/../../../includes/APIconnector.php';
	if(!function_exists("APIconnectorUser") && file_exists($apiLib)) require_once ($apiLib);

    $o_fw_user_session = APIconnectorUser('usersessionget', $_COOKIE['username'], $_COOKIE['sessionID']);
	$response = json_decode($o_fw_user_session);
    if(isset($response, $response->data) && !isset($response->error))
	{
        $userID = $response->data->userID;
    }
    if($userID)
	{
		require __DIR__ . '/output_functions.php';
		
    	// Variables
    	$username = $_COOKIE['username'];
    	$sessionID = $_COOKIE['sessionID'];
    	$method = $_POST['method'];
    	$data = json_encode(array());
    	$caID = $_POST['caID'];

    	// Handle method
    	switch($method)
    	{
    		case 'update_seen':
                $result = 0;
                $parameters = array("userID"=> $userID);
                $result = fw_set_notifications_seen($o_main, $parameters);

                $data = array();
                $data['result'] = $result;
                $data = json_encode($data);
    		break;

    		case 'update_pressed':
                $result = 0;

                $parameters = array("userID"=> $userID, "notificationID"=> $_POST['notification_id']);
                $result = fw_set_notification_pressed($o_main, $parameters);

                $data = array();
                $data['result'] = $result;
                $data = json_encode($data);
    		break;
            case 'add_notification':
                // $result = 0;
                // $parameters = array("userID"=> $userID);
                // $result = fw_set_notifications_seen($o_main, $parameters);
				//
                // $data = array();
                // $data['result'] = $result;
                // $data = json_encode($data);
    		break;

    		case 'get_unseen_notification_count':
                $result = 0;

				//get not seen count
				$parameters = array("userID"=> $userID, "countOnly"=>1, "unseen"=>1);
				$notSeenCount = fw_get_notifications($o_main, $parameters);

				$s_fw_account_upgrade_time = '';
				$s_fw_account_upgrade_check = BASEPATH.'upgrade';
				$s_fw_account_upgrade_lock_check = BASEPATH.'upgrade_lock';
				$b_fw_account_upgrade = $b_fw_account_upgrade_lock = FALSE;
				if(is_file($s_fw_account_upgrade_check))
				{
					$b_fw_account_upgrade_lock = is_file($s_fw_account_upgrade_lock_check);
					$o_query = $o_main->db->get('accountinfo');
					$v_accountinfo = $o_query ? $o_query->row_array() : array();

					$v_response = json_decode(APIconnectorAccount('account_upgrade_check', $v_accountinfo['accountname'], $v_accountinfo['password']),TRUE);
					if(isset($v_response['error'], $v_response['status']) && $v_response['status'] == 0)
					{
						// remove upgrade file
						if(!$b_fw_account_upgrade_lock)
						{
							unlink($s_fw_account_upgrade_check);
							$b_fw_account_upgrade = FALSE;
						}
					} else {
						$s_fw_account_upgrade_time = $v_response['data'];
						if($v_response['data'] != file_get_contents($s_fw_account_upgrade_check))
						{
							include(BASEPATH.'modules/Languages/input/includes/ftp_commands.php');
							ftp_file_put_content('/upgrade', $v_response['data']);
						}
						$b_fw_account_upgrade = TRUE;
					}
				}

				$data = array();
                $data['result'] = $notSeenCount;
				$data['upgrade'] = ($b_fw_account_upgrade?1:0);
				if($b_fw_account_upgrade) $data['upgrade_text'] = $formText_AccountUpgradeWillBePerformedOn_Framework.": ".$s_fw_account_upgrade_time;
				$data['upgrade_lock'] = ($b_fw_account_upgrade_lock?1:0);
				if($b_fw_account_upgrade_lock) $data['upgrade_text'] = '<center><h3>Account upgrade in progress</h3></center>';
                $data = json_encode($data);
    		break;

            case 'get_notifications':
				$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
				$per_page = 50;
				$offset = ($page - 1)*$per_page;
                $parameters = array("userID"=> $userID, "seen"=>1, "page"=>$page, "per_page"=>$per_page);
                $notifications = fw_get_notifications($o_main, $parameters);

				//get total count of notifications
				$parameters = array("userID"=> $userID, "countOnly"=>1);
				$totalCount = fw_get_notifications($o_main, $parameters);

				//unseen notifications
                $parameters = array("userID"=> $userID, "unseen"=>1);
                $new_notifications = fw_get_notifications($o_main, $parameters);

                $fw_notificationTotalCount = count($notifications) + count($new_notifications);
                ob_start();
                if($fw_notificationTotalCount == 0) {
					if(!$_POST['entriesOnly']){
	                    ?>
	                    <div class="fw_notification_nonotification"><?php echo $formText_NoNotifications_notification;?></div>
	                    <?php
					}
                } else {
					if(count($new_notifications) > 0){
						if(!$_POST['entriesOnly']){
							?>
				            <div class="fw_notification_subtitle"><?php echo $formText_New_notification;?></div>
							<?php
						}
						foreach($new_notifications as $notification) {
	                        $notificationImage = "";
	                        $notificationImage = "../elementsGlobal/avatar_placeholder.jpg";

	                        if($notification['created_by_user_id'] > 0){
								if(!isset($_SESSION['user_details'][$notification['created_by_user_id']]))
        			            {
                        			$data = json_decode(APIconnectorUser("userdetailsget", $username, $sessionID, array('USER_ID' => $notification['created_by_user_id'])));
    								$image_json = json_decode($data->image);
    								if(is_object($image_json)){
    									$data->image = "";
    								} else {
    									$data->image = $image_json[0];
    								}
                                    $_SESSION['user_details'][$notification['created_by_user_id']] = $data;
                                } else {
                                    $data = $_SESSION['user_details'][$notification['created_by_user_id']];
                                }
    							if($data->image != ""){
    								$notificationImage = 'https://pics.getynet.com/profileimages/'.$data->image;
    							}
	                			$fullname = trim(trim($data->name.' '.$data->middle_name).' '.$data->last_name);

	                        } else {

	                        }

							$notificationUrl = "";
							if($notification['content_table'] == "postfeed" && $notification['content_id'] > 0) {
								$notificationUrl = $_SERVER['PHP_SELF']."/../../../../../index.php?pageID=".$_POST['pageID']."&accountname=".$_POST['accountname']."&companyID=".$_POST['companyID'].(1==$_POST['url_share']?'':'&caID='.$_GET['caID'])."&module=Frontpage&folderfile=output&folder=output&postid=".$notification['content_id'];
							} else if($notification['content_table'] == "group_page" && $notification['content_id'] > 0) {
								$notificationUrl = $_SERVER['PHP_SELF']."/../../../../../index.php?pageID=".$_POST['pageID']."&accountname=".$_POST['accountname']."&companyID=".$_POST['companyID'].(1==$_POST['url_share']?'':'&caID='.$_GET['caID'])."&module=GroupPage&folder=output&folderfile=output&inc_obj=details&cid=".$notification['content_id'];
							} else if($notification['content_table'] == "feedback_comment" && $notification['content_id'] > 0) {
								$o_query = $o_main->db->query("SELECT id, content_id FROM feedback_comment WHERE id = '".$o_main->db->escape_str($notification['content_id'])."'");
								$v_comment = $o_query ? $o_query->row_array() : array();
								$v_post_id = 0;
								if($v_comment){
									$v_post_id = $v_comment['content_id'];
								}
								$notificationUrl = $_SERVER['PHP_SELF']."/../../../../../index.php?pageID=".$_POST['pageID']."&accountname=".$_POST['accountname']."&companyID=".$_POST['companyID'].(1==$_POST['url_share']?'':'&caID='.$_GET['caID'])."&module=Frontpage&folderfile=output&folder=output&postid=".$v_post_id."&commentid=".$notification['content_id'];
							}
	                        ?>
	                        <div class="fw_notification_item <?php if(!$notification['is_pressed']) echo 'not_pressed';?>" data-notification-id="<?php echo $notification['id'];?>" data-href="<?php echo $notificationUrl?>">
	                            <div class="fw_notification_image"><img src="<?php echo $notificationImage;?>" alt=""/></div>
	                            <div class="fw_notification_info">
	                                <div class="fw_notification_info_wrapper">
	                                    <div class="fw_notification_info_text">
	                                        <b><?php echo $memberRequest['companyName']?></b>
	                                        <?php echo $notification['text']?>
	                                        <div class="fw_notification_info_text_image"></div>
	                                        <div class="clear"></div>
	                                    </div>
	                                </div>
	                            </div>
	                            <div class="clear"></div>
	                        </div>
	                        <?php
	                    }
					}
					if(!$_POST['entriesOnly']){
						?>
			            <div class="fw_notification_subtitle"><?php echo $formText_Earlier_notification;?></div>
						<?php
					}
                    foreach($notifications as $notification) {
                        $notificationImage = "";
                        $notificationImage = "../elementsGlobal/avatar_placeholder.jpg";

                        if($notification['created_by_user_id'] > 0){
							if(!isset($_SESSION['user_details'][$notification['created_by_user_id']]))
    			            {
                    			$data = json_decode(APIconnectorUser("userdetailsget", $username, $sessionID, array('USER_ID' => $notification['created_by_user_id'])));
								$image_json = json_decode($data->image);
								if(is_object($image_json)){
									$data->image = "";
								} else {
									$data->image = $image_json[0];
								}
                                $_SESSION['user_details'][$notification['created_by_user_id']] = $data;
                            } else {
                                $data = $_SESSION['user_details'][$notification['created_by_user_id']];
                            }
							if($data->image != ""){
								$notificationImage = 'https://pics.getynet.com/profileimages/'.$data->image;
							}
                			$fullname = trim(trim($data->name.' '.$data->middle_name).' '.$data->last_name);

                        } else {

                        }

						$notificationUrl = "";
						if($notification['content_table'] == "postfeed" && $notification['content_id'] > 0) {
							$notificationUrl = $_SERVER['PHP_SELF']."/../../../../../index.php?pageID=".$_POST['pageID']."&accountname=".$_POST['accountname']."&companyID=".$_POST['companyID'].(1==$_POST['url_share']?'':'&caID='.$_GET['caID'])."&module=Frontpage&folderfile=output&folder=output&postid=".$notification['content_id'];
						} else if($notification['content_table'] == "feedback_comment" && $notification['content_id'] > 0) {
							$o_query = $o_main->db->query("SELECT id, content_id FROM feedback_comment WHERE id = '".$o_main->db->escape_str($notification['content_id'])."'");
							$v_comment = $o_query ? $o_query->row_array() : array();
							$v_post_id = 0;
							if($v_comment){
								$v_post_id = $v_comment['content_id'];
							}
							$notificationUrl = $_SERVER['PHP_SELF']."/../../../../../index.php?pageID=".$_POST['pageID']."&accountname=".$_POST['accountname']."&companyID=".$_POST['companyID'].(1==$_POST['url_share']?'':'&caID='.$_GET['caID'])."&module=Frontpage&folderfile=output&folder=output&postid=".$v_post_id."&commentid=".$notification['content_id'];
						}
                        ?>
                        <div class="fw_notification_item <?php if(!$notification['is_pressed']) echo 'not_pressed';?>" data-notification-id="<?php echo $notification['id'];?>" data-href="<?php echo $notificationUrl?>">
                            <div class="fw_notification_image"><img src="<?php echo $notificationImage;?>" alt=""/></div>
                            <div class="fw_notification_info">
                                <div class="fw_notification_info_wrapper">
                                    <div class="fw_notification_info_text">
                                        <b><?php echo $memberRequest['companyName']?></b>
                                        <?php echo $notification['text']?>
                                        <div class="fw_notification_info_text_image"></div>
                                        <div class="clear"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="clear"></div>
                        </div>
                        <?php
                    }
					if(($offset + $per_page) < $totalCount) {
						?>
						<div class="fw_load_more_notifications fw_text_link_color" data-page="<?php echo $page;?>"><?php echo $formText_LoadMore_notification;?></div>
						<?php
					}
					?>
					<?php
                }
                $returnHtml =  ob_get_contents();
                ob_end_clean();
                $data = array();
                $data['html'] = $returnHtml;
                $data = json_encode($data);
            break;
    	}
    }

	echo $data;
}
