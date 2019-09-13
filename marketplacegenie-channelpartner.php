<?php
/**
 * @package MarketplaceGenie_API
 * @version 1.1
 */
/*
	Plugin Name: MarketplaceGenie API
	Description: The Marketplace Genie API adaptor synchronizes data between WordPress/WooCommerce and the Marketplace Genie platform.
	Author: MarketplaceGenie (Pty) Ltd
	Version: 1.1
	Author URI: https://www.marketplacegenie.co.za/app/woocommerce/
 */

if (!defined( 'WPINC' )) 
{
    exit;
}
/*

todo

Options Menu

First time sync to add attributes

Status ----> Select Box (Yes/No)

Pricing ----> Select Box (Yes/No)
1) WooCommerce Pricing or Custom Takealot Pricing

Inventory ----> Select Box (Yes/No)
1. When to update inventory takelot
   1) Manual Edits (tick box)
   2) Live Sync
        a) Order Creation (tick box)
        b) Order Completion
        c) Payment Complete

Daily Sync
Pricing Only
Inventory Only

Log
Create .txt file

*/
class MarketplaceGenie 
{
    public  $version        =   '1.0.1';
    const   OPTION_GROUP    =   'marketplacegenie-option-group';
  //  const   URL	            =	'http://www.marketplacegenie.local:10002/takealot'; // takealot-api/v1';
  //  const   URL	            =	'https://channelparter.api.marketplacegenie.com/takealot';
    const   URL	            =	'https://api.marketplacegenie.com/channelpartner/takealot';
    // https://api.marketplacegenie.com/takealot/{v1}/status
    private $apiKey         =   null;

    public function init() 
    {
    }

    public static function onOrderStatusUpdate($order_id) //status, $order_id = null, $order = null)
    {
        $offerAttributes    =   [
//            'price'             =>  0,
  //          'rrp'               =>  0,
    //        'leadtime_days'     =>  0,
            'leadtime_stock'    =>  0,
        //    'status'            =>  null,
            'sku'               =>  null    
        ];

        $order = wc_get_order($order_id);
        if ($order) {
            $order->get_item_count();
            // WC_Order_Item
            foreach ($order->get_items() as $order_item) {
                $product = $order_item->get_product();

                $offerAttributes["leadtime_stock"] = $product->get_stock_quantity();
                $offerAttributes['sku']            = $product->get_sku();
                $result = self::apiUpdateOffer($offerAttributes, $product->get_sku());

            }
        }
    }

    private static function isLicensed($response)
    {
        if (isset($response->Api) && ($response->Api == "WARN")) {
            return false;
        }
        else {
            return true;
        }
    }


    private static function setProductStock($product, $offer)
    {
/**
 * @desc        Calculate the total stock held at all Takealot marketplaces.
 * 
 */
        $total  =   0;
/**
 * @desc        JSON sample of data to evaluate.
 * 
 *
        "stock_at_takealot": [
            {
                "warehouse": {
                    "warehouse_id": 1, 
                    "name": "cpt"
                }, 
                "quantity_available": 0
            },
            ...
        ], 
 *
 */
        foreach ($offer->stock_at_takealot as $detail) {
            $total += intval($detail->quantity_available);
            $product->add_meta_data("stock_at_takealot_{$detail->warehouse->name}", $detail->quantity_available);
        }

        $product->add_meta_data("stock_at_takealot", $total);
    }

    private static function updateProductStock($product, $offer)
    {
        $total  =   0;
        foreach ($offer->stock_at_takealot as $detail) {
            $total += intval($detail->quantity_available);
            $product->update_meta_data("stock_at_takealot_{$detail->warehouse->name}", $detail->quantity_available);
        }

        $product->update_meta_data("stock_at_takealot", $total);
    }

