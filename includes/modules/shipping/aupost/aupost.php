<?php
/*
// +----------------------------------------------------------------------+
// | bitcommerce,	http://www.bitcommerce.org                            |
// +----------------------------------------------------------------------+
// | Copyright (c) 2022 bitcommerce.org                                   |
// | This source file is subject to version 3.0 of the GPL license        |
// | Portions Copyright (c) 2005 http://www.zen-cart.com                  |
// | Portions Copyright (c) 2003 osCommerce                               |
// +----------------------------------------------------------------------+

  Copyright (c) 2007-2009 Rod Gasson / VCSWEB
 
    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.

 $Id: aupost.php,v2.2.1  Nov 2016

*/

// class constructor

require_once( BITCOMMERCE_PKG_CLASS_PATH.'CommercePluginShippingBase.php' );

class aupost extends CommercePluginShippingBase {

	public function __construct() {
		parent::__construct();
		$this->title			= tra( 'Australia Post' );
		$this->description		= tra( 'Canada Post Parcel Service <p>You will need a Account Number, Username and Password from the <a href="https://www.canadapost.ca/cpotools/apps/drc/home?execution=e1s1">Developer Program</a></p>' );
	}
/*
function __construct()
{
    global $order, $db, $template ;

    // disable only when entire cart is free shipping
    if (zen_get_shipping_enabled($this->code))  $this->enabled = ((MODULE_SHIPPING_AUPOST_STATUS == 'True') ? true : false);


    $this->code = 'aupost';
    $this->title = MODULE_SHIPPING_AUPOST_TEXT_TITLE ;
    $this->description = MODULE_SHIPPING_AUPOST_TEXT_DESCRIPTION;
    $this->sort_order = MODULE_SHIPPING_AUPOST_SORT_ORDER;
    $this->icon = $template->get_template_dir('aupost.jpg', '' ,'','images/icons'). '/aupost.jpg';
    if (zen_not_null($this->icon)) $this->quotes['icon'] = zen_image($this->icon, $this->title);
    $this->logo = $template->get_template_dir('aupost_logo.jpg', '','' ,'images/icons'). '/aupost_logo.jpg';
    $this->tax_class = MODULE_SHIPPING_AUPOST_TAX_CLASS;
    $this->tax_basis = 'Shipping' ;    // It'll always work this way, regardless of any global settings

     if (MODULE_SHIPPING_AUPOST_ICONS != "No" )
	 {
        if (zen_not_null($this->logo)) $this->title = zen_image($this->logo, $this->title) ;
    }

    $this->allowed_methods = explode(", ", MODULE_SHIPPING_AUPOST_TYPES1) ;
}
*/
// class methods
//////////////////////////////////////////////////////////////

