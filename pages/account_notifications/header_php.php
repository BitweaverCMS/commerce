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
// $Id: header_php.php,v 1.7 2005/11/30 07:46:26 spiderr Exp $
//
  if( !$gBitUser->isRegistered() ) {
    $_SESSION['navigation']->set_snapshot();
    zen_redirect(FILENAME_LOGIN);
  }

  require_once(DIR_FS_MODULES . 'require_languages.php');

  $global_query = "select global_product_notifications
                   from   " . TABLE_CUSTOMERS_INFO . "
                   where  customers_info_id = '" . (int)$_SESSION['customer_id'] . "'";

  $global = $db->Execute($global_query);

	if (isset($_POST['action']) && ($_POST['action'] == 'process')) {
		if (isset($_POST['product_global']) && is_numeric($_POST['product_global'])) {
			$product_global = zen_db_prepare_input($_POST['product_global']);
		} else {
			$product_global = '0';
		}

		(array)$products = $_POST['products'];

		if ($product_global != $global->fields['global_product_notifications']) {
			$product_global = (($global->fields['global_product_notifications'] == '1') ? '0' : '1');

			$sql = "update " . TABLE_CUSTOMERS_INFO . "
					set    global_product_notifications = '" . (int)$product_global . "'
					where  customers_info_id = '" . (int)$_SESSION['customer_id'] . "'";

			$db->Execute($sql);

		}
		if( count($products) > 0 ) {
			$products_parsed = array();
			for ($i=0, $n=count($products); $i<$n; $i++) {
				if (is_numeric($products[$i])) {
				$products_parsed[] = $products[$i];
				}
			}
			if (count($products_parsed) > 0) {
				$check_query = "select count(*) as `total`
								from   " . TABLE_PRODUCTS_NOTIFICATIONS . "
								where  `customers_id` = '" . (int)$_SESSION['customer_id'] . "'
								and    `products_id` not in (" . implode(',', $products_parsed) . ")";

				$check = $db->Execute($check_query);

				if ($check->fields['total'] > 0) {
				$sql = "delete from " . TABLE_PRODUCTS_NOTIFICATIONS . "
						where       `customers_id` = '" . (int)$_SESSION['customer_id'] . "'
						and         `products_id` not in (" . implode(',', $products_parsed) . ")";

				$db->Execute($sql);
				}
			}
		} else {
			$check_query = "select count(*) as `total`
							from   " . TABLE_PRODUCTS_NOTIFICATIONS . "
							where  `customers_id` = '" . (int)$_SESSION['customer_id'] . "'";


			$check = $db->Execute($check_query);

			if ($check->fields['total'] > 0) {
				$sql = "delete from " . TABLE_PRODUCTS_NOTIFICATIONS . "
						where `customers_id` = '" . (int)$_SESSION['customer_id'] . "'";

				$db->Execute($sql);

			}
		}

		$messageStack->add_session('account', SUCCESS_NOTIFICATIONS_UPDATED, 'success');

		zen_redirect(zen_href_link(FILENAME_ACCOUNT, '', 'SSL'));
	}

  $products_check_query = "select count(*) as `total`
                           from   " . TABLE_PRODUCTS_NOTIFICATIONS . "
                           where  `customers_id` = '" . (int)$_SESSION['customer_id'] . "'";

  $products_check = $db->Execute($products_check_query);
  if ($products_check->fields['total'] > 0) $flag_products_check = true;

  $breadcrumb->add(NAVBAR_TITLE_1, zen_href_link(FILENAME_ACCOUNT, '', 'SSL'));
  $breadcrumb->add(NAVBAR_TITLE_2);
?>