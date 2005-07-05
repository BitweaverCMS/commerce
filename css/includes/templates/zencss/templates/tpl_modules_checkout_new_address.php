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
// $Id: tpl_modules_checkout_new_address.php,v 1.1 2005/07/05 05:59:28 bitweaver Exp $
//
?>
<?php echo FORM_REQUIRED_INFORMATION; ?> 
<?php
  if (ACCOUNT_GENDER == 'true') {
    if (isset($gender)) {
      $male = ($gender == 'm') ? true : false;
    } else {
      $male = ($entry['entry_gender'] == 'm') ? true : false;
    }
    $female = !$male;
?>
<div class="formrow"> 
  <label><?php echo ENTRY_GENDER; ?></label>
  <?php echo zen_draw_radio_field('gender', 'm', $male) . '&nbsp;&nbsp;' . MALE . '&nbsp;&nbsp;' . zen_draw_radio_field('gender', 'f', $female) . '&nbsp;&nbsp;' . FEMALE . '&nbsp;' . (zen_not_null(ENTRY_GENDER_TEXT) ? '<span class="inputrequirement">' . ENTRY_GENDER_TEXT . '</span>': ''); ?> 
</div>
<?php
  }
?>
<div class="formrow"> 
  <label><?php echo ENTRY_FIRST_NAME; ?></label>
  <?php echo zen_draw_input_field('firstname', $entry['entry_firstname']) . '&nbsp;' . (zen_not_null(ENTRY_FIRST_NAME_TEXT) ? '<span class="inputrequirement">' . ENTRY_FIRST_NAME_TEXT . '</span>': ''); ?> 
</div>
<div class="formrow"> 
  <label><?php echo ENTRY_LAST_NAME; ?></label>
  <?php echo zen_draw_input_field('lastname', $entry['entry_lastname']) . '&nbsp;' . (zen_not_null(ENTRY_LAST_NAME_TEXT) ? '<span class="inputrequirement">' . ENTRY_LAST_NAME_TEXT . '</span>': ''); ?> 
</div>
<?php
  if (ACCOUNT_COMPANY == 'true') {
?>
<div class="formrow"> 
  <label><?php echo ENTRY_COMPANY; ?></label>
  <?php echo zen_draw_input_field('company', $entry['entry_company']) . '&nbsp;' . (zen_not_null(ENTRY_COMPANY_TEXT) ? '<span class="inputrequirement">' . ENTRY_COMPANY_TEXT . '</span>': ''); ?> 
</div>
<?php
  }
?>
<div class="formrow"> 
  <label><?php echo ENTRY_STREET_ADDRESS; ?></label>
  <?php echo zen_draw_input_field('street_address', $entry['entry_street_address']) . '&nbsp;' . (zen_not_null(ENTRY_STREET_ADDRESS_TEXT) ? '<span class="inputrequirement">' . ENTRY_STREET_ADDRESS_TEXT . '</span>': ''); ?> 
</div>
<?php
  if (ACCOUNT_SUBURB == 'true') {
?>
<div class="formrow"> 
  <label><?php echo ENTRY_SUBURB; ?></label>
  <?php echo zen_draw_input_field('suburb', $entry['entry_suburb']) . '&nbsp;' . (zen_not_null(ENTRY_SUBURB_TEXT) ? '<span class="inputrequirement">' . ENTRY_SUBURB_TEXT . '</span>': ''); ?> 
</div>
<?php
  }
?>
<div class="formrow"> 
  <label><?php echo ENTRY_CITY; ?></label>
  <?php echo zen_draw_input_field('city', $entry['entry_city']) . '&nbsp;' . (zen_not_null(ENTRY_CITY_TEXT) ? '<span class="inputrequirement">' . ENTRY_CITY_TEXT . '</span>': ''); ?> 
</div>
<?php
  if (ACCOUNT_STATE == 'true') {
?>
<div class="formrow"> 
  <label><?php echo ENTRY_STATE; ?></label>
  <?php
    if ($process == true) {
      if ($entry_state_has_zones == true) {
        $zones_array = array();
        $zones_query = "select zone_name from " . TABLE_ZONES . " 
                        where zone_country_id = '" . (int)$country . "' 
                        order by zone_name";

        $zones_values = $db->Execute($zones_query);

        while (!$zones_values->EOF) {
          $zones_array[] = array('id' => $zones_values->fields['zone_name'], 'text' => $zones_values->fields['zone_name']);
          $zones_values->MoveNext();
        }
        echo zen_draw_pull_down_menu('state', $zones_array);
      } else {
        echo zen_draw_input_field('state');
      }
    } else {
      echo zen_draw_input_field('state', zen_get_zone_name($entry['entry_country_id'], $entry['entry_zone_id'], $entry['entry_state']));
    }

    if (zen_not_null(ENTRY_STATE_TEXT)) echo '&nbsp;<span class="inputrequirement">' . ENTRY_STATE_TEXT . '</span';
?>
</div>
<?php
  }
?>
<div class="formrow"> 
  <label><?php echo ENTRY_POST_CODE; ?></label>
  <?php echo zen_draw_input_field('postcode', $entry['entry_postcode']) . '&nbsp;' . (zen_not_null(ENTRY_POST_CODE_TEXT) ? '<span class="inputrequirement">' . ENTRY_POST_CODE_TEXT . '</span>': ''); ?> 
</div>
<div class="formrow"> 
  <label><?php echo ENTRY_COUNTRY; ?></label>
  <?php echo zen_get_country_list('country', $entry['entry_country_id']) . '&nbsp;' . (zen_not_null(ENTRY_COUNTRY_TEXT) ? '<span class="inputrequirement">' . ENTRY_COUNTRY_TEXT . '</span>': ''); ?> 
</div>
<?php
  if ((isset($_GET['edit']) && ($_SESSION['customer_default_address_id'] != $_GET['edit'])) || (isset($_GET['edit']) == false) ) {
?>
<?php echo zen_draw_checkbox_field('primary', 'on', false, 'id="primary"') . ' ' . SET_AS_PRIMARY; ?> 
<?php
  }
?>
