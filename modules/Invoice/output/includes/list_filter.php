<div class="p_tableFilter">
    <div class="p_tableFilter_left">
        <?php echo $formText_Company_output;
            $sql = "SELECT * FROM ownercompany";
            $result2 = mysql_query($sql);
            $result = $o_main->db->query($sql);
            if($result && $result->num_rows()>0)
            if($result->num_rows() > 1){
            ?>
            <span class="selectDiv selected">
                <span class="selectDivWrapper">
                    <select name="defaultSelect" class="companyFilter">
                        <option value="0"><?php echo $formText_SelectCompany_output; ?></option>
                        <?php

                        foreach($result->result() AS $row){ ?>
                            <option value="<?php echo $row->id; ?>" <?php echo $row->id == $company_filter ? 'selected="selected"' : ''; ?>><?php echo $row->name; ?></option>
                        <?php } ?>
                    </select>
                </span>
                <span class="arrowDown"></span>
            </span>
        <?php } else {
            $ownerCompany = $result->result();
            $company_filter = $ownerCompany[0]->id;
            ?>
            <span class="selectDiv selected">
                <span class="selectDivWrapper">
                    <select name="defaultSelect" class="companyFilter">
                        <option value="<?php echo $ownerCompany[0]->id; ?>"><?php echo $ownerCompany[0]->name; ?></option>
                    </select>
                </span>
                <span class="arrowDown"></span>
            </span>
        <?php } ?>

        <?php
        if ($company_filter && !$invoice_accountconfig['activate_global_export']) {
            $ownerCompany_sql = $o_main->db->query("SELECT * FROM ownercompany WHERE id = ?", array($company_filter));
            if($ownerCompany_sql && $ownerCompany_sql->num_rows()>0)
            $ownerCompany = $ownerCompany_sql->result();
            include(__DIR__.'/../../../OwnerCompany/output/includes/readOutputLanguage.php');
            if($ownerCompany[0]->exportScriptFolder == "" || $ownerCompany[0]->exportScriptFolder == null){
                include(__DIR__.'/../../../OwnerCompany/output/includes/exportScripts/CordelExport/exportBtn.php');
            } else {
                include($ownerCompany[0]->exportScriptFolder.'/exportBtn.php');
            }
        }
        /*
        <?php echo $formText_Category_output; ?>
        <span class="selectDiv selected">
            <span class="selectDivWrapper">
                <select name="defaultSelect" class="categoryFilter">
                    <option value="0"><?php echo $formText_SelectCategory_output; ?></option>
                    <?php
                    $sql = "SELECT * FROM officespace_category";
                    $result = mysql_query($sql);
                    while ($row = mysql_fetch_assoc($result)): ?>
                        <option value="<?php echo $row['id']; ?>" <?php echo $row['id'] == $category_filter ? 'selected="selected"' : ''; ?>><?php echo $row['name']; ?></option>
                    <?php endwhile; ?>
                </select>
            </span>
            <span class="arrowDown"></span>
        </span>
        */ ?>
    </div>
    <div class="p_tableFilter_right">
        <form class="searchFilterForm" id="searchFilterForm">
            <input type="text" class="searchFilter" value="<?php echo $search_filter; ?>">
            <button id="p_tableFilterSearchBtn"><?php echo $formText_Search_output; ?></button>
        </form>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function() {

    // Filter by building
    $('.companyFilter').on('change', function(e) {
        var data = {
            company_filter: $(this).val(),
            list_filter: '<?php echo $list_filter; ?>',
            search_filter: '',
        };
        loadView('list', data);
    });

    // Filter by customer name
    $('.searchFilterForm').on('submit', function(e) {
        e.preventDefault();
        var data = {
            company_filter: $('.companyFilter').val(),
            list_filter: '<?php echo $list_filter; ?>',
            search_filter: $('.searchFilter').val()
        };
        loadView('list', data);
    });
});


</script>
