<?php

register_activation_hook(__FILE__, 'marketplacegenie_install');
global $marketplacegenie_db_version;
$marketplacegenie_db_version = '8.1';

function marketplacegenie_install()
{
    global $wpdb;
    global $marketplacegenie_db_version;
    $table_name = $wpdb->prefix . 'marketplacegenie_orders';
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE $table_name (
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
    $sql2 = "CREATE TABLE $table_name (
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
    $sql2 = "CREATE TABLE $table_name (
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
    $sql2 = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		`response` text  DEFAULT NULL,
		`created_at` text  DEFAULT NULL,
		`methodname` text  DEFAULT NULL,
		 `url` text  DEFAULT NULL,
		 PRIMARY KEY  (id)
	) $charset_collate;";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql2);


    add_option('marketplacegenie_db_version', $marketplacegenie_db_version);
}