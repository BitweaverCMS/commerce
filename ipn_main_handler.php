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
// |                                                                      |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available through the world-wide-web at the following url:           |
// | http://www.zen-cart.com/license/2_0.txt.                             |
// | If you did not receive a copy of the zen-cart license and are unable |
// | to obtain it through the world-wide-web, please send a note to       |
// | license@zen-cart.com so we can mail you a copy immediately.          |
// +----------------------------------------------------------------------+
//  $Id: ipn_main_handler.php,v 1.2 2005/07/05 16:44:02 spiderr Exp $
//
DEFINE('MODULE_PAYMENT_PAYPAL_DOMAIN', 'www.paypal.com');
DEFINE('MODULE_PAYMENT_PAYPAL_HANDLER', '/cgi-bin/webscr');

if (!is_array($_POST)) {
  if (MODULE_PAYMENT_PAYPAL_IPN_DEBUG == 'Yes') mail(STORE_OWNER_EMAIL_ADDRESS,'IPN DEBUG MESSAGE', '999. Empty Post Array - Possible Security Alert');
  die();
}
$session_post = $_POST['custom'];
$session_stuff = explode('=', $session_post);
$_POST[$session_stuff[0]] = $session_stuff[1];
require('includes/modules/payment/paypal/ipn_application_top.php');

if (MODULE_PAYMENT_PAYPAL_IPN_DEBUG == 'Yes') mail(STORE_OWNER_EMAIL_ADDRESS,'IPN DEBUG MESSAGE', '1. Got past Application_top.php');
require('includes/languages/english/checkout_process.php');

//Parse url
$web=parse_url('https://' . MODULE_PAYMENT_PAYPAL_DOMAIN . MODULE_PAYMENT_PAYPAL_HANDLER);
 
if (MODULE_PAYMENT_PAYPAL_IPN_DEBUG == 'Yes') mail(STORE_OWNER_EMAIL_ADDRESS,'IPN DEBUG MESSAGE', '2. Got past parse_url');

//build post string 
foreach($_POST as $i=>$v) { 
  $postdata.= $i . "=" . urlencode($v) . "&";  
}
if (MODULE_PAYMENT_PAYPAL_IPN_DEBUG == 'Yes') mail(STORE_OWNER_EMAIL_ADDRESS,'IPN DEBUG MESSAGE', '3. Got past foreach');

$postdata.="cmd=_notify-validate";

//Set the port number
if($web['scheme'] == "https") { 
  $web['port']="443";  $ssl="ssl://"; } else { $web['port']="80"; 
}  

//Create paypal connection
$fp=@fsockopen($ssl . $web['host'],$web['port'],$errnum,$errstr,30); 
if (MODULE_PAYMENT_PAYPAL_IPN_DEBUG == 'Yes') mail(STORE_OWNER_EMAIL_ADDRESS,'IPN DEBUG MESSAGE', '4. Got past fsockopen');

if(!$fp) { 
  if (MODULE_PAYMENT_PAYPAL_IPN_DEBUG == 'Yes') mail(STORE_OWNER_EMAIL_ADDRESS,'IPN DEBUG MESSAGE', '5. Failed fsockopen ' .  $ssl . $web['host'] . '#');
} else { 
  if (MODULE_PAYMENT_PAYPAL_IPN_DEBUG == 'Yes') mail(STORE_OWNER_EMAIL_ADDRESS,'IPN DEBUG MESSAGE', '6. in main process');

//Post Data
 
  fputs($fp, "POST $web[path] HTTP/1.1\r\n"); 
  fputs($fp, "Host: $web[host]\r\n"); 
  fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n"); 
  fputs($fp, "Content-length: ".strlen($postdata)."\r\n"); 
  fputs($fp, "Connection: close\r\n\r\n"); 
  fputs($fp, $postdata . "\r\n\r\n"); 

//loop through the response from the server 
  while(!feof($fp)) { 
    $info[]=@fgets($fp, 1024); 
  } 

//close fp - we are done with it 
  fclose($fp); 
  if (MODULE_PAYMENT_PAYPAL_IPN_DEBUG == 'Yes') mail(STORE_OWNER_EMAIL_ADDRESS,'IPN DEBUG MESSAGE', '7. closed ipn');

//break up results into a string
  $info = implode(",",$info); 
}

require(DIR_WS_CLASSES . 'shipping.php');
require(DIR_WS_CLASSES . 'payment.php');
$payment_modules = new payment($_SESSION['payment']);
$shipping_modules = new shipping($_SESSION['shipping']);
require(DIR_WS_CLASSES . 'order.php');
$order = new order();
if (MODULE_PAYMENT_PAYPAL_IPN_DEBUG == 'Yes') mail(STORE_OWNER_EMAIL_ADDRESS,'IPN DEBUG MESSAGE', '7.1 Started Order ' . $_SESSION['payment'] );
require(DIR_WS_CLASSES . 'order_total.php');
$order_total_modules = new order_total();
$order_totals = $order_total_modules->process();


