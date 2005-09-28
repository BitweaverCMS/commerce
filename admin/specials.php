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
//  $Id: specials.php,v 1.10 2005/09/28 22:38:58 spiderr Exp $
//

  require('includes/application_top.php');

  
  $currencies = new currencies();

  $action = (isset($_GET['action']) ? $_GET['action'] : '');

  if (zen_not_null($action)) {
    switch ($action) {
      case 'setflag':
        zen_set_specials_status($_GET['id'], $_GET['flag']);

        // reset products_price_sorter for searches etc.
        $update_price = $db->Execute("select products_id from " . TABLE_SPECIALS . " where specials_id = '" . $_GET['id'] . "'");
        zen_update_products_price_sorter($update_price->fields['products_id']);

        zen_redirect(zen_href_link_admin(FILENAME_SPECIALS, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . 'sID=' . $_GET['id'], 'NONSSL'));
        break;
      case 'insert':
        if ($_POST['products_id'] < 1) {
          $messageStack->add_session(ERROR_NOTHING_SELECTED, 'caution');
        } else {
        $products_id = zen_db_prepare_input($_POST['products_id']);
        $products_price = zen_db_prepare_input($_POST['products_price']);
        $specials_price = zen_db_prepare_input($_POST['specials_price']);

        if (substr($specials_price, -1) == '%') {
          $new_special_insert = $db->Execute("select products_id, products_price, products_priced_by_attribute
                                              from " . TABLE_PRODUCTS . "
                                              where products_id = '" . (int)$products_id . "'");

// check if priced by attribute
          if ($new_special_insert->fields['products_priced_by_attribute'] == '1') {
            $products_price = zen_get_products_base_price($products_id);
          } else {
            $products_price = $new_special_insert->fields['products_price'];
          }

          $specials_price = ($products_price - (($specials_price / 100) * $products_price));
        }

        $specials_date_available = ((zen_db_prepare_input($_POST['start']) == '') ? '0001-01-01' : zen_date_raw($_POST['start']));
        $expires_date = ((zen_db_prepare_input($_POST['end']) == '') ? '0001-01-01' : zen_date_raw($_POST['end']));

        $db->Execute("insert into " . TABLE_SPECIALS . "
                    (products_id, specials_new_products_price, specials_date_added, expires_date, status, specials_date_available)
                    values ('" . (int)$products_id . "',
                            '" . zen_db_input($specials_price) . "',
                            now(),
                            '" . zen_db_input($expires_date) . "', '1', '" . zen_db_input($specials_date_available) . "')");

        $new_special = $db->Execute("select specials_id from " . TABLE_SPECIALS . " where products_id='" . (int)$products_id . "'");

        // reset products_price_sorter for searches etc.
        zen_update_products_price_sorter((int)$products_id);

        } // nothing selected
        if ($_GET['go_back'] == 'ON'){
          zen_redirect(zen_href_link_admin(FILENAME_PRODUCTS_PRICE_MANAGER, 'products_id=' . $products_id));
        } else {
          zen_redirect(zen_href_link_admin(FILENAME_SPECIALS, 'page=' . $_GET['page'] . '&sID=' . $new_special->fields['specials_id']));
        }
        break;
      case 'update':
        $specials_id = zen_db_prepare_input($_POST['specials_id']);

        if ($_POST['products_priced_by_attribute'] == '1') {
          $products_price = zen_get_products_base_price($_POST['update_products_id']);
        } else {
          $products_price = zen_db_prepare_input($_POST['products_price']);
        }

        $specials_price = zen_db_prepare_input($_POST['specials_price']);

        if (substr($specials_price, -1) == '%') $specials_price = ($products_price - (($specials_price / 100) * $products_price));

        $specials_date_available = ((zen_db_prepare_input($_POST['start']) == '') ? '0001-01-01' : zen_date_raw($_POST['start']));
        $expires_date = ((zen_db_prepare_input($_POST['end']) == '') ? '0001-01-01' : zen_date_raw($_POST['end']));

        $db->Execute("update " . TABLE_SPECIALS . "
                      set specials_new_products_price = '" . zen_db_input($specials_price) . "',
                          specials_last_modified = now(),
                          expires_date = '" . zen_db_input($expires_date) . "',
                          specials_date_available = '" . zen_db_input($specials_date_available) . "'
                      where specials_id = '" . (int)$specials_id . "'");

        // reset products_price_sorter for searches etc.
        $update_price = $db->Execute("select products_id from " . TABLE_SPECIALS . " where specials_id = '" . (int)$specials_id . "'");
        zen_update_products_price_sorter($update_price->fields['products_id']);

        zen_redirect(zen_href_link_admin(FILENAME_SPECIALS, 'page=' . $_GET['page'] . '&sID=' . $specials_id));
        break;
      case 'deleteconfirm':
        // demo active test
        if (zen_admin_demo()) {
          $_GET['action']= '';
          $messageStack->add_session(ERROR_ADMIN_DEMO, 'caution');
          zen_redirect(zen_href_link_admin(FILENAME_SPECIALS, 'page=' . $_GET['page']));
        }
        $specials_id = zen_db_prepare_input($_GET['sID']);

        // reset products_price_sorter for searches etc.
        $update_price = $db->Execute("select products_id from " . TABLE_SPECIALS . " where specials_id = '" . $specials_id . "'");
        $update_price_id = $update_price->fields['products_id'];

        $db->Execute("delete from " . TABLE_SPECIALS . "
                      where specials_id = '" . (int)$specials_id . "'");

        zen_update_products_price_sorter($update_price_id);

        zen_redirect(zen_href_link_admin(FILENAME_SPECIALS, 'page=' . $_GET['page']));
        break;
    }
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
<?php
  if ( ($action == 'new') || ($action == 'edit') ) {
?>
<link rel="stylesheet" type="text/css" href="includes/javascript/spiffyCal/spiffyCal_v2_1.css">
<script language="JavaScript" src="includes/javascript/spiffyCal/spiffyCal_v2_1.js"></script>
<?php
  }
?>
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
<body onload="init()">
<div id="spiffycalendar" class="text"></div>
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
         <tr><?php echo zen_draw_form('search', FILENAME_SPECIALS, '', 'get'); ?>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
            <td class="pageHeading" align="right"><?php echo zen_draw_separator('pixel_trans.gif', 1, HEADING_IMAGE_HEIGHT); ?></td>
            <td class="smallText" align="right">
<?php
// show reset search
  if (isset($_GET['search']) && zen_not_null($_GET['search'])) {
    echo '<a href="' . zen_href_link_admin(FILENAME_SPECIALS) . '">' . zen_image_button('button_reset.gif', IMAGE_RESET) . '</a>&nbsp;&nbsp;';
  }
  echo HEADING_TITLE_SEARCH_DETAIL . ' ' . zen_draw_input_field('search');
  if (isset($_GET['search']) && zen_not_null($_GET['search'])) {
    $keywords = zen_db_input(zen_db_prepare_input($_GET['search']));
    echo '<br/ >' . TEXT_INFO_SEARCH_DETAIL_FILTER . $keywords;
  }
?>
            </td>
          </form></tr>
          <tr>
            <td colspan="3" class="main"><?php echo TEXT_STATUS_WARNING; ?></td>
          </tr>
        </table></td>
      </tr>
<?php
  if ( ($action == 'new') || ($action == 'edit') ) {
    $form_action = 'insert';
    if ( ($action == 'edit') && isset($_GET['sID']) ) {
      $form_action = 'update';

      $product = $db->Execute("select p.`products_id`, pd.`products_name`, p.`products_price`, p.`products_priced_by_attribute`,
                                      s.`specials_new_products_price`, s.`expires_date`, s.`specials_date_available`
                               from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " .
                                        TABLE_SPECIALS . " s
                               where p.`products_id` = pd.`products_id`
                               and pd.`language_id` = '" . (int)$_SESSION['languages_id'] . "'
                               and p.`products_id` = s.`products_id`
                               and s.`specials_id` = '" . (int)$_GET['sID'] . "'");

      $sInfo = new objectInfo($product->fields);

      if ($sInfo->products_priced_by_attribute == '1') {
        $sInfo->products_price = zen_get_products_base_price($product->fields['products_id']);
      }

    } else {
      $sInfo = new objectInfo(array());

// create an array of products on special, which will be excluded from the pull down menu of products
// (when creating a new product on special)
      $specials_array = array();
      $specials = $db->Execute("select p.`products_id`, p.`products_model`
                                from " . TABLE_PRODUCTS . " p, " . TABLE_SPECIALS . " s
                                where s.`products_id` = p.`products_id`");

      while (!$specials->EOF) {
        $specials_array[] = $specials->fields['products_id'];
        $specials->MoveNext();
      }

// never include Gift Vouchers for specials
      $gift_vouchers = $db->Execute("select distinct p.`products_id`, p.`products_model`
                                from " . TABLE_PRODUCTS . " p, " . TABLE_SPECIALS . " s
                                where UPPER( p.`products_model` ) like '%GIFT%'");

      while (!$gift_vouchers->EOF) {
        if(substr($gift_vouchers->fields['products_model'], 0, 4) == 'GIFT') {
          $specials_array[] = $gift_vouchers->fields['products_id'];
        }
        $gift_vouchers->MoveNext();
      }

// do not include things that cannot go in the cart
      $not_for_cart = $db->Execute("select p.`products_id` from " . TABLE_PRODUCTS . " p left join " . TABLE_PRODUCT_TYPES . " pt on p.`products_type`= pt.`type_id` where pt.allow_add_to_cart = 'N'");

      while (!$not_for_cart->EOF) {
        $specials_array[] = $not_for_cart->fields['products_id'];
        $not_for_cart->MoveNext();
      }
    }
?>
<script language="javascript">
var StartDate = new ctlSpiffyCalendarBox("StartDate", "new_special", "start", "btnDate1","<?php echo (($sInfo->specials_date_available == '0001-01-01') ? '' : zen_date_short($sInfo->specials_date_available)); ?>",scBTNMODE_CUSTOMBLUE);
var EndDate = new ctlSpiffyCalendarBox("EndDate", "new_special", "end", "btnDate2","<?php echo (($sInfo->expires_date == '0001-01-01') ? '' : zen_date_short($sInfo->expires_date)); ?>",scBTNMODE_CUSTOMBLUE);
</script>

      <tr><form name="new_special" <?php echo 'action="' . zen_href_link_admin(FILENAME_SPECIALS, zen_get_all_get_params(array('action', 'info', 'sID')) . 'action=' . $form_action . '&go_back=' . $_GET['go_back'], 'NONSSL') . '"'; ?> method="post"><?php if ($form_action == 'update') echo zen_draw_hidden_field('specials_id', $_GET['sID']); ?>
        <td><br><table border="0" cellspacing="0" cellpadding="2">
          <tr>
            <td class="main"><?php echo TEXT_SPECIALS_PRODUCT; ?>&nbsp;</td>
            <td class="main"><?php echo (isset($sInfo->products_name)) ? $sInfo->products_name . ' <small>(' . $currencies->format($sInfo->products_price) . ')</small>' : zen_draw_products_pull_down('products_id', 'size="5" style="font-size:10px"', $specials_array, true, $_GET['add_products_id'], true); echo zen_draw_hidden_field('products_price', (isset($sInfo->products_price) ? $sInfo->products_price : '')); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo TEXT_SPECIALS_SPECIAL_PRICE; ?>&nbsp;</td>
            <td class="main"><?php echo zen_draw_input_field('specials_price', (isset($sInfo->specials_new_products_price) ? $sInfo->specials_new_products_price : '')); echo zen_draw_hidden_field('products_priced_by_attribute', $sInfo->products_priced_by_attribute); echo zen_draw_hidden_field('update_products_id', $sInfo->products_id); ?></td>
          </tr>

          <tr>
            <td class="main"><?php echo TEXT_SPECIALS_AVAILABLE_DATE; ?>&nbsp;</td>
            <td class="main"><script language="javascript">StartDate.writeControl(); StartDate.dateFormat="<?php echo DATE_FORMAT_SPIFFYCAL; ?>";</script></td>
          </tr>
          <tr>
            <td class="main"><?php echo TEXT_SPECIALS_EXPIRES_DATE; ?>&nbsp;</td>
            <td class="main"><script language="javascript">EndDate.writeControl(); EndDate.dateFormat="<?php echo DATE_FORMAT_SPIFFYCAL; ?>";</script></td>
          </tr>

        </table></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
          <tr>
            <td class="main"><br><?php echo TEXT_SPECIALS_PRICE_TIP; ?></td>
            <td class="main" align="right" valign="top"><br><?php echo (($form_action == 'insert') ? zen_image_submit('button_insert.gif', IMAGE_INSERT) : zen_image_submit('button_update.gif', IMAGE_UPDATE)). '&nbsp;&nbsp;&nbsp;<a href="' . zen_href_link_admin(FILENAME_SPECIALS, 'page=' . $_GET['page'] . (isset($_GET['sID']) ? '&sID=' . $_GET['sID'] : '')) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>'; ?></td>
          </tr>
        </table></td>
      </form></tr>
<?php
  } else {
?>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent" align="right"><?php echo 'ID#'; ?>&nbsp;</td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_PRODUCTS; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_PRODUCTS_MODEL; ?></td>
                <td colspan="2" class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_PRODUCTS_PRICE; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_AVAILABLE_DATE; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_EXPIRES_DATE; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_STATUS; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php
// create search filter
  $search = '';
  if (isset($_GET['search']) && zen_not_null($_GET['search'])) {
    $keywords = zen_db_input(zen_db_prepare_input($_GET['search']));
    $search = " and (pd.`products_name` like '%" . $keywords . "%' or pd.`products_description` like '%" . $keywords . "%' or p.`products_model` like '%" . $keywords . "%')";
  }

// order of display
  $order_by = " order by pd.`products_name` ";

// create split page control

    $specials_query_raw = "select p.`products_id`, pd.`products_name`, p.`products_model`, p.`products_price`, p.`products_priced_by_attribute`, s.`specials_id`, s.`specials_new_products_price`, s.`specials_date_added`, s.`specials_last_modified`, s.`expires_date`, s.`date_status_change`, s.`status`, s.`specials_date_available` from " . TABLE_PRODUCTS . " p, " . TABLE_SPECIALS . " s, " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.`products_id` = pd.`products_id` and pd.`language_id` = '" . (int)$_SESSION['languages_id'] . "' and p.`products_id` = s.`products_id`" . $search . $order_by;
    $specials_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS, $specials_query_raw, $specials_query_numrows);
    $specials = $db->Execute($specials_query_raw);
    while (!$specials->EOF) {
      if ((!isset($_GET['sID']) || (isset($_GET['sID']) && ($_GET['sID'] == $specials->fields['specials_id']))) && !isset($sInfo)) {
        $products = $db->Execute("select `products_image`
                                  from " . TABLE_PRODUCTS . "
                                  where `products_id` = '" . (int)$specials->fields['products_id'] . "'");

        $sInfo_array = array_merge($specials->fields, $products->fields);
        $sInfo = new objectInfo($sInfo_array);
      }

      if (isset($sInfo) && is_object($sInfo) && ($specials->fields['specials_id'] == $sInfo->specials_id)) {
        echo '                  <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . zen_href_link_admin(FILENAME_SPECIALS, 'page=' . $_GET['page'] . '&sID=' . $sInfo->specials_id . '&action=edit') . '\'">' . "\n";
      } else {
        echo '                  <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . zen_href_link_admin(FILENAME_SPECIALS, 'page=' . $_GET['page'] . '&sID=' . $specials->fields['specials_id'] . '&action=edit') . '\'">' . "\n";
      }

      if ($specials->fields['products_priced_by_attribute'] == '1') {
        $specials_current_price = zen_get_products_base_price($specials->fields['products_id']);
      } else {
        $specials_current_price = $specials->fields['products_price'];
      }

      $sale_price = zen_get_products_special_price($specials->fields['products_id'], false);

?>
                <td  class="dataTableContent" align="right"><?php echo $specials->fields['products_id']; ?>&nbsp;</td>
                <td  class="dataTableContent"><?php echo $specials->fields['products_name']; ?></td>
                <td  class="dataTableContent" align="left"><?php echo $specials->fields['products_model']; ?>&nbsp;</td>
                <td colspan="2" class="dataTableContent" align="right"><?php echo zen_get_products_display_price($specials->fields['products_id']); ?></td>
                <td  class="dataTableContent" align="center"><?php echo (($specials->fields['specials_date_available'] != '0001-01-01' and $specials->fields['specials_date_available'] !='') ? zen_date_short($specials->fields['specials_date_available']) : TEXT_NONE); ?></td>
                <td  class="dataTableContent" align="center"><?php echo (($specials->fields['expires_date'] != '0001-01-01' and $specials->fields['expires_date'] !='') ? zen_date_short($specials->fields['expires_date']) : TEXT_NONE); ?></td>
                <td  class="dataTableContent" align="center">
<?php
      if ($specials->fields['status'] == '1') {
        echo '<a href="' . zen_href_link_admin(FILENAME_SPECIALS, 'action=setflag&flag=0&id=' . $specials->fields['specials_id'] . '&page=' . $_GET['page'], 'NONSSL') . '">' . zen_image(DIR_WS_IMAGES . 'icon_green_on.gif', IMAGE_ICON_STATUS_ON) . '</a>';
      } else {
        echo '<a href="' . zen_href_link_admin(FILENAME_SPECIALS, 'action=setflag&flag=1&id=' . $specials->fields['specials_id'] . '&page=' . $_GET['page'], 'NONSSL') . '">' . zen_image(DIR_WS_IMAGES . 'icon_red_on.gif', IMAGE_ICON_STATUS_OFF) . '</a>';
      }
?>
                </td>
                <td class="dataTableContent" align="right">
                  <?php echo '<a href="' . zen_href_link_admin(FILENAME_SPECIALS, 'page=' . $_GET['page'] . '&sID=' . $specials->fields['specials_id'] . '&action=edit') . '">' . zen_image(DIR_WS_IMAGES . 'icon_edit.gif', ICON_EDIT) . '</a>'; ?>
				          <?php echo '<a href="' . zen_href_link_admin(FILENAME_SPECIALS, 'page=' . $_GET['page'] . '&sID=' . $specials->fields['specials_id'] . '&action=delete') . '">' . zen_image(DIR_WS_IMAGES . 'icon_delete.gif', ICON_DELETE) . '</a>'; ?>
                  <?php if (isset($sInfo) && is_object($sInfo) && ($specials->fields['specials_id'] == $sInfo->specials_id)) { echo zen_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . zen_href_link_admin(FILENAME_SPECIALS, zen_get_all_get_params(array('sID')) . 'sID=' . $specials->fields['specials_id']) . '">' . zen_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>
				        </td>
      </tr>
<?php
      $specials->MoveNext();
    }
?>
              <tr>
                <td colspan="4"><table border="0" width="100%" cellpadding="0"cellspacing="2">
                  <tr>
                    <td class="smallText" valign="top"><?php echo $specials_split->display_count($specials_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_SPECIALS); ?></td>
                    <td class="smallText" align="right"><?php echo $specials_split->display_links($specials_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?></td>
                  </tr>
<?php
  if (empty($action)) {
?>
                  <tr>
                    <td colspan="2" align="right"><?php echo '<a href="' . zen_href_link_admin(FILENAME_SPECIALS, 'page=' . $_GET['page'] . '&action=new') . '">' . zen_image_button('button_new_product.gif', IMAGE_NEW_PRODUCT) . '</a>'; ?></td>
                  </tr>
<?php
  }
?>
                </table></td>
              </tr>
            </table></td>
<?php
  $heading = array();
  $contents = array();

  switch ($action) {
    case 'delete':
      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_DELETE_SPECIALS . '</b>');

      $contents = array('form' => zen_draw_form('specials', FILENAME_SPECIALS, 'page=' . $_GET['page'] . '&sID=' . $sInfo->specials_id . '&action=deleteconfirm'));
      $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
      $contents[] = array('text' => '<br><b>' . $sInfo->products_name . '</b>');
      $contents[] = array('align' => 'center', 'text' => '<br>' . zen_image_submit('button_delete.gif', IMAGE_DELETE) . '&nbsp;<a href="' . zen_href_link_admin(FILENAME_SPECIALS, 'page=' . $_GET['page'] . '&sID=' . $sInfo->specials_id) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    default:
      if (is_object($sInfo)) {
        $heading[] = array('text' => '<b>' . $sInfo->products_name . '</b>');

      if ($sInfo->products_priced_by_attribute == '1') {
        $specials_current_price = zen_get_products_base_price($sInfo->products_id);
      } else {
        $specials_current_price = $sInfo->products_price;
      }

        $contents[] = array('align' => 'center', 'text' => '<a href="' . zen_href_link_admin(FILENAME_SPECIALS, 'page=' . $_GET['page'] . '&sID=' . $sInfo->specials_id . '&action=edit') . '">' . zen_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . zen_href_link_admin(FILENAME_SPECIALS, 'page=' . $_GET['page'] . '&sID=' . $sInfo->specials_id . '&action=delete') . '">' . zen_image_button('button_delete.gif', IMAGE_DELETE) . '</a>');
        $contents[] = array('align' => 'center', 'text' => '<a href="' . zen_href_link_admin(FILENAME_PRODUCTS_PRICE_MANAGER, 'action=edit&products_id=' . $sInfo->products_id) . '">' . zen_image_button('button_products_price_manager.gif', IMAGE_PRODUCTS_PRICE_MANAGER) . '</a>');
        $contents[] = array('text' => '<br>' . TEXT_INFO_DATE_ADDED . ' ' . zen_date_short($sInfo->specials_date_added));
        $contents[] = array('text' => '' . TEXT_INFO_LAST_MODIFIED . ' ' . zen_date_short($sInfo->specials_last_modified));
        $contents[] = array('align' => 'center', 'text' => '<br>' . zen_info_image($sInfo->products_image, $sInfo->products_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT));
        $contents[] = array('text' => '<br>' . TEXT_INFO_ORIGINAL_PRICE . ' ' . $currencies->format($specials_current_price));
        $contents[] = array('text' => '' . TEXT_INFO_NEW_PRICE . ' ' . $currencies->format($sInfo->specials_new_products_price));
        $contents[] = array('text' => '' . TEXT_INFO_DISPLAY_PRICE . ' ' . zen_get_products_display_price($sInfo->products_id));

        $contents[] = array('text' => '<br>' . TEXT_INFO_AVAILABLE_DATE . ' <b>' . (($specials->fields['specials_date_available'] != '0001-01-01' and $specials->fields['specials_date_available'] !='') ? zen_date_short($specials->fields['specials_date_available']) : TEXT_NONE) . '</b>');
        $contents[] = array('text' => '<br>' . TEXT_INFO_EXPIRES_DATE . ' <b>' . (($specials->fields['expires_date'] != '0001-01-01' and $specials->fields['expires_date'] !='') ? zen_date_short($specials->fields['expires_date']) : TEXT_NONE) . '</b>');
        $contents[] = array('text' => '' . TEXT_INFO_STATUS_CHANGE . ' ' . zen_date_short($sInfo->date_status_change));
        $contents[] = array('align' => 'center', 'text' => '<a href="' . zen_href_link_admin(FILENAME_CATEGORIES, '&action=new_product' . '&cPath=' . zen_get_product_path($sInfo->products_id, 'override') . '&pID=' . $sInfo->products_id . '&product_type=' . zen_get_products_type($sInfo->products_id)) . '">' . zen_image_button('button_edit_product.gif', IMAGE_EDIT_PRODUCT) . '<br />' . TEXT_PRODUCT_EDIT . '</a>');
      }
      break;
  }
  if ( (zen_not_null($heading)) && (zen_not_null($contents)) ) {
    echo '            <td width="25%" valign="top">' . "\n";

    $box = new box;
    echo $box->infoBox($heading, $contents);

    echo '            </td>' . "\n";
  }
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
</body>
</html>
<?php require(DIR_FS_ADMIN_INCLUDES . 'application_bottom.php'); ?>