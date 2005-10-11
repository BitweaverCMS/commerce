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
// $Id: header_php.php,v 1.8 2005/10/11 03:50:14 spiderr Exp $
//
  if (!$_SESSION['customer_id']) {
    $_SESSION['navigation']->set_snapshot();
    zen_redirect(FILENAME_LOGIN);
  }

  require(DIR_FS_MODULES . 'require_languages.php');

  $product_info_query = "select p.`products_id`, p.`products_model`, p.`products_image`,
                                p.`products_price`, p.`products_tax_class_id`, pd.`products_name`
                         from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd
                         where p.`products_id` = '" . (int)$_GET['products_id'] . "'
                         and p.`products_status` = '1'
                         and p.`products_id` = pd.`products_id`
                         and pd.`language_id` = '" . (int)$_SESSION['languages_id'] . "'";

  $product_info = $db->Execute($product_info_query);

  if (!$product_info->RecordCount()) {
    zen_redirect(zen_href_link(FILENAME_PRODUCT_REVIEWS, zen_get_all_get_params(array('action'))));
  }

  $customer_query = "select `customers_firstname`, `customers_lastname`, `customers_email_address`
                     from " . TABLE_CUSTOMERS . "
                     where `customers_id` = '" . (int)$_SESSION['customer_id'] . "'";


  $customer = $db->Execute($customer_query);

  if (isset($_GET['action']) && ($_GET['action'] == 'process')) {
    $rating = zen_db_prepare_input($_POST['rating']);
    $review_text = zen_db_prepare_input($_POST['review_text']);

    $error = false;
    if (strlen($review_text) < REVIEW_TEXT_MIN_LENGTH) {
      $error = true;

      $messageStack->add('review_text', JS_REVIEW_TEXT);
    }

    if (($rating < 1) || ($rating > 5)) {
      $error = true;

      $messageStack->add('review_text', JS_REVIEW_RATING);
    }

    if ($error == false) {
      if (REVIEWS_APPROVAL == '1') {
        $review_status = '0';
      } else {
        $review_status = '1';
      }

      $sql = "insert into " . TABLE_REVIEWS . "
                              (products_id, customers_id, customers_name, reviews_rating, `date_added`, status)
                     values ('" . (int)$_GET['products_id'] . "', '" . (int)$_SESSION['customer_id'] . "', '" .
                             zen_db_input($customer->fields['customers_firstname']) . ' ' .
                             zen_db_input($customer->fields['customers_lastname']) . "', '" .
                             zen_db_input($rating) . "', now(), " . zen_db_input($review_status) . ")";

      $rs = $db->Execute($sql);

      $insert_id = zen_db_insert_id( TABLE_REVIEWS, 'reviews_id' );

      $sql = "insert into " . TABLE_REVIEWS_DESCRIPTION . "
                          (reviews_id, languages_id, reviews_text)
                     values ('" . (int)$insert_id . "', '" . (int)$_SESSION['languages_id'] . "', '" .
                             zen_db_input($review_text) . "')";

      $db->Execute($sql);
// send review-notification email to admin
      if (REVIEWS_APPROVAL == '1' && SEND_EXTRA_REVIEW_NOTIFICATION_EMAILS_TO_STATUS == '1' and defined('SEND_EXTRA_REVIEW_NOTIFICATION_EMAILS_TO') and SEND_EXTRA_REVIEW_NOTIFICATION_EMAILS_TO !='') {
        $email_text  = sprintf(EMAIL_PRODUCT_REVIEW_CONTENT_INTRO, $product_info->fields['products_name']) . "\n\n" ;
        $email_text .= sprintf(EMAIL_PRODUCT_REVIEW_CONTENT_DETAILS, $review_text)."\n\n";
        $email_subject = sprintf(EMAIL_REVIEW_PENDING_SUBJECT,$product_info->fields['products_name']);
        $html_msg['EMAIL_SUBJECT'] = sprintf(EMAIL_REVIEW_PENDING_SUBJECT,$product_info->fields['products_name']);
        $html_msg['EMAIL_MESSAGE_HTML'] = str_replace('\n','',sprintf(EMAIL_PRODUCT_REVIEW_CONTENT_INTRO, $product_info->fields['products_name']));
        $html_msg['EMAIL_MESSAGE_HTML'] .= '<br />';
        $html_msg['EMAIL_MESSAGE_HTML'] .= str_replace('\n','',sprintf(EMAIL_PRODUCT_REVIEW_CONTENT_DETAILS, $review_text));
	   $extra_info=email_collect_extra_info($name,$email_address, $customer->fields['customers_firstname'] . ' ' . $customer->fields['customers_lastname'] , $customer->fields['customers_email_address'] );
       $html_msg['EXTRA_INFO'] = $extra_info['HTML'];
       zen_mail('', SEND_EXTRA_REVIEW_NOTIFICATION_EMAILS_TO, $email_subject ,
             $email_text . $extra_info['TEXT'], STORE_NAME, EMAIL_FROM, $html_msg, 'reviews_extra');
      }
// end send email

      zen_redirect(zen_href_link(FILENAME_PRODUCT_REVIEWS, zen_get_all_get_params(array('action'))));
    }
  }

  $products_price = zen_get_products_display_price($product_info->fields['products_id']);

  $products_name = $product_info->fields['products_name'];

  if ($product_info->fields['products_model'] != '') {
    $products_model = '<br /><span class="smallText">[' . $product_info->fields['products_model'] . ']</span>';
  } else {
    $products_model = '';
  }

// set image
//  $products_image = $product_info->fields['products_image'];
  if ($product_info->fields['products_image'] == '' and PRODUCTS_IMAGE_NO_IMAGE_STATUS == '1') {
    $products_image = PRODUCTS_IMAGE_NO_IMAGE;
  } else {
    $products_image = $product_info->fields['products_image'];
  }

  $breadcrumb->add(NAVBAR_TITLE);
?>
