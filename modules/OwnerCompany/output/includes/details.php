<?php
// List btn
require_once __DIR__ . '/list_btn.php';
if(isset($_GET['cid'])){ $cid = $o_main->db->escape_like_str($_GET['cid']); } else { $cid = nuul; }
$s_page_reload_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$v_data->id;

$sql = "SELECT oc.* FROM ownercompany oc WHERE oc.id = ?";
$result = $o_main->db->query($sql, array($cid));
if($result && $result->num_rows() > 0) $ownerCompany = $result->row();

$s_sql = "SELECT * FROM projectforaccounting WHERE projectnumber = ?";
$o_query = $o_main->db->query($s_sql, array($ownerCompany->accountingproject_code));
$project = $o_query ? $o_query->row_array() : array();

$s_sql = "SELECT * FROM departmentforaccounting WHERE departmentnumber = ?";
$o_query = $o_main->db->query($s_sql, array($ownerCompany->accountingdepartment_code));
$department = $o_query ? $o_query->row_array() : array();

$o_query = $o_main->db->get('ownercompany_basisconfig');
$ownercompany_basisconfig = $o_query ? $o_query->row_array() : array();

$o_query = $o_main->db->get('ownercompany_accountconfig');
$ownercompany_accountconfig = $o_query ? $o_query->row_array() : array();

if(intval($ownercompany_accountconfig['activate_export']) > 0)
{
	$ownercompany_accountconfig['activate_export'] = intval($ownercompany_accountconfig['activate_export']) - 1;
} else {
	$ownercompany_accountconfig['activate_export'] = intval($ownercompany_basisconfig['activate_export']);
}
if(intval($ownercompany_accountconfig['activate_customer_portal']) > 0)
{
	$ownercompany_accountconfig['activate_customer_portal'] = intval($ownercompany_accountconfig['activate_customer_portal']) - 1;
} else {
	$ownercompany_accountconfig['activate_customer_portal'] = intval($ownercompany_basisconfig['activate_customer_portal']);
}

