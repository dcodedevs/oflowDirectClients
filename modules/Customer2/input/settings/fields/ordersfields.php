<?php
$prefields = array("id¤ordersid¤{$formText_id_accounts}¤orders¤ID¤¤¤¤¤0¤1¤¤0¤0¤0¤0¤¤","moduleID¤ordersmoduleID¤{$formText_moduleID_accounts}¤orders¤ModuleID¤¤¤¤¤1¤0¤¤1¤1¤1¤0¤¤","createdBy¤createdBy¤{$formText_createdBy_Input}¤orders¤UsernameLogged¤¤¤¤¤1¤0¤¤0¤1¤0¤¤¤","created¤subjectcreated¤{$formText_created_Input}¤orders¤DateTimeUpdateCreate¤¤¤¤¤1¤0¤¤0¤1¤0¤¤¤","updatedBy¤subjectupdatedBy¤{$formText_updatedBy_Input}¤orders¤UsernameLogged¤¤¤¤¤1¤0¤¤1¤0¤0¤¤¤","updated¤subjectupdated¤{$formText_updated_Input}¤orders¤DateTimeUpdateCreate¤¤¤¤¤1¤0¤¤1¤0¤0¤¤¤","sortnr¤sortnr¤{$formText_ordernr_Input}¤orders¤OrderNr¤¤¤¤¤1¤0¤¤1¤1¤0¤¤¤","Status¤ordersStatus¤{$formText_Status_accounts}¤orders¤Dropdown¤¤¤¤¤1¤0¤1:$formText_active_statusdropdown::2:$formText_onHold_statusdropdown::3:$formText_deliveredMustBeFollowedUp_statusdropdown::4:$formText_finished_statusdropdown:¤1¤1¤0¤0¤¤","projectLeader¤ordersprojectLeader¤{$formText_ProjectLeader_accounts}¤orders¤UserDropdown¤¤¤¤¤1¤0¤¤1¤1¤0¤0¤¤","contactPerson¤orderscontactPerson¤{$formText_ContactPerson_accounts}¤orders¤RelatedIDLayer¤¤¤¤¤0¤0¤contactperson(::)id:ID:customerID:customerID(::)vd:name:Name:¤1¤1¤0¤0¤¤","articleNumber¤ordersarticleNumber¤{$formText_ArticleNumber_accounts}¤orders¤RelatedIDLayer¤¤¤¤¤0¤0¤article(::)id:Nr(::)dcv:name:Name:articleName,c:price:Price:pricePerPiece,c:projectfaccnumber:projectFAccNumber:projectFAccNumber,c:currencyId:currencyId:currencyId¤1¤1¤1¤0¤¤","articleName¤ordersarticleName¤{$formText_ArticleName_accounts}¤orders¤Textfield¤¤¤¤¤0¤0¤¤1¤1¤1¤0¤¤","describtion¤ordersdescribtion¤{$formText_describtion_accounts}¤orders¤Textarea¤¤¤¤¤0¤0¤¤1¤1¤0¤0¤¤","amount¤ordersamount¤{$formText_Amount_accounts}¤orders¤Decimal¤¤1¤¤¤0¤0¤20,4¤1¤1¤0¤0¤¤","pricePerPiece¤orderspricePerPiece¤{$formText_PricePerPiece_accounts}¤orders¤Decimal¤¤¤¤¤0¤0¤¤1¤1¤0¤0¤¤","discountPercent¤ordersdiscountPercent¤{$formText_DiscountPercent_accounts}¤orders¤Decimal¤¤¤¤¤0¤0¤11,2¤1¤1¤0¤0¤¤","priceTotal¤orderspriceTotal¤{$formText_PriceTotal_accounts}¤orders¤Decimal¤¤¤¤¤0¤0¤¤1¤1¤0¤0¤¤","delieveryDate¤ordersdelieveryDate¤{$formText_DelieveryDate_accounts}¤orders¤Date¤¤¤¤¤1¤0¤¤1¤1¤0¤0¤¤","expectedTimeuseMinutes¤ordersexpectedTimeuseMinutes¤{$formText_ExpectedTimeuseMinutes_accounts}¤orders¤Number¤¤¤¤¤1¤0¤¤1¤1¤0¤0¤¤","monthsInvoicedFromStart¤ordersmonthsInvoicedFromStart¤{$formText_MonthsInvoicedFromStart_accounts}¤orders¤Number¤¤¤¤¤1¤1¤¤1¤1¤0¤0¤¤","dateFrom¤ordersdateFrom¤{$formText_DateFrom_accounts}¤orders¤Date¤¤¤¤¤1¤0¤¤1¤1¤0¤0¤¤","dateTo¤ordersdateTo¤{$formText_DateTo_accounts}¤orders¤Date¤¤¤¤¤1¤1¤¤1¤1¤0¤0¤¤","content_status¤orderscontent_status¤{$formText_ContentStatus_accounts}¤orders¤ContentStatus¤¤¤¤¤1¤0¤¤1¤1¤0¤0¤¤","vatCode¤ordersvatCode¤{$formText_VatCode_accounts}¤orders¤Textfield¤¤¤¤¤1¤0¤¤1¤1¤0¤0¤¤","vatPercent¤ordersvatPercent¤{$formText_VatPercent_accounts}¤orders¤Decimal¤¤¤¤¤1¤0¤¤1¤1¤0¤0¤¤","bookaccountNr¤ordersbookaccountNr¤{$formText_BookaccountNr_accounts}¤orders¤Number¤¤¤¤¤1¤0¤¤1¤1¤0¤0¤¤","gross¤ordersgross¤{$formText_Gross_accounts}¤orders¤Decimal¤¤¤¤¤1¤0¤¤1¤1¤0¤0¤¤","currencyId¤orderscurrencyId¤{$formText_CurrencyId_accounts}¤orders¤RelatedIDLayer¤¤¤¤¤1¤0¤currency(::)id:Id(::)vd:shortname:Currency:¤1¤1¤0¤0¤¤","ownercompany_id¤ordersownercompany_id¤{$formText_OwnercompanyId_accounts}¤orders¤Number¤¤¤¤¤0¤0¤¤1¤1¤0¤0¤¤","singleactivity¤orderssingleactivity¤{$formText_Singleactivity_accounts}¤orders¤Checkbox¤¤¤¤¤0¤0¤¤1¤1¤0¤0¤¤","planningStatus¤ordersplanningStatus¤{$formText_PlanningStatus_accounts}¤orders¤Dropdown¤¤¤¤¤0¤0¤0:Not planned::1:In plan::2:Doing::3:On hold¤1¤1¤0¤0¤¤","productelement_id¤ordersproductelement_id¤{$formText_ProductelementId_accounts}¤orders¤RelatedIDLayer¤¤¤¤¤0¤0¤productelement(::)id:ID(::)vd:name:Name:¤1¤1¤0¤0¤¤","orderdescdoccustomer¤ordersorderdescdoccustomer¤{$formText_Orderdescdoccustomer_accounts}¤orders¤File¤¤¤¤¤0¤0¤T1,2¤1¤1¤0¤0¤¤","orderdescdocdeveloper¤ordersorderdescdocdeveloper¤{$formText_Orderdescdocdeveloper_accounts}¤orders¤File¤¤¤¤¤0¤0¤T1,10¤1¤1¤0¤0¤¤","projectchecklist¤ordersprojectchecklist¤{$formText_Projectchecklist_accounts}¤orders¤File¤¤¤¤¤0¤0¤t1¤1¤1¤0¤0¤¤","doneTime¤ordersdoneTime¤{$formText_DoneTime_accounts}¤orders¤DateTimeUpdateCreate¤¤¤¤¤0¤0¤¤1¤1¤0¤0¤¤","invoiceDateSettingFromSubscription¤ordersinvoiceDateSettingFromSubscription¤{$formText_InvoiceDateSettingFromSubscription_accounts}¤orders¤Number¤¤¤¤¤0¤0¤¤1¤1¤0¤0¤¤","seperateInvoiceFromSubscription¤ordersseperateInvoiceFromSubscription¤{$formText_SeperateInvoiceFromSubscription_accounts}¤orders¤ID¤¤¤¤¤0¤0¤¤1¤1¤0¤0¤¤","projectCode¤ordersprojectCode¤{$formText_ProjectCode_accounts}¤orders¤Textfield¤¤¤¤¤0¤0¤¤1¤1¤0¤0¤¤","prepaidCommonCost¤ordersprepaidCommonCost¤{$formText_PrepaidCommonCost_accounts}¤orders¤Checkbox¤¤¤¤¤0¤0¤¤1¤1¤0¤0¤¤","periodization¤ordersperiodization¤{$formText_Periodization_accounts}¤orders¤Dropdown¤¤¤¤¤0¤0¤0:None::1:Divide on month::2:Divide on days in period¤1¤1¤0¤0¤¤","periodizationMonths¤ordersperiodizationMonths¤{$formText_PeriodizationMonths_accounts}¤orders¤Textfield¤¤¤¤¤0¤0¤¤1¤1¤0¤0¤¤","collectingorderId¤orderscollectingorderId¤{$formText_CollectingorderId_accounts}¤orders¤ID¤¤¤¤¤0¤0¤¤1¤1¤0¤0¤¤","customerID¤orderscustomerID¤{$formText_CustomerID_accounts}¤orders¤RelatedIDLayer¤¤¤¤¤0¤0¤customer(::)id:ID(::)vd:name:Name:¤1¤1¤0¤0¤¤","adminFee¤ordersadminFee¤{$formText_AdminFee_accounts}¤orders¤Checkbox¤¤¤¤¤0¤0¤¤1¤1¤0¤0¤¤","not_full_order¤ordersnot_full_order¤{$formText_NotFullOrder_accounts}¤orders¤Checkbox¤¤¤¤¤0¤0¤¤1¤1¤0¤0¤¤","departmentCode¤ordersdepartmentCode¤{$formText_DepartmentCode_accounts}¤orders¤Textfield¤¤¤¤¤0¤0¤¤1¤1¤0¤0¤¤","subscribtionId¤orderssubscribtionId¤{$formText_SubscribtionId_accounts}¤orders¤ID¤¤¤¤¤0¤0¤¤1¤1¤0¤0¤¤","external_sys_id¤ordersexternal_sys_id¤{$formText_ExternalSysId_accounts}¤orders¤Textfield¤¤¤¤¤0¤0¤¤1¤1¤0¤0¤¤");
?>