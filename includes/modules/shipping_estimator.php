<?php
//
// +----------------------------------------------------------------------+
// |zen-cart Open Source E-commerce                                       |
// +----------------------------------------------------------------------+
// | Copyright (c) 2004 The zen-cart developers                           |
// |                                                                      |
// | http://www.zen-cart.com/index.php                                    |
// |                                                                      |
// | Portions Copyright (c) 2003 osCommerce   |
// | Copyright (c) 2003 Edwin Bekaert (edwin@ednique.com)|
// | Customized by: Linda McGrath osCommerce@WebMakers.com|
// | * This now handles Free Shipping for orders over $total as defined in the Admin|
// |  * This now shows Free Shipping on Virtual products       |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available through the world-wide-web at the following url:           |
// | http://www.zen-cart.com/license/2_0.txt.                             |
// | If you did not receive a copy of the zen-cart license and are unable |
// | to obtain it through the world-wide-web, please send a note to       |
// | license@zen-cart.com so we can mail you a copy immediately.          |
// +----------------------------------------------------------------------+
// $Id: shipping_estimator.php,v 1.7 2005/10/06 21:01:49 spiderr Exp $
//

global $gBitDb, $order, $currencies;

?>
<!-- shipping_estimator //-->
<script language="javascript" type="text/javascript">
  function shipincart_submit(sid){
    if(sid){
      document.estimator.sid.value=sid;
    }
    document.estimator.submit();
    return false;
  }
</script>
              <table align="center" valign="top" width = "100%"><tr><td>