    public static function onSynchronizeProducts()
    {
        $page   =   1;
        $pages  = 0;
        $request =   self::apiOffers($page, 100);

     //   var_dump($offer);die;
        if (self::isLicensed($request)) {
            set_time_limit(500);
            // page_size
            // page_number
            // total_results
            $pages = ceil((float) $request->total_results / (float) $request->page_size);

            do {
                print("Page Size :" . $request->page_size . "<br>");
                print("Page Number :" . $request->page_number . "<br>");
                
                foreach ($request->offers as $offer) {
                    $product_id = wc_get_product_id_by_sku($offer->sku);

                    if ($product_id) {
    //  Product     exists.
                        print("Product exists.");
                        $product = wc_get_product($product_id);
                    
                        $product->set_stock_quantity( $offer->leadtime_stock );
                    
                        $product->update_meta_data("tsin_id", $offer->tsin_id);
                        $product->update_meta_data("offer_id", $offer->offer_id);
                        $product->update_meta_data("gtin", $offer->gtin);
                        $product->update_meta_data("mp_barcode", $offer->mp_barcode);
                        $product->update_meta_data("price", $offer->price, true);
                        $product->update_meta_data("rrp", $offer->rrp, true);
                        $product->update_meta_data("leadtime_days", $offer->leadtime_days, true);
                        $product->update_meta_data("takealot_url", $offer->takealot_url, true);
                    
                        self::updateProductStock($product, $offer);
                    
                        $product->save();
                    }
                    else {
    // Create product.
                        $product = new WC_Product_Simple();
                    
                        $product->set_manage_stock( true );
                        $product->set_name( $offer->title );
                        $product->set_sku( $offer->sku );
                        $product->set_price( $offer->price );
                        $product->set_regular_price( $offer->rrp );
                        $product->set_sale_price( $offer->price ); 
                        $product->set_stock_quantity( $offer->leadtime_stock );
                    
                        $product->add_meta_data("tsin_id", $offer->tsin_id, true);
                        $product->add_meta_data("offer_id", $offer->offer_id, true);
                        $product->add_meta_data("gtin", $offer->gtin, true);
                        $product->add_meta_data("mp_barcode", $offer->mp_barcode, true);
                        $product->add_meta_data("price", $offer->price, true);
                        $product->add_meta_data("rrp", $offer->rrp, true);
                        $product->add_meta_data("leadtime_days", $offer->leadtime_days, true);
                        $product->add_meta_data("takealot_url", $offer->takealot_url, true);
                    
                        self::setProductStock($product, $offer);
                    
                        $product->save();
                    }
                }
                if (++$page <= $pages) {
                    $request = self::apiOffers($page, 100);
                    if (!self::isLicensed($request)) {
                        break;
                    }
                }
            } while($page <= $pages);
        }
    }

    public static function apiEnabled()
    {
        return ('true' == esc_attr(get_option('marketplacegenie_api')));
    }

    public static function apiStatus()
    {
        $result     =   false;
        $args       =   array(
            'headers'   =>  array( 'Content-type' => 'application/json' )
        );

        $result = wp_remote_get(self::URL . '/offers/status', $args);
        
        if (is_array($result) && array_key_exists('response', $result) && (intval($result['response']['code']) == 200))
        {
            $jsonObject = json_decode($result['body'], true);

            if ((array_key_exists('result_string', $jsonObject) == true) && ($jsonObject['result_string'] == 'System OK')) {
                $result = true;
            }
            else 
            {
                $result = false;
            }
        }

        return $result;
    }

    public static function updateOffer($id)
    {
        $result = false;
        $offerAttributes    =   Array(
            'price'             =>  0,
            'rrp'               =>  0,
            'leadtime_days'     =>  0,
            'leadtime_stock'    =>  0,
            'status'            =>  null,
            'sku'               =>  null    
        );

        $product = wc_get_product($id);

        if (($product != null) && ($product != false)) {
            $offerAttributes['price']          = $product->get_price();
            $offerAttributes['rrp']            = $product->get_regular_price();
            $offerAttributes['leadtime_days']  = 4;
            $offerAttributes['leadtime_stock'] = $product->get_stock_quantity();
            $offerAttributes['status']         = $product->get_status() == 'publish' ? 'Active' : 'Inactive';
            $offerAttributes['sku']            = $product->get_sku();

            $result = self::apiUpdateOffer($offerAttributes, $product->get_sku());
        }

        return $result;
    }

