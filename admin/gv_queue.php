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
//  $Id$
//

  require('includes/application_top.php');


  $currencies = new currencies();

// bof: find gv for a particular order and set page
  if ($_GET['order'] != '') {
    $gv_id = $gBitDb->getOne("select `unique_id`
                                  from " . TABLE_COUPON_GV_QUEUE . "
                                  where `order_id` = '" . $_GET['order'] . "' and `release_flag` = 'N'");

    $_GET['gid'] = $gv_id;

    $gv_page = $gBitDb->Execute("select c.`customers_firstname`, c.`customers_lastname`, gv.`unique_id`, gv.`date_created`, gv.`amount`, gv.`order_id` from " . TABLE_CUSTOMERS . " c, "
		 . TABLE_COUPON_GV_QUEUE . " gv where (gv.`customer_id` = c.`customers_id` and gv.`release_flag` = 'N')"
		 . " order by gv.`order_id`, gv.`unique_id`");
    $page_cnt=1;
    while (!$gv_page->EOF) {
      if ($gv_page->fields['order_id'] == $_GET['order']) {
        break;
      }
      $page_cnt++;
      $gv_page->MoveNext();
    }
    $_GET['page'] = round(($page_cnt/MAX_DISPLAY_SEARCH_RESULTS));
    zen_redirect(zen_href_link_admin(FILENAME_GV_QUEUE, 'gid=' . $gv_id . '&page=' . $_GET['page']));
  }
// eof: find gv for a particular order and set page

	if ($_GET['action'] == 'confirmrelease' && BitBase::verifyId( $_GET['gid'] ) ) {
		$gv = $gBitDb->getRow("select release_flag from " . TABLE_COUPON_GV_QUEUE . " where unique_id=?", array( $_GET['gid'] ) );

		if( $gv['release_flag'] == 'N' && ($gv = $gBitDb->getRow( "select customer_id, amount, order_id from " . TABLE_COUPON_GV_QUEUE . " where unique_id=?", array( $_GET['gid'] ) )) ) {
			$fromUser = new BitUser( $gv['customer_id'] );
			$fromUser->load();

			if( $couponCode = CommerceVoucher::customerSendCoupon( $fromUser, array( 'email'=>$fromUser->getField( 'email' ), 'to_name'=>$fromUser->getDisplayName() ), $gv['amount'] ) ) {

				$gBitSmarty->assign( 'gvAmount', $currencies->format( $gv['amount'] ) );

				//send the message
				$textMessage = $gBitSmarty->fetch( 'bitpackage:bitcommerce/gv_purchase_email_text.tpl' );
				$htmlMessage = $gBitSmarty->fetch( 'bitpackage:bitcommerce/gv_purchase_email_html.tpl' );

				zen_mail( $fromUser->getDisplayName(), $fromUser->getField('email'), TEXT_REDEEM_GV_SUBJECT . TEXT_REDEEM_GV_SUBJECT_ORDER . $gv['order_id'] , $textMessage, STORE_NAME, EMAIL_FROM, $htmlMessage, 'gv_queue');

				$gBitDb->Execute("update " . TABLE_COUPON_GV_QUEUE . "
						  set `release_flag`= 'Y'
						  where `unique_id`='" . $_GET['gid'] . "'");
			}
			bit_redirect( BITCOMMERCE_PKG_URL.'admin/gv_queue.php' );
      }
  }
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
</head>
<body>
<!-- header //-->
<?php require(DIR_FS_ADMIN_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->
<table border="0" width="100%" cellspacing="2" cellpadding="2">
  <tr>
<!-- body_text //-->
    <td width="100%" valign="top"><table>
      <tr>
        <td width="100%"><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
            <td class="pageHeading" align="right"><?php echo zen_draw_separator('pixel_trans.gif', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table>
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_CUSTOMERS; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_ORDERS_ID; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_VOUCHER_VALUE; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_DATE_PURCHASED; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php
  $gv_query_raw =  "SELECT co.`billing_name`, uu.`real_name`, uu.`login`, uu.`email`, gv.`unique_id`, gv.`date_created`, gv.`amount`, gv.`order_id` 
					FROM " . TABLE_COUPON_GV_QUEUE . " gv 
						INNER JOIN " . TABLE_ORDERS . " co ON(gv.`order_id`=co.`orders_id`) 
						INNER JOIN `".BIT_DB_PREFIX."users_users` uu ON (gv.`customer_id`=uu.`user_id`) 
					WHERE gv.`release_flag` = 'N' ORDER BY gv.`order_id`, gv.`unique_id`";
  $gv_list = $gBitDb->query($gv_query_raw, array(), $gv_query_numrows, ((int)($_GET['page'] - 1) * MAX_DISPLAY_SEARCH_RESULTS) );
  while (!$gv_list->EOF) {
    if (((!$_GET['gid']) || (@$_GET['gid'] == $gv_list->fields['unique_id'])) && (!$gInfo)) {
      $gInfo = new objectInfo($gv_list->fields);
    }
    if ( (is_object($gInfo)) && ($gv_list->fields['unique_id'] == $gInfo->unique_id) ) {
      echo '              <tr class="info" onmouseover="this.style.cursor=\'hand\'" onclick="document.location.href=\'' . zen_href_link_admin('gv_queue.php', zen_get_all_get_params(array('gid', 'action')) . 'gid=' . $gInfo->unique_id . '&action=edit') . '\'">' . "\n";
    } else {
      echo '              <tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';this.style.cursor=\'hand\'" onmouseout="this.className=\'dataTableRow\'" onclick="document.location.href=\'' . zen_href_link_admin('gv_queue.php', zen_get_all_get_params(array('gid', 'action')) . 'gid=' . $gv_list->fields['unique_id']) . '\'">' . "\n";
    }
?>
                <td class="dataTableContent"><?php echo $gv_list->fields['billing_name'] . ' ( ' . $gv_list->fields['email']; ?> ) </td>
                <td class="dataTableContent" align="center"><?php echo $gv_list->fields['order_id']; ?></td>
                <td class="dataTableContent" align="right"><?php echo $currencies->format($gv_list->fields['amount']); ?></td>
                <td class="dataTableContent" align="right"><?php echo zen_datetime_short($gv_list->fields['date_created']); ?></td>
                <td class="dataTableContent" align="right"><?php if ( (is_object($gInfo)) && ($gv_list->fields['unique_id'] == $gInfo->unique_id) ) { echo zen_image(DIR_WS_IMAGES . 'icon_arrow_right.gif'); } else { echo '<a href="' . zen_href_link_admin(FILENAME_GV_QUEUE, 'page=' . $_GET['page'] . '&gid=' . $gv_list->fields['unique_id']) . '">' . zen_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
    $gv_list->MoveNext();
  }
/*
?>
              <tr>
                <td colspan="5"><table>
                  <tr>
                    <td class="smallText" valign="top"><?php echo $gv_split->display_count($gv_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_GIFT_VOUCHERS); ?></td>
                    <td class="smallText" align="right"><?php echo $gv_split->display_links($gv_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?></td>
                  </tr>
                </table></td>
              </tr>
*/
?>
            </table></td>
<?php


  $heading = array();
  $contents = array();
  switch ($_GET['action']) {
    case 'release':
      $heading[] = array('text' => '[' . $gInfo->unique_id . '] ' . zen_datetime_short($gInfo->date_created) . ' ' . $currencies->format($gInfo->amount));

      $contents[] = array('align' => 'center', 'text' => '<a href="' . zen_href_link_admin('gv_queue.php', 'action=confirmrelease&gid=' . $gInfo->unique_id,'NONSSL') . '">' . zen_image_button('button_confirm_red.gif', IMAGE_CONFIRM) . '</a> <a href="' . zen_href_link_admin('gv_queue.php', 'action=cancel&gid=' . $gInfo->unique_id,'NONSSL') . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    default:
      $heading[] = array('text' => '[' . $gInfo->unique_id . '] ' . zen_datetime_short($gInfo->date_created) . ' ' . $currencies->format($gInfo->amount));

      if ($gv_list->RecordCount() == 0) {
        $contents[] = array('align' => 'center','text' => TEXT_GV_NONE);
      } else {
        $contents[] = array('align' => 'center','text' => '<a href="' . zen_href_link_admin('gv_queue.php','action=release&gid=' . $gInfo->unique_id,'NONSSL'). '">' . zen_image_button('button_release_gift.gif', tra( 'Release Gift Certificate' ) ) . '</a>');

// quick link to order
        $contents[] = array('align' => 'center', 'text' => '<br />' . zen_image(DIR_WS_IMAGES . 'pixel_black.gif','','90%','3'));
        $contents[] = array('align' => 'center', 'text' => TEXT_EDIT_ORDER . $gInfo->order_id);
        $contents[] = array('align' => 'center', 'text' => '<a href="' . zen_href_link_admin(FILENAME_ORDERS, 'oID=' . $gInfo->order_id . '&action=edit', 'NONSSL') . '">' . zen_image_button('button_order.gif', IMAGE_ORDER) . '</a>');
      }
      break;
   }

  if ( (zen_not_null($heading)) && (zen_not_null($contents)) ) {
    echo '            <td width="25%" valign="top">' . "\n";

    $box = new box;
    echo $box->infoBox($heading, $contents);

    echo '            </td>' . "\n";
  }
?>
          </tr>
        </table></td>
      </tr>
    </table></td>
<!-- body_text_eof //-->
  </tr>
</table>
<!-- body_eof //-->

<!-- footer //-->
<?php require(DIR_FS_ADMIN_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
<br />
</body>
</html>
<?php require(DIR_FS_ADMIN_INCLUDES . 'application_bottom.php'); ?>
