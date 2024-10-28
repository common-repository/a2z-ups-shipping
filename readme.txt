
=== Automated UPS Shipping for WooCommerce – HPOS supported ===
Contributors: aarsiv
Tags: UPS, UPS Shipping, UPS Shipping Method, UPS WooCommerce, UPS Plugin
Requires at least: 4.0.1
Tested up to: 6.5
Requires PHP: 5.6
Stable tag: 4.3.2
License: GPLv3 or later License
URI: http://www.gnu.org/licenses/gpl-3.0.html

UPS plugin: Real-time rates, label printing, auto tracking emails, previews on product pages, and more. Seamless integration.

== Description ==

Discover the ultimate UPS shipping solution for your WooCommerce store with our powerful UPS Shipping plugin. Seamlessly integrate UPS services to provide your customers with real-time shipping rates, streamline label printing, automate tracking number email generation, and offer shipping rate previews directly on product pages.

With the most popular UPS Shipping Plugin for WooCommerce, you ensure your customers always experience accurate shipping costs. Our premium features include label printing and a custom boxing algorithm, guaranteeing precise deliveries while saving you valuable time to focus on your business.

= Our plugin offers =
1. Real-time UPS shipping rates displayed effortlessly on product pages, no login required.
2. Integration directly with UPS systems for up-to-date shipping rates based on your UPS account.
3. Premium label printing directly from the backoffice order page, with automatic tracking number email generation (Premium).
4. Support for dimensional weight and negotiated rates, ensuring fair and accurate pricing.
5. Flexible shipping options, including single-box or multiple-box configurations based on product dimensions (Premium).
6. Free shipping settings by Product, Category, Manufacturer, or Supplier.
7. Compatibility with all UPS services and package types, with customizable shipping options per Zone.
8. Individual shipping method settings for Free Shipping Limit, Additional Fee, and Insurance.
9. Smart caching system for maximum speed optimization.
10. Easy testing mode toggle within the module configuration.

= About [Shipi](https://myshipi.com) =

We are Web Development Company in France. We are planning for High Quality WordPress, Woocommerce, Edd Downloads Plugins. We are launched on 4th Nov 2018. 

= What a2Z Plugins Group Tell to Customers? =

> "Make Your Shop With Smile"

Useful filters:

1) Customs Rates

>function ups_shipping_cost_conversion($ship_cost, $pack_weight = 0, $to_country = "", $rate_code = ""){
>    $sample_flat_rates = array("GB"=>array( //Use ISO 3166-1 alpha-2 as country code
>								"weight_from" => 10,
>								"weight_upto" => 30,
>								"rate" => 2000,
>								"rate_code" => "ups_12", //You can add UPS service type and use it based on your's need. Get this from our plugin's configuration (services tab).
>							),
>								"US"=>array(
>								"weight_from" => 1,
>								"weight_upto" => 30,
>								"rate" => 5000,
>								),
>							);
>
>		if(!empty($to_country) && !empty($sample_flat_rates)){
>			if(isset($sample_flat_rates[$to_country]) && ($pack_weight >= $sample_flat_rates[$to_country]['weight_from']) && ($pack_weight <= $sample_flat_rates[$to_country]['weight_upto'])){
>				$flat_rate = $sample_flat_rates[$to_country]['rate'];
>				return $flat_rate;
>			}else{
>				return $ship_cost;
>			}
>		}else{
>				return $ship_cost;
>		}
>
>    }
>    add_filter('hitstacks_ups_shipping_cost_conversion','ups_shipping_cost_conversion',10,4);

