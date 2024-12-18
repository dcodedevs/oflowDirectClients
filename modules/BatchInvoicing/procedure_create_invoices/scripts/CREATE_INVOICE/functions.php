<?php
function generate_kidnumber($v_settings, $customerIdToDisplay, $newInvoiceNrOnInvoice, $kidnumber){
	if($v_settings['kidOnInvoice'] > 0)
	{
		//generate kid number if it wasn't set by syncing hook
		if($kidnumber == 0){
			$kidnumber = '';
			$emptynumber = $v_settings['kidCustNumAmount'] - strlen($customerIdToDisplay);
			for($i = 0;$i<$emptynumber;$i++)
				$kidnumber .= "0";
			$kidnumber .= $customerIdToDisplay;

			if($v_settings['kidOnInvoice'] > 1)
			{
				if($v_settings['kidOnInvoice'] == 3) {
					$kidnumber = '';
				}
				$emptynumber = $v_settings['kidInvNumAmount'] - strlen($newInvoiceNrOnInvoice);
				for($i = 0;$i<$emptynumber;$i++)
					$kidnumber .="0";
				$kidnumber .=$newInvoiceNrOnInvoice;
			}
			if($v_settings['kidOnInvoice'] == 4) {
				$kidnumber = '';
				$emptynumber = $v_settings['kidCustNumAmount'] - strlen($customerIdToDisplay);
				for($i = 0;$i<$emptynumber;$i++)
					$kidnumber .= "0";
				$kidnumber .= $customerIdToDisplay;

				$emptynumber = 2 - strlen($v_settings['kidPaymentType']);
				for($i = 0;$i<$emptynumber;$i++)
					$kidnumber .="0";
				$kidnumber .= $v_settings['kidPaymentType'];

				$emptynumber = $v_settings['kidInvNumAmount'] - strlen($newInvoiceNrOnInvoice);
				for($i = 0;$i<$emptynumber;$i++)
					$kidnumber .="0";
				$kidnumber .= $newInvoiceNrOnInvoice;
			}
			if($v_settings['kidOnInvoice'] == 5) {
				$kidnumber = '';
				if(strlen($v_settings['kidOwnercompanyId']) < 7){
					$emptynumber = 7 - strlen($v_settings['kidOwnercompanyId']);
					for($i = 0;$i<$emptynumber;$i++)
						$kidnumber .="0";
				}
				$kidnumber .= $v_settings['kidOwnercompanyId'];

				$emptynumber = $v_settings['kidInvNumAmount'] - strlen($newInvoiceNrOnInvoice);
				for($i = 0;$i<$emptynumber;$i++)
					$kidnumber .="0";
				$kidnumber .= $newInvoiceNrOnInvoice;
			}
			if($v_settings['kidOnInvoice'] == 6) {
				$kidnumber = '';
				if(strlen($v_settings['clientNumberFactoring']) < $v_settings['kidCustNumAmount']){
					$emptynumber = $v_settings['kidCustNumAmount'] - strlen($v_settings['clientNumberFactoring']);
					for($i = 0;$i<$emptynumber;$i++)
						$kidnumber .="0";
				}
				$kidnumber .= $v_settings['clientNumberFactoring'];

				$emptynumber = $v_settings['kidInvNumAmount'] - strlen($newInvoiceNrOnInvoice);
				for($i = 0;$i<$emptynumber;$i++)
					$kidnumber .="0";
				$kidnumber .= $newInvoiceNrOnInvoice;
			}
			if($v_settings['kidOnInvoice'] == 7) {
				$kidnumber = '';
				$emptynumber = $v_settings['kidInvNumAmount'] - strlen($newInvoiceNrOnInvoice);
				for($i = 0;$i<$emptynumber;$i++)
					$kidnumber .="0";
				$kidnumber .=$newInvoiceNrOnInvoice;

				$emptynumber = $v_settings['kidCustNumAmount'] - strlen($customerIdToDisplay);
				for($i = 0;$i<$emptynumber;$i++)
					$kidnumber .= "0";
				$kidnumber .= $customerIdToDisplay;
			}


			$controlnumber = proc_mod10($kidnumber);

			$kidnumber .= $controlnumber;
		}
	}
	return $kidnumber;
}
function proc_rem_style($str)
{
	$str = trim($str);
	$str = strip_tags($str,'<p><ol><ul><li><b><i><strong>');
	$str = str_replace('<p>',"",$str);
	$str = str_replace('</p>',"<br />",$str);
	$str = str_replace('&rdquo;',"\"",$str);
	return $str;
}
function proc_mod10( $kid_u ){

        $siffer = str_split(strrev($kid_u));
        $sum = 0;

        for($i=0; $i<count($siffer); ++$i) $sum += proc_tverrsum(( $i & 1 ) ? $siffer[$i] * 1 : $siffer[$i] * 2);


		$controlnumber = ($sum==0) ? 0 : 10 - substr($sum, -1);
		if ($controlnumber == 10) $controlnumber = 0;

        return $controlnumber;

}

function proc_tverrsum($tall){
	return array_sum(str_split($tall));
}

function proc_handle_input($v_data)
{
	if(is_array($v_data))
	{
		foreach($v_data as $s_key => $s_value)
		{
			$v_data[$s_key] = proc_handle_input($s_value);
		}
	} else {
		$v_data = htmlspecialchars(trim($v_data));
	}
	return $v_data;
}