if($ownercompany_accountconfig['activate_customer_portal'])
{
	$s_sql = "SELECT * FROM ownercompany_contacts WHERE ownercompany_id = '".$o_main->db->escape_str($ownerCompany->id)."' ORDER BY sortnr ASC";
	$o_query = $o_main->db->query($s_sql);
	$v_contact_persons = $o_query ? $o_query->result_array() : array();

	$s_sql = "SELECT * FROM ownercompany_qualityicons WHERE ownercompany_id = '".$o_main->db->escape_str($ownerCompany->id)."' ORDER BY id ASC";
	$o_query = $o_main->db->query($s_sql);
	$v_quality_icons = $o_query ? $o_query->result_array() : array();
}
$default_label = $formText_Default_output;
if($ownercompany_accountconfig['default_set_name'] != "") {
    $default_label = $ownercompany_accountconfig['default_set_name'];
}
?>
<div id="p_container" class="p_container">
	<div class="p_containerInner">

		<div class="p_content">
			<div class="p_pageContent">
				<div class="p_pageDetails">
					<div class="p_pageDetailsTitle"><?php echo $formText_CompanyDetails_Output;?></div>
					<div class="p_contentBlock no-vertical-padding">
                        <div class="customerDetails">
                            <table width="100%" border="0" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td class="txt-label"><?php echo $formText_CompanyName_output;?></td>
                                    <td class="txt-value"><?php echo $ownerCompany->companyname;?></td>
                                </tr>
								<?php /*
                                <tr>
                                    <td class="txt-label"><?php echo $formText_Logo_output;?></td>
                                    <td class="txt-value">
                                        <div>
                                            <?php foreach (json_decode($ownerCompany->logo) as $image):
                                                $officeSpaceImage = $image[1][3];
                                                if($officeSpaceImage == "") {
                                                    $officeSpaceImage = $image[1][0];
                                                }
                                            ?>
                                                <div class="office-image">
                                                    <div class="office-image-img">
                                                        <img style="width:200px;" src="../<?php echo $officeSpaceImage; ?>" />
                                                    </div>
                                                    <div class="office-image-button">
                                                        <a href="#" class="deleteLogo" data-image-upload-id="<?php echo $image[4]; ?>"><?php echo $formText_Delete_output; ?></a>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                            <a href="#" class="addLogoBtn" <?php if(count(json_decode($ownerCompany->logo)) >0){ ?> style="display: none;" <?php } ?>>
                                        <?php echo $formText_AddImage_output; ?></a>
                                    </td>
                                </tr>*/?>
                                <tr>
                                    <td class="txt-label"><?php echo $formText_CompanyPostal_output;?></td>
                                    <td class="txt-value"><?php echo $ownerCompany->companypostalbox;?></td>
                                </tr>
                                <tr>
                                    <td class="txt-label"><?php echo $formText_CompanyZipCode_output;?></td>
                                    <td class="txt-value"><?php echo $ownerCompany->companyzipcode;?></td>
                                </tr>
                                <tr>
                                    <td class="txt-label"><?php echo $formText_CompanyPostalPlace_output;?></td>
                                    <td class="txt-value"><?php echo $ownerCompany->companypostalplace;?></td>
                                </tr>
                                <tr>
                                    <td class="txt-label"><?php echo $formText_CompanyCountry_output;?></td>
                                    <td class="txt-value"><?php echo $ownerCompany->companyCountry;?></td>
                                </tr>
                                <tr>
                                    <td class="txt-label"><?php echo $formText_CompanyPhone_output;?></td>
                                    <td class="txt-value"><?php echo $ownerCompany->companyphone;?></td>
                                </tr>
                                <tr>
                                    <td class="txt-label"><?php echo $formText_CompanyEmail_output;?></td>
                                    <td class="txt-value"><?php echo $ownerCompany->companyEmail;?></td>
                                </tr>
                                <tr>
                                    <td class="txt-label"><?php echo $formText_CompanyOrgNr_output;?></td>
                                    <td class="txt-value"><?php echo $ownerCompany->companyorgnr;?></td>
                                </tr>
                                <tr>
                                    <td class="txt-label"><?php echo $formText_ExtraTextAfterCompanyOrgNr_output;?></td>
                                    <td class="txt-value"><?php echo $ownerCompany->extra_text_after_company_org_number;?></td>
                                </tr>
								<tr>
									<td class="txt-label"><?php echo $formText_Project_output;?></td>
									<td class="txt-value">
										<?php echo $project['projectnumber']; ?>
										<?php echo $project['name'];?>
									</td>
								</tr>
                                <tr>
                                    <td class="txt-label"><?php echo $formText_Department_output;?></td>
                                    <td class="txt-value">
                                        <?php echo $department['departmentnumber']; ?>
                                        <?php echo $department['name'];?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="txt-label"><?php echo $formText_ClientNumber_output;?></td>
                                    <td class="txt-value"><?php echo $ownerCompany->clientNumberFactoring;?></td>
                                </tr>
								<?php if($ownercompany_accountconfig['activate_division']) { ?>
	                                <tr>
	                                    <td class="txt-label"><?php echo $formText_Division_output;?></td>
	                                    <td class="txt-value"><?php echo $ownerCompany->division;?></td>
	                                </tr>
								<?php } ?>
                                <tr>
                                    <td class="txt-label"></td>
                                    <td class="txt-value"></td>
                                    <td class="btn-edit" colspan="2"><?php if($moduleAccesslevel > 10) { ?><button class="output-btn small output-edit-detail editBtnBlank" data-customer-id="<?php echo $cid; ?>"><?php echo $formText_Edit_Output;?></button><?php } ?></td>
                                </tr>
                            </table>
                        </div>
					</div>
					<?php
					if($variables->developeraccess >= 5){
						?>
						<div class="p_pageDetailsTitle"><?php echo $formText_AdditionalLogos_Output;?></div>
						<div class="p_contentBlock no-vertical-padding">
	                        <div class="customerDetails">
	                            <table width="100%" border="0" cellpadding="0" cellspacing="0">
									<tr>
	                                    <td class="txt-label"><?php echo $formText_Logos_output;?></td>
	                                    <td class="txt-value">
	                                        <div>
	                                            <?php

												$s_sql = "SELECT * FROM ownercompany_logos WHERE ownercompanyId = ?";
												$o_query = $o_main->db->query($s_sql, array($ownerCompany->id));
												$additionalLogos = $o_query ? $o_query->result_array() : array();
												foreach($additionalLogos as $additionalLogo){
													$images = json_decode($additionalLogo['logo']);
													$officeSpaceImage = $images[0][1][3];
													if($officeSpaceImage == "") {
														$officeSpaceImage = $images[0][1][0];
													}
													?>
													<div class="office-image">
														<div class="office-image-img">
															<img style="width:200px;" src="../<?php echo $officeSpaceImage; ?>" />
														</div>
														<div class="office-image-img">
															<div><label><?php echo $formText_LogoWidth_output;?></label>: <?php echo $additionalLogo['logo_width'];?></div>
															<div><label><?php echo $formText_LogoPositionX_output;?></label>: <?php echo $additionalLogo['logo_pos_x'];?></div>
															<div><label><?php echo $formText_LogoPositionY_output;?></label>: <?php echo $additionalLogo['logo_pos_y'];?></div>
														</div>
														<div class="office-image-button">
															<a href="#" class="editLogo" data-logo-id="<?php echo $additionalLogo['id'];?>"><?php echo $formText_Edit_output; ?></a>
															<a href="#" class="deleteLogos" data-logo-id="<?php echo $additionalLogo['id'];?>" data-image-upload-id="<?php echo $image[4]; ?>"><?php echo $formText_Delete_output; ?></a>
														</div>
													</div>
												<?php } ?>
	                                        </div>
	                                        <a href="#" class="addLogosBtn"><?php echo $formText_AddImage_output; ?></a>
	                                    </td>
	                                </tr>
								</table>
							</div>
						</div>
					<?php } ?>
                    <div class="p_pageDetailsTitle"><?php echo $formText_Kid_Output;?></div>
                    <div class="p_contentBlock no-vertical-padding">
                        <div class="customerDetails">
                            <table width="100%" border="0" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td class="txt-label"><?php echo $formText_KidOnInvoice_output;?></td>
                                    <td class="txt-value"><?php echo $ownerCompany->kidOnInvoice;?></td>
                                </tr>
                                <tr>
                                    <td class="txt-label"><?php echo $formText_KidCustNumAmount_output;?></td>
                                    <td class="txt-value"><?php echo $ownerCompany->kidCustNumAmount;?></td>
                                </tr>
                                <tr>
                                    <td class="txt-label"><?php echo $formText_KidPaymentType_output;?></td>
                                    <td class="txt-value"><?php echo $ownerCompany->kidPaymentType;?></td>
                                </tr>
                                <tr>
                                    <td class="txt-label"><?php echo $formText_KidInvNumAmount_output;?></td>
                                    <td class="txt-value"><?php echo $ownerCompany->kidInvNumAmount;?></td>
                                </tr>
                                <tr>
                                    <td class="txt-label"><?php echo $formText_KidOwnercompanyId_output;?></td>
                                    <td class="txt-value"><?php echo $ownerCompany->kidOwnercompanyId;?></td>
                                </tr>
								<tr>
                                    <td class="txt-label"><?php echo $formText_AvtalegiroDataSender_output;?></td>
                                    <td class="txt-value"><?php echo $ownerCompany->avtalegiro_data_sender;?></td>
                                </tr>
								<tr>
                                    <td class="txt-label"><?php echo $formText_AvtalegiroDrawingReferenceText_output;?></td>
                                    <td class="txt-value"><?php echo $ownerCompany->avtalegiro_drawing_reference_text;?></td>
                                </tr>
                                <tr>
                                    <td class="txt-label"></td>
                                    <td class="txt-value"></td>
                                    <td class="btn-edit" colspan="2"><?php if($moduleAccesslevel > 10) { ?><button class="output-btn small output-edit-kid-detail editBtnBlank" data-customer-id="<?php echo $cid; ?>"><?php echo $formText_Edit_Output;?></button><?php } ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <div class="p_pageDetailsTitle"><?php echo $formText_Invoice_Output;?></div>
                    <div class="p_contentBlock no-vertical-padding">
                        <div class="customerDetails">
                            <table width="100%" border="0" cellpadding="0" cellspacing="0">
								<tr>
                                    <td class="txt-label"><?php echo $formText_InvoiceTemplate_output;?></td>
                                    <td class="txt-value">
										<?php if($ownerCompany->invoice_template == 1){ echo $formText_Alternative_output; } else { echo $formText_Default_output;}?>
									</td>
                                </tr>
								<?php
								if($ownerCompany->invoice_template == 1){
									?>
									<tr>
	                                    <td class="txt-label"><?php echo $formText_InvoiceFooterLogo_output;?></td>
	                                    <td class="txt-value">
	                                        <div>
	                                            <?php foreach (json_decode($ownerCompany->invoice_footer_logos) as $image):
	                                                $officeSpaceImage = $image[1][3];
	                                                if($officeSpaceImage == "") {
	                                                    $officeSpaceImage = $image[1][0];
	                                                }
	                                            ?>
	                                                <div class="office-image">
	                                                    <div class="office-image-img">
	                                                        <img style="width:200px;" src="../<?php echo $officeSpaceImage; ?>" />
	                                                    </div>
	                                                    <div class="office-image-button">
	                                                        <a href="#" class="deleteInvoiceLogo" data-image-upload-id="<?php echo $image[4]; ?>"><?php echo $formText_Delete_output; ?></a>
	                                                    </div>
	                                                </div>
	                                            <?php endforeach; ?>
	                                        </div>
											<a href="#" class="addInvoiceFooterLogoBtn">
											<?php echo $formText_AddImage_output; ?></a>
	                                    </td>
	                                </tr>
									<?php
								}
								?>
                                <tr>
                                    <td class="txt-label"><?php echo $formText_InvoiceFromEmail_output;?></td>
                                    <td class="txt-value"><?php echo $ownerCompany->invoiceFromEmail;?></td>
                                </tr>
                                <tr>
                                    <td class="txt-label"><?php echo $formText_InvoiceSubjectEmail_output;?></td>
                                    <td class="txt-value"><?php echo $ownerCompany->invoiceSubjectEmail;?></td>
                                </tr>
                                <tr>
                                    <td class="txt-label" valign="top"><?php echo $formText_InvoiceTextEmail_output;?></td>
                                    <td class="txt-value" valign="top"><?php echo nl2br($ownerCompany->invoiceTextEmail);?></td>
                                </tr>
                                <tr>
                                    <td class="txt-label"><?php echo $formText_InvoiceLogo_output;?></td>
                                    <td class="txt-value">
                                        <div>
                                            <?php foreach (json_decode($ownerCompany->invoicelogo) as $image):
                                                $officeSpaceImage = $image[1][3];
                                                if($officeSpaceImage == "") {
                                                    $officeSpaceImage = $image[1][0];
                                                }
                                            ?>
                                                <div class="office-image">
                                                    <div class="office-image-img">
                                                        <img style="width:200px;" src="../<?php echo $officeSpaceImage; ?>" />
                                                    </div>
                                                    <div class="office-image-button">
                                                        <a href="#" class="deleteInvoiceLogo" data-image-upload-id="<?php echo $image[4]; ?>"><?php echo $formText_Delete_output; ?></a>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                            <a href="#" class="addInvoiceLogoBtn" <?php if(count(json_decode($ownerCompany->invoicelogo)) >0){ ?> style="display: none;" <?php } ?>>
                                        <?php echo $formText_AddImage_output; ?></a>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="txt-label"><?php echo $formText_InvoiceLogoWidth_output;?></td>
                                    <td class="txt-value"><?php echo $ownerCompany->invoicelogoWidth;?></td>
                                </tr>
                                <tr>
                                    <td class="txt-label"><?php echo $formText_InvoiceLogoPositionX_output;?></td>
                                    <td class="txt-value"><?php echo $ownerCompany->invoicelogoPositionX;?></td>
                                </tr>
                                <tr>
                                    <td class="txt-label"><?php echo $formText_InvoiceLogoPositionY_output;?></td>
                                    <td class="txt-value"><?php echo $ownerCompany->invoicelogoPositionY;?></td>
                                </tr>
                                <tr>
                                    <td class="txt-label"><?php echo $formText_InvoiceBottomText_output;?></td>
                                    <td class="txt-value"><?php echo $ownerCompany->invoicebottomtext;?></td>
                                </tr>
                                <tr>
                                    <td class="txt-label"><?php echo $formText_CompanyAccount_output;?></td>
                                    <td class="txt-value"><?php echo $ownerCompany->companyaccount;?></td>
                                </tr>
                                <tr>
                                    <td class="txt-label"><?php echo $formText_CompanyIban_output;?></td>
                                    <td class="txt-value"><?php echo $ownerCompany->companyiban;?></td>
                                </tr>
                                <tr>
                                    <td class="txt-label"><?php echo $formText_CompanySwift_output;?></td>
                                    <td class="txt-value"><?php echo $ownerCompany->companyswift;?></td>
                                </tr>
                                <tr>
                                    <td class="txt-label"><?php echo $formText_CompanyBankAccount2_output;?></td>
                                    <td class="txt-value"><?php echo $ownerCompany->companyBankAccount2;?></td>
                                </tr>
                                <tr>
                                    <td class="txt-label"><?php echo $formText_CompanyIban2_output;?></td>
                                    <td class="txt-value"><?php echo $ownerCompany->companyiban2;?></td>
                                </tr>
                                <tr>
                                    <td class="txt-label"><?php echo $formText_CompanySwift2_output;?></td>
                                    <td class="txt-value"><?php echo $ownerCompany->companyswift2;?></td>
                                </tr>
                                <tr>
                                    <td class="txt-label"><?php echo $formText_CompanyBankAccount3_output;?></td>
                                    <td class="txt-value"><?php echo $ownerCompany->companyBankAccount3;?></td>
                                </tr>
                                <tr>
                                    <td class="txt-label"><?php echo $formText_CompanyIban3_output;?></td>
                                    <td class="txt-value"><?php echo $ownerCompany->companyiban3;?></td>
                                </tr>
                                <tr>
                                    <td class="txt-label"><?php echo $formText_CompanySwift3_output;?></td>
                                    <td class="txt-value"><?php echo $ownerCompany->companyswift3;?></td>
                                </tr>
                                <tr>
                                    <td class="txt-label"><?php echo $formText_EmailFromExtra1_output;?></td>
                                    <td class="txt-value"><?php echo $ownerCompany->emailFromExtra1;?></td>
                                </tr>
                                <tr>
                                    <td class="txt-label"><?php echo $formText_EmailFromExtra2_output;?></td>
                                    <td class="txt-value"><?php echo $ownerCompany->EmailFromExtra2;?></td>
                                </tr>
                                <tr>
                                    <td class="txt-label"><?php echo $formText_EmailFromExtra3_output;?></td>
                                    <td class="txt-value"><?php echo $ownerCompany->EmailFromExtra3;?></td>
                                </tr>

                                <tr>
                                    <td class="txt-label"></td>
                                    <td class="txt-value"></td>
                                    <td class="btn-edit" colspan="2"><?php if($moduleAccesslevel > 10) { ?><button class="output-btn small output-edit-invoice-detail editBtnBlank" data-customer-id="<?php echo $cid; ?>"><?php echo $formText_Edit_Output;?></button><?php } ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="p_pageDetailsTitle"><?php echo $formText_Accounting_Output;?></div>
                    <div class="p_contentBlock no-vertical-padding">
                        <div class="customerDetails">
                            <table width="100%" border="0" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td class="txt-label"><?php echo $formText_NumberDecimalsOnInvoice_output;?></td>
                                    <td class="txt-value"><?php echo $ownerCompany->numberDecimalsOnInvoice;?></td>
                                </tr>
                                <tr>
                                    <td class="txt-label"><?php echo $formText_AccountRoundingsOnInvoice_output;?></td>
                                    <td class="txt-value"><?php
                                        $o_query = $o_main->db->query("SELECT * FROM bookaccount WHERE id = ?", array($ownerCompany->accountRoundingsOnInvoice));
                                        $bookaccountItem = ($o_query ? $o_query->row_array() : array());
                                        echo $bookaccountItem['name'];?></td>
                                </tr>
                                <tr>
                                    <td class="txt-label"><?php echo $formText_AccountCustomerLedger_output;?></td>
                                    <td class="txt-value"><?php
                                        $o_query = $o_main->db->query("SELECT * FROM bookaccount WHERE id = ?", array($ownerCompany->accountCustomerLedger));
                                        $bookaccountItem = ($o_query ? $o_query->row_array() : array());
                                        echo $bookaccountItem['name'];
                                     ?></td>
                                </tr>
                                <tr>
                                    <td class="txt-label"><?php echo $formText_AllowMultiCurrencies_output;?></td>
                                    <td class="txt-value"><?php echo $ownerCompany->allowMultiCurrencies;?></td>
                                </tr>
                                <tr>
                                    <td class="txt-label"><?php echo $formText_CurrencyNameWhenOnlyOne_output;?></td>
                                    <td class="txt-value"><?php echo $ownerCompany->currencyNameWhenOnlyOne;?></td>
                                </tr>
                                <tr>
                                    <td class="txt-label"><?php echo $formText_CurrencyCodeWhenOnlyOne_output;?></td>
                                    <td class="txt-value"><?php echo $ownerCompany->currencyCodeWhenOnlyOne;?></td>
                                </tr>
                                <tr>
                                    <td class="txt-label"><?php echo $formText_SetNextInvoiceNumberAutomatic_output;?></td>
                                    <td class="txt-value"><?php if($ownerCompany->set_next_invoicenumber_automatic == 0) { echo $formText_Yes_Output; } else if($ownerCompany->set_next_invoicenumber_automatic == 1){ echo $formText_No_Output; };?></td>
                                </tr>
                                <tr>
                                    <td class="txt-label"><?php echo $formText_NextInvoiceNr_output;?></td>
                                    <td class="txt-value"><?php echo $ownerCompany->nextInvoiceNr;?></td>
                                </tr>
								<tr>
									<td class="txt-label"><?php echo $formText_ExternalOwnerCompanyCode_output;?></td>
									<td class="txt-value"><?php echo $ownerCompany->external_ownercompany_code;?></td>
								</tr>
                                <tr>
                                    <td class="txt-label"></td>
                                    <td class="txt-value"></td>
                                    <td class="btn-edit" colspan="2"><?php if($moduleAccesslevel > 10) { ?><button class="output-btn small output-edit-accounting-detail editBtnBlank" data-customer-id="<?php echo $cid; ?>"><?php echo $formText_Edit_Output;?></button><?php } ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
					<?php if (!$ownercompany_accountconfig['activate_global_external_company_id']): ?>
	                    <div class="p_pageDetailsTitle"><?php echo $formText_Customer_Output;?></div>
	                    <div class="p_contentBlock no-vertical-padding">
	                        <div class="customerDetails">
	                            <table width="100%" border="0" cellpadding="0" cellspacing="0">
	                                <tr>
										<?php
										$customerIdTypeNames = array(
											'-1' => $formText_FromExternalSystem_output,
											'0' => $formText_NotSet_output,
											'1' => $formText_Automaticly_output,
											'2' => $formText_Manually_output,
											'3' => $formText_AutomaticAndEditable_output											);
										?>
	                                    <td class="txt-label"><?php echo $formText_CustomerIdAutoOrManually_output;?></td>
	                                    <td class="txt-value"><?php echo $customerIdTypeNames[$ownerCompany->customerid_autoormanually]; ?></td>
	                                </tr>
	                                <?php if ($ownerCompany->customerid_autoormanually != -1) { ?>
	                                    <tr>
	                                        <td class="txt-label"><?php echo $formText_NextCustomerId_output;?></td>
	                                        <td class="txt-value"><?php echo $ownerCompany->nextCustomerId;?></td>
	                                    </tr>
									<?php } ?>
	                                <?php if ($ownerCompany->customerid_autoormanually == 1 || $ownerCompany->customerid_autoormanually == 3) { ?>
	                                    <tr>
	                                        <td class="txt-label"><?php echo $formText_UseIntegration_output;?></td>
	                                        <td class="txt-value"><?php if($ownerCompany->use_integration == "0" || $ownerCompany->use_integration == "" || $ownerCompany->use_integration == null){ echo $formText_NoIntegration_output; } else { echo $ownerCompany->use_integration; } ?></td>
	                                    </tr>
	                                <?php } ?>
	                                <tr>
	                                    <td class="txt-label"></td>
	                                    <td class="txt-value"></td>
	                                    <td class="btn-edit" colspan="2"><?php if($moduleAccesslevel > 10) { ?><button class="output-btn small output-edit-customerid-detail editBtnBlank" data-customer-id="<?php echo $cid; ?>"><?php echo $formText_Edit_Output;?></button><?php } ?></td>
	                                </tr>
	                            </table>
	                        </div>
	                    </div>
					<?php endif; ?>
                    <?php /* ?>
                    <div class="p_pageDetailsTitle"><?php echo $formText_Sale_Output;?></div>
                    <div class="p_contentBlock no-vertical-padding">
                        <div class="customerDetails">
                            <table width="100%" border="0" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td class="txt-label"><?php echo $formText_AdminFee_output;?></td>
                                    <td class="txt-value"><?php
                                    switch(intval($ownerCompany->addAdminFeeAutomatically)){
                                        case 0:
                                            echo $formText_Never_output;
                                        break;
                                        case 1:
                                            echo $formText_Always_output;
                                        break;
                                        case 2:
                                            echo $formText_OnlyIfPrint_output;
                                        break;
                                    }
                                    ?></td>
                                </tr>
                                <?php if($ownerCompany->addAdminFeeAutomatically > 0){
                                    $o_query = $o_main->db->query("SELECT * FROM article WHERE id = ?", array($ownerCompany->chooseArticleForAdminFee));
                                    $article = ($o_query ? $o_query->row_array() : array());
                                ?>
                                    <tr>
                                        <td class="txt-label"><?php echo $formText_ArticalForAdminFee_output;?></td>
                                        <td class="txt-value"><?php echo $article['name'];?></td>
                                    </tr>
                                <?php } ?>
                                <tr>
                                    <td class="txt-label"></td>
                                    <td class="txt-value"></td>
                                    <td class="btn-edit" colspan="2"><?php if($moduleAccesslevel > 10) { ?><button class="output-btn small output-edit-sale-detail editBtnBlank" data-customer-id="<?php echo $cid; ?>"><?php echo $formText_Edit_Output;?></button><?php } ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>*/?>

                    <?php if($ownercompany_accountconfig['activate_export']) { ?>
					<div class="p_pageDetailsTitle"><?php echo $formText_Export_Output;?></div>
                    <div class="p_contentBlock no-vertical-padding">
                        <div class="customerDetails">
                            <?php
                            $exportSendMethodTypes = array(
                                0 => $formText_None_output,
                                1 => $formText_Email_output,
                                2 => $formText_Ftp_output
                            );
                            ?>
                            <table width="100%" border="0" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td class="txt-label"><?php echo $formText_ExportScriptFolder_output;?></td>
                                    <td class="txt-value"><?php echo  basename($ownerCompany->exportScriptFolder);?></td>
                                </tr>
                                <tr>
                                    <td class="txt-label"><?php echo $formText_ActivatePeriodization_output;?></td>
                                    <td class="txt-value"><input id="periodizationCheck" type="checkbox" disabled readonly <?php if($ownerCompany->activatePeriodization) echo 'checked';?>/><label for="periodizationCheck"></label></td>
                                </tr>
                                <tr>
                                    <td class="txt-label"><?php echo $formText_BalancePeriodizationAccountcode_output;?></td>
                                    <td class="txt-value"><?php echo $ownerCompany->balancePeriodizationAccountcode;?></td>
                                </tr>
								<tr>
									<td class="txt-label"><?php echo $formText_ExportSendMethod_output;?></td>
									<td class="txt-value">
                                        <?php
                                        $sendMethod = $ownerCompany->exportSendMethod ? $ownerCompany->exportSendMethod : 0;
                                        echo $exportSendMethodTypes[$sendMethod];
                                        ?>
                                    </td>
								</tr>
								<tr>
									<td class="txt-label"><?php echo $formText_ExportFtpUsername_output;?></td>
									<td class="txt-value"><?php echo $ownerCompany->exportFtpUsername;?></td>
								</tr>
								<tr>
									<td class="txt-label"><?php echo $formText_ExportFtpPassword_output;?></td>
									<td class="txt-value">
                                        <?php for ($i = 0; $i < strlen($ownerCompany->exportFtpPassword); $i++) {
                                            echo '*';
                                        } ?>
                                    </td>
								</tr>
								<tr>
									<td class="txt-label"><?php echo $formText_ExportFtpHost_output;?></td>
									<td class="txt-value"><?php echo $ownerCompany->exportFtpHost;?></td>
								</tr>
								<tr>
									<td class="txt-label"><?php echo $formText_ExportFtpPort_output;?></td>
									<td class="txt-value"><?php echo $ownerCompany->exportFtpPort;?></td>
								</tr>
								<tr>
									<td class="txt-label"><?php echo $formText_ExportFtpPath_output;?></td>
									<td class="txt-value"><?php echo $ownerCompany->exportFtpPath;?></td>
								</tr>
								<tr>
									<td class="txt-label"><?php echo $formText_ExportFtpUseSsl_output;?></td>
									<td class="txt-value"><input id="exportFtpUseSsl" type="checkbox" disabled readonly <?php if($ownerCompany->exportFtpUseSSL) echo 'checked';?>/><label for="exportFtpUseSsl"></label></td>
								</tr>
								<tr>
									<td class="txt-label"><?php echo $formText_NextInvoiceExportVoucherNumber_output;?></td>
									<td class="txt-value"><?php echo $ownerCompany->nextInvoiceExportVoucherNumber;?></td>
								</tr>
                                <tr>
                                    <td class="txt-label"></td>
                                    <td class="txt-value"></td>
                                    <td class="btn-edit" colspan="2"><?php if($moduleAccesslevel > 10) { ?><button class="output-btn small output-edit-export-detail editBtnBlank" data-customer-id="<?php echo $cid; ?>"><?php echo $formText_Edit_Output;?></button><?php } ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="p_pageDetailsTitle"><?php echo $formText_Export2_Output;?></div>
                    <div class="p_contentBlock no-vertical-padding">
                        <div class="customerDetails">
                            <table width="100%" border="0" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td class="txt-label"><?php echo $formText_Export2ScriptFolder_output;?></td>
                                    <td class="txt-value"><?php echo  basename($ownerCompany->export2ScriptFolder);?></td>
                                </tr>
                                <tr>
                                    <td class="txt-label"><?php echo $formText_Export2SendMethod_output;?></td>
                                    <td class="txt-value">
                                        <?php
                                        $sendMethod = $ownerCompany->export2SendMethod ? $ownerCompany->export2SendMethod : 0;
                                        echo $exportSendMethodTypes[$sendMethod];
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="txt-label"><?php echo $formText_Export2FtpUsername_output;?></td>
                                    <td class="txt-value"><?php echo $ownerCompany->exportFtp2Username;?></td>
                                </tr>
                                <tr>
                                    <td class="txt-label"><?php echo $formText_Export2FtpPassword_output;?></td>
                                    <td class="txt-value">
                                        <?php for ($i = 0; $i < strlen($ownerCompany->export2FtpPassword); $i++) {
                                            echo '*';
                                        } ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="txt-label"><?php echo $formText_Export2FtpHost_output;?></td>
                                    <td class="txt-value"><?php echo $ownerCompany->export2FtpHost;?></td>
                                </tr>
                                <tr>
                                    <td class="txt-label"><?php echo $formText_Export2FtpPort_output;?></td>
                                    <td class="txt-value"><?php echo $ownerCompany->export2FtpPort;?></td>
                                </tr>
                                <tr>
                                    <td class="txt-label"><?php echo $formText_Export2FtpPath_output;?></td>
                                    <td class="txt-value"><?php echo $ownerCompany->export2FtpPath;?></td>
                                </tr>
                                <tr>
                                    <td class="txt-label"><?php echo $formText_Export2FtpUseSsl_output;?></td>
                                    <td class="txt-value"><input id="export2FtpUseSsl" type="checkbox" disabled readonly <?php if($ownerCompany->export2FtpUseSSL) echo 'checked';?>/><label for="export2FtpUseSsl"></label></td>
                                </tr>
                                <tr>
                                    <td class="txt-label"></td>
                                    <td class="txt-value"></td>
                                    <td class="btn-edit" colspan="2"><?php if($moduleAccesslevel > 10) { ?><button class="output-btn small output-edit-export2-detail editBtnBlank" data-customer-id="<?php echo $cid; ?>"><?php echo $formText_Edit_Output;?></button><?php } ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
					<?php } ?>

					<?php if($ownercompany_accountconfig['activate_customer_portal']) { ?>
					<div class="p_pageDetailsTitle"><?php echo $formText_CusotmerPortalContacts_Output;?> <span class="dragDropText">(<?php echo $formText_DragAndDropToChangeOrder_output;?>)</span> <span class="editContactperson fw_icon_title_color cursorButton" data-contactid="0">+ <?php echo $formText_Add_output;?></span></div>
                    <div class="p_contentBlock no-vertical-padding">
                        <div class="customerDetails">
							<div class="contactPersonList">
								<?php
								foreach($v_contact_persons as $contactPerson) {
									?>
									<div class="contactPersonBlock" id="sort_<?php echo $contactPerson['id']?>">
										<div class="contactPersonBlockRow">
											<?php echo "<b>".$contactPerson['first_name']." ".$contactPerson['middle_name']." ".$contactPerson['last_name']."</b> "; ?>
											<span class="glyphicon glyphicon-pencil cursorButton editContactperson" data-contactid="<?php echo $contactPerson['id'];?>"></span>
											<span class="glyphicon glyphicon-trash cursorButton deleteContactperson" data-contactid="<?php echo $contactPerson['id'];?>"></span>
										</div>
										<div class="contactPersonBlockRow titleRow"><?php echo $contactPerson['title']; ?></div>
										<div class="contactPersonBlockRow"><?php echo $contactPerson['mobile']; ?></div>
										<div class="contactPersonBlockRow"><?php echo $contactPerson['email']; ?></div>
									</div>
									<?php
								}
								?>
							</div>
                        </div>
                    </div>

					<div class="p_pageDetailsTitle"><?php echo $formText_CusotmerPortalQualityIcons_Output;?> <span class="editQualityIcon fw_icon_title_color cursorButton">+ <?php echo $formText_Add_output;?></span></div>
                    <div class="p_contentBlock no-vertical-padding">
                        <div class="customerDetails">
                            <div class="companyInfoBlockContent">
								<?php
								foreach($v_quality_icons as $icon) {
									$image = json_decode($icon['icon'], true);
								?>
									<?php if(count($image) > 0) {
										$imageLink = "../".$image[0][1][0];
										if(strpos($image[0][1][0], ".getynet.com") > 0) {
											$imageLink = $image[0][1][0];
										}
										?>
										<div class="icon_image">
											<img src="<?php echo $imageLink;?>" class=""/>
											<span class="glyphicon glyphicon-trash cursorButton deleteQualityIcon" data-iconid="<?php echo $icon['id'];?>"></span>
										</div>
									<?php } ?>
								<?php } ?>
								<div class="clear"></div>
							</div>
                        </div>
                    </div>
					<?php } ?>

					<?php if($ownercompany_accountconfig['activate_company_product_sets']) {?>
						<div class="p_pageDetailsTitle"><?php echo $formText_Sets_Output;?></div>
						<div class="p_contentBlock no-vertical-padding">
							<div class="customerDetails">
								<?php
								$s_sql = "SELECT * FROM company_product_set WHERE id = ?";
								$o_query = $o_main->db->query($s_sql, array($ownerCompany->company_product_set_id));
								$company_product_set = $o_query ? $o_query->row_array() : array();
								?>
	                            <table width="100%" border="0" cellpadding="0" cellspacing="0">
	                                <tr>
	                                    <td class="txt-label"><?php echo $formText_CompanyProductSet_output;?></td>
	                                    <td class="txt-value"><?php if($company_product_set) { echo $company_product_set['name']; } else { echo $default_label; }?></td>
	                                </tr>
	                                <tr>
	                                    <td class="txt-label"></td>
	                                    <td class="txt-value"></td>
	                                    <td class="btn-edit" colspan="2"><?php if($moduleAccesslevel > 10) { ?><button class="output-btn small output-edit-set-detail editBtnBlank" data-customer-id="<?php echo $cid; ?>"><?php echo $formText_Edit_Output;?></button><?php } ?></td>
	                                </tr>
	                            </table>
	                        </div>
						</div>
					<?php } ?>
                    <!-- <div class="p_pageDetailsTitle">
                        <?php echo $formText_Projects_Output;?>
                        <a href="#" class="output-edit-project" data-ownercompany-id="<?php echo $ownerCompany->id; ?>">
                            <span class="glyphicon glyphicon-plus"></span>
                            <?php echo $formText_AddProject_output; ?>
                        </a>
                    </div>
                    <div class="p_contentBlock no-vertical-padding">
                        <div class="projectDetails">
                            <br/>
                             <table class="table table-bordered table-striped">
                                <tr>
                                    <th><?php echo $formText_ProjectNumber_output; ?></th>
                                    <th><?php echo $formText_Name_output; ?></th>
                                    <th><?php echo $formText_Edit_output; ?></th>
                                </tr>
                                <?php
                                $result = $o_main->db->query("SELECT * FROM projectforaccounting WHERE ownercompany_id = ? AND (parentId = 0 OR parentId is null)", array($ownerCompany->id));
                                if($result && $result->num_rows() > 0)
                                foreach($result->result() AS $row){ ?>
                                    <tr>
                                        <td><?php echo $row->projectnumber; ?></td>
                                        <td><?php echo $row->name; ?></td>
                                        <td>
                                            <?php if($moduleAccesslevel > 10) { ?><button class="output-btn small output-edit-project editBtnBlank" data-ownercompany-id="<?php echo $ownerCompany->id; ?>" data-project-id="<?php echo $row->id; ?>"><?php echo $formText_Edit_Output;?></button><?php } ?>
                                            <?php if($moduleAccesslevel > 110) { ?><button class="output-btn small output-delete-project editBtnBlank" data-ownercompany-id="<?php echo $ownerCompany->id; ?>" data-project-id="<?php echo $row->id; ?>"><?php echo $formText_Delete_Output;?></button><?php } ?>
                                            <?php if(intval($row->parentId) == 0){?>
                                                <?php if($moduleAccesslevel > 10) { ?><button class="output-btn small output-edit-subproject editBtnBlank" data-ownercompany-id="<?php echo $ownerCompany->id; ?>" data-projectparent-id="<?php echo $row->id; ?>" data-project-id=""><?php echo $formText_AddSubProject_Output;?></button><?php } ?>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                    <?php
                                        $result2 = $o_main->db->query("SELECT * FROM projectforaccounting WHERE ownercompany_id = ? AND parentId = ?", array($ownerCompany->id, $row->id));
                                        if($result2 && $result2->num_rows() > 0)
                                        foreach($result2->result() AS $row2){
                                            ?>
                                            <tr class="subproject">
                                                <td><?php echo $row2->projectnumber; ?></td>
                                                <td><?php echo $row2->name; ?></td>
                                                <td>
                                                    <?php if($moduleAccesslevel > 10) { ?><button class="output-btn small output-edit-project editBtnBlank" data-ownercompany-id="<?php echo $ownerCompany->id; ?>" data-project-id="<?php echo $row2->id; ?>"><?php echo $formText_Edit_Output;?></button><?php } ?>
                                                    <?php if($moduleAccesslevel > 110) { ?><button class="output-btn small output-delete-project editBtnBlank" data-ownercompany-id="<?php echo $ownerCompany->id; ?>" data-project-id="<?php echo $row2->id; ?>"><?php echo $formText_Delete_Output;?></button><?php } ?>
                                                </td>
                                            </tr>
                                            <?php
                                        }

                                    ?>
                                <?php } ?>
                            </table>
                        </div>
                    </div> -->
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
var out_popup;
var out_popup_options={
	follow: [true, true],
	fadeSpeed: 0,
	followSpeed: 200,
	modalClose: false,
	escClose: false,
	closeClass:'b-close',
	onOpen: function(){
		$(this).addClass('opened');
		//$(this).find('.b-close').on('click', function(){out_popup.close();});
	},
	onClose: function(){
		$(this).removeClass('opened');
		if($(this).is('.close-reload')) {
            var redirectUrl = $(this).data("redirect");
            if(redirectUrl != "" && redirectUrl != undefined){
                document.location.href = redirectUrl;
            } else {
                loadView("details", {cid:'<?php echo $cid;?>'});
            }
          // window.location.reload();
        }
	}
};

$(document).ready(function() {
    $('.output-edit-detail').on('click', function(e) {
        e.preventDefault();
        var data = {
            ownercompanyId: '<?php echo $cid; ?>',
        };
        ajaxCall('editOwnerDetails', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    });
    $(".output-edit-sale-detail").on('click', function(e) {
        e.preventDefault();
        var data = {
            ownercompanyId: '<?php echo $cid; ?>',
        };
        ajaxCall('editSaleDetails', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    });
    $('.output-edit-kid-detail').on('click', function(e) {
        e.preventDefault();
        var data = {
            ownercompanyId: '<?php echo $cid; ?>',
        };
        ajaxCall('editKidDetails', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    });
    $('.output-edit-invoice-detail').on('click', function(e) {
        e.preventDefault();
        var data = {
            ownercompanyId: '<?php echo $cid; ?>',
        };
        ajaxCall('editInvoiceDetails', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    });
    $('.output-edit-accounting-detail').on('click', function(e) {
        e.preventDefault();
        var data = {
            ownercompanyId: '<?php echo $cid; ?>',
        };
        ajaxCall('editAccountingDetails', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    });
    $('.output-edit-customerid-detail').on('click', function(e) {
        e.preventDefault();
        var data = {
            ownercompanyId: '<?php echo $cid; ?>',
        };
        ajaxCall('editCustomerIdDetails', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    });
    $('.output-edit-export-detail').on('click', function(e) {
        e.preventDefault();
        var data = {
            ownercompanyId: '<?php echo $cid; ?>',
        };
        ajaxCall('editExportDetails', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    });
    $('.output-edit-export2-detail').on('click', function(e) {
        e.preventDefault();
        var data = {
            ownercompanyId: '<?php echo $cid; ?>',
        };
        ajaxCall('editExport2Details', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    });
	$(".output-edit-set-detail").off("click").on("click", function(e){
		e.preventDefault();
        var data = {
            ownercompanyId: '<?php echo $cid; ?>',
        };
        ajaxCall('editSets', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
	})

    $('.output-edit-project').on('click', function(e) {
        e.preventDefault();
        var data = {
            ownercompanyId: '<?php echo $cid; ?>',
            projectId: $(this).data('project-id'),
        };
        ajaxCall('editProject', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    });

    $('.output-edit-subproject').on('click', function(e) {
        e.preventDefault();
        var data = {
            ownercompanyId: '<?php echo $cid; ?>',
            projectId: $(this).data('project-id'),
            parentId: $(this).data('projectparent-id'),
        };
        ajaxCall('editProject', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    });

    $('.addLogoBtn').on('click', function(e) {
        e.preventDefault();
        var data = {
            ownercompanyId: '<?php echo $cid; ?>',
        };
        ajaxCall('addLogo', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    });


    $(".deleteLogo").on('click', function(e){
        e.preventDefault();
        var self = $(this);

        bootbox.confirm('<?php echo $formText_ConfirmDelete_output; ?>', function(result) {
            if (result) {
                var data = {
                    imageUploadId: self.data('image-upload-id'),
                    ownercompanyId: '<?php echo $cid; ?>',
                };
                ajaxCall('deleteLogo', data, function(json) {
                    loadView("details", {cid:'<?php echo $cid;?>'});
                });
            }
        });
    });


	$('.editLogo').on('click', function(e) {
        e.preventDefault();
        var data = {
            ownercompanyId: '<?php echo $cid; ?>',
			cid: $(this).data("logo-id")
        };
        ajaxCall('addAdditionalLogos', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    });
	$('.addLogosBtn').on('click', function(e) {
        e.preventDefault();
        var data = {
            ownercompanyId: '<?php echo $cid; ?>',
        };
        ajaxCall('addAdditionalLogos', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    });
	$(".deleteLogos").on('click', function(e){
        e.preventDefault();
        var self = $(this);

        bootbox.confirm('<?php echo $formText_ConfirmDelete_output; ?>', function(result) {
            if (result) {
                var data = {
                    cid: self.data('logo-id'),
                };
                ajaxCall('deleteAdditionalLogos', data, function(json) {
                    loadView("details", {cid:'<?php echo $cid;?>'});
                });
            }
        });
	   });

     $('.addInvoiceLogoBtn').on('click', function(e) {
        e.preventDefault();
        var data = {
            ownercompanyId: '<?php echo $cid; ?>',
        };
        ajaxCall('addInvoiceLogo', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    });


    $(".deleteInvoiceLogo").on('click', function(e){
        e.preventDefault();
        var self = $(this);

        bootbox.confirm('<?php echo $formText_ConfirmDelete_output; ?>', function(result) {
            if (result) {
                var data = {
                    imageUploadId: self.data('image-upload-id'),
                    ownercompanyId: '<?php echo $cid; ?>',
                };
                ajaxCall('deleteInvoiceLogo', data, function(json) {
                    loadView("details", {cid:'<?php echo $cid;?>'});
                });
            }
        });
    });

     $('.addInvoiceFooterLogoBtn').on('click', function(e) {
        e.preventDefault();
        var data = {
            ownercompanyId: '<?php echo $cid; ?>',
        };
        ajaxCall('addInvoiceFooterLogo', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    });
	$(".deleteInvoiceFooterLogo").on('click', function(e){
        e.preventDefault();
        var self = $(this);

        bootbox.confirm('<?php echo $formText_ConfirmDelete_output; ?>', function(result) {
            if (result) {
                var data = {
                    imageUploadId: self.data('image-upload-id'),
                    ownercompanyId: '<?php echo $cid; ?>',
                };
                ajaxCall('deleteInvoiceFooterLogo', data, function(json) {
                    loadView("details", {cid:'<?php echo $cid;?>'});
                });
            }
        });
    });

    $(".output-delete-project").on('click', function(e){
        e.preventDefault();
        var self = $(this);

        bootbox.confirm('<?php echo $formText_ConfirmDelete_output; ?>', function(result) {
            if (result) {
                var data = {
                    projectId: self.data('project-id'),
                    action: 'deleteProject'
                };
                ajaxCall('editProject', data, function(json) {
                    loadView("details", {cid:'<?php echo $cid;?>'});
                });
            }
        });
    });



	$(".contactPersonList").sortable({
		update: function(event, ui) {
			var data = {
				order: $(this).sortable('serialize'),
				action: "updateOrder"
			}
			ajaxCall('editContactperson', data, function(obj) {
				loadView("details", {cid:'<?php echo $cid;?>'});
			});
		}
	});
	$(".editContactperson").off("click").on("click", function(e){
		e.preventDefault();
		var contactId = $(this).data("contactid");
        var data = {
            contactId: contactId,
			ownercompany_id: '<?php echo $cid;?>'
        };
        ajaxCall('editContactperson', data, function(obj) {
			$('#popupeditboxcontent').html(obj.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
        });
	})
	$(".deleteContactperson").on('click', function(e){
        e.preventDefault();
		var contactId = $(this).data("contactid");
        var data = {
            contactId: contactId,
            output_delete: 1
        };
        bootbox.confirm('<?php echo $formText_ConfirmDeleteContact_output; ?>', function(result) {
            if (result) {
                ajaxCall('editContactperson', data, function(json) {
                    loadView("details", {cid:'<?php echo $cid;?>'});
                });
            }
        })
    });


	$(".editQualityIcon").off("click").on("click", function(e){
		e.preventDefault();
		var iconId = $(this).data("iconid");
        var data = {
            iconId: iconId,
			ownercompany_id: '<?php echo $cid;?>'
        };
        ajaxCall('editQualityIcon', data, function(obj) {
			$('#popupeditboxcontent').html(obj.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
        });
	})
	$(".deleteQualityIcon").on('click', function(e){
        e.preventDefault();
		var iconId = $(this).data("iconid");
        var data = {
			iconId: iconId,
            output_delete: 1
        };
        bootbox.confirm('<?php echo $formText_ConfirmDeleteIcon_output; ?>', function(result) {
            if (result) {
                ajaxCall('editQualityIcon', data, function(json) {
                    loadView("details", {cid:'<?php echo $cid;?>'});
                });
            }
        })
    });
});
</script>

<style>
#p_container .p_contentBlock .projectDetails .subproject td:first-child {
    padding-left: 40px;
}
.txt-label {
    width: 30%;
}
.office-image {
    margin-bottom:20px;
}
</style>
<style>
.companyInfoTableWrapper {
	background: #fff;
	padding: 15px 15px;
}
.companyInfoBlock {
	border-bottom: 1px solid #cecece;
	padding: 0px 0px 15px 0px;
	margin-bottom: 15px;
}
.companyInfoBlock.last {
	border-bottom: 0;
}
.companyInfoBlock .companyInfoBlockTitle {
	font-weight: bold;
	margin-bottom: 10px;
}
.companyInfoBlock .companyInfoBlockRow {
	padding: 5px 0px;
}
.companyInfoBlock .companyInfoBlockRowLabel {
	display: inline-block;
	width: 150px;
	vertical-align: top;
	color: #7C7C7C;
}
.companyInfoBlock .companyInfoBlockRowText {
	display: inline-block;
	width: calc(100% - 180px);
	vertical-align: top;
}
.companyInfoBlock .companyInfoBlockRowLabelTitle {
	text-transform: uppercase;
	margin-bottom: 5px;
}
.companyInfoBlock .companyInfoBlockContent {
	position: relative;
}
.companyInfoBlock .companyInfoBlockEdit {
	position: absolute;
	bottom: 0px;
	right: 10px;
}
.contactPersonBlock {
	margin-bottom: 10px;
}
.contactPersonBlockRow.titleRow {
	color: #7C7C7C;
}
.cursorButton {
	cursor: pointer;
	color: #46b2e2;
	font-weight: normal;
	margin-left: 10px;
}
.icon_image {
	position: relative;
	display: inline-block;
	vertical-align: middle;
	width: 140px;
	margin-right: 5px;
	margin-bottom: 5px;
}
.icon_image img {
	width: 100%;
	height: 100%;
	object-fit: contain;
}
.icon_image .deleteIcon {
	display: none;
	position: absolute;
	top: 5px;
	right: 5px;
	background: #fff;
	margin-left: 0;
	padding: 3px;
	font-size: 10px;
	border-radius: 2px;
}
.icon_image:hover .deleteIcon {
	display: block;
}
.dragDropText {
	color: #7C7C7C;
	font-weight: normal;
}
</style>
