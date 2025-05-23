<?php
//
// +----------------------------------------------------------------------+
// |zen-cart Open Source E-commerce																			 |
// +----------------------------------------------------------------------+
// | Copyright (c) 2003 The zen-cart developers													 |
// |																																			|
// | http://www.zen-cart.com/index.php																		|
// |																																			|
// | Portions Copyright (c) 2003 osCommerce															 |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license,			 |
// | that is bundled with this package in the file LICENSE, and is				|
// | available through the world-wide-web at the following url:					 |
// | http://www.zen-cart.com/license/2_0.txt.														 |
// | If you did not receive a copy of the zen-cart license and are unable |
// | to obtain it through the world-wide-web, please send a note to			 |
// | license@zen-cart.com so we can mail you a copy immediately.					|
// +----------------------------------------------------------------------+
// $Id$
//
/**
 * @package ZenCart_Functions
*/

// use when proportional images is turned off or from a template directory
////
// The HTML image wrapper function
function zen_image_OLD($src, $alt = '', $width = '', $height = '', $parameters = '') {
	global $template_dir, $gBitSmarty;

//auto replace with defined missing image
	if ($src == DIR_WS_IMAGES and PRODUCTS_IMAGE_NO_IMAGE_STATUS == '1') {
		$src = DIR_WS_IMAGES . PRODUCTS_IMAGE_NO_IMAGE;
	}

	if ( (empty($src) || ($src == DIR_WS_IMAGES)) && (IMAGE_REQUIRED == 'false') ) {
		return false;
	}

	// if not in current template switch to template_default
	if (!file_exists($src)) {
		$src = str_replace(DIR_WS_TEMPLATES . $template_dir, DIR_WS_TEMPLATES . 'template_default', $src);
	}

// alt is added to the img tag even if it is null to prevent browsers from outputting
// the image filename as default
	$image = '<img src="' . zen_output_string($src) . '" border="0" alt="' . zen_output_string($alt) . '"';

	if (zen_not_null($alt)) {
		$image .= ' title=" ' . zen_output_string($alt) . ' "';
	}

	if ( (CONFIG_CALCULATE_IMAGE_SIZE == 'true') && (empty($width) || empty($height)) ) {
		if ($image_size = @getimagesize($src)) {
			if (empty($width) && zen_not_null($height)) {
				$ratio = $height / $image_size[1];
				$width = $image_size[0] * $ratio;
			} elseif (zen_not_null($width) && empty($height)) {
				$ratio = $width / $image_size[0];
				$height = $image_size[1] * $ratio;
			} elseif (empty($width) && empty($height)) {
				$width = $image_size[0];
				$height = $image_size[1];
			}
		} elseif (IMAGE_REQUIRED == 'false') {
			return false;
		}
	}

	if (zen_not_null($width) && zen_not_null($height)) {
		$image .= ' width="' . zen_output_string($width) . '" height="' . zen_output_string($height) . '"';
	}

	if (zen_not_null($parameters)) $image .= ' ' . $parameters;

	$image .= ' />';

	return $image;
}


