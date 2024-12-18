<?php

$cid = $_POST['cid'] ? $o_main->db->escape_str($_POST['cid']) : 0;
$action = $_POST['action'] ? $_POST['action'] : '';

$s_sql = "SELECT * FROM customer WHERE id = ?";
$o_query = $o_main->db->query($s_sql, array($cid));
$customerToMerge = ($o_query ? $o_query->row_array() : array());

$externalSql = "SELECT cei.id id,
    cei.external_id external_id,
    cei.customer_id customer_id,
    oc.name name,
    oc.customerid_autoormanually customerid_autoormanually,
    oc.external_ownercompany_code external_ownercompany_code,
    oc.id ownercompany_id
    FROM customer_externalsystem_id cei
    LEFT JOIN ownercompany oc ON oc.id = cei.ownercompany_id
    WHERE cei.customer_id = ?";

$ownerCompanyIds = array();
$showAddButton = false;
$externalRowArray = array();

$o_query = $o_main->db->query($externalSql, array($customerToMerge['id']));
if($o_query && $o_query->num_rows()>0){
    $externalRowArray = $o_query->result_array();
}
if($externalRowArray) {
    echo $formText_CanNotMergeCustomerWithCustomerNumber_output;
    return;
}

$v_update = array(
    'orders' => "UPDATE orders SET customerID = ? WHERE customerID = ?",
    'subscriptionmulti' => "UPDATE subscriptionmulti SET customerId = ? WHERE customerId = ?",
    'contactperson' => "UPDATE contactperson SET customerId = ? WHERE customerId = ? AND (inactive IS NULL OR inactive = 0)",
    'customerindustriconnect' => "UPDATE customerindustriconnect SET customerId = ? WHERE customerId = ?",
    'customerareaconnect' => "UPDATE customerareaconnect SET customerId = ? WHERE customerId = ?",
    'customer_comments' => "UPDATE customer_comments SET customer_id = ? WHERE customer_id = ?",
    'customer_collectingorder' => "UPDATE customer_collectingorder SET customerId = ? WHERE customerId = ?",
    'sys_filearchive_folder' => "UPDATE sys_filearchive_folder SET connected_content_id = ? WHERE connected_content_id = ? AND connected_content_table = 'customer'",
    'project' => "UPDATE project SET customerId = ? WHERE customerId = ?",
    'invoice' => "UPDATE invoice SET customerId = ? WHERE customerId = ?",
    'prospect' => "UPDATE prospect SET customerId = ? WHERE customerId = ?",
    'project2' => "UPDATE project2 SET customerId = ? WHERE customerId = ?",
    'customer_selfdefined_values' => "UPDATE customer_selfdefined_values SET customer_id = ? WHERE customer_id = ?",
);
if($customerToMerge) {
    if(isset($_POST['output_form_submit']))
	{
		$newCustomerId = $_POST['customerId'];

		$s_sql = "SELECT * FROM customer WHERE id = ?";
		$o_query = $o_main->db->query($s_sql, array($newCustomerId));
		$customerMergeInto = ($o_query ? $o_query->row_array() : array());

		$b_do_merge = TRUE;
		$b_confirm_contactpersons = FALSE;
		$s_sql = "SELECT * FROM contactperson AS c1 JOIN contactperson AS c2 ON c2.email = c1.email WHERE c1.customerId = '".$o_main->db->escape_str($customerToMerge['id'])."' AND c2.customerId = '".$o_main->db->escape_str($customerMergeInto['id'])."' AND (c1.inactive IS NULL OR c1.inactive = 0) AND (c2.inactive IS NULL OR c2.inactive = 0) GROUP BY c1.id";
		$o_query = $o_main->db->query($s_sql);
		if($o_query && $o_query->num_rows()>0)
		{
			if(!isset($_POST['output_form_confirm']))
			{
				$b_do_merge = FALSE;
				$b_confirm_contactpersons = TRUE;
			}
		}
        if($b_do_merge)
		{
			if($customerMergeInto)
			{
				$mergeError = FALSE;

				$v_sql = array();
				foreach($v_update as $s_table => $s_sql)
				{
					$o_query = $o_main->db->query("SELECT * FROM ".$s_table." LIMIT 1");
					if($o_query && $o_query->num_rows()>0)
					{
						if('contactperson' == $s_table)
						{

							$o_find = $o_main->db->query("SELECT * FROM contactperson WHERE customerId = ? AND (inactive IS NULL OR inactive = 0)", array($customerToMerge['id']));
							if($o_find && $o_find->num_rows()>0)
							foreach($o_find->result_array() as $v_row)
							{
								$o_find2 = $o_main->db->query("SELECT * FROM contactperson WHERE customerId = ? AND email = ? AND (inactive IS NULL OR inactive = 0)", array($customerMergeInto['id'], $v_row['email']));
								if($o_find2 && $o_find2->num_rows()>0)
								{
									/*if(isset($_POST['confirm_cp_'.$v_row['id']]) && 1 == $_POST['confirm_cp_'.$v_row['id']])
									{
										$v_sql[] = "UPDATE contactperson SET customerId = ? WHERE customerId = ? AND id = '".$o_main->db->escape_str($v_row['id'])."'";
									}*/
								} else {
									$v_sql[] = "UPDATE contactperson SET customerId = ? WHERE customerId = ? AND id = '".$o_main->db->escape_str($v_row['id'])."'";
								}
							}

						} else if('customer_selfdefined_values' == $s_table) {
                            $o_find = $o_main->db->query("SELECT * FROM customer_selfdefined_values WHERE customer_id = ?", array($customerToMerge['id']));
                            if($o_find && $o_find->num_rows()>0)
							foreach($o_find->result_array() as $v_row)
							{
                                $o_find2 = $o_main->db->query("SELECT * FROM customer_selfdefined_values WHERE customer_id = ? AND selfdefined_fields_id = ?", array($customerMergeInto['id'], $v_row['selfdefined_fields_id']));
								if($o_find2 && $o_find2->num_rows()>0)
								{

                                } else {
                                    $v_sql[] = "UPDATE customer_selfdefined_values SET customer_id = ? WHERE customer_id = ? AND id = '".$o_main->db->escape_str($v_row['id'])."'";
                                }
                            }
                        } else {
							$v_sql[] = $s_sql;
						}
					}
				}

				$o_main->db->trans_begin();
				foreach($v_sql as $s_sql)
				{
					if($mergeError) continue;
					$o_query = $o_main->db->query($s_sql, array($customerMergeInto['id'], $customerToMerge['id']));
					if(!$o_query){
						$mergeError = true;
						echo $o_main->db->last_query()."<br>";
					}
				}
				if(!$mergeError)
				{
					$s_sql = "SELECT * FROM getynet_event_client";
					$o_query = $o_main->db->query($s_sql);
					$getynet_event_client = ($o_query ? $o_query->row_array():array());
					if($getynet_event_client['ge_account_url'] != "" && $getynet_event_client['ge_account_token'] != ""){
						$params = array(
							'api_url' => $getynet_event_client['ge_account_url'],
							'access_token'=> $getynet_event_client['ge_account_token'],
							'module' => 'GetynetEventProvider',
							'action' => 'customer_merge_participants',
							'params' => array(
								'customerMergeIntoId'=> $customerMergeInto['id'],
								'customerToMergeId'=> $customerToMerge['id'],
								'ge_provider_id' => $getynet_event_client['ge_provider_id']
							)
						);
						$response = fw_api_call($params, false);
						if(!$response['status']){
							$mergeError = true;
						}

					}
				}
				if(!$mergeError){
					$s_sql = "UPDATE customer SET content_status = 2 WHERE id = ?";
					$o_query = $o_main->db->query($s_sql, array($cid));
					$o_main->db->trans_commit();
					$fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$newCustomerId;
				} else {
					$o_main->db->trans_rollback();
					$fw_error_msg['error_'.count($fw_error_msg)] = $formText_ErrorOccuredDuringMergePleaseContactSystemDeveloper_output;
				}
			} else {
				$fw_error_msg['error_'.count($fw_error_msg)] = $formText_CustomerNotFound_output;
			}
		}

		if($b_confirm_contactpersons)
		{
			ob_start();
			?><div class="inner"><?php
			$s_sql = "SELECT c1.*, c2.name AS to_name, c2.middlename AS to_middlename, c2.lastname AS to_lastname, c2.title AS to_title, c2.email AS to_email, c2.mobile AS to_mobile FROM contactperson AS c1 JOIN contactperson AS c2 ON c2.email = c1.email WHERE c1.customerId = '".$o_main->db->escape_str($customerToMerge['id'])."' AND c2.customerId = '".$o_main->db->escape_str($customerMergeInto['id'])."' AND (c1.inactive IS NULL OR c1.inactive = 0) AND (c2.inactive IS NULL OR c2.inactive = 0) GROUP BY c1.id";
			$o_query = $o_main->db->query($s_sql);
			if($o_query && $o_query->num_rows()>0)
			foreach($o_query->result_array() as $v_row)
			{
				?>
				<div class="panel panel-default">
					<div class="panel-heading"><strong><?php echo $formText_ThisPersonExistsInTargetCustomer_Output;?></strong>: <?php echo preg_replace('/\s+/', ' ', $v_row['to_name'].' '.$v_row['to_middlename'].' '.$v_row['to_lastname']).', '.$v_row['to_title'].', '.$v_row['to_email'].', '.$v_row['to_mobile'];?></div>
					<div class="panel-body">
						<div class="row">
							<div class="col-xs-12">
								<strong><?php echo $formText_FollowingPersonWillNotBeTransfered_Output;?></strong>: <?php echo preg_replace('/\s+/', ' ', $v_row['name'].' '.$v_row['middlename'].' '.$v_row['lastname']).', '.$v_row['title'].', '.$v_row['email'].', '.$v_row['mobile'];?>
							</div>
							<?php /*?><div class="col-xs-2">
								<label class="radio-inline">
									<input type="radio" name="confirm_cp_<?php echo $v_row['id'];?>" value="1"> <?php echo $formText_Yes_Output;?>
								</label>
								<label class="radio-inline">
									<input type="radio" name="confirm_cp_<?php echo $v_row['id'];?>" value="0" checked> <?php echo $formText_No_Output;?>
								</label>
							</div><?php */?>
						</div>
					</div>
				</div>
				<?php
			}
			?></div><?php
			$fw_return_data['confirmation'] = 1;
			$fw_return_data['html'] = ob_get_clean();
		}

		return;

    } else {


		$b_show_merge_button = FALSE;
		$s_found_customer = '';
		$s_sql = "SELECT GROUP_CONCAT(id) ids, CONCAT(COALESCE(TRIM(name),''), '', COALESCE(TRIM(middlename), ''), '', COALESCE(TRIM(lastname), '')) mergedName, COUNT(*) c
		FROM customer WHERE content_status < 2 AND (selfregistered IS NULL OR selfregistered = 0 OR selfregistered = 2)
		GROUP BY CONCAT(COALESCE(TRIM(name),''), '', COALESCE(TRIM(middlename), ''), '', COALESCE(TRIM(lastname), '')) HAVING c >= 1 AND mergedName = ?";
		$o_query = $o_main->db->query($s_sql, array(trim($customerToMerge['name']).trim($customerToMerge['middlename']).trim($customerToMerge['lastname'])));
		$duplicatesByName = $o_query ? $o_query->result_array() : array();
		$duplicatesByRegisterId = array();
		if($customerToMerge['publicRegisterId'] != "" && $customerToMerge['publicRegisterId'] != 0)
		{
			$s_sql = "SELECT GROUP_CONCAT(id) ids, publicRegisterId, COUNT(*) c
			FROM customer WHERE content_status < 2  AND (selfregistered IS NULL OR selfregistered = 0 OR selfregistered = 2)
			GROUP BY TRIM(publicRegisterId) HAVING c >= 1 AND publicRegisterId = ?";
			$o_query = $o_main->db->query($s_sql, array($customerToMerge['publicRegisterId']));
			$duplicatesByRegisterId = $o_query ? $o_query->result_array() : array();
		}
		$v_found_match = array();
		if((count($duplicatesByName)+count($duplicatesByRegisterId)) > 0)
		{
			 foreach($duplicatesByName as $duplicates)
			 {
				 $customerIds = explode(",",$duplicates['ids']);
				 foreach($customerIds as $customerId)
				 {
					 if($customerId != $customerToMerge['id'])
					 {
						 $o_query = $o_main->db->query("SELECT c.*, cexternl.external_id as customerExternalNr FROM customer c
							 LEFT OUTER JOIN customer_externalsystem_id cexternl ON cexternl.customer_id = c.id
							 WHERE c.id = ?", array($customerId));
						 $customer = $o_query ? $o_query->row_array() : array();
						 $external_string = "";
						 if($customer['customerExternalNr'] != "")
						 {
							$external_string =' ('.$formText_CustomerNr_output.": ".$customer['customerExternalNr'].')';
						 }
						 $v_found_match[] = array(
						 	'id' => $customer['id'],
							'created' => $customer['created'],
							'name' => $customer['name']." ".$customer['middlename']." ".$customer['lastname'] .' '.$external_string,
						);
					 }
				 }
			 }
			 foreach($duplicatesByRegisterId as $duplicates){
				 $customerIds = explode(",",$duplicates['ids']);
				 foreach($customerIds as $customerId){
					 if($customerId != $customerToMerge['id']){
						 $o_query = $o_main->db->query("SELECT c.*, cexternl.external_id as customerExternalNr FROM customer c
							 LEFT OUTER JOIN customer_externalsystem_id cexternl ON cexternl.customer_id = c.id
							 WHERE c.id = ?", array($customerId));
						 $customer = $o_query ? $o_query->row_array() : array();
						 $external_string = "";
						 if($customer['customerExternalNr'] != ""){
							$external_string =' ('.$formText_CustomerNr_output.": ".$customer['customerExternalNr'].')';
						 }
						 $v_found_match[] = array(
						 	'id' => $customer['id'],
							'created' => $customer['created'],
							'name' => $customer['name']." ".$customer['middlename']." ".$customer['lastname'] .' '.$external_string,
						);
					 }
				 }
			 }
		}
	}
    ?>
    <div class="popupform">
        <div id="popup-validate-message" style="display:none;"></div>
        <form class="output-form main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=merge_customer";?>" method="post">
            <input type="hidden" name="fwajax" value="1">
            <input type="hidden" name="fw_nocss" value="1">
            <input type="hidden" name="output_form_submit" value="1">
            <input type="hidden" name="cid" value="<?php echo $cid;?>">
            <input type="hidden" name="redirect_url" value="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$cid; ?>">
            <div class="defaultForm">
                <div class="inner">
                    <div class="popupformTitle"><?php echo $formText_MergeIntoOtherCustomer_output;?></div>
                    <div class="line">
                        <?php
                        if($variables->developeraccess > 5){
                            echo $formText_TablesToBeMerged_output.":</br>";
                            foreach($v_update as $key=>$value){
                                echo $key."<br/>";
                            }
                        }
                        ?>
                    </div>
                    <div class="line">
                        <div class="lineTitle"><?php echo $formText_Customer_Output; ?></div>
                        <div class="lineInput">
                            <?php
                            $customer = array('id'=>'');
							if(1 < count($v_found_match))
							{
								?><div class="output-merge-customer-match">
								<h5><?php echo $formText_ChooseFoundDuplicate_Output;?></h5><?php
								foreach($v_found_match as $v_item)
								{
									?><div><a href="#" class="script" data-customer-id="<?php echo $v_item['id'];?>"><?php echo $v_item['name'];?></a></div><?php
								}
								?></div><?php
							} else {
								if(1 == count($v_found_match))
								{
									$customer = $v_found_match[0];
									?><a href="#" class="selectCustomer"><?php echo $customer['name'];?></a><?php
								} else {
									?><a href="#" class="selectCustomer"><?php echo $formText_SelectCustomer_Output;?></a><?php
								}

							}
							?>
							<input type="hidden" name="customerId" id="customerId" value="<?php print $customer['id'];?>" required>
                        </div>
                        <div class="clear"></div>
                    </div>
                </div>
            </div>
			<div class="confirmationForm"> </div>

            <div class="popupformbtn">
                <button type="button" class="output-btn b-large b-close"><?php echo $formText_Cancel_Output;?></button>
                <input type="submit" name="sbmbtn" value="<?php echo $formText_Save_Output; ?>">
            </div>
        </form>
    </div>
    <style>
    .modal {
        z-index: 10000;
    }
    </style>
    <script type="text/javascript" src="../modules/<?php echo $module;?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
    <script type="text/javascript">
    $("form.output-form").validate({
        ignore: [],
        submitHandler: function(form) {
            var customerName = $(".selectCustomer").html();
            bootbox.confirm('<?php echo "<b>".$formText_TheMergeIsUnreversable_output."</b><br/>".$formText_YouWillBeMerging_output." \'".$customerToMerge['name']." ".$customerToMerge['middlename']." ".$customerToMerge['lastname']."\' ".$formText_into_output." \'"; ?>'+customerName+"'", function(result) {
                if (result) {
                    fw_loading_start();
                    $(".errorText").hide().html("");
                    $.ajax({
                        url: $(form).attr("action"),
                        cache: false,
                        type: "POST",
                        dataType: "json",
                        data: $(form).serialize(),
                        success: function (json) {
                            fw_loading_end();
                            if(json.data !== undefined && json.data.confirmation) {
                                $(".popupform .output-form").append("<input type='hidden' name='output_form_confirm' value='1'/>");
                                //$(".popupform .defaultForm").hide();
                                $(".popupform .confirmationForm").html(json.data.html).show();
                            } else {
                                if(json.error !== undefined)
                                {
                                    var _msg = '';
									$.each(json.error, function(index, value){
                                        var _type = Array("error");
                                        if(index.length > 0 && index.indexOf("_") > 0) _type = index.split("_");
										_msg = _msg + '<div class="msg-' + _type[0] + '">' + value + '</div>';
                                    });
                                    $('#popup-validate-message').html(_msg, true);
									$("#popup-validate-message").show();
                                    fw_loading_end();
                                    fw_click_instance = fw_changes_made = false;
                                } else {
                                    if(json.redirect_url !== undefined)
                                    {
                                        out_popup.addClass("close-reload").data("redirect", json.redirect_url);
                                        out_popup.close();
                                    }
                                }
                            }
                        }
                    }).fail(function() {
                        $("#popup-validate-message").html("<?php echo $formText_ErrorOccuredSavingContent_Output;?>", true);
                        $("#popup-validate-message").show();
                        $('#popupeditbox').css('height', $('#popupeditboxcontent').height());
                        fw_loading_end();
                    });
                }
            })

        },
        invalidHandler: function(event, validator) {
            var errors = validator.numberOfInvalids();
            if (errors) {
                var message = errors == 1
                ? '<?php echo $formText_YouMissed_validate; ?> 1 <?php echo $formText_field_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>'
                : '<?php echo $formText_YouMissed_validate; ?> ' + errors + ' <?php echo $formText_fields_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>';

                $("#popup-validate-message").html(message);
                $("#popup-validate-message").show();
                $('#popupeditbox').css('height', $('#popupeditboxcontent').height());
            } else {
                $("#popup-validate-message").hide();
            }
            setTimeout(function(){ $('#popupeditbox').height(''); }, 200);
        },
        errorPlacement: function(error, element) {
            if(element.attr("name") == "customerId") {
                error.insertAfter(".selectCustomer");
            }
            if(element.attr("name") == "projectLeader") {
                error.insertAfter(".popupform .selectEmployee");
            }
        },
        messages: {
            customerId: "<?php echo $formText_SelectTheCustomer_output;?>",
            projectLeader: "<?php echo $formText_SelectProjectLeader_output;?>",
        }
    });
	output_bind_elements();
    function output_bind_elements()
	{
		$(".selectCustomer").unbind("click").bind("click", function(e){
			e.preventDefault();
			fw_loading_start();
			var _data = { fwajax: 1, fw_nocss: 1, mergeFromCustomerId: "<?php echo $cid;?>"};
			$.ajax({
				cache: false,
				type: 'POST',
				dataType: 'json',
				url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=get_customers";?>',
				data: _data,
				success: function(obj){
					$('#popupeditboxcontent2').html('');
					$('#popupeditboxcontent2').html(obj.html);
					out_popup2 = $('#popupeditbox2').bPopup(out_popup_options);
					$("#popupeditbox2:not(.opened)").remove();
					fw_loading_end();
				}
			});
		})
	}
	$('.output-merge-customer-match a').off('click').on('click', function(e){
        e.preventDefault();
        $('#customerId').val($(this).data('customer-id')).before('<a href="#" class="selectCustomer">' + $(this).text() + '</a>');
		$('.output-merge-customer-match').remove();
		output_bind_elements();
    })
    </script>
<?php } else {
    echo $formText_NoCustomer_output;
}