function validate_ehf_invoice_date_format($date)
{
	//check format 2013-06-30
	if(preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$date))
	{
		return true;
	} else {
		return false;
	}
}
function validate_ehf_invoice_vat_code($vat_code)
{
	//
	// https://vefa.difi.no/ehf/g3/billing-3.0/norway/
	// https://docs.peppol.eu/poacc/billing/3.0/codelist/UNCL5305/
	// UNCL5305
	//
	$v_check = array(
		'S',// - MVA, vanlig sats (25%)
		//'S',// - MVA, redusert sats, mellom (15%)
		//'S',// - Moms, redusert sats, rå fisk (11,11%)
		//'S',// - MVA, redusert sats, lav (12%)
		'E',// - Momsfritak (0%)
		'Z',// - Momsfritak (Varer og tjenester som ikke er inkludert i merverdiavgiftsforskriften) (0%)
		'K',// - Utslippskostnader for private eller offentlige virksomheter - kjøper beregner merverdiavgift (0%)
		'AE',// - Omvendt merverdiavgift (0%)
		'G',// - Eksport av varer og tjenester (0%)
	);

	return in_array($vat_code, $v_check);
}
function validate_ehf_invoice_currency_code($currency)
{
	//
	// https://www.iban.com/currency-codes
	// ISO4217
	//
	$v_check = array(
		array("AFGHANISTAN","Afghani","AFN","971"),
		array("ALBANIA","Lek","ALL","8"),
		array("ALGERIA","Algerian Dinar","DZD","12"),
		array("AMERICAN SAMOA","US Dollar","USD","840"),
		array("ANDORRA","Euro","EUR","978"),
		array("ANGOLA","Kwanza","AOA","973"),
		array("ANGUILLA","East Caribbean Dollar","XCD","951"),
		array("ANTIGUA AND BARBUDA","East Caribbean Dollar","XCD","951"),
		array("ARGENTINA","Argentine Peso","ARS","32"),
		array("ARMENIA","Armenian Dram","AMD","51"),
		array("ARUBA","Aruban Florin","AWG","533"),
		array("AUSTRALIA","Australian Dollar","AUD","36"),
		array("AUSTRIA","Euro","EUR","978"),
		array("AZERBAIJAN","Azerbaijanian Manat","AZN","944"),
		array("BAHAMAS (THE)","Bahamian Dollar","BSD","44"),
		array("BAHRAIN","Bahraini Dinar","BHD","48"),
		array("BANGLADESH","Taka","BDT","50"),
		array("BARBADOS","Barbados Dollar","BBD","52"),
		array("BELARUS","Belarussian Ruble","BYN","933"),
		array("BELGIUM","Euro","EUR","978"),
		array("BELIZE","Belize Dollar","BZD","84"),
		array("BENIN","CFA Franc BCEAO","XOF","952"),
		array("BERMUDA","Bermudian Dollar","BMD","60"),
		array("BHUTAN","Ngultrum","BTN","64"),
		array("BHUTAN","Indian Rupee","INR","356"),
		array("BOLIVIA (PLURINATIONAL STATE OF)","Boliviano","BOB","68"),
		array("BOLIVIA (PLURINATIONAL STATE OF)","Mvdol","BOV","984"),
		array("BONAIRE, SINT EUSTATIUS AND SABA","US Dollar","USD","840"),
		array("BOSNIA AND HERZEGOVINA","Convertible Mark","BAM","977"),
		array("BOTSWANA","Pula","BWP","72"),
		array("BOUVET ISLAND","Norwegian Krone","NOK","578"),
		array("BRAZIL","Brazilian Real","BRL","986"),
		array("BRITISH INDIAN OCEAN TERRITORY (THE)","US Dollar","USD","840"),
		array("BRUNEI DARUSSALAM","Brunei Dollar","BND","96"),
		array("BULGARIA","Bulgarian Lev","BGN","975"),
		array("BURKINA FASO","CFA Franc BCEAO","XOF","952"),
		array("BURUNDI","Burundi Franc","BIF","108"),
		array("CABO VERDE","Cabo Verde Escudo","CVE","132"),
		array("CAMBODIA","Riel","KHR","116"),
		array("CAMEROON","CFA Franc BEAC","XAF","950"),
		array("CANADA","Canadian Dollar","CAD","124"),
		array("CAYMAN ISLANDS (THE)","Cayman Islands Dollar","KYD","136"),
		array("CENTRAL AFRICAN REPUBLIC (THE)","CFA Franc BEAC","XAF","950"),
		array("CHAD","CFA Franc BEAC","XAF","950"),
		array("CHILE","Unidad de Fomento","CLF","990"),
		array("CHILE","Chilean Peso","CLP","152"),
		array("CHINA","Yuan Renminbi","CNY","156"),
		array("CHRISTMAS ISLAND","Australian Dollar","AUD","36"),
		array("COCOS (KEELING) ISLANDS (THE)","Australian Dollar","AUD","36"),
		array("COLOMBIA","Colombian Peso","COP","170"),
		array("COLOMBIA","Unidad de Valor Real","COU","970"),
		array("COMOROS (THE)","Comoro Franc","KMF","174"),
		array("CONGO (THE DEMOCRATIC REPUBLIC OF THE)","Congolese Franc","CDF","976"),
		array("CONGO (THE)","CFA Franc BEAC","XAF","950"),
		array("COOK ISLANDS (THE)","New Zealand Dollar","NZD","554"),
		array("COSTA RICA","Costa Rican Colon","CRC","188"),
		array("CROATIA","Kuna","HRK","191"),
		array("CUBA","Peso Convertible","CUC","931"),
		array("CUBA","Cuban Peso","CUP","192"),
		array("CURA€AO","Netherlands Antillean Guilder","ANG","532"),
		array("CYPRUS","Euro","EUR","978"),
		array("CZECH REPUBLIC (THE)","Czech Koruna","CZK","203"),
		array("C?TE D'IVOIRE","CFA Franc BCEAO","XOF","952"),
		array("DENMARK","Danish Krone","DKK","208"),
		array("DJIBOUTI","Djibouti Franc","DJF","262"),
		array("DOMINICA","East Caribbean Dollar","XCD","951"),
		array("DOMINICAN REPUBLIC (THE)","Dominican Peso","DOP","214"),
		array("ECUADOR","US Dollar","USD","840"),
		array("EGYPT","Egyptian Pound","EGP","818"),
		array("EL SALVADOR","El Salvador Colon","SVC","222"),
		array("EL SALVADOR","US Dollar","USD","840"),
		array("EQUATORIAL GUINEA","CFA Franc BEAC","XAF","950"),
		array("ERITREA","Nakfa","ERN","232"),
		array("ESTONIA","Euro","EUR","978"),
		array("ETHIOPIA","Ethiopian Birr","ETB","230"),
		array("EUROPEAN UNION","Euro","EUR","978"),
		array("FALKLAND ISLANDS (THE) [MALVINAS]","Falkland Islands Pound","FKP","238"),
		array("FAROE ISLANDS (THE)","Danish Krone","DKK","208"),
		array("FIJI","Fiji Dollar","FJD","242"),
		array("FINLAND","Euro","EUR","978"),
		array("FRANCE","Euro","EUR","978"),
		array("FRENCH GUIANA","Euro","EUR","978"),
		array("FRENCH POLYNESIA","CFP Franc","XPF","953"),
		array("FRENCH SOUTHERN TERRITORIES (THE)","Euro","EUR","978"),
		array("GABON","CFA Franc BEAC","XAF","950"),
		array("GAMBIA (THE)","Dalasi","GMD","270"),
		array("GEORGIA","Lari","GEL","981"),
		array("GERMANY","Euro","EUR","978"),
		array("GHANA","Ghana Cedi","GHS","936"),
		array("GIBRALTAR","Gibraltar Pound","GIP","292"),
		array("GREECE","Euro","EUR","978"),
		array("GREENLAND","Danish Krone","DKK","208"),
		array("GRENADA","East Caribbean Dollar","XCD","951"),
		array("GUADELOUPE","Euro","EUR","978"),
		array("GUAM","US Dollar","USD","840"),
		array("GUATEMALA","Quetzal","GTQ","320"),
		array("GUERNSEY","Pound Sterling","GBP","826"),
		array("GUINEA","Guinea Franc","GNF","324"),
		array("GUINEA-BISSAU","CFA Franc BCEAO","XOF","952"),
		array("GUYANA","Guyana Dollar","GYD","328"),
		array("HAITI","Gourde","HTG","332"),
		array("HAITI","US Dollar","USD","840"),
		array("HEARD ISLAND AND McDONALD ISLANDS","Australian Dollar","AUD","36"),
		array("HOLY SEE (THE)","Euro","EUR","978"),
		array("HONDURAS","Lempira","HNL","340"),
		array("HONG KONG","Hong Kong Dollar","HKD","344"),
		array("HUNGARY","Forint","HUF","348"),
		array("ICELAND","Iceland Krona","ISK","352"),
		array("INDIA","Indian Rupee","INR","356"),
		array("INDONESIA","Rupiah","IDR","360"),
		array("INTERNATIONAL MONETARY FUND (IMF)ÿ","SDR (Special Drawing Right)","XDR","960"),
		array("IRAN (ISLAMIC REPUBLIC OF)","Iranian Rial","IRR","364"),
		array("IRAQ","Iraqi Dinar","IQD","368"),
		array("IRELAND","Euro","EUR","978"),
		array("ISLE OF MAN","Pound Sterling","GBP","826"),
		array("ISRAEL","New Israeli Sheqel","ILS","376"),
		array("ITALY","Euro","EUR","978"),
		array("JAMAICA","Jamaican Dollar","JMD","388"),
		array("JAPAN","Yen","JPY","392"),
		array("JERSEY","Pound Sterling","GBP","826"),
		array("JORDAN","Jordanian Dinar","JOD","400"),
		array("KAZAKHSTAN","Tenge","KZT","398"),
		array("KENYA","Kenyan Shilling","KES","404"),
		array("KIRIBATI","Australian Dollar","AUD","36"),
		array("KOREA (THE DEMOCRATIC PEOPLE?S REPUBLIC OF)","North Korean Won","KPW","408"),
		array("KOREA (THE REPUBLIC OF)","Won","KRW","410"),
		array("KUWAIT","Kuwaiti Dinar","KWD","414"),
		array("KYRGYZSTAN","Som","KGS","417"),
		array("LAO PEOPLE?S DEMOCRATIC REPUBLIC (THE)","Kip","LAK","418"),
		array("LATVIA","Euro","EUR","978"),
		array("LEBANON","Lebanese Pound","LBP","422"),
		array("LESOTHO","Loti","LSL","426"),
		array("LESOTHO","Rand","ZAR","710"),
		array("LIBERIA","Liberian Dollar","LRD","430"),
		array("LIBYA","Libyan Dinar","LYD","434"),
		array("LIECHTENSTEIN","Swiss Franc","CHF","756"),
		array("LITHUANIA","Euro","EUR","978"),
		array("LUXEMBOURG","Euro","EUR","978"),
		array("MACAO","Pataca","MOP","446"),
		array("MADAGASCAR","Malagasy Ariary","MGA","969"),
		array("MALAWI","Kwacha","MWK","454"),
		array("MALAYSIA","Malaysian Ringgit","MYR","458"),
		array("MALDIVES","Rufiyaa","MVR","462"),
		array("MALI","CFA Franc BCEAO","XOF","952"),
		array("MALTA","Euro","EUR","978"),
		array("MARSHALL ISLANDS (THE)","US Dollar","USD","840"),
		array("MARTINIQUE","Euro","EUR","978"),
		array("MAURITANIA","Ouguiya","MRU","929"),
		array("MAURITIUS","Mauritius Rupee","MUR","480"),
		array("MAYOTTE","Euro","EUR","978"),
		array("MEMBER COUNTRIES OF THE AFRICAN DEVELOPMENT BANK GROUP","ADB Unit of Account","XUA","965"),
		array("MEXICO","Mexican Peso","MXN","484"),
		array("MEXICO","Mexican Unidad de Inversion (UDI)","MXV","979"),
		array("MICRONESIA (FEDERATED STATES OF)","US Dollar","USD","840"),
		array("MOLDOVA (THE REPUBLIC OF)","Moldovan Leu","MDL","498"),
		array("MONACO","Euro","EUR","978"),
		array("MONGOLIA","Tugrik","MNT","496"),
		array("MONTENEGRO","Euro","EUR","978"),
		array("MONTSERRAT","East Caribbean Dollar","XCD","951"),
		array("MOROCCO","Moroccan Dirham","MAD","504"),
		array("MOZAMBIQUE","Mozambique Metical","MZN","943"),
		array("MYANMAR","Kyat","MMK","104"),
		array("NAMIBIA","Namibia Dollar","NAD","516"),
		array("NAMIBIA","Rand","ZAR","710"),
		array("NAURU","Australian Dollar","AUD","36"),
		array("NEPAL","Nepalese Rupee","NPR","524"),
		array("NETHERLANDS (THE)","Euro","EUR","978"),
		array("NEW CALEDONIA","CFP Franc","XPF","953"),
		array("NEW ZEALAND","New Zealand Dollar","NZD","554"),
		array("NICARAGUA","Cordoba Oro","NIO","558"),
		array("NIGER (THE)","CFA Franc BCEAO","XOF","952"),
		array("NIGERIA","Naira","NGN","566"),
		array("NIUE","New Zealand Dollar","NZD","554"),
		array("NORFOLK ISLAND","Australian Dollar","AUD","36"),
		array("NORTHERN MARIANA ISLANDS (THE)","US Dollar","USD","840"),
		array("NORWAY","Norwegian Krone","NOK","578"),
		array("OMAN","Rial Omani","OMR","512"),
		array("PAKISTAN","Pakistan Rupee","PKR","586"),
		array("PALAU","US Dollar","USD","840"),
		array("PANAMA","Balboa","PAB","590"),
		array("PANAMA","US Dollar","USD","840"),
		array("PAPUA NEW GUINEA","Kina","PGK","598"),
		array("PARAGUAY","Guarani","PYG","600"),
		array("PERU","Nuevo Sol","PEN","604"),
		array("PHILIPPINES (THE)","Philippine Peso","PHP","608"),
		array("PITCAIRN","New Zealand Dollar","NZD","554"),
		array("POLAND","Zloty","PLN","985"),
		array("PORTUGAL","Euro","EUR","978"),
		array("PUERTO RICO","US Dollar","USD","840"),
		array("QATAR","Qatari Rial","QAR","634"),
		array("REPUBLIC OF NORTH MACEDONIA","Denar","MKD","807"),
		array("ROMANIA","Romanian Leu","RON","946"),
		array("RUSSIAN FEDERATION (THE)","Russian Ruble","RUB","643"),
		array("RWANDA","Rwanda Franc","RWF","646"),
		array("RUNION","Euro","EUR","978"),
		array("SAINT BARTHLEMY","Euro","EUR","978"),
		array("SAINT HELENA, ASCENSION AND TRISTAN DA CUNHA","Saint Helena Pound","SHP","654"),
		array("SAINT KITTS AND NEVIS","East Caribbean Dollar","XCD","951"),
		array("SAINT LUCIA","East Caribbean Dollar","XCD","951"),
		array("SAINT MARTIN (FRENCH PART)","Euro","EUR","978"),
		array("SAINT PIERRE AND MIQUELON","Euro","EUR","978"),
		array("SAINT VINCENT AND THE GRENADINES","East Caribbean Dollar","XCD","951"),
		array("SAMOA","Tala","WST","882"),
		array("SAN MARINO","Euro","EUR","978"),
		array("SAO TOME AND PRINCIPE","Dobra","STN","930"),
		array("SAUDI ARABIA","Saudi Riyal","SAR","682"),
		array("SENEGAL","CFA Franc BCEAO","XOF","952"),
		array("SERBIA","Serbian Dinar","RSD","941"),
		array("SEYCHELLES","Seychelles Rupee","SCR","690"),
		array("SIERRA LEONE","Leone","SLL","694"),
		array("SINGAPORE","Singapore Dollar","SGD","702"),
		array("SINT MAARTEN (DUTCH PART)","Netherlands Antillean Guilder","ANG","532"),
		array("SISTEMA UNITARIO DE COMPENSACION REGIONAL DE PAGOS 'SUCRE'","Sucre","XSU","994"),
		array("SLOVAKIA","Euro","EUR","978"),
		array("SLOVENIA","Euro","EUR","978"),
		array("SOLOMON ISLANDS","Solomon Islands Dollar","SBD","90"),
		array("SOMALIA","Somali Shilling","SOS","706"),
		array("SOUTH AFRICA","Rand","ZAR","710"),
		array("SOUTH SUDAN","South Sudanese Pound","SSP","728"),
		array("SPAIN","Euro","EUR","978"),
		array("SRI LANKA","Sri Lanka Rupee","LKR","144"),
		array("SUDAN (THE)","Sudanese Pound","SDG","938"),
		array("SURINAME","Surinam Dollar","SRD","968"),
		array("SVALBARD AND JAN MAYEN","Norwegian Krone","NOK","578"),
		array("SWAZILAND","Lilangeni","SZL","748"),
		array("SWEDEN","Swedish Krona","SEK","752"),
		array("SWITZERLAND","WIR Euro","CHE","947"),
		array("SWITZERLAND","Swiss Franc","CHF","756"),
		array("SWITZERLAND","WIR Franc","CHW","948"),
		array("SYRIAN ARAB REPUBLIC","Syrian Pound","SYP","760"),
		array("TAIWAN (PROVINCE OF CHINA)","New Taiwan Dollar","TWD","901"),
		array("TAJIKISTAN","Somoni","TJS","972"),
		array("TANZANIA, UNITED REPUBLIC OF","Tanzanian Shilling","TZS","834"),
		array("THAILAND","Baht","THB","764"),
		array("TIMOR-LESTE","US Dollar","USD","840"),
		array("TOGO","CFA Franc BCEAO","XOF","952"),
		array("TOKELAU","New Zealand Dollar","NZD","554"),
		array("TONGA","Pa?anga","TOP","776"),
		array("TRINIDAD AND TOBAGO","Trinidad and Tobago Dollar","TTD","780"),
		array("TUNISIA","Tunisian Dinar","TND","788"),
		array("TURKEY","Turkish Lira","TRY","949"),
		array("TURKMENISTAN","Turkmenistan New Manat","TMT","934"),
		array("TURKS AND CAICOS ISLANDS (THE)","US Dollar","USD","840"),
		array("TUVALU","Australian Dollar","AUD","36"),
		array("UGANDA","Uganda Shilling","UGX","800"),
		array("UKRAINE","Hryvnia","UAH","980"),
		array("UNITED ARAB EMIRATES (THE)","UAE Dirham","AED","784"),
		array("UNITED KINGDOM OF GREAT BRITAIN AND NORTHERN IRELAND (THE)","Pound Sterling","GBP","826"),
		array("UNITED STATES MINOR OUTLYING ISLANDS (THE)","US Dollar","USD","840"),
		array("UNITED STATES OF AMERICA (THE)","US Dollar","USD","840"),
		array("UNITED STATES OF AMERICA (THE)","US Dollar (Next day)","USN","997"),
		array("URUGUAY","Uruguay Peso en Unidades Indexadas (URUIURUI)","UYI","940"),
		array("URUGUAY","Peso Uruguayo","UYU","858"),
		array("UZBEKISTAN","Uzbekistan Sum","UZS","860"),
		array("VANUATU","Vatu","VUV","548"),
		array("VENEZUELA (BOLIVARIAN REPUBLIC OF)","Bolivar","VEF","937"),
		array("VIET NAM","Dong","VND","704"),
		array("VIRGIN ISLANDS (BRITISH)","US Dollar","USD","840"),
		array("VIRGIN ISLANDS (U.S.)","US Dollar","USD","840"),
		array("WALLIS AND FUTUNA","CFP Franc","XPF","953"),
		array("WESTERN SAHARA","Moroccan Dirham","MAD","504"),
		array("YEMEN","Yemeni Rial","YER","886"),
		array("ZAMBIA","Zambian Kwacha","ZMW","967"),
		array("ZIMBABWE","Zimbabwe Dollar","ZWL","932"),
		array("LAND ISLANDS","Euro","EUR","978")
	);

	$b_found = FALSE;
	foreach($v_check as $v_item) if($currency == $v_item[2] || $currency == $v_item[3]) $b_found = TRUE;

	return $b_found;
}

