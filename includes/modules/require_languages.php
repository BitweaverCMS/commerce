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

$language_page_directory = DIR_WS_LANGUAGES . $gBitCustomer->getLanguage() . '/';

// determine language or template language file
if (file_exists($language_page_directory . $gCommerceSystem->mTemplateDir . '/' . $current_page_base . '.php')) {
	$template_dir_select = $gCommerceSystem->mTemplateDir . '/';
} else {
	$template_dir_select = '';
}

// set language or template language file
$directory_array = $gCommerceSystem->get_template_part($language_page_directory . $template_dir_select, '/^'.$current_page_base . '\./');

// load language file(s)
while(list ($key, $value) = each($directory_array)) {
	require_once($language_page_directory . $template_dir_select . $value);
}

$directory_array = $gCommerceSystem->get_template_part($language_page_directory . $template_dir, '/^'.$current_page_base . '/');

// load language file(s)
while(list ($key, $value) = each($directory_array)) {
	require_once($language_page_directory . $template_dir_select . $value);
}
/*
$directory_array = $gCommerceSystem->get_template_part($language_page_directory . 'extra_definitions/' . $gCommerceSystem->mTemplateDir . '/', '/^'.$current_page_base . '/');

while(list ($key, $value) = each($directory_array)) {
	require($language_page_directory . 'extra_definitions/' . $gCommerceSystem->mTemplateDir . '/' . $value);
}

$directory_array = $gCommerceSystem->get_template_part($language_page_directory . 'extra_definitions/', '/^'.$current_page_base . '/');

while(list ($key, $value) = each($directory_array)) {
	require($language_page_directory . 'extra_definitions/' . $value);
}
*/
?>
