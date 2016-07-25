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

  $action = (isset($_GET['action']) ? $_GET['action'] : '');

  if (zen_not_null($action)) {
    switch ($action) {
      case 'insert':
        $tax_class_title = zen_db_prepare_input($_POST['tax_class_title']);
        $tax_class_description = zen_db_prepare_input($_POST['tax_class_description']);

        $gBitDb->Execute("INSERT INTO " . TABLE_TAX_CLASS . "
                    (`tax_class_title`, `tax_class_description`, `date_added`)
                    VALUES ('" . zen_db_input($tax_class_title) . "',
                            '" . zen_db_input($tax_class_description) . "',
                            " . $gBitDb->mDb->sysTimeStamp . ")");

        zen_redirect(zen_href_link_admin(FILENAME_TAX_CLASSES));
        break;
      case 'save':
        $tax_class_id = zen_db_prepare_input($_GET['tID']);
        $tax_class_title = zen_db_prepare_input($_POST['tax_class_title']);
        $tax_class_description = zen_db_prepare_input($_POST['tax_class_description']);

        $gBitDb->Execute("UPDATE " . TABLE_TAX_CLASS . "
                      SET `tax_class_id` = '" . (int)$tax_class_id . "',
                          `tax_class_title` = '" . zen_db_input($tax_class_title) . "',
                          `tax_class_description` = '" . zen_db_input($tax_class_description) . "',
                          `last_modified` = " . $gBitDb->mDb->sysTimeStamp . "
                      WHERE `tax_class_id` = '" . (int)$tax_class_id . "'");

        zen_redirect(zen_href_link_admin(FILENAME_TAX_CLASSES, 'page=' . $_GET['page'] . '&tID=' . $tax_class_id));
        break;
      case 'deleteconfirm':
        // demo active test
        if (zen_admin_demo()) {
          $_GET['action']= '';
          $messageStack->add_session(ERROR_ADMIN_DEMO, 'caution');
          zen_redirect(zen_href_link_admin(FILENAME_TAX_CLASSES, 'page=' . $_GET['page']));
        }
        $tax_class_id = zen_db_prepare_input($_GET['tID']);

        $gBitDb->Execute("DELETE FROM " . TABLE_TAX_CLASS . "
                      WHERE `tax_class_id` = '" . (int)$tax_class_id . "'");

        zen_redirect(zen_href_link_admin(FILENAME_TAX_CLASSES, 'page=' . $_GET['page']));
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
<table class="width100p"><tr><td><table class="width100p">
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table>
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_TAX_CLASSES; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php
  $classes_query_raw = "select `tax_class_id`, `tax_class_title`, `tax_class_description`, `last_modified`, `date_added` from " . TABLE_TAX_CLASS . " order by `tax_class_title`";
  $classes_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS, $classes_query_raw, $classes_query_numrows);
  $classes = $gBitDb->Execute($classes_query_raw);
  while (!$classes->EOF) {
    if ((!isset($_GET['tID']) || (isset($_GET['tID']) && ($_GET['tID'] == $classes->fields['tax_class_id']))) && !isset($tcInfo) && (substr($action, 0, 3) != 'new')) {
      $tcInfo = new objectInfo($classes->fields);
    }

    if (isset($tcInfo) && is_object($tcInfo) && ($classes->fields['tax_class_id'] == $tcInfo->tax_class_id)) {
      echo '              <tr id="defaultSelected" class="info" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . zen_href_link_admin(FILENAME_TAX_CLASSES, 'page=' . $_GET['page'] . '&tID=' . $tcInfo->tax_class_id . '&action=edit') . '\'">' . "\n";
    } else {
      echo'              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . zen_href_link_admin(FILENAME_TAX_CLASSES, 'page=' . $_GET['page'] . '&tID=' . $classes->fields['tax_class_id']) . '\'">' . "\n";
    }
?>
                <td class="dataTableContent"><?php echo $classes->fields['tax_class_title']; ?></td>
                <td class="dataTableContent" align="right"><?php if (isset($tcInfo) && is_object($tcInfo) && ($classes->fields['tax_class_id'] == $tcInfo->tax_class_id)) { echo zen_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . zen_href_link_admin(FILENAME_TAX_CLASSES, 'page=' . $_GET['page'] . '&tID=' . $classes->fields['tax_class_id']) . '">' . zen_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
    $classes->MoveNext();
  }
?>
              <tr>
                <td colspan="2"><table>
                  <tr>
                    <td class="smallText" valign="top"><?php echo $classes_split->display_count($classes_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_TAX_CLASSES); ?></td>
                    <td class="smallText" align="right"><?php echo $classes_split->display_links($classes_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?></td>
                  </tr>
<?php
  if (empty($action)) {
?>
                  <tr>
                    <td colspan="2" align="right"><?php echo '<a href="' . zen_href_link_admin(FILENAME_TAX_CLASSES, 'page=' . $_GET['page'] . '&action=new') . '">' . zen_image_button('button_new_tax_class.gif', IMAGE_NEW_TAX_CLASS) . '</a>'; ?></td>
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
      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_NEW_TAX_CLASS . '</b>');

      $contents = array('form' => zen_draw_form_admin('classes', FILENAME_TAX_CLASSES, 'page=' . $_GET['page'] . '&action=insert'));
      $contents[] = array('text' => TEXT_INFO_INSERT_INTRO);
      $contents[] = array('text' => '<br>' . TEXT_INFO_CLASS_TITLE . '<br>' . zen_draw_input_field('tax_class_title', '', zen_set_field_length(TABLE_TAX_CLASS, 'tax_class_title')));
      $contents[] = array('text' => '<br>' . TEXT_INFO_CLASS_DESCRIPTION . '<br>' . zen_draw_input_field('tax_class_description', '', zen_set_field_length(TABLE_TAX_CLASS, 'tax_class_description')));
      $contents[] = array('align' => 'center', 'text' => '<br>' . zen_image_submit('button_insert.gif', IMAGE_INSERT) . '&nbsp;<a href="' . zen_href_link_admin(FILENAME_TAX_CLASSES, 'page=' . $_GET['page']) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    case 'edit':
      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_EDIT_TAX_CLASS . '</b>');

      $contents = array('form' => zen_draw_form_admin('classes', FILENAME_TAX_CLASSES, 'page=' . $_GET['page'] . '&tID=' . $tcInfo->tax_class_id . '&action=save'));
      $contents[] = array('text' => TEXT_INFO_EDIT_INTRO);
      $contents[] = array('text' => '<br>' . TEXT_INFO_CLASS_TITLE . '<br>' . zen_draw_input_field('tax_class_title', $tcInfo->tax_class_title, zen_set_field_length(TABLE_TAX_CLASS, 'tax_class_title')));
      $contents[] = array('text' => '<br>' . TEXT_INFO_CLASS_DESCRIPTION . '<br>' . zen_draw_input_field('tax_class_description', $tcInfo->tax_class_description, zen_set_field_length(TABLE_TAX_CLASS, 'tax_class_description')));
      $contents[] = array('align' => 'center', 'text' => '<br>' . zen_image_submit('button_update.gif', IMAGE_UPDATE) . '&nbsp;<a href="' . zen_href_link_admin(FILENAME_TAX_CLASSES, 'page=' . $_GET['page'] . '&tID=' . $tcInfo->tax_class_id) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    case 'delete':
      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_DELETE_TAX_CLASS . '</b>');

      $contents = array('form' => zen_draw_form_admin('classes', FILENAME_TAX_CLASSES, 'page=' . $_GET['page'] . '&tID=' . $tcInfo->tax_class_id . '&action=deleteconfirm'));
      $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
      $contents[] = array('text' => '<br><b>' . $tcInfo->tax_class_title . '</b>');
      $contents[] = array('align' => 'center', 'text' => '<br>' . zen_image_submit('button_delete.gif', IMAGE_DELETE) . '&nbsp;<a href="' . zen_href_link_admin(FILENAME_TAX_CLASSES, 'page=' . $_GET['page'] . '&tID=' . $tcInfo->tax_class_id) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    default:
      if (isset($tcInfo) && is_object($tcInfo)) {
        $heading[] = array('text' => '<b>' . $tcInfo->tax_class_title . '</b>');

        $contents[] = array('align' => 'center', 'text' => '<a href="' . zen_href_link_admin(FILENAME_TAX_CLASSES, 'page=' . $_GET['page'] . '&tID=' . $tcInfo->tax_class_id . '&action=edit') . '">' . zen_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . zen_href_link_admin(FILENAME_TAX_CLASSES, 'page=' . $_GET['page'] . '&tID=' . $tcInfo->tax_class_id . '&action=delete') . '">' . zen_image_button('button_delete.gif', IMAGE_DELETE) . '</a>');
        $contents[] = array('text' => '<br>' . TEXT_INFO_DATE_ADDED . ' ' . zen_date_short($tcInfo->date_added));
        $contents[] = array('text' => '' . TEXT_INFO_LAST_MODIFIED . ' ' . zen_date_short($tcInfo->last_modified));
        $contents[] = array('text' => '<br>' . TEXT_INFO_CLASS_DESCRIPTION . '<br>' . $tcInfo->tax_class_description);
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
