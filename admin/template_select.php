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
// get an array of template info
  $dir = @dir(DIR_FS_CATALOG_TEMPLATES);
  if (!$dir) die('DIR_FS_CATALOG_TEMPLATES NOT SET');
  while ($file = $dir->read()) {
    if (is_dir(DIR_FS_CATALOG_TEMPLATES . $file) && strtoupper($file) != 'CVS' && $file != 'template_default') {
      if (file_exists(DIR_FS_CATALOG_TEMPLATES . $file . '/template_info.php')) {
        require(DIR_FS_CATALOG_TEMPLATES . $file . '/template_info.php');
        $template_info[$file] = array('name' => $template_name,
                                      'version' => $template_version,
                                      'author' => $template_author,
                                      'description' => $template_description,
                                      'screenshot' => $template_screenshot);
      }
    }
  }

  $action = (isset($_GET['action']) ? $_GET['action'] : '');

  if (zen_not_null($action)) {
    switch ($action) {
      case 'insert':
        $check_query = $gBitDb->Execute("select * from " . TABLE_TEMPLATE_SELECT . " where template_language = '" . $_POST['lang'] . "'");
        if ($check_query->RecordCount() < 1 ) {
          $gBitDb->Execute("insert into " . TABLE_TEMPLATE_SELECT . " (template_dir, template_language) values ('" . $_POST['ln'] . "', '" . $_POST['lang'] . "')");
          $_GET['tID'] = zen_db_insert_id( TABLE_TEMPLATE_SELECT, 'template_id' );
        }
        $action="";
        break;
      case 'save':
        $gBitDb->Execute("update " . TABLE_TEMPLATE_SELECT . " set template_dir = '" . $_POST['ln'] . "' where template_id = '" . $_GET['tID'] . "'");
        break;
      case 'deleteconfirm':
        $check_query = $gBitDb->Execute("select template_language from " . TABLE_TEMPLATE_SELECT . " where template_id = '" . $_GET['tID'] . "'");
        if ( $check_query->fields['template_language'] != 0 ) {
          $gBitDb->Execute("delete from " . TABLE_TEMPLATE_SELECT . " where template_id = '" . $_GET['tID'] . "'");
          zen_redirect(zen_href_link_admin(FILENAME_TEMPLATE_SELECT, 'page=' . $_GET['page']));
        }
        $action="";
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
                      <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_LANGUAGE; ?></td>
                      <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_NAME; ?></td>
                      <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_DIRECTORY; ?></td>
                      <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
                    </tr>
                    <?php
  $template_query_raw = "select * from " . TABLE_TEMPLATE_SELECT;
  $template_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS, $template_query_raw, $template_query_numrows);
  $templates = $gBitDb->Execute($template_query_raw);
  while (!$templates->EOF) {
    if ((!isset($_GET['tID']) || (isset($_GET['tID']) && ($_GET['tID'] == $templates->fields['template_id']))) && !isset($tInfo) && (substr($action, 0, 3) != 'new')) {
      $tInfo = new objectInfo($templates->fields);
    }

    if (isset($tInfo) && is_object($tInfo) && ($templates->fields['template_id'] == $tInfo->template_id)) {
      echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . zen_href_link_admin(FILENAME_TEMPLATE_SELECT, 'page=' . $_GET['page'] . '&tID=' . $tInfo->template_id . '&action=edit') . '\'">' . "\n";
    } else {
      echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . zen_href_link_admin(FILENAME_TEMPLATE_SELECT, 'page=' . $_GET['page'] . '&tID=' . $templates->fields['template_id']) . '\'">' . "\n";
    }
    if ($templates->fields['template_language'] == 0) {
      $template_language = "Default(All)";
    } else {
      $ln = $gBitDb->Execute("select name
                          from " . TABLE_LANGUAGES . "
                          where languages_id = '" . $templates->fields['template_language'] . "'");
      $template_language = $ln->fields['name'];
    }
?>
                    <td class="dataTableContent"><?php echo $template_language; ?></td>
                    <td class="dataTableContent"><?php echo $template_info[$templates->fields['template_dir']]['name']; ?></td>
                    <td class="dataTableContent" align="center"><?php echo $templates->fields['template_dir']; ?></td>
                    <td class="dataTableContent" align="right">
                      <?php if (isset($tInfo) && is_object($tInfo) && ($templates->fields['template_id'] == $tInfo->template_id) ) { echo zen_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . zen_href_link_admin(FILENAME_TEMPLATE_DEFAULT, 'page=' . $_GET['page'] . '&tID=' . $templates->fields['template_id']) . '">' . zen_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>
                      &nbsp;</td>
                    </tr>
<?php
    $templates->MoveNext();
  }
?>
                    <tr>
                      <td colspan="4"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                          <tr>
                            <td class="smallText" valign="top"><?php echo $template_split->display_count($template_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_TEMPLATES); ?></td>
                            <td class="smallText" align="right"><?php echo $template_split->display_links($template_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?></td>
                          </tr>
                          <?php
  if (empty($action)) {
?>
                          <tr>
                            <td colspan="2" align="right"><?php echo '<a href="' . zen_href_link_admin(FILENAME_TEMPLATE_SELECT, 'page=' . $_GET['page'] . '&action=new') . '">' . zen_image_button('button_new_language.gif', IMAGE_NEW_TEMPLATE) . '</a>'; ?></td>
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
      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_NEW_TEMPLATE . '</b>');

      $contents = array('form' => zen_draw_form_admin('zones', FILENAME_TEMPLATE_SELECT, 'page=' . $_GET['page'] . '&action=insert'));
      $contents[] = array('text' => TEXT_INFO_INSERT_INTRO);
      while (list ($key, $value) = each($template_info) ) {
        $template_array[] = array('id' => $key, 'text' => $value['name']);
      }
      $lns = $gBitDb->Execute("select name, languages_id from " . TABLE_LANGUAGES);
      while (!$lns->EOF) {
        $language_array[] = array('text' => $lns->fields['name'], 'id' => $lns->fields['languages_id']);
        $lns->MoveNext();
      } 
      $contents[] = array('text' => '<br>' . TEXT_INFO_TEMPLATE_NAME . '<br>' . zen_draw_pull_down_menu('ln', $template_array));
      $contents[] = array('text' => '<br>' . TEXT_INFO_LANGUAGE_NAME . '<br>' . zen_draw_pull_down_menu('lang', $language_array, $_POST['lang']));
      $contents[] = array('align' => 'center', 'text' => '<br>' . zen_image_submit('button_insert.gif', IMAGE_INSERT) . '&nbsp;<a href="' . zen_href_link_admin(FILENAME_TEMPLATE_SELECT, 'page=' . $_GET['page']) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    case 'edit':
      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_EDIT_TEMPLATE . '</b>');

      $contents = array('form' => zen_draw_form_admin('zones', FILENAME_TEMPLATE_SELECT, 'page=' . $_GET['page'] . '&tID=' . $tInfo->template_id . '&action=save'));
      $contents[] = array('text' => TEXT_INFO_EDIT_INTRO);
      reset($template_info);
      while (list ($key, $value) = each($template_info) ) {
        $template_array[] = array('id' => $key, 'text' => $value['name']);
      }
      $contents[] = array('text' => '<br>' . TEXT_INFO_TEMPLATE_NAME . '<br>' . zen_draw_pull_down_menu('ln', $template_array));
      $contents[] = array('align' => 'center', 'text' => '<br>' . zen_image_submit('button_update.gif', IMAGE_UPDATE) . '&nbsp;<a href="' . zen_href_link_admin(FILENAME_TEMPLATE_SELECT, 'page=' . $_GET['page'] . '&tID=' . $tInfo->template_id) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    case 'delete':
      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_DELETE_TEMPLATE . '</b>');

      $contents = array('form' => zen_draw_form_admin('zones', FILENAME_TEMPLATE_SELECT, 'page=' . $_GET['page'] . '&tID=' . $tInfo->template_id . '&action=deleteconfirm'));
      $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
      $contents[] = array('text' => '<br><b>' . $template_info[$tInfo->template_dir]['name'] . '</b>');
      $contents[] = array('align' => 'center', 'text' => '<br>' . zen_image_submit('button_delete.gif', IMAGE_DELETE) . '&nbsp;<a href="' . zen_href_link_admin(FILENAME_TEMPLATE_SELECT, 'page=' . $_GET['page'] . '&tID=' . $tInfo->template_id) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    default:
      if (isset($tInfo) && is_object($tInfo)) {
        $heading[] = array('text' => '<b>' . $template_info[$tInfo->template_dir]['name'] . '</b>');
        if ($tInfo->template_language == 0) {
          $contents[] = array('align' => 'center', 'text' => '<a href="' . zen_href_link_admin(FILENAME_TEMPLATE_SELECT, 'page=' . $_GET['page'] . '&tID=' . $tInfo->template_id . '&action=edit') . '">' . zen_image_button('button_edit.gif', IMAGE_EDIT) . '</a>');
        } else {
          $contents[] = array('align' => 'center', 'text' => '<a href="' . zen_href_link_admin(FILENAME_TEMPLATE_SELECT, 'page=' . $_GET['page'] . '&tID=' . $tInfo->template_id . '&action=edit') . '">' . zen_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . zen_href_link_admin(FILENAME_TEMPLATE_SELECT, 'page=' . $_GET['page'] . '&tID=' . $tInfo->template_id . '&action=delete') . '">' . zen_image_button('button_delete.gif', IMAGE_DELETE) . '</a>');
        }
        $contents[] = array('text' => '<br>' . TEXT_INFO_TEMPLATE_AUTHOR  . $template_info[$tInfo->template_dir]['author']);
        $contents[] = array('text' => '<br>' . TEXT_INFO_TEMPLATE_VERSION  . $template_info[$tInfo->template_dir]['version']);
        $contents[] = array('text' => '<br>' . TEXT_INFO_TEMPLATE_DESCRIPTION  . '<br />' . $template_info[$tInfo->template_dir]['description']);
        $contents[] = array('text' => '<br>' . TEXT_INFO_TEMPLATE_INSTALLED  . '<br />');
        while (list ($key, $value) = each($template_info) ) {
          $contents[] = array('text' => '<a href="' . DIR_WS_CATALOG_TEMPLATE . $key . '/images/' . $value['screenshot'] . '" target = "_blank">' . zen_image_button('button_preview.gif', IMAGE_PREVIEW) . '</a>&nbsp;&nbsp;' . $value['name']);
        }
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
