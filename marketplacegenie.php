<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.marketplacegenie.co.za/app/woocommerce/
 * @since             2.0.0
 * @package           Marketplacegenie
 *
 * @wordpress-plugin
 * Plugin Name:       Marketplacegenie API
 * Plugin URI:        https://www.marketplacegenie.co.za/app/woocommerce/
 * Description:       This plugin will seamlessly allow you to integrate your WooCommerce store with the Marketplace Genie platform and to Takealot Reseller Portal.
 * Version:           2.0.6
 * Author:            Marketplacegenie (Pty) Ltd
 * Author URI:        https://www.marketplacegenie.co.za/app/woocommerce/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       marketplacegenie
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
//
  // date_default_timezone_set('	Africa/Harare');
if( wp_doing_ajax() ){

    add_action('wp_ajax_check_api_key', 'check_api_key');
    function check_api_key()
    {
        if (isset($_POST['action']) && $_POST['action'] == 'check_api_key' )
        {
            echo json_encode(Marketplacegenie::validateApiKey($_POST['api_key']));
        }
        die();
    }
}

require_once plugin_dir_path( __FILE__ ) . 'includes/class-marketplacegenie-uninstall.php';
$marketplacegenie_import_time=get_option('marketplacegenie_import_time');

$arrayimporttime = (date_parse_from_format("Y.n.j H:iP", $marketplacegenie_import_time));
$finalimporttime = $arrayimporttime['hour'];

$marketplacegenie_export_time=get_option('marketplacegenie_export_time');

$arrayexportime = (date_parse_from_format("Y.n.j H:iP", $marketplacegenie_export_time));
$finalexporttime = $arrayexportime['hour'];

if(!empty($marketplacegenie_import_time))
{
    //echo "--------------import_time-----------";
    $import_time=$finalimporttime.":32:00";
    //echo $import_time;
}else{
    $import_time= $marketplacegenie_import_time;
}


if(!empty($marketplacegenie_export_time))
{
    //echo "--------------export_time-----------";
    $export_time=$finalexporttime.":13:00";
    //echo $export_time;
}else{

    $export_time= $marketplacegenie_export_time;
}
/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'MARKETPLACEGENIE_VERSION', '2.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-marketplacegenie-activator.php
 */
function activate_marketplacegenie() {



	require_once plugin_dir_path( __FILE__ ) . 'includes/class-marketplacegenie-activator.php';

	Marketplacegenie_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-marketplacegenie-deactivator.php
 */
function deactivate_marketplacegenie() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-marketplacegenie-deactivator.php';
	Marketplacegenie_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_marketplacegenie' );



/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-marketplacegenie.php';
require plugin_dir_path( __FILE__ ) . 'includes/class-marketplacegenie-install.php';
/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_marketplacegenie() {

	$plugin = new Marketplacegenie();
	$plugin->run();

}
run_marketplacegenie();

if (in_array( 'woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins'))))
{
    function marketplacegenie_init()
    {
        $marketplaceGenie = new Marketplacegenie();
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
        register_setting( Marketplacegenie::OPTION_GROUP,       'marketplacegenie_api',                     'marketplacegenie_validateApi' );
        register_setting( Marketplacegenie::OPTION_GROUP,       'marketplacegenie_api_key',                 'marketplacegenie_validateApiKey' );
        register_setting( Marketplacegenie::OPTION_GROUP_SYNC,  'marketplacegenie_sync_live',               'marketplacegenie_sync_live' );
        register_setting( Marketplacegenie::OPTION_GROUP_SYNC,  'marketplacegenie_sync_manual_inventory',   'marketplacegenie_sync_manual_inventory' );
        register_setting( Marketplacegenie::OPTION_GROUP_SYNC,  'marketplacegenie_takealot_cron_export',    'marketplacegenie_takealot_cron_export' );
        register_setting( Marketplacegenie::OPTION_GROUP_SYNC,  'marketplacegenie_export_time',             'marketplacegenie_export_time' );

        register_setting( Marketplacegenie::OPTION_GROUP_SYNC,  'marketplacegenie_sync_manual_price',       'marketplacegenie_sync_manual_price' );

        register_setting( Marketplacegenie::OPTION_GROUP_IMPORT,'marketplacegenie_takealot_cron_import',    'marketplacegenie_takealot_cron_import' );

        register_setting( Marketplacegenie::OPTION_GROUP, 'marketplacegenie_takealot_exchange_rate' );
        register_setting( Marketplacegenie::OPTION_GROUP, 'marketplacegenie_takealot_fixed_rate' );
        register_setting( Marketplacegenie::OPTION_GROUP, 'marketplacegenie_takealot_tag_export' );

    }
    add_action( 'init', 'marketplacegenie_adminInit' );

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

        add_menu_page( 'MarketplaceGenie', 'MarketplaceGenie',  'manage_options','marketplacegenie', 'marketplacegenie_wooCommerceOptions', plugins_url( 'marketplacegenie-api/img/takealot-16x16.png' ), 50 );
     //   add_submenu_page('marketplacegenie', 'Listings', 'Listings', 'manage_options', 'my-menu' );
       // add_submenu_page('marketplacegenie', 'Orders', 'Orders', 'manage_options', 'my-menu2' );
      //  add_submenu_page('marketplacegenie', 'Settings', 'Settings', 'manage_options', 'marketplacegenie' );
        add_submenu_page('marketplacegenie', 'Settings', 'Settings', 'manage_options',  'marketplacegenie' );
        add_submenu_page('marketplacegenie', 'Logs', 'Logs', 'manage_options',  'admin.php?page=marketplacegenie&tab=log' );

    }
    add_action( 'admin_menu', 'marketplacegenie_wooCommerceMenu' );

    add_action('admin_menu', 'downloadmanual');
    function downloadmanual() {
        global $submenu;
        $submenu['marketplacegenie'][] = array(
            '<div id="downloadmanual">Download Manual</div>', // <-- trick
            'manage_options',
            site_url( '/wp-content/plugins/marketplacegenie-api/public/takealot.pdf' )
        );
    }

    add_action( 'admin_footer', 'make_downloadmanual_blank' );
    function make_downloadmanual_blank()
    {
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                $('#downloadmanual').parent().attr('target','_blank');
            });
        </script>
        <?php
    }
    function marketplacegenie_wooCommerceOptions()
    {
        $marketplaceGenieApiKey  = esc_attr(get_option('marketplacegenie_api_key'));
        echo ' <style>
body {font-family: Arial, Helvetica, sans-serif;}

/* The Modal (background) */
.modal {
  display: none; /* Hidden by default */
  position: fixed; /* Stay in place */
  z-index: 1; /* Sit on top */
  padding-top: 100px; /* Location of the box */
  left: 0;
  top: 0;
  width: 100%; /* Full width */
  height: 100%; /* Full height */
  overflow: auto; /* Enable scroll if needed */
  background-color: rgb(0,0,0); /* Fallback color */
  background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
}

/* Modal Content */
.modal-content {
  background-color: #fefefe;
  margin: auto;
  padding: 20px;
  border: 1px solid #888;
  width: 50%;
}

/* The Close Button */
.close {
  color: #aaaaaa;
  float: right;
  font-size: 28px;
  font-weight: bold;
}

.close:hover,
.close:focus {
  color: #000;
  text-decoration: none;
  cursor: pointer;
}
.switch {
  position: relative;
  display: inline-block;
  width: 60px;
  height: 34px;
}

.switch input { 
  opacity: 0;
  width: 0;
  height: 0;
}

.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  -webkit-transition: .4s;
  transition: .4s;
}