	/**
	 * Get quote from shipping provider's API:
	 *
	 * @param string $method
	 * @return array of quotation results
	 */
	public function quote( $pShipHash ) {
		global $db, $order, $cart, $currencies, $template, $parcelweight, $packageitems;
		//	$module = substr($_SESSION['shipping'], 0,6);
		//	$method = substr($_SESSION['shipping'],7);
		// removed misguided attempt to retrieve user selection from session.
		// method argument is supplied to this module by Zen Cart if required (single quote).
		// see later comments on removing underscores from AusPost-defined shipping methods.
eb( $pShipHash );
		if (zen_not_null($method) && (isset($_SESSION['aupostQuotes'])))
		{
			$testmethod = $_SESSION['aupostQuotes']['methods'] ;

			foreach($testmethod as $temp) {
				$search = array_search("$method", $temp) ;

				if (strlen($search) > 0 && $search >= 0) break ;
				}

			$usemod = $this->title ; 
			$usetitle = $temp['title'] ;

			if (MODULE_SHIPPING_AUPOST_ICONS != "No" )
			{  // strip the icons //
				if (preg_match('/(title)=("[^"]*")/',$this->title, $module))  $usemod = trim($module[2], "\"") ;
				if (preg_match('/(title)=("[^"]*")/',$temp['title'], $title)) $usetitle = trim($title[2], "\"") ;
			}

			 $this->quotes = array
			(
				'id' => $this->code,
				'module' => $usemod,
				'methods' => array
				(
					array
					(
						'id' => $method,
						'title' => $usetitle,
						'cost' => $temp['cost']
					)
				)
			);

			if ($this->tax_class >  0)
			{
				$this->quotes['tax'] = zen_get_tax_rate($this->tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);
			}

			return $this->quotes;   // return a single quote

		}  ////////////////////////////  Single Quote Exit Point //////////////////////////////////

	  // Maximums
		$MAXWEIGHT_P = 20 ; // 20kgs
		$MAXLENGTH_P = 105 ;  // 105cm max parcel length
		$MAXGIRTH_P =  140 ;  // 140cm max parcel girth  ( (width + height) * 2)

		// default dimensions //
		$x = explode(',', MODULE_SHIPPING_AUPOST_DIMS) ;
		$defaultdims = array($x[0],$x[1],$x[2]) ;
		sort($defaultdims) ;  // length[2]. width[1], height=[0]

		// initialise variables
		$parcelwidth = 0 ;
		$parcellength = 0 ;
		$parcelheight = 0 ;
		$parcelweight = 0 ;
		$cube = 0 ;

		$frompcode = MODULE_SHIPPING_AUPOST_SPCODE;
		$dest_country=$order->delivery['country']['iso_code_2'];
		$topcode = str_replace(" ","",($order->delivery['postcode']));
		$aus_rate = (float)$currencies->get_value('AUD') ;
		$ordervalue=$order->info['total'] / $aus_rate ;
		$tare = MODULE_SHIPPING_AUPOST_TARE ;

		  $FlatText = " Using AusPost Flat Rate." ;

		// loop through cart extracting productIDs and qtys //

		$myorder = $_SESSION['cart']->get_products();
	 
		for($x = 0 ; $x < count($myorder) ; $x++ )
		{
			$t = $myorder[$x]['id'] ;
			$q = $myorder[$x]['quantity'];
			$w = $myorder[$x]['weight'];
	 
			$dim_query = "select products_length, products_height, products_width from " . TABLE_PRODUCTS . " where products_id='$t' limit 1 ";
			$dims = $db->Execute($dim_query);

			// re-orientate //
			$var = array($dims->fields['products_width'], $dims->fields['products_height'], $dims->fields['products_length']) ; sort($var) ;
			$dims->fields['products_length'] = $var[2] ; $dims->fields['products_width'] = $var[1] ;  $dims->fields['products_height'] = $var[0] ;
			// if no dimensions provided use the defaults
			if($dims->fields['products_height'] == 0) {$dims->fields['products_height'] = $defaultdims[0] ; }
			if($dims->fields['products_width']  == 0) {$dims->fields['products_width']  = $defaultdims[1] ; }
			if($dims->fields['products_length'] == 0) {$dims->fields['products_length'] = $defaultdims[2] ; }
			if($w == 0) {$w = 1 ; }  // 1 gram minimum
			
			$parcelweight += $w * $q;
			
			// get the cube of these items
			$itemcube =  ($dims->fields['products_width'] * $dims->fields['products_height'] * $dims->fields['products_length'] * $q) ;
			// Increase widths and length of parcel as needed
			if ($dims->fields['products_width'] >  $parcelwidth)  { $parcelwidth  = $dims->fields['products_width']  ; }
			if ($dims->fields['products_length'] > $parcellength) { $parcellength = $dims->fields['products_length'] ; }
			// Stack on top on existing items
			$parcelheight =  ($dims->fields['products_height'] * ($q)) + $parcelheight  ;
			$packageitems =  $packageitems + $q ;

			// Useful debugging information //
			if ( MODULE_SHIPPING_AUPOST_DEBUG == "Yes" )
			{
				$dim_query = "select products_name from " . TABLE_PRODUCTS_DESCRIPTION . " where products_id='$t' limit 1 ";
				$name = $db->Execute($dim_query);

				echo "<center><table border=1 width=95% ><th colspan=8>Debugging information</hr>
				<tr><th>Item " . ($x + 1) . "</th><td colspan=7>" . $name->fields['products_name'] . "</td>
				<tr><th width=1%>Attribute</th><th colspan=3>Item</th><th colspan=4>Parcel</th></tr>
				<tr><th>Qty</th><td>&nbsp; " . $q . "<th>Weight</th><td>&nbsp; " . $w . "</td>
				<th>Qty</th><td>&nbsp;$packageitems</td><th>Weight</th><td>&nbsp;" ; echo $parcelweight + (($parcelweight* $tare)/100) ; echo "</td></tr>
				<tr><th>Dimensions</th><td colspan=3>&nbsp; " . $dims->fields['products_length'] . " x " . $dims->fields['products_width'] . " x "  . $dims->fields['products_height'] . "</td>
				<td colspan=4>&nbsp;$parcellength  x  $parcelwidth  x $parcelheight </td></tr>
				<tr><th>Cube</th><td colspan=3>&nbsp; " . $itemcube . "</td><td colspan=4>&nbsp;" . ($parcelheight * $parcellength * $parcelwidth) . " </td></tr>
				<tr><th>CubicWeight</th><td colspan=3>&nbsp;" . (($dims->fields['products_length'] * $dims->fields['products_height'] * $dims->fields['products_width']) * 0.00001 * 250) . "Kgs  </td><td colspan=4>&nbsp;" . (($parcelheight * $parcellength * $parcelwidth) * 0.00001 * 250) . "Kgs </td></tr>
				</table></center> " ;
			}
		}

	   

		// package created, now re-orientate and check dimensions
		$var = array($parcelheight, $parcellength, $parcelwidth) ; sort($var) ;
		$parcelheight = $var[0] ; $parcelwidth = $var[1] ; $parcellength = $var[2] ;
		$girth = ($parcelheight * 2) + ($parcelwidth * 2)  ;

		$parcelweight = $parcelweight + (($parcelweight*$tare)/100) ;

		if (MODULE_SHIPPING_AUPOST_WEIGHT_FORMAT == "gms") {$parcelweight = $parcelweight/1000 ; }

	//  save dimensions for display purposes on quote form (this way I don't need to hack another system file)
	$_SESSION['swidth'] = $parcelwidth ; $_SESSION['sheight'] = $parcelheight ;
	$_SESSION['slength'] = $parcellength ;  // $_SESSION['boxes'] = $shipping_num_boxes ;

		// Check for maximum length allowed
		if($parcellength > $MAXLENGTH_P)
		{
			 $cost = $this->_get_error_cost($dest_country) ;

		   if ($cost == 0) return  ;
	   
			$methods[] = array('title' => ' (AusPost excess length)', 'cost' => $cost ) ;
			$this->quotes['methods'] = $methods;   // set it
			return $this->quotes;
		}  // exceeds AustPost maximum length. No point in continuing.



	   // Check girth
		if($girth > $MAXGIRTH_P )
		{
			 $cost = $this->_get_error_cost($dest_country) ;

		   if ($cost == 0)  return  ;
		  
			$methods[] = array('title' => ' (AusPost excess girth)', 'cost' => $cost ) ;
			$this->quotes['methods'] = $methods;   // set it
			return $this->quotes;
		}  // exceeds AustPost maximum girth. No point in continuing.



		if ($parcelweight > $MAXWEIGHT_P)
		{
			 $cost = $this->_get_error_cost($dest_country) ;

		   if ($cost == 0)  return  ;
		  
			$methods[] = array('title' => ' (AusPost excess weight)', 'cost' => $cost ) ;
			$this->quotes['methods'] = $methods;   // set it
			return $this->quotes;
		}  // exceeds AustPost maximum weight. No point in continuing.

		// Check to see if cache is useful
		if(isset($_SESSION['aupostParcel']))
		{
			$test = explode(",", $_SESSION['aupostParcel']) ;

			if (
				($test[0] == $dest_country) &&
				($test[1] == $topcode) &&
				($test[2] == $parcelwidth) &&
				($test[3] == $parcelheight) &&
				($test[4] == $parcellength) &&
				($test[5] == $parcelweight) &&
				($test[6] == $ordervalue)
			   )
			{
				if ( MODULE_SHIPPING_AUPOST_DEBUG == "Yes" )
				{
					echo "<center><table border=1 width=95% ><td align=center><font color=\"#FF0000\">Using Cached quotes </font></td></table></center>" ;
				}


	$this->quotes =  $_SESSION['aupostQuotes'] ;
	return $this->quotes ;

	///////////////////////////////////  Cache Exit Point //////////////////////////////////

			} // No cache match -  get new quote from server //

		}  // No cache session -  get new quote from server //
	///////////////////////////////////////////////////////////////////////////////////////////////

		// always save new session  CSV //
		$_SESSION['aupostParcel'] = implode(",", array($dest_country, $topcode, $parcelwidth, $parcelheight, $parcellength, $parcelweight, $ordervalue)) ;
		$shipping_weight = $parcelweight ;  // global value for zencart

		// convert cm to mm 'cos thats what the server uses //
		$parcelwidth = $parcelwidth ;
		$parcelheight = $parcelheight ;
		$parcellength = $parcellength ;

		// Set destination code ( postcode if AU, else 2 char iso country code )
		$dcode = ($dest_country == "AU") ? $topcode:$dest_country ;

		if (!$dcode) $dcode =  SHIPPING_ORIGIN_ZIP ; // if no destination postcode - (aka first run - zencart only), set to local (cheapest rates)

		$flags = ((MODULE_SHIPPING_AUPOST_HIDE_PARCEL == "No") || ( MODULE_SHIPPING_AUPOST_DEBUG == "Yes" )) ? 0:1 ;

		// Server query string //
		$qu = $this->get_auspost_api("https://digitalapi.auspost.com.au/postage/parcel/domestic/service.xml?from_postcode=" . MODULE_SHIPPING_AUPOST_SPCODE . "&to_postcode=$dcode&length=$parcellength&width=$parcelwidth&height=$parcelheight&weight=$parcelweight") ; 

		if ( MODULE_SHIPPING_AUPOST_DEBUG == "Yes" )  { echo "<table><tr><td>Server Returned:<br>" . $qu . "</td></tr></table>" ; }

		// If we have any results, parse them into an array   
		$xml = ($qu == '') ? array() : new SimpleXMLElement($qu)  ;

	 // print_r($xml) ; exit ;
		/////  Initialise our quote array(s)
		$this->quotes = array('id' => $this->code, 'module' => $this->title);
		$methods = array() ;
		

	///////////////////////////////////////
	//
	//  loop through the quotes retrieved //

		$i = 0 ;  // counter
		foreach($xml as $foo => $bar)
		{
			$id = str_replace("_", "", $xml->service[$i]->code);
		// remove underscores from AusPost methods. Zen Cart uses underscore as delimiter between module and method.
		// underscores must also be removed from case statements below.

			$cost = (float)($xml->service[$i]->price);
			$description = ($xml->service[$i]->name);
			$i++ ;

	if ( MODULE_SHIPPING_AUPOST_DEBUG == "Yes" )  { echo "<table><tr><td>" ; echo "ID $id COST $cost DESC $description" ; echo "</td></tr></table>" ; }

			$add = 0 ; $f = 0 ; $info=0 ;

	switch ($id) {
		
			 case  "AUSPARCELREGULARSATCHEL5KG" ;
			 case  "AUSPARCELREGULARSATCHEL3KG" ;
		 case  "AUSPARCELREGULARSATCHEL500G" ;
			if ((in_array("Prepaid Satchel", $this->allowed_methods)))
			{
				$add = MODULE_SHIPPING_AUPOST_PPS_HANDLING ; $f = 1 ;
			}
			break;
			
		case  "AUSPARCELEXPRESSSATCHEL5KG" ;
			case  "AUSPARCELEXPRESSSATCHEL3KG" ;
		case  "AUSPARCELEXPRESSSATCHEL500G" ;
			if ((in_array("Prepaid Express Satchel", $this->allowed_methods)))
			{
				$add =  MODULE_SHIPPING_AUPOST_PPSE_HANDLING ; $f = 1 ;
			}
			break;
			
		case  "AUSPARCELREGULAR" ;
			if (in_array("Regular Parcel", $this->allowed_methods))
			{
				$add = MODULE_SHIPPING_AUPOST_RPP_HANDLING ; $f = 1 ;
			}
			break;
			
			case  "REG" ;
			if (in_array("Registered Parcel", $this->allowed_methods))
			{
				$add =  MODULE_SHIPPING_AUPOST_RPP_HANDLING + MODULE_SHIPPING_AUPOST_RI_HANDLING ; $f = 1 ; $info = $xml->information[0]->registration ;
			}
			break;
			
			case  "AUSPARCELEXPRESS" ;
			if (in_array("Express Parcel", $this->allowed_methods)) 
			{
				$add = MODULE_SHIPPING_AUPOST_EXP_HANDLING ; $f = 1 ;
			}
			break;
		
		case  "AUSPARCELPLATINUM" ;
			 if (in_array("Express Post Platinum Parcel", $this->allowed_methods))
			{
				$add = MODULE_SHIPPING_AUPOST_PLAT_HANDLING ; $f = 1 ;
			}
			break;
			
		 case  "AUSPARCELPLATINUMSATCHEL5KG" ;
			 case  "AUSPARCELPLATINUMSATCHEL3KG" ;
		 case  "AUSPARCELPLATINUMSATCHEL500G" ;
			if ((in_array("Express Post Platinum Satchel", $this->allowed_methods)))
			{
				$add = MODULE_SHIPPING_AUPOST_PLATSATCH_HANDLING ; $f = 1 ;
			}
			break;
		}
			//////////////////////////////
			if ((($cost > 0) && ($f == 1)) || ( MODULE_SHIPPING_AUPOST_DEBUG == "Yes" ))
			{
				$cost = $cost + $add ;
		   if ( MODULE_SHIPPING_AUPOST_CORE_WEIGHT == "Yes") { $cost = $cost * $shipping_num_boxes ; }

				if (($dest_country == "AU") && (($this->tax_class) > 0))
				{
					$t = $cost - ($cost / (zen_get_tax_rate($this->tax_class, $order->delivery['country']['id'], $order->delivery['zone_id'])+1)) ;

					if ($t > 0) $cost = $t ;
				}
				if  (MODULE_SHIPPING_AUPOST_HIDE_HANDLING !='Yes')
				{
					$details = ' (Includes ' . $currencies->format($add / $aus_rate ). ' Packaging &amp; Handling ';

					if ($info > 0)  {
						$details = $details." +$".$info." fee)." ;

					}  else {$details = $details.")" ;}


				}

				$cost = $cost / $aus_rate;

				$methods[] = array('id' => "$id",  'title' => " ".$description . " " . $details, 'cost' => ($cost ));
			}


		}  // end foreach loop

	///////////////////////////////////////////////////////////////////////
	//
	//  check to ensure we have at least one valid quote - produce error message if not.

		if  (sizeof($methods) == 0) {

			$cost = $this->_get_error_cost($dest_country) ;

		   if ($cost == 0)  return  ;

		   $methods[] = array( 'id' => "Error",  'title' =>MODULE_SHIPPING_AUPOST_TEXT_ERROR ,'cost' => $cost ) ;
		}


		//  Sort by cost //
		$sarray[] = array() ;
		$resultarr = array() ;

		foreach($methods as $key => $value)
		{
			$sarray[ $key ] = $value['cost'] ;
		}
		asort( $sarray ) ;

		foreach($sarray as $key => $value)
		{
			$resultarr[ $key ] = $methods[ $key ] ;
		}

	  $this->quotes['methods'] = array_values($resultarr) ;   // set it

		if ($this->tax_class >  0)
		{
			$this->quotes['tax'] = zen_get_tax_rate($this->tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);
	   
		}
		
	 
	$_SESSION['aupostQuotes'] = $this->quotes  ; // save as session to avoid reprocessing when single method required

	return $this->quotes;   //  all done //

	   ///////////////////////////////////  Final Exit Point //////////////////////////////////
	}

