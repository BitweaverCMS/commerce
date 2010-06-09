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
      case 'save':
        if (isset($_GET['gID'])) $group_id = zen_db_prepare_input($_GET['gID']);
        $group_name = zen_db_prepare_input($_POST['group_name']);
        $group_percentage = zen_db_prepare_input($_POST['group_percentage']);
        if ($group_name) {
          $sql_data_array = array('group_name' => $group_name,
                                  'group_percentage' => $group_percentage);
          if ($action == 'insert') {
            $insert_sql_data = array('date_added' => $gBitDb->NOW() );
            $sql_data_array = array_merge($sql_data_array, $insert_sql_data);
            $gBitDb->associateInsert(TABLE_GROUP_PRICING, $sql_data_array);
            $group_id = zen_db_insert_id( TABLE_GROUP_PRICING, 'group_id' );
          } elseif ($action == 'save') {
            $update_sql_data = array('last_modified' => $gBitDb->NOW() );
            $sql_data_array = array_merge($sql_data_array, $update_sql_data);
            $gBitDb->associateInsert(TABLE_GROUP_PRICING, $sql_data_array, 'update', "group_id = '" . (int)$group_id . "'");
          }
        }
        zen_redirect(zen_href_link_admin(FILENAME_GROUP_PRICING, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . 'gID=' . $group_id));
      break;
      case 'deleteconfirm':
        if (zen_admin_demo()) {
          $_GET['action']= '';
          $messageStack->add_session(ERROR_ADMIN_DEMO, 'caution');
          zen_redirect(zen_href_link_admin(FILENAME_GROUP_PRICING, 'page=' . $_GET['page']));
        }
        $group_id = zen_db_prepare_input($_GET['gID']);
        $gBitDb->Execute("delete from " . TABLE_GROUP_PRICING . " where `group_id` = '" . (int)$group_id . "'");
        $customers_query = $gBitDb->Execute("select customers_id from " . TABLE_CUSTOMERS . " where customers_group_pricing = '" . (int)$group_id . "'");
        while (!$customers_query->EOF) {
          $gBitDb->Execute("update " . TABLE_CUSTOMERS ." set `customers_group_pricing` = '0'");
          $customers_query->MoveNext();
        }
        zen_redirect(zen_href_link_admin(FILENAME_GROUP_PRICING, 'page=' . $_GET['page']));
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
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
            <td class="pageHeading" align="right"><?php echo zen_draw_separator('pixel_trans.gif', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_GROUP_ID; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_GROUP_NAME; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_GROUP_AMOUNT; ?>&nbsp;</td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php
  $groups_query_raw = "select * from " . TABLE_GROUP_PRICING;
  $groups_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS, $groups_query_raw, $groups_query_numrows);
  $groups = $gBitDb->Execute($groups_query_raw);
  while (!$groups->EOF) {
    if ((!isset($_GET['gID']) || (isset($_GET['gID']) && ($_GET['gID'] == $groups->fields['group_id']))) && !isset($gInfo) && (substr($action, 0, 3) != 'new')) {
      $group_customers = $gBitDb->Execute("select count(*) as customer_count from " . TABLE_CUSTOMERS .
                                       " where customers_group_pricing = '" . (int)$groups->fields['group_id'] . "'");
      $gInfo_array = array_merge($groups->fields, $group_customers->fields);
      $gInfo = new objectInfo($gInfo_array);
    }

    if (isset($gInfo) && is_object($gInfo) && ($groups->fields['group_id'] == $gInfo->group_id)) {
      echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . zen_href_link_admin(FILENAME_GROUP_PRICING, 'page=' . $_GET['page'] . '&gID=' . $groups->fields['group_id'] . '&action=edit') . '\'">' . "\n";
    } else {
      echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . zen_href_link_admin(FILENAME_GROUP_PRICING, 'page=' . $_GET['page'] . '&gID=' . $groups->fields['group_id'] . '&action=edit') . '\'">' . "\n";
    }
?>
                <td class="dataTableContent"><?php echo $groups->fields['group_id']; ?></td>
                <td class="dataTableContent"><?php echo $groups->fields['group_name']; ?></td>
                <td class="dataTableContent"><?php echo $groups->fields['group_percentage']; ?></td>
                <td class="dataTableContent" align="right">
                  <?php echo '<a href="' . zen_href_link_admin(FILENAME_GROUP_PRICING, 'page=' . $_GET['page'] . '&gID=' . $groups->fields['group_id'] . '&action=edit') . '">' . zen_image(DIR_WS_IMAGES . 'icon_edit.gif', ICON_EDIT) . '</a>'; ?>
                  <?php echo '<a href="' . zen_href_link_admin(FILENAME_GROUP_PRICING, 'page=' . $_GET['page'] . '&gID=' . $groups->fields['group_id'] . '&action=delete') . '">' . zen_image(DIR_WS_IMAGES . 'icon_delete.gif', ICON_DELETE) . '</a>'; ?>
                  <?php if (isset($gInfo) && is_object($gInfo) && ($groups->fields['group_id'] == $gInfo->group_id)) { echo zen_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . zen_href_link_admin(FILENAME_GROUP_PRICING, zen_get_all_get_params(array('gID')) . 'gID=' . $groups->fields['group_id']) . '">' . zen_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>
                </td>
              </tr>
<?php
    $groups->MoveNext();
  }
?>
              <tr>
                <td colspan="4"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText" valign="top"><?php echo $groups_split->display_count($groups_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_PRICING_GROUPS); ?></td>
                    <td class="smallText" align="right"><?php echo $groups_split->display_links($groups_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?></td>
                  </tr>
                </table></td>
              </tr>
<?php
  if (empty($action)) {
  	if ( !isset($gInfo) ) $gInfo->group_id = 1;
?>
              <tr>
                <td align="right" colspan="4" class="smallText"><?php echo '<a href="' . zen_href_link_admin(FILENAME_GROUP_PRICING, 'page=' . $_GET['page'] . '&gID=' . $gInfo->group_id . '&action=new') . '">' . zen_image_button('button_insert.gif', IMAGE_INSERT) . '</a>'; ?></td>
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
      $heading[] = array('text' => '<b>' . TEXT_HEADING_NEW_PRICING_GROUP . '</b>');

      $contents = array('form' => zen_draw_form_admin('group_pricing', FILENAME_GROUP_PRICING, 'action=insert', 'post'));
      $contents[] = array('text' => TEXT_NEW_INTRO);
      $contents[] = array('text' => '<br>' . TEXT_GROUP_PRICING_NAME . '<br>' . zen_draw_input_field('group_name', '', zen_set_field_length(TABLE_GROUP_PRICING, 'group_name')));
      $contents[] = array('text' => '<br>' . TEXT_GROUP_PRICING_AMOUNT . '<br>' . zen_draw_input_field('group_percentage', ''));
      $contents[] = array('align' => 'center', 'text' => '<br>' . zen_image_submit('button_save.gif', IMAGE_SAVE) . ' <a href="' . zen_href_link_admin(FILENAME_GROUP_PRICING, 'page=' . $_GET['page'] . '&gID=' . $_GET['gID']) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    case 'edit':
      $heading[] = array('text' => '<b>' . TEXT_HEADING_EDIT_PRICING_GROUP . '</b>');

      $contents = array('form' => zen_draw_form_admin('group_pricing', FILENAME_GROUP_PRICING, 'page=' . $_GET['page'] . '&gID=' . $gInfo->group_id . '&action=save', 'post'));
      $contents[] = array('text' => TEXT_EDIT_INTRO);
      $contents[] = array('text' => '<br />' . TEXT_GROUP_PRICING_NAME . '<br>' . zen_draw_input_field('group_name', $gInfo->group_name, zen_set_field_length(TABLE_GROUP_PRICING, 'group_name')));
      $contents[] = array('text' => '<br>' . TEXT_GROUP_PRICING_AMOUNT . '<br>' . zen_draw_input_field('group_percentage', $gInfo->group_percentage));
      $contents[] = array('align' => 'center', 'text' => '<br>' . zen_image_submit('button_save.gif', IMAGE_SAVE) . ' <a href="' . zen_href_link_admin(FILENAME_GROUP_PRICING, 'page=' . $_GET['page'] . '&gID=' . $gInfo->group_id) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    case 'delete':
      $heading[] = array('text' => '<b>' . TEXT_HEADING_DELETE_PRICING_GROUP . '</b>');

      $contents = array('form' => zen_draw_form_admin('group_pricing', FILENAME_GROUP_PRICING, 'page=' . $_GET['page'] . '&gID=' . $gInfo->group_id . '&action=deleteconfirm'));
      $contents[] = array('text' => TEXT_DELETE_INTRO);
      $contents[] = array('text' => '<br><b>' . $gInfo->group_name . '</b>');

      if ($gInfo->customer_count > 0) {
        $contents[] = array('text' => '<br>' . zen_draw_checkbox_field('delete_customers') . ' ' . TEXT_DELETE_CUSTOMERS);
        $contents[] = array('text' => '<br>' . sprintf(TEXT_DELETE_WARNING_CUSTOMERS, $gInfo->customer_count));
      }

      $contents[] = array('align' => 'center', 'text' => '<br>' . zen_image_submit('button_delete.gif', IMAGE_DELETE) . ' <a href="' . zen_href_link_admin(FILENAME_GROUP_PRICING, 'page=' . $_GET['page'] . '&gID=' . $gInfo->group_id) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    default:
      if (isset($gInfo) && is_object($gInfo)) {
        $heading[] = array('text' => '<b>' . $gInfo->group_name . '</b>');

        $contents[] = array('align' => 'center', 'text' => '<a href="' . zen_href_link_admin(FILENAME_GROUP_PRICING, 'page=' . $_GET['page'] . '&gID=' . $gInfo->group_id . '&action=edit') . '">' . zen_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . zen_href_link_admin(FILENAME_GROUP_PRICING, 'page=' . $_GET['page'] . '&gID=' . $gInfo->group_id . '&action=delete') . '">' . zen_image_button('button_delete.gif', IMAGE_DELETE) . '</a>');
        $contents[] = array('text' => '<br>' . TEXT_DATE_ADDED . ' ' . zen_date_short($gInfo->date_added));
        if (zen_not_null($gInfo->last_modified)) $contents[] = array('text' => TEXT_LAST_MODIFIED . ' ' . zen_date_short($gInfo->last_modified));
        $contents[] = array('text' => '<br>' . TEXT_CUSTOMERS . ' ' . $gInfo->customer_count);
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