.slider:before {
  position: absolute;
  content: "";
  height: 26px;
  width: 26px;
  left: 4px;
  bottom: 4px;
  background-color: white;
  -webkit-transition: .4s;
  transition: .4s;
}

input:checked + .slider {
  background-color: #2196F3;
}

input:focus + .slider {
  box-shadow: 0 0 1px #2196F3;
}

input:checked + .slider:before {
  -webkit-transform: translateX(26px);
  -ms-transform: translateX(26px);
  transform: translateX(26px);
}

/* Rounded sliders */
.slider.round {
  border-radius: 34px;
}

.slider.round:before {
  border-radius: 50%;
}
</style>
 

<!-- The Modal -->
<div id="myModal" class="modal">

  <!-- Modal content -->
  <div class="modal-content">
    <div id="wf-onboarding-fresh-install" class="wf-onboarding-modal">
     <form method="post" action="options.php" id ="keyupdateform">
                    <input type="hidden" name="_action" value="setkey">
            <div id="wf-onboarding-fresh-install-1" class="wf-onboarding-modal-content">
                <div class="wf-onboarding-logo" style="text-align: center"><img src="/wp-content/plugins/marketplacegenie-api/img/logo.png" width= "255px" alt="Marketplacegenie  "></div>
                <h3>Congratulations! <br><br> You have successfully installed the Marketplace Genie WooCommerce Plugin</h3>
                <h4>Please visit <a target="_blank" href="https://www.marketplacegenie.com/subscribe/marketplace/takealot/woocommerce">marketplacegenie.com/register</a> to obtain  your API key</h4>
                <div id="wf-onboarding-license">
                 
                 <input type="text" required placeholder="Enter API Key" id="registrationkeymodal" name="marketplacegenie_api_key"  value="'.$marketplaceGenieApiKey.'" /></div>
                <div id="wf-onboarding-footer">
                    <ul>
                        <li>
                            <input type="checkbox" class="wf-option-checkbox wf-small" id="remember"> <label for="wf-onboarding-agree">By checking this box, I agree to the Marketplacegenie <a href="https://www.marketplacegenie.com/legal/terms-and-conditions" target="_blank" rel="noopener noreferrer">terms</a> and <a href="https://www.marketplacegenie.com/legal/terms-and-conditions" target="_blank" rel="noopener noreferrer">privacy policy</a></label>
                              </li>
                        <li><a onclick="validate() ; return false;;" href="" class="wf-onboarding-btn wf-onboarding-btn-primary wf-disabled" id="wf-onboarding-continue">Continue</a></li>
                    </ul>
                </div>
              
            </div>
            
          
        </div>
         </form>
  </div>

</div>

<script>
function updatekey()
{
var tosend = document.getElementById("registrationkeymodal").value;
var url = "admin.php?page=marketplacegenie&apikey="+ tosend;
  console.error(url);
window.location.href = url;

}
 function validate() {
        if (document.getElementById("remember").checked) {
           updatekey();
        } else {
            alert("Please Accept Terms and Conditions to proceed ");
        }
    }
