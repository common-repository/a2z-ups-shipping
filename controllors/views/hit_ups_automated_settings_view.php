<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
// delete_option('hit_ups_auto_main_settings');
wp_enqueue_script("jquery");
$error = $success =  '';

global $woocommerce;
global $wpdb;
$_carriers = array(
							//domestic
							'ups_12'                    => '3 Day Select',
							'ups_03'                    => 'Ground',
							'ups_02'                    => '2nd Day Air',
							'ups_59'                    => '2nd Day Air AM',
							'ups_01'                    => 'Next Day Air',
							'ups_13'                    => 'Next Day Air Saver',
							'ups_14'                    => 'Next Day Air Early AM',

							//international		
							'ups_11'                    => 'UPS Standard',
							'ups_07'                    => 'UPS Express',
							'ups_08'                    => 'UPS Expedited',
							'ups_54'                    => 'UPS Express Plus',
							'ups_65'                    => 'UPS Saver',
							'ups_96'                    => 'Worldwide Express Freight',
							'ups_08'                    => 'UPS ExpeditedSM',

							//Other services
							'ups_92'                    => 'SurePost Less than 1 lb',
							'ups_93'                    => 'SurePost 1 lb or Greater',
							'ups_94'                    => 'SurePost BPM',
							'ups_95'                    => 'SurePost Media',
							'ups_82'                    => 'Today Standard',
							"ups_83"					=> "UPS Today Dedicated Courier",
							"ups_84"					=> "UPS Today Intercity",
							"ups_85"				    => "UPS Today Express",
							"ups_86" 					=> "UPS Today Express Saver",
							'ups_M2'                    => 'First Class Mail',
							'ups_M3'                    => 'Priority Mail',
							'ups_M4'                    => 'Expedited Mail Innovations',
							'ups_M5'                    => 'Priority Mail Innovations',
							'ups_M6'                    => 'EconomyMail Innovations',
							'ups_70'                    => 'Access Point Economy',
							
						);
	$Domestic_service = array(
		//domestic
		'ups_12'                    => '3 Day Select',
		'ups_03'                    => 'Ground',
		'ups_02'                    => '2nd Day Air',
		'ups_59'                    => '2nd Day Air AM',
		'ups_01'                    => 'Next Day Air',
		'ups_13'                    => 'Next Day Air Saver',
		'ups_14'                    => 'Next Day Air Early AM',
	);
	$international_service = array(
		//international		
		'ups_11'                    => 'UPS Standard',
		'ups_07'                    => 'UPS Express',
		'ups_08'                    => 'UPS Expedited',
		'ups_54'                    => 'UPS Express Plus',
		'ups_65'                    => 'UPS Saver',
		'ups_96'                    => 'Worldwide Express Freight',
		'ups_08'                    => 'UPS ExpeditedSM',

		//Other services
		'ups_92'                    => 'SurePost Less than 1 lb',
		'ups_93'                    => 'SurePost 1 lb or Greater',
		'ups_94'                    => 'SurePost BPM',
		'ups_95'                    => 'SurePost Media',
		'ups_82'                    => 'Today Standard',
		"ups_83"					=> "UPS Today Dedicated Courier",
		"ups_84"					=> "UPS Today Intercity",
		"ups_85"				    => "UPS Today Express",
		"ups_86" 					=> "UPS Today Express Saver",
		'ups_M2'                    => 'First Class Mail',
		'ups_M3'                    => 'Priority Mail',
		'ups_M4'                    => 'Expedited Mail Innovations',
		'ups_M5'                    => 'Priority Mail Innovations',
		'ups_M6'                    => 'EconomyMail Innovations',
		'ups_70'                    => 'Access Point Economy',

	);
	function sanitize_array($string)
	{
		foreach ($string as $k => $v) {
			if (is_array($v)) {
				$string[$k] = sanitize_array($v);
			} else {
				$string[$k] = sanitize_text_field($v);
			}
		}

		return $string;
	}
$print_size = array("GIF" => "GIF");
$countires =  array(
			'AF' => 'Afghanistan',
			'AX' => 'Aland Islands',
			'AL' => 'Albania',
			'DZ' => 'Algeria',
			'AS' => 'American Samoa',
			'AD' => 'Andorra',
			'AO' => 'Angola',
			'AI' => 'Anguilla',
			'AQ' => 'Antarctica',
			'AG' => 'Antigua and Barbuda',
			'AR' => 'Argentina',
			'AM' => 'Armenia',
			'AW' => 'Aruba',
			'AU' => 'Australia',
			'AT' => 'Austria',
			'AZ' => 'Azerbaijan',
			'BS' => 'Bahamas',
			'BH' => 'Bahrain',
			'BD' => 'Bangladesh',
			'BB' => 'Barbados',
			'BY' => 'Belarus',
			'BE' => 'Belgium',
			'BZ' => 'Belize',
			'BJ' => 'Benin',
			'BM' => 'Bermuda',
			'BT' => 'Bhutan',
			'BO' => 'Bolivia',
			'BQ' => 'Bonaire, Saint Eustatius and Saba',
			'BA' => 'Bosnia and Herzegovina',
			'BW' => 'Botswana',
			'BV' => 'Bouvet Island',
			'BR' => 'Brazil',
			'IO' => 'British Indian Ocean Territory',
			'VG' => 'British Virgin Islands',
			'BN' => 'Brunei',
			'BG' => 'Bulgaria',
			'BF' => 'Burkina Faso',
			'BI' => 'Burundi',
			'KH' => 'Cambodia',
			'CM' => 'Cameroon',
			'CA' => 'Canada',
			'CV' => 'Cape Verde',
			'KY' => 'Cayman Islands',
			'CF' => 'Central African Republic',
			'TD' => 'Chad',
			'CL' => 'Chile',
			'CN' => 'China',
			'CX' => 'Christmas Island',
			'CC' => 'Cocos Islands',
			'CO' => 'Colombia',
			'KM' => 'Comoros',
			'CK' => 'Cook Islands',
			'CR' => 'Costa Rica',
			'HR' => 'Croatia',
			'CU' => 'Cuba',
			'CW' => 'Curacao',
			'CY' => 'Cyprus',
			'CZ' => 'Czech Republic',
			'CD' => 'Democratic Republic of the Congo',
			'DK' => 'Denmark',
			'DJ' => 'Djibouti',
			'DM' => 'Dominica',
			'DO' => 'Dominican Republic',
			'TL' => 'East Timor',
			'EC' => 'Ecuador',
			'EG' => 'Egypt',
			'SV' => 'El Salvador',
			'GQ' => 'Equatorial Guinea',
			'ER' => 'Eritrea',
			'EE' => 'Estonia',
			'ET' => 'Ethiopia',
			'FK' => 'Falkland Islands',
			'FO' => 'Faroe Islands',
			'FJ' => 'Fiji',
			'FI' => 'Finland',
			'FR' => 'France',
			'GF' => 'French Guiana',
			'PF' => 'French Polynesia',
			'TF' => 'French Southern Territories',
			'GA' => 'Gabon',
			'GM' => 'Gambia',
			'GE' => 'Georgia',
			'DE' => 'Germany',
			'GH' => 'Ghana',
			'GI' => 'Gibraltar',
			'GR' => 'Greece',
			'GL' => 'Greenland',
			'GD' => 'Grenada',
			'GP' => 'Guadeloupe',
			'GU' => 'Guam',
			'GT' => 'Guatemala',
			'GG' => 'Guernsey',
			'GN' => 'Guinea',
			'GW' => 'Guinea-Bissau',
			'GY' => 'Guyana',
			'HT' => 'Haiti',
			'HM' => 'Heard Island and McDonald Islands',
			'HN' => 'Honduras',
			'HK' => 'Hong Kong',
			'HU' => 'Hungary',
			'IS' => 'Iceland',
			'IN' => 'India',
			'ID' => 'Indonesia',
			'IR' => 'Iran',
			'IQ' => 'Iraq',
			'IE' => 'Ireland',
			'IM' => 'Isle of Man',
			'IL' => 'Israel',
			'IT' => 'Italy',
			'CI' => 'Ivory Coast',
			'JM' => 'Jamaica',
			'JP' => 'Japan',
			'JE' => 'Jersey',
			'JO' => 'Jordan',
			'KZ' => 'Kazakhstan',
			'KE' => 'Kenya',
			'KI' => 'Kiribati',
			'XK' => 'Kosovo',
			'KW' => 'Kuwait',
			'KG' => 'Kyrgyzstan',
			'LA' => 'Laos',
			'LV' => 'Latvia',
			'LB' => 'Lebanon',
			'LS' => 'Lesotho',
			'LR' => 'Liberia',
			'LY' => 'Libya',
			'LI' => 'Liechtenstein',
			'LT' => 'Lithuania',
			'LU' => 'Luxembourg',
			'MO' => 'Macao',
			'MK' => 'Macedonia',
			'MG' => 'Madagascar',
			'MW' => 'Malawi',
			'MY' => 'Malaysia',
			'MV' => 'Maldives',
			'ML' => 'Mali',
			'MT' => 'Malta',
			'MH' => 'Marshall Islands',
			'MQ' => 'Martinique',
			'MR' => 'Mauritania',
			'MU' => 'Mauritius',
			'YT' => 'Mayotte',
			'MX' => 'Mexico',
			'FM' => 'Micronesia',
			'MD' => 'Moldova',
			'MC' => 'Monaco',
			'MN' => 'Mongolia',
			'ME' => 'Montenegro',
			'MS' => 'Montserrat',
			'MA' => 'Morocco',
			'MZ' => 'Mozambique',
			'MM' => 'Myanmar',
			'NA' => 'Namibia',
			'NR' => 'Nauru',
			'NP' => 'Nepal',
			'NL' => 'Netherlands',
			'NC' => 'New Caledonia',
			'NZ' => 'New Zealand',
			'NI' => 'Nicaragua',
			'NE' => 'Niger',
			'NG' => 'Nigeria',
			'NU' => 'Niue',
			'NF' => 'Norfolk Island',
			'KP' => 'North Korea',
			'MP' => 'Northern Mariana Islands',
			'NO' => 'Norway',
			'OM' => 'Oman',
			'PK' => 'Pakistan',
			'PW' => 'Palau',
			'PS' => 'Palestinian Territory',
			'PA' => 'Panama',
			'PG' => 'Papua New Guinea',
			'PY' => 'Paraguay',
			'PE' => 'Peru',
			'PH' => 'Philippines',
			'PN' => 'Pitcairn',
			'PL' => 'Poland',
			'PT' => 'Portugal',
			'PR' => 'Puerto Rico',
			'QA' => 'Qatar',
			'CG' => 'Republic of the Congo',
			'RE' => 'Reunion',
			'RO' => 'Romania',
			'RU' => 'Russia',
			'RW' => 'Rwanda',
			'BL' => 'Saint Barthelemy',
			'SH' => 'Saint Helena',
			'KN' => 'Saint Kitts and Nevis',
			'LC' => 'Saint Lucia',
			'MF' => 'Saint Martin',
			'PM' => 'Saint Pierre and Miquelon',
			'VC' => 'Saint Vincent and the Grenadines',
			'WS' => 'Samoa',
			'SM' => 'San Marino',
			'ST' => 'Sao Tome and Principe',
			'SA' => 'Saudi Arabia',
			'SN' => 'Senegal',
			'RS' => 'Serbia',
			'SC' => 'Seychelles',
			'SL' => 'Sierra Leone',
			'SG' => 'Singapore',
			'SX' => 'Sint Maarten',
			'SK' => 'Slovakia',
			'SI' => 'Slovenia',
			'SB' => 'Solomon Islands',
			'SO' => 'Somalia',
			'ZA' => 'South Africa',
			'GS' => 'South Georgia and the South Sandwich Islands',
			'KR' => 'South Korea',
			'SS' => 'South Sudan',
			'ES' => 'Spain',
			'LK' => 'Sri Lanka',
			'SD' => 'Sudan',
			'SR' => 'Suriname',
			'SJ' => 'Svalbard and Jan Mayen',
			'SZ' => 'Swaziland',
			'SE' => 'Sweden',
			'CH' => 'Switzerland',
			'SY' => 'Syria',
			'TW' => 'Taiwan',
			'TJ' => 'Tajikistan',
			'TZ' => 'Tanzania',
			'TH' => 'Thailand',
			'TG' => 'Togo',
			'TK' => 'Tokelau',
			'TO' => 'Tonga',
			'TT' => 'Trinidad and Tobago',
			'TN' => 'Tunisia',
			'TR' => 'Turkey',
			'TM' => 'Turkmenistan',
			'TC' => 'Turks and Caicos Islands',
			'TV' => 'Tuvalu',
			'VI' => 'U.S. Virgin Islands',
			'UG' => 'Uganda',
			'UA' => 'Ukraine',
			'AE' => 'United Arab Emirates',
			'GB' => 'United Kingdom',
			'US' => 'United States',
			'UM' => 'United States Minor Outlying Islands',
			'UY' => 'Uruguay',
			'UZ' => 'Uzbekistan',
			'VU' => 'Vanuatu',
			'VA' => 'Vatican',
			'VE' => 'Venezuela',
			'VN' => 'Vietnam',
			'WF' => 'Wallis and Futuna',
			'EH' => 'Western Sahara',
			'YE' => 'Yemen',
			'ZM' => 'Zambia',
			'ZW' => 'Zimbabwe',
		);
