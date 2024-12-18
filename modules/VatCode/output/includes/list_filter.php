<?php

$s_sql = "SELECT * FROM vatcode_set ORDER BY name ASC";
$o_query = $o_main->db->query($s_sql);
$vatcode_sets = $o_query ? $o_query->result_array() : array();

$s_sql = "SELECT * FROM ownercompany_accountconfig";
$o_query = $o_main->db->query($s_sql);
$ownercompany_accountconfig = $o_query ? $o_query->row_array() : array();

$s_sql = "SELECT * FROM company_product_set ORDER BY name ASC";
$o_query = $o_main->db->query($s_sql);
$company_product_sets = $o_query ? $o_query->result_array() : array();

$default_label = $formText_Default_output;
if($ownercompany_accountconfig['default_set_name'] != "") {
    $default_label = $ownercompany_accountconfig['default_set_name'];
}
?>
<?php
    if($ownercompany_accountconfig['activate_company_product_sets'] && count($company_product_sets) > 0){
?>
    <div class="article_set_wrapper">
        <label><?php echo $formText_CompanyProductSet_output;?>: </label>
        <select class="changeSet" autocomplete="off">
            <option value="-1"><?php echo $formText_ChooseSet_output;?></option>
            <option value="0" <?php if($company_product_set_id == 0) echo 'selected';?>><?php echo $default_label;?></option>
            <?php foreach($company_product_sets as $company_product_set) { ?>
                <option value="<?php echo $company_product_set['id'];?>" <?php if($company_product_set['id'] == $company_product_set_id) echo 'selected';?>><?php echo $company_product_set['name'];?></option>
            <?php } ?>
        </select>
    </div>
<?php } ?>
<script type="text/javascript">
    $(document).ready(function(){
        $(".changeSet").off("change").on("change", function(e){
            e.preventDefault();
            var data = {
                set_id: $(".changeSet").val()
            };
            loadView('list', data);
        })
    })
</script>
