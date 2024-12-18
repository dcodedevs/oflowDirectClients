<?php
$history_id = $_POST['history_id'];

$s_sql = "SELECT * FROM customerhistoryextsystem WHERE id = ? ORDER BY created ASC";
$o_query = $o_main->db->query($s_sql, array($history_id));
$customerhistoryextsystem = ($o_query ? $o_query->row_array():array());

$s_sql = "SELECT * FROM customer WHERE id = ?";
$o_query = $o_main->db->query($s_sql, array($customerhistoryextsystem['customer_id']));
$customer = ($o_query ? $o_query->row_array():array());

$s_sql = "SELECT * FROM customerhistoryextsystemcategory WHERE id = ?";
$o_query = $o_main->db->query($s_sql, array($customerhistoryextsystem['history_category_id']));
$history_category = ($o_query ? $o_query->row_array():array());

if($history_id > 0){
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
                <table class="table table-fixed">
                    <tr><td><b><?php echo $formText_CustomerName_output;?></b></td><td><?php echo $customer['name']." ".$customer['middlename']." ".$customer['lastname'];?></td></tr>
                    <tr><td><b><?php echo $formText_CategoryName_output;?></b></td><td><?php echo $history_category['name'];?></td></tr>
                    <?php
                    $maxFieldNumber = 10;
                    for($x=1; $x<= $maxFieldNumber; $x++){
                        if($history_category['field_'.$x.'_label'] != ""){
                            ?>
                            <tr><td><b><?php echo $history_category['field_'.$x.'_label'];?></b></td><td><?php echo $customerhistoryextsystem['field_'.$x];?></td></tr>
                            <?php
                        }
                    }
                    ?>
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