</script>
  ';
        if(isset($_GET['apikey']))
        {
            $mykey = $_GET['apikey'];
            global $wpdb;

            $table_name  = $wpdb->prefix."options";
            $set = array('option_value' => $mykey);
            $condition = array('option_name' => 'marketplacegenie_api_key');
            $wpdb->update($table_name, $set, $condition);
            update_option('marketplacegenie_api_key', $mykey);
      echo '<meta http-equiv="refresh" content="0;url=admin.php?page=marketplacegenie" />';
        }
        $tab            =   "general";
        $notification   =   null;

        if (isset($_REQUEST["tab"])) {
            switch ($_REQUEST["tab"]) {
                case "general":
                case "synchronization":
                case "import":
                case "settings":
                case "log":
                    $tab = $_REQUEST["tab"];
                    break;
            }
        }

        if (isset($_REQUEST["cleartab"])) {
            global $wpdb;

            $table_name = $wpdb->prefix . 'marketplacegenie_logs';
            $wpdb->query('TRUNCATE TABLE  '.$table_name);
            echo '<meta http-equiv="refresh" content="0;url=admin.php?page=marketplacegenie&tab=log" />';
            die();
        }
            if (isset($_POST["_action"])) {

            switch ($_POST["_action"]) {
                case "general":
                    break;

                case "synchronization":

               break;
                case "import":
                    if (isset($_POST["submit"]) && $_POST["submit"] == 'Import Products')
                    {
                        logFile('Start full manual import');

                        update_option('marketplacegenie_cron_import_enable', 0);

                        Marketplacegenie::marketplacegenie_cron_import_part();

                        $notification = <<<EOD
                        <div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible"> 
                            <p><strong>Import of products will begin in 1 minute</strong></p>
                            <button type="button" class="notice-dismiss">
                                <span class="screen-reader-text">Dismiss this notice.</span>
                            </button>
                        </div>
EOD;
                    }
                    break;
                case "settings":
                    update_option('marketplacegenie_takealot_tag_export', $_POST['MarketplacegenieTakealotTagExport'] );
                    update_option('marketplacegenie_takealot_fixed_rate', floatval($_POST['MarketplacegenieTakealotFixedRate']));
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
        global $wpdb;
        $table = $wpdb->prefix.'marketplacegenie_settings';
        $wpdb->query('TRUNCATE TABLE  '.$table);
        $marketplaceGenieApi    = esc_attr(get_option('marketplacegenie_api'));
        $marketplaceGenieApiKey = esc_attr(get_option('marketplacegenie_api_key'));

        $marketplacegenie_sync_live             = is_checked(esc_attr(get_option('marketplacegenie_sync_live')));
        $marketplacegenie_sync_manual_inventory = is_checked(esc_attr(get_option('marketplacegenie_sync_manual_inventory')));
        $marketplacegenie_takealot_cron_export  = is_checked(esc_attr(get_option('marketplacegenie_takealot_cron_export')));
        $marketplacegenie_sync_manual_price     = is_checked(esc_attr(get_option('marketplacegenie_sync_manual_price')));
        marketplace_update('marketplacegenie_api' , $marketplaceGenieApi);
        marketplace_update('marketplacegenie_api_key' , $marketplaceGenieApiKey);
        marketplace_update('marketplacegenie_sync_live' , $marketplacegenie_sync_live);
        marketplace_update('marketplacegenie_sync_manual_inventory' , $marketplacegenie_sync_manual_inventory);
        marketplace_update('marketplacegenie_takealot_cron_export' , $marketplacegenie_takealot_cron_export);
        marketplace_update('marketplacegenie_sync_manual_price' , $marketplacegenie_takealot_cron_export);
        marketplace_update('marketplacegenie_takealot_cron_export' , $marketplacegenie_sync_manual_price);

        // custom date filled by user
        $marketplacegenie_export_time=get_option('marketplacegenie_export_time');

        $markup                 = null;

        if (!current_user_can( 'manage_options' ))
        {
            wp_die( __( 'You do not have sufficient permissions to access this page.' ));
        }

        ob_start();

        settings_fields(Marketplacegenie::OPTION_GROUP);
        do_settings_sections(Marketplacegenie::OPTION_GROUP);
        //   submit_button();
        //   submit_button( string $text = null, string $type = 'primary', string $name = 'submit', bool $wrap = true, array|string $other_attributes = null )
        submit_button("Apply" /* text */, "primary", "submit" /* name */, true, [ "id" => "buttonGeneral" ]);
        $markup = ob_get_contents();
        ob_end_clean();

        $admin_url = admin_url('admin-ajax.php');

        include_once 'options-head.php';

        if ($tab == "general") {
            $checkApiKey = Marketplacegenie::validateApiKey();
            $apiKeyStatus = '';
            $apiKeyExpire = $checkApiKey['expire'];
            if($apiKeyExpire == "License key is Valid")
            {
                echo "<script>document.getElementById('welcomemessage').style.display='none'</script>";
            }
            if ( isset($_GET['settings-updated']) && $_GET['settings-updated'] === true )
                $apiKeyStatus = $checkApiKey['status'];

            $markup = <<<EOD
<div class="wrap">
<img width="250px" src="/wp-content/plugins/marketplacegenie-api/img/logo.png"/> <br>
   
    $notification
    <form method="post" action="options.php"  >
                    <input type="hidden" name="_action" value="general">

        <nav class="nav-tab-wrapper">
            <a class="nav-tab" href="admin.php?page=marketplacegenie&tab=general">General</a>
            <a class="nav-tab" href="admin.php?page=marketplacegenie&tab=synchronization">Synchronization</a>
            <a class="nav-tab" style ="display:none" href="admin.php?page=marketplacegenie&tab=import">Import</a>
            <a class="nav-tab" href="admin.php?page=marketplacegenie&tab=settings">Settings</a>
           
        </nav>
        <div id="welcomemessage"  style = "display:block;" class="updated woocommerce-message wc-connect">
            <p><strong>Welcome to Marketplacegenie</strong> – You‘re almost ready to integrate</p>
            <p class="submit">
                <a href="https://www.marketplacegenie.com" target="_blank" class="button-primary">Sign in to your Marketplace Genie account to get your API key!</a>
            </p>
        </div>
        <table class="form-table">
            <tr valign="top">
                <th scope="row">Marketplacegenie API Key</th>
                <td>
                    <input style ="min-width:280px;" type="text" id="marketplace_id" name="marketplacegenie_api_key" value="$marketplaceGenieApiKey" />
                    <button id="buttonCheckApiKey" class="button button-default">Check</button>
                    <div id="checkApiKey">$apiKeyStatus</div>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">Enable API</th>
                <td>
                    <input type="checkbox" name="marketplacegenie_check" id="checkControl"/>
                    <input type="hidden" name="marketplacegenie_api" id="checkValue" value="$marketplaceGenieApi"/>
                </td>
            </tr>
            <tr valign="top">
            <th scope="row">License Key Valid</th>
            <td id="apiKeyExpire">$apiKeyExpire</td>
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
var btn = document.getElementById('buttonCheckApiKey');
 jQuery(document).ready(function() { 
        setTimeout(function () {
        autocheck (); 
        }, 100);
     });
 
function checkApiKey(e){
    e.preventDefault();
     autocheck ();
} 
function autocheck (){ 
var modal = document.getElementById("myModal");
    jQuery.ajax({
        type: "POST",
        url: '$admin_url',
        dataType: "json",
        data: {
            'action'    : 'check_api_key',
            'api_key'   : jQuery('[name=marketplacegenie_api_key]').val(),
        },
        success: function (response) {
            try{ 
             console.error("APIKEY  " + jQuery('[name=marketplacegenie_api_key]').val());
            jQuery('#checkApiKey').html(response.status);
            jQuery('#apiKeyExpire').html( response.expire);
            if(response.expire == "License key is Valid")
                {
                   document.getElementById('welcomemessage').style.display='none' 
                     modal.style.display = "none";
                }
                else {
                     modal.style.display = "block";
                }
           }
           catch(error){
                 modal.style.display = "block";
           }
        } 
    });
 }
btn.addEventListener('click', checkApiKey, btn); 
        
</script>
EOD;
        }
        elseif ($tab == "synchronization")
        {
            ob_start();



            $marketplacegenie_export_time =  get_option( 'marketplacegenie_export_time' );
            settings_fields(Marketplacegenie::OPTION_GROUP_SYNC);
            do_settings_sections(Marketplacegenie::OPTION_GROUP_SYNC);

            submit_button("Apply" /* text */, "primary", "submit" /* name */, true, [ "id" => "buttonSynchronization" ]);
            $markup = ob_get_contents();
            ob_end_clean();
            $jsurl2=   get_option( 'siteurl' ) .'/wp-content/plugins/marketplacegenie-api/admin/css/jquery.timepicker.css';
            $jsurl =   get_option( 'siteurl' ) .'/wp-content/plugins/marketplacegenie-api/admin/js/jquery.timepicker.js';
            $markup = <<<EOD
            <div class="wrap">
                <h1 class="wp-heading-inline" ><img width="250px" src="/wp-content/plugins/marketplacegenie-api/img/logo.png"/> <br>Product Synchronization</h1>
                $notification
                <div id="message" class="updated woocommerce-message wc-connect">
                    <p>Synchronize your Takealot market product portfolio with WooCommerce using <strong>Marketplace Genie</strong>.</p>
                </div>
                <form method="post" action="options.php">
                    <input type="hidden" name="_action" value="synchronization">
                    <nav class="nav-tab-wrapper">
                        <a class="nav-tab" href="admin.php?page=marketplacegenie&tab=general">General</a>
                        <a class="nav-tab" href="admin.php?page=marketplacegenie&tab=synchronization">Synchronization</a>
                        <a style="display:none;" class="nav-tab" href="admin.php?page=marketplacegenie&tab=import">Import</a>
                        <a class="nav-tab" href="admin.php?page=marketplacegenie&tab=settings">Settings</a>
                        
                    </nav>
                    <table class="form-table">
                        <tr valign="top">
                            <th scope="row"><strong>Setting</strong></th>
                            <td><strong>On / Off</strong></td>
                            <td><strong>Description</strong></td>
                            <td><strong>Date Picker</strong></td>
                        </tr>
                         <tr valign="top">
                            <th scope="row">Enable Daily Synchronization </th>
                            <td>
                            <!--    <input type="checkbox" name="manual_sync_export" id="manual_sync_export"> -->

                                <input type="hidden" name="hidden_sync_export" id="hidden_sync_export">

                                 <input type="checkbox" name="marketplacegenie_takealot_cron_export" id="marketplacegenie_takealot_cron_export" $marketplacegenie_takealot_cron_export>
                            </td>
    
                            <td>
                                Inventory and Pricing Synchronization of entire catalog ( Matching SKUs ) occurs daily at the time set. Please note if changed / disabled it take 24 hours to effect.
                            </td>
                            
                            <td>    

                                <input type="text"  onkeydown="plsselectfrom(this)" id="marketplacegenie_export_time" name="marketplacegenie_export_time" value="$marketplacegenie_export_time" autocomplete="off">  

                            </td>   
                        </tr>
                        <tr valign="top">
                            <th scope="row"><u>Inventory </u></th>
                        </tr>
                        <tr valign="top">
                            <th scope="row">Live Synchronisation</th>
                            <td><input type="checkbox" name="marketplacegenie_sync_live" id="marketplacegenie_sync_live" $marketplacegenie_sync_live></td>
                            <td>Inventory Synchronization occurs when processing orders and order status updates.</td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">Manual Synchronisation</th>
                            <td><input type="checkbox" name="marketplacegenie_sync_manual_inventory" id="marketplacegenie_sync_manual_inventory" $marketplacegenie_sync_manual_inventory></td>
                            <td>Inventory Synchronization occurs when processing manual inventory updates.</td>
                        </tr>
                        
                       
                        <tr valign="top">
                            <th scope="row"><u>Pricing    </u></th>
                        </tr>
                        <tr valign="top">
                            <th scope="row">Manual Synchronisation</th>
                            <td><input type="checkbox" name="marketplacegenie_sync_manual_price" id="marketplacegenie_sync_manual_price" $marketplacegenie_sync_manual_price></td>
                            <td>Inventory Synchronization occurs when processing manual pricing updates</td>
                        </tr>
                    </table>
                    $markup
                </form>
            </div>
            <!--FOR DATEPICKER IN LOG TAB-->

<link rel="stylesheet" href="$jsurl2">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.css">
<script type="text/javascript" src="https://code.jquery.com/jquery-1.12.4.js"></script>

<script type="text/javascript" src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.20/jquery.datetimepicker.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.20/jquery.datetimepicker.css">
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.20/jquery.datetimepicker.full.js"></script>
 
 <script type="text/javascript" src="$jsurl"></script>
 <script>
 
 function plsselectfrom (wherefrom)
 {
     //
     document.getElementById('marketplacegenie_export_time').value = "";
     alert("Please Use Mouse to Select Time")
     
 }
  $( function() {
    $("#datepicker").datepicker({ 
        dateFormat: "Y-m-d H:i",
    });
      $('#marketplacegenie_export_time').timepicker({ 'timeFormat': 'H:i:s' });
  

    $("#datetimepicker3").datetimepicker({
        format:"Y-m-d H:i",
        minDate: 0,
       
    });   ;

  
    $("#import_img").on("click",function(event){
                event.preventDefault();
                
                if (confirm(" This cant be reversed– click ok to proceed") == true)
                {
                        jQuery.ajax({
                           type: "post",
                           url    : "admin-ajax.php" ,
                           data: {action:"import_image"},  
                           success: function(data)
                           {
                                $("#import_image_text").text("Your product image imported successfully");
                                console.log(data);
                               // window.location.href =url+"/routes?delete=1";
                                                             
                           }
                         });
                } else
                {
                    console.log("no");
                    return false;
                }
    });  

    $("#marketplacegenie_takealot_cron_export").change(function() {
        if(this.checked)
        {
            $("#datetimepicker2").removeAttr("disabled");
            $("#datetimepicker2").attr("required",true);
            $("#hidden_sync_export").val("");
           
        }else
        {
            $("#datetimepicker2").removeAttr("required");
            $("#datetimepicker2").attr("disabled","disabled");
            $("#datetimepicker2").val("");
            $("#hidden_sync_export").val("1");

        }
    });

    if ($('#marketplacegenie_takealot_cron_export').is(':checked'))
    {
        $("#datetimepicker2").removeAttr("disabled");
        $("#datetimepicker2").attr("required",true);
        $("#hidden_sync_export").val("");
    } else {
        $("#datetimepicker2").removeAttr("required");
        $("#datetimepicker2").attr("disabled","disabled");
        $("#datetimepicker2").val("");
        $("#hidden_sync_export").val("1");
    }

  } );
  </script>
<!--FOR DATEPICKER IN LOG TAB-->
EOD;
        }
        elseif ($tab == "import")
        {
            $marketplacegenie_takealot_cron_import = is_checked(esc_attr(get_option('marketplacegenie_takealot_cron_import')));
            $marketplacegenie_import_time = get_option('marketplacegenie_import_time');
            ob_start();

            settings_fields(Marketplacegenie::OPTION_GROUP_IMPORT);
            do_settings_sections(Marketplacegenie::OPTION_GROUP_IMPORT);
            submit_button("Apply" /* text */, "primary", "submit" /* name */, true, [ "id" => "" ]);
            $markup = ob_get_contents();
            ob_end_clean();

            ob_start();
            // submit_button("Import Attributes" /* text */, "primary", "submit" /* name */, false, [ "id" => "import_attributes" ]);
            submit_button("Import Products" /* text */, "primary", "submit" /* name */, true, [ "id" => "import_products" ]);
            $import_button = ob_get_contents();
            ob_end_clean();

            $manual_sync_import=get_option('marketplacegenie_manual_import_on');
            if($manual_sync_import=="on")
            {
                $manual_sync_import="checked='checked'";
            }else
            {
                $manual_sync_import="";
            }

            $markup = <<<EOD
            <div class="wrap">
                <h1 class="wp-heading-inline"><img width="250px" src="/wp-content/plugins/marketplacegenie-api/img/logo.png"/> <br>Marketplace Import</h1>
                $notification
                <div id="message" class="updated woocommerce-message wc-connect">
                    <p>Synchronize your Takealot market product portfolio with WooCommerce using <strong>Marketplace Genie</strong>.</p>
                </div>
                <form method="post" action="options.php">
                    <input type="hidden" name="_action" value="import">
                    <nav class="nav-tab-wrapper">
                        <a class="nav-tab" href="admin.php?page=marketplacegenie&tab=general">General</a>
                        <a class="nav-tab" href="admin.php?page=marketplacegenie&tab=synchronization">Synchronization</a>
                        <a style ="display:none" class="nav-tab" href="admin.php?page=marketplacegenie&tab=import">Import</a>
                        <a class="nav-tab" href="admin.php?page=marketplacegenie&tab=settings">Settings</a>
                    
                    </nav>
                    <table class="form-table">
                        <tr valign="top">
                            <th scope="row"><strong>Setting</strong></th>
                            <td><strong>On / Off</strong></td>
                            <td><strong>Description</strong></td>
                            <td><strong>Date Picker</strong></td>
                        </tr>
                <tr valign="top">
                    <th scope="row">Enable Daily Synchronization</th>
                    <td>
                        <input type="checkbox" name="marketplacegenie_takealot_cron_import" id="marketplacegenie_takealot_cron_import" $marketplacegenie_takealot_cron_import>

                   <!--  <input type="checkbox" name="manual_sync_import" id="manual_sync_import" $manual_sync_import> -->

                        <input type="hidden" name="hidden_sync_import" id="hidden_sync_import">
                    </td>
                        <td>Marketplace Synchronization occurs in the evening</td>

                        <td>
                        <input type="text" id="datetimepicker3" name="datetimepicker3" value="$marketplacegenie_import_time" autocomplete="off" >
                        </td>
                </tr>
                    </table>
                    $markup
                </form>
                <form method="post">
                    <input type="hidden" name="_action" value="import">
                    $import_button
                </form>

                <input type="submit" id="import_img" name="btn_import_image" class="button button-primary" value ="Import Product Images"> 
                <div class="loderbox">  <div class="loader loader_img" style="display:none;"></div></div>

            </div>

            <!--FOR DATEPICKER IN LOG TAB-->

            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.css">
            <script type="text/javascript" src="https://code.jquery.com/jquery-1.12.4.js"></script>
            
            <script type="text/javascript" src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
            
            <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.20/jquery.datetimepicker.js"></script>
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.20/jquery.datetimepicker.css">
            <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.20/jquery.datetimepicker.full.js"></script>
            
             <script>
              $( function() {
                $("#datepicker").datepicker({ 
                    dateFormat: "Y-m-d H:i",
                });
                 
              /*  $("#datetimepicker2").datetimepicker({
                    format:"Y-m-d H:i",
                    minDate: 0,
                    minTime: 0
                });*/
                $('#datetimepicker2x').timepicker();
                $("#datetimepicker3").datetimepicker({
                    format:"Y-m-d H:i",
                    minDate: 0,
                   
                });
            
                $("#import_img").on("click",function(event){
                            event.preventDefault();
                            
                            if (confirm(" This cant be reversed– click ok to proceed") == true)
                            {
                                    jQuery('.loader').css('display','block');
                                    jQuery.ajax({
                                       type: "post",
                                       url    : "admin-ajax.php" ,
                                       data: {action:"import_image"},  
                                       success: function(data)
                                       {
                                            
                                            jQuery('.loader').css('display','none');
                                            var objJSON = JSON.parse(data);
                                            if(objJSON.status==400)
                                            {
                                                $("#import_image_text").text('The API key is not valid'); 
                                                jQuery('#message').css('border-left-color','#dc3232');
                                            }
            
                                                if(objJSON.status==300)
                                                {
                                                    $("#import_image_text").text('No image found in API');
                                                    jQuery('#message').css('border-left-color','#dc3232');
                                                }
                                            
                                                    if(objJSON.status==200)
                                                    {
                                                        $("#import_image_text").text('Images imported successfully');
                                                    }
                                          
                                                                         
                                       },
                                        error: function (jqXHR, exception) {
                                            jQuery('.loader').css('display','none');
                                        }
                                     });
                            } else
                            {
                                console.log("no");
                                return false;
                            }
                });  
            
                $("#marketplacegenie_takealot_cron_import").change(function() {
                    if(this.checked)
                    {
                        $("#datetimepicker3").removeAttr("disabled");
                        $("#datetimepicker3").attr("required",true);
                        $("#hidden_sync_import").val("");
                       
                    }else
                    {
                        $("#datetimepicker3").removeAttr("required");
                        $("#datetimepicker3").attr("disabled","disabled");
                        $("#datetimepicker3").val("");
                        $("#hidden_sync_import").val("1");
            
                    }
                });
            
                if ($('#marketplacegenie_takealot_cron_import').is(':checked'))
                {
                    $("#datetimepicker3").removeAttr("disabled");
                    $("#datetimepicker3").attr("required",true);
                    $("#hidden_sync_import").val("");
                }else
                {
                   
                    $("#datetimepicker3").removeAttr("required");
                    $("#datetimepicker3").attr("disabled","disabled");
                    $("#datetimepicker3").val("");
                    $("#hidden_sync_import").val("1");
                }
            
              } );
              </script>
            <!--FOR DATEPICKER IN LOG TAB-->
            
            <style>
                    .opacity{
                        border-left-color: #dc3232;
                        background: #fff;
                        border-left: 4px solid #fff;
                        box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
                        margin: 5px 15px 2px;
                        padding: 1px 12px;
                    }
                        .loader {
                            margin: 0 auto;
                  border: 15px solid #f3f3f3;
                  border-radius: 50%;
                  border-top: 16px solid #3498db;
                  width: 120px;
                  height: 120px;
                  margin-top:10px;
                  -webkit-animation: spin 2s linear infinite; /* Safari */
                  animation: spin 2s linear infinite;
                }
            
                /* Safari */
                @-webkit-keyframes spin {
                  0% { -webkit-transform: rotate(0deg); }
                  100% { -webkit-transform: rotate(360deg); }
                }
            
                @keyframes spin {
                  0% { transform: rotate(0deg); }
                  100% { transform: rotate(360deg); }
                }
            
            </style>
EOD;
        }
        elseif ($tab == "settings")
        {
            ob_start();
            submit_button("Apply" /* text */, "primary", "submit" /* name */, true, [ "id" => "buttonSettings" ]);
            $markup = ob_get_contents();
            ob_end_clean();
            $marketplacegenieTakealotExchangeRate = get_option('marketplacegenie_takealot_exchange_rate');
            $marketplacegenieTakealotFixedRate = get_option('marketplacegenie_takealot_fixed_rate');
            $MarketplacegenieTakealotTagExport = get_option('marketplacegenie_takealot_tag_export');
            if($MarketplacegenieTakealotTagExport == "on"){$MarketplacegenieTakealotTagExport = "checked";}
            $markup = <<<EOD
            <div class="wrap">
                <h1 class="wp-heading-inline"><img width="250px" src="/wp-content/plugins/marketplacegenie-api/img/logo.png"/> <br>Advanced Configuration Settings</h1>
                $notification
                <div id="message" class="updated woocommerce-message wc-connect">
                    <p>Set data exchange options between your Takealot market product portfolio and WooCommerce.</p>
                </div>    
                <form method="post" action="">
                    <input type="hidden" name="_action" value="settings">
                    <nav class="nav-tab-wrapper">
                        <a class="nav-tab" href="admin.php?page=marketplacegenie&tab=general">General</a>
                        <a class="nav-tab" href="admin.php?page=marketplacegenie&tab=synchronization">Synchronization</a>
                        <a style = "display:none" class="nav-tab" href="admin.php?page=marketplacegenie&tab=import">Import</a>
                        <a class="nav-tab" href="admin.php?page=marketplacegenie&tab=settings">Settings</a>
                         
                    </nav>
                    <table class="form-table">
                        <tr valign="top">
                            <th scope="row">Takealot Exchange Rate</th>
                            
                            <td>
                                <input type="text"   min="1" step=".0000001"  name="MarketplacegenieTakealotExchangeRate" id="marketplacegenieTakealotExchangeRate" value="$marketplacegenieTakealotExchangeRate">
                            </td>
                        </tr>
                         <tr valign="top">
                            <th scope="row">Takealot Fixed Rate</th>
                            <td>
                                <input type="text"   min="1" step=".0000001"  name="MarketplacegenieTakealotFixedRate" id="marketplacegenieTakealotFixedRate" value="$marketplacegenieTakealotFixedRate">
                            </td>
                        </tr>
                         <tr valign="top">
                            <th scope="row">Product Tagging<br> <span style="font-size: 10px;"> Only tagged products will sync  </span></th>
                            
                            <td>
                              <input type="checkbox" name="MarketplacegenieTakealotTagExport" id="MarketplacegenieTakealotTagExport" $MarketplacegenieTakealotTagExport>
               </td>
                        </tr>
                    </table>
                    $markup
                </form>
            </div>
EOD;
        }

        elseif ($tab == "log")
        {
global $wpdb;
$table = $wpdb->prefix . "marketplacegenie_logs";
$results = $wpdb->get_results("SELECT * FROM $table  ORDER BY id desc  ");

?>

<link rel="stylesheet" type="text/css" href="//cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css">

<div class="container" style="margin-right: 50px; margin-left: 50px;">
    <div class="row">
        <div class="m-portlet__body">
            <img width="250px" src="/wp-content/plugins/marketplacegenie-api/img/logo.png"/> <br>

            <h2>My Log</h2>
            <!-- Latest compiled and minified CSS -->

            <div class="table-responsive users-table">
                <a  href="admin.php?page=marketplacegenie&cleartab=true&tab=log">  <input type="button" value="Clear Log"    ></a>
                <table border="1" id="myTable"  class="display" >
                    <thead>
                    <tr>

                        <td> ID</td>
                        <td>Created At</td>
                        <td>Response</td>

                    </tr>
                    </thead>
                    <tbody>

                    <?php foreach ($results as $result) {


                        ?>

                        <tr>

                            <td><?php echo $result->id  ; ?></td>
                            <td><?php echo $result->created_at; ?></td>
                            <td><?php echo $result->response; ?></td>

                        </tr>
                    <?php } ?>
                    </tbody>
                </table>


                <script src="//ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
                <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script>
                <script src="//cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
                <script>
                    $(document).ready(function () {
                        $('#myTable').DataTable({
                            "order": [[ 0, "desc" ]]
                        } );
                    });
                </script>
            </div>
        </div>
    </div>
</div>
  <?php

        }
        print($markup);
    }

    add_action( 'admin_init', 'marketplacegenie_sync_after_update_product', 12);
    function marketplacegenie_sync_after_update_product ()
    {

        $marketplaceGenieApi                    = esc_attr(get_option('marketplacegenie_api'));

        $marketplacegenie_sync_live             = esc_attr(get_option('marketplacegenie_sync_live'));
        $marketplacegenie_sync_manual_inventory = esc_attr(get_option('marketplacegenie_sync_manual_inventory'));
        $marketplacegenie_takealot_cron_export  = esc_attr(get_option('marketplacegenie_takealot_cron_export'));
        $marketplacegenie_sync_manual_price     = esc_attr(get_option('marketplacegenie_sync_manual_price'));

        if ($marketplaceGenieApi)
        {
            if (!$marketplacegenie_takealot_cron_export)
            {
                if ($marketplacegenie_sync_manual_inventory || $marketplacegenie_sync_manual_price)
                {
                    // add_action( 'save_post_product',                    'Marketplacegenie::updateOffer' );
                    // add_action( 'woocommerce_product_quick_edit_save',  'Marketplacegenie::updateOffer' );
                    add_action( 'save_post', 'Marketplacegenie::updateOffer' );
                }
                if ($marketplacegenie_sync_live)
                {
                    add_action( "woocommerce_product_set_stock",       "Marketplacegenie::onOrderStatusUpdate");
                    add_action( "woocommerce_order_status_completed",  "Marketplacegenie::onOrderStatusUpdate");
                    add_action( "woocommerce_order_status_refunded",   "Marketplacegenie::onOrderStatusUpdate");
                    add_action( "woocommerce_order_status_cancelled",  "Marketplacegenie::onOrderStatusUpdate");
                    add_action( "woocommerce_payment_complete",        "Marketplacegenie::onOrderStatusUpdate");
                    add_action( "woocommerce_order_status_changed",    "Marketplacegenie::onOrderStatusUpdate");
                    add_action( 'save_post', 'Marketplacegenie::updateOffer' );
                }
            }
        }
    }

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

        if (Marketplacegenie::apiEnabled())
        {

            $admin_bar->add_menu( array(
                'id'    => 'marketplacegenie-takealot-api-status',
                'title' => __('<span>API Status' . (Marketplacegenie::apiStatus() ? '&nbsp;<span style="color: #00ff00;">+</span>':'<span style="color: #ff0000;"> -</span></span>')),
                'href'  => 'admin.php?page=marketplacegenie',
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
<input type="text" class="short" name="Marketplacegenie_{$key}" id="_marketplacegenie_{$key}" value="{$meta}" readonly>
</p>
EOD;
                print($markup); //"$attribute: $meta<br>");
            }
        }
    }
    function is_checked ($option)
    {
        $output = empty($option) ? '' : 'checked="checked"';
        return $output;
    }

    function logFile( $response)
    {
        date_default_timezone_set("Africa/Maseru");
  /*      $upload_dir = wp_upload_dir();
        $file = $upload_dir['basedir'] . '/logFile.txt';
        $text = date('Y-m-d H:i:s T') . ' - ' . print_r($textLog, true);//Выводим переданную переменную
        $log = file_exists($file) ? file_get_contents($file) : '';
        file_put_contents($file, $text . PHP_EOL . $log);*/

        global $wpdb;
        $table = $wpdb->prefix.'marketplacegenie_logs';
        $data = array( 'response' => $response , 'created_at' =>  date('Y-m-d H:i:s'));
        $format = array('%s','%s');
        $wpdb->insert($table,$data,$format);
        $my_id = $wpdb->insert_id;

    }

}

