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
//  $Id: newsletters.php,v 1.7 2005/09/27 22:33:51 spiderr Exp $

  require('includes/application_top.php');

  $action = (isset($_GET['action']) ? $_GET['action'] : '');

  if (zen_not_null($action)) {

    switch ($action) {
      case 'set_editor':
        if ($_GET['reset_editor'] == '0') {
          $_SESSION['html_editor_preference_status'] = 'NONE';
        } else {
          $_SESSION['html_editor_preference_status'] = 'HTMLAREA';
        }
        $action='';
        zen_redirect(zen_href_link_admin(FILENAME_NEWSLETTERS, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . 'nID=' . $newsletter_id));
        break;
      case 'lock':
      case 'unlock':
        $newsletter_id = zen_db_prepare_input($_GET['nID']);
        $status = (($action == 'lock') ? '1' : '0');

        $db->Execute("update " . TABLE_NEWSLETTERS . "
                      set locked = '" . $status . "'
                      where newsletters_id = '" . (int)$newsletter_id . "'");

        zen_redirect(zen_href_link_admin(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $_GET['nID']));
        break;
      case 'insert':
      case 'update':
        if (isset($_POST['newsletter_id'])) $newsletter_id = zen_db_prepare_input($_POST['newsletter_id']);
        $newsletter_module = zen_db_prepare_input($_POST['module']);
        $title = zen_db_prepare_input($_POST['title']);
        $content = zen_db_prepare_input($_POST['content']);
        $content_html = zen_db_prepare_input($_POST['content_html']);

        $newsletter_error = false;
        if (empty($title)) {
          $messageStack->add(ERROR_NEWSLETTER_TITLE, 'error');
          $newsletter_error = true;
        }

        if (empty($newsletter_module)) {
          $messageStack->add(ERROR_NEWSLETTER_MODULE, 'error');
          $newsletter_error = true;
        }

        if ($newsletter_error == false) {
          $sql_data_array = array('title' => $title,
                                  'content' => $content,
                                  'content_html' => $content_html,
                                  'module' => $newsletter_module);

          if ($action == 'insert') {
            $sql_data_array['date_added'] = 'now()';
            $sql_data_array['status'] = '0';
            $sql_data_array['locked'] = '0';

            $db->associateInsert(TABLE_NEWSLETTERS, $sql_data_array);
            $newsletter_id = zen_db_insert_id( TABLE_NEWSLETTERS, 'newsletter_id' );
          } elseif ($action == 'update') {
            $db->associateInsert(TABLE_NEWSLETTERS, $sql_data_array, 'update', "newsletters_id = '" . (int)$newsletter_id . "'");
          }

          zen_redirect(zen_href_link_admin(FILENAME_NEWSLETTERS, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . 'nID=' . $newsletter_id));
        } else {
          $action = 'new';
        }
        break;
      case 'deleteconfirm':
        $newsletter_id = zen_db_prepare_input($_GET['nID']);

        $db->Execute("delete from " . TABLE_NEWSLETTERS . "
                      where newsletters_id = '" . (int)$newsletter_id . "'");

        zen_redirect(zen_href_link_admin(FILENAME_NEWSLETTERS, 'page=' . $_GET['page']));
        break;
      case 'delete':
      case 'new': if (!isset($_GET['nID'])) break;
      case 'send':
        // demo active test
        if (zen_admin_demo()) {
          $_GET['action']= '';
          $messageStack->add_session(ERROR_ADMIN_DEMO, 'caution');
          zen_redirect(zen_href_link_admin(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $_GET['nID']));
        }
      case 'confirm_send':
        $newsletter_id = zen_db_prepare_input($_GET['nID']);

        $check = $db->Execute("select locked
                               from " . TABLE_NEWSLETTERS . "
                               where newsletters_id = '" . (int)$newsletter_id . "'");

        if ($check->fields['locked'] < 1) {
          switch ($action) {
            case 'delete': $error = ERROR_REMOVE_UNLOCKED_NEWSLETTER; break;
            case 'new': $error = ERROR_EDIT_UNLOCKED_NEWSLETTER; break;
            case 'send': $error = ERROR_SEND_UNLOCKED_NEWSLETTER; break;
            case 'confirm_send': $error = ERROR_SEND_UNLOCKED_NEWSLETTER; break;
          }

          $messageStack->add_session($error, 'error');

          zen_redirect(zen_href_link_admin(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $_GET['nID']));
        }
        break;
    }
  }

  if ($_GET['mail_sent_to']) {
    $messageStack->add(sprintf(NOTICE_EMAIL_SENT_TO, $_GET['mail_sent_to']), 'success');
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
  if (typeof _editor_url == "string") HTMLArea.replace('content_html');
  }
  // -->
</script>
<script language="javascript" type="text/javascript"><!--
var form = "";
var submitted = false;
var error = false;
var error_message = "";

function check_select(field_name, field_default, message) {
  if (form.elements[field_name] && (form.elements[field_name].type != "hidden")) {
    var field_value = form.elements[field_name].value;

    if (field_value == field_default) {
      error_message = error_message + "* " + message + "\n";
      error = true;
    }
  }
}
function check_message(msg) {
  if (form.elements['content'] && form.elements['content_html']) {
    var field_value1 = form.elements['content'].value;
    var field_value2 = form.elements['content_html'].value;

    if ((field_value1 == '' || field_value1.length < 3) && (field_value2 == '' || field_value2.length < 3)) {
      error_message = error_message + "* " + msg + "\n";
      error = true;
    }
  }
}
function check_form(form_name) {
  if (submitted == true) {
    alert("<?php echo JS_ERROR_SUBMITTED; ?>");
    return false;
  }
  error = false;
  form = form_name;
  error_message = "<?php echo JS_ERROR; ?>";

//  check_message("<?php echo ENTRY_NOTHING_TO_SEND; ?>");
check_select('audience_selected','',"<?php echo ERROR_PLEASE_SELECT_AUDIENCE; ?>");
  if (error == true) {
    alert(error_message);
    return false;
  } else {
    submitted = true;
    return true;
  }
}
//--></script>
<?php if ($_SESSION['html_editor_preference_status']=="FCKEDITOR") include (DIR_WS_INCLUDES.'fckeditor.php'); ?>
<?php if ($_SESSION['html_editor_preference_status']=="HTMLAREA")  include (DIR_WS_INCLUDES.'htmlarea.php'); ?>
</head>
<body onload="init()">
<div id="spiffycalendar" class="text"></div>
<!-- header //-->
<?php require(DIR_FS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->
<table border="0" width="100%" cellspacing="2" cellpadding="2">
  <tr>
<!-- body_text //-->
    <td width="100%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="0">
      <tr>
        <td width="100%"><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
            <td class="pageHeading" align="right"><?php echo zen_draw_separator('pixel_trans.gif', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
<?php
// toggle switch for editor
        $editor_array = array(array('id' => '0', 'text' => TEXT_NONE),
                              array('id' => '1', 'text' => TEXT_HTML_AREA));
        echo TEXT_EDITOR_INFO . zen_draw_form('set_editor_form', FILENAME_NEWSLETTERS, '', 'get') . '&nbsp;&nbsp;' . zen_draw_pull_down_menu('reset_editor', $editor_array, ($_SESSION['html_editor_preference_status'] == 'HTMLAREA' ? '1' : '0'), 'onChange="this.form.submit();"') .
        zen_draw_hidden_field('action', 'set_editor') .
        '</form>';
?>
          </tr>
        </table></td>
      </tr>
<?php
  if ($action == 'new') {
    $form_action = 'insert';

    $parameters = array('title' => '',
                        'content' => '',
            'content_html' => '',
                        'module' => '');

    $nInfo = new objectInfo($parameters);

    if (isset($_GET['nID'])) {
      $form_action = 'update';

      $nID = zen_db_prepare_input($_GET['nID']);


      $newsletter = $db->Execute("select title, content, content_html, module
                                  from " . TABLE_NEWSLETTERS . "
                                  where newsletters_id = '" . (int)$nID . "'");

      $nInfo->objectInfo($newsletter->fields);
    } elseif ($_POST) {
      $nInfo->objectInfo($_POST);
    }

    $file_extension = substr($PHP_SELF, strrpos($PHP_SELF, '.'));
    $directory_array = array();
    if ($dir = dir(DIR_WS_MODULES . 'newsletters/')) {
      while ($file = $dir->read()) {
        if (!is_dir(DIR_WS_MODULES . 'newsletters/' . $file)) {
          if (substr($file, strrpos($file, '.')) == $file_extension) {
            $directory_array[] = $file;
          }
        }
      }
      sort($directory_array);
      $dir->close();
    }

    for ($i=0, $n=sizeof($directory_array); $i<$n; $i++) {
      $modules_array[] = array('id' => substr($directory_array[$i], 0, strrpos($directory_array[$i], '.')), 'text' => substr($directory_array[$i], 0, strrpos($directory_array[$i], '.')));
    }
?>
      <tr>
        <td><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr><?php echo zen_draw_form('newsletter', FILENAME_NEWSLETTERS, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . 'action=' . $form_action,'post', 'onsubmit="return check_form(newsletter);"'); if ($form_action == 'update') echo zen_draw_hidden_field('newsletter_id', $nID); ?>

        <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
          <tr>
            <td class="main"><?php echo TEXT_NEWSLETTER_MODULE; ?></td>
            <td class="main"><?php echo zen_draw_pull_down_menu('module', $modules_array, $nInfo->module); ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo TEXT_NEWSLETTER_TITLE; ?></td>
            <td class="main"><?php echo zen_draw_input_field('title', $nInfo->title, 'size="50"', 'text', true, true); ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main" valign="top"><?php echo TEXT_NEWSLETTER_CONTENT_HTML; ?></td>
            <td class="main">
        <?php if (is_null($_SESSION['html_editor_preference_status'])) echo TEXT_HTML_EDITOR_NOT_DEFINED; ?>
        <?php if ($_SESSION['html_editor_preference_status']=="FCKEDITOR") {
          $oFCKeditor = new FCKeditor ;
          $oFCKeditor->Value = $nInfo->content_html ;
          $oFCKeditor->CreateFCKeditor( 'content_html', '97%', '350' ) ;  //instanceName, width, height (px or %)
          } else { // using HTMLAREA or just raw "source"
          echo zen_draw_textarea_field('content_html', 'soft', '100%', '30', $nInfo->content_html,'id="content_html"');
          } ?>
          </td>
          </tr>
          <tr>
            <td class="main" valign="top"><?php echo TEXT_NEWSLETTER_CONTENT; ?></td>
            <td class="main"><?php echo zen_draw_textarea_field('content', 'soft', '100%', '20', $nInfo->content); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
          <tr>
            <td class="main" align="right"><?php echo (($form_action == 'insert') ? zen_image_submit('button_save.gif', IMAGE_SAVE) : zen_image_submit('button_update.gif', IMAGE_UPDATE)). '&nbsp;&nbsp;<a href="' . zen_href_link_admin(FILENAME_NEWSLETTERS, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . (isset($_GET['nID']) ? 'nID=' . $_GET['nID'] : '')) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>'; ?></td>
          </tr>
        </table></td>
      </form></tr>
<?php
  } elseif ($action == 'preview') {
    $nID = zen_db_prepare_input($_GET['nID']);

    $newsletter = $db->Execute("select title, content, content_html, module
                                from " . TABLE_NEWSLETTERS . "
                                where newsletters_id = '" . (int)$nID . "'");

    $nInfo = new objectInfo($newsletter->fields);
?>
      <tr>
        <td align="right"><?php echo '<a href="' . zen_href_link_admin(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $_GET['nID']) . '">' . zen_image_button('button_back.gif', IMAGE_BACK) . '</a>'; ?></td>
      </tr>
      <tr>
        <td width="500"><hr /><strong><?php echo strip_tags(TEXT_NEWSLETTER_CONTENT_HTML); ?></strong><br /><?php echo nl2br($nInfo->content_html); ?></td>
      </tr>
      <tr>
        <td width="500"><hr /><strong><?php echo strip_tags(TEXT_NEWSLETTER_CONTENT); ?></strong><br /><tt><?php echo nl2br($nInfo->content); ?></tt><hr /></td>
      </tr>
      <tr>
        <td align="right"><?php echo '<a href="' . zen_href_link_admin(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $_GET['nID']) . '">' . zen_image_button('button_back.gif', IMAGE_BACK) . '</a>'; ?></td>
      </tr>
<?php
  } elseif ($action == 'send') {
    $nID = zen_db_prepare_input($_GET['nID']);

    $newsletter = $db->Execute("select title, content, content_html, module
                                from " . TABLE_NEWSLETTERS . "
                                where newsletters_id = '" . (int)$nID . "'");

    $nInfo = new objectInfo($newsletter->fields);

    include(DIR_WS_LANGUAGES . $gBitCustomer->getLanguage() . '/modules/newsletters/' . $nInfo->module . substr($PHP_SELF, strrpos($PHP_SELF, '.')));
    include(DIR_WS_MODULES . 'newsletters/' . $nInfo->module . substr($PHP_SELF, strrpos($PHP_SELF, '.')));
    $module_name = $nInfo->module;
    $module = new $module_name($nInfo->title, $nInfo->content, $nInfo->content_html);
?>
      <tr>
        <td><?php if ($module->show_choose_audience) { echo $module->choose_audience(); } else { echo $module->confirm(); } ?></td>
      </tr>
<?php
  } elseif ($action == 'confirm') { // show count of customers to receive messages, and preview of contents.
    $nID = zen_db_prepare_input($_GET['nID']);

    $newsletter = $db->Execute("select title, content, content_html, module
                                from " . TABLE_NEWSLETTERS . "
                                where newsletters_id = '" . (int)$nID . "'");

    $nInfo = new objectInfo($newsletter->fields);

    include(DIR_WS_LANGUAGES . $gBitCustomer->getLanguage() . '/modules/newsletters/' . $nInfo->module . substr($PHP_SELF, strrpos($PHP_SELF, '.')));
    include(DIR_WS_MODULES . 'newsletters/' . $nInfo->module . substr($PHP_SELF, strrpos($PHP_SELF, '.')));
    $module_name = $nInfo->module;
    $module = new $module_name($nInfo->title, $nInfo->content, $nInfo->content_html);
?>
      <tr>
        <td><?php echo $module->confirm(); ?></td>
      </tr>
<?php
  } elseif ($action == 'confirm_send') { // confirmed, now go ahead and send the messages
    $nID = zen_db_prepare_input($_GET['nID']);

    $newsletter = $db->Execute("select newsletters_id, title, content, content_html, module
                                from " . TABLE_NEWSLETTERS . "
                                where newsletters_id = '" . (int)$nID . "'");

    $nInfo = new objectInfo($newsletter->fields);

    include(DIR_WS_LANGUAGES . $gBitCustomer->getLanguage() . '/modules/newsletters/' . $nInfo->module . substr($PHP_SELF, strrpos($PHP_SELF, '.')));
    include(DIR_WS_MODULES . 'newsletters/' . $nInfo->module . substr($PHP_SELF, strrpos($PHP_SELF, '.')));
    $module_name = $nInfo->module;
    $module = new $module_name($nInfo->title, $nInfo->content, $nInfo->content_html, $_POST['audience_selected']);
?>
      <tr>
        <td><table border="0" cellspacing="0" cellpadding="2">
          <tr>
            <td class="main" valign="middle"><?php echo zen_image(DIR_WS_IMAGES . 'ani_send_email.gif', IMAGE_ANI_SEND_EMAIL); ?></td>
            <td class="main" valign="middle"><b><?php echo TEXT_PLEASE_WAIT; ?></b>
<?php
  zen_set_time_limit(600);
  flush();
  $i = $module->send($nInfo->newsletters_id);
?>
      </td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo zen_draw_separator('pixel_trans.gif', '15', '10'); ?></td>
      </tr>
      <tr>
        <td class="main"><font color="#ff0000"><b><h1><?php echo TEXT_FINISHED_SENDING_EMAILS; ?></h1></b></font></td>
      </tr>
      <tr>
        <td class="main"><font color="#ff0000"><?php echo sprintf(TEXT_AFTER_EMAIL_INSTRUCTIONS,$i); ?></font></td>
      </tr>
      <tr>
        <td><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td align="center"><?php echo '<a href="' . zen_href_link_admin(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $_GET['nID']) . '">' . zen_image_button('button_back.gif', IMAGE_BACK) . '</a>'; ?></td>
      </tr>
<?php
  } else {
?>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_NEWSLETTERS; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_SIZE; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_MODULE; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_SENT; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_STATUS; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php
    $newsletters_query_raw = "select `newsletters_id`, `title`, length(`content`) as `content_length`, length(`content_html`) as `content_html_length`, `module`, `date_added`, `date_sent`, `status`, `locked` from " . TABLE_NEWSLETTERS . " order by `date_added` desc";
    $newsletters_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS, $newsletters_query_raw, $newsletters_query_numrows);
    $newsletters = $db->Execute($newsletters_query_raw);
    while (!$newsletters->EOF) {
    if ((!isset($_GET['nID']) || (isset($_GET['nID']) && ($_GET['nID'] == $newsletters->fields['newsletters_id']))) && !isset($nInfo) && (substr($action, 0, 3) != 'new')) {
        $nInfo = new objectInfo($newsletters->fields);
      }

      if (isset($nInfo) && is_object($nInfo) && ($newsletters->fields['newsletters_id'] == $nInfo->newsletters_id) ) {
        echo '                  <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . zen_href_link_admin(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $nInfo->newsletters_id . '&action=preview') . '\'">' . "\n";
      } else {
        echo '                  <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . zen_href_link_admin(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $newsletters->fields['newsletters_id']) . '\'">' . "\n";
      }
?>
                <td class="dataTableContent"><?php echo '<a href="' . zen_href_link_admin(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $newsletters->fields['newsletters_id'] . '&action=preview') . '">' . zen_image(DIR_WS_ICONS . 'preview.gif', ICON_PREVIEW) . '</a>&nbsp;' . $newsletters->fields['title']; ?></td>
                <td class="dataTableContent" align="right"><?php echo number_format($newsletters->fields['content_length']+$newsletters->fields['content_html_length']) . ' bytes'; ?></td>
                <td class="dataTableContent" align="right"><?php echo $newsletters->fields['module']; ?></td>
                <td class="dataTableContent" align="center"><?php if ($newsletters->fields['status'] == '1') { echo zen_image(DIR_WS_ICONS . 'tick.gif', ICON_TICK); } else { echo zen_image(DIR_WS_ICONS . 'cross.gif', ICON_CROSS); } ?></td>
                <td class="dataTableContent" align="center"><?php if ($newsletters->fields['locked'] > 0) { echo zen_image(DIR_WS_ICONS . 'locked.gif', ICON_LOCKED); } else { echo zen_image(DIR_WS_ICONS . 'unlocked.gif', ICON_UNLOCKED); } ?></td>
                <td class="dataTableContent" align="right"><?php if (isset($nInfo) && is_object($nInfo) && ($newsletters->fields['newsletters_id'] == $nInfo->newsletters_id) ) { echo zen_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); }
                                  else { echo '<a href="' . zen_href_link_admin(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $newsletters->fields['newsletters_id']) . '">' . zen_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
      $newsletters->MoveNext();
    }
?>
              <tr>
                <td colspan="6"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText" valign="top"><?php echo $newsletters_split->display_count($newsletters_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_NEWSLETTERS); ?></td>
                    <td class="smallText" align="right"><?php echo $newsletters_split->display_links($newsletters_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?></td>
                  </tr>
                  <tr>
                    <td align="right" colspan="2"><?php echo '<a href="' . zen_href_link_admin(FILENAME_NEWSLETTERS, 'action=new') . '">' . zen_image_button('button_new_newsletter.gif', IMAGE_NEW_NEWSLETTER) . '</a>'; ?></td>
                  </tr>
                </table></td>
              </tr>
            </table></td>
<?php
  $heading = array();
  $contents = array();

  switch ($action) {
    case 'delete':
      $heading[] = array('text' => '<b>' . $nInfo->title . '</b>');

      $contents = array('form' => zen_draw_form('newsletters', FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $nInfo->newsletters_id . '&action=deleteconfirm'));
      $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
      $contents[] = array('text' => '<br /><b>' . $nInfo->title . '</b>');
      $contents[] = array('align' => 'center', 'text' => '<br />' . zen_image_submit('button_delete.gif', IMAGE_DELETE) . ' <a href="' . zen_href_link_admin(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $_GET['nID']) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    default:
      if (is_object($nInfo)) {
        $heading[] = array('text' => '<b>' . $nInfo->title . '</b>');

        if ($nInfo->locked > 0) {
          $contents[] = array('align' => 'center', 'text' => '<a href="' . zen_href_link_admin(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $nInfo->newsletters_id . '&action=new') . '">' . zen_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . zen_href_link_admin(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $nInfo->newsletters_id . '&action=delete') . '">' . zen_image_button('button_delete.gif', IMAGE_DELETE) . '</a> <a href="' . zen_href_link_admin(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $nInfo->newsletters_id . '&action=preview') . '">' . zen_image_button('button_preview.gif', IMAGE_PREVIEW) . '</a> <a href="' . zen_href_link_admin(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $nInfo->newsletters_id . '&action=send') . '">' . zen_image_button('button_send.gif', IMAGE_SEND) . '</a> <a href="' . zen_href_link_admin(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $nInfo->newsletters_id . '&action=unlock') . '">' . zen_image_button('button_unlock.gif', IMAGE_UNLOCK) . '</a>');
        } else {
          $contents[] = array('align' => 'center', 'text' => '<a href="' . zen_href_link_admin(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $nInfo->newsletters_id . '&action=preview') . '">' . zen_image_button('button_preview.gif', IMAGE_PREVIEW) . '</a> <a href="' . zen_href_link_admin(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $nInfo->newsletters_id . '&action=lock') . '">' . zen_image_button('button_lock.gif', IMAGE_LOCK) . '</a>');
        }
        $contents[] = array('text' => '<br />' . TEXT_NEWSLETTER_DATE_ADDED . ' ' . zen_date_short($nInfo->date_added));
        if ($nInfo->status == '1') $contents[] = array('text' => TEXT_NEWSLETTER_DATE_SENT . ' ' . zen_date_short($nInfo->date_sent));
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
<?php
  }
?>
    </table></td>
<!-- body_text_eof //-->
  </tr>
</table>
<!-- body_eof //-->

<!-- footer //-->
<?php require(DIR_FS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
<br />
</body>
</html>
<?php require(DIR_FS_INCLUDES . 'application_bottom.php'); ?>
