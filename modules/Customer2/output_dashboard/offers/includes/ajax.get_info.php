<?php
if(!function_exists("include_local")) include(__DIR__."/../../../input/includes/fn_include_local.php");

include_once(__DIR__."/readOutputLanguage.php");

$currentYear = date("Y");
$monthArray = array(
    1=>$formText_January_output,
    2=>$formText_February_output,
    3=>$formText_March_output,
    4=>$formText_April_output,
    5=>$formText_May_output,
    6=>$formText_June_output,
    7=>$formText_July_output,
    8=>$formText_August_output,
    9=>$formText_September_output,
    10=>$formText_October_output,
    11=>$formText_November_output,
    12=>$formText_December_output
);
$currentMonth = date("n");

$departmentActive = false;
$o_query = $o_main->db->query("SELECT * FROM departmentforaccounting WHERE content_status < 2");
$departmentCount = $o_query ? $o_query->num_rows() : 0;
if($departmentCount > 0){
    $departmentActive = true;
}
?>

<div class="output-content-sub-title"><?php echo $formText_LatestReadByCustomer_output;?></div>
<table class="table project-table-fixed">
    <tr>
        <th width="70px"><?php echo $formText_Date_output;?></th>
        <th width="45px"><?php echo $formText_Time_output;?></th>
        <th width="110px"><?php echo $formText_Customer_output;?></th>
        <th ><?php echo $formText_Offer_output;?></th>
    </tr>
    <?php

        $s_sql = "SELECT * FROM repeatingorder_accountconfig WHERE content_status < 2";
        $o_query = $o_main->db->query($s_sql);
        $repeatingorder_accountconfig = ($o_query ? $o_query->row_array() : array());

        $completedProjects = array();

        $o_query = $o_main->db->query("SELECT offer.*, offer_pdf.*, CONCAT_WS(' ', customer.name, customer.middlename, customer.lastname) as customerName, file_links_log.created  FROM offer
            JOIN offer_pdf ON offer_pdf.offer_id = offer.id
            LEFT OUTER JOIN customer ON customer.id = offer.customerId
            JOIN file_links ON file_links.content_id = offer_pdf.id AND file_links.content_table='offer_pdf'
            JOIN file_links_log ON file_links_log.key_used = file_links.link_key AND file_links_log.successful = 1
            WHERE offer.content_status < 2
            GROUP BY offer_pdf.id
            ORDER BY file_links_log.created DESC LIMIT 5");
        $recentReadOffers = $o_query ? $o_query->result_array() : array();
        foreach($recentReadOffers as $recentReadOffer) {

        ?>
        <tr>
            <td width="70px"><?php echo date("d.m.Y", strtotime($recentReadOffer['created']));?></td>
            <td width="45px"><?php echo date("H:i", strtotime($recentReadOffer['created']));?></td>
            <td width="110px"><?php echo $recentReadOffer['customerName'];?></td>
            <td><?php echo $recentReadOffer['offer_headline'];?></td>
        </tr>
    <?php } ?>
</table>

<div class="output-content-sub-title"><?php echo $formText_LatestSentToCustomer_output;?></div>
<table class="table project-table-fixed">
    <tr>
        <th width="70px"><?php echo $formText_Date_output;?></th>
        <th width="45px"><?php echo $formText_Time_output;?></th>
        <th width="110px"><?php echo $formText_Customer_output;?></th>
        <th ><?php echo $formText_Offer_output;?></th>
    </tr>
    <?php

        $s_sql = "SELECT * FROM repeatingorder_accountconfig WHERE content_status < 2";
        $o_query = $o_main->db->query($s_sql);
        $repeatingorder_accountconfig = ($o_query ? $o_query->row_array() : array());

        $completedProjects = array();

        $o_query = $o_main->db->query("SELECT offer.*, offer_pdf.*, CONCAT_WS(' ', customer.name, customer.middlename, customer.lastname) as customerName FROM offer
            JOIN offer_pdf ON offer_pdf.offer_id = offer.id
            LEFT OUTER JOIN customer ON customer.id = offer.customerId
            WHERE offer.content_status < 2
            ORDER BY offer_pdf.created DESC LIMIT 5");
        $recentReadOffers = $o_query ? $o_query->result_array() : array();
        foreach($recentReadOffers as $recentReadOffer) {

        ?>
        <tr>
            <td width="70px"><?php echo date("d.m.Y", strtotime($recentReadOffer['created']));?></td>
            <td width="45px"><?php echo date("H:i", strtotime($recentReadOffer['created']));?></td>
            <td width="110px"><?php echo $recentReadOffer['customerName'];?></td>
            <td><?php echo $recentReadOffer['offer_headline'];?></td>
        </tr>
    <?php } ?>
</table>
