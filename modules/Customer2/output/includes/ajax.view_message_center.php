<?php
$messageId = $_POST['messageId'];
$customerId = $_POST['customerId'];
if($messageId > 0){
    $sqlNoLimit = "SELECT p.*, CONCAT_WS(' ',c.name, c.middlename, c.lastname) AS customerName
    FROM message_center_message p
    LEFT OUTER JOIN customer c ON c.id = p.customer_id
    WHERE p.id = ? AND c.id = ? ORDER BY p.created DESC";
    $o_query = $o_main->db->query($sqlNoLimit, array($messageId, $customerId));
    $message = $o_query ? $o_query->row_array() : array();
    if($message['message_type'] == 1 && $message['receiver_username'] == $variables->loggID && ($message['read_date'] != "" && $message['read_date'] !="0000-00-00")){
        $sqlNoLimit = "UPDATE message_center_message SET read_date = NOW() WHERE id = ?";
        $o_query = $o_main->db->query($sqlNoLimit, array($message['id']));
    }
    $messageTypes = array($formText_None_output, $formText_FromCustomerPortal_output, $formText_ToCustomerPortal_output, $formText_FromEmployeeInApp_output);
    ?>
    <div class="popupform">
    	<div id="popup-validate-message" style="display:none;"></div>
        <?php /*?>
        <p class="module__text"><?php echo $accountConfigData['moduleText']; ?></p>
        */?>
        <div class="reportform-container">
            <form class="output-form main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=send_message";?>" method="post">
                <input type="hidden" name="fwajax" value="1">
                <input type="hidden" name="fw_nocss" value="1">
                <input type="hidden" name="output_form_submit" value="1">
                <input type="hidden" name="cid" value="<?php print $_POST['cid'];?>">
                <input type="hidden" name="customerId" value="<?php print $_POST['customerId'];?>">
                <table class="table table-fixed">
                    <tr><td width="15%"><b><?php echo $formText_Date_output?></b></td><td><?php echo date("d.m.Y", strtotime($message['created']));?></td></tr>
                    <tr><td><b><?php echo $formText_MessageType_output?></b></td><td><?php echo $messageTypes[intval($message['message_type'])];?></td></tr>
                    <tr><td><b><?php echo $formText_Sender_output?></b></td><td><?php echo $message['sender_name']."</br>".$message['sender_username'];?></td></tr>
                    <tr><td><b><?php echo $formText_Receiver_output?></b></td><td><?php echo $message['receiver_name']."</br>".$message['receiver_username'];?></td></tr>
                    <tr><td><b><?php echo $formText_Message_output?></b></td><td>
                        <?php echo nl2br($message['message']);?>
                    </td></tr>
                </table>
                <div class="form-group">
                    <div class="popupformbtn">
                		<button type="button" class="output-btn b-large b-close"><?php echo $formText_Close_Output;?></button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <?php
}
?>
<style>
.popupform {
    border: 0;
}
</style>
