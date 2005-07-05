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
// $Id: gv_faq.php,v 1.1 2005/07/05 05:59:02 bitweaver Exp $
//

define('NAVBAR_TITLE', TEXT_GV_NAMES . ' FAQ');
define('HEADING_TITLE', TEXT_GV_NAMES . ' FAQ');

define('TEXT_INFORMATION', '<ul>
  <li><a href="'.zen_href_link(FILENAME_GV_FAQ,'faq_item=1','NONSSL').'">Purchasing ' . TEXT_GV_NAMES . '</a></li>
  <li><a href="'.zen_href_link(FILENAME_GV_FAQ,'faq_item=2','NONSSL').'">How to send ' . TEXT_GV_NAMES . '</a></li>
  <li><a href="'.zen_href_link(FILENAME_GV_FAQ,'faq_item=3','NONSSL').'">Buying with ' . TEXT_GV_NAMES . '</a></li>
  <li><a href="'.zen_href_link(FILENAME_GV_FAQ,'faq_item=4','NONSSL').'">Redeeming ' . TEXT_GV_NAMES . '</a></li>
  <li><a href="'.zen_href_link(FILENAME_GV_FAQ,'faq_item=5','NONSSL').'">When problems occur</a></li>
</ul>');
switch ($_GET['faq_item']) {
  case '1':
define('SUB_HEADING_TITLE','Purchasing ' . TEXT_GV_NAMES);
define('SUB_HEADING_TEXT','<p>' . TEXT_GV_NAMES . ' are purchased just like any other item in our store. You can
  pay for them using the stores standard payment method(s).</p>
  <p>Once purchased the value of the ' . TEXT_GV_NAME . ' will be added to your own personal
  ' . TEXT_GV_NAME . ' Account. If you have funds in your ' . TEXT_GV_NAME . ' Account, you will
  notice that the amount now shows in he Shopping Cart box, and also provides a
  link to a page where you can send the ' . TEXT_GV_NAME . ' to some one via email.</p>');
  break;
  case '2':
define('SUB_HEADING_TITLE','How to Send ' . TEXT_GV_NAMES);
define('SUB_HEADING_TEXT','<p>To send a ' . TEXT_GV_NAME  . ' you need to go to our Send ' . TEXT_GV_NAME . ' Page. You can
  find the link to this page in the Shopping Cart Box in the right hand column of
  each page.</p>
  <p>When you send a ' . TEXT_GV_NAME . ', you need to specify the following:</p>
  <ul>
  <li>The name of the person you are sending the ' . TEXT_GV_NAME . ' to.</li>
  <li>The email address of the person you are sending the ' . TEXT_GV_NAME . ' to.</li>
  <li>The amount you want to send. (Note you don\'t have to send the full amount that
  is in your ' . TEXT_GV_NAME . ' Account.)</li>
  <li>A short message which will apear in the email.</li>
  </ul>
  <p>Please ensure that you have entered all of the information correctly, although
  you will be given the opportunity to change this as much as you want before
  the email is actually sent.</p>');
  break;
  case '3':
  define('SUB_HEADING_TITLE','Buying with ' . TEXT_GV_NAMES);
  define('SUB_HEADING_TEXT','<p>If you have funds in your ' . TEXT_GV_NAME . ' Account, you can use those funds to
  purchase other items in out store. At the checkout stage, an extra box will
  appear. Enter the amount to apply from the funds in your ' . TEXT_GV_NAME . ' Account.</p>
  <p>Please note, you will still have to select another payment method if there
  is not enough in your ' . TEXT_GV_NAME . ' Account to cover the cost of your purchase.
  If you have more funds in your ' . TEXT_GV_NAME . ' Account than the total cost of
  your purchase the balance will be left in your ' . TEXT_GV_NAME . ' Account for the
  future.</p>');
  break;
  case '4':
  define('SUB_HEADING_TITLE','Redeeming ' . TEXT_GV_NAMES);
  define('SUB_HEADING_TEXT','<p>If you receive a ' . TEXT_GV_NAME  . ' by email it will contain details of who sent
  you the ' . TEXT_GV_NAME . ', along with possibly a short message from them. The Email
  will also contain the ' . TEXT_GV_NAME . ' ' . TEXT_GV_REDEEM . '. It is probably a good idea to print
  out this email for future reference. You can now redeem the  ' . TEXT_GV_NAME . ' in
  two ways.</p>
  <ol><li>By clicking on the link containe within the email for this express purpose.
  This will take you to the store\'s Redeem  ' . TEXT_GV_NAME . ' page. You will the be requested
  to create an account, before the ' . TEXT_GV_NAME . ' is validated and placed in your
   ' . TEXT_GV_NAME . ' Account ready for you to spend it on whatever you want.</li>
  <li>During the checkout procces, on the same page that you select a payment method
there will be a box to enter a ' . TEXT_GV_REDEEM . ' ' . TEXT_GV_REDEEM . '. Enter the ' . TEXT_GV_REDEEM . ' here, and click the redeem button. The ' . TEXT_GV_REDEEM . ' will be
validated and added to your ' . TEXT_GV_NAME . ' account. You Can then use the amount to purchase any item from our store</li></ol>');
  break;
  case '5':
  define('SUB_HEADING_TITLE','When problems occur');
  define('SUB_HEADING_TEXT','<p>For any queries regarding the ' . TEXT_GV_NAME . ' System, please contact the store
  by email at '. STORE_OWNER_EMAIL_ADDRESS . '. Please make sure you give
  as much information as possible in the email. </p>');
  break;
  default:
  define('SUB_HEADING_TITLE','');
  define('SUB_HEADING_TEXT','<p>Please choose from one of the questions above.</p>');

  }
?>