(Note: Flat rate filter example code will set flat rate for all UPS carriers. Have to add code to check and alter rate for specific carrier. While copy paste the code from worpress plugin page may throw error "Undefined constant". It can be fixed by replacing backtick (`) to apostrophe (') )

2) To Sort the rates from Lowest to Highest

> add_filter( 'woocommerce_package_rates' , 'hitshipo_sort_shipping_methods', 10, 2 );
> function hitshipo_sort_shipping_methods( $rates, $package ) {
>   if ( empty( $rates ) ) return;
>       if ( ! is_array( $rates ) ) return;
> uasort( $rates, function ( $a, $b ) { 
>   if ( $a == $b ) return 0;
>       return ( $a->cost < $b->cost ) ? -1 : 1; 
>  } );
>       return $rates;
> }


== Screenshots ==
1. Configuration - UPS Details.
2. Configuration - UPS Shipper Address.
3. Configuration - UPS Rate Section.
4. Configuration - UPS Available Services.
5. Output - UPS Shipping Rates in Shop.
6. Output - My Account Page Shipping Section.
5. Output - Edit Order Page Shipping Section.


== Changelog ==
= 4.3.2=
*Release Date - 1 May  2024*
	> Bug Fixed

= 4.3.1=
*Release Date - 30 April  2024*
	> Bug Fixed

= 4.3.0=
*Release Date - 29 April  2024*
	> Minor improvements

= 4.2.1=
*Release Date - 06 Feb 2024*
	> Minor improvements

= 4.2.0=
*Release Date - 18 Dec 2023*
	> Added HPOS support

= 4.1.1=
*Release Date - 28 Nov 2023*
	> Added delivery confirmation support.

= 4.1.0=
*Release Date - 15 Nov 2023*
	> Added Vendor collection support.

= 4.0.7=
*Release Date - 03 Nov 2023*
	> Fixed total value not sending with weight based pack.

= 4.0.6=
*Release Date - 15 Sep 2023*
	> Minor debug improvement

= 4.0.5=
*Release Date - 15 Sep 2023*
	> Minor bug fix

= 4.0.4=
*Release Date - 07 Sep 2023*
	> Fixed negotiated rates issue with OAuth API

= 4.0.3=
*Release Date - 04 Sep 2023*
	> Added additional customer classification options

= 4.0.2=
*Release Date - 01 Sep 2023*
	> Minor improvements

= 4.0.1=
*Release Date - 09 Aug 2023*
	> Fixed creating label automatically always

= 4.0.0=
*Release Date - 24 Jul 2023*
	> Added OAUTH API support

= 3.6.8=
*Release Date - 23 Jun 2023*
	> Reduced configurations on initial setup

= 3.6.7=
*Release Date - 15 May 2023*
	> Minor Improvements

= 3.6.6=
*Release Date - 10 May 2023*
	> Minor Improvements

= 3.6.5=
*Release Date - 20 March 2023*
	> minor bugfix

= 3.6.4=
*Release Date - 09 March 2023*
	> minor bugfix

= 3.6.3=
*Release Date - 09 March 2023*
	> minor bugfix

= 3.6.2=
*Release Date - 22 February 2023*
	> minor bugfix

= 3.6.1=
*Release Date - 20 February 2023*
	> Updated meeting link

= 3.6.0=
*Release Date - 15 February 2023*
	> Added option to link with hitshipo using integration key

= 3.5.10=
*Release Date - 25 january 2023*
	>minor improvements

= 3.5.9=
*Release Date - 28 December 2022*
	>minor improvements

= 3.5.8=
*Release Date - 17 November 2022*
	>update tested version

= 3.5.7=
*Release Date - 01 November 2022*
	>minor improvements

= 3.5.6=
*Release Date - 28 October 2022*
	>minor bug fix
	
= 3.5.5=
*Release Date - 28 September2022*
	>minor improvements

= 3.5.4 =
*Release Date - 09 September2022*
	>minor improvements

= 3.5.3 =
*Release Date - 06 September2022*
	>minor bug fix

= 3.5.2 =
*Release Date - 25 August 2022*
	>Security Improvements & Minor Fixes

= 3.5.1 =
*Release Date - 19 August 2022*
	>Security Improvements & Minor Fixes

= 3.5.0 =
*Release Date - 19 August 2022*
	>Security Updates

= 3.4.5 =
*Release Date - 13 August 2022*
	>minor bug fix

= 3.4.4 =
*Release Date - 12 August 2022*
	>minor bug fix

= 3.4.3 =
*Release Date - 12 August 2022*
	>Add new button,plugin name change

= 3.4.2
*Release Date - 01 August 2022*
	>minor bug fix and intergration field add
= 3.4.1
*Release Date - 21 July 2022*
	> add new button

= 3.4.0
*Release Date - 19 July 2022*
	> SHIPPING LABEL AUTOMATION

= 3.3.1
*Release Date - 10 June 2022*
	> minor bug fix

= 3.3.0
*Release Date - 06 June 2022*
	> added new password field


= 3.2.1
*Release Date - 15 July 2021*
	> Wordpress Version Updated

= 3.2.0
*Release Date - 12 july 2021*
	> Return Shipment labels Added. Show shipping price in invoice

= 3.1.1
*Release Date - 05 June 2021*
	> Minor bug fix

= 3.1.0
*Release Date - 08 may 2021*
	> Added Customer Classification For US

= 3.0.5
*Release Date - 06 may 2021*
	> Minor Bug Fixes

= 3.0.4
*Release Date - 13 Apr 2021*
	> Minor Email label value Bug Fix
	
= 3.0.3
*Release Date - 31 Mar 2021*
	> Added Save & Start 60-day trail button

= 3.0.2
*Release Date - 27 Mar 2021*
	> Minor Bug Fix

= 3.0.1
*Release Date - 24 Mar 2021*
	> Minor Bug Fix

= 3.0.0
*Release Date - 20 Mar 2021*
	> New UI for UPS

= 2.3.9
*Release Date - 19 Jan 2021*
	> Bugfixes.

= 2.3.8
*Release Date - 23 December 2020*
	> Fixed order data not sending to Shipo while changing carrier name.

= 2.3.7
*Release Date - 19 December 2020*
	> Added surcharge flag to rate filter.

= 2.3.6
*Release Date - 12 December 2020*
	> Minor bug fixes.

= 2.3.5
*Release Date - 28 November 2020*
	> Added custom rates filter.

= 2.3.4
*Release Date - 27 November 2020*
	> Minor bug Fixes.

= 2.3.3
*Release Date - 24 November 2020*
	> Minor bug Fixes.

= 2.3.2
*Release Date - 28 October 2020*
	> Minor bug Fixes.

= 2.3.1
*Release Date - 27 October 2020*
	> Minor bug Fixes.

= 2.3.0
*Release Date - 17 October 2020*
	> Exclude Country for Rates.

= 2.2.8
*Release Date - 01 Aug 2020*
	> fixes some minor bug.

= 2.2.7
*Release Date - 22 Jul 2020*
	> fixes some minor bug.

= 2.2.6
*Release Date - 16 Jul 2020*
	> fixes for multivendor.

= 2.2.5
*Release Date - 16 Jul 2020*
	> includes Bugfixes.

= 2.2.4
*Release Date - 11 Jul 2020*
	> includes Bugfixes.

= 2.2.3
*Release Date - 4 Jul 2020*
	> includes Bugfixes.

= 2.2.2
*Release Date - 3 Jul 2020*
	> includes Bugfixes.

= 2.2.1
*Release Date - 28 Jun 2020*
	> variable product weight issue fixed.

= 2.2.0
*Release Date - 17 Jun 2020*
	> Added Feature Sending tracking number to Customer.

= 2.1.2
*Release Date - 13 Jun 2020*
	> Bugfixes.

= 2.1.1
*Release Date - 13 Jun 2020*
	> Bugfixes.

= 2.1.0
*Release Date - 5 Jun 2020*
	> Added tracking in front office.

= 2.0.6 =
*Release Date - 2 Jun 2020*
	> Multi vendor released.
	
= 2.0.5 =
*Release Date - 9 May 2020*
	> sent shiping price to shipo & acc_rates

= 2.0.4 =
*Release Date - 22 April 2020*
	> sent shiping price to shipo

= 2.0.3 =
*Release Date - 21 April 2020*
	> Minor Bug fixes

= 2.0.2 =
*Release Date - 17 April 2020*
	> changed weight and dim conversion

= 2.0.1 =
*Release Date - 11 March 2020*
	> changed service pack type default set to customer supplied pack

= 2.0.0 =
*Release Date - 07 March 2020*
	> Initial Version compatibility with Shipi

= 1.0.0 =
*Release Date - 11 November 2018*
	> Initial Version