function validate_ehf_invoice_country($country)
{
	$v_check = array(
		'AD' => 'Andorra',
		'AE' => 'United Arab Emirates',
		'AF' => 'Afghanistan',
		'AG' => 'Antigua and Barbuda',
		'AI' => 'Anguilla',
		'AL' => 'Albania',
		'AM' => 'Armenia',
		'AO' => 'Angola',
		'AQ' => 'Antarctica',
		'AR' => 'Argentina',
		'AS' => 'American Samoa',
		'AT' => 'Austria',
		'AU' => 'Australia',
		'AW' => 'Aruba',
		'AX' => 'land Islands',
		'AZ' => 'Azerbaijan',
		'BA' => 'Bosnia and Herzegovina',
		'BB' => 'Barbados',
		'BD' => 'Bangladesh',
		'BE' => 'Belgium',
		'BF' => 'Burkina Faso',
		'BG' => 'Bulgaria',
		'BH' => 'Bahrain',
		'BI' => 'Burundi',
		'BJ' => 'Benin',
		'BL' => 'Saint Barth‚lemy',
		'BM' => 'Bermuda',
		'BN' => 'Brunei Darussalam',
		'BO' => 'Bolivia (Plurinational State of)',
		'BQ' => 'Bonaire, Sint Eustatius and Saba',
		'BR' => 'Brazil',
		'BS' => 'Bahamas',
		'BT' => 'Bhutan',
		'BV' => 'Bouvet Island',
		'BW' => 'Botswana',
		'BY' => 'Belarus',
		'BZ' => 'Belize',
		'CA' => 'Canada',
		'CC' => 'Cocos (Keeling) Islands',
		'CD' => 'Congo, Democratic Republic of the',
		'CF' => 'Central African Republic',
		'CG' => 'Congo',
		'CH' => 'Switzerland',
		'CI' => 'C"te d\'Ivoire',
		'CK' => 'Cook Islands',
		'CL' => 'Chile',
		'CM' => 'Cameroon',
		'CN' => 'China',
		'CO' => 'Colombia',
		'CR' => 'Costa Rica',
		'CU' => 'Cuba',
		'CV' => 'Cabo Verde',
		'CW' => 'Cura‡ao',
		'CX' => 'Christmas Island',
		'CY' => 'Cyprus',
		'CZ' => 'Czechia',
		'DE' => 'Germany',
		'DJ' => 'Djibouti',
		'DK' => 'Denmark',
		'DM' => 'Dominica',
		'DO' => 'Dominican Republic',
		'DZ' => 'Algeria',
		'EC' => 'Ecuador',
		'EE' => 'Estonia',
		'EG' => 'Egypt',
		'EH' => 'Western Sahara',
		'ER' => 'Eritrea',
		'ES' => 'Spain',
		'ET' => 'Ethiopia',
		'FI' => 'Finland',
		'FJ' => 'Fiji',
		'FK' => 'Falkland Islands (Malvinas)',
		'FM' => 'Micronesia (Federated States of)',
		'FO' => 'Faroe Islands',
		'FR' => 'France',
		'GA' => 'Gabon',
		'GB' => 'United Kingdom of Great Britain and Northern Ireland',
		'GD' => 'Grenada',
		'GE' => 'Georgia',
		'GF' => 'French Guiana',
		'GG' => 'Guernsey',
		'GH' => 'Ghana',
		'GI' => 'Gibraltar',
		'GL' => 'Greenland',
		'GM' => 'Gambia',
		'GN' => 'Guinea',
		'GP' => 'Guadeloupe',
		'GQ' => 'Equatorial Guinea',
		'GR' => 'Greece',
		'GS' => 'South Georgia and the South Sandwich Islands',
		'GT' => 'Guatemala',
		'GU' => 'Guam',
		'GW' => 'Guinea-Bissau',
		'GY' => 'Guyana',
		'HK' => 'Hong Kong',
		'HM' => 'Heard Island and McDonald Islands',
		'HN' => 'Honduras',
		'HR' => 'Croatia',
		'HT' => 'Haiti',
		'HU' => 'Hungary',
		'ID' => 'Indonesia',
		'IE' => 'Ireland',
		'IL' => 'Israel',
		'IM' => 'Isle of Man',
		'IN' => 'India',
		'IO' => 'British Indian Ocean Territory',
		'IQ' => 'Iraq',
		'IR' => 'Iran (Islamic Republic of)',
		'IS' => 'Iceland',
		'IT' => 'Italy',
		'JE' => 'Jersey',
		'JM' => 'Jamaica',
		'JO' => 'Jordan',
		'JP' => 'Japan',
		'KE' => 'Kenya',
		'KG' => 'Kyrgyzstan',
		'KH' => 'Cambodia',
		'KI' => 'Kiribati',
		'KM' => 'Comoros',
		'KN' => 'Saint Kitts and Nevis',
		'KP' => 'Korea (Democratic People\'s Republic of)',
		'KR' => 'Korea, Republic of',
		'KW' => 'Kuwait',
		'KY' => 'Cayman Islands',
		'KZ' => 'Kazakhstan',
		'LA' => 'Lao People\'s Democratic Republic',
		'LB' => 'Lebanon',
		'LC' => 'Saint Lucia',
		'LI' => 'Liechtenstein',
		'LK' => 'Sri Lanka',
		'LR' => 'Liberia',
		'LS' => 'Lesotho',
		'LT' => 'Lithuania',
		'LU' => 'Luxembourg',
		'LV' => 'Latvia',
		'LY' => 'Libya',
		'MA' => 'Morocco',
		'MC' => 'Monaco',
		'MD' => 'Moldova, Republic of',
		'ME' => 'Montenegro',
		'MF' => 'Saint Martin (French part)',
		'MG' => 'Madagascar',
		'MH' => 'Marshall Islands',
		'MK' => 'North Macedonia',
		'ML' => 'Mali',
		'MM' => 'Myanmar',
		'MN' => 'Mongolia',
		'MO' => 'Macao',
		'MP' => 'Northern Mariana Islands',
		'MQ' => 'Martinique',
		'MR' => 'Mauritania',
		'MS' => 'Montserrat',
		'MT' => 'Malta',
		'MU' => 'Mauritius',
		'MV' => 'Maldives',
		'MW' => 'Malawi',
		'MX' => 'Mexico',
		'MY' => 'Malaysia',
		'MZ' => 'Mozambique',
		'NA' => 'Namibia',
		'NC' => 'New Caledonia',
		'NE' => 'Niger',
		'NF' => 'Norfolk Island',
		'NG' => 'Nigeria',
		'NI' => 'Nicaragua',
		'NL' => 'Netherlands',
		'NO' => 'Norway',
		'NP' => 'Nepal',
		'NR' => 'Nauru',
		'NU' => 'Niue',
		'NZ' => 'New Zealand',
		'OM' => 'Oman',
		'PA' => 'Panama',
		'PE' => 'Peru',
		'PF' => 'French Polynesia',
		'PG' => 'Papua New Guinea',
		'PH' => 'Philippines',
		'PK' => 'Pakistan',
		'PL' => 'Poland',
		'PM' => 'Saint Pierre and Miquelon',
		'PN' => 'Pitcairn',
		'PR' => 'Puerto Rico',
		'PS' => 'Palestine, State of',
		'PT' => 'Portugal',
		'PW' => 'Palau',
		'PY' => 'Paraguay',
		'QA' => 'Qatar',
		'RE' => 'R‚union',
		'RO' => 'Romania',
		'RS' => 'Serbia',
		'RU' => 'Russian Federation',
		'RW' => 'Rwanda',
		'SA' => 'Saudi Arabia',
		'SB' => 'Solomon Islands',
		'SC' => 'Seychelles',
		'SD' => 'Sudan',
		'SE' => 'Sweden',
		'SG' => 'Singapore',
		'SH' => 'Saint Helena, Ascension and Tristan da Cunha',
		'SI' => 'Slovenia',
		'SJ' => 'Svalbard and Jan Mayen',
		'SK' => 'Slovakia',
		'SL' => 'Sierra Leone',
		'SM' => 'San Marino',
		'SN' => 'Senegal',
		'SO' => 'Somalia',
		'SR' => 'Suriname',
		'SS' => 'South Sudan',
		'ST' => 'Sao Tome and Principe',
		'SV' => 'El Salvador',
		'SX' => 'Sint Maarten (Dutch part)',
		'SY' => 'Syrian Arab Republic',
		'SZ' => 'Eswatini',
		'TC' => 'Turks and Caicos Islands',
		'TD' => 'Chad',
		'TF' => 'French Southern Territories',
		'TG' => 'Togo',
		'TH' => 'Thailand',
		'TJ' => 'Tajikistan',
		'TK' => 'Tokelau',
		'TL' => 'Timor-Leste',
		'TM' => 'Turkmenistan',
		'TN' => 'Tunisia',
		'TO' => 'Tonga',
		'TR' => 'Turkey',
		'TT' => 'Trinidad and Tobago',
		'TV' => 'Tuvalu',
		'TW' => 'Taiwan, Province of China',
		'TZ' => 'Tanzania, United Republic of',
		'UA' => 'Ukraine',
		'UG' => 'Uganda',
		'UM' => 'United States Minor Outlying Islands',
		'US' => 'United States of America',
		'UY' => 'Uruguay',
		'UZ' => 'Uzbekistan',
		'VA' => 'Holy See',
		'VC' => 'Saint Vincent and the Grenadines',
		'VE' => 'Venezuela (Bolivarian Republic of)',
		'VG' => 'Virgin Islands (British)',
		'VI' => 'Virgin Islands (U.S.)',
		'VN' => 'Viet Nam',
		'VU' => 'Vanuatu',
		'WF' => 'Wallis and Futuna',
		'WS' => 'Samoa',
		'YE' => 'Yemen',
		'YT' => 'Mayotte',
		'ZA' => 'South Africa',
		'ZM' => 'Zambia',
		'ZW' => 'Zimbabwe',
	);

	$b_found = FALSE;
	foreach($v_check as $s_key => $s_name) if($country == $s_key) $b_found = TRUE;

	return $b_found;
}
function validate_ehf_invoice($v_data)
{
	$v_return = array();
	$v_data = proc_handle_input($v_data);

	global $o_main;
	$_POST['folder'] = "procedure_create_invoices";
	include(__DIR__."/../../../output/includes/readOutputLanguage.php");

	if('' == $v_data['invoice_nr'])
	{
		$v_return[] = $formText_InvoiceNumberIsMissingOrInvalid_Ehf;
	}
	if('' == $v_data['invoice_issue_date'] || !validate_ehf_invoice_date_format($v_data['invoice_issue_date']))
	{
		$v_return[] = $formText_InvoiceDateIsMissingOrInvalid_Ehf;
	}
	if('' == $v_data['invoice_type_code'])
	{
		$v_return[] = $formText_InvoiceTypeCodeIsMissingOrInvalid_Ehf;
	}
	if(!empty($v_data['tax_point_date']) && !validate_ehf_invoice_date_format($v_data['tax_point_date']))
	{
		$v_return[] = $formText_TaxPointDateIsInvalid_Ehf;
	}
	if('' == $v_data['currency_code'] || !validate_ehf_invoice_currency_code($v_data['currency_code']))
	{
		$v_return[] = $formText_CurrencyCodeIsMissingOrInvalid_Ehf;
	}
	if(!empty($v_data['period_start']) && !validate_ehf_invoice_date_format($v_data['period_start']))
	{
		$v_return[] = $formText_PeriodStartDateIsInvalid_Ehf;
	}
	if(!empty($v_data['period_end']) && !validate_ehf_invoice_date_format($v_data['period_end']))
	{
		$v_return[] = $formText_PeriodEndDateIsInvalid_Ehf;
	}
	if((!empty($v_data['period_start']) && empty($v_data['period_end'])) || (empty($v_data['period_start']) && !empty($v_data['period_end'])))
	{
		$v_return[] = $formText_PeriodIsInvalid_Ehf;
	}
	if('' == $v_data['supplier_org_nr'])
	{
		$v_return[] = $formText_SupplierOrgNumberIsMissingOrInvalid_Ehf;
	}
	if('' == $v_data['supplier_name'])
	{
		$v_return[] = $formText_SupplierNameIsMissingOrInvalid_Ehf;
	}
	if('' == $v_data['supplier_city'])
	{
		$v_return[] = $formText_CityOfSupplierIsMissingOrInvalid_Ehf;
	}
	if('' == $v_data['supplier_postal_code'])
	{
		$v_return[] = $formText_PostalCodeOfSupplierIsMissingOrInvalid_Ehf;
	}
	if('' == $v_data['supplier_country'] || !validate_ehf_invoice_country($v_data['supplier_country']))
	{
		$v_return[] = $formText_CountryOfSupplierIsMissingOrInvalid_Ehf;
	}

	if('' == $v_data['customer_org_nr'])
	{
		$v_return[] = $formText_CustomerOrgNumIsMissingOrInvalid_Ehf;
	}
	if('' == $v_data['customer_org_nr_vat'])
	{
		$v_return[] = $formText_CustomerOrgVatNumIsMissingOrInvalid_Ehf;
	}
	if('' == $v_data['customer_name'])
	{
		$v_return[] = $formText_CustomerNameIsMissingOrInvalid_Ehf;
	}
	if('' == $v_data['customer_city'])
	{
		$v_return[] = $formText_CityOfCustomerIsMissingOrInvalid_Ehf;
	}
	if('' == $v_data['customer_postal_code'])
	{
		$v_return[] = $formText_PostalCodeOfCustomerIsMissingOrInvalid_Ehf;
	}
	if('' == $v_data['customer_country'] || !validate_ehf_invoice_country($v_data['customer_country']))
	{
		$v_return[] = $formText_CountryOfCustomerIsMissingOrInvalid_Ehf;
	}
	if(!empty($v_data['customer_legal_name']) && '' == $v_data['customer_legal_org_nr'])
	{
		$v_return[] = $formText_CustomerLegalOrgNumberIsMissingOrInvalid_Ehf;
	}
	/*if('' == $v_data['customer_contact_id'])
	{
		$v_return[] = $formText_CustomerContactpersonIsMissingOrInvalid_Ehf;
	}*/
	if(!empty($v_data['tax_representative_name']))
	{
		if('' == $v_data['tax_representative_country'] || !validate_ehf_invoice_country($v_data['tax_representative_country']))
		{
			$v_return[] = $formText_TaxRepresentativePartyCountryIsMissingOrInvalid_Ehf;
		}
	}
	if(!empty($v_data['delivery_date']) && !validate_ehf_invoice_date_format($v_data['delivery_date']))
	{
		$v_return[] = $formText_DeliveryDateIsInvalid_Ehf;
	}
	if(!empty($v_data['delivery_country']) && !validate_ehf_invoice_country($v_data['delivery_country']))
	{
		$v_return[] = $formText_CountryOfDeliveryIsMissingOrInvalid_Ehf;
	}
	if('' == $v_data['payment_means_code'])
	{
		$v_return[] = $formText_PaymentMensCodeIsMissingOrInvalid_Ehf;
	}
	if(empty($v_data['payment_due_date']) || !validate_ehf_invoice_date_format($v_data['payment_due_date']))
	{
		$v_return[] = $formText_PaymentDueDateIsMissingOrInvalid_Ehf;
	}
	if('' == $v_data['payment_bank_account_type'])
	{
		$v_return[] = $formText_PaymentBankAccountTypeIsMissingOrInvalid_Ehf;
	}
	if('' == $v_data['payment_bank_account'])
	{
		$v_return[] = $formText_PaymentBankAccountIsMissingOrInvalid_Ehf;
	}

	foreach($v_data['tax_subtotal'] as $v_item)
	{
		if(!validate_ehf_invoice_vat_code($v_item['tax_category']))
		{
			$v_return[] = $formText_GivenTaxCodeIsInvalidForEhf_Ehf.': '.$v_item['tax_category'].' (UNCL5305)';
		}
	}
	return $v_return;
}

