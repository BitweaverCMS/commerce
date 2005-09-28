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
// |   DevosC, Developing open source Code                                |
// |   Copyright (c) 2004 DevosC.com                                      |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available through the world-wide-web at the following url:           |
// | http://www.zen-cart.com/license/2_0.txt.                             |
// | If you did not receive a copy of the zen-cart license and are unable |
// | to obtain it through the world-wide-web, please send a note to       |
// | license@zen-cart.com so we can mail you a copy immediately.          |
// +----------------------------------------------------------------------+
//  $Id: paypal.php,v 1.5 2005/09/28 22:38:57 spiderr Exp $
//
  require('includes/application_top.php');

  $action = (isset($HTTP_GET_VARS['action']) ? $HTTP_GET_VARS['action'] : '');

  require(DIR_FS_CATALOG_MODULES . 'payment/paypal.php');
//  require_once(DIR_FS_CATALOG_MODULES . 'payment/paypal/database_tables.inc.php');

  $payment_statuses = array();
  $payment_status_trans = $db->Execute("select payment_status_name as payment_status from " . TABLE_PAYPAL_PAYMENT_STATUS );
  while (!$payment_status_trans->EOF) {
    $payment_statuses[] = array('id' => $payment_status_trans->fields['payment_status'],
                               'text' => $payment_status_trans->fields['payment_status']);
  $payment_status_trans->MoveNext();
  }

?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
<link rel="stylesheet" type="text/css" href="includes/cssjsmenuhover.css" media="all" id="hoverJS">
<script language="javascript" src="includes/menu.js"></script>
<script language="javascript" src="includes/general.js"></script>
<script type="text/javascript">
  <!--
  function init()
  {
    cssjsmenu('navbar');
    if (document.getElementById)
    {
      var kill = document.getElementById('hoverJS');
      kill.disabled = true;
    }
  }
  // -->
