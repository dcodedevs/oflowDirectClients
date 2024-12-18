<?php
if($variables->developeraccess < 20){
    echo $formText_CanBeEditedOnlyByServiceProvider_output;
    return;
}
$s_sql = "SELECT * FROM customer_basisconfig ORDER BY id DESC";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
    $customer_basisconfig = $o_query->row_array();
}

$s_sql = "SELECT * FROM customer_accountconfig ORDER BY id DESC";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
    $v_customer_accountconfig = $o_query->row_array();
}
require_once("fnc_rewritebasisconfig.php");
rewriteCustomerBasisconfig();

if($moduleAccesslevel > 10)
{
	if(isset($_POST['output_form_submit']))
	{
		if($_POST['updateCheckbox']) {
			$autorenewal = $_POST['autorenewal'];
			$resourceId = $_POST['resourceId'];

			$s_sql = "UPDATE subscriptiontype SET
			updated = now(),
			updatedBy= ?,
			autorenewal= ?
			WHERE id = ?";
			$o_main->db->query($s_sql, array($variables->loggID, $autorenewal, $resourceId));
			$fw_return_data = $resourceId;
		} else if($_POST['updatePeriodUnit']) {
			$periodUnit = $_POST['periodUnit'];
			$resourceId = $_POST['resourceId'];

			$s_sql = "UPDATE subscriptiontype SET
			updated = now(),
			updatedBy= ?,
			periodUnit= ?
			WHERE id = ?";
			$o_main->db->query($s_sql, array($variables->loggID, $periodUnit, $resourceId));
			$fw_return_data = $resourceId;
		} else if($_POST['updatePeriodising']) {
			$defaultPeriodising = $_POST['periodising'];
			$resourceId = $_POST['resourceId'];

			$s_sql = "UPDATE subscriptiontype SET
			updated = now(),
			updatedBy= ?,
			defaultPeriodising= ?
			WHERE id = ?";
			$o_main->db->query($s_sql, array($variables->loggID, $defaultPeriodising, $resourceId));
			$fw_return_data = $resourceId;
		} else if($_POST['updatehideOnInsider']) {
   			$hideOnInsider = $_POST['hideOnInsider'];
   			$resourceId = $_POST['resourceId'];

   			$s_sql = "UPDATE subscriptiontype SET
   			updated = now(),
   			updatedBy= ?,
   			hide_on_insider= ?
   			WHERE id = ?";
   			$o_main->db->query($s_sql, array($variables->loggID, $hideOnInsider, $resourceId));
   			$fw_return_data = $resourceId;
   		}  else if($_POST['updateshowOnInsider']) {
   			$showOnInsider = $_POST['showOnInsider'];
   			$resourceId = $_POST['resourceId'];

   			$s_sql = "UPDATE subscriptiontype SET
   			updated = now(),
   			updatedBy= ?,
   			show_on_insider_persontab= ?
   			WHERE id = ?";
   			$o_main->db->query($s_sql, array($variables->loggID, $showOnInsider, $resourceId));
   			$fw_return_data = $resourceId;
   		} else if($_POST['updateactivateSpecifiedInvoicing']) {
   			$activateSpecifiedInvoicing = $_POST['activateSpecifiedInvoicing'];
   			$resourceId = $_POST['resourceId'];

   			$s_sql = "UPDATE subscriptiontype SET
   			updated = now(),
   			updatedBy= ?,
   			activate_specified_invoicing= ?
   			WHERE id = ?";
   			$o_main->db->query($s_sql, array($variables->loggID, $activateSpecifiedInvoicing, $resourceId));
   			$fw_return_data = $resourceId;
   		}  else if($_POST['updatedefaultNameInInvoiceLine']) {
   			$defaultNameInInvoiceLine = $_POST['defaultNameInInvoiceLine'];
   			$resourceId = $_POST['resourceId'];

   			$s_sql = "UPDATE subscriptiontype SET
   			updated = now(),
   			updatedBy= ?,
   			default_subscriptionname_in_invoiceline = ?
   			WHERE id = ?";
   			$o_main->db->query($s_sql, array($variables->loggID, $defaultNameInInvoiceLine, $resourceId));
   			$fw_return_data = $resourceId;
   		}  else if($_POST['updatesubscriptionCategory']) {
   			$subscriptionCategory = $_POST['subscriptionCategory'];
   			$resourceId = $_POST['resourceId'];

   			$s_sql = "UPDATE subscriptiontype SET
   			updated = now(),
   			updatedBy= ?,
   			subscription_category = ?
   			WHERE id = ?";
   			$o_main->db->query($s_sql, array($variables->loggID, $subscriptionCategory, $resourceId));
   			$fw_return_data = $resourceId;
   		}  else if($_POST['update_activate_checking_and_doing']) {
   			$activate_checking_and_doing = $_POST['activate_checking_and_doing'];
   			$resourceId = $_POST['resourceId'];

   			$s_sql = "UPDATE subscriptiontype SET
   			updated = now(),
   			updatedBy= ?,
   			activate_checking_and_doing = ?
   			WHERE id = ?";
   			$o_main->db->query($s_sql, array($variables->loggID, $activate_checking_and_doing, $resourceId));
   			$fw_return_data = $resourceId;
   		}  else if($_POST['update_activate_own_tab_in_batchrenewal']) {
   			$activate_own_tab_in_batchrenewal = $_POST['activate_own_tab_in_batchrenewal'];
   			$resourceId = $_POST['resourceId'];

   			$s_sql = "UPDATE subscriptiontype SET
   			updated = now(),
   			updatedBy= ?,
   			activate_own_tab_in_batchrenewal = ?
   			WHERE id = ?";
   			$o_main->db->query($s_sql, array($variables->loggID, $activate_own_tab_in_batchrenewal, $resourceId));
   			$fw_return_data = $resourceId;
   		}   else if($_POST['update_hide_subscriptionlines']) {
   			$hide_subscriptionlines = $_POST['hide_subscriptionlines'];
   			$resourceId = $_POST['resourceId'];

   			$s_sql = "UPDATE subscriptiontype SET
   			updated = now(),
   			updatedBy= ?,
   			hide_subscriptionlines = ?
   			WHERE id = ?";
   			$o_main->db->query($s_sql, array($variables->loggID, $hide_subscriptionlines, $resourceId));
   			$fw_return_data = $resourceId;
   		}  else if($_POST['update_script_for_renewal']) {
   			$script_for_renewal = $_POST['script_for_renewal'];
   			$resourceId = $_POST['resourceId'];

   			$s_sql = "UPDATE subscriptiontype SET
   			updated = now(),
   			updatedBy= ?,
   			script_for_generating_order = ?
   			WHERE id = ?";
   			$o_main->db->query($s_sql, array($variables->loggID, $script_for_renewal, $resourceId));
   			$fw_return_data = $resourceId;
   		}  else if($_POST['update_activateSubmemberWithoutInvoicing']) {
   			$activateSubmemberWithoutInvoicing = $_POST['activateSubmemberWithoutInvoicing'];
   			$resourceId = $_POST['resourceId'];

   			$s_sql = "UPDATE subscriptiontype SET
   			updated = now(),
   			updatedBy = ?,
   			activateSubmemberWithoutInvoicing = ?
   			WHERE id = ?";
   			$o_main->db->query($s_sql, array($variables->loggID, $activateSubmemberWithoutInvoicing, $resourceId));
   			$fw_return_data = $resourceId;
		}  else if($_POST['update_useMainContactAsContactperson']) {
   			$useMainContactAsContactperson = $_POST['useMainContactAsContactperson'];
   			$resourceId = $_POST['resourceId'];

   			$s_sql = "UPDATE subscriptiontype SET
   			updated = now(),
   			updatedBy = ?,
   			useMainContactAsContactperson = ?
   			WHERE id = ?";
   			$o_main->db->query($s_sql, array($variables->loggID, $useMainContactAsContactperson, $resourceId));
   			$fw_return_data = $resourceId;
   		}  else if($_POST['update_activatePricelist2']) {
   			$activatePricelist2 = $_POST['activatePricelist2'];
   			$resourceId = $_POST['resourceId'];

   			$s_sql = "UPDATE subscriptiontype SET
   			updated = now(),
   			updatedBy = ?,
   			activatePricelist2 = ?
   			WHERE id = ?";
   			$o_main->db->query($s_sql, array($variables->loggID, $activatePricelist2, $resourceId));
   			$fw_return_data = $resourceId;
   		} else if($_POST['update_activateSubscriptionInvoiceToOtherCustomer']) {
   			$activatePricelist2 = $_POST['activateSubscriptionInvoiceToOtherCustomer'];
   			$resourceId = $_POST['resourceId'];

   			$s_sql = "UPDATE subscriptiontype SET
   			updated = now(),
   			updatedBy = ?,
   			activateSubscriptionInvoiceToOtherCustomer = ?
   			WHERE id = ?";
   			$o_main->db->query($s_sql, array($variables->loggID, $activatePricelist2, $resourceId));
   			$fw_return_data = $resourceId;
   		}  else if($_POST['update_activateSubtypeFilterInEmailMarketingModule']) {
   			$activatePricelist2 = $_POST['activateSubtypeFilterInEmailMarketingModule'];
   			$resourceId = $_POST['resourceId'];

   			$s_sql = "UPDATE subscriptiontype SET
   			updated = now(),
   			updatedBy = ?,
   			activateSubtypeFilterInEmailMarketingModule = ?
   			WHERE id = ?";
   			$o_main->db->query($s_sql, array($variables->loggID, $activatePricelist2, $resourceId));
   			$fw_return_data = $resourceId;
   		}  else if($_POST['update_activatePersonalSubscriptionConnection']) {
   			$activatePricelist2 = $_POST['activatePersonalSubscriptionConnection'];
   			$resourceId = $_POST['resourceId'];

   			$s_sql = "UPDATE subscriptiontype SET
   			updated = now(),
   			updatedBy = ?,
   			activatePersonalSubscriptionConnection = ?
   			WHERE id = ?";
   			$o_main->db->query($s_sql, array($variables->loggID, $activatePricelist2, $resourceId));
   			$fw_return_data = $resourceId;
   		}  else if($_POST['update_hideContactperson']) {
   			$activatePricelist2 = $_POST['hideContactperson'];
   			$resourceId = $_POST['resourceId'];

   			$s_sql = "UPDATE subscriptiontype SET
   			updated = now(),
   			updatedBy = ?,
   			hide_contactperson = ?
   			WHERE id = ?";
   			$o_main->db->query($s_sql, array($variables->loggID, $activatePricelist2, $resourceId));
   			$fw_return_data = $resourceId;
   		} else {
            if(isset($_POST['editResourceSub']) || isset($_POST['deleteSubResource'])){
                if($_POST['editResourceSub'] > 0) {
    				$s_sql = "UPDATE subscriptiontype_subtype SET
    				updated = now(),
    				updatedBy= ?,
    				name= ?,
                    subscriptiontype_id = ?
    				WHERE id = ?";
    				$o_main->db->query($s_sql, array($variables->loggID, $_POST['resourceName'], $_POST['type_id'], $_POST['editResourceSub']));
    				$fw_return_data = $_POST['editResourceSub'];
    			}
    			else if(intval($_POST['deleteSubResource']) == 0) {
    				$s_sql = "INSERT INTO subscriptiontype_subtype SET
    				id=NULL,
    				moduleID = ?,
    				created = now(),
    				createdBy= ?,
    				name= ?,
                    subscriptiontype_id = ?";
    				$o_main->db->query($s_sql, array($moduleID, $variables->loggID, $_POST['resourceName'], $_POST['type_id']));
    				$fw_return_data = $o_main->db->insert_id();
    			} else {
    				$s_sql = "SELECT * FROM subscriptionmulti WHERE subscriptionsubtypeId = ?";
    				$o_query = $o_main->db->query($s_sql, array($_POST['deleteSubResource']));
    				if($o_query && $o_query->num_rows() == 0) {
    					$s_sql = "DELETE subscriptiontype_subtype FROM subscriptiontype_subtype WHERE subscriptiontype_subtype.id = ?";
    					$o_main->db->query($s_sql, array($_POST['deleteSubResource']));
    				}
    				$fw_return_data = $_POST['deleteSubResource'];
    			}
            } else if(isset($_POST['editResourceSelfdefined']) || isset($_POST['deleteResourceSelfdefined'])) {
                if($_POST['editResourceSelfdefined'] > 0) {
                    $s_sql = "UPDATE subscriptiontype_selfdefined_connection SET
                    updated = now(),
                    updatedBy= ?,
                    subscriptiontype_id = ?,
                    selfdefinedfield_id = ?,
                    not_mandatory = ?
                    WHERE id = ?";
                    $o_main->db->query($s_sql, array($variables->loggID, $_POST['type_id'], $_POST['selfdefinedfield_id'], $_POST['not_mandatory'], $_POST['editResourceSelfdefined']));
                    $fw_return_data = $_POST['editResourceSelfdefined'];
                }
                else if(intval($_POST['deleteResourceSelfdefined']) == 0) {
                    $s_sql = "INSERT INTO subscriptiontype_selfdefined_connection SET
                    id=NULL,
                    moduleID = ?,
                    created = now(),
                    createdBy= ?,
                    subscriptiontype_id = ?,
                    selfdefinedfield_id = ?,
                    not_mandatory = ?";
                    $o_main->db->query($s_sql, array($moduleID, $variables->loggID, $_POST['type_id'], $_POST['selfdefinedfield_id'], $_POST['not_mandatory']));
                    $fw_return_data = $o_main->db->insert_id();
                } else {
                    $s_sql = "DELETE subscriptiontype_selfdefined_connection FROM subscriptiontype_selfdefined_connection WHERE subscriptiontype_selfdefined_connection.id = ?";
                    $o_main->db->query($s_sql, array($_POST['deleteResourceSelfdefined']));
                    $fw_return_data = $_POST['deleteResourceSelfdefined'];
                }
            } else {
    			if($_POST['editResource'] > 0) {
    				$s_sql = "UPDATE subscriptiontype SET
    				updated = now(),
    				updatedBy= ?,
    				name= ?
    				WHERE id = ?";
    				$o_main->db->query($s_sql, array($variables->loggID, $_POST['resourceName'], $_POST['editResource']));
    				$fw_return_data = $_POST['editResource'];
    			}
    			else if(intval($_POST['deleteResource']) == 0) {
    				$s_sql = "INSERT INTO subscriptiontype SET
    				id=NULL,
    				moduleID = ?,
    				created = now(),
    				createdBy= ?,
    				name= ?,
    				autorenewal = 1,
    				periodUnit = 0";
    				$o_main->db->query($s_sql, array($moduleID, $variables->loggID, $_POST['resourceName']));
    				$fw_return_data = $o_main->db->insert_id();
    			} else {
    				$s_sql = "SELECT * FROM subscriptionmulti WHERE subscriptiontypeId = ?";
    				$o_query = $o_main->db->query($s_sql, array($_POST['deleteResource']));
    				if($o_query && $o_query->num_rows() == 0) {
    					$s_sql = "DELETE subscriptiontype FROM subscriptiontype WHERE subscriptiontype.id = ?";
    					$o_main->db->query($s_sql, array($_POST['deleteResource']));
    				}
    				$fw_return_data = $_POST['deleteResource'];
    			}
            }
		}

		echo $fw_return_data;
		$fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=list";
		return;
	}
}
?>
<div class="popupform">
	<div id="popup-validate-message" style="display:none;"></div>

	<div class="popupformTitle"><?php echo $formText_AddEditSubscriptionType_output;?> <?php echo $resource['name']?></div>
	<div class="errorMessage"></div>
	<div class="resourceList" data-action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=add_subscription_type";?>"
	data-action2="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=add_subscription_type";?>">
	<?php
	$resources = array();
	$s_sql = "SELECT * FROM subscriptiontype WHERE content_status < 2 ORDER BY name ASC";
	$o_query = $o_main->db->query($s_sql);
	if($o_query && $o_query->num_rows()>0) {
	    $resources = $o_query->result_array();
	}

	foreach($resources as $resource){
		?>
		<div class="resourceRowSortable" id="sort_<?php echo $resource['id']?>">
			<div class="resourceRow">
				<div class="column nameColumn">
					<div class="columnWrapper"><?php echo $resource['id']." - ".$resource['name']?></div>
				</div>
				<div class="column" style="width: 50%;">
					<div class="columnWrapper">
						<div class="columnInputWrapper">
							<label for="autorenewal<?php echo $resource['id']?>"><?php echo $formText_AutoRenewal_output;?></label>
							<select name="autorenewal" class="autorenewal"  data-resource-id="<?php echo $resource['id']?>" id="autorenewal<?php echo $resource['id']?>" autocomplete="off">
								<option value="1"><?php echo $formText_Yes_Output;?></option>
								<option value="0" <?php if($resource['autorenewal'] == 0) { echo 'selected';}?> ><?php echo $formText_No_Output;?></option>
                                <?php if($v_customer_accountconfig['activateSubTypeRenewalChoiceGP']){ ?>
    								<option value="2" <?php if($resource['autorenewal'] == 2) { echo 'selected';}?> ><?php echo $formText_RenewInGetynetPayOnly_Output;?></option>
                                <?php } ?>
							</select>
						</div>
						<div class="columnInputWrapper">
							<label style="vertical-align: middle;"><?php echo $formText_PeriodSpecifyingUnit_output;?></label>
							<select name="periodUnit" class="periodUnitChange" autocomplete="off"  data-resource-id="<?php echo $resource['id']?>">
								<option value="0"><?php echo $formText_Month_Output;?></option>
								<option value="1" <?php if($resource['periodUnit'] == 1) { echo 'selected';}?> ><?php echo $formText_Year_Output;?></option>
							</select>
						</div>
						<div class="columnInputWrapper">
							<label style="vertical-align: middle;"><?php echo $formText_DefaultPeriodising_output;?></label>
							<select name="periodisingChange" class="periodisingChange" autocomplete="off"  data-resource-id="<?php echo $resource['id']?>">
								<option value="0"><?php echo $formText_None_Output;?></option>
								<option value="1" <?php if($resource['defaultPeriodising'] == 1) { echo 'selected';}?> ><?php echo $formText_DivideOnMonths_Output;?></option>
								<option value="2" <?php if($resource['defaultPeriodising'] == 2) { echo 'selected';}?> ><?php echo $formText_DivideOnDays_Output;?></option>
							</select>
						</div>
						<?php if($v_customer_accountconfig['linked_insider_account'] != "" && $v_customer_accountconfig['linked_insider_account_token'] != "") { ?>
							<div class="columnInputWrapper">
								<label style="vertical-align: middle;"><?php echo $formText_hideOnInsiderAccount_output;?></label>
								<select name="hideOnInsider" class="hideOnInsider" autocomplete="off"  data-resource-id="<?php echo $resource['id']?>">
									<option value="0" <?php if($resource['hide_on_insider'] == 0) { echo 'selected';}?>><?php echo $formText_No_Output;?></option>
									<option value="1" <?php if($resource['hide_on_insider'] == 1) { echo 'selected';}?>><?php echo $formText_Yes_Output;?></option>
								</select>
							</div>
                            <div class="columnInputWrapper">
								<label style="vertical-align: middle;"><?php echo $formText_showOnInsiderPersonTab_output;?></label>
								<select name="showOnInsider" class="showOnInsider" autocomplete="off"  data-resource-id="<?php echo $resource['id']?>">
									<option value="0" <?php if($resource['show_on_insider_persontab'] == 0) { echo 'selected';}?>><?php echo $formText_No_Output;?></option>
									<option value="1" <?php if($resource['show_on_insider_persontab'] == 1) { echo 'selected';}?>><?php echo $formText_Yes_Output;?></option>
								</select>
							</div>
						<?php } ?>
                        <?php if($customer_basisconfig['activate_specified_invoicing']) { ?>
                            <div class="columnInputWrapper">
								<label style="vertical-align: middle;"><?php echo $formText_SpecifiedInvoicing_output;?></label>
								<select name="activateSpecifiedInvoicing" class="activateSpecifiedInvoicing" autocomplete="off"  data-resource-id="<?php echo $resource['id']?>">
									<option value="0" <?php if($resource['activate_specified_invoicing'] == 0) { echo 'selected';}?>><?php echo $formText_No_Output;?></option>
									<option value="1" <?php if($resource['activate_specified_invoicing'] == 1) { echo 'selected';}?>><?php echo $formText_Yes_Output;?></option>
								</select>
							</div>
                        <?php } ?>
                        <div class="columnInputWrapper">
                            <label style="vertical-align: middle;"><?php echo $formText_DefaultPlaceSubscriptionNameInInvoiceLine_output;?></label>
                            <select name="defaultNameInInvoiceLine" class="defaultNameInInvoiceLine" autocomplete="off"  data-resource-id="<?php echo $resource['id']?>">
                                <option value="0" <?php if($resource['default_subscriptionname_in_invoiceline'] == 0) { echo 'selected';}?>><?php echo $formText_No_Output;?></option>
                                <option value="1" <?php if($resource['default_subscriptionname_in_invoiceline'] == 1) { echo 'selected';}?>><?php echo $formText_Yes_Output;?></option>
                            </select>
                        </div>
                        <div class="columnInputWrapper">
                            <label style="vertical-align: middle;"><?php echo $formText_SubscriptionCategory_output;?></label>
                            <select name="subscriptionCategory" class="subscriptionCategory" autocomplete="off"  data-resource-id="<?php echo $resource['id']?>">
                                <option value="0" <?php if($resource['subscription_category'] == 0) { echo 'selected';}?>><?php echo $formText_RepeatingOrder_Output;?></option>
                                <option value="1" <?php if($resource['subscription_category'] == 1) { echo 'selected';}?>><?php echo $formText_CollectWorkProject_Output;?></option>
                                <option value="2" <?php if($resource['subscription_category'] == 2) { echo 'selected';}?>><?php echo $formText_PriceListGeneratingOrder_Output;?></option>
                            </select>
                        </div>
                        <?php if($resource['subscription_category'] == 1) { ?>
                            <div class="columnInputWrapper">
                                <label style="vertical-align: middle;"><?php echo $formText_ActivateCheckingAndDoing_output;?></label>
                                <select name="activate_checking_and_doing" class="activate_checking_and_doing" autocomplete="off"  data-resource-id="<?php echo $resource['id']?>">
                                    <option value="0" <?php if($resource['activate_checking_and_doing'] == 0) { echo 'selected';}?>><?php echo $formText_No_Output;?></option>
                                    <option value="1" <?php if($resource['activate_checking_and_doing'] == 1) { echo 'selected';}?>><?php echo $formText_Yes_Output;?></option>
                                </select>
                            </div>
                        <?php } ?>
                        <div class="columnInputWrapper">
                            <label style="vertical-align: middle;"><?php echo $formText_ActivateOwnTabInBatchrenewal_output;?></label>
                            <select name="activate_own_tab_in_batchrenewal" class="activate_own_tab_in_batchrenewal" autocomplete="off"  data-resource-id="<?php echo $resource['id']?>">
                                <option value="0" <?php if($resource['activate_own_tab_in_batchrenewal'] == 0) { echo 'selected';}?>><?php echo $formText_No_Output;?></option>
                                <option value="1" <?php if($resource['activate_own_tab_in_batchrenewal'] == 1) { echo 'selected';}?>><?php echo $formText_Yes_Output;?></option>
                            </select>
                        </div>
                        <div class="columnInputWrapper">
                            <label style="vertical-align: middle;"><?php echo $formText_HideSubscriptionLines_output;?></label>
                            <select name="hide_subscriptionlines" class="hide_subscriptionlines" autocomplete="off"  data-resource-id="<?php echo $resource['id']?>">
                                <option value="0" <?php if($resource['hide_subscriptionlines'] == 0) { echo 'selected';}?>><?php echo $formText_No_Output;?></option>
                                <option value="1" <?php if($resource['hide_subscriptionlines'] == 1) { echo 'selected';}?>><?php echo $formText_Yes_Output;?></option>
                            </select>
                        </div>
                        <div class="columnInputWrapper">
                            <label style="vertical-align: middle;"><?php echo $formText_ScriptForGeneratingOrder_output;?></label>
                            <select name="script_for_generating_order" class="script_for_generating_order" autocomplete="off"  data-resource-id="<?php echo $resource['id']?>">
                                <option value="" <?php if($resource['script_for_generating_order'] == "") { echo 'selected';}?>><?php echo $formText_Default_Output;?></option>
                                <?php
                                $folders = array();
                                $directories = glob(__DIR__ . '/../../../SubscriptionReportAdvanced/output/includes/scripts/*' , GLOB_ONLYDIR);
                                foreach($directories as $directory){
                                    $folder = array();
                                    $folder['folderName'] = basename($directory);
                                    $folder['folderPath'] = $directory;
                                    array_push($folders, $folder);
                                }

                                foreach($folders as $folder) {
                                    ?>
                                    <option value="<?php echo $folder['folderName']?>" <?php if($resource['script_for_generating_order'] == $folder['folderName']) echo ' selected';?>><?php echo $folder['folderName']?></option>
                                    <?php
                                }
                                ?>
                            </select>
                        </div>
                        <div class="columnInputWrapper">
                            <label style="vertical-align: middle;"><?php echo $formText_ActivateSubmemberWithoutInvoicing_output;?></label>
                            <select name="activateSubmemberWithoutInvoicing" class="activateSubmemberWithoutInvoicing" autocomplete="off"  data-resource-id="<?php echo $resource['id']?>">
                                <option value="0" <?php if($resource['activateSubmemberWithoutInvoicing'] == 0) { echo 'selected';}?>><?php echo $formText_No_Output;?></option>
                                <option value="1" <?php if($resource['activateSubmemberWithoutInvoicing'] == 1) { echo 'selected';}?>><?php echo $formText_Yes_Output;?></option>
                            </select>
                        </div>
                        <div class="columnInputWrapper">
                            <label style="vertical-align: middle;"><?php echo $formText_useMainContactOnInvoices_output;?></label>
                            <select name="useMainContactAsContactperson" class="useMainContactAsContactperson" autocomplete="off"  data-resource-id="<?php echo $resource['id']?>">
                                <option value="0" <?php if($resource['useMainContactAsContactperson'] == 0) { echo 'selected';}?>><?php echo $formText_No_Output;?></option>
                                <option value="1" <?php if($resource['useMainContactAsContactperson'] == 1) { echo 'selected';}?>><?php echo $formText_Yes_Output;?></option>
                            </select>
                        </div>
                        <div class="columnInputWrapper">
                            <label style="vertical-align: middle;"><?php echo $formText_HideContactpersons_output;?></label>
                            <select name="hideContactperson" class="hideContactperson" autocomplete="off"  data-resource-id="<?php echo $resource['id']?>">
                                <option value="0" <?php if($resource['hide_contactperson'] == 0) { echo 'selected';}?>><?php echo $formText_No_Output;?></option>
                                <option value="1" <?php if($resource['hide_contactperson'] == 1) { echo 'selected';}?>><?php echo $formText_Yes_Output;?></option>
                            </select>
                        </div>
                        <div class="columnInputWrapper">
                            <label style="vertical-align: middle;"><?php echo $formText_ActivatePricelist2_output;?></label>
                            <select name="activatePricelist2" class="activatePricelist2" autocomplete="off"  data-resource-id="<?php echo $resource['id']?>">
                                <option value="0" <?php if($resource['activatePricelist2'] == 0) { echo 'selected';}?>><?php echo $formText_No_Output;?></option>
                                <option value="1" <?php if($resource['activatePricelist2'] == 1) { echo 'selected';}?>><?php echo $formText_Yes_Output;?></option>
                            </select>
                        </div>
                        <div class="columnInputWrapper">
                            <label style="vertical-align: middle;"><?php echo $formText_ActivateSubscriptionInvoiceToOtherCustomer_output;?></label>
                            <select name="activateSubscriptionInvoiceToOtherCustomer" class="activateSubscriptionInvoiceToOtherCustomer" autocomplete="off"  data-resource-id="<?php echo $resource['id']?>">
                                <option value="0" <?php if($resource['activateSubscriptionInvoiceToOtherCustomer'] == 0) { echo 'selected';}?>><?php echo $formText_No_Output;?></option>
                                <option value="1" <?php if($resource['activateSubscriptionInvoiceToOtherCustomer'] == 1) { echo 'selected';}?>><?php echo $formText_Yes_Output;?></option>
                            </select>
                        </div>
                        <div class="columnInputWrapper">
                            <label style="vertical-align: middle;"><?php echo $formText_ActivateSubtypeFilterInEmailMarketingModule_output;?></label>
                            <select name="activateSubtypeFilterInEmailMarketingModule" class="activateSubtypeFilterInEmailMarketingModule" autocomplete="off"  data-resource-id="<?php echo $resource['id']?>">
                                <option value="0" <?php if($resource['activateSubtypeFilterInEmailMarketingModule'] == 0) { echo 'selected';}?>><?php echo $formText_No_Output;?></option>
                                <option value="1" <?php if($resource['activateSubtypeFilterInEmailMarketingModule'] == 1) { echo 'selected';}?>><?php echo $formText_Yes_Output;?></option>
                            </select>
                        </div>
                        <div class="columnInputWrapper">
                            <label style="vertical-align: middle;"><?php echo $formText_ActivatePersonalSubscriptionConnection_output;?></label>
                            <select name="activatePersonalSubscriptionConnection" class="activatePersonalSubscriptionConnection" autocomplete="off"  data-resource-id="<?php echo $resource['id']?>">
                                <option value="0" <?php if($resource['activatePersonalSubscriptionConnection'] == 0) { echo 'selected';}?>><?php echo $formText_No_Output;?></option>
                                <option value="1" <?php if($resource['activatePersonalSubscriptionConnection'] == 1) { echo 'selected';}?>><?php echo $formText_Yes_Output;?></option>
                            </select>
                        </div>

                        <div class="columnInputWrapper">
                            <?php
                            $subtypes = array();
                            $s_sql = "SELECT * FROM subscriptiontype_subtype WHERE subscriptiontype_id = ? ORDER BY name ASC";
                        	$o_query = $o_main->db->query($s_sql, array($resource['id']));
                        	if($o_query && $o_query->num_rows()>0) {
                        	    $subtypes = $o_query->result_array();
                        	}
                            ?>
                            <div class="subtypeWrapper">
                                <label style="vertical-align: middle;" for="subtypes_<?php echo $resource['id'];?>"><?php echo $formText_Subtypes_output;?></label>

                                <div class="subtypes">
                                <?php foreach($subtypes as $subtype) {
                                ?>
                                <div class="subtype">
                                    <div class="resourceRow2">
                                        <div class="column">
                        					<div class="columnWrapper">
                                                <?php echo $subtype['id']." - ".$subtype['name'];?>
                                                <?php if($subtype['type'] == 4) echo " (".$formText_Free_output.")";?>
                                            </div>
                                        </div>
                                        <div class="column actionColumn">
                        					<div class="columnWrapper">
                    							<ul class="actions">
                    	                            <?php if ($moduleAccesslevel > 10): ?>
                    	                                <?php if (!$owneraccess || ($owneraccess && $_GET['userID'] == $subtype['createdBy'])): ?>
                    	    								<li class="edit">
                    	    									<a href="" data-edit-resource2-first="<?php echo $subtype['id']; ?>" alt="<?php echo $formText_Edit_output;?>" title="<?php echo $formText_Edit_output;?>">
                    	    										<span class="glyphicon glyphicon-edit"></span>
                    	    									</a>
                    	    								</li>
                    	                                <?php endif; ?>
                    	                            <?php endif; ?>

                    	                            <?php if ($moduleAccesslevel > 100):
                    	                            	$s_sql = "SELECT * FROM subscriptionmulti WHERE subscriptionsubtypeId = ?";
                    									$o_query = $o_main->db->query($s_sql, array($subtype['id']));
                    									if($o_query && $o_query->num_rows() == 0) {
                    		                            	 ?>
                    		                                <?php if (!$owneraccess || ($owneraccess && $_GET['userID'] == $subtype['createdBy'])): ?>
                    		    								<li class="delete">
                    		    									<a href="" data-delete-resource2-first="<?php echo $subtype['id']; ?>" alt="<?php echo $formText_Delete_output;?>" title="<?php echo $formText_Delete_output;?>">
                    		    										<span class="glyphicon glyphicon-trash"></span>
                    		    									</a>
                    		    								</li>
                    		                                <?php endif; ?>
                    		                            <?php } ?>
                    	                            <?php endif; ?>
                    							</ul>
                        					</div>
                        				</div>
                        				<div class="clear"></div>
                                    </div>
                                    <div class="deleteRow">
                    					<ul class="actions">
                    						<li class="delete">
                    							<a href="" data-delete-resource2-id="<?php echo $subtype['id']; ?>" alt="<?php echo $formText_Delete_output;?>" title="<?php echo $formText_Delete_output;?>">
                    								<?php echo $formText_Delete_output;?>
                    							</a>
                    						</li>
                    						<li class="cancel">
                    							<a href="" data-delete2-cancel="1"><?php echo $formText_Cancel_output;?></a>
                    						</li>
                    					</ul>
                    				</div>
                    				<div class="editRow">
                    					<input type="text" name="resourceName" id="resourceSub_<?php echo $resource['id']?>_<?php echo $subtype['id'];?>" value="<?php echo $subtype['name']?>" autocomplete="off"/>

                                        <div class="editRowDivider"></div>
                                        <div class="save" data-resource-save2-id="<?php echo $subtype['id']?>" data-type-id="<?php echo $resource['id'];?>"><?php echo $formText_Save_output?></div>
                    					<div class="cancel" data-resource-save2-cancel="<?php echo $subtype['id']?>"><?php echo $formText_Cancel_output?></div>
                    				</div>
                                </div>
                                <?php
                                }
                                ?>

                            	<div class="newResource2 editRow">
                            		<input type="text" name="resourceName" id="resourceSub_<?php echo $resource['id'];?>_0" value="" autocomplete="off"/>

                                    <div class="editRowDivider"></div>
                            		<div class="save" data-resource-save2-id="0" data-type-id="<?php echo $resource['id'];?>"><?php echo $formText_Save_output?></div>
                            		<div class="cancel" data-resource-save2-cancel="0"><?php echo $formText_Cancel_output?></div>
                            	</div>
                            	<div class="addNewSubType">
                            		<div class="plusTextBox active">
                            			<div class="plusBox addres"><div class="lineH"></div><div class="lineV"></div></div>
                            			<div class="text"><?php echo $formText_AddSubscriptionSubType_output; ?></div>
                            		</div>
                            		<div class="clear"></div>
                            	</div>
                            </div>
                            </div>
                        </div>
                        <div class="columnInputWrapper">
                            <?php
                            $subtypes = array();
                            $s_sql = "SELECT subscriptiontype_selfdefined_connection.*, customer_selfdefined_fields.name FROM subscriptiontype_selfdefined_connection
                            LEFT OUTER JOIN customer_selfdefined_fields ON customer_selfdefined_fields.id = subscriptiontype_selfdefined_connection.selfdefinedfield_id
                            WHERE subscriptiontype_selfdefined_connection.subscriptiontype_id = ? ORDER BY customer_selfdefined_fields.name ASC";
                        	$o_query = $o_main->db->query($s_sql, array($resource['id']));
                        	if($o_query && $o_query->num_rows()>0) {
                        	    $subtypes = $o_query->result_array();
                        	}
                            ?>
                            <div class="subtypeWrapper">
                                <label style="vertical-align: middle;" for="selfdefinedfields_<?php echo $resource['id'];?>"><?php echo $formText_SelfdefinedfieldChoices_output;?></label>

                                <div class="subtypes">
                                <?php foreach($subtypes as $subtype) {
                                ?>
                                <div class="subtype">
                                    <div class="resourceRow3">
                                        <div class="column">
                        					<div class="columnWrapper">
                                                <?php echo $subtype['name'];?>
                                                <?php if($subtype['not_mandatory'] == 1) echo " (".$formText_NotMandatory_output.")";?>
                                            </div>
                                        </div>
                                        <div class="column actionColumn">
                        					<div class="columnWrapper">
                    							<ul class="actions">
                    	                            <?php if ($moduleAccesslevel > 10): ?>
                    	                                <?php if (!$owneraccess || ($owneraccess && $_GET['userID'] == $subtype['createdBy'])): ?>
                    	    								<li class="edit">
                    	    									<a href="" data-edit-resource3-first="<?php echo $subtype['id']; ?>" alt="<?php echo $formText_Edit_output;?>" title="<?php echo $formText_Edit_output;?>">
                    	    										<span class="glyphicon glyphicon-edit"></span>
                    	    									</a>
                    	    								</li>
                    	                                <?php endif; ?>
                    	                            <?php endif; ?>

                    	                            <?php if ($moduleAccesslevel > 100): ?>
                		                                <?php if (!$owneraccess || ($owneraccess && $_GET['userID'] == $subtype['createdBy'])): ?>
                		    								<li class="delete">
                		    									<a href="" data-delete-resource3-first="<?php echo $subtype['id']; ?>" alt="<?php echo $formText_Delete_output;?>" title="<?php echo $formText_Delete_output;?>">
                		    										<span class="glyphicon glyphicon-trash"></span>
                		    									</a>
                		    								</li>
                		                                <?php endif; ?>
                    	                            <?php endif; ?>
                    							</ul>
                        					</div>
                        				</div>
                        				<div class="clear"></div>
                                    </div>
                                    <div class="deleteRow">
                    					<ul class="actions">
                    						<li class="delete">
                    							<a href="" data-delete-resource3-id="<?php echo $subtype['id']; ?>" alt="<?php echo $formText_Delete_output;?>" title="<?php echo $formText_Delete_output;?>">
                    								<?php echo $formText_Delete_output;?>
                    							</a>
                    						</li>
                    						<li class="cancel">
                    							<a href="" data-delete3-cancel="1"><?php echo $formText_Cancel_output;?></a>
                    						</li>
                    					</ul>
                    				</div>
                    				<div class="editRow">
                    					<label for="resourceSelfdefined_<?php echo $resource['id']?>_<?php echo $subtype['id'];?>"><?php echo $formText_SelfdefinedField_output;?></label>
                                        <select id="resourceSelfdefined_<?php echo $resource['id']?>_<?php echo $subtype['id'];?>" autocomplete="off">
                                            <option value=""><?php echo $formText_Select_output;?></option>
                                            <?php
                            				$o_query = $o_main->db->query("SELECT * FROM customer_selfdefined_fields WHERE content_status = 0 ORDER BY name");
                            				if($o_query && $o_query->num_rows()>0)
                            				foreach($o_query->result_array() as $v_row)
                            				{
                            					?><option value="<?php echo $v_row['id'];?>"<?php echo ($v_row['id'] == $subtype['selfdefinedfield_id'] ? ' selected' : '');?>><?php echo $v_row['name'];?></option><?php
                            				}
                            				?>
                                        </select>

                                        <label for="resourceSelfdefinedNotMandatory_<?php echo $resource['id']?>_<?php echo $subtype['id'];?>"><?php echo $formText_NotMandatory_output;?></label>

                                        <input type="checkbox" class="checkbox" id="resourceSelfdefinedNotMandatory_<?php echo $resource['id']?>_<?php echo $subtype['id'];?>" autocomplete="off" value="1" <?php if($subtype['not_mandatory'] == 1)  echo 'checked';?>/>


                                        <div class="editRowDivider"></div>
                                        <div class="save" data-resource-save3-id="<?php echo $subtype['id']?>" data-type-id="<?php echo $resource['id'];?>"><?php echo $formText_Save_output?></div>
                    					<div class="cancel" data-resource-save3-cancel="<?php echo $subtype['id']?>"><?php echo $formText_Cancel_output?></div>
                    				</div>
                                </div>
                                <?php
                                }
                                ?>

                            	<div class="newResource3 editRow">
                                    <label for="resourceSelfdefined_<?php echo $resource['id']?>_0"><?php echo $formText_SelfdefinedField_output;?></label>
                                    <select id="resourceSelfdefined_<?php echo $resource['id']?>_0" autocomplete="off">
                                        <option value=""><?php echo $formText_Select_output;?></option>
                                        <?php
                                        $o_query = $o_main->db->query("SELECT * FROM customer_selfdefined_fields WHERE content_status = 0 ORDER BY name");
                                        if($o_query && $o_query->num_rows()>0)
                                        foreach($o_query->result_array() as $v_row)
                                        {
                                            ?><option value="<?php echo $v_row['id'];?>"><?php echo $v_row['name'];?></option><?php
                                        }
                                        ?>
                                    </select>
                                    <label for="resourceSelfdefinedNotMandatory_<?php echo $resource['id']?>_0"><?php echo $formText_NotMandatory_output;?></label>

                                    <input type="checkbox" class="checkbox"  id="resourceSelfdefinedNotMandatory_<?php echo $resource['id']?>_0" autocomplete="off" value="1"/>

                                    <div class="editRowDivider"></div>
                            		<div class="save" data-resource-save3-id="0" data-type-id="<?php echo $resource['id'];?>"><?php echo $formText_Save_output?></div>
                            		<div class="cancel" data-resource-save3-cancel="0"><?php echo $formText_Cancel_output?></div>
                            	</div>
                            	<div class="addNewSelfdefined">
                            		<div class="plusTextBox active">
                            			<div class="plusBox addres"><div class="lineH"></div><div class="lineV"></div></div>
                            			<div class="text"><?php echo $formText_AddselfdefinedfieldChoice_output; ?></div>
                            		</div>
                            		<div class="clear"></div>
                            	</div>
                            </div>
                            </div>
                        </div>
					</div>
				</div>
				<div class="column actionColumn">
					<div class="columnWrapper">
						<?php if(!$resource['disabledForEditing']) { ?>
							<ul class="actions">
	                            <?php if ($moduleAccesslevel > 10): ?>
	                                <?php if (!$owneraccess || ($owneraccess && $_GET['userID'] == $resource['createdBy'])): ?>
	    								<li class="edit">
	    									<a href="" data-edit-resource-first="<?php echo $resource['id']; ?>" alt="<?php echo $formText_Edit_output;?>" title="<?php echo $formText_Edit_output;?>">
	    										<span class="glyphicon glyphicon-edit"></span>
	    									</a>
	    								</li>
	                                <?php endif; ?>
	                            <?php endif; ?>

	                            <?php if ($moduleAccesslevel > 100):
	                            	$s_sql = "SELECT * FROM subscriptionmulti WHERE subscriptiontypeId = ?";
									$o_query = $o_main->db->query($s_sql, array($resource['id']));
									if($o_query && $o_query->num_rows() == 0) {
		                            	 ?>
		                                <?php if (!$owneraccess || ($owneraccess && $_GET['userID'] == $resource['createdBy'])): ?>
		    								<li class="delete">
		    									<a href="" data-delete-resource-first="<?php echo $resource['id']; ?>" alt="<?php echo $formText_Delete_output;?>" title="<?php echo $formText_Delete_output;?>">
		    										<span class="glyphicon glyphicon-trash"></span>
		    									</a>
		    								</li>
		                                <?php endif; ?>
		                            <?php } ?>
	                            <?php endif; ?>
							</ul>
						<?php } ?>
					</div>
				</div>
				<div class="clear"></div>
			</div>
			<?php if(!$resource['disabledForEditing']) { ?>
				<div class="deleteRow">
					<ul class="actions">
						<li class="delete">
							<a href="" data-delete-resource-id="<?php echo $resource['id']; ?>" alt="<?php echo $formText_Delete_output;?>" title="<?php echo $formText_Delete_output;?>">
								<?php echo $formText_Delete_output;?>
							</a>
						</li>
						<li class="cancel">
							<a href="" data-delete-cancel="1"><?php echo $formText_Cancel_output;?></a>
						</li>
					</ul>
				</div>
				<div class="editRow">
					<input type="text" name="resourceName" id="resource<?php echo $resource['id']?>" value="<?php echo $resource['name']?>" autocomplete="off"/>
					<div class="save" data-resource-save-id="<?php echo $resource['id']?>"><?php echo $formText_Save_output?></div>
					<div class="cancel" data-resource-save-cancel="<?php echo $resource['id']?>"><?php echo $formText_Cancel_output?></div>
				</div>
			<?php } ?>

		</div>
		<?php
	}
	?>
	</div>
	<div class="newResource editRow">
		<input type="text" name="resourceName" id="resource0" value="" autocomplete="off"/>
		<div class="save" data-resource-save-id="0"><?php echo $formText_Save_output?></div>
		<div class="cancel" data-resource-save-cancel="0"><?php echo $formText_Cancel_output?></div>
	</div>
	<div class="addNew">
		<div class="plusTextBox active">
			<div class="plusBox addres"><div class="lineH"></div><div class="lineV"></div></div>
			<div class="text"><?php echo $formText_AddSubscriptionType_output; ?></div>
		</div>
		<div class="clear"></div>
	</div>

	<!-- <div class="explanation"><?php echo $formText_DragAndDropToChangeOrder_output;?></div> -->
