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
//  $Id: zones.php,v 1.10 2009/01/07 04:18:28 spiderr Exp $
//
  require('includes/application_top.php');

  $action = (isset($_GET['action']) ? $_GET['action'] : '');

  if (zen_not_null($action)) {
    switch ($action) {
      case 'insert':
        $zone_country_id = zen_db_prepare_input($_POST['zone_country_id']);
        $zone_code = zen_db_prepare_input($_POST['zone_code']);
        $zone_name = zen_db_prepare_input($_POST['zone_name']);

        $gBitDb->Execute("insert into " . TABLE_ZONES . "
                    (`zone_country_id`, `zone_code`, `zone_name`)
                    values ('" . (int)$zone_country_id . "',
                            '" . zen_db_input($zone_code) . "',
                            '" . zen_db_input($zone_name) . "')");

        zen_redirect(zen_href_link_admin(FILENAME_ZONES));
        break;
      case 'save':
        $zone_id = zen_db_prepare_input($_GET['cID']);
        $zone_country_id = zen_db_prepare_input($_POST['zone_country_id']);
        $zone_code = zen_db_prepare_input($_POST['zone_code']);
        $zone_name = zen_db_prepare_input($_POST['zone_name']);

        $gBitDb->Execute("update " . TABLE_ZONES . "
                      set `zone_country_id` = '" . (int)$zone_country_id . "',
                          `zone_code` = '" . zen_db_input($zone_code) . "',
                          `zone_name` = '" . zen_db_input($zone_name) . "'
                      where `zone_id` = '" . (int)$zone_id . "'");

        zen_redirect(zen_href_link_admin(FILENAME_ZONES, 'cID=' . $zone_id));
        break;
      case 'deleteconfirm':
        // demo active test
        if (zen_admin_demo()) {
          $_GET['action']= '';
          $messageStack->add_session(ERROR_ADMIN_DEMO, 'caution');
          zen_redirect(zen_href_link_admin(FILENAME_ZONES, 'page=' . $_GET['page']));
        }
        $zone_id = zen_db_prepare_input($_GET['cID']);

        $gBitDb->Execute("delete from " . TABLE_ZONES . " where `zone_id` = '" . (int)$zone_id . "'");

        zen_redirect(zen_href_link_admin(FILENAME_ZONES, 'page=' . $_GET['page']));
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
          <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
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
                      <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_COUNTRY_NAME; ?></td>
                      <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_ZONE_NAME; ?></td>
                      <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_ZONE_CODE; ?></td>
                      <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
					</tr>
				<?php
	$zones_query_raw = "select z.`zone_id`, c.`countries_id`, c.`countries_name`, z.`zone_name`, z.`zone_code`, z.`zone_country_id` from " . TABLE_ZONES . " z, " . TABLE_COUNTRIES . " c where z.`zone_country_id` = c.`countries_id` ORDER BY c.`countries_name`, z.`zone_name`";
	$zones = $gBitDb->query( $zones_query_raw, NULL );
	while (!$zones->EOF) {
		if ((!isset($_GET['cID']) || (isset($_GET['cID']) && ($_GET['cID'] == $zones->fields['zone_id']))) && !isset($cInfo) && (substr($action, 0, 3) != 'new')) {
			$cInfo = new objectInfo($zones->fields);
		}

		if (isset($cInfo) && is_object($cInfo) && ($zones->fields['zone_id'] == $cInfo->zone_id)) {
			echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . zen_href_link_admin(FILENAME_ZONES, 'cID=' . $cInfo->zone_id . '&action=edit') . '\'">' . "\n";
		} else {
			echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . zen_href_link_admin(FILENAME_ZONES, 'cID=' . $zones->fields['zone_id']) . '\'">' . "\n";
		}
?>
                    <td class="dataTableContent"><?php echo $zones->fields['countries_name']; ?></td>
                    <td class="dataTableContent"><?php echo $zones->fields['zone_name']; ?></td>
                    <td class="dataTableContent" align="center"><?php echo $zones->fields['zone_code']; ?></td>
                    <td class="dataTableContent" align="right">
                      <?php if (isset($cInfo) && is_object($cInfo) && ($zones->fields['zone_id'] == $cInfo->zone_id) ) { echo zen_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . zen_href_link_admin(FILENAME_ZONES, 'cID=' . $zones->fields['zone_id']) . '">' . zen_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>
                      &nbsp;</td>
                    </tr>
<?php
    $zones->MoveNext();
  }
?>
                  </table></td>
                <?php
  $heading = array();
  $contents = array();

  switch ($action) {
    case 'new':
      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_NEW_ZONE . '</b>');

      $contents = array('form' => zen_draw_form_admin('zones', FILENAME_ZONES, 'action=insert'));
      $contents[] = array('text' => TEXT_INFO_INSERT_INTRO);
      $contents[] = array('text' => '<br>' . TEXT_INFO_ZONES_NAME . '<br>' . zen_draw_input_field('zone_name'));
      $contents[] = array('text' => '<br>' . TEXT_INFO_ZONES_CODE . '<br>' . zen_draw_input_field('zone_code'));
      $contents[] = array('text' => '<br>' . TEXT_INFO_COUNTRY_NAME . '<br>' . zen_draw_pull_down_menu('zone_country_id', zen_get_countries_admin()));
      $contents[] = array('align' => 'center', 'text' => '<br>' . zen_image_submit('button_insert.gif', IMAGE_INSERT) . '&nbsp;<a href="' . zen_href_link_admin(FILENAME_ZONES) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    case 'edit':
      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_EDIT_ZONE . '</b>');

      $contents = array('form' => zen_draw_form_admin('zones', FILENAME_ZONES, 'cID=' . $cInfo->zone_id . '&action=save'));
      $contents[] = array('text' => TEXT_INFO_EDIT_INTRO);
      $contents[] = array('text' => '<br>' . TEXT_INFO_ZONES_NAME . '<br>' . zen_draw_input_field('zone_name', $cInfo->zone_name));
      $contents[] = array('text' => '<br>' . TEXT_INFO_ZONES_CODE . '<br>' . zen_draw_input_field('zone_code', $cInfo->zone_code));
      $contents[] = array('text' => '<br>' . TEXT_INFO_COUNTRY_NAME . '<br>' . zen_draw_pull_down_menu('zone_country_id', zen_get_countries_admin(), $cInfo->countries_id));
      $contents[] = array('align' => 'center', 'text' => '<br>' . zen_image_submit('button_update.gif', IMAGE_UPDATE) . '&nbsp;<a href="' . zen_href_link_admin(FILENAME_ZONES, 'cID=' . $cInfo->zone_id) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    case 'delete':
      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_DELETE_ZONE . '</b>');

      $contents = array('form' => zen_draw_form_admin('zones', FILENAME_ZONES, 'cID=' . $cInfo->zone_id . '&action=deleteconfirm'));
      $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
      $contents[] = array('text' => '<br><b>' . $cInfo->zone_name . '</b>');
      $contents[] = array('align' => 'center', 'text' => '<br>' . zen_image_submit('button_delete.gif', IMAGE_DELETE) . '&nbsp;<a href="' . zen_href_link_admin(FILENAME_ZONES, 'cID=' . $cInfo->zone_id) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    default:
      if (isset($cInfo) && is_object($cInfo)) {
        $heading[] = array('text' => '<b>' . $cInfo->zone_name . '</b>');

        $contents[] = array('align' => 'center', 'text' => '<a href="' . zen_href_link_admin(FILENAME_ZONES, 'cID=' . $cInfo->zone_id . '&action=edit') . '">' . zen_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . zen_href_link_admin(FILENAME_ZONES, 'cID=' . $cInfo->zone_id . '&action=delete') . '">' . zen_image_button('button_delete.gif', IMAGE_DELETE) . '</a>');
        $contents[] = array('text' => '<br>' . TEXT_INFO_ZONES_NAME . '<br>' . $cInfo->zone_name . ' (' . $cInfo->zone_code . ')');
        $contents[] = array('text' => '<br>' . TEXT_INFO_COUNTRY_NAME . ' ' . $cInfo->countries_name);
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
