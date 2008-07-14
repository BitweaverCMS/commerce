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
//  $Id: category_product_listing.php,v 1.1 2008/07/14 13:06:45 lsces Exp $
//
?>
    <table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?>&nbsp;-&nbsp;<?php echo zen_output_generated_category_path($current_category_id); ?></td>
            <td class="pageHeading" align="right"><?php echo zen_draw_separator('pixel_trans.gif', 1, HEADING_IMAGE_HEIGHT); ?></td>
            <td align="right"><table border="0" width="100%" cellspacing="0" cellpadding="0">
              <tr>
                <td class="smallText" align="right">
<?php
    echo zen_draw_form_admin('search', FILENAME_CATEGORIES, '', 'get');
// show reset search
    if (isset($_GET['search']) && zen_not_null($_GET['search'])) {
      echo '<a href="' . zen_href_link_admin(FILENAME_CATEGORIES) . '">' . zen_image_button('button_reset.gif', IMAGE_RESET) . '</a>&nbsp;&nbsp;';
    }
    echo HEADING_TITLE_SEARCH_DETAIL . ' ' . zen_draw_input_field('search');
    if (isset($_GET['search']) && zen_not_null($_GET['search'])) {
      $keywords = zen_db_input(zen_db_prepare_input($_GET['search']));
      echo '<br/ >' . TEXT_INFO_SEARCH_DETAIL_FILTER . $keywords;
    }
    echo '</form>';
?>
                </td>
              </tr>
              <tr>
                <td class="smallText" align="right">
<?php
    echo zen_draw_form_admin('goto', FILENAME_CATEGORIES, '', 'get');
    echo HEADING_TITLE_GOTO . ' ' . zen_draw_pull_down_menu('cPath', zen_get_category_tree(), $current_category_id, 'onChange="this.form.submit();"');
    echo '</form>';
?>
                </td>
              </tr>
            </table></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
<?php if ($action == '') { ?>
                <td class="dataTableHeadingContent" width="20" align="right"><?php echo TABLE_HEADING_ID; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_CATEGORIES_PRODUCTS; ?></td>
                <td class="dataTableHeadingContent" align="center">Picture<?php // echo TABLE_HEADING_MODEL; ?></td>
                <td class="dataTableHeadingContent" align="left"><?php echo TABLE_HEADING_MODEL; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_PRICE; ?></td>
                <td class="dataTableHeadingContent" align="right">&nbsp;</td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_QUANTITY; ?>&nbsp;&nbsp;&nbsp;</td>
                <td class="dataTableHeadingContent" width="50" align="center"><?php echo TABLE_HEADING_STATUS; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_CATEGORIES_SORT_ORDER; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
<?php } // action == '' ?>
              </tr>