    private static function apiUpdateOffer($offerAttributes, $key, $key_type = 'SKU')
    {
        $result     =   false;
        $args       =   array(
            'method'    =>  'PATCH',
            'headers'   =>  array(
                "Authorization" =>  'Bearer ' . get_option('marketplacegenie_api_key'),
                "Content-type"  =>  'application/json' ),
            'body'      =>  json_encode($offerAttributes)
        );

        $result = wp_remote_request( self::URL . "/offers/{$key_type}{$key}", $args );

        if (is_array($result) && array_key_exists('response', $result) && (intval($result['response']['code']) == 200)) {
            $result = true;
        }
        else {
            $result = false;
        }
        
        return $result;
    }

    public static function apiOffers($page = 1, $page_size = 25)
    {
        $result     =   false;
        $args       =   array(
            'method'    =>  'GET',
            'headers'   =>  array(
                "Authorization" =>  'Bearer ' . get_option('marketplacegenie_api_key'),
                "Content-type"  =>  'application/json' ),
            'body'      =>  null // json_encode([ "page" => $page, "page_size" => $size ])
        );

//        $result = wp_remote_request( self::URL . "/offers/SKUMTS7047", $args );
        $result = wp_remote_request(add_query_arg(["page" => $page, "page_size" => $page_size], self::URL . "/offers"), $args);
        $result = json_decode($result["body"]);
/*
        if (is_array($result) && array_key_exists('response', $result) && (intval($result['response']['code']) == 200)) {
            $result = true;
        }
        else {
            $result = false;
        }
  */      
        return $result;
    }
}
/**
 * @desc        Check if WooCommerce is active.
 * 
 */
