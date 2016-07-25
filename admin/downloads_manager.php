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

  
  $currencies = new currencies();

  $languages = zen_get_languages();

  $action = (isset($_GET['action']) ? $_GET['action'] : '');

  if (zen_not_null($action)) {
    switch ($action) {
      case 'insert':
      case 'save':
        $gBitDb->Execute("update " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " set products_attributes_filename='" . $_POST['products_attributes_filename'] . "', products_attributes_maxdays='" . $_POST['products_attributes_maxdays'] . "', products_attributes_maxcount='" . $_POST['products_attributes_maxcount'] . "' where products_attributes_id='" . $_GET['padID'] . "'");
        zen_redirect(zen_href_link_admin(FILENAME_DOWNLOADS_MANAGER, 'padID=' . $_GET['padID'] . '&page=' . $_GET['page']));
        break;
    }
  }

?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<script type="text/javascript"><!--
function go_option() {
  if (document.option_order_by.selected.options[document.option_order_by.selected.selectedIndex].value != "none") {
    location = "<?php echo zen_href_link_admin(FILENAME_ATTRIBUTES_CONTROLLER, 'option_page=' . ($_GET['option_page'] ? $_GET['option_page'] : 1)); ?>&option_order_by="+document.option_order_by.selected.options[document.option_order_by.selected.selectedIndex].value;
  }
}
//--></script>
</head>
<!-- <body> -->
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF">
<!-- header //-->
<?php require(DIR_FS_ADMIN_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<?php echo zen_draw_form_admin('search', FILENAME_DOWNLOADS_MANAGER, '', 'get'); ?>
<?php
// show reset search
  if (isset($_GET['search']) && zen_not_null($_GET['search'])) {
    echo '<a href="' . zen_href_link_admin(FILENAME_DOWNLOADS_MANAGER) . '">' . zen_image_button('button_reset.gif', IMAGE_RESET) . '</a>&nbsp;&nbsp;';
  }
  echo HEADING_TITLE_SEARCH_DETAIL . ' ' . zen_draw_input_field('search');
  if (isset($_GET['search']) && zen_not_null($_GET['search'])) {
    $keywords = zen_db_input(zen_db_prepare_input($_GET['search']));
    echo '<br/ >' . TEXT_INFO_SEARCH_DETAIL_FILTER . $keywords;
  }
?>
          </form>

<div><?php echo zen_image(DIR_WS_IMAGES . 'icon_status_red.gif') . TEXT_INFO_FILENAME_MISSING; ?> &nbsp;&nbsp;&nbsp;<?php echo zen_image(DIR_WS_IMAGES . 'icon_status_green.gif') . TEXT_INFO_FILENAME_GOOD; ?></div>
<table>
<!-- downloads by product_name//-->
      <tr>
        <td width="100%"><table width="100%" border="0" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table>

              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_ATTRIBUTES_ID; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_PRODUCTS_ID; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_PRODUCT; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_MODEL; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_OPT_NAME; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_OPT_VALUE; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_TEXT_FILENAME; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_TEXT_MAX_DAYS; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_TEXT_MAX_COUNT; ?></td>
                <td class="dataTableHeadingContent">&nbsp;</td>
              </tr>

<?php
// create search filter
  $search = '';
  if (isset($_GET['search']) && zen_not_null($_GET['search'])) {
    $keywords = zen_db_input(zen_db_prepare_input($_GET['search']));
    $search = " and pd.`products_name` like '%" . $keywords . "%' or pad.`products_attributes_filename` like '%" . $keywords . "%' or pd.`products_description` like '%" . $keywords . "%' or p.`products_model` like '%" . $keywords . "%'";
  }

// order of display
  $order_by = " order by pd.`products_name` ";

// create split page control
  $products_downloads_query_raw = ("select pad.*, pa.*, pd.*, p.* from " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " pad left join " . TABLE_PRODUCTS_ATTRIBUTES . " pa on pad.`products_attributes_id` = pa.`products_attributes_id` left join " . TABLE_PRODUCTS_OPTIONS_MAP . " pom ON pa.`products_options_values_id`=pom.`products_options_values_id` left join " . TABLE_PRODUCTS_DESCRIPTION . " pd on pom.`products_id` = pd.`products_id` and pd.`language_id` ='" . (int)$_SESSION['languages_id'] . "' left join " . TABLE_PRODUCTS . " p on p.`products_id`= pom.`products_id` " . " where pa.`products_attributes_id` = pad.`products_attributes_id`" . $search . $order_by);
  $products_downloads_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS_DOWNLOADS_MANAGER, $products_downloads_query_raw, $products_downloads_query_numrows);
  $products_downloads_query = $gBitDb->Execute($products_downloads_query_raw);

  while (!$products_downloads_query->EOF) {

    if ((!isset($_GET['padID']) || (isset($_GET['padID']) && ($_GET['padID'] == $products_downloads_query->fields['products_attributes_id']))) && !isset($padInfo)) {
      $padInfo_array = array_merge($products_downloads_query->fields);
      $padInfo = new objectInfo($padInfo_array);
    }

// Could go into /admin/includes/configure.php
  define('DIR_FS_DOWNLOAD', DIR_FS_CATALOG . 'download/');
  $filename_is_missing='';
  if ( !file_exists(DIR_FS_DOWNLOAD . $products_downloads_query->fields['products_attributes_filename']) ) {
    $filename_is_missing = zen_image(DIR_WS_IMAGES . 'icon_status_red.gif');
  } else {
    $filename_is_missing = zen_image(DIR_WS_IMAGES . 'icon_status_green.gif');
  }
?>
<?php
      if (isset($padInfo) && is_object($padInfo) && ($products_downloads_query->fields['products_attributes_id'] == $padInfo->products_attributes_id)) {
        echo '              <tr id="defaultSelected" class="info" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . zen_href_link_admin(FILENAME_DOWNLOADS_MANAGER, zen_get_all_get_params(array('padID', 'action')) . 'padID=' . $padInfo->products_attributes_id . '&action=edit' . '&page=' . $_GET['page']) . '\'">' . "\n";
      } else {
        echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . zen_href_link_admin(FILENAME_DOWNLOADS_MANAGER, zen_get_all_get_params(array('padID')) . 'padID=' . $products_downloads_query->fields['products_attributes_id'] . '&page=' . $_GET['page']) . '\'">' . "\n";
      }
?>

                <td class="smallText"><?php echo $products_downloads_query->fields['products_attributes_id']; ?></td>
                <td class="smallText"><?php echo $products_downloads_query->fields['products_id']; ?></td>
                <td class="smallText"><?php echo $products_downloads_query->fields['products_name']; ?></td>
                <td class="smallText"><?php echo $products_downloads_query->fields['products_model']; ?></td>
                <td class="smallText"><?php echo zen_options_name($products_downloads_query->fields['options_id']); ?></td>
                <td class="smallText"><?php echo zen_values_name($products_downloads_query->fields['options_values_id']); ?></td>
                <td class="smallText"><?php echo $filename_is_missing . '&nbsp;' . $products_downloads_query->fields['products_attributes_filename']; ?></td>
                <td class="smallText"><?php echo $products_downloads_query->fields['products_attributes_maxdays']; ?></td>
                <td class="smallText"><?php echo $products_downloads_query->fields['products_attributes_maxcount']; ?></td>
                <td class="dataTableContent" align="right"><?php if (isset($padInfo) && is_object($padInfo) && ($products_downloads_query->fields['products_attributes_id'] == $padInfo->products_attributes_id)) { echo zen_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . zen_href_link_admin(FILENAME_DOWNLOADS_MANAGER, zen_get_all_get_params(array('padID')) . 'padID=' . $products_downloads_query->fields['products_attributes_id'] . '&page=' . $_GET['page']) . '">' . zen_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
  $products_downloads_query->MoveNext();
  }
?>
<?php
// bof: split page control and search filter
?>
              <tr>
                <td colspan="10"><table>
                  <tr>
                    <td class="smallText" valign="top"><?php echo $products_downloads_split->display_count($products_downloads_query_numrows, MAX_DISPLAY_SEARCH_RESULTS_DOWNLOADS_MANAGER, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_PRODUCTS_DOWNLOADS_MANAGER); ?></td>
<!--
                    <td class="smallText" align="right"><?php echo $products_downloads_split->display_links($products_downloads_query_numrows, MAX_DISPLAY_SEARCH_RESULTS_DOWNLOADS_MANAGER, MAX_DISPLAY_PAGE_LINKS, $_GET['page'], zen_get_all_get_params(array('page', 'info', 'x', 'y', 'cID'))); ?></td>
-->
                    <td class="smallText" align="right"><?php echo $products_downloads_split->display_links($products_downloads_query_numrows, MAX_DISPLAY_SEARCH_RESULTS_DOWNLOADS_MANAGER, MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?></td>


                  </tr>
<?php
  if (isset($_GET['search']) && zen_not_null($_GET['search'])) {
?>
                  <tr>
                    <td align="right" colspan="2"><?php echo '<a href="' . zen_href_link_admin(FILENAME_DOWNLOADS_MANAGER) . '">' . zen_image_button('button_reset.gif', IMAGE_RESET) . '</a>'; ?></td>
                  </tr>
<?php
  }
?>
                </table></td>
              </tr>
<?php
// eof: split page control
?>
            </table></td>

<?php
  $heading = array();
  $contents = array();

  switch ($action) {
/*
    case 'confirm':
      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_DELETE_PRODUCTS_DOWNLOAD . '</b>');

      $contents = array('form' => zen_draw_form_admin('products_downloads_delete', FILENAME_DOWNLOADS_MANAGER, zen_get_all_get_params(array('padID', 'action')) . 'padID=' . $padInfo->products_attributes_id . '&action=deleteconfirm' . '&page=' . $_GET['page']));
      $contents[] = array('text' => TEXT_DELETE_INTRO . '<br /><br /><b>' . $padInfo->products_name . ' - ' . $padInfo->products_attributes_filename . '</b>');
      $contents[] = array('align' => 'center', 'text' => '<br />' . zen_image_submit('button_delete.gif', IMAGE_DELETE) . ' <a href="' . zen_href_link_admin(FILENAME_DOWNLOADS_MANAGER, zen_get_all_get_params(array('padID', 'action')) . 'padID=' . $padInfo->products_attributes_id) . '&page=' . $_GET['page'] . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
*/
    case 'edit':
      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_EDIT_PRODUCTS_DOWNLOAD . '</b>');

      $contents = array('form' => zen_draw_form_admin('products_downloads_edit', FILENAME_DOWNLOADS_MANAGER, zen_get_all_get_params(array('padID', 'action')) . 'padID=' . $padInfo->products_attributes_id . '&action=save' . '&page=' . $_GET['page']));
      $contents[] = array('text' => '<b>' . TEXT_PRODUCTS_NAME . $padInfo->products_name . '<br />' . TEXT_PRODUCTS_MODEL . $padInfo->products_model . '</b>');
      $contents[] = array('text' => '<br />' . TEXT_INFO_EDIT_INTRO);
      $contents[] = array('text' => '<br />' . TEXT_INFO_FILENAME . '<br />' . zen_draw_input_field('products_attributes_filename', $padInfo->products_attributes_filename));
      $contents[] = array('text' => '<br />' . TEXT_INFO_MAX_DAYS . '<br />' . zen_draw_input_field('products_attributes_maxdays', $padInfo->products_attributes_maxdays));
      $contents[] = array('text' => '<br />' . TEXT_INFO_MAX_COUNT . '<br />' . zen_draw_input_field('products_attributes_maxcount', $padInfo->products_attributes_maxcount));
      $contents[] = array('align' => 'center', 'text' => '<br />' . zen_image_submit('button_update.gif', IMAGE_UPDATE) . '&nbsp;<a href="' . zen_href_link_admin(FILENAME_DOWNLOADS_MANAGER, 'padID=' . $padInfo->products_attributes_id) . '&page=' . $_GET['page'] . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    default:
      if (isset($padInfo) && is_object($padInfo)) {
        $heading[] = array('text' => '<b>' . $padInfo->products_attributes_id . ' ' . $padInfo->products_attributes_filename . '</b>');

        $contents[] = array('align' => 'center', 'text' =>
          '<a href="' . zen_href_link_admin(FILENAME_DOWNLOADS_MANAGER, zen_get_all_get_params(array('padID', 'action')) . 'padID=' . $padInfo->products_attributes_id . '&page=' . $_GET['page'].'&action=edit') . '">' . zen_image_button('button_edit.gif', IMAGE_EDIT) . '</a>' .
          '<a href="' . zen_href_link_admin(FILENAME_ATTRIBUTES_CONTROLLER, 'products_id=' . $padInfo->products_id) . '">' . zen_image_button('button_edit_attribs.gif', IMAGE_EDIT_ATTRIBUTES) . '</a>'
          );
        $contents[] = array('text' => '<br />' . TEXT_PRODUCTS_NAME . $padInfo->products_name);
        $contents[] = array('text' => TEXT_PRODUCTS_MODEL . $padInfo->products_model);
        $contents[] = array('text' => TEXT_INFO_FILENAME . $padInfo->products_attributes_filename);
        $contents[] = array('text' => TEXT_INFO_MAX_DAYS . $padInfo->products_attributes_maxdays);
        $contents[] = array('text' => TEXT_INFO_MAX_COUNT . $padInfo->products_attributes_maxcount);

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
        </td></table>
<!-- body_text_eof //-->
<!-- footer //-->
<?php require(DIR_FS_ADMIN_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
</body>
</html>
<?php require(DIR_FS_ADMIN_INCLUDES . 'application_bottom.php'); ?>  