</div>

<script type="text/javascript" src="../modules/<?php echo $module?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
<script type="text/javascript">
$(function() {
	resizePopupEdit();
	function resizePopupEdit(){

	}
	$(window).resize(resizePopupEdit);
	bindPopupActions();
	function bindPopupActions(){
		// $(".resourceList").sortable({
		// 	update: function(event, ui) {
		//         var info = $(this).sortable("serialize");
		//         var action = $(this).data("action");
		//         $.ajax({
		// 			type: 'POST',
		// 			url: action,
		// 			data: info,
		// 			success: function(data){
		// 				var result = $.parseJSON(data);
		// 				// success
		// 				if(result.result == 1){
		// 					$(".popupform .errorMessage").hide();
		// 				} else {
		// 					$(".popupform .errorMessage").html("<?php echo $formText_ErrorChangingResourceOrder_output;?>").show();
		// 				}
		// 			}
		// 		});
		//     }
		// });
		// Edit resource
		$("[data-edit-resource-first]").unbind("click").on('click', function(e){
			e.preventDefault();
			$(this).parents(".resourceRow").next(".deleteRow").hide();
			$(this).parents(".resourceRow").next().next(".editRow").show();
		});
		$("[data-resource-save-cancel").unbind("click").on('click', function(e){
			e.preventDefault();
			$(this).parents(".editRow").hide();
		})
		$("[data-resource-save-id").unbind("click").on('click', function(e){
			e.preventDefault();
			var resourceID = $(this).data('resource-save-id'),
				resourceName = $("#resource"+resourceID).val(),
				self = $(this);
		        var action = $(".resourceList").data("action2");
			$.ajax({
				type: 'POST',
				url: action,
				cache: false,
				data: 'fwajax=1&fw_nocss=1&editResource=' + resourceID + '&output_form_submit=1&resourceName='+resourceName,
				success: function(result){
					if(parseInt(result.html) > 0){
						ajaxCall('add_subscription_type', {}, function(obj) {
				            $('#popupeditboxcontent').html('');
				            $('#popupeditboxcontent').html(obj.html);
				            out_popup = $('#popupeditbox').bPopup(out_popup_options);
				            $("#popupeditbox:not(.opened)").remove();
				        });
					} else {
						$(".popupform .errorMessage").html("<?php echo $formText_ErrorSavingResource_output;?>").show();
					}
				}
			});
		})
        $("[data-resource-save2-id").unbind("click").on('click', function(e){
			e.preventDefault();
            var typeId = $(this).data("type-id");
			var resourceID = $(this).data('resource-save2-id'),
				resourceName = $("#resourceSub_"+typeId+"_"+resourceID).val(),
				self = $(this);

	        var action = $(".resourceList").data("action2");
			$.ajax({
				type: 'POST',
				url: action,
				cache: false,
				data: 'fwajax=1&fw_nocss=1&editResourceSub=' + resourceID + '&output_form_submit=1&type_id='+typeId+'&resourceName='+resourceName,
				success: function(result){
					if(parseInt(result.html) > 0){
						ajaxCall('add_subscription_type', {}, function(obj) {
				            $('#popupeditboxcontent').html('');
				            $('#popupeditboxcontent').html(obj.html);
				            out_popup = $('#popupeditbox').bPopup(out_popup_options);
				            $("#popupeditbox:not(.opened)").remove();
				        });
					} else {
						$(".popupform .errorMessage").html("<?php echo $formText_ErrorSavingResource_output;?>").show();
					}
				}
			});
		})

		$("[data-resource-save2-cancel").unbind("click").on('click', function(e){
			e.preventDefault();
			$(this).parents(".editRow").hide();
		})
        $("[data-edit-resource2-first]").unbind("click").on('click', function(e){
			e.preventDefault();
			$(this).parents(".resourceRow2").next(".deleteRow").hide();
			$(this).parents(".resourceRow2").next().next(".editRow").show();
		});
        // Delete resource
		$("[data-delete-resource2-first").unbind("click").on('click', function(e){
			e.preventDefault();
			$(this).parents(".resourceRow2").next().next(".editRow").hide();
			$(this).parents(".resourceRow2").next(".deleteRow").show();
		})
		$("[data-delete2-cancel").unbind("click").on('click', function(e){
			e.preventDefault();
			$(this).parents(".deleteRow").hide();
		})
		$("[data-delete-resource2-id]").unbind("click").on('click', function(e){
			e.preventDefault();
			var resourceName = $(this).parents(".resourceRow2").find(".nameColumn .columnWrapper").html();
			var self = $(this);
	        var action = $(".resourceList").data("action2");
			$.ajax({
				type: 'POST',
				url: action,
				cache: false,
				data: 'output_form_submit=1&fwajax=1&fw_nocss=1&deleteSubResource=' + $(this).data('delete-resource2-id'),
				success: function(result){
					if(parseInt(result.html) != 0){
						var deleteRow = self.parents(".deleteRow");
						deleteRow.hide();
						deleteRow.prev('.resourceRow2').remove();
						$(".popupform .errorMessage").hide();
					} else {
						$(".popupform .errorMessage").html("<?php echo $formText_ErrorDeletingResource_output;?>").show();
					}
				}
			});
		});

		// Delete resource
		$("[data-delete-resource-first").unbind("click").on('click', function(e){
			e.preventDefault();
			$(this).parents(".resourceRow").next().next(".editRow").hide();
			$(this).parents(".resourceRow").next(".deleteRow").show();
		})
		$("[data-delete-cancel").unbind("click").on('click', function(e){
			e.preventDefault();
			$(this).parents(".deleteRow").hide();
		})
		$("[data-delete-resource-id]").unbind("click").on('click', function(e){
			e.preventDefault();
			var resourceName = $(this).parents(".resourceRow").find(".nameColumn .columnWrapper").html();
			var self = $(this);
	        var action = $(".resourceList").data("action2");
			$.ajax({
				type: 'POST',
				url: action,
				cache: false,
				data: 'output_form_submit=1&fwajax=1&fw_nocss=1&deleteResource=' + $(this).data('delete-resource-id'),
				success: function(result){
					if(parseInt(result.html) != 0){
						var deleteRow = self.parents(".deleteRow");
						deleteRow.hide();
						deleteRow.prev('.resourceRow').remove();
						$(".popupform .errorMessage").hide();
					} else {
						$(".popupform .errorMessage").html("<?php echo $formText_ErrorDeletingResource_output;?>").show();
					}
				}
			});
		});

        $("[data-resource-save3-id").unbind("click").on('click', function(e){
			e.preventDefault();
            var typeId = $(this).data("type-id");
			var editResourceSelfdefined = $(this).data('resource-save3-id'),
				selfdefinedfield_id = $("#resourceSelfdefined_"+typeId+"_"+editResourceSelfdefined).val(),
                not_mandatory = 0;
                if($("#resourceSelfdefinedNotMandatory_"+typeId+"_"+editResourceSelfdefined).is(":checked")){
                    not_mandatory = 1;
                }
				self = $(this);

	        var action = $(".resourceList").data("action2");
			$.ajax({
				type: 'POST',
				url: action,
				cache: false,
				data: 'fwajax=1&fw_nocss=1&editResourceSelfdefined=' + editResourceSelfdefined + '&output_form_submit=1&type_id='+typeId+'&selfdefinedfield_id='+selfdefinedfield_id+"&not_mandatory="+not_mandatory,
				success: function(result){
					if(parseInt(result.html) > 0){
						ajaxCall('add_subscription_type', {}, function(obj) {
				            $('#popupeditboxcontent').html('');
				            $('#popupeditboxcontent').html(obj.html);
				            out_popup = $('#popupeditbox').bPopup(out_popup_options);
				            $("#popupeditbox:not(.opened)").remove();
				        });
					} else {
						$(".popupform .errorMessage").html("<?php echo $formText_ErrorSavingResource_output;?>").show();
					}
				}
			});
		})

		$("[data-resource-save3-cancel").unbind("click").on('click', function(e){
			e.preventDefault();
			$(this).parents(".editRow").hide();
		})
        $("[data-edit-resource3-first]").unbind("click").on('click', function(e){
			e.preventDefault();
			$(this).parents(".resourceRow3").next(".deleteRow").hide();
			$(this).parents(".resourceRow3").next().next(".editRow").show();
		});
        // Delete resource
		$("[data-delete-resource3-first").unbind("click").on('click', function(e){
			e.preventDefault();
			$(this).parents(".resourceRow3").next().next(".editRow").hide();
			$(this).parents(".resourceRow3").next(".deleteRow").show();
		})
		$("[data-delete3-cancel").unbind("click").on('click', function(e){
			e.preventDefault();
			$(this).parents(".deleteRow").hide();
		})
		$("[data-delete-resource3-id]").unbind("click").on('click', function(e){
			e.preventDefault();
			var resourceName = $(this).parents(".resourceRow3").find(".nameColumn .columnWrapper").html();
			var self = $(this);
	        var action = $(".resourceList").data("action2");
			$.ajax({
				type: 'POST',
				url: action,
				cache: false,
				data: 'output_form_submit=1&fwajax=1&fw_nocss=1&deleteResourceSelfdefined=' + $(this).data('delete-resource3-id'),
				success: function(result){
					if(parseInt(result.html) != 0){
						var deleteRow = self.parents(".deleteRow");
						deleteRow.hide();
						deleteRow.prev('.resourceRow3').remove();
						$(".popupform .errorMessage").hide();
					} else {
						$(".popupform .errorMessage").html("<?php echo $formText_ErrorDeletingResource_output;?>").show();
					}
				}
			});
		});

		$(".popupform .addNew").unbind("click").bind("click", function(){
			$(".newResource").show();
		})
        $(".popupform .addNewSubType").unbind("click").bind("click", function(){
            $(this).parents(".subtypes").find(".newResource2").show();
		})
        $(".popupform .addNewSelfdefined").unbind("click").bind("click", function(){
            $(this).parents(".subtypes").find(".newResource3").show();
		})

		$(".autorenewal").change(function(){
			var checked = $(this).val();
			var resourceId = $(this).data("resource-id");
			var action = $(".resourceList").data("action2");
			fw_loading_start();
			$.ajax({
				type: 'POST',
				url: action,
				cache: false,
				data: 'output_form_submit=1&fwajax=1&fw_nocss=1&updateCheckbox=1&resourceId=' + resourceId+'&autorenewal='+checked,
				success: function(result){
					fw_loading_end();
					if(parseInt(result.html) > 0){
						ajaxCall('add_subscription_type', {}, function(obj) {
				            $('#popupeditboxcontent').html('');
				            $('#popupeditboxcontent').html(obj.html);
				            out_popup = $('#popupeditbox').bPopup(out_popup_options);
				            $("#popupeditbox:not(.opened)").remove();
				        });
					} else {
						$(".popupform .errorMessage").html("<?php echo $formText_ErrorSavingResource_output;?>").show();
					}
				}
			});
		})
		$(".periodUnitChange").change(function(){
			var periodUnit = $(this).val();
			var resourceId = $(this).data("resource-id");
			var action = $(".resourceList").data("action2");

			fw_loading_start();
			$.ajax({
				type: 'POST',
				url: action,
				cache: false,
				data: 'output_form_submit=1&fwajax=1&fw_nocss=1&updatePeriodUnit=1&resourceId=' + resourceId+'&periodUnit='+periodUnit,
				success: function(result){
					fw_loading_end();
					if(parseInt(result.html) > 0){
						ajaxCall('add_subscription_type', {}, function(obj) {
				            $('#popupeditboxcontent').html('');
				            $('#popupeditboxcontent').html(obj.html);
				            out_popup = $('#popupeditbox').bPopup(out_popup_options);
				            $("#popupeditbox:not(.opened)").remove();
				        });
					} else {
						$(".popupform .errorMessage").html("<?php echo $formText_ErrorSavingResource_output;?>").show();
					}
				}
			});
		})
		$(".periodisingChange").change(function(){
			var periodUnit = $(this).val();
			var resourceId = $(this).data("resource-id");
			var action = $(".resourceList").data("action2");

			fw_loading_start();
			$.ajax({
				type: 'POST',
				url: action,
				cache: false,
				data: 'output_form_submit=1&fwajax=1&fw_nocss=1&updatePeriodising=1&resourceId=' + resourceId+'&periodising='+periodUnit,
				success: function(result){
					fw_loading_end();
					if(parseInt(result.html) > 0){
						ajaxCall('add_subscription_type', {}, function(obj) {
				            $('#popupeditboxcontent').html('');
				            $('#popupeditboxcontent').html(obj.html);
				            out_popup = $('#popupeditbox').bPopup(out_popup_options);
				            $("#popupeditbox:not(.opened)").remove();
				        });
					} else {
						$(".popupform .errorMessage").html("<?php echo $formText_ErrorSavingResource_output;?>").show();
					}
				}
			});
		})
		$(".hideOnInsider").change(function(){
			var periodUnit = $(this).val();
			var resourceId = $(this).data("resource-id");
			var action = $(".resourceList").data("action2");

			fw_loading_start();
			$.ajax({
				type: 'POST',
				url: action,
				cache: false,
				data: 'output_form_submit=1&fwajax=1&fw_nocss=1&updatehideOnInsider=1&resourceId=' + resourceId+'&hideOnInsider='+periodUnit,
				success: function(result){
					fw_loading_end();
					if(parseInt(result.html) > 0){
						ajaxCall('add_subscription_type', {}, function(obj) {
				            $('#popupeditboxcontent').html('');
				            $('#popupeditboxcontent').html(obj.html);
				            out_popup = $('#popupeditbox').bPopup(out_popup_options);
				            $("#popupeditbox:not(.opened)").remove();
				        });
					} else {
						$(".popupform .errorMessage").html("<?php echo $formText_ErrorSavingResource_output;?>").show();
					}
				}
			});
		})
		$(".showOnInsider").change(function(){
			var periodUnit = $(this).val();
			var resourceId = $(this).data("resource-id");
			var action = $(".resourceList").data("action2");

			fw_loading_start();
			$.ajax({
				type: 'POST',
				url: action,
				cache: false,
				data: 'output_form_submit=1&fwajax=1&fw_nocss=1&updateshowOnInsider=1&resourceId=' + resourceId+'&showOnInsider='+periodUnit,
				success: function(result){
					fw_loading_end();
					if(parseInt(result.html) > 0){
						ajaxCall('add_subscription_type', {}, function(obj) {
				            $('#popupeditboxcontent').html('');
				            $('#popupeditboxcontent').html(obj.html);
				            out_popup = $('#popupeditbox').bPopup(out_popup_options);
				            $("#popupeditbox:not(.opened)").remove();
				        });
					} else {
						$(".popupform .errorMessage").html("<?php echo $formText_ErrorSavingResource_output;?>").show();
					}
				}
			});
		})

		$(".activateSpecifiedInvoicing").change(function(){
			var periodUnit = $(this).val();
			var resourceId = $(this).data("resource-id");
			var action = $(".resourceList").data("action2");

			fw_loading_start();
			$.ajax({
				type: 'POST',
				url: action,
				cache: false,
				data: 'output_form_submit=1&fwajax=1&fw_nocss=1&updateactivateSpecifiedInvoicing=1&resourceId=' + resourceId+'&activateSpecifiedInvoicing='+periodUnit,
				success: function(result){
					fw_loading_end();
					if(parseInt(result.html) > 0){
						ajaxCall('add_subscription_type', {}, function(obj) {
				            $('#popupeditboxcontent').html('');
				            $('#popupeditboxcontent').html(obj.html);
				            out_popup = $('#popupeditbox').bPopup(out_popup_options);
				            $("#popupeditbox:not(.opened)").remove();
				        });
					} else {
						$(".popupform .errorMessage").html("<?php echo $formText_ErrorSavingResource_output;?>").show();
					}
				}
			});
		})

		$(".defaultNameInInvoiceLine").change(function(){
			var periodUnit = $(this).val();
			var resourceId = $(this).data("resource-id");
			var action = $(".resourceList").data("action2");

			fw_loading_start();
			$.ajax({
				type: 'POST',
				url: action,
				cache: false,
				data: 'output_form_submit=1&fwajax=1&fw_nocss=1&updatedefaultNameInInvoiceLine=1&resourceId=' + resourceId+'&defaultNameInInvoiceLine='+periodUnit,
				success: function(result){
					fw_loading_end();
					if(parseInt(result.html) > 0){
						ajaxCall('add_subscription_type', {}, function(obj) {
				            $('#popupeditboxcontent').html('');
				            $('#popupeditboxcontent').html(obj.html);
				            out_popup = $('#popupeditbox').bPopup(out_popup_options);
				            $("#popupeditbox:not(.opened)").remove();
				        });
					} else {
						$(".popupform .errorMessage").html("<?php echo $formText_ErrorSavingResource_output;?>").show();
					}
				}
			});
		})
        $(".subscriptionCategory").change(function(){
			var periodUnit = $(this).val();
			var resourceId = $(this).data("resource-id");
			var action = $(".resourceList").data("action2");

			fw_loading_start();
			$.ajax({
				type: 'POST',
				url: action,
				cache: false,
				data: 'output_form_submit=1&fwajax=1&fw_nocss=1&updatesubscriptionCategory=1&resourceId=' + resourceId+'&subscriptionCategory='+periodUnit,
				success: function(result){
					fw_loading_end();
					if(parseInt(result.html) > 0){
						ajaxCall('add_subscription_type', {}, function(obj) {
				            $('#popupeditboxcontent').html('');
				            $('#popupeditboxcontent').html(obj.html);
				            out_popup = $('#popupeditbox').bPopup(out_popup_options);
				            $("#popupeditbox:not(.opened)").remove();
				        });
					} else {
						$(".popupform .errorMessage").html("<?php echo $formText_ErrorSavingResource_output;?>").show();
					}
				}
			});
		})
        $(".activate_checking_and_doing").change(function(){
			var periodUnit = $(this).val();
			var resourceId = $(this).data("resource-id");
			var action = $(".resourceList").data("action2");

			fw_loading_start();
			$.ajax({
				type: 'POST',
				url: action,
				cache: false,
				data: 'output_form_submit=1&fwajax=1&fw_nocss=1&update_activate_checking_and_doing=1&resourceId=' + resourceId+'&activate_checking_and_doing='+periodUnit,
				success: function(result){
					fw_loading_end();
					if(parseInt(result.html) > 0){
						ajaxCall('add_subscription_type', {}, function(obj) {
				            $('#popupeditboxcontent').html('');
				            $('#popupeditboxcontent').html(obj.html);
				            out_popup = $('#popupeditbox').bPopup(out_popup_options);
				            $("#popupeditbox:not(.opened)").remove();
				        });
					} else {
						$(".popupform .errorMessage").html("<?php echo $formText_ErrorSavingResource_output;?>").show();
					}
				}
			});
		})

        $(".activate_own_tab_in_batchrenewal").change(function(){
			var periodUnit = $(this).val();
			var resourceId = $(this).data("resource-id");
			var action = $(".resourceList").data("action2");

			fw_loading_start();
			$.ajax({
				type: 'POST',
				url: action,
				cache: false,
				data: 'output_form_submit=1&fwajax=1&fw_nocss=1&update_activate_own_tab_in_batchrenewal=1&resourceId=' + resourceId+'&activate_own_tab_in_batchrenewal='+periodUnit,
				success: function(result){
					fw_loading_end();
					if(parseInt(result.html) > 0){
						ajaxCall('add_subscription_type', {}, function(obj) {
				            $('#popupeditboxcontent').html('');
				            $('#popupeditboxcontent').html(obj.html);
				            out_popup = $('#popupeditbox').bPopup(out_popup_options);
				            $("#popupeditbox:not(.opened)").remove();
				        });
					} else {
						$(".popupform .errorMessage").html("<?php echo $formText_ErrorSavingResource_output;?>").show();
					}
				}
			});
		})

        $(".hide_subscriptionlines").change(function(){
			var periodUnit = $(this).val();
			var resourceId = $(this).data("resource-id");
			var action = $(".resourceList").data("action2");

			fw_loading_start();
			$.ajax({
				type: 'POST',
				url: action,
				cache: false,
				data: 'output_form_submit=1&fwajax=1&fw_nocss=1&update_hide_subscriptionlines=1&resourceId=' + resourceId+'&hide_subscriptionlines='+periodUnit,
				success: function(result){
					fw_loading_end();
					if(parseInt(result.html) > 0){
						ajaxCall('add_subscription_type', {}, function(obj) {
				            $('#popupeditboxcontent').html('');
				            $('#popupeditboxcontent').html(obj.html);
				            out_popup = $('#popupeditbox').bPopup(out_popup_options);
				            $("#popupeditbox:not(.opened)").remove();
				        });
					} else {
						$(".popupform .errorMessage").html("<?php echo $formText_ErrorSavingResource_output;?>").show();
					}
				}
			});
		})
        $(".script_for_generating_order").change(function(){
			var periodUnit = $(this).val();
			var resourceId = $(this).data("resource-id");
			var action = $(".resourceList").data("action2");

			fw_loading_start();
			$.ajax({
				type: 'POST',
				url: action,
				cache: false,
				data: 'output_form_submit=1&fwajax=1&fw_nocss=1&update_script_for_renewal=1&resourceId=' + resourceId+'&script_for_renewal='+periodUnit,
				success: function(result){
					fw_loading_end();
					if(parseInt(result.html) > 0){
						ajaxCall('add_subscription_type', {}, function(obj) {
				            $('#popupeditboxcontent').html('');
				            $('#popupeditboxcontent').html(obj.html);
				            out_popup = $('#popupeditbox').bPopup(out_popup_options);
				            $("#popupeditbox:not(.opened)").remove();
				        });
					} else {
						$(".popupform .errorMessage").html("<?php echo $formText_ErrorSavingResource_output;?>").show();
					}
				}
			});
		})
        $(".activateSubmemberWithoutInvoicing").change(function(){
			var periodUnit = $(this).val();
			var resourceId = $(this).data("resource-id");
			var action = $(".resourceList").data("action2");

			fw_loading_start();
			$.ajax({
				type: 'POST',
				url: action,
				cache: false,
				data: 'output_form_submit=1&fwajax=1&fw_nocss=1&update_activateSubmemberWithoutInvoicing=1&resourceId=' + resourceId+'&activateSubmemberWithoutInvoicing='+periodUnit,
				success: function(result){
					fw_loading_end();
					if(parseInt(result.html) > 0){
						ajaxCall('add_subscription_type', {}, function(obj) {
				            $('#popupeditboxcontent').html('');
				            $('#popupeditboxcontent').html(obj.html);
				            out_popup = $('#popupeditbox').bPopup(out_popup_options);
				            $("#popupeditbox:not(.opened)").remove();
				        });
					} else {
						$(".popupform .errorMessage").html("<?php echo $formText_ErrorSavingResource_output;?>").show();
					}
				}
			});
		})
        $(".useMainContactAsContactperson").change(function(){
			var periodUnit = $(this).val();
			var resourceId = $(this).data("resource-id");
			var action = $(".resourceList").data("action2");

			fw_loading_start();
			$.ajax({
				type: 'POST',
				url: action,
				cache: false,
				data: 'output_form_submit=1&fwajax=1&fw_nocss=1&update_useMainContactAsContactperson=1&resourceId=' + resourceId+'&useMainContactAsContactperson='+periodUnit,
				success: function(result){
					fw_loading_end();
					if(parseInt(result.html) > 0){
						ajaxCall('add_subscription_type', {}, function(obj) {
				            $('#popupeditboxcontent').html('');
				            $('#popupeditboxcontent').html(obj.html);
				            out_popup = $('#popupeditbox').bPopup(out_popup_options);
				            $("#popupeditbox:not(.opened)").remove();
				        });
					} else {
						$(".popupform .errorMessage").html("<?php echo $formText_ErrorSavingResource_output;?>").show();
					}
				}
			});
		})
        $(".hideContactperson").change(function(){
			var periodUnit = $(this).val();
			var resourceId = $(this).data("resource-id");
			var action = $(".resourceList").data("action2");

			fw_loading_start();
			$.ajax({
				type: 'POST',
				url: action,
				cache: false,
				data: 'output_form_submit=1&fwajax=1&fw_nocss=1&update_hideContactperson=1&resourceId=' + resourceId+'&hideContactperson='+periodUnit,
				success: function(result){
					fw_loading_end();
					if(parseInt(result.html) > 0){
						ajaxCall('add_subscription_type', {}, function(obj) {
				            $('#popupeditboxcontent').html('');
				            $('#popupeditboxcontent').html(obj.html);
				            out_popup = $('#popupeditbox').bPopup(out_popup_options);
				            $("#popupeditbox:not(.opened)").remove();
				        });
					} else {
						$(".popupform .errorMessage").html("<?php echo $formText_ErrorSavingResource_output;?>").show();
					}
				}
			});
		})
        $(".activatePricelist2").change(function(){
			var periodUnit = $(this).val();
			var resourceId = $(this).data("resource-id");
			var action = $(".resourceList").data("action2");

			fw_loading_start();
			$.ajax({
				type: 'POST',
				url: action,
				cache: false,
				data: 'output_form_submit=1&fwajax=1&fw_nocss=1&update_activatePricelist2=1&resourceId=' + resourceId+'&activatePricelist2='+periodUnit,
				success: function(result){
					fw_loading_end();
					if(parseInt(result.html) > 0){
						ajaxCall('add_subscription_type', {}, function(obj) {
				            $('#popupeditboxcontent').html('');
				            $('#popupeditboxcontent').html(obj.html);
				            out_popup = $('#popupeditbox').bPopup(out_popup_options);
				            $("#popupeditbox:not(.opened)").remove();
				        });
					} else {
						$(".popupform .errorMessage").html("<?php echo $formText_ErrorSavingResource_output;?>").show();
					}
				}
			});
		})
        $(".activateSubscriptionInvoiceToOtherCustomer").change(function(){
			var periodUnit = $(this).val();
			var resourceId = $(this).data("resource-id");
			var action = $(".resourceList").data("action2");

			fw_loading_start();
			$.ajax({
				type: 'POST',
				url: action,
				cache: false,
				data: 'output_form_submit=1&fwajax=1&fw_nocss=1&update_activateSubscriptionInvoiceToOtherCustomer=1&resourceId=' + resourceId+'&activateSubscriptionInvoiceToOtherCustomer='+periodUnit,
				success: function(result){
					fw_loading_end();
					if(parseInt(result.html) > 0){
						ajaxCall('add_subscription_type', {}, function(obj) {
				            $('#popupeditboxcontent').html('');
				            $('#popupeditboxcontent').html(obj.html);
				            out_popup = $('#popupeditbox').bPopup(out_popup_options);
				            $("#popupeditbox:not(.opened)").remove();
				        });
					} else {
						$(".popupform .errorMessage").html("<?php echo $formText_ErrorSavingResource_output;?>").show();
					}
				}
			});
		})
        $(".activateSubtypeFilterInEmailMarketingModule").change(function(){
			var periodUnit = $(this).val();
			var resourceId = $(this).data("resource-id");
			var action = $(".resourceList").data("action2");

			fw_loading_start();
			$.ajax({
				type: 'POST',
				url: action,
				cache: false,
				data: 'output_form_submit=1&fwajax=1&fw_nocss=1&update_activateSubtypeFilterInEmailMarketingModule=1&resourceId=' + resourceId+'&activateSubtypeFilterInEmailMarketingModule='+periodUnit,
				success: function(result){
					fw_loading_end();
					if(parseInt(result.html) > 0){
						ajaxCall('add_subscription_type', {}, function(obj) {
				            $('#popupeditboxcontent').html('');
				            $('#popupeditboxcontent').html(obj.html);
				            out_popup = $('#popupeditbox').bPopup(out_popup_options);
				            $("#popupeditbox:not(.opened)").remove();
				        });
					} else {
						$(".popupform .errorMessage").html("<?php echo $formText_ErrorSavingResource_output;?>").show();
					}
				}
			});
		})
        $(".activatePersonalSubscriptionConnection").change(function(){
			var periodUnit = $(this).val();
			var resourceId = $(this).data("resource-id");
			var action = $(".resourceList").data("action2");

			fw_loading_start();
			$.ajax({
				type: 'POST',
				url: action,
				cache: false,
				data: 'output_form_submit=1&fwajax=1&fw_nocss=1&update_activatePersonalSubscriptionConnection=1&resourceId=' + resourceId+'&activatePersonalSubscriptionConnection='+periodUnit,
				success: function(result){
					fw_loading_end();
					if(parseInt(result.html) > 0){
						ajaxCall('add_subscription_type', {}, function(obj) {
				            $('#popupeditboxcontent').html('');
				            $('#popupeditboxcontent').html(obj.html);
				            out_popup = $('#popupeditbox').bPopup(out_popup_options);
				            $("#popupeditbox:not(.opened)").remove();
				        });
					} else {
						$(".popupform .errorMessage").html("<?php echo $formText_ErrorSavingResource_output;?>").show();
					}
				}
			});
		})
	}
});
</script>
<style>
.subtypeWrapper {
    margin-top: 10px;
    border-top: 1px solid #cecece;
}
.columnInputWrapper {
	margin: 5px 0px;
}
.columnWrapper label {
	margin-right: 10px;
	margin-bottom: 0;
}
.addWorkLeader {
	cursor: pointer;
}
.workleaderBlock {
	margin-left: 10px;
}
.popupform, .popupeditform {
	width:100%;
	margin:0 auto;
	position:relative;
}
.popupform .addNew {
	margin-left: 20px;
}
label.error { display: none !important; }
input.error { border-color:#c11; }
#popup-validate-message, .error-msg { font-weight:bold; color:#c11; padding-bottom:10px; }
/* css for timepicker */
.ui-timepicker-div .ui-widget-header { margin-bottom: 8px; }
.ui-timepicker-div dl { text-align: left; }
.ui-timepicker-div dl dt { height: 25px; margin-bottom: -25px; }
.ui-timepicker-div dl dd { margin: 0 10px 10px 65px; }
.ui-timepicker-div td { font-size: 90%; }
.ui-tpicker-grid-label { background: none; border: none; margin: 0; padding: 0; }
.clear {
	clear:both;
}
.inner {
	padding:10px;
}
.pplineV {
	position:absolute;
	top:0;bottom:0;left:70%;
	border-left:1px solid #e8e8e8;
}
.popupform input.popupforminput, .popupform textarea.popupforminput, .col-md-8z input {
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
.popupformname {
	font-size:12px;
	font-weight:bold;
	padding:5px 0px;
}
.popupforminput.botspace {
	margin-bottom:10px;
}
textarea {
	min-height:50px;
	max-width:100%;
	min-width:100%;
	width:100%;
}
.popupformname {
	font-weight: 700;
	font-size: 13px;
}
.popupformbtn {
	text-align:right;
	margin:10px;
}
.popupformbtn input {
	border-radius: 4px;
	border:0px none;
	background-color:#0393ff;
	font-size:13px;
	line-height:0px;
	padding: 20px 35px;
	font-weight:700;
	color:#FFF;
}
.error {
	border: 1px solid #c11;
}
.popupform .lineTitle {
	font-weight:700;
}
.popupform .line .lineTitle {
	width:30%;
	float:left;
	font-weight:700;
	padding:5px 0;
}
.popupform .line .lineInput {
	width:70%;
	float:left;
}
.addNew {
	cursor: pointer;
}
.addNew .plusTextBox {
	float: none;
}
.subDepartments {
	margin-left: 30px;
}
.actions {
	margin-left: 0;
	padding-left: 0;
}
.addNewSubType .plusTextBox {
    float: none;
    margin-top: 10px;
    margin-left: 15px;
    cursor: pointer;
}
.addNewSelfdefined .plusTextBox {
    float: none;
    margin-top: 10px;
    margin-left: 15px;
    cursor: pointer;
}
</style>
