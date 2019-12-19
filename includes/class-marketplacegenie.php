<?php



class Marketplacegenie {

    public  $version            = '1.0.1';
    const   API_KEY_PREFIX      = 'Bearer ';
    const   OPTION_GROUP        = 'marketplacegenie-option-group';
    const   OPTION_GROUP_SYNC   = 'marketplacegenie-option-group-sync';
    const   OPTION_GROUP_IMPORT = 'marketplacegenie-option-group-sync-import';
    const   URL                 = 'https://api.marketplacegenie.com/channelpartner/takealot';
	protected $loader;
 	protected $plugin_name;

 	public function __construct() {
		if ( defined( 'MARKETPLACEGENIE_VERSION' ) ) {
			$this->version = MARKETPLACEGENIE_VERSION;
		}
		$this->plugin_name = 'marketplacegenie';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}
    private $apiKey             =   null;

    public function init()
    {
    }
    public static function onOrderStatusUpdate($order_id) //status, $order_id = null, $order = null)
    {
        logFile('Start order #' . $order_id->id . ' update');

        $offerAttributes    =   [
            //'price'           =>  0,
            //'rrp'             =>  0,
            //'leadtime_days'   =>  0,
            'leadtime_stock'    =>  0,
            //'status'          =>  null,
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
        if (isset($response->Api) && ($response->Api == "WARN"))
        {
            logFile('License error');
          //  self::marketplacegenie_remove_cron_import();
         //   self::marketplacegenie_remove_cron_export();

            return false;
        }
        else
        {
            return true;
        }
    }

    private static function setProductStock($product, $offer)
    {
        $total  =   0;
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
    public static function marketplacegenie_cron_import_part(){
        logFile('------Full import Page Start ----');

        return ;
        if (!Marketplacegenie::apiEnabled())
            return;
        //$startScript = microtime(); // for work pseudo cron
        //self::marketplacegenie_remove_cron_import();
        $page       = get_option('marketplacegenie_cron_import_page') ?: 1;
        $page_size  = get_option('marketplacegenie_cron_import_page_size') ?: 100;

        $request = self::apiOffers($page, $page_size);
        //logFile('-----*****-----');die;

        logFile('Start import Page ' . $page);

        $pages = ceil((float) $request->total_results / (float) $request->page_size);

        if (self::isLicensed($request))
        {
            try {
                set_time_limit(500);

                remove_action( 'save_post_product', 'Marketplacegenie::updateOffer' );
                foreach ($request->offers as $offer) {
                    $product_id = wc_get_product_id_by_sku($offer->sku);
                    //$product_id = wc_get_product_id_by_sku('Cross-100x180-Blue');
                    if ($product_id) {
                        //  Product exists.
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
                        remove_action( 'admin_init', 'marketplacegenie_sync_after_update_product', 12);

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
                        remove_action( 'admin_init', 'marketplacegenie_sync_after_update_product', 12);

                        $product->save();
                    }
                }

                unset($request->offers);

                logFile($request);
                logFile('Server API answer ');

                add_action( 'save_post_product', 'Marketplacegenie::updateOffer' );

                if (++$page <= $pages)
                {
                    //Save data for next part import
                    update_option('marketplacegenie_cron_import_page', $page);
                    update_option('marketplacegenie_cron_import_page_size', $request->page_size);

                    logFile('___________________ End import part ' . --$page . ' / ' . $pages . ' ___________________');

                }
                else
                {
                    //Cron import success
                    logFile('================== Cron import success ==================');
                    self::marketplacegenie_remove_cron_import();
                }

            } catch (Exception $e) {
                logFile('***************** Error import *****************');
                logFile($e);
                self::marketplacegenie_remove_cron_import();
            }
        }

        //$max_time = ((int)ini_get("max_execution_time") <= 60 ) ? (int)ini_get("max_execution_time") - 3 : 60; // for work pseudo cron
        //$endScript = microtime(); // for work pseudo cron
        //$sleep = (int)$max_time - ( (int)$endScript - (int)$startScript ); // for work pseudo cron

        //sleep( $sleep );
        //logFile( $sleep );
        //wp_remote_post(site_url());

    }
    //Deprecated

    public static function onSynchronizeProducts()
    {
        if (!Marketplacegenie::apiEnabled())
            return;

        $page   = 1;
        $pages  = 0;
        $count_product_sync = 0;
        $request = self::apiOffers($page, 100);
        //var_dump($offer);die;
        logFile('Start full import');
        logFile($request);
        if (self::isLicensed($request)) {

            set_time_limit(500);
            // page_size
            // page_number
            // total_results
            $pages = ceil((float) $request->total_results / (float) $request->page_size);

            do {
                logFile('Page size = ' . $request->page_size);
                logFile('Page Number = ' . $page);

                foreach ($request->offers as $offer) {
                    $product_id = wc_get_product_id_by_sku($offer->sku);

                    if ($product_id) {
                        //  Product exists.
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
                        remove_action( 'admin_init', 'marketplacegenie_sync_after_update_product', 12);

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
                        remove_action( 'admin_init', 'marketplacegenie_sync_after_update_product', 12);

                        $product->save();
                    }
                    $count_product_sync++;
                }

                if (++$page <= $pages) {
                    $request = self::apiOffers($page, 100);

                    if (!self::isLicensed($request)) {
                        break;
                    }

                }
            } while($page <= $pages);
        }
        logFile('Synchronized product = ' . $count_product_sync);
        return $count_product_sync;
    }

    public static function apiEnabled()
    {
        return ('true' == esc_attr(get_option('marketplacegenie_api')));
    }

    public static function apiStatus()
    {
        if (!Marketplacegenie::apiEnabled())
            return;

        $LicenseStatus = false;

        $apiKey     = get_option('marketplacegenie_api_key');
        $args       =   array(
            'headers'       =>  array(
                "Authorization" =>  self::API_KEY_PREFIX . $apiKey,
                'Content-type' => 'application/json' )
        );

        if (!$apiKey)
            return;

        $result = wp_remote_get(self::URL . '/status', $args);

        if (is_array($result) && array_key_exists('response', $result) && (intval($result['response']['code']) == 200))
        {
            $result = json_decode($result["body"]);

            if ($result->LicenseStatus)
                $LicenseStatus = $result->LicenseStatus;
        }

        if (!$LicenseStatus) {
            function general_admin_notice()
            {
                echo '<div class="notice notice-error is-dismissible">
                     <p>Marketplacegenie API key is not valid</p>
                 </div>';
            }
            add_action('admin_notices', 'general_admin_notice');
        }

        return $LicenseStatus;
    }
    public static function validateApiKey($apiKey = false)
    {
        if (!Marketplacegenie::apiEnabled())
            return;

        $apiKey     = $apiKey ?: get_option('marketplacegenie_api_key');
        $validate   = false;
        $args       = array(
            'headers'       =>  array(
                'Authorization' =>  self::API_KEY_PREFIX . $apiKey,
                'Content-type'  => 'application/json' )
        );

        if (!$apiKey)
            return;

        $result = wp_remote_get(self::URL . '/status', $args);

        logFile('validateApiKey. Get ' . self::URL . '/status');

        if (is_array($result) && array_key_exists('response', $result) && (intval($result['response']['code']) == 200)) {
            $result = json_decode($result["body"]);

            if ($result->LicenseStatus){
                $validate['status'] = '<span style="position: absolute; color: green">Valid</span>';

                if (property_exists($result, 'Expires'))
                {
                    if ($result->Expires)
                    {
                        $dteStart = new DateTime('now');
                        $dteEnd   = new DateTime($result->Expires);
                        $dteDiff = $dteStart->diff($dteEnd);

                        if ( $dteDiff->invert == 0)
                        {
                            $validate['expire'] = 'License key is Valid for ' . $dteDiff->format("%d") . ' Days';
                        }
                        else
                        {
                            $validate['expire'] = 'License key is Expired';
                        }
                    }
                }
                else {
                    $validate['expire'] = 'License key is Valid';
                }
            }
            else
            {
                $validate['status'] = '<span style="position: absolute; color: red">Not Valid</span>';
                $validate['expire'] = 'License key is Incorrect';
            }
        }
        else
        {
            $validate['status'] = '<span style="position: absolute; color: red">Not Valid</span>';
            $validate['expire'] = 'License key is Incorrect';
        }

        return $validate;
    }

    public static function updateOffer($id)
    {

        if (!Marketplacegenie::apiEnabled()){    return; }


        $marketplacegenie_takealot_cron         = esc_attr(get_option('marketplacegenie_takealot_cron'));
        $marketplacegenie_sync_manual_price     = esc_attr(get_option('marketplacegenie_sync_manual_price'));
        $marketplacegenie_sync_manual_inventory = esc_attr(get_option('marketplacegenie_sync_manual_inventory'));
        $TERate   = esc_attr(get_option('marketplacegenie_takealot_exchange_rate'));
        $TFRate   = esc_attr(get_option('marketplacegenie_takealot_fixed_rate'));
        $Tagexport  = esc_attr(get_option('marketplacegenie_takealot_tag_export'));
        if(is_numeric ($TERate))
        {
           if($TERate == 0){$TERate  =1; }
        }else
        {
            $TERate= 1;
        }
        if(is_numeric ($TFRate))
        {
            if($TFRate == 0){$TFRate  =0; }
        }else
        {
            $TFRate= 0;
        }
        $result = false;
        $offerAttributes = array();
        $product = wc_get_product($id);

        if (($product != null) && ($product != false))
        {
            $offerAttributes = array();
            if ($marketplacegenie_takealot_cron || $marketplacegenie_sync_manual_price)
            {

               $offerAttributes['price']          = ceil(($product->get_price()  + $TFRate )* $TERate);

               $offerAttributes['rrp']            = ceil(($product->get_regular_price() + $TFRate ) * $TERate);



            }
            if ($marketplacegenie_takealot_cron || $marketplacegenie_sync_manual_inventory)
            {
                $offerAttributes['leadtime_days']  = 4;
                $offerAttributes['leadtime_stock'] = $product->get_stock_quantity();
                $offerAttributes['status']         = $product->get_status() == 'publish' ? 'Active' : 'Inactive';
                $offerAttributes['sku']            = $product->get_sku();
            }
            if (!$offerAttributes)
                return;

            $result = self::apiUpdateOffer($offerAttributes, $product->get_sku());
        }

        return $result;
    }


    public static function updateOffers()
    {
        if (!Marketplacegenie::apiEnabled()) {
            return;
        }

        logFile('================== Cron export start ==================');
        //$startScript = microtime(); // for work pseudo cron

        $page = get_option('marketplacegenie_cron_export_page') ?: 1;
        $page_size = 50;
        $count_pages = wp_count_posts('product');
        $total_pages = $count_pages->publish;
        $pages = ceil((float)$total_pages / (float)$page_size);


        try {

            $posts = get_posts(array(
                'numberposts' => $page_size,
                'offset' => ($page - 1) * $page_size,
                'orderby' => 'date',
                'order' => 'ASC',
                'post_type' => 'product',
                'suppress_filters' => true,
            ));

            if ($posts && $page <= $pages) {
                logFile('------------------ Export offers. Page ' . $page . ' / ' . $pages . ' ------------------');

                foreach ($posts as $post) {
                    $product = wc_get_product($post->ID);


                    $offerAttributes = array();
                    $offerAttributes['price'] = $product->get_price();
                    $offerAttributes['rrp'] = $product->get_regular_price();
                    $offerAttributes['leadtime_days'] = 4;
                    $offerAttributes['leadtime_stock'] = $product->get_stock_quantity();
                    $offerAttributes['status'] = $product->get_status() == 'publish' ? 'Active' : 'Inactive';
                    $offerAttributes['sku'] = $product->get_sku();
                    //logFile('-------'.$offerAttributes['sku']);continue;

                            if ($offerAttributes['sku']) {
                                $result = self::apiUpdateOffer($offerAttributes, $product->get_sku());
                                if (!$result) {
                                    logFile('Update product with SKU = ' . $product->get_sku() . ' is failed');

                                }
                            } else {
                                logFile('Update product "' . $product->get_title() . '" id = ' . $product->get_id() . ' no have SKU');
                            }


                        if ($offerAttributes['sku']) {
                            $result = self::apiUpdateOffer($offerAttributes, $product->get_sku());
                            if (!$result) {
                                logFile('Update product with SKU = ' . $product->get_sku() . ' is failed');
                            }
                        } else {
                            logFile('Update product "' . $product->get_title() . '" id = ' . $product->get_id() . ' no have SKU');
                        }


                }

                //Save data for next part export
                update_option('marketplacegenie_cron_export_page', ++$page);
                update_option('marketplacegenie_cron_export_page_size', $page_size);

                $cron_export_page = get_option('marketplacegenie_cron_export_page');

                logFile('___________________ End export part ' . --$page . ' / ' . $pages . ' ___________________');

                logFile('Update ' . count($posts) . ' products');

                logFile('Updated marketplacegenie_cron_export_page -- ' . $cron_export_page . ' / ' . $pages);
            } else {
                //Cron export success

                self::marketplacegenie_remove_cron_export();
            }
        } catch (Exception $e) {
            logFile('***************** Error export *****************');
        }

        //$max_time = (ini_get("max_execution_time") <= 120 ) ? ini_get("max_execution_time") - 3 : 120; // for work pseudo cron
        //$endScript = microtime(); // for work pseudo cron
        //$sleep = (int)$max_time - ( $endScript - $startScript ); // for work pseudo cron

        //sleep( $sleep );
        //logFile( $sleep );
        //logFile( $endScript - $startScript);
        //logFile('___________________ End Export part ' . --$page .' ___________________');
        logFile('================== Cron export success proc end ==================');
        wp_remote_post(site_url());
    }

    private static function apiUpdateOffer($offerAttributes, $key, $key_type = 'SKU')
    {
        if (!Marketplacegenie::apiEnabled())
        { return;  }
        $MarketplacegenieTakealotTagExport = get_option('marketplacegenie_takealot_tag_export');
        //$key                    = 'AFT0655'; //test
        //$offerAttributes['sku'] = $key;      //test
        logFile(' Marketplacegenie Takealot Tag Export  = ' . $MarketplacegenieTakealotTagExport);
        $result     =   false;
        $args       =   array(
            'method'    =>  'PATCH',
            'headers'   =>  array(
                "Authorization" =>  self::API_KEY_PREFIX . get_option('marketplacegenie_api_key'),
                "Content-type"  =>  'application/json' ),
            'body'      =>  json_encode($offerAttributes)
        );
        $result = false;
        $xid = wc_get_product_id_by_sku($key);
        $product = wc_get_product($xid);
        $current_tags = get_the_terms($xid, 'product_tag');
        if ($MarketplacegenieTakealotTagExport = "on") {
            logFile(' Marketplacegenie Takealot Tag Export Exists = ' . in_array("takealot", $current_tags));
            if (in_array("takealot", $current_tags)) {
                $result = wp_remote_request( self::URL . "/offers/{$key_type}{$key}", $args );
                // logFile('apiUpdateOffer. Get ' . self::URL . "/offers/{$key_type}{$key}");
                // if (!is_wp_error($result)) {
                if (is_array($result) && array_key_exists('response', $result) && (intval($result['response']['code']) == 200)) {


                    $body = wp_remote_retrieve_body($result);
                    $data = json_decode($body, true);
                    $status = $data['status'];
                    $sku = $key;
                    if($status =="Success")
                    {
                        $offer = $data['offer'];
                        $actions = $data['actions'];

                        $message = "apiUpdateOffer   " . $status . "   ". $sku   .  "  ". implode(" ,  ",$actions) ;
                    }
                    else{
                        $message = "apiUpdateOffer   " . $status . "   ". $sku   .  "  Failed" ;
                    }

                    logFile($message);
                    $result = true;
                }
                else {
                    $result = false;
                    logFile('Update failed.. SKU = ' . $key);
                }


            }
        } else {
            $result = wp_remote_request( self::URL . "/offers/{$key_type}{$key}", $args );
            // logFile('apiUpdateOffer. Get ' . self::URL . "/offers/{$key_type}{$key}");
            // if (!is_wp_error($result)) {
            if (is_array($result) && array_key_exists('response', $result) && (intval($result['response']['code']) == 200)) {


                $body = wp_remote_retrieve_body($result);
                $data = json_decode($body, true);
                $status = $data['status'];
                $sku = $key;
                if($status =="Success")
                {
                    $offer = $data['offer'];
                    $actions = $data['actions'];

                    $message = "apiUpdateOffer   " . $status . "   ". $sku   .  "  ". implode(" ,  ",$actions) ;
                }
                else{
                    $message = "apiUpdateOffer   " . $status . "   ". $sku   .  "  Failed" ;
                }

                logFile($message);
                $result = true;
            }
            else {
                $result = false;
                logFile('Update failed.. SKU = ' . $key);
            }
        }



        // }
        return $result;
    }

    public static function apiOffers($page = 1, $page_size = 100)
    {

        if (!Marketplacegenie::apiEnabled())
        {return; }

        $result     =   false;
        $args       =   array(
            'method'    =>  'GET',
            'timeout'     => 4,
            'body'      =>  null,
            'headers'   =>  array(
                "Authorization" =>  self::API_KEY_PREFIX . get_option('marketplacegenie_api_key'),
                "Content-type"  =>  'application/json' ),
            //'body'      =>  null // json_encode([ "page" => $page, "page_size" => $size ])
        );

        logFile('Get offers URL ' . add_query_arg(["page" => $page, "page_size" => $page_size], self::URL . "/offers"));
        //$result = wp_remote_request( self::URL . "/offers/AFT0655", $args );
        $result = wp_remote_request(add_query_arg(["page" => $page, "page_size" => $page_size], self::URL . "/offers"), $args);
        $result = json_decode($result["body"]);

        return $result;
    }
    public static function marketplacegenie_remove_cron_import()
    {
        update_option('marketplacegenie_last_full_import', (int)strtotime('now'));
        update_option('marketplacegenie_cron_import_page', '');
        update_option('marketplacegenie_cron_import_page_size', '');
        update_option('marketplacegenie_cron_import_enable', 0);

        wp_clear_scheduled_hook( 'marketplacegenie_do_cron_in_progress_import_hook' );
    }

    public static function marketplacegenie_remove_cron_export()
    {
        update_option('marketplacegenie_last_full_export', (int)strtotime('now'));
        update_option('marketplacegenie_cron_export_page_size', '');
        update_option('marketplacegenie_cron_export_page', '');
        update_option('marketplacegenie_cron_export_enable', 0);

        update_option('marketplacegenie_manual_export_on', '');
        update_option('marketplacegenie_takealot_cron_export', '');
        update_option('marketplacegenie_export_time', '');

        wp_clear_scheduled_hook( 'marketplacegenie_do_cron_in_progress_export_hook' );
    }

	private function load_dependencies() {

         require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-marketplacegenie-loader.php';
         require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-marketplacegenie-i18n.php';
         require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-marketplacegenie-admin.php';
         require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-marketplacegenie-public.php';

		$this->loader = new Marketplacegenie_Loader();

	}


	private function set_locale() {

		$plugin_i18n = new Marketplacegenie_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	private function define_admin_hooks() {

		$plugin_admin = new Marketplacegenie_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

	}


	private function define_public_hooks() {

		$plugin_public = new Marketplacegenie_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	}


	public function run() {
		$this->loader->run();
	}

	public function get_plugin_name() {
		return $this->plugin_name;
	}


	public function get_loader() {
		return $this->loader;
	}


	public function get_version() {
		return $this->version;
	}

}
