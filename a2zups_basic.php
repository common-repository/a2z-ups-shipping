<?php

/**
 * Plugin Name:  UPS Rates & Labels
 * Plugin URI: https://a2zplugins.com/product/ups-express-shipping-with-label-printing/
 * Description: Realtime Shipping Rates, Shipping label, commercial invoice automation included.
 * Version: 4.3.3
 * Author: Shipi
 * Author URI: https://myshipi.com/
 * Developer: aarsiv
 * Developer URI: https://myshipi.com/
 * Text Domain: hit_ups_auto
 * Domain Path: /i18n/languages/
 *
 * WC requires at least: 2.6
 * WC tested up to: 5.8
 *
 *
 * @package WooCommerce
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

// Define WC_PLUGIN_FILE.
if (!defined('HIT_UPS_AUTO_PLUGIN_FILE')) {
	define('HIT_UPS_AUTO_PLUGIN_FILE', __FILE__);
}

// set HPOS feature compatible by plugin
add_action(
    'before_woocommerce_init',
    function () {
        if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
        }
    }
);
// Include the main WooCommerce class.
if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {

	if (!class_exists('hit_ups_auto_parent')) {
		class hit_ups_auto_parent
		{
			private $errror = '';
			private $hpos_enabled = false;
			private $new_prod_editor_enabled = false;
			public function __construct()
			{
				if (get_option("woocommerce_custom_orders_table_enabled") === "yes") {
 		            $this->hpos_enabled = true;
 		        }
 		        if (get_option("woocommerce_feature_product_block_editor_enabled") === "yes") {
 		            $this->new_prod_editor_enabled = true;
 		        }
				add_action('woocommerce_shipping_init', array($this, 'hit_ups_init'));
				add_action('init', array($this, 'hit_order_status_update'));
				add_filter('woocommerce_shipping_methods', array($this, 'hit_ups_method'));
				add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'hit_ups_plugin_action_links'));
				add_action('add_meta_boxes', array($this, 'create_ups_shipping_meta_box'));
				if ($this->hpos_enabled) {
					add_action( 'woocommerce_process_shop_order_meta', array($this, 'hit_create_ups_shipping'), 10, 1 );
					add_action( 'woocommerce_process_shop_order_meta', array($this, 'create_ups_return_label'), 10, 1 );
				} else {
					add_action('save_post', array($this, 'hit_create_ups_shipping'), 10, 1);
					add_action('save_post', array($this, 'create_ups_return_label'), 10, 1);
				}
				add_action('admin_menu', array($this, 'hit_ups_menu_page'));
				add_action('woocommerce_order_status_processing', array($this, 'hit_wc_checkout_order_processed'));
				// add_action( 'woocommerce_checkout_order_processed', array( $this, 'hit_wc_checkout_order_processed' ) );
				// add_action( 'woocommerce_thankyou', array( $this, 'hit_wc_checkout_order_processed' ) );
				add_action('woocommerce_order_details_after_order_table', array($this, 'ups_track'));
				$general_settings = get_option('hit_ups_auto_main_settings');
				$general_settings = empty($general_settings) ? array() : $general_settings;

				if (isset($general_settings['hit_ups_auto_v_enable']) && $general_settings['hit_ups_auto_v_enable'] == 'yes') {
					// add_action( 'woocommerce_product_options_shipping', array($this,'hit_choose_vendor_address' ));
					add_action('woocommerce_process_product_meta', array($this, 'hit_save_product_meta'));
					add_filter('woocommerce_product_data_tabs', array($this, 'custom_product_tabs'));
					// Edit User Hooks
					add_action('edit_user_profile', array($this, 'hit_define_ups_credentails'));
					add_action('edit_user_profile_update', array($this, 'save_user_fields'));
					add_filter('woocommerce_product_data_panels', array($this, 'hit_choose_vendor_address')); // WC 2.6 and up

				}
			}
			function hit_ups_menu_page()
			{
				$general_settings = get_option('hit_ups_auto_main_settings');
				if (isset($general_settings['hit_ups_auto_integration_key']) && !empty($general_settings['hit_ups_auto_integration_key'])) {
					add_menu_page(__( 'UPS Labels', 'hit_ups_auto' ), 'UPS Labels', 'manage_options', 'hit-ups-labels', array($this,'my_label_page_contents'), '', 6);
				}
				add_submenu_page('options-general.php', 'UPS Config', 'UPS Config', 'manage_options', 'hit-ups-configuration', array($this, 'my_admin_page_contents'));
			}
			
			function my_label_page_contents(){
				$general_settings = get_option('hit_ups_auto_main_settings');
				$url = site_url();
				if (isset($general_settings['hit_ups_auto_integration_key']) && !empty($general_settings['hit_ups_auto_integration_key'])) {
					echo "<iframe style='width: 100%;height: 100vh;' src='https://app.myshipi.com/embed/label.php?shop=".$url."&key=".$general_settings['hit_ups_auto_integration_key']."&show=ship'></iframe>";
				}
            }
			function my_admin_page_contents()
			{
				include_once('controllors/views/hit_ups_automated_settings_view.php');
			}


			function custom_product_tabs($tabs)
			{

				$tabs['ups'] = array(
					'label'		=> __('UPS ACCOUNTS', 'woocommerce'),
					'target'	=> 'ups_account',
					'class'		=> array('show_if_simple', 'show_if_variable'),
				);

				return $tabs;
			}


			public function ups_track($order)
			{
				$general_settings = get_option('hit_ups_auto_main_settings', array());
				$order_id = $order->get_id();
				$json_data = get_option('hit_ups_auto_values_' . $order_id);

				if (!empty($json_data) && isset($general_settings['hit_ups_auto_trk_status_cus']) && $general_settings['hit_ups_auto_trk_status_cus'] == "yes") {
					$array_data_to_track = json_decode($json_data, true);
					$track_datas = array();

					if (isset($array_data_to_track[0])) {
						$track_datas = $array_data_to_track;
					} else {
						$track_datas[] = $array_data_to_track;
					}

					$trk_count = 1;
					$tot_trk_count = count($track_datas);

					if ($track_datas) {
						_e('<div style = "box-shadow: 1px 1px 10px 1px #d2d2d2;margin-bottom:20px;">
							<div style= "font-size: 1.5rem; padding: 20px;">
							UPS Tracking</div>','hit_ups_auto');
						$to_disp = "";
						foreach ($track_datas as $value) {
							if (isset($general_settings['hit_ups_auto_site_id']) && isset($general_settings['hit_ups_auto_site_pwd']) && isset($general_settings['hit_ups_auto_access_key'])) {
								$trk_no = $value['tracking_num'];	//1ZA15888YW89001457
								if (isset($general_settings['hit_ups_auto_api_type']) && ($general_settings['hit_ups_auto_api_type'] == "REST") && isset($general_settings['hit_ups_auto_rest_site_id'])) {
									$auth_token = get_transient("hitshipo_ups_rest_auth_token_default");
									if (!class_exists("ups_rest")) {
										include_once("controllors/ups_rest_main.php");
									}
									$ups_rest_obj = new ups_rest();
									$ups_rest_obj->mode = (isset($general_settings['hit_ups_auto_test']) && $general_settings['hit_ups_auto_test'] == 'yes') ? 'test' : 'live';
									if (empty($auth_token)) {
										$auth_token = $ups_rest_obj->gen_access_token($value['hit_ups_auto_rest_grant_type'], $value['hit_ups_auto_rest_site_id'], $value['hit_ups_auto_rest_site_pwd']);
										set_transient("hitshipo_ups_rest_auth_token_".$key, $auth_token, 14200);
									}
									$result = $ups_rest_obj->make_trk_res_rest($trk_no, $auth_token);
									$xml = isset($result['trackResponse']) ? $result['trackResponse'] : [];
								} else {
									// $user_id = $value['user_id'];

									$request = '<?xml version="1.0"?>
												<AccessRequest xml:lang="en-US">
												  <AccessLicenseNumber>' . $general_settings['hit_ups_auto_access_key'] . '</AccessLicenseNumber>
												  <UserId>' . $general_settings['hit_ups_auto_site_id'] . '</UserId>
												  <Password>' . $general_settings['hit_ups_auto_site_pwd'] . '</Password>
												</AccessRequest>
												<?xml version="1.0"?>
												<TrackRequest xml:lang="en-US">
												  <Request>
												    <TransactionReference>
												      <CustomerContext>QAST Track</CustomerContext>
												      <XpciVersion>1.0</XpciVersion>
												    </TransactionReference>
												    <RequestAction>Track</RequestAction>
												    <RequestOption>activity</RequestOption>
												  </Request>
												  <TrackingNumber>' . $trk_no . '</TrackingNumber>
												</TrackRequest>';

									//<TrackingNumber>'.$row[8].'</TrackingNumber>  ER751105042015062,1Z12345E0291980793,1ZWX0692YP40636269		'.$trk_no.'
									$url = 'https://onlinetools.ups.com/ups.app/xml/Track';		//Live
									// $url='https://wwwcie.ups.com/ups.app/xml/Track'; 	//TEST
									

									$wp_pst = wp_remote_post($url, array(
										'body'        => $request,
										'timeout'     => '45',
										'redirection' => '10',
										'httpversion' => '1.0',
										'blocking'    => true,
										'sslverify'   => FALSE
									));
									
									$result = (string)isset($wp_pst['body']) ? $wp_pst['body'] : "";
									// $result = json_decode($result, true);
								}

								if (!empty($result)) {
									if (!isset($xml) || !is_array($xml)) {
										$xml = simplexml_load_string($result);
										$xml = json_decode(json_encode($xml), true);
									}

									if (isset($xml['Shipment']['Package']['Activity']) || isset($xml['shipment'][0]['package'][0]['activity'])) {
										$events = [];
										$last_event_status = '';
										if (isset($xml['Shipment']['Package']['Activity'][0]) || isset($xml['shipment'][0]['package'][0]['activity'][0])) {
											$events = isset($xml['shipment'][0]['package'][0]['activity'][0]) ? $xml['shipment'][0]['package'][0]['activity'] : $xml['Shipment']['Package']['Activity'];
										} else {
											$events[0] = isset($xml['shipment'][0]['package'][0]['activity']) ? $xml['shipment'][0]['package'][0]['activity'] : $xml['Shipment']['Package']['Activity'];
										}

										if (isset($events[0])) {
											if (isset($events[0]['status']['description'])) {	//REST
												$last_event_status = $events[0]['status']['description'];
											} else {
												$last_event_status = isset($events[0]['Status']['StatusType']['Description']) ? $events[0]['Status']['StatusType']['Description'] : '-';
											}
										}
										
										$to_disp .= '<div style= "background-color:#4CBB87; width: 100%; height: max-content; display: flex; flex-direction: row; text-align: center;">
															<div style= "color: #ecf0f1; display: inline-flex; flex-direction: column; align-items: center; padding: 23px; width: 50%; align-self: center;">Package Status: ' . $last_event_status . '</div>
															<span style= "border-left: 4px solid #fdfdfd; margin-top: 4%; margin-bottom: 4%;"></span>
															<div style= "color: #ecf0f1; display: inline-flex; flex-direction: column; align-items: center; padding: 12px; width: 50%; align-self: center;">Package ' . $trk_count . ' of ' . $tot_trk_count . '
																<span>Tracking No: ' . $trk_no . '</span>
															</div>
														</div>
														<div style= "padding-bottom: 5px;">
															<ul style= "list-style: none; padding-bottom: 5px;">';

										foreach ($events as $key => $value) {
											if (isset($value['status']['description'])) {	// REST
												$event_status = $value['status']['description'];
											} else {
												$event_status = isset($value['Status']['StatusType']['Description']) ? $value['Status']['StatusType']['Description'] : '-';
											}
											if (isset($value['location']['address']['city'])) {	// REST
												$event_loc = $value['location']['address']['city'];
											} else {
												$event_loc = isset($value['ActivityLocation']['Address']['City']) ? $value['ActivityLocation']['Address']['City'] : '-';
											}
											if (isset($value['location']['address']['countryCode'])) {	// REST
												$event_country = $value['location']['address']['countryCode'];
											} else {
												$event_country = isset($value['ActivityLocation']['Address']['CountryCode']) ? $value['ActivityLocation']['Address']['CountryCode'] : '-';
											}
											if (isset($value['time'])) {	// REST
												$event_time = isset($value['time']) ? date('h:i - A', strtotime($value['time'])) : '-';
											} else {
												$event_time = isset($value['GMTTime']) ? date('h:i - A', strtotime($value['GMTTime'])) : '-';
											}
											if (isset($value['date'])) {	// REST
												$event_date = isset($value['date']) ? date('M d Y', strtotime($value['date'])) : '-';
											} else {
												$event_date = isset($value['GMTDate']) ? date('M d Y', strtotime($value['GMTDate'])) : '-';
											}
											

											$to_disp .= '<li style= "display: flex; flex-direction: row;font-size: 16px;">
																<div style= "display: flex;margin-top: 0px; margin-bottom: 0px; ">
																	<div style="border-left:1px #ecf0f1 solid; position: relative; left:161px; height:150%; margin-top: -28px; z-index: -1;"></div>
																	<div style= "display: flex; flex-direction: column; width: 120px; align-items: end;">
																		<p style= "font-weight: bold; margin: 0;">' . $event_date . '</p>
																		<p style= "margin: 0; color: #4a5568;">' . $event_time . '</p>
																	</div>
																	<div style= "display: flex; flex-direction: column; width: 80px; align-items: center;">';

											if (isset($value['Status']['StatusType']['Code']) && $value['Status']['StatusType']['Code'] == "D") {
												$to_disp .= '<img style="width: 34px; height: 34px;" src="data:image/svg+xml;charset=utf-8;base64,PHN2ZyB4bWxuczpza2V0Y2g9Imh0dHA6Ly93d3cuYm9oZW1pYW5jb2RpbmcuY29tL3NrZXRjaC9ucyIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB2aWV3Qm94PSIwIDAgMTI4IDEyOCIgZW5hYmxlLWJhY2tncm91bmQ9Im5ldyAwIDAgMTI4IDEyOCI+PHN0eWxlIHR5cGU9InRleHQvY3NzIj4uc3Qwe2ZpbGw6IzRDQkI4Nzt9IC5zdDF7ZmlsbDojRkZGRkZGO308L3N0eWxlPjxnIGlkPSJEZWxpdmVkIiBza2V0Y2g6dHlwZT0iTVNMYXllckdyb3VwIj48cGF0aCBpZD0iT3ZhbC03LUNvcHktMiIgc2tldGNoOnR5cGU9Ik1TU2hhcGVHcm91cCIgY2xhc3M9InN0MCIgZD0iTTY0IDEyOGMzNS4zIDAgNjQtMjguNyA2NC02NHMtMjguNy02NC02NC02NC02NCAyOC43LTY0IDY0IDI4LjcgNjQgNjQgNjR6Ii8+PHBhdGggaWQ9IlNoYXBlIiBza2V0Y2g6dHlwZT0iTVNTaGFwZUdyb3VwIiBjbGFzcz0ic3QxIiBkPSJNODIuNSA1My4ybC0zLjQtMy40Yy0uNS0uNS0xLS43LTEuNy0uN3MtMS4yLjItMS43LjdsLTE2LjIgMTYuNS03LjMtNy40Yy0uNS0uNS0xLS43LTEuNy0uN3MtMS4yLjItMS43LjdsLTMuNCAzLjRjLS41LjUtLjcgMS0uNyAxLjdzLjIgMS4yLjcgMS43bDkgOS4xIDMuNCAzLjRjLjUuNSAxIC43IDEuNy43czEuMi0uMiAxLjctLjdsMy40LTMuNCAxNy45LTE4LjJjLjUtLjUuNy0xIC43LTEuN3MtLjItMS4yLS43LTEuN3oiLz48L2c+PC9zdmc+">';
											} elseif(isset($value['status']['type']['code']) && $value['status']['type']['code'] == "D"){	// REST
												$to_disp .= '<img style="width: 34px; height: 34px;" src="data:image/svg+xml;charset=utf-8;base64,PHN2ZyB4bWxuczpza2V0Y2g9Imh0dHA6Ly93d3cuYm9oZW1pYW5jb2RpbmcuY29tL3NrZXRjaC9ucyIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB2aWV3Qm94PSIwIDAgMTI4IDEyOCIgZW5hYmxlLWJhY2tncm91bmQ9Im5ldyAwIDAgMTI4IDEyOCI+PHN0eWxlIHR5cGU9InRleHQvY3NzIj4uc3Qwe2ZpbGw6IzRDQkI4Nzt9IC5zdDF7ZmlsbDojRkZGRkZGO308L3N0eWxlPjxnIGlkPSJEZWxpdmVkIiBza2V0Y2g6dHlwZT0iTVNMYXllckdyb3VwIj48cGF0aCBpZD0iT3ZhbC03LUNvcHktMiIgc2tldGNoOnR5cGU9Ik1TU2hhcGVHcm91cCIgY2xhc3M9InN0MCIgZD0iTTY0IDEyOGMzNS4zIDAgNjQtMjguNyA2NC02NHMtMjguNy02NC02NC02NC02NCAyOC43LTY0IDY0IDI4LjcgNjQgNjQgNjR6Ii8+PHBhdGggaWQ9IlNoYXBlIiBza2V0Y2g6dHlwZT0iTVNTaGFwZUdyb3VwIiBjbGFzcz0ic3QxIiBkPSJNODIuNSA1My4ybC0zLjQtMy40Yy0uNS0uNS0xLS43LTEuNy0uN3MtMS4yLjItMS43LjdsLTE2LjIgMTYuNS03LjMtNy40Yy0uNS0uNS0xLS43LTEuNy0uN3MtMS4yLjItMS43LjdsLTMuNCAzLjRjLS41LjUtLjcgMS0uNyAxLjdzLjIgMS4yLjcgMS43bDkgOS4xIDMuNCAzLjRjLjUuNSAxIC43IDEuNy43czEuMi0uMiAxLjctLjdsMy40LTMuNCAxNy45LTE4LjJjLjUtLjUuNy0xIC43LTEuN3MtLjItMS4yLS43LTEuN3oiLz48L2c+PC9zdmc+">';
											} else {
												$to_disp .= '<div style="width: 36px; height: 36px; border-radius: 50%; border-width: 1px; border-style: solid; border-color: #ecf0f1; margin-top: 10px; background-color: #ffffff;">
																	<div style="width: 12px; height: 12px; transform: translate(-50%,-50%); background-color: #ddd; border-radius: 100%; margin-top: 17px; margin-left: 17px;"></div>
																</div>';
											}

											$to_disp .= '</div>
																	<div style= "display: flex; flex-direction: column; width: 300px;">
																		<p style= "font-weight: bold; margin: 0;">' . $event_status . '</p>
																		<p style= "margin: 0; color: #4a5568;">' . $event_loc . ' , ' . $event_country . '</p>
																	</div>
																</div>
															</li>';
										}
										$to_disp .= '</ul></div>';
									} else {
										$to_disp .= '<h4 style= "text-align: center;">Sorry! No data found for this package...<h4/></div>';
										_e($to_disp,'hit_ups_auto');
										return;
									}
								} else {
									$to_disp .= '<h4 style= "text-align: center;>Sorry! No data found for this package...<h4/></div>';
									_e($to_disp,'hit_ups_auto');
									return;
								}
							}
							$trk_count++;
						}
						$to_disp .= '</div>';
						_e($to_disp,'hit_ups_auto');
					}
				}
			}

			public function save_user_fields($user_id)
			{
				if (isset($_POST['hit_ups_auto_country'])) {
					$general_settings['hit_ups_auto_site_id'] = sanitize_text_field(isset($_POST['hit_ups_auto_site_id']) ? $_POST['hit_ups_auto_site_id'] : '');
					$general_settings['hit_ups_auto_site_pwd'] = sanitize_text_field(isset($_POST['hit_ups_auto_site_pwd']) ? $_POST['hit_ups_auto_site_pwd'] : '');
					$general_settings['hit_ups_auto_acc_no'] = sanitize_text_field(isset($_POST['hit_ups_auto_acc_no']) ? $_POST['hit_ups_auto_acc_no'] : '');
					$general_settings['hit_ups_auto_access_key'] = sanitize_text_field(isset($_POST['hit_ups_auto_access_key']) ? $_POST['hit_ups_auto_access_key'] : '');
					$general_settings['hit_ups_auto_rest_site_id'] = sanitize_text_field(isset($_POST['hit_ups_auto_rest_site_id']) ? $_POST['hit_ups_auto_rest_site_id'] : '');
					$general_settings['hit_ups_auto_rest_site_pwd'] = sanitize_text_field(isset($_POST['hit_ups_auto_rest_site_pwd']) ? $_POST['hit_ups_auto_rest_site_pwd'] : '');
					$general_settings['hit_ups_auto_rest_acc_no'] = sanitize_text_field(isset($_POST['hit_ups_auto_rest_acc_no']) ? $_POST['hit_ups_auto_rest_acc_no'] : '');
					$general_settings['hit_ups_auto_rest_grant_type'] = sanitize_text_field(isset($_POST['hit_ups_auto_rest_grant_type']) ? $_POST['hit_ups_auto_rest_grant_type'] : '');
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
					$general_settings['hit_ups_auto_con_rate'] = sanitize_text_field(isset($_POST['hit_ups_auto_con_rate']) ? $_POST['hit_ups_auto_con_rate'] : '');
					$general_settings['hit_ups_auto_def_dom'] = sanitize_text_field(isset($_POST['hit_ups_auto_def_dom']) ? $_POST['hit_ups_auto_def_dom'] : '');

					$general_settings['hit_ups_auto_def_inter'] = sanitize_text_field(isset($_POST['hit_ups_auto_def_inter']) ? $_POST['hit_ups_auto_def_inter'] : '');

					update_post_meta($user_id, 'hit_ups_auto_vendor_settings', $general_settings);
				}
			}

			public function hit_define_ups_credentails($user)
			{

				$main_settings = get_option('hit_ups_auto_main_settings');
				$main_settings = empty($main_settings) ? array() : $main_settings;
				$allow = false;

				if (!isset($main_settings['hit_ups_auto_v_roles'])) {
					return;
				} else {
					foreach ($user->roles as $value) {
						if (in_array($value, $main_settings['hit_ups_auto_v_roles'])) {
							$allow = true;
						}
					}
				}

				if (!$allow) {
					return;
				}

				$general_settings = get_post_meta($user->ID, 'hit_ups_auto_vendor_settings', true);
				$general_settings = empty($general_settings) ? array() : $general_settings;
				$ven_col_type = array(" 0356" => "IOSS", " 0357" => "VOEC", "0358" => "HMRC", "0359" => "PVA");
				$countires =  array(
					'AF' => 'Afghanistan',
					'AL' => 'Albania',
					'DZ' => 'Algeria',
					'AS' => 'American Samoa',
					'AD' => 'Andorra',
					'AO' => 'Angola',
					'AI' => 'Anguilla',
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
					'BA' => 'Bosnia and Herzegovina',
					'BW' => 'Botswana',
					'BR' => 'Brazil',
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
					'CO' => 'Colombia',
					'KM' => 'Comoros',
					'CK' => 'Cook Islands',
					'CR' => 'Costa Rica',
					'HR' => 'Croatia',
					'CU' => 'Cuba',
					'CY' => 'Cyprus',
					'CZ' => 'Czech Republic',
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
					'HN' => 'Honduras',
					'HK' => 'Hong Kong',
					'HU' => 'Hungary',
					'IS' => 'Iceland',
					'IN' => 'India',
					'ID' => 'Indonesia',
					'IR' => 'Iran',
					'IQ' => 'Iraq',
					'IE' => 'Ireland',
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
					'KP' => 'North Korea',
					'MP' => 'Northern Mariana Islands',
					'NO' => 'Norway',
					'OM' => 'Oman',
					'PK' => 'Pakistan',
					'PW' => 'Palau',
					'PA' => 'Panama',
					'PG' => 'Papua New Guinea',
					'PY' => 'Paraguay',
					'PE' => 'Peru',
					'PH' => 'Philippines',
					'PL' => 'Poland',
					'PT' => 'Portugal',
					'PR' => 'Puerto Rico',
					'QA' => 'Qatar',
					'CG' => 'Republic of the Congo',
					'RE' => 'Reunion',
					'RO' => 'Romania',
					'RU' => 'Russia',
					'RW' => 'Rwanda',
					'SH' => 'Saint Helena',
					'KN' => 'Saint Kitts and Nevis',
					'LC' => 'Saint Lucia',
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
					'SK' => 'Slovakia',
					'SI' => 'Slovenia',
					'SB' => 'Solomon Islands',
					'SO' => 'Somalia',
					'ZA' => 'South Africa',
					'KR' => 'South Korea',
					'SS' => 'South Sudan',
					'ES' => 'Spain',
					'LK' => 'Sri Lanka',
					'SD' => 'Sudan',
					'SR' => 'Suriname',
					'SZ' => 'Swaziland',
					'SE' => 'Sweden',
					'CH' => 'Switzerland',
					'SY' => 'Syria',
					'TW' => 'Taiwan',
					'TJ' => 'Tajikistan',
					'TZ' => 'Tanzania',
					'TH' => 'Thailand',
					'TG' => 'Togo',
					'TO' => 'Tonga',
					'TT' => 'Trinidad and Tobago',
					'TN' => 'Tunisia',
					'TR' => 'Turkey',
					'TC' => 'Turks and Caicos Islands',
					'TV' => 'Tuvalu',
					'VI' => 'U.S. Virgin Islands',
					'UG' => 'Uganda',
					'UA' => 'Ukraine',
					'AE' => 'United Arab Emirates',
					'GB' => 'United Kingdom',
					'US' => 'United States',
					'UY' => 'Uruguay',
					'UZ' => 'Uzbekistan',
					'VU' => 'Vanuatu',
					'VE' => 'Venezuela',
					'VN' => 'Vietnam',
					'YE' => 'Yemen',
					'ZM' => 'Zambia',
					'ZW' => 'Zimbabwe',
				);
				$_ups_carriers = array(
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

				$ups_core = array();
				$ups_core['AD'] = array('region' => 'EU', 'currency' => 'EUR', 'weight' => 'KG_CM');
				$ups_core['AE'] = array('region' => 'AP', 'currency' => 'AED', 'weight' => 'KG_CM');
				$ups_core['AF'] = array('region' => 'AP', 'currency' => 'AFN', 'weight' => 'KG_CM');
				$ups_core['AG'] = array('region' => 'AM', 'currency' => 'XCD', 'weight' => 'LB_IN');
				$ups_core['AI'] = array('region' => 'AM', 'currency' => 'XCD', 'weight' => 'LB_IN');
				$ups_core['AL'] = array('region' => 'AP', 'currency' => 'EUR', 'weight' => 'KG_CM');
				$ups_core['AM'] = array('region' => 'AP', 'currency' => 'AMD', 'weight' => 'KG_CM');
				$ups_core['AN'] = array('region' => 'AM', 'currency' => 'ANG', 'weight' => 'KG_CM');
				$ups_core['AO'] = array('region' => 'AP', 'currency' => 'AOA', 'weight' => 'KG_CM');
				$ups_core['AR'] = array('region' => 'AM', 'currency' => 'ARS', 'weight' => 'KG_CM');
				$ups_core['AS'] = array('region' => 'AM', 'currency' => 'USD', 'weight' => 'LB_IN');
				$ups_core['AT'] = array('region' => 'EU', 'currency' => 'EUR', 'weight' => 'KG_CM');
				$ups_core['AU'] = array('region' => 'AP', 'currency' => 'AUD', 'weight' => 'KG_CM');
				$ups_core['AW'] = array('region' => 'AM', 'currency' => 'AWG', 'weight' => 'LB_IN');
				$ups_core['AZ'] = array('region' => 'AM', 'currency' => 'AZN', 'weight' => 'KG_CM');
				$ups_core['AZ'] = array('region' => 'AM', 'currency' => 'AZN', 'weight' => 'KG_CM');
				$ups_core['GB'] = array('region' => 'EU', 'currency' => 'GBP', 'weight' => 'KG_CM');
				$ups_core['BA'] = array('region' => 'AP', 'currency' => 'BAM', 'weight' => 'KG_CM');
				$ups_core['BB'] = array('region' => 'AM', 'currency' => 'BBD', 'weight' => 'LB_IN');
				$ups_core['BD'] = array('region' => 'AP', 'currency' => 'BDT', 'weight' => 'KG_CM');
				$ups_core['BE'] = array('region' => 'EU', 'currency' => 'EUR', 'weight' => 'KG_CM');
				$ups_core['BF'] = array('region' => 'AP', 'currency' => 'XOF', 'weight' => 'KG_CM');
				$ups_core['BG'] = array('region' => 'EU', 'currency' => 'BGN', 'weight' => 'KG_CM');
				$ups_core['BH'] = array('region' => 'AP', 'currency' => 'BHD', 'weight' => 'KG_CM');
				$ups_core['BI'] = array('region' => 'AP', 'currency' => 'BIF', 'weight' => 'KG_CM');
				$ups_core['BJ'] = array('region' => 'AP', 'currency' => 'XOF', 'weight' => 'KG_CM');
				$ups_core['BM'] = array('region' => 'AM', 'currency' => 'BMD', 'weight' => 'LB_IN');
				$ups_core['BN'] = array('region' => 'AP', 'currency' => 'BND', 'weight' => 'KG_CM');
				$ups_core['BO'] = array('region' => 'AM', 'currency' => 'BOB', 'weight' => 'KG_CM');
				$ups_core['BR'] = array('region' => 'AM', 'currency' => 'BRL', 'weight' => 'KG_CM');
				$ups_core['BS'] = array('region' => 'AM', 'currency' => 'BSD', 'weight' => 'LB_IN');
				$ups_core['BT'] = array('region' => 'AP', 'currency' => 'BTN', 'weight' => 'KG_CM');
				$ups_core['BW'] = array('region' => 'AP', 'currency' => 'BWP', 'weight' => 'KG_CM');
				$ups_core['BY'] = array('region' => 'AP', 'currency' => 'BYR', 'weight' => 'KG_CM');
				$ups_core['BZ'] = array('region' => 'AM', 'currency' => 'BZD', 'weight' => 'KG_CM');
				$ups_core['CA'] = array('region' => 'AM', 'currency' => 'CAD', 'weight' => 'LB_IN');
				$ups_core['CF'] = array('region' => 'AP', 'currency' => 'XAF', 'weight' => 'KG_CM');
				$ups_core['CG'] = array('region' => 'AP', 'currency' => 'XAF', 'weight' => 'KG_CM');
				$ups_core['CH'] = array('region' => 'EU', 'currency' => 'CHF', 'weight' => 'KG_CM');
				$ups_core['CI'] = array('region' => 'AP', 'currency' => 'XOF', 'weight' => 'KG_CM');
				$ups_core['CK'] = array('region' => 'AP', 'currency' => 'NZD', 'weight' => 'KG_CM');
				$ups_core['CL'] = array('region' => 'AM', 'currency' => 'CLP', 'weight' => 'KG_CM');
				$ups_core['CM'] = array('region' => 'AP', 'currency' => 'XAF', 'weight' => 'KG_CM');
				$ups_core['CN'] = array('region' => 'AP', 'currency' => 'CNY', 'weight' => 'KG_CM');
				$ups_core['CO'] = array('region' => 'AM', 'currency' => 'COP', 'weight' => 'KG_CM');
				$ups_core['CR'] = array('region' => 'AM', 'currency' => 'CRC', 'weight' => 'KG_CM');
				$ups_core['CU'] = array('region' => 'AM', 'currency' => 'CUC', 'weight' => 'KG_CM');
				$ups_core['CV'] = array('region' => 'AP', 'currency' => 'CVE', 'weight' => 'KG_CM');
				$ups_core['CY'] = array('region' => 'AP', 'currency' => 'EUR', 'weight' => 'KG_CM');
				$ups_core['CZ'] = array('region' => 'EU', 'currency' => 'CZK', 'weight' => 'KG_CM');
				$ups_core['DE'] = array('region' => 'AP', 'currency' => 'EUR', 'weight' => 'KG_CM');
				$ups_core['DJ'] = array('region' => 'EU', 'currency' => 'DJF', 'weight' => 'KG_CM');
				$ups_core['DK'] = array('region' => 'AM', 'currency' => 'DKK', 'weight' => 'KG_CM');
				$ups_core['DM'] = array('region' => 'AM', 'currency' => 'XCD', 'weight' => 'LB_IN');
				$ups_core['DO'] = array('region' => 'AP', 'currency' => 'DOP', 'weight' => 'LB_IN');
				$ups_core['DZ'] = array('region' => 'AM', 'currency' => 'DZD', 'weight' => 'KG_CM');
				$ups_core['EC'] = array('region' => 'EU', 'currency' => 'USD', 'weight' => 'KG_CM');
				$ups_core['EE'] = array('region' => 'AP', 'currency' => 'EUR', 'weight' => 'KG_CM');
				$ups_core['EG'] = array('region' => 'AP', 'currency' => 'EGP', 'weight' => 'KG_CM');
				$ups_core['ER'] = array('region' => 'EU', 'currency' => 'ERN', 'weight' => 'KG_CM');
				$ups_core['ES'] = array('region' => 'AP', 'currency' => 'EUR', 'weight' => 'KG_CM');
				$ups_core['ET'] = array('region' => 'AU', 'currency' => 'ETB', 'weight' => 'KG_CM');
				$ups_core['FI'] = array('region' => 'AP', 'currency' => 'EUR', 'weight' => 'KG_CM');
				$ups_core['FJ'] = array('region' => 'AP', 'currency' => 'FJD', 'weight' => 'KG_CM');
				$ups_core['FK'] = array('region' => 'AM', 'currency' => 'GBP', 'weight' => 'KG_CM');
				$ups_core['FM'] = array('region' => 'AM', 'currency' => 'USD', 'weight' => 'LB_IN');
				$ups_core['FO'] = array('region' => 'AM', 'currency' => 'DKK', 'weight' => 'KG_CM');
				$ups_core['FR'] = array('region' => 'EU', 'currency' => 'EUR', 'weight' => 'KG_CM');
				$ups_core['GA'] = array('region' => 'AP', 'currency' => 'XAF', 'weight' => 'KG_CM');
				$ups_core['GB'] = array('region' => 'EU', 'currency' => 'GBP', 'weight' => 'KG_CM');
				$ups_core['GD'] = array('region' => 'AM', 'currency' => 'XCD', 'weight' => 'LB_IN');
				$ups_core['GE'] = array('region' => 'AM', 'currency' => 'GEL', 'weight' => 'KG_CM');
				$ups_core['GF'] = array('region' => 'AM', 'currency' => 'EUR', 'weight' => 'KG_CM');
				$ups_core['GG'] = array('region' => 'AM', 'currency' => 'GBP', 'weight' => 'KG_CM');
				$ups_core['GH'] = array('region' => 'AP', 'currency' => 'GBS', 'weight' => 'KG_CM');
				$ups_core['GI'] = array('region' => 'AM', 'currency' => 'GBP', 'weight' => 'KG_CM');
				$ups_core['GL'] = array('region' => 'AM', 'currency' => 'DKK', 'weight' => 'KG_CM');
				$ups_core['GM'] = array('region' => 'AP', 'currency' => 'GMD', 'weight' => 'KG_CM');
				$ups_core['GN'] = array('region' => 'AP', 'currency' => 'GNF', 'weight' => 'KG_CM');
				$ups_core['GP'] = array('region' => 'AM', 'currency' => 'EUR', 'weight' => 'KG_CM');
				$ups_core['GQ'] = array('region' => 'AP', 'currency' => 'XAF', 'weight' => 'KG_CM');
				$ups_core['GR'] = array('region' => 'EU', 'currency' => 'EUR', 'weight' => 'KG_CM');
				$ups_core['GT'] = array('region' => 'AM', 'currency' => 'GTQ', 'weight' => 'KG_CM');
				$ups_core['GU'] = array('region' => 'AM', 'currency' => 'USD', 'weight' => 'LB_IN');
				$ups_core['GW'] = array('region' => 'AP', 'currency' => 'XOF', 'weight' => 'KG_CM');
				$ups_core['GY'] = array('region' => 'AP', 'currency' => 'GYD', 'weight' => 'LB_IN');
				$ups_core['HK'] = array('region' => 'AM', 'currency' => 'HKD', 'weight' => 'KG_CM');
				$ups_core['HN'] = array('region' => 'AM', 'currency' => 'HNL', 'weight' => 'KG_CM');
				$ups_core['HR'] = array('region' => 'AP', 'currency' => 'HRK', 'weight' => 'KG_CM');
				$ups_core['HT'] = array('region' => 'AM', 'currency' => 'HTG', 'weight' => 'LB_IN');
				$ups_core['HU'] = array('region' => 'EU', 'currency' => 'HUF', 'weight' => 'KG_CM');
				$ups_core['IC'] = array('region' => 'EU', 'currency' => 'EUR', 'weight' => 'KG_CM');
				$ups_core['ID'] = array('region' => 'AP', 'currency' => 'IDR', 'weight' => 'KG_CM');
				$ups_core['IE'] = array('region' => 'EU', 'currency' => 'EUR', 'weight' => 'KG_CM');
				$ups_core['IL'] = array('region' => 'AP', 'currency' => 'ILS', 'weight' => 'KG_CM');
				$ups_core['IN'] = array('region' => 'AP', 'currency' => 'INR', 'weight' => 'KG_CM');
				$ups_core['IQ'] = array('region' => 'AP', 'currency' => 'IQD', 'weight' => 'KG_CM');
				$ups_core['IR'] = array('region' => 'AP', 'currency' => 'IRR', 'weight' => 'KG_CM');
				$ups_core['IS'] = array('region' => 'EU', 'currency' => 'ISK', 'weight' => 'KG_CM');
				$ups_core['IT'] = array('region' => 'EU', 'currency' => 'EUR', 'weight' => 'KG_CM');
				$ups_core['JE'] = array('region' => 'AM', 'currency' => 'GBP', 'weight' => 'KG_CM');
				$ups_core['JM'] = array('region' => 'AM', 'currency' => 'JMD', 'weight' => 'KG_CM');
				$ups_core['JO'] = array('region' => 'AP', 'currency' => 'JOD', 'weight' => 'KG_CM');
				$ups_core['JP'] = array('region' => 'AP', 'currency' => 'JPY', 'weight' => 'KG_CM');
				$ups_core['KE'] = array('region' => 'AP', 'currency' => 'KES', 'weight' => 'KG_CM');
				$ups_core['KG'] = array('region' => 'AP', 'currency' => 'KGS', 'weight' => 'KG_CM');
				$ups_core['KH'] = array('region' => 'AP', 'currency' => 'KHR', 'weight' => 'KG_CM');
				$ups_core['KI'] = array('region' => 'AP', 'currency' => 'AUD', 'weight' => 'KG_CM');
				$ups_core['KM'] = array('region' => 'AP', 'currency' => 'KMF', 'weight' => 'KG_CM');
				$ups_core['KN'] = array('region' => 'AM', 'currency' => 'XCD', 'weight' => 'LB_IN');
				$ups_core['KP'] = array('region' => 'AP', 'currency' => 'KPW', 'weight' => 'LB_IN');
				$ups_core['KR'] = array('region' => 'AP', 'currency' => 'KRW', 'weight' => 'KG_CM');
				$ups_core['KV'] = array('region' => 'AM', 'currency' => 'EUR', 'weight' => 'KG_CM');
				$ups_core['KW'] = array('region' => 'AP', 'currency' => 'KWD', 'weight' => 'KG_CM');
				$ups_core['KY'] = array('region' => 'AM', 'currency' => 'KYD', 'weight' => 'KG_CM');
				$ups_core['KZ'] = array('region' => 'AP', 'currency' => 'KZF', 'weight' => 'LB_IN');
				$ups_core['LA'] = array('region' => 'AP', 'currency' => 'LAK', 'weight' => 'KG_CM');
				$ups_core['LB'] = array('region' => 'AP', 'currency' => 'USD', 'weight' => 'KG_CM');
				$ups_core['LC'] = array('region' => 'AM', 'currency' => 'XCD', 'weight' => 'KG_CM');
				$ups_core['LI'] = array('region' => 'AM', 'currency' => 'CHF', 'weight' => 'LB_IN');
				$ups_core['LK'] = array('region' => 'AP', 'currency' => 'LKR', 'weight' => 'KG_CM');
				$ups_core['LR'] = array('region' => 'AP', 'currency' => 'LRD', 'weight' => 'KG_CM');
				$ups_core['LS'] = array('region' => 'AP', 'currency' => 'LSL', 'weight' => 'KG_CM');
				$ups_core['LT'] = array('region' => 'EU', 'currency' => 'EUR', 'weight' => 'KG_CM');
				$ups_core['LU'] = array('region' => 'EU', 'currency' => 'EUR', 'weight' => 'KG_CM');
				$ups_core['LV'] = array('region' => 'EU', 'currency' => 'EUR', 'weight' => 'KG_CM');
				$ups_core['LY'] = array('region' => 'AP', 'currency' => 'LYD', 'weight' => 'KG_CM');
				$ups_core['MA'] = array('region' => 'AP', 'currency' => 'MAD', 'weight' => 'KG_CM');
				$ups_core['MC'] = array('region' => 'AM', 'currency' => 'EUR', 'weight' => 'KG_CM');
				$ups_core['MD'] = array('region' => 'AP', 'currency' => 'MDL', 'weight' => 'KG_CM');
				$ups_core['ME'] = array('region' => 'AM', 'currency' => 'EUR', 'weight' => 'KG_CM');
				$ups_core['MG'] = array('region' => 'AP', 'currency' => 'MGA', 'weight' => 'KG_CM');
				$ups_core['MH'] = array('region' => 'AM', 'currency' => 'USD', 'weight' => 'LB_IN');
				$ups_core['MK'] = array('region' => 'AP', 'currency' => 'MKD', 'weight' => 'KG_CM');
				$ups_core['ML'] = array('region' => 'AP', 'currency' => 'COF', 'weight' => 'KG_CM');
				$ups_core['MM'] = array('region' => 'AP', 'currency' => 'USD', 'weight' => 'KG_CM');
				$ups_core['MN'] = array('region' => 'AP', 'currency' => 'MNT', 'weight' => 'KG_CM');
				$ups_core['MO'] = array('region' => 'AP', 'currency' => 'MOP', 'weight' => 'KG_CM');
				$ups_core['MP'] = array('region' => 'AM', 'currency' => 'USD', 'weight' => 'LB_IN');
				$ups_core['MQ'] = array('region' => 'AM', 'currency' => 'EUR', 'weight' => 'KG_CM');
				$ups_core['MR'] = array('region' => 'AP', 'currency' => 'MRO', 'weight' => 'KG_CM');
				$ups_core['MS'] = array('region' => 'AM', 'currency' => 'XCD', 'weight' => 'LB_IN');
				$ups_core['MT'] = array('region' => 'AP', 'currency' => 'EUR', 'weight' => 'KG_CM');
				$ups_core['MU'] = array('region' => 'AP', 'currency' => 'MUR', 'weight' => 'KG_CM');
				$ups_core['MV'] = array('region' => 'AP', 'currency' => 'MVR', 'weight' => 'KG_CM');
				$ups_core['MW'] = array('region' => 'AP', 'currency' => 'MWK', 'weight' => 'KG_CM');
				$ups_core['MX'] = array('region' => 'AM', 'currency' => 'MXN', 'weight' => 'KG_CM');
				$ups_core['MY'] = array('region' => 'AP', 'currency' => 'MYR', 'weight' => 'KG_CM');
				$ups_core['MZ'] = array('region' => 'AP', 'currency' => 'MZN', 'weight' => 'KG_CM');
				$ups_core['NA'] = array('region' => 'AP', 'currency' => 'NAD', 'weight' => 'KG_CM');
				$ups_core['NC'] = array('region' => 'AP', 'currency' => 'XPF', 'weight' => 'KG_CM');
				$ups_core['NE'] = array('region' => 'AP', 'currency' => 'XOF', 'weight' => 'KG_CM');
				$ups_core['NG'] = array('region' => 'AP', 'currency' => 'NGN', 'weight' => 'KG_CM');
				$ups_core['NI'] = array('region' => 'AM', 'currency' => 'NIO', 'weight' => 'KG_CM');
				$ups_core['NL'] = array('region' => 'EU', 'currency' => 'EUR', 'weight' => 'KG_CM');
				$ups_core['NO'] = array('region' => 'EU', 'currency' => 'NOK', 'weight' => 'KG_CM');
				$ups_core['NP'] = array('region' => 'AP', 'currency' => 'NPR', 'weight' => 'KG_CM');
				$ups_core['NR'] = array('region' => 'AP', 'currency' => 'AUD', 'weight' => 'KG_CM');
				$ups_core['NU'] = array('region' => 'AP', 'currency' => 'NZD', 'weight' => 'KG_CM');
				$ups_core['NZ'] = array('region' => 'AP', 'currency' => 'NZD', 'weight' => 'KG_CM');
				$ups_core['OM'] = array('region' => 'AP', 'currency' => 'OMR', 'weight' => 'KG_CM');
				$ups_core['PA'] = array('region' => 'AM', 'currency' => 'USD', 'weight' => 'KG_CM');
				$ups_core['PE'] = array('region' => 'AM', 'currency' => 'PEN', 'weight' => 'KG_CM');
				$ups_core['PF'] = array('region' => 'AP', 'currency' => 'XPF', 'weight' => 'KG_CM');
				$ups_core['PG'] = array('region' => 'AP', 'currency' => 'PGK', 'weight' => 'KG_CM');
				$ups_core['PH'] = array('region' => 'AP', 'currency' => 'PHP', 'weight' => 'KG_CM');
				$ups_core['PK'] = array('region' => 'AP', 'currency' => 'PKR', 'weight' => 'KG_CM');
				$ups_core['PL'] = array('region' => 'EU', 'currency' => 'PLN', 'weight' => 'KG_CM');
				$ups_core['PR'] = array('region' => 'AM', 'currency' => 'USD', 'weight' => 'LB_IN');
				$ups_core['PT'] = array('region' => 'EU', 'currency' => 'EUR', 'weight' => 'KG_CM');
				$ups_core['PW'] = array('region' => 'AM', 'currency' => 'USD', 'weight' => 'KG_CM');
				$ups_core['PY'] = array('region' => 'AM', 'currency' => 'PYG', 'weight' => 'KG_CM');
				$ups_core['QA'] = array('region' => 'AP', 'currency' => 'QAR', 'weight' => 'KG_CM');
				$ups_core['RE'] = array('region' => 'AP', 'currency' => 'EUR', 'weight' => 'KG_CM');
				$ups_core['RO'] = array('region' => 'EU', 'currency' => 'RON', 'weight' => 'KG_CM');
				$ups_core['RS'] = array('region' => 'AP', 'currency' => 'RSD', 'weight' => 'KG_CM');
				$ups_core['RU'] = array('region' => 'AP', 'currency' => 'RUB', 'weight' => 'KG_CM');
				$ups_core['RW'] = array('region' => 'AP', 'currency' => 'RWF', 'weight' => 'KG_CM');
				$ups_core['SA'] = array('region' => 'AP', 'currency' => 'SAR', 'weight' => 'KG_CM');
				$ups_core['SB'] = array('region' => 'AP', 'currency' => 'SBD', 'weight' => 'KG_CM');
				$ups_core['SC'] = array('region' => 'AP', 'currency' => 'SCR', 'weight' => 'KG_CM');
				$ups_core['SD'] = array('region' => 'AP', 'currency' => 'SDG', 'weight' => 'KG_CM');
				$ups_core['SE'] = array('region' => 'EU', 'currency' => 'SEK', 'weight' => 'KG_CM');
				$ups_core['SG'] = array('region' => 'AP', 'currency' => 'SGD', 'weight' => 'KG_CM');
				$ups_core['SH'] = array('region' => 'AP', 'currency' => 'SHP', 'weight' => 'KG_CM');
				$ups_core['SI'] = array('region' => 'EU', 'currency' => 'EUR', 'weight' => 'KG_CM');
				$ups_core['SK'] = array('region' => 'EU', 'currency' => 'EUR', 'weight' => 'KG_CM');
				$ups_core['SL'] = array('region' => 'AP', 'currency' => 'SLL', 'weight' => 'KG_CM');
				$ups_core['SM'] = array('region' => 'EU', 'currency' => 'EUR', 'weight' => 'KG_CM');
				$ups_core['SN'] = array('region' => 'AP', 'currency' => 'XOF', 'weight' => 'KG_CM');
				$ups_core['SO'] = array('region' => 'AM', 'currency' => 'SOS', 'weight' => 'KG_CM');
				$ups_core['SR'] = array('region' => 'AM', 'currency' => 'SRD', 'weight' => 'KG_CM');
				$ups_core['SS'] = array('region' => 'AP', 'currency' => 'SSP', 'weight' => 'KG_CM');
				$ups_core['ST'] = array('region' => 'AP', 'currency' => 'STD', 'weight' => 'KG_CM');
				$ups_core['SV'] = array('region' => 'AM', 'currency' => 'USD', 'weight' => 'KG_CM');
				$ups_core['SY'] = array('region' => 'AP', 'currency' => 'SYP', 'weight' => 'KG_CM');
				$ups_core['SZ'] = array('region' => 'AP', 'currency' => 'SZL', 'weight' => 'KG_CM');
				$ups_core['TC'] = array('region' => 'AM', 'currency' => 'USD', 'weight' => 'LB_IN');
				$ups_core['TD'] = array('region' => 'AP', 'currency' => 'XAF', 'weight' => 'KG_CM');
				$ups_core['TG'] = array('region' => 'AP', 'currency' => 'XOF', 'weight' => 'KG_CM');
				$ups_core['TH'] = array('region' => 'AP', 'currency' => 'THB', 'weight' => 'KG_CM');
				$ups_core['TJ'] = array('region' => 'AP', 'currency' => 'TJS', 'weight' => 'KG_CM');
				$ups_core['TL'] = array('region' => 'AP', 'currency' => 'USD', 'weight' => 'KG_CM');
				$ups_core['TN'] = array('region' => 'AP', 'currency' => 'TND', 'weight' => 'KG_CM');
				$ups_core['TO'] = array('region' => 'AP', 'currency' => 'TOP', 'weight' => 'KG_CM');
				$ups_core['TR'] = array('region' => 'AP', 'currency' => 'TRY', 'weight' => 'KG_CM');
				$ups_core['TT'] = array('region' => 'AM', 'currency' => 'TTD', 'weight' => 'LB_IN');
				$ups_core['TV'] = array('region' => 'AP', 'currency' => 'AUD', 'weight' => 'KG_CM');
				$ups_core['TW'] = array('region' => 'AP', 'currency' => 'TWD', 'weight' => 'KG_CM');
				$ups_core['TZ'] = array('region' => 'AP', 'currency' => 'TZS', 'weight' => 'KG_CM');
				$ups_core['UA'] = array('region' => 'AP', 'currency' => 'UAH', 'weight' => 'KG_CM');
				$ups_core['UG'] = array('region' => 'AP', 'currency' => 'USD', 'weight' => 'KG_CM');
				$ups_core['US'] = array('region' => 'AM', 'currency' => 'USD', 'weight' => 'LB_IN');
				$ups_core['UY'] = array('region' => 'AM', 'currency' => 'UYU', 'weight' => 'KG_CM');
				$ups_core['UZ'] = array('region' => 'AP', 'currency' => 'UZS', 'weight' => 'KG_CM');
				$ups_core['VC'] = array('region' => 'AM', 'currency' => 'XCD', 'weight' => 'LB_IN');
				$ups_core['VE'] = array('region' => 'AM', 'currency' => 'VEF', 'weight' => 'KG_CM');
				$ups_core['VG'] = array('region' => 'AM', 'currency' => 'USD', 'weight' => 'LB_IN');
				$ups_core['VI'] = array('region' => 'AM', 'currency' => 'USD', 'weight' => 'LB_IN');
				$ups_core['VN'] = array('region' => 'AP', 'currency' => 'VND', 'weight' => 'KG_CM');
				$ups_core['VU'] = array('region' => 'AP', 'currency' => 'VUV', 'weight' => 'KG_CM');
				$ups_core['WS'] = array('region' => 'AP', 'currency' => 'WST', 'weight' => 'KG_CM');
				$ups_core['XB'] = array('region' => 'AM', 'currency' => 'EUR', 'weight' => 'LB_IN');
				$ups_core['XC'] = array('region' => 'AM', 'currency' => 'EUR', 'weight' => 'LB_IN');
				$ups_core['XE'] = array('region' => 'AM', 'currency' => 'ANG', 'weight' => 'LB_IN');
				$ups_core['XM'] = array('region' => 'AM', 'currency' => 'EUR', 'weight' => 'LB_IN');
				$ups_core['XN'] = array('region' => 'AM', 'currency' => 'XCD', 'weight' => 'LB_IN');
				$ups_core['XS'] = array('region' => 'AP', 'currency' => 'SIS', 'weight' => 'KG_CM');
				$ups_core['XY'] = array('region' => 'AM', 'currency' => 'ANG', 'weight' => 'LB_IN');
				$ups_core['YE'] = array('region' => 'AP', 'currency' => 'YER', 'weight' => 'KG_CM');
				$ups_core['YT'] = array('region' => 'AP', 'currency' => 'EUR', 'weight' => 'KG_CM');
				$ups_core['ZA'] = array('region' => 'AP', 'currency' => 'ZAR', 'weight' => 'KG_CM');
				$ups_core['ZM'] = array('region' => 'AP', 'currency' => 'ZMW', 'weight' => 'KG_CM');
				$ups_core['ZW'] = array('region' => 'AP', 'currency' => 'USD', 'weight' => 'KG_CM');

				_e('<hr><h3 class="heading">UPS - <a href="https://myshipi.com/" target="_blank">Shipi</a></h3>','hit_ups_auto');
				$soap_api_hide = $rest_api_hide = "";
				if (isset($main_settings['hit_ups_auto_api_type']) && $main_settings['hit_ups_auto_api_type'] == "REST") {
					$soap_api_hide = 'style="display:none;"';
				} else {
					$rest_api_hide = 'style="display:none;"';
				}
?>

				<table class="form-table">
					<tr <?php _e($soap_api_hide, 'hit_ups_auto') ?>>
						<td style=" width: 50%; padding: 5px; ">
							<h4> <span class="woocommerce-help-tip" data-tip="<?php _e('UPS Integration Team will give this details to you.', 'hit_ups_auto') ?>"></span> <?php _e('UPS XML API Site ID', 'hit_ups_auto') ?></h4>
							<p> <?php _e('Leave this field as empty to use default account.', 'hit_ups_auto') ?> </p>
						</td>
						<td>
							<input type="text" name="hit_ups_auto_site_id" value="<?php _e(isset($general_settings['hit_ups_auto_site_id']) ? $general_settings['hit_ups_auto_site_id'] : '','hit_ups_auto'); ?>">
						</td>

					</tr>
					<tr <?php _e($soap_api_hide, 'hit_ups_auto') ?>>
						<td style=" width: 50%; padding: 5px; ">
							<h4> <span class="woocommerce-help-tip" data-tip="<?php _e('UPS Integration Team will give this details to you.', 'hit_ups_auto') ?>"></span> <?php _e('UPS XML API Password', 'hit_ups_auto') ?></h4>
							<p> <?php _e('Leave this field as empty to use default account.', 'hit_ups_auto') ?> </p>
						</td>
						<td>
							<input type="text" name="hit_ups_auto_site_pwd" value="<?php _e(isset($general_settings['hit_ups_auto_site_pwd']) ? $general_settings['hit_ups_auto_site_pwd'] : '','hit_ups_auto'); ?>">
						</td>
					</tr>
					<tr <?php _e($soap_api_hide, 'hit_ups_auto') ?>>
						<td style=" width: 50%; padding: 5px; ">
							<h4> <span class="woocommerce-help-tip" data-tip="<?php _e('UPS Integration Team will give this details to you.', 'hit_ups_auto') ?>"></span> <?php _e('UPS Account Number', 'hit_ups_auto') ?></h4>
							<p> <?php _e('Leave this field as empty to use default account.', 'hit_ups_auto') ?> </p>
						</td>
						<td>

							<input type="text" name="hit_ups_auto_acc_no" value="<?php  isset($general_settings['hit_ups_auto_acc_no']) ? _e($general_settings['hit_ups_auto_acc_no'],'hit_ups_auto') : ''; ?>">
						</td>
					</tr>
					<tr <?php _e($soap_api_hide, 'hit_ups_auto') ?>>
						<td style=" width: 50%; padding: 5px; ">
							<h4> <span class="woocommerce-help-tip" data-tip="<?php _e('This is for proceed with return labels.', 'hit_ups_auto') ?>"></span> <?php _e('UPS Access Key', 'hit_ups_auto') ?></h4>
							<p> <?php _e('Leave this field as empty to use default account.', 'hit_ups_auto') ?> </p>
						</td>
						<td>

							<input type="text" name="hit_ups_auto_access_key" value="<?php isset($general_settings['hit_ups_auto_access_key']) ? _e($general_settings['hit_ups_auto_access_key'],'hit_ups_auto') : ''; ?>">
						</td>
					</tr>
					<tr <?php _e($rest_api_hide, 'hit_ups_auto') ?>>
						<td style=" width: 50%; padding: 5px; ">
							<h4> <span class="woocommerce-help-tip" data-tip="<?php _e('UPS Integration Team will give this details to you.', 'hit_ups_auto') ?>"></span> <?php _e('UPS OAuth API Client ID', 'hit_ups_auto') ?></h4>
							<p> <?php _e('Leave this field as empty to use default account.', 'hit_ups_auto') ?> </p>
						</td>
						<td>
							<input type="text" name="hit_ups_auto_rest_site_id" value="<?php _e(isset($general_settings['hit_ups_auto_rest_site_id']) ? $general_settings['hit_ups_auto_rest_site_id'] : '','hit_ups_auto'); ?>">
						</td>

					</tr>
					<tr <?php _e($rest_api_hide, 'hit_ups_auto') ?>>
						<td style=" width: 50%; padding: 5px; ">
							<h4> <span class="woocommerce-help-tip" data-tip="<?php _e('UPS Integration Team will give this details to you.', 'hit_ups_auto') ?>"></span> <?php _e('UPS OAuth API Client Password', 'hit_ups_auto') ?></h4>
							<p> <?php _e('Leave this field as empty to use default account.', 'hit_ups_auto') ?> </p>
						</td>
						<td>
							<input type="text" name="hit_ups_auto_rest_site_pwd" value="<?php _e(isset($general_settings['hit_ups_auto_rest_site_pwd']) ? $general_settings['hit_ups_auto_rest_site_pwd'] : '','hit_ups_auto'); ?>">
						</td>
					</tr>
					<tr <?php _e($rest_api_hide, 'hit_ups_auto') ?>>
						<td style=" width: 50%; padding: 5px; ">
							<h4> <span class="woocommerce-help-tip" data-tip="<?php _e('UPS Integration Team will give this details to you.', 'hit_ups_auto') ?>"></span> <?php _e('UPS Account Number', 'hit_ups_auto') ?></h4>
							<p> <?php _e('Leave this field as empty to use default account.', 'hit_ups_auto') ?> </p>
						</td>
						<td>

							<input type="text" name="hit_ups_auto_rest_acc_no" value="<?php  isset($general_settings['hit_ups_auto_rest_acc_no']) ? _e($general_settings['hit_ups_auto_rest_acc_no'],'hit_ups_auto') : ''; ?>">
						</td>
					</tr>
					<tr <?php _e($rest_api_hide, 'hit_ups_auto') ?>>
						<td style=" width: 50%; padding: 5px; ">
							<h4> <span class="woocommerce-help-tip" data-tip="<?php _e('This is for proceed with return labels.', 'hit_ups_auto') ?>"></span> <?php _e('Grant type', 'hit_ups_auto') ?></h4>
							<p> <?php _e('Leave this field as empty to use default account.', 'hit_ups_auto') ?> </p>
						</td>
						<td>
							<select name="hit_ups_auto_rest_grant_type" class="wc-enhanced-select" style="width:210px;">
							<?php
								if (isset($general_settings['hit_ups_auto_rest_grant_type']) && $general_settings['hit_ups_auto_rest_grant_type'] == "client_credentials") {
									_e('<option value="client_credentials" selected>Client Credentials</option>', 'hit_ups_auto');
								} else {
									_e('<option value="client_credentials">Client Credentials</option>', 'hit_ups_auto');
								}
							?>
						</td>
					</tr>
					<tr>
						<td style=" width: 50%; padding: 5px; ">
							<h4> <span class="woocommerce-help-tip" data-tip="<?php _e('Shipping Person Name', 'hit_ups_auto') ?>"></span> <?php _e('Shipper Name', 'hit_ups_auto') ?></h4>
						</td>
						<td>
							<input type="text" name="hit_ups_auto_shipper_name" value="<?php isset($general_settings['hit_ups_auto_shipper_name']) ? _e($general_settings['hit_ups_auto_shipper_name'],'hit_ups_auto') : ''; ?>">
						</td>
					</tr>
					<tr>
						<td style=" width: 50%; padding: 5px; ">
							<h4> <span class="woocommerce-help-tip" data-tip="<?php _e('Shipper Company Name.', 'hit_ups_auto') ?>"></span> <?php _e('Company Name', 'hit_ups_auto') ?></h4>
						</td>
						<td>
							<input type="text" name="hit_ups_auto_company" value="<?php  isset($general_settings['hit_ups_auto_company']) ? _e($general_settings['hit_ups_auto_company'],'hit_ups_auto') : ''; ?>">
						</td>
					</tr>
					<tr>
						<td style=" width: 50%; padding: 5px; ">
							<h4> <span class="woocommerce-help-tip" data-tip="<?php _e('Shipper Mobile / Contact Number.', 'hit_ups_auto') ?>"></span> <?php _e('Contact Number', 'hit_ups_auto') ?></h4>
						</td>
						<td>
							<input type="text" name="hit_ups_auto_mob_num" value="<?php isset($general_settings['hit_ups_auto_mob_num']) ? _e($general_settings['hit_ups_auto_mob_num'],'hit_ups_auto') : ''; ?>">
						</td>
					</tr>
					<tr>
						<td style=" width: 50%; padding: 5px; ">
							<h4> <span class="woocommerce-help-tip" data-tip="<?php _e('Email Address of the Shipper.', 'hit_ups_auto') ?>"></span> <?php _e('Email Address', 'hit_ups_auto') ?></h4>
						</td>
						<td>
							<input type="text" name="hit_ups_auto_email" value="<?php isset($general_settings['hit_ups_auto_email']) ? _e($general_settings['hit_ups_auto_email'],'hit_ups_auto') : ''; ?>">
						</td>
					</tr>
					<tr>
						<td style=" width: 50%; padding: 5px; ">
							<h4> <span class="woocommerce-help-tip" data-tip="<?php _e('Address Line 1 of the Shipper from Address.', 'hit_ups_auto') ?>"></span> <?php _e('Address Line 1', 'hit_ups_auto') ?></h4>
						</td>
						<td>
							<input type="text" name="hit_ups_auto_address1" value="<?php isset($general_settings['hit_ups_auto_address1']) ? _e($general_settings['hit_ups_auto_address1'],'hit_ups_auto') : ''; ?>">
						</td>
					</tr>
					<tr>
						<td style=" width: 50%; padding: 5px; ">
							<h4> <span class="woocommerce-help-tip" data-tip="<?php _e('Address Line 2 of the Shipper from Address.', 'hit_ups_auto') ?>"></span> <?php _e('Address Line 2', 'hit_ups_auto') ?></h4>
						</td>
						<td>
							<input type="text" name="hit_ups_auto_address2" value="<?php isset($general_settings['hit_ups_auto_address2']) ? _e($general_settings['hit_ups_auto_address2'],'hit_ups_auto') : ''; ?>">
						</td>
					</tr>
					<tr>
						<td style=" width: 50%;padding: 5px; ">
							<h4> <span class="woocommerce-help-tip" data-tip="<?php _e('City of the Shipper from address.', 'hit_ups_auto') ?>"></span> <?php _e('City', 'hit_ups_auto') ?></h4>
						</td>
						<td>
							<input type="text" name="hit_ups_auto_city" value="<?php isset($general_settings['hit_ups_auto_city']) ? _e($general_settings['hit_ups_auto_city'],'hit_ups_auto') : ''; ?>">
						</td>
					</tr>
					<tr>
						<td style=" width: 50%; padding: 5px; ">
							<h4> <span class="woocommerce-help-tip" data-tip="<?php _e('State of the Shipper from address.', 'hit_ups_auto') ?>"></span> <?php _e('State (Two Digit String)', 'hit_ups_auto') ?></h4>
						</td>
						<td>
							<input type="text" name="hit_ups_auto_state" value="<?php isset($general_settings['hit_ups_auto_state']) ? _e($general_settings['hit_ups_auto_state'],'hit_ups_auto') : ''; ?>">
						</td>
					</tr>
					<tr>
						<td style=" width: 50%; padding: 5px; ">
							<h4> <span class="woocommerce-help-tip" data-tip="<?php _e('Postal/Zip Code.', 'hit_ups_auto') ?>"></span> <?php _e('Postal/Zip Code', 'hit_ups_auto') ?></h4>
						</td>
						<td>
							<input type="text" name="hit_ups_auto_zip" value="<?php isset($general_settings['hit_ups_auto_zip']) ? _e($general_settings['hit_ups_auto_zip'],'hit_ups_auto') : ''; ?>">
						</td>
					</tr>
					<tr>
						<td style=" width: 50%; padding: 5px; ">
							<h4> <span class="woocommerce-help-tip" data-tip="<?php _e('Country of the Shipper from Address.', 'hit_ups_auto') ?>"></span> <?php _e('Country', 'hit_ups_auto') ?></h4>
						</td>
						<td>
							<select name="hit_ups_auto_country" class="wc-enhanced-select" style="width:210px;">
								<?php foreach ($countires as $key => $value) {

									if (isset($general_settings['hit_ups_auto_country']) && ($general_settings['hit_ups_auto_country'] == $key)) {
										 _e("<option value=" . $key . " selected='true'>" . $value . " [" . $ups_core[$key]['currency'] . "]</option>",'hit_ups_auto');
									} else {
										_e("<option value=" . $key . ">" . $value . " [" . $ups_core[$key]['currency'] . "]</option>",'hit_ups_auto');
									}
								} ?>
							</select>
						</td>
					</tr>
					<tr>
						<td style=" width: 50%; ">
							<h4> <span class="woocommerce-help-tip" data-tip="<?php _e('GSTIN/VAT No.', 'hit_ups_auto') ?>"></span> <?php _e('GSTIN/VAT No', 'hit_ups_auto') ?></h4>
						</td>
						<td>
							<input type="text" name="hit_ups_auto_gstin" value="<?php isset($general_settings['hit_ups_auto_gstin']) ? _e($general_settings['hit_ups_auto_gstin'],'hit_ups_auto') : ''; ?>">
						</td>
					</tr>
					<tr>
						<td style=" width: 50%; ">
							<h4> <span class="woocommerce-help-tip" data-tip="<?php _e('Conversion Rate from Site Currency to UPS Currency.', 'hit_ups_auto') ?>"></span> <?php _e('Conversion Rate from Site Currency to UPS Currency', 'hit_ups_auto') ?></h4>
						</td>
						<td>
							<input type="text" name="hit_ups_auto_con_rate" value="<?php isset($general_settings['hit_ups_auto_con_rate']) ? _e($general_settings['hit_ups_auto_con_rate'],'hit_ups_auto') : ''; ?>">
						</td>
					</tr>
					<tr>
						<td style=" width: 50%; padding: 5px; ">
							<h4><span class="woocommerce-help-tip" data-tip="<?php _e('Vendor Collector type', 'hit_ups_auto') ?>"></span><?php _e('Vendor Collector type', 'hit_ups_auto') ?></h4>
						</td>
						<td>
							<select name="hit_ups_auto_ven_col_type" class="wc-enhanced-select" style="width:210px;">
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
						<td style=" width: 50%; ">
							<h4><span class="woocommerce-help-tip" data-tip="<?php _e('Vendor Collector ID', 'hit_ups_auto') ?>"></span><?php _e('Vendor Collector ID', 'hit_ups_auto') ?></h4>
						</td>
						<td>
							<input type="text" name="hit_ups_auto_ven_col_id" value="<?php isset($general_settings['hit_ups_auto_ven_col_id']) ? _e($general_settings['hit_ups_auto_ven_col_id'],'hit_ups_auto') : ''; ?>">
						</td>
					</tr>
					<tr>
						<td style=" width: 50%; padding: 5px; ">
							<h4> <span class="woocommerce-help-tip" data-tip="<?php _e('Default Domestic Express Shipping.', 'hit_ups_auto') ?>"></span> <?php _e('Default Domestic Service', 'hit_ups_auto') ?></h4>
							<p><?php _e('This will be used while shipping label generation.', 'hit_ups_auto') ?></p>
						</td>
						<td>
							<select name="hit_ups_auto_def_dom" class="wc-enhanced-select" style="width:210px;">
								<?php foreach ($_ups_carriers as $key => $value) {
									if (isset($general_settings['hit_ups_auto_def_dom']) && ($general_settings['hit_ups_auto_def_dom'] == $key)) {
										_e("<option value=" . $key . " selected='true'>[" . $key . "] " . $value . "</option>",'hit_ups_auto');
									} else {
										_e("<option value=" . $key . ">[" . $key . "] " . $value . "</option>",'hit_ups_auto');
									}
								} ?>
							</select>
						</td>
					</tr>
					<tr>
						<td style=" width: 50%; padding: 5px; ">
							<h4> <span class="woocommerce-help-tip" data-tip="<?php _e('Default International Shipping.', 'hit_ups_auto') ?>"></span> <?php _e('Default International Service', 'hit_ups_auto') ?></h4>
							<p><?php _e('This will be used while shipping label generation.', 'hit_ups_auto') ?></p>
						</td>
						<td>
							<select name="hit_ups_auto_def_inter" class="wc-enhanced-select" style="width:210px;">
								<?php foreach ($_ups_carriers as $key => $value) {
									if (isset($general_settings['hit_ups_auto_def_inter']) && ($general_settings['hit_ups_auto_def_inter'] == $key)) {
										_e("<option value=" . $key . " selected='true'>[" . $key . "] " . $value . "</option>",'hit_ups_auto');
									} else {
										_e("<option value=" . $key . ">[" . $key . "] " . $value . "</option>",'hit_ups_auto');
									}
								} ?>
							</select>
						</td>
					</tr>
				</table>
				<hr>
			<?php
			}
			public function hit_save_product_meta($post_id)
			{
				if (isset($_POST['hit_ups_auto_shipment'])) {
					$hit_ups_auto_shipment = isset($_POST['hit_ups_auto_shipment']) ? sanitize_text_field($_POST['hit_ups_auto_shipment']) : '';
					if (!empty($hit_ups_auto_shipment)){
						if ($this->hpos_enabled && $this->new_prod_editor_enabled) {
	 	                    $hpos_prod_data = wc_get_product($post_id);
	 	                    $hpos_prod_data->update_meta_data("hit_ups_auto_address", (string) esc_html( $hit_ups_auto_shipment ));
	 	                } else {
	 	                	update_post_meta($post_id, 'hit_ups_auto_address', (string) esc_html($hit_ups_auto_shipment));
	 	                }
					}
				}
			}
			public function hit_choose_vendor_address()
			{
				global $woocommerce, $post;
				$hit_multi_vendor = get_option('hit_multi_vendor');
				$hit_multi_vendor = empty($hit_multi_vendor) ? array() : $hit_multi_vendor;
				if ($this->hpos_enabled) {
				    $hpos_prod_data = wc_get_product($post->ID);
				    $selected_addr = $hpos_prod_data->get_meta("hit_ups_auto_address");
				} else {
					$selected_addr = get_post_meta($post->ID, 'hit_ups_auto_address', true);
				}

				$main_settings = get_option('hit_ups_auto_main_settings');
				$main_settings = empty($main_settings) ? array() : $main_settings;
				if (!isset($main_settings['hit_ups_auto_v_roles']) || empty($main_settings['hit_ups_auto_v_roles'])) {
					return;
				}
				$v_users = get_users(['role__in' => $main_settings['hit_ups_auto_v_roles']]);
			?>
				<div id='ups_account' class='panel woocommerce_options_panel'>
					<div class="options_group">
						<p class="form-field hit_ups_auto_shipment">
							<label for="hit_ups_auto_shipment"><?php _e('UPS Express Account', 'woocommerce'); ?></label>
							<select id="hit_ups_auto_shipment" style="width:240px;" name="hit_ups_auto_shipment" class="wc-enhanced-select" data-placeholder="<?php _e('Search for a product&hellip;', 'woocommerce'); ?>">
								<option value="default">Default Account</option>
								<?php
								if ($v_users) {
									foreach ($v_users as $value) {
										_e('<option value="' .  $value->data->ID  . '" ' . ($selected_addr == $value->data->ID ? 'selected="true"' : '') . '>' . $value->data->display_name . '</option>','hit_ups_auto');
									}
								}
								?>
							</select>

						</p>
					</div>
				</div>
<?php
			}
			public function hit_ups_init()
			{
				include_once("controllors/hit_ups_auto_init.php");
			}
			public function hit_order_status_update()
			{
				global $woocommerce;

				if (isset($_GET['shipi_key'])) {
					$shipi_key = sanitize_text_field($_GET['shipi_key']);
					if ($shipi_key == 'fetch' && get_transient('hitshipo_ups_nonce_temp')) {
						_e(json_encode(array(get_transient('hitshipo_ups_nonce_temp'))),'hit_ups_auto');
						die();
					}
				}

				if (isset($_GET['hitshipo_integration_key']) && isset($_GET['hitshipo_action'])) {
					$integration_key = sanitize_text_field($_GET['integration_key']);
					$hitshipo_action = sanitize_text_field($_GET['hitshipo_action']);
					$general_settings = get_option('hit_ups_auto_main_settings');
					$general_settings = empty($general_settings) ? array() : $general_settings;
					if (isset($general_settings['hit_ups_auto_integration_key']) && $integration_key == $general_settings['hit_ups_auto_integration_key']) {
						if ($hitshipo_action == 'stop_working') {
							update_option('hitshipo_ups_working_status', 'stop_working');
						} else if ($hitshipo_action = 'start_working') {
							update_option('hitshipo_ups_working_status', 'start_working');
						}
					}
				}


				if (isset($_GET['h1t_updat3_0rd3r']) && isset($_GET['key']) && isset($_GET['action'])) {
					$order_id = sanitize_text_field($_GET['h1t_updat3_0rd3r']);
					$key = sanitize_text_field($_GET['key']);
					$action = sanitize_text_field($_GET['action']);
					$order_ids = explode(",", $order_id);
					$general_settings = get_option('hit_ups_auto_main_settings', array());


					if (isset($general_settings['hit_ups_auto_integration_key']) && $general_settings['hit_ups_auto_integration_key'] == $key) {
						if ($action == 'processing') {
							foreach ($order_ids as $order_id) {
								$order = wc_get_order($order_id);
								$order->update_status('processing');
							}
						} else if ($action == 'completed') {
							foreach ($order_ids as $order_id) {
								$order = wc_get_order($order_id);
								$order->update_status('completed');
							}
						}
					}
					die();
				}

				if (isset($_GET['h1t_updat3_sh1pp1ng']) && isset($_GET['key']) && isset($_GET['user_id']) && isset($_GET['carrier']) && isset($_GET['track']) && isset($_GET['service'])) {

					$order_id = sanitize_text_field($_GET['h1t_updat3_sh1pp1ng']);
					$order = wc_get_order($order_id);
					$key = sanitize_text_field($_GET['key']);
					$general_settings = get_option('hit_ups_auto_main_settings', array());
					$user_id = sanitize_text_field($_GET['user_id']);
					$carrier = sanitize_text_field($_GET['carrier']);
					$track = sanitize_text_field($_GET['track']);
					$service = sanitize_text_field($_GET['service']);
					$label_count = 0;

					if (isset($_GET['label_count'])) {
						$label_count = sanitize_text_field($_GET['label_count']);
					}

					$output['selected_service'] = $service;
					$output['status'] = 'success';
					$output['tracking_num'] = $track;
					$labels = '';
					// Save multiple laels in a database
					if ($label_count > 0) {
						for ($i = 1; $i < $label_count; $i++) {
							$labels .= "https://app.myshipi.com/api/shipping_labels/" . $user_id . "/" . $carrier . "/order_" . $order_id . "_track_" . $track . "_label_" . $i . ".gif,";
						}
					} else {
						$labels = "https://app.myshipi.com/api/shipping_labels/" . $user_id . "/" . $carrier . "/order_" . $order_id . "_track_" . $track . "_label.gif";
					}


					$output['label'] = rtrim($labels, ',');
					$output['invoice'] = "https://app.myshipi.com/api/shipping_labels/" . $user_id . "/" . $carrier . "/order_" . $order_id . "_track_" . $track . "_invoice.pdf";
					$result_arr = array();
					$today = date("F j, Y, g:i a");
					$to = $order->get_billing_email();

					if (isset($general_settings['hit_ups_auto_integration_key']) && $general_settings['hit_ups_auto_integration_key'] == $key) {

						if (isset($_GET['label'])) {
							$output['user_id'] = sanitize_text_field($_GET['label']);
							$result_arr = !empty(get_option('hit_ups_auto_values_' . $order_id, array())) ? json_decode(get_option('hit_ups_auto_values_' . $order_id)) : []; // json_decode(get_option('hit_ups_auto_values_'.$order_id, array()));
							$result_arr[] = $output;
						} else {
							$result_arr[] = $output;
						}
						update_option('hit_ups_auto_values_' . $order_id, json_encode($result_arr));
					}
					die();
				}
			}
			public function hit_ups_method($methods)
			{
				$methods['hit_ups_auto'] = 'hit_ups_auto';
				return $methods;
			}

			public function hit_ups_plugin_action_links($links)
			{
				$setting_value = version_compare(WC()->version, '2.1', '>=') ? "wc-settings" : "woocommerce_settings";
				$plugin_links = array(
					'<a href="' . admin_url('admin.php?page=' . $setting_value  . '&tab=shipping&section=hit_ups_auto') . '" style="color:green;">' . __('Configure', 'hit_ups_auto') . '</a>',
					'<a href="#" target="_blank" >' . __('Support', 'hit_ups_auto') . '</a>'
				);
				return array_merge($plugin_links, $links);
			}
			public function create_ups_shipping_meta_box()
			{
				$meta_scrn = $this->hpos_enabled ? wc_get_page_screen_id( 'shop-order' ) : 'shop_order';
				add_meta_box('hit_create_ups_shipping', __('Automated UPS Shipping Label', 'hit_ups_auto'), array($this, 'create_ups_shipping_label_genetation'), $meta_scrn, 'side', 'core');
				add_meta_box('create_ups_return_label', __('UPS Return Label', 'hit_ups_auto'), array($this, 'create_ups_return_label_genetation'), $meta_scrn, 'side', 'core');
			}
			public function create_ups_shipping_label_genetation($post)
			{
				if(!$this->hpos_enabled && $post->post_type !='shop_order' ){
		    		return;
		    	}
				$order = (!$this->hpos_enabled) ? wc_get_order( $post->ID ) : $post;
				$order_id = $order->get_id();

				$_ups_carriers = array(
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

				$_packing_types_ser = array(
					'02' => 'Customer Supplied Package',
					'01' => 'UPS Letter',
					'03' => 'Tube',
					'04' => 'PAK',
					'21' => 'UPS Express Box',
					'24' => 'UPS 25KG Box',
					'25' => 'UPS 10KG Box',
					'30' => 'Pallet',
					'2a' => 'Small Express Box',
					'2b' => 'Medium Express Box',
					'2c' => 'Large Express Box',
					'56' => 'Flats 57 = Parcels',
					'58' => 'BPM 59 = First Class',
					'60' => 'Priority',
					'61' => 'Machinables',
					'62' => 'Irregulars',
					'63' => 'Parcel Post',
					'64' => 'BPM Parcel',
					'65' => 'Media Mail',
					'66' => 'BPM Flat',
					'67' => 'Standard Flat',
				);

				$general_settings = get_option('hit_ups_auto_main_settings', array());
				$items = $order->get_items();

				$custom_settings = array();
				$custom_settings['default'] =  array();
				$vendor_settings = array();

				$pack_products = array();

				foreach ($items as $item) {
					$product_data = $item->get_data();
					$product = array();
					$product['product_name'] = $product_data['name'];
					$product['product_quantity'] = $product_data['quantity'];
					$product['product_id'] = $product_data['product_id'];

					$pack_products[] = $product;
				}

				if (isset($general_settings['hit_ups_auto_v_enable']) && $general_settings['hit_ups_auto_v_enable'] == 'yes' && isset($general_settings['hit_ups_auto_v_labels']) && $general_settings['hit_ups_auto_v_labels'] == 'yes') {
					// Multi Vendor Enabled
					foreach ($pack_products as $key => $value) {

						$product_id = $value['product_id'];
						if ($this->hpos_enabled) {
						    $hpos_prod_data = wc_get_product($product_id);
						    $ups_account = $hpos_prod_data->get_meta("hit_ups_auto_address");
						} else {
							$ups_account = get_post_meta($product_id, 'hit_ups_auto_address', true);
						}
						if (empty($ups_account) || $ups_account == "default") {
							$ups_account = 'default';
							$vendor_settings[$ups_account] = $custom_settings['default'];
							$vendor_settings[$ups_account]['products'][] = $value;
						}

						if ($ups_account != 'default') {
							$user_account = get_post_meta($ups_account, 'hit_ups_auto_vendor_settings', true);
							$user_account = empty($user_account) ? array() : $user_account;
							if (!empty($user_account)) {
								if (!isset($vendor_settings[$ups_account])) {

									$vendor_settings[$ups_account] = $custom_settings['default'];
									unset($value['product_id']);
									$vendor_settings[$ups_account]['products'][] = $value;
								}
							} else {
								$ups_account = 'default';
								$vendor_settings[$ups_account] = $custom_settings['default'];
								$vendor_settings[$ups_account]['products'][] = $value;
							}
						}
					}
				}

				if (empty($vendor_settings)) {
					$custom_settings['default']['products'] = $pack_products;
				} else {
					$custom_settings = $vendor_settings;
				}

				$json_data = get_option('hit_ups_auto_values_' . $order_id);
				$notice = get_option('hit_ups_auto_status_' . $order_id, null);
				if ($notice && $notice != 'success') {
					_e("<p style='color:red'>" . $notice . "</p>",'hit_ups_auto');
					delete_option('hit_ups_auto_status_' . $order_id);
				}
				if (!empty($json_data)) {
					$array_data = json_decode($json_data, true);
					if (isset($array_data[0])) {
						foreach ($array_data as $key => $value) {
							if (isset($value['user_id'])) {
								unset($custom_settings[$value['user_id']]);
							}
							if (isset($value['user_id']) && $value['user_id'] == 'default') {
								_e('<br/><b>Default Account</b><br/>','hit_ups_auto');
							} else {
								$user = get_user_by('id', $value['user_id']);
								_e('<br/><b>Account:</b> <small>' . $user->display_name . '</small><br/>','hit_ups_auto');
							}
							if (isset($value['tracking_num'])) {
								echo '<b>Tracking No: </b><a href="https://tracking.hitshipo.com?id='.$order_id.'&no='.$value["tracking_num"].'&track=1" target="_blank">#'.$value["tracking_num"].'</a><br/>';
							}
							$labels = explode(",", $value['label']);

							foreach ($labels as $key => $label) {
								_e('<a href="' . $label . '" target="_blank" style="background:#300; color: #FFD100;border-color: #300;box-shadow: 0px 1px 0px #FFCC00;text-shadow: 0px 1px 0px #FFD100;margin-top: 5px;" class="button button-primary"> Shipping Label' . ($key + 1) . ' </a> ','hit_ups_auto');
							}
							_e(' <a href="' . $value['invoice'] . '" target="_blank" class="button button-primary" style="margin-top: 5px;"> Invoice </a><br/>','hit_ups_auto');
						}
					} else {
						$labels = explode(",", $array_data['label']);
						foreach ($labels as $key => $label) {
							_e('<a href="' . $label . '" target="_blank" style="background:#300; color: #FFD100;border-color: #300;box-shadow: 0px 1px 0px #FFCC00;text-shadow: 0px 1px 0px #FFD100;margin-top: 5px;" class="button button-primary"> Shipping Label' . ($key + 1) . ' </a> ','hit_ups_auto');
						}
						$custom_settings = array();
						_e(' <a href="' . $array_data['invoice'] . '" target="_blank" class="button button-primary" style="margin-top: 5px;"> Invoice </a>','hit_ups_auto');
					}
					_e(' <button name="hit_ups_auto_reset" style="margin-top: 5px" class="button button-secondary"> Reset All </button>','hit_ups_auto');
				}
				foreach ($custom_settings as $ukey => $value) {
					$order = wc_get_order($order_id);
					$service_code = $multi_ven = '';
					foreach ($order->get_shipping_methods() as $item_id => $item) {
						$service_nme = $item->get_meta('hit_ups_auto_service');
						// $service_code = str_replace("ups_", "", $service_nme);
						$multi_ven = $item->get_meta('a2z_multi_ven');
					}
					if ($ukey == 'default') {
						 _e('<br/><b>Default Account</b>','hit_ups_auto');
						_e('<br/><select name="hit_ups_auto_service_code_default">','hit_ups_auto');						
						if (!empty($general_settings['hit_ups_auto_carrier'])) {
							foreach ($general_settings['hit_ups_auto_carrier'] as $key => $value) {
								if ($service_nme != $key ) {
									_e("<option value='" . $key . "' >" . $key . ' - ' . $_ups_carriers[$key] . "</option>",'hit_ups_auto');
								}else {
									_e("<option value='" . $key . "'selected>" . $key . ' - ' . $_ups_carriers[$key] . "</option>",'hit_ups_auto');
								}
							}
						}
						_e('</select>','hit_ups_auto');

						_e('<br/><b>Pack Type</b>','hit_ups_auto');
						_e('<br/><select name="hit_ups_auto_pack_type">','hit_ups_auto');

						foreach ($_packing_types_ser as $key => $value) {
							_e("<option value='" . $key . "'>" . $key . ' - ' . $_packing_types_ser[$key] . "</option>",'hit_ups_auto');
						}

						_e('</select>','hit_ups_auto');

						_e('<br/><b>Shipment Content</b>','hit_ups_auto');

						_e('<br/><input type="text" style="width:250px;margin-bottom:10px;"  name="hit_ups_auto_shipment_content_default" placeholder="Shipment content" value="' . (isset($general_settings['hit_ups_auto_ship_content']) ? $general_settings['hit_ups_auto_ship_content'] : "") . '" >','hit_ups_auto');
						_e('<button name="hit_ups_auto_create_label" value="default" style="background:#300; color: #FFD100;border-color: #300;box-shadow: 0px 1px 0px #300;text-shadow: 0px 1px 0px #FFD100;" class="button button-primary">Create Shipment</button>','hit_ups_auto');
					
					} else {
						$order = wc_get_order($order_id);
						$service_code = $multi_ven = '';
						foreach ($order->get_shipping_methods() as $item_id => $item) {
							$service_nme = $item->get_meta('hit_ups_auto_service');
							// $service_code = str_replace("ups_", "", $service_nme);
							$multi_ven = $item->get_meta('a2z_multi_ven');
						}
						$user = get_user_by('id', $ukey);
						_e('<br/><b>Account:</b> <small>' . $user->display_name . '</small>','hit_ups_auto');
						_e('<br/><select name="hit_ups_auto_service_code_' . $ukey . '">','hit_ups_auto');
						if (!empty($general_settings['hit_ups_auto_carrier'])) {
							foreach ($general_settings['hit_ups_auto_carrier'] as $key => $value) {
								if ($service_nme != $key ) {
									_e("<option value='" . $key . "' >" . $key . ' - ' . $_ups_carriers[$key] . "</option>",'hit_ups_auto');
								}else {
									_e("<option value='" . $key . "'selected>" . $key . ' - ' . $_ups_carriers[$key] . "</option>",'hit_ups_auto');
								}
							}
						}
						_e('</select>','hit_ups_auto');

						_e('<br/><b>Pack Type</b>','hit_ups_auto');
						_e('<br/><select name="hit_ups_auto_pack_type">','hit_ups_auto');

						foreach ($_packing_types_ser as $key => $value) {
							_e("<option value='" . $key . "'>" . $key . ' - ' . $_packing_types_ser[$key] . "</option>",'hit_ups_auto');
						}

						_e('</select>','hit_ups_auto');

						_e('<br/><b>Shipment Content</b>','hit_ups_auto');

						_e('<br/><input type="text" style="width:250px;margin-bottom:10px;"  name="hit_ups_auto_shipment_content_' . $ukey . '" placeholder="Shipment content" value="' . (($general_settings['hit_ups_auto_ship_content']) ? $general_settings['hit_ups_auto_ship_content'] : "") . '" >','hit_ups_auto');

						_e('<button name="hit_ups_auto_create_label" value="' . $ukey . '" style="background:#300; color: #FFD100;border-color: #300;box-shadow: 0px 1px 0px #300;text-shadow: 0px 1px 0px #FFD100;" class="button button-primary">Create Shipment</button><br/>','hit_ups_auto');
					}
				}

				//...................
			}

			public function create_ups_return_label_genetation($post)
			{
				if(!$this->hpos_enabled && $post->post_type !='shop_order' ){
		    		return;
		    	}
				$order = (!$this->hpos_enabled) ? wc_get_order( $post->ID ) : $post;
				$order_id = $order->get_id();

				$_ups_carriers = array(
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
					"ups_83"					=> "UPS Today Dedicated Courier",
					"ups_84"					=> "UPS Today Intercity",
					"ups_85"					=> "UPS Today Express",
					"ups_86" 					=> "UPS Today Express Saver",
					'ups_M2'                    => 'First Class Mail',
					'ups_M3'                    => 'Priority Mail',
					'ups_M4'                    => 'Expedited Mail Innovations',
					'ups_M5'                    => 'Priority Mail Innovations',
					'ups_M6'                    => 'EconomyMail Innovations',
					'ups_70'                    => 'Access Point Economy',
					'ups_96'                    => 'Worldwide Express Freight'
				);
				$general_settings = get_option('hit_ups_auto_main_settings', array());

				$json_data = get_option('hit_ups_auto_return_values_' . $order_id);

				if (empty($json_data)) {

					_e('<b>Choose Service to Return</b>','hit_ups_auto');
					_e('<br/><select name="hit_ups_auto_return_service_code_default">','hit_ups_auto');
					if (!empty($general_settings['hit_ups_auto_carrier'])) {
						foreach ($general_settings['hit_ups_auto_carrier'] as $key => $value) {
							_e("<option value='" . $key . "'>" . $key . ' - ' . $_ups_carriers[$key] . "</option>",'hit_ups_auto');
						}
					}
					_e('</select>','hit_ups_auto');


					_e('<br/><b>Products to return</b>','hit_ups_auto');
					_e('<br/>','hit_ups_auto');
					_e('<table>','hit_ups_auto');
					$items = $order->get_items();
					foreach ($items as $item) {
						$product_data = $item->get_data();

						$product_variation_id = $item->get_variation_id();
						$product_id = $product_data['product_id'];
						if (!empty($product_variation_id) && $product_variation_id != 0) {
							$product_id = $product_variation_id;
						}

						_e("<tr><td><input type='checkbox' name='return_products_ups[]' checked value='" . $product_id . "'>
					    	</td>",'hit_ups_auto');
						_e("<td style='width:150px;'><small title='" . $product_data['name'] . "'>" . substr($product_data['name'], 0, 7) . "</small></td>",'hit_ups_auto');
						_e("<td><input type='number' name='qty_products_ups[" . $product_id . "]' style='width:50px;' value='" . $product_data['quantity'] . "'></td>",'hit_ups_auto');
						_e("</tr>",'hit_ups_auto');
					}
					_e('</table><br/>','hit_ups_auto');

					$notice = get_option('hit_ups_auto_return_status_' . $order_id, null);
					if ($notice && $notice != 'success') {
						_e("<p style='color:red'>" . $notice . "</p>",'hit_ups_auto');
						delete_option('hit_ups_auto_return_status_' . $order_id);
					}

					_e('<button name="hit_ups_auto_create_return_label" value="default" style="background:#300; color: #FFD100;border-color: #300;box-shadow: 0px 1px 0px #300;text-shadow: 0px 1px 0px #FFD100;" class="button button-primary">Create Return Shipment</button>','hit_ups_auto');
				} else {
					$array_data = json_decode($json_data, true);

					$labels = explode(',', $array_data[0]['label']);
					foreach ($labels as $count => $label) {
						_e('<a href="' . $label . '" target="_blank" style="background:#300; color: #FFD100;border-color: #300;box-shadow: 0px 1px 0px #300;text-shadow: 0px 1px 0px #FFD100; margin-top:2px" class="button button-primary"> Return Label ' . ($count + 1) . ' </a> ','hit_ups_auto');
					}

					_e('</br><a href="' . $array_data[0]['invoice'] . '" target="_blank" class="button button-primary" style="margin-top: 2px"> Invoice </a></br>','hit_ups_auto');
					_e('<button name="hit_ups_auto_return_reset" class="button button-secondary" style="margin-top:3px;"> Reset</button>','hit_ups_auto');
				}
			}
			public function hit_wc_checkout_order_processed($order_id)
			{
				
				$_ups_carriers = array(
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
					"ups_83"					=> "UPS Today Dedicated Courier",
					"ups_84"					=> "UPS Today Intercity",
					"ups_85"					=> "UPS Today Express",
					"ups_86" 					=> "UPS Today Express Saver",
					'ups_M2'                    => 'First Class Mail',
					'ups_M3'                    => 'Priority Mail',
					'ups_M4'                    => 'Expedited Mail Innovations',
					'ups_M5'                    => 'Priority Mail Innovations',
					'ups_M6'                    => 'EconomyMail Innovations',
					'ups_70'                    => 'Access Point Economy',
					'ups_96'                    => 'Worldwide Express Freight'
				);
				if ($this->hpos_enabled) {
	 		        if ('shop_order' !== Automattic\WooCommerce\Utilities\OrderUtil::get_order_type($order_id)) {
	 		            return;
	 		        }
	 		    } else {
	 		    	$post = get_post($order_id);
					if ($post->post_type != 'shop_order') {
						return;
					}
	 		    }

				$ship_content = !empty($_POST['hit_ups_auto_shipment_content']) ? sanitize_text_field($_POST['hit_ups_auto_shipment_content']) : 'Shipment Content';
				$order = wc_get_order($order_id);
				$service_code = $multi_ven = '';
				foreach ($order->get_shipping_methods() as $item_id => $item) {
					$service_nme = $item->get_meta('hit_ups_auto_service');
					$service_code = str_replace("ups_", "", $service_nme);
					$multi_ven = $item->get_meta('a2z_multi_ven');
				}

				$order_data = $order->get_data();
				$order_id = $order_data['id'];
				$order_currency = $order_data['currency'];

				// $order_shipping_first_name = $order_data['shipping']['first_name'];
				// $order_shipping_last_name = $order_data['shipping']['last_name'];
				// $order_shipping_company = empty($order_data['shipping']['company']) ? $order_data['shipping']['first_name'] :  $order_data['shipping']['company'];
				// $order_shipping_address_1 = $order_data['shipping']['address_1'];
				// $order_shipping_address_2 = $order_data['shipping']['address_2'];
				// $order_shipping_city = $order_data['shipping']['city'];
				// $order_shipping_state = $order_data['shipping']['state'];
				// $order_shipping_postcode = $order_data['shipping']['postcode'];
				// $order_shipping_country = $order_data['shipping']['country'];
				// $order_shipping_phone = $order_data['billing']['phone'];
				// $order_shipping_email = $order_data['billing']['email'];

				$shipping_arr = (isset($order_data['shipping']['first_name']) && $order_data['shipping']['first_name'] != "") ? $order_data['shipping'] : $order_data['billing'];
                $order_shipping_first_name = $shipping_arr['first_name'];
                $order_shipping_last_name = $shipping_arr['last_name'];
                $order_shipping_company = empty($shipping_arr['company']) ? $shipping_arr['first_name'] :  $shipping_arr['company'];
                $order_shipping_address_1 = $shipping_arr['address_1'];
                $order_shipping_address_2 = $shipping_arr['address_2'];
                $order_shipping_city = $shipping_arr['city'];
                $order_shipping_state = $shipping_arr['state'];
                $order_shipping_postcode = $shipping_arr['postcode'];
                $order_shipping_country = $shipping_arr['country'];
                $order_shipping_phone = $order_data['billing']['phone'];
                $order_shipping_email = $order_data['billing']['email'];

				$items = $order->get_items();
				$pack_products = array();
				$total_weg = 0;

				//weight conversion wc_get_weight( $weight, $to_unit, $from_unit )
				$general_settings = get_option('hit_ups_auto_main_settings', array());
				if (!isset($general_settings['hit_ups_auto_label_automation']) || (isset($general_settings['hit_ups_auto_label_automation']) && $general_settings['hit_ups_auto_label_automation'] != "yes")) {
					return false;
				}
				$woo_weg_unit = get_option('woocommerce_weight_unit');
				$woo_dim_unit = get_option('woocommerce_dimension_unit');
				$config_weg_unit = $general_settings['hit_ups_auto_weight_unit'];
				$custom_settings = array();
				$custom_settings['default'] = array(
					'hit_ups_auto_site_id' => $general_settings['hit_ups_auto_site_id'],
					'hit_ups_auto_site_pwd' => $general_settings['hit_ups_auto_site_pwd'],
					'hit_ups_auto_acc_no' => $general_settings['hit_ups_auto_acc_no'],
					'hit_ups_auto_access_key' => $general_settings['hit_ups_auto_access_key'],
					'hit_ups_auto_rest_site_id' 	=>	isset($general_settings['hit_ups_auto_rest_site_id']) ? $general_settings['hit_ups_auto_rest_site_id'] : '',
					'hit_ups_auto_rest_site_pwd' 	=>	isset($general_settings['hit_ups_auto_rest_site_pwd']) ? $general_settings['hit_ups_auto_rest_site_pwd'] : '',
					'hit_ups_auto_rest_acc_no' 		=>	isset($general_settings['hit_ups_auto_rest_acc_no']) ? $general_settings['hit_ups_auto_rest_acc_no'] : '',
					'hit_ups_auto_rest_grant_type' 	=>	isset($general_settings['hit_ups_auto_rest_grant_type']) ? $general_settings['hit_ups_auto_rest_grant_type'] : '',
					'hit_ups_auto_shipper_name' => $general_settings['hit_ups_auto_shipper_name'],
					'hit_ups_auto_company' => $general_settings['hit_ups_auto_company'],
					'hit_ups_auto_mob_num' => $general_settings['hit_ups_auto_mob_num'],
					'hit_ups_auto_email' => $general_settings['hit_ups_auto_email'],
					'hit_ups_auto_address1' => $general_settings['hit_ups_auto_address1'],
					'hit_ups_auto_address2' => $general_settings['hit_ups_auto_address2'],
					'hit_ups_auto_city' => $general_settings['hit_ups_auto_city'],
					'hit_ups_auto_state' => $general_settings['hit_ups_auto_state'],
					'hit_ups_auto_zip' => $general_settings['hit_ups_auto_zip'],
					'hit_ups_auto_country' => $general_settings['hit_ups_auto_country'],
					'hit_ups_auto_gstin' => $general_settings['hit_ups_auto_gstin'],
					'hit_ups_auto_con_rate' => $general_settings['hit_ups_auto_con_rate'],
					'service_code' => $service_code,
					'hit_ups_auto_label_email' => $general_settings['hit_ups_auto_label_email'],
					'hit_ups_auto_ven_col_type' => isset($general_settings['hit_ups_auto_ven_col_type']) ? $general_settings['hit_ups_auto_ven_col_type'] : "",
					'hit_ups_auto_ven_col_id' => isset($general_settings['hit_ups_auto_ven_col_id']) ? $general_settings['hit_ups_auto_ven_col_id'] : "",
				);
				$vendor_settings = array();
				$mod_weg_unit = (!empty($config_weg_unit) && $config_weg_unit == 'LB_IN') ? 'lbs' : 'kg';
				$mod_dim_unit = (!empty($config_weg_unit) && $config_weg_unit == 'LB_IN') ? 'in' : 'cm';

				foreach ($items as $item) {
					$product_data = $item->get_data();
					$product = array();
					$product['product_name'] = $product_data['name'];
					$product['product_quantity'] = $product_data['quantity'];
					$product['product_id'] = $product_data['product_id'];
					$product_variation_id = $item->get_variation_id();
					if (empty($product_variation_id)) {
						$getproduct = wc_get_product($product_data['product_id']);
					} else {
						$getproduct = wc_get_product($product_variation_id);
					}

					$product['price'] = $getproduct->get_price();
					$product['width'] = !empty($getproduct->get_width()) ? round(wc_get_dimension($getproduct->get_width(), $mod_dim_unit, $woo_dim_unit)) : 0.5;
					$product['height'] = !empty($getproduct->get_height()) ? round(wc_get_dimension($getproduct->get_height(), $mod_dim_unit, $woo_dim_unit)) : 0.5;
					$product['depth'] = !empty($getproduct->get_length()) ? round(wc_get_dimension($getproduct->get_length(), $mod_dim_unit, $woo_dim_unit)) : 0.5;
					$product['weight'] = !empty($getproduct->get_weight()) ? (float)round(wc_get_weight($getproduct->get_weight(), $mod_weg_unit, $woo_weg_unit), 2) : 0.5;
					$total_weg += $product['weight'];
					$pack_products[] = $product;
				}

				if (isset($general_settings['hit_ups_auto_v_enable']) && $general_settings['hit_ups_auto_v_enable'] == 'yes' && isset($general_settings['hit_ups_auto_v_labels']) && $general_settings['hit_ups_auto_v_labels'] == 'yes') {
					// Multi Vendor Enabled
					foreach ($pack_products as $key => $value) {

						$product_id = $value['product_id'];
						if ($this->hpos_enabled) {
						    $hpos_prod_data = wc_get_product($product_id);
						    $ups_account = $hpos_prod_data->get_meta("hit_ups_auto_address");
						} else {
							$ups_account = get_post_meta($product_id, 'hit_ups_auto_address', true);
						}
						if (empty($ups_account) || $ups_account == 'default') {
							$ups_account = 'default';

							if (!isset($vendor_settings[$ups_account])) {
								$vendor_settings[$ups_account] = $custom_settings['default'];
							}
							$vendor_settings[$ups_account]['products'][] = $value;
						}

						if ($ups_account != 'default') {
							$user_account = get_post_meta($ups_account, 'hit_ups_auto_vendor_settings', true);
							$user_account = empty($user_account) ? array() : $user_account;
							if (!empty($user_account)) {
								if (!isset($vendor_settings[$ups_account])) {

									$vendor_settings[$ups_account] = $custom_settings['default'];

									if ($user_account['hit_ups_auto_site_id'] != '' && $user_account['hit_ups_auto_site_pwd'] != '' && $user_account['hit_ups_auto_acc_no'] != '') {

										$vendor_settings[$ups_account]['hit_ups_auto_site_id'] = $user_account['hit_ups_auto_site_id'];

										if ($user_account['hit_ups_auto_site_pwd'] != '') {
											$vendor_settings[$ups_account]['hit_ups_auto_site_pwd'] = $user_account['hit_ups_auto_site_pwd'];
										}

										if ($user_account['hit_ups_auto_acc_no'] != '') {
											$vendor_settings[$ups_account]['hit_ups_auto_acc_no'] = $user_account['hit_ups_auto_acc_no'];
										}

										$vendor_settings[$ups_account]['hit_ups_auto_access_key'] = !empty($user_account['hit_ups_auto_access_key']) ? $user_account['hit_ups_auto_access_key'] : '';
									}
									// REST credentials
									$vendor_settings[$ups_account]['hit_ups_auto_rest_site_id'] = isset($user_account['hit_ups_auto_rest_site_id']) ? $user_account['hit_ups_auto_rest_site_id'] : "";
									$vendor_settings[$ups_account]['hit_ups_auto_rest_site_pwd'] = isset($user_account['hit_ups_auto_rest_site_pwd']) ? $user_account['hit_ups_auto_rest_site_pwd'] : "";
									$vendor_settings[$ups_account]['hit_ups_auto_rest_acc_no'] = isset($user_account['hit_ups_auto_rest_acc_no']) ? $user_account['hit_ups_auto_rest_acc_no'] : "";
									$vendor_settings[$ups_account]['hit_ups_auto_rest_grant_type'] = isset($user_account['hit_ups_auto_rest_grant_type']) ? $user_account['hit_ups_auto_rest_grant_type'] : "client_credentials";
									if ($user_account['hit_ups_auto_address1'] != '' && $user_account['hit_ups_auto_city'] != '' && $user_account['hit_ups_auto_state'] != '' && $user_account['hit_ups_auto_zip'] != '' && $user_account['hit_ups_auto_country'] != '' && $user_account['hit_ups_auto_shipper_name'] != '') {

										if ($user_account['hit_ups_auto_shipper_name'] != '') {
											$vendor_settings[$ups_account]['hit_ups_auto_shipper_name'] = $user_account['hit_ups_auto_shipper_name'];
										}

										if ($user_account['hit_ups_auto_company'] != '') {
											$vendor_settings[$ups_account]['hit_ups_auto_company'] = $user_account['hit_ups_auto_company'];
										}

										if ($user_account['hit_ups_auto_mob_num'] != '') {
											$vendor_settings[$ups_account]['hit_ups_auto_mob_num'] = $user_account['hit_ups_auto_mob_num'];
										}

										if ($user_account['hit_ups_auto_email'] != '') {
											$vendor_settings[$ups_account]['hit_ups_auto_email'] = $user_account['hit_ups_auto_email'];
										}

										if ($user_account['hit_ups_auto_address1'] != '') {
											$vendor_settings[$ups_account]['hit_ups_auto_address1'] = $user_account['hit_ups_auto_address1'];
										}

										$vendor_settings[$ups_account]['hit_ups_auto_address2'] = $user_account['hit_ups_auto_address2'];

										if ($user_account['hit_ups_auto_city'] != '') {
											$vendor_settings[$ups_account]['hit_ups_auto_city'] = $user_account['hit_ups_auto_city'];
										}

										if ($user_account['hit_ups_auto_state'] != '') {
											$vendor_settings[$ups_account]['hit_ups_auto_state'] = $user_account['hit_ups_auto_state'];
										}

										if ($user_account['hit_ups_auto_zip'] != '') {
											$vendor_settings[$ups_account]['hit_ups_auto_zip'] = $user_account['hit_ups_auto_zip'];
										}

										if ($user_account['hit_ups_auto_country'] != '') {
											$vendor_settings[$ups_account]['hit_ups_auto_country'] = $user_account['hit_ups_auto_country'];
										}

										$vendor_settings[$ups_account]['hit_ups_auto_gstin'] = $user_account['hit_ups_auto_gstin'];

										$vendor_settings[$ups_account]['hit_ups_auto_con_rate'] = $user_account['hit_ups_auto_con_rate'];
										if (isset($user_account['hit_ups_auto_ven_col_type']) && isset($user_account['hit_ups_auto_ven_col_id']) && !empty($user_account['hit_ups_auto_ven_col_id'])) {
											$vendor_settings[$ups_account]['hit_ups_auto_ven_col_type'] = $user_account['hit_ups_auto_ven_col_type'];
											$vendor_settings[$ups_account]['hit_ups_auto_ven_col_id'] = $user_account['hit_ups_auto_ven_col_id'];
										}
									}

									if (isset($general_settings['hit_ups_auto_v_email']) && $general_settings['hit_ups_auto_v_email'] == 'yes') {
										$user_dat = get_userdata($ups_account);
										$vendor_settings[$ups_account]['hit_ups_auto_label_email'] = $user_dat->data->user_email;
									}

									if ($multi_ven != '') {
										$array_ven = explode('|', $multi_ven);
										$scode = '';
										foreach ($array_ven as $key => $svalue) {
											$ex_service = explode("_", $svalue);
											if ($ex_service[0] == $ups_account) {
												$vendor_settings[$ups_account]['service_code'] = $ex_service[1];
											}
										}

										if ($scode == '') {
											if ($order_data['shipping']['country'] != $vendor_settings[$ups_account]['hit_ups_auto_country']) {
												$vendor_settings[$ups_account]['service_code'] = $user_account['hit_ups_auto_def_inter'];
											} else {
												$vendor_settings[$ups_account]['service_code'] = $user_account['hit_ups_auto_def_dom'];
											}
										}
									} else {
										if ($order_data['shipping']['country'] != $vendor_settings[$ups_account]['hit_ups_auto_country']) {
											$vendor_settings[$ups_account]['service_code'] = $user_account['hit_ups_auto_def_inter'];
										} else {
											$vendor_settings[$ups_account]['service_code'] = $user_account['hit_ups_auto_def_dom'];
										}
									}
								}
								unset($value['product_id']);
								$vendor_settings[$ups_account]['products'][] = $value;
							}
						}
					}
				}
				if (empty($vendor_settings)) {
					$custom_settings['default']['products'] = $pack_products;
				} else {
					$custom_settings = $vendor_settings;
				}
				$order_id = $order_data['id'];
				$order_currency = $order_data['currency'];

				// $order_shipping_first_name = $order_data['shipping']['first_name'];
				// $order_shipping_last_name = $order_data['shipping']['last_name'];
				// $order_shipping_company = empty($order_data['shipping']['company']) ? $order_data['shipping']['first_name'] :  $order_data['shipping']['company'];
				// $order_shipping_address_1 = $order_data['shipping']['address_1'];
				// $order_shipping_address_2 = $order_data['shipping']['address_2'];
				// $order_shipping_city = $order_data['shipping']['city'];
				// $order_shipping_state = $order_data['shipping']['state'];
				// $order_shipping_postcode = $order_data['shipping']['postcode'];
				// $order_shipping_country = $order_data['shipping']['country'];
				// $order_shipping_phone = $order_data['billing']['phone'];
				// $order_shipping_email = $order_data['billing']['email'];

				$shipping_arr = (isset($order_data['shipping']['first_name']) && $order_data['shipping']['first_name'] != "") ? $order_data['shipping'] : $order_data['billing'];
                $order_shipping_first_name = $shipping_arr['first_name'];
                $order_shipping_last_name = $shipping_arr['last_name'];
                $order_shipping_company = empty($shipping_arr['company']) ? $shipping_arr['first_name'] :  $shipping_arr['company'];
                $order_shipping_address_1 = $shipping_arr['address_1'];
                $order_shipping_address_2 = $shipping_arr['address_2'];
                $order_shipping_city = $shipping_arr['city'];
                $order_shipping_state = $shipping_arr['state'];
                $order_shipping_postcode = $shipping_arr['postcode'];
                $order_shipping_country = $shipping_arr['country'];
                $order_shipping_phone = $order_data['billing']['phone'];
                $order_shipping_email = $order_data['billing']['email'];

				$desination_country = (isset($order_data['shipping']['country']) && $order_data['shipping']['country'] != '') ? $order_data['shipping']['country'] : $order_data['billing']['country'];
				if (empty($service_code)) {
					if (!isset($general_settings['hit_ups_international_service']) && !isset($general_settings['hit_ups_Domestic_service'])) {
						return;
					}
					if (isset($general_settings['hit_ups_auto_country']) && $general_settings["hit_ups_auto_country"] == $desination_country && $general_settings['hit_ups_Domestic_service'] != 'null') {
						$service_code = str_replace("ups_", "", $general_settings['hit_ups_Domestic_service']);
					} elseif (isset($general_settings['hit_ups_auto_country']) && $general_settings["hit_ups_auto_country"] != $desination_country && $general_settings['hit_ups_international_service'] != 'null') {
						$service_code = str_replace("ups_", "", $general_settings['hit_ups_international_service']);
					} else {
						return;
					}
				}
				if (!empty($general_settings) && isset($general_settings['hit_ups_auto_integration_key'])) {
					$mode = 'live';
					if (isset($general_settings['hit_ups_auto_test']) && $general_settings['hit_ups_auto_test'] == 'yes') {
						$mode = 'test';
					}
					$execution = 'auto';
					// if (isset($general_settings['hit_ups_auto_label_automation']) && $general_settings['hit_ups_auto_label_automation'] == 'yes') {
					// 	$execution = 'auto';
					// }
					$pack_type = isset($_POST['hit_ups_auto_pack_type']) ? sanitize_text_field($_POST['hit_ups_auto_pack_type']) : '02';
					$shipping_total = $order->get_shipping_total();
					foreach ($custom_settings as $key => $cvalue) {
						$data = array();
						$data['integrated_key'] = $general_settings['hit_ups_auto_integration_key'];
						$data['order_id'] = $order_id;
						$data['exec_type'] = $execution;
						$data['mode'] = $mode;
						$data['carrier_type'] = "UPS";
						$data['ship_price'] = $order_data['shipping_total'];
						$data['acc_rates'] = ($general_settings['hit_ups_auto_account_rates'] == 'yes') ? "Y" : "N";
						$data['meta'] = array(
							"site_id" => $cvalue['hit_ups_auto_site_id'],
							"password"  => $cvalue['hit_ups_auto_site_pwd'],
							"accountnum" => $cvalue['hit_ups_auto_acc_no'],
							"site_acess" => $cvalue['hit_ups_auto_access_key'],
							"api_type" => isset($general_settings['hit_ups_auto_api_type']) ? $general_settings['hit_ups_auto_api_type'] : "SOAP",
							"rest_api_key" => isset($cvalue['hit_ups_auto_rest_site_id']) ? $cvalue['hit_ups_auto_rest_site_id'] : "",
							"rest_secret_key" => isset($cvalue['hit_ups_auto_rest_site_pwd']) ? $cvalue['hit_ups_auto_rest_site_pwd'] : "",
							"rest_acc_no" => isset($cvalue['hit_ups_auto_rest_acc_no']) ? $cvalue['hit_ups_auto_rest_acc_no'] : "",
							"rest_grant_type" => isset($cvalue['hit_ups_auto_rest_grant_type']) ? $cvalue['hit_ups_auto_rest_grant_type'] : "",
							"t_company" => $order_shipping_company,
							"t_address1" => $order_shipping_address_1,
							"t_address2" => $order_shipping_address_2,
							"t_city" => $order_shipping_city,
							"t_state" => $order_shipping_state,
							"t_postal" => $order_shipping_postcode,
							"t_country" => $order_shipping_country,
							"t_name" => $order_shipping_first_name . ' ' . $order_shipping_last_name,
							"t_phone" => $order_shipping_phone,
							"t_email" => $order_shipping_email,
							"dutiable" => $general_settings['hit_ups_auto_duty_payment'],
							"insurance" => $general_settings['hit_ups_auto_insure'],
							"pack_this" => "Y",
							"residential" => 'false',
							"drop_off_type" => "REGULAR_PICKUP",
							"packing_type" => $pack_type,
							"shipping_charge" => $shipping_total,
							"products" => $cvalue['products'],
							"pack_algorithm" => $general_settings['hit_ups_auto_packing_type'],
							"max_weight" => $general_settings['hit_ups_auto_max_weight'],
							"wight_dim_unit" => $general_settings['hit_ups_auto_weight_unit'],
							"plt" => ($general_settings['hit_ups_auto_ppt'] == 'yes') ? "Y" : "N",
							"airway_bill" => ($general_settings['hit_ups_auto_aabill'] == 'yes') ? "Y" : "N",
							"sd" => ($general_settings['hit_ups_auto_sat'] == 'yes') ? "Y" : "N",
							"cod" => ($general_settings['hit_ups_auto_cod'] == 'yes') ? "Y" : "N",
							"service_code" => $service_code,
							"shipment_content" => $ship_content,
							"s_company" => $cvalue['hit_ups_auto_company'],
							"s_address1" => $cvalue['hit_ups_auto_address1'],
							"s_address2" => $cvalue['hit_ups_auto_address2'],
							"s_city" => $cvalue['hit_ups_auto_city'],
							"s_state" => $cvalue['hit_ups_auto_state'],
							"s_postal" => $cvalue['hit_ups_auto_zip'],
							"s_country" => $cvalue['hit_ups_auto_country'],
							"gstin" => $cvalue['hit_ups_auto_gstin'],
							"s_name" => $cvalue['hit_ups_auto_shipper_name'],
							"s_phone" => $cvalue['hit_ups_auto_mob_num'],
							"s_email" => $cvalue['hit_ups_auto_email'],
							"ven_col_type" => isset($cvalue['hit_ups_auto_ven_col_type']) ? $cvalue['hit_ups_auto_ven_col_type'] : "",
							"ven_col_id" => isset($cvalue['hit_ups_auto_ven_col_id']) ? $cvalue['hit_ups_auto_ven_col_id'] : "",
							"sig_req" => "",				//$general_settings['hit_ups_auto_sign_req'],
							"label_format" => "GIF",
							"label_size" => $general_settings['hit_ups_auto_print_size'],
							"sent_email_to" => $cvalue['hit_ups_auto_label_email'],
							"del_con" => isset($general_settings['hit_ups_auto_del_con']) ? $general_settings['hit_ups_auto_del_con'] : "NONE",
							"label" => $key,

						);

						foreach ($data['meta']['products'] as $key => $value) {
							$data['meta']['products'][$key]['product_name'] = preg_replace('/[^\p{L}\s+\p{N}]/u', '', $value['product_name']);
						}
						// print_r(json_encode( $data));
						// Auto Shipment
						// $auto_ship_url = "http://localhost/hitshipo/label_api/create_shipment.php";
						$auto_ship_url = "https://app.myshipi.com/label_api/create_shipment.php";
						wp_remote_post($auto_ship_url, array(
								'method'      => 'POST',
								'timeout'     => 45,
								'redirection' => 5,
								'httpversion' => '1.0',
								'blocking'    => false,
								'headers'     => array('Content-Type' => 'application/json; charset=utf-8'),
								'body'        => json_encode($data),
								'sslverify'   => FALSE
							)
						);

					}
				}
			}
			// Save the data of the Meta field
			public function hit_create_ups_shipping($order_id)
			{
				if ($this->hpos_enabled) {
	 		        if ('shop_order' !== Automattic\WooCommerce\Utilities\OrderUtil::get_order_type($order_id)) {
	 		            return;
	 		        }
	 		    } else {
	 		    	$post = get_post($order_id);
					if ($post->post_type != 'shop_order') {
						return;
					}
	 		    }

				if (isset($_POST['hit_ups_auto_reset'])) {
					delete_option('hit_ups_auto_values_' . $order_id);
				}

				if (isset($_POST['hit_ups_auto_create_label'])) {
					$create_shipment_for = sanitize_text_field($_POST['hit_ups_auto_create_label']);
					$service_code = str_replace("ups_", "", sanitize_text_field($_POST['hit_ups_auto_service_code_' . $create_shipment_for]));
					$ship_content = !empty($_POST['hit_ups_auto_shipment_content_' . $create_shipment_for]) ? sanitize_text_field($_POST['hit_ups_auto_shipment_content_' . $create_shipment_for]) : 'Shipment Content';
					$order = wc_get_order($order_id);
					if ($order) {
						$order_data = $order->get_data();
						$order_id = $order_data['id'];
						$order_currency = $order_data['currency'];

						// $order_shipping_first_name = $order_data['shipping']['first_name'];
						// $order_shipping_last_name = $order_data['shipping']['last_name'];
						// $order_shipping_company = empty($order_data['shipping']['company']) ? $order_data['shipping']['first_name'] :  $order_data['shipping']['company'];
						// $order_shipping_address_1 = $order_data['shipping']['address_1'];
						// $order_shipping_address_2 = $order_data['shipping']['address_2'];
						// $order_shipping_city = $order_data['shipping']['city'];
						// $order_shipping_state = $order_data['shipping']['state'];
						// $order_shipping_postcode = $order_data['shipping']['postcode'];
						// $order_shipping_country = $order_data['shipping']['country'];
						// $order_shipping_phone = $order_data['billing']['phone'];
						// $order_shipping_email = $order_data['billing']['email'];

						$shipping_arr = (isset($order_data['shipping']['first_name']) && $order_data['shipping']['first_name'] != "") ? $order_data['shipping'] : $order_data['billing'];
						$order_shipping_first_name = $shipping_arr['first_name'];
						$order_shipping_last_name = $shipping_arr['last_name'];
						$order_shipping_company = empty($shipping_arr['company']) ? $shipping_arr['first_name'] :  $shipping_arr['company'];
						$order_shipping_address_1 = $shipping_arr['address_1'];
						$order_shipping_address_2 = $shipping_arr['address_2'];
						$order_shipping_city = $shipping_arr['city'];
						$order_shipping_state = $shipping_arr['state'];
						$order_shipping_postcode = $shipping_arr['postcode'];
						$order_shipping_country = $shipping_arr['country'];
						$order_shipping_phone = $order_data['billing']['phone'];
						$order_shipping_email = $order_data['billing']['email'];

						$items = $order->get_items();
						$pack_products = array();
						$total_weg = 0;
						//weight conversion wc_get_weight( $weight, $to_unit, $from_unit )
						$general_settings = get_option('hit_ups_auto_main_settings', array());

						$woo_weg_unit = get_option('woocommerce_weight_unit');
						$woo_dim_unit = get_option('woocommerce_dimension_unit');
						$config_weg_unit = $general_settings['hit_ups_auto_weight_unit'];
						$mod_weg_unit = (!empty($config_weg_unit) && $config_weg_unit == 'LB_IN') ? 'lbs' : 'kg';
						$mod_dim_unit = (!empty($config_weg_unit) && $config_weg_unit == 'LB_IN') ? 'in' : 'cm';

						foreach ($items as $item) {
							$product_data = $item->get_data();
							$product = array();
							$product['product_name'] = $product_data['name'];
							$product['product_quantity'] = $product_data['quantity'];
							$product['product_id'] = $product_data['product_id'];

							$product_variation_id = $item->get_variation_id();
							if (empty($product_variation_id)) {
								$getproduct = wc_get_product($product_data['product_id']);
							} else {
								$getproduct = wc_get_product($product_variation_id);
							}

							$product['price'] = $getproduct->get_price();
							$product['width'] = !empty($getproduct->get_width()) ? round(wc_get_dimension($getproduct->get_width(), $mod_dim_unit, $woo_dim_unit)) : 0.5;
							$product['height'] = !empty($getproduct->get_height()) ? round(wc_get_dimension($getproduct->get_height(), $mod_dim_unit, $woo_dim_unit)) : 0.5;
							$product['depth'] = !empty($getproduct->get_length()) ? round(wc_get_dimension($getproduct->get_length(), $mod_dim_unit, $woo_dim_unit)) : 0.5;
							$product['weight'] = !empty($getproduct->get_weight()) ? (float)round(wc_get_weight($getproduct->get_weight(), $mod_weg_unit, $woo_weg_unit), 2) : 0.5;
							$total_weg += $product['weight'];
							$pack_products[] = $product;
						}
						$custom_settings = array();
						$custom_settings['default'] = array(
							'hit_ups_auto_site_id' => $general_settings['hit_ups_auto_site_id'],
							'hit_ups_auto_site_pwd' => $general_settings['hit_ups_auto_site_pwd'],
							'hit_ups_auto_acc_no' => $general_settings['hit_ups_auto_acc_no'],
							'hit_ups_auto_access_key' => $general_settings['hit_ups_auto_access_key'],
							'hit_ups_auto_rest_site_id' 	=>	isset($general_settings['hit_ups_auto_rest_site_id']) ? $general_settings['hit_ups_auto_rest_site_id'] : '',
							'hit_ups_auto_rest_site_pwd' 	=>	isset($general_settings['hit_ups_auto_rest_site_pwd']) ? $general_settings['hit_ups_auto_rest_site_pwd'] : '',
							'hit_ups_auto_rest_acc_no' 		=>	isset($general_settings['hit_ups_auto_rest_acc_no']) ? $general_settings['hit_ups_auto_rest_acc_no'] : '',
							'hit_ups_auto_rest_grant_type' 	=>	isset($general_settings['hit_ups_auto_rest_grant_type']) ? $general_settings['hit_ups_auto_rest_grant_type'] : '',
							'hit_ups_auto_shipper_name' => $general_settings['hit_ups_auto_shipper_name'],
							'hit_ups_auto_company' => $general_settings['hit_ups_auto_company'],
							'hit_ups_auto_mob_num' => $general_settings['hit_ups_auto_mob_num'],
							'hit_ups_auto_email' => $general_settings['hit_ups_auto_email'],
							'hit_ups_auto_address1' => $general_settings['hit_ups_auto_address1'],
							'hit_ups_auto_address2' => $general_settings['hit_ups_auto_address2'],
							'hit_ups_auto_city' => $general_settings['hit_ups_auto_city'],
							'hit_ups_auto_state' => $general_settings['hit_ups_auto_state'],
							'hit_ups_auto_zip' => $general_settings['hit_ups_auto_zip'],
							'hit_ups_auto_country' => $general_settings['hit_ups_auto_country'],
							'hit_ups_auto_gstin' => $general_settings['hit_ups_auto_gstin'],
							'hit_ups_auto_con_rate' => $general_settings['hit_ups_auto_con_rate'],
							'service_code' => $service_code,
							'hit_ups_auto_label_email' => $general_settings['hit_ups_auto_label_email'],
							'hit_ups_auto_ven_col_type' => isset($general_settings['hit_ups_auto_ven_col_type']) ? $general_settings['hit_ups_auto_ven_col_type'] : "",
							'hit_ups_auto_ven_col_id' => isset($general_settings['hit_ups_auto_ven_col_id']) ? $general_settings['hit_ups_auto_ven_col_id'] : "",
						);

						$vendor_settings = array();
						if (isset($general_settings['hit_ups_auto_v_enable']) && $general_settings['hit_ups_auto_v_enable'] == 'yes' && isset($general_settings['hit_ups_auto_v_labels']) && $general_settings['hit_ups_auto_v_labels'] == 'yes') {
							// Multi Vendor Enabled
							
							foreach ($pack_products as $key => $value) {
								$product_id = $value['product_id'];
								if ($this->hpos_enabled) {
								    $hpos_prod_data = wc_get_product($product_id);
								    $ups_account = $hpos_prod_data->get_meta("hit_ups_auto_address");
								} else {
									$ups_account = get_post_meta($product_id, 'hit_ups_auto_address', true);
								}
								if (empty($ups_account) || $ups_account == 'default') {
									$ups_account = 'default';
									if (!isset($vendor_settings[$ups_account])) {
										$vendor_settings[$ups_account] = $custom_settings['default'];
									}

									$vendor_settings[$ups_account]['products'][] = $value;
								}
								
								if ($ups_account != 'default') {
									$user_account = get_post_meta($ups_account, 'hit_ups_auto_vendor_settings', true);
									$user_account = empty($user_account) ? array() : $user_account;
									if (!empty($user_account)) {
										if (!isset($vendor_settings[$ups_account])) {

											$vendor_settings[$ups_account] = $custom_settings['default'];

											if ($user_account['hit_ups_auto_site_id'] != '' && $user_account['hit_ups_auto_site_pwd'] != '' && $user_account['hit_ups_auto_acc_no'] != '') {

												$vendor_settings[$ups_account]['hit_ups_auto_site_id'] = $user_account['hit_ups_auto_site_id'];

												if ($user_account['hit_ups_auto_site_pwd'] != '') {
													$vendor_settings[$ups_account]['hit_ups_auto_site_pwd'] = $user_account['hit_ups_auto_site_pwd'];
												}

												if ($user_account['hit_ups_auto_acc_no'] != '') {
													$vendor_settings[$ups_account]['hit_ups_auto_acc_no'] = $user_account['hit_ups_auto_acc_no'];
												}

												// $vendor_settings[$ups_account]['hit_ups_auto_access_key'] = !empty($user_account['hit_ups_auto_access_key']) ? $user_account['hit_ups_auto_access_key'] : '';
												if ($user_account['hit_ups_auto_access_key'] != '') {
													$vendor_settings[$ups_account]['hit_ups_auto_access_key'] = $user_account['hit_ups_auto_access_key'];
												}
											}
											// REST credentials
											$vendor_settings[$ups_account]['hit_ups_auto_rest_site_id'] = isset($user_account['hit_ups_auto_rest_site_id']) ? $user_account['hit_ups_auto_rest_site_id'] : "";
											$vendor_settings[$ups_account]['hit_ups_auto_rest_site_pwd'] = isset($user_account['hit_ups_auto_rest_site_pwd']) ? $user_account['hit_ups_auto_rest_site_pwd'] : "";
											$vendor_settings[$ups_account]['hit_ups_auto_rest_acc_no'] = isset($user_account['hit_ups_auto_rest_acc_no']) ? $user_account['hit_ups_auto_rest_acc_no'] : "";
											$vendor_settings[$ups_account]['hit_ups_auto_rest_grant_type'] = isset($user_account['hit_ups_auto_rest_grant_type']) ? $user_account['hit_ups_auto_rest_grant_type'] : "client_credentials";
											if ($user_account['hit_ups_auto_address1'] != '' && $user_account['hit_ups_auto_city'] != '' && $user_account['hit_ups_auto_state'] != '' && $user_account['hit_ups_auto_zip'] != '' && $user_account['hit_ups_auto_country'] != '' && $user_account['hit_ups_auto_shipper_name'] != '') {

												if ($user_account['hit_ups_auto_shipper_name'] != '') {
													$vendor_settings[$ups_account]['hit_ups_auto_shipper_name'] = $user_account['hit_ups_auto_shipper_name'];
												}

												if ($user_account['hit_ups_auto_company'] != '') {
													$vendor_settings[$ups_account]['hit_ups_auto_company'] = $user_account['hit_ups_auto_company'];
												}

												if ($user_account['hit_ups_auto_mob_num'] != '') {
													$vendor_settings[$ups_account]['hit_ups_auto_mob_num'] = $user_account['hit_ups_auto_mob_num'];
												}

												if ($user_account['hit_ups_auto_email'] != '') {
													$vendor_settings[$ups_account]['hit_ups_auto_email'] = $user_account['hit_ups_auto_email'];
												}

												if ($user_account['hit_ups_auto_address1'] != '') {
													$vendor_settings[$ups_account]['hit_ups_auto_address1'] = $user_account['hit_ups_auto_address1'];
												}

												$vendor_settings[$ups_account]['hit_ups_auto_address2'] = $user_account['hit_ups_auto_address2'];

												if ($user_account['hit_ups_auto_city'] != '') {
													$vendor_settings[$ups_account]['hit_ups_auto_city'] = $user_account['hit_ups_auto_city'];
												}

												if ($user_account['hit_ups_auto_state'] != '') {
													$vendor_settings[$ups_account]['hit_ups_auto_state'] = $user_account['hit_ups_auto_state'];
												}

												if ($user_account['hit_ups_auto_zip'] != '') {
													$vendor_settings[$ups_account]['hit_ups_auto_zip'] = $user_account['hit_ups_auto_zip'];
												}

												if ($user_account['hit_ups_auto_country'] != '') {
													$vendor_settings[$ups_account]['hit_ups_auto_country'] = $user_account['hit_ups_auto_country'];
												}

												$vendor_settings[$ups_account]['hit_ups_auto_gstin'] = $user_account['hit_ups_auto_gstin'];

												$vendor_settings[$ups_account]['hit_ups_auto_con_rate'] = $user_account['hit_ups_auto_con_rate'];
												if (isset($user_account['hit_ups_auto_ven_col_type']) && isset($user_account['hit_ups_auto_ven_col_id']) && !empty($user_account['hit_ups_auto_ven_col_id'])) {
													$vendor_settings[$ups_account]['hit_ups_auto_ven_col_type'] = $user_account['hit_ups_auto_ven_col_type'];
													$vendor_settings[$ups_account]['hit_ups_auto_ven_col_id'] = $user_account['hit_ups_auto_ven_col_id'];
												}
											}

											if (isset($general_settings['hit_ups_auto_v_email']) && $general_settings['hit_ups_auto_v_email'] == 'yes') {
												$user_dat = get_userdata($ups_account);
												$vendor_settings[$ups_account]['hit_ups_auto_label_email'] = $user_dat->data->user_email;
											}


											if ($order_data['shipping']['country'] != $vendor_settings[$ups_account]['hit_ups_auto_country']) {
												$vendor_settings[$ups_account]['service_code'] = empty($service_code) ? $user_account['hit_ups_auto_def_inter'] : $service_code;
											} else {
												$vendor_settings[$ups_account]['service_code'] = empty($service_code) ? $user_account['hit_ups_auto_def_dom'] : $service_code;
											}
										}
										unset($value['product_id']);
										$vendor_settings[$ups_account]['products'][] = $value;
									}
								}
							}
						}

						if (empty($vendor_settings)) {

							$custom_settings['default']['products'] = $pack_products;
						} else {
							$custom_settings = $vendor_settings;
						}

						if (!empty($general_settings) && isset($general_settings['hit_ups_auto_integration_key']) && isset($custom_settings[$create_shipment_for])) {
							$mode = 'live';
							if (isset($general_settings['hit_ups_auto_test']) && $general_settings['hit_ups_auto_test'] == 'yes') {
								$mode = 'test';
							}

							$execution = 'manual';
							// if (isset($general_settings['hit_ups_auto_label_automation']) && $general_settings['hit_ups_auto_label_automation'] == 'yes') {
							// 	$execution = 'auto';
							// }
							$pack_type = isset($_POST['hit_ups_auto_pack_type']) ? sanitize_text_field($_POST['hit_ups_auto_pack_type']) : '02';
							$shipping_total = $order->get_shipping_total();
							$data = array();
							$data['integrated_key'] = $general_settings['hit_ups_auto_integration_key'];
							$data['order_id'] = $order_id;
							$data['exec_type'] = $execution;
							$data['mode'] = $mode;
							$data['carrier_type'] = "UPS";
							$data['ship_price'] = $order_data['shipping_total'];
							$data['acc_rates'] = ($general_settings['hit_ups_auto_account_rates'] == 'yes') ? "Y" : "N";
							$data['meta'] = array(
								"site_id" => $custom_settings[$create_shipment_for]['hit_ups_auto_site_id'],
								"password"  => $custom_settings[$create_shipment_for]['hit_ups_auto_site_pwd'],
								"accountnum" => $custom_settings[$create_shipment_for]['hit_ups_auto_acc_no'],
								"site_acess" => $custom_settings[$create_shipment_for]['hit_ups_auto_access_key'],
								"api_type" => isset($general_settings['hit_ups_auto_api_type']) ? $general_settings['hit_ups_auto_api_type'] : "SOAP",
								"rest_api_key" => isset($custom_settings[$create_shipment_for]['hit_ups_auto_rest_site_id']) ? $custom_settings[$create_shipment_for]['hit_ups_auto_rest_site_id'] : "",
								"rest_secret_key" => isset($custom_settings[$create_shipment_for]['hit_ups_auto_rest_site_pwd']) ? $custom_settings[$create_shipment_for]['hit_ups_auto_rest_site_pwd'] : "",
								"rest_acc_no" => isset($custom_settings[$create_shipment_for]['hit_ups_auto_rest_acc_no']) ? $custom_settings[$create_shipment_for]['hit_ups_auto_rest_acc_no'] : "",
								"rest_grant_type" => isset($custom_settings[$create_shipment_for]['hit_ups_auto_rest_grant_type']) ? $custom_settings[$create_shipment_for]['hit_ups_auto_rest_grant_type'] : "",
								"t_company" => $order_shipping_company,
								"t_address1" => $order_shipping_address_1,
								"t_address2" => $order_shipping_address_2,
								"t_city" => $order_shipping_city,
								"t_state" => $order_shipping_state,
								"t_postal" => $order_shipping_postcode,
								"t_country" => $order_shipping_country,
								"t_name" => $order_shipping_first_name . ' ' . $order_shipping_last_name,
								"t_phone" => $order_shipping_phone,
								"t_email" => $order_shipping_email,
								"dutiable" => $general_settings['hit_ups_auto_duty_payment'],
								"insurance" => $general_settings['hit_ups_auto_insure'],
								"pack_this" => "Y",
								"residential" => 'false',
								"drop_off_type" => "REGULAR_PICKUP",
								"packing_type" => $pack_type,
								"shipping_charge" => $shipping_total,
								"products" => $custom_settings[$create_shipment_for]['products'],
								"pack_algorithm" => $general_settings['hit_ups_auto_packing_type'],
								"max_weight" => $general_settings['hit_ups_auto_max_weight'],
								"wight_dim_unit" => $general_settings['hit_ups_auto_weight_unit'],
								"plt" => ($general_settings['hit_ups_auto_ppt'] == 'yes') ? "Y" : "N",
								"airway_bill" => ($general_settings['hit_ups_auto_aabill'] == 'yes') ? "Y" : "N",
								"sd" => ($general_settings['hit_ups_auto_sat'] == 'yes') ? "Y" : "N",
								"cod" => ($general_settings['hit_ups_auto_cod'] == 'yes') ? "Y" : "N",
								"service_code" => str_replace('ups_', '', $custom_settings[$create_shipment_for]['service_code']),
								"shipment_content" => $ship_content,
								"s_company" => $custom_settings[$create_shipment_for]['hit_ups_auto_company'],
								"s_address1" => $custom_settings[$create_shipment_for]['hit_ups_auto_address1'],
								"s_address2" => $custom_settings[$create_shipment_for]['hit_ups_auto_address2'],
								"s_city" => $custom_settings[$create_shipment_for]['hit_ups_auto_city'],
								"s_state" => $custom_settings[$create_shipment_for]['hit_ups_auto_state'],
								"s_postal" => $custom_settings[$create_shipment_for]['hit_ups_auto_zip'],
								"s_country" => $custom_settings[$create_shipment_for]['hit_ups_auto_country'],
								"gstin" => $custom_settings[$create_shipment_for]['hit_ups_auto_gstin'],
								"s_name" => $custom_settings[$create_shipment_for]['hit_ups_auto_shipper_name'],
								"s_phone" => $custom_settings[$create_shipment_for]['hit_ups_auto_mob_num'],
								"s_email" => $custom_settings[$create_shipment_for]['hit_ups_auto_email'],
								"ven_col_type" => isset($custom_settings[$create_shipment_for]['hit_ups_auto_ven_col_type']) ? $custom_settings[$create_shipment_for]['hit_ups_auto_ven_col_type'] : "",
								"ven_col_id" => isset($custom_settings[$create_shipment_for]['hit_ups_auto_ven_col_id']) ? $custom_settings[$create_shipment_for]['hit_ups_auto_ven_col_id'] : "",
								"label_format" => "GIF",
								"sig_req" => "",
								"label_size" => $general_settings['hit_ups_auto_print_size'],
								"sent_email_to" => $custom_settings[$create_shipment_for]['hit_ups_auto_label_email'],
								"debug_log" => ($general_settings['hit_ups_auto_developer_rate'] == 'yes') ? 'Y' : 'N',
								"add_shipping_invoice_flag" => ($general_settings['hit_ups_add_shipping_invoice'] == "yes") ? 'Y' : 'N',
								"del_con" => isset($general_settings['hit_ups_auto_del_con']) ? $general_settings['hit_ups_auto_del_con'] : "NONE",
								"label" => $create_shipment_for
							);
							
							foreach ($data['meta']['products'] as $key => $value) {
								$data['meta']['products'][$key]['product_name'] = preg_replace('/[^\p{L}\s+\p{N}]/u', '', $value['product_name']);
							}

						// Manual Shipment
						// $manual_ship_url = "http://localhost/hitshipo/api/ups_manual.php";
						// $manual_ship_url = "http://localhost/hitshipo/label_api/create_shipment.php";
						$manual_ship_url = "https://app.myshipi.com/label_api/create_shipment.php";
						
						$response = wp_remote_post( $manual_ship_url , array(
							'method'      => 'POST',
							'timeout'     => 45,
							'redirection' => 5,
							'httpversion' => '1.0',
							'blocking'    => true,
							'headers'     => array('Content-Type' => 'application/json; charset=utf-8'),
							'body'        => json_encode($data),
							'sslverify'   => FALSE
							)
						);
						
						$output = (is_array($response) && isset($response['body'])) ? json_decode($response['body'],true) : [];
						
							if ($output) {
								if (isset($output['status'])) {
									if (isset($output['status']) && $output['status'] != 'success') {
										update_option('hit_ups_auto_status_' . $order_id, $output['status']);
									} else if (isset($output['status']) && $output['status'] == 'success') {
										$output['user_id'] = $create_shipment_for;
										$result_arr = [];
										$val = get_option('hit_ups_auto_values_' . $order_id, array());
										if(!empty($val)){
											$result_arr = json_decode($val, true);
										}
										$result_arr[] = $output;
										
										$order_status = apply_filters('hitshipo_ups_cus_order_status','');
										if (!empty($order_status)) {
											$order = wc_get_order($order_id);
											$order->update_status($order_status);
										}
										update_option('hit_ups_auto_values_' . $order_id, json_encode($result_arr));
									} else {
										update_option('hit_ups_auto_status_' . $order_id, "Unknown response found.");
									}
								}
								
							} 
							
							else {
								update_option('hit_ups_auto_status_' . $order_id, 'Site not Connected with Shipi. Contact Shipi Team.');
							}
						}
					}
				}
			}
			public function sanitize_array($string)
			{
				foreach ($string as $k => $v) {
					if (is_array($v)) {
						$string[$k] = $this->sanitize_array($v);
					} else {
						$string[$k] = sanitize_text_field($v);
					}
				}

				return $string;
			}

			public function create_ups_return_label($order_id)
			{
				if ($this->hpos_enabled) {
	 		        if ('shop_order' !== Automattic\WooCommerce\Utilities\OrderUtil::get_order_type($order_id)) {
	 		            return;
	 		        }
	 		    } else {
	 		    	$post = get_post($order_id);
					if ($post->post_type != 'shop_order') {
						return;
					}
	 		    }

				if (isset($_POST['hit_ups_auto_return_reset'])) {
					delete_option('hit_ups_auto_return_values_' . $order_id);
				}

				if (isset($_POST['hit_ups_auto_create_return_label'])) {
					$create_shipment_for = sanitize_text_field($_POST['hit_ups_auto_create_return_label']);
					$service_code = str_replace("ups_", "", sanitize_text_field($_POST['hit_ups_auto_return_service_code_' . $create_shipment_for]));
					$ship_content = "Return Shipment";
					$enabled_products = isset($_POST['return_products_ups']) ? $this->sanitize_array($_POST['return_products_ups']) : array();
					$qty_products = isset($_POST['qty_products_ups']) ? $this->sanitize_array($_POST['qty_products_ups']) : array();
					$order = wc_get_order($order_id);
					$order = wc_get_order($order_id);
					if ($order && !empty($enabled_products)) {
						$order_data = $order->get_data();
						$order_id = $order_data['id'];
						$order_currency = $order_data['currency'];

						// $order_shipping_first_name = $order_data['shipping']['first_name'];
						// $order_shipping_last_name = $order_data['shipping']['last_name'];
						// $order_shipping_company = empty($order_data['shipping']['company']) ? $order_data['shipping']['first_name'] :  $order_data['shipping']['company'];
						// $order_shipping_address_1 = $order_data['shipping']['address_1'];
						// $order_shipping_address_2 = $order_data['shipping']['address_2'];
						// $order_shipping_city = $order_data['shipping']['city'];
						// $order_shipping_state = $order_data['shipping']['state'];
						// $order_shipping_postcode = $order_data['shipping']['postcode'];
						// $order_shipping_country = $order_data['shipping']['country'];
						// $order_shipping_phone = $order_data['billing']['phone'];
						// $order_shipping_email = $order_data['billing']['email'];

						$shipping_arr = (isset($order_data['shipping']['first_name']) && $order_data['shipping']['first_name'] != "") ? $order_data['shipping'] : $order_data['billing'];
						$order_shipping_first_name = $shipping_arr['first_name'];
						$order_shipping_last_name = $shipping_arr['last_name'];
						$order_shipping_company = empty($shipping_arr['company']) ? $shipping_arr['first_name'] :  $shipping_arr['company'];
						$order_shipping_address_1 = $shipping_arr['address_1'];
						$order_shipping_address_2 = $shipping_arr['address_2'];
						$order_shipping_city = $shipping_arr['city'];
						$order_shipping_state = $shipping_arr['state'];
						$order_shipping_postcode = $shipping_arr['postcode'];
						$order_shipping_country = $shipping_arr['country'];
						$order_shipping_phone = $order_data['billing']['phone'];
						$order_shipping_email = $order_data['billing']['email'];

						$items = $order->get_items();
						$pack_products = array();

						$total_weg = 0;
						//weight conversion wc_get_weight( $weight, $to_unit, $from_unit )
						$general_settings = get_option('hit_ups_auto_main_settings', array());

						$woo_weg_unit = get_option('woocommerce_weight_unit');
						$woo_dim_unit = get_option('woocommerce_dimension_unit');
						$config_weg_unit = $general_settings['hit_ups_auto_weight_unit'];
						$mod_weg_unit = (!empty($config_weg_unit) && $config_weg_unit == 'LB_IN') ? 'lbs' : 'kg';
						$mod_dim_unit = (!empty($config_weg_unit) && $config_weg_unit == 'LB_IN') ? 'in' : 'cm';

						foreach ($items as $item) {
							$product_data = $item->get_data();
							$product = array();
							$product['product_name'] = $product_data['name'];
							$product['product_quantity'] = $product_data['quantity'];
							$product['product_id'] = $product_data['product_id'];

							// if ($this->hpos_enabled) {
							//     $hpos_prod_data = wc_get_product($product_data['product_id']);
							//     $saved_cc = $hpos_prod_data->get_meta("hits_dhl_cc");
							// } else {
							// 	$saved_cc = get_post_meta($product_data['product_id'], 'hits_dhl_cc', true);
							// }
							// if (!empty($saved_cc)) {
							// 	$product['commodity_code'] = $saved_cc;
							// }

							$product_variation_id = $item->get_variation_id();
							$product_id = $product_data['product_id'];
							if (empty($product_variation_id)) {
								$getproduct = wc_get_product($product_data['product_id']);
							} else {
								$getproduct = wc_get_product($product_variation_id);
								$product_id = $product_variation_id;
							}

							if (!in_array($product_id, $enabled_products)) {
								continue;
							} else {
								if ($qty_products[$product_id] == 0) {
									continue;
								} else {
									$product['product_quantity'] = $qty_products[$product_id];
								}
							}

							$product['price'] = $getproduct->get_price();
							if (!$product['price']) {
								$product['price'] = (isset($product_data['total']) && isset($product_data['quantity'])) ? number_format(($product_data['total'] / $product_data['quantity']), 2) : 0;
							}
							$product['width'] = !empty($getproduct->get_width()) ? round(wc_get_dimension($getproduct->get_width(), $mod_dim_unit, $woo_dim_unit)) : 0.5;
							$product['height'] = !empty($getproduct->get_height()) ? round(wc_get_dimension($getproduct->get_height(), $mod_dim_unit, $woo_dim_unit)) : 0.5;
							$product['depth'] = !empty($getproduct->get_length()) ? round(wc_get_dimension($getproduct->get_length(), $mod_dim_unit, $woo_dim_unit)) : 0.5;
							$product['weight'] = !empty($getproduct->get_weight()) ? (float)round(wc_get_weight($getproduct->get_weight(), $mod_weg_unit, $woo_weg_unit), 2) : 0.5;
							$total_weg += $product['weight'];
							$pack_products[] = $product;
							$return_prod[] = $product;
						}
						$custom_settings = array();
						$custom_settings['default'] = array(
							'hit_ups_auto_site_id' => $general_settings['hit_ups_auto_site_id'],
							'hit_ups_auto_site_pwd' => $general_settings['hit_ups_auto_site_pwd'],
							'hit_ups_auto_acc_no' => $general_settings['hit_ups_auto_acc_no'],
							'hit_ups_auto_access_key' => $general_settings['hit_ups_auto_access_key'],
							'hit_ups_auto_rest_site_id' 	=>	isset($general_settings['hit_ups_auto_rest_site_id']) ? $general_settings['hit_ups_auto_rest_site_id'] : '',
							'hit_ups_auto_rest_site_pwd' 	=>	isset($general_settings['hit_ups_auto_rest_site_pwd']) ? $general_settings['hit_ups_auto_rest_site_pwd'] : '',
							'hit_ups_auto_rest_acc_no' 		=>	isset($general_settings['hit_ups_auto_rest_acc_no']) ? $general_settings['hit_ups_auto_rest_acc_no'] : '',
							'hit_ups_auto_rest_grant_type' 	=>	isset($general_settings['hit_ups_auto_rest_grant_type']) ? $general_settings['hit_ups_auto_rest_grant_type'] : '',
							'hit_ups_auto_shipper_name' => $general_settings['hit_ups_auto_shipper_name'],
							'hit_ups_auto_company' => $general_settings['hit_ups_auto_company'],
							'hit_ups_auto_mob_num' => $general_settings['hit_ups_auto_mob_num'],
							'hit_ups_auto_email' => $general_settings['hit_ups_auto_email'],
							'hit_ups_auto_address1' => $general_settings['hit_ups_auto_address1'],
							'hit_ups_auto_address2' => $general_settings['hit_ups_auto_address2'],
							'hit_ups_auto_city' => $general_settings['hit_ups_auto_city'],
							'hit_ups_auto_state' => $general_settings['hit_ups_auto_state'],
							'hit_ups_auto_zip' => $general_settings['hit_ups_auto_zip'],
							'hit_ups_auto_country' => $general_settings['hit_ups_auto_country'],
							'hit_ups_auto_gstin' => $general_settings['hit_ups_auto_gstin'],
							'hit_ups_auto_con_rate' => $general_settings['hit_ups_auto_con_rate'],
							'service_code' => $service_code,
							'hit_ups_auto_label_email' => $general_settings['hit_ups_auto_label_email'],
							'hit_ups_auto_ven_col_type' => isset($general_settings['hit_ups_auto_ven_col_type']) ? $general_settings['hit_ups_auto_ven_col_type'] : "",
							'hit_ups_auto_ven_col_id' => isset($general_settings['hit_ups_auto_ven_col_id']) ? $general_settings['hit_ups_auto_ven_col_id'] : "",
						);

						$vendor_settings = array();

						if (isset($general_settings['hit_ups_auto_v_enable']) && $general_settings['hit_ups_auto_v_enable'] == 'yes' && isset($general_settings['hit_ups_auto_v_labels']) && $general_settings['hit_ups_auto_v_labels'] == 'yes') {
							// Multi Vendor Enabled
							foreach ($pack_products as $key => $value) {
								$product_id = $value['product_id'];
								if ($this->hpos_enabled) {
								    $hpos_prod_data = wc_get_product($product_id);
								    $ups_account = $hpos_prod_data->get_meta("hit_ups_auto_address");
								} else {
									$ups_account = get_post_meta($product_id, 'hit_ups_auto_address', true);
								}
								if (empty($ups_account) || $ups_account == 'default') {
									$ups_account = 'default';
									if (!isset($vendor_settings[$ups_account])) {
										$vendor_settings[$ups_account] = $custom_settings['default'];
									}

									$vendor_settings[$ups_account]['products'][] = $value;
								}
								if ($ups_account != 'default') {
									$user_account = get_post_meta($ups_account, 'hit_ups_auto_vendor_settings', true);
									$user_account = empty($user_account) ? array() : $user_account;
									if (!empty($user_account)) {
										if (!isset($vendor_settings[$ups_account])) {

											$vendor_settings[$ups_account] = $custom_settings['default'];

											if ($user_account['hit_ups_auto_site_id'] != '' && $user_account['hit_ups_auto_site_pwd'] != '' && $user_account['hit_ups_auto_acc_no'] != '') {

												$vendor_settings[$ups_account]['hit_ups_auto_site_id'] = $user_account['hit_ups_auto_site_id'];

												if ($user_account['hit_ups_auto_site_pwd'] != '') {
													$vendor_settings[$ups_account]['hit_ups_auto_site_pwd'] = $user_account['hit_ups_auto_site_pwd'];
												}

												if ($user_account['hit_ups_auto_acc_no'] != '') {
													$vendor_settings[$ups_account]['hit_ups_auto_acc_no'] = $user_account['hit_ups_auto_acc_no'];
												}

												// $vendor_settings[$ups_account]['hit_ups_auto_access_key'] = !empty($user_account['hit_ups_auto_access_key']) ? $user_account['hit_ups_auto_access_key'] : '';
												if ($user_account['hit_ups_auto_access_key'] != '') {
													$vendor_settings[$ups_account]['hit_ups_auto_access_key'] = $user_account['hit_ups_auto_access_key'];
												}
											}
											// REST credentials
											$vendor_settings[$ups_account]['hit_ups_auto_rest_site_id'] = isset($user_account['hit_ups_auto_rest_site_id']) ? $user_account['hit_ups_auto_rest_site_id'] : "";
											$vendor_settings[$ups_account]['hit_ups_auto_rest_site_pwd'] = isset($user_account['hit_ups_auto_rest_site_pwd']) ? $user_account['hit_ups_auto_rest_site_pwd'] : "";
											$vendor_settings[$ups_account]['hit_ups_auto_rest_acc_no'] = isset($user_account['hit_ups_auto_rest_acc_no']) ? $user_account['hit_ups_auto_rest_acc_no'] : "";
											$vendor_settings[$ups_account]['hit_ups_auto_rest_grant_type'] = isset($user_account['hit_ups_auto_rest_grant_type']) ? $user_account['hit_ups_auto_rest_grant_type'] : "client_credentials";
											if ($user_account['hit_ups_auto_address1'] != '' && $user_account['hit_ups_auto_city'] != '' && $user_account['hit_ups_auto_state'] != '' && $user_account['hit_ups_auto_zip'] != '' && $user_account['hit_ups_auto_country'] != '' && $user_account['hit_ups_auto_shipper_name'] != '') {

												if ($user_account['hit_ups_auto_shipper_name'] != '') {
													$vendor_settings[$ups_account]['hit_ups_auto_shipper_name'] = $user_account['hit_ups_auto_shipper_name'];
												}

												if ($user_account['hit_ups_auto_company'] != '') {
													$vendor_settings[$ups_account]['hit_ups_auto_company'] = $user_account['hit_ups_auto_company'];
												}

												if ($user_account['hit_ups_auto_mob_num'] != '') {
													$vendor_settings[$ups_account]['hit_ups_auto_mob_num'] = $user_account['hit_ups_auto_mob_num'];
												}

												if ($user_account['hit_ups_auto_email'] != '') {
													$vendor_settings[$ups_account]['hit_ups_auto_email'] = $user_account['hit_ups_auto_email'];
												}

												if ($user_account['hit_ups_auto_address1'] != '') {
													$vendor_settings[$ups_account]['hit_ups_auto_address1'] = $user_account['hit_ups_auto_address1'];
												}

												$vendor_settings[$ups_account]['hit_ups_auto_address2'] = $user_account['hit_ups_auto_address2'];

												if ($user_account['hit_ups_auto_city'] != '') {
													$vendor_settings[$ups_account]['hit_ups_auto_city'] = $user_account['hit_ups_auto_city'];
												}

												if ($user_account['hit_ups_auto_state'] != '') {
													$vendor_settings[$ups_account]['hit_ups_auto_state'] = $user_account['hit_ups_auto_state'];
												}

												if ($user_account['hit_ups_auto_zip'] != '') {
													$vendor_settings[$ups_account]['hit_ups_auto_zip'] = $user_account['hit_ups_auto_zip'];
												}

												if ($user_account['hit_ups_auto_country'] != '') {
													$vendor_settings[$ups_account]['hit_ups_auto_country'] = $user_account['hit_ups_auto_country'];
												}

												$vendor_settings[$ups_account]['hit_ups_auto_gstin'] = $user_account['hit_ups_auto_gstin'];

												$vendor_settings[$ups_account]['hit_ups_auto_con_rate'] = $user_account['hit_ups_auto_con_rate'];
												if (isset($user_account['hit_ups_auto_ven_col_type']) && isset($user_account['hit_ups_auto_ven_col_id']) && !empty($user_account['hit_ups_auto_ven_col_id'])) {
													$vendor_settings[$ups_account]['hit_ups_auto_ven_col_type'] = $user_account['hit_ups_auto_ven_col_type'];
													$vendor_settings[$ups_account]['hit_ups_auto_ven_col_id'] = $user_account['hit_ups_auto_ven_col_id'];
												}
											}

											if (isset($general_settings['hit_ups_auto_v_email']) && $general_settings['hit_ups_auto_v_email'] == 'yes') {
												$user_dat = get_userdata($ups_account);
												$vendor_settings[$ups_account]['hit_ups_auto_label_email'] = $user_dat->data->user_email;
											}


											if ($order_data['shipping']['country'] != $vendor_settings[$ups_account]['hit_ups_auto_country']) {
												$vendor_settings[$ups_account]['service_code'] = empty($service_code) ? $user_account['hit_ups_auto_def_inter'] : $service_code;
											} else {
												$vendor_settings[$ups_account]['service_code'] = empty($service_code) ? $user_account['hit_ups_auto_def_dom'] : $service_code;
											}
										}
										unset($value['product_id']);
										$vendor_settings[$ups_account]['products'][] = $value;
									}
								}
							}
						}
						if (empty($vendor_settings)) {

							$custom_settings['default']['products'] = $pack_products;
						} else {
							$custom_settings = $vendor_settings;
						}

						if (!empty($general_settings) && isset($general_settings['hit_ups_auto_integration_key']) && isset($custom_settings[$create_shipment_for])) {
							$mode = 'live';
							if (isset($general_settings['hit_ups_auto_test']) && $general_settings['hit_ups_auto_test'] == 'yes') {
								$mode = 'test';
							}

							$execution = 'manual';
							// if (isset($general_settings['hit_ups_auto_label_automation']) && $general_settings['hit_ups_auto_label_automation'] == 'yes') {
							// 	$execution = 'auto';
							// }
							$pack_type = isset($_POST['hit_ups_auto_pack_type']) ? sanitize_text_field($_POST['hit_ups_auto_pack_type']) : '02';
							$shipping_total = $order->get_shipping_total();
							$data = array();
							$data['integrated_key'] = $general_settings['hit_ups_auto_integration_key'];
							$data['order_id'] = $order_id;
							$data['exec_type'] = $execution;
							$data['mode'] = $mode;
							$data['carrier_type'] = "UPS";
							$data['ship_price'] = $order_data['shipping_total'];
							$data['acc_rates'] = ($general_settings['hit_ups_auto_account_rates'] == 'yes') ? "Y" : "N";
							$data['meta'] = array(
								"site_id" => $custom_settings[$create_shipment_for]['hit_ups_auto_site_id'],
								"password"  => $custom_settings[$create_shipment_for]['hit_ups_auto_site_pwd'],
								"accountnum" => $custom_settings[$create_shipment_for]['hit_ups_auto_acc_no'],
								"site_acess" => $custom_settings[$create_shipment_for]['hit_ups_auto_access_key'],
								"api_type" => isset($general_settings['hit_ups_auto_api_type']) ? $general_settings['hit_ups_auto_api_type'] : "SOAP",
								"rest_api_key" => isset($custom_settings[$create_shipment_for]['hit_ups_auto_rest_site_id']) ? $custom_settings[$create_shipment_for]['hit_ups_auto_rest_site_id'] : "",
								"rest_secret_key" => isset($custom_settings[$create_shipment_for]['hit_ups_auto_rest_site_pwd']) ? $custom_settings[$create_shipment_for]['hit_ups_auto_rest_site_pwd'] : "",
								"rest_acc_no" => isset($custom_settings[$create_shipment_for]['hit_ups_auto_rest_acc_no']) ? $custom_settings[$create_shipment_for]['hit_ups_auto_rest_acc_no'] : "",
								"rest_grant_type" => isset($custom_settings[$create_shipment_for]['hit_ups_auto_rest_grant_type']) ? $custom_settings[$create_shipment_for]['hit_ups_auto_rest_grant_type'] : "",
								"s_company" => $order_shipping_company,
								"s_address1" => $order_shipping_address_1,
								"s_address2" => $order_shipping_address_2,
								"s_city" => $order_shipping_city,
								"s_state" => $order_shipping_state,
								"s_postal" => $order_shipping_postcode,
								"s_country" => $order_shipping_country,
								"s_name" => $order_shipping_phone,
								"s_phone" => $order_shipping_phone,
								"s_email" => $order_shipping_email,
								"dutiable" => $general_settings['hit_ups_auto_duty_payment'],
								"insurance" => $general_settings['hit_ups_auto_insure'],
								"pack_this" => "Y",
								"residential" => 'false',
								"drop_off_type" => "REGULAR_PICKUP",
								"packing_type" => $pack_type,
								"shipping_charge" => $shipping_total,
								"products" => $pack_products, //$custom_settings[$create_shipment_for]['products'],
								"pack_algorithm" => $general_settings['hit_ups_auto_packing_type'],
								"max_weight" => $general_settings['hit_ups_auto_max_weight'],
								"wight_dim_unit" => $general_settings['hit_ups_auto_weight_unit'],
								// "total_product_weg" => $total_weg,
								"plt" => ($general_settings['hit_ups_auto_ppt'] == 'yes') ? "Y" : "N",
								"airway_bill" => ($general_settings['hit_ups_auto_aabill'] == 'yes') ? "Y" : "N",
								"sd" => ($general_settings['hit_ups_auto_sat'] == 'yes') ? "Y" : "N",
								"cod" => ($general_settings['hit_ups_auto_cod'] == 'yes') ? "Y" : "N",
								"service_code" => str_replace('ups_', '', $custom_settings[$create_shipment_for]['service_code']),
								"shipment_content" => $ship_content,
								"t_company" => $custom_settings[$create_shipment_for]['hit_ups_auto_company'],
								"t_address1" => $custom_settings[$create_shipment_for]['hit_ups_auto_address1'],
								"t_address2" => $custom_settings[$create_shipment_for]['hit_ups_auto_address2'],
								"t_city" => $custom_settings[$create_shipment_for]['hit_ups_auto_city'],
								"t_state" => $custom_settings[$create_shipment_for]['hit_ups_auto_state'],
								"t_postal" => $custom_settings[$create_shipment_for]['hit_ups_auto_zip'],
								"t_country" => $custom_settings[$create_shipment_for]['hit_ups_auto_country'],
								"gstin" => '', //$custom_settings[$create_shipment_for]['hit_ups_auto_gstin'],
								"t_name" => $custom_settings[$create_shipment_for]['hit_ups_auto_shipper_name'],
								"t_phone" => $custom_settings[$create_shipment_for]['hit_ups_auto_mob_num'],
								"t_email" => $custom_settings[$create_shipment_for]['hit_ups_auto_email'],
								"label_format" => "GIF",
								"sig_req" => "",
								"label_size" => $general_settings['hit_ups_auto_print_size'],
								"sent_email_to" => $custom_settings[$create_shipment_for]['hit_ups_auto_label_email'],
								"debug_log" => ($general_settings['hit_ups_auto_developer_rate'] == 'yes') ? 'Y' : 'N',
								"return" => "1",
								"add_shipping_invoice_flag" => ($general_settings['hit_ups_add_shipping_invoice'] == "yes") ? 'Y' : 'N',
								"del_con" => isset($general_settings['hit_ups_auto_del_con']) ? $general_settings['hit_ups_auto_del_con'] : "NONE",
								"label" => $create_shipment_for
							);

							//RETURN SHIPMENT

							foreach ($data['meta']['products'] as $key => $value) {
								$data['meta']['products'][$key]['product_name'] = preg_replace('/[^\p{L}\s+\p{N}]/u', '', $value['product_name']);
							}
							// $return_ship_url = "http://localhost/hitshipo/label_api/create_shipment.php";
							$return_ship_url = "https://app.myshipi.com/label_api/create_shipment.php";
							$wp_pst = wp_remote_post($return_ship_url, array(
								'body'        => json_encode($data),
								'timeout'     => '60',
								'redirection' => '10',
								'httpversion' => '1.0',
								'blocking'    => true,
								'headers'     => array(),
								'cookies'     => array(),
								'sslverify'   => FALSE
							));
							// $output = wp_remote_retrieve_body($wp_pst);
							$output = (is_array($wp_pst) && isset($wp_pst['body'])) ? (string)$wp_pst['body']: [];
							
							$output = json_decode($output, true);

							if ($output) {
								if (isset($output['status'])) {
									if (isset($output['status']) && $output['status'] != 'success') {
										update_option('hit_ups_auto_return_status_' . $order_id, $output['status']);
									} else if (isset($output['status']) && $output['status'] == 'success') {
										$output['user_id'] = $create_shipment_for;
										$result_arr = [];
										$val = get_option('hit_ups_auto_return_values_' . $order_id, array());
										if(!empty($val)){
											$result_arr = json_decode($val, true);
										}
										$result_arr[] = $output;

										update_option('hit_ups_auto_return_values_' . $order_id, json_encode($result_arr));
									}
								}
							} else {
								update_option('hit_ups_auto_return_status_' . $order_id, 'Site not Connected with Shipi. Contact Shipi Team.');
							}
						}
					}
				}
			}
		}
	}
	new hit_ups_auto_parent();
}
