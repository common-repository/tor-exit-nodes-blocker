<?php
	/*
	 * Plugin Name: Tor Blocker
	 * Plugin URI: http://pike.hqpeak.com
	 * Description: This version is obsolete now, use https://wordpress.org/plugins/pike-firewall 
	 * Version: 2.0.0
	 * Author: HQPeak
	 * Author URI: http://hqpeak.com
	 * License: GPL2
	 */

	/*  Copyright 2015  HQPeak  (email: contact@hqpeak.com)
	
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

	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

	// Plugin Version constant define
	if ( !defined('PIKEFIREWALL_VERSION') )	define('PIKEFIREWALL_VERSION', '2.0.0');

	// Global Settings
	$pike_firewall_options = get_option('pikefirewallsettings');
	
	$default_tor = isset($pike_firewall_options['default_tor']) ? $pike_firewall_options['default_tor'] : "";
	$default_proxy = isset($pike_firewall_options['default_proxy']) ? $pike_firewall_options['default_proxy'] : "";
	$services_update_time = isset($pike_firewall_options['services_update_time']) ? $pike_firewall_options['services_update_time'] : time();
	
	$checkbox_options = isset($pike_firewall_options['check']) ? $pike_firewall_options['check'] : array("check"=>array());
	$deny_list = isset($pike_firewall_options['deny']) ? $pike_firewall_options['deny'] : "";	
	$stealth_mode = isset($pike_firewall_options['stealth_mode']) ? $pike_firewall_options['stealth_mode'] : array("stealth_mode"=>array());
	$captcha_check = isset($pike_firewall_options['captcha_check']) ? $pike_firewall_options['captcha_check'] : array("captcha_check"=>array());
	$cron_check = isset($pike_firewall_options['cron_check']) ? $pike_firewall_options['cron_check'] : array("cron_check"=>array());
	$msg = isset($pike_firewall_options['custom_msg']) ? $pike_firewall_options['custom_msg'] : array("custom_msg"=>array("text"=>""));
	$intrusion_options = isset($pike_firewall_options['intrusion']) ? $pike_firewall_options['intrusion'] : array();
	
	
	// Add CSRF protection to the plugin
	add_action('init', 'csrf_protect', 1);
	
	function csrf_protect() {
		if ( isset($_POST['pike-firewall-submit']) || isset($_POST['pike-firewall-delete']) || isset($_POST['pike-firewall-csv']) ) {
			if ( !isset($_POST['main_form_nonce']) || !wp_verify_nonce($_POST['main_form_nonce'], 'form_submit') ) {
				wp_die(__('CSRF detected!'));
			}
		}
	}

	
	// Update plugin
	add_action('plugins_loaded', 'update_plugin_check');
	function update_plugin_check() {
		if (!function_exists('get_plugins')) {
 			require_once(ABSPATH.'wp-admin/includes/plugin.php' );
 		}
 		
 		global $wpdb, $pike_firewall_options;

 		if ( !isset($pike_firewall_options['version']) ) { 
 			pikefirewall_plugin_activate();

 			$table_name = $wpdb->prefix."blocker";
 			$table_name_log = $wpdb->prefix."blocker_log";
 			$table_name_asn = $wpdb->prefix."blocker_asn";
 			
 			$sql = "DROP TABLE IF EXISTS $table_name";
 			$wpdb->query($sql);
 			$sql = "DROP TABLE IF EXISTS $table_name_log";
 			$wpdb->query($sql);
 			$sql = "DROP TABLE IF EXISTS $table_name_asn";
 			$wpdb->query($sql);
 			delete_option('torblockersettings');
 			 			
 			$pike_firewall_options['version'] = PIKEFIREWALL_VERSION;
 			update_option('pikefirewallsettings', $pike_firewall_options);
 		} else {
 			if ( PIKEFIREWALL_VERSION != $pike_firewall_options['version'] ) {
 				$pike_firewall_options['version'] = PIKEFIREWALL_VERSION;
 				update_option('pikefirewallsettings', $pike_firewall_options);
 			}
 		}
	}	
	
	
	// Add the plugin settings
	add_action('admin_init', 'pike_firewall_menu_setting');
	
	function pike_firewall_menu_setting() { 
		register_setting('pikefirewallgroup', 'pikefirewallsettings');
	}
	
	
	// Add the options page
	add_action('admin_menu', 'pike_firewall_menu');
	
	function pike_firewall_menu() {
// 		add_options_page(__('Pike Firewall Plugin Options'), __('Pike Firewall'), 'manage_options', 'pike_firewall_menu', 'pike_firewall_menu_options');
		add_menu_page(__('Pike Firewall Settings'), __('Pike Firewall'), 'manage_options', 'pike_firewall_menu', 'pike_firewall_menu_options', '', 66);
	}
	
	// Create plugin options page
	function pike_firewall_menu_options() {
		if ( !current_user_can('manage_options') )  {
			wp_(__('You do not have sufficient permissions to access this page.'));
		}
	
		global $wpdb, $pike_firewall_options, $cron_check, $captcha_check, $checkbox_options, $msg, $stealth_mode, $intrusion_options;
		
		$active_tab = 'main';
		if ( isset( $_GET['tab'] ) ) {
			$active_tab = $_GET['tab'];
		}
	
		ob_start(); ?>
			<div class="wrap">
				<h1>Pike Firewall Settings</h1>
				<?php settings_errors(); ?>
				<p>Future development and support for <strong>Torblocker</strong> plugin is over. Currently it is upgraded to the last version of <strong>Pike Firewall</strong> plugin. Please use this NEW plugin in future. You can download it from it from this <a href="<?php echo esc_url('http://pike.hqpeak.com/') ?>" target="_blank"><strong>plugin page</strong>.</a></p>
				<h2 class="nav-tab-wrapper">
					<a href="<?php echo esc_url('?page=pike_firewall_menu&tab=main') ?>" class="nav-tab <?php echo $active_tab == 'main' ? 'nav-tab-active' : ''; ?>">General</a>
					<a href="<?php echo esc_url('?page=pike_firewall_menu&tab=logs') ?>" class="nav-tab <?php echo $active_tab == 'logs' ? 'nav-tab-active' : ''; ?>">Logs</a>
				</h2>
				<?php if( $active_tab == 'main' ) { ?>
				<form method="post" action="options.php" id="main-form">
					<?php settings_fields('pikefirewallgroup'); ?>					
					<p>		
						<label><big><strong>Update Tor block list:</strong></big></label><br />
						<label><small>Default is free version of the tor exit list service. During beta period is equal to premium!</small></label><br />
						<input type="text" name="pikefirewallsettings[default_tor]" value="<?php echo esc_url($pike_firewall_options['default_tor']); ?>" size="40" />
					</p>
					<p>		
						<label><big><strong>Proxy list:</strong></big></label><br />
						<label><small>Default is free version of the proxy list service. During beta period is equal to premium!</small></label><br />
						<input type="text" name="pikefirewallsettings[default_proxy]" value="<?php echo esc_url($pike_firewall_options['default_proxy']); ?>" size="40" />
					</p>
					<p>		
						<label><big><strong>Data centers:</strong></big></label><br />
						<label><small>Truncated database of data centers ip ranges that allow anonymous services hosting is shipped with plugin installation.<br/>For complete database and realtime updates write at contact[at]hqpeak[dot]com</small></label><br />
					</p>
					<p>
					 	<a href="<?php echo esc_url('http://pike.hqpeak.com/') ?>" target="_blank">Learn more</a> or get <a href="<?php echo esc_url('http://pike.hqpeak.com/account/') ?>" target="_blank">premium service</a> access.
					</p><br />
					<p>
						<label><big><strong>Cron Job:</strong></big></label><br />
						<input type="checkbox" name="pikefirewallsettings[cron_check][]" value="on" <?php echo (in_array('on', $cron_check)) ? 'checked' : '' ?>>Enable Cron Job<br />
						<label><small>(When checked, updates are performed using WP_Cron, if not plugin is taking care reagrding updates)</small></label><br />
					</p><br/>
					<p>
						<label><big><strong>Filter Humans:</strong></big></label><br />
						<input type="checkbox" name="pikefirewallsettings[captcha_check][]" value="on" <?php echo (in_array('on', $captcha_check)) ? 'checked' : '' ?>>Proove that visitor is a human&nbsp;&nbsp;
						<label><small>(When enabled, a visitor coming form Anonymous network is required to proove himself as human before proceeding with action)</small></label><br />
					</p><br/>
					<p>	
						<label><big><strong>Requests to deny:</strong></big></label><br />
						<label><small>(Here goes all the POST and GET parameters you want to deny [enter them one by one, separated by comma])</small></label><br />
						<textarea name="pikefirewallsettings[deny]" rows="8" cols="60"><?php echo esc_html($pike_firewall_options['deny']); ?></textarea>
					</p><br />
					<p>
						<label><big><strong>Requests to allow:</strong></big></label><br />
						<input type="checkbox" name="pikefirewallsettings[check][]" value="visit" <?php echo (in_array('visit', $checkbox_options) ? 'checked' : ''); ?>>Visits&nbsp;&nbsp;
						<label><small>(Anonymous users can read only public content on the site)</small></label><br />
						<input type="checkbox" name="pikefirewallsettings[check][]" value="comment" <?php echo (in_array('comment', $checkbox_options) ? 'checked' : ''); ?>>Comments&nbsp;&nbsp;
						<label><small>(Anonymous users can post comments)</small></label><br />
						<input type="checkbox" name="pikefirewallsettings[check][]" value="registration" <?php echo (in_array('registration', $checkbox_options) ? 'checked' : ''); ?>>Registration&nbsp;&nbsp;
						<label><small>(Anonymous users can register for the site)</small></label><br />
						<input type="checkbox" name="pikefirewallsettings[check][]" value="subscription" <?php echo (in_array('subscription', $checkbox_options) ? 'checked' : ''); ?>>Subscription&nbsp;&nbsp;
						<label><small>(Anonymous users can subscribe)</small></label><br />
						<input type="checkbox" name="pikefirewallsettings[check][]" value="administration" <?php echo (in_array('administration', $checkbox_options) ? 'checked' : ''); ?>>Administration&nbsp;&nbsp;
						<label><small>(Anonymous users can access administration panel)</small></label><br />
						<input type="checkbox" name="pikefirewallsettings[check][]" value="request" <?php echo (in_array('request', $checkbox_options) ? 'checked' : ''); ?>>Request&nbsp;&nbsp;
						<label><small>(Anonymous users can send POST requests)</small></label><br />
						<input type="hidden" name="pikefirewallsettings[services_update_time]" value=<?php echo $pike_firewall_options['services_update_time']; ?> />
					</p><br />
					<p>
						<label><big><strong>Intrusion Detection:</strong></big></label><br />
						<input type="checkbox" name="pikefirewallsettings[intrusion][]" value="foreign_origin" <?php echo (in_array('foreign_origin', $intrusion_options) ? 'checked' : ''); ?>>POST requests with foreign origin&nbsp;&nbsp;<br />
						<input type="checkbox" name="pikefirewallsettings[intrusion][]" value="strange_useragent" <?php echo (in_array('strange_useragent', $intrusion_options) ? 'checked' : ''); ?>>POST requests with strange User Agent&nbsp;&nbsp;<br />
						<input type="checkbox" name="pikefirewallsettings[intrusion][]" value="user_enumeration" <?php echo (in_array('user_enumeration', $intrusion_options) ? 'checked' : ''); ?>>Wordpress user enumeration&nbsp;&nbsp;<br />
						<input type="checkbox" name="pikefirewallsettings[intrusion][]" value="invisible_chars" <?php echo (in_array('invisible_chars', $intrusion_options) ? 'checked' : ''); ?>>Detect invisible characters on input&nbsp;&nbsp;<br />
						<input type="checkbox" name="pikefirewallsettings[intrusion][]" value="proxy_headers" <?php echo (in_array('proxy_headers', $intrusion_options) ? 'checked' : ''); ?>>Detect Proxy Headers&nbsp;&nbsp;<br />
					</p><br />
					<p>
						<label><big><strong>Custom Pike Firewall logo message:</strong></big></label><br />
						<input type="checkbox" name="pikefirewallsettings[custom_msg][enabled]" value="enable" <?php echo (in_array('enable', $msg) ? 'checked' : ''); ?>>Enable Anonymous logo message&nbsp;&nbsp;
						<label><small>(When enabled, a custom message with Anonymous logo and ip address of the tor user is displayed)</small></label><br />
						<label><small>(Here goes the custom message you want to show to the Anonymous users)</small></label><br />
						<textarea name="pikefirewallsettings[custom_msg][text]" rows="8" cols="60"><?php echo esc_html($msg['text']); ?></textarea>
					</p><br />
					<p>
						<label><big><strong>Pike Firewall logs:</strong></big></label><br />
						<input type="checkbox" name="pikefirewallsettings[stealth_mode][]" value="on" <?php echo (in_array('on', $stealth_mode)) ? 'checked' : '' ?>>Enable Stealth Mode logging&nbsp;&nbsp;
						<label><small>(When enabled, all anonymous users vistis are logged in database)</small></label><br />
					</p><br/>
					<p class="submit">
						<input type="submit" name="pike-firewall-submit" id="submitBtn" class="button-primary" value="Save" />
					</p>
					<?php wp_nonce_field('form_submit', 'main_form_nonce') ?>
				</form>
				<?php } else {
					$table_name = $wpdb->prefix."pike_firewall_log";
					
					if ( $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name ) {
						$logs = $wpdb->get_results("SELECT * FROM $table_name");
					} else {
						//wp_die(__('Table does not exist in database!'));
						pike_firewall_error_notice("Logs table does not exist in database!", 'notice-error');
					}
					
					if ( isset($_POST['pike-firewall-delete']) ) {
						$toDelete = isset($_POST['selected']) ? $_POST['selected'] : null;
					
						if ( is_array($toDelete) && !empty($toDelete) ) {
							$strDelete = '';
								
							foreach ( $toDelete as $key => $item ) {
								$item = esc_sql($item); 								
								$strDelete .= $item.', ';
							}
								
 							if ( !$wpdb->query("DELETE FROM $table_name WHERE id IN (".rtrim($strDelete, ', ').")")) {
 								$wpdb->show_errors();
 								wp_die($wpdb->print_error());
 							}
							
 							echo "<script>location.reload(true)</script>";
 							wp_die();
						}
					}
				?>
					
					<div class="logs">
					<?php if ( $logs ) { ?>
						<form action="" method="post">
							<p>
								<input type="submit" name="pike-firewall-csv" id="downloadBtn" class="button-primary" value="Export to CSV" />
							</p>
							<div class="buttons">
								<input type="submit" name="pike-firewall-delete" id="deleteBtn" value="Delete" />&nbsp;&nbsp;&nbsp;
								<span id="checkAll" onclick="checkAll(this)">Check All</span><br/>
							</div>
							<table>
								<tr>
									<th>Select</th>
									<th>IP</th>
									<th>URL</th>
									<th>Type</th>
									<th>Time</th>
								</tr>
								<?php foreach ( $logs as $log ) { ?>					
								<tr>	
									<td><input type="checkbox" name="selected[]" value="<?php echo $log->id ?>" /></td>
									<td><?php echo esc_html($log->ip); ?></td>
									<td class="url"><?php echo nl2br(esc_html(urldecode($log->landing_page))); ?></td>
									<td><?php echo esc_html($log->type); ?></td>
									<td><?php echo esc_html(date('d.m.Y H:i:s', strtotime($log->systime))); ?></td>
								</tr>
								<?php } ?>
							</table>
							<?php wp_nonce_field('form_submit', 'main_form_nonce') ?>
						</form>
					<?php } else { ?>
						<h2>No entries to show!</h2>
					<?php } ?>
					</div>
					
				<?php } ?>			
			</div>
		<?php
		echo ob_get_clean(); 
	}
	
	
	// Include styles and scripts for admin
	add_action('admin_enqueue_scripts', 'scripts_init');
	
	function scripts_init($page) {
			
		global $cron_check;
		
		wp_enqueue_script('jquery');
		
		if ( !isset($cron_check[0]) || strtolower($cron_check[0]) == 'off' ) {
			wp_register_script('pikefirewall-script-ajax', plugins_url('js/pike_firewall_ajax.js', __FILE__), array('jquery'));
			wp_localize_script('pikefirewall-script-ajax', 'pikefirewallAjax', array('ajaxurl' => admin_url('admin-ajax.php')));
			wp_enqueue_script('pikefirewall-script-ajax');
		}
		
		if( 'toplevel_page_pike_firewall_menu' != $page ) {
			return;
		}
		
		wp_enqueue_script('pikefirewall-script', plugins_url('js/pike_firewall_scripts.js', __FILE__), array('jquery'));
		wp_enqueue_style('logs-style', plugins_url('css/logs.css', __FILE__));
	}
	
	
	// Include frontend styles and scripts
	add_action('wp_enqueue_scripts', 'frontend_scripts_init');

	function frontend_scripts_init() {
		
		global $cron_check;
		
		if ( !isset($cron_check[0]) || strtolower($cron_check[0]) == 'off' ) {
			wp_enqueue_script('jquery');
			wp_register_script('pikefirewall-script-ajax', plugins_url('js/pike_firewall_ajax.js', __FILE__), array('jquery'));
			wp_localize_script('pikefirewall-script-ajax', 'pikefirewallAjax', array('ajaxurl' => admin_url('admin-ajax.php')));
			wp_enqueue_script('pikefirewall-script-ajax');
		}
	}
	
	
	// Plugin activation
	register_activation_hook(__FILE__, 'pikefirewall_plugin_activate');
	
	function pikefirewall_plugin_activate() {
		global $wpdb;
			
		$table_name = $wpdb->prefix."pike_firewall_single_ip";
		$table_name_iprange = $wpdb->prefix."pike_firewall_ip_range";
		$table_name_log = $wpdb->prefix."pike_firewall_log";
		
		if (	$wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name && 
				$wpdb->get_var("SHOW TABLES LIKE '$table_name_iprange'") != $table_name_iprange &&
				$wpdb->get_var("SHOW TABLES LIKE '$table_name_log'") != $table_name_log	) {

			$msg_html = "<!DOCTYPE html>
<html>
	<head>
		<title>Pike Firewall</title>
			
		<link href='http://fonts.googleapis.com/css?family=Varela' rel='stylesheet' type='text/css'>
		<style>
			body {
				background-color: #efefef;
				font-family: 'Valera', sans-serif;
			}
		
			p {
				font-size:18px; 
				text-align:center;
			}
		
			.message {
				width: 600px;
				height: auto;
				background-color: #fff;
				box-shadow: 3px 3px 2px #444;
				margin-left: auto;
				margin-right: auto;
				margin-top: 100px;
				padding: 10px;
			}
		</style>
	</head>
	<body>
		<div class='message'>
			<p style='font-weight:bold;'>
				[pike_firewall_logo]<br/>
				[ip_address]<br/>
			</p>
		</div>
	</body>
</html>";
			
			$defaults = array("default_tor"=>"http://pike.hqpeak.com/api/tor", "deny"=>"", "check"=>array("visit"), "services_update_time"=>time(), "custom_msg" => array("enabled"=>"enable", "text"=>"$msg_html"), "stealth_mode" => array("Off"), "captcha_check" => array("Off"), "cron_check" => array("Off"), "default_proxy"=>"http://pike.hqpeak.com/api/proxy", "intrusion"=>array());
			$settings = wp_parse_args(get_option('pikefirewallsettings', $defaults), $defaults);
			update_option('pikefirewallsettings', $settings);
			
			$pike_firewall_options = get_option('pikefirewallsettings');
			$default_tor = isset($pike_firewall_options['default_tor']) ? $pike_firewall_options['default_tor'] : "";
			$default_proxy = isset($pike_firewall_options['default_proxy']) ? $pike_firewall_options['default_proxy'] : "";
			
			$sql = "CREATE TABLE $table_name(ip INT(11) UNSIGNED NOT NULL, PRIMARY KEY (ip))";
			$sql_iprange = "CREATE TABLE $table_name_iprange(min INT(11) UNSIGNED NOT NULL, max INT(11) UNSIGNED NOT NULL, KEY (min), KEY (max))";
			$sql_log = "CREATE TABLE $table_name_log(
														id INT(10) NOT NULL AUTO_INCREMENT, 
														ip VARCHAR(25) NOT NULL, 
														landing_page TEXT NOT NULL, 
														type VARCHAR(255) NOT NULL,
														systime TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL ,
														PRIMARY KEY (id)
													)";
			
			require_once ABSPATH.'wp-admin/includes/upgrade.php';
			
			dbDelta($sql);
			dbDelta($sql_iprange);
			dbDelta($sql_log);
			
			// Single IP
			$ip_arr_tor = pike_firewall_get_ip($default_tor); // changed
			$ip_long_tor = pike_firewall_to_long($ip_arr_tor);
			
			$ip_arr_proxy = pike_firewall_get_proxy($default_proxy);
			$ip_long_proxy = pike_firewall_to_long($ip_arr_proxy);
			
			if (is_array($ip_long_tor) && is_array($ip_long_proxy)) {
				$ip_arr_merged = append_arrays($ip_long_tor, $ip_long_proxy);
				if ($ip_arr_merged != 0) {
					pike_firewall_fill_table($ip_arr_merged);
				}
			} else {
				if (is_array($ip_long_tor) && sizeof($ip_long_tor)>0) {
					pike_firewall_fill_table($ip_long_tor);
				} elseif (is_array($ip_arr_proxy) && sizeof($ip_arr_proxy)) {
					pike_firewall_fill_table($ip_long_proxy);
				}
			}
			
			// IP Range
 			$ip_arr = json_decode(file_get_contents(WP_PLUGIN_DIR.'/tor-exit-nodes-blocker/pike-firewall-iprange.json'), true);
			$ip_long = pike_firewall_to_long_iprange($ip_arr);
			if (is_array($ip_long) && sizeof($ip_long)>0) {
				pike_firewall_fill_table_iprange($ip_long);
			}
		}
	}
	
	
	// Merge two arrays
	function append_arrays($arr1, $arr2){
		$out_arr = array();
		if ( is_array($arr1) && is_array($arr2) && sizeof($arr2) > 0){
			foreach ( $arr1 as $key => $val ){
				if (!in_array($val, $out_arr)) {$out_arr[] = $val;}
			}
			foreach ( $arr2 as $key => $val ){
				if (!in_array($val, $out_arr)) {$out_arr[] = $val;}
			}
			if (sizeof($out_arr) > 0){return $out_arr;}else{return 0;}
		}else{
			return 0;
		}
	}

	
	// Get Proxy IP
	function pike_firewall_get_proxy($url){
		$response = wp_remote_get($url);
		if( !is_wp_error( $response ) && is_array( $response ) && isset( $response['body']) ) {
			$data = $response['body'];
		}else{
 			//wp_die(__('Service unavailable'));
			return array();
		}
		//decode output as array
		$service_data = json_decode($data, true);
	
		//never trust the input - sanitate every ip
		if (is_array($service_data) && ($size = sizeof($service_data)) > 0){
			for ($i=0; $i<$size; $i++){
				if (!preg_match("/^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}/", $service_data[$i]))
					$service_data[$i] = "0.0.0.0";
			}
		}else{
			//wp_die(__("Bad output"));
			return array();
		}
	
		return $service_data;
	}
	
	// Get Tor users IP
	function pike_firewall_get_ip($url){
		/*
		$ch = curl_init();
		$timeout = 5;
	
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		$data = curl_exec($ch);
		$ce = curl_errno($ch);
		curl_close($ch);
	
		if ($ce != 0) return array(); //wp_die(__("Error opening service"));
		//*/
		
		$response = wp_remote_get($url);
		if( !is_wp_error( $response ) && is_array( $response ) && isset( $response['body']) ) {
			$data = $response['body'];
		}else{
			//return array();
			$data = fallback_service();
		}
		//decode output as array
		$service_data = json_decode($data, true);
	
		//never trust the input - sanitate every ip
		if (is_array($service_data) && ($size = sizeof($service_data)) > 0){
			for ($i=0; $i<$size; $i++){
				if (!preg_match("/^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}/", $service_data[$i]))
					$service_data[$i] = "0.0.0.0";
			}
		}else{
			//wp_die(__("Bad output"));
			return array();
		}
	
		return $service_data;
	}
	
	// Service fallback
	function fallback_service() {
		$fallback_response = wp_remote_get('https://check.torproject.org/cgi-bin/TorBulkExitList.py?ip=8.8.8.8&port=');
		if( !is_wp_error( $fallback_response ) && is_array( $fallback_response ) && isset( $fallback_response['body']) ) {
			$fallback_parts = explode("\n", $fallback_response['body']);
			foreach ( $fallback_parts as $part ) {
				if ( preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', $part) ) {
					break;
				}
			
				array_shift($fallback_parts);
			}
			
			return json_encode($fallback_parts);
		}else{
 			//wp_die(__('Service unavailable! Try again later.'));
			return array();
		}
	}		

	// Convert IPs into long integers
	function pike_firewall_to_long($ip_arr){
		if (is_array($ip_arr)){
			$ip_arr = array_unique($ip_arr);
			$ip2long = array();
			
			foreach ($ip_arr as $ip){
				//if ( !in_array(ip2long($ip), $ip2long) )
					$ip2long[] = ip2long($ip);
			}
		}else{
// 			wp_die(__("Bad output"));
			return false;
		}

		return $ip2long;
	}
	
	// Put the array with IPs into table
	function pike_firewall_fill_table($ip_long){
		global $wpdb;
		
		$tmp = $ip_long;
		$table_name = $wpdb->prefix."pike_firewall_single_ip";

		$q = sizeof($ip_long)/300;
		for ( $i=0;$i<=$q;$i++ ){
			$ip_long = array();							
			for( $k=$i*300; $k<($i+1)*300;$k++ ){
				if (isset($tmp[$k])) $ip_long[] = $tmp[$k];
			}
			if (is_array($ip_long) && sizeof($ip_long)>0){
				$sql = "INSERT INTO $table_name (ip) VALUES ";
				
				foreach ($ip_long as $long){
					$sql .= "('".esc_sql($long)."'), ";
				}
				$sql = rtrim($sql, ', ');
				$wpdb->query($sql);
			}
		}
	}
	
	// Convert iP Range list into long integers
	function pike_firewall_to_long_iprange($ip_arr){
		if (is_array($ip_arr)){
			$ip2long = array();
				
			foreach ( $ip_arr as $key => $value ) {
				//if ( !in_array(ip2long($ip), $ip2long) )
				$ip2long[$key][0] = ip2long($value[0]);
				$ip2long[$key][1] = ip2long($value[1]);
			}
		}else{
// 			wp_die(__("Bad output"));
			return false;
		}
	
		return $ip2long;
	}
	
	// Put the array with IP Ranges into table
	function pike_firewall_fill_table_iprange($ip_long){
		global $wpdb;
	
		$table_name = $wpdb->prefix."pike_firewall_ip_range";
		$tmp = $ip_long;
		
		end($ip_long);
		$counter = key($ip_long);
	
		$q = $counter/600;
		for ( $i=0;$i<=$q;$i++ ){
			$ip_long = array();
			for( $k=$i*600; $k<($i+1)*600; $k++ ){
				if (isset($tmp[$k])) $ip_long[] = $tmp[$k];
			}
			if (is_array($ip_long) && sizeof($ip_long)>0){
				$sql = "INSERT INTO $table_name (min, max) VALUES ";
	
				foreach ($ip_long as $long){
					$sql .= "('".esc_sql($long[0])."', '".esc_sql($long[1])."'), ";
				}
				$sql = rtrim($sql, ', ');
				$wpdb->query($sql);
			}
		}
	}
	
	
	// Check if the time has passed to update the IPs table
	function pike_firewall_table_update_check(){
		global $wpdb;
		
		$pike_firewall_options = get_option('pikefirewallsettings');
		$default_tor = isset($pike_firewall_options['default_tor']) ? $pike_firewall_options['default_tor'] : "";
		$default_proxy = isset($pike_firewall_options['default_proxy']) ? $pike_firewall_options['default_proxy'] : "";
		$services_update_time = isset($pike_firewall_options['services_update_time']) ? $pike_firewall_options['services_update_time'] : time() ;
	
		$check = false;
		$t = time();
		$diff = $t - $services_update_time;
		
		$ip_long_tor = array();
		$ip_long_proxy = array();
		
		$table_name = $wpdb->prefix."pike_firewall_single_ip";
		$table_name_iprange = $wpdb->prefix."pike_firewall_ip_range";
		$table_name_log = $wpdb->prefix."pike_firewall_log";
		
		if (	$wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name &&
				$wpdb->get_var("SHOW TABLES LIKE '$table_name_iprange'") == $table_name_iprange &&
				$wpdb->get_var("SHOW TABLES LIKE '$table_name_log'") == $table_name_log	) {

			if ( ($default_tor == 'http://pike.hqpeak.com/api/tor' && $diff >= 1800) ||
				(preg_match('/^http(s)?:\/\/(w{3}\.)?pike.hqpeak.com(\/.+)+\?id=[0-9a-zA-Z]{40}&format=json/', $default_tor) && $diff >= 400))
			{
				$ip_arr_tor = pike_firewall_get_ip($default_tor);
				$ip_long_tor = pike_firewall_to_long($ip_arr_tor);
			}
			
			if ( $default_proxy == 'http://pike.hqpeak.com/api/proxy' && $diff >= 1800)
			{
				$ip_arr_proxy = pike_firewall_get_proxy($default_proxy);
				$ip_long_proxy = pike_firewall_to_long($ip_arr_proxy);
			}
			
			if (is_array($ip_long_tor) && is_array($ip_long_proxy)) { 
				$ip_arr_merged = append_arrays($ip_long_tor, $ip_long_proxy);
				if ($ip_arr_merged != 0) {
					$sql = "DELETE FROM $table_name";
					$wpdb->query($sql);
					pike_firewall_fill_table($ip_arr_merged);
					$check = true;
				}
			} else {
				if (is_array($ip_long_tor) && sizeof($ip_long_tor)>0) {
					$sql = "DELETE FROM $table_name";
					$wpdb->query($sql);
					pike_firewall_fill_table($ip_long_tor);
					$check = true;
				} elseif (is_array($ip_long_proxy) && sizeof($ip_long_proxy)>0) {
					$sql = "DELETE FROM $table_name";
					$wpdb->query($sql);
					pike_firewall_fill_table($ip_long_proxy);
					$check = true;
				}
			}
			
			if ( $check ) {
				$pike_firewall_options['services_update_time'] = time();
			}
			
			update_option("pikefirewallsettings", $pike_firewall_options);
		}
	}
	
