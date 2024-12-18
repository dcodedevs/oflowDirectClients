<?php 
$cid = isset($_GET['cid']) ? $_GET['cid'] : 0;

$sql = "SELECT * FROM creditor WHERE id = ?";
$o_query = $o_main->db->query($sql, array($cid));
$creditor = $o_query ? $o_query->row_array() : array();

if($creditor) {
    $s_list_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=creditor_list&cid=".$cid;

    
    ?>
    <div id="p_container" class="p_container <?php echo $folderName; ?>">
        <div class="p_containerInner">
            <div class="p_content">
                <div class="p_pageContent">
                    <a href="<?php echo $s_list_link?>" class="output-click-helper optimize back-to-list" style="display: block; margin-bottom:10px; float: left;"><?php echo $formText_BackToCreditor_outpup;?></a>
                    <div class="clear"></div>
                </div>
            </div>
            <div class="creditor_info_row title_row">
                <?php echo $formText_CreditorName_output;?>:
                <b><?php echo $creditor['companyname'];?></b>
            </div>
            <div class="page_table_wrapper">
                <?php 
                
                $sql = "SELECT * FROM creditor_syncing_log WHERE creditor_id = ? AND created >= '2024-03-22' ORDER BY created ASC LIMIT 500";
                $o_query = $o_main->db->query($sql, array($creditor['id']));
                $creditor_syncing_logs = $o_query ? $o_query->result_array() : array();
                ?>
                <table class="table">
                    <?php 
                        foreach($creditor_syncing_logs as $creditor_syncing_log){
                            ?>
                            <tr>
                                <td><?php echo $creditor_syncing_log['created']?></td>
                                <td><?php echo $creditor_syncing_log['log']?></td>
                                <td><?php echo $creditor_syncing_log['number_of_tries']?></td>
                            </tr>
                            <?php
                        }
                    ?>

                </table>
            </div>
        </div>
    </div>
<?php } ?>