$duty_payment_type = array('S' =>'Shipper','R' =>'Recipient');
		$value = array();
		$value['AD'] = array('region' => 'EU', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['AE'] = array('region' => 'AP', 'currency' =>'AED', 'weight' => 'KG_CM');
		$value['AF'] = array('region' => 'AP', 'currency' =>'AFN', 'weight' => 'KG_CM');
		$value['AG'] = array('region' => 'AM', 'currency' =>'XCD', 'weight' => 'LB_IN');
		$value['AI'] = array('region' => 'AM', 'currency' =>'XCD', 'weight' => 'LB_IN');
		$value['AL'] = array('region' => 'AP', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['AM'] = array('region' => 'AP', 'currency' =>'AMD', 'weight' => 'KG_CM');
		$value['AN'] = array('region' => 'AM', 'currency' =>'ANG', 'weight' => 'KG_CM');
		$value['AO'] = array('region' => 'AP', 'currency' =>'AOA', 'weight' => 'KG_CM');
		$value['AR'] = array('region' => 'AM', 'currency' =>'ARS', 'weight' => 'KG_CM');
		$value['AS'] = array('region' => 'AM', 'currency' =>'USD', 'weight' => 'LB_IN');
		$value['AT'] = array('region' => 'EU', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['AU'] = array('region' => 'AP', 'currency' =>'AUD', 'weight' => 'KG_CM');
		$value['AW'] = array('region' => 'AM', 'currency' =>'AWG', 'weight' => 'LB_IN');
		$value['AZ'] = array('region' => 'AM', 'currency' =>'AZN', 'weight' => 'KG_CM');
		$value['AZ'] = array('region' => 'AM', 'currency' =>'AZN', 'weight' => 'KG_CM');
		$value['GB'] = array('region' => 'EU', 'currency' =>'GBP', 'weight' => 'KG_CM');
		$value['BA'] = array('region' => 'AP', 'currency' =>'BAM', 'weight' => 'KG_CM');
		$value['BB'] = array('region' => 'AM', 'currency' =>'BBD', 'weight' => 'LB_IN');
		$value['BD'] = array('region' => 'AP', 'currency' =>'BDT', 'weight' => 'KG_CM');
		$value['BE'] = array('region' => 'EU', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['BF'] = array('region' => 'AP', 'currency' =>'XOF', 'weight' => 'KG_CM');
		$value['BG'] = array('region' => 'EU', 'currency' =>'BGN', 'weight' => 'KG_CM');
		$value['BH'] = array('region' => 'AP', 'currency' =>'BHD', 'weight' => 'KG_CM');
		$value['BI'] = array('region' => 'AP', 'currency' =>'BIF', 'weight' => 'KG_CM');
		$value['BJ'] = array('region' => 'AP', 'currency' =>'XOF', 'weight' => 'KG_CM');
		$value['BM'] = array('region' => 'AM', 'currency' =>'BMD', 'weight' => 'LB_IN');
		$value['BN'] = array('region' => 'AP', 'currency' =>'BND', 'weight' => 'KG_CM');
		$value['BO'] = array('region' => 'AM', 'currency' =>'BOB', 'weight' => 'KG_CM');
		$value['BR'] = array('region' => 'AM', 'currency' =>'BRL', 'weight' => 'KG_CM');
		$value['BS'] = array('region' => 'AM', 'currency' =>'BSD', 'weight' => 'LB_IN');
		$value['BT'] = array('region' => 'AP', 'currency' =>'BTN', 'weight' => 'KG_CM');
		$value['BW'] = array('region' => 'AP', 'currency' =>'BWP', 'weight' => 'KG_CM');
		$value['BY'] = array('region' => 'AP', 'currency' =>'BYR', 'weight' => 'KG_CM');
		$value['BZ'] = array('region' => 'AM', 'currency' =>'BZD', 'weight' => 'KG_CM');
		$value['CA'] = array('region' => 'AM', 'currency' =>'CAD', 'weight' => 'LB_IN');
		$value['CF'] = array('region' => 'AP', 'currency' =>'XAF', 'weight' => 'KG_CM');
		$value['CG'] = array('region' => 'AP', 'currency' =>'XAF', 'weight' => 'KG_CM');
		$value['CH'] = array('region' => 'EU', 'currency' =>'CHF', 'weight' => 'KG_CM');
		$value['CI'] = array('region' => 'AP', 'currency' =>'XOF', 'weight' => 'KG_CM');
		$value['CK'] = array('region' => 'AP', 'currency' =>'NZD', 'weight' => 'KG_CM');
		$value['CL'] = array('region' => 'AM', 'currency' =>'CLP', 'weight' => 'KG_CM');
		$value['CM'] = array('region' => 'AP', 'currency' =>'XAF', 'weight' => 'KG_CM');
		$value['CN'] = array('region' => 'AP', 'currency' =>'CNY', 'weight' => 'KG_CM');
		$value['CO'] = array('region' => 'AM', 'currency' =>'COP', 'weight' => 'KG_CM');
		$value['CR'] = array('region' => 'AM', 'currency' =>'CRC', 'weight' => 'KG_CM');
		$value['CU'] = array('region' => 'AM', 'currency' =>'CUC', 'weight' => 'KG_CM');
		$value['CV'] = array('region' => 'AP', 'currency' =>'CVE', 'weight' => 'KG_CM');
		$value['CY'] = array('region' => 'AP', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['CZ'] = array('region' => 'EU', 'currency' =>'CZF', 'weight' => 'KG_CM');
		$value['DE'] = array('region' => 'AP', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['DJ'] = array('region' => 'EU', 'currency' =>'DJF', 'weight' => 'KG_CM');
		$value['DK'] = array('region' => 'AM', 'currency' =>'DKK', 'weight' => 'KG_CM');
		$value['DM'] = array('region' => 'AM', 'currency' =>'XCD', 'weight' => 'LB_IN');
		$value['DO'] = array('region' => 'AP', 'currency' =>'DOP', 'weight' => 'LB_IN');
		$value['DZ'] = array('region' => 'AM', 'currency' =>'DZD', 'weight' => 'KG_CM');
		$value['EC'] = array('region' => 'EU', 'currency' =>'USD', 'weight' => 'KG_CM');
		$value['EE'] = array('region' => 'AP', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['EG'] = array('region' => 'AP', 'currency' =>'EGP', 'weight' => 'KG_CM');
		$value['ER'] = array('region' => 'EU', 'currency' =>'ERN', 'weight' => 'KG_CM');
		$value['ES'] = array('region' => 'AP', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['ET'] = array('region' => 'AU', 'currency' =>'ETB', 'weight' => 'KG_CM');
		$value['FI'] = array('region' => 'AP', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['FJ'] = array('region' => 'AP', 'currency' =>'FJD', 'weight' => 'KG_CM');
		$value['FK'] = array('region' => 'AM', 'currency' =>'GBP', 'weight' => 'KG_CM');
		$value['FM'] = array('region' => 'AM', 'currency' =>'USD', 'weight' => 'LB_IN');
		$value['FO'] = array('region' => 'AM', 'currency' =>'DKK', 'weight' => 'KG_CM');
		$value['FR'] = array('region' => 'EU', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['GA'] = array('region' => 'AP', 'currency' =>'XAF', 'weight' => 'KG_CM');
		$value['GB'] = array('region' => 'EU', 'currency' =>'GBP', 'weight' => 'KG_CM');
		$value['GD'] = array('region' => 'AM', 'currency' =>'XCD', 'weight' => 'LB_IN');
		$value['GE'] = array('region' => 'AM', 'currency' =>'GEL', 'weight' => 'KG_CM');
		$value['GF'] = array('region' => 'AM', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['GG'] = array('region' => 'AM', 'currency' =>'GBP', 'weight' => 'KG_CM');
		$value['GH'] = array('region' => 'AP', 'currency' =>'GBS', 'weight' => 'KG_CM');
		$value['GI'] = array('region' => 'AM', 'currency' =>'GBP', 'weight' => 'KG_CM');
		$value['GL'] = array('region' => 'AM', 'currency' =>'DKK', 'weight' => 'KG_CM');
		$value['GM'] = array('region' => 'AP', 'currency' =>'GMD', 'weight' => 'KG_CM');
		$value['GN'] = array('region' => 'AP', 'currency' =>'GNF', 'weight' => 'KG_CM');
		$value['GP'] = array('region' => 'AM', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['GQ'] = array('region' => 'AP', 'currency' =>'XAF', 'weight' => 'KG_CM');
		$value['GR'] = array('region' => 'EU', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['GT'] = array('region' => 'AM', 'currency' =>'GTQ', 'weight' => 'KG_CM');
		$value['GU'] = array('region' => 'AM', 'currency' =>'USD', 'weight' => 'LB_IN');
		$value['GW'] = array('region' => 'AP', 'currency' =>'XOF', 'weight' => 'KG_CM');
		$value['GY'] = array('region' => 'AP', 'currency' =>'GYD', 'weight' => 'LB_IN');
		$value['HK'] = array('region' => 'AM', 'currency' =>'HKD', 'weight' => 'KG_CM');
		$value['HN'] = array('region' => 'AM', 'currency' =>'HNL', 'weight' => 'KG_CM');
		$value['HR'] = array('region' => 'AP', 'currency' =>'HRK', 'weight' => 'KG_CM');
		$value['HT'] = array('region' => 'AM', 'currency' =>'HTG', 'weight' => 'LB_IN');
		$value['HU'] = array('region' => 'EU', 'currency' =>'HUF', 'weight' => 'KG_CM');
		$value['IC'] = array('region' => 'EU', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['ID'] = array('region' => 'AP', 'currency' =>'IDR', 'weight' => 'KG_CM');
		$value['IE'] = array('region' => 'EU', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['IL'] = array('region' => 'AP', 'currency' =>'ILS', 'weight' => 'KG_CM');
		$value['IN'] = array('region' => 'AP', 'currency' =>'INR', 'weight' => 'KG_CM');
		$value['IQ'] = array('region' => 'AP', 'currency' =>'IQD', 'weight' => 'KG_CM');
		$value['IR'] = array('region' => 'AP', 'currency' =>'IRR', 'weight' => 'KG_CM');
		$value['IS'] = array('region' => 'EU', 'currency' =>'ISK', 'weight' => 'KG_CM');
		$value['IT'] = array('region' => 'EU', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['JE'] = array('region' => 'AM', 'currency' =>'GBP', 'weight' => 'KG_CM');
		$value['JM'] = array('region' => 'AM', 'currency' =>'JMD', 'weight' => 'KG_CM');
		$value['JO'] = array('region' => 'AP', 'currency' =>'JOD', 'weight' => 'KG_CM');
		$value['JP'] = array('region' => 'AP', 'currency' =>'JPY', 'weight' => 'KG_CM');
		$value['KE'] = array('region' => 'AP', 'currency' =>'KES', 'weight' => 'KG_CM');
		$value['KG'] = array('region' => 'AP', 'currency' =>'KGS', 'weight' => 'KG_CM');
		$value['KH'] = array('region' => 'AP', 'currency' =>'KHR', 'weight' => 'KG_CM');
		$value['KI'] = array('region' => 'AP', 'currency' =>'AUD', 'weight' => 'KG_CM');
		$value['KM'] = array('region' => 'AP', 'currency' =>'KMF', 'weight' => 'KG_CM');
		$value['KN'] = array('region' => 'AM', 'currency' =>'XCD', 'weight' => 'LB_IN');
		$value['KP'] = array('region' => 'AP', 'currency' =>'KPW', 'weight' => 'LB_IN');
		$value['KR'] = array('region' => 'AP', 'currency' =>'KRW', 'weight' => 'KG_CM');
		$value['KV'] = array('region' => 'AM', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['KW'] = array('region' => 'AP', 'currency' =>'KWD', 'weight' => 'KG_CM');
		$value['KY'] = array('region' => 'AM', 'currency' =>'KYD', 'weight' => 'KG_CM');
		$value['KZ'] = array('region' => 'AP', 'currency' =>'KZF', 'weight' => 'LB_IN');
		$value['LA'] = array('region' => 'AP', 'currency' =>'LAK', 'weight' => 'KG_CM');
		$value['LB'] = array('region' => 'AP', 'currency' =>'USD', 'weight' => 'KG_CM');
		$value['LC'] = array('region' => 'AM', 'currency' =>'XCD', 'weight' => 'KG_CM');
		$value['LI'] = array('region' => 'AM', 'currency' =>'CHF', 'weight' => 'LB_IN');
		$value['LK'] = array('region' => 'AP', 'currency' =>'LKR', 'weight' => 'KG_CM');
		$value['LR'] = array('region' => 'AP', 'currency' =>'LRD', 'weight' => 'KG_CM');
		$value['LS'] = array('region' => 'AP', 'currency' =>'LSL', 'weight' => 'KG_CM');
		$value['LT'] = array('region' => 'EU', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['LU'] = array('region' => 'EU', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['LV'] = array('region' => 'EU', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['LY'] = array('region' => 'AP', 'currency' =>'LYD', 'weight' => 'KG_CM');
		$value['MA'] = array('region' => 'AP', 'currency' =>'MAD', 'weight' => 'KG_CM');
		$value['MC'] = array('region' => 'AM', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['MD'] = array('region' => 'AP', 'currency' =>'MDL', 'weight' => 'KG_CM');
		$value['ME'] = array('region' => 'AM', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['MG'] = array('region' => 'AP', 'currency' =>'MGA', 'weight' => 'KG_CM');
		$value['MH'] = array('region' => 'AM', 'currency' =>'USD', 'weight' => 'LB_IN');
		$value['MK'] = array('region' => 'AP', 'currency' =>'MKD', 'weight' => 'KG_CM');
		$value['ML'] = array('region' => 'AP', 'currency' =>'COF', 'weight' => 'KG_CM');
		$value['MM'] = array('region' => 'AP', 'currency' =>'USD', 'weight' => 'KG_CM');
		$value['MN'] = array('region' => 'AP', 'currency' =>'MNT', 'weight' => 'KG_CM');
		$value['MO'] = array('region' => 'AP', 'currency' =>'MOP', 'weight' => 'KG_CM');
		$value['MP'] = array('region' => 'AM', 'currency' =>'USD', 'weight' => 'LB_IN');
		$value['MQ'] = array('region' => 'AM', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['MR'] = array('region' => 'AP', 'currency' =>'MRO', 'weight' => 'KG_CM');
		$value['MS'] = array('region' => 'AM', 'currency' =>'XCD', 'weight' => 'LB_IN');
		$value['MT'] = array('region' => 'AP', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['MU'] = array('region' => 'AP', 'currency' =>'MUR', 'weight' => 'KG_CM');
		$value['MV'] = array('region' => 'AP', 'currency' =>'MVR', 'weight' => 'KG_CM');
		$value['MW'] = array('region' => 'AP', 'currency' =>'MWK', 'weight' => 'KG_CM');
		$value['MX'] = array('region' => 'AM', 'currency' =>'MXN', 'weight' => 'KG_CM');
		$value['MY'] = array('region' => 'AP', 'currency' =>'MYR', 'weight' => 'KG_CM');
		$value['MZ'] = array('region' => 'AP', 'currency' =>'MZN', 'weight' => 'KG_CM');
		$value['NA'] = array('region' => 'AP', 'currency' =>'NAD', 'weight' => 'KG_CM');
		$value['NC'] = array('region' => 'AP', 'currency' =>'XPF', 'weight' => 'KG_CM');
		$value['NE'] = array('region' => 'AP', 'currency' =>'XOF', 'weight' => 'KG_CM');
		$value['NG'] = array('region' => 'AP', 'currency' =>'NGN', 'weight' => 'KG_CM');
		$value['NI'] = array('region' => 'AM', 'currency' =>'NIO', 'weight' => 'KG_CM');
		$value['NL'] = array('region' => 'EU', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['NO'] = array('region' => 'EU', 'currency' =>'NOK', 'weight' => 'KG_CM');
		$value['NP'] = array('region' => 'AP', 'currency' =>'NPR', 'weight' => 'KG_CM');
		$value['NR'] = array('region' => 'AP', 'currency' =>'AUD', 'weight' => 'KG_CM');
		$value['NU'] = array('region' => 'AP', 'currency' =>'NZD', 'weight' => 'KG_CM');
		$value['NZ'] = array('region' => 'AP', 'currency' =>'NZD', 'weight' => 'KG_CM');
		$value['OM'] = array('region' => 'AP', 'currency' =>'OMR', 'weight' => 'KG_CM');
		$value['PA'] = array('region' => 'AM', 'currency' =>'USD', 'weight' => 'KG_CM');
		$value['PE'] = array('region' => 'AM', 'currency' =>'PEN', 'weight' => 'KG_CM');
		$value['PF'] = array('region' => 'AP', 'currency' =>'XPF', 'weight' => 'KG_CM');
		$value['PG'] = array('region' => 'AP', 'currency' =>'PGK', 'weight' => 'KG_CM');
		$value['PH'] = array('region' => 'AP', 'currency' =>'PHP', 'weight' => 'KG_CM');
		$value['PK'] = array('region' => 'AP', 'currency' =>'PKR', 'weight' => 'KG_CM');
		$value['PL'] = array('region' => 'EU', 'currency' =>'PLN', 'weight' => 'KG_CM');
		$value['PR'] = array('region' => 'AM', 'currency' =>'USD', 'weight' => 'LB_IN');
		$value['PT'] = array('region' => 'EU', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['PW'] = array('region' => 'AM', 'currency' =>'USD', 'weight' => 'KG_CM');
		$value['PY'] = array('region' => 'AM', 'currency' =>'PYG', 'weight' => 'KG_CM');
		$value['QA'] = array('region' => 'AP', 'currency' =>'QAR', 'weight' => 'KG_CM');
		$value['RE'] = array('region' => 'AP', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['RO'] = array('region' => 'EU', 'currency' =>'RON', 'weight' => 'KG_CM');
		$value['RS'] = array('region' => 'AP', 'currency' =>'RSD', 'weight' => 'KG_CM');
		$value['RU'] = array('region' => 'AP', 'currency' =>'RUB', 'weight' => 'KG_CM');
		$value['RW'] = array('region' => 'AP', 'currency' =>'RWF', 'weight' => 'KG_CM');
		$value['SA'] = array('region' => 'AP', 'currency' =>'SAR', 'weight' => 'KG_CM');
		$value['SB'] = array('region' => 'AP', 'currency' =>'SBD', 'weight' => 'KG_CM');
		$value['SC'] = array('region' => 'AP', 'currency' =>'SCR', 'weight' => 'KG_CM');
		$value['SD'] = array('region' => 'AP', 'currency' =>'SDG', 'weight' => 'KG_CM');
		$value['SE'] = array('region' => 'EU', 'currency' =>'SEK', 'weight' => 'KG_CM');
		$value['SG'] = array('region' => 'AP', 'currency' =>'SGD', 'weight' => 'KG_CM');
		$value['SH'] = array('region' => 'AP', 'currency' =>'SHP', 'weight' => 'KG_CM');
		$value['SI'] = array('region' => 'EU', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['SK'] = array('region' => 'EU', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['SL'] = array('region' => 'AP', 'currency' =>'SLL', 'weight' => 'KG_CM');
		$value['SM'] = array('region' => 'EU', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['SN'] = array('region' => 'AP', 'currency' =>'XOF', 'weight' => 'KG_CM');
		$value['SO'] = array('region' => 'AM', 'currency' =>'SOS', 'weight' => 'KG_CM');
		$value['SR'] = array('region' => 'AM', 'currency' =>'SRD', 'weight' => 'KG_CM');
		$value['SS'] = array('region' => 'AP', 'currency' =>'SSP', 'weight' => 'KG_CM');
		$value['ST'] = array('region' => 'AP', 'currency' =>'STD', 'weight' => 'KG_CM');
		$value['SV'] = array('region' => 'AM', 'currency' =>'USD', 'weight' => 'KG_CM');
		$value['SY'] = array('region' => 'AP', 'currency' =>'SYP', 'weight' => 'KG_CM');
		$value['SZ'] = array('region' => 'AP', 'currency' =>'SZL', 'weight' => 'KG_CM');
		$value['TC'] = array('region' => 'AM', 'currency' =>'USD', 'weight' => 'LB_IN');
		$value['TD'] = array('region' => 'AP', 'currency' =>'XAF', 'weight' => 'KG_CM');
		$value['TG'] = array('region' => 'AP', 'currency' =>'XOF', 'weight' => 'KG_CM');
		$value['TH'] = array('region' => 'AP', 'currency' =>'THB', 'weight' => 'KG_CM');
		$value['TJ'] = array('region' => 'AP', 'currency' =>'TJS', 'weight' => 'KG_CM');
		$value['TL'] = array('region' => 'AP', 'currency' =>'USD', 'weight' => 'KG_CM');
		$value['TN'] = array('region' => 'AP', 'currency' =>'TND', 'weight' => 'KG_CM');
		$value['TO'] = array('region' => 'AP', 'currency' =>'TOP', 'weight' => 'KG_CM');
		$value['TR'] = array('region' => 'AP', 'currency' =>'TRY', 'weight' => 'KG_CM');
		$value['TT'] = array('region' => 'AM', 'currency' =>'TTD', 'weight' => 'LB_IN');
		$value['TV'] = array('region' => 'AP', 'currency' =>'AUD', 'weight' => 'KG_CM');
		$value['TW'] = array('region' => 'AP', 'currency' =>'TWD', 'weight' => 'KG_CM');
		$value['TZ'] = array('region' => 'AP', 'currency' =>'TZS', 'weight' => 'KG_CM');
		$value['UA'] = array('region' => 'AP', 'currency' =>'UAH', 'weight' => 'KG_CM');
		$value['UG'] = array('region' => 'AP', 'currency' =>'USD', 'weight' => 'KG_CM');
		$value['US'] = array('region' => 'AM', 'currency' =>'USD', 'weight' => 'LB_IN');
		$value['UY'] = array('region' => 'AM', 'currency' =>'UYU', 'weight' => 'KG_CM');
		$value['UZ'] = array('region' => 'AP', 'currency' =>'UZS', 'weight' => 'KG_CM');
		$value['VC'] = array('region' => 'AM', 'currency' =>'XCD', 'weight' => 'LB_IN');
		$value['VE'] = array('region' => 'AM', 'currency' =>'VEF', 'weight' => 'KG_CM');
		$value['VG'] = array('region' => 'AM', 'currency' =>'USD', 'weight' => 'LB_IN');
		$value['VI'] = array('region' => 'AM', 'currency' =>'USD', 'weight' => 'LB_IN');
		$value['VN'] = array('region' => 'AP', 'currency' =>'VND', 'weight' => 'KG_CM');
		$value['VU'] = array('region' => 'AP', 'currency' =>'VUV', 'weight' => 'KG_CM');
		$value['WS'] = array('region' => 'AP', 'currency' =>'WST', 'weight' => 'KG_CM');
		$value['XB'] = array('region' => 'AM', 'currency' =>'EUR', 'weight' => 'LB_IN');
		$value['XC'] = array('region' => 'AM', 'currency' =>'EUR', 'weight' => 'LB_IN');
		$value['XE'] = array('region' => 'AM', 'currency' =>'ANG', 'weight' => 'LB_IN');
		$value['XM'] = array('region' => 'AM', 'currency' =>'EUR', 'weight' => 'LB_IN');
		$value['XN'] = array('region' => 'AM', 'currency' =>'XCD', 'weight' => 'LB_IN');
		$value['XS'] = array('region' => 'AP', 'currency' =>'SIS', 'weight' => 'KG_CM');
		$value['XY'] = array('region' => 'AM', 'currency' =>'ANG', 'weight' => 'LB_IN');
		$value['YE'] = array('region' => 'AP', 'currency' =>'YER', 'weight' => 'KG_CM');
		$value['YT'] = array('region' => 'AP', 'currency' =>'EUR', 'weight' => 'KG_CM');
		$value['ZA'] = array('region' => 'AP', 'currency' =>'ZAR', 'weight' => 'KG_CM');
		$value['ZM'] = array('region' => 'AP', 'currency' =>'ZMW', 'weight' => 'KG_CM');
		$value['ZW'] = array('region' => 'AP', 'currency' =>'USD', 'weight' => 'KG_CM');
	$packing_type = array("per_item" => "Pack Items Induviually", "weight_based" => "Weight Based Packing");
	$classification = array("00" => "Rate associated with account number","01"=>"Daily Rates","04"=>"Retail Rates","05"=>"Regional Rates", "06"=>"General List Rates","07"=>"Alternative Zoning","08"=>"General List Rates II","09"=>"SMB Loyalty","10"=>"All Inclusive","11"=>"Value Bundle I","53"=>"Standard List Rates",);
	$weight_dim_unit = array("KG_CM" => "KG_CM", "LB_IN" => "LB_IN");
	$ven_col_type = array(" 0356" => "IOSS", " 0357" => "VOEC", "0358" => "HMRC", "0359" => "PVA");
	$del_con_type = array("NONE" => "None", "SIG" => "Signature Required", "ADLT_SIG" => "Adult Signature Required");
	$currencys = $value; 
	$general_settings = get_option('hit_ups_auto_main_settings');
	$general_settings = empty($general_settings) ? array() : $general_settings;
	
	if(isset($_POST['save']))
	{
		$hitshippo_ups_shipo_password ='';
		if(isset($_POST['hit_ups_auto_site_id'])){
		$general_settings['hit_ups_auto_integration_key'] = sanitize_text_field(isset($_POST['hit_ups_auto_integration_key']) ? $_POST['hit_ups_auto_integration_key'] : '');
		$general_settings['hit_ups_auto_site_id'] = sanitize_text_field(isset($_POST['hit_ups_auto_site_id']) ? $_POST['hit_ups_auto_site_id'] : '');
		$general_settings['hit_ups_auto_site_pwd'] = sanitize_text_field(isset($_POST['hit_ups_auto_site_pwd']) ? $_POST['hit_ups_auto_site_pwd'] : '');
		$general_settings['hit_ups_auto_acc_no'] = sanitize_text_field(isset($_POST['hit_ups_auto_acc_no']) ? $_POST['hit_ups_auto_acc_no'] : '');
		$general_settings['hit_ups_auto_access_key'] = sanitize_text_field(isset($_POST['hit_ups_auto_access_key']) ? $_POST['hit_ups_auto_access_key'] : '');
		$general_settings['hit_ups_auto_api_type'] = sanitize_text_field(isset($_POST['hit_ups_auto_api_type']) ? $_POST['hit_ups_auto_api_type'] : '');
		$general_settings['hit_ups_auto_rest_site_id'] = sanitize_text_field(isset($_POST['hit_ups_auto_rest_site_id']) ? $_POST['hit_ups_auto_rest_site_id'] : '');
		$general_settings['hit_ups_auto_rest_site_pwd'] = sanitize_text_field(isset($_POST['hit_ups_auto_rest_site_pwd']) ? $_POST['hit_ups_auto_rest_site_pwd'] : '');
		$general_settings['hit_ups_auto_rest_usr_pwd'] = sanitize_text_field(isset($_POST['hit_ups_auto_rest_usr_pwd']) ? $_POST['hit_ups_auto_rest_usr_pwd'] : '');
		$general_settings['hit_ups_auto_rest_acc_no'] = sanitize_text_field(isset($_POST['hit_ups_auto_rest_acc_no']) ? $_POST['hit_ups_auto_rest_acc_no'] : '');
		$general_settings['hit_ups_auto_rest_grant_type'] = sanitize_text_field(isset($_POST['hit_ups_auto_rest_grant_type']) ? $_POST['hit_ups_auto_rest_grant_type'] : '');
		$general_settings['hit_ups_auto_test'] = sanitize_text_field(isset($_POST['hit_ups_auto_test']) ? 'yes' : 'no');
		$general_settings['hit_ups_auto_rates'] = sanitize_text_field(isset($_POST['hit_ups_auto_rates']) ? 'yes' : 'no');
		$general_settings['hit_ups_auto_shipper_name'] = sanitize_text_field(isset($_POST['hit_ups_auto_shipper_name']) ? $_POST['hit_ups_auto_shipper_name'] : '');
		$general_settings['hit_ups_auto_company'] = sanitize_text_field(isset($_POST['hit_ups_auto_company']) ? $_POST['hit_ups_auto_company'] : '');
		$general_settings['hit_ups_auto_mob_num'] = sanitize_text_field(isset($_POST['hit_ups_auto_mob_num']) ? $_POST['hit_ups_auto_mob_num'] : '');
		$general_settings['hit_ups_auto_email'] = sanitize_text_field(isset($_POST['hit_ups_auto_email']) ? $_POST['hit_ups_auto_email'] : '');
		$general_settings['hit_ups_auto_address1'] = sanitize_text_field(isset($_POST['hit_ups_auto_address1']) ? $_POST['hit_ups_auto_address1'] : '');
		$general_settings['hit_ups_auto_address2'] = sanitize_text_field(isset($_POST['hit_ups_auto_address2']) ? $_POST['hit_ups_auto_address2'] : '');
		$general_settings['hit_ups_auto_city'] = sanitize_text_field(isset($_POST['hit_ups_auto_city']) ? $_POST['hit_ups_auto_city'] : '');
		$general_settings['hit_ups_auto_state'] = sanitize_text_field(isset($_POST['hit_ups_auto_state']) ? $_POST['hit_ups_auto_state'] : '');
		$general_settings['hit_ups_auto_zip'] = sanitize_text_field(isset($_POST['hit_ups_auto_zip']) ? $_POST['hit_ups_auto_zip'] : '');
		$general_settings['hit_ups_auto_country'] = sanitize_text_field(isset($_POST['hit_ups_auto_country']) ? $_POST['hit_ups_auto_country'] : '');
		$general_settings['hit_ups_auto_gstin'] = sanitize_text_field(isset($_POST['hit_ups_auto_gstin']) ? $_POST['hit_ups_auto_gstin'] : '');
		$general_settings['hit_ups_auto_ven_col_type'] = sanitize_text_field(isset($_POST['hit_ups_auto_ven_col_type']) ? $_POST['hit_ups_auto_ven_col_type'] : '');
		$general_settings['hit_ups_auto_ven_col_id'] = sanitize_text_field(isset($_POST['hit_ups_auto_ven_col_id']) ? $_POST['hit_ups_auto_ven_col_id'] : '');
		$general_settings['hit_ups_auto_carrier'] = !empty($_POST['hit_ups_auto_carrier']) ? sanitize_array($_POST['hit_ups_auto_carrier']) : array();
		$general_settings['hit_ups_Domestic_service'] = !empty($_POST['hit_ups_Domestic_service']) ? sanitize_meta('hit_ups_Domestic_service', $_POST['hit_ups_Domestic_service'], 'post') : array();
		$general_settings['hit_ups_international_service'] = !empty($_POST['hit_ups_international_service']) ? sanitize_meta('hit_ups_international_service', $_POST['hit_ups_international_service'], 'post') : array();
		$general_settings['hit_ups_auto_carrier_name'] = !empty($_POST['hit_ups_auto_carrier_name']) ? sanitize_array($_POST['hit_ups_auto_carrier_name']) : array();
		$general_settings['hit_ups_auto_account_rates'] = sanitize_text_field(isset($_POST['hit_ups_auto_account_rates']) ? 'yes' : 'no');
		$general_settings['hit_ups_auto_developer_rate'] = sanitize_text_field(isset($_POST['hit_ups_auto_developer_rate']) ? 'yes' :'no');
		$general_settings['hit_ups_auto_insure'] = sanitize_text_field(isset($_POST['hit_ups_auto_insure']) ? 'yes' :'no');
		$general_settings['hit_ups_auto_exclude_countries'] = !empty($_POST['hit_ups_auto_exclude_countries']) ? $_POST['hit_ups_auto_exclude_countries'] : array();
		
		$general_settings['hit_ups_auto_uostatus'] = sanitize_text_field(isset($_POST['hit_ups_auto_uostatus']) ? 'yes' :'no');
		$general_settings['hit_ups_auto_trk_status_cus'] = sanitize_text_field(isset($_POST['hit_ups_auto_trk_status_cus']) ? 'yes' :'no');
		$general_settings['hit_ups_auto_aabill'] = sanitize_text_field(isset($_POST['hit_ups_auto_aabill']) ? 'yes' :'no');
		$general_settings['hit_ups_auto_cod'] = sanitize_text_field(isset($_POST['hit_ups_auto_cod']) ? 'yes' :'no');
		$general_settings['hit_ups_auto_sat'] = sanitize_text_field(isset($_POST['hit_ups_auto_sat']) ? 'yes' :'no');
		$general_settings['hit_ups_auto_ppt'] = sanitize_text_field(isset($_POST['hit_ups_auto_ppt']) ? 'yes' :'no');
		$general_settings['hit_ups_auto_label_automation'] = sanitize_text_field(isset($_POST['hit_ups_auto_label_automation']) ? 'yes' :'no');
		$general_settings['hit_ups_add_shipping_invoice'] = sanitize_text_field(isset($_POST['hit_ups_add_shipping_invoice']) ? 'yes' :'no');
		
		$general_settings['hit_ups_auto_packing_type'] = sanitize_text_field(isset($_POST['hit_ups_auto_packing_type']) ? $_POST['hit_ups_auto_packing_type'] : 'per_item');
		$general_settings['hit_ups_auto_max_weight'] = sanitize_text_field(isset($_POST['hit_ups_auto_max_weight']) ? $_POST['hit_ups_auto_max_weight'] : '100');
		$general_settings['hit_ups_auto_customer_classification'] = sanitize_text_field(isset($_POST['hit_ups_auto_customer_classification']) ? $_POST['hit_ups_auto_customer_classification'] : '00');
		// $general_settings['hit_ups_auto_integration_key'] = sanitize_text_field(isset($_POST['hit_ups_auto_integration_key']) ? $_POST['hit_ups_auto_integration_key'] : '');
		$general_settings['hit_ups_auto_label_email'] = sanitize_text_field(isset($_POST['hit_ups_auto_label_email']) ? $_POST['hit_ups_auto_label_email'] : '');
		$general_settings['hit_ups_auto_ship_content'] = sanitize_text_field(isset($_POST['hit_ups_auto_ship_content']) ? $_POST['hit_ups_auto_ship_content'] : 'No shipment content');
		$general_settings['hit_ups_auto_mail_sub'] = sanitize_text_field(isset($_POST['hit_ups_auto_mail_sub']) ? $_POST['hit_ups_auto_mail_sub'] : 'No shipment content');
		$general_settings['hit_ups_auto_print_size'] = sanitize_text_field(isset($_POST['hit_ups_auto_print_size']) ? $_POST['hit_ups_auto_print_size'] : '6X4_PDF');
		$general_settings['hit_ups_auto_sign_req'] = sanitize_text_field(isset($_POST['hit_ups_auto_sign_req']) ? $_POST['hit_ups_auto_sign_req'] : 'no');
		$general_settings['hit_ups_auto_duty_payment'] = sanitize_text_field(isset($_POST['hit_ups_auto_duty_payment']) ? $_POST['hit_ups_auto_duty_payment'] : 'none');
		$general_settings['hit_ups_auto_del_con'] = sanitize_text_field(isset($_POST['hit_ups_auto_del_con']) ? $_POST['hit_ups_auto_del_con'] : 'NONE');
		$general_settings['hit_ups_auto_weight_unit'] = sanitize_text_field(isset($_POST['hit_ups_auto_weight_unit']) ? $_POST['hit_ups_auto_weight_unit'] : 'KG_CM');
		$general_settings['hit_ups_auto_con_rate'] = sanitize_text_field(isset($_POST['hit_ups_auto_con_rate']) ? $_POST['hit_ups_auto_con_rate'] : '');
		
		// Multi Vendor Settings

		$general_settings['hit_ups_auto_v_enable'] = sanitize_text_field(isset($_POST['hit_ups_auto_v_enable']) ? 'yes' : 'no');
		$general_settings['hit_ups_auto_v_rates'] = sanitize_text_field(isset($_POST['hit_ups_auto_v_rates']) ? 'yes' : 'no');
		$general_settings['hit_ups_auto_v_labels'] = sanitize_text_field(isset($_POST['hit_ups_auto_v_labels']) ? 'yes' : 'no');
		$general_settings['hit_ups_auto_v_roles'] = !empty($_POST['hit_ups_auto_v_roles']) ? $_POST['hit_ups_auto_v_roles'] : array();
		$general_settings['hit_ups_auto_v_email'] = sanitize_text_field(isset($_POST['hit_ups_auto_v_email']) ? 'yes' : 'no');
		$general_settings['hit_ups_auto_currency'] = isset($value[(isset($general_settings['hit_ups_auto_country']) ? $general_settings['hit_ups_auto_country'] : 'HIT')]) ? $value[$general_settings['hit_ups_auto_country']]['currency'] : '';

		update_option('hit_ups_auto_main_settings', $general_settings);
		$success = 'Settings Saved Successfully.';
		// reset all auth tokens if operating mode changes
		if (isset($general_settings['hit_ups_auto_test']) && !empty($general_settings['hit_ups_auto_test']) && isset($general_settings['hit_ups_auto_api_type']) && ($general_settings['hit_ups_auto_api_type'] == "REST")) {
			if (isset($_POST['hitshippo_ups_last_mode']) && !empty($_POST['hitshippo_ups_last_mode'])) {
				if ($_POST['hitshippo_ups_last_mode'] != $general_settings['hit_ups_auto_test']) {
					try {
						$wpdb->query("DELETE FROM `".$wpdb->prefix."options` WHERE `option_name` LIKE '_transient_hitshipo_ups_rest_auth_token_%'");
					} catch (Exception $e) {
						
					}
				}
			}
		}
	}
	if ((!isset($general_settings['hit_ups_auto_integration_key']) || empty($general_settings['hit_ups_auto_integration_key'])) && isset($_POST['shipo_link_type']) && $_POST['shipo_link_type'] == "WITH") {
		$general_settings['hit_ups_auto_integration_key'] = sanitize_text_field(isset($_POST['hit_ups_auto_integration_key']) ? $_POST['hit_ups_auto_integration_key'] : '');
		update_option('hit_ups_auto_main_settings', $general_settings);
		update_option('hitshipo_ups_working_status', 'start_working');
		$success = 'Site Linked Successfully.<br><br> It\'s great to have you here.';
	}
	
if(!isset($general_settings['hit_ups_auto_integration_key']) || empty($general_settings['hit_ups_auto_integration_key'])){
			$random_nonce = wp_generate_password(16, false);
			set_transient( 'hitshipo_ups_nonce_temp', $random_nonce, HOUR_IN_SECONDS );
			
			$general_settings['hitshippo_ups_track_audit'] = sanitize_text_field(isset($_POST['hitshippo_ups_track_audit']) ? 'yes' : 'no');
			$general_settings['hitshippo_ups_daily_report'] = sanitize_text_field(isset($_POST['hitshippo_ups_daily_report']) ? 'yes' : 'no');
			$general_settings['hitshippo_ups_monthly_report'] = sanitize_text_field(isset($_POST['hitshippo_ups_monthly_report']) ? 'yes' : 'no');
			$general_settings['hitshippo_ups_shipo_signup'] = sanitize_text_field(isset($_POST['hitshippo_ups_shipo_signup']) ? $_POST['hitshippo_ups_shipo_signup'] : '');
			$hitshippo_ups_shipo_password = sanitize_text_field(isset($_POST['hitshippo_ups_shipo_password']) ? $_POST['hitshippo_ups_shipo_password'] : '');
			update_option('hit_ups_auto_main_settings', $general_settings);
			$hitshippo_ups_shipo_password = base64_encode($hitshippo_ups_shipo_password);

			$link_hitshipo_request = json_encode(array('site_url' => site_url(),
				'site_name' => get_bloginfo('name'),
				'email_address' => $general_settings['hitshippo_ups_shipo_signup'],
				'password' => $hitshippo_ups_shipo_password,
				'nonce' => $random_nonce,
				'audit' => $general_settings['hitshippo_ups_track_audit'],
				'd_report' => $general_settings['hitshippo_ups_daily_report'],
				'm_report' => $general_settings['hitshippo_ups_monthly_report'],
				'pulgin' => 'UPS',
				'platfrom' => 'Woocommerce',
			));
			
			$link_site_url = "https://app.myshipi.com/api/link-site.php";
			$link_site_response = wp_remote_post( $link_site_url , array(
					'method'      => 'POST',
					'timeout'     => 45,
					'redirection' => 5,
					'httpversion' => '1.0',
					'blocking'    => true,
					'headers'     => array('Content-Type' => 'application/json; charset=utf-8'),
					'body'        => $link_hitshipo_request,
					'sslverify'   => FALSE
					)
				);
				
				$link_site_response = ( is_array($link_site_response) && isset($link_site_response['body'])) ? json_decode($link_site_response['body'], true) : array();
				if($link_site_response){
					
				if($link_site_response['status'] != 'error'){
						$general_settings['hit_ups_auto_integration_key'] = sanitize_text_field($link_site_response['integration_key']);
						update_option('hit_ups_auto_main_settings', $general_settings);
						
						update_option('hitshipo_ups_working_status', 'start_working');
						$success = 'Site Linked Successfully.<br><br> It\'s great to have you here. ' . (isset($link_site_response['trail']) ? 'Your 60days Trail period is started. To know about this more, please check your inbox.' : '' ) . '<br><br><button class="button" type="submit">Back to Settings</button>';
					}else{
						$error = '<p style="color:red;">'. $link_site_response['message'] .'</p>';
						$success = '';
					}
				}else{
					$error = '<p style="color:red;">Failed to connect with Shipi</p>';
					$success = '';
				}
				
		
		}
		
	}
	$initial_setup = empty($general_settings) ? true : false;
	$countries_obj   = new WC_Countries();
	$default_country = $countries_obj->get_base_country();
	$general_settings['hit_ups_auto_currency'] = isset($value[(isset($general_settings['hit_ups_auto_country']) ? $general_settings['hit_ups_auto_country'] : 'HIT')]) ? $value[$general_settings['hit_ups_auto_country']]['currency'] : (isset($value[$default_country]) ? $value[$default_country]['currency'] : "");
	$general_settings['hit_ups_auto_woo_currency'] = get_option('woocommerce_currency');
	
?>

<style>
.notice{display:none;}
#multistepsform {
  width: 80%;
  margin: 50px auto;
  text-align: center;
  position: relative;
}
#multistepsform fieldset {
  background: white;
  text-align:left;
  border: 0 none;
  border-radius: 5px;
  <?php if (!$initial_setup) { ?>
  box-shadow: 0 0 15px 1px rgba(0, 0, 0, 0.4);
  <?php } ?>
  padding: 20px 30px;
  box-sizing: border-box;
  position: relative;
}
<?php if (!$initial_setup) { ?>
#multistepsform fieldset:not(:first-of-type) {
  display: none;
}
<?php } ?>
#multistepsform input[type=text], #multistepsform input[type=password], #multistepsform input[type=number], #multistepsform input[type=email], 
#multistepsform textarea {
  padding: 5px;
  width: 95%;
}
#multistepsform input:focus,
#multistepsform textarea:focus {
  border-color: #679b9b;
  outline: none;
  color: #637373;
}
#multistepsform .action-button {
  width: 100px;
  background: #341b14;
  font-weight: bold;
  color: #fab52c;
  transition: 150ms;
  border: 0 none;
  float:right;
  border-radius: 1px;
  cursor: pointer;
  padding: 10px 5px;
  margin: 10px 5px;
}
#multistepsform .action-button:hover,
#multistepsform .action-button:focus {
  box-shadow: 0 0 0 2px #f08a5d, 0 0 0 3px #ff976;
  color: #fff;
}
#multistepsform .fs-title {
  font-size: 15px;
  text-transform: uppercase;
  color: #2c3e50;
  margin-bottom: 10px;
}
#multistepsform .fs-subtitle {
  font-weight: normal;
  font-size: 13px;
  color: #666;
  margin-bottom: 20px;
}
#multistepsform #progressbar {
  margin-bottom: 30px;
  overflow: hidden;
  counter-reset: step;
}
#multistepsform #progressbar li {
  list-style-type: none;
  color: #ffb406;
  text-transform: uppercase;
  font-size: 9px;
  width: 16.5%;
  float: left;
  position: relative;
}
#multistepsform #progressbar li:before {
  content: counter(step);
  counter-increment: step;
  width: 20px;
  line-height: 20px;
  display: block;
  font-size: 10px;
  color: #fff;
  background: #ffb406;
  border-radius: 3px;
  margin: 0 auto 5px auto;
}
#multistepsform #progressbar li:after {
  content: "";
  width: 100%;
  height: 2px;
  background: #ffb406;
  position: absolute;
  left: -50%;
  top: 9px;
  z-index: -1;
}
#multistepsform #progressbar li:first-child:after {
  content: none;
}
#multistepsform #progressbar li.active {
  color: #341B14;
}
#multistepsform #progressbar li.active:before, #multistepsform #progressbar li.active:after {
  background: #341B14;
  color: white;
}
.setting{
	cursor: pointer;
	border: 0px;
	padding: 10px 5px;
  	margin: 10px 5px;
 	background-color: #341b14!important;
	font-weight: bold; 
	color:#fab52c!important;
	border-radius: 3px;
}
		</style>
<div style="text-align:center;margin-top:20px;"><img src="<?php _e(plugin_dir_url(__FILE__),'hit_ups_auto'); ?>ups_logo.png" style="width:100px;"></div>

<?php if($success != ''){
	 _e('<form id="multistepsform" method="post"><fieldset>
    <center><h2 class="fs-title" style="line-height:27px;">'. $success .'</h2>
	</center></form>','hit_ups_auto');
}else{
	?>
<!-- multistep form -->
<form id="multistepsform" method="post">
  <?php if (!$initial_setup) { ?>
  <!-- progressbar -->
  <ul id="progressbar">
    <li class="active">Integration</li>
    <li>Setup</li>
    <li>Packing</li>
    <li>Rates</li>
    <li>Shipping Label</li>
    <li>Shipi</li>
  </ul>
  <?php } ?>
  <?php if($error == ''){

  ?>
  <!-- fieldsets -->
	<fieldset>
		<center><h2 class="fs-title">UPS Account Information</h2>
		
		<table style="padding-left:10px;padding-right:10px;">
			<tr>
				<td><span style="float:left;padding-right:10px;"><input type="checkbox" name="hit_ups_auto_test" <?php  _e((isset($general_settings['hit_ups_auto_test']) && $general_settings['hit_ups_auto_test'] == 'yes') ? 'checked="true"' : '','hit_ups_auto'); ?> value="yes" ><small style="color:gray"> Enable Test Mode.</small></span></td>
				<td><span style="float:right;padding-right:10px;"><input type="checkbox" name="hit_ups_auto_rates" <?php  _e((isset($general_settings['hit_ups_auto_rates']) && $general_settings['hit_ups_auto_rates'] == 'yes') || ($initial_setup) ? 'checked="true"' : '','hit_ups_auto'); ?> value="yes" ><small style="color:gray"> Enable Live Shipping Rates.</small></span></td>
				<td><span style="float:right;padding-right:10px;"><input type="checkbox" name="hit_ups_auto_label_automation" <?php _e((isset($general_settings['hit_ups_auto_label_automation']) && $general_settings['hit_ups_auto_label_automation'] == 'yes') || ($initial_setup) ? 'checked="true"' : '','hit_ups_auto'); ?> value="yes" ><small style="color:gray"> Create Label automatically.</small></span></td>
				<td><span style="float:right;padding-right:10px;"><input type="checkbox" name="hit_ups_auto_developer_rate" <?php _e((isset($general_settings['hit_ups_auto_developer_rate']) && $general_settings['hit_ups_auto_developer_rate'] == 'yes') ? 'checked="true"' : '','hit_ups_auto'); ?> value="yes" ><small style="color:gray"> Enable Debug Mode.</small></span></td>
				<td style="display: none;"><input type="text" name="hitshippo_ups_last_mode" value="<?php echo (isset($general_settings['hit_ups_auto_test'])) ? $general_settings['hit_ups_auto_test'] : ''; ?>"></td>
			</tr>
		</table>
		</center>
		<table style="width:100%;">
			<tr><td colspan="2" style="padding:10px;"><hr></td></tr>
			<tr>
				<td colspan="2" style="padding:10px;">
					<center>
						<b><?php _e('Select API type','hit_ups_auto') ?></b><br><br>
						<?php
							if ((!isset($general_settings['hit_ups_auto_site_id']) && !isset($general_settings['hit_ups_auto_api_type'])) || (isset($general_settings['hit_ups_auto_api_type']) && $general_settings['hit_ups_auto_api_type'] == "REST")) {
							?>
								<input type="radio" name="hit_ups_auto_api_type" value="REST" checked> I don't have access key (OAuth API) &nbsp; &nbsp;
								<input type="radio" name="hit_ups_auto_api_type" value="SOAP"> I have access key (XML API)
							<?php
							} else {
							?>
								<input type="radio" name="hit_ups_auto_api_type" value="REST"> I don't have access key (OAuth API) &nbsp; &nbsp;
								<input type="radio" name="hit_ups_auto_api_type" value="SOAP" checked> I have access key (XML API)
							<?php
							}
						?>
					</center>
				</td>
			</tr>
		<tr><td colspan="2" style="padding:10px;"><hr></td></tr>
			<tr class="SOAP_auth">
				<td style=" width: 50%;padding:10px;">
					<?php _e('UPS Login User ID','hit_ups_auto') ?><font style="color:red;">*</font>
					<input type="text" class="input-text regular-input" id="hit_ups_auto_site_id" name="hit_ups_auto_site_id" value="<?php _e((isset($general_settings['hit_ups_auto_site_id'])) ? $general_settings['hit_ups_auto_site_id'] : '','hit_ups_auto'); ?>">
				</td>
				<td style="padding:10px;">
				<?php _e('UPS Login Password','hit_ups_auto') ?><font style="color:red;">*</font>
				<input type="text" name="hit_ups_auto_site_pwd" id ="hit_ups_auto_site_pwd" value="<?php _e((isset($general_settings['hit_ups_auto_site_pwd'])) ? $general_settings['hit_ups_auto_site_pwd'] : '','hit_ups_auto'); ?>">			
			</td>
			</tr>
			<tr class="SOAP_auth">
				<td style=" width: 50%;padding:10px;">
					<?php _e('UPS Account number','hit_ups_auto') ?><font style="color:red;">*</font>
					<input type="text" class="input-text regular-input" id="hit_ups_auto_acc_no" name="hit_ups_auto_acc_no" value="<?php _e((isset($general_settings['hit_ups_auto_acc_no'])) ? $general_settings['hit_ups_auto_acc_no'] : '','hit_ups_auto'); ?>">
				</td>
				<td style="padding:10px;">
				<?php _e('UPS Access Key','hit_ups_auto') ?><font style="color:red;">*</font>
				<input type="text" name="hit_ups_auto_access_key" id="hit_ups_auto_access_key" value="<?php _e((isset($general_settings['hit_ups_auto_access_key'])) ? $general_settings['hit_ups_auto_access_key'] : '','hit_ups_auto'); ?>">			
			</td>
			</tr>
			<tr class="REST_auth">
				<td style=" width: 50%;padding:10px;">
					<?php _e('Client ID','hit_ups_auto') ?><font style="color:red;">*</font>
					<input type="text" class="input-text regular-input" id="hit_ups_auto_rest_site_id" name="hit_ups_auto_rest_site_id" value="<?php _e((isset($general_settings['hit_ups_auto_rest_site_id'])) ? $general_settings['hit_ups_auto_rest_site_id'] : '','hit_ups_auto'); ?>">
				</td>
				<td style="padding:10px;">
					<?php _e('Client Secret','hit_ups_auto') ?><font style="color:red;">*</font>
					<input type="text" name="hit_ups_auto_rest_site_pwd" id ="hit_ups_auto_rest_site_pwd" value="<?php _e((isset($general_settings['hit_ups_auto_rest_site_pwd'])) ? $general_settings['hit_ups_auto_rest_site_pwd'] : '','hit_ups_auto'); ?>">			
				</td>
			</tr>
			<tr class="REST_auth">
				<td style=" width: 50%;padding:10px;">
					<?php _e('UPS Account number','hit_ups_auto') ?><font style="color:red;">*</font>
					<input type="text" class="input-text regular-input" id="hit_ups_auto_rest_acc_no" name="hit_ups_auto_rest_acc_no" value="<?php _e((isset($general_settings['hit_ups_auto_rest_acc_no'])) ? $general_settings['hit_ups_auto_rest_acc_no'] : '','hit_ups_auto'); ?>">
				</td>
				<td style="padding:10px;">
					<?php _e('Grant Type','hit_ups_auto') ?><font style="color:red;">*</font><br>
					<select name="hit_ups_auto_rest_grant_type" id="hit_ups_auto_rest_grant_type" class="wc-enhanced-select" style="width:95%;padding:5px;">
						<option value="client_credentials" <?php _e((isset($general_settings['hit_ups_auto_rest_grant_type']) && $general_settings['hit_ups_auto_rest_grant_type'] == 'client_credentials') ? 'Selected="true"' : '','hit_ups_auto'); ?>> Client Credentials </option>
					</select>
				</td>
			</tr>
			<tr>
				<td style="padding:10px;">
				<?php _e('UPS Weight Unit','hit_ups_auto') ?><font style="color:red;">*</font><br>
					<select name="hit_ups_auto_weight_unit" class="wc-enhanced-select" style="width:95%;padding:5px;">
						<option value="LB_IN" <?php _e((isset($general_settings['hit_ups_auto_weight_unit']) && $general_settings['hit_ups_auto_weight_unit'] == 'LB_IN') ? 'Selected="true"' : '','hit_ups_auto'); ?>> LB & IN </option>
						<option value="KG_CM" <?php _e((isset($general_settings['hit_ups_auto_weight_unit']) && $general_settings['hit_ups_auto_weight_unit'] == 'KG_CM') ? 'Selected="true"' : '','hit_ups_auto'); ?>> KG & CM </option>
					</select>
				</td>
				<td style="padding:10px;">
					<?php _e('Change UPS currency','hit_ups_auto') ?><font style="color:red;">*</font>
					<select name="hit_ups_auto_currency" style="width:95%;padding:5px;">
							
						<?php foreach($currencys as  $currency)
						{
							if(isset($general_settings['hit_ups_auto_currency']) && ($general_settings['hit_ups_auto_currency'] == $currency['currency']))
							{
								_e("<option value=".$currency['currency']." selected='true'>".$currency['currency']."</option>",'hit_ups_auto');
							}
							else
							{
								_e("<option value=".$currency['currency'].">".$currency['currency']."</option>",'hit_ups_auto');
							}
						}

						if (!isset($general_settings['hit_ups_auto_currency']) || ($general_settings['hit_ups_auto_currency'] != "NMP")) {
								_e("<option value=NMP>NMP</option>",'hit_ups_auto');
						}elseif (isset($general_settings['hit_ups_auto_currency']) && ($general_settings['hit_ups_auto_currency'] == "NMP")) {
								_e("<option value=NMP selected='true'>NMP</option>",'hit_ups_auto');
						} ?>
					</select>
				</td>
			</tr>
			<tr><td colspan="2" style="padding:10px;"><hr></td></tr>
			<?php if (isset($general_settings['hit_ups_auto_currency']) && $general_settings['hit_ups_auto_woo_currency'] != $general_settings['hit_ups_auto_currency'] ){
				?>
					<tr><td colspan="2" style="text-align:center;"><small><?php _e(' Your Website Currency is ','hit_ups_auto') ?> <b><?php _e($general_settings['hit_ups_auto_woo_currency'],'hit_ups_auto');?></b> and your UPS currency is <b><?php _e((isset($general_settings['hit_ups_auto_currency'])) ? $general_settings['hit_ups_auto_currency'] : '(Choose country)','hit_ups_auto'); ?></b>. <?php _e(($general_settings['hit_ups_auto_woo_currency'] != $general_settings['hit_ups_auto_currency'] ) ? 'So you have to consider the converstion rate.' : '','hit_ups_auto') ?></small>
						</td>
					</tr>
					
					<tr><td colspan="2" style="text-align:center;">
					<input type="checkbox" id="auto_con" name="hit_ups_auto_auto_con_rate" <?php _e((isset($general_settings['hit_ups_auto_auto_con_rate']) && $general_settings['hit_ups_auto_auto_con_rate'] == 'yes') ? 'checked="true"' : '','hit_ups_auto'); ?> value="yes" ><?php _e('Auto Currency Conversion ','hit_ups_auto') ?>
						
					</td>
					</tr>
					
					<tr>
						<td style="padding:10px;text-align:center;" colspan="2" class="con_rate" >
							<?php _e('Exchange Rate','hit_ups_auto') ?><font style="color:red;">*</font> <?php _e("( ".$general_settings['hit_ups_auto_woo_currency']."->".$general_settings['hit_ups_auto_currency']." )",'hit_ups_auto'); ?>
							<br><input type="text" style="width:240px;" name="hit_ups_auto_con_rate" value="<?php _e((isset($general_settings['hit_ups_auto_con_rate'])) ? $general_settings['hit_ups_auto_con_rate'] : '','hit_ups_auto'); ?>">
							<br><small style="color:gray;"><?php _e('Enter conversion rate.','hit_ups_auto') ?></small>
						</td>
					</tr>
					<tr><td colspan="2" style="padding:10px;"><hr></td></tr>
				<?php
			}
			?>
			
		</table>
		<?php if(isset($general_settings['hit_ups_auto_integration_key']) && $general_settings['hit_ups_auto_integration_key'] !=''){
			_e('<input type="submit" name="save" class="action-button save_change" style="width:auto;float:left;" value="Save Changes" />','hit_ups_auto');
		}

		?>
		<?php if (!$initial_setup) { ?>
		<input type="button" name="next" class="next action-button" value="Next" />
		<?php } ?>
    </fieldset>
	<fieldset>
		<center><h2 class="fs-title">Shipping Address Information</h2></center>
		
		<table style="width:100%;">
			<tr><td colspan="2" style="padding:10px;"><hr></td></tr>
			<tr>
				<td style=" width: 50%;padding:10px;">
					<?php _e('Shipper Name','hit_ups_auto') ?><font style="color:red;">*</font>
					<input type="text" name="hit_ups_auto_shipper_name" id="hit_ups_auto_shipper_name" value="<?php _e((isset($general_settings['hit_ups_auto_shipper_name'])) ? $general_settings['hit_ups_auto_shipper_name'] : '','hit_ups_auto'); ?>">
				</td>
				<td style="padding:10px;">
				<?php _e('Company Name','hit_ups_auto') ?><font style="color:red;">*</font>
				<input type="text" name="hit_ups_auto_company" id="hit_ups_auto_company" value="<?php _e((isset($general_settings['hit_ups_auto_company'])) ? $general_settings['hit_ups_auto_company'] : '','hit_ups_auto'); ?>">
				</td>
			</tr>
			<tr>
				<td style=" width: 50%;padding:10px;">
					<?php _e('Shipper Mobile / Contact Number','hit_ups_auto') ?><font style="color:red;">*</font>
					<input type="text" name="hit_ups_auto_mob_num" id="hit_ups_auto_mob_num" value="<?php _e((isset($general_settings['hit_ups_auto_mob_num'])) ? $general_settings['hit_ups_auto_mob_num'] : '','hit_ups_auto'); ?>">
				</td>
				<td style="padding:10px;">
				<?php _e('Email Address of the Shipper','hit_ups_auto') ?><font style="color:red;">*</font>
				<input type="text" name="hit_ups_auto_email" id="hit_ups_auto_email" value="<?php _e((isset($general_settings['hit_ups_auto_email'])) ? $general_settings['hit_ups_auto_email'] : '','hit_ups_auto'); ?>">
				</td>
			</tr>
			<tr>
				<td style=" width: 50%;padding:10px;">
					<?php _e('Address Line 1','hit_ups_auto') ?><font style="color:red;">*</font>
					<input type="text" name="hit_ups_auto_address1" id="hit_ups_auto_address1" value="<?php _e((isset($general_settings['hit_ups_auto_address1'])) ? $general_settings['hit_ups_auto_address1'] : '','hit_ups_auto'); ?>">
				</td>
				<td style="padding:10px;">
				<?php _e('Address Line 2','hit_ups_auto') ?>
				<input type="text" name="hit_ups_auto_address2" value="<?php _e((isset($general_settings['hit_ups_auto_address2'])) ? $general_settings['hit_ups_auto_address2'] : '','hit_ups_auto'); ?>">
				</td>
			</tr>
			<tr>
				<td style=" width: 50%;padding:10px;">
					<?php _e('City of the Shipper from address','hit_ups_auto') ?><font style="color:red;">*</font>
					<input type="text" name="hit_ups_auto_city"  id="hit_ups_auto_city" value="<?php _e((isset($general_settings['hit_ups_auto_city'])) ? $general_settings['hit_ups_auto_city'] : '','hit_ups_auto'); ?>">
				</td>
				<td style="padding:10px;">
				<?php _e('State (Two digit ISO code accepted.)','hit_ups_auto') ?><font style="color:red;">*</font>
				<input type="text" name="hit_ups_auto_state" id="hit_ups_auto_state" value="<?php _e((isset($general_settings['hit_ups_auto_state'])) ? $general_settings['hit_ups_auto_state'] : '','hit_ups_auto'); ?>">
				</td>
			</tr>
			<tr>
				<td style=" width: 50%;padding:10px;">
					<?php _e('Postal/Zip Code','hit_ups_auto') ?><font style="color:red;">*</font>
					<input type="text" name="hit_ups_auto_zip" id="hit_ups_auto_zip" value="<?php _e((isset($general_settings['hit_ups_auto_zip'])) ? $general_settings['hit_ups_auto_zip'] : '','hit_ups_auto'); ?>">
				</td>
				<td style="padding:10px;">
				<?php _e('Country of the Shipper from Address','hit_ups_auto') ?><font style="color:red;">*</font>
				<select name="hit_ups_auto_country" class="wc-enhanced-select" style="width:95%;padding:5px;">
						<?php foreach($countires as $key => $value)
						{
							if(isset($general_settings['hit_ups_auto_country']) && ($general_settings['hit_ups_auto_country'] == $key))
							{
								_e("<option value=".$key." selected='true'>".$value."</option>",'hit_ups_auto');
							}
							else
							{
								_e("<option value=".$key.">".$value."</option>",'hit_ups_auto');
							}
						} ?>
					</select>
				</td>
			</tr>

			<tr>
				<td style=" width: 50%;padding:10px;">
					<?php _e('GSTIN','hit_ups_auto') ?>
					<input type="text" name="hit_ups_auto_gstin" value="<?php _e((isset($general_settings['hit_ups_auto_gstin'])) ? $general_settings['hit_ups_auto_gstin'] : '','hit_ups_auto'); ?>">
				</td>
				<td style="padding:10px;">
					<?php _e('Vendor Collector type','hit_ups_auto') ?><font style="color:red;">*</font>
					<select name="hit_ups_auto_ven_col_type" class="wc-enhanced-select" style="width:95%;padding:5px;">
						<?php foreach($ven_col_type as $key => $value)
						{
							if(isset($general_settings['hit_ups_auto_ven_col_type']) && ($general_settings['hit_ups_auto_ven_col_type'] == $key))
							{
								_e("<option value=".$key." selected='true'>".$value."</option>",'hit_ups_auto');
							}
							else
							{
								_e("<option value=".$key.">".$value."</option>",'hit_ups_auto');
							}
						} ?>
					</select>
				</td>
			</tr>
			<tr>
				<td style=" width: 50%;padding:10px;">
					<?php _e('Vendor Collector ID','hit_ups_auto') ?>
					<input type="text" name="hit_ups_auto_ven_col_id" value="<?php _e((isset($general_settings['hit_ups_auto_ven_col_id'])) ? $general_settings['hit_ups_auto_ven_col_id'] : '','hit_ups_auto'); ?>">
				</td>
				<td style="padding:10px;">
					
				</td>
			</tr>
			<tr><td colspan="2" style="padding:10px;"><hr></td></tr>
		</table>
		<center><h2 class="fs-title">Are you gonna use Multi Vendor?</h2></center><br>
		<table style="padding-left:10px;padding-right:10px;">
			<td><span style="float:left;padding-right:10px;"><input type="checkbox" name="hit_ups_auto_v_enable" <?php _e((isset($general_settings['hit_ups_auto_v_enable']) && $general_settings['hit_ups_auto_v_enable'] == 'yes') ? 'checked="true"' : '','hit_ups_auto'); ?> value="yes" ><small style="color:gray"> Use Multi-Vendor.</small></span></td>
			<td><span style="float:right;padding-right:10px;"><input type="checkbox" name="hit_ups_auto_v_rates" <?php _e((isset($general_settings['hit_ups_auto_v_rates']) && $general_settings['hit_ups_auto_v_rates'] == 'yes') || ($initial_setup) ? 'checked="true"' : '','hit_ups_auto'); ?> value="yes" ><small style="color:gray"> Get rates from vendor address.</small></span></td>
			<td><span style="float:right;padding-right:10px;"><input type="checkbox" name="hit_ups_auto_v_labels" <?php _e((isset($general_settings['hit_ups_auto_v_labels']) && $general_settings['hit_ups_auto_v_labels'] == 'yes') || ($initial_setup) ? 'checked="true"' : '','hit_ups_auto'); ?> value="yes" ><small style="color:gray"> Create Label from vendor address.</small></span></td>
			<td><span style="float:right;padding-right:10px;"><input type="checkbox" name="hit_ups_auto_v_email" <?php _e((isset($general_settings['hit_ups_auto_v_email']) && $general_settings['hit_ups_auto_v_email'] == 'yes') ? 'checked="true"' : '','hit_ups_auto'); ?> value="yes" ><small style="color:gray"> Email the shipping labels to vendors.</small></span></td>
			</table>
		<table style="width:100%">
							
							
							<tr>
								<td style=" width: 50%;padding:10px;text-align:center;">
									<?php _e('Vendor role','hit_ups_auto') ?></h4><br>
									<select name="hit_ups_auto_v_roles[]" style="padding:5px;width:240px;">

										<?php foreach (get_editable_roles() as $role_name => $role_info){
											if(isset($general_settings['hit_ups_auto_v_roles']) && in_array($role_name, $general_settings['hit_ups_auto_v_roles'])){
												_e("<option value=".$role_name." selected='true'>".$role_info['name']."</option>",'hit_ups_auto');
											}else{
												_e("<option value=".$role_name.">".$role_info['name']."</option>",'hit_ups_auto');	
											}
											
										}
									?>

									</select><br>
									<small style="color:gray;"> To this role users edit page, you can find the new<br>fields to enter the ship from address.</small>
									
								</td>
							</tr>
							<tr><td style="padding:10px;"><hr></td></tr>
						</table>
		<?php if(isset($general_settings['hit_ups_auto_integration_key']) && $general_settings['hit_ups_auto_integration_key'] !=''){
			_e('<input type="submit" name="save" class="action-button save_change" style="width:auto;float:left;" value="Save Changes" />','hit_ups_auto');
		}

		?>
		<?php if (!$initial_setup) { ?>
		<input type="button" name="next" class="next action-button" value="Next" />
		<input type="button" name="previous" class="previous action-button" value="Previous" />
		<?php } ?>

    </fieldset>
	<fieldset <?php echo ($initial_setup) ? 'style="display:none"' : ''?>>
		<center><h2 class="fs-title">Choose Packing ALGORITHM</h2></center><br/>
		<table style="width:100%">
	
			<tr>
				<td style=" width: 50%;padding:10px;">
					<?php _e('Select Package Type','hit_ups_auto') ?>
				</td>
				<td style="padding:10px;">
					<select name="hit_ups_auto_packing_type" style="padding:5px; width:95%;" id = "hit_ups_auto_packing_type" class="wc-enhanced-select" style="width:153px;" onchange="changepacktype(this)">
						<?php foreach($packing_type as $key => $value)
						{
							if(isset($general_settings['hit_ups_auto_packing_type']) && ($general_settings['hit_ups_auto_packing_type'] == $key))
							{
								_e("<option value=".$key." selected='true'>".$value."</option>",'hit_ups_auto');
							}
							else
							{
								_e("<option value=".$key.">".$value."</option>",'hit_ups_auto');
							}
						} ?>
					</select>
				</td>
			</tr>
			<tr style=" display:none;" id="weight_based">
				<td style=" width: 50%;padding:10px;">
				<?php _e('What is the Maximum weight to one package? (Weight based shipping only)','hit_ups_auto') ?><font style="color:red;">*</font>
				</td>
				<td style="padding:10px;">
					<input type="number" name="hit_ups_auto_max_weight" placeholder="" value="<?php _e((isset($general_settings['hit_ups_auto_max_weight'])) ? $general_settings['hit_ups_auto_max_weight'] : '','hit_ups_auto'); ?>">
				</td>
			</tr>
		</table>
		
	<?php if(isset($general_settings['hit_ups_auto_integration_key']) && $general_settings['hit_ups_auto_integration_key'] !=''){
		_e('<input type="submit" name="save" class="action-button save_change" style="width:auto;float:left;" value="Save Changes" />','hit_ups_auto');
	}

	?>
	<?php if (!$initial_setup) { ?>
	<input type="button" name="next" class="next action-button" value="Next" />
	<input type="button" name="previous" class="previous action-button" value="Previous" />
	<?php } ?>

</fieldset>
<fieldset>
  <center><h2 class="fs-title">Rates</h2><br/>
  	<table style="padding-left:10px;padding-right:10px;">
	  <td><span style="float:left;padding-right:10px;"><input type="checkbox" name="hit_ups_auto_insure" <?php _e((isset($general_settings['hit_ups_auto_insure']) && $general_settings['hit_ups_auto_insure'] == 'yes') ? 'checked="true"' : '','hit_ups_auto'); ?> value="yes" ><small style="color:gray"> UPS Insurance</small></span></td>
		<td><span style="float:right;padding-right:10px;"><input type="checkbox" name="hit_ups_auto_account_rates" <?php _e((isset($general_settings['hit_ups_auto_account_rates']) && $general_settings['hit_ups_auto_account_rates'] == 'yes') || ($initial_setup) ? 'checked="true"' : '','hit_ups_auto'); ?> value="yes" ><small style="color:gray"> Fetch UPS account rates.</small></span></td>
		
		</table></center>

  	<table style="width:100%; <?php echo ($initial_setup) ? 'display:none;' : ''?>">
			
			<tr><td colspan="2" style="padding:10px;"><hr></td></tr>
			<tr><td colspan="2" style="padding:10px;"><center><h2 class="fs-title">Do you wants to exclude countries?</h2></center></td></tr>
				
			<tr>
				<td colspan="2" style="text-align:center;padding:10px;">
					<?php _e('Exclude Countries','hit_ups_auto') ?><br>
					<select name="hit_ups_auto_exclude_countries[]" multiple="true" style="padding:5px;width:270px;">

					<?php
					$general_settings['hit_ups_auto_exclude_countries'] = empty($general_settings['hit_ups_auto_exclude_countries'])? array() : $general_settings['hit_ups_auto_exclude_countries'];
					foreach ($countires as $key => $county){
						if(isset($general_settings['hit_ups_auto_exclude_countries']) && in_array($key,$general_settings['hit_ups_auto_exclude_countries'])){
							_e("<option value=".$key." selected='true'>".$county."</option>",'hit_ups_auto');
						}else{
							_e("<option value=".$key.">".$county."</option>",'hit_ups_auto');	
						}
						
					}
					?>

					</select>
				</td>
				<tr><td colspan="2" style="padding:10px;"><hr></td></tr>
				
			</tr>
			<tr>
				<td style=" width: 50%;padding:10px;">
					<?php _e('Customer Classification','hit_ups_auto') ?>
				</td>
				<td style="padding:10px;">
					<select name="hit_ups_auto_customer_classification" style="padding:5px; width:95%;" id = "hit_ups_auto_customer_classification" class="wc-enhanced-select" style="width:153px;">
						<?php foreach($classification as $key => $value)
						{
							if(isset($general_settings['hit_ups_auto_customer_classification']) && ($general_settings['hit_ups_auto_customer_classification'] == $key))
							{
								_e("<option value=".$key." selected='true'>".$value."</option>",'hit_ups_auto');
							}
							else
							{
								_e("<option value=".$key.">".$value."</option>",'hit_ups_auto');
							}
						} ?>
					</select>
				</td>
			</tr>			
			<tr><td colspan="2" style="padding:10px;"><hr></td></tr>
		</table>
				<center><h2 class="fs-title">Shipping Services & Price adjustment</h2></center>
				<table style="width:100%;">
				
					<tr>
						<td>
							<h3 style="font-size: 1.10em;"><?php _e('Carries','hit_ups_auto') ?></h3>
						</td>
						<td>
							<h3 style="font-size: 1.10em;"><?php _e('Alternate Name for Carrier','hit_ups_auto') ?></h3>
						</td>
						
					</tr>
							<?php foreach($_carriers as $key => $value)
							{
								if($key == 'ups_11'){
									_e(' <tr><td colspan="4" style="padding:10px;"><hr></td></tr><tr ><td colspan="4" style="text-align:center;"><div style="padding:10px;border:1px solid gray;"><b><u>INTERNATIONAL SERVICES</u><br>
									This all are the services provided by UPS to ship internationally.<br>
									
								</b></div></td></tr> <tr><td colspan="4" style="padding:10px;"><hr></td></tr>','hit_ups_auto');
								}else if($key == "ups_12"){
									_e(' <tr><td colspan="4" style="padding:10px;"><hr></td></tr><tr ><td colspan="4" style="text-align:center;"><div style="padding:10px;border:1px solid gray;"><b><u>DOMESTIC SERVICES</u><br>
										This all are the services provided by UPS to ship domestic.<br>
									</b></div>
									</td></tr> <tr><td colspan="4" style="padding:10px;"><hr></td></tr>','hit_ups_auto');
								}else if ($key == 'ups_92'){
									_e(' <tr><td colspan="4" style="padding:10px;"><hr></td></tr><tr ><td colspan="4" style="text-align:center;"><b><u>OTHER SERVICES</u><br>
										
									</b>
									</td></tr> <tr><td colspan="4" style="padding:10px;"><hr></td></tr>','hit_ups_auto');
								}
								$ser_to_enable = ["ups_03", "ups_02", "ups_13", "ups_11", "ups_07", "ups_65"];
								_e('	<tr>
										<td>
										<input type="checkbox" value="yes" name="hit_ups_auto_carrier['.$key.']" '. ((isset($general_settings['hit_ups_auto_carrier'][$key]) && $general_settings['hit_ups_auto_carrier'][$key] == 'yes') || ($initial_setup && in_array($key, $ser_to_enable)) ? 'checked="true"' : '') .' > <small>'.__($value,"hit_ups_auto").' - [ '.$key.' ]</small>
										</td>
										<td>
											<input type="text" name="hit_ups_auto_carrier_name['.$key.']" value="'.((isset($general_settings['hit_ups_auto_carrier_name'][$key])) ? __($general_settings['hit_ups_auto_carrier_name'][$key],"hit_ups_auto") : '').'">
										</td>
										</tr>','hit_ups_auto');
							} ?>
							 <tr><td colspan="4" style="padding:10px;"><hr></td></tr>
				</table>
				<?php if(isset($general_settings['hit_ups_auto_integration_key']) && $general_settings['hit_ups_auto_integration_key'] !=''){
					_e('<input type="submit" name="save" class="action-button save_change" style="width:auto;float:left;" value="Save Changes" />','hit_ups_auto');
				}

				?>
			<?php if (!$initial_setup) { ?>
			<input type="button" name="next" class="next action-button" value="Next" />
  			<input type="button" name="previous" class="previous action-button" value="Previous" />
			<?php } ?>
	
 </fieldset>
 <fieldset>
 <center><h2 class="fs-title">Configure Shipping Label</h2><br/>
 <table style="padding-left:10px;padding-right:10px;">

		
		<td><span style="float:right;padding-right:10px;"><input type="checkbox" name="hit_ups_auto_uostatus" <?php _e((isset($general_settings['hit_ups_auto_uostatus']) && $general_settings['hit_ups_auto_uostatus'] == 'yes') ? 'checked="true"' : '','hit_ups_auto'); ?> value="yes" ><small style="color:gray"> Update order status by tracking</small></span></td>
		<td><span style="float:right;padding-right:10px;"><input type="checkbox" name="hit_ups_auto_trk_status_cus" <?php _e((isset($general_settings['hit_ups_auto_trk_status_cus']) && $general_settings['hit_ups_auto_trk_status_cus'] == 'yes') ? 'checked="true"' : '','hit_ups_auto'); ?> value="yes" ><small style="color:gray"> UPS tracking informations to Customers</small></span></td>
        <td><span style="float:right;padding-right:10px;"><input type="checkbox" name="hit_ups_add_shipping_invoice" <?php _e((isset($general_settings['hit_ups_add_shipping_invoice']) && $general_settings['hit_ups_add_shipping_invoice'] == 'yes') ? 'checked="true"' : '','hit_ups_auto'); ?> value="yes" ><small style="color:gray">Add Shipping Cost to invoice</small></span></td>
		</table>
		
		</center>
  <table style="width:100%">
  	<tr><td colspan="2" style="padding:10px;"><hr></td></tr>
		
	  <tr>
	  		<td style=" width: 50%;padding:10px;">
				<?php _e('Shipment Content','hit_ups_auto') ?>
				<input type="text" name="hit_ups_auto_ship_content" placeholder="" value="<?php _e((isset($general_settings['hit_ups_auto_ship_content'])) ? $general_settings['hit_ups_auto_ship_content'] : '','hit_ups_auto'); ?>">
			</td>
			<td style="padding:10px;">
				<?php _e('Shipping Label Format','hit_ups_auto') ?>
				<select name="hit_ups_auto_print_size" style="width:95%;padding:5px;">
					<?php foreach($print_size as $key => $value)
					{
						if(isset($general_settings['hit_ups_auto_print_size']) && ($general_settings['hit_ups_auto_print_size'] == $key))
						{
							_e("<option value=".$key." selected='true'>".$value."</option>",'hit_ups_auto');
						}
						else
						{
							_e("<option value=".$key.">".$value."</option>",'hit_ups_auto');
						}
					} ?>
				</select>
			</td>
		</tr>
		
		<tr>
			<td style=" width: 50%;padding:10px;">
			<?php _e('Email address to sent Shipping label','hit_ups_auto') ?>
			<input type="text" name="hit_ups_auto_label_email" placeholder="" value="<?php _e((isset($general_settings['hit_ups_auto_label_email'])) ? $general_settings['hit_ups_auto_label_email'] : '','hit_ups_auto'); ?>"><br>
			<small style="color:gray;"> While creating the shipping label at the Shipi, It will sent the label, invoice to the given e-mail. If you don't need this then leave this field empty.</small>
			</td>
		
			<td style="padding:10px;">
				<?php _e('Who will pay the duty payment','hit_ups_auto') ?><font style="color:red;">*</font>
				<select name="hit_ups_auto_duty_payment" style="width:95%;padding:5px;">
					<?php foreach($duty_payment_type as $key => $value)
					{
						if(isset($general_settings['hit_ups_auto_duty_payment']) && ($general_settings['hit_ups_auto_duty_payment'] == $key))
						{
							_e("<option value=".$key." selected='true'>".$value."</option>",'hit_ups_auto');
						}
						else
						{
							_e("<option value=".$key.">".$value."</option>",'hit_ups_auto');
						}
					} ?>
				</select><br>
			</td>
		</tr>
		<tr>
			<td style="width: 50%;padding:10px;">
				<?php _e('Delivery Confirmation','hit_ups_auto') ?>
				<select name="hit_ups_auto_del_con" style="width:95%;padding:5px;">
					<?php foreach($del_con_type as $key => $value)
					{
						if(isset($general_settings['hit_ups_auto_del_con']) && ($general_settings['hit_ups_auto_del_con'] == $key))
						{
							_e("<option value=".$key." selected='true'>".$value."</option>",'hit_ups_auto');
						}
						else
						{
							_e("<option value=".$key.">".$value."</option>",'hit_ups_auto');
						}
					} ?>
				</select><br>
			</td>
			<td style="padding:10px;">
				
			</td>
		</tr>

		<tr><td colspan="2" style="padding:10px;"><hr></td></tr>
		</table>
			<!-- // SHIPPING LABEL AUTOMATION -->

	<center <?php echo ($initial_setup) ? 'style="display:none"' : ''?>><h2 class="fs-title">SHIPPING LABEL AUTOMATION</h2><br/>
  	<table style="padding-left:10px;padding-right:10px;">
		<tr>
			<small style="color:red; text-align: justify;">Note: </small><small style="color:gray;">When "Create Label automatically" is chosen then the default shipping services chosen from here will be used to generate labels automatically for the orders placed using other service.</small>
		</tr>
			<tr>
				<td style=" width: 50%;padding:10px;">
					<p> <span class="" ></span>	<?php _e('Default Domestic Service','a2z_dhlexpress') ?><p>
				</td>
				<td style="padding:10px;">
					<select name="hit_ups_Domestic_service" style="padding:5px; width:95%;" id = "hit_ups_Domestic_service" class="wc-enhanced-select" style="width:153px;" onchange="changepacktype(this)">
					<option value="null" selected ='true'>No option</option>
						<?php 
							foreach($Domestic_service as $key => $values){
								if(isset($general_settings['hit_ups_Domestic_service']) && $general_settings['hit_ups_Domestic_service'] == $key)
								{
									_e("<option value=".$key." selected ='true'>".$values."-[".$key."]"."</option>",'hit_ups_auto');
								}
								else{
									_e("<option value=".$key.">".$values."-[".$key."]"."</option>",'hit_ups_auto');
								}
							}
						?>
					</select>
				</td>
			</tr>
			<tr>
				<td style=" width: 50%;padding:10px;">
					<p> <span class="" ></span>	<?php _e('Default International Service','a2z_dhlexpress') ?></p>
				</td>
				<td style="padding:10px;">
					<select name="hit_ups_international_service" style="padding:5px; width:95%;" id = "hit_ups_international_service" class="wc-enhanced-select" style="width:153px;" onchange="changepacktype(this)">
					<option value="null" selected ='true'>No option</option>
						<?php									
							foreach($international_service as $key => $values){
								if(isset($general_settings['hit_ups_international_service']) && $general_settings['hit_ups_international_service'] == $key)
								{
									_e("<option value=".$key." selected ='true'>".$values."-[".$key."]"."</option>",'hit_ups_auto');
								}
								else{
									_e("<option value=".$key.">".$values."-[".$key."]"."</option>",'hit_ups_auto');
								}
							}
						
							?>
							
					</select>
				</td>
			</tr>						
	</table>
		</center>
		
		
		<?php if(isset($general_settings['hit_ups_auto_integration_key']) && $general_settings['hit_ups_auto_integration_key'] !=''){
			_e('<input type="submit" name="save" class="action-button save_change" style="width:auto;float:left;" value="Save Changes" />','hit_ups_auto');
		}

		?>
		<?php if (!$initial_setup) { ?>
		<input type="button" name="next" class="next action-button" value="Next" />
		<input type="button" name="previous" class="previous action-button" value="Previous" />
		<?php } ?>
	
 </fieldset>
  <?php } 
?>
<fieldset>
    <center><h2 class="fs-title">LINK Shipi</h2><br>
	<img src="<?php _e(plugin_dir_url(__FILE__),'hit_ups_auto'); ?>ups_logo.png">
	<h3 class="fs-subtitle">Shipi is performing all the operations in its own server. So it won't affect your page speed or server usage.</h3>
	<?php 
		if(!isset($general_settings['hit_ups_auto_integration_key']) || empty($general_settings['hit_ups_auto_integration_key'])){
		?>
		<input type="radio" name="shipo_link_type" id="WITHOUT" value="WITHOUT" checked>I don't have Shipi account  &nbsp; &nbsp; &nbsp;
		<input type="radio" name="shipo_link_type" id="WITH" value="WITH">I have Shipi integration key
<br><hr>
		<table class="with_shipo_acc" style="width:100%;text-align:center;display: none;">
		<tr>
			<td style="width: 50%;padding:10px;">
				<?php _e('Enter Intergation Key', 'hit_ups_auto') ?><font style="color:red;">*</font><br>
				
				<input type="text" style="width:330px;" class="intergration" id="shipo_intergration"  name="hit_ups_auto_integration_key" value="">
			</td>
		</tr>
	</table>
	<table class="without_shipo_acc" style="padding-left:10px;padding-right:10px;">
		<td><span style="float:left;padding-right:10px;"><input type="checkbox" name="hitshippo_ups_track_audit" <?php _e((isset($general_settings['hitshippo_ups_track_audit']) && $general_settings['hitshippo_ups_track_audit'] == 'yes') ? 'checked="true"' : '','hit_ups_auto'); ?> value="yes" ><small style="color:gray"> Track shipments everyday & Update the order status.</small></span></td>
		<td><span style="float:right;padding-right:10px;"><input type="checkbox" name="hitshippo_ups_daily_report" <?php _e((isset($general_settings['hitshippo_ups_daily_report']) && $general_settings['hitshippo_ups_daily_report'] == 'yes') ? 'checked="true"' : '','hit_ups_auto'); ?> value="yes" ><small style="color:gray"> Daily Report.</small></span></td>
		<td><span style="float:right;padding-right:10px;"><input type="checkbox" name="hitshippo_ups_monthly_report" <?php _e((isset($general_settings['hitshippo_ups_monthly_report']) && $general_settings['hitshippo_ups_monthly_report'] == 'yes') ? 'checked="true"' : '','hit_ups_auto'); ?> value="yes" ><small style="color:gray"> Monthly Report.</small></span></td>
		</table></center>
    <table class="without_shipo_acc" style="width:100%;text-align:center;">
	<tr><td style="padding:10px;"></td></tr>
	
	<tr>
		<td style=" width: 50%;padding:10px;">
			<?php _e('Email address to signup / check the registered email.','hit_ups_auto') ?><font style="color:red;">*</font><br>
			<input type="email" style="width:330px;" id="shipo_mail" placeholder="Enter email address" name="hitshippo_ups_shipo_signup"  placeholder="" value="<?php _e((isset($general_settings['hitshippo_ups_shipo_signup'])) ? $general_settings['hitshippo_ups_shipo_signup'] : '','hit_ups_auto'); ?>">
		</td>
	</tr>
	<tr>
		<td style=" width: 50%;padding:10px;">
			<?php _e('Enter Password') ?><font style="color:red;">*</font><br>
				<input type="password" style="width:330px;" id="shipo_password" placeholder="Enter Password" name="hitshippo_ups_shipo_password"  placeholder="" value="">
			</td>
		</tr>
	
	<tr><td style="padding:10px;"><hr></td></tr>
	
	</table>

	<?php }else{
		?>
		<tr>
				<td style="padding:10px;">
					<?php _e('Shipi Intergation Key', 'hit_ups_auto') ?><br><br>
				</td>
			</tr>
			<tr>
				<td><span style="padding-right:10px; text-align:center;"><input type="checkbox" id='intergration_ckeck_box'><small style="color:gray">Edit intergration key</small></span></td>
			</tr>
			<tr>
				<td>
					<input style="width:24%; text-align:center; pointer-events:none;" required type="text" id="intergration" name="hit_ups_auto_integration_key" placeholder="" value="<?php _e((isset($general_settings['hit_ups_auto_integration_key'])) ? $general_settings['hit_ups_auto_integration_key'] : '','hit_ups_auto'); ?>">
				</td>
			</tr>
		<p style="font-size:14px;line-height:24px;">
			Site Linked Successfully. <br><br>
		It's great to have you here. Your account has been linked successfully with Shipi. <br><br>
Make your customers happier by reacting faster and handling their service requests in a timely manner, meaning higher store reviews and more revenue.</p>
		<?php
		_e('</center>','hit_ups_auto');
	}
	?>
	<?php _e('<center>' . $error . '</center>','hit_ups_auto'); ?>
	
	<?php if(!isset($general_settings['hit_ups_auto_integration_key']) || empty($general_settings['hit_ups_auto_integration_key'])){
					_e('<input type="submit" name="save" class="action-button save_change" style="width:auto;" value="SAVE & START " />','hit_ups_auto');
					if (!$initial_setup) {
						if (empty($error)) {
						_e('<input type="button" name="previous" class="previous action-button" value="Previous" />','hit_ups_auto');
						}else {
							_e( '<button type="button" style="padding:11px;" name="previous" class="previous action-button"  onclick="location.reload();">Previous</button>');
						}
					}
				 }else{
					 _e('<input type="submit" name="save"  class="action-button save_change" style="width:auto;" value="Save Changes" />','hit_ups_auto');
					 _e('<input type="button" name="previous" class="previous action-button" value="Previous" />','hit_ups_auto');
				
				
  }
  ?>
  
  </fieldset>
<?php
} ?>
</form>
<center><a href="https://app.myshipi.com/support" target="_blank" style="width:auto;margin-right :20px;" class="button button-primary">Trouble in configuration? / not working? Email us.</a>
<a href="https://meetings.hubspot.com/hitshipo" target="_blank" style="width:auto;" class="button button-primary">Looking for demo ? Book your slot with our expert</a></center>

<script type="text/javascript">
var current_fs, next_fs, previous_fs;
var left, opacity, scale;
var animating;
jQuery(".next").click(function () {
  if (animating) return false;
  animating = true;

  current_fs = jQuery(this).parent();
  next_fs = jQuery(this).parent().next();
  jQuery("#progressbar li").eq(jQuery("fieldset").index(next_fs)).addClass("active");
  next_fs.show();
  document.body.scrollTop = 0; // For Safari
  document.documentElement.scrollTop = 0; 
  current_fs.animate(
    { opacity: 0 },
    {
      step: function (now, mx) {
        scale = 1 - (1 - now) * 0.2;
        left = now * 50 + "%";
        opacity = 1 - now;
        current_fs.css({
          transform: "scale(" + scale + ")"});
        next_fs.css({ left: left, opacity: opacity });
      },
      duration: 0,
      complete: function () {
        current_fs.hide();
        animating = false;
      },
      //easing: "easeInOutBack"
    }
  );
});

jQuery(".previous").click(function () {
  if (animating) return false;
  animating = true;

  current_fs = jQuery(this).parent();
  previous_fs = jQuery(this).parent().prev();
  jQuery("#progressbar li")
    .eq(jQuery("fieldset").index(current_fs))
    .removeClass("active");

  previous_fs.show();
  current_fs.animate(
    { opacity: 0 },
    {
      step: function (now, mx) {
        scale = 0.8 + (1 - now) * 0.2;
        left = (1 - now) * 50 + "%";
        opacity = 1 - now;
        current_fs.css({ left: left });
        previous_fs.css({
          transform: "scale(" + scale + ")",
          opacity: opacity
        });
      },
      duration: 0,
      complete: function () {
        current_fs.hide();
        animating = false;
      },
      //easing: "easeInOutBack"
    }
  );
});

jQuery(".submit").click(function () {
  return false;
});

function changepacktype(selectbox){

	var weight = document.getElementById("weight_based");
	var box_type = selectbox.value;
	if(box_type == "weight_based"){			
		weight.style.display = "table-row";
	}else{
		weight.style.display = "none";
	}
	
}
	var box_type = jQuery('#hit_ups_auto_packing_type').val();
	// var weight = document.getElementById("weight_based");
	
	if (box_type != "weight_based") {
		jQuery('#weight_based').hide();
		// weight.style.display = "none";
	}else{
		// weight.style.display = "table-row";
		jQuery('#weight_based').show();
	}

jQuery(document).ready(function(){
	var api_type = jQuery("input[name='hit_ups_auto_api_type']:checked").val();
	if (api_type == "REST") {
		jQuery('.SOAP_auth').attr('hidden', 'hidden');
		jQuery('.REST_auth').removeAttr('hidden');
	} else {
		jQuery('.SOAP_auth').removeAttr('hidden');
		jQuery('.REST_auth').attr('hidden', 'hidden');
	}
	jQuery("input[name='hit_ups_auto_api_type']").change(function() {
		if (this.value == "REST") {
			jQuery('.SOAP_auth').attr('hidden', 'hidden');
			jQuery('.REST_auth').removeAttr('hidden');
		} else {
			jQuery('.SOAP_auth').removeAttr('hidden');
			jQuery('.REST_auth').attr('hidden', 'hidden');
		}
	});
    if('#checkAll'){
    	jQuery('#checkAll').on('click',function(){
            jQuery('.ups_auto_service').each(function(){
                this.checked = true;
            });
    	});
    }
    if('#uncheckAll'){
		jQuery('#uncheckAll').on('click',function(){
            jQuery('.ups_auto_service').each(function(){
                this.checked = false;
            });
    	});
	}

	jQuery("#hit_ups_auto_cod").change(function() {
		if(this.checked) {
	        jQuery('#col_type').show();
	    }else{
	    	jQuery('#col_type').hide();
	    }
	});
	jQuery(".save_change").click(function () {
		var side_id = jQuery('#hit_ups_auto_site_id').val();
		var side_pwd = jQuery('#hit_ups_auto_site_pwd').val();
		var acc_no = jQuery('#hit_ups_auto_acc_no').val();
		var access_key = jQuery('#hit_ups_auto_access_key').val();
		var shipper_name = jQuery('#hit_ups_auto_shipper_name').val();
		var auto_company = jQuery('#hit_ups_auto_company').val();
		var auto_mob_num = jQuery('#hit_ups_auto_mob_num').val();
		var auto_email = jQuery('#hit_ups_auto_email').val();
		var auto_address1 = jQuery('#hit_ups_auto_address1').val();
		var auto_city = jQuery('#hit_ups_auto_city').val();
		var auto_state = jQuery('#hit_ups_auto_state').val();
		var auto_zip = jQuery('#hit_ups_auto_zip').val();
		var shipo_mail = jQuery('#shipo_mail').val();
		var shipo_password = jQuery('#shipo_password').val();
		var shipo_intergration = jQuery('#shipo_intergration').val();
		var rest_c_id = jQuery("#hit_ups_auto_rest_site_id").val();
		var rest_c_secret = jQuery("#hit_ups_auto_rest_site_pwd").val();
		var rest_acc_no = jQuery("#hit_ups_auto_rest_acc_no").val();
		var rest_grant_type = jQuery("#hit_ups_auto_rest_grant_type").val();
		var api_type = jQuery("input[name='hit_ups_auto_api_type']:checked").val();

		if (api_type == "REST") {
			if(rest_c_id == ''){
				alert("UPS Client ID is Empty");
				return false;
			};
			if(rest_c_secret == ''){
				alert("UPS Client Secret is Empty");
				return false;
			};
			if(rest_acc_no == ''){
				alert("UPS Account number is Empty");
				return false;
			};
		} else {
			if(side_id == ''){
				alert("UPS Login User ID is Empty");
				return false;
			};
			if(side_pwd == ''){
				alert("UPS Login Password is Empty");
				return false;
			};
			if(acc_no == ''){
				alert("UPS Account number is Empty");
				return false;
			};
			if(access_key == ''){
				alert("UPS Access Key is Empty");
				return false;
			};
		}
		if(shipper_name == ''){
			alert("Shipper Name is Empty");
			return false;
		};
		if(auto_company == ''){
			alert("Company Name is Empty");
			return false;
		};
		if(auto_mob_num == ''){
			alert("Shipper Mobile / Contact Number is Empty");
			return false;
		};
		if(auto_email == ''){
			alert("Email Address of the Shipper is Empty");
			return false;
		};
		if(auto_address1 == ''){
			alert("Address Line 1 is Empty");
			return false;
		};
		if(auto_city	 == ''){
			alert("City of the Shipper is Empty");
			return false;
		};
		if(auto_state == ''){
			alert("State is Empty");
			return false;
		};
		if(auto_zip == ''){
			alert("Postal/Zip Code is Empty");
			return false;
		};
		var link_type = jQuery("input[name='shipo_link_type']:checked").val();
			if (link_type === 'WITHOUT') {
				if(shipo_mail == ''){
						alert('Enter Shipi Email');
						return false;
					}
					if(shipo_password == ''){
						alert('Enter Shipi Password');
						return false;
					}
			} else {
				if(shipo_intergration == ''){
						alert('Enter Shipi intergtraion Key');
						return false;
					}
			}
		
	});

});
jQuery(function () {
	jQuery("#intergration_ckeck_box").click(function () {
            if (jQuery(this).is(":checked")) {
                jQuery("#intergration").css("pointer-events", "auto");
            } else {
				jQuery("#intergration").css("pointer-events", "none");
            }
        });
    });
	jQuery(document).ready(function() {
		jQuery("input[name='shipo_link_type']").change(function() {
			if (jQuery(this).val() == "WITHOUT") {
				jQuery(".without_shipo_acc").show();
				jQuery(".with_shipo_acc").hide();
			} else if (jQuery(this).val() == "WITH") {
				jQuery(".without_shipo_acc").hide();
				jQuery(".with_shipo_acc").show();
			}
		});
	});
</script>
<!--Start of Tawk.to Script-->
<script type="text/javascript">
var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();
(function(){
var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
s1.async=true;
s1.src='https://embed.tawk.to/671925bb4304e3196ad6b676/1iat3mpss';
s1.charset='UTF-8';
s1.setAttribute('crossorigin','*');
s0.parentNode.insertBefore(s1,s0);
})();
</script>
<!--End of Tawk.to Script-->