if (in_array( 'woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) 
{
    function marketplacegenie_init() 
    {
        $marketplaceGenie = new MarketplaceGenie();
        $marketplaceGenie->init();
    }
    add_action( 'init', 'marketplacegenie_init' );

    function marketplacegenie_QueryVariables( $vars ) 
    {
        $vars[] = 'marketplacegenie';
        return $vars;
    }
    add_filter( 'query_vars', 'marketplacegenie_QueryVariables' );
/**
 * @desc        Administration.
 * 
 */
    function marketplacegenie_adminInit() 
    {
        register_setting( MarketplaceGenie::OPTION_GROUP, 'marketplacegenie_api', 'marketplacegenie_validateApi' );
        register_setting( MarketplaceGenie::OPTION_GROUP, 'marketplacegenie_api_key', 'marketplacegenie_validateApiKey' );

        register_setting( MarketplaceGenie::OPTION_GROUP, 'marketplacegenie_takealot_exchange_rate' );
    }
    add_action( 'admin_init', 'marketplacegenie_adminInit' );

    function marketplacegenie_validateApi($val)
    {
        return $val;
    }

    function marketplacegenie_validateApiKey($val)
    {
        return $val;
    }

    function marketplacegenie_wooCommerceMenu() 
    {
        add_submenu_page( 'woocommerce', 'MarketplaceGenie Options', 'MarketplaceGenie', 'manage_options', 'marketplacegenie', 'marketplacegenie_wooCommerceOptions' );
    }
    add_action( 'admin_menu', 'marketplacegenie_wooCommerceMenu' );

    function marketplacegenie_wooCommerceOptions() 
    {
        $tab            =   "general";
        $notification   =   null;
        
        if (isset($_REQUEST["tab"])) {
            switch ($_REQUEST["tab"]) {
                case "general":
                case "synchronization":
                case "settings":
                    $tab = $_REQUEST["tab"];
                    break;
            }
        }

        if (isset($_POST["_action"])) {
            switch ($_POST["_action"]) {
                case "general":
                    break;
                case "synchronization":
                    /* returns number of products syncd. better to return a meaningful descriptor. */
                    $n = MarketplaceGenie::onSynchronizeProducts();
                    
                    $notification = <<<EOD
<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible"> 
    <p><strong>Synchronized $n product offers from the Takealot marketplace to WooCommerce.</strong></p>
    <button type="button" class="notice-dismiss">
        <span class="screen-reader-text">Dismiss this notice.</span>
    </button>
</div>
EOD;
                    break;
                case "settings":
                    update_option('marketplacegenie_takealot_exchange_rate', floatval($_POST['MarketplacegenieTakealotExchangeRate']));
                    $notification = <<<EOD
<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible"> 
    <p><strong>Settings saved.</strong></p>
    <button type="button" class="notice-dismiss">
        <span class="screen-reader-text">Dismiss this notice.</span>
    </button>
</div>
EOD;
                    break;
            }
        }

        $marketplaceGenieApi    = esc_attr(get_option('marketplacegenie_api'));
        $marketplaceGenieApiKey = esc_attr(get_option('marketplacegenie_api_key'));
        $markup                 = null;

        if (!current_user_can( 'manage_options' ))  
        {
            wp_die( __( 'You do not have sufficient permissions to access this page.' ));
        }

        ob_start();

        settings_fields(MarketplaceGenie::OPTION_GROUP);
        do_settings_sections(MarketplaceGenie::OPTION_GROUP);
     //   submit_button();
     //   submit_button( string $text = null, string $type = 'primary', string $name = 'submit', bool $wrap = true, array|string $other_attributes = null )
        submit_button("Apply" /* text */, "primary", "submit" /* name */, true, [ "id" => "buttonGeneral" ]);
        $markup = ob_get_contents();
        ob_end_clean();

        include_once 'options-head.php';

        if ($tab == "general") {
        $markup = <<<EOD
<div class="wrap">
    <h1 class="wp-heading-inline">MarketplaceGenie</h1>
    $notification
    <form method="post" action="options.php">
                    <input type="hidden" name="_action" value="general">

        <nav class="nav-tab-wrapper">
            <a class="nav-tab" href="/wp-admin/admin.php?page=marketplacegenie&tab=general">General</a>
            <a class="nav-tab" href="/wp-admin/admin.php?page=marketplacegenie&tab=synchronization">Synchronization</a>
            <a class="nav-tab" href="/wp-admin/admin.php?page=marketplacegenie&tab=settings">Settings</a>
        </nav>
        <div id="message" class="updated woocommerce-message wc-connect">
	        <p><strong>Welcome to MarketplaceGenie</strong> – You‘re almost ready to integrate</p>
            <p class="submit">
                <a href="https://www.marketplacegenie.com" target="_blank" class="button-primary">Sign in to your Marketplace Genie account to get your API key!</a>
            </p>
        </div>
        <table class="form-table">
            <tr valign="top">
                <th scope="row">MarketplaceGenie API Key</th>
                <td><input type="text" name="marketplacegenie_api_key" value="$marketplaceGenieApiKey" /></td>
            </tr>
            <tr valign="top">
                <th scope="row">Enable API</th>
                <td>
                    <input type="checkbox" name="marketplacegenie_check" id="checkControl"/>
                    <input type="hidden" name="marketplacegenie_api" id="checkValue" value="$marketplaceGenieApi"/>
                </td>
            </tr>
        </table>
        $markup
    </form>
</div>
<script>
var o = document.getElementById('checkControl');

if ('$marketplaceGenieApi' == 'true') 
{
    o.checked = true;
} 
else
{
    o.checked = false;
}

o.addEventListener('click', function() 
{ 
    if (this.checked == true)
    {
        document.getElementById('checkValue').value = 'true';
    }
    else 
    {
        document.getElementById('checkValue').value = '';
    }
});
</script>
EOD;
        }
        elseif ($tab == "synchronization") {
            ob_start();
            submit_button("Apply" /* text */, "primary", "submit" /* name */, true, [ "id" => "buttonSynchronization" ]);
            $markup = ob_get_contents();
            ob_end_clean();
    
            $markup = <<<EOD
            <div class="wrap">
                <h1 class="wp-heading-inline">Product Synchronization</h1>
                $notification
                <div id="message" class="updated woocommerce-message wc-connect">
                    <p>Synchronize your Takealot market product portfolio with WooCommerce using <strong>Marketplace Genie</strong>.</p>
                </div>
                <form method="post" action="">
                    <input type="hidden" name="_action" value="synchronization">
                    <nav class="nav-tab-wrapper">
                        <a class="nav-tab" href="/wp-admin/admin.php?page=marketplacegenie&tab=general">General</a>
                        <a class="nav-tab" href="/wp-admin/admin.php?page=marketplacegenie&tab=synchronization">Synchronization</a>
                        <a class="nav-tab" href="/wp-admin/admin.php?page=marketplacegenie&tab=settings">Settings</a>
                    </nav>
                    <table class="form-table">
                        <tr valign="top">
                            <th scope="row">Takealot Title precedence</th>
                            <td>
                                <input type="checkbox" name="MarketplacegenieTakealotSetTitle" id="marketplacegenieTakealotSetTitle">
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">Takealot Status precedence</th>
                            <td>
                                <input type="checkbox" name="MarketplacegenieTakealotSetStatus" id="marketplacegenieTakealotSetStatus">
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">Enable Cron</th>
                            <td>
                                <input type="checkbox" name="MarketplacegenieTakealotCron" id="marketplacegenieTakealotCron">
                            </td>
                        </tr>
                    </table>
                    $markup
                </form>
            </div>
EOD;
        }
        elseif ($tab == "settings") {
            ob_start();
            submit_button("Apply" /* text */, "primary", "submit" /* name */, true, [ "id" => "buttonSettings" ]);
            $markup = ob_get_contents();
            ob_end_clean();
            $marketplacegenieTakealotExchangeRate = get_option('marketplacegenie_takealot_exchange_rate');
            $markup = <<<EOD
            <div class="wrap">
                <h1 class="wp-heading-inline">Advanced Configuration Settings</h1>
                $notification
                <div id="message" class="updated woocommerce-message wc-connect">
                    <p>Set data exchange options between your Takealot market product portfolio and WooCommerce.</p>
                </div>    
                <form method="post" action="">
                    <input type="hidden" name="_action" value="settings">
                    <nav class="nav-tab-wrapper">
                        <a class="nav-tab" href="/wp-admin/admin.php?page=marketplacegenie&tab=general">General</a>
                        <a class="nav-tab" href="/wp-admin/admin.php?page=marketplacegenie&tab=synchronization">Synchronization</a>
                        <a class="nav-tab" href="/wp-admin/admin.php?page=marketplacegenie&tab=settings">Settings</a>
                    </nav>
                    <table class="form-table">
                        <tr valign="top">
                            <th scope="row">Takealot Exchange Rate</th>
                            <td>
                                <input type="text" name="MarketplacegenieTakealotExchangeRate" id="marketplacegenieTakealotExchangeRate" value="$marketplacegenieTakealotExchangeRate">
                            </td>
                        </tr>
                    </table>
                    $markup
                </form>
            </div>
EOD;
        }
        print($markup);
	}
// 
	add_action( 'save_post', 'MarketplaceGenie::updateOffer' );
    add_action( 'woocommerce_product_quick_edit_save', 'MarketplaceGenie::updateOffer' );

    add_action ( "woocommerce_order_status_completed", "MarketplaceGenie::onOrderStatusUpdate");
    add_action ( "woocommerce_order_status_refunded",  "MarketplaceGenie::onOrderStatusUpdate");
    add_action ( "woocommerce_order_status_cancelled", "MarketplaceGenie::onOrderStatusUpdate");
    add_action ( "woocommerce_payment_complete",       "MarketplaceGenie::onOrderStatusUpdate");
    add_action ( "woocommerce_order_status_changed",   "MarketplaceGenie::onOrderStatusUpdate");
    
    add_action ( "woocommerce_product_set_stock", "MarketplaceGenie::onOrderStatusUpdate");

// The code for displaying WooCommerce Product Custom Fields
// raised in:
// wordpress/wp-content/plugins/woocommerce/includes/admin/meta-boxes/views/html-product-data-general.php
    add_action('woocommerce_product_options_general_product_data', 'marketplacegenie_woocommerceProductCustomFields'); 

    function marketplacegenie_addSettingsLink( $links ) 
    {
        $settings_link = '<a href="'.admin_url( 'admin.php?page=marketplacegenie' ).'">' . __( 'Settings' ) . '</a>';
        array_unshift( $links, $settings_link );
        return $links;
    }

    $marketplaceGeniePlugin = plugin_basename( __FILE__ );

    add_filter( "plugin_action_links_$marketplaceGeniePlugin", 'marketplacegenie_addSettingsLink' );

    add_action('admin_bar_menu', 'marketplacegenie_addToolbarItems', 100);

    function marketplacegenie_addToolbarItems($admin_bar)
    {
        if (MarketplaceGenie::apiEnabled())
        {
            $admin_bar->add_menu( array(
                'id'    => 'marketplacegenie-takealot-api-status',
                'title' => __('<span>API Status' . (MarketplaceGenie::apiStatus() ? '&nbsp;<span style="color: #00ff00;">+</span>':'<span style="color: #ff0000;">-</span></span>')),
                'href'  => '#',
                'meta'  => array(
                    'title' => __('Marketplace Genie Takealot API Status'),            
                ),
            ));
        }
    }


        // The code for displaying WooCommerce Product Custom Fields
        function marketplacegenie_woocommerceProductCustomFields() // the function has no parameters.
        {
            global $woocommerce, $post;  // we must rely on global variables to establish context. a post is used to infer a product.

            $metaAttributes = [
                "tsin_id"               => "Takealot TSIN",
                "offer_id"              => "Takealot Offer ID",
                "gtin"                  => "Takealot GTIN",
                "mp_barcode"            => "Barcode",
                "leadtime_days"         => "Leadtime Days",
                "takealot_url"          => "Takealot URL",
                "stock_at_takealot"     => "Takealot Stock",
                "stock_at_takealot_cpt" => "Stock Capetown",
                "stock_at_takealot_jhb" => "Stock Johannesburg",
                "price"                 => "Price",
                "rrp"                   => "Recommended Price"
            ];

            $product = wc_get_product($post->ID);
            foreach ($metaAttributes as $key=>$attribute) {
                if ($product->meta_exists($key)) {
                    $meta = $product->get_meta($key);
                    $markup = <<<EOD
<p class="form-field">
    <label for="_marketplacegenie_{$key}">{$attribute}</label>
    <input type="text" class="short" name="MarketplaceGenie_{$key}" id="_marketplacegenie_{$key}" value="{$meta}" readonly>
</p>
EOD;
                    print($markup); //"$attribute: $meta<br>");
                }
            }
/*
<p class="form-field _regular_price_field ">
        <label for="_regular_price">Regular price (£)</label><input type="text" class="short wc_input_price" style="" name="_regular_price" id="_regular_price" value="5499" placeholder=""> </p>
        
<div class="marketplace-suggestion-container" data-suggestion-slug="product-edit-min-max-quantities"><img src="https://woocommerce.com/wp-content/plugins/wccom-plugins//marketplace-suggestions/icons/min-max-quantities.svg" class="marketplace-suggestion-icon"><div class="marketplace-suggestion-container-content"><h4>Min/Max Quantities</h4><p>Specify minimum and maximum allowed product quantities for orders to be completed</p></div><div class="marketplace-suggestion-container-cta"><a href="https://woocommerce.com/products/min-max-quantities/?wccom-site=http%3A%2F%2Fwordpress.marketplacegenie.local&amp;wccom-back=%2Fwp-admin%2Fpost.php%3Fpost%3D9795%26%23038%3Baction%3Dedit&amp;wccom-woo-version=3.7.0&amp;utm_source=editproduct&amp;utm_campaign=marketplacesuggestions&amp;utm_medium=product" target="blank" class="button">Learn More</a><a class="suggestion-dismiss" title="Dismiss this suggestion" href="#"></a></div></div>
        */
        }
}
 ?>
