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
//  $Id: coupon_admin.php,v 1.11 2006/01/12 19:35:26 spiderr Exp $
//

  require('includes/application_top.php');

  $currencies = new currencies();

  if ($_GET['selected_box']) {
    $_GET['action']='';
    $_GET['old_action']='';
  }

  if (($_GET['action'] == 'send_email_to_user') && ($_POST['customers_email_address']) && (!$_POST['back_x'])) {
	$audience_select = get_audience_sql_query($_POST['customers_email_address'], 'email');
        $mail = $db->Execute($audience_select['query_string']);
        $mail_sent_to = $audience_select['query_name'];
      if ($_POST['email_to']) {
        $mail_sent_to = $_POST['email_to'];
        }

    $coupon_result = $db->Execute("select `coupon_code`
                                   from " . TABLE_COUPONS . "
                                   where `coupon_id` = '" . $_GET['cid'] . "'");

    $coupon_name = $db->Execute("select `coupon_name`, `coupon_description`
                                 from " . TABLE_COUPONS_DESCRIPTION . "
                                 where `coupon_id` = '" . $_GET['cid'] . "'
                                 and `language_id` = '" . $_SESSION['languages_id'] . "'");

    // demo active test
    if (zen_admin_demo()) {
      $_GET['action']= '';
      $messageStack->add_session(ERROR_ADMIN_DEMO, 'caution');
      zen_redirect(zen_href_link_admin(FILENAME_COUPON_ADMIN, 'mail_sent_to=' . urlencode($mail_sent_to)));
    }
    $from = zen_db_prepare_input($_POST['from']);
    $subject = zen_db_prepare_input($_POST['subject']);
    $recip_count=0;
    while (!$mail->EOF) {
      $message = zen_db_prepare_input($_POST['message']);
      $message .= "\n\n" . TEXT_TO_REDEEM . "\n\n";
      $message .= TEXT_VOUCHER_IS . $coupon_result->fields['coupon_code'] . "\n\n";
      $message .= TEXT_REMEMBER . "\n\n";
      $message .= sprintf(TEXT_VISIT ,HTTP_CATALOG_SERVER . DIR_WS_CATALOG);

      // disclaimer
      $message .= "\n-----\n" . sprintf(EMAIL_DISCLAIMER, STORE_OWNER_EMAIL_ADDRESS) . "\n\n";

      $html_msg['EMAIL_FIRST_NAME'] = $mail->fields['customers_firstname'];
      $html_msg['EMAIL_LAST_NAME']  = $mail->fields['customers_lastname'];
      $html_msg['EMAIL_MESSAGE_HTML'] = zen_db_prepare_input($_POST['message_html']);
      $html_msg['COUPON_TEXT_TO_REDEEM'] = TEXT_TO_REDEEM;
      $html_msg['COUPON_TEXT_VOUCHER_IS'] = TEXT_VOUCHER_IS;
      $html_msg['COUPON_CODE'] = $coupon_result->fields['coupon_code'];
      $html_msg['COUPON_DESCRIPTION'] =(!empty($coupon_name->fields['coupon_description']) ? $coupon_name->fields['coupon_description'] : '');
	  $html_msg['COUPON_TEXT_REMEMBER']  = TEXT_REMEMBER;
      $html_msg['COUPON_REDEEM_STORENAME_URL'] = sprintf(TEXT_VISIT ,'<a href="'.HTTP_CATALOG_SERVER . DIR_WS_CATALOG.'">'.STORE_NAME.'</a>');

//Send the emails
      zen_mail($mail->fields['customers_firstname'] . ' ' . $mail->fields['customers_lastname'], $mail->fields['customers_email_address'], $subject , $message, '',$from, $html_msg, 'coupon');

      $recip_count++;
      $mail->MoveNext();
    }
    // send one to Admin if enabled
    if (SEND_EXTRA_DISCOUNT_COUPON_ADMIN_EMAILS_TO_STATUS== '1' and SEND_EXTRA_DISCOUNT_COUPON_ADMIN_EMAILS_TO != '') {
      zen_mail('', SEND_EXTRA_DISCOUNT_COUPON_ADMIN_EMAILS_TO, SEND_EXTRA_DISCOUNT_COUPON_ADMIN_EMAILS_TO_SUBJECT . ' ' . $subject , $message, '',$from, $html_msg, 'coupon_extra');
    }
    zen_redirect(zen_href_link_admin(FILENAME_COUPON_ADMIN, 'mail_sent_to=' . urlencode($mail_sent_to) . '&recip_count='. $recip_count ));
  }

  if ( ($_GET['action'] == 'preview_email') && (!$_POST['customers_email_address']) ) {
    $_GET['action'] = 'email';
    $messageStack->add(ERROR_NO_CUSTOMER_SELECTED, 'error');
  }

  if ($_GET['mail_sent_to']) {
    $messageStack->add(sprintf(NOTICE_EMAIL_SENT_TO, $_GET['mail_sent_to']. '(' . $_GET['recip_count'] . ')'), 'success');
    $_GET['mail_sent_to'] = '';
  }

  switch ($_GET['action']) {
      case 'set_editor':
        if ($_GET['reset_editor'] == '0') {
          $_SESSION['html_editor_preference_status'] = 'NONE';
        } else {
          $_SESSION['html_editor_preference_status'] = 'HTMLAREA';
        }
        $action='';
        zen_redirect(zen_href_link_admin(FILENAME_COUPON_ADMIN));
        break;
    case 'confirmdelete':
      // demo active test
      if (zen_admin_demo()) {
        $_GET['action']= '';
        $messageStack->add_session(ERROR_ADMIN_DEMO, 'caution');
        zen_redirect(zen_href_link_admin(FILENAME_COUPON_ADMIN));
      }
      $db->Execute("update " . TABLE_COUPONS . "
                    set coupon_active = 'N'
                    where coupon_id='".$_GET['cid']."'");
      break;
    case 'update':
      $update_errors = 0;
      // get all HTTP_POST_VARS and validate
      $_POST['coupon_code'] = trim($_POST['coupon_code']);
        $languages = zen_get_languages();
        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
          $language_id = $languages[$i]['id'];
          $_POST['coupon_name'][$language_id] = trim($_POST['coupon_name'][$language_id]);
          if (!$_POST['coupon_name'][$language_id]) {
            $update_errors = 1;
            $messageStack->add(ERROR_NO_COUPON_NAME . $languages[$i]['name'], 'error');
          }
          $_POST['coupon_desc'][$language_id] = trim($_POST['coupon_desc'][$language_id]);
        }
      $_POST['coupon_amount'] = trim($_POST['coupon_amount']);
      if (!$_POST['coupon_name']) {
        $update_errors = 1;
        $messageStack->add(ERROR_NO_COUPON_NAME, 'error');
      }
      if ((!$_POST['coupon_amount']) && (!$_POST['coupon_free_ship'])) {
        $update_errors = 1;
        $messageStack->add(ERROR_NO_COUPON_AMOUNT, 'error');
      }
      if (!$_POST['coupon_code']) {
        $coupon_code = create_coupon_code();
      }
      if ($_POST['coupon_code']) $coupon_code = $_POST['coupon_code'];
      $query1 = $db->Execute("select coupon_code
                              from " . TABLE_COUPONS . "
                              where coupon_code = '" . zen_db_prepare_input($coupon_code) . "'");

      if ($query1->RecordCount()>0 && $_POST['coupon_code'] && $_GET['oldaction'] != 'voucheredit')  {
        $update_errors = 1;
        $messageStack->add(ERROR_COUPON_EXISTS, 'error');
      }
      if ($update_errors != 0) {
        $_GET['action'] = 'new';
      } else {
        $_GET['action'] = 'update_preview';
      }
      break;
    case 'update_confirm':
      if ( ($_POST['back_x']) || ($_POST['back_y']) ) {
        $_GET['action'] = 'new';
      } else {
        if ($_POST['coupon_free_ship']) {
        	$coupon_type = 'S';
        } elseif (substr($_POST['coupon_amount'], -1) == '%') {
        	$_POST['coupon_amount'] = str_replace( '%', '', $_POST['coupon_amount'] );
        	$coupon_type='P';
        } else {
	        $coupon_type = "F";
        }

        $sql_data_array = array('coupon_code' => zen_db_prepare_input($_POST['coupon_code']),
                                'coupon_amount' => zen_db_prepare_input($_POST['coupon_amount']),
                                'coupon_type' => zen_db_prepare_input($coupon_type),
                                'uses_per_coupon' => zen_db_prepare_input($_POST['coupon_uses_coupon']),
                                'uses_per_user' => zen_db_prepare_input($_POST['coupon_uses_user']),
                                'coupon_minimum_order' => zen_db_prepare_input($_POST['coupon_min_order']),
                                'restrict_to_products' => zen_db_prepare_input($_POST['coupon_products']),
                                'restrict_to_categories' => zen_db_prepare_input($_POST['coupon_categories']),
                                'coupon_start_date' => $_POST['coupon_startdate'],
                                'coupon_expire_date' => $_POST['coupon_finishdate'],
                                'date_created' => $db->NOW(),
                                'date_modified' => $db->NOW());
        $languages = zen_get_languages();
        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
          $language_id = $languages[$i]['id'];
          $sql_data_marray[$i] = array('coupon_name' => zen_db_prepare_input($_POST['coupon_name'][$language_id]),
                                 'coupon_description' => zen_db_prepare_input($_POST['coupon_desc'][$language_id])
                                 );
        }
        if ($_GET['oldaction']=='voucheredit') {
          $db->associateUpdate( TABLE_COUPONS, $sql_data_array, array( 'name'=>"coupon_id", 'value'=>$_GET['cid'] ) );
          for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
            $language_id = $languages[$i]['id'];
            $db->Execute("update " . TABLE_COUPONS_DESCRIPTION . "
                          set `coupon_name` = '" . zen_db_prepare_input($_POST['coupon_name'][$language_id]) . "',
                          `coupon_description` = '" . zen_db_prepare_input($_POST['coupon_desc'][$language_id]) . "'
                          where `coupon_id` = '" . $_GET['cid'] . "'
                          and `language_id` = '" . $language_id . "'");
          }
        } else {
          $db->associateInsert(TABLE_COUPONS, $sql_data_array);
          $insert_id = zen_db_insert_id( TABLE_COUPONS, 'coupon_id' );
          $cid = $insert_id;
          $_GET['cid'] = $cid;

          for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
            $language_id = $languages[$i]['id'];
            $sql_data_marray[$i]['coupon_id'] = $insert_id;
            $sql_data_marray[$i]['language_id'] = $language_id;
            $db->associateInsert(TABLE_COUPONS_DESCRIPTION, $sql_data_marray[$i]);
          }
        }
      }
      zen_redirect(zen_href_link_admin(FILENAME_COUPON_ADMIN, 'cid=' . $_GET['cid']));
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
<link rel="stylesheet" type="text/css" href="includes/javascript/spiffyCal/spiffyCal_v2_1.css">
<script language="JavaScript" src="includes/javascript/spiffyCal/spiffyCal_v2_1.js"></script>
<script language="javascript">
  var dateAvailable = new ctlSpiffyCalendarBox("dateAvailable", "new_product", "products_date_available","btnDate1","<?php echo $pInfo->products_date_available; ?>",scBTNMODE_CUSTOMBLUE);
</script>
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
  if (typeof _editor_url == "string") HTMLArea.replace('message_html');
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
  if (form.elements['message'] && form.elements['message_html']) {
    var field_value1 = form.elements['message'].value;
    var field_value2 = form.elements['message_html'].value;

    if ((field_value1 == '' || field_value1.length < 3) && (field_value2 == '' || field_value2.length < 3)) {
      error_message = error_message + "* " + msg + "\n";
      error = true;
    }
  }
}
function check_input(field_name, field_size, message) {
  if (form.elements[field_name] && (form.elements[field_name].type != "hidden")) {
    var field_value = form.elements[field_name].value;

    if (field_value == '' || field_value.length < field_size) {
      error_message = error_message + "* " + message + "\n";
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

  check_select('customers_email_address', '', "<?php echo ERROR_NO_CUSTOMER_SELECTED; ?>");
  check_message("<?php echo ENTRY_NOTHING_TO_SEND; ?>");
  check_input('subject','',"<?php echo ERROR_NO_SUBJECT; ?>");

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
<?php require(DIR_FS_ADMIN_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->
<table border="0" width="100%" cellspacing="2" cellpadding="2">
  <tr>
<!-- body_text //-->
<?php
  switch ($_GET['action']) {
  case 'voucherreport':
?>
      <td width="100%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="0">
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
                <td class="dataTableHeadingContent"><?php echo CUSTOMER_ID; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo CUSTOMER_NAME; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo IP_ADDRESS; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo REDEEM_DATE; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php
    $cc_query_raw = "select * from " . TABLE_COUPON_REDEEM_TRACK . " where coupon_id = '" . $_GET['cid'] . "'";
    $cc_split = new splitPageResults($_GET['reports_page'], MAX_DISPLAY_SEARCH_RESULTS_DISCOUNT_COUPONS_REPORTS, $cc_query_raw, $cc_query_numrows);
    $cc_list = $db->Execute($cc_query_raw);
    while (!$cc_list->EOF) {
      $rows++;
      if (strlen($rows) < 2) {
        $rows = '0' . $rows;
      }
      if (((!$_GET['uid']) || (@$_GET['uid'] == $cc_list->fields['unique_id'])) && (!$cInfo)) {
        $cInfo = new objectInfo($cc_list->fields);
      }
      if ( (is_object($cInfo)) && ($cc_list->fields['unique_id'] == $cInfo->unique_id) ) {
        echo '          <tr class="dataTableRowSelected" onmouseover="this.style.cursor=\'hand\'" onclick="document.location.href=\'' . zen_href_link_admin(FILENAME_COUPON_ADMIN, zen_get_all_get_params(array('cid', 'action', 'uid')) . 'cid=' . $cInfo->coupon_id . '&action=voucherreport&uid=' . $cinfo->unique_id) . '\'">' . "\n";
      } else {
        echo '          <tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';this.style.cursor=\'hand\'" onmouseout="this.className=\'dataTableRow\'" onclick="document.location.href=\'' . zen_href_link_admin(FILENAME_COUPON_ADMIN, zen_get_all_get_params(array('cid', 'action', 'uid')) . 'cid=' . $cc_list->fields['coupon_id'] . '&action=voucherreport&uid=' . $cc_list->fields['unique_id']) . '\'">' . "\n";
      }
$customer = $db->Execute("select `customers_firstname`, `customers_lastname`
                          from " . TABLE_CUSTOMERS . "
                          where `customers_id` = '" . $cc_list->fields['customer_id'] . "'");

?>
                <td class="dataTableContent"><?php echo $cc_list->fields['customer_id']; ?></td>
                <td class="dataTableContent" align="center"><?php echo $customer->fields['customers_firstname'] . ' ' . $customer->fields['customers_lastname']; ?></td>
                <td class="dataTableContent" align="center"><?php echo $cc_list->fields['redeem_ip']; ?></td>
                <td class="dataTableContent" align="center"><?php echo zen_date_short($cc_list->fields['redeem_date']); ?></td>
                <td class="dataTableContent" align="right"><?php if ( (is_object($cInfo)) && ($cc_list->fields['unique_id'] == $cInfo->unique_id) ) { echo zen_image(DIR_WS_IMAGES . 'icon_arrow_right.gif'); } else { echo '<a href="' . zen_href_link_admin(FILENAME_COUPON_ADMIN, 'reports_page=' . $_GET['reports_page'] . '&cid=' . $cc_list->fields['coupon_id']) . '">' . zen_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
      $cc_list->MoveNext();
    }
?>
          <tr>
            <td colspan="5"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr>
                <td class="smallText">&nbsp;<?php echo $cc_split->display_count($cc_query_numrows, MAX_DISPLAY_SEARCH_RESULTS_DISCOUNT_COUPONS_REPORTS, $_GET['reports_page'], TEXT_DISPLAY_NUMBER_OF_COUPONS); ?>&nbsp;</td>
                <td align="right" class="smallText">&nbsp;<?php echo $cc_split->display_links($cc_query_numrows, MAX_DISPLAY_SEARCH_RESULTS_DISCOUNT_COUPONS_REPORTS, MAX_DISPLAY_PAGE_LINKS, $_GET['reports_page'], 'action=voucherreport&cid=' . $cInfo->coupon_id, 'reports_page'); ?>&nbsp;</td>
              </tr>

              <tr>
                <td align="right" colspan="2" class="smallText"><?php echo '<a href="' . zen_href_link_admin(FILENAME_COUPON_ADMIN, 'page=' . $_GET['page'] . '&cid=' . $cInfo->coupon_id) . '">' . zen_image_button('button_back.gif', IMAGE_BACK) . '</a>'; ?></td>
              </tr>
            </table></td>
          </tr>
             </table></td>
<?php
    $heading = array();
    $contents = array();
      $coupon_desc = $db->Execute("select `coupon_name`
                                   from " . TABLE_COUPONS_DESCRIPTION . "
                                   where `coupon_id` = '" . $_GET['cid'] . "'
                                   and `language_id` = '" . $_SESSION['languages_id'] . "'");
      $count_customers = $db->Execute("select * from " . TABLE_COUPON_REDEEM_TRACK . "
                                       where `coupon_id` = '" . $_GET['cid'] . "'
                                       and `customer_id` = '" . $cInfo->customer_id . "'");

      $heading[] = array('text' => '<b>[' . $_GET['cid'] . ']' . COUPON_NAME . ' ' . $coupon_desc->fields['coupon_name'] . '</b>');
      $contents[] = array('text' => '<b>' . TEXT_REDEMPTIONS . '</b>');
//      $contents[] = array('text' => TEXT_REDEMPTIONS_TOTAL . '=' . $cc_list->RecordCount());
      $contents[] = array('text' => TEXT_REDEMPTIONS_TOTAL . '=' . $cc_query_numrows);
      $contents[] = array('text' => TEXT_REDEMPTIONS_CUSTOMER . '=' . $count_customers->RecordCount());
      $contents[] = array('text' => '');
?>
    <td width="25%" valign="top">
<?php
      $box = new box;
      echo $box->infoBox($heading, $contents);
      echo '            </td>' . "\n";
?>
<?php
    break;
  case 'preview_email':
    $coupon_result = $db->Execute("select `coupon_code`
                                   from " .TABLE_COUPONS . "
                                   where `coupon_id` = '" . $_GET['cid'] . "'");

    $coupon_name = $db->Execute("select `coupon_name`
                                 from " . TABLE_COUPONS_DESCRIPTION . "
                                 where `coupon_id` = '" . $_GET['cid'] . "'
                                 and `language_id` = '" . $_SESSION['languages_id'] . "'");

	$audience_select = get_audience_sql_query($_POST['customers_email_address']);
    $mail_sent_to = $audience_select['query_name'];

?>
      <td width="100%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="0">
      <tr>
        <td width="100%"><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
            <td class="pageHeading" align="right"><?php echo zen_draw_separator('pixel_trans.gif', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
          <tr><?php echo zen_draw_form('mail', FILENAME_COUPON_ADMIN, 'action=send_email_to_user&cid=' . $_GET['cid']); ?>
            <td><table border="0" width="100%" cellpadding="0" cellspacing="2">
              <tr>
                <td><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
              </tr>
              <tr>
                <td class="smallText"><b><?php echo TEXT_CUSTOMER; ?></b><br /><?php echo $mail_sent_to; ?></td>
              </tr>
              <tr>
                <td><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
              </tr>
              <tr>
                <td class="smallText"><b><?php echo TEXT_COUPON; ?></b><br /><?php echo $coupon_name->fields['coupon_name']; ?></td>
              </tr>
              <tr>
                <td><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
              </tr>
              <tr>
                <td class="smallText"><b><?php echo TEXT_FROM; ?></b><br /><?php echo htmlspecialchars(stripslashes($_POST['from'])); ?></td>
              </tr>
              <tr>
                <td><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
              </tr>
              <tr>
                <td class="smallText"><b><?php echo TEXT_SUBJECT; ?></b><br /><?php echo htmlspecialchars(stripslashes($_POST['subject'])); ?></td>
              </tr>
              <tr>
                <td><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
              </tr>
              <tr>
                <td><hr /><b><?php echo TEXT_RICH_TEXT_MESSAGE; ?></b><br /><?php echo stripslashes($_POST['message_html']); ?></td>
              </tr>
              <tr>
                <td ><hr /><b><?php echo TEXT_MESSAGE; ?></b><br /><tt><?php echo nl2br(htmlspecialchars(stripslashes($_POST['message']))); ?></tt><hr /></td>
              </tr>
              <tr>
                <td><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
              </tr>
              <tr>
                <td>
<?php
/* Re-Post all POST'ed variables */
    reset($_POST);
    while (list($key, $value) = each($_POST)) {
      if (!is_array($_POST[$key])) {
        echo zen_draw_hidden_field($key, htmlspecialchars(stripslashes($value)));
      }
    }
?>
                <table border="0" width="100%" cellpadding="0" cellspacing="2">
                  <tr>
                    <td><?php ?>&nbsp;</td>
                    <td align="right"><?php echo '<a href="' . zen_href_link_admin(FILENAME_COUPON_ADMIN) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a> ' . zen_image_submit('button_send_mail.gif', IMAGE_SEND_EMAIL); ?></td>
                  </tr>
                </table></td>
              </tr>
            </table></td>
          </form></tr>
<?php
    break;
  case 'email':
    $coupon_result = $db->Execute("select `coupon_code`
                                   from " . TABLE_COUPONS . "
                                   where `coupon_id` = '" . $_GET['cid'] . "'");
    $coupon_name = $db->Execute("select `coupon_name`
                                 from " . TABLE_COUPONS_DESCRIPTION . "
                                 where `coupon_id` = '" . $_GET['cid'] . "'
                                 and `language_id` = '" . $_SESSION['languages_id'] . "'");
?>
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
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
          <tr><?php echo zen_draw_form('mail', FILENAME_COUPON_ADMIN, 'action=preview_email&cid='. $_GET['cid'],'post', 'onsubmit="return check_form(mail);"'); ?>
            <td><table border="0" width="100%" cellpadding="0" cellspacing="2">
              <tr>
                <td colspan="2"><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
              </tr>
              <tr>
                <td class="main"><?php echo TEXT_COUPON; ?>&nbsp;&nbsp;</td>
                <td><?php echo $coupon_name->fields['coupon_name']; ?></td>
              </tr>
              <tr>
                <td colspan="2"><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
              </tr>
<?php
    $customers = get_audiences_list('email');
?>
              <tr>
                <td class="main"><?php echo TEXT_CUSTOMER; ?>&nbsp;&nbsp;</td>
                <td><?php echo zen_draw_pull_down_menu('customers_email_address', $customers, $_GET['customer']);?></td>
              </tr>
              <tr>
                <td colspan="2"><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
              </tr>
              <tr>
                <td class="main"><?php echo TEXT_FROM; ?>&nbsp;&nbsp;</td>
                <td><?php echo zen_draw_input_field('from', EMAIL_FROM, 'size="50"'); ?></td>
              </tr>
              <tr>
                <td colspan="2"><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
              </tr>
<?php
/*
              <tr>
                <td class="main"><?php echo TEXT_RESTRICT; ?>&nbsp;&nbsp;</td>
                <td><?php echo zen_draw_checkbox_field('customers_restrict', $customers_restrict);?></td>
              </tr>
              <tr>
                <td colspan="2"><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
              </tr>
*/
?>
              <tr>
                <td class="main"><?php echo TEXT_SUBJECT; ?>&nbsp;&nbsp;</td>
                <td><?php echo zen_draw_input_field('subject', '', 'size="50"'); ?></td>
              </tr>
              <tr>
                <td colspan="2"><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
              </tr>
              <tr>
                <td valign="top" class="main"><?php echo TEXT_RICH_TEXT_MESSAGE; ?></td>
                <td>
				<?php if (is_null($_SESSION['html_editor_preference_status'])) echo TEXT_HTML_EDITOR_NOT_DEFINED; ?>
				<?php if (EMAIL_USE_HTML != 'true') echo TEXT_WARNING_HTML_DISABLED; ?>
				<?php if ($_SESSION['html_editor_preference_status']=="FCKEDITOR") {
					$oFCKeditor = new FCKeditor ;
					$oFCKeditor->Value = ($_POST['message_html']=='') ? TEXT_COUPON_ANNOUNCE : stripslashes($_POST['message_html']) ;
					$oFCKeditor->CreateFCKeditor( 'message_html', '97%', '250' ) ;  //instanceName, width, height (px or %)
					} else { // using HTMLAREA or just raw "source"
  if (EMAIL_USE_HTML == 'true') {
					echo zen_draw_textarea_field('message_html', 'soft', '100%', '20', ($_POST['message_html']=='') ? TEXT_COUPON_ANNOUNCE : stripslashes($_POST['message_html']), 'id="message_html"');
}
					} ?>
				</td>
              </tr>
              <tr>
                <td valign="top" class="main"><?php echo TEXT_MESSAGE; ?>&nbsp;&nbsp;</td>
                <td><?php echo zen_draw_textarea_field('message', 'soft', '60', '15', strip_tags(($_POST['message_html']=='') ? TEXT_COUPON_ANNOUNCE : stripslashes($_POST['message_html']))); ?></td>
              </tr>
              <tr>
                <td colspan="2"><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
              </tr>
              <tr>
                <td colspan="2" align="right"><?php echo '<a href="' . zen_href_link_admin(FILENAME_COUPON_ADMIN) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a> ' .  zen_image_submit('button_send_mail.gif', IMAGE_SEND_EMAIL); ?></td>
              </tr>
            </table></td>
          </form></tr>
	</table></td>
<?php
    break;
  case 'update_preview':
?>
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
      <td>
<?php echo zen_draw_form('coupon', FILENAME_COUPON_ADMIN, 'action=update_confirm&oldaction=' . $_GET['oldaction'] . '&cid=' . $_GET['cid']); ?>
      <table border="0" width="100%" cellspacing="0" cellpadding="6">
<?php
        $languages = zen_get_languages();
        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
            $language_id = $languages[$i]['id'];
?>
      <tr>
        <td align="left"><?php echo COUPON_NAME; ?></td>
        <td align="left"><?php echo zen_db_prepare_input($_POST['coupon_name'][$language_id]); ?></td>
      </tr>
<?php
}
?>
<?php
        $languages = zen_get_languages();
        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
            $language_id = $languages[$i]['id'];
?>
      <tr>
        <td align="left"><?php echo COUPON_DESC; ?></td>
        <td align="left"><?php echo zen_db_prepare_input($_POST['coupon_desc'][$language_id]); ?></td>
      </tr>
<?php
}
?>
      <tr>
        <td align="left"><?php echo COUPON_AMOUNT; ?></td>
        <td align="left"><?php echo zen_db_prepare_input($_POST['coupon_amount']); ?></td>
      </tr>

      <tr>
        <td align="left"><?php echo COUPON_MIN_ORDER; ?></td>
        <td align="left"><?php echo zen_db_prepare_input($_POST['coupon_min_order']); ?></td>
      </tr>

      <tr>
        <td align="left"><?php echo COUPON_FREE_SHIP; ?></td>
<?php
    if ($_POST['coupon_free_ship']) {
?>
        <td align="left"><?php echo TEXT_FREE_SHIPPING; ?></td>
<?php
    } else {
?>
        <td align="left"><?php echo TEXT_NO_FREE_SHIPPING; ?></td>
<?php
    }
?>
      </tr>
      <tr>
        <td align="left"><?php echo COUPON_CODE; ?></td>
<?php
    if ($_POST['coupon_code']) {
      $c_code = $_POST['coupon_code'];
    } else {
      $c_code = $coupon_code;
    }
?>
        <td align="left"><?php echo $coupon_code; ?></td>
      </tr>

      <tr>
        <td align="left"><?php echo COUPON_USES_COUPON; ?></td>
        <td align="left"><?php echo $_POST['coupon_uses_coupon']; ?></td>
      </tr>

      <tr>
        <td align="left"><?php echo COUPON_USES_USER; ?></td>
        <td align="left"><?php echo $_POST['coupon_uses_user']; ?></td>
      </tr>

      <tr>
        <td align="left"><?php echo COUPON_STARTDATE; ?></td>
<?php
    $start_date = date(DATE_FORMAT, mktime(0, 0, 0, $_POST['coupon_startdate_month'],$_POST['coupon_startdate_day'] ,$_POST['coupon_startdate_year'] ));
?>
        <td align="left"><?php echo $start_date; ?></td>
      </tr>

      <tr>
        <td align="left"><?php echo COUPON_FINISHDATE; ?></td>
<?php
    $finish_date = date(DATE_FORMAT, mktime(0, 0, 0, $_POST['coupon_finishdate_month'],$_POST['coupon_finishdate_day'] ,$_POST['coupon_finishdate_year'] ));
?>
        <td align="left"><?php echo $finish_date; ?></td>
      </tr>
<?php
        $languages = zen_get_languages();
        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
          $language_id = $languages[$i]['id'];
          echo zen_draw_hidden_field('coupon_name[' . $languages[$i]['id'] . ']', stripslashes($_POST['coupon_name'][$language_id]));
          echo zen_draw_hidden_field('coupon_desc[' . $languages[$i]['id'] . ']', stripslashes($_POST['coupon_desc'][$language_id]));
       }
    echo zen_draw_hidden_field('coupon_amount', $_POST['coupon_amount']);
    echo zen_draw_hidden_field('coupon_min_order', $_POST['coupon_min_order']);
    echo zen_draw_hidden_field('coupon_free_ship', $_POST['coupon_free_ship']);
    echo zen_draw_hidden_field('coupon_code', stripslashes($c_code));
    echo zen_draw_hidden_field('coupon_uses_coupon', $_POST['coupon_uses_coupon']);
    echo zen_draw_hidden_field('coupon_uses_user', $_POST['coupon_uses_user']);
    echo zen_draw_hidden_field('coupon_products', $_POST['coupon_products']);
    echo zen_draw_hidden_field('coupon_categories', $_POST['coupon_categories']);
    echo zen_draw_hidden_field('coupon_startdate', date('Y-m-d', mktime(0, 0, 0, $_POST['coupon_startdate_month'],$_POST['coupon_startdate_day'] ,$_POST['coupon_startdate_year'] )));
    echo zen_draw_hidden_field('coupon_finishdate', date('Y-m-d', mktime(0, 0, 0, $_POST['coupon_finishdate_month'],$_POST['coupon_finishdate_day'] ,$_POST['coupon_finishdate_year'] )));
?>
     <tr>
        <td align="left"><?php echo zen_image_submit('button_confirm.gif',COUPON_BUTTON_CONFIRM); ?></td>
        <td align="left"><?php echo zen_image_submit('button_back.gif',COUPON_BUTTON_BACK, 'name=back'); ?></td>
      </td>
      </tr>

      </td></table></form>
      </tr>

      </table></td>
<?php

    break;
  case 'voucheredit':
    $languages = zen_get_languages();
    for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
      $language_id = $languages[$i]['id'];
      $coupon = $db->Execute("select `coupon_name`, `coupon_description`
                              from " . TABLE_COUPONS_DESCRIPTION . "
                              where `coupon_id` = '" .  $_GET['cid'] . "'
                              and `language_id` = '" . $language_id . "'");

      $_POST['coupon_name'][$language_id] = $coupon->fields['coupon_name'];
      $_POST['coupon_desc'][$language_id] = $coupon->fields['coupon_description'];
    }

    $coupon = $db->Execute("select `coupon_code`, `coupon_amount`, `coupon_type`, `coupon_minimum_order`,
                                   `coupon_start_date`, `coupon_expire_date`, `uses_per_coupon`,
                                   `uses_per_user`, `restrict_to_products`, `restrict_to_categories`
                            from " . TABLE_COUPONS . "
                            where `coupon_id` = '" . $_GET['cid'] . "'");

    $_POST['coupon_amount'] = $coupon->fields['coupon_amount'];
    if ($coupon->fields['coupon_type']=='P') {
      $_POST['coupon_amount'] .= '%';
    }
    if ($coupon->fields['coupon_type']=='S') {
      $_POST['coupon_free_ship'] = true;
    } else {
	  $_POST['coupon_free_ship'] = false;
	}
    $_POST['coupon_min_order'] = $coupon->fields['coupon_minimum_order'];
    $_POST['coupon_code'] = $coupon->fields['coupon_code'];
    $_POST['coupon_uses_coupon'] = $coupon->fields['uses_per_coupon'];
    $_POST['coupon_uses_user'] = $coupon->fields['uses_per_user'];
    $_POST['coupon_startdate'] = $coupon->fields['coupon_start_date'];
    $_POST['coupon_finishdate'] = $coupon->fields['coupon_expire_date'];
  case 'new':
// set some defaults
    if ($_GET['action'] != 'voucheredit' and $_POST['coupon_uses_user'] == '') $_POST['coupon_uses_user'] = 1;
?>
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
      <td>
<?php
    echo zen_draw_form('coupon', FILENAME_COUPON_ADMIN, 'action=update&oldaction='.$_GET['action'] . '&cid=' . $_GET['cid']);
?>
      <table border="0" width="100%" cellspacing="0" cellpadding="6">
<?php
        $languages = zen_get_languages();
        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
        $language_id = $languages[$i]['id'];
?>
      <tr>
        <td align="left" class="main"><?php if ($i==0) echo COUPON_NAME; ?></td>
        <td align="left"><?php echo zen_draw_input_field('coupon_name[' . $languages[$i]['id'] . ']', stripslashes($_POST['coupon_name'][$language_id])) . '&nbsp;' . zen_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']); ?></td>
        <td align="left" class="main" width="40%"><?php if ($i==0) echo COUPON_NAME_HELP; ?></td>
      </tr>
<?php
}
?>
<?php
        $languages = zen_get_languages();
        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
        $language_id = $languages[$i]['id'];
?>

      <tr>
        <td align="left" valign="top" class="main"><?php if ($i==0) echo COUPON_DESC; ?></td>
        <td align="left" valign="top"><?php echo zen_draw_textarea_field('coupon_desc[' . $languages[$i]['id'] . ']','physical','24','3', stripslashes($_POST['coupon_desc'][$language_id])) . '&nbsp;' . zen_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']); ?></td>
        <td align="left" valign="top" class="main"><?php if ($i==0) echo COUPON_DESC_HELP; ?></td>
      </tr>
<?php
}
?>
      <tr>
        <td align="left" class="main"><?php echo COUPON_AMOUNT; ?></td>
        <td align="left"><?php echo zen_draw_input_field('coupon_amount', $_POST['coupon_amount']); ?></td>
        <td align="left" class="main"><?php echo COUPON_AMOUNT_HELP; ?></td>
      </tr>
      <tr>
        <td align="left" class="main"><?php echo COUPON_MIN_ORDER; ?></td>
        <td align="left"><?php echo zen_draw_input_field('coupon_min_order', $_POST['coupon_min_order']); ?></td>
        <td align="left" class="main"><?php echo COUPON_MIN_ORDER_HELP; ?></td>
      </tr>
      <tr>
        <td align="left" class="main"><?php echo COUPON_FREE_SHIP; ?></td>
        <td align="left"><input type="checkbox" name="coupon_free_ship" <?php if ($_POST['coupon_free_ship']) echo 'CHECKED'; ?>></td>
        <td align="left" class="main"><?php echo COUPON_FREE_SHIP_HELP; ?></td>
      </tr>
      <tr>
        <td align="left" class="main"><?php echo COUPON_CODE; ?></td>
        <td align="left"><?php echo zen_draw_input_field('coupon_code', $_POST['coupon_code']); ?></td>
        <td align="left" class="main"><?php echo COUPON_CODE_HELP; ?></td>
      </tr>
      <tr>
        <td align="left" class="main"><?php echo COUPON_USES_COUPON; ?></td>
        <td align="left"><?php echo zen_draw_input_field('coupon_uses_coupon', ($_POST['coupon_uses_coupon'] >= 1 ? $_POST['coupon_uses_coupon'] : '')); ?></td>
        <td align="left" class="main"><?php echo COUPON_USES_COUPON_HELP; ?></td>
      </tr>
      <tr>
        <td align="left" class="main"><?php echo COUPON_USES_USER; ?></td>
        <td align="left"><?php echo zen_draw_input_field('coupon_uses_user', ($_POST['coupon_uses_user'] >= 1 ? $_POST['coupon_uses_user'] : '')); ?></td>
        <td align="left" class="main"><?php echo COUPON_USES_USER_HELP; ?></td>
      </tr>
      <tr>
<?php
    if (!$_POST['coupon_startdate']) {
      $coupon_startdate = split("[-]", date('Y-m-d'));
    } else {
      $coupon_startdate = split("[-]", $_POST['coupon_startdate']);
    }
    if (!$_POST['coupon_finishdate']) {
      $coupon_finishdate = split("[-]", date('Y-m-d'));
      $coupon_finishdate[0] = $coupon_finishdate[0] + 1;
    } else {
      $coupon_finishdate = split("[-]", $_POST['coupon_finishdate']);
    }
?>
        <td align="left" class="main"><?php echo COUPON_STARTDATE; ?></td>
        <td align="left"><?php echo zen_draw_date_selector('coupon_startdate', mktime(0,0,0, $coupon_startdate[1], $coupon_startdate[2], $coupon_startdate[0], 0)); ?></td>
        <td align="left" class="main"><?php echo COUPON_STARTDATE_HELP; ?></td>
      </tr>
      <tr>
        <td align="left" class="main"><?php echo COUPON_FINISHDATE; ?></td>
        <td align="left"><?php echo zen_draw_date_selector('coupon_finishdate', mktime(0,0,0, $coupon_finishdate[1], $coupon_finishdate[2], $coupon_finishdate[0], 0)); ?></td>
        <td align="left" class="main"><?php echo COUPON_FINISHDATE_HELP; ?></td>
      </tr>
      <tr>
        <td align="left"><?php echo zen_image_submit('button_preview.gif',COUPON_BUTTON_PREVIEW); ?></td>
        <td align="left"><?php echo '&nbsp;&nbsp;<a href="' . zen_href_link_admin(FILENAME_COUPON_ADMIN, 'cid=' . $_GET['cid']); ?>"><?php echo zen_image_button('button_cancel.gif', IMAGE_CANCEL); ?></a>
      </td>
      </tr>
      </td></table></form>
      </tr>

      </table></td>
<?php
    break;
  default:
?>
    <td width="100%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td width="100%"><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
            <td class="main"><?php echo zen_draw_form('status', FILENAME_COUPON_ADMIN, '', 'get'); ?>
<?php
    $status_array[] = array('id' => 'Y', 'text' => TEXT_COUPON_ACTIVE);
    $status_array[] = array('id' => 'N', 'text' => TEXT_COUPON_INACTIVE);
    $status_array[] = array('id' => '*', 'text' => TEXT_COUPON_ALL);

    if ($_GET['status']) {
      $status = zen_db_prepare_input($_GET['status']);
    } else {
      $status = 'Y';
    }
    echo HEADING_TITLE_STATUS . ' ' . zen_draw_pull_down_menu('status', $status_array, $status, 'onChange="this.form.submit();"');
?>
              </form>
           </td>
            <td class="main">
<?php
// toggle switch for editor
        $editor_array = array(array('id' => '0', 'text' => TEXT_NONE),
                              array('id' => '1', 'text' => TEXT_HTML_AREA));
        echo TEXT_EDITOR_INFO . zen_draw_form('set_editor_form', FILENAME_COUPON_ADMIN, '', 'get') . '&nbsp;&nbsp;' . zen_draw_pull_down_menu('reset_editor', $editor_array, ($_SESSION['html_editor_preference_status'] == 'HTMLAREA' ? '1' : '0'), 'onChange="this.form.submit();"') .
        zen_draw_hidden_field('action', 'set_editor') .
        '</form>';
?>
</td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo COUPON_NAME; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo COUPON_AMOUNT; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo COUPON_CODE; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php
    if ($_GET['page'] > 1) $rows = $_GET['page'] * 20 - 20;
    if ($status != '*') {
      $cc_query_raw = "select `coupon_id`, `coupon_code`, `coupon_amount`, `coupon_type`, `coupon_start_date`, `coupon_expire_date`, `uses_per_user`, `uses_per_coupon`, `restrict_to_products`, `restrict_to_categories`, `date_created`, `date_modified` from " . TABLE_COUPONS ." where `coupon_active`='" . zen_db_input($status) . "' and `coupon_type` != 'G'";
    } else {
      $cc_query_raw = "select `coupon_id`, `coupon_code`, `coupon_amount`, `coupon_type`, `coupon_start_date`, `coupon_expire_date`, `uses_per_user`, `uses_per_coupon`, `restrict_to_products`, `restrict_to_categories`, `date_created`, `date_modified` from " . TABLE_COUPONS . " where `coupon_type` != 'G'";
    }
    $cc_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS_DISCOUNT_COUPONS, $cc_query_raw, $cc_query_numrows);
    $cc_list = $db->Execute($cc_query_raw);
    while (!$cc_list->EOF) {
      $rows++;
      if (strlen($rows) < 2) {
        $rows = '0' . $rows;
      }
      if (((!$_GET['cid']) || (@$_GET['cid'] == $cc_list->fields['coupon_id'])) && (!$cInfo)) {
        $cInfo = new objectInfo($cc_list->fields);
      }
      if ( (is_object($cInfo)) && ($cc_list->fields['coupon_id'] == $cInfo->coupon_id) ) {
        echo '          <tr class="dataTableRowSelected" onmouseover="this.style.cursor=\'hand\'" onclick="document.location.href=\'' . zen_href_link_admin(FILENAME_COUPON_ADMIN, zen_get_all_get_params(array('cid', 'action')) . 'cid=' . $cInfo->coupon_id . '&action=edit') . '\'">' . "\n";
      } else {
        echo '          <tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';this.style.cursor=\'hand\'" onmouseout="this.className=\'dataTableRow\'" onclick="document.location.href=\'' . zen_href_link_admin(FILENAME_COUPON_ADMIN, zen_get_all_get_params(array('cid', 'action')) . 'cid=' . $cc_list->fields['coupon_id']) . '\'">' . "\n";
      }
      $coupon_desc = $db->Execute("select `coupon_name`
                                   from " . TABLE_COUPONS_DESCRIPTION . "
                                   where `coupon_id` = '" . $cc_list->fields['coupon_id'] . "'
                                   and `language_id` = '" . $_SESSION['languages_id'] . "'");
?>
                <td class="dataTableContent"><?php echo $coupon_desc->fields['coupon_name']; ?></td>
                <td class="dataTableContent" align="center">
<?php
      if ($cc_list->fields['coupon_type'] == 'P') {
        echo $cc_list->fields['coupon_amount'] . '%';
      } elseif ($cc_list->fields['coupon_type'] == 'S') {
        echo TEXT_FREE_SHIPPING;
      } else {
        echo $currencies->format($cc_list->fields['coupon_amount']);
      }
?>
            &nbsp;</td>
                <td class="dataTableContent" align="center"><?php echo $cc_list->fields['coupon_code']; ?></td>
                <td class="dataTableContent" align="right"><?php if ( (is_object($cInfo)) && ($cc_list->fields['coupon_id'] == $cInfo->coupon_id) ) { echo zen_image(DIR_WS_IMAGES . 'icon_arrow_right.gif'); } else { echo '<a href="' . zen_href_link_admin(FILENAME_COUPON_ADMIN, 'page=' . $_GET['page'] . '&cid=' . $cc_list->fields['coupon_id']) . '">' . zen_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
      $cc_list->MoveNext();
    }
?>
          <tr>
            <td colspan="5"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr>
                <td class="smallText">&nbsp;<?php echo $cc_split->display_count($cc_query_numrows, MAX_DISPLAY_SEARCH_RESULTS_DISCOUNT_COUPONS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_COUPONS); ?>&nbsp;</td>
                <td align="right" class="smallText">&nbsp;<?php echo $cc_split->display_links($cc_query_numrows, MAX_DISPLAY_SEARCH_RESULTS_DISCOUNT_COUPONS, MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?>&nbsp;</td>
              </tr>

              <tr>
                <td align="right" colspan="2" class="smallText"><?php echo '<a href="' . zen_href_link_admin(FILENAME_COUPON_ADMIN, 'page=' . $_GET['page'] . '&cid=' . $cInfo->coupon_id . '&action=new') . '">' . zen_image_button('button_insert.gif', IMAGE_INSERT) . '</a>'; ?></td>
              </tr>
            </table></td>
          </tr>
        </table></td>

<?php

    $heading = array();
    $contents = array();

    switch ($_GET['action']) {
    case 'release':
      break;
    case 'voucherreport':
      $heading[] = array('text' => '<b>' . TEXT_HEADING_COUPON_REPORT . '</b>');
      $contents[] = array('text' => TEXT_NEW_INTRO);
      break;
    case 'new':
      $heading[] = array('text' => '<b>' . TEXT_HEADING_NEW_COUPON . '</b>');
      $contents[] = array('text' => TEXT_NEW_INTRO);
      $contents[] = array('text' => '<br />' . COUPON_NAME . '<br />' . zen_draw_input_field('name'));
      $contents[] = array('text' => '<br />' . COUPON_AMOUNT . '<br />' . zen_draw_input_field('voucher_amount'));
      $contents[] = array('text' => '<br />' . COUPON_CODE . '<br />' . zen_draw_input_field('voucher_code'));
      $contents[] = array('text' => '<br />' . COUPON_USES_COUPON . '<br />' . zen_draw_input_field('voucher_number_of'));
      break;
    default:
      $heading[] = array('text'=>'['.$cInfo->coupon_id.']  '.$cInfo->coupon_code);
      $amount = $cInfo->coupon_amount;
      if ($cInfo->coupon_type == 'P') {
        $amount .= '%';
      } else {
        $amount = $currencies->format($amount);
      }
      if ($_GET['action'] == 'voucherdelete') {
        $contents[] = array('text'=> TEXT_CONFIRM_DELETE . '</br></br>' .
                '<a href="'.zen_href_link_admin(FILENAME_COUPON_ADMIN,'action=confirmdelete&cid='.$_GET['cid'],'NONSSL').'">'.zen_image_button('button_confirm.gif','Confirm Delete ' . TEXT_GV_NAME).'</a>' .
                '<a href="'.zen_href_link_admin(FILENAME_COUPON_ADMIN,'cid='.$cInfo->coupon_id,'NONSSL').'">'.zen_image_button('button_cancel.gif','Cancel').'</a>'
                );
      } else {
        $prod_details = NONE;
//bof 12-6ke
$product_query = $db->query("select * from " . TABLE_COUPON_RESTRICT . " where `coupon_id` = ? and `product_id` != '0'", array( $cInfo->coupon_id ) );
		if ($product_query->RecordCount() > 0) $prod_details = TEXT_SEE_RESTRICT;
//eof 12-6ke
        $cat_details = NONE;
//bof 12-6ke
$category_query = $db->query("select * from " . TABLE_COUPON_RESTRICT . " where `coupon_id` = ? and `category_id` != '0'", array( $cInfo->coupon_id ));
        if ($category_query->RecordCount() > 0) $cat_details = TEXT_SEE_RESTRICT;
//eof 12-6ke
        $coupon_name = $db->query("select `coupon_name`
                                     from " . TABLE_COUPONS_DESCRIPTION . "
                                     where `coupon_id` = ? and `language_id` = ?", array( $cInfo->coupon_id, $_SESSION['languages_id'] ) );
        $uses_coupon = $cInfo->uses_per_coupon;
        $uses_user = $cInfo->uses_per_user;
        if ($uses_coupon == 0 || $uses_coupon == '') $uses_coupon = TEXT_UNLIMITED;
        if ($uses_user == 0 || $uses_user == '') $uses_user = TEXT_UNLIMITED;
        $contents[] = array('text'=>COUPON_NAME . '&nbsp;::&nbsp; ' . $coupon_name->fields['coupon_name'] . '<br />' .
                     COUPON_AMOUNT . '&nbsp;::&nbsp; ' . $amount . '<br />' .
                     COUPON_STARTDATE . '&nbsp;::&nbsp; ' . zen_date_short($cInfo->coupon_start_date) . '<br />' .
                     COUPON_FINISHDATE . '&nbsp;::&nbsp; ' . zen_date_short($cInfo->coupon_expire_date) . '<br />' .
                     COUPON_USES_COUPON . '&nbsp;::&nbsp; ' . $uses_coupon . '<br />' .
                     COUPON_USES_USER . '&nbsp;::&nbsp; ' . $uses_user . '<br />' .
                     COUPON_PRODUCTS . '&nbsp;::&nbsp; ' . $prod_details . '<br />' .
                     COUPON_CATEGORIES . '&nbsp;::&nbsp; ' . $cat_details . '<br />' .
                     DATE_CREATED . '&nbsp;::&nbsp; ' . zen_date_short($cInfo->date_created) . '<br />' .
                     DATE_MODIFIED . '&nbsp;::&nbsp; ' . zen_date_short($cInfo->date_modified) . '<br /><br />' .
                     ($cInfo->coupon_id != '' ?
                     '<center><a href="'.zen_href_link_admin(FILENAME_COUPON_ADMIN,'action=email&cid='.$cInfo->coupon_id,'NONSSL').'">'.zen_image_button('button_email.gif','Email ' . TEXT_GV_NAME).'</a>' .
                     '<a href="'.zen_href_link_admin(FILENAME_COUPON_ADMIN,'action=voucheredit&cid='.$cInfo->coupon_id,'NONSSL').'">'.zen_image_button('button_edit.gif','Edit ' . TEXT_GV_NAME).'</a>' .
                     '<a href="'.zen_href_link_admin(FILENAME_COUPON_ADMIN,'action=voucherdelete&cid='.$cInfo->coupon_id,'NONSSL').'">'.zen_image_button('button_delete.gif','Delete ' . TEXT_GV_NAME).'</a>' .
                     '<br /><a href="'.zen_href_link_admin('coupon_restrict.php','cid='.$cInfo->coupon_id,'NONSSL').'">'.zen_image_button('button_restrict.gif','Restrict').'</a><a href="'.zen_href_link_admin(FILENAME_COUPON_ADMIN,'action=voucherreport&cid='.$cInfo->coupon_id,'NONSSL').'">'.zen_image_button('button_report.gif',TEXT_GV_NAME . ' Report').'</a></center>'
                     : ' who ' . $cInfo->coupon_id . ' - ' . $_GET['cid'])
                     );
        }
        break;
      }
?>
    <td width="25%" valign="top">
<?php
      $box = new box;
      echo $box->infoBox($heading, $contents);
    echo '            </td>' . "\n";
    }
?>
      </tr>
    </table></td>
<!-- body_text_eof //-->
  </tr>
</table>
<!-- body_eof //-->
<!-- footer //-->
<?php require(DIR_FS_ADMIN_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
</body>
</html>
<?php require(DIR_FS_ADMIN_INCLUDES . 'application_bottom.php'); ?>
