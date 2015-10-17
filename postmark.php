<?php
/**
 * Plugin Name: Postmark WordPress Plugin
 * Plugin URI: https://github.com/moxienyc/Postmark-WordPress-Plugin
 * Description: Overwrites wp_mail to send emails through Postmark.
 * Author: Moxie
 * Version: 1.0.0
 * Author URI: https://getmoxied.net/
 */

// Define.
define( 'POSTMARK_ENDPOINT', 'http://api.postmarkapp.com/email' );

// Admin Functionality.
add_action( 'admin_menu', 'pm_admin_menu' );

// Add Postmark to Settings.
function pm_admin_menu() {
	add_options_page( 'Postmark', 'Postmark', 'manage_options', 'pm_admin', 'pm_admin_options' );
}

function pm_admin_action_links( $links, $file ) {
	static $pm_plugin;
	if ( ! $pm_plugin ) {
		$pm_plugin = plugin_basename( __FILE__ );
	}
	if ( $file === $pm_plugin ) {
		$settings_link = '<a href="' . admin_url( 'options-general.php' ) . '?page=pm_admin">Settings</a>';
		array_unshift( $links, $settings_link );
	}
	return $links;
}

add_filter( 'plugin_action_links', 'pm_admin_action_links', 10, 2 );


function pm_admin_options() {
	if ( isset( $_POST['submit'] ) && ! empty( $_POST['submit'] ) && wp_verify_nonce( $_POST['pm_save_nonce'], 'save postmark settings' ) ) {

		if ( isset( $_POST['pm_enabled'] ) && 1 === $_POST['pm_enabled'] ) {
			$pm_enabled = 1;
		} else {
			$pm_enabled = 0;
		}

		$api_key = $_POST['pm_api_key'];
		$sender_email = $_POST['pm_sender_address'];

		if ( isset( $_POST['pm_forcehtml'] ) && 1 === $_POST['pm_forcehtml'] ) {
			$pm_forcehtml = 1;
		} else {
			$pm_forcehtml = 0;
		}

		if ( isset( $_POST['pm_poweredby'] ) && 1 === $_POST['pm_poweredby'] ) {
			$pm_poweredby = 1;
		} else {
			$pm_poweredby = 0;
		}

		update_option( 'postmark_enabled', $pm_enabled );
		update_option( 'postmark_api_key', $api_key );
		update_option( 'postmark_sender_address', $sender_email );
		update_option( 'postmark_force_html', $pm_forcehtml );
		update_option( 'postmark_poweredby', $pm_poweredby );

		$msg_updated = 'Postmark settings have been saved.';
	}
?>

	<script type="text/javascript" >
	jQuery(document).ready(function($) {

		$("#test-form").submit(function(e){
			e.preventDefault();
			var $this = $(this);
			var send_to = $('#pm_test_address').val();

			$("#test-form .button-primary").val("Sending…");
			$.post(ajaxurl, {email: send_to, action:$this.attr("action")}, function(data){
				$("#test-form .button-primary").val(data);
});
});

});
</script>

  <div class="wrap">

	<?php if( isset( $msg_updated ) && '' !== $msg_updated ) : ?><div class="updated"><p><?php echo $msg_updated; ?></p></div><?php endif; ?>

	<div id="icon-tools" class="icon32"></div>
	<h2><img src="<?php echo WP_PLUGIN_URL . '/' . str_replace( basename( __FILE__ ), '',plugin_basename( __FILE__ ) ) ?>images/PM-Logo.jpg" /></h2>
	<h3>What is Postmark?</h3>
	<p>This Postmark Approved plugin enables WordPress blogs of any size to deliver and track WordPress notification emails reliably, with minimal setup time and zero maintenance. </p>
	<p>If you don't already have a free Postmark account, <a href="https://postmarkapp.com/sign_up">you can get one in minutes</a>. Every account comes with 1000 free sends.</p>

	<br />

	<h3>Your Postmark Settings</h3>
	<form method="post" action="<?php echo admin_url( 'options-general.php' ); ?>?page=pm_admin">
	  <?php wp_nonce_field( 'save postmark settings','pm_save_nonce' ); ?>
	  <table class="form-table">
		<tbody>
		<tr>
		  <th><label for="pm_enabled">Send using Postmark</label></th>
		  <td><input name="pm_enabled" id="pm_enabled" type="checkbox" value="1"<?php if ( 1 === get_option( 'postmark_enabled' ) ) : echo ' checked="checked"'; endif; ?>/> <span style="font-size:11px;">Sends emails sent using wp_mail via Postmark.</span></td>
		</tr>
		<tr>
		  <th><label for="pm_api_key">Postmark API Key</label></th>
		  <td><input name="pm_api_key" id="pm_api_key" type="text" value="<?php echo get_option( 'postmark_api_key' ); ?>" class="regular-text"/> <br/><span style="font-size:11px;">Your API key is available in the <strong>credentials</strong> screen of your Postmark server. <a href="https://postmarkapp.com/servers/">Create a new server in Postmark</a>.</span></td>
		</tr>
		<tr>
		  <th><label for="pm_sender_address">Sender Email Address</label></th>
		  <td><input name="pm_sender_address" id="pm_sender_address" type="text" value="<?php echo get_option( 'postmark_sender_address' ); ?>" class="regular-text"/> <br/><span style="font-size:11px;">This email needs to be one of your <strong>verified sender signatures</strong>. <br/>It will appear as the "from" email on all outbound messages. <a href="https://postmarkapp.com/signatures">Set one up in Postmark</a>.</span></td>
		</tr>
		<tr>
		  <th><label for="pm_forcehtml">Force HTML</label></th>
		  <td><input name="pm_forcehtml" id="pm_forcehtml" type="checkbox" value="1"<?php if ( 1 === get_option( 'postmark_force_html' ) ) : echo ' checked="checked"'; endif; ?>/> <span style="font-size:11px;">Force all emails to be sent as HTML.</span></td>
		</tr>
		<tr>
		  <th><label for="pm_poweredby">Support Postmark</label></th>
		  <td><input name="pm_poweredby" id="pm_poweredby" type="checkbox" value="1"<?php if ( 1 === get_option( 'postmark_poweredby' ) ) : echo ' checked="checked"'; endif; ?>/> <span style="font-size:11px;">Adds a credit to Postmark at the bottom of emails.</span></td>
		</tr>
		</tbody>
	  </table>
	  <div class="submit">
		<input type="submit" name="submit" value="Save" class="button-primary" />
	  </div>
	</form>

	<br />

	<h3>Test Postmark Sending</h3>
	<form method="post" id="test-form" action="">
	  <table class="form-table">
		<tbody>
		<tr>
		  <th><label for="pm_test_address">Send a Test Email To</label></th>
		  <td> <input name="pm_test_address" id="pm_test_address" type="text" value="<?php echo get_option( 'postmark_sender_address' ); ?>" class="regular-text"/></td>
		</tr>
		</tbody>
	  </table>
	  <div class="submit">
		<input type="submit" name="submit" value="Send Test Email" class="button-primary" />
	  </div>
	</form>

	<p style="margin-top:40px; padding-top:10px; border-top:1px solid #ddd;">This plugin is brought to you by <a href="http://www.postmarkapp.com">Postmark</a> &amp; <a href="http://www.andydev.co.uk/">Andrew Yates</a>.</p>

  </div>

<?php
}

