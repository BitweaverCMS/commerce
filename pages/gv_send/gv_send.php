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
// $Id: gv_send.php,v 1.1 2005/10/06 19:38:28 spiderr Exp $
//
//echo 'Local Customer GV = ' . $local_customer_gv . '<br />';
//echo 'Base Customer GV = ' . $base_customer_gv . '<br />';
//echo 'Local Customer Send = ' . $local_customer_send . '<br />';
//echo 'Base Customer Send = ' . $base_customer_send . '<br />';
?>
<table border="0" width="100%" cellspacing="0" cellpadding="0">
  <tr>
    <td width="100%"><table border="0" width="100%" cellspacing="0" cellpadding="0">
      <tr>
        <td class="pageHeading"><h1><?php echo HEADING_TITLE; ?></h1></td>
      </tr>
    </table></td>
  </tr>
  <tr>
    <td class="main" height="10px"></td>
  </tr>
  <tr>
    <td class="plainBox">
      <table border="0" width="100%" cellspacing="2" cellpadding="2">
        <tr>
          <td class="main"><?php echo TEXT_AVAILABLE_BALANCE . $currencies->format($gv_current_balance); ?></td>
        </tr>
      </table>
    </td>
  </tr>
<?php
  if ($_GET['action'] == 'doneprocess') {
?>
  <tr>
    <td class="main"><?php echo TEXT_SUCCESS; ?></td>
  </tr>
  <tr>
    <td align="right"><br /><a href="<?php echo zen_href_link(FILENAME_DEFAULT, '', 'NONSSL'); ?>"><?php echo zen_image_button(BUTTON_IMAGE_CONTINUE, BUTTON_CONTINUE_ALT); ?></a></td>
  </tr>
<?php
  }
  if ($_GET['action'] == 'send' && !$error) {
    // validate entries
      $gv_amount = (double) $gv_amount;
      $gv_query = "select `customers_firstname`, `customers_lastname`
                   from " . TABLE_CUSTOMERS . "
                   where `customers_id` = '" . $_SESSION['customer_id'] . "'";

      $gv_result = $db->Execute($gv_query);

      $send_name = $gv_result->fields['customers_firstname'] . ' ' . $gv_result->fields['customers_lastname'];
?>
  <tr>
    <td class="main"><form action="<?php echo zen_href_link(FILENAME_GV_SEND, 'action=process', 'NONSSL'); ?>" method="post"><table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td class="main"><?php echo sprintf(MAIN_MESSAGE, $currencies->format($_POST['amount'], false), $_POST['to_name'], $_POST['email'], $_POST['to_name'], $currencies->format($_POST['amount'], false), $send_name); ?></td>
      </tr>
<?php
      if ($_POST['message']) {
?>
      <tr>
        <td class="main"><?php echo sprintf(PERSONAL_MESSAGE, $gv_result->fields['customers_firstname']); ?></td>
      </tr>
      <tr>
        <td class="main"><tt><?php echo stripslashes($_POST['message']); ?></tt></td>
      </tr>
<?php
      }

      echo zen_draw_hidden_field('send_name', $send_name) . zen_draw_hidden_field('to_name', stripslashes($_POST['to_name'])) . zen_draw_hidden_field('email', $_POST['email']) . zen_draw_hidden_field('amount', $gv_amount) . zen_draw_hidden_field('message', stripslashes($_POST['message']));
?>
      <tr>
        <td class="main"><?php echo zen_image_submit(BUTTON_IMAGE_BACK, BUTTON_BACK_ALT, 'name=back') . '</a>'; ?></td>
        <td align="right"><?php echo zen_image_submit(BUTTON_IMAGE_SEND, BUTTON_SEND_ALT); ?></td>
      </tr>
    </table></form></td>
  </tr>
<?php
  } elseif ($_GET['action']=='' || $error) {
?>
  <tr>
    <td class="main"><?php echo HEADING_TEXT; ?></td>
  </tr>
  <tr>
    <td class="main"><form action="<?php echo zen_href_link(FILENAME_GV_SEND, 'action=send', 'NONSSL'); ?>" method="post"><table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td class="main"><?php echo ENTRY_NAME; ?><br /><?php echo zen_draw_input_field('to_name', $_POST['to_name'], 'size="40"');?></td>
      </tr>
      <tr>
        <td class="main"><?php echo ENTRY_EMAIL; ?><br /><?php echo zen_draw_input_field('email', $_POST['email'], 'size="40"'); if ($error) echo $error_email; ?></td>
      </tr>
      <tr>
        <td class="main"><?php echo ENTRY_AMOUNT; ?><br /><?php echo zen_draw_input_field('amount', $_POST['amount'], '', '', false); if ($error) echo $error_amount; ?></td>
      </tr>
      <tr>
        <td class="main"><?php echo ENTRY_MESSAGE; ?><br /><?php echo zen_draw_textarea_field('message', 'soft', 50, 15, stripslashes($_POST['message'])); ?></td>
      </tr>
    </table>
    <table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td class="main"><?php echo zen_back_link() . zen_image_button(BUTTON_IMAGE_BACK, BUTTON_BACK_ALT) . '</a>'; ?></td>
        <td class="main" align="right"><?php echo zen_image_submit(BUTTON_IMAGE_SEND, BUTTON_SEND_ALT); ?></td>
      </tr>
    </table></form></td>
  </tr>
<?php
  }
?>
  <tr>
    <td class="main" height="10px"></td>
  </tr>
  <tr>
    <td colspan="2" class="main"><?php echo EMAIL_ADVISORY_INCLUDED_WARNING . str_replace('-----', '', EMAIL_ADVISORY); ?></td>
  </tr>
</table>