	function _get_error_cost($dest_country) {
		
		$x = explode(',', MODULE_SHIPPING_AUPOST_COST_ON_ERROR) ;

		unset($_SESSION['aupostParcel']) ;  // don't cache errors.

		$cost = $dest_country == "AU" ?  $x[0]:$x[1] ;

		if ($cost == 0) {
			$this->enabled = FALSE ;
			unset($_SESSION['aupostQuotes']) ;
		} else {  
			$this->quotes = array('id' => $this->code, 'module' => 'Flat Rate'); 
		}

		return $cost;
    }

	/**
	* rows for com_configuration table as associative array of column => value
	*/
	protected function config() {
		$ret = array_merge( parent::config(), array( 
			$this->getModuleKeyTrunk().'_AUPOST_AUTHKEY' => array(
				'configuration_title' => 'Auspost API Key',
				'configuration_description' => 'To use this module, you must obtain a 36 digit API Key from the <a href=\"https://auspost.com.au/devcentre/pacpcs-registration.asp\" target=\"_blank\">Auspost Development Centre</a>',
			),
			$this->getModuleKeyTrunk().'_AUPOST_SPCODE' => array(
				'configuration_title' => 'Dispatch Postcode',
				'configuration_value' => '2000',
				'configuration_description' => 'Dispatch Postcode?',
			),
			$this->getModuleKeyTrunk().'_AUPOST_RPP_HANDLING' => array(
				'configuration_title' => 'Handling Fee - Regular parcels',
				'configuration_value' => '0.00',
				'configuration_description' => 'Handling Fee Regular parcels',
			),
			$this->getModuleKeyTrunk().'_AUPOST_PPS_HANDLING' => array(
				'configuration_title' => 'Handling Fee - Prepaid Satchels',
				'configuration_value' => '0.00',
				'configuration_description' => 'Handling Fee for Prepaid Satchels.',
			),
			$this->getModuleKeyTrunk().'_AUPOST_PPSE_HANDLING' => array(
				'configuration_title' => 'Handling Fee - Prepaid Satchels - Express',
				'configuration_value' => '0.00',
				'configuration_description' => 'Handling Fee for Prepaid Express Satchels.',
			),
			$this->getModuleKeyTrunk().'_AUPOST_EXP_HANDLING' => array(
				'configuration_title' => 'Handling Fee - Express parcels',
				'configuration_value' => '0.00',
				'configuration_description' => 'Handling Fee for Express parcels.',
			),
			$this->getModuleKeyTrunk().'_AUPOST_PLAT_HANDLING' => array(
				'configuration_title' => 'Handling Fee - Platinum parcels',
				'configuration_value' => '0.00',
				'configuration_description' => 'Handling Fee for Platinum parcels.',
			),
			$this->getModuleKeyTrunk().'_AUPOST_PLATSATCH_HANDLING' => array(
				'configuration_title' => 'Handling Fee - Platinum Satchels',
				'configuration_value' => '0.00',
				'configuration_description' => 'Handling Fee for Platinum Satchels.',
			),
			$this->getModuleKeyTrunk().'_AUPOST_DIMS' => array(
				'configuration_title' => 'Default Parcel Dimensions',
				'configuration_value' => '10,10,2',
				'configuration_description' => 'Default Parcel dimensions (in cm). Three comma seperated values (eg 10,10,2 = 10cm x 10cm x 2cm). These are used if the dimensions of individual products are not set',
			),
			$this->getModuleKeyTrunk().'_AUPOST_COST_ON_ERROR' => array(
				'configuration_title' => 'Cost on Error',
				'configuration_value' => '25',
				'configuration_description' => 'If an error occurs this Flat Rate fee will be used.</br> A value of zero will disable this module on error.',
			),
			$this->getModuleKeyTrunk().'_AUPOST_TARE' => array(
				'configuration_title' => 'Tare percent.',
				'configuration_value' => '10',
				'configuration_description' => 'Add this percentage of the items total weight as the tare weight. (This module ignores the global settings that seems to confuse many users. 10% seems to work pretty well.).',
			),
			$this->getModuleKeyTrunk().'_AUPOST_HIDE_HANDLING' => array(
				'configuration_title' => 'Hide Handling Fees?',
				'configuration_value' => 'Yes',
				'configuration_description' => 'The handling fees are still in the total shipping cost but the Handling Fee is not itemised on the invoice.',
				'set_function' => 'zen_cfg_select_option(array(\'Yes\', \'No\'), ',
			),
			$this->getModuleKeyTrunk().'_AUPOST_WEIGHT_FORMAT' => array(
				'configuration_title' => 'Parcel Weight format',
				'configuration_value' => 'kgs',
				'configuration_description' => 'Are your store items weighted by grams or Kilos? (required so that we can pass the correct weight to the server).',
				'set_function' => 'zen_cfg_select_option(array(\'gms\', \'kgs\'), ',
			),
			$this->getModuleKeyTrunk().'_AUPOST_DEBUG' => array(
				'configuration_title' => 'Enable Debug?',
				'configuration_value' => 'No',
				'configuration_description' => 'See how parcels are created from individual items.</br>Shows all methods returned by the server, including possible errors. <strong>Do not enable in a production environment</strong>',
				'set_function' => 'zen_cfg_select_option(array(\'No\', \'Yes\'), ',
			),
			$this->getModuleKeyTrunk().'_AUPOST_TYPES1' => array(
				'configuration_title' => 'Shipping Methods for Australia',
				'configuration_value' => 'Regular Parcel, Registered Parcel, Express Parcel, , Prepaid Satchel, Prepaid Express Satchel, Express Post Platinum Parcel, Express Post Platinum Satchel',
				'configuration_description' => 'Select the methods you wish to allow',
				'set_function' => 'zen_cfg_select_multioption(array(\'Regular Parcel\',\'Express Parcel\',\'Prepaid Satchel\',\'Prepaid Express Satchel\',\'Express Post Platinum Parcel\',\'Express Post Platinum Satchel\'), ',
			)
		) );
		// set some default values
		$ret[$this->getModuleKeyTrunk().'_ORIGIN_COUNTRY_CODE']['configuration_value'] = 'AU';
		return $ret;
	}

	function get_auspost_api($url)
	{
		$crl = curl_init();
		$timeout = 5;
		curl_setopt ($crl, CURLOPT_HTTPHEADER, array('AUTH-KEY:' . MODULE_SHIPPING_AUPOST_AUTHKEY));
		curl_setopt ($crl, CURLOPT_URL, $url);
		curl_setopt ($crl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($crl, CURLOPT_CONNECTTIMEOUT, $timeout);
		$ret = curl_exec($crl);
		curl_close($crl);
		return $ret;
	}

}