// Cron setup
add_filter( 'cron_schedules', 'cron_import_interval' );
function cron_import_interval( $schedules )
{
    $schedules['cron_import_interval'] = array(
        'interval' => 55,
        'display' => 'One in 55 second'
    );
    return $schedules;
}

if (Marketplacegenie::apiEnabled()) {
    update_option('marketplacegenie_cron_import_enable', 0);
    update_option('marketplacegenie_takealot_cron_import', 0);
    $import_start       = get_option('marketplacegenie_cron_import_enable');
    $export_start       = get_option('marketplacegenie_cron_export_enable');
    $scheduled_import   = get_option('marketplacegenie_takealot_cron_import');
    $scheduled_export   = get_option('marketplacegenie_takealot_cron_export');
    $is_already_import  = get_option('marketplacegenie_last_full_import');

    // import cron
    if ($import_start || $scheduled_import)
    {
        add_action( 'init', 'marketplacegenie_add_cron_import' );
        function marketplacegenie_add_cron_import()
        {
            $is_importing = get_option('marketplacegenie_cron_import_page');

            if ( $is_importing && ! wp_next_scheduled( 'marketplacegenie_do_cron_in_progress_import_hook' ) )
            {
                wp_schedule_event( time(), 'cron_import_interval', 'marketplacegenie_do_cron_in_progress_import_hook');
            }

            if ( ! wp_next_scheduled( 'marketplacegenie_do_cron_in_progress_import_hook' ) )
            {
                $marketplacegenie_last_full_import = get_option('marketplacegenie_last_full_import');

                if (!$marketplacegenie_last_full_import || $marketplacegenie_last_full_import + 24*60*60 < strtotime('now')) {
                    wp_schedule_event( strtotime('now ' . $import_time), 'daily', 'marketplacegenie_do_cron_import_hook');
                }
            }
        }

        add_action( 'marketplacegenie_do_cron_in_progress_import_hook', 'marketplacegenie_do_cron_import' );
        add_action( 'marketplacegenie_do_cron_import_hook', 'marketplacegenie_do_cron_import' );
        function marketplacegenie_do_cron_import()
        {
            Marketplacegenie::marketplacegenie_cron_import_part();
        }
    }

    // export cron
    if ($export_start || $scheduled_export)
    {
        // start cron
        add_action( 'init', 'marketplacegenie_add_cron_export' );
        function marketplacegenie_add_cron_export()
        {

            $is_exporting = get_option('marketplacegenie_cron_export_page');
            $export_time =  get_option('marketplacegenie_export_time');
            if ( $is_exporting && ! wp_next_scheduled( 'marketplacegenie_do_cron_in_progress_export_hook' ) )
            {

                wp_schedule_event( time(), 'cron_import_interval', 'marketplacegenie_do_cron_in_progress_export_hook');
            }

            if ( ! wp_next_scheduled( 'marketplacegenie_do_cron_in_progress_export_hook' ) )
            {

                $marketplacegenie_last_full_export = get_option('marketplacegenie_last_full_export');

                //24hour
                if ( $marketplacegenie_last_full_export + 24*60*60 < strtotime('now')) {

                 $fixedtime = strtotime('now ' . $export_time)- 7200;
                    wp_schedule_event($fixedtime , 'daily', 'marketplacegenie_do_cron_export_hook');
                  //  wp_schedule_event($fixedtime , 'daily', 'marketplacegenie_do_cron_export_hook');
                }
            }

        }

       add_action( 'marketplacegenie_do_cron_in_progress_export_hook', 'marketplacegenie_do_cron_export' );

        add_action( 'marketplacegenie_do_cron_export_hook', 'marketplacegenie_do_cron_export' );

        function marketplacegenie_do_cron_export()
        {
            Marketplacegenie::updateOffers();
        }

    }

    // refresh cron
    if ($scheduled_import || $scheduled_export) {

        // start cron
        add_action( 'init', 'marketplacegenie_add_cron_update_hook' );
        function marketplacegenie_add_cron_update_hook()
        {
            if ( ! wp_next_scheduled( 'marketplacegenie_add_cron_update' ) )
            {

                logFile('Update cron');
                wp_schedule_event( time()+50, 'cron_import_interval', 'marketplacegenie_add_cron_update');
            }

        }

        add_action( 'marketplacegenie_add_cron_update', 'marketplacegenie_do_cron_update' );
        function marketplacegenie_do_cron_update()
        {

            $max_time = (ini_get("max_execution_time") <= 120 ) ? ini_get("max_execution_time") - 10 : 120;

            sleep($max_time);

            wp_remote_post(site_url());
        }
    }
}


