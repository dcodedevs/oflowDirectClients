<?php
function formatDate($date) {
    global $formText_NotSet_output;
    if ($date == '0000-00-00' || !$date || empty($date)) return $formText_NotSet_output;
    return date('d.m.Y', strtotime($date));
}
$prospectId = $_POST['cid'];

$s_sql = "SELECT * FROM prospect WHERE id = ? ORDER BY id DESC";
$o_query = $o_main->db->query($s_sql, array($prospectId));
$prospect = $o_query ? $o_query->row_array() : array();

$s_sql = "SELECT * FROM prospecttype WHERE id = ?";
$o_query = $o_main->db->query($s_sql, array($prospect['prospecttypeId']));
$prospectType = $o_query ? $o_query->row_array() : array();

$s_sql = "SELECT * FROM prospectcampaign WHERE id = ?";
$o_query = $o_main->db->query($s_sql, array($prospect['prospectCampaignId']));
$prospectCampaign = $o_query ? $o_query->row_array() : array();

$rows = array();
$s_sql = "SELECT * FROM prospectcontactpoint WHERE prospectId = ? ORDER BY date ASC";
$o_query = $o_main->db->query($s_sql, array($prospectId));
if($o_query && $o_query->num_rows()>0) {
    $rows = $o_query->result_array();
}
?>
<div class="popupform">
    <div class="popupformTitle"><?php echo $formText_ProspectDetails_output;?></div>
    <div class="line">
        <div class="lineTitle"><?php echo $formText_ProspectType_Output; ?></div>
        <div class="lineInput">
            <?php
            echo $prospectType['name'];
            if($prospectCampaign) {
                echo " - ".$prospectCampaign['name'];
            }
            ?>
        </div>
        <div class="clear"></div>
    </div>
    <div class="line">
        <div class="lineTitle"><?php echo $formText_Value_Output; ?></div>
        <div class="lineInput">
            <?php
            echo number_format($prospect['value'], 2, ",", " ");
            ?>
        </div>
        <div class="clear"></div>
    </div>
    <div class="line">
        <div class="lineTitle"><?php echo $formText_Quantity_Output; ?></div>
        <div class="lineInput">
            <?php
            echo number_format($prospect['quantity'], 2, ",", " ");
            ?>
        </div>
        <div class="clear"></div>
    </div>
    <div class="line">
        <div class="lineTitle"><?php echo $formText_Info_Output; ?></div>
        <div class="lineInput">
            <?php
            echo $prospect['info'];
            ?>
        </div>
        <div class="clear"></div>
    </div>
    <div class="line">
        <div class="lineTitle"><?php echo $formText_Files_Output; ?></div>
        <div class="lineInput">
            <ul>
            <?php
            $files = json_decode($prospect['files'], true);
            foreach($files as $file){
                $fileName = $file[0];
                $fileUrl = $extradomaindirroot.'/../'.$file[1][0];
                if(strpos($file[1][0],'uploads/protected/')!==false)
                {
                    $fileUrl = $extradomaindirroot.'/../'.$file[1][0].'?caID='.$_GET['caID'].'&table=prospect&field=files&ID='.$prospect['id'];
                }
                ?>
                <li>
                    <a href="<?php echo $fileUrl; ?>" download target="_blank">
                        <span class="glyphicon glyphicon-paperclip"></span> <?php echo $fileName; ?>
                    </a>
                </li>
                <?php
            }
            ?>
            </ul>
        </div>
        <div class="clear"></div>
    </div>
    <div class="line">
        <div class="lineTitle"><?php echo $formText_Notes_Output; ?></div>

        <div class="clear"></div>
    </div>

    <?php
    foreach($rows as $row){
    ?>
        <div class="noteWrapper">
            <b><?php echo formatDate($row['date']); ?></b>
            <div><?php echo nl2br($row['text']); ?></div>
        </div>
    <?php } ?>
</div>
<style>
    .noteWrapper {
        margin-top: 10px;
        margin-bottom: 10px;
        padding: 10px;
        background-color: rgb(246, 250, 254);
        border: 1px solid rgb(217, 217, 217);
        border-radius: 5px;
    }
    .popupform, .popupeditform {
        border: 0;
    }
</style>
