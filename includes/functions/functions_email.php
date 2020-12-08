<?php
//
// +----------------------------------------------------------------------+
// |zen-cart Open Source E-commerce                                       |
// +----------------------------------------------------------------------+
// | Copyright (c) 2003 The zen-cart developers                           |
// |                                                                      |
// | http://www.zen-cart.com/index.php                                    |
// |                                                                      |
// | Portions Copyright (c) 2003 osCommerce                               |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available through the world-wide-web at the following url:           |
// | http://www.zen-cart.com/license/2_0.txt.                             |
// | If you did not receive a copy of the zen-cart license and are unable |
// | to obtain it through the world-wide-web, please send a note to       |
// | license@zen-cart.com so we can mail you a copy immediately.          |
// +----------------------------------------------------------------------+
// $Id$
//
//
define('EMAIL_SYSTEM_DEBUG','off');
////
// Send email (text/html) using MIME
// This is the central mail function. The SMTP Server should be configured correctly in php.ini
// Parameters:
// $to_name           The name of the recipient, e.g. "Jan Wildeboer"
// $to_email_address  The eMail address of the recipient, e.g. jan.wildeboer@gmx.de  (used as $to_email_address after validation)
// $email_subject     The subject of the eMail
// $email_text        The text of the eMail, may contain HTML entities
// $from_email_name   The name of the sender, e.g. Shop Administration
// $from_email_adress The eMail address of the sender, e.g. info@myzenshop.com
// $block             Array containing values to be inserted into HTML-based email template
// $module            The module name of the routine calling zen_mail. Used for html template selection and email archiving.
//                    This is passed to the archive function denoting what module initiated the sending of the email
// $attachments_list  Array of attachment names/mime-types to be included  (this portion still in testing, and not fully reliable)

	function zen_mail($to_name, $to_address, $email_subject, $email_text, $from_email_name, $from_email_address, $block=array(), $module='default', $attachments_list='', $pFormat='' ) {
		global $gCommerceSystem, $gBitDb, $messageStack;
		if (SEND_EMAILS != 'true') return false;  // if sending email is disabled in Admin, just exit
		if (!zen_not_null($email_text) && !zen_not_null($block['EMAIL_MESSAGE_HTML'])) return false;  // if no text or html-msg supplied, exit

		// Parse "from" addresses for "name" <email@address.com> structure, and supply name/address info from it.
		if ( preg_match("/ *([^<]*) *<([^>]*)> */i",$from_email_address,$regs)) {
			$from_email_name = trim($regs[1]);
			$from_email_address = $regs[2];
		}
		// if email name is same as email address, use the Store Name as the senders 'Name'
		if ($from_email_name == $from_email_address) $from_email_name = STORE_NAME;

		// loop thru multiple email recipients if more than one listed  --- (esp for the admin's "Extra" emails)...
		foreach(explode(',',$to_address) as $key=>$value) {
			if (preg_match("/ *([^<]*) *<([^>]*)> */i",$value,$regs)) {
				$to_name = str_replace('"', '', trim($regs[1]));
				$to_email_address = $regs[2];
			} elseif (preg_match("/ *([^ ]*) */i",$value,$regs)) {
				$to_email_address = trim($regs[1]);
			}
			if (!isset($to_email_address)) $to_email_address=$to_address; //if not more than one, just use the main one.

			//define some additional html message blocks available to templates, then build the html portion.
			if( empty( $block['EMAIL_TO_NAME'] ) )      $block['EMAIL_TO_NAME'] = $to_name;
			if( empty( $block['EMAIL_TO_ADDRESS'] ) )   $block['EMAIL_TO_ADDRESS'] = $to_email_address;
			if( empty( $block['EMAIL_SUBJECT'] ) )      $block['EMAIL_SUBJECT'] = $email_subject;
			if( empty( $block['EMAIL_FROM_NAME'] ) )    $block['EMAIL_FROM_NAME'] = $from_email_name;
			if( empty( $block['EMAIL_FROM_ADDRESS'] ) ) $block['EMAIL_FROM_ADDRESS'] = $from_email_address;
			$email_html = zen_build_html_email_from_template($module, $block);


			//  if ($attachments_list == '') $attachments_list= array();

			// Instantiate a new mail object
			$message = new email(array('X-Mailer: Bitcommerce'));

			// bof: body of the email clean-up
			// clean up &amp; and && from email text
			while (strstr($email_text, '&amp;&amp;')) $email_text = str_replace('&amp;&amp;', '&amp;', $email_text);
			while (strstr($email_text, '&amp;')) $email_text = str_replace('&amp;', '&', $email_text);
			while (strstr($email_text, '&&')) $email_text = str_replace('&&', '&', $email_text);

			// clean up money &euro; to e
			while (strstr($email_text, '&euro;')) $email_text = str_replace('&euro;', 'e', $email_text);

			// fix double quotes
			while (strstr($email_text, '&quot;')) $email_text = str_replace('&quot;', '"', $email_text);

			// fix slashes
			$email_text = stripslashes($email_text);
			// eof: body of the email clean-up

			//determine customer's email preference type: HTML or TEXT-ONLY  (HTML assumed if not specified)
			$customers_email_format = $gBitDb->getOne("select customers_email_format from " . TABLE_CUSTOMERS . " where customers_email_address = ?", array( $to_email_address ) );
			if( !empty( $pFormat ) ) { 
				$customers_email_format=$pFormat;
			} elseif ($customers_email_format=='NONE' || $customers_email_format=='OUT') {
				return; //if requested no mail, then don't send.
			} elseif( empty( $customers_email_format ) ) {
				$customers_email_format='HTML'; // Default to HTML messages, then send HTML format
			}

			//determine what format to send messages in if this is an "extra"/admin-copy email:
			if (ADMIN_EXTRA_EMAIL_FORMAT == 'TEXT' && substr($module,-6)=='_extra') {
				$email_html='';  // just blank out the html portion if admin has selected text-only
			}

			// Build the email based on whether customer has selected HTML or TEXT, and whether we have supplied HTML or TEXT-only components
// $customers_email_format = 'TEXT';  // FORCE to text messages
			// Build the email based on whether customer has selected HTML or TEXT, and whether we have supplied HTML or TEXT-only components
			if (!zen_not_null($email_text)) {
				$text = str_replace('<br[[:space:]]*/?[[:space:]]*>', "@CRLF", $block['EMAIL_MESSAGE_HTML']);
				$text = str_replace('</p>', '</p>@CRLF', $text);
				$text = htmlspecialchars(stripslashes(strip_tags($text)));
			} else {
				$text = strip_tags($email_text);
			}

			if ( $gCommerceSystem->isConfigActive( 'EMAIL_USE_HTML' ) && !empty( $email_html ) && ($customers_email_format == 'HTML' || (ADMIN_EXTRA_EMAIL_FORMAT != 'TEXT' && substr($module,-6)=='_extra'))) {
				$message->add_html($email_html, $text);
			} else {
				$message->add_text($text);
				$email_html=''; // since sending a text-only message, empty the HTML portion so it's not archived either.
			}

			// process attachments
			if ( defined( 'EMAIL_ATTACHMENTS_ENABLED' ) && EMAIL_ATTACHMENTS_ENABLED && zen_not_null($attachments_list) ) {
//				while ( list($key, $value) = each($attachments_list)) {
				$fileraw = $message->get_file(DIR_FS_ADMIN.'attachments/'.$attachments_list['file']);
				$filemime = ((zen_not_null($attachments_list['file_type']) ) ? $attachments_list['file_type'] : $message->findMime($attachments_list) );    //findMime determines what type this attachment is (XLS, PDF, etc) and sends proper vendor c_type.
				$message->add_attachment($fileraw, $attachments_list['file'], $filemime);
//				} //endwhile attach_list
			} //endif attachments

			// Prepare message
			$message->build_message();
			// send the actual email
			$result = $message->send($to_name, $to_email_address, $from_email_name, $from_email_address, $email_subject);

			if (!$result && $messageStack) {
				$messageStack->add(sprintf(EMAIL_SEND_FAILED, $to_name, $to_email_address, $email_subject),'error');
			}

			// Archive this message to storage log
			if (EMAIL_ARCHIVE == 'true'  && $module != 'password_forgotten_admin' && $module != 'cc_middle_digs') {  // don't archive pwd-resets and CC numbers
				zen_mail_archive_write($to_name, $to_email_address, $from_email_name, $from_email_address, $email_subject, $email_html, $text, $module );
			} // endif archiving
		} // end foreach loop thru possible multiple email addresses
	}  // end function

	function zen_mail_archive_write($to_name, $to_email_address, $from_email_name, $from_email_address, $email_subject, $email_html, $email_text, $module) {
	// this function stores sent emails into a table in the database as a log record of email activity.  This table CAN get VERY big!
	// To disable this function, set the "Email Archives" switch to 'false' in ADMIN!
		global $gBitDb;
		$to_name = zen_db_prepare_input($to_name);
		$to_email_address = zen_db_prepare_input($to_email_address);
		$from_email_name = zen_db_prepare_input($from_email_name);
		$from_email_address = zen_db_prepare_input($from_email_address);
		$email_subject = zen_db_prepare_input($email_subject);
		$email_html = (EMAIL_USE_HTML=='true') ? zen_db_prepare_input($email_html) : zen_db_prepare_input('HTML disabled in admin');
		$email_text = zen_db_prepare_input($email_text);
		$module = zen_db_prepare_input($module);

		$gBitDb->query( "insert into " . TABLE_EMAIL_ARCHIVE . " (email_to_name, email_to_address, email_from_name, email_from_address, email_subject, email_html, email_text, date_sent, module) values (?,?,?,?,?,?,?,?,?)",
						 array( zen_db_input($to_name), zen_db_input($to_email_address), zen_db_input($from_email_name), zen_db_input($from_email_address), zen_db_input($email_subject), zen_db_input($email_html), zen_db_input($email_text), $gBitDb->qtNOW(), zen_db_input($module) ) );
		return $gBitDb;
	}

	//DEFINE EMAIL-ARCHIVABLE-MODULES LIST // this array will likely be used by the email archive log VIEWER module in future
		$emodules_array = array();
		$emodules_array[] = array('id' => 'newsletters', 'text' => 'Newsletters');
		$emodules_array[] = array('id' => 'product_notification', 'text' => 'Product Notifications');
		$emodules_array[] = array('id' => 'direct_email', 'text' => 'One-Time Email');
		$emodules_array[] = array('id' => 'contact_us', 'text' => 'Contact Us');
		$emodules_array[] = array('id' => 'coupon', 'text' => 'Send Coupon');
		$emodules_array[] = array('id' => 'coupon_extra', 'text' => 'Send Coupon');
		$emodules_array[] = array('id' => 'gv_queue', 'text' => 'Send-GV-Queue');
		$emodules_array[] = array('id' => 'gv_mail', 'text' => 'Send-GV');
		$emodules_array[] = array('id' => 'gv_mail_extra', 'text' => 'Send-GV-Extra');
		$emodules_array[] = array('id' => 'welcome', 'text' => 'New Customer Welcome');
		$emodules_array[] = array('id' => 'welcome_extra', 'text' => 'New Customer Welcome-Extra');
		$emodules_array[] = array('id' => 'password_forgotten', 'text' => 'Password Forgotten');
		$emodules_array[] = array('id' => 'password_forgotten_admin', 'text' => 'Password Forgotten');
		$emodules_array[] = array('id' => 'checkout', 'text' => 'Checkout');
		$emodules_array[] = array('id' => 'checkout_extra', 'text' => 'Checkout-Extra');
		$emodules_array[] = array('id' => 'order_status', 'text' => 'Order Status');
		$emodules_array[] = array('id' => 'order_status_extra', 'text' => 'Order Status-Extra');
		$emodules_array[] = array('id' => 'low_stock', 'text' => 'Low Stock Notices');
		$emodules_array[] = array('id' => 'cc_middle_digs', 'text' => 'CC - Middle-Digits');
		$emodules_array[] = array('id' => 'tell_a_friend', 'text' => 'Tell-A-Friend');
		$emodules_array[] = array('id' => 'tell_a_friend_extra', 'text' => 'Tell-A-Friend-Extra');
		$emodules_array[] = array('id' => 'purchase_order', 'text' => 'Purchase Order');
		$emodules_array[] = array('id' => 'payment_modules', 'text' => 'Payment Modules');
		$emodules_array[] = array('id' => 'payment_modules_extra', 'text' => 'Payment Modules-Extra');
