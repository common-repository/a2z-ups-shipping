<?php
	/**
	 * 
	 */
	class ups_rest
	{
		public $mode = "test";
		public $orderCurrency = "";
		public $live_rate_url = "https://onlinetools.ups.com/api/rating/v2205/shop?additionalinfo=timeintransit";
		public $test_rate_url = "https://wwwcie.ups.com/api/rating/v2205/shop?additionalinfo=timeintransit";
		public $live_auth_url = "https://onlinetools.ups.com/security/v1/oauth/token";
		public $test_auth_url = "https://wwwcie.ups.com/security/v1/oauth/token";
		public $live_trk_url = "https://onlinetools.ups.com/api/track/v1/details/";
		public $test_trk_url = "https://wwwcie.ups.com/api/track/v1/details/";
		public $order_total = 0;
		public $total_pack_count = 0;
		public $total_pack_weight = 0;
		public $weg_unit = "";
		public $dim_unit = "";
		public $mod_weg_unit = "";
		public $woo_weg_unit = "";
		function __construct()
		{
			// code...
		}
		public function gen_access_token($grant_type='', $api_key='', $api_secret='')
		{
			$request_url = ($this->mode == "test") ? $this->test_auth_url : $this->live_auth_url;
			$result = wp_remote_post(
				$request_url,
				array(
					'method' => 'POST',
					'timeout' => 70,
					'sslverify' => 0,
					'body' => 'grant_type='.$grant_type,
					'headers' => array(
						"Content-Type" => "application/x-www-form-urlencoded",
						"x-merchant-id" => $api_key,
						"Authorization" => "Basic " . base64_encode($api_key.":".$api_secret)
					)
				)
			);
			if (is_array($result) && isset($result['body']) && !empty($result['body'])) {
				$auth_data = json_decode($result['body']);
				return isset($auth_data->access_token) ? $auth_data->access_token : "";
			}
			return;
		}
		public function make_rate_req_rest($general_settings=[], $ven_settings=[], $rec_addr=[], $packages=[])
		{
			if (isset($general_settings['hit_ups_auto_weight_unit']) && $general_settings['hit_ups_auto_weight_unit'] == "KG_CM") {
				$this->weg_unit = "KGS";
				$this->dim_unit = "CM";
			} else {
				$this->weg_unit = "LBS";
				$this->dim_unit = "IN";
			}
			
			$rate_req = [];
			$rate_req['RateRequest']['Request']['RequestOption'] = "Shoptimeintransit";
			if (isset($ven_settings['hit_ups_auto_country']) && $ven_settings['hit_ups_auto_country'] == "US") {
				$rate_req['RateRequest']['Request']['CustomerClassification'] = isset($general_settings['hit_ups_auto_customer_classification']) ? $general_settings['hit_ups_auto_customer_classification'] : "00";
			}
			$rate_req['RateRequest']['Shipment']['Shipper'] = $this->make_ship_addr($ven_settings);
			$rate_req['RateRequest']['Shipment']['ShipTo'] = $this->make_rec_addr($rec_addr);
			$rate_req['RateRequest']['Shipment']['ShipFrom'] = $this->make_ship_addr($ven_settings);
			$rate_req['RateRequest']['Shipment']['Package'] = $this->make_pack_info($packages, $general_settings);
			$rate_req['RateRequest']['Shipment']['ShipmentTotalWeight'] = [
				'UnitOfMeasurement' => [
					"Code" => $this->weg_unit,
					"Description" => ($this->weg_unit == "KGS") ? "Kilograms" : "Pounds"
				],
				'Weight' => substr(number_format($this->total_pack_weight, 4), 0, 6)
			];
			$rate_req['RateRequest']['Shipment']['ShipmentRatingOptions'] = ['NegotiatedRatesIndicator' => "1"];
			$rate_req['RateRequest']['Shipment']['DeliveryTimeInformation'] = ['PackageBillType' => '03'];
			$rate_req['RateRequest']['Shipment']['InvoiceLineTotal'] = ["CurrencyCode" => $this->orderCurrency, "MonetaryValue" => (string)$this->order_total];
			if (isset($general_settings['hit_ups_auto_del_con']) && !empty($general_settings['hit_ups_auto_del_con']) && ($general_settings['hit_ups_auto_del_con'] != "NONE")) {
				$rate_req['RateRequest']['Shipment']['ShipmentServiceOptions']['DeliveryConfirmation']['DCISType'] = ($general_settings['hit_ups_auto_del_con'] == "SIG") ? "1" : "2";
			}
			return $rate_req;
		}
		private function make_ship_addr($ven_settings=[])
		{
			$ship_addr = [];
			$ship_addr['Name'] = isset($ven_settings['hit_ups_auto_shipper_name']) ? $ven_settings['hit_ups_auto_shipper_name'] : "";
			$ship_addr['ShipperNumber'] = isset($ven_settings['hit_ups_auto_rest_acc_no']) ? $ven_settings['hit_ups_auto_rest_acc_no'] : "";
			$ship_addr['Address']['AddressLine'][] = isset($ven_settings['hit_ups_auto_address1']) ? $ven_settings['hit_ups_auto_address1'] : "";
			if (isset($ven_settings['hit_ups_auto_address2']) && !empty($ven_settings['hit_ups_auto_address2'])) {
				$ship_addr['Address']['AddressLine'][] = $ven_settings['hit_ups_auto_address2'];
			}
			$ship_addr['Address']['City'] = isset($ven_settings['hit_ups_auto_city']) ? $ven_settings['hit_ups_auto_city'] : "";
			$ship_addr['Address']['StateProvinceCode'] = isset($ven_settings['hit_ups_auto_state']) ? substr($ven_settings['hit_ups_auto_state'], 0, 2) : "";
			$ship_addr['Address']['PostalCode'] = isset($ven_settings['hit_ups_auto_zip']) ? $ven_settings['hit_ups_auto_zip'] : "";
			$ship_addr['Address']['CountryCode'] = isset($ven_settings['hit_ups_auto_country']) ? $ven_settings['hit_ups_auto_country'] : "";
			return $ship_addr;
		}
		private function make_rec_addr($rec_addr=[])
		{
			$rec_addr_info = [];
			$rec_addr_info['Address']['AddressLine'][] = isset($rec_addr['address_1']) ? $rec_addr['address_1'] : "";
			if (isset($rec_addr['address_2']) && !empty($rec_addr['address_2'])) {
				$rec_addr_info['Address']['AddressLine'][] = $rec_addr['address_2'];
			}
			$rec_addr_info['Address']['City'] = isset($rec_addr['city']) ? $rec_addr['city'] : "";
			$rec_addr_info['Address']['StateProvinceCode'] = isset($rec_addr['state']) ? substr($rec_addr['state'], 0, 2) : "";
			$rec_addr_info['Address']['PostalCode'] = isset($rec_addr['postcode']) ? $rec_addr['postcode'] : "";
			$rec_addr_info['Address']['CountryCode'] = isset($rec_addr['country']) ? $rec_addr['country'] : "";
			return $rec_addr_info;
		}
		private function make_pack_info($packs=[], $general_settings=[])
		{
			$packs_info = [];
			if (!empty($packs)) {
				foreach ($packs as $p_key => $pack) {
					$curr_pack_info = [];
					$curr_pack_info['PackagingType'] = ["Code" => "02"];
					$curr_pack_info['PackageWeight']['UnitOfMeasurement'] = [
						"Code" => $this->weg_unit,
						"Description" => ($this->weg_unit == "KGS") ? "Kilograms" : "Pounds"
					];
					$curr_pack_info['PackageWeight']['Weight'] = isset($pack['Weight']['Value']) ? substr(number_format(wc_get_weight($pack['Weight']['Value'],$this->mod_weg_unit,$this->woo_weg_unit), 4), 0, 6) : "0.5000";
					if (isset($pack["Dimensions"])) {
						$curr_pack_info['Dimensions']['UnitOfMeasurement'] = [
							"Code" => $this->dim_unit,
							"Description" => ($this->dim_unit == "KGS") ? "Centimeter" : "Inches"
						];
						$curr_pack_info['Dimensions']['Length'] = isset($pack['Dimensions']['Length']) ? substr(number_format($pack['Dimensions']['Length'], 4), 0, 6) : "0.5000";
						$curr_pack_info['Dimensions']['Width'] = isset($pack['Dimensions']['Width']) ? substr(number_format($pack['Dimensions']['Width'], 4), 0, 6) : "0.5000";
						$curr_pack_info['Dimensions']['Height'] = isset($pack['Dimensions']['Height']) ? substr(number_format($pack['Dimensions']['Height'], 4), 0, 6) : "0.5000";
					}
					if (isset($general_settings['hit_ups_auto_insure']) && $general_settings['hit_ups_auto_insure'] == "yes") {
						$curr_pack_info['PackageServiceOptions']['DeclaredValue'] = [
							"CurrencyCode" => $this->orderCurrency,
							"MonetaryValue" => isset($pack['InsuredValue']['Amount']) ? (string) round($pack['InsuredValue']['Amount'], 2) : ""
						];
					}
					$packs_info[] = $curr_pack_info;
					foreach ($pack['packed_products'] as $pp_key => $p_prods) {
						if (isset($p_prods['price'])) {
							$this->order_total += $p_prods['price'];
						}
					}
					$this->total_pack_weight += isset($pack['Weight']['Value']) ? substr(number_format(wc_get_weight($pack['Weight']['Value'],$this->mod_weg_unit,$this->woo_weg_unit), 4), 0, 6) : "0.5000";
					$this->total_pack_count++;
				}
			}
			return $packs_info;
		}
		public function get_rate_res_rest($req_data=[], $auth_tok="")
		{
			$request_url = ($this->mode == "test") ? $this->test_rate_url : $this->live_rate_url;
			$result = wp_remote_post(
				$request_url,
				array(
					'method' => 'POST',
					'timeout' => 70,
					'sslverify' => 0,
					'body' => json_encode($req_data),
					'headers' => array(
						"Content-Type" => "application/json",
						"Authorization" => "Bearer " . $auth_tok
					)
				)
			);
			if (is_array($result) && isset($result['body']) && !empty($result['body'])) {
				$rate_res_data = json_decode($result['body']);
				return $rate_res_data;
			}
			return;
		}
		public function make_trk_res_rest($trk_no='', $auth_tok="")
		{
			$request_url = ($this->mode == "test") ? $this->test_trk_url : $this->live_trk_url;
			$request_url .= $trk_no."?locale=en_US&returnSignature=true";
			$result = wp_remote_post(
				$request_url,
				array(
					'method' => 'GET',
					'timeout' => 70,
					'sslverify' => 0,
					'body' => [],
					'headers' => array(
						"Content-Type" => "application/json",
						"Authorization" => "Bearer " . $auth_tok,
						"transId" => "11111111",
						"transactionSrc" => "Shipi"
					)
				)
			);
			if (is_array($result) && isset($result['body']) && !empty($result['body'])) {
				$trk_res_data = json_decode($result['body'], true);
				return $trk_res_data;
			}
			return;
		}
	}