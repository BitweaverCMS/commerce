<?php
//
// +----------------------------------------------------------------------+
// |zen-cart Open Source E-commerce                                       |
// +----------------------------------------------------------------------+
// | Copyright (c) 2004 The zen-cart developers                           |
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

  $action = (isset($_GET['action']) ? $_GET['action'] : '');
  $current_category_id = (isset($_GET['current_category_id']) ? $_GET['current_category_id'] : $current_category_id);

  if (zen_not_null($action)) {
    switch ($action) {
      case 'remove_product':
        $gBitDb->Execute("delete from " . TABLE_MEDIA_TO_PRODUCTS . "
                      where media_id = '" . (int)$_GET['mID'] . "'
                      and product_id = '" . (int)$_GET['product_id'] . "'");
       zen_redirect(zen_href_link_admin(FILENAME_MEDIA_MANAGER, 'action=products&current_category_id=' . $current_category_id) . '&mID=' . (int)$_GET['mID']);

      break;
      case 'add_product':
        $product_add_query = $gBitDb->Execute("insert into " . TABLE_MEDIA_TO_PRODUCTS . " (media_id, product_id) values
                                           ('" . (int)$_GET['mID'] . "', '" . (int)$_GET['current_product_id'] . "')");
         zen_redirect(zen_href_link_admin(FILENAME_MEDIA_MANAGER, 'action=products&current_category_id=' . $current_category_id) . '&mID=' . $_GET['mID']);

      break;
      case 'new_cat':
    $current_category_id = (isset($_GET['current_category_id']) ? $_GET['current_category_id'] : $current_category_id);
    $productsId = $new_product_query->fields['products_id'];
    zen_redirect(zen_href_link_admin(FILENAME_MEDIA_MANAGER, 'action=products&current_category_id=' . $current_category_id . '&mID=' . $_GET['mID']));
      break;
      case 'remove_clip':
        $delete_query = "delete from " . TABLE_MEDIA_CLIPS . " where clip_id  = '" . $_GET['clip_id'] . "'";
        $gBitDb->Execute($delete_query);
        zen_redirect(zen_href_link_admin(FILENAME_MEDIA_MANAGER, 'action=edit&page=' . $_GET['page']));
      break;
      case 'insert':
      case 'save':
        if (isset($_POST['add_clip'])) {
          $clip_name = $_FILES['clip_filename'];
          $clip_name = zen_db_prepare_input($clip_name['name']);
          if ($clip_name) {
            $media_type = $_POST['media_type'];
            $ext = $gBitDb->Execute("select type_ext from " . TABLE_MEDIA_TYPES . " where `type_id` = '" . $_POST['media_type'] . "'");
            if (ereg($ext->fields['type_ext'], $clip_name)) {

              if ($media_upload = new upload('clip_filename')) {
                $media_upload->set_destination(DIR_FS_CATALOG_MEDIA . $_POST['media_dir']);
                if ($media_upload->parse() && $media_upload->save()) {
                  $media_upload_filename = $_POST['media_dir'] . $media_upload->filename;
                }
                if ($media_upload->filename != 'none' && $media_upload->filename != '') {

                  $gBitDb->Execute("insert into " . TABLE_MEDIA_CLIPS . "
                                (`media_id`, `clip_type`, `clip_filename`, `date_added`) values (
                                 '" . $_GET['mID'] . "',
                                 '" . $media_type . "',
                                 '" . $media_upload_filename . "', ".$gBitDb->qtNOW().")");
                }
              }

            }
          }
        }
        if (isset($_GET['mID'])) $media_id = zen_db_prepare_input($_GET['mID']);
        $media_name = zen_db_prepare_input($_POST['media_name']);

        $sql_data_array = array('media_name' => $media_name);

        if ($media_name == '') {
          $messageStack->add_session(ERROR_UNKNOWN_DATA, 'caution');
        } else {
          if ($action == 'insert') {
            $insert_sql_data = array('date_added' => $gBitDb->NOW());

            $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

            $gBitDb->associateInsert(TABLE_MEDIA_MANAGER, $sql_data_array);
            $media_id = zen_db_insert_id( TABLE_MEDIA_MANAGER, 'media_id' );
          } elseif ($action == 'save') {
            $update_sql_data = array('last_modified' => $gBitDb->NOW());

            $sql_data_array = array_merge($sql_data_array, $update_sql_data);

            $gBitDb->associateInsert(TABLE_MEDIA_MANAGER, $sql_data_array, 'update', "media_id = '" . (int)$media_id . "'");
          }
        }

        zen_redirect(zen_href_link_admin(FILENAME_MEDIA_MANAGER, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . ($media_id != '' ? 'mID=' . $media_id : '')));
        break;
      case 'deleteconfirm':
        // demo active test
        if (zen_admin_demo()) {
          $_GET['action']= '';
          $messageStack->add_session(ERROR_ADMIN_DEMO, 'caution');
          zen_redirect(zen_href_link_admin(FILENAME_MEDIA_MANAGER, 'page=' . $_GET['page']));
        }
        $media_id = zen_db_prepare_input($_GET['mID']);


        $gBitDb->Execute("delete from " . TABLE_MEDIA_MANAGER . "
                      where media_id = '" . (int)$media_id . "'");

        if (isset($_POST['delete_products']) && ($_POST['delete_products'] == 'on')) {

//          while (!$products->EOF) {
//            zen_remove_product($products->fields['products_id']);
//            $products->MoveNext();
//          }
        } else {
//          $gBitDb->Execute("update " . TABLE_PRODUCTS . "
//                        set manufacturers_id = ''
//                        where manufacturers_id = '" . (int)$manufacturers_id . "'");
        }

        zen_redirect(zen_href_link_admin(FILENAME_MEDIA_MANAGER, 'page=' . $_GET['page']));
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
<link rel="stylesheet" type="text/css" href="includes/cssjsmenuhover.css" media="all" id="hoverJS"/>
<script type="text/javascript" src="includes/menu.js"></script>
<script type="text/javascript" src="includes/general.js"></script>
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
<!-- header //-->
<?php require(DIR_FS_ADMIN_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->
<table border="0" width="100%" cellspacing="2" cellpadding="2">
  <tr>
<!-- body_text //-->
    <td width="100%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td width="100%"><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE_MEDIA_MANAGER; ?></td>
            <td class="pageHeading" align="right"><?php echo zen_draw_separator('pixel_trans.gif', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_MEDIA; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php
  $media_query_raw = "select * from " . TABLE_MEDIA_MANAGER . " order by `media_name`";
  $media_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS, $media_query_raw, $media_query_numrows);
  $media = $gBitDb->Execute($media_query_raw);
  while (!$media->EOF) {
    if ((!isset($_GET['mID']) || (isset($_GET['mID']) && ($_GET['mID'] == $media->fields['media_id']))) && !isset($mInfo) && (substr($action, 0, 3) != 'new')) {

      $mInfo = new objectInfo($media->fields);
    }

    if (isset($mInfo) && is_object($mInfo) && ($media->fields['media_id'] == $mInfo->media_id)) {
      echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . zen_href_link_admin(FILENAME_MEDIA_MANAGER, 'page=' . $_GET['page'] . '&mID=' . $media->fields['media_id']) . '\'">' . "\n";
    } else {
      echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . zen_href_link_admin(FILENAME_MEDIA_MANAGER, 'page=' . $_GET['page'] . '&mID=' . $media->fields['media_id']) . '\'">' . "\n";
    }
?>
                <td class="dataTableContent"><?php echo $media->fields['media_name']; ?></td>
                <td class="dataTableContent" align="right">
                  <?php echo '<a href="' . zen_href_link_admin(FILENAME_MEDIA_MANAGER, 'page=' . $_GET['page'] . '&mID=' . $media->fields['media_id'] . '&action=edit') . '">' . zen_image(DIR_WS_IMAGES . 'icon_edit.gif', ICON_EDIT) . '</a>'; ?>
                  <?php echo '<a href="' . zen_href_link_admin(FILENAME_MEDIA_MANAGER, 'page=' . $_GET['page'] . '&mID=' . $media->fields['media_id'] . '&action=delete') . '">' . zen_image(DIR_WS_IMAGES . 'icon_delete.gif', ICON_DELETE) . '</a>'; ?>
                  <?php if (isset($mInfo) && is_object($mInfo) && ($media->fields['media_id'] == $mInfo->media_id)) { echo zen_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . zen_href_link_admin(FILENAME_MEDIA_MANAGER, zen_get_all_get_params(array('mID')) . 'mID=' . $media->fields['media_id']) . '">' . zen_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>
                </td>
              </tr>
<?php
    $media->MoveNext();
  }
?>
              <tr>
                <td colspan="2"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText" valign="top"><?php echo $media_split->display_count($media_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_MEDIA); ?></td>
                    <td class="smallText" align="right"><?php echo $media_split->display_links($media_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?></td>
                  </tr>
                </table></td>
              </tr>
<?php
  if (empty($action)) {
?>
              <tr>
                <td align="right" colspan="2" class="smallText"><?php echo '<a href="' . zen_href_link_admin(FILENAME_MEDIA_MANAGER, 'page=' . $_GET['page'] . '&mID=' . $mInfo->media_id . '&action=new') . '">' . zen_image_button('button_insert.gif', IMAGE_INSERT) . '</a>'; ?></td>
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
      $heading[] = array('text' => '<b>' . TEXT_HEADING_NEW_MEDIA_COLLECTION . '</b>');

      $contents = array('form' => zen_draw_form_admin('collections', FILENAME_MEDIA_MANAGER, 'action=insert', 'post', 'enctype="multipart/form-data"'));
      $contents[] = array('text' => TEXT_NEW_INTRO);
      $contents[] = array('text' => '<br>' . TEXT_MEDIA_COLLECTION_NAME . '<br>' . zen_draw_input_field('media_name', '', zen_set_field_length(TABLE_MEDIA_MANAGER, 'media_name')));

      $contents[] = array('align' => 'center', 'text' => '<br>' . zen_image_submit('button_save.gif', IMAGE_SAVE) . ' <a href="' . zen_href_link_admin(FILENAME_MEDIA_MANAGER, 'page=' . $_GET['page'] . '&mID=' . $_GET['mID']) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    case 'edit':
      $heading[] = array('text' => '<b>' . TEXT_HEADING_EDIT_MEDIA_COLLECTION . '</b>');

      $contents = array('form' => zen_draw_form_admin('collections', FILENAME_MEDIA_MANAGER, 'page=' . $_GET['page'] . '&mID=' . $mInfo->media_id . '&action=save', 'post', 'enctype="multipart/form-data"'));
      $contents[] = array('text' => TEXT_EDIT_INTRO);
      $contents[] = array('text' => '<br />' . TEXT_MEDIA_COLLECTION_NAME . '<br>' . zen_draw_input_field('media_name', $mInfo->media_name, zen_set_field_length(TABLE_MEDIA_MANAGER, 'media_name')));
      $contents[] = array('align' => 'center', 'text' => '<br />' . zen_image_submit('button_save.gif', IMAGE_SAVE) . ' <a href="' . zen_href_link_admin(FILENAME_MEDIA_MANAGER, 'page=' . $_GET['page'] . '&mID=' . $mInfo->media_id) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');

      $contents[] = array('text' => zen_draw_separator('pixel_black.gif'));
      $contents[] = array('text' => TEXT_MEDIA_EDIT_INSTRUCTIONS);
      $contents[] = array('text' => zen_draw_separator('pixel_black.gif'));

      $dir = @dir(DIR_FS_CATALOG_MEDIA);
      $dir_info[] = array('id' => '', 'text' => "Main Directory");
      while ($file = $dir->read()) {
        if (is_dir(DIR_FS_CATALOG_MEDIA . $file) && strtoupper($file) != 'CVS' && $file != "." && $file != "..") {
          $dir_info[] = array('id' => $file . '/', 'text' => $file);
        }
      }
      $contents[] = array('text' => '<br />' . TEXT_ADD_MEDIA_CLIP . zen_draw_file_field('clip_filename'));
      $contents[] = array('text' => TEXT_MEDIA_CLIP_DIR . ' ' . zen_draw_pull_down_menu('media_dir', $dir_info));
      $media_type_query = "select `type_id`, `type_name`, type_ext from " . TABLE_MEDIA_TYPES;
      $media_types = $gBitDb->Execute($media_type_query);
      while (!$media_types->EOF) {
        $media_types_array[] = array('id' => $media_types->fields['type_id'], 'text' => $media_types->fields['type_name'] . ' (' . $media_types->fields['type_ext'] . ')');
        $media_types->MoveNext();
      }
      $contents[] = array('text' => TEXT_MEDIA_CLIP_TYPE . ' ' . zen_draw_pull_down_menu('media_type', $media_types_array));

      $contents[] = array('text' => '<input type="submit" name="add_clip" value="Add">');
      $clip_query = "select * from " . TABLE_MEDIA_CLIPS . " where media_id = '" . $mInfo->media_id . "'";
      $clips = $gBitDb->Execute($clip_query);
      while (!$clips->EOF) {
        $contents[] = array('text' => '<a href="' . zen_href_link_admin(FILENAME_MEDIA_MANAGER, 'action=remove_clip&mID='.$mInfo->media_id.'&clip_id='.$clips->fields['clip_id']) . '">' . zen_image_button('button_delete.gif', IMAGE_DELETE) . '</a>&nbsp;' . $clips->fields['clip_filename'] . '<br />');
        $clips->MoveNext();
      }
      break;
    case 'delete':
      $heading[] = array('text' => '<b>' . TEXT_HEADING_DELETE_MEDIA_COLLECTION . '</b>');

      $contents = array('form' => zen_draw_form_admin('collections', FILENAME_MEDIA_MANAGER, 'page=' . $_GET['page'] . '&mID=' . $mInfo->media_id . '&action=deleteconfirm'));
      $contents[] = array('text' => TEXT_DELETE_INTRO);
      $contents[] = array('text' => '<br><b>' . $mInfo->media_name . '</b>');

      if ($mInfo->products_count > 0) {
        $contents[] = array('text' => '<br>' . zen_draw_checkbox_field('delete_products') . ' ' . TEXT_DELETE_PRODUCTS);
        $contents[] = array('text' => '<br>' . sprintf(TEXT_DELETE_WARNING_PRODUCTS, $mInfo->products_count));
      }

      $contents[] = array('align' => 'center', 'text' => '<br />' . zen_image_submit('button_delete.gif', IMAGE_DELETE) . ' <a href="' . zen_href_link_admin(FILENAME_MEDIA_MANAGER, 'page=' . $_GET['page'] . '&mID=' . $mInfo->media_id) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    case 'products':
      $new_product_query = $gBitDb->Execute("select ptc.*, pd.`products_name` from " . TABLE_PRODUCTS_TO_CATEGORIES . " ptc  left join " . TABLE_PRODUCTS_DESCRIPTION . " pd on ptc.`products_id` = pd.`products_id` and pd.`language_id` = '" . (int)$_SESSION['languages_id'] . "' where ptc.`categories_id`='" . $current_category_id . "' order by pd.`products_name`");
      $heading[] = array('text' => '<b>' . TEXT_HEADING_ASSIGN_MEDIA_COLLECTION . '</b>');
      $contents[] = array('text' => TEXT_PRODUCTS_INTRO . '<br /><br />');
      $contents[] = array('text' => zen_draw_form_admin('new_category', FILENAME_MEDIA_MANAGER, '', 'get') . '&nbsp;&nbsp;' .
                           zen_draw_pull_down_menu('current_category_id', zen_get_category_tree('', '', '0'), '', 'onChange="this.form.submit();"') . zen_draw_hidden_field('products_id', $_GET['products_id']) . zen_draw_hidden_field('action', 'new_cat') . zen_draw_hidden_field('mID', $mInfo->media_id) . '&nbsp;&nbsp;</form>');
      $product_array = $zc_products->get_products_in_category($current_category_id);
      if ($product_array) {
        $contents[] = array('text' => zen_draw_form_admin('new_product', FILENAME_MEDIA_MANAGER, '', 'get') . '&nbsp;&nbsp;' .
                           zen_draw_pull_down_menu('current_product_id', $product_array) . '&nbsp;' . '<input type="submit" name="add_product" value="Add">' .
                           zen_draw_hidden_field('current_category_id', $current_category_id) .
                           zen_draw_hidden_field('action', 'add_product') .
                           zen_draw_hidden_field('mID', $mInfo->media_id) . '&nbsp;&nbsp;</form>');
      } else {
        $contents[] = array('text' => '&nbsp;&nbsp;' . TEXT_NO_PRODUCTS);
      }
      $products_linked_query = "select * from " . TABLE_MEDIA_TO_PRODUCTS . "
                                where media_id = '" . $mInfo->media_id . "'";
      $products_linked = $gBitDb->Execute($products_linked_query);
      while (!$products_linked->EOF) {
        $contents[] = array('text' => '<a href="' . zen_href_link_admin(FILENAME_MEDIA_MANAGER, 'action=remove_product&mID='.$mInfo->media_id.'&product_id='. $products_linked->fields['product_id']) . '">' . zen_image_button('button_delete.gif', IMAGE_DELETE) . '</a>&nbsp;' . $zc_products->products_name($products_linked->fields['product_id']) . '<br />');
        $products_linked->MoveNext();
      }
      $contents[] = array('align' => 'center', 'text' =>  '<br /><a href="' . zen_href_link_admin(FILENAME_MEDIA_MANAGER, 'page=' . $_GET['page'] . '&mID=' . $mInfo->media_id) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    default:
      if (isset($mInfo) && is_object($mInfo)) {
        $heading[] = array('text' => '<b>' . $mInfo->media_name . '</b>');

        $contents[] = array('align' => 'center', 'text' => '<a href="' . zen_href_link_admin(FILENAME_MEDIA_MANAGER, 'page=' . $_GET['page'] . '&mID=' . $mInfo->media_id . '&action=edit') . '">' . zen_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . zen_href_link_admin(FILENAME_MEDIA_MANAGER, 'page=' . $_GET['page'] . '&mID=' . $mInfo->media_id . '&action=delete') . '">' . zen_image_button('button_delete.gif', IMAGE_DELETE) . '</a> ' . '<a href="' . zen_href_link_admin(FILENAME_MEDIA_MANAGER, 'page=' . $_GET['page'] . '&mID=' . $mInfo->media_id . '&action=products') . '">' . zen_image_button('button_assign_to_product.gif', IMAGE_PRODUCTS) . '</a>');
        $contents[] = array('text' => '<br>' . TEXT_DATE_ADDED . ' ' . zen_date_short($mInfo->date_added));
        if (zen_not_null($mInfo->last_modified)) $contents[] = array('text' => TEXT_LAST_MODIFIED . ' ' . zen_date_short($mInfo->last_modified));
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
