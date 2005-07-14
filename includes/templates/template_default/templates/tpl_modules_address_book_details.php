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
// $Id: tpl_modules_address_book_details.php,v 1.2 2005/07/14 04:55:16 spiderr Exp $
//
?>
<table border="0" width="100%" cellspacing="2" cellpadding="2">
  <tr>
    <td class="inputRequirement" align="right" colspan="2"><?php echo FORM_REQUIRED_INFORMATION; ?></td>
  </tr>
<?php
  if (ACCOUNT_GENDER == 'true') {
    if (isset($gender)) {
      $male = ($gender == 'm') ? true : false;
    } else {
      $male = ($entry->fields['entry_gender'] == 'm') ? true : false;
    }
    $female = !$male;
?>
  <tr>
    <td class="main"><?php echo ENTRY_GENDER; ?></td>
    <td class="main"><?php echo zen_draw_radio_field('gender', 'm', $male) . '&nbsp;&nbsp;' . MALE . '&nbsp;&nbsp;' . zen_draw_radio_field('gender', 'f', $female) . '&nbsp;&nbsp;' . FEMALE . '&nbsp;' . (zen_not_null(ENTRY_GENDER_TEXT) ? '<span class="inputRequirement">' . ENTRY_GENDER_TEXT . '</span>': ''); ?></td>
  </tr>
<?php
  }
?>
  <tr>
    <td class="main"><?php echo ENTRY_FIRST_NAME; ?></td>
    <td class="main"><?php echo zen_draw_input_field('firstname', $entry->fields['entry_firstname']) . '&nbsp;' . (zen_not_null(ENTRY_FIRST_NAME_TEXT) ? '<span class="inputRequirement">' . ENTRY_FIRST_NAME_TEXT . '</span>': ''); ?></td>
  </tr>
  <tr>
    <td class="main"><?php echo ENTRY_LAST_NAME; ?></td>
    <td class="main"><?php echo zen_draw_input_field('lastname', $entry->fields['entry_lastname']) . '&nbsp;' . (zen_not_null(ENTRY_LAST_NAME_TEXT) ? '<span class="inputRequirement">' . ENTRY_LAST_NAME_TEXT . '</span>': ''); ?></td>
  </tr>
  <tr>
    <td colspan="2" height="10px"></td>
  </tr>
<?php
  if (ACCOUNT_COMPANY == 'true') {
?>
  <tr>
    <td class="main"><?php echo ENTRY_COMPANY; ?></td>
    <td class="main"><?php echo zen_draw_input_field('company', $entry->fields['entry_company']) . '&nbsp;' . (zen_not_null(ENTRY_COMPANY_TEXT) ? '<span class="inputRequirement">' . ENTRY_COMPANY_TEXT . '</span>': ''); ?></td>
  </tr>
  <tr>
    <td colspan="2" height="10px"></td>
  </tr>
<?php
  }
?>
  <tr>
    <td class="main"><?php echo ENTRY_STREET_ADDRESS; ?></td>
    <td class="main"><?php echo zen_draw_input_field('street_address', $entry->fields['entry_street_address']) . '&nbsp;' . (zen_not_null(ENTRY_STREET_ADDRESS_TEXT) ? '<span class="inputRequirement">' . ENTRY_STREET_ADDRESS_TEXT . '</span>': ''); ?></td>
  </tr>
<?php
  if (ACCOUNT_SUBURB == 'true') {
?>
  <tr>
    <td class="main"><?php echo ENTRY_SUBURB; ?></td>
    <td class="main"><?php echo zen_draw_input_field('suburb', $entry->fields['entry_suburb']) . '&nbsp;' . (zen_not_null(ENTRY_SUBURB_TEXT) ? '<span class="inputRequirement">' . ENTRY_SUBURB_TEXT . '</span>': ''); ?></td>
  </tr>
<?php
  }
?>
  <tr>
    <td class="main"><?php echo ENTRY_CITY; ?></td>
    <td class="main"><?php echo zen_draw_input_field('city', $entry->fields['entry_city']) . '&nbsp;' . (zen_not_null(ENTRY_CITY_TEXT) ? '<span class="inputRequirement">' . ENTRY_CITY_TEXT . '</span>': ''); ?></td>
  </tr>
<?php
  if (ACCOUNT_STATE == 'true') {
?>
  <tr>
    <td class="main"><?php echo ENTRY_STATE; ?></td>
    <td class="main">
<?php
    if ($process == true) {
      if ($entry_state_has_zones == true) {
        $zones_array = array();
        $zones_values = $db->Execute("select zone_name
                                     from " . TABLE_ZONES . "
                                     where zone_country_id = '" . (int)$country . "'
                                     order by zone_name");

        while (!$zones_values->EOF) {
          $zones_array[] = array('id' => $zones_values->fields['zone_name'], 'text' => $zones_values->fields['zone_name']);
          $zones_values->MoveNext();
        }
        echo zen_draw_pull_down_menu('state', $zones_array, $zone_name);
      } else {
        echo zen_draw_input_field('state');
      }
    } else {
      echo zen_draw_input_field('state', zen_get_zone_name($entry->fields['entry_country_id'], $entry->fields['entry_zone_id'], $entry->fields['entry_state']));
    }

    if (zen_not_null(ENTRY_STATE_TEXT)) echo '&nbsp;<span class="inputRequirement">' . ENTRY_STATE_TEXT . '</span>';
?>
    </td>
  </tr>
<?php
  }
?>
  <tr>
    <td class="main"><?php echo ENTRY_POST_CODE; ?></td>
    <td class="main"><?php echo zen_draw_input_field('postcode', $entry->fields['entry_postcode']) . '&nbsp;' . (zen_not_null(ENTRY_POST_CODE_TEXT) ? '<span class="inputRequirement">' . ENTRY_POST_CODE_TEXT . '</span>': ''); ?></td>
  </tr>
  <tr>
    <td class="main"><?php echo ENTRY_COUNTRY; ?></td>
    <td class="main"><?php echo zen_get_country_list('country', $entry->fields['entry_country_id']) . '&nbsp;' . (zen_not_null(ENTRY_COUNTRY_TEXT) ? '<span class="inputRequirement">' . ENTRY_COUNTRY_TEXT . '</span>': ''); ?></td>
  </tr>
<?php
  if ((isset($_GET['edit']) && ($_SESSION['customer_default_address_id'] != $_GET['edit'])) || (isset($_GET['edit']) == false) ) {
?>
  <tr>
    <td colspan="2" class="main"><?php echo zen_draw_checkbox_field('primary', 'on', false, 'id="primary"') . ' ' . SET_AS_PRIMARY; ?></td>
  </tr>
<?php
  }
?>
</table> 