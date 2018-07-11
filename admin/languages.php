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

  require('includes/application_top.php');
  $action = (isset($_GET['action']) ? $_GET['action'] : '');
  if (zen_not_null($action)) {
    switch ($action) {
      case 'insert':
        $name = zen_db_prepare_input($_POST['name']);
        $code = zen_db_prepare_input($_POST['code']);
        $image = zen_db_prepare_input($_POST['image']);
        $directory = zen_db_prepare_input($_POST['directory']);
        $sort_order = zen_db_prepare_input($_POST['sort_order']);
        $check = $gBitDb->Execute("select * from " . TABLE_LANGUAGES . " where `code` = '" . $code . "'");
        if ($check->RecordCount() > 0) {
          $messageStack->add(ERROR_DUPLICATE_LANGUAGE_CODE, 'error');
        } else {

          $gBitDb->Execute("insert into " . TABLE_LANGUAGES . "
                        (`name`, `code`, `image`, `directory`, `sort_order`)
                        values ('" . zen_db_input($name) . "', '" . zen_db_input($code) . "',
                                '" . zen_db_input($image) . "', '" . zen_db_input($directory) . "',
                                '" . zen_db_input($sort_order) . "')");

          $insert_id = zen_db_insert_id( TABLE_LANGUAGES, 'languages_id' );

// create additional categories_description records

          $categories = $gBitDb->Execute("select c.`categories_id`, cd.`categories_name`,
                                    `categories_description`
                                      from " . TABLE_CATEGORIES . " c
                                      left join " . TABLE_CATEGORIES_DESCRIPTION . " cd
                                      on c.`categories_id` = cd.`categories_id`
                                      where cd.`language_id` = '" . (int)$_SESSION['languages_id'] . "'");

          while (!$categories->EOF) {
            $gBitDb->Execute("insert into " . TABLE_CATEGORIES_DESCRIPTION . "
                          (`categories_id`, `language_id`, `categories_name`,
                          `categories_description`)
                          values ('" . (int)$categories->fields['categories_id'] . "', '" . (int)$insert_id . "',
                                  '" . zen_db_input($categories->fields['categories_name']) . "',
                                  '" . zen_db_input($categories->fields['categories_description']) . "')");
            $categories->MoveNext();
          }

// create additional products_description records
          $products = $gBitDb->Execute("select p.`products_id`, pd.`products_name`, pd.`products_description`,
                                           pd.`products_url`
                                    from " . TABLE_PRODUCTS . " p
                                    left join " . TABLE_PRODUCTS_DESCRIPTION . " pd
                                    on p.`products_id` = pd.`products_id`
                                    where pd.`language_id` = '" . (int)$_SESSION['languages_id'] . "'");

          while (!$products->EOF) {
            $gBitDb->Execute("insert into " . TABLE_PRODUCTS_DESCRIPTION . "
                        (`products_id`, `language_id`, `products_name`, `products_description`, `products_url`)
                        values ('" . (int)$products->fields['products_id'] . "',
                                '" . (int)$insert_id . "',
                                '" . zen_db_input($products->fields['products_name']) . "',
                                '" . zen_db_input($products->fields['products_description']) . "',
                                '" . zen_db_input($products->fields['products_url']) . "')");
            $products->MoveNext();
          }

// create additional products_options records
          $products_options = $gBitDb->Execute("select `products_options_id`, `products_options_name`,
                              `products_options_sort_order`, `products_options_type`, `products_options_length`, `products_options_comment`, 
                              `products_options_images_per_row`, `products_options_images_style`
                                           from " . TABLE_PRODUCTS_OPTIONS . "
                                           where `language_id` = '" . (int)$_SESSION['languages_id'] . "'");

          while (!$products_options->EOF) {
            $gBitDb->Execute("insert into " . TABLE_PRODUCTS_OPTIONS . "
		                    (`products_options_id`, `language_id`, `products_options_name`,
		                    `products_options_sort_order`, `products_options_type`, `products_options_length`, `products_options_comment`, `products_options_images_per_row`, `products_options_images_style`)
      						      values ('" . (int)$products_options->fields['products_options_id'] . "',
						                    '" . (int)$insert_id . "',
						                    '" . zen_db_input($products_options->fields['products_options_name']) . "',
						                    '" . zen_db_input($products_options->fields['products_options_sort_order']) . "',
						                    '" . zen_db_input($products_options->fields['products_options_type']) . "',
						                    '" . zen_db_input($products_options->fields['products_options_length']) . "',
						                    '" . zen_db_input($products_options->fields['products_options_comment']) . "',
						                    '" . zen_db_input($products_options->fields['products_options_images_per_row']) . "',
						                    '" . zen_db_input($products_options->fields['products_options_images_style']) . "')");

            $products_options->MoveNext();
          }

// create additional manufacturers_info records
          $manufacturers = $gBitDb->Execute("select m.`manufacturers_id`, mi.`manufacturers_url`
		                               from " . TABLE_MANUFACTURERS . " m
						   left join " . TABLE_MANUFACTURERS_INFO . " mi
						   on m.`manufacturers_id` = mi.`manufacturers_id`
						   where mi.`languages_id` = '" . (int)$_SESSION['languages_id'] . "'");

          while (!$manufacturers->EOF) {
            $gBitDb->Execute("insert into " . TABLE_MANUFACTURERS_INFO . "
		                    (`manufacturers_id`, `languages_id`, `manufacturers_url`)
					              values ('" . $manufacturers->fields['manufacturers_id'] . "', '" . (int)$insert_id . "',
					                      '" . zen_db_input($manufacturers->fields['manufacturers_url']) . "')");

            $manufacturers->MoveNext();
          }

// create additional suppliers_info records
          $suppliers = $gBitDb->Execute("select m.`suppliers_id`, mi.`suppliers_url`
		                               from " . TABLE_SUPPLIERS . " m
						   left join " . TABLE_SUPPLIERS_INFO . " mi
						   on m.`suppliers_id` = mi.`suppliers_id`
						   where mi.`languages_id` = '" . (int)$_SESSION['languages_id'] . "'");

          while (!$suppliers->EOF) {
            $gBitDb->Execute("insert into " . TABLE_SUPPLIERS_INFO . "
		                    (`suppliers_id`, `languages_id`, `suppliers_url`)
					              values ('" . $suppliers->fields['suppliers_id'] . "', '" . (int)$insert_id . "',
					                      '" . zen_db_input($suppliers->fields['suppliers_url']) . "')");

            $suppliers->MoveNext();
          }


// create additional orders_status records
          $orders_status = $gBitDb->Execute("select `orders_status_id`, `orders_status_name`
		                               from " . TABLE_ORDERS_STATUS . "
					   where `language_id` = '" . (int)$_SESSION['languages_id'] . "'");

          while (!$orders_status->EOF) {
            $gBitDb->Execute("insert into " . TABLE_ORDERS_STATUS . "
		                      (`orders_status_id`, `language_id`, `orders_status_name`)
					                values ('" . (int)$orders_status->fields['orders_status_id'] . "',
				                          '" . (int)$insert_id . "',
				                          '" . zen_db_input($orders_status->fields['orders_status_name']) . "')");
            $orders_status->MoveNext();
          }
          if (isset($_POST['default']) && ($_POST['default'] == 'on')) {
            $gBitDb->Execute("update " . TABLE_CONFIGURATION . "
       	                set `configuration_value` = '" . zen_db_input($code) . "'
			where `configuration_key` = 'DEFAULT_LANGUAGE'");
          }

// create additional coupons_description records
          $coupons = $gBitDb->Execute("select c.`coupon_id`, cd.`coupon_name`, cd.`coupon_description`
                                    from " . TABLE_COUPONS . " c
                                    left join " . TABLE_COUPONS_DESCRIPTION . " cd
                                    on c.`coupon_id` = cd.`coupon_id`
                                    where cd.`coupon_id` = '" . (int)$_SESSION['languages_id'] . "'");

          while (!$coupons->EOF) {
            $gBitDb->Execute("insert into " . TABLE_COUPONS_DESCRIPTION . "
                        (`coupon_id`, language_id, `coupon_name`, `coupon_description`)
                        values ('" . (int)$coupons->fields['coupon_id'] . "',
                                '" . (int)$insert_id . "',
                                '" . zen_db_input($coupons->fields['coupon_name']) . "',
                                '" . zen_db_input($coupons->fields['coupon_description']) . "')");
            $coupons->MoveNext();
          }

          zen_redirect(zen_href_link_admin(FILENAME_LANGUAGES, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . 'lID=' . $insert_id));
        }

        break;
      case 'save':
        $lID = zen_db_prepare_input($_GET['lID']);
        $name = zen_db_prepare_input($_POST['name']);
        $code = zen_db_prepare_input($_POST['code']);
        $image = zen_db_prepare_input($_POST['image']);
        $directory = zen_db_prepare_input($_POST['directory']);
        $sort_order = zen_db_prepare_input($_POST['sort_order']);
        $gBitDb->query( "UPDATE " . TABLE_LANGUAGES . " SET `name` = ?, `code` = ?, `image` = ?, `directory`=?, `sort_order` = ?  WHERE `languages_id` = ?", array( zen_db_input($name), zen_db_input($code),  zen_db_input($image), zen_db_input($directory), zen_db_input($sort_order), (int)$lID ) );

        if ($_POST['default'] == 'on') {
          $gBitDb->Execute("update " . TABLE_CONFIGURATION . "
		                set `configuration_value` = '" . zen_db_input($code) . "'
						where `configuration_key` = 'DEFAULT_LANGUAGE'");
        }
        zen_redirect(zen_href_link_admin(FILENAME_LANGUAGES, 'page=' . $_GET['page'] . '&lID=' . $_GET['lID']));
        break;
      case 'deleteconfirm':
        // demo active test
        if (zen_admin_demo()) {
          $_GET['action']= '';
          $messageStack->add_session(ERROR_ADMIN_DEMO, 'caution');
          zen_redirect(zen_href_link_admin(FILENAME_LANGUAGES, 'page=' . $_GET['page']));
        }
        $lID = zen_db_prepare_input($_GET['lID']);
        $lng = $gBitDb->Execute("select `languages_id`
		                     from " . TABLE_LANGUAGES . "
							 where `code` = `" . DEFAULT_CURRENCY . "'");

        if ($lng->fields['languages_id'] == $lID) {
          $gBitDb->Execute("update " . TABLE_CONFIGURATION . "
		                set `configuration_value` = ''
						where `configuration_key` = 'DEFAULT_CURRENCY'");
        }
        $gBitDb->Execute("delete from " . TABLE_CATEGORIES_DESCRIPTION . " where `language_id` = '" . (int)$lID . "'");
        $gBitDb->Execute("delete from " . TABLE_PRODUCTS_DESCRIPTION . " where `language_id` = '" . (int)$lID . "'");
        $gBitDb->Execute("delete from " . TABLE_PRODUCTS_OPTIONS . " where `language_id` = '" . (int)$lID . "'");
        $gBitDb->Execute("delete from " . TABLE_MANUFACTURERS_INFO . " where `languages_id` = '" . (int)$lID . "'");
        $gBitDb->Execute("delete from " . TABLE_ORDERS_STATUS . " where `language_id` = '" . (int)$lID . "'");
        $gBitDb->Execute("delete from " . TABLE_LANGUAGES . " where `languages_id` = '" . (int)$lID . "'");
        $gBitDb->Execute("delete from " . TABLE_COUPONS_DESCRIPTION . " where `language_id` = '" . (int)$lID . "'");
        zen_redirect(zen_href_link_admin(FILENAME_LANGUAGES, 'page=' . $_GET['page']));
        break;
      case 'delete':
        $lID = zen_db_prepare_input($_GET['lID']);
        $lng = $gBitDb->Execute("select `code` from " . TABLE_LANGUAGES . " where `languages_id` = '" . (int)$lID . "'");
        $remove_language = true;
        if ($lng->fields['code'] == DEFAULT_LANGUAGE) {
          $remove_language = false;
          $messageStack->add(ERROR_REMOVE_DEFAULT_LANGUAGE, 'error');
        }
        break;
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
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_LANGUAGE_NAME; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_LANGUAGE_CODE; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php
  $languages_query_raw = "select `languages_id`, `name`, `code`, `image`, `directory`, `sort_order` from " . TABLE_LANGUAGES . " order by `sort_order`";
  $languages_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS, $languages_query_raw, $languages_query_numrows);
  $languages = $gBitDb->Execute($languages_query_raw);
  while (!$languages->EOF) {
    if ((!isset($_GET['lID']) || (isset($_GET['lID']) && ($_GET['lID'] == $languages->fields['languages_id']))) && !isset($lInfo) && (substr($action, 0, 3) != 'new')) {
      $lInfo = new objectInfo($languages->fields);
    }
    if (isset($lInfo) && is_object($lInfo) && ($languages->fields['languages_id'] == $lInfo->languages_id) ) {
      echo '                  <tr id="defaultSelected" class="info" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . zen_href_link_admin(FILENAME_LANGUAGES, 'page=' . $_GET['page'] . '&lID=' . $lInfo->languages_id . '&action=edit') . '\'">' . "\n";
    } else {
      echo '                  <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . zen_href_link_admin(FILENAME_LANGUAGES, 'page=' . $_GET['page'] . '&lID=' . $languages->fields['languages_id']) . '\'">' . "\n";
    }
    if (DEFAULT_LANGUAGE == $languages->fields['code']) {
      echo '                <td class="dataTableContent"><b>' . $languages->fields['name'] . ' (' . TEXT_DEFAULT . ')</b></td>' . "\n";
    } else {
      echo '                <td class="dataTableContent">' . $languages->fields['name'] . '</td>' . "\n";
    }
?>
                <td class="dataTableContent"><?php echo $languages->fields['code']; ?></td>
                <td class="dataTableContent" align="right"><?php if (isset($lInfo) && is_object($lInfo) && ($languages->fields['languages_id'] == $lInfo->languages_id)) { echo zen_image(DIR_WS_IMAGES . 'icon_arrow_right.gif'); } else { echo '<a href="' . zen_href_link_admin(FILENAME_LANGUAGES, 'page=' . $_GET['page'] . '&lID=' . $languages->fields['languages_id']) . '">' . zen_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
    $languages->MoveNext();
  }
?>
              <tr>
                <td colspan="3"><table>
                  <tr>
                    <td class="smallText" valign="top"><?php echo $languages_split->display_count($languages_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_LANGUAGES); ?></td>
                    <td class="smallText" align="right"><?php echo $languages_split->display_links($languages_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?></td>
                  </tr>
<?php
  if (empty($action)) {
?>
                  <tr>
                    <td align="right" colspan="2"><?php echo '<a href="' . zen_href_link_admin(FILENAME_LANGUAGES, 'page=' . $_GET['page'] . '&lID=' . $lInfo->languages_id . '&action=new') . '">' . zen_image_button('button_new_language.gif', IMAGE_NEW_LANGUAGE) . '</a>'; ?></td>
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
    case 'new':
      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_NEW_LANGUAGE . '</b>');
      $contents = array('form' => zen_draw_form_admin('languages', FILENAME_LANGUAGES, 'action=insert'));
      $contents[] = array('text' => TEXT_INFO_INSERT_INTRO);
      $contents[] = array('text' => '<br>' . TEXT_INFO_LANGUAGE_NAME . '<br>' . zen_draw_input_field('name'));
      $contents[] = array('text' => '<br>' . TEXT_INFO_LANGUAGE_CODE . '<br>' . zen_draw_input_field('code'));
      $contents[] = array('text' => '<br>' . TEXT_INFO_LANGUAGE_IMAGE . '<br>' . zen_draw_input_field('image', 'icon.gif'));
      $contents[] = array('text' => '<br>' . TEXT_INFO_LANGUAGE_DIRECTORY . '<br>' . zen_draw_input_field('directory'));
      $contents[] = array('text' => '<br>' . TEXT_INFO_LANGUAGE_SORT_ORDER . '<br>' . zen_draw_input_field('sort_order'));
      $contents[] = array('text' => '<br>' . zen_draw_checkbox_field('default') . ' ' . TEXT_SET_DEFAULT);
      $contents[] = array('align' => 'center', 'text' => '<br>' . zen_image_submit('button_insert.gif', IMAGE_INSERT) . ' <a href="' . zen_href_link_admin(FILENAME_LANGUAGES, 'page=' . $_GET['page'] . '&lID=' . $_GET['lID']) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    case 'edit':
      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_EDIT_LANGUAGE . '</b>');
      $contents = array('form' => zen_draw_form_admin('languages', FILENAME_LANGUAGES, 'page=' . $_GET['page'] . '&lID=' . $lInfo->languages_id . '&action=save'));
      $contents[] = array('text' => TEXT_INFO_EDIT_INTRO);
      $contents[] = array('text' => '<br>' . TEXT_INFO_LANGUAGE_NAME . '<br>' . zen_draw_input_field('name', $lInfo->name));
      $contents[] = array('text' => '<br>' . TEXT_INFO_LANGUAGE_CODE . '<br>' . zen_draw_input_field('code', $lInfo->code));
      $contents[] = array('text' => '<br>' . TEXT_INFO_LANGUAGE_IMAGE . '<br>' . zen_draw_input_field('image', $lInfo->image));
      $contents[] = array('text' => '<br>' . TEXT_INFO_LANGUAGE_DIRECTORY . '<br>' . zen_draw_input_field('directory', $lInfo->directory));
      $contents[] = array('text' => '<br>' . TEXT_INFO_LANGUAGE_SORT_ORDER . '<br>' . zen_draw_input_field('sort_order', $lInfo->sort_order));
      if (DEFAULT_LANGUAGE != $lInfo->code) $contents[] = array('text' => '<br>' . zen_draw_checkbox_field('default') . ' ' . TEXT_SET_DEFAULT);
      $contents[] = array('align' => 'center', 'text' => '<br>' . zen_image_submit('button_update.gif', IMAGE_UPDATE) . ' <a href="' . zen_href_link_admin(FILENAME_LANGUAGES, 'page=' . $_GET['page'] . '&lID=' . $lInfo->languages_id) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    case 'delete':
      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_DELETE_LANGUAGE . '</b>');
      $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
      $contents[] = array('text' => '<br><b>' . $lInfo->name . '</b>');
      $contents[] = array('align' => 'center', 'text' => '<br>' . (($remove_language) ? '<a href="' . zen_href_link_admin(FILENAME_LANGUAGES, 'page=' . $_GET['page'] . '&lID=' . $lInfo->languages_id . '&action=deleteconfirm') . '">' . zen_image_button('button_delete.gif', IMAGE_DELETE) . '</a>' : '') . ' <a href="' . zen_href_link_admin(FILENAME_LANGUAGES, 'page=' . $_GET['page'] . '&lID=' . $lInfo->languages_id) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    default:
      if (is_object($lInfo)) {
        $heading[] = array('text' => '<b>' . $lInfo->name . '</b>');
        $contents[] = array('align' => 'center', 'text' => '<a href="' . zen_href_link_admin(FILENAME_LANGUAGES, 'page=' . $_GET['page'] . '&lID=' . $lInfo->languages_id . '&action=edit') . '">' . zen_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . zen_href_link_admin(FILENAME_LANGUAGES, 'page=' . $_GET['page'] . '&lID=' . $lInfo->languages_id . '&action=delete') . '">' . zen_image_button('button_delete.gif', IMAGE_DELETE) . '</a>');
        $contents[] = array('text' => '<br>' . TEXT_INFO_LANGUAGE_NAME . ' ' . $lInfo->name);
        $contents[] = array('text' => TEXT_INFO_LANGUAGE_CODE . ' ' . $lInfo->code);
        $contents[] = array('text' => '<br>' . zen_image(DIR_WS_CATALOG_LANGUAGES . $lInfo->directory . '/images/' . $lInfo->image, $lInfo->name));
        $contents[] = array('text' => '<br>' . TEXT_INFO_LANGUAGE_DIRECTORY . '<br>' . DIR_WS_CATALOG_LANGUAGES . '<b>' . $lInfo->directory . '</b>');
        $contents[] = array('text' => '<br>' . TEXT_INFO_LANGUAGE_SORT_ORDER . ' ' . $lInfo->sort_order);
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