<?php
    $categories_count = 0;
    $rows = 0;
    if (isset($_GET['search'])) {
      $search = zen_db_prepare_input($_GET['search']);

      $categories = $gBitDb->Execute("select c.`categories_id`, cd.`categories_name`, cd.`categories_description`, c.`categories_image`,
                                         c.`parent_id`, c.`sort_order`, c.`date_added`, c.`last_modified`,
                                         c.`categories_status`
                                  from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd
                                  where c.`categories_id` = cd.`categories_id`
                                  and cd.`language_id` = '" . (int)$_SESSION['languages_id'] . "'
                                  and cd.`categories_name` like '%" . zen_db_input($search) . "%'
                                  order by c.`sort_order`, cd.`categories_name`");
    } else {
      $categories = $gBitDb->Execute("select c.`categories_id`, cd.`categories_name`, cd.`categories_description`, c.`categories_image`,
                                         c.`parent_id`, c.`sort_order`, c.`date_added`, c.`last_modified`,
                                         c.`categories_status`
                                  from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd
                                  where c.`parent_id` = '" . (int)$current_category_id . "'
                                  and c.`categories_id` = cd.`categories_id`
                                  and cd.`language_id` = '" . (int)$_SESSION['languages_id'] . "'
                                  order by c.`sort_order`, cd.`categories_name`");
    }
    while (!$categories->EOF) {
      $categories_count++;
      $rows++;

// Get parent_id for subcategories if search
      if (isset($_GET['search'])) $cPath = $categories->fields['parent_id'];

      if ((!isset($_GET['cID']) && !isset($_GET['pID']) || (isset($_GET['cID']) && ($_GET['cID'] == $categories->fields['categories_id']))) && !isset($cInfo) && (substr($action, 0, 3) != 'new')) {
        //$category_childs = array('childs_count' => zen_childs_in_category_count($categories->fields['categories_id']));
        //$category_products = array('products_count' => zen_products_in_category_count($categories->fields['categories_id']));

        //$cInfo_array = array_merge($categories->fields, $category_childs, $category_products);
        $cInfo = new objectInfo($categories->fields);
      }

      if (isset($cInfo) && is_object($cInfo) && ($categories->fields['categories_id'] == $cInfo->categories_id) ) {
        echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\''  . zen_href_link_admin(FILENAME_CATEGORIES, zen_get_path($categories->fields['categories_id'])) . '\'">' . "\n";
      } else {
        echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . zen_href_link_admin(FILENAME_CATEGORIES, zen_get_path($categories->fields['categories_id'])) . '\'">' . "\n";
      }
?>
<?php if ($action == '') { ?>
                <td class="dataTableContent" width="20" align="right"><?php echo $categories->fields['categories_id']; ?></td>
                <td class="dataTableContent"><?php echo '<a href="' . zen_href_link_admin(FILENAME_CATEGORIES, zen_get_path($categories->fields['categories_id'])) . '">' . zen_image(DIR_WS_ICONS . 'folder.gif', ICON_FOLDER) . '</a>&nbsp;<b>' . $categories->fields['categories_name'] . '</b>'; ?></td>
                <td class="dataTableContent" align="center">&nbsp;</td>
                <td class="dataTableContent" align="right">&nbsp;<?php echo zen_get_products_sale_discount('', $categories->fields['categories_id'], true); ?></td>
                <td class="dataTableContent" align="center">&nbsp;</td>
                <td class="dataTableContent" align="right" valign="bottom">
                  <?php
                  if (SHOW_COUNTS_ADMIN == 'false') {
                    // don't show counts
                  } else {
                    // show counts
                    $total_products = zen_get_products_to_categories($categories->fields['categories_id'], true);
                    $total_products_on = zen_get_products_to_categories($categories->fields['categories_id'], false);
                    echo $total_products_on . ' ' . tra('of') . ' ' . $total_products . ' ' . tra('active');
                  }
                  ?>
                  &nbsp;&nbsp;
                </td>
                <td class="dataTableContent" width="50" align="left">
<?php
      if ($categories->fields['categories_status'] == '1') {
        echo '<a href="' . zen_href_link_admin(FILENAME_CATEGORIES, 'action=setflag_categories&flag=0&cID=' . $categories->fields['categories_id'] . '&cPath=' . $cPath . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')) . '">' . zen_image(DIR_WS_IMAGES . 'icon_green_on.gif', IMAGE_ICON_STATUS_ON) . '</a>';
      } else {
        echo '<a href="' . zen_href_link_admin(FILENAME_CATEGORIES, 'action=setflag_categories&flag=1&cID=' . $categories->fields['categories_id'] . '&cPath=' . $cPath . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')) . '">' . zen_image(DIR_WS_IMAGES . 'icon_red_on.gif', IMAGE_ICON_STATUS_OFF) . '</a>';
      }
      if (zen_get_products_to_categories($categories->fields['categories_id'], true, 'products_active') == 'true') {
        echo '&nbsp;&nbsp;' . zen_image(DIR_WS_IMAGES . 'icon_yellow_on.gif', IMAGE_ICON_LINKED);
      }
?>
                </td>
                <td class="dataTableContent" align="right"><?php echo $categories->fields['sort_order']; ?></td>
                <td class="dataTableContent" align="right">
                  <?php echo '<a href="' . zen_href_link_admin(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&cID=' . $categories->fields['categories_id'] . '&action=edit_category') . '">' . zen_image(DIR_WS_IMAGES . 'icon_edit.gif', ICON_EDIT) . '</a>'; ?>
                  <?php echo '<a href="' . zen_href_link_admin(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&cID=' . $categories->fields['categories_id'] . '&action=delete_category') . '">' . zen_image(DIR_WS_IMAGES . 'icon_delete.gif', ICON_DELETE) . '</a>'; ?>
                  <?php echo '<a href="' . zen_href_link_admin(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&cID=' . $categories->fields['categories_id'] . '&action=move_category') . '">' . zen_image(DIR_WS_IMAGES . 'icon_move.gif', ICON_MOVE) . '</a>'; ?>
                </td>
<?php } // action == '' ?>
              </tr>
<?php
      $categories->MoveNext();
    }


    switch ($_SESSION['categories_products_sort_order']) {
      case (0):
        $order_by = " order by p.`products_sort_order`, pd.`products_name`";
        break;
      case (1):
        $order_by = " order by pd.`products_name`";
        break;
      case (2);
        $order_by = " order by p.`products_model`";
        break;
      }

    $products_count = 0;
    if (isset($_GET['search'])) {
      $products_query_raw = ("select p.`products_type`, p.`products_id`, pd.`products_name`, p.`products_quantity`,
                                       p.`products_image`, p.`products_price`, p.`products_date_added`,
                                       p.`products_last_modified`, p.`products_date_available`,
                                       p.`products_status`, p2c.`categories_id`,
                                       p.`products_model`,
                                       p.`products_quantity_order_min`, p.`products_quantity_order_units`, p.`products_priced_by_attribute`,
                                       p.`product_is_free`, p.`product_is_call`, p.`products_quantity_mixed`,
                                       p.`products_quantity_order_max`, p.`products_sort_order`
                                from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd, "
                                       . TABLE_PRODUCTS_TO_CATEGORIES . " p2c
                                where p.`products_id` = pd.`products_id`
                                and pd.`language_id` = '" . (int)$_SESSION['languages_id'] . "'
                                and p.`products_id` = p2c.`products_id`
                                and (
                                pd.`products_name` like '%" . zen_db_input($_GET['search']) . "%'
                                or pd.`products_description` like '%" . zen_db_input($_GET['search']) . "%'
                                or p.`products_model` like '%" . zen_db_input($_GET['search']) . "%')" .
                                $order_by);
    } else {
      $products_query_raw = ("select p.`products_type`, p.`products_id`, pd.`products_name`, p.`products_quantity`,
                                       p.`products_image`, p.`products_price`, p.`products_date_added`,
                                       p.`products_last_modified`, p.`products_date_available`,
                                       p.`products_status`, p.`products_model`,
                                       p.`products_quantity_order_min`, p.`products_quantity_order_units`, p.`products_priced_by_attribute`,
                                       p.`product_is_free`, p.`product_is_call`, p.`products_quantity_mixed`,
                                       p.`products_quantity_order_max`, p.`products_sort_order`
                                from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c
                                where p.`products_id` = pd.`products_id`
                                and pd.`language_id` = '" . (int)$_SESSION['languages_id'] . "'
                                and p.`products_id` = p2c.`products_id`
                                and p2c.`categories_id` = '" . (int)$current_category_id . "'" .
                                $order_by);
    }
// Split Page
// reset page when page is unknown
if ( isset($_GET['page']) and $_GET['page'] == '' and $_GET['pID'] != '') {
  $old_page = $_GET['page'];
  $check_page = $gBitDb->Execute($products_query_raw);
  if ($check_page->RecordCount() > MAX_DISPLAY_RESULTS_CATEGORIES) {
    $check_count=1;
    while (!$check_page->EOF) {
      if ($check_page->fields['products_id'] == $_GET['pID']) {
        break;
      }
      $check_count++;
      $check_page->MoveNext();
    }
    $_GET['page'] = round((($check_count/MAX_DISPLAY_RESULTS_CATEGORIES)+(fmod($check_count,MAX_DISPLAY_RESULTS_CATEGORIES) !=0 ? .5 : 0)),0);
    $page = $_GET['page'];
    if ($old_page != $_GET['page']) {
//      zen_redirect(zen_href_link_admin(FILENAME_CATEGORIES, 'cPath=' . $_GET['cPath'] . '&pID=' . $_GET['pID'] . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')));
    }
  } else {
    $_GET['page'] = 1;
  }
}
    $offset = new splitPageResults($_GET['page'], MAX_DISPLAY_RESULTS_CATEGORIES, $products_query_raw, $products_query_numrows);
    $products = $gBitDb->query($products_query_raw, NULL, MAX_DISPLAY_RESULTS_CATEGORIES, $offset);
// Split Page

    while (!$products->EOF) {
      $products_count++;
      $rows++;

// Get categories_id for product if search
      if (isset($_GET['search'])) $cPath = $products->fields['categories_id'];

      if ( (!isset($_GET['pID']) && !isset($_GET['cID']) || (isset($_GET['pID']) && ($_GET['pID'] == $products->fields['products_id']))) && !isset($pInfo) && !isset($cInfo) && (substr($action, 0, 3) != 'new')) {
// find out the rating average from customer reviews
        $reviews = $gBitDb->Execute("select (avg(`reviews_rating`) / 5 * 100) as `average_rating`
                                 from " . TABLE_REVIEWS . "
                                 where `products_id` = '" . (int)$products->fields['products_id'] . "'");
        $pInfo_array = array_merge($products->fields, $reviews->fields);
        $pInfo = new objectInfo($pInfo_array);
      }

// Split Page
      $type_handler = $zc_products->get_admin_handler($products->fields['products_type']);
      if (isset($pInfo) && is_object($pInfo) && ($products->fields['products_id'] == $pInfo->products_id) ) {
        echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . zen_href_link_admin($type_handler , 'page=' . $_GET['page'] . '&product_type=' . $products->fields['products_type'] . '&cPath=' . $cPath . '&pID=' . $products->fields['products_id'] . '&action=new_product') . '\'">' . "\n";
      } else {
        echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . zen_href_link_admin($type_handler , 'page=' . $_GET['page'] . '&product_type=' . $products->fields['products_type'] . '&cPath=' . $cPath . '&pID=' . $products->fields['products_id'] . '&action=new_product') . '\'">' . "\n";
      }
// Split Page
?>
                <td class="dataTableContent" width="20" align="right"><?php echo $products->fields['products_id']; ?></td>
                <td class="dataTableContent"><?php echo '<a href="' . zen_href_link_admin(FILENAME_PRODUCT, 'cPath=' . $cPath . '&pID=' . $products->fields['products_id'] . '&action=new_product_preview&read=only' . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')) . '">' . zen_image(DIR_WS_ICONS . 'preview.gif', ICON_PREVIEW) . '</a>&nbsp;' . $products->fields['products_name']; ?></td>
                <td class="dataTableContent" align="center"><?php echo zen_image_OLD($products->fields['products_image']); ?></td>
                <td class="dataTableContent"><?php echo $products->fields['products_model']; ?></td>
                <td colspan="2" class="dataTableContent" align="right"><?php echo CommerceProduct::getDisplayPrice($products->fields['products_id']); ?></td>
                <td class="dataTableContent" align="right"><?php echo $products->fields['products_quantity']; ?></td>
                <td class="dataTableContent" width="50" align="left">
<?php
      if ($products->fields['products_status'] == '1') {
        echo '<a href="' . zen_href_link_admin(FILENAME_CATEGORIES, 'action=setflag&flag=0&pID=' . $products->fields['products_id'] . '&cPath=' . $cPath . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')) . '">' . zen_image(DIR_WS_IMAGES . 'icon_green_on.gif', IMAGE_ICON_STATUS_ON) . '</a>';
      } else {
        echo '<a href="' . zen_href_link_admin(FILENAME_CATEGORIES, 'action=setflag&flag=1&pID=' . $products->fields['products_id'] . '&cPath=' . $cPath . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')) . '">' . zen_image(DIR_WS_IMAGES . 'icon_red_on.gif', IMAGE_ICON_STATUS_OFF) . '</a>';
      }
      if (zen_get_product_is_linked($products->fields['products_id']) == 'true') {
        echo '&nbsp;&nbsp;' . zen_image(DIR_WS_IMAGES . 'icon_yellow_on.gif', IMAGE_ICON_LINKED) . '<br>';
      }
?>
                </td>
<?php if ($action == '') { ?>
                <td class="dataTableContent" align="right"><?php echo $products->fields['products_sort_order']; ?></td>
                <td class="dataTableContent" align="right">
        <?php echo '<a href="' . zen_href_link_admin($type_handler, 'cPath=' . $cPath . '&product_type=' . $products->fields['products_type'] . '&pID=' . $products->fields['products_id']  . '&action=new_product' . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')) . '">' . zen_image(DIR_WS_IMAGES . 'icon_edit.gif', ICON_EDIT) . '</a>'; ?>
        <?php echo '<a href="' . zen_href_link_admin($type_handler, 'cPath=' . $cPath . '&product_type=' . $products->fields['products_type'] . '&pID=' . $products->fields['products_id'] . '&action=delete_product' . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')) . '">' . zen_image(DIR_WS_IMAGES . 'icon_delete.gif', ICON_DELETE) . '</a>'; ?>
        <?php echo '<a href="' . zen_href_link_admin($type_handler, 'cPath=' . $cPath . '&product_type=' . $products->fields['products_type'] . '&pID=' . $products->fields['products_id'] . '&action=move_product' . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')) . '">' . zen_image(DIR_WS_IMAGES . 'icon_move.gif', ICON_MOVE) . '</a>'; ?>
        <?php echo '<a href="' . zen_href_link_admin($type_handler, 'cPath=' . $cPath . '&product_type=' . $products->fields['products_type'] . '&pID=' . $products->fields['products_id'] .'&action=copy_to' . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')) . '">' . zen_image(DIR_WS_IMAGES . 'icon_copy_to.gif', ICON_COPY_TO) . '</a>'; ?>
<?php

		echo '<a href="products_options.php?products_id=' . $products->fields['products_id'] . '">' . (zen_has_product_attributes($products->fields['products_id'], 'false')  ? zen_image(DIR_WS_IMAGES . 'icon_attributes_on.gif', ICON_ATTRIBUTES) : zen_image(DIR_WS_IMAGES . 'icon_attributes.gif', ICON_ATTRIBUTES)) . '</a>';


        if ($zc_products->get_allow_add_to_cart($products->fields['products_id']) == "Y") {
          echo '<a href="' . zen_href_link_admin(FILENAME_PRODUCTS_PRICE_MANAGER, 'products_id=' . $products->fields['products_id'] . '&current_category_id=' . $current_category_id) . '">' . zen_image(DIR_WS_IMAGES . 'icon_products_price_manager.gif', ICON_PRODUCTS_PRICE_MANAGER) . '</a>';
        }
// meta tags
        if (zen_get_metatags_keywords($products->fields['products_id'], (int)$_SESSION['languages_id']) or zen_get_metatags_description($products->fields['products_id'], (int)$_SESSION['languages_id'])) {
          echo ' <a href="' . zen_href_link_admin($type_handler, 'page=' . $_GET['page'] . '&product_type=' . $products->fields['products_type'] . '&cPath=' . $cPath . '&pID=' . $products->fields['products_id']  . '&action=new_product_meta_tags') . '">' . zen_image(DIR_WS_IMAGES . 'icon_edit_metatags_on.gif', ICON_METATAGS_ON) . '</a>';
        } else {
          echo ' <a href="' . zen_href_link_admin($type_handler, 'page=' . $_GET['page'] . '&product_type=' . $products->fields['products_type'] . '&cPath=' . $cPath . '&pID=' . $products->fields['products_id']  . '&action=new_product_meta_tags') . '">' . zen_image(DIR_WS_IMAGES . 'icon_edit_metatags_off.gif', ICON_METATAGS_OFF) . '</a>';
        }
?>
<?php } // action == '' ?>

                </td>
              </tr>
<?php
      $products->MoveNext();
    }

    $cPath_back = '';
    if( !empty( $cPath_array ) ) {
      for ($i=0, $n=sizeof($cPath_array)-1; $i<$n; $i++) {
        if (empty($cPath_back)) {
          $cPath_back .= $cPath_array[$i];
        } else {
          $cPath_back .= '_' . $cPath_array[$i];
        }
      }
    }

    $cPath_back = (zen_not_null($cPath_back)) ? 'cPath=' . $cPath_back . '&' : '';
?>
<?php if ($action == '') { ?>

              <tr>
                <td colspan="3"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText"><?php echo TEXT_CATEGORIES . '&nbsp;' . $categories_count . '<br />' . TEXT_PRODUCTS . '&nbsp;' . $products_count; ?></td>
                    <td align="right" class="smallText"><?php if( !empty( $cPath_array ) ) echo '<a href="' . zen_href_link_admin(FILENAME_CATEGORIES, $cPath_back . 'cID=' . $current_category_id) . '">' . zen_image_button('button_back.gif', IMAGE_BACK) . '</a>&nbsp;'; if (!isset($_GET['search'])) echo '<a href="' . zen_href_link_admin(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&action=new_category') . '">' . zen_image_button('button_new_category.gif', IMAGE_NEW_CATEGORY) . '</a>&nbsp;'; ?>
<?php
	if( !empty( $cPath ) ) {
?>
<form name="newproduct" action="<?php echo zen_href_link_admin(FILENAME_CATEGORIES, '', 'NONSSL'); ?>" method = "get"><?php    echo zen_image_submit('button_new_product.gif', IMAGE_NEW_PRODUCT); ?>
<?php

  $sql = "select ptc.`product_type_id`, pt.`type_name` from " . TABLE_PRODUCT_TYPES_TO_CATEGORY . " ptc, " . TABLE_PRODUCT_TYPES . " pt
          where ptc.`category_id` = '" . $current_category_id . "'
          and pt.`type_id` = ptc.`product_type_id`";
  $restrict_types = $gBitDb->Execute($sql);
  if ($restrict_types->RecordCount() >0 ) {
    $product_restrict_types_array = array();
    while (!$restrict_types->EOF) {
      $product_restrict_types_array[] = array('id' => $restrict_types->fields['product_type_id'],
                                         'text' => $restrict_types->fields['type_name']);
      $restrict_types->MoveNext();
    }
   } else {
    $product_restrict_types_array = $product_types_array;
  }
?>
<?php echo '&nbsp;&nbsp;' . zen_draw_pull_down_menu('product_type', $product_restrict_types_array); ?>
           <input type="hidden" name="cPath" value="<?php echo $cPath; ?>">
           <input type="hidden" name="action" value="new_product">
<?php
	}
?>
          </form>
          &nbsp;</td>
                  </tr>
                </table></td>
              </tr>
<?php } // turn off when editing ?>
            </table></td></tr>
      <tr>
        <td><table border="0" cellspacing="0" cellpadding="0" style="width:550px">
          <tr>
            <td class="smallText" align="center" valign="top"><?php echo TEXT_LEGEND; ?></td>
            <td class="smallText" align="center" valign="top"><?php echo TEXT_LEGEND_STATUS_OFF . '<br />' . zen_image(DIR_WS_IMAGES . 'icon_red_on.gif', IMAGE_ICON_STATUS_OFF); ?></td>
            <td class="smallText" align="center" valign="top"><?php echo TEXT_LEGEND_STATUS_ON . '<br />' . zen_image(DIR_WS_IMAGES . 'icon_green_on.gif', IMAGE_ICON_STATUS_ON); ?></td>
            <td class="smallText" align="center" valign="top"><?php echo TEXT_LEGEND_LINKED . '<br />' . zen_image(DIR_WS_IMAGES . 'icon_yellow_on.gif', IMAGE_ICON_LINKED); ?></td>
            <td class="smallText" align="center"valign="top"><?php echo TEXT_LEGEND_META_TAGS . '<br />' . TEXT_YES . '&nbsp;' . TEXT_NO . '<br />' . zen_image(DIR_WS_IMAGES . 'icon_edit_metatags_on.gif', ICON_METATAGS_ON) . '&nbsp;' . zen_image(DIR_WS_IMAGES . 'icon_edit_metatags_off.gif', ICON_METATAGS_OFF); ?></td>
          </tr>
        </table></td>