////
// The HTML image wrapper function
function zen_image($src, $alt = '', $width = '', $height = '', $parameters = '') {
	global $template_dir;

	// soft clean the alt tag
	$alt = zen_clean_html($alt);

	// use old method on template images
	if (strstr($src, 'includes/templates') or strstr($src, 'includes/languages') or PROPORTIONAL_IMAGES_STATUS == '0') {
		return zen_image_OLD($src, $alt, $width, $height, $parameters);
	}

//auto replace with defined missing image
	if ($src == DIR_WS_IMAGES and PRODUCTS_IMAGE_NO_IMAGE_STATUS == '1') {
		$src = DIR_WS_IMAGES . PRODUCTS_IMAGE_NO_IMAGE;
	}

	if ( (empty($src) || ($src == DIR_WS_IMAGES)) && (IMAGE_REQUIRED == 'false') ) {
		return false;
	}

	// if not in current template switch to template_default
	if (!file_exists($src)) {
		$src = str_replace(DIR_WS_TEMPLATES . $template_dir, DIR_WS_TEMPLATES . 'template_default', $src);
	}

// alt is added to the img tag even if it is null to prevent browsers from outputting
// the image filename as default
	$image = '<img src="' . zen_output_string($src) . '" alt="' . zen_output_string($alt) . '"';

	if (zen_not_null($alt)) {
		$image .= ' title=" ' . zen_output_string($alt) . ' "';
	}

	$srcPath = BIT_ROOT_PATH.$src;
	if ( ((CONFIG_CALCULATE_IMAGE_SIZE == 'true') && (empty($width) || empty($height))) ) {
		if ($image_size = @getimagesize($srcPath)) {
			if (empty($width) && zen_not_null($height)) {
				$ratio = $height / $image_size[1];
				$width = $image_size[0] * $ratio;
			} elseif (zen_not_null($width) && empty($height)) {
				$ratio = $width / $image_size[0];
				$height = $image_size[1] * $ratio;
			} elseif (empty($width) && empty($height)) {
				$width = $image_size[0];
				$height = $image_size[1];
			}
		} elseif (IMAGE_REQUIRED == 'false') {
			return false;
		}
	}


	if (zen_not_null($width) && zen_not_null($height) and file_exists($srcPath) && is_file( $srcPath ) ) {
//			$image .= ' width="' . zen_output_string($width) . '" height="' . zen_output_string($height) . '"';
// proportional images
		$image_size = getimagesize($srcPath);
		// fix division by zero error
		$ratio = ($image_size[0] != 0 ? $width / $image_size[0] : 1);
		if ($image_size[1]*$ratio > $height) {
			$ratio = $height / $image_size[1];
			$width = $image_size[0] * $ratio;
		} else {
			$height = $image_size[1] * $ratio;
		}

		$image .= ' width="' . $width . '" height="' . $height . '"';
	} else {
		 // override on missing image to allow for proportional and required/not required
		if (IMAGE_REQUIRED == 'false') {
			return false;
		} else {
//				 $image .= ' width="' . SMALL_IMAGE_WIDTH . '" height="' . SMALL_IMAGE_HEIGHT . '"';
		}
	}

	if (zen_not_null($parameters)) $image .= ' ' . $parameters;

	$image .= ' />';

	return $image;
}

////
// Output a separator either through whitespace, or with an image
function zen_draw_separator($image = 'true', $width = '100%', $height = '1') {

	// set default to use from template - zen_image will translate if not found in current template
	if ($image == 'true') {
		$image = BITCOMMERCE_PKG_URL . 'images/' . OTHER_IMAGE_BLACK_SEPARATOR;
	} else {
		if (!strstr($image, DIR_WS_TEMPLATE_IMAGES)) {
			$image = DIR_WS_TEMPLATE_IMAGES . $image;
		}
	}
	return zen_image($image, '', $width, $height);
}

////
// Output a form
function zen_draw_form($name, $action, $method = 'post', $parameters = '') {
	$form = '<form name="' . zen_output_string($name) . '" action="' . zen_output_string($action) . '" method="' . zen_output_string($method) . '"';

	if (zen_not_null($parameters)) $form .= ' ' . $parameters;

	$form .= '>';

	return $form;
}

////
// Output a selection field - alias function for zen_draw_checkbox_field() and zen_draw_radio_field()
function zen_draw_selection_field( $pParamHash ) { // $name, $type, $value = '', $checked = false, $parameters = '', $label='', $help='') {
	$cssClass = '';
	switch( $pParamHash['type'] ) {
		case 'radio':
		case 'checkbox':
			break;
		default:
			$cssClass .= 'form-control';
			break;
	}

	$selection = '<div class="'.$pParamHash['type'].' products-option-'.(!empty( $pParamHash['value'] ) && is_numeric( $pParamHash['value'] ) ? $pParamHash['value'] : $pParamHash['type']).'"><label><input class="'.$cssClass.'" type="' . zen_output_string($pParamHash['type']) . '" name="' . zen_output_string($pParamHash['name']) . '"';

	if( isset( $pParamHash['value'] ) ) {
		$selection .= ' value="' . zen_output_string($pParamHash['value']) . '"';
	}

	if ( !empty($pParamHash['checked'] ) ) {
		$selection .= ' checked="checked"';
	}

	if ( !empty( $pParamHash['parameters'] ) ) {
		$selection .= ' ' . $pParamHash['parameters'];
	}

	$selection .= ' />';

	if( !empty( $pParamHash['label'] ) ) {
		$selection .= tra( $pParamHash['label'] );
	}

	$selection .= '</label>';

	if( !empty( $pParamHash['help'] ) ) {
		$selection .= '<p class="help-block">'.tra( $pParamHash['help'] ).'</p>';
	}

	$selection .= '</div>';
	return $selection;
}

