<?php

/**
 * Fired during plugin activation
 *
 * @link       https://www.marketplacegenie.co.za/app/woocommerce/
 * @since      1.0.0
 *
 * @package    Marketplacegenie
 * @subpackage Marketplacegenie/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Marketplacegenie
 * @subpackage Marketplacegenie/includes
 * @author     Marketplacegenie (Pty) Ltd <dominic@wardslaus.co.za>
 */
class Marketplacegenie_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {



        global $marketplacegenie_db_version;
        $marketplacegenie_db_version = '8.1';

        global $wpdb;

        $table_name = $wpdb->prefix . 'marketplacegenie_orders';
        $charset_collate = $wpdb->get_charset_collate();
        $sql = " CREATE TABLE IF NOT EXISTS $table_name (
	     id mediumint(9) NOT NULL AUTO_INCREMENT,
		 `orderid` LONGTEXT NOT NULL,
		 `orderstatus` text NOT NULL,
		 `collectiondate` LONGTEXT NOT NULL,
		 `company` LONGTEXT NOT NULL,
		 `contactperson` LONGTEXT NOT NULL,
	     `address1` LONGTEXT NOT NULL,
	     `address2` LONGTEXT NOT NULL,
	     `suburb` LONGTEXT NOT NULL,
	     `city` LONGTEXT NOT NULL,
	     `workphone` LONGTEXT NOT NULL,
	     `homephone` LONGTEXT NOT NULL,
	     `cellphone` LONGTEXT NOT NULL,
	     `instructions` LONGTEXT NOT NULL,
	     `postalcode` LONGTEXT NOT NULL,
	     `created_at` LONGTEXT NOT NULL,
	     `processed` LONGTEXT NOT NULL,
	     `processed_at` LONGTEXT NOT NULL,
 
 	    PRIMARY KEY  (id)
	) $charset_collate;";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);



        $table_name = $wpdb->prefix . 'marketplacegenie_settings';
        $sql2 = " CREATE TABLE IF NOT EXISTS $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		 `option_name` text  DEFAULT NULL,
		 `option_value` text  DEFAULT NULL,
	     `created_at` LONGTEXT NOT NULL,
		 `updated_at` LONGTEXT NOT NULL,
		 PRIMARY KEY  (id)
	) $charset_collate;";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql2);

        $table_name = $wpdb->prefix . 'marketplacegenie_competition';
        $sql2 = " CREATE TABLE IF NOT EXISTS $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		 `option_name` text  DEFAULT NULL,
		 `option_value` text  DEFAULT NULL,
	     `created_at` LONGTEXT NOT NULL,
		 `updated_at` LONGTEXT NOT NULL,
		 PRIMARY KEY  (id)
	) $charset_collate;";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql2);


        $table_name = $wpdb->prefix . 'marketplacegenie_logs';
        $sql2 = " CREATE TABLE IF NOT EXISTS $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		`response` text  DEFAULT NULL,
		`created_at` text  DEFAULT NULL,
		`methodname` text  DEFAULT NULL,
	      `apiname` text  DEFAULT NULL,
		 `url` text  DEFAULT NULL,
		 PRIMARY KEY  (id)
	) $charset_collate;";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql2);


        add_option('marketplacegenie_db_version', $marketplacegenie_db_version);


	}

}