/////////////////////////////////////////////////////////////////////////////////////////
////////END SECTION FOR EMAIL FUNCTIONS//////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////


// select email template based on 'module' (supplied as param to function)
// selectively go thru each template tag and substitute appropriate text
// finally, build full html content as "return" output from class



	function zen_build_html_email_from_template($module='default',$block) {
		global $gBitSmarty;

		try {
			$gBitSmarty->assign( 'emailVars', $block );
			$tplName = 'bitpackage:'.BITCOMMERCE_PKG_NAME.'/email_template_'.$module.'.tpl';
			$htmlMessage = $gBitSmarty->fetch( $tplName );
		} catch( Exception $e ) {
			// Identify and Read the template file for the type of message being sent
			$template_filename_base = DIR_FS_EMAIL_TEMPLATES . "email_template_";

			if (file_exists($template_filename_base . str_replace(array('_extra','_admin'),'',$module) . '.html')) {
				$template_filename = $template_filename_base . str_replace(array('_extra','_admin'),'',$module) . '.html';
				} elseif (file_exists($template_filename_base . 'default' . '.html')) {
				$template_filename = $template_filename_base . 'default' . '.html';
				} else {
				echo 'ERROR: The email template file for '.$template_filename_base.' or '.$template_filename.' cannot be found.';
				return ''; // couldn't find template file, so return an empty string for html message.
			}

			if (! $fh = fopen($template_filename, 'rb')) {	 // note: the 'b' is for compatibility with Windows systems
				echo 'ERROR: The email template file '.$template_filename_base.' or '.$template_filename.' cannot be opened';
			}

			$htmlMessage = fread($fh, filesize($template_filename));
			fclose($fh);

			//strip linebreaks and tabs out of the template
			$htmlMessage = zen_convert_linefeeds(array("\r\n", "\n", "\r", "\t"), '', $htmlMessage);


			//check for some specifics that need to be included with all messages
			if( empty( $block['EMAIL_STORE_NAME'] ) )
				 $block['EMAIL_STORE_NAME'] = STORE_NAME;
			if( empty( $block['EMAIL_STORE_URL'] ) )			
				$block['EMAIL_STORE_URL'] = '<a href="'. zen_get_root_uri() .'">'.STORE_NAME.'</a>';
			if( empty( $block['EMAIL_STORE_OWNER'] ) )		
				$block['EMAIL_STORE_OWNER']	= STORE_OWNER;
			if( empty( $block['EMAIL_FOOTER_COPYRIGHT'] ) )
				$block['EMAIL_FOOTER_COPYRIGHT'] = EMAIL_FOOTER_COPYRIGHT;
			if( empty( $block['EMAIL_DISCLAIMER'] ) )			 
				$block['EMAIL_DISCLAIMER'] = sprintf(EMAIL_DISCLAIMER, '<a href="mailto:' . STORE_OWNER_EMAIL_ADDRESS . '">'. STORE_OWNER_EMAIL_ADDRESS .' </a>');
			if( empty( $block['EMAIL_SPAM_DISCLAIMER'] ) )
				$block['EMAIL_SPAM_DISCLAIMER']	= EMAIL_SPAM_DISCLAIMER;
			if( empty( $block['BASE_HREF'] ) )						
				$block['BASE_HREF'] = zen_get_page_uri();
			if( empty( $block['EMAIL_DATE_SHORT'] ) )
				$block['EMAIL_DATE_SHORT'] = zen_date_short(date("Y-m-d"));
			if( empty( $block['EMAIL_DATE_LONG'] ) )			
				$block['EMAIL_DATE_LONG'] = zen_date_long(date("Y-m-d"));
			if( empty( $block['GV_LINK_OTHER'] ) )
				$block['GV_LINK_OTHER']	= '';
			if( empty( $block['EXTRA_INFO'] ) || $block['EXTRA_INFO'] =='EXTRA_INFO' )
				$block['EXTRA_INFO'] = '';
			if (substr($module,-6) != '_extra' && $module != 'contact_us')
				$block['EXTRA_INFO'] = '';

			$block['COUPON_BLOCK'] = '';
			if( !empty( $block['COUPON_TEXT_VOUCHER_IS'] ) && !empty( $block['COUPON_TEXT_TO_REDEEM'] ) ) {
				$block['COUPON_BLOCK'] = '<div class="coupon-block">' . $block['COUPON_TEXT_VOUCHER_IS'] . $block['COUPON_DESCRIPTION'] . '<br />' . $block['COUPON_TEXT_TO_REDEEM'] . '<span class="coupon-code">' . $block['COUPON_CODE'] . '</span></div>';
			}

			$block['GV_BLOCK'] = '';
			if ( !empty( $block['GV_WORTH'] ) && !empty( $block['GV_REDEEM'] ) && !empty( $block['GV_CODE_URL'] ) ) {
				$block['GV_BLOCK'] = '<div class="gv-block">' . $block['GV_WORTH'] . '<br />' . $block['GV_REDEEM'] . $block['GV_CODE_URL'] . '<br />' . $block['GV_LINK_OTHER'] . '</div>';
			}

			//prepare the "unsubscribe" link:
			$block['UNSUBSCRIBE_LINK'] = str_replace("\n",'',tra('Unsubscribe')) . ' <a href="' . zen_get_page_uri( FILENAME_UNSUBSCRIBE, "unsubscribe_address=" . $block['EMAIL_TO_ADDRESS'] ) . '">' . zen_get_page_uri( FILENAME_UNSUBSCRIBE, "unsubscribe_address=" . $block['EMAIL_TO_ADDRESS'] ) . '</a>';

			//now replace the $BLOCK_NAME items in the template file with the values passed to this function's array
			foreach ($block as $key=>$value) {
				if( !is_object( $block[$key] ) ) {
					$htmlMessage = str_replace('$' . $key, $value, $htmlMessage);
				}
			}

	//DEBUG -- to display preview on-screen
			if (EMAIL_SYSTEM_DEBUG=='on') echo $htmlMessage;
		}

		return $htmlMessage;
	}

	function email_collect_extra_info($from, $email_from, $login, $login_email, $login_phone='') {

// get host_address from either session or one time for both email types to save server load
	if (!$_SESSION['customers_host_address']) {
		if (SESSION_IP_TO_HOST_ADDRESS == 'true') {
			$email_host_address = gethostbyaddr($_SERVER['REMOTE_ADDR']);
		} else {
			$email_host_address = OFFICE_IP_TO_HOST_ADDRESS;
		}
	} else {
		$email_host_address = $_SESSION['customers_host_address'];
	}

	// generate footer details for "also-send-to" emails
		$extra_info=array();
		$extra_info['TEXT'] =
			OFFICE_USE . "\t" . "\n" .
			OFFICE_FROM . "\t" . $from . "\n" .
			OFFICE_EMAIL. "\t" . $email_from . "\n" .
			OFFICE_LOGIN_NAME . "\t" . $login . "\n" .
			OFFICE_LOGIN_EMAIL . "\t" . $login_email . "\n" .
			($login_phone !='' ? OFFICE_LOGIN_PHONE . "\t" . $login_phone . "\n" : '') .
			OFFICE_IP_ADDRESS . "\t" . $_SERVER['REMOTE_ADDR'] . "\n" .
			OFFICE_HOST_ADDRESS . "\t" . $email_host_address . "\n" .
			OFFICE_DATE_TIME . "\t" . date("D M j Y G:i:s T") . "\n\n";

		$extra_info['HTML'] = '<table class="extra-info">' .
			'<tr><td class="extra-info-bold" colspan="2">' . OFFICE_USE . '</td></tr>' .
			'<tr><td class="extra-info-bold">' . OFFICE_FROM . '</td><td>' . $from . '</td></tr>' .
			'<tr><td class="extra-info-bold">' . OFFICE_EMAIL. '</td><td>' . $email_from . '</td></tr>' .
			'<tr><td class="extra-info-bold">' . OFFICE_LOGIN_NAME . '</td><td>' . $login . '</td></tr>' .
			'<tr><td class="extra-info-bold">' . OFFICE_LOGIN_EMAIL . '</td><td>' . $login_email . '</td></tr>' .
			($login_phone !='' ? '<tr><td class="extra-info-bold">' . OFFICE_LOGIN_PHONE . '</td><td>' . $login_phone . '</td></tr>' : '') .
			'<tr><td class="extra-info-bold">' . OFFICE_IP_ADDRESS . '</td><td>' . $_SERVER['REMOTE_ADDR'] . '</td></tr>' .
			'<tr><td class="extra-info-bold">' . OFFICE_HOST_ADDRESS . '</td><td>' . $email_host_address . '</td></tr>' .
			'<tr><td class="extra-info-bold">' . OFFICE_DATE_TIME . '</td><td>' . date('D M j Y G:i:s T') . '</td></tr>' . '</table>';
		return $extra_info;
	}