</script>
</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF" onload="SetFocus(), init();">
<!-- header //-->
<?php require(DIR_FS_ADMIN_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->
<table border="0" width="100%" cellspacing="2" cellpadding="2">
  <tr>
<!-- body_text //-->
    <td width="100%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_ADMIN_TITLE; ?></td>
            <td class="pageHeading" align="right"><?php echo zen_draw_separator('pixel_trans.gif', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
            <td class="smallText" align="right">
<?php
echo zen_draw_form('payment_status', FILENAME_PAYPAL, '', 'get') . HEADING_PAYMENT_STATUS . ' ' . zen_draw_pull_down_menu('payment_status', array_merge(array(array('id' => '', 'text' => TEXT_ALL_IPNS)), $payment_statuses), $HTTP_GET_VARS['payment_status'], 'onChange="this.form.submit();"').'</form>';
?>
            </td>
            <td class="pageHeading" align="right"><?php echo zen_draw_separator('pixel_trans.gif', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_ORDER_NUMBER; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_TXN_TYPE; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_PAYMENT_STATUS; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_PAYMENT_AMOUNT; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php
  if(isset($HTTP_GET_VARS['payment_status']) && zen_not_null($HTTP_GET_VARS['payment_status']) ) {
    $ipn_search = "and p.payment_status = '" . zen_db_prepare_input($HTTP_GET_VARS['payment_status']) . "'";
    switch($HTTP_GET_VARS['payment_status']) {
      case 'Pending':
      case 'Completed':
      default:
        $ipn_query_raw = "select p.`zen_order_id`, p.`paypal_ipn_id`, p.`txn_type`, p.`payment_type`, p.`payment_status`, p.`pending_reason`, p.`mc_currency`, p.`payer_status`, p`.mc_currency`, p.`date_added`, p.`mc_gross` from " . TABLE_PAYPAL . " as p, " .TABLE_ORDERS . " as o  where o.`orders_id` = p.`zen_order_id` " . $ipn_search . " order by o.`orders_id` DESC";
      break;
    }
  } else {
        $ipn_query_raw = "select p.`zen_order_id`, p.`paypal_ipn_id`, p.`txn_type`, p.`payment_type`, p.`payment_status`, p.`pending_reason`, p.`mc_currency`, p.`payer_status`, p.`mc_currency`, p.`date_added`, p.`mc_gross` from " . TABLE_PAYPAL . " as p left join " .TABLE_ORDERS . " as o on o.`orders_id` = p.`zen_order_id` order by p.`paypal_ipn_id` DESC";
  }
  $ipn_split = new splitPageResults($HTTP_GET_VARS['page'], MAX_DISPLAY_SEARCH_RESULTS, $ipn_query_raw, $ipn_query_numrows);
  $ipn_trans = $db->Execute($ipn_query_raw);
  while (!$ipn_trans->EOF) {
    if ((!isset($HTTP_GET_VARS['ipnID']) || (isset($HTTP_GET_VARS['ipnID']) && ($HTTP_GET_VARS['ipnID'] == $ipn_trans->fields['paypal_ipn_id']))) && !isset($ipnInfo) ) {
      $ipnInfo = new objectInfo($ipn_trans->fields);
    }

    if (isset($ipnInfo) && is_object($ipnInfo) && ($ipn_trans->fields['paypal_ipn_id'] == $ipnInfo->paypal_ipn_id) ) {
      echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . zen_href_link_admin(FILENAME_ORDERS, 'page=' . $HTTP_GET_VARS['page'] . '&ipnID=' . $ipnInfo->paypal_ipn_id . '&oID=' . $ipnInfo->zen_order_id . '&action=edit' . '&referer=ipn') . '\'">' . "\n";
    } else {
      echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . zen_href_link_admin(FILENAME_PAYPAL, 'page=' . $HTTP_GET_VARS['page'] . '&ipnID=' . $ipn_trans->fields['paypal_ipn_id']) . '\'">' . "\n";
    }
?>
                <td class="dataTableContent"> <?php echo $ipn_trans->fields['zen_order_id'] ?> </td>
                <td class="dataTableContent"> <?php echo $ipn_trans->fields['txn_type']; ?>
                <td class="dataTableContent"><?php echo $ipn_trans->fields['payment_status_name'] . ' '. $ipn_trans->fields['payment_status']; ?></td>
                <td class="dataTableContent" align="right"><?php echo $ipn_trans->fields['mc_currency'] . ' '.number_format($ipn_trans->fields['mc_gross'], 2); ?></td>
                <td class="dataTableContent" align="right"><?php if (isset($ipnInfo) && is_object($ipnInfo) && ($ipn_trans->fields['paypal_ipn_id'] == $ipnInfo->paypal_ipn_id) ) { echo zen_image(DIR_WS_IMAGES . 'icon_arrow_right.gif'); } else { echo '<a href="' . zen_href_link_admin(FILENAME_PAYPAL, 'page=' . $HTTP_GET_VARS['page'] . '&ipnID=' . $ipn_trans->fields['paypal_ipn_id']) . '">' . zen_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
    $ipn_trans->MoveNext();
  }
?>
              <tr>
                <td colspan="5"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText" valign="top"><?php echo $ipn_split->display_count($ipn_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $HTTP_GET_VARS['page'], "Displaying <b>%d</b> to <b>%d</b> (of <b>%d</b> IPN's)"); ?></td>
                    <td class="smallText" align="right"><?php echo $ipn_split->display_links($ipn_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $HTTP_GET_VARS['page']); ?></td>
                  </tr>
                </table></td>
              </tr>
            </table></td>
<?php
  $heading = array();
  $contents = array();

  switch ($action) {
    case 'new':
      break;
    case 'edit':
      break;
    case 'delete':
      break;
    default:
      if (is_object($ipnInfo)) {
        $heading[] = array('text' => '<b>' . TEXT_INFO_PAYPAL_IPN_HEADING.' #' . $ipnInfo->paypal_ipn_id . '</b>');
        $ipn = $db->Execute("select * from " . TABLE_PAYPAL_PAYMENT_STATUS_HISTORY . " where paypal_ipn_id = '" . $ipnInfo->paypal_ipn_id . "'");
        $ipn_count = $ipn->RecordCount();

        $contents[] = array('align' => 'center', 'text' => '<a href="' . zen_href_link_admin(FILENAME_ORDERS, zen_get_all_get_params(array('ipnID', 'action')) . 'oID=' . $ipnInfo->zen_order_id .'&' . 'ipnID=' . $ipnInfo->paypal_ipn_id .'&action=edit' . '&referer=ipn') . '">' . zen_image_button('button_orders.gif', IMAGE_ORDERS) . '</a>');
        $contents[] = array('text' => '<br>' . TABLE_HEADING_NUM_HISTORY_ENTRIES . ': '. $ipn_count);
        $count = 1;
        while (!$ipn->EOF) {
          $contents[] = array('text' => '<br>' . TABLE_HEADING_ENTRY_NUM . ': '. $count);
          $contents[] = array('text' =>  TABLE_HEADING_DATE_ADDED . ': '. zen_datetime_short($ipn->fields['date_added']));
          $contents[] = array('text' =>  TABLE_HEADING_TRANS_ID . ': '.$ipn->fields['txn_id']);
          $contents[] = array('text' =>  TABLE_HEADING_PAYMENT_STATUS . ': '.$ipn->fields['payment_status']);
          $contents[] = array('text' =>  TABLE_HEADING_PENDING_REASON . ': '.$ipn->fields['pending_reason']);
          $count++;
          $ipn->MoveNext();
        }
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
<br>
</body>
</html>
<?php require(DIR_FS_ADMIN_INCLUDES . 'application_bottom.php'); ?>
