<?php
if(!function_exists("include_local")) include(__DIR__."/../../../input/includes/fn_include_local.php");

include_once(__DIR__."/readOutputLanguage.php");

$s_sql = "SELECT * FROM collecting_cases WHERE (status = 0 OR status is null) AND (caselevel = 0 OR caselevel is null) ORDER BY created DESC";
$o_query = $o_main->db->query($s_sql);
$casesInReminderCount = $o_query ? $o_query->num_rows() : 0;
$s_sql = "SELECT * FROM collecting_cases WHERE (status = 0 OR status is null) AND (caselevel = 1) ORDER BY created DESC";
$o_query = $o_main->db->query($s_sql);
$casesInCollectingCount = $o_query ? $o_query->num_rows() : 0;

$s_sql = "SELECT * FROM collecting_cases WHERE (status = 1) AND (caselevel = 0 OR caselevel is null) ORDER BY created DESC";
$o_query = $o_main->db->query($s_sql);
$completedCasesInReminderCount = $o_query ? $o_query->num_rows() : 0;
$s_sql = "SELECT * FROM collecting_cases WHERE (status = 1) AND (caselevel = 1) ORDER BY created DESC";
$o_query = $o_main->db->query($s_sql);
$completedCasesInCollectingCount = $o_query ? $o_query->num_rows() : 0;

$s_sql = "SELECT * FROM collecting_cases WHERE YEAR(created) = '".date('Y')."'";
$o_query = $o_main->db->query($s_sql);
$newCasesCount = $o_query ? $o_query->num_rows() : 0;

?>
<table class="table">
    <tr>
        <td><?php echo $formText_CasesInReminderLevel_output?></td>
        <td><?php echo $casesInReminderCount;?></td>
    </tr>
    <tr>
        <td><?php echo $formText_CasesInCollectingLevel_output?></td>
        <td><?php echo $casesInCollectingCount;?></td>
    </tr>
    <tr>
        <td><?php echo $formText_NewCasesThisYear_output?></td>
        <td><?php echo $newCasesCount;?></td>
    </tr>
    <tr>
        <td><?php echo $formText_CompletedCasesInReminderLevel_output?></td>
        <td><?php echo $completedCasesInReminderCount;?></td>
    </tr>
    <tr>
        <td><?php echo $formText_CompletedCasesInCollectingLevel_output?></td>
        <td><?php echo $completedCasesInCollectingCount;?></td>
    </tr>
</table>
