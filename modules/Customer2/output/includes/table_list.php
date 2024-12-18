<?php
$o_query = $o_main->db->get('ownercompany_accountconfig');
$ownercompany_accountconfig = $o_query ? $o_query->row_array() : array();
?>
<div class="p_headerLine"><?php
if($moduleAccesslevel > 10)
{	?>
	<div class="backToCustomer btnStyle">
		<div class="plusTextBox active">
			<div class="text"><?php echo $formText_Back_Output; ?></div>
			<div class="clear"></div>
		</div>
	</div>
	<div class="clear"></div>
	<?php
}
?>
</div>
<script type="text/javascript">
    $(".backToCustomer").on('click', function(e){
        e.preventDefault();
        fw_load_ajax('<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=list"; ?>', false, true);
    });
</script>

<?php
$search_filter = isset($_GET['search_filter']) ? $_GET['search_filter'] : "";

if($search_filter != ""){
	$s_sql = "SELECT * FROM customer WHERE content_status < 2 AND customer.name LIKE '%".$search_filter."%' ORDER BY name";
	$o_query = $o_main->db->query($s_sql, array($search_filter));
	$customers = $o_query ? $o_query->result_array() : array();
} else {
}


$editableFields = array(
    "publicRegisterId"=> array("label"=>$formText_PublicRegisterNumer_output, "width"=>"200"),
    "phone"=>array("label"=>$formText_Phone_output, "width"=>"200"),
    "email"=>array("label"=>$formText_Email_output, "width"=>"200"),
    "credittimeDays"=>array("label"=>$formText_CreditTimeDays_output, "width"=>"200"),
    "invoiceEmail"=>array("label"=>$formText_InvoiceEmail_output, "width"=>"200"),
    "homepage"=>array("label"=>$formText_Homepage_output, "width"=>"200"),
    "paStreet"=>array("label"=>$formText_Street_output, "width"=>"200"),
    "paStreet2"=>array("label"=>$formText_Street2_output, "width"=>"200"),
    "paPostalNumber"=>array("label"=>$formText_PostalNumber_output, "width"=>"200"),
    "paCity"=>array("label"=>$formText_City_output, "width"=>"200"),
    "paCountry"=>array("label"=>$formText_Country_output, "width"=>"200"),
    "vaStreet"=>array("label"=>$formText_VisitingStreet_output, "width"=>"200"),
    "vaStreet2"=>array("label"=>$formText_VisitingStreet2_output, "width"=>"200"),
    "vaPostalNumber"=>array("label"=>$formText_VisitingPostalNumber_output, "width"=>"200"),
    "vaCity"=>array("label"=>$formText_VisitingCity_output, "width"=>"200"),
    "vaCountry"=>array("label"=>$formText_VisitingCountry_output, "width"=>"200")
);
?>
<div class="p_tableFilter">

    <div class="p_tableFilter_left">
		<form class="searchFilterForm" id="searchFilterForm">
			<input type="text" class="searchFilter" value="<?php echo $search_filter;?>" autocomplete="off">
			<button id="p_tableFilterSearchBtn"><?php echo $formText_Search_output; ?></button>
		</form>
	</div>
	<div class="p_tableFilter_right">
	</div>
	<div class="clear"></div>
	<div class="filter_message">
		<?php
		if($search_filter != ""){
			echo count($customers). " ".$formText_CustomersInSelection_output;
		} else {
			echo $formText_FilterDownBySearch_output;
		}
		?>
	</div>
</div>
<div class="table_wrapper">
    <table class="table table_scrollable_head">
        <tr>
            <th width="200px" class="firstRow"><?php echo $formText_CustomerName_output;?></th>
            <?php
            foreach($editableFields as $index => $editableFieldArray){
                ?>
                <th width="<?php echo $editableFieldArray['width']?>px"><?php echo $editableFieldArray['label'];?></th>
                <?php
            }
            ?>
        </tr>
    </table>
    <table class="table table_scrollable">
        <?php

        foreach($customers as $customer) {
            ?>
            <tr>
                <td width="200px" class="firstRow"><?php echo $customer['name']." ".$customer['middle_name']." ".$customer['last_name'];?></td>
                <?php
                foreach($editableFields as $index => $editableFieldArray){
                    ?>
                    <td width="<?php echo $editableFieldArray['width']?>px">
                        <div class="field_view">
                            <?php echo $customer[$index];?>
                        </div>
                        <div class="field_edit">
                            <input type="hidden" class="field_view_value" value="<?php echo $customer[$index];?>"/>
                            <input type="text" class="field_item" data-customer-id="<?php echo $customer['id']?>" data-field="<?php echo $index;?>" value="<?php echo $customer[$index];?>"/>
                            <div class="cancel_field"><?php echo $formText_Cancel_output;?></div>
                            <div class="save_field"><?php echo $formText_Save_output;?></div>
                        </div>
                    </td>
                    <?php
                }
                ?>
            </tr>
            <?php
        }
        ?>
    </table>