if (isset($_SESSION['customer_id'])) $new_order_id = $order->create($order_totals);

if (MODULE_PAYMENT_PAYPAL_IPN_DEBUG == 'Yes') mail(STORE_OWNER_EMAIL_ADDRESS,'IPN DEBUG MESSAGE', '8 Created Order ' . $new_order_id );

if(eregi("VERIFIED",$info) && $_POST['txn_type'] == 'web_accept' && $_POST['business'] == MODULE_PAYMENT_PAYPAL_BUSINESS_ID)  { 
  if (MODULE_PAYMENT_PAYPAL_IPN_DEBUG == 'Yes') mail(STORE_OWNER_EMAIL_ADDRESS,'IPN DEBUG MESSAGE', 'Order Verified ' . $new_order_id );
  // do success stuff here
  // Things to do
  // create paypal database entry
  $paypal_order = array(
                        zen_order_id => $new_order_id,
                        txn_type => $_POST['txn_type'],
                        reason_code => $_POST['reason_code'],
                        payment_type => $_POST['payment_type'],
                        payment_status => $_POST['payment_status'],
                        pending_reason => $_POST['pending_reason'],
                        invoice => $_POST['invoice'],
                        mc_currency => $_POST['mc_currency'],
                        first_name => $_POST['first_name'],
                        last_name => $_POST['last_name'],
                        payer_business_name => $_POST['payer_business_name'],
                        address_name => $_POST['address_name'],
                        address_street => $_POST['addrss_street'],
                        address_city => $_POST['address_city'],
                        address_state => $_POST['address_state'],
                        address_zip => $_POST['address_zip'],
                        address_country => $_POST['address_country'],
                        address_status => $_POST['address_status'],
                        payer_email => $_POST['payer_email'],
                        payer_id => $_POST['payer_id'],
                        payer_status => $_POST['payer_status'],
                        payment_date => $_POST['payment_date'],
                        business => $_POST['business'],
                        receiver_email => $_POST['receiver_email'],
                        receiver_id => $_POST['receiver_id'],
                        txn_id => $_POST['txn_id'],
                        parent_txn_id => $_POST['parent_txn_id'],
                        num_cart_items => $_POST['num_cart_items'],
                        mc_gross => $_POST['mc_gross'],
                        mc_fee => $_POST['mc_fee'],
                        settle_amount => $_POST['settle_amount'],
                        settle_currency => $_POST['settle_currency'],
                        exchange_rate => $_POST['exchange_rate'],
                        notify_version => $_POST['notify_version'],
                        verify_sign => $_POST['verify_sign'],
                        last_modified => $_POST['last_modified'],
                        date_added => $_POST['date_added'],
                        memo => $_POST['memo']
                        );
                        
  if (MODULE_PAYMENT_PAYPAL_IPN_DEBUG == 'Yes')  mail(STORE_OWNER_EMAIL_ADDRESS,'IPN DEBUG MESSAGE', '10. Created Order Array' );

  $db->associateInsert(TABLE_PAYPAL, $paypal_order);

  if (MODULE_PAYMENT_PAYPAL_IPN_DEBUG == 'Yes') mail(STORE_OWNER_EMAIL_ADDRESS,'IPN DEBUG MESSAGE', '11. Got past Create DB Array');

  if (isset($_SESSION['customer_id'])) {
    $order->create_add_products($new_order_id, 2);
    if (MODULE_PAYMENT_PAYPAL_IPN_DEBUG == 'Yes') mail(STORE_OWNER_EMAIL_ADDRESS,'IPN DEBUG MESSAGE', '12. Finalised Order');
    $order->send_order_email($new_order_id, 2);
    $_SESSION['cart']->reset(true);
  }
  if (MODULE_PAYMENT_PAYPAL_IPN_DEBUG == 'Yes') mail(STORE_OWNER_EMAIL_ADDRESS,'IPN DEBUG MESSAGE', '13. Sent Email');



} else { 
  if (MODULE_PAYMENT_PAYPAL_IPN_DEBUG == 'Yes') mail(STORE_OWNER_EMAIL_ADDRESS,'IPN DEBUG MESSAGE', '14. Catastrophic Failure' . '#'.$_POST['business'].'#'.MODULE_PAYMENT_PAYPAL_BUSINESS_ID.'#'.$_POST['txn_type'].'#'.$info);
} 
?>