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
// $Id: tpl_gv_send_default.php,v 1.1 2005/07/05 05:59:28 bitweaver Exp $
//
?>
<h1><?php echo HEADING_TITLE; ?></h1>
<?php
  if ($_GET['action'] == 'process') {
?>
<p><?php echo TEXT_SUCCESS; ?></p>
<p><?php echo 'gv '.$id1; ?></p>
<a href="<?php echo zen_href_link(FILENAME_DEFAULT, '', 'NONSSL'); ?>"><?php echo zen_image_button('button_continue.gif', IMAGE_BUTTON_CONTINUE); ?></a> 
<?php
  }  
  if ($_GET['action'] == 'send' && !$error) {
    // validate entries
      $gv_amount = (double) $gv_amount;
      $gv_query = "select customers_firstname, customers_lastname 
                   from " . TABLE_CUSTOMERS . " 
                   where customers_id = '" . $_SESSION['customer_id'] . "'";

      $gv_result = $db->Execute($gv_query);

      $send_name = $gv_result->fields['customers_firstname'] . ' ' . $gv_result->fields['customers_lastname'];
?>
<form action="<?php echo zen_href_link(FILENAME_GV_SEND, 'action=process', 'NONSSL'); ?>" method="post">
  <?php echo sprintf(MAIN_MESSAGE, $currencies->format($_POST['amount']), $_POST['to_name'], $_POST['email'], $_POST['to_name'], $currencies->format($_POST['amount']), $send_name); ?> 
  <?php
      if ($_POST['message']) {
?>
  <?php echo sprintf(PERSONAL_MESSAGE, $gv_result->fields['customers_firstname']); ?> 
  <?php echo stripslashes($_POST['message']); ?> 
  <?php
      }


 echo zen_draw_hidden_field('send_name', $send_name) . zen_draw_hidden_field('to_name', stripslashes($_POST['to_name'])) . zen_draw_hidden_field('email', $_POST['email']) . zen_draw_hidden_field('amount', $gv_amount) . zen_draw_hidden_field('message', stripslashes($_POST['message']));
  ?>
  <?php echo zen_image_submit('button_back.gif', IMAGE_BUTTON_BACK, 'name=back') . '</a>'; ?> 
  <?php echo zen_image_submit('button_send.gif', IMAGE_BUTTON_CONTINUE); ?> 
</form>
<?php
  } elseif ($_GET['action']=='' || $error) {
?>
<h2><?php echo HEADING_TEXT; ?></h2>
<form action="<?php echo zen_href_link(FILENAME_GV_SEND, 'action=send', 'NONSSL'); ?>" method="post">
  <div class="formrow"> 
    <label><?php echo ENTRY_NAME; ?></label>
    <?php echo zen_draw_input_field('to_name', $_POST['to_name']);?> </div>
  <div class="formrow"> 
    <label><?php echo ENTRY_EMAIL; ?></label>
    <?php echo zen_draw_input_field('email', $_POST['email']); if ($error) echo $error_email; ?> 
  </div>
  <div class="formrow"> 
    <label><?php echo ENTRY_AMOUNT; ?></label>
    <?php echo zen_draw_input_field('amount', $_POST['amount'], '', '', false); if ($error) echo $error_amount; ?> 
  </div>
  <div class="formrow"> 
    <label><?php echo ENTRY_MESSAGE; ?></label>
    <?php echo zen_draw_textarea_field('message', 'soft', 50, 15, stripslashes($_POST['message'])); ?> 
  </div>
  <?php
    $back = sizeof($_SESSION['navigation']->path)-2;
?>
  <div class="row"> <span class="right"><?php echo zen_image_submit('button_continue.gif', IMAGE_BUTTON_CONTINUE); ?></span> 
    <span class="left"><?php echo '<a href="' . zen_href_link('index.php', zen_array_to_string($_SESSION['navigation']->path[$back]['get'], array('action')), $_SESSION['navigation']->path[$back]['mode']) . '">' . zen_image_button('button_back.gif', IMAGE_BUTTON_BACK) . '</a>'; ?></span> 
  </div>
  <br class="clear" />
</form>
<?php
  }
?>