// call import image function
add_action( 'wp_ajax_import_image', 'import_image' );
add_action( 'wp_ajax_nopriv_import_image', 'import_image' );
function import_image()
{
    onimportProductimage();
}

//FOR IMPORT IMAGE BUTTON
function onimportProductimage()
{
    logFile('Start proccess of image import');
    if (!Marketplacegenie::apiEnabled())
        return;
    $page   = 1;
    $pages  = 0;
    $count_product_sync = 0;
    $request = Marketplacegenie::apiOffers($page, 100);
    // var_dump($request); die;
    if($request->LicenseStatus == false && !empty($request->LicenseStatus) ){
        logFile('API validation error');
        $resp["status"] = '400';
        $resp["code"] = 'API_KEY_ERROR';
        $resp["messages"] = 'Api validation Error';
        $resp["data"] = 'The api key is not valid';
        echo  $finalresponse = json_encode($resp);
        exit();
    }
    //var_dump($request);die;
    logFile('Start full import of images');

    // if (Marketplacegenie::isLicensedimage($request)) {
    set_time_limit(500);
    // page_size
    // page_number
    // total_results
    $pages = ceil((float) $request->total_results / (float) $request->page_size);

    do{

        logFile('Total results = ' . $request->total_results. '||Page Number = ' . $request->page_number . '||Page size = ' . $request->page_size . '||Total pages = ' . $pages);

        foreach ($request->offers as $offer)
        {
            $product_id = wc_get_product_id_by_sku($offer->sku);
            if ($product_id)
            {
                //  Product exists.
                $product = wc_get_product($product_id);
                //----------for image update
                $post_id = $product_id; //"10457";
                $image_url = $offer->featured_image; //"http://techexprt.com/crypto/assets/images/coint_trader_logo_final.png";//  //$offer->sku
                if (empty($image_url))
                {
                    logFile('Images are not found');
                    $resp["status"] = '300';
                    $resp["code"] = 'API_DATABASE_ERROR';
                    $resp["messages"] = 'Database Error';
                    $resp["data"] = 'No image found';
                    echo  $finalresponse = json_encode($resp);
                    exit();
                }
                else
                {

                    $image_name = basename($image_url);
                    $upload_dir = wp_upload_dir(); // Set upload folder
                    if (file_get_contents($image_url))
                    {
                        $image_data = file_get_contents($image_url); // Get image data
                        $unique_file_name = wp_unique_filename($upload_dir['path'], $image_name); // Generate unique name
                        $filename = basename($unique_file_name); // Create image file name

                        // Check folder permission and define file location
                        if (wp_mkdir_p($upload_dir['path']))
                        {
                            $file = $upload_dir['path'] . '/' . $filename;
                        }else{
                            $file = $upload_dir['basedir'] . '/' . $filename;
                        }

                        // Create the image  file on the server
                        file_put_contents($file, $image_data);

                        // Check image file type
                        $wp_filetype = wp_check_filetype($filename, null);

                        // Set attachment data
                        $attachment = array(
                            'post_mime_type' => $wp_filetype['type'],
                            'post_title' => sanitize_file_name($filename),
                            'post_content' => '',
                            'post_status' => 'inherit',
                        );

                        // Create the attachment
                        $attach_id = wp_insert_attachment($attachment, $file, $post_id);

                        // Define attachment metadata
                        $attach_data = wp_generate_attachment_metadata($attach_id, $file);

                        // Assign metadata to attachment
                        wp_update_attachment_metadata($attach_id, $attach_data);

                        // And finally assign featured image to post
                        set_post_thumbnail($post_id, $attach_id);
                    }
                    //----------for image update

                    $resp["status"] = '200';
                    $resp["code"] = 'API_success';
                    $resp["messages"] = 'imported successfully';
                    $resp["data"] = 'Images imported successfully';
                    echo $finalresponse = json_encode($resp);
                }
            }
            else{

            }
            $count_product_sync++;
        }

        if (++$page <= $pages)
        {
            logFile('Next Update Images of page'. $page);
            $request = Marketplacegenie::apiOffers($page, 100);
            if (!Marketplacegenie::isLicensedimage($request))
            {
                logFile('Licensed Error'. $page);
                break;
            }

        }
    }while($page <= $pages);
    // }
    logFile('------All product image Import successfully---' . $page);
    $resp["status"] = '200';
    $resp["code"] = 'API_success';
    $resp["messages"] = 'imported successfully';
    $resp["data"] = 'Images imported successfully';
    echo $finalresponse = json_encode($resp);
    // = ' . $count_product_sync
    return $finalresponse;
}



function marketplace_update($option , $value)
{
    global $wpdb;
    $table = $wpdb->prefix.'marketplacegenie_settings';
  //  $wpdb->query('TRUNCATE TABLE  '.$table);
    $data = array('option_name' => $option, 'option_value' => $value);
    $format = array('%s','%s');
    $wpdb->insert($table,$data,$format);

}
