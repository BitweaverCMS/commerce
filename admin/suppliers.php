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

if (!is_dir(DIR_FS_CATALOG_IMAGES . 'suppliers')) mkdir(DIR_FS_CATALOG_IMAGES . 'suppliers');

  $action = (isset($_GET['action']) ? $_GET['action'] : '');
  if (zen_not_null($action)) {
    switch ($action) {
      case 'insert':
      case 'save':
        if (isset($_GET['mID'])) $suppliers_id = zen_db_prepare_input($_GET['mID']);
        $suppliers_name = zen_db_prepare_input($_POST['suppliers_name']);

        $sql_data_array = array('suppliers_name' => $suppliers_name);

        if ($action == 'insert') {
          $insert_sql_data = array('date_added' => $gBitDb->NOW());

          $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

          $gBitDb->associateInsert(TABLE_SUPPLIERS, $sql_data_array);
          $suppliers_id = zen_db_insert_id( TABLE_SUPPLIERS, 'suppliers_id' );
        } elseif ($action == 'save') {
          $update_sql_data = array('last_modified' => $gBitDb->NOW());

          $sql_data_array = array_merge($sql_data_array, $update_sql_data);
          $gBitDb->associateUpdate(TABLE_SUPPLIERS, $sql_data_array, array( 'suppliers_id'=> (int)$suppliers_id ) );

        }

        $suppliers_image = new upload('suppliers_image');
        $suppliers_image->set_destination(DIR_FS_CATALOG_IMAGES . $_POST['img_dir']);
        if ( $suppliers_image->parse() &&  $suppliers_image->save()) {
          // remove image from database if none
         if ($suppliers_image->filename != 'none') {
            $gBitDb->Execute("update " . TABLE_SUPPLIERS . "
                          set `suppliers_image` = '" .  $_POST['img_dir'] . $suppliers_image->filename . "'
                          where `suppliers_id` = '" . (int)$suppliers_id . "'");
          } else {
            $gBitDb->Execute("update " . TABLE_SUPPLIERS . "
                          set `suppliers_image` = ''
                          where `suppliers_id` = '" . (int)$suppliers_id . "'");
          }
        }

        $languages = zen_get_languages();
        for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
          $suppliers_url_array = $_POST['suppliers_url'];
          $language_id = $languages[$i]['id'];

          $sql_data_array = array('suppliers_url' => zen_db_prepare_input($suppliers_url_array[$language_id]));

          if ($action == 'insert') {
            $insert_sql_data = array('suppliers_id' => $suppliers_id,
                                     'languages_id' => $language_id);

            $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

            $gBitDb->associateInsert(TABLE_SUPPLIERS_INFO, $sql_data_array);
          } elseif ($action == 'save') {
            $gBitDb->associateUpdate(TABLE_SUPPLIERS_INFO, $sql_data_array, array( 'suppliers_id' => (int)$suppliers_id , 'languages_id' => (int)$language_id) );
          }
        }

        zen_redirect(zen_href_link_admin(FILENAME_SUPPLIERS, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . 'mID=' . $suppliers_id));
        break;
      case 'deleteconfirm':
        // demo active test
        if (zen_admin_demo()) {
          $_GET['action']= '';
          $messageStack->add_session(ERROR_ADMIN_DEMO, 'caution');
          zen_redirect(zen_href_link_admin(FILENAME_SUPPLIERS, 'page=' . $_GET['page']));
        }
        $suppliers_id = zen_db_prepare_input($_GET['mID']);

        if (isset($_POST['delete_image']) && ($_POST['delete_image'] == 'on')) {
          $supplier = $gBitDb->Execute("select `suppliers_image`
                                        from " . TABLE_SUPPLIERS . "
                                        where `suppliers_id` = '" . (int)$suppliers_id . "'");

          $image_location = DIR_FS_CATALOG_IMAGES . $supplier->fields['suppliers_image'];

          if (file_exists($image_location)) @unlink($image_location);
        }

        $gBitDb->Execute("delete from " . TABLE_SUPPLIERS . "
                      where `suppliers_id` = '" . (int)$suppliers_id . "'");
        $gBitDb->Execute("delete from " . TABLE_SUPPLIERS_INFO . "
                      where `suppliers_id` = '" . (int)$suppliers_id . "'");

        if (isset($_POST['delete_products']) && ($_POST['delete_products'] == 'on')) {
          $products = $gBitDb->Execute("select products_id
                                    from " . TABLE_PRODUCTS . "
                                    where suppliers_id = '" . (int)$suppliers_id . "'");

          while (!$products->EOF) {
            zen_remove_product($products->fields['products_id']);
            $products->MoveNext();
          }
        } else {
          $gBitDb->Execute("update " . TABLE_PRODUCTS . "
                        set `suppliers_id` = ''
                        where `suppliers_id` = '" . (int)$suppliers_id . "'");
        }

        zen_redirect(zen_href_link_admin(FILENAME_SUPPLIERS, 'page=' . $_GET['page']));
        break;
    }
  }
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css"/>
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
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_SUPPLIERS; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php
  $suppliers_query_raw = "select `suppliers_id`, `suppliers_name`, `suppliers_image`, `date_added`, `last_modified` from " . TABLE_SUPPLIERS . " order by `suppliers_name`";
  $suppliers_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS, $suppliers_query_raw, $suppliers_query_numrows);
  $suppliers = $gBitDb->Execute($suppliers_query_raw);
  while (!$suppliers->EOF) {
    if ((!isset($_GET['mID']) || (isset($_GET['mID']) && ($_GET['mID'] == $suppliers->fields['suppliers_id']))) && !isset($mInfo) && (substr($action, 0, 3) != 'new')) {
      $supplier_products = $gBitDb->Execute("select count(*) as `products_count`
                                             from " . TABLE_PRODUCTS . "
                                             where `suppliers_id` = '" . (int)$suppliers->fields['suppliers_id'] . "'");

      $mInfo_array = array_merge($suppliers->fields, $supplier_products->fields);
      $mInfo = new objectInfo($mInfo_array);
    }

    if (isset($mInfo) && is_object($mInfo) && ($suppliers->fields['suppliers_id'] == $mInfo->suppliers_id)) {
      echo '              <tr id="defaultSelected" class="info" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . zen_href_link_admin(FILENAME_SUPPLIERS, 'page=' . $_GET['page'] . '&mID=' . $suppliers->fields['suppliers_id'] . '&action=edit') . '\'">' . "\n";
    } else {
      echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . zen_href_link_admin(FILENAME_SUPPLIERS, 'page=' . $_GET['page'] . '&mID=' . $suppliers->fields['suppliers_id'] . '&action=edit') . '\'">' . "\n";
    }
?>
                <td class="dataTableContent"><?php echo $suppliers->fields['suppliers_name']; ?></td>
                <td class="dataTableContent" align="right">
                  <?php echo '<a href="' . zen_href_link_admin(FILENAME_SUPPLIERS, 'page=' . $_GET['page'] . '&mID=' . $suppliers->fields['suppliers_id'] . '&action=edit') . '">' . zen_image(DIR_WS_IMAGES . 'icon_edit.gif', ICON_EDIT) . '</a>'; ?>
                  <?php echo '<a href="' . zen_href_link_admin(FILENAME_SUPPLIERS, 'page=' . $_GET['page'] . '&mID=' . $suppliers->fields['suppliers_id'] . '&action=delete') . '">' . zen_image(DIR_WS_IMAGES . 'icon_delete.gif', ICON_DELETE) . '</a>'; ?>
                  <?php if (isset($mInfo) && is_object($mInfo) && ($suppliers->fields['suppliers_id'] == $mInfo->suppliers_id)) { echo zen_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . zen_href_link_admin(FILENAME_SUPPLIERS, zen_get_all_get_params(array('mID')) . 'mID=' . $suppliers->fields['suppliers_id']) . '">' . zen_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>
                </td>
              </tr>
<?php
    $suppliers->MoveNext();
  }
?>
              <tr>
                <td colspan="2"><table>
                  <tr>
                    <td class="smallText" valign="top"><?php echo $suppliers_split->display_count($suppliers_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_SUPPLIERS); ?></td>
                    <td class="smallText" align="right"><?php echo $suppliers_split->display_links($suppliers_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?></td>
                  </tr>
                </table></td>
              </tr>
<?php
  if ( empty($action) ) {
?>
              <tr>
                <td align="right" colspan="2" class="smallText"><?php echo '<a href="' . zen_href_link_admin(FILENAME_SUPPLIERS, 'page=' . $_GET['page'] . '&mID=' . ( isset($mInfo) ? $mInfo->suppliers_id : '' ) . '&action=new') . '">' . zen_image_button('button_insert.gif', IMAGE_INSERT) . '</a>'; ?></td>
              </tr>
<?php
  }
?>
            </table></td>
<?php
  $heading = array();
  $contents = array();

  switch ($action) {
    case 'new':
      $heading[] = array('text' => '<b>' . TEXT_HEADING_NEW_MANUFACTURER . '</b>');

      $contents = array('form' => zen_draw_form_admin('suppliers', FILENAME_SUPPLIERS, 'action=insert', 'post', 'enctype="multipart/form-data"'));
      $contents[] = array('text' => TEXT_NEW_INTRO);
      $contents[] = array('text' => '<br>' . TEXT_SUPPLIERS_NAME . '<br>' . zen_draw_input_field('suppliers_name', '', zen_set_field_length(TABLE_SUPPLIERS, 'suppliers_name')));
      $contents[] = array('text' => '<br>' . TEXT_SUPPLIERS_IMAGE . '<br>' . zen_draw_file_field('suppliers_image'));
      $dir = @dir(DIR_FS_CATALOG_IMAGES);
      $dir_info[] = array('id' => '', 'text' => "Main Directory");
      while ($file = $dir->read()) {
        if (is_dir(DIR_FS_CATALOG_IMAGES . $file) && strtoupper($file) != 'CVS' && $file != "." && $file != "..") {
          $dir_info[] = array('id' => $file . '/', 'text' => $file);
        }
      }

      $default_directory = 'suppliers/';

      $contents[] = array('text' => '<BR />' . TEXT_PRODUCTS_IMAGE_DIR . zen_draw_pull_down_menu('img_dir', $dir_info, $default_directory));

      $supplier_inputs_string = '';
      $languages = zen_get_languages();
      for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
        $supplier_inputs_string .= '<br>' . zen_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' . zen_draw_input_field('suppliers_url[' . $languages[$i]['id'] . ']', '', zen_set_field_length(TABLE_SUPPLIERS_INFO, 'suppliers_url') );
      }

      $contents[] = array('text' => '<br>' . TEXT_SUPPLIERS_URL . $supplier_inputs_string);
      $contents[] = array('align' => 'center', 'text' => '<br>' . zen_image_submit('button_save.gif', IMAGE_SAVE) . ' <a href="' . zen_href_link_admin(FILENAME_SUPPLIERS, 'page=' . $_GET['page'] . '&mID=' . $_GET['mID']) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    case 'edit':
      $heading[] = array('text' => '<b>' . TEXT_HEADING_EDIT_MANUFACTURER . '</b>');

      $contents = array('form' => zen_draw_form_admin('suppliers', FILENAME_SUPPLIERS, 'page=' . $_GET['page'] . '&mID=' . $mInfo->suppliers_id . '&action=save', 'post', 'enctype="multipart/form-data"'));
      $contents[] = array('text' => TEXT_EDIT_INTRO);
      $contents[] = array('text' => '<br />' . TEXT_SUPPLIERS_NAME . '<br>' . zen_draw_input_field('suppliers_name', $mInfo->suppliers_name, zen_set_field_length(TABLE_SUPPLIERS, 'suppliers_name')));
      $contents[] = array('text' => '<br />' . TEXT_SUPPLIERS_IMAGE . '<br>' . zen_draw_file_field('suppliers_image') . '<br />' . $mInfo->suppliers_image);
      $dir = @dir(DIR_FS_CATALOG_IMAGES);
      $dir_info[] = array('id' => '', 'text' => "Main Directory");
      while ($file = $dir->read()) {
        if (is_dir(DIR_FS_CATALOG_IMAGES . $file) && strtoupper($file) != 'CVS' && $file != "." && $file != "..") {
          $dir_info[] = array('id' => $file . '/', 'text' => $file);
        }
      }
      $default_directory = substr( $mInfo->suppliers_image, 0,strpos( $mInfo->suppliers_image, '/')+1);
      $contents[] = array('text' => '<BR />' . TEXT_PRODUCTS_IMAGE_DIR . zen_draw_pull_down_menu('img_dir', $dir_info, $default_directory));
      $contents[] = array('text' => '<br />' . zen_info_image($mInfo->suppliers_image, $mInfo->suppliers_name));
      $supplier_inputs_string = '';
      $languages = zen_get_languages();
      for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
        $supplier_inputs_string .= '<br>' . zen_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' . zen_draw_input_field('suppliers_url[' . $languages[$i]['id'] . ']', zen_get_manufacturer_url($mInfo->suppliers_id, $languages[$i]['id']), zen_set_field_length(TABLE_SUPPLIERS_INFO, 'suppliers_url'));
      }

      $contents[] = array('text' => '<br>' . TEXT_SUPPLIERS_URL . $supplier_inputs_string);
      $contents[] = array('align' => 'center', 'text' => '<br>' . zen_image_submit('button_save.gif', IMAGE_SAVE) . ' <a href="' . zen_href_link_admin(FILENAME_SUPPLIERS, 'page=' . $_GET['page'] . '&mID=' . $mInfo->suppliers_id) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    case 'delete':
      $heading[] = array('text' => '<b>' . TEXT_HEADING_DELETE_MANUFACTURER . '</b>');

      $contents = array('form' => zen_draw_form_admin('suppliers', FILENAME_SUPPLIERS, 'page=' . $_GET['page'] . '&mID=' . $mInfo->suppliers_id . '&action=deleteconfirm'));
      $contents[] = array('text' => TEXT_DELETE_INTRO);
      $contents[] = array('text' => '<br><b>' . $mInfo->suppliers_name . '</b>');
      $contents[] = array('text' => '<br>' . zen_draw_checkbox_field('delete_image', '', true) . ' ' . TEXT_DELETE_IMAGE);

      if ($mInfo->products_count > 0) {
        $contents[] = array('text' => '<br>' . zen_draw_checkbox_field('delete_products') . ' ' . TEXT_DELETE_PRODUCTS);
        $contents[] = array('text' => '<br>' . sprintf(TEXT_DELETE_WARNING_PRODUCTS, $mInfo->products_count));
      }

      $contents[] = array('align' => 'center', 'text' => '<br>' . zen_image_submit('button_delete.gif', IMAGE_DELETE) . ' <a href="' . zen_href_link_admin(FILENAME_SUPPLIERS, 'page=' . $_GET['page'] . '&mID=' . $mInfo->suppliers_id) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    default:
      if (isset($mInfo) && is_object($mInfo)) {
        $heading[] = array('text' => '<b>' . $mInfo->suppliers_name . '</b>');

        $contents[] = array('align' => 'center', 'text' => '<a href="' . zen_href_link_admin(FILENAME_SUPPLIERS, 'page=' . $_GET['page'] . '&mID=' . $mInfo->suppliers_id . '&action=edit') . '">' . zen_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . zen_href_link_admin(FILENAME_SUPPLIERS, 'page=' . $_GET['page'] . '&mID=' . $mInfo->suppliers_id . '&action=delete') . '">' . zen_image_button('button_delete.gif', IMAGE_DELETE) . '</a>');
        $contents[] = array('text' => '<br>' . TEXT_DATE_ADDED . ' ' . zen_date_short($mInfo->date_added));
        if (zen_not_null($mInfo->last_modified)) $contents[] = array('text' => TEXT_LAST_MODIFIED . ' ' . zen_date_short($mInfo->last_modified));
        $contents[] = array('text' => '<br>' . zen_info_image($mInfo->suppliers_image, $mInfo->suppliers_name));
        $contents[] = array('text' => '<br>' . TEXT_PRODUCTS . ' ' . $mInfo->products_count);
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