add_action( 'wp_ajax_pm_admin_test', 'pm_admin_test_ajax' );
function pm_admin_test_ajax() {
	$response = pm_send_test();
	wp_send_json( $response );
	// End Admin Functionality.
}

// Override wp_mail() if postmark enabled.
if ( 1 === get_option( 'postmark_enabled' ) ) {
	if ( ! function_exists( 'wp_mail' ) ) {
		function wp_mail( $to, $subject, $message, $headers = '', $attachments = array() ) {

			// Define Headers.
			$postmark_headers = array(
				'Accept' => 'application/json',
				'Content-Type' => 'application/json',
				'X-Postmark-Server-Token' => get_option( 'postmark_api_key' ),
			);

			// If "Support Postmark" is on.
			if ( 1 === get_option( 'postmark_poweredby' ) ) {
				// Check Content Type.
				if ( ! strpos( $headers, 'text/html' ) ) {
					$message .= "\n\nPostmark solves your WordPress email problems. Send transactional email confidently using http://postmarkapp.com";
				}
			}

			// Send Email.
			if ( ! is_array( $to ) ) {
				$recipients = explode( ',' , $to );
			} else {
				$recipients = $to;
			}

			foreach ( $recipients as $recipient ) {
				// Construct Message.
				$email = array();
				$email['To'] = $recipient;
				$email['From'] = get_option( 'postmark_sender_address' );
				$email['Subject'] = $subject;
				$email['TextBody'] = $message;

				if ( false !== strpos( $headers, 'text/html' ) || 1 === get_option( 'postmark_force_html' ) ) {
					$email['HtmlBody'] = $message;
				}

				$response = pm_send_mail( $postmark_headers, $email );
			}
			return ( isset( $response ) ) ? $response : '';
		}
	}
}


function pm_send_test() {
	$email_address = $_POST['email'];

	// Define Headers.
	$postmark_headers = array(
		'Accept' => 'application/json',
		'Content-Type' => 'application/json',
		'X-Postmark-Server-Token' => get_option( 'postmark_api_key' ),
	);

	$message = 'This is a test email sent via Postmark from '.get_bloginfo( 'name' ) . '.';
	$html_message = 'This is a test email sent via <strong>Postmark</strong> from '.get_bloginfo( 'name' ).'.';

	if ( 1 === get_option( 'postmark_poweredby' ) ) {
		$message .= "\n\nPostmark solves your WordPress email problems. Send transactional email confidently using http://postmarkapp.com";
		$html_message .= '<br /><br />Postmark solves your WordPress email problems. Send transactional email confidently using <a href="http://postmarkapp.com">Postmark</a>.';
	}

	$email = array();
	$email['To'] = $email_address;
	$email['From'] = get_option( 'postmark_sender_address' );
	$email['Subject'] = get_bloginfo( 'name' ) . ' Postmark Test';
	$email['TextBody'] = $message;

	if ( 1 === get_option( 'postmark_force_html' ) ) {
		$email['HtmlBody'] = $html_message;
	}

	$response = pm_send_mail( $postmark_headers, $email );

	if ( false === $response ) {
		return 'Test Failed with Error';
	} else {
		return 'Test Sent';
	}
}


function pm_send_mail( $headers, $email ) {
	$args = array(
		'headers' => $headers,
		'body' => json_encode( $email ),
	);
	$response = wp_remote_post( POSTMARK_ENDPOINT, $args );

	// To prevent the error : "PHP Fatal error:  Cannot use object of type WP_Error as array" if $response is a WP_Error object.
	if ( is_wp_error( $response ) ) {
		return false;
	}

	if ( 200 === $response['response']['code'] ) {
		return true;
	} else {
		return false;
	}
}
?>
