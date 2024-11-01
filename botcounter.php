<?php
/*
Plugin Name: Bot Counter
Plugin URI: http://www.yunheyun.com/plugin
Description: Count the times search engine comes to your bolg. Display the time when it last come.
Version: 0.0.6
Author: CHEN Yunlin
Author URI: http://www.yunheyun.com
License: GPL2
*/
?>
<?php
/*  Copyright 2011 CHEN Yunlin (email : yunheyun@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
?>
<?php

global $wp_bot_counter_version;
$wp_bot_counter_version = '0.0.3';


function on_bot_visit()
{
	global $wpdb;
	$table_name = $wpdb->prefix . "wp_bot_counter";
	$bots = $wpdb->get_results("SELECT mark, counter FROM $table_name");
	$user_agent = $_SERVER['HTTP_USER_AGENT'];
	foreach ($bots as $bot) {
		if (stristr($user_agent, $bot->mark) !== false) {
			$counter = $bot->counter + 1;
			$now_time = date_i18n("Y-m-d H:i:s");
			$now_timestamp = time();
			$wpdb->update($table_name,
				array("counter"=>$counter, "last_time"=>$now_time, "last_timestamp"=>$now_timestamp),
				array("mark"=>$bot->mark),
				array("%d", "%s", "%d"),
				array("%s")
			);
			break;
		}
	}
}

function botcounter_menu()
{
	include 'botcounter-admin.php';
}

function bot_admin_actions()
{
	add_options_page('Bot Counter', 'Bot Counter', 1, 'Bot-Counter', 'botcounter_menu');
}

function bot_counter_install()
{
	global $wp_bot_counter_version;
	global $wpdb;
	$table_name = $wpdb->prefix . "wp_bot_counter";
	$sql = "CREATE TABLE " . $table_name . " (
		mark VARCHAR(20) NOT NULL,
		counter INT(9) DEFAULT 0,
		last_time VARCHAR(20) DEFAULT '',
		last_timestamp INT(9) DEFAULT 0,
		UNIQUE KEY mark (mark)
	);";
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql);
	$wpdb->query("DELETE FROM $table_name");
	$wpdb->insert($table_name, array('mark' => 'Googlebot'));
	$wpdb->insert($table_name, array('mark' => 'Baiduspider'));
	$wpdb->insert($table_name, array('mark' => 'msnbot'));
	$prv_version = get_option('wp_bot_counter_version');
	if ($prv_version === false) {
		add_option('wp_bot_counter_version', $wp_bot_counter_version);
	} else {
		update_option('wp_bot_counter_version', $wp_bot_counter_version);
	}
}

function bot_counter_uninstall()
{
	global $wpdb;
	$table_name = $wpdb->prefix . "wp_bot_counter";
	$wpdb->query("
		DROPE TABLE $table_name");
	delete_option('wp_bot_counter_version');
	remove_action('wp_footer', 'on_bot_visit');
	remove_action('admin_menu', 'bot_admin_actions');
}

register_activation_hook( __FILE__, 'bot_counter_install');
register_deactivation_hook( __FILE__, 'bot_counter_uninstall');
add_action('wp_footer', 'on_bot_visit');
add_action('admin_menu', 'bot_admin_actions');
