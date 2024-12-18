<?php
function formatDate($date) {
    global $formText_NotSet_output;
    if ($date == '0000-00-00' || !$date || empty($date)) return $formText_NotSet_output;
    return date('d.m.Y', strtotime($date));
}
$customerId = $_POST['customerId'];
$showAll = false;
if(isset($_POST['showAll']) && $_POST['showAll']){
    $showAll = true;
}
$defaultCount = 10;
$perPageDefault = 50;
$showUntil = $defaultCount;
if(isset($_POST['showUntil']) && intval($_POST['showUntil'])>0){
    $showUntil = intval($_POST['showUntil']);
}

$invoice_count = 0;
$s_sql = "SELECT * FROM prospect WHERE customerId = ? ORDER BY id DESC";
$o_query = $o_main->db->query($s_sql, array($customerId));
if($o_query && $o_query->num_rows()>0) {
 $invoice_count = $o_query->num_rows();
}

if($showAll){
    $o_query = $o_main->db->query($s_sql, array($customerId));
} else  {
    $sql = "SELECT * FROM prospect WHERE customerId = ? ORDER BY id DESC LIMIT ".$showUntil." OFFSET 0";
    $o_query = $o_main->db->query($sql, array($customerId));
}
if($o_query) {
    $showingNow = $o_query->num_rows();
    $rows = $o_query->result_array();
}

?>
<?php if($moduleAccesslevel > 10) { ?><button class="addEntryBtn edit-prospect-btn" data-prospect-id="0" data-customer-id="<?php echo $customerId; ?>"><?php echo $formText_AddProspect_output;?></button><?php } ?>

<table class="table table-bordered table-striped">
    <tr>
        <th><?php echo $formText_CreatedDate_output; ?></th>
        <th><?php echo $formText_ResponsiblePerson_output; ?></th>
        <th><?php echo $formText_Status_output; ?></th>
        <th><?php echo $formText_ProspectType_output; ?></th>
        <th>&nbsp;</th>
        <!-- <th>&nbsp;</th> -->
    </tr>
    <?php
    foreach($rows as $row){
        $s_sql = "SELECT * FROM contactperson WHERE id = ?";
        $o_query = $o_main->db->query($s_sql, array($row['employeeId']));
        if($o_query && $o_query->num_rows()>0) {
            $responsiblePerson = $o_query->row_array();
        }
        $s_sql = "SELECT * FROM prospecttype WHERE id = ?";
        $o_query = $o_main->db->query($s_sql, array($row['prospecttypeId']));
        $prospectType = $o_query ? $o_query->row_array() : array();

        $s_sql = "SELECT * FROM prospectcampaign WHERE id = ?";
        $o_query = $o_main->db->query($s_sql, array($row['prospectCampaignId']));
        $prospectCampaign = $o_query ? $o_query->row_array() : array();

        $status = "";
        if($row['closed'] == 0) {
            $status = $formText_Active_Output;
        } else {
            if($row['statusAfterClosed'] == 1){
                $status = $formText_Closed_output." - ".$formText_Sale_output;
            } else if ($row['statusAfterClosed'] == 2) {
                $status = $formText_Closed_output." - ".$formText_Refused_output;
            } else if ($row['statusAfterClosed'] == 3) {
                $status = $formText_Closed_output." - ".$formText_WillContact_output;
            } else if ($row['statusAfterClosed'] == 4) {
                $status = $formText_Closed_output." - ".$formText_ResultNotRelevant_output;
            }
        }
    ?>
        <tr>
            <td><?php echo formatDate($row['created']); ?></td>
            <td><?php echo $responsiblePerson['name']." ".$responsiblePerson['middlename']." ".$responsiblePerson['lastname']; ?></td>
            <td><?php echo $status; ?></td>
            <td>
                <?php echo $prospectType['name'];
                if($prospectCampaign){
                    echo " - ".$prospectCampaign['name'];
                }
                 ?>
            </td>
            <td>
                <a href="#" class="showContactPoints" data-prospect-id="<?php echo $row['id']?>"><?php echo $formText_ViewDetails_output; ?></a>
            </td>
            <!-- <td>
                <span class="edit-prospect-btn glyphicon glyphicon-edit" data-prospect-id="<?php echo $row['id']?>" data-customer-id="<?php echo $customerId;?>"></span>
                <span class="delete-prospect-btn glyphicon glyphicon-trash" data-prospect-id="<?php echo $row['id']?>" data-delete-msg="<?php echo $formText_ConfirmDelete_output;?>"></span>
            </td> -->
        </tr>
    <?php } ?>
</table>
<?php if($invoice_count > $showingNow) {?>
<div class="dropdownShowRow">
    <?php echo $formText_Showing_output." ".$showingNow." ".$formText_Of_output." ".$invoice_count;?>
    <?php if($invoice_count-$showingNow >= $perPageDefault){ ?>
        <a href="#" class="invoiceShowNext"><?php echo $formText_Show_output." ".$perPageDefault." ".$formText_More_output;?></a>
    <?php } ?>
    <a href="#" class="invoiceShowAll"><?php echo $formText_ShowAll_output;?></a>
</div>
<?php } ?>
<style>
.edit-prospect-btn {
    margin-bottom: 10px;
}
</style>
<script type="text/javascript">
    $(".invoiceShowAll").unbind("click").bind("click", function(e){
        e.preventDefault();
        var data = {
            customerId: <?php echo $customerId;?>,
            showAll: true
        };
        ajaxCall('invoice_list', data, function(json) {
            $(".invoices_content").html(json.html).slideDown();
        });
    })
    $(".invoiceShowNext").unbind("click").bind("click", function(e){
        e.preventDefault();
        var data = {
            customerId: <?php echo $customerId;?>,
            showUntil: <?php echo $showingNow+$perPageDefault;?>
        };
        ajaxCall('invoice_list', data, function(json) {
            $(".invoices_content").html(json.html).slideDown();
        });
    })
    $(".edit-prospect-btn").unbind("click").bind("click", function(e){
		e.preventDefault();
        fw_loading_start();
        var cid = $(this).data("prospect-id");
        if(cid === undefined) cid = 0;
        $.ajax({
            cache: false,
            type: 'POST',
            dataType: 'json',
            url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=edit_prospect";?>',
            data: { fwajax: 1, fw_nocss: 1, customerId: '<?php echo $customerId;?>', cid: cid},
            success: function(obj){
                $('#popupeditboxcontent').html('');
                $('#popupeditboxcontent').html(obj.html);
                out_popup = $('#popupeditbox').bPopup(out_popup_options);
                $("#popupeditbox:not(.opened)").remove();
                fw_loading_end();
            }
        });
    })
</script>
