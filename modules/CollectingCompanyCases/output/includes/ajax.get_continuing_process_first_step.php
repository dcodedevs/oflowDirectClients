<?php
$s_sql = "SELECT * FROM collecting_company_cases_continuing_process_steps WHERE collecting_company_cases_continuing_process_id = '".$o_main->db->escape_str($_POST['continuing_process_id'])."'  ORDER BY sortnr ASC";
$o_query = $o_main->db->query($s_sql);
$first_step = $o_query ? $o_query->row_array() : array();

if($first_step){
    ?>
    <div class="">
        <?php echo $first_step['name'];?></br>
        <?php echo $formText_CreateLetter_output.": "; if($first_step['create_letter']) { echo $formText_Yes_output; } else { echo $formText_No_output; }?></br>
        <?php
            if($first_step['create_letter']){
                $s_sql = "SELECT * FROM collecting_cases_pdftext WHERE id = ? AND content_status < 2 ORDER BY sortnr ASC";
                $o_query = $o_main->db->query($s_sql, array(intval($first_step['collecting_cases_pdftext_id'])));
                $letter = $o_query ? $o_query->row_array() : array();
                echo $letter['name'];?></br>
            <?php
            } else {
                ?>
                <?php echo $formText_AppearInLegalStepHandling_output.": "; if($first_step['appear_in_legal_step_handling']) { echo $formText_Yes_output; } else { echo $formText_No_output; }?></br>  
                <?php echo $formText_AppearInCallDebitorStepHandling_output.": "; if($first_step['appear_in_call_debitor_step_handling']) { echo $formText_Yes_output; } else { echo $formText_No_output; }?></br>
                <?php
            }
        ?>
    </div>
<?php } ?>
