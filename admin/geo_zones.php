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

  $saction = (isset($_GET['saction']) ? $_GET['saction'] : '');

  if (zen_not_null($saction)) {
    switch ($saction) {
      case 'insert_sub':
        $zID = zen_db_prepare_input($_GET['zID']);
        $zone_country_id = zen_db_prepare_input($_POST['zone_country_id']);
        $zone_id = zen_db_prepare_input($_POST['zone_id']);

        $gBitDb->Execute("insert into " . TABLE_ZONES_TO_GEO_ZONES . "
                    (`zone_country_id`, `zone_id`, `geo_zone_id`, `date_added`)
                    values ('" . (int)$zone_country_id . "',
                            '" . (int)$zone_id . "',
                            '" . (int)$zID . "', 
                            " . $gBitDb->mDb->sysTimeStamp . ")");

        $new_subzone_id = zen_db_insert_id( TABLE_ZONES_TO_GEO_ZONES, 'association_id' );

        zen_redirect(zen_href_link_admin(FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $_GET['zID'] . '&action=list&spage=' . $_GET['spage'] . '&sID=' . $new_subzone_id));
        break;
      case 'save_sub':
        $sID = zen_db_prepare_input($_GET['sID']);
        $zID = zen_db_prepare_input($_GET['zID']);
        $zone_country_id = zen_db_prepare_input($_POST['zone_country_id']);
        $zone_id = zen_db_prepare_input($_POST['zone_id']);

        $gBitDb->Execute("update " . TABLE_ZONES_TO_GEO_ZONES . "
                      set `geo_zone_id` = '" . (int)$zID . "',
                          `zone_country_id` = '" . (int)$zone_country_id . "',
                          `zone_id` = " . (zen_not_null($zone_id) ? "'" . (int)$zone_id . "'" : 'null') . ",
                          `last_modified` = " . $gBitDb->mDb->sysTimeStamp . "
					where `association_id` = '" . (int)$sID . "'");


        zen_redirect(zen_href_link_admin(FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $_GET['zID'] . '&action=list&spage=' . $_GET['spage'] . '&sID=' . $_GET['sID']));
        break;
      case 'deleteconfirm_sub':
        // demo active test
        if (zen_admin_demo()) {
          $_GET['action']= '';
          $messageStack->add_session(ERROR_ADMIN_DEMO, 'caution');
          zen_redirect(zen_href_link_admin(FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $_GET['zID'] . '&action=list&spage=' . $_GET['spage']));
        }
        $sID = zen_db_prepare_input($_GET['sID']);

        $gBitDb->Execute("delete from " . TABLE_ZONES_TO_GEO_ZONES . "
                      where `association_id` = '" . (int)$sID . "'");

        zen_redirect(zen_href_link_admin(FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $_GET['zID'] . '&action=list&spage=' . $_GET['spage']));
        break;
    }
  }

  $action = (isset($_GET['action']) ? $_GET['action'] : '');

  if (zen_not_null($action)) {
    switch ($action) {
      case 'insert_zone':
        $geo_zone_name = zen_db_prepare_input($_POST['geo_zone_name']);
        $geo_zone_description = zen_db_prepare_input($_POST['geo_zone_description']);

        $gBitDb->Execute("insert into " . TABLE_GEO_ZONES . "
                    (`geo_zone_name`, `geo_zone_description`, `date_added`)
                    values ('" . zen_db_input($geo_zone_name) . "',
                            '" . zen_db_input($geo_zone_description) . "',
                            " . $gBitDb->mDb->sysTimeStamp . ")");

        $new_zone_id = zen_db_insert_id( TABLE_GEO_ZONES, 'geo_zone_id' );

        zen_redirect(zen_href_link_admin(FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $new_zone_id));
        break;
      case 'save_zone':
        $zID = zen_db_prepare_input($_GET['zID']);
        $geo_zone_name = zen_db_prepare_input($_POST['geo_zone_name']);
        $geo_zone_description = zen_db_prepare_input($_POST['geo_zone_description']);

        $gBitDb->Execute("update " . TABLE_GEO_ZONES . "
                      set `geo_zone_name` = '" . zen_db_input($geo_zone_name) . "',
                          `geo_zone_description` = '" . zen_db_input($geo_zone_description) . "',
                          `last_modified` = " . $gBitDb->mDb->sysTimeStamp . " where `geo_zone_id` = '" . (int)$zID . "'");


        zen_redirect(zen_href_link_admin(FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $_GET['zID']));
        break;
      case 'deleteconfirm_zone':
        // demo active test
        if (zen_admin_demo()) {
          $_GET['action']= '';
          $messageStack->add_session(ERROR_ADMIN_DEMO, 'caution');
          zen_redirect(zen_href_link_admin(FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage']));
        }
        $zID = zen_db_prepare_input($_GET['zID']);

        $check_tax_rates = $gBitDb->Execute("select `tax_zone_id` from " . TABLE_TAX_RATES . " where tax_zone_id='" . $zID . "'");
        if ($check_tax_rates->RecordCount() > 0) {
          $_GET['action']= '';
          $messageStack->add_session(ERROR_TAX_RATE_EXISTS, 'caution');
        } else {
          $gBitDb->Execute("delete from " . TABLE_ZONES_TO_GEO_ZONES . "
                        where `geo_zone_id` = '" . (int)$zID . "'");

          $gBitDb->Execute("delete from " . TABLE_GEO_ZONES . "
                        where `geo_zone_id` = '" . (int)$zID . "'");
        }

        zen_redirect(zen_href_link_admin(FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage']));
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
<?php
  if (isset($_GET['zID']) && (($saction == 'edit') || ($saction == 'new'))) {
?>
<script type="text/javascript"><!--
function resetZoneSelected(theForm) {
  if (theForm.state.value != '') {
    theForm.zone_id.selectedIndex = '0';
    if (theForm.zone_id.options.length > 0) {
      theForm.state.value = '<?php echo JS_STATE_SELECT; ?>';
    }
  }
}

function update_zone(theForm) {
  var NumState = theForm.zone_id.options.length;
  var SelectedCountry = "";

  while(NumState > 0) {
    NumState--;
    theForm.zone_id.options[NumState] = null;
  }

  SelectedCountry = theForm.zone_country_id.options[theForm.zone_country_id.selectedIndex].value;

<?php echo zen_js_zone_list('SelectedCountry', 'theForm', 'zone_id'); ?>

}
//--></script>
<?php
  }
?>
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
            <td class="pageHeading"><?php echo HEADING_TITLE; if (isset($_GET['zone'])) echo '<br><span class="smallText">' . zen_get_geo_zone_name($_GET['zone']) . '</span>'; ?></td>
            <td class="pageHeading" align="right"><?php echo zen_draw_separator('pixel_trans.gif', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top">
<?php
  if ($action == 'list') {
?>
            <table>
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_COUNTRY; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_COUNTRY_ZONE; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php
    $rows = 0;
    $zones_query_raw = "select a.`association_id`, a.`zone_country_id`, c.`countries_name`, a.`zone_id`, a.`geo_zone_id`, a.`last_modified`, a.`date_added`, z.`zone_name` from " . TABLE_ZONES_TO_GEO_ZONES . " a left join " . TABLE_COUNTRIES . " c on a.`zone_country_id` = c.`countries_id` left join " . TABLE_ZONES . " z on a.`zone_id` = z.`zone_id` where a.`geo_zone_id` = " . $_GET['zID'] . " order by `association_id`";
    $zones_split = new splitPageResults($_GET['spage'], MAX_DISPLAY_SEARCH_RESULTS, $zones_query_raw, $zones_query_numrows);
    $offset = (isset($_GET['spage']) ? (($_GET['spage']-1) * MAX_DISPLAY_SEARCH_RESULTS) : 0);
	$zones = $gBitDb->query($zones_query_raw, FALSE, MAX_DISPLAY_SEARCH_RESULTS, $offset );
    while (!$zones->EOF) {
      $rows++;
      if ((!isset($_GET['sID']) || (isset($_GET['sID']) && ($_GET['sID'] == $zones->fields['association_id']))) && !isset($sInfo) && (substr($action, 0, 3) != 'new')) {
        $sInfo = new objectInfo($zones->fields);
      }
      if (isset($sInfo) && is_object($sInfo) && ($zones->fields['association_id'] == $sInfo->association_id)) {
        echo '                  <tr id="defaultSelected" class="info" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . zen_href_link_admin(FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $_GET['zID'] . '&action=list&spage=' . $_GET['spage'] . '&sID=' . $sInfo->association_id . '&saction=edit') . '\'">' . "\n";
      } else {
        echo '                  <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . zen_href_link_admin(FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $_GET['zID'] . '&action=list&spage=' . $_GET['spage'] . '&sID=' . $zones->fields['association_id']) . '\'">' . "\n";
      }
?>
                <td class="dataTableContent"><?php echo (($zones->fields['countries_name']) ? $zones->fields['countries_name'] : TEXT_ALL_COUNTRIES); ?></td>
                <td class="dataTableContent"><?php echo (($zones->fields['zone_id']) ? $zones->fields['zone_name'] : PLEASE_SELECT); ?></td>
                <td class="dataTableContent" align="right"><?php if (isset($sInfo) && is_object($sInfo) && ($zones->fields['association_id'] == $sInfo->association_id)) { echo zen_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . zen_href_link_admin(FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $_GET['zID'] . '&action=list&spage=' . $_GET['spage'] . '&sID=' . $zones->fields['association_id']) . '">' . zen_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
      $zones->MoveNext();
    }
?>
              <tr>
                <td colspan="3"><table>
                  <tr>
                    <td class="smallText" valign="top"><?php echo $zones_split->display_count($zones_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $_GET['spage'], TEXT_DISPLAY_NUMBER_OF_COUNTRIES); ?></td>
                    <td class="smallText" align="right"><?php echo $zones_split->display_links($zones_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['spage'], 'zpage=' . $_GET['zpage'] . '&zID=' . $_GET['zID'] . '&action=list', 'spage'); ?></td>
                  </tr>
                </table></td>
              </tr>
              <tr>
                <td align="right" colspan="3"><?php if (empty($saction)) echo '<a href="' . zen_href_link_admin(FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $_GET['zID']) . '">' . zen_image_button('button_back.gif', IMAGE_BACK) . '</a> <a href="' . zen_href_link_admin(FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $_GET['zID'] . '&action=list&spage=' . $_GET['spage'] . '&' . (isset($sInfo) ? 'sID=' . $sInfo->association_id . '&' : '') . 'saction=new') . '">' . zen_image_button('button_insert.gif', IMAGE_INSERT) . '</a>'; ?></td>
              </tr>
            </table>
<?php
  } else {
?>
            <table>
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_TAX_ZONES; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php
    $zones_query_raw = "select `geo_zone_id`, `geo_zone_name`, `geo_zone_description`, `last_modified`, `date_added` from " . TABLE_GEO_ZONES . " order by `geo_zone_name`";
    $zones_split = new splitPageResults($_GET['zpage'], MAX_DISPLAY_SEARCH_RESULTS, $zones_query_raw, $zones_query_numrows);
    $offset = (isset($_GET['zpage']) ? (($_GET['zpage']-1) * MAX_DISPLAY_SEARCH_RESULTS) : 0);
	$zones = $gBitDb->query($zones_query_raw, FALSE, MAX_DISPLAY_SEARCH_RESULTS, $offset );
    while (!$zones->EOF) {
      if ((!isset($_GET['zID']) || (isset($_GET['zID']) && ($_GET['zID'] == $zones->fields['geo_zone_id']))) && !isset($zInfo) && (substr($action, 0, 3) != 'new')) {
        $num_zones = $gBitDb->Execute("select count(*) as `num_zones`
                                   from " . TABLE_ZONES_TO_GEO_ZONES . "
                                   where `geo_zone_id` = '" . (int)$zones->fields['geo_zone_id'] . "'
                                   group by `geo_zone_id`");

        if ($num_zones->fields['num_zones'] > 0) {
          $zones->fields['num_zones'] = $num_zones->fields['num_zones'];
        } else {
          $zones->fields['num_zones'] = 0;
        }

        $num_tax_rates = $gBitDb->Execute("select count(*) as `num_tax_rates`
                                   from " . TABLE_TAX_RATES . "
                                   where `tax_zone_id` = '" . (int)$zones->fields['geo_zone_id'] . "'
                                   group by `tax_zone_id`");

        if ($num_tax_rates->fields['num_tax_rates'] > 0) {
          $zones->fields['num_tax_rates'] = $num_tax_rates->fields['num_tax_rates'];
        } else {
          $zones->fields['num_tax_rates'] = 0;
        }

        $zInfo = new objectInfo($zones->fields);
      }
      if (isset($zInfo) && is_object($zInfo) && ($zones->fields['geo_zone_id'] == $zInfo->geo_zone_id)) {
        echo '                  <tr id="defaultSelected" class="info" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . zen_href_link_admin(FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $zInfo->geo_zone_id . '&action=list') . '\'">' . "\n";
      } else {
        echo '                  <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . zen_href_link_admin(FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $zones->fields['geo_zone_id']) . '\'">' . "\n";
      }
?>
                <td class="dataTableContent"><?php echo '<a href="' . zen_href_link_admin(FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $zones->fields['geo_zone_id'] . '&action=list') . '">' . zen_image(DIR_WS_ICONS . 'folder.gif', ICON_FOLDER) . '</a>&nbsp;' . $zones->fields['geo_zone_name']; ?></td>
                <td class="dataTableContent" align="right"><?php if (isset($zInfo) && is_object($zInfo) && ($zones->fields['geo_zone_id'] == $zInfo->geo_zone_id)) { echo zen_image(DIR_WS_IMAGES . 'icon_arrow_right.gif'); } else { echo '<a href="' . zen_href_link_admin(FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $zones->fields['geo_zone_id']) . '">' . zen_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
      $zones->MoveNext();
    }
?>
              <tr>
                <td colspan="2"><table>
                  <tr>
                    <td class="smallText"><?php echo $zones_split->display_count($zones_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $_GET['zpage'], TEXT_DISPLAY_NUMBER_OF_TAX_ZONES); ?></td>
                    <td class="smallText" align="right"><?php echo $zones_split->display_links($zones_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['zpage'], '', 'zpage'); ?></td>
                  </tr>
                </table></td>
              </tr>
              <tr>
                <td align="right" colspan="2"><?php if (!$action) echo '<a href="' . zen_href_link_admin(FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $zInfo->geo_zone_id . '&action=new_zone') . '">' . zen_image_button('button_insert.gif', IMAGE_INSERT) . '</a>'; ?></td>
              </tr>
            </table>
<?php
  }
?>
            </td>
<?php
  $heading = array();
  $contents = array();

  if ($action == 'list') {
    switch ($saction) {
      case 'new':
        $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_NEW_SUB_ZONE . '</b>');

        $contents = array('form' => zen_draw_form_admin('zones', FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $_GET['zID'] . '&action=list&spage=' . $_GET['spage'] . '&' . (isset($_GET['sID']) ? 'sID=' . $_GET['sID'] . '&' : '') . 'saction=insert_sub'));
        $contents[] = array('text' => TEXT_INFO_NEW_SUB_ZONE_INTRO);
        $contents[] = array('text' => '<br>' . TEXT_INFO_COUNTRY . '<br>' . zen_draw_pull_down_menu('zone_country_id', zen_get_countries_admin(TEXT_ALL_COUNTRIES), '', 'onChange="update_zone(this.form);"'));
        $contents[] = array('text' => '<br>' . TEXT_INFO_COUNTRY_ZONE . '<br>' . zen_draw_pull_down_menu('zone_id', zen_prepare_country_zones_pull_down()));
        $contents[] = array('align' => 'center', 'text' => '<br>' . zen_image_submit('button_insert.gif', IMAGE_INSERT) . ' <a href="' . zen_href_link_admin(FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $_GET['zID'] . '&action=list&spage=' . $_GET['spage'] . '&' . (isset($_GET['sID']) ? 'sID=' . $_GET['sID'] : '')) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
        break;
      case 'edit':
        $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_EDIT_SUB_ZONE . '</b>');

        $contents = array('form' => zen_draw_form_admin('zones', FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $_GET['zID'] . '&action=list&spage=' . $_GET['spage'] . '&sID=' . $sInfo->association_id . '&saction=save_sub'));
        $contents[] = array('text' => TEXT_INFO_EDIT_SUB_ZONE_INTRO);
        $contents[] = array('text' => '<br>' . TEXT_INFO_COUNTRY . '<br>' . zen_draw_pull_down_menu('zone_country_id', zen_get_countries_admin(TEXT_ALL_COUNTRIES), $sInfo->zone_country_id, 'onChange="update_zone(this.form);"'));
        $contents[] = array('text' => '<br>' . TEXT_INFO_COUNTRY_ZONE . '<br>' . zen_draw_pull_down_menu('zone_id', zen_prepare_country_zones_pull_down($sInfo->zone_country_id), $sInfo->zone_id));
        $contents[] = array('align' => 'center', 'text' => '<br>' . zen_image_submit('button_update.gif', IMAGE_UPDATE) . ' <a href="' . zen_href_link_admin(FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $_GET['zID'] . '&action=list&spage=' . $_GET['spage'] . '&sID=' . $sInfo->association_id) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
        break;
      case 'delete':
        $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_DELETE_SUB_ZONE . '</b>');

        $contents = array('form' => zen_draw_form_admin('zones', FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $_GET['zID'] . '&action=list&spage=' . $_GET['spage'] . '&sID=' . $sInfo->association_id . '&saction=deleteconfirm_sub'));
        $contents[] = array('text' => TEXT_INFO_DELETE_SUB_ZONE_INTRO);
        $contents[] = array('text' => '<br><b>' . $sInfo->countries_name . '</b>');
        $contents[] = array('align' => 'center', 'text' => '<br>' . zen_image_submit('button_delete.gif', IMAGE_DELETE) . ' <a href="' . zen_href_link_admin(FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $_GET['zID'] . '&action=list&spage=' . $_GET['spage'] . '&sID=' . $sInfo->association_id) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
        break;
      default:
        if (isset($sInfo) && is_object($sInfo)) {
          $heading[] = array('text' => '<b>' . $sInfo->countries_name . '</b>');

          $contents[] = array('align' => 'center', 'text' => '<a href="' . zen_href_link_admin(FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $_GET['zID'] . '&action=list&spage=' . $_GET['spage'] . '&sID=' . $sInfo->association_id . '&saction=edit') . '">' . zen_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . zen_href_link_admin(FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $_GET['zID'] . '&action=list&spage=' . $_GET['spage'] . '&sID=' . $sInfo->association_id . '&saction=delete') . '">' . zen_image_button('button_delete.gif', IMAGE_DELETE) . '</a>');
          $contents[] = array('text' => '<br>' . TEXT_INFO_DATE_ADDED . ' ' . zen_date_short($sInfo->date_added));
          if (zen_not_null($sInfo->last_modified)) $contents[] = array('text' => TEXT_INFO_LAST_MODIFIED . ' ' . zen_date_short($sInfo->last_modified));
        }
        break;
    }
  } else {
    switch ($action) {
      case 'new_zone':
        $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_NEW_ZONE . '</b>');

        $contents = array('form' => zen_draw_form_admin('zones', FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $_GET['zID'] . '&action=insert_zone'));
        $contents[] = array('text' => TEXT_INFO_NEW_ZONE_INTRO);
        $contents[] = array('text' => '<br>' . TEXT_INFO_ZONE_NAME . '<br>' . zen_draw_input_field('geo_zone_name', '', zen_set_field_length(TABLE_GEO_ZONES, 'geo_zone_name')));
        $contents[] = array('text' => '<br>' . TEXT_INFO_ZONE_DESCRIPTION . '<br>' . zen_draw_input_field('geo_zone_description', '', zen_set_field_length(TABLE_GEO_ZONES, 'geo_zone_description')));
        $contents[] = array('align' => 'center', 'text' => '<br>' . zen_image_submit('button_insert.gif', IMAGE_INSERT) . ' <a href="' . zen_href_link_admin(FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $_GET['zID']) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
        break;
      case 'edit_zone':
        $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_EDIT_ZONE . '</b>');

        $contents = array('form' => zen_draw_form_admin('zones', FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $zInfo->geo_zone_id . '&action=save_zone'));
        $contents[] = array('text' => TEXT_INFO_EDIT_ZONE_INTRO);
        $contents[] = array('text' => '<br>' . TEXT_INFO_ZONE_NAME . '<br>' . zen_draw_input_field('geo_zone_name', $zInfo->geo_zone_name, zen_set_field_length(TABLE_GEO_ZONES, 'geo_zone_name')));
        $contents[] = array('text' => '<br>' . TEXT_INFO_ZONE_DESCRIPTION . '<br>' . zen_draw_input_field('geo_zone_description', $zInfo->geo_zone_description, zen_set_field_length(TABLE_GEO_ZONES, 'geo_zone_description')));
        $contents[] = array('align' => 'center', 'text' => '<br>' . zen_image_submit('button_update.gif', IMAGE_UPDATE) . ' <a href="' . zen_href_link_admin(FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $zInfo->geo_zone_id) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
        break;
      case 'delete_zone':
        $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_DELETE_ZONE . '</b>');

        $contents = array('form' => zen_draw_form_admin('zones', FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $zInfo->geo_zone_id . '&action=deleteconfirm_zone'));
        $contents[] = array('text' => TEXT_INFO_DELETE_ZONE_INTRO);
        $contents[] = array('text' => '<br><b>' . $zInfo->geo_zone_name . '</b>');
        $contents[] = array('align' => 'center', 'text' => '<br>' . zen_image_submit('button_delete.gif', IMAGE_DELETE) . ' <a href="' . zen_href_link_admin(FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $zInfo->geo_zone_id) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
        break;
      default:
        if (isset($zInfo) && is_object($zInfo)) {
          $heading[] = array('text' => '<b>' . $zInfo->geo_zone_name . '</b>');

          $contents[] = array('align' => 'center', 'text' => '<a href="' . zen_href_link_admin(FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $zInfo->geo_zone_id . '&action=edit_zone') . '">' . zen_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . zen_href_link_admin(FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $zInfo->geo_zone_id . '&action=delete_zone') . '">' . zen_image_button('button_delete.gif', IMAGE_DELETE) . '</a>' . ' <a href="' . zen_href_link_admin(FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $zInfo->geo_zone_id . '&action=list') . '">' . zen_image_button('button_details.gif', IMAGE_DETAILS) . '</a>');
          $contents[] = array('align' => 'center', 'text' =>  ($zInfo->num_tax_rates > 0 ? '<a href="' . zen_href_link_admin(FILENAME_TAX_RATES, '', 'NONSSL') . '">' . zen_image_button('button_tax_rates.gif', IMAGE_TAX_RATES) . '</a>' : ''));
          $contents[] = array('text' => '<br>' . TEXT_INFO_NUMBER_ZONES . ' ' . $zInfo->num_zones);
          $contents[] = array('text' => '<br>' . TEXT_INFO_NUMBER_TAX_RATES . ' ' . $zInfo->num_tax_rates);
          $contents[] = array('text' => '<br>' . TEXT_INFO_DATE_ADDED . ' ' . zen_date_short($zInfo->date_added));
          if (zen_not_null($zInfo->last_modified)) $contents[] = array('text' => TEXT_INFO_LAST_MODIFIED . ' ' . zen_date_short($zInfo->last_modified));
          $contents[] = array('text' => '<br>' . TEXT_INFO_ZONE_DESCRIPTION . '<br>' . $zInfo->geo_zone_description);
        }
        break;
    }
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
