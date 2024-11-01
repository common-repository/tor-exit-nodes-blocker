<?php 
	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
	
	if ( isset($custom_msg) && !empty($custom_msg) ) {
		echo $custom_msg;
	} else {
		
?>
		<!DOCTYPE html>
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
				<div class="message">
					<p style="font-weight:bold;">
						<img src="<?php echo WP_PLUGIN_URL.'/pike-firewall/img/pike.jpeg' ?>" /><br/>
						<?php echo long2ip($long_ip) ?><br/>
					</p>
				</div>
			</body>
		</html>
<?php } ?>