//----------------------------------------------------------------------------------------------------
 function smtpmail($mail_to, $subject, $message, $headers = "") {
	global $messageStack;
	 $message = preg_replace("/(?<!\r)\n/si", "\r\n", $message);
	 $mailbox = (EMAIL_SMTPAUTH_MAILBOX) ? EMAIL_SMTPAUTH_MAILBOX : EMAIL_FROM;
	 $mail_to_array = explode(",", $mail_to);

	 if (!defined('EMAIL_SMTPAUTH_MAIL_SERVER_PORT')) define('EMAIL_SMTPAUTH_MAIL_SERVER_PORT','25');

	 if( !$socket = @fsockopen(EMAIL_SMTPAUTH_MAIL_SERVER, EMAIL_SMTPAUTH_MAIL_SERVER_PORT, $errno, $errstr, 20) ) {
			$messageStack->add("ERROR: Could not connect to SMTP host : $errno : $errstr", 'error');
			return false;
	 }

	 server_parse($socket, "220");

	 fputs($socket, "EHLO ".EMAIL_SMTPAUTH_MAIL_SERVER."\r\n");
	 server_parse($socket, "250");
	 fputs($socket, "AUTH LOGIN\r\n");
	 server_parse($socket, "334");
	 fputs($socket, base64_encode($mailbox) . "\r\n");
	 server_parse($socket, "334");
	 fputs($socket, base64_encode(EMAIL_SMTPAUTH_PASSWORD) . "\r\n");
	 server_parse($socket, "235");

	 fputs($socket, "MAIL FROM: <" . EMAIL_FROM . ">\r\n");
	 server_parse($socket, "250");

	 $to_header = "To: ";
	 @reset( $mail_to_array );
	 while( list( , $mail_to_address ) = each( $mail_to_array )) {
			$mail_to_address = trim($mail_to_address);
			if ( preg_match('/[^ ]+\@[^ ]+/', $mail_to_address) ) {
				 fputs( $socket, "RCPT TO: <$mail_to_address>\r\n" );
				 server_parse( $socket, "250" );
			}
			$to_header .= ( ( $mail_to_address != '' ) ? ', ' : '' ) . "<$mail_to_address>";
	 }

	 fputs($socket, "DATA\r\n");
	 server_parse($socket, "354");
	 fputs($socket, "Subject: $subject\r\n");
	 fputs($socket, "$to_header\r\n");
	 fputs($socket, "$headers\r\n\r\n");
	 fputs($socket, "$message\r\n");
	 fputs($socket, ".\r\n");
	 server_parse($socket, "250");

	 fputs($socket, "QUIT\r\n");
	 fclose($socket);

	 return true;
 }

function server_parse($socket, $response) {
	 while(substr($server_response,3,1)!=' ') {
			if(!($server_response=fgets($socket,256))) die("Couldn't get mail server response codes");
	 }
	 if(!(substr($server_response,0,3)==$response)) die("Ran into problems sending Mail. Response: $server_response");
}
//----------------------------------------------------------------------------------------------------

?>
