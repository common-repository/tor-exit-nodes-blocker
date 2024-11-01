<?php
	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

	session_start();
	include_once(dirname(__FILE__).'/captcha/simple-php-captcha.php');
	
	if ( isset($_POST['pike-firewall-submit']) ) {
		if ( !isset($_POST['main_form_nonce']) || !wp_verify_nonce($_POST['main_form_nonce'], 'form_submit') ) {
			wp_die(__('CSRF detected!'));
		}
		
		$captcha_code = ( isset($_SESSION['captcha']) && isset($_POST['captcha']) && $_POST['captcha'] != "" ) ? $_SESSION['captcha']['code'] : "";
		$captcha = rawurldecode(sanitize_text_field(trim($_POST['captcha'])));
		
		if ( $captcha_code != "" && $captcha === $captcha_code ) {
			$_SESSION['captcha_valid'] = true;
		
			if ( isset($_SESSION['url_redirect']) ) {
				$url = $_SESSION['url_redirect'];
				wp_redirect($url);
				exit;
			} else {
				if ( current_user_can('administrator') ) {
				   	wp_redirect(admin_url());
				   	exit;
				} else {
					wp_redirect(home_url());
					exit;
				}
			}
		}
	}
	
	$_SESSION['captcha'] = simple_php_captcha();
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<title>Pike Firewall</title>
		
		<link href='http://fonts.googleapis.com/css?family=Varela' rel='stylesheet' type='text/css'>
		<style>
			body {
					background-color: #f1f1f1;
					font-family: 'Valera', sans-serif;
				}
						
				#form-box {
					position: fixed; /* or absolute */
				  	top: 50%;
				  	left: 50%;
				  	width: 300px;
				  	height: 200px;
				  	margin-top: -100px;
				  	margin-left: -150px;
				}
				
				.img-thumbnail {
					border: 1px solid #ddd;
					display: block;
				 			margin: auto;
				}
				
				#captcha {
					width: 100%;
					padding: 2px 6px;
				    font-size: 1.3em;
				    outline: 0;
				    border: 1px solid #ddd;
				 			-webkit-box-shadow: inset 0 1px 2px rgba(0,0,0,.07);
								box-shadow: inset 0 1px 2px rgba(0,0,0,.07);
				 			background-color: #fff;
				 			color: #32373c;
				 			outline: 0;
				 			-webkit-transition: .05s border-color ease-in-out;
				 			transition: .05s border-color ease-in-out;
				}
				
				#captcha:focus {
					border-color: #5b9dd9;
				}
				
				.info {
					color: #32373c;
					font-size: 14px;
				}
				
				#submit {
					background: #0091cd;
				    border-color: #0073aa;
				    border-width: 1px;
				    border-style: solid;
				    -webkit-box-shadow: inset 0 1px 0 rgba(120,200,230,.6);
				    box-shadow: inset 0 1px 0 rgba(120,200,230,.6);
				    color: #fff;
				   	display: inline-block;
				    text-decoration: none;
				    font-size: 13px;
				    line-height: 26px;
				    height: 28px;
				    margin: 0;
				    padding: 0 10px 1px;
				    cursor: pointer;
				    -webkit-appearance: none;
				    -webkit-border-radius: 3px;
				    border-radius: 3px;
				    white-space: nowrap;
				    -webkit-box-sizing: border-box;
				    -moz-box-sizing: border-box;
				    box-sizing: border-box;
				}
		</style>
	</head>
	
	<body>
		<div id="form-box">
			<form name="frm" action="" method="post">
				<img src="<?php echo $_SESSION['captcha']['image_src'] ?>" alt="Captcha" class="img-thumbnail" /><br/>
				<input type="text" class="form-control" id="captcha" name="captcha" placeholder="" autocomplete="off" required /><br/>
				<span class="text-navy small info">Are you human?</span>
				<br/><br/>
				<input type="submit" name="pike-firewall-submit" id="submit" value="Submit" />
				<?php wp_nonce_field('form_submit', 'main_form_nonce') ?>
			</form>
		</div>
	</body>
</html>