function create_ehf_invoice($v_data)
{
	$v_data = proc_handle_input($v_data);

$s_xml = '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL;
$s_xml .= '<Invoice xmlns="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2" xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2" xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2">'.PHP_EOL;
$s_xml .= '<cbc:CustomizationID>urn:cen.eu:en16931:2017#compliant#urn:fdc:peppol.eu:2017:poacc:billing:3.0</cbc:CustomizationID>'.PHP_EOL;
$s_xml .= '<cbc:ProfileID>urn:fdc:peppol.eu:2017:poacc:billing:01:1.0</cbc:ProfileID>'.PHP_EOL;
$s_xml .= '<cbc:ID>'.$v_data['invoice_nr'].'</cbc:ID>'.PHP_EOL;
$s_xml .= '<cbc:IssueDate>'.$v_data['invoice_issue_date'].'</cbc:IssueDate>'.PHP_EOL; //2013-06-30
$s_xml .= '	<cbc:DueDate>'.$v_data['payment_due_date'].'</cbc:DueDate>'.PHP_EOL;
$s_xml .= '<cbc:InvoiceTypeCode>'.$v_data['invoice_type_code'].'</cbc:InvoiceTypeCode>'.PHP_EOL;
if(isset($v_data['invoice_note']) && $v_data['invoice_note'] != '')
$s_xml .= '<cbc:Note>'.$v_data['invoice_note'].'</cbc:Note>'.PHP_EOL;
if(isset($v_data['tax_point_date']) && $v_data['tax_point_date'] != '')
$s_xml .= '<cbc:TaxPointDate>'.$v_data['tax_point_date'].'</cbc:TaxPointDate>'.PHP_EOL;
$s_xml .= '<cbc:DocumentCurrencyCode>'.$v_data['currency_code'].'</cbc:DocumentCurrencyCode>'.PHP_EOL;
if(isset($v_data['accounting_cost']) && $v_data['accounting_cost'] != '')
$s_xml .= '<cbc:AccountingCost>'.$v_data['accounting_cost'].'</cbc:AccountingCost>'.PHP_EOL;
$s_xml .= '<cbc:BuyerReference>'.$v_data['supplier_contact_id'].'</cbc:BuyerReference>'.PHP_EOL;
if(isset($v_data['period_start']) && $v_data['period_start'] != '' && isset($v_data['period_end']) && $v_data['period_end'] != '')
$s_xml .= '<cac:InvoicePeriod><cbc:StartDate>'.$v_data['period_start'].'</cbc:StartDate><cbc:EndDate>'.$v_data['period_end'].'</cbc:EndDate></cac:InvoicePeriod>'.PHP_EOL;
if(isset($v_data['order_reference']) && $v_data['order_reference'] != '')
$s_xml .= '<cac:OrderReference><cbc:ID>'.$v_data['order_reference'].'</cbc:ID></cac:OrderReference>'.PHP_EOL;
if(isset($v_data['contract_document_reference']) && $v_data['contract_document_reference'] != '')
{
$s_xml .= '	<cac:ContractDocumentReference>'.PHP_EOL;
$s_xml .= '		<cbc:ID>'.$v_data['contract_document_reference'].'</cbc:ID>'.PHP_EOL;
$s_xml .= '	</cac:ContractDocumentReference>'.PHP_EOL;
}

foreach($v_data['additional_document_reference'] as $v_item)
{
$s_xml .= '	<cac:AdditionalDocumentReference>'.PHP_EOL;
$s_xml .= '		<cbc:ID>'.$v_item['id'].'</cbc:ID>'.PHP_EOL;
if((isset($v_item['attachment_binary']) && $v_item['attachment_binary'] != '') || (isset($v_item['attachment_uri']) && $v_item['attachment_uri'] != ''))
{
$s_xml .= '		<cac:Attachment>'.PHP_EOL;
if(isset($v_item['attachment_uri']) && $v_item['attachment_uri'] != '')
{
$s_xml .= '			<cac:ExternalReference>'.PHP_EOL;
$s_xml .= '				<cbc:URI>'.$v_item['attachment_uri'].'</cbc:URI>'.PHP_EOL;
$s_xml .= '			</cac:ExternalReference>'.PHP_EOL;
}
if(isset($v_item['attachment_binary']) && $v_item['attachment_binary'] != '')
{
$s_xml .= '			<cbc:EmbeddedDocumentBinaryObject mimeCode="'.$v_item['attachment_mime'].'" filename="'.$v_item['id'].'">'.PHP_EOL;
$s_xml .= $v_item['attachment_binary'].PHP_EOL;
$s_xml .= '			</cbc:EmbeddedDocumentBinaryObject>'.PHP_EOL;
}
$s_xml .= '		</cac:Attachment>'.PHP_EOL;
}
$s_xml .= '	</cac:AdditionalDocumentReference>'.PHP_EOL;
}

$s_xml .= '<cac:AccountingSupplierParty>'.PHP_EOL;
$s_xml .= '<cac:Party>'.PHP_EOL;
$s_xml .= '	<cbc:EndpointID schemeID="0192">'.$v_data['supplier_org_nr'].'</cbc:EndpointID>'.PHP_EOL;
if(isset($v_data['supplier_identification']) && $v_data['supplier_identification'] != '') {
$s_xml .= '		<cac:PartyIdentification>'.PHP_EOL;
$s_xml .= '			<cbc:ID schemeID="0088">'.$v_data['supplier_identification'].'</cbc:ID>'.PHP_EOL;
$s_xml .= '		</cac:PartyIdentification>'.PHP_EOL;
}
$s_xml .= '		<cac:PartyName>'.PHP_EOL;
$s_xml .= '			<cbc:Name>'.$v_data['supplier_name'].'</cbc:Name>'.PHP_EOL;
$s_xml .= '		</cac:PartyName>'.PHP_EOL;
$s_xml .= '		<cac:PostalAddress>'.PHP_EOL;
$s_xml .= '			<cbc:StreetName>'.$v_data['supplier_street'].'</cbc:StreetName>'.PHP_EOL;
//$s_xml .= '			<cbc:AdditionalStreetName>Suite 123</cbc:AdditionalStreetName>'.PHP_EOL;
$s_xml .= '			<cbc:CityName>'.$v_data['supplier_city'].'</cbc:CityName>'.PHP_EOL;
$s_xml .= '			<cbc:PostalZone>'.$v_data['supplier_postal_code'].'</cbc:PostalZone>'.PHP_EOL;
//$s_xml .= '			<cbc:CountrySubentity>RegionA</cbc:CountrySubentity>'.PHP_EOL;
$s_xml .= '			<cac:Country>'.PHP_EOL;
$s_xml .= '				<cbc:IdentificationCode>'.$v_data['supplier_country'].'</cbc:IdentificationCode>'.PHP_EOL;
$s_xml .= '			</cac:Country>'.PHP_EOL;
$s_xml .= '		</cac:PostalAddress>'.PHP_EOL;
if(isset($v_data['supplier_org_nr_vat']) && $v_data['supplier_org_nr_vat'] != '')
{
$s_xml .= '		<cac:PartyTaxScheme>'.PHP_EOL;
$s_xml .= '			<cbc:CompanyID>'.$v_data['supplier_org_nr_vat'].'</cbc:CompanyID>'.PHP_EOL; // 123456785MVA
$s_xml .= '			<cac:TaxScheme>'.PHP_EOL;
$s_xml .= '				<cbc:ID>VAT</cbc:ID>'.PHP_EOL;
$s_xml .= '			</cac:TaxScheme>'.PHP_EOL;
$s_xml .= '		</cac:PartyTaxScheme>'.PHP_EOL;
$s_xml .= '		<cac:PartyTaxScheme>'.PHP_EOL;
$s_xml .= '			<cbc:CompanyID>Foretaksregisteret</cbc:CompanyID>'.PHP_EOL;
$s_xml .= '			<cac:TaxScheme>'.PHP_EOL;
$s_xml .= '				<cbc:ID>TAX</cbc:ID>'.PHP_EOL;
$s_xml .= '			</cac:TaxScheme>'.PHP_EOL;
$s_xml .= '		</cac:PartyTaxScheme>'.PHP_EOL;
}
$s_xml .= '		<cac:PartyLegalEntity>'.PHP_EOL;
$s_xml .= '			<cbc:RegistrationName>'.$v_data['supplier_name'].'</cbc:RegistrationName>'.PHP_EOL;
$s_xml .= '			<cbc:CompanyID schemeID="0192">'.$v_data['supplier_org_nr'].'</cbc:CompanyID>'.PHP_EOL;
$s_xml .= '		</cac:PartyLegalEntity>'.PHP_EOL;
// Recommended
if($v_data['supplier_contact_name'] != '' || $v_data['supplier_contact_phone'] != '' || $v_data['supplier_contact_email'] != '') {
$s_xml .= '		<cac:Contact>'.PHP_EOL;
if(isset($v_data['supplier_contact_name']) && $v_data['supplier_contact_name'] != '')
$s_xml .= '			<cbc:Name>'.$v_data['supplier_contact_name'].'</cbc:Name>'.PHP_EOL;
if(isset($v_data['supplier_contact_phone']) && $v_data['supplier_contact_phone'] != '')
$s_xml .= '			<cbc:Telephone>'.$v_data['supplier_contact_phone'].'</cbc:Telephone>'.PHP_EOL;
if(isset($v_data['supplier_contact_email']) && $v_data['supplier_contact_email'] != '')
$s_xml .= '			<cbc:ElectronicMail>'.$v_data['supplier_contact_email'].'</cbc:ElectronicMail>'.PHP_EOL;
$s_xml .= '		</cac:Contact>'.PHP_EOL;
}
$s_xml .= '	</cac:Party>'.PHP_EOL;
$s_xml .= '</cac:AccountingSupplierParty>'.PHP_EOL;
$s_xml .= '<cac:AccountingCustomerParty>'.PHP_EOL;
$s_xml .= '	<cac:Party>'.PHP_EOL;
$s_xml .= '		<cbc:EndpointID schemeID="0192">'.$v_data['customer_org_nr'].'</cbc:EndpointID>'.PHP_EOL;
$s_xml .= '		<cac:PartyIdentification>'.PHP_EOL;
//$s_xml .= '			<cbc:ID schemeID="0088">'.$v_data['customer_identification'].'</cbc:ID>'.PHP_EOL;
$s_xml .= '			<cbc:ID>'.$v_data['customer_identification'].'</cbc:ID>'.PHP_EOL;
$s_xml .= '		</cac:PartyIdentification>'.PHP_EOL;
$s_xml .= '		<cac:PartyName>'.PHP_EOL;
$s_xml .= '			<cbc:Name>'.$v_data['customer_name'].'</cbc:Name>'.PHP_EOL;
$s_xml .= '		</cac:PartyName>'.PHP_EOL;
$s_xml .= '		<cac:PostalAddress>'.PHP_EOL;
if(isset($v_data['customer_street']) && $v_data['customer_street'] != '')
$s_xml .= '			<cbc:StreetName>'.$v_data['customer_street'].'</cbc:StreetName>'.PHP_EOL;
if(isset($v_data['customer_street_additional']) && $v_data['customer_street_additional'] != '')
$s_xml .= '			<cbc:AdditionalStreetName>'.$v_data['customer_street_additional'].'</cbc:AdditionalStreetName>'.PHP_EOL;
$s_xml .= '			<cbc:CityName>'.$v_data['customer_city'].'</cbc:CityName>'.PHP_EOL;
$s_xml .= '			<cbc:PostalZone>'.$v_data['customer_postal_code'].'</cbc:PostalZone>'.PHP_EOL;
if(isset($v_data['customer_country_subentity']) && $v_data['customer_country_subentity'] != '')
$s_xml .= '			<cbc:CountrySubentity>'.$v_data['customer_country_subentity'].'</cbc:CountrySubentity>'.PHP_EOL;
$s_xml .= '			<cac:Country>'.PHP_EOL;
$s_xml .= '				<cbc:IdentificationCode>'.$v_data['customer_country'].'</cbc:IdentificationCode>'.PHP_EOL;
$s_xml .= '			</cac:Country>'.PHP_EOL;
$s_xml .= '		</cac:PostalAddress>'.PHP_EOL;
$s_xml .= '		<cac:PartyTaxScheme>'.PHP_EOL;
$s_xml .= '			<cbc:CompanyID>'.$v_data['customer_org_nr_vat'].'</cbc:CompanyID>'.PHP_EOL;
$s_xml .= '			<cac:TaxScheme>'.PHP_EOL;
$s_xml .= '				<cbc:ID>VAT</cbc:ID>'.PHP_EOL;
$s_xml .= '			</cac:TaxScheme>'.PHP_EOL;
$s_xml .= '		</cac:PartyTaxScheme>'.PHP_EOL;
if(isset($v_data['customer_legal_name']) && $v_data['customer_legal_name'] != '')
{
$s_xml .= '		<cac:PartyLegalEntity>'.PHP_EOL;
$s_xml .= '			<cbc:RegistrationName>'.$v_data['customer_legal_name'].'</cbc:RegistrationName>'.PHP_EOL;
$s_xml .= '			<cbc:CompanyID schemeID="0192">'.$v_data['customer_legal_org_nr'].'</cbc:CompanyID>'.PHP_EOL;
$s_xml .= '		</cac:PartyLegalEntity>'.PHP_EOL;
}
if($v_data['customer_contact_name'] != '' || $v_data['customer_contact_phone'] != '' || $v_data['customer_contact_email'] != '') {
$s_xml .= '		<cac:Contact>'.PHP_EOL;
if(isset($v_data['customer_contact_name']) && $v_data['customer_contact_name'] != '')
$s_xml .= '			<cbc:Name>'.$v_data['customer_contact_name'].'</cbc:Name>'.PHP_EOL;
if(isset($v_data['customer_contact_phone']) && $v_data['customer_contact_phone'] != '')
$s_xml .= '			<cbc:Telephone>'.$v_data['customer_contact_phone'].'</cbc:Telephone>'.PHP_EOL;
if(isset($v_data['customer_contact_email']) && $v_data['customer_contact_email'] != '')
$s_xml .= '			<cbc:ElectronicMail>'.$v_data['customer_contact_email'].'</cbc:ElectronicMail>'.PHP_EOL;
$s_xml .= '		</cac:Contact>'.PHP_EOL;
}
$s_xml .= '	</cac:Party>'.PHP_EOL;
$s_xml .= '</cac:AccountingCustomerParty>'.PHP_EOL;
if(isset($v_data['payee_identification']) && $v_data['payee_identification'] != '')
{
$s_xml .= '<cac:PayeeParty>'.PHP_EOL;
$s_xml .= '	<cac:PartyIdentification>'.PHP_EOL;
//$s_xml .= '		<cbc:ID schemeID="0088">'.$v_data['payee_identification'].'</cbc:ID>'.PHP_EOL;
$s_xml .= '		<cbc:ID>'.$v_data['payee_identification'].'</cbc:ID>'.PHP_EOL;
$s_xml .= '	</cac:PartyIdentification>'.PHP_EOL;
$s_xml .= '	<cac:PartyName>'.PHP_EOL;
$s_xml .= '		<cbc:Name>'.$v_data['payee_name'].'</cbc:Name>'.PHP_EOL;
$s_xml .= '	</cac:PartyName>'.PHP_EOL;
$s_xml .= '	<cac:PartyLegalEntity>'.PHP_EOL;
$s_xml .= '		<cbc:CompanyID schemeID="0192">'.$v_data['payee_company_id'].'</cbc:CompanyID>'.PHP_EOL;
$s_xml .= '	</cac:PartyLegalEntity>'.PHP_EOL;
$s_xml .= '</cac:PayeeParty>'.PHP_EOL;
}
if(isset($v_data['tax_representative_name']) && $v_data['tax_representative_name'] != '')
{
$s_xml .= '<cac:TaxRepresentativeParty>'.PHP_EOL;
$s_xml .= '	<cac:PartyName>'.PHP_EOL;
$s_xml .= '		<cbc:Name>'.$v_data['tax_representative_name'].'</cbc:Name>'.PHP_EOL;
$s_xml .= '	</cac:PartyName>'.PHP_EOL;
$s_xml .= '	<cac:PostalAddress>'.PHP_EOL;
if(isset($v_data['tax_representative_street']) && $v_data['tax_representative_street'] != '')
$s_xml .= '			<cbc:StreetName>'.$v_data['tax_representative_street'].'</cbc:StreetName>'.PHP_EOL;
if(isset($v_data['tax_representative_street_additional']) && $v_data['tax_representative_street_additional'] != '')
$s_xml .= '			<cbc:AdditionalStreetName>'.$v_data['tax_representative_street_additional'].'</cbc:AdditionalStreetName>'.PHP_EOL;
if(isset($v_data['tax_representative_city']) && $v_data['tax_representative_city'] != '')
$s_xml .= '			<cbc:CityName>'.$v_data['tax_representative_city'].'</cbc:CityName>'.PHP_EOL;
if(isset($v_data['tax_representative_postal_code']) && $v_data['tax_representative_postal_code'] != '')
$s_xml .= '			<cbc:PostalZone>'.$v_data['tax_representative_postal_code'].'</cbc:PostalZone>'.PHP_EOL;
if(isset($v_data['tax_representative_country_subentity']) && $v_data['tax_representative_country_subentity'] != '')
$s_xml .= '			<cbc:CountrySubentity>'.$v_data['tax_representative_country_subentity'].'</cbc:CountrySubentity>'.PHP_EOL;
$s_xml .= '			<cac:Country>'.PHP_EOL;
$s_xml .= '				<cbc:IdentificationCode>'.$v_data['tax_representative_country'].'</cbc:IdentificationCode>'.PHP_EOL;
$s_xml .= '			</cac:Country>'.PHP_EOL;
$s_xml .= '		</cac:PostalAddress>'.PHP_EOL;
if(isset($v_data['tax_representative_tax_scheme_company_nr']) && $v_data['tax_representative_tax_scheme_company_nr'] != '')
{
$s_xml .= '		<cac:PartyTaxScheme>'.PHP_EOL;
$s_xml .= '			<cbc:CompanyID>'.$v_data['tax_representative_tax_scheme_company_nr'].'</cbc:CompanyID>'.PHP_EOL;
$s_xml .= '			<cac:TaxScheme>'.PHP_EOL;
$s_xml .= '				<cbc:ID>VAT</cbc:ID>'.PHP_EOL;
$s_xml .= '			</cac:TaxScheme>'.PHP_EOL;
$s_xml .= '		</cac:PartyTaxScheme>'.PHP_EOL;
}
$s_xml .= '</cac:TaxRepresentativeParty>'.PHP_EOL;
}
if(isset($v_data['delivery_date']) && $v_data['delivery_date'] != '')
{
$s_xml .= '<cac:Delivery>'.PHP_EOL;
$s_xml .= '	<cbc:ActualDeliveryDate>'.$v_data['delivery_date'].'</cbc:ActualDeliveryDate>'.PHP_EOL;
if(!empty($v_data['delivery_location']) || !empty($v_data['delivery_street']) || !empty($v_data['delivery_street_additional']) || !empty($v_data['delivery_city']) || !empty($v_data['delivery_postal_code']) || !empty($v_data['delivery_country']))
{
$s_xml .= '	<cac:DeliveryLocation>'.PHP_EOL;
if(!empty($v_data['delivery_location']))
$s_xml .= '		<cbc:ID schemeID="0088">'.$v_data['delivery_location'].'</cbc:ID>'.PHP_EOL;
if(!empty($v_data['delivery_street']) || !empty($v_data['delivery_street_additional']) || !empty($v_data['delivery_city']) || !empty($v_data['delivery_postal_code']) || !empty($v_data['delivery_country']))
{
$s_xml .= '		<cac:Address>'.PHP_EOL;
if(!empty($v_data['delivery_street']))
$s_xml .= '			<cbc:StreetName>'.$v_data['delivery_street'].'</cbc:StreetName>'.PHP_EOL;
if(!empty($v_data['delivery_street_additional']))
$s_xml .= '			<cbc:BuildingNumber>'.$v_data['delivery_street_additional'].'</cbc:BuildingNumber>'.PHP_EOL;
if(!empty($v_data['delivery_city']))
$s_xml .= '			<cbc:CityName>'.$v_data['delivery_city'].'</cbc:CityName>'.PHP_EOL;
if(!empty($v_data['delivery_postal_code']))
$s_xml .= '			<cbc:PostalZone>'.$v_data['delivery_postal_code'].'</cbc:PostalZone>'.PHP_EOL;
if(empty($v_data['delivery_country'])) $v_data['delivery_country'] = 'NO';
$s_xml .= '			<cac:Country>'.PHP_EOL;
$s_xml .= '				<cbc:IdentificationCode>'.$v_data['delivery_country'].'</cbc:IdentificationCode>'.PHP_EOL;
$s_xml .= '			</cac:Country>'.PHP_EOL;
$s_xml .= '		</cac:Address>'.PHP_EOL;
}
$s_xml .= '	</cac:DeliveryLocation>'.PHP_EOL;
}
$s_xml .= '</cac:Delivery>'.PHP_EOL;
}
$s_xml .= '<cac:PaymentMeans>'.PHP_EOL;
$s_xml .= '	<cbc:PaymentMeansCode>'.$v_data['payment_means_code'].'</cbc:PaymentMeansCode>'.PHP_EOL;
if(isset($v_data['payment_id']) && $v_data['payment_id'] != '')
{
$s_xml .= '	<cbc:PaymentID>'.$v_data['payment_id'].'</cbc:PaymentID>'.PHP_EOL;
}
$s_xml .= '	<cac:PayeeFinancialAccount>'.PHP_EOL;
$s_xml .= '		<cbc:ID>'.$v_data['payment_bank_account'].'</cbc:ID>'.PHP_EOL;
if(isset($v_data['payment_financial_institution_bic']) && $v_data['payment_financial_institution_bic'] != '')
{
$s_xml .= '		<cac:FinancialInstitutionBranch>'.PHP_EOL;
$s_xml .= '			<cbc:ID>'.$v_data['payment_financial_institution_bic'].'</cbc:ID>'.PHP_EOL;
$s_xml .= '		</cac:FinancialInstitutionBranch>'.PHP_EOL;
}
$s_xml .= '	</cac:PayeeFinancialAccount>'.PHP_EOL;
$s_xml .= '</cac:PaymentMeans>'.PHP_EOL;
if(isset($v_data['payment_terms']) && $v_data['payment_terms'] != '')
{
$s_xml .= '<cac:PaymentTerms>'.PHP_EOL;
$s_xml .= '	<cbc:Note>'.$v_data['payment_terms'].'</cbc:Note>'.PHP_EOL;
$s_xml .= '</cac:PaymentTerms>'.PHP_EOL;
}
foreach($v_data['allowance_charge'] as $v_item)
{
$s_xml .= '<cac:AllowanceCharge>'.PHP_EOL;
$s_xml .= '	<cbc:ChargeIndicator>'.$v_item['type'].'</cbc:ChargeIndicator>'.PHP_EOL;
if(isset($v_item['reason_code']) && $v_item['reason_code'] != '')
$s_xml .= '	<cbc:AllowanceChargeReasonCode>'.$v_item['reason_code'].'</cbc:AllowanceChargeReasonCode>'.PHP_EOL;
if(isset($v_item['reason']) && $v_item['reason'] != '')
$s_xml .= '	<cbc:AllowanceChargeReason>'.$v_item['reason'].'</cbc:AllowanceChargeReason>'.PHP_EOL;
$s_xml .= '	<cbc:Amount currencyID="NOK">'.$v_item['amount'].'</cbc:Amount>'.PHP_EOL;
$s_xml .= '	<cac:TaxCategory>'.PHP_EOL;
$s_xml .= '		<cbc:ID>'.$v_item['tax_category'].'</cbc:ID>'.PHP_EOL;
$s_xml .= '		<cbc:Percent>'.$v_item['tax_percent'].'</cbc:Percent>'.PHP_EOL;
$s_xml .= '		<cac:TaxScheme>'.PHP_EOL;
$s_xml .= '			<cbc:ID>VAT</cbc:ID>'.PHP_EOL;
$s_xml .= '		</cac:TaxScheme>'.PHP_EOL;
$s_xml .= '	</cac:TaxCategory>'.PHP_EOL;
$s_xml .= '</cac:AllowanceCharge>'.PHP_EOL;
}
$s_xml .= '<cac:TaxTotal>'.PHP_EOL;
$s_xml .= '	<cbc:TaxAmount currencyID="NOK">'.$v_data['tax_amount'].'</cbc:TaxAmount>'.PHP_EOL;
foreach($v_data['tax_subtotal'] as $v_item)
{
$s_xml .= '	<cac:TaxSubtotal>'.PHP_EOL;
$s_xml .= '		<cbc:TaxableAmount currencyID="NOK">'.$v_item['taxable_amount'].'</cbc:TaxableAmount>'.PHP_EOL;
$s_xml .= '		<cbc:TaxAmount currencyID="NOK">'.$v_item['tax_amount'].'</cbc:TaxAmount>'.PHP_EOL;
$s_xml .= '		<cac:TaxCategory>'.PHP_EOL;
$s_xml .= '			<cbc:ID>'.$v_item['tax_category'].'</cbc:ID>'.PHP_EOL;
$s_xml .= '			<cbc:Percent>'.$v_item['tax_percent'].'</cbc:Percent>'.PHP_EOL;
if(isset($v_item['tax_exemption_reason']) && $v_item['tax_exemption_reason'] != '')
$s_xml .= '			<cbc:TaxExemptionReason>'.$v_item['tax_exemption_reason'].'</cbc:TaxExemptionReason>'.PHP_EOL;
$s_xml .= '			<cac:TaxScheme>'.PHP_EOL;
$s_xml .= '				<cbc:ID>VAT</cbc:ID>'.PHP_EOL;
$s_xml .= '			</cac:TaxScheme>'.PHP_EOL;
$s_xml .= '		</cac:TaxCategory>'.PHP_EOL;
$s_xml .= '	</cac:TaxSubtotal>'.PHP_EOL;
}
$s_xml .= '</cac:TaxTotal>'.PHP_EOL;
$s_xml .= '<cac:LegalMonetaryTotal>'.PHP_EOL;
$s_xml .= '	<cbc:LineExtensionAmount currencyID="NOK">'.$v_data['legal_monetary_line_extension'].'</cbc:LineExtensionAmount>'.PHP_EOL;
$s_xml .= '	<cbc:TaxExclusiveAmount currencyID="NOK">'.$v_data['legal_monetary_tax_exclusive'].'</cbc:TaxExclusiveAmount>'.PHP_EOL;
$s_xml .= '	<cbc:TaxInclusiveAmount currencyID="NOK">'.$v_data['legal_monetary_tax_inclusive'].'</cbc:TaxInclusiveAmount>'.PHP_EOL;
if(isset($v_data['legal_monetary_allowance_total']) && $v_data['legal_monetary_allowance_total'] != '')
$s_xml .= '	<cbc:AllowanceTotalAmount currencyID="NOK">'.$v_data['legal_monetary_allowance_total'].'</cbc:AllowanceTotalAmount>'.PHP_EOL;
if(isset($v_data['legal_monetary_charge_total']) && $v_data['legal_monetary_charge_total'] != '')
$s_xml .= '	<cbc:ChargeTotalAmount currencyID="NOK">'.$v_data['legal_monetary_charge_total'].'</cbc:ChargeTotalAmount>'.PHP_EOL;
if(isset($v_data['legal_monetary_prepaid']) && $v_data['legal_monetary_prepaid'] != '')
$s_xml .= '	<cbc:PrepaidAmount currencyID="NOK">'.$v_data['legal_monetary_prepaid'].'</cbc:PrepaidAmount>'.PHP_EOL;
if(isset($v_data['legal_monetary_payable_rounding']) && $v_data['legal_monetary_payable_rounding'] != '')
$s_xml .= '	<cbc:PayableRoundingAmount currencyID="NOK">'.$v_data['legal_monetary_payable_rounding'].'</cbc:PayableRoundingAmount>'.PHP_EOL;
$s_xml .= '	<cbc:PayableAmount currencyID="NOK">'.$v_data['legal_monetary_payable_amount'].'</cbc:PayableAmount>'.PHP_EOL;
$s_xml .= '</cac:LegalMonetaryTotal>'.PHP_EOL;
foreach($v_data['invoice_line'] as $v_item)
{
$s_xml .= '<cac:InvoiceLine>'.PHP_EOL;
$s_xml .= '	<cbc:ID>'.$v_item['id'].'</cbc:ID>'.PHP_EOL;
if(isset($v_item['note']) && $v_item['note'] != '')
$s_xml .= '	<cbc:Note>'.$v_item['note'].'</cbc:Note>'.PHP_EOL;
$s_xml .= '	<cbc:InvoicedQuantity unitCode="NAR">'.$v_item['quantity'].'</cbc:InvoicedQuantity>'.PHP_EOL;
$s_xml .= '	<cbc:LineExtensionAmount currencyID="NOK">'.$v_item['amount'].'</cbc:LineExtensionAmount>'.PHP_EOL;
if(isset($v_item['accounting_cost']) && $v_item['accounting_cost'] != '')
$s_xml .= '	<cbc:AccountingCost>'.$v_item['accounting_cost'].'</cbc:AccountingCost>'.PHP_EOL;
if(isset($v_item['period_from']) && $v_item['period_from'] != '' && isset($v_item['period_to']) && $v_item['period_to'] != '')
{
$s_xml .= '	<cac:InvoicePeriod>'.PHP_EOL;
$s_xml .= '		<cbc:StartDate>'.$v_item['period_from'].'</cbc:StartDate>'.PHP_EOL;
$s_xml .= '		<cbc:EndDate>'.$v_item['period_to'].'</cbc:EndDate>'.PHP_EOL;
$s_xml .= '	</cac:InvoicePeriod>'.PHP_EOL;
}
if(isset($v_item['reference']) && $v_item['reference'] != '')
{
$s_xml .= '	<cac:OrderLineReference>'.PHP_EOL;
$s_xml .= '		<cbc:LineID>'.$v_item['reference'].'</cbc:LineID>'.PHP_EOL;
$s_xml .= '	</cac:OrderLineReference>'.PHP_EOL;
}
foreach($v_item['allowance_charge'] as $v_sub_item)
{
$s_xml .= '	<cac:AllowanceCharge>'.PHP_EOL;
$s_xml .= '		<cbc:ChargeIndicator>'.$v_sub_item['type'].'</cbc:ChargeIndicator>'.PHP_EOL;
if(isset($v_sub_item['reason']) && $v_sub_item['reason'] != '')
$s_xml .= '		<cbc:AllowanceChargeReason>'.$v_sub_item['reason'].'</cbc:AllowanceChargeReason>'.PHP_EOL;
$s_xml .= '		<cbc:Amount currencyID="NOK">'.$v_sub_item['amount'].'</cbc:Amount>'.PHP_EOL;
$s_xml .= '	</cac:AllowanceCharge>'.PHP_EOL;
}
$s_xml .= '	<cac:Item>'.PHP_EOL;
foreach($v_item['description'] as $v_sub_item)
$s_xml .= '		<cbc:Description>'.$v_sub_item['text'].'</cbc:Description>'.PHP_EOL;
$s_xml .= '		<cbc:Name>'.$v_item['name'].'</cbc:Name>'.PHP_EOL;
if(isset($v_item['sellers_item_identification']) && $v_item['sellers_item_identification'] != '')
{
$s_xml .= '		<cac:SellersItemIdentification>'.PHP_EOL;
$s_xml .= '			<cbc:ID>'.$v_item['sellers_item_identification'].'</cbc:ID>'.PHP_EOL;
$s_xml .= '		</cac:SellersItemIdentification>'.PHP_EOL;
}
if(isset($v_item['standard_item_identification']) && $v_item['standard_item_identification'] != '')
{
$s_xml .= '		<cac:StandardItemIdentification>'.PHP_EOL;
$s_xml .= '			<cbc:ID schemeID="0088">'.$v_item['standard_item_identification'].'</cbc:ID>'.PHP_EOL;
$s_xml .= '		</cac:StandardItemIdentification>'.PHP_EOL;
}
if(isset($v_item['origin_country']) && $v_item['origin_country'] != '')
{
$s_xml .= '		<cac:OriginCountry>'.PHP_EOL;
$s_xml .= '		<cbc:IdentificationCode>'.$v_item['origin_country'].'</cbc:IdentificationCode>'.PHP_EOL;
$s_xml .= '		</cac:OriginCountry>'.PHP_EOL;
}
foreach($v_item['commodity_classification'] as $v_sub_item)
{
$s_xml .= '		<cac:CommodityClassification>'.PHP_EOL;
$s_xml .= '			<cbc:ItemClassificationCode listID="'.$v_sub_item['id'].'">'.$v_sub_item['code'].'</cbc:ItemClassificationCode>'.PHP_EOL;
$s_xml .= '		</cac:CommodityClassification>'.PHP_EOL;
}
$s_xml .= '		<cac:ClassifiedTaxCategory>'.PHP_EOL;
$s_xml .= '			<cbc:ID>'.$v_item['classified_tax_category'].'</cbc:ID>'.PHP_EOL;
$s_xml .= '			<cbc:Percent>'.$v_item['classified_tax_percent'].'</cbc:Percent>'.PHP_EOL;
$s_xml .= '			<cac:TaxScheme>'.PHP_EOL;
$s_xml .= '				<cbc:ID>VAT</cbc:ID>'.PHP_EOL;
$s_xml .= '			</cac:TaxScheme>'.PHP_EOL;
$s_xml .= '		</cac:ClassifiedTaxCategory>'.PHP_EOL;
foreach($v_item['addition_item_property'] as $v_sub_item)
{
$s_xml .= '		<cac:AdditionalItemProperty>'.PHP_EOL;
$s_xml .= '			<cbc:Name>'.$v_sub_item['name'].'</cbc:Name>'.PHP_EOL;
$s_xml .= '			<cbc:Value>'.$v_sub_item['value'].'</cbc:Value>'.PHP_EOL;
$s_xml .= '		</cac:AdditionalItemProperty>'.PHP_EOL;
}
$s_xml .= '	</cac:Item>'.PHP_EOL;
$s_xml .= '	<cac:Price>'.PHP_EOL;
$s_xml .= '		<cbc:PriceAmount currencyID="NOK">'.$v_item['price'].'</cbc:PriceAmount>'.PHP_EOL;
if(isset($v_item['base_quantity']) && $v_item['base_quantity'] != '')
$s_xml .= '		<cbc:BaseQuantity>'.$v_item['base_quantity'].'</cbc:BaseQuantity>'.PHP_EOL;
foreach($v_item['price_allowance_charge'] as $v_sub_item)
{
$s_xml .= '		<cac:AllowanceCharge>'.PHP_EOL;
$s_xml .= '			<cbc:ChargeIndicator>'.$v_sub_item['type'].'</cbc:ChargeIndicator>'.PHP_EOL;
if(isset($v_sub_item['reason']) && $v_sub_item['reason'] != '')
$s_xml .= '			<cbc:AllowanceChargeReason>'.$v_sub_item['reason'].'</cbc:AllowanceChargeReason>'.PHP_EOL;
if(isset($v_sub_item['multiplier_factor']) && $v_sub_item['multiplier_factor'] != '')
$s_xml .= '			<cbc:MultiplierFactorNumeric>'.$v_sub_item['multiplier_factor'].'</cbc:MultiplierFactorNumeric>'.PHP_EOL;
$s_xml .= '			<cbc:Amount currencyID="NOK">'.$v_sub_item['amount'].'</cbc:Amount>'.PHP_EOL;
if(isset($v_sub_item['base_amount']) && $v_sub_item['base_amount'] != '')
$s_xml .= '			<cbc:BaseAmount currencyID="NOK">'.$v_sub_item['base_amount'].'</cbc:BaseAmount>'.PHP_EOL;
$s_xml .= '		</cac:AllowanceCharge>'.PHP_EOL;
}
$s_xml .= '	</cac:Price>'.PHP_EOL;
$s_xml .= '</cac:InvoiceLine>'.PHP_EOL;
}
$s_xml .= '</Invoice>'.PHP_EOL;
return $s_xml;
}