<?php
// Only do when something is in the cart
if ($_SESSION['cart']->count_contents() > 0) {

// Could be placed in english.php
// shopping cart quotes
  // shipping cost
  require('includes/classes/http_client.php'); // shipping in basket

  //if($cart->get_content_type() !== 'virtual') {
    if ($_SESSION['customer_id']) {
      // user is logged in
      if (isset($_POST['address_id'])){
        // user changed address
        $sendto = $_POST['address_id'];
      }elseif ($_SESSION['cart_address_id']){
        // user once changed address
        $sendto = $_SESSION['cart_address_id'];
//        $sendto = $_SESSION['customer_default_address_id'];
      }else{
        // first timer
        $sendto = $_SESSION['customer_default_address_id'];
      }
      $_SESSION['sendto'] = $sendto;
      // set session now
      $_SESSION['cart_address_id'] = $sendto;
      // set shipping to null ! multipickjup changes address to store address...
      $shipping='';
      // include the order class (uses the sendto !)
      require(DIR_FS_CLASSES . 'order.php');
      $order = new order;
    }else{
// user not logged in !
      if (isset($_POST['country_id'])){
        // country is selected
        $_SESSION['country_info'] = zen_get_countries($_POST['country_id'],true);
        $country_info = $_SESSION['country_info'];
        $order->delivery = array('postcode' => $_POST['zip_code'],
                                 'country' => array('id' => $_POST['country_id'], 'title' => $country_info['countries_name'], 'iso_code_2' => $country_info['countries_iso_code_2'], 'iso_code_3' =>  $country_info['countries_iso_code_3']),
                                 'country_id' => $_POST['country_id'],
//add state zone_id
                                 'zone_id' => $_POST['state'],
                                 'format_id' => zen_get_address_format_id($_POST['country_id']));
        $_SESSION['cart_country_id'] = $_POST['country_id'];
//add state zone_id
        $_SESSION['cart_zone'] = $_POST['zone_id'];
        $_SESSION['cart_zip_code'] = $_POST['zip_code'];
      } elseif ($_SESSION['cart_country_id']){
        // session is available
        $_SESSION['country_info'] = zen_get_countries($_SESSION['cart_country_id'],true);
        $country_info = $_SESSION['country_info'];

// fix here - check for error on $cart_country_id
        $order->delivery = array('postcode' => $_SESSION['cart_zip_code'],
                                 'country' => array('id' => $_SESSION['cart_country_id'], 'title' => $country_info['countries_name'], 'iso_code_2' => $country_info['countries_iso_code_2'], 'iso_code_3' =>  $country_info['countries_iso_code_3']),
                                 'country_id' => $_SESSION['cart_country_id'],
                                 'format_id' => zen_get_address_format_id($_SESSION['cart_country_id']));
      } else {
        // first timer
        $_SESSION['cart_country_id'] = STORE_COUNTRY;
        $_SESSION['country_info'] = zen_get_countries(STORE_COUNTRY,true);
        $country_info = $_SESSION['country_info'];
        $order->delivery = array(//'postcode' => '',
                                 'country' => array('id' => STORE_COUNTRY, 'title' => $country_info['countries_name'], 'iso_code_2' => $country_info['countries_iso_code_2'], 'iso_code_3' =>  $country_info['countries_iso_code_3']),
                                 'country_id' => STORE_COUNTRY,
                                 'format_id' => zen_get_address_format_id($_POST['country_id']));
      }
      // set the cost to be able to calculate free shipping
      $order->info = array('total' => $_SESSION['cart']->show_total(), // TAX ????
                           'currency' => $currency,
                           'currency_value'=> $currencies->currencies[$currency]['value']);
    }
// weight and count needed for shipping !
    $total_weight = $_SESSION['cart']->show_weight();
    $total_count = $_SESSION['cart']->count_contents();
    require(DIR_FS_CLASSES . 'shipping.php');
    $shipping_modules = new shipping;
    $quotes = $shipping_modules->quote();
    $order->info['subtotal'] = $_SESSION['cart']->show_total();

// set selections for displaying
    $selected_country = $order->delivery['country']['id'];
    $selected_address = $sendto;
  //}
// eo shipping cost
  // check free shipping based on order $total
  if ( defined('MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING') && (MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING == 'true')) {
    switch (MODULE_ORDER_TOTAL_SHIPPING_DESTINATION) {
      case 'national':
        if ($order->delivery['country_id'] == STORE_COUNTRY) $pass = true; break;
      case 'international':
        if ($order->delivery['country_id'] != STORE_COUNTRY) $pass = true; break;
      case 'both':

        $pass = true; break;
      default:
        $pass = false; break;
    }
    $free_shipping = false;
    if ( ($pass == true) && ($_SESSION['cart']->show_total() >= MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER)) {
      $free_shipping = true;
      include(DIR_WS_LANGUAGES . $gBitCustomer->getLanguage() . '/modules/order_total/ot_shipping.php');
    }
  } else {
    $free_shipping = false;
  }
  // begin shipping cost
  if(!$free_shipping && $_SESSION['cart']->get_content_type() !== 'virtual'){
    if (zen_not_null($_POST['sid'])){
      list($module, $method) = explode('_', $_POST['sid']);
      $_SESSION['cart_sid'] = $_POST['sid'];
    }elseif ($_SESSION['cart_sid']){
      list($module, $method) = explode('_', $_SESSION['cart_sid']);
    }else{
      $module="";
      $method="";
    }
    if (zen_not_null($module)){
      $selected_quote = $shipping_modules->quote($method, $module);
      if($selected_quote[0]['error'] || !zen_not_null($selected_quote[0]['methods'][0]['cost'])){
        $selected_shipping = $shipping_modules->cheapest();
        $order->info['shipping_method'] = $selected_shipping['title'];
        $order->info['shipping_cost'] = $selected_shipping['cost'];
        $order->info['total']+= $selected_shipping['cost'];
      }else{
        $order->info['shipping_method'] = $selected_quote[0]['module'].' ('.$selected_quote[0]['methods'][0]['title'].')';
        $order->info['shipping_cost'] = $selected_quote[0]['methods'][0]['cost'];
        $order->info['total']+= $selected_quote[0]['methods'][0]['cost'];
        $selected_shipping['title'] = $order->info['shipping_method'];
        $selected_shipping['cost'] = $order->info['shipping_cost'];
        $selected_shipping['id'] = $selected_quote[0]['id'].'_'.$selected_quote[0]['methods'][0]['id'];
      }
    }else{
      $selected_shipping = $shipping_modules->cheapest();
      $order->info['shipping_method'] = $selected_shipping['title'];
      $order->info['shipping_cost'] = $selected_shipping['cost'];
      $order->info['total']+= $selected_shipping['cost'];
    }
  }
// virtual products need a free shipping
  if($_SESSION['cart']->get_content_type() == 'virtual') {
    $order->info['shipping_method'] = CART_SHIPPING_METHOD_FREE_TEXT . ' ' . CART_SHIPPING_METHOD_ALL_DOWNLOADS;
    $order->info['shipping_cost'] = 0;
  }
  if($free_shipping) {
    $order->info['shipping_method'] = MODULE_ORDER_TOTAL_SHIPPING_TITLE;
    $order->info['shipping_cost'] = 0;
  }
  $shipping=$selected_shipping;
// end of shipping cost
// end free shipping based on order total

//the following 3 lines removed to get rid of the broken image, and replace with just the text output.
//  $info_box_contents = array();
//  $info_box_contents[] = array('text' => CART_SHIPPING_OPTIONS);
//  new infoBoxHeading($info_box_contents, false, false);
echo '<strong>' . CART_SHIPPING_OPTIONS . '</strong>';


  if (SHOW_SHIPPING_ESTIMATOR_BUTTON == '1') {
    $show_in = FILENAME_POPUP_SHIPPING_ESTIMATOR;
  } else {
    $show_in = FILENAME_SHOPPING_CART;
  }
  $ShipTxt= zen_draw_form('estimator', zen_href_link($show_in, '', 'NONSSL'), 'post'); //'onSubmit="return check_form();"'
  $ShipTxt.=zen_draw_hidden_field('sid', $selected_shipping['id']);
  $ShipTxt.='<table>';
  if(sizeof($quotes)) {
    if ($_SESSION['customer_id']) {
      // logged in
      $ShipTxt.='<tr><td colspan="3" class="main">' . CART_ITEMS . $total_count . '</td></tr>';
      $addresses = $gBitDb->Execute("select `address_book_id`, `entry_city` as `city`, `entry_postcode` as `postcode`, `entry_state` as `state`, `entry_zone_id` as `zone_id`, `entry_country_id` as `country_id` from " . TABLE_ADDRESS_BOOK . " where `customers_id` = '" . $_SESSION['customer_id'] . "'");
      // only display addresses if more than 1
      if ($addresses->RecordCount() > 1){
        while (!$addresses->EOF) {
          $addresses_array[] = array('id' => $addresses->fields['address_book_id'], 'text' => zen_address_format(zen_get_address_format_id($addresses->fields['country_id']), $addresses->fields, 0, ' ', ' '));
	  $addresses->MoveNext();
        }
        $ShipTxt.='<tr><td colspan="3" class="main" nowrap="nowrap">' .
                  CART_SHIPPING_METHOD_ADDRESS .'&nbsp;'. zen_draw_pull_down_menu('address_id', $addresses_array, $selected_address, 'onchange="return shipincart_submit(\'\');"').'</td></tr>';
      }
      $ShipTxt.='<tr valign="top"><td class="main">' . CART_SHIPPING_METHOD_TO .'</td><td colspan="2" class="main">'. zen_address_format($order->delivery['format_id'], $order->delivery, 1, ' ', '<br />') . '</td></tr>';
    } else {
// not logged in
//      $ShipTxt.=zen_output_warning(CART_SHIPPING_OPTIONS_LOGIN);
      $ShipTxt.='<tr><td colspan="3" class="main">' . CART_ITEMS . $total_count . /*'&nbsp;-&nbsp;Weight: ' . $total_weight .*/ '</td></tr>' . "\n\n";
      if($_SESSION['cart']->get_content_type() != 'virtual'){
        $ShipTxt.='<tr><td class="main">' .
                  ENTRY_COUNTRY .'</td><td colspan="2" class="main">'. zen_get_country_list('country_id', $selected_country,'style="width=200"') . '</td></tr>' . "\n\n";
//add state zone_id
        $state_array[] = array('id' => '', 'text' => PULL_DOWN_SHIPPING_ESTIMATOR_SELECT);
        $state_values = $gBitDb->Execute("select `zone_name`, `zone_id` from " . TABLE_ZONES . " where `zone_country_id` = '$selected_country' order by `zone_country_id` DESC, `zone_name`");
        while (!$state_values->EOF) {
          $state_array[] = array('id' => $state_values->fields['zone_id'],
                                 'text' => $state_values->fields['zone_name']);
	$state_values->MoveNext();
        }

      $ShipTxt.= '<tr><td colspan="1" class="main">' . ENTRY_STATE .'</td><td colspan="2" class="main">'. zen_draw_pull_down_menu('state',$state_array) . '</td></tr>';

        if(CART_SHIPPING_METHOD_ZIP_REQUIRED == "true"){
          $ShipTxt.='<tr><td colspan="1" class="main">'.ENTRY_POST_CODE .'</td><td colspan="2" class="main">'. zen_draw_input_field('zip_code', $selected_zip, 'size="7"') . '</td></tr>';
        }
//        $ShipTxt.='&nbsp;<a href="_" onclick="return shipincart_submit(\'\');">'.CART_SHIPPING_METHOD_RECALCULATE.'</a></td></tr>';
        $ShipTxt.='<tr><td colspan="3" class="main"><a href="_" onclick="return shipincart_submit(\'\');">'. zen_image_button(BUTTON_IMAGE_UPDATE, BUTTON_UPDATE_ALT) . ' </a></td></tr>';
	$ShipTxt.='<tr><td colspan="3">' . zen_draw_separator('pixel_trans.gif') . '</tr></td>';
      }
    }
    if($_SESSION['cart']->get_content_type() == 'virtual'){
      // virtual product/download
      //$ShipTxt.='<tr><td colspan="3" class="main">'.zen_draw_separator().'</td></tr>';
      $ShipTxt.='<tr><td class="main">' . CART_SHIPPING_METHOD_FREE_TEXT . ' ' . CART_SHIPPING_METHOD_ALL_DOWNLOADS . '</td></tr>';
    }elseif ($free_shipping==1) {
      // order $total is free
      $ShipTxt.='<tr><td colspan="3" class="main">'.zen_draw_separator().'</td></tr>';
      $ShipTxt.='<tr><td class="main">' . sprintf(FREE_SHIPPING_DESCRIPTION, $currencies->format(MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER)) . '</td><td>&nbsp;</td></tr>';
    }else{
      // shipping display
      $ShipTxt.='<tr><td></td><td class="main" align="left"><strong>' . CART_SHIPPING_METHOD_TEXT . '</strong></td><td class="main" align="center"><strong>' . CART_SHIPPING_METHOD_RATES . '</strong></td></tr>';
      $ShipTxt.='<tr><td colspan="3" class="main">'.zen_draw_separator().'</td></tr>';
      for ($i=0, $n=sizeof($quotes); $i<$n; $i++) {
        if(sizeof($quotes[$i]['methods'])==1){
          // simple shipping method
          $thisquoteid = $quotes[$i]['id'].'_'.$quotes[$i]['methods'][0]['id'];
          $ShipTxt.= '<tr class="'.$extra.'">';
          $ShipTxt.='<td class="main">'.$quotes[$i]['icon'].'&nbsp;</td>';
          if($quotes[$i]['error']){
            $ShipTxt.='<td colspan="2" class="main">'.$quotes[$i]['module'].'&nbsp;';
            $ShipTxt.= '('.$quotes[$i]['error'].')</td></tr>';
          }else{
            if($selected_shipping['id'] == $thisquoteid){
              $ShipTxt.='<td class="main"><strong>'.$quotes[$i]['module'].'&nbsp;';
              $ShipTxt.= '('.$quotes[$i]['methods'][0]['title'].')</strong></td><td align="right" class="main"><strong>'.$currencies->format(zen_add_tax($quotes[$i]['methods'][0]['cost'], $quotes[$i]['tax'])).'<strong></td></tr>';
            }else{
              $ShipTxt.='<td class="main">'.$quotes[$i]['module'].'&nbsp;';
              $ShipTxt.= '('.$quotes[$i]['methods'][0]['title'].')</td><td align="right" class="main">'.$currencies->format(zen_add_tax($quotes[$i]['methods'][0]['cost'], $quotes[$i]['tax'])).'</td></tr>';
            }
          }
        } else {
          // shipping method with sub methods (multipickup)
          for ($j=0, $n2=sizeof($quotes[$i]['methods']); $j<$n2; $j++) {
            $thisquoteid = $quotes[$i]['id'].'_'.$quotes[$i]['methods'][$j]['id'];
            $ShipTxt.= '<tr class="'.$extra.'">';
            $ShipTxt.='<td class="main">'.$quotes[$i]['icon'].'&nbsp;</td>';
            if($quotes[$i]['error']){
              $ShipTxt.='<td colspan="2" class="main">'.$quotes[$i]['module'].'&nbsp;';
              $ShipTxt.= '('.$quotes[$i]['error'].')</td></tr>';
            }else{
              if($selected_shipping['id'] == $thisquoteid){
                $ShipTxt.='<td class="main"><strong>'.$quotes[$i]['module'].'&nbsp;';
                $ShipTxt.= '('.$quotes[$i]['methods'][$j]['title'].')</strong></td><td align="right" class="main"><strong>'.$currencies->format(zen_add_tax($quotes[$i]['methods'][$j]['cost'], $quotes[$i]['tax'])).'</strong></td></tr>';
              }else{
                $ShipTxt.='<td class="main">'.$quotes[$i]['module'].'&nbsp;';
                $ShipTxt.= '('.$quotes[$i]['methods'][$j]['title'].')</td><td align="right" class="main">'.$currencies->format(zen_add_tax($quotes[$i]['methods'][$j]['cost'], $quotes[$i]['tax'])).'</td></tr>';
              }
            }
          }
        }
      }
    }
  }
  $ShipTxt.= '</table></form>';

  $info_box_contents = array();
  $info_box_contents[] = array('text' => $ShipTxt);
  new infoBox($info_box_contents);

} // Only do when something is in the cart
?>
             </td></tr></table>
<!-- shipping_estimator_eof //-->
