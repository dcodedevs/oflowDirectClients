<?php
$s_sql = "select * from customer_accountconfig ORDER BY id DESC";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0) {
    $v_customer_accountconfig = $o_query->row_array();
}
$b_enable_search = ($v_customer_accountconfig['getynet_customer_search'] == 1 ? true : false);
$b_enable_all_accounts = ($v_customer_accountconfig['getynet_show_all_accounts'] == 1 ? true : false);
$b_enable_grant_all = ($v_customer_accountconfig['getynet_grant_access_for_multi_partner_company'] == 1 ? true : false);
$b_enable_grant_admin = ($v_customer_accountconfig['getynet_grant_admin_access'] == 1 ? true : false);
$b_enable_grant_system_admin = ($v_customer_accountconfig['getynet_grant_system_admin_access'] == 1 ? true : false);
$b_enable_grant_designer = ($v_customer_accountconfig['getynet_grant_designer_access'] == 1 ? true : false);
$b_enable_grant_developer = ($v_customer_accountconfig['getynet_grant_developer_access'] == 1 ? true : false);

if($moduleAccesslevel > 10)
{
	if(isset($_POST['output_form_submit']))
	{
		if(!function_exists("APIconnectAccount")) include(__DIR__."/../../input/includes/APIconnect.php");
		$s_sql = "select * from accountinfo";
		$o_query = $o_main->db->query($s_sql);
		if($o_query && $o_query->num_rows()>0) {
		    $v_accountinfo = $o_query->row_array();
		}
		if(isset($_POST['search_name']))
		{
			$s_search = trim($_POST['search_name']);
			if(strlen($s_search) >= 3)
			{
				$v_data = array
				(
					"PARTNER_ID"=>$v_customer_accountconfig['getynet_partner_id'],
					"PARTNER_PWD"=>$v_customer_accountconfig['getynet_partner_pw'],
					"SEARCH"=>$s_search
				);
				$v_response = json_decode(APIconnectAccount("companysearchlist", $v_accountinfo['accountname'], $v_accountinfo['password'], $v_data), true);
				if(array_key_exists('data',$v_response))
				{
					ob_start();
					?>
					<table class="table table-bordered table-striped table-condensed">
						<tr>
							<th class="smallColumn">#</th>
							<th><?php echo $formText_CompanyName_output; ?></th>
							<th><?php echo $formText_City_output; ?></th>
							<th><?php echo $formText_Country_output; ?></th>
							<th class="smallColumn actionWidth">&nbsp;</th>
						</tr>
						<?php
						$l_per_page = 15;
						$l_start_page = intval($_POST['page']);
						$l_show_from = ($l_start_page*$l_per_page)+1;
						$l_show_to = $l_show_from+$l_per_page;
						$l_total_amount = sizeof($v_response['data']);
						$l_total_pages = ceil($l_total_amount/$l_per_page);
						if($l_total_amount==0)
						{
							?><tr><td colspan="5"><center><?php echo $formText_NothingFound_output;?></center></td></tr><?php
						} else {
							$l_x = 1;
							foreach($v_response['data'] as $v_item)
							{
								if($l_x >= $l_show_from && $l_show_to > $l_x)
								{
									?><tr>
										<td><?php echo $l_x;?></td>
										<td><?php echo $v_item['name'];?></td>
										<td><?php echo $v_item['paCity'];?></td>
										<td><?php echo $v_item['paCountry'];?></td>
										<td><a class="script" href="#" onClick="output_getynet_customer_pick(this);" data-customer-id="<?php echo $v_item['id'];?>"><?php echo $formText_Choose_output;?></a></td>
									</tr><?php
								}
								$l_x++;
							}
						}
						?>
					</table>
					<?php
					if($l_total_pages>1)
					{
						$l_boundary_interval = 7;
						$l_boundary_left = ($l_start_page - $l_boundary_interval);
						$l_boundary_right = ($l_start_page + $l_boundary_interval);
						if($l_boundary_left < 0) $l_boundary_right += -$l_boundary_left+2;
						if($l_boundary_right > $l_total_pages) $l_boundary_left -= $l_boundary_right - $l_total_pages+2;
						?><nav>
							<ul class="pagination pagination-sm">
								<?php
								if($l_start_page==0)
								{
									?><li class="disabled"><span><span aria-hidden="true">&laquo;</span></span></li><?php
								} else {
									?><li><a class="script" href="#" onClick="output_getynet_customer_search(<?php echo ($l_start_page-1);?>);" aria-label="<?=$formText_Previous_fieldtype;?>"><span aria-hidden="true">&laquo;</span></a></li><?php
								}
								for($l_x=0; $l_x < $l_total_pages; $l_x++)
								{
									if($l_x < 1 || ($l_x > $l_boundary_left && $l_x < $l_boundary_right) || $l_x >= ($l_total_pages - 1))
									{
										$b_print_space = true;
										if($l_x == $l_start_page)
										{
											?><li class="active"><span><?=($l_x+1);?></span></li><?php
										} else {
											?><li><a class="script" href="#" onClick="output_getynet_customer_search(<?php echo $l_x;?>);"><?=($l_x+1);?></a></li><?php
										}
									} else if($b_print_space) {
										$b_print_space = false;
										?><li><a class="script" onClick="javascript:return false;">...</a></li><?php
									}
								}
								if($l_start_page==($l_total_pages-1))
								{
									?><li class="disabled"><span><span aria-hidden="true">&raquo;</span></span></li><?php
								} else {
									?><li><a class="script" href="#" onClick="output_getynet_customer_search(<?php echo ($l_start_page+1);?>);" aria-label="<?=$formText_Next_fieldtype;?>"><span aria-hidden="true">&raquo;</span></a></li><?php
								}
								?>
							</ul>
						</nav><?php
					}
					$s_buffer = ob_get_clean();
					echo $s_buffer;
				} else {
					$fw_error_msg = $formText_ErrorOccuredWhileSearchingData_output;
				}
			} else {
				$fw_error_msg = $formText_SearchCriteriaShouldBeAtLeast3CharactersLong_output;
			}
		} else if(isset($_POST['getynet_customer_id'])) {
			$l_customer_id = intval($_POST['customer_id']);
			$l_getynet_customer_id = intval($_POST['getynet_customer_id']);
			if($l_customer_id > 0 && $l_getynet_customer_id > 0)
			{
				$s_sql = "SELECT * FROM customer WHERE id = ?";
				$o_query = $o_main->db->query($s_sql, array($l_customer_id));
				if($o_query && $o_query->num_rows()>0) {
					$o_main->db->query("UPDATE customer SET getynet_customer_id = ? WHERE id = ?", array($l_getynet_customer_id, $l_customer_id));
				} else {
					$fw_error_msg = $formText_CustomerNotFound_output;
				}

			} else {
				$fw_error_msg = $formText_CustomerNotFound_output;
			}

			$fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$_POST['customer_id'];
		} else {
			$l_customer_id = intval($_POST['customer_id']);
			if($l_customer_id > 0)
			{
				$s_sql = "SELECT * FROM customer WHERE id = ?";
				$o_query = $o_main->db->query($s_sql, array($l_customer_id));
				if($o_query && $o_query->num_rows()>0) {
					$v_customer = $o_query->row_array();
					$v_data = array
					(
						"PARTNER_ID"=>$v_customer_accountconfig['getynet_partner_id'],
						"PARTNER_PWD"=>$v_customer_accountconfig['getynet_partner_pw'],
						"COMPANYNR"=>$v_customer['publicRegisterId'],
						"COMPANYNAME"=>$v_customer['name'],
						"LANGUAGEID"=>"no",
						"ADRESSLINE1"=>$v_customer['paStreet'],
						"ADRESSLINE2"=>"",
						"POSTALCODE"=>$v_customer['paPostalNumber'],
						"CITY"=>$v_customer['paCity'],
						"COUNTRY"=>$v_customer['paCountry'],
						"PHONE"=>$v_customer['phone'],
						"FAX"=>$v_customer['fax'],
						"EMAIL"=>$v_customer['email'],
						"FORCE"=>1 // Force duplicate
					);

					$v_response = json_decode(APIconnectAccount("companycreatenew", $v_accountinfo['accountname'], $v_accountinfo['password'], $v_data), true);

					if(array_key_exists('data',$v_response))
					{
						if(isset($v_response['data']['companyID']) && $v_response['data']['companyID'] > 0)
						{
							$o_main->db->query("UPDATE customer SET getynet_customer_id = ? WHERE id = ?", array($v_response['data']['companyID'], $l_customer_id));
						} else {
							$fw_error_msg = $formText_ErrorOccuredConnectingGetynetCustomer_output;
						}
					} else {
						$fw_error_msg = $formText_ErrorOccuredConnectingGetynetCustomer_output;
					}
				} else {
					$fw_error_msg = $formText_CustomerNotFound_output;
				}
			} else {
				$fw_error_msg = $formText_CustomerNotFound_output;
			}

			$fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$_POST['customer_id'];
		}
		return;
	}
}