// 	add_action('init', 'pike_firewall_table_update_check', 1);
	
	
	// Update maxmind and plugin database
	function ajax_scan() {
		global $cron_check;
		
		if ( !isset($cron_check[0]) || $cron_check[0] == 'Off' ) {
			pike_firewall_table_update_check();
			wp_die();	// this is required to terminate immediately and return a proper response
		}
	}
	
	// Ajax request for to update ip database
	add_action('wp_ajax_pike_firewall_ajax', 'ajax_scan');
	add_action('wp_ajax_nopriv_pike_firewall_ajax', 'ajax_scan');


	// Search for match between user ip and ip in the tor exit list
	function match_address(){
		global $wpdb;

		$table_name = $wpdb->prefix."pike_firewall_single_ip";
		$table_name_iprange = $wpdb->prefix."pike_firewall_ip_range";
		
		if ( $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name && 
			 $wpdb->get_var("SHOW TABLES LIKE '$table_name_iprange'") == $table_name_iprange ){
			
			if ( isset( $_SERVER['REMOTE_ADDR'] ) ){
				$user_address = $_SERVER['REMOTE_ADDR'];
				$user2long = ip2long($user_address);
			
				$pikefirewall_address = $wpdb->get_row("SELECT * FROM $table_name_iprange WHERE $user2long BETWEEN `min` AND `max`");
				if ($pikefirewall_address !== NULL){
					return array('address' => $user2long, 'type' => 'IP Range');
				} else {
					$pikefirewall_address = $wpdb->get_row("SELECT * FROM $table_name WHERE `ip`=$user2long");
					if ($pikefirewall_address !== NULL){
						return array('address' => $pikefirewall_address->ip, 'type' => 'Tor/Proxy');
					}
				}
			}

			if ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ){
				$user_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
				$user2long = ip2long($user_address);
			
				$pikefirewall_address = $wpdb->get_row("SELECT * FROM $table_name_iprange WHERE $user2long BETWEEN `min` AND `max`");
				if ($pikefirewall_address !== NULL){
					return array('address' => $user2long, 'type' => 'IP Range');
				} else {
					$pikefirewall_address = $wpdb->get_row("SELECT * FROM $table_name WHERE `ip`=$user2long");
					if ($pikefirewall_address !== NULL){
						return array('address' => $pikefirewall_address->ip, 'type' => 'Tor/Proxy');
					}
				}
			}
			
			return false;
			
		}else{
// 			wp_die(__('Table with ip addresses from tor exit list does not exist.'));
			return false;
		}	
	}


	// Stores tor user ip, visited url and time in database.
	function savelog($long_ip) {
		global $wpdb;

		$table_name_log = $wpdb->prefix."pike_firewall_log";
		
		if ($wpdb->get_var("SHOW TABLES LIKE '$table_name_log'") == $table_name_log){
			$default_ip = long2ip($long_ip['address']);
			$page_url = !empty($_SERVER['HTTPS']) ? 'https://' : 'http://';
			$page_url .= $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
			$page_url = urlencode($page_url);
			
			if ( !empty($_POST) ) {
				$page_url .= "\nPOST: ";
				foreach ( $_POST as $kpost=>$post ) {
					$page_url .= esc_sql(esc_html($kpost))."=".esc_sql(esc_html($post)).", ";
				}	
				$page_url = rtrim($page_url, ", ");
			}
			
			if ( !empty($_GET) ) {
				$page_url .= "\nGET: ";
				foreach ( $_GET as $kget=>$get ) {
					$page_url .= esc_sql(esc_html($kget))."=".esc_sql(esc_html($get)).", ";
				}
				$page_url = rtrim($page_url, ", ");
			}
			
			if ( !empty($_COOKIE) ) {
				$page_url .= "\nCOOKIE: ";
				foreach ( $_COOKIE as $kcookie=>$cookie ) {
					$page_url .= esc_sql(esc_html($kcookie))."=".esc_sql(esc_html($cookie)).", ";
				}
				$page_url = rtrim($page_url, ", ");
			}

			if ( !$wpdb->insert($table_name_log, array('ip' => esc_sql($default_ip), 'landing_page' => $page_url, 'type' => esc_sql($long_ip['type']))) ) {
				$wpdb->show_errors();
				wp_die($wpdb->print_error());
			}
		} else {
			//wp_die(__('Table with tor users logs does not exist.'));
			pike_firewall_error_notice("Logs table does not exist in database!", 'notice-error');
		}
	}
	
	
	// Check if stealth_mode is active
	function check_stealth() {
		global $wpdb, $stealth_mode;
		
		$user_ip = "";
		
		if ( isset( $_SERVER['REMOTE_ADDR'] ) ){
			$user_ip = $_SERVER['REMOTE_ADDR'];
		}
			
		if ( isset( $_SERVER['REMOTE_ADDR'] ) ){
			$user_ip = $_SERVER['REMOTE_ADDR'];
		}
				
		if ( ($long_ip = match_address()) && $stealth_mode[0] == "on" ) {
			savelog($long_ip);
		}
	}
	
	add_action('init', 'check_stealth');
	
	
	// Show captcha to filter humans
	function show_captcha(){
		if ( match_address() ) {
			session_start();
// 			$_SESSION['url_redirect'] = 'http'.(isset($_SERVER['HTTPS']) ? 's' : '').'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].(!empty($_SERVER['QUERY_STRING']) ? '?'.$_SERVER['QUERY_STRING'] : '');
			$_SESSION['url_redirect'] = 'http'.(isset($_SERVER['HTTPS']) ? 's' : '').'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
			
			$pike_firewall_options = get_option('pikefirewallsettings');
			$captcha_check = isset($pike_firewall_options['captcha_check'])?$pike_firewall_options['captcha_check']:array("captcha_check"=>array());
			
			if ( !isset($_SESSION['captcha_valid']) || $_SESSION['captcha_valid'] !== true ) {
				if ( isset($captcha_check[0]) && $captcha_check[0] == "on" ) {
						
					require_once(WP_PLUGIN_DIR.'/tor-exit-nodes-blocker/pike-firewall-userhuman.php');
					exit;
						
				}
			}
		}
	}
	
	add_action('init', 'show_captcha', 4);
	
	
	// Replace predefined tags in custom error message
	function tags_replace($str, $longip) {
		$onion = "<img src='".WP_PLUGIN_URL.'/tor-exit-nodes-blocker/img/pike.jpeg'."'/>";
		$ip_address = long2ip($longip);
		$resStr = str_replace(array('[pike_firewall_logo]', '[ip_address]'), array($onion, $ip_address), $str);
		return $resStr;
	}
	
	
	// Deny reading public content
	function pike_firewall_read_content(){
		$pike_firewall_options = get_option('pikefirewallsettings');
		$checkbox_options = isset($pike_firewall_options['check'])? $pike_firewall_options['check']:array("check"=>array());
		$msg = isset($pike_firewall_options['custom_msg'])?$pike_firewall_options['custom_msg']:array("custom_msg"=>array("text"=>""));
		$stealth_mode = isset($pike_firewall_options['stealth_mode'])?$pike_firewall_options['stealth_mode']:array("stealth_mode"=>array());
	
		if ( !in_array('visit', $checkbox_options) && !is_admin() && ($long_ip = match_address()) ) {
			if ( $stealth_mode[0] != "on" ) {
				savelog($long_ip);
			}
			
			if ( isset($msg['enabled']) && $msg['enabled'] === "enable" ) {
				$custom_msg = tags_replace($msg['text'], $long_ip['address']);
				require_once(WP_PLUGIN_DIR.'/tor-exit-nodes-blocker/pike-firewall-logo-view.php');
				die();
			} else {
				wp_die(__('You do not have sufficient permissions to read any public content from this site.'));
			}
		}
		
	}
	
	add_action('init', 'pike_firewall_read_content');
	
	
	// Deny comments
	function pike_firewall_post_comments($comment_id){
		$pike_firewall_options = get_option('pikefirewallsettings');
		$checkbox_options = isset($pike_firewall_options['check'])? $pike_firewall_options['check']:array("check"=>array());
		$msg = isset($pike_firewall_options['custom_msg'])?$pike_firewall_options['custom_msg']:array("custom_msg"=>array("text"=>""));
		$stealth_mode = isset($pike_firewall_options['stealth_mode'])?$pike_firewall_options['stealth_mode']:array("stealth_mode"=>array());
			
		if ( !in_array('comment', $checkbox_options) && !empty($_POST['comment']) && ($long_ip = match_address()) ) {
			if ( $stealth_mode[0] != "on" ) {
				savelog($long_ip);
			}
			
			if ( $msg['enabled'] === "enable" ) {
				$custom_msg = tags_replace($msg['text'], $long_ip['address']);
				require_once(WP_PLUGIN_DIR.'/tor-exit-nodes-blocker/pike-firewall-logo-view.php');
				die();
			} else {
				wp_die(__('You do not have sufficient permissions to post comments.'));
			}
		}
		
	}
	
	add_action('init', 'pike_firewall_post_comments');
	
	
	// Deny registration
	function pike_firewall_user_registration(){
		$pike_firewall_options = get_option('pikefirewallsettings');
		$checkbox_options = isset($pike_firewall_options['check'])? $pike_firewall_options['check']:array("check"=>array());
		$msg = isset($pike_firewall_options['custom_msg'])?$pike_firewall_options['custom_msg']:array("custom_msg"=>array("text"=>""));
		$stealth_mode = isset($pike_firewall_options['stealth_mode'])?$pike_firewall_options['stealth_mode']:array("stealth_mode"=>array());
			
		if ( !in_array('register', $checkbox_options) && ($long_ip = match_address()) ) {
			if ( $stealth_mode[0] != "on" ) {
				savelog($long_ip);
			}
			
			if ( $msg['enabled'] === "enable" ) {
				$custom_msg = tags_replace($msg['text'], $long_ip['address']);
				require_once(WP_PLUGIN_DIR.'/tor-exit-nodes-blocker/pike-firewall-logo-view.php');
				die();
			} else {
				wp_die(__('You do not have sufficient permissions to register for this site.'));
			}
		}
		
	}
	
	add_action('register_post', 'pike_firewall_user_registration');
	
	
	// Deny subscription
	function pike_firewall_subscription(){
		$pike_firewall_options = get_option('pikefirewallsettings');
		$checkbox_options = isset($pike_firewall_options['check'])? $pike_firewall_options['check']:array("check"=>array());
		$msg = isset($pike_firewall_options['custom_msg'])?$pike_firewall_options['custom_msg']:array("custom_msg"=>array("text"=>""));
		$stealth_mode = isset($pike_firewall_options['stealth_mode'])?$pike_firewall_options['stealth_mode']:array("stealth_mode"=>array());
		
		$url_parts = explode('/', $_SERVER['REQUEST_URI']);
			
		if ( !in_array('subscription', $checkbox_options) && (in_array('feed', array_keys($_REQUEST)) || in_array('feed', $url_parts)) && ($long_ip = match_address()) ) {
			if ( $stealth_mode[0] != "on" ) {
				savelog($long_ip);
			}
			
			if ( $msg['enabled'] === "enable" ) {
				$custom_msg = tags_replace($msg['text'], $long_ip['address']);
				require_once(WP_PLUGIN_DIR.'/tor-exit-nodes-blocker/pike-firewall-logo-view.php');
				die();
			} else {
				wp_die(__('You do not have sufficient permissions to enter the feed section.'));
			}
		}
		
	}
	
	add_action('init', 'pike_firewall_subscription');
	
	
	// Deny administration panel access
	function pike_firewall_admin_access_deny(){
		$pike_firewall_options = get_option('pikefirewallsettings');
		$checkbox_options = isset($pike_firewall_options['check'])? $pike_firewall_options['check']:array("check"=>array());
		$msg = isset($pike_firewall_options['custom_msg'])?$pike_firewall_options['custom_msg']:array("custom_msg"=>array("text"=>""));
		$stealth_mode = isset($pike_firewall_options['stealth_mode'])?$pike_firewall_options['stealth_mode']:array("stealth_mode"=>array());
		
		if ( !in_array('administration', $checkbox_options) && ($long_ip = match_address()) ) {
			if ( $stealth_mode[0] != "on" ) {
				savelog($long_ip);
			}
			
			if ( $msg['enabled'] === "enable" ) {
				$custom_msg = tags_replace($msg['text'], $long_ip['address']);
				require_once(WP_PLUGIN_DIR.'/tor-exit-nodes-blocker/pike-firewall-logo-view.php');
				die();
			} else {
				wp_die(__('You do not have sufficient permissions to enter the Dashboard.'));
			}
		}
		
	}
	
	add_action('admin_init','pike_firewall_admin_access_deny');
	
	
	// Deny POST requests
	function pike_firewall_post_request_deny(){
		$pike_firewall_options = get_option('pikefirewallsettings');
		$checkbox_options = isset($pike_firewall_options['check'])? $pike_firewall_options['check']:array("check"=>array());
		$msg = isset($pike_firewall_options['custom_msg'])?$pike_firewall_options['custom_msg']:array("custom_msg"=>array("text"=>""));
		$stealth_mode = isset($pike_firewall_options['stealth_mode'])?$pike_firewall_options['stealth_mode']:array("stealth_mode"=>array());

		if ( !in_array('request', $checkbox_options) && $_SERVER['REQUEST_METHOD'] == 'POST' && ($long_ip = match_address())) {
			if ( $stealth_mode[0] != "on" ) {
				savelog($long_ip); 
			}
			
			if ( $msg['enabled'] === "enable" ) {
				$custom_msg = tags_replace($msg['text'], $long_ip['address']);
				require_once(WP_PLUGIN_DIR.'/tor-exit-nodes-blocker/pike-firewall-logo-view.php');
				die();
			} else {
				wp_die(__('You do not have sufficient permissions to take any actions on this site.'));
			}
		}
				
	}
	
	add_action('init', 'pike_firewall_post_request_deny');
	
	
	// Deny specific requests
	function pike_firewall_block_requests(){	
		$pike_firewall_options = get_option('pikefirewallsettings');
		$deny_list = isset($pike_firewall_options['deny']) ? $pike_firewall_options['deny'] : "";
		$msg = isset($pike_firewall_options['custom_msg'])?$pike_firewall_options['custom_msg']:array("custom_msg"=>array("text"=>""));
		$stealth_mode = isset($pike_firewall_options['stealth_mode'])?$pike_firewall_options['stealth_mode']:array("stealth_mode"=>array());
			
		$all_requests = explode(',', $deny_list);
		$check = false;
	
		// changed
		foreach ($all_requests as $request){
			if ( (in_array(trim($request), array_keys($_POST)) || in_array(trim($request), array_keys($_GET))) && ($long_ip = match_address()) ){
				if ( $stealth_mode[0] != "on" ) {
					savelog($long_ip);
				}
				
				$check = true;
				break;
			}
		}
			
		if ($check) {
			if ( $msg['enabled'] === "enable" ) {
				$custom_msg = tags_replace($msg['text'], $long_ip['address']);
				require_once(WP_PLUGIN_DIR.'/tor-exit-nodes-blocker/pike-firewall-logo-view.php');
				die();
			} else {
				wp_die(__('You do not have sufficient permissions to visit this URL.'));
			}
		}
	
	}
	
	add_action('init', 'pike_firewall_block_requests');
	
	
	// Delete table in the database
	function pike_firewall_plugin_deactivate(){
		global $wpdb;
				
		$table_name = $wpdb->prefix."pike_firewall_single_ip";
		$table_name_iprange = $wpdb->prefix."pike_firewall_ip_range";
		$table_name_log = $wpdb->prefix."pike_firewall_log";
		
		$sql = "DROP TABLE IF EXISTS $table_name";
		$wpdb->query($sql);
		$sql_iprange = "DROP TABLE IF EXISTS $table_name_iprange";
		$wpdb->query($sql_iprange);
		$sql = "DROP TABLE IF EXISTS $table_name_log";
		$wpdb->query($sql);
//  		delete_option('pikefirewallsettings');
		if ( wp_get_schedule('hourly_update_event') !== false ) {
			wp_clear_scheduled_hook('hourly_update_event');
		}
	
	}
	
	register_deactivation_hook(__FILE__, 'pike_firewall_plugin_deactivate');
	
	
	// Creates widget for the Tor Blocker
	function widget_display($args) {
		echo $args['before_widget'];
					
		if ( $long_ip = match_address() ) {
			echo "<img src='".WP_PLUGIN_URL."/tor-exit-nodes-blocker/img/pike.jpeg' width='60px' style='display:table; margin:0 auto' />";
			echo "<strong style='display:table; margin:0 auto'>".long2ip($long_ip['address'])."</strong>";
		} else {
			if ( isset( $_SERVER['REMOTE_ADDR'] ) ){
				$default_ip = $_SERVER['REMOTE_ADDR'];
			}
					
			if ( isset( $_SERVER['REMOTE_ADDR'] ) ){
				$default_ip = $_SERVER['REMOTE_ADDR'];
			}
					
			//echo "<strong>".$default_ip."</strong>";
		}
			
		echo $args['after_widget'];
	}
	
	add_action('widgets_init', function() {
		wp_register_sidebar_widget(
				'Pike_Firewall_Widget',       // unique widget id
				'Pike Firewall Widget',       // widget name
				'widget_display',  	        // callback function
				array(                     // options
						'description' => __('Pike Firewall Widget!', 'text_domain')
				)
		);
	});

	
	// Cron Job function
	function update_database_cron() {
		global $wpdb;
		
		// Update ip table
		$pike_firewall_options = get_option('pikefirewallsettings');
		$default_tor = isset($pike_firewall_options['default_tor']) ? $pike_firewall_options['default_tor'] : "";
		$default_proxy = isset($pike_firewall_options['default_proxy']) ? $pike_firewall_options['default_proxy'] : "";
		
		$check = false;
		$ip_long_tor = array();
		$ip_long_proxy = array();
		
		$table_name = $wpdb->prefix."pike_firewall_single_ip";
		$table_name_iprange = $wpdb->prefix."pike_firewall_ip_range";
		$table_name_log = $wpdb->prefix."pike_firewall_log";
				
		if (	$wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name &&
				$wpdb->get_var("SHOW TABLES LIKE '$table_name_iprange'") == $table_name_iprange &&
				$wpdb->get_var("SHOW TABLES LIKE '$table_name_log'") == $table_name_log	) {
		
			if ( $default_tor == 'http://pike.hqpeak.com/api/tor' ||
				 preg_match('/^http(s)?:\/\/(w{3}\.)?pike.hqpeak.com(\/.+)+\?id=[0-9a-zA-Z]{40}&format=json/', $default_tor) )
			{
				$ip_arr_tor = pike_firewall_get_ip($default_tor);
				$ip_long_tor = pike_firewall_to_long($ip_arr_tor);
			}
			
			if ( $default_proxy == 'http://pike.hqpeak.com/api/proxy' )
			{
				$ip_arr_proxy = pike_firewall_get_proxy($default_proxy);
				$ip_long_proxy = pike_firewall_to_long($ip_arr_proxy);
			}
			
			if (is_array($ip_long_tor) && is_array($ip_long_proxy)) {
				$ip_arr_merged = append_arrays($ip_long_tor, $ip_long_proxy);
				if ($ip_arr_merged != 0) {
					$sql = "DELETE FROM $table_name";
					$wpdb->query($sql);
					pike_firewall_fill_table($ip_arr_merged);
					$check = true;
				}
			} else {
				if (is_array($ip_long_tor) && sizeof($ip_long_tor)>0) {
					$sql = "DELETE FROM $table_name";
					$wpdb->query($sql);
					pike_firewall_fill_table($ip_long_tor);
					$check = true;
				} elseif (is_array($ip_long_proxy) && sizeof($ip_long_proxy)>0) {
					$sql = "DELETE FROM $table_name";
					$wpdb->query($sql);
					pike_firewall_fill_table($ip_long_proxy);
					$check = true;
				}
			}
			
			if ( $check ) {
				$pike_firewall_options['services_update_time'] = time() ;
				update_option("pikefirewallsettings", $pike_firewall_options);
			}
		}
	}

	
	// Update maxmind and plugin database
	function cron_job_action() {
		update_database_cron();
	}
		
 	add_action('hourly_update_event', 'cron_job_action');
 	
	
	// Check if there are cron jobs that needs to be activated
	function cron_job_scan() {
		global $cron_check;
		
		if ( isset($cron_check[0]) && strtolower($cron_check[0]) == 'on' ) {
			if ( wp_get_schedule('hourly_update_event') === false ) {
				wp_schedule_event(time(), 'hourly', 'hourly_update_event');
			}
		} else {
			if ( wp_get_schedule('hourly_update_event') !== false ) {
				wp_clear_scheduled_hook('hourly_update_event');
			}
		}
	}
	
	add_action('init', 'cron_job_scan', 1);
	
	
	function pike_firewall_error_notice($msg="", $class="notice-success", $dismissable="is-dismissible") {
	?>		
		<div class="<?php echo 'notice '.$class.' '.$dismissable; ?> pike-firewall-notice">
			<p><?php echo __($msg); ?></p>
		</div>
	<?php
	}
	
	
	add_action('init', 'logs_to_csv');
	function logs_to_csv() {
		if ( isset($_POST['pike-firewall-csv']) ) {
			global $wpdb;
		
			$table_name_log = $wpdb->prefix."pike_firewall_log";
			if ( $wpdb->get_var("SHOW TABLES LIKE '$table_name_log'") == $table_name_log ) {
				$csv_filename = "pike_firewall_logs_".date('Y-m-d', time()).".csv";
				$csv_logs = $wpdb->get_results("SELECT * FROM $table_name_log");
			
				$fp = fopen('php://output', 'w+');
				fputcsv($fp, array('IP', 'URL', 'Type', 'Time'));
			
				if ( $csv_logs ) {
					foreach ( $csv_logs as $csv_log  ) {
						fputcsv($fp, array($csv_log->ip, urldecode($csv_log->landing_page), $csv_log->type, $csv_log->systime));
					}
				}
			
				fclose($fp);
			
				// Export the data and prompt a csv file for download
				@header('Content-Type: text/csv; charset=utf-8');
				@header('Content-Disposition: attachment; filename='.$csv_filename);
				//readfile($csv_filename);
				exit;
			} else {
				wp_die(__('Logs table does not exist in database!'));
			}
		}
	}
	
	
	// POST requests with foreign origin check
	add_action('init', 'pike_firewall_foreign_origin_check', 15);
	function pike_firewall_foreign_origin_check() {
		add_action('foreign_origin_request', 'pike_firewall_foreign_request_check', 10, 1);
		do_action('foreign_origin_request', parse_url(site_url(), PHP_URL_HOST));
	}
	
	function pike_firewall_foreign_request_check($wpurl) {
		$pike_firewall_options = get_option('pikefirewallsettings');
		$intrusion_options = isset($pike_firewall_options['intrusion'])? $pike_firewall_options['intrusion']:array("intrusion"=>array());
		$msg = isset($pike_firewall_options['custom_msg'])?$pike_firewall_options['custom_msg']:array("custom_msg"=>array("text"=>""));
		$stealth_mode = isset($pike_firewall_options['stealth_mode'])?$pike_firewall_options['stealth_mode']:array("stealth_mode"=>array());
		
		$long_ip = array('address' => ip2long("127.0.0.1"), 'type' => 'Foreign Origin');
		if ( in_array('foreign_origin', $intrusion_options) && test() ) {
			savelog($long_ip);
			if ( $msg['enabled'] === "enable" ) {
				$custom_msg = tags_replace($msg['text'], $long_ip['address']);
				require_once(WP_PLUGIN_DIR.'/tor-exit-nodes-blocker/pike-firewall-logo-view.php');
				die();
			} else {
				wp_die( __('You have sent request with foreign origin.') );
			}
		}
	}
	
	
	// POST requests with strange User Agent check
	add_action('init', 'pike_firewall_user_agent_check', 15);
	function pike_firewall_user_agent_check() {
		$pike_firewall_options = get_option('pikefirewallsettings');
		$intrusion_options = isset($pike_firewall_options['intrusion'])? $pike_firewall_options['intrusion']:array("intrusion"=>array());
		$msg = isset($pike_firewall_options['custom_msg'])?$pike_firewall_options['custom_msg']:array("custom_msg"=>array("text"=>""));
		$stealth_mode = isset($pike_firewall_options['stealth_mode'])?$pike_firewall_options['stealth_mode']:array("stealth_mode"=>array());
		
		$long_ip = array('address' => ip2long("127.0.0.1"), 'type' => 'Strange User Agent');
		if ( in_array('strange_useragent', $intrusion_options) && test() ) {
			savelog($long_ip);
			if ( $msg['enabled'] === "enable" ) {
				$custom_msg = tags_replace($msg['text'], $long_ip['address']);
				require_once(WP_PLUGIN_DIR.'/tor-exit-nodes-blocker/pike-firewall-logo-view.php');
				die();
			} else {
				wp_die( __('You have sent request with strange User Agent.') );
			}
		}
	}
	
	
	// WP User Enumeratin check
	add_action('init', 'pike_firewall_user_enumeration_check', 15);
	function pike_firewall_user_enumeration_check() {
		add_action('user_enumeration', 'pike_firewall_user_enumeration', 10, 3);
		do_action('user_enumeration', parse_url(site_url(), PHP_URL_HOST), $_POST, $_GET);
	}
	
	function pike_firewall_user_enumeration($wpurl="", $post=array(), $get=array()) {
		$pike_firewall_options = get_option('pikefirewallsettings');
		$intrusion_options = isset($pike_firewall_options['intrusion'])? $pike_firewall_options['intrusion']:array("intrusion"=>array());
		$msg = isset($pike_firewall_options['custom_msg'])?$pike_firewall_options['custom_msg']:array("custom_msg"=>array("text"=>""));
		$stealth_mode = isset($pike_firewall_options['stealth_mode'])?$pike_firewall_options['stealth_mode']:array("stealth_mode"=>array());
		
		$long_ip = array('address' => ip2long("127.0.0.1"), 'type' => 'User Enumeration');
		if ( in_array('user_enumeration', $intrusion_options) && test() ) {
			savelog($long_ip);
			if ( $msg['enabled'] === "enable" ) {
				$custom_msg = tags_replace($msg['text'], $long_ip['address']);
				require_once(WP_PLUGIN_DIR.'/tor-exit-nodes-blocker/pike-firewall-logo-view.php');
				die();
			} else {
				wp_die( __('Wordpress User Enumeration detected.') );
			}
		}
	}
	
	
	// Invisible characters check
	add_action('init', 'pike_firewall_invisible_chars_check', 15);
	function pike_firewall_invisible_chars_check() {
		$pike_firewall_options = get_option('pikefirewallsettings');
		$intrusion_options = isset($pike_firewall_options['intrusion'])? $pike_firewall_options['intrusion']:array("intrusion"=>array());
		$msg = isset($pike_firewall_options['custom_msg'])?$pike_firewall_options['custom_msg']:array("custom_msg"=>array("text"=>""));
		$stealth_mode = isset($pike_firewall_options['stealth_mode'])?$pike_firewall_options['stealth_mode']:array("stealth_mode"=>array());
		
		$long_ip = array('address' => ip2long("127.0.0.1"), 'type' => 'Invisible Character');
		if ( in_array('invisible_chars', $intrusion_options) && test() ) {
			savelog($long_ip);
			if ( $msg['enabled'] === "enable" ) {
				$custom_msg = tags_replace($msg['text'], $long_ip['address']);
				require_once(WP_PLUGIN_DIR.'/tor-exit-nodes-blocker/pike-firewall-logo-view.php');
				die();
			} else {
				wp_die( __('Invisible Characters detected in input.') );
			}
		}
	}
	
	
	// Proxy Headers check
	add_action('init', 'pike_firewall_proxy_headers_check', 15);
	function pike_firewall_proxy_headers_check() {
		$pike_firewall_options = get_option('pikefirewallsettings');
		$intrusion_options = isset($pike_firewall_options['intrusion'])? $pike_firewall_options['intrusion']:array("intrusion"=>array());
		$msg = isset($pike_firewall_options['custom_msg'])?$pike_firewall_options['custom_msg']:array("custom_msg"=>array("text"=>""));
		$stealth_mode = isset($pike_firewall_options['stealth_mode'])?$pike_firewall_options['stealth_mode']:array("stealth_mode"=>array());

		$long_ip = array('address' => ip2long("127.0.0.1"), 'type' => 'Proxy Headers');
		if ( in_array('proxy_headers', $intrusion_options) && test() ) {
			savelog($long_ip);
			if ( $msg['enabled'] === "enable" ) {
				$custom_msg = tags_replace($msg['text'], $long_ip['address']);
				require_once(WP_PLUGIN_DIR.'/tor-exit-nodes-blocker/pike-firewall-logo-view.php');
				die();
			} else {
				wp_die( __('Proxy Headers detected.') );
			}
		}
	}
	
	
	// Test new functions
	function test() {
		return false;
	}
?>