</div>
<script type="text/javascript">
$(document).ready(function() {
	// $(".searchFilter").on("keyup", function(e){
	// 	e.preventDefault();
    //     var data = {
    //         search_filter: $('.searchFilter').val()
    //     };
	// 	loadView("table_list", data);
	// })

	// Filter by customer name
    $('.searchFilterForm').on('submit', function(e) {
        e.preventDefault();
        var data = {
            search_filter: $('.searchFilter').val()
        };
		loadView("table_list", data);
    });

    $('.table_wrapper').scroll(function(e) {
        var scroll = $(this).scrollTop();
        var scrollLeft = $(this).scrollLeft();
        $(".table_scrollable_head").width($(".table_scrollable tbody").width()).css({"top": scroll+"px"});
        $(".firstRow").css({"left": scrollLeft+"px"});
    });
    $(".table_scrollable td").dblclick(function(e){
        e.preventDefault();
        if($(this).find(".field_edit").length > 0) {
            $(this).find(".field_view").hide();
            $(this).find(".field_item").val($(this).find(".field_view_value").val());
            $(this).find(".field_edit").show();
            $(this).find(".field_item").focus();
        }
    })
    $(".cancel_field").on("click", function(){
        var parent = $(this).parents("td");
        parent.find(".field_edit").hide();
        parent.find(".field_view").show();
    })
    $(".save_field").on("click", function(){
        var parent = $(this).parents("td");
        var item = parent.find(".field_item");
        var data = {
            customer_id: item.data("customer-id"),
            field: item.data("field"),
            value: item.val(),
        }
        ajaxCall("save_field", data, function(json){
            if(json.data != undefined){
                parent.find(".field_view").html(json.data);
                parent.find(".field_view_value").val(json.data);
                parent.find(".field_edit").hide();
                parent.find(".field_view").show();
            } else {
                if(json.error){
                    alert(json.error);
                }
            }
        })
    })
});
</script>
<style>
    .field_edit {
        display: none;
    }
    .table_scrollable_head {
        table-layout: fixed;
        background: #fff;
        position: relative;
        top: 0;
        margin-bottom: 0;
        z-index: 10;
    }
    .table_wrapper {
        max-height: 500px;
        overflow: auto;
        position: relative;
    }
	.filter_message {
		padding: 5px 15px;
	}
    .table_scrollable {
        table-layout: fixed;
    }
    .firstRow {
        position: relative;
        left: 0px;
        background: #fff;
    }
    .cancel_field {
        display: inline-block;
        padding: 2px 5px;
        border: 1px solid #194273;
        background: #fff;
        color: #194273;
        border-radius:5px;
        margin-top: 3px;
        cursor: pointer;
    }
    .save_field {
        display: inline-block;
        padding: 2px 5px;
        border: 1px solid #194273;
        background: #194273;
        color: #fff;
        border-radius:5px;
        margin-top: 3px;
        cursor: pointer;
    }

    #fw_getynet {
        display: none;
    }
    #fw_account.alternative {
        max-width: 100% !important;
        min-height: auto !important;
        margin-top: 0 !important;
    }
    body.desktop #fw_account.alternative .fw_col.col0 {
        display: none !important;
    }
    #fw_account.alternative .fw_module_head_wrapper {
        display: none !important;
    }
    .p_headerLine {
        /* display: none; */
    }
    .p_container {
        max-width: 100%;
    }
    body.desktop #fw_account.alternative .fw_col.col1 {
        width: 96% !important;
        margin: 0px 2% !important;
        left: 0 !important;
    }
    .p_container .p_containerInner {
        margin-top: 0px !important;
    }
</style>