unset($v_data);
if(isset($_POST['customer_id']) && $_POST['customer_id'] > 0)
{
	$s_sql = "SELECT * FROM customer WHERE id = ?";
    $o_query = $o_main->db->query($s_sql, array($_POST['customer_id']));
    if($o_query && $o_query->num_rows()>0) {
        $v_data = $o_query->row_array();
    }
}
if($v_data['id'] == 0) return;
?>
<div class="popupform">
<form class="output-form" action="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=".$s_inc_act ;?>" method="post">
	<input type="hidden" name="fwajax" value="1">
	<input type="hidden" name="fw_nocss" value="1">
	<input type="hidden" name="output_form_submit" value="1">
	<input type="hidden" name="customer_id" value="<?php print $_POST['customer_id'];?>">
	<div id="popup-validate-message"></div>
	<div class="inner">
		<?php if($b_enable_search) {?>
		<div class="line">
		<div class="lineTitle"><?php echo $formText_Name_Output; ?></div>
		<div class="lineInput">
			<input class="popupforminput botspace" name="name" type="text" value="<?php echo $v_data['name'];?>" style="width:90%;" autocomplete="off">

			<button type="button" class="btn output-btn search" aria-label="Search" style="margin-bottom:3px;">
				<span class="glyphicon glyphicon-search" aria-hidden="true"></span></div>
			</button>

		<div class="clear"></div>
		</div>
		<?php } ?>
		<?php /*?><div class="line">
		<div class="lineTitle"><?php echo $formText_CompanyNr_Output; ?></div>
		<div class="lineInput"><input class="popupforminput botspace" name="company_nr" type="text" value="<?php echo $v_data['publicRegisterId'];?>"></div>
		<div class="clear"></div>
		</div>
		<div class="line">
		<div class="lineTitle"><?php echo $formText_Street_Output; ?></div>
		<div class="lineInput"><input class="popupforminput botspace" name="street" type="text" value="<?php echo $v_data['paStreet'];?>"></div>
		<div class="clear"></div>
		</div>
		<div class="line">
		<div class="lineTitle"><?php echo $formText_PostalCode_Output; ?></div>
		<div class="lineInput"><input class="popupforminput botspace" name="postal_code" type="text" value="<?php echo $v_data['paPostalNumber'];?>"></div>
		<div class="clear"></div>
		</div>
		<div class="line">
		<div class="lineTitle"><?php echo $formText_City_Output; ?></div>
		<div class="lineInput"><input class="popupforminput botspace" name="city" type="text" value="<?php echo $v_data['paCity'];?>"></div>
		<div class="clear"></div>
		</div>
		<div class="line">
		<div class="lineTitle"><?php echo $formText_Country_Output; ?></div>
		<div class="lineInput"><input class="popupforminput botspace" name="country" type="text" value="<?php echo $v_data['paCountry'];?>"></div>
		<div class="clear"></div>
		</div>
		<div class="line">
		<div class="lineTitle"><?php echo $formText_Phone_Output; ?></div>
		<div class="lineInput"><input class="popupforminput botspace" name="phone" type="text" value="<?php echo $v_data['phone'];?>"></div>
		<div class="clear"></div>
		</div>
		<div class="line">
		<div class="lineTitle"><?php echo $formText_Fax_Output; ?></div>
		<div class="lineInput"><input class="popupforminput botspace" name="fax" type="text" value="<?php echo $v_data['fax'];?>"></div>
		<div class="clear"></div>
		</div>
		<div class="line">
		<div class="lineTitle"><?php echo $formText_Email_Output; ?></div>
		<div class="lineInput"><input class="popupforminput botspace" name="email" type="text" value="<?php echo $v_data['email'];?>"></div>
		<div class="clear"></div>
		</div><?php */?>
		<div class="clear"></div>
		<div class="search-result"></div>
	</div>
	<div class="popupformbtn">
		<button type="button" class="output-btn b-large b-close"><?php echo $formText_Cancel_Output;?></button>
		<input type="submit" name="sbmbtn"<?php if($b_enable_search) {?> class="hide"<?php } ?> value="<?php echo $formText_CreateNewFromCompanyCard_Output; ?>">
	</div>