////
// Output a form checkbox field
function zen_draw_checkbox_field($name, $value = '', $checked = false, $parameters = '', $label='', $help='') {
	return zen_draw_selection_field( array( 'name' => $name, 'type'=>'checkbox', 'value'=>$value, 'checked'=>$checked, 'label'=>$label, 'parameters'=>$parameters ));
}

////
// Output a form radio field
function zen_draw_radio_field($name, $value = '', $checked = false, $parameters = '', $label='', $help='') {
	return zen_draw_selection_field( array( 'name' => $name, 'type'=>'radio', 'value'=>$value, 'checked'=>$checked, 'label'=>$label, 'parameters'=>$parameters ));
}

////
// Output a form textarea field
function zen_draw_textarea_field($name, $wrap, $width, $height, $text = '', $parameters = '', $reinsert_value = true) {
	$field = '<textarea class="form-control" name="' . zen_output_string($name) . '" wrap="' . zen_output_string($wrap) . '" cols="' . zen_output_string($width) . '" rows="' . zen_output_string($height) . '"';

	if (zen_not_null($parameters)) $field .= ' ' . $parameters;

	$field .= '>';

	if ( (isset($GLOBALS[$name])) && ($reinsert_value == true) ) {
		$field .= stripslashes($GLOBALS[$name]);
	} elseif (zen_not_null($text)) {
		$field .= $text;
	}

	$field .= '</textarea>';

	return $field;
}

////
// Hide form elements
function zen_hide_session_id() {
	global $session_started;

	if ( ($session_started == true) && defined('SID') && zen_not_null(SID) ) {
		return zen_draw_hidden_field(session_name(), session_id());
	}
}

////
// Creates a pull-down list of countries
function zen_get_country_zone_list( $pName, $pCountriesId, $pSelected = '', $pParameters = '') {
	$ret = '';
	if( $zones = zen_get_country_zones( $pCountriesId ) ) {
		$zoneArray = array(array('id' => '', 'text' => tra('Please Choose Your State or Province') ));
		foreach( $zones as $zoneId=>$zoneHash ) {
			$zoneArray[] = array('id' => $zoneHash['zone_id'], 'text' => $zoneHash['zone_name']);
		}
		if( !empty( $pSelected ) && !is_numeric( $pSelected ) ) {
			$pSelected = zen_get_zone_id( $pCountriesId, $pSelected );
		}
		$ret = zen_draw_pull_down_menu( $pName, $zoneArray, $pSelected, $pParameters );
	}
	return $ret;
}

////
// Creates a pull-down list of countries
function zen_get_country_list($name, $selected = '', $parameters = '') {
	if( $countries = zen_get_countries() ) {
		$countries_array = array(array('id' => '', 'text' => tra('Please Choose Your Country') ));

		for ($i=0, $n=sizeof($countries); $i<$n; $i++) {
			$countries_array[] = array('id' => $countries[$i]['countries_id'], 'text' => $countries[$i]['countries_name']);
		}

		if( !empty( $selected ) && !is_numeric( $selected ) ) {
			$selected = zen_get_country_id( $selected );
		}
	}

	return zen_draw_pull_down_menu($name, $countries_array, $selected, $parameters);
}

function commerce_country_select( $pParams ) {
	return zen_get_country_list( $pParams['name'], $pParams['selected'] );
}

global $gBitSmarty;
$gBitSmarty->registerPlugin( 'function', 'commerce_country_select', 'commerce_country_select' );