function generateHtml($ordersArray, $v_customer, $v_settings, $bankAccountData, $contantPersonLine,$s_reference,$s_delivery_date,$s_delivery_address, $customerIdToDisplay,$dateValShow,$dateExpireShow,$hasAnyDiscount,$currentCurrencyDisplay, $decimalPlaces, $v_proc_variables, $variables, $accountname, $successfully_drawn="", $processed_credit_card=""){
	global $o_main;
	global $_GET;
	$variables->developeraccess = 0;
	$_GET['folder'] = 'procedure_create_invoices';
	include(__DIR__."/../../../output/includes/readOutputLanguage.php");
	require_once(__DIR__.'/../../../output/fnc_getMaxDecimalAmount.php');

	$s_sql = "SELECT * FROM batch_invoicing_accountconfig";
	$o_query = $o_main->db->query($s_sql);
	$batchinvoicing_accountconfig = $o_query ? $o_query->row_array() : array();
	$processingFeeSum = 0;

	if($v_customer['useOwnInvoiceAdress']) {
		$s_cust_addr_prefix = 'ia';
		$customerAddress = 'own address';
		$customerAddress = $v_customer['iaStreet1']."<br />".(!empty($v_customer['iaStreet2']) ? $v_customer['iaStreet2'] . '<br />' : '').$v_customer['iaPostalNumber']." ".$v_customer['iaCity'] . "<br>" . $v_customer['iaCountry'];
	} else {
		$s_cust_addr_prefix = 'pa';
		$customerAddress = $v_customer['paStreet']."<br />".(!empty($v_customer['paStreet2']) ? $v_customer['paStreet2'] . '<br />' : '').$v_customer['paPostalNumber']." ".$v_customer['paCity'] . "<br>" . $v_customer['paCountry'];
	}
	$s_customer = trim($v_customer['name']." ".$v_customer['middlename']." ".$v_customer['lastname'])."<br />".$customerAddress;

	$s_invoice_text = "<b>".$v_settings['companyname']."</b>
	<br />".$v_settings['companypostalbox']."
	<br />".$v_settings['companyzipcode']." ".$v_settings['companypostalplace']."
	 <br />".$v_settings['companyCountry'];

	$s_invoice_text .= '<br/><table  border="0" cellpadding="0" cellspacing="0">
	<tr><td width="97">'.$formText_Email.':</td><td>'.$v_settings['companyEmail'].'</td></tr>
	<tr><td width="97">'.$formText_phone.':</td><td>'.$v_settings['companyphone'].'</td></tr>
	<tr><td width="97">'.$formText_orgNr.':</td><td>'.$v_settings['companyorgnr'].'</td></tr>';
	if($bankAccountData['companyiban'] != "") {
		$s_invoice_text .= '<tr><td width="97">'.$formText_iban.':</td><td>'.$bankAccountData['companyiban'].'</td></tr>';
	}
	if($bankAccountData['companyswift'] != "") {
		$s_invoice_text .= '<tr><td width="97">'.$formText_swiftCode.':</td><td>'.$bankAccountData['companyswift'].'</td></tr>';
	}
	$s_invoice_text .= '</table>';

	$html1 = '';
	$html2 = '';
	$html3 = '';
	$html4 = '';
	$html5 = '';
	if($ordersArray['totals']['total']  >= 0){
		$pdf_header_text = $formText_invoice_header;
	} else {
		$pdf_header_text = $formText_CreditInvoice_header;
	}
	$html1 = '
	<div>
		<table border="0" cellpadding="0" cellspacing="0">';
		if($v_settings['invoice_template'] == 1){
			$html1 .= '<tr>
				 	<td width="240">
					 	<br /><br /><br /><br /><br />
					</td>
					<td width="300"><br /><br />'.($s_invoice_text).'
					</td>
				</tr>
				<tr>
					<td width="240">'.$formText_customernr." ".$customerIdToDisplay."<br/>".($s_customer).'</td>
					<td width="300">
					<br/><br/>'.$formText_account.'&nbsp;&nbsp;&nbsp;'.$bankAccountData['companyaccount'].'
					<br/>'.$formText_iban.'&nbsp;&nbsp;&nbsp; '.$bankAccountData['companyiban'].'
					<br/>'.$formText_swift.'&nbsp;&nbsp;&nbsp; '.$bankAccountData['companyswift'].'</td>
				</tr>';
		} else {
			$html1 .= '<tr>
				 	<td colspan="2">
				 	<br /><br /><br /><br /><br /><br /><br />
					<br />
					</td>
				</tr>
				<tr>
					<td width="240">'.($s_customer).'</td>
					<td width="300">'.($s_invoice_text).'</td>
				</tr>';
		}

	$html1 .= '<tr><td colspan="2"></td></tr>
		<tr>
			<td>
				<table cellspacing="0" cellpadding="0" border="0" width="220">
					'.(!empty($contantPersonLine) ? '<tr><td>'.$formText_YourContactperson.': '.($contantPersonLine).'</td></tr>' : '').'
					'.(!empty($s_reference) ? '<tr><td>'.$formText_Reference.': '.($s_reference).'</td></tr>' : '').'
					'.(!empty($s_delivery_date) ? '<tr><td>'.$formText_DeliveryDate.': '.($s_delivery_date).'</td></tr>' : '').'
					'.(!empty($s_delivery_address) ? '<tr><td>'.$formText_DeliveryAddress.': '.($s_delivery_address).'</td></tr>' : '').'
				</table>
			</td>';
	if($v_settings['invoice_template'] != 1){
		$html1 .= '
				<td style="background-color: #E6E6E6;">
					<div style="height: 15px;"></div>
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<table cellspacing="0" cellpadding="0" border="0" width="220" style="text-align: left; ">
					<tr><td colspan="2"><span style="font-size:30px;"><b>'.($pdf_header_text).'</b></span></td></tr>

					<tr><td width="82">'.$formText_invoicenr.':</td><td>';
		$html2 = '
					</td></tr>
					<tr><td width="82">'.$formText_date.':</td><td>'.$dateValShow.'</td></tr>
					<tr><td width="82">'.$formText_customernr.':</td><td>'.($customerIdToDisplay).'</td></tr>
					';
		$html2 .= '<tr><td colspan="2"></td></tr><tr><td colspan="2"><b>'.($formText_PaymentInformation).'</b></td></tr>
					<tr><td width="82">'.$formText_dueDate.':</td><td>'.$dateExpireShow.'</td></tr>';
		if($bankAccountData['companyaccount'] != ""){
			$html2 .='<tr><td width="82">'.$formText_account.':</td><td>'.$bankAccountData['companyaccount'].'</td></tr>';
		}
		if($v_settings['kidOnInvoice'] > 0 || $batchinvoicing_accountconfig['activate_not_update_kid_number']){ $html3 .='<tr><td width="82">'.$formText_kidnr.':</td><td >';}
		$html4 = '
					</td></tr>';
		$html5 = '
					</table>
					&nbsp;
				</td>
				</tr>';
	} else {
		foreach($ordersArray['list'] as $order){
			if($order['adminFee']){
				$processingFeeSum+= $order['priceTotal'];
			}
		}
		$html1 .= '<td></td></tr>
		<tr>
			<td colspan="2">
			<table cellspacing="0" cellpadding="3" border="0" width="480px;">
				<tr>
				<td width="240px" style="background-color: #cddce9; font-size:30px;"><b>'.$formText_InvoiceInformation_output.'</b></td>
				<td width="120px" style="background-color: #cddce9; font-size:30px;"><b>'.$pdf_header_text.'</b></td><td width="120px" style="background-color: #cddce9; text-align: right; font-size:30px;"><b>';

		$html2 = '</b></td>
			</tr>
			</table>
			</td>
		</tr>';
		$html5 ='';

		$html3 = '<table cellspacing="0" cellpadding="3" border="0" width="480px;">
			<tr>
				<td><b>'.$formText_Fee.'</b></td>
				<td><b>'.number_format(floatval($processingFeeSum),$decimalPlaces,',',' ').'</b></td>
				<td><b>'.$formText_sumNoVat.'</b></td>
				<td><b>'.number_format(floatval($ordersArray['totals']['totalSum']),$decimalPlaces,',',' ').'</b></td>
				<td><b>'.$formText_vat.'</b></td>
				<td><b>'.number_format(floatval($ordersArray['totals']['totalVat']),$decimalPlaces,',',' ').'</b></td>
				<td><b>'.$formText_total.'</b></td>
				<td style="text-align: right;"><b>'.number_format(floatval($ordersArray['totals']['total']),$decimalPlaces,',',' ').'</b></td>
			</tr>
		</table>
		<br/>
		<table cellspacing="0" cellpadding="2" border="0" width="480px;">
		<tr>
			<td style="background-color: #e8eef5;">'.$formText_date.'</td>
			<td style="background-color: #e8eef5;">'.$dateValShow.'</td>
			<td style="background-color: #e8eef5;"></td>
			<td style="background-color: #e8eef5; text-align: right;"></td>
		</tr>
		<tr>
			<td style="background-color: #e8eef5;">'.$formText_dueDate.'</td>
			<td style="background-color: #e8eef5;">'.$dateExpireShow.'</td>
			<td style="background-color: #e8eef5;">'.$formText_total.'</td>
			<td style="background-color: #e8eef5; text-align: right;">'.number_format(floatval($ordersArray['totals']['total']),$decimalPlaces,',',' ').'</td>
		</tr>
		<tr>
			<td style="background-color: #e8eef5;">';

		if($v_settings['kidOnInvoice'] > 0 || $batchinvoicing_accountconfig['activate_not_update_kid_number']){
			$html3 .= '<b>'.$formText_kidnr.'</b></td><td style="background-color: #e8eef5;">';
		}else {
			$html3 .= '</td><td style="background-color: #e8eef5;">';
		}
		$html4 = '</td>
			<td style="background-color: #e8eef5;"><b>'.$formText_account.'</b></td>
			<td style="background-color: #e8eef5; text-align: right;">'.$bankAccountData['companyaccount'].'</td>
		</tr>';

		$html4 .= '</table>';
	}

	$html5 .= '
		<tr><td colspan="2"></td></tr>
		<tr><td colspan="2"></td></tr>
		<tr>
			<td colspan="2">
				<table cellspacing="0" cellpadding="0" border="0" width="480px; height: 10px;">
					<tr>
						<td height="20" width="'.($hasAnyDiscount ? '180' : '230').'" style="color: #999999; border-bottom:1px solid #000000;">'.($formText_text).'</td>
						<td height="20" width="30" style="color: #999999; border-bottom:1px solid #000000;"></td>
						<td height="20" width="70" style="color: #999999; text-align:right; border-bottom:1px solid #000000;">'.$formText_price.'</td>
						<td height="20" width="40" style="color: #999999; text-align:center; border-bottom:1px solid #000000;">'.$formText_amount.'</td>
						'.($hasAnyDiscount ? '<td height="20" width="50" style="color: #999999; text-align:center; border-bottom:1px solid #000000;">'.$formText_discount.'</td>' : '').'
						<td height="20" width="40" style="color: #999999; text-align:center; border-bottom:1px solid #000000;">'.$formText_Vat.'</td>
						<td height="20" width="70" style="color: #999999; text-align:right; border-bottom:1px solid #000000;">'.$formText_totalprice.'</td>
					</tr>
				</table>
				<table cellspacing="0" cellpadding="0" border="0" width="480px">
				<tr>
				<td width="'.($hasAnyDiscount ? '180' : '230').'"></td>
				<td width="30"></td>
				<td width="70"></td>
				<td width="40"></td>
				'.($hasAnyDiscount ? '<td width="50"></td>' : '').'
				<td width="40"></td>
				<td width="70"></td>
				</tr>';

				$sum = 0;
				foreach($ordersArray['list'] as $order){
					if($v_settings['invoice_template'] == 1 && $order['adminFee']){
						continue;
					}
					$decimalNumber = getMaxDecimalAmount($order['amount']);
					$html5.= '
					<tr><td>'.proc_rem_style($order['articleName']).'</td><td></td><td style="text-align:right;">'.proc_rem_style(number_format(floatval($order['pricePerPiece']),2,',',' ')).'</td><td style="text-align:center;">'.proc_rem_style(number_format(floatval($order['amount']),$decimalNumber,',',' ')).'</td>'.($hasAnyDiscount ? '<td style="text-align:center;">'.proc_rem_style(number_format(floatval($order['discountPercent']), 2, ',', ' ')).'%</td>' : '').'<td style="text-align:center;">'.proc_rem_style($order['vatPercentRate']).'%</td><td style="text-align:right;">'.proc_rem_style(number_format(floatval($order['priceTotal']),2,',',' ')).'</td></tr>';
				}


	if($v_settings['invoice_template'] != 1){
		$html5.= '
					<tr><td colspan="5"></td></tr>
					</table>
					<table cellspacing="0" cellpadding="0" border="0" width="480px">
						<tr><td style="border-top:1px solid #000000;"></td></tr>
					</table>
					<table cellspacing="0" cellpadding="0" border="0" width="100%">
					<tr><td width="240"></td><td width="240">
					<table cellspacing="0" cellpadding="0" border="0" width="240">
					<tr><td >'.$formText_sumNoVat.' '.$currentCurrencyDisplay.'</td><td style="text-align:right;">'.number_format(floatval($ordersArray['totals']['totalSum']),$decimalPlaces,',',' ').'</td></tr>
					<tr><td height="20" style="border-bottom:1px solid #000000;">'.$formText_vat.' '.$currentCurrencyDisplay.'</td><td style="text-align:right; border-bottom:1px solid #000000;">'.number_format(floatval($ordersArray['totals']['totalVat']),$decimalPlaces,',',' ').'</td></tr>
					<tr><td height="5"></td><td></td></tr>
					<tr><td height="20" style="vertical-align: middle; border-bottom:1px solid #000000;">'.$formText_total.' '.$currentCurrencyDisplay.'</td><td style="vertical-align: middle; text-align:right; border-bottom:1px solid #000000;">'.number_format(floatval($ordersArray['totals']['total']),$decimalPlaces,',',' ').'</td></tr>
					</table>
					</td></tr>
					<tr><td colspan="2"><br /><br /><br /><br />'.$v_settings['invoicebottomtext'].'</td></tr>
					</table>
				</td>
			</tr>
			</table>
		</div>';
	} else {
		$html5.= '
					<tr><td colspan="5"></td></tr>
					</table>
					<table cellspacing="0" cellpadding="0" border="0" width="480px">
						<tr><td style="border-top:1px solid #000000;"></td></tr>
					</table>
					<table cellspacing="0" cellpadding="0" border="0" width="480px">
					<tr><td><br /><br />'.$v_settings['invoicebottomtext'].'</td></tr>
					</table>
				</td>
			</tr>
			</table>
		</div>';
	}
	return array($html1,$html2,$html3,$html4,$html5);
}