</form>
</div>
<script type="text/javascript" src="../modules/<?php echo $module?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
<script type="text/javascript">
$(function() {
	$("form.output-form input").on('keydown', function(e){
		if(e.keyCode == 13)
		{
			e.preventDefault();
			output_getynet_customer_search(0);
		}
	});
	$("form.output-form .output-btn.search").on('click', function(e){
		e.preventDefault();
		output_getynet_customer_search(0);
	});
	$("form.output-form").validate({
		submitHandler: function(form){
			$.ajax({
				url: $(form).attr("action"),
				cache: false,
				type: "POST",
				dataType: "json",
				data: $(form).serialize(),
				success: function (data) {
					if(data.error !== undefined)
					{
						$("#popup-validate-message").html(data.error).show();
						fw_loading_end();
						fw_click_instance = false;
					} else {
						if(data.redirect_url !== undefined)
						{
							out_popup.addClass("close-reload");
							out_popup.close();
						}
						fw_click_instance = false;
					}
				}
			}).fail(function() {
				$("#popup-validate-message").html("<?php echo $formText_ErrorOccuredSavingContent_Output;?>", true).show();
				$('#popupeditbox').css('height', $('#popupeditboxcontent').height());
				fw_loading_end();
				fw_click_instance = false;
			});
		},
		invalidHandler: function(event, validator) {
			var errors = validator.numberOfInvalids();
			if (errors) {
				var message = errors == 1
				? '<?php echo $formText_YouMissed_validate; ?> 1 <?php echo $formText_field_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>'
				: '<?php echo $formText_YouMissed_validate; ?> ' + errors + ' <?php echo $formText_fields_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>';

				$("#popup-validate-message").html(message).show();
				$('#popupeditbox').css('height', $('#popupeditboxcontent').height());
			} else {
				$("#popup-validate-message").hide();
			}
			setTimeout(function(){ $('#popupeditbox').height(''); }, 200);
		}
	});
});
function output_getynet_customer_pick(_this)
{
	if(!fw_click_instance)
	{
		fw_click_instance = true;
		fw_loading_start();
		$("#popup-validate-message").html('').hide();
		$.ajax({
			url: "<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=".$s_inc_act ;?>",
			cache: false,
			type: "POST",
			dataType: "json",
			data: {fwajax: 1, fw_nocss: 1, output_form_submit: 1, customer_id: '<?php print $_POST['customer_id'];?>', getynet_customer_id: $(_this).data('customer-id') },
			success: function (data) {
				if(data.error !== undefined)
				{
					$("#popup-validate-message").html(data.error).show();
				} else {
					out_popup.addClass("close-reload");
					out_popup.close();
				}
				fw_loading_end();
				fw_click_instance = false;
			}
		}).fail(function() {
			$("#popup-validate-message").html("<?php echo $formText_ErrorOccuredProcessingRequest_Output;?>", true).show();
			$('#popupeditbox').css('height', $('#popupeditboxcontent').height());
			fw_loading_end();
			fw_click_instance = false;
		});
	}
}
function output_getynet_customer_search(page)
{
	if(!fw_click_instance)
	{
		fw_click_instance = true;
		fw_loading_start();
		$("#popup-validate-message").html('').hide();
		$.ajax({
			url: "<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=".$s_inc_act ;?>",
			cache: false,
			type: "POST",
			dataType: "json",
			data: {fwajax: 1, fw_nocss: 1, output_form_submit: 1, search_name: $("form.output-form input[name=name]").val(), page: page },
			success: function (data) {
				if(data.error !== undefined)
				{
					$("#popup-validate-message").html(data.error).show();
				} else {
					$("form.output-form div.search-result").html(data.html);
				}
				$("form.output-form input[name=sbmbtn]").removeClass('hide');
				fw_loading_end();
				fw_click_instance = false;
			}
		}).fail(function() {
			$("#popup-validate-message").html("<?php echo $formText_ErrorOccuredProcessingRequest_Output;?>", true).show();
			$('#popupeditbox').css('height', $('#popupeditboxcontent').height());
			fw_loading_end();
			fw_click_instance = false;
		});
	}
}
</script>
<style>
.popupform input.popupforminput, .popupform textarea.popupforminput, .popupform select.popupforminput {
	width:100%;
	border-radius: 4px;
	padding:5px 10px;
	font-size:12px;
	line-height:17px;
	color:#3c3c3f;
	background-color:transparent;
	-webkit-box-sizing: border-box;
	   -moz-box-sizing: border-box;
		 -o-box-sizing: border-box;
			box-sizing: border-box;
	font-weight:400;
	border: 1px solid #cccccc;
}
</style>
