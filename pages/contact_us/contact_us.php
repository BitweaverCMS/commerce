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
// $Id$
//
?>
<?php echo zen_draw_form('contact_us', zen_href_link(FILENAME_CONTACT_US, 'action=send')); ?>
<table  width="100%" border="0" cellspacing="2" cellpadding="2" >
  <tr>
    <td class="pageHeading" colspan="2"><h1><?php echo HEADING_TITLE; ?></h1></td>
  </tr>

<?php if (CONTACT_US_STORE_NAME_ADDRESS== '1') { ?>
  <tr>
    <td class="main" align="left" colspan="2"><?php echo nl2br(STORE_NAME_ADDRESS); ?></td>
  </tr>
<?php } ?>

<?php if (DEFINE_CONTACT_US_STATUS == '1') { ?>
  <tr>
    <td class="plainBox" colspan="2"><?php require($define_contact_us); ?></td>
  </tr>
<?php } ?>

<?php
  if ($messageStack->size('contact') > 0) {
?>
  <tr>
    <td class="main" colspan="2"><?php echo $messageStack->output('contact'); ?></td>
  </tr>
<?php
  }

  if (isset($_GET['action']) && ($_GET['action'] == 'success')) {
?>
  <tr>
    <td class="plainBox" colspan="2"><?php echo TEXT_SUCCESS; ?></td>
  </tr>
  <tr>
    <td class="main" colspan="2"><?php echo zen_back_link() . zen_image_button(BUTTON_IMAGE_BACK, BUTTON_BACK_ALT) . '</a>'; ?></td>
  </tr>
<?php
  } else {
?>
<?php
// show dropdown if set
    if (CONTACT_US_LIST !=''){
?>
  <tr>
    <td class="plainBoxHeading" colspan="2"><?php echo SEND_TO_TEXT; ?></td>
  </tr>
  <tr>
    <td class="main" colspan="2"><?php echo zen_draw_pull_down_menu('send_to',  $send_to_array); ?></td>
  </tr>
<?php
    }
?>
  <tr>
    <td class="plainBoxHeading" colspan="2"><?php echo ENTRY_NAME; ?></td>
  </tr>
  <tr>
    <td class="main" colspan="2"><?php echo zen_draw_input_field('name', BitBase::getParameter( $_SESSION, 'name' ) ); ?></td>
  </tr>
  <tr>
    <td class="plainBoxHeading" colspan="2"><?php echo ENTRY_EMAIL; ?></td>
  </tr>
  <tr>
    <td class="main" colspan="2"><?php echo zen_draw_input_field('email', ($error ? $_POST['name'] : ($gBitUser->isRegistered() ? $gBitUser->getField( 'real_name' ) : ''))); ?></td>
  </tr>
  <tr>
    <td class="plainBoxHeading" colspan="2"><?php echo ENTRY_ENQUIRY; ?></td>
  </tr>
  <tr>
    <td class="main" colspan="2"><?php echo zen_draw_textarea_field('enquiry', 'soft', 50, 15); ?></td>
  </tr>
  <tr>
    <td class="main"><?php echo zen_back_link() . zen_image_button(BUTTON_IMAGE_BACK, BUTTON_BACK_ALT) . '</a>'; ?></td>
    <td align="right"><?php echo zen_image_submit(BUTTON_IMAGE_SEND, BUTTON_SEND_ALT); ?></td>
  </tr>
<?php
  }
?>
</table></form>
