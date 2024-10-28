<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
if ( ! class_exists( 'hit_ups_auto' ) ) {
    class hit_ups_auto extends WC_Shipping_Method {
        /**
         * Constructor for your shipping class
         *
         * @access public
         * @return void
         */
        public $hpos_enabled = false;
        public function __construct() {
            $this->id                 = 'hit_ups_auto';
			$this->method_title       = __( 'UPS' );  // Title shown in admin
			$this->title       = __( 'UPS' );
            $this->method_description = __( '' ); // 
            $this->enabled            = "yes"; // This can be added as an setting but for this example its forced enabled
            $this->init();
            if (get_option("woocommerce_custom_orders_table_enabled") === "yes") {
 		        $this->hpos_enabled = true;
 		    }
        }

        /**
         * Init your settings
         *
         * @access public
         * @return void
         */
        function init() {
            // Load the settings API
            $this->init_form_fields(); // This is part of the settings API. Override the method to add your own settings
            $this->init_settings(); // This is part of the settings API. Loads settings you previously init.

            // Save settings in admin if you have any defined
            add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
			
        }
        
        /**
         * calculate_shipping function.
         *
         * @access public
         * @param mixed $package
         * @return void
         */
        public function calculate_shipping( $package = array() ) {
        	
  			 $execution_status = get_option('hitshipo_ups_working_status');
   			if(!empty($execution_status)){
	  			 if($execution_status == 'stop_working'){
		  			 return;
	   			}
   			}
			$pack_aft_hook = apply_filters('hit_ups_auto_rate_packages', $package);
			
			$general_settings = get_option('hit_ups_auto_main_settings');
			$general_settings = empty($general_settings) ? array() : $general_settings;
			
			if(!is_array($general_settings)){
				return;
			}

			

			//excluded Countries
			if(isset($general_settings['hit_ups_auto_exclude_countries'])){

				if(in_array($pack_aft_hook['destination']['country'],$general_settings['hit_ups_auto_exclude_countries'])){
					return;
				}
				}

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
			
			$custom_settings = array();
			$custom_settings['default'] = array(
												'hit_ups_auto_site_id' 			=> 	isset($general_settings['hit_ups_auto_site_id'])?$general_settings['hit_ups_auto_site_id']:'',
												'hit_ups_auto_site_pwd'			=> 	isset($general_settings['hit_ups_auto_site_pwd'])?$general_settings['hit_ups_auto_site_pwd']:'',
												'hit_ups_auto_acc_no' 			=> 	isset($general_settings['hit_ups_auto_acc_no'])?$general_settings['hit_ups_auto_acc_no']:'',
												'hit_ups_auto_access_key' 		=> 	isset($general_settings['hit_ups_auto_access_key'])?$general_settings['hit_ups_auto_access_key']:'',
												'hit_ups_auto_rest_site_id' 	=>	isset($general_settings['hit_ups_auto_rest_site_id'])?$general_settings['hit_ups_auto_rest_site_id']:'',
												'hit_ups_auto_rest_site_pwd' 	=>	isset($general_settings['hit_ups_auto_rest_site_pwd'])?$general_settings['hit_ups_auto_rest_site_pwd']:'',
												'hit_ups_auto_rest_acc_no' 		=>	isset($general_settings['hit_ups_auto_rest_acc_no'])?$general_settings['hit_ups_auto_rest_acc_no']:'',
												'hit_ups_auto_rest_grant_type' 	=>	isset($general_settings['hit_ups_auto_rest_grant_type'])?$general_settings['hit_ups_auto_rest_grant_type']:'',
												'hit_ups_auto_shipper_name' 	=> 	isset($general_settings['hit_ups_auto_shipper_name'])?$general_settings['hit_ups_auto_shipper_name']:'',
												'hit_ups_auto_company' 			=> 	isset($general_settings['hit_ups_auto_company'])?$general_settings['hit_ups_auto_company']:'',
												'hit_ups_auto_mob_num' 			=> 	isset($general_settings['hit_ups_auto_mob_num'])?$general_settings['hit_ups_auto_mob_num']:'',
												'hit_ups_auto_email' 			=> 	isset($general_settings['hit_ups_auto_email'])?$general_settings['hit_ups_auto_email']:'',
												'hit_ups_auto_address1' 		=> 	isset($general_settings['hit_ups_auto_address1'])?$general_settings['hit_ups_auto_address1']:'',
												'hit_ups_auto_address2' 		=> 	isset($general_settings['hit_ups_auto_address2'])?$general_settings['hit_ups_auto_address2']:'',
												'hit_ups_auto_city' 			=> 	isset($general_settings['hit_ups_auto_city'])?$general_settings['hit_ups_auto_city']:'',
												'hit_ups_auto_state' 			=> 	isset($general_settings['hit_ups_auto_state'])?$general_settings['hit_ups_auto_state']:'',
												'hit_ups_auto_zip' 				=> 	isset($general_settings['hit_ups_auto_zip'])?$general_settings['hit_ups_auto_zip']:'',
												'hit_ups_auto_country' 			=> 	isset($general_settings['hit_ups_auto_country'])?$general_settings['hit_ups_auto_country']:'',
												'hit_ups_auto_gstin' 			=> 	isset($general_settings['hit_ups_auto_gstin'])?$general_settings['hit_ups_auto_gstin']:'',
												'hit_ups_auto_con_rate' 		=> 	isset($general_settings['hit_ups_auto_con_rate'])?$general_settings['hit_ups_auto_con_rate']:'',
											);
			$vendor_settings = array();
			$des_coun = $pack_aft_hook['destination']['country'];
			$orderCurrency = $value[$des_coun]['currency'];	
			if(isset($general_settings['hit_ups_auto_v_enable']) && $general_settings['hit_ups_auto_v_enable'] == 'yes' && isset($general_settings['hit_ups_auto_v_rates']) && $general_settings['hit_ups_auto_v_rates'] == 'yes'){
				// Multi Vendor Enabled
				foreach ($pack_aft_hook['contents'] as $key => $value) {
					
					$product_id = $value['product_id'];
					if ($this->hpos_enabled) {
					    $hpos_prod_data = wc_get_product($product_id);
					    $ups_account = $hpos_prod_data->get_meta("hit_ups_auto_address");
					} else {
						$ups_account = get_post_meta($product_id,'hit_ups_auto_address', true);
					}
				
					if(empty($ups_account)||$ups_account=="default"){
						$ups_account = 'default';
						$vendor_settings[$ups_account] = $custom_settings['default'];
						$vendor_settings[$ups_account]['products'][] = $value;
					}

					if($ups_account != 'default'){
						$user_account = get_post_meta($ups_account,'hit_ups_auto_vendor_settings', true);
						
						$user_account = empty($user_account) ? array() : $user_account;
						if(!empty($user_account)){
							if(!isset($vendor_settings[$ups_account])){

								$vendor_settings[$ups_account] = $custom_settings['default'];
								
								if($user_account['hit_ups_auto_site_id'] != ''){
									$vendor_settings[$ups_account]['hit_ups_auto_site_id'] = $user_account['hit_ups_auto_site_id'];

									if($user_account['hit_ups_auto_site_pwd'] != ''){
										$vendor_settings[$ups_account]['hit_ups_auto_site_pwd'] = $user_account['hit_ups_auto_site_pwd'];
									}

									if($user_account['hit_ups_auto_acc_no'] != ''){
										$vendor_settings[$ups_account]['hit_ups_auto_acc_no'] = $user_account['hit_ups_auto_acc_no'];
									}
									if($user_account['hit_ups_auto_access_key'] != ''){
										$vendor_settings[$ups_account]['hit_ups_auto_access_key'] = $user_account['hit_ups_auto_access_key'];
									}
								}
								// REST credentials
								$vendor_settings[$ups_account]['hit_ups_auto_rest_site_id'] = isset($user_account['hit_ups_auto_rest_site_id']) ? $user_account['hit_ups_auto_rest_site_id'] : "";
								$vendor_settings[$ups_account]['hit_ups_auto_rest_site_pwd'] = isset($user_account['hit_ups_auto_rest_site_pwd']) ? $user_account['hit_ups_auto_rest_site_pwd'] : "";
								$vendor_settings[$ups_account]['hit_ups_auto_rest_acc_no'] = isset($user_account['hit_ups_auto_rest_acc_no']) ? $user_account['hit_ups_auto_rest_acc_no'] : "";
								$vendor_settings[$ups_account]['hit_ups_auto_rest_grant_type'] = isset($user_account['hit_ups_auto_rest_grant_type']) ? $user_account['hit_ups_auto_rest_grant_type'] : "client_credentials";

								if($user_account['hit_ups_auto_shipper_name'] != ''){
									$vendor_settings[$ups_account]['hit_ups_auto_shipper_name'] = $user_account['hit_ups_auto_shipper_name'];
								}

								if($user_account['hit_ups_auto_company'] != ''){
									$vendor_settings[$ups_account]['hit_ups_auto_company'] = $user_account['hit_ups_auto_company'];
								}

								if($user_account['hit_ups_auto_mob_num'] != ''){
									$vendor_settings[$ups_account]['hit_ups_auto_mob_num'] = $user_account['hit_ups_auto_mob_num'];
								}

								if($user_account['hit_ups_auto_email'] != ''){
									$vendor_settings[$ups_account]['hit_ups_auto_email'] = $user_account['hit_ups_auto_email'];
								}

								$vendor_settings[$ups_account]['hit_ups_auto_address2'] = $user_account['hit_ups_auto_address2'];
								
								if($user_account['hit_ups_auto_city'] != ''){
									$vendor_settings[$ups_account]['hit_ups_auto_city'] = $user_account['hit_ups_auto_city'];
								}


								if($user_account['hit_ups_auto_state'] != ''){
									$vendor_settings[$ups_account]['hit_ups_auto_state'] = $user_account['hit_ups_auto_state'];
								}


								if($user_account['hit_ups_auto_zip'] != ''){
									$vendor_settings[$ups_account]['hit_ups_auto_zip'] = $user_account['hit_ups_auto_zip'];
								}

								if($user_account['hit_ups_auto_country'] != ''){
									$vendor_settings[$ups_account]['hit_ups_auto_country'] = $user_account['hit_ups_auto_country'];
								}

								$vendor_settings[$ups_account]['hit_ups_auto_gstin'] = $user_account['hit_ups_auto_gstin'];

								$vendor_settings[$ups_account]['hit_ups_auto_con_rate'] = $user_account['hit_ups_auto_con_rate'];
								
							}

							$vendor_settings[$ups_account]['products'][] = $value;
						}
					}

				}

			}

			if(empty($vendor_settings)){
				$custom_settings['default']['products'] = $pack_aft_hook['contents'];
			}else{
				$custom_settings = $vendor_settings;
			}


			$mesage_time = date('c');
			$message_date = date('Y-m-d');
			$weight_unit = $dim_unit = '';
			if(!empty($general_settings['hit_ups_auto_weight_unit']) && $general_settings['hit_ups_auto_weight_unit'] == 'KG_CM') {
				$weight_unit = 'KG';
				$dim_unit = 'CM';
			} else {
				$weight_unit = 'LB';
				$dim_unit = 'IN';
			}

			if(!isset($general_settings['hit_ups_auto_packing_type'])){
				return;
			}
			//.....
			$woo_weg_unit = get_option('woocommerce_weight_unit');
				
				$config_weg_unit = $general_settings['hit_ups_auto_weight_unit'];
				$mod_weg_unit = (!empty($config_weg_unit) && $config_weg_unit == 'LB_IN') ? 'lbs' : 'kg';
				
			if(isset($general_settings['hit_ups_auto_rates']) && $general_settings['hit_ups_auto_rates'] == 'yes' && isset($pack_aft_hook['destination']['country']) && !empty($pack_aft_hook['destination']['country']))
			{
				foreach ($custom_settings as $key => $value) {
				
					$shipping_rates[$key] = array();
					$surcharge = [];
				
				if ( ( "US" == $des_coun ) && ( "PR" == $des_coun ) ) {		
						$destination_country = "PR";
				}
				
				$weight_unit = $dim_unit = '';
				if(!empty($general_settings['hit_ups_auto_weight_unit']) && $general_settings['hit_ups_auto_weight_unit'] == 'KG_CM')
				{
					$weight_unit = 'KGS';
					$dim_unit = 'CM';
				}
				else
				{
					$weight_unit = 'LBS';
					$dim_unit = 'IN';
				}
				$total_weight_for_request = 0;
				if ($pack_aft_hook) {
				foreach ($pack_aft_hook['contents'] as $parcel) {
						
						$quantity = $parcel['quantity'];
						for($i=0;$i<$quantity; $i++)
						{
							$product = $parcel['data']->get_data();
							if ( isset($parcel['variation']) && !empty($parcel['variation']) ) {
								$default_attributes = $parcel['data']->get_parent_data();
								$total_weight = !empty($product['weight'])?$product['weight'] : $default_attributes['weight'];
							}else{
							$total_weight   =(string) $product['weight'];
							}
							$total_weight   = str_replace(',','.',$total_weight);
							if($total_weight<0.001){
								$total_weight = 0.001;
							}else{
								$total_weight = round((float)$total_weight,3);
							}
							$total_weight_for_request += (float)round(wc_get_weight($total_weight,$mod_weg_unit,$woo_weg_unit),2);
						}
					}
				}
				
				$packages = $this->hit_get_ups_packages($value['products'],$general_settings,$orderCurrency);
				$order_total = 0;
				foreach ($pack_aft_hook['contents'] as $item_id => $values) {
					$order_total += (float) $values['line_subtotal'];
				}
				if (isset($general_settings['hit_ups_auto_api_type']) && ($general_settings['hit_ups_auto_api_type'] == "REST") && isset($value['hit_ups_auto_rest_site_id'])) {
					$auth_token = get_transient("hitshipo_ups_rest_auth_token_".$key);
					if (!class_exists("ups_rest")) {
						include_once("ups_rest_main.php");
					}
					$ups_rest_obj = new ups_rest();
					$ups_rest_obj->mode = (isset($general_settings['hit_ups_auto_test']) && $general_settings['hit_ups_auto_test'] == 'yes') ? 'test' : 'live';
					$ups_rest_obj->orderCurrency = $orderCurrency;
					$ups_rest_obj->mod_weg_unit = $mod_weg_unit;
					$ups_rest_obj->woo_weg_unit = $woo_weg_unit;
					if (empty($auth_token)) {
						$auth_token = $ups_rest_obj->gen_access_token($value['hit_ups_auto_rest_grant_type'], $value['hit_ups_auto_rest_site_id'], $value['hit_ups_auto_rest_site_pwd']);
						set_transient("hitshipo_ups_rest_auth_token_".$key, $auth_token, 14200);
					}
					$xmlRequest = $ups_rest_obj->make_rate_req_rest($general_settings, $value, $pack_aft_hook['destination'], $packages);
					$result = $ups_rest_obj->get_rate_res_rest($xmlRequest, $auth_token);
					$xml = isset($result->RateResponse) ? $result->RateResponse : $result;
					$order_total_weight = $ups_rest_obj->total_pack_weight;
				} else {
					$req_package = '';
					$order_total_weight = 0;
					foreach ($packages as $group_kay => $parcel) {
						
						$dims = '';
						if(isset($parcel['Dimensions']['Length']) && isset($parcel['Dimensions']['Width']) && isset($parcel['Dimensions']['Height']) ){
						if($parcel['Dimensions']['Length'] && $parcel['Dimensions']['Width'] && $parcel['Dimensions']['Height']){
							$dims = '<Dimensions>
							<UnitOfMeasurement>
							<Code>'.$dim_unit.'</Code>
							</UnitOfMeasurement>
							<Length>'.$parcel['Dimensions']['Length'].'</Length>
							<Width>'.$parcel['Dimensions']['Width'].'</Width>
							<Height>'.$parcel['Dimensions']['Height'].'</Height>
							</Dimensions>';
						}
					}
					
					$parcel_weight = (float)round(wc_get_weight($parcel['Weight']['Value'],$mod_weg_unit,$woo_weg_unit),5);
					$parcel_cost = isset($parcel['InsuredValue']['Amount']) ? $parcel['InsuredValue']['Amount'] : 0;
					$ins_options = "";
					if (isset($general_settings['hit_ups_auto_insure']) && $general_settings['hit_ups_auto_insure'] == "yes") {
						$ins_options .= "<DeclaredValue><CurrencyCode>".$orderCurrency."</CurrencyCode><MonetaryValue>".$parcel_cost."</MonetaryValue></DeclaredValue>";
					}
					$order_total_weight += $parcel_weight;
						$req_package .= '<Package>
						<PackagingType>
								<Code>02</Code>
							</PackagingType>
							'.$dims.'
							<PackageWeight>
							<UnitOfMeasurement>
								<Code>'.$weight_unit.'</Code>
							</UnitOfMeasurement>
								<Weight>'.$parcel_weight.'</Weight>
							</PackageWeight>
							<PackageServiceOptions>
							'.$ins_options.'
							</PackageServiceOptions>
						</Package>';
					}

					if (isset($general_settings['hit_ups_auto_del_con']) && !empty($general_settings['hit_ups_auto_del_con']) && ($general_settings['hit_ups_auto_del_con'] != "NONE")) {
						$del_con_code = ($general_settings['hit_ups_auto_del_con'] == "SIG") ? "2" : "3";
						$req_package .= "<ShipmentServiceOptions><DeliveryConfirmation><DCISType>".$del_con_code."</DCISType></DeliveryConfirmation></ShipmentServiceOptions>";
					}

					$cus_classification = '';
					
					if($value['hit_ups_auto_country'] == 'US'){
						$cus_classification = '<CustomerClassification>
						<Code>'.$general_settings['hit_ups_auto_customer_classification'].'</Code>
						</CustomerClassification>';
					}

					$xmlRequest =  file_get_contents(dirname(__FILE__).'/xml/rate.xml');
					$xmlRequest = str_replace('{customer_classification}',$cus_classification,$xmlRequest);
					$xmlRequest = str_replace('{ac_hit}',$value['hit_ups_auto_access_key'],$xmlRequest);
					$xmlRequest = str_replace('{usr}',$value['hit_ups_auto_site_id'],$xmlRequest);
					$xmlRequest = str_replace('{pwd}',$value['hit_ups_auto_site_pwd'],$xmlRequest);
					$xmlRequest = str_replace('{from_name}',$value['hit_ups_auto_shipper_name'],$xmlRequest);
					$xmlRequest = str_replace('{from_acc}',$value['hit_ups_auto_acc_no'],$xmlRequest);
					$xmlRequest = str_replace('{from_city}',$value['hit_ups_auto_city'],$xmlRequest);
					$xmlRequest = str_replace('{from_state}',$value['hit_ups_auto_state'],$xmlRequest);
					$xmlRequest = str_replace('{from_country}',$value['hit_ups_auto_country'],$xmlRequest);
					$xmlRequest = str_replace('{from_postal}',$value['hit_ups_auto_zip'],$xmlRequest);
					$xmlRequest = str_replace('{to_city}',$pack_aft_hook['destination']['city'],$xmlRequest);
					$xmlRequest = str_replace('{to_country}',$des_coun,$xmlRequest);
					$xmlRequest = str_replace('{to_postal}',$pack_aft_hook['destination']['postcode'],$xmlRequest);
					// $xmlRequest = str_replace('{weight_code}',$weight_unit,$xmlRequest);
					// $xmlRequest = str_replace('{weight}',$total_weight_for_request,$xmlRequest);
					$xmlRequest = str_replace('{package}',$req_package,$xmlRequest);
					
					
					$request_url = (isset($general_settings['hit_ups_auto_test']) && $general_settings['hit_ups_auto_test'] == 'yes') ? 'https://wwwcie.ups.com/ups.app/xml/Rate' : 'https://onlinetools.ups.com/ups.app/xml/Rate';
					$result = wp_remote_post($request_url, array(
					'method' => 'POST',
					'timeout' => 70,
					'sslverify' => 0,
					'body' => $xmlRequest,
					'sslverify'   => FALSE
						)
					);
					
					libxml_use_internal_errors(true);
					if(!empty($result) && isset($result['body']))
					{
						$xml = simplexml_load_string($result['body']);
					}
				}
				$admin_user = false;
				if (is_user_logged_in()) {
					$user = wp_get_current_user();
					if (isset($user->roles) && !empty($user->roles) && in_array("administrator", $user->roles)) {
						$admin_user = true;
					}
				}
				if(isset($general_settings['hit_ups_auto_developer_rate']) && $general_settings['hit_ups_auto_developer_rate'] == 'yes' && $admin_user)
				{
					echo "<pre><b style='color:red;'>Debug log will be shown only for admins and it can be disabled by unchecking 'Enable Debug Mode' option in configuration.</b>";
					echo "<h1> Request </h1><br/>";
					if (is_array($xmlRequest)) {
						print_r($xmlRequest);
					} else {
						print_r(htmlspecialchars($xmlRequest));
					}
					echo "<br/><h1> Response </h1><br/>";
					print_r($xml);
					die();
				
				}
				if((isset($xml->Response->ResponseStatusCode) && $xml->Response->ResponseStatusCode != '1') || (isset($xml->Response->ResponseStatus->Code) && $xml->Response->ResponseStatus->Code != '1'))
				{
					return false;
				}
				if (empty($xml) || !isset($xml->RatedShipment)) {
					return false;
				}
				$_carriers = array(
							//"Public carrier name" => "technical name",
							'ups_12'                    => '3 Day Select',
							'ups_03'                    => 'Ground',
							'ups_02'                    => '2nd Day Air',
							'ups_59'                    => '2nd Day Air AM',
							'ups_01'                    => 'Next Day Air',
							'ups_13'                    => 'Next Day Air Saver',
							'ups_14'                    => 'Next Day Air Early AM',
							'ups_11'                    => 'UPS Standard',
							'ups_07'                    => 'UPS Express',
							'ups_08'                    => 'UPS Expedited',
							'ups_54'                    => 'UPS Express Plus',
							'ups_65'                    => 'UPS Saver',
							'ups_92'                    => 'SurePost Less than 1 lb',
							'ups_93'                    => 'SurePost 1 lb or Greater',
							'ups_94'                    => 'SurePost BPM',
							'ups_95'                    => 'SurePost Media',
							'ups_08'                    => 'UPS ExpeditedSM',
							'ups_82'                    => 'Today Standard',
							"ups_83"					 => "UPS Today Dedicated Courier",
							"ups_84"					=> "UPS Today Intercity",
							"ups_85"					 => "UPS Today Express",
							"ups_86" 					=> "UPS Today Express Saver",
							'ups_M2'                    => 'First Class Mail',
							'ups_M3'                    => 'Priority Mail',
							'ups_M4'                    => 'Expedited Mail Innovations',
							'ups_M5'                    => 'Priority Mail Innovations',
							'ups_M6'                    => 'EconomyMail Innovations',
							'ups_70'                    => 'Access Point Economy',
							'ups_96'                    => 'Worldwide Express Freight'
						);
				$carriers_available = isset($general_settings['hit_ups_auto_carrier']) && is_array($general_settings['hit_ups_auto_carrier']) ? $general_settings['hit_ups_auto_carrier'] : array();
				
				$surcharge_key = 0;
				foreach($xml->RatedShipment as $indshipment)
				{
					$rate_code = 'ups_'.(string)$indshipment->Service->Code;
					$rate_cost = 0;
					if(array_key_exists($rate_code,$carriers_available))
					{
						if ( $general_settings['hit_ups_auto_account_rates'] == 'yes' && isset( $indshipment->NegotiatedRates->NetSummaryCharges->GrandTotal->MonetaryValue ) )
						{
							$isoUPSCurrency = (string) $indshipment->NegotiatedRates->NetSummaryCharges->GrandTotal->CurrencyCode;
							$rate_cost = (float) $indshipment->NegotiatedRates->NetSummaryCharges->GrandTotal->MonetaryValue;
							
						} elseif ($general_settings['hit_ups_auto_account_rates'] == 'yes' && isset( $indshipment->NegotiatedRateCharges->TotalCharge->MonetaryValue )){
							$isoUPSCurrency = (string) $indshipment->NegotiatedRateCharges->TotalCharge->CurrencyCode;
							$rate_cost = (float) $indshipment->NegotiatedRateCharges->TotalCharge->MonetaryValue;
						}
						else
						{
							$isoUPSCurrency = (string) $indshipment->TotalCharges->CurrencyCode;
							$rate_cost = (float) $indshipment->TotalCharges->MonetaryValue;
							
						}

						if ( isset($indshipment->RatedShipmentWarning) && !empty($indshipment->RatedShipmentWarning) ) {
							$surcharge[$surcharge_key] = "no";
							foreach ($indshipment->RatedShipmentWarning as $warning) {
								if ( isset($surcharge[$surcharge_key]) && $surcharge[$surcharge_key] == "no" &&  (string)$warning == "A Delivery Area Surcharge has been added to the service cost.") {
									$surcharge[$surcharge_key] = "yes";
								}
							}
						}else {
							$surcharge[$surcharge_key] = "no";
						}
						
						$surcharge_key ++;

						if ($general_settings['hit_ups_auto_currency'] != get_option('woocommerce_currency')) {
							$exchange_rate = $value['hit_ups_auto_con_rate'];
							
							if($exchange_rate && $exchange_rate > 0){
								$rate_cost /= $exchange_rate;
							}
							
						}

						$rate[$rate_code] = $rate_cost;
						
						
					}
					else{
						continue;
					}
					
				}

				$shipping_rates[$key] = $rate;
			}
			if(!empty($shipping_rates)){
				$i=0;
				$final_price = array();
				foreach ($shipping_rates as $mkey => $rate) {
					$cheap_p = 0;
					$cheap_s = '';
					foreach ($rate as $key => $cvalue) {
						if ($i > 0){

							if(!in_array($key, array('C','Q'))){
								if($cheap_p == 0 && $cheap_s == ''){
									$cheap_p = $cvalue;
									$cheap_s = $key;
									
								}else if ($cheap_p > $cvalue){
									$cheap_p = $cvalue;
									$cheap_s = $key;
								}
							}
						}else{
							$final_price[] = array('price' => $cvalue, 'code' => $key, 'multi_v' => $mkey.'_'. $key);
						}
					}

					if($cheap_p != 0 && $cheap_s != ''){
						foreach ($final_price as $key => $value) {
							$value['price'] = $value['price'] + $cheap_p;
							$value['multi_v'] = $value['multi_v'] . '|' . $mkey . '_' . $cheap_s;
							$final_price[$key] = $value;
						}
					}

					$i++;
					
				}

				foreach ($final_price as $key => $value) {
					
					$rate_cost = $value['price'];
					$rate_code = $value['code'];
					$multi_ven = $value['multi_v'];

					if (!empty($general_settings['hit_ups_auto_carrier_adj_percentage'][$rate_code])) {
							$rate_cost += $rate_cost * ($general_settings['hit_ups_auto_carrier_adj_percentage'][$rate_code] / 100);
						}
					if (!empty($general_settings['hit_ups_auto_carrier_adj'][$rate_code])) {
							$rate_cost += $general_settings['hit_ups_auto_carrier_adj'][$rate_code];
						}

						$rate_cost = round($rate_cost, 2);

					$carriers_available = isset($general_settings['hit_ups_auto_carrier']) && is_array($general_settings['hit_ups_auto_carrier']) ? $general_settings['hit_ups_auto_carrier'] : array();

					$carriers_name_available = isset($general_settings['hit_ups_auto_carrier_name']) && is_array($general_settings['hit_ups_auto_carrier']) ? $general_settings['hit_ups_auto_carrier_name'] : array();
					
					if(array_key_exists($rate_code,$carriers_available))
						{
							$name = isset($carriers_name_available[$rate_code]) && !empty($carriers_name_available[$rate_code]) ? $carriers_name_available[$rate_code] : $_carriers[$rate_code];
							
							$rate_cost = apply_filters('hitstacks_ups_rate_cost',$rate_cost,$rate_code,$order_total);
							if($rate_cost < 1){
								$name .= ' - Free';
							}

							if(!isset($general_settings['hit_ups_auto_v_rates']) || $general_settings['hit_ups_auto_v_rates'] != 'yes'){
								$multi_ven = '';
							}

							// This is where you'll add your rates
							$rate = array(
								'id'       => 'hit'.$rate_code,
								'label'    => $name,
								'cost'     => apply_filters( "hitstacks_ups_shipping_cost_conversion", $rate_cost, $order_total_weight, $pack_aft_hook['destination']['country'], $rate_code, $surcharge[$key]),
								'meta_data' => array('hit_multi_ven' => $multi_ven,'hit_ups_auto_service' => $rate_code)
							);
							
							// Register the rate
							
							$this->add_rate( $rate );
						}

				}
			}
			}
			
        }

        public function hit_get_ups_packages($package,$general_settings,$orderCurrency,$chk = false)
		{
			switch ($general_settings['hit_ups_auto_packing_type']) {
				case 'box' :
					return $this->box_shipping($package,$general_settings,$orderCurrency,$chk);
					break;
				case 'weight_based' :
					return $this->weight_based_shipping($package,$general_settings,$orderCurrency,$chk);
					break;
				case 'per_item' :
				default :
					return $this->per_item_shipping($package,$general_settings,$orderCurrency,$chk);
					break;
			}
		}
		private function weight_based_shipping($package,$general_settings,$orderCurrency,$chk = false)
		{
			
			if ( ! class_exists( 'WeightPack' ) ) {
				include_once 'classes/weight_pack/class-hit-weight-packing.php';
			}
			$max_weight = isset($general_settings['hit_ups_auto_max_weight']) && $general_settings['hit_ups_auto_max_weight'] !=''  ? $general_settings['hit_ups_auto_max_weight'] : 10 ;
			$weight_pack=new WeightPack('pack_descending');
			$weight_pack->set_max_weight($max_weight);

			$package_total_weight = 0;
			$insured_value = 0;

			$ctr = 0;
			foreach ($package as $item_id => $values) {
				$ctr++;
				$product = $values['data'];
				$product_data = $product->get_data();
				$get_prod = wc_get_product($values['product_id']);

				if (!$product_data['weight']) {
					if ($get_prod->is_type('variable')) {
						$parent_prod_data = $product->get_parent_data();

						if ($parent_prod_data['weight']) {

							$product_data['weight'] = !empty($parent_prod_data['weight']) ? $parent_prod_data['weight'] : 0.001;
						} else {
							$product_data['weight'] = 0.001;
						}
					}else{
					$product_data['weight'] = 0.001;
					}
				}
				$chk_qty = $chk ? $values['product_quantity'] : $values['quantity'];

				$weight_pack->add_item($product_data['weight'], $product_data, $chk_qty);
			}

			$pack   =   $weight_pack->pack_items();  
			$errors =   $pack->get_errors();
			if( !empty($errors) ){
				//do nothing
				return;
			} else {
				$boxes    =   $pack->get_packed_boxes();
				$unpacked_items =   $pack->get_unpacked_items();

				$insured_value        =   0;

				$packages      =   array_merge( $boxes, $unpacked_items ); // merge items if unpacked are allowed
				$package_count  =   sizeof($packages);
				// get all items to pass if item info in box is not distinguished
				$packable_items =   $weight_pack->get_packable_items();
				$all_items    =   array();
				if(is_array($packable_items)){
					foreach($packable_items as $packable_item){
						$all_items[]    =   $packable_item['data'];
					}
				}
				//pre($packable_items);
				$order_total = '';

				$to_ship  = array();
				$group_id = 1;
				foreach($packages as $package){//pre($package);
					$packed_products = array();
					if(($package_count  ==  1) && isset($order_total)){
						$insured_value  =  (isset($product_data['product_price']) ? $product_data['product_price'] : $product_data['price']) * (isset($values['product_quantity']) ? $values['product_quantity'] : $values['quantity']);
					}else{
						$insured_value  =   0;
						if(!empty($package['items'])){
							foreach($package['items'] as $item){               

								$insured_value        =   $insured_value; //+ $item->price;
							}
						}else{
							if( isset($order_total) && $package_count){
								$insured_value  =   $order_total/$package_count;
							}
						}
					}
					$packed_products    =   isset($package['items']) ? $package['items'] : $all_items;
					// Creating package request
					$package_total_weight   = $package['weight'];

					$insurance_array = array(
						'Amount' => $insured_value,
						'Currency' => $orderCurrency
					);

					$group = array(
						'GroupNumber' => $group_id,
						'GroupPackageCount' => 1,
						'Weight' => array(
						'Value' => round($package_total_weight, 3),
						'Units' => (isset($general_settings['weg_dim']) && $general_settings['weg_dim'] ==='yes') ? 'KG' : 'LBS'
					),
						'packed_products' => $packed_products,
					);
					$group['InsuredValue'] = $insurance_array;
					$group['packtype'] = 'BOX';

					$to_ship[] = $group;
					$group_id++;
				}
			}
			
			return $to_ship;
		}
		private function box_shipping($package,$general_settings,$orderCurrency,$chk = false)
		{
			if (!class_exists('HIT_Boxpack')) {
				include_once 'classes/hit-box-packing.php';
			}
			$boxpack = new HIT_Boxpack();
			$boxes = Configuration::get('hit_ups_auto_shipping_services_box');
			if(empty($boxes))
			{
				return false;
			}
			$boxes = unserialize($boxes);
			// Define boxes
			foreach ($boxes as $key => $box) {
				if (!$box['enabled']) {
					continue;
				}
				$box['pack_type'] = !empty($box['pack_type']) ? $box['pack_type'] : 'BOX' ;

				$newbox = $boxpack->add_box($box['length'], $box['width'], $box['height'], $box['box_weight'], $box['pack_type']);

				if (isset($box['id'])) {
					$newbox->set_id(current(explode(':', $box['id'])));
				}

				if ($box['max_weight']) {
					$newbox->set_max_weight($box['max_weight']);
				}
				if ($box['pack_type']) {
					$newbox->set_packtype($box['pack_type']);
				}
			}

			// Add items
			foreach ($package as $item_id => $values) {

				$skip_product = '';
				if($skip_product){
					continue;
				}

				if ( $values['width'] && $values['height'] && $values['depth'] && $values['weight'] ) {

					$dimensions = array( $values['depth'], $values['height'], $values['width']);
					$chk_qty = $chk ? $values['product_quantity'] : $values['cart_quantity'];
					for ($i = 0; $i < $chk_qty; $i ++) {
						$boxpack->add_item($dimensions[2], $dimensions[1], $dimensions[0], $values['weight'], $values['price'], array(
							'data' => $values
						)
										  );
					}
				} else {
					//    $this->debug(sprintf(__('Product #%s is missing dimensions. Aborting.', 'wf-shipping-ups'), $item_id), 'error');
					return;
				}
			}

			// Pack it
			$boxpack->pack();
			$packages = $boxpack->get_packages();
			$to_ship = array();
			$group_id = 1;
			foreach ($packages as $package) {
				if ($package->unpacked === true) {
					//$this->debug('Unpacked Item');
				} else {
					//$this->debug('Packed ' . $package->id);
				}

				$dimensions = array($package->length, $package->width, $package->height);

				sort($dimensions);
				$insurance_array = array(
					'Amount' => round($package->value),
					'Currency' => $orderCurrency->iso_code
				);


				$group = array(
					'GroupNumber' => $group_id,
					'GroupPackageCount' => 1,
					'Weight' => array(
					'Value' => round($package->weight, 3),
					'Units' => (isset($general_settings['weg_dim']) && $general_settings['weg_dim'] ==='yes') ? 'KG' : 'LBS'
				),
					'Dimensions' => array(
					'Length' => max(1, round($dimensions[2], 3)),
					'Width' => max(1, round($dimensions[1], 3)),
					'Height' => max(1, round($dimensions[0], 3)),
					'Units' => (isset($general_settings['weg_dim']) && $general_settings['weg_dim'] ==='yes') ? 'CM' : 'IN'
				),
					'InsuredValue' => $insurance_array,
					'packed_products' => array(),
					'package_id' => $package->id,
					'packtype' => isset($package->packtype)?$package->packtype:'BOX'
				);

				if (!empty($package->packed) && is_array($package->packed)) {
					foreach ($package->packed as $packed) {
						$group['packed_products'][] = $packed->get_meta('data');
					}
				}

				$to_ship[] = $group;

				$group_id++;
			}

			return $to_ship;
		}
		private function per_item_shipping($package,$general_settings,$orderCurrency,$chk = false) {
			$to_ship = array();
			$group_id = 1;
			
			// Get weight of order
			foreach ($package as $item_id => $values) {
				$product = $values['data'];
				$product_data = $product->get_data();
				$get_prod = wc_get_product($values['product_id']);
				$parent_prod_data = [];

				if ($get_prod->is_type('variable')) {
					$parent_prod_data = $product->get_parent_data();
				}
				
				$group = array();
				$insurance_array = array(
					'Amount' => round($product_data['price']),
					'Currency' => $orderCurrency
				);

				if($product_data['weight']){
					$ups_per_item_weight = round($product_data['weight'] > 0.001 ? $product_data['weight'] : 0.001, 3);
				}else{
					$ups_per_item_weight = isset($parent_prod_data['weight']) ? (round($parent_prod_data['weight'] > 0.001 ? $parent_prod_data['weight'] : 0.001, 3)) : 0.001;
				}
				$group = array(
					'GroupNumber' => $group_id,
					'GroupPackageCount' => 1,
					'Weight' => array(
						'Value' => $ups_per_item_weight,
						'Units' => (isset($general_settings['hit_ups_auto_weight_unit']) && $general_settings['hit_ups_auto_weight_unit'] == 'KG_CM') ? 'KG' : 'LBS'
				),
					'packed_products' => array($product_data)
				);

				if (isset($product_data['width']) && isset($product_data['height']) && isset($product_data['length'])) {

					$group['Dimensions'] = array(
						'Length' => max(1, round((float)$product_data['length'],3)),
						'Width' => max(1, round((float)$product_data['width'],3)),
						'Height' => max(1, round((float)$product_data['height'],3)),
						'Units' => (isset($general_settings['hit_ups_auto_weight_unit']) && $general_settings['hit_ups_auto_weight_unit'] == 'KG_CM') ? 'CM' : 'IN'
					);
				}elseif ($parent_prod_data['width'] && $parent_prod_data['height'] && $parent_prod_data['length']) {
					$group['Dimensions'] = array(
						'Length' => max(1, round((float)$parent_prod_data['length'], 3)),
						'Width' => max(1, round((float)$parent_prod_data['width'], 3)),
						'Height' => max(1, round((float)$parent_prod_data['height'], 3)),
						'Units' => (isset($general_settings['hit_ups_auto_weight_unit']) && $general_settings['hit_ups_auto_weight_unit'] == 'KG_CM') ? 'CM' : 'IN'
					);
				}

				$group['packtype'] = 'BOX';

				$group['InsuredValue'] = $insurance_array;

				$chk_qty = $chk ? $values['product_quantity'] : $values['quantity'];

				for ($i = 0; $i < $chk_qty; $i++)
					$to_ship[] = $group;

				$group_id++;
			}

			return $to_ship;
		}
		private function hit_get_zipcode_or_city($country, $city, $postcode) {
			$no_postcode_country = array('AE', 'AF', 'AG', 'AI', 'AL', 'AN', 'AO', 'AW', 'BB', 'BF', 'BH', 'BI', 'BJ', 'BM', 'BO', 'BS', 'BT', 'BW', 'BZ', 'CD', 'CF', 'CG', 'CI', 'CK',
									 'CL', 'CM', 'CO', 'CR', 'CV', 'DJ', 'DM', 'DO', 'EC', 'EG', 'ER', 'ET', 'FJ', 'FK', 'GA', 'GD', 'GH', 'GI', 'GM', 'GN', 'GQ', 'GT', 'GW', 'GY', 'HK', 'HN', 'HT', 'IE', 'IQ', 'IR',
									 'JM', 'JO', 'KE', 'KH', 'KI', 'KM', 'KN', 'KP', 'KW', 'KY', 'LA', 'LB', 'LC', 'LK', 'LR', 'LS', 'LY', 'ML', 'MM', 'MO', 'MR', 'MS', 'MT', 'MU', 'MW', 'MZ', 'NA', 'NE', 'NG', 'NI',
									 'NP', 'NR', 'NU', 'OM', 'PA', 'PE', 'PF', 'PY', 'QA', 'RW', 'SA', 'SB', 'SC', 'SD', 'SL', 'SN', 'SO', 'SR', 'SS', 'ST', 'SV', 'SY', 'TC', 'TD', 'TG', 'TL', 'TO', 'TT', 'TV', 'TZ',
									 'UG', 'UY', 'VC', 'VE', 'VG', 'VN', 'VU', 'WS', 'XA', 'XB', 'XC', 'XE', 'XL', 'XM', 'XN', 'XS', 'YE', 'ZM', 'ZW');

			$postcode_city = !in_array( $country, $no_postcode_country ) ? $postcode_city = "<Postalcode>{$postcode}</Postalcode>" : '';
			if( !empty($city) ){
				$postcode_city .= "<City>{$city}</City>";
			}
			return $postcode_city;
		}	
		/**
		 * Initialise Gateway Settings Form Fields
		 */
		public function init_form_fields() {
			 $this->form_fields = array('hit_ups_auto' => array('type'=>'hit_ups_auto'));
		}
		 public function generate_hit_ups_auto_html()

		 {

			$general_settings = get_option('hit_ups_auto_main_settings');
			$general_settings = empty($general_settings) ? array() : $general_settings;
			if(!empty($general_settings)){
				wp_redirect(admin_url('options-general.php?page=hit-ups-configuration'));
			}

			if(isset($_POST['configure_the_plugin'])){
				// global $woocommerce;
				// $countries_obj   = new WC_Countries();
				// $countries   = $countries_obj->__get('countries');
				// $default_country = $countries_obj->get_base_country();

				// if(!isset($general_settings['hit_ups_auto_country'])){
				// 	$general_settings['hit_ups_auto_country'] = $default_country;
				// 	update_option('hit_ups_auto_main_settings', $general_settings);
				
				// }
				wp_redirect(admin_url('options-general.php?page=hit-ups-configuration'));	
			}
		?>
			<style>

			.card {
				background-color: #fff;
				border-radius: 5px;
				width: 800px;
				max-width: 800px;
				height: auto;
				text-align:center;
				margin: 10px auto 100px auto;
				box-shadow: 0px 1px 20px 1px hsla(213, 33%, 68%, .6);
			}  

			.content {
				padding: 20px 20px;
			}


			h2 {
				text-transform: uppercase;
				color: #000;
				font-weight: bold;
			}


			.boton {
				text-align: center;
			}

			.boton button {
				font-size: 18px;
				border: none;
				outline: none;
				color: #166DB4;
				text-transform: capitalize;
				background-color: #fff;
				cursor: pointer;
				font-weight: bold;
			}

			button:hover {
				text-decoration: underline;
				text-decoration-color: #166DB4;
			}
						</style>
						<!-- Fuente Mulish -->
						

			<div class="card">
				<div class="content">
					<div class="logo">
					<img src="<?php _e(plugin_dir_url(__FILE__),'hit_ups_auto'); ?>views/ups_logo.png" style="width:150px;" alt="logo UPS" />
					</div>
					<h2><strong>Shipi + UPS</strong></h2>
					<p style="font-size: 14px;line-height: 27px;">
					<?php _e('Welcome to Shipi! You are at just one-step ahead to configure the UPS with Shipi.','a2z_dhlexpress') ?><br>
					<?php _e('We have lot of features that will take your e-commerce store to another level.','a2z_dhlexpress') ?><br><br>
					<?php _e('Shipi helps you to save time, reduce errors, and worry less when you automate your tedious, manual tasks. Shipi + our plugin can generate shipping labels, Commercial invoice, display real time rates, track orders, audit shipments, and supports both domestic & international UPS services.','a2z_dhlexpress') ?><br><br>
					<?php _e('Make your customers happier by reacting faster and handling their service requests in a timely manner, meaning higher store reviews and more revenue.','a2z_dhlexpress') ?><br>
					</p>
						
				</div>
				<div class="boton" style="padding-bottom:10px;">
				<button class="button-primary" name="configure_the_plugin" style="padding:8px;">Configure the plugin</button>
				</div>
				</div>
			<?php
			_e( '<style>button.button-primary.woocommerce-save-button{display:none;}</style>','hit_ups_auto');
			
		 }


    }
}