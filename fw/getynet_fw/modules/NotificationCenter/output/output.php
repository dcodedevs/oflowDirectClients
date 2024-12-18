<?php
return;
?>
<?php
require __DIR__ . '/output_functions.php';

//get not seen count
$parameters = array("userID"=> $variables->userID, "countOnly"=>1, "unseen"=>1);
$notSeenCount = fw_get_notifications($o_main, $parameters);

?>
<div class="fw_link_box" id="fw_notificationcenter_content">
    <a href="#" class="fw_notificationcenter_header_button " id="fw_notificationcenter_header_button">
        <span class="fas fa-bell"></span>
        <span class="request-count countWrapper active" <?php if($notSeenCount == 0) { ?> style="display:none"<?php } ?>><?php echo $notSeenCount;?></span>
    </a>
    <div class="fw_notificationcenter_dropdown">
        <div class="fw_notification_wrapper">
            <div class="fw_notification_title"><?php echo $formText_Notifications_notification;?></div>
            <div class="fw_notification_error"></div>
            <div class="fw_notification_list">
    			<div class="loading"><div class="lds-ring"><div></div><div></div><div></div><div></div></div></div>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/output_javascript.php'; ?>
