<?php
$thisFieldType = 0;
$thisDatabaseField = "INT";
$thisShowOnList = 1;
$thisExtraFieldInfo = "";
$thisAboutInfo = str_replace(array(PHP_EOL,"\t"), array('<br />','&nbsp;&nbsp;'),
"This field is popup layer with posibility to link GetynetDB Customer ID to local customer either by search or adding new if not exists.

In <b>Extra value</b> should be written pairing data for adding new customer in such manner <b>[table_field]:[API_field]:[mandatory]:: ...next pair</b>, where [table_field] is field name from same table, [API_field] is parameter key used in API function \"companycreatenew\" and [mandatory] is for field mandatory checking functionality and can contain 0 - not mandatory or 1 - mandatory.

Example:
companyNumber:COMPANYNR:0 ::name:COMPANYNAME:1 ::language:LANGUAGEID:0 ::paAdressline1:ADRESSLINE1:0 ::paAdressline2:ADRESSLINE2:0 ::paPostalCode:POSTALCODE:0 ::paCity:CITY:0 ::paCountry:COUNTRY:0 ::phone:PHONE:0 ::fax:FAX:0::email:EMAIL:0

After input change there optionaly is trigerred JS function <b>changed_[field_ui_id]([changed_id])</b>.

Example: function changed_&lt;?=\$field['ui_id'];?&gt;(id) {}");
?>