function generateHtmlCreditor($ordersArray, $v_customer, $v_settings, $bankAccountData, $contantPersonLine,$s_reference,$s_delivery_date,$s_delivery_address, $customerIdToDisplay,$dateValShow,$dateExpireShow,$hasAnyDiscount,$currentCurrencyDisplay, $decimalPlaces, $v_proc_variables, $variables, $accountname, $successfully_drawn="", $processed_credit_card=""){
	global $o_main;
	global $_GET;
	$variables->developeraccess = 0;
	include(__DIR__."/../../../output/includes/readOutputLanguage.php");
	require_once(__DIR__.'/../../../output/fnc_getMaxDecimalAmount.php');

	$s_sql = "SELECT * FROM batch_invoicing_accountconfig";
	$o_query = $o_main->db->query($s_sql);
	$batchinvoicing_accountconfig = $o_query ? $o_query->row_array() : array();
	if($v_customer['useOwnInvoiceAdress']) {
		$s_cust_addr_prefix = 'ia';
		$customerAddress = 'own address';
		$customerAddress = $v_customer['iaStreet1']."<br />".(!empty($v_customer['iaStreet2']) ? $v_customer['iaStreet2'] . '<br />' : '').$v_customer['iaPostalNumber']." ".$v_customer['iaCity'] . "<br>" . $v_customer['iaCountry'];
	} else {
		$s_cust_addr_prefix = 'pa';
		$customerAddress = $v_customer['paStreet']."<br />".(!empty($v_customer['paStreet2']) ? $v_customer['paStreet2'] . '<br />' : '').$v_customer['paPostalNumber']." ".$v_customer['paCity'] . "<br>" . $v_customer['paCountry'];
	}
	$s_customer = trim($v_customer['name']." ".$v_customer['middlename']." ".$v_customer['lastname'])."<br />".$customerAddress;

	$s_invoice_text = $v_settings['companyname']." <br />".$v_settings['companypostalbox']." <br />".$v_settings['companyzipcode']." ".$v_settings['companypostalplace']." <br />".$v_settings['companyCountry'].
	" <br />".$formText_Email.": ".$v_settings['companyEmail']." <br />".$formText_phone.": ".$v_settings['companyphone']." <br />".$formText_orgNr.": ".$v_settings['companyorgnr']." ".$v_settings['extra_text_after_company_org_number'];/*.
	" <br />".$formText_iban.": ".$bankAccountData['companyiban']." <br />".$formText_swiftCode.": ".$bankAccountData['companyswift']."<br />".$formText_account.": ".$bankAccountData['companyaccount'];*/

	$html1 = '';
	$html2 = '';
	$html3 = '';
	$html4 = '';
	$html5 = '';
	if($ordersArray['totals']['total']  >= 0){
		$pdf_header_text = $formText_invoice_header;
	} else {
		$pdf_header_text = $formText_CreditInvoice_header;
	}
	$html1 = '
	<div>
		<table border="0" cellpadding="0" cellspacing="0">
	 <tr><td><br /><br /><br /><br /><br /><br />
	<br /></td></tr>
		<tr>
			<td width="250">'.($s_customer).'</td>
			<td width="240"><span style="font-size:46px;"><b>'.($pdf_header_text).'</b></span><br />'.($s_invoice_text).'</td>
		</tr>
		<tr><td colspan="2"></td></tr>
		<tr>
			<td>
				<table cellspacing="0" cellpadding="0" border="0" width="220">
					'.(!empty($contantPersonLine) ? '<tr><td>'.$formText_YourContactperson.': '.($contantPersonLine).'</td></tr>' : '').'
					'.(!empty($s_reference) ? '<tr><td>'.$formText_Reference.': '.($s_reference).'</td></tr>' : '').'
					'.(!empty($s_delivery_date) ? '<tr><td>'.$formText_DeliveryDate.': '.($s_delivery_date).'</td></tr>' : '').'
					'.(!empty($s_delivery_address) ? '<tr><td>'.$formText_DeliveryAddress.': '.($s_delivery_address).'</td></tr>' : '').'
				</table>
			</td>
			<td>
				<table cellspacing="0" cellpadding="0" border="0" width="240">
				<tr><td>'.$formText_invoicenr.':</td><td style="text-align:right;">';
	$html2 = '
				</td></tr>';
				if($v_settings['kidOnInvoice'] > 0 || $batchinvoicing_accountconfig['activate_not_update_kid_number']){ $html3 .='<tr><td>'.$formText_kidnr.':</td><td style="text-align:right;">';}
	$html4 = '
				</td></tr>';
	$html5 = '
				<tr><td>'.$formText_customernr.':</td><td style="text-align:right;">'.($customerIdToDisplay).'</td></tr>
				<tr><td>'.$formText_date.':</td><td style="text-align:right;">'.$dateValShow.'</td></tr>
				</table>
			</td>
		</tr>
		<tr><td colspan="2"></td></tr>
		<tr><td colspan="2"></td></tr>
		<tr>
			<td colspan="2">
				<div style="border-top:1px solid #000000; border-bottom:1px solid #000000;">
				<table cellspacing="0" cellpadding="0" border="0" width="100%">
				<tr><td colspan="5"></td></tr>
				<tr><td colspan="5"></td></tr>
				<tr><td width="'.($hasAnyDiscount ? '190' : '240').'" style="font-weight:bold;">'.($formText_text).'</td><td width="30"></td><td width="70" style="font-weight:bold;">'.$formText_price.'</td><td width="40" style="font-weight:bold;">'.$formText_amount.'</td>'.($hasAnyDiscount ? '<td width="50" style="font-weight:bold;">'.$formText_discount.'</td>' : '').'<td width="40" style="font-weight:bold;">'.$formText_Vat.'</td><td width="70" style="font-weight:bold; text-align:right;">'.$formText_totalprice.'</td></tr>';

				$sum = 0;
				foreach($ordersArray['list'] as $order){
					$decimalNumber = getMaxDecimalAmount($order['amount']);
					$html5.= '
					<tr><td>'.proc_rem_style($order['articleName']).'</td><td></td><td>'.proc_rem_style(number_format(floatval($order['pricePerPiece']),2,',',' ')).'</td><td>'.proc_rem_style(number_format(floatval($order['amount']),$decimalNumber,',',' ')).'</td>'.($hasAnyDiscount ? '<td>'.proc_rem_style(number_format(floatval($order['discountPercent']), 2, ',', ' ')).'%</td>' : '').'<td>'.proc_rem_style($order['vatPercentRate']).'%</td><td style="text-align:right;">'.proc_rem_style(number_format(floatval($order['priceTotal']),2,',',' ')).'</td></tr>';
					}
				$deductedText = "";
				if($processed_credit_card){
					$deductedText = $formText_WasNotAbleToDrawFromYourCard.": ".$processed_credit_card;
					if($successfully_drawn){
						$deductedText = $formText_AmountWasSuccessfullyDeductedFromYourCard.": ".$processed_credit_card;
					}
				}

	$html5.= '
				<tr><td colspan="5"></td></tr>
				<tr><td colspan="5"></td></tr>
				</table>
				</div>
				<br />
				<table cellspacing="0" cellpadding="0" border="0" width="100%">
				<tr><td width="340"></td><td width="150">
				<table cellspacing="0" cellpadding="0" border="0" width="150">
				<tr><td>'.$formText_sumNoVat.' '.$currentCurrencyDisplay.'</td><td style="text-align:right;">'.number_format(floatval($ordersArray['totals']['totalSum']),$decimalPlaces,',',' ').'</td></tr>
				<tr><td style="border-bottom:1px solid #000000;">'.$formText_vat.' '.$currentCurrencyDisplay.'</td><td style="text-align:right; border-bottom:1px solid #000000;">'.number_format(floatval($ordersArray['totals']['totalVat']),$decimalPlaces,',',' ').'</td></tr>
				<tr><td style="border-bottom:1px solid #000000;">'.$formText_total.' '.$currentCurrencyDisplay.'</td><td style="text-align:right; border-bottom:1px solid #000000;">'.number_format(floatval($ordersArray['totals']['total']),$decimalPlaces,',',' ').'</td></tr>
				</table>
				</td></tr>
				<tr><td colspan="2">'.$deductedText.'<br /><br /><br /><br />'.$v_settings['invoicebottomtext'].'</td></tr>
				</table>
			</td>
		</tr>
		</table>
	</div>';
	return array($html1,$html2,$html3,$html4,$html5);
}


