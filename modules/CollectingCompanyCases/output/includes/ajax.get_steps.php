<?php
$s_sql = "SELECT * FROM collecting_cases_collecting_process_steps WHERE collecting_cases_collecting_process_id = '".$o_main->db->escape_str($_POST['collecting_process'])."'  ORDER BY sortnr ASC";
$o_query = $o_main->db->query($s_sql);
$all_steps = $o_query ? $o_query->result_array() : array();

?>
<select name="collecting_cases_process_step_id" autocomplete="off" >
    <option value=""><?php echo $formText_Select_output;?></option>
   <?php
   foreach($all_steps as $step){
       ?>
       <option value="<?php echo $step['id'];?>" <?php if($step['id'] == $_POST['current_step']) echo 'selected';?>><?php echo $step['name'];?></option>
       <?php
   }
   ?>
</select>
