
=== Automated UPS Shipping for WooCommerce – HPOS supported ===
Contributors: aarsiv
Tags: UPS, UPS Shipping, UPS Shipping Method, UPS WooCommerce, UPS Plugin
Requires at least: 4.0.1
Tested up to: 6.7
Requires PHP: 5.6
Stable tag: 4.3.3
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
= 4.3.3 =
	> Wordpress version tested

= 4.3.2=
	> Bug Fixed