function create_pdf($filepath, $file, $files_attached_pdf, $newInvoiceNrOnInvoice, $invoicelogo, $v_settings, $accountname, $html, $template, $html_footer = ''){
	ob_end_clean();
	if(!class_exists("TCPDF"))
	{
		require_once(__DIR__."/../../../output/includes/tcpdf/config/lang/eng.php");
		require_once(__DIR__."/../../../output/includes/tcpdf/tcpdf.php");
	}
	if(!class_exists("MYPDF_invoice"))
	{
		class MYPDF_invoice extends TCPDF {
			protected $footer_html;
			protected $invoice_footer_logos;

			public function setCustomInfo($footer_html, $invoice_footer_logos) {
				$this->footer_html = $footer_html;
				$this->invoice_footer_logos = $invoice_footer_logos;
			}
			public function Header() {
			}
			// Page footer
			public function Footer() {
				$footer_html = $this->footer_html;
				$this->SetY(-45);
				// $this->writeHTML("<hr>", true, false, false, false, '');

				$this->setCellPaddings('', '', '', 3);
				$this->SetFont('dejavusans', '', 9);
				$this->writeHTML($footer_html, true, false, true, false, '');
				$invoice_footer_logos = $this->invoice_footer_logos;
				$footer_html_logos = "";
				foreach($invoice_footer_logos as $invoice_footer_logo) {
					// $this->Image(__DIR__."/../../../../../".$invoice_footer_logo[1][0], "", "", 0, 10, '', '', '', true, 300, 'L');
					$footer_html_logos .= '&nbsp;<img src="'.__DIR__."/../../../../../".$invoice_footer_logo[1][0].'" style="height: 10mm, width: auto;">&nbsp;<span style="width: 10px;"></span>';
				}
				$this->writeHTML($footer_html_logos, true, false, true, false, '');
			}
		}
	}

	// create new PDF document
	$pdf = new MYPDF_invoice(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'iso-8859-1', false);

	$pdf->SetCreator(PDF_CREATOR);
	$pdf->SetAuthor('ERP');
	$pdf->SetTitle('Invoice: '.$newInvoiceNrOnInvoice);
	$pdf->SetSubject('');
	$pdf->SetKeywords('');

	$pdf->setPrintHeader(false);
	$pdf->setPrintFooter(true);
	$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
	$pdf->SetMargins(20, 5, 10);
	$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
	$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
	$pdf->setLanguageArray($l);
	$pdf->SetFont('dejavusans', '', 9); //helvetica dejavusans
	if($v_settings['invoice_template'] == 1){
		$pdf->setCustomInfo($html_footer, json_decode($v_settings['invoice_footer_logos']));
	}
	$pdf->AddPage();
	$pdf->SetAutoPageBreak(false);
	//$pdf->Image($extraimagedir.''.$v_invoice_log[0][1][0] , 20, 5, 40, 15, '', '', '', true, 300);
	//
	if ($invoicelogo[0][1][0]) {
		$allowed = array('gif', 'png', 'jpg');
		$ext = pathinfo(mb_strtolower($invoicelogo[0][1][0]), PATHINFO_EXTENSION);
		if (in_array($ext, $allowed)) {
			$divider = 3;
			$logoWidth = (is_numeric($v_settings['invoicelogoWidth']) ? $v_settings['invoicelogoWidth'] : 100) / $divider;
			$logoPosX = (is_numeric($v_settings['invoicelogoPositionX']) ? $v_settings['invoicelogoPositionX'] : 0) / $divider + 17;
			$logoPosY = (is_numeric($v_settings['invoicelogoPositionY']) ? $v_settings['invoicelogoPositionY'] : 0) / $divider + 6;
			if($v_settings['invoice_template'] == 1){
				$pdf->Image(__DIR__."/../../../../../".$invoicelogo[0][1][0], $logoPosX, $logoPosY, $logoWidth, 0, '', '', '', true, 300, 'L');
			} else {
				$pdf->Image(__DIR__."/../../../../../".$invoicelogo[0][1][0], $logoPosX, $logoPosY, $logoWidth, 0, '', '', '', true, 300, 'C');
			}
		}
	}

	$pdf->writeHTML($html, true, false, true, false, '');

	if('Creditor' == $template)
	{
		$image_file = '/modules/BatchInvoicing/output/elementsOutput/getynet_pay_logo.png';
		$pdf->Image($image_file, 20, 270, 45, 0, '', '', '', true, 300);
		$pdf->SetTextColor(127);
		$pdf->MultiCell(0, 0, 'Getynet Pay håndterer betaling for denne fakturaen.', 0, 'L', 0, 1, 19, 280);
	}
	$pdf->lastPage();

	$pdf->Output($filepath.$file, 'F');//'FD');

	if(count($files_attached_pdf) > 0){
		//fix for special characters in shell command
		setlocale(LC_CTYPE, "en_US.UTF-8");

		// use setasign\Fpdi\Fpdi;
		// require_once(__DIR__."/../../../output/includes/fpdi2/Fpdi.php");
		// require_once(__DIR__."/../../../output/includes/fpdi/fpdi.php");
		copy($filepath.$file, $filepath."copy_".$file);
		$files = array($filepath."copy_".$file);
		$files= array_merge($files, $files_attached_pdf);

		$outputName = $filepath.$file;

		$cmd = "gs -q -dNOPAUSE -dBATCH -sDEVICE=pdfwrite -sOutputFile=$outputName ";
		//Add each pdf file to the end of the command
		foreach($files as $file_pdf) {
		    $cmd .= escapeshellarg($file_pdf)." ";
		}
		$cmd .= '-c "[ /Title (Invoice: '.$newInvoiceNrOnInvoice.') /DOCINFO pdfmark"';
		$result = shell_exec($cmd);

		// $merged_pdf = new Fpdi();
		// $merged_pdf->setPrintHeader(false);
		// $merged_pdf->setPrintFooter(false);
		// // iterate through the files
		// foreach ($files AS $fileItem) {
		// 	// get the page count
		// 	$pageCount = $merged_pdf->setSourceFile($fileItem);
		// 	// iterate through all pages
		// 	for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
		// 		// import a page
		// 		$templateId = $merged_pdf->importPage($pageNo, '/MediaBox');
		// 		// get the size of the imported page
		// 		$size = $merged_pdf->getTemplateSize($templateId);
		//
		// 		// create a page (landscape or portrait depending on the imported page size)
		// 		if ($size['w'] > $size['h']) {
		// 			$merged_pdf->AddPage('L', array($size['w'], $size['h']));
		// 		} else {
		// 			$merged_pdf->AddPage('P', array($size['w'], $size['h']));
		// 		}
		//
		// 		// use the imported page
		// 		$merged_pdf->useTemplate($templateId);
		// 	}
		// }
		// $merged_pdf->Output($filepath.$file, 'F');//'FD